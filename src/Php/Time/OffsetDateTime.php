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

use Php\Time\Format\DateTimeFormatter;
use Php\Time\Temporal\ChronoField;
use Php\Time\Temporal\ChronoUnit;
use Php\Time\Temporal\Temporal;
use Php\Time\Temporal\TemporalAccessor;
use Php\Time\Temporal\TemporalAccessorDefaults;
use Php\Time\Temporal\TemporalAdjuster;
use Php\Time\Temporal\TemporalAmount;
use Php\Time\Temporal\TemporalField;
use Php\Time\Temporal\TemporalQuery;
use Php\Time\Temporal\TemporalUnit;

/**
 * A date-time with an offset from UTC/Greenwich in the ISO-8601 calendar system,
 * such as {@code 2007-12-03T10:15:30+01:00}.
 * <p>
 * {@code OffsetDateTime} is an immutable representation of a date-time with an offset.
 * This class stores all date and time fields, to a precision of nanoseconds,
 * as well as the offset from UTC/Greenwich. For example, the value
 * "2nd October 2007 at 13:45.30.123456789 +02:00" can be stored in an {@code OffsetDateTime}.
 * <p>
 * {@code OffsetDateTime}, {@link java.time.ZonedDateTime} and {@link java.time.Instant} all store an instant
 * on the time-line to nanosecond precision.
 * {@code Instant} is the simplest, simply representing the instant.
 * {@code OffsetDateTime} adds to the instant the offset from UTC/Greenwich, which allows
 * the local date-time to be obtained.
 * {@code ZonedDateTime} adds full time-zone rules.
 * <p>
 * It is intended that {@code ZonedDateTime} or {@code Instant} is used to model data
 * in simpler applications. This class may be used when modeling date-time concepts in
 * more detail, or when communicating to a database or in a network protocol.
 *
 * <p>
 * This is a <a href="{@docRoot}/java/lang/doc-files/ValueBased.html">value-based</a>
 * class; use of identity-sensitive operations (including reference equality
 * ({@code ==}), identity hash code, or synchronization) on instances of
 * {@code OffsetDateTime} may have unpredictable results and should be avoided.
 * The {@code equals} method should be used for comparisons.
 *
 * @implSpec
 * This class is immutable and thread-safe.
 *
 * @since 1.8
 */
final class OffsetDateTime implements Temporal, TemporalAdjuster
{

    public static function init()
    {
        self::$MIN = LocalDateTime::MIN()->atOffset(ZoneOffset::MAX());
        self::$MAX = LocalDateTime::MAX()->atOffset(ZoneOffset::MIN());
    }

    /**
     * The minimum supported {@code OffsetDateTime}, '-999999999-01-01T00:00:00+18:00'.
     * This is the local date-time of midnight at the start of the minimum date
     * in the maximum offset (larger offsets are earlier on the time-line).
     * This combines {@link LocalDateTime#MIN} and {@link ZoneOffset#MAX}.
     * This could be used by an application as a "far past" date-time.
     * @return OffsetDateTime
     */
    public function MIN()
    {
        return self::$MIN;
    }

    private static $MIN;

    /**
     * The maximum supported {@code OffsetDateTime}, '+999999999-12-31T23:59:59.999999999-18:00'.
     * This is the local date-time just before midnight at the end of the maximum date
     * in the minimum offset (larger negative offsets are later on the time-line).
     * This combines {@link LocalDateTime#MAX} and {@link ZoneOffset#MIN}.
     * This could be used by an application as a "far future" date-time.
     * @return OffsetDateTime
     */
    public function MAX()
    {
        return self::$MAX;
    }

    private static $MAX;

    /**
     * Gets a comparator that compares two {@code OffsetDateTime} instances
     * based solely on the instant.
     * <p>
     * This method differs from the comparison in {@link #compareTo} in that it
     * only compares the underlying instant.
     *
     * @return Comparator a comparator that compares in time-line order
     *
     * @see #isAfter
     * @see #isBefore
     * @see #isEqual
     */
    public static function timeLineOrder()
    {
        return OffsetDateTime::compareInstant;
    }

    /**
     * Compares this {@code OffsetDateTime} to another date-time.
     * The comparison is based on the instant.
     *
     * @param $datetime1 OffsetDateTime the first date-time to compare, not null
     * @param $datetime2 OffsetDateTime the other date-time to compare to, not null
     * @return int the comparator value, negative if less, positive if greater
     */
    private
    static function compareInstant(OffsetDateTime $datetime1, OffsetDateTime $datetime2)
    {
        if ($datetime1->getOffset()->equals($datetime2->getOffset())) {
            return $datetime1->toLocalDateTime()->compareTo($datetime2->toLocalDateTime());
        }
        $cmp = Long::compare($datetime1->toEpochSecond(), $datetime2->toEpochSecond());
        if ($cmp == 0) {
            $cmp = $datetime1->toLocalTime()->getNano() - $datetime2->toLocalTime()->getNano();
        }
        return $cmp;
    }

    /**
     * The local date-time.
     * @var LocalDateTime
     */
    private $dateTime;
    /**
     * The offset from UTC/Greenwich.
     * @var ZoneOffset
     */
    private $offset;

    //-----------------------------------------------------------------------
    /**
     * Obtains the current date-time from the system clock in the default time-zone.
     * <p>
     * This will query the {@link Clock#systemDefaultZone() system clock} in the default
     * time-zone to obtain the current date-time.
     * The offset will be calculated from the time-zone in the clock.
     * <p>
     * Using this method will prevent the ability to use an alternate clock for testing
     * because the clock is hard-coded.
     *
     * @return OffsetDateTime the current date-time using the system clock, not null
     */
    public static function now()
    {
        return self::now(Clock::systemDefaultZone());
    }

    /**
     * Obtains the current date-time from the system clock in the specified time-zone.
     * <p>
     * This will query the {@link Clock#system(ZoneId) system clock} to obtain the current date-time.
     * Specifying the time-zone avoids dependence on the default time-zone.
     * The offset will be calculated from the specified time-zone.
     * <p>
     * Using this method will prevent the ability to use an alternate clock for testing
     * because the clock is hard-coded.
     *
     * @param $zone ZoneId the zone ID to use, not null
     * @return OffsetDateTime the current date-time using the system clock, not null
     */
    public
    static function now(ZoneId $zone)
    {
        return self::now(Clock::system($zone));
    }

    /**
     * Obtains the current date-time from the specified clock.
     * <p>
     * This will query the specified clock to obtain the current date-time.
     * The offset will be calculated from the time-zone in the clock.
     * <p>
     * Using this method allows the use of an alternate clock for testing.
     * The alternate clock may be introduced using {@link Clock dependency injection}.
     *
     * @param $clock Clock the clock to use, not null
     * @return OffsetDateTime the current date-time, not null
     */
    public
    static function now($clock)
    {
        $now = $clock->instant();  // called once
        return self::ofInstant($now, $clock->getZone()->getRules()->getOffset($now));
    }

//-----------------------------------------------------------------------
    /**
     * Obtains an instance of {@code OffsetDateTime} from a date, time and offset.
     * <p>
     * This creates an offset date-time with the specified local date, time and offset.
     *
     * @param $date LocalDate the local date, not null
     * @param $time LocalTime the local time, not null
     * @param $offset ZoneOffset the zone offset, not null
     * @return OffsetDateTime the offset date-time, not null
     */
    public
    static function of(LocalDate $date, LocalTime $time, ZoneOffset $offset)
    {
        $dt = LocalDateTime::of($date, $time);
        return new OffsetDateTime($dt, $offset);
    }

