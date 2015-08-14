<?php

namespace Php\Time\Temporal;


class TemporalDefaults
{
    private function __construct()
    {
    }

    public static function with(Temporal $_this, TemporalAdjuster $adjuster)
    {
        return $adjuster->adjustInto($_this);
    }

    public static function plus(Temporal $_this, TemporalAmount $amount)
    {
        return $amount->addTo($_this);
    }

    public static function minus(Temporal $_this, TemporalAmount $amount)
    {
        return $amount->subtractFrom($_this);
    }

    public static function minus(Temporal $_this, $amountToSubtract, TemporalUnit $unit)
    {
        return ($amountToSubtract == Long::MIN_VALUE ? $_this->plus(Long::MAX_VALUE, $unit)->plus(1, $unit) : $_this->plus(-$amountToSubtract, $unit));
    }


}