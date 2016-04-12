<?php

namespace Celest\Format\Builder;

use Celest\DateTimeException;
use Celest\Format\DateTimeParseContext;
use Celest\Format\DateTimePrintContext;
use Celest\Format\ParsePosition;
use Celest\Temporal\ChronoField;
use Celest\Temporal\TemporalQuery;
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
        if ($zone === null) {
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
        if ($cached === null || $cached->getKey() !== $regionIdsSize) {
            {
                $cached = $context->isCaseSensitive() ? self::$cachedPrefixTree : self::$cachedPrefixTreeCI;
                if ($cached === null || $cached->getKey() !== $regionIdsSize) {
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
            throw new \OutOfRangeException();
        }

        if ($position === $length) {
            return ~$position;
        }

// handle fixed time-zone IDs
        $nextChar = $text[$position];
        if ($nextChar === '+' || $nextChar === '-') {
            return $this->parseOffsetBased($context, $text, $position, $position, OffsetIdPrinterParser::INSTANCE_ID_Z());
        } else if ($length >= $position + 2) {
            $nextNextChar = $text[$position + 1];
            if ($context->charEquals($nextChar, 'U') && $context->charEquals($nextNextChar, 'T')) {
                if ($length >= $position + 3 && $context->charEquals($text[$position + 2], 'C')) {
                    return $this->parseOffsetBased($context, $text, $position, $position + 3, OffsetIdPrinterParser::INSTANCE_ID_ZERO());
                }
                return $this->parseOffsetBased($context, $text, $position, $position + 2, OffsetIdPrinterParser::INSTANCE_ID_ZERO());
            } else if ($context->charEquals($nextChar, 'G') && $length >= $position + 3 &&
                $context->charEquals($nextNextChar, 'M') && $context->charEquals($text[$position + 2], 'T')
            ) {
                return $this->parseOffsetBased($context, $text, $position, $position + 3, OffsetIdPrinterParser::INSTANCE_ID_ZERO());
            }
        }

        // parse
        $ppos = new ParsePosition($position);

        $parsedZoneId = $this->match($text, $ppos, $context->isCaseSensitive());
        /*
        $tree = $this->getTree($context);
        $parsedZoneId = $tree->match($text, $ppos);
        */

        if ($parsedZoneId === null) {
            if ($context->charEquals($nextChar, 'Z')) {
                $context->setParsedZone(ZoneOffset::UTC());
                return $position + 1;
            }
            return ~$position;
        }
        $context->setParsedZone(ZoneId::of($parsedZoneId));
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
        $prefix = strtoupper(substr($text, $prefixPos, $position - $prefixPos));
        if ($position >= strlen($text)) {
            $context->setParsedZone(ZoneId::of($prefix));
            return $position;
        }

// '0' or 'Z' after prefix is not part of a valid ZoneId; use bare prefix
        if ($text[$position] === '0' ||
            $context->charEquals($text[$position], 'Z')
        ) {
            $context->setParsedZone(ZoneId::of($prefix));
            return $position;
        }

        $newContext = $context->copy();
        $endPos = $parser->parse($newContext, $text, $position);
        try {
            if ($endPos < 0) {
                if ($parser == OffsetIdPrinterParser::INSTANCE_ID_Z()) {
                    return ~$prefixPos;
                }
                $context->setParsedZone(ZoneId::of($prefix));
                return $position;
            }
            $offset = $newContext->getParsed(ChronoField::OFFSET_SECONDS());
            $zoneOffset = ZoneOffset::ofTotalSeconds($offset);
            $context->setParsedZone(ZoneId::ofOffset($prefix, $zoneOffset));
            return $endPos;
        } catch (DateTimeException $dte) {
            return ~$prefixPos;
        }
    }

    public function __toString()
    {
        return $this->description;
    }


    /**
     * TODO performance
     *
     * @param string $text
     * @param ParsePosition $ppos
     * @param bool $isCaseSensitive
     * @return null|string
     */
    private function match($text, ParsePosition $ppos, $isCaseSensitive)
    {
        $ids = ZoneId::getAvailableZoneIds();

        if (!$isCaseSensitive) {
            $ids = \array_map('\strtolower', $ids);
            $text = \strtolower($text);
        }

        $ids = \array_flip($ids);

        $pos = $ppos->getIndex();
        $max = \strlen($text) - 1;


        for ($i = $max; $i >= $pos; $i--) {
            $str = \substr($text, $pos, $i - $pos + 1);
            if (isset($ids[$str])) {
                $ppos->setIndex($i + 1);
                return ZoneId::getAvailableZoneIds()[$ids[$str]];
            }
        }

        return null;
    }
}
