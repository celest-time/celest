<?php
/*
 * Copyright (c) 2007-present, Stephen Colebourne & Michael Nascimento Santos
 *
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *
 *  * Redistributions of source code must retain the above copyright notice,
 *    this list of conditions and the following disclaimer.
 *
 *  * Redistributions in binary form must reproduce the above copyright notice,
 *    this list of conditions and the following disclaimer in the documentation
 *    and/or other materials provided with the distribution.
 *
 *  * Neither the name of JSR-310 nor the names of its contributors
 *    may be used to endorse or promote products derived from this software
 *    without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR
 * CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL,
 * EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO,
 * PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR
 * PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF
 * LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING
 * NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
 * SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 */

namespace Php\Time\Zone\Compiler;

use Php\Time\DateTimeException;
use Php\Time\DayOfWeek;
use Php\Time\IllegalArgumentException;
use Php\Time\LocalDateTime;
use Php\Time\LocalTime;
use Php\Time\Month;
use Php\Time\Temporal\ChronoField;
use Php\Time\Year;
use Php\Time\Zone\TimeDefinition;
use Php\Time\Zone\ZoneOffsetTransition;
use Php\Time\Zone\ZoneOffsetTransitionRule;
use Php\Time\Zone\ZoneRules;
use Php\Time\ZoneOffset;

/**
 * A mutable builder used to create all the rules for a historic time-zone.
 * <p>
 * The rules of a time-zone describe how the offset changes over time.
 * The rules are created by building windows on the time-line within which
 * the different rules apply. The rules may be one of two kinds:
 * <p><ul>
 * <li>Fixed savings - A single fixed amount of savings from the standard offset will apply.</li>
 * <li>Rules - A set of one or more rules describe how daylight savings changes during the window.</li>
 * </ul><p>
 *
 * <h3>Specification for implementors</h3>
 * This class is a mutable builder used to create zone instances.
 * It must only be used from a single thread.
 * The created instances are immutable and thread-safe.
 */
class ZoneRulesBuilder
{

    /**
     * The list of windows. @var TZWindow[]
     */
    private $windowList = [];
    /**
     * A map for deduplicating the output. @var array
     */
    private $deduplicateMap;

    //-----------------------------------------------------------------------
    /**
     * Constructs an instance of the builder that can be used to create zone rules.
     * <p>
     * The builder is used by adding one or more windows representing portions
     * of the time-line. The standard offset from UTC/Greenwich will be constant
     * within a window, although two adjacent windows can have the same standard offset.
     * <p>
     * Within each window, there can either be a
     * {@link #setFixedSavingsToWindow fixed savings amount} or a
     * {@link #addRuleToWindow list of rules}.
     */
    public function __construct()
    {
    }

//-----------------------------------------------------------------------
    /**
     * Adds a window to the builder that can be used to filter a set of rules.
     * <p>
     * This method defines and adds a window to the zone where the standard offset is specified.
     * The window limits the effect of subsequent additions of transition rules
     * or fixed savings. If neither rules or fixed savings are added to the window
     * then the window will default to no savings.
     * <p>
     * Each window must be added sequentially, as the start instant of the window
     * is derived from the until instant of the previous window.
     *
     * @param ZoneOffset $standardOffset the standard offset, not null
     * @param LocalDateTime $until the date-time that the offset applies until, not null
     * @param TimeDefinition $untilDefinition the time type for the until date-time, not null
     * @return ZoneRulesBuilder $this, for chaining
     * @throws \LogicException if the window order is invalid
     */
    public function addWindow(
        ZoneOffset $standardOffset,
        LocalDateTime $until,
        TimeDefinition $untilDefinition)
    {
        $window = new TZWindow($standardOffset, $until, $untilDefinition);
        if (count($this->windowList) > 0) {
            $previous = $this->windowList[count($this->windowList) - 1];
            $window->validateWindowOrder($previous);
        }

        $this->windowList[] = $window;
        return $this;
    }

