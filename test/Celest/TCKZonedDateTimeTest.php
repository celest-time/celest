<?php
/*
 * Copyright (c) 2012, 2013, Oracle and/or its affiliates. All rights reserved.
 * DO NOT ALTER OR REMOVE COPYRIGHT NOTICES OR THIS FILE HEADER.
 *
 * This code is free software; you can redistribute it and/or modify it
 * under the terms of the GNU General Public License version 2 only, as
 * published by the Free Software Foundation.
 *
 * This code is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or
 * FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License
 * version 2 for more details ($a copy is included in the LICENSE file that
 * accompanied this code).
 *
 * You should have received $a copy of the GNU General Public License version
 * 2 awith this work; if not, write to the Free Software Foundation,
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
 * Copyright (c) 2008-2012, Stephen Colebourne & Michael Nascimento Santos
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

use Celest\Chrono\IsoChronology;
use Celest\Format\DateTimeFormatter;
use Celest\Helper\Long;
use Celest\Helper\Math;
use Celest\Temporal\AbstractTemporalAccessor;
use Celest\Temporal\ChronoField as CF;
use Celest\Temporal\ChronoUnit as CU;
use Celest\Temporal\JulianFields;
use Celest\Temporal\TemporalField;
use Celest\Temporal\TemporalQueries;
use Celest\Temporal\TemporalQuery;

class TemporalAccessor_LDT_ZoneId extends AbstractTemporalAccessor
{
    private $base;

    public function __construct(ZonedDateTime $base)
    {
        $this->base = $base;
    }

    public function isSupported(TemporalField $field) : bool
    {
        return $this->base->toLocalDateTime()->isSupported($field);
    }

    public function getLong(TemporalField $field) : int
    {
        return $this->base->toLocalDateTime()->getLong($field);
    }

    public function query(TemporalQuery $query)
    {
        if ($query == TemporalQueries::zoneId()) {
            return $this->base->getZone();
        }
        return parent::query($query);
    }
}

class TemporalAccessor_Instant_ZoneId extends AbstractTemporalAccessor
{
    private $base;

    public function __construct(ZonedDateTime $base)
    {
        $this->base = $base;
    }

    public function isSupported(TemporalField $field) : bool
    {
        return $field == CF::INSTANT_SECONDS() || $field == CF::NANO_OF_SECOND();
    }

    public function getLong(TemporalField $field) : int
    {
        return $this->base->toInstant()->getLong($field);
    }

    public function query(TemporalQuery $query)
    {
        if ($query == TemporalQueries::zoneId()) {
            return $this->base->getZone();
        }
        return parent::query($query);
    }
}

/**
 * Test ZonedDateTime::
 */
class TCKZonedDateTimeTest extends AbstractDateTimeTest
{

    private static function OFFSET_0100()
    {
        return ZoneOffset::ofHours(1);
    }

    private static function OFFSET_0200()
    {
        return ZoneOffset::ofHours(2);
    }

    private static function OFFSET_0130()
    {
        return ZoneOffset::of("+01:30");
    }

    private static function OFFSET_MAX()
    {
        return ZoneOffset::MAX();
    }

    private static function OFFSET_MIN()
    {
        return ZoneOffset::MIN();
    }

    private static function ZONE_0100()
    {
        return self::OFFSET_0100();
    }

    private static function ZONE_0200()
    {
        return self::OFFSET_0200();
    }

    private static function ZONE_M0100()
    {
        return ZoneOffset::ofHours(-1);
    }

    private static function ZONE_LONDON()
    {
        return ZoneId::of("Europe/London");
    }

    private static function ZONE_PARIS()
    {
        return ZoneId::of("Europe/Paris");
    }

    private static function TEST_LOCAL_2008_06_30_11_30_59_500()
    {
        return LocalDateTime::of(2008, 6, 30, 11, 30, 59, 500);
    }

    private static function TEST_PARIS_OVERLAP_2008_10_26_02_30()
    {
        return LocalDateTime::of(2008, 10, 26, 2, 30);
    }

    /** @var LocalDateTime */
    private $TEST_PARIS_GAP_2008_03_30_02_30;
    /** @var ZonedDateTime */
    private $TEST_DATE_TIME;
    /** @var ZonedDateTime */
    private $TEST_DATE_TIME_PARIS;

    public function setUp()
    {
        $this->TEST_DATE_TIME = ZonedDateTime::ofDateTime(self::TEST_LOCAL_2008_06_30_11_30_59_500(), self::ZONE_0100());
        $this->TEST_DATE_TIME_PARIS = ZonedDateTime::ofDateTime(self::TEST_LOCAL_2008_06_30_11_30_59_500(), self::ZONE_PARIS());
        $this->TEST_PARIS_GAP_2008_03_30_02_30 = LocalDateTime::of(2008, 3, 30, 2, 30);
    }

    //-----------------------------------------------------------------------
    protected function samples()
    {
        return [$this->TEST_DATE_TIME];
    }

    protected function validFields()
    {
        return [
            CF::NANO_OF_SECOND(),
            CF::NANO_OF_DAY(),
            CF::MICRO_OF_SECOND(),
            CF::MICRO_OF_DAY(),
            CF::MILLI_OF_SECOND(),
            CF::MILLI_OF_DAY(),
            CF::SECOND_OF_MINUTE(),
            CF::SECOND_OF_DAY(),
            CF::MINUTE_OF_HOUR(),
            CF::MINUTE_OF_DAY(),
            CF::CLOCK_HOUR_OF_AMPM(),
            CF::HOUR_OF_AMPM(),
            CF::CLOCK_HOUR_OF_DAY(),
            CF::HOUR_OF_DAY(),
            CF::AMPM_OF_DAY(),
            CF::DAY_OF_WEEK(),
            CF::ALIGNED_DAY_OF_WEEK_IN_MONTH(),
            CF::ALIGNED_DAY_OF_WEEK_IN_YEAR(),
            CF::DAY_OF_MONTH(),
            CF::DAY_OF_YEAR(),
            CF::EPOCH_DAY(),
            CF::ALIGNED_WEEK_OF_MONTH(),
            CF::ALIGNED_WEEK_OF_YEAR(),
            CF::MONTH_OF_YEAR(),
            CF::PROLEPTIC_MONTH(),
            CF::YEAR_OF_ERA(),
            CF::YEAR(),
            CF::ERA(),
            CF::OFFSET_SECONDS(),
            CF::INSTANT_SECONDS(),
            JulianFields::JULIAN_DAY(),
            JulianFields::MODIFIED_JULIAN_DAY(),
            JulianFields::RATA_DIE(),
        ];
    }

    protected function invalidFields()
    {
        return array_diff(CF::values(), $this->validFields());
    }

    //-----------------------------------------------------------------------
    // now()
    //-----------------------------------------------------------------------

    public function test_now()
    {
        $expected = ZonedDateTime::nowOf(Clock::systemDefaultZone());
        $test = ZonedDateTime::now();
        $diff = Math::abs($test->toLocalTime()->toNanoOfDay() - $expected->toLocalTime()->toNanoOfDay());
        if ($diff >= 100000000) {
            // may be date change
            $expected = ZonedDateTime::nowOf(Clock::systemDefaultZone());
            $test = ZonedDateTime::now();
            $diff = Math::abs($test->toLocalTime()->toNanoOfDay() - $expected->toLocalTime()->toNanoOfDay());
        }
        $this->assertTrue($diff < 100000000);  // less than 0.1 secs
    }

    //-----------------------------------------------------------------------
    // now(ZoneId)
    //-----------------------------------------------------------------------
    public function test_now_ZoneId_nullZoneId()
    {
        TestHelper::assertNullException($this, function () {
            ZonedDateTime::nowIn(null);
        });
    }


    public function test_now_ZoneId()
    {
        $zone = ZoneId::of("UTC+01:02:03");
        $expected = ZonedDateTime::nowOf(Clock::system($zone));
        $test = ZonedDateTime::nowIn($zone);
        for ($i = 0; $i < 100; $i++) {
            if ($expected->equals($test)) {
                $this->assertTrue(true);
                return;
            }
            $expected = ZonedDateTime::nowOf(Clock::system($zone));
            $test = ZonedDateTime::nowIn($zone);
        }
        $this->assertEquals($test, $expected);
    }

    //-----------------------------------------------------------------------
    // now(Clock)
    //-----------------------------------------------------------------------
    public function test_now_Clock_nullClock()
    {
        TestHelper::assertNullException($this, function () {
            ZonedDateTime::nowOf(null);
        });
    }

    /**
     * @group long
     */
    public function test_now_Clock_allSecsInDay_utc()
    {
        for ($i = 0; $i < (2 * 24 * 60 * 60); $i++) {
            $instant = Instant::ofEpochSecond($i)->plusNanos(123456789);
            $clock = Clock::fixed($instant, ZoneOffset::UTC());
            $test = ZonedDateTime::nowOf($clock);
            $this->assertEquals($test->getYear(), 1970);
            $this->assertEquals($test->getMonth(), Month::JANUARY());
            $this->assertEquals($test->getDayOfMonth(), ($i < 24 * 60 * 60 ? 1 : 2));
            $this->assertEquals($test->getHour(), ($i / (60 * 60)) % 24);
            $this->assertEquals($test->getMinute(), ($i / 60) % 60);
            $this->assertEquals($test->getSecond(), $i % 60);
            $this->assertEquals($test->getNano(), 123456789);
            $this->assertEquals($test->getOffset(), ZoneOffset::UTC());
            $this->assertEquals($test->getZone(), ZoneOffset::UTC());
        }
    }

    /**
     * @group long
     */
    public function test_now_Clock_allSecsInDay_zone()
    {
        $zone = ZoneId::of("Europe/London");
        for ($i = 0; $i < (2 * 24 * 60 * 60); $i++) {
            $instant = Instant::ofEpochSecond($i)->plusNanos(123456789);
            $expected = ZonedDateTime::ofInstant($instant, $zone);
            $clock = Clock::fixed($expected->toInstant(), $zone);
            $test = ZonedDateTime::nowOf($clock);
            $this->assertEquals($test, $expected);
        }
    }


    /**
     * @group long
     */
    public function test_now_Clock_allSecsInDay_beforeEpoch()
    {
        $expected = LocalTime::MIDNIGHT()->plusNanos(123456789);
        for ($i = -1; $i >= -(24 * 60 * 60); $i--) {
            $instant = Instant::ofEpochSecond($i)->plusNanos(123456789);
            $clock = Clock::fixed($instant, ZoneOffset::UTC());
            $test = ZonedDateTime::nowOf($clock);
            $this->assertEquals($test->getYear(), 1969);
            $this->assertEquals($test->getMonth(), Month::DECEMBER());
            $this->assertEquals($test->getDayOfMonth(), 31);
            $expected = $expected->minusSeconds(1);
            $this->assertEquals($test->toLocalTime(), $expected);
            $this->assertEquals($test->getOffset(), ZoneOffset::UTC());
            $this->assertEquals($test->getZone(), ZoneOffset::UTC());
        }
    }


    public function test_now_Clock_offsets()
    {
        $base = ZonedDateTime::ofDateTime(LocalDateTime::of(1970, 1, 1, 12, 0), ZoneOffset::UTC());
        for ($i = -9; $i < 15; $i++) {
            $offset = ZoneOffset::ofHours($i);
            $clock = Clock::fixed($base->toInstant(), $offset);
            $test = ZonedDateTime::nowOf($clock);
            $this->assertEquals($test->getHour(), (12 + $i) % 24);
            $this->assertEquals($test->getMinute(), 0);
            $this->assertEquals($test->getSecond(), 0);
            $this->assertEquals($test->getNano(), 0);
            $this->assertEquals($test->getOffset(), $offset);
            $this->assertEquals($test->getZone(), $offset);
        }
    }

    //-----------------------------------------------------------------------
    // $this->dateTime factories
    //-----------------------------------------------------------------------
    function check(ZonedDateTime $test, $y, $m, $d, $h, $min, $s, $n, ZoneOffset $offset, ZoneId $zone)
    {
        $this->assertEquals($test->getYear(), $y);
        $this->assertEquals($test->getMonth()->getValue(), $m);
        $this->assertEquals($test->getDayOfMonth(), $d);
        $this->assertEquals($test->getHour(), $h);
        $this->assertEquals($test->getMinute(), $min);
        $this->assertEquals($test->getSecond(), $s);
        $this->assertEquals($test->getNano(), $n);
        $this->assertEquals($test->getOffset(), $offset);
        $this->assertEquals($test->getZone(), $zone);
    }

//-----------------------------------------------------------------------
// of(LocalDate, LocalTime, ZoneId)
//-----------------------------------------------------------------------

    public function test_factory_of_LocalDateLocalTime()
    {
        $test = ZonedDateTime::ofDateAndTime(LocalDate::of(2008, 6, 30), LocalTime::of(11, 30, 10, 500), self::ZONE_PARIS());
        $this->check($test, 2008, 6, 30, 11, 30, 10, 500, self::OFFSET_0200(), self::ZONE_PARIS());
    }


    public function test_factory_of_LocalDateLocalTime_inGap()
    {
        $test = ZonedDateTime::ofDateAndTime($this->TEST_PARIS_GAP_2008_03_30_02_30->toLocalDate(), $this->TEST_PARIS_GAP_2008_03_30_02_30->toLocalTime(), self::ZONE_PARIS());
        $this->check($test, 2008, 3, 30, 3, 30, 0, 0, self::OFFSET_0200(), self::ZONE_PARIS());  // one $hour later in summer $offset
    }


    public function test_factory_of_LocalDateLocalTime_inOverlap()
    {
        $test = ZonedDateTime::ofDateAndTime(self::TEST_PARIS_OVERLAP_2008_10_26_02_30()->toLocalDate(), self::TEST_PARIS_OVERLAP_2008_10_26_02_30()->toLocalTime(), self::ZONE_PARIS());
        $this->check($test, 2008, 10, 26, 2, 30, 0, 0, self::OFFSET_0200(), self::ZONE_PARIS());  // same time in summer $offset
    }

    public function test_factory_of_LocalDateLocalTime_nullDate()
    {
        TestHelper::assertNullException($this, function () {
            ZonedDateTime::ofDateAndTime(null, LocalTime::of(11, 30, 10, 500), self::ZONE_PARIS());
        });
    }

    public function test_factory_of_LocalDateLocalTime_nullTime()
    {
        TestHelper::assertNullException($this, function () {
            ZonedDateTime::ofDateAndTime(LocalDate::of(2008, 6, 30), null, self::ZONE_PARIS());
        });
    }

    public function test_factory_of_LocalDateLocalTime_nullZone()
    {
        TestHelper::assertNullException($this, function () {
            ZonedDateTime::ofDateAndTime(LocalDate::of(2008, 6, 30), LocalTime::of(11, 30, 10, 500), null);
        });
    }

    //-----------------------------------------------------------------------
    // of(LocalDateTime, ZoneId)
    //-----------------------------------------------------------------------

    public function test_factory_of_LocalDateTime()
    {
        $base = LocalDateTime::of(2008, 6, 30, 11, 30, 10, 500);
        $test = ZonedDateTime::ofDateTime($base, self::ZONE_PARIS());
        $this->check($test, 2008, 6, 30, 11, 30, 10, 500, self::OFFSET_0200(), self::ZONE_PARIS());
    }


    public function test_factory_of_LocalDateTime_inGap()
    {
        $test = ZonedDateTime::ofDateTime($this->TEST_PARIS_GAP_2008_03_30_02_30, self::ZONE_PARIS());
        $this->check($test, 2008, 3, 30, 3, 30, 0, 0, self::OFFSET_0200(), self::ZONE_PARIS());  // one $hour later in summer $offset
    }


    public function test_factory_of_LocalDateTime_inOverlap()
    {
        $test = ZonedDateTime::ofDateTime(self::TEST_PARIS_OVERLAP_2008_10_26_02_30(), self::ZONE_PARIS());
        $this->check($test, 2008, 10, 26, 2, 30, 0, 0, self::OFFSET_0200(), self::ZONE_PARIS());  // same time in summer $offset
    }

    public function test_factory_of_LocalDateTime_nullDateTime()
    {
        TestHelper::assertNullException($this, function () {
            ZonedDateTime::ofDateTime(null, self::ZONE_PARIS());
        });
    }

    public function test_factory_of_LocalDateTime_nullZone()
    {
        TestHelper::assertNullException($this, function () {
            $base = LocalDateTime::of(2008, 6, 30, 11, 30, 10, 500);
            ZonedDateTime::ofDateTime($base, null);
        });

    }

    //-----------------------------------------------------------------------
    // of(int..., ZoneId)
    //-----------------------------------------------------------------------

