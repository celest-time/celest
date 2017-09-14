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

use Celest\DateTimeException;
use Celest\Helper\Long;
use Celest\Helper\Math;
use Celest\Temporal\ChronoUnit as CU;
use Celest\Temporal\ChronoField as CF;
use Celest\Temporal\ChronoUnit;
use Celest\Temporal\Temporal;
use Celest\Temporal\TemporalAdjuster;
use Celest\Temporal\TemporalUnit;
use Celest\Temporal\UnsupportedTemporalTypeException;

/**
 * A date expressed in terms of a standard year-month-day calendar system.
 * <p>
 * This class is used by applications seeking to handle dates in non-ISO calendar systems.
 * For example, the Japanese, Minguo, Thai Buddhist and others.
 * <p>
 * {@code ChronoLocalDate} is built on the generic concepts of year, month and day.
 * The calendar system, represented by a {@link java.time.chrono.Chronology}, expresses the relationship between
 * the fields and this class allows the resulting date to be manipulated.
 * <p>
 * Note that not all calendar systems are suitable for use with this class.
 * For example, the Mayan calendar uses a system that bears no relation to years, months and days.
 * <p>
 * The API design encourages the use of {@code LocalDate} for the majority of the application.
 * This includes code to read and write from a persistent data store, such as a database,
 * and to send dates and times across a network. The {@code ChronoLocalDate} instance is then used
 * at the user interface level to deal with localized input/output.
 *
 * <P>Example: </p>
 * <pre>
 *        System.out.printf("Example()%n");
 *        // Enumerate the list of available calendars and print today for each
 *        Set&lt;Chronology&gt; chronos = Chronology.getAvailableChronologies();
 *        for (Chronology chrono : chronos) {
 *            ChronoLocalDate date = chrono.dateNow();
 *            System.out.printf("   %20s: %s%n", chrono.getID(), date.toString());
 *        }
 *
 *        // Print the Hijrah date and calendar
 *        ChronoLocalDate date = Chronology.of("Hijrah").dateNow();
 *        int day = date.get(ChronoField.DAY_OF_MONTH);
 *        int dow = date.get(ChronoField.DAY_OF_WEEK);
 *        int month = date.get(ChronoField.MONTH_OF_YEAR);
 *        int year = date.get(ChronoField.YEAR);
 *        System.out.printf("  Today is %s %s %d-%s-%d%n", date.getChronology().getID(),
 *                dow, day, month, year);
 *        // Print today's date and the last day of the year
 *        ChronoLocalDate now1 = Chronology.of("Hijrah").dateNow();
 *        ChronoLocalDate first = now1.with(ChronoField.DAY_OF_MONTH, 1)
 *                .with(ChronoField.MONTH_OF_YEAR, 1);
 *        ChronoLocalDate last = first.plus(1, ChronoUnit.YEARS)
 *                .minus(1, ChronoUnit.DAYS);
 *        System.out.printf("  Today is %s: start: %s; end: %s%n", last.getChronology().getID(),
 *                first, last);
 * </pre>
 *
 * <h3>Adding Calendars</h3>
 * <p> The set of calendars is extensible by defining a subclass of {@link ChronoLocalDate}
 * to represent a date instance and an implementation of {@code Chronology}
 * to be the factory for the ChronoLocalDate subclass.
 * </p>
 * <p> To permit the discovery of the additional calendar types the implementation of
 * {@code Chronology} must be registered as a Service implementing the {@code Chronology} interface
 * in the {@code META-INF/Services} file as per the specification of {@link java.util.ServiceLoader}.
 * The subclass must function according to the {@code Chronology} class description and must provide its
 * {@link java.time.chrono.Chronology#getId() chronlogy ID} and {@link Chronology#getCalendarType() calendar type}. </p>
 *
 * @implSpec
 * This abstract class must be implemented with care to ensure other classes operate correctly.
 * All implementations that can be instantiated must be final, immutable and thread-safe.
 * Subclasses should be Serializable wherever possible.
 *
 * @param <D> the ChronoLocalDate of this date-time
 * @since 1.8
 */
abstract class ChronoLocalDateImpl extends AbstractChronoLocalDate implements Temporal, TemporalAdjuster
{

    /**
     * Casts the {@code Temporal} to {@code ChronoLocalDate} ensuring it bas the specified chronology.
     *
     * @param Chronology $chrono the chronology to check for, not null
     * @param Temporal $temporal a date-time to cast, not null
     * @return static the date-time checked and cast to {@code ChronoLocalDate}, not null
     * @throws ClassCastException if the date-time cannot be cast to ChronoLocalDate
     *  or the chronology is not equal this Chronology
     */
    static function ensureValid(Chronology $chrono, Temporal $temporal)
    {
        /** @var ChronoLocalDate $other */
        $other = $temporal;
        if ($chrono->equals($other->getChronology()) === false) {
            throw new ClassCastException("Chronology mismatch, expected: " . $chrono->getId() . ", actual: " . $other->getChronology()->getId());
        }
        return $other;
    }

