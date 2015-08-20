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

use Php\Time\Chrono\Chronology;
use Php\Time\Chrono\IsoChronology;
use Php\Time\Format\DateTimeFormatter;
use Php\Time\Helper\Math;
use Php\Time\Temporal\ChronoField;
use Php\Time\Temporal\Temporal;
use Php\Time\Temporal\TemporalAccessor;
use Php\Time\Temporal\TemporalAccessorDefaults;
use Php\Time\Temporal\TemporalAdjuster;
use Php\Time\Temporal\TemporalField;
use Php\Time\Temporal\TemporalQueries;
use Php\Time\Temporal\TemporalQuery;
use Php\Time\Temporal\UnsupportedTemporalTypeException;
use Php\Time\Temporal\ValueRange;

/**
 * A month-day in the ISO-8601 calendar system, such as {@code --12-03}.
 * <p>
 * {@code MonthDay} is an immutable date-time object that represents the combination
 * of a month and day-of-month. Any field that can be derived from a month and day,
 * such as quarter-of-year, can be obtained.
 * <p>
 * This class does not store or represent a year, time or time-zone.
 * For example, the value "December 3rd" can be stored in a {@code MonthDay}.
 * <p>
 * Since a {@code MonthDay} does not possess a year, the leap day of
 * February 29th is considered valid.
 * <p>
 * This class implements {@link TemporalAccessor} rather than {@link Temporal}.
 * This is because it is not possible to define whether February 29th is valid or not
 * without external information, preventing the implementation of plus/minus.
 * Related to this, {@code MonthDay} only provides access to query and set the fields
 * {@code MONTH_OF_YEAR} and {@code DAY_OF_MONTH}.
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
 * {@code MonthDay} may have unpredictable results and should be avoided.
 * The {@code equals} method should be used for comparisons.
 *
 * @implSpec
 * This class is immutable and thread-safe.
 *
 * @since 1.8
 */
final class MonthDay implements TemporalAccessor, TemporalAdjuster
{
    public static function init()
    {
        self::$PARSER = (new DateTimeFormatterBuilder())
           ->appendLiteral("--")
        ->appendValue(ChronoField::MONTH_OF_YEAR(), 2)
        ->appendLiteral('-')
        ->appendValue(ChronoField::DAY_OF_MONTH(), 2)
        ->toFormatter();
    }

    /**
     * Parser.
     * @var DateTimeFormatter
     */
    private static $PARSER;

    /**
     * The month-of-year, not null.
     * @var int
     */
    private $month;
    /**
     * The day-of-month.
     * @var int
     */
    private $day;

    //-----------------------------------------------------------------------
    /**
     * Obtains the current month-day from the system clock in the default time-zone.
     * <p>
     * This will query the {@link Clock#systemDefaultZone() system clock} in the default
     * time-zone to obtain the current month-day.
     * <p>
     * Using this method will prevent the ability to use an alternate clock for testing
     * because the clock is hard-coded.
     *
     * @return MonthDay the current month-day using the system clock and default time-zone, not null
     */
    public static function now()
    {
        return self::nowOf(Clock::systemDefaultZone());
    }

    /**
     * Obtains the current month-day from the system clock in the specified time-zone.
     * <p>
     * This will query the {@link Clock#system(ZoneId) system clock} to obtain the current month-day.
     * Specifying the time-zone avoids dependence on the default time-zone.
     * <p>
     * Using this method will prevent the ability to use an alternate clock for testing
     * because the clock is hard-coded.
     *
     * @param $zone ZoneId  the zone ID to use, not null
     * @return MonthDay the current month-day using the system clock, not null
     */
    public static function nowIn(ZoneId $zone)
    {
        return self::nowOf(Clock::system($zone));
    }

