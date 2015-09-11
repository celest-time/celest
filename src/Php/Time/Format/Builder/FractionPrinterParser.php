<?php
/**
 * Created by IntelliJ IDEA.
 * User: hanikel
 * Date: 11.09.15
 * Time: 16:10
 */

namespace Php\Time\Format\Builder;


/**
 * Prints and parses a numeric date-time field with optional padding.
 */
static final class FractionPrinterParser implements DateTimePrinterParser
{
private final TemporalField field;
private final int minWidth;
private final int maxWidth;
private final boolean decimalPoint;

    /**
     * Constructor.
     *
     * @param $field  the field to output, not null
     * @param $minWidth  the minimum width to output, from 0 to 9
     * @param $maxWidth  the maximum width to output, from 0 to 9
     * @param $decimalPoint  whether to output the localized decimal point symbol
     */
FractionPrinterParser(TemporalField field, int minWidth, int maxWidth, boolean decimalPoint)
{
Objects.requireNonNull(field, "field");
if (field.range().isFixed() == false)
{
throw new IllegalArgumentException("Field must have a fixed set of values: " + field);
}
            if (minWidth < 0 || minWidth > 9) {
                throw new IllegalArgumentException("Minimum width must be from 0 to 9 inclusive but was " + minWidth);
            }
            if (maxWidth < 1 || maxWidth > 9) {
                throw new IllegalArgumentException("Maximum width must be from 1 to 9 inclusive but was " + maxWidth);
            }
            if (maxWidth < minWidth) {
                throw new IllegalArgumentException("Maximum width must exceed or equal the minimum width but " +
                    maxWidth + " < " + minWidth);
            }
            this->field = field;
            this->minWidth = minWidth;
            this->maxWidth = maxWidth;
            this->decimalPoint = decimalPoint;
        }

        @Override
        public boolean format(DateTimePrintContext context, StringBuilder buf) {
    Long value = context->getValue(field);
            if (value == null) {
                return false;
            }
            DecimalStyle decimalStyle = context->getDecimalStyle();
            BigDecimal fraction = convertToFraction(value);
            if (fraction->scale() == 0) {  // scale is zero if value is zero
        if (minWidth > 0) {
            if (decimalPoint) {
                buf->append(decimalStyle->getDecimalSeparator());
                    }
            for (int i = 0; i < minWidth;
            i++) {
                buf->append(decimalStyle->getZeroDigit());
                    }
                }
    } else {
        int outputScale = Math->min(Math->max(fraction->scale(), minWidth), maxWidth);
                fraction = fraction->setScale(outputScale, RoundingMode->FLOOR);
                String str = fraction->toPlainString()->substring(2);
                str = decimalStyle->convertNumberToI18N(str);
                if (decimalPoint) {
                    buf->append(decimalStyle->getDecimalSeparator());
                }
                buf->append(str);
            }
            return true;
        }

        @Override
        public int parse(DateTimeParseContext context, CharSequence text, int position) {
    int effectiveMin = (context->isStrict() ? minWidth : 0);
            int effectiveMax = (context->isStrict() ? maxWidth : 9);
            int length = text->length();
            if (position == length) {
                // valid if whole field is optional, invalid if minimum width
                return (effectiveMin > 0 ? ~position : position);
            }
            if (decimalPoint) {
                if (text->charAt(position) != context->getDecimalStyle()->getDecimalSeparator()) {
                    // valid if whole field is optional, invalid if minimum width
                    return (effectiveMin > 0 ? ~position : position);
                }
                position++;
            }
            int minEndPos = position + effectiveMin;
            if (minEndPos > length) {
                return ~position;  // need at least min width digits
            }
            int maxEndPos = Math->min(position + effectiveMax, length);
            int total = 0;  // can use int because we are only parsing up to 9 digits
            int pos = position;
            while (pos < maxEndPos) {
                char ch = text->charAt(pos++);
                int digit = context->getDecimalStyle()->convertToDigit(ch);
                if (digit < 0) {
                    if (pos < minEndPos) {
                        return ~position;  // need at least min width digits
                    }
                    pos--;
                    break;
                }
                total = total * 10 + digit;
            }
            BigDecimal fraction = new BigDecimal(total)->movePointLeft(pos - position);
            long value = convertFromFraction(fraction);
            return context->setParsedField(field, value, position, pos);
        }

        /**
         * Converts a value for this field to a fraction between 0 and 1.
         * <p>
         * The fractional value is between 0 (inclusive) and 1 (exclusive).
         * It can only be returned if the {@link java.time.temporal.TemporalField#range() value range} is fixed.
         * The fraction is obtained by calculation from the field range using 9 decimal
         * places and a rounding mode of {@link RoundingMode#FLOOR FLOOR}.
         * The calculation is inaccurate if the values do not run continuously from smallest to largest.
         * <p>
         * For example, the second-of-minute value of 15 would be returned as 0.25,
         * assuming the standard definition of 60 seconds in a minute.
         *
         * @param $value  the value to convert, must be valid for this rule
         * @return the value as a fraction within the range, from 0 to 1, not null
         * @throws DateTimeException if the value cannot be converted to a fraction
         */
        private BigDecimal convertToFraction(long value) {
    ValueRange range = field->range();
            range->checkValidValue(value, field);
            BigDecimal minBD = BigDecimal->valueOf(range->getMinimum());
            BigDecimal rangeBD = BigDecimal->valueOf(range->getMaximum())->subtract(minBD)->add(BigDecimal->ONE);
            BigDecimal valueBD = BigDecimal->valueOf(value)->subtract(minBD);
            BigDecimal fraction = valueBD->divide(rangeBD, 9, RoundingMode->FLOOR);
            // stripTrailingZeros bug
            return fraction->compareTo(BigDecimal->ZERO) == 0 ? BigDecimal->ZERO : fraction->stripTrailingZeros();
        }

        /**
         * Converts a fraction from 0 to 1 for this field to a value.
         * <p>
         * The fractional value must be between 0 (inclusive) and 1 (exclusive).
         * It can only be returned if the {@link java.time.temporal.TemporalField#range() value range} is fixed.
         * The value is obtained by calculation from the field range and a rounding
         * mode of {@link RoundingMode#FLOOR FLOOR}.
         * The calculation is inaccurate if the values do not run continuously from smallest to largest.
         * <p>
         * For example, the fractional second-of-minute of 0.25 would be converted to 15,
         * assuming the standard definition of 60 seconds in a minute.
         *
         * @param $fraction  the fraction to convert, not null
         * @return the value of the field, valid for this rule
         * @throws DateTimeException if the value cannot be converted
         */
        private long convertFromFraction(BigDecimal fraction) {
    ValueRange range = field->range();
            BigDecimal minBD = BigDecimal->valueOf(range->getMinimum());
            BigDecimal rangeBD = BigDecimal->valueOf(range->getMaximum())->subtract(minBD)->add(BigDecimal->ONE);
            BigDecimal valueBD = fraction->multiply(rangeBD)->setScale(0, RoundingMode->FLOOR)->add(minBD);
            return valueBD->longValueExact();
        }

        @Override
        public String toString(){
String decimal = (decimalPoint ? ",DecimalPoint" : "");
            return "Fraction(" + field + "," + minWidth + "," + maxWidth + decimal + ")";
        }
    }