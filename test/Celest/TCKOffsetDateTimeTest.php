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

use Celest\Chrono\IsoChronology;
use Celest\Format\DateTimeFormatter;
use Celest\Helper\Math;
use Celest\Temporal\ChronoField as CF;
use Celest\Temporal\ChronoUnit as CU;
use Celest\Temporal\JulianFields;
use Celest\Temporal\Temporal;
use Celest\Temporal\TemporalAccessor;
use Celest\Temporal\TemporalField;
use Celest\Temporal\TemporalQueries;
use Celest\Temporal\TemporalQuery;
use Celest\Temporal\TemporalUnit;
use Exception;

/**
 * Test OffsetDateTime.
 */
class TCKOffsetDateTimeTest extends AbstractDateTimeTest
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

    private static function OFFSET_MONE()
    {
        return ZoneOffset::ofHours(-1);
    }

    private static function OFFSET_MTWO()
    {
        return ZoneOffset::ofHours(-2);
    }

    private static function TEST_2008_6_30_11_30_59_000000500()
    {
        return OffsetDateTime::of(2008, 6, 30, 11, 30, 59, 500, self::OFFSET_PONE());
    }

    //-----------------------------------------------------------------------
    protected function samples()
    {
        return [self::TEST_2008_6_30_11_30_59_000000500(), OffsetDateTime::MIN(), OffsetDateTime::MAX()];
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
//List<TemporalField> list = new ArrayList<>(Arrays.<TemporalField>asList(CF::values())); TODO
//        list.removeAll(validFields());
//        return list;
        return [];
    }

    //-----------------------------------------------------------------------
    // constants
    //-----------------------------------------------------------------------

    public function test_constant_MIN()
    {
        $this->check(OffsetDateTime::MIN(), Year::MIN_VALUE, 1, 1, 0, 0, 0, 0, ZoneOffset::MAX());
    }


    public function test_constant_MAX()
    {
        $this->check(OffsetDateTime::MAX(), Year::MAX_VALUE, 12, 31, 23, 59, 59, 999999999, ZoneOffset::MIN());
    }

    //-----------------------------------------------------------------------
    // now()
    //-----------------------------------------------------------------------

    public function test_now()
    {
        $expected = OffsetDateTime::nowOf(Clock::systemDefaultZone());
        $test = OffsetDateTime::now();
        $diff = Math::abs($test->toLocalTime()->toNanoOfDay() - $expected->toLocalTime()->toNanoOfDay());
        if ($diff >= 100000000) {
            // may be date change
            $expected = OffsetDateTime::nowOf(Clock::systemDefaultZone());
            $test = OffsetDateTime::now();
            $diff = Math::abs($test->toLocalTime()->toNanoOfDay() - $expected->toLocalTime()->toNanoOfDay());
        }
        $this->assertTrue($diff < 100000000);  // less than 0.1 secs
    }

    //-----------------------------------------------------------------------
    // now(Clock)
    //-----------------------------------------------------------------------

    /**
     * @group long
     */
    public function test_now_Clock_allSecsInDay_utc()
    {
        for ($i = 0; $i < (2 * 24 * 60 * 60); $i++) {
            $instant = Instant::ofEpochSecond($i)->plusNanos(123456789);
            $clock = Clock::fixed($instant, ZoneOffset::UTC());
            $test = OffsetDateTime::nowOf($clock);
            $this->assertEquals($test->getYear(), 1970);
            $this->assertEquals($test->getMonth(), Month::JANUARY());
            $this->assertEquals($test->getDayOfMonth(), ($i < 24 * 60 * 60 ? 1 : 2));
            $this->assertEquals($test->getHour(), ($i / (60 * 60)) % 24);
            $this->assertEquals($test->getMinute(), ($i / 60) % 60);
            $this->assertEquals($test->getSecond(), $i % 60);
            $this->assertEquals($test->getNano(), 123456789);
            $this->assertEquals($test->getOffset(), ZoneOffset::UTC());
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
            $test = OffsetDateTime::nowOf($clock);
            $this->assertEquals($test->getYear(), 1970);
            $this->assertEquals($test->getMonth(), Month::JANUARY());
            $this->assertEquals($test->getDayOfMonth(), ($i < 24 * 60 * 60) ? 1 : 2);
            $this->assertEquals($test->getHour(), ($i / (60 * 60)) % 24);
            $this->assertEquals($test->getMinute(), ($i / 60) % 60);
            $this->assertEquals($test->getSecond(), $i % 60);
            $this->assertEquals($test->getNano(), 123456789);
            $this->assertEquals($test->getOffset(), self::OFFSET_PONE());
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
            $test = OffsetDateTime::nowOf($clock);
            $this->assertEquals($test->getYear(), 1969);
            $this->assertEquals($test->getMonth(), Month::DECEMBER());
            $this->assertEquals($test->getDayOfMonth(), 31);
            $expected = $expected->minusSeconds(1);
            $this->assertEquals($test->toLocalTime(), $expected);
            $this->assertEquals($test->getOffset(), ZoneOffset::UTC());
        }
    }


    public function test_now_Clock_offsets()
    {
        $base = OffsetDateTime::of(1970, 1, 1, 12, 0, 0, 0, ZoneOffset::UTC());
        for ($i = -9; $i < 15; $i++) {
            $offset = ZoneOffset::ofHours($i);
            $clock = Clock::fixed($base->toInstant(), $offset);
            $test = OffsetDateTime::nowOf($clock);
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
            OffsetDateTime::nowIn(null);
        });
    }

    public function test_now_Clock_nullClock()
    {
        TestHelper::assertNullException($this, function () {
            OffsetDateTime::nowOf(null);
        });
    }

    //-----------------------------------------------------------------------
    private function check(OffsetDateTime $test, $y, $mo, $d, $h, $m, $s, $n, ZoneOffset $offset)
    {
        $this->assertEquals($test->getYear(), $y);
        $this->assertEquals($test->getMonth()->getValue(), $mo);
        $this->assertEquals($test->getDayOfMonth(), $d);
        $this->assertEquals($test->getHour(), $h);
        $this->assertEquals($test->getMinute(), $m);
        $this->assertEquals($test->getSecond(), $s);
        $this->assertEquals($test->getNano(), $n);
        $this->assertEquals($test->getOffset(), $offset);
        $this->assertEquals($test, $test);
        $this->assertEquals(OffsetDateTime::ofDateTime(LocalDateTime::of($y, $mo, $d, $h, $m, $s, $n), $offset), $test);
    }

    //-----------------------------------------------------------------------
    // factories
    //-----------------------------------------------------------------------

    public function test_factory_of_intsHMSN()
    {
        $test = OffsetDateTime::of(2008, 6, 30, 11, 30, 10, 500, self::OFFSET_PONE());
        $this->check($test, 2008, 6, 30, 11, 30, 10, 500, self::OFFSET_PONE());
    }

    //-----------------------------------------------------------------------

    public function test_factory_of_LocalDateLocalTimeZoneOffset()
    {
        $date = LocalDate::of(2008, 6, 30);
        $time = LocalTime::of(11, 30, 10, 500);
        $test = OffsetDateTime::ofDateAndTime($date, $time, self::OFFSET_PONE());
        $this->check($test, 2008, 6, 30, 11, 30, 10, 500, self::OFFSET_PONE());
    }

    public function test_factory_of_LocalDateLocalTimeZoneOffset_nullLocalDate()
    {
        TestHelper::assertNullException($this, function () {
            $time = LocalTime::of(11, 30, 10, 500);
            OffsetDateTime::ofDateAndTime(null, $time, self::OFFSET_PONE());
        });

    }

    public function test_factory_of_LocalDateLocalTimeZoneOffset_nullLocalTime()
    {
        TestHelper::assertNullException($this, function () {
            $date = LocalDate::of(2008, 6, 30);
            OffsetDateTime::ofDateAndTime($date, null, self::OFFSET_PONE());
        });

    }

    public function test_factory_of_LocalDateLocalTimeZoneOffset_nullOffset()
    {
        TestHelper::assertNullException($this, function () {
            $date = LocalDate::of(2008, 6, 30);
            $time = LocalTime::of(11, 30, 10, 500);
            OffsetDateTime::ofDateAndTime($date, $time, null);
        });

    }

    //-----------------------------------------------------------------------

    public function test_factory_of_LocalDateTimeZoneOffset()
    {
        $dt = LocalDateTime::ofDateAndTime(LocalDate::of(2008, 6, 30), LocalTime::of(11, 30, 10, 500));
        $test = OffsetDateTime::ofDateTime($dt, self::OFFSET_PONE());
        $this->check($test, 2008, 6, 30, 11, 30, 10, 500, self::OFFSET_PONE());
    }

    public function test_factory_of_LocalDateTimeZoneOffset_nullProvider()
    {
        TestHelper::assertNullException($this, function () {
            OffsetDateTime::ofDateTime(null, self::OFFSET_PONE());
        });
    }

    public function test_factory_of_LocalDateTimeZoneOffset_nullOffset()
    {
        TestHelper::assertNullException($this, function () {
            $dt = LocalDateTime::ofDateAndTime(LocalDate::of(2008, 6, 30), LocalTime::of(11, 30, 10, 500));
            OffsetDateTime::ofDateTime($dt, null);
        });

    }

    //-----------------------------------------------------------------------
    // from()
    //-----------------------------------------------------------------------

    public function test_factory_CalendricalObject()
    {
        $this->assertEquals(OffsetDateTime::from(
            OffsetDateTime::ofDateAndTime(LocalDate::of(2007, 7, 15), LocalTime::of(17, 30), self::OFFSET_PONE())),
            OffsetDateTime::ofDateAndTime(LocalDate::of(2007, 7, 15), LocalTime::of(17, 30), self::OFFSET_PONE()));
    }

    /**
     * @expectedException \Celest\DateTimeException
     */
    public function test_factory_CalendricalObject_invalid_noDerive()
    {
        OffsetDateTime::from(LocalTime::of(12, 30));
    }

    public function test_factory_Calendricals_null()
    {
        TestHelper::assertNullException($this, function () {
            OffsetDateTime::from(null);
        });
    }

    //-----------------------------------------------------------------------
    // parse()
    //-----------------------------------------------------------------------
    /**
     * @dataProvider provider_sampleToString()
     */
    public function test_parse($y, $month, $d, $h, $m, $s, $n, $offsetId, $text)
    {
        $t = OffsetDateTime::parse($text);
        $this->assertEquals($t->getYear(), $y);
        $this->assertEquals($t->getMonth()->getValue(), $month);
        $this->assertEquals($t->getDayOfMonth(), $d);
        $this->assertEquals($t->getHour(), $h);
        $this->assertEquals($t->getMinute(), $m);
        $this->assertEquals($t->getSecond(), $s);
        $this->assertEquals($t->getNano(), $n);
        $this->assertEquals($t->getOffset()->getId(), $offsetId);
    }

    /**
     * @expectedException \Celest\DateTimeParseException
     */
    public function test_factory_parse_illegalValue()
    {
        OffsetDateTime::parse("2008-06-32T11:15+01:00");
    }

    /**
     * @expectedException \Celest\DateTimeParseException
     */
    public function test_factory_parse_invalidValue()
    {
        OffsetDateTime::parse("2008-06-31T11:15+01:00");
    }

    public function test_factory_parse_nullText()
    {
        TestHelper::assertNullException($this, function () {
            OffsetDateTime::parse(null);
        });
    }

    //-----------------------------------------------------------------------
    // parse(DateTimeFormatter)
    //-----------------------------------------------------------------------

    public function test_factory_parse_formatter()
    {
        $f = DateTimeFormatter::ofPattern("y M d H m s XXX");
        $test = OffsetDateTime::parseWith("2010 12 3 11 30 0 +01:00", $f);
        $this->assertEquals($test, OffsetDateTime::ofDateAndTime(LocalDate::of(2010, 12, 3), LocalTime::of(11, 30), ZoneOffset::ofHours(1)));
    }

    public function test_factory_parse_formatter_nullText()
    {
        TestHelper::assertNullException($this, function () {
            $f = DateTimeFormatter::ofPattern("y M d H m s");
            OffsetDateTime::parseWith(null, $f);
        });

    }

    public function test_factory_parse_formatter_nullFormatter()
    {
        TestHelper::assertNullException($this, function () {
            OffsetDateTime::parseWith("ANY", null);
        });
    }

    //-----------------------------------------------------------------------
    /*(expectedExceptions = NullPointerException.class) TODO
        public function test_constructor_nullTime()
        {
            Constructor < OffsetDateTime> con = OffsetDateTime::class . getDeclaredConstructor(LocalDateTime::class, ZoneOffset::class);
    con . setAccessible(true);
    try {
        con . newInstance(null, self::OFFSET_PONE());
    } catch
    (InvocationTargetException $ex) {
        throw $ex->getCause();
    }
        }
    
    (expectedExceptions = NullPointerException .class)
    public function test_constructor_nullOffset()
    {
    Constructor < OffsetDateTime> con = OffsetDateTime::class->getDeclaredConstructor(LocalDateTime::class, ZoneOffset::class);
    con->setAccessible(true);
    try
    {
    con->newInstance(LocalDateTime::of(LocalDate::of(2008, 6, 30), LocalTime::of(11, 30)), null);
    }
    
    catch
    (InvocationTargetException $ex) {
        throw $ex->getCause();
    }
        }*/

    //-----------------------------------------------------------------------
    // basics
    //-----------------------------------------------------------------------
    function provider_sampleTimes()
    {
        return [
            [2008, 6, 30, 11, 30, 20, 500, self::OFFSET_PONE()],
            [2008, 6, 30, 11, 0, 0, 0, self::OFFSET_PONE()],
            [2008, 6, 30, 23, 59, 59, 999999999, self::OFFSET_PONE()],
            [-1, 1, 1, 0, 0, 0, 0, self::OFFSET_PONE()],
        ];
    }

    /**
     * @dataProvider provider_sampleTimes
     */
    public function test_get($y, $o, $d, $h, $m, $s, $n, ZoneOffset $offset)
    {
        $localDate = LocalDate::of($y, $o, $d);
        $localTime = LocalTime::of($h, $m, $s, $n);
        $localDateTime = LocalDateTime::ofDateAndTime($localDate, $localTime);
        $a = OffsetDateTime::ofDateTime($localDateTime, $offset);

        $this->assertEquals($a->getYear(), $localDate->getYear());
        $this->assertEquals($a->getMonth(), $localDate->getMonth());
        $this->assertEquals($a->getDayOfMonth(), $localDate->getDayOfMonth());
        $this->assertEquals($a->getDayOfYear(), $localDate->getDayOfYear());
        $this->assertEquals($a->getDayOfWeek(), $localDate->getDayOfWeek());

        $this->assertEquals($a->getHour(), $localDateTime->getHour());
        $this->assertEquals($a->getMinute(), $localDateTime->getMinute());
        $this->assertEquals($a->getSecond(), $localDateTime->getSecond());
        $this->assertEquals($a->getNano(), $localDateTime->getNano());

        $this->assertEquals($a->toOffsetTime(), OffsetTime::ofLocalTime($localTime, $offset));
        $this->assertEquals($a->__toString(), $localDateTime->__toString() . $offset->__toString());
    }

    //-----------------------------------------------------------------------
    // isSupported(TemporalField)
    //-----------------------------------------------------------------------

    public function test_isSupported_TemporalField()
    {
        //$this->assertEquals(self::TEST_2008_6_30_11_30_59_000000500()->isSupported((TemporalField) null), false); TODO
        $this->assertEquals(self::TEST_2008_6_30_11_30_59_000000500()->isSupported(CF::NANO_OF_SECOND()), true);
        $this->assertEquals(self::TEST_2008_6_30_11_30_59_000000500()->isSupported(CF::NANO_OF_DAY()), true);
        $this->assertEquals(self::TEST_2008_6_30_11_30_59_000000500()->isSupported(CF::MICRO_OF_SECOND()), true);
        $this->assertEquals(self::TEST_2008_6_30_11_30_59_000000500()->isSupported(CF::MICRO_OF_DAY()), true);
        $this->assertEquals(self::TEST_2008_6_30_11_30_59_000000500()->isSupported(CF::MILLI_OF_SECOND()), true);
        $this->assertEquals(self::TEST_2008_6_30_11_30_59_000000500()->isSupported(CF::MILLI_OF_DAY()), true);
        $this->assertEquals(self::TEST_2008_6_30_11_30_59_000000500()->isSupported(CF::SECOND_OF_MINUTE()), true);
        $this->assertEquals(self::TEST_2008_6_30_11_30_59_000000500()->isSupported(CF::SECOND_OF_DAY()), true);
        $this->assertEquals(self::TEST_2008_6_30_11_30_59_000000500()->isSupported(CF::MINUTE_OF_HOUR()), true);
        $this->assertEquals(self::TEST_2008_6_30_11_30_59_000000500()->isSupported(CF::MINUTE_OF_DAY()), true);
        $this->assertEquals(self::TEST_2008_6_30_11_30_59_000000500()->isSupported(CF::HOUR_OF_AMPM()), true);
        $this->assertEquals(self::TEST_2008_6_30_11_30_59_000000500()->isSupported(CF::CLOCK_HOUR_OF_AMPM()), true);
        $this->assertEquals(self::TEST_2008_6_30_11_30_59_000000500()->isSupported(CF::HOUR_OF_DAY()), true);
        $this->assertEquals(self::TEST_2008_6_30_11_30_59_000000500()->isSupported(CF::CLOCK_HOUR_OF_DAY()), true);
        $this->assertEquals(self::TEST_2008_6_30_11_30_59_000000500()->isSupported(CF::AMPM_OF_DAY()), true);
        $this->assertEquals(self::TEST_2008_6_30_11_30_59_000000500()->isSupported(CF::DAY_OF_WEEK()), true);
        $this->assertEquals(self::TEST_2008_6_30_11_30_59_000000500()->isSupported(CF::ALIGNED_DAY_OF_WEEK_IN_MONTH()), true);
        $this->assertEquals(self::TEST_2008_6_30_11_30_59_000000500()->isSupported(CF::ALIGNED_DAY_OF_WEEK_IN_YEAR()), true);
        $this->assertEquals(self::TEST_2008_6_30_11_30_59_000000500()->isSupported(CF::DAY_OF_MONTH()), true);
        $this->assertEquals(self::TEST_2008_6_30_11_30_59_000000500()->isSupported(CF::DAY_OF_YEAR()), true);
        $this->assertEquals(self::TEST_2008_6_30_11_30_59_000000500()->isSupported(CF::EPOCH_DAY()), true);
        $this->assertEquals(self::TEST_2008_6_30_11_30_59_000000500()->isSupported(CF::ALIGNED_WEEK_OF_MONTH()), true);
        $this->assertEquals(self::TEST_2008_6_30_11_30_59_000000500()->isSupported(CF::ALIGNED_WEEK_OF_YEAR()), true);
        $this->assertEquals(self::TEST_2008_6_30_11_30_59_000000500()->isSupported(CF::MONTH_OF_YEAR()), true);
        $this->assertEquals(self::TEST_2008_6_30_11_30_59_000000500()->isSupported(CF::PROLEPTIC_MONTH()), true);
        $this->assertEquals(self::TEST_2008_6_30_11_30_59_000000500()->isSupported(CF::YEAR()), true);
        $this->assertEquals(self::TEST_2008_6_30_11_30_59_000000500()->isSupported(CF::YEAR_OF_ERA()), true);
        $this->assertEquals(self::TEST_2008_6_30_11_30_59_000000500()->isSupported(CF::ERA()), true);
        $this->assertEquals(self::TEST_2008_6_30_11_30_59_000000500()->isSupported(CF::INSTANT_SECONDS()), true);
        $this->assertEquals(self::TEST_2008_6_30_11_30_59_000000500()->isSupported(CF::OFFSET_SECONDS()), true);
    }

    //-----------------------------------------------------------------------
    // isSupported(TemporalUnit)
    //-----------------------------------------------------------------------

    public function test_isSupported_TemporalUnit()
    {
        //$this->assertEquals(self::TEST_2008_6_30_11_30_59_000000500()->isSupported((TemporalUnit) null), false); TODO
        $this->assertEquals(self::TEST_2008_6_30_11_30_59_000000500()->isUnitSupported(CU::NANOS()), true);
        $this->assertEquals(self::TEST_2008_6_30_11_30_59_000000500()->isUnitSupported(CU::MICROS()), true);
        $this->assertEquals(self::TEST_2008_6_30_11_30_59_000000500()->isUnitSupported(CU::MILLIS()), true);
        $this->assertEquals(self::TEST_2008_6_30_11_30_59_000000500()->isUnitSupported(CU::SECONDS()), true);
        $this->assertEquals(self::TEST_2008_6_30_11_30_59_000000500()->isUnitSupported(CU::MINUTES()), true);
        $this->assertEquals(self::TEST_2008_6_30_11_30_59_000000500()->isUnitSupported(CU::HOURS()), true);
        $this->assertEquals(self::TEST_2008_6_30_11_30_59_000000500()->isUnitSupported(CU::HALF_DAYS()), true);
        $this->assertEquals(self::TEST_2008_6_30_11_30_59_000000500()->isUnitSupported(CU::DAYS()), true);
        $this->assertEquals(self::TEST_2008_6_30_11_30_59_000000500()->isUnitSupported(CU::WEEKS()), true);
        $this->assertEquals(self::TEST_2008_6_30_11_30_59_000000500()->isUnitSupported(CU::MONTHS()), true);
        $this->assertEquals(self::TEST_2008_6_30_11_30_59_000000500()->isUnitSupported(CU::YEARS()), true);
        $this->assertEquals(self::TEST_2008_6_30_11_30_59_000000500()->isUnitSupported(CU::DECADES()), true);
        $this->assertEquals(self::TEST_2008_6_30_11_30_59_000000500()->isUnitSupported(CU::CENTURIES()), true);
        $this->assertEquals(self::TEST_2008_6_30_11_30_59_000000500()->isUnitSupported(CU::MILLENNIA()), true);
        $this->assertEquals(self::TEST_2008_6_30_11_30_59_000000500()->isUnitSupported(CU::ERAS()), true);
        $this->assertEquals(self::TEST_2008_6_30_11_30_59_000000500()->isUnitSupported(CU::FOREVER()), false);
    }

    //-----------------------------------------------------------------------
    // get(TemporalField)
    //-----------------------------------------------------------------------

    public function test_get_TemporalField()
    {
        $test = OffsetDateTime::ofDateAndTime(LocalDate::of(2008, 6, 30), LocalTime::of(12, 30, 40, 987654321), self::OFFSET_PONE());
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
        $test = OffsetDateTime::ofDateAndTime(LocalDate::of(2008, 6, 30), LocalTime::of(12, 30, 40, 987654321), self::OFFSET_PONE());
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

        $this->assertEquals($test->getLong(CF::INSTANT_SECONDS()), $test->toEpochSecond());
        $this->assertEquals($test->getLong(CF::OFFSET_SECONDS()), 3600);
    }

    //-----------------------------------------------------------------------
    // query(TemporalQuery)
    //-----------------------------------------------------------------------
    function data_query()
    {
        return [
            [self::TEST_2008_6_30_11_30_59_000000500(), TemporalQueries::chronology(), IsoChronology::INSTANCE()],
            [self::TEST_2008_6_30_11_30_59_000000500(), TemporalQueries::zoneId(), null],
            [self::TEST_2008_6_30_11_30_59_000000500(), TemporalQueries::precision(), CU::NANOS()],
            [self::TEST_2008_6_30_11_30_59_000000500(), TemporalQueries::zone(), self::OFFSET_PONE()],
            [self::TEST_2008_6_30_11_30_59_000000500(), TemporalQueries::offset(), self::OFFSET_PONE()],
            [self::TEST_2008_6_30_11_30_59_000000500(), TemporalQueries::localDate(), LocalDate::of(2008, 6, 30)],
            [self::TEST_2008_6_30_11_30_59_000000500(), TemporalQueries::localTime(), LocalTime::of(11, 30, 59, 500)],
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
            self::TEST_2008_6_30_11_30_59_000000500()->query(null);
        });
    }

    //-----------------------------------------------------------------------
    // adjustInto(Temporal)
    //-----------------------------------------------------------------------
    function data_adjustInto()
    {
        return [
            [OffsetDateTime::of(2012, 3, 4, 23, 5, 0, 0, self::OFFSET_PONE()), OffsetDateTime::of(2012, 3, 4, 1, 1, 1, 100, ZoneOffset::UTC()), OffsetDateTime::of(2012, 3, 4, 23, 5, 0, 0, self::OFFSET_PONE()), null],
            [OffsetDateTime::of(2012, 3, 4, 23, 5, 0, 0, self::OFFSET_PONE()), OffsetDateTime::MAX(), OffsetDateTime::of(2012, 3, 4, 23, 5, 0, 0, self::OFFSET_PONE()), null],
            [OffsetDateTime::of(2012, 3, 4, 23, 5, 0, 0, self::OFFSET_PONE()), OffsetDateTime::MIN(), OffsetDateTime::of(2012, 3, 4, 23, 5, 0, 0, self::OFFSET_PONE()), null],
            [OffsetDateTime::MAX(), OffsetDateTime::of(2012, 3, 4, 23, 5, 0, 0, self::OFFSET_PONE()), OffsetDateTime::ofDateTime(OffsetDateTime::MAX()->toLocalDateTime(), ZoneOffset::ofHours(-18)), null],
            [OffsetDateTime::MIN(), OffsetDateTime::of(2012, 3, 4, 23, 5, 0, 0, self::OFFSET_PONE()), OffsetDateTime::ofDateTime(OffsetDateTime::MIN()->toLocalDateTime(), ZoneOffset::ofHours(18)), null],

            [OffsetDateTime::of(2012, 3, 4, 23, 5, 0, 0, self::OFFSET_PONE()), ZonedDateTime::of(2012, 3, 4, 1, 1, 1, 100, self::ZONE_GAZA()), ZonedDateTime::of(2012, 3, 4, 23, 5, 0, 0, self::ZONE_GAZA()), null],

            [OffsetDateTime::of(2012, 3, 4, 23, 5, 0, 0, self::OFFSET_PONE()), LocalDateTime::of(2012, 3, 4, 1, 1, 1, 100), null, DateTimeException::class],
            [OffsetDateTime::of(2012, 3, 4, 23, 5, 0, 0, self::OFFSET_PONE()), LocalDate::of(2210, 2, 2), null, DateTimeException::class],
            [OffsetDateTime::of(2012, 3, 4, 23, 5, 0, 0, self::OFFSET_PONE()), LocalTime::of(22, 3, 0), null, DateTimeException::class],
            [OffsetDateTime::of(2012, 3, 4, 23, 5, 0, 0, self::OFFSET_PONE()), OffsetTime::of(22, 3, 0, 0, ZoneOffset::UTC()), null, DateTimeException::class],
            //[OffsetDateTime::of(2012, 3, 4, 23, 5, 0, 0, self::OFFSET_PONE()), null, null, NullPointerException::class], TODO
        ];
    }

    /**
     * @dataProvider data_adjustInto
     */
    public function test_adjustInto(OffsetDateTime $test, Temporal $temporal, $expected, $expectedEx)
    {
        if ($expectedEx == null) {
            $result = $test->adjustInto($temporal);
            $this->assertEquals($result, $expected);
        } else {
            try {
                $test->adjustInto($temporal);
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
        $this->markTestIncomplete();
        /*$sample = OffsetDateTime::ofDateAndTime(LocalDate::of(2012, 3, 4), LocalTime::of(23, 5), self::OFFSET_PONE());
        $adjuster = new TemporalAdjuster() {
        @Override
    public Temporal adjustInto(Temporal $dateTime) {
            return $sample;
        }
    };
        $this->assertEquals(self::TEST_2008_6_30_11_30_59_000000500()->adjust($adjuster), $sample);*/
    }


    public function test_with_adjustment_LocalDate()
    {
        $test = self::TEST_2008_6_30_11_30_59_000000500()->adjust(LocalDate::of(2012, 9, 3));
        $this->assertEquals($test, OffsetDateTime::ofDateAndTime(LocalDate::of(2012, 9, 3), LocalTime::of(11, 30, 59, 500), self::OFFSET_PONE()));
    }


    public function test_with_adjustment_LocalTime()
    {
        $test = self::TEST_2008_6_30_11_30_59_000000500()->adjust(LocalTime::of(19, 15));
        $this->assertEquals($test, OffsetDateTime::ofDateAndTime(LocalDate::of(2008, 6, 30), LocalTime::of(19, 15), self::OFFSET_PONE()));
    }


    public function test_with_adjustment_LocalDateTime()
    {
        $test = self::TEST_2008_6_30_11_30_59_000000500()->adjust(LocalDateTime::ofDateAndTime(LocalDate::of(2012, 9, 3), LocalTime::of(19, 15)));
        $this->assertEquals($test, OffsetDateTime::ofDateAndTime(LocalDate::of(2012, 9, 3), LocalTime::of(19, 15), self::OFFSET_PONE()));
    }


    public function test_with_adjustment_OffsetTime()
    {
        $test = self::TEST_2008_6_30_11_30_59_000000500()->adjust(OffsetTime::ofLocalTime(LocalTime::of(19, 15), self::OFFSET_PTWO()));
        $this->assertEquals($test, OffsetDateTime::ofDateAndTime(LocalDate::of(2008, 6, 30), LocalTime::of(19, 15), self::OFFSET_PTWO()));
    }


    public function test_with_adjustment_OffsetDateTime()
    {
        $test = self::TEST_2008_6_30_11_30_59_000000500()->adjust(OffsetDateTime::ofDateAndTime(LocalDate::of(2012, 9, 3), LocalTime::of(19, 15), self::OFFSET_PTWO()));
        $this->assertEquals($test, OffsetDateTime::ofDateAndTime(LocalDate::of(2012, 9, 3), LocalTime::of(19, 15), self::OFFSET_PTWO()));
    }


    public function test_with_adjustment_Month()
    {
        $test = self::TEST_2008_6_30_11_30_59_000000500()->adjust(Month::DECEMBER());
        $this->assertEquals($test, OffsetDateTime::ofDateAndTime(LocalDate::of(2008, 12, 30), LocalTime::of(11, 30, 59, 500), self::OFFSET_PONE()));
    }


    public function test_with_adjustment_ZoneOffset()
    {
        $test = self::TEST_2008_6_30_11_30_59_000000500()->adjust(self::OFFSET_PTWO());
        $this->assertEquals($test, OffsetDateTime::ofDateAndTime(LocalDate::of(2008, 6, 30), LocalTime::of(11, 30, 59, 500), self::OFFSET_PTWO()));
    }

    public function test_with_adjustment_null()
    {
        TestHelper::assertNullException($this, function () {
            self::TEST_2008_6_30_11_30_59_000000500()->adjust(null);
        });
    }

    public function test_withOffsetSameLocal_null()
    {
        TestHelper::assertNullException($this, function () {
            $base = OffsetDateTime::ofDateAndTime(LocalDate::of(2008, 6, 30), LocalTime::of(11, 30, 59), self::OFFSET_PONE());
            $base->withOffsetSameLocal(null);
        });


    }

//-----------------------------------------------------------------------
// withOffsetSameInstant()
//-----------------------------------------------------------------------

    public function test_withOffsetSameInstant()
    {
        $base = OffsetDateTime::ofDateAndTime(LocalDate::of(2008, 6, 30), LocalTime::of(11, 30, 59), self::OFFSET_PONE());
        $test = $base->withOffsetSameInstant(self::OFFSET_PTWO());
        $expected = OffsetDateTime::ofDateAndTime(LocalDate::of(2008, 6, 30), LocalTime::of(12, 30, 59), self::OFFSET_PTWO());
        $this->assertEquals($test, $expected);
    }

    public function test_withOffsetSameInstant_null()
    {
        TestHelper::assertNullException($this, function () {
            $base = OffsetDateTime::ofDateAndTime(LocalDate::of(2008, 6, 30), LocalTime::of(11, 30, 59), self::OFFSET_PONE());
            $base->withOffsetSameInstant(null);
        });

    }

//-----------------------------------------------------------------------
// with(long,TemporalUnit)
//-----------------------------------------------------------------------
    function data_withFieldLong()
    {
        return [
            [self::TEST_2008_6_30_11_30_59_000000500(), CF::YEAR(), 2009,
                OffsetDateTime::of(2009, 6, 30, 11, 30, 59, 500, self::OFFSET_PONE())],
            [self::TEST_2008_6_30_11_30_59_000000500(), CF::MONTH_OF_YEAR(), 7,
                OffsetDateTime::of(2008, 7, 30, 11, 30, 59, 500, self::OFFSET_PONE())],
            [self::TEST_2008_6_30_11_30_59_000000500(), CF::DAY_OF_MONTH(), 15,
                OffsetDateTime::of(2008, 6, 15, 11, 30, 59, 500, self::OFFSET_PONE())],
            [self::TEST_2008_6_30_11_30_59_000000500(), CF::HOUR_OF_DAY(), 14,
                OffsetDateTime::of(2008, 6, 30, 14, 30, 59, 500, self::OFFSET_PONE())],
            [self::TEST_2008_6_30_11_30_59_000000500(), CF::OFFSET_SECONDS(), -3600,
                OffsetDateTime::of(2008, 6, 30, 11, 30, 59, 500, self::OFFSET_MONE())],
        ];
    }

    /**
     * @dataProvider data_withFieldLong
     */
    public function test_with_fieldLong(OffsetDateTime $base, TemporalField $setField, $setValue, OffsetDateTime $expected)
    {
        $this->assertEquals($base->with($setField, $setValue), $expected);
    }

//-----------------------------------------------------------------------
// withYear()
//-----------------------------------------------------------------------

    public
    function test_withYear_normal()
    {
        $base = OffsetDateTime::ofDateAndTime(LocalDate::of(2008, 6, 30), LocalTime::of(11, 30, 59), self::OFFSET_PONE());
        $test = $base->withYear(2007);
        $this->assertEquals($test, OffsetDateTime::ofDateAndTime(LocalDate::of(2007, 6, 30), LocalTime::of(11, 30, 59), self::OFFSET_PONE()));
    }

//-----------------------------------------------------------------------
// withMonth()
//-----------------------------------------------------------------------

    public
    function test_withMonth_normal()
    {
        $base = OffsetDateTime::ofDateAndTime(LocalDate::of(2008, 6, 30), LocalTime::of(11, 30, 59), self::OFFSET_PONE());
        $test = $base->withMonth(1);
        $this->assertEquals($test, OffsetDateTime::ofDateAndTime(LocalDate::of(2008, 1, 30), LocalTime::of(11, 30, 59), self::OFFSET_PONE()));
    }

//-----------------------------------------------------------------------
// withDayOfMonth()
//-----------------------------------------------------------------------

    public
    function test_withDayOfMonth_normal()
    {
        $base = OffsetDateTime::ofDateAndTime(LocalDate::of(2008, 6, 30), LocalTime::of(11, 30, 59), self::OFFSET_PONE());
        $test = $base->withDayOfMonth(15);
        $this->assertEquals($test, OffsetDateTime::ofDateAndTime(LocalDate::of(2008, 6, 15), LocalTime::of(11, 30, 59), self::OFFSET_PONE()));
    }

//-----------------------------------------------------------------------
// withDayOfYear(int)
//-----------------------------------------------------------------------

    public
    function test_withDayOfYear_normal()
    {
        $t = self::TEST_2008_6_30_11_30_59_000000500()->withDayOfYear(33);
        $this->assertEquals($t, OffsetDateTime::ofDateAndTime(LocalDate::of(2008, 2, 2), LocalTime::of(11, 30, 59, 500), self::OFFSET_PONE()));
    }

    /**
     * @expectedException \Celest\DateTimeException
     */
    public function test_withDayOfYear_illegal()
    {
        self::TEST_2008_6_30_11_30_59_000000500()->withDayOfYear(367);
    }

    /**
     * @expectedException \Celest\DateTimeException
     */
    public function test_withDayOfYear_invalid()
    {
        OffsetDateTime::ofDateAndTime(LocalDate::of(2007, 2, 2), LocalTime::of(11, 30), self::OFFSET_PONE())->withDayOfYear(366);
    }

//-----------------------------------------------------------------------
// withHour()
//-----------------------------------------------------------------------

    public function test_withHour_normal()
    {
        $base = OffsetDateTime::ofDateAndTime(LocalDate::of(2008, 6, 30), LocalTime::of(11, 30, 59), self::OFFSET_PONE());
        $test = $base->withHour(15);
        $this->assertEquals($test, OffsetDateTime::ofDateAndTime(LocalDate::of(2008, 6, 30), LocalTime::of(15, 30, 59), self::OFFSET_PONE()));
    }

//-----------------------------------------------------------------------
// withMinute()
//-----------------------------------------------------------------------

    public function test_withMinute_normal()
    {
        $base = OffsetDateTime::ofDateAndTime(LocalDate::of(2008, 6, 30), LocalTime::of(11, 30, 59), self::OFFSET_PONE());
        $test = $base->withMinute(15);
        $this->assertEquals($test, OffsetDateTime::ofDateAndTime(LocalDate::of(2008, 6, 30), LocalTime::of(11, 15, 59), self::OFFSET_PONE()));
    }

//-----------------------------------------------------------------------
// withSecond()
//-----------------------------------------------------------------------

    public function test_withSecond_normal()
    {
        $base = OffsetDateTime::ofDateAndTime(LocalDate::of(2008, 6, 30), LocalTime::of(11, 30, 59), self::OFFSET_PONE());
        $test = $base->withSecond(15);
        $this->assertEquals($test, OffsetDateTime::ofDateAndTime(LocalDate::of(2008, 6, 30), LocalTime::of(11, 30, 15), self::OFFSET_PONE()));
    }

//-----------------------------------------------------------------------
// withNano()
//-----------------------------------------------------------------------

    public function test_withNanoOfSecond_normal()
    {
        $base = OffsetDateTime::ofDateAndTime(LocalDate::of(2008, 6, 30), LocalTime::of(11, 30, 59, 1), self::OFFSET_PONE());
        $test = $base->withNano(15);
        $this->assertEquals($test, OffsetDateTime::ofDateAndTime(LocalDate::of(2008, 6, 30), LocalTime::of(11, 30, 59, 15), self::OFFSET_PONE()));
    }

//-----------------------------------------------------------------------
// truncatedTo(TemporalUnit)
//-----------------------------------------------------------------------

    public function test_truncatedTo_normal()
    {
        $this->assertEquals(self::TEST_2008_6_30_11_30_59_000000500()->truncatedTo(CU::NANOS()), self::TEST_2008_6_30_11_30_59_000000500());
        $this->assertEquals(self::TEST_2008_6_30_11_30_59_000000500()->truncatedTo(CU::SECONDS()), self::TEST_2008_6_30_11_30_59_000000500()->withNano(0));
        $this->assertEquals(self::TEST_2008_6_30_11_30_59_000000500()->truncatedTo(CU::DAYS()), self::TEST_2008_6_30_11_30_59_000000500()->adjust(LocalTime::MIDNIGHT()));
    }

    public function test_truncatedTo_null()
    {
        TestHelper::assertNullException($this, function () {
            self::TEST_2008_6_30_11_30_59_000000500()->truncatedTo(null);
        });
    }

//-----------------------------------------------------------------------
// plus(Period)
//-----------------------------------------------------------------------

    public function test_plus_Period()
    {
        $period = MockSimplePeriod::of(7, CU::MONTHS());
        $t = self::TEST_2008_6_30_11_30_59_000000500()->plusAmount($period);
        $this->assertEquals($t, OffsetDateTime::of(2009, 1, 30, 11, 30, 59, 500, self::OFFSET_PONE()));
    }

//-----------------------------------------------------------------------
// plus(Duration)
//-----------------------------------------------------------------------

    public function test_plus_Duration()
    {
        $dur = Duration::ofSeconds(62, 3);
        $t = self::TEST_2008_6_30_11_30_59_000000500()->plusAmount($dur);
        $this->assertEquals($t, OffsetDateTime::of(2008, 6, 30, 11, 32, 1, 503, self::OFFSET_PONE()));
    }


    public function test_plus_Duration_zero()
    {
        $t = self::TEST_2008_6_30_11_30_59_000000500()->plusAmount(Duration::ZERO());
        $this->assertEquals($t, self::TEST_2008_6_30_11_30_59_000000500());
    }

    public function test_plus_Duration_null()
    {
        TestHelper::assertNullException($this, function () {
            self::TEST_2008_6_30_11_30_59_000000500()->plusAmount(null);
        });
    }

//-----------------------------------------------------------------------
// plusYears()
//-----------------------------------------------------------------------

    public function test_plusYears()
    {
        $base = OffsetDateTime::of(2008, 6, 30, 11, 30, 59, 0, self::OFFSET_PONE());
        $test = $base->plusYears(1);
        $this->assertEquals($test, OffsetDateTime::of(2009, 6, 30, 11, 30, 59, 0, self::OFFSET_PONE()));
    }

//-----------------------------------------------------------------------
// plusMonths()
//-----------------------------------------------------------------------

    public function test_plusMonths()
    {
        $base = OffsetDateTime::of(2008, 6, 30, 11, 30, 59, 0, self::OFFSET_PONE());
        $test = $base->plusMonths(1);
        $this->assertEquals($test, OffsetDateTime::of(2008, 7, 30, 11, 30, 59, 0, self::OFFSET_PONE()));
    }

//-----------------------------------------------------------------------
// plusWeeks()
//-----------------------------------------------------------------------

    public function test_plusWeeks()
    {
        $base = OffsetDateTime::of(2008, 6, 30, 11, 30, 59, 0, self::OFFSET_PONE());
        $test = $base->plusWeeks(1);
        $this->assertEquals($test, OffsetDateTime::of(2008, 7, 7, 11, 30, 59, 0, self::OFFSET_PONE()));
    }

//-----------------------------------------------------------------------
// plusDays()
//-----------------------------------------------------------------------

    public function test_plusDays()
    {
        $base = OffsetDateTime::of(2008, 6, 30, 11, 30, 59, 0, self::OFFSET_PONE());
        $test = $base->plusDays(1);
        $this->assertEquals($test, OffsetDateTime::of(2008, 7, 1, 11, 30, 59, 0, self::OFFSET_PONE()));
    }

//-----------------------------------------------------------------------
// plusHours()
//-----------------------------------------------------------------------

    public function test_plusHours()
    {
        $base = OffsetDateTime::of(2008, 6, 30, 11, 30, 59, 0, self::OFFSET_PONE());
        $test = $base->plusHours(13);
        $this->assertEquals($test, OffsetDateTime::of(2008, 7, 1, 0, 30, 59, 0, self::OFFSET_PONE()));
    }

//-----------------------------------------------------------------------
// plusMinutes()
//-----------------------------------------------------------------------

    public function test_plusMinutes()
    {
        $base = OffsetDateTime::of(2008, 6, 30, 11, 30, 59, 0, self::OFFSET_PONE());
        $test = $base->plusMinutes(30);
        $this->assertEquals($test, OffsetDateTime::of(2008, 6, 30, 12, 0, 59, 0, self::OFFSET_PONE()));
    }

//-----------------------------------------------------------------------
// plusSeconds()
//-----------------------------------------------------------------------

    public function test_plusSeconds()
    {
        $base = OffsetDateTime::of(2008, 6, 30, 11, 30, 59, 0, self::OFFSET_PONE());
        $test = $base->plusSeconds(1);
        $this->assertEquals($test, OffsetDateTime::of(2008, 6, 30, 11, 31, 0, 0, self::OFFSET_PONE()));
    }

//-----------------------------------------------------------------------
// plusNanos()
//-----------------------------------------------------------------------

    public function test_plusNanos()
    {
        $base = OffsetDateTime::of(2008, 6, 30, 11, 30, 59, 0, self::OFFSET_PONE());
        $test = $base->plusNanos(1);
        $this->assertEquals($test, OffsetDateTime::of(2008, 6, 30, 11, 30, 59, 1, self::OFFSET_PONE()));
    }

//-----------------------------------------------------------------------
// minus(Period)
//-----------------------------------------------------------------------

    public function test_minus_Period()
    {
        $period = MockSimplePeriod::of(7, CU::MONTHS());
        $t = self::TEST_2008_6_30_11_30_59_000000500()->minusAmount($period);
        $this->assertEquals($t, OffsetDateTime::of(2007, 11, 30, 11, 30, 59, 500, self::OFFSET_PONE()));
    }

//-----------------------------------------------------------------------
// minus(Duration)
//-----------------------------------------------------------------------

    public function test_minus_Duration()
    {
        $dur = Duration::ofSeconds(62, 3);
        $t = self::TEST_2008_6_30_11_30_59_000000500()->minusAmount($dur);
        $this->assertEquals($t, OffsetDateTime::of(2008, 6, 30, 11, 29, 57, 497, self::OFFSET_PONE()));
    }


    public function test_minus_Duration_zero()
    {
        $t = self::TEST_2008_6_30_11_30_59_000000500()->minusAmount(Duration::ZERO());
        $this->assertEquals($t, self::TEST_2008_6_30_11_30_59_000000500());
    }

    public function test_minus_Duration_null()
    {
        TestHelper::assertNullException($this, function () {
            self::TEST_2008_6_30_11_30_59_000000500()->minusAmount(null);
        });
    }

//-----------------------------------------------------------------------
// minusYears()
//-----------------------------------------------------------------------

    public function test_minusYears()
    {
        $base = OffsetDateTime::of(2008, 6, 30, 11, 30, 59, 0, self::OFFSET_PONE());
        $test = $base->minusYears(1);
        $this->assertEquals($test, OffsetDateTime::of(2007, 6, 30, 11, 30, 59, 0, self::OFFSET_PONE()));
    }

//-----------------------------------------------------------------------
// minusMonths()
//-----------------------------------------------------------------------

    public function test_minusMonths()
    {
        $base = OffsetDateTime::of(2008, 6, 30, 11, 30, 59, 0, self::OFFSET_PONE());
        $test = $base->minusMonths(1);
        $this->assertEquals($test, OffsetDateTime::of(2008, 5, 30, 11, 30, 59, 0, self::OFFSET_PONE()));
    }

//-----------------------------------------------------------------------
// minusWeeks()
//-----------------------------------------------------------------------

    public function test_minusWeeks()
    {
        $base = OffsetDateTime::of(2008, 6, 30, 11, 30, 59, 0, self::OFFSET_PONE());
        $test = $base->minusWeeks(1);
        $this->assertEquals($test, OffsetDateTime::of(2008, 6, 23, 11, 30, 59, 0, self::OFFSET_PONE()));
    }

//-----------------------------------------------------------------------
// minusDays()
//-----------------------------------------------------------------------

    public function test_minusDays()
    {
        $base = OffsetDateTime::of(2008, 6, 30, 11, 30, 59, 0, self::OFFSET_PONE());
        $test = $base->minusDays(1);
        $this->assertEquals($test, OffsetDateTime::of(2008, 6, 29, 11, 30, 59, 0, self::OFFSET_PONE()));
    }

//-----------------------------------------------------------------------
// minusHours()
//-----------------------------------------------------------------------

    public function test_minusHours()
    {
        $base = OffsetDateTime::of(2008, 6, 30, 11, 30, 59, 0, self::OFFSET_PONE());
        $test = $base->minusHours(13);
        $this->assertEquals($test, OffsetDateTime::of(2008, 6, 29, 22, 30, 59, 0, self::OFFSET_PONE()));
    }

//-----------------------------------------------------------------------
// minusMinutes()
//-----------------------------------------------------------------------

    public function test_minusMinutes()
    {
        $base = OffsetDateTime::of(2008, 6, 30, 11, 30, 59, 0, self::OFFSET_PONE());
        $test = $base->minusMinutes(30);
        $this->assertEquals($test, OffsetDateTime::of(2008, 6, 30, 11, 0, 59, 0, self::OFFSET_PONE()));
    }

//-----------------------------------------------------------------------
// minusSeconds()
//-----------------------------------------------------------------------

    public function test_minusSeconds()
    {
        $base = OffsetDateTime::of(2008, 6, 30, 11, 30, 59, 0, self::OFFSET_PONE());
        $test = $base->minusSeconds(1);
        $this->assertEquals($test, OffsetDateTime::of(2008, 6, 30, 11, 30, 58, 0, self::OFFSET_PONE()));
    }

//-----------------------------------------------------------------------
// minusNanos()
//-----------------------------------------------------------------------

    public function test_minusNanos()
    {
        $base = OffsetDateTime::of(2008, 6, 30, 11, 30, 59, 0, self::OFFSET_PONE());
        $test = $base->minusNanos(1);
        $this->assertEquals($test, OffsetDateTime::of(2008, 6, 30, 11, 30, 58, 999999999, self::OFFSET_PONE()));
    }

//-----------------------------------------------------------------------
// until(Temporal, TemporalUnit)
//-----------------------------------------------------------------------
    function data_untilUnit()
    {
        return [
            [OffsetDateTime::of(2010, 6, 30, 1, 1, 1, 0, self::OFFSET_PONE()), OffsetDateTime::of(2010, 6, 30, 13, 1, 1, 0, self::OFFSET_PONE()), CU::HALF_DAYS(), 1],
            [OffsetDateTime::of(2010, 6, 30, 1, 1, 1, 0, self::OFFSET_PONE()), OffsetDateTime::of(2010, 6, 30, 2, 1, 1, 0, self::OFFSET_PONE()), CU::HOURS(), 1],
            [OffsetDateTime::of(2010, 6, 30, 1, 1, 1, 0, self::OFFSET_PONE()), OffsetDateTime::of(2010, 6, 30, 2, 1, 1, 0, self::OFFSET_PONE()), CU::MINUTES(), 60],
            [OffsetDateTime::of(2010, 6, 30, 1, 1, 1, 0, self::OFFSET_PONE()), OffsetDateTime::of(2010, 6, 30, 2, 1, 1, 0, self::OFFSET_PONE()), CU::SECONDS(), 3600],
            [OffsetDateTime::of(2010, 6, 30, 1, 1, 1, 0, self::OFFSET_PONE()), OffsetDateTime::of(2010, 6, 30, 2, 1, 1, 0, self::OFFSET_PONE()), CU::MILLIS(), 3600 * 1000],
            [OffsetDateTime::of(2010, 6, 30, 1, 1, 1, 0, self::OFFSET_PONE()), OffsetDateTime::of(2010, 6, 30, 2, 1, 1, 0, self::OFFSET_PONE()), CU::MICROS(), 3600 * 1000 * 1000],
            [OffsetDateTime::of(2010, 6, 30, 1, 1, 1, 0, self::OFFSET_PONE()), OffsetDateTime::of(2010, 6, 30, 2, 1, 1, 0, self::OFFSET_PONE()), CU::NANOS(), 3600 * 1000 * 1000 * 1000],

            [OffsetDateTime::of(2010, 6, 30, 1, 1, 1, 0, self::OFFSET_PONE()), OffsetDateTime::of(2010, 6, 30, 14, 1, 1, 0, self::OFFSET_PTWO()), CU::HALF_DAYS(), 1],
            [OffsetDateTime::of(2010, 6, 30, 1, 1, 1, 0, self::OFFSET_PONE()), OffsetDateTime::of(2010, 6, 30, 3, 1, 1, 0, self::OFFSET_PTWO()), CU::HOURS(), 1],
            [OffsetDateTime::of(2010, 6, 30, 1, 1, 1, 0, self::OFFSET_PONE()), OffsetDateTime::of(2010, 6, 30, 3, 1, 1, 0, self::OFFSET_PTWO()), CU::MINUTES(), 60],
            [OffsetDateTime::of(2010, 6, 30, 1, 1, 1, 0, self::OFFSET_PONE()), OffsetDateTime::of(2010, 6, 30, 3, 1, 1, 0, self::OFFSET_PTWO()), CU::SECONDS(), 3600],
            [OffsetDateTime::of(2010, 6, 30, 1, 1, 1, 0, self::OFFSET_PONE()), OffsetDateTime::of(2010, 6, 30, 3, 1, 1, 0, self::OFFSET_PTWO()), CU::MILLIS(), 3600 * 1000],
            [OffsetDateTime::of(2010, 6, 30, 1, 1, 1, 0, self::OFFSET_PONE()), OffsetDateTime::of(2010, 6, 30, 3, 1, 1, 0, self::OFFSET_PTWO()), CU::MICROS(), 3600 * 1000 * 1000],
            [OffsetDateTime::of(2010, 6, 30, 1, 1, 1, 0, self::OFFSET_PONE()), OffsetDateTime::of(2010, 6, 30, 3, 1, 1, 0, self::OFFSET_PTWO()), CU::NANOS(), 3600 * 1000 * 1000 * 1000],

            [OffsetDateTime::of(2010, 6, 30, 1, 1, 1, 0, self::OFFSET_PONE()), OffsetDateTime::of(2010, 7, 1, 1, 1, 0, 999999999, self::OFFSET_PONE()), CU::DAYS(), 0],
            [OffsetDateTime::of(2010, 6, 30, 1, 1, 1, 0, self::OFFSET_PONE()), OffsetDateTime::of(2010, 7, 1, 1, 1, 1, 0, self::OFFSET_PONE()), CU::DAYS(), 1],
            [OffsetDateTime::of(2010, 6, 30, 1, 1, 1, 0, self::OFFSET_PONE()), OffsetDateTime::of(2010, 8, 29, 1, 1, 1, 0, self::OFFSET_PONE()), CU::MONTHS(), 1],
            [OffsetDateTime::of(2010, 6, 30, 1, 1, 1, 0, self::OFFSET_PONE()), OffsetDateTime::of(2010, 8, 30, 1, 1, 1, 0, self::OFFSET_PONE()), CU::MONTHS(), 2],
            [OffsetDateTime::of(2010, 6, 30, 1, 1, 1, 0, self::OFFSET_PONE()), OffsetDateTime::of(2010, 8, 31, 1, 1, 1, 0, self::OFFSET_PONE()), CU::MONTHS(), 2],
        ];
    }

    /**
     * @dataProvider data_untilUnit
     */
    public function test_until_TemporalUnit(OffsetDateTime $odt1, OffsetDateTime $odt2, TemporalUnit $unit, $expected)
    {
        $amount = $odt1->until($odt2, $unit);
        $this->assertEquals($amount, $expected);
    }

    /**
     * @dataProvider data_untilUnit
     */
    public function test_until_TemporalUnit_negated(OffsetDateTime $odt1, OffsetDateTime $odt2, TemporalUnit $unit, $expected)
    {
        $amount = $odt2->until($odt1, $unit);
        $this->assertEquals($amount, -$expected);
    }

    /**
     * @dataProvider data_untilUnit
     */
    public function test_until_TemporalUnit_between(OffsetDateTime $odt1, OffsetDateTime $odt2, TemporalUnit $unit, $expected)
    {
        $amount = $unit->between($odt1, $odt2);
        $this->assertEquals($amount, $expected);
    }


    public function test_until_convertedType()
    {
        $odt = OffsetDateTime::of(2010, 6, 30, 1, 1, 1, 0, self::OFFSET_PONE());
        $zdt = $odt->plusSeconds(3)->toZonedDateTime();
        $this->assertEquals($odt->until($zdt, CU::SECONDS()), 3);
    }

    /**
     * @expectedException \Celest\DateTimeException
     */
    public function test_until_invalidType()
    {
        $odt = OffsetDateTime::of(2010, 6, 30, 1, 1, 1, 0, self::OFFSET_PONE());
        $odt->until(Instant::ofEpochSecond(12), CU::SECONDS());
    }

    /**
     * @expectedException \Celest\DateTimeException
     */
    public function test_until_invalidTemporalUnit()
    {
        $odt1 = OffsetDateTime::of(2010, 6, 30, 1, 1, 1, 0, self::OFFSET_PONE());
        $odt2 = OffsetDateTime::of(2010, 6, 30, 2, 1, 1, 0, self::OFFSET_PONE());
        $odt1->until($odt2, CU::FOREVER());
    }

//-----------------------------------------------------------------------
// format(DateTimeFormatter)
//-----------------------------------------------------------------------

    public function test_format_formatter()
    {
        $f = DateTimeFormatter::ofPattern("y M d H m s");
        $t = OffsetDateTime::of(2010, 12, 3, 11, 30, 0, 0, self::OFFSET_PONE())->format($f);
        $this->assertEquals($t, "2010 12 3 11 30 0");
    }

    public function test_format_formatter_null()
    {
        TestHelper::assertNullException($this, function () {
            OffsetDateTime::of(2010, 12, 3, 11, 30, 0, 0, self::OFFSET_PONE())->format(null);
        });
    }

//-----------------------------------------------------------------------
// atZoneSameInstant()
//-----------------------------------------------------------------------

    public function test_atZone()
    {
        $t = OffsetDateTime::of(2008, 6, 30, 11, 30, 0, 0, self::OFFSET_MTWO());
        $this->assertEquals($t->atZoneSameInstant(self::ZONE_PARIS()),
            ZonedDateTime::of(2008, 6, 30, 15, 30, 0, 0, self::ZONE_PARIS()));
    }

    public function test_atZone_nullTimeZone()
    {
        TestHelper::assertNullException($this, function () {
            $t = OffsetDateTime::of(2008, 6, 30, 11, 30, 0, 0, self::OFFSET_PTWO());
            $t->atZoneSameInstant(null);
        });

    }

//-----------------------------------------------------------------------
// atZoneSimilarLocal()
//-----------------------------------------------------------------------

    public function test_atZoneSimilarLocal()
    {
        $t = OffsetDateTime::of(2008, 6, 30, 11, 30, 0, 0, self::OFFSET_MTWO());
        $this->assertEquals($t->atZoneSimilarLocal(self::ZONE_PARIS()),
            ZonedDateTime::of(2008, 6, 30, 11, 30, 0, 0, self::ZONE_PARIS()));
    }


    public function test_atZoneSimilarLocal_dstGap()
    {
        $t = OffsetDateTime::of(2007, 4, 1, 0, 0, 0, 0, self::OFFSET_MTWO());
        $this->assertEquals($t->atZoneSimilarLocal(self::ZONE_GAZA()),
            ZonedDateTime::of(2007, 4, 1, 1, 0, 0, 0, self::ZONE_GAZA()));
    }


    public function test_atZone_dstOverlapSummer()
    {
        $t = OffsetDateTime::of(2007, 10, 28, 2, 30, 0, 0, self::OFFSET_PTWO());
        $this->assertEquals($t->atZoneSimilarLocal(self::ZONE_PARIS())->toLocalDateTime(), $t->toLocalDateTime());
        $this->assertEquals($t->atZoneSimilarLocal(self::ZONE_PARIS())->getOffset(), self::OFFSET_PTWO());
        $this->assertEquals($t->atZoneSimilarLocal(self::ZONE_PARIS())->getZone(), self::ZONE_PARIS());
    }


    public function test_atZone_dstOverlapWinter()
    {
        $t = OffsetDateTime::of(2007, 10, 28, 2, 30, 0, 0, self::OFFSET_PONE());
        $this->assertEquals($t->atZoneSimilarLocal(self::ZONE_PARIS())->toLocalDateTime(), $t->toLocalDateTime());
        $this->assertEquals($t->atZoneSimilarLocal(self::ZONE_PARIS())->getOffset(), self::OFFSET_PONE());
        $this->assertEquals($t->atZoneSimilarLocal(self::ZONE_PARIS())->getZone(), self::ZONE_PARIS());
    }

    public function test_atZoneSimilarLocal_nullTimeZone()
    {
        TestHelper::assertNullException($this, function () {
            $t = OffsetDateTime::of(2008, 6, 30, 11, 30, 0, 0, self::OFFSET_PTWO());
            $t->atZoneSimilarLocal(null);
        });

    }

//-----------------------------------------------------------------------
// toEpochSecond()
//-----------------------------------------------------------------------
    /**
     * @group long
     */
    public function test_toEpochSecond_afterEpoch()
    {
        for ($i = 0; $i < 100000; $i++) {
            $a = OffsetDateTime::of(1970, 1, 1, 0, 0, 0, 0, ZoneOffset::UTC())->plusSeconds($i);
            $this->assertEquals($a->toEpochSecond(), $i);
        }
    }

    /**
     * @group long
     */
    public function test_toEpochSecond_beforeEpoch()
    {
        for ($i = 0; $i < 100000; $i++) {
            $a = OffsetDateTime::of(1970, 1, 1, 0, 0, 0, 0, ZoneOffset::UTC())->minusSeconds($i);
            $this->assertEquals($a->toEpochSecond(), -$i);
        }
    }

//-----------------------------------------------------------------------
// compareTo()
//-----------------------------------------------------------------------

    public function test_compareTo_timeMins()
    {
        $this->markTestIncomplete('Comparator');
        $a = OffsetDateTime::of(2008, 6, 30, 11, 29, 3, 0, self::OFFSET_PONE());
        $b = OffsetDateTime::of(2008, 6, 30, 11, 30, 2, 0, self::OFFSET_PONE());  // a is before b due to time
        $this->assertEquals($a->compareTo($b) < 0, true);
        $this->assertEquals($b->compareTo($a) > 0, true);
        $this->assertEquals($a->compareTo($a) == 0, true);
        $this->assertEquals($b->compareTo($b) == 0, true);
        $this->assertEquals($a->toInstant()->compareTo($b->toInstant()) < 0, true);
        $this->assertEquals(OffsetDateTime::timeLineOrder()->compare($a, $b) < 0, true);
    }


    public function test_compareTo_timeSecs()
    {
        $this->markTestIncomplete('Comparator');
        $a = OffsetDateTime::of(2008, 6, 30, 11, 29, 2, 0, self::OFFSET_PONE());
        $b = OffsetDateTime::of(2008, 6, 30, 11, 29, 3, 0, self::OFFSET_PONE());  // a is before b due to time
        $this->assertEquals($a->compareTo($b) < 0, true);
        $this->assertEquals($b->compareTo($a) > 0, true);
        $this->assertEquals($a->compareTo($a) == 0, true);
        $this->assertEquals($b->compareTo($b) == 0, true);
        $this->assertEquals($a->toInstant()->compareTo($b->toInstant()) < 0, true);
        $this->assertEquals(OffsetDateTime::timeLineOrder()->compare($a, $b) < 0, true);
    }


    public function test_compareTo_timeNanos()
    {
        $this->markTestIncomplete('Comparator');
        $a = OffsetDateTime::of(2008, 6, 30, 11, 29, 40, 4, self::OFFSET_PONE());
        $b = OffsetDateTime::of(2008, 6, 30, 11, 29, 40, 5, self::OFFSET_PONE());  // a is before b due to time
        $this->assertEquals($a->compareTo($b) < 0, true);
        $this->assertEquals($b->compareTo($a) > 0, true);
        $this->assertEquals($a->compareTo($a) == 0, true);
        $this->assertEquals($b->compareTo($b) == 0, true);
        $this->assertEquals($a->toInstant()->compareTo($b->toInstant()) < 0, true);
        $this->assertEquals(OffsetDateTime::timeLineOrder()->compare($a, $b) < 0, true);
    }


    public function test_compareTo_offset()
    {
        $this->markTestIncomplete('Comparator');
        $a = OffsetDateTime::of(2008, 6, 30, 11, 30, 0, 0, self::OFFSET_PTWO());
        $b = OffsetDateTime::of(2008, 6, 30, 11, 30, 0, 0, self::OFFSET_PONE());  // a is before b due to offset
        $this->assertEquals($a->compareTo($b) < 0, true);
        $this->assertEquals($b->compareTo($a) > 0, true);
        $this->assertEquals($a->compareTo($a) == 0, true);
        $this->assertEquals($b->compareTo($b) == 0, true);
        $this->assertEquals($a->toInstant()->compareTo($b->toInstant()) < 0, true);
        $this->assertEquals(OffsetDateTime::timeLineOrder()->compare($a, $b) < 0, true);
    }


    public function test_compareTo_offsetNanos()
    {
        $this->markTestIncomplete('Comparator');
        $a = OffsetDateTime::of(2008, 6, 30, 11, 30, 40, 6, self::OFFSET_PTWO());
        $b = OffsetDateTime::of(2008, 6, 30, 11, 30, 40, 5, self::OFFSET_PONE());  // a is before b due to offset
        $this->assertEquals($a->compareTo($b) < 0, true);
        $this->assertEquals($b->compareTo($a) > 0, true);
        $this->assertEquals($a->compareTo($a) == 0, true);
        $this->assertEquals($b->compareTo($b) == 0, true);
        $this->assertEquals($a->toInstant()->compareTo($b->toInstant()) < 0, true);
        $this->assertEquals(OffsetDateTime::timeLineOrder()->compare($a, $b) < 0, true);
    }


    public function test_compareTo_both()
    {
        $this->markTestIncomplete('Comparator');
        $a = OffsetDateTime::of(2008, 6, 30, 11, 50, 0, 0, self::OFFSET_PTWO());
        $b = OffsetDateTime::of(2008, 6, 30, 11, 20, 0, 0, self::OFFSET_PONE());  // a is before b on instant scale
        $this->assertEquals($a->compareTo($b) < 0, true);
        $this->assertEquals($b->compareTo($a) > 0, true);
        $this->assertEquals($a->compareTo($a) == 0, true);
        $this->assertEquals($b->compareTo($b) == 0, true);
        $this->assertEquals($a->toInstant()->compareTo($b->toInstant()) < 0, true);
        $this->assertEquals(OffsetDateTime::timeLineOrder()->compare($a, $b) < 0, true);
    }


    public function test_compareTo_bothNanos()
    {
        $this->markTestIncomplete('Comparator');
        $a = OffsetDateTime::of(2008, 6, 30, 11, 20, 40, 4, self::OFFSET_PTWO());
        $b = OffsetDateTime::of(2008, 6, 30, 10, 20, 40, 5, self::OFFSET_PONE());  // a is before b on instant scale
        $this->assertEquals($a->compareTo($b) < 0, true);
        $this->assertEquals($b->compareTo($a) > 0, true);
        $this->assertEquals($a->compareTo($a) == 0, true);
        $this->assertEquals($b->compareTo($b) == 0, true);
        $this->assertEquals($a->toInstant()->compareTo($b->toInstant()) < 0, true);
        $this->assertEquals(OffsetDateTime::timeLineOrder()->compare($a, $b) < 0, true);
    }


    public function test_compareTo_bothInstantComparator()
    {
        $this->markTestIncomplete('Comparator');
        $a = OffsetDateTime::of(2008, 6, 30, 11, 20, 40, 4, self::OFFSET_PTWO());
        $b = OffsetDateTime::of(2008, 6, 30, 10, 20, 40, 5, self::OFFSET_PONE());
        $this->assertEquals($a->compareTo($b), OffsetDateTime::timeLineOrder()->compare($a, $b), "for nano != nano, compareTo and timeLineOrder() should be the same");
    }


    public function test_compareTo_hourDifference()
    {
        $a = OffsetDateTime::of(2008, 6, 30, 10, 0, 0, 0, self::OFFSET_PONE());
        $b = OffsetDateTime::of(2008, 6, 30, 11, 0, 0, 0, self::OFFSET_PTWO());  // a is before b despite being same time-line time
        $this->assertEquals($a->compareTo($b) < 0, true);
        $this->assertEquals($b->compareTo($a) > 0, true);
        $this->assertEquals($a->compareTo($a) == 0, true);
        $this->assertEquals($b->compareTo($b) == 0, true);
        $this->assertEquals($a->toInstant()->compareTo($b->toInstant()) == 0, true);
    }


    public function test_compareTo_max()
    {
        $a = OffsetDateTime::of(Year::MAX_VALUE, 12, 31, 23, 59, 0, 0, self::OFFSET_MONE());
        $b = OffsetDateTime::of(Year::MAX_VALUE, 12, 31, 23, 59, 0, 0, self::OFFSET_MTWO());  // a is before b due to offset
        $this->assertEquals($a->compareTo($b) < 0, true);
        $this->assertEquals($b->compareTo($a) > 0, true);
        $this->assertEquals($a->compareTo($a) == 0, true);
        $this->assertEquals($b->compareTo($b) == 0, true);
    }


    public function test_compareTo_min()
    {
        $a = OffsetDateTime::of(Year::MIN_VALUE, 1, 1, 0, 0, 0, 0, self::OFFSET_PTWO());
        $b = OffsetDateTime::of(Year::MIN_VALUE, 1, 1, 0, 0, 0, 0, self::OFFSET_PONE());  // a is before b due to offset
        $this->assertEquals($a->compareTo($b) < 0, true);
        $this->assertEquals($b->compareTo($a) > 0, true);
        $this->assertEquals($a->compareTo($a) == 0, true);
        $this->assertEquals($b->compareTo($b) == 0, true);
    }

    public function test_compareTo_null()
    {
        TestHelper::assertNullException($this, function () {
            $a = OffsetDateTime::of(2008, 6, 30, 11, 30, 59, 0, self::OFFSET_PONE());
            $a->compareTo(null);
        });

    }

    public function test_compareToNonOffsetDateTime()
    {
        $this->markTestSkipped('compareTo');
        $c = self::TEST_2008_6_30_11_30_59_000000500();
        $c->compareTo(new Object());
    }

//-----------------------------------------------------------------------
// isAfter() / isBefore() / isEqual()
//-----------------------------------------------------------------------

    public
    function test_isBeforeIsAfterIsEqual1()
    {
        $a = OffsetDateTime::of(2008, 6, 30, 11, 30, 58, 3, self::OFFSET_PONE());
        $b = OffsetDateTime::of(2008, 6, 30, 11, 30, 59, 2, self::OFFSET_PONE());  // a is before b due to time
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


    public
    function test_isBeforeIsAfterIsEqual2()
    {
        $a = OffsetDateTime::of(2008, 6, 30, 11, 30, 59, 2, self::OFFSET_PONE());
        $b = OffsetDateTime::of(2008, 6, 30, 11, 30, 59, 3, self::OFFSET_PONE());  // a is before b due to time
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


    public
    function test_isBeforeIsAfterIsEqual_instantComparison()
    {
        $a = OffsetDateTime::of(2008, 6, 30, 10, 0, 0, 0, self::OFFSET_PONE());
        $b = OffsetDateTime::of(2008, 6, 30, 11, 0, 0, 0, self::OFFSET_PTWO());  // a is same instant as b
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
            $a = OffsetDateTime::of(2008, 6, 30, 11, 30, 59, 0, self::OFFSET_PONE());
            $a->isBefore(null);
        });

    }

    public function test_isEqual_null()
    {
        TestHelper::assertNullException($this, function () {
            $a = OffsetDateTime::of(2008, 6, 30, 11, 30, 59, 0, self::OFFSET_PONE());
            $a->isEqual(null);
        });

    }

    public function test_isAfter_null()
    {
        TestHelper::assertNullException($this, function () {
            $a = OffsetDateTime::of(2008, 6, 30, 11, 30, 59, 0, self::OFFSET_PONE());
            $a->isAfter(null);
        });

    }

//-----------------------------------------------------------------------
// equals() / hashCode()
//-----------------------------------------------------------------------
    /**
     * @dataProvider provider_sampleTimes
     */
    public function test_equals_true($y, $o, $d, $h, $m, $s, $n, ZoneOffset $ignored)
    {
        $a = OffsetDateTime::of($y, $o, $d, $h, $m, $s, $n, self::OFFSET_PONE());
        $b = OffsetDateTime::of($y, $o, $d, $h, $m, $s, $n, self::OFFSET_PONE());
        $this->assertEquals($a->equals($b), true);
    }

    /**
     * @dataProvider provider_sampleTimes
     */
    public function test_equals_false_year_differs($y, $o, $d, $h, $m, $s, $n, ZoneOffset $ignored)
    {
        $a = OffsetDateTime::of($y, $o, $d, $h, $m, $s, $n, self::OFFSET_PONE());
        $b = OffsetDateTime::of($y + 1, $o, $d, $h, $m, $s, $n, self::OFFSET_PONE());
        $this->assertEquals($a->equals($b), false);
    }

    /**
     * @dataProvider provider_sampleTimes
     */
    public function test_equals_false_hour_differs($y, $o, $d, $h, $m, $s, $n, ZoneOffset $ignored)
    {
        $h = ($h == 23 ? 22 : $h);
        $a = OffsetDateTime::of($y, $o, $d, $h, $m, $s, $n, self::OFFSET_PONE());
        $b = OffsetDateTime::of($y, $o, $d, $h + 1, $m, $s, $n, self::OFFSET_PONE());
        $this->assertEquals($a->equals($b), false);
    }

    /**
     * @dataProvider provider_sampleTimes
     */
    public function test_equals_false_minute_differs($y, $o, $d, $h, $m, $s, $n, ZoneOffset $ignored)
    {
        $m = ($m == 59 ? 58 : $m);
        $a = OffsetDateTime::of($y, $o, $d, $h, $m, $s, $n, self::OFFSET_PONE());
        $b = OffsetDateTime::of($y, $o, $d, $h, $m + 1, $s, $n, self::OFFSET_PONE());
        $this->assertEquals($a->equals($b), false);
    }

    /**
     * @dataProvider provider_sampleTimes
     */
    public function test_equals_false_second_differs($y, $o, $d, $h, $m, $s, $n, ZoneOffset $ignored)
    {
        $s = ($s == 59 ? 58 : $s);
        $a = OffsetDateTime::of($y, $o, $d, $h, $m, $s, $n, self::OFFSET_PONE());
        $b = OffsetDateTime::of($y, $o, $d, $h, $m, $s + 1, $n, self::OFFSET_PONE());
        $this->assertEquals($a->equals($b), false);
    }

    /**
     * @dataProvider provider_sampleTimes
     */
    public function test_equals_false_nano_differs($y, $o, $d, $h, $m, $s, $n, ZoneOffset $ignored)
    {
        $n = ($n == 999999999 ? 999999998 : $n);
        $a = OffsetDateTime::of($y, $o, $d, $h, $m, $s, $n, self::OFFSET_PONE());
        $b = OffsetDateTime::of($y, $o, $d, $h, $m, $s, $n + 1, self::OFFSET_PONE());
        $this->assertEquals($a->equals($b), false);
    }

    /**
     * @dataProvider provider_sampleTimes
     */
    public function test_equals_false_offset_differs($y, $o, $d, $h, $m, $s, $n, ZoneOffset $ignored)
    {
        $a = OffsetDateTime::of($y, $o, $d, $h, $m, $s, $n, self::OFFSET_PONE());
        $b = OffsetDateTime::of($y, $o, $d, $h, $m, $s, $n, self::OFFSET_PTWO());
        $this->assertEquals($a->equals($b), false);
    }


    public function test_equals_itself_true()
    {
        $this->assertEquals(self::TEST_2008_6_30_11_30_59_000000500()->equals(self::TEST_2008_6_30_11_30_59_000000500()), true);
    }


    public function test_equals_string_false()
    {
        $this->assertEquals(self::TEST_2008_6_30_11_30_59_000000500()->equals("2007-07-15"), false);
    }


    public function test_equals_null_false()
    {
        $this->assertEquals(self::TEST_2008_6_30_11_30_59_000000500()->equals(null), false);
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
        ];
    }

    /**
     * @dataProvider provider_sampleToString
     */
    public function test_toString($y, $o, $d, $h, $m, $s, $n, $offsetId, $expected)
    {
        $t = OffsetDateTime::of($y, $o, $d, $h, $m, $s, $n, ZoneOffset::of($offsetId));
        $str = $t->__toString();
        $this->assertEquals($str, $expected);
    }

}
