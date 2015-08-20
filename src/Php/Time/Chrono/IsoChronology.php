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
 * Copyright (c) 2012, Stephen Colebourne & Michael Nascimento Santos
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

namespace Php\Time\Chrono;

use Php\Time\Format\ResolverStyle;
use Php\Time\Helper\Math;
use Php\Time\LocalDate;
use Php\Time\DateTimeException;
use Php\Time\Month;
use Php\Time\Period;
use Php\Time\Temporal\ChronoField;
use Php\Time\Temporal\TemporalAccessor;
use Php\Time\Year;
use Php\Time\ZonedDateTime;
use Php\Time\LocalDateTime;
use Php\Time\ZoneId;
use Php\Time\Instant;
use Php\Time\Clock;

/**
 * The ISO calendar system.
 * <p>
 * This chronology defines the rules of the ISO calendar system.
 * This calendar system is based on the ISO-8601 standard, which is the
 * <i>de facto</i> world calendar.
 * <p>
 * The fields are defined as follows:
 * <ul>
 * <li>era - There are two eras, 'Current Era' (CE) and 'Before Current Era' (BCE).
 * <li>year-of-era - The year-of-era is the same as the proleptic-year for the current CE era.
 *  For the BCE era before the ISO epoch the year increases from 1 upwards as time goes backwards.
 * <li>proleptic-year - The proleptic year is the same as the year-of-era for the
 *  current era. For the previous era, years have zero, then negative values.
 * <li>month-of-year - There are 12 months in an ISO year, numbered from 1 to 12.
 * <li>day-of-month - There are between 28 and 31 days in each of the ISO month, numbered from 1 to 31.
 *  Months 4, 6, 9 and 11 have 30 days, Months 1, 3, 5, 7, 8, 10 and 12 have 31 days.
 *  Month 2 has 28 days, or 29 in a leap year.
 * <li>day-of-year - There are 365 days in a standard ISO year and 366 in a leap year.
 *  The days are numbered from 1 to 365 or 1 to 366.
 * <li>leap-year - Leap years occur every 4 years, except where the year is divisble by 100 and not divisble by 400.
 * </ul>
 *
 * @implSpec
 * This class is immutable and thread-safe.
 *
 * @since 1.8
 */
final class IsoChronology extends AbstractChronology
{
    /**
     * Singleton instance of the ISO chronology.
     * @return IsoChronology
     */
    public static function INSTANCE()
    {
        if (!self::$INSTANCE) {
            self::$INSTANCE = new IsoChronology();
        }

        return self::$INSTANCE;
    }

    /** @var IsoChronology */
    private static $INSTANCE;

    /**
     * Restricted constructor.
     */
    protected function __construct()
    {
    }

    //-----------------------------------------------------------------------
    /**
     * Gets the ID of the chronology - 'ISO'.
     * <p>
     * The ID uniquely identifies the {@code Chronology}.
     * It can be used to lookup the {@code Chronology} using {@link Chronology#of(String)}.
     *
     * @return string the chronology ID - 'ISO'
     * @see #getCalendarType()
     */
    public function getId()
    {
        return "ISO";
    }

    /**
     * Gets the calendar type of the underlying calendar system - 'iso8601'.
     * <p>
     * The calendar type is an identifier defined by the
     * <em>Unicode Locale Data Markup Language (LDML)</em> specification.
     * It can be used to lookup the {@code Chronology} using {@link Chronology#of(String)}.
     * It can also be used as part of a locale, accessible via
     * {@link Locale#getUnicodeLocaleType(String)} with the key 'ca'.
     *
     * @return string the calendar system type - 'iso8601'
     * @see #getId()
     */
    public function getCalendarType()
    {
        return "iso8601";
    }

    /**
     * Gets the textual representation of this chronology.
     * <p>
     * This returns the textual name used to identify the chronology,
     * suitable for presentation to the user.
     * The parameters control the style of the returned text and the locale.
     *
     * @implSpec
     * The default implementation behaves as though the formatter was used to
     * format the chronology textual name.
     *
     * @param $style TextStyle the style of the text required, not null
     * @param $locale Locale the locale to use, not null
     * @return string the text value of the chronology, not null
     */
    function getDisplayName(TextStyle $style, Locale $locale)
    {
        return ChronologyDefaults::getDisplayName($this, $style, $locale);
    }

//-----------------------------------------------------------------------
    /**
     * Obtains an ISO local date from the era, year-of-era, month-of-year
     * and day-of-month fields.
     *
     * @param $era Era the ISO era, not null
     * @param $yearOfEra int  the ISO year-of-era
     * @param $month int the ISO month-of-year
     * @param $dayOfMonth int the ISO day-of-month
     * @return LocalDate the ISO local date, not null
     * @throws DateTimeException if unable to create the date
     * @throws ClassCastException if the type of {@code era} is not {@code IsoEra}
     */
    public function dateEra(Era $era, $yearOfEra, $month, $dayOfMonth)
    {
        return $this->date($this->prolepticYear($era, $yearOfEra), $month, $dayOfMonth);
    }

