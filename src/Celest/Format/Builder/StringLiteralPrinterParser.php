<?php

namespace Celest\Format\Builder;

use Celest\Format\DateTimeParseContext;
use Celest\Format\DateTimePrintContext;

/**
 * Prints or parses a string literal.
 */
final class StringLiteralPrinterParser implements DateTimePrinterParser
{
    /** @var string */
    private $literal;

    public function __construct($literal)
    {
        $this->literal = $literal;  // validated by caller
    }

    public function format(DateTimePrintContext $context, &$buf)
    {
        $buf .= $this->literal;
        return true;
    }

    public function parse(DateTimeParseContext $context, $text, $position)
    {
        $length = strlen($text);
        if ($position > $length || $position < 0) {
            throw new \OutOfRangeException();
        }

        if ($context->subSequenceEquals($text, $position, $this->literal, 0, strlen($this->literal)) == false) {
            return ~$position;
        }
        return $position + strlen($this->literal);
    }

    public function __toString()
    {
        $converted = str_replace("'", "''", $this->literal);
        return "'" . $converted . "'";
    }
}
