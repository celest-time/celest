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

use Celest\Format\DateTimeFormatter;
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
use Exception;


/**
 * Test OffsetTime.
 */
class TCKOffsetTimeTest extends AbstractDateTimeTest
{

    private static function OFFSET_PONE()
    {
        return ZoneOffset::ofHours(1);
    }

    private static function OFFSET_PTWO()
    {
        return ZoneOffset::ofHours(2);
    }

    private static function ZONE_GAZA()
    {
        return ZoneId::of("Asia/Gaza");
    }

    private static function DATE()
    {
        return LocalDate::of(2008, 12, 3);
    }

    private static function TEST_11_30_59_500_PONE()
    {
        return OffsetTime::of(11, 30, 59, 500, self::OFFSET_PONE());
    }

    //-----------------------------------------------------------------------
    protected function samples()
    {
        return [self::TEST_11_30_59_500_PONE(), OffsetTime::MIN(), OffsetTime::MAX()];
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
            CF::OFFSET_SECONDS(),
        ];
    }

    protected function invalidFields()
    {
        $list = array_diff(CF::values(), $this->validFields());
        $list[] = JulianFields::JULIAN_DAY();
        $list[] = JulianFields::MODIFIED_JULIAN_DAY();
        $list[] = JulianFields::RATA_DIE();
        return $list;
    }

    //-----------------------------------------------------------------------
    // constants
    //-----------------------------------------------------------------------

    public function test_constant_MIN()
    {
        $this->check(OffsetTime::MIN(), 0, 0, 0, 0, ZoneOffset::MAX());
    }


    public function test_constant_MAX()
    {
        $this->check(OffsetTime::MAX(), 23, 59, 59, 999999999, ZoneOffset::MIN());
    }

    //-----------------------------------------------------------------------
    // now()
    //-----------------------------------------------------------------------

    public function test_now()
    {
        $nowDT = ZonedDateTime::now();

        $expected = OffsetTime::nowOf(Clock::systemDefaultZone());
        $test = OffsetTime::now();
        $diff = Math::abs($test->toLocalTime()->toNanoOfDay() - $expected->toLocalTime()->toNanoOfDay());
        $this->assertTrue($diff < 100000000);  // less than 0.1 secs
        $this->assertEquals($test->getOffset(), $nowDT->getOffset());
    }

    //-----------------------------------------------------------------------
    // now(Clock)
    //-----------------------------------------------------------------------

    /**
     * @group long
     */
    public function test_now_Clock_allSecsInDay()
    {
        for ($i = 0; $i < (2 * 24 * 60 * 60); $i++) {
            $instant = Instant::ofEpochSecond($i, 8);
            $clock = Clock::fixed($instant, ZoneOffset::UTC());
            $test = OffsetTime::nowOf($clock);
            $this->assertEquals($test->getHour(), ($i / (60 * 60)) % 24);
            $this->assertEquals($test->getMinute(), ($i / 60) % 60);
            $this->assertEquals($test->getSecond(), $i % 60);
            $this->assertEquals($test->getNano(), 8);
            $this->assertEquals($test->getOffset(), ZoneOffset::UTC());
        }
    }


    /**
     * @group long
     */
    public function test_now_Clock_beforeEpoch()
    {
        for ($i = -1; $i >= -(24 * 60 * 60); $i--) {
            $instant = Instant::ofEpochSecond($i, 8);
            $clock = Clock::fixed($instant, ZoneOffset::UTC());
            $test = OffsetTime::nowOf($clock);
            $this->assertEquals($test->getHour(), (($i + 24 * 60 * 60) / (60 * 60)) % 24);
            $this->assertEquals($test->getMinute(), (($i + 24 * 60 * 60) / 60) % 60);
            $this->assertEquals($test->getSecond(), ($i + 24 * 60 * 60) % 60);
            $this->assertEquals($test->getNano(), 8);
            $this->assertEquals($test->getOffset(), ZoneOffset::UTC());
        }
    }

    /**
     * @group long
     */
    public function test_now_Clock_offsets()
    {
        $base = LocalDateTime::of(1970, 1, 1, 12, 0)->toInstant(ZoneOffset::UTC());
        for ($i = -9; $i < 15; $i++) {
            $offset = ZoneOffset::ofHours($i);
            $clock = Clock::fixed($base, $offset);
            $test = OffsetTime::nowOf($clock);
            $this->assertEquals($test->getHour(), (12 + $i) % 24);
            $this->assertEquals($test->getMinute(), 0);
            $this->assertEquals($test->getSecond(), 0);
            $this->assertEquals($test->getNano(), 0);
            $this->assertEquals($test->getOffset(), $offset);
        }
    }

    public function test_now_Clock_nullZoneId()
    {
        TestHelper::assertNullException($this, function () {
            OffsetTime::nowIn(null);
        });
    }

    public function test_now_Clock_nullClock()
    {
        TestHelper::assertNullException($this, function () {
            OffsetTime::nowOf(null);
        });
    }

    //-----------------------------------------------------------------------
    // factories
    //-----------------------------------------------------------------------
    private function check(OffsetTime $test, $h, $m, $s, $n, ZoneOffset $offset)
    {
        $this->assertEquals($test->toLocalTime(), LocalTime::of($h, $m, $s, $n));
        $this->assertEquals($test->getOffset(), $offset);

        $this->assertEquals($test->getHour(), $h);
        $this->assertEquals($test->getMinute(), $m);
        $this->assertEquals($test->getSecond(), $s);
        $this->assertEquals($test->getNano(), $n);

        $this->assertEquals($test, $test);
        $this->assertEquals(OffsetTime::ofLocalTime(LocalTime::of($h, $m, $s, $n), $offset), $test);
    }

//-----------------------------------------------------------------------

    public function test_factory_intsHMSN()
    {
        $test = OffsetTime::of(11, 30, 10, 500, self::OFFSET_PONE());
        $this->check($test, 11, 30, 10, 500, self::OFFSET_PONE());
    }

