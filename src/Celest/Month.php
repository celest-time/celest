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

use Celest\Chrono\AbstractChronology;
use Celest\Chrono\IsoChronology;
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
 * A month-of-year, such as 'July'.
 * <p>
 * {@code Month} is an enum representing the 12 months of the year -
 * January, February, March, April, May, June, July, August, September, October,
 * November and December.
 * <p>
 * In addition to the textual enum name, each month-of-year has an {@code int} value.
 * The {@code int} value follows normal usage and the ISO-8601 standard,
 * from 1 (January) to 12 (December). It is recommended that applications use the enum
 * rather than the {@code int} value to ensure code clarity.
 * <p>
 * <b>Do not use {@code ordinal()} to obtain the numeric representation of {@code Month}.
 * Use {@code getValue()} instead.</b>
 * <p>
 * This enum represents a common concept that is found in many calendar systems.
 * As such, this enum may be used by any calendar system that has the month-of-year
 * concept defined exactly equivalent to the ISO-8601 calendar system.
 *
 * @implSpec
 * This is an immutable and thread-safe enum.
 *
 * @since 1.8
 */
final class Month extends AbstractTemporalAccessor implements TemporalAccessor, TemporalAdjuster
{
    /**
     * @internal
     */
    public static function init()
    {
        self::$JANUARY = new Month(1, "JANUARY");
        self::$FEBRUARY = new Month(2, "FEBRUARY");
        self::$MARCH = new Month(3, "MARCH");
        self::$APRIL = new Month(4, "APRIL");
        self::$MAY = new Month(5, "MAY");
        self::$JUNE = new Month(6, "JUNE");
        self::$JULY = new Month(7, "JULY");
        self::$AUGUST = new Month(8, "AUGUST");
        self::$SEPTEMBER = new Month(9, "SEPTEMBER");
        self::$OCTOBER = new Month(10, "OCTOBER");
        self::$NOVEMBER = new Month(11, "NOVEMBER");
        self::$DECEMBER = new Month(12, "DECEMBER");

        self::$ENUMS = [
            self::$JANUARY,
            self::$FEBRUARY,
            self::$MARCH,
            self::$APRIL,
            self::$MAY,
            self::$JUNE,
            self::$JULY,
            self::$AUGUST,
            self::$SEPTEMBER,
            self::$OCTOBER,
            self::$NOVEMBER,
            self::$DECEMBER,
        ];
    }

    /**
     * The singleton instance for the month of January with 31 days.
     * This has the numeric value of {@code 1}.
     * @return Month
     */
    public static function JANUARY()
    {
        return self::$JANUARY;
    }

    /** @var Month */
    private static $JANUARY;

    /**
     * The singleton instance for the month of February with 28 days, or 29 in a leap year.
     * This has the numeric value of {@code 2}.
     * @return Month
     */
    public static function FEBRUARY()
    {
        return self::$FEBRUARY;
    }

    /** @var Month */
    private static $FEBRUARY;

    /**
     * The singleton instance for the month of March with 31 days.
     * This has the numeric value of {@code 3}.
     * @return Month
     */
    public static function MARCH()
    {
        return self::$MARCH;
    }

    /** @var Month */
    private static $MARCH;

    /**
     * The singleton instance for the month of April with 30 days.
     * This has the numeric value of {@code 4}.
     * @return Month
     */
    public static function APRIL()
    {
        return self::$APRIL;
    }

    /** @var Month */
    private static $APRIL;

    /**
     * The singleton instance for the month of May with 31 days.
     * This has the numeric value of {@code 5}.
     * @return Month
     */
    public static function MAY()
    {
        return self::$MAY;
    }

    /** @var Month */
    private static $MAY;

    /**
     * The singleton instance for the month of June with 30 days.
     * This has the numeric value of {@code 6}.
     * @return Month
     */
    public static function JUNE()
    {
        return self::$JUNE;
    }

    /** @var Month */
    private static $JUNE;

    /**
     * The singleton instance for the month of July with 31 days.
     * This has the numeric value of {@code 7}.
     * @return Month
     */
    public static function JULY()
    {
        return self::$JULY;
    }

    /** @var Month */
    private static $JULY;

    /**
     * The singleton instance for the month of August with 31 days.
     * This has the numeric value of {@code 8}.
     * @return Month
     */
    public static function AUGUST()
    {
        return self::$AUGUST;
    }

    /** @var Month */
    private static $AUGUST;

    /**
     * The singleton instance for the month of September with 30 days.
     * This has the numeric value of {@code 9}.
     * @return Month
     */
    public static function SEPTEMBER()
    {
        return self::$SEPTEMBER;
    }

    /** @var Month */
    private static $SEPTEMBER;

