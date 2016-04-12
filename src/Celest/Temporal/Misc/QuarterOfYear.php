<?php

namespace Celest\Temporal\Misc;

use Celest\Format\ResolverStyle;
use Celest\Helper\Math;
use Celest\Locale;
use Celest\Temporal\ChronoField;
use Celest\Temporal\ChronoUnit;
use Celest\Temporal\FieldValues;
use Celest\Temporal\IsoFields;
use Celest\Temporal\Temporal;
use Celest\Temporal\TemporalAccessor;
use Celest\Temporal\TemporalField;
use Celest\Temporal\UnsupportedTemporalTypeException;
use Celest\Temporal\ValueRange;

class QuarterOfYear implements TemporalField
{
    public function getBaseUnit()
    {
        return IsoFields::QUARTER_YEARS();
    }

    public function getRangeUnit()
    {
        return ChronoUnit::YEARS();
    }

    public function range()
    {
        return ValueRange::of(1, 4);
    }

    public function isSupportedBy(TemporalAccessor $temporal)
    {
        return $temporal->isSupported(ChronoField::MONTH_OF_YEAR()) && IsoFields::isIso($temporal);
    }

    public function getFrom(TemporalAccessor $temporal)
    {
        if ($this->isSupportedBy($temporal) === false) {
            throw new UnsupportedTemporalTypeException("Unsupported field: QuarterOfYear");
        }
        $moy = $temporal->getLong(ChronoField::MONTH_OF_YEAR());
        return Math::div(($moy + 2), 3);
    }

    public function adjustInto(Temporal $temporal, $newValue)
    {
        // calls getFrom() to check if supported
        $curValue = $this->getFrom($temporal);
        $this->range()->checkValidValue($newValue, $this);  // strictly check from 1 to 4
        return $temporal->with(ChronoField::MONTH_OF_YEAR(), $temporal->getLong(ChronoField::MONTH_OF_YEAR()) + ($newValue - $curValue) * 3);
    }

    public function __toString()
    {
        return "QuarterOfYear";
    }

    public function getDisplayName(Locale $locale)
    {
        return $this->__toString();
    }

    public function isDateBased()
    {
        return true;
    }

    public function isTimeBased()
    {
        return false;
    }


    public function rangeRefinedBy(TemporalAccessor $temporal)
    {
        return $this->range();
    }

    public function resolve(
        FieldValues $fieldValues,
        TemporalAccessor $partialTemporal,
        ResolverStyle $resolverStyle)
    {
        return null;
    }
}
