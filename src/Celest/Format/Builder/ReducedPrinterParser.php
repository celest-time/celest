<?php

namespace Celest\Format\Builder;

use Celest\Chrono\AbstractChronology;
use Celest\Chrono\ChronoLocalDate;
use Celest\DateTimeException;
use Celest\Format\DateTimeParseContext;
use Celest\Format\DateTimePrintContext;
use Celest\Format\SignStyle;
use Celest\Helper\Integer;
use Celest\Helper\Math;
use Celest\IllegalArgumentException;
use Celest\LocalDate;
use Celest\Temporal\TemporalField;

/**
 * Prints and parses a reduced numeric date-time field.
 */
final class ReducedPrinterParser extends NumberPrinterParser
{

    /** @var LocalDate */
    private static $BASE_DATE;

    /**
     * The base date for reduced value parsing.
     * @return LocalDate
     */
    public static function BASE_DATE()
    {
        if (self::$BASE_DATE === null) {
            self::$BASE_DATE = LocalDate::of(2000, 1, 1);
        }
        return self::$BASE_DATE;
    }


    /** @var int */
    private $baseValue;
    /** @var ChronoLocalDate */
    private $baseDate;

    /**
     * Constructor.
     *
     * @param TemporalField $field the field to format, validated not null
     * @param int $minWidth the minimum field width, from 1 to 10
     * @param int $maxWidth the maximum field width, from 1 to 10
     * @param int $baseValue the base value
     * @param ChronoLocalDate|null $baseDate the base date
     * @param int $subsequentWidth the subsequentWidth for this instance
     * @throws DateTimeException
     * @throws IllegalArgumentException
     */
    public function __construct(TemporalField $field, $minWidth, $maxWidth,
                                $baseValue, $baseDate, $subsequentWidth = 0)
    {
        parent::__construct($field, $minWidth, $maxWidth, SignStyle::NOT_NEGATIVE(), $subsequentWidth);


        $this->baseValue = $baseValue;
        $this->baseDate = $baseDate;

        if ($minWidth < 1 || $minWidth > 10) {
            throw new IllegalArgumentException("The minWidth must be from 1 to 10 inclusive but was " . $minWidth);
        }

        if ($maxWidth < 1 || $maxWidth > 10) {
            throw new IllegalArgumentException("The maxWidth must be from 1 to 10 inclusive but was " . $minWidth);
        }
        if ($maxWidth < $minWidth) {
            throw new IllegalArgumentException("Maximum width must exceed or equal the minimum width but " .
                $maxWidth . " < " . $minWidth);
        }
        if ($baseDate === null) {
            if ($field->range()->isValidValue($baseValue) === false) {
                throw new IllegalArgumentException("The base value must be within the range of the field");
            }
            if ((($baseValue) + self::$EXCEED_POINTS[$maxWidth]) > Integer::MAX_VALUE) {
                throw new DateTimeException("Unable to add printer-parser as the range exceeds the capacity of an int");
            }
        }
    }

    public function getValue(DateTimePrintContext $context, $value)
    {
        $absValue = Math::abs($value);
        $baseValue = $this->baseValue;
        if ($this->baseDate != null) {
            $chrono = AbstractChronology::from($context->getTemporal());
            $baseValue = $chrono->dateFrom($this->baseDate)->get($this->field);
        }

        if ($value >= $baseValue && $value < $baseValue + self::$EXCEED_POINTS[$this->minWidth]) {
            // Use the reduced value if it fits in minWidth
            return $absValue % self::$EXCEED_POINTS[$this->minWidth];
        }
        // Otherwise truncate to fit in maxWidth
        return $absValue % self::$EXCEED_POINTS[$this->maxWidth];
    }

    public function setValue(DateTimeParseContext $context, $value, $errorPos, $successPos)
    {
        $baseValue = $this->baseValue;
        if ($this->baseDate !== null) {
            $chrono = $context->getEffectiveChronology();
            $baseValue = $chrono->dateFrom($this->baseDate)->get($this->field);

            // In case the Chronology is changed later, add a callback when/if it changes
            $initialValue = $value;
            $context->addChronoChangedListener(
                function ($_) use ($context, $initialValue, $errorPos, $successPos) {
                    /* Repeat the set of the field using the current Chronology
                     * The success/error position is ignored because the value is
                     * intentionally being overwritten.
                     */
                    $this->setValue($context, $initialValue, $errorPos, $successPos);
                });
        }
        $parseLen = $successPos - $errorPos;
        if ($parseLen == $this->minWidth && $value >= 0) {
            $range = self::$EXCEED_POINTS[$this->minWidth];
            $lastPart = $baseValue % $range;
            $basePart = $baseValue - $lastPart;
            if ($baseValue > 0) {
                $value = $basePart + $value;
            } else {
                $value = $basePart - $value;
            }
            if ($value < $baseValue) {
                $value += $range;
            }
        }
        return $context->setParsedField($this->field, $value, $errorPos, $successPos);
    }

    /**
     * Returns a new instance with fixed width flag set.
     *
     * @return ReducedPrinterParser a new updated printer-parser, not null
     */
    public function withFixedWidth()
    {
        if ($this->subsequentWidth == -1) {
            return $this;
        }

        return new ReducedPrinterParser($this->field, $this->minWidth, $this->maxWidth, $this->baseValue, $this->baseDate, -1);
    }

    /**
     * Returns a new instance with an updated subsequent width.
     *
     * @param int $subsequentWidth the width of subsequent non-negative numbers, 0 or greater
     * @return ReducedPrinterParser a new updated printer-parser, not null
     */
    public function withSubsequentWidth($subsequentWidth)
    {
        return new ReducedPrinterParser($this->field, $this->minWidth, $this->maxWidth, $this->baseValue, $this->baseDate,
            $this->subsequentWidth + $subsequentWidth);
    }

    /**
     * For a ReducedPrinterParser, fixed width is false if the mode is strict,
     * otherwise it is set as for NumberPrinterParser.
     * @param DateTimeParseContext $context the context
     * @return bool if the field is fixed width
     * @see DateTimeFormatterBuilder#appendValueReduced(java.time.temporal.TemporalField, int, int, int)
     */
    public function isFixedWidth(DateTimeParseContext $context)
    {
        if ($context->isStrict() == false) {
            return false;
        }

        return parent::isFixedWidth($context);
    }

    public function __toString()
    {
        return "ReducedValue(" . $this->field . "," . $this->minWidth . "," . $this->maxWidth . "," . ($this->baseDate != null ? $this->baseDate : $this->baseValue) . ")";
    }
}