    /**
     * The singleton instance for the month of October with 31 days.
     * This has the numeric value of {@code 10}.
     * @return Month
     */
    public static function OCTOBER()
    {
        return self::$OCTOBER;
    }

    /** @var Month */
    private static $OCTOBER;

    /**
     * The singleton instance for the month of November with 30 days.
     * This has the numeric value of {@code 11}.
     * @return Month
     */
    public static function NOVEMBER()
    {
        return self::$NOVEMBER;
    }

    /** @var Month */
    private static $NOVEMBER;

    /**
     * The singleton instance for the month of December with 31 days.
     * This has the numeric value of {@code 12}.
     * @return Month
     */
    public static function DECEMBER()
    {
        return self::$DECEMBER;
    }

    /** @var Month */
    private static $DECEMBER;

    /**
     * Private cache of all the constants.
     * @var Month[]
     */
    private static $ENUMS;

    /** @var int */
    private $val;
    /** @var string */
    private $name;

    private function __construct($val, $name)
    {
        $this->val = $val;
        $this->name = $name;
    }

    //-----------------------------------------------------------------------
    /**
     * Obtains an instance of {@code Month} from an {@code int} value.
     * <p>
     * {@code Month} is an enum representing the 12 months of the year.
     * This factory allows the enum to be obtained from the {@code int} value.
     * The {@code int} value follows the ISO-8601 standard, from 1 (January) to 12 (December).
     *
     * @param int $month the month-of-year to represent, from 1 (January) to 12 (December)
     * @return Month the month-of-year, not null
     * @throws DateTimeException if the month-of-year is invalid
     */
    public static function of($month)
    {
        if ($month < 1 || $month > 12) {
            throw new DateTimeException("Invalid value for MonthOfYear: " . $month);
        }
        return self::$ENUMS[$month - 1];
    }

    //-----------------------------------------------------------------------
    /**
     * Obtains an instance of {@code Month} from a temporal object.
     * <p>
     * This obtains a month based on the specified temporal.
     * A {@code TemporalAccessor} represents an arbitrary set of date and time information,
     * which this factory converts to an instance of {@code Month}.
     * <p>
     * The conversion extracts the {@link ChronoField#MONTH_OF_YEAR MONTH_OF_YEAR} field.
     * The extraction is only permitted if the temporal object has an ISO
     * chronology, or can be converted to a {@code LocalDate}.
     * <p>
     * This method matches the signature of the functional interface {@link TemporalQuery}
     * allowing it to be used as a query via method reference, {@code Month::from}.
     *
     * @param TemporalAccessor $temporal the temporal object to convert, not null
     * @return Month the month-of-year, not null
     * @throws DateTimeException if unable to convert to a {@code Month}
     */
    public static function from(TemporalAccessor $temporal)
    {
        if ($temporal instanceof Month) {
            return $temporal;
        }
        try {
            if (IsoChronology::INSTANCE()->equals(AbstractChronology::from($temporal)) === false) {
                $temporal = LocalDate::from($temporal);
            }
            return self::of($temporal->get(ChronoField::MONTH_OF_YEAR()));
        } catch (DateTimeException $ex) {
            throw new DateTimeException("Unable to obtain Month from TemporalAccessor: " .
                $temporal . " of type " . get_class($temporal), $ex);
        }
    }

    /**
     * @return Month[]
     */
    public static function values()
    {
        return self::$ENUMS;
    }

    public static function valueOf($string)
    {
        switch ($string) {
            case 'JANUARY':
                return self::JANUARY();
            case 'FEBRUARY':
                return self::FEBRUARY();
            case 'MARCH':
                return self::MARCH();
            case 'APRIL':
                return self::APRIL();
            case 'MAY':
                return self::MAY();
            case 'JUNE':
                return self::JUNE();
            case 'JULY':
                return self::JULY();
            case 'AUGUST':
                return self::AUGUST();
            case 'SEPTEMBER':
                return self::SEPTEMBER();
            case 'OCTOBER':
                return self::OCTOBER();
            case 'NOVEMBER':
                return self::NOVEMBER();
            case 'DECEMBER':
                return self::DECEMBER();
        }
        throw new \InvalidArgumentException();
    }

    //-----------------------------------------------------------------------
    /**
     * Gets the month-of-year {@code int} value.
     * <p>
     * The values are numbered following the ISO-8601 standard,
     * from 1 (January) to 12 (December).
     *
     * @return int the month-of-year, from 1 (January) to 12 (December)
     */
    public function getValue()
    {
        return $this->val;
    }