    /**
     * Adds a window that applies until the end of time to the builder that can be
     * used to filter a set of rules.
     * <p>
     * This method defines and adds a window to the zone where the standard offset is specified.
     * The window limits the effect of subsequent additions of transition rules
     * or fixed savings. If neither rules or fixed savings are added to the window
     * then the window will default to no savings.
     * <p>
     * This must be added after all other windows.
     * No more windows can be added after this one.
     *
     * @param ZoneOffset $standardOffset the standard offset, not null
     * @return ZoneRulesBuilder $this, for chaining
     * @throws \LogicException if a forever window has already been added
     */
    public function addWindowForever(ZoneOffset $standardOffset)
    {
        return $this->addWindow($standardOffset, LocalDateTime::MAX(), TimeDefinition::WALL());
    }

//-----------------------------------------------------------------------
    /**
     * Sets the previously added window to have fixed savings.
     * <p>
     * Setting a window to have fixed savings simply means that a single daylight
     * savings amount applies throughout the window. The window could be small,
     * such as a single summer, or large, such as a multi-year daylight savings.
     * <p>
     * A window can either have fixed savings or rules but not both.
     *
     * @param int $fixedSavingAmountSecs the amount of saving to use for the whole window, not null
     * @return ZoneRulesBuilder $this, for chaining
     * @throws \LogicException if no window has yet been added
     * @throws \LogicException if the window already has rules
     */
    public function setFixedSavingsToWindow($fixedSavingAmountSecs)
    {
        if (empty($this->windowList)) {
            throw new \LogicException("Must add a window before setting the fixed savings");
        }

        $window = $this->windowList[count($this->windowList) - 1];
        $window->setFixedSavings($fixedSavingAmountSecs);
        return $this;
    }

    //-----------------------------------------------------------------------
    /**
     * Adds a single transition rule to the current window.
     * <p>
     * This adds a rule such that the offset, expressed as a daylight savings amount,
     * changes at the specified date-time.
     *
     * @param LocalDateTime $transitionDateTime the date-time that the transition occurs as defined by timeDefintion, not null
     * @param TimeDefinition $timeDefinition the definition of how to convert local to actual time, not null
     * @param int $savingAmountSecs the amount of saving from the standard offset after the transition in seconds
     * @return ZoneRulesBuilder $this, for chaining
     * @throws \LogicException if no window has yet been added
     * @throws \LogicException if the window already has fixed savings
     * @throws \LogicException if the window has reached the maximum capacity of 2000 rules
     */
    public function addRuleToWindow(
        LocalDateTime $transitionDateTime,
        TimeDefinition $timeDefinition,
        $savingAmountSecs)
    {
        return $this->addRuleToWindow9(
            $transitionDateTime->getYear(), $transitionDateTime->getYear(),
            $transitionDateTime->getMonth(), $transitionDateTime->getDayOfMonth(),
            null, $transitionDateTime->toLocalTime(), false, $timeDefinition, $savingAmountSecs);
    }

    /**
     * Adds a single transition rule to the current window.
     * <p>
     * This adds a rule such that the offset, expressed as a daylight savings amount,
     * changes at the specified date-time.
     *
     * @param int $year the year of the transition, from MIN_VALUE to MAX_VALUE
     * @param Month $month the month of the transition, not null
     * @param int $dayOfMonthIndicator the day-of-month of the transition, adjusted by dayOfWeek,
     *   from 1 to 31 adjusted later, or -1 to -28 adjusted earlier from the last day of the month
     * @param LocalTime $time the time that the transition occurs as defined by timeDefintion, not null
     * @param bool $timeEndOfDay whether midnight is at the end of day
     * @param TimeDefinition $timeDefinition the definition of how to convert local to actual time, not null
     * @param int $savingAmountSecs the amount of saving from the standard offset after the transition in seconds
     * @return ZoneRulesBuilder $this, for chaining
     * @throws DateTimeException if a date-time field is out of range
     * @throws \LogicException if no window has yet been added
     * @throws \LogicException if the window already has fixed savings
     * @throws \LogicException if the window has reached the maximum capacity of 2000 rules
     */
    public function addRuleToWindow7(
        $year,
        Month $month,
        $dayOfMonthIndicator,
        LocalTime $time,
        $timeEndOfDay,
        TimeDefinition $timeDefinition,
        $savingAmountSecs)
    {
        return $this->addRuleToWindow9($year, $year, $month, $dayOfMonthIndicator, null, $time, $timeEndOfDay, $timeDefinition, $savingAmountSecs);
    }

