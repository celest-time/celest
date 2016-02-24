<?php

namespace Celest\Temporal\Misc;

use Celest\Chrono\ChronologyDefaults;
use Celest\DateTimeException;
use Celest\Format\ResolverStyle;
use Celest\Helper\Math;
use Celest\Locale;
use Celest\Temporal\FieldValues;
use Celest\Temporal\ChronoField as CF;
use Celest\Temporal\Temporal;
use Celest\Temporal\TemporalAccessor;
use Celest\Temporal\TemporalField;
use Celest\Temporal\TemporalUnit;
use Celest\Temporal\ValueRange;

/**
 * Implementation of JulianFields.  Each instance is a singleton.
 * @internal
 */
final class JulianField implements TemporalField
{
    /** @var string */
    private $name;
    /** @var TemporalUnit */
    private $baseUnit;
    /** @var TemporalUnit */
    private $rangeUnit;
    /** @var ValueRange */
    private $range;
    /** @var int */
    private $offset;

    public function __construct($name, TemporalUnit $baseUnit, TemporalUnit $rangeUnit, $offset)
    {
        $this->name = $name;
        $this->baseUnit = $baseUnit;
        $this->rangeUnit = $rangeUnit;
        $this->range = ValueRange::of(-365243219162 + $offset, 365241780471 + $offset);
        $this->offset = $offset;
    }

//-----------------------------------------------------------------------
    public function getBaseUnit()
    {
        return $this->baseUnit;
    }

    public function getRangeUnit()
    {
        return $this->rangeUnit;
    }

    public function isDateBased()
    {
        return true;
    }

    public function isTimeBased()
    {
        return false;
    }

    public function range()
    {
        return $this->range;
    }

    //-----------------------------------------------------------------------
    public function isSupportedBy(TemporalAccessor $temporal)
    {
        return $temporal->isSupported(CF::EPOCH_DAY());
    }

    public
    function rangeRefinedBy(TemporalAccessor $temporal)
    {
        if ($this->isSupportedBy($temporal) == false) {
            throw new DateTimeException("Unsupported field: " . $this);
        }
        return $this->range();
    }

    public function getFrom(TemporalAccessor $temporal)
    {
        return $temporal->getLong(CF::EPOCH_DAY()) + $this->offset;
    }

    public function adjustInto(Temporal $temporal, $newValue)
    {
        if ($this->range()->isValidValue($newValue) == false) {
            throw new DateTimeException("Invalid value: " . $this->name . " " . $newValue);
        }
        return $temporal->with(CF::EPOCH_DAY(), Math::subtractExact($newValue, $this->offset));
        }

    //-----------------------------------------------------------------------
    public function resolve(FieldValues $fieldValues, TemporalAccessor $partialTemporal, ResolverStyle $resolverStyle)
    {
        $value = $fieldValues->remove($this);
            $chrono = ChronologyDefaults::from($partialTemporal);
            if ($resolverStyle == ResolverStyle::LENIENT()) {
                return $chrono->dateEpochDay(Math::subtractExact($value, $this->offset));
            }
            $this->range()->checkValidValue($value, $this);
            return $chrono->dateEpochDay($value - $this->offset);
        }

    //-----------------------------------------------------------------------
    public function __toString()
    {
        return $this->name;
    }

    public function getDisplayName(Locale $locale)
    {
        return $this->__toString();
    }
}