    /**
     * Obtains the current month-day from the specified clock.
     * <p>
     * This will query the specified clock to obtain the current month-day.
     * Using this method allows the use of an alternate clock for testing.
     * The alternate clock may be introduced using {@link Clock dependency injection}.
     *
     * @param $clock Clock the clock to use, not null
     * @return MonthDay the current month-day, not null
     */
    public
    static function nowOf(Clock $clock)
    {
        $now = LocalDate::nowOf($clock);  // called once
        return MonthDay::of($now->getMonth(), $now->getDayOfMonth());
    }

//-----------------------------------------------------------------------
    /**
     * Obtains an instance of {@code MonthDay}.
     * <p>
     * The day-of-month must be valid for the month within a leap year.
     * Hence, for February, day 29 is valid.
     * <p>
     * For example, passing in April and day 31 will throw an exception, as
     * there can never be April 31st in any year. By contrast, passing in
     * February 29th is permitted, as that month-day can sometimes be valid.
     *
     * @param $month Month the month-of-year to represent, not null
     * @param $dayOfMonth int the day-of-month to represent, from 1 to 31
     * @return MonthDay the month-day, not null
     * @throws DateTimeException if the value of any field is out of range,
     *  or if the day-of-month is invalid for the month
     */
    public
    static function of(Month $month, $dayOfMonth)
    {
        ChronoField::DAY_OF_MONTH()->checkValidValue($dayOfMonth);
        if ($dayOfMonth > $month->maxLength()) {
            throw new DateTimeException("Illegal value for DayOfMonth field, value " . $dayOfMonth .
                " is not valid for month " . $month->name());
        }

        return new MonthDay($month->getValue(), $dayOfMonth);
    }

    /**
     * Obtains an instance of {@code MonthDay}.
     * <p>
     * The day-of-month must be valid for the month within a leap year.
     * Hence, for month 2 (February), day 29 is valid.
     * <p>
     * For example, passing in month 4 (April) and day 31 will throw an exception, as
     * there can never be April 31st in any year. By contrast, passing in
     * February 29th is permitted, as that month-day can sometimes be valid.
     *
     * @param $month int the month-of-year to represent, from 1 (January) to 12 (December)
     * @param $dayOfMonth int the day-of-month to represent, from 1 to 31
     * @return MonthDay the month-day, not null
     * @throws DateTimeException if the value of any field is out of range,
     *  or if the day-of-month is invalid for the month
     */
    public static function ofNumerical($month, $dayOfMonth)
    {
        return self::of(Month::of($month), $dayOfMonth);
    }

//-----------------------------------------------------------------------
    /**
     * Obtains an instance of {@code MonthDay} from a temporal object.
     * <p>
     * This obtains a month-day based on the specified temporal.
     * A {@code TemporalAccessor} represents an arbitrary set of date and time information,
     * which this factory converts to an instance of {@code MonthDay}.
     * <p>
     * The conversion extracts the {@link ChronoField#MONTH_OF_YEAR MONTH_OF_YEAR} and
     * {@link ChronoField#DAY_OF_MONTH DAY_OF_MONTH} fields.
     * The extraction is only permitted if the temporal object has an ISO
     * chronology, or can be converted to a {@code LocalDate}.
     * <p>
     * This method matches the signature of the functional interface {@link TemporalQuery}
     * allowing it to be used as a query via method reference, {@code MonthDay::from}.
     *
     * @param $temporal TemporalAccessor the temporal object to convert, not null
     * @return MonthDay the month-day, not null
     * @throws DateTimeException if unable to convert to a {@code MonthDay}
     */
    public
    static function from(TemporalAccessor $temporal)
    {
        if ($temporal instanceof MonthDay) {
            return $temporal;
        }

        try {
            if (IsoChronology::INSTANCE()->equals(Chronology::from($temporal)) == false) {
                $temporal = LocalDate::from($temporal);
            }
            return self::ofNumerical($temporal->get(ChronoField::MONTH_OF_YEAR()), $temporal->get(ChronoField::DAY_OF_MONTH()));
        } catch (DateTimeException $ex) {
            throw new DateTimeException("Unable to obtain MonthDay from TemporalAccessor: " .
                $temporal . " of type " . get_class($temporal), $ex);
        }
    }

    //-----------------------------------------------------------------------
    /**
     * Obtains an instance of {@code MonthDay} from a text string such as {@code --12-03}.
     * <p>
     * The string must represent a valid month-day.
     * The format is {@code --MM-dd}.
     *
     * @param $text string the text to parse such as "--12-03", not null
     * @return MonthDay the parsed month-day, not null
     * @throws DateTimeParseException if the text cannot be parsed
     */
    public static function parse($text)
    {
        return self::parseWith($text, self::PARSER);
    }

