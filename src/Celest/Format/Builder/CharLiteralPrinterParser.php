<?php declare(strict_types=1);

namespace Celest\Format\Builder;

use Celest\Format\DateTimePrintContext;
use Celest\Format\DateTimeParseContext;
use Celest\IllegalArgumentException;

/**
 * Prints or parses a character literal.
 */
final class CharLiteralPrinterParser implements DateTimePrinterParser
{
    /** @var string */
    private $literal;

    public function __construct(string $literal)
    {
        if (strlen($literal) !== 1) {
            throw new IllegalArgumentException();
        }
        $this->literal = $literal;
    }

    public function format(DateTimePrintContext $context, ?string &$buf) : bool
    {
        $buf .= $this->literal;
        return true;
    }

    public function parse(DateTimeParseContext $context, string $text, int $position) : int
    {
        $length = strlen($text);

        if ($position === $length) {
            return ~$position;
        }

        if ($position < 0 || $position >= $length) throw new \OutOfRangeException();
        $ch = $text[$position];
        if ($ch !== $this->literal) {
            if ($context->isCaseSensitive() ||
                (strtoupper($ch) !== strtoupper($this->literal) &&
                    strtolower($ch) !== strtolower($this->literal))
            ) {
                return ~$position;
            }
        }
        return $position + 1;
    }

    public function __toString() : string
    {
        if ($this->literal === '\'') {
            return "''";
        }

        return "'" . $this->literal . "'";
    }
}