    public function test_factory_of_ints()
    {
        $test = ZonedDateTime::of(2008, 6, 30, 11, 30, 10, 500, self::ZONE_PARIS());
        $this->check($test, 2008, 6, 30, 11, 30, 10, 500, self::OFFSET_0200(), self::ZONE_PARIS());
    }

    //-----------------------------------------------------------------------
    // ofInstant(Instant, ZoneId)
    //-----------------------------------------------------------------------

    public function test_factory_ofInstant_Instant_ZR()
    {
        $instant = LocalDateTime::of(2008, 6, 30, 11, 30, 10, 35)->toInstant(self::OFFSET_0200());
        $test = ZonedDateTime::ofInstant($instant, self::ZONE_PARIS());
        $this->check($test, 2008, 6, 30, 11, 30, 10, 35, self::OFFSET_0200(), self::ZONE_PARIS());
    }


    public function test_factory_ofInstant_Instant_ZO()
    {
        $instant = LocalDateTime::of(2008, 6, 30, 11, 30, 10, 45)->toInstant(self::OFFSET_0200());
        $test = ZonedDateTime::ofInstant($instant, self::OFFSET_0200());
        $this->check($test, 2008, 6, 30, 11, 30, 10, 45, self::OFFSET_0200(), self::OFFSET_0200());
    }


    public function test_factory_ofInstant_Instant_inGap()
    {
        $instant = $this->TEST_PARIS_GAP_2008_03_30_02_30->toInstant(self::OFFSET_0100());
        $test = ZonedDateTime::ofInstant($instant, self::ZONE_PARIS());
        $this->check($test, 2008, 3, 30, 3, 30, 0, 0, self::OFFSET_0200(), self::ZONE_PARIS());  // one $hour later in summer $offset
    }


    public function test_factory_ofInstant_Instant_inOverlap_earlier()
    {
        $instant = self::TEST_PARIS_OVERLAP_2008_10_26_02_30()->toInstant(self::OFFSET_0200());
        $test = ZonedDateTime::ofInstant($instant, self::ZONE_PARIS());
        $this->check($test, 2008, 10, 26, 2, 30, 0, 0, self::OFFSET_0200(), self::ZONE_PARIS());  // same time and $offset
    }


    public function test_factory_ofInstant_Instant_inOverlap_later()
    {
        $instant = self::TEST_PARIS_OVERLAP_2008_10_26_02_30()->toInstant(self::OFFSET_0100());
        $test = ZonedDateTime::ofInstant($instant, self::ZONE_PARIS());
        $this->check($test, 2008, 10, 26, 2, 30, 0, 0, self::OFFSET_0100(), self::ZONE_PARIS());  // same time and $offset
    }


    public function test_factory_ofInstant_Instant_invalidOffset()
    {
        $instant = LocalDateTime::of(2008, 6, 30, 11, 30, 10, 500)->toInstant(self::OFFSET_0130());
        $test = ZonedDateTime::ofInstant($instant, self::ZONE_PARIS());
        $this->check($test, 2008, 6, 30, 12, 0, 10, 500, self::OFFSET_0200(), self::ZONE_PARIS());  // corrected $offset, thus altered time
    }


    /**
     * @group long
     */
    public function test_factory_ofInstant_allSecsInDay()
    {
        for ($i = 0; $i < (24 * 60 * 60); $i++) {
            $instant = Instant::ofEpochSecond($i);
            $test = ZonedDateTime::ofInstant($instant, self::OFFSET_0100());
            $this->assertEquals($test->getYear(), 1970);
            $this->assertEquals($test->getMonth(), Month::JANUARY());
            $this->assertEquals($test->getDayOfMonth(), 1 + ($i >= 23 * 60 * 60 ? 1 : 0));
            $this->assertEquals($test->getHour(), (($i / (60 * 60)) + 1) % 24);
            $this->assertEquals($test->getMinute(), ($i / 60) % 60);
            $this->assertEquals($test->getSecond(), $i % 60);
        }
    }


    /**
     * @group long
     */
    public function test_factory_ofInstant_allDaysInCycle()
    {
        // sanity $this->check using different algorithm
        $expected = LocalDateTime::of(1970, 1, 1, 0, 0, 0, 0)->atZone(ZoneOffset::UTC());
        for ($i = 0; $i < 146097; $i++) {
            $instant = Instant::ofEpochSecond($i * 24 * 60 * 60);
            $test = ZonedDateTime::ofInstant($instant, ZoneOffset::UTC());
            $this->assertEquals($test, $expected);
            $expected = $expected->plusDays(1);
        }
    }


    public function test_factory_ofInstant_minWithMinOffset()
    {
        $days_0000_to_1970 = (146097 * 5) - (30 * 365 + 7);
        $year = Year::MIN_VALUE;
        $days = ($year * 365 + (\intdiv($year, 4) - \intdiv($year, 100) + \intdiv($year, 400))) - $days_0000_to_1970;
        $instant = Instant::ofEpochSecond($days * 24 * 60 * 60 - self::OFFSET_MIN()->getTotalSeconds());
        $test = ZonedDateTime::ofInstant($instant, self::OFFSET_MIN());
        $this->assertEquals($test->getYear(), Year::MIN_VALUE);
        $this->assertEquals($test->getMonth()->getValue(), 1);
        $this->assertEquals($test->getDayOfMonth(), 1);
        $this->assertEquals($test->getOffset(), self::OFFSET_MIN());
        $this->assertEquals($test->getHour(), 0);
        $this->assertEquals($test->getMinute(), 0);
        $this->assertEquals($test->getSecond(), 0);
        $this->assertEquals($test->getNano(), 0);
    }


    public function test_factory_ofInstant_minWithMaxOffset()
    {
        $days_0000_to_1970 = (146097 * 5) - (30 * 365 + 7);
        $year = Year::MIN_VALUE;
        $days = ($year * 365 + (\intdiv($year, 4) - \intdiv($year, 100) + \intdiv($year, 400))) - $days_0000_to_1970;
        $instant = Instant::ofEpochSecond($days * 24 * 60 * 60 - self::OFFSET_MAX()->getTotalSeconds());
        $test = ZonedDateTime::ofInstant($instant, self::OFFSET_MAX());
        $this->assertEquals($test->getYear(), Year::MIN_VALUE);
        $this->assertEquals($test->getMonth()->getValue(), 1);
        $this->assertEquals($test->getDayOfMonth(), 1);
        $this->assertEquals($test->getOffset(), self::OFFSET_MAX());
        $this->assertEquals($test->getHour(), 0);
        $this->assertEquals($test->getMinute(), 0);
        $this->assertEquals($test->getSecond(), 0);
        $this->assertEquals($test->getNano(), 0);
    }


    public function test_factory_ofInstant_maxWithMinOffset()
    {
        $days_0000_to_1970 = (146097 * 5) - (30 * 365 + 7);
        $year = Year::MAX_VALUE;
        $days = ($year * 365 + (\intdiv($year, 4) - \intdiv($year, 100) + \intdiv($year, 400))) + 365 - $days_0000_to_1970;
        $instant = Instant::ofEpochSecond(($days + 1) * 24 * 60 * 60 - 1 - self::OFFSET_MIN()->getTotalSeconds());
        $test = ZonedDateTime::ofInstant($instant, self::OFFSET_MIN());
        $this->assertEquals($test->getYear(), Year::MAX_VALUE);
        $this->assertEquals($test->getMonth()->getValue(), 12);
        $this->assertEquals($test->getDayOfMonth(), 31);
        $this->assertEquals($test->getOffset(), self::OFFSET_MIN());
        $this->assertEquals($test->getHour(), 23);
        $this->assertEquals($test->getMinute(), 59);
        $this->assertEquals($test->getSecond(), 59);
        $this->assertEquals($test->getNano(), 0);
    }


    public function test_factory_ofInstant_maxWithMaxOffset()
    {
        $days_0000_to_1970 = (146097 * 5) - (30 * 365 + 7);
        $year = Year::MAX_VALUE;
        $days = ($year * 365 + (\intdiv($year, 4) - \intdiv($year, 100) + \intdiv($year, 400))) + 365 - $days_0000_to_1970;
        $instant = Instant::ofEpochSecond(($days + 1) * 24 * 60 * 60 - 1 - self::OFFSET_MAX()->getTotalSeconds());
        $test = ZonedDateTime::ofInstant($instant, self::OFFSET_MAX());
        $this->assertEquals($test->getYear(), Year::MAX_VALUE);
        $this->assertEquals($test->getMonth()->getValue(), 12);
        $this->assertEquals($test->getDayOfMonth(), 31);
        $this->assertEquals($test->getOffset(), self::OFFSET_MAX());
        $this->assertEquals($test->getHour(), 23);
        $this->assertEquals($test->getMinute(), 59);
        $this->assertEquals($test->getSecond(), 59);
        $this->assertEquals($test->getNano(), 0);
    }

    //-----------------------------------------------------------------------
    /**
     * @expectedException \Celest\DateTimeException
     */
    public function test_factory_ofInstant_maxInstantWithMaxOffset()
    {
        $instant = Instant::ofEpochSecond(Long::MAX_VALUE);
        ZonedDateTime::ofInstant($instant, self::OFFSET_MAX());
    }

    /**
     * @expectedException \Celest\DateTimeException
     */
    public function test_factory_ofInstant_maxInstantWithMinOffset()
    {
        $instant = Instant::ofEpochSecond(Long::MAX_VALUE);
        ZonedDateTime::ofInstant($instant, self::OFFSET_MIN());
    }

    /**
     * @expectedException \Celest\DateTimeException
     */
    public function test_factory_ofInstant_tooBig()
    {
        $days_0000_to_1970 = (146097 * 5) - (30 * 365 + 7);
        $year = Year::MAX_VALUE + 1;
        $days = ($year * 365 + ($year / 4 - $year / 100 + $year / 400)) - $days_0000_to_1970;
        $instant = Instant::ofEpochSecond($days * 24 * 60 * 60);
        ZonedDateTime::ofInstant($instant, ZoneOffset::UTC());
    }

    /**
     * @expectedException \Celest\DateTimeException
     */
    public function test_factory_ofInstant_tooLow()
    {
        $days_0000_to_1970 = (146097 * 5) - (30 * 365 + 7);
        $year = Year::MIN_VALUE - 1;
        $days = ($year * 365 + ($year / 4 - $year / 100 + $year / 400)) - $days_0000_to_1970;
        $instant = Instant::ofEpochSecond($days * 24 * 60 * 60);
        ZonedDateTime::ofInstant($instant, ZoneOffset::UTC());
    }

    public function test_factory_ofInstant_Instant_nullInstant()
    {
        TestHelper::assertNullException($this, function () {
            ZonedDateTime::ofInstant(null, self::ZONE_0100());
        });
    }

    public function test_factory_ofInstant_Instant_nullZone()
    {
        TestHelper::assertNullException($this, function () {
            ZonedDateTime::ofInstant(Instant::EPOCH(), null);
        });
    }

    //-----------------------------------------------------------------------
    // ofStrict(LocalDateTime, ZoneId, ZoneOffset)
    //-----------------------------------------------------------------------

    public function test_factory_ofStrict_LDT_ZI_ZO()
    {
        $normal = LocalDateTime::of(2008, 6, 30, 11, 30, 10, 500);
        $test = ZonedDateTime::ofStrict($normal, self::OFFSET_0200(), self::ZONE_PARIS());
        $this->check($test, 2008, 6, 30, 11, 30, 10, 500, self::OFFSET_0200(), self::ZONE_PARIS());
    }

    /**
     * @expectedException \Celest\DateTimeException
     */
    public function test_factory_ofStrict_LDT_ZI_ZO_inGap()
    {
        try {
            ZonedDateTime::ofStrict($this->TEST_PARIS_GAP_2008_03_30_02_30, self::OFFSET_0100(), self::ZONE_PARIS());
        } catch (DateTimeException $ex) {
            $this->assertContains(" gap", $ex->getMessage(), true);
            throw $ex;
        }
    }

    /**
     * @expectedException \Celest\DateTimeException
     */
    public function test_factory_ofStrict_LDT_ZI_ZO_inOverlap_invalidOfset()
    {
        try {
            ZonedDateTime::ofStrict(self::TEST_PARIS_OVERLAP_2008_10_26_02_30(), self::OFFSET_0130(), self::ZONE_PARIS());
        } catch (DateTimeException $ex) {
            $this->assertContains(" is not valid for ", $ex->getMessage(), true);
            throw $ex;
        }
    }

    /**
     * @expectedException \Celest\DateTimeException
     */
    public function test_factory_ofStrict_LDT_ZI_ZO_invalidOffset()
    {
        try {
            ZonedDateTime::ofStrict(self::TEST_LOCAL_2008_06_30_11_30_59_500(), self::OFFSET_0130(), self::ZONE_PARIS());
        } catch (DateTimeException $ex) {
            $this->assertContains(" is not valid for ", $ex->getMessage(), true);
            throw $ex;
        }
    }

    public function test_factory_ofStrict_LDT_ZI_ZO_nullLDT()
    {
        TestHelper::assertNullException($this, function () {
            ZonedDateTime::ofStrict(null, self::OFFSET_0100(), self::ZONE_PARIS());
        });
    }

    public function test_factory_ofStrict_LDT_ZI_ZO_nullZO()
    {
        TestHelper::assertNullException($this, function () {
            ZonedDateTime::ofStrict(self::TEST_LOCAL_2008_06_30_11_30_59_500(), null, self::ZONE_PARIS());
        });
    }

    public function test_factory_ofStrict_LDT_ZI_ZO_nullZI()
    {
        TestHelper::assertNullException($this, function () {
            ZonedDateTime::ofStrict(self::TEST_LOCAL_2008_06_30_11_30_59_500(), self::OFFSET_0100(), null);
        });
    }

    public function factory_ofNative_data()
    {
        return [
            [new \DateTime('now')],
            [new \DateTime('now', new \DateTimeZone('Europe/Berlin'))],
        ];
    }

    /**
     * @dataProvider factory_ofNative_data
     */
    public function test_factory_ofNative(\DateTimeInterface $dateTime)
    {
        $dt = ZonedDateTime::ofNativeDateTime($dateTime);
        $this->markTestIncomplete('Test checks missing');
    }

    //-----------------------------------------------------------------------
    // from(TemporalAccessor)
    //-----------------------------------------------------------------------

    public function test_factory_from_TemporalAccessor_ZDT()
    {
        $this->assertEquals(ZonedDateTime::from($this->TEST_DATE_TIME_PARIS), $this->TEST_DATE_TIME_PARIS);
    }


    public function test_factory_from_TemporalAccessor_LDT_ZoneId()
    {
        $this->assertEquals(ZonedDateTime::from(new TemporalAccessor_LDT_ZoneId($this->TEST_DATE_TIME_PARIS)), $this->TEST_DATE_TIME_PARIS);
    }


    public function test_factory_from_TemporalAccessor_Instant_ZoneId()
    {
        $this->assertEquals(ZonedDateTime::from(new TemporalAccessor_Instant_ZoneId($this->TEST_DATE_TIME_PARIS)), $this->TEST_DATE_TIME_PARIS);
    }

    /**
     * @expectedException \Celest\DateTimeException
     */
    public function test_factory_from_TemporalAccessor_invalid_noDerive()
    {
        ZonedDateTime::from(LocalTime::of(12, 30));
    }

    public function test_factory_from_TemporalAccessor_null()
    {
        TestHelper::assertNullException($this, function () {
            ZonedDateTime::from(null);
        });
    }

    //-----------------------------------------------------------------------
    // parse()
    //-----------------------------------------------------------------------
    /**
     * @dataProvider provider_sampleToString
     */
    public function test_parse($y, $month, $d, $h, $m, $s, $n, $zoneId, $text)
    {
        $z = ZonedDateTime::parse($text);
        $this->assertEquals($z->getYear(), $y);
        $this->assertEquals($z->getMonth()->getValue(), $month);
        $this->assertEquals($z->getDayOfMonth(), $d);
        $this->assertEquals($z->getHour(), $h);
        $this->assertEquals($z->getMinute(), $m);
        $this->assertEquals($z->getSecond(), $s);
        $this->assertEquals($z->getNano(), $n);
        $this->assertEquals($z->getZone()->getId(), $zoneId);
    }