    //-----------------------------------------------------------------------
    /**
     * Gets the textual representation, such as 'Jan' or 'December'.
     * <p>
     * This returns the textual name used to identify the month-of-year,
     * suitable for presentation to the user.
     * The parameters control the style of the returned text and the locale.
     * <p>
     * If no textual mapping is found then the {@link #getValue() numeric value} is returned.
     *
     * @param TextStyle $style the length of the text required, not null
     * @param Locale $locale the locale to use, not null
     * @return string the text value of the month-of-year, not null
     */
    public function getDisplayName(TextStyle $style, Locale $locale)
    {
        return (new DateTimeFormatterBuilder())->appendText2(ChronoField::MONTH_OF_YEAR(), $style)->toFormatter2($locale)->format($this);
    }

    //-----------------------------------------------------------------------
    /**
     * Checks if the specified field is supported.
     * <p>
     * This checks if this month-of-year can be queried for the specified field.
     * If false, then calling the {@link #range(TemporalField) range} and
     * {@link #get(TemporalField) get} methods will throw an exception.
     * <p>
     * If the field is {@link ChronoField#MONTH_OF_YEAR MONTH_OF_YEAR} then
     * this method returns true.
     * All other {@code ChronoField} instances will return false.
     * <p>
     * If the field is not a {@code ChronoField}, then the result of this method
     * is obtained by invoking {@code TemporalField.isSupportedBy(TemporalAccessor)}
     * passing {@code this} as the argument.
     * Whether the field is supported is determined by the field.
     *
     * @param TemporalField $field the field to check, null returns false
     * @return bool true if the field is supported on this month-of-year, false if not
     */
    public function isSupported(TemporalField $field)
    {
        if ($field instanceof ChronoField) {
            return $field == ChronoField::MONTH_OF_YEAR();
        }

        return $field !== null && $field->isSupportedBy($this);
    }

    /**
     * Gets the range of valid values for the specified field.
     * <p>
     * The range object expresses the minimum and maximum valid values for a field.
     * This month is used to enhance the accuracy of the returned range.
     * If it is not possible to return the range, because the field is not supported
     * or for some other reason, an exception is thrown.
     * <p>
     * If the field is {@link ChronoField#MONTH_OF_YEAR MONTH_OF_YEAR} then the
     * range of the month-of-year, from 1 to 12, will be returned.
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
        if ($field == ChronoField::MONTH_OF_YEAR()) {
            return $field->range();
        }

        return parent::range($field);
    }

    /**
     * Gets the value of the specified field from this month-of-year as an {@code int}.
     * <p>
     * This queries this month for the value of the specified field.
     * The returned value will always be within the valid range of values for the field.
     * If it is not possible to return the value, because the field is not supported
     * or for some other reason, an exception is thrown.
     * <p>
     * If the field is {@link ChronoField#MONTH_OF_YEAR MONTH_OF_YEAR} then the
     * value of the month-of-year, from 1 to 12, will be returned.
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
        if ($field == ChronoField::MONTH_OF_YEAR()) {
            return $this->getValue();
        }

        return parent::get($field);
    }

    /**
     * Gets the value of the specified field from this month-of-year as a {@code long}.
     * <p>
     * This queries this month for the value of the specified field.
     * If it is not possible to return the value, because the field is not supported
     * or for some other reason, an exception is thrown.
     * <p>
     * If the field is {@link ChronoField#MONTH_OF_YEAR MONTH_OF_YEAR} then the
     * value of the month-of-year, from 1 to 12, will be returned.
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
        if ($field == ChronoField::MONTH_OF_YEAR()) {
            return $this->getValue();
        } else
            if ($field instanceof ChronoField) {
                throw new UnsupportedTemporalTypeException("Unsupported field: " . $field);
            }
        return $field->getFrom($this);
    }

    //-----------------------------------------------------------------------
    /**
     * Returns the month-of-year that is the specified number of quarters after this one.
     * <p>
     * The calculation rolls around the end of the year from December to January.
     * The specified period may be negative.
     * <p>
     * This instance is immutable and unaffected by this method call.
     *
     * @param int $months the months to add, positive or negative
     * @return Month the resulting month, not null
     */
    public function plus($months)
    {
        $amount = (int)($months % 12);
        return self::$ENUMS[($this->val + ($amount + 11)) % 12];
    }

