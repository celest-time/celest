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
 * Copyright (c) 2007-2012, Stephen Colebourne & Michael Nascimento Santos
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

namespace Celest\Chrono;

use Celest\Helper\Math;
use Celest\LocalTime;
use Celest\Temporal\ChronoField;
use Celest\Temporal\ChronoUnit;
use Celest\Temporal\Temporal;
use Celest\Temporal\TemporalAdjuster;
use Celest\Temporal\TemporalField;
use Celest\Temporal\TemporalUnit;
use Celest\ZoneId;

/**
 * A date-time without a time-zone for the calendar neutral API.
 * <p>
 * {@code ChronoLocalDateTime} is an immutable date-time object that represents a date-time, often
 * viewed as year-month-day-hour-minute-second. This object can also access other
 * fields such as day-of-year, day-of-week and week-of-year.
 * <p>
 * This class stores all date and time fields, to a precision of nanoseconds.
 * It does not store or represent a time-zone. For example, the value
 * "2nd October 2007 at 13:45.30.123456789" can be stored in an {@code ChronoLocalDateTime}.
 *
 * @implSpec
 * This class is immutable and thread-safe.
 * @serial
 * @param <D> the concrete type for the date of this date-time
 * @since 1.8
 */
final class ChronoLocalDateTimeImpl extends AbstractChronoLocalDateTime implements ChronoLocalDateTime, Temporal, TemporalAdjuster
{
    /**
     * Hours per day.
     */
    const HOURS_PER_DAY = 24;
    /**
     * Minutes per hour.
     */
    const MINUTES_PER_HOUR = 60;
    /**
     * Minutes per day.
     */
    const MINUTES_PER_DAY = self::MINUTES_PER_HOUR * self::HOURS_PER_DAY;
    /**
     * Seconds per minute.
     */
    const SECONDS_PER_MINUTE = 60;
    /**
     * Seconds per hour.
     */
    const SECONDS_PER_HOUR = self::SECONDS_PER_MINUTE * self::MINUTES_PER_HOUR;
    /**
     * Seconds per day.
     */
    const SECONDS_PER_DAY = self::SECONDS_PER_HOUR * self::HOURS_PER_DAY;
    /**
     * Milliseconds per day.
     */
    const MILLIS_PER_DAY = self::SECONDS_PER_DAY * 1000;
    /**
     * Microseconds per day.
     */
    const MICROS_PER_DAY = self::SECONDS_PER_DAY * 1000000;
    /**
     * Nanos per second.
     */
    const NANOS_PER_SECOND = 1000000000;
    /**
     * Nanos per minute.
     */
    const NANOS_PER_MINUTE = self::NANOS_PER_SECOND * self::SECONDS_PER_MINUTE;
    /**
     * Nanos per hour.
     */
    const NANOS_PER_HOUR = self::NANOS_PER_MINUTE * self::MINUTES_PER_HOUR;
    /**
     * Nanos per day.
     */
    const NANOS_PER_DAY = self::NANOS_PER_HOUR * self::HOURS_PER_DAY;

    /**
     * The date part.
     * @var ChronoLocalDate
     */
    private $date;
    /**
     * The time part.
     * @var LocalTime
     */
    private $time;

    //-----------------------------------------------------------------------
    /**
     * Obtains an instance of {@code ChronoLocalDateTime} from a date and time.
     *
     * @param ChronoLocalDate $date the local date, not null
     * @param LocalTime $time the local time, not null
     * @return ChronoLocalDateTimeImpl the local date-time, not null
     */
    static function of(ChronoLocalDate $date, LocalTime $time)
    {
        return new ChronoLocalDateTimeImpl($date, $time);
    }

    /**
     * Casts the {@code Temporal} to {@code ChronoLocalDateTime} ensuring it has the specified chronology.
     *
     * @param Chronology $chrono the chronology to check for, not null
     * @param Temporal $temporal a date-time to cast, not null
     * @return ChronoLocalDateTimeImpl the date-time checked and cast to {@code ChronoLocalDateTimeImpl}, not null
     * @throws ClassCastException if the date-time cannot be cast to ChronoLocalDateTimeImpl
     *  or the chronology is not equal this Chronology
     */
    static function ensureValid(Chronology $chrono, Temporal $temporal)
    {
        // TODO check cast
        $other = $temporal;
        if ($chrono->equals($other->getChronology()) === false) {
            throw new ClassCastException("Chronology mismatch, required: " . $chrono->getId()
                . ", actual: " . $other->getChronology()->getId());
        }
        return $other;
    }

    /**
     * Constructor.
     *
     * @param ChronoLocalDate $date the date part of the date-time, not null
     * @param LocalTime $time the time part of the date-time, not null
     */
    private function __construct(ChronoLocalDate $date, LocalTime $time)
    {
        $this->date = $date;
        $this->time = $time;
    }