    function data_parseAdditional()
    {
        return [
            ["2012-06-30T12:30:40Z[GMT]", 2012, 6, 30, 12, 30, 40, 0, "GMT"],
            ["2012-06-30T12:30:40Z[UT]", 2012, 6, 30, 12, 30, 40, 0, "UT"],
            ["2012-06-30T12:30:40Z[UTC]", 2012, 6, 30, 12, 30, 40, 0, "UTC"],
            ["2012-06-30T12:30:40+01:00[Z]", 2012, 6, 30, 12, 30, 40, 0, "Z"],
            ["2012-06-30T12:30:40+01:00[+01:00]", 2012, 6, 30, 12, 30, 40, 0, "+01:00"],
            ["2012-06-30T12:30:40+01:00[GMT+01:00]", 2012, 6, 30, 12, 30, 40, 0, "GMT+01:00"],
            ["2012-06-30T12:30:40+01:00[UT+01:00]", 2012, 6, 30, 12, 30, 40, 0, "UT+01:00"],
            ["2012-06-30T12:30:40+01:00[UTC+01:00]", 2012, 6, 30, 12, 30, 40, 0, "UTC+01:00"],
            ["2012-06-30T12:30:40-01:00[-01:00]", 2012, 6, 30, 12, 30, 40, 0, "-01:00"],
            ["2012-06-30T12:30:40-01:00[GMT-01:00]", 2012, 6, 30, 12, 30, 40, 0, "GMT-01:00"],
            ["2012-06-30T12:30:40-01:00[UT-01:00]", 2012, 6, 30, 12, 30, 40, 0, "UT-01:00"],
            ["2012-06-30T12:30:40-01:00[UTC-01:00]", 2012, 6, 30, 12, 30, 40, 0, "UTC-01:00"],
            ["2012-06-30T12:30:40+01:00[Europe/London]", 2012, 6, 30, 12, 30, 40, 0, "Europe/London"],
        ];
    }

    /**
     * @dataProvider data_parseAdditional
     */
    public function test_parseAdditional($text, $y, $month, $d, $h, $m, $s, $n, $zoneId)
    {
        $z = ZonedDateTime::parse($text);
        $this->assertEquals($z->getYear(), $y);
        $this->assertEquals($z->getMonth()->getValue(), $month);
        $this->assertEquals($z->getDayOfMonth(), $d);
        $this->assertEquals($z->getHour(), $h);
        $this->assertEquals($z->getMinute(), $m);
        $this->assertEquals($z->getSecond(), $s);
        $this->assertEquals($z->getNano(), $n);
        $this->assertEquals($z->getZone()->getId(), $zoneId);
    }

    /**
     * @expectedException \Celest\DateTimeParseException
     */
    public function test_factory_parse_illegalValue()
    {
        ZonedDateTime::parse("2008-06-32T11:15+01:00[Europe/Paris]");
    }

    /**
     * @expectedException \Celest\DateTimeParseException
     */
    public function test_factory_parse_invalidValue()
    {
        ZonedDateTime::parse("2008-06-31T11:15+01:00[Europe/Paris]");
    }

    public function test_factory_parse_nullText()
    {
        TestHelper::assertNullException($this, function () {
            ZonedDateTime::parse(null);
        });
    }

    //-----------------------------------------------------------------------
    // parse(DateTimeFormatter)
    //-----------------------------------------------------------------------

    public function test_factory_parse_formatter()
    {
        $f = DateTimeFormatter::ofPattern("y M d H m s VV");
        $test = ZonedDateTime::parseWith("2010 12 3 11 30 0 Europe/London", $f);
        $this->assertEquals($test, ZonedDateTime::ofDateTime(LocalDateTime::of(2010, 12, 3, 11, 30), ZoneId::of("Europe/London")));
    }

    public function test_factory_parse_formatter_nullText()
    {
        TestHelper::assertNullException($this, function () {
            $f = DateTimeFormatter::ofPattern("y M d H m s");
            ZonedDateTime::parseWith(null, $f);
        });

    }

    public function test_factory_parse_formatter_nullFormatter()
    {
        TestHelper::assertNullException($this, function () {
            ZonedDateTime::parseWith("ANY", null);
        });
    }

    //-----------------------------------------------------------------------
    // basics
    //-----------------------------------------------------------------------
    function provider_sampleTimes()
    {
        return [
            [2008, 6, 30, 11, 30, 20, 500, self::ZONE_0100()],
            [2008, 6, 30, 11, 0, 0, 0, self::ZONE_0100()],
            [2008, 6, 30, 11, 30, 20, 500, self::ZONE_PARIS()],
            [2008, 6, 30, 11, 0, 0, 0, self::ZONE_PARIS()],
            [2008, 6, 30, 23, 59, 59, 999999999, self::ZONE_0100()],
            [-1, 1, 1, 0, 0, 0, 0, self::ZONE_0100()],
        ];
    }

    /**
     * @dataProvider provider_sampleTimes
     */
    public function test_get($y, $o, $d, $h, $m, $s, $n, ZoneId $zone)
    {
        $localDate = LocalDate::of($y, $o, $d);
        $localTime = LocalTime::of($h, $m, $s, $n);
        $localDateTime = LocalDateTime::ofDateAndTime($localDate, $localTime);
        $offset = $zone->getRules()->getOffsetDateTime($localDateTime);
        $a = ZonedDateTime::ofDateTime($localDateTime, $zone);

        $this->assertEquals($a->getYear(), $localDate->getYear());
        $this->assertEquals($a->getMonth(), $localDate->getMonth());
        $this->assertEquals($a->getDayOfMonth(), $localDate->getDayOfMonth());
        $this->assertEquals($a->getDayOfYear(), $localDate->getDayOfYear());
        $this->assertEquals($a->getDayOfWeek(), $localDate->getDayOfWeek());

        $this->assertEquals($a->getHour(), $localTime->getHour());
        $this->assertEquals($a->getMinute(), $localTime->getMinute());
        $this->assertEquals($a->getSecond(), $localTime->getSecond());
        $this->assertEquals($a->getNano(), $localTime->getNano());

        $this->assertEquals($a->toLocalDate(), $localDate);
        $this->assertEquals($a->toLocalTime(), $localTime);
        $this->assertEquals($a->toLocalDateTime(), $localDateTime);
        if ($zone instanceof ZoneOffset) {
            $this->assertEquals($a->__toString(), $localDateTime->__toString() . $offset->__toString());
        } else {
            $this->assertEquals($a->__toString(), $localDateTime->__toString() . $offset->__toString() . "[" . $zone->__toString() . "]");
        }
    }

    //-----------------------------------------------------------------------
    // isSupported(TemporalField)
    //-----------------------------------------------------------------------

    public function test_isSupported_TemporalField()
    {
        // TODO $this->assertEquals($this->TEST_DATE_TIME->isSupported(null), false);
        $this->assertEquals($this->TEST_DATE_TIME->isSupported(CF::NANO_OF_SECOND()), true);
        $this->assertEquals($this->TEST_DATE_TIME->isSupported(CF::NANO_OF_DAY()), true);
        $this->assertEquals($this->TEST_DATE_TIME->isSupported(CF::MICRO_OF_SECOND()), true);
        $this->assertEquals($this->TEST_DATE_TIME->isSupported(CF::MICRO_OF_DAY()), true);
        $this->assertEquals($this->TEST_DATE_TIME->isSupported(CF::MILLI_OF_SECOND()), true);
        $this->assertEquals($this->TEST_DATE_TIME->isSupported(CF::MILLI_OF_DAY()), true);
        $this->assertEquals($this->TEST_DATE_TIME->isSupported(CF::SECOND_OF_MINUTE()), true);
        $this->assertEquals($this->TEST_DATE_TIME->isSupported(CF::SECOND_OF_DAY()), true);
        $this->assertEquals($this->TEST_DATE_TIME->isSupported(CF::MINUTE_OF_HOUR()), true);
        $this->assertEquals($this->TEST_DATE_TIME->isSupported(CF::MINUTE_OF_DAY()), true);
        $this->assertEquals($this->TEST_DATE_TIME->isSupported(CF::HOUR_OF_AMPM()), true);
        $this->assertEquals($this->TEST_DATE_TIME->isSupported(CF::CLOCK_HOUR_OF_AMPM()), true);
        $this->assertEquals($this->TEST_DATE_TIME->isSupported(CF::HOUR_OF_DAY()), true);
        $this->assertEquals($this->TEST_DATE_TIME->isSupported(CF::CLOCK_HOUR_OF_DAY()), true);
        $this->assertEquals($this->TEST_DATE_TIME->isSupported(CF::AMPM_OF_DAY()), true);
        $this->assertEquals($this->TEST_DATE_TIME->isSupported(CF::DAY_OF_WEEK()), true);
        $this->assertEquals($this->TEST_DATE_TIME->isSupported(CF::ALIGNED_DAY_OF_WEEK_IN_MONTH()), true);
        $this->assertEquals($this->TEST_DATE_TIME->isSupported(CF::ALIGNED_DAY_OF_WEEK_IN_YEAR()), true);
        $this->assertEquals($this->TEST_DATE_TIME->isSupported(CF::DAY_OF_MONTH()), true);
        $this->assertEquals($this->TEST_DATE_TIME->isSupported(CF::DAY_OF_YEAR()), true);
        $this->assertEquals($this->TEST_DATE_TIME->isSupported(CF::EPOCH_DAY()), true);
        $this->assertEquals($this->TEST_DATE_TIME->isSupported(CF::ALIGNED_WEEK_OF_MONTH()), true);
        $this->assertEquals($this->TEST_DATE_TIME->isSupported(CF::ALIGNED_WEEK_OF_YEAR()), true);
        $this->assertEquals($this->TEST_DATE_TIME->isSupported(CF::MONTH_OF_YEAR()), true);
        $this->assertEquals($this->TEST_DATE_TIME->isSupported(CF::PROLEPTIC_MONTH()), true);
        $this->assertEquals($this->TEST_DATE_TIME->isSupported(CF::YEAR()), true);
        $this->assertEquals($this->TEST_DATE_TIME->isSupported(CF::YEAR_OF_ERA()), true);
        $this->assertEquals($this->TEST_DATE_TIME->isSupported(CF::ERA()), true);
        $this->assertEquals($this->TEST_DATE_TIME->isSupported(CF::INSTANT_SECONDS()), true);
        $this->assertEquals($this->TEST_DATE_TIME->isSupported(CF::OFFSET_SECONDS()), true);
    }

    //-----------------------------------------------------------------------
    // isSupported(TemporalUnit)
    //-----------------------------------------------------------------------

    public function test_isSupported_TemporalUnit()
    {
        // TODO $this->assertEquals($this->TEST_DATE_TIME->isUnitSupported(null), false);
        $this->assertEquals($this->TEST_DATE_TIME->isUnitSupported(CU::NANOS()), true);
        $this->assertEquals($this->TEST_DATE_TIME->isUnitSupported(CU::MICROS()), true);
        $this->assertEquals($this->TEST_DATE_TIME->isUnitSupported(CU::MILLIS()), true);
        $this->assertEquals($this->TEST_DATE_TIME->isUnitSupported(CU::SECONDS()), true);
        $this->assertEquals($this->TEST_DATE_TIME->isUnitSupported(CU::MINUTES()), true);
        $this->assertEquals($this->TEST_DATE_TIME->isUnitSupported(CU::HOURS()), true);
        $this->assertEquals($this->TEST_DATE_TIME->isUnitSupported(CU::HALF_DAYS()), true);
        $this->assertEquals($this->TEST_DATE_TIME->isUnitSupported(CU::DAYS()), true);
        $this->assertEquals($this->TEST_DATE_TIME->isUnitSupported(CU::WEEKS()), true);
        $this->assertEquals($this->TEST_DATE_TIME->isUnitSupported(CU::MONTHS()), true);
        $this->assertEquals($this->TEST_DATE_TIME->isUnitSupported(CU::YEARS()), true);
        $this->assertEquals($this->TEST_DATE_TIME->isUnitSupported(CU::DECADES()), true);
        $this->assertEquals($this->TEST_DATE_TIME->isUnitSupported(CU::CENTURIES()), true);
        $this->assertEquals($this->TEST_DATE_TIME->isUnitSupported(CU::MILLENNIA()), true);
        $this->assertEquals($this->TEST_DATE_TIME->isUnitSupported(CU::ERAS()), true);
        $this->assertEquals($this->TEST_DATE_TIME->isUnitSupported(CU::FOREVER()), false);
    }

    //-----------------------------------------------------------------------
    // get(TemporalField)
    //-----------------------------------------------------------------------

    public function test_get_TemporalField()
    {
        $test = ZonedDateTime::ofDateTime(LocalDateTime::of(2008, 6, 30, 12, 30, 40, 987654321), self::ZONE_0100());
        $this->assertEquals($test->get(CF::YEAR()), 2008);
        $this->assertEquals($test->get(CF::MONTH_OF_YEAR()), 6);
        $this->assertEquals($test->get(CF::DAY_OF_MONTH()), 30);
        $this->assertEquals($test->get(CF::DAY_OF_WEEK()), 1);
        $this->assertEquals($test->get(CF::DAY_OF_YEAR()), 182);

        $this->assertEquals($test->get(CF::HOUR_OF_DAY()), 12);
        $this->assertEquals($test->get(CF::MINUTE_OF_HOUR()), 30);
        $this->assertEquals($test->get(CF::SECOND_OF_MINUTE()), 40);
        $this->assertEquals($test->get(CF::NANO_OF_SECOND()), 987654321);
        $this->assertEquals($test->get(CF::HOUR_OF_AMPM()), 0);
        $this->assertEquals($test->get(CF::AMPM_OF_DAY()), 1);

        $this->assertEquals($test->get(CF::OFFSET_SECONDS()), 3600);
    }


    public function test_getLong_TemporalField()
    {
        $test = ZonedDateTime::ofDateTime(LocalDateTime::of(2008, 6, 30, 12, 30, 40, 987654321), self::ZONE_0100());
        $this->assertEquals($test->getLong(CF::YEAR()), 2008);
        $this->assertEquals($test->getLong(CF::MONTH_OF_YEAR()), 6);
        $this->assertEquals($test->getLong(CF::DAY_OF_MONTH()), 30);
        $this->assertEquals($test->getLong(CF::DAY_OF_WEEK()), 1);
        $this->assertEquals($test->getLong(CF::DAY_OF_YEAR()), 182);

        $this->assertEquals($test->getLong(CF::HOUR_OF_DAY()), 12);
        $this->assertEquals($test->getLong(CF::MINUTE_OF_HOUR()), 30);
        $this->assertEquals($test->getLong(CF::SECOND_OF_MINUTE()), 40);
        $this->assertEquals($test->getLong(CF::NANO_OF_SECOND()), 987654321);
        $this->assertEquals($test->getLong(CF::HOUR_OF_AMPM()), 0);
        $this->assertEquals($test->getLong(CF::AMPM_OF_DAY()), 1);

        $this->assertEquals($test->getLong(CF::OFFSET_SECONDS()), 3600);
        $this->assertEquals($test->getLong(CF::INSTANT_SECONDS()), $test->toEpochSecond());
    }

    //-----------------------------------------------------------------------
    // query(TemporalQuery)
    //-----------------------------------------------------------------------

    public function test_query_chrono()
    {
        $this->assertEquals($this->TEST_DATE_TIME->query(TemporalQueries::chronology()), IsoChronology::INSTANCE());
        $this->assertEquals(TemporalQueries::chronology()->queryFrom($this->TEST_DATE_TIME), IsoChronology::INSTANCE());
    }


    public function test_query_zoneId()
    {
        $this->assertEquals($this->TEST_DATE_TIME->query(TemporalQueries::zoneId()), $this->TEST_DATE_TIME->getZone());
        $this->assertEquals(TemporalQueries::zoneId()->queryFrom($this->TEST_DATE_TIME), $this->TEST_DATE_TIME->getZone());
    }


    public function test_query_precision()
    {
        $this->assertEquals($this->TEST_DATE_TIME->query(TemporalQueries::precision()), CU::NANOS());
        $this->assertEquals(TemporalQueries::precision()->queryFrom($this->TEST_DATE_TIME), CU::NANOS());
    }


    public function test_query_offset()
    {
        $this->assertEquals($this->TEST_DATE_TIME->query(TemporalQueries::offset()), $this->TEST_DATE_TIME->getOffset());
        $this->assertEquals(TemporalQueries::offset()->queryFrom($this->TEST_DATE_TIME), $this->TEST_DATE_TIME->getOffset());
    }


    public function test_query_zone()
    {
        $this->assertEquals($this->TEST_DATE_TIME->query(TemporalQueries::zone()), $this->TEST_DATE_TIME->getZone());
        $this->assertEquals(TemporalQueries::zone()->queryFrom($this->TEST_DATE_TIME), $this->TEST_DATE_TIME->getZone());
    }

    public function test_query_null()
    {
        TestHelper::assertNullException($this, function () {
            $this->TEST_DATE_TIME->query(null);
        });
    }

    //-----------------------------------------------------------------------
    // withEarlierOffsetAtOverlap()
    //-----------------------------------------------------------------------

