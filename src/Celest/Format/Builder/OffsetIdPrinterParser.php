<?php
/**
 * Created by IntelliJ IDEA.
 * User: hanikel
 * Date: 11.09.15
 * Time: 16:11
 */

namespace Celest\Format\Builder;

use Celest\Format\DateTimeParseContext;
use Celest\Format\DateTimePrintContext;
use Celest\Helper\Math;
use Celest\Temporal\ChronoField;
use Symfony\Component\Yaml\Exception\RuntimeException;


/**
 * Prints or parses an offset ID.
 */
final class OffsetIdPrinterParser implements DateTimePrinterParser
{
    public static $PATTERNS =
        [
            "+HH", "+HHmm", "+HH:mm", "+HHMM", "+HH:MM", "+HHMMss", "+HH:MM:ss", "+HHMMSS", "+HH:MM:SS",
        ];  // order used in pattern builder

//static final OffsetIdPrinterParser INSTANCE_ID_Z = new OffsetIdPrinterParser("+HH:MM:ss", "Z");
//static final OffsetIdPrinterParser INSTANCE_ID_ZERO = new OffsetIdPrinterParser("+HH:MM:ss", "0");

    /** @var string */
    private $noOffsetText;
    /** @var int */
    private $type;

    /**
     * Constructor.
     *
     * @param string $pattern the pattern
     * @param string $noOffsetText the text to use for UTC, not null
     */
    public function __construct($pattern, $noOffsetText)
    {
        //Objects->requireNonNull(pattern, "pattern");
//Objects->requireNonNull(noOffsetText, "noOffsetText");
        $this->type = $this->checkPattern($pattern);
        $this->noOffsetText = $noOffsetText;
    }

    private function checkPattern($pattern)
    {
        $i = array_search($pattern, self::$PATTERNS);
        if ($i === false)
            throw new RuntimeException("Invalid zone offset pattern: " . $pattern);
        return $i;
    }

    public function format(DateTimePrintContext $context, &$buf)
    {
        $offsetSecs = $context->getValueField(ChronoField::OFFSET_SECONDS());
        if ($offsetSecs === null) {
            return false;
        }

        $totalSecs = $offsetSecs;
        if ($totalSecs === 0) {
            $buf .= $this->noOffsetText;
        } else {
            $absHours = Math::abs(($totalSecs / 3600) % 100);  // anything larger than 99 silently dropped
            $absMinutes = Math::abs(($totalSecs / 60) % 60);
            $absSeconds = Math::abs($totalSecs % 60);
            $bufPos = strlen($buf);
            $output = $absHours;
            $buf .= ($totalSecs < 0 ? "-" : "+")
                . Math::div($absHours, 10) . ($absHours % 10);
            if ($this->type >= 3 || ($this->type >= 1 && $absMinutes > 0)) {
                $buf .= (($this->type % 2) === 0 ? ":" : "")
                    . Math::div($absMinutes, 10) . ($absMinutes % 10);
                $output .= $absMinutes;
                if ($this->type >= 7 || ($this->type >= 5 && $absSeconds > 0)) {
                    $buf .= (($this->type % 2) === 0 ? ":" : "")
                        . Math::div($absSeconds, 10) . ($absSeconds % 10);
                    $output .= $absSeconds;
                }
            }
            if ($output === 0) {
                $buf = substr($buf, 0, $bufPos);
                $buf .= $this->noOffsetText;
            }
        }
        return true;
    }

    public function parse(DateTimeParseContext $context, $text, $position)
    {
        $length = strlen($text);
        $noOffsetLen = strlen($this->noOffsetText);
        if ($noOffsetLen == 0) {
            if ($position === $length) {
                return $context->setParsedField(ChronoField::OFFSET_SECONDS(), 0, $position, $position);
            }
        } else {
            if ($position === $length) {
                return ~$position;
            }

            if ($context->subSequenceEquals($text, $position, $this->noOffsetText, 0, $noOffsetLen)) {
                return $context->setParsedField(ChronoField::OFFSET_SECONDS(), 0, $position, $position + $noOffsetLen);
            }
        }

// parse normal plus/minus offset
        $sign = $text[$position];  // IOOBE if invalid position
        if ($sign === '+' || $sign === '-') {
            // starts
            $negative = ($sign === '-' ? -1 : 1);
            $array = [0, 0, 0, 0];
            $array[0] = $position + 1;
            if (($this->parseNumber($array, 1, $text, true) ||
                    $this->parseNumber($array, 2, $text, $this->type >= 3) ||
                    $this->parseNumber($array, 3, $text, false)) === false
            ) {
                // success
                $offsetSecs = $negative * ($array[1] * 3600 + $array[2] * 60 + $array[3]);
                return $context->setParsedField(ChronoField::OFFSET_SECONDS(), $offsetSecs, $position, $array[0]);
            }
        }
        // handle special case of empty no offset text
        if ($noOffsetLen === 0) {
            return $context->setParsedField(ChronoField::OFFSET_SECONDS(), 0, $position, $position + $noOffsetLen);
        }
        return ~$position;
    }

    /**
     * Parse a two digit zero-prefixed number.
     *
     * @param array $array the array of parsed data, 0=pos,1=hours,2=mins,3=secs, not null
     * @param int $arrayIndex the index to parse the value into
     * @param string $parseText the offset ID, not null
     * @param bool $required whether this number is required
     * @return bool true if an error occurred
     */
    private function parseNumber(array &$array, $arrayIndex, $parseText, $required)
    {
        if (($this->type + 3) / 2 < $arrayIndex) {
            return false;  // ignore seconds/minutes
        }
        $pos = $array[0];
        if (($this->type % 2) === 0 && $arrayIndex > 1) {
            if ($pos + 1 > strlen($parseText) || $parseText[$pos] !== ':') {
                return $required;
            }
            $pos++;
        }
        if ($pos + 2 > strlen($parseText)) {
            return $required;
        }
        $ch1 = ord($parseText[$pos++]);
        $ch2 = ord($parseText[$pos++]);
        if ($ch1 < ord('0') || $ch1 > ord('9') || $ch2 < ord('0') || $ch2 > ord('9')) {
            return $required;
        }
        $value = ($ch1 - 48) * 10 + ($ch2 - 48);
        if ($value < 0 || $value > 59) {
            return $required;
        }
        $array[$arrayIndex] = $value;
        $array[0] = $pos;
        return false;
    }

    public function __toString()
    {
        $converted = str_replace("'", "''", $this->noOffsetText);
        return "Offset(" . self::$PATTERNS[$this->type] . ",'" . $converted . "')";
    }
}