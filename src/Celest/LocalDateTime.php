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
namespace Celest;

use Celest\Chrono\AbstractChronoLocalDateTime;
use Celest\Chrono\ChronoLocalDateTime;
use Celest\Format\DateTimeFormatter;
use Celest\Helper\Long;
use Celest\Helper\Math;
use Celest\Temporal\ChronoField;
use Celest\Temporal\ChronoUnit;
use Celest\Temporal\Temporal;
use Celest\Temporal\TemporalAccessor;
use Celest\Temporal\TemporalAdjuster;
use Celest\Temporal\TemporalAmount;
use Celest\Temporal\TemporalField;
use Celest\Temporal\TemporalQueries;
use Celest\Temporal\TemporalQuery;
use Celest\Temporal\TemporalUnit;
use Celest\Temporal\UnsupportedTemporalTypeException;
use Celest\Temporal\ValueRange;

/**
 * A date-time without a time-zone in the ISO-8601 calendar system,
 * such as {@code 2007-12-03T10:15:30}.
 * <p>
 * {@code LocalDateTime} is an immutable date-time object that represents a date-time,
 * often viewed as year-month-day-hour-minute-second. Other date and time fields,
 * such as day-of-year, day-of-week and week-of-year, can also be accessed.
 * Time is represented to nanosecond precision.
 * For example, the value "2nd October 2007 at 13:45.30.123456789" can be
 * stored in a {@code LocalDateTime}.
 * <p>
 * This class does not store or represent a time-zone.
 * Instead, it is a description of the date, as used for birthdays, combined with
 * the local time as seen on a wall clock.
 * It cannot represent an instant on the time-line without additional information
 * such as an offset or time-zone.
 * <p>
 * The ISO-8601 calendar system is the modern civil calendar system used today
 * in most of the world. It is equivalent to the proleptic Gregorian calendar
 * system, in which today's rules for leap years are applied for all time.
 * For most applications written today, the ISO-8601 rules are entirely suitable.
 * However, any application that makes use of historical dates, and requires them
 * to be accurate will find the ISO-8601 approach unsuitable.
 *
 * <p>
 * This is a <a href="{@docRoot}/java/lang/doc-files/ValueBased.html">value-based</a>
 * class; use of identity-sensitive operations (including reference equality
 * ({@code ==}), identity hash code, or synchronization) on instances of
 * {@code LocalDateTime} may have unpredictable results and should be avoided.
 * The {@code equals} method should be used for comparisons.
 *
 * @implSpec
 * This class is immutable and thread-safe.
 *
 * @since 1.8
 */
final class LocalDateTime extends AbstractChronoLocalDateTime implements Temporal, TemporalAdjuster, ChronoLocalDateTime, \Serializable, \JsonSerializable
{

    public static function init()
    {
        self::$MIN = LocalDateTime::ofDateAndTime(LocalDate::MIN(), LocalTime::MIN());
        self::$MAX = LocalDateTime::ofDateAndTime(LocalDate::MAX(), LocalTime::MAX());
    }

    /**
     * The minimum supported {@code LocalDateTime}, '-999999999-01-01T00:00:00'.
     * This is the local date-time of midnight at the start of the minimum date.
     * This combines {@link LocalDate#MIN} and {@link LocalTime#MIN}.
     * This could be used by an application as a "far past" date-time.
     * @return LocalDateTime
     */
    public static function MIN()
    {
        return self::$MIN;
    }

    /** @var @var LocalDateTime */
    private static $MIN;

    /**
     * The maximum supported {@code LocalDateTime}, '+999999999-12-31T23:59:59.999999999'.
     * This is the local date-time just before midnight at the end of the maximum date.
     * This combines {@link LocalDate#MAX} and {@link LocalTime#MAX}.
     * This could be used by an application as a "far future" date-time.
     * @return LocalDateTime
     */
    public static function MAX()
    {
        return self::$MAX;
    }

    /** @var @var LocalDateTime */
    private static $MAX;

    /**
     * The date part.
     * @var LocalDate
     */
    private $date;
    /**
     * The time part.
     * @var LocalTime
     */
    private $time;

    //-----------------------------------------------------------------------
    /**
     * Obtains the current date-time from the system clock in the default time-zone.
     * <p>
     * This will query the {@link Clock#systemDefaultZone() system clock} in the default
     * time-zone to obtain the current date-time.
     * <p>
     * Using this method will prevent the ability to use an alternate clock for testing
     * because the clock is hard-coded.
     *
     * @return LocalDateTime the current date-time using the system clock and default time-zone, not null
     */
    public static function now()
    {
        return self::nowOf(Clock::systemDefaultZone());
    }

    /**
     * Obtains the current date-time from the system clock in the specified time-zone.
     * <p>
     * This will query the {@link Clock#system(ZoneId) system clock} to obtain the current date-time.
     * Specifying the time-zone avoids dependence on the default time-zone.
     * <p>
     * Using this method will prevent the ability to use an alternate clock for testing
     * because the clock is hard-coded.
     *
     * @param ZoneId $zone the zone ID to use, not null
     * @return LocalDateTime the current date-time using the system clock, not null
     */
    public static function nowIn(ZoneId $zone)
    {
        return self::nowOf(Clock::system($zone));
    }

    /**
     * Obtains the current date-time from the specified clock.
     * <p>
     * This will query the specified clock to obtain the current date-time.
     * Using this method allows the use of an alternate clock for testing.
     * The alternate clock may be introduced using {@link Clock dependency injection}.
     *
     * @param Clock $clock the clock to use, not null
     * @return LocalDateTime the current date-time, not null
     */
    public static function nowOf(Clock $clock)
    {
        $now = $clock->instant();  // called once
        $offset = $clock->getZone()->getRules()->getOffset($now);
        return self::ofEpochSecond($now->getEpochSecond(), $now->getNano(), $offset);
    }

//-----------------------------------------------------------------------
    /**
     * Obtains an instance of {@code LocalDateTime} from year, month,
     * day, hour, minute, second and nanosecond.
     * <p>
     * This returns a {@code LocalDateTime} with the specified year, month,
     * day-of-month, hour, minute, second and nanosecond.
     * The day must be valid for the year and month, otherwise an exception will be thrown.
     *
     * @param int $year the year to represent, from MIN_YEAR to MAX_YEAR
     * @param Month $month the month-of-year to represent, not null
     * @param int $dayOfMonth the day-of-month to represent, from 1 to 31
     * @param int $hour the hour-of-day to represent, from 0 to 23
     * @param int $minute the minute-of-hour to represent, from 0 to 59
     * @param int $second the second-of-minute to represent, from 0 to 59
     * @param int $nanoOfSecond the nano-of-second to represent, from 0 to 999,999,999
     * @return LocalDateTime the local date-time, not null
     * @throws DateTimeException if the value of any field is out of range,
     *  or if the day-of-month is invalid for the month-year
     */
    public static function ofMonth($year, Month $month, $dayOfMonth, $hour, $minute, $second = 0, $nanoOfSecond = 0)
    {
        $date = LocalDate::ofMonth($year, $month, $dayOfMonth);
        $time = LocalTime::of($hour, $minute, $second, $nanoOfSecond);
        return new LocalDateTime($date, $time);
    }

    /**
     * Obtains an instance of {@code LocalDateTime} from year, month,
     * day, hour, minute, second and nanosecond.
     * <p>
     * This returns a {@code LocalDateTime} with the specified year, month,
     * day-of-month, hour, minute, second and nanosecond.
     * The day must be valid for the year and month, otherwise an exception will be thrown.
     *
     * @param int $year the year to represent, from MIN_YEAR to MAX_YEAR
     * @param int $month the month-of-year to represent, from 1 (January) to 12 (December)
     * @param int $dayOfMonth the day-of-month to represent, from 1 to 31
     * @param int $hour the hour-of-day to represent, from 0 to 23
     * @param int $minute the minute-of-hour to represent, from 0 to 59
     * @param int $second the second-of-minute to represent, from 0 to 59
     * @param int $nanoOfSecond the nano-of-second to represent, from 0 to 999,999,999
     * @return LocalDateTime the local date-time, not null
     * @throws DateTimeException if the value of any field is out of range,
     *  or if the day-of-month is invalid for the month-year
     */
    public static function of($year, $month, $dayOfMonth, $hour, $minute, $second = 0, $nanoOfSecond = 0)
    {
        $date = LocalDate::of($year, $month, $dayOfMonth);
        $time = LocalTime::of($hour, $minute, $second, $nanoOfSecond);
        return new LocalDateTime($date, $time);
    }