    public function test_withEarlierOffsetAtOverlap_notAtOverlap()
    {
        $base = ZonedDateTime::ofStrict(self::TEST_LOCAL_2008_06_30_11_30_59_500(), self::OFFSET_0200(), self::ZONE_PARIS());
        $test = $base->withEarlierOffsetAtOverlap();
        $this->assertEquals($test, $base);  // not changed
    }


    public function test_withEarlierOffsetAtOverlap_atOverlap()
    {
        $base = ZonedDateTime::ofStrict(self::TEST_PARIS_OVERLAP_2008_10_26_02_30(), self::OFFSET_0100(), self::ZONE_PARIS());
        $test = $base->withEarlierOffsetAtOverlap();
        $this->assertEquals($test->getOffset(), self::OFFSET_0200());  // $offset changed to earlier
        $this->assertEquals($test->toLocalDateTime(), $base->toLocalDateTime());  // date-time not changed
    }


    public function test_withEarlierOffsetAtOverlap_atOverlap_noChange()
    {
        $base = ZonedDateTime::ofStrict(self::TEST_PARIS_OVERLAP_2008_10_26_02_30(), self::OFFSET_0200(), self::ZONE_PARIS());
        $test = $base->withEarlierOffsetAtOverlap();
        $this->assertEquals($test, $base);  // not changed
    }

    //-----------------------------------------------------------------------
    // withLaterOffsetAtOverlap()
    //-----------------------------------------------------------------------

    public function test_withLaterOffsetAtOverlap_notAtOverlap()
    {
        $base = ZonedDateTime::ofStrict(self::TEST_LOCAL_2008_06_30_11_30_59_500(), self::OFFSET_0200(), self::ZONE_PARIS());
        $test = $base->withLaterOffsetAtOverlap();
        $this->assertEquals($test, $base);  // not changed
    }


    public function test_withLaterOffsetAtOverlap_atOverlap()
    {
        $base = ZonedDateTime::ofStrict(self::TEST_PARIS_OVERLAP_2008_10_26_02_30(), self::OFFSET_0200(), self::ZONE_PARIS());
        $test = $base->withLaterOffsetAtOverlap();
        $this->assertEquals($test->getOffset(), self::OFFSET_0100());  // $offset changed to later
        $this->assertEquals($test->toLocalDateTime(), $base->toLocalDateTime());  // date-time not changed
    }


    public function test_withLaterOffsetAtOverlap_atOverlap_noChange()
    {
        $base = ZonedDateTime::ofStrict(self::TEST_PARIS_OVERLAP_2008_10_26_02_30(), self::OFFSET_0100(), self::ZONE_PARIS());
        $test = $base->withLaterOffsetAtOverlap();
        $this->assertEquals($test, $base);  // not changed
    }

    //-----------------------------------------------------------------------
    // withZoneSameLocal(ZoneId)
    //-----------------------------------------------------------------------

    public function test_withZoneSameLocal()
    {
        $ldt = LocalDateTime::of(2008, 6, 30, 23, 30, 59, 0);
        $base = ZonedDateTime::ofDateTime($ldt, self::ZONE_0100());
        $test = $base->withZoneSameLocal(self::ZONE_0200());
        $this->assertEquals($test->toLocalDateTime(), $base->toLocalDateTime());
    }


    public function test_withZoneSameLocal_noChange()
    {
        $ldt = LocalDateTime::of(2008, 6, 30, 23, 30, 59, 0);
        $base = ZonedDateTime::ofDateTime($ldt, self::ZONE_0100());
        $test = $base->withZoneSameLocal(self::ZONE_0100());
        $this->assertEquals($test, $base);
    }


    public function test_withZoneSameLocal_retainOffset1()
    {
        $ldt = LocalDateTime::of(2008, 11, 2, 1, 30, 59, 0);  // overlap
        $base = ZonedDateTime::ofDateTime($ldt, ZoneId::of("UTC-04:00"));
        $test = $base->withZoneSameLocal(ZoneId::of("America/New_York"));
        $this->assertEquals($base->getOffset(), ZoneOffset::ofHours(-4));
        $this->assertEquals($test->getOffset(), ZoneOffset::ofHours(-4));
    }


    public function test_withZoneSameLocal_retainOffset2()
    {
        $ldt = LocalDateTime::of(2008, 11, 2, 1, 30, 59, 0);  // overlap
        $base = ZonedDateTime::ofDateTime($ldt, ZoneId::of("UTC-05:00"));
        $test = $base->withZoneSameLocal(ZoneId::of("America/New_York"));
        $this->assertEquals($base->getOffset(), ZoneOffset::ofHours(-5));
        $this->assertEquals($test->getOffset(), ZoneOffset::ofHours(-5));
    }

    public function test_withZoneSameLocal_null()
    {
        TestHelper::assertNullException($this, function () {
            $ldt = LocalDateTime::of(2008, 6, 30, 23, 30, 59, 0);
            $base = ZonedDateTime::ofDateTime($ldt, self::ZONE_0100());
            $base->withZoneSameLocal(null);
        });

    }

    //-----------------------------------------------------------------------
    // withZoneSameInstant()
    //-----------------------------------------------------------------------

    public function test_withZoneSameInstant()
    {
        $base = ZonedDateTime::ofDateTime(self::TEST_LOCAL_2008_06_30_11_30_59_500(), self::ZONE_0100());
        $test = $base->withZoneSameInstant(self::ZONE_0200());
        $expected = ZonedDateTime::ofDateTime(self::TEST_LOCAL_2008_06_30_11_30_59_500()->plusHours(1), self::ZONE_0200());
        $this->assertEquals($test, $expected);
    }


    public function test_withZoneSameInstant_noChange()
    {
        $base = ZonedDateTime::ofDateTime(self::TEST_LOCAL_2008_06_30_11_30_59_500(), self::ZONE_0100());
        $test = $base->withZoneSameInstant(self::ZONE_0100());
        $this->assertEquals($test, $base);
    }

    public function test_withZoneSameInstant_null()
    {
        TestHelper::assertNullException($this, function () {
            $base = ZonedDateTime::ofDateTime(self::TEST_LOCAL_2008_06_30_11_30_59_500(), self::ZONE_0100());
            $base->withZoneSameInstant(null);
        });

    }

    //-----------------------------------------------------------------------
    // withFixedOffsetZone()
    //-----------------------------------------------------------------------

    public function test_withZoneLocked()
    {
        $base = ZonedDateTime::ofDateTime(self::TEST_LOCAL_2008_06_30_11_30_59_500(), self::ZONE_PARIS());
        $test = $base->withFixedOffsetZone();
        $expected = ZonedDateTime::ofDateTime(self::TEST_LOCAL_2008_06_30_11_30_59_500(), self::ZONE_0200());
        $this->assertEquals($test, $expected);
    }

    //-----------------------------------------------------------------------
    // with(TemporalAdjuster)
    //-----------------------------------------------------------------------

    public function test_with_adjuster_LocalDateTime_sameOffset()
    {
        $base = ZonedDateTime::ofDateTime(self::TEST_LOCAL_2008_06_30_11_30_59_500(), self::ZONE_PARIS());
        $test = $base->adjust(LocalDateTime::of(2012, 7, 15, 14, 30));
        $this->check($test, 2012, 7, 15, 14, 30, 0, 0, self::OFFSET_0200(), self::ZONE_PARIS());
    }


    public function test_with_adjuster_LocalDateTime_adjustedOffset()
    {
        $base = ZonedDateTime::ofDateTime(self::TEST_LOCAL_2008_06_30_11_30_59_500(), self::ZONE_PARIS());
        $test = $base->adjust(LocalDateTime::of(2012, 1, 15, 14, 30));
        $this->check($test, 2012, 1, 15, 14, 30, 0, 0, self::OFFSET_0100(), self::ZONE_PARIS());
    }


    public function test_with_adjuster_LocalDate()
    {
        $base = ZonedDateTime::ofDateTime(self::TEST_LOCAL_2008_06_30_11_30_59_500(), self::ZONE_PARIS());
        $test = $base->adjust(LocalDate::of(2012, 7, 28));
        $this->check($test, 2012, 7, 28, 11, 30, 59, 500, self::OFFSET_0200(), self::ZONE_PARIS());
    }


    public function test_with_adjuster_LocalTime()
    {
        $base = ZonedDateTime::ofDateTime(self::TEST_PARIS_OVERLAP_2008_10_26_02_30(), self::ZONE_PARIS());
        $test = $base->adjust(LocalTime::of(2, 29));
        $this->check($test, 2008, 10, 26, 2, 29, 0, 0, self::OFFSET_0200(), self::ZONE_PARIS());
    }


    public function test_with_adjuster_Year()
    {
        $ldt = LocalDateTime::of(2008, 6, 30, 23, 30, 59, 0);
        $base = ZonedDateTime::ofDateTime($ldt, self::ZONE_0100());
        $test = $base->adjust(Year::of(2007));
        $this->assertEquals($test, ZonedDateTime::ofDateTime($ldt->withYear(2007), self::ZONE_0100()));
    }


    public function test_with_adjuster_Month_adjustedDayOfMonth()
    {
        $base = ZonedDateTime::ofDateTime(LocalDateTime::of(2012, 7, 31, 0, 0), self::ZONE_PARIS());
        $test = $base->adjust(Month::JUNE());
        $this->check($test, 2012, 6, 30, 0, 0, 0, 0, self::OFFSET_0200(), self::ZONE_PARIS());
    }


    public function test_with_adjuster_Offset_same()
    {
        $base = ZonedDateTime::ofDateTime(LocalDateTime::of(2012, 7, 31, 0, 0), self::ZONE_PARIS());
        $test = $base->adjust(ZoneOffset::ofHours(2));
        $this->check($test, 2012, 7, 31, 0, 0, 0, 0, self::OFFSET_0200(), self::ZONE_PARIS());
    }


    public function test_with_adjuster_Offset_timeAdjust()
    {
        $base = ZonedDateTime::ofDateTime(LocalDateTime::of(2012, 7, 31, 0, 0), self::ZONE_PARIS());
        $test = $base->adjust(ZoneOffset::ofHours(1));
        $this->check($test, 2012, 7, 31, 0, 0, 0, 0, self::OFFSET_0200(), self::ZONE_PARIS());  // invalid $offset $ignored
    }


    public function test_with_adjuster_LocalDate_retainOffset1()
    {
        $newYork = ZoneId::of("America/New_York");
        $ldt = LocalDateTime::of(2008, 11, 1, 1, 30);
        $base = ZonedDateTime::ofDateTime($ldt, $newYork);
        $this->assertEquals($base->getOffset(), ZoneOffset::ofHours(-4));
        $test = $base->adjust(LocalDate::of(2008, 11, 2));
        $this->assertEquals($test->getOffset(), ZoneOffset::ofHours(-4));
    }


    public function test_with_adjuster_LocalDate_retainOffset2()
    {
        $newYork = ZoneId::of("America/New_York");
        $ldt = LocalDateTime::of(2008, 11, 3, 1, 30);
        $base = ZonedDateTime::ofDateTime($ldt, $newYork);
        $this->assertEquals($base->getOffset(), ZoneOffset::ofHours(-5));
        $test = $base->adjust(LocalDate::of(2008, 11, 2));
        $this->assertEquals($test->getOffset(), ZoneOffset::ofHours(-5));
    }


    public function test_with_adjuster_OffsetDateTime_validOffsetNotInOverlap()
    {
        // ODT will be $a valid ZDT for the $zone, so must be retained exactly
        $odt = self::TEST_LOCAL_2008_06_30_11_30_59_500()->atOffset(self::OFFSET_0200());
        $zdt = self::TEST_PARIS_OVERLAP_2008_10_26_02_30()->atZone(self::ZONE_PARIS());
        $test = $zdt->adjust($odt);
        $this->assertEquals($test->toOffsetDateTime(), $odt);
    }


    public function test_with_adjuster_OffsetDateTime_invalidOffsetIgnored()
    {
        // ODT has invalid $offset for ZDT, so only LDT is set
        $odt = self::TEST_LOCAL_2008_06_30_11_30_59_500()->atOffset(self::OFFSET_0130());
        $zdt = self::TEST_PARIS_OVERLAP_2008_10_26_02_30()->atZone(self::ZONE_PARIS());
        $test = $zdt->adjust($odt);
        $this->assertEquals($test->toLocalDateTime(), self::TEST_LOCAL_2008_06_30_11_30_59_500());
        $this->assertEquals($test->getOffset(), $zdt->getOffset());
    }


    public function test_with_adjuster_OffsetDateTime_retainOffsetInOverlap1()
    {
        // ODT will be $a valid ZDT for the $zone, so must be retained exactly
        $odt = self::TEST_PARIS_OVERLAP_2008_10_26_02_30()->atOffset(self::OFFSET_0100());
        $zdt = self::TEST_LOCAL_2008_06_30_11_30_59_500()->atZone(self::ZONE_PARIS());
        $test = $zdt->adjust($odt);
        $this->assertEquals($test->toOffsetDateTime(), $odt);
    }


    public function test_with_adjuster_OffsetDateTime_retainOffsetInOverlap2()
    {
        // ODT will be $a valid ZDT for the $zone, so must be retained exactly
        $odt = self::TEST_PARIS_OVERLAP_2008_10_26_02_30()->atOffset(self::OFFSET_0200());
        $zdt = self::TEST_LOCAL_2008_06_30_11_30_59_500()->atZone(self::ZONE_PARIS());
        $test = $zdt->adjust($odt);
        $this->assertEquals($test->toOffsetDateTime(), $odt);
    }


    public function test_with_adjuster_OffsetTime_validOffsetNotInOverlap()
    {
        // OT has valid $offset for resulting time
        $ot = OffsetTime::of(15, 50, 30, 40, self::OFFSET_0100());
        $zdt = self::TEST_PARIS_OVERLAP_2008_10_26_02_30()->atZone(self::ZONE_PARIS());
        $test = $zdt->adjust($ot);
        $this->assertEquals($test->toLocalDateTime(), $this->dateTime(2008, 10, 26, 15, 50, 30, 40));
        $this->assertEquals($test->getOffset(), self::OFFSET_0100());
    }


    public function test_with_adjuster_OffsetTime_invalidOffsetIgnored1()
    {
        // OT has invalid $offset for ZDT, so only LT is set
        $ot = OffsetTime::of(0, 50, 30, 40, self::OFFSET_0130());
        $zdt = $this->dateTimeZoned(2008, 10, 26, 2, 30, 0, 0, self::OFFSET_0200(), self::ZONE_PARIS());  // earlier part of overlap
        $test = $zdt->adjust($ot);
        $this->assertEquals($test->toLocalDateTime(), $this->dateTime(2008, 10, 26, 0, 50, 30, 40));
        $this->assertEquals($test->getOffset(), self::OFFSET_0200());  // $offset not adjusted
    }


    public function test_with_adjuster_OffsetTime_invalidOffsetIgnored2()
    {
        // OT has invalid $offset for ZDT, so only LT is set
        $ot = OffsetTime::of(15, 50, 30, 40, self::OFFSET_0130());
        $zdt = $this->dateTimeZoned(2008, 10, 26, 2, 30, 0, 0, self::OFFSET_0200(), self::ZONE_PARIS());  // earlier part of overlap
        $test = $zdt->adjust($ot);
        $this->assertEquals($test->toLocalDateTime(), $this->dateTime(2008, 10, 26, 15, 50, 30, 40));
        $this->assertEquals($test->getOffset(), self::OFFSET_0100());  // $offset adjusted because of time change
    }


    public function test_with_adjuster_OffsetTime_validOffsetIntoOverlap1()
    {
        // OT has valid $offset for resulting time
        $ot = OffsetTime::of(2, 30, 30, 40, self::OFFSET_0100());  // valid $offset in overlap
        $zdt = $this->dateTimeZoned(2008, 10, 26, 0, 0, 0, 0, self::OFFSET_0200(), self::ZONE_PARIS());  // just $before overlap
        $test = $zdt->adjust($ot);
        $this->assertEquals($test->toLocalDateTime(), $this->dateTime(2008, 10, 26, 2, 30, 30, 40));
        $this->assertEquals($test->getOffset(), self::OFFSET_0100());
    }


    public function test_with_adjuster_OffsetTime_validOffsetIntoOverlap2()
    {
        // OT has valid $offset for resulting time
        $ot = OffsetTime::of(2, 30, 30, 40, self::OFFSET_0200());  // valid $offset in overlap
        $zdt = $this->dateTimeZoned(2008, 10, 26, 0, 0, 0, 0, self::OFFSET_0200(), self::ZONE_PARIS());  // just $before overlap
        $test = $zdt->adjust($ot);
        $this->assertEquals($test->toLocalDateTime(), $this->dateTime(2008, 10, 26, 2, 30, 30, 40));
        $this->assertEquals($test->getOffset(), self::OFFSET_0200());
    }

