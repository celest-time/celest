<?php

namespace Php\Time\Format\Builder;

use Php\Time\Helper\Long;
use Php\Time\Temporal\TemporalField;
use Php\Time\Format\SignStyle;
use Php\Time\Format\DateTimePrintContext;
use Php\Time\DateTimeException;
use Php\Time\Helper\Math;
use Php\Time\Format\DateTimeParseContext;

/**
 * Prints and parses a numeric date-time field with optional padding.
 */
class NumberPrinterParser implements DateTimePrinterParser
{

    /**
     * Array of 10 to the power of n.
     * @var int[]
     */
    static $EXCEED_POINTS =
        [
            0,
            10,
            100,
            1000,
            10000,
            100000,
            1000000,
            10000000,
            100000000,
            1000000000,
            10000000000,
        ];

    /** @var TemporalField */
    public $field;
    /** @var int */
    public $minWidth;
    /** @var int */
    public $maxWidth;
    /** @var SignStyle */
    public $signStyle;
    /** @var int */
    public $subsequentWidth;

    /**
     * Constructor.
     *
     * @param TemporalField $field the field to format, not null
     * @param int $minWidth the minimum field width, from 1 to 19
     * @param int $maxWidth the maximum field width, from minWidth to 19
     * @param SignStyle $signStyle the positive/negative sign style, not null
     * @param int $subsequentWidth the width of subsequent non-negative numbers, 0 or greater,
     *  -1 if fixed width due to active adjacent parsing
     */
    public function __construct(TemporalField $field, $minWidth, $maxWidth, SignStyle $signStyle, $subsequentWidth = 0)
    {
        // validated by caller
        $this->field = $field;
        $this->minWidth = $minWidth;
        $this->maxWidth = $maxWidth;
        $this->signStyle = $signStyle;
        $this->subsequentWidth = $subsequentWidth;
    }

    /**
     * Returns a new instance with fixed width flag set.
     *
     * @return NumberPrinterParser a new updated printer-parser, not null
     */
    public function withFixedWidth()
    {
        if ($this->subsequentWidth === -1) {
            return $this;
        }

        return new NumberPrinterParser($this->field, $this->minWidth, $this->maxWidth, $this->signStyle, -1);
    }

    /**
     * Returns a new instance with an updated subsequent width.
     *
     * @param int $subsequentWidth the width of subsequent non-negative numbers, 0 or greater
     * @return NumberPrinterParser a new updated printer-parser, not null
     */
    public function withSubsequentWidth($subsequentWidth)
    {
        return new NumberPrinterParser($this->field, $this->minWidth, $this->maxWidth, $this->signStyle, $this->subsequentWidth + $subsequentWidth);
    }

    public function format(DateTimePrintContext $context, &$buf)
    {
        $valueLong = $context->getValueField($this->field);
        if ($valueLong === null) {
            return false;
        }

        $value = $this->getValue($context, $valueLong);
        $decimalStyle = $context->getDecimalStyle();
        $str = ($value === Long::MIN_VALUE ? "9223372036854775808" : strval(Math::abs($value)));
        if (strlen($str) > $this->maxWidth) {
            throw new DateTimeException("Field " . $this->field .
                " cannot be printed as the value " . $value .
                " exceeds the maximum print width of " . $this->maxWidth);
        }
        $str = $decimalStyle->convertNumberToI18N($str);

        if ($value >= 0) {
            switch ($this->signStyle) {
                case SignStyle::EXCEEDS_PAD():
                    if ($this->minWidth < 19 && $value >= self::$EXCEED_POINTS[$this->minWidth]) {
                        $buf .= $decimalStyle->getPositiveSign();
                    }
                    break;
                case SignStyle::ALWAYS():
                    $buf .= $decimalStyle->getPositiveSign();
                    break;
            }
        } else {
            switch ($this->signStyle) {
                case SignStyle::NORMAL():
                case SignStyle::EXCEEDS_PAD():
                case SignStyle::ALWAYS():
                    $buf .= $decimalStyle->getNegativeSign();
                    break;
                case SignStyle::NOT_NEGATIVE():
                    throw new DateTimeException("Field " . $this->field .
                        " cannot be printed as the value " . $value .
                        " cannot be negative according to the SignStyle");
            }
        }
        for ($i = 0; $i < $this->minWidth - strlen($str); $i++) {
            $buf .= $decimalStyle->getZeroDigit();
        }
        $buf .= $str;
        return true;
    }

    /**
     * Gets the value to output.
     *
     * @param DateTimePrintContext $context  the context
     * @param int $value the value of the field, not null
     * @return int the value
     */
    public function getValue(DateTimePrintContext $context, $value)
    {
        return $value;
    }

    /**
     * For NumberPrinterParser, the width is fixed depending on the
     * minWidth, maxWidth, signStyle and whether subsequent fields are fixed.
     * @param DateTimeParseContext $context the context
     * @return true if the field is fixed width
     * @see DateTimeFormatterBuilder#appendValue(java.time.temporal.TemporalField, int)
     */
    public function isFixedWidth(DateTimeParseContext $context)
    {
        return $this->subsequentWidth === -1 ||
        ($this->subsequentWidth > 0 && $this->minWidth === $this->maxWidth && $this->signStyle == SignStyle::NOT_NEGATIVE());
    }

