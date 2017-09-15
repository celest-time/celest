<?php declare(strict_types=1);

namespace Celest\Temporal;

use Celest\DateTimeException;

abstract class AbstractTemporalAccessor implements TemporalAccessor
{
    /**
     * @inheritdoc
     */
    public function range(TemporalField $field) : ValueRange
    {
        if ($field instanceof ChronoField) {
            if ($this->isSupported($field)) {
                return $field->range();
            }

            throw new UnsupportedTemporalTypeException("Unsupported field: " . $field);
        }
        return $field->rangeRefinedBy($this);
    }

    /**
     * @inheritdoc
     */
    public function get(TemporalField $field) : int
    {
        $range = $this->range($field);
        if ($range->isIntValue() === false) {
            throw new UnsupportedTemporalTypeException("Invalid field " . $field . " for get() method, use getLong() instead");
        }

        $value = $this->getLong($field);
        if ($range->isValidValue($value) === false) {
            throw new DateTimeException("Invalid value for " . $field . " (valid values " . $range . "): " . $value);
        }
        return $value;
    }

    /**
     * @inheritdoc
     */
    public function query(TemporalQuery $query)
    {
        if ($query == TemporalQueries::zoneId()
            || $query == TemporalQueries::chronology()
            || $query == TemporalQueries::precision()
        ) {
            return null;
        }

        return $query->queryFrom($this);
    }

    /**
     * @inheritdoc
     */
    function __toString() : string
    {
        return get_class($this);
    }
}