//-----------------------------------------------------------------------

    public function test_factory_LocalTimeZoneOffset()
    {
        $localTime = LocalTime::of(11, 30, 10, 500);
        $test = OffsetTime::ofLocalTime($localTime, self::OFFSET_PONE());
        $this->check($test, 11, 30, 10, 500, self::OFFSET_PONE());
    }

    public function test_factory_LocalTimeZoneOffset_nullTime()
    {
        TestHelper::assertNullException($this, function () {
            OffsetTime::ofLocalTime(null, self::OFFSET_PONE());
        });
    }

    public function test_factory_LocalTimeZoneOffset_nullOffset()
    {
        TestHelper::assertNullException($this, function () {
            $localTime = LocalTime::of(11, 30, 10, 500);
            OffsetTime::ofLocalTime($localTime, null);
        });

    }

    //-----------------------------------------------------------------------
    // ofInstant()
    //-----------------------------------------------------------------------
    public function test_factory_ofInstant_nullInstant()
    {
        TestHelper::assertNullException($this, function () {
            OffsetTime::ofInstant(null, ZoneOffset::UTC());
        });
    }

    public function test_factory_ofInstant_nullOffset()
    {
        TestHelper::assertNullException($this, function () {
            $instant = Instant::ofEpochSecond(0);
            OffsetTime::ofInstant($instant, null);
        });

    }

    /**
     * @group long
     */
    public function test_factory_ofInstant_allSecsInDay()
    {
        for ($i = 0; $i < (2 * 24 * 60 * 60); $i++) {
            $instant = Instant::ofEpochSecond($i, 8);
            $test = OffsetTime::ofInstant($instant, ZoneOffset::UTC());
            $this->assertEquals($test->getHour(), ($i / (60 * 60)) % 24);
            $this->assertEquals($test->getMinute(), ($i / 60) % 60);
            $this->assertEquals($test->getSecond(), $i % 60);
            $this->assertEquals($test->getNano(), 8);
        }
    }

    /**
     * @group long
     */
    public function test_factory_ofInstant_beforeEpoch()
    {
        for ($i = -1; $i >= -(24 * 60 * 60); $i--) {
            $instant = Instant::ofEpochSecond($i, 8);
            $test = OffsetTime::ofInstant($instant, ZoneOffset::UTC());
            $this->assertEquals($test->getHour(), (($i + 24 * 60 * 60) / (60 * 60)) % 24);
            $this->assertEquals($test->getMinute(), (($i + 24 * 60 * 60) / 60) % 60);
            $this->assertEquals($test->getSecond(), ($i + 24 * 60 * 60) % 60);
            $this->assertEquals($test->getNano(), 8);
        }
    }

    //-----------------------------------------------------------------------

    public function test_factory_ofInstant_maxYear()
    {
        $test = OffsetTime::ofInstant(Instant::MAX(), ZoneOffset::UTC());
        $this->assertEquals($test->getHour(), 23);
        $this->assertEquals($test->getMinute(), 59);
        $this->assertEquals($test->getSecond(), 59);
        $this->assertEquals($test->getNano(), 999999999);
    }


    public function test_factory_ofInstant_minYear()
    {
        $test = OffsetTime::ofInstant(Instant::MIN(), ZoneOffset::UTC());
        $this->assertEquals($test->getHour(), 0);
        $this->assertEquals($test->getMinute(), 0);
        $this->assertEquals($test->getSecond(), 0);
        $this->assertEquals($test->getNano(), 0);
    }

    //-----------------------------------------------------------------------
    // from(TemporalAccessor)
    //-----------------------------------------------------------------------

    public function test_factory_from_TemporalAccessor_OT()
    {
        $this->assertEquals(OffsetTime::from(OffsetTime::of(17, 30, 0, 0, self::OFFSET_PONE())), OffsetTime::of(17, 30, 0, 0, self::OFFSET_PONE()));
    }


    public function test_from_TemporalAccessor_ZDT()
    {
        $base = LocalDateTime::of(2007, 7, 15, 11, 30, 59, 500)->atZone(self::OFFSET_PONE());
        $this->assertEquals(OffsetTime::from($base), self::TEST_11_30_59_500_PONE());
    }

    /**
     * @expectedException \Celest\DateTimeException
     */
    public function test_factory_from_TemporalAccessor_invalid_noDerive()
    {
        OffsetTime::from(LocalDate::of(2007, 7, 15));
    }

    public function test_factory_from_TemporalAccessor_null()
    {
        TestHelper::assertNullException($this, function () {
            OffsetTime::from(null);
        });
    }

    //-----------------------------------------------------------------------
    // parse()
    //-----------------------------------------------------------------------
    /**
     * @dataProvider provider_sampleToString
     */
    public function test_factory_parse_validText($h, $m, $s, $n, $offsetId, $parsable)
    {
        $t = OffsetTime::parse($parsable);
        $this->assertNotNull($t, $parsable);
        $this->check($t, $h, $m, $s, $n, ZoneOffset::of($offsetId));
    }

    function provider_sampleBadParse()
    {
        return [
            ["00;00"],
            ["12-00"],
            ["-01:00"],
            ["00:00:00-09"],
            ["00:00:00,09"],
            ["00:00:abs"],
            ["11"],
            ["11:30"],
            ["11:30+01:00[Europe/Paris]"],
        ];
    }

    /**
     * @dataProvider provider_sampleBadParse
     * @expectedException \Celest\DateTimeParseException
     */
    public function test_factory_parse_invalidText($unparsable)
    {
        OffsetTime::parse($unparsable);
    }

