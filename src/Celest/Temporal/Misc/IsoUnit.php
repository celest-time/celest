<?php

namespace Celest\Temporal\Misc;

use Celest\Duration;
use Celest\Helper\Math;
use Celest\Temporal\ChronoField;
use Celest\Temporal\ChronoUnit;
use Celest\Temporal\IsoFields;
use Celest\Temporal\Temporal;
use Celest\Temporal\TemporalUnit;

/**
 * Implementation of the unit.
 */
class IsoUnit implements TemporalUnit
{
    /**
     * Unit that represents the concept of a week-based-year.
     * @return IsoUnit
     */
    public static function WEEK_BASED_YEARS()
    {
        if (self::$WEEK_BASED_YEARS === null) {
            self::$WEEK_BASED_YEARS = new IsoUnit("WeekBasedYears", Duration::ofSeconds(31556952));
        }
        return self::$WEEK_BASED_YEARS;
    }
    /** @var IsoUnit */
    private static $WEEK_BASED_YEARS;

    /**
     * Unit that represents the concept of a quarter-year.
     * @return IsoUnit
     */
    public static function QUARTER_YEARS()
    {
        if (self::$QUARTER_YEARS === null) {
            self::$QUARTER_YEARS = new IsoUnit("QuarterYears", Duration::ofSeconds(31556952 / 4));
        }
        return self::$QUARTER_YEARS;
    }
    /** @var IsoUnit */
    private static $QUARTER_YEARS;

    /** @var string */
    private $name;
    /** @var Duration */
    private $duration;

    private function __construct($name, Duration $estimatedDuration)
    {
        $this->name = $name;
        $this->duration = $estimatedDuration;
    }

    public function getDuration()
    {
        return $this->duration;
    }

    public
    function isDurationEstimated()
    {
        return true;
    }

    public
    function isDateBased()
    {
        return true;
    }

    public
    function isTimeBased()
    {
        return false;
    }

    public function isSupportedBy(Temporal $temporal)
    {
        return $temporal->isSupported(ChronoField::EPOCH_DAY());
    }

    public function addTo(Temporal $temporal, $amount)
    {
        switch ($this) {
            case IsoFields::WEEK_BASED_YEARS():
                return $temporal->with(IsoFields::WEEK_BASED_YEAR(),
                    Math::addExact($temporal->get(IsoFields::WEEK_BASED_YEAR()), $amount));
            case IsoFields::QUARTER_YEARS():
                // no overflow (256 is multiple of 4)
                return $temporal->plus($amount / 256, ChronoUnit::YEARS())
                    ->plus(($amount % 256) * 3, ChronoUnit::MONTHS());
            default:
                throw new IllegalStateException("Unreachable");
        }
    }

    public function between(Temporal $temporal1Inclusive, Temporal $temporal2Exclusive)
    {
        if (get_class($temporal1Inclusive) !== get_class($temporal2Exclusive)) {
            return $temporal1Inclusive->until($temporal2Exclusive, $this);
        }

        switch ($this) {
            case self::WEEK_BASED_YEARS():
                return Math::subtractExact($temporal2Exclusive->getLong(IsoFields::WEEK_BASED_YEAR()),
                    $temporal1Inclusive->getLong(IsoFields::WEEK_BASED_YEAR()));
            case self::QUARTER_YEARS():
                return $temporal1Inclusive->until($temporal2Exclusive, ChronoUnit::MONTHS()) / 3;
            default:
                throw new IllegalStateException("Unreachable");
        }
    }

    public
    function __toString()
    {
        return $this->name;
    }
}