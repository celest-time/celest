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
 * Copyright (c) 2007-2012, Stephen Colebourne & Michael Nascimento Santos
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
use Celest\Temporal\MockFieldNoValue;
use Celest\Temporal\TemporalAccessor;
use Celest\Temporal\TemporalAdjusters;
use Celest\Temporal\TemporalQueries;
use Celest\Temporal\TemporalQuery;
use Celest\Temporal\TemporalUnit;

/**
 * Test LocalDate.
 */
class TCKLocalDateTest extends AbstractDateTimeTest
{

    private static function OFFSET_PONE()
    {
        return ZoneOffset::ofHours(1);
    }

    private static function OFFSET_PTWO()
    {
        return ZoneOffset::ofHours(2);
    }

    private static function ZONE_PARIS()
    {
        return ZoneId::of("Europe/Paris");
    }

    private static function ZONE_GAZA()
    {
        return ZoneId::of("Asia/Gaza");
    }

    private static function TEST_2007_07_15()
    {
        return LocalDate::of(2007, 7, 15);
    }

    private static function MAX_VALID_EPOCHDAYS()
    {
        return LocalDate::MAX()->toEpochDay();
    }

    private static function MIN_VALID_EPOCHDAYS()
    {
        return LocalDate::MIN()->toEpochDay();
    }

    private static function MAX_INSTANT()
    {
        return LocalDate::MAX()->atStartOfDayWithZone(ZoneOffset::UTC())->toInstant();
    }

    private static function MIN_INSTANT()
    {
        return LocalDate::MIN()->atStartOfDayWithZone(ZoneOffset::UTC())->toInstant();
    }

    //-----------------------------------------------------------------------
    protected function samples()
    {
        return [self::TEST_2007_07_15(), LocalDate::MAX(), LocalDate::MIN()];
    }

