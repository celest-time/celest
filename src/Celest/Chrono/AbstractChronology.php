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
namespace Celest\Chrono;

use Celest\DateTimeException;
use Celest\DayOfWeek;
use Celest\Format\ResolverStyle;
use Celest\Helper\Math;
use Celest\Temporal\ChronoField;
use Celest\Temporal\ChronoUnit;
use Celest\Temporal\TemporalAdjusters;

/**
 * An abstract implementation of a calendar system, used to organize and identify dates.
 * <p>
 * The main date and time API is built on the ISO calendar system.
 * The chronology operates behind the scenes to represent the general concept of a calendar system.
 * <p>
 * See {@link Chronology} for more details.
 *
 * @implSpec
 * This class is separated from the {@code Chronology} interface so that the static methods
 * are not inherited. While {@code Chronology} can be implemented directly, it is strongly
 * recommended to extend this abstract class instead.
 * <p>
 * This class must be implemented with care to ensure other classes operate correctly.
 * All implementations that can be instantiated must be final, immutable and thread-safe.
 * Subclasses should be Serializable wherever possible.
 *
 * @since 1.8
 */
abstract class AbstractChronology implements Chronology
{

    /**
     * ChronoLocalDate order constant.
     */
//static final Comparator<ChronoLocalDate> DATE_ORDER =
//(Comparator<ChronoLocalDate> & Serializable) (date1, date2) -> {
//return Long.compare(date1.toEpochDay(), date2.toEpochDay());
//};
    /**
     * ChronoLocalDateTime order constant.
     */
//    static final Comparator<ChronoLocalDateTime<? extends ChronoLocalDate>> DATE_TIME_ORDER =
//    (Comparator<ChronoLocalDateTime<? extends ChronoLocalDate>> & Serializable) (dateTime1, dateTime2) -> {
//    int cmp = Long.compare(dateTime1.toLocalDate().toEpochDay(), dateTime2.toLocalDate().toEpochDay());
//            if (cmp == 0) {
//                cmp = Long.compare(dateTime1.toLocalTime().toNanoOfDay(), dateTime2.toLocalTime().toNanoOfDay());
//            }
//            return cmp;
//        };
    /**
     * ChronoZonedDateTime order constant.
     */
//    static final Comparator<ChronoZonedDateTime<><!--> INSTANT_ORDER =
//    (Comparator<ChronoZonedDateTime--><>> & Serializable) (dateTime1, dateTime2) -> {
//    int cmp = Long.compare(dateTime1.toEpochSecond(), dateTime2.toEpochSecond());
//                if (cmp == 0) {
//                    cmp = Long.compare(dateTime1.toLocalTime().getNano(), dateTime2.toLocalTime().getNano());
//                }
//                return cmp;
//            };

    /**
     * Map of available calendars by ID.
     * @var Chronology[]
     */
    private static $CHRONOS_BY_ID = [];
    /**
     * Map of available calendars by calendar type.
     * @var Chronology[]
     */
    private static $CHRONOS_BY_TYPE = [];

    /**
     * Register a Chronology by ID and type for lookup by {@link #of(String)}.
     * Chronos must not be registered until they are completely constructed.
     * Specifically, not in the constructor of Chronology.
     *
     * @param Chronology $chrono the chronology to register; not null
     * @param null|string $id the ID to register the chronology
     * @return Chronology the already registered Chronology if any, may be null
     */
    static function registerChrono(Chronology $chrono, $id = null)
    {
        if($id === null) {
            $id = $chrono->getId();
        }

        $prev = self::$CHRONOS_BY_ID->putIfAbsent($id, $chrono);
        if ($prev == null) {
            $type = $chrono->getCalendarType();
            if ($type != null) {
                self::$CHRONOS_BY_TYPE->putIfAbsent($type, $chrono);
            }
        }
        return $prev;
    }

