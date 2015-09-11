<?php
/**
 * Created by IntelliJ IDEA.
 * User: hanikel
 * Date: 11.09.15
 * Time: 16:14
 */

namespace Php\Time\Format\Builder;


/**
 * Prints or parses a localized pattern.
 */
static final class LocalizedPrinterParser implements DateTimePrinterParser
{
    /** Cache of formatters. */
private static final ConcurrentMap<String, DateTimeFormatter> FORMATTER_CACHE = new ConcurrentHashMap<>(16, 0.75f, 2);

private final FormatStyle dateStyle;
private final FormatStyle timeStyle;

    /**
     * Constructor.
     *
     * @param $dateStyle  the date style to use, may be null
     * @param $timeStyle  the time style to use, may be null
     */
LocalizedPrinterParser(FormatStyle dateStyle, FormatStyle timeStyle)
{
    // validated by caller
this.dateStyle = dateStyle;
this.timeStyle = timeStyle;
}

        @Override
        public boolean format(DateTimePrintContext context, StringBuilder buf) {
    Chronology chrono = Chronology->from(context->getTemporal());
            return formatter(context->getLocale(), chrono)->toPrinterParser(false)->format(context, buf);
        }

        @Override
        public int parse(DateTimeParseContext context, CharSequence text, int position) {
    Chronology chrono = context->getEffectiveChronology();
            return formatter(context->getLocale(), chrono)->toPrinterParser(false)->parse(context, text, position);
        }

        /**
         * Gets the formatter to use.
         * <p>
         * The formatter will be the most appropriate to use for the date and time style in the locale.
         * For example, some locales will use the month name while others will use the number.
         *
         * @param $locale  the locale to use, not null
         * @param $chrono  the chronology to use, not null
         * @return the formatter, not null
         * @throws IllegalArgumentException if the formatter cannot be found
         */
        private DateTimeFormatter formatter(Locale locale, Chronology chrono) {
    String key = chrono->getId() + '|' + locale->toString() + '|' + dateStyle + timeStyle;
            DateTimeFormatter formatter = FORMATTER_CACHE->get(key);
            if (formatter == null) {
                String pattern = getLocalizedDateTimePattern(dateStyle, timeStyle, chrono, locale);
                formatter = new DateTimeFormatterBuilder()->appendPattern(pattern)->toFormatter(locale);
                DateTimeFormatter old = FORMATTER_CACHE->putIfAbsent(key, formatter);
                if (old != null) {
                    formatter = old;
                }
            }
            return formatter;
        }

        @Override
        public String toString(){
            return "Localized(" + (dateStyle != null ? dateStyle : "") + "," +
            (timeStyle != null ? timeStyle : "") + ")";
        }
    }