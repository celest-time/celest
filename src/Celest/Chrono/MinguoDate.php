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
use Celest\Temporal\ChronoField;
use Celest\Temporal\TemporalAccessor;
use Celest\Temporal\TemporalField;
use Celest\Temporal\UnsupportedTemporalTypeException;
use Celest\Temporal\ValueRange;
use Celest\ZoneId;

/**
 * A date in the Minguo calendar system.
 * <p>
 * This date operates using the {@linkplain MinguoChronology Minguo calendar}.
 * This calendar system is primarily used in the Republic of China, often known as Taiwan.
 * Dates are aligned such that {@code 0001-01-01 (Minguo)} is {@code 1912-01-01 (ISO)}.
 *
 * <p>
 * This is a <a href="{@docRoot}/java/lang/doc-files/ValueBased.html">value-based</a>
 * class; use of identity-sensitive operations (including reference equality
 * ({@code ==}), identity hash code, or synchronization) on instances of
 * {@code MinguoDate} may have unpredictable results and should be avoided.
 * The {@code equals} method should be used for comparisons.
 *
 * @implSpec
 * This class is immutable and thread-safe.
 *
 * @since 1.8
 */
final class MinguoDate extends ChronoLocalDateImpl implements ChronoLocalDate
{

    /**
     * The underlying date.
     * @var LocalDate
     */
    private $isoDate;

    //-----------------------------------------------------------------------
    /**
     * Obtains the current {@code MinguoDate} from the system clock in the default time-zone.
     * <p>
     * This will query the {@link Clock#systemDefaultZone() system clock} in the default
     * time-zone to obtain the current date.
     * <p>
     * Using this method will prevent the ability to use an alternate clock for testing
     * because the clock is hard-coded.
     *
     * @return MinguoDate the current date using the system clock and default time-zone, not null
     */
    public static function now()
    {
        return self::nowOf(Clock::systemDefaultZone());
    }

    /**
     * Obtains the current {@code MinguoDate} from the system clock in the specified time-zone.
     * <p>
     * This will query the {@link Clock#system(ZoneId) system clock} to obtain the current date.
     * Specifying the time-zone avoids dependence on the default time-zone.
     * <p>
     * Using this method will prevent the ability to use an alternate clock for testing
     * because the clock is hard-coded.
     *
     * @param ZoneId $zone the zone ID to use, not null
     * @return MinguoDate the current date using the system clock, not null
     */
    public static function nowIn(ZoneId $zone)
    {
        return self::nowOf(Clock::system($zone));
    }

    /**
     * Obtains the current {@code MinguoDate} from the specified clock.
     * <p>
     * This will query the specified clock to obtain the current date - today.
     * Using this method allows the use of an alternate clock for testing.
     * The alternate clock may be introduced using {@linkplain Clock dependency injection}.
     *
     * @param Clock $clock the clock to use, not null
     * @return MinguoDate the current date, not null
     * @throws DateTimeException if the current date cannot be obtained
     */
    public static function nowOf(Clock $clock)
    {
        return new MinguoDate(LocalDate::nowOf($clock));
    }

    /**
     * Obtains a {@code MinguoDate} representing a date in the Minguo calendar
     * system from the proleptic-year, month-of-year and day-of-month fields.
     * <p>
     * This returns a {@code MinguoDate} with the specified fields.
     * The day must be valid for the year and month, otherwise an exception will be thrown.
     *
     * @param int $prolepticYear the Minguo proleptic-year
     * @param int $month the Minguo month-of-year, from 1 to 12
     * @param int $dayOfMonth the Minguo day-of-month, from 1 to 31
     * @return MinguoDate the date in Minguo calendar system, not null
     * @throws DateTimeException if the value of any field is out of range,
     *  or if the day-of-month is invalid for the month-year
     */
    public static function of($prolepticYear, $month, $dayOfMonth)
    {
        return new MinguoDate(LocalDate::of($prolepticYear + MinguoChronology::YEARS_DIFFERENCE, $month, $dayOfMonth));
    }

    /**
     * @internal
     */
    public static function ofIsoDate(LocalDate $isoDate)
    {
        return new MinguoDate($isoDate);
    }