    /**
     * Initialization of the maps from id and type to Chronology.
     * The ServiceLoader is used to find and register any implementations
     * of {@link java.time.chrono.AbstractChronology} found in the bootclass loader.
     * The built-in chronologies are registered explicitly.
     * Calendars configured via the Thread's context classloader are local
     * to that thread and are ignored.
     * <p>
     * The initialization is done only once using the registration
     * of the IsoChronology as the test and the final step.
     * Multiple threads may perform the initialization concurrently.
     * Only the first registration of each Chronology is retained by the
     * ConcurrentHashMap.
     * @return bool true if the cache was initialized
     */
    private static function initCache()
    {
        if (self::$CHRONOS_BY_ID->get("ISO") == null) {
// Initialization is incomplete

// Register built-in Chronologies
//            self::registerChrono(HijrahChronology->INSTANCE);
//self::registerChrono(JapaneseChronology->INSTANCE);
//self::registerChrono(MinguoChronology->INSTANCE);
//lf::registerChrono(ThaiBuddhistChronology->INSTANCE);

// Register Chronologies from the ServiceLoader
//@SuppressWarnings("rawtypes")
//ServiceLoader < AbstractChronology> loader = ServiceLoader->load(AbstractChronology .class, null);
//for (AbstractChronology chrono : loader)
//{
//    String id = chrono->getId();
//if (id->equals("ISO") || registerChrono(chrono) != null)
//{
// Log the attempt to replace an existing Chronology
//        PlatformLogger logger = PlatformLogger->getLogger("java.time.chrono");
//logger->warning("Ignoring duplicate Chronology, from ServiceLoader configuration " + id);
//}
//}

// finally, register IsoChronology to mark initialization is complete
            self::registerChrono(IsoChronology::INSTANCE());
            return true;
        }
        return false;
    }

    //-----------------------------------------------------------------------
    /**
     * Obtains an instance of {@code Chronology} from a locale.
     * <p>
     * See {@link Chronology#ofLocale(Locale)}.
     *
     * @param Locale $locale the locale to use to obtain the calendar system, not null
     * @return Chronology the calendar system associated with the locale, not null
     * @throws DateTimeException if the locale-specified calendar cannot be found
     */
    static function ofLocale(Locale $locale)
    {
        $type = $locale->getUnicodeLocaleType("ca");
        if ($type == null || "iso" === $type || "iso8601" === $type) {
            return IsoChronology::INSTANCE();
        }

// Not pre-defined; lookup by the type
//        do {
//            Chronology chrono = CHRONOS_BY_TYPE->get(type);
//            if (chrono != null) {
//                return chrono;
//            }
//            // If not found, do the initialization (once) and repeat the lookup
//        } while (initCache());
//
//// Look for a Chronology using ServiceLoader of the Thread's ContextClassLoader
//// Application provided Chronologies must not be cached
//        @SuppressWarnings("rawtypes")
//        ServiceLoader < Chronology> loader = ServiceLoader->load(Chronology .class);
//        for (Chronology chrono : $loader) {
//            if ($type->equals($chrono->getCalendarType())) {
//                return $chrono;
//            }
//        }
        throw new DateTimeException("Unknown calendar system: " . $type);
    }

    //-----------------------------------------------------------------------
    /**
     * Obtains an instance of {@code Chronology} from a chronology ID or
     * calendar system type.
     * <p>
     * See {@link Chronology#of(String)}.
     *
     * @param string $id the chronology ID or calendar system type, not null
     * @return Chronology the chronology with the identifier requested, not null
     * @throws DateTimeException if the chronology cannot be found
     */
    static function of($id)
    {
        do {
            $chrono = self::of0($id);
            if ($chrono != null) {
                return $chrono;
            }
// If not found, do the initialization (once) and repeat the lookup
        } while (self::initCache());

// Look for a Chronology using ServiceLoader of the Thread's ContextClassLoader
// Application provided Chronologies must not be cached
//        @SuppressWarnings("rawtypes")
//        ServiceLoader < Chronology> loader = ServiceLoader->load(Chronology .class);
//        for (Chronology chrono : loader) {
//            if (id->equals(chrono->getId()) || id->equals(chrono->getCalendarType())) {
//                return chrono;
//            }
//        }
        throw new DateTimeException("Unknown chronology: " . $id);
    }

    /**
     * Obtains an instance of {@code Chronology} from a chronology ID or
     * calendar system type.
     *
     * @param string $id the chronology ID or calendar system type, not null
     * @return string the chronology with the identifier requested, or {@code null} if not found
     */
    private static function of0($id)
    {
        $chrono = self::$CHRONOS_BY_ID->get($id);
        if ($chrono == null) {
            $chrono = self::$CHRONOS_BY_TYPE->get($id);
        }

        return $chrono;
    }