//-----------------------------------------------------------------------s
    /**
     * @expectedException \Celest\DateTimeParseException
     */
    public function test_factory_parse_illegalHour()
    {
        OffsetTime::parse("25:00+01:00");
    }

    /**
     * @expectedException \Celest\DateTimeParseException
     */
    public function test_factory_parse_illegalMinute()
    {
        OffsetTime::parse("12:60+01:00");
    }

    /**
     * @expectedException \Celest\DateTimeParseException
     */
    public function test_factory_parse_illegalSecond()
    {
        OffsetTime::parse("12:12:60+01:00");
    }

    //-----------------------------------------------------------------------
    // parse(DateTimeFormatter)
    //-----------------------------------------------------------------------

    public function test_factory_parse_formatter()
    {
        $f = DateTimeFormatter::ofPattern("H m s XXX");
        $test = OffsetTime::parseWith("11 30 0 +01:00", $f);
        $this->assertEquals($test, OffsetTime::of(11, 30, 0, 0, ZoneOffset::ofHours(1)));
    }

    public function test_factory_parse_formatter_nullText()
    {
        TestHelper::assertNullException($this, function () {
            $f = DateTimeFormatter::ofPattern("y M d H m s");
            OffsetTime::parseWith(null, $f);
        });

    }

    public function test_factory_parse_formatter_nullFormatter()
    {
        TestHelper::assertNullException($this, function () {
            OffsetTime::parseWith("ANY", null);
        });
    }

    //-----------------------------------------------------------------------
    // constructor
    //-----------------------------------------------------------------------
    /*(expectedExceptions = NullPointerException::class) TODO
        public function test_constructor_nullTime()
        {
            Constructor < OffsetTime> con = OffsetTime::class->getDeclaredConstructor(LocalTime::class, ZoneOffset::class);
    con->setAccessible(true);
    try {
        con->newInstance(null, self::OFFSET_PONE());
    } catch
    (InvocationTargetException $ex) {
        throw $ex->getCause();
    }
        }
    
    (expectedExceptions = NullPointerException::class)
        public function test_constructor_nullOffset()
        {
            Constructor < OffsetTime> con = OffsetTime::class->getDeclaredConstructor(LocalTime::class, ZoneOffset::class);
            con->setAccessible(true);
            try {
                con->newInstance(LocalTime::of(11, 30, 0, 0), null);
            } catch (InvocationTargetException $ex) {
                throw $ex->getCause();
            }
        }*/

    //-----------------------------------------------------------------------
    // basics
    //-----------------------------------------------------------------------
    function provider_sampleTimes()
    {
        return [
            [11, 30, 20, 500, self::OFFSET_PONE()],
            [11, 0, 0, 0, self::OFFSET_PONE()],
            [23, 59, 59, 999999999, self::OFFSET_PONE()],
        ];
    }

    /**
     * @dataProvider provider_sampleTimes
     */
    public function test_get($h, $m, $s, $n, ZoneOffset $offset)
    {
        $localTime = LocalTime::of($h, $m, $s, $n);
        $a = OffsetTime::ofLocalTime($localTime, $offset);

        $this->assertEquals($a->toLocalTime(), $localTime);
        $this->assertEquals($a->getOffset(), $offset);
        $this->assertEquals($a->__toString(), $localTime->__toString() . $offset->__toString());
        $this->assertEquals($a->getHour(), $localTime->getHour());
        $this->assertEquals($a->getMinute(), $localTime->getMinute());
        $this->assertEquals($a->getSecond(), $localTime->getSecond());
        $this->assertEquals($a->getNano(), $localTime->getNano());
    }

    //-----------------------------------------------------------------------
    // isSupported(TemporalField)
    //-----------------------------------------------------------------------

    public function test_isSupported_TemporalField()
    {
        // $this->assertEquals(self::TEST_11_30_59_500_PONE()->isSupported((TemporalField) null), false); TODO
        $this->assertEquals(self::TEST_11_30_59_500_PONE()->isSupported(CF::NANO_OF_SECOND()), true);
        $this->assertEquals(self::TEST_11_30_59_500_PONE()->isSupported(CF::NANO_OF_DAY()), true);
        $this->assertEquals(self::TEST_11_30_59_500_PONE()->isSupported(CF::MICRO_OF_SECOND()), true);
        $this->assertEquals(self::TEST_11_30_59_500_PONE()->isSupported(CF::MICRO_OF_DAY()), true);
        $this->assertEquals(self::TEST_11_30_59_500_PONE()->isSupported(CF::MILLI_OF_SECOND()), true);
        $this->assertEquals(self::TEST_11_30_59_500_PONE()->isSupported(CF::MILLI_OF_DAY()), true);
        $this->assertEquals(self::TEST_11_30_59_500_PONE()->isSupported(CF::SECOND_OF_MINUTE()), true);
        $this->assertEquals(self::TEST_11_30_59_500_PONE()->isSupported(CF::SECOND_OF_DAY()), true);
        $this->assertEquals(self::TEST_11_30_59_500_PONE()->isSupported(CF::MINUTE_OF_HOUR()), true);
        $this->assertEquals(self::TEST_11_30_59_500_PONE()->isSupported(CF::MINUTE_OF_DAY()), true);
        $this->assertEquals(self::TEST_11_30_59_500_PONE()->isSupported(CF::HOUR_OF_AMPM()), true);
        $this->assertEquals(self::TEST_11_30_59_500_PONE()->isSupported(CF::CLOCK_HOUR_OF_AMPM()), true);
        $this->assertEquals(self::TEST_11_30_59_500_PONE()->isSupported(CF::HOUR_OF_DAY()), true);
        $this->assertEquals(self::TEST_11_30_59_500_PONE()->isSupported(CF::CLOCK_HOUR_OF_DAY()), true);
        $this->assertEquals(self::TEST_11_30_59_500_PONE()->isSupported(CF::AMPM_OF_DAY()), true);
        $this->assertEquals(self::TEST_11_30_59_500_PONE()->isSupported(CF::DAY_OF_WEEK()), false);
        $this->assertEquals(self::TEST_11_30_59_500_PONE()->isSupported(CF::ALIGNED_DAY_OF_WEEK_IN_MONTH()), false);
        $this->assertEquals(self::TEST_11_30_59_500_PONE()->isSupported(CF::ALIGNED_DAY_OF_WEEK_IN_YEAR()), false);
        $this->assertEquals(self::TEST_11_30_59_500_PONE()->isSupported(CF::DAY_OF_MONTH()), false);
        $this->assertEquals(self::TEST_11_30_59_500_PONE()->isSupported(CF::DAY_OF_YEAR()), false);
        $this->assertEquals(self::TEST_11_30_59_500_PONE()->isSupported(CF::EPOCH_DAY()), false);
        $this->assertEquals(self::TEST_11_30_59_500_PONE()->isSupported(CF::ALIGNED_WEEK_OF_MONTH()), false);
        $this->assertEquals(self::TEST_11_30_59_500_PONE()->isSupported(CF::ALIGNED_WEEK_OF_YEAR()), false);
        $this->assertEquals(self::TEST_11_30_59_500_PONE()->isSupported(CF::MONTH_OF_YEAR()), false);
        $this->assertEquals(self::TEST_11_30_59_500_PONE()->isSupported(CF::PROLEPTIC_MONTH()), false);
        $this->assertEquals(self::TEST_11_30_59_500_PONE()->isSupported(CF::YEAR()), false);
        $this->assertEquals(self::TEST_11_30_59_500_PONE()->isSupported(CF::YEAR_OF_ERA()), false);
        $this->assertEquals(self::TEST_11_30_59_500_PONE()->isSupported(CF::ERA()), false);
        $this->assertEquals(self::TEST_11_30_59_500_PONE()->isSupported(CF::INSTANT_SECONDS()), false);
        $this->assertEquals(self::TEST_11_30_59_500_PONE()->isSupported(CF::OFFSET_SECONDS()), true);
    }

    //-----------------------------------------------------------------------
    // isSupported(TemporalUnit)
    //-----------------------------------------------------------------------

    public function test_isSupported_TemporalUnit()
    {
        // $this->assertEquals(self::TEST_11_30_59_500_PONE()->isSupported((TemporalUnit) null), false); TODO
        $this->assertEquals(self::TEST_11_30_59_500_PONE()->isUnitSupported(CU::NANOS()), true);
        $this->assertEquals(self::TEST_11_30_59_500_PONE()->isUnitSupported(CU::MICROS()), true);
        $this->assertEquals(self::TEST_11_30_59_500_PONE()->isUnitSupported(CU::MILLIS()), true);
        $this->assertEquals(self::TEST_11_30_59_500_PONE()->isUnitSupported(CU::SECONDS()), true);
        $this->assertEquals(self::TEST_11_30_59_500_PONE()->isUnitSupported(CU::MINUTES()), true);
        $this->assertEquals(self::TEST_11_30_59_500_PONE()->isUnitSupported(CU::HOURS()), true);
        $this->assertEquals(self::TEST_11_30_59_500_PONE()->isUnitSupported(CU::HALF_DAYS()), true);
        $this->assertEquals(self::TEST_11_30_59_500_PONE()->isUnitSupported(CU::DAYS()), false);
        $this->assertEquals(self::TEST_11_30_59_500_PONE()->isUnitSupported(CU::WEEKS()), false);
        $this->assertEquals(self::TEST_11_30_59_500_PONE()->isUnitSupported(CU::MONTHS()), false);
        $this->assertEquals(self::TEST_11_30_59_500_PONE()->isUnitSupported(CU::YEARS()), false);
        $this->assertEquals(self::TEST_11_30_59_500_PONE()->isUnitSupported(CU::DECADES()), false);
        $this->assertEquals(self::TEST_11_30_59_500_PONE()->isUnitSupported(CU::CENTURIES()), false);
        $this->assertEquals(self::TEST_11_30_59_500_PONE()->isUnitSupported(CU::MILLENNIA()), false);
        $this->assertEquals(self::TEST_11_30_59_500_PONE()->isUnitSupported(CU::ERAS()), false);
        $this->assertEquals(self::TEST_11_30_59_500_PONE()->isUnitSupported(CU::FOREVER()), false);
    }

    //-----------------------------------------------------------------------
    // get(TemporalField)
    //-----------------------------------------------------------------------

    public function test_get_TemporalField()
    {
        $test = OffsetTime::of(12, 30, 40, 987654321, self::OFFSET_PONE());
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
        $test = OffsetTime::of(12, 30, 40, 987654321, self::OFFSET_PONE());
        $this->assertEquals($test->getLong(CF::HOUR_OF_DAY()), 12);
        $this->assertEquals($test->getLong(CF::MINUTE_OF_HOUR()), 30);
        $this->assertEquals($test->getLong(CF::SECOND_OF_MINUTE()), 40);
        $this->assertEquals($test->getLong(CF::NANO_OF_SECOND()), 987654321);
        $this->assertEquals($test->getLong(CF::HOUR_OF_AMPM()), 0);
        $this->assertEquals($test->getLong(CF::AMPM_OF_DAY()), 1);

        $this->assertEquals($test->getLong(CF::OFFSET_SECONDS()), 3600);
    }

    //-----------------------------------------------------------------------
    // query(TemporalQuery)
    //-----------------------------------------------------------------------
    function data_query()
    {
        return [
            [self::TEST_11_30_59_500_PONE(), TemporalQueries::chronology(), null],
            [self::TEST_11_30_59_500_PONE(), TemporalQueries::zoneId(), null],
            [self::TEST_11_30_59_500_PONE(), TemporalQueries::precision(), CU::NANOS()],
            [self::TEST_11_30_59_500_PONE(), TemporalQueries::zone(), self::OFFSET_PONE()],
            [self::TEST_11_30_59_500_PONE(), TemporalQueries::offset(), self::OFFSET_PONE()],
            [self::TEST_11_30_59_500_PONE(), TemporalQueries::localDate(), null],
            [self::TEST_11_30_59_500_PONE(), TemporalQueries::localTime(), LocalTime::of(11, 30, 59, 500)],
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
            self::TEST_11_30_59_500_PONE()->query(null);
        });
    }

    //-----------------------------------------------------------------------
    // withOffsetSameLocal()
    //-----------------------------------------------------------------------

    public function test_withOffsetSameLocal()
    {
        $base = OffsetTime::of(11, 30, 59, 0, self::OFFSET_PONE());
        $test = $base->withOffsetSameLocal(self::OFFSET_PTWO());
        $this->assertEquals($test->toLocalTime(), $base->toLocalTime());
        $this->assertEquals($test->getOffset(), self::OFFSET_PTWO());
    }


    public function test_withOffsetSameLocal_noChange()
    {
        $base = OffsetTime::of(11, 30, 59, 0, self::OFFSET_PONE());
        $test = $base->withOffsetSameLocal(self::OFFSET_PONE());
        $this->assertEquals($test, $base);
    }

    public function test_withOffsetSameLocal_null()
    {
        TestHelper::assertNullException($this, function () {
            $base = OffsetTime::of(11, 30, 59, 0, self::OFFSET_PONE());
            $base->withOffsetSameLocal(null);
        });

    }

    //-----------------------------------------------------------------------
    // withOffsetSameInstant()
    //-----------------------------------------------------------------------

    public function test_withOffsetSameInstant()
    {
        $base = OffsetTime::of(11, 30, 59, 0, self::OFFSET_PONE());
        $test = $base->withOffsetSameInstant(self::OFFSET_PTWO());
        $expected = OffsetTime::of(12, 30, 59, 0, self::OFFSET_PTWO());
        $this->assertEquals($test, $expected);
    }


    public function test_withOffsetSameInstant_noChange()
    {
        $base = OffsetTime::of(11, 30, 59, 0, self::OFFSET_PONE());
        $test = $base->withOffsetSameInstant(self::OFFSET_PONE());
        $this->assertEquals($test, $base);
    }

    public function test_withOffsetSameInstant_null()
    {
        TestHelper::assertNullException($this, function () {
            $base = OffsetTime::of(11, 30, 59, 0, self::OFFSET_PONE());
            $base->withOffsetSameInstant(null);
        });

    }

    //-----------------------------------------------------------------------
    // adjustInto(Temporal)
    //-----------------------------------------------------------------------
    function data_adjustInto()
    {
        return [
            [OffsetTime::ofLocalTime(LocalTime::of(23, 5), self::OFFSET_PONE()), OffsetTime::ofLocalTime(LocalTime::of(1, 1, 1, 100), ZoneOffset::UTC()), OffsetTime::ofLocalTime(LocalTime::of(23, 5), self::OFFSET_PONE()), null],
            [OffsetTime::ofLocalTime(LocalTime::of(23, 5), self::OFFSET_PONE()), OffsetTime::MAX(), OffsetTime::ofLocalTime(LocalTime::of(23, 5), self::OFFSET_PONE()), null],
            [OffsetTime::ofLocalTime(LocalTime::of(23, 5), self::OFFSET_PONE()), OffsetTime::MIN(), OffsetTime::ofLocalTime(LocalTime::of(23, 5), self::OFFSET_PONE()), null],
            [OffsetTime::MAX(), OffsetTime::ofLocalTime(LocalTime::of(23, 5), self::OFFSET_PONE()), OffsetTime::ofLocalTime(OffsetTime::MAX()->toLocalTime(), ZoneOffset::ofHours(-18)), null],
            [OffsetTime::MIN(), OffsetTime::ofLocalTime(LocalTime::of(23, 5), self::OFFSET_PONE()), OffsetTime::ofLocalTime(OffsetTime::MIN()->toLocalTime(), ZoneOffset::ofHours(18)), null],


            [OffsetTime::ofLocalTime(LocalTime::of(23, 5), self::OFFSET_PONE()), ZonedDateTime::ofDateTime(LocalDateTime::of(2012, 3, 4, 1, 1, 1, 100), self::ZONE_GAZA()), ZonedDateTime::ofDateTime(LocalDateTime::of(2012, 3, 4, 23, 5), self::ZONE_GAZA()), null],
            [OffsetTime::ofLocalTime(LocalTime::of(23, 5), self::OFFSET_PONE()), OffsetDateTime::ofDateTime(LocalDateTime::of(2012, 3, 4, 1, 1, 1, 100), ZoneOffset::UTC()), OffsetDateTime::ofDateTime(LocalDateTime::of(2012, 3, 4, 23, 5), self::OFFSET_PONE()), null],

            [OffsetTime::ofLocalTime(LocalTime::of(23, 5), self::OFFSET_PONE()), LocalDateTime::of(2012, 3, 4, 1, 1, 1, 100), null, DateTimeException::class],
            [OffsetTime::ofLocalTime(LocalTime::of(23, 5), self::OFFSET_PONE()), LocalDate::of(2210, 2, 2), null, DateTimeException::class],
            [OffsetTime::ofLocalTime(LocalTime::of(23, 5), self::OFFSET_PONE()), LocalTime::of(22, 3, 0), null, DateTimeException::class],
            // TODO [OffsetTime::ofLocalTime(LocalTime::of(23, 5), self::OFFSET_PONE()), null, null, NullPointerException::class],
        ];
    }

    /**
     * @dataProvider data_adjustInto
     */
    public function test_adjustInto(OffsetTime $test, Temporal $temporal, $expected, $expectedEx)
    {
        if ($expectedEx == null) {
            $result = $test->adjustInto($temporal);
            $this->assertEquals($result, $expected);
        } else {
            try {
                $result = $test->adjustInto($temporal);
                $this->fail();
            } catch (Exception $ex) {
                $this->assertInstanceOf($expectedEx, $ex);
            }
        }
    }

