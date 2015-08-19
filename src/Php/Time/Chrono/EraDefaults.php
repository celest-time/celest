<?php

namespace Php\Time\Chrono;

use Php\Time\Temporal\ChronoField;
use Php\Time\Temporal\ChronoUnit;
use Php\Time\Temporal\Temporal;
use Php\Time\Temporal\TemporalAccessorDefaults;
use Php\Time\Temporal\TemporalField;
use Php\Time\Temporal\TemporalQueries;
use Php\Time\Temporal\TemporalQuery;
use Php\Time\UnsupportedTemporalTypeException;

final class EraDefaults
{
    private function __construct() {}

    public static function isSupported(Era $_this, TemporalField $field)
    {
        if ($field instanceof ChronoField) {
            return $field == ChronoField::ERA();
        }
        return $field != null && $field->isSupportedBy($_this);
    }

    public static function range(Era $_this, TemporalField $field)
    {
        return TemporalAccessorDefaults::range($_this, $field);
    }

    public static function get(Era $_this, TemporalField $field)
    {
        if ($field == ChronoField::ERA()) {
            return $_this->getValue();
        }

        return TemporalAccessorDefaults::get($_this, $field);
    }

    public static function getLong(Era $_this, TemporalField $field)
    {
        if ($field == ChronoField::ERA()) {
            return $_this->getValue();
        } else
            if ($field instanceof ChronoField) {
                throw new UnsupportedTemporalTypeException("Unsupported field: " . $field);
            }
        return $field->getFrom($_this);
    }

    public static function query(Era $_this, TemporalQuery $query)
    {
        if ($query == TemporalQueries::precision()) {
            return ChronoUnit::ERAS();
        }

        return TemporalAccessorDefaults::query($_this, $query);
    }

    public static function adjustInto(Era $_this, Temporal $temporal)
    {
        return $temporal->with(ChronoField::ERA(), $_this->getValue());
    }

//-----------------------------------------------------------------------

    public static function getDisplayName(Era $_this, TextStyle $style, Locale $locale)
    {
        return (new DateTimeFormatterBuilder())->appendText(ChronoField::ERA(), $style)->toFormatter($locale)->format($_this);
    }

}