    /**
     * Returns the available chronologies.
     * <p>
     * Each returned {@code Chronology} is available for use in the system.
     * The set of chronologies includes the system chronologies and
     * any chronologies provided by the application via ServiceLoader
     * configuration.
     *
     * @return Chronology[] the independent, modifiable set of the available chronology IDs, not null
     */
    static function getAvailableChronologies()
    {
//initCache();       // force initialization
//HashSet < Chronology> chronos = new HashSet <> (CHRONOS_BY_ID->values());
//
//    /// Add in Chronologies from the ServiceLoader configuration
//@SuppressWarnings("rawtypes")
//ServiceLoader < Chronology> loader = ServiceLoader->load(Chronology .class);
//for (Chronology chrono : loader)
//{
//chronos->add(chrono);
//}
//
//return chronos;
    }

//-----------------------------------------------------------------------
    /**
     * Creates an instance.
     */
    protected function __construct()
    {
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
     * {@code ChronoField} instances are resolved by this method, which may
     * be overridden in subclasses.
     * <ul>
     * <li>{@code EPOCH_DAY} - If present, this is converted to a date and
     *  all other date fields are then cross-checked against the date.
     * <li>{@code PROLEPTIC_MONTH} - If present, then it is split into the
     *  {@code YEAR} and {@code MONTH_OF_YEAR}. If the mode is strict or smart
     *  then the field is validated.
     * <li>{@code YEAR_OF_ERA} and {@code ERA} - If both are present, then they
     *  are combined to form a {@code YEAR}. In lenient mode, the {@code YEAR_OF_ERA}
     *  range is not validated, in smart and strict mode it is. The {@code ERA} is
     *  validated for range in all three modes. If only the {@code YEAR_OF_ERA} is
     *  present, and the mode is smart or lenient, then the last available era
     *  is assumed. In strict mode, no era is assumed and the {@code YEAR_OF_ERA} is
     *  left untouched. If only the {@code ERA} is present, then it is left untouched.
     * <li>{@code YEAR}, {@code MONTH_OF_YEAR} and {@code DAY_OF_MONTH} -
     *  If all three are present, then they are combined to form a date.
     *  In all three modes, the {@code YEAR} is validated.
     *  If the mode is smart or strict, then the month and day are validated.
     *  If the mode is lenient, then the date is combined in a manner equivalent to
     *  creating a date on the first day of the first month in the requested year,
     *  then adding the difference in months, then the difference in days.
     *  If the mode is smart, and the day-of-month is greater than the maximum for
     *  the year-month, then the day-of-month is adjusted to the last day-of-month.
     *  If the mode is strict, then the three fields must form a valid date.
     * <li>{@code YEAR} and {@code DAY_OF_YEAR} -
     *  If both are present, then they are combined to form a date.
     *  In all three modes, the {@code YEAR} is validated.
     *  If the mode is lenient, then the date is combined in a manner equivalent to
     *  creating a date on the first day of the requested year, then adding
     *  the difference in days.
     *  If the mode is smart or strict, then the two fields must form a valid date.
     * <li>{@code YEAR}, {@code MONTH_OF_YEAR}, {@code ALIGNED_WEEK_OF_MONTH} and
     *  {@code ALIGNED_DAY_OF_WEEK_IN_MONTH} -
     *  If all four are present, then they are combined to form a date.
     *  In all three modes, the {@code YEAR} is validated.
     *  If the mode is lenient, then the date is combined in a manner equivalent to
     *  creating a date on the first day of the first month in the requested year, then adding
     *  the difference in months, then the difference in weeks, then in days.
     *  If the mode is smart or strict, then the all four fields are validated to
     *  their outer ranges. The date is then combined in a manner equivalent to
     *  creating a date on the first day of the requested year and month, then adding
     *  the amount in weeks and days to reach their values. If the mode is strict,
     *  the date is additionally validated to check that the day and week adjustment
     *  did not change the month.
     * <li>{@code YEAR}, {@code MONTH_OF_YEAR}, {@code ALIGNED_WEEK_OF_MONTH} and
     *  {@code DAY_OF_WEEK} - If all four are present, then they are combined to
     *  form a date. The approach is the same as described above for
     *  years, months and weeks in {@code ALIGNED_DAY_OF_WEEK_IN_MONTH}.
     *  The day-of-week is adjusted as the next or same matching day-of-week once
     *  the years, months and weeks have been handled.
     * <li>{@code YEAR}, {@code ALIGNED_WEEK_OF_YEAR} and {@code ALIGNED_DAY_OF_WEEK_IN_YEAR} -
     *  If all three are present, then they are combined to form a date.
     *  In all three modes, the {@code YEAR} is validated.
     *  If the mode is lenient, then the date is combined in a manner equivalent to
     *  creating a date on the first day of the requested year, then adding
     *  the difference in weeks, then in days.
     *  If the mode is smart or strict, then the all three fields are validated to
     *  their outer ranges. The date is then combined in a manner equivalent to
     *  creating a date on the first day of the requested year, then adding
     *  the amount in weeks and days to reach their values. If the mode is strict,
     *  the date is additionally validated to check that the day and week adjustment
     *  did not change the year.
     * <li>{@code YEAR}, {@code ALIGNED_WEEK_OF_YEAR} and {@code DAY_OF_WEEK} -
     *  If all three are present, then they are combined to form a date.
     *  The approach is the same as described above for years and weeks in
     *  {@code ALIGNED_DAY_OF_WEEK_IN_YEAR}. The day-of-week is adjusted as the
     *  next or same matching day-of-week once the years and weeks have been handled.
     * </ul>
     * <p>
     * The default implementation is suitable for most calendar systems.
     * If {@link java.time.temporal.ChronoField#YEAR_OF_ERA} is found without an {@link java.time.temporal.ChronoField#ERA}
     * then the last era in {@link #eras()} is used.
     * The implementation assumes a 7 day week, that the first day-of-month
     * has the value 1, that first day-of-year has the value 1, and that the
     * first of the month and year always exists.
     *
     * @param array $fieldValues TemporalField=>int the map of fields to values, which can be updated, not null
     * @param ResolverStyle $resolverStyle the requested type of resolve, not null
     * @return ChronoLocalDate the resolved date, null if insufficient information to create a date
     * @throws DateTimeException if the date cannot be resolved, typically
     *  because of a conflict in the input data
     */
    public function resolveDate(array &$fieldValues, ResolverStyle $resolverStyle)
    {
        // check epoch-day before inventing era
        if (self::contains($fieldValues, ChronoField::EPOCH_DAY())) {
            return $this->dateEpochDay(self::remove($fieldValues, ChronoField::EPOCH_DAY()));
        }

// fix proleptic month before inventing era
        $this->resolveProlepticMonth($fieldValues, $resolverStyle);

// invent era if necessary to resolve year-of-era
        $resolved = $this->resolveYearOfEra($fieldValues, $resolverStyle);
        if ($resolved !== null) {
            return $resolved;
        }

        // build date
        if (self::contains($fieldValues, ChronoField::YEAR())) {
            if (self::contains($fieldValues, ChronoField::MONTH_OF_YEAR())) {
                if (self::contains($fieldValues, ChronoField::DAY_OF_MONTH())) {
                    return $this->resolveYMD($fieldValues, $resolverStyle);
                }
                if (self::contains($fieldValues, ChronoField::ALIGNED_WEEK_OF_MONTH())) {
                    if (self::contains($fieldValues, ChronoField::ALIGNED_DAY_OF_WEEK_IN_MONTH())) {
                        return $this->resolveYMAA($fieldValues, $resolverStyle);
                    }
                    if (self::contains($fieldValues, ChronoField::DAY_OF_WEEK())) {
                        return $this->resolveYMAD($fieldValues, $resolverStyle);
                    }
                }
            }
            if (self::contains($fieldValues, ChronoField::DAY_OF_YEAR())) {
                return $this->resolveYD($fieldValues, $resolverStyle);
            }
            if (self::contains($fieldValues, ChronoField::ALIGNED_WEEK_OF_YEAR())) {
                if (self::contains($fieldValues, ChronoField::ALIGNED_DAY_OF_WEEK_IN_YEAR())) {
                    return $this->resolveYAA($fieldValues, $resolverStyle);
                }
                if (self::contains($fieldValues, ChronoField::DAY_OF_WEEK())) {
                    return $this->resolveYAD($fieldValues, $resolverStyle);
                }
            }
        }
        return null;
    }

    /**
     * @param $array
     * @param ChronoField $field
     * @return bool
     */
    private static function contains(array $array, ChronoField $field)
    {
        return array_key_exists($field->__toString(), $array);
    }

    /**
     * @param $array
     * @param ChronoField $field
     * @return null|int
     */
    protected static function remove(array &$array, ChronoField $field)
    {
        $id = $field->__toString();
        // Get null or the value
        $val = @$array[$id];
        unset ($array[$id]);
        return $val[1];
    }

    protected function resolveProlepticMonth(array &$fieldValues, ResolverStyle $resolverStyle)
    {
        $pMonth = self::remove($fieldValues, ChronoField::PROLEPTIC_MONTH());
        if ($pMonth != null) {
            if ($resolverStyle != ResolverStyle::LENIENT()) {
                ChronoField::PROLEPTIC_MONTH()->checkValidValue($pMonth);
            }

// first day-of-month is likely to be safest for setting proleptic-month
// cannot add to year zero, as not all chronologies have a year zero
            $chronoDate = $this->dateNow()
                ->with(ChronoField::DAY_OF_MONTH(), 1)->with(ChronoField::PROLEPTIC_MONTH(), $pMonth);
            self::addFieldValue($fieldValues, ChronoField::MONTH_OF_YEAR(), $chronoDate->get(ChronoField::MONTH_OF_YEAR()));
            self::addFieldValue($fieldValues, ChronoField::YEAR(), $chronoDate->get(ChronoField::YEAR()));
        }
    }

    protected function resolveYearOfEra(array &$fieldValues, ResolverStyle $resolverStyle)
    {
        $yoeLong = self::remove($fieldValues, ChronoField::YEAR_OF_ERA());
        if ($yoeLong != null) {
            $eraLong = self::remove($fieldValues, ChronoField::ERA());
            if ($resolverStyle != ResolverStyle::LENIENT()) {
                $yoe = $this->range(ChronoField::YEAR_OF_ERA())->checkValidIntValue($yoeLong, ChronoField::YEAR_OF_ERA());
            } else {
                $yoe = Math::toIntExact($yoeLong);
            }
            if ($eraLong != null) {
                $eraObj = $this->eraOf($this->range(ChronoField::ERA())->checkValidIntValue($eraLong, ChronoField::ERA()));
                self::addFieldValue($fieldValues, ChronoField::YEAR(), $this->prolepticYear($eraObj, $yoe));
            } else if (self::contains($fieldValues, ChronoField::YEAR())) {
                $year = $this->range(ChronoField::YEAR())->checkValidIntValue($fieldValues[ChronoField::YEAR()->__toString()][1], ChronoField::YEAR());
                $chronoDate = $this->dateYearDay($year, 1);
                self::addFieldValue($fieldValues, ChronoField::YEAR(), $this->prolepticYear($chronoDate->getEra(), $yoe));
            } else if ($resolverStyle == ResolverStyle::STRICT()) {
                // do not invent era if strict
                // reinstate the field removed earlier, no cross-check issues
                $fieldValues[ChronoField::YEAR_OF_ERA()->__toString()][1] = $yoeLong;
            } else {
                $eras = $this->eras();
                if (empty($eras)) {
                    self::addFieldValue($fieldValues, ChronoField::YEAR(), $yoe);
                } else {
                    $eraObj = $eras[count($eras) - 1];
                    $this->addFieldValue($fieldValues, ChronoField::YEAR(), $this->prolepticYear($eraObj, $yoe));
                }
            }
        } else if (self::contains($fieldValues, ChronoField::ERA())) {
            $this->range(ChronoField::ERA())->checkValidValue($fieldValues[ChronoField::ERA()->__toString()][1], ChronoField::ERA());  // always validated
        }

        return null;
    }

    protected
    function resolveYMD(array &$fieldValues, ResolverStyle $resolverStyle)
    {
        $y = $this->range(ChronoField::YEAR())->checkValidIntValue(self::remove($fieldValues, ChronoField::YEAR()), ChronoField::YEAR());
        if ($resolverStyle == ResolverStyle::LENIENT()) {
            $months = Math::subtractExact(self::remove($fieldValues, ChronoField::MONTH_OF_YEAR()), 1);
            $days = Math::subtractExact(self::remove($fieldValues, ChronoField::DAY_OF_MONTH()), 1);
            return $this->date($y, 1, 1)->plus($months, ChronoUnit::MONTHS())->plus($days, ChronoUnit::DAYS());
        }

        $moy = $this->range(ChronoField::MONTH_OF_YEAR())->checkValidIntValue(self::remove($fieldValues, ChronoField::MONTH_OF_YEAR()), ChronoField::MONTH_OF_YEAR());
        $domRange = $this->range(ChronoField::DAY_OF_MONTH());
        $dom = $domRange->checkValidIntValue(self::remove($fieldValues, ChronoField::DAY_OF_MONTH()), ChronoField::DAY_OF_MONTH());
        if ($resolverStyle == ResolverStyle::SMART()) {  // previous valid
            try {
                return $this->date($y, $moy, $dom);
            } catch (DateTimeException $ex) {
                return $this->date($y, $moy, 1)->adjust(TemporalAdjusters::lastDayOfMonth());
            }
        }
        return $this->date($y, $moy, $dom);
    }

    protected
    function resolveYD(array &$fieldValues, ResolverStyle $resolverStyle)
    {
        $y = $this->range(ChronoField::YEAR())->checkValidIntValue(self::remove($fieldValues, ChronoField::YEAR()), ChronoField::YEAR());
        if ($resolverStyle == ResolverStyle::LENIENT()) {
            $days = Math::subtractExact(self::remove($fieldValues, ChronoField::DAY_OF_YEAR()), 1);
            return $this->dateYearDay($y, 1)->plus($days, ChronoUnit::DAYS());
        }
        $doy = $this->range(ChronoField::DAY_OF_YEAR())->checkValidIntValue(self::remove($fieldValues, ChronoField::DAY_OF_YEAR()), ChronoField::DAY_OF_YEAR());
        return $this->dateYearDay($y, $doy);  // smart is same as strict
    }

    protected
    function resolveYMAA(array &$fieldValues, ResolverStyle $resolverStyle)
    {
        $y = $this->range(ChronoField::YEAR())->checkValidIntValue(self::remove($fieldValues, ChronoField::YEAR()), ChronoField::YEAR());
        if ($resolverStyle == ResolverStyle::LENIENT()) {
            $months = Math::subtractExact(self::remove($fieldValues, ChronoField::MONTH_OF_YEAR()), 1);
            $weeks = Math::subtractExact(self::remove($fieldValues, ChronoField::ALIGNED_WEEK_OF_MONTH()), 1);
            $days = Math::subtractExact(self::remove($fieldValues, ChronoField::ALIGNED_DAY_OF_WEEK_IN_MONTH()), 1);
            return $this->date($y, 1, 1)->plus($months, ChronoUnit::MONTHS())->plus($weeks, ChronoUnit::WEEKS())->plus($days, ChronoUnit::DAYS());
        }
        $moy = $this->range(ChronoField::MONTH_OF_YEAR())->checkValidIntValue(self::remove($fieldValues, ChronoField::MONTH_OF_YEAR()), ChronoField::MONTH_OF_YEAR());
        $aw = $this->range(ChronoField::ALIGNED_WEEK_OF_MONTH())->checkValidIntValue(self::remove($fieldValues, ChronoField::ALIGNED_WEEK_OF_MONTH()), ChronoField::ALIGNED_WEEK_OF_MONTH());
        $ad = $this->range(ChronoField::ALIGNED_DAY_OF_WEEK_IN_MONTH())->checkValidIntValue(self::remove($fieldValues, ChronoField::ALIGNED_DAY_OF_WEEK_IN_MONTH()), ChronoField::ALIGNED_DAY_OF_WEEK_IN_MONTH());
        $date = $this->date($y, $moy, 1)->plus(($aw - 1) * 7 + ($ad - 1), ChronoUnit::DAYS());
        if ($resolverStyle == ResolverStyle::STRICT() && $date->get(ChronoField::MONTH_OF_YEAR()) != $moy) {
            throw new DateTimeException("Strict mode rejected resolved date as it is in a different month");
        }
        return $date;
    }

    protected
    function resolveYMAD(array &$fieldValues, ResolverStyle $resolverStyle)
    {
        $y = $this->range(ChronoField::YEAR())->checkValidIntValue(self::remove($fieldValues, ChronoField::YEAR()), ChronoField::YEAR());
        if ($resolverStyle == ResolverStyle::LENIENT()) {
            $months = Math::subtractExact(self::remove($fieldValues, ChronoField::MONTH_OF_YEAR()), 1);
            $weeks = Math::subtractExact(self::remove($fieldValues, ChronoField::ALIGNED_WEEK_OF_MONTH()), 1);
            $dow = Math::subtractExact(self::remove($fieldValues, ChronoField::DAY_OF_WEEK()), 1);
            return $this->resolveAligned($this->date($y, 1, 1), $months, $weeks, $dow);
        }

        $moy = $this->range(ChronoField::MONTH_OF_YEAR())->checkValidIntValue(self::remove($fieldValues, ChronoField::MONTH_OF_YEAR()), ChronoField::MONTH_OF_YEAR());
        $aw = $this->range(ChronoField::ALIGNED_WEEK_OF_MONTH())->checkValidIntValue(self::remove($fieldValues, ChronoField::ALIGNED_WEEK_OF_MONTH()), ChronoField::ALIGNED_WEEK_OF_MONTH());
        $dow = $this->range(ChronoField::DAY_OF_WEEK())->checkValidIntValue(self::remove($fieldValues, ChronoField::DAY_OF_WEEK()), ChronoField::DAY_OF_WEEK());
        $date = $this->date($y, $moy, 1)->plus(($aw - 1) * 7, ChronoUnit::DAYS())->adjust(TemporalAdjusters::nextOrSame(DayOfWeek::of($dow)));
        if ($resolverStyle == ResolverStyle::STRICT() && $date->get(ChronoField::MONTH_OF_YEAR()) != $moy) {
            throw new DateTimeException("Strict mode rejected resolved date as it is in a different month");
        }
        return $date;
    }

    protected
    function resolveYAA(array &$fieldValues, ResolverStyle $resolverStyle)
    {
        $y = $this->range(ChronoField::YEAR())->checkValidIntValue(self::remove($fieldValues, ChronoField::YEAR()), ChronoField::YEAR());
        if ($resolverStyle == ResolverStyle::LENIENT()) {
            $weeks = Math::subtractExact(self::remove($fieldValues, ChronoField::ALIGNED_WEEK_OF_YEAR()), 1);
            $days = Math::subtractExact(self::remove($fieldValues, ChronoField::ALIGNED_DAY_OF_WEEK_IN_YEAR()), 1);
            return $this->dateYearDay($y, 1)->plus($weeks, ChronoUnit::WEEKS())->plus($days, ChronoUnit::DAYS());
        }
        $aw = $this->range(ChronoField::ALIGNED_WEEK_OF_YEAR())->checkValidIntValue(self::remove($fieldValues, ChronoField::ALIGNED_WEEK_OF_YEAR()), ChronoField::ALIGNED_WEEK_OF_YEAR());
        $ad = $this->range(ChronoField::ALIGNED_DAY_OF_WEEK_IN_YEAR())->checkValidIntValue(self::remove($fieldValues, ChronoField::ALIGNED_DAY_OF_WEEK_IN_YEAR()), ChronoField::ALIGNED_DAY_OF_WEEK_IN_YEAR());
        $date = $this->dateYearDay($y, 1)->plus(($aw - 1) * 7 + ($ad - 1), ChronoUnit::DAYS());
        if ($resolverStyle == ResolverStyle::STRICT() && $date->get(ChronoField::YEAR()) != $y) {
            throw new DateTimeException("Strict mode rejected resolved date as it is in a different year");
        }
        return $date;
    }

    protected
    function resolveYAD(array &$fieldValues, ResolverStyle $resolverStyle)
    {
        $y = $this->range(ChronoField::YEAR())->checkValidIntValue(self::remove($fieldValues, ChronoField::YEAR()), ChronoField::YEAR());
        if ($resolverStyle == ResolverStyle::LENIENT()) {
            $weeks = Math::subtractExact(self::remove($fieldValues, ChronoField::ALIGNED_WEEK_OF_YEAR()), 1);
            $dow = Math::subtractExact(self::remove($fieldValues, ChronoField::DAY_OF_WEEK()), 1);
            return $this->resolveAligned($this->dateYearDay($y, 1), 0, $weeks, $dow);
        }
        $aw = $this->range(ChronoField::ALIGNED_WEEK_OF_YEAR())->checkValidIntValue(self::remove($fieldValues, ChronoField::ALIGNED_WEEK_OF_YEAR()), ChronoField::ALIGNED_WEEK_OF_YEAR());
        $dow = $this->range(ChronoField::DAY_OF_WEEK())->checkValidIntValue(self::remove($fieldValues, ChronoField::DAY_OF_WEEK()), ChronoField::DAY_OF_WEEK());
        $date = $this->dateYearDay($y, 1)->plus(($aw - 1) * 7, ChronoUnit::DAYS())->adjust(TemporalAdjusters::nextOrSame(DayOfWeek::of($dow)));
        if ($resolverStyle == ResolverStyle::STRICT() && $date->get(ChronoField::YEAR()) != $y) {
            throw new DateTimeException("Strict mode rejected resolved date as it is in a different year");
        }
        return $date;
    }

    protected
    function resolveAligned(ChronoLocalDate $base, $months, $weeks, $dow)
    {
        $date = $base->plus($months, ChronoUnit::MONTHS())->plus($weeks, ChronoUnit::WEEKS());
        if ($dow > 7) {
            $date = $date->plus(($dow - 1) / 7, ChronoUnit::WEEKS());
            $dow = (($dow - 1) % 7) + 1;
        } else if ($dow < 1) {
            $date = $date->plus(Math::subtractExact($dow, 7) / 7, ChronoUnit::WEEKS());
            $dow = (($dow + 6) % 7) + 1;
        }
        return $date->adjust(TemporalAdjusters::nextOrSame(DayOfWeek::of((int)$dow)));
    }

    /**
     * Adds a field-value pair to the map, checking for conflicts.
     * <p>
     * If the field is not already present, then the field-value pair is added to the map.
     * If the field is already present and it has the same value as that specified, no action occurs.
     * If the field is already present and it has a different value to that specified, then
     * an exception is thrown.
     *
     * @param $fieldValues
     * @param ChronoField $field the field to add, not null
     * @param int $value the value to add, not null
     * @throws DateTimeException
     */
    protected
    static function addFieldValue(array &$fieldValues, ChronoField $field, $value)
    {
        $old = self::contains($fieldValues, $field) ? $fieldValues[$field->__toString()][1] : null;  // check first for better error message
        if ($old !== null && $old !== $value) {
            throw new DateTimeException("Conflict found: " . $field . " " . $old . " differs from " . $field . " " . $value);
        }
        $fieldValues[$field->__toString()] = [$field, $value];
    }

    //-----------------------------------------------------------------------
    /**
     * Compares this chronology to another chronology.
     * <p>
     * The comparison order first by the chronology ID string, then by any
     * additional information specific to the subclass.
     * It is "consistent with equals", as defined by {@link Comparable}.
     *
     * @implSpec
     * This implementation compares the chronology ID.
     * Subclasses must compare any additional state that they store.
     *
     * @param Chronology $other the other chronology to compare to, not null
     * @return int the comparator value, negative if less, positive if greater
     */
    public function compareTo(Chronology $other)
    {
        return strcmp($this->getId(), $other->getId());
    }

    /**
     * Checks if this chronology is equal to another chronology.
     * <p>
     * The comparison is based on the entire state of the object.
     *
     * @implSpec
     * This implementation checks the type and calls
     * {@link #compareTo(java.time.chrono.Chronology)}.
     *
     * @param mixed $obj the object to check, null returns false
     * @return bool true if this is equal to the other chronology
     */
    public function equals($obj)
    {
        if ($this == $obj) {
            return true;
        }
        if ($obj instanceof AbstractChronology) {
            return $this->compareTo($obj) === 0;
        }
        return false;
    }

    //-----------------------------------------------------------------------
    /**
     * Outputs this chronology as a {@code String}, using the chronology ID.
     *
     * @return string a string representation of this chronology, not null
     */
    public function __toString()
    {
        return $this->getId();
    }
}