    /**
     * Obtains an ISO local date from the proleptic-year, month-of-year
     * and day-of-month fields.
     * <p>
     * This is equivalent to {@link LocalDate#of(int, int, int)}.
     *
     * @param $prolepticYear int the ISO proleptic-year
     * @param $month int the ISO month-of-year
     * @param $dayOfMonth int the ISO day-of-month
     * @return LocalDate the ISO local date, not null
     * @throws DateTimeException if unable to create the date
     */
    public function date($prolepticYear, $month, $dayOfMonth)
    {
        return LocalDate::ofNumerical($prolepticYear, $month, $dayOfMonth);
    }

    /**
     * Obtains an ISO local date from the era, year-of-era and day-of-year fields.
     *
     * @param $era Era the ISO era, not null
     * @param $yearOfEra int the ISO year-of-era
     * @param $dayOfYear int the ISO day-of-year
     * @return LocalDate the ISO local date, not null
     * @throws DateTimeException if unable to create the date
     */
    public function dateEraYearDay(Era $era, $yearOfEra, $dayOfYear)
    {
        return $this->dateYearDay($this->prolepticYear($era, $yearOfEra), $dayOfYear);
    }

    /**
     * Obtains an ISO local date from the proleptic-year and day-of-year fields.
     * <p>
     * This is equivalent to {@link LocalDate#ofYearDay(int, int)}.
     *
     * @param $prolepticYear int the ISO proleptic-year
     * @param $dayOfYear int the ISO day-of-year
     * @return LocalDate the ISO local date, not null
     * @throws DateTimeException if unable to create the date
     */
    public function dateYearDay($prolepticYear, $dayOfYear)
    {
        return LocalDate::ofYearDay($prolepticYear, $dayOfYear);
    }

    /**
     * Obtains an ISO local date from the epoch-day.
     * <p>
     * This is equivalent to {@link LocalDate#ofEpochDay(long)}.
     *
     * @param $epochDay int the epoch day
     * @return LocalDate the ISO local date, not null
     * @throws DateTimeException if unable to create the date
     */
    public function dateEpochDay($epochDay)
    {
        return LocalDate::ofEpochDay($epochDay);
    }

//-----------------------------------------------------------------------
    /**
     * Obtains an ISO local date from another date-time object.
     * <p>
     * This is equivalent to {@link LocalDate#from(TemporalAccessor)}.
     *
     * @param $temporal TemporalAccessor the date-time object to convert, not null
     * @return LocalDate the ISO local date, not null
     * @throws DateTimeException if unable to create the date
     */
    public function dateFrom(TemporalAccessor $temporal)
    {
        return LocalDate::from($temporal);
    }

    /**
     * Obtains an ISO local date-time from another date-time object.
     * <p>
     * This is equivalent to {@link LocalDateTime#from(TemporalAccessor)}.
     *
     * @param $temporal TemporalAccessor the date-time object to convert, not null
     * @return LocalDateTime the ISO local date-time, not null
     * @throws DateTimeException if unable to create the date-time
     */
    public function localDateTime(TemporalAccessor $temporal)
    {
        return LocalDateTime::from($temporal);
    }

    /**
     * Obtains an ISO zoned date-time from another date-time object.
     * <p>
     * This is equivalent to {@link ZonedDateTime#from(TemporalAccessor)}.
     *
     * @param $temporal TemporalAccessor the date-time object to convert, not null
     * @return ZonedDateTime the ISO zoned date-time, not null
     * @throws DateTimeException if unable to create the date-time
     */
    public function zonedDateTimeFrom(TemporalAccessor $temporal)
    {
        return ZonedDateTime::from($temporal);
    }

