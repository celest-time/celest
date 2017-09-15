<?php

namespace Celest;


use Celest\Temporal\Temporal;
use Celest\Temporal\TemporalUnit;

class NINETY_FIVE_MINS implements TemporalUnit
{
    public function getDuration() : Duration
    {
        return Duration::ofMinutes(95);
    }

    public function isDurationEstimated() : bool
    {
        return false;
    }

    public function isDateBased() : bool
    {
        return false;
    }

    public function isTimeBased() : bool
    {
        return false;
    }

    public function isSupportedBy(Temporal $temporal) : bool
    {
        return false;
    }

    public function addTo(Temporal $temporal, int $amount)
    {
        throw new \LogicException();
    }

    public function between(Temporal $temporal1, Temporal $temporal2) : int
    {
        throw new \LogicException();
    }

    public function __toString() : string
    {
        return "NinetyFiveMins";
    }
}
