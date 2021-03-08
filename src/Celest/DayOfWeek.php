<?php

namespace Celest;


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
use Celest\Format\DateTimeFormatterBuilder;
use Celest\Format\TextStyle;
use Celest\Temporal\AbstractTemporalAccessor;
use Celest\Temporal\ChronoField;
use Celest\Temporal\ChronoUnit;
use Celest\Temporal\Temporal;
use Celest\Temporal\TemporalAccessor;
use Celest\Temporal\TemporalAdjuster;
use Celest\Temporal\TemporalField;
use Celest\Temporal\TemporalQueries;
use Celest\Temporal\TemporalQuery;
use Celest\Temporal\UnsupportedTemporalTypeException;
use Celest\Temporal\ValueRange;

/**
 * A day-of-week, such as 'Tuesday'.
 * <p>
 * {@code DayOfWeek} is an enum representing the 7 days of the week -
 * Monday, Tuesday, Wednesday, Thursday, Friday, Saturday and Sunday.
 * <p>
 * In addition to the textual enum name, each day-of-week has an {@code int} value.
 * The {@code int} value follows the ISO-8601 standard, from 1 (Monday) to 7 (Sunday).
 * It is recommended that applications use the enum rather than the {@code int} value
 * to ensure code clarity.
 * <p>
 * This enum provides access to the localized textual form of the day-of-week.
 * Some locales also assign different numeric values to the days, declaring
 * Sunday to have the value 1, however this class provides no support for this.
 * See {@link WeekFields} for localized week-numbering.
 * <p>
 * <b>Do not use {@code ordinal()} to obtain the numeric representation of {@code DayOfWeek}.
 * Use {@code getValue()} instead.</b>
 * <p>
 * This enum represents a common concept that is found in many calendar systems.
 * As such, this enum may be used by any calendar system that has the day-of-week
 * concept defined exactly equivalent to the ISO calendar system.
 *
 * @implSpec
 * This is an immutable and thread-safe enum.
 *
 * @since 1.8
 */
final class DayOfWeek extends AbstractTemporalAccessor implements TemporalAccessor, TemporalAdjuster, \JsonSerializable
{
    /**
     * @internal
     */
    public static function init()
    {
        self::$MONDAY = new DayOfWeek(0, "MONDAY");
        self::$TUESDAY = new DayOfWeek(1, "TUESDAY");
        self::$WEDNESDAY = new DayOfWeek(2, "WEDNESDAY");
        self::$THURSDAY = new DayOfWeek(3, "THURSDAY");
        self::$FRIDAY = new DayOfWeek(4, "FRIDAY");
        self::$SATURDAY = new DayOfWeek(5, "SATURDAY");
        self::$SUNDAY = new DayOfWeek(6, "SUNDAY");

        self::$ENUMS = [
            self::$MONDAY,
            self::$TUESDAY,
            self::$WEDNESDAY,
            self::$THURSDAY,
            self::$FRIDAY,
            self::$SATURDAY,
            self::$SUNDAY,
        ];
    }

    /**
     * The singleton instance for the day-of-week of Monday.
     * This has the numeric value of {@code 1}.
     * @return DayOfWeek
     */
    public static function MONDAY()
    {
        return self::$MONDAY;
    }

    /** @var DayOfWeek */
    private static $MONDAY;

    /**
     * The singleton instance for the day-of-week of Tuesday.
     * This has the numeric value of {@code 2}.
     * @return DayOfWeek
     */
    public static function TUESDAY()
    {
        return self::$TUESDAY;
    }

    /** @var DayOfWeek */
    private static $TUESDAY;

    /**
     * The singleton instance for the day-of-week of Wednesday.
     * This has the numeric value of {@code 3}.
     * @return DayOfWeek
     */
    public static function WEDNESDAY()
    {
        return self::$WEDNESDAY;
    }

    /** @var DayOfWeek */
    private static $WEDNESDAY;

    /**
     * The singleton instance for the day-of-week of Thursday.
     * This has the numeric value of {@code 4}.
     * @return DayOfWeek
     */
    public static function THURSDAY()
    {
        return self::$THURSDAY;
    }

    /** @var DayOfWeek */
    private static $THURSDAY;