    protected function validFields()
    {
        return [
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
        return array_diff(CF::values(), $this->validFields());
    }

    //-----------------------------------------------------------------------
    private function check(LocalDate $test, $y, $m, $d)
    {
        $this->assertEquals($test->getYear(), $y);
        $this->assertEquals($test->getMonth()->getValue(), $m);
        $this->assertEquals($test->getDayOfMonth(), $d);
        $this->assertEquals($test, $test);
        $this->assertEquals(LocalDate::of($y, $m, $d), $test);
    }

    //-----------------------------------------------------------------------
    // constants
    //-----------------------------------------------------------------------

    public function test_constant_MIN()
    {
        $this->check(LocalDate::MIN(), Year::MIN_VALUE, 1, 1);
    }


    public function test_constant_MAX()
    {
        $this->check(LocalDate::MAX(), Year::MAX_VALUE, 12, 31);
    }

    //-----------------------------------------------------------------------
    // now()
    //-----------------------------------------------------------------------

    public function test_now()
    {
        $expected = LocalDate::nowOf(Clock::systemDefaultZone());
        $test = LocalDate::now();
        for ($i = 0; $i < 100; $i++) {
            if ($expected->equals($test)) {
                $this->assertTrue(true);
                return;
            }
            $expected = LocalDate::nowOf(Clock::systemDefaultZone());
            $test = LocalDate::now();
        }
        $this->assertEquals($test, $expected);
    }

    //-----------------------------------------------------------------------
    // now(ZoneId)
    //-----------------------------------------------------------------------
    public function test_now_ZoneId_nullZoneId()
    {
        TestHelper::assertNullException($this, function () {
            LocalDate::nowFrom(null);
        });
    }


    public function test_now_ZoneId()
    {
        $zone = ZoneId::of("UTC+01:02:03");
        $expected = LocalDate::nowOf(Clock::system($zone));
        $test = LocalDate::nowFrom($zone);
        for ($i = 0; $i < 100; $i++) {
            if ($expected->equals($test)) {
                $this->assertTrue(true);
                return;
            }
            $expected = LocalDate::nowOf(Clock::system($zone));
            $test = LocalDate::nowFrom($zone);
        }
        $this->assertEquals($test, $expected);
    }

    //-----------------------------------------------------------------------
    // now(Clock)
    //-----------------------------------------------------------------------
    public function test_now_Clock_nullClock()
    {
        TestHelper::assertNullException($this, function () {
            LocalDate::nowOf(null);
        });
    }


    /**
     * @group long
     */
    public function test_now_Clock_allSecsInDay_utc()
    {
        for ($i = 0; $i < (2 * 24 * 60 * 60); $i++) {
            $instant = Instant::ofEpochSecond($i);
            $clock = Clock::fixed($instant, ZoneOffset::UTC());
            $test = LocalDate::nowOf($clock);
            $this->assertEquals($test->getYear(), 1970);
            $this->assertEquals($test->getMonth(), Month::JANUARY());
            $this->assertEquals($test->getDayOfMonth(), ($i < 24 * 60 * 60 ? 1 : 2));
        }
    }


    /**
     * @group long
     */
    public function test_now_Clock_allSecsInDay_offset()
    {
        for ($i = 0; $i < (2 * 24 * 60 * 60); $i++) {
            $instant = Instant::ofEpochSecond($i);
            $clock = Clock::fixed($instant->minusSeconds(self::OFFSET_PONE()->getTotalSeconds()), self::OFFSET_PONE());
            $test = LocalDate::nowOf($clock);
            $this->assertEquals($test->getYear(), 1970);
            $this->assertEquals($test->getMonth(), Month::JANUARY());
            $this->assertEquals($test->getDayOfMonth(), ($i < 24 * 60 * 60) ? 1 : 2);
        }
    }


    /**
     * @group long
     */
    public function test_now_Clock_allSecsInDay_beforeEpoch()
    {
        for ($i = -1; $i >= -(2 * 24 * 60 * 60); $i--) {
            $instant = Instant::ofEpochSecond($i);
            $clock = Clock::fixed($instant, ZoneOffset::UTC());
            $test = LocalDate::nowOf($clock);
            $this->assertEquals($test->getYear(), 1969);
            $this->assertEquals($test->getMonth(), Month::DECEMBER());
            $this->assertEquals($test->getDayOfMonth(), ($i >= -24 * 60 * 60 ? 31 : 30));
        }
    }

    //-----------------------------------------------------------------------

    public function test_now_Clock_maxYear()
    {
        $clock = Clock::fixed(self::MAX_INSTANT(), ZoneOffset::UTC());
        $test = LocalDate::nowOf($clock);
        $this->assertEquals($test, LocalDate::MAX());
    }

    /**
     * @expectedException \Celest\DateTimeException
     */
    public function test_now_Clock_tooBig()
    {
        $clock = Clock::fixed(self::MAX_INSTANT()->plusSeconds(24 * 60 * 60), ZoneOffset::UTC());
        LocalDate::nowOf($clock);
    }


    public function test_now_Clock_minYear()
    {
        $clock = Clock::fixed(self::MIN_INSTANT(), ZoneOffset::UTC());
        $test = LocalDate::nowOf($clock);
        $this->assertEquals($test, LocalDate::MIN());
    }

    /**
     * @expectedException \Celest\DateTimeException
     */
    public function test_now_Clock_tooLow()
    {
        $clock = Clock::fixed(self::MIN_INSTANT()->minusNanos(1), ZoneOffset::UTC());
        LocalDate::nowOf($clock);
    }

    //-----------------------------------------------------------------------
    // of() factories
    //-----------------------------------------------------------------------

    public function test_factory_of_intsMonth()
    {
        $this->assertEquals(self::TEST_2007_07_15(), LocalDate::ofMonth(2007, Month::JULY(), 15));
    }

    /**
     * @expectedException \Celest\DateTimeException
     */
    public function test_factory_of_intsMonth_29febNonLeap()
    {
        LocalDate::ofMonth(2007, Month::FEBRUARY(), 29);
    }

    /**
     * @expectedException \Celest\DateTimeException
     */
    public function test_factory_of_intsMonth_31apr()
    {
        LocalDate::ofMonth(2007, Month::APRIL(), 31);
    }

    /**
     * @expectedException \Celest\DateTimeException
     */
    public function test_factory_of_intsMonth_dayTooLow()
    {
        LocalDate::ofMonth(2007, Month::JANUARY(), 0);
    }

    /**
     * @expectedException \Celest\DateTimeException
     */
    public function test_factory_of_intsMonth_dayTooHigh()
    {
        LocalDate::ofMonth(2007, Month::JANUARY(), 32);
    }

    public function test_factory_of_intsMonth_nullMonth()
    {
        TestHelper::assertNullException($this, function () {
            LocalDate::ofMonth(2007, null, 30);
        });
    }

    /**
     * @expectedException \Celest\DateTimeException
     */
    public function test_factory_of_intsMonth_yearTooLow()
    {
        LocalDate::ofMonth(Integer::MIN_VALUE, Month::JANUARY(), 1);
    }

    //-----------------------------------------------------------------------

    public function test_factory_of_ints()
    {
        $this->check(self::TEST_2007_07_15(), 2007, 7, 15);
    }

    /**
     * @expectedException \Celest\DateTimeException
     */
    public function test_factory_of_ints_29febNonLeap()
    {
        LocalDate::of(2007, 2, 29);
    }

    /**
     * @expectedException \Celest\DateTimeException
     */
    public function test_factory_of_ints_31apr()
    {
        LocalDate::of(2007, 4, 31);
    }

    /**
     * @expectedException \Celest\DateTimeException
     */
    public function test_factory_of_ints_dayTooLow()
    {
        LocalDate::of(2007, 1, 0);
    }

    /**
     * @expectedException \Celest\DateTimeException
     */
    public function test_factory_of_ints_dayTooHigh()
    {
        LocalDate::of(2007, 1, 32);
    }

    /**
     * @expectedException \Celest\DateTimeException
     */
    public function test_factory_of_ints_monthTooLow()
    {
        LocalDate::of(2007, 0, 1);
    }

    /**
     * @expectedException \Celest\DateTimeException
     */
    public function test_factory_of_ints_monthTooHigh()
    {
        LocalDate::of(2007, 13, 1);
    }

    /**
     * @expectedException \Celest\DateTimeException
     */
    public function test_factory_of_ints_yearTooLow()
    {
        LocalDate::of(Integer::MIN_VALUE, 1, 1);
    }

    //-----------------------------------------------------------------------

    public function test_factory_ofYearDay_ints_nonLeap()
    {
        $date = LocalDate::of(2007, 1, 1);
        for ($i = 1; $i < 365; $i++) {
            $this->assertEquals(LocalDate::ofYearDay(2007, $i), $date);
            $date = $this->next($date);
        }
    }


    public function test_factory_ofYearDay_ints_leap()
    {
        $date = LocalDate::of(2008, 1, 1);
        for ($i = 1; $i < 366; $i++) {
            $this->assertEquals(LocalDate::ofYearDay(2008, $i), $date);
            $date = $this->next($date);
        }
    }

    /**
     * @expectedException \Celest\DateTimeException
     */
    public function test_factory_ofYearDay_ints_366nonLeap()
    {
        LocalDate::ofYearDay(2007, 366);
    }

    /**
     * @expectedException \Celest\DateTimeException
     */
    public function test_factory_ofYearDay_ints_dayTooLow()
    {
        LocalDate::ofYearDay(2007, 0);
    }

    /**
     * @expectedException \Celest\DateTimeException
     */
    public function test_factory_ofYearDay_ints_dayTooHigh()
    {
        LocalDate::ofYearDay(2007, 367);
    }

    /**
     * @expectedException \Celest\DateTimeException
     */
    public function test_factory_ofYearDay_ints_yearTooLow()
    {
        LocalDate::ofYearDay(Integer::MIN_VALUE, 1);
    }

    //-----------------------------------------------------------------------
    // Since plusDays/minusDays actually depends on MJDays, it cannot be used for testing
    private function next(LocalDate $date)
    {
        $newDayOfMonth = $date->getDayOfMonth() + 1;
        if ($newDayOfMonth <= $date->getMonth()->length(Year::isLeapYear($date->getYear()))) {
            return $date->withDayOfMonth($newDayOfMonth);
        }
        $date = $date->withDayOfMonth(1);
        if ($date->getMonth() == Month::DECEMBER()) {
            $date = $date->withYear($date->getYear() + 1);
        }
        return $date->adjust($date->getMonth()->plus(1));
    }

    private function previous(LocalDate $date)
    {
        $newDayOfMonth = $date->getDayOfMonth() - 1;
        if ($newDayOfMonth > 0) {
            return $date->withDayOfMonth($newDayOfMonth);
        }
        $date = $date->adjust($date->getMonth()->minus(1));
        if ($date->getMonth() == Month::DECEMBER()) {
            $date = $date->withYear($date->getYear() - 1);
        }
        return $date->withDayOfMonth($date->getMonth()->length(Year::isLeapYear($date->getYear())));
    }

    //-----------------------------------------------------------------------
    // ofEpochDay()
    //-----------------------------------------------------------------------
    /**
     * @group long
     */
    public function test_factory_ofEpochDay()
    {
        $date_0000_01_01 = -678941 - 40587;
        $this->assertEquals(LocalDate::ofEpochDay(0), LocalDate::of(1970, 1, 1));
        $this->assertEquals(LocalDate::ofEpochDay($date_0000_01_01), LocalDate::of(0, 1, 1));
        $this->assertEquals(LocalDate::ofEpochDay($date_0000_01_01 - 1), LocalDate::of(-1, 12, 31));
        $this->assertEquals(LocalDate::ofEpochDay(self::MAX_VALID_EPOCHDAYS()), LocalDate::of(Year::MAX_VALUE, 12, 31));
        $this->assertEquals(LocalDate::ofEpochDay(self::MIN_VALID_EPOCHDAYS()), LocalDate::of(Year::MIN_VALUE, 1, 1));

        $test = LocalDate::of(0, 1, 1);
        for ($i = $date_0000_01_01; $i < 700000; $i++) {
            $this->assertEquals(LocalDate::ofEpochDay($i), $test);
            $test = $this->next($test);
        }
        $test = LocalDate::of(0, 1, 1);
        for ($i = $date_0000_01_01; $i > -2000000; $i--) {
            $this->assertEquals(LocalDate::ofEpochDay($i), $test);
            $test = $this->previous($test);
        }
    }

    /**
     * @expectedException \Celest\DateTimeException
     */
    public function test_factory_ofEpochDay_aboveMax()
    {
        LocalDate::ofEpochDay(self::MAX_VALID_EPOCHDAYS() + 1);
    }

    /**
     * @expectedException \Celest\DateTimeException
     */
    public function test_factory_ofEpochDay_belowMin()
    {
        LocalDate::ofEpochDay(self::MIN_VALID_EPOCHDAYS() - 1);
    }

    //-----------------------------------------------------------------------
    // from()
    //-----------------------------------------------------------------------

    public function test_from_TemporalAccessor()
    {
        $this->assertEquals(LocalDate::from(LocalDate::of(2007, 7, 15)), LocalDate::of(2007, 7, 15));
        $this->assertEquals(LocalDate::from(LocalDateTime::of(2007, 7, 15, 12, 30)), LocalDate::of(2007, 7, 15));
    }

    /**
     * @expectedException \Celest\DateTimeException
     */
    public function test_from_TemporalAccessor_invalid_noDerive()
    {
        LocalDate::from(LocalTime::of(12, 30));
    }

    public function test_from_TemporalAccessor_null()
    {
        TestHelper::assertNullException($this, function () {
            LocalDate::from(null);
        });
    }

    //-----------------------------------------------------------------------
    // parse()
    //-----------------------------------------------------------------------

    /**
     * @dataProvider provider_sampleToString
     */
    public function test_factory_parse_validText($y, $m, $d, $parsable)
    {
        $t = LocalDate::parse($parsable);
        $this->assertNotNull($t, $parsable);
        $this->assertEquals($t->getYear(), $y, $parsable);
        $this->assertEquals($t->getMonth()->getValue(), $m, $parsable);
        $this->assertEquals($t->getDayOfMonth(), $d, $parsable);
    }

    function provider_sampleBadParse()
    {
        return [
            ["2008/07/05"],
            ["10000-01-01"],
            ["2008-1-1"],
            ["2008--01"],
            ["ABCD-02-01"],
            ["2008-AB-01"],
            ["2008-02-AB"],
            ["-0000-02-01"],
            ["2008-02-01Z"],
            ["2008-02-01+01:00"],
            ["2008-02-01+01:00[Europe/Paris]"],
        ];
    }

    /**
     * @dataProvider provider_sampleBadParse
     * @expectedException \Celest\DateTimeParseException
     */
    public function test_factory_parse_invalidText($unparsable)
    {
        LocalDate::parse($unparsable);
    }

    /**
     * @expectedException \Celest\DateTimeParseException
     */
    public function test_factory_parse_illegalValue()
    {
        LocalDate::parse("2008-06-32");
    }

    /**
     * @expectedException \Celest\DateTimeParseException
     */
    public function test_factory_parse_invalidValue()
    {
        LocalDate::parse("2008-06-31");
    }

    public function test_factory_parse_nullText()
    {
        TestHelper::assertNullException($this, function () {
            LocalDate::parse(null);
        });
    }

    //-----------------------------------------------------------------------
    // parse(DateTimeFormatter)
    //-----------------------------------------------------------------------

    public function test_factory_parse_formatter()
    {
        $f = DateTimeFormatter::ofPattern("y M d");
        $test = LocalDate::parseWith("2010 12 3", $f);
        $this->assertEquals($test, LocalDate::of(2010, 12, 3));
    }

    public function test_factory_parse_formatter_nullText()
    {
        TestHelper::assertNullException($this, function () {
            $f = DateTimeFormatter::ofPattern("y M d");
            LocalDate::parseWith(null, $f);
        });

    }

    public function test_factory_parse_formatter_nullFormatter()
    {
        TestHelper::assertNullException($this, function () {
            LocalDate::parseWith("ANY", null);
        });
    }

    //-----------------------------------------------------------------------
    // isSupported(TemporalField)
    //-----------------------------------------------------------------------

    public function test_isSupported_TemporalField()
    {
        //$this->assertEquals(self::TEST_2007_07_15()->isSupported((TemporalField) null), false); TODo
        $this->assertEquals(self::TEST_2007_07_15()->isSupported(CF::NANO_OF_SECOND()), false);
        $this->assertEquals(self::TEST_2007_07_15()->isSupported(CF::NANO_OF_DAY()), false);
        $this->assertEquals(self::TEST_2007_07_15()->isSupported(CF::MICRO_OF_SECOND()), false);
        $this->assertEquals(self::TEST_2007_07_15()->isSupported(CF::MICRO_OF_DAY()), false);
        $this->assertEquals(self::TEST_2007_07_15()->isSupported(CF::MILLI_OF_SECOND()), false);
        $this->assertEquals(self::TEST_2007_07_15()->isSupported(CF::MILLI_OF_DAY()), false);
        $this->assertEquals(self::TEST_2007_07_15()->isSupported(CF::SECOND_OF_MINUTE()), false);
        $this->assertEquals(self::TEST_2007_07_15()->isSupported(CF::SECOND_OF_DAY()), false);
        $this->assertEquals(self::TEST_2007_07_15()->isSupported(CF::MINUTE_OF_HOUR()), false);
        $this->assertEquals(self::TEST_2007_07_15()->isSupported(CF::MINUTE_OF_DAY()), false);
        $this->assertEquals(self::TEST_2007_07_15()->isSupported(CF::HOUR_OF_AMPM()), false);
        $this->assertEquals(self::TEST_2007_07_15()->isSupported(CF::CLOCK_HOUR_OF_AMPM()), false);
        $this->assertEquals(self::TEST_2007_07_15()->isSupported(CF::HOUR_OF_DAY()), false);
        $this->assertEquals(self::TEST_2007_07_15()->isSupported(CF::CLOCK_HOUR_OF_DAY()), false);
        $this->assertEquals(self::TEST_2007_07_15()->isSupported(CF::AMPM_OF_DAY()), false);
        $this->assertEquals(self::TEST_2007_07_15()->isSupported(CF::DAY_OF_WEEK()), true);
        $this->assertEquals(self::TEST_2007_07_15()->isSupported(CF::ALIGNED_DAY_OF_WEEK_IN_MONTH()), true);
        $this->assertEquals(self::TEST_2007_07_15()->isSupported(CF::ALIGNED_DAY_OF_WEEK_IN_YEAR()), true);
        $this->assertEquals(self::TEST_2007_07_15()->isSupported(CF::DAY_OF_MONTH()), true);
        $this->assertEquals(self::TEST_2007_07_15()->isSupported(CF::DAY_OF_YEAR()), true);
        $this->assertEquals(self::TEST_2007_07_15()->isSupported(CF::EPOCH_DAY()), true);
        $this->assertEquals(self::TEST_2007_07_15()->isSupported(CF::ALIGNED_WEEK_OF_MONTH()), true);
        $this->assertEquals(self::TEST_2007_07_15()->isSupported(CF::ALIGNED_WEEK_OF_YEAR()), true);
        $this->assertEquals(self::TEST_2007_07_15()->isSupported(CF::MONTH_OF_YEAR()), true);
        $this->assertEquals(self::TEST_2007_07_15()->isSupported(CF::PROLEPTIC_MONTH()), true);
        $this->assertEquals(self::TEST_2007_07_15()->isSupported(CF::YEAR()), true);
        $this->assertEquals(self::TEST_2007_07_15()->isSupported(CF::YEAR_OF_ERA()), true);
        $this->assertEquals(self::TEST_2007_07_15()->isSupported(CF::ERA()), true);
        $this->assertEquals(self::TEST_2007_07_15()->isSupported(CF::INSTANT_SECONDS()), false);
        $this->assertEquals(self::TEST_2007_07_15()->isSupported(CF::OFFSET_SECONDS()), false);
    }

    //-----------------------------------------------------------------------
    // isSupported(TemporalUnit)
    //-----------------------------------------------------------------------

    public function test_isSupported_TemporalUnit()
    {
        //$this->assertEquals(self::TEST_2007_07_15()->isSupported((TemporalUnit) null), false); TODO
        $this->assertEquals(self::TEST_2007_07_15()->isUnitSupported(CU::NANOS()), false);
        $this->assertEquals(self::TEST_2007_07_15()->isUnitSupported(CU::MICROS()), false);
        $this->assertEquals(self::TEST_2007_07_15()->isUnitSupported(CU::MILLIS()), false);
        $this->assertEquals(self::TEST_2007_07_15()->isUnitSupported(CU::SECONDS()), false);
        $this->assertEquals(self::TEST_2007_07_15()->isUnitSupported(CU::MINUTES()), false);
        $this->assertEquals(self::TEST_2007_07_15()->isUnitSupported(CU::HOURS()), false);
        $this->assertEquals(self::TEST_2007_07_15()->isUnitSupported(CU::HALF_DAYS()), false);
        $this->assertEquals(self::TEST_2007_07_15()->isUnitSupported(CU::DAYS()), true);
        $this->assertEquals(self::TEST_2007_07_15()->isUnitSupported(CU::WEEKS()), true);
        $this->assertEquals(self::TEST_2007_07_15()->isUnitSupported(CU::MONTHS()), true);
        $this->assertEquals(self::TEST_2007_07_15()->isUnitSupported(CU::YEARS()), true);
        $this->assertEquals(self::TEST_2007_07_15()->isUnitSupported(CU::DECADES()), true);
        $this->assertEquals(self::TEST_2007_07_15()->isUnitSupported(CU::CENTURIES()), true);
        $this->assertEquals(self::TEST_2007_07_15()->isUnitSupported(CU::MILLENNIA()), true);
        $this->assertEquals(self::TEST_2007_07_15()->isUnitSupported(CU::ERAS()), true);
        $this->assertEquals(self::TEST_2007_07_15()->isUnitSupported(CU::FOREVER()), false);
    }

    //-----------------------------------------------------------------------
    // get(TemporalField)
    //-----------------------------------------------------------------------

    public function test_get_TemporalField()
    {
        $test = LocalDate::of(2008, 6, 30);
        $this->assertEquals($test->get(CF::YEAR()), 2008);
        $this->assertEquals($test->get(CF::MONTH_OF_YEAR()), 6);
        $this->assertEquals($test->get(CF::YEAR_OF_ERA()), 2008);
        $this->assertEquals($test->get(CF::ERA()), 1);
        $this->assertEquals($test->get(CF::DAY_OF_MONTH()), 30);
        $this->assertEquals($test->get(CF::DAY_OF_WEEK()), 1);
        $this->assertEquals($test->get(CF::DAY_OF_YEAR()), 182);
    }


    public function test_getLong_TemporalField()
    {
        $test = LocalDate::of(2008, 6, 30);
        $this->assertEquals($test->getLong(CF::YEAR()), 2008);
        $this->assertEquals($test->getLong(CF::MONTH_OF_YEAR()), 6);
        $this->assertEquals($test->getLong(CF::YEAR_OF_ERA()), 2008);
        $this->assertEquals($test->getLong(CF::ERA()), 1);
        $this->assertEquals($test->getLong(CF::PROLEPTIC_MONTH()), 2008 * 12 + 6 - 1);
        $this->assertEquals($test->getLong(CF::DAY_OF_MONTH()), 30);
        $this->assertEquals($test->getLong(CF::DAY_OF_WEEK()), 1);
        $this->assertEquals($test->getLong(CF::DAY_OF_YEAR()), 182);
    }

    //-----------------------------------------------------------------------
    // query(TemporalQuery)
    //-----------------------------------------------------------------------
    function data_query()
    {
        return [
            [self::TEST_2007_07_15(), TemporalQueries::chronology(), IsoChronology::INSTANCE()],
            [self::TEST_2007_07_15(), TemporalQueries::zoneId(), null],
            [self::TEST_2007_07_15(), TemporalQueries::precision(), CU::DAYS()],
            [self::TEST_2007_07_15(), TemporalQueries::zone(), null],
            [self::TEST_2007_07_15(), TemporalQueries::offset(), null],
            [self::TEST_2007_07_15(), TemporalQueries::localDate(), self::TEST_2007_07_15()],
            [self::TEST_2007_07_15(), TemporalQueries::localTime(), null],
        ];
    }

    /**
     * @dataProvider data_query
     */
    public function test_query(TemporalAccessor $temporal, TemporalQuery $query, $expected)
    {
        $this->assertEquals($temporal->query($query), $expected);
    }

    /**
     * @dataProvider data_query
     */
    public function test_queryFrom(TemporalAccessor $temporal, TemporalQuery $query, $expected)
    {
        $this->assertEquals($query->queryFrom($temporal), $expected);
    }

    public function test_query_null()
    {
        TestHelper::assertNullException($this, function () {
            self::TEST_2007_07_15()->query(null);
        });
    }

    //-----------------------------------------------------------------------
    // get*()
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

    //-----------------------------------------------------------------------
    /**
     * @dataProvider provider_sampleDates
     */
    public function test_get($y, $m, $d)
    {
        $a = LocalDate::of($y, $m, $d);
        $this->assertEquals($a->getYear(), $y);
        $this->assertEquals($a->getMonth(), Month::of($m));
        $this->assertEquals($a->getDayOfMonth(), $d);
    }

    /**
     * @dataProvider provider_sampleDates
     */
    public function test_getDOY($y, $m, $d)
    {
        $a = LocalDate::of($y, $m, $d);
        $total = 0;
        for ($i = 1; $i < $m; $i++) {
            $total += Month::of($i)->length(Year::isLeapYear($y));
        }
        $doy = $total + $d;
        $this->assertEquals($a->getDayOfYear(), $doy);
    }


    public function test_getDayOfWeek()
    {
        $dow = DayOfWeek::MONDAY();
        foreach (Month::values() as $month) {
            $length = $month->length(false);
            for ($i = 1; $i <= $length; $i++) {
                $d = LocalDate::ofMonth(2007, $month, $i);
                $this->assertSame($d->getDayOfWeek(), $dow);
                $dow = $dow->plus(1);
            }
        }
    }

    //-----------------------------------------------------------------------
    // isLeapYear()
    //-----------------------------------------------------------------------

    public function test_isLeapYear()
    {
        $this->assertEquals(LocalDate::of(1999, 1, 1)->isLeapYear(), false);
        $this->assertEquals(LocalDate::of(2000, 1, 1)->isLeapYear(), true);
        $this->assertEquals(LocalDate::of(2001, 1, 1)->isLeapYear(), false);
        $this->assertEquals(LocalDate::of(2002, 1, 1)->isLeapYear(), false);
        $this->assertEquals(LocalDate::of(2003, 1, 1)->isLeapYear(), false);
        $this->assertEquals(LocalDate::of(2004, 1, 1)->isLeapYear(), true);
        $this->assertEquals(LocalDate::of(2005, 1, 1)->isLeapYear(), false);

        $this->assertEquals(LocalDate::of(1500, 1, 1)->isLeapYear(), false);
        $this->assertEquals(LocalDate::of(1600, 1, 1)->isLeapYear(), true);
        $this->assertEquals(LocalDate::of(1700, 1, 1)->isLeapYear(), false);
        $this->assertEquals(LocalDate::of(1800, 1, 1)->isLeapYear(), false);
        $this->assertEquals(LocalDate::of(1900, 1, 1)->isLeapYear(), false);
    }

    //-----------------------------------------------------------------------
    // lengthOfMonth()
    //-----------------------------------------------------------------------

    public function test_lengthOfMonth_notLeapYear()
    {
        $this->assertEquals(LocalDate::of(2007, 1, 1)->lengthOfMonth(), 31);
        $this->assertEquals(LocalDate::of(2007, 2, 1)->lengthOfMonth(), 28);
        $this->assertEquals(LocalDate::of(2007, 3, 1)->lengthOfMonth(), 31);
        $this->assertEquals(LocalDate::of(2007, 4, 1)->lengthOfMonth(), 30);
        $this->assertEquals(LocalDate::of(2007, 5, 1)->lengthOfMonth(), 31);
        $this->assertEquals(LocalDate::of(2007, 6, 1)->lengthOfMonth(), 30);
        $this->assertEquals(LocalDate::of(2007, 7, 1)->lengthOfMonth(), 31);
        $this->assertEquals(LocalDate::of(2007, 8, 1)->lengthOfMonth(), 31);
        $this->assertEquals(LocalDate::of(2007, 9, 1)->lengthOfMonth(), 30);
        $this->assertEquals(LocalDate::of(2007, 10, 1)->lengthOfMonth(), 31);
        $this->assertEquals(LocalDate::of(2007, 11, 1)->lengthOfMonth(), 30);
        $this->assertEquals(LocalDate::of(2007, 12, 1)->lengthOfMonth(), 31);
    }


    public function test_lengthOfMonth_leapYear()
    {
        $this->assertEquals(LocalDate::of(2008, 1, 1)->lengthOfMonth(), 31);
        $this->assertEquals(LocalDate::of(2008, 2, 1)->lengthOfMonth(), 29);
        $this->assertEquals(LocalDate::of(2008, 3, 1)->lengthOfMonth(), 31);
        $this->assertEquals(LocalDate::of(2008, 4, 1)->lengthOfMonth(), 30);
        $this->assertEquals(LocalDate::of(2008, 5, 1)->lengthOfMonth(), 31);
        $this->assertEquals(LocalDate::of(2008, 6, 1)->lengthOfMonth(), 30);
        $this->assertEquals(LocalDate::of(2008, 7, 1)->lengthOfMonth(), 31);
        $this->assertEquals(LocalDate::of(2008, 8, 1)->lengthOfMonth(), 31);
        $this->assertEquals(LocalDate::of(2008, 9, 1)->lengthOfMonth(), 30);
        $this->assertEquals(LocalDate::of(2008, 10, 1)->lengthOfMonth(), 31);
        $this->assertEquals(LocalDate::of(2008, 11, 1)->lengthOfMonth(), 30);
        $this->assertEquals(LocalDate::of(2008, 12, 1)->lengthOfMonth(), 31);
    }

    //-----------------------------------------------------------------------
    // lengthOfYear()
    //-----------------------------------------------------------------------

    public function test_lengthOfYear()
    {
        $this->assertEquals(LocalDate::of(2007, 1, 1)->lengthOfYear(), 365);
        $this->assertEquals(LocalDate::of(2008, 1, 1)->lengthOfYear(), 366);
    }

    //-----------------------------------------------------------------------
    // with()
    //-----------------------------------------------------------------------

    public function test_with_adjustment()
    {
        $sample = LocalDate::of(2012, 3, 4);
        $adjuster = TemporalAdjusters::fromCallable(function () use ($sample) {
            return $sample;
        });
        $this->assertEquals(self::TEST_2007_07_15()->adjust($adjuster), $sample);
    }

    public function test_with_adjustment_null()
    {
        TestHelper::assertNullException($this, function () {
            self::TEST_2007_07_15()->adjust(null);
        });
    }

    //-----------------------------------------------------------------------
    // with(TemporalField,long)
    //-----------------------------------------------------------------------

    public function test_with_TemporalField_long_normal()
    {
        $t = self::TEST_2007_07_15()->with(CF::YEAR(), 2008);
        $this->assertEquals($t, LocalDate::of(2008, 7, 15));
    }

    public function test_with_TemporalField_long_null()
    {
        TestHelper::assertNullException($this, function () {
            self::TEST_2007_07_15()->with(null, 1);
        });
    }

    /**
     * @expectedException \Celest\DateTimeException
     */
    public function test_with_TemporalField_long_invalidField()
    {
        self::TEST_2007_07_15()->with(MockFieldNoValue::INSTANCE(), 1);
    }

    /**
     * @expectedException \Celest\DateTimeException
     */
    public function test_with_TemporalField_long_timeField()
    {
        self::TEST_2007_07_15()->with(CF::AMPM_OF_DAY(), 1);
    }

    /**
     * @expectedException \Celest\DateTimeException
     */
    public function test_with_TemporalField_long_invalidValue()
    {
        self::TEST_2007_07_15()->with(CF::DAY_OF_WEEK(), -1);
    }

    //-----------------------------------------------------------------------
    // withYear()
    //-----------------------------------------------------------------------

    public function test_withYear_int_normal()
    {
        $t = self::TEST_2007_07_15()->withYear(2008);
        $this->assertEquals($t, LocalDate::of(2008, 7, 15));
    }

    /**
     * @expectedException \Celest\DateTimeException
     */
    public function test_withYear_int_invalid()
    {
        self::TEST_2007_07_15()->withYear(Year::MIN_VALUE - 1);
    }


    public function test_withYear_int_adjustDay()
    {
        $t = LocalDate::of(2008, 2, 29)->withYear(2007);
        $expected = LocalDate::of(2007, 2, 28);
        $this->assertEquals($t, $expected);
    }

    //-----------------------------------------------------------------------
    // withMonth()
    //-----------------------------------------------------------------------

    public function test_withMonth_int_normal()
    {
        $t = self::TEST_2007_07_15()->withMonth(1);
        $this->assertEquals($t, LocalDate::of(2007, 1, 15));
    }

    /**
     * @expectedException \Celest\DateTimeException
     */
    public function test_withMonth_int_invalid()
    {
        self::TEST_2007_07_15()->withMonth(13);
    }


    public function test_withMonth_int_adjustDay()
    {
        $t = LocalDate::of(2007, 12, 31)->withMonth(11);
        $expected = LocalDate::of(2007, 11, 30);
        $this->assertEquals($t, $expected);
    }

    //-----------------------------------------------------------------------
    // withDayOfMonth()
    //-----------------------------------------------------------------------

    public function test_withDayOfMonth_normal()
    {
        $t = self::TEST_2007_07_15()->withDayOfMonth(1);
        $this->assertEquals($t, LocalDate::of(2007, 7, 1));
    }

    /**
     * @expectedException \Celest\DateTimeException
     */
    public function test_withDayOfMonth_illegal()
    {
        self::TEST_2007_07_15()->withDayOfMonth(32);
    }

    /**
     * @expectedException \Celest\DateTimeException
     */
    public function test_withDayOfMonth_invalid()
    {
        LocalDate::of(2007, 11, 30)->withDayOfMonth(31);
    }

    //-----------------------------------------------------------------------
    // withDayOfYear(int)
    //-----------------------------------------------------------------------

    public function test_withDayOfYear_normal()
    {
        $t = self::TEST_2007_07_15()->withDayOfYear(33);
        $this->assertEquals($t, LocalDate::of(2007, 2, 2));
    }

    /**
     * @expectedException \Celest\DateTimeException
     */
    public function test_withDayOfYear_illegal()
    {
        self::TEST_2007_07_15()->withDayOfYear(367);
    }

    /**
     * @expectedException \Celest\DateTimeException
     */
    public function test_withDayOfYear_invalid()
    {
        self::TEST_2007_07_15()->withDayOfYear(366);
    }

    //-----------------------------------------------------------------------
    // plus(Period)
    //-----------------------------------------------------------------------

    public function test_plus_Period_positiveMonths()
    {
        $period = MockSimplePeriod::of(7, CU::MONTHS());
        $t = self::TEST_2007_07_15()->plusAmount($period);
        $this->assertEquals($t, LocalDate::of(2008, 2, 15));
    }


    public function test_plus_Period_negativeDays()
    {
        $period = MockSimplePeriod::of(-25, CU::DAYS());
        $t = self::TEST_2007_07_15()->plusAmount($period);
        $this->assertEquals($t, LocalDate::of(2007, 6, 20));
    }

    /**
     * @expectedException \Celest\DateTimeException
     */
    public function test_plus_Period_timeNotAllowed()
    {
        $period = MockSimplePeriod::of(7, CU::HOURS());
        self::TEST_2007_07_15()->plusAmount($period);
    }

    public function test_plus_Period_null()
    {
        TestHelper::assertNullException($this, function () {
            self::TEST_2007_07_15()->plusAmount(null);
        });
    }

    /**
     * @expectedException \Celest\DateTimeException
     */
    public function test_plus_Period_invalidTooLarge()
    {
        $period = MockSimplePeriod::of(1, CU::YEARS());
        LocalDate::of(Year::MAX_VALUE, 1, 1)->plusAmount($period);
    }

    /**
     * @expectedException \Celest\DateTimeException
     */
    public function test_plus_Period_invalidTooSmall()
    {
        $period = MockSimplePeriod::of(-1, CU::YEARS());
        LocalDate::of(Year::MIN_VALUE, 1, 1)->plusAmount($period);
    }

    //-----------------------------------------------------------------------
    // plus(long,TemporalUnit)
    //-----------------------------------------------------------------------

    public function test_plus_longTemporalUnit_positiveMonths()
    {
        $t = self::TEST_2007_07_15()->plus(7, CU::MONTHS());
        $this->assertEquals($t, LocalDate::of(2008, 2, 15));
    }


    public function test_plus_longTemporalUnit_negativeDays()
    {
        $t = self::TEST_2007_07_15()->plus(-25, CU::DAYS());
        $this->assertEquals($t, LocalDate::of(2007, 6, 20));
    }

    /**
     * @expectedException \Celest\DateTimeException
     */
    public function test_plus_longTemporalUnit_timeNotAllowed()
    {
        self::TEST_2007_07_15()->plus(7, CU::HOURS());
    }

    public function test_plus_longTemporalUnit_null()
    {
        TestHelper::assertNullException($this, function () {
            self::TEST_2007_07_15()->plus(1, null);
        });
    }

    /**
     * @expectedException \Celest\DateTimeException
     */
    public function test_plus_longTemporalUnit_invalidTooLarge()
    {
        LocalDate::of(Year::MAX_VALUE, 1, 1)->plus(1, CU::YEARS());
    }

    /**
     * @expectedException \Celest\DateTimeException
     */
    public function test_plus_longTemporalUnit_invalidTooSmall()
    {
        LocalDate::of(Year::MIN_VALUE, 1, 1)->plus(-1, CU::YEARS());
    }

    //-----------------------------------------------------------------------
    // plusYears()
    //-----------------------------------------------------------------------

    public function test_plusYears_long_normal()
    {
        $t = self::TEST_2007_07_15()->plusYears(1);
        $this->assertEquals($t, LocalDate::of(2008, 7, 15));
    }


    public function test_plusYears_long_negative()
    {
        $t = self::TEST_2007_07_15()->plusYears(-1);
        $this->assertEquals($t, LocalDate::of(2006, 7, 15));
    }


    public function test_plusYears_long_adjustDay()
    {
        $t = LocalDate::of(2008, 2, 29)->plusYears(1);
        $expected = LocalDate::of(2009, 2, 28);
        $this->assertEquals($t, $expected);
    }


    public function test_plusYears_long_big()
    {
        $years = 20 + Year::MAX_VALUE;
        $test = LocalDate::of(-40, 6, 1)->plusYears($years);
        $this->assertEquals($test, LocalDate::of((int)(-40 + $years), 6, 1));
    }

    /**
     * @expectedException \Celest\DateTimeException
     */
    public function test_plusYears_long_invalidTooLarge()
    {
        $test = LocalDate::of(Year::MAX_VALUE, 6, 1);
        $test->plusYears(1);
    }

    /**
     * @expectedException \Celest\DateTimeException
     */
    public function test_plusYears_long_invalidTooLargeMaxAddMax()
    {
        $test = LocalDate::of(Year::MAX_VALUE, 12, 1);
        $test->plusYears(Long::MAX_VALUE);
    }

    /**
     * @expectedException \Celest\DateTimeException
     */
    public function test_plusYears_long_invalidTooLargeMaxAddMin()
    {
        $test = LocalDate::of(Year::MAX_VALUE, 12, 1);
        $test->plusYears(Long::MIN_VALUE);
    }

    /**
     * @expectedException \Celest\DateTimeException
     */
    public function test_plusYears_long_invalidTooSmall_validInt()
    {
        LocalDate::of(Year::MIN_VALUE, 1, 1)->plusYears(-1);
    }

    /**
     * @expectedException \Celest\DateTimeException
     */
    public function test_plusYears_long_invalidTooSmall_invalidInt()
    {
        LocalDate::of(Year::MIN_VALUE, 1, 1)->plusYears(-10);
    }

    //-----------------------------------------------------------------------
    // plusMonths()
    //-----------------------------------------------------------------------

    public function test_plusMonths_long_normal()
    {
        $t = self::TEST_2007_07_15()->plusMonths(1);
        $this->assertEquals($t, LocalDate::of(2007, 8, 15));
    }


    public function test_plusMonths_long_overYears()
    {
        $t = self::TEST_2007_07_15()->plusMonths(25);
        $this->assertEquals($t, LocalDate::of(2009, 8, 15));
    }


    public function test_plusMonths_long_negative()
    {
        $t = self::TEST_2007_07_15()->plusMonths(-1);
        $this->assertEquals($t, LocalDate::of(2007, 6, 15));
    }


    public function test_plusMonths_long_negativeAcrossYear()
    {
        $t = self::TEST_2007_07_15()->plusMonths(-7);
        $this->assertEquals($t, LocalDate::of(2006, 12, 15));
    }


    public function test_plusMonths_long_negativeOverYears()
    {
        $t = self::TEST_2007_07_15()->plusMonths(-31);
        $this->assertEquals($t, LocalDate::of(2004, 12, 15));
    }


    public function test_plusMonths_long_adjustDayFromLeapYear()
    {
        $t = LocalDate::of(2008, 2, 29)->plusMonths(12);
        $expected = LocalDate::of(2009, 2, 28);
        $this->assertEquals($t, $expected);
    }


    public function test_plusMonths_long_adjustDayFromMonthLength()
    {
        $t = LocalDate::of(2007, 3, 31)->plusMonths(1);
        $expected = LocalDate::of(2007, 4, 30);
        $this->assertEquals($t, $expected);
    }


    public function test_plusMonths_long_big()
    {
        $months = 20 + Integer::MAX_VALUE;
        $test = LocalDate::of(-40, 6, 1)->plusMonths($months);
        $this->assertEquals($test, LocalDate::of((-40 + Math::div($months, 12)), 6 + ($months % 12), 1));
    }

    /**
     * @expectedException \Celest\DateTimeException
     */
    public function test_plusMonths_long_invalidTooLarge()
    {
        LocalDate::of(Year::MAX_VALUE, 12, 1)->plusMonths(1);
    }

    /**
     * @expectedException \Celest\DateTimeException
     */
    public function test_plusMonths_long_invalidTooLargeMaxAddMax()
    {
        $test = LocalDate::of(Year::MAX_VALUE, 12, 1);
        $test->plusMonths(Long::MAX_VALUE);
    }

    /**
     * @expectedException \Celest\DateTimeException
     */
    public function test_plusMonths_long_invalidTooLargeMaxAddMin()
    {
        $test = LocalDate::of(Year::MAX_VALUE, 12, 1);
        $test->plusMonths(Long::MIN_VALUE);
    }

    /**
     * @expectedException \Celest\DateTimeException
     */
    public function test_plusMonths_long_invalidTooSmall()
    {
        LocalDate::of(Year::MIN_VALUE, 1, 1)->plusMonths(-1);
    }


    public function test_plusWeeks_normal()
    {
        $t = self::TEST_2007_07_15()->plusWeeks(1);
        $this->assertEquals($t, LocalDate::of(2007, 7, 22));
    }


    public function test_plusWeeks_overMonths()
    {
        $t = self::TEST_2007_07_15()->plusWeeks(9);
        $this->assertEquals($t, LocalDate::of(2007, 9, 16));
    }


    public function test_plusWeeks_overYears()
    {
        $t = LocalDate::of(2006, 7, 16)->plusWeeks(52);
        $this->assertEquals($t, self::TEST_2007_07_15());
    }


    public function test_plusWeeks_overLeapYears()
    {
        $t = self::TEST_2007_07_15()->plusYears(-1)->plusWeeks(104);
        $this->assertEquals($t, LocalDate::of(2008, 7, 12));
    }


    public function test_plusWeeks_negative()
    {
        $t = self::TEST_2007_07_15()->plusWeeks(-1);
        $this->assertEquals($t, LocalDate::of(2007, 7, 8));
    }


    public function test_plusWeeks_negativeAcrossYear()
    {
        $t = self::TEST_2007_07_15()->plusWeeks(-28);
        $this->assertEquals($t, LocalDate::of(2006, 12, 31));
    }


    public function test_plusWeeks_negativeOverYears()
    {
        $t = self::TEST_2007_07_15()->plusWeeks(-104);
        $this->assertEquals($t, LocalDate::of(2005, 7, 17));
    }


    public function test_plusWeeks_maximum()
    {
        $t = LocalDate::of(Year::MAX_VALUE, 12, 24)->plusWeeks(1);
        $expected = LocalDate::of(Year::MAX_VALUE, 12, 31);
        $this->assertEquals($t, $expected);
    }


    public function test_plusWeeks_minimum()
    {
        $t = LocalDate::of(Year::MIN_VALUE, 1, 8)->plusWeeks(-1);
        $expected = LocalDate::of(Year::MIN_VALUE, 1, 1);
        $this->assertEquals($t, $expected);
    }

    /**
     * @expectedException \Celest\DateTimeException
     */
    public function test_plusWeeks_invalidTooLarge()
    {
        LocalDate::of(Year::MAX_VALUE, 12, 25)->plusWeeks(1);
    }

    /**
     * @expectedException \Celest\DateTimeException
     */
    public function test_plusWeeks_invalidTooSmall()
    {
        LocalDate::of(Year::MIN_VALUE, 1, 7)->plusWeeks(-1);
    }

    /**
     * @expectedException \Celest\ArithmeticException
     */
    public function test_plusWeeks_invalidMaxMinusMax()
    {
        LocalDate::of(Year::MAX_VALUE, 12, 25)->plusWeeks(Long::MAX_VALUE);
    }

    /**
     * @expectedException \Celest\ArithmeticException
     */
    public function test_plusWeeks_invalidMaxMinusMin()
    {
        LocalDate::of(Year::MAX_VALUE, 12, 25)->plusWeeks(Long::MIN_VALUE);
    }


    public function test_plusDays_normal()
    {
        $t = self::TEST_2007_07_15()->plusDays(1);
        $this->assertEquals($t, LocalDate::of(2007, 7, 16));
    }


    public function test_plusDays_overMonths()
    {
        $t = self::TEST_2007_07_15()->plusDays(62);
        $this->assertEquals($t, LocalDate::of(2007, 9, 15));
    }


    public function test_plusDays_overYears()
    {
        $t = LocalDate::of(2006, 7, 14)->plusDays(366);
        $this->assertEquals($t, self::TEST_2007_07_15());
    }


    public function test_plusDays_overLeapYears()
    {
        $t = self::TEST_2007_07_15()->plusYears(-1)->plusDays(365 + 366);
        $this->assertEquals($t, LocalDate::of(2008, 7, 15));
    }


    public function test_plusDays_negative()
    {
        $t = self::TEST_2007_07_15()->plusDays(-1);
        $this->assertEquals($t, LocalDate::of(2007, 7, 14));
    }


    public function test_plusDays_negativeAcrossYear()
    {
        $t = self::TEST_2007_07_15()->plusDays(-196);
        $this->assertEquals($t, LocalDate::of(2006, 12, 31));
    }


    public function test_plusDays_negativeOverYears()
    {
        $t = self::TEST_2007_07_15()->plusDays(-730);
        $this->assertEquals($t, LocalDate::of(2005, 7, 15));
    }


    public function test_plusDays_maximum()
    {
        $t = LocalDate::of(Year::MAX_VALUE, 12, 30)->plusDays(1);
        $expected = LocalDate::of(Year::MAX_VALUE, 12, 31);
        $this->assertEquals($t, $expected);
    }


    public function test_plusDays_minimum()
    {
        $t = LocalDate::of(Year::MIN_VALUE, 1, 2)->plusDays(-1);
        $expected = LocalDate::of(Year::MIN_VALUE, 1, 1);
        $this->assertEquals($t, $expected);
    }

    /**
     * @expectedException \Celest\DateTimeException
     */
    public function test_plusDays_invalidTooLarge()
    {
        LocalDate::of(Year::MAX_VALUE, 12, 31)->plusDays(1);
    }

    /**
     * @expectedException \Celest\DateTimeException
     */
    public function test_plusDays_invalidTooSmall()
    {
        LocalDate::of(Year::MIN_VALUE, 1, 1)->plusDays(-1);
    }

    /**
     * @expectedException \Celest\DateTimeException
     */
    public function test_plusDays_overflowTooLarge()
    {
        LocalDate::of(Year::MAX_VALUE, 12, 31)->plusDays(Long::MAX_VALUE);
    }

    /**
     * @expectedException \Celest\DateTimeException
     */
    public function test_plusDays_overflowTooSmall()
    {
        LocalDate::of(Year::MIN_VALUE, 1, 1)->plusDays(Long::MIN_VALUE);
    }

    //-----------------------------------------------------------------------
    // minus(Period)
    //-----------------------------------------------------------------------

    public function test_minus_Period_positiveMonths()
    {
        $period = MockSimplePeriod::of(7, CU::MONTHS());
        $t = self::TEST_2007_07_15()->minusAmount($period);
        $this->assertEquals($t, LocalDate::of(2006, 12, 15));
    }


    public function test_minus_Period_negativeDays()
    {
        $period = MockSimplePeriod::of(-25, CU::DAYS());
        $t = self::TEST_2007_07_15()->minusAmount($period);
        $this->assertEquals($t, LocalDate::of(2007, 8, 9));
    }

    /**
     * @expectedException \Celest\DateTimeException
     */
    public function test_minus_Period_timeNotAllowed()
    {
        $period = MockSimplePeriod::of(7, CU::HOURS());
        self::TEST_2007_07_15()->minusAmount($period);
    }

    public function test_minus_Period_null()
    {
        TestHelper::assertNullException($this, function () {
            self::TEST_2007_07_15()->minusAmount(null);
        });
    }

    /**
     * @expectedException \Celest\DateTimeException
     */
    public function test_minus_Period_invalidTooLarge()
    {
        $period = MockSimplePeriod::of(-1, CU::YEARS());
        LocalDate::of(Year::MAX_VALUE, 1, 1)->minusAmount($period);
    }

    /**
     * @expectedException \Celest\DateTimeException
     */
    public function test_minus_Period_invalidTooSmall()
    {
        $period = MockSimplePeriod::of(1, CU::YEARS());
        LocalDate::of(Year::MIN_VALUE, 1, 1)->minusAmount($period);
    }

    //-----------------------------------------------------------------------
    // minus(long,TemporalUnit)
    //-----------------------------------------------------------------------

    public function test_minus_longTemporalUnit_positiveMonths()
    {
        $t = self::TEST_2007_07_15()->minus(7, CU::MONTHS());
        $this->assertEquals($t, LocalDate::of(2006, 12, 15));
    }


    public function test_minus_longTemporalUnit_negativeDays()
    {
        $t = self::TEST_2007_07_15()->minus(-25, CU::DAYS());
        $this->assertEquals($t, LocalDate::of(2007, 8, 9));
    }

    /**
     * @expectedException \Celest\DateTimeException
     */
    public function test_minus_longTemporalUnit_timeNotAllowed()
    {
        self::TEST_2007_07_15()->minus(7, CU::HOURS());
    }

    public function test_minus_longTemporalUnit_null()
    {
        TestHelper::assertNullException($this, function () {
            self::TEST_2007_07_15()->minus(1, null);
        });
    }

    /**
     * @expectedException \Celest\DateTimeException
     */
    public function test_minus_longTemporalUnit_invalidTooLarge()
    {
        LocalDate::of(Year::MAX_VALUE, 1, 1)->minus(-1, CU::YEARS());
    }

    /**
     * @expectedException \Celest\DateTimeException
     */
    public function test_minus_longTemporalUnit_invalidTooSmall()
    {
        LocalDate::of(Year::MIN_VALUE, 1, 1)->minus(1, CU::YEARS());
    }

    //-----------------------------------------------------------------------
    // minusYears()
    //-----------------------------------------------------------------------

    public function test_minusYears_long_normal()
    {
        $t = self::TEST_2007_07_15()->minusYears(1);
        $this->assertEquals($t, LocalDate::of(2006, 7, 15));
    }


    public function test_minusYears_long_negative()
    {
        $t = self::TEST_2007_07_15()->minusYears(-1);
        $this->assertEquals($t, LocalDate::of(2008, 7, 15));
    }


    public function test_minusYears_long_adjustDay()
    {
        $t = LocalDate::of(2008, 2, 29)->minusYears(1);
        $expected = LocalDate::of(2007, 2, 28);
        $this->assertEquals($t, $expected);
    }


    public function test_minusYears_long_big()
    {
        $years = 20 + Year::MAX_VALUE;
        $test = LocalDate::of(40, 6, 1)->minusYears($years);
        $this->assertEquals($test, LocalDate::of((int)(40 - $years), 6, 1));
    }

    /**
     * @expectedException \Celest\DateTimeException
     */
    public function test_minusYears_long_invalidTooLarge()
    {
        $test = LocalDate::of(Year::MAX_VALUE, 6, 1);
        $test->minusYears(-1);
    }

    /**
     * @expectedException \Celest\DateTimeException
     */
    public function test_minusYears_long_invalidTooLargeMaxAddMax()
    {
        $test = LocalDate::of(Year::MAX_VALUE, 12, 1);
        $test->minusYears(Long::MAX_VALUE);
    }

    /**
     * @expectedException \Celest\DateTimeException
     */
    public function test_minusYears_long_invalidTooLargeMaxAddMin()
    {
        $test = LocalDate::of(Year::MAX_VALUE, 12, 1);
        $test->minusYears(Long::MIN_VALUE);
    }

    /**
     * @expectedException \Celest\DateTimeException
     */
    public function test_minusYears_long_invalidTooSmall()
    {
        LocalDate::of(Year::MIN_VALUE, 1, 1)->minusYears(1);
    }

    //-----------------------------------------------------------------------
    // minusMonths()
    //-----------------------------------------------------------------------

    public function test_minusMonths_long_normal()
    {
        $t = self::TEST_2007_07_15()->minusMonths(1);
        $this->assertEquals($t, LocalDate::of(2007, 6, 15));
    }


    public function test_minusMonths_long_overYears()
    {
        $t = self::TEST_2007_07_15()->minusMonths(25);
        $this->assertEquals($t, LocalDate::of(2005, 6, 15));
    }


    public function test_minusMonths_long_negative()
    {
        $t = self::TEST_2007_07_15()->minusMonths(-1);
        $this->assertEquals($t, LocalDate::of(2007, 8, 15));
    }


    public function test_minusMonths_long_negativeAcrossYear()
    {
        $t = self::TEST_2007_07_15()->minusMonths(-7);
        $this->assertEquals($t, LocalDate::of(2008, 2, 15));
    }


    public function test_minusMonths_long_negativeOverYears()
    {
        $t = self::TEST_2007_07_15()->minusMonths(-31);
        $this->assertEquals($t, LocalDate::of(2010, 2, 15));
    }


    public function test_minusMonths_long_adjustDayFromLeapYear()
    {
        $t = LocalDate::of(2008, 2, 29)->minusMonths(12);
        $expected = LocalDate::of(2007, 2, 28);
        $this->assertEquals($t, $expected);
    }


    public function test_minusMonths_long_adjustDayFromMonthLength()
    {
        $t = LocalDate::of(2007, 3, 31)->minusMonths(1);
        $expected = LocalDate::of(2007, 2, 28);
        $this->assertEquals($t, $expected);
    }


    public function test_minusMonths_long_big()
    {
        $months = 20 + Integer::MAX_VALUE;
        $test = LocalDate::of(40, 6, 1)->minusMonths($months);
        $this->assertEquals($test, LocalDate::of((int)(40 - $months / 12), 6 - (int)($months % 12), 1));
    }

    /**
     * @expectedException \Celest\DateTimeException
     */
    public function test_minusMonths_long_invalidTooLarge()
    {
        LocalDate::of(Year::MAX_VALUE, 12, 1)->minusMonths(-1);
    }

    /**
     * @expectedException \Celest\DateTimeException
     */
    public function test_minusMonths_long_invalidTooLargeMaxAddMax()
    {
        $test = LocalDate::of(Year::MAX_VALUE, 12, 1);
        $test->minusMonths(Long::MAX_VALUE);
    }

    /**
     * @expectedException \Celest\DateTimeException
     */
    public function test_minusMonths_long_invalidTooLargeMaxAddMin()
    {
        $test = LocalDate::of(Year::MAX_VALUE, 12, 1);
        $test->minusMonths(Long::MIN_VALUE);
    }

    /**
     * @expectedException \Celest\DateTimeException
     */
    public function test_minusMonths_long_invalidTooSmall()
    {
        LocalDate::of(Year::MIN_VALUE, 1, 1)->minusMonths(1);
    }


    public function test_minusWeeks_normal()
    {
        $t = self::TEST_2007_07_15()->minusWeeks(1);
        $this->assertEquals($t, LocalDate::of(2007, 7, 8));
    }


    public function test_minusWeeks_overMonths()
    {
        $t = self::TEST_2007_07_15()->minusWeeks(9);
        $this->assertEquals($t, LocalDate::of(2007, 5, 13));
    }


    public function test_minusWeeks_overYears()
    {
        $t = LocalDate::of(2008, 7, 13)->minusWeeks(52);
        $this->assertEquals($t, self::TEST_2007_07_15());
    }


    public function test_minusWeeks_overLeapYears()
    {
        $t = self::TEST_2007_07_15()->minusYears(-1)->minusWeeks(104);
        $this->assertEquals($t, LocalDate::of(2006, 7, 18));
    }


    public function test_minusWeeks_negative()
    {
        $t = self::TEST_2007_07_15()->minusWeeks(-1);
        $this->assertEquals($t, LocalDate::of(2007, 7, 22));
    }


    public function test_minusWeeks_negativeAcrossYear()
    {
        $t = self::TEST_2007_07_15()->minusWeeks(-28);
        $this->assertEquals($t, LocalDate::of(2008, 1, 27));
    }


    public function test_minusWeeks_negativeOverYears()
    {
        $t = self::TEST_2007_07_15()->minusWeeks(-104);
        $this->assertEquals($t, LocalDate::of(2009, 7, 12));
    }


    public function test_minusWeeks_maximum()
    {
        $t = LocalDate::of(Year::MAX_VALUE, 12, 24)->minusWeeks(-1);
        $expected = LocalDate::of(Year::MAX_VALUE, 12, 31);
        $this->assertEquals($t, $expected);
    }


    public function test_minusWeeks_minimum()
    {
        $t = LocalDate::of(Year::MIN_VALUE, 1, 8)->minusWeeks(1);
        $expected = LocalDate::of(Year::MIN_VALUE, 1, 1);
        $this->assertEquals($t, $expected);
    }

    /**
     * @expectedException \Celest\DateTimeException
     */
    public function test_minusWeeks_invalidTooLarge()
    {
        LocalDate::of(Year::MAX_VALUE, 12, 25)->minusWeeks(-1);
    }

    /**
     * @expectedException \Celest\DateTimeException
     */
    public function test_minusWeeks_invalidTooSmall()
    {
        LocalDate::of(Year::MIN_VALUE, 1, 7)->minusWeeks(1);
    }

    /**
     * @expectedException \Celest\ArithmeticException
     */
    public function test_minusWeeks_invalidMaxMinusMax()
    {
        LocalDate::of(Year::MAX_VALUE, 12, 25)->minusWeeks(Long::MAX_VALUE);
    }

    /**
     * @expectedException \Celest\ArithmeticException
     */
    public function test_minusWeeks_invalidMaxMinusMin()
    {
        LocalDate::of(Year::MAX_VALUE, 12, 25)->minusWeeks(Long::MIN_VALUE);
    }


    public function test_minusDays_normal()
    {
        $t = self::TEST_2007_07_15()->minusDays(1);
        $this->assertEquals($t, LocalDate::of(2007, 7, 14));
    }


    public function test_minusDays_overMonths()
    {
        $t = self::TEST_2007_07_15()->minusDays(62);
        $this->assertEquals($t, LocalDate::of(2007, 5, 14));
    }


    public function test_minusDays_overYears()
    {
        $t = LocalDate::of(2008, 7, 16)->minusDays(367);
        $this->assertEquals($t, self::TEST_2007_07_15());
    }


    public function test_minusDays_overLeapYears()
    {
        $t = self::TEST_2007_07_15()->plusYears(2)->minusDays(365 + 366);
        $this->assertEquals($t, self::TEST_2007_07_15());
    }


    public function test_minusDays_negative()
    {
        $t = self::TEST_2007_07_15()->minusDays(-1);
        $this->assertEquals($t, LocalDate::of(2007, 7, 16));
    }


    public function test_minusDays_negativeAcrossYear()
    {
        $t = self::TEST_2007_07_15()->minusDays(-169);
        $this->assertEquals($t, LocalDate::of(2007, 12, 31));
    }


    public function test_minusDays_negativeOverYears()
    {
        $t = self::TEST_2007_07_15()->minusDays(-731);
        $this->assertEquals($t, LocalDate::of(2009, 7, 15));
    }


    public function test_minusDays_maximum()
    {
        $t = LocalDate::of(Year::MAX_VALUE, 12, 30)->minusDays(-1);
        $expected = LocalDate::of(Year::MAX_VALUE, 12, 31);
        $this->assertEquals($t, $expected);
    }


    public function test_minusDays_minimum()
    {
        $t = LocalDate::of(Year::MIN_VALUE, 1, 2)->minusDays(1);
        $expected = LocalDate::of(Year::MIN_VALUE, 1, 1);
        $this->assertEquals($t, $expected);
    }

    /**
     * @expectedException \Celest\DateTimeException
     */
    public function test_minusDays_invalidTooLarge()
    {
        LocalDate::of(Year::MAX_VALUE, 12, 31)->minusDays(-1);
    }

    /**
     * @expectedException \Celest\DateTimeException
     */
    public function test_minusDays_invalidTooSmall()
    {
        LocalDate::of(Year::MIN_VALUE, 1, 1)->minusDays(1);
    }

    /**
     * @expectedException \Celest\DateTimeException
     */
    public function test_minusDays_overflowTooLarge()
    {
        LocalDate::of(Year::MAX_VALUE, 12, 31)->minusDays(Long::MIN_VALUE);
    }

    /**
     * @expectedException \Celest\DateTimeException
     */
    public function test_minusDays_overflowTooSmall()
    {
        LocalDate::of(Year::MIN_VALUE, 1, 1)->minusDays(Long::MAX_VALUE);
    }

    //-----------------------------------------------------------------------
    // until(Temporal, TemporalUnit)
    //-----------------------------------------------------------------------
    function data_periodUntilUnit()
    {
        return [
            [$this->date(2000, 1, 1), $this->date(2000, 1, 1), CU::DAYS(), 0],
            [$this->date(2000, 1, 1), $this->date(2000, 1, 1), CU::WEEKS(), 0],
            [$this->date(2000, 1, 1), $this->date(2000, 1, 1), CU::MONTHS(), 0],
            [$this->date(2000, 1, 1), $this->date(2000, 1, 1), CU::YEARS(), 0],
            [$this->date(2000, 1, 1), $this->date(2000, 1, 1), CU::DECADES(), 0],
            [$this->date(2000, 1, 1), $this->date(2000, 1, 1), CU::CENTURIES(), 0],
            [$this->date(2000, 1, 1), $this->date(2000, 1, 1), CU::MILLENNIA(), 0],

            [$this->date(2000, 1, 15), $this->date(2000, 2, 14), CU::DAYS(), 30],
            [$this->date(2000, 1, 15), $this->date(2000, 2, 15), CU::DAYS(), 31],
            [$this->date(2000, 1, 15), $this->date(2000, 2, 16), CU::DAYS(), 32],

            [$this->date(2000, 1, 15), $this->date(2000, 2, 17), CU::WEEKS(), 4],
            [$this->date(2000, 1, 15), $this->date(2000, 2, 18), CU::WEEKS(), 4],
            [$this->date(2000, 1, 15), $this->date(2000, 2, 19), CU::WEEKS(), 5],
            [$this->date(2000, 1, 15), $this->date(2000, 2, 20), CU::WEEKS(), 5],

            [$this->date(2000, 1, 15), $this->date(2000, 2, 14), CU::MONTHS(), 0],
            [$this->date(2000, 1, 15), $this->date(2000, 2, 15), CU::MONTHS(), 1],
            [$this->date(2000, 1, 15), $this->date(2000, 2, 16), CU::MONTHS(), 1],
            [$this->date(2000, 1, 15), $this->date(2000, 3, 14), CU::MONTHS(), 1],
            [$this->date(2000, 1, 15), $this->date(2000, 3, 15), CU::MONTHS(), 2],
            [$this->date(2000, 1, 15), $this->date(2000, 3, 16), CU::MONTHS(), 2],

            [$this->date(2000, 1, 15), $this->date(2001, 1, 14), CU::YEARS(), 0],
            [$this->date(2000, 1, 15), $this->date(2001, 1, 15), CU::YEARS(), 1],
            [$this->date(2000, 1, 15), $this->date(2001, 1, 16), CU::YEARS(), 1],
            [$this->date(2000, 1, 15), $this->date(2004, 1, 14), CU::YEARS(), 3],
            [$this->date(2000, 1, 15), $this->date(2004, 1, 15), CU::YEARS(), 4],
            [$this->date(2000, 1, 15), $this->date(2004, 1, 16), CU::YEARS(), 4],

            [$this->date(2000, 1, 15), $this->date(2010, 1, 14), CU::DECADES(), 0],
            [$this->date(2000, 1, 15), $this->date(2010, 1, 15), CU::DECADES(), 1],

            [$this->date(2000, 1, 15), $this->date(2100, 1, 14), CU::CENTURIES(), 0],
            [$this->date(2000, 1, 15), $this->date(2100, 1, 15), CU::CENTURIES(), 1],

            [$this->date(2000, 1, 15), $this->date(3000, 1, 14), CU::MILLENNIA(), 0],
            [$this->date(2000, 1, 15), $this->date(3000, 1, 15), CU::MILLENNIA(), 1],
        ];
    }

    /**
     * @dataProvider data_periodUntilUnit
     */
    public function test_until_TemporalUnit(LocalDate $date1, LocalDate $date2, TemporalUnit $unit, $expected)
    {
        $amount = $date1->until($date2, $unit);
        $this->assertEquals($amount, $expected);
    }

    /**
     * @dataProvider data_periodUntilUnit
     */
    public function test_until_TemporalUnit_negated(LocalDate $date1, LocalDate $date2, TemporalUnit $unit, $expected)
    {
        $amount = $date2->until($date1, $unit);
        $this->assertEquals($amount, -$expected);
    }

    /**
     * @dataProvider data_periodUntilUnit
     */
    public function test_until_TemporalUnit_between(LocalDate $date1, LocalDate $date2, TemporalUnit $unit, $expected)
    {
        $amount = $unit->between($date1, $date2);
        $this->assertEquals($amount, $expected);
    }


    public function test_until_convertedType()
    {
        $start = LocalDate::of(2010, 6, 30);
        $end = $start->plusDays(2)->atStartOfDay()->atOffset(self::OFFSET_PONE());
        $this->assertEquals($start->until($end, CU::DAYS()), 2);
    }

    /**
     * @expectedException \Celest\DateTimeException
     */
    public function test_until_invalidType()
    {
        $start = LocalDate::of(2010, 6, 30);
        $start->until(LocalTime::of(11, 30), CU::DAYS());
    }

    /**
     * @expectedException \Celest\Temporal\UnsupportedTemporalTypeException
     */
    public function test_until_TemporalUnit_unsupportedUnit()
    {
        self::TEST_2007_07_15()->until(self::TEST_2007_07_15(), CU::HOURS());
    }

    public function test_until_TemporalUnit_nullEnd()
    {
        TestHelper::assertNullException($this, function () {
            self::TEST_2007_07_15()->until(null, CU::DAYS());
        });
    }

    public function test_until_TemporalUnit_nullUnit()
    {
        TestHelper::assertNullException($this, function () {
            self::TEST_2007_07_15()->until(self::TEST_2007_07_15(), null);
        });
    }

    //-----------------------------------------------------------------------
    // until(ChronoLocalDate)
    //-----------------------------------------------------------------------
    function data_periodUntil()
    {
        return [
            [2010, 1, 1, 2010, 1, 1, 0, 0, 0],
            [2010, 1, 1, 2010, 1, 2, 0, 0, 1],
            [2010, 1, 1, 2010, 1, 31, 0, 0, 30],
            [2010, 1, 1, 2010, 2, 1, 0, 1, 0],
            [2010, 1, 1, 2010, 2, 28, 0, 1, 27],
            [2010, 1, 1, 2010, 3, 1, 0, 2, 0],
            [2010, 1, 1, 2010, 12, 31, 0, 11, 30],
            [2010, 1, 1, 2011, 1, 1, 1, 0, 0],
            [2010, 1, 1, 2011, 12, 31, 1, 11, 30],
            [2010, 1, 1, 2012, 1, 1, 2, 0, 0],

            [2010, 1, 10, 2010, 1, 1, 0, 0, -9],
            [2010, 1, 10, 2010, 1, 2, 0, 0, -8],
            [2010, 1, 10, 2010, 1, 9, 0, 0, -1],
            [2010, 1, 10, 2010, 1, 10, 0, 0, 0],
            [2010, 1, 10, 2010, 1, 11, 0, 0, 1],
            [2010, 1, 10, 2010, 1, 31, 0, 0, 21],
            [2010, 1, 10, 2010, 2, 1, 0, 0, 22],
            [2010, 1, 10, 2010, 2, 9, 0, 0, 30],
            [2010, 1, 10, 2010, 2, 10, 0, 1, 0],
            [2010, 1, 10, 2010, 2, 28, 0, 1, 18],
            [2010, 1, 10, 2010, 3, 1, 0, 1, 19],
            [2010, 1, 10, 2010, 3, 9, 0, 1, 27],
            [2010, 1, 10, 2010, 3, 10, 0, 2, 0],
            [2010, 1, 10, 2010, 12, 31, 0, 11, 21],
            [2010, 1, 10, 2011, 1, 1, 0, 11, 22],
            [2010, 1, 10, 2011, 1, 9, 0, 11, 30],
            [2010, 1, 10, 2011, 1, 10, 1, 0, 0],

            [2010, 3, 30, 2011, 5, 1, 1, 1, 1],
            [2010, 4, 30, 2011, 5, 1, 1, 0, 1],

            [2010, 2, 28, 2012, 2, 27, 1, 11, 30],
            [2010, 2, 28, 2012, 2, 28, 2, 0, 0],
            [2010, 2, 28, 2012, 2, 29, 2, 0, 1],

            [2012, 2, 28, 2014, 2, 27, 1, 11, 30],
            [2012, 2, 28, 2014, 2, 28, 2, 0, 0],
            [2012, 2, 28, 2014, 3, 1, 2, 0, 1],

            [2012, 2, 29, 2014, 2, 28, 1, 11, 30],
            [2012, 2, 29, 2014, 3, 1, 2, 0, 1],
            [2012, 2, 29, 2014, 3, 2, 2, 0, 2],

            [2012, 2, 29, 2016, 2, 28, 3, 11, 30],
            [2012, 2, 29, 2016, 2, 29, 4, 0, 0],
            [2012, 2, 29, 2016, 3, 1, 4, 0, 1],

            [2010, 1, 1, 2009, 12, 31, 0, 0, -1],
            [2010, 1, 1, 2009, 12, 30, 0, 0, -2],
            [2010, 1, 1, 2009, 12, 2, 0, 0, -30],
            [2010, 1, 1, 2009, 12, 1, 0, -1, 0],
            [2010, 1, 1, 2009, 11, 30, 0, -1, -1],
            [2010, 1, 1, 2009, 11, 2, 0, -1, -29],
            [2010, 1, 1, 2009, 11, 1, 0, -2, 0],
            [2010, 1, 1, 2009, 1, 2, 0, -11, -30],
            [2010, 1, 1, 2009, 1, 1, -1, 0, 0],

            [2010, 1, 15, 2010, 1, 15, 0, 0, 0],
            [2010, 1, 15, 2010, 1, 14, 0, 0, -1],
            [2010, 1, 15, 2010, 1, 1, 0, 0, -14],
            [2010, 1, 15, 2009, 12, 31, 0, 0, -15],
            [2010, 1, 15, 2009, 12, 16, 0, 0, -30],
            [2010, 1, 15, 2009, 12, 15, 0, -1, 0],
            [2010, 1, 15, 2009, 12, 14, 0, -1, -1],

            [2010, 2, 28, 2009, 3, 1, 0, -11, -27],
            [2010, 2, 28, 2009, 2, 28, -1, 0, 0],
            [2010, 2, 28, 2009, 2, 27, -1, 0, -1],

            [2010, 2, 28, 2008, 2, 29, -1, -11, -28],
            [2010, 2, 28, 2008, 2, 28, -2, 0, 0],
            [2010, 2, 28, 2008, 2, 27, -2, 0, -1],

            [2012, 2, 29, 2009, 3, 1, -2, -11, -28],
            [2012, 2, 29, 2009, 2, 28, -3, 0, -1],
            [2012, 2, 29, 2009, 2, 27, -3, 0, -2],

            [2012, 2, 29, 2008, 3, 1, -3, -11, -28],
            [2012, 2, 29, 2008, 2, 29, -4, 0, 0],
            [2012, 2, 29, 2008, 2, 28, -4, 0, -1],
        ];
    }

    /**
     * @dataProvider data_periodUntil
     */
    public function test_periodUntil_LocalDate($y1, $m1, $d1, $y2, $m2, $d2, $ye, $me, $de)
    {
        $start = LocalDate::of($y1, $m1, $d1);
        $end = LocalDate::of($y2, $m2, $d2);
        $test = $start->untilDate($end);
        $this->assertEquals($test->getYears(), $ye);
        $this->assertEquals($test->getMonths(), $me);
        $this->assertEquals($test->getDays(), $de);
    }


    public function test_periodUntil_LocalDate_max()
    {
        $years = Math::toIntExact(Year::MAX_VALUE - Year::MIN_VALUE);
        $this->assertEquals(LocalDate::MIN()->untilDate(LocalDate::MAX()), Period::of($years, 11, 30));
    }

    public function test_periodUntil_LocalDate_null()
    {
        TestHelper::assertNullException($this, function () {
            self::TEST_2007_07_15()->untilDate(null);
        });
    }

    //-----------------------------------------------------------------------
    // format(DateTimeFormatter)
    //-----------------------------------------------------------------------

    public function test_format_formatter()
    {
        $f = DateTimeFormatter::ofPattern("y M d");
        $t = LocalDate::of(2010, 12, 3)->format($f);
        $this->assertEquals($t, "2010 12 3");
    }

    public function test_format_formatter_null()
    {
        TestHelper::assertNullException($this, function () {
            LocalDate::of(2010, 12, 3)->format(null);
        });
    }

    //-----------------------------------------------------------------------
    // atTime()
    //-----------------------------------------------------------------------

    public function test_atTime_LocalTime()
    {
        $t = LocalDate::of(2008, 6, 30);
        $this->assertEquals($t->atTime(LocalTime::of(11, 30)), LocalDateTime::of(2008, 6, 30, 11, 30));
    }

    public function test_atTime_LocalTime_null()
    {
        TestHelper::assertNullException($this, function () {
            $t = LocalDate::of(2008, 6, 30);
            $t->atTime(null);
        });

    }

    //-------------------------------------------------------------------------

    public function test_atTime_int_int()
    {
        $t = LocalDate::of(2008, 6, 30);
        $this->assertEquals($t->atTimeNumerical(11, 30), LocalDateTime::of(2008, 6, 30, 11, 30));
    }

    /**
     * @expectedException \Celest\DateTimeException
     */
    public function test_atTime_int_int_hourTooSmall()
    {
        $t = LocalDate::of(2008, 6, 30);
        $t->atTimeNumerical(-1, 30);
    }

    /**
     * @expectedException \Celest\DateTimeException
     */
    public function test_atTime_int_int_hourTooBig()
    {
        $t = LocalDate::of(2008, 6, 30);
        $t->atTimeNumerical(24, 30);
    }

    /**
     * @expectedException \Celest\DateTimeException
     */
    public function test_atTime_int_int_minuteTooSmall()
    {
        $t = LocalDate::of(2008, 6, 30);
        $t->atTimeNumerical(11, -1);
    }

    /**
     * @expectedException \Celest\DateTimeException
     */
    public function test_atTime_int_int_minuteTooBig()
    {
        $t = LocalDate::of(2008, 6, 30);
        $t->atTimeNumerical(11, 60);
    }


    public function test_atTime_int_int_int()
    {
        $t = LocalDate::of(2008, 6, 30);
        $this->assertEquals($t->atTimeNumerical(11, 30, 40), LocalDateTime::of(2008, 6, 30, 11, 30, 40));
    }

    /**
     * @expectedException \Celest\DateTimeException
     */
    public function test_atTime_int_int_int_hourTooSmall()
    {
        $t = LocalDate::of(2008, 6, 30);
        $t->atTimeNumerical(-1, 30, 40);
    }

    /**
     * @expectedException \Celest\DateTimeException
     */
    public function test_atTime_int_int_int_hourTooBig()
    {
        $t = LocalDate::of(2008, 6, 30);
        $t->atTimeNumerical(24, 30, 40);
    }

    /**
     * @expectedException \Celest\DateTimeException
     */
    public function test_atTime_int_int_int_minuteTooSmall()
    {
        $t = LocalDate::of(2008, 6, 30);
        $t->atTimeNumerical(11, -1, 40);
    }

    /**
     * @expectedException \Celest\DateTimeException
     */
    public function test_atTime_int_int_int_minuteTooBig()
    {
        $t = LocalDate::of(2008, 6, 30);
        $t->atTimeNumerical(11, 60, 40);
    }

    /**
     * @expectedException \Celest\DateTimeException
     */
    public function test_atTime_int_int_int_secondTooSmall()
    {
        $t = LocalDate::of(2008, 6, 30);
        $t->atTimeNumerical(11, 30, -1);
    }

    /**
     * @expectedException \Celest\DateTimeException
     */
    public function test_atTime_int_int_int_secondTooBig()
    {
        $t = LocalDate::of(2008, 6, 30);
        $t->atTimeNumerical(11, 30, 60);
    }


    public function test_atTime_int_int_int_int()
    {
        $t = LocalDate::of(2008, 6, 30);
        $this->assertEquals($t->atTimeNumerical(11, 30, 40, 50), LocalDateTime::of(2008, 6, 30, 11, 30, 40, 50));
    }

    /**
     * @expectedException \Celest\DateTimeException
     */
    public function test_atTime_int_int_int_int_hourTooSmall()
    {
        $t = LocalDate::of(2008, 6, 30);
        $t->atTimeNumerical(-1, 30, 40, 50);
    }

    /**
     * @expectedException \Celest\DateTimeException
     */
    public function test_atTime_int_int_int_int_hourTooBig()
    {
        $t = LocalDate::of(2008, 6, 30);
        $t->atTimeNumerical(24, 30, 40, 50);
    }

    /**
     * @expectedException \Celest\DateTimeException
     */
    public function test_atTime_int_int_int_int_minuteTooSmall()
    {
        $t = LocalDate::of(2008, 6, 30);
        $t->atTimeNumerical(11, -1, 40, 50);
    }

    /**
     * @expectedException \Celest\DateTimeException
     */
    public function test_atTime_int_int_int_int_minuteTooBig()
    {
        $t = LocalDate::of(2008, 6, 30);
        $t->atTimeNumerical(11, 60, 40, 50);
    }

    /**
     * @expectedException \Celest\DateTimeException
     */
    public function test_atTime_int_int_int_int_secondTooSmall()
    {
        $t = LocalDate::of(2008, 6, 30);
        $t->atTimeNumerical(11, 30, -1, 50);
    }

    /**
     * @expectedException \Celest\DateTimeException
     */
    public function test_atTime_int_int_int_int_secondTooBig()
    {
        $t = LocalDate::of(2008, 6, 30);
        $t->atTimeNumerical(11, 30, 60, 50);
    }

    /**
     * @expectedException \Celest\DateTimeException
     */
    public function test_atTime_int_int_int_int_nanoTooSmall()
    {
        $t = LocalDate::of(2008, 6, 30);
        $t->atTimeNumerical(11, 30, 40, -1);
    }

    /**
     * @expectedException \Celest\DateTimeException
     */
    public function test_atTime_int_int_int_int_nanoTooBig()
    {
        $t = LocalDate::of(2008, 6, 30);
        $t->atTimeNumerical(11, 30, 40, 1000000000);
    }

    //-----------------------------------------------------------------------

    public function test_atTime_OffsetTime()
    {
        $t = LocalDate::of(2008, 6, 30);
        $this->assertEquals($t->atOffsetTime(OffsetTime::of(11, 30, 0, 0, self::OFFSET_PONE())), OffsetDateTime::of(2008, 6, 30, 11, 30, 0, 0, self::OFFSET_PONE()));
    }

    public function test_atTime_OffsetTime_null()
    {
        TestHelper::assertNullException($this, function () {
            $t = LocalDate::of(2008, 6, 30);
            $t->atTime(null);
        });

    }

    //-----------------------------------------------------------------------
    // atStartOfDay()
    //-----------------------------------------------------------------------
    function data_atStartOfDay()
    {
        return [
            [LocalDate::of(2008, 6, 30), LocalDateTime::of(2008, 6, 30, 0, 0)],
            [LocalDate::of(-12, 6, 30), LocalDateTime::of(-12, 6, 30, 0, 0)],
        ];
    }

    /**
     * @dataProvider data_atStartOfDay
     */
    public function test_atStartOfDay(LocalDate $test, LocalDateTime $expected)
    {
        $this->assertEquals($test->atStartOfDay(), $expected);
    }

    //-----------------------------------------------------------------------
    // atStartOfDay(ZoneId)
    //-----------------------------------------------------------------------
    function data_atStartOfDayZoneId()
    {
        return [
            [LocalDate::of(2008, 6, 30), self::ZONE_PARIS(), ZonedDateTime::ofDateTime(LocalDateTime::of(2008, 6, 30, 0, 0), self::ZONE_PARIS())],
            [LocalDate::of(2008, 6, 30), self::OFFSET_PONE(), ZonedDateTime::ofDateTime(LocalDateTime::of(2008, 6, 30, 0, 0), self::OFFSET_PONE())],
            [LocalDate::of(2007, 4, 1), self::ZONE_GAZA(), ZonedDateTime::ofDateTime(LocalDateTime::of(2007, 4, 1, 1, 0), self::ZONE_GAZA())],
        ];
    }

    /**
     * @dataProvider data_atStartOfDayZoneId
     */
    public function test_atStartOfDay_ZoneId(LocalDate $test, ZoneId $zone, ZonedDateTime $expected)
    {
        $this->assertEquals($test->atStartOfDayWithZone($zone), $expected);
    }

    public function test_atStartOfDay_ZoneId_null()
    {
        TestHelper::assertNullException($this, function () {
            $t = LocalDate::of(2008, 6, 30);
            $t->atStartOfDayWithZone(null);
        });

    }

    //-----------------------------------------------------------------------
    // toEpochDay()
    //-----------------------------------------------------------------------

    /**
     * @group long
     */
    public function test_toEpochDay()
    {
        $date_0000_01_01 = -678941 - 40587;

        $test = LocalDate::of(0, 1, 1);
        for ($i = $date_0000_01_01; $i < 700000; $i++) {
            $this->assertEquals($test->toEpochDay(), $i);
            $test = $this->next($test);
        }
        $test = LocalDate::of(0, 1, 1);
        for ($i = $date_0000_01_01; $i > -2000000; $i--) {
            $this->assertEquals($test->toEpochDay(), $i);
            $test = $this->previous($test);
        }

        $this->assertEquals(LocalDate::of(1858, 11, 17)->toEpochDay(), -40587);
        $this->assertEquals(LocalDate::of(1, 1, 1)->toEpochDay(), -678575 - 40587);
        $this->assertEquals(LocalDate::of(1995, 9, 27)->toEpochDay(), 49987 - 40587);
        $this->assertEquals(LocalDate::of(1970, 1, 1)->toEpochDay(), 0);
        $this->assertEquals(LocalDate::of(-1, 12, 31)->toEpochDay(), -678942 - 40587);
    }

    //-----------------------------------------------------------------------
    // compareTo()
    //-----------------------------------------------------------------------

    public function test_comparisons()
    {
        $this->doTest_comparisons_LocalDate([
                LocalDate::of(Year::MIN_VALUE, 1, 1),
                LocalDate::of(Year::MIN_VALUE, 12, 31),
                LocalDate::of(-1, 1, 1),
                LocalDate::of(-1, 12, 31),
                LocalDate::of(0, 1, 1),
                LocalDate::of(0, 12, 31),
                LocalDate::of(1, 1, 1),
                LocalDate::of(1, 12, 31),
                LocalDate::of(2006, 1, 1),
                LocalDate::of(2006, 12, 31),
                LocalDate::of(2007, 1, 1),
                LocalDate::of(2007, 12, 31),
                LocalDate::of(2008, 1, 1),
                LocalDate::of(2008, 2, 29),
                LocalDate::of(2008, 12, 31),
                LocalDate::of(Year::MAX_VALUE, 1, 1),
                LocalDate::of(Year::MAX_VALUE, 12, 31)]
        );
    }

    /**
     * @param $localDates LocalDate[]
     */
    function doTest_comparisons_LocalDate($localDates)
    {
        for ($i = 0; $i < count($localDates); $i++) {
            $a = $localDates[$i];
            for ($j = 0; $j < count($localDates); $j++) {
                $b = $localDates[$j];
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

    public function test_compareTo_ObjectNull()
    {
        TestHelper::assertNullException($this, function () {
            self::TEST_2007_07_15()->compareTo(null);
        });
    }


    public function test_isBefore()
    {
        $this->assertTrue(self::TEST_2007_07_15()->isBefore(LocalDate::of(2007, 07, 16)));
        $this->assertFalse(self::TEST_2007_07_15()->isBefore(LocalDate::of(2007, 07, 14)));
        $this->assertFalse(self::TEST_2007_07_15()->isBefore(self::TEST_2007_07_15()));
    }

    public function test_isBefore_ObjectNull()
    {
        TestHelper::assertNullException($this, function () {
            self::TEST_2007_07_15()->isBefore(null);
        });
    }

    public function test_isAfter_ObjectNull()
    {
        TestHelper::assertNullException($this, function () {
            self::TEST_2007_07_15()->isAfter(null);
        });
    }


    public function test_isAfter()
    {
        $this->assertTrue(self::TEST_2007_07_15()->isAfter(LocalDate::of(2007, 07, 14)));
        $this->assertFalse(self::TEST_2007_07_15()->isAfter(LocalDate::of(2007, 07, 16)));
        $this->assertFalse(self::TEST_2007_07_15()->isAfter(self::TEST_2007_07_15()));
    }


    public function test_compareToNonLocalDate()
    {
        TestHelper::assertTypeError($this, function () {
            $c = self::TEST_2007_07_15();
            $c->compareTo(new \stdClass());
        });
    }

    //-----------------------------------------------------------------------
    // equals()
    //-----------------------------------------------------------------------
    /**
     * @dataProvider provider_sampleDates
     */
    public function test_equals_true($y, $m, $d)
    {
        $a = LocalDate::of($y, $m, $d);
        $b = LocalDate::of($y, $m, $d);
        $this->assertEquals($a->equals($b), true);
    }

    /**
     * @dataProvider provider_sampleDates
     */
    public function test_equals_false_year_differs($y, $m, $d)
    {
        $a = LocalDate::of($y, $m, $d);
        $b = LocalDate::of($y + 1, $m, $d);
        $this->assertEquals($a->equals($b), false);
    }

    /**
     * @dataProvider provider_sampleDates
     */
    public function test_equals_false_month_differs($y, $m, $d)
    {
        $a = LocalDate::of($y, $m, $d);
        $b = LocalDate::of($y, $m + 1, $d);
        $this->assertEquals($a->equals($b), false);
    }

    /**
     * @dataProvider provider_sampleDates
     */
    public function test_equals_false_day_differs($y, $m, $d)
    {
        $a = LocalDate::of($y, $m, $d);
        $b = LocalDate::of($y, $m, $d + 1);
        $this->assertEquals($a->equals($b), false);
    }


    public function test_equals_itself_true()
    {
        $this->assertEquals(self::TEST_2007_07_15()->equals(self::TEST_2007_07_15()), true);
    }


    public function test_equals_string_false()
    {
        $this->assertEquals(self::TEST_2007_07_15()->equals("2007-07-15"), false);
    }


    public function test_equals_null_false()
    {
        $this->assertEquals(self::TEST_2007_07_15()->equals(null), false);
    }

    //-----------------------------------------------------------------------
    // toString()
    //-----------------------------------------------------------------------
    function provider_sampleToString()
    {
        return [
            [2008, 7, 5, "2008-07-05"],
            [2007, 12, 31, "2007-12-31"],
            [999, 12, 31, "0999-12-31"],
            [-1, 1, 2, "-0001-01-02"],
            [9999, 12, 31, "9999-12-31"],
            [-9999, 12, 31, "-9999-12-31"],
            [10000, 1, 1, "+10000-01-01"],
            [-10000, 1, 1, "-10000-01-01"],
            [12345678, 1, 1, "+12345678-01-01"],
            [-12345678, 1, 1, "-12345678-01-01"],
        ];
    }

    /**
     * @dataProvider provider_sampleToString
     */
    public function test_toString($y, $m, $d, $expected)
    {
        $t = LocalDate::of($y, $m, $d);
        $str = $t->__toString();
        $this->assertEquals($str, $expected);
    }

    private function date($year, $month, $day)
    {
        return LocalDate::of($year, $month, $day);
    }

}
