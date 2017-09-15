<?php declare(strict_types=1);

namespace Celest\Temporal;

use Celest\Chrono\ChronoLocalDate;
use Celest\Chrono\ChronoLocalDateTime;
use Celest\Chrono\ChronoZonedDateTime;
use Celest\LocalTime;

abstract class AbstractTemporalUnit implements TemporalUnit
{
    /**
     * @inheritdoc
     */
    public function isSupportedBy(Temporal $temporal) : bool
    {
        if ($temporal instanceof LocalTime) {
            return $this->isTimeBased();
        }
        if ($temporal instanceof ChronoLocalDate) {
            return $this->isDateBased();
        }
        if ($temporal instanceof ChronoLocalDateTime || $temporal instanceof ChronoZonedDateTime) {
            return true;
        }
        try {
            $temporal->plus(1, $this);
            return true;
        } catch (UnsupportedTemporalTypeException $ex) {
            return false;
        } catch (\RuntimeException $ex) {
            try {
                $temporal->plus(-1, $this);
                return true;
            } catch (\RuntimeException $ex2) {
                return false;
            }
        }
    }
}