    /**
     * The singleton instance for the day-of-week of Friday.
     * This has the numeric value of {@code 5}.
     * @return DayOfWeek
     */
    public static function FRIDAY()
    {
        return self::$FRIDAY;
    }

    /** @var DayOfWeek */
    private static $FRIDAY;

    /**
     * The singleton instance for the day-of-week of Saturday.
     * This has the numeric value of {@code 6}.
     * @return DayOfWeek
     */
    public static function SATURDAY()
    {
        return self::$SATURDAY;
    }

    /** @var DayOfWeek */
    private static $SATURDAY;

    /**
     * The singleton instance for the day-of-week of Sunday.
     * This has the numeric value of {@code 7}.
     * @return DayOfWeek
     */
    public static function SUNDAY()
    {
        return self::$SUNDAY;
    }

    /** @var DayOfWeek */
    private static $SUNDAY;


    /**
     * Private cache of all the constants.
     * @var DayOfWeek[]
     */
    private static $ENUMS;

    /** @var int */
    private $ordinal;
    /** @var string */
    private $name;

    private function __construct($ordinal, $name)
    {
        $this->ordinal = $ordinal;
        $this->name = $name;
    }

    //-----------------------------------------------------------------------
    /**
     * Obtains an instance of {@code DayOfWeek} from an {@code int} value.
     * <p>
     * {@code DayOfWeek} is an enum representing the 7 days of the week.
     * This factory allows the enum to be obtained from the {@code int} value.
     * The {@code int} value follows the ISO-8601 standard, from 1 (Monday) to 7 (Sunday).
     *
     * @param int $dayOfWeek the day-of-week to represent, from 1 (Monday) to 7 (Sunday)
     * @return DayOfWeek the day-of-week singleton, not null
     * @throws DateTimeException if the day-of-week is invalid
     */
    public static function of($dayOfWeek)
    {
        if ($dayOfWeek < 1 || $dayOfWeek > 7) {
            throw new DateTimeException("Invalid value for DayOfWeek: " . $dayOfWeek);
        }
        return self::$ENUMS[$dayOfWeek - 1];
    }

    //-----------------------------------------------------------------------
    /**
     * Obtains an instance of {@code DayOfWeek} from a temporal object.
     * <p>
     * This obtains a day-of-week based on the specified temporal.
     * A {@code TemporalAccessor} represents an arbitrary set of date and time information,
     * which this factory converts to an instance of {@code DayOfWeek}.
     * <p>
     * The conversion extracts the {@link ChronoField#DAY_OF_WEEK DAY_OF_WEEK} field.
     * <p>
     * This method matches the signature of the functional interface {@link TemporalQuery}
     * allowing it to be used as a query via method reference, {@code DayOfWeek::from}.
     *
     * @param TemporalAccessor $temporal the temporal object to convert, not null
     * @return DayOfWeek the day-of-week, not null
     * @throws DateTimeException if unable to convert to a {@code DayOfWeek}
     */
    public static function from(TemporalAccessor $temporal)
    {
        if ($temporal instanceof DayOfWeek) {
            return $temporal;
        }
        try {
            return self::of($temporal->get(ChronoField::DAY_OF_WEEK()));
        } catch (DateTimeException $ex) {
            throw new DateTimeException("Unable to obtain DayOfWeek from TemporalAccessor: " .
                $temporal . " of type " . get_class($temporal), $ex);
        }
    }

    /**
     * @return DayOfWeek[]
     */
    public static function values()
    {
        return self::$ENUMS;
    }


    public static function valueOf($string)
    {
        switch ($string) {
            case 'MONDAY':
                return self::MONDAY();
            case 'TUESDAY':
                return self::TUESDAY();
            case 'WEDNESDAY':
                return self::WEDNESDAY();
            case 'THURSDAY':
                return self::THURSDAY();
            case 'FRIDAY':
                return self::FRIDAY();
            case 'SATURDAY':
                return self::SATURDAY();
            case 'SUNDAY':
                return self::SUNDAY();
        }

        throw new \InvalidArgumentException();
    }

    //-----------------------------------------------------------------------
    /**
     * Gets the day-of-week {@code int} value.
     * <p>
     * The values are numbered following the ISO-8601 standard, from 1 (Monday) to 7 (Sunday).
     * See {@link java.time.temporal.WeekFields#dayOfWeek()} for localized week-numbering.
     *
     * @return int the day-of-week, from 1 (Monday) to 7 (Sunday)
     */
    public function getValue()
    {
        return $this->ordinal + 1;
    }

