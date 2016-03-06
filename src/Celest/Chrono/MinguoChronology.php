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
use Celest\LocalDate;
use Celest\Temporal\ChronoField;
use Celest\Temporal\TemporalAccessor;
use Celest\Temporal\ValueRange;
use Celest\ZoneId;

/**
 * The Minguo calendar system.
 * <p>
 * This chronology defines the rules of the Minguo calendar system.
 * This calendar system is primarily used in the Republic of China, often known as Taiwan.
 * Dates are aligned such that {@code 0001-01-01 (Minguo)} is {@code 1912-01-01 (ISO)}.
 * <p>
 * The fields are defined as follows:
 * <ul>
 * <li>era - There are two eras, the current 'Republic' (ERA_ROC) and the previous era (ERA_BEFORE_ROC).
 * <li>year-of-era - The year-of-era for the current era increases uniformly from the epoch at year one.
 *  For the previous era the year increases from one as time goes backwards.
 *  The value for the current era is equal to the ISO proleptic-year minus 1911.
 * <li>proleptic-year - The proleptic year is the same as the year-of-era for the
 *  current era. For the previous era, years have zero, then negative values.
 *  The value is equal to the ISO proleptic-year minus 1911.
 * <li>month-of-year - The Minguo month-of-year exactly matches ISO.
 * <li>day-of-month - The Minguo day-of-month exactly matches ISO.
 * <li>day-of-year - The Minguo day-of-year exactly matches ISO.
 * <li>leap-year - The Minguo leap-year pattern exactly matches ISO, such that the two calendars
 *  are never out of step.
 * </ul>
 *
 * @implSpec
 * This class is immutable and thread-safe.
 *
 * @since 1.8
 */
final class MinguoChronology extends AbstractChronology {

    /**
     * Singleton instance for the Minguo chronology.
     */
    public static function INSTANCE() {
      return new MinguoChronology();
    }

    /**
     * The difference in years between ISO and Minguo.
     */
    const YEARS_DIFFERENCE = 1911;

    /**
     * Restricted constructor.
     */
    protected function __construct() {
    }

    //-----------------------------------------------------------------------
    /**
     * Gets the ID of the chronology - 'Minguo'.
     * <p>
     * The ID uniquely identifies the {@code Chronology}.
     * It can be used to lookup the {@code Chronology} using {@link Chronology#of(String)}.
     *
     * @return string the chronology ID - 'Minguo'
     * @see #getCalendarType()
     */
    public function getId() {
        return "Minguo";
    }

    /**
     * Gets the calendar type of the underlying calendar system - 'roc'.
     * <p>
     * The calendar type is an identifier defined by the
     * <em>Unicode Locale Data Markup Language (LDML)</em> specification.
     * It can be used to lookup the {@code Chronology} using {@link Chronology#of(String)}.
     * It can also be used as part of a locale, accessible via
     * {@link Locale#getUnicodeLocaleType(String)} with the key 'ca'.
     *
     * @return string the calendar system type - 'roc'
     * @see #getId()
     */
    public function getCalendarType() {
        return "roc";
    }

    //-----------------------------------------------------------------------
    /**
     * Obtains a local date in Minguo calendar system from the
     * era, year-of-era, month-of-year and day-of-month fields.
     *
     * @param Era $era  the Minguo era, not null
     * @param int $yearOfEra  the year-of-era
     * @param int $month  the month-of-year
     * @param int $dayOfMonth  the day-of-month
     * @return MinguoDate the Minguo local date, not null
     * @throws DateTimeException if unable to create the date
     * @throws ClassCastException if the {@code era} is not a {@code MinguoEra}
     */
    public function dateEra(Era $era, $yearOfEra, $month, $dayOfMonth) {
    return $this->date($this->prolepticYear($era, $yearOfEra), $month, $dayOfMonth);
}

    /**
     * Obtains a local date in Minguo calendar system from the
     * proleptic-year, month-of-year and day-of-month fields.
     *
     * @param int $prolepticYear  the proleptic-year
     * @param int $month  the month-of-year
     * @param int $dayOfMonth  the day-of-month
     * @return MinguoDate the Minguo local date, not null
     * @throws DateTimeException if unable to create the date
     */
    public function date($prolepticYear, $month, $dayOfMonth) {
    return MinguoDate::ofIsoDate(LocalDate::of($prolepticYear + self::YEARS_DIFFERENCE, $month, $dayOfMonth));
}