//-----------------------------------------------------------------------
// with(WithAdjuster)
//-----------------------------------------------------------------------

    public function test_with_adjustment()
    {
        $sample = OffsetTime::of(23, 5, 0, 0, self::OFFSET_PONE());
        $adjuster = TemporalAdjusters::fromCallable(function () use ($sample) {
            return $sample;
        });
        $this->assertEquals(self::TEST_11_30_59_500_PONE()->adjust($adjuster), $sample);
    }


    public function test_with_adjustment_LocalTime()
    {
        $test = self::TEST_11_30_59_500_PONE()->adjust(LocalTime::of(13, 30));
        $this->assertEquals($test, OffsetTime::of(13, 30, 0, 0, self::OFFSET_PONE()));
    }


    public function test_with_adjustment_OffsetTime()
    {
        $test = self::TEST_11_30_59_500_PONE()->adjust(OffsetTime::of(13, 35, 0, 0, self::OFFSET_PTWO()));
        $this->assertEquals($test, OffsetTime::of(13, 35, 0, 0, self::OFFSET_PTWO()));
    }


    public function test_with_adjustment_ZoneOffset()
    {
        $test = self::TEST_11_30_59_500_PONE()->adjust(self::OFFSET_PTWO());
        $this->assertEquals($test, OffsetTime::of(11, 30, 59, 500, self::OFFSET_PTWO()));
    }


    public function test_with_adjustment_AmPm()
    {
        $adjuster = TemporalAdjusters::fromCallable(function (Temporal $dateTime) {
            return $dateTime->with(CF::HOUR_OF_DAY(), 23);
        });
        $test = self::TEST_11_30_59_500_PONE()->adjust($adjuster);
        $this->assertEquals($test, OffsetTime::of(23, 30, 59, 500, self::OFFSET_PONE()));
    }

    public function test_with_adjustment_null()
    {
        TestHelper::assertNullException($this, function () {
            self::TEST_11_30_59_500_PONE()->adjust(null);
        });
    }

