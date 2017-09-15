<?php declare(strict_types=1);

namespace Celest\Temporal\Misc;

use Celest\Chrono\AbstractChronology;
use Celest\DateTimeException;
use Celest\Format\ResolverStyle;
use Celest\Helper\Math;
use Celest\Locale;
use Celest\Temporal\ChronoField as CF;
use Celest\Temporal\FieldValues;
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

    public function __construct(string $name, TemporalUnit $baseUnit, TemporalUnit $rangeUnit, int $offset)
    {
        $this->name = $name;
        $this->baseUnit = $baseUnit;
        $this->rangeUnit = $rangeUnit;
        $this->range = ValueRange::of(-365243219162 + $offset, 365241780471 + $offset);
        $this->offset = $offset;
    }

//-----------------------------------------------------------------------
    public function getBaseUnit() : TemporalUnit
    {
        return $this->baseUnit;
    }

    public function getRangeUnit() : TemporalUnit
    {
        return $this->rangeUnit;
    }

    public function isDateBased() : bool
    {
        return true;
    }

    public function isTimeBased() : bool
    {
        return false;
    }

    public function range() : ValueRange
    {
        return $this->range;
    }

    //-----------------------------------------------------------------------
    public function isSupportedBy(TemporalAccessor $temporal) : bool
    {
        return $temporal->isSupported(CF::EPOCH_DAY());
    }

    public function rangeRefinedBy(TemporalAccessor $temporal) : ValueRange
    {
        if ($this->isSupportedBy($temporal) === false) {
            throw new DateTimeException("Unsupported field: " . $this);
        }
        return $this->range();
    }

    public function getFrom(TemporalAccessor $temporal) : int
    {
        return $temporal->getLong(CF::EPOCH_DAY()) + $this->offset;
    }

    public function adjustInto(Temporal $temporal, int $newValue) : Temporal
    {
        if ($this->range()->isValidValue($newValue) === false) {
            throw new DateTimeException("Invalid value: " . $this->name . " " . $newValue);
        }
        return $temporal->with(CF::EPOCH_DAY(), Math::subtractExact($newValue, $this->offset));
    }

    //-----------------------------------------------------------------------
    public function resolve(FieldValues $fieldValues, TemporalAccessor $partialTemporal, ResolverStyle $resolverStyle) : ?TemporalAccessor
    {
        $value = $fieldValues->remove($this);
        $chrono = AbstractChronology::from($partialTemporal);
        if ($resolverStyle == ResolverStyle::LENIENT()) {
            return $chrono->dateEpochDay(Math::subtractExact($value, $this->offset));
        }
        $this->range()->checkValidValue($value, $this);
        return $chrono->dateEpochDay($value - $this->offset);
    }

    //-----------------------------------------------------------------------
    public function __toString() : string
    {
        return $this->name;
    }

    public function getDisplayName(Locale $locale) : string
    {
        return $this->__toString();
    }
}
