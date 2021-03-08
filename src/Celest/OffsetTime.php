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

use Celest\Format\DateTimeFormatter;
use Celest\Helper\Long;
use Celest\Helper\Math;
use Celest\Temporal\AbstractTemporal;
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
 * A time with an offset from UTC/Greenwich in the ISO-8601 calendar system,
 * such as {@code 10:15:30+01:00}.
 * <p>
 * {@code OffsetTime} is an immutable date-time object that represents a time, often
 * viewed as hour-minute-second-offset.
 * This class stores all time fields, to a precision of nanoseconds,
 * as well as a zone offset.
 * For example, the value "13:45.30.123456789+02:00" can be stored
 * in an {@code OffsetTime}.
 *
 * <p>
 * This is a <a href="{@docRoot}/java/lang/doc-files/ValueBased.html">value-based</a>
 * class; use of identity-sensitive operations (including reference equality
 * ({@code ==}), identity hash code, or synchronization) on instances of
 * {@code OffsetTime} may have unpredictable results and should be avoided.
 * The {@code equals} method should be used for comparisons.
 *
 * @implSpec
 * This class is immutable and thread-safe.
 *
 * @since 1.8
 */
final class OffsetTime extends AbstractTemporal implements Temporal, TemporalAdjuster, \JsonSerializable
{


    public static function init()
    {
        self::$MIN = LocalTime::MIN()->atOffset(ZoneOffset::MAX());
        self::$MAX = LocalTime::MAX()->atOffset(ZoneOffset::MIN());
    }

    /**
     * The minimum supported {@code OffsetTime}, '00:00:00+18:00'.
     * This is the time of midnight at the start of the day in the maximum offset
     * (larger offsets are earlier on the time-line).
     * This combines {@link LocalTime#MIN} and {@link ZoneOffset#MAX}.
     * This could be used by an application as a "far past" date.
     * @return OffsetTime
     */
    public static function MIN()
    {
        return self::$MIN;
    }

    /** @var OffsetTime */
    private static $MIN;

    /**
     * The maximum supported {@code OffsetTime}, '23:59:59.999999999-18:00'.
     * This is the time just before midnight at the end of the day in the minimum offset
     * (larger negative offsets are later on the time-line).
     * This combines {@link LocalTime#MAX} and {@link ZoneOffset#MIN}.
     * This could be used by an application as a "far future" date.
     * @return OffsetTime
     */
    public static function MAX()
    {
        return self::$MAX;
    }

    /** @var OffsetTime */
    private static $MAX;

    /**
     * The local date-time.
     * @var LocalTime
     */
    private $time;
    /**
     * The offset from UTC/Greenwich.
     * @var ZoneOffset
     */
    private $offset;

    //-----------------------------------------------------------------------
    /**
     * Obtains the current time from the system clock in the default time-zone.
     * <p>
     * This will query the {@link Clock#systemDefaultZone() system clock} in the default
     * time-zone to obtain the current time.
     * The offset will be calculated from the time-zone in the clock.
     * <p>
     * Using this method will prevent the ability to use an alternate clock for testing
     * because the clock is hard-coded.
     *
     * @return OffsetTime the current time using the system clock and default time-zone, not null
     */
    public static function now()
    {
        return self::nowOf(Clock::systemDefaultZone());
    }

    /**
     * Obtains the current time from the system clock in the specified time-zone.
     * <p>
     * This will query the {@link Clock#system(ZoneId) system clock} to obtain the current time.
     * Specifying the time-zone avoids dependence on the default time-zone.
     * The offset will be calculated from the specified time-zone.
     * <p>
     * Using this method will prevent the ability to use an alternate clock for testing
     * because the clock is hard-coded.
     *
     * @param ZoneId $zone the zone ID to use, not null
     * @return OffsetTime the current time using the system clock, not null
     */
    public static function nowIn(ZoneId $zone)
    {
        return self::nowOf(Clock::system($zone));
    }

    /**
     * Obtains the current time from the specified clock.
     * <p>
     * This will query the specified clock to obtain the current time.
     * The offset will be calculated from the time-zone in the clock.
     * <p>
     * Using this method allows the use of an alternate clock for testing.
     * The alternate clock may be introduced using {@link Clock dependency injection}.
     *
     * @param Clock $clock the clock to use, not null
     * @return OffsetTime the current time, not null
     */
    public static function nowOf(Clock $clock)
    {
        $now = $clock->instant();  // called once
        return self::ofInstant($now, $clock->getZone()->getRules()->getOffset($now));
    }

    //-----------------------------------------------------------------------
    /**
     * Obtains an instance of {@code OffsetTime} from a local time and an offset.
     *
     * @param LocalTime $time the local time, not null
     * @param ZoneOffset $offset the zone offset, not null
     * @return OffsetTime the offset time, not null
     */
    public static function ofLocalTime(LocalTime $time, ZoneOffset $offset)
    {
        return new OffsetTime($time, $offset);
    }