//-----------------------------------------------------------------------
// with(TemporalField, long)
//-----------------------------------------------------------------------

    public function test_with_TemporalField()
    {
        $test = OffsetTime::of(12, 30, 40, 987654321, self::OFFSET_PONE());
        $this->assertEquals($test->with(CF::HOUR_OF_DAY(), 15), OffsetTime::of(15, 30, 40, 987654321, self::OFFSET_PONE()));
        $this->assertEquals($test->with(CF::MINUTE_OF_HOUR(), 50), OffsetTime::of(12, 50, 40, 987654321, self::OFFSET_PONE()));
        $this->assertEquals($test->with(CF::SECOND_OF_MINUTE(), 50), OffsetTime::of(12, 30, 50, 987654321, self::OFFSET_PONE()));
        $this->assertEquals($test->with(CF::NANO_OF_SECOND(), 12345), OffsetTime::of(12, 30, 40, 12345, self::OFFSET_PONE()));
        $this->assertEquals($test->with(CF::HOUR_OF_AMPM(), 6), OffsetTime::of(18, 30, 40, 987654321, self::OFFSET_PONE()));
        $this->assertEquals($test->with(CF::AMPM_OF_DAY(), 0), OffsetTime::of(0, 30, 40, 987654321, self::OFFSET_PONE()));

        $this->assertEquals($test->with(CF::OFFSET_SECONDS(), 7205), OffsetTime::of(12, 30, 40, 987654321, ZoneOffset::ofHoursMinutesSeconds(2, 0, 5)));
    }

    public function test_with_TemporalField_null()
    {
        TestHelper::assertNullException($this, function () {
            self::TEST_11_30_59_500_PONE()->with(null, 0);
        });
    }

    /**
     * @expectedException \Celest\DateTimeException
     */
    public function test_with_TemporalField_invalidField()
    {
        self::TEST_11_30_59_500_PONE()->with(CF::YEAR(), 0);
    }

//-----------------------------------------------------------------------
// withHour()
//-----------------------------------------------------------------------

    public function test_withHour_normal()
    {
        $base = OffsetTime::of(11, 30, 59, 0, self::OFFSET_PONE());
        $test = $base->withHour(15);
        $this->assertEquals($test, OffsetTime::of(15, 30, 59, 0, self::OFFSET_PONE()));
    }


    public function test_withHour_noChange()
    {
        $base = OffsetTime::of(11, 30, 59, 0, self::OFFSET_PONE());
        $test = $base->withHour(11);
        $this->assertEquals($test, $base);
    }

//-----------------------------------------------------------------------
// withMinute()
//-----------------------------------------------------------------------

    public function test_withMinute_normal()
    {
        $base = OffsetTime::of(11, 30, 59, 0, self::OFFSET_PONE());
        $test = $base->withMinute(15);
        $this->assertEquals($test, OffsetTime::of(11, 15, 59, 0, self::OFFSET_PONE()));
    }


    public function test_withMinute_noChange()
    {
        $base = OffsetTime::of(11, 30, 59, 0, self::OFFSET_PONE());
        $test = $base->withMinute(30);
        $this->assertEquals($test, $base);
    }

//-----------------------------------------------------------------------
// withSecond()
//-----------------------------------------------------------------------

    public function test_withSecond_normal()
    {
        $base = OffsetTime::of(11, 30, 59, 0, self::OFFSET_PONE());
        $test = $base->withSecond(15);
        $this->assertEquals($test, OffsetTime::of(11, 30, 15, 0, self::OFFSET_PONE()));
    }


    public function test_withSecond_noChange()
    {
        $base = OffsetTime::of(11, 30, 59, 0, self::OFFSET_PONE());
        $test = $base->withSecond(59);
        $this->assertEquals($test, $base);
    }

//-----------------------------------------------------------------------
// withNano()
//-----------------------------------------------------------------------

    public function test_withNanoOfSecond_normal()
    {
        $base = OffsetTime::of(11, 30, 59, 1, self::OFFSET_PONE());
        $test = $base->withNano(15);
        $this->assertEquals($test, OffsetTime::of(11, 30, 59, 15, self::OFFSET_PONE()));
    }


    public function test_withNanoOfSecond_noChange()
    {
        $base = OffsetTime::of(11, 30, 59, 1, self::OFFSET_PONE());
        $test = $base->withNano(1);
        $this->assertEquals($test, $base);
    }

//-----------------------------------------------------------------------
// truncatedTo(TemporalUnit)
//-----------------------------------------------------------------------

    public function test_truncatedTo_normal()
    {
        $this->assertEquals(self::TEST_11_30_59_500_PONE()->truncatedTo(CU::NANOS()), self::TEST_11_30_59_500_PONE());
        $this->assertEquals(self::TEST_11_30_59_500_PONE()->truncatedTo(CU::SECONDS()), self::TEST_11_30_59_500_PONE()->withNano(0));
        $this->assertEquals(self::TEST_11_30_59_500_PONE()->truncatedTo(CU::DAYS()), self::TEST_11_30_59_500_PONE()->adjust(LocalTime::MIDNIGHT()));
    }

    public function test_truncatedTo_null()
    {
        TestHelper::assertNullException($this, function () {
            self::TEST_11_30_59_500_PONE()->truncatedTo(null);
        });
    }

//-----------------------------------------------------------------------
// plus(PlusAdjuster)
//-----------------------------------------------------------------------

    public function test_plus_PlusAdjuster()
    {
        $period = MockSimplePeriod::of(7, CU::MINUTES());
        $t = self::TEST_11_30_59_500_PONE()->plusAmount($period);
        $this->assertEquals($t, OffsetTime::of(11, 37, 59, 500, self::OFFSET_PONE()));
    }


    public function test_plus_PlusAdjuster_noChange()
    {
        $t = self::TEST_11_30_59_500_PONE()->plusAmount(MockSimplePeriod::of(0, CU::SECONDS()));
        $this->assertEquals($t, self::TEST_11_30_59_500_PONE());
    }


    public function test_plus_PlusAdjuster_zero()
    {
        $t = self::TEST_11_30_59_500_PONE()->plusAmount(Period::ZERO());
        $this->assertEquals($t, self::TEST_11_30_59_500_PONE());
    }

    public function test_plus_PlusAdjuster_null()
    {
        TestHelper::assertNullException($this, function () {
            self::TEST_11_30_59_500_PONE()->plusAmount(null);
        });
    }

//-----------------------------------------------------------------------
// plusHours()
//-----------------------------------------------------------------------

    public function test_plusHours()
    {
        $base = OffsetTime::of(11, 30, 59, 0, self::OFFSET_PONE());
        $test = $base->plusHours(13);
        $this->assertEquals($test, OffsetTime::of(0, 30, 59, 0, self::OFFSET_PONE()));
    }


    public function test_plusHours_zero()
    {
        $base = OffsetTime::of(11, 30, 59, 0, self::OFFSET_PONE());
        $test = $base->plusHours(0);
        $this->assertEquals($test, $base);
    }

//-----------------------------------------------------------------------
// plusMinutes()
//-----------------------------------------------------------------------

    public function test_plusMinutes()
    {
        $base = OffsetTime::of(11, 30, 59, 0, self::OFFSET_PONE());
        $test = $base->plusMinutes(30);
        $this->assertEquals($test, OffsetTime::of(12, 0, 59, 0, self::OFFSET_PONE()));
    }


    public function test_plusMinutes_zero()
    {
        $base = OffsetTime::of(11, 30, 59, 0, self::OFFSET_PONE());
        $test = $base->plusMinutes(0);
        $this->assertEquals($test, $base);
    }

//-----------------------------------------------------------------------
// plusSeconds()
//-----------------------------------------------------------------------

    public function test_plusSeconds()
    {
        $base = OffsetTime::of(11, 30, 59, 0, self::OFFSET_PONE());
        $test = $base->plusSeconds(1);
        $this->assertEquals($test, OffsetTime::of(11, 31, 0, 0, self::OFFSET_PONE()));
    }


    public function test_plusSeconds_zero()
    {
        $base = OffsetTime::of(11, 30, 59, 0, self::OFFSET_PONE());
        $test = $base->plusSeconds(0);
        $this->assertEquals($test, $base);
    }

//-----------------------------------------------------------------------
// plusNanos()
//-----------------------------------------------------------------------

    public function test_plusNanos()
    {
        $base = OffsetTime::of(11, 30, 59, 0, self::OFFSET_PONE());
        $test = $base->plusNanos(1);
        $this->assertEquals($test, OffsetTime::of(11, 30, 59, 1, self::OFFSET_PONE()));
    }


    public function test_plusNanos_zero()
    {
        $base = OffsetTime::of(11, 30, 59, 0, self::OFFSET_PONE());
        $test = $base->plusNanos(0);
        $this->assertEquals($test, $base);
    }