    /**
     * Obtains an instance of {@code MonthDay} from a text string using a specific formatter.
     * <p>
     * The text is parsed using the formatter, returning a month-day.
     *
     * @param $text string the text to parse, not null
     * @param $formatter DateTimeFormatter the formatter to use, not null
     * @return MonthDay the parsed month-day, not null
     * @throws DateTimeParseException if the text cannot be parsed
     */
    public
    static function parseWith($text, DateTimeFormatter $formatter)
    {
        return $formatter->parse($text, MonthDay::from);
    }

//-----------------------------------------------------------------------
    /**
     * Constructor, previously validated.
     *
     * @param $month int the month-of-year to represent, validated from 1 to 12
     * @param $dayOfMonth int the day-of-month to represent, validated from 1 to 29-31
     */
    private function __construct($month, $dayOfMonth)
    {
        $this->month = $month;
        $this->day = $dayOfMonth;
    }

//-----------------------------------------------------------------------
    /**
     * Checks if the specified field is supported.
     * <p>
     * This checks if this month-day can be queried for the specified field.
     * If false, then calling the {@link #range(TemporalField) range} and
     * {@link #get(TemporalField) get} methods will throw an exception.
     * <p>
     * If the field is a {@link ChronoField} then the query is implemented here.
     * The supported fields are:
     * <ul>
     * <li>{@code MONTH_OF_YEAR}
     * <li>{@code YEAR}
     * </ul>
     * All other {@code ChronoField} instances will return false.
     * <p>
     * If the field is not a {@code ChronoField}, then the result of this method
     * is obtained by invoking {@code TemporalField.isSupportedBy(TemporalAccessor)}
     * passing {@code this} as the argument.
     * Whether the field is supported is determined by the field.
     *
     * @param $field TemporalField the field to check, null returns false
     * @return bool true if the field is supported on this month-day, false if not
     */
    public function isSupported(TemporalField $field)
    {
        if ($field instanceof ChronoField) {
            return $field == ChronoField::MONTH_OF_YEAR() || $field == ChronoField::DAY_OF_MONTH();
        }

        return $field != null && $field->isSupportedBy($this);
    }

    /**
     * Gets the range of valid values for the specified field.
     * <p>
     * The range object expresses the minimum and maximum valid values for a field.
     * This month-day is used to enhance the accuracy of the returned range.
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
        if ($field == ChronoField::MONTH_OF_YEAR()) {
            return $field->range();
        } else
            if ($field == ChronoField::DAY_OF_MONTH()) {
                return ValueRange::ofVariable(1, $this->getMonth()->minLength(), $this->getMonth()->maxLength());
            }
        return TemporalAccessorDefaults::range($this, $field);
    }

    /**
     * Gets the value of the specified field from this month-day as an {@code int}.
     * <p>
     * This queries this month-day for the value of the specified field.
     * The returned value will always be within the valid range of values for the field.
     * If it is not possible to return the value, because the field is not supported
     * or for some other reason, an exception is thrown.
     * <p>
     * If the field is a {@link ChronoField} then the query is implemented here.
     * The {@link #isSupported(TemporalField) supported fields} will return valid
     * values based on this month-day.
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
        return $this->range($field)->checkValidIntValue($this->getLong($field), $field);
    }

    /**
     * Gets the value of the specified field from this month-day as a {@code long}.
     * <p>
     * This queries this month-day for the value of the specified field.
     * If it is not possible to return the value, because the field is not supported
     * or for some other reason, an exception is thrown.
     * <p>
     * If the field is a {@link ChronoField} then the query is implemented here.
     * The {@link #isSupported(TemporalField) supported fields} will return valid
     * values based on this month-day.
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
                // alignedDOW and alignedWOM not supported because they cannot be set in with()
                case ChronoField::DAY_OF_MONTH():
                    return $this->day;
                case ChronoField::MONTH_OF_YEAR():
                    return $this->month;
            }

            throw new UnsupportedTemporalTypeException("Unsupported field: " . $field);
        }
        return $field->getFrom($this);
    }

    //-----------------------------------------------------------------------
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
        return $this->month;
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
        return Month::of($this->month);
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
        return $this->day;
    }

//-----------------------------------------------------------------------
    /**
     * Checks if the year is valid for this month-day.
     * <p>
     * This method checks whether this month and day and the input year form
     * a valid date. This can only return false for February 29th.
     *
     * @param $year int the year to validate
     * @return bool true if the year is valid for this month-day
     * @see Year#isValidMonthDay(MonthDay)
     */
    public
    function isValidYear($year)
    {
        return ($this->day == 29 && $this->month == 2 && Year::isLeapYear($year) == false) == false;
    }

//-----------------------------------------------------------------------
    /**
     * Returns a copy of this {@code MonthDay} with the month-of-year altered.
     * <p>
     * This returns a month-day with the specified month.
     * If the day-of-month is invalid for the specified month, the day will
     * be adjusted to the last valid day-of-month.
     * <p>
     * This instance is immutable and unaffected by this method call.
     *
     * @param $month int the month-of-year to set in the returned month-day, from 1 (January) to 12 (December)
     * @return MonthDay a {@code MonthDay} based on this month-day with the requested month, not null
     * @throws DateTimeException if the month-of-year value is invalid
     */
    public function withMonth($month)
    {
        return self::with(Month::of($month));
    }

