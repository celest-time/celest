<?php

namespace Celest\Format\Builder;

use Celest\DateTimeException;
use Celest\Format\DateTimeParseContext;
use Celest\Temporal\ChronoField;
use Celest\Temporal\TemporalQuery;
use Celest\Format\DateTimePrintContext;
use Celest\Zone\ZoneRulesProvider;
use Celest\ZoneId;
use Celest\ZoneOffset;


/**
 * Prints or parses a zone ID.
 */
class ZoneIdPrinterParser implements DateTimePrinterParser
{
    /** @var TemporalQuery */
    private $query;
    /** @var string */
    private $description;

    public function __construct(TemporalQuery $query, $description)
    {
        $this->query = $query;
        $this->description = $description;
    }

    public function format(DateTimePrintContext $context, &$buf)
    {
        /** @var ZoneID $zone */
        $zone = $context->getValue($this->query);
        if ($zone == null) {
            return false;
        }

        $buf .= $zone->getId();
        return true;
    }

    /**
     * The cached tree to speed up parsing.
     */
//private static volatile Entry < Integer, PrefixTree > $cachedPrefixTree;
//private static volatile Entry < Integer, PrefixTree > $cachedPrefixTreeCI;

    protected function getTree(DateTimeParseContext $context)
    {
        // prepare parse tree
        $regionIds = ZoneRulesProvider::getAvailableZoneIds();
        $regionIdsSize = count($regionIds);
        $cached = $context->isCaseSensitive()
            ? self::$cachedPrefixTree : self::$cachedPrefixTreeCI;
        if ($cached == null || $cached->getKey() != $regionIdsSize) {
            {
                $cached = $context->isCaseSensitive() ? self::$cachedPrefixTree : self::$cachedPrefixTreeCI;
                if ($cached == null || $cached->getKey() != $regionIdsSize) {
                    $cached = new SimpleImmutableEntry($regionIdsSize, PrefixTree::newTree($regionIds, $context));
                    if ($context->isCaseSensitive()) {
                        self::$cachedPrefixTree = $cached;
                    } else {
                        self::$cachedPrefixTreeCI = $cached;
                    }
                }
            }
        }
        return $cached->getValue();
    }

    /**
     * This implementation looks for the longest matching string.
     * For example, parsing Etc/GMT-2 will return Etc/GMC-2 rather than just
     * Etc/GMC although both are valid.
     */
    public function parse(DateTimeParseContext $context, $text, $position)
    {
        $length = strlen($text);
        if ($position > $length) {
            throw new IndexOutOfBoundsException();
        }

        if ($position == $length) {
            return ~$position;
        }

// handle fixed time-zone IDs
        $nextChar = $text[$position];
        if ($nextChar == '+' || $nextChar == '-') {
            return $this->parseOffsetBased($context, $text, $position, $position, OffsetIdPrinterParser::INSTANCE_ID_Z());
        } else if ($length >= $position + 2) {
            $nextNextChar = $text[$position + 1];
            if ($context->charEquals($nextChar, 'U') && $context->charEquals($nextNextChar, 'T')) {
                if ($length >= $position + 3 && $context->charEquals($text->charAt($position + 2), 'C')) {
                    return $this->parseOffsetBased($context, $text, $position, $position + 3, OffsetIdPrinterParser::INSTANCE_ID_ZERO());
                }
                return $this->parseOffsetBased($context, $text, $position, $position + 2, OffsetIdPrinterParser::INSTANCE_ID_ZERO());
            } else if ($context->charEquals($nextChar, 'G') && $length >= $position + 3 &&
                $context->charEquals($nextNextChar, 'M') && $context->charEquals($text->charAt($position + 2), 'T')
            ) {
                return $this->parseOffsetBased($context, $text, $position, $position + 3, OffsetIdPrinterParser::INSTANCE_ID_ZERO());
            }
        }

        // parse
        $tree = $this->getTree($context);
        $ppos = new ParsePosition($position);
        $parsedZoneId = $tree->match($text, $ppos);
        if ($parsedZoneId == null) {
            if ($context->charEquals($nextChar, 'Z')) {
                $context->setParsed(ZoneOffset::UTC());
                return $position + 1;
            }
            return ~$position;
        }
        $context->setParsed(ZoneId::of($parsedZoneId));
        return $ppos->getIndex();
    }

    /**
     * Parse an offset following a prefix and set the ZoneId if it is valid.
     * To matching the parsing of ZoneId.of the values are not normalized
     * to ZoneOffsets.
     *
     * @param DateTimeParseContext $context the parse context
     * @param string $text the input text
     * @param int $prefixPos start of the prefix
     * @param int $position start of text after the prefix
     * @param OffsetIdPrinterParser $parser parser for the value after the prefix
     * @return int the position after the parse
     */
    private function parseOffsetBased(DateTimeParseContext $context, $text, $prefixPos, $position, OffsetIdPrinterParser $parser)
    {
        $prefix = $text->substring($prefixPos, $position)->toUpperCase();
        if ($position >= strlen($text)) {
            $context->setParsed(ZoneId::of($prefix));
            return $position;
        }

// '0' or 'Z' after prefix is not part of a valid ZoneId; use bare prefix
        if ($text->charAt($position) == '0' ||
            $context->charEquals($text->charAt(position), 'Z')
        ) {
            $context->setParsed(ZoneId::of($prefix));
            return $position;
        }

        $newContext = $context->copy();
        $endPos = $parser->parse($newContext, $text, $position);
        try {
            if ($endPos < 0) {
                if ($parser == OffsetIdPrinterParser::INSTANCE_ID_Z()) {
                    return ~$prefixPos;
                }
                $context->setParsed(ZoneId::of($prefix));
                return $position;
            }
            $offset = (int)$newContext->getParsed(ChronoField::OFFSET_SECONDS())->longValue();
            $zoneOffset = ZoneOffset::ofTotalSeconds($offset);
            $context->setParsed(ZoneId::ofOffset($prefix, $zoneOffset));
            return $endPos;
        } catch (DateTimeException $dte) {
            return ~$prefixPos;
        }
    }

    public function __toString()
    {
        return $this->description;
    }
}
