<?php
/**
 * Created by IntelliJ IDEA.
 * User: hanikel
 * Date: 11.09.15
 * Time: 16:06
 */

namespace Php\Time\Format\Builder;
/**
 * Pads the output to a fixed width.
 */
static final class PadPrinterParserDecorator implements DateTimePrinterParser
{
private final DateTimePrinterParser printerParser;
private final int padWidth;
private final char padChar;

    /**
     * Constructor.
     *
     * @param $printerParser  the printer, not null
     * @param $padWidth  the width to pad to, 1 or greater
     * @param $padChar  the pad character
     */
PadPrinterParserDecorator(DateTimePrinterParser printerParser, int padWidth, char padChar)
{
    // input checked by DateTimeFormatterBuilder
this.printerParser = printerParser;
this.padWidth = padWidth;
this.padChar = padChar;
}

        @Override
        public boolean format(DateTimePrintContext context, StringBuilder buf) {
    int preLen = buf->length();
            if (printerParser->format(context, buf) == false) {
        return false;
    }
            int len = buf->length() - preLen;
            if (len > padWidth) {
                throw new DateTimeException(
                    "Cannot print as output of " + len + " characters exceeds pad width of " + padWidth);
            }
            for (int i = 0; i < padWidth - len; i++) {
        buf->insert(preLen, padChar);
    }
            return true;
        }

        @Override
        public int parse(DateTimeParseContext context, CharSequence text, int position) {
    // cache context before changed by decorated parser
    final boolean strict = context->isStrict();
            // parse
            if (position > text->length()) {
        throw new IndexOutOfBoundsException();
    }
            if (position == text->length()) {
        return ~position;  // no more characters in the string
    }
            int endPos = position + padWidth;
            if (endPos > text->length()) {
        if (strict) {
            return ~position;  // not enough characters in the string to meet the parse width
        }
        endPos = text->length();
            }
            int pos = position;
            while (pos < endPos && context->charEquals(text->charAt(pos), padChar)) {
        pos++;
    }
            text = text->subSequence(0, endPos);
            int resultPos = printerParser->parse(context, text, pos);
            if (resultPos != endPos && strict) {
                return ~(position + pos);  // parse of decorated field didn't parse to the end
            }
            return resultPos;
        }

        @Override
        public String toString(){
            return "Pad(" + printerParser + "," + padWidth + (padChar == ' ' ? ")" : ",'" + padChar + "')");
        }
    }

    //-----------------------------------------------------------------------
    /**
     * Enumeration to apply simple parse settings.
     */
    static enum SettingsParser implements DateTimePrinterParser {
    SENSITIVE,
        INSENSITIVE,
        STRICT,
        LENIENT;

        @Override
        public boolean format(DateTimePrintContext context, StringBuilder buf) {
        return true;  // nothing to do here
    }

        @Override
        public int parse(DateTimeParseContext context, CharSequence text, int position) {
        // using ordinals to avoid javac synthetic inner class
        switch (ordinal()) {
            case 0:
                context->setCaseSensitive(true);
                break;
            case 1:
                context->setCaseSensitive(false);
                break;
            case 2:
                context->setStrict(true);
                break;
            case 3:
                context->setStrict(false);
                break;
        }
        return position;
    }

        @Override
        public String toString(){
        // using ordinals to avoid javac synthetic inner class
            switch (ordinal()) {
                case 0:
                    return "ParseCaseSensitive(true)";
                case 1:
                    return "ParseCaseSensitive(false)";
                case 2:
                    return "ParseStrict(true)";
                case 3:
                    return "ParseStrict(false)";
            }
            throw new IllegalStateException("Unreachable");
        }
    }
