<?php declare(strict_types=1);

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

    public function __construct(string $literal)
    {
        $this->literal = $literal;  // validated by caller
    }

    public function format(DateTimePrintContext $context, string &$buf) : bool
    {
        $buf .= $this->literal;
        return true;
    }

    public function parse(DateTimeParseContext $context, string $text, int $position) : int
    {
        $length = strlen($text);
        if ($position > $length || $position < 0) {
            throw new \OutOfRangeException();
        }

        if ($context->subSequenceEquals($text, $position, $this->literal, 0, strlen($this->literal)) === false) {
            return ~$position;
        }
        return $position + strlen($this->literal);
    }

    public function __toString() : string
    {
        $converted = str_replace("'", "''", $this->literal);
        return "'" . $converted . "'";
    }
}