    /**
     * Obtains an instance of {@code OffsetTime} from an hour, minute, second and nanosecond.
     * <p>
     * This creates an offset time with the four specified fields.
     * <p>
     * This method exists primarily for writing test cases.
     * Non test-code will typically use other methods to create an offset time.
     * {@code LocalTime} has two additional convenience variants of the
     * equivalent factory method taking fewer arguments.
     * They are not provided here to reduce the footprint of the API.
     *
     * @param int $hour the hour-of-day to represent, from 0 to 23
     * @param int $minute the minute-of-hour to represent, from 0 to 59
     * @param int $second the second-of-minute to represent, from 0 to 59
     * @param int $nanoOfSecond the nano-of-second to represent, from 0 to 999,999,999
     * @param ZoneOffset $offset the zone offset, not null
     * @return OffsetTime the offset time, not null
     * @throws DateTimeException if the value of any field is out of range
     */
    public static function of($hour, $minute, $second, $nanoOfSecond, ZoneOffset $offset)
    {
        return new OffsetTime(LocalTime::of($hour, $minute, $second, $nanoOfSecond), $offset);
    }

//-----------------------------------------------------------------------
    /**
     * Obtains an instance of {@code OffsetTime} from an {@code Instant} and zone ID.
     * <p>
     * This creates an offset time with the same instant as that specified.
     * Finding the offset from UTC/Greenwich is simple as there is only one valid
     * offset for each instant.
     * <p>
     * The date component of the instant is dropped during the conversion.
     * This means that the conversion can never fail due to the instant being
     * out of the valid range of dates.
     *
     * @param Instant $instant the instant to create the time from, not null
     * @param ZoneId $zone the time-zone, which may be an offset, not null
     * @return OffsetTime the offset time, not null
     */
    public static function ofInstant(Instant $instant, ZoneId $zone)
    {
        $rules = $zone->getRules();
        $offset = $rules->getOffset($instant);
        $localSecond = $instant->getEpochSecond() + $offset->getTotalSeconds();  // overflow caught later
        $secsOfDay = (int)Math::floorMod($localSecond, LocalTime::SECONDS_PER_DAY);
        $time = LocalTime::ofNanoOfDay($secsOfDay * LocalTime::NANOS_PER_SECOND + $instant->getNano());
        return new OffsetTime($time, $offset);
    }

    //-----------------------------------------------------------------------
    /**
     * Obtains an instance of {@code OffsetTime} from a temporal object.
     * <p>
     * This obtains an offset time based on the specified temporal.
     * A {@code TemporalAccessor} represents an arbitrary set of date and time information,
     * which this factory converts to an instance of {@code OffsetTime}.
     * <p>
     * The conversion extracts and combines the {@code ZoneOffset} and the
     * {@code LocalTime} from the temporal object.
     * Implementations are permitted to perform optimizations such as accessing
     * those fields that are equivalent to the relevant objects.
     * <p>
     * This method matches the signature of the functional interface {@link TemporalQuery}
     * allowing it to be used as a query via method reference, {@code OffsetTime::from}.
     *
     * @param TemporalAccessor $temporal the temporal object to convert, not null
     * @return OffsetTime the offset time, not null
     * @throws DateTimeException if unable to convert to an {@code OffsetTime}
     */
    public static function from(TemporalAccessor $temporal)
    {
        if ($temporal instanceof OffsetTime) {
            return $temporal;
        }

        try {
            $time = LocalTime::from($temporal);
            $offset = ZoneOffset::from($temporal);
            return new OffsetTime($time, $offset);
        } catch (DateTimeException $ex) {
            throw new DateTimeException("Unable to obtain OffsetTime from TemporalAccessor: " .
                $temporal . " of type " . get_class($temporal), $ex);
        }
    }

    //-----------------------------------------------------------------------
    /**
     * Obtains an instance of {@code OffsetTime} from a text string such as {@code 10:15:30+01:00}.
     * <p>
     * The string must represent a valid time and is parsed using
     * {@link java.time.format.DateTimeFormatter#ISO_OFFSET_TIME}.
     *
     * @param string $text the text to parse such as "10:15:30+01:00", not null
     * @return OffsetTime the parsed local time, not null
     * @throws DateTimeParseException if the text cannot be parsed
     */
    public static function parse($text)
    {
        return self::parseWith($text, DateTimeFormatter::ISO_OFFSET_TIME());
    }

    /**
     * Obtains an instance of {@code OffsetTime} from a text string using a specific formatter.
     * <p>
     * The text is parsed using the formatter, returning a time.
     *
     * @param string $text the text to parse, not null
     * @param DateTimeFormatter $formatter the formatter to use, not null
     * @return OffsetTime the parsed offset time, not null
     * @throws DateTimeParseException if the text cannot be parsed
     */
    public static function parseWith($text, DateTimeFormatter $formatter)
    {
        return $formatter->parseQuery($text, TemporalQueries::fromCallable([OffsetTime::class, 'from']));
    }

//-----------------------------------------------------------------------
    /**
     * Constructor.
     *
     * @param LocalTime $time the local time, not null
     * @param ZoneOffset $offset the zone offset, not null
     */
    private function __construct(LocalTime $time, ZoneOffset $offset)
    {
        $this->time = $time;
        $this->offset = $offset;
    }

