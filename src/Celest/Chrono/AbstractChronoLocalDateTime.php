<?php

namespace Celest\Chrono;


use Celest\DateTimeException;
use Celest\Format\DateTimeFormatter;
use Celest\Instant;
use Celest\Temporal\AbstractTemporal;
use Celest\Temporal\ChronoField;
use Celest\Temporal\ChronoUnit;
use Celest\Temporal\Temporal;
use Celest\Temporal\TemporalAccessor;
use Celest\Temporal\TemporalAdjuster;
use Celest\Temporal\TemporalAmount;
use Celest\Temporal\TemporalQueries;
use Celest\Temporal\TemporalQuery;
use Celest\Temporal\TemporalUnit;
use Celest\ZoneOffset;

abstract class AbstractChronoLocalDateTime extends AbstractTemporal implements ChronoLocalDateTime
{
    /**
     * @inheritdoc
     */
    public static function timeLineOrder()
    {
        return AbstractChronology::DATE_TIME_ORDER();
    }

    /**
     * @inheritdoc
     */
    public static function from(TemporalAccessor $temporal)
    {
        if ($temporal instanceof ChronoLocalDateTime) {
            return $temporal;
        }

        $chrono = $temporal->query(TemporalQueries::chronology());
        if ($chrono === null) {
            throw new DateTimeException("Unable to obtain ChronoLocalDateTime from TemporalAccessor: " . get_class($temporal));
        }
        return $chrono->localDateTime($temporal);
    }

    /**
     * @inheritdoc
     */
    public function getChronology()
    {
        return $this->toLocalDate()->getChronology();
    }

    /**
     * @inheritdoc
     */
    public function isUnitSupported(TemporalUnit $unit) : bool
    {
        if ($unit instanceof ChronoUnit) {
            return $unit !== ChronoUnit::FOREVER();
        }

        return $unit !== null && $unit->isSupportedBy($this);
    }

    /**
     * @inheritdoc
     */
    public function adjust(TemporalAdjuster $adjuster)
    {
        return ChronoLocalDateTimeImpl::ensureValid($this->getChronology(), parent::adjust($adjuster));
    }

    /**
     * @inheritdoc
     */
    public function plusAmount(TemporalAmount $amount)
    {
        return ChronoLocalDateTimeImpl::ensureValid($this->getChronology(), parent::plusAmount($amount));
    }

    /**
     * @inheritdoc
     */
    public function minusAmount(TemporalAmount $amount)
    {
        return ChronoLocalDateTimeImpl::ensureValid($this->getChronology(), parent::minusAmount($amount));
    }

    /**
     * @inheritdoc
     */
    public function minus(int $amountToSubtract, TemporalUnit $unit)
    {
        return ChronoLocalDateTimeImpl::ensureValid($this->getChronology(), parent::minus($amountToSubtract, $unit));
    }

    /**
     * @inheritdoc
     */
    public function query(TemporalQuery $query)
    {
        if ($query == TemporalQueries::zoneId() || $query == TemporalQueries::zone() || $query == TemporalQueries::offset()) {
            return null;
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

    /**
     * @inheritdoc
     */
    public function adjustInto(Temporal $temporal)
    {
        return $temporal
            ->with(ChronoField::EPOCH_DAY(), $this->toLocalDate()->toEpochDay())
            ->with(ChronoField::NANO_OF_DAY(), $this->toLocalTime()->toNanoOfDay());
    }

    /**
     * @inheritdoc
     */
    public function format(DateTimeFormatter $formatter) : string
    {
        return $formatter->format($this);
    }

    /**
     * @inheritdoc
     */
    public function toInstant(ZoneOffset $offset) : Instant
    {
        return Instant::ofEpochSecond($this->toEpochSecond($offset), $this->toLocalTime()->getNano());
    }

    /**
     * @inheritdoc
     */
    public function toEpochSecond(ZoneOffset $offset) : int
    {
        $epochDay = $this->toLocalDate()->toEpochDay();
        $secs = $epochDay * 86400 + $this->toLocalTime()->toSecondOfDay();
        $secs -= $offset->getTotalSeconds();
        return $secs;
    }

    /**
     * @inheritdoc
     */
    public function compareTo(ChronoLocalDateTime $other) : int
    {
        $cmp = $this->toLocalDate()->compareTo($other->toLocalDate());
        if ($cmp === 0) {
            $cmp = $this->toLocalTime()->compareTo($other->toLocalTime());
            if ($cmp === 0) {
                $cmp = $this->getChronology()->compareTo($other->getChronology());
            }
        }
        return $cmp;
    }

    /**
     * @inheritdoc
     */
    public function isAfter(ChronoLocalDateTime $other) : bool
    {
        $thisEpDay = $this->toLocalDate()->toEpochDay();
        $otherEpDay = $other->toLocalDate()->toEpochDay();
        return $thisEpDay > $otherEpDay ||
        ($thisEpDay === $otherEpDay && $this->toLocalTime()->toNanoOfDay() > $other->toLocalTime()->toNanoOfDay());
    }

    /**
     * @inheritdoc
     */
    public function isBefore(ChronoLocalDateTime $other) : bool
    {
        $thisEpDay = $this->toLocalDate()->toEpochDay();
        $otherEpDay = $other->toLocalDate()->toEpochDay();
        return $thisEpDay < $otherEpDay ||
        ($thisEpDay === $otherEpDay && $this->toLocalTime()->toNanoOfDay() < $other->toLocalTime()->toNanoOfDay());
    }

    /**
     * @inheritdoc
     */
    public function isEqual(ChronoLocalDateTime $other) : bool
    {
        // Do the time check first, it is cheaper than computing EPOCH day.
        return $this->toLocalTime()->toNanoOfDay() === $other->toLocalTime()->toNanoOfDay() &&
        $this->toLocalDate()->toEpochDay() === $other->toLocalDate()->toEpochDay();
    }
}
