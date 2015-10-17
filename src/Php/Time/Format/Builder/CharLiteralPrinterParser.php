<?php

namespace Php\Time\Format\Builder;
use Php\Time\Format\DateTimePrintContext;
use Php\Time\Format\DateTimeParseContext;

/**
 * Prints or parses a character literal.
 */
final class CharLiteralPrinterParser implements DateTimePrinterParser
{
    /** @var string */
    private $literal;

    public function __construct($literal)
    {
        $this->literal = $literal;
    }

    public function format(DateTimePrintContext $context, &$buf)
    {
        $buf .= $this->literal;
        return true;
    }

    public function parse(DateTimeParseContext $context, $text, $position)
    {
        $length = strlen($text);
        if ($position == $length) {
            return ~$position;
        }

        $ch = $text[$position];
        if ($ch !== $this->literal) {
            if ($context->isCaseSensitive() ||
                (strtoupper($ch) !== strtoupper($this->literal) &&
                    strtolower($ch) != strtolower($this->literal))
            ) {
                return ~$position;
            }
        }
        return $position + 1;
    }

    public function __toString()
    {
        if ($this->literal == '\'') {
            return "''";
        }

        return "'" . $this->literal . "'";
    }
}
