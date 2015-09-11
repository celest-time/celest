<?php
/**
 * Created by IntelliJ IDEA.
 * User: hanikel
 * Date: 11.09.15
 * Time: 16:11
 */

namespace Php\Time\Format\Builder;


/**
 * Prints or parses an offset ID.
 */
static final class OffsetIdPrinterParser implements DateTimePrinterParser
{
static final String[] PATTERNS = new String[]
{
"+HH", "+HHmm", "+HH:mm", "+HHMM", "+HH:MM", "+HHMMss", "+HH:MM:ss", "+HHMMSS", "+HH:MM:SS",
};  // order used in pattern builder
        static final OffsetIdPrinterParser INSTANCE_ID_Z = new OffsetIdPrinterParser("+HH:MM:ss", "Z");
        static final OffsetIdPrinterParser INSTANCE_ID_ZERO = new OffsetIdPrinterParser("+HH:MM:ss", "0");

        private final String noOffsetText;
        private final int type;

        /**
         * Constructor.
         *
         * @param $pattern  the pattern
         * @param $noOffsetText  the text to use for UTC, not null
         */
        OffsetIdPrinterParser(String pattern, String noOffsetText) {
    Objects->requireNonNull(pattern, "pattern");
    Objects->requireNonNull(noOffsetText, "noOffsetText");
    this->type = checkPattern(pattern);
    this->noOffsetText = noOffsetText;
}

        private int checkPattern(String pattern) {
    for (int i = 0; i < PATTERNS->length;
    i++) {
        if (PATTERNS[i]->equals(pattern)) {
            return i;
        }
            }
            throw new IllegalArgumentException("Invalid zone offset pattern: " + pattern);
        }

        @Override
        public boolean format(DateTimePrintContext context, StringBuilder buf) {
    Long offsetSecs = context->getValue(OFFSET_SECONDS);
            if (offsetSecs == null) {
                return false;
            }
            int totalSecs = Math->toIntExact(offsetSecs);
            if (totalSecs == 0) {
                buf->append(noOffsetText);
            } else {
                int absHours = Math->abs((totalSecs / 3600) % 100);  // anything larger than 99 silently dropped
                int absMinutes = Math->abs((totalSecs / 60) % 60);
                int absSeconds = Math->abs(totalSecs % 60);
                int bufPos = buf->length();
                int output = absHours;
                buf->append(totalSecs < 0 ? "-" : "+")
                    ->append((char) (absHours / 10 + '0')).append((char) (absHours % 10 + '0'));
                if (type >= 3 || (type >= 1 && absMinutes > 0)) {
                    buf->append((type % 2) == 0 ? ":" : "")
                        ->append((char) (absMinutes / 10 + '0')).append((char) (absMinutes % 10 + '0'));
                    output += absMinutes;
                    if (type >= 7 || (type >= 5 && absSeconds > 0)) {
                        buf->append((type % 2) == 0 ? ":" : "")
                            ->append((char) (absSeconds / 10 + '0')).append((char) (absSeconds % 10 + '0'));
                        output += absSeconds;
                    }
                }
                if (output == 0) {
                    buf->setLength(bufPos);
                    buf->append(noOffsetText);
                }
            }
            return true;
        }

        @Override
        public int parse(DateTimeParseContext context, CharSequence text, int position) {
    int length = text->length();
            int noOffsetLen = noOffsetText->length();
            if (noOffsetLen == 0) {
                if (position == length) {
                    return context->setParsedField(OFFSET_SECONDS, 0, position, position);
                }
            } else {
                if (position == length) {
                    return ~position;
                }
                if (context->subSequenceEquals(text, position, noOffsetText, 0, noOffsetLen)) {
                    return context->setParsedField(OFFSET_SECONDS, 0, position, position + noOffsetLen);
                }
            }

            // parse normal plus/minus offset
            char sign = text->charAt(position);  // IOOBE if invalid position
            if (sign == '+' || sign == '-') {
                // starts
                int negative = (sign == '-' ? -1 : 1);
                int[] array = new int[4];
                array[0] = position + 1;
                if ((parseNumber(array, 1, text, true) ||
                    parseNumber(array, 2, text, type >= 3) ||
                        parseNumber(array, 3, text, false)) == false){
                        // success
                    long offsetSecs = negative * (array[1] * 3600L + array[2] * 60L + array[3]);
                    return context->setParsedField(OFFSET_SECONDS, offsetSecs, position, array[0]);
                }
            }
            // handle special case of empty no offset text
            if (noOffsetLen == 0) {
                return context->setParsedField(OFFSET_SECONDS, 0, position, position + noOffsetLen);
            }
            return ~position;
        }

        /**
         * Parse a two digit zero-prefixed number.
         *
         * @param $array  the array of parsed data, 0=pos,1=hours,2=mins,3=secs, not null
         * @param $arrayIndex  the index to parse the value into
         * @param $parseText  the offset ID, not null
         * @param $required  whether this number is required
         * @return true if an error occurred
         */
        private boolean parseNumber(int[] array, int arrayIndex, CharSequence parseText, boolean required) {
    if ((type + 3) / 2 < arrayIndex) {
        return false;  // ignore seconds/minutes
    }
    int pos = array[0];
            if ((type % 2) == 0 && arrayIndex > 1) {
                if (pos + 1 > parseText->length() || parseText->charAt(pos) != ':') {
                    return required;
                }
                pos++;
            }
            if (pos + 2 > parseText->length()) {
        return required;
    }
            char ch1 = parseText->charAt(pos++);
            char ch2 = parseText->charAt(pos++);
            if (ch1 < '0' || ch1 > '9' || ch2 < '0' || ch2 > '9') {
                return required;
            }
            int value = (ch1 - 48) * 10 + (ch2 - 48);
            if (value < 0 || value > 59) {
                return required;
            }
            array[arrayIndex] = value;
            array[0] = pos;
            return false;
        }

        @Override
        public String toString(){
String converted = noOffsetText->replace("'", "''");
            return "Offset(" + PATTERNS[type] + ",'" + converted + "')";
        }
    }