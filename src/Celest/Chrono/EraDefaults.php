<?php

namespace Celest\Chrono;

use Celest\Format\DateTimeFormatterBuilder;
use Celest\Format\TextStyle;
use Celest\Locale;
use Celest\Temporal\ChronoField;
use Celest\Temporal\ChronoUnit;
use Celest\Temporal\Temporal;
use Celest\Temporal\TemporalAccessorDefaults;
use Celest\Temporal\TemporalField;
use Celest\Temporal\TemporalQueries;
use Celest\Temporal\TemporalQuery;
use Celest\Temporal\UnsupportedTemporalTypeException;

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
        return (new DateTimeFormatterBuilder())->appendText2(ChronoField::ERA(), $style)->toFormatter2($locale)->format($_this);
    }

}