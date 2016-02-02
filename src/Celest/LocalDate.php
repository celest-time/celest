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

use Celest\Chrono\ChronoLocalDate;
use Celest\Chrono\ChronoLocalDateDefaults;
use Celest\Chrono\Era;
use Celest\Chrono\IsoChronology;
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
use SebastianBergmann\Comparator\Comparator;


/**
 * A date without a time-zone in the ISO-8601 calendar system,
 * such as {@code 2007-12-03}.
 * <p>
 * {@code LocalDate} is an immutable date-time object that represents a date,
 * often viewed as year-month-day. Other date fields, such as day-of-year,
 * day-of-week and week-of-year, can also be accessed.
 * For example, the value "2nd October 2007" can be stored in a {@code LocalDate}.
 * <p>
 * This class does not store or represent a time or time-zone.
 * Instead, it is a description of the date, as used for birthdays.
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
 * {@code LocalDate} may have unpredictable results and should be avoided.
 * The {@code equals} method should be used for comparisons.
 *
 * @implSpec
 * This class is immutable and thread-safe.
 *
 * @since 1.8
 */
final class LocalDate implements Temporal, TemporalAdjuster, ChronoLocalDate
{
    public static function init()
    {
        self::$MIN = LocalDate::ofNumerical(Year::MIN_VALUE, 1, 1);
        self::$MAX = LocalDate::ofNumerical(Year::MAX_VALUE, 12, 31);
    }

    /**
     * The minimum supported {@code LocalDate}, '-999999999-01-01'.
     * This could be used by an application as a "far past" date.
     * @return LocalDate
     */
    public static function MIN()
    {
        return self::$MIN;
    }

    /** @var @var LocalDate */
    private static $MIN;

    /**
     * The maximum supported {@code LocalDate}, '+999999999-12-31'.
     * This could be used by an application as a "far future" date.
     * @return LocalDate
     */
    public static function MAX()
    {
        return self::$MAX;
    }

    /** @var @var LocalDate */
    private static $MAX;

    /**
     * The number of days in a 400 year cycle.
     */
    const DAYS_PER_CYCLE = 146097;
    /**
     * The number of days from year zero to year 1970.
     * There are five 400 year cycles from year zero to 2000.
     * There are 7 leap years from 1970 to 2000.
     */
    const DAYS_0000_TO_1970 = (self::DAYS_PER_CYCLE * 5) - (30 * 365 + 7);

    /**
     * The year.
     * @var int
     */
    private $year;
    /**
     * The month-of-year.
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
     * Obtains the current date from the system clock in the default time-zone.
     * <p>
     * This will query the {@link Clock#systemDefaultZone() system clock} in the default
     * time-zone to obtain the current date.
     * <p>
     * Using this method will prevent the ability to use an alternate clock for testing
     * because the clock is hard-coded.
     *
     * @return LocalDate the current date using the system clock and default time-zone, not null
     */
    public static function now()
    {
        return self::nowOf(Clock::systemDefaultZone());
    }

    /**
     * Obtains the current date from the system clock in the specified time-zone.
     * <p>
     * This will query the {@link Clock#system(ZoneId) system clock} to obtain the current date.
     * Specifying the time-zone avoids dependence on the default time-zone.
     * <p>
     * Using this method will prevent the ability to use an alternate clock for testing
     * because the clock is hard-coded.
     *
     * @param ZoneId $zone the zone ID to use, not null
     * @return LocalDate the current date using the system clock, not null
     */
    public static function nowFrom(ZoneId $zone)
    {
        return self::nowOf(Clock::system($zone));
    }

    /**
     * Obtains the current date from the specified clock.
     * <p>
     * This will query the specified clock to obtain the current date - today.
     * Using this method allows the use of an alternate clock for testing.
     * The alternate clock may be introduced using {@link Clock dependency injection}.
     *
     * @param Clock $clock the clock to use, not null
     * @return LocalDate the current date, not null
     */
    public static function nowOf(Clock $clock)
    {
        // inline to avoid creating object and Instant checks
        $now = $clock->instant();  // called once
        $offset = $clock->getZone()->getRules()->getOffset($now);
        $epochSec = $now->getEpochSecond() + $offset->getTotalSeconds();  // overflow caught later
        $epochDay = Math::floorDiv($epochSec, LocalTime::SECONDS_PER_DAY);
        return LocalDate::ofEpochDay($epochDay);
    }

//-----------------------------------------------------------------------
    /**
     * Obtains an instance of {@code LocalDate} from a year, month and day.
     * <p>
     * This returns a {@code LocalDate} with the specified year, month and day-of-month.
     * The day must be valid for the year and month, otherwise an exception will be thrown.
     *
     * @param int $year the year to represent, from MIN_YEAR to MAX_YEAR
     * @param Month $month the month-of-year to represent, not null
     * @param int $dayOfMonth the day-of-month to represent, from 1 to 31
     * @return LocalDate the local date, not null
     * @throws DateTimeException if the value of any field is out of range,
     *  or if the day-of-month is invalid for the month-year
     */
    public
    static function of($year, Month $month, $dayOfMonth)
    {
        ChronoField::YEAR()->checkValidValue($year);
        ChronoField::DAY_OF_MONTH()->checkValidValue($dayOfMonth);
        return self::create($year, $month->getValue(), $dayOfMonth);
    }

    /**
     * Obtains an instance of {@code LocalDate} from a year, month and day.
     * <p>
     * This returns a {@code LocalDate} with the specified year, month and day-of-month.
     * The day must be valid for the year and month, otherwise an exception will be thrown.
     *
     * @param int $year the year to represent, from MIN_YEAR to MAX_YEAR
     * @param int $month  the month-of-year to represent, from 1 (January) to 12 (December)
     * @param int $dayOfMonth the day-of-month to represent, from 1 to 31
     * @return LocalDate the local date, not null
     * @throws DateTimeException if the value of any field is out of range,
     *  or if the day-of-month is invalid for the month-year
     */
    public
    static function ofNumerical($year, $month, $dayOfMonth)
    {
        ChronoField::YEAR()->checkValidValue($year);
        ChronoField::MONTH_OF_YEAR()->checkValidValue($month);
        ChronoField::DAY_OF_MONTH()->checkValidValue($dayOfMonth);
        return self::create($year, $month, $dayOfMonth);
    }

    public static function fromQuery() {
        return TemporalQueries::fromCallable([self::class, 'from']);
    }

//-----------------------------------------------------------------------
    /**
     * Obtains an instance of {@code LocalDate} from a year and day-of-year.
     * <p>
     * This returns a {@code LocalDate} with the specified year and day-of-year.
     * The day-of-year must be valid for the year, otherwise an exception will be thrown.
     *
     * @param int $year the year to represent, from MIN_YEAR to MAX_YEAR
     * @param int $dayOfYear the day-of-year to represent, from 1 to 366
     * @return LocalDate the local date, not null
     * @throws DateTimeException if the value of any field is out of range,
     *  or if the day-of-year is invalid for the year
     */
    public
    static function ofYearDay($year, $dayOfYear)
    {
        ChronoField::YEAR()->checkValidValue($year);
        ChronoField::DAY_OF_YEAR()->checkValidValue($dayOfYear);
        $leap = IsoChronology::INSTANCE()->isLeapYear($year);
        if ($dayOfYear == 366 && $leap == false) {
            throw new DateTimeException("Invalid date 'DayOfYear 366' as '" . $year . "' is not a leap year");
        }

        $moy = Month::of(Math::div(($dayOfYear - 1), 31) + 1);
        $monthEnd = $moy->firstDayOfYear($leap) + $moy->length($leap) - 1;
        if ($dayOfYear > $monthEnd) {
            $moy = $moy->plus(1);
        }
        $dom = $dayOfYear - $moy->firstDayOfYear($leap) + 1;
        return new LocalDate($year, $moy->getValue(), $dom);
    }

