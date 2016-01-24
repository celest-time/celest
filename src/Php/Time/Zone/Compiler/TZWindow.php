<?php

namespace Php\Time\Zone\Compiler;

use Php\Time\DayOfWeek;
use Php\Time\Helper\Math;
use Php\Time\LocalDateTime;
use Php\Time\LocalTime;
use Php\Time\Month;
use Php\Time\Year;
use Php\Time\Zone\TimeDefinition;
use Php\Time\ZoneOffset;

/**
 * A definition of a window in the time-line.
 * The window will have one standard offset and will either have a
 * fixed DST savings or a set of rules.
 */
class TZWindow
{
    /** The standard offset during the window, not null. @var ZoneOffset */
    public $standardOffset;
    /** The end local time, not null. @var LocalDateTime */
    private $windowEnd;
    /** The type of the end time, not null. @var TimeDefinition */
    private $timeDefinition;

    /** The fixed amount of the saving to be applied during this window. @var int */
    public $fixedSavingAmountSecs;
    /** The rules for the current window. @var TZRule[] */
    public $ruleList = [];
    /** The latest year that the last year starts at. @var int */
    private $maxLastRuleStartYear = Year::MIN_VALUE;
    /** The last rules. @var TZRule[] */
    public $lastRuleList = [];

    /**
     * Constructor.
     *
     * @param $standardOffset ZoneOffset the standard offset applicable during the window, not null
     * @param $windowEnd LocalDateTime the end of the window, relative to the time definition, null if forever
     * @param $timeDefinition TimeDefinition the time definition for calculating the true end, not null
     */
    public function __construct(
        ZoneOffset $standardOffset,
        LocalDateTime $windowEnd,
        TimeDefinition $timeDefinition)
    {
        $this->windowEnd = $windowEnd;
        $this->timeDefinition = $timeDefinition;
        $this->standardOffset = $standardOffset;
    }

    /**
     * Sets the fixed savings amount for the window.
     *
     * @param $fixedSavingAmount int the amount of daylight saving to apply throughout the window, may be null
     * @throws \LogicException if the window already has rules
     */
    function setFixedSavings($fixedSavingAmount)
    {
        if (count($this->ruleList) > 0 || count($this->lastRuleList) > 0) {
            throw new \LogicException("Window has DST rules, so cannot have fixed savings");
        }

        $this->fixedSavingAmountSecs = $fixedSavingAmount;
    }

    /**
     * Adds a rule to the current window.
     *
     * @param $startYear int the start year of the rule, from MIN_VALUE to MAX_VALUE
     * @param $endYear int the end year of the rule, from MIN_VALUE to MAX_VALUE
     * @param $month Month the month of the transition, not null
     * @param $dayOfMonthIndicator int the day-of-month of the transition, adjusted by dayOfWeek,
     *   from 1 to 31 adjusted later, or -1 to -28 adjusted earlier from the last day of the month
     * @param $dayOfWeek DayOfWeek|null the day-of-week to adjust to, null if day-of-month should not be adjusted
     * @param $time LocalTime the time that the transition occurs as defined by timeDefintion, not null
     * @param $timeEndOfDay bool whether midnight is at the end of day
     * @param $timeDefinition TimeDefinition the definition of how to convert local to actual time, not null
     * @param $savingAmountSecs int the amount of saving from the standard offset in seconds
     * @throws \LogicException if the window already has fixed savings
     * @throws \LogicException if the window has reached the maximum capacity of 2000 rules
     */
    function addRule(
        $startYear,
        $endYear,
        Month $month,
        $dayOfMonthIndicator,
        $dayOfWeek,
        LocalTime $time,
        $timeEndOfDay,
        TimeDefinition $timeDefinition,
        $savingAmountSecs)
    {

        if ($this->fixedSavingAmountSecs != null) {
            throw new \LogicException("Window has a fixed DST saving, so cannot have DST rules");
        }

        if (count($this->ruleList) >= 2000) {
            throw new \LogicException("Window has reached the maximum number of allowed rules");
        }
        $lastRule = false;
            if ($endYear === Year::MAX_VALUE) {
                $lastRule = true;
                $endYear = $startYear;
            }
            $year = $startYear;
            while ($year <= $endYear) {
                $rule = new TZRule($year, $month, $dayOfMonthIndicator, $dayOfWeek, $time, $timeEndOfDay, $timeDefinition, $savingAmountSecs);
                if ($lastRule) {
                    $this->lastRuleList[] = $rule;
                    $this->maxLastRuleStartYear = Math::max($startYear, $this->maxLastRuleStartYear);
                } else {
                    $this->ruleList[] = $rule;
                }
                $year++;
            }
        }