//-----------------------------------------------------------------------
// minus(MinusAdjuster)
//-----------------------------------------------------------------------

    public function test_minus_MinusAdjuster()
    {
        $period = MockSimplePeriod::of(7, CU::MINUTES());
        $t = self::TEST_11_30_59_500_PONE()->minusAmount($period);
        $this->assertEquals($t, OffsetTime::of(11, 23, 59, 500, self::OFFSET_PONE()));
    }


    public function test_minus_MinusAdjuster_noChange()
    {
        $t = self::TEST_11_30_59_500_PONE()->minusAmount(MockSimplePeriod::of(0, CU::SECONDS()));
        $this->assertEquals($t, self::TEST_11_30_59_500_PONE());
    }


    public function test_minus_MinusAdjuster_zero()
    {
        $t = self::TEST_11_30_59_500_PONE()->minusAmount(Period::ZERO());
        $this->assertEquals($t, self::TEST_11_30_59_500_PONE());
    }

    public function test_minus_MinusAdjuster_null()
    {
        TestHelper::assertNullException($this, function () {
            self::TEST_11_30_59_500_PONE()->minusAmount(null);
        });
    }

//-----------------------------------------------------------------------
// minusHours()
//-----------------------------------------------------------------------

    public function test_minusHours()
    {
        $base = OffsetTime::of(11, 30, 59, 0, self::OFFSET_PONE());
        $test = $base->minusHours(-13);
        $this->assertEquals($test, OffsetTime::of(0, 30, 59, 0, self::OFFSET_PONE()));
    }


    public function test_minusHours_zero()
    {
        $base = OffsetTime::of(11, 30, 59, 0, self::OFFSET_PONE());
        $test = $base->minusHours(0);
        $this->assertEquals($test, $base);
    }

//-----------------------------------------------------------------------
// minusMinutes()
//-----------------------------------------------------------------------

    public function test_minusMinutes()
    {
        $base = OffsetTime::of(11, 30, 59, 0, self::OFFSET_PONE());
        $test = $base->minusMinutes(50);
        $this->assertEquals($test, OffsetTime::of(10, 40, 59, 0, self::OFFSET_PONE()));
    }


    public function test_minusMinutes_zero()
    {
        $base = OffsetTime::of(11, 30, 59, 0, self::OFFSET_PONE());
        $test = $base->minusMinutes(0);
        $this->assertEquals($test, $base);
    }

//-----------------------------------------------------------------------
// minusSeconds()
//-----------------------------------------------------------------------

    public function test_minusSeconds()
    {
        $base = OffsetTime::of(11, 30, 59, 0, self::OFFSET_PONE());
        $test = $base->minusSeconds(60);
        $this->assertEquals($test, OffsetTime::of(11, 29, 59, 0, self::OFFSET_PONE()));
    }


    public function test_minusSeconds_zero()
    {
        $base = OffsetTime::of(11, 30, 59, 0, self::OFFSET_PONE());
        $test = $base->minusSeconds(0);
        $this->assertEquals($test, $base);
    }

//-----------------------------------------------------------------------
// minusNanos()
//-----------------------------------------------------------------------

    public function test_minusNanos()
    {
        $base = OffsetTime::of(11, 30, 59, 0, self::OFFSET_PONE());
        $test = $base->minusNanos(1);
        $this->assertEquals($test, OffsetTime::of(11, 30, 58, 999999999, self::OFFSET_PONE()));
    }


    public function test_minusNanos_zero()
    {
        $base = OffsetTime::of(11, 30, 59, 0, self::OFFSET_PONE());
        $test = $base->minusNanos(0);
        $this->assertEquals($test, $base);
    }

//-----------------------------------------------------------------------
// until(Temporal, TemporalUnit)
//-----------------------------------------------------------------------
    function data_untilUnit()
    {
        return [
            [OffsetTime::of(1, 1, 1, 0, self::OFFSET_PONE()), OffsetTime::of(13, 1, 1, 0, self::OFFSET_PONE()), CU::HALF_DAYS(), 1],
            [OffsetTime::of(1, 1, 1, 0, self::OFFSET_PONE()), OffsetTime::of(2, 1, 1, 0, self::OFFSET_PONE()), CU::HOURS(), 1],
            [OffsetTime::of(1, 1, 1, 0, self::OFFSET_PONE()), OffsetTime::of(2, 1, 1, 0, self::OFFSET_PONE()), CU::MINUTES(), 60],
            [OffsetTime::of(1, 1, 1, 0, self::OFFSET_PONE()), OffsetTime::of(2, 1, 1, 0, self::OFFSET_PONE()), CU::SECONDS(), 3600],
            [OffsetTime::of(1, 1, 1, 0, self::OFFSET_PONE()), OffsetTime::of(2, 1, 1, 0, self::OFFSET_PONE()), CU::MILLIS(), 3600 * 1000],
            [OffsetTime::of(1, 1, 1, 0, self::OFFSET_PONE()), OffsetTime::of(2, 1, 1, 0, self::OFFSET_PONE()), CU::MICROS(), 3600 * 1000 * 1000],
            [OffsetTime::of(1, 1, 1, 0, self::OFFSET_PONE()), OffsetTime::of(2, 1, 1, 0, self::OFFSET_PONE()), CU::NANOS(), 3600 * 1000 * 1000 * 1000],

            [OffsetTime::of(1, 1, 1, 0, self::OFFSET_PONE()), OffsetTime::of(14, 1, 1, 0, self::OFFSET_PTWO()), CU::HALF_DAYS(), 1],
            [OffsetTime::of(1, 1, 1, 0, self::OFFSET_PONE()), OffsetTime::of(3, 1, 1, 0, self::OFFSET_PTWO()), CU::HOURS(), 1],
            [OffsetTime::of(1, 1, 1, 0, self::OFFSET_PONE()), OffsetTime::of(3, 1, 1, 0, self::OFFSET_PTWO()), CU::MINUTES(), 60],
            [OffsetTime::of(1, 1, 1, 0, self::OFFSET_PONE()), OffsetTime::of(3, 1, 1, 0, self::OFFSET_PTWO()), CU::SECONDS(), 3600],
            [OffsetTime::of(1, 1, 1, 0, self::OFFSET_PONE()), OffsetTime::of(3, 1, 1, 0, self::OFFSET_PTWO()), CU::MILLIS(), 3600 * 1000],
            [OffsetTime::of(1, 1, 1, 0, self::OFFSET_PONE()), OffsetTime::of(3, 1, 1, 0, self::OFFSET_PTWO()), CU::MICROS(), 3600 * 1000 * 1000],
            [OffsetTime::of(1, 1, 1, 0, self::OFFSET_PONE()), OffsetTime::of(3, 1, 1, 0, self::OFFSET_PTWO()), CU::NANOS(), 3600 * 1000 * 1000 * 1000],
        ];
    }

    /**
     * @dataProvider data_untilUnit
     */
    public function test_until_TemporalUnit(OffsetTime $offsetTime1, OffsetTime $offsetTime2, TemporalUnit $unit, $expected)
    {
        $amount = $offsetTime1->until($offsetTime2, $unit);
        $this->assertEquals($amount, $expected);
    }

    /**
     * @dataProvider data_untilUnit
     */
    public function test_until_TemporalUnit_negated(OffsetTime $offsetTime1, OffsetTime $offsetTime2, TemporalUnit $unit, $expected)
    {
        $amount = $offsetTime2->until($offsetTime1, $unit);
        $this->assertEquals($amount, -$expected);
    }

    /**
     * @dataProvider data_untilUnit
     */
    public function test_until_TemporalUnit_between(OffsetTime $offsetTime1, OffsetTime $offsetTime2, TemporalUnit $unit, $expected)
    {
        $amount = $unit->between($offsetTime1, $offsetTime2);
        $this->assertEquals($amount, $expected);
    }


    public function test_until_convertedType()
    {
        $offsetTime = OffsetTime::of(1, 1, 1, 0, self::OFFSET_PONE());
        $offsetDateTime = $offsetTime->plusSeconds(3)->atDate(LocalDate::of(1980, 2, 10));
        $this->assertEquals($offsetTime->until($offsetDateTime, CU::SECONDS()), 3);
    }

    /**
     * @expectedException \Celest\DateTimeException
     */
    public function test_until_invalidType()
    {
        $offsetTime = OffsetTime::of(1, 1, 1, 0, self::OFFSET_PONE());
        $offsetTime->until(LocalDate::of(1980, 2, 10), CU::SECONDS());
    }

    /**
     * @expectedException \Celest\DateTimeException
     */
    public function test_until_invalidTemporalUnit()
    {
        $offsetTime1 = OffsetTime::of(1, 1, 1, 0, self::OFFSET_PONE());
        $offsetTime2 = OffsetTime::of(2, 1, 1, 0, self::OFFSET_PONE());
        $offsetTime1->until($offsetTime2, CU::MONTHS());
    }