    //-----------------------------------------------------------------------
    /**
     * Obtains an instance of {@code LocalDate} from the epoch day count.
     * <p>
     * This returns a {@code LocalDate} with the specified epoch-day.
     * The {@link ChronoField#EPOCH_DAY EPOCH_DAY} is a simple incrementing count
     * of days where day 0 is 1970-01-01. Negative numbers represent earlier days.
     *
     * @param int $epochDay the Epoch Day to convert, based on the epoch 1970-01-01
     * @return LocalDate the local date, not null
     * @throws DateTimeException if the epoch day exceeds the supported date range
     * TODO check rounding, add Check
     */
    public static function ofEpochDay($epochDay)
    {
        $zeroDay = $epochDay + self::DAYS_0000_TO_1970;
        // find the march-based year
        $zeroDay -= 60;  // adjust to 0000-03-01 so leap day is at end of four year cycle
        $adjust = 0;
        if ($zeroDay < 0) {
            // adjust negative years to positive for calculation
            $adjustCycles = Math::div($zeroDay + 1, self::DAYS_PER_CYCLE) - 1;
            $adjust = $adjustCycles * 400;
            $zeroDay += -$adjustCycles * self::DAYS_PER_CYCLE;
        }

        $yearEst = Math::div(400 * $zeroDay + 591, self::DAYS_PER_CYCLE);
        $doyEst = $zeroDay - (365 * $yearEst + Math::div($yearEst, 4) - Math::div($yearEst, 100) + Math::div($yearEst, 400));
        if ($doyEst < 0) {
            // fix estimate
            $yearEst--;
            $doyEst = $zeroDay - (365 * $yearEst + Math::div($yearEst, 4) - Math::div($yearEst, 100) + Math::div($yearEst, 400));
        }
        $yearEst += $adjust;  // reset any negative year
        $marchDoy0 = $doyEst;

        // convert march-based values back to january-based
        $marchMonth0 = Math::div($marchDoy0 * 5 + 2, 153);
        $month = ($marchMonth0 + 2) % 12 + 1;
        $dom = $marchDoy0 - Math::div($marchMonth0 * 306 + 5, 10) + 1;
        $yearEst += Math::div($marchMonth0, 10);

        // check year now we are certain it is correct
        $year = ChronoField::YEAR()->checkValidIntValue($yearEst);
        return new LocalDate($year, $month, $dom);
    }

    //-----------------------------------------------------------------------
    /**
     * Obtains an instance of {@code LocalDate} from a temporal object.
     * <p>
     * This obtains a local date based on the specified temporal.
     * A {@code TemporalAccessor} represents an arbitrary set of date and time information,
     * which this factory converts to an instance of {@code LocalDate}.
     * <p>
     * The conversion uses the {@link TemporalQueries#localDate()} query, which relies
     * on extracting the {@link ChronoField#EPOCH_DAY EPOCH_DAY} field.
     * <p>
     * This method matches the signature of the functional interface {@link TemporalQuery}
     * allowing it to be used as a query via method reference, {@code LocalDate::from}.
     *
     * @param TemporalAccessor $temporal the temporal object to convert, not null
     * @return LocalDate the local date, not null
     * @throws DateTimeException if unable to convert to a {@code LocalDate}
     */
    public static function from(TemporalAccessor $temporal)
    {
        /** @var LocalDate $date */
        $date = $temporal->query(TemporalQueries::localDate());
        if ($date == null) {
            throw new DateTimeException("Unable to obtain LocalDate from TemporalAccessor: " .
                $temporal . " of type " . get_class($temporal));
        }

        return $date;
    }

//-----------------------------------------------------------------------
    /**
     * Obtains an instance of {@code LocalDate} from a text string such as {@code 2007-12-03}.
     * <p>
     * The string must represent a valid date and is parsed using
     * {@link java.time.format.DateTimeFormatter#ISO_LOCAL_DATE}.
     *
     * @param string $text the text to parse such as "2007-12-03", not null
     * @return LocalDate the parsed local date, not null
     * @throws DateTimeParseException if the text cannot be parsed
     */
    public static function parse($text)
    {
        return self::parseWith($text, DateTimeFormatter::ISO_LOCAL_DATE);
    }

    /**
     * Obtains an instance of {@code LocalDate} from a text string using a specific formatter.
     * <p>
     * The text is parsed using the formatter, returning a date.
     *
     * @param string $text the text to parse, not null
     * @param DateTimeFormatter $formatter the formatter to use, not null
     * @return LocalDate the parsed local date, not null
     * @throws DateTimeParseException if the text cannot be parsed
     */
    public
    static function parseWith($text, DateTimeFormatter $formatter)
    {
        return $formatter->parse($text, LocalDate::from);
    }

//-----------------------------------------------------------------------
    /**
     * Creates a local date from the year, month and day fields.
     *
     * @param int $year the year to represent, validated from MIN_YEAR to MAX_YEAR
     * @param int $month the month-of-year to represent, from 1 to 12, validated
     * @param int $dayOfMonth the day-of-month to represent, validated from 1 to 31
     * @return LocalDate the local date, not null
     * @throws DateTimeException if the day-of-month is invalid for the month-year
     */
    private
    static function create($year, $month, $dayOfMonth)
    {
        if ($dayOfMonth > 28) {
            $dom = 31;
            switch ($month) {
                case 2:
                    $dom = (IsoChronology::INSTANCE()->isLeapYear($year) ? 29 : 28);
                    break;
                case 4:
                case 6:
                case 9:
                case 11:
                    $dom = 30;
                    break;
            }

            if ($dayOfMonth > $dom) {
                if ($dayOfMonth == 29) {
                    throw new DateTimeException("Invalid date 'February 29' as '" . $year . "' is not a leap year");
                } else {
                    throw new DateTimeException("Invalid date '" . Month::of($month) . " " . $dayOfMonth . "'");
                }
            }
        }
        return new LocalDate($year, $month, $dayOfMonth);
    }

    /**
     * Resolves the date, resolving days past the end of month.
     *
     * @param int $year the year to represent, validated from MIN_YEAR to MAX_YEAR
     * @param int $month the month-of-year to represent, validated from 1 to 12
     * @param int $day the day-of-month to represent, validated from 1 to 31
     * @return LocalDate the resolved date, not null
     */
    private
    static function resolvePreviousValid($year, $month, $day)
    {
        switch ($month) {
            case 2:
                $day = Math::min($day, IsoChronology::INSTANCE()->isLeapYear($year) ? 29 : 28);
                break;
            case 4:
            case 6:
            case 9:
            case 11:
                $day = Math::min($day, 30);
                break;
        }

        return new LocalDate($year, $month, $day);
    }

    /**
     * Constructor, previously validated.
     *
     * @param int $year the year to represent, from MIN_YEAR to MAX_YEAR
     * @param int $month the month-of-year to represent, not null
     * @param int $dayOfMonth the day-of-month to represent, valid for year-month, from 1 to 31
     */
    private
    function __construct($year, $month, $dayOfMonth)
    {
        $this->year = $year;
        $this->month = $month;
        $this->day = $dayOfMonth;
    }

//-----------------------------------------------------------------------
    /**
     * Checks if the specified field is supported.
     * <p>
     * This checks if this date can be queried for the specified field.
     * If false, then calling the {@link #range(TemporalField) range},
     * {@link #get(TemporalField) get} and {@link #with(TemporalField, long)}
     * methods will throw an exception.
     * <p>
     * If the field is a {@link ChronoField} then the query is implemented here.
     * The supported fields are:
     * <ul>
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
     * @param TemporalField $field  the field to check, null returns false
     * @return bool true if the field is supported on this date, false if not
     */
    public function isSupported(TemporalField $field)
    {
        return ChronoLocalDateDefaults::isSupported($this, $field);
    }

