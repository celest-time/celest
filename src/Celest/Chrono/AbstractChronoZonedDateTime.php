<?php declare(strict_types=1);

namespace Celest\Chrono;


use Celest\DateTimeException;
use Celest\Format\DateTimeFormatter;
use Celest\Helper\Long;
use Celest\Instant;
use Celest\Temporal\AbstractTemporal;
use Celest\Temporal\ChronoField;
use Celest\Temporal\ChronoUnit;
use Celest\Temporal\TemporalAccessor;
use Celest\Temporal\TemporalAdjuster;
use Celest\Temporal\TemporalAmount;
use Celest\Temporal\TemporalField;
use Celest\Temporal\TemporalQueries;
use Celest\Temporal\TemporalQuery;
use Celest\Temporal\TemporalUnit;
use Celest\Temporal\UnsupportedTemporalTypeException;
use Celest\Temporal\ValueRange;

abstract class AbstractChronoZonedDateTime extends AbstractTemporal implements ChronoZonedDateTime
{
    /**
     * @inheritdoc
     */
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

    /**
     * @inheritdoc
     */
    public function range(TemporalField $field) : ValueRange
    {
        if ($field instanceof ChronoField) {
            if ($field == ChronoField::INSTANT_SECONDS() || $field == ChronoField::OFFSET_SECONDS()) {
                return $field->range();
            }

            return $this->toLocalDateTime()->range($field);
        }
        return $field->rangeRefinedBy($this);
    }

    /**
     * @inheritdoc
     */
    public function get(TemporalField $field) : int
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
        return parent::get($field);
    }

    /**
     * @inheritdoc
     */
    public function getLong(TemporalField $field) : int
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

    /**
     * @inheritdoc
     */
    public function toLocalDate()
    {
        return $this->toLocalDateTime()->toLocalDate();
    }

    /**
     * @inheritdoc
     */
    public function toLocalTime()
    {
        return $this->toLocalDateTime()->toLocalTime();
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
            return $unit != ChronoUnit::FOREVER();
        }

        return $unit !== null && $unit->isSupportedBy($this);
    }

    /**
     * @inheritdoc
     */
    public function adjust(TemporalAdjuster $adjuster)
    {
        return ChronoZonedDateTimeImpl::ensureValid($this->getChronology(), parent::adjust($adjuster));
    }

    /**
     * @inheritdoc
     */
    public function plusAmount(TemporalAmount $amount)
    {
        return ChronoZonedDateTimeImpl::ensureValid($this->getChronology(), parent::plusAmount($amount));
    }

    /**
     * @inheritdoc
     */
    public function minusAmount(TemporalAmount $amount)
    {
        return ChronoZonedDateTimeImpl::ensureValid($this->getChronology(), parent::minusAmount($amount));
    }

    /**
     * @inheritdoc
     */
    public function minus(int $amountToSubtract, TemporalUnit $unit)
    {
        return ChronoZonedDateTimeImpl::ensureValid($this->getChronology(), parent::minus($amountToSubtract, $unit));
    }

    /**
     * @inheritdoc
     */
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
    public function toInstant() : Instant
    {
        return Instant::ofEpochSecond($this->toEpochSecond(), $this->toLocalTime()->getNano());
    }

    /**
     * @inheritdoc
     */
    public function toEpochSecond() : int
    {
        $epochDay = $this->toLocalDate()->toEpochDay();
        $secs = $epochDay * 86400 + $this->toLocalTime()->toSecondOfDay();
        $secs -= $this->getOffset()->getTotalSeconds();
        return $secs;
    }

    /**
     * @inheritdoc
     */
    public function compareTo(ChronoZonedDateTime $other) : int
    {
        $cmp = Long::compare($this->toEpochSecond(), $other->toEpochSecond());
        if ($cmp === 0) {
            $cmp = $this->toLocalTime()->getNano() - $other->toLocalTime()->getNano();
            if ($cmp === 0) {
                $cmp = $this->toLocalDateTime()->compareTo($other->toLocalDateTime());
                if ($cmp === 0) {
                    $cmp = $this->getZone()->getId() === $other->getZone()->getId();
                    if ($cmp) {
                        $cmp = $this->getChronology()->compareTo($other->getChronology());
                    }
                }
            }
        }
        return $cmp;
    }

    /**
     * @inheritdoc
     */
    public function isBefore(ChronoZonedDateTime $other) : bool
    {
        $thisEpochSec = $this->toEpochSecond();
        $otherEpochSec = $other->toEpochSecond();
        return $thisEpochSec < $otherEpochSec ||
        ($thisEpochSec === $otherEpochSec && $this->toLocalTime()->getNano() < $other->toLocalTime()->getNano());
    }

    /**
     * @inheritdoc
     */
    public function isAfter(ChronoZonedDateTime $other) : bool
    {
        $thisEpochSec = $this->toEpochSecond();
        $otherEpochSec = $other->toEpochSecond();
        return $thisEpochSec > $otherEpochSec ||
        ($thisEpochSec === $otherEpochSec && $this->toLocalTime()->getNano() > $other->toLocalTime()->getNano());
    }

    /**
     * @inheritdoc
     */
    public function isEqual(ChronoZonedDateTime $other) : bool
    {
        return $this->toEpochSecond() === $other->toEpochSecond() &&
        $this->toLocalTime()->getNano() === $other->toLocalTime()->getNano();
    }

}