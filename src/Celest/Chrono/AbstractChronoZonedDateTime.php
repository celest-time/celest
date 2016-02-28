<?php

namespace Celest\Chrono;


use Celest\DateTimeException;
use Celest\Format\DateTimeFormatter;
use Celest\Helper\Long;
use Celest\Instant;
use Celest\Temporal\ChronoField;
use Celest\Temporal\ChronoUnit;
use Celest\Temporal\TemporalAccessor;
use Celest\Temporal\TemporalAccessorDefaults;
use Celest\Temporal\TemporalAdjuster;
use Celest\Temporal\TemporalAmount;
use Celest\Temporal\TemporalDefaults;
use Celest\Temporal\TemporalField;
use Celest\Temporal\TemporalQueries;
use Celest\Temporal\TemporalQuery;
use Celest\Temporal\TemporalUnit;
use Celest\Temporal\UnsupportedTemporalTypeException;

abstract class AbstractChronoZonedDateTime implements ChronoZonedDateTime
{
    public static function from(TemporalAccessor $temporal)
    {
        if ($temporal instanceof ChronoZonedDateTime) {
            return $temporal;
        }

        /** @var Chronology $chrono */
        $chrono = $temporal->query(TemporalQueries::chronology());
        if ($chrono === null) {
            throw new DateTimeException("Unable to obtain ChronoZonedDateTime from TemporalAccessor: " . get_class($temporal));
        }
        return $chrono->zonedDateTimeFrom($temporal);
    }

    public function range(TemporalField $field)
    {
        if ($field instanceof ChronoField) {
            if ($field == ChronoField::INSTANT_SECONDS() || $field == ChronoField::OFFSET_SECONDS()) {
                return $field->range();
            }

            return $this->toLocalDateTime()->range($field);
        }
        return $field->rangeRefinedBy($this);
    }

    public function get(TemporalField $field)
    {
        if ($field instanceof ChronoField) {
            switch ($field) {
                case ChronoField::INSTANT_SECONDS():
                    throw new UnsupportedTemporalTypeException("Invalid field 'InstantSeconds' for get() method, use getLong() instead");
                case ChronoField::OFFSET_SECONDS():
                    return $this->getOffset()->getTotalSeconds();
            }

            return $this->toLocalDateTime()->get($field);
        }
        return TemporalAccessorDefaults::get($this, $field);
    }

    public function getLong(TemporalField $field)
    {
        if ($field instanceof ChronoField) {
            switch ($field) {
                case ChronoField::INSTANT_SECONDS():
                    return $this->toEpochSecond();
                case ChronoField::OFFSET_SECONDS():
                    return $this->getOffset()->getTotalSeconds();
            }
            return $this->toLocalDateTime()->getLong($field);
        }
        return $field->getFrom($this);
    }

    public function toLocalDate()
    {
        return $this->toLocalDateTime()->toLocalDate();
    }

    public function toLocalTime()
    {
        return $this->toLocalDateTime()->toLocalTime();
    }

    public function getChronology()
    {
        return $this->toLocalDate()->getChronology();
    }

    public function isUnitSupported(TemporalUnit $unit)
    {
        if ($unit instanceof ChronoUnit) {
            return $unit != ChronoUnit::FOREVER();
        }

        return $unit != null && $unit->isSupportedBy($this);
    }

    public function adjust(TemporalAdjuster $adjuster)
    {
        return ChronoZonedDateTimeImpl::ensureValid($this->getChronology(), TemporalDefaults::adjust($this, $adjuster));
}

    public function plusAmount(TemporalAmount $amount)
    {
        return ChronoZonedDateTimeImpl::ensureValid($this->getChronology(), TemporalDefaults::plusAmount($this, $amount));
}

    public function minusAmount(TemporalAmount $amount)
    {
        return ChronoZonedDateTimeImpl::ensureValid($this->getChronology(), TemporalDefaults::minusAmount($this, $amount));
}

    public function minus($amountToSubtract, TemporalUnit $unit)
    {
        return ChronoZonedDateTimeImpl::ensureValid($this->getChronology(), TemporalDefaults::minus($this, $amountToSubtract, $unit));
}

    public function query(TemporalQuery $query)
    {
        if ($query == TemporalQueries::zone() || $query == TemporalQueries::zoneId()) {
            return $this->getZone();
        } else if ($query == TemporalQueries::offset()) {
            return $this->getOffset();
        } else if ($query == TemporalQueries::localTime()) {
            return $this->toLocalTime();
        } else if ($query == TemporalQueries::chronology()) {
            return $this->getChronology();
        } else if ($query == TemporalQueries::precision()) {
            return ChronoUnit::NANOS();
        }
        // inline TemporalAccessor.super.query(query) as an optimization
        // non-JDK classes are not permitted to make this optimization
        return $query->queryFrom($this);
    }

    public function format(DateTimeFormatter $formatter)
    {
        return $formatter->format($this);
    }

    public function toInstant()
    {
        return Instant::ofEpochSecond($this->toEpochSecond(), $this->toLocalTime()->getNano());
    }

    public function toEpochSecond()
    {
        $epochDay = $this->toLocalDate()->toEpochDay();
        $secs = $epochDay * 86400 + $this->toLocalTime()->toSecondOfDay();
        $secs -= $this->getOffset()->getTotalSeconds();
        return $secs;
    }

    public function compareTo(ChronoZonedDateTime $other)
    {
        $cmp = Long::compare($this->toEpochSecond(), $other->toEpochSecond());
        if ($cmp == 0) {
            $cmp = $this->toLocalTime()->getNano() - $other->toLocalTime()->getNano();
            if ($cmp == 0) {
                $cmp = $this->toLocalDateTime()->compareTo($other->toLocalDateTime());
                if ($cmp == 0) {
                    $cmp = $this->getZone()->getId()->compareTo($other->getZone()->getId());
                    if ($cmp == 0) {
                        $cmp = $this->getChronology()->compareTo($other->getChronology());
                    }
                }
            }
        }
        return $cmp;
    }

    public function isBefore(ChronoZonedDateTime $other)
    {
        $thisEpochSec = $this->toEpochSecond();
        $otherEpochSec = $other->toEpochSecond();
        return $thisEpochSec < $otherEpochSec ||
        ($thisEpochSec === $otherEpochSec && $this->toLocalTime()->getNano() < $other->toLocalTime()->getNano());
    }

    public function isAfter(ChronoZonedDateTime $other)
    {
        $thisEpochSec = $this->toEpochSecond();
        $otherEpochSec = $other->toEpochSecond();
        return $thisEpochSec > $otherEpochSec ||
        ($thisEpochSec === $otherEpochSec && $this->toLocalTime()->getNano() > $other->toLocalTime()->getNano());
    }

    public function isEqual(ChronoZonedDateTime $other)
    {
        return $this->toEpochSecond() === $other->toEpochSecond() &&
        $this->toLocalTime()->getNano() === $other->toLocalTime()->getNano();
    }

}