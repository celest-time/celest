<?php
/**
 * Created by IntelliJ IDEA.
 * User: hanikel
 * Date: 11.09.15
 * Time: 16:09
 */

namespace Php\Time\Format\Builder;


/**
 * Prints and parses a numeric date-time field with optional padding.
 */
static class NumberPrinterParser implements DateTimePrinterParser
{

    /**
     * Array of 10 to the power of n.
     */
static final long[] EXCEED_POINTS = new long[]
{
0L,
10L,
100L,
1000L,
10000L,
100000L,
1000000L,
10000000L,
100000000L,
1000000000L,
10000000000L,
};

        final TemporalField field;
        final int minWidth;
        final int maxWidth;
        private final SignStyle signStyle;
        final int subsequentWidth;

        /**
         * Constructor.
         *
         * @param $field  the field to format, not null
         * @param $minWidth  the minimum field width, from 1 to 19
         * @param $maxWidth  the maximum field width, from minWidth to 19
         * @param $signStyle  the positive/negative sign style, not null
         */
        NumberPrinterParser(TemporalField field, int minWidth, int maxWidth, SignStyle signStyle) {
    // validated by caller
    this->field = field;
    this->minWidth = minWidth;
    this->maxWidth = maxWidth;
    this->signStyle = signStyle;
    this->subsequentWidth = 0;
}

        /**
         * Constructor.
         *
         * @param $field  the field to format, not null
         * @param $minWidth  the minimum field width, from 1 to 19
         * @param $maxWidth  the maximum field width, from minWidth to 19
         * @param $signStyle  the positive/negative sign style, not null
         * @param $subsequentWidth  the width of subsequent non-negative numbers, 0 or greater,
         *  -1 if fixed width due to active adjacent parsing
         */
        protected NumberPrinterParser(TemporalField field, int minWidth, int maxWidth, SignStyle signStyle, int subsequentWidth) {
    // validated by caller
    this->field = field;
    this->minWidth = minWidth;
    this->maxWidth = maxWidth;
    this->signStyle = signStyle;
    this->subsequentWidth = subsequentWidth;
}

        /**
         * Returns a new instance with fixed width flag set.
         *
         * @return a new updated printer-parser, not null
         */
        NumberPrinterParser withFixedWidth(){
            if (subsequentWidth == -1) {
                return $this;
            }
            return new NumberPrinterParser(field, minWidth, maxWidth, signStyle, -1);
        }

        /**
         * Returns a new instance with an updated subsequent width.
         *
         * @param $subsequentWidth  the width of subsequent non-negative numbers, 0 or greater
         * @return a new updated printer-parser, not null
         */
        NumberPrinterParser withSubsequentWidth(int subsequentWidth) {
    return new NumberPrinterParser(field, minWidth, maxWidth, signStyle, this->subsequentWidth + subsequentWidth);
}

        @Override
        public boolean format(DateTimePrintContext context, StringBuilder buf) {
    Long valueLong = context->getValue(field);
            if (valueLong == null) {
                return false;
            }
            long value = getValue(context, valueLong);
            DecimalStyle decimalStyle = context->getDecimalStyle();
            String str = (value == Long->MIN_VALUE ? "9223372036854775808" : Long->toString(Math->abs(value)));
            if (str->length() > maxWidth) {
        throw new DateTimeException("Field " + field +
            " cannot be printed as the value " + value +
            " exceeds the maximum print width of " + maxWidth);
    }
            str = decimalStyle->convertNumberToI18N(str);

            if (value >= 0) {
                switch (signStyle) {
                    case EXCEEDS_PAD:
                        if (minWidth < 19 && value >= EXCEED_POINTS[minWidth]) {
                        buf->append(decimalStyle->getPositiveSign());
                    }
                        break;
                    case ALWAYS:
                        buf->append(decimalStyle->getPositiveSign());
                        break;
                }
            } else {
                switch (signStyle) {
                    case NORMAL:
                    case EXCEEDS_PAD:
                    case ALWAYS:
                        buf->append(decimalStyle->getNegativeSign());
                        break;
                    case NOT_NEGATIVE:
                        throw new DateTimeException("Field " + field +
                            " cannot be printed as the value " + value +
                            " cannot be negative according to the SignStyle");
                }
            }
            for (int i = 0; i < minWidth - str->length(); i++) {
        buf->append(decimalStyle->getZeroDigit());
    }
            buf->append(str);
            return true;
        }

        /**
         * Gets the value to output.
         *
         * @param $context  the context
         * @param $value  the value of the field, not null
         * @return the value
         */
        long getValue(DateTimePrintContext context, long value) {
    return value;
}

