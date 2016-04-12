<?php

namespace Celest\Temporal\Misc;

use Celest\Chrono\AbstractChronology;
use Celest\Chrono\IsoChronology;
use Celest\Format\ResolverStyle;
use Celest\LocalDate;
use Celest\Locale;
use Celest\Temporal\ChronoField;
use Celest\Temporal\ChronoUnit;
use Celest\Temporal\FieldValues;
use Celest\Temporal\IsoFields;
use Celest\Temporal\Temporal;
use Celest\Temporal\TemporalAccessor;
use Celest\Temporal\TemporalField;
use Celest\Temporal\UnsupportedTemporalTypeException;

class WeekBasedYear implements TemporalField
{
    public function getBaseUnit()
    {
        return IsoFields::WEEK_BASED_YEARS();
    }

    public function getRangeUnit()
    {
        return ChronoUnit::FOREVER();
    }

    public function range()
    {
        return ChronoField::YEAR()->range();
    }

    public function isSupportedBy(TemporalAccessor $temporal)
    {
        return $temporal->isSupported(ChronoField::EPOCH_DAY()) && AbstractChronology::from($temporal)->equals(IsoChronology::INSTANCE());
    }

    public function getFrom(TemporalAccessor $temporal)
    {
        if ($this->isSupportedBy($temporal) === false) {
            throw new UnsupportedTemporalTypeException("Unsupported field: WeekBasedYear");
        }
        return IsoFields::getWeekBasedYear(LocalDate::from($temporal));
    }

    public function adjustInto(Temporal $temporal, $newValue)
    {
        if ($this->isSupportedBy($temporal) === false) {
            throw new UnsupportedTemporalTypeException("Unsupported field: WeekBasedYear");
        }
        $newWby = $this->range()->checkValidIntValue($newValue, IsoFields::WEEK_BASED_YEAR());  // strict check
        $date = LocalDate::from($temporal);
        $dow = $date->get(ChronoField::DAY_OF_WEEK());
        $week = IsoFields::getWeek($date);
        if ($week === 53 && IsoFields::getWeekRangeInt($newWby) === 52) {
            $week = 52;
        }
        $resolved = LocalDate::of($newWby, 1, 4);  // 4th is guaranteed to be in week one
        $days = ($dow - $resolved->get(ChronoField::DAY_OF_WEEK())) + (($week - 1) * 7);
        $resolved = $resolved->plusDays($days);
        return $temporal->adjust($resolved);
    }

    public function __toString()
    {
        return "WeekBasedYear";
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
