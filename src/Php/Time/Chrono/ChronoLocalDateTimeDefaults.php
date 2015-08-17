<?php

namespace Php\Time\Chrono;


use Php\Time\DateTimeException;
use Php\Time\Format\DateTimeFormatter;
use Php\Time\Instant;
use Php\Time\Temporal\ChronoField;
use Php\Time\Temporal\ChronoUnit;
use Php\Time\Temporal\Temporal;
use Php\Time\Temporal\TemporalAccessor;
use Php\Time\Temporal\TemporalAdjuster;
use Php\Time\Temporal\TemporalAmount;
use Php\Time\Temporal\TemporalDefaults;
use Php\Time\Temporal\TemporalQuery;
use Php\Time\Temporal\TemporalUnit;
use Php\Time\ZoneOffset;

final class ChronoLocalDateTimeDefaults
{
    private function __construct()
    {
    }

    public static function timeLineOrder()
    {
        return AbstractChronology::DATE_TIME_ORDER();
    }

    public static function from(TemporalAccessor $temporal)
    {
        if ($temporal instanceof ChronoLocalDateTime) {
            return $temporal;
        }

        $chrono = $temporal->query(TemporalQueries::chronology());
        if ($chrono == null) {
            throw new DateTimeException("Unable to obtain ChronoLocalDateTime from TemporalAccessor: " . get_class($temporal));
        }
        return $chrono->localDateTime($temporal);
    }

    public static function getChronology(ChronoLocalDateTime $_this)
    {
        return $_this->toLocalDate()->getChronology();
    }

    public static function isSupported(ChronoLocalDateTime $_this, TemporalUnit $unit)
    {
        if ($unit instanceof ChronoUnit) {
            return $unit != ChronoUnit::FOREVER();
        }

        return $unit != null && $unit->isSupportedBy($_this);
    }

    public static function with(ChronoLocalDateTime $_this, TemporalAdjuster $adjuster)
    {
        return ChronoLocalDateTimeImpl::ensureValid($_this->getChronology(), TemporalDefaults::with($_this, $adjuster));
    }

    public static function plus(ChronoLocalDateTime $_this, TemporalAmount $amount)
    {
        return ChronoLocalDateTimeImpl::ensureValid($_this->getChronology(), TemporalDefaults::plus($_this, $amount));
    }

    public static function minus(ChronoLocalDateTime $_this, TemporalAmount $amount)
    {
        return ChronoLocalDateTimeImpl::ensureValid($_this->getChronology(), TemporalDefaults::minus($_this, $amount));
    }

    public static function minus(ChronoLocalDateTime $_this, $amountToSubtract, TemporalUnit $unit)
    {
        return ChronoLocalDateTimeImpl::ensureValid($_this->getChronology(), TemporalDefaults::minus($_this, $amountToSubtract, $unit));
    }

    public static function query(ChronoLocalDateTime $_this, TemporalQuery $query)
    {
        if ($query == TemporalQueries::zoneId() || $query == TemporalQueries::zone() || $query == TemporalQueries::offset()) {
            return null;
        } else if ($query == TemporalQueries::localTime()) {
            return $_this->toLocalTime();
        } else if ($query == TemporalQueries::chronology()) {
            return $_this->getChronology();
        } else if ($query == TemporalQueries::precision()) {
            return $query;
        }
        // inline TemporalAccessor.super.query(query) as an optimization
        // non-JDK classes are not permitted to make this optimization
        return $query->queryFrom($_this);
    }

    public static function adjustInto(ChronoLocalDateTime $_this, Temporal $temporal)
    {
        return $temporal
            ->with(ChronoField::EPOCH_DAY(), $_this->toLocalDate()->toEpochDay())
            ->with(ChronoField::NANO_OF_DAY(), $_this->toLocalTime()->toNanoOfDay());
    }

    public static function format(ChronoLocalDateTime $_this, DateTimeFormatter $formatter)
    {
        return $formatter->format($_this);
    }

    public static function  toInstant(ChronoLocalDateTime $_this, ZoneOffset $offset)
    {
        return Instant::ofEpochSecond($_this->toEpochSecond($offset), $_this->toLocalTime()->getNano());
    }

    public static function toEpochSecond(ChronoLocalDateTime $_this, ZoneOffset $offset)
    {
        $epochDay = $_this->toLocalDate()->toEpochDay();
        $secs = $epochDay * 86400 + $_this->toLocalTime()->toSecondOfDay();
        $secs -= $offset->getTotalSeconds();
        return $secs;
    }

    public static function compareTo(ChronoLocalDateTime $_this, ChronoLocalDateTime $other)
    {
        $cmp = $_this->toLocalDate()->compareTo($other->toLocalDate());
        if ($cmp == 0) {
            $cmp = $_this->toLocalTime()->compareTo($other->toLocalTime());
            if ($cmp == 0) {
                $cmp = $_this->getChronology()->compareTo($other->getChronology());
            }
        }
        return $cmp;
    }

    function isAfter(ChronoLocalDateTime $_this, ChronoLocalDateTime $other)
    {
        $thisEpDay = $_this->toLocalDate()->toEpochDay();
        $otherEpDay = $other->toLocalDate()->toEpochDay();
        return $thisEpDay > $otherEpDay ||
        ($thisEpDay == $otherEpDay && $_this->toLocalTime()->toNanoOfDay() > $other->toLocalTime()->toNanoOfDay());
    }

    function isBefore(ChronoLocalDateTime $_this, ChronoLocalDateTime $other)
    {
        $thisEpDay = $_this->toLocalDate()->toEpochDay();
        $otherEpDay = $other->toLocalDate()->toEpochDay();
        return $thisEpDay < $otherEpDay ||
        ($thisEpDay == $otherEpDay && $_this->toLocalTime()->toNanoOfDay() < $other->toLocalTime()->toNanoOfDay());
    }

    function isEqual(ChronoLocalDateTime $_this, ChronoLocalDateTime $other)
    {
        // Do the time check first, it is cheaper than computing EPOCH day.
        return $_this->toLocalTime()->toNanoOfDay() == $other->toLocalTime()->toNanoOfDay() &&
        $_this->toLocalDate()->toEpochDay() == $other->toLocalDate()->toEpochDay();
    }
}