    //-----------------------------------------------------------------------
    /**
     * @param int $amountToAdd
     * @param TemporalUnit $unit
     * @return static
     * @throws UnsupportedTemporalTypeException
     */
    public function plus(int $amountToAdd, TemporalUnit $unit)
    {
        if ($unit instanceof CU) {
            switch ($unit) {
                case CU::DAYS():
                    return $this->plusDays($amountToAdd);
                case CU::WEEKS():
                    return $this->plusDays(Math::multiplyExact($amountToAdd, 7));
                case CU::MONTHS():
                    return $this->plusMonths($amountToAdd);
                case CU::YEARS():
                    return $this->plusYears($amountToAdd);
                case CU::DECADES():
                    return $this->plusYears(Math::multiplyExact($amountToAdd, 10));
                case CU::CENTURIES():
                    return $this->plusYears(Math::multiplyExact($amountToAdd, 100));
                case CU::MILLENNIA():
                    return $this->plusYears(Math::multiplyExact($amountToAdd, 1000));
                case CU::ERAS():
                    return $this->with(CF::ERA(), Math::addExact($this->getLong(CF::ERA()), $amountToAdd));
            }
            throw new UnsupportedTemporalTypeException("Unsupported unit: " . $unit);
        }
        return parent::plus($amountToAdd, $unit);
    }

    //-----------------------------------------------------------------------
    /**
     * Returns a copy of this date with the specified number of years added.
     * <p>
     * This adds the specified period in years to the date.
     * In some cases, adding years can cause the resulting date to become invalid.
     * If this occurs, then other fields, typically the day-of-month, will be adjusted to ensure
     * that the result is valid. Typically this will select the last valid day of the month.
     * <p>
     * This instance is immutable and unaffected by this method call.
     *
     * @param int $yearsToAdd the years to add, may be negative
     * @return static a date based on this one with the years added, not null
     * @throws DateTimeException if the result exceeds the supported date range
     */
    abstract function plusYears($yearsToAdd);

    /**
     * Returns a copy of this date with the specified number of months added.
     * <p>
     * This adds the specified period in months to the date.
     * In some cases, adding months can cause the resulting date to become invalid.
     * If this occurs, then other fields, typically the day-of-month, will be adjusted to ensure
     * that the result is valid. Typically this will select the last valid day of the month.
     * <p>
     * This instance is immutable and unaffected by this method call.
     *
     * @param int $monthsToAdd the months to add, may be negative
     * @return static a date based on this one with the months added, not null
     * @throws DateTimeException if the result exceeds the supported date range
     */
    abstract function plusMonths($monthsToAdd);

    /**
     * Returns a copy of this date with the specified number of weeks added.
     * <p>
     * This adds the specified period in weeks to the date.
     * In some cases, adding weeks can cause the resulting date to become invalid.
     * If this occurs, then other fields will be adjusted to ensure that the result is valid.
     * <p>
     * The default implementation uses {@link #plusDays(long)} using a 7 day week.
     * <p>
     * This instance is immutable and unaffected by this method call.
     *
     * @param int $weeksToAdd the weeks to add, may be negative
     * @return static a date based on this one with the weeks added, not null
     * @throws DateTimeException if the result exceeds the supported date range
     */
    function plusWeeks($weeksToAdd)
    {
        return $this->plusDays(Math::multiplyExact($weeksToAdd, 7));
    }

    /**
     * Returns a copy of this date with the specified number of days added.
     * <p>
     * This adds the specified period in days to the date.
     * <p>
     * This instance is immutable and unaffected by this method call.
     *
     * @param int $daysToAdd the days to add, may be negative
     * @return static a date based on this one with the days added, not null
     * @throws DateTimeException if the result exceeds the supported date range
     */
    abstract function plusDays($daysToAdd);

    //-----------------------------------------------------------------------
    /**
     * Returns a copy of this date with the specified number of years subtracted.
     * <p>
     * This subtracts the specified period in years to the date.
     * In some cases, subtracting years can cause the resulting date to become invalid.
     * If this occurs, then other fields, typically the day-of-month, will be adjusted to ensure
     * that the result is valid. Typically this will select the last valid day of the month.
     * <p>
     * The default implementation uses {@link #plusYears(long)}.
     * <p>
     * This instance is immutable and unaffected by this method call.
     *
     * @param int $yearsToSubtract the years to subtract, may be negative
     * @return static a date based on this one with the years subtracted, not null
     * @throws DateTimeException if the result exceeds the supported date range
     */
    function minusYears($yearsToSubtract)
    {
        return ($yearsToSubtract === Long::MIN_VALUE ? $this->plusYears(Long::MAX_VALUE)->plusYears(1) : $this->plusYears(-$yearsToSubtract));
    }