    /**
     * Obtains an instance of {@code LocalDateTime} from a date and time.
     *
     * @param LocalDate $date the local date, not null
     * @param LocalTime $time the local time, not null
     * @return LocalDateTime the local date-time, not null
     */
    public static function ofDateAndTime(LocalDate $date, LocalTime $time)
    {
        return new LocalDateTime($date, $time);
    }

//-------------------------------------------------------------------------
    /**
     * Obtains an instance of {@code LocalDateTime} from an {@code Instant} and zone ID.
     * <p>
     * This creates a local date-time based on the specified instant.
     * First, the offset from UTC/Greenwich is obtained using the zone ID and instant,
     * which is simple as there is only one valid offset for each instant.
     * Then, the instant and offset are used to calculate the local date-time.
     *
     * @param Instant $instant the instant to create the date-time from, not null
     * @param ZoneId $zone the time-zone, which may be an offset, not null
     * @return LocalDateTime the local date-time, not null
     * @throws DateTimeException if the result exceeds the supported range
     */
    public static function ofInstant(Instant $instant, ZoneId $zone)
    {
        $rules = $zone->getRules();
        $offset = $rules->getOffset($instant);
        return self::ofEpochSecond($instant->getEpochSecond(), $instant->getNano(), $offset);
    }

    /**
     * Obtains an instance of {@code LocalDateTime} using seconds from the
     * epoch of 1970-01-01T00:00:00Z.
     * <p>
     * This allows the {@link ChronoField#INSTANT_SECONDS epoch-second} field
     * to be converted to a local date-time. This is primarily intended for
     * low-level conversions rather than general application usage.
     *
     * @param int $epochSecond the number of seconds from the epoch of 1970-01-01T00:00:00Z
     * @param int $nanoOfSecond the nanosecond within the second, from 0 to 999,999,999
     * @param ZoneOffset $offset the zone offset, not null
     * @return LocalDateTime the local date-time, not null
     * @throws DateTimeException if the result exceeds the supported range,
     *  or if the nano-of-second is invalid
     */
    public static function ofEpochSecond($epochSecond, $nanoOfSecond, ZoneOffset $offset)
    {
        try {
            ChronoField::NANO_OF_SECOND()->checkValidValue($nanoOfSecond);
            $localSecond = Math::addExact($epochSecond, $offset->getTotalSeconds());
            $localEpochDay = Math::floorDiv($localSecond, LocalTime::SECONDS_PER_DAY);
            $secsOfDay = Math::floorMod($localSecond, LocalTime::SECONDS_PER_DAY);
            $date = LocalDate::ofEpochDay($localEpochDay);
            $time = LocalTime::ofNanoOfDay($secsOfDay * LocalTime::NANOS_PER_SECOND + $nanoOfSecond);
            return new LocalDateTime($date, $time);
        } catch (ArithmeticException $ex) {
            throw new DateTimeException("Value out of bounds", $ex);
        }
    }

//-----------------------------------------------------------------------
    /**
     * Obtains an instance of {@code LocalDateTime} from a temporal object.
     * <p>
     * This obtains a local date-time based on the specified temporal.
     * A {@code TemporalAccessor} represents an arbitrary set of date and time information,
     * which this factory converts to an instance of {@code LocalDateTime}.
     * <p>
     * The conversion extracts and combines the {@code LocalDate} and the
     * {@code LocalTime} from the temporal object.
     * Implementations are permitted to perform optimizations such as accessing
     * those fields that are equivalent to the relevant objects.
     * <p>
     * This method matches the signature of the functional interface {@link TemporalQuery}
     * allowing it to be used as a query via method reference, {@code LocalDateTime::from}.
     *
     * @param TemporalAccessor $temporal the temporal object to convert, not null
     * @return LocalDateTime the local date-time, not null
     * @throws DateTimeException if unable to convert to a {@code LocalDateTime}
     */
    public static function from(TemporalAccessor $temporal)
    {
        if ($temporal instanceof LocalDateTime) {
            return $temporal;
        } else
            if ($temporal instanceof ZonedDateTime) {
                return $temporal->toLocalDateTime();
            } else if ($temporal instanceof OffsetDateTime) {
                return $temporal->toLocalDateTime();
            }
        try {
            $date = LocalDate::from($temporal);
            $time = LocalTime::from($temporal);
            return new LocalDateTime($date, $time);
        } catch (DateTimeException $ex) {
            throw new DateTimeException("Unable to obtain LocalDateTime from TemporalAccessor: " .
                $temporal . " of type " . get_class($temporal), $ex);
        }
    }

    //-----------------------------------------------------------------------
    /**
     * Obtains an instance of {@code LocalDateTime} from a text string such as {@code 2007-12-03T10:15:30}.
     * <p>
     * The string must represent a valid date-time and is parsed using
     * {@link java.time.format.DateTimeFormatter#ISO_LOCAL_DATE_TIME}.
     *
     * @param string $text the text to parse such as "2007-12-03T10:15:30", not null
     * @return LocalDateTime the parsed local date-time, not null
     * @throws DateTimeParseException if the text cannot be parsed
     */
    public static function parse($text)
    {
        return self::parseWith($text, DateTimeFormatter::ISO_LOCAL_DATE_TIME());
    }

    /**
     * Obtains an instance of {@code LocalDateTime} from a text string using a specific formatter.
     * <p>
     * The text is parsed using the formatter, returning a date-time.
     *
     * @param string $text the text to parse, not null
     * @param DateTimeFormatter $formatter the formatter to use, not null
     * @return LocalDateTime the parsed local date-time, not null
     * @throws DateTimeParseException if the text cannot be parsed
     */
    public static function parseWith($text, DateTimeFormatter $formatter)
    {
        return $formatter->parseQuery($text, TemporalQueries::fromCallable([LocalDateTime::class, 'from']));
    }

//-----------------------------------------------------------------------
    /**
     * Constructor.
     *
     * @param LocalDate $date the date part of the date-time, validated not null
     * @param LocalTime $time the time part of the date-time, validated not null
     */
    private function __construct(LocalDate $date, LocalTime $time)
    {
        $this->date = $date;
        $this->time = $time;
    }

    /**
     * Returns a copy of this date-time with the new date and time, checking
     * to see if a new object is in fact required.
     *
     * TODO package visibility
     *
     * @param LocalDate $newDate the date of the new date-time, not null
     * @param LocalTime $newTime the time of the new date-time, not null
     * @return LocalDateTime the date-time, not null
     */
    public function _with(LocalDate $newDate, LocalTime $newTime)
    {
        if ($this->date === $newDate && $this->time === $newTime) {
            return $this;
        }

        return new LocalDateTime($newDate, $newTime);
    }

//-----------------------------------------------------------------------
    /**
     * Checks if the specified field is supported.
     * <p>
     * This checks if this date-time can be queried for the specified field.
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
     * <li>{@code DAY_OF_WEEK}
     * <li>{@code ALIGNED_DAY_OF_WEEK_IN_MONTH}
     * <li>{@code ALIGNED_DAY_OF_WEEK_IN_YEAR}
     * <li>{@code DAY_OF_MONTH}
     * <li>{@code DAY_OF_YEAR}
     * <li>{@code EPOCH_DAY}
     * <li>{@code ALIGNED_WEEK_OF_MONTH}
     * <li>{@code ALIGNED_WEEK_OF_YEAR}
     * <li>{@code MONTH_OF_YEAR}
     * <li>{@code PROLEPTIC_MONTH}
     * <li>{@code YEAR_OF_ERA}
     * <li>{@code YEAR}
     * <li>{@code ERA}
     * </ul>
     * All other {@code ChronoField} instances will return false.
     * <p>
     * If the field is not a {@code ChronoField}, then the result of this method
     * is obtained by invoking {@code TemporalField.isSupportedBy(TemporalAccessor)}
     * passing {@code this} as the argument.
     * Whether the field is supported is determined by the field.
     *
     * @param TemporalField $field the field to check, null returns false
     * @return bool true if the field is supported on this date-time, false if not
     */
    public function isSupported(TemporalField $field)
    {
        if ($field instanceof ChronoField) {
            $f = $field;
            return $f->isDateBased() || $f->isTimeBased();
        }

        return $field !== null && $field->isSupportedBy($this);
    }

    /**
     * Checks if the specified unit is supported.
     * <p>
     * This checks if the specified unit can be added to, or subtracted from, this date-time.
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
     * <li>{@code DAYS}
     * <li>{@code WEEKS}
     * <li>{@code MONTHS}
     * <li>{@code YEARS}
     * <li>{@code DECADES}
     * <li>{@code CENTURIES}
     * <li>{@code MILLENNIA}
     * <li>{@code ERAS}
     * </ul>
     * All other {@code ChronoUnit} instances will return false.
     * <p>
     * If the unit is not a {@code ChronoUnit}, then the result of this method
     * is obtained by invoking {@code TemporalUnit.isSupportedBy(Temporal)}
     * passing {@code this} as the argument.
     * Whether the unit is supported is determined by the unit.
     *
     * @param TemporalUnit $unit the unit to check, null returns false
     * @return bool true if the unit can be added/subtracted, false if not
     */
    public function isUnitSupported(TemporalUnit $unit)
    {
        return parent::isUnitSupported($unit);
    }

//-----------------------------------------------------------------------
    /**
     * Gets the range of valid values for the specified field.
     * <p>
     * The range object expresses the minimum and maximum valid values for a field.
     * This date-time is used to enhance the accuracy of the returned range.
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
     * @param TemporalField $field the field to query the range for, not null
     * @return ValueRange the range of valid values for the field, not null
     * @throws DateTimeException if the range for the field cannot be obtained
     * @throws UnsupportedTemporalTypeException if the field is not supported
     */
    public function range(TemporalField $field)
    {
        if ($field instanceof ChronoField) {
            $f = $field;
            return ($f->isTimeBased() ? $this->time->range($field) : $this->date->range($field));
        }

        return $field->rangeRefinedBy($this);
    }

