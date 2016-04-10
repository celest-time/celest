<?php
/*
 * Copyright (c) 2012, 2013, Oracle and/or its affiliates. All rights reserved.
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
use Celest\LocalTime;
use Celest\Temporal\ChronoField;
use Celest\Temporal\ChronoField as CF;
use Celest\Temporal\TemporalAccessor;
use Celest\Temporal\TemporalField;
use Celest\Temporal\UnsupportedTemporalTypeException;
use Celest\Temporal\ValueRange;
use Celest\ZoneId;

/**
 * A date in the Thai Buddhist calendar system.
 * <p>
 * This date operates using the {@linkplain ThaiBuddhistChronology Thai Buddhist calendar}.
 * This calendar system is primarily used in Thailand.
 * Dates are aligned such that {@code 2484-01-01 (Buddhist)} is {@code 1941-01-01 (ISO)}.
 *
 * <p>
 * This is a <a href="{@docRoot}/java/lang/doc-files/ValueBased.html">value-based</a>
 * class; use of identity-sensitive operations (including reference equality
 * ({@code ==}), identity hash code, or synchronization) on instances of
 * {@code ThaiBuddhistDate} may have unpredictable results and should be avoided.
 * The {@code equals} method should be used for comparisons.
 *
 * @implSpec
 * This class is immutable and thread-safe.
 *
 * @since 1.8
 */
final class ThaiBuddhistDate extends ChronoLocalDateImpl implements ChronoLocalDate
{
    /**
     * The underlying date.
     * @var LocalDate
     */
    private $isoDate;

    //-----------------------------------------------------------------------
    /**
     * Obtains the current {@code ThaiBuddhistDate} from the system clock in the default time-zone.
     * <p>
     * This will query the {@link Clock#systemDefaultZone() system clock} in the default
     * time-zone to obtain the current date.
     * <p>
     * Using this method will prevent the ability to use an alternate clock for testing
     * because the clock is hard-coded.
     *
     * @return ThaiBuddhistDate the current date using the system clock and default time-zone, not null
     */
    public static function now()
    {
        return self::nowOf(Clock::systemDefaultZone());
    }

    /**
     * Obtains the current {@code ThaiBuddhistDate} from the system clock in the specified time-zone.
     * <p>
     * This will query the {@link Clock#system(ZoneId) system clock} to obtain the current date.
     * Specifying the time-zone avoids dependence on the default time-zone.
     * <p>
     * Using this method will prevent the ability to use an alternate clock for testing
     * because the clock is hard-coded.
     *
     * @param ZoneId $zone the zone ID to use, not null
     * @return ThaiBuddhistDate the current date using the system clock, not null
     */
    public static function nowFrom(ZoneId $zone)
    {
        return self::nowOf(Clock::system($zone));
    }

    /**
     * Obtains the current {@code ThaiBuddhistDate} from the specified clock.
     * <p>
     * This will query the specified clock to obtain the current date - today.
     * Using this method allows the use of an alternate clock for testing.
     * The alternate clock may be introduced using {@linkplain Clock dependency injection}.
     *
     * @param Clock $clock the clock to use, not null
     * @return ThaiBuddhistDate the current date, not null
     * @throws DateTimeException if the current date cannot be obtained
     */
    public static function nowOf(Clock $clock)
    {
        return new ThaiBuddhistDate(LocalDate::nowOf($clock));
    }

    /**
     * Obtains a {@code ThaiBuddhistDate} representing a date in the Thai Buddhist calendar
     * system from the proleptic-year, month-of-year and day-of-month fields.
     * <p>
     * This returns a {@code ThaiBuddhistDate} with the specified fields.
     * The day must be valid for the year and month, otherwise an exception will be thrown.
     *
     * @param int $prolepticYear the Thai Buddhist proleptic-year
     * @param int $month the Thai Buddhist month-of-year, from 1 to 12
     * @param int $dayOfMonth the Thai Buddhist day-of-month, from 1 to 31
     * @return ThaiBuddhistDate the date in Thai Buddhist calendar system, not null
     * @throws DateTimeException if the value of any field is out of range,
     *  or if the day-of-month is invalid for the month-year
     */
    public static function of($prolepticYear, $month, $dayOfMonth)
    {
        return new ThaiBuddhistDate(LocalDate::of($prolepticYear - ThaiBuddhistChronology::YEARS_DIFFERENCE, $month, $dayOfMonth));
    }

