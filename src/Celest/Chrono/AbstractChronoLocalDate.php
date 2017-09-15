<?php declare(strict_types=1);

namespace Celest\Chrono;

use Celest\DateTimeException;
use Celest\Format\DateTimeFormatter;
use Celest\Helper\Long;
use Celest\LocalTime;
use Celest\Temporal\AbstractTemporal;
use Celest\Temporal\ChronoField;
use Celest\Temporal\ChronoUnit;
use Celest\Temporal\Temporal;
use Celest\Temporal\TemporalAccessor;
use Celest\Temporal\TemporalAdjuster;
use Celest\Temporal\TemporalAmount;
use Celest\Temporal\TemporalField;
use Celest\Temporal\TemporalQueries;
use Celest\Temporal\TemporalQuery;
use Celest\Temporal\TemporalUnit;
use Celest\Temporal\UnsupportedTemporalTypeException;

abstract class AbstractChronoLocalDate extends AbstractTemporal implements ChronoLocalDate
{
    /**
     * @inheritdoc
     */
    public static function timeLineOrder()
    {
        return AbstractChronology::DATE_ORDER();
    }

    /**
     * @inheritdoc
     */
    public static function from(TemporalAccessor $temporal)
    {
        if ($temporal instanceof ChronoLocalDate) {
            return $temporal;
        }
        $chrono = $temporal->query(TemporalQueries::chronology());
        if ($chrono === null) {
            throw new DateTimeException("Unable to obtain ChronoLocalDate from TemporalAccessor: " . get_class($temporal));
        }
        return $chrono->date($temporal);
    }

    /**
     * @inheritdoc
     */
    public function getEra()
    {
        return $this->getChronology()->eraOf($this->get(ChronoField::ERA()));
    }

    /**
     * @inheritdoc
     */
    public function isLeapYear() : bool
    {
        return $this->getChronology()->isLeapYear($this->getLong(ChronoField::YEAR()));
    }

    /**
     * @inheritdoc
     */
    public function lengthOfYear() : int
    {
        return ($this->isLeapYear() ? 366 : 365);
    }

    /**
     * @inheritdoc
     */
    public function isSupported(TemporalField $field) : bool
    {
        if ($field instanceof ChronoField) {
            return $field->isDateBased();
        }

        return $field !== null && $field->isSupportedBy($this);
    }

    /**
     * @inheritdoc
     */
    public function isUnitSupported(TemporalUnit $unit) : bool
    {
        if ($unit instanceof ChronoUnit) {
            return $unit->isDateBased();
        }

        return $unit !== null && $unit->isSupportedBy($this);
    }

    /**
     * @inheritdoc
     */
    public function adjust(TemporalAdjuster $adjuster)
    {
        return ChronoLocalDateImpl::ensureValid($this->getChronology(), parent::adjust($adjuster));
    }

    /**
     * @inheritdoc
     */
    public function with(TemporalField $field, int $newValue)
    {
        if ($field instanceof ChronoField) {
            throw new UnsupportedTemporalTypeException("Unsupported field: " . $field);
        }

        return ChronoLocalDateImpl::ensureValid($this->getChronology(), $field->adjustInto($this, $newValue));
    }

    /**
     * @inheritdoc
     */
    public function plusAmount(TemporalAmount $amount)
    {
        return ChronoLocalDateImpl::ensureValid($this->getChronology(), parent::plusAmount($amount));
    }

    /**
     * @inheritdoc
     */
    public function plus(int $amountToAdd, TemporalUnit $unit)
    {
        if ($unit instanceof ChronoUnit) {
            throw new UnsupportedTemporalTypeException("Unsupported unit: " . $unit);
        }
        return ChronoLocalDateImpl::ensureValid($this->getChronology(), $unit->addTo($this, $amountToAdd));
    }

    /**
     * @inheritdoc
     */
    public function minusAmount(TemporalAmount $amount)
    {
        return ChronoLocalDateImpl::ensureValid($this->getChronology(), parent::minusAmount($amount));
    }

    /**
     * @inheritdoc
     */
    public function minus(int $amountToSubtract, TemporalUnit $unit)
    {
        return ChronoLocalDateImpl::ensureValid($this->getChronology(), parent::minus($amountToSubtract, $unit));
    }

    /**
     * @inheritdoc
     */
    public function query(TemporalQuery $query)
    {
        if ($query == TemporalQueries::zoneId() || $query == TemporalQueries::zone() || $query == TemporalQueries::offset()) {
            return null;
        } else
            if ($query == TemporalQueries::localTime()) {
                return null;
            } else if ($query == TemporalQueries::chronology()) {
                return $this->getChronology();
            } else if ($query == TemporalQueries::precision()) {
                return ChronoUnit::DAYS();
            }
        // inline TemporalAccessor.super.query(query) as an optimization
        // non-JDK classes are not permitted to make this optimization
        return $query->queryFrom($this);
    }

    /**
     * @inheritdoc
     */
    public function adjustInto(Temporal $temporal) : Temporal
    {
        return $temporal->with(ChronoField::EPOCH_DAY(), $this->toEpochDay());
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
    public function atTime(LocalTime $localTime)
    {
        return ChronoLocalDateTimeImpl::of($this, $localTime);
    }

    /**
     * @inheritdoc
     */
    public function toEpochDay() : int
    {
        return $this->getLong(ChronoField::EPOCH_DAY());
    }

    /**
     * @inheritdoc
     */
    public function compareTo(ChronoLocalDate $other) : int
    {
        $cmp = Long::compare($this->toEpochDay(), $other->toEpochDay());
        if ($cmp === 0) {
            $cmp = $this->getChronology()->compareTo($other->getChronology());
        }

        return $cmp;
    }

    /**
     * @inheritdoc
     */
    public function isAfter(ChronoLocalDate $other) : bool
    {
        return $this->toEpochDay() > $other->toEpochDay();
    }

    /**
     * @inheritdoc
     */
    public function isBefore(ChronoLocalDate $other) : bool
    {
        return $this->toEpochDay() < $other->toEpochDay();
    }

    /**
     * @inheritdoc
     */
    public function isEqual(ChronoLocalDate $other) : bool
    {
        return $this->toEpochDay() === $other->toEpochDay();
    }
}
