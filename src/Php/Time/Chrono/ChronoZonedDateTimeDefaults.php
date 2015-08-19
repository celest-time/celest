<?php

namespace Php\Time\Chrono;


use Php\Time\DateTimeException;
use Php\Time\Format\DateTimeFormatter;
use Php\Time\Helper\Long;
use Php\Time\Instant;
use Php\Time\Temporal\ChronoField;
use Php\Time\Temporal\ChronoUnit;
use Php\Time\Temporal\TemporalAccessor;
use Php\Time\Temporal\TemporalAccessorDefaults;
use Php\Time\Temporal\TemporalAdjuster;
use Php\Time\Temporal\TemporalAmount;
use Php\Time\Temporal\TemporalDefaults;
use Php\Time\Temporal\TemporalField;
use Php\Time\Temporal\TemporalQuery;
use Php\Time\Temporal\TemporalUnit;
use Php\Time\UnsupportedTemporalTypeException;

final class ChronoZonedDateTimeDefaults
{
    private function __construct()
    {
    }

    public static function from(ChronoZonedDateTime $_this, TemporalAccessor $temporal)
    {
        if ($temporal instanceof ChronoZonedDateTime) {
            return $temporal;
        }

        $chrono = $temporal->query(TemporalQueries::chronology());
        if ($chrono == null) {
            throw new DateTimeException("Unable to obtain ChronoZonedDateTime from TemporalAccessor: " . get_class($temporal));
        }
        return $chrono->zonedDateTime($temporal);
    }

    public static function range(ChronoZonedDateTime $_this, TemporalField $field)
    {
        if ($field instanceof ChronoField) {
            if ($field == ChronoField::INSTANT_SECONDS() || $field == ChronoField::OFFSET_SECONDS()) {
                return $field->range();
            }

            return $_this->toLocalDateTime()->range($field);
        }
        return $field->rangeRefinedBy($_this);
    }

    public static function get(ChronoZonedDateTime $_this, TemporalField $field)
    {
        if ($field instanceof ChronoField) {
            switch ($field) {
                case ChronoField::INSTANT_SECONDS():
                    throw new UnsupportedTemporalTypeException("Invalid field 'InstantSeconds' for get() method, use getLong() instead");
                case ChronoField::OFFSET_SECONDS():
                    return $_this->getOffset()->getTotalSeconds();
            }

            return $_this->toLocalDateTime()->get($field);
        }
        return TemporalAccessorDefaults::get($_this, $field);
    }

    public static function getLong(ChronoZonedDateTime $_this, TemporalField $field)
    {
        if ($field instanceof ChronoField) {
            switch ($field) {
                case ChronoField::INSTANT_SECONDS():
                    return $_this->toEpochSecond();
                case ChronoField::OFFSET_SECONDS():
                    return $_this->getOffset()->getTotalSeconds();
            }
            return $_this->toLocalDateTime()->getLong($field);
        }
        return $field->getFrom($_this);
    }

    public static function toLocalDate(ChronoZonedDateTime $_this)
    {
        return $_this->toLocalDateTime()->toLocalDate();
    }

    public static function toLocalTime(ChronoZonedDateTime $_this)
    {
        return $_this->toLocalDateTime()->toLocalTime();
    }

    public static function getChronology(ChronoZonedDateTime $_this)
    {
        return $_this->toLocalDate()->getChronology();
    }

    function isSupported(ChronoZonedDateTime $_this, TemporalUnit $unit)
    {
        if ($unit instanceof ChronoUnit) {
            return $unit != ChronoUnit::FOREVER();
        }

        return $unit != null && $unit->isSupportedBy($_this);
    }

    function with(ChronoZonedDateTime $_this, TemporalAdjuster $adjuster)
    {
        return ChronoZonedDateTimeImpl::ensureValid($_this->getChronology(), TemporalDefaults::with($_this, $adjuster));
}