    /**
     * Obtains a {@code ThaiBuddhistDate} from a temporal object.
     * <p>
     * This obtains a date in the Thai Buddhist calendar system based on the specified temporal.
     * A {@code TemporalAccessor} represents an arbitrary set of date and time information,
     * which this factory converts to an instance of {@code ThaiBuddhistDate}.
     * <p>
     * The conversion typically uses the {@link ChronoField#EPOCH_DAY EPOCH_DAY}
     * field, which is standardized across calendar systems.
     * <p>
     * This method matches the signature of the functional interface {@link TemporalQuery}
     * allowing it to be used as a query via method reference, {@code ThaiBuddhistDate::from}.
     *
     * @param TemporalAccessor $temporal the temporal object to convert, not null
     * @return ThaiBuddhistDate the date in Thai Buddhist calendar system, not null
     * @throws DateTimeException if unable to convert to a {@code ThaiBuddhistDate}
     */
    public static function from(TemporalAccessor $temporal)
    {
        return ThaiBuddhistChronology::INSTANCE()->dateFrom($temporal);
    }

    /**
     * @internal
     */
    public static function ofIsoDate(LocalDate $isoDate)
    {
        return new ThaiBuddhistDate($isoDate);
    }

    //-----------------------------------------------------------------------
    /**
     * Creates an instance from an ISO date.
     *
     * @param LocalDate $isoDate the standard local date, validated not null
     */
    private function __construct(LocalDate $isoDate)
    {
        $this->isoDate = $isoDate;
    }

    //-----------------------------------------------------------------------
    /**
     * Gets the chronology of this date, which is the Thai Buddhist calendar system.
     * <p>
     * The {@code Chronology} represents the calendar system in use.
     * The era and other fields in {@link ChronoField} are defined by the chronology.
     *
     * @return ThaiBuddhistChronology the Thai Buddhist chronology, not null
     */
    public function getChronology()
    {
        return ThaiBuddhistChronology::INSTANCE();
    }

    /**
     * Gets the era applicable at this date.
     * <p>
     * The Thai Buddhist calendar system has two eras, 'BE' and 'BEFORE_BE',
     * defined by {@link ThaiBuddhistEra}.
     *
     * @return ThaiBuddhistEra the era applicable at this date, not null
     */
    public function getEra()
    {
        return ($this->getProlepticYear() >= 1 ? ThaiBuddhistEra::BE() : ThaiBuddhistEra::BEFORE_BE());
    }

    /**
     * Returns the length of the month represented by this date.
     * <p>
     * This returns the length of the month in days.
     * Month lengths match those of the ISO calendar system.
     *
     * @return int the length of the month in days
     */
    public function lengthOfMonth()
    {
        return $this->isoDate->lengthOfMonth();
    }

    //-----------------------------------------------------------------------
    public function range(TemporalField $field)
    {
        if ($field instanceof ChronoField) {
            if ($this->isSupported($field)) {
                $f = $field;
                switch ($f) {
                    case CF::DAY_OF_MONTH():
                    case CF::DAY_OF_YEAR():
                    case CF::ALIGNED_WEEK_OF_MONTH():
                        return $this->isoDate->range($field);
                    case CF::YEAR_OF_ERA(): {
                        $range = CF::YEAR()->range();
                        $max = ($this->getProlepticYear() <= 0 ? -($range->getMinimum() + ThaiBuddhistChronology::YEARS_DIFFERENCE) + 1 : $range->getMaximum() + ThaiBuddhistChronology::YEARS_DIFFERENCE);
                        return ValueRange::of(1, $max);
                    }
                }
                return $this->getChronology()->range($f);
            }
            throw new UnsupportedTemporalTypeException("Unsupported field: " . $field);
        }
        return $field->rangeRefinedBy($this);
    }


