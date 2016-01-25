<?php

namespace Php\Time\Format\Builder;

use Php\Time\Format\DateTimeParseContext;
use Php\Time\Format\DateTimePrintContext;
use Php\Time\Temporal\TemporalField;

/**
 * Defaults a value into the parse if not currently present.
 */
class DefaultValueParser implements DateTimePrinterParser
{
    /** @var TemporalField */
    private $field;
    /** @var int */
    private $value;

    public function __construct(TemporalField $field, $value)
    {
        $this->field = $field;
        $this->value = $value;
    }

    public function format(DateTimePrintContext $context, &$buf)
    {
        return true;
    }

    public function parse(DateTimeParseContext $context, $text, $position)
    {
        if ($context->getParsed($this->field) == null) {
            $context->setParsedField($this->field, $this->value, $position, $position);
        }

        return $position;
    }

    function __toString()
    {
        return 'Default(' . $this->field . ':' . $this->value .')';
    }
}
