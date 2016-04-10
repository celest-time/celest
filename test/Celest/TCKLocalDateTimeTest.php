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
use Celest\Helper\Integer;
use Celest\Helper\Long;
use Celest\Helper\Math;
use Celest\Temporal\ChronoField as CF;
use Celest\Temporal\ChronoUnit as CU;
use Celest\Temporal\JulianFields;
use Celest\Temporal\Temporal;
use Celest\Temporal\TemporalAccessor;
use Celest\Temporal\TemporalAdjusters;
use Celest\Temporal\TemporalQueries;
use Celest\Temporal\TemporalQuery;
use Celest\Temporal\TemporalUnit;

/**
 * Test LocalDateTime
 */
class TCKLocalDateTimeTest extends AbstractDateTimeTest
{

    private static function OFFSET_PONE()
    {
        return ZoneOffset::ofHours(1);
    }

    private static function OFFSET_PTWO()
    {
        return ZoneOffset::ofHours(2);
    }

    private static function OFFSET_MTWO()
    {
        return ZoneOffset::ofHours(-2);
    }

    private static function ZONE_PARIS()
    {
        return ZoneId::of("Europe/Paris");
    }

    private static function ZONE_GAZA()
    {
        return ZoneId::of("Asia/Gaza");
    }

    private static function TEST_200707_15_12_30_40_987654321()
    {
        return LocalDateTime::of(2007, 7, 15, 12, 30, 40, 987654321);
    }

    /** @var LocalDateTime */
    private $MAX_DATE_TIME;
    /** @var LocalDateTime */
    private $MIN_DATE_TIME;
    /** @var Instant */
    private $MAX_INSTANT;
    /** @var Instant */
    private $MIN_INSTANT;

    public function setUp()
    {
        $this->MAX_DATE_TIME = LocalDateTime::MAX();
        $this->MIN_DATE_TIME = LocalDateTime::MIN();
        $this->MAX_INSTANT = $this->MAX_DATE_TIME->atZone(ZoneOffset::UTC())->toInstant();
        $this->MIN_INSTANT = $this->MIN_DATE_TIME->atZone(ZoneOffset::UTC())->toInstant();
    }

    //-----------------------------------------------------------------------

    protected function samples()
    {
        return [self::TEST_200707_15_12_30_40_987654321(), LocalDateTime::MAX(), LocalDateTime::MIN()];
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
            JulianFields::JULIAN_DAY(),
            JulianFields::MODIFIED_JULIAN_DAY(),
            JulianFields::RATA_DIE(),
        ];
    }


    protected function invalidFields()
    {
        /*List<TemporalField> list = new ArrayList<>(Arrays.<TemporalField>asList(CF::values()));
                list.removeAll(validFields());*/
        return [];
    }

    //-----------------------------------------------------------------------
    private function check(LocalDateTime $test, $y, $m, $d, $h, $mi, $s, $n)
    {
        $this->assertEquals($test->getYear(), $y);
        $this->assertEquals($test->getMonth()->getValue(), $m);
        $this->assertEquals($test->getDayOfMonth(), $d);
        $this->assertEquals($test->getHour(), $h);
        $this->assertEquals($test->getMinute(), $mi);
        $this->assertEquals($test->getSecond(), $s);
        $this->assertEquals($test->getNano(), $n);
        $this->assertEquals($test, $test);
        //$this->assertEquals($test->hashCode(), $test->hashCode());
        $this->assertEquals(LocalDateTime::of($y, $m, $d, $h, $mi, $s, $n), $test);
    }

    private function createDateMidnight($year, $month, $day)
    {
        return LocalDateTime::of($year, $month, $day, 0, 0);
    }

//-----------------------------------------------------------------------
// constants
//-----------------------------------------------------------------------
    public function test_constant_MIN()
    {
        $this->check(LocalDateTime::MIN(), Year::MIN_VALUE, 1, 1, 0, 0, 0, 0);
    }

    public function test_constant_MAX()
    {
        $this->check(LocalDateTime::MAX(), Year::MAX_VALUE, 12, 31, 23, 59, 59, 999999999);
    }

//-----------------------------------------------------------------------
// now()
//-----------------------------------------------------------------------
    public function test_now()
    {
        $expected = LocalDateTime::nowOf(Clock::systemDefaultZone());
        $test = LocalDateTime::now();
        $diff = Math::abs($test->toLocalTime()->toNanoOfDay() - $expected->toLocalTime()->toNanoOfDay());
        if ($diff >= 100000000) {
            // may be $date change
            $expected = LocalDateTime::nowOf(Clock::systemDefaultZone());
            $test = LocalDateTime::now();
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
            LocalDateTime::nowIn(null);
        });
    }


    public function test_now_ZoneId()
    {
        $zone = ZoneId::of("UTC+01:02:03");
        $expected = LocalDateTime::nowOf(Clock::system($zone));
        $test = LocalDateTime::nowIn($zone);
        for ($i = 0; $i < 100; $i++) {
            if ($expected->equals($test)) {
                return;
            }
            $expected = LocalDateTime::nowOf(Clock::system($zone));
            $test = LocalDateTime::nowIn($zone);
        }
        $this->assertEquals($test, $expected);
    }