    public function test_with_adjuster_null()
    {
        TestHelper::assertNullException($this, function () {
            $base = ZonedDateTime::ofDateTime(self::TEST_LOCAL_2008_06_30_11_30_59_500(), self::ZONE_0100());
            $base->adjust(null);
        });

    }

    //-----------------------------------------------------------------------
    // with(long,TemporalUnit)
    //-----------------------------------------------------------------------
    function data_withFieldLong()
    {
        return [
            // set simple fields
            [self::TEST_LOCAL_2008_06_30_11_30_59_500()->atZone(self::ZONE_PARIS()), CF::YEAR(), 2009,
                $this->dateTimeZoned(2009, 6, 30, 11, 30, 59, 500, self::OFFSET_0200(), self::ZONE_PARIS())],
            [self::TEST_LOCAL_2008_06_30_11_30_59_500()->atZone(self::ZONE_PARIS()), CF::MONTH_OF_YEAR(), 7,
                $this->dateTimeZoned(2008, 7, 30, 11, 30, 59, 500, self::OFFSET_0200(), self::ZONE_PARIS())],
            [self::TEST_LOCAL_2008_06_30_11_30_59_500()->atZone(self::ZONE_PARIS()), CF::DAY_OF_MONTH(), 15,
                $this->dateTimeZoned(2008, 6, 15, 11, 30, 59, 500, self::OFFSET_0200(), self::ZONE_PARIS())],
            [self::TEST_LOCAL_2008_06_30_11_30_59_500()->atZone(self::ZONE_PARIS()), CF::HOUR_OF_DAY(), 14,
                $this->dateTimeZoned(2008, 6, 30, 14, 30, 59, 500, self::OFFSET_0200(), self::ZONE_PARIS())],

            // set around overlap
            [self::TEST_PARIS_OVERLAP_2008_10_26_02_30()->atZone(self::ZONE_PARIS())->withEarlierOffsetAtOverlap(), CF::HOUR_OF_DAY(), 0,
                $this->dateTimeZoned(2008, 10, 26, 0, 30, 0, 0, self::OFFSET_0200(), self::ZONE_PARIS())],
            [self::TEST_PARIS_OVERLAP_2008_10_26_02_30()->atZone(self::ZONE_PARIS())->withLaterOffsetAtOverlap(), CF::HOUR_OF_DAY(), 0,
                $this->dateTimeZoned(2008, 10, 26, 0, 30, 0, 0, self::OFFSET_0200(), self::ZONE_PARIS())],
            [self::TEST_PARIS_OVERLAP_2008_10_26_02_30()->atZone(self::ZONE_PARIS())->withEarlierOffsetAtOverlap(), CF::MINUTE_OF_HOUR(), 20,
                $this->dateTimeZoned(2008, 10, 26, 2, 20, 0, 0, self::OFFSET_0200(), self::ZONE_PARIS())],  // $offset unchanged
            [self::TEST_PARIS_OVERLAP_2008_10_26_02_30()->atZone(self::ZONE_PARIS())->withLaterOffsetAtOverlap(), CF::MINUTE_OF_HOUR(), 20,
                $this->dateTimeZoned(2008, 10, 26, 2, 20, 0, 0, self::OFFSET_0100(), self::ZONE_PARIS())],  // $offset unchanged
            [self::TEST_PARIS_OVERLAP_2008_10_26_02_30()->atZone(self::ZONE_PARIS())->withEarlierOffsetAtOverlap(), CF::HOUR_OF_DAY(), 3,
                $this->dateTimeZoned(2008, 10, 26, 3, 30, 0, 0, self::OFFSET_0100(), self::ZONE_PARIS())],
            [self::TEST_PARIS_OVERLAP_2008_10_26_02_30()->atZone(self::ZONE_PARIS())->withLaterOffsetAtOverlap(), CF::HOUR_OF_DAY(), 3,
                $this->dateTimeZoned(2008, 10, 26, 3, 30, 0, 0, self::OFFSET_0100(), self::ZONE_PARIS())],

            // set $offset
            [self::TEST_LOCAL_2008_06_30_11_30_59_500()->atZone(self::ZONE_PARIS()), CF::OFFSET_SECONDS(), 7200,
                $this->dateTimeZoned(2008, 6, 30, 11, 30, 59, 500, self::OFFSET_0200(), self::ZONE_PARIS())],  // $offset unchanged
            [self::TEST_LOCAL_2008_06_30_11_30_59_500()->atZone(self::ZONE_PARIS()), CF::OFFSET_SECONDS(), 3600,
                $this->dateTimeZoned(2008, 6, 30, 11, 30, 59, 500, self::OFFSET_0200(), self::ZONE_PARIS())],  // invalid $offset $ignored
            [self::TEST_PARIS_OVERLAP_2008_10_26_02_30()->atZone(self::ZONE_PARIS())->withEarlierOffsetAtOverlap(), CF::OFFSET_SECONDS(), 3600,
                $this->dateTimeZoned(2008, 10, 26, 2, 30, 0, 0, self::OFFSET_0100(), self::ZONE_PARIS())],
            [self::TEST_PARIS_OVERLAP_2008_10_26_02_30()->atZone(self::ZONE_PARIS())->withLaterOffsetAtOverlap(), CF::OFFSET_SECONDS(), 3600,
                $this->dateTimeZoned(2008, 10, 26, 2, 30, 0, 0, self::OFFSET_0100(), self::ZONE_PARIS())],
            [self::TEST_PARIS_OVERLAP_2008_10_26_02_30()->atZone(self::ZONE_PARIS())->withEarlierOffsetAtOverlap(), CF::OFFSET_SECONDS(), 7200,
                $this->dateTimeZoned(2008, 10, 26, 2, 30, 0, 0, self::OFFSET_0200(), self::ZONE_PARIS())],
            [self::TEST_PARIS_OVERLAP_2008_10_26_02_30()->atZone(self::ZONE_PARIS())->withLaterOffsetAtOverlap(), CF::OFFSET_SECONDS(), 7200,
                $this->dateTimeZoned(2008, 10, 26, 2, 30, 0, 0, self::OFFSET_0200(), self::ZONE_PARIS())],
        ];
    }

    /**
     * @dataProvider data_withFieldLong
     */
    public function test_with_fieldLong(ZonedDateTime $base, TemporalField $setField, $setValue, ZonedDateTime $expected)
    {
        $this->assertEquals($base->with($setField, $setValue), $expected);
    }

    /**
     * @dataProvider data_withFieldLong
     */
    public function test_with_adjuster_ensureZoneOffsetConsistent(ZonedDateTime $base, TemporalField $setField, $setValue, ZonedDateTime $expected)
    {
        if ($setField == CF::OFFSET_SECONDS()) {
            $this->assertEquals($base->adjust(ZoneOffset::ofTotalSeconds($setValue)), $expected);
        } else {
            $this->assertTrue(true);
        }
    }

    /**
     * @dataProvider data_withFieldLong
     */
    public function test_with_adjuster_ensureOffsetDateTimeConsistent(ZonedDateTime $base, TemporalField $setField, $setValue, ZonedDateTime $expected)
    {
        if ($setField == CF::OFFSET_SECONDS()) {
            $odt = $base->toOffsetDateTime()->with($setField, $setValue);
            $this->assertEquals($base->adjust($odt), $expected);
        } else {
            $this->assertTrue(true);
        }
    }

    //-----------------------------------------------------------------------
    // withYear()
    //-----------------------------------------------------------------------

    public function test_withYear_normal()
    {
        $base = ZonedDateTime::ofDateTime(self::TEST_LOCAL_2008_06_30_11_30_59_500(), self::ZONE_0100());
        $test = $base->withYear(2007);
        $this->assertEquals($test, ZonedDateTime::ofDateTime(self::TEST_LOCAL_2008_06_30_11_30_59_500()->withYear(2007), self::ZONE_0100()));
    }


    public function test_withYear_noChange()
    {
        $base = ZonedDateTime::ofDateTime(self::TEST_LOCAL_2008_06_30_11_30_59_500(), self::ZONE_0100());
        $test = $base->withYear(2008);
        $this->assertEquals($test, $base);
    }

    //-----------------------------------------------------------------------
    // with(Month)
    //-----------------------------------------------------------------------

    public function test_withMonth_Month_normal()
    {
        $base = ZonedDateTime::ofDateTime(self::TEST_LOCAL_2008_06_30_11_30_59_500(), self::ZONE_0100());
        $test = $base->adjust(Month::JANUARY());
        $this->assertEquals($test, ZonedDateTime::ofDateTime(self::TEST_LOCAL_2008_06_30_11_30_59_500()->withMonth(1), self::ZONE_0100()));
    }

    public function test_withMonth_Month_null()
    {
        TestHelper::assertNullException($this, function () {
            $base = ZonedDateTime::ofDateTime(self::TEST_LOCAL_2008_06_30_11_30_59_500(), self::ZONE_0100());
            $base->adjust(null);
        });

    }

    //-----------------------------------------------------------------------
    // withMonth()
    //-----------------------------------------------------------------------

    public function test_withMonth_normal()
    {
        $base = ZonedDateTime::ofDateTime(self::TEST_LOCAL_2008_06_30_11_30_59_500(), self::ZONE_0100());
        $test = $base->withMonth(1);
        $this->assertEquals($test, ZonedDateTime::ofDateTime(self::TEST_LOCAL_2008_06_30_11_30_59_500()->withMonth(1), self::ZONE_0100()));
    }


    public function test_withMonth_noChange()
    {
        $base = ZonedDateTime::ofDateTime(self::TEST_LOCAL_2008_06_30_11_30_59_500(), self::ZONE_0100());
        $test = $base->withMonth(6);
        $this->assertEquals($test, $base);
    }

    /**
     * @expectedException \Celest\DateTimeException
     */
    public function test_withMonth_tooBig()
    {
        $this->TEST_DATE_TIME->withMonth(13);
    }

    /**
     * @expectedException \Celest\DateTimeException
     */
    public function test_withMonth_tooSmall()
    {
        $this->TEST_DATE_TIME->withMonth(0);
    }

    //-----------------------------------------------------------------------
    // withDayOfMonth()
    //-----------------------------------------------------------------------

    public function test_withDayOfMonth_normal()
    {
        $base = ZonedDateTime::ofDateTime(self::TEST_LOCAL_2008_06_30_11_30_59_500(), self::ZONE_0100());
        $test = $base->withDayOfMonth(15);
        $this->assertEquals($test, ZonedDateTime::ofDateTime(self::TEST_LOCAL_2008_06_30_11_30_59_500()->withDayOfMonth(15), self::ZONE_0100()));
    }


    public function test_withDayOfMonth_noChange()
    {
        $base = ZonedDateTime::ofDateTime(self::TEST_LOCAL_2008_06_30_11_30_59_500(), self::ZONE_0100());
        $test = $base->withDayOfMonth(30);
        $this->assertEquals($test, $base);
    }

    /**
     * @expectedException \Celest\DateTimeException
     */
    public function test_withDayOfMonth_tooBig()
    {
        LocalDateTime::of(2007, 7, 2, 11, 30)->atZone(self::ZONE_PARIS())->withDayOfMonth(32);
    }

    /**
     * @expectedException \Celest\DateTimeException
     */
    public function test_withDayOfMonth_tooSmall()
    {
        $this->TEST_DATE_TIME->withDayOfMonth(0);
    }

    /**
     * @expectedException \Celest\DateTimeException
     */
    public function test_withDayOfMonth_invalid31()
    {
        LocalDateTime::of(2007, 6, 2, 11, 30)->atZone(self::ZONE_PARIS())->withDayOfMonth(31);
    }

    //-----------------------------------------------------------------------
    // withDayOfYear()
    //-----------------------------------------------------------------------

    public function test_withDayOfYear_normal()
    {
        $base = ZonedDateTime::ofDateTime(self::TEST_LOCAL_2008_06_30_11_30_59_500(), self::ZONE_0100());
        $test = $base->withDayOfYear(33);
        $this->assertEquals($test, ZonedDateTime::ofDateTime(self::TEST_LOCAL_2008_06_30_11_30_59_500()->withDayOfYear(33), self::ZONE_0100()));
    }


    public function test_withDayOfYear_noChange()
    {
        $ldt = LocalDateTime::of(2008, 2, 5, 23, 30, 59, 0);
        $base = ZonedDateTime::ofDateTime($ldt, self::ZONE_0100());
        $test = $base->withDayOfYear(36);
        $this->assertEquals($test, $base);
    }

    /**
     * @expectedException \Celest\DateTimeException
     */
    public function test_withDayOfYear_tooBig()
    {
        $this->TEST_DATE_TIME->withDayOfYear(367);
    }

    /**
     * @expectedException \Celest\DateTimeException
     */
    public function test_withDayOfYear_tooSmall()
    {
        $this->TEST_DATE_TIME->withDayOfYear(0);
    }

    /**
     * @expectedException \Celest\DateTimeException
     */
    public function test_withDayOfYear_invalid366()
    {
        LocalDateTime::of(2007, 2, 2, 11, 30)->atZone(self::ZONE_PARIS())->withDayOfYear(366);
    }

    //-----------------------------------------------------------------------
    // withHour()
    //-----------------------------------------------------------------------

    public function test_withHour_normal()
    {
        $base = ZonedDateTime::ofDateTime(self::TEST_LOCAL_2008_06_30_11_30_59_500(), self::ZONE_0100());
        $test = $base->withHour(15);
        $this->assertEquals($test, ZonedDateTime::ofDateTime(self::TEST_LOCAL_2008_06_30_11_30_59_500()->withHour(15), self::ZONE_0100()));
    }


    public function test_withHour_noChange()
    {
        $base = ZonedDateTime::ofDateTime(self::TEST_LOCAL_2008_06_30_11_30_59_500(), self::ZONE_0100());
        $test = $base->withHour(11);
        $this->assertEquals($test, $base);
    }

    //-----------------------------------------------------------------------
    // withMinute()
    //-----------------------------------------------------------------------

    public function test_withMinute_normal()
    {
        $base = ZonedDateTime::ofDateTime(self::TEST_LOCAL_2008_06_30_11_30_59_500(), self::ZONE_0100());
        $test = $base->withMinute(15);
        $this->assertEquals($test, ZonedDateTime::ofDateTime(self::TEST_LOCAL_2008_06_30_11_30_59_500()->withMinute(15), self::ZONE_0100()));
    }


    public function test_withMinute_noChange()
    {
        $base = ZonedDateTime::ofDateTime(self::TEST_LOCAL_2008_06_30_11_30_59_500(), self::ZONE_0100());
        $test = $base->withMinute(30);
        $this->assertEquals($test, $base);
    }

    //-----------------------------------------------------------------------
    // withSecond()
    //-----------------------------------------------------------------------

    public function test_withSecond_normal()
    {
        $base = ZonedDateTime::ofDateTime(self::TEST_LOCAL_2008_06_30_11_30_59_500(), self::ZONE_0100());
        $test = $base->withSecond(12);
        $this->assertEquals($test, ZonedDateTime::ofDateTime(self::TEST_LOCAL_2008_06_30_11_30_59_500()->withSecond(12), self::ZONE_0100()));
    }


    public function test_withSecond_noChange()
    {
        $base = ZonedDateTime::ofDateTime(self::TEST_LOCAL_2008_06_30_11_30_59_500(), self::ZONE_0100());
        $test = $base->withSecond(59);
        $this->assertEquals($test, $base);
    }

    //-----------------------------------------------------------------------
    // withNano()
    //-----------------------------------------------------------------------

    public function test_withNanoOfSecond_normal()
    {
        $base = ZonedDateTime::ofDateTime(self::TEST_LOCAL_2008_06_30_11_30_59_500(), self::ZONE_0100());
        $test = $base->withNano(15);
        $this->assertEquals($test, ZonedDateTime::ofDateTime(self::TEST_LOCAL_2008_06_30_11_30_59_500()->withNano(15), self::ZONE_0100()));
    }


    public function test_withNanoOfSecond_noChange()
    {
        $base = ZonedDateTime::ofDateTime(self::TEST_LOCAL_2008_06_30_11_30_59_500(), self::ZONE_0100());
        $test = $base->withNano(500);
        $this->assertEquals($test, $base);
    }

    //-----------------------------------------------------------------------
    // truncatedTo(TemporalUnit)
    //-----------------------------------------------------------------------

