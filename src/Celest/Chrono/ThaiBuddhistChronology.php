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

namespace Celest\Chrono;

use Celest\Clock;
use Celest\DateTimeException;
use Celest\Format\ResolverStyle;
use Celest\LocalDate;
use Celest\Temporal\ChronoField;
use Celest\Temporal\ChronoField as CF;
use Celest\Temporal\FieldValues;
use Celest\Temporal\TemporalAccessor;
use Celest\Temporal\ValueRange;
use Celest\ZoneId;

/**
 * The Thai Buddhist calendar system.
 * <p>
 * This chronology defines the rules of the Thai Buddhist calendar system.
 * This calendar system is primarily used in Thailand.
 * Dates are aligned such that {@code 2484-01-01 (Buddhist)} is {@code 1941-01-01 (ISO)}.
 * <p>
 * The fields are defined as follows:
 * <ul>
 * <li>era - There are two eras, the current 'Buddhist' (ERA_BE) and the previous era (ERA_BEFORE_BE).
 * <li>year-of-era - The year-of-era for the current era increases uniformly from the epoch at year one.
 *  For the previous era the year increases from one as time goes backwards.
 *  The value for the current era is equal to the ISO proleptic-year plus 543.
 * <li>proleptic-year - The proleptic year is the same as the year-of-era for the
 *  current era. For the previous era, years have zero, then negative values.
 *  The value is equal to the ISO proleptic-year plus 543.
 * <li>month-of-year - The ThaiBuddhist month-of-year exactly matches ISO.
 * <li>day-of-month - The ThaiBuddhist day-of-month exactly matches ISO.
 * <li>day-of-year - The ThaiBuddhist day-of-year exactly matches ISO.
 * <li>leap-year - The ThaiBuddhist leap-year pattern exactly matches ISO, such that the two calendars
 *  are never out of step.
 * </ul>
 *
 * @implSpec
 * This class is immutable and thread-safe.
 *
 * @since 1.8
 */
final class ThaiBuddhistChronology extends AbstractChronology
{

    /**
     * Singleton instance of the Buddhist chronology.
     * @return ThaiBuddhistChronology
     */
    public static function INSTANCE()
    {
        if (self::$INSTANCE === null) {
            self::$INSTANCE = new ThaiBuddhistChronology();
        }
        return self::$INSTANCE;
    }

    /** @var ThaiBuddhistChronology */
    private static $INSTANCE;

    /**
     * Containing the offset to add to the ISO year.
     */
    const YEARS_DIFFERENCE = 543;
    /**
     * Narrow names for eras.
     */
//private static final HashMap<String, String[]> ERA_NARROW_NAMES = new HashMap<>();
    /**
     * Short names for eras.
     */
//private static final HashMap<String, String[]> ERA_SHORT_NAMES = new HashMap<>();
    /**
     * Full names for eras.
     */
//private static final HashMap<String, String[]> ERA_FULL_NAMES = new HashMap<>();
    /**
     * Fallback language for the era names.
     */
//private static final String FALLBACK_LANGUAGE = "en";
    /**
     * Language that has the era names.
     */
//private static final String TARGET_LANGUAGE = "th";
    /**
     * Name data.
     */
    /*static {
    ERA_NARROW_NAMES.put(FALLBACK_LANGUAGE, new String[]{"BB", "BE"});
            ERA_NARROW_NAMES.put(TARGET_LANGUAGE, new String[]{"BB", "BE"});
            ERA_SHORT_NAMES.put(FALLBACK_LANGUAGE, new String[]{"B.B.", "B.E."});
            ERA_SHORT_NAMES.put(TARGET_LANGUAGE,
                new String[]{"\u0e1e.\u0e28.",
                    "\u0e1b\u0e35\u0e01\u0e48\u0e2d\u0e19\u0e04\u0e23\u0e34\u0e2a\u0e15\u0e4c\u0e01\u0e32\u0e25\u0e17\u0e35\u0e48"});
            ERA_FULL_NAMES.put(FALLBACK_LANGUAGE, new String[]{"Before Buddhist", "Budhhist Era"});
            ERA_FULL_NAMES.put(TARGET_LANGUAGE,
                new String[]{"\u0e1e\u0e38\u0e17\u0e18\u0e28\u0e31\u0e01\u0e23\u0e32\u0e0a",
                    "\u0e1b\u0e35\u0e01\u0e48\u0e2d\u0e19\u0e04\u0e23\u0e34\u0e2a\u0e15\u0e4c\u0e01\u0e32\u0e25\u0e17\u0e35\u0e48"});
        }*/

