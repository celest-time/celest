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
use Php\Time\Helper\Long;
use Php\Time\Helper\Math;
use Php\Time\Temporal\ChronoField;
use Php\Time\Temporal\ChronoUnit;
use Php\Time\Temporal\Temporal;
use Php\Time\Temporal\TemporalAccessor;
use Php\Time\Temporal\TemporalAccessorDefaults;
use Php\Time\Temporal\TemporalAdjuster;
use Php\Time\Temporal\TemporalAmount;
use Php\Time\Temporal\TemporalField;
use Php\Time\Temporal\TemporalQueries;
use Php\Time\Temporal\TemporalQuery;
use Php\Time\Temporal\TemporalUnit;
use Php\Time\Temporal\ValueRange;

/**
 * A year-month in the ISO-8601 calendar system, such as {@code 2007-12}.
 * <p>
 * {@code YearMonth} is an immutable date-time object that represents the combination
 * of a year and month. Any field that can be derived from a year and month, such as
 * quarter-of-year, can be obtained.
 * <p>
 * This class does not store or represent a day, time or time-zone.
 * For example, the value "October 2007" can be stored in a {@code YearMonth}.
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
 * {@code YearMonth} may have unpredictable results and should be avoided.
 * The {@code equals} method should be used for comparisons.
 *
 * @implSpec
 * This class is immutable and thread-safe.
 *
 * @since 1.8
 */
final class YearMonth implements Temporal, TemporalAdjuster
{
    public function init()
    {
        self::$PARSER = (new DateTimeFormatterBuilder())
            ->appendValue(ChronoField::YEAR(), 4, 10, SignStyle::EXCEEDS_PAD)
            ->appendLiteral('-')
            ->appendValue(ChronoField::MONTH_OF_YEAR(), 2)
            ->toFormatter();
    }

    /**
     * Parser.
     * @var DateTimeFormatter
     */
    private static $PARSER;

    /**
     * The year.
     * @var int
     */
    private $year;
    /**
     * The month-of-year, not null.
     * @var int
     */
    private $month;

    //-----------------------------------------------------------------------
    /**
     * Obtains the current year-month from the system clock in the default time-zone.
     * <p>
     * This will query the {@link Clock#systemDefaultZone() system clock} in the default
     * time-zone to obtain the current year-month.
     * <p>
     * Using this method will prevent the ability to use an alternate clock for testing
     * because the clock is hard-coded.
     *
     * @return YearMonth the current year-month using the system clock and default time-zone, not null
     */
    public static function now()
    {
        return self::now(Clock::systemDefaultZone());
    }

    /**
     * Obtains the current year-month from the system clock in the specified time-zone.
     * <p>
     * This will query the {@link Clock#system(ZoneId) system clock} to obtain the current year-month.
     * Specifying the time-zone avoids dependence on the default time-zone.
     * <p>
     * Using this method will prevent the ability to use an alternate clock for testing
     * because the clock is hard-coded.
     *
     * @param $zone ZoneId the zone ID to use, not null
     * @return YearMonth the current year-month using the system clock, not null
     */
    public
    static function now(ZoneId $zone)
    {
        return self::now(Clock::system($zone));
    }

    /**
     * Obtains the current year-month from the specified clock.
     * <p>
     * This will query the specified clock to obtain the current year-month.
     * Using this method allows the use of an alternate clock for testing.
     * The alternate clock may be introduced using {@link Clock dependency injection}.
     *
     * @param $clock Clock the clock to use, not null
     * @return YearMonth the current year-month, not null
     */
    public
    static function now(Clock $clock)
    {
        $now = LocalDate::now($clock);  // called once
        return YearMonth::of($now->getYear(), $now->getMonth());
    }

//-----------------------------------------------------------------------
    /**
     * Obtains an instance of {@code YearMonth} from a year and month.
     *
     * @param $year int the year to represent, from MIN_YEAR to MAX_YEAR
     * @param $month Month the month-of-year to represent, not null
     * @return YearMonth the year-month, not null
     * @throws DateTimeException if the year value is invalid
     */
    public
    static function of($year, Month $month)
    {
        return self::of($year, $month->getValue());
    }