    /**
     * Obtains a {@code MinguoDate} from a temporal object.
     * <p>
     * This obtains a date in the Minguo calendar system based on the specified temporal.
     * A {@code TemporalAccessor} represents an arbitrary set of date and time information,
     * which this factory converts to an instance of {@code MinguoDate}.
     * <p>
     * The conversion typically uses the {@link ChronoField#EPOCH_DAY EPOCH_DAY}
     * field, which is standardized across calendar systems.
     * <p>
     * This method matches the signature of the functional interface {@link TemporalQuery}
     * allowing it to be used as a query via method reference, {@code MinguoDate::from}.
     *
     * @param TemporalAccessor $temporal the temporal object to convert, not null
     * @return MinguoDate the date in Minguo calendar system, not null
     * @throws DateTimeException if unable to convert to a {@code MinguoDate}
     */
    public static function from(TemporalAccessor $temporal)
    {
        return MinguoChronology::INSTANCE()->dateFrom($temporal);
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
     * Gets the chronology of this date, which is the Minguo calendar system.
     * <p>
     * The {@code Chronology} represents the calendar system in use.
     * The era and other fields in {@link ChronoField} are defined by the chronology.
     *
     * @return MinguoChronology the Minguo chronology, not null
     */
    public function getChronology()
    {
        return MinguoChronology::INSTANCE();
    }

    /**
     * Gets the era applicable at this date.
     * <p>
     * The Minguo calendar system has two eras, 'ROC' and 'BEFORE_ROC',
     * defined by {@link MinguoEra}.
     *
     * @return MinguoEra the era applicable at this date, not null
     */
    public function getEra()
    {
        return ($this->getProlepticYear() >= 1 ? MinguoEra::ROC() : MinguoEra::BEFORE_ROC());
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
                switch ($field) {
                    case ChronoField::DAY_OF_MONTH():
                    case ChronoField::DAY_OF_YEAR():
                    case ChronoField::ALIGNED_WEEK_OF_MONTH():
                        return $this->isoDate->range($field);
                    case ChronoField::YEAR_OF_ERA(): {
                        $range = ChronoField::YEAR()->range();
                        $max = ($this->getProlepticYear() <= 0 ? -$range->getMinimum() + 1 + MinguoChronology::YEARS_DIFFERENCE : $range->getMaximum() - MinguoChronology::YEARS_DIFFERENCE);
                        return ValueRange::of(1, $max);
                    }
                }
                return $this->getChronology()->range($field);
            }
            throw new UnsupportedTemporalTypeException("Unsupported field: " . $field);
        }
        return $field->rangeRefinedBy($this);
    }

    public function getLong(TemporalField $field)
    {
        if ($field instanceof ChronoField) {
            switch ($field) {
                case ChronoField::PROLEPTIC_MONTH():
                    return $this->getProlepticMonth();
                case ChronoField::YEAR_OF_ERA(): {
                    $prolepticYear = $this->getProlepticYear();
                    return ($prolepticYear >= 1 ? $prolepticYear : 1 - $prolepticYear);
                }
                case ChronoField::YEAR():
                    return $this->getProlepticYear();
                case ChronoField::ERA():
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
        return $this->isoDate->getYear() - MinguoChronology::YEARS_DIFFERENCE;
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
                case ChronoField::PROLEPTIC_MONTH():
                    $this->getChronology()->range($f)->checkValidValue($newValue, $f);
                    return $this->plusMonths($newValue - $this->getProlepticMonth());
                case ChronoField::YEAR_OF_ERA():
                case ChronoField::YEAR():
                case ChronoField::ERA(): {
                    $nvalue = $this->getChronology()->range($f)->checkValidIntValue($newValue, $f);
                    switch ($f) {
                        case ChronoField::YEAR_OF_ERA():
                            return $this->withDate($this->isoDate->withYear($this->getProlepticYear() >= 1 ? $nvalue + MinguoChronology::YEARS_DIFFERENCE : (1 - $nvalue) + MinguoChronology::YEARS_DIFFERENCE));
                        case ChronoField::YEAR():
                            return $this->withDate($this->isoDate->withYear($nvalue + MinguoChronology::YEARS_DIFFERENCE));
                        case ERA:
                            return $this->withDate($this->isoDate->withYear((1 - $this->getProlepticYear()) + MinguoChronology::YEARS_DIFFERENCE));
                    }
                }
            }
            return $this->withDate($this->isoDate->with($field, $newValue));
        }
        return parent::with($field, $newValue);
    }

    //-----------------------------------------------------------------------
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
        return ($newDate->equals($this->isoDate) ? $this : new MinguoDate($newDate));
    }

    public function untilDate(ChronoLocalDate $endDate)
    {
        $period = $this->isoDate->untilDate($endDate);
        return $this->getChronology()->period($period->getYears(), $period->getMonths(), $period->getDays());
    }

    public function toEpochDay()
    {
        return $this->isoDate->toEpochDay();
    }

    //-------------------------------------------------------------------------
    /**
     * Compares this date to another date, including the chronology.
     * <p>
     * Compares this {@code MinguoDate} with another ensuring that the date is the same.
     * <p>
     * Only objects of type {@code MinguoDate} are compared, other types return false.
     * To compare the dates of two {@code TemporalAccessor} instances, including dates
     * in two different chronologies, use {@link ChronoField#EPOCH_DAY} as a comparator.
     *
     * @param mixed $obj the object to check, null returns false
     * @return bool true if this is equal to the other date
     */
    public function equals($obj)
    {
        if ($this === $obj) {
            return true;
        }
        if ($obj instanceof MinguoDate) {
            $otherDate = $obj;
            return $this->isoDate->equals($otherDate->isoDate);
        }
        return false;
    }
}
