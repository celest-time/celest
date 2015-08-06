<?php

/*
 * Copyright (c) 2012, 2015, Oracle and/or its affiliates. All rights reserved.
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

namespace Php\Time;

use Php\Time\Temporal\Temporal;
use Php\Time\Temporal\TemporalAccessor;
use Php\Time\Temporal\TemporalAdjuster;
use Php\Time\Temporal\TemporalAmount;
use Php\Time\Temporal\TemporalField;
use Php\Time\Temporal\TemporalUnit;
use Php\Time\Temporal\ValueRange;

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
const MINUTES_PER_DAY = MINUTES_PER_HOUR * HOURS_PER_DAY;
/**
 * Seconds per minute.
 */
const SECONDS_PER_MINUTE = 60;
/**
 * Seconds per hour.
 */
const SECONDS_PER_HOUR = SECONDS_PER_MINUTE * MINUTES_PER_HOUR;
/**
 * Seconds per day.
 */
const SECONDS_PER_DAY = SECONDS_PER_HOUR * HOURS_PER_DAY;
/**
 * Milliseconds per day.
 */
const MILLIS_PER_DAY = SECONDS_PER_DAY * 1000;
/**
 * Microseconds per day.
 */
const MICROS_PER_DAY = SECONDS_PER_DAY * 1000000;
/**
 * Nanos per second.
 */
const NANOS_PER_SECOND = 1000000000;
/**
 * Nanos per minute.
 */
const NANOS_PER_MINUTE = NANOS_PER_SECOND * SECONDS_PER_MINUTE;
/**
 * Nanos per hour.
 */
const NANOS_PER_HOUR = NANOS_PER_MINUTE * MINUTES_PER_HOUR;
/**
 * Nanos per day.
 */
const NANOS_PER_DAY = NANOS_PER_HOUR * HOURS_PER_DAY;

/**
 * A time without a time-zone in the ISO-8601 calendar system,
 * such as {@code 10:15:30}.
 * <p>
 * {@code LocalTime} is an immutable date-time object that represents a time,
 * often viewed as hour-minute-second.
 * Time is represented to nanosecond precision.
 * For example, the value "13:45.30.123456789" can be stored in a {@code LocalTime}.
 * <p>
 * This class does not store or represent a date or time-zone.
 * Instead, it is a description of the local time as seen on a wall clock.
 * It cannot represent an instant on the time-line without additional information
 * such as an offset or time-zone.
 * <p>
 * The ISO-8601 calendar system is the modern civil calendar system used today
 * in most of the world. This API assumes that all calendar systems use the same
 * representation, this class, for time-of-day.
 *
 * <p>
 * This is a <a href="{@docRoot}/java/lang/doc-files/ValueBased.html">value-based</a>
 * class; use of identity-sensitive operations (including reference equality
 * ({@code ==}), identity hash code, or synchronization) on instances of
 * {@code LocalTime} may have unpredictable results and should be avoided.
 * The {@code equals} method should be used for comparisons.
 *
 * @implSpec
 * This class is immutable and thread-safe.
 *
 * @since 1.8
 */
public