    public function parse(DateTimeParseContext $context, $text, $position)
    {
        $length = strlen($text);
        if ($position === $length) {
            return ~$position;
        }

        if($position < 0 || $position >= $length) throw new \OutOfRangeException();
        $sign = $text[$position];
        $negative = false;
        $positive = false;
        if ($sign === $context->getDecimalStyle()->getPositiveSign()) {
            if ($this->signStyle->parse(true, $context->isStrict(), $this->minWidth === $this->maxWidth) === false) {
                return ~$position;
            }
            $positive = true;
            $position++;
        } else if ($sign === $context->getDecimalStyle()->getNegativeSign()) {
            if ($this->signStyle->parse(false, $context->isStrict(), $this->minWidth === $this->maxWidth) === false) {
                return ~$position;
            }
            $negative = true;
            $position++;
        } else {
            if ($this->signStyle == SignStyle::ALWAYS() && $context->isStrict()) {
                return ~$position;
            }
        }
        $effMinWidth = ($context->isStrict() || $this->isFixedWidth($context) ? $this->minWidth : 1);
        $minEndPos = $position + $effMinWidth;
        if ($minEndPos > $length) {
            return ~$position;
        }
        $effMaxWidth = ($context->isStrict() || $this->isFixedWidth($context) ? $this->maxWidth : 9) + Math::max($this->subsequentWidth, 0);
        $total = 0;
        $totalBig = null;
        $pos = $position;
        for ($pass = 0; $pass < 2; $pass++) {
            $maxEndPos = Math::min($pos + $effMaxWidth, $length);
            while ($pos < $maxEndPos) {
                $ch = $text[$pos++];
                $digit = $context->getDecimalStyle()->convertToDigit($ch);
                if ($digit < 0) {
                    $pos--;
                    if ($pos < $minEndPos) {
                        return ~$position;  // need at least min width digits
                    }
                    break;
                }
                if (($pos - $position) > 18) {
                    if ($totalBig === null) {
                        $totalBig = \gmp_init($total);
                    }
                    $totalBig = \gmp_add(\gmp_mul($totalBig, "10"), \gmp_init($digit));
                } else {
                    $total = $total * 10 + $digit;
                }
            }
            if ($this->subsequentWidth > 0 && $pass === 0) {
                // re-parse now we know the correct width
                $parseLen = $pos - $position;
                $effMaxWidth = Math::max($effMinWidth, $parseLen - $this->subsequentWidth);
                $pos = $position;
                $total = 0;
                $totalBig = null;
            } else {
                break;
            }
        }
        if ($negative) {
            if ($totalBig !== null) {
                if (\gmp_cmp($totalBig, "0") === 0 && $context->isStrict()) {
                    return ~($position - 1);  // minus zero not allowed
                }
                $totalBig = \gmp_neg($totalBig);
            } else {
                if ($total === 0 && $context->isStrict()) {
                    return ~($position - 1);  // minus zero not allowed
                }
                $total = -$total;
            }
        } else if ($this->signStyle == SignStyle::EXCEEDS_PAD() && $context->isStrict()) {
            $parseLen = $pos - $position;
            if ($positive) {
                if ($parseLen <= $this->minWidth) {
                    return ~($position - 1);  // '+' only parsed if minWidth exceeded
                }
            } else {
                if ($parseLen > $this->minWidth) {
                    return ~$position;  // '+' must be parsed if minWidth exceeded
                }
            }
        }
        if ($totalBig !== null) {
            if (gmp_cmp($totalBig, "-9223372036854775808") < 0 || gmp_cmp($totalBig, "9223372036854775807") > 0) {
                // overflow, parse 1 less digit
            $totalBig = gmp_div($totalBig, "10");
            $pos--;
        }
            return $this->setValue($context, gmp_intval($totalBig), $position, $pos);
        }
        return $this->setValue($context, $total, $position, $pos);
    }

    /**
     * Stores the value.
     *
     * @param DateTimeParseContext $context the context to store into, not null
     * @param int $value the value
     * @param int $errorPos the position of the field being parsed
     * @param int $successPos the position after the field being parsed
     * @return int the new position
     */
    public function setValue(DateTimeParseContext $context, $value, $errorPos, $successPos)
    {
        return $context->setParsedField($this->field, $value, $errorPos, $successPos);
    }

    public function __toString()
    {
        if ($this->minWidth == 1 && $this->maxWidth == 19 && $this->signStyle == SignStyle::NORMAL()) {
            return "Value(" . $this->field . ")";
        }

        if ($this->minWidth == $this->maxWidth && $this->signStyle == SignStyle::NOT_NEGATIVE()) {
            return "Value(" . $this->field . "," . $this->minWidth . ")";
        }
        return "Value(" . $this->field . "," . $this->minWidth . "," . $this->maxWidth . "," . $this->signStyle . ")";
    }
}