    /**
     * Restricted constructor.
     */
    protected function __construct()
    {
    }

    //-----------------------------------------------------------------------
    /**
     * Gets the ID of the chronology - 'ThaiBuddhist'.
     * <p>
     * The ID uniquely identifies the {@code Chronology}.
     * It can be used to lookup the {@code Chronology} using {@link Chronology#of(String)}.
     *
     * @return string the chronology ID - 'ThaiBuddhist'
     * @see #getCalendarType()
     */
    public function getId()
    {
        return "ThaiBuddhist";
    }

    /**
     * Gets the calendar type of the underlying calendar system - 'buddhist'.
     * <p>
     * The calendar type is an identifier defined by the
     * <em>Unicode Locale Data Markup Language (LDML)</em> specification.
     * It can be used to lookup the {@code Chronology} using {@link Chronology#of(String)}.
     * It can also be used as part of a locale, accessible via
     * {@link Locale#getUnicodeLocaleType(String)} with the key 'ca'.
     *
     * @return string the calendar system type - 'buddhist'
     * @see #getId()
     */
    public function getCalendarType()
    {
        return "buddhist";
    }

    //-----------------------------------------------------------------------
    /**
     * Obtains a local date in Thai Buddhist calendar system from the
     * era, year-of-era, month-of-year and day-of-month fields.
     *
     * @param Era $era the Thai Buddhist era, not null
     * @param int $yearOfEra the year-of-era
     * @param int $month the month-of-year
     * @param int $dayOfMonth the day-of-month
     * @return ThaiBuddhistDate the Thai Buddhist local date, not null
     * @throws DateTimeException if unable to create the date
     * @throws ClassCastException if the {@code era} is not a {@code ThaiBuddhistEra}
     */
    public function dateEra(Era $era, $yearOfEra, $month, $dayOfMonth)
    {
        return $this->date($this->prolepticYear($era, $yearOfEra), $month, $dayOfMonth);
    }

    /**
     * Obtains a local date in Thai Buddhist calendar system from the
     * proleptic-year, month-of-year and day-of-month fields.
     *
     * @param int $prolepticYear the proleptic-year
     * @param int $month the month-of-year
     * @param int $dayOfMonth the day-of-month
     * @return ThaiBuddhistDate the Thai Buddhist local date, not null
     * @throws DateTimeException if unable to create the date
     */
    public function date($prolepticYear, $month, $dayOfMonth)
    {
        return ThaiBuddhistDate::ofIsoDate(LocalDate::of($prolepticYear - self::YEARS_DIFFERENCE, $month, $dayOfMonth));
    }

    /**
     * Obtains a local date in Thai Buddhist calendar system from the
     * era, year-of-era and day-of-year fields.
     *
     * @param Era $era the Thai Buddhist era, not null
     * @param int $yearOfEra the year-of-era
     * @param int $dayOfYear the day-of-year
     * @return ThaiBuddhistDate the Thai Buddhist local date, not null
     * @throws DateTimeException if unable to create the date
     * @throws ClassCastException if the {@code era} is not a {@code ThaiBuddhistEra}
     */
    public function dateEraYearDay(Era $era, $yearOfEra, $dayOfYear)
    {
        return $this->dateYearDay($this->prolepticYear($era, $yearOfEra), $dayOfYear);
    }