    /**
     * Returns a copy of this {@code MonthDay} with the month-of-year altered.
     * <p>
     * This returns a month-day with the specified month.
     * If the day-of-month is invalid for the specified month, the day will
     * be adjusted to the last valid day-of-month.
     * <p>
     * This instance is immutable and unaffected by this method call.
     *
     * @param $month Month the month-of-year to set in the returned month-day, not null
     * @return MonthDay a {@code MonthDay} based on this month-day with the requested month, not null
     */
    public function with(Month $month)
    {
        if ($month->getValue() == $this->month) {
            return $this;
        }

        $day = Math::min($this->day, $month->maxLength());
        return new MonthDay($month->getValue(), $day);
    }

    /**
     * Returns a copy of this {@code MonthDay} with the day-of-month altered.
     * <p>
     * This returns a month-day with the specified day-of-month.
     * If the day-of-month is invalid for the month, an exception is thrown.
     * <p>
     * This instance is immutable and unaffected by this method call.
     *
     * @param $dayOfMonth int the day-of-month to set in the return month-day, from 1 to 31
     * @return MonthDay a {@code MonthDay} based on this month-day with the requested day, not null
     * @throws DateTimeException if the day-of-month value is invalid,
     *  or if the day-of-month is invalid for the month
     */
    public function withDayOfMonth($dayOfMonth)
    {
        if ($dayOfMonth == $this->day) {
            return $this;
        }

        return self::ofNumerical($this->month, $dayOfMonth);
    }

//-----------------------------------------------------------------------
    /**
     * Queries this month-day using the specified query.
     * <p>
     * This queries this month-day using the specified query strategy object.
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
        if ($query == TemporalQueries::chronology()) {
            return IsoChronology::INSTANCE();
        }

        return TemporalAccessorDefaults::query($this, $query);
    }

    /**
     * Adjusts the specified temporal object to have this month-day.
     * <p>
     * This returns a temporal object of the same observable type as the input
     * with the month and day-of-month changed to be the same as this.
     * <p>
     * The adjustment is equivalent to using {@link Temporal#with(TemporalField, long)}
     * twice, passing {@link ChronoField#MONTH_OF_YEAR} and
     * {@link ChronoField#DAY_OF_MONTH} as the fields.
     * If the specified temporal object does not use the ISO calendar system then
     * a {@code DateTimeException} is thrown.
     * <p>
     * In most cases, it is clearer to reverse the calling pattern by using
     * {@link Temporal#with(TemporalAdjuster)}:
     * <pre>
     *   // these two lines are equivalent, but the second approach is recommended
     *   temporal = thisMonthDay.adjustInto(temporal);
     *   temporal = temporal.with(thisMonthDay);
     * </pre>
     * <p>
     * This instance is immutable and unaffected by this method call.
     *
     * @param $temporal Temporal the target object to be adjusted, not null
     * @return Temporal the adjusted object, not null
     * @throws DateTimeException if unable to make the adjustment
     * @throws ArithmeticException if numeric overflow occurs
     */
    public function  adjustInto(Temporal $temporal)
    {
        if (Chronology::from($temporal)->equals(IsoChronology::INSTANCE()) == false) {
            throw new DateTimeException("Adjustment only supported on ISO date-time");
        }

        $temporal = $temporal->with(ChronoField::MONTH_OF_YEAR(), $this->month);
        return $temporal->with(ChronoField::DAY_OF_MONTH(), Math::min($temporal->range(ChronoField::DAY_OF_MONTH())->getMaximum(), $this->day));
    }

