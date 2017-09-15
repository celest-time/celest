<?php declare(strict_types=1);
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
namespace Celest\Temporal;

use Celest\Duration;
use Celest\Helper\Long;

/**
 * A standard set of date periods units.
 * <p>
 * This set of units provide unit-based access to manipulate a date, time or date-time.
 * The standard set of units can be extended by implementing {@link TemporalUnit}.
 * <p>
 * These units are intended to be applicable in multiple calendar systems.
 * For example, most non-ISO calendar systems define units of years, months and days,
 * just with slightly different rules.
 * The documentation of each unit explains how it operates.
 *
 * @implSpec
 * This is a final, immutable and thread-safe enum.
 *
 * @since 1.8
 */
class ChronoUnit implements TemporalUnit
{
    public static function init() : void
    {
        self::$NANOS = new ChronoUnit("Nanos", Duration::ofNanos(1));
        self::$MICROS = new ChronoUnit("Micros", Duration::ofNanos(1000));
        self::$MILLIS = new ChronoUnit("Millis", Duration::ofNanos(1000000));
        self::$SECONDS = new ChronoUnit("Seconds", Duration::ofSeconds(1));
        self::$MINUTES = new ChronoUnit("Minutes", Duration::ofSeconds(60));
        self::$HOURS = new ChronoUnit("Hours", Duration::ofSeconds(3600));
        self::$HALF_DAYS = new ChronoUnit("HalfDays", Duration::ofSeconds(43200));
        self::$DAYS = new ChronoUnit("Days", Duration::ofSeconds(86400));
        self::$WEEKS = new ChronoUnit("Weeks", Duration::ofSeconds(7 * 86400));
        self::$MONTHS = new ChronoUnit("Months", Duration::ofSeconds(31556952 / 12));
        self::$YEARS = new ChronoUnit("Years", Duration::ofSeconds(31556952));
        self::$DECADES = new ChronoUnit("Decades", Duration::ofSeconds(31556952 * 10));
        self::$CENTURIES = new ChronoUnit("Centuries", Duration::ofSeconds(31556952 * 100));
        self::$MILLENNIA = new ChronoUnit("Millennia", Duration::ofSeconds(31556952 * 1000));
        self::$ERAS = new ChronoUnit("Eras", Duration::ofSeconds(31556952 * 1000000000));
        self::$FOREVER = new ChronoUnit("Forever", Duration::ofSeconds(Long::MAX_VALUE, 999999999));
    }

    /**
     * Unit that represents the concept of a nanosecond, the smallest supported unit of time.
     * For the ISO calendar system, it is equal to the 1,000,000,000th part of the second unit.
     * @return ChronoUnit
     */
    public static function NANOS() : ChronoUnit
    {
        return self::$NANOS;
    }

    /** @var ChronoUnit */
    private static $NANOS;

    /**
     * Unit that represents the concept of a microsecond.
     * For the ISO calendar system, it is equal to the 1,000,000th part of the second unit.
     * @return ChronoUnit
     */
    public static function MICROS() : ChronoUnit
    {
        return self::$MICROS;
    }

    /** @var ChronoUnit */
    private static $MICROS;

    /**
     * Unit that represents the concept of a millisecond.
     * For the ISO calendar system, it is equal to the 1000th part of the second unit.
     * @return ChronoUnit
     */
    public static function MILLIS() : ChronoUnit
    {
        return self::$MILLIS;
    }

    /** @var ChronoUnit */
    private static $MILLIS;

    /**
     * Unit that represents the concept of a second.
     * For the ISO calendar system, it is equal to the second in the SI system
     * of units, except around a leap-second.
     * @return ChronoUnit
     */
    public static function SECONDS() : ChronoUnit
    {
        return self::$SECONDS;
    }

    /** @var ChronoUnit */
    private static $SECONDS;

    /**
     * Unit that represents the concept of a minute.
     * For the ISO calendar system, it is equal to 60 seconds.
     * @return ChronoUnit
     */
    public static function MINUTES() : ChronoUnit
    {
        return self::$MINUTES;
    }

    /** @var ChronoUnit */
    private static $MINUTES;

    /**
     * Unit that represents the concept of an hour.
     * For the ISO calendar system, it is equal to 60 minutes.
     * @return ChronoUnit
     */
    public static function HOURS() : ChronoUnit
    {
        return self::$HOURS;
    }

    /** @var ChronoUnit */
    private static $HOURS;

    /**
     * Unit that represents the concept of half a day, as used in AM/PM.
     * For the ISO calendar system, it is equal to 12 hours.
     * @return ChronoUnit
     */
    public static function HALF_DAYS() : ChronoUnit
    {
        return self::$HALF_DAYS;
    }

    /** @var ChronoUnit */
    private static $HALF_DAYS;

    /**
     * Unit that represents the concept of a day.
     * For the ISO calendar system, it is the standard day from midnight to midnight.
     * The estimated duration of a day is {@code 24 Hours}.
     * <p>
     * When used with other calendar systems it must correspond to the day defined by
     * the rising and setting of the Sun on Earth. It is not required that days begin
     * at midnight - when converting between calendar systems, the date should be
     * equivalent at midday.
     * @return ChronoUnit
     */
    public static function DAYS() : ChronoUnit
    {
        return self::$DAYS;
    }

    /** @var ChronoUnit */
    private static $DAYS;

    /**
     * Unit that represents the concept of a week.
     * For the ISO calendar system, it is equal to 7 days.
     * <p>
     * When used with other calendar systems it must correspond to an integral number of days.
     * @return ChronoUnit
     */
    public static function WEEKS() : ChronoUnit
    {
        return self::$WEEKS;
    }

    /** @var ChronoUnit */
    private static $WEEKS;