        /**
         * For NumberPrinterParser, the width is fixed depending on the
         * minWidth, maxWidth, signStyle and whether subsequent fields are fixed.
         * @param $context the context
         * @return true if the field is fixed width
         * @see DateTimeFormatterBuilder#appendValue(java.time.temporal.TemporalField, int)
         */
        boolean isFixedWidth(DateTimeParseContext context) {
    return subsequentWidth == -1 ||
    (subsequentWidth > 0 && minWidth == maxWidth && signStyle == SignStyle::NOT_NEGATIVE());
}

        @Override
        public int parse(DateTimeParseContext context, CharSequence text, int position) {
    int length = text->length();
            if (position == length) {
                return ~position;
            }
            char sign = text->charAt(position);  // IOOBE if invalid position
            boolean negative = false;
            boolean positive = false;
            if (sign == context->getDecimalStyle()->getPositiveSign()) {
        if (signStyle->parse(true, context->isStrict(), minWidth == maxWidth) == false) {
            return ~position;
        }
                positive = true;
                position++;
            } else if (sign == context->getDecimalStyle()->getNegativeSign()) {
        if (signStyle->parse(false, context->isStrict(), minWidth == maxWidth) == false) {
            return ~position;
        }
                negative = true;
                position++;
            } else {
        if (signStyle == SignStyle::ALWAYS() && context->isStrict()) {
            return ~position;
        }
            }
            int effMinWidth = (context->isStrict() || isFixedWidth(context) ? minWidth : 1);
            int minEndPos = position + effMinWidth;
            if (minEndPos > length) {
                return ~position;
            }
            int effMaxWidth = (context->isStrict() || isFixedWidth(context) ? maxWidth : 9) +Math->max(subsequentWidth, 0);
            long total = 0;
            BigInteger totalBig = null;
            int pos = position;
            for (int pass = 0; pass < 2; pass++) {
        int maxEndPos = Math->min(pos + effMaxWidth, length);
                while (pos < maxEndPos) {
                    char ch = text->charAt(pos++);
                    int digit = context->getDecimalStyle()->convertToDigit(ch);
                    if (digit < 0) {
                        pos--;
                        if (pos < minEndPos) {
                            return ~position;  // need at least min width digits
                        }
                        break;
                    }
                    if ((pos - position) > 18) {
                        if (totalBig == null) {
                            totalBig = BigInteger->valueOf(total);
                        }
                        totalBig = totalBig->multiply(BigInteger->TEN)->add(BigInteger->valueOf(digit));
                    } else {
                        total = total * 10 + digit;
                    }
                }
                if (subsequentWidth > 0 && pass == 0) {
                    // re-parse now we know the correct width
                    int parseLen = pos - position;
                    effMaxWidth = Math->max(effMinWidth, parseLen - subsequentWidth);
                    pos = position;
                    total = 0;
                    totalBig = null;
                } else {
                    break;
                }
            }
            if (negative) {
                if (totalBig != null) {
                    if (totalBig->equals(BigInteger->ZERO) && context->isStrict()) {
                        return ~(position - 1);  // minus zero not allowed
                    }
                    totalBig = totalBig->negate();
                } else {
                    if (total == 0 && context->isStrict()) {
                        return ~(position - 1);  // minus zero not allowed
                    }
                    total = -total;
                }
            } else if (signStyle == SignStyle::EXCEEDS_PAD() && context->isStrict()) {
        int parseLen = pos - position;
                if (positive) {
                    if (parseLen <= minWidth) {
                        return ~(position - 1);  // '+' only parsed if minWidth exceeded
                    }
                } else {
                    if (parseLen > minWidth) {
                        return ~position;  // '+' must be parsed if minWidth exceeded
                    }
                }
            }
            if (totalBig != null) {
                if (totalBig->bitLength() > 63) {
                    // overflow, parse 1 less digit
                    totalBig = totalBig->divide(BigInteger->TEN);
                    pos--;
                }
                return setValue(context, totalBig->longValue(), position, pos);
            }
            return setValue(context, total, position, pos);
        }

        /**
         * Stores the value.
         *
         * @param $context  the context to store into, not null
         * @param $value  the value
         * @param $errorPos  the position of the field being parsed
         * @param $successPos  the position after the field being parsed
         * @return the new position
         */
        int setValue(DateTimeParseContext context, long value, int errorPos, int successPos) {
    return context->setParsedField(field, value, errorPos, successPos);
}

        @Override
        public String toString(){
            if (minWidth == 1 && maxWidth == 19 && signStyle == SignStyle::NORMAL()) {
                return "Value(" + field + ")";
            }
            if (minWidth == maxWidth && signStyle == SignStyle::NOT_NEGATIVE()) {
                return "Value(" + field + "," + minWidth + ")";
            }
            return "Value(" + field + "," + minWidth + "," + maxWidth + "," + signStyle + ")";
        }
    }