    /**
     * Formats this month-day using the specified formatter.
     * <p>
     * This month-day will be passed to the formatter to produce a string.
     *
     * @param $formatter DateTimeFormatter the formatter to use, not null
     * @return string the formatted month-day string, not null
     * @throws DateTimeException if an error occurs during printing
     */
    public function format(DateTimeFormatter $formatter)
    {
        return $formatter->format($this);
    }

//-----------------------------------------------------------------------
    /**
     * Combines this month-day with a year to create a {@code LocalDate}.
     * <p>
     * This returns a {@code LocalDate} formed from this month-day and the specified year.
     * <p>
     * A month-day of February 29th will be adjusted to February 28th in the resulting
     * date if the year is not a leap year.
     * <p>
     * This instance is immutable and unaffected by this method call.
     *
     * @param $year int the year to use, from MIN_YEAR to MAX_YEAR
     * @return LocalDate the local date formed from this month-day and the specified year, not null
     * @throws DateTimeException if the year is outside the valid range of years
     */
    public
    function atYear($year)
    {
        return LocalDate::ofNumerical($year, $this->month, self::isValidYear($year) ? $this->day : 28);
    }

//-----------------------------------------------------------------------
    /**
     * Compares this month-day to another month-day.
     * <p>
     * The comparison is based first on value of the month, then on the value of the day.
     * It is "consistent with equals", as defined by {@link Comparable}.
     *
     * @param $other MonthDay the other month-day to compare to, not null
     * @return int the comparator value, negative if less, positive if greater
     */
    public function compareTo(MonthDay $other)
    {
        $cmp = ($this->month - $other->month);
        if ($cmp == 0) {
            $cmp = ($this->day - $other->day);
        }

        return $cmp;
    }

    /**
     * Checks if this month-day is after the specified month-day.
     *
     * @param $other MonthDay the other month-day to compare to, not null
     * @return bool true if this is after the specified month-day
     */
    public
    function isAfter(MonthDay $other)
    {
        return $this->compareTo($other) > 0;
    }

    /**
     * Checks if this month-day is before the specified month-day.
     *
     * @param $other MonthDay the other month-day to compare to, not null
     * @return bool true if this point is before the specified month-day
     */
    public
    function isBefore(MonthDay $other)
    {
        return $this->compareTo($other) < 0;
    }

//-----------------------------------------------------------------------
    /**
     * Checks if this month-day is equal to another month-day.
     * <p>
     * The comparison is based on the time-line position of the month-day within a year.
     *
     * @param $obj mixed the object to check, null returns false
     * @return bool true if this is equal to the other month-day
     */
    public function equals($obj)
    {
        if ($this === $obj) {
            return true;
        }

        if ($obj instanceof MonthDay) {
            $other = $obj;
            return $this->month == $other->month && $this->day == $other->day;
        }
        return false;
    }

//-----------------------------------------------------------------------
    /**
     * Outputs this month-day as a {@code String}, such as {@code --12-03}.
     * <p>
     * The output will be in the format {@code --MM-dd}:
     *
     * @return string a string representation of this month-day, not null
     */
    public function __toString()
    {
        return "--"
        . $this->month < 10 ? "0" : "" . $this->month
        . $this->day < 10 ? "-0" : "-" . $this->day;
    }
}