    public function test_truncatedTo_normal()
    {
        $this->assertEquals($this->TEST_DATE_TIME->truncatedTo(CU::NANOS()), $this->TEST_DATE_TIME);
        $this->assertEquals($this->TEST_DATE_TIME->truncatedTo(CU::SECONDS()), $this->TEST_DATE_TIME->withNano(0));
        $this->assertEquals($this->TEST_DATE_TIME->truncatedTo(CU::DAYS()), $this->TEST_DATE_TIME->adjust(LocalTime::MIDNIGHT()));
    }

    public function test_truncatedTo_null()
    {
        TestHelper::assertNullException($this, function () {
            $this->TEST_DATE_TIME->truncatedTo(null);
        });
    }

    //-----------------------------------------------------------------------
    // plus/minus
    //-----------------------------------------------------------------------
    function data_plusDays()
    {
        return [
            // $normal
            [$this->dateTimeZoned(2008, 6, 30, 23, 30, 59, 0, self::OFFSET_0100(), self::ZONE_0100()), 0, $this->dateTimeZoned(2008, 6, 30, 23, 30, 59, 0, self::OFFSET_0100(), self::ZONE_0100())],
            [$this->dateTimeZoned(2008, 6, 30, 23, 30, 59, 0, self::OFFSET_0100(), self::ZONE_0100()), 1, $this->dateTimeZoned(2008, 7, 1, 23, 30, 59, 0, self::OFFSET_0100(), self::ZONE_0100())],
            [$this->dateTimeZoned(2008, 6, 30, 23, 30, 59, 0, self::OFFSET_0100(), self::ZONE_0100()), -1, $this->dateTimeZoned(2008, 6, 29, 23, 30, 59, 0, self::OFFSET_0100(), self::ZONE_0100())],
            // skip over gap
            [$this->dateTimeZoned(2008, 3, 30, 1, 30, 0, 0, self::OFFSET_0100(), self::ZONE_PARIS()), 1, $this->dateTimeZoned(2008, 3, 31, 1, 30, 0, 0, self::OFFSET_0200(), self::ZONE_PARIS())],
            [$this->dateTimeZoned(2008, 3, 30, 3, 30, 0, 0, self::OFFSET_0200(), self::ZONE_PARIS()), -1, $this->dateTimeZoned(2008, 3, 29, 3, 30, 0, 0, self::OFFSET_0100(), self::ZONE_PARIS())],
            // land in gap
            [$this->dateTimeZoned(2008, 3, 29, 2, 30, 0, 0, self::OFFSET_0100(), self::ZONE_PARIS()), 1, $this->dateTimeZoned(2008, 3, 30, 3, 30, 0, 0, self::OFFSET_0200(), self::ZONE_PARIS())],
            [$this->dateTimeZoned(2008, 3, 31, 2, 30, 0, 0, self::OFFSET_0200(), self::ZONE_PARIS()), -1, $this->dateTimeZoned(2008, 3, 30, 3, 30, 0, 0, self::OFFSET_0200(), self::ZONE_PARIS())],
            // skip over overlap
            [$this->dateTimeZoned(2008, 10, 26, 1, 30, 0, 0, self::OFFSET_0200(), self::ZONE_PARIS()), 1, $this->dateTimeZoned(2008, 10, 27, 1, 30, 0, 0, self::OFFSET_0100(), self::ZONE_PARIS())],
            [$this->dateTimeZoned(2008, 10, 25, 3, 30, 0, 0, self::OFFSET_0200(), self::ZONE_PARIS()), 1, $this->dateTimeZoned(2008, 10, 26, 3, 30, 0, 0, self::OFFSET_0100(), self::ZONE_PARIS())],
            // land in overlap
            [$this->dateTimeZoned(2008, 10, 25, 2, 30, 0, 0, self::OFFSET_0200(), self::ZONE_PARIS()), 1, $this->dateTimeZoned(2008, 10, 26, 2, 30, 0, 0, self::OFFSET_0200(), self::ZONE_PARIS())],
            [$this->dateTimeZoned(2008, 10, 27, 2, 30, 0, 0, self::OFFSET_0100(), self::ZONE_PARIS()), -1, $this->dateTimeZoned(2008, 10, 26, 2, 30, 0, 0, self::OFFSET_0100(), self::ZONE_PARIS())],
        ];
    }

    function data_plusTime()
    {
        return [
            // $normal
            [$this->dateTimeZoned(2008, 6, 30, 23, 30, 59, 0, self::OFFSET_0100(), self::ZONE_0100()), 0, $this->dateTimeZoned(2008, 6, 30, 23, 30, 59, 0, self::OFFSET_0100(), self::ZONE_0100())],
            [$this->dateTimeZoned(2008, 6, 30, 23, 30, 59, 0, self::OFFSET_0100(), self::ZONE_0100()), 1, $this->dateTimeZoned(2008, 7, 1, 0, 30, 59, 0, self::OFFSET_0100(), self::ZONE_0100())],
            [$this->dateTimeZoned(2008, 6, 30, 23, 30, 59, 0, self::OFFSET_0100(), self::ZONE_0100()), -1, $this->dateTimeZoned(2008, 6, 30, 22, 30, 59, 0, self::OFFSET_0100(), self::ZONE_0100())],
            // gap
            [$this->dateTimeZoned(2008, 3, 30, 1, 30, 0, 0, self::OFFSET_0100(), self::ZONE_PARIS()), 1, $this->dateTimeZoned(2008, 3, 30, 3, 30, 0, 0, self::OFFSET_0200(), self::ZONE_PARIS())],
            [$this->dateTimeZoned(2008, 3, 30, 3, 30, 0, 0, self::OFFSET_0200(), self::ZONE_PARIS()), -1, $this->dateTimeZoned(2008, 3, 30, 1, 30, 0, 0, self::OFFSET_0100(), self::ZONE_PARIS())],
            // overlap
            [$this->dateTimeZoned(2008, 10, 26, 1, 30, 0, 0, self::OFFSET_0200(), self::ZONE_PARIS()), 1, $this->dateTimeZoned(2008, 10, 26, 2, 30, 0, 0, self::OFFSET_0200(), self::ZONE_PARIS())],
            [$this->dateTimeZoned(2008, 10, 26, 1, 30, 0, 0, self::OFFSET_0200(), self::ZONE_PARIS()), 2, $this->dateTimeZoned(2008, 10, 26, 2, 30, 0, 0, self::OFFSET_0100(), self::ZONE_PARIS())],
            [$this->dateTimeZoned(2008, 10, 26, 1, 30, 0, 0, self::OFFSET_0200(), self::ZONE_PARIS()), 3, $this->dateTimeZoned(2008, 10, 26, 3, 30, 0, 0, self::OFFSET_0100(), self::ZONE_PARIS())],
            [$this->dateTimeZoned(2008, 10, 26, 2, 30, 0, 0, self::OFFSET_0200(), self::ZONE_PARIS()), 1, $this->dateTimeZoned(2008, 10, 26, 2, 30, 0, 0, self::OFFSET_0100(), self::ZONE_PARIS())],
            [$this->dateTimeZoned(2008, 10, 26, 2, 30, 0, 0, self::OFFSET_0200(), self::ZONE_PARIS()), 2, $this->dateTimeZoned(2008, 10, 26, 3, 30, 0, 0, self::OFFSET_0100(), self::ZONE_PARIS())],
        ];
    }

    //-----------------------------------------------------------------------
    // plus(TemporalAmount)
    //-----------------------------------------------------------------------
    /**
     * @dataProvider data_plusDays
     */
    public function test_plus_TemporalAmount_Period_days(ZonedDateTime $base, $amount, ZonedDateTime $expected)
    {
        $this->assertEquals($base->plusAmount(Period::ofDays($amount)), $expected);
    }

    /**
     * @dataProvider data_plusTime
     */
    public function test_plus_TemporalAmount_Period_hours(ZonedDateTime $base, $amount, ZonedDateTime $expected)
    {
        $this->assertEquals($base->plusAmount(MockSimplePeriod::of($amount, CU::HOURS())), $expected);
    }

    /**
     * @dataProvider data_plusTime
     */
    public function test_plus_TemporalAmount_Duration_hours(ZonedDateTime $base, $amount, ZonedDateTime $expected)
    {
        $this->assertEquals($base->plusAmount(Duration::ofHours($amount)), $expected);
    }


    public function test_plus_TemporalAmount()
    {
        $period = MockSimplePeriod::of(7, CU::MONTHS());
        $z = ZonedDateTime::ofDateTime(LocalDateTime::of(2008, 6, 1, 12, 30, 59, 500), self::ZONE_0100());
        $expected = ZonedDateTime::ofDateTime(LocalDateTime::of(2009, 1, 1, 12, 30, 59, 500), self::ZONE_0100());
        $this->assertEquals($z->plusAmount($period), $expected);
    }


    public function test_plus_TemporalAmount_Duration()
    {
        $duration = Duration::ofSeconds(4 * 60 * 60 + 5 * 60 + 6);
        $z = ZonedDateTime::ofDateTime(LocalDateTime::of(2008, 6, 1, 12, 30, 59, 500), self::ZONE_0100());
        $expected = ZonedDateTime::ofDateTime(LocalDateTime::of(2008, 6, 1, 16, 36, 5, 500), self::ZONE_0100());
        $this->assertEquals($z->plusAmount($duration), $expected);
    }


    public function test_plus_TemporalAmount_Period_zero()
    {
        $z = $this->TEST_DATE_TIME->plusAmount(MockSimplePeriod::ZERO_DAYS());
        $this->assertEquals($z, $this->TEST_DATE_TIME);
    }


    public function test_plus_TemporalAmount_Duration_zero()
    {
        $z = $this->TEST_DATE_TIME->plusAmount(Duration::ZERO());
        $this->assertEquals($z, $this->TEST_DATE_TIME);
    }

    public function test_plus_TemporalAmount_null()
    {
        TestHelper::assertNullException($this, function () {
            $this->TEST_DATE_TIME->plusAmount(null);
        });
    }

    //-----------------------------------------------------------------------
    // plus(long,TemporalUnit)
    //-----------------------------------------------------------------------
    /**
     * @dataProvider data_plusDays
     */
    public function test_plus_longUnit_days(ZonedDateTime $base, $amount, ZonedDateTime $expected)
    {
        $this->assertEquals($base->plus($amount, CU::DAYS()), $expected);
    }

    /**
     * @dataProvider data_plusTime
     */
    public function test_plus_longUnit_hours(ZonedDateTime $base, $amount, ZonedDateTime $expected)
    {
        $this->assertEquals($base->plus($amount, CU::HOURS()), $expected);
    }

    /**
     * @dataProvider data_plusTime
     */
    public function test_plus_longUnit_minutes(ZonedDateTime $base, $amount, ZonedDateTime $expected)
    {
        $this->assertEquals($base->plus($amount * 60, CU::MINUTES()), $expected);
    }

    /**
     * @dataProvider data_plusTime
     */
    public function test_plus_longUnit_seconds(ZonedDateTime $base, $amount, ZonedDateTime $expected)
    {
        $this->assertEquals($base->plus($amount * 3600, CU::SECONDS()), $expected);
    }

    /**
     * @dataProvider data_plusTime
     */
    public function test_plus_longUnit_nanos(ZonedDateTime $base, $amount, ZonedDateTime $expected)
    {
        $this->assertEquals($base->plus($amount * 3600000000000, CU::NANOS()), $expected);
    }

    public function test_plus_longUnit_null()
    {
        TestHelper::assertNullException($this, function () {
            $this->TEST_DATE_TIME_PARIS->plus(0, null);
        });
    }

    //-----------------------------------------------------------------------
    // plusYears()
    //-----------------------------------------------------------------------

    public function test_plusYears()
    {
        $ldt = LocalDateTime::of(2008, 6, 30, 23, 30, 59, 0);
        $base = ZonedDateTime::ofDateTime($ldt, self::ZONE_0100());
        $test = $base->plusYears(1);
        $this->assertEquals($test, ZonedDateTime::ofDateTime($ldt->plusYears(1), self::ZONE_0100()));
    }


    public function test_plusYears_zero()
    {
        $ldt = LocalDateTime::of(2008, 6, 30, 23, 30, 59, 0);
        $base = ZonedDateTime::ofDateTime($ldt, self::ZONE_0100());
        $test = $base->plusYears(0);
        $this->assertEquals($test, $base);
    }

    //-----------------------------------------------------------------------
    // plusMonths()
    //-----------------------------------------------------------------------

    public function test_plusMonths()
    {
        $ldt = LocalDateTime::of(2008, 6, 30, 23, 30, 59, 0);
        $base = ZonedDateTime::ofDateTime($ldt, self::ZONE_0100());
        $test = $base->plusMonths(1);
        $this->assertEquals($test, ZonedDateTime::ofDateTime($ldt->plusMonths(1), self::ZONE_0100()));
    }


    public function test_plusMonths_zero()
    {
        $ldt = LocalDateTime::of(2008, 6, 30, 23, 30, 59, 0);
        $base = ZonedDateTime::ofDateTime($ldt, self::ZONE_0100());
        $test = $base->plusMonths(0);
        $this->assertEquals($test, $base);
    }

    //-----------------------------------------------------------------------
    // plusWeeks()
    //-----------------------------------------------------------------------

    public function test_plusWeeks()
    {
        $ldt = LocalDateTime::of(2008, 6, 30, 23, 30, 59, 0);
        $base = ZonedDateTime::ofDateTime($ldt, self::ZONE_0100());
        $test = $base->plusWeeks(1);
        $this->assertEquals($test, ZonedDateTime::ofDateTime($ldt->plusWeeks(1), self::ZONE_0100()));
    }


    public function test_plusWeeks_zero()
    {
        $ldt = LocalDateTime::of(2008, 6, 30, 23, 30, 59, 0);
        $base = ZonedDateTime::ofDateTime($ldt, self::ZONE_0100());
        $test = $base->plusWeeks(0);
        $this->assertEquals($test, $base);
    }

    //-----------------------------------------------------------------------
    // plusDays()
    //-----------------------------------------------------------------------
    /**
     * @dataProvider data_plusDays
     */
    public function test_plusDays(ZonedDateTime $base, $amount, ZonedDateTime $expected)
    {
        $this->assertEquals($base->plusDays($amount), $expected);
    }

    //-----------------------------------------------------------------------
    // plusHours()
    //-----------------------------------------------------------------------
    /**
     * @dataProvider data_plusTime
     */
    public function test_plusHours(ZonedDateTime $base, $amount, ZonedDateTime $expected)
    {
        $this->assertEquals($base->plusHours($amount), $expected);
    }

    //-----------------------------------------------------------------------
    // plusMinutes()
    //-----------------------------------------------------------------------
    /**
     * @dataProvider data_plusTime
     */
    public function test_plusMinutes(ZonedDateTime $base, $amount, ZonedDateTime $expected)
    {
        $this->assertEquals($base->plusMinutes($amount * 60), $expected);
    }


    public function test_plusMinutes_minutes()
    {
        $ldt = LocalDateTime::of(2008, 6, 30, 23, 30, 59, 0);
        $base = ZonedDateTime::ofDateTime($ldt, self::ZONE_0100());
        $test = $base->plusMinutes(30);
        $this->assertEquals($test, ZonedDateTime::ofDateTime($ldt->plusMinutes(30), self::ZONE_0100()));
    }

    //-----------------------------------------------------------------------
    // plusSeconds()
    //-----------------------------------------------------------------------
    /**
     * @dataProvider data_plusTime
     */
    public function test_plusSeconds(ZonedDateTime $base, $amount, ZonedDateTime $expected)
    {
        $this->assertEquals($base->plusSeconds($amount * 3600), $expected);
    }


    public function test_plusSeconds_seconds()
    {
        $ldt = LocalDateTime::of(2008, 6, 30, 23, 30, 59, 0);
        $base = ZonedDateTime::ofDateTime($ldt, self::ZONE_0100());
        $test = $base->plusSeconds(1);
        $this->assertEquals($test, ZonedDateTime::ofDateTime($ldt->plusSeconds(1), self::ZONE_0100()));
    }

    //-----------------------------------------------------------------------
    // plusNanos()
    //-----------------------------------------------------------------------
    /**
     * @dataProvider data_plusTime
     */
    public function test_plusNanos(ZonedDateTime $base, $amount, ZonedDateTime $expected)
    {
        $this->assertEquals($base->plusNanos($amount * 3600000000000), $expected);
    }


    public function test_plusNanos_nanos()
    {
        $ldt = LocalDateTime::of(2008, 6, 30, 23, 30, 59, 0);
        $base = ZonedDateTime::ofDateTime($ldt, self::ZONE_0100());
        $test = $base->plusNanos(1);
        $this->assertEquals($test, ZonedDateTime::ofDateTime($ldt->plusNanos(1), self::ZONE_0100()));
    }

