<?php declare(strict_types=1);

namespace Celest\Format\Builder;

use Celest\Chrono\IsoChronology;
use Celest\Format\DateTimeParseContext;
use Celest\Format\DateTimePrintContext;
use Celest\Format\DateTimeTextProvider;
use Celest\Format\SignStyle;
use Celest\Format\TextStyle;
use Celest\Temporal\TemporalField;
use Celest\Temporal\TemporalQueries;

/**
 * Prints or parses field text.
 */
final class TextPrinterParser implements DateTimePrinterParser
{
    /** @var TemporalField */
    private $field;
    /** @var TextStyle */
    private $textStyle;
    /** @var DateTimeTextProvider */
    private $provider;
    /**
     * The cached number printer parser.
     * Immutable and volatile, so no synchronization needed.
     * @var NumberPrinterParser
     */

    private $numberPrinterParser;

    /**
     * Constructor.
     *
     * @param TemporalField $field the field to output, not null
     * @param TextStyle $textStyle the text style, not null
     * @param DateTimeTextProvider $provider the text provider, not null
     */
    public function __construct(TemporalField $field, TextStyle $textStyle, DateTimeTextProvider $provider)
    {
        // validated by caller
        $this->field = $field;
        $this->textStyle = $textStyle;
        $this->provider = $provider;
    }

    public function format(DateTimePrintContext $context, string &$buf) : bool
    {
        $value = $context->getValueField($this->field);
        if ($value === null) {
            return false;
        }

        $text = null;
        $chrono = $context->getTemporal()->query(TemporalQueries::chronology());
        if ($chrono === null || $chrono == IsoChronology::INSTANCE()) {
            $text = $this->provider->getText($this->field, $value, $this->textStyle, $context->getLocale());
        } else {
            $text = $this->provider->getText2($chrono, $this->field, $value, $this->textStyle, $context->getLocale());
        }
        if ($text === null) {
            return $this->numberPrinterParser()->format($context, $buf);
        }
        $buf .= $text;
        return true;
    }

    public function parse(DateTimeParseContext $context, string $parseText, int $position) : int
    {
        $length = strlen($parseText);
        if ($position < 0 || $position > $length) {
            throw new \OutOfRangeException();
        }

        $style = ($context->isStrict() ? $this->textStyle : null);
        $chrono = $context->getEffectiveChronology();
        $it = null;
        if ($chrono === null || $chrono == IsoChronology::INSTANCE()) {
            $it = $this->provider->getTextIterator($this->field, $style, $context->getLocale());
        } else {
            $it = $this->provider->getTextIterator2($chrono, $this->field, $style, $context->getLocale());
        }
        if ($it !== null) {
            foreach ($it as $key => $value) {
                // fix numeric indices
                $key = strval($key);
                if ($context->subSequenceEquals($key, 0, $parseText, $position, strlen($key))) {
                    return $context->setParsedField($this->field, $value, $position, $position + strlen($key));
                }
            }
            if ($context->isStrict()) {
                return ~$position;
            }
        }
        return $this->numberPrinterParser()->parse($context, $parseText, $position);
    }

    /**
     * Create and cache a number printer parser.
     * @return NumberPrinterParser the number printer parser for this field, not null
     */
    private function numberPrinterParser() : NumberPrinterParser
    {
        if ($this->numberPrinterParser === null) {
            $this->numberPrinterParser = new NumberPrinterParser($this->field, 1, 19, SignStyle::NORMAL());
        }
        return $this->numberPrinterParser;
    }

    public function __toString() : string
    {
        if ($this->textStyle == TextStyle::FULL()) {
            return "Text(" . $this->field . ")";
        }
        return "Text(" . $this->field . "," . $this->textStyle . ")";
    }
}