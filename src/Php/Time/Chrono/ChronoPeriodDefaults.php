<?php

namespace Php\Time\Chrono;

class ChronoPeriodDefaults
{
    public static function between(ChronoPeriod $_this, ChronoLocalDate $startDateInclusive, ChronoLocalDate $endDateExclusive)
    {
        return $startDateInclusive->until($endDateExclusive);
    }

    public static function isZero(ChronoPeriod $_this)
    {
        foreach ($_this->getUnits() as $unit) {
            if ($_this->get($unit) != 0) {
                return false;
            }
        }
        return true;
    }

    public static function isNegative(ChronoPeriod $_this)
    {
        foreach ($_this->getUnits() as $unit) {
            if ($_this->get($unit) < 0) {
                return true;
            }
        }
        return false;
    }

    public static function negated(ChronoPeriod $_this)
    {
        return $_this->multipliedBy(-1);
    }
}