    /**
     * Adds a multi-year transition rule to the current window.
     * <p>
     * This adds a rule such that the offset, expressed as a daylight savings amount,
     * changes at the specified date-time for each year in the range.
     *
     * @param int $startYear the start year of the rule, from MIN_VALUE to MAX_VALUE
     * @param int $endYear the end year of the rule, from MIN_VALUE to MAX_VALUE
     * @param Month $month the month of the transition, not null
     * @param int $dayOfMonthIndicator the day-of-month of the transition, adjusted by dayOfWeek,
     *   from 1 to 31 adjusted later, or -1 to -28 adjusted earlier from the last day of the month
     * @param DayOfWeek|null $dayOfWeek the day-of-week to adjust to, null if day-of-month should not be adjusted
     * @param LocalTime $time the time that the transition occurs as defined by timeDefintion, not null
     * @param bool $timeEndOfDay whether midnight is at the end of day
     * @param TimeDefinition $timeDefinition the definition of how to convert local to actual time, not null
     * @param int $savingAmountSecs the amount of saving from the standard offset after the transition in seconds
     * @return ZoneRulesBuilder $this, for chaining
     * @throws DateTimeException if a date-time field is out of range
     * @throws IllegalArgumentException if the day of month indicator is invalid
     * @throws IllegalArgumentException if the end of day midnight flag does not match the time
     * @throws \LogicException if no window has yet been added
     * @throws \LogicException if the window already has fixed savings
     * @throws \LogicException if the window has reached the maximum capacity of 2000 rules
     */
    public
    function addRuleToWindow9(
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
        ChronoField::YEAR()->checkValidValue($startYear);
        ChronoField::YEAR()->checkValidValue($endYear);

        if ($dayOfMonthIndicator < -28 || $dayOfMonthIndicator > 31 || $dayOfMonthIndicator === 0) {
            throw new IllegalArgumentException("Day of month indicator must be between -28 and 31 inclusive excluding zero");
        }

        if ($timeEndOfDay && $time->equals(LocalTime::MIDNIGHT()) == false) {
            throw new IllegalArgumentException("Time must be midnight when end of day flag is true");
        }
        if (empty($this->windowList)) {
            throw new \LogicException("Must add a window before adding a rule");
        }
        $window = $this->windowList[count($this->windowList) - 1];
        $window->addRule($startYear, $endYear, $month, $dayOfMonthIndicator, $dayOfWeek, $time, $timeEndOfDay, $timeDefinition, $savingAmountSecs);
        return $this;
    }

    //-----------------------------------------------------------------------
    /**
     * Completes the build converting the builder to a set of time-zone rules.
     * <p>
     * Calling this method alters the state of the builder.
     * Further rules should not be added to this builder once this method is called.
     *
     * @param string $zoneId the time-zone ID, not null
     * @return ZoneRules the zone rules, not null
     * @throws \LogicException if no windows have been added
     * @throws \LogicException if there is only one rule defined as being forever for any given window
     */
    public function toRules($zoneId)
    {
        return $this->_toRules($zoneId, $arr = []);
    }