    function plus(ChronoZonedDateTime $_this, TemporalAmount $amount)
    {
        return ChronoZonedDateTimeImpl::ensureValid($_this->getChronology(), TemporalDefaults::plus($_this, $amount));
}

    function minus(ChronoZonedDateTime $_this, TemporalAmount $amount)
    {
        return ChronoZonedDateTimeImpl::ensureValid($_this->getChronology(), TemporalDefaults::minus($_this, $amount));
}

    function minus(ChronoZonedDateTime $_this, $amountToSubtract, TemporalUnit $unit)
    {
        return ChronoZonedDateTimeImpl::ensureValid($_this->getChronology(), TemporalDefaults::minus($_this, $amountToSubtract, $unit));
}

    function query(ChronoZonedDateTime $_this, TemporalQuery $query)
    {
        if ($query == TemporalQueries::zone() || $query == TemporalQueries::zoneId()) {
            return $_this->getZone();
        } else if ($query == TemporalQueries::offset()) {
            return $_this->getOffset();
        } else if ($query == TemporalQueries::localTime()) {
            return $_this->toLocalTime();
        } else if ($query == TemporalQueries::chronology()) {
            return $_this->getChronology();
        } else if ($query == TemporalQueries::precision()) {
            return ChronoUnit::NANOS();
        }
        // inline TemporalAccessor.super.query(query) as an optimization
        // non-JDK classes are not permitted to make this optimization
        return $query->queryFrom($_this);
    }

    function format(ChronoZonedDateTime $_this, DateTimeFormatter $formatter)
    {
        return $formatter->format($_this);
    }

    function toInstant(ChronoZonedDateTime $_this)
    {
        return Instant::ofEpochSecond($_this->toEpochSecond(), $_this->toLocalTime()->getNano());
    }

    function toEpochSecond(ChronoZonedDateTime $_this)
    {
        $epochDay = $_this->toLocalDate()->toEpochDay();
        $secs = $epochDay * 86400 + $_this->toLocalTime()->toSecondOfDay();
        $secs -= $_this->getOffset()->getTotalSeconds();
        return $secs;
    }

    function compareTo(ChronoZonedDateTime $_this, ChronoZonedDateTime $other)
    {
        $cmp = Long::compare($_this->toEpochSecond(), $other->toEpochSecond());
        if ($cmp == 0) {
            $cmp = $_this->toLocalTime()->getNano() - $other->toLocalTime()->getNano();
            if ($cmp == 0) {
                $cmp = $_this->toLocalDateTime()->compareTo($other->toLocalDateTime());
                if ($cmp == 0) {
                    $cmp = $_this->getZone()->getId()->compareTo($other->getZone()->getId());
                    if ($cmp == 0) {
                        $cmp = $_this->getChronology()->compareTo($other->getChronology());
                    }
                }
            }
        }
        return $cmp;
    }

    function isBefore(ChronoZonedDateTime $_this, ChronoZonedDateTime $other)
    {
        $thisEpochSec = $_this->toEpochSecond();
        $otherEpochSec = $other->toEpochSecond();
        return $thisEpochSec < $otherEpochSec ||
        ($thisEpochSec == $otherEpochSec && $_this->toLocalTime()->getNano() < $other->toLocalTime()->getNano());
    }

    function isAfter(ChronoZonedDateTime $_this, ChronoZonedDateTime $other)
    {
        $thisEpochSec = $_this->toEpochSecond();
        $otherEpochSec = $other->toEpochSecond();
        return $thisEpochSec > $otherEpochSec ||
        ($thisEpochSec == $otherEpochSec && $_this->toLocalTime()->getNano() > $other->toLocalTime()->getNano());
    }

    function isEqual(ChronoZonedDateTime $_this, ChronoZonedDateTime $other)
    {
        return $_this->toEpochSecond() == $other->toEpochSecond() &&
        $_this->toLocalTime()->getNano() == $other->toLocalTime()->getNano();
    }

}