    /**
     * Obtains an ISO zoned date-time in this chronology from an {@code Instant}.
     * <p>
     * This is equivalent to {@link ZonedDateTime#ofInstant(Instant, ZoneId)}.
     *
     * @param $instant Instant the instant to create the date-time from, not null
     * @param $zone ZoneId the time-zone, not null
     * @return ZonedDateTime the zoned date-time, not null
     * @throws DateTimeException if the result exceeds the supported range
     */
    public function zonedDateTime(Instant $instant, ZoneId $zone)
    {
        return ZonedDateTime::ofInstant($instant, $zone);
    }

//-----------------------------------------------------------------------
    /**
     * Obtains the current ISO local date from the system clock in the default time-zone.
     * <p>
     * This will query the {@link Clock#systemDefaultZone() system clock} in the default
     * time-zone to obtain the current date.
     * <p>
     * Using this method will prevent the ability to use an alternate clock for testing
     * because the clock is hard-coded.
     *
     * @return LocalDate the current ISO local date using the system clock and default time-zone, not null
     * @throws DateTimeException if unable to create the date
     */
    public function dateNow()
    {
        return $this->dateNowOf(Clock::systemDefaultZone());
    }

    /**
     * Obtains the current ISO local date from the system clock in the specified time-zone.
     * <p>
     * This will query the {@link Clock#system(ZoneId) system clock} to obtain the current date.
     * Specifying the time-zone avoids dependence on the default time-zone.
     * <p>
     * Using this method will prevent the ability to use an alternate clock for testing
     * because the clock is hard-coded.
     *
     * @return LocalDate the current ISO local date using the system clock, not null
     * @throws DateTimeException if unable to create the date
     */
    public function dateNowIn(ZoneId $zone)
    {
        return $this->dateNowOf(Clock::system($zone));
    }

