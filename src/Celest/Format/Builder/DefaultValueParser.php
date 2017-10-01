<?php declare(strict_types=1);

namespace Celest\Format\Builder;

use Celest\Format\DateTimeParseContext;
use Celest\Format\DateTimePrintContext;
use Celest\Temporal\TemporalField;

/**
 * Defaults a value into the parse if not currently present.
 */
class DefaultValueParser implements DateTimePrinterParser
{
    /** @var TemporalField */
    private $field;
    /** @var int */
    private $value;

    public function __construct(TemporalField $field, int $value)
    {
        $this->field = $field;
        $this->value = $value;
    }

    public function format(DateTimePrintContext $context, string &$buf) : bool
    {
        return true;
    }

    public function parse(DateTimeParseContext $context, string $text, int $position) : int
    {
        if ($context->getParsed($this->field) === null) {
            $context->setParsedField($this->field, $this->value, $position, $position);
        }

        return $position;
    }

    function __toString() : string
    {
        return 'Default(' . $this->field . ':' . $this->value . ')';
    }
}