    public function getLong(TemporalField $field)
    {
        if ($field instanceof ChronoField) {
            switch ($field) {
                case CF::PROLEPTIC_MONTH():
                    return $this->getProlepticMonth();
                case CF::YEAR_OF_ERA(): {
                    $prolepticYear = $this->getProlepticYear();
                    return ($prolepticYear >= 1 ? $prolepticYear : 1 - $prolepticYear);
                }

                case CF::YEAR():
                    return $this->getProlepticYear();
                case CF::ERA():
                    return ($this->getProlepticYear() >= 1 ? 1 : 0);
            }
            return $this->isoDate->getLong($field);
        }
        return $field->getFrom($this);
    }

    private function getProlepticMonth()
    {
        return $this->getProlepticYear() * 12 + $this->isoDate->getMonthValue() - 1;
    }

    private function getProlepticYear()
    {
        return $this->isoDate->getYear() + ThaiBuddhistChronology::YEARS_DIFFERENCE;
    }

    //-----------------------------------------------------------------------

    public function with(TemporalField $field, $newValue)
    {
        if ($field instanceof ChronoField) {
            $f = $field;
            if ($this->getLong($f) === $newValue) {
                return $this;
            }

            switch ($f) {
                case CF::PROLEPTIC_MONTH():
                    $this->getChronology()->range($f)->checkValidValue($newValue, $f);
                    return $this->plusMonths($newValue - $this->getProlepticMonth());
                case CF::YEAR_OF_ERA():
                case CF::YEAR():
                case ERA: {
                    $nvalue = $this->getChronology()->range($f)->checkValidIntValue($newValue, $f);
                    switch ($f) {
                        case CF::YEAR_OF_ERA():
                            return $this->withDate($this->isoDate->withYear(($this->getProlepticYear() >= 1 ? $nvalue : 1 - $nvalue) - ThaiBuddhistChronology::YEARS_DIFFERENCE));
                        case CF::YEAR():
                            return $this->withDate($this->isoDate->withYear($nvalue - ThaiBuddhistChronology::YEARS_DIFFERENCE));
                        case ERA:
                            return $this->withDate($this->isoDate->withYear((1 - $this->getProlepticYear()) - ThaiBuddhistChronology::YEARS_DIFFERENCE));
                    }
                }
            }
            return $this->withDate($this->isoDate->with($field, $newValue));
        }
        return parent::with($field, $newValue);
    }

    function plusYears($years)
    {
        return $this->withDate($this->isoDate->plusYears($years));
    }

    function plusMonths($months)
    {
        return $this->withDate($this->isoDate->plusMonths($months));
    }


    function plusDays($days)
    {
        return $this->withDate($this->isoDate->plusDays($days));
    }

    private function withDate(LocalDate $newDate)
    {
        return ($newDate->equals($this->isoDate) ? $this : new ThaiBuddhistDate($newDate));
    }

    // for javadoc and covariant return type
    public final function atTime(LocalTime $localTime)
    {
        return parent::atTime($localTime);
    }


    public function untilDate(ChronoLocalDate $endDate)
    {
        $period = $this->isoDate->untilDate($endDate);
        return $this->getChronology()->period($period->getYears(), $period->getMonths(), $period->getDays());
    }

    // override for performance
    public function toEpochDay()
    {
        return $this->isoDate->toEpochDay();
    }

    //-------------------------------------------------------------------------
    /**
     * Compares this date to another date, including the chronology.
     * <p>
     * Compares this {@code ThaiBuddhistDate} with another ensuring that the date is the same.
     * <p>
     * Only objects of type {@code ThaiBuddhistDate} are compared, other types return false.
     * To compare the dates of two {@code TemporalAccessor} instances, including dates
     * in two different chronologies, use {@link ChronoField#EPOCH_DAY} as a comparator.
     *
     * @param mixed $obj the object to check, null returns false
     * @return true if this is equal to the other date
     */
    public function equals($obj)
    {
        if ($this === $obj) {
            return true;
        }
        if ($obj instanceof ThaiBuddhistDate) {
            return $this->isoDate->equals($obj->isoDate);
        }
        return false;
    }
}