    /**
     * Obtains an instance of {@code YearMonth} from a year and month.
     *
     * @param $year int the year to represent, from MIN_YEAR to MAX_YEAR
     * @param $month int the month-of-year to represent, from 1 (January) to 12 (December)
     * @return YearMonth the year-month, not null
     * @throws DateTimeException if either field value is invalid
     */
    public
    static function of($year, $month)
    {
        ChronoField::YEAR()->checkValidValue($year);
        ChronoField::MONTH_OF_YEAR()->checkValidValue($month);
        return new YearMonth($year, $month);
    }

//-----------------------------------------------------------------------
    /**
     * Obtains an instance of {@code YearMonth} from a temporal object.
     * <p>
     * This obtains a year-month based on the specified temporal.
     * A {@code TemporalAccessor} represents an arbitrary set of date and time information,
     * which this factory converts to an instance of {@code YearMonth}.
     * <p>
     * The conversion extracts the {@link ChronoField#YEAR YEAR} and
     * {@link ChronoField#MONTH_OF_YEAR MONTH_OF_YEAR} fields.
     * The extraction is only permitted if the temporal object has an ISO
     * chronology, or can be converted to a {@code LocalDate}.
     * <p>
     * This method matches the signature of the functional interface {@link TemporalQuery}
     * allowing it to be used as a query via method reference, {@code YearMonth::from}.
     *
     * @param $temporal TemporalAccessor the temporal object to convert, not null
     * @return YearMonth the year-month, not null
     * @throws DateTimeException if unable to convert to a {@code YearMonth}
     */
    public
    static function from(TemporalAccessor $temporal)
    {
        if ($temporal instanceof YearMonth) {
            return $temporal;
        }

        try {
            if (IsoChronology::INSTANCE()->equals(Chronology::from($temporal)) == false) {
                $temporal = LocalDate::from($temporal);
            }
            return self::of($temporal->get(ChronoField::YEAR()), $temporal->get(ChronoField::MONTH_OF_YEAR()));
        } catch (DateTimeException $ex) {
            throw new DateTimeException("Unable to obtain YearMonth from TemporalAccessor: " .
                $temporal . " of type " . get_class($temporal), $ex);
        }
    }

    //-----------------------------------------------------------------------
    /**
     * Obtains an instance of {@code YearMonth} from a text string such as {@code 2007-12}.
     * <p>
     * The string must represent a valid year-month.
     * The format must be {@code uuuu-MM}.
     * Years outside the range 0000 to 9999 must be prefixed by the plus or minus symbol.
     *
     * @param $text string the text to parse such as "2007-12", not null
     * @return YearMonth the parsed year-month, not null
     * @throws DateTimeParseException if the text cannot be parsed
     */
    public static function parse($text)
    {
        return self::parse($text, self::$PARSER);
    }

    /**
     * Obtains an instance of {@code YearMonth} from a text string using a specific formatter.
     * <p>
     * The text is parsed using the formatter, returning a year-month.
     *
     * @param $text string the text to parse, not null
     * @param $formatter DateTimeFormatter the formatter to use, not null
     * @return YearMonth the parsed year-month, not null
     * @throws DateTimeParseException if the text cannot be parsed
     */
    public
    static function parse($text, DateTimeFormatter $formatter)
    {
        return $formatter->parse($text, YearMonth::from);
    }

//-----------------------------------------------------------------------
    /**
     * Constructor.
     *
     * @param $year int the year to represent, validated from MIN_YEAR to MAX_YEAR
     * @param $month int the month-of-year to represent, validated from 1 (January) to 12 (December)
     */
    private
    function __construct($year, $month)
    {
        $this->year = $year;
        $this->month = $month;
    }

    /**
     * Returns a copy of this year-month with the new year and month, checking
     * to see if a new object is in fact required.
     *
     * @param $newYear int  the year to represent, validated from MIN_YEAR to MAX_YEAR
     * @param $newMonth int the month-of-year to represent, validated not null
     * @return YearMonth the year-month, not null
     */
    private function with($newYear, $newMonth)
    {
        if ($this->year == $newYear && $this->month == $newMonth) {
            return $this;
        }

        return new YearMonth($newYear, $newMonth);
    }

//-----------------------------------------------------------------------
    /**
     * Checks if the specified field is supported.
     * <p>
     * This checks if this year-month can be queried for the specified field.
     * If false, then calling the {@link #range(TemporalField) range},
     * {@link #get(TemporalField) get} and {@link #with(TemporalField, long)}
     * methods will throw an exception.
     * <p>
     * If the field is a {@link ChronoField} then the query is implemented here.
     * The supported fields are:
     * <ul>
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
     * @param $field TemporalField the field to check, null returns false
     * @return bool true if the field is supported on this year-month, false if not
     */
    public function isSupported(TemporalField $field)
    {
        if ($field instanceof ChronoField) {
            return $field == ChronoField::YEAR() || $field == ChronoField::MONTH_OF_YEAR() ||
            $field == ChronoField::PROLEPTIC_MONTH() || $field == ChronoField::YEAR_OF_ERA() || $field == ChronoField::ERA();
        }

        return $field != null && $field->isSupportedBy($this);
    }

