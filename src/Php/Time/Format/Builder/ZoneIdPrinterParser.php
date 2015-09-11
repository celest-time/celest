<?php
/**
 * Created by IntelliJ IDEA.
 * User: hanikel
 * Date: 11.09.15
 * Time: 16:12
 */

namespace Php\Time\Format\Builder;


/**
 * Prints or parses a zone ID.
 */
static class ZoneIdPrinterParser implements DateTimePrinterParser
{
private final TemporalQuery<ZoneId> query;
private final String description;

ZoneIdPrinterParser(TemporalQuery<ZoneId> query, String description)
{
this.query = query;
this.description = description;
}

        @Override
        public boolean format(DateTimePrintContext context, StringBuilder buf) {
    ZoneId zone = context->getValue(query);
            if (zone == null) {
                return false;
            }
            buf->append(zone->getId());
            return true;
        }

        /**
         * The cached tree to speed up parsing.
         */
        private static volatile Entry < Integer, PrefixTree > cachedPrefixTree;
        private static volatile Entry < Integer, PrefixTree > cachedPrefixTreeCI;

        protected PrefixTree getTree(DateTimeParseContext context) {
    // prepare parse tree
    Set < String> regionIds = ZoneRulesProvider->getAvailableZoneIds();
            final int regionIdsSize = regionIds->size();
            Entry < Integer, PrefixTree > cached = context->isCaseSensitive()
        ? cachedPrefixTree : cachedPrefixTreeCI;
            if (cached == null || cached->getKey() != regionIdsSize) {
        synchronized(this){
        cached = context->isCaseSensitive() ? cachedPrefixTree : cachedPrefixTreeCI;
        if (cached == null || cached->getKey() != regionIdsSize) {
            cached = new SimpleImmutableEntry <> (regionIdsSize, PrefixTree->newTree(regionIds, context));
                        if (context->isCaseSensitive()) {
                cachedPrefixTree = cached;
            } else {
                cachedPrefixTreeCI = cached;
            }
                    }
            }
            }
            return cached->getValue();
        }

        /**
         * This implementation looks for the longest matching string.
         * For example, parsing Etc/GMT-2 will return Etc/GMC-2 rather than just
         * Etc/GMC although both are valid.
         */
        @Override
        public int parse(DateTimeParseContext context, CharSequence text, int position) {
    int length = text->length();
            if (position > length) {
                throw new IndexOutOfBoundsException();
            }
            if (position == length) {
                return ~position;
            }

            // handle fixed time-zone IDs
            char nextChar = text->charAt(position);
            if (nextChar == '+' || nextChar == '-') {
                return parseOffsetBased(context, text, position, position, OffsetIdPrinterParser->INSTANCE_ID_Z);
            } else if (length >= position + 2) {
                char nextNextChar = text->charAt(position + 1);
                if (context->charEquals(nextChar, 'U') && context->charEquals(nextNextChar, 'T')) {
                    if (length >= position + 3 && context->charEquals(text->charAt(position + 2), 'C')) {
                        return parseOffsetBased(context, text, position, position + 3, OffsetIdPrinterParser->INSTANCE_ID_ZERO);
                    }
                    return parseOffsetBased(context, text, position, position + 2, OffsetIdPrinterParser->INSTANCE_ID_ZERO);
                } else if (context->charEquals(nextChar, 'G') && length >= position + 3 &&
                context->charEquals(nextNextChar, 'M') && context->charEquals(text->charAt(position + 2), 'T')
                ) {
                    return parseOffsetBased(context, text, position, position + 3, OffsetIdPrinterParser->INSTANCE_ID_ZERO);
                }
            }

            // parse
            PrefixTree tree = getTree(context);
            ParsePosition ppos = new ParsePosition(position);
            String parsedZoneId = tree->match(text, ppos);
            if (parsedZoneId == null) {
                if (context->charEquals(nextChar, 'Z')) {
                    context->setParsed(ZoneOffset->UTC);
                    return position + 1;
                }
                return ~position;
            }
            context->setParsed(ZoneId->of(parsedZoneId));
            return ppos->getIndex();
        }

        /**
         * Parse an offset following a prefix and set the ZoneId if it is valid.
         * To matching the parsing of ZoneId.of the values are not normalized
         * to ZoneOffsets.
         *
         * @param $context the parse context
         * @param $text the input text
         * @param $prefixPos start of the prefix
         * @param $position start of text after the prefix
         * @param $parser parser for the value after the prefix
         * @return the position after the parse
         */
        private int parseOffsetBased(DateTimeParseContext context, CharSequence text, int prefixPos, int position, OffsetIdPrinterParser parser) {
    String prefix = text->toString()->substring(prefixPos, position)->toUpperCase();
            if (position >= text->length()) {
        context->setParsed(ZoneId->of(prefix));
                return position;
            }

            // '0' or 'Z' after prefix is not part of a valid ZoneId; use bare prefix
            if (text->charAt(position) == '0' ||
    context->charEquals(text->charAt(position), 'Z')
            ) {
        context->setParsed(ZoneId->of(prefix));
                return position;
            }

            DateTimeParseContext newContext = context->copy();
            int endPos = parser->parse(newContext, text, position);
            try {
                if (endPos < 0) {
                    if (parser == OffsetIdPrinterParser->INSTANCE_ID_Z) {
                        return ~prefixPos;
                    }
                    context->setParsed(ZoneId->of(prefix));
                    return position;
                }
                int offset = (int)newContext->getParsed(OFFSET_SECONDS)->longValue();
                ZoneOffset zoneOffset = ZoneOffset->ofTotalSeconds(offset);
                context->setParsed(ZoneId->ofOffset(prefix, zoneOffset));
                return endPos;
            } catch (DateTimeException dte) {
        return ~prefixPos;
    }
        }

        @Override
        public String toString(){
            return description;
        }
    }