    /**
     * Returns a copy of this date with the specified number of months subtracted.
     * <p>
     * This subtracts the specified period in months to the date.
     * In some cases, subtracting months can cause the resulting date to become invalid.
     * If this occurs, then other fields, typically the day-of-month, will be adjusted to ensure
     * that the result is valid. Typically this will select the last valid day of the month.
     * <p>
     * The default implementation uses {@link #plusMonths(long)}.
     * <p>
     * This instance is immutable and unaffected by this method call.
     *
     * @param int $monthsToSubtract the months to subtract, may be negative
     * @return static a date based on this one with the months subtracted, not null
     * @throws DateTimeException if the result exceeds the supported date range
     */
    function minusMonths($monthsToSubtract)
    {
        return ($monthsToSubtract === Long::MIN_VALUE ? $this->plusMonths(Long::MAX_VALUE)->plusMonths(1) : $this->plusMonths(-$monthsToSubtract));
    }

    /**
     * Returns a copy of this date with the specified number of weeks subtracted.
     * <p>
     * This subtracts the specified period in weeks to the date.
     * In some cases, subtracting weeks can cause the resulting date to become invalid.
     * If this occurs, then other fields will be adjusted to ensure that the result is valid.
     * <p>
     * The default implementation uses {@link #plusWeeks(long)}.
     * <p>
     * This instance is immutable and unaffected by this method call.
     *
     * @param int $weeksToSubtract the weeks to subtract, may be negative
     * @return static a date based on this one with the weeks subtracted, not null
     * @throws DateTimeException if the result exceeds the supported date range
     */
    function minusWeeks($weeksToSubtract)
    {
        return ($weeksToSubtract === Long::MIN_VALUE ? $this->plusWeeks(Long::MAX_VALUE)->plusWeeks(1) : $this->plusWeeks(-$weeksToSubtract));
    }

    /**
     * Returns a copy of this date with the specified number of days subtracted.
     * <p>
     * This subtracts the specified period in days to the date.
     * <p>
     * The default implementation uses {@link #plusDays(long)}.
     * <p>
     * This instance is immutable and unaffected by this method call.
     *
     * @param int $daysToSubtract the days to subtract, may be negative
     * @return static a date based on this one with the days subtracted, not null
     * @throws DateTimeException if the result exceeds the supported date range
     */
    function minusDays($daysToSubtract)
    {
        return ($daysToSubtract === Long::MIN_VALUE ? $this->plusDays(Long::MAX_VALUE)->plusDays(1) : $this->plusDays(-$daysToSubtract));
    }

    //-----------------------------------------------------------------------
    public function until(Temporal $endExclusive, TemporalUnit $unit)
    {
        $end = $this->getChronology()->dateFrom($endExclusive);
        if ($unit instanceof ChronoUnit) {
            switch ($unit) {
                case CU::DAYS():
                    return $this->daysUntil($end);
                case CU::WEEKS():
                    return $this->daysUntil($end) / 7;
                case CU::MONTHS():
                    return $this->monthsUntil($end);
                case CU::YEARS():
                    return $this->monthsUntil($end) / 12;
                case CU::DECADES():
                    return $this->monthsUntil($end) / 120;
                case CU::CENTURIES():
                    return $this->monthsUntil($end) / 1200;
                case CU::MILLENNIA():
                    return $this->monthsUntil($end) / 12000;
                case CU::ERAS():
                    return $end->getLong(CF::ERA()) - $this->getLong(CF::ERA());
            }
            throw new UnsupportedTemporalTypeException("Unsupported unit: " . $unit);
        }
        return $unit->between($this, $end);
    }

    private function daysUntil(ChronoLocalDate $end)
    {
        return $end->toEpochDay() - $this->toEpochDay();  // no overflow
    }

    private function monthsUntil(ChronoLocalDate $end)
    {
        $range = $this->getChronology()->range(CF::MONTH_OF_YEAR());
        if ($range->getMaximum() !== 12) {
            throw new \AssertionError("ChronoLocalDateImpl only supports Chronologies with 12 months per year");
        }
        $packed1 = $this->getLong(CF::PROLEPTIC_MONTH()) * 32 + $this->get(CF::DAY_OF_MONTH());  // no overflow
        $packed2 = $end->getLong(CF::PROLEPTIC_MONTH()) * 32 + $end->get(CF::DAY_OF_MONTH());  // no overflow
        return ($packed2 - $packed1) / 32;
    }

    public function equals($obj) : bool
    {
        if ($this === $obj) {
            return true;
        }
        if ($obj instanceof ChronoLocalDate) {
            return $this->compareTo($obj) === 0;
        }
        return false;
    }

    public function __toString() : string
    {
        // getLong() reduces chances of exceptions in toString()
        $yoe = $this->getLong(CF::YEAR_OF_ERA());
        $moy = $this->getLong(CF::MONTH_OF_YEAR());
        $dom = $this->getLong(CF::DAY_OF_MONTH());
        return $this->getChronology()->__toString()
        . " "
        . $this->getEra()
        . " "
        . $yoe
        . ($moy < 10 ? "-0" : "-") . $moy
        . ($dom < 10 ? "-0" : "-") . $dom;
    }

}