    /**
     * Gets the value of the specified field from this date-time as an {@code int}.
     * <p>
     * This queries this date-time for the value of the specified field.
     * The returned value will always be within the valid range of values for the field.
     * If it is not possible to return the value, because the field is not supported
     * or for some other reason, an exception is thrown.
     * <p>
     * If the field is a {@link ChronoField} then the query is implemented here.
     * The {@link #isSupported(TemporalField) supported fields} will return valid
     * values based on this date-time, except {@code NANO_OF_DAY}, {@code MICRO_OF_DAY},
     * {@code EPOCH_DAY} and {@code PROLEPTIC_MONTH} which are too large to fit in
     * an {@code int} and throw a {@code DateTimeException}.
     * All other {@code ChronoField} instances will throw an {@code UnsupportedTemporalTypeException}.
     * <p>
     * If the field is not a {@code ChronoField}, then the result of this method
     * is obtained by invoking {@code TemporalField.getFrom(TemporalAccessor)}
     * passing {@code this} as the argument. Whether the value can be obtained,
     * and what the value represents, is determined by the field.
     *
     * @param TemporalField $field the field to get, not null
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
            $f = $field;
            return ($f->isTimeBased() ? $this->time->get($field) : $this->date->get($field));
        }

        return parent::get($field);
    }

    /**
     * Gets the value of the specified field from this date-time as a {@code long}.
     * <p>
     * This queries this date-time for the value of the specified field.
     * If it is not possible to return the value, because the field is not supported
     * or for some other reason, an exception is thrown.
     * <p>
     * If the field is a {@link ChronoField} then the query is implemented here.
     * The {@link #isSupported(TemporalField) supported fields} will return valid
     * values based on this date-time.
     * All other {@code ChronoField} instances will throw an {@code UnsupportedTemporalTypeException}.
     * <p>
     * If the field is not a {@code ChronoField}, then the result of this method
     * is obtained by invoking {@code TemporalField.getFrom(TemporalAccessor)}
     * passing {@code this} as the argument. Whether the value can be obtained,
     * and what the value represents, is determined by the field.
     *
     * @param TemporalField $field the field to get, not null
     * @return int the value for the field
     * @throws DateTimeException if a value for the field cannot be obtained
     * @throws UnsupportedTemporalTypeException if the field is not supported
     * @throws ArithmeticException if numeric overflow occurs
     */
    public function getLong(TemporalField $field)
    {
        if ($field instanceof ChronoField) {
            $f = $field;
            return ($f->isTimeBased() ? $this->time->getLong($field) : $this->date->getLong($field));
        }

        return $field->getFrom($this);
    }

    //-----------------------------------------------------------------------
    /**
     * Gets the {@code LocalDate} part of this date-time.
     * <p>
     * This returns a {@code LocalDate} with the same year, month and day
     * as this date-time.
     *
     * @return LocalDate the date part of this date-time, not null
     */
    public function toLocalDate()
    {
        return $this->date;
    }

    /**
     * Gets the year field.
     * <p>
     * This method returns the primitive {@code int} value for the year.
     * <p>
     * The year returned by this method is proleptic as per {@code get(YEAR)}.
     * To obtain the year-of-era, use {@code get(YEAR_OF_ERA)}.
     *
     * @return int the year, from MIN_YEAR to MAX_YEAR
     */
    public function getYear()
    {
        return $this->date->getYear();
    }

    /**
     * Gets the month-of-year field from 1 to 12.
     * <p>
     * This method returns the month as an {@code int} from 1 to 12.
     * Application code is frequently clearer if the enum {@link Month}
     * is used by calling {@link #getMonth()}.
     *
     * @return int the month-of-year, from 1 to 12
     * @see #getMonth()
     */
    public function getMonthValue()
    {
        return $this->date->getMonthValue();
    }

    /**
     * Gets the month-of-year field using the {@code Month} enum.
     * <p>
     * This method returns the enum {@link Month} for the month.
     * This avoids confusion as to what {@code int} values mean.
     * If you need access to the primitive {@code int} value then the enum
     * provides the {@link Month#getValue() int value}.
     *
     * @return Month the month-of-year, not null
     * @see #getMonthValue()
     */
    public function getMonth()
    {
        return $this->date->getMonth();
    }

    /**
     * Gets the day-of-month field.
     * <p>
     * This method returns the primitive {@code int} value for the day-of-month.
     *
     * @return int the day-of-month, from 1 to 31
     */
    public function getDayOfMonth()
    {
        return $this->date->getDayOfMonth();
    }

    /**
     * Gets the day-of-year field.
     * <p>
     * This method returns the primitive {@code int} value for the day-of-year.
     *
     * @return int the day-of-year, from 1 to 365, or 366 in a leap year
     */
    public function getDayOfYear()
    {
        return $this->date->getDayOfYear();
    }

    /**
     * Gets the day-of-week field, which is an enum {@code DayOfWeek}.
     * <p>
     * This method returns the enum {@link DayOfWeek} for the day-of-week.
     * This avoids confusion as to what {@code int} values mean.
     * If you need access to the primitive {@code int} value then the enum
     * provides the {@link DayOfWeek#getValue() int value}.
     * <p>
     * Additional information can be obtained from the {@code DayOfWeek}.
     * This includes textual names of the values.
     *
     * @return DayOfWeek the day-of-week, not null
     */
    public function getDayOfWeek()
    {
        return $this->date->getDayOfWeek();
    }

    //-----------------------------------------------------------------------
    /**
     * Gets the {@code LocalTime} part of this date-time.
     * <p>
     * This returns a {@code LocalTime} with the same hour, minute, second and
     * nanosecond as this date-time.
     *
     * @return LocalTime the time part of this date-time, not null
     */
    public function toLocalTime()
    {
        return $this->time;
    }

    /**
     * Gets the hour-of-day field.
     *
     * @return int the hour-of-day, from 0 to 23
     */
    public function getHour()
    {
        return $this->time->getHour();
    }

    /**
     * Gets the minute-of-hour field.
     *
     * @return int the minute-of-hour, from 0 to 59
     */
    public function getMinute()
    {
        return $this->time->getMinute();
    }

    /**
     * Gets the second-of-minute field.
     *
     * @return int the second-of-minute, from 0 to 59
     */
    public function getSecond()
    {
        return $this->time->getSecond();
    }

    /**
     * Gets the nano-of-second field.
     *
     * @return int the nano-of-second, from 0 to 999,999,999
     */
    public function getNano()
    {
        return $this->time->getNano();
    }

    //-----------------------------------------------------------------------
    /**
     * Returns an adjusted copy of this date-time.
     * <p>
     * This returns a {@code LocalDateTime}, based on this one, with the date-time adjusted.
     * The adjustment takes place using the specified adjuster strategy object.
     * Read the documentation of the adjuster to understand what adjustment will be made.
     * <p>
     * A simple adjuster might simply set the one of the fields, such as the year field.
     * A more complex adjuster might set the date to the last day of the month.
     * <p>
     * A selection of common adjustments is provided in
     * {@link java.time.temporal.TemporalAdjusters TemporalAdjusters}.
     * These include finding the "last day of the month" and "next Wednesday".
     * Key date-time classes also implement the {@code TemporalAdjuster} interface,
     * such as {@link Month} and {@link java.time.MonthDay MonthDay}.
     * The adjuster is responsible for handling special cases, such as the varying
     * lengths of month and leap years.
     * <p>
     * For example this code returns a date on the last day of July:
     * <pre>
     *  import static java.time.Month.*;
     *  import static java.time.temporal.TemporalAdjusters.*;
     *
     *  result = localDateTime.with(JULY).with(lastDayOfMonth());
     * </pre>
     * <p>
     * The classes {@link LocalDate} and {@link LocalTime} implement {@code TemporalAdjuster},
     * thus this method can be used to change the date, time or offset:
     * <pre>
     *  result = localDateTime.with(date);
     *  result = localDateTime.with(time);
     * </pre>
     * <p>
     * The result of this method is obtained by invoking the
     * {@link TemporalAdjuster#adjustInto(Temporal)} method on the
     * specified adjuster passing {@code this} as the argument.
     * <p>
     * This instance is immutable and unaffected by this method call.
     *
     * @param TemporalAdjuster $adjuster the adjuster to use, not null
     * @return LocalDateTime a {@code LocalDateTime} based on {@code this} with the adjustment made, not null
     * @throws DateTimeException if the adjustment cannot be made
     * @throws ArithmeticException if numeric overflow occurs
     */
    public function adjust(TemporalAdjuster $adjuster)
    {
        // optimizations
        if ($adjuster instanceof LocalDate) {
            return $this->_with($adjuster, $this->time);
        } else
            if ($adjuster instanceof LocalTime) {
                return $this->_with($this->date, $adjuster);
            } else if ($adjuster instanceof LocalDateTime) {
                return $adjuster;
            }
        return $adjuster->adjustInto($this);
    }