    /**
     * Returns a copy of this date-time with the new date and time, checking
     * to see if a new object is in fact required.
     *
     * @param Temporal $newDate the date of the new date-time, not null
     * @param LocalTime $newTime the time of the new date-time, not null
     * @return ChronoLocalDateTimeImpl the date-time, not null
     */
    private function _with(Temporal $newDate, LocalTime $newTime)
    {
        if ($this->date === $newDate && $this->time === $newTime) {
            return $this;
        }
        // Validate that the new Temporal is a ChronoLocalDate (and not something else)
        $cd = ChronoLocalDateImpl::ensureValid($this->date->getChronology(), $newDate);
        return new ChronoLocalDateTimeImpl($cd, $newTime);
    }

    //-----------------------------------------------------------------------
    public function toLocalDate()
    {
        return $this->date;
    }

    public function toLocalTime()
    {
        return $this->time;
    }

    //-----------------------------------------------------------------------
    public function isSupported(TemporalField $field)
    {
        if ($field instanceof ChronoField) {
            $f = $field;
            return $f->isDateBased() || $f->isTimeBased();
        }
        return $field !== null && $field->isSupportedBy($this);
    }

    public function range(TemporalField $field)
    {
        if ($field instanceof ChronoField) {
            $f = $field;
            return ($f->isTimeBased() ? $this->time->range($field) : $this->date->range($field));
        }
        return $field->rangeRefinedBy($this);
    }

    public function get(TemporalField $field)
    {
        if ($field instanceof ChronoField) {
            $f = $field;
            return ($f->isTimeBased() ? $this->time->get($field) : $this->date->get($field));
        }
        return $this->range($field)->checkValidIntValue($this->getLong($field), $field);
    }

    public function getLong(TemporalField $field)
    {
        if ($field instanceof ChronoField) {
            $f = $field;
            return ($f->isTimeBased() ? $this->time->getLong($field) : $this->date->getLong($field));
        }
        return $field->getFrom($this);
    }

    //-----------------------------------------------------------------------
    public function adjust(TemporalAdjuster $adjuster)
    {
        if ($adjuster instanceof ChronoLocalDate) {
            // The Chronology is checked in with(date,time)
            return $this->_with($adjuster, $this->time);
        } else if ($adjuster instanceof LocalTime) {
            return $this->_with($this->date, $adjuster);
        } else if ($adjuster instanceof ChronoLocalDateTimeImpl) {
            return ChronoLocalDateTimeImpl::ensureValid($this->date->getChronology(), $adjuster);
        }
        return ChronoLocalDateTimeImpl::ensureValid($this->date->getChronology(), $adjuster->adjustInto($this));
    }

    public function with(TemporalField $field, $newValue)
    {
        if ($field instanceof ChronoField) {
            $f = $field;
            if ($f->isTimeBased()) {
                return $this->_with($this->date, $this->time->with($field, $newValue));
            } else {
                return $this->_with($this->date->with($field, $newValue), $this->time);
            }
        }
        return ChronoLocalDateTimeImpl::ensureValid($this->date->getChronology(), $field->adjustInto($this, $newValue));
    }

    //-----------------------------------------------------------------------
    public function plus($amountToAdd, TemporalUnit $unit)
    {
        if ($unit instanceof ChronoUnit) {
            $f = $unit;
            switch ($f) {
                case ChronoUnit::NANOS():
                    return $this->plusNanos($amountToAdd);
                case ChronoUnit::MICROS():
                    return $this->plusDays(Math::div($amountToAdd, self::MICROS_PER_DAY))->plusNanos(($amountToAdd % self::MICROS_PER_DAY) * 1000);
                case ChronoUnit::MILLIS():
                    return $this->plusDays(Math::div($amountToAdd, self::MILLIS_PER_DAY))->plusNanos(($amountToAdd % self::MILLIS_PER_DAY) * 1000000);
                case ChronoUnit::SECONDS():
                    return $this->plusSeconds($amountToAdd);
                case ChronoUnit::MINUTES():
                    return $this->plusMinutes($amountToAdd);
                case ChronoUnit::HOURS():
                    return $this->plusHours($amountToAdd);
                case ChronoUnit::HALF_DAYS():
                    return $this->plusDays(Math::div($amountToAdd, 256))->plusHours(($amountToAdd % 256) * 12);  // no overflow (256 is multiple of 2)
            }
            return $this->_with($this->date->plus($amountToAdd, $unit), $this->time);
        }
        return ChronoLocalDateTimeImpl::ensureValid($this->date->getChronology(), $unit->addTo($this, $amountToAdd));
    }