final class LocalTime
    implements Temporal, TemporalAdjuster
{

    public static function init()
    {
        for ($i = 0; $i < 24; $i++) {
            self::$HOURS[$i] = new LocalTime($i, 0, 0, 0);
        }
        self::$MIDNIGHT = self::$HOURS[0];
        self::$NOON = self::$HOURS[12];
        self::$MIN = self::$HOURS[0];
        self::$MAX = new LocalTime(23, 59, 59, 999999999);
    }

    /**
     * The minimum supported {@code LocalTime}, '00:00'.
     * This is the time of midnight at the start of the day.
     * @return LocalTime
     */
    public static function MIN()
    {
        return self::$MIN;
    }

    /** @var LocalTime */
    private static $MIN;

    /**
     * The maximum supported {@code LocalTime}, '23:59:59.999999999'.
     * This is the time just before midnight at the end of the day.
     */
    public static function MAX()
    {
        return self::$MAX;
    }

    /** @var LocalTime */
    private static $MAX;

    /**
     * The time of midnight at the start of the day, '00:00'.
     */
    public static function MIDNIGHT()
    {
        return self::$MIDNIGHT;
    }

    /** @var LocalTime */
    private static $MIDNIGHT;

    /**
     * The time of noon in the middle of the day, '12:00'.
     */
    public static function NOON()
    {
        return self::$NOON;
    }

    /** @var LocalTime */
    private static $NOON;
    /**
     * Constants for the local time of each hour.
     * @var LocalTime[]
     */
    private static $HOURS = [];


    /**
     * The hour.
     * @var int
     */
    private $hour;
    /**
     * The minute.
     * @var int
     */
    private $minute;
    /**
     * The second.
     * @var int
     */
    private $second;
    /**
     * The nanosecond.
     * @var int
     */
    private $nano;

    //-----------------------------------------------------------------------
    /**
     * Obtains the current time from the system clock in the default time-zone.
     * <p>
     * This will query the {@link Clock#systemDefaultZone() system clock} in the default
     * time-zone to obtain the current time.
     * <p>
     * Using this method will prevent the ability to use an alternate clock for testing
     * because the clock is hard-coded.
     *
     * @return LocalTime the current time using the system clock and default time-zone, not null
     */
    public static function now()
    {
        return self::now(Clock::systemDefaultZone());
    }

    /**
     * Obtains the current time from the system clock in the specified time-zone.
     * <p>
     * This will query the {@link Clock#system(ZoneId) system clock} to obtain the current time.
     * Specifying the time-zone avoids dependence on the default time-zone.
     * <p>
     * Using this method will prevent the ability to use an alternate clock for testing
     * because the clock is hard-coded.
     *
     * @param $zone ZoneId the zone ID to use, not null
     * @return LocalTime the current time using the system clock, not null
     */
    public static function now(ZoneId $zone)
    {
        return self::now(Clock::system($zone));
    }

    /**
     * Obtains the current time from the specified clock.
     * <p>
     * This will query the specified clock to obtain the current time.
     * Using this method allows the use of an alternate clock for testing.
     * The alternate clock may be introduced using {@link Clock dependency injection}.
     *
     * @param $clock Clock the clock to use, not null
     * @return LocalTime the current time, not null
     */
    public
    static function now(Clock $clock)
    {
        // inline OffsetTime factory to avoid creating object and InstantProvider checks
        $now = $clock->instant();  // called once
        $offset = $clock->getZone()->getRules()->getOffset($now);
        $localSecond = $now->getEpochSecond() + $offset->getTotalSeconds();  // overflow caught later
        $secsOfDay = (int)Math::floorMod($localSecond, SECONDS_PER_DAY);
        return self::ofNanoOfDay($secsOfDay * NANOS_PER_SECOND + $now->getNano());
    }

//-----------------------------------------------------------------------
    /**
     * Obtains an instance of {@code LocalTime} from an hour and minute.
     * <p>
     * This returns a {@code LocalTime} with the specified hour and minute.
     * The second and nanosecond fields will be set to zero.
     *
     * @param $hour int the hour-of-day to represent, from 0 to 23
     * @param $minute int the minute-of-hour to represent, from 0 to 59
     * @return LocalTime the local time, not null
     * @throws DateTimeException if the value of any field is out of range
     */
    public static function of($hour, $minute)
    {
        HOUR_OF_DAY::checkValidValue($hour);
        if ($minute == 0) {
            return self::$HOURS[$hour];  // for performance
        }

        MINUTE_OF_HOUR::checkValidValue($minute);
        return new LocalTime($hour, $minute, 0, 0);
    }

    /**
     * Obtains an instance of {@code LocalTime} from an hour, minute and second.
     * <p>
     * This returns a {@code LocalTime} with the specified hour, minute and second.
     * The nanosecond field will be set to zero.
     *
     * @param $hour int the hour-of-day to represent, from 0 to 23
     * @param $minute int the minute-of-hour to represent, from 0 to 59
     * @param $second int the second-of-minute to represent, from 0 to 59
     * @return LocalTime the local time, not null
     * @throws DateTimeException if the value of any field is out of range
     */
    public static function of($hour, $minute, $second)
    {
        HOUR_OF_DAY::checkValidValue($hour);
        if (($minute | $second) == 0) {
            return self::$HOURS[$hour];  // for performance
        }

        MINUTE_OF_HOUR::checkValidValue($minute);
        SECOND_OF_MINUTE::checkValidValue($second);
        return new LocalTime($hour, $minute, $second, 0);
    }

    /**
     * Obtains an instance of {@code LocalTime} from an hour, minute, second and nanosecond.
     * <p>
     * This returns a {@code LocalTime} with the specified hour, minute, second and nanosecond.
     *
     * @param $hour int the hour-of-day to represent, from 0 to 23
     * @param $minute int the minute-of-hour to represent, from 0 to 59
     * @param $second int the second-of-minute to represent, from 0 to 59
     * @param $nanoOfSecond int the nano-of-second to represent, from 0 to 999,999,999
     * @return LocalTime the local time, not null
     * @throws DateTimeException if the value of any field is out of range
     */
    public static function of($hour, $minute, $second, $nanoOfSecond)
    {
        HOUR_OF_DAY::checkValidValue($hour);
        MINUTE_OF_HOUR::checkValidValue($minute);
        SECOND_OF_MINUTE::checkValidValue($second);
        NANO_OF_SECOND::checkValidValue($nanoOfSecond);
        return self::create($hour, $minute, $second, $nanoOfSecond);
    }

//-----------------------------------------------------------------------
    /**
     * Obtains an instance of {@code LocalTime} from a second-of-day value.
     * <p>
     * This returns a {@code LocalTime} with the specified second-of-day.
     * The nanosecond field will be set to zero.
     *
     * @param $secondOfDay int the second-of-day, from {@code 0} to {@code 24 * 60 * 60 - 1}
     * @return LocalTime the local time, not null
     * @throws DateTimeException if the second-of-day value is invalid
     */
    public
    static function ofSecondOfDay($secondOfDay)
    {
        SECOND_OF_DAY::checkValidValue($secondOfDay);
        $hours = (int)($secondOfDay / SECONDS_PER_HOUR);
        $secondOfDay -= $hours * SECONDS_PER_HOUR;
        $minutes = (int)($secondOfDay / SECONDS_PER_MINUTE);
        $secondOfDay -= $minutes * SECONDS_PER_MINUTE;
        return self::create($hours, $minutes, (int)$secondOfDay, 0);
    }

    /**
     * Obtains an instance of {@code LocalTime} from a nanos-of-day value.
     * <p>
     * This returns a {@code LocalTime} with the specified nanosecond-of-day.
     *
     * @param $nanoOfDay int the nano of day, from {@code 0} to {@code 24 * 60 * 60 * 1,000,000,000 - 1}
     * @return LocalTime the local time, not null
     * @throws DateTimeException if the nanos of day value is invalid
     */
    public
    static function ofNanoOfDay($nanoOfDay)
    {
        NANO_OF_DAY::checkValidValue($nanoOfDay);
        $hours = (int)($nanoOfDay / NANOS_PER_HOUR);
        $nanoOfDay -= $hours * NANOS_PER_HOUR;
        $minutes = (int)($nanoOfDay / NANOS_PER_MINUTE);
        $nanoOfDay -= $minutes * NANOS_PER_MINUTE;
        $seconds = (int)($nanoOfDay / NANOS_PER_SECOND);
        $nanoOfDay -= $seconds * NANOS_PER_SECOND;
        return self::create($hours, $minutes, $seconds, (int)$nanoOfDay);
    }

//-----------------------------------------------------------------------
    /**
     * Obtains an instance of {@code LocalTime} from a temporal object.
     * <p>
     * This obtains a local time based on the specified temporal.
     * A {@code TemporalAccessor} represents an arbitrary set of date and time information,
     * which this factory converts to an instance of {@code LocalTime}.
     * <p>
     * The conversion uses the {@link TemporalQueries#localTime()} query, which relies
     * on extracting the {@link ChronoField#NANO_OF_DAY NANO_OF_DAY} field.
     * <p>
     * This method matches the signature of the functional interface {@link TemporalQuery}
     * allowing it to be used as a query via method reference, {@code LocalTime::from}.
     *
     * @param $temporal TemporalAccessor the temporal object to convert, not null
     * @return LocalTime the local time, not null
     * @throws DateTimeException if unable to convert to a {@code LocalTime}
     */
    public
    static function from(TemporalAccessor $temporal)
    {
        $time = $temporal->query(TemporalQueries::localTime());
        if ($time == null) {
            throw new DateTimeException("Unable to obtain LocalTime from TemporalAccessor: " .
                $temporal . " of type " . get_class($temporal);
        }

        return $time;
    }

//-----------------------------------------------------------------------
    /**
     * Obtains an instance of {@code LocalTime} from a text string such as {@code 10:15}.
     * <p>
     * The string must represent a valid time and is parsed using
     * {@link java.time.format.DateTimeFormatter#ISO_LOCAL_TIME}.
     *
     * @param string $text the text to parse such as "10:15:30", not null
     * @return LocalTime the parsed local time, not null
     * @throws DateTimeParseException if the text cannot be parsed
     */
    public
    static function parse($text)
    {
        return self::parse($text, DateTimeFormatter::ISO_LOCAL_TIME);
    }

    /**
     * Obtains an instance of {@code LocalTime} from a text string using a specific formatter.
     * <p>
     * The text is parsed using the formatter, returning a time.
     *
     * @param $text string the text to parse, not null
     * @param $formatter DateTimeFormatter the formatter to use, not null
     * @return LocalTime the parsed local time, not null
     * @throws DateTimeParseException if the text cannot be parsed
     */
    public
    static function parse($text, DateTimeFormatter $formatter)
    {
        return $formatter->parse($text, LocalTime::from);
    }

    //-----------------------------------------------------------------------
    /**
     * Creates a local time from the hour, minute, second and nanosecond fields.
     * <p>
     * This factory may return a cached value, but applications must not rely on this.
     *
     * @param $hour int the hour-of-day to represent, validated from 0 to 23
     * @param $minute int the minute-of-hour to represent, validated from 0 to 59
     * @param $second int the second-of-minute to represent, validated from 0 to 59
     * @param $nanoOfSecond int the nano-of-second to represent, validated from 0 to 999,999,999
     * @return LocalTime LocalTime the local time, not null
     */
    private static function create($hour, $minute, $second, $nanoOfSecond)
    {
        if (($minute | $second | $nanoOfSecond) == 0) {
            return self::$HOURS[$hour];
        }

        return new LocalTime($hour, $minute, $second, $nanoOfSecond);
    }

    /**
     * Constructor, previously validated.
     *
     * @param $hour int the hour-of-day to represent, validated from 0 to 23
     * @param $minute int the minute-of-hour to represent, validated from 0 to 59
     * @param $second int the second-of-minute to represent, validated from 0 to 59
     * @param $nanoOfSecond int the nano-of-second to represent, validated from 0 to 999,999,999
     */
    private function __construct($hour, $minute, $second, $nanoOfSecond)
    {
        $this->hour = $hour;
        $this->minute = $minute;
        $this->second = $second;
        $this->nano = $nanoOfSecond;
    }

//-----------------------------------------------------------------------
    /**
     * Checks if the specified field is supported.
     * <p>
     * This checks if this time can be queried for the specified field.
     * If false, then calling the {@link #range(TemporalField) range},
     * {@link #get(TemporalField) get} and {@link #with(TemporalField, long)}
     * methods will throw an exception.
     * <p>
     * If the field is a {@link ChronoField} then the query is implemented here.
     * The supported fields are:
     * <ul>
     * <li>{@code NANO_OF_SECOND}
     * <li>{@code NANO_OF_DAY}
     * <li>{@code MICRO_OF_SECOND}
     * <li>{@code MICRO_OF_DAY}
     * <li>{@code MILLI_OF_SECOND}
     * <li>{@code MILLI_OF_DAY}
     * <li>{@code SECOND_OF_MINUTE}
     * <li>{@code SECOND_OF_DAY}
     * <li>{@code MINUTE_OF_HOUR}
     * <li>{@code MINUTE_OF_DAY}
     * <li>{@code HOUR_OF_AMPM}
     * <li>{@code CLOCK_HOUR_OF_AMPM}
     * <li>{@code HOUR_OF_DAY}
     * <li>{@code CLOCK_HOUR_OF_DAY}
     * <li>{@code AMPM_OF_DAY}
     * </ul>
     * All other {@code ChronoField} instances will return false.
     * <p>
     * If the field is not a {@code ChronoField}, then the result of this method
     * is obtained by invoking {@code TemporalField.isSupportedBy(TemporalAccessor)}
     * passing {@code this} as the argument.
     * Whether the field is supported is determined by the field.
     *
     * @param $field TemporalField the field to check, null returns false
     * @return bool true if the field is supported on this time, false if not
     */
    public function isSupported(TemporalField $field)
    {
        if ($field instanceof ChronoField) {
            return $field->isTimeBased();
        }

        return $field != null && $field->isSupportedBy($this);
    }

    /**
     * Checks if the specified unit is supported.
     * <p>
     * This checks if the specified unit can be added to, or subtracted from, this time.
     * If false, then calling the {@link #plus(long, TemporalUnit)} and
     * {@link #minus(long, TemporalUnit) minus} methods will throw an exception.
     * <p>
     * If the unit is a {@link ChronoUnit} then the query is implemented here.
     * The supported units are:
     * <ul>
     * <li>{@code NANOS}
     * <li>{@code MICROS}
     * <li>{@code MILLIS}
     * <li>{@code SECONDS}
     * <li>{@code MINUTES}
     * <li>{@code HOURS}
     * <li>{@code HALF_DAYS}
     * </ul>
     * All other {@code ChronoUnit} instances will return false.
     * <p>
     * If the unit is not a {@code ChronoUnit}, then the result of this method
     * is obtained by invoking {@code TemporalUnit.isSupportedBy(Temporal)}
     * passing {@code this} as the argument.
     * Whether the unit is supported is determined by the unit.
     *
     * @param $unit TemporalUnit the unit to check, null returns false
     * @return bool true if the unit can be added/subtracted, false if not
     */
    public function isSupported(TemporalUnit $unit)
    {
        if ($unit instanceof ChronoUnit) {
            return $unit->isTimeBased();
        }

        return $unit != null && $unit->isSupportedBy($this);
    }

    //-----------------------------------------------------------------------
    /**
     * Gets the range of valid values for the specified field.
     * <p>
     * The range object expresses the minimum and maximum valid values for a field.
     * This time is used to enhance the accuracy of the returned range.
     * If it is not possible to return the range, because the field is not supported
     * or for some other reason, an exception is thrown.
     * <p>
     * If the field is a {@link ChronoField} then the query is implemented here.
     * The {@link #isSupported(TemporalField) supported fields} will return
     * appropriate range instances.
     * All other {@code ChronoField} instances will throw an {@code UnsupportedTemporalTypeException}.
     * <p>
     * If the field is not a {@code ChronoField}, then the result of this method
     * is obtained by invoking {@code TemporalField.rangeRefinedBy(TemporalAccessor)}
     * passing {@code this} as the argument.
     * Whether the range can be obtained is determined by the field.
     *
     * @param $field TemporalField the field to query the range for, not null
     * @return ValueRange the range of valid values for the field, not null
     * @throws DateTimeException if the range for the field cannot be obtained
     * @throws UnsupportedTemporalTypeException if the field is not supported
     */
    public function range(TemporalField $field)
    {
        return Temporal::range($field);
    }

    /**
     * Gets the value of the specified field from this time as an {@code int}.
     * <p>
     * This queries this time for the value of the specified field.
     * The returned value will always be within the valid range of values for the field.
     * If it is not possible to return the value, because the field is not supported
     * or for some other reason, an exception is thrown.
     * <p>
     * If the field is a {@link ChronoField} then the query is implemented here.
     * The {@link #isSupported(TemporalField) supported fields} will return valid
     * values based on this time, except {@code NANO_OF_DAY} and {@code MICRO_OF_DAY}
     * which are too large to fit in an {@code int} and throw a {@code DateTimeException}.
     * All other {@code ChronoField} instances will throw an {@code UnsupportedTemporalTypeException}.
     * <p>
     * If the field is not a {@code ChronoField}, then the result of this method
     * is obtained by invoking {@code TemporalField.getFrom(TemporalAccessor)}
     * passing {@code this} as the argument. Whether the value can be obtained,
     * and what the value represents, is determined by the field.
     *
     * @param $field TemporalField the field to get, not null
     * @return int the value for the field
     * @throws DateTimeException if a value for the field cannot be obtained or
     *         the value is outside the range of valid values for the field
     * @throws UnsupportedTemporalTypeException if the field is not supported or
     *         the range of values exceeds an {@code int}
     * @throws ArithmeticException if numeric overflow occurs
     */
    public function get(TemporalField $field)
    {
        if ($field instanceof ChronoField) {
            return $this->get0($field);
        }

        return Temporal::get($field);
    }

    /**
     * Gets the value of the specified field from this time as a {@code long}.
     * <p>
     * This queries this time for the value of the specified field.
     * If it is not possible to return the value, because the field is not supported
     * or for some other reason, an exception is thrown.
     * <p>
     * If the field is a {@link ChronoField} then the query is implemented here.
     * The {@link #isSupported(TemporalField) supported fields} will return valid
     * values based on this time.
     * All other {@code ChronoField} instances will throw an {@code UnsupportedTemporalTypeException}.
     * <p>
     * If the field is not a {@code ChronoField}, then the result of this method
     * is obtained by invoking {@code TemporalField.getFrom(TemporalAccessor)}
     * passing {@code this} as the argument. Whether the value can be obtained,
     * and what the value represents, is determined by the field.
     *
     * @param $field TemporalField the field to get, not null
     * @return int the value for the field
     * @throws DateTimeException if a value for the field cannot be obtained
     * @throws UnsupportedTemporalTypeException if the field is not supported
     * @throws ArithmeticException if numeric overflow occurs
     */
    public function getLong(TemporalField $field)
    {
        if ($field instanceof ChronoField) {
            if ($field == NANO_OF_DAY) {
                return $this->toNanoOfDay();
            }

            if ($field == MICRO_OF_DAY) {
                return $this->toNanoOfDay() / 1000;
            }
            return $this->get0($field);
        }
        return $field->getFrom($this);
    }

    private function get0(TemporalField $field)
    {
        switch ($field) {
            case NANO_OF_SECOND:
                return $this->nano;
            case NANO_OF_DAY:
                throw new UnsupportedTemporalTypeException("Invalid field 'NanoOfDay' for get() method, use getLong() instead");
            case MICRO_OF_SECOND:
                return $this->nano / 1000;
            case MICRO_OF_DAY:
                throw new UnsupportedTemporalTypeException("Invalid field 'MicroOfDay' for get() method, use getLong() instead");
            case MILLI_OF_SECOND:
                return $this->nano / 1000000;
            case MILLI_OF_DAY:
                return (int)($this->toNanoOfDay() / 1000000);
            case SECOND_OF_MINUTE:
                return $this->second;
            case SECOND_OF_DAY:
                return $this->toSecondOfDay();
            case MINUTE_OF_HOUR:
                return $this->minute;
            case MINUTE_OF_DAY:
                return $this->hour * 60 + $this->minute;
            case HOUR_OF_AMPM:
                return $this->hour % 12;
            case CLOCK_HOUR_OF_AMPM:
                $ham = $this->hour % 12;
                return ($ham % 12 == 0 ? 12 : $ham);
            case HOUR_OF_DAY:
                return $this->hour;
            case CLOCK_HOUR_OF_DAY:
                return ($this->hour == 0 ? 24 : $this->hour);
            case AMPM_OF_DAY:
                return $this->hour / 12;
        }

        throw new UnsupportedTemporalTypeException("Unsupported field: " . $field);
    }

//-----------------------------------------------------------------------
    /**
     * Gets the hour-of-day field.
     *
     * @return int the hour-of-day, from 0 to 23
     */
    public function getHour()
    {
        return $this->hour;
    }

    /**
     * Gets the minute-of-hour field.
     *
     * @return int the minute-of-hour, from 0 to 59
     */
    public
    function getMinute()
    {
        return $this->minute;
    }

    /**
     * Gets the second-of-minute field.
     *
     * @return int the second-of-minute, from 0 to 59
     */
    public function getSecond()
    {
        return $this->second;
    }

    /**
     * Gets the nano-of-second field.
     *
     * @return int the nano-of-second, from 0 to 999,999,999
     */
    public function getNano()
    {
        return $this->nano;
    }

    //-----------------------------------------------------------------------
    /**
     * Returns an adjusted copy of this time.
     * <p>
     * This returns a {@code LocalTime}, based on this one, with the time adjusted.
     * The adjustment takes place using the specified adjuster strategy object.
     * Read the documentation of the adjuster to understand what adjustment will be made.
     * <p>
     * A simple adjuster might simply set the one of the fields, such as the hour field.
     * A more complex adjuster might set the time to the last hour of the day.
     * <p>
     * The result of this method is obtained by invoking the
     * {@link TemporalAdjuster#adjustInto(Temporal)} method on the
     * specified adjuster passing {@code this} as the argument.
     * <p>
     * This instance is immutable and unaffected by this method call.
     *
     * @param $adjuster TemporalAdjuster the adjuster to use, not null
     * @return LocalTime a {@code LocalTime} based on {@code this} with the adjustment made, not null
     * @throws DateTimeException if the adjustment cannot be made
     * @throws ArithmeticException if numeric overflow occurs
     */
    public function with(TemporalAdjuster $adjuster)
    {
        // optimizations
        if ($adjuster instanceof LocalTime) {
            return $adjuster;
        }

        return $adjuster->adjustInto($this);
    }

    /**
     * Returns a copy of this time with the specified field set to a new value.
     * <p>
     * This returns a {@code LocalTime}, based on this one, with the value
     * for the specified field changed.
     * This can be used to change any supported field, such as the hour, minute or second.
     * If it is not possible to set the value, because the field is not supported or for
     * some other reason, an exception is thrown.
     * <p>
     * If the field is a {@link ChronoField} then the adjustment is implemented here.
     * The supported fields behave as follows:
     * <ul>
     * <li>{@code NANO_OF_SECOND} -
     *  Returns a {@code LocalTime} with the specified nano-of-second.
     *  The hour, minute and second will be unchanged.
     * <li>{@code NANO_OF_DAY} -
     *  Returns a {@code LocalTime} with the specified nano-of-day.
     *  This completely replaces the time and is equivalent to {@link #ofNanoOfDay(long)}.
     * <li>{@code MICRO_OF_SECOND} -
     *  Returns a {@code LocalTime} with the nano-of-second replaced by the specified
     *  micro-of-second multiplied by 1,000.
     *  The hour, minute and second will be unchanged.
     * <li>{@code MICRO_OF_DAY} -
     *  Returns a {@code LocalTime} with the specified micro-of-day.
     *  This completely replaces the time and is equivalent to using {@link #ofNanoOfDay(long)}
     *  with the micro-of-day multiplied by 1,000.
     * <li>{@code MILLI_OF_SECOND} -
     *  Returns a {@code LocalTime} with the nano-of-second replaced by the specified
     *  milli-of-second multiplied by 1,000,000.
     *  The hour, minute and second will be unchanged.
     * <li>{@code MILLI_OF_DAY} -
     *  Returns a {@code LocalTime} with the specified milli-of-day.
     *  This completely replaces the time and is equivalent to using {@link #ofNanoOfDay(long)}
     *  with the milli-of-day multiplied by 1,000,000.
     * <li>{@code SECOND_OF_MINUTE} -
     *  Returns a {@code LocalTime} with the specified second-of-minute.
     *  The hour, minute and nano-of-second will be unchanged.
     * <li>{@code SECOND_OF_DAY} -
     *  Returns a {@code LocalTime} with the specified second-of-day.
     *  The nano-of-second will be unchanged.
     * <li>{@code MINUTE_OF_HOUR} -
     *  Returns a {@code LocalTime} with the specified minute-of-hour.
     *  The hour, second-of-minute and nano-of-second will be unchanged.
     * <li>{@code MINUTE_OF_DAY} -
     *  Returns a {@code LocalTime} with the specified minute-of-day.
     *  The second-of-minute and nano-of-second will be unchanged.
     * <li>{@code HOUR_OF_AMPM} -
     *  Returns a {@code LocalTime} with the specified hour-of-am-pm.
     *  The AM/PM, minute-of-hour, second-of-minute and nano-of-second will be unchanged.
     * <li>{@code CLOCK_HOUR_OF_AMPM} -
     *  Returns a {@code LocalTime} with the specified clock-hour-of-am-pm.
     *  The AM/PM, minute-of-hour, second-of-minute and nano-of-second will be unchanged.
     * <li>{@code HOUR_OF_DAY} -
     *  Returns a {@code LocalTime} with the specified hour-of-day.
     *  The minute-of-hour, second-of-minute and nano-of-second will be unchanged.
     * <li>{@code CLOCK_HOUR_OF_DAY} -
     *  Returns a {@code LocalTime} with the specified clock-hour-of-day.
     *  The minute-of-hour, second-of-minute and nano-of-second will be unchanged.
     * <li>{@code AMPM_OF_DAY} -
     *  Returns a {@code LocalTime} with the specified AM/PM.
     *  The hour-of-am-pm, minute-of-hour, second-of-minute and nano-of-second will be unchanged.
     * </ul>
     * <p>
     * In all cases, if the new value is outside the valid range of values for the field
     * then a {@code DateTimeException} will be thrown.
     * <p>
     * All other {@code ChronoField} instances will throw an {@code UnsupportedTemporalTypeException}.
     * <p>
     * If the field is not a {@code ChronoField}, then the result of this method
     * is obtained by invoking {@code TemporalField.adjustInto(Temporal, long)}
     * passing {@code this} as the argument. In this case, the field determines
     * whether and how to adjust the instant.
     * <p>
     * This instance is immutable and unaffected by this method call.
     *
     * @param $field TemporalField the field to set in the result, not null
     * @param $newValue int the new value of the field in the result
     * @return LocalTime a {@code LocalTime} based on {@code this} with the specified field set, not null
     * @throws DateTimeException if the field cannot be set
     * @throws UnsupportedTemporalTypeException if the field is not supported
     * @throws ArithmeticException if numeric overflow occurs
     */
    public function with(TemporalField $field, $newValue)
    {
        if ($field instanceof ChronoField) {
            $f = $field;
            $f->checkValidValue($newValue);
            switch ($f) {
                case NANO_OF_SECOND:
                    return $this->withNano((int)$newValue);
                case NANO_OF_DAY:
                    return LocalTime::ofNanoOfDay($newValue);
                case MICRO_OF_SECOND:
                    return $this->withNano((int)$newValue * 1000);
                case MICRO_OF_DAY:
                    return LocalTime::ofNanoOfDay($newValue * 1000);
                case MILLI_OF_SECOND:
                    return $this->withNano((int)$newValue * 1000000);
                case MILLI_OF_DAY:
                    return LocalTime::ofNanoOfDay($newValue * 1000000);
                case SECOND_OF_MINUTE:
                    return $this->withSecond((int)$newValue);
                case SECOND_OF_DAY:
                    return $this->plusSeconds($newValue - $this->toSecondOfDay());
                case MINUTE_OF_HOUR:
                    return $this->withMinute((int)$newValue);
                case MINUTE_OF_DAY:
                    return $this->plusMinutes($newValue - ($this->hour * 60 + $this->minute));
                case HOUR_OF_AMPM:
                    return $this->plusHours($newValue - ($this->hour % 12));
                case CLOCK_HOUR_OF_AMPM:
                    return $this->plusHours(($newValue == 12 ? 0 : $newValue) - ($this->hour % 12));
                case HOUR_OF_DAY:
                    return $this->withHour((int)$newValue);
                case CLOCK_HOUR_OF_DAY:
                    return $this->withHour((int)($newValue == 24 ? 0 : $newValue));
                case AMPM_OF_DAY:
                    return $this->plusHours(($newValue - ($this->hour / 12)) * 12);
            }

            throw new UnsupportedTemporalTypeException("Unsupported field: " . $field);
        }
        return $field->adjustInto($this, $newValue);
    }

    //-----------------------------------------------------------------------
    /**
     * Returns a copy of this {@code LocalTime} with the hour-of-day altered.
     * <p>
     * This instance is immutable and unaffected by this method call.
     *
     * @param $hour int the hour-of-day to set in the result, from 0 to 23
     * @return LocalTime a {@code LocalTime} based on this time with the requested hour, not null
     * @throws DateTimeException if the hour value is invalid
     */
    public function withHour($hour)
    {
        if ($this->hour == $hour) {
            return $this;
        }

        HOUR_OF_DAY::checkValidValue($hour);
        return $this->create($hour, $this->minute, $this->second, $this->nano);
    }

    /**
     * Returns a copy of this {@code LocalTime} with the minute-of-hour altered.
     * <p>
     * This instance is immutable and unaffected by this method call.
     *
     * @param $minute int the minute-of-hour to set in the result, from 0 to 59
     * @return LocalTime a {@code LocalTime} based on this time with the requested minute, not null
     * @throws DateTimeException if the minute value is invalid
     */
    public function withMinute($minute)
    {
        if ($this->minute == $minute) {
            return $this;
        }

        MINUTE_OF_HOUR->checkValidValue($minute);
    return self::create($this->hour, $minute, $this->second, $this->nano);
}

    /**
     * Returns a copy of this {@code LocalTime} with the second-of-minute altered.
     * <p>
     * This instance is immutable and unaffected by this method call.
     *
     * @param $second int the second-of-minute to set in the result, from 0 to 59
     * @return LocalTime a {@code LocalTime} based on this time with the requested second, not null
     * @throws DateTimeException if the second value is invalid
     */
    public function withSecond($second)
    {
        if ($this->second == $second) {
            return $this;
        }

        SECOND_OF_MINUTE::checkValidValue($second);
        return self::create($this->hour, $this->minute, $second, $this->nano);
    }

    /**
     * Returns a copy of this {@code LocalTime} with the nano-of-second altered.
     * <p>
     * This instance is immutable and unaffected by this method call.
     *
     * @param $nanoOfSecond int the nano-of-second to set in the result, from 0 to 999,999,999
     * @return LocalTime a {@code LocalTime} based on this time with the requested nanosecond, not null
     * @throws DateTimeException if the nanos value is invalid
     */
    public function withNano($nanoOfSecond)
    {
        if ($this->nano == $nanoOfSecond) {
            return $this;
        }

        NANO_OF_SECOND::checkValidValue($nanoOfSecond);
        return self::create($this->hour, $this->minute, $this->second, $nanoOfSecond);
    }

    //-----------------------------------------------------------------------
    /**
     * Returns a copy of this {@code LocalTime} with the time truncated.
     * <p>
     * Truncation returns a copy of the original time with fields
     * smaller than the specified unit set to zero.
     * For example, truncating with the {@link ChronoUnit#MINUTES minutes} unit
     * will set the second-of-minute and nano-of-second field to zero.
     * <p>
     * The unit must have a {@linkplain TemporalUnit#getDuration() duration}
     * that divides into the length of a standard day without remainder.
     * This includes all supplied time units on {@link ChronoUnit} and
     * {@link ChronoUnit#DAYS DAYS}. Other units throw an exception.
     * <p>
     * This instance is immutable and unaffected by this method call.
     *
     * @param $unit TemporalUnit the unit to truncate to, not null
     * @return LocalTime a {@code LocalTime} based on this time with the time truncated, not null
     * @throws DateTimeException if unable to truncate
     * @throws UnsupportedTemporalTypeException if the unit is not supported
     */
    public function truncatedTo(TemporalUnit $unit)
    {
        if ($unit == ChronoUnit::NANOS) {
            return $this;
        }

        $unitDur = $unit->getDuration();
        if ($unitDur->getSeconds() > SECONDS_PER_DAY) {
            throw new UnsupportedTemporalTypeException("Unit is too large to be used for truncation");
        }
        $dur = $unitDur->toNanos();
        if ((NANOS_PER_DAY % $dur) != 0) {
            throw new UnsupportedTemporalTypeException("Unit must divide into a standard day without remainder");
        }
        $nod = $this->toNanoOfDay();
        return $this->ofNanoOfDay(($nod / $dur) * $dur);
    }

    //-----------------------------------------------------------------------
    /**
     * Returns a copy of this time with the specified amount added.
     * <p>
     * This returns a {@code LocalTime}, based on this one, with the specified amount added.
     * The amount is typically {@link Duration} but may be any other type implementing
     * the {@link TemporalAmount} interface.
     * <p>
     * The calculation is delegated to the amount object by calling
     * {@link TemporalAmount#addTo(Temporal)}. The amount implementation is free
     * to implement the addition in any way it wishes, however it typically
     * calls back to {@link #plus(long, TemporalUnit)}. Consult the documentation
     * of the amount implementation to determine if it can be successfully added.
     * <p>
     * This instance is immutable and unaffected by this method call.
     *
     * @param $amountToAdd TemporalAmount the amount to add, not null
     * @return LocalTime a {@code LocalTime} based on this time with the addition made, not null
     * @throws DateTimeException if the addition cannot be made
     * @throws ArithmeticException if numeric overflow occurs
     */
    public function plus(TemporalAmount $amountToAdd)
    {
        return $amountToAdd->addTo($this);
    }

    /**
     * Returns a copy of this time with the specified amount added.
     * <p>
     * This returns a {@code LocalTime}, based on this one, with the amount
     * in terms of the unit added. If it is not possible to add the amount, because the
     * unit is not supported or for some other reason, an exception is thrown.
     * <p>
     * If the field is a {@link ChronoUnit} then the addition is implemented here.
     * The supported fields behave as follows:
     * <ul>
     * <li>{@code NANOS} -
     *  Returns a {@code LocalTime} with the specified number of nanoseconds added.
     *  This is equivalent to {@link #plusNanos(long)}.
     * <li>{@code MICROS} -
     *  Returns a {@code LocalTime} with the specified number of microseconds added.
     *  This is equivalent to {@link #plusNanos(long)} with the amount
     *  multiplied by 1,000.
     * <li>{@code MILLIS} -
     *  Returns a {@code LocalTime} with the specified number of milliseconds added.
     *  This is equivalent to {@link #plusNanos(long)} with the amount
     *  multiplied by 1,000,000.
     * <li>{@code SECONDS} -
     *  Returns a {@code LocalTime} with the specified number of seconds added.
     *  This is equivalent to {@link #plusSeconds(long)}.
     * <li>{@code MINUTES} -
     *  Returns a {@code LocalTime} with the specified number of minutes added.
     *  This is equivalent to {@link #plusMinutes(long)}.
     * <li>{@code HOURS} -
     *  Returns a {@code LocalTime} with the specified number of hours added.
     *  This is equivalent to {@link #plusHours(long)}.
     * <li>{@code HALF_DAYS} -
     *  Returns a {@code LocalTime} with the specified number of half-days added.
     *  This is equivalent to {@link #plusHours(long)} with the amount
     *  multiplied by 12.
     * </ul>
     * <p>
     * All other {@code ChronoUnit} instances will throw an {@code UnsupportedTemporalTypeException}.
     * <p>
     * If the field is not a {@code ChronoUnit}, then the result of this method
     * is obtained by invoking {@code TemporalUnit.addTo(Temporal, long)}
     * passing {@code this} as the argument. In this case, the unit determines
     * whether and how to perform the addition.
     * <p>
     * This instance is immutable and unaffected by this method call.
     *
     * @param $amountToAdd int the amount of the unit to add to the result, may be negative
     * @param $unit TemporalUnit the unit of the amount to add, not null
     * @return LocalTime a {@code LocalTime} based on this time with the specified amount added, not null
     * @throws DateTimeException if the addition cannot be made
     * @throws UnsupportedTemporalTypeException if the unit is not supported
     * @throws ArithmeticException if numeric overflow occurs
     */
    public function plus($amountToAdd, TemporalUnit $unit)
    {
        if ($unit instanceof ChronoUnit) {
            switch ($unit) {
                case NANOS:
                    return $this->plusNanos($amountToAdd);
                case MICROS:
                    return $this->plusNanos(($amountToAdd % MICROS_PER_DAY) * 1000);
                case MILLIS:
                    return $this->plusNanos(($amountToAdd % MILLIS_PER_DAY) * 1000000);
                case SECONDS:
                    return $this->plusSeconds($amountToAdd);
                case MINUTES:
                    return $this->plusMinutes($amountToAdd);
                case HOURS:
                    return $this->plusHours($amountToAdd);
                case HALF_DAYS:
                    return $this->plusHours(($amountToAdd % 2) * 12);
            }

            throw new UnsupportedTemporalTypeException("Unsupported unit: " . $unit);
        }
        return $unit->addTo($this, $amountToAdd);
    }

    //-----------------------------------------------------------------------
    /**
     * Returns a copy of this {@code LocalTime} with the specified number of hours added.
     * <p>
     * This adds the specified number of hours to this time, returning a new time.
     * The calculation wraps around midnight.
     * <p>
     * This instance is immutable and unaffected by this method call.
     *
     * @param $hoursToAdd int the hours to add, may be negative
     * @return LocalTime a {@code LocalTime} based on this time with the hours added, not null
     */
    public function plusHours($hoursToAdd)
    {
        if ($hoursToAdd == 0) {
            return $this;
        }

        $newHour = ((int)($hoursToAdd % HOURS_PER_DAY) + $this->hour + HOURS_PER_DAY) % HOURS_PER_DAY;
        return $this->create($newHour, $this->minute, $this->second, $this->nano);
    }

    /**
     * Returns a copy of this {@code LocalTime} with the specified number of minutes added.
     * <p>
     * This adds the specified number of minutes to this time, returning a new time.
     * The calculation wraps around midnight.
     * <p>
     * This instance is immutable and unaffected by this method call.
     *
     * @param $minutesToAdd int the minutes to add, may be negative
     * @return LocalTime a {@code LocalTime} based on this time with the minutes added, not null
     */
    public function plusMinutes($minutesToAdd)
    {
        if ($minutesToAdd == 0) {
            return $this;
        }

        $mofd = $this->hour * MINUTES_PER_HOUR + $this->minute;
        $newMofd = ((int)($minutesToAdd % MINUTES_PER_DAY) + $mofd + MINUTES_PER_DAY) % MINUTES_PER_DAY;
        if ($mofd == $newMofd) {
            return $this;
        }
        $newHour = $newMofd / MINUTES_PER_HOUR;
        $newMinute = $newMofd % MINUTES_PER_HOUR;
        return self::create($newHour, $newMinute, $this->second, $this->nano);
    }

    /**
     * Returns a copy of this {@code LocalTime} with the specified number of seconds added.
     * <p>
     * This adds the specified number of seconds to this time, returning a new time.
     * The calculation wraps around midnight.
     * <p>
     * This instance is immutable and unaffected by this method call.
     *
     * @param $secondstoAdd int the seconds to add, may be negative
     * @return LocalTime a {@code LocalTime} based on this time with the seconds added, not null
     */
    public function plusSeconds($secondstoAdd)
    {
        if ($secondstoAdd == 0) {
            return $this;
        }

        $sofd = $this->hour * SECONDS_PER_HOUR +
            $this->minute * SECONDS_PER_MINUTE + $this->second;
        $newSofd = ((int)($secondstoAdd % SECONDS_PER_DAY) + $sofd + SECONDS_PER_DAY) % SECONDS_PER_DAY;
        if ($sofd == $newSofd) {
            return $this;
        }
        $newHour = $newSofd / SECONDS_PER_HOUR;
        $newMinute = ($newSofd / SECONDS_PER_MINUTE) % MINUTES_PER_HOUR;
        $newSecond = $newSofd % SECONDS_PER_MINUTE;
        return self::create($newHour, $newMinute, $newSecond, $this->nano);
    }

    /**
     * Returns a copy of this {@code LocalTime} with the specified number of nanoseconds added.
     * <p>
     * This adds the specified number of nanoseconds to this time, returning a new time.
     * The calculation wraps around midnight.
     * <p>
     * This instance is immutable and unaffected by this method call.
     *
     * @param $nanosToAdd int the nanos to add, may be negative
     * @return LocalTime a {@code LocalTime} based on this time with the nanoseconds added, not null
     */
    public function plusNanos($nanosToAdd)
    {
        if ($nanosToAdd == 0) {
            return $this;
        }

        $nofd = $this->toNanoOfDay();
        $newNofd = (($nanosToAdd % NANOS_PER_DAY) + $nofd + NANOS_PER_DAY) % NANOS_PER_DAY;
        if ($nofd == $newNofd) {
            return $this;
        }
        $newHour = (int)($newNofd / NANOS_PER_HOUR);
        $newMinute = (int)(($newNofd / NANOS_PER_MINUTE) % MINUTES_PER_HOUR);
        $newSecond = (int)(($newNofd / NANOS_PER_SECOND) % SECONDS_PER_MINUTE);
        $newNano = (int)($newNofd % NANOS_PER_SECOND);
        return self::create($newHour, $newMinute, $newSecond, $newNano);
    }

    //-----------------------------------------------------------------------
    /**
     * Returns a copy of this time with the specified amount subtracted.
     * <p>
     * This returns a {@code LocalTime}, based on this one, with the specified amount subtracted.
     * The amount is typically {@link Duration} but may be any other type implementing
     * the {@link TemporalAmount} interface.
     * <p>
     * The calculation is delegated to the amount object by calling
     * {@link TemporalAmount#subtractFrom(Temporal)}. The amount implementation is free
     * to implement the subtraction in any way it wishes, however it typically
     * calls back to {@link #minus(long, TemporalUnit)}. Consult the documentation
     * of the amount implementation to determine if it can be successfully subtracted.
     * <p>
     * This instance is immutable and unaffected by this method call.
     *
     * @param $amountToSubtract TemporalAmount the amount to subtract, not null
     * @return LocalTime a {@code LocalTime} based on this time with the subtraction made, not null
     * @throws DateTimeException if the subtraction cannot be made
     * @throws ArithmeticException if numeric overflow occurs
     */
    public function minus(TemporalAmount $amountToSubtract)
    {
        return $amountToSubtract->subtractFrom($this);
    }

    /**
     * Returns a copy of this time with the specified amount subtracted.
     * <p>
     * This returns a {@code LocalTime}, based on this one, with the amount
     * in terms of the unit subtracted. If it is not possible to subtract the amount,
     * because the unit is not supported or for some other reason, an exception is thrown.
     * <p>
     * This method is equivalent to {@link #plus(long, TemporalUnit)} with the amount negated.
     * See that method for a full description of how addition, and thus subtraction, works.
     * <p>
     * This instance is immutable and unaffected by this method call.
     *
     * @param $amountToSubtract int the amount of the unit to subtract from the result, may be negative
     * @param $unit TemporalUnit the unit of the amount to subtract, not null
     * @return LocalTime a {@code LocalTime} based on this time with the specified amount subtracted, not null
     * @throws DateTimeException if the subtraction cannot be made
     * @throws UnsupportedTemporalTypeException if the unit is not supported
     * @throws ArithmeticException if numeric overflow occurs
     */
    public function minus($amountToSubtract, TemporalUnit $unit)
    {
        return ($amountToSubtract == Long::MIN_VALUE ? $this->plus(Long::MAX_VALUE, $unit)->plus(1, $unit) : $this->plus(-$amountToSubtract, $unit));
    }

//-----------------------------------------------------------------------
    /**
     * Returns a copy of this {@code LocalTime} with the specified number of hours subtracted.
     * <p>
     * This subtracts the specified number of hours from this time, returning a new time.
     * The calculation wraps around midnight.
     * <p>
     * This instance is immutable and unaffected by this method call.
     *
     * @param $hoursToSubtract int the hours to subtract, may be negative
     * @return LocalTime a {@code LocalTime} based on this time with the hours subtracted, not null
     */
    public
    function minusHours($hoursToSubtract)
    {
        return $this->plusHours(-($hoursToSubtract % HOURS_PER_DAY));
    }

    /**
     * Returns a copy of this {@code LocalTime} with the specified number of minutes subtracted.
     * <p>
     * This subtracts the specified number of minutes from this time, returning a new time.
     * The calculation wraps around midnight.
     * <p>
     * This instance is immutable and unaffected by this method call.
     *
     * @param $minutesToSubtract int the minutes to subtract, may be negative
     * @return LocalTime a {@code LocalTime} based on this time with the minutes subtracted, not null
     */
    public
    function minusMinutes($minutesToSubtract)
    {
        return $this->plusMinutes(-($minutesToSubtract % MINUTES_PER_DAY));
    }

    /**
     * Returns a copy of this {@code LocalTime} with the specified number of seconds subtracted.
     * <p>
     * This subtracts the specified number of seconds from this time, returning a new time.
     * The calculation wraps around midnight.
     * <p>
     * This instance is immutable and unaffected by this method call.
     *
     * @param $secondsToSubtract int the seconds to subtract, may be negative
     * @return LocalTime a {@code LocalTime} based on this time with the seconds subtracted, not null
     */
    public function minusSeconds($secondsToSubtract)
    {
        return $this->plusSeconds(-($secondsToSubtract % SECONDS_PER_DAY));
    }

    /**
     * Returns a copy of this {@code LocalTime} with the specified number of nanoseconds subtracted.
     * <p>
     * This subtracts the specified number of nanoseconds from this time, returning a new time.
     * The calculation wraps around midnight.
     * <p>
     * This instance is immutable and unaffected by this method call.
     *
     * @param $nanosToSubtract int the nanos to subtract, may be negative
     * @return LocalTime a {@code LocalTime} based on this time with the nanoseconds subtracted, not null
     */
    public function minusNanos($nanosToSubtract)
    {
        return $this->plusNanos(-($nanosToSubtract % NANOS_PER_DAY));
    }

//-----------------------------------------------------------------------
    /**
     * Queries this time using the specified query.
     * <p>
     * This queries this time using the specified query strategy object.
     * The {@code TemporalQuery} object defines the logic to be used to
     * obtain the result. Read the documentation of the query to understand
     * what the result of this method will be.
     * <p>
     * The result of this method is obtained by invoking the
     * {@link TemporalQuery#queryFrom(TemporalAccessor)} method on the
     * specified query passing {@code this} as the argument.
     *
     * @param r the type of the result
     * @param $query TemporalQuery the query to invoke, not null
     * @return mixed the query result, null may be returned (defined by the query)
     * @throws DateTimeException if unable to query (defined by the query)
     * @throws ArithmeticException if numeric overflow occurs (defined by the query)
     */
    public function query(TemporalQuery $query)
    {
        if ($query == TemporalQueries::chronology() || $query == TemporalQueries::zoneId() ||
            $query == TemporalQueries::zone() || $query == TemporalQueries::offset()
        ) {
            return null;
        } else
            if ($query == TemporalQueries::localTime()) {
                return $this;
            } else if ($query == TemporalQueries::localDate()) {
                return null;
            } else if ($query == TemporalQueries::precision()) {
                return NANOS;
            }
        // inline TemporalAccessor.super.query(query) as an optimization
        // non-JDK classes are not permitted to make this optimization
        return $query->queryFrom($this);
    }

    /**
     * Adjusts the specified temporal object to have the same time as this object.
     * <p>
     * This returns a temporal object of the same observable type as the input
     * with the time changed to be the same as this.
     * <p>
     * The adjustment is equivalent to using {@link Temporal#with(TemporalField, long)}
     * passing {@link ChronoField#NANO_OF_DAY} as the field.
     * <p>
     * In most cases, it is clearer to reverse the calling pattern by using
     * {@link Temporal#with(TemporalAdjuster)}:
     * <pre>
     *   // these two lines are equivalent, but the second approach is recommended
     *   temporal = thisLocalTime.adjustInto(temporal);
     *   temporal = temporal.with(thisLocalTime);
     * </pre>
     * <p>
     * This instance is immutable and unaffected by this method call.
     *
     * @param $temporal Temporal the target object to be adjusted, not null
     * @return Temporal the adjusted object, not null
     * @throws DateTimeException if unable to make the adjustment
     * @throws ArithmeticException if numeric overflow occurs
     */
    public function adjustInto(Temporal $temporal)
    {
        return $temporal->with(NANO_OF_DAY, $this->toNanoOfDay());
    }

    /**
     * Calculates the amount of time until another time in terms of the specified unit.
     * <p>
     * This calculates the amount of time between two {@code LocalTime}
     * objects in terms of a single {@code TemporalUnit}.
     * The start and end points are {@code this} and the specified time.
     * The result will be negative if the end is before the start.
     * The {@code Temporal} passed to this method is converted to a
     * {@code LocalTime} using {@link #from(TemporalAccessor)}.
     * For example, the amount in hours between two times can be calculated
     * using {@code startTime.until(endTime, HOURS)}.
     * <p>
     * The calculation returns a whole number, representing the number of
     * complete units between the two times.
     * For example, the amount in hours between 11:30 and 13:29 will only
     * be one hour as it is one minute short of two hours.
     * <p>
     * There are two equivalent ways of using this method.
     * The first is to invoke this method.
     * The second is to use {@link TemporalUnit#between(Temporal, Temporal)}:
     * <pre>
     *   // these two lines are equivalent
     *   amount = start.until(end, MINUTES);
     *   amount = MINUTES.between(start, end);
     * </pre>
     * The choice should be made based on which makes the code more readable.
     * <p>
     * The calculation is implemented in this method for {@link ChronoUnit}.
     * The units {@code NANOS}, {@code MICROS}, {@code MILLIS}, {@code SECONDS},
     * {@code MINUTES}, {@code HOURS} and {@code HALF_DAYS} are supported.
     * Other {@code ChronoUnit} values will throw an exception.
     * <p>
     * If the unit is not a {@code ChronoUnit}, then the result of this method
     * is obtained by invoking {@code TemporalUnit.between(Temporal, Temporal)}
     * passing {@code this} as the first argument and the converted input temporal
     * as the second argument.
     * <p>
     * This instance is immutable and unaffected by this method call.
     *
     * @param $endExclusive Temporal the end time, exclusive, which is converted to a {@code LocalTime}, not null
     * @param $unit $unit the unit to measure the amount in, not null
     * @return int the amount of time between this time and the end time
     * @throws DateTimeException if the amount cannot be calculated, or the end
     *  temporal cannot be converted to a {@code LocalTime}
     * @throws UnsupportedTemporalTypeException if the unit is not supported
     * @throws ArithmeticException if numeric overflow occurs
     */
    public function until(Temporal $endExclusive, TemporalUnit $unit)
    {
        $end = LocalTime::from($endExclusive);
        if ($unit instanceof ChronoUnit) {
            $nanosUntil = $end->toNanoOfDay() - $this->toNanoOfDay();  // no overflow
            switch ($unit) {
                case NANOS:
                    return $nanosUntil;
                case MICROS:
                    return $nanosUntil / 1000;
                case MILLIS:
                    return $nanosUntil / 1000000;
                case SECONDS:
                    return $nanosUntil / NANOS_PER_SECOND;
                case MINUTES:
                    return $nanosUntil / NANOS_PER_MINUTE;
                case HOURS:
                    return $nanosUntil / NANOS_PER_HOUR;
                case HALF_DAYS:
                    return $nanosUntil / (12 * NANOS_PER_HOUR);
            }

            throw new UnsupportedTemporalTypeException("Unsupported unit: " . $unit);
        }
        return $unit->between($this, $end);
    }

    /**
     * Formats this time using the specified formatter.
     * <p>
     * This time will be passed to the formatter to produce a string.
     *
     * @param $formatter DateTimeFormatter the formatter to use, not null
     * @return string the formatted time string, not null
     * @throws DateTimeException if an error occurs during printing
     */
    public function format(DateTimeFormatter $formatter)
    {
        return $formatter->format($this);
    }

//-----------------------------------------------------------------------
    /**
     * Combines this time with a date to create a {@code LocalDateTime}.
     * <p>
     * This returns a {@code LocalDateTime} formed from this time at the specified date.
     * All possible combinations of date and time are valid.
     *
     * @param $date LocalDate the date to combine with, not null
     * @return LocalDateTime LocalTime the local date-time formed from this time and the specified date, not null
     */
    public
    function atDate(LocalDate $date)
    {
        return LocalDateTime::of($date, $this);
    }

    /**
     * Combines this time with an offset to create an {@code OffsetTime}.
     * <p>
     * This returns an {@code OffsetTime} formed from this time at the specified offset.
     * All possible combinations of time and offset are valid.
     *
     * @param $offset ZoneOffset the offset to combine with, not null
     * @return OffsetTime the offset time formed from this time and the specified offset, not null
     */
    public function atOffset(ZoneOffset $offset)
    {
        return OffsetTime::of($this, $offset);
    }

//-----------------------------------------------------------------------
    /**
     * Extracts the time as seconds of day,
     * from {@code 0} to {@code 24 * 60 * 60 - 1}.
     *
     * @return int the second-of-day equivalent to this time
     */
    public function toSecondOfDay()
    {
        $total = $this->hour * SECONDS_PER_HOUR;
        $total += $this->minute * SECONDS_PER_MINUTE;
        $total += $this->second;
        return $total;
    }

    /**
     * Extracts the time as nanos of day,
     * from {@code 0} to {@code 24 * 60 * 60 * 1,000,000,000 - 1}.
     *
     * @return int the nano of day equivalent to this time
     */
    public function toNanoOfDay()
    {
        $total = $this->hour * NANOS_PER_HOUR;
        $total += $this->minute * NANOS_PER_MINUTE;
        $total += $this->second * NANOS_PER_SECOND;
        $total += $this->nano;
        return $total;
    }

//-----------------------------------------------------------------------
    /**
     * Compares this time to another time.
     * <p>
     * The comparison is based on the time-line position of the local times within a day.
     * It is "consistent with equals", as defined by {@link Comparable}.
     *
     * @param $other LocalTime the other time to compare to, not null
     * @return int the comparator value, negative if less, positive if greater
     * @throws NullPointerException if {@code other} is null
     */
    public function compareTo(LocalTime $other)
    {
        $cmp = Integer::compare($this->hour, $other->hour);
        if ($cmp == 0) {
            $cmp = Integer::compare($this->minute, $other->minute);
            if ($cmp == 0) {
                $cmp = Integer::compare($this->second, $other->second);
                if ($cmp == 0) {
                    $cmp = Integer::compare($this->nano, $other->nano);
                }
            }
        }
        return $cmp;
    }

    /**
     * Checks if this time is after the specified time.
     * <p>
     * The comparison is based on the time-line position of the time within a day.
     *
     * @param $other LocalTime the other time to compare to, not null
     * @return bool true if this is after the specified time
     * @throws NullPointerException if {@code other} is null
     */
    public
    function isAfter(LocalTime $other)
    {
        return $this->compareTo($other) > 0;
    }

    /**
     * Checks if this time is before the specified time.
     * <p>
     * The comparison is based on the time-line position of the time within a day.
     *
     * @param $other LocalTime the other time to compare to, not null
     * @return true if this point is before the specified time
     * @throws NullPointerException if {@code other} is null
     */
    public
    function isBefore(LocalTime $other)
    {
        return $this->compareTo($other) < 0;
    }

//-----------------------------------------------------------------------
    /**
     * Checks if this time is equal to another time.
     * <p>
     * The comparison is based on the time-line position of the time within a day.
     * <p>
     * Only objects of type {@code LocalTime} are compared, other types return false.
     * To compare the date of two {@code TemporalAccessor} instances, use
     * {@link ChronoField#NANO_OF_DAY} as a comparator.
     *
     * @param $obj mixed the object to check, null returns false
     * @return true if this is equal to the other time
     */
    public function equals($obj)
    {
        if ($this == $obj) {
            return true;
        }

        if ($obj instanceof LocalTime) {
            $other = $obj;
            return $this->hour == $other->hour && $this->minute == $other->minute &&
            $this->second == $other->second && $this->nano == $other->nano;
        }
        return false;
    }

//-----------------------------------------------------------------------
    /**
     * Outputs this time as a {@code String}, such as {@code 10:15}.
     * <p>
     * The output will be one of the following ISO-8601 formats:
     * <ul>
     * <li>{@code HH:mm}</li>
     * <li>{@code HH:mm:ss}</li>
     * <li>{@code HH:mm:ss.SSS}</li>
     * <li>{@code HH:mm:ss.SSSSSS}</li>
     * <li>{@code HH:mm:ss.SSSSSSSSS}</li>
     * </ul>
     * The format used will be the shortest that outputs the full value of
     * the time where the omitted parts are implied to be zero.
     *
     * @return string a string representation of this time, not null
     */
    public function __toString()
    {
        $buf = "";
        $hourValue = $this->hour;
        $minuteValue = $this->minute;
        $secondValue = $this->second;
        $nanoValue = $this->nano;
        $buf .= ($hourValue < 10 ? "0" : "") . $hourValue
            . ($minuteValue < 10 ? ":0" : ":") . $minuteValue;
        if ($secondValue > 0 || $nanoValue > 0) {
            $buf .= ($secondValue < 10 ? ":0" : ":") . $secondValue;
            if ($nanoValue > 0) {
                $buf .= '.';
                if ($nanoValue % 1000000 == 0) {
                    $buf .= substr(($nanoValue / 1000000) + 1000, 1);
                } else if ($nanoValue % 1000 == 0) {
                    $buf .= substr(($nanoValue / 1000) + 1000000, 1);
                } else {
                    $buf .= substr(($nanoValue) + 1000000000, 1);
                }
            }
        }
        return $buf;
    }
}
