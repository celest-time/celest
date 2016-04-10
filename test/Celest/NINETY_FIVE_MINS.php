<?php

namespace Celest;


use Celest\Temporal\Temporal;
use Celest\Temporal\TemporalUnit;

class NINETY_FIVE_MINS implements TemporalUnit
{
    public function getDuration()
    {
        return Duration::ofMinutes(95);
    }

    public function isDurationEstimated()
    {
        return false;
    }

    public function isDateBased()
    {
        return false;
    }

    public function isTimeBased()
    {
        return false;
    }

    public function isSupportedBy(Temporal $temporal)
    {
        return false;
    }

    public function addTo(Temporal $temporal, $amount)
    {
        throw new \LogicException();
    }

    public function between(Temporal $temporal1, Temporal $temporal2)
    {
        throw new \LogicException();
    }

    public function __toString()
    {
        return "NinetyFiveMins";
    }
}
