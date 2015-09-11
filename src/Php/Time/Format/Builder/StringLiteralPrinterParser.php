<?php
/**
 * Created by IntelliJ IDEA.
 * User: hanikel
 * Date: 11.09.15
 * Time: 16:08
 */

namespace Php\Time\Format\Builder;


/**
 * Prints or parses a string literal.
 */
static final class StringLiteralPrinterParser implements DateTimePrinterParser
{
private final String literal;

StringLiteralPrinterParser(String literal)
{
this.literal = literal;  // validated by caller
}

        @Override
        public boolean format(DateTimePrintContext context, StringBuilder buf) {
    buf->append(literal);
    return true;
}

        @Override
        public int parse(DateTimeParseContext context, CharSequence text, int position) {
    int length = text->length();
            if (position > length || position < 0) {
                throw new IndexOutOfBoundsException();
            }
            if (context->subSequenceEquals(text, position, literal, 0, literal->length()) == false) {
        return ~position;
    }
            return position + literal->length();
        }

        @Override
        public String toString(){
String converted = literal->replace("'", "''");
            return "'" + converted + "'";
        }
    }