    /**
     * Obtains an instance of {@code OffsetDateTime} from a date-time and offset.
     * <p>
     * This creates an offset date-time with the specified local date-time and offset.
     *
     * @param $dateTime LocalDateTime the local date-time, not null
     * @param $offset ZoneOffset the zone offset, not null
     * @return OffsetDateTime the offset date-time, not null
     */
    public
    static function of(LocalDateTime $dateTime, ZoneOffset $offset)
    {
        return new OffsetDateTime($dateTime, $offset);
    }

    /**
     * Obtains an instance of {@code OffsetDateTime} from a year, month, day,
     * hour, minute, second, nanosecond and offset.
     * <p>
     * This creates an offset date-time with the seven specified fields.
     * <p>
     * This method exists primarily for writing test cases.
     * Non test-code will typically use other methods to create an offset time.
     * {@code LocalDateTime} has five additional convenience variants of the
     * equivalent factory method taking fewer arguments.
     * They are not provided here to reduce the footprint of the API.
     *
     * @param $year int the year to represent, from MIN_YEAR to MAX_YEAR
     * @param $month int the month-of-year to represent, from 1 (January) to 12 (December)
     * @param $dayOfMonth int the day-of-month to represent, from 1 to 31
     * @param $hour int the hour-of-day to represent, from 0 to 23
     * @param $minute int the minute-of-hour to represent, from 0 to 59
     * @param $second int the second-of-minute to represent, from 0 to 59
     * @param $nanoOfSecond int the nano-of-second to represent, from 0 to 999,999,999
     * @param $offset ZoneOffset the zone offset, not null
     * @return OffsetDateTime the offset date-time, not null
     * @throws DateTimeException if the value of any field is out of range, or
     *  if the day-of-month is invalid for the month-year
     */
    public
    static function of($year, $month, $dayOfMonth,
                       $hour, $minute, $second, $nanoOfSecond, ZoneOffset $offset)
    {
        $dt = LocalDateTime::of($year, $month, $dayOfMonth, $hour, $minute, $second, $nanoOfSecond);
        return new OffsetDateTime($dt, $offset);
    }

//-----------------------------------------------------------------------
    /**
     * Obtains an instance of {@code OffsetDateTime} from an {@code Instant} and zone ID.
     * <p>
     * This creates an offset date-time with the same instant as that specified.
     * Finding the offset from UTC/Greenwich is simple as there is only one valid
     * offset for each instant.
     *
     * @param $instant Instant the instant to create the date-time from, not null
     * @param $zone ZoneId the time-zone, which may be an offset, not null
     * @return OffsetDateTime the offset date-time, not null
     * @throws DateTimeException if the result exceeds the supported range
     */
    public
    static function ofInstant(Instant $instant, ZoneId $zone)
    {
        $rules = $zone->getRules();
        $offset = $rules->getOffset($instant);
        $ldt = LocalDateTime::ofEpochSecond($instant->getEpochSecond(), $instant->getNano(), $offset);
        return new OffsetDateTime($ldt, $offset);
    }

//-----------------------------------------------------------------------
    /**
     * Obtains an instance of {@code OffsetDateTime} from a temporal object.
     * <p>
     * This obtains an offset date-time based on the specified temporal.
     * A {@code TemporalAccessor} represents an arbitrary set of date and time information,
     * which this factory converts to an instance of {@code OffsetDateTime}.
     * <p>
     * The conversion will first obtain a {@code ZoneOffset} from the temporal object.
     * It will then try to obtain a {@code LocalDateTime}, falling back to an {@code Instant} if necessary.
     * The result will be the combination of {@code ZoneOffset} with either
     * with {@code LocalDateTime} or {@code Instant}.
     * Implementations are permitted to perform optimizations such as accessing
     * those fields that are equivalent to the relevant objects.
     * <p>
     * This method matches the signature of the functional interface {@link TemporalQuery}
     * allowing it to be used as a query via method reference, {@code OffsetDateTime::from}.
     *
     * @param $temporal TemporalAccessor the temporal object to convert, not null
     * @return OffsetDateTime the offset date-time, not null
     * @throws DateTimeException if unable to convert to an {@code OffsetDateTime}
     */
    public
    static function from(TemporalAccessor $temporal)
    {
        if ($temporal instanceof OffsetDateTime) {
            return $temporal;
        }

        try {
            $offset = ZoneOffset::from($temporal);
            $date = $temporal->query(TemporalQueries::localDate());
            $time = $temporal->query(TemporalQueries::localTime());
            if ($date != null && $time != null) {
                return OffsetDateTime::of($date, $time, $offset);
            } else {
                $instant = Instant::from($temporal);
                return OffsetDateTime::ofInstant($instant, $offset);
            }
        } catch (DateTimeException $ex) {
            throw new DateTimeException("Unable to obtain OffsetDateTime from TemporalAccessor: " .
                $temporal . " of type " . get_class($temporal), $ex);
        }
    }

    //-----------------------------------------------------------------------
    /**
     * Obtains an instance of {@code OffsetDateTime} from a text string
     * such as {@code 2007-12-03T10:15:30+01:00}.
     * <p>
     * The string must represent a valid date-time and is parsed using
     * {@link java.time.format.DateTimeFormatter#ISO_OFFSET_DATE_TIME}.
     *
     * @param $text string the text to parse such as "2007-12-03T10:15:30+01:00", not null
     * @return OffsetDateTime the parsed offset date-time, not null
     * @throws DateTimeParseException if the text cannot be parsed
     */
    public static function parse($text)
    {
        return self::parse($text, DateTimeFormatter::ISO_OFFSET_DATE_TIME);
    }

    /**
     * Obtains an instance of {@code OffsetDateTime} from a text string using a specific formatter.
     * <p>
     * The text is parsed using the formatter, returning a date-time.
     *
     * @param $text string the text to parse, not null
     * @param $formatter DateTimeFormatter the formatter to use, not null
     * @return OffsetDateTime the parsed offset date-time, not null
     * @throws DateTimeParseException if the text cannot be parsed
     */
    public
    static function parse($text, DateTimeFormatter $formatter)
    {
        return $formatter->parse($text, OffsetDateTime::from);
    }

//-----------------------------------------------------------------------
    /**
     * Constructor.
     *
     * @param $dateTime LocalDateTime the local date-time, not null
     * @param $offset ZoneOffset the zone offset, not null
     */
    private
    function __construct(LocalDateTime $dateTime, ZoneOffset $offset)
    {
        $this->dateTime = $dateTime;
        $this->offset = $offset;
    }

