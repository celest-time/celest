<?php

namespace Celest\Chrono;

abstract class AbstractChronoPeriod implements ChronoPeriod
{
    /**
     * @inheritdoc
     */
    public static function between(ChronoLocalDate $startDateInclusive, ChronoLocalDate $endDateExclusive)
    {
        return $startDateInclusive->untilDate($endDateExclusive);
    }

    /**
     * @inheritdoc
     */
    public function isZero()
    {
        foreach ($this->getUnits() as $unit) {
            if ($this->get($unit) != 0) {
                return false;
            }
        }
        return true;
    }

    /**
     * @inheritdoc
     */
    public function isNegative()
    {
        foreach ($this->getUnits() as $unit) {
            if ($this->get($unit) < 0) {
                return true;
            }
        }
        return false;
    }

    /**
     * @inheritdoc
     */
    public function negated()
    {
        return $this->multipliedBy(-1);
    }
}