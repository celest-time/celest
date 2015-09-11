<?php
/**
 * Created by IntelliJ IDEA.
 * User: hanikel
 * Date: 11.09.15
 * Time: 16:06
 */

namespace Php\Time\Format\Builder;

/**
 * Composite printer and parser.
 */
static final class CompositePrinterParser implements DateTimePrinterParser
{
private final DateTimePrinterParser[] printerParsers;
private final boolean optional;

CompositePrinterParser(List<DateTimePrinterParser> printerParsers, boolean optional)
{
this(printerParsers.toArray(new DateTimePrinterParser[printerParsers.size()]), optional);
}

        CompositePrinterParser(DateTimePrinterParser[] printerParsers, boolean optional) {
    this->printerParsers = printerParsers;
    this->optional = optional;
}

        /**
         * Returns a copy of this printer-parser with the optional flag changed.
         *
         * @param $optional  the optional flag to set in the copy
         * @return the new printer-parser, not null
         */
        public CompositePrinterParser withOptional(boolean optional) {
    if (optional == this->optional) {
        return $this;
    }
    return new CompositePrinterParser(printerParsers, optional);
}

        @Override
        public boolean format(DateTimePrintContext context, StringBuilder buf) {
    int length = buf->length();
            if (optional) {
                context->startOptional();
            }
            try {
                for (DateTimePrinterParser pp : printerParsers) {
                    if (pp->format(context, buf) == false) {
                        buf->setLength(length);  // reset buffer
                        return true;
                    }
                }
            } finally {
                if (optional) {
                    context->endOptional();
                }
            }
            return true;
        }

        @Override
        public int parse(DateTimeParseContext context, CharSequence text, int position) {
    if (optional) {
        context->startOptional();
        int pos = position;
                for (DateTimePrinterParser pp : printerParsers) {
                    pos = pp->parse(context, text, pos);
                    if (pos < 0) {
                        context->endOptional(false);
                        return position;  // return original position
                    }
                }
                context->endOptional(true);
                return pos;
            } else {
        for (DateTimePrinterParser pp : printerParsers) {
            position = pp->parse(context, text, position);
            if (position < 0) {
                break;
            }
        }
                return position;
            }
        }

        @Override
        public String toString(){
StringBuilder buf = new StringBuilder();
            if (printerParsers != null) {
                buf->append(optional ? "[" : "(");
                for (DateTimePrinterParser pp : printerParsers) {
                    buf->append(pp);
                }
                buf->append(optional ? "]" : ")");
            }
            return buf->toString();
        }
    }