    /**
     * Returns a new time based on this one, returning {@code this} where possible.
     *
     * @param LocalTime $time the time to create with, not null
     * @param ZoneOffset $offset the zone offset to create with, not null
     * @return OffsetTime
     */
    private function _with(LocalTime $time, ZoneOffset $offset)
    {
        if ($this->time === $time && $this->offset->equals($offset)) {
            return $this;
        }

        return new OffsetTime($time, $offset);
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
     * <li>{@code OFFSET_SECONDS}
     * </ul>
     * All other {@code ChronoField} instances will return false.
     * <p>
     * If the field is not a {@code ChronoField}, then the result of this method
     * is obtained by invoking {@code TemporalField.isSupportedBy(TemporalAccessor)}
     * passing {@code this} as the argument.
     * Whether the field is supported is determined by the field.
     *
     * @param TemporalField $field the field to check, null returns false
     * @return bool true if the field is supported on this time, false if not
     */
    public function isSupported(TemporalField $field)
    {
        if ($field instanceof ChronoField) {
            return $field->isTimeBased() || $field == ChronoField::OFFSET_SECONDS();
        }

        return $field !== null && $field->isSupportedBy($this);
    }

    /**
     * Checks if the specified unit is supported.
     * <p>
     * This checks if the specified unit can be added to, or subtracted from, this offset-time.
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
     * @param TemporalUnit $unit the unit to check, null returns false
     * @return bool true if the unit can be added/subtracted, false if not
     */
    public function isUnitSupported(TemporalUnit $unit)
    {
        if ($unit instanceof ChronoUnit) {
            return $unit->isTimeBased();
        }

        return $unit !== null && $unit->isSupportedBy($this);
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
     * @param TemporalField $field the field to query the range for, not null
     * @return ValueRange the range of valid values for the field, not null
     * @throws DateTimeException if the range for the field cannot be obtained
     * @throws UnsupportedTemporalTypeException if the field is not supported
     */
    public function range(TemporalField $field)
    {
        if ($field instanceof ChronoField) {
            if ($field == ChronoField::OFFSET_SECONDS()) {
                return $field->range();
            }

            return $this->time->range($field);
        }
        return $field->rangeRefinedBy($this);
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
        return parent::get($field);
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
     * @param TemporalField $field the field to get, not null
     * @return int the value for the field
     * @throws DateTimeException if a value for the field cannot be obtained
     * @throws UnsupportedTemporalTypeException if the field is not supported
     * @throws ArithmeticException if numeric overflow occurs
     */
    public function getLong(TemporalField $field)
    {
        if ($field instanceof ChronoField) {
            if ($field == ChronoField::OFFSET_SECONDS()) {
                return $this->offset->getTotalSeconds();
            }

            return $this->time->getLong($field);
        }
        return $field->getFrom($this);
    }

    //-----------------------------------------------------------------------
    /**
     * Gets the zone offset, such as '+01:00'.
     * <p>
     * This is the offset of the local time from UTC/Greenwich.
     *
     * @return ZoneOffset the zone offset, not null
     */
    public function getOffset()
    {
        return $this->offset;
    }

    /**
     * Returns a copy of this {@code OffsetTime} with the specified offset ensuring
     * that the result has the same local time.
     * <p>
     * This method returns an object with the same {@code LocalTime} and the specified {@code ZoneOffset}.
     * No calculation is needed or performed.
     * For example, if this time represents {@code 10:30+02:00} and the offset specified is
     * {@code +03:00}, then this method will return {@code 10:30+03:00}.
     * <p>
     * To take into account the difference between the offsets, and adjust the time fields,
     * use {@link #withOffsetSameInstant}.
     * <p>
     * This instance is immutable and unaffected by this method call.
     *
     * @param ZoneOffset $offset the zone offset to change to, not null
     * @return OffsetTime an {@code OffsetTime} based on this time with the requested offset, not null
     */
    public function withOffsetSameLocal(ZoneOffset $offset)
    {
        return $offset !== null && $offset->equals($this->offset) ? $this : new OffsetTime($this->time, $offset);
    }

    /**
     * Returns a copy of this {@code OffsetTime} with the specified offset ensuring
     * that the result is at the same instant on an implied day.
     * <p>
     * This method returns an object with the specified {@code ZoneOffset} and a {@code LocalTime}
     * adjusted by the difference between the two offsets.
     * This will result in the old and new objects representing the same instant on an implied day.
     * This is useful for finding the local time in a different offset.
     * For example, if this time represents {@code 10:30+02:00} and the offset specified is
     * {@code +03:00}, then this method will return {@code 11:30+03:00}.
     * <p>
     * To change the offset without adjusting the local time use {@link #withOffsetSameLocal}.
     * <p>
     * This instance is immutable and unaffected by this method call.
     *
     * @param ZoneOffset $offset the zone offset to change to, not null
     * @return OffsetTime an {@code OffsetTime} based on this time with the requested offset, not null
     */
    public function withOffsetSameInstant(ZoneOffset $offset)
    {
        if ($offset->equals($this->offset)) {
            return $this;
        }

        $difference = $offset->getTotalSeconds() - $this->offset->getTotalSeconds();
        $adjusted = $this->time->plusSeconds($difference);
        return new OffsetTime($adjusted, $offset);
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

//-----------------------------------------------------------------------
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
     * Returns an adjusted copy of this time.
     * <p>
     * This returns an {@code OffsetTime}, based on this one, with the time adjusted.
     * The adjustment takes place using the specified adjuster strategy object.
     * Read the documentation of the adjuster to understand what adjustment will be made.
     * <p>
     * A simple adjuster might simply set the one of the fields, such as the hour field.
     * A more complex adjuster might set the time to the last hour of the day.
     * <p>
     * The classes {@link LocalTime} and {@link ZoneOffset} implement {@code TemporalAdjuster},
     * thus this method can be used to change the time or offset:
     * <pre>
     *  result = offsetTime.with($this->time);
     *  result = offsetTime.with(offset);
     * </pre>
     * <p>
     * The result of this method is obtained by invoking the
     * {@link TemporalAdjuster#adjustInto(Temporal)} method on the
     * specified adjuster passing {@code this} as the argument.
     * <p>
     * This instance is immutable and unaffected by this method call.
     *
     * @param TemporalAdjuster $adjuster the adjuster to use, not null
     * @return OffsetTime an {@code OffsetTime} based on {@code this} with the adjustment made, not null
     * @throws DateTimeException if the adjustment cannot be made
     * @throws ArithmeticException if numeric overflow occurs
     */
    public function adjust(TemporalAdjuster $adjuster)
    {
        // optimizations
        if ($adjuster instanceof LocalTime) {
            return $this->_with($adjuster, $this->offset);
        } else
            if ($adjuster instanceof ZoneOffset) {
                return $this->_with($this->time, $adjuster);
            } else if ($adjuster instanceof OffsetTime) {
                return $adjuster;
            }
        return $adjuster->adjustInto($this);
    }

    /**
     * Returns a copy of this time with the specified field set to a new value.
     * <p>
     * This returns an {@code OffsetTime}, based on this one, with the value
     * for the specified field changed.
     * This can be used to change any supported field, such as the hour, minute or second.
     * If it is not possible to set the value, because the field is not supported or for
     * some other reason, an exception is thrown.
     * <p>
     * If the field is a {@link ChronoField} then the adjustment is implemented here.
     * <p>
     * The {@code OFFSET_SECONDS} field will return a time with the specified offset.
     * The local time is unaltered. If the new offset value is outside the valid range
     * then a {@code DateTimeException} will be thrown.
     * <p>
     * The other {@link #isSupported(TemporalField) supported fields} will behave as per
     * the matching method on {@link LocalTime#with(TemporalField, long)} LocalTime}.
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
     * @param TemporalField $field the field to set in the result, not null
     * @param int $newValue the new value of the field in the result
     * @return OffsetTime an {@code OffsetTime} based on {@code this} with the specified field set, not null
     * @throws DateTimeException if the field cannot be set
     * @throws UnsupportedTemporalTypeException if the field is not supported
     * @throws ArithmeticException if numeric overflow occurs
     */
    public function with(TemporalField $field, $newValue)
    {
        if ($field instanceof ChronoField) {
            if ($field == ChronoField::OFFSET_SECONDS()) {
                $f = $field;
                return $this->_with($this->time, ZoneOffset::ofTotalSeconds($f->checkValidIntValue($newValue)));
            }

            return $this->_with($this->time->with($field, $newValue), $this->offset);
        }
        return $field->adjustInto($this, $newValue);
    }

    //-----------------------------------------------------------------------
    /**
     * Returns a copy of this {@code OffsetTime} with the hour-of-day altered.
     * <p>
     * The offset does not affect the calculation and will be the same in the result.
     * <p>
     * This instance is immutable and unaffected by this method call.
     *
     * @param int $hour the hour-of-day to set in the result, from 0 to 23
     * @return OffsetTime an {@code OffsetTime} based on this time with the requested hour, not null
     * @throws DateTimeException if the hour value is invalid
     */
    public function withHour($hour)
    {
        return $this->_with($this->time->withHour($hour), $this->offset);
    }

    /**
     * Returns a copy of this {@code OffsetTime} with the minute-of-hour altered.
     * <p>
     * The offset does not affect the calculation and will be the same in the result.
     * <p>
     * This instance is immutable and unaffected by this method call.
     *
     * @param int $minute the minute-of-hour to set in the result, from 0 to 59
     * @return OffsetTime an {@code OffsetTime} based on this time with the requested minute, not null
     * @throws DateTimeException if the minute value is invalid
     */
    public function withMinute($minute)
    {
        return $this->_with($this->time->withMinute($minute), $this->offset);
    }

    /**
     * Returns a copy of this {@code OffsetTime} with the second-of-minute altered.
     * <p>
     * The offset does not affect the calculation and will be the same in the result.
     * <p>
     * This instance is immutable and unaffected by this method call.
     *
     * @param int $second the second-of-minute to set in the result, from 0 to 59
     * @return OffsetTime an {@code OffsetTime} based on this time with the requested second, not null
     * @throws DateTimeException if the second value is invalid
     */
    public function withSecond($second)
    {
        return $this->_with($this->time->withSecond($second), $this->offset);
    }

    /**
     * Returns a copy of this {@code OffsetTime} with the nano-of-second altered.
     * <p>
     * The offset does not affect the calculation and will be the same in the result.
     * <p>
     * This instance is immutable and unaffected by this method call.
     *
     * @param int $nanoOfSecond the nano-of-second to set in the result, from 0 to 999,999,999
     * @return OffsetTime an {@code OffsetTime} based on this time with the requested nanosecond, not null
     * @throws DateTimeException if the nanos value is invalid
     */
    public function withNano($nanoOfSecond)
    {
        return $this->_with($this->time->withNano($nanoOfSecond), $this->offset);
    }

    //-----------------------------------------------------------------------
    /**
     * Returns a copy of this {@code OffsetTime} with the time truncated.
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
     * The offset does not affect the calculation and will be the same in the result.
     * <p>
     * This instance is immutable and unaffected by this method call.
     *
     * @param TemporalUnit $unit the unit to truncate to, not null
     * @return OffsetTime an {@code OffsetTime} based on this time with the time truncated, not null
     * @throws DateTimeException if unable to truncate
     * @throws UnsupportedTemporalTypeException if the unit is not supported
     */
    public function truncatedTo(TemporalUnit $unit)
    {
        return $this->_with($this->time->truncatedTo($unit), $this->offset);
    }

//-----------------------------------------------------------------------
    /**
     * Returns a copy of this time with the specified amount added.
     * <p>
     * This returns an {@code OffsetTime}, based on this one, with the specified amount added.
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
     * @param TemporalAmount $amountToAdd the amount to add, not null
     * @return OffsetTime an {@code OffsetTime} based on this time with the addition made, not null
     * @throws DateTimeException if the addition cannot be made
     * @throws ArithmeticException if numeric overflow occurs
     */
    public function plusAmount(TemporalAmount $amountToAdd)
    {
        return $amountToAdd->addTo($this);
    }

    /**
     * Returns a copy of this time with the specified amount added.
     * <p>
     * This returns an {@code OffsetTime}, based on this one, with the amount
     * in terms of the unit added. If it is not possible to add the amount, because the
     * unit is not supported or for some other reason, an exception is thrown.
     * <p>
     * If the field is a {@link ChronoUnit} then the addition is implemented by
     * {@link LocalTime#plus(long, TemporalUnit)}.
     * The offset is not part of the calculation and will be unchanged in the result.
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
     * @return OffsetTime an {@code OffsetTime} based on this time with the specified amount added, not null
     * @throws DateTimeException if the addition cannot be made
     * @throws UnsupportedTemporalTypeException if the unit is not supported
     * @throws ArithmeticException if numeric overflow occurs
     */
    public function plus($amountToAdd, TemporalUnit $unit)
    {
        if ($unit instanceof ChronoUnit) {
            return $this->_with($this->time->plus($amountToAdd, $unit), $this->offset);
        }

        return $unit->addTo($this, $amountToAdd);
    }

    //-----------------------------------------------------------------------
    /**
     * Returns a copy of this {@code OffsetTime} with the specified number of hours added.
     * <p>
     * This adds the specified number of hours to this time, returning a new time.
     * The calculation wraps around midnight.
     * <p>
     * This instance is immutable and unaffected by this method call.
     *
     * @param int $hours the hours to add, may be negative
     * @return OffsetTime an {@code OffsetTime} based on this time with the hours added, not null
     */
    public function plusHours($hours)
    {
        return $this->_with($this->time->plusHours($hours), $this->offset);
    }

    /**
     * Returns a copy of this {@code OffsetTime} with the specified number of minutes added.
     * <p>
     * This adds the specified number of minutes to this time, returning a new time.
     * The calculation wraps around midnight.
     * <p>
     * This instance is immutable and unaffected by this method call.
     *
     * @param int $minutes the minutes to add, may be negative
     * @return OffsetTime an {@code OffsetTime} based on this time with the minutes added, not null
     */
    public function plusMinutes($minutes)
    {
        return $this->_with($this->time->plusMinutes($minutes), $this->offset);
    }

    /**
     * Returns a copy of this {@code OffsetTime} with the specified number of seconds added.
     * <p>
     * This adds the specified number of seconds to this time, returning a new time.
     * The calculation wraps around midnight.
     * <p>
     * This instance is immutable and unaffected by this method call.
     *
     * @param int $seconds the seconds to add, may be negative
     * @return OffsetTime an {@code OffsetTime} based on this time with the seconds added, not null
     */
    public function plusSeconds($seconds)
    {
        return $this->_with($this->time->plusSeconds($seconds), $this->offset);
    }

    /**
     * Returns a copy of this {@code OffsetTime} with the specified number of nanoseconds added.
     * <p>
     * This adds the specified number of nanoseconds to this time, returning a new time.
     * The calculation wraps around midnight.
     * <p>
     * This instance is immutable and unaffected by this method call.
     *
     * @param int $nanos the nanos to add, may be negative
     * @return OffsetTime an {@code OffsetTime} based on this time with the nanoseconds added, not null
     */
    public function plusNanos($nanos)
    {
        return $this->_with($this->time->plusNanos($nanos), $this->offset);
    }

//-----------------------------------------------------------------------
    /**
     * Returns a copy of this time with the specified amount subtracted.
     * <p>
     * This returns an {@code OffsetTime}, based on this one, with the specified amount subtracted.
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
     * @param TemporalAmount $amountToSubtract the amount to subtract, not null
     * @return OffsetTime an {@code OffsetTime} based on this time with the subtraction made, not null
     * @throws DateTimeException if the subtraction cannot be made
     * @throws ArithmeticException if numeric overflow occurs
     */
    public function minusAmount(TemporalAmount $amountToSubtract)
    {
        return $amountToSubtract->subtractFrom($this);
    }

    /**
     * Returns a copy of this time with the specified amount subtracted.
     * <p>
     * This returns an {@code OffsetTime}, based on this one, with the amount
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
     * @return OffsetTime an {@code OffsetTime} based on this time with the specified amount subtracted, not null
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
     * Returns a copy of this {@code OffsetTime} with the specified number of hours subtracted.
     * <p>
     * This subtracts the specified number of hours from this time, returning a new time.
     * The calculation wraps around midnight.
     * <p>
     * This instance is immutable and unaffected by this method call.
     *
     * @param int $hours the hours to subtract, may be negative
     * @return OffsetTime an {@code OffsetTime} based on this time with the hours subtracted, not null
     */
    public function minusHours($hours)
    {
        return $this->_with($this->time->minusHours($hours), $this->offset);
    }

    /**
     * Returns a copy of this {@code OffsetTime} with the specified number of minutes subtracted.
     * <p>
     * This subtracts the specified number of minutes from this time, returning a new time.
     * The calculation wraps around midnight.
     * <p>
     * This instance is immutable and unaffected by this method call.
     *
     * @param int $minutes the minutes to subtract, may be negative
     * @return OffsetTime an {@code OffsetTime} based on this time with the minutes subtracted, not null
     */
    public function minusMinutes($minutes)
    {
        return $this->_with($this->time->minusMinutes($minutes), $this->offset);
    }

    /**
     * Returns a copy of this {@code OffsetTime} with the specified number of seconds subtracted.
     * <p>
     * This subtracts the specified number of seconds from this time, returning a new time.
     * The calculation wraps around midnight.
     * <p>
     * This instance is immutable and unaffected by this method call.
     *
     * @param int $seconds the seconds to subtract, may be negative
     * @return OffsetTime an {@code OffsetTime} based on this time with the seconds subtracted, not null
     */
    public function minusSeconds($seconds)
    {
        return $this->_with($this->time->minusSeconds($seconds), $this->offset);
    }

    /**
     * Returns a copy of this {@code OffsetTime} with the specified number of nanoseconds subtracted.
     * <p>
     * This subtracts the specified number of nanoseconds from this time, returning a new time.
     * The calculation wraps around midnight.
     * <p>
     * This instance is immutable and unaffected by this method call.
     *
     * @param int $nanos the nanos to subtract, may be negative
     * @return OffsetTime an {@code OffsetTime} based on this time with the nanoseconds subtracted, not null
     */
    public function minusNanos($nanos)
    {
        return $this->_with($this->time->minusNanos($nanos), $this->offset);
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
     * @param <R> the type of the result
     * @param TemporalQuery $query the query to invoke, not null
     * @return mixed the query result, null may be returned (defined by the query)
     * @throws DateTimeException if unable to query (defined by the query)
     * @throws ArithmeticException if numeric overflow occurs (defined by the query)
     */
    public function query(TemporalQuery $query)
    {
        if ($query == TemporalQueries::offset() || $query == TemporalQueries::zone()) {
            return $this->offset;
        } else if ($query == TemporalQueries::zoneId() | $query == TemporalQueries::chronology() || $query == TemporalQueries::localDate()) {
            return null;
        } else if ($query == TemporalQueries::localTime()) {
            return $this->time;
        } else if ($query == TemporalQueries::precision()) {
            return ChronoUnit::NANOS();
        }
        // inline TemporalAccessor.super.query(query) as an optimization
        // non-JDK classes are not permitted to make this optimization
        return $query->queryFrom($this);
    }

    /**
     * Adjusts the specified temporal object to have the same offset and time
     * as this object.
     * <p>
     * This returns a temporal object of the same observable type as the input
     * with the offset and time changed to be the same as this.
     * <p>
     * The adjustment is equivalent to using {@link Temporal#with(TemporalField, long)}
     * twice, passing {@link ChronoField#NANO_OF_DAY} and
     * {@link ChronoField#OFFSET_SECONDS} as the fields.
     * <p>
     * In most cases, it is clearer to reverse the calling pattern by using
     * {@link Temporal#with(TemporalAdjuster)}:
     * <pre>
     *   // these two lines are equivalent, but the second approach is recommended
     *   temporal = thisOffsetTime.adjustInto(temporal);
     *   temporal = temporal.with(thisOffsetTime);
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
        return $temporal
            ->with(ChronoField::NANO_OF_DAY(), $this->time->toNanoOfDay())
            ->with(ChronoField::OFFSET_SECONDS(), $this->offset->getTotalSeconds());
    }

    /**
     * Calculates the amount of time until another time in terms of the specified unit.
     * <p>
     * This calculates the amount of time between two {@code OffsetTime}
     * objects in terms of a single {@code TemporalUnit}.
     * The start and end points are {@code this} and the specified time.
     * The result will be negative if the end is before the start.
     * For example, the amount in hours between two times can be calculated
     * using {@code startTime.until(endTime, HOURS)}.
     * <p>
     * The {@code Temporal} passed to this method is converted to a
     * {@code OffsetTime} using {@link #from(TemporalAccessor)}.
     * If the offset differs between the two times, then the specified
     * end time is normalized to have the same offset as this time.
     * <p>
     * The calculation returns a whole number, representing the number of
     * complete units between the two times.
     * For example, the amount in hours between 11:30Z and 13:29Z will only
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
     * @param Temporal $endExclusive the end time, exclusive, which is converted to an {@code OffsetTime}, not null
     * @param TemporalUnit $unit the unit to measure the amount in, not null
     * @return int the amount of time between this time and the end time
     * @throws DateTimeException if the amount cannot be calculated, or the end
     *  temporal cannot be converted to an {@code OffsetTime}
     * @throws UnsupportedTemporalTypeException if the unit is not supported
     * @throws ArithmeticException if numeric overflow occurs
     */
    public function until(Temporal $endExclusive, TemporalUnit $unit)
    {
        $end = OffsetTime::from($endExclusive);
        if ($unit instanceof ChronoUnit) {
            $nanosUntil = $end->toEpochNano() - $this->toEpochNano();  // no overflow
            switch ($unit) {
                case ChronoUnit::NANOS():
                    return $nanosUntil;
                case ChronoUnit::MICROS():
                    return $nanosUntil / 1000;
                case ChronoUnit::MILLIS():
                    return $nanosUntil / 1000000;
                case ChronoUnit::SECONDS():
                    return $nanosUntil / LocalTime::NANOS_PER_SECOND;
                case ChronoUnit::MINUTES():
                    return $nanosUntil / LocalTime::NANOS_PER_MINUTE;
                case ChronoUnit::HOURS():
                    return $nanosUntil / LocalTime::NANOS_PER_HOUR;
                case ChronoUnit::HALF_DAYS():
                    return $nanosUntil / (12 * LocalTime::NANOS_PER_HOUR);
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
     * @param DateTimeFormatter $formatter the formatter to use, not null
     * @return string the formatted time string, not null
     * @throws DateTimeException if an error occurs during printing
     */
    public function format(DateTimeFormatter $formatter)
    {
        return $formatter->format($this);
    }

//-----------------------------------------------------------------------
    /**
     * Combines this time with a date to create an {@code OffsetDateTime}.
     * <p>
     * This returns an {@code OffsetDateTime} formed from this time and the specified date.
     * All possible combinations of date and time are valid.
     *
     * @param LocalDate $date the date to combine with, not null
     * @return OffsetDateTime the offset date-time formed from this time and the specified date, not null
     */
    public function atDate(LocalDate $date)
    {
        return OffsetDateTime::ofDateAndTime($date, $this->time, $this->offset);
    }

    //-----------------------------------------------------------------------
    /**
     * Converts this time to epoch nanos based on 1970-01-01Z.
     *
     * @return int the epoch nanos value
     */
    private function toEpochNano()
    {
        $nod = $this->time->toNanoOfDay();
        $offsetNanos = $this->offset->getTotalSeconds() * LocalTime::NANOS_PER_SECOND;
        return $nod - $offsetNanos;
    }

//-----------------------------------------------------------------------
    /**
     * Compares this {@code OffsetTime} to another time.
     * <p>
     * The comparison is based first on the UTC equivalent instant, then on the local time.
     * It is "consistent with equals", as defined by {@link Comparable}.
     * <p>
     * For example, the following is the comparator order:
     * <ol>
     * <li>{@code 10:30+01:00}</li>
     * <li>{@code 11:00+01:00}</li>
     * <li>{@code 12:00+02:00}</li>
     * <li>{@code 11:30+01:00}</li>
     * <li>{@code 12:00+01:00}</li>
     * <li>{@code 12:30+01:00}</li>
     * </ol>
     * Values #2 and #3 represent the same instant on the time-line.
     * When two values represent the same instant, the local time is compared
     * to distinguish them. This step is needed to make the ordering
     * consistent with {@code equals()}.
     * <p>
     * To compare the underlying local time of two {@code TemporalAccessor} instances,
     * use {@link ChronoField#NANO_OF_DAY} as a comparator.
     *
     * @param OffsetTime $other the other time to compare to, not null
     * @return int the comparator value, negative if less, positive if greater
     * @throws NullPointerException if {@code other} is null
     */
    public function compareTo(OffsetTime $other)
    {
        if ($this->offset->equals($other->offset)) {
            return $this->time->compareTo($other->time);
        }

        $compare = Long::compare($this->toEpochNano(), $other->toEpochNano());
        if ($compare === 0) {
            $compare = $this->time->compareTo($other->time);
        }
        return $compare;
    }

    //-----------------------------------------------------------------------
    /**
     * Checks if the instant of this {@code OffsetTime} is after that of the
     * specified time applying both times to a common date.
     * <p>
     * This method differs from the comparison in {@link #compareTo} in that it
     * only compares the instant of the time. This is equivalent to converting both
     * times to an instant using the same date and comparing the instants.
     *
     * @param OffsetTime $other the other time to compare to, not null
     * @return bool true if this is after the instant of the specified time
     */
    public function isAfter(OffsetTime $other)
    {
        return $this->toEpochNano() > $other->toEpochNano();
    }

    /**
     * Checks if the instant of this {@code OffsetTime} is before that of the
     * specified time applying both times to a common date.
     * <p>
     * This method differs from the comparison in {@link #compareTo} in that it
     * only compares the instant of the time. This is equivalent to converting both
     * times to an instant using the same date and comparing the instants.
     *
     * @param OffsetTime $other the other time to compare to, not null
     * @return bool true if this is before the instant of the specified time
     */
    public function isBefore(OffsetTime $other)
    {
        return $this->toEpochNano() < $other->toEpochNano();
    }

    /**
     * Checks if the instant of this {@code OffsetTime} is equal to that of the
     * specified time applying both times to a common date.
     * <p>
     * This method differs from the comparison in {@link #compareTo} and {@link #equals}
     * in that it only compares the instant of the time. This is equivalent to converting both
     * times to an instant using the same date and comparing the instants.
     *
     * @param OffsetTime $other the other time to compare to, not null
     * @return bool true if this is equal to the instant of the specified time
     */
    public function isEqual(OffsetTime $other)
    {
        return $this->toEpochNano() === $other->toEpochNano();
    }

    //-----------------------------------------------------------------------
    /**
     * Checks if this time is equal to another time.
     * <p>
     * The comparison is based on the local-time and the offset.
     * To compare for the same instant on the time-line, use {@link #isEqual(OffsetTime)}.
     * <p>
     * Only objects of type {@code OffsetTime} are compared, other types return false.
     * To compare the underlying local time of two {@code TemporalAccessor} instances,
     * use {@link ChronoField#NANO_OF_DAY} as a comparator.
     *
     * @param mixed $obj the object to check, null returns false
     * @return bool true if this is equal to the other time
     */
    public function equals($obj)
    {
        if ($this === $obj) {
            return true;
        }
        if ($obj instanceof OffsetTime) {
            /** @var OffsetTime $other */
            $other = $obj;
            return $this->time->equals($other->time) && $this->offset->equals($other->offset);
        }
        return false;
    }


    //-----------------------------------------------------------------------
    /**
     * Outputs this time as a {@code String}, such as {@code 10:15:30+01:00}.
     * <p>
     * The output will be one of the following ISO-8601 formats:
     * <ul>
     * <li>{@code HH:mmXXXXX}</li>
     * <li>{@code HH:mm:ssXXXXX}</li>
     * <li>{@code HH:mm:ss.SSSXXXXX}</li>
     * <li>{@code HH:mm:ss.SSSSSSXXXXX}</li>
     * <li>{@code HH:mm:ss.SSSSSSSSSXXXXX}</li>
     * </ul>
     * The format used will be the shortest that outputs the full value of
     * the time where the omitted parts are implied to be zero.
     *
     * @return string a string representation of this time, not null
     */
    public function __toString()
    {
        return $this->time->__toString() . $this->offset->__toString();
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

OffsetTime::init();
