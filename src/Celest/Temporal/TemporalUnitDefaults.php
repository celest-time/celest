<?php

namespace Celest\Temporal;


use Celest\Chrono\ChronoLocalDate;
use Celest\Chrono\ChronoLocalDateTime;
use Celest\Chrono\ChronoZonedDateTime;
use Celest\LocalTime;

final class TemporalUnitDefaults
{
    private function __construct() {}

    public static function isSupportedBy(TemporalUnit $_this, Temporal $temporal)
    {
        if ($temporal instanceof LocalTime) {
            return $_this->isTimeBased();
        }
        if ($temporal instanceof ChronoLocalDate) {
            return $_this->isDateBased();
        }
        if ($temporal instanceof ChronoLocalDateTime || $temporal instanceof ChronoZonedDateTime) {
            return true;
        }
        try {
            $temporal->plus(1, $_this);
            return true;
        } catch (UnsupportedTemporalTypeException $ex) {
            return false;
        } catch (\RuntimeException $ex) {
            try {
                $temporal->plus(-1, $_this);
                return true;
            } catch (\RuntimeException $ex2) {
                return false;
            }
        }
    }
}