    /**
     * Returns a copy of this date-time with the specified field set to a new value.
     * <p>
     * This returns a {@code LocalDateTime}, based on this one, with the value
     * for the specified field changed.
     * This can be used to change any supported field, such as the year, month or day-of-month.
     * If it is not possible to set the value, because the field is not supported or for
     * some other reason, an exception is thrown.
     * <p>
     * In some cases, changing the specified field can cause the resulting date-time to become invalid,
     * such as changing the month from 31st January to February would make the day-of-month invalid.
     * In cases like this, the field is responsible for resolving the date. Typically it will choose
     * the previous valid date, which would be the last valid day of February in this example.
     * <p>
     * If the field is a {@link ChronoField} then the adjustment is implemented here.
     * The {@link #isSupported(TemporalField) supported fields} will behave as per
     * the matching method on {@link LocalDate#with(TemporalField, long) LocalDate}
     * or {@link LocalTime#with(TemporalField, long) LocalTime}.
     * All other {@code ChronoField} instances will throw an {@code UnsupportedTemporalTypeException}.
     * <p>
     * If the field is not a {@code ChronoField}, then the result of this method
     * is obtained by invoking {@code TemporalField.adjustInto(Temporal, long)}
     * passing {@code this} as the argument. In this case, the field determines
     * whether and how to adjust the instant.
     * <p>
     * This instance is immutable and unaffected by this method call.
     *
     * @param TemporalField $field the field to set in the result, not null
     * @param int $newValue the new value of the field in the result
     * @return LocalDateTime a {@code LocalDateTime} based on {@code this} with the specified field set, not null
     * @throws DateTimeException if the field cannot be set
     * @throws UnsupportedTemporalTypeException if the field is not supported
     * @throws ArithmeticException if numeric overflow occurs
     */
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
        return $field->adjustInto($this, $newValue);
    }

    //-----------------------------------------------------------------------
    /**
     * Returns a copy of this {@code LocalDateTime} with the year altered.
     * <p>
     * The time does not affect the calculation and will be the same in the result.
     * If the day-of-month is invalid for the year, it will be changed to the last valid day of the month.
     * <p>
     * This instance is immutable and unaffected by this method call.
     *
     * @param int $year the year to set in the result, from MIN_YEAR to MAX_YEAR
     * @return LocalDateTime a {@code LocalDateTime} based on this date-time with the requested year, not null
     * @throws DateTimeException if the year value is invalid
     */
    public function withYear($year)
    {
        return $this->_with($this->date->withYear($year), $this->time);
    }

    /**
     * Returns a copy of this {@code LocalDateTime} with the month-of-year altered.
     * <p>
     * The time does not affect the calculation and will be the same in the result.
     * If the day-of-month is invalid for the year, it will be changed to the last valid day of the month.
     * <p>
     * This instance is immutable and unaffected by this method call.
     *
     * @param int $month the month-of-year to set in the result, from 1 (January) to 12 (December)
     * @return LocalDateTime a {@code LocalDateTime} based on this date-time with the requested month, not null
     * @throws DateTimeException if the month-of-year value is invalid
     */
    public function withMonth($month)
    {
        return $this->_with($this->date->withMonth($month), $this->time);
    }

    /**
     * Returns a copy of this {@code LocalDateTime} with the day-of-month altered.
     * <p>
     * If the resulting date-time is invalid, an exception is thrown.
     * The time does not affect the calculation and will be the same in the result.
     * <p>
     * This instance is immutable and unaffected by this method call.
     *
     * @param int $dayOfMonth the day-of-month to set in the result, from 1 to 28-31
     * @return LocalDateTime a {@code LocalDateTime} based on this date-time with the requested day, not null
     * @throws DateTimeException if the day-of-month value is invalid,
     *  or if the day-of-month is invalid for the month-year
     */
    public function withDayOfMonth($dayOfMonth)
    {
        return $this->_with($this->date->withDayOfMonth($dayOfMonth), $this->time);
    }

    /**
     * Returns a copy of this {@code LocalDateTime} with the day-of-year altered.
     * <p>
     * If the resulting date-time is invalid, an exception is thrown.
     * <p>
     * This instance is immutable and unaffected by this method call.
     *
     * @param int $dayOfYear the day-of-year to set in the result, from 1 to 365-366
     * @return LocalDateTime a {@code LocalDateTime} based on this date with the requested day, not null
     * @throws DateTimeException if the day-of-year value is invalid,
     *  or if the day-of-year is invalid for the year
     */
    public function withDayOfYear($dayOfYear)
    {
        return $this->_with($this->date->withDayOfYear($dayOfYear), $this->time);
    }

    //-----------------------------------------------------------------------
    /**
     * Returns a copy of this {@code LocalDateTime} with the hour-of-day altered.
     * <p>
     * This instance is immutable and unaffected by this method call.
     *
     * @param int $hour the hour-of-day to set in the result, from 0 to 23
     * @return LocalDateTime a {@code LocalDateTime} based on this date-time with the requested hour, not null
     * @throws DateTimeException if the hour value is invalid
     */
    public function withHour($hour)
    {
        $newTime = $this->time->withHour($hour);
        return $this->_with($this->date, $newTime);
    }

    /**
     * Returns a copy of this {@code LocalDateTime} with the minute-of-hour altered.
     * <p>
     * This instance is immutable and unaffected by this method call.
     *
     * @param int $minute the minute-of-hour to set in the result, from 0 to 59
     * @return LocalDateTime a {@code LocalDateTime} based on this date-time with the requested minute, not null
     * @throws DateTimeException if the minute value is invalid
     */
    public function withMinute($minute)
    {
        $newTime = $this->time->withMinute($minute);
        return $this->_with($this->date, $newTime);
    }

    /**
     * Returns a copy of this {@code LocalDateTime} with the second-of-minute altered.
     * <p>
     * This instance is immutable and unaffected by this method call.
     *
     * @param int $second the second-of-minute to set in the result, from 0 to 59
     * @return LocalDateTime a {@code LocalDateTime} based on this date-time with the requested second, not null
     * @throws DateTimeException if the second value is invalid
     */
    public function withSecond($second)
    {
        $newTime = $this->time->withSecond($second);
        return $this->_with($this->date, $newTime);
    }

    /**
     * Returns a copy of this {@code LocalDateTime} with the nano-of-second altered.
     * <p>
     * This instance is immutable and unaffected by this method call.
     *
     * @param int $nanoOfSecond the nano-of-second to set in the result, from 0 to 999,999,999
     * @return LocalDateTime a {@code LocalDateTime} based on this date-time with the requested nanosecond, not null
     * @throws DateTimeException if the nano value is invalid
     */
    public function withNano($nanoOfSecond)
    {
        $newTime = $this->time->withNano($nanoOfSecond);
        return $this->_with($this->date, $newTime);
    }

    //-----------------------------------------------------------------------
    /**
     * Returns a copy of this {@code LocalDateTime} with the time truncated.
     * <p>
     * Truncation returns a copy of the original date-time with fields
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
     * @param TemporalUnit $unit the unit to truncate to, not null
     * @return LocalDateTime a {@code LocalDateTime} based on this date-time with the time truncated, not null
     * @throws DateTimeException if unable to truncate
     * @throws UnsupportedTemporalTypeException if the unit is not supported
     */
    public function truncatedTo(TemporalUnit $unit)
    {
        return $this->_with($this->date, $this->time->truncatedTo($unit));
    }

    //-----------------------------------------------------------------------
    /**
     * Returns a copy of this date-time with the specified amount added.
     * <p>
     * This returns a {@code LocalDateTime}, based on this one, with the specified amount added.
     * The amount is typically {@link Period} or {@link Duration} but may be
     * any other type implementing the {@link TemporalAmount} interface.
     * <p>
     * The calculation is delegated to the amount object by calling
     * {@link TemporalAmount#addTo(Temporal)}. The amount implementation is free
     * to implement the addition in any way it wishes, however it typically
     * calls back to {@link #plus(long, TemporalUnit)}. Consult the documentation
     * of the amount implementation to determine if it can be successfully added.
     * <p>
     * This instance is immutable and unaffected by this method call.
     *
     * @param TemporalAmount $amountToAdd the amount to add, not null
     * @return LocalDateTime a {@code LocalDateTime} based on this date-time with the addition made, not null
     * @throws DateTimeException if the addition cannot be made
     * @throws ArithmeticException if numeric overflow occurs
     */
    public function plusAmount(TemporalAmount $amountToAdd)
    {
        if ($amountToAdd instanceof Period) {
            $periodToAdd = $amountToAdd;
            return $this->_with($this->date->plusAmount($periodToAdd), $this->time);
        }
        return $amountToAdd->addTo($this);
    }

    /**
     * Returns a copy of this date-time with the specified amount added.
     * <p>
     * This returns a {@code LocalDateTime}, based on this one, with the amount
     * in terms of the unit added. If it is not possible to add the amount, because the
     * unit is not supported or for some other reason, an exception is thrown.
     * <p>
     * If the field is a {@link ChronoUnit} then the addition is implemented here.
     * Date units are added as per {@link LocalDate#plus(long, TemporalUnit)}.
     * Time units are added as per {@link LocalTime#plus(long, TemporalUnit)} with
     * any overflow in days added equivalent to using {@link #plusDays(long)}.
     * <p>
     * If the field is not a {@code ChronoUnit}, then the result of this method
     * is obtained by invoking {@code TemporalUnit.addTo(Temporal, long)}
     * passing {@code this} as the argument. In this case, the unit determines
     * whether and how to perform the addition.
     * <p>
     * This instance is immutable and unaffected by this method call.
     *
     * @param int $amountToAdd the amount of the unit to add to the result, may be negative
     * @param TemporalUnit $unit the unit of the amount to add, not null
     * @return LocalDateTime a {@code LocalDateTime} based on this date-time with the specified amount added, not null
     * @throws DateTimeException if the addition cannot be made
     * @throws UnsupportedTemporalTypeException if the unit is not supported
     * @throws ArithmeticException if numeric overflow occurs
     */
    public function plus($amountToAdd, TemporalUnit $unit)
    {
        if ($unit instanceof ChronoUnit) {
            $f = $unit;
            switch ($f) {
                case ChronoUnit::NANOS():
                    return $this->plusNanos($amountToAdd);
                case ChronoUnit::MICROS():
                    return $this->plusDays($amountToAdd / LocalTime::MICROS_PER_DAY)->plusNanos(($amountToAdd % LocalTime::MICROS_PER_DAY) * 1000);
                case ChronoUnit::MILLIS():
                    return $this->plusDays($amountToAdd / LocalTime::MILLIS_PER_DAY)->plusNanos(($amountToAdd % LocalTime::MILLIS_PER_DAY) * 1000000);
                case ChronoUnit::SECONDS():
                    return $this->plusSeconds($amountToAdd);
                case ChronoUnit::MINUTES():
                    return $this->plusMinutes($amountToAdd);
                case ChronoUnit::HOURS():
                    return $this->plusHours($amountToAdd);
                case ChronoUnit::HALF_DAYS():
                    return $this->plusDays($amountToAdd / 256)->plusHours(($amountToAdd % 256) * 12);  // no overflow (256 is multiple of 2)
            }
            return $this->_with($this->date->plus($amountToAdd, $unit), $this->time);
        }
        return $unit->addTo($this, $amountToAdd);
    }

    //-----------------------------------------------------------------------
    /**
     * Returns a copy of this {@code LocalDateTime} with the specified number of years added.
     * <p>
     * This method adds the specified amount to the years field in three steps:
     * <ol>
     * <li>Add the input years to the year field</li>
     * <li>Check if the resulting date would be invalid</li>
     * <li>Adjust the day-of-month to the last valid day if necessary</li>
     * </ol>
     * <p>
     * For example, 2008-02-29 (leap year) plus one year would result in the
     * invalid date 2009-02-29 (standard year). Instead of returning an invalid
     * result, the last valid day of the month, 2009-02-28, is selected instead.
     * <p>
     * This instance is immutable and unaffected by this method call.
     *
     * @param int $years the years to add, may be negative
     * @return LocalDateTime a {@code LocalDateTime} based on this date-time with the years added, not null
     * @throws DateTimeException if the result exceeds the supported date range
     */
    public function plusYears($years)
    {
        $newDate = $this->date->plusYears($years);
        return $this->_with($newDate, $this->time);
    }

    /**
     * Returns a copy of this {@code LocalDateTime} with the specified number of months added.
     * <p>
     * This method adds the specified amount to the months field in three steps:
     * <ol>
     * <li>Add the input months to the month-of-year field</li>
     * <li>Check if the resulting date would be invalid</li>
     * <li>Adjust the day-of-month to the last valid day if necessary</li>
     * </ol>
     * <p>
     * For example, 2007-03-31 plus one month would result in the invalid date
     * 2007-04-31. Instead of returning an invalid result, the last valid day
     * of the month, 2007-04-30, is selected instead.
     * <p>
     * This instance is immutable and unaffected by this method call.
     *
     * @param int $months the months to add, may be negative
     * @return LocalDateTime a {@code LocalDateTime} based on this date-time with the months added, not null
     * @throws DateTimeException if the result exceeds the supported date range
     */
    public function plusMonths($months)
    {
        $newDate = $this->date->plusMonths($months);
        return $this->_with($newDate, $this->time);
    }

    /**
     * Returns a copy of this {@code LocalDateTime} with the specified number of weeks added.
     * <p>
     * This method adds the specified amount in weeks to the days field incrementing
     * the month and year fields as necessary to ensure the result remains valid.
     * The result is only invalid if the maximum/minimum year is exceeded.
     * <p>
     * For example, 2008-12-31 plus one week would result in 2009-01-07.
     * <p>
     * This instance is immutable and unaffected by this method call.
     *
     * @param int $weeks the weeks to add, may be negative
     * @return LocalDateTime a {@code LocalDateTime} based on this date-time with the weeks added, not null
     * @throws DateTimeException if the result exceeds the supported date range
     */
    public function plusWeeks($weeks)
    {
        $newDate = $this->date->plusWeeks($weeks);
        return $this->_with($newDate, $this->time);
    }

    /**
     * Returns a copy of this {@code LocalDateTime} with the specified number of days added.
     * <p>
     * This method adds the specified amount to the days field incrementing the
     * month and year fields as necessary to ensure the result remains valid.
     * The result is only invalid if the maximum/minimum year is exceeded.
     * <p>
     * For example, 2008-12-31 plus one day would result in 2009-01-01.
     * <p>
     * This instance is immutable and unaffected by this method call.
     *
     * @param int $days the days to add, may be negative
     * @return LocalDateTime a {@code LocalDateTime} based on this date-time with the days added, not null
     * @throws DateTimeException if the result exceeds the supported date range
     */
    public function plusDays($days)
    {
        $newDate = $this->date->plusDays($days);
        return $this->_with($newDate, $this->time);
    }

    //-----------------------------------------------------------------------
    /**
     * Returns a copy of this {@code LocalDateTime} with the specified number of hours added.
     * <p>
     * This instance is immutable and unaffected by this method call.
     *
     * @param int $hours the hours to add, may be negative
     * @return LocalDateTime a {@code LocalDateTime} based on this date-time with the hours added, not null
     * @throws DateTimeException if the result exceeds the supported date range
     */
    public function plusHours($hours)
    {
        return $this->plusWithOverflow($this->date, $hours, 0, 0, 0, 1);
    }

    /**
     * Returns a copy of this {@code LocalDateTime} with the specified number of minutes added.
     * <p>
     * This instance is immutable and unaffected by this method call.
     *
     * @param int $minutes the minutes to add, may be negative
     * @return LocalDateTime a {@code LocalDateTime} based on this date-time with the minutes added, not null
     * @throws DateTimeException if the result exceeds the supported date range
     */
    public function plusMinutes($minutes)
    {
        return $this->plusWithOverflow($this->date, 0, $minutes, 0, 0, 1);
    }

    /**
     * Returns a copy of this {@code LocalDateTime} with the specified number of seconds added.
     * <p>
     * This instance is immutable and unaffected by this method call.
     *
     * @param int $seconds the seconds to add, may be negative
     * @return LocalDateTime a {@code LocalDateTime} based on this date-time with the seconds added, not null
     * @throws DateTimeException if the result exceeds the supported date range
     */
    public function plusSeconds($seconds)
    {
        return $this->plusWithOverflow($this->date, 0, 0, $seconds, 0, 1);
    }

    /**
     * Returns a copy of this {@code LocalDateTime} with the specified number of nanoseconds added.
     * <p>
     * This instance is immutable and unaffected by this method call.
     *
     * @param int $nanos the nanos to add, may be negative
     * @return LocalDateTime a {@code LocalDateTime} based on this date-time with the nanoseconds added, not null
     * @throws DateTimeException if the result exceeds the supported date range
     */
    public function plusNanos($nanos)
    {
        return $this->plusWithOverflow($this->date, 0, 0, 0, $nanos, 1);
    }

    //-----------------------------------------------------------------------
    /**
     * Returns a copy of this date-time with the specified amount subtracted.
     * <p>
     * This returns a {@code LocalDateTime}, based on this one, with the specified amount subtracted.
     * The amount is typically {@link Period} or {@link Duration} but may be
     * any other type implementing the {@link TemporalAmount} interface.
     * <p>
     * The calculation is delegated to the amount object by calling
     * {@link TemporalAmount#subtractFrom(Temporal)}. The amount implementation is free
     * to implement the subtraction in any way it wishes, however it typically
     * calls back to {@link #minus(long, TemporalUnit)}. Consult the documentation
     * of the amount implementation to determine if it can be successfully subtracted.
     * <p>
     * This instance is immutable and unaffected by this method call.
     *
     * @param TemporalAmount $amountToSubtract the amount to subtract, not null
     * @return LocalDateTime a {@code LocalDateTime} based on this date-time with the subtraction made, not null
     * @throws DateTimeException if the subtraction cannot be made
     * @throws ArithmeticException if numeric overflow occurs
     */
    public function minusAmount(TemporalAmount $amountToSubtract)
    {
        if ($amountToSubtract instanceof Period) {
            $periodToSubtract = $amountToSubtract;
            return $this->_with($this->date->minusAmount($periodToSubtract), $this->time);
        }
        return $amountToSubtract->subtractFrom($this);
    }

    /**
     * Returns a copy of this date-time with the specified amount subtracted.
     * <p>
     * This returns a {@code LocalDateTime}, based on this one, with the amount
     * in terms of the unit subtracted. If it is not possible to subtract the amount,
     * because the unit is not supported or for some other reason, an exception is thrown.
     * <p>
     * This method is equivalent to {@link #plus(long, TemporalUnit)} with the amount negated.
     * See that method for a full description of how addition, and thus subtraction, works.
     * <p>
     * This instance is immutable and unaffected by this method call.
     *
     * @param int $amountToSubtract the amount of the unit to subtract from the result, may be negative
     * @param TemporalUnit $unit the unit of the amount to subtract, not null
     * @return LocalDateTime a {@code LocalDateTime} based on this date-time with the specified amount subtracted, not null
     * @throws DateTimeException if the subtraction cannot be made
     * @throws UnsupportedTemporalTypeException if the unit is not supported
     * @throws ArithmeticException if numeric overflow occurs
     */
    public function minus($amountToSubtract, TemporalUnit $unit)
    {
        return ($amountToSubtract === Long::MIN_VALUE ? $this->plus(Long::MAX_VALUE, $unit)->plus(1, $unit) : $this->plus(-$amountToSubtract, $unit));
    }

    //-----------------------------------------------------------------------
    /**
     * Returns a copy of this {@code LocalDateTime} with the specified number of years subtracted.
     * <p>
     * This method subtracts the specified amount from the years field in three steps:
     * <ol>
     * <li>Subtract the input years from the year field</li>
     * <li>Check if the resulting date would be invalid</li>
     * <li>Adjust the day-of-month to the last valid day if necessary</li>
     * </ol>
     * <p>
     * For example, 2008-02-29 (leap year) minus one year would result in the
     * invalid date 2009-02-29 (standard year). Instead of returning an invalid
     * result, the last valid day of the month, 2009-02-28, is selected instead.
     * <p>
     * This instance is immutable and unaffected by this method call.
     *
     * @param int $years the years to subtract, may be negative
     * @return LocalDateTime a {@code LocalDateTime} based on this date-time with the years subtracted, not null
     * @throws DateTimeException if the result exceeds the supported date range
     */
    public function minusYears($years)
    {
        return ($years === Long::MIN_VALUE ? $this->plusYears(Long::MAX_VALUE)->plusYears(1) : $this->plusYears(-$years));
    }

    /**
     * Returns a copy of this {@code LocalDateTime} with the specified number of months subtracted.
     * <p>
     * This method subtracts the specified amount from the months field in three steps:
     * <ol>
     * <li>Subtract the input months from the month-of-year field</li>
     * <li>Check if the resulting date would be invalid</li>
     * <li>Adjust the day-of-month to the last valid day if necessary</li>
     * </ol>
     * <p>
     * For example, 2007-03-31 minus one month would result in the invalid date
     * 2007-04-31. Instead of returning an invalid result, the last valid day
     * of the month, 2007-04-30, is selected instead.
     * <p>
     * This instance is immutable and unaffected by this method call.
     *
     * @param int $months the months to subtract, may be negative
     * @return LocalDateTime a {@code LocalDateTime} based on this date-time with the months subtracted, not null
     * @throws DateTimeException if the result exceeds the supported date range
     */
    public function minusMonths($months)
    {
        return ($months === Long::MIN_VALUE ? $this->plusMonths(Long::MAX_VALUE)->plusMonths(1) : $this->plusMonths(-$months));
    }

    /**
     * Returns a copy of this {@code LocalDateTime} with the specified number of weeks subtracted.
     * <p>
     * This method subtracts the specified amount in weeks from the days field decrementing
     * the month and year fields as necessary to ensure the result remains valid.
     * The result is only invalid if the maximum/minimum year is exceeded.
     * <p>
     * For example, 2009-01-07 minus one week would result in 2008-12-31.
     * <p>
     * This instance is immutable and unaffected by this method call.
     *
     * @param int $weeks the weeks to subtract, may be negative
     * @return LocalDateTime a {@code LocalDateTime} based on this date-time with the weeks subtracted, not null
     * @throws DateTimeException if the result exceeds the supported date range
     */
    public function minusWeeks($weeks)
    {
        return ($weeks === Long::MIN_VALUE ? $this->plusWeeks(Long::MAX_VALUE)->plusWeeks(1) : $this->plusWeeks(-$weeks));
    }

    /**
     * Returns a copy of this {@code LocalDateTime} with the specified number of days subtracted.
     * <p>
     * This method subtracts the specified amount from the days field decrementing the
     * month and year fields as necessary to ensure the result remains valid.
     * The result is only invalid if the maximum/minimum year is exceeded.
     * <p>
     * For example, 2009-01-01 minus one day would result in 2008-12-31.
     * <p>
     * This instance is immutable and unaffected by this method call.
     *
     * @param int $days the days to subtract, may be negative
     * @return LocalDateTime a {@code LocalDateTime} based on this date-time with the days subtracted, not null
     * @throws DateTimeException if the result exceeds the supported date range
     */
    public function minusDays($days)
    {
        return ($days === Long::MIN_VALUE ? $this->plusDays(Long::MAX_VALUE)->plusDays(1) : $this->plusDays(-$days));
    }

    //-----------------------------------------------------------------------
    /**
     * Returns a copy of this {@code LocalDateTime} with the specified number of hours subtracted.
     * <p>
     * This instance is immutable and unaffected by this method call.
     *
     * @param int $hours the hours to subtract, may be negative
     * @return LocalDateTime a {@code LocalDateTime} based on this date-time with the hours subtracted, not null
     * @throws DateTimeException if the result exceeds the supported date range
     */
    public function minusHours($hours)
    {
        return $this->plusWithOverflow($this->date, $hours, 0, 0, 0, -1);
    }

    /**
     * Returns a copy of this {@code LocalDateTime} with the specified number of minutes subtracted.
     * <p>
     * This instance is immutable and unaffected by this method call.
     *
     * @param int $minutes the minutes to subtract, may be negative
     * @return LocalDateTime a {@code LocalDateTime} based on this date-time with the minutes subtracted, not null
     * @throws DateTimeException if the result exceeds the supported date range
     */
    public function minusMinutes($minutes)
    {
        return $this->plusWithOverflow($this->date, 0, $minutes, 0, 0, -1);
    }

    /**
     * Returns a copy of this {@code LocalDateTime} with the specified number of seconds subtracted.
     * <p>
     * This instance is immutable and unaffected by this method call.
     *
     * @param int $seconds the seconds to subtract, may be negative
     * @return LocalDateTime a {@code LocalDateTime} based on this date-time with the seconds subtracted, not null
     * @throws DateTimeException if the result exceeds the supported date range
     */
    public function minusSeconds($seconds)
    {
        return $this->plusWithOverflow($this->date, 0, 0, $seconds, 0, -1);
    }

    /**
     * Returns a copy of this {@code LocalDateTime} with the specified number of nanoseconds subtracted.
     * <p>
     * This instance is immutable and unaffected by this method call.
     *
     * @param int $nanos the nanos to subtract, may be negative
     * @return LocalDateTime a {@code LocalDateTime} based on this date-time with the nanoseconds subtracted, not null
     * @throws DateTimeException if the result exceeds the supported date range
     */
    public function minusNanos($nanos)
    {
        return $this->plusWithOverflow($this->date, 0, 0, 0, $nanos, -1);
    }

    //-----------------------------------------------------------------------
    /**
     * Returns a copy of this {@code LocalDateTime} with the specified period added.
     * <p>
     * This instance is immutable and unaffected by this method call.
     *
     * @param LocalDate $newDate the new date to base the calculation on, not null
     * @param int $hours the hours to add, may be negative
     * @param int $minutes the minutes to add, may be negative
     * @param int $seconds the seconds to add, may be negative
     * @param int $nanos the nanos to add, may be negative
     * @param int $sign the sign to determine add or subtract
     * @return LocalDateTime the combined result, not null
     */
    private function plusWithOverflow(LocalDate $newDate, $hours, $minutes, $seconds, $nanos, $sign)
    {
        // 9223372036854775808 long, 2147483648 int
        if (($hours | $minutes | $seconds | $nanos) === 0) {
            return $this->_with($newDate, $this->time);
        }
        $totDays = Math::div($nanos, LocalTime::NANOS_PER_DAY) +             //   max/24*60*60*1B
            Math::div($seconds, LocalTime::SECONDS_PER_DAY) +                //   max/24*60*60
            Math::div($minutes, LocalTime::MINUTES_PER_DAY) +                //   max/24*60
            Math::div($hours, LocalTime::HOURS_PER_DAY);                     //   max/24
        $totDays *= $sign;                                   // total max*0.4237...
        $totNanos = $nanos % LocalTime::NANOS_PER_DAY +                    //   max  86400000000000
            ($seconds % LocalTime::SECONDS_PER_DAY) * LocalTime::NANOS_PER_SECOND +   //   max  86400000000000
            ($minutes % LocalTime::MINUTES_PER_DAY) * LocalTime::NANOS_PER_MINUTE +   //   max  86400000000000
            ($hours % LocalTime::HOURS_PER_DAY) * LocalTime::NANOS_PER_HOUR;          //   max  86400000000000
        $curNoD = $this->time->toNanoOfDay();                       //   max  86400000000000
        $totNanos = $totNanos * $sign + $curNoD;                    // total 432000000000000
        $totDays += Math::floorDiv($totNanos, LocalTime::NANOS_PER_DAY);
        $newNoD = Math::floorMod($totNanos, LocalTime::NANOS_PER_DAY);
        $newTime = ($newNoD === $curNoD ? $this->time : LocalTime::ofNanoOfDay($newNoD));
        return $this->_with($newDate->plusDays($totDays), $newTime);
    }

    //-----------------------------------------------------------------------
    /**
     * Queries this date-time using the specified query.
     * <p>
     * This queries this date-time using the specified query strategy object.
     * The {@code TemporalQuery} object defines the logic to be used to
     * obtain the result. Read the documentation of the query to understand
     * what the result of this method will be.
     * <p>
     * The result of this method is obtained by invoking the
     * {@link TemporalQuery#queryFrom(TemporalAccessor)} method on the
     * specified query passing {@code this} as the argument.
     *
     * @param <R> the type of the result
     * @param TemporalQuery $query the query to invoke, not null
     * @return LocalDateTime the query result, null may be returned (defined by the query)
     * @throws DateTimeException if unable to query (defined by the query)
     * @throws ArithmeticException if numeric overflow occurs (defined by the query)
     */
    public function query(TemporalQuery $query)
    {
        if ($query == TemporalQueries::localDate()) {
            return $this->date;
        }

        return parent::query($query);
    }

    /**
     * Adjusts the specified temporal object to have the same date and time as this object.
     * <p>
     * This returns a temporal object of the same observable type as the input
     * with the date and time changed to be the same as this.
     * <p>
     * The adjustment is equivalent to using {@link Temporal#with(TemporalField, long)}
     * twice, passing {@link ChronoField#EPOCH_DAY} and
     * {@link ChronoField#NANO_OF_DAY} as the fields.
     * <p>
     * In most cases, it is clearer to reverse the calling pattern by using
     * {@link Temporal#with(TemporalAdjuster)}:
     * <pre>
     *   // these two lines are equivalent, but the second approach is recommended
     *   temporal = thisLocalDateTime.adjustInto(temporal);
     *   temporal = temporal.with(thisLocalDateTime);
     * </pre>
     * <p>
     * This instance is immutable and unaffected by this method call.
     *
     * @param Temporal $temporal the target object to be adjusted, not null
     * @return Temporal the adjusted object, not null
     * @throws DateTimeException if unable to make the adjustment
     * @throws ArithmeticException if numeric overflow occurs
     */
    public function adjustInto(Temporal $temporal)
    {
        return parent::adjustInto($temporal);
    }

    /**
     * Calculates the amount of time until another date-time in terms of the specified unit.
     * <p>
     * This calculates the amount of time between two {@code LocalDateTime}
     * objects in terms of a single {@code TemporalUnit}.
     * The start and end points are {@code this} and the specified date-time.
     * The result will be negative if the end is before the start.
     * The {@code Temporal} passed to this method is converted to a
     * {@code LocalDateTime} using {@link #from(TemporalAccessor)}.
     * For example, the amount in days between two date-times can be calculated
     * using {@code startDateTime.until(endDateTime, DAYS)}.
     * <p>
     * The calculation returns a whole number, representing the number of
     * complete units between the two date-times.
     * For example, the amount in months between 2012-06-15T00:00 and 2012-08-14T23:59
     * will only be one month as it is one minute short of two months.
     * <p>
     * There are two equivalent ways of using this method.
     * The first is to invoke this method.
     * The second is to use {@link TemporalUnit#between(Temporal, Temporal)}:
     * <pre>
     *   // these two lines are equivalent
     *   amount = start.until(end, MONTHS);
     *   amount = MONTHS.between(start, end);
     * </pre>
     * The choice should be made based on which makes the code more readable.
     * <p>
     * The calculation is implemented in this method for {@link ChronoUnit}.
     * The units {@code NANOS}, {@code MICROS}, {@code MILLIS}, {@code SECONDS},
     * {@code MINUTES}, {@code HOURS} and {@code HALF_DAYS}, {@code DAYS},
     * {@code WEEKS}, {@code MONTHS}, {@code YEARS}, {@code DECADES},
     * {@code CENTURIES}, {@code MILLENNIA} and {@code ERAS} are supported.
     * Other {@code ChronoUnit} values will throw an exception.
     * <p>
     * If the unit is not a {@code ChronoUnit}, then the result of this method
     * is obtained by invoking {@code TemporalUnit.between(Temporal, Temporal)}
     * passing {@code this} as the first argument and the converted input temporal
     * as the second argument.
     * <p>
     * This instance is immutable and unaffected by this method call.
     *
     * @param Temporal $endExclusive the end date, exclusive, which is converted to a {@code LocalDateTime}, not null
     * @param TemporalUnit $unit the unit to measure the amount in, not null
     * @return int the amount of time between this date-time and the end date-time
     * @throws DateTimeException if the amount cannot be calculated, or the end
     *  temporal cannot be converted to a {@code LocalDateTime}
     * @throws UnsupportedTemporalTypeException if the unit is not supported
     * @throws ArithmeticException if numeric overflow occurs
     */
    public function until(Temporal $endExclusive, TemporalUnit $unit)
    {
        $end = LocalDateTime::from($endExclusive);
        if ($unit instanceof ChronoUnit) {
            if ($unit->isTimeBased()) {
                $amount = $this->date->daysUntil($end->date);
                if ($amount === 0) {
                    return $this->time->until($end->time, $unit);
                }

                $timePart = $end->time->toNanoOfDay() - $this->time->toNanoOfDay();
                if ($amount > 0) {
                    $amount--;  // safe
                    $timePart += LocalTime::NANOS_PER_DAY;  // safe
                } else {
                    $amount++;  // safe
                    $timePart -= LocalTime::NANOS_PER_DAY;  // safe
                }
                switch ($unit) {
                    case ChronoUnit::NANOS():
                        $amount = Math::multiplyExact($amount, LocalTime::NANOS_PER_DAY);
                        break;
                    case ChronoUnit::MICROS():
                        $amount = Math::multiplyExact($amount, LocalTime::MICROS_PER_DAY);
                        $timePart = Math::div($timePart, 1000);
                        break;
                    case ChronoUnit::MILLIS():
                        $amount = Math::multiplyExact($amount, LocalTime::MILLIS_PER_DAY);
                        $timePart = Math::div($timePart, 1000000);
                        break;
                    case ChronoUnit::SECONDS():
                        $amount = Math::multiplyExact($amount, LocalTime::SECONDS_PER_DAY);
                        $timePart = Math::div($timePart, LocalTime::NANOS_PER_SECOND);
                        break;
                    case ChronoUnit::MINUTES():
                        $amount = Math::multiplyExact($amount, LocalTime::MINUTES_PER_DAY);
                        $timePart = Math::div($timePart, LocalTime::NANOS_PER_MINUTE);
                        break;
                    case ChronoUnit::HOURS():
                        $amount = Math::multiplyExact($amount, LocalTime::HOURS_PER_DAY);
                        $timePart = Math::div($timePart, LocalTime::NANOS_PER_HOUR);
                        break;
                    case ChronoUnit::HALF_DAYS():
                        $amount = Math::multiplyExact($amount, 2);
                        $timePart = Math::div($timePart, (LocalTime::NANOS_PER_HOUR * 12));
                        break;
                }
                return Math::addExact($amount, $timePart);
            }
            $endDate = $end->date;
            if ($endDate->isAfter($this->date) && $end->time->isBefore($this->time)) {
                $endDate = $endDate->minusDays(1);
            } else if ($endDate->isBefore($this->date) && $end->time->isAfter($this->time)) {
                $endDate = $endDate->plusDays(1);
            }
            return $this->date->until($endDate, $unit);
        }
        return $unit->between($this, $end);
    }

    /**
     * Formats this date-time using the specified formatter.
     * <p>
     * This date-time will be passed to the formatter to produce a string.
     *
     * @param DateTimeFormatter $formatter the formatter to use, not null
     * @return string the formatted date-time string, not null
     * @throws DateTimeException if an error occurs during printing
     */
    public function format(DateTimeFormatter $formatter)
    {
        return $formatter->format($this);
    }