    /**
     * Checks if the specified unit is supported.
     * <p>
     * This checks if the specified unit can be added to, or subtracted from, this year-month.
     * If false, then calling the {@link #plus(long, TemporalUnit)} and
     * {@link #minus(long, TemporalUnit) minus} methods will throw an exception.
     * <p>
     * If the unit is a {@link ChronoUnit} then the query is implemented here.
     * The supported units are:
     * <ul>
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
     * @param $unit TemporalUnit the unit to check, null returns false
     * @return bool true if the unit can be added/subtracted, false if not
     */
    public function isUnitSupported(TemporalUnit $unit)
    {
        if ($unit instanceof ChronoUnit) {
            return $unit == ChronoUnit::MONTHS() || $unit == ChronoUnit::YEARS() || $unit == ChronoUnit::DECADES() || $unit == ChronoUnit::CENTURIES() || $unit == ChronoUnit::MILLENNIA() || $unit == ChronoUnit::ERAS();
        }

        return $unit != null && $unit->isSupportedBy($this);
    }

    //-----------------------------------------------------------------------
    /**
     * Gets the range of valid values for the specified field.
     * <p>
     * The range object expresses the minimum and maximum valid values for a field.
     * This year-month is used to enhance the accuracy of the returned range.
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
        if ($field == ChronoField::YEAR_OF_ERA()) {
            return ($this->getYear() <= 0 ? ValueRange::of(1, Year::MAX_VALUE + 1) : ValueRange::of(1, Year::MAX_VALUE));
        }

        return TemporalAccessorDefaults::range($this, $field);
    }

    /**
     * Gets the value of the specified field from this year-month as an {@code int}.
     * <p>
     * This queries this year-month for the value of the specified field.
     * The returned value will always be within the valid range of values for the field.
     * If it is not possible to return the value, because the field is not supported
     * or for some other reason, an exception is thrown.
     * <p>
     * If the field is a {@link ChronoField} then the query is implemented here.
     * The {@link #isSupported(TemporalField) supported fields} will return valid
     * values based on this year-month, except {@code PROLEPTIC_MONTH} which is too
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
        return $this->range($field)->checkValidIntValue($this->getLong($field), $field);
    }

    /**
     * Gets the value of the specified field from this year-month as a {@code long}.
     * <p>
     * This queries this year-month for the value of the specified field.
     * If it is not possible to return the value, because the field is not supported
     * or for some other reason, an exception is thrown.
     * <p>
     * If the field is a {@link ChronoField} then the query is implemented here.
     * The {@link #isSupported(TemporalField) supported fields} will return valid
     * values based on this year-month.
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
                case ChronoField::MONTH_OF_YEAR():
                    return $this->month;
                case ChronoField::PROLEPTIC_MONTH():
                    return $this->getProlepticMonth();
                case ChronoField::YEAR_OF_ERA():
                    return ($this->year < 1 ? 1 - $this->year : $this->year);
                case ChronoField::YEAR():
                    return $this->year;
                case ChronoField::ERA():
                    return ($this->year < 1 ? 0 : 1);
            }

            throw new UnsupportedTemporalTypeException("Unsupported field: " . $field);
        }
        return $field->getFrom($this);
    }

    private function getProlepticMonth()
    {
        return ($this->year * 12 + $this->month - 1);
    }

//-----------------------------------------------------------------------
    /**
     * Gets the year field.
     * <p>
     * This method returns the primitive {@code int} value for the year.
     * <p>
     * The year returned by this method is proleptic as per {@code get(YEAR)}.
     *
     * @return int the year, from MIN_YEAR to MAX_YEAR
     */
    public
    function getYear()
    {
        return $this->year;
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

//-----------------------------------------------------------------------
    /**
     * Checks if the year is a leap year, according to the ISO proleptic
     * calendar system rules.
     * <p>
     * This method applies the current rules for leap years across the whole time-line.
     * In general, a year is a leap year if it is divisible by four without
     * remainder. However, years divisible by 100, are not leap years, with
     * the exception of years divisible by 400 which are.
     * <p>
     * For example, 1904 is a leap year it is divisible by 4.
     * 1900 was not a leap year as it is divisible by 100, however 2000 was a
     * leap year as it is divisible by 400.
     * <p>
     * The calculation is proleptic - applying the same rules into the far future and far past.
     * This is historically inaccurate, but is correct for the ISO-8601 standard.
     *
     * @return bool true if the year is leap, false otherwise
     */
    public function isLeapYear()
    {
        return IsoChronology::INSTANCE()->isLeapYear($this->year);
    }

    /**
     * Checks if the day-of-month is valid for this year-month.
     * <p>
     * This method checks whether this year and month and the input day form
     * a valid date.
     *
     * @param $dayOfMonth int the day-of-month to validate, from 1 to 31, invalid value returns false
     * @return bool true if the day is valid for this year-month
     */
    public function isValidDay($dayOfMonth)
    {
        return $dayOfMonth >= 1 && $dayOfMonth <= $this->lengthOfMonth();
    }

    /**
     * Returns the length of the month, taking account of the year.
     * <p>
     * This returns the length of the month in days.
     * For example, a date in January would return 31.
     *
     * @return int the length of the month in days, from 28 to 31
     */
    public function lengthOfMonth()
    {
        return $this->getMonth()->length($this->isLeapYear());
    }

    /**
     * Returns the length of the year.
     * <p>
     * This returns the length of the year in days, either 365 or 366.
     *
     * @return int 366 if the year is leap, 365 otherwise
     */
    public
    function lengthOfYear()
    {
        return ($this->isLeapYear() ? 366 : 365);
    }

//-----------------------------------------------------------------------
    /**
     * Returns an adjusted copy of this year-month.
     * <p>
     * This returns a {@code YearMonth}, based on this one, with the year-month adjusted.
     * The adjustment takes place using the specified adjuster strategy object.
     * Read the documentation of the adjuster to understand what adjustment will be made.
     * <p>
     * A simple adjuster might simply set the one of the fields, such as the year field.
     * A more complex adjuster might set the year-month to the next month that
     * Halley's comet will pass the Earth.
     * <p>
     * The result of this method is obtained by invoking the
     * {@link TemporalAdjuster#adjustInto(Temporal)} method on the
     * specified adjuster passing {@code this} as the argument.
     * <p>
     * This instance is immutable and unaffected by this method call.
     *
     * @param $adjuster TemporalAdjuster the adjuster to use, not null
     * @return YearMonth a {@code YearMonth} based on {@code this} with the adjustment made, not null
     * @throws DateTimeException if the adjustment cannot be made
     * @throws ArithmeticException if numeric overflow occurs
     */
    public function adjust(TemporalAdjuster $adjuster)
    {
        return $adjuster->adjustInto($this);
    }

    /**
     * Returns a copy of this year-month with the specified field set to a new value.
     * <p>
     * This returns a {@code YearMonth}, based on this one, with the value
     * for the specified field changed.
     * This can be used to change any supported field, such as the year or month.
     * If it is not possible to set the value, because the field is not supported or for
     * some other reason, an exception is thrown.
     * <p>
     * If the field is a {@link ChronoField} then the adjustment is implemented here.
     * The supported fields behave as follows:
     * <ul>
     * <li>{@code MONTH_OF_YEAR} -
     *  Returns a {@code YearMonth} with the specified month-of-year.
     *  The year will be unchanged.
     * <li>{@code PROLEPTIC_MONTH} -
     *  Returns a {@code YearMonth} with the specified proleptic-month.
     *  This completely replaces the year and month of this object.
     * <li>{@code YEAR_OF_ERA} -
     *  Returns a {@code YearMonth} with the specified year-of-era
     *  The month and era will be unchanged.
     * <li>{@code YEAR} -
     *  Returns a {@code YearMonth} with the specified year.
     *  The month will be unchanged.
     * <li>{@code ERA} -
     *  Returns a {@code YearMonth} with the specified era.
     *  The month and year-of-era will be unchanged.
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
     * @return YearMonth a {@code YearMonth} based on {@code this} with the specified field set, not null
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
                case ChronoField::MONTH_OF_YEAR():
                    return $this->withMonth((int)$newValue);
                case ChronoField::PROLEPTIC_MONTH():
                    return $this->plusMonths($newValue - $this->getProlepticMonth());
                case ChronoField::YEAR_OF_ERA():
                    return $this->withYear((int)($this->year < 1 ? 1 - $newValue : $newValue));
                case ChronoField::YEAR():
                    return $this->withYear((int)$newValue);
                case ChronoField::ERA():
                    return ($this->getLong(ChronoField::ERA()) == $newValue ? $this : $this->withYear(1 - $this->year));
            }

            throw new UnsupportedTemporalTypeException("Unsupported field: " . $field);
        }
        return $field->adjustInto($this, $newValue);
    }

    //-----------------------------------------------------------------------
    /**
     * Returns a copy of this {@code YearMonth} with the year altered.
     * <p>
     * This instance is immutable and unaffected by this method call.
     *
     * @param $year int the year to set in the returned year-month, from MIN_YEAR to MAX_YEAR
     * @return YearMonth a {@code YearMonth} based on this year-month with the requested year, not null
     * @throws DateTimeException if the year value is invalid
     */
    public function withYear($year)
    {
        ChronoField::YEAR()->checkValidValue($year);
        return $this->with($year, $this->month);
    }

    /**
     * Returns a copy of this {@code YearMonth} with the month-of-year altered.
     * <p>
     * This instance is immutable and unaffected by this method call.
     *
     * @param $month int the month-of-year to set in the returned year-month, from 1 (January) to 12 (December)
     * @return YearMonth a {@code YearMonth} based on this year-month with the requested month, not null
     * @throws DateTimeException if the month-of-year value is invalid
     */
    public
    function withMonth($month)
    {
        ChronoField::MONTH_OF_YEAR()->checkValidValue($month);
        return $this->with($this->year, $month);
    }

//-----------------------------------------------------------------------
    /**
     * Returns a copy of this year-month with the specified amount added.
     * <p>
     * This returns a {@code YearMonth}, based on this one, with the specified amount added.
     * The amount is typically {@link Period} but may be any other type implementing
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
     * @return YearMonth a {@code YearMonth} based on this year-month with the addition made, not null
     * @throws DateTimeException if the addition cannot be made
     * @throws ArithmeticException if numeric overflow occurs
     */
    public function plusAmount(TemporalAmount $amountToAdd)
    {
        return $amountToAdd->addTo($this);
    }

    /**
     * Returns a copy of this year-month with the specified amount added.
     * <p>
     * This returns a {@code YearMonth}, based on this one, with the amount
     * in terms of the unit added. If it is not possible to add the amount, because the
     * unit is not supported or for some other reason, an exception is thrown.
     * <p>
     * If the field is a {@link ChronoUnit} then the addition is implemented here.
     * The supported fields behave as follows:
     * <ul>
     * <li>{@code MONTHS} -
     *  Returns a {@code YearMonth} with the specified number of months added.
     *  This is equivalent to {@link #plusMonths(long)}.
     * <li>{@code YEARS} -
     *  Returns a {@code YearMonth} with the specified number of years added.
     *  This is equivalent to {@link #plusYears(long)}.
     * <li>{@code DECADES} -
     *  Returns a {@code YearMonth} with the specified number of decades added.
     *  This is equivalent to calling {@link #plusYears(long)} with the amount
     *  multiplied by 10.
     * <li>{@code CENTURIES} -
     *  Returns a {@code YearMonth} with the specified number of centuries added.
     *  This is equivalent to calling {@link #plusYears(long)} with the amount
     *  multiplied by 100.
     * <li>{@code MILLENNIA} -
     *  Returns a {@code YearMonth} with the specified number of millennia added.
     *  This is equivalent to calling {@link #plusYears(long)} with the amount
     *  multiplied by 1,000.
     * <li>{@code ERAS} -
     *  Returns a {@code YearMonth} with the specified number of eras added.
     *  Only two eras are supported so the amount must be one, zero or minus one.
     *  If the amount is non-zero then the year is changed such that the year-of-era
     *  is unchanged.
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
     * @return YearMonth a {@code YearMonth} based on this year-month with the specified amount added, not null
     * @throws DateTimeException if the addition cannot be made
     * @throws UnsupportedTemporalTypeException if the unit is not supported
     * @throws ArithmeticException if numeric overflow occurs
     */
    public
    function plus($amountToAdd, TemporalUnit $unit)
    {
        if ($unit instanceof ChronoUnit) {
            switch ($unit) {
                case ChronoUnit::MONTHS():
                    return $this->plusMonths($amountToAdd);
                case ChronoUnit::YEARS():
                    return $this->plusYears($amountToAdd);
                case ChronoUnit::DECADES():
                    return $this->plusYears(Math::multiplyExact($amountToAdd, 10));
                case ChronoUnit::CENTURIES():
                    return $this->plusYears(Math::multiplyExact($amountToAdd, 100));
                case ChronoUnit::MILLENNIA():
                    return $this->plusYears(Math::multiplyExact($amountToAdd, 1000));
                case ChronoUnit::ERAS():
                    return $this->with(ChronoField::ERA(), Math::addExact($this->getLong(ChronoField::ERA()), $amountToAdd));
            }

            throw new UnsupportedTemporalTypeException("Unsupported unit: " . $unit);
        }
        return $unit->addTo($this, $amountToAdd);
    }

    /**
     * Returns a copy of this {@code YearMonth} with the specified number of years added.
     * <p>
     * This instance is immutable and unaffected by this method call.
     *
     * @param $yearsToAdd int the years to add, may be negative
     * @return YearMonth a {@code YearMonth} based on this year-month with the years added, not null
     * @throws DateTimeException if the result exceeds the supported range
     */
    public function plusYears($yearsToAdd)
    {
        if ($yearsToAdd == 0) {
            return $this;
        }
        $newYear = ChronoField::YEAR()->checkValidIntValue($this->year + $yearsToAdd);  // safe overflow
        return $this->with($newYear, $this->month);
    }

    /**
     * Returns a copy of this {@code YearMonth} with the specified number of months added.
     * <p>
     * This instance is immutable and unaffected by this method call.
     *
     * @param $monthsToAdd int  the months to add, may be negative
     * @return YearMonth a {@code YearMonth} based on this year-month with the months added, not null
     * @throws DateTimeException if the result exceeds the supported range
     */
    public function plusMonths($monthsToAdd)
    {
        if ($monthsToAdd == 0) {
            return $this;
        }
        $monthCount = $this->year * 12 + ($this->month - 1);
        $calcMonths = $monthCount + $monthsToAdd;  // safe overflow
        $newYear = ChronoField::YEAR()->checkValidIntValue(Math::floorDiv($calcMonths, 12));
        $newMonth = (int)Math::floorMod($calcMonths, 12) + 1;
        return $this->with($newYear, $newMonth);
    }

    //-----------------------------------------------------------------------
    /**
     * Returns a copy of this year-month with the specified amount subtracted.
     * <p>
     * This returns a {@code YearMonth}, based on this one, with the specified amount subtracted.
     * The amount is typically {@link Period} but may be any other type implementing
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
     * @return YearMonth a {@code YearMonth} based on this year-month with the subtraction made, not null
     * @throws DateTimeException if the subtraction cannot be made
     * @throws ArithmeticException if numeric overflow occurs
     */
    public function minus(TemporalAmount $amountToSubtract)
    {
        return $amountToSubtract->subtractFrom($this);
    }

    /**
     * Returns a copy of this year-month with the specified amount subtracted.
     * <p>
     * This returns a {@code YearMonth}, based on this one, with the amount
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
     * @return YearMonth a {@code YearMonth} based on this year-month with the specified amount subtracted, not null
     * @throws DateTimeException if the subtraction cannot be made
     * @throws UnsupportedTemporalTypeException if the unit is not supported
     * @throws ArithmeticException if numeric overflow occurs
     */
    public function minus($amountToSubtract, TemporalUnit $unit)
    {
        return ($amountToSubtract == Long::MIN_VALUE ? $this->plus(Long::MAX_VALUE, $unit)->plus(1, $unit) : $this->plus(-$amountToSubtract, $unit));
    }

    /**
     * Returns a copy of this {@code YearMonth} with the specified number of years subtracted.
     * <p>
     * This instance is immutable and unaffected by this method call.
     *
     * @param $yearsToSubtract int the years to subtract, may be negative
     * @return YearMonth a {@code YearMonth} based on this year-month with the years subtracted, not null
     * @throws DateTimeException if the result exceeds the supported range
     */
    public function minusYears($yearsToSubtract)
    {
        return ($yearsToSubtract == Long::MIN_VALUE ? $this->plusYears(Long::MAX_VALUE)->plusYears(1) : $this->plusYears(-$yearsToSubtract));
    }

    /**
     * Returns a copy of this {@code YearMonth} with the specified number of months subtracted.
     * <p>
     * This instance is immutable and unaffected by this method call.
     *
     * @param $monthsToSubtract int the months to subtract, may be negative
     * @return YearMonth a {@code YearMonth} based on this year-month with the months subtracted, not null
     * @throws DateTimeException if the result exceeds the supported range
     */
    public function minusMonths($monthsToSubtract)
    {
        return ($monthsToSubtract == Long::MIN_VALUE ? $this->plusMonths(Long::MAX_VALUE)->plusMonths(1) : $this->plusMonths(-$monthsToSubtract));
    }

    //-----------------------------------------------------------------------
    /**
     * Queries this year-month using the specified query.
     * <p>
     * This queries this year-month using the specified query strategy object.
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
        } else
            if ($query == TemporalQueries::precision()) {
                return ChronoUnit::MONTHS();
            }
        return TemporalAccessorDefaults::query($this, $query);
    }

    /**
     * Adjusts the specified temporal object to have this year-month.
     * <p>
     * This returns a temporal object of the same observable type as the input
     * with the year and month changed to be the same as this.
     * <p>
     * The adjustment is equivalent to using {@link Temporal#with(TemporalField, long)}
     * passing {@link ChronoField#PROLEPTIC_MONTH} as the field.
     * If the specified temporal object does not use the ISO calendar system then
     * a {@code DateTimeException} is thrown.
     * <p>
     * In most cases, it is clearer to reverse the calling pattern by using
     * {@link Temporal#with(TemporalAdjuster)}:
     * <pre>
     *   // these two lines are equivalent, but the second approach is recommended
     *   temporal = thisYearMonth.adjustInto(temporal);
     *   temporal = temporal.with(thisYearMonth);
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
        if (Chronology::from($temporal)->equals(IsoChronology::INSTANCE()) == false) {
            throw new DateTimeException("Adjustment only supported on ISO date-time");
        }

        return $temporal->with(ChronoField::PROLEPTIC_MONTH(), $this->getProlepticMonth());
    }

    /**
     * Calculates the amount of time until another year-month in terms of the specified unit.
     * <p>
     * This calculates the amount of time between two {@code YearMonth}
     * objects in terms of a single {@code TemporalUnit}.
     * The start and end points are {@code this} and the specified year-month.
     * The result will be negative if the end is before the start.
     * The {@code Temporal} passed to this method is converted to a
     * {@code YearMonth} using {@link #from(TemporalAccessor)}.
     * For example, the amount in years between two year-months can be calculated
     * using {@code startYearMonth.until(endYearMonth, YEARS)}.
     * <p>
     * The calculation returns a whole number, representing the number of
     * complete units between the two year-months.
     * For example, the amount in decades between 2012-06 and 2032-05
     * will only be one decade as it is one month short of two decades.
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
     * The units {@code MONTHS}, {@code YEARS}, {@code DECADES},
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
     * @param $endExclusive Temporal the end date, exclusive, which is converted to a {@code YearMonth}, not null
     * @param $unit TemporalUnit the unit to measure the amount in, not null
     * @return int the amount of time between this year-month and the end year-month
     * @throws DateTimeException if the amount cannot be calculated, or the end
     *  temporal cannot be converted to a {@code YearMonth}
     * @throws UnsupportedTemporalTypeException if the unit is not supported
     * @throws ArithmeticException if numeric overflow occurs
     */
    public function until(Temporal $endExclusive, TemporalUnit $unit)
    {
        $end = YearMonth::from($endExclusive);
        if ($unit instanceof ChronoUnit) {
            $monthsUntil = $end->getProlepticMonth() - $this->getProlepticMonth();  // no overflow
            switch ($unit) {
                case ChronoUnit::MONTHS():
                    return $monthsUntil;
                case ChronoUnit::YEARS():
                    return $monthsUntil / 12;
                case ChronoUnit::DECADES():
                    return $monthsUntil / 120;
                case ChronoUnit::CENTURIES():
                    return $monthsUntil / 1200;
                case ChronoUnit::MILLENNIA():
                    return $monthsUntil / 12000;
                case ChronoUnit::ERAS():
                    return $end->getLong(ChronoField::ERA()) - $this->getLong(ChronoField::ERA());
            }

            throw new UnsupportedTemporalTypeException("Unsupported unit: " . $unit);
        }
        return $unit->between($this, $end);
    }

    /**
     * Formats this year-month using the specified formatter.
     * <p>
     * This year-month will be passed to the formatter to produce a string.
     *
     * @param $formatter DateTimeFormatter the formatter to use, not null
     * @return string the formatted year-month string, not null
     * @throws DateTimeException if an error occurs during printing
     */
    public function format(DateTimeFormatter $formatter)
    {
        return $formatter->format($this);
    }

//-----------------------------------------------------------------------
    /**
     * Combines this year-month with a day-of-month to create a {@code LocalDate}.
     * <p>
     * This returns a {@code LocalDate} formed from this year-month and the specified day-of-month.
     * <p>
     * The day-of-month value must be valid for the year-month.
     * <p>
     * This method can be used as part of a chain to produce a date:
     * <pre>
     *  LocalDate date = year.atMonth(month).atDay(day);
     * </pre>
     *
     * @param $dayOfMonth int the day-of-month to use, from 1 to 31
     * @return LocalDate the date formed from this year-month and the specified day, not null
     * @throws DateTimeException if the day is invalid for the year-month
     * @see #isValidDay(int)
     */
    public
    function atDay($dayOfMonth)
    {
        return LocalDate::of($this->year, $this->month, $dayOfMonth);
    }

    /**
     * Returns a {@code LocalDate} at the end of the month.
     * <p>
     * This returns a {@code LocalDate} based on this year-month.
     * The day-of-month is set to the last valid day of the month, taking
     * into account leap years.
     * <p>
     * This method can be used as part of a chain to produce a date:
     * <pre>
     *  LocalDate date = year.atMonth(month).atEndOfMonth();
     * </pre>
     *
     * @return LocalDate the last valid date of this year-month, not null
     */
    public function atEndOfMonth()
    {
        return LocalDate::of($this->year, $this->month, $this->lengthOfMonth());
    }

//-----------------------------------------------------------------------
    /**
     * Compares this year-month to another year-month.
     * <p>
     * The comparison is based first on the value of the year, then on the value of the month.
     * It is "consistent with equals", as defined by {@link Comparable}.
     *
     * @param $other YearMonth the other year-month to compare to, not null
     * @return int the comparator value, negative if less, positive if greater
     */
    public function compareTo(YearMonth $other)
    {
        $cmp = ($this->year - $other->year);
        if ($cmp == 0) {
            $cmp = ($this->month - $other->month);
        }

        return $cmp;
    }

    /**
     * Checks if this year-month is after the specified year-month.
     *
     * @param $other YearMonth the other year-month to compare to, not null
     * @return bool true if this is after the specified year-month
     */
    public function isAfter(YearMonth $other)
    {
        return $this->compareTo($other) > 0;
    }

    /**
     * Checks if this year-month is before the specified year-month.
     *
     * @param $other YearMonth the other year-month to compare to, not null
     * @return bool true if this point is before the specified year-month
     */
    public function isBefore(YearMonth $other)
    {
        return $this->compareTo($other) < 0;
    }

    //-----------------------------------------------------------------------
    /**
     * Checks if this year-month is equal to another year-month.
     * <p>
     * The comparison is based on the time-line position of the year-months.
     *
     * @param $obj mixed the object to check, null returns false
     * @return bool true if this is equal to the other year-month
     */
    public function equals($obj)
    {
        if ($this === $obj) {
            return true;
        }

        if ($obj instanceof YearMonth) {
            $other = $obj;
            return $this->year == $other->year && $this->month == $other->month;
        }
        return false;
    }

//-----------------------------------------------------------------------
    /**
     * Outputs this year-month as a {@code String}, such as {@code 2007-12}.
     * <p>
     * The output will be in the format {@code uuuu-MM}:
     *
     * @return string a string representation of this year-month, not null
     */
    public function __toString()
    {
        $absYear = Math::abs($this->year);
        $buf = '';
        if ($absYear < 1000) {
            if ($this->year < 0) {
                $y = (string)($this->year - 10000);
                $buf .= substr($y, 0, 1) . substr($y, 2);
            } else {
                $buf .= substr($this->year + 10000, 1);
            }
        } else {
            $buf .= $this->year;
        }
        return $buf . ($this->month < 10 ? "-0" : "-")
        . $this->month;
    }
}

