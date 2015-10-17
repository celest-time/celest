<?php

namespace Php\Time\Format\Builder;
use Php\Time\Format\DateTimePrintContext;
use Php\Time\Temporal\ChronoField;
use Php\Time\ZoneOffset;
use Php\Time\Helper\Math;
use Php\Time\LocalDateTime;
use Php\Time\Format\DateTimeParseContext;
use Php\Time\Format\DateTimeFormatterBuilder;
use Php\Time\Format\DateTimeFormatter;
use RuntimeException;

/**
 * Prints or parses an ISO-8601 instant.
 */
final class InstantPrinterParser implements DateTimePrinterParser
{
    // days in a 400 year cycle = 146097
    // days in a 10,000 year cycle = 146097 * 25
    // seconds per day = 86400

    const SECONDS_PER_10000_YEARS = 146097 * 25 * 86400;

    const SECONDS_0000_TO_1970 = ((146097 * 5) - (30 * 365 + 7)) * 86400;
    /** @var int */
    private $fractionalDigits;

    function __construct($fractionalDigits)
    {
        $this->fractionalDigits = $fractionalDigits;
    }

    public function format(DateTimePrintContext $context, &$buf)
    {
        // use INSTANT_SECONDS, thus this code is not bound by Instant.MAX
        $inSecs = $context->getValue(ChronoField::INSTANT_SECONDS());
        $inNanos = null;
        if ($context->getTemporal()->isSupported(ChronoField::NANO_OF_SECOND())) {
            $inNanos = $context->getTemporal()->getLong(ChronoField::NANO_OF_SECOND());
        }

        if ($inSecs == null) {
            return false;
        }
        $inSec = $inSecs;
        $inNano = ChronoField::NANO_OF_SECOND()->checkValidIntValue($inNanos != null ? $inNanos : 0);
        // format mostly using LocalDateTime.toString
        if ($inSec >= -self::SECONDS_0000_TO_1970) {
            // current era
            $zeroSecs = $inSec - self::SECONDS_PER_10000_YEARS + self::SECONDS_0000_TO_1970;
            $hi = Math::floorDiv($zeroSecs, self::SECONDS_PER_10000_YEARS) + 1;
            $lo = Math::floorMod($zeroSecs, self::SECONDS_PER_10000_YEARS);
            $ldt = LocalDateTime::ofEpochSecond($lo - self::SECONDS_0000_TO_1970, 0, ZoneOffset::UTC());
            if ($hi > 0) {
                $buf .= '+' . $hi;
            }
            $buf .= $ldt;
            if ($ldt->getSecond() == 0) {
                $buf .= ":00";
            }
        } else {
            // before current era
            $zeroSecs = $inSec + self::SECONDS_0000_TO_1970;
            $hi = $zeroSecs / self::SECONDS_PER_10000_YEARS;
            $lo = $zeroSecs % self::SECONDS_PER_10000_YEARS;
            $ldt = LocalDateTime::ofEpochSecond($lo - self::SECONDS_0000_TO_1970, 0, ZoneOffset::UTC());
            $pos = strlen($buf);
            $buf->append($ldt);
            if ($ldt->getSecond() === 0) {
                $buf .= ":00";
            }
            if ($hi < 0) {
                if ($ldt->getYear() == -10000) {
                    $buf->replace($pos, $pos + 2, ($hi - 1));
                } else if ($lo == 0) {
                    $buf->insert($pos, $hi);
                } else {
                    $buf->insert($pos + 1, Math::abs($hi));
                }
            }
        }
        // add fraction
        if (($this->fractionalDigits < 0 && $inNano > 0) || $this->fractionalDigits > 0) {
            $buf .= '.';
            $div = 100000000;
            for ($i = 0; (($this->fractionalDigits == -1 && $inNano > 0) ||
                ($this->fractionalDigits == -2 && ($inNano > 0 || ($i % 3) != 0)) ||
                $i < $this->fractionalDigits); $i++) {
                $digit = $inNano / $div;
                $buf .= $digit + '0';
                $inNano = $inNano - ($digit * $div);
                $div = $div / 10;
            }
        }
        $buf .= 'Z';
        return true;
    }

    public function parse(DateTimeParseContext $context, $text, $position)
    {
        // new context to avoid overwriting fields like year/month/day
        $minDigits = ($this->fractionalDigits < 0 ? 0 : $this->fractionalDigits);
        $maxDigits = ($this->fractionalDigits < 0 ? 9 : $this->fractionalDigits);
        $parser = (new DateTimeFormatterBuilder())
            ->append(DateTimeFormatter::ISO_LOCAL_DATE())->appendLiteral('T')
            ->appendValue(ChronoField::HOUR_OF_DAY(), 2)->appendLiteral(':')
            ->appendValue(ChronoField::MINUTE_OF_HOUR(), 2)->appendLiteral(':')
            ->appendValue(ChronoField::SECOND_OF_MINUTE(), 2)
            ->appendFraction(ChronoField::NANO_OF_SECOND(), $minDigits, $maxDigits, true)
            ->appendLiteral('Z')
            ->toFormatter()->toPrinterParser(false);
        $newContext = $context->copy();
        $pos = $parser->parse($newContext, $text, $position);
        if ($pos < 0) {
            return $pos;
        }

// parser restricts most fields to 2 digits, so definitely int
// correctly parsed nano is also guaranteed to be valid
        $yearParsed = $newContext->getParsed(ChronoField::YEAR());
        $month = $newContext->getParsed(ChronoField::MONTH_OF_YEAR())->intValue();
        $day = $newContext->getParsed(ChronoField::DAY_OF_MONTH())->intValue();
        $hour = $newContext->getParsed(ChronoField::HOUR_OF_DAY())->intValue();
        $min = $newContext->getParsed(ChronoField::MINUTE_OF_HOUR())->intValue();
        $secVal = $newContext->getParsed(ChronoField::SECOND_OF_MINUTE());
        $nanoVal = $newContext->getParsed(ChronoField::NANO_OF_SECOND());
        $sec = ($secVal != null ? $secVal->intValue() : 0);
        $nano = ($nanoVal != null ? $nanoVal->intValue() : 0);
        $days = 0;
        if ($hour == 24 && $min == 0 && $sec == 0 && $nano == 0) {
            $hour = 0;
            $days = 1;
        } else if ($hour == 23 && $min == 59 && $sec == 60) {
            $context->setParsedLeapSecond();
            $sec = 59;
        }
        $year = (int)$yearParsed % 10000;
        try {
            $ldt = LocalDateTime::of($year, $month, $day, $hour, $min, $sec, 0)->plusDays($days);
            $instantSecs = $ldt->toEpochSecond(ZoneOffset::UTC());
            $instantSecs += Math::multiplyExact($yearParsed / 10000, self::SECONDS_PER_10000_YEARS);
        } catch (RuntimeException $ex) {
            // TODO What do we actually catch here and why
            return ~$position;
        }
        $successPos = $pos;
        $successPos = $context->setParsedField(ChronoField::INSTANT_SECONDS(), $instantSecs, $position, $successPos);
        return $context->setParsedField(ChronoField::NANO_OF_SECOND(), $nano, $position, $successPos);
    }

    public function __toString()
    {
        return "Instant()";
    }
}