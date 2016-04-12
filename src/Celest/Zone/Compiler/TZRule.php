<?php

namespace Celest\Zone\Compiler;

use Celest\Chrono\IsoChronology;
use Celest\DayOfWeek;
use Celest\LocalDate;
use Celest\LocalDateTime;
use Celest\LocalTime;
use Celest\Month;
use Celest\Temporal\TemporalAdjusters;
use Celest\Zone\TimeDefinition;
use Celest\Zone\ZoneOffsetTransition;
use Celest\Zone\ZoneOffsetTransitionRule;
use Celest\ZoneOffset;

/**
 * A definition of the way a local time can be converted to an offset time.
 */
class TZRule
{
    /** The year. @var int */
    public $year;
    /** The month. @var Month */
    public $month;
    /** The day-of-month. @var int */
    public $dayOfMonthIndicator;
    /** The day-of-month. @var DayOfWeek|null */
    public $dayOfWeek;
    /** The local time. @var LocalTime */
    public $time;
    /** Whether the local time is end of day. @var bool */
    public $timeEndOfDay;
    /** The type of the time. @var TimeDefinition */
    public $timeDefinition;
    /** The amount of the saving to be applied after this point. @var int */
    public $savingAmountSecs;

    /**
     * Constructor.
     *
     * @param int $year the year
     * @param Month $month the month, not null
     * @param int $dayOfMonthIndicator the day-of-month of the transition, adjusted by dayOfWeek,
     *   from 1 to 31 adjusted later, or -1 to -28 adjusted earlier from the last day of the month
     * @param |null $dayOfWeek DayOfWeek the day-of-week, null if day-of-month is exact
     * @param LocalTime $time the time, not null
     * @param bool $timeEndOfDay whether midnight is at the end of day
     * @param TimeDefinition $timeDefinition the time definition, not null
     * @param int $savingAfterSecs the savings amount in seconds
     */
    public function __construct($year, Month $month, $dayOfMonthIndicator,
                                $dayOfWeek, LocalTime $time, $timeEndOfDay,
                                TimeDefinition $timeDefinition, $savingAfterSecs)
    {
        $this->year = $year;
        $this->month = $month;
        $this->dayOfMonthIndicator = $dayOfMonthIndicator;
        $this->dayOfWeek = $dayOfWeek;
        $this->time = $time;
        $this->timeEndOfDay = $timeEndOfDay;
        $this->timeDefinition = $timeDefinition;
        $this->savingAmountSecs = $savingAfterSecs;
    }

    /**
     * Converts this to a transition.
     *
     * @param ZoneOffset $standardOffset the active standard offset, not null
     * @param int $savingsBeforeSecs the active savings in seconds
     * @return ZoneOffsetTransition the transition, not null
     */
    function toTransition(ZoneOffset $standardOffset, $savingsBeforeSecs)
    {
        // copy of code in ZoneOffsetTransitionRule to avoid infinite loop
        $date = $this->toLocalDate();
        $date = ZoneRulesBuilder::deduplicate($date);
        $ldt = ZoneRulesBuilder::deduplicate(LocalDateTime::ofDateAndTime($date, $this->time));
        /** @var ZoneOffset $wallOffset */
        $wallOffset = ZoneRulesBuilder::deduplicate(ZoneOffset::ofTotalSeconds($standardOffset->getTotalSeconds() + $savingsBeforeSecs));
        $dt = ZoneRulesBuilder::deduplicate($this->timeDefinition->createDateTime($ldt, $standardOffset, $wallOffset));
        $offsetAfter = ZoneRulesBuilder::deduplicate(ZoneOffset::ofTotalSeconds($standardOffset->getTotalSeconds() + $this->savingAmountSecs));
        return new ZoneOffsetTransition($dt, $wallOffset, $offsetAfter);
    }

    /**
     * Converts this to a transition rule.
     *
     * @param ZoneOffset $standardOffset the active standard offset, not null
     * @param int $savingsBeforeSecs the active savings before the transition in seconds
     * @return ZoneOffsetTransitionRule the transition, not null
     */
    function toTransitionRule(ZoneOffset $standardOffset, $savingsBeforeSecs)
    {
        // optimize stored format
        if ($this->dayOfMonthIndicator < 0) {
            if ($this->month != Month::FEBRUARY()) {
                $this->dayOfMonthIndicator = $this->month->maxLength() - 6;
            }
        }
        if ($this->timeEndOfDay && $this->dayOfMonthIndicator > 0 && ($this->dayOfMonthIndicator === 28 && $this->month == Month::FEBRUARY()) === false) {
            $date = LocalDate::ofMonth(2004, $this->month, $this->dayOfMonthIndicator)->plusDays(1);  // leap-year
            $this->month = $date->getMonth();
            $this->dayOfMonthIndicator = $date->getDayOfMonth();
            if ($this->dayOfWeek !== null) {
                $this->dayOfWeek = $this->dayOfWeek->plus(1);
            }
            $this->timeEndOfDay = false;
        }

// build rule
        $trans = $this->toTransition($standardOffset, $savingsBeforeSecs);
        return ZoneOffsetTransitionRule::of(
            $this->month, $this->dayOfMonthIndicator, $this->dayOfWeek, $this->time, $this->timeEndOfDay, $this->timeDefinition,
            $standardOffset, $trans->getOffsetBefore(), $trans->getOffsetAfter());
    }

    public static function compareTo(TZRule $_this, TZRule $other)
    {
        $cmp = $_this->year - $other->year;
        $cmp = ($cmp === 0 ? $_this->month->compareTo($other->month) : $cmp);
        if ($cmp === 0) {
            // convert to date to handle dow/domIndicator/timeEndOfDay
            $thisDate = $_this->toLocalDate();
            $otherDate = $other->toLocalDate();
            $cmp = $thisDate->compareTo($otherDate);
        }

        $cmp = ($cmp === 0 ? $_this->time->compareTo($other->time) : $cmp);
        return $cmp;
    }

    /**
     * @return LocalDate
     */
    private function toLocalDate()
    {
        if ($this->dayOfMonthIndicator < 0) {
            $monthLen = $this->month->length(IsoChronology::INSTANCE()->isLeapYear($this->year));
            $date = LocalDate::ofMonth($this->year, $this->month, $monthLen + 1 + $this->dayOfMonthIndicator);
            if ($this->dayOfWeek !== null) {
                $date = $date->adjust(TemporalAdjusters::previousOrSame($this->dayOfWeek));
            }
        } else {
            $date = LocalDate::ofMonth($this->year, $this->month, $this->dayOfMonthIndicator);
            if ($this->dayOfWeek !== null) {
                $date = $date->adjust(TemporalAdjusters::nextOrSame($this->dayOfWeek));
            }
        }
        if ($this->timeEndOfDay) {
            $date = $date->plusDays(1);
        }
        return $date;
    }

    function __toString()
    {
        return $this->year . '-' . $this->month . '-' . $this->dayOfMonthIndicator
        . 'T' . $this->time
        . ' ' . $this->timeDefinition
        . ' ' . $this->savingAmountSecs
        . ' ' . $this->dayOfWeek
        . ' ' . $this->timeEndOfDay;
    }


    // no equals() or hashCode()
}