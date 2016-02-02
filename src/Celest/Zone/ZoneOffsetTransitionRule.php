<?php
/*
 * Copyright (c) 2012, 2013, Oracle and/or its affiliates. All rights reserved.
 * DO NOT ALTER OR REMOVE COPYRIGHT NOTICES OR THIS FILE HEADER.
 *
 * This code is free software; you can redistribute it and/or modify it
 * under the terms of the GNU General Public License version 2 only, as
 * published by the Free Software Foundation.  Oracle designates this
 * particular file as subject to the "Classpath" exception as provided
 * by Oracle in the LICENSE file that accompanied this code.
 *
 * This code is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or
 * FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License
 * version 2 for more details (a copy is included in the LICENSE file that
 * accompanied this code).
 *
 * You should have received a copy of the GNU General Public License version
 * 2 along with this work; if not, write to the Free Software Foundation,
 * Inc., 51 Franklin St, Fifth Floor, Boston, MA 02110-1301 USA.
 *
 * Please contact Oracle, 500 Oracle Parkway, Redwood Shores, CA 94065 USA
 * or visit www.oracle.com if you need additional information or have any
 * questions.
 */

/*
 * This file is available under and governed by the GNU General Public
 * License version 2 only, as published by the Free Software Foundation.
 * However, the following notice accompanied the original version of this
 * file:
 *
 * Copyright (c) 2009-2012, Stephen Colebourne & Michael Nascimento Santos
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
namespace Celest\Zone;

use Celest\Chrono\IsoChronology;
use Celest\DayOfWeek;
use Celest\IllegalArgumentException;
use Celest\LocalDate;
use Celest\LocalDateTime;
use Celest\LocalTime;
use Celest\Month;
use Celest\Temporal\TemporalAdjusters;
use Celest\ZoneOffset;

/**
 * A rule expressing how to create a transition.
 * <p>
 * This class allows rules for identifying future transitions to be expressed.
 * A rule might be written in many forms:
 * <ul>
 * <li>the 16th March
 * <li>the Sunday on or after the 16th March
 * <li>the Sunday on or before the 16th March
 * <li>the last Sunday in February
 * </ul>
 * These different rule types can be expressed and queried.
 *
 * @implSpec
 * This class is immutable and thread-safe.
 *
 * @since 1.8
 */
final class ZoneOffsetTransitionRule
{

    /**
     * The month of the month-day of the first day of the cutover week.
     * The actual date will be adjusted by the dowChange field.
     * @var Month
     */
    private $month;
    /**
     * The day-of-month of the month-day of the cutover week.
     * If positive, it is the start of the week where the cutover can occur.
     * If negative, it represents the end of the week where cutover can occur.
     * The value is the number of days from the end of the month, such that
     * {@code -1} is the last day of the month, {@code -2} is the second
     * to last day, and so on.
     * @var int
     */
    private $dom;
    /**
     * The cutover day-of-week, null to retain the day-of-month.
     * @var DayOfWeek
     */
    private $dow;
    /**
     * The cutover time in the 'before' offset.
     * @var LocalTime
     */
    private $time;
    /**
     * Whether the cutover time is midnight at the end of day.
     * @var bool
     */
    private $timeEndOfDay;
    /**
     * The definition of how the local time should be interpreted.
     * @var TimeDefinition
     */
    private $timeDefinition;
    /**
     * The standard offset at the cutover.
     * @var ZoneOffset
     */
    private $standardOffset;
    /**
     * The offset before the cutover.
     * @var ZoneOffset
     */
    private $offsetBefore;
    /**
     * The offset after the cutover.
     * @var ZoneOffset
     */
    private $offsetAfter;