//-----------------------------------------------------------------------
// now(Clock)
//-----------------------------------------------------------------------
    public function test_now_Clock_nullClock()
    {
        TestHelper::assertNullException($this, function () {
            LocalDateTime::nowOf(null);
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
            $test = LocalDateTime::nowOf($clock);
            $this->assertEquals($test->getYear(), 1970);
            $this->assertEquals($test->getMonth(), Month::JANUARY());
            $this->assertEquals($test->getDayOfMonth(), ($i < 24 * 60 * 60 ? 1 : 2));
            $this->assertEquals($test->getHour(), ($i / (60 * 60)) % 24);
            $this->assertEquals($test->getMinute(), ($i / 60) % 60);
            $this->assertEquals($test->getSecond(), $i % 60);
            $this->assertEquals($test->getNano(), 123456789);
        }
    }

    /**
     * @group long
     */
    public function test_now_Clock_allSecsInDay_offset()
    {
        for ($i = 0; $i < (2 * 24 * 60 * 60); $i++) {
            $instant = Instant::ofEpochSecond($i)->plusNanos(123456789);
            $clock = Clock::fixed($instant->minusSeconds(self::OFFSET_PONE()->getTotalSeconds()), self::OFFSET_PONE());
            $test = LocalDateTime::nowOf($clock);
            $this->assertEquals($test->getYear(), 1970);
            $this->assertEquals($test->getMonth(), Month::JANUARY());
            $this->assertEquals($test->getDayOfMonth(), ($i < 24 * 60 * 60) ? 1 : 2);
            $this->assertEquals($test->getHour(), ($i / (60 * 60)) % 24);
            $this->assertEquals($test->getMinute(), ($i / 60) % 60);
            $this->assertEquals($test->getSecond(), $i % 60);
            $this->assertEquals($test->getNano(), 123456789);
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
            $test = LocalDateTime::nowOf($clock);
            $this->assertEquals($test->getYear(), 1969);
            $this->assertEquals($test->getMonth(), Month::DECEMBER());
            $this->assertEquals($test->getDayOfMonth(), 31);
            $expected = $expected->minusSeconds(1);
            $this->assertEquals($test->toLocalTime(), $expected);
        }
    }

//-----------------------------------------------------------------------

    public function test_now_Clock_maxYear()
    {
        $clock = Clock::fixed($this->MAX_INSTANT, ZoneOffset::UTC());
        $test = LocalDateTime::nowOf($clock);
        $this->assertEquals($test, $this->MAX_DATE_TIME);
    }

    /**
     * @expectedException \Celest\DateTimeException
     */
    public function test_now_Clock_tooBig()
    {
        $clock = Clock::fixed($this->MAX_INSTANT->plusSeconds(24 * 60 * 60), ZoneOffset::UTC());
        LocalDateTime::nowOf($clock);
    }


    public function test_now_Clock_minYear()
    {
        $clock = Clock::fixed($this->MIN_INSTANT, ZoneOffset::UTC());
        $test = LocalDateTime::nowOf($clock);
        $this->assertEquals($test, $this->MIN_DATE_TIME);
    }

    /**
     * @expectedException \Celest\DateTimeException
     */
    public function test_now_Clock_tooLow()
    {
        $clock = Clock::fixed($this->MIN_INSTANT->minusNanos(1), ZoneOffset::UTC());
        LocalDateTime::nowOf($clock);
    }

    //-----------------------------------------------------------------------
    // of() factories
    //-----------------------------------------------------------------------
    //-----------------------------------------------------------------------

    public function test_factory_of_4intsMonth()
    {
        $dateTime = LocalDateTime::ofMonth(2007, Month::JULY(), 15, 12, 30);
        $this->check($dateTime, 2007, 7, 15, 12, 30, 0, 0);
    }

    /**
     * @expectedException \Celest\DateTimeException
     */
    public function test_factory_of_4intsMonth_yearTooLow()
    {
        LocalDateTime::ofMonth(Integer::MIN_VALUE, Month::JULY(), 15, 12, 30);
    }

    public function test_factory_of_4intsMonth_nullMonth()
    {
        TestHelper::assertNullException($this, function () {
            LocalDateTime::ofMonth(2007, null, 15, 12, 30);
        });
    }

    /**
     * @expectedException \Celest\DateTimeException
     */
    public function test_factory_of_4intsMonth_dayTooLow()
    {
        LocalDateTime::ofMonth(2007, Month::JULY(), -1, 12, 30);
    }

    /**
     * @expectedException \Celest\DateTimeException
     */
    public function test_factory_of_4intsMonth_dayTooHigh()
    {
        LocalDateTime::ofMonth(2007, Month::JULY(), 32, 12, 30);
    }

    /**
     * @expectedException \Celest\DateTimeException
     */
    public function test_factory_of_4intsMonth_hourTooLow()
    {
        LocalDateTime::ofMonth(2007, Month::JULY(), 15, -1, 30);
    }

    /**
     * @expectedException \Celest\DateTimeException
     */
    public function test_factory_of_4intsMonth_hourTooHigh()
    {
        LocalDateTime::ofMonth(2007, Month::JULY(), 15, 24, 30);
    }

    /**
     * @expectedException \Celest\DateTimeException
     */
    public function test_factory_of_4intsMonth_minuteTooLow()
    {
        LocalDateTime::ofMonth(2007, Month::JULY(), 15, 12, -1);
    }

    /**
     * @expectedException \Celest\DateTimeException
     */
    public function test_factory_of_4intsMonth_minuteTooHigh()
    {
        LocalDateTime::ofMonth(2007, Month::JULY(), 15, 12, 60);
    }

    //-----------------------------------------------------------------------

    public function test_factory_of_5intsMonth()
    {
        $dateTime = LocalDateTime::ofMonth(2007, Month::JULY(), 15, 12, 30, 40);
        $this->check($dateTime, 2007, 7, 15, 12, 30, 40, 0);
    }

    /**
     * @expectedException \Celest\DateTimeException
     */
    public function test_factory_of_5intsMonth_yearTooLow()
    {
        LocalDateTime::ofMonth(Integer::MIN_VALUE, Month::JULY(), 15, 12, 30, 40);
    }

    public function test_factory_of_5intsMonth_nullMonth()
    {
        TestHelper::assertNullException($this, function () {
            LocalDateTime::ofMonth(2007, null, 15, 12, 30, 40);
        });
    }

    /**
     * @expectedException \Celest\DateTimeException
     */
    public function test_factory_of_5intsMonth_dayTooLow()
    {
        LocalDateTime::ofMonth(2007, Month::JULY(), -1, 12, 30, 40);
    }

    /**
     * @expectedException \Celest\DateTimeException
     */
    public function test_factory_of_5intsMonth_dayTooHigh()
    {
        LocalDateTime::ofMonth(2007, Month::JULY(), 32, 12, 30, 40);
    }

    /**
     * @expectedException \Celest\DateTimeException
     */
    public function test_factory_of_5intsMonth_hourTooLow()
    {
        LocalDateTime::ofMonth(2007, Month::JULY(), 15, -1, 30, 40);
    }

    /**
     * @expectedException \Celest\DateTimeException
     */
    public function test_factory_of_5intsMonth_hourTooHigh()
    {
        LocalDateTime::ofMonth(2007, Month::JULY(), 15, 24, 30, 40);
    }

    /**
     * @expectedException \Celest\DateTimeException
     */
    public function test_factory_of_5intsMonth_minuteTooLow()
    {
        LocalDateTime::ofMonth(2007, Month::JULY(), 15, 12, -1, 40);
    }

    /**
     * @expectedException \Celest\DateTimeException
     */
    public function test_factory_of_5intsMonth_minuteTooHigh()
    {
        LocalDateTime::ofMonth(2007, Month::JULY(), 15, 12, 60, 40);
    }

    /**
     * @expectedException \Celest\DateTimeException
     */
    public function test_factory_of_5intsMonth_secondTooLow()
    {
        LocalDateTime::ofMonth(2007, Month::JULY(), 15, 12, 30, -1);
    }

    /**
     * @expectedException \Celest\DateTimeException
     */
    public function test_factory_of_5intsMonth_secondTooHigh()
    {
        LocalDateTime::ofMonth(2007, Month::JULY(), 15, 12, 30, 60);
    }

    //-----------------------------------------------------------------------

    public function test_factory_of_6intsMonth()
    {
        $dateTime = LocalDateTime::ofMonth(2007, Month::JULY(), 15, 12, 30, 40, 987654321);
        $this->check($dateTime, 2007, 7, 15, 12, 30, 40, 987654321);
    }

    /**
     * @expectedException \Celest\DateTimeException
     */
    public function test_factory_of_6intsMonth_yearTooLow()
    {
        LocalDateTime::ofMonth(Integer::MIN_VALUE, Month::JULY(), 15, 12, 30, 40, 987654321);
    }

    public function test_factory_of_6intsMonth_nullMonth()
    {
        TestHelper::assertNullException($this, function () {
            LocalDateTime::ofMonth(2007, null, 15, 12, 30, 40, 987654321);
        });
    }

    /**
     * @expectedException \Celest\DateTimeException
     */
    public function test_factory_of_6intsMonth_dayTooLow()
    {
        LocalDateTime::ofMonth(2007, Month::JULY(), -1, 12, 30, 40, 987654321);
    }

    /**
     * @expectedException \Celest\DateTimeException
     */
    public function test_factory_of_6intsMonth_dayTooHigh()
    {
        LocalDateTime::ofMonth(2007, Month::JULY(), 32, 12, 30, 40, 987654321);
    }

    /**
     * @expectedException \Celest\DateTimeException
     */
    public function test_factory_of_6intsMonth_hourTooLow()
    {
        LocalDateTime::ofMonth(2007, Month::JULY(), 15, -1, 30, 40, 987654321);
    }

    /**
     * @expectedException \Celest\DateTimeException
     */
    public function test_factory_of_6intsMonth_hourTooHigh()
    {
        LocalDateTime::ofMonth(2007, Month::JULY(), 15, 24, 30, 40, 987654321);
    }

    /**
     * @expectedException \Celest\DateTimeException
     */
    public function test_factory_of_6intsMonth_minuteTooLow()
    {
        LocalDateTime::ofMonth(2007, Month::JULY(), 15, 12, -1, 40, 987654321);
    }

    /**
     * @expectedException \Celest\DateTimeException
     */
    public function test_factory_of_6intsMonth_minuteTooHigh()
    {
        LocalDateTime::ofMonth(2007, Month::JULY(), 15, 12, 60, 40, 987654321);
    }

    /**
     * @expectedException \Celest\DateTimeException
     */
    public function test_factory_of_6intsMonth_secondTooLow()
    {
        LocalDateTime::ofMonth(2007, Month::JULY(), 15, 12, 30, -1, 987654321);
    }

    /**
     * @expectedException \Celest\DateTimeException
     */
    public function test_factory_of_6intsMonth_secondTooHigh()
    {
        LocalDateTime::ofMonth(2007, Month::JULY(), 15, 12, 30, 60, 987654321);
    }

    /**
     * @expectedException \Celest\DateTimeException
     */
    public function test_factory_of_6intsMonth_nanoTooLow()
    {
        LocalDateTime::ofMonth(2007, Month::JULY(), 15, 12, 30, 40, -1);
    }

    /**
     * @expectedException \Celest\DateTimeException
     */
    public function test_factory_of_6intsMonth_nanoTooHigh()
    {
        LocalDateTime::ofMonth(2007, Month::JULY(), 15, 12, 30, 40, 1000000000);
    }

    //-----------------------------------------------------------------------

    public function test_factory_of_5ints()
    {
        $dateTime = LocalDateTime::of(2007, 7, 15, 12, 30);
        $this->check($dateTime, 2007, 7, 15, 12, 30, 0, 0);
    }

    /**
     * @expectedException \Celest\DateTimeException
     */
    public function test_factory_of_5ints_yearTooLow()
    {
        LocalDateTime::of(Integer::MIN_VALUE, 7, 15, 12, 30);
    }

    /**
     * @expectedException \Celest\DateTimeException
     */
    public function test_factory_of_5ints_monthTooLow()
    {
        LocalDateTime::of(2007, 0, 15, 12, 30);
    }

    /**
     * @expectedException \Celest\DateTimeException
     */
    public function test_factory_of_5ints_monthTooHigh()
    {
        LocalDateTime::of(2007, 13, 15, 12, 30);
    }

    /**
     * @expectedException \Celest\DateTimeException
     */
    public function test_factory_of_5ints_dayTooLow()
    {
        LocalDateTime::of(2007, 7, -1, 12, 30);
    }

    /**
     * @expectedException \Celest\DateTimeException
     */
    public function test_factory_of_5ints_dayTooHigh()
    {
        LocalDateTime::of(2007, 7, 32, 12, 30);
    }

    /**
     * @expectedException \Celest\DateTimeException
     */
    public function test_factory_of_5ints_hourTooLow()
    {
        LocalDateTime::of(2007, 7, 15, -1, 30);
    }

    /**
     * @expectedException \Celest\DateTimeException
     */
    public function test_factory_of_5ints_hourTooHigh()
    {
        LocalDateTime::of(2007, 7, 15, 24, 30);
    }

    /**
     * @expectedException \Celest\DateTimeException
     */
    public function test_factory_of_5ints_minuteTooLow()
    {
        LocalDateTime::of(2007, 7, 15, 12, -1);
    }

    /**
     * @expectedException \Celest\DateTimeException
     */
    public function test_factory_of_5ints_minuteTooHigh()
    {
        LocalDateTime::of(2007, 7, 15, 12, 60);
    }

    //-----------------------------------------------------------------------

    public function test_factory_of_6ints()
    {
        $dateTime = LocalDateTime::of(2007, 7, 15, 12, 30, 40);
        $this->check($dateTime, 2007, 7, 15, 12, 30, 40, 0);
    }

    /**
     * @expectedException \Celest\DateTimeException
     */
    public function test_factory_of_6ints_yearTooLow()
    {
        LocalDateTime::of(Integer::MIN_VALUE, 7, 15, 12, 30, 40);
    }

    /**
     * @expectedException \Celest\DateTimeException
     */
    public function test_factory_of_6ints_monthTooLow()
    {
        LocalDateTime::of(2007, 0, 15, 12, 30, 40);
    }

    /**
     * @expectedException \Celest\DateTimeException
     */
    public function test_factory_of_6ints_monthTooHigh()
    {
        LocalDateTime::of(2007, 13, 15, 12, 30, 40);
    }

    /**
     * @expectedException \Celest\DateTimeException
     */
    public function test_factory_of_6ints_dayTooLow()
    {
        LocalDateTime::of(2007, 7, -1, 12, 30, 40);
    }

    /**
     * @expectedException \Celest\DateTimeException
     */
    public function test_factory_of_6ints_dayTooHigh()
    {
        LocalDateTime::of(2007, 7, 32, 12, 30, 40);
    }

    /**
     * @expectedException \Celest\DateTimeException
     */
    public function test_factory_of_6ints_hourTooLow()
    {
        LocalDateTime::of(2007, 7, 15, -1, 30, 40);
    }

    /**
     * @expectedException \Celest\DateTimeException
     */
    public function test_factory_of_6ints_hourTooHigh()
    {
        LocalDateTime::of(2007, 7, 15, 24, 30, 40);
    }

    /**
     * @expectedException \Celest\DateTimeException
     */
    public function test_factory_of_6ints_minuteTooLow()
    {
        LocalDateTime::of(2007, 7, 15, 12, -1, 40);
    }

    /**
     * @expectedException \Celest\DateTimeException
     */
    public function test_factory_of_6ints_minuteTooHigh()
    {
        LocalDateTime::of(2007, 7, 15, 12, 60, 40);
    }

    /**
     * @expectedException \Celest\DateTimeException
     */
    public function test_factory_of_6ints_secondTooLow()
    {
        LocalDateTime::of(2007, 7, 15, 12, 30, -1);
    }

    /**
     * @expectedException \Celest\DateTimeException
     */
    public function test_factory_of_6ints_secondTooHigh()
    {
        LocalDateTime::of(2007, 7, 15, 12, 30, 60);
    }

    //-----------------------------------------------------------------------

    public function test_factory_of_7ints()
    {
        $dateTime = LocalDateTime::of(2007, 7, 15, 12, 30, 40, 987654321);
        $this->check($dateTime, 2007, 7, 15, 12, 30, 40, 987654321);
    }

    /**
     * @expectedException \Celest\DateTimeException
     */
    public function test_factory_of_7ints_yearTooLow()
    {
        LocalDateTime::of(Integer::MIN_VALUE, 7, 15, 12, 30, 40, 987654321);
    }

    /**
     * @expectedException \Celest\DateTimeException
     */
    public function test_factory_of_7ints_monthTooLow()
    {
        LocalDateTime::of(2007, 0, 15, 12, 30, 40, 987654321);
    }

    /**
     * @expectedException \Celest\DateTimeException
     */
    public function test_factory_of_7ints_monthTooHigh()
    {
        LocalDateTime::of(2007, 13, 15, 12, 30, 40, 987654321);
    }

    /**
     * @expectedException \Celest\DateTimeException
     */
    public function test_factory_of_7ints_dayTooLow()
    {
        LocalDateTime::of(2007, 7, -1, 12, 30, 40, 987654321);
    }

    /**
     * @expectedException \Celest\DateTimeException
     */
    public function test_factory_of_7ints_dayTooHigh()
    {
        LocalDateTime::of(2007, 7, 32, 12, 30, 40, 987654321);
    }

    /**
     * @expectedException \Celest\DateTimeException
     */
    public function test_factory_of_7ints_hourTooLow()
    {
        LocalDateTime::of(2007, 7, 15, -1, 30, 40, 987654321);
    }

    /**
     * @expectedException \Celest\DateTimeException
     */
    public function test_factory_of_7ints_hourTooHigh()
    {
        LocalDateTime::of(2007, 7, 15, 24, 30, 40, 987654321);
    }

    /**
     * @expectedException \Celest\DateTimeException
     */
    public function test_factory_of_7ints_minuteTooLow()
    {
        LocalDateTime::of(2007, 7, 15, 12, -1, 40, 987654321);
    }

    /**
     * @expectedException \Celest\DateTimeException
     */
    public function test_factory_of_7ints_minuteTooHigh()
    {
        LocalDateTime::of(2007, 7, 15, 12, 60, 40, 987654321);
    }

    /**
     * @expectedException \Celest\DateTimeException
     */
    public function test_factory_of_7ints_secondTooLow()
    {
        LocalDateTime::of(2007, 7, 15, 12, 30, -1, 987654321);
    }

    /**
     * @expectedException \Celest\DateTimeException
     */
    public function test_factory_of_7ints_secondTooHigh()
    {
        LocalDateTime::of(2007, 7, 15, 12, 30, 60, 987654321);
    }

    /**
     * @expectedException \Celest\DateTimeException
     */
    public function test_factory_of_7ints_nanoTooLow()
    {
        LocalDateTime::of(2007, 7, 15, 12, 30, 40, -1);
    }

    /**
     * @expectedException \Celest\DateTimeException
     */
    public function test_factory_of_7ints_nanoTooHigh()
    {
        LocalDateTime::of(2007, 7, 15, 12, 30, 40, 1000000000);
    }

    //-----------------------------------------------------------------------

    public function test_factory_of_LocalDate_LocalTime()
    {
        $dateTime = LocalDateTime::ofDateAndTime(LocalDate::of(2007, 7, 15), LocalTime::of(12, 30, 40, 987654321));
        $this->check($dateTime, 2007, 7, 15, 12, 30, 40, 987654321);
    }

    public function test_factory_of_LocalDate_LocalTime_nullLocalDate()
    {
        TestHelper::assertNullException($this, function () {
            LocalDateTime::ofDateAndTime(null, LocalTime::of(12, 30, 40, 987654321));
        });
    }

    public function test_factory_of_LocalDate_LocalTime_nullLocalTime()
    {
        TestHelper::assertNullException($this, function () {
            LocalDateTime::ofDateAndTime(LocalDate::of(2007, 7, 15), null);
        });
    }

    //-----------------------------------------------------------------------
    // ofInstant()
    //-----------------------------------------------------------------------
    function data_instantFactory()
    {
        return [
            [Instant::ofEpochSecond(86400 + 3600 + 120 + 4, 500), self::ZONE_PARIS(), LocalDateTime::of(1970, 1, 2, 2, 2, 4, 500)],
            [Instant::ofEpochSecond(86400 + 3600 + 120 + 4, 500), self::OFFSET_MTWO(), LocalDateTime::of(1970, 1, 1, 23, 2, 4, 500)],
            [Instant::ofEpochSecond(-86400 + 4, 500), self::OFFSET_PTWO(), LocalDateTime::of(1969, 12, 31, 2, 0, 4, 500)],
            [OffsetDateTime::ofDateTime(LocalDateTime::of(Year::MIN_VALUE, 1, 1, 0, 0), ZoneOffset::UTC())->toInstant(),
                ZoneOffset::UTC(), LocalDateTime::MIN()],
            [OffsetDateTime::ofDateTime(LocalDateTime::of(Year::MAX_VALUE, 12, 31, 23, 59, 59, 999999999), ZoneOffset::UTC())->toInstant(),
                ZoneOffset::UTC(), LocalDateTime::MAX()],
        ];
    }

    /**
     * @dataProvider data_instantFactory
     */
    public function test_factory_ofInstant(Instant $instant, ZoneId $zone, LocalDateTime $expected)
    {
        $test = LocalDateTime::ofInstant($instant, $zone);
        $this->assertEquals($test, $expected);
    }

    /**
     * @expectedException \Celest\DateTimeException
     */
    public function test_factory_ofInstant_instantTooBig()
    {
        LocalDateTime::ofInstant(Instant::MAX(), self::OFFSET_PONE());
    }

    /**
     * @expectedException \Celest\DateTimeException
     */
    public function test_factory_ofInstant_instantTooSmall()
    {
        LocalDateTime::ofInstant(Instant::MIN(), self::OFFSET_PONE());
    }

    public function test_factory_ofInstant_nullInstant()
    {
        TestHelper::assertNullException($this, function () {
            LocalDateTime::ofInstant(null, self::ZONE_GAZA());
        });
    }

    public function test_factory_ofInstant_nullZone()
    {
        TestHelper::assertNullException($this, function () {
            LocalDateTime::ofInstant(Instant::EPOCH(), null);
        });
    }

    //-----------------------------------------------------------------------
    // ofEpochSecond()
    //-----------------------------------------------------------------------

    /**
     * @group long
     */
    public function test_factory_ofEpochSecond_longOffset_afterEpoch()
    {
        $base = LocalDateTime::of(1970, 1, 1, 2, 0, 0, 500);
        for ($i = 0; $i < 100000; $i++) {
            $test = LocalDateTime::ofEpochSecond($i, 500, self::OFFSET_PTWO());
            $this->assertEquals($test, $base->plusSeconds($i));
        }
    }

    /**
     * @group long
     */
    public function test_factory_ofEpochSecond_longOffset_beforeEpoch()
    {
        $base = LocalDateTime::of(1970, 1, 1, 2, 0, 0, 500);
        for ($i = 0; $i < 100000; $i++) {
            $test = LocalDateTime::ofEpochSecond(-$i, 500, self::OFFSET_PTWO());
            $this->assertEquals($test, $base->minusSeconds($i));
        }
    }

    /**
     * @expectedException \Celest\DateTimeException
     */
    public function test_factory_ofEpochSecond_longOffset_tooBig()
    {
        LocalDateTime::ofEpochSecond(Long::MAX_VALUE, 500, self::OFFSET_PONE());  // TODO: better $test
    }

    /**
     * @expectedException \Celest\DateTimeException
     */
    public function test_factory_ofEpochSecond_longOffset_tooSmall()
    {
        LocalDateTime::ofEpochSecond(Long::MIN_VALUE, 500, self::OFFSET_PONE());  // TODO: better $test
    }

    /**
     * @expectedException \Celest\DateTimeException
     */
    public function test_factory_ofEpochSecond_badNanos_toBig()
    {
        LocalDateTime::ofEpochSecond(0, 1000000000, self::OFFSET_PONE());
    }

    /**
     * @expectedException \Celest\DateTimeException
     */
    public function test_factory_ofEpochSecond_badNanos_toSmall()
    {
        LocalDateTime::ofEpochSecond(0, -1, self::OFFSET_PONE());
    }

    public function test_factory_ofEpochSecond_longOffset_nullOffset()
    {
        TestHelper::assertNullException($this, function () {
            LocalDateTime::ofEpochSecond(0, 500, null);
        });
    }

    //-----------------------------------------------------------------------
    // from()
    //-----------------------------------------------------------------------

    public function test_from_TemporalAccessor()
    {
        $base = LocalDateTime::of(2007, 7, 15, 17, 30);
        $this->assertEquals(LocalDateTime::from($base), $base);
        $this->assertEquals(LocalDateTime::from(ZonedDateTime::ofDateTime($base, ZoneOffset::ofHours(2))), $base);
    }

    /**
     * @expectedException \Celest\DateTimeException
     */
    public function test_from_TemporalAccessor_invalid_noDerive()
    {
        LocalDateTime::from(LocalTime::of(12, 30));
    }

    public function test_from_TemporalAccessor_null()
    {
        TestHelper::assertNullException($this, function () {
            LocalDateTime::from(null);
        });
    }

    //-----------------------------------------------------------------------
    // parse()
    //-----------------------------------------------------------------------
    /**
     * @dataProvider provider_sampleToString
     */
    public function test_parse($y, $month, $d, $h, $m, $s, $n, $text)
    {
        $t = LocalDateTime::parse($text);
        $this->assertEquals($t->getYear(), $y);
        $this->assertEquals($t->getMonth()->getValue(), $month);
        $this->assertEquals($t->getDayOfMonth(), $d);
        $this->assertEquals($t->getHour(), $h);
        $this->assertEquals($t->getMinute(), $m);
        $this->assertEquals($t->getSecond(), $s);
        $this->assertEquals($t->getNano(), $n);
    }

    /**
     * @expectedException \Celest\DateTimeParseException
     */
    public function test_factory_parse_illegalValue()
    {
        LocalDateTime::parse("2008-06-32T11:15");
    }

    /**
     * @expectedException \Celest\DateTimeParseException
     */
    public function test_factory_parse_invalidValue()
    {
        LocalDateTime::parse("2008-06-31T11:15");
    }

    public function test_factory_parse_nullText()
    {
        TestHelper::assertNullException($this, function () {
            LocalDateTime::parse(null);
        });
    }

    //-----------------------------------------------------------------------
    // parse(DateTimeFormatter)
    //-----------------------------------------------------------------------

    public function test_factory_parse_formatter()
    {
        $f = DateTimeFormatter::ofPattern("y M d H m s");
        $test = LocalDateTime::parseWith("2010 12 3 11 30 45", $f);
        $this->assertEquals($test, LocalDateTime::of(2010, 12, 3, 11, 30, 45));
    }

    public function test_factory_parse_formatter_nullText()
    {
        TestHelper::assertNullException($this, function () {
            $f = DateTimeFormatter::ofPattern("y M d H m s");
            LocalDateTime::parseWith(null, $f);
        });

    }

    public function test_factory_parse_formatter_nullFormatter()
    {
        TestHelper::assertNullException($this, function () {
            LocalDateTime::parseWith("ANY", null);
        });
    }

    //-----------------------------------------------------------------------
    // isSupported(TemporalField)
    //-----------------------------------------------------------------------

    public function test_isSupported_TemporalField()
    {
        //$this->assertEquals(self::TEST_200707_15_12_30_40_987654321()->isSupported(null), false); TODO
        $this->assertEquals(self::TEST_200707_15_12_30_40_987654321()->isSupported(CF::NANO_OF_SECOND()), true);
        $this->assertEquals(self::TEST_200707_15_12_30_40_987654321()->isSupported(CF::NANO_OF_DAY()), true);
        $this->assertEquals(self::TEST_200707_15_12_30_40_987654321()->isSupported(CF::MICRO_OF_SECOND()), true);
        $this->assertEquals(self::TEST_200707_15_12_30_40_987654321()->isSupported(CF::MICRO_OF_DAY()), true);
        $this->assertEquals(self::TEST_200707_15_12_30_40_987654321()->isSupported(CF::MILLI_OF_SECOND()), true);
        $this->assertEquals(self::TEST_200707_15_12_30_40_987654321()->isSupported(CF::MILLI_OF_DAY()), true);
        $this->assertEquals(self::TEST_200707_15_12_30_40_987654321()->isSupported(CF::SECOND_OF_MINUTE()), true);
        $this->assertEquals(self::TEST_200707_15_12_30_40_987654321()->isSupported(CF::SECOND_OF_DAY()), true);
        $this->assertEquals(self::TEST_200707_15_12_30_40_987654321()->isSupported(CF::MINUTE_OF_HOUR()), true);
        $this->assertEquals(self::TEST_200707_15_12_30_40_987654321()->isSupported(CF::MINUTE_OF_DAY()), true);
        $this->assertEquals(self::TEST_200707_15_12_30_40_987654321()->isSupported(CF::HOUR_OF_AMPM()), true);
        $this->assertEquals(self::TEST_200707_15_12_30_40_987654321()->isSupported(CF::CLOCK_HOUR_OF_AMPM()), true);
        $this->assertEquals(self::TEST_200707_15_12_30_40_987654321()->isSupported(CF::HOUR_OF_DAY()), true);
        $this->assertEquals(self::TEST_200707_15_12_30_40_987654321()->isSupported(CF::CLOCK_HOUR_OF_DAY()), true);
        $this->assertEquals(self::TEST_200707_15_12_30_40_987654321()->isSupported(CF::AMPM_OF_DAY()), true);
        $this->assertEquals(self::TEST_200707_15_12_30_40_987654321()->isSupported(CF::DAY_OF_WEEK()), true);
        $this->assertEquals(self::TEST_200707_15_12_30_40_987654321()->isSupported(CF::ALIGNED_DAY_OF_WEEK_IN_MONTH()), true);
        $this->assertEquals(self::TEST_200707_15_12_30_40_987654321()->isSupported(CF::ALIGNED_DAY_OF_WEEK_IN_YEAR()), true);
        $this->assertEquals(self::TEST_200707_15_12_30_40_987654321()->isSupported(CF::DAY_OF_MONTH()), true);
        $this->assertEquals(self::TEST_200707_15_12_30_40_987654321()->isSupported(CF::DAY_OF_YEAR()), true);
        $this->assertEquals(self::TEST_200707_15_12_30_40_987654321()->isSupported(CF::EPOCH_DAY()), true);
        $this->assertEquals(self::TEST_200707_15_12_30_40_987654321()->isSupported(CF::ALIGNED_WEEK_OF_MONTH()), true);
        $this->assertEquals(self::TEST_200707_15_12_30_40_987654321()->isSupported(CF::ALIGNED_WEEK_OF_YEAR()), true);
        $this->assertEquals(self::TEST_200707_15_12_30_40_987654321()->isSupported(CF::MONTH_OF_YEAR()), true);
        $this->assertEquals(self::TEST_200707_15_12_30_40_987654321()->isSupported(CF::PROLEPTIC_MONTH()), true);
        $this->assertEquals(self::TEST_200707_15_12_30_40_987654321()->isSupported(CF::YEAR()), true);
        $this->assertEquals(self::TEST_200707_15_12_30_40_987654321()->isSupported(CF::YEAR_OF_ERA()), true);
        $this->assertEquals(self::TEST_200707_15_12_30_40_987654321()->isSupported(CF::ERA()), true);
        $this->assertEquals(self::TEST_200707_15_12_30_40_987654321()->isSupported(CF::INSTANT_SECONDS()), false);
        $this->assertEquals(self::TEST_200707_15_12_30_40_987654321()->isSupported(CF::OFFSET_SECONDS()), false);
    }

    //-----------------------------------------------------------------------
    // isSupported(TemporalUnit)
    //-----------------------------------------------------------------------

    public function test_isSupported_TemporalUnit()
    {
        //$this->assertEquals(self::TEST_200707_15_12_30_40_987654321()->isUnitSupported(null), false); TODO
        $this->assertEquals(self::TEST_200707_15_12_30_40_987654321()->isUnitSupported(CU::NANOS()), true);
        $this->assertEquals(self::TEST_200707_15_12_30_40_987654321()->isUnitSupported(CU::MICROS()), true);
        $this->assertEquals(self::TEST_200707_15_12_30_40_987654321()->isUnitSupported(CU::MILLIS()), true);
        $this->assertEquals(self::TEST_200707_15_12_30_40_987654321()->isUnitSupported(CU::SECONDS()), true);
        $this->assertEquals(self::TEST_200707_15_12_30_40_987654321()->isUnitSupported(CU::MINUTES()), true);
        $this->assertEquals(self::TEST_200707_15_12_30_40_987654321()->isUnitSupported(CU::HOURS()), true);
        $this->assertEquals(self::TEST_200707_15_12_30_40_987654321()->isUnitSupported(CU::HALF_DAYS()), true);
        $this->assertEquals(self::TEST_200707_15_12_30_40_987654321()->isUnitSupported(CU::DAYS()), true);
        $this->assertEquals(self::TEST_200707_15_12_30_40_987654321()->isUnitSupported(CU::WEEKS()), true);
        $this->assertEquals(self::TEST_200707_15_12_30_40_987654321()->isUnitSupported(CU::MONTHS()), true);
        $this->assertEquals(self::TEST_200707_15_12_30_40_987654321()->isUnitSupported(CU::YEARS()), true);
        $this->assertEquals(self::TEST_200707_15_12_30_40_987654321()->isUnitSupported(CU::DECADES()), true);
        $this->assertEquals(self::TEST_200707_15_12_30_40_987654321()->isUnitSupported(CU::CENTURIES()), true);
        $this->assertEquals(self::TEST_200707_15_12_30_40_987654321()->isUnitSupported(CU::MILLENNIA()), true);
        $this->assertEquals(self::TEST_200707_15_12_30_40_987654321()->isUnitSupported(CU::ERAS()), true);
        $this->assertEquals(self::TEST_200707_15_12_30_40_987654321()->isUnitSupported(CU::FOREVER()), false);
    }

    //-----------------------------------------------------------------------
    // get(TemporalField)
    //-----------------------------------------------------------------------

    public function test_get_TemporalField()
    {
        $test = LocalDateTime::of(2008, 6, 30, 12, 30, 40, 987654321);
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
    }


    public function test_getLong_TemporalField()
    {
        $test = LocalDateTime::of(2008, 6, 30, 12, 30, 40, 987654321);
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
    }

    //-----------------------------------------------------------------------
    // query(TemporalQuery)
    //-----------------------------------------------------------------------
    function data_query()
    {
        return [
            [self::TEST_200707_15_12_30_40_987654321(), TemporalQueries::chronology(), IsoChronology::INSTANCE()],
            [self::TEST_200707_15_12_30_40_987654321(), TemporalQueries::zoneId(), null],
            [self::TEST_200707_15_12_30_40_987654321(), TemporalQueries::precision(), CU::NANOS()],
            [self::TEST_200707_15_12_30_40_987654321(), TemporalQueries::zone(), null],
            [self::TEST_200707_15_12_30_40_987654321(), TemporalQueries::offset(), null],
            [self::TEST_200707_15_12_30_40_987654321(), TemporalQueries::localDate(), LocalDate::of(2007, 7, 15)],
            [self::TEST_200707_15_12_30_40_987654321(), TemporalQueries::localTime(), LocalTime::of(12, 30, 40, 987654321)],
        ];
    }

    /**
     * @dataProvider data_query
     */
    public function test_query(TemporalAccessor $temporal, TemporalQuery $query, $expected)
    {
        $this->assertEquals($expected, $temporal->query($query));
    }

    /**
     * @dataProvider data_query
     */
    public function test_queryFrom(TemporalAccessor $temporal, TemporalQuery $query, $expected)
    {
        $this->assertEquals($expected, $query->queryFrom($temporal));
    }

    public function test_query_null()
    {
        TestHelper::assertNullException($this, function () {
            self::TEST_200707_15_12_30_40_987654321()->query(null);
        });
    }

    //-----------------------------------------------------------------------
    function provider_sampleDates()
    {
        return [
            [2008, 7, 5],
            [2007, 7, 5],
            [2006, 7, 5],
            [2005, 7, 5],
            [2004, 1, 1],
            [-1, 1, 2],
        ];
    }

    function provider_sampleTimes()
    {
        return [
            [0, 0, 0, 0],
            [0, 0, 0, 1],
            [0, 0, 1, 0],
            [0, 0, 1, 1],
            [0, 1, 0, 0],
            [0, 1, 0, 1],
            [0, 1, 1, 0],
            [0, 1, 1, 1],
            [1, 0, 0, 0],
            [1, 0, 0, 1],
            [1, 0, 1, 0],
            [1, 0, 1, 1],
            [1, 1, 0, 0],
            [1, 1, 0, 1],
            [1, 1, 1, 0],
            [1, 1, 1, 1],
        ];
    }

    //-----------------------------------------------------------------------
    // get*()
    //-----------------------------------------------------------------------
    /**
     * @dataProvider provider_sampleDates
     */
    public function test_get_dates($y, $m, $d)
    {
        $a = LocalDateTime::of($y, $m, $d, 12, 30);
        $this->assertEquals($a->getYear(), $y);
        $this->assertEquals($a->getMonth(), Month::of($m));
        $this->assertEquals($a->getDayOfMonth(), $d);
    }

    /**
     * @dataProvider provider_sampleDates
     */
    public function test_getDOY($y, $m, $d)
    {
        $a = LocalDateTime::of($y, $m, $d, 12, 30);
        $total = 0;
        for ($i = 1; $i < $m; $i++) {
            $total += Month::of($i)->length(Year::isLeapYear($y));
        }
        $doy = $total + $d;
        $this->assertEquals($a->getDayOfYear(), $doy);
    }

    /**
     * @dataProvider provider_sampleTimes
     */
    public function test_get_times($h, $m, $s, $ns)
    {
        $a = LocalDateTime::ofDateAndTime(self::TEST_200707_15_12_30_40_987654321()->toLocalDate(), LocalTime::of($h, $m, $s, $ns));
        $this->assertEquals($a->getHour(), $h);
        $this->assertEquals($a->getMinute(), $m);
        $this->assertEquals($a->getSecond(), $s);
        $this->assertEquals($a->getNano(), $ns);
    }

    //-----------------------------------------------------------------------
    // getDayOfWeek()
    //-----------------------------------------------------------------------

    public function test_getDayOfWeek()
    {
        $dow = DayOfWeek::MONDAY();
        foreach (Month::values() as $month) {
            $length = $month->length(false);
            for ($i = 1; $i <= $length; $i++) {
                $d = LocalDateTime::ofDateAndTime(LocalDate::ofMonth(2007, $month, $i),
                    self::TEST_200707_15_12_30_40_987654321()->toLocalTime());
                $this->assertSame($d->getDayOfWeek(), $dow);
                $dow = $dow->plus(1);
            }
        }
    }

    //-----------------------------------------------------------------------
    // adjustInto(Temporal)
    //-----------------------------------------------------------------------
    function data_adjustInto()
    {
        return [
            [LocalDateTime::of(2012, 3, 4, 23, 5), LocalDateTime::of(2012, 3, 4, 1, 1, 1, 100), LocalDateTime::of(2012, 3, 4, 23, 5, 0, 0), null],
            [LocalDateTime::ofMonth(2012, Month::MARCH(), 4, 0, 0), LocalDateTime::of(2012, 3, 4, 1, 1, 1, 100), LocalDateTime::of(2012, 3, 4, 0, 0), null],
            [LocalDateTime::of(2012, 3, 4, 23, 5), LocalDateTime::MAX(), LocalDateTime::of(2012, 3, 4, 23, 5), null],
            [LocalDateTime::of(2012, 3, 4, 23, 5), LocalDateTime::MIN(), LocalDateTime::of(2012, 3, 4, 23, 5), null],
            [LocalDateTime::MAX(), LocalDateTime::of(2012, 3, 4, 23, 5), LocalDateTime::MAX(), null],
            [LocalDateTime::MIN(), LocalDateTime::of(2012, 3, 4, 23, 5), LocalDateTime::MIN(), null],

            [LocalDateTime::of(2012, 3, 4, 23, 5), OffsetDateTime::of(2210, 2, 2, 0, 0, 0, 0, ZoneOffset::UTC()), OffsetDateTime::of(2012, 3, 4, 23, 5, 0, 0, ZoneOffset::UTC()), null],
            [LocalDateTime::of(2012, 3, 4, 23, 5), OffsetDateTime::of(2210, 2, 2, 0, 0, 0, 0, self::OFFSET_PONE()), OffsetDateTime::of(2012, 3, 4, 23, 5, 0, 0, self::OFFSET_PONE()), null],
            [LocalDateTime::of(2012, 3, 4, 23, 5), ZonedDateTime::of(2210, 2, 2, 0, 0, 0, 0, self::ZONE_PARIS()), ZonedDateTime::of(2012, 3, 4, 23, 5, 0, 0, self::ZONE_PARIS()), null],

            [LocalDateTime::of(2012, 3, 4, 23, 5), LocalDate::of(2210, 2, 2), null, DateTimeException::class],
            [LocalDateTime::of(2012, 3, 4, 23, 5), LocalTime::of(22, 3, 0), null, DateTimeException::class],
            [LocalDateTime::of(2012, 3, 4, 23, 5), OffsetTime::of(22, 3, 0, 0, ZoneOffset::UTC()), null, DateTimeException::class],
            //[LocalDateTime::of(2012, 3, 4, 23, 5), null, null, NullPointerException::class], TODO
        ];
    }

    /**
     * @dataProvider data_adjustInto
     */
    public function test_adjustInto(LocalDateTime $test, Temporal $temporal, $expected, $expectedEx)
    {
        if ($expectedEx === null) {
            $result = $test->adjustInto($temporal);
            $this->assertEquals($result, $expected);
        } else {
            try {
                $test->adjustInto($temporal);
                $this->fail();
            } catch (\Exception $ex) {
                $this->assertInstanceOf($expectedEx, $ex);
            }
        }
    }

//-----------------------------------------------------------------------
// with()
//-----------------------------------------------------------------------

    public function test_with_adjustment()
    {
        $sample = LocalDateTime::of(2012, 3, 4, 23, 5);
        $adjuster = TemporalAdjusters::fromCallable(function ($s) use ($sample) {
            return $sample;
        });
        $this->assertEquals(self::TEST_200707_15_12_30_40_987654321()->adjust($adjuster), $sample);
    }

    public function test_with_adjustment_null()
    {
        TestHelper::assertNullException($this, function () {
            self::TEST_200707_15_12_30_40_987654321()->adjust(null);
        });
    }

//-----------------------------------------------------------------------
// withYear()
//-----------------------------------------------------------------------

    public function test_withYear_int_normal()
    {
        $t = self::TEST_200707_15_12_30_40_987654321()->withYear(2008);
        $this->check($t, 2008, 7, 15, 12, 30, 40, 987654321);
    }

    /**
     * @expectedException \Celest\DateTimeException
     */
    public function test_withYear_int_invalid()
    {
        self::TEST_200707_15_12_30_40_987654321()->withYear(Year::MIN_VALUE - 1);
    }


    public function test_withYear_int_adjustDay()
    {
        $t = LocalDateTime::of(2008, 2, 29, 12, 30)->withYear(2007);
        $expected = LocalDateTime::of(2007, 2, 28, 12, 30);
        $this->assertEquals($t, $expected);
    }

//-----------------------------------------------------------------------
// withMonth()
//-----------------------------------------------------------------------

    public function test_withMonth_int_normal()
    {
        $t = self::TEST_200707_15_12_30_40_987654321()->withMonth(1);
        $this->check($t, 2007, 1, 15, 12, 30, 40, 987654321);
    }

    /**
     * @expectedException \Celest\DateTimeException
     */

    public function test_withMonth_int_invalid()
    {
        self::TEST_200707_15_12_30_40_987654321()->withMonth(13);
    }


    public function test_withMonth_int_adjustDay()
    {
        $t = LocalDateTime::of(2007, 12, 31, 12, 30)->withMonth(11);
        $expected = LocalDateTime::of(2007, 11, 30, 12, 30);
        $this->assertEquals($t, $expected);
    }

//-----------------------------------------------------------------------
// withDayOfMonth()
//-----------------------------------------------------------------------

    public function test_withDayOfMonth_normal()
    {
        $t = self::TEST_200707_15_12_30_40_987654321()->withDayOfMonth(1);
        $this->check($t, 2007, 7, 1, 12, 30, 40, 987654321);
    }

    /**
     * @expectedException \Celest\DateTimeException
     */

    public function test_withDayOfMonth_invalid()
    {
        LocalDateTime::of(2007, 11, 30, 12, 30)->withDayOfMonth(32);
    }

    /**
     * @expectedException \Celest\DateTimeException
     */

    public function test_withDayOfMonth_invalidCombination()
    {
        LocalDateTime::of(2007, 11, 30, 12, 30)->withDayOfMonth(31);
    }

//-----------------------------------------------------------------------
// withDayOfYear(int)
//-----------------------------------------------------------------------

    public function test_withDayOfYear_normal()
    {
        $t = self::TEST_200707_15_12_30_40_987654321()->withDayOfYear(33);
        $this->assertEquals($t, LocalDateTime::of(2007, 2, 2, 12, 30, 40, 987654321));
    }

    /**
     * @expectedException \Celest\DateTimeException
     */

    public function test_withDayOfYear_illegal()
    {
        self::TEST_200707_15_12_30_40_987654321()->withDayOfYear(367);
    }

    /**
     * @expectedException \Celest\DateTimeException
     */

    public function test_withDayOfYear_invalid()
    {
        self::TEST_200707_15_12_30_40_987654321()->withDayOfYear(366);
    }

//-----------------------------------------------------------------------
// withHour()
//-----------------------------------------------------------------------

    public function test_withHour_normal()
    {
        $t = self::TEST_200707_15_12_30_40_987654321();
        for ($i = 0; $i < 24; $i++) {
            $t = $t->withHour($i);
            $this->assertEquals($t->getHour(), $i);
        }
    }

    /**
     * @expectedException \Celest\DateTimeException
     */

    public function test_withHour_hourTooLow()
    {
        self::TEST_200707_15_12_30_40_987654321()->withHour(-1);
    }

    /**
     * @expectedException \Celest\DateTimeException
     */

    public function test_withHour_hourTooHigh()
    {
        self::TEST_200707_15_12_30_40_987654321()->withHour(24);
    }

//-----------------------------------------------------------------------
// withMinute()
//-----------------------------------------------------------------------

    public function test_withMinute_normal()
    {
        $t = self::TEST_200707_15_12_30_40_987654321();
        for ($i = 0; $i < 60; $i++) {
            $t = $t->withMinute($i);
            $this->assertEquals($t->getMinute(), $i);
        }
    }

    /**
     * @expectedException \Celest\DateTimeException
     */

    public function test_withMinute_minuteTooLow()
    {
        self::TEST_200707_15_12_30_40_987654321()->withMinute(-1);
    }

    /**
     * @expectedException \Celest\DateTimeException
     */

    public function test_withMinute_minuteTooHigh()
    {
        self::TEST_200707_15_12_30_40_987654321()->withMinute(60);
    }

//-----------------------------------------------------------------------
// withSecond()
//-----------------------------------------------------------------------

    public function test_withSecond_normal()
    {
        $t = self::TEST_200707_15_12_30_40_987654321();
        for ($i = 0; $i < 60; $i++) {
            $t = $t->withSecond($i);
            $this->assertEquals($t->getSecond(), $i);
        }
    }

    /**
     * @expectedException \Celest\DateTimeException
     */

    public function test_withSecond_secondTooLow()
    {
        self::TEST_200707_15_12_30_40_987654321()->withSecond(-1);
    }

    /**
     * @expectedException \Celest\DateTimeException
     */

    public function test_withSecond_secondTooHigh()
    {
        self::TEST_200707_15_12_30_40_987654321()->withSecond(60);
    }

//-----------------------------------------------------------------------
// withNano()
//-----------------------------------------------------------------------

    public function test_withNanoOfSecond_normal()
    {
        /** @var LocalDateTime $t */
        $t = self::TEST_200707_15_12_30_40_987654321();
        $t = $t->withNano(1);
        $this->assertEquals($t->getNano(), 1);
        $t = $t->withNano(10);
        $this->assertEquals($t->getNano(), 10);
        $t = $t->withNano(100);
        $this->assertEquals($t->getNano(), 100);
        $t = $t->withNano(999999999);
        $this->assertEquals($t->getNano(), 999999999);
    }

    /**
     * @expectedException \Celest\DateTimeException
     */

    public function test_withNanoOfSecond_nanoTooLow()
    {
        self::TEST_200707_15_12_30_40_987654321()->withNano(-1);
    }

    /**
     * @expectedException \Celest\DateTimeException
     */

    public function test_withNanoOfSecond_nanoTooHigh()
    {
        self::TEST_200707_15_12_30_40_987654321()->withNano(1000000000);
    }

//-----------------------------------------------------------------------
// truncatedTo(TemporalUnit)
//-----------------------------------------------------------------------

    public function test_truncatedTo_normal()
    {
        $this->assertEquals(self::TEST_200707_15_12_30_40_987654321()->truncatedTo(CU::NANOS()), self::TEST_200707_15_12_30_40_987654321());
        $this->assertEquals(self::TEST_200707_15_12_30_40_987654321()->truncatedTo(CU::SECONDS()), self::TEST_200707_15_12_30_40_987654321()->withNano(0));
        $this->assertEquals(self::TEST_200707_15_12_30_40_987654321()->truncatedTo(CU::DAYS()), self::TEST_200707_15_12_30_40_987654321()->adjust(LocalTime::MIDNIGHT()));
    }

    public function test_truncatedTo_null()
    {
        TestHelper::assertNullException($this, function () {
            self::TEST_200707_15_12_30_40_987654321()->truncatedTo(null);
        });
    }

//-----------------------------------------------------------------------
// plus(TemporalAmount)
//-----------------------------------------------------------------------

    public function test_plus_TemporalAmount_positiveMonths()
    {
        $period = MockSimplePeriod::of(7, CU::MONTHS());
        $t = self::TEST_200707_15_12_30_40_987654321()->plusAmount($period);
        $this->assertEquals($t, LocalDateTime::of(2008, 2, 15, 12, 30, 40, 987654321));
    }


    public function test_plus_TemporalAmount_negativeDays()
    {
        $period = MockSimplePeriod::of(-25, CU::DAYS());
        $t = self::TEST_200707_15_12_30_40_987654321()->plusAmount($period);
        $this->assertEquals($t, LocalDateTime::of(2007, 6, 20, 12, 30, 40, 987654321));
    }

    /**
     * @expectedException \Celest\DateTimeException
     */
    public function test_plus_TemporalAmount_invalidTooLarge()
    {
        $period = MockSimplePeriod::of(1, CU::YEARS());
        LocalDateTime::of(Year::MAX_VALUE, 1, 1, 0, 0)->plusAmount($period);
    }

    /**
     * @expectedException \Celest\DateTimeException
     */
    public function test_plus_TemporalAmount_invalidTooSmall()
    {
        $period = MockSimplePeriod::of(-1, CU::YEARS());
        LocalDateTime::of(Year::MIN_VALUE, 1, 1, 0, 0)->plusAmount($period);
    }

    public function test_plus_TemporalAmount_null()
    {
        TestHelper::assertNullException($this, function () {
            self::TEST_200707_15_12_30_40_987654321()->plusAmount(null);
        });
    }

//-----------------------------------------------------------------------
// plus(long,TemporalUnit)
//-----------------------------------------------------------------------

    public function test_plus_longTemporalUnit_positiveMonths()
    {
        $t = self::TEST_200707_15_12_30_40_987654321()->plus(7, CU::MONTHS());
        $this->assertEquals($t, LocalDateTime::of(2008, 2, 15, 12, 30, 40, 987654321));
    }


    public function test_plus_longTemporalUnit_negativeDays()
    {
        $t = self::TEST_200707_15_12_30_40_987654321()->plus(-25, CU::DAYS());
        $this->assertEquals($t, LocalDateTime::of(2007, 6, 20, 12, 30, 40, 987654321));
    }

    public function test_plus_longTemporalUnit_null()
    {
        TestHelper::assertNullException($this, function () {
            self::TEST_200707_15_12_30_40_987654321()->plus(1, null);
        });
    }

    /**
     * @expectedException \Celest\DateTimeException
     */
    public function test_plus_longTemporalUnit_invalidTooLarge()
    {
        LocalDateTime::of(Year::MAX_VALUE, 1, 1, 0, 0)->plus(1, CU::YEARS());
    }

    /**
     * @expectedException \Celest\DateTimeException
     */
    public function test_plus_longTemporalUnit_invalidTooSmall()
    {
        LocalDateTime::of(Year::MIN_VALUE, 1, 1, 0, 0)->plus(-1, CU::YEARS());
    }

//-----------------------------------------------------------------------
// plusYears()
//-----------------------------------------------------------------------

    public function test_plusYears_int_normal()
    {
        $t = self::TEST_200707_15_12_30_40_987654321()->plusYears(1);
        $this->check($t, 2008, 7, 15, 12, 30, 40, 987654321);
    }


    public function test_plusYears_int_negative()
    {
        $t = self::TEST_200707_15_12_30_40_987654321()->plusYears(-1);
        $this->check($t, 2006, 7, 15, 12, 30, 40, 987654321);
    }


    public function test_plusYears_int_adjustDay()
    {
        $t = $this->createDateMidnight(2008, 2, 29)->plusYears(1);
        $this->check($t, 2009, 2, 28, 0, 0, 0, 0);
    }

    /**
     * @expectedException \Celest\DateTimeException
     */
    public function test_plusYears_int_invalidTooLarge()
    {
        $this->createDateMidnight(Year::MAX_VALUE, 1, 1)->plusYears(1);
    }

    /**
     * @expectedException \Celest\DateTimeException
     */
    public function test_plusYears_int_invalidTooSmall()
    {
        LocalDate::of(Year::MIN_VALUE, 1, 1)->plusYears(-1);
    }

//-----------------------------------------------------------------------
// plusMonths()
//-----------------------------------------------------------------------

    public function test_plusMonths_int_normal()
    {
        $t = self::TEST_200707_15_12_30_40_987654321()->plusMonths(1);
        $this->check($t, 2007, 8, 15, 12, 30, 40, 987654321);
    }


    public function test_plusMonths_int_overYears()
    {
        $t = self::TEST_200707_15_12_30_40_987654321()->plusMonths(25);
        $this->check($t, 2009, 8, 15, 12, 30, 40, 987654321);
    }


    public function test_plusMonths_int_negative()
    {
        $t = self::TEST_200707_15_12_30_40_987654321()->plusMonths(-1);
        $this->check($t, 2007, 6, 15, 12, 30, 40, 987654321);
    }


    public function test_plusMonths_int_negativeAcrossYear()
    {
        $t = self::TEST_200707_15_12_30_40_987654321()->plusMonths(-7);
        $this->check($t, 2006, 12, 15, 12, 30, 40, 987654321);
    }


    public function test_plusMonths_int_negativeOverYears()
    {
        $t = self::TEST_200707_15_12_30_40_987654321()->plusMonths(-31);
        $this->check($t, 2004, 12, 15, 12, 30, 40, 987654321);
    }


    public function test_plusMonths_int_adjustDayFromLeapYear()
    {
        $t = $this->createDateMidnight(2008, 2, 29)->plusMonths(12);
        $this->check($t, 2009, 2, 28, 0, 0, 0, 0);
    }


    public function test_plusMonths_int_adjustDayFromMonthLength()
    {
        $t = $this->createDateMidnight(2007, 3, 31)->plusMonths(1);
        $this->check($t, 2007, 4, 30, 0, 0, 0, 0);
    }

    /**
     * @expectedException \Celest\DateTimeException
     */
    public function test_plusMonths_int_invalidTooLarge()
    {
        $this->createDateMidnight(Year::MAX_VALUE, 12, 1)->plusMonths(1);
    }

    /**
     * @expectedException \Celest\DateTimeException
     */
    public function test_plusMonths_int_invalidTooSmall()
    {
        $this->createDateMidnight(Year::MIN_VALUE, 1, 1)->plusMonths(-1);
    }

//-----------------------------------------------------------------------
// plusWeeks()
//-----------------------------------------------------------------------
    function provider_samplePlusWeeksSymmetry()
    {
        return
            [
                [$this->createDateMidnight(-1, 1, 1)],
                [$this->createDateMidnight(-1, 2, 28)],
                [$this->createDateMidnight(-1, 3, 1)],
                [$this->createDateMidnight(-1, 12, 31)],
                [$this->createDateMidnight(0, 1, 1)],
                [$this->createDateMidnight(0, 2, 28)],
                [$this->createDateMidnight(0, 2, 29)],
                [$this->createDateMidnight(0, 3, 1)],
                [$this->createDateMidnight(0, 12, 31)],
                [$this->createDateMidnight(2007, 1, 1)],
                [$this->createDateMidnight(2007, 2, 28)],
                [$this->createDateMidnight(2007, 3, 1)],
                [$this->createDateMidnight(2007, 12, 31)],
                [$this->createDateMidnight(2008, 1, 1)],
                [$this->createDateMidnight(2008, 2, 28)],
                [$this->createDateMidnight(2008, 2, 29)],
                [$this->createDateMidnight(2008, 3, 1)],
                [$this->createDateMidnight(2008, 12, 31)],
                [$this->createDateMidnight(2099, 1, 1)],
                [$this->createDateMidnight(2099, 2, 28)],
                [$this->createDateMidnight(2099, 3, 1)],
                [$this->createDateMidnight(2099, 12, 31)],
                [$this->createDateMidnight(2100, 1, 1)],
                [$this->createDateMidnight(2100, 2, 28)],
                [$this->createDateMidnight(2100, 3, 1)],
                [$this->createDateMidnight(2100, 12, 31)],
            ];
    }

    /**
     * @dataProvider provider_samplePlusWeeksSymmetry
     * @group long
     */
    public function test_plusWeeks_symmetry(LocalDateTime $reference)
    {
        for ($weeks = 0; $weeks < 365 * 8;
             $weeks++) {
            $t = $reference->plusWeeks($weeks)->plusWeeks(-$weeks);
            $this->assertEquals($t, $reference);

            $t = $reference->plusWeeks(-$weeks)->plusWeeks($weeks);
            $this->assertEquals($t, $reference);
        }
    }


    public function test_plusWeeks_normal()
    {
        $t = self::TEST_200707_15_12_30_40_987654321()->plusWeeks(1);
        $this->check($t, 2007, 7, 22, 12, 30, 40, 987654321);
    }


    public function test_plusWeeks_overMonths()
    {
        $t = self::TEST_200707_15_12_30_40_987654321()->plusWeeks(9);
        $this->check($t, 2007, 9, 16, 12, 30, 40, 987654321);
    }


    public function test_plusWeeks_overYears()
    {
        $t = LocalDateTime::of(2006, 7, 16, 12, 30, 40, 987654321)->plusWeeks(52);
        $this->assertEquals($t, self::TEST_200707_15_12_30_40_987654321());
    }


    public function test_plusWeeks_overLeapYears()
    {
        $t = self::TEST_200707_15_12_30_40_987654321()->plusYears(-1)->plusWeeks(104);
        $this->check($t, 2008, 7, 12, 12, 30, 40, 987654321);
    }


    public function test_plusWeeks_negative()
    {
        $t = self::TEST_200707_15_12_30_40_987654321()->plusWeeks(-1);
        $this->check($t, 2007, 7, 8, 12, 30, 40, 987654321);
    }


    public function test_plusWeeks_negativeAcrossYear()
    {
        $t = self::TEST_200707_15_12_30_40_987654321()->plusWeeks(-28);
        $this->check($t, 2006, 12, 31, 12, 30, 40, 987654321);
    }


    public function test_plusWeeks_negativeOverYears()
    {
        $t = self::TEST_200707_15_12_30_40_987654321()->plusWeeks(-104);
        $this->check($t, 2005, 7, 17, 12, 30, 40, 987654321);
    }


    public function test_plusWeeks_maximum()
    {
        $t = $this->createDateMidnight(Year::MAX_VALUE, 12, 24)->plusWeeks(1);
        $this->check($t, Year::MAX_VALUE, 12, 31, 0, 0, 0, 0);
    }


    public function test_plusWeeks_minimum()
    {
        $t = $this->createDateMidnight(Year::MIN_VALUE, 1, 8)->plusWeeks(-1);
        $this->check($t, Year::MIN_VALUE, 1, 1, 0, 0, 0, 0);
    }

    /**
     * @expectedException \Celest\DateTimeException
     */
    public function test_plusWeeks_invalidTooLarge()
    {
        $this->createDateMidnight(Year::MAX_VALUE, 12, 25)->plusWeeks(1);
    }

    /**
     * @expectedException \Celest\DateTimeException
     */
    public function test_plusWeeks_invalidTooSmall()
    {
        $this->createDateMidnight(Year::MIN_VALUE, 1, 7)->plusWeeks(-1);
    }

//-----------------------------------------------------------------------
// plusDays()
//-----------------------------------------------------------------------
    function provider_samplePlusDaysSymmetry()
    {
        return [
            [$this->createDateMidnight(-1, 1, 1)],
            [$this->createDateMidnight(-1, 2, 28)],
            [$this->createDateMidnight(-1, 3, 1)],
            [$this->createDateMidnight(-1, 12, 31)],
            [$this->createDateMidnight(0, 1, 1)],
            [$this->createDateMidnight(0, 2, 28)],
            [$this->createDateMidnight(0, 2, 29)],
            [$this->createDateMidnight(0, 3, 1)],
            [$this->createDateMidnight(0, 12, 31)],
            [$this->createDateMidnight(2007, 1, 1)],
            [$this->createDateMidnight(2007, 2, 28)],
            [$this->createDateMidnight(2007, 3, 1)],
            [$this->createDateMidnight(2007, 12, 31)],
            [$this->createDateMidnight(2008, 1, 1)],
            [$this->createDateMidnight(2008, 2, 28)],
            [$this->createDateMidnight(2008, 2, 29)],
            [$this->createDateMidnight(2008, 3, 1)],
            [$this->createDateMidnight(2008, 12, 31)],
            [$this->createDateMidnight(2099, 1, 1)],
            [$this->createDateMidnight(2099, 2, 28)],
            [$this->createDateMidnight(2099, 3, 1)],
            [$this->createDateMidnight(2099, 12, 31)],
            [$this->createDateMidnight(2100, 1, 1)],
            [$this->createDateMidnight(2100, 2, 28)],
            [$this->createDateMidnight(2100, 3, 1)],
            [$this->createDateMidnight(2100, 12, 31)],
        ];
    }

    /**
     * @dataProvider provider_samplePlusDaysSymmetry
     * @group long
     */
    public function test_plusDays_symmetry(LocalDateTime $reference)
    {
        for ($days = 0; $days < 365 * 8;
             $days++) {
            $t = $reference->plusDays($days)->plusDays(-$days);
            $this->assertEquals($t, $reference);

            $t = $reference->plusDays(-$days)->plusDays($days);
            $this->assertEquals($t, $reference);
        }
    }


    public function test_plusDays_normal()
    {
        $t = self::TEST_200707_15_12_30_40_987654321()->plusDays(1);
        $this->check($t, 2007, 7, 16, 12, 30, 40, 987654321);
    }


    public function test_plusDays_overMonths()
    {
        $t = self::TEST_200707_15_12_30_40_987654321()->plusDays(62);
        $this->check($t, 2007, 9, 15, 12, 30, 40, 987654321);
    }


    public function test_plusDays_overYears()
    {
        $t = LocalDateTime::of(2006, 7, 14, 12, 30, 40, 987654321)->plusDays(366);
        $this->assertEquals($t, self::TEST_200707_15_12_30_40_987654321());
    }


    public function test_plusDays_overLeapYears()
    {
        $t = self::TEST_200707_15_12_30_40_987654321()->plusYears(-1)->plusDays(365 + 366);
        $this->check($t, 2008, 7, 15, 12, 30, 40, 987654321);
    }


    public function test_plusDays_negative()
    {
        $t = self::TEST_200707_15_12_30_40_987654321()->plusDays(-1);
        $this->check($t, 2007, 7, 14, 12, 30, 40, 987654321);
    }


    public function test_plusDays_negativeAcrossYear()
    {
        $t = self::TEST_200707_15_12_30_40_987654321()->plusDays(-196);
        $this->check($t, 2006, 12, 31, 12, 30, 40, 987654321);
    }


    public function test_plusDays_negativeOverYears()
    {
        $t = self::TEST_200707_15_12_30_40_987654321()->plusDays(-730);
        $this->check($t, 2005, 7, 15, 12, 30, 40, 987654321);
    }


    public function test_plusDays_maximum()
    {
        $t = $this->createDateMidnight(Year::MAX_VALUE, 12, 30)->plusDays(1);
        $this->check($t, Year::MAX_VALUE, 12, 31, 0, 0, 0, 0);
    }


    public function test_plusDays_minimum()
    {
        $t = $this->createDateMidnight(Year::MIN_VALUE, 1, 2)->plusDays(-1);
        $this->check($t, Year::MIN_VALUE, 1, 1, 0, 0, 0, 0);
    }

    /**
     * @expectedException \Celest\DateTimeException
     */
    public function test_plusDays_invalidTooLarge()
    {
        $this->createDateMidnight(Year::MAX_VALUE, 12, 31)->plusDays(1);
    }

    /**
     * @expectedException \Celest\DateTimeException
     */
    public function test_plusDays_invalidTooSmall()
    {
        $this->createDateMidnight(Year::MIN_VALUE, 1, 1)->plusDays(-1);
    }

    /**
     * @expectedException \Celest\DateTimeException
     */
    public function test_plusDays_overflowTooLarge()
    {
        $this->createDateMidnight(Year::MAX_VALUE, 12, 31)->plusDays(Long::MAX_VALUE);
    }

    /**
     * @expectedException \Celest\DateTimeException
     */
    public function test_plusDays_overflowTooSmall()
    {
        $this->createDateMidnight(Year::MIN_VALUE, 1, 1)->plusDays(Long::MIN_VALUE);
    }

//-----------------------------------------------------------------------
// plusHours()
//-----------------------------------------------------------------------

    public function test_plusHours_one()
    {
        $t = self::TEST_200707_15_12_30_40_987654321()->adjust(LocalTime::MIDNIGHT());
        $d = $t->toLocalDate();

        for ($i = 0; $i < 50; $i++) {
            $t = $t->plusHours(1);

            if (($i + 1) % 24 == 0) {
                $d = $d->plusDays(1);
            }

            $this->assertEquals($t->toLocalDate(), $d);
            $this->assertEquals($t->getHour(), ($i + 1) % 24);
        }
    }


    public function test_plusHours_fromZero()
    {
        $base = self::TEST_200707_15_12_30_40_987654321()->adjust(LocalTime::MIDNIGHT());
        $d = $base->toLocalDate()->minusDays(3);
        $t = LocalTime::of(21, 0);

        for ($i = -50; $i < 50; $i++) {
            $dt = $base->plusHours($i);
            $t = $t->plusHours(1);

            if ($t->getHour() == 0) {
                $d = $d->plusDays(1);
            }

            $this->assertEquals($dt->toLocalDate(), $d);
            $this->assertEquals($dt->toLocalTime(), $t);
        }
    }


    public function test_plusHours_fromOne()
    {
        $base = self::TEST_200707_15_12_30_40_987654321()->adjust(LocalTime::of(1, 0));
        $d = $base->toLocalDate()->minusDays(3);
        $t = LocalTime::of(22, 0);

        for ($i = -50; $i < 50; $i++) {
            $dt = $base->plusHours($i);

            $t = $t->plusHours(1);

            if ($t->getHour() == 0) {
                $d = $d->plusDays(1);
            }

            $this->assertEquals($dt->toLocalDate(), $d);
            $this->assertEquals($dt->toLocalTime(), $t);
        }
    }

//-----------------------------------------------------------------------
// plusMinutes()
//-----------------------------------------------------------------------

    public function test_plusMinutes_one()
    {
        $t = self::TEST_200707_15_12_30_40_987654321()->adjust(LocalTime::MIDNIGHT());
        $d = $t->toLocalDate();

        $hour = 0;
        $min = 0;

        for ($i = 0; $i < 70; $i++) {
            $t = $t->plusMinutes(1);
            $min++;
            if ($min == 60) {
                $hour++;
                $min = 0;
            }

            $this->assertEquals($t->toLocalDate(), $d);
            $this->assertEquals($t->getHour(), $hour);
            $this->assertEquals($t->getMinute(), $min);
        }
    }


    public function test_plusMinutes_fromZero()
    {
        $base = self::TEST_200707_15_12_30_40_987654321()->adjust(LocalTime::MIDNIGHT());
        $d = $base->toLocalDate()->minusDays(1);
        $t = LocalTime::of(22, 49);

        for ($i = -70; $i < 70; $i++) {
            $dt = $base->plusMinutes($i);
            $t = $t->plusMinutes(1);

            if ($t->equals(LocalTime::MIDNIGHT())) {
                $d = $d->plusDays(1);
            }

            $this->assertEquals($dt->toLocalDate(), $d, $i);
            $this->assertEquals($dt->toLocalTime(), $t, $i);
        }
    }


    public function test_plusMinutes_noChange_oneDay()
    {
        $t = self::TEST_200707_15_12_30_40_987654321()->plusMinutes(24 * 60);
        $this->assertEquals($t->toLocalDate(), self::TEST_200707_15_12_30_40_987654321()->toLocalDate()->plusDays(1));
    }

//-----------------------------------------------------------------------
// plusSeconds()
//-----------------------------------------------------------------------

    public function test_plusSeconds_one()
    {
        $t = self::TEST_200707_15_12_30_40_987654321()->adjust(LocalTime::MIDNIGHT());
        $d = $t->toLocalDate();

        $hour = 0;
        $min = 0;
        $sec = 0;

        for ($i = 0; $i < 3700; $i++) {
            $t = $t->plusSeconds(1);
            $sec++;
            if ($sec == 60) {
                $min++;
                $sec = 0;
            }
            if ($min == 60) {
                $hour++;
                $min = 0;
            }

            $this->assertEquals($t->toLocalDate(), $d);
            $this->assertEquals($t->getHour(), $hour);
            $this->assertEquals($t->getMinute(), $min);
            $this->assertEquals($t->getSecond(), $sec);
        }
    }

    function plusSeconds_fromZero()
    {
        $delta = 30;

        $date = self::TEST_200707_15_12_30_40_987654321()->toLocalDate()->minusDays(1);
        $hour = 22;
        $min = 59;
        $sec = 0;

        $ret = [];

        for ($i = -3660; $i <= 3660;) {
            $ret[] = [$i, $date, $hour, $min, $sec];
            $i += $delta;
            $sec += $delta;

            if ($sec >= 60) {
                $min++;
                $sec -= 60;

                if ($min == 60) {
                    $hour++;
                    $min = 0;

                    if ($hour == 24) {
                        $hour = 0;
                    }
                }
            }

            if ($i == 0) {
                $date = $date->plusDays(1);
            }

        }
        return $ret;
    }

    /**
     * @dataProvider plusSeconds_fromZero
     */
    public function test_plusSeconds_fromZero($seconds, LocalDate $date, $hour, $min, $sec)
    {
        $base = self::TEST_200707_15_12_30_40_987654321()->adjust(LocalTime::MIDNIGHT());
        $t = $base->plusSeconds($seconds);

        $this->assertEquals($date, $t->toLocalDate());
        $this->assertEquals($hour, $t->getHour());
        $this->assertEquals($min, $t->getMinute());
        $this->assertEquals($sec, $t->getSecond());
    }


    public function test_plusSeconds_noChange_oneDay()
    {
        $t = self::TEST_200707_15_12_30_40_987654321()->plusSeconds(24 * 60 * 60);
        $this->assertEquals($t->toLocalDate(), self::TEST_200707_15_12_30_40_987654321()->toLocalDate()->plusDays(1));
    }

//-----------------------------------------------------------------------
// plusNanos()
//-----------------------------------------------------------------------

    public function test_plusNanos_halfABillion()
    {
        $t = self::TEST_200707_15_12_30_40_987654321()->adjust(LocalTime::MIDNIGHT());
        $d = $t->toLocalDate();

        $hour = 0;
        $min = 0;
        $sec = 0;
        $nanos = 0;

        for ($i = 0; $i < 3700 * 1000000000; $i += 500000000) {
            $t = $t->plusNanos(500000000);
            $nanos += 500000000;
            if ($nanos == 1000000000) {
                $sec++;
                $nanos = 0;
            }
            if ($sec == 60) {
                $min++;
                $sec = 0;
            }
            if ($min == 60) {
                $hour++;
                $min = 0;
            }

            $this->assertEquals($t->toLocalDate(), $d, $i);
            $this->assertEquals($t->getHour(), $hour);
            $this->assertEquals($t->getMinute(), $min);
            $this->assertEquals($t->getSecond(), $sec);
            $this->assertEquals($t->getNano(), $nanos);
        }
    }

    function plusNanos_fromZero()
    {
        $delta = 7500000000;

        $date = self::TEST_200707_15_12_30_40_987654321()->toLocalDate()->minusDays(1);
        $hour = 22;
        $min = 59;
        $sec = 0;
        $nanos = 0;

        $ret = [];

        for ($i = -3660 * 1000000000; $i <= 3660 * 1000000000;) {
            $ret[] = [$i, $date, $hour, $min, $sec, $nanos];
            $i += $delta;
            $nanos += $delta;

            if ($nanos >= 1000000000) {
                $sec += Math::div($nanos, 1000000000);
                $nanos %= 1000000000;

                if ($sec >= 60) {
                    $min++;
                    $sec %= 60;

                    if ($min == 60) {
                        $hour++;
                        $min = 0;

                        if ($hour == 24) {
                            $hour = 0;
                            $date = $date->plusDays(1);
                        }
                    }
                }
            }
        }
        return $ret;
    }

    /**
     * @dataProvider plusNanos_fromZero
     */
    public
    function test_plusNanos_fromZero($nanoseconds, LocalDate $date, $hour, $min, $sec, $nanos)
    {
        $base = self::TEST_200707_15_12_30_40_987654321()->adjust(LocalTime::MIDNIGHT());
        $t = $base->plusNanos($nanoseconds);

        $this->assertEquals($date, $t->toLocalDate());
        $this->assertEquals($hour, $t->getHour());
        $this->assertEquals($min, $t->getMinute());
        $this->assertEquals($sec, $t->getSecond());
        $this->assertEquals($nanos, $t->getNano());
    }


    public
    function test_plusNanos_noChange_oneDay()
    {
        $t = self::TEST_200707_15_12_30_40_987654321()->plusNanos(24 * 60 * 60 * 1000000000);
        $this->assertEquals($t->toLocalDate(), self::TEST_200707_15_12_30_40_987654321()->toLocalDate()->plusDays(1));
    }

//-----------------------------------------------------------------------
// minus(TemporalAmount)
//-----------------------------------------------------------------------

    public
    function test_minus_TemporalAmount_positiveMonths()
    {
        $period = MockSimplePeriod::of(7, CU::MONTHS());
        $t = self::TEST_200707_15_12_30_40_987654321()->minusAmount($period);
        $this->assertEquals($t, LocalDateTime::of(2006, 12, 15, 12, 30, 40, 987654321));
    }


    public
    function test_minus_TemporalAmount_negativeDays()
    {
        $period = MockSimplePeriod::of(-25, CU::DAYS());
        $t = self::TEST_200707_15_12_30_40_987654321()->minusAmount($period);
        $this->assertEquals($t, LocalDateTime::of(2007, 8, 9, 12, 30, 40, 987654321));
    }

    /**
     * @expectedException \Celest\DateTimeException
     */
    public
    function test_minus_TemporalAmount_invalidTooLarge()
    {
        $period = MockSimplePeriod::of(-1, CU::YEARS());
        LocalDateTime::of(Year::MAX_VALUE, 1, 1, 0, 0)->minusAmount($period);
    }

    /**
     * @expectedException \Celest\DateTimeException
     */
    public
    function test_minus_TemporalAmount_invalidTooSmall()
    {
        $period = MockSimplePeriod::of(1, CU::YEARS());
        LocalDateTime::of(Year::MIN_VALUE, 1, 1, 0, 0)->minusAmount($period);
    }

    public
    function test_minus_TemporalAmount_null()
    {
        TestHelper::assertNullException($this, function () {
            self::TEST_200707_15_12_30_40_987654321()->minusAmount(null);
        });
    }

//-----------------------------------------------------------------------
// minus(long,TemporalUnit)
//-----------------------------------------------------------------------

    public
    function test_minus_longTemporalUnit_positiveMonths()
    {
        $t = self::TEST_200707_15_12_30_40_987654321()->minus(7, CU::MONTHS());
        $this->assertEquals($t, LocalDateTime::of(2006, 12, 15, 12, 30, 40, 987654321));
    }


    public
    function test_minus_longTemporalUnit_negativeDays()
    {
        $t = self::TEST_200707_15_12_30_40_987654321()->minus(-25, CU::DAYS());
        $this->assertEquals($t, LocalDateTime::of(2007, 8, 9, 12, 30, 40, 987654321));
    }

    public
    function test_minus_longTemporalUnit_null()
    {
        TestHelper::assertNullException($this, function () {
            self::TEST_200707_15_12_30_40_987654321()->minus(1, null);
        });
    }

    /**
     * @expectedException \Celest\DateTimeException
     */
    public
    function test_minus_longTemporalUnit_invalidTooLarge()
    {
        LocalDateTime::of(Year::MAX_VALUE, 1, 1, 0, 0)->minus(-1, CU::YEARS());
    }

    /**
     * @expectedException \Celest\DateTimeException
     */
    public
    function test_minus_longTemporalUnit_invalidTooSmall()
    {
        LocalDateTime::of(Year::MIN_VALUE, 1, 1, 0, 0)->minus(1, CU::YEARS());
    }

//-----------------------------------------------------------------------
// minusYears()
//-----------------------------------------------------------------------

    public
    function test_minusYears_int_normal()
    {
        $t = self::TEST_200707_15_12_30_40_987654321()->minusYears(1);
        $this->check($t, 2006, 7, 15, 12, 30, 40, 987654321);
    }


    public
    function test_minusYears_int_negative()
    {
        $t = self::TEST_200707_15_12_30_40_987654321()->minusYears(-1);
        $this->check($t, 2008, 7, 15, 12, 30, 40, 987654321);
    }


    public
    function test_minusYears_int_adjustDay()
    {
        $t = $this->createDateMidnight(2008, 2, 29)->minusYears(1);
        $this->check($t, 2007, 2, 28, 0, 0, 0, 0);
    }

    /**
     * @expectedException \Celest\DateTimeException
     */
    public
    function test_minusYears_int_invalidTooLarge()
    {
        $this->createDateMidnight(Year::MAX_VALUE, 1, 1)->minusYears(-1);
    }

    /**
     * @expectedException \Celest\DateTimeException
     */
    public
    function test_minusYears_int_invalidTooSmall()
    {
        $this->createDateMidnight(Year::MIN_VALUE, 1, 1)->minusYears(1);
    }

//-----------------------------------------------------------------------
// minusMonths()
//-----------------------------------------------------------------------

    public
    function test_minusMonths_int_normal()
    {
        $t = self::TEST_200707_15_12_30_40_987654321()->minusMonths(1);
        $this->check($t, 2007, 6, 15, 12, 30, 40, 987654321);
    }


    public
    function test_minusMonths_int_overYears()
    {
        $t = self::TEST_200707_15_12_30_40_987654321()->minusMonths(25);
        $this->check($t, 2005, 6, 15, 12, 30, 40, 987654321);
    }


    public
    function test_minusMonths_int_negative()
    {
        $t = self::TEST_200707_15_12_30_40_987654321()->minusMonths(-1);
        $this->check($t, 2007, 8, 15, 12, 30, 40, 987654321);
    }


    public
    function test_minusMonths_int_negativeAcrossYear()
    {
        $t = self::TEST_200707_15_12_30_40_987654321()->minusMonths(-7);
        $this->check($t, 2008, 2, 15, 12, 30, 40, 987654321);
    }


    public
    function test_minusMonths_int_negativeOverYears()
    {
        $t = self::TEST_200707_15_12_30_40_987654321()->minusMonths(-31);
        $this->check($t, 2010, 2, 15, 12, 30, 40, 987654321);
    }


    public
    function test_minusMonths_int_adjustDayFromLeapYear()
    {
        $t = $this->createDateMidnight(2008, 2, 29)->minusMonths(12);
        $this->check($t, 2007, 2, 28, 0, 0, 0, 0);
    }


    public
    function test_minusMonths_int_adjustDayFromMonthLength()
    {
        $t = $this->createDateMidnight(2007, 3, 31)->minusMonths(1);
        $this->check($t, 2007, 2, 28, 0, 0, 0, 0);
    }

    /**
     * @expectedException \Celest\DateTimeException
     */
    public
    function test_minusMonths_int_invalidTooLarge()
    {
        $this->createDateMidnight(Year::MAX_VALUE, 12, 1)->minusMonths(-1);
    }

    /**
     * @expectedException \Celest\DateTimeException
     */
    public
    function test_minusMonths_int_invalidTooSmall()
    {
        $this->createDateMidnight(Year::MIN_VALUE, 1, 1)->minusMonths(1);
    }

//-----------------------------------------------------------------------
// minusWeeks()
//-----------------------------------------------------------------------
    function provider_sampleMinusWeeksSymmetry()
    {
        return [
            [$this->createDateMidnight(-1, 1, 1)],
            [$this->createDateMidnight(-1, 2, 28)],
            [$this->createDateMidnight(-1, 3, 1)],
            [$this->createDateMidnight(-1, 12, 31)],
            [$this->createDateMidnight(0, 1, 1)],
            [$this->createDateMidnight(0, 2, 28)],
            [$this->createDateMidnight(0, 2, 29)],
            [$this->createDateMidnight(0, 3, 1)],
            [$this->createDateMidnight(0, 12, 31)],
            [$this->createDateMidnight(2007, 1, 1)],
            [$this->createDateMidnight(2007, 2, 28)],
            [$this->createDateMidnight(2007, 3, 1)],
            [$this->createDateMidnight(2007, 12, 31)],
            [$this->createDateMidnight(2008, 1, 1)],
            [$this->createDateMidnight(2008, 2, 28)],
            [$this->createDateMidnight(2008, 2, 29)],
            [$this->createDateMidnight(2008, 3, 1)],
            [$this->createDateMidnight(2008, 12, 31)],
            [$this->createDateMidnight(2099, 1, 1)],
            [$this->createDateMidnight(2099, 2, 28)],
            [$this->createDateMidnight(2099, 3, 1)],
            [$this->createDateMidnight(2099, 12, 31)],
            [$this->createDateMidnight(2100, 1, 1)],
            [$this->createDateMidnight(2100, 2, 28)],
            [$this->createDateMidnight(2100, 3, 1)],
            [$this->createDateMidnight(2100, 12, 31)],
        ];
    }

    /**
     * @dataProvider provider_sampleMinusWeeksSymmetry
     * @group long
     */
    public
    function test_minusWeeks_symmetry(LocalDateTime $reference)
    {
        for ($weeks = 0; $weeks < 365 * 8;
             $weeks++) {
            $t = $reference->minusWeeks($weeks)->minusWeeks(-$weeks);
            $this->assertEquals($t, $reference);

            $t = $reference->minusWeeks(-$weeks)->minusWeeks($weeks);
            $this->assertEquals($t, $reference);
        }
    }


    public
    function test_minusWeeks_normal()
    {
        $t = self::TEST_200707_15_12_30_40_987654321()->minusWeeks(1);
        $this->check($t, 2007, 7, 8, 12, 30, 40, 987654321);
    }


    public
    function test_minusWeeks_overMonths()
    {
        $t = self::TEST_200707_15_12_30_40_987654321()->minusWeeks(9);
        $this->check($t, 2007, 5, 13, 12, 30, 40, 987654321);
    }


    public
    function test_minusWeeks_overYears()
    {
        $t = LocalDateTime::of(2008, 7, 13, 12, 30, 40, 987654321)->minusWeeks(52);
        $this->assertEquals($t, self::TEST_200707_15_12_30_40_987654321());
    }


    public
    function test_minusWeeks_overLeapYears()
    {
        $t = self::TEST_200707_15_12_30_40_987654321()->minusYears(-1)->minusWeeks(104);
        $this->check($t, 2006, 7, 18, 12, 30, 40, 987654321);
    }


    public
    function test_minusWeeks_negative()
    {
        $t = self::TEST_200707_15_12_30_40_987654321()->minusWeeks(-1);
        $this->check($t, 2007, 7, 22, 12, 30, 40, 987654321);
    }


    public
    function test_minusWeeks_negativeAcrossYear()
    {
        $t = self::TEST_200707_15_12_30_40_987654321()->minusWeeks(-28);
        $this->check($t, 2008, 1, 27, 12, 30, 40, 987654321);
    }


    public
    function test_minusWeeks_negativeOverYears()
    {
        $t = self::TEST_200707_15_12_30_40_987654321()->minusWeeks(-104);
        $this->check($t, 2009, 7, 12, 12, 30, 40, 987654321);
    }


    public
    function test_minusWeeks_maximum()
    {
        $t = $this->createDateMidnight(Year::MAX_VALUE, 12, 24)->minusWeeks(-1);
        $this->check($t, Year::MAX_VALUE, 12, 31, 0, 0, 0, 0);
    }


    public
    function test_minusWeeks_minimum()
    {
        $t = $this->createDateMidnight(Year::MIN_VALUE, 1, 8)->minusWeeks(1);
        $this->check($t, Year::MIN_VALUE, 1, 1, 0, 0, 0, 0);
    }

    /**
     * @expectedException \Celest\DateTimeException
     */
    public
    function test_minusWeeks_invalidTooLarge()
    {
        $this->createDateMidnight(Year::MAX_VALUE, 12, 25)->minusWeeks(-1);
    }

    /**
     * @expectedException \Celest\DateTimeException
     */
    public
    function test_minusWeeks_invalidTooSmall()
    {
        $this->createDateMidnight(Year::MIN_VALUE, 1, 7)->minusWeeks(1);
    }

//-----------------------------------------------------------------------
// minusDays()
//-----------------------------------------------------------------------
    function provider_sampleMinusDaysSymmetry()
    {
        return [
            [$this->createDateMidnight(-1, 1, 1)],
            [$this->createDateMidnight(-1, 2, 28)],
            [$this->createDateMidnight(-1, 3, 1)],
            [$this->createDateMidnight(-1, 12, 31)],
            [$this->createDateMidnight(0, 1, 1)],
            [$this->createDateMidnight(0, 2, 28)],
            [$this->createDateMidnight(0, 2, 29)],
            [$this->createDateMidnight(0, 3, 1)],
            [$this->createDateMidnight(0, 12, 31)],
            [$this->createDateMidnight(2007, 1, 1)],
            [$this->createDateMidnight(2007, 2, 28)],
            [$this->createDateMidnight(2007, 3, 1)],
            [$this->createDateMidnight(2007, 12, 31)],
            [$this->createDateMidnight(2008, 1, 1)],
            [$this->createDateMidnight(2008, 2, 28)],
            [$this->createDateMidnight(2008, 2, 29)],
            [$this->createDateMidnight(2008, 3, 1)],
            [$this->createDateMidnight(2008, 12, 31)],
            [$this->createDateMidnight(2099, 1, 1)],
            [$this->createDateMidnight(2099, 2, 28)],
            [$this->createDateMidnight(2099, 3, 1)],
            [$this->createDateMidnight(2099, 12, 31)],
            [$this->createDateMidnight(2100, 1, 1)],
            [$this->createDateMidnight(2100, 2, 28)],
            [$this->createDateMidnight(2100, 3, 1)],
            [$this->createDateMidnight(2100, 12, 31)],
        ];
    }

    /**
     * @dataProvider provider_sampleMinusDaysSymmetry
     * @group long
     */
    public
    function test_minusDays_symmetry(LocalDateTime $reference)
    {
        for ($days = 0; $days < 365 * 8;
             $days++) {
            $t = $reference->minusDays($days)->minusDays(-$days);
            $this->assertEquals($t, $reference);

            $t = $reference->minusDays(-$days)->minusDays($days);
            $this->assertEquals($t, $reference);
        }
    }


    public
    function test_minusDays_normal()
    {
        $t = self::TEST_200707_15_12_30_40_987654321()->minusDays(1);
        $this->check($t, 2007, 7, 14, 12, 30, 40, 987654321);
    }


    public
    function test_minusDays_overMonths()
    {
        $t = self::TEST_200707_15_12_30_40_987654321()->minusDays(62);
        $this->check($t, 2007, 5, 14, 12, 30, 40, 987654321);
    }


    public
    function test_minusDays_overYears()
    {
        $t = LocalDateTime::of(2008, 7, 16, 12, 30, 40, 987654321)->minusDays(367);
        $this->assertEquals($t, self::TEST_200707_15_12_30_40_987654321());
    }


    public
    function test_minusDays_overLeapYears()
    {
        $t = self::TEST_200707_15_12_30_40_987654321()->plusYears(2)->minusDays(365 + 366);
        $this->assertEquals($t, self::TEST_200707_15_12_30_40_987654321());
    }


    public
    function test_minusDays_negative()
    {
        $t = self::TEST_200707_15_12_30_40_987654321()->minusDays(-1);
        $this->check($t, 2007, 7, 16, 12, 30, 40, 987654321);
    }


    public
    function test_minusDays_negativeAcrossYear()
    {
        $t = self::TEST_200707_15_12_30_40_987654321()->minusDays(-169);
        $this->check($t, 2007, 12, 31, 12, 30, 40, 987654321);
    }


    public
    function test_minusDays_negativeOverYears()
    {
        $t = self::TEST_200707_15_12_30_40_987654321()->minusDays(-731);
        $this->check($t, 2009, 7, 15, 12, 30, 40, 987654321);
    }


    public
    function test_minusDays_maximum()
    {
        $t = $this->createDateMidnight(Year::MAX_VALUE, 12, 30)->minusDays(-1);
        $this->check($t, Year::MAX_VALUE, 12, 31, 0, 0, 0, 0);
    }


    public
    function test_minusDays_minimum()
    {
        $t = $this->createDateMidnight(Year::MIN_VALUE, 1, 2)->minusDays(1);
        $this->check($t, Year::MIN_VALUE, 1, 1, 0, 0, 0, 0);
    }

    /**
     * @expectedException \Celest\DateTimeException
     */
    public
    function test_minusDays_invalidTooLarge()
    {
        $this->createDateMidnight(Year::MAX_VALUE, 12, 31)->minusDays(-1);
    }

    /**
     * @expectedException \Celest\DateTimeException
     */
    public
    function test_minusDays_invalidTooSmall()
    {
        $this->createDateMidnight(Year::MIN_VALUE, 1, 1)->minusDays(1);
    }

    /**
     * @expectedException \Celest\DateTimeException
     */
    public
    function test_minusDays_overflowTooLarge()
    {
        $this->createDateMidnight(Year::MAX_VALUE, 12, 31)->minusDays(Long::MIN_VALUE);
    }

    /**
     * @expectedException \Celest\DateTimeException
     */
    public
    function test_minusDays_overflowTooSmall()
    {
        $this->createDateMidnight(Year::MIN_VALUE, 1, 1)->minusDays(Long::MAX_VALUE);
    }

//-----------------------------------------------------------------------
// minusHours()
//-----------------------------------------------------------------------

    public
    function test_minusHours_one()
    {
        $t = self::TEST_200707_15_12_30_40_987654321()->adjust(LocalTime::MIDNIGHT());
        $d = $t->toLocalDate();

        for ($i = 0; $i < 50; $i++) {
            $t = $t->minusHours(1);

            if ($i % 24 == 0) {
                $d = $d->minusDays(1);
            }

            $this->assertEquals($t->toLocalDate(), $d);
            $this->assertEquals($t->getHour(), (((-$i + 23) % 24) + 24) % 24);
        }
    }


    public
    function test_minusHours_fromZero()
    {
        $base = self::TEST_200707_15_12_30_40_987654321()->adjust(LocalTime::MIDNIGHT());
        $d = $base->toLocalDate()->plusDays(2);
        $t = LocalTime::of(3, 0);

        for ($i = -50; $i < 50; $i++) {
            $dt = $base->minusHours($i);
            $t = $t->minusHours(1);

            if ($t->getHour() == 23) {
                $d = $d->minusDays(1);
            }

            $this->assertEquals($dt->toLocalDate(), $d, $i);
            $this->assertEquals($dt->toLocalTime(), $t);
        }
    }


    public
    function test_minusHours_fromOne()
    {
        $base = self::TEST_200707_15_12_30_40_987654321()->adjust(LocalTime::of(1, 0));
        $d = $base->toLocalDate()->plusDays(2);
        $t = LocalTime::of(4, 0);

        for ($i = -50; $i < 50; $i++) {
            $dt = $base->minusHours($i);

            $t = $t->minusHours(1);

            if ($t->getHour() == 23) {
                $d = $d->minusDays(1);
            }

            $this->assertEquals($dt->toLocalDate(), $d, $i);
            $this->assertEquals($dt->toLocalTime(), $t);
        }
    }

//-----------------------------------------------------------------------
// minusMinutes()
//-----------------------------------------------------------------------

    public
    function test_minusMinutes_one()
    {
        $t = self::TEST_200707_15_12_30_40_987654321()->adjust(LocalTime::MIDNIGHT());
        $d = $t->toLocalDate()->minusDays(1);

        $hour = 0;
        $min = 0;

        for ($i = 0; $i < 70; $i++) {
            $t = $t->minusMinutes(1);
            $min--;
            if ($min == -1) {
                $hour--;
                $min = 59;

                if ($hour == -1) {
                    $hour = 23;
                }
            }
            $this->assertEquals($t->toLocalDate(), $d);
            $this->assertEquals($t->getHour(), $hour);
            $this->assertEquals($t->getMinute(), $min);
        }
    }


    public
    function test_minusMinutes_fromZero()
    {
        $base = self::TEST_200707_15_12_30_40_987654321()->adjust(LocalTime::MIDNIGHT());
        $d = $base->toLocalDate()->minusDays(1);
        $t = LocalTime::of(22, 49);

        for ($i = 70; $i > -70; $i--) {
            $dt = $base->minusMinutes($i);
            $t = $t->plusMinutes(1);

            if ($t->equals(LocalTime::MIDNIGHT())) {
                $d = $d->plusDays(1);
            }

            $this->assertEquals($dt->toLocalDate(), $d);
            $this->assertEquals($dt->toLocalTime(), $t);
        }
    }


    public
    function test_minusMinutes_noChange_oneDay()
    {
        $t = self::TEST_200707_15_12_30_40_987654321()->minusMinutes(24 * 60);
        $this->assertEquals($t->toLocalDate(), self::TEST_200707_15_12_30_40_987654321()->toLocalDate()->minusDays(1));
    }

//-----------------------------------------------------------------------
// minusSeconds()
//-----------------------------------------------------------------------

    public
    function test_minusSeconds_one()
    {
        $t = self::TEST_200707_15_12_30_40_987654321()->adjust(LocalTime::MIDNIGHT());
        $d = $t->toLocalDate()->minusDays(1);

        $hour = 0;
        $min = 0;
        $sec = 0;

        for ($i = 0; $i < 3700; $i++) {
            $t = $t->minusSeconds(1);
            $sec--;
            if ($sec == -1) {
                $min--;
                $sec = 59;

                if ($min == -1) {
                    $hour--;
                    $min = 59;

                    if ($hour == -1) {
                        $hour = 23;
                    }
                }
            }

            $this->assertEquals($t->toLocalDate(), $d);
            $this->assertEquals($t->getHour(), $hour);
            $this->assertEquals($t->getMinute(), $min);
            $this->assertEquals($t->getSecond(), $sec);
        }
    }

    function minusSeconds_fromZero()
    {

        $delta = 30;

        $date = self::TEST_200707_15_12_30_40_987654321()->toLocalDate()->minusDays(1);
        $hour = 22;
        $min = 59;
        $sec = 0;

        $ret = [];
        for ($i = 3660; $i >= -3660;) {
            $ret[] = [$i, $date, $hour, $min, $sec];
            $i -= $delta;
            $sec += $delta;

            if ($sec >= 60) {
                $min++;
                $sec -= 60;

                if ($min == 60) {
                    $hour++;
                    $min = 0;

                    if ($hour == 24) {
                        $hour = 0;
                    }
                }
            }

            if ($i == 0) {
                $date = $date->plusDays(1);
            }
        }

        return $ret;
    }

    /**
     * @dataProvider minusSeconds_fromZero
     */
    public
    function test_minusSeconds_fromZero($seconds, LocalDate $date, $hour, $min, $sec)
    {
        $base = self::TEST_200707_15_12_30_40_987654321()->adjust(LocalTime::MIDNIGHT());
        $t = $base->minusSeconds($seconds);

        $this->assertEquals($date, $t->toLocalDate());
        $this->assertEquals($hour, $t->getHour());
        $this->assertEquals($min, $t->getMinute());
        $this->assertEquals($sec, $t->getSecond());
    }

//-----------------------------------------------------------------------
// minusNanos()
//-----------------------------------------------------------------------

    /**
     * @group long
     */
    public function test_minusNanos_halfABillion()
    {
        $t = self::TEST_200707_15_12_30_40_987654321()->adjust(LocalTime::MIDNIGHT());
        $d = $t->toLocalDate()->minusDays(1);

        $hour = 0;
        $min = 0;
        $sec = 0;
        $nanos = 0;

        for ($i = 0; $i < 3700 * 1000000000; $i += 500000000) {
            $t = $t->minusNanos(500000000);
            $nanos -= 500000000;

            if ($nanos < 0) {
                $sec--;
                $nanos += 1000000000;

                if ($sec == -1) {
                    $min--;
                    $sec += 60;

                    if ($min == -1) {
                        $hour--;
                        $min += 60;

                        if ($hour == -1) {
                            $hour += 24;
                        }
                    }
                }
            }

            $this->assertEquals($t->toLocalDate(), $d);
            $this->assertEquals($t->getHour(), $hour);
            $this->assertEquals($t->getMinute(), $min);
            $this->assertEquals($t->getSecond(), $sec);
            $this->assertEquals($t->getNano(), $nanos);
        }
    }

    function minusNanos_fromZero()
    {
        $delta = 7500000000;

        $date = self::TEST_200707_15_12_30_40_987654321()->toLocalDate()->minusDays(1);
        $hour = 22;
        $min = 59;
        $sec = 0;
        $nanos = 0;

        $ret = [];

        for ($i = 3660 * 1000000000; $i >= -3660 * 1000000000;) {
            $ret[] = [$i, $date, $hour, $min, $sec, $nanos];
            $nanos += $delta;
            $i -= $delta;

            if ($nanos >= 1000000000) {
                $sec += Math::div($nanos, 1000000000);
                $nanos %= 1000000000;

                if ($sec >= 60) {
                    $min++;
                    $sec %= 60;

                    if ($min == 60) {
                        $hour++;
                        $min = 0;

                        if ($hour == 24) {
                            $hour = 0;
                            $date = $date->plusDays(1);
                        }
                    }
                }
            }
        }
        return $ret;
    }

    /**
     * @dataProvider minusNanos_fromZero
     */
    public
    function test_minusNanos_fromZero($nanoseconds, LocalDate $date, $hour, $min, $sec, $nanos)
    {
        $base = self::TEST_200707_15_12_30_40_987654321()->adjust(LocalTime::MIDNIGHT());
        $t = $base->minusNanos($nanoseconds);

        $this->assertEquals($date, $t->toLocalDate());
        $this->assertEquals($hour, $t->getHour());
        $this->assertEquals($min, $t->getMinute());
        $this->assertEquals($sec, $t->getSecond());
        $this->assertEquals($nanos, $t->getNano());
    }

//-----------------------------------------------------------------------
// until(Temporal, TemporalUnit)
//-----------------------------------------------------------------------
    function data_periodUntilUnit()
    {
        return [
// $date only
            [$this->dtNoon(2000, 1, 1), $this->dtNoon(2000, 1, 1), CU::DAYS(), 0],
            [$this->dtNoon(2000, 1, 1), $this->dtNoon(2000, 1, 1), CU::WEEKS(), 0],
            [$this->dtNoon(2000, 1, 1), $this->dtNoon(2000, 1, 1), CU::MONTHS(), 0],
            [$this->dtNoon(2000, 1, 1), $this->dtNoon(2000, 1, 1), CU::YEARS(), 0],
            [$this->dtNoon(2000, 1, 1), $this->dtNoon(2000, 1, 1), CU::DECADES(), 0],
            [$this->dtNoon(2000, 1, 1), $this->dtNoon(2000, 1, 1), CU::CENTURIES(), 0],
            [$this->dtNoon(2000, 1, 1), $this->dtNoon(2000, 1, 1), CU::MILLENNIA(), 0],

            [$this->dtNoon(2000, 1, 15), $this->dtNoon(2000, 2, 14), CU::DAYS(), 30],
            [$this->dtNoon(2000, 1, 15), $this->dtNoon(2000, 2, 15), CU::DAYS(), 31],
            [$this->dtNoon(2000, 1, 15), $this->dtNoon(2000, 2, 16), CU::DAYS(), 32],

            [$this->dtNoon(2000, 1, 15), $this->dtNoon(2000, 2, 17), CU::WEEKS(), 4],
            [$this->dtNoon(2000, 1, 15), $this->dtNoon(2000, 2, 18), CU::WEEKS(), 4],
            [$this->dtNoon(2000, 1, 15), $this->dtNoon(2000, 2, 19), CU::WEEKS(), 5],
            [$this->dtNoon(2000, 1, 15), $this->dtNoon(2000, 2, 20), CU::WEEKS(), 5],

            [$this->dtNoon(2000, 1, 15), $this->dtNoon(2000, 2, 14), CU::MONTHS(), 0],
            [$this->dtNoon(2000, 1, 15), $this->dtNoon(2000, 2, 15), CU::MONTHS(), 1],
            [$this->dtNoon(2000, 1, 15), $this->dtNoon(2000, 2, 16), CU::MONTHS(), 1],
            [$this->dtNoon(2000, 1, 15), $this->dtNoon(2000, 3, 14), CU::MONTHS(), 1],
            [$this->dtNoon(2000, 1, 15), $this->dtNoon(2000, 3, 15), CU::MONTHS(), 2],
            [$this->dtNoon(2000, 1, 15), $this->dtNoon(2000, 3, 16), CU::MONTHS(), 2],

            [$this->dtNoon(2000, 1, 15), $this->dtNoon(2001, 1, 14), CU::YEARS(), 0],
            [$this->dtNoon(2000, 1, 15), $this->dtNoon(2001, 1, 15), CU::YEARS(), 1],
            [$this->dtNoon(2000, 1, 15), $this->dtNoon(2001, 1, 16), CU::YEARS(), 1],
            [$this->dtNoon(2000, 1, 15), $this->dtNoon(2004, 1, 14), CU::YEARS(), 3],
            [$this->dtNoon(2000, 1, 15), $this->dtNoon(2004, 1, 15), CU::YEARS(), 4],
            [$this->dtNoon(2000, 1, 15), $this->dtNoon(2004, 1, 16), CU::YEARS(), 4],

            [$this->dtNoon(2000, 1, 15), $this->dtNoon(2010, 1, 14), CU::DECADES(), 0],
            [$this->dtNoon(2000, 1, 15), $this->dtNoon(2010, 1, 15), CU::DECADES(), 1],

            [$this->dtNoon(2000, 1, 15), $this->dtNoon(2100, 1, 14), CU::CENTURIES(), 0],
            [$this->dtNoon(2000, 1, 15), $this->dtNoon(2100, 1, 15), CU::CENTURIES(), 1],

            [$this->dtNoon(2000, 1, 15), $this->dtNoon(3000, 1, 14), CU::MILLENNIA(), 0],
            [$this->dtNoon(2000, 1, 15), $this->dtNoon(3000, 1, 15), CU::MILLENNIA(), 1],

// time only
            [$this->dtEpoch(0, 0, 0, 0), $this->dtEpoch(0, 0, 0, 0), CU::NANOS(), 0],
            [$this->dtEpoch(0, 0, 0, 0), $this->dtEpoch(0, 0, 0, 0), CU::MICROS(), 0],
            [$this->dtEpoch(0, 0, 0, 0), $this->dtEpoch(0, 0, 0, 0), CU::MILLIS(), 0],
            [$this->dtEpoch(0, 0, 0, 0), $this->dtEpoch(0, 0, 0, 0), CU::SECONDS(), 0],
            [$this->dtEpoch(0, 0, 0, 0), $this->dtEpoch(0, 0, 0, 0), CU::MINUTES(), 0],
            [$this->dtEpoch(0, 0, 0, 0), $this->dtEpoch(0, 0, 0, 0), CU::HOURS(), 0],
            [$this->dtEpoch(0, 0, 0, 0), $this->dtEpoch(0, 0, 0, 0), CU::HALF_DAYS(), 0],

            [$this->dtEpoch(0, 0, 0, 0), $this->dtEpoch(2, 0, 0, 0), CU::NANOS(), 2 * 3600 * 1000000000],
            [$this->dtEpoch(0, 0, 0, 0), $this->dtEpoch(2, 0, 0, 0), CU::MICROS(), 2 * 3600 * 1000000],
            [$this->dtEpoch(0, 0, 0, 0), $this->dtEpoch(2, 0, 0, 0), CU::MILLIS(), 2 * 3600 * 1000],
            [$this->dtEpoch(0, 0, 0, 0), $this->dtEpoch(2, 0, 0, 0), CU::SECONDS(), 2 * 3600],
            [$this->dtEpoch(0, 0, 0, 0), $this->dtEpoch(2, 0, 0, 0), CU::MINUTES(), 2 * 60],
            [$this->dtEpoch(0, 0, 0, 0), $this->dtEpoch(2, 0, 0, 0), CU::HOURS(), 2],
            [$this->dtEpoch(0, 0, 0, 0), $this->dtEpoch(2, 0, 0, 0), CU::HALF_DAYS(), 0],

            [$this->dtEpoch(0, 0, 0, 0), $this->dtEpoch(14, 0, 0, 0), CU::NANOS(), 14 * 3600 * 1000000000],
            [$this->dtEpoch(0, 0, 0, 0), $this->dtEpoch(14, 0, 0, 0), CU::MICROS(), 14 * 3600 * 1000000],
            [$this->dtEpoch(0, 0, 0, 0), $this->dtEpoch(14, 0, 0, 0), CU::MILLIS(), 14 * 3600 * 1000],
            [$this->dtEpoch(0, 0, 0, 0), $this->dtEpoch(14, 0, 0, 0), CU::SECONDS(), 14 * 3600],
            [$this->dtEpoch(0, 0, 0, 0), $this->dtEpoch(14, 0, 0, 0), CU::MINUTES(), 14 * 60],
            [$this->dtEpoch(0, 0, 0, 0), $this->dtEpoch(14, 0, 0, 0), CU::HOURS(), 14],
            [$this->dtEpoch(0, 0, 0, 0), $this->dtEpoch(14, 0, 0, 0), CU::HALF_DAYS(), 1],

            [$this->dtEpoch(0, 0, 0, 0), $this->dtEpoch(2, 30, 40, 1500), CU::NANOS(), (2 * 3600 + 30 * 60 + 40) * 1000000000 + 1500],
            [$this->dtEpoch(0, 0, 0, 0), $this->dtEpoch(2, 30, 40, 1500), CU::MICROS(), (2 * 3600 + 30 * 60 + 40) * 1000000 + 1],
            [$this->dtEpoch(0, 0, 0, 0), $this->dtEpoch(2, 30, 40, 1500), CU::MILLIS(), (2 * 3600 + 30 * 60 + 40) * 1000],
            [$this->dtEpoch(0, 0, 0, 0), $this->dtEpoch(2, 30, 40, 1500), CU::SECONDS(), 2 * 3600 + 30 * 60 + 40],
            [$this->dtEpoch(0, 0, 0, 0), $this->dtEpoch(2, 30, 40, 1500), CU::MINUTES(), 2 * 60 + 30],
            [$this->dtEpoch(0, 0, 0, 0), $this->dtEpoch(2, 30, 40, 1500), CU::HOURS(), 2],

// combinations
            [$this->dt(2000, 1, 15, 12, 30, 40, 500), $this->dt(2000, 1, 15, 12, 30, 40, 499), CU::NANOS(), -1],
            [$this->dt(2000, 1, 15, 12, 30, 40, 500), $this->dt(2000, 1, 15, 12, 30, 40, 500), CU::NANOS(), 0],
            [$this->dt(2000, 1, 15, 12, 30, 40, 500), $this->dt(2000, 1, 15, 12, 30, 40, 501), CU::NANOS(), 1],

            [$this->dt(2000, 1, 15, 12, 30, 40, 500), $this->dt(2000, 1, 15, 12, 30, 39, 500), CU::SECONDS(), -1],
            [$this->dt(2000, 1, 15, 12, 30, 40, 500), $this->dt(2000, 1, 15, 12, 30, 39, 501), CU::SECONDS(), 0],
            [$this->dt(2000, 1, 15, 12, 30, 40, 500), $this->dt(2000, 1, 15, 12, 30, 40, 499), CU::SECONDS(), 0],
            [$this->dt(2000, 1, 15, 12, 30, 40, 500), $this->dt(2000, 1, 15, 12, 30, 40, 500), CU::SECONDS(), 0],
            [$this->dt(2000, 1, 15, 12, 30, 40, 500), $this->dt(2000, 1, 15, 12, 30, 40, 501), CU::SECONDS(), 0],
            [$this->dt(2000, 1, 15, 12, 30, 40, 500), $this->dt(2000, 1, 15, 12, 30, 41, 499), CU::SECONDS(), 0],
            [$this->dt(2000, 1, 15, 12, 30, 40, 500), $this->dt(2000, 1, 15, 12, 30, 41, 500), CU::SECONDS(), 1],

            [$this->dt(2000, 1, 15, 12, 30, 40, 500), $this->dt(2000, 1, 16, 12, 30, 40, 499), CU::NANOS(), -1 + 86400000000000],
            [$this->dt(2000, 1, 15, 12, 30, 40, 500), $this->dt(2000, 1, 16, 12, 30, 40, 500), CU::NANOS(), 0 + 86400000000000],
            [$this->dt(2000, 1, 15, 12, 30, 40, 500), $this->dt(2000, 1, 16, 12, 30, 40, 501), CU::NANOS(), 1 + 86400000000000],

            [$this->dt(2000, 1, 15, 12, 30, 40, 500), $this->dt(2000, 1, 16, 12, 30, 39, 499), CU::SECONDS(), -2 + 86400],
            [$this->dt(2000, 1, 15, 12, 30, 40, 500), $this->dt(2000, 1, 16, 12, 30, 39, 500), CU::SECONDS(), -1 + 86400],
            [$this->dt(2000, 1, 15, 12, 30, 40, 500), $this->dt(2000, 1, 16, 12, 30, 39, 501), CU::SECONDS(), -1 + 86400],
            [$this->dt(2000, 1, 15, 12, 30, 40, 500), $this->dt(2000, 1, 16, 12, 30, 40, 499), CU::SECONDS(), -1 + 86400],
            [$this->dt(2000, 1, 15, 12, 30, 40, 500), $this->dt(2000, 1, 16, 12, 30, 40, 500), CU::SECONDS(), 0 + 86400],
            [$this->dt(2000, 1, 15, 12, 30, 40, 500), $this->dt(2000, 1, 16, 12, 30, 40, 501), CU::SECONDS(), 0 + 86400],
            [$this->dt(2000, 1, 15, 12, 30, 40, 500), $this->dt(2000, 1, 16, 12, 30, 41, 499), CU::SECONDS(), 0 + 86400],
            [$this->dt(2000, 1, 15, 12, 30, 40, 500), $this->dt(2000, 1, 16, 12, 30, 41, 500), CU::SECONDS(), 1 + 86400],

            [$this->dt(2000, 1, 15, 12, 30, 40, 500), $this->dt(2000, 1, 16, 12, 29, 40, 499), CU::MINUTES(), -2 + 24 * 60],
            [$this->dt(2000, 1, 15, 12, 30, 40, 500), $this->dt(2000, 1, 16, 12, 29, 40, 500), CU::MINUTES(), -1 + 24 * 60],
            [$this->dt(2000, 1, 15, 12, 30, 40, 500), $this->dt(2000, 1, 16, 12, 29, 40, 501), CU::MINUTES(), -1 + 24 * 60],
            [$this->dt(2000, 1, 15, 12, 30, 40, 500), $this->dt(2000, 1, 16, 12, 30, 40, 499), CU::MINUTES(), -1 + 24 * 60],
            [$this->dt(2000, 1, 15, 12, 30, 40, 500), $this->dt(2000, 1, 16, 12, 30, 40, 500), CU::MINUTES(), 0 + 24 * 60],
            [$this->dt(2000, 1, 15, 12, 30, 40, 500), $this->dt(2000, 1, 16, 12, 30, 40, 501), CU::MINUTES(), 0 + 24 * 60],
            [$this->dt(2000, 1, 15, 12, 30, 40, 500), $this->dt(2000, 1, 16, 12, 31, 40, 499), CU::MINUTES(), 0 + 24 * 60],
            [$this->dt(2000, 1, 15, 12, 30, 40, 500), $this->dt(2000, 1, 16, 12, 31, 40, 500), CU::MINUTES(), 1 + 24 * 60],

            [$this->dt(2000, 1, 15, 12, 30, 40, 500), $this->dt(2000, 1, 16, 11, 30, 40, 499), CU::HOURS(), -2 + 24],
            [$this->dt(2000, 1, 15, 12, 30, 40, 500), $this->dt(2000, 1, 16, 11, 30, 40, 500), CU::HOURS(), -1 + 24],
            [$this->dt(2000, 1, 15, 12, 30, 40, 500), $this->dt(2000, 1, 16, 11, 30, 40, 501), CU::HOURS(), -1 + 24],
            [$this->dt(2000, 1, 15, 12, 30, 40, 500), $this->dt(2000, 1, 16, 12, 30, 40, 499), CU::HOURS(), -1 + 24],
            [$this->dt(2000, 1, 15, 12, 30, 40, 500), $this->dt(2000, 1, 16, 12, 30, 40, 500), CU::HOURS(), 0 + 24],
            [$this->dt(2000, 1, 15, 12, 30, 40, 500), $this->dt(2000, 1, 16, 12, 30, 40, 501), CU::HOURS(), 0 + 24],
            [$this->dt(2000, 1, 15, 12, 30, 40, 500), $this->dt(2000, 1, 16, 13, 30, 40, 499), CU::HOURS(), 0 + 24],
            [$this->dt(2000, 1, 15, 12, 30, 40, 500), $this->dt(2000, 1, 16, 13, 30, 40, 500), CU::HOURS(), 1 + 24],

            [$this->dt(2000, 1, 15, 12, 30, 40, 500), $this->dt(2000, 1, 13, 12, 30, 40, 499), CU::DAYS(), -2],
            [$this->dt(2000, 1, 15, 12, 30, 40, 500), $this->dt(2000, 1, 13, 12, 30, 40, 500), CU::DAYS(), -2],
            [$this->dt(2000, 1, 15, 12, 30, 40, 500), $this->dt(2000, 1, 13, 12, 30, 40, 501), CU::DAYS(), -1],
            [$this->dt(2000, 1, 15, 12, 30, 40, 500), $this->dt(2000, 1, 14, 12, 30, 40, 499), CU::DAYS(), -1],
            [$this->dt(2000, 1, 15, 12, 30, 40, 500), $this->dt(2000, 1, 14, 12, 30, 40, 500), CU::DAYS(), -1],
            [$this->dt(2000, 1, 15, 12, 30, 40, 500), $this->dt(2000, 1, 14, 12, 30, 40, 501), CU::DAYS(), 0],
            [$this->dt(2000, 1, 15, 12, 30, 40, 500), $this->dt(2000, 1, 15, 12, 30, 40, 499), CU::DAYS(), 0],
            [$this->dt(2000, 1, 15, 12, 30, 40, 500), $this->dt(2000, 1, 15, 12, 30, 40, 500), CU::DAYS(), 0],
            [$this->dt(2000, 1, 15, 12, 30, 40, 500), $this->dt(2000, 1, 15, 12, 30, 40, 501), CU::DAYS(), 0],
            [$this->dt(2000, 1, 15, 12, 30, 40, 500), $this->dt(2000, 1, 16, 12, 30, 40, 499), CU::DAYS(), 0],
            [$this->dt(2000, 1, 15, 12, 30, 40, 500), $this->dt(2000, 1, 16, 12, 30, 40, 500), CU::DAYS(), 1],
            [$this->dt(2000, 1, 15, 12, 30, 40, 500), $this->dt(2000, 1, 16, 12, 30, 40, 501), CU::DAYS(), 1],
            [$this->dt(2000, 1, 15, 12, 30, 40, 500), $this->dt(2000, 1, 17, 12, 30, 40, 499), CU::DAYS(), 1],
            [$this->dt(2000, 1, 15, 12, 30, 40, 500), $this->dt(2000, 1, 17, 12, 30, 40, 500), CU::DAYS(), 2],
            [$this->dt(2000, 1, 15, 12, 30, 40, 500), $this->dt(2000, 1, 17, 12, 30, 40, 501), CU::DAYS(), 2],
        ];
    }

    /**
     * @dataProvider data_periodUntilUnit
     */
    public
    function test_until_TemporalUnit(LocalDateTime $dt1, LocalDateTime $dt2, TemporalUnit $unit, $expected)
    {
        $amount = $dt1->until($dt2, $unit);
        $this->assertEquals($amount, $expected);
    }

    /**
     * @dataProvider data_periodUntilUnit
     */
    public
    function test_until_TemporalUnit_negated(LocalDateTime $dt1, LocalDateTime $dt2, TemporalUnit $unit, $expected)
    {
        $amount = $dt2->until($dt1, $unit);
        $this->assertEquals($amount, -$expected);
    }

    /**
     * @dataProvider data_periodUntilUnit
     */
    public
    function test_until_TemporalUnit_between(LocalDateTime $dt1, LocalDateTime $dt2, TemporalUnit $unit, $expected)
    {
        $amount = $unit->between($dt1, $dt2);
        $this->assertEquals($amount, $expected);
    }


    public
    function test_until_convertedType()
    {
        $start = LocalDateTime::of(2010, 6, 30, 2, 30);
        $end = $start->plusDays(2)->atOffset(self::OFFSET_PONE());
        $this->assertEquals($start->until($end, CU::DAYS()), 2);
    }

    /**
     * @expectedException \Celest\DateTimeException
     */
    public
    function test_until_invalidType()
    {
        $start = LocalDateTime::of(2010, 6, 30, 2, 30);
        $start->until(LocalTime::of(11, 30), CU::DAYS());
    }

    public
    function test_until_TemporalUnit_nullEnd()
    {
        TestHelper::assertNullException($this, function () {
            self::TEST_200707_15_12_30_40_987654321()->until(null, CU::HOURS());
        });
    }

    public
    function test_until_TemporalUnit_nullUnit()
    {
        TestHelper::assertNullException($this, function () {
            self::TEST_200707_15_12_30_40_987654321()->until(self::TEST_200707_15_12_30_40_987654321(), null);
        });
    }

//-----------------------------------------------------------------------
// format(DateTimeFormatter)
//-----------------------------------------------------------------------

    public
    function test_format_formatter()
    {
        $f = DateTimeFormatter::ofPattern("y M d H m s");
        $t = LocalDateTime::of(2010, 12, 3, 11, 30, 45)->format($f);
        $this->assertEquals($t, "2010 12 3 11 30 45");
    }

    public
    function test_format_formatter_null()
    {
        TestHelper::assertNullException($this, function () {
            LocalDateTime::of(2010, 12, 3, 11, 30, 45)->format(null);
        });
    }

//-----------------------------------------------------------------------
// atOffset()
//-----------------------------------------------------------------------

    public
    function test_atOffset()
    {
        $t = LocalDateTime::of(2008, 6, 30, 11, 30);
        $this->assertEquals($t->atOffset(self::OFFSET_PTWO()), OffsetDateTime::ofDateTime(LocalDateTime::of(2008, 6, 30, 11, 30), self::OFFSET_PTWO()));
    }

    public
    function test_atOffset_nullZoneOffset()
    {
        TestHelper::assertNullException($this, function () {
            $t = LocalDateTime::of(2008, 6, 30, 11, 30);
            $t->atOffset(null);
        });
    }

//-----------------------------------------------------------------------
// atZone()
//-----------------------------------------------------------------------

    public
    function test_atZone()
    {
        $t = LocalDateTime::of(2008, 6, 30, 11, 30);
        $this->assertEquals($t->atZone(self::ZONE_PARIS()),
            ZonedDateTime::ofDateTime(LocalDateTime::of(2008, 6, 30, 11, 30), self::ZONE_PARIS()));
    }


    public
    function test_atZone_Offset()
    {
        $t = LocalDateTime::of(2008, 6, 30, 11, 30);
        $this->assertEquals($t->atZone(self::OFFSET_PTWO()), ZonedDateTime::ofDateTime(LocalDateTime::of(2008, 6, 30, 11, 30), self::OFFSET_PTWO()));
    }


    public
    function test_atZone_dstGap()
    {
        $t = LocalDateTime::of(2007, 4, 1, 0, 0);
        $this->assertEquals($t->atZone(self::ZONE_GAZA()),
            ZonedDateTime::ofDateTime(LocalDateTime::of(2007, 4, 1, 1, 0), self::ZONE_GAZA()));
    }


    public
    function test_atZone_dstOverlap()
    {
        $t = LocalDateTime::of(2007, 10, 28, 2, 30);
        $this->assertEquals($t->atZone(self::ZONE_PARIS()),
            ZonedDateTime::ofStrict(LocalDateTime::of(2007, 10, 28, 2, 30), self::OFFSET_PTWO(), self::ZONE_PARIS()));
    }

    public
    function test_atZone_nullTimeZone()
    {
        TestHelper::assertNullException($this, function () {
            $t = LocalDateTime::of(2008, 6, 30, 11, 30);
            $t->atZone(null);
        });

    }

//-----------------------------------------------------------------------
// toEpochSecond()
//-----------------------------------------------------------------------

    /**
     * @group long
     */
    public
    function test_toEpochSecond_afterEpoch()
    {
        for ($i = -5; $i < 5; $i++) {
            $offset = ZoneOffset::ofHours($i);
            for ($j = 0; $j < 100000; $j++) {
                $a = LocalDateTime::of(1970, 1, 1, 0, 0)->plusSeconds($j);
                $this->assertEquals($a->toEpochSecond($offset), $j - $i * 3600);
            }
        }
    }

    /**
     * @group long
     */
    public
    function test_toEpochSecond_beforeEpoch()
    {
        for ($i = 0; $i < 100000; $i++) {
            $a = LocalDateTime::of(1970, 1, 1, 0, 0)->minusSeconds($i);
            $this->assertEquals($a->toEpochSecond(ZoneOffset::UTC()), -$i);
        }
    }

//-----------------------------------------------------------------------
// compareTo()
//-----------------------------------------------------------------------

    /**
     * @group long
     */
    public function test_comparisons()
    {
        $this->comparisons_LocalDateTime([
                LocalDate::of(Year::MIN_VALUE, 1, 1),
                LocalDate::of(Year::MIN_VALUE, 12, 31),
                LocalDate::of(-1, 1, 1),
                LocalDate::of(-1, 12, 31),
                LocalDate::of(0, 1, 1),
                LocalDate::of(0, 12, 31),
                LocalDate::of(1, 1, 1),
                LocalDate::of(1, 12, 31),
                LocalDate::of(2008, 1, 1),
                LocalDate::of(2008, 2, 29),
                LocalDate::of(2008, 12, 31),
                LocalDate::of(Year::MAX_VALUE, 1, 1),
                LocalDate::of(Year::MAX_VALUE, 12, 31)]
        );
    }

    function comparisons_LocalDateTime(array $localDates)
    {
        $this->comparisons_LocalDateTime2(
            $localDates, [
                LocalTime::MIDNIGHT(),
                LocalTime::of(0, 0, 0, 999999999),
                LocalTime::of(0, 0, 59, 0),
                LocalTime::of(0, 0, 59, 999999999),
                LocalTime::of(0, 59, 0, 0),
                LocalTime::of(0, 59, 59, 999999999),
                LocalTime::NOON(),
                LocalTime::of(12, 0, 0, 999999999),
                LocalTime::of(12, 0, 59, 0),
                LocalTime::of(12, 0, 59, 999999999),
                LocalTime::of(12, 59, 0, 0),
                LocalTime::of(12, 59, 59, 999999999),
                LocalTime::of(23, 0, 0, 0),
                LocalTime::of(23, 0, 0, 999999999),
                LocalTime::of(23, 0, 59, 0),
                LocalTime::of(23, 0, 59, 999999999),
                LocalTime::of(23, 59, 0, 0),
                LocalTime::of(23, 59, 59, 999999999)]
        );
    }

    function comparisons_LocalDateTime2(array $localDates, array $localTimes)
    {
        $localDateTimes = [];
        foreach ($localDates as $localDate) {
            foreach ($localTimes as $localTime) {
                $localDateTimes[] = LocalDateTime::ofDateAndTime($localDate, $localTime);
            }
        }

        $this->doTest_comparisons_LocalDateTime($localDateTimes);
    }

    function doTest_comparisons_LocalDateTime(array $localDateTimes)
    {
        for ($i = 0; $i < count($localDateTimes); $i++) {
            $a = $localDateTimes[$i];
            for ($j = 0; $j < count($localDateTimes); $j++) {
                $b = $localDateTimes[$j];
                if ($i < $j) {
                    $this->assertTrue($a->compareTo($b) < 0, $a . " <=> " . $b);
                    $this->assertEquals($a->isBefore($b), true, $a . " <=> " . $b);
                    $this->assertEquals($a->isAfter($b), false, $a . " <=> " . $b);
                    $this->assertEquals($a->equals($b), false, $a . " <=> " . $b);
                } else if ($i > $j) {
                    $this->assertTrue($a->compareTo($b) > 0, $a . " <=> " . $b);
                    $this->assertEquals($a->isBefore($b), false, $a . " <=> " . $b);
                    $this->assertEquals($a->isAfter($b), true, $a . " <=> " . $b);
                    $this->assertEquals($a->equals($b), false, $a . " <=> " . $b);
                } else {
                    $this->assertEquals($a->compareTo($b), 0, $a . " <=> " . $b);
                    $this->assertEquals($a->isBefore($b), false, $a . " <=> " . $b);
                    $this->assertEquals($a->isAfter($b), false, $a . " <=> " . $b);
                    $this->assertEquals($a->equals($b), true, $a . " <=> " . $b);
                }
            }
        }
    }

    public
    function test_compareTo_ObjectNull()
    {
        TestHelper::assertNullException($this, function () {
            self::TEST_200707_15_12_30_40_987654321()->compareTo(null);
        });
    }

    public
    function test_isBefore_ObjectNull()
    {
        TestHelper::assertNullException($this, function () {
            self::TEST_200707_15_12_30_40_987654321()->isBefore(null);
        });
    }

    public
    function test_isAfter_ObjectNull()
    {
        TestHelper::assertNullException($this, function () {
            self::TEST_200707_15_12_30_40_987654321()->isAfter(null);
        });
    }

    public
    function compareToNonLocalDateTime()
    {
        $c = self::TEST_200707_15_12_30_40_987654321();
        $c->compareTo(new Object());
    }

//-----------------------------------------------------------------------
// equals()
//-----------------------------------------------------------------------
    function provider_sampleDateTimes()
    {
        $sampleDates = $this->provider_sampleDates();
        $sampleTimes = $this->provider_sampleTimes();


        $ret = [];
        foreach ($sampleDates as $sampleDate) {
            foreach ($sampleTimes as $sampleTime) {
                $ret[] = array_merge($sampleDate, $sampleTime);
            }
        }

        return $ret;
    }

    /**
     * @dataProvider provider_sampleDateTimes
     */
    public
    function test_equals_true($y, $m, $d, $h, $mi, $s, $n)
    {
        $a = LocalDateTime::of($y, $m, $d, $h, $mi, $s, $n);
        $b = LocalDateTime::of($y, $m, $d, $h, $mi, $s, $n);
        $this->assertTrue($a->equals($b));
    }

    /**
     * @dataProvider provider_sampleDateTimes
     */
    public
    function test_equals_false_year_differs($y, $m, $d, $h, $mi, $s, $n)
    {
        $a = LocalDateTime::of($y, $m, $d, $h, $mi, $s, $n);
        $b = LocalDateTime::of($y + 1, $m, $d, $h, $mi, $s, $n);
        $this->assertFalse($a->equals($b));
    }

    /**
     * @dataProvider provider_sampleDateTimes
     */
    public
    function test_equals_false_month_differs($y, $m, $d, $h, $mi, $s, $n)
    {
        $a = LocalDateTime::of($y, $m, $d, $h, $mi, $s, $n);
        $b = LocalDateTime::of($y, $m + 1, $d, $h, $mi, $s, $n);
        $this->assertFalse($a->equals($b));
    }

    /**
     * @dataProvider provider_sampleDateTimes
     */
    public
    function test_equals_false_day_differs($y, $m, $d, $h, $mi, $s, $n)
    {
        $a = LocalDateTime::of($y, $m, $d, $h, $mi, $s, $n);
        $b = LocalDateTime::of($y, $m, $d + 1, $h, $mi, $s, $n);
        $this->assertFalse($a->equals($b));
    }

    /**
     * @dataProvider provider_sampleDateTimes
     */
    public
    function test_equals_false_hour_differs($y, $m, $d, $h, $mi, $s, $n)
    {
        $a = LocalDateTime::of($y, $m, $d, $h, $mi, $s, $n);
        $b = LocalDateTime::of($y, $m, $d, $h + 1, $mi, $s, $n);
        $this->assertFalse($a->equals($b));
    }

    /**
     * @dataProvider provider_sampleDateTimes
     */
    public
    function test_equals_false_minute_differs($y, $m, $d, $h, $mi, $s, $n)
    {
        $a = LocalDateTime::of($y, $m, $d, $h, $mi, $s, $n);
        $b = LocalDateTime::of($y, $m, $d, $h, $mi + 1, $s, $n);
        $this->assertFalse($a->equals($b));
    }

    /**
     * @dataProvider provider_sampleDateTimes
     */
    public
    function test_equals_false_second_differs($y, $m, $d, $h, $mi, $s, $n)
    {
        $a = LocalDateTime::of($y, $m, $d, $h, $mi, $s, $n);
        $b = LocalDateTime::of($y, $m, $d, $h, $mi, $s + 1, $n);
        $this->assertFalse($a->equals($b));
    }

    /**
     * @dataProvider provider_sampleDateTimes
     */
    public
    function test_equals_false_nano_differs($y, $m, $d, $h, $mi, $s, $n)
    {
        $a = LocalDateTime::of($y, $m, $d, $h, $mi, $s, $n);
        $b = LocalDateTime::of($y, $m, $d, $h, $mi, $s, $n + 1);
        $this->assertFalse($a->equals($b));
    }


    public
    function test_equals_itself_true()
    {
        $this->assertEquals(self::TEST_200707_15_12_30_40_987654321()->equals(self::TEST_200707_15_12_30_40_987654321()), true);
    }


    public
    function test_equals_string_false()
    {
        $this->assertEquals(self::TEST_200707_15_12_30_40_987654321()->equals("2007-07-15T12:30:40.987654321"), false);
    }


    public
    function test_equals_null_false()
    {
        $this->assertEquals(self::TEST_200707_15_12_30_40_987654321()->equals(null), false);
    }

//-----------------------------------------------------------------------
// hashCode()
//-----------------------------------------------------------------------
    /**
     * @dataProvider provider_sampleDateTimes
     */
    /*public function test_hashCode($y, $m, $d, $h, $mi, $s, $n)
    {
        $a = LocalDateTime::of($y, $m, $d, $h, $mi, $s, $n);
        $this->assertEquals($a->hashCode(), $a->hashCode());
        $b = LocalDateTime::of($y, $m, $d, $h, $mi, $s, $n);
        $this->assertEquals($a->hashCode(), $b->hashCode());
    }*/

//-----------------------------------------------------------------------
// toString()
//-----------------------------------------------------------------------
    function provider_sampleToString()
    {
        return [
            [2008, 7, 5, 2, 1, 0, 0, "2008-07-05T02:01"],
            [2007, 12, 31, 23, 59, 1, 0, "2007-12-31T23:59:01"],
            [999, 12, 31, 23, 59, 59, 990000000, "0999-12-31T23:59:59.990"],
            [-1, 1, 2, 23, 59, 59, 999990000, "-0001-01-02T23:59:59.999990"],
            [-2008, 1, 2, 23, 59, 59, 999999990, "-2008-01-02T23:59:59.999999990"],
        ];
    }

    /**
     * @dataProvider provider_sampleToString
     */
    public
    function test_toString($y, $m, $d, $h, $mi, $s, $n, $expected)
    {
        $t = LocalDateTime::of($y, $m, $d, $h, $mi, $s, $n);
        $str = $t->__toString();
        $this->assertEquals($str, $expected);
    }

    private
    function dtNoon($year, $month, $day)
    {
        return LocalDateTime::of($year, $month, $day, 12, 0);
    }

    private
    function dtEpoch($hour, $min, $sec, $nano)
    {
        return LocalDateTime::of(1970, 1, 1, $hour, $min, $sec, $nano);
    }

    private
    function dt($year, $month, $day, $hour, $min, $sec, $nano)
    {
        return LocalDateTime::of($year, $month, $day, $hour, $min, $sec, $nano);
    }

}
