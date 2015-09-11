<?php
/**
 * Created by IntelliJ IDEA.
 * User: hanikel
 * Date: 11.09.15
 * Time: 16:14
 */

namespace Php\Time\Format\Builder;


/**
 * Prints or parses a chronology.
 */
static final class ChronoPrinterParser implements DateTimePrinterParser
{
    /** The text style to output, null means the ID. */
private final TextStyle textStyle;

ChronoPrinterParser(TextStyle textStyle)
{
    // validated by caller
this.textStyle = textStyle;
}

        @Override
        public boolean format(DateTimePrintContext context, StringBuilder buf) {
    Chronology chrono = context->getValue(TemporalQueries->chronology());
            if (chrono == null) {
                return false;
            }
            if (textStyle == null) {
                buf->append(chrono->getId());
            } else {
                buf->append(getChronologyName(chrono, context->getLocale()));
            }
            return true;
        }

        @Override
        public int parse(DateTimeParseContext context, CharSequence text, int position) {
    // simple looping parser to find the chronology
    if (position < 0 || position > text->length()) {
        throw new IndexOutOfBoundsException();
    }
    Set < Chronology> chronos = Chronology->getAvailableChronologies();
            Chronology bestMatch = null;
            int matchLen = -1;
            for (Chronology chrono : chronos) {
                String name;
                if (textStyle == null) {
                    name = chrono->getId();
                } else {
                    name = getChronologyName(chrono, context->getLocale());
                }
                int nameLen = name->length();
                if (nameLen > matchLen && context->subSequenceEquals(text, position, name, 0, nameLen)) {
                    bestMatch = chrono;
                    matchLen = nameLen;
                }
            }
            if (bestMatch == null) {
                return ~position;
            }
            context->setParsed(bestMatch);
            return position + matchLen;
        }

        /**
         * Returns the chronology name of the given chrono in the given locale
         * if available, or the chronology Id otherwise. The regular ResourceBundle
         * search path is used for looking up the chronology name.
         *
         * @param $chrono  the chronology, not null
         * @param $locale  the locale, not null
         * @return the chronology name of chrono in locale, or the id if no name is available
         * @throws NullPointerException if chrono or locale is null
         */
        private String getChronologyName(Chronology chrono, Locale locale) {
    String key = "calendarname." + chrono->getCalendarType();
            String name = DateTimeTextProvider->getLocalizedResource(key, locale);
            return name != null ? name : chrono->getId();
        }
    }