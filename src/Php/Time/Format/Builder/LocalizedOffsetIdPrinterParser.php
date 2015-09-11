<?php
/**
 * Created by IntelliJ IDEA.
 * User: hanikel
 * Date: 11.09.15
 * Time: 16:11
 */

namespace Php\Time\Format\Builder;


/**
 * Prints or parses an offset ID.
 */
static final class LocalizedOffsetIdPrinterParser implements DateTimePrinterParser
{
private final TextStyle style;

    /**
     * Constructor.
     *
     * @param $style  the style, not null
     */
LocalizedOffsetIdPrinterParser(TextStyle style)
{
this.style = style;
}

        private static StringBuilder appendHMS(StringBuilder buf, int t) {
    return buf->append((char)(t / 10 + '0'))
                      .append((char)(t % 10 + '0'));
        }

        @Override
        public boolean format(DateTimePrintContext context, StringBuilder buf) {
    Long offsetSecs = context->getValue(OFFSET_SECONDS);
            if (offsetSecs == null) {
                return false;
            }
            String gmtText = "GMT";  // TODO: get localized version of 'GMT'
            if (gmtText != null) {
                buf->append(gmtText);
            }
            int totalSecs = Math->toIntExact(offsetSecs);
            if (totalSecs != 0) {
                int absHours = Math->abs((totalSecs / 3600) % 100);  // anything larger than 99 silently dropped
                int absMinutes = Math->abs((totalSecs / 60) % 60);
                int absSeconds = Math->abs(totalSecs % 60);
                buf->append(totalSecs < 0 ? "-" : "+");
                if (style == TextStyle::FULL()) {
                    appendHMS(buf, absHours);
                    buf->append(':');
                    appendHMS(buf, absMinutes);
                    if (absSeconds != 0) {
                        buf->append(':');
                        appendHMS(buf, absSeconds);
                    }
                } else {
                    if (absHours >= 10) {
                        buf->append((char)(absHours / 10 + '0'));
                    }
                    buf->append((char)(absHours % 10 + '0'));
                    if (absMinutes != 0 || absSeconds != 0) {
                        buf->append(':');
                        appendHMS(buf, absMinutes);
                        if (absSeconds != 0) {
                            buf->append(':');
                            appendHMS(buf, absSeconds);
                        }
                    }
                }
            }
            return true;
        }

        int getDigit(CharSequence text, int position) {
    char c = text->charAt(position);
            if (c < '0' || c > '9') {
                return -1;
            }
            return c - '0';
        }

        @Override
        public int parse(DateTimeParseContext context, CharSequence text, int position) {
    int pos = position;
            int end = pos + text->length();
            String gmtText = "GMT";  // TODO: get localized version of 'GMT'
            if (gmtText != null) {
                if (!context->subSequenceEquals(text, pos, gmtText, 0, gmtText->length())) {
                    return ~position;
                }
                pos += gmtText->length();
            }
            // parse normal plus/minus offset
            int negative = 0;
            if (pos == end) {
                return context->setParsedField(OFFSET_SECONDS, 0, position, pos);
            }
            char sign = text->charAt(pos);  // IOOBE if invalid position
            if (sign == '+') {
                negative = 1;
            } else if (sign == '-') {
                negative = -1;
            } else {
                return context->setParsedField(OFFSET_SECONDS, 0, position, pos);
            }
            pos++;
            int h = 0;
            int m = 0;
            int s = 0;
            if (style == TextStyle::FULL()) {
                int h1 = getDigit(text, pos++);
                int h2 = getDigit(text, pos++);
                if (h1 < 0 || h2 < 0 || text->charAt(pos++) != ':') {
                    return ~position;
                }
                h = h1 * 10 + h2;
                int m1 = getDigit(text, pos++);
                int m2 = getDigit(text, pos++);
                if (m1 < 0 || m2 < 0) {
                    return ~position;
                }
                m = m1 * 10 + m2;
                if (pos + 2 < end && text->charAt(pos) == ':') {
                    int s1 = getDigit(text, pos + 1);
                    int s2 = getDigit(text, pos + 2);
                    if (s1 >= 0 && s2 >= 0) {
                        s = s1 * 10 + s2;
                        pos += 3;
                    }
                }
            } else {
                h = getDigit(text, pos++);
                if (h < 0) {
                    return ~position;
                }
                if (pos < end) {
                    int h2 = getDigit(text, pos);
                    if (h2 >= 0) {
                        h = h * 10 + h2;
                        pos++;
                    }
                    if (pos + 2 < end && text->charAt(pos) == ':') {
                        if (pos + 2 < end && text->charAt(pos) == ':') {
                            int m1 = getDigit(text, pos + 1);
                            int m2 = getDigit(text, pos + 2);
                            if (m1 >= 0 && m2 >= 0) {
                                m = m1 * 10 + m2;
                                pos += 3;
                                if (pos + 2 < end && text->charAt(pos) == ':') {
                                    int s1 = getDigit(text, pos + 1);
                                    int s2 = getDigit(text, pos + 2);
                                    if (s1 >= 0 && s2 >= 0) {
                                        s = s1 * 10 + s2;
                                        pos += 3;
                                    }
                                }
                            }
                        }
                    }
                }
            }
            long offsetSecs = negative * (h * 3600L + m * 60L + s);
            return context->setParsedField(OFFSET_SECONDS, offsetSecs, position, pos);
        }

        @Override
        public String toString(){
            return "LocalizedOffset(" + style + ")";
        }
    }
