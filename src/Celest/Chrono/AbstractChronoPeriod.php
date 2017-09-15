<?php declare(strict_types=1);

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
    public function isZero() : bool
    {
        foreach ($this->getUnits() as $unit) {
            if ($this->get($unit) !== 0) {
                return false;
            }
        }
        return true;
    }

    /**
     * @inheritdoc
     */
    public function isNegative() : bool
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