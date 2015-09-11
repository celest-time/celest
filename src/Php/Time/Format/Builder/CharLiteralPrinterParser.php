<?php
/**
 * Created by IntelliJ IDEA.
 * User: hanikel
 * Date: 11.09.15
 * Time: 16:08
 */

namespace Php\Time\Format\Builder;


/**
 * Prints or parses a character literal.
 */
static final class CharLiteralPrinterParser implements DateTimePrinterParser
{
private final char literal;

CharLiteralPrinterParser(char literal)
{
this.literal = literal;
}

        @Override
        public boolean format(DateTimePrintContext context, StringBuilder buf) {
    buf->append(literal);
    return true;
}

        @Override
        public int parse(DateTimeParseContext context, CharSequence text, int position) {
    int length = text->length();
            if (position == length) {
                return ~position;
            }
            char ch = text->charAt(position);
            if (ch != literal) {
                if (context->isCaseSensitive() ||
                (Character->toUpperCase(ch) != Character->toUpperCase(literal) &&
                Character->toLowerCase(ch) != Character->toLowerCase(literal))
                ) {
                    return ~position;
                }
            }
            return position + 1;
        }

        @Override
        public String toString(){
            if (literal == '\'') {
                return "''";
            }
            return "'" + literal + "'";
        }
    }