    /**
     * Returns a new date-time based on this one, returning {@code this} where possible.
     *
     * @param $dateTime LocalDateTime the date-time to create with, not null
     * @param $offset ZoneOffset the zone offset to create with, not null
     * @return OffsetDateTime
     */
    private function with(LocalDateTime $dateTime, ZoneOffset $offset)
    {
        if ($this->dateTime == $dateTime && $this->offset->equals($offset)) {
            return $this;
        }

        return new OffsetDateTime($dateTime, $offset);
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
     * <li>{@code INSTANT_SECONDS}
     * <li>{@code OFFSET_SECONDS}
     * </ul>
     * All other {@code ChronoField} instances will return false.
     * <p>
     * If the field is not a {@code ChronoField}, then the result of this method
     * is obtained by invoking {@code TemporalField.isSupportedBy(TemporalAccessor)}
     * passing {@code this} as the argument.
     * Whether the field is supported is determined by the field.
     *
     * @param $field TemporalField the field to check, null returns false
     * @return bool true if the field is supported on this date-time, false if not
     */
    public function isSupported(TemporalField $field)
    {
        return $field instanceof ChronoField || ($field != null && $field->isSupportedBy($this));
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
     * @param $unit TemporalUnit  the unit to check, null returns false
     * @return bool if the unit can be added/subtracted, false if not
     */
    public function isSupported(TemporalUnit $unit)
    {
        if ($unit instanceof ChronoUnit) {
            return $unit != ChronoUnit::FOREVER();
        }

        return $unit != null && $unit->isSupportedBy($this);
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
     * @param $field TemporalField the field to query the range for, not null
     * @return ValueRange the range of valid values for the field, not null
     * @throws DateTimeException if the range for the field cannot be obtained
     * @throws UnsupportedTemporalTypeException if the field is not supported
     */
    public function range(TemporalField $field)
    {
        if ($field instanceof ChronoField) {
            if ($field == ChronoField::INSTANT_SECONDS() || $field == ChronoField::OFFSET_SECONDS()) {
                return $field->range();
            }

            return $this->dateTime->range($field);
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
     * {@code EPOCH_DAY}, {@code PROLEPTIC_MONTH} and {@code INSTANT_SECONDS} which are too
     * large to fit in an {@code int} and throw a {@code DateTimeException}.
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
            switch ($field) {
                case ChronoField::INSTANT_SECONDS():
                    throw new UnsupportedTemporalTypeException("Invalid field 'InstantSeconds' for get() method, use getLong() instead");
                case ChronoField::OFFSET_SECONDS():
                    return $this->getOffset()->getTotalSeconds();
            }

            return $this->dateTime->get($field);
        }
        return TemporalAccessorDefaults::get($this, $field);
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
     * @param $field TemporalField the field to get, not null
     * @return int the value for the field
     * @throws DateTimeException if a value for the field cannot be obtained
     * @throws UnsupportedTemporalTypeException if the field is not supported
     * @throws ArithmeticException if numeric overflow occurs
     */
    public function getLong(TemporalField $field)
    {
        if ($field instanceof ChronoField) {
            switch ($field) {
                case ChronoField::INSTANT_SECONDS():
                    return $this->toEpochSecond();
                case ChronoField::OFFSET_SECONDS():
                    return $this->getOffset()->getTotalSeconds();
            }

            return $this->dateTime->getLong($field);
        }
        return $field->getFrom($this);
    }

    //-----------------------------------------------------------------------
    /**
     * Gets the zone offset, such as '+01:00'.
     * <p>
     * This is the offset of the local date-time from UTC/Greenwich.
     *
     * @return ZoneOffset the zone offset, not null
     */
    public function getOffset()
    {
        return $this->offset;
    }

    /**
     * Returns a copy of this {@code OffsetDateTime} with the specified offset ensuring
     * that the result has the same local date-time.
     * <p>
     * This method returns an object with the same {@code LocalDateTime} and the specified {@code ZoneOffset}.
     * No calculation is needed or performed.
     * For example, if this time represents {@code 2007-12-03T10:30+02:00} and the offset specified is
     * {@code +03:00}, then this method will return {@code 2007-12-03T10:30+03:00}.
     * <p>
     * To take into account the difference between the offsets, and adjust the time fields,
     * use {@link #withOffsetSameInstant}.
     * <p>
     * This instance is immutable and unaffected by this method call.
     *
     * @param $offset ZoneOffset the zone offset to change to, not null
     * @return OffsetDateTime an {@code OffsetDateTime} based on this date-time with the requested offset, not null
     */
    public function withOffsetSameLocal(ZoneOffset $offset)
    {
        return $this->with($this->dateTime, $offset);
    }

    /**
     * Returns a copy of this {@code OffsetDateTime} with the specified offset ensuring
     * that the result is at the same instant.
     * <p>
     * This method returns an object with the specified {@code ZoneOffset} and a {@code LocalDateTime}
     * adjusted by the difference between the two offsets.
     * This will result in the old and new objects representing the same instant.
     * This is useful for finding the local time in a different offset.
     * For example, if this time represents {@code 2007-12-03T10:30+02:00} and the offset specified is
     * {@code +03:00}, then this method will return {@code 2007-12-03T11:30+03:00}.
     * <p>
     * To change the offset without adjusting the local time use {@link #withOffsetSameLocal}.
     * <p>
     * This instance is immutable and unaffected by this method call.
     *
     * @param $offset ZoneOffset the zone offset to change to, not null
     * @return OffsetDateTime an {@code OffsetDateTime} based on this date-time with the requested offset, not null
     * @throws DateTimeException if the result exceeds the supported date range
     */
    public function withOffsetSameInstant(ZoneOffset $offset)
    {
        if ($offset->equals($this->offset)) {
            return $this;
        }

        $difference = $offset->getTotalSeconds() - $this->offset->getTotalSeconds();
        $adjusted = $this->dateTime->plusSeconds($difference);
        return new OffsetDateTime($adjusted, $offset);
    }

//-----------------------------------------------------------------------
    /**
     * Gets the {@code LocalDateTime} part of this date-time.
     * <p>
     * This returns a {@code LocalDateTime} with the same year, month, day and time
     * as this date-time.
     *
     * @return LocalDateTime the local date-time part of this date-time, not null
     */
    public function toLocalDateTime()
    {
        return $this->dateTime;
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
    public
    function toLocalDate()
    {
        return $this->dateTime->toLocalDate();
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
    public
    function getYear()
    {
        return $this->dateTime->getYear();
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
        return $this->dateTime->getMonthValue();
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
        return $this->dateTime->getMonth();
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
        return $this->dateTime->getDayOfMonth();
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
        return $this->dateTime->getDayOfYear();
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
        return $this->dateTime->getDayOfWeek();
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
        return $this->dateTime->toLocalTime();
    }

    /**
     * Gets the hour-of-day field.
     *
     * @return int the hour-of-day, from 0 to 23
     */
    public function getHour()
    {
        return $this->dateTime->getHour();
    }

    /**
     * Gets the minute-of-hour field.
     *
     * @return int the minute-of-hour, from 0 to 59
     */
    public function getMinute()
    {
        return $this->dateTime->getMinute();
    }

    /**
     * Gets the second-of-minute field.
     *
     * @return int the second-of-minute, from 0 to 59
     */
    public function getSecond()
    {
        return $this->dateTime->getSecond();
    }

    /**
     * Gets the nano-of-second field.
     *
     * @return int the nano-of-second, from 0 to 999,999,999
     */
    public function getNano()
    {
        return $this->dateTime->getNano();
    }

    //-----------------------------------------------------------------------
    /**
     * Returns an adjusted copy of this date-time.
     * <p>
     * This returns an {@code OffsetDateTime}, based on this one, with the date-time adjusted.
     * The adjustment takes place using the specified adjuster strategy object.
     * Read the documentation of the adjuster to understand what adjustment will be made.
     * <p>
     * A simple adjuster might simply set the one of the fields, such as the year field.
     * A more complex adjuster might set the date to the last day of the month.
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
     *  result = offsetDateTime.with(JULY).with(lastDayOfMonth());
     * </pre>
     * <p>
     * The classes {@link LocalDate}, {@link LocalTime} and {@link ZoneOffset} implement
     * {@code TemporalAdjuster}, thus this method can be used to change the date, time or offset:
     * <pre>
     *  result = offsetDateTime.with(date);
     *  result = offsetDateTime.with(time);
     *  result = offsetDateTime.with(offset);
     * </pre>
     * <p>
     * The result of this method is obtained by invoking the
     * {@link TemporalAdjuster#adjustInto(Temporal)} method on the
     * specified adjuster passing {@code this} as the argument.
     * <p>
     * This instance is immutable and unaffected by this method call.
     *
     * @param $adjuster TemporalAdjuster the adjuster to use, not null
     * @return OffsetDateTime an {@code OffsetDateTime} based on {@code this} with the adjustment made, not null
     * @throws DateTimeException if the adjustment cannot be made
     * @throws ArithmeticException if numeric overflow occurs
     */
    public function with(TemporalAdjuster $adjuster)
    {
        // optimizations
        if ($adjuster instanceof LocalDate || $adjuster instanceof LocalTime || $adjuster instanceof LocalDateTime) {
            return $this->with($this->dateTime->with($adjuster), $this->offset);
        } else
            if ($adjuster instanceof Instant) {
                return $this->ofInstant($adjuster, $this->offset);
            } else if ($adjuster instanceof ZoneOffset) {
                return $this->with($this->dateTime, $adjuster);
            } else if ($adjuster instanceof OffsetDateTime) {
                return $adjuster;
            }
        return $adjuster->adjustInto($this);
    }

    /**
     * Returns a copy of this date-time with the specified field set to a new value.
     * <p>
     * This returns an {@code OffsetDateTime}, based on this one, with the value
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
     * <p>
     * The {@code INSTANT_SECONDS} field will return a date-time with the specified instant.
     * The offset and nano-of-second are unchanged.
     * If the new instant value is outside the valid range then a {@code DateTimeException} will be thrown.
     * <p>
     * The {@code OFFSET_SECONDS} field will return a date-time with the specified offset.
     * The local date-time is unaltered. If the new offset value is outside the valid range
     * then a {@code DateTimeException} will be thrown.
     * <p>
     * The other {@link #isSupported(TemporalField) supported fields} will behave as per
     * the matching method on {@link LocalDateTime#with(TemporalField, long) LocalDateTime}.
     * In this case, the offset is not part of the calculation and will be unchanged.
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
     * @return OffsetDateTime an {@code OffsetDateTime} based on {@code this} with the specified field set, not null
     * @throws DateTimeException if the field cannot be set
     * @throws UnsupportedTemporalTypeException if the field is not supported
     * @throws ArithmeticException if numeric overflow occurs
     */
    public function with(TemporalField $field, $newValue)
    {
        if ($field instanceof ChronoField) {
            $f = $field;
            switch ($f) {
                case ChronoField::INSTANT_SECONDS():
                    return $this->ofInstant(Instant::ofEpochSecond($newValue, $this->getNano()), $this->offset);
                case ChronoField::OFFSET_SECONDS(): {
                    return $this->with($this->dateTime, ZoneOffset::ofTotalSeconds($f->checkValidIntValue($newValue)));
                }
            }
            return $this->with($this->dateTime->with($field, $newValue), $this->offset);
        }
        return $field->adjustInto($this, $newValue);
    }

    //-----------------------------------------------------------------------
    /**
     * Returns a copy of this {@code OffsetDateTime} with the year altered.
     * <p>
     * The time and offset do not affect the calculation and will be the same in the result.
     * If the day-of-month is invalid for the year, it will be changed to the last valid day of the month.
     * <p>
     * This instance is immutable and unaffected by this method call.
     *
     * @param $year int the year to set in the result, from MIN_YEAR to MAX_YEAR
     * @return OffsetDateTime an {@code OffsetDateTime} based on this date-time with the requested year, not null
     * @throws DateTimeException if the year value is invalid
     */
    public function withYear($year)
    {
        return $this->with($this->dateTime->withYear($year), $this->offset);
    }

    /**
     * Returns a copy of this {@code OffsetDateTime} with the month-of-year altered.
     * <p>
     * The time and offset do not affect the calculation and will be the same in the result.
     * If the day-of-month is invalid for the year, it will be changed to the last valid day of the month.
     * <p>
     * This instance is immutable and unaffected by this method call.
     *
     * @param $month int the month-of-year to set in the result, from 1 (January) to 12 (December)
     * @return OffsetDateTime an {@code OffsetDateTime} based on this date-time with the requested month, not null
     * @throws DateTimeException if the month-of-year value is invalid
     */
    public
    function withMonth($month)
    {
        return $this->with($this->dateTime->withMonth($month), $this->offset);
    }

    /**
     * Returns a copy of this {@code OffsetDateTime} with the day-of-month altered.
     * <p>
     * If the resulting {@code OffsetDateTime} is invalid, an exception is thrown.
     * The time and offset do not affect the calculation and will be the same in the result.
     * <p>
     * This instance is immutable and unaffected by this method call.
     *
     * @param $dayOfMonth int the day-of-month to set in the result, from 1 to 28-31
     * @return OffsetDateTime an {@code OffsetDateTime} based on this date-time with the requested day, not null
     * @throws DateTimeException if the day-of-month value is invalid,
     *  or if the day-of-month is invalid for the month-year
     */
    public
    function withDayOfMonth($dayOfMonth)
    {
        return $this->with($this->dateTime->withDayOfMonth($dayOfMonth), $this->offset);
    }

    /**
     * Returns a copy of this {@code OffsetDateTime} with the day-of-year altered.
     * <p>
     * The time and offset do not affect the calculation and will be the same in the result.
     * If the resulting {@code OffsetDateTime} is invalid, an exception is thrown.
     * <p>
     * This instance is immutable and unaffected by this method call.
     *
     * @param $dayOfYear int the day-of-year to set in the result, from 1 to 365-366
     * @return OffsetDateTime an {@code OffsetDateTime} based on this date with the requested day, not null
     * @throws DateTimeException if the day-of-year value is invalid,
     *  or if the day-of-year is invalid for the year
     */
    public
    function withDayOfYear($dayOfYear)
    {
        return $this->with($this->dateTime->withDayOfYear($dayOfYear), $this->offset);
    }

//-----------------------------------------------------------------------
    /**
     * Returns a copy of this {@code OffsetDateTime} with the hour-of-day altered.
     * <p>
     * The date and offset do not affect the calculation and will be the same in the result.
     * <p>
     * This instance is immutable and unaffected by this method call.
     *
     * @param $hour int the hour-of-day to set in the result, from 0 to 23
     * @return OffsetDateTime an {@code OffsetDateTime} based on this date-time with the requested hour, not null
     * @throws DateTimeException if the hour value is invalid
     */
    public
    function withHour($hour)
    {
        return $this->with($this->dateTime->withHour($hour), $this->offset);
    }

    /**
     * Returns a copy of this {@code OffsetDateTime} with the minute-of-hour altered.
     * <p>
     * The date and offset do not affect the calculation and will be the same in the result.
     * <p>
     * This instance is immutable and unaffected by this method call.
     *
     * @param $minute int the minute-of-hour to set in the result, from 0 to 59
     * @return OffsetDateTime an {@code OffsetDateTime} based on this date-time with the requested minute, not null
     * @throws DateTimeException if the minute value is invalid
     */
    public
    function withMinute($minute)
    {
        return $this->with($this->dateTime->withMinute($minute), $this->offset);
    }

    /**
     * Returns a copy of this {@code OffsetDateTime} with the second-of-minute altered.
     * <p>
     * The date and offset do not affect the calculation and will be the same in the result.
     * <p>
     * This instance is immutable and unaffected by this method call.
     *
     * @param $second int the second-of-minute to set in the result, from 0 to 59
     * @return OffsetDateTime an {@code OffsetDateTime} based on this date-time with the requested second, not null
     * @throws DateTimeException if the second value is invalid
     */
    public
    function withSecond($second)
    {
        return $this->with($this->dateTime->withSecond($second), $this->offset);
    }

    /**
     * Returns a copy of this {@code OffsetDateTime} with the nano-of-second altered.
     * <p>
     * The date and offset do not affect the calculation and will be the same in the result.
     * <p>
     * This instance is immutable and unaffected by this method call.
     *
     * @param $nanoOfSecond int the nano-of-second to set in the result, from 0 to 999,999,999
     * @return OffsetDateTime an {@code OffsetDateTime} based on this date-time with the requested nanosecond, not null
     * @throws DateTimeException if the nano value is invalid
     */
    public
    function withNano($nanoOfSecond)
    {
        return $this->with($this->dateTime->withNano($nanoOfSecond), $this->offset);
    }

//-----------------------------------------------------------------------
    /**
     * Returns a copy of this {@code OffsetDateTime} with the time truncated.
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
     * The offset does not affect the calculation and will be the same in the result.
     * <p>
     * This instance is immutable and unaffected by this method call.
     *
     * @param $unit TemporalUnit the unit to truncate to, not null
     * @return OffsetDateTime an {@code OffsetDateTime} based on this date-time with the time truncated, not null
     * @throws DateTimeException if unable to truncate
     * @throws UnsupportedTemporalTypeException if the unit is not supported
     */
    public
    function truncatedTo(TemporalUnit $unit)
    {
        return $this->with($this->dateTime->truncatedTo($unit), $this->offset);
    }

    //-----------------------------------------------------------------------
    /**
     * Returns a copy of this date-time with the specified amount added.
     * <p>
     * This returns an {@code OffsetDateTime}, based on this one, with the specified amount added.
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
     * @param $amountToAdd TemporalAmount the amount to add, not null
     * @return OffsetDateTime an {@code OffsetDateTime} based on this date-time with the addition made, not null
     * @throws DateTimeException if the addition cannot be made
     * @throws ArithmeticException if numeric overflow occurs
     */
    public function plus(TemporalAmount $amountToAdd)
    {
        return $amountToAdd->addTo($this);
    }

    /**
     * Returns a copy of this date-time with the specified amount added.
     * <p>
     * This returns an {@code OffsetDateTime}, based on this one, with the amount
     * in terms of the unit added. If it is not possible to add the amount, because the
     * unit is not supported or for some other reason, an exception is thrown.
     * <p>
     * If the field is a {@link ChronoUnit} then the addition is implemented by
     * {@link LocalDateTime#plus(long, TemporalUnit)}.
     * The offset is not part of the calculation and will be unchanged in the result.
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
     * @return OffsetDateTime an {@code OffsetDateTime} based on this date-time with the specified amount added, not null
     * @throws DateTimeException if the addition cannot be made
     * @throws UnsupportedTemporalTypeException if the unit is not supported
     * @throws ArithmeticException if numeric overflow occurs
     */
    public function plus($amountToAdd, TemporalUnit $unit)
    {
        if ($unit instanceof ChronoUnit) {
            return $this->with($this->dateTime->plus($amountToAdd, $unit), $this->offset);
        }
        return $unit->addTo($this, $amountToAdd);
    }

    //-----------------------------------------------------------------------
    /**
     * Returns a copy of this {@code OffsetDateTime} with the specified number of years added.
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
     * @param $years int the years to add, may be negative
     * @return OffsetDateTime an {@code OffsetDateTime} based on this date-time with the years added, not null
     * @throws DateTimeException if the result exceeds the supported date range
     */
    public function plusYears($years)
    {
        return $this->with($this->dateTime->plusYears($years), $this->offset);
    }

    /**
     * Returns a copy of this {@code OffsetDateTime} with the specified number of months added.
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
     * @param $months int the months to add, may be negative
     * @return OffsetDateTime an {@code OffsetDateTime} based on this date-time with the months added, not null
     * @throws DateTimeException if the result exceeds the supported date range
     */
    public function plusMonths($months)
    {
        return $this->with($this->dateTime->plusMonths($months), $this->offset);
    }

    /**
     * Returns a copy of this OffsetDateTime with the specified number of weeks added.
     * <p>
     * This method adds the specified amount in weeks to the days field incrementing
     * the month and year fields as necessary to ensure the result remains valid.
     * The result is only invalid if the maximum/minimum year is exceeded.
     * <p>
     * For example, 2008-12-31 plus one week would result in 2009-01-07.
     * <p>
     * This instance is immutable and unaffected by this method call.
     *
     * @param $weeks int the weeks to add, may be negative
     * @return OffsetDateTime an {@code OffsetDateTime} based on this date-time with the weeks added, not null
     * @throws DateTimeException if the result exceeds the supported date range
     */
    public function plusWeeks($weeks)
    {
        return $this->with($this->dateTime->plusWeeks($weeks), $this->offset);
    }

    /**
     * Returns a copy of this OffsetDateTime with the specified number of days added.
     * <p>
     * This method adds the specified amount to the days field incrementing the
     * month and year fields as necessary to ensure the result remains valid.
     * The result is only invalid if the maximum/minimum year is exceeded.
     * <p>
     * For example, 2008-12-31 plus one day would result in 2009-01-01.
     * <p>
     * This instance is immutable and unaffected by this method call.
     *
     * @param $days int the days to add, may be negative
     * @return OffsetDateTime an {@code OffsetDateTime} based on this date-time with the days added, not null
     * @throws DateTimeException if the result exceeds the supported date range
     */
    public function plusDays($days)
    {
        return $this->with($this->dateTime->plusDays($days), $this->offset);
    }

    /**
     * Returns a copy of this {@code OffsetDateTime} with the specified number of hours added.
     * <p>
     * This instance is immutable and unaffected by this method call.
     *
     * @param $hours int the hours to add, may be negative
     * @return OffsetDateTime an {@code OffsetDateTime} based on this date-time with the hours added, not null
     * @throws DateTimeException if the result exceeds the supported date range
     */
    public function plusHours($hours)
    {
        return $this->with($this->dateTime->plusHours($hours), $this->offset);
    }

    /**
     * Returns a copy of this {@code OffsetDateTime} with the specified number of minutes added.
     * <p>
     * This instance is immutable and unaffected by this method call.
     *
     * @param $minutes int the minutes to add, may be negative
     * @return OffsetDateTime an {@code OffsetDateTime} based on this date-time with the minutes added, not null
     * @throws DateTimeException if the result exceeds the supported date range
     */
    public function plusMinutes($minutes)
    {
        return $this->with($this->dateTime->plusMinutes($minutes), $this->offset);
    }

    /**
     * Returns a copy of this {@code OffsetDateTime} with the specified number of seconds added.
     * <p>
     * This instance is immutable and unaffected by this method call.
     *
     * @param $seconds int the seconds to add, may be negative
     * @return OffsetDateTime an {@code OffsetDateTime} based on this date-time with the seconds added, not null
     * @throws DateTimeException if the result exceeds the supported date range
     */
    public function plusSeconds($seconds)
    {
        return $this->with($this->dateTime->plusSeconds($seconds), $this->offset);
    }

    /**
     * Returns a copy of this {@code OffsetDateTime} with the specified number of nanoseconds added.
     * <p>
     * This instance is immutable and unaffected by this method call.
     *
     * @param $nanos int the nanos to add, may be negative
     * @return OffsetDateTime an {@code OffsetDateTime} based on this date-time with the nanoseconds added, not null
     * @throws DateTimeException if the unit cannot be added to this type
     */
    public function plusNanos($nanos)
    {
        return $this->with($this->dateTime->plusNanos($nanos), $this->offset);
    }

    //-----------------------------------------------------------------------
    /**
     * Returns a copy of this date-time with the specified amount subtracted.
     * <p>
     * This returns an {@code OffsetDateTime}, based on this one, with the specified amount subtracted.
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
     * @param $amountToSubtract TemporalAmount the amount to subtract, not null
     * @return OffsetDateTime an {@code OffsetDateTime} based on this date-time with the subtraction made, not null
     * @throws DateTimeException if the subtraction cannot be made
     * @throws ArithmeticException if numeric overflow occurs
     */
    public function minus(TemporalAmount $amountToSubtract)
    {
        return $amountToSubtract->subtractFrom($this);
    }

    /**
     * Returns a copy of this date-time with the specified amount subtracted.
     * <p>
     * This returns an {@code OffsetDateTime}, based on this one, with the amount
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
     * @return OffsetDateTime an {@code OffsetDateTime} based on this date-time with the specified amount subtracted, not null
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
     * Returns a copy of this {@code OffsetDateTime} with the specified number of years subtracted.
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
     * @param $years int the years to subtract, may be negative
     * @return OffsetDateTime an {@code OffsetDateTime} based on this date-time with the years subtracted, not null
     * @throws DateTimeException if the result exceeds the supported date range
     */
    public
    function minusYears($years)
    {
        return ($years == Long::MIN_VALUE ? $this->plusYears(Long::MAX_VALUE)->plusYears(1) : $this->plusYears(-$years));
    }

    /**
     * Returns a copy of this {@code OffsetDateTime} with the specified number of months subtracted.
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
     * @param $months int the months to subtract, may be negative
     * @return OffsetDateTime an {@code OffsetDateTime} based on this date-time with the months subtracted, not null
     * @throws DateTimeException if the result exceeds the supported date range
     */
    public
    function minusMonths($months)
    {
        return ($months == Long::MIN_VALUE ? $this->plusMonths(Long::MAX_VALUE)->plusMonths(1) : $this->plusMonths(-$months));
    }

    /**
     * Returns a copy of this {@code OffsetDateTime} with the specified number of weeks subtracted.
     * <p>
     * This method subtracts the specified amount in weeks from the days field decrementing
     * the month and year fields as necessary to ensure the result remains valid.
     * The result is only invalid if the maximum/minimum year is exceeded.
     * <p>
     * For example, 2008-12-31 minus one week would result in 2009-01-07.
     * <p>
     * This instance is immutable and unaffected by this method call.
     *
     * @param $weeks int the weeks to subtract, may be negative
     * @return OffsetDateTime an {@code OffsetDateTime} based on this date-time with the weeks subtracted, not null
     * @throws DateTimeException if the result exceeds the supported date range
     */
    public
    function minusWeeks($weeks)
    {
        return ($weeks == Long::MIN_VALUE ? $this->plusWeeks(Long::MAX_VALUE)->plusWeeks(1) : $this->plusWeeks(-$weeks));
    }

    /**
     * Returns a copy of this {@code OffsetDateTime} with the specified number of days subtracted.
     * <p>
     * This method subtracts the specified amount from the days field decrementing the
     * month and year fields as necessary to ensure the result remains valid.
     * The result is only invalid if the maximum/minimum year is exceeded.
     * <p>
     * For example, 2008-12-31 minus one day would result in 2009-01-01.
     * <p>
     * This instance is immutable and unaffected by this method call.
     *
     * @param $days int the days to subtract, may be negative
     * @return OffsetDateTime an {@code OffsetDateTime} based on this date-time with the days subtracted, not null
     * @throws DateTimeException if the result exceeds the supported date range
     */
    public
    function minusDays($days)
    {
        return ($days == Long::MIN_VALUE ? $this->plusDays(Long::MAX_VALUE)->plusDays(1) : $this->plusDays(-$days));
    }

    /**
     * Returns a copy of this {@code OffsetDateTime} with the specified number of hours subtracted.
     * <p>
     * This instance is immutable and unaffected by this method call.
     *
     * @param $hours int the hours to subtract, may be negative
     * @return OffsetDateTime an {@code OffsetDateTime} based on this date-time with the hours subtracted, not null
     * @throws DateTimeException if the result exceeds the supported date range
     */
    public
    function minusHours($hours)
    {
        return ($hours == Long::MIN_VALUE ? $this->plusHours(Long::MAX_VALUE)->plusHours(1) : $this->plusHours(-$hours));
    }

    /**
     * Returns a copy of this {@code OffsetDateTime} with the specified number of minutes subtracted.
     * <p>
     * This instance is immutable and unaffected by this method call.
     *
     * @param $minutes int the minutes to subtract, may be negative
     * @return OffsetDateTime an {@code OffsetDateTime} based on this date-time with the minutes subtracted, not null
     * @throws DateTimeException if the result exceeds the supported date range
     */
    public function minusMinutes($minutes)
    {
        return ($minutes == Long::MIN_VALUE ? $this->plusMinutes(Long::MAX_VALUE)->plusMinutes(1) : $this->plusMinutes(-$minutes));
    }

    /**
     * Returns a copy of this {@code OffsetDateTime} with the specified number of seconds subtracted.
     * <p>
     * This instance is immutable and unaffected by this method call.
     *
     * @param $seconds int the seconds to subtract, may be negative
     * @return OffsetDateTime an {@code OffsetDateTime} based on this date-time with the seconds subtracted, not null
     * @throws DateTimeException if the result exceeds the supported date range
     */
    public
    function minusSeconds($seconds)
    {
        return ($seconds == Long::MIN_VALUE ? $this->plusSeconds(Long::MAX_VALUE)->plusSeconds(1) : $this->plusSeconds(-$seconds));
    }

    /**
     * Returns a copy of this {@code OffsetDateTime} with the specified number of nanoseconds subtracted.
     * <p>
     * This instance is immutable and unaffected by this method call.
     *
     * @param $nanos  the nanos to subtract, may be negative
     * @return OffsetDateTime an {@code OffsetDateTime} based on this date-time with the nanoseconds subtracted, not null
     * @throws DateTimeException if the result exceeds the supported date range
     */
    public
    function minusNanos($nanos)
    {
        return ($nanos == Long::MIN_VALUE ? $this->plusNanos(Long::MAX_VALUE)->plusNanos(1) : $this->plusNanos(-$nanos));
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
     * @param $query TemporalQuery the query to invoke, not null
     * @return mixed the query result, null may be returned (defined by the query)
     * @throws DateTimeException if unable to query (defined by the query)
     * @throws ArithmeticException if numeric overflow occurs (defined by the query)
     */
    public function query(TemporalQuery $query)
    {
        if ($query == TemporalQueries::offset() || $query == TemporalQueries::zone()) {
            return $this->getOffset();
        } else
            if ($query == TemporalQueries::zoneId()) {
                return null;
            } else if ($query == TemporalQueries::localDate()) {
                return $this->toLocalDate();
            } else if ($query == TemporalQueries::localTime()) {
                return $this->toLocalTime();
            } else if ($query == TemporalQueries::chronology()) {
                return IsoChronology->INSTANCE;
        } else if ($query == TemporalQueries::precision()) {
                return ChronoUnit::NANOS();
            }
        // inline TemporalAccessor.super.query(query) as an optimization
        // non-JDK classes are not permitted to make this optimization
        return $query->queryFrom($this);
    }

    /**
     * Adjusts the specified temporal object to have the same offset, date
     * and time as this object.
     * <p>
     * This returns a temporal object of the same observable type as the input
     * with the offset, date and time changed to be the same as this.
     * <p>
     * The adjustment is equivalent to using {@link Temporal#with(TemporalField, long)}
     * three times, passing {@link ChronoField#EPOCH_DAY},
     * {@link ChronoField#NANO_OF_DAY} and {@link ChronoField#OFFSET_SECONDS} as the fields.
     * <p>
     * In most cases, it is clearer to reverse the calling pattern by using
     * {@link Temporal#with(TemporalAdjuster)}:
     * <pre>
     *   // these two lines are equivalent, but the second approach is recommended
     *   temporal = thisOffsetDateTime.adjustInto(temporal);
     *   temporal = temporal.with(thisOffsetDateTime);
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
        // OffsetDateTime is treated as three separate fields, not an instant
        // this produces the most consistent set of results overall
        // the offset is set after the date and time, as it is typically a small
        // tweak to the result, with ZonedDateTime frequently ignoring the offset
        return $temporal
            ->with(ChronoField::EPOCH_DAY(), $this->toLocalDate()->toEpochDay())
            ->with(ChronoField::NANO_OF_DAY(), $this->toLocalTime()->toNanoOfDay())
            ->with(ChronoField::OFFSET_SECONDS(), $this->getOffset()->getTotalSeconds());
    }

    /**
     * Calculates the amount of time until another date-time in terms of the specified unit.
     * <p>
     * This calculates the amount of time between two {@code OffsetDateTime}
     * objects in terms of a single {@code TemporalUnit}.
     * The start and end points are {@code this} and the specified date-time.
     * The result will be negative if the end is before the start.
     * For example, the amount in days between two date-times can be calculated
     * using {@code startDateTime.until(endDateTime, DAYS)}.
     * <p>
     * The {@code Temporal} passed to this method is converted to a
     * {@code OffsetDateTime} using {@link #from(TemporalAccessor)}.
     * If the offset differs between the two date-times, the specified
     * end date-time is normalized to have the same offset as this date-time.
     * <p>
     * The calculation returns a whole number, representing the number of
     * complete units between the two date-times.
     * For example, the amount in months between 2012-06-15T00:00Z and 2012-08-14T23:59Z
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
     * @param $endExclusive Temporal the end date, exclusive, which is converted to an {@code OffsetDateTime}, not null
     * @param $unit TemporalUnit the unit to measure the amount in, not null
     * @return int the amount of time between this date-time and the end date-time
     * @throws DateTimeException if the amount cannot be calculated, or the end
     *  temporal cannot be converted to an {@code OffsetDateTime}
     * @throws UnsupportedTemporalTypeException if the unit is not supported
     * @throws ArithmeticException if numeric overflow occurs
     */
    public function until(Temporal $endExclusive, TemporalUnit $unit)
    {
        $end = OffsetDateTime::from($endExclusive);
        if ($unit instanceof ChronoUnit) {
            $end = $end->withOffsetSameInstant($this->offset);
            return $this->dateTime->until($end->dateTime, $unit);
        }

        return $unit->between($this, $end);
    }

    /**
     * Formats this date-time using the specified formatter.
     * <p>
     * This date-time will be passed to the formatter to produce a string.
     *
     * @param $formatter DateTimeFormatter the formatter to use, not null
     * @return string the formatted date-time string, not null
     * @throws DateTimeException if an error occurs during printing
     */
    public function format(DateTimeFormatter $formatter)
    {
        return $formatter->format($this);
    }

//-----------------------------------------------------------------------
    /**
     * Combines this date-time with a time-zone to create a {@code ZonedDateTime}
     * ensuring that the result has the same instant.
     * <p>
     * This returns a {@code ZonedDateTime} formed from this date-time and the specified time-zone.
     * This conversion will ignore the visible local date-time and use the underlying instant instead.
     * This avoids any problems with local time-line gaps or overlaps.
     * The result might have different values for fields such as hour, minute an even day.
     * <p>
     * To attempt to retain the values of the fields, use {@link #atZoneSimilarLocal(ZoneId)}.
     * To use the offset as the zone ID, use {@link #toZonedDateTime()}.
     *
     * @param $zone ZoneId the time-zone to use, not null
     * @return ZonedDateTime the zoned date-time formed from this date-time, not null
     */
    public function atZoneSameInstant(ZoneId $zone)
    {
        return ZonedDateTime::ofInstant($this->dateTime, $this->offset, $zone);
    }

    /**
     * Combines this date-time with a time-zone to create a {@code ZonedDateTime}
     * trying to keep the same local date and time.
     * <p>
     * This returns a {@code ZonedDateTime} formed from this date-time and the specified time-zone.
     * Where possible, the result will have the same local date-time as this object.
     * <p>
     * Time-zone rules, such as daylight savings, mean that not every time on the
     * local time-line exists. If the local date-time is in a gap or overlap according to
     * the rules then a resolver is used to determine the resultant local time and offset.
     * This method uses {@link ZonedDateTime#ofLocal(LocalDateTime, ZoneId, ZoneOffset)}
     * to retain the offset from this instance if possible.
     * <p>
     * Finer control over gaps and overlaps is available in two ways.
     * If you simply want to use the later offset at overlaps then call
     * {@link ZonedDateTime#withLaterOffsetAtOverlap()} immediately after this method.
     * <p>
     * To create a zoned date-time at the same instant irrespective of the local time-line,
     * use {@link #atZoneSameInstant(ZoneId)}.
     * To use the offset as the zone ID, use {@link #toZonedDateTime()}.
     *
     * @param $zone ZoneId the time-zone to use, not null
     * @return ZonedDateTime the zoned date-time formed from this date and the earliest valid time for the zone, not null
     */
    public function atZoneSimilarLocal(ZoneId $zone)
    {
        return ZonedDateTime::ofLocal($this->dateTime, $zone, $this->offset);
    }

//-----------------------------------------------------------------------
    /**
     * Converts this date-time to an {@code OffsetTime}.
     * <p>
     * This returns an offset time with the same local time and offset.
     *
     * @return OffsetTime an OffsetTime representing the time and offset, not null
     */
    public
    function toOffsetTime()
    {
        return OffsetTime::of($this->dateTime->toLocalTime(), $this->offset);
    }

    /**
     * Converts this date-time to a {@code ZonedDateTime} using the offset as the zone ID.
     * <p>
     * This creates the simplest possible {@code ZonedDateTime} using the offset
     * as the zone ID.
     * <p>
     * To control the time-zone used, see {@link #atZoneSameInstant(ZoneId)} and
     * {@link #atZoneSimilarLocal(ZoneId)}.
     *
     * @return ZonedDateTime a zoned date-time representing the same local date-time and offset, not null
     */
    public
    function toZonedDateTime()
    {
        return ZonedDateTime::of($this->dateTime, $this->offset);
    }

    /**
     * Converts this date-time to an {@code Instant}.
     * <p>
     * This returns an {@code Instant} representing the same point on the
     * time-line as this date-time.
     *
     * @return Instant an {@code Instant} representing the same instant, not null
     */
    public function toInstant()
    {
        return $this->dateTime->toInstant($this->offset);
    }

    /**
     * Converts this date-time to the number of seconds from the epoch of 1970-01-01T00:00:00Z.
     * <p>
     * This allows this date-time to be converted to a value of the
     * {@link ChronoField#INSTANT_SECONDS epoch-seconds} field. This is primarily
     * intended for low-level conversions rather than general application usage.
     *
     * @return int the number of seconds from the epoch of 1970-01-01T00:00:00Z
     */
    public function toEpochSecond()
    {
        return $this->dateTime->toEpochSecond($this->offset);
    }

    //-----------------------------------------------------------------------
    /**
     * Compares this date-time to another date-time.
     * <p>
     * The comparison is based on the instant then on the local date-time.
     * It is "consistent with equals", as defined by {@link Comparable}.
     * <p>
     * For example, the following is the comparator order:
     * <ol>
     * <li>{@code 2008-12-03T10:30+01:00}</li>
     * <li>{@code 2008-12-03T11:00+01:00}</li>
     * <li>{@code 2008-12-03T12:00+02:00}</li>
     * <li>{@code 2008-12-03T11:30+01:00}</li>
     * <li>{@code 2008-12-03T12:00+01:00}</li>
     * <li>{@code 2008-12-03T12:30+01:00}</li>
     * </ol>
     * Values #2 and #3 represent the same instant on the time-line.
     * When two values represent the same instant, the local date-time is compared
     * to distinguish them. This step is needed to make the ordering
     * consistent with {@code equals()}.
     *
     * @param $other OffsetDateTime the other date-time to compare to, not null
     * @return int the comparator value, negative if less, positive if greater
     */
    public function compareTo(OffsetDateTime $other)
    {
        $cmp = $this->compareInstant($this, $other);
        if ($cmp == 0) {
            $cmp = $this->toLocalDateTime()->compareTo($other->toLocalDateTime());
        }

        return $cmp;
    }

//-----------------------------------------------------------------------
    /**
     * Checks if the instant of this date-time is after that of the specified date-time.
     * <p>
     * This method differs from the comparison in {@link #compareTo} and {@link #equals} in that it
     * only compares the instant of the date-time. This is equivalent to using
     * {@code dateTime1.toInstant().isAfter(dateTime2.toInstant());}.
     *
     * @param $other OffsetDateTime the other date-time to compare to, not null
     * @return true if this is after the instant of the specified date-time
     */
    public function isAfter(OffsetDateTime $other)
    {
        $thisEpochSec = $this->toEpochSecond();
        $otherEpochSec = $other->toEpochSecond();
        return $thisEpochSec > $otherEpochSec ||
        ($thisEpochSec == $otherEpochSec && $this->toLocalTime()->getNano() > $other->toLocalTime()->getNano());
    }

    /**
     * Checks if the instant of this date-time is before that of the specified date-time.
     * <p>
     * This method differs from the comparison in {@link #compareTo} in that it
     * only compares the instant of the date-time. This is equivalent to using
     * {@code dateTime1.toInstant().isBefore(dateTime2.toInstant());}.
     *
     * @param $other OffsetDateTime the other date-time to compare to, not null
     * @return bool true if this is before the instant of the specified date-time
     */
    public
    function isBefore(OffsetDateTime $other)
    {
        $thisEpochSec = $this->toEpochSecond();
        $otherEpochSec = $other->toEpochSecond();
        return $thisEpochSec < $otherEpochSec ||
        ($thisEpochSec == $otherEpochSec && $this->toLocalTime()->getNano() < $other->toLocalTime()->getNano());
    }

    /**
     * Checks if the instant of this date-time is equal to that of the specified date-time.
     * <p>
     * This method differs from the comparison in {@link #compareTo} and {@link #equals}
     * in that it only compares the instant of the date-time. This is equivalent to using
     * {@code dateTime1.toInstant().equals(dateTime2.toInstant());}.
     *
     * @param $other OffsetDateTime the other date-time to compare to, not null
     * @return true if the instant equals the instant of the specified date-time
     */
    public
    function isEqual(OffsetDateTime $other)
    {
        return $this->toEpochSecond() == $other->toEpochSecond() &&
        $this->toLocalTime()->getNano() == $other->toLocalTime()->getNano();
    }

//-----------------------------------------------------------------------
    /**
     * Checks if this date-time is equal to another date-time.
     * <p>
     * The comparison is based on the local date-time and the offset.
     * To compare for the same instant on the time-line, use {@link #isEqual}.
     * Only objects of type {@code OffsetDateTime} are compared, other types return false.
     *
     * @param $obj mixed the object to check, null returns false
     * @return true if this is equal to the other date-time
     */
    public function equals($obj)
    {
        if ($this === $obj) {
            return true;
        }

        if ($obj instanceof OffsetDateTime) {
            $other = $obj;
            return $this->dateTime->equals($other->dateTime) && $this->offset->equals($other->offset);
        }
        return false;
    }

//-----------------------------------------------------------------------
    /**
     * Outputs this date-time as a {@code String}, such as {@code 2007-12-03T10:15:30+01:00}.
     * <p>
     * The output will be one of the following ISO-8601 formats:
     * <ul>
     * <li>{@code uuuu-MM-dd'T'HH:mmXXXXX}</li>
     * <li>{@code uuuu-MM-dd'T'HH:mm:ssXXXXX}</li>
     * <li>{@code uuuu-MM-dd'T'HH:mm:ss.SSSXXXXX}</li>
     * <li>{@code uuuu-MM-dd'T'HH:mm:ss.SSSSSSXXXXX}</li>
     * <li>{@code uuuu-MM-dd'T'HH:mm:ss.SSSSSSSSSXXXXX}</li>
     * </ul>
     * The format used will be the shortest that outputs the full value of
     * the time where the omitted parts are implied to be zero.
     *
     * @return string a string representation of this date-time, not null
     */
    public function toString()
    {
        return $this->dateTime->__toString() . $this->offset->__toString();
    }
}