//-----------------------------------------------------------------------
    /**
     * Combines this date-time with an offset to create an {@code OffsetDateTime}.
     * <p>
     * This returns an {@code OffsetDateTime} formed from this date-time at the specified offset.
     * All possible combinations of date-time and offset are valid.
     *
     * @param ZoneOffset $offset the offset to combine with, not null
     * @return OffsetDateTime the offset date-time formed from this date-time and the specified offset, not null
     */
    public function atOffset(ZoneOffset $offset)
    {
        return OffsetDateTime::ofDateTime($this, $offset);
    }

    /**
     * Combines this date-time with a time-zone to create a {@code ZonedDateTime}.
     * <p>
     * This returns a {@code ZonedDateTime} formed from this date-time at the
     * specified time-zone. The result will match this date-time as closely as possible.
     * Time-zone rules, such as daylight savings, mean that not every local date-time
     * is valid for the specified zone, thus the local date-time may be adjusted.
     * <p>
     * The local date-time is resolved to a single instant on the time-line.
     * This is achieved by finding a valid offset from UTC/Greenwich for the local
     * date-time as defined by the {@link ZoneRules rules} of the zone ID.
     *<p>
     * In most cases, there is only one valid offset for a local date-time.
     * In the case of an overlap, where clocks are set back, there are two valid offsets.
     * This method uses the earlier offset typically corresponding to "summer".
     * <p>
     * In the case of a gap, where clocks jump forward, there is no valid offset.
     * Instead, the local date-time is adjusted to be later by the length of the gap.
     * For a typical one hour daylight savings change, the local date-time will be
     * moved one hour later into the offset typically corresponding to "summer".
     * <p>
     * To obtain the later offset during an overlap, call
     * {@link ZonedDateTime#withLaterOffsetAtOverlap()} on the result of this method.
     * To throw an exception when there is a gap or overlap, use
     * {@link ZonedDateTime#ofStrict(LocalDateTime, ZoneOffset, ZoneId)}.
     *
     * @param ZoneId $zone the time-zone to use, not null
     * @return ZonedDateTime the zoned date-time formed from this date-time, not null
     */
    public function atZone(ZoneId $zone)
    {
        return ZonedDateTime::ofDateTime($this, $zone);
    }