    /**
     * Unit that represents the concept of a month.
     * For the ISO calendar system, the length of the month varies by month-of-year.
     * The estimated duration of a month is one twelfth of {@code 365.2425 Days}.
     * <p>
     * When used with other calendar systems it must correspond to an integral number of days.
     * @return ChronoUnit
     */
    public static function MONTHS() : ChronoUnit
    {
        return self::$MONTHS;
    }

    /** @var ChronoUnit */
    private static $MONTHS;

    /**
     * Unit that represents the concept of a year.
     * For the ISO calendar system, it is equal to 12 months.
     * The estimated duration of a year is {@code 365.2425 Days}.
     * <p>
     * When used with other calendar systems it must correspond to an integral number of days
     * or months roughly equal to a year defined by the passage of the Earth around the Sun.
     * @return ChronoUnit
     */
    public static function YEARS() : ChronoUnit
    {
        return self::$YEARS;
    }

    /** @var ChronoUnit */
    private static $YEARS;

    /**
     * Unit that represents the concept of a decade.
     * For the ISO calendar system, it is equal to 10 years.
     * <p>
     * When used with other calendar systems it must correspond to an integral number of days
     * and is normally an integral number of years.
     * @return ChronoUnit
     */
    public static function DECADES() : ChronoUnit
    {
        return self::$DECADES;
    }

    /** @var ChronoUnit */
    private static $DECADES;

    /**
     * Unit that represents the concept of a century.
     * For the ISO calendar system, it is equal to 100 years.
     * <p>
     * When used with other calendar systems it must correspond to an integral number of days
     * and is normally an integral number of years.
     * @return ChronoUnit
     */
    public static function CENTURIES() : ChronoUnit
    {
        return self::$CENTURIES;
    }

    /** @var ChronoUnit */
    private static $CENTURIES;

    /**
     * Unit that represents the concept of a millennium.
     * For the ISO calendar system, it is equal to 1000 years.
     * <p>
     * When used with other calendar systems it must correspond to an integral number of days
     * and is normally an integral number of years.
     * @return ChronoUnit
     */
    public static function MILLENNIA() : ChronoUnit
    {
        return self::$MILLENNIA;
    }

    /** @var ChronoUnit */
    private static $MILLENNIA;

    /**
     * Unit that represents the concept of an era.
     * The ISO calendar system doesn't have eras thus it is impossible to add
     * an era to a date or date-time.
     * The estimated duration of the era is artificially defined as {@code 1,000,000,000 Years}.
     * <p>
     * When used with other calendar systems there are no restrictions on the unit.
     * @return ChronoUnit
     */
    public static function ERAS() : ChronoUnit
    {
        return self::$ERAS;
    }

    /** @var ChronoUnit */
    private static $ERAS;

    /**
     * Artificial unit that represents the concept of forever.
     * This is primarily used with {@link TemporalField} to represent unbounded fields
     * such as the year or era.
     * The estimated duration of the era is artificially defined as the largest duration
     * supported by {@code Duration}.
     * @return ChronoUnit
     */
    public static function FOREVER() : ChronoUnit
    {
        return self::$FOREVER;
    }

    /** @var ChronoUnit */
    private static $FOREVER;

    /** @var string */
    private $name;
    /** @var Duration */
    private $duration;

    /**
     * @param string $name
     * @param Duration $estimatedDuration
     */
    private function __construct(string $name, Duration $estimatedDuration)
    {
        $this->name = $name;
        $this->duration = $estimatedDuration;
    }

//-----------------------------------------------------------------------
    /**
     * Gets the estimated duration of this unit in the ISO calendar system.
     * <p>
     * All of the units in this class have an estimated duration.
     * Days vary due to daylight saving time, while months have different lengths.
     *
     * @return Duration the estimated duration of this unit, not null
     */
    public function getDuration() : Duration
    {
        return $this->duration;
    }

    /**
     * Checks if the duration of the unit is an estimate.
     * <p>
     * All time units in this class are considered to be accurate, while all date
     * units in this class are considered to be estimated.
     * <p>
     * This definition ignores leap seconds, but considers that Days vary due to
     * daylight saving time and months have different lengths.
     *
     * @return bool true if the duration is estimated, false if accurate
     */
    public function isDurationEstimated() : bool
    {
        return $this->compareTo(self::$DAYS) >= 0;
    }

//-----------------------------------------------------------------------
    /**
     * Checks if this unit is a date unit.
     * <p>
     * All units from days to eras inclusive are date-based.
     * Time-based units and {@code FOREVER} return false.
     *
     * @return bool true if a date unit, false if a time unit
     */
    public function isDateBased() : bool
    {
        return $this->compareTo(self::$DAYS) >= 0 && $this != self::$FOREVER;
    }

    /**
     * Checks if this unit is a time unit.
     * <p>
     * All units from nanos to half-days inclusive are time-based.
     * Date-based units and {@code FOREVER} return false.
     *
     * @return bool true if a time unit, false if a date unit
     */
    public function isTimeBased() : bool
    {
        return $this->compareTo(self::$DAYS) < 0;
    }

//-----------------------------------------------------------------------
    public function isSupportedBy(Temporal $temporal) : bool
    {
        return $temporal->isUnitSupported($this);
    }

    public function addTo(Temporal $temporal, int $amount) : Temporal
    {
        return $temporal->plus($amount, $this);
    }

//-----------------------------------------------------------------------
    public function between(Temporal $temporal1Inclusive, Temporal $temporal2Exclusive) : int
    {
        return $temporal1Inclusive->until($temporal2Exclusive, $this);
    }

//-----------------------------------------------------------------------
    public function __toString() : string
    {
        return $this->name;
    }

    private function compareTo(ChronoUnit $other) : int
    {
        return $this->duration->compareTo($other->duration);
    }
}

ChronoUnit::init();