    //-----------------------------------------------------------------------
    /**
     * Gets the textual representation, such as 'Mon' or 'Friday'.
     * <p>
     * This returns the textual name used to identify the day-of-week,
     * suitable for presentation to the user.
     * The parameters control the style of the returned text and the locale.
     * <p>
     * If no textual mapping is found then the {@link #getValue() numeric value} is returned.
     *
     * @param TextStyle $style the length of the text required, not null
     * @param Locale $locale the locale to use, not null
     * @return String the text value of the day-of-week, not null
     */
    public function getDisplayName(TextStyle $style, Locale $locale)
    {
        return (new DateTimeFormatterBuilder())->appendText2(ChronoField::DAY_OF_WEEK(), $style)->toFormatter2($locale)->format($this);
    }

    //-----------------------------------------------------------------------
    /**
     * Checks if the specified field is supported.
     * <p>
     * This checks if this day-of-week can be queried for the specified field.
     * If false, then calling the {@link #range(TemporalField) range} and
     * {@link #get(TemporalField) get} methods will throw an exception.
     * <p>
     * If the field is {@link ChronoField#DAY_OF_WEEK DAY_OF_WEEK} then
     * this method returns true.
     * All other {@code ChronoField} instances will return false.
     * <p>
     * If the field is not a {@code ChronoField}, then the result of this method
     * is obtained by invoking {@code TemporalField.isSupportedBy(TemporalAccessor)}
     * passing {@code this} as the argument.
     * Whether the field is supported is determined by the field.
     *
     * @param TemporalField $field the field to check, null returns false
     * @return boolean true if the field is supported on this day-of-week, false if not
     */
    public function isSupported(TemporalField $field)
    {
        if ($field instanceof ChronoField) {
            return $field == ChronoField::DAY_OF_WEEK();
        }
        return $field !== null && $field->isSupportedBy($this);
    }

    /**
     * Gets the range of valid values for the specified field.
     * <p>
     * The range object expresses the minimum and maximum valid values for a field.
     * This day-of-week is used to enhance the accuracy of the returned range.
     * If it is not possible to return the range, because the field is not supported
     * or for some other reason, an exception is thrown.
     * <p>
     * If the field is {@link ChronoField#DAY_OF_WEEK DAY_OF_WEEK} then the
     * range of the day-of-week, from 1 to 7, will be returned.
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
        if ($field == ChronoField::DAY_OF_WEEK()) {
            return $field->range();
        }
        return parent::range($field);
    }

    /**
     * Gets the value of the specified field from this day-of-week as an {@code int}.
     * <p>
     * This queries this day-of-week for the value of the specified field.
     * The returned value will always be within the valid range of values for the field.
     * If it is not possible to return the value, because the field is not supported
     * or for some other reason, an exception is thrown.
     * <p>
     * If the field is {@link ChronoField#DAY_OF_WEEK DAY_OF_WEEK} then the
     * value of the day-of-week, from 1 to 7, will be returned.
     * All other {@code ChronoField} instances will throw an {@code UnsupportedTemporalTypeException}.
     * <p>
     * If the field is not a {@code ChronoField}, then the result of this method
     * is obtained by invoking {@code TemporalField.getFrom(TemporalAccessor)}
     * passing {@code this} as the argument. Whether the value can be obtained,
     * and what the value represents, is determined by the field.
     *
     * @param TemporalField $field the field to get, not null
     * @return int the value for the field, within the valid range of values
     * @throws DateTimeException if a value for the field cannot be obtained or
     *         the value is outside the range of valid values for the field
     * @throws UnsupportedTemporalTypeException if the field is not supported or
     *         the range of values exceeds an {@code int}
     * @throws ArithmeticException if numeric overflow occurs
     */
    public function get(TemporalField $field)
    {
        if ($field == ChronoField::DAY_OF_WEEK()) {
            return $this->getValue();
        }
        return parent::get($field);
    }