    //-----------------------------------------------------------------------
    // minus(TemporalAmount)
    //-----------------------------------------------------------------------
    /**
     * @dataProvider data_plusDays
     */
    public function test_minus_TemporalAmount_Period_days(ZonedDateTime $base, $amount, ZonedDateTime $expected)
    {
        $this->assertEquals($base->minusAmount(Period::ofDays(-$amount)), $expected);
    }

    /**
     * @dataProvider data_plusTime
     */
    public function test_minus_TemporalAmount_Period_hours(ZonedDateTime $base, $amount, ZonedDateTime $expected)
    {
        $this->assertEquals($base->minusAmount(MockSimplePeriod::of(-$amount, CU::HOURS())), $expected);
    }

    /**
     * @dataProvider data_plusTime
     */
    public function test_minus_TemporalAmount_Duration_hours(ZonedDateTime $base, $amount, ZonedDateTime $expected)
    {
        $this->assertEquals($base->minusAmount(Duration::ofHours(-$amount)), $expected);
    }


    public function test_minus_TemporalAmount()
    {
        $period = MockSimplePeriod::of(7, CU::MONTHS());
        $z = ZonedDateTime::ofDateTime(LocalDateTime::of(2008, 6, 1, 12, 30, 59, 500), self::ZONE_0100());
        $expected = ZonedDateTime::ofDateTime(LocalDateTime::of(2007, 11, 1, 12, 30, 59, 500), self::ZONE_0100());
        $this->assertEquals($z->minusAmount($period), $expected);
    }


    public function test_minus_TemporalAmount_Duration()
    {
        $duration = Duration::ofSeconds(4 * 60 * 60 + 5 * 60 + 6);
        $z = ZonedDateTime::ofDateTime(LocalDateTime::of(2008, 6, 1, 12, 30, 59, 500), self::ZONE_0100());
        $expected = ZonedDateTime::ofDateTime(LocalDateTime::of(2008, 6, 1, 8, 25, 53, 500), self::ZONE_0100());
        $this->assertEquals($z->minusAmount($duration), $expected);
    }


    public function test_minus_TemporalAmount_Period_zero()
    {
        $z = $this->TEST_DATE_TIME->minusAmount(MockSimplePeriod::ZERO_DAYS());
        $this->assertEquals($z, $this->TEST_DATE_TIME);
    }


    public function test_minus_TemporalAmount_Duration_zero()
    {
        $z = $this->TEST_DATE_TIME->minusAmount(Duration::ZERO());
        $this->assertEquals($z, $this->TEST_DATE_TIME);
    }

    public function test_minus_TemporalAmount_null()
    {
        TestHelper::assertNullException($this, function () {
            $this->TEST_DATE_TIME->minusAmount(null);
        });
    }

    //-----------------------------------------------------------------------
    // minusYears()
    //-----------------------------------------------------------------------

    public function test_minusYears()
    {
        $ldt = LocalDateTime::of(2008, 6, 30, 23, 30, 59, 0);
        $base = ZonedDateTime::ofDateTime($ldt, self::ZONE_0100());
        $test = $base->minusYears(1);
        $this->assertEquals($test, ZonedDateTime::ofDateTime($ldt->minusYears(1), self::ZONE_0100()));
    }


    public function test_minusYears_zero()
    {
        $ldt = LocalDateTime::of(2008, 6, 30, 23, 30, 59, 0);
        $base = ZonedDateTime::ofDateTime($ldt, self::ZONE_0100());
        $test = $base->minusYears(0);
        $this->assertEquals($test, $base);
    }

    //-----------------------------------------------------------------------
    // minusMonths()
    //-----------------------------------------------------------------------

    public function test_minusMonths()
    {
        $ldt = LocalDateTime::of(2008, 6, 30, 23, 30, 59, 0);
        $base = ZonedDateTime::ofDateTime($ldt, self::ZONE_0100());
        $test = $base->minusMonths(1);
        $this->assertEquals($test, ZonedDateTime::ofDateTime($ldt->minusMonths(1), self::ZONE_0100()));
    }


    public function test_minusMonths_zero()
    {
        $ldt = LocalDateTime::of(2008, 6, 30, 23, 30, 59, 0);
        $base = ZonedDateTime::ofDateTime($ldt, self::ZONE_0100());
        $test = $base->minusMonths(0);
        $this->assertEquals($test, $base);
    }

    //-----------------------------------------------------------------------
    // minusWeeks()
    //-----------------------------------------------------------------------

    public function test_minusWeeks()
    {
        $ldt = LocalDateTime::of(2008, 6, 30, 23, 30, 59, 0);
        $base = ZonedDateTime::ofDateTime($ldt, self::ZONE_0100());
        $test = $base->minusWeeks(1);
        $this->assertEquals($test, ZonedDateTime::ofDateTime($ldt->minusWeeks(1), self::ZONE_0100()));
    }


    public function test_minusWeeks_zero()
    {
        $ldt = LocalDateTime::of(2008, 6, 30, 23, 30, 59, 0);
        $base = ZonedDateTime::ofDateTime($ldt, self::ZONE_0100());
        $test = $base->minusWeeks(0);
        $this->assertEquals($test, $base);
    }

    //-----------------------------------------------------------------------
    // minusDays()
    //-----------------------------------------------------------------------
    /**
     * @dataProvider data_plusDays
     */
    public function test_minusDays(ZonedDateTime $base, $amount, ZonedDateTime $expected)
    {
        $this->assertEquals($base->minusDays(-$amount), $expected);
    }

    //-----------------------------------------------------------------------
    // minusHours()
    //-----------------------------------------------------------------------
    /**
     * @dataProvider data_plusTime
     */
    public function test_minusHours(ZonedDateTime $base, $amount, ZonedDateTime $expected)
    {
        $this->assertEquals($base->minusHours(-$amount), $expected);
    }

    //-----------------------------------------------------------------------
    // minusMinutes()
    //-----------------------------------------------------------------------
    /**
     * @dataProvider data_plusTime
     */
    public function test_minusMinutes(ZonedDateTime $base, $amount, ZonedDateTime $expected)
    {
        $this->assertEquals($base->minusMinutes(-$amount * 60), $expected);
    }


    public function test_minusMinutes_minutes()
    {
        $ldt = LocalDateTime::of(2008, 6, 30, 23, 30, 59, 0);
        $base = ZonedDateTime::ofDateTime($ldt, self::ZONE_0100());
        $test = $base->minusMinutes(30);
        $this->assertEquals($test, ZonedDateTime::ofDateTime($ldt->minusMinutes(30), self::ZONE_0100()));
    }

    //-----------------------------------------------------------------------
    // minusSeconds()
    //-----------------------------------------------------------------------
    /**
     * @dataProvider data_plusTime
     */
    public function test_minusSeconds(ZonedDateTime $base, $amount, ZonedDateTime $expected)
    {
        $this->assertEquals($base->minusSeconds(-$amount * 3600), $expected);
    }


    public function test_minusSeconds_seconds()
    {
        $ldt = LocalDateTime::of(2008, 6, 30, 23, 30, 59, 0);
        $base = ZonedDateTime::ofDateTime($ldt, self::ZONE_0100());
        $test = $base->minusSeconds(1);
        $this->assertEquals($test, ZonedDateTime::ofDateTime($ldt->minusSeconds(1), self::ZONE_0100()));
    }

    //-----------------------------------------------------------------------
    // minusNanos()
    //-----------------------------------------------------------------------
    /**
     * @dataProvider data_plusTime
     */
    public function test_minusNanos(ZonedDateTime $base, $amount, ZonedDateTime $expected)
    {
        $this->assertEquals($base->minusNanos(-$amount * 3600000000000), $expected);
    }


    public function test_minusNanos_nanos()
    {
        $ldt = LocalDateTime::of(2008, 6, 30, 23, 30, 59, 0);
        $base = ZonedDateTime::ofDateTime($ldt, self::ZONE_0100());
        $test = $base->minusNanos(1);
        $this->assertEquals($test, ZonedDateTime::ofDateTime($ldt->minusNanos(1), self::ZONE_0100()));
    }

    //-----------------------------------------------------------------------
    // until(Temporal,TemporalUnit)
    //-----------------------------------------------------------------------
    // TODO: more tests for $period between two different zones
    // compare results to OffsetDateTime.until, especially wrt dates

    /**
     * @dataProvider data_plusDays
     */
    public function test_until_days(ZonedDateTime $base, $expected, ZonedDateTime $end)
    {
        if ($base->toLocalTime()->equals($end->toLocalTime()) == false) {
            $this->assertTrue(true);
            return;  // avoid DST gap input values
        }

        $this->assertEquals($base->until($end, CU::DAYS()), $expected);
    }

    /**
     * @dataProvider data_plusTime
     */
    public function test_until_hours(ZonedDateTime $base, $expected, ZonedDateTime $end)
    {
        $this->assertEquals($base->until($end, CU::HOURS()), $expected);
    }

    /**
     * @dataProvider data_plusTime
     */
    public function test_until_minutes(ZonedDateTime $base, $expected, ZonedDateTime $end)
    {
        $this->assertEquals($base->until($end, CU::MINUTES()), $expected * 60);
    }

    /**
     * @dataProvider data_plusTime
     */
    public function test_until_seconds(ZonedDateTime $base, $expected, ZonedDateTime $end)
    {
        $this->assertEquals($base->until($end, CU::SECONDS()), $expected * 3600);
    }

    /**
     * @dataProvider data_plusTime
     */
    public function test_until_nanos(ZonedDateTime $base, $expected, ZonedDateTime $end)
    {
        $this->assertEquals($base->until($end, CU::NANOS()), $expected * 3600000000000);
    }


    public function test_until_parisLondon()
    {
        $midnightLondon = LocalDate::of(2012, 6, 28)->atStartOfDayWithZone(self::ZONE_LONDON());
        $midnightParis1 = LocalDate::of(2012, 6, 29)->atStartOfDayWithZone(self::ZONE_PARIS());
        $oneAm1 = LocalDateTime::of(2012, 6, 29, 1, 0)->atZone(self::ZONE_PARIS());
        $midnightParis2 = LocalDate::of(2012, 6, 30)->atStartOfDayWithZone(self::ZONE_PARIS());

        $this->assertEquals($midnightLondon->until($midnightParis1, CU::HOURS()), 23);
        $this->assertEquals($midnightLondon->until($oneAm1, CU::HOURS()), 24);
        $this->assertEquals($midnightLondon->until($midnightParis2, CU::HOURS()), 23 + 24);

        $this->assertEquals($midnightLondon->until($midnightParis1, CU::DAYS()), 0);
        $this->assertEquals($midnightLondon->until($oneAm1, CU::DAYS()), 1);
        $this->assertEquals($midnightLondon->until($midnightParis2, CU::DAYS()), 1);
    }


    public function test_until_gap()
    {
        $before = $this->TEST_PARIS_GAP_2008_03_30_02_30->withHour(0)->withMinute(0)->atZone(self::ZONE_PARIS());
        $after = $this->TEST_PARIS_GAP_2008_03_30_02_30->withHour(0)->withMinute(0)->plusDays(1)->atZone(self::ZONE_PARIS());

        $this->assertEquals($before->until($after, CU::HOURS()), 23);
        $this->assertEquals($before->until($after, CU::DAYS()), 1);
    }


    public function test_until_overlap()
    {
        $before = self::TEST_PARIS_OVERLAP_2008_10_26_02_30()->withHour(0)->withMinute(0)->atZone(self::ZONE_PARIS());
        $after = self::TEST_PARIS_OVERLAP_2008_10_26_02_30()->withHour(0)->withMinute(0)->plusDays(1)->atZone(self::ZONE_PARIS());

        $this->assertEquals($before->until($after, CU::HOURS()), 25);
        $this->assertEquals($before->until($after, CU::DAYS()), 1);
    }

    /**
     * @expectedException \Celest\DateTimeException
     */
    public function test_until_differentType()
    {
        $this->TEST_DATE_TIME_PARIS->until(self::TEST_LOCAL_2008_06_30_11_30_59_500(), CU::DAYS());
    }

    public function test_until_nullTemporal()
    {
        TestHelper::assertNullException($this, function () {
            $this->TEST_DATE_TIME_PARIS->until(null, CU::DAYS());
        });
    }

    public function test_until_nullUnit()
    {
        TestHelper::assertNullException($this, function () {
            $this->TEST_DATE_TIME_PARIS->until($this->TEST_DATE_TIME_PARIS, null);
        });
    }

    //-----------------------------------------------------------------------
    // format(DateTimeFormatter)
    //-----------------------------------------------------------------------

    public function test_format_formatter()
    {
        $f = DateTimeFormatter::ofPattern("y M d H m s");
        $z = ZonedDateTime::ofDateTime($this->dateTime(2010, 12, 3, 11, 30), self::ZONE_PARIS())->format($f);
        $this->assertEquals($z, "2010 12 3 11 30 0");
    }

    public function test_format_formatter_null()
    {
        TestHelper::assertNullException($this, function () {
            ZonedDateTime::ofDateTime($this->dateTime(2010, 12, 3, 11, 30), self::ZONE_PARIS())->format(null);
        });
    }

    //-----------------------------------------------------------------------
    // toOffsetDateTime()
    //-----------------------------------------------------------------------

    public function test_toOffsetDateTime()
    {
        $this->assertEquals($this->TEST_DATE_TIME->toOffsetDateTime(), OffsetDateTime::ofDateTime($this->TEST_DATE_TIME->toLocalDateTime(), $this->TEST_DATE_TIME->getOffset()));
    }

    public function toDateTime_data()
    {
        return [
            [ZonedDateTime::of(2008, 6, 30, 11, 30, 39, 1337, self::ZONE_0100())],
            [ZonedDateTime::of(2008, 6, 30, 11, 30, 39, 1337, self::ZONE_PARIS())],
        ];
    }

    /**
     * @dataProvider toDateTime_data
     */
    public function test_toDateTime(ZonedDateTime $zdt)
    {
        if ($zdt->getZone()->getId()[0] === '+') {
            // https://github.com/facebook/hhvm/issues/6783
            $this->markTestSkipped("HHVM doesn't support Offset based timezones");
        }

        $dt = $zdt->toNativeDateTime();
        $this->markTestIncomplete('Need to implement cheks');
    }

    //-----------------------------------------------------------------------
    // toInstant()
    //-----------------------------------------------------------------------
    function data_toInstant()
    {
        return [
            [LocalDateTime::of(1970, 1, 1, 0, 0, 0, 0), 0, 0],
            [LocalDateTime::of(1970, 1, 1, 0, 0, 0, 1), 0, 1],
            [LocalDateTime::of(1970, 1, 1, 0, 0, 0, 999999999), 0, 999999999],
            [LocalDateTime::of(1970, 1, 1, 0, 0, 1, 0), 1, 0],
            [LocalDateTime::of(1970, 1, 1, 0, 0, 1, 1), 1, 1],
            [LocalDateTime::of(1969, 12, 31, 23, 59, 59, 999999999), -1, 999999999],
            [LocalDateTime::of(1970, 1, 2, 0, 0), 24 * 60 * 60, 0],
            [LocalDateTime::of(1969, 12, 31, 0, 0), -24 * 60 * 60, 0],
        ];
    }

    /**
     * @dataProvider data_toInstant
     */
    public function test_toInstant_UTC(LocalDateTime $ldt, $expectedEpSec, $expectedNos)
    {
        $dt = $ldt->atZone(ZoneOffset::UTC());
        $test = $dt->toInstant();
        $this->assertEquals($test->getEpochSecond(), $expectedEpSec);
        $this->assertEquals($test->getNano(), $expectedNos);
    }

    /**
     * @dataProvider data_toInstant
     */
    public function test_toInstant_P0100(LocalDateTime $ldt, $expectedEpSec, $expectedNos)
    {
        $dt = $ldt->atZone(self::ZONE_0100());
        $test = $dt->toInstant();
        $this->assertEquals($test->getEpochSecond(), $expectedEpSec - 3600);
        $this->assertEquals($test->getNano(), $expectedNos);
    }

    /**
     * @dataProvider data_toInstant
     */
    public function test_toInstant_M0100(LocalDateTime $ldt, $expectedEpSec, $expectedNos)
    {
        $dt = $ldt->atZone(self::ZONE_M0100());
        $test = $dt->toInstant();
        $this->assertEquals($test->getEpochSecond(), $expectedEpSec + 3600);
        $this->assertEquals($test->getNano(), $expectedNos);
    }

    //-----------------------------------------------------------------------
    // toEpochSecond()
    //-----------------------------------------------------------------------