    /**
     * Obtains an instance defining the yearly rule to create transitions between two offsets.
     * <p>
     * Applications should normally obtain an instance from {@link ZoneRules}.
     * This factory is only intended for use when creating {@link ZoneRules}.
     *
     * @param Month $month the month of the month-day of the first day of the cutover week, not null
     * @param int $dayOfMonthIndicator the day of the month-day of the cutover week, positive if the week is that
     *  day or later, negative if the week is that day or earlier, counting from the last day of the month,
     *  from -28 to 31 excluding 0
     * @param DayOfWeek|null $dayOfWeek the required day-of-week, null if the month-day should not be changed
     * @param LocalTime $time the cutover time in the 'before' offset, not null
     * @param bool $timeEndOfDay whether the time is midnight at the end of day
     * @param TimeDefinition $timeDefnition how to interpret the cutover
     * @param ZoneOffset $standardOffset the standard offset in force at the cutover, not null
     * @param ZoneOffset $offsetBefore the offset before the cutover, not null
     * @param ZoneOffset $offsetAfter the offset after the cutover, not null
     * @return ZoneOffsetTransitionRule the rule, not null
     * @throws IllegalArgumentException if the day of month indicator is invalid
     * @throws IllegalArgumentException if the end of day flag is true when the time is not midnight
     */
    public static function of(
        Month $month,
        $dayOfMonthIndicator,
        $dayOfWeek,
        LocalTime $time,
        $timeEndOfDay,
        TimeDefinition $timeDefnition,
        ZoneOffset $standardOffset,
        ZoneOffset $offsetBefore,
        ZoneOffset $offsetAfter)
    {
        if ($dayOfMonthIndicator < -28 || $dayOfMonthIndicator > 31 || $dayOfMonthIndicator == 0) {
            throw new IllegalArgumentException("Day of month indicator must be between -28 and 31 inclusive excluding zero");
        }

        if ($timeEndOfDay && $time->equals(LocalTime::MIDNIGHT()) == false) {
            throw new IllegalArgumentException("Time must be midnight when end of day flag is true");
        }
        return new ZoneOffsetTransitionRule($month, $dayOfMonthIndicator, $dayOfWeek, $time, $timeEndOfDay, $timeDefnition, $standardOffset, $offsetBefore, $offsetAfter);
    }

    /**
     * Creates an instance defining the yearly rule to create transitions between two offsets.
     *
     * @param Month $month the month of the month-day of the first day of the cutover week, not null
     * @param int $dayOfMonthIndicator the day of the month-day of the cutover week, positive if the week is that
     *  day or later, negative if the week is that day or earlier, counting from the last day of the month,
     *  from -28 to 31 excluding 0
     * @param $dayOfWeek|null DayOfWeek the required day-of-week, null if the month-day should not be changed
     * @param LocalTime $time the cutover time in the 'before' offset, not null
     * @param bool $timeEndOfDay whether the time is midnight at the end of day
     * @param TimeDefinition $timeDefinition how to interpret the cutover
     * @param ZoneOffset $standardOffset the standard offset in force at the cutover, not null
     * @param ZoneOffset $offsetBefore the offset before the cutover, not null
     * @param ZoneOffset $offsetAfter the offset after the cutover, not null
     * @throws IllegalArgumentException if the day of month indicator is invalid
     * @throws IllegalArgumentException if the end of day flag is true when the time is not midnight
     */
    private function __construct(
        Month $month,
        $dayOfMonthIndicator,
        $dayOfWeek,
        LocalTime $time,
        $timeEndOfDay,
        TimeDefinition $timeDefinition,
        ZoneOffset $standardOffset,
        ZoneOffset $offsetBefore,
        ZoneOffset $offsetAfter)
    {
        $this->month = $month;
        $this->dom = $dayOfMonthIndicator;
        $this->dow = $dayOfWeek;
        $this->time = $time;
        $this->timeEndOfDay = $timeEndOfDay;
        $this->timeDefinition = $timeDefinition;
        $this->standardOffset = $standardOffset;
        $this->offsetBefore = $offsetBefore;
        $this->offsetAfter = $offsetAfter;
    }

//-----------------------------------------------------------------------
    //-----------------------------------------------------------------------
    /**
     * Gets the month of the transition.
     * <p>
     * If the rule defines an exact date then the month is the month of that date.
     * <p>
     * If the rule defines a week where the transition might occur, then the month
     * if the month of either the earliest or latest possible date of the cutover.
     *
     * @return Month the month of the transition, not null
     */
    public function getMonth()
    {
        return $this->month;
    }

    /**
     * Gets the indicator of the day-of-month of the transition.
     * <p>
     * If the rule defines an exact date then the day is the month of that date.
     * <p>
     * If the rule defines a week where the transition might occur, then the day
     * defines either the start of the end of the transition week.
     * <p>
     * If the value is positive, then it represents a normal day-of-month, and is the
     * earliest possible date that the transition can be.
     * The date may refer to 29th February which should be treated as 1st March in non-leap years.
     * <p>
     * If the value is negative, then it represents the number of days back from the
     * end of the month where {@code -1} is the last day of the month.
     * In this case, the day identified is the latest possible date that the transition can be.
     *
     * @return int the day-of-month indicator, from -28 to 31 excluding 0
     */
    public function getDayOfMonthIndicator()
    {
        return $this->dom;
    }

    /**
     * Gets the day-of-week of the transition.
     * <p>
     * If the rule defines an exact date then this returns null.
     * <p>
     * If the rule defines a week where the cutover might occur, then this method
     * returns the day-of-week that the month-day will be adjusted to.
     * If the day is positive then the adjustment is later.
     * If the day is negative then the adjustment is earlier.
     *
     * @return DayOfWeek the day-of-week that the transition occurs, null if the rule defines an exact date
     */
    public function getDayOfWeek()
    {
        return $this->dow;
    }