    private function plusDays($days)
    {
        return $this->_with($this->date->plus($days, ChronoUnit::DAYS()), $this->time);
    }

    private function plusHours($hours)
    {
        return $this->plusWithOverflow($this->date, $hours, 0, 0, 0);
    }

    private function plusMinutes($minutes)
    {
        return $this->plusWithOverflow($this->date, 0, $minutes, 0, 0);
    }

    function plusSeconds($seconds)
    {
        return $this->plusWithOverflow($this->date, 0, 0, $seconds, 0);
    }

    private function plusNanos($nanos)
    {
        return $this->plusWithOverflow($this->date, 0, 0, 0, $nanos);
    }

    //-----------------------------------------------------------------------
    private function plusWithOverflow(ChronoLocalDate $newDate, $hours, $minutes, $seconds, $nanos)
    {
        // 9223372036854775808 long, 2147483648 int
        if (($hours | $minutes | $seconds | $nanos) === 0) {
            return $this->_with($newDate, $this->time);
        }
        $totDays = Math::div($nanos, self::NANOS_PER_DAY) +             //   max/24*60*60*1B
            Math::div($seconds, self::SECONDS_PER_DAY) +                //   max/24*60*60
            Math::div($minutes, self::MINUTES_PER_DAY) +                //   max/24*60
            Math::div($hours, self::HOURS_PER_DAY);                     //   max/24
        $totNanos = $$nanos % self::NANOS_PER_DAY +                    //   max  86400000000000
            ($seconds % self::SECONDS_PER_DAY) * self::NANOS_PER_SECOND +   //   max  86400000000000
            ($minutes % self::MINUTES_PER_DAY) * self::NANOS_PER_MINUTE +   //   max  86400000000000
            ($hours % self::HOURS_PER_DAY) * self::NANOS_PER_HOUR;          //   max  86400000000000
        $curNoD = $this->time->toNanoOfDay();                          //   max  86400000000000
        $totNanos = $totNanos + $curNoD;                              // total 432000000000000
        $totDays += Math::floorDiv($totNanos, self::NANOS_PER_DAY);
        $newNoD = Math::floorMod($totNanos, self::NANOS_PER_DAY);
        $newTime = ($newNoD === $curNoD ? $this->time : LocalTime::ofNanoOfDay($newNoD));
        return $this->_with($newDate->plus($totDays, ChronoUnit::DAYS()), $newTime);
    }

    //-----------------------------------------------------------------------
    public function atZone(ZoneId $zone)
    {
        return ChronoZonedDateTimeImpl::ofBest($this, $zone, null);
    }

    //-----------------------------------------------------------------------
    public function until(Temporal $endExclusive, TemporalUnit $unit)
    {
        $end = $this->getChronology()->localDateTime($endExclusive);
        if ($unit instanceof ChronoUnit) {
            if ($unit->isTimeBased()) {
                $amount = $end->getLong(ChronoField::EPOCH_DAY()) - $this->date->getLong(ChronoField::EPOCH_DAY());
                switch ($unit) {
                    case ChronoUnit::NANOS():
                        $amount = Math::multiplyExact($amount, self::NANOS_PER_DAY);
                        break;
                    case ChronoUnit::MICROS():
                        $amount = Math::multiplyExact($amount, self::MICROS_PER_DAY);
                        break;
                    case ChronoUnit::MILLIS():
                        $amount = Math::multiplyExact($amount, self::MILLIS_PER_DAY);
                        break;
                    case ChronoUnit::SECONDS():
                        $amount = Math::multiplyExact($amount, self::SECONDS_PER_DAY);
                        break;
                    case ChronoUnit::MINUTES():
                        $amount = Math::multiplyExact($amount, self::MINUTES_PER_DAY);
                        break;
                    case ChronoUnit::HOURS():
                        $amount = Math::multiplyExact($amount, self::HOURS_PER_DAY);
                        break;
                    case ChronoUnit::HALF_DAYS():
                        $amount = Math::multiplyExact($amount, 2);
                        break;
                }
                return Math::addExact($amount, $this->time->until($end->toLocalTime(), $unit));
            }
            $endDate = $end->toLocalDate();
            if ($end->toLocalTime()->isBefore($this->time)) {
                $endDate = $endDate->minus(1, ChronoUnit::DAYS());
            }
            return $this->date->until($endDate, $unit);
        }
        return $unit->between($this, $end);
    }

    //-----------------------------------------------------------------------
    public function equals($obj)
    {
        if ($this === $obj) {
            return true;
        }
        if ($obj instanceof ChronoLocalDateTime) {
            return $this->compareTo($obj) === 0;
        }
        return false;
    }

    public function __toString()
    {
        return $this->toLocalDate()->__toString() . 'T' . $this->toLocalTime()->__toString();
    }

}
