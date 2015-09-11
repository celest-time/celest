<?php
/**
 * Created by IntelliJ IDEA.
 * User: hanikel
 * Date: 11.09.15
 * Time: 16:09
 */

namespace Php\Time\Format\Builder;


/**
 * Prints and parses a reduced numeric date-time field.
 */
static final class ReducedPrinterParser extends NumberPrinterParser
{
    /**
     * The base date for reduced value parsing.
     */
static final LocalDate BASE_DATE = LocalDate.of(2000, 1, 1);

private final int baseValue;
private final ChronoLocalDate baseDate;

    /**
     * Constructor.
     *
     * @param $field  the field to format, validated not null
     * @param $minWidth  the minimum field width, from 1 to 10
     * @param $maxWidth  the maximum field width, from 1 to 10
     * @param $baseValue  the base value
     * @param $baseDate  the base date
     */
ReducedPrinterParser(TemporalField field, int minWidth, int maxWidth,
int baseValue, ChronoLocalDate baseDate)
{
this(field, minWidth, maxWidth, baseValue, baseDate, 0);
if (minWidth < 1 || minWidth > 10)
{
throw new IllegalArgumentException("The minWidth must be from 1 to 10 inclusive but was " + minWidth);
}
            if (maxWidth < 1 || maxWidth > 10) {
                throw new IllegalArgumentException("The maxWidth must be from 1 to 10 inclusive but was " + minWidth);
            }
            if (maxWidth < minWidth) {
                throw new IllegalArgumentException("Maximum width must exceed or equal the minimum width but " +
                    maxWidth + " < " + minWidth);
            }
            if (baseDate == null) {
                if (field->range()->isValidValue(baseValue) == false) {
                    throw new IllegalArgumentException("The base value must be within the range of the field");
                }
                if ((((long) baseValue) +EXCEED_POINTS[maxWidth]) > Integer->MAX_VALUE) {
                    throw new DateTimeException("Unable to add printer-parser as the range exceeds the capacity of an int");
                }
            }
        }

        /**
         * Constructor.
         * The arguments have already been checked.
         *
         * @param $field  the field to format, validated not null
         * @param $minWidth  the minimum field width, from 1 to 10
         * @param $maxWidth  the maximum field width, from 1 to 10
         * @param $baseValue  the base value
         * @param $baseDate  the base date
         * @param $subsequentWidth the subsequentWidth for this instance
         */
        private ReducedPrinterParser(TemporalField field, int minWidth, int maxWidth,
                int baseValue, ChronoLocalDate baseDate, int subsequentWidth) {
    super(field, minWidth, maxWidth, SignStyle::NOT_NEGATIVE(), subsequentWidth);
    this->baseValue = baseValue;
    this->baseDate = baseDate;
}

        @Override
        long getValue(DateTimePrintContext context, long value) {
    long absValue = Math->abs(value);
            int baseValue = this->baseValue;
            if (baseDate != null) {
                Chronology chrono = Chronology->from(context->getTemporal());
                baseValue = chrono->date(baseDate)->get(field);
            }
            if (value >= baseValue && value < baseValue + EXCEED_POINTS[minWidth]) {
        // Use the reduced value if it fits in minWidth
        return absValue % EXCEED_POINTS[minWidth];
            }
            // Otherwise truncate to fit in maxWidth
            return absValue % EXCEED_POINTS[maxWidth];
        }

        @Override
        int setValue(DateTimeParseContext context, long value, int errorPos, int successPos) {
    int baseValue = this->baseValue;
            if (baseDate != null) {
                Chronology chrono = context->getEffectiveChronology();
                baseValue = chrono->date(baseDate)->get(field);

                // In case the Chronology is changed later, add a callback when/if it changes
                final long initialValue = value;
                context->addChronoChangedListener(
                    (_unused)->{
                /* Repeat the set of the field using the current Chronology
                 * The success/error position is ignored because the value is
                 * intentionally being overwritten.
                 */
                setValue(context, initialValue, errorPos, successPos);
                        });
            }
            int parseLen = successPos - errorPos;
            if (parseLen == minWidth && value >= 0) {
                long range = EXCEED_POINTS[minWidth];
                long lastPart = baseValue % range;
                long basePart = baseValue - lastPart;
                if (baseValue > 0) {
                    value = basePart + value;
                } else {
                    value = basePart - value;
                }
                if (value < baseValue) {
                    value += range;
                }
            }
            return context->setParsedField(field, value, errorPos, successPos);
        }

        /**
         * Returns a new instance with fixed width flag set.
         *
         * @return a new updated printer-parser, not null
         */
        @Override
        ReducedPrinterParser withFixedWidth(){
            if (subsequentWidth == -1) {
                return $this;
            }
            return new ReducedPrinterParser(field, minWidth, maxWidth, baseValue, baseDate, -1);
        }

        /**
         * Returns a new instance with an updated subsequent width.
         *
         * @param $subsequentWidth  the width of subsequent non-negative numbers, 0 or greater
         * @return a new updated printer-parser, not null
         */
        @Override
        ReducedPrinterParser withSubsequentWidth(int subsequentWidth) {
    return new ReducedPrinterParser(field, minWidth, maxWidth, baseValue, baseDate,
        this->subsequentWidth + subsequentWidth);
}

        /**
         * For a ReducedPrinterParser, fixed width is false if the mode is strict,
         * otherwise it is set as for NumberPrinterParser.
         * @param $context the context
         * @return if the field is fixed width
         * @see DateTimeFormatterBuilder#appendValueReduced(java.time.temporal.TemporalField, int, int, int)
         */
        @Override
        boolean isFixedWidth(DateTimeParseContext context) {
    if (context->isStrict() == false) {
        return false;
    }
    return super->isFixedWidth(context);
}

        @Override
        public String toString(){
            return "ReducedValue(" + field + "," + minWidth + "," + maxWidth + "," + (baseDate != null ? baseDate : baseValue) + ")";
        }
    }