<?php
/**
 * Created by IntelliJ IDEA.
 * User: hanikel
 * Date: 11.09.15
 * Time: 16:08
 */

namespace Php\Time\Format\Builder;

/**
 * Defaults a value into the parse if not currently present.
 */
static class DefaultValueParser implements DateTimePrinterParser
{
private final TemporalField field;
private final long value;

DefaultValueParser(TemporalField field, long value)
{
this.field = field;
this.value = value;
}

        public boolean format(DateTimePrintContext context, StringBuilder buf) {
    return true;
}

        public int parse(DateTimeParseContext context, CharSequence text, int position) {
    if (context->getParsed(field) == null) {
        context->setParsedField(field, value, position, position);
    }
    return position;
}
    }