    /**
     * Validates that this window is after the previous one.
     *
     * @param $previous TZWindow the previous window, not null
     * @throws \LogicException if the window order is invalid
     */
    function validateWindowOrder(TZWindow $previous)
    {
        if ($this->windowEnd->isBefore($previous->windowEnd)) {
            throw new \LogicException("Windows must be added in date-time order: " .
                $this->windowEnd . " < " . $previous->windowEnd);
        }
    }

    /**
     * Adds rules to make the last rules all start from the same year.
     * Also add one more year to avoid weird case where penultimate year has odd offset.
     *
     * @param $windowStartYear int the window start year
     * @throws \LogicException if there is only one rule defined as being forever
     */
    function tidy($windowStartYear)
    {
        if (count($this->lastRuleList) === 1) {
            throw new \LogicException("Cannot have only one rule defined as being forever");
        }

        // handle last rules
        if ($this->windowEnd->equals(LocalDateTime::MAX())) {
            // setup at least one real rule, which closes off other windows nicely
            $this->maxLastRuleStartYear = Math::max($this->maxLastRuleStartYear, $windowStartYear) + 1;
            foreach ($this->lastRuleList as $lastRule) {
                $this->addRule($lastRule->year, $this->maxLastRuleStartYear, $lastRule->month, $lastRule->dayOfMonthIndicator,
                    $lastRule->dayOfWeek, $lastRule->time, $lastRule->timeEndOfDay, $lastRule->timeDefinition, $lastRule->savingAmountSecs);
                $lastRule->year = $this->maxLastRuleStartYear + 1;
            }
                if ($this->maxLastRuleStartYear == Year::MAX_VALUE) {
                    $this->lastRuleList = [];
                } else {
                    $this->maxLastRuleStartYear++;
                }
            } else {
            // convert all within the endYear limit
            $endYear = $this->windowEnd->getYear();
                foreach ($this->lastRuleList as $lastRule) {
                    $this->addRule($lastRule->year, $endYear + 1, $lastRule->month, $lastRule->dayOfMonthIndicator,
                        $lastRule->dayOfWeek, $lastRule->time, $lastRule->timeEndOfDay, $lastRule->timeDefinition, $lastRule->savingAmountSecs);
                }
                $this->lastRuleList = [];
            $this->maxLastRuleStartYear = Year::MAX_VALUE;
            }

            // ensure lists are sorted
            usort($this->ruleList, [TZRule::class, 'compareTo']);
            usort($this->lastRuleList, [TZRule::class, 'compareTo']);

            // default fixed savings to zero
            if (count($this->ruleList) === 0 && $this->fixedSavingAmountSecs === null) {
                $this->fixedSavingAmountSecs = 0;
            }
        }

    /**
     * Checks if the window is empty.
     *
     * @return bool true if the window is only a standard offset
     */
    function isSingleWindowStandardOffset()
    {
        return $this->windowEnd->equals(LocalDateTime::MAX()) && $this->timeDefinition == TimeDefinition::WALL() &&
        $this->fixedSavingAmountSecs === null && empty($this->lastRuleList) && empty($this->ruleList);
    }

    /**
     * Creates the wall offset for the local date-time at the end of the window.
     *
     * @param $savingsSecs int the amount of savings in use in seconds
     * @return ZoneOffset the created date-time epoch second in the wall offset, not null
     */
    function createWallOffset($savingsSecs)
    {
        return ZoneOffset::ofTotalSeconds($this->standardOffset->getTotalSeconds() + $savingsSecs);
    }

    /**
     * Creates the offset date-time for the local date-time at the end of the window.
     *
     * @param $savingsSecs int the amount of savings in use in seconds
     * @return int the created date-time epoch second in the wall offset, not null
     */
    function createDateTimeEpochSecond($savingsSecs)
    {
        $wallOffset = $this->createWallOffset($savingsSecs);
        $ldt = $this->timeDefinition->createDateTime($this->windowEnd, $this->standardOffset, $wallOffset);
        return $ldt->toEpochSecond($wallOffset);
    }
}