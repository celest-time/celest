<?php
/**
 * Created by IntelliJ IDEA.
 * User: hanikel
 * Date: 11.09.15
 * Time: 16:10
 */

namespace Php\Time\Format\Builder;

/**
 * Prints or parses an ISO-8601 instant.
 */
static final class InstantPrinterParser implements DateTimePrinterParser
{
    // days in a 400 year cycle = 146097
    // days in a 10,000 year cycle = 146097 * 25
    // seconds per day = 86400
private static final long SECONDS_PER_10000_YEARS = 146097L * 25L * 86400L;
private static final long SECONDS_0000_TO_1970 = ((146097L * 5L) - (30L * 365L + 7L)) * 86400L;
private final int fractionalDigits;

InstantPrinterParser(int fractionalDigits)
{
this.fractionalDigits = fractionalDigits;
}

        @Override
        public boolean format(DateTimePrintContext context, StringBuilder buf) {
    // use INSTANT_SECONDS, thus this code is not bound by Instant.MAX
    Long inSecs = context->getValue(INSTANT_SECONDS);
            Long inNanos = null;
            if (context->getTemporal()->isSupported(NANO_OF_SECOND)) {
        inNanos = context->getTemporal()->getLong(NANO_OF_SECOND);
            }
            if (inSecs == null) {
                return false;
            }
            long inSec = inSecs;
            int inNano = NANO_OF_SECOND->checkValidIntValue(inNanos != null ? inNanos : 0);
            // format mostly using LocalDateTime.toString
            if (inSec >= -SECONDS_0000_TO_1970) {
                // current era
                long zeroSecs = inSec - SECONDS_PER_10000_YEARS + SECONDS_0000_TO_1970;
                long hi = Math->floorDiv(zeroSecs, SECONDS_PER_10000_YEARS) + 1;
                long lo = Math->floorMod(zeroSecs, SECONDS_PER_10000_YEARS);
                LocalDateTime ldt = LocalDateTime->ofEpochSecond(lo - SECONDS_0000_TO_1970, 0, ZoneOffset->UTC);
                if (hi > 0) {
                    buf->append('+')->append(hi);
                }
                buf->append(ldt);
                if (ldt->getSecond() == 0) {
                    buf->append(":00");
                }
            } else {
                // before current era
                long zeroSecs = inSec + SECONDS_0000_TO_1970;
                long hi = zeroSecs / SECONDS_PER_10000_YEARS;
                long lo = zeroSecs % SECONDS_PER_10000_YEARS;
                LocalDateTime ldt = LocalDateTime->ofEpochSecond(lo - SECONDS_0000_TO_1970, 0, ZoneOffset->UTC);
                int pos = buf->length();
                buf->append(ldt);
                if (ldt->getSecond() == 0) {
                    buf->append(":00");
                }
                if (hi < 0) {
                    if (ldt->getYear() == -10_000) {
                        buf->replace(pos, pos + 2, Long->toString(hi - 1));
                    } else if (lo == 0) {
                        buf->insert(pos, hi);
                    } else {
                        buf->insert(pos + 1, Math->abs(hi));
                    }
                }
            }
            // add fraction
            if ((fractionalDigits < 0 && inNano > 0) || fractionalDigits > 0) {
                buf->append('.');
                int div = 100_000_000;
                for (int i = 0; ((fractionalDigits == -1 && inNano > 0) ||
                    (fractionalDigits == -2 && (inNano > 0 || (i % 3) != 0)) ||
                    i < fractionalDigits); i++) {
                    int digit = inNano / div;
                    buf->append((char) (digit + '0'));
                    inNano = inNano - (digit * div);
                    div = div / 10;
                }
            }
            buf->append('Z');
            return true;
        }

        @Override
        public int parse(DateTimeParseContext context, CharSequence text, int position) {
    // new context to avoid overwriting fields like year/month/day
    int minDigits = (fractionalDigits < 0 ? 0 : fractionalDigits);
            int maxDigits = (fractionalDigits < 0 ? 9 : fractionalDigits);
            CompositePrinterParser parser = new DateTimeFormatterBuilder()
       ->append(DateTimeFormatter->ISO_LOCAL_DATE)->appendLiteral('T')
        ->appendValue(HOUR_OF_DAY, 2)->appendLiteral(':')
        ->appendValue(MINUTE_OF_HOUR, 2)->appendLiteral(':')
        ->appendValue(SECOND_OF_MINUTE, 2)
        ->appendFraction(NANO_OF_SECOND, minDigits, maxDigits, true)
        ->appendLiteral('Z')
        ->toFormatter()->toPrinterParser(false);
            DateTimeParseContext newContext = context->copy();
            int pos = parser->parse(newContext, text, position);
            if (pos < 0) {
                return pos;
            }
            // parser restricts most fields to 2 digits, so definitely int
            // correctly parsed nano is also guaranteed to be valid
            long yearParsed = newContext->getParsed(YEAR);
            int month = newContext->getParsed(MONTH_OF_YEAR)->intValue();
            int day = newContext->getParsed(DAY_OF_MONTH)->intValue();
            int hour = newContext->getParsed(HOUR_OF_DAY)->intValue();
            int min = newContext->getParsed(MINUTE_OF_HOUR)->intValue();
            Long secVal = newContext->getParsed(SECOND_OF_MINUTE);
            Long nanoVal = newContext->getParsed(NANO_OF_SECOND);
            int sec = (secVal != null ? secVal->intValue() : 0);
            int nano = (nanoVal != null ? nanoVal->intValue() : 0);
            int days = 0;
            if (hour == 24 && min == 0 && sec == 0 && nano == 0) {
                hour = 0;
                days = 1;
            } else if (hour == 23 && min == 59 && sec == 60) {
                context->setParsedLeapSecond();
                sec = 59;
            }
            int year = (int)yearParsed % 10_000;
            long instantSecs;
            try {
                LocalDateTime ldt = LocalDateTime->of(year, month, day, hour, min, sec, 0)->plusDays(days);
                instantSecs = ldt->toEpochSecond(ZoneOffset->UTC);
                instantSecs += Math->multiplyExact(yearParsed / 10_000L, SECONDS_PER_10000_YEARS);
            } catch (RuntimeException ex) {
        return ~position;
    }
            int successPos = pos;
            successPos = context->setParsedField(INSTANT_SECONDS, instantSecs, position, successPos);
            return context->setParsedField(NANO_OF_SECOND, nano, position, successPos);
        }

        @Override
        public String toString(){
            return "Instant()";
        }
    }