    /**
     * Obtains a local date in Minguo calendar system from the
     * era, year-of-era and day-of-year fields.
     *
     * @param Era $era  the Minguo era, not null
     * @param int $yearOfEra  the year-of-era
     * @param int $dayOfYear  the day-of-year
     * @return MinguoDate the Minguo local date, not null
     * @throws DateTimeException if unable to create the date
     * @throws ClassCastException if the {@code era} is not a {@code MinguoEra}
     */
    public function dateEraYearDay(Era $era, $yearOfEra, $dayOfYear) {
    return $this->dateYearDay($this->prolepticYear($era, $yearOfEra), $dayOfYear);
}

    /**
     * Obtains a local date in Minguo calendar system from the
     * proleptic-year and day-of-year fields.
     *
     * @param int $prolepticYear  the proleptic-year
     * @param int $dayOfYear  the day-of-year
     * @return MinguoDate the Minguo local date, not null
     * @throws DateTimeException if unable to create the date
     */
    public function dateYearDay($prolepticYear, $dayOfYear) {
    return MinguoDate::ofIsoDate(LocalDate::ofYearDay($prolepticYear + self::YEARS_DIFFERENCE, $dayOfYear));
}

    /**
     * Obtains a local date in the Minguo calendar system from the epoch-day.
     *
     * @param int $epochDay  the epoch day
     * @return MinguoDate the Minguo local date, not null
     * @throws DateTimeException if unable to create the date
     */
    public function dateEpochDay($epochDay) {
    return MinguoDate::ofIsoDate(LocalDate::ofEpochDay($epochDay));
}

    public function dateNow() {
        return $this->dateNowOf(Clock::systemDefaultZone());
    }

    public function dateNowIn(ZoneId $zone) {
    return $this->dateNowOf(Clock::system($zone));
}

    public function dateNowOf(Clock $clock) {
    return $this->dateFrom(LocalDate::nowof($clock));
}

    public function dateFrom(TemporalAccessor $temporal) {
    if ($temporal instanceof MinguoDate) {
        return $temporal;
        }
    return MinguoDate::ofIsoDate(LocalDate::from($temporal));
}

    //-----------------------------------------------------------------------
    /**
     * Checks if the specified year is a leap year.
     * <p>
     * Minguo leap years occur exactly in line with ISO leap years.
     * This method does not validate the year passed in, and only has a
     * well-defined result for years in the supported range.
     *
     * @param int $prolepticYear  the proleptic-year to check, not validated for range
     * @return true if the year is a leap year
     */
    public function isLeapYear($prolepticYear) {
    return IsoChronology::INSTANCE()->isLeapYear($prolepticYear + self::YEARS_DIFFERENCE);
}

    public function prolepticYear(Era $era, $yearOfEra) {
    if ($era instanceof MinguoEra === false) {
        throw new ClassCastException("Era must be MinguoEra");
    }
    return ($era == MinguoEra::ROC() ? $yearOfEra : 1 - $yearOfEra);
}

    public function eraOf($eraValue) {
    return MinguoEra::of($eraValue);
}

    public function eras() {
        return MinguoEra::values();
    }

    //-----------------------------------------------------------------------
    public function range(ChronoField $field) {
    switch ($field) {
        case ChronoField::PROLEPTIC_MONTH(): {
            $range = ChronoField::PROLEPTIC_MONTH()->range();
                return ValueRange::of($range->getMinimum() - self::YEARS_DIFFERENCE * 12, $range->getMaximum() - self::YEARS_DIFFERENCE * 12);
            }
        case ChronoField::YEAR_OF_ERA(): {
            $range = ChronoField::YEAR()->range();
                return ValueRange::ofVariable(1, $range->getMaximum() - self::YEARS_DIFFERENCE, -$range->getMinimum() + 1 + self::YEARS_DIFFERENCE);
            }
        case ChronoField::YEAR(): {
            $range = ChronoField::YEAR()->range();
                return ValueRange::of($range->getMinimum() - self::YEARS_DIFFERENCE, $range->getMaximum() - self::YEARS_DIFFERENCE);
            }
    }
    return $field->range();
}
}