    /**
     * Gets the value of the specified field from this day-of-week as a {@code long}.
     * <p>
     * This queries this day-of-week for the value of the specified field.
     * If it is not possible to return the value, because the field is not supported
     * or for some other reason, an exception is thrown.
     * <p>
     * If the field is {@link ChronoField#DAY_OF_WEEK DAY_OF_WEEK} then the
     * value of the day-of-week, from 1 to 7, will be returned.
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
        if ($field == ChronoField::DAY_OF_WEEK()) {
            return $this->getValue();
        } else if ($field instanceof ChronoField) {
            throw new UnsupportedTemporalTypeException("Unsupported field: " . $field);
        }
        return $field->getFrom($this);
    }

    //-----------------------------------------------------------------------
    /**
     * Returns the day-of-week that is the specified number of days after this one.
     * <p>
     * The calculation rolls around the end of the week from Sunday to Monday.
     * The specified period may be negative.
     * <p>
     * This instance is immutable and unaffected by this method call.
     *
     * @param int $days the days to add, positive or negative
     * @return DayOfWeek the resulting day-of-week, not null
     */
    public function plus($days)
    {
        return self::$ENUMS[($this->ordinal + (($days % 7) + 7)) % 7];
    }

    /**
     * Returns the day-of-week that is the specified number of days before this one.
     * <p>
     * The calculation rolls around the start of the year from Monday to Sunday.
     * The specified period may be negative.
     * <p>
     * This instance is immutable and unaffected by this method call.
     *
     * @param int $days the days to subtract, positive or negative
     * @return DayOfWeek the resulting day-of-week, not null
     */
    public function minus($days)
    {
        return $this->plus(-($days % 7));
    }

    //-----------------------------------------------------------------------
    /**
     * Queries this day-of-week using the specified query.
     * <p>
     * This queries this day-of-week using the specified query strategy object.
     * The {@code TemporalQuery} object defines the logic to be used to
     * obtain the result. Read the documentation of the query to understand
     * what the result of this method will be.
     * <p>
     * The result of this method is obtained by invoking the
     * {@link TemporalQuery#queryFrom(TemporalAccessor)} method on the
     * specified query passing {@code this} as the argument.
     *
     * @param <R> the type of the result
     * @param query TemporalQuery the query to invoke, not null
     * @return mixed query result, null may be returned (defined by the query)
     * @throws DateTimeException if unable to query (defined by the query)
     * @throws ArithmeticException if numeric overflow occurs (defined by the query)
     */
    public function query(TemporalQuery $query)
    {
        if ($query == TemporalQueries::precision()) {
            return ChronoUnit::DAYS();
        }
        return parent::query($query);
    }

    /**
     * Adjusts the specified temporal object to have this day-of-week.
     * <p>
     * This returns a temporal object of the same observable type as the input
     * with the day-of-week changed to be the same as this.
     * <p>
     * The adjustment is equivalent to using {@link Temporal#with(TemporalField, long)}
     * passing {@link ChronoField#DAY_OF_WEEK} as the field.
     * Note that this adjusts forwards or backwards within a Monday to Sunday week.
     * See {@link java.time.temporal.WeekFields#dayOfWeek()} for localized week start days.
     * See {@code TemporalAdjuster} for other adjusters with more control,
     * such as {@code next(MONDAY)}.
     * <p>
     * In most cases, it is clearer to reverse the calling pattern by using
     * {@link Temporal#with(TemporalAdjuster)}:
     * <pre>
     *   // these two lines are equivalent, but the second approach is recommended
     *   temporal = thisDayOfWeek.adjustInto(temporal);
     *   temporal = temporal.with(thisDayOfWeek);
     * </pre>
     * <p>
     * For example, given a date that is a Wednesday, the following are output:
     * <pre>
     *   dateOnWed.with(MONDAY);     // two days earlier
     *   dateOnWed.with(TUESDAY);    // one day earlier
     *   dateOnWed.with(WEDNESDAY);  // same date
     *   dateOnWed.with(THURSDAY);   // one day later
     *   dateOnWed.with(FRIDAY);     // two days later
     *   dateOnWed.with(SATURDAY);   // three days later
     *   dateOnWed.with(SUNDAY);     // four days later
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
        return $temporal->with(ChronoField::DAY_OF_WEEK(), $this->getValue());
    }

    public function __toString()
    {
        return $this->name;
    }

    /**
     * Serialize into an ISO integer for JSON representation
     * @return int
     */
    public function jsonSerialize()
    {
        return $this->getValue();
    }
}

DayOfWeek::init();