    /**
     * Returns the month-of-year that is the specified number of months before this one.
     * <p>
     * The calculation rolls around the start of the year from January to December.
     * The specified period may be negative.
     * <p>
     * This instance is immutable and unaffected by this method call.
     *
     * @param int $months the months to subtract, positive or negative
     * @return Month the resulting month, not null
     */
    public function minus($months)
    {
        return $this->plus(-($months % 12));
    }

//-----------------------------------------------------------------------
    /**
     * Gets the length of this month in days.
     * <p>
     * This takes a flag to determine whether to return the length for a leap year or not.
     * <p>
     * February has 28 days in a standard year and 29 days in a leap year.
     * April, June, September and November have 30 days.
     * All other months have 31 days.
     *
     * @param bool $leapYear true if the length is required for a leap year
     * @return int the length of this month in days, from 28 to 31
     */
    public function length($leapYear)
    {
        switch ($this->val) {
            case 2:
                return ($leapYear ? 29 : 28);
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
     * Gets the minimum length of this month in days.
     * <p>
     * February has a minimum length of 28 days.
     * April, June, September and November have 30 days.
     * All other months have 31 days.
     *
     * @return int the minimum length of this month in days, from 28 to 31
     */
    public function minLength()
    {
        switch ($this->val) {
            case 2:
                return 28;
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
     * Gets the maximum length of this month in days.
     * <p>
     * February has a maximum length of 29 days.
     * April, June, September and November have 30 days.
     * All other months have 31 days.
     *
     * @return int the maximum length of this month in days, from 29 to 31
     */
    public function maxLength()
    {
        switch ($this->val) {
            case 2:
                return 29;
            case 4:
            case 6:
            case 9:
            case 11:
                return 30;
            default:
                return 31;
        }
    }

//-----------------------------------------------------------------------
    /**
     * Gets the day-of-year corresponding to the first day of this month.
     * <p>
     * This returns the day-of-year that this month begins on, using the leap
     * year flag to determine the length of February.
     *
     * @param bool $leapYear true if the length is required for a leap year
     * @return int the day of year corresponding to the first day of this month, from 1 to 336
     */
    public function firstDayOfYear($leapYear)
    {
        $leap = $leapYear ? 1 : 0;
        switch ($this->val) {
            case 1:
                return 1;
            case 2:
                return 32;
            case 3:
                return 60 + $leap;
            case 4:
                return 91 + $leap;
            case 5:
                return 121 + $leap;
            case 6:
                return 152 + $leap;
            case 7:
                return 182 + $leap;
            case 8:
                return 213 + $leap;
            case 9:
                return 244 + $leap;
            case 10:
                return 274 + $leap;
            case 11:
                return 305 + $leap;
            case 12:
            default:
                return 335 + $leap;
        }
    }

    /**
     * Gets the month corresponding to the first month of this quarter.
     * <p>
     * The year can be divided into four quarters.
     * This method returns the first month of the quarter for the base month.
     * January, February and March return January.
     * April, May and June return April.
     * July, August and September return July.
     * October, November and December return October.
     *
     * @return Month the first month of the quarter corresponding to this month, not null
     */
    public function firstMonthOfQuarter()
    {
        return self::$ENUMS[(int)(($this->val - 1) / 3) * 3];
    }

//-----------------------------------------------------------------------
    /**
     * Queries this month-of-year using the specified query.
     * <p>
     * This queries this month-of-year using the specified query strategy object.
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
        if ($query == TemporalQueries::chronology()) {
            return IsoChronology::INSTANCE();
        } else
            if ($query == TemporalQueries::precision()) {
                return ChronoUnit::MONTHS();
            }
        return parent::query($query);
    }

    /**
     * Adjusts the specified temporal object to have this month-of-year.
     * <p>
     * This returns a temporal object of the same observable type as the input
     * with the month-of-year changed to be the same as this.
     * <p>
     * The adjustment is equivalent to using {@link Temporal#with(TemporalField, long)}
     * passing {@link ChronoField#MONTH_OF_YEAR} as the field.
     * If the specified temporal object does not use the ISO calendar system then
     * a {@code DateTimeException} is thrown.
     * <p>
     * In most cases, it is clearer to reverse the calling pattern by using
     * {@link Temporal#with(TemporalAdjuster)}:
     * <pre>
     *   // these two lines are equivalent, but the second approach is recommended
     *   temporal = thisMonth.adjustInto(temporal);
     *   temporal = temporal.with(thisMonth);
     * </pre>
     * <p>
     * For example, given a date in May, the following are output:
     * <pre>
     *   dateInMay.with(JANUARY);    // four months earlier
     *   dateInMay.with(APRIL);      // one months earlier
     *   dateInMay.with(MAY);        // same date
     *   dateInMay.with(JUNE);       // one month later
     *   dateInMay.with(DECEMBER);   // seven months later
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
        if (AbstractChronology::from($temporal)->equals(IsoChronology::INSTANCE()) === false) {
            throw new DateTimeException("Adjustment only supported on ISO date-time");
        }

        return $temporal->with(ChronoField::MONTH_OF_YEAR(), $this->getValue());
    }

    public function __toString()
    {
        return $this->name;
    }

    public function compareTo(Month $other)
    {
        return $this->val - $other->val;
    }

    public function name()
    {
        return $this->__toString();
    }
}

Month::init();