//-----------------------------------------------------------------------
    /**
     * Compares this date-time to another date-time.
     * <p>
     * The comparison is primarily based on the date-time, from earliest to latest.
     * It is "consistent with equals", as defined by {@link Comparable}.
     * <p>
     * If all the date-times being compared are instances of {@code LocalDateTime},
     * then the comparison will be entirely based on the date-time.
     * If some dates being compared are in different chronologies, then the
     * chronology is also considered, see {@link ChronoLocalDateTime#compareTo}.
     *
     * @param ChronoLocalDateTime $other the other date-time to compare to, not null
     * @return int the comparator value, negative if less, positive if greater
     */
    public function compareTo(ChronoLocalDateTime $other)
    {
        if ($other instanceof LocalDateTime) {
            return $this->compareTo0($other);
        }
        return parent::compareTo($other);
    }

    private function compareTo0(LocalDateTime $other)
    {
        $cmp = $this->date->compareTo0($other->toLocalDate());
        if ($cmp === 0) {
            $cmp = $this->time->compareTo($other->toLocalTime());
        }
        return $cmp;
    }

    /**
     * Checks if this date-time is after the specified date-time.
     * <p>
     * This checks to see if this date-time represents a point on the
     * local time-line after the other date-time.
     *
     * <pre>
     *   LocalDate a = LocalDateTime.of(2012, 6, 30, 12, 00);
     *   LocalDate b = LocalDateTime.of(2012, 7, 1, 12, 00);
     *   a.isAfter(b) == false
     *   a.isAfter(a) == false
     *   b.isAfter(a) == true
     * </pre>
     * <p>
     * This method only considers the position of the two date-times on the local time-line.
     * It does not take into account the chronology, or calendar system.
     * This is different from the comparison in {@link #compareTo(ChronoLocalDateTime)},
     * but is the same approach as {@link ChronoLocalDateTime#timeLineOrder()}.
     *
     * @param ChronoLocalDateTime $other the other date-time to compare to, not null
     * @return bool true if this date-time is after the specified date-time
     */
    public function isAfter(ChronoLocalDateTime $other)
    {
        if ($other instanceof LocalDateTime) {
            return $this->compareTo0($other) > 0;
        }

        return parent::isAfter($other);
    }

    /**
     * Checks if this date-time is before the specified date-time.
     * <p>
     * This checks to see if this date-time represents a point on the
     * local time-line before the other date-time.
     * <pre>
     *   LocalDate a = LocalDateTime.of(2012, 6, 30, 12, 00);
     *   LocalDate b = LocalDateTime.of(2012, 7, 1, 12, 00);
     *   a.isBefore(b) == true
     *   a.isBefore(a) == false
     *   b.isBefore(a) == false
     * </pre>
     * <p>
     * This method only considers the position of the two date-times on the local time-line.
     * It does not take into account the chronology, or calendar system.
     * This is different from the comparison in {@link #compareTo(ChronoLocalDateTime)},
     * but is the same approach as {@link ChronoLocalDateTime#timeLineOrder()}.
     *
     * @param ChronoLocalDateTime $other the other date-time to compare to, not null
     * @return bool true if this date-time is before the specified date-time
     */
    public function isBefore(ChronoLocalDateTime $other)
    {
        if ($other instanceof LocalDateTime) {
            return $this->compareTo0($other) < 0;
        }
        return parent::isBefore($other);
    }

    /**
     * Checks if this date-time is equal to the specified date-time.
     * <p>
     * This checks to see if this date-time represents the same point on the
     * local time-line as the other date-time.
     *
     * <pre>
     *   LocalDate a = LocalDateTime.of(2012, 6, 30, 12, 00);
     *   LocalDate b = LocalDateTime.of(2012, 7, 1, 12, 00);
     *   a.isEqual(b) == false
     *   a.isEqual(a) == true
     *   b.isEqual(a) == false
     * </pre>
     * <p>
     * This method only considers the position of the two date-times on the local time-line.
     * It does not take into account the chronology, or calendar system.
     * This is different from the comparison in {@link #compareTo(ChronoLocalDateTime)},
     * but is the same approach as {@link ChronoLocalDateTime#timeLineOrder()}.
     *
     * @param ChronoLocalDateTime $other the other date-time to compare to, not null
     * @return bool true if this date-time is equal to the specified date-time
     */
    public function isEqual(ChronoLocalDateTime $other)
    {
        if ($other instanceof LocalDateTime) {
            return $this->compareTo0($other) === 0;
        }

        return parent::isEqual($other);
    }

    //-----------------------------------------------------------------------
    /**
     * Checks if this date-time is equal to another date-time.
     * <p>
     * Compares this {@code LocalDateTime} with another ensuring that the date-time is the same.
     * Only objects of type {@code LocalDateTime} are compared, other types return false.
     *
     * @param mixed $obj the object to check, null returns false
     * @return bool true if this is equal to the other date-time
     */
    public function equals($obj)
    {
        if ($this === $obj) {
            return true;
        }


        if ($obj instanceof LocalDateTime) {
            $other = $obj;
            return $this->date->equals($other->date) && $this->time->equals($other->time);
        }
        return false;
    }

