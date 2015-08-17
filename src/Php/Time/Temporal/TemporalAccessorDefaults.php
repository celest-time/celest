<?php

namespace Php\Time\Temporal;

use Php\Time\DateTimeException;
use Php\Time\UnsupportedTemporalTypeException;

final class TemporalAccessorDefaults
{
    private function __construct() {}

    public static function range(TemporalAccessor $_this, TemporalField $field)
    {
        if ($field instanceof ChronoField) {
            if ($_this->isSupported($field)) {
                return $field->range();
            }

            throw new UnsupportedTemporalTypeException("Unsupported field: " . $field);
        }
        return $field->rangeRefinedBy($_this);
    }

    static public function get(TemporalAccessor $_this, TemporalField $field)
    {
        $range = $_this->range($field);
        if ($range->isIntValue() == false) {
            throw new UnsupportedTemporalTypeException("Invalid field " . $field . " for get() method, use getLong() instead");
        }

        $value = $_this->getLong($field);
        if ($range->isValidValue($value) == false) {
            throw new DateTimeException("Invalid value for " . $field . " (valid values " . $range . "): " . $value);
        }
        return (int)$value;
    }

    static public function query(TemporalAccessor $_this, TemporalQuery $query)
    {
        if ($query == TemporalQueries::zoneId()
            || $query == TemporalQueries::chronology()
            || $query == TemporalQueries::precision()
        ) {
            return null;
        }

        return $query->queryFrom($_this);
    }
}