    /**
     * @group long
     */
    public function test_toEpochSecond_afterEpoch()
    {
        $ldt = LocalDateTime::of(1970, 1, 1, 0, 0)->plusHours(1);
        for ($i = 0; $i < 100000; $i++) {
            $a = ZonedDateTime::ofDateTime($ldt, self::ZONE_PARIS());
            $this->assertEquals($a->toEpochSecond(), $i);
            $ldt = $ldt->plusSeconds(1);
        }
    }

    /**
     * @group long
     */
    public function test_toEpochSecond_beforeEpoch()
    {
        $ldt = LocalDateTime::of(1970, 1, 1, 0, 0)->plusHours(1);
        for ($i = 0; $i < 100000; $i++) {
            $a = ZonedDateTime::ofDateTime($ldt, self::ZONE_PARIS());
            $this->assertEquals($a->toEpochSecond(), -$i);
            $ldt = $ldt->minusSeconds(1);
        }
    }

    /**
     * @dataProvider data_toInstant
     */
    public function test_toEpochSecond_UTC(LocalDateTime $ldt, $expectedEpSec, $expectedNos)
    {
        $dt = $ldt->atZone(ZoneOffset::UTC());
        $this->assertEquals($dt->toEpochSecond(), $expectedEpSec);
    }

    /**
     * @dataProvider data_toInstant
     */
    public function test_toEpochSecond_P0100(LocalDateTime $ldt, $expectedEpSec, $expectedNos)
    {
        $dt = $ldt->atZone(self::ZONE_0100());
        $this->assertEquals($dt->toEpochSecond(), $expectedEpSec - 3600);
    }

    /**
     * @dataProvider data_toInstant
     */
    public function test_toEpochSecond_M0100(LocalDateTime $ldt, $expectedEpSec, $expectedNos)
    {
        $dt = $ldt->atZone(self::ZONE_M0100());
        $this->assertEquals($dt->toEpochSecond(), $expectedEpSec + 3600);
    }

    //-----------------------------------------------------------------------
    // compareTo()
    //-----------------------------------------------------------------------

    public function test_compareTo_time1()
    {
        $a = ZonedDateTime::of(2008, 6, 30, 11, 30, 39, 0, self::ZONE_0100());
        $b = ZonedDateTime::of(2008, 6, 30, 11, 30, 41, 0, self::ZONE_0100());  // $a is $before $b due to time
        $this->assertEquals($a->compareTo($b) < 0, true);
        $this->assertEquals($b->compareTo($a) > 0, true);
        $this->assertEquals($a->compareTo($a) == 0, true);
        $this->assertEquals($b->compareTo($b) == 0, true);
    }


    public function test_compareTo_time2()
    {
        $a = ZonedDateTime::of(2008, 6, 30, 11, 30, 40, 4, self::ZONE_0100());
        $b = ZonedDateTime::of(2008, 6, 30, 11, 30, 40, 5, self::ZONE_0100());  // $a is $before $b due to time
        $this->assertEquals($a->compareTo($b) < 0, true);
        $this->assertEquals($b->compareTo($a) > 0, true);
        $this->assertEquals($a->compareTo($a) == 0, true);
        $this->assertEquals($b->compareTo($b) == 0, true);
    }


    public function test_compareTo_offset1()
    {
        $a = ZonedDateTime::of(2008, 6, 30, 11, 30, 41, 0, self::ZONE_0200());
        $b = ZonedDateTime::of(2008, 6, 30, 11, 30, 39, 0, self::ZONE_0100());  // $a is $before $b due to $offset
        $this->assertEquals($a->compareTo($b) < 0, true);
        $this->assertEquals($b->compareTo($a) > 0, true);
        $this->assertEquals($a->compareTo($a) == 0, true);
        $this->assertEquals($b->compareTo($b) == 0, true);
    }


    public function test_compareTo_offset2()
    {
        $a = ZonedDateTime::of(2008, 6, 30, 11, 30, 40, 5, ZoneId::of("UTC+01:01"));
        $b = ZonedDateTime::of(2008, 6, 30, 11, 30, 40, 4, self::ZONE_0100());  // $a is $before $b due to $offset
        $this->assertEquals($a->compareTo($b) < 0, true);
        $this->assertEquals($b->compareTo($a) > 0, true);
        $this->assertEquals($a->compareTo($a) == 0, true);
        $this->assertEquals($b->compareTo($b) == 0, true);
    }


    public function test_compareTo_both()
    {
        $a = ZonedDateTime::of(2008, 6, 30, 11, 50, 0, 0, self::ZONE_0200());
        $b = ZonedDateTime::of(2008, 6, 30, 11, 20, 0, 0, self::ZONE_0100());  // $a is $before $b on $instant scale
        $this->assertEquals($a->compareTo($b) < 0, true);
        $this->assertEquals($b->compareTo($a) > 0, true);
        $this->assertEquals($a->compareTo($a) == 0, true);
        $this->assertEquals($b->compareTo($b) == 0, true);
    }


    public function test_compareTo_bothNanos()
    {
        $a = ZonedDateTime::of(2008, 6, 30, 11, 20, 40, 5, self::ZONE_0200());
        $b = ZonedDateTime::of(2008, 6, 30, 10, 20, 40, 6, self::ZONE_0100());  // $a is $before $b on $instant scale
        $this->assertEquals($a->compareTo($b) < 0, true);
        $this->assertEquals($b->compareTo($a) > 0, true);
        $this->assertEquals($a->compareTo($a) == 0, true);
        $this->assertEquals($b->compareTo($b) == 0, true);
    }


    public function test_compareTo_hourDifference()
    {
        $a = ZonedDateTime::of(2008, 6, 30, 10, 0, 0, 0, self::ZONE_0100());
        $b = ZonedDateTime::of(2008, 6, 30, 11, 0, 0, 0, self::ZONE_0200());  // $a is $before $b despite being same time-line time
        $this->assertEquals($a->compareTo($b) < 0, true);
        $this->assertEquals($b->compareTo($a) > 0, true);
        $this->assertEquals($a->compareTo($a) == 0, true);
        $this->assertEquals($b->compareTo($b) == 0, true);
    }

    public function test_compareTo_null()
    {
        TestHelper::assertNullException($this, function () {

            $a = ZonedDateTime::of(2008, 6, 30, 23, 30, 59, 0, self::ZONE_0100());
            $a->compareTo(null);
        });

    }

    public function provider_datetime_conversion_data()
    {
        return [
            [ZonedDateTime::of(2008, 6, 30, 10, 12, 35, 12345000, self::ZONE_0100())],
            [ZonedDateTime::of(2008, 6, 30, 10, 12, 35, 12345000, self::ZONE_LONDON())],
        ];
    }

    /**
     * @dataProvider provider_datetime_conversion_data
     */
    public function test_datetime_conversion(ZonedDateTime $zdt)
    {
        if ($zdt->getZone()->getId()[0] === '+') {
            // https://github.com/facebook/hhvm/issues/6783
            $this->markTestSkipped("HHVM doesn't support Offset based timezones");
        }

        $dt = $zdt->toNativeDateTime();
        $zdt2 = ZonedDateTime::ofNativeDateTime($dt);
        $this->assertEquals($zdt, $zdt2);
    }

    //-----------------------------------------------------------------------
    // isBefore()
    //-----------------------------------------------------------------------
    function data_isBefore()
    {
        return [
            [11, 30, self::ZONE_0100(), 11, 31, self::ZONE_0100(), true], // $a is $before $b due to time
            [11, 30, self::ZONE_0200(), 11, 30, self::ZONE_0100(), true], // $a is $before $b due to $offset
            [11, 30, self::ZONE_0200(), 10, 30, self::ZONE_0100(), false], // $a is equal $b due to same $instant
        ];
    }

    /**
     * @dataProvider data_isBefore
     */
    public function test_isBefore($hour1, $minute1, ZoneId $zone1, $hour2, $minute2, ZoneId $zone2, $expected)
    {
        $a = ZonedDateTime::of(2008, 6, 30, $hour1, $minute1, 0, 0, $zone1);
        $b = ZonedDateTime::of(2008, 6, 30, $hour2, $minute2, 0, 0, $zone2);
        $this->assertEquals($a->isBefore($b), $expected);
        $this->assertEquals($b->isBefore($a), false);
        $this->assertEquals($a->isBefore($a), false);
        $this->assertEquals($b->isBefore($b), false);
    }

    public function test_isBefore_null()
    {
        TestHelper::assertNullException($this, function () {
            $a = ZonedDateTime::of(2008, 6, 30, 23, 30, 59, 0, self::ZONE_0100());
            $a->isBefore(null);
        });

    }

    //-----------------------------------------------------------------------
    // isAfter()
    //-----------------------------------------------------------------------
    function data_isAfter()
    {
        return [
            [11, 31, self::ZONE_0100(), 11, 30, self::ZONE_0100(), true], // $a is $after $b due to time
            [11, 30, self::ZONE_0100(), 11, 30, self::ZONE_0200(), true], // $a is $after $b due to $offset
            [11, 30, self::ZONE_0200(), 10, 30, self::ZONE_0100(), false], // $a is equal $b due to same $instant
        ];
    }

    /**
     * @dataProvider data_isAfter
     */
    public function test_isAfter($hour1, $minute1, ZoneId $zone1, $hour2, $minute2, ZoneId $zone2, $expected)
    {
        $a = ZonedDateTime::of(2008, 6, 30, $hour1, $minute1, 0, 0, $zone1);
        $b = ZonedDateTime::of(2008, 6, 30, $hour2, $minute2, 0, 0, $zone2);
        $this->assertEquals($a->isAfter($b), $expected);
        $this->assertEquals($b->isAfter($a), false);
        $this->assertEquals($a->isAfter($a), false);
        $this->assertEquals($b->isAfter($b), false);
    }

    public function test_isAfter_null()
    {
        TestHelper::assertNullException($this, function () {
            $a = ZonedDateTime::of(2008, 6, 30, 23, 30, 59, 0, self::ZONE_0100());
            $a->isAfter(null);
        });
    }

    //-----------------------------------------------------------------------
    // equals() / hashCode()
    //-----------------------------------------------------------------------
    /**
     * @dataProvider provider_sampleTimes
     */
    public function test_equals_true($y, $o, $d, $h, $m, $s, $n, ZoneId $ignored)
    {
        $a = ZonedDateTime::ofDateTime($this->dateTime($y, $o, $d, $h, $m, $s, $n), self::ZONE_0100());
        $b = ZonedDateTime::ofDateTime($this->dateTime($y, $o, $d, $h, $m, $s, $n), self::ZONE_0100());
        $this->assertEquals($a->equals($b), true);
        //$this->assertEquals($a->hashCode() == $b->hashCode(), true);
    }

    /**
     * @dataProvider provider_sampleTimes
     */
    public function test_equals_false_year_differs($y, $o, $d, $h, $m, $s, $n, ZoneId $ignored)
    {
        $a = ZonedDateTime::ofDateTime($this->dateTime($y, $o, $d, $h, $m, $s, $n), self::ZONE_0100());
        $b = ZonedDateTime::ofDateTime($this->dateTime($y + 1, $o, $d, $h, $m, $s, $n), self::ZONE_0100());
        $this->assertEquals($a->equals($b), false);
    }

    /**
     * @dataProvider provider_sampleTimes
     */
    public function test_equals_false_hour_differs($y, $o, $d, $h, $m, $s, $n, ZoneId $ignored)
    {
        $h = ($h == 23 ? 22 : $h);
        $a = ZonedDateTime::ofDateTime($this->dateTime($y, $o, $d, $h, $m, $s, $n), self::ZONE_0100());
        $b = ZonedDateTime::ofDateTime($this->dateTime($y, $o, $d, $h + 1, $m, $s, $n), self::ZONE_0100());
        $this->assertEquals($a->equals($b), false);
    }

    /**
     * @dataProvider provider_sampleTimes
     */
    public function test_equals_false_minute_differs($y, $o, $d, $h, $m, $s, $n, ZoneId $ignored)
    {
        $m = ($m == 59 ? 58 : $m);
        $a = ZonedDateTime::ofDateTime($this->dateTime($y, $o, $d, $h, $m, $s, $n), self::ZONE_0100());
        $b = ZonedDateTime::ofDateTime($this->dateTime($y, $o, $d, $h, $m + 1, $s, $n), self::ZONE_0100());
        $this->assertEquals($a->equals($b), false);
    }

    /**
     * @dataProvider provider_sampleTimes
     */
    public function test_equals_false_second_differs($y, $o, $d, $h, $m, $s, $n, ZoneId $ignored)
    {
        $s = ($s == 59 ? 58 : $s);
        $a = ZonedDateTime::ofDateTime($this->dateTime($y, $o, $d, $h, $m, $s, $n), self::ZONE_0100());
        $b = ZonedDateTime::ofDateTime($this->dateTime($y, $o, $d, $h, $m, $s + 1, $n), self::ZONE_0100());
        $this->assertEquals($a->equals($b), false);
    }

    /**
     * @dataProvider provider_sampleTimes
     */
    public function test_equals_false_nano_differs($y, $o, $d, $h, $m, $s, $n, ZoneId $ignored)
    {
        $n = ($n == 999999999 ? 999999998 : $n);
        $a = ZonedDateTime::ofDateTime($this->dateTime($y, $o, $d, $h, $m, $s, $n), self::ZONE_0100());
        $b = ZonedDateTime::ofDateTime($this->dateTime($y, $o, $d, $h, $m, $s, $n + 1), self::ZONE_0100());
        $this->assertEquals($a->equals($b), false);
    }

    /**
     * @dataProvider provider_sampleTimes
     */
    public function test_equals_false_offset_differs($y, $o, $d, $h, $m, $s, $n, ZoneId $ignored)
    {
        $a = ZonedDateTime::ofDateTime($this->dateTime($y, $o, $d, $h, $m, $s, $n), self::ZONE_0100());
        $b = ZonedDateTime::ofDateTime($this->dateTime($y, $o, $d, $h, $m, $s, $n), self::ZONE_0200());
        $this->assertEquals($a->equals($b), false);
    }


    public function test_equals_itself_true()
    {
        $this->assertEquals($this->TEST_DATE_TIME->equals($this->TEST_DATE_TIME), true);
    }


    public function test_equals_string_false()
    {
        $this->assertEquals($this->TEST_DATE_TIME->equals("2007-07-15"), false);
    }

    //-----------------------------------------------------------------------
    // toString()
    //-----------------------------------------------------------------------
    function provider_sampleToString()
    {
        return [
            [2008, 6, 30, 11, 30, 59, 0, "Z", "2008-06-30T11:30:59Z"],
            [2008, 6, 30, 11, 30, 59, 0, "+01:00", "2008-06-30T11:30:59+01:00"],
            [2008, 6, 30, 11, 30, 59, 999000000, "Z", "2008-06-30T11:30:59.999Z"],
            [2008, 6, 30, 11, 30, 59, 999000000, "+01:00", "2008-06-30T11:30:59.999+01:00"],
            [2008, 6, 30, 11, 30, 59, 999000, "Z", "2008-06-30T11:30:59.000999Z"],
            [2008, 6, 30, 11, 30, 59, 999000, "+01:00", "2008-06-30T11:30:59.000999+01:00"],
            [2008, 6, 30, 11, 30, 59, 999, "Z", "2008-06-30T11:30:59.000000999Z"],
            [2008, 6, 30, 11, 30, 59, 999, "+01:00", "2008-06-30T11:30:59.000000999+01:00"],

            [2008, 6, 30, 11, 30, 59, 999, "Europe/London", "2008-06-30T11:30:59.000000999+01:00[Europe/London]"],
            [2008, 6, 30, 11, 30, 59, 999, "Europe/Paris", "2008-06-30T11:30:59.000000999+02:00[Europe/Paris]"],
        ];
    }

    /**
     * @dataProvider provider_sampleToString
     */
    public function test_toString($y, $o, $d, $h, $m, $s, $n, $zoneId, $expected)
    {
        $z = ZonedDateTime::ofDateTime($this->dateTime($y, $o, $d, $h, $m, $s, $n), ZoneId::of($zoneId));
        $str = $z->__toString();
        $this->assertEquals($str, $expected);
    }

    private static function dateTime(
        $year, $month, $dayOfMonth,
        $hour, $minute, $second = 0, $nanoOfSecond = 0)
    {
        return LocalDateTime::of($year, $month, $dayOfMonth, $hour, $minute, $second, $nanoOfSecond);
    }

    private static function dateTimeZoned(
        $year, $month, $dayOfMonth,
        $hour, $minute, $second, $nanoOfSecond, ZoneOffset $offset, ZoneId $zoneId)
    {
        return ZonedDateTime::ofStrict(LocalDateTime::of($year, $month, $dayOfMonth, $hour, $minute, $second, $nanoOfSecond), $offset, $zoneId);
    }

}