//-----------------------------------------------------------------------
    /**
     * Outputs this date-time as a {@code String}, such as {@code 2007-12-03T10:15:30}.
     * <p>
     * The output will be one of the following ISO-8601 formats:
     * <ul>
     * <li>{@code uuuu-MM-dd'T'HH:mm}</li>
     * <li>{@code uuuu-MM-dd'T'HH:mm:ss}</li>
     * <li>{@code uuuu-MM-dd'T'HH:mm:ss.SSS}</li>
     * <li>{@code uuuu-MM-dd'T'HH:mm:ss.SSSSSS}</li>
     * <li>{@code uuuu-MM-dd'T'HH:mm:ss.SSSSSSSSS}</li>
     * </ul>
     * The format used will be the shortest that outputs the full value of
     * the time where the omitted parts are implied to be zero.
     *
     * @return string a string representation of this date-time, not null
     */
    public function __toString()
    {
        return $this->date->__toString() . 'T' . $this->time->__toString();
    }

    public function serialize()
    {
        return
            $this->date->getYear() . ':' .
            $this->date->getMonthValue() . ':' .
            $this->date->getDayOfMonth() . ':' .
            $this->time->getHour() . ':' .
            $this->time->getMinute() . ':' .
            $this->time->getSecond() . ':' .
            $this->time->getNano();
    }

    public function unserialize($serialized)
    {
        $v = explode(':', $serialized);
        $this->date = LocalDate::of((int) $v[0], (int) $v[1], (int) $v[2]);
        $this->time = LocalTime::of((int) $v[3], (int) $v[4], (int) $v[5]);
    }

    /**
     * Serialize into an ISO string for JSON representation
     * @see __toString()
     * @return string
     */
    public function jsonSerialize()
    {
        return $this->__toString();
    }
}

LocalDateTime::init();