    /**
     * Obtains the current ISO local date from the specified clock.
     * <p>
     * This will query the specified clock to obtain the current date - today.
     * Using this method allows the use of an alternate clock for testing.
     * The alternate clock may be introduced using {@link Clock dependency injection}.
     *
     * @param $clock Clock the clock to use, not null
     * @return LocalDate the current ISO local date, not null
     * @throws DateTimeException if unable to create the date
     */
    public function dateNowOf(Clock $clock)
    {
        return $this->dateFrom(LocalDate::nowOf($clock));
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
     * @param $prolepticYear int the ISO proleptic year to check
     * @return true if the year is leap, false otherwise
     */
    public function isLeapYear($prolepticYear)
    {
        return (($prolepticYear & 3) == 0) && (($prolepticYear % 100) != 0 || ($prolepticYear % 400) == 0);
    }

    public function prolepticYear(Era $era, $yearOfEra)
    {
        if ($era instanceof IsoEra == false) {
            throw new ClassCastException("Era must be IsoEra");
        }

        return ($era == IsoEra::CE() ? $yearOfEra : 1 - $yearOfEra);
    }

    public function eraOf($eraValue)
    {
        return IsoEra::of($eraValue);
    }

    public function eras()
    {
        return [IsoEra::BCE(), IsoEra::CE()];
    }

//-----------------------------------------------------------------------
    /**
     * Resolves parsed {@code ChronoField} values into a date during parsing.
     * <p>
     * Most {@code TemporalField} implementations are resolved using the
     * resolve method on the field. By contrast, the {@code ChronoField} class
     * defines fields that only have meaning relative to the chronology.
     * As such, {@code ChronoField} date fields are resolved here in the
     * context of a specific chronology.
     * <p>
     * {@code ChronoField} instances on the ISO calendar system are resolved
     * as follows.
     * <ul>
     * <li>{@code EPOCH_DAY} - If present, this is converted to a {@code LocalDate}
     *  and all other date fields are then cross-checked against the date.
     * <li>{@code PROLEPTIC_MONTH} - If present, then it is split into the
     *  {@code YEAR} and {@code MONTH_OF_YEAR}. If the mode is strict or smart
     *  then the field is validated.
     * <li>{@code YEAR_OF_ERA} and {@code ERA} - If both are present, then they
     *  are combined to form a {@code YEAR}. In lenient mode, the {@code YEAR_OF_ERA}
     *  range is not validated, in smart and strict mode it is. The {@code ERA} is
     *  validated for range in all three modes. If only the {@code YEAR_OF_ERA} is
     *  present, and the mode is smart or lenient, then the current era (CE/AD)
     *  is assumed. In strict mode, no era is assumed and the {@code YEAR_OF_ERA} is
     *  left untouched. If only the {@code ERA} is present, then it is left untouched.
     * <li>{@code YEAR}, {@code MONTH_OF_YEAR} and {@code DAY_OF_MONTH} -
     *  If all three are present, then they are combined to form a {@code LocalDate}.
     *  In all three modes, the {@code YEAR} is validated. If the mode is smart or strict,
     *  then the month and day are validated, with the day validated from 1 to 31.
     *  If the mode is lenient, then the date is combined in a manner equivalent to
     *  creating a date on the first of January in the requested year, then adding
     *  the difference in months, then the difference in days.
     *  If the mode is smart, and the day-of-month is greater than the maximum for
     *  the year-month, then the day-of-month is adjusted to the last day-of-month.
     *  If the mode is strict, then the three fields must form a valid date.
     * <li>{@code YEAR} and {@code DAY_OF_YEAR} -
     *  If both are present, then they are combined to form a {@code LocalDate}.
     *  In all three modes, the {@code YEAR} is validated.
     *  If the mode is lenient, then the date is combined in a manner equivalent to
     *  creating a date on the first of January in the requested year, then adding
     *  the difference in days.
     *  If the mode is smart or strict, then the two fields must form a valid date.
     * <li>{@code YEAR}, {@code MONTH_OF_YEAR}, {@code ALIGNED_WEEK_OF_MONTH} and
     *  {@code ALIGNED_DAY_OF_WEEK_IN_MONTH} -
     *  If all four are present, then they are combined to form a {@code LocalDate}.
     *  In all three modes, the {@code YEAR} is validated.
     *  If the mode is lenient, then the date is combined in a manner equivalent to
     *  creating a date on the first of January in the requested year, then adding
     *  the difference in months, then the difference in weeks, then in days.
     *  If the mode is smart or strict, then the all four fields are validated to
     *  their outer ranges. The date is then combined in a manner equivalent to
     *  creating a date on the first day of the requested year and month, then adding
     *  the amount in weeks and days to reach their values. If the mode is strict,
     *  the date is additionally validated to check that the day and week adjustment
     *  did not change the month.
     * <li>{@code YEAR}, {@code MONTH_OF_YEAR}, {@code ALIGNED_WEEK_OF_MONTH} and
     *  {@code DAY_OF_WEEK} - If all four are present, then they are combined to
     *  form a {@code LocalDate}. The approach is the same as described above for
     *  years, months and weeks in {@code ALIGNED_DAY_OF_WEEK_IN_MONTH}.
     *  The day-of-week is adjusted as the next or same matching day-of-week once
     *  the years, months and weeks have been handled.
     * <li>{@code YEAR}, {@code ALIGNED_WEEK_OF_YEAR} and {@code ALIGNED_DAY_OF_WEEK_IN_YEAR} -
     *  If all three are present, then they are combined to form a {@code LocalDate}.
     *  In all three modes, the {@code YEAR} is validated.
     *  If the mode is lenient, then the date is combined in a manner equivalent to
     *  creating a date on the first of January in the requested year, then adding
     *  the difference in weeks, then in days.
     *  If the mode is smart or strict, then the all three fields are validated to
     *  their outer ranges. The date is then combined in a manner equivalent to
     *  creating a date on the first day of the requested year, then adding
     *  the amount in weeks and days to reach their values. If the mode is strict,
     *  the date is additionally validated to check that the day and week adjustment
     *  did not change the year.
     * <li>{@code YEAR}, {@code ALIGNED_WEEK_OF_YEAR} and {@code DAY_OF_WEEK} -
     *  If all three are present, then they are combined to form a {@code LocalDate}.
     *  The approach is the same as described above for years and weeks in
     *  {@code ALIGNED_DAY_OF_WEEK_IN_YEAR}. The day-of-week is adjusted as the
     *  next or same matching day-of-week once the years and weeks have been handled.
     * </ul>
     *
     * @param $fieldValues array the map of fields to values, which can be updated, not null
     * @param $resolverStyle ResolverStyle the requested type of resolve, not null
     * @return LocalDate the resolved date, null if insufficient information to create a date
     * @throws DateTimeException if the date cannot be resolved, typically
     *  because of a conflict in the input data
     */
    public function resolveDate($fieldValues, ResolverStyle $resolverStyle)
    {
        return parent::resolveDate($fieldValues, $resolverStyle);
    }

// override for better proleptic algorithm
    protected function resolveProlepticMonth($fieldValues, ResolverStyle $resolverStyle)
    {
        $pMonth = self::remove($fieldValues, ChronoField::PROLEPTIC_MONTH());
        if ($pMonth != null) {
            if ($resolverStyle != ResolverStyle::LENIENT()) {
                ChronoField::PROLEPTIC_MONTH()->checkValidValue($pMonth);
            }

            $this->addFieldValue($fieldValues, ChronoField::MONTH_OF_YEAR(), Math::floorMod($pMonth, 12) + 1);
            $this->addFieldValue($fieldValues, ChronoField::YEAR(), Math::floorDiv($pMonth, 12));
        }
    }

// override for enhanced behaviour
    protected function resolveYearOfEra($fieldValues, ResolverStyle $resolverStyle)
    {
        $yoeLong = self::remove($fieldValues, ChronoField::YEAR_OF_ERA());
        if ($yoeLong != null) {
            if ($resolverStyle != ResolverStyle::LENIENT()) {
                ChronoField::YEAR_OF_ERA()->checkValidValue($yoeLong);
            }

            $era = self::remove($fieldValues, ChronoField::ERA());
            if ($era == null) {
                $year = $fieldValues->get(ChronoField::YEAR());
                if ($resolverStyle == ResolverStyle::STRICT()) {
                    // do not invent era if strict, but do cross-check with year
                    if ($year != null) {
                        $this->addFieldValue($fieldValues, ChronoField::YEAR(), ($year > 0 ? $yoeLong : Math::subtractExact(1, $yoeLong)));
                    } else {
                        // reinstate the field removed earlier, no cross-check issues
                        $fieldValues->put(ChronoField::YEAR_OF_ERA(), $yoeLong);
                    }
                } else {
                    // invent era
                    $this->addFieldValue($fieldValues, ChronoField::YEAR(), ($year == null || $year > 0 ? $yoeLong : Math::subtractExact(1, $yoeLong)));
                }
            } else if ($era->longValue() == 1) {
                $this->addFieldValue($fieldValues, ChronoField::YEAR(), $yoeLong);
            } else if ($era->longValue() == 0) {
                $this->addFieldValue($fieldValues, ChronoField::YEAR(), Math::subtractExact(1, $yoeLong));
            } else {
                throw new DateTimeException("Invalid value for era: " . $era);
            }
        } else if ($fieldValues->containsKey(ChronoField::ERA())) {
            ChronoField::ERA()->checkValidValue($fieldValues->get(ChronoField::ERA()));  // always validated
        }
        return null;
    }

    // override for performance
    function resolveYMD($fieldValues, ResolverStyle $resolverStyle)
    {
        $y = ChronoField::YEAR()->checkValidIntValue(self::remove($fieldValues, ChronoField::YEAR()));
        if ($resolverStyle == ResolverStyle::LENIENT()) {
            $months = Math::subtractExact(self::remove($fieldValues, ChronoField::MONTH_OF_YEAR()), 1);
            $days = Math::subtractExact(self::remove($fieldValues, ChronoField::DAY_OF_MONTH()), 1);
            return LocalDate::ofNumerical($y, 1, 1)->plusMonths($months)->plusDays($days);
        }

        $moy = ChronoField::MONTH_OF_YEAR()->checkValidIntValue(self::remove($fieldValues, ChronoField::MONTH_OF_YEAR()));
        $dom = ChronoField::DAY_OF_MONTH()->checkValidIntValue(self::remove($fieldValues, ChronoField::DAY_OF_MONTH()));
        if ($resolverStyle == ResolverStyle::SMART()) {  // previous valid
            if ($moy == 4 || $moy == 6 || $moy == 9 || $moy == 11) {
                $dom = Math::min($dom, 30);
            } else if ($moy == 2) {
                $dom = Math::min($dom, Month::FEBRUARY()->length(Year::isLeapYear($y)));

            }
        }
        return LocalDate::ofNumerical($y, $moy, $dom);
    }

    //-----------------------------------------------------------------------
    public function range(ChronoField $field)
    {
        return $field->range();
    }

    //-----------------------------------------------------------------------
    /**
     * Obtains a period for this chronology based on years, months and days.
     * <p>
     * This returns a period tied to the ISO chronology using the specified
     * years, months and days. See {@link Period} for further details.
     *
     * @param $years int the number of years, may be negative
     * @param $months int  the number of years, may be negative
     * @param $days int the number of years, may be negative
     * @return Period the ISO period, not null
     */
    // override with covariant return type
    public function period($years, $months, $days)
    {
        return Period::of($years, $months, $days);
    }

    /**
     * Obtains an instance of {@code Chronology} from a temporal object.
     * <p>
     * This obtains a chronology based on the specified temporal.
     * A {@code TemporalAccessor} represents an arbitrary set of date and time information,
     * which this factory converts to an instance of {@code Chronology}.
     * <p>
     * The conversion will obtain the chronology using {@link TemporalQueries#chronology()}.
     * If the specified temporal object does not have a chronology, {@link IsoChronology} is returned.
     * <p>
     * This method matches the signature of the functional interface {@link TemporalQuery}
     * allowing it to be used as a query via method reference, {@code Chronology::from}.
     *
     * @param $temporal TemporalAccessor the temporal to convert, not null
     * @return Chronology the chronology, not null
     * @throws DateTimeException if unable to convert to an {@code Chronology}
     */
    static function from(TemporalAccessor $temporal)
    {
        // TODO: Implement from() method.
    }
}