//-----------------------------------------------------------------------
// format(DateTimeFormatter)
//-----------------------------------------------------------------------

    public function test_format_formatter()
    {
        $f = DateTimeFormatter::ofPattern("H m s");
        $t = OffsetTime::of(11, 30, 0, 0, self::OFFSET_PONE())->format($f);
        $this->assertEquals($t, "11 30 0");
    }

    public function test_format_formatter_null()
    {
        TestHelper::assertNullException($this, function () {
            OffsetTime::of(11, 30, 0, 0, self::OFFSET_PONE())->format(null);
        });
    }

//-----------------------------------------------------------------------
// compareTo()
//-----------------------------------------------------------------------

    public function test_compareTo_time()
    {
        $a = OffsetTime::of(11, 29, 0, 0, self::OFFSET_PONE());
        $b = OffsetTime::of(11, 30, 0, 0, self::OFFSET_PONE());  // a is before b due to time
        $this->assertEquals($a->compareTo($b) < 0, true);
        $this->assertEquals($b->compareTo($a) > 0, true);
        $this->assertEquals($a->compareTo($a) == 0, true);
        $this->assertEquals($b->compareTo($b) == 0, true);
        $this->assertEquals($this->convertInstant($a)->compareTo($this->convertInstant($b)) < 0, true);
    }


    public function test_compareTo_offset()
    {
        $a = OffsetTime::of(11, 30, 0, 0, self::OFFSET_PTWO());
        $b = OffsetTime::of(11, 30, 0, 0, self::OFFSET_PONE());  // a is before b due to offset
        $this->assertEquals($a->compareTo($b) < 0, true);
        $this->assertEquals($b->compareTo($a) > 0, true);
        $this->assertEquals($a->compareTo($a) == 0, true);
        $this->assertEquals($b->compareTo($b) == 0, true);
        $this->assertEquals($this->convertInstant($a)->compareTo($this->convertInstant($b)) < 0, true);
    }


    public function test_compareTo_both()
    {
        $a = OffsetTime::of(11, 50, 0, 0, self::OFFSET_PTWO());
        $b = OffsetTime::of(11, 20, 0, 0, self::OFFSET_PONE());  // a is before b on instant scale
        $this->assertEquals($a->compareTo($b) < 0, true);
        $this->assertEquals($b->compareTo($a) > 0, true);
        $this->assertEquals($a->compareTo($a) == 0, true);
        $this->assertEquals($b->compareTo($b) == 0, true);
        $this->assertEquals($this->convertInstant($a)->compareTo($this->convertInstant($b)) < 0, true);
    }


    public function test_compareTo_bothNearStartOfDay()
    {
        $a = OffsetTime::of(0, 10, 0, 0, self::OFFSET_PONE());
        $b = OffsetTime::of(2, 30, 0, 0, self::OFFSET_PTWO());  // a is before b on instant scale
        $this->assertEquals($a->compareTo($b) < 0, true);
        $this->assertEquals($b->compareTo($a) > 0, true);
        $this->assertEquals($a->compareTo($a) == 0, true);
        $this->assertEquals($b->compareTo($b) == 0, true);
        $this->assertEquals($this->convertInstant($a)->compareTo($this->convertInstant($b)) < 0, true);
    }


    public function test_compareTo_hourDifference()
    {
        $a = OffsetTime::of(10, 0, 0, 0, self::OFFSET_PONE());
        $b = OffsetTime::of(11, 0, 0, 0, self::OFFSET_PTWO());  // a is before b despite being same time-line time
        $this->assertEquals($a->compareTo($b) < 0, true);
        $this->assertEquals($b->compareTo($a) > 0, true);
        $this->assertEquals($a->compareTo($a) == 0, true);
        $this->assertEquals($b->compareTo($b) == 0, true);
        $this->assertEquals($this->convertInstant($a)->compareTo($this->convertInstant($b)) == 0, true);
    }

    public function test_compareTo_null()
    {
        TestHelper::assertNullException($this, function () {
            $a = OffsetTime::of(11, 30, 59, 0, self::OFFSET_PONE());
            $a->compareTo(null);
        });

    }

    public function test_compareToNonOffsetTime()
    {
        TestHelper::assertTypeError($this, function () {
            $c = self::TEST_11_30_59_500_PONE();
            $c->compareTo(new \stdClass());
        });
    }

    private function convertInstant(OffsetTime $ot)
    {
        return self::DATE()->atTime($ot->toLocalTime())->toInstant($ot->getOffset());
    }