    /**
     * Checks if the specified unit is supported.
     * <p>
     * This checks if the specified unit can be added to, or subtracted from, this date.
     * If false, then calling the {@link #plus(long, TemporalUnit)} and
     * {@link #minus(long, TemporalUnit) minus} methods will throw an exception.
     * <p>
     * If the unit is a {@link ChronoUnit} then the query is implemented here.
     * The supported units are:
     * <ul>
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
        return ChronoLocalDateDefaults::isUnitSupported($this, $unit);
    }

//-----------------------------------------------------------------------
    /**
     * Gets the range of valid values for the specified field.
     * <p>
     * The range object expresses the minimum and maximum valid values for a field.
     * This date is used to enhance the accuracy of the returned range.
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
            /** @var ChronoField $f */
            $f = $field;
            if ($f->isDateBased()) {
                switch ($f) {
                    case ChronoField::DAY_OF_MONTH():
                        return ValueRange::of(1, $this->lengthOfMonth());
                    case ChronoField::DAY_OF_YEAR():
                        return ValueRange::of(1, $this->lengthOfYear());
                    case ChronoField::ALIGNED_WEEK_OF_MONTH():
                        return ValueRange::of(1, $this->getMonth() == Month::FEBRUARY() && $this->isLeapYear() == false ? 4 : 5);
                    case ChronoField::YEAR_OF_ERA():
                        return ($this->getYear() <= 0 ? ValueRange::of(1, Year::MAX_VALUE + 1) : ValueRange::of(1, Year::MAX_VALUE));
                }

                return $field->range();
            }
            throw new UnsupportedTemporalTypeException("Unsupported field: " . $field);
        }
        return $field->rangeRefinedBy($this);
    }

    /**
     * Gets the value of the specified field from this date as an {@code int}.
     * <p>
     * This queries this date for the value of the specified field.
     * The returned value will always be within the valid range of values for the field.
     * If it is not possible to return the value, because the field is not supported
     * or for some other reason, an exception is thrown.
     * <p>
     * If the field is a {@link ChronoField} then the query is implemented here.
     * The {@link #isSupported(TemporalField) supported fields} will return valid
     * values based on this date, except {@code EPOCH_DAY} and {@code PROLEPTIC_MONTH}
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
        if ($field instanceof ChronoField) {
            return $this->get0($field);
        }

        return ChronoLocalDateDefaults::get($this, $field);
    }

    /**
     * Gets the value of the specified field from this date as a {@code long}.
     * <p>
     * This queries this date for the value of the specified field.
     * If it is not possible to return the value, because the field is not supported
     * or for some other reason, an exception is thrown.
     * <p>
     * If the field is a {@link ChronoField} then the query is implemented here.
     * The {@link #isSupported(TemporalField) supported fields} will return valid
     * values based on this date.
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
            if ($field == ChronoField::EPOCH_DAY()) {
                return $this->toEpochDay();
            }

            if ($field == ChronoField::PROLEPTIC_MONTH()) {
                return $this->getProlepticMonth();
            }
            return $this->get0($field);
        }
        return $field->getFrom($this);
    }

    private function get0(TemporalField $field)
    {
        switch ($field) {
            case ChronoField::DAY_OF_WEEK():
                return $this->getDayOfWeek()->getValue();
            case ChronoField::ALIGNED_DAY_OF_WEEK_IN_MONTH():
                return (($this->day - 1) % 7) + 1;
            case ChronoField::ALIGNED_DAY_OF_WEEK_IN_YEAR():
                return (($this->getDayOfYear() - 1) % 7) + 1;
            case ChronoField::DAY_OF_MONTH():
                return $this->day;
            case ChronoField::DAY_OF_YEAR():
                return $this->getDayOfYear();
            case ChronoField::EPOCH_DAY():
                throw new UnsupportedTemporalTypeException("Invalid field 'EpochDay' for get() method, use getLong() instead");
            case ChronoField::ALIGNED_WEEK_OF_MONTH():
                return Math::div($this->day - 1, 7) + 1;
            case ChronoField::ALIGNED_WEEK_OF_YEAR():
                return Math::div($this->getDayOfYear() - 1, 7) + 1;
            case ChronoField::MONTH_OF_YEAR():
                return $this->month;
            case ChronoField::PROLEPTIC_MONTH():
                throw new UnsupportedTemporalTypeException("Invalid field 'ProlepticMonth' for get() method, use getLong() instead");
            case ChronoField::YEAR_OF_ERA():
                return ($this->year >= 1 ? $this->year : 1 - $this->year);
            case ChronoField::YEAR():
                return $this->year;
            case ChronoField::ERA():
                return ($this->year >= 1 ? 1 : 0);
        }

        throw new UnsupportedTemporalTypeException("Unsupported field: " . $field);
    }

    private
    function getProlepticMonth()
    {
        return ($this->year * 12 + $this->month - 1);
    }

//-----------------------------------------------------------------------
    /**
     * Gets the chronology of this date, which is the ISO calendar system.
     * <p>
     * The {@code Chronology} represents the calendar system in use.
     * The ISO-8601 calendar system is the modern civil calendar system used today
     * in most of the world. It is equivalent to the proleptic Gregorian calendar
     * system, in which today's rules for leap years are applied for all time.
     *
     * @return IsoChronology the ISO chronology, not null
     */
    public function getChronology()
    {
        return IsoChronology::INSTANCE();
    }

    /**
     * Gets the era applicable at this date.
     * <p>
     * The official ISO-8601 standard does not define eras, however {@code IsoChronology} does.
     * It defines two eras, 'CE' from year one onwards and 'BCE' from year zero backwards.
     * Since dates before the Julian-Gregorian cutover are not in line with history,
     * the cutover between 'BCE' and 'CE' is also not aligned with the commonly used
     * eras, often referred to using 'BC' and 'AD'.
     * <p>
     * Users of this class should typically ignore this method as it exists primarily
     * to fulfill the {@link ChronoLocalDate} contract where it is necessary to support
     * the Japanese calendar system.
     * <p>
     * The returned era will be a singleton capable of being compared with the constants
     * in {@link IsoChronology} using the {@code ==} operator.
     *
     * @return Era the {@code IsoChronology} era constant applicable at this date, not null
     */
    public function getEra()
    {
        return ChronoLocalDateDefaults::getEra($this);
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
    public
    function getMonth()
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

    /**
     * Gets the day-of-year field.
     * <p>
     * This method returns the primitive {@code int} value for the day-of-year.
     *
     * @return int the day-of-year, from 1 to 365, or 366 in a leap year
     */
    public
    function getDayOfYear()
    {
        return $this->getMonth()->firstDayOfYear($this->isLeapYear()) + $this->day - 1;
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
    public
    function getDayOfWeek()
    {
        $dow0 = (int)Math::floorMod($this->toEpochDay() + 3, 7);
        return DayOfWeek::of($dow0 + 1);
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
     * Returns the length of the month represented by this date.
     * <p>
     * This returns the length of the month in days.
     * For example, a date in January would return 31.
     *
     * @return int the length of the month in days
     */
    public function lengthOfMonth()
    {
        switch ($this->month) {
            case 2:
                return ($this->isLeapYear() ? 29 : 28);
            case 4:
            case 6:
            case 9:
            case 11:
                return 30;
            default:
                return 31;
        }
    }

    /**
     * Returns the length of the year represented by this date.
     * <p>
     * This returns the length of the year in days, either 365 or 366.
     *
     * @return int 366 if the year is leap, 365 otherwise
     */
    public function lengthOfYear()
    {
        return ($this->isLeapYear() ? 366 : 365);
    }

//-----------------------------------------------------------------------
    /**
     * Returns an adjusted copy of this date.
     * <p>
     * This returns a {@code LocalDate}, based on this one, with the date adjusted.
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
     *  result = localDate.with(JULY).with(lastDayOfMonth());
     * </pre>
     * <p>
     * The result of this method is obtained by invoking the
     * {@link TemporalAdjuster#adjustInto(Temporal)} method on the
     * specified adjuster passing {@code this} as the argument.
     * <p>
     * This instance is immutable and unaffected by this method call.
     *
     * @param TemporalAdjuster $adjuster the adjuster to use, not null
     * @return LocalDate a {@code LocalDate} based on {@code this} with the adjustment made, not null
     * @throws DateTimeException if the adjustment cannot be made
     * @throws ArithmeticException if numeric overflow occurs
     */
    public function adjust(TemporalAdjuster $adjuster)
    {
        // optimizations
        if ($adjuster instanceof LocalDate) {
            return $adjuster;
        }

        return $adjuster->adjustInto($this);
    }

    /**
     * Returns a copy of this date with the specified field set to a new value.
     * <p>
     * This returns a {@code LocalDate}, based on this one, with the value
     * for the specified field changed.
     * This can be used to change any supported field, such as the year, month or day-of-month.
     * If it is not possible to set the value, because the field is not supported or for
     * some other reason, an exception is thrown.
     * <p>
     * In some cases, changing the specified field can cause the resulting date to become invalid,
     * such as changing the month from 31st January to February would make the day-of-month invalid.
     * In cases like this, the field is responsible for resolving the date. Typically it will choose
     * the previous valid date, which would be the last valid day of February in this example.
     * <p>
     * If the field is a {@link ChronoField} then the adjustment is implemented here.
     * The supported fields behave as follows:
     * <ul>
     * <li>{@code DAY_OF_WEEK} -
     *  Returns a {@code LocalDate} with the specified day-of-week.
     *  The date is adjusted up to 6 days forward or backward within the boundary
     *  of a Monday to Sunday week.
     * <li>{@code ALIGNED_DAY_OF_WEEK_IN_MONTH} -
     *  Returns a {@code LocalDate} with the specified aligned-day-of-week.
     *  The date is adjusted to the specified month-based aligned-day-of-week.
     *  Aligned weeks are counted such that the first week of a given month starts
     *  on the first day of that month.
     *  This may cause the date to be moved up to 6 days into the following month.
     * <li>{@code ALIGNED_DAY_OF_WEEK_IN_YEAR} -
     *  Returns a {@code LocalDate} with the specified aligned-day-of-week.
     *  The date is adjusted to the specified year-based aligned-day-of-week.
     *  Aligned weeks are counted such that the first week of a given year starts
     *  on the first day of that year.
     *  This may cause the date to be moved up to 6 days into the following year.
     * <li>{@code DAY_OF_MONTH} -
     *  Returns a {@code LocalDate} with the specified day-of-month.
     *  The month and year will be unchanged. If the day-of-month is invalid for the
     *  year and month, then a {@code DateTimeException} is thrown.
     * <li>{@code DAY_OF_YEAR} -
     *  Returns a {@code LocalDate} with the specified day-of-year.
     *  The year will be unchanged. If the day-of-year is invalid for the
     *  year, then a {@code DateTimeException} is thrown.
     * <li>{@code EPOCH_DAY} -
     *  Returns a {@code LocalDate} with the specified epoch-day.
     *  This completely replaces the date and is equivalent to {@link #ofEpochDay(long)}.
     * <li>{@code ALIGNED_WEEK_OF_MONTH} -
     *  Returns a {@code LocalDate} with the specified aligned-week-of-month.
     *  Aligned weeks are counted such that the first week of a given month starts
     *  on the first day of that month.
     *  This adjustment moves the date in whole week chunks to match the specified week.
     *  The result will have the same day-of-week as this date.
     *  This may cause the date to be moved into the following month.
     * <li>{@code ALIGNED_WEEK_OF_YEAR} -
     *  Returns a {@code LocalDate} with the specified aligned-week-of-year.
     *  Aligned weeks are counted such that the first week of a given year starts
     *  on the first day of that year.
     *  This adjustment moves the date in whole week chunks to match the specified week.
     *  The result will have the same day-of-week as this date.
     *  This may cause the date to be moved into the following year.
     * <li>{@code MONTH_OF_YEAR} -
     *  Returns a {@code LocalDate} with the specified month-of-year.
     *  The year will be unchanged. The day-of-month will also be unchanged,
     *  unless it would be invalid for the new month and year. In that case, the
     *  day-of-month is adjusted to the maximum valid value for the new month and year.
     * <li>{@code PROLEPTIC_MONTH} -
     *  Returns a {@code LocalDate} with the specified proleptic-month.
     *  The day-of-month will be unchanged, unless it would be invalid for the new month
     *  and year. In that case, the day-of-month is adjusted to the maximum valid value
     *  for the new month and year.
     * <li>{@code YEAR_OF_ERA} -
     *  Returns a {@code LocalDate} with the specified year-of-era.
     *  The era and month will be unchanged. The day-of-month will also be unchanged,
     *  unless it would be invalid for the new month and year. In that case, the
     *  day-of-month is adjusted to the maximum valid value for the new month and year.
     * <li>{@code YEAR} -
     *  Returns a {@code LocalDate} with the specified year.
     *  The month will be unchanged. The day-of-month will also be unchanged,
     *  unless it would be invalid for the new month and year. In that case, the
     *  day-of-month is adjusted to the maximum valid value for the new month and year.
     * <li>{@code ERA} -
     *  Returns a {@code LocalDate} with the specified era.
     *  The year-of-era and month will be unchanged. The day-of-month will also be unchanged,
     *  unless it would be invalid for the new month and year. In that case, the
     *  day-of-month is adjusted to the maximum valid value for the new month and year.
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
     * @param TemporalField $field the field to set in the result, not null
     * @param int $newValue the new value of the field in the result
     * @return LocalDate a {@code LocalDate} based on {@code this} with the specified field set, not null
     * @throws DateTimeException if the field cannot be set
     * @throws UnsupportedTemporalTypeException if the field is not supported
     * @throws ArithmeticException if numeric overflow occurs
     */
    public function with(TemporalField $field, $newValue)
    {
        if ($field instanceof ChronoField) {
            /** @var Chronofield $f */
            $f = $field;
            $f->checkValidValue($newValue);
            switch ($f) {
                case ChronoField::DAY_OF_WEEK():
                    return $this->plusDays($newValue - $this->getDayOfWeek()->getValue());
                case ChronoField::ALIGNED_DAY_OF_WEEK_IN_MONTH():
                    return $this->plusDays($newValue - $this->getLong(ChronoField::ALIGNED_DAY_OF_WEEK_IN_MONTH()));
                case ChronoField::ALIGNED_DAY_OF_WEEK_IN_YEAR():
                    return $this->plusDays($newValue - $this->getLong(ChronoField::ALIGNED_DAY_OF_WEEK_IN_YEAR()));
                case ChronoField::DAY_OF_MONTH():
                    return $this->withDayOfMonth((int)$newValue);
                case ChronoField::DAY_OF_YEAR():
                    return $this->withDayOfYear((int)$newValue);
                case ChronoField::EPOCH_DAY():
                    return LocalDate::ofEpochDay($newValue);
                case ChronoField::ALIGNED_WEEK_OF_MONTH():
                    return $this->plusWeeks($newValue - $this->getLong(ChronoField::ALIGNED_WEEK_OF_MONTH()));
                case ChronoField::ALIGNED_WEEK_OF_YEAR():
                    return $this->plusWeeks($newValue - $this->getLong(ChronoField::ALIGNED_WEEK_OF_YEAR()));
                case ChronoField::MONTH_OF_YEAR():
                    return $this->withMonth((int)$newValue);
                case ChronoField::PROLEPTIC_MONTH():
                    return $this->plusMonths($newValue - $this->getProlepticMonth());
                case ChronoField::YEAR_OF_ERA():
                    return $this->withYear((int)($this->year >= 1 ? $newValue : 1 - $newValue));
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
     * Returns a copy of this {@code LocalDate} with the year altered.
     * <p>
     * If the day-of-month is invalid for the year, it will be changed to the last valid day of the month.
     * <p>
     * This instance is immutable and unaffected by this method call.
     *
     * @param int $year the year to set in the result, from MIN_YEAR to MAX_YEAR
     * @return LocalDate a {@code LocalDate} based on this date with the requested year, not null
     * @throws DateTimeException if the year value is invalid
     */
    public function withYear($year)
    {
        if ($this->year == $year) {
            return $this;
        }

        ChronoField::YEAR()->checkValidValue($year);
        return self::resolvePreviousValid($year, $this->month, $this->day);
    }

    /**
     * Returns a copy of this {@code LocalDate} with the month-of-year altered.
     * <p>
     * If the day-of-month is invalid for the year, it will be changed to the last valid day of the month.
     * <p>
     * This instance is immutable and unaffected by this method call.
     *
     * @param int $month the month-of-year to set in the result, from 1 (January) to 12 (December)
     * @return LocalDate a {@code LocalDate} based on this date with the requested month, not null
     * @throws DateTimeException if the month-of-year value is invalid
     */
    public function withMonth($month)
    {
        if ($this->month == $month) {
            return $this;
        }

        ChronoField::MONTH_OF_YEAR()->checkValidValue($month);
        return self::resolvePreviousValid($this->year, $month, $this->day);
    }

    /**
     * Returns a copy of this {@code LocalDate} with the day-of-month altered.
     * <p>
     * If the resulting date is invalid, an exception is thrown.
     * <p>
     * This instance is immutable and unaffected by this method call.
     *
     * @param int $dayOfMonth the day-of-month to set in the result, from 1 to 28-31
     * @return LocalDate a {@code LocalDate} based on this date with the requested day, not null
     * @throws DateTimeException if the day-of-month value is invalid,
     *  or if the day-of-month is invalid for the month-year
     */
    public function withDayOfMonth($dayOfMonth)
    {
        if ($this->day == $dayOfMonth) {
            return $this;
        }

        return self::ofNumerical($this->year, $this->month, $dayOfMonth);
    }

    /**
     * Returns a copy of this {@code LocalDate} with the day-of-year altered.
     * <p>
     * If the resulting date is invalid, an exception is thrown.
     * <p>
     * This instance is immutable and unaffected by this method call.
     *
     * @param int $dayOfYear the day-of-year to set in the result, from 1 to 365-366
     * @return LocalDate a {@code LocalDate} based on this date with the requested day, not null
     * @throws DateTimeException if the day-of-year value is invalid,
     *  or if the day-of-year is invalid for the year
     */
    public function withDayOfYear($dayOfYear)
    {
        if ($this->getDayOfYear() == $dayOfYear) {
            return $this;
        }

        return self::ofYearDay($this->year, $dayOfYear);
    }

//-----------------------------------------------------------------------
    /**
     * Returns a copy of this date with the specified amount added.
     * <p>
     * This returns a {@code LocalDate}, based on this one, with the specified amount added.
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
     * @param TemporalAmount $amountToAdd the amount to add, not null
     * @return LocalDate a {@code LocalDate} based on this date with the addition made, not null
     * @throws DateTimeException if the addition cannot be made
     * @throws ArithmeticException if numeric overflow occurs
     */
    public function plusAmount(TemporalAmount $amountToAdd)
    {
        if ($amountToAdd instanceof Period) {
            $periodToAdd = $amountToAdd;
            return $this->plusMonths($periodToAdd->toTotalMonths())->plusDays($periodToAdd->getDays());
        }

        return $amountToAdd->addTo($this);
    }

    /**
     * Returns a copy of this date with the specified amount added.
     * <p>
     * This returns a {@code LocalDate}, based on this one, with the amount
     * in terms of the unit added. If it is not possible to add the amount, because the
     * unit is not supported or for some other reason, an exception is thrown.
     * <p>
     * In some cases, adding the amount can cause the resulting date to become invalid.
     * For example, adding one month to 31st January would result in 31st February.
     * In cases like this, the unit is responsible for resolving the date.
     * Typically it will choose the previous valid date, which would be the last valid
     * day of February in this example.
     * <p>
     * If the field is a {@link ChronoUnit} then the addition is implemented here.
     * The supported fields behave as follows:
     * <ul>
     * <li>{@code DAYS} -
     *  Returns a {@code LocalDate} with the specified number of days added.
     *  This is equivalent to {@link #plusDays(long)}.
     * <li>{@code WEEKS} -
     *  Returns a {@code LocalDate} with the specified number of weeks added.
     *  This is equivalent to {@link #plusWeeks(long)} and uses a 7 day week.
     * <li>{@code MONTHS} -
     *  Returns a {@code LocalDate} with the specified number of months added.
     *  This is equivalent to {@link #plusMonths(long)}.
     *  The day-of-month will be unchanged unless it would be invalid for the new
     *  month and year. In that case, the day-of-month is adjusted to the maximum
     *  valid value for the new month and year.
     * <li>{@code YEARS} -
     *  Returns a {@code LocalDate} with the specified number of years added.
     *  This is equivalent to {@link #plusYears(long)}.
     *  The day-of-month will be unchanged unless it would be invalid for the new
     *  month and year. In that case, the day-of-month is adjusted to the maximum
     *  valid value for the new month and year.
     * <li>{@code DECADES} -
     *  Returns a {@code LocalDate} with the specified number of decades added.
     *  This is equivalent to calling {@link #plusYears(long)} with the amount
     *  multiplied by 10.
     *  The day-of-month will be unchanged unless it would be invalid for the new
     *  month and year. In that case, the day-of-month is adjusted to the maximum
     *  valid value for the new month and year.
     * <li>{@code CENTURIES} -
     *  Returns a {@code LocalDate} with the specified number of centuries added.
     *  This is equivalent to calling {@link #plusYears(long)} with the amount
     *  multiplied by 100.
     *  The day-of-month will be unchanged unless it would be invalid for the new
     *  month and year. In that case, the day-of-month is adjusted to the maximum
     *  valid value for the new month and year.
     * <li>{@code MILLENNIA} -
     *  Returns a {@code LocalDate} with the specified number of millennia added.
     *  This is equivalent to calling {@link #plusYears(long)} with the amount
     *  multiplied by 1,000.
     *  The day-of-month will be unchanged unless it would be invalid for the new
     *  month and year. In that case, the day-of-month is adjusted to the maximum
     *  valid value for the new month and year.
     * <li>{@code ERAS} -
     *  Returns a {@code LocalDate} with the specified number of eras added.
     *  Only two eras are supported so the amount must be one, zero or minus one.
     *  If the amount is non-zero then the year is changed such that the year-of-era
     *  is unchanged.
     *  The day-of-month will be unchanged unless it would be invalid for the new
     *  month and year. In that case, the day-of-month is adjusted to the maximum
     *  valid value for the new month and year.
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
     * @param int $amountToAdd the amount of the unit to add to the result, may be negative
     * @param TemporalUnit $unit the unit of the amount to add, not null
     * @return LocalDate a {@code LocalDate} based on this date with the specified amount added, not null
     * @throws DateTimeException if the addition cannot be made
     * @throws UnsupportedTemporalTypeException if the unit is not supported
     * @throws ArithmeticException if numeric overflow occurs
     */
    public function plus($amountToAdd, TemporalUnit $unit)
    {
        if ($unit instanceof ChronoUnit) {
            /** @var ChronoUnit $f */
            $f = $unit;
            switch ($f) {
                case ChronoUnit::DAYS():
                    return $this->plusDays($amountToAdd);
                case ChronoUnit::WEEKS():
                    return $this->plusWeeks($amountToAdd);
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

    //-----------------------------------------------------------------------
    /**
     * Returns a copy of this {@code LocalDate} with the specified number of years added.
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
     * @param int $yearsToAdd the years to add, may be negative
     * @return LocalDate a {@code LocalDate} based on this date with the years added, not null
     * @throws DateTimeException if the result exceeds the supported date range
     */
    public function plusYears($yearsToAdd)
    {
        if ($yearsToAdd == 0) {
            return $this;
        }

        $newYear = ChronoField::YEAR()->checkValidIntValue($this->year + $yearsToAdd);  // safe overflow
        return self::resolvePreviousValid($newYear, $this->month, $this->day);
    }

    /**
     * Returns a copy of this {@code LocalDate} with the specified number of months added.
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
     * @param int $monthsToAdd the months to add, may be negative
     * @return LocalDate a {@code LocalDate} based on this date with the months added, not null
     * @throws DateTimeException if the result exceeds the supported date range
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
        return self::resolvePreviousValid($newYear, $newMonth, $this->day);
    }

    /**
     * Returns a copy of this {@code LocalDate} with the specified number of weeks added.
     * <p>
     * This method adds the specified amount in weeks to the days field incrementing
     * the month and year fields as necessary to ensure the result remains valid.
     * The result is only invalid if the maximum/minimum year is exceeded.
     * <p>
     * For example, 2008-12-31 plus one week would result in 2009-01-07.
     * <p>
     * This instance is immutable and unaffected by this method call.
     *
     * @param int $weeksToAdd the weeks to add, may be negative
     * @return LocalDate a {@code LocalDate} based on this date with the weeks added, not null
     * @throws DateTimeException if the result exceeds the supported date range
     */
    public function plusWeeks($weeksToAdd)
    {
        return $this->plusDays(Math::multiplyExact($weeksToAdd, 7));
    }

    /**
     * Returns a copy of this {@code LocalDate} with the specified number of days added.
     * <p>
     * This method adds the specified amount to the days field incrementing the
     * month and year fields as necessary to ensure the result remains valid.
     * The result is only invalid if the maximum/minimum year is exceeded.
     * <p>
     * For example, 2008-12-31 plus one day would result in 2009-01-01.
     * <p>
     * This instance is immutable and unaffected by this method call.
     *
     * @param int $daysToAdd the days to add, may be negative
     * @return LocalDate a {@code LocalDate} based on this date with the days added, not null
     * @throws DateTimeException if the result exceeds the supported date range
     */
    public
    function plusDays($daysToAdd)
    {
        if ($daysToAdd == 0) {
            return $this;
        }

        $mjDay = Math::addExact($this->toEpochDay(), $daysToAdd);
        return self::ofEpochDay($mjDay);
    }

    //-----------------------------------------------------------------------
    /**
     * Returns a copy of this date with the specified amount subtracted.
     * <p>
     * This returns a {@code LocalDate}, based on this one, with the specified amount subtracted.
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
     * @param TemporalAmount $amountToSubtract the amount to subtract, not null
     * @return LocalDate a {@code LocalDate} based on this date with the subtraction made, not null
     * @throws DateTimeException if the subtraction cannot be made
     * @throws ArithmeticException if numeric overflow occurs
     */
    public function minusAmount(TemporalAmount $amountToSubtract)
    {
        if ($amountToSubtract instanceof Period) {
            /** @var Period $periodToSubtract */
            $periodToSubtract = $amountToSubtract;
            return $this->minusMonths($periodToSubtract->toTotalMonths())->minusDays($periodToSubtract->getDays());
        }

        return $amountToSubtract->subtractFrom($this);
    }

    /**
     * Returns a copy of this date with the specified amount subtracted.
     * <p>
     * This returns a {@code LocalDate}, based on this one, with the amount
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
     * @return LocalDate a {@code LocalDate} based on this date with the specified amount subtracted, not null
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
     * Returns a copy of this {@code LocalDate} with the specified number of years subtracted.
     * <p>
     * This method subtracts the specified amount from the years field in three steps:
     * <ol>
     * <li>Subtract the input years from the year field</li>
     * <li>Check if the resulting date would be invalid</li>
     * <li>Adjust the day-of-month to the last valid day if necessary</li>
     * </ol>
     * <p>
     * For example, 2008-02-29 (leap year) minus one year would result in the
     * invalid date 2007-02-29 (standard year). Instead of returning an invalid
     * result, the last valid day of the month, 2007-02-28, is selected instead.
     * <p>
     * This instance is immutable and unaffected by this method call.
     *
     * @param int $yearsToSubtract the years to subtract, may be negative
     * @return LocalDate a {@code LocalDate} based on this date with the years subtracted, not null
     * @throws DateTimeException if the result exceeds the supported date range
     */
    public
    function minusYears($yearsToSubtract)
    {
        return ($yearsToSubtract == Long::MIN_VALUE ? $this->plusYears(Long::MAX_VALUE)->plusYears(1) : $this->plusYears(-$yearsToSubtract));
    }

    /**
     * Returns a copy of this {@code LocalDate} with the specified number of months subtracted.
     * <p>
     * This method subtracts the specified amount from the months field in three steps:
     * <ol>
     * <li>Subtract the input months from the month-of-year field</li>
     * <li>Check if the resulting date would be invalid</li>
     * <li>Adjust the day-of-month to the last valid day if necessary</li>
     * </ol>
     * <p>
     * For example, 2007-03-31 minus one month would result in the invalid date
     * 2007-02-31. Instead of returning an invalid result, the last valid day
     * of the month, 2007-02-28, is selected instead.
     * <p>
     * This instance is immutable and unaffected by this method call.
     *
     * @param int $monthsToSubtract the months to subtract, may be negative
     * @return LocalDate a {@code LocalDate} based on this date with the months subtracted, not null
     * @throws DateTimeException if the result exceeds the supported date range
     */
    public
    function minusMonths($monthsToSubtract)
    {
        return ($monthsToSubtract == Long::MIN_VALUE ? $this->plusMonths(Long::MAX_VALUE)->plusMonths(1) : $this->plusMonths(-$monthsToSubtract));
    }

    /**
     * Returns a copy of this {@code LocalDate} with the specified number of weeks subtracted.
     * <p>
     * This method subtracts the specified amount in weeks from the days field decrementing
     * the month and year fields as necessary to ensure the result remains valid.
     * The result is only invalid if the maximum/minimum year is exceeded.
     * <p>
     * For example, 2009-01-07 minus one week would result in 2008-12-31.
     * <p>
     * This instance is immutable and unaffected by this method call.
     *
     * @param int $weeksToSubtract the weeks to subtract, may be negative
     * @return LocalDate a {@code LocalDate} based on this date with the weeks subtracted, not null
     * @throws DateTimeException if the result exceeds the supported date range
     */
    public function minusWeeks($weeksToSubtract)
    {
        return ($weeksToSubtract == Long::MIN_VALUE ? $this->plusWeeks(Long::MAX_VALUE)->plusWeeks(1) : $this->plusWeeks(-$weeksToSubtract));
    }

    /**
     * Returns a copy of this {@code LocalDate} with the specified number of days subtracted.
     * <p>
     * This method subtracts the specified amount from the days field decrementing the
     * month and year fields as necessary to ensure the result remains valid.
     * The result is only invalid if the maximum/minimum year is exceeded.
     * <p>
     * For example, 2009-01-01 minus one day would result in 2008-12-31.
     * <p>
     * This instance is immutable and unaffected by this method call.
     *
     * @param int $daysToSubtract the days to subtract, may be negative
     * @return LocalDate a {@code LocalDate} based on this date with the days subtracted, not null
     * @throws DateTimeException if the result exceeds the supported date range
     */
    public function minusDays($daysToSubtract)
    {
        return ($daysToSubtract == Long::MIN_VALUE ? $this->plusDays(Long::MAX_VALUE)->plusDays(1) : $this->plusDays(-$daysToSubtract));
    }

    //-----------------------------------------------------------------------
    /**
     * Queries this date using the specified query.
     * <p>
     * This queries this date using the specified query strategy object.
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
        if ($query == TemporalQueries::localDate()) {
            return $this;
        }

        return ChronoLocalDateDefaults::query($this, $query);
    }

    /**
     * Adjusts the specified temporal object to have the same date as this object.
     * <p>
     * This returns a temporal object of the same observable type as the input
     * with the date changed to be the same as this.
     * <p>
     * The adjustment is equivalent to using {@link Temporal#with(TemporalField, long)}
     * passing {@link ChronoField#EPOCH_DAY} as the field.
     * <p>
     * In most cases, it is clearer to reverse the calling pattern by using
     * {@link Temporal#with(TemporalAdjuster)}:
     * <pre>
     *   // these two lines are equivalent, but the second approach is recommended
     *   temporal = thisLocalDate.adjustInto(temporal);
     *   temporal = temporal.with(thisLocalDate);
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
        return ChronoLocalDateDefaults::adjustInto($this, $temporal);
    }

    /**
     * Calculates the amount of time until another date in terms of the specified unit.
     * <p>
     * This calculates the amount of time between two {@code LocalDate}
     * objects in terms of a single {@code TemporalUnit}.
     * The start and end points are {@code this} and the specified date.
     * The result will be negative if the end is before the start.
     * The {@code Temporal} passed to this method is converted to a
     * {@code LocalDate} using {@link #from(TemporalAccessor)}.
     * For example, the amount in days between two dates can be calculated
     * using {@code startDate.until(endDate, DAYS)}.
     * <p>
     * The calculation returns a whole number, representing the number of
     * complete units between the two dates.
     * For example, the amount in months between 2012-06-15 and 2012-08-14
     * will only be one month as it is one day short of two months.
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
     * The units {@code DAYS}, {@code WEEKS}, {@code MONTHS}, {@code YEARS},
     * {@code DECADES}, {@code CENTURIES}, {@code MILLENNIA} and {@code ERAS}
     * are supported. Other {@code ChronoUnit} values will throw an exception.
     * <p>
     * If the unit is not a {@code ChronoUnit}, then the result of this method
     * is obtained by invoking {@code TemporalUnit.between(Temporal, Temporal)}
     * passing {@code this} as the first argument and the converted input temporal
     * as the second argument.
     * <p>
     * This instance is immutable and unaffected by this method call.
     *
     * @param Temporal $endExclusive the end date, exclusive, which is converted to a {@code LocalDate}, not null
     * @param TemporalUnit $unit the unit to measure the amount in, not null
     * @return int the amount of time between this date and the end date
     * @throws DateTimeException if the amount cannot be calculated, or the end
     *  temporal cannot be converted to a {@code LocalDate}
     * @throws UnsupportedTemporalTypeException if the unit is not supported
     * @throws ArithmeticException if numeric overflow occurs
     */
    public function until(Temporal $endExclusive, TemporalUnit $unit)
    {
        $end = LocalDate::from($endExclusive);
        if ($unit instanceof ChronoUnit) {
            switch ($unit) {
                case ChronoUnit::DAYS():
                    return $this->daysUntil($end);
                case ChronoUnit::WEEKS():
                    return $this->daysUntil($end) / 7;
                case ChronoUnit::MONTHS():
                    return $this->monthsUntil($end);
                case ChronoUnit::YEARS():
                    return $this->monthsUntil($end) / 12;
                case ChronoUnit::DECADES():
                    return $this->monthsUntil($end) / 120;
                case ChronoUnit::CENTURIES():
                    return $this->monthsUntil($end) / 1200;
                case ChronoUnit::MILLENNIA():
                    return $this->monthsUntil($end) / 12000;
                case ChronoUnit::ERAS():
                    return $end->getLong(ChronoField::ERA()) - $this->getLong(ChronoField::ERA());
            }

            throw new UnsupportedTemporalTypeException("Unsupported unit: " . $unit);
        }
        return $unit->between($this, $end);
    }

    /**
     * TODO Should be package local
     * @internal
     * @param LocalDate $end
     * @return int
     */
    function daysUntil(LocalDate $end)
    {
        return $end->toEpochDay() - $this->toEpochDay();  // no overflow
    }

    private function monthsUntil(LocalDate $end)
    {
        $packed1 = $this->getProlepticMonth() * 32 + $this->getDayOfMonth();  // no overflow
        $packed2 = $end->getProlepticMonth() * 32 + $end->getDayOfMonth();  // no overflow
        return ($packed2 - $packed1) / 32;
    }

    /**
     * Calculates the period between this date and another date as a {@code Period}.
     * <p>
     * This calculates the period between two dates in terms of years, months and days.
     * The start and end points are {@code this} and the specified date.
     * The result will be negative if the end is before the start.
     * The negative sign will be the same in each of year, month and day.
     * <p>
     * The calculation is performed using the ISO calendar system.
     * If necessary, the input date will be converted to ISO.
     * <p>
     * The start date is included, but the end date is not.
     * The period is calculated by removing complete months, then calculating
     * the remaining number of days, adjusting to ensure that both have the same sign.
     * The number of months is then normalized into years and months based on a 12 month year.
     * A month is considered to be complete if the end day-of-month is greater
     * than or equal to the start day-of-month.
     * For example, from {@code 2010-01-15} to {@code 2011-03-18} is "1 year, 2 months and 3 days".
     * <p>
     * There are two equivalent ways of using this method.
     * The first is to invoke this method.
     * The second is to use {@link Period#between(LocalDate, LocalDate)}:
     * <pre>
     *   // these two lines are equivalent
     *   period = start.until(end);
     *   period = Period.between(start, end);
     * </pre>
     * The choice should be made based on which makes the code more readable.
     *
     * @param ChronoLocalDate $endDateExclusive the end date, exclusive, which may be in any chronology, not null
     * @return Period the period between this date and the end date, not null
     */
    public function untilDate(ChronoLocalDate $endDateExclusive)
    {
        $end = LocalDate::from($endDateExclusive);
        $totalMonths = $end->getProlepticMonth() - $this->getProlepticMonth();  // safe
        $days = $end->day - $this->day;
        if ($totalMonths > 0 && $days < 0) {
            $totalMonths--;
            $calcDate = $this->plusMonths($totalMonths);
            $days = (int)($end->toEpochDay() - $calcDate->toEpochDay());  // safe
        } else
            if ($totalMonths < 0 && $days > 0) {
                $totalMonths++;
                $days -= $end->lengthOfMonth();
            }
        $years = $totalMonths / 12;  // safe
        $months = (int)($totalMonths % 12);  // safe
        return Period::of(Math::toIntExact($years), $months, $days);
    }

    /**
     * Formats this date using the specified formatter.
     * <p>
     * This date will be passed to the formatter to produce a string.
     *
     * @param DateTimeFormatter $formatter the formatter to use, not null
     * @return string the formatted date string, not null
     * @throws DateTimeException if an error occurs during printing
     */
    public function format(DateTimeFormatter $formatter)
    {
        return $formatter->format($this);
    }

//-----------------------------------------------------------------------
    /**
     * Combines this date with a time to create a {@code LocalDateTime}.
     * <p>
     * This returns a {@code LocalDateTime} formed from this date at the specified time.
     * All possible combinations of date and time are valid.
     *
     * @param LocalTime $time the time to combine with, not null
     * @return LocalDateTime the local date-time formed from this date and the specified time, not null
     */
    public function atTime(LocalTime $time)
    {
        return LocalDateTime::ofDateAndTime($this, $time);
    }

    /**
     * Combines this date with a time to create a {@code LocalDateTime}.
     * <p>
     * This returns a {@code LocalDateTime} formed from this date at the
     * specified hour, minute, second and nanosecond.
     * The individual time fields must be within their valid range.
     * All possible combinations of date and time are valid.
     *
     * @param int $hour the hour-of-day to use, from 0 to 23
     * @param int $minute the minute-of-hour to use, from 0 to 59
     * @param int $second the second-of-minute to represent, from 0 to 59
     * @param int $nanoOfSecond the nano-of-second to represent, from 0 to 999,999,999
     * @return LocalDateTime the local date-time formed from this date and the specified time, not null
     * @throws DateTimeException if the value of any field is out of range
     */
    public function atTimeNumerical($hour, $minute, $second = 0, $nanoOfSecond = 0)
    {
        return $this->atTime(LocalTime::of($hour, $minute, $second, $nanoOfSecond));
    }

    /**
     * Combines this date with an offset time to create an {@code OffsetDateTime}.
     * <p>
     * This returns an {@code OffsetDateTime} formed from this date at the specified time.
     * All possible combinations of date and time are valid.
     *
     * @param OffsetTime $time the time to combine with, not null
     * @return OffsetDateTime the offset date-time formed from this date and the specified time, not null
     */
    public
    function atOffsetTime(OffsetTime $time)
    {
        return OffsetDateTime::of(LocalDateTime::ofDateAndTime($this, $time->toLocalTime()), $time->getOffset());
    }

    /**
     * Combines this date with the time of midnight to create a {@code LocalDateTime}
     * at the start of this date.
     * <p>
     * This returns a {@code LocalDateTime} formed from this date at the time of
     * midnight, 00:00, at the start of this date.
     *
     * @return LocalDateTime the local date-time of midnight at the start of this date, not null
     */
    public function atStartOfDay()
    {
        return LocalDateTime::ofDateAndTime($this, LocalTime::MIDNIGHT());
    }

    /**
     * Returns a zoned date-time from this date at the earliest valid time according
     * to the rules in the time-zone.
     * <p>
     * Time-zone rules, such as daylight savings, mean that not every local date-time
     * is valid for the specified zone, thus the local date-time may not be midnight.
     * <p>
     * In most cases, there is only one valid offset for a local date-time.
     * In the case of an overlap, there are two valid offsets, and the earlier one is used,
     * corresponding to the first occurrence of midnight on the date.
     * In the case of a gap, the zoned date-time will represent the instant just after the gap.
     * <p>
     * If the zone ID is a {@link ZoneOffset}, then the result always has a time of midnight.
     * <p>
     * To convert to a specific time in a given time-zone call {@link #atTime(LocalTime)}
     * followed by {@link LocalDateTime#atZone(ZoneId)}.
     *
     * @param ZoneId $zone the zone ID to use, not null
     * @return ZonedDateTime the zoned date-time formed from this date and the earliest valid time for the zone, not null
     */
    public function atStartOfDayWithZone(ZoneId $zone)
    {
        // need to handle case where there is a gap from 11:30 to 00:30
        // standard ZDT factory would result in 01:00 rather than 00:30
        $ldt = $this->atTime(LocalTime::MIDNIGHT());
        if ($zone instanceof ZoneOffset === false) {
            $rules = $zone->getRules();
            $trans = $rules->getTransition($ldt);
            if ($trans != null && $trans->isGap()) {
                $ldt = $trans->getDateTimeAfter();
            }
        }
        return ZonedDateTime::of($ldt, $zone);
    }

    //-----------------------------------------------------------------------
    public function toEpochDay()
    {
        $y = $this->year;
        $m = $this->month;
        $total = 0;
        $total += 365 * $y;
        if ($y >= 0) {
            $total += Math::div($y + 3, 4) - Math::div($y + 99, 100) + Math::div($y + 399, 400);
        } else {
            $total -= Math::div($y, -4) - Math::div($y, -100) + Math::div($y, -400);
        }
        $total += Math::div(367 * $m - 362, 12);
        $total += $this->day - 1;
        if ($m > 2) {
            $total--;
            if ($this->isLeapYear() == false) {
                $total--;
            }
        }
        return $total - self::DAYS_0000_TO_1970;
    }

//-----------------------------------------------------------------------
    /**
     * Compares this date to another date.
     * <p>
     * The comparison is primarily based on the date, from earliest to latest.
     * It is "consistent with equals", as defined by {@link Comparable}.
     * <p>
     * If all the dates being compared are instances of {@code LocalDate},
     * then the comparison will be entirely based on the date.
     * If some dates being compared are in different chronologies, then the
     * chronology is also considered, see {@link java.time.chrono.ChronoLocalDate#compareTo}.
     *
     * @param ChronoLocalDate $other the other date to compare to, not null
     * @return int the comparator value, negative if less, positive if greater
     */
    public function compareTo(ChronoLocalDate $other)
    {
        if ($other instanceof LocalDate) {
            return $this->compareTo0($other);
        }

        return ChronoLocalDateDefaults::compareTo($this, $other);
    }

    /**
     * TODO internal
     * @param LocalDate $otherDate
     * @return int
     */
    function compareTo0(LocalDate $otherDate)
    {
        $cmp = ($this->year - $otherDate->year);
        if ($cmp == 0) {
            $cmp = ($this->month - $otherDate->month);
            if ($cmp == 0) {
                $cmp = ($this->day - $otherDate->day);
            }
        }
        return $cmp;
    }

    /**
     * Checks if this date is after the specified date.
     * <p>
     * This checks to see if this date represents a point on the
     * local time-line after the other date.
     * <pre>
     *   LocalDate a = LocalDate.of(2012, 6, 30);
     *   LocalDate b = LocalDate.of(2012, 7, 1);
     *   a.isAfter(b) == false
     *   a.isAfter(a) == false
     *   b.isAfter(a) == true
     * </pre>
     * <p>
     * This method only considers the position of the two dates on the local time-line.
     * It does not take into account the chronology, or calendar system.
     * This is different from the comparison in {@link #compareTo(ChronoLocalDate)},
     * but is the same approach as {@link ChronoLocalDate#timeLineOrder()}.
     *
     * @param ChronoLocalDate $other the other date to compare to, not null
     * @return bool true if this date is after the specified date
     */
    public function isAfter(ChronoLocalDate $other)
    {
        if ($other instanceof LocalDate) {
            return $this->compareTo0($other) > 0;
        }

        return ChronoLocalDateDefaults::isAfter($this, $other);
    }

    /**
     * Checks if this date is before the specified date.
     * <p>
     * This checks to see if this date represents a point on the
     * local time-line before the other date.
     * <pre>
     *   LocalDate a = LocalDate.of(2012, 6, 30);
     *   LocalDate b = LocalDate.of(2012, 7, 1);
     *   a.isBefore(b) == true
     *   a.isBefore(a) == false
     *   b.isBefore(a) == false
     * </pre>
     * <p>
     * This method only considers the position of the two dates on the local time-line.
     * It does not take into account the chronology, or calendar system.
     * This is different from the comparison in {@link #compareTo(ChronoLocalDate)},
     * but is the same approach as {@link ChronoLocalDate#timeLineOrder()}.
     *
     * @param ChronoLocalDate $other the other date to compare to, not null
     * @return bool true if this date is before the specified date
     */
    public function isBefore(ChronoLocalDate $other)
    {
        if ($other instanceof LocalDate) {
            return $this->compareTo0($other) < 0;
        }

        return ChronoLocalDateDefaults::isBefore($this, $other);
    }

    /**
     * Checks if this date is equal to the specified date.
     * <p>
     * This checks to see if this date represents the same point on the
     * local time-line as the other date.
     * <pre>
     *   LocalDate a = LocalDate.of(2012, 6, 30);
     *   LocalDate b = LocalDate.of(2012, 7, 1);
     *   a.isEqual(b) == false
     *   a.isEqual(a) == true
     *   b.isEqual(a) == false
     * </pre>
     * <p>
     * This method only considers the position of the two dates on the local time-line.
     * It does not take into account the chronology, or calendar system.
     * This is different from the comparison in {@link #compareTo(ChronoLocalDate)}
     * but is the same approach as {@link ChronoLocalDate#timeLineOrder()}.
     *
     * @param ChronoLocalDate $other the other date to compare to, not null
     * @return bool true if this date is equal to the specified date
     */
    public function isEqual(ChronoLocalDate $other)
    {
        if ($other instanceof LocalDate) {
            return $this->compareTo0($other) == 0;
        }

        return ChronoLocalDateDefaults::isEqual($this, $other);
    }

    //-----------------------------------------------------------------------
    /**
     * Checks if this date is equal to another date.
     * <p>
     * Compares this {@code LocalDate} with another ensuring that the date is the same.
     * <p>
     * Only objects of type {@code LocalDate} are compared, other types return false.
     * To compare the dates of two {@code TemporalAccessor} instances, including dates
     * in two different chronologies, use {@link ChronoField#EPOCH_DAY} as a comparator.
     *
     * @param mixed $obj the object to check, null returns false
     * @return bool true if this is equal to the other date
     */
    public function equals($obj)
    {
        if ($this == $obj) {
            return true;
        }

        if ($obj instanceof LocalDate) {
            return $this->compareTo0($obj) == 0;
        }
        return false;
    }

//-----------------------------------------------------------------------
    /**
     * Outputs this date as a {@code String}, such as {@code 2007-12-03}.
     * <p>
     * The output will be in the ISO-8601 format {@code uuuu-MM-dd}.
     *
     * @return string a string representation of this date, not null
     */
    public function __toString()
    {
        $yearValue = $this->year;
        $monthValue = $this->month;
        $dayValue = $this->day;
        $absYear = abs($yearValue);
        $buf = "";
        if ($absYear < 1000) {
            if ($yearValue < 0) {
                $yearNeg = (string)($yearValue - 10000);
                $buf .= $yearNeg[0] . substr($yearNeg, 2);
            } else {
                $buf .= substr($yearValue + 10000, 1);
            }
        } else {
            if ($yearValue > 9999) {
                $buf .= '+';
            }
            $buf .= $yearValue;
        }
        return $buf
        . ($monthValue < 10 ? "-0" : "-")
        . $monthValue
        . ($dayValue < 10 ? "-0" : "-")
        . $dayValue;
    }

    /**
     * @inheritdoc
     */
    static function timeLineOrder()
    {
        return ChronoLocalDateDefaults::timeLineOrder();
    }
}

LocalDate::init();