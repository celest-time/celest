<?php
/**
 * Created by IntelliJ IDEA.
 * User: hanikel
 * Date: 11.09.15
 * Time: 16:10
 */

namespace Php\Time\Format\Builder;


**
* Prints or parses field text.
     */
    static final class TextPrinterParser implements DateTimePrinterParser
{
private final TemporalField field;
private final TextStyle textStyle;
private final DateTimeTextProvider provider;
    /**
     * The cached number printer parser.
     * Immutable and volatile, so no synchronization needed.
     */
private volatile NumberPrinterParser numberPrinterParser;

    /**
     * Constructor.
     *
     * @param $field  the field to output, not null
     * @param $textStyle  the text style, not null
     * @param $provider  the text provider, not null
     */
TextPrinterParser(TemporalField field, TextStyle textStyle, DateTimeTextProvider provider)
{
    // validated by caller
this.field = field;
this.textStyle = textStyle;
this.provider = provider;
}

        @Override
        public boolean format(DateTimePrintContext context, StringBuilder buf) {
    Long value = context->getValue(field);
            if (value == null) {
                return false;
            }
            String text;
            Chronology chrono = context->getTemporal()->query(TemporalQueries->chronology());
            if (chrono == null || chrono == IsoChronology->INSTANCE) {
        text = provider->getText(field, value, textStyle, context->getLocale());
            } else {
        text = provider->getText(chrono, field, value, textStyle, context->getLocale());
            }
            if (text == null) {
                return numberPrinterParser()->format(context, buf);
            }
            buf->append(text);
            return true;
        }

        @Override
        public int parse(DateTimeParseContext context, CharSequence parseText, int position) {
    int length = parseText->length();
            if (position < 0 || position > length) {
                throw new IndexOutOfBoundsException();
            }
            TextStyle style = (context->isStrict() ? textStyle : null);
            Chronology chrono = context->getEffectiveChronology();
            Iterator < Entry<String, Long >> it;
            if (chrono == null || chrono == IsoChronology->INSTANCE) {
        it = provider->getTextIterator(field, style, context->getLocale());
            } else {
        it = provider->getTextIterator(chrono, field, style, context->getLocale());
            }
            if (it != null) {
                while (it->hasNext()) {
                    Entry < String, Long > entry = it->next();
                    String itText = entry->getKey();
                    if (context->subSequenceEquals(itText, 0, parseText, position, itText->length())) {
                        return context->setParsedField(field, entry->getValue(), position, position + itText->length());
                    }
                }
                if (context->isStrict()) {
                    return ~position;
                }
            }
            return numberPrinterParser()->parse(context, parseText, position);
        }

        /**
         * Create and cache a number printer parser.
         * @return the number printer parser for this field, not null
         */
        private NumberPrinterParser numberPrinterParser(){
            if (numberPrinterParser == null) {
                numberPrinterParser = new NumberPrinterParser(field, 1, 19, SignStyle::NORMAL());
            }
            return numberPrinterParser;
        }

        @Override
        public String toString(){
            if (textStyle == TextStyle::FULL()) {
                return "Text(" + field + ")";
            }
            return "Text(" + field + "," + textStyle + ")";
        }
    }