//-----------------------------------------------------------------------
// isAfter() / isBefore() / isEqual()
//-----------------------------------------------------------------------

    public function test_isBeforeIsAfterIsEqual1()
    {
        $a = OffsetTime::of(11, 30, 58, 0, self::OFFSET_PONE());
        $b = OffsetTime::of(11, 30, 59, 0, self::OFFSET_PONE());  // a is before b due to time
        $this->assertEquals($a->isBefore($b), true);
        $this->assertEquals($a->isEqual($b), false);
        $this->assertEquals($a->isAfter($b), false);

        $this->assertEquals($b->isBefore($a), false);
        $this->assertEquals($b->isEqual($a), false);
        $this->assertEquals($b->isAfter($a), true);

        $this->assertEquals($a->isBefore($a), false);
        $this->assertEquals($b->isBefore($b), false);

        $this->assertEquals($a->isEqual($a), true);
        $this->assertEquals($b->isEqual($b), true);

        $this->assertEquals($a->isAfter($a), false);
        $this->assertEquals($b->isAfter($b), false);
    }


    public function test_isBeforeIsAfterIsEqual1nanos()
    {
        $a = OffsetTime::of(11, 30, 59, 3, self::OFFSET_PONE());
        $b = OffsetTime::of(11, 30, 59, 4, self::OFFSET_PONE());  // a is before b due to time
        $this->assertEquals($a->isBefore($b), true);
        $this->assertEquals($a->isEqual($b), false);
        $this->assertEquals($a->isAfter($b), false);

        $this->assertEquals($b->isBefore($a), false);
        $this->assertEquals($b->isEqual($a), false);
        $this->assertEquals($b->isAfter($a), true);

        $this->assertEquals($a->isBefore($a), false);
        $this->assertEquals($b->isBefore($b), false);

        $this->assertEquals($a->isEqual($a), true);
        $this->assertEquals($b->isEqual($b), true);

        $this->assertEquals($a->isAfter($a), false);
        $this->assertEquals($b->isAfter($b), false);
    }


    public function test_isBeforeIsAfterIsEqual2()
    {
        $a = OffsetTime::of(11, 30, 59, 0, self::OFFSET_PTWO());
        $b = OffsetTime::of(11, 30, 58, 0, self::OFFSET_PONE());  // a is before b due to offset
        $this->assertEquals($a->isBefore($b), true);
        $this->assertEquals($a->isEqual($b), false);
        $this->assertEquals($a->isAfter($b), false);

        $this->assertEquals($b->isBefore($a), false);
        $this->assertEquals($b->isEqual($a), false);
        $this->assertEquals($b->isAfter($a), true);

        $this->assertEquals($a->isBefore($a), false);
        $this->assertEquals($b->isBefore($b), false);

        $this->assertEquals($a->isEqual($a), true);
        $this->assertEquals($b->isEqual($b), true);

        $this->assertEquals($a->isAfter($a), false);
        $this->assertEquals($b->isAfter($b), false);
    }


    public function test_isBeforeIsAfterIsEqual2nanos()
    {
        $a = OffsetTime::of(11, 30, 59, 4, ZoneOffset::ofTotalSeconds(self::OFFSET_PONE()->getTotalSeconds() + 1));
        $b = OffsetTime::of(11, 30, 59, 3, self::OFFSET_PONE());  // a is before b due to offset
        $this->assertEquals($a->isBefore($b), true);
        $this->assertEquals($a->isEqual($b), false);
        $this->assertEquals($a->isAfter($b), false);

        $this->assertEquals($b->isBefore($a), false);
        $this->assertEquals($b->isEqual($a), false);
        $this->assertEquals($b->isAfter($a), true);

        $this->assertEquals($a->isBefore($a), false);
        $this->assertEquals($b->isBefore($b), false);

        $this->assertEquals($a->isEqual($a), true);
        $this->assertEquals($b->isEqual($b), true);

        $this->assertEquals($a->isAfter($a), false);
        $this->assertEquals($b->isAfter($b), false);
    }


    public function test_isBeforeIsAfterIsEqual_instantComparison()
    {
        $a = OffsetTime::of(11, 30, 59, 0, self::OFFSET_PTWO());
        $b = OffsetTime::of(10, 30, 59, 0, self::OFFSET_PONE());  // a is same instant as b
        $this->assertEquals($a->isBefore($b), false);
        $this->assertEquals($a->isEqual($b), true);
        $this->assertEquals($a->isAfter($b), false);

        $this->assertEquals($b->isBefore($a), false);
        $this->assertEquals($b->isEqual($a), true);
        $this->assertEquals($b->isAfter($a), false);

        $this->assertEquals($a->isBefore($a), false);
        $this->assertEquals($b->isBefore($b), false);

        $this->assertEquals($a->isEqual($a), true);
        $this->assertEquals($b->isEqual($b), true);

        $this->assertEquals($a->isAfter($a), false);
        $this->assertEquals($b->isAfter($b), false);
    }

    public function test_isBefore_null()
    {
        TestHelper::assertNullException($this, function () {
            $a = OffsetTime::of(11, 30, 59, 0, self::OFFSET_PONE());
            $a->isBefore(null);
        });

    }

    public function test_isAfter_null()
    {
        TestHelper::assertNullException($this, function () {
            $a = OffsetTime::of(11, 30, 59, 0, self::OFFSET_PONE());
            $a->isAfter(null);
        });

    }

    public function test_isEqual_null()
    {
        TestHelper::assertNullException($this, function () {
            $a = OffsetTime::of(11, 30, 59, 0, self::OFFSET_PONE());
            $a->isEqual(null);
        });

    }

//-----------------------------------------------------------------------
// equals() / hashCode()
//-----------------------------------------------------------------------
    /**
     * @dataProvider provider_sampleTimes
     */
    public function test_equals_true($h, $m, $s, $n, ZoneOffset $ignored)
    {
        $a = OffsetTime::of($h, $m, $s, $n, self::OFFSET_PONE());
        $b = OffsetTime::of($h, $m, $s, $n, self::OFFSET_PONE());
        $this->assertEquals($a->equals($b), true);
    }

    /**
     * @dataProvider provider_sampleTimes
     */
    public function test_equals_false_hour_differs($h, $m, $s, $n, ZoneOffset $ignored)
    {
        $h = ($h == 23 ? 22 : $h);
        $a = OffsetTime::of($h, $m, $s, $n, self::OFFSET_PONE());
        $b = OffsetTime::of($h + 1, $m, $s, $n, self::OFFSET_PONE());
        $this->assertEquals($a->equals($b), false);
    }

    /**
     * @dataProvider provider_sampleTimes
     */
    public function test_equals_false_minute_differs($h, $m, $s, $n, ZoneOffset $ignored)
    {
        $m = ($m == 59 ? 58 : $m);
        $a = OffsetTime::of($h, $m, $s, $n, self::OFFSET_PONE());
        $b = OffsetTime::of($h, $m + 1, $s, $n, self::OFFSET_PONE());
        $this->assertEquals($a->equals($b), false);
    }

    /**
     * @dataProvider provider_sampleTimes
     */
    public function test_equals_false_second_differs($h, $m, $s, $n, ZoneOffset $ignored)
    {
        $s = ($s == 59 ? 58 : $s);
        $a = OffsetTime::of($h, $m, $s, $n, self::OFFSET_PONE());
        $b = OffsetTime::of($h, $m, $s + 1, $n, self::OFFSET_PONE());
        $this->assertEquals($a->equals($b), false);
    }

    /**
     * @dataProvider provider_sampleTimes
     */
    public function test_equals_false_nano_differs($h, $m, $s, $n, ZoneOffset $ignored)
    {
        $n = ($n == 999999999 ? 999999998 : $n);
        $a = OffsetTime::of($h, $m, $s, $n, self::OFFSET_PONE());
        $b = OffsetTime::of($h, $m, $s, $n + 1, self::OFFSET_PONE());
        $this->assertEquals($a->equals($b), false);
    }

    /**
     * @dataProvider provider_sampleTimes
     */
    public function test_equals_false_offset_differs($h, $m, $s, $n, ZoneOffset $ignored)
    {
        $a = OffsetTime::of($h, $m, $s, $n, self::OFFSET_PONE());
        $b = OffsetTime::of($h, $m, $s, $n, self::OFFSET_PTWO());
        $this->assertEquals($a->equals($b), false);
    }


    public function test_equals_itself_true()
    {
        $this->assertEquals(self::TEST_11_30_59_500_PONE()->equals(self::TEST_11_30_59_500_PONE()), true);
    }


    public function test_equals_string_false()
    {
        $this->assertEquals(self::TEST_11_30_59_500_PONE()->equals("2007-07-15"), false);
    }


    public function test_equals_null_false()
    {
        $this->assertEquals(self::TEST_11_30_59_500_PONE()->equals(null), false);
    }

//-----------------------------------------------------------------------
// toString()
//-----------------------------------------------------------------------
    function provider_sampleToString()
    {
        return [
            [11, 30, 59, 0, "Z", "11:30:59Z"],
            [11, 30, 59, 0, "+01:00", "11:30:59+01:00"],
            [11, 30, 59, 999000000, "Z", "11:30:59.999Z"],
            [11, 30, 59, 999000000, "+01:00", "11:30:59.999+01:00"],
            [11, 30, 59, 999000, "Z", "11:30:59.000999Z"],
            [11, 30, 59, 999000, "+01:00", "11:30:59.000999+01:00"],
            [11, 30, 59, 999, "Z", "11:30:59.000000999Z"],
            [11, 30, 59, 999, "+01:00", "11:30:59.000000999+01:00"],
        ];
    }

    /**
     * @dataProvider provider_sampleToString
     */
    public function test_toString($h, $m, $s, $n, $offsetId, $expected)
    {
        $t = OffsetTime::of($h, $m, $s, $n, ZoneOffset::of($offsetId));
        $str = $t->__toString();
        $this->assertEquals($str, $expected);
    }

    /**
     * @dataProvider provider_sampleToString
     */
    public function test_jsonSerializable($h, $m, $s, $n, $offsetId, $expected)
    {
        $t = OffsetTime::of($h, $m, $s, $n, ZoneOffset::of($offsetId));
        $this->assertEquals(json_decode(json_encode($t)), $expected);
    }

}