    /**
     * Gets the local time of day of the transition which must be checked with
     * {@link #isMidnightEndOfDay()}.
     * <p>
     * The time is converted into an instant using the time definition.
     *
     * @return LocalTime the local time of day of the transition, not null
     */
    public function getLocalTime()
    {
        return $this->time;
    }

    /**
     * Is the transition local time midnight at the end of day.
     * <p>
     * The transition may be represented as occurring at 24:00.
     *
     * @return bool whether a local time of midnight is at the start or end of the day
     */
    public function isMidnightEndOfDay()
    {
        return $this->timeEndOfDay;
    }

    /**
     * Gets the time definition, specifying how to convert the time to an instant.
     * <p>
     * The local time can be converted to an instant using the standard offset,
     * the wall offset or UTC.
     *
     * @return TimeDefinition the time definition, not null
     */
    public function getTimeDefinition()
    {
        return $this->timeDefinition;
    }

    /**
     * Gets the standard offset in force at the transition.
     *
     * @return ZoneOffset the standard offset, not null
     */
    public function getStandardOffset()
    {
        return $this->standardOffset;
    }

    /**
     * Gets the offset before the transition.
     *
     * @return ZoneOffset the offset before, not null
     */
    public
    function getOffsetBefore()
    {
        return $this->offsetBefore;
    }

    /**
     * Gets the offset after the transition.
     *
     * @return ZoneOffset the offset after, not null
     */
    public function getOffsetAfter()
    {
        return $this->offsetAfter;
    }

    //-----------------------------------------------------------------------
    /**
     * Creates a transition instance for the specified year.
     * <p>
     * Calculations are performed using the ISO-8601 chronology.
     *
     * @param int $year the year to create a transition for, not null
     * @return ZoneOffsetTransition the transition instance, not null
     */
    public function createTransition($year)
    {
        if ($this->dom < 0) {
            $date = LocalDate::of($year, $this->month, $this->month->length(IsoChronology::INSTANCE()->isLeapYear($year)) + 1 + $this->dom);
            if ($this->dow !== null) {
                $date = $date->adjust(TemporalAdjusters::previousOrSame($this->dow));
            }
        } else {
            $date = LocalDate::of($year, $this->month, $this->dom);
            if ($this->dow !== null) {
                $date = $date->adjust(TemporalAdjusters::nextOrSame($this->dow));
            }
        }
        if ($this->timeEndOfDay) {
            $date = $date->plusDays(1);
        }
        $localDT = LocalDateTime::ofDateAndTime($date, $this->time);
        $transition = $this->timeDefinition->createDateTime($localDT, $this->standardOffset, $this->offsetBefore);
        return ZoneOffsetTransition::of($transition, $this->offsetBefore, $this->offsetAfter);
    }

    //-----------------------------------------------------------------------
    /**
     * Checks if this object equals another.
     * <p>
     * The entire state of the object is compared.
     *
     * @param mixed $otherRule the other object to compare to, null returns false
     * @return bool true if equal
     */
    public function equals($otherRule)
    {
        if ($otherRule === $this) {
            return true;
        }

        if ($otherRule instanceof ZoneOffsetTransitionRule) {
            $other = $otherRule;
            return $this->month == $other->month && $this->dom == $other->dom && $this->dow == $other->dow &&
            $this->timeDefinition == $other->timeDefinition &&
            $this->time->equals($other->time) &&
            $this->timeEndOfDay == $other->timeEndOfDay &&
            $this->standardOffset->equals($other->standardOffset) &&
            $this-> offsetBefore->equals($other->offsetBefore) &&
            $this->offsetAfter->equals($other->offsetAfter);
        }
        return false;
    }

//-----------------------------------------------------------------------
    /**
     * Returns a string describing this object.
     *
     * @return string a string for debugging, not null
     */
    public
    function __toString()
    {
        $buf = "TransitionRule["
            . ($this->offsetBefore->compareTo($this->offsetAfter) > 0 ? "Gap " : "Overlap ")
            . $this->offsetBefore . " to " . $this->offsetAfter . ", ";
        if ($this->dow != null) {
            if ($this->dom == -1) {
                $buf .= $this->dow . " on or before last day of " . $this->month;
            } else if ($this->dom < 0) {
                $buf .= $this->dow . " on or before last day minus " . (-$this->dom - 1) . " of " . $this->month;
            } else {
                $buf .= $this->dow . " on or after " . $this->month . ' ' . $this->dom;
            }
        } else {
            $buf .= $this->month . ' ' . $this->dom;
        }
        $buf .= " at " . ($this->timeEndOfDay ? "24:00" : $this->time)
            . " " . $this->timeDefinition
            . ", standard offset " . $this->standardOffset
            . ']';
        return $buf;
    }
}