    /**
     * Completes the build converting the builder to a set of time-zone rules.
     * <p>
     * Calling this method alters the state of the builder.
     * Further rules should not be added to this builder once this method is called.
     *
     * @param string $zoneId the time-zone ID, not null
     * @param array $deduplicateMap a map for deduplicating the values, not null
     * @return ZoneRules the zone rules, not null
     * @throws \LogicException if no windows have been added
     * @throws \LogicException if there is only one rule defined as being forever for any given window
     */
    public function _toRules($zoneId, &$deduplicateMap)
    {
        $this->deduplicateMap = $deduplicateMap;
        if (empty($this->windowList)) {
            throw new \LogicException("No windows have been added to the builder");
        }

        /** @var ZoneOffsetTransition[] $standardTransitionList */
        $standardTransitionList = [];
        /** @var ZoneOffsetTransition[] */
        $transitionList = [];
        /** @var ZoneOffsetTransitionRule[] */
        $lastTransitionRuleList = [];

        // initialize the standard offset calculation
        $firstWindow = $this->windowList[0];
        $loopStandardOffset = $firstWindow->standardOffset;
        $loopSavings = 0;
        if ($firstWindow->fixedSavingAmountSecs !== null) {
            $loopSavings = $firstWindow->fixedSavingAmountSecs;
        }
        /** @var ZoneOffset $firstWallOffset */
        $firstWallOffset = $this->deduplicate(ZoneOffset::ofTotalSeconds($loopStandardOffset->getTotalSeconds() + $loopSavings));
        /** @var LocalDateTime $loopWindowStart */
        $loopWindowStart = $this->deduplicate(LocalDateTime::ofNumerical(Year::MIN_VALUE, 1, 1, 0, 0));
        $loopWindowOffset = $firstWallOffset;

        // build the windows and rules to interesting data
        foreach ($this->windowList as $window) {
            // tidy the state
            $window->tidy($loopWindowStart->getYear());

            // calculate effective savings at the start of the window
            $effectiveSavings = $window->fixedSavingAmountSecs;
            if ($effectiveSavings === null) {
                // apply rules from this window together with the standard offset and
                // savings from the last window to find the savings amount applicable
                // at start of this window
                $effectiveSavings = 0;
                foreach ($window->ruleList as $rule) {
                    $trans = $rule->toTransition($loopStandardOffset, $loopSavings);
                    if ($trans->toEpochSecond() > $loopWindowStart->toEpochSecond($loopWindowOffset)) {
                        // previous savings amount found, which could be the savings amount at
                        // the instant that the window starts (hence isAfter)
                        break;
                    }
                    $effectiveSavings = $rule->savingAmountSecs;
                }
            }

            // check if standard offset changed, and update it
            if ($loopStandardOffset->equals($window->standardOffset) === false) {
                $standardTransitionList[] = $this->deduplicate(
                    ZoneOffsetTransition::of(
                        LocalDateTime::ofEpochSecond($loopWindowStart->toEpochSecond($loopWindowOffset), 0, $loopStandardOffset),
                        $loopStandardOffset, $window->standardOffset));
                $loopStandardOffset = $this->deduplicate($window->standardOffset);
            }

            // check if the start of the window represents a transition
            $effectiveWallOffset = $this->deduplicate(ZoneOffset::ofTotalSeconds($loopStandardOffset->getTotalSeconds() + $effectiveSavings));
            if ($loopWindowOffset->equals($effectiveWallOffset) === false) {
                $trans = $this->deduplicate(
                    ZoneOffsetTransition::of($loopWindowStart, $loopWindowOffset, $effectiveWallOffset));
                $transitionList[] = $trans;
            }
            $loopSavings = $effectiveSavings;

            // apply rules within the window
            foreach ($window->ruleList as $rule) {
                /** @var ZoneOffsetTransition $trans */
                $trans = $this->deduplicate($rule->toTransition($loopStandardOffset, $loopSavings));
                if ($trans !== null &&
                    $trans->toEpochSecond() < $loopWindowStart->toEpochSecond($loopWindowOffset) === false &&
                    $trans->toEpochSecond() < $window->createDateTimeEpochSecond($loopSavings) &&
                    $trans->getOffsetBefore()->equals($trans->getOffsetAfter()) === false
                ) {
                    $transitionList[] = $trans;
                    $loopSavings = $rule->savingAmountSecs;
                }
            }

            // calculate last rules
            foreach ($window->lastRuleList as $lastRule) {
                $transitionRule = $this->deduplicate($lastRule->toTransitionRule($loopStandardOffset, $loopSavings));
                $lastTransitionRuleList[] = $transitionRule;
                $loopSavings = $lastRule->savingAmountSecs;
            }

            // finally we can calculate the true end of the window, passing it to the next window
            $loopWindowOffset = $this->deduplicate($window->createWallOffset($loopSavings));
            $loopWindowStart = $this->deduplicate(LocalDateTime::ofEpochSecond(
                $window->createDateTimeEpochSecond($loopSavings), 0, $loopWindowOffset));
        }
        return ZoneRules::of(
            $firstWindow->standardOffset, $firstWallOffset, $standardTransitionList,
            $transitionList, $lastTransitionRuleList);
    }

    public static function deduplicate($object)
    {
        return $object;
        // TODO
//        if ($this->deduplicateMap->containsKey($object) == false) {
//            $this->deduplicateMap->put($object, $object);
//        }
//
//        return $this->deduplicateMap->get($object);
    }

}