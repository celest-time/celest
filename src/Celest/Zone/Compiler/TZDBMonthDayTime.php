<?php

namespace Celest\Zone\Compiler;

use Celest\DayOfWeek;
use Celest\LocalDate;
use Celest\LocalTime;
use Celest\Month;
use Celest\Zone\TimeDefinition;


/**
 * Class representing a month-day-time in the TZDB file.
 */
abstract class TZDBMonthDayTime
{
    /** The month of the cutover. @var Month */
    public $month;
    /** The day-of-month of the cutover. @var int */
    public $dayOfMonth = 1;
    /** Whether to adjust forwards. @var bool */
    public $adjustForwards = true;
    /** The day-of-week of the cutover. @var DayOfWeek */
    public $dayOfWeek;
    /** The time of the cutover. @var LocalTime */
    public $time;
    /** Whether this is midnight end of day. @var bool */
    public $endOfDay;
    /** The time of the cutover. @var TimeDefinition */
    public $timeDefinition;

    public function __construct()
    {
        $this->month = Month::JANUARY();
        $this->time = LocalTime::MIDNIGHT();
        $this->timeDefinition = TimeDefinition::WALL();
    }


    public function adjustToFowards($year)
    {
        if ($this->adjustForwards === false && $this->dayOfMonth > 0) {
            $adjustedDate = LocalDate::of($year, $this->month, $this->dayOfMonth)->minusDays(6);
            $this->dayOfMonth = $adjustedDate->getDayOfMonth();
            $this->month = $adjustedDate->getMonth();
            $this->adjustForwards = true;
        }
    }
}