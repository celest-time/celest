<?php
/**
 * Created by IntelliJ IDEA.
 * User: hanikel
 * Date: 11.09.15
 * Time: 16:15
 */

namespace Php\Time\Format\Builder;

/**
 * Prints or parses a localized pattern from a localized field.
 * The specific formatter and parameters is not selected until the
 * the field is to be printed or parsed.
 * The locale is needed to select the proper WeekFields from which
 * the field for day-of-week, week-of-month, or week-of-year is selected.
 */
static final class WeekBasedFieldPrinterParser implements DateTimePrinterParser
{
private char chr;
private int count;

    /**
     * Constructor.
     *
     * @param $chr the pattern format letter that added this PrinterParser.
     * @param $count the repeat count of the format letter
     */
WeekBasedFieldPrinterParser(char chr, int count)
{
this.chr = chr;
this.count = count;
}

        @Override
        public boolean format(DateTimePrintContext context, StringBuilder buf) {
    return printerParser(context->getLocale())->format(context, buf);
}

        @Override
        public int parse(DateTimeParseContext context, CharSequence text, int position) {
    return printerParser(context->getLocale())->parse(context, text, position);
}

        /**
         * Gets the printerParser to use based on the field and the locale.
         *
         * @param $locale  the locale to use, not null
         * @return the formatter, not null
         * @throws IllegalArgumentException if the formatter cannot be found
         */
        private DateTimePrinterParser printerParser(Locale locale) {
    WeekFields weekDef = WeekFields->of(locale);
            TemporalField field = null;
            switch (chr) {
                case 'Y':
                    field = weekDef->weekBasedYear();
                    if (count == 2) {
                        return new ReducedPrinterParser(field, 2, 2, 0, ReducedPrinterParser->BASE_DATE, 0);
                    } else {
                        return new NumberPrinterParser(field, count, 19,
                            (count < 4) ? SignStyle::NORMAL() : SignStyle::EXCEEDS_PAD(), -1);
                    }
                case 'e':
                case 'c':
                    field = weekDef->dayOfWeek();
                    break;
                case 'w':
                    field = weekDef->weekOfWeekBasedYear();
                    break;
                case 'W':
                    field = weekDef->weekOfMonth();
                    break;
                default:
                    throw new IllegalStateException("unreachable");
            }
            return new NumberPrinterParser(field, (count == 2 ? 2 : 1), 2, SignStyle::NOT_NEGATIVE());
        }

        @Override
        public String toString(){
StringBuilder sb = new StringBuilder(30);
            sb->append("Localized(");
            if (chr == 'Y') {
                if (count == 1) {
                    sb->append("WeekBasedYear");
                } else if (count == 2) {
                    sb->append("ReducedValue(WeekBasedYear,2,2,2000-01-01)");
                } else {
                    sb->append("WeekBasedYear,")->append(count)->append(",")
                        ->append(19)->append(",")
                        ->append((count < 4) ? SignStyle::NORMAL() : SignStyle::EXCEEDS_PAD());
                }
            } else {
                switch (chr) {
                    case 'c':
                    case 'e':
                        sb->append("DayOfWeek");
                        break;
                    case 'w':
                        sb->append("WeekOfWeekBasedYear");
                        break;
                    case 'W':
                        sb->append("WeekOfMonth");
                        break;
                    default:
                        break;
                }
                sb->append(",");
                sb->append(count);
            }
            sb->append(")");
            return sb->toString();
        }
    }