    /**
     * Obtains a local date in Thai Buddhist calendar system from the
     * proleptic-year and day-of-year fields.
     *
     * @param int $prolepticYear the proleptic-year
     * @param int $dayOfYear the day-of-year
     * @return ThaiBuddhistDate the Thai Buddhist local date, not null
     * @throws DateTimeException if unable to create the date
     */
    public function dateYearDay($prolepticYear, $dayOfYear)
    {
        return ThaiBuddhistDate::ofIsoDate(LocalDate::ofYearDay($prolepticYear - self::YEARS_DIFFERENCE, $dayOfYear));
    }

    /**
     * Obtains a local date in the Thai Buddhist calendar system from the epoch-day.
     *
     * @param int $epochDay the epoch day
     * @return ThaiBuddhistDate the Thai Buddhist local date, not null
     * @throws DateTimeException if unable to create the date
     */
    public function dateEpochDay($epochDay)
    {
        return ThaiBuddhistDate::ofIsoDate(LocalDate::ofEpochDay($epochDay));
    }

    public function dateNow()
    {
        return $this->dateNowOf(Clock::systemDefaultZone());
    }

    public function dateNowIn(ZoneId $zone)
    {
        return $this->dateNowOf(Clock::system($zone));
    }

    public function dateNowOf(Clock $clock)
    {
        return $this->dateFrom(LocalDate::nowOf($clock));
    }

    public function dateFrom(TemporalAccessor $temporal)
    {
        if ($temporal instanceof ThaiBuddhistDate) {
            return $temporal;
        }
        return ThaiBuddhistDate::ofIsoDate(LocalDate::from($temporal));
    }

    //-----------------------------------------------------------------------
    /**
     * Checks if the specified year is a leap year.
     * <p>
     * Thai Buddhist leap years occur exactly in line with ISO leap years.
     * This method does not validate the year passed in, and only has a
     * well-defined result for years in the supported range.
     *
     * @param int $prolepticYear the proleptic-year to check, not validated for range
     * @return true if the year is a leap year
     */
    public function isLeapYear($prolepticYear)
    {
        return IsoChronology::INSTANCE()->isLeapYear($prolepticYear - self::YEARS_DIFFERENCE);
    }

    public function prolepticYear(Era $era, $yearOfEra)
    {
        if ($era instanceof ThaiBuddhistEra === false) {
            throw new ClassCastException("Era must be BuddhistEra");
        }
        return ($era == ThaiBuddhistEra::BE() ? $yearOfEra : 1 - $yearOfEra);
    }

    public function eraOf($eraValue)
    {
        return ThaiBuddhistEra::of($eraValue);
    }

    public function eras()
    {
        return ThaiBuddhistEra::values();
    }

    //-----------------------------------------------------------------------
    public function range(ChronoField $field)
    {
        switch ($field) {
            case CF::PROLEPTIC_MONTH(): {
                $range = CF::PROLEPTIC_MONTH()->range();
                return ValueRange::of($range->getMinimum() + self::YEARS_DIFFERENCE * 12, $range->getMaximum() + self::YEARS_DIFFERENCE * 12);
            }
            case CF::YEAR_OF_ERA(): {
                $range = CF::YEAR()->range();
                return ValueRange::ofVariable(1, -($range->getMinimum() + self::YEARS_DIFFERENCE) + 1, $range->getMaximum() + self::YEARS_DIFFERENCE);
            }
            case CF::YEAR(): {
                $range = CF::YEAR()->range();
                return ValueRange::of($range->getMinimum() + self::YEARS_DIFFERENCE, $range->getMaximum() + self::YEARS_DIFFERENCE);
            }
        }
        return $field->range();
    }

    //-----------------------------------------------------------------------
    /**
     * @return ThaiBuddhistDate
     */
    public function resolveDate(FieldValues $fieldValues, ResolverStyle $resolverStyle)
    {
        return parent::resolveDate($fieldValues, $resolverStyle);
    }

}
