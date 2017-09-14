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

use Celest\Helper\Long;
use Celest\Helper\Math;
use Celest\Temporal\ChronoField as CF;
use Celest\Temporal\ChronoUnit as CU;
use Celest\Temporal\JulianFields;
use Celest\Temporal\TemporalAccessor;
use Celest\Temporal\TemporalAmount;
use Celest\Temporal\TemporalField;
use Celest\Temporal\TemporalQueries;
use Celest\Temporal\TemporalQuery;
use Celest\Temporal\TemporalUnit;

/**
 * Test Instant::
 */
class TCKInstantTest extends AbstractDateTimeTest
{

    private static function MIN_SECOND()
    {
        return Instant::MIN()->getEpochSecond();
    }

    private static function MAX_SECOND()
    {
        return Instant::MAX()->getEpochSecond();
    }

    private static function ZONE_PARIS()
    {
        return ZoneId::of("Europe/Paris");
    }

    private static function OFFSET_PTWO()
    {
        return ZoneOffset::ofHours(2);
    }

    private function TEST_12345123456789()
    {
        return Instant::ofEpochSecond(12345, 123456789);
    }


    //-----------------------------------------------------------------------
    protected function samples()
    {
        return [self::TEST_12345123456789(), Instant::MIN(), Instant::MAX(), Instant::EPOCH()];
    }

    protected function validFields()
    {
        return [
            CF::NANO_OF_SECOND(),
            CF::MICRO_OF_SECOND(),
            CF::MILLI_OF_SECOND(),
            CF::INSTANT_SECONDS(),
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
    private function check(Instant $instant, $epochSecs, $nos)
    {
        $this->assertEquals($instant->getEpochSecond(), $epochSecs);
        $this->assertEquals($instant->getNano(), $nos);
        $this->assertEquals($instant, $instant);
    }

    //-----------------------------------------------------------------------
    public function test_constant_EPOCH()
    {
        $this->check(Instant::EPOCH(), 0, 0);
    }

    public function test_constant_MIN()
    {
        $this->check(Instant::MIN(), -31557014167219200, 0);
    }

    public function test_constant_MAX()
    {
        $this->check(Instant::MAX(), 31556889864403199, 999999999);
    }

    //-----------------------------------------------------------------------
    // now()
    //-----------------------------------------------------------------------

    public function test_now()
    {
        $expected = Instant::nowOf(Clock::systemUTC());
        $test = Instant::now();
        $diff = Math::abs($test->toEpochMilli() - $expected->toEpochMilli());
        $this->assertTrue($diff < 100);  // less than 0.1 secs
    }

    //-----------------------------------------------------------------------
    // now(Clock)
    //-----------------------------------------------------------------------
    public function test_now_Clock_nullClock()
    {
        TestHelper::assertNullException($this, function () {
            Instant::nowOf(null);
        });
    }


    /**
     * @group long
     */
    public function test_now_Clock_allSecsInDay_utc()
    {
        for ($i = 0; $i < (2 * 24 * 60 * 60); $i++) {
            $expected = Instant::ofEpochSecond($i)->plusNanos(123456789);
            $clock = Clock::fixed($expected, ZoneOffset::UTC());
            $test = Instant::nowOf($clock);
            $this->assertEquals($test, $expected);
        }
    }


    /**
     * @group long
     */
    public function test_now_Clock_allSecsInDay_beforeEpoch()
    {
        for ($i = -1; $i >= -(24 * 60 * 60); $i--) {
            $expected = Instant::ofEpochSecond($i)->plusNanos(123456789);
            $clock = Clock::fixed($expected, ZoneOffset::UTC());
            $test = Instant::nowOf($clock);
            $this->assertEquals($test, $expected);
        }
    }

    //-----------------------------------------------------------------------
    // ofEpochSecond(long)
    //-----------------------------------------------------------------------

    public function test_factory_seconds_long()
    {
        for ($i = -2; $i <= 2; $i++) {
            $t = Instant::ofEpochSecond($i);
            $this->assertEquals($t->getEpochSecond(), $i);
            $this->assertEquals($t->getNano(), 0);
        }
    }

//-----------------------------------------------------------------------
// ofEpochSecond(long,long)
//-----------------------------------------------------------------------

    public function test_factory_seconds_long_long()
    {
        for ($i = -2; $i <= 2; $i++) {
            for ($j = 0; $j < 10; $j++) {
                $t = Instant::ofEpochSecond($i, $j);
                $this->assertEquals($t->getEpochSecond(), $i);
                $this->assertEquals($t->getNano(), $j);
            }
            for ($j = -10; $j < 0; $j++) {
                $t = Instant::ofEpochSecond($i, $j);
                $this->assertEquals($t->getEpochSecond(), $i - 1);
                $this->assertEquals($t->getNano(), $j + 1000000000);
            }
            for ($j = 999999990; $j < 1000000000; $j++) {
                $t = Instant::ofEpochSecond($i, $j);
                $this->assertEquals($t->getEpochSecond(), $i);
                $this->assertEquals($t->getNano(), $j);
            }
        }
    }


    public function test_factory_seconds_long_long_nanosNegativeAdjusted()
    {
        $test = Instant::ofEpochSecond(2, -1);
        $this->assertEquals($test->getEpochSecond(), 1);
        $this->assertEquals($test->getNano(), 999999999);
    }

    /**
     * @expectedException \Celest\DateTimeException
     */
    public function test_factory_seconds_long_long_tooBig()
    {
        Instant::ofEpochSecond(self::MAX_SECOND(), 1000000000);
    }

    /**
     * @expectedException \Celest\ArithmeticException
     */
    public function test_factory_seconds_long_long_tooBigBig()
    {
        Instant::ofEpochSecond(Long::MAX_VALUE, Long::MAX_VALUE);
    }

    //-----------------------------------------------------------------------
    // ofEpochMilli(long)
    //-----------------------------------------------------------------------
    function provider_factory_millis_long()
    {
        return [
            [
                0, 0, 0],
            [
                1, 0, 1000000],
            [
                2, 0, 2000000],
            [
                999, 0, 999000000],
            [
                1000, 1, 0],
            [
                1001, 1, 1000000],
            [
                -1, -1, 999000000],
            [
                -2, -1, 998000000],
            [
                -999, -1, 1000000],
            [
                -1000, -1, 0],
            [
                -1001, -2, 999000000],
        ];
    }

    /**
     * @dataProvider provider_factory_millis_long
     */
    public function test_factory_millis_long($millis, $expectedSeconds, $expectedNanoOfSecond)
    {
        $t = Instant::ofEpochMilli($millis);
        $this->assertEquals($t->getEpochSecond(), $expectedSeconds);
        $this->assertEquals($t->getNano(), $expectedNanoOfSecond);
    }

    function provider_factory_datetime()
    {
        return [
            [new \DateTime('2000-01-01')],
            [new \DateTime('now', new \DateTimeZone('Europe/Berlin'))],
            [new \DateTime()],
        ];
    }

    /**
     * @dataProvider provider_factory_datetime
     */
    public function test_factory_datetime(\DateTimeInterface $dateTime)
    {
        $t = Instant::ofDateTime($dateTime);
        $this->assertEquals($t->getEpochSecond(), $dateTime->getTimestamp());
        $this->assertEquals($t->getNano(), $dateTime->format('u') * 1000);
    }

    //-----------------------------------------------------------------------
    // parse(String)
    //-----------------------------------------------------------------------
    // see also parse tests under toString()
    function provider_factory_parse()
    {
        return [
            ["1970-01-01T00:00:00Z", 0, 0],
            ["1970-01-01t00:00:00Z", 0, 0],
            ["1970-01-01T00:00:00z", 0, 0],
            ["1970-01-01T00:00:00.0Z", 0, 0],
            ["1970-01-01T00:00:00.000000000Z", 0, 0],

            ["1970-01-01T00:00:00.000000001Z", 0, 1],
            ["1970-01-01T00:00:00.100000000Z", 0, 100000000],
            ["1970-01-01T00:00:01Z", 1, 0],
            ["1970-01-01T00:01:00Z", 60, 0],
            ["1970-01-01T00:01:01Z", 61, 0],
            ["1970-01-01T00:01:01.000000001Z", 61, 1],
            ["1970-01-01T01:00:00.000000000Z", 3600, 0],
            ["1970-01-01T01:01:01.000000001Z", 3661, 1],
            ["1970-01-02T01:01:01.100000000Z", 90061, 100000000],
        ];
    }

    /**
     * @dataProvider provider_factory_parse
     */
    public function test_factory_parse($text, $expectedEpochSeconds, $expectedNanoOfSecond)
    {
        $t = Instant::parse($text);
        $this->assertEquals($t->getEpochSecond(), $expectedEpochSeconds);
        $this->assertEquals($t->getNano(), $expectedNanoOfSecond);
    }

    /**
     * @dataProvider provider_factory_parse
     */
    public function test_factory_parseLowercase($text, $expectedEpochSeconds, $expectedNanoOfSecond)
    {
        $t = Instant::parse(strtolower($text));
        $this->assertEquals($t->getEpochSecond(), $expectedEpochSeconds);
        $this->assertEquals($t->getNano(), $expectedNanoOfSecond);
    }

// TODO: should comma be accepted?
//    (dataProvider="Parse")
//    public function test_factory_parse_comma($text, $expectedEpochSeconds, $expectedNanoOfSecond) {
//        $text = $text.replace('.', ',');
//    $t = Instant::parse($text);
//        $this->assertEquals($t->getEpochSecond(), $expectedEpochSeconds);
//        $this->assertEquals($t->getNano(), $expectedNanoOfSecond);
//    }

    function provider_factory_parseFailures()
    {
        return [
            [""],
            ["Z"],
            ["1970-01-01T00:00:00"],
            ["1970-01-01T00:00:0Z"],
            ["1970-01-01T00:0:00Z"],
            ["1970-01-01T0:00:00Z"],
            ["1970-01-01T00:00:00.0000000000Z"],
        ];
    }

    /**
     * @dataProvider provider_factory_parseFailures
     * @expectedException \Celest\DateTimeParseException
     */
    public function test_factory_parseFailures($text)
    {
        Instant::parse($text);
    }

    /**
     * @dataProvider provider_factory_parseFailures
     * @expectedException \Celest\DateTimeParseException
     */

    public function test_factory_parseFailures_comma($text)
    {
        $text = str_replace('.', ',', $text);
        Instant::parse($text);
    }

    public function test_factory_parse_nullText()
    {
        TestHelper::assertNullException($this, function () {
            Instant::parse(null);
        });
    }

    //-----------------------------------------------------------------------
    // get(TemporalField)
    //-----------------------------------------------------------------------

    public function test_get_TemporalField()
    {
        $test = self::TEST_12345123456789();
        $this->assertEquals($test->get(CF::NANO_OF_SECOND()), 123456789);
        $this->assertEquals($test->get(CF::MICRO_OF_SECOND()), 123456);
        $this->assertEquals($test->get(CF::MILLI_OF_SECOND()), 123);
    }


    public function test_getLong_TemporalField()
    {
        $test = self::TEST_12345123456789();
        $this->assertEquals($test->getLong(CF::NANO_OF_SECOND()), 123456789);
        $this->assertEquals($test->getLong(CF::MICRO_OF_SECOND()), 123456);
        $this->assertEquals($test->getLong(CF::MILLI_OF_SECOND()), 123);
        $this->assertEquals($test->getLong(CF::INSTANT_SECONDS()), 12345);
    }

    //-----------------------------------------------------------------------
    // query(TemporalQuery)
    //-----------------------------------------------------------------------
    function data_query()
    {
        return [
            [self::TEST_12345123456789(), TemporalQueries::chronology(), null],
            [self::TEST_12345123456789(), TemporalQueries::zoneId(), null],
            [self::TEST_12345123456789(), TemporalQueries::precision(), CU::NANOS()],
            [self::TEST_12345123456789(), TemporalQueries::zone(), null],
            [self::TEST_12345123456789(), TemporalQueries::offset(), null],
            [self::TEST_12345123456789(), TemporalQueries::localDate(), null],
            [self::TEST_12345123456789(), TemporalQueries::localTime(), null],
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
            self::TEST_12345123456789()->query(null);
        });
    }

//-----------------------------------------------------------------------
// adjustInto(Temporal)
//-----------------------------------------------------------------------
    function data_adjustInto()
    {
        return [
            [Instant::ofEpochSecond(10, 200), Instant::ofEpochSecond(20), Instant::ofEpochSecond(10, 200), null],
            [Instant::ofEpochSecond(10, -200), Instant::now(), Instant::ofEpochSecond(10, -200), null],
            [Instant::ofEpochSecond(-10), Instant::EPOCH(), Instant::ofEpochSecond(-10), null],
            [Instant::ofEpochSecond(10), Instant::MIN(), Instant::ofEpochSecond(10), null],
            [Instant::ofEpochSecond(10), Instant::MAX(), Instant::ofEpochSecond(10), null],

            [Instant::ofEpochSecond(10, 200), LocalDateTime::of(1970, 1, 1, 0, 0, 20)->toInstant(ZoneOffset::UTC()), Instant::ofEpochSecond(10, 200), null],
            [Instant::ofEpochSecond(10, 200), OffsetDateTime::of(1970, 1, 1, 0, 0, 20, 10, ZoneOffset::UTC()), OffsetDateTime::of(1970, 1, 1, 0, 0, 10, 200, ZoneOffset::UTC()), null],
            [Instant::ofEpochSecond(10, 200), OffsetDateTime::of(1970, 1, 1, 0, 0, 20, 10, self::OFFSET_PTWO()), OffsetDateTime::of(1970, 1, 1, 2, 0, 10, 200, self::OFFSET_PTWO()), null],
            [Instant::ofEpochSecond(10, 200), ZonedDateTime::of(1970, 1, 1, 0, 0, 20, 10, self::ZONE_PARIS()), ZonedDateTime::of(1970, 1, 1, 1, 0, 10, 200, self::ZONE_PARIS()), null],

            [Instant::ofEpochSecond(10, 200), LocalDateTime::of(1970, 1, 1, 0, 0, 20), null, DateTimeException::class],
            //[Instant::ofEpochSecond(10, 200), null, null, \NullException::class], // TODO
        ];
    }

    /**
     * @dataProvider data_adjustInto
     */

    public function test_adjustInto(Instant $test, $temporal, $expected, $expectedEx)
    {
        if ($expectedEx === null) {
            $result = $test->adjustInto($temporal);
            $this->assertEquals($result, $expected);
        } else {
            try {
                $result = $test->adjustInto($temporal);
                $this->fail();
            } catch (\Exception $ex) {
                $this->assertInstanceOf($expectedEx, $ex);
            }
        }
    }

//-----------------------------------------------------------------------
// with(TemporalAdjuster)
//-----------------------------------------------------------------------
    function data_with()
    {
        return [
            [Instant::ofEpochSecond(10, 200), Instant::ofEpochSecond(20), Instant::ofEpochSecond(20), null],
            [Instant::ofEpochSecond(10), Instant::ofEpochSecond(20, -100), Instant::ofEpochSecond(20, -100), null],
            [Instant::ofEpochSecond(-10), Instant::EPOCH(), Instant::ofEpochSecond(0), null],
            [Instant::ofEpochSecond(10), Instant::MIN(), Instant::MIN(), null],
            [Instant::ofEpochSecond(10), Instant::MAX(), Instant::MAX(), null],

            [Instant::ofEpochSecond(10, 200), LocalDateTime::of(1970, 1, 1, 0, 0, 20)->toInstant(ZoneOffset::UTC()), Instant::ofEpochSecond(20), null],

            [Instant::ofEpochSecond(10, 200), LocalDateTime::of(1970, 1, 1, 0, 0, 20), null, DateTimeException::class],
            //[Instant::ofEpochSecond(10, 200), null, null, \NullException::class], TODO
        ];
    }


    /**
     * @dataProvider data_with
     */
    public function test_with_temporalAdjuster(Instant $test, $adjuster, $expected, $expectedEx)
    {
        if ($expectedEx === null) {
            $result = $test->adjust($adjuster);
            $this->assertEquals($result, $expected);
        } else {
            try {
                $result = $test->adjust($adjuster);
                $this->fail();
            } catch (\Exception $ex) {
                $this->assertInstanceOf($expectedEx, $ex);
            }
        }
    }

//-----------------------------------------------------------------------
// with(TemporalField, long)
//-----------------------------------------------------------------------
    function data_with_longTemporalField()
    {
        return [
            [Instant::ofEpochSecond(10, 200), CF::INSTANT_SECONDS(), 100, Instant::ofEpochSecond(100, 200), null],
            [Instant::ofEpochSecond(10, 200), CF::INSTANT_SECONDS(), 0, Instant::ofEpochSecond(0, 200), null],
            [Instant::ofEpochSecond(10, 200), CF::INSTANT_SECONDS(), -100, Instant::ofEpochSecond(-100, 200), null],
            [Instant::ofEpochSecond(10, 200), CF::NANO_OF_SECOND(), 100, Instant::ofEpochSecond(10, 100), null],
            [Instant::ofEpochSecond(10, 200), CF::NANO_OF_SECOND(), 0, Instant::ofEpochSecond(10), null],
            [Instant::ofEpochSecond(10, 200), CF::MICRO_OF_SECOND(), 100, Instant::ofEpochSecond(10, 100 * 1000), null],
            [Instant::ofEpochSecond(10, 200), CF::MICRO_OF_SECOND(), 0, Instant::ofEpochSecond(10), null],
            [Instant::ofEpochSecond(10, 200), CF::MILLI_OF_SECOND(), 100, Instant::ofEpochSecond(10, 100 * 1000 * 1000), null],
            [Instant::ofEpochSecond(10, 200), CF::MILLI_OF_SECOND(), 0, Instant::ofEpochSecond(10), null],

            [Instant::ofEpochSecond(10, 200), CF::NANO_OF_SECOND(), 1000000000, null, DateTimeException::class],
            [Instant::ofEpochSecond(10, 200), CF::MICRO_OF_SECOND(), 1000000, null, DateTimeException::class],
            [Instant::ofEpochSecond(10, 200), CF::MILLI_OF_SECOND(), 1000, null, DateTimeException::class],

            [Instant::ofEpochSecond(10, 200), CF::SECOND_OF_MINUTE(), 1, null, DateTimeException::class],
            [Instant::ofEpochSecond(10, 200), CF::SECOND_OF_DAY(), 1, null, DateTimeException::class],
            [Instant::ofEpochSecond(10, 200), CF::OFFSET_SECONDS(), 1, null, DateTimeException::class],
            [Instant::ofEpochSecond(10, 200), CF::NANO_OF_DAY(), 1, null, DateTimeException::class],
            [Instant::ofEpochSecond(10, 200), CF::MINUTE_OF_HOUR(), 1, null, DateTimeException::class],
            [Instant::ofEpochSecond(10, 200), CF::MINUTE_OF_DAY(), 1, null, DateTimeException::class],
            [Instant::ofEpochSecond(10, 200), CF::MILLI_OF_DAY(), 1, null, DateTimeException::class],
            [Instant::ofEpochSecond(10, 200), CF::MICRO_OF_DAY(), 1, null, DateTimeException::class],


        ];
    }

    /**
     * @dataProvider data_with_longTemporalField
     */
    public function test_with_longTemporalField(Instant $test, TemporalField $field, $value, $expected, $expectedEx)
    {
        if ($expectedEx === null) {
            $result = $test->with($field, $value);
            $this->assertEquals($result, $expected);
        } else {
            try {
                $result = $test->with($field, $value);
                $this->fail();
            } catch (\Exception $ex) {
                $this->assertInstanceOf($expectedEx, $ex);
            }
        }
    }

//-----------------------------------------------------------------------
// truncated(TemporalUnit)
//-----------------------------------------------------------------------
    function data_truncatedToValid()
    {
        return [
            [Instant::ofEpochSecond(86400 + 3600 + 60 + 1, 123456789), CU::NANOS(), Instant::ofEpochSecond(86400 + 3600 + 60 + 1, 123456789)],
            [Instant::ofEpochSecond(86400 + 3600 + 60 + 1, 123456789), CU::MICROS(), Instant::ofEpochSecond(86400 + 3600 + 60 + 1, 123456000)],
            [Instant::ofEpochSecond(86400 + 3600 + 60 + 1, 123456789), CU::MILLIS(), Instant::ofEpochSecond(86400 + 3600 + 60 + 1, 123000000)],
            [Instant::ofEpochSecond(86400 + 3600 + 60 + 1, 123456789), CU::SECONDS(), Instant::ofEpochSecond(86400 + 3600 + 60 + 1, 0)],
            [Instant::ofEpochSecond(86400 + 3600 + 60 + 1, 123456789), CU::MINUTES(), Instant::ofEpochSecond(86400 + 3600 + 60, 0)],
            [Instant::ofEpochSecond(86400 + 3600 + 60 + 1, 123456789), CU::HOURS(), Instant::ofEpochSecond(86400 + 3600, 0)],
            [Instant::ofEpochSecond(86400 + 3600 + 60 + 1, 123456789), CU::DAYS(), Instant::ofEpochSecond(86400, 0)],

            [Instant::ofEpochSecond(86400 + 3600 + 60 + 1, 123456789), new NINETY_MINS(), Instant::ofEpochSecond(86400 + 0, 0)],
            [Instant::ofEpochSecond(86400 + 7200 + 60 + 1, 123456789), new NINETY_MINS(), Instant::ofEpochSecond(86400 + 5400, 0)],
            [Instant::ofEpochSecond(86400 + 10800 + 60 + 1, 123456789), new NINETY_MINS(), Instant::ofEpochSecond(86400 + 10800, 0)],
        ];
    }

    /**
     * @dataProvider data_truncatedToValid
     */
    public function test_truncatedTo_valid(Instant $input, TemporalUnit $unit, Instant $expected)
    {
        $this->assertEquals($input->truncatedTo($unit), $expected);
    }

    function data_truncatedToInvalid()
    {
        return [
            [Instant::ofEpochSecond(1, 123456789), new NINETY_FIVE_MINS()],
            [Instant::ofEpochSecond(1, 123456789), CU::WEEKS()],
            [Instant::ofEpochSecond(1, 123456789), CU::MONTHS()],
            [Instant::ofEpochSecond(1, 123456789), CU::YEARS()],
        ];
    }

    /**
     * @expectedException \Celest\DateTimeException
     * @dataProvider data_truncatedToInvalid
     */
    public function test_truncatedTo_invalid(Instant $input, TemporalUnit $unit)
    {
        $input->truncatedTo($unit);
    }

    public function test_truncatedTo_null()
    {
        TestHelper::assertNullException($this, function () {
            self::TEST_12345123456789()->truncatedTo(null);
        });
    }

    //-----------------------------------------------------------------------
    // plus(TemporalAmount)
    //-----------------------------------------------------------------------
    function data_plusTemporalAmount()
    {
        return [
            [CU::DAYS(), MockSimplePeriod::of(1, CU::DAYS()), 86401, 0],
            [CU::HOURS(), MockSimplePeriod::of(2, CU::HOURS()), 7201, 0],
            [CU::MINUTES(), MockSimplePeriod::of(4, CU::MINUTES()), 241, 0],
            [CU::SECONDS(), MockSimplePeriod::of(5, CU::SECONDS()), 6, 0],
            [CU::NANOS(), MockSimplePeriod::of(6, CU::NANOS()), 1, 6],
            [CU::DAYS(), MockSimplePeriod::of(10, CU::DAYS()), 864001, 0],
            [CU::HOURS(), MockSimplePeriod::of(11, CU::HOURS()), 39601, 0],
            [CU::MINUTES(), MockSimplePeriod::of(12, CU::MINUTES()), 721, 0],
            [CU::SECONDS(), MockSimplePeriod::of(13, CU::SECONDS()), 14, 0],
            [CU::NANOS(), MockSimplePeriod::of(14, CU::NANOS()), 1, 14],
            [CU::SECONDS(), Duration::ofSeconds(20, 40), 21, 40],
            [CU::NANOS(), Duration::ofSeconds(30, 300), 31, 300],
        ];
    }

    /**
     * @dataProvider data_plusTemporalAmount
     */
    public function test_plusTemporalAmount(TemporalUnit $unit, TemporalAmount $amount, $seconds, $nanos)
    {
        $inst = Instant::ofEpochMilli(1000);
        $actual = $inst->plusAmount($amount);
        $expected = Instant::ofEpochSecond($seconds, $nanos);
        $this->assertEquals($actual, $expected, "plus(TemporalAmount) failed");
    }

    function data_badPlusTemporalAmount()
    {
        return [
            [MockSimplePeriod::of(2, CU::YEARS())],
            [MockSimplePeriod::of(2, CU::MONTHS())],
        ];
    }

    /**
     * @expectedException \Celest\DateTimeException
     * @dataProvider data_badPlusTemporalAmount
     */
    public function test_badPlusTemporalAmount(TemporalAmount $amount)
    {
        $inst = Instant::ofEpochMilli(1000);
        $inst->plusAmount($amount);
    }

    //-----------------------------------------------------------------------
    function provider_plus()
    {
        return [
            [self::MIN_SECOND(), 0, -self::MIN_SECOND(), 0, 0, 0],

            [self::MIN_SECOND(), 0, 1, 0, self::MIN_SECOND() + 1, 0],
            [self::MIN_SECOND(), 0, 0, 500, self::MIN_SECOND(), 500],
            [self::MIN_SECOND(), 0, 0, 1000000000, self::MIN_SECOND() + 1, 0],

            [self::MIN_SECOND() + 1, 0, -1, 0, self::MIN_SECOND(), 0],
            [self::MIN_SECOND() + 1, 0, 0, -500, self::MIN_SECOND(), 999999500],
            [self::MIN_SECOND() + 1, 0, 0, -1000000000, self::MIN_SECOND(), 0],

            [-4, 666666667, -4, 666666667, -7, 333333334],
            [-4, 666666667, -3, 0, -7, 666666667],
            [-4, 666666667, -2, 0, -6, 666666667],
            [-4, 666666667, -1, 0, -5, 666666667],
            [-4, 666666667, -1, 333333334, -4, 1],
            [-4, 666666667, -1, 666666667, -4, 333333334],
            [-4, 666666667, -1, 999999999, -4, 666666666],
            [-4, 666666667, 0, 0, -4, 666666667],
            [-4, 666666667, 0, 1, -4, 666666668],
            [-4, 666666667, 0, 333333333, -3, 0],
            [-4, 666666667, 0, 666666666, -3, 333333333],
            [-4, 666666667, 1, 0, -3, 666666667],
            [-4, 666666667, 2, 0, -2, 666666667],
            [-4, 666666667, 3, 0, -1, 666666667],
            [-4, 666666667, 3, 333333333, 0, 0],

            [-3, 0, -4, 666666667, -7, 666666667],
            [-3, 0, -3, 0, -6, 0],
            [-3, 0, -2, 0, -5, 0],
            [-3, 0, -1, 0, -4, 0],
            [-3, 0, -1, 333333334, -4, 333333334],
            [-3, 0, -1, 666666667, -4, 666666667],
            [-3, 0, -1, 999999999, -4, 999999999],
            [-3, 0, 0, 0, -3, 0],
            [-3, 0, 0, 1, -3, 1],
            [-3, 0, 0, 333333333, -3, 333333333],
            [-3, 0, 0, 666666666, -3, 666666666],
            [-3, 0, 1, 0, -2, 0],
            [-3, 0, 2, 0, -1, 0],
            [-3, 0, 3, 0, 0, 0],
            [-3, 0, 3, 333333333, 0, 333333333],

            [-2, 0, -4, 666666667, -6, 666666667],
            [-2, 0, -3, 0, -5, 0],
            [-2, 0, -2, 0, -4, 0],
            [-2, 0, -1, 0, -3, 0],
            [-2, 0, -1, 333333334, -3, 333333334],
            [-2, 0, -1, 666666667, -3, 666666667],
            [-2, 0, -1, 999999999, -3, 999999999],
            [-2, 0, 0, 0, -2, 0],
            [-2, 0, 0, 1, -2, 1],
            [-2, 0, 0, 333333333, -2, 333333333],
            [-2, 0, 0, 666666666, -2, 666666666],
            [-2, 0, 1, 0, -1, 0],
            [-2, 0, 2, 0, 0, 0],
            [-2, 0, 3, 0, 1, 0],
            [-2, 0, 3, 333333333, 1, 333333333],

            [-1, 0, -4, 666666667, -5, 666666667],
            [-1, 0, -3, 0, -4, 0],
            [-1, 0, -2, 0, -3, 0],
            [-1, 0, -1, 0, -2, 0],
            [-1, 0, -1, 333333334, -2, 333333334],
            [-1, 0, -1, 666666667, -2, 666666667],
            [-1, 0, -1, 999999999, -2, 999999999],
            [-1, 0, 0, 0, -1, 0],
            [-1, 0, 0, 1, -1, 1],
            [-1, 0, 0, 333333333, -1, 333333333],
            [-1, 0, 0, 666666666, -1, 666666666],
            [-1, 0, 1, 0, 0, 0],
            [-1, 0, 2, 0, 1, 0],
            [-1, 0, 3, 0, 2, 0],
            [-1, 0, 3, 333333333, 2, 333333333],

            [-1, 666666667, -4, 666666667, -4, 333333334],
            [-1, 666666667, -3, 0, -4, 666666667],
            [-1, 666666667, -2, 0, -3, 666666667],
            [-1, 666666667, -1, 0, -2, 666666667],
            [-1, 666666667, -1, 333333334, -1, 1],
            [-1, 666666667, -1, 666666667, -1, 333333334],
            [-1, 666666667, -1, 999999999, -1, 666666666],
            [-1, 666666667, 0, 0, -1, 666666667],
            [-1, 666666667, 0, 1, -1, 666666668],
            [-1, 666666667, 0, 333333333, 0, 0],
            [-1, 666666667, 0, 666666666, 0, 333333333],
            [-1, 666666667, 1, 0, 0, 666666667],
            [-1, 666666667, 2, 0, 1, 666666667],
            [-1, 666666667, 3, 0, 2, 666666667],
            [-1, 666666667, 3, 333333333, 3, 0],

            [0, 0, -4, 666666667, -4, 666666667],
            [0, 0, -3, 0, -3, 0],
            [0, 0, -2, 0, -2, 0],
            [0, 0, -1, 0, -1, 0],
            [0, 0, -1, 333333334, -1, 333333334],
            [0, 0, -1, 666666667, -1, 666666667],
            [0, 0, -1, 999999999, -1, 999999999],
            [0, 0, 0, 0, 0, 0],
            [0, 0, 0, 1, 0, 1],
            [0, 0, 0, 333333333, 0, 333333333],
            [0, 0, 0, 666666666, 0, 666666666],
            [0, 0, 1, 0, 1, 0],
            [0, 0, 2, 0, 2, 0],
            [0, 0, 3, 0, 3, 0],
            [0, 0, 3, 333333333, 3, 333333333],

            [0, 333333333, -4, 666666667, -3, 0],
            [0, 333333333, -3, 0, -3, 333333333],
            [0, 333333333, -2, 0, -2, 333333333],
            [0, 333333333, -1, 0, -1, 333333333],
            [0, 333333333, -1, 333333334, -1, 666666667],
            [0, 333333333, -1, 666666667, 0, 0],
            [0, 333333333, -1, 999999999, 0, 333333332],
            [0, 333333333, 0, 0, 0, 333333333],
            [0, 333333333, 0, 1, 0, 333333334],
            [0, 333333333, 0, 333333333, 0, 666666666],
            [0, 333333333, 0, 666666666, 0, 999999999],
            [0, 333333333, 1, 0, 1, 333333333],
            [0, 333333333, 2, 0, 2, 333333333],
            [0, 333333333, 3, 0, 3, 333333333],
            [0, 333333333, 3, 333333333, 3, 666666666],

            [1, 0, -4, 666666667, -3, 666666667],
            [1, 0, -3, 0, -2, 0],
            [1, 0, -2, 0, -1, 0],
            [1, 0, -1, 0, 0, 0],
            [1, 0, -1, 333333334, 0, 333333334],
            [1, 0, -1, 666666667, 0, 666666667],
            [1, 0, -1, 999999999, 0, 999999999],
            [1, 0, 0, 0, 1, 0],
            [1, 0, 0, 1, 1, 1],
            [1, 0, 0, 333333333, 1, 333333333],
            [1, 0, 0, 666666666, 1, 666666666],
            [1, 0, 1, 0, 2, 0],
            [1, 0, 2, 0, 3, 0],
            [1, 0, 3, 0, 4, 0],
            [1, 0, 3, 333333333, 4, 333333333],

            [2, 0, -4, 666666667, -2, 666666667],
            [2, 0, -3, 0, -1, 0],
            [2, 0, -2, 0, 0, 0],
            [2, 0, -1, 0, 1, 0],
            [2, 0, -1, 333333334, 1, 333333334],
            [2, 0, -1, 666666667, 1, 666666667],
            [2, 0, -1, 999999999, 1, 999999999],
            [2, 0, 0, 0, 2, 0],
            [2, 0, 0, 1, 2, 1],
            [2, 0, 0, 333333333, 2, 333333333],
            [2, 0, 0, 666666666, 2, 666666666],
            [2, 0, 1, 0, 3, 0],
            [2, 0, 2, 0, 4, 0],
            [2, 0, 3, 0, 5, 0],
            [2, 0, 3, 333333333, 5, 333333333],

            [3, 0, -4, 666666667, -1, 666666667],
            [3, 0, -3, 0, 0, 0],
            [3, 0, -2, 0, 1, 0],
            [3, 0, -1, 0, 2, 0],
            [3, 0, -1, 333333334, 2, 333333334],
            [3, 0, -1, 666666667, 2, 666666667],
            [3, 0, -1, 999999999, 2, 999999999],
            [3, 0, 0, 0, 3, 0],
            [3, 0, 0, 1, 3, 1],
            [3, 0, 0, 333333333, 3, 333333333],
            [3, 0, 0, 666666666, 3, 666666666],
            [3, 0, 1, 0, 4, 0],
            [3, 0, 2, 0, 5, 0],
            [3, 0, 3, 0, 6, 0],
            [3, 0, 3, 333333333, 6, 333333333],

            [3, 333333333, -4, 666666667, 0, 0],
            [3, 333333333, -3, 0, 0, 333333333],
            [3, 333333333, -2, 0, 1, 333333333],
            [3, 333333333, -1, 0, 2, 333333333],
            [3, 333333333, -1, 333333334, 2, 666666667],
            [3, 333333333, -1, 666666667, 3, 0],
            [3, 333333333, -1, 999999999, 3, 333333332],
            [3, 333333333, 0, 0, 3, 333333333],
            [3, 333333333, 0, 1, 3, 333333334],
            [3, 333333333, 0, 333333333, 3, 666666666],
            [3, 333333333, 0, 666666666, 3, 999999999],
            [3, 333333333, 1, 0, 4, 333333333],
            [3, 333333333, 2, 0, 5, 333333333],
            [3, 333333333, 3, 0, 6, 333333333],
            [3, 333333333, 3, 333333333, 6, 666666666],

            [self::MAX_SECOND() - 1, 0, 1, 0, self::MAX_SECOND(), 0],
            [self::MAX_SECOND() - 1, 0, 0, 500, self::MAX_SECOND() - 1, 500],
            [self::MAX_SECOND() - 1, 0, 0, 1000000000, self::MAX_SECOND(), 0],

            [self::MAX_SECOND(), 0, -1, 0, self::MAX_SECOND() - 1, 0],
            [self::MAX_SECOND(), 0, 0, -500, self::MAX_SECOND() - 1, 999999500],
            [self::MAX_SECOND(), 0, 0, -1000000000, self::MAX_SECOND() - 1, 0],

            [self::MAX_SECOND(), 0, -self::MAX_SECOND(), 0, 0, 0],
        ];
    }

    /**
     * @dataProvider provider_plus
     */
    public function test_plus_Duration($seconds, $nanos, $otherSeconds, $otherNanos, $expectedSeconds, $expectedNanoOfSecond)
    {
        $i = Instant::ofEpochSecond($seconds, $nanos)->plusAmount(Duration::ofSeconds($otherSeconds, $otherNanos));
        $this->assertEquals($i->getEpochSecond(), $expectedSeconds);
        $this->assertEquals($i->getNano(), $expectedNanoOfSecond);
    }

    /**
     * @expectedException \Celest\DateTimeException
     */
    public function test_plus_Duration_overflowTooBig()
    {
        $i = Instant::ofEpochSecond(self::MAX_SECOND(), 999999999);
        $i->plusAmount(Duration::ofSeconds(0, 1));
    }

    /**
     * @expectedException \Celest\DateTimeException
     */
    public function test_plus_Duration_overflowTooSmall()
    {
        $i = Instant::ofEpochSecond(self::MIN_SECOND());
        $i->plusAmount(Duration::ofSeconds(-1, 999999999));
    }

    //-----------------------------------------------------------------------$a
    /**
     * @dataProvider provider_plus
     */
    public function test_plus_longTemporalUnit($seconds, $nanos, $otherSeconds, $otherNanos, $expectedSeconds, $expectedNanoOfSecond)
    {
        $i = Instant::ofEpochSecond($seconds, $nanos)->plus($otherSeconds, CU::SECONDS())->plus($otherNanos, CU::NANOS());
        $this->assertEquals($i->getEpochSecond(), $expectedSeconds);
        $this->assertEquals($i->getNano(), $expectedNanoOfSecond);
    }

    /**
     * @expectedException \Celest\DateTimeException
     */
    public function test_plus_longTemporalUnit_overflowTooBig()
    {
        $i = Instant::ofEpochSecond(self::MAX_SECOND(), 999999999);
        $i->plus(1, CU::NANOS());
    }

    /**
     * @expectedException \Celest\DateTimeException
     */
    public function test_plus_longTemporalUnit_overflowTooSmall()
    {
        $i = Instant::ofEpochSecond(self::MIN_SECOND());
        $i->plus(999999999, CU::NANOS());
        $i->plus(-1, CU::SECONDS());
    }

    //-----------------------------------------------------------------------
    function provider_plusSeconds_long()
    {
        return [
            [0, 0, 0, 0, 0],
            [0, 0, 1, 1, 0],
            [0, 0, -1, -1, 0],
            [0, 0, self::MAX_SECOND(), self::MAX_SECOND(), 0],
            [0, 0, self::MIN_SECOND(), self::MIN_SECOND(), 0],
            [1, 0, 0, 1, 0],
            [1, 0, 1, 2, 0],
            [1, 0, -1, 0, 0],
            [1, 0, self::MAX_SECOND() - 1, self::MAX_SECOND(), 0],
            [1, 0, self::MIN_SECOND(), self::MIN_SECOND() + 1, 0],
            [1, 1, 0, 1, 1],
            [1, 1, 1, 2, 1],
            [1, 1, -1, 0, 1],
            [1, 1, self::MAX_SECOND() - 1, self::MAX_SECOND(), 1],
            [1, 1, self::MIN_SECOND(), self::MIN_SECOND() + 1, 1],
            [-1, 1, 0, -1, 1],
            [-1, 1, 1, 0, 1],
            [-1, 1, -1, -2, 1],
            [-1, 1, self::MAX_SECOND(), self::MAX_SECOND() - 1, 1],
            [-1, 1, self::MIN_SECOND() + 1, self::MIN_SECOND(), 1],

            [self::MAX_SECOND(), 2, -self::MAX_SECOND(), 0, 2],
            [self::MIN_SECOND(), 2, -self::MIN_SECOND(), 0, 2],
        ];
    }

    /**
     * @dataProvider provider_plusSeconds_long
     */
    public function test_plusSeconds_long($seconds, $nanos, $amount, $expectedSeconds, $expectedNanoOfSecond)
    {
        $t = Instant::ofEpochSecond($seconds, $nanos);
        $t = $t->plusSeconds($amount);
        $this->assertEquals($t->getEpochSecond(), $expectedSeconds);
        $this->assertEquals($t->getNano(), $expectedNanoOfSecond);
    }

    /**
     * @expectedException \Celest\ArithmeticException
     */
    public function test_plusSeconds_long_overflowTooBig()
    {
        $t = Instant::ofEpochSecond(1, 0);
        $t->plusSeconds(Long::MAX_VALUE);
    }

    /**
     * @expectedException \Celest\ArithmeticException
     */
    public function test_plusSeconds_long_overflowTooSmall()
    {
        $t = Instant::ofEpochSecond(-1, 0);
        $t->plusSeconds(Long::MIN_VALUE);
    }

    //-----------------------------------------------------------------------
    function provider_plusMillis_long()
    {
        return [
            [0, 0, 0, 0, 0],
            [0, 0, 1, 0, 1000000],
            [0, 0, 999, 0, 999000000],
            [0, 0, 1000, 1, 0],
            [0, 0, 1001, 1, 1000000],
            [0, 0, 1999, 1, 999000000],
            [0, 0, 2000, 2, 0],
            [0, 0, -1, -1, 999000000],
            [0, 0, -999, -1, 1000000],
            [0, 0, -1000, -1, 0],
            [0, 0, -1001, -2, 999000000],
            [0, 0, -1999, -2, 1000000],

            [0, 1, 0, 0, 1],
            [0, 1, 1, 0, 1000001],
            [0, 1, 998, 0, 998000001],
            [0, 1, 999, 0, 999000001],
            [0, 1, 1000, 1, 1],
            [0, 1, 1998, 1, 998000001],
            [0, 1, 1999, 1, 999000001],
            [0, 1, 2000, 2, 1],
            [0, 1, -1, -1, 999000001],
            [0, 1, -2, -1, 998000001],
            [0, 1, -1000, -1, 1],
            [0, 1, -1001, -2, 999000001],

            [0, 1000000, 0, 0, 1000000],
            [0, 1000000, 1, 0, 2000000],
            [0, 1000000, 998, 0, 999000000],
            [0, 1000000, 999, 1, 0],
            [0, 1000000, 1000, 1, 1000000],
            [0, 1000000, 1998, 1, 999000000],
            [0, 1000000, 1999, 2, 0],
            [0, 1000000, 2000, 2, 1000000],
            [0, 1000000, -1, 0, 0],
            [0, 1000000, -2, -1, 999000000],
            [0, 1000000, -999, -1, 2000000],
            [0, 1000000, -1000, -1, 1000000],
            [0, 1000000, -1001, -1, 0],
            [0, 1000000, -1002, -2, 999000000],

            [0, 999999999, 0, 0, 999999999],
            [0, 999999999, 1, 1, 999999],
            [0, 999999999, 999, 1, 998999999],
            [0, 999999999, 1000, 1, 999999999],
            [0, 999999999, 1001, 2, 999999],
            [0, 999999999, -1, 0, 998999999],
            [0, 999999999, -1000, -1, 999999999],
            [0, 999999999, -1001, -1, 998999999],

            [0, 0, Long::MAX_VALUE, \intdiv(Long::MAX_VALUE, 1000), (Long::MAX_VALUE % 1000) * 1000000],
            [0, 0, Long::MIN_VALUE, \intdiv(Long::MIN_VALUE, 1000) - 1, (Long::MIN_VALUE % 1000) * 1000000 + 1000000000],
        ];
    }

    /**
     * @dataProvider provider_plusMillis_long
     */

    public function test_plusMillis_long($seconds, $nanos, $amount, $expectedSeconds, $expectedNanoOfSecond)
    {
        $t = Instant::ofEpochSecond($seconds, $nanos);
        $t = $t->plusMillis($amount);
        $this->assertEquals($t->getEpochSecond(), $expectedSeconds);
        $this->assertEquals($t->getNano(), $expectedNanoOfSecond);
    }

    /**
     * @dataProvider provider_plusMillis_long
     */
    public function test_plusMillis_long_oneMore($seconds, $nanos, $amount, $expectedSeconds, $expectedNanoOfSecond)
    {
        $t = Instant::ofEpochSecond($seconds + 1, $nanos);
        $t = $t->plusMillis($amount);
        $this->assertEquals($t->getEpochSecond(), $expectedSeconds + 1);
        $this->assertEquals($t->getNano(), $expectedNanoOfSecond);
    }

    /**
     * @dataProvider provider_plusMillis_long
     */
    public function test_plusMillis_long_minusOneLess($seconds, $nanos, $amount, $expectedSeconds, $expectedNanoOfSecond)
    {
        $t = Instant::ofEpochSecond($seconds - 1, $nanos);
        $t = $t->plusMillis($amount);
        $this->assertEquals($t->getEpochSecond(), $expectedSeconds - 1);
        $this->assertEquals($t->getNano(), $expectedNanoOfSecond);
    }


    public function test_plusMillis_long_max()
    {
        $t = Instant::ofEpochSecond(self::MAX_SECOND(), 998999999);
        $t = $t->plusMillis(1);
        $this->assertEquals($t->getEpochSecond(), self::MAX_SECOND());
        $this->assertEquals($t->getNano(), 999999999);
    }

    /**
     * @expectedException \Celest\DateTimeException
     */
    public function test_plusMillis_long_overflowTooBig()
    {
        $t = Instant::ofEpochSecond(self::MAX_SECOND(), 999000000);
        $t->plusMillis(1);
    }


    public function test_plusMillis_long_min()
    {
        $t = Instant::ofEpochSecond(self::MIN_SECOND(), 1000000);
        $t = $t->plusMillis(-1);
        $this->assertEquals($t->getEpochSecond(), self::MIN_SECOND());
        $this->assertEquals($t->getNano(), 0);
    }

    /**
     * @expectedException \Celest\DateTimeException
     */
    public function test_plusMillis_long_overflowTooSmall()
    {
        $t = Instant::ofEpochSecond(self::MIN_SECOND(), 0);
        $t->plusMillis(-1);
    }

    //-----------------------------------------------------------------------
    function provider_plusNanos_long()
    {
        return [
            [0, 0, 0, 0, 0],
            [0, 0, 1, 0, 1],
            [0, 0, 999999999, 0, 999999999],
            [0, 0, 1000000000, 1, 0],
            [0, 0, 1000000001, 1, 1],
            [0, 0, 1999999999, 1, 999999999],
            [0, 0, 2000000000, 2, 0],
            [0, 0, -1, -1, 999999999],
            [0, 0, -999999999, -1, 1],
            [0, 0, -1000000000, -1, 0],
            [0, 0, -1000000001, -2, 999999999],
            [0, 0, -1999999999, -2, 1],

            [1, 0, 0, 1, 0],
            [1, 0, 1, 1, 1],
            [1, 0, 999999999, 1, 999999999],
            [1, 0, 1000000000, 2, 0],
            [1, 0, 1000000001, 2, 1],
            [1, 0, 1999999999, 2, 999999999],
            [1, 0, 2000000000, 3, 0],
            [1, 0, -1, 0, 999999999],
            [1, 0, -999999999, 0, 1],
            [1, 0, -1000000000, 0, 0],
            [1, 0, -1000000001, -1, 999999999],
            [1, 0, -1999999999, -1, 1],

            [-1, 0, 0, -1, 0],
            [-1, 0, 1, -1, 1],
            [-1, 0, 999999999, -1, 999999999],
            [-1, 0, 1000000000, 0, 0],
            [-1, 0, 1000000001, 0, 1],
            [-1, 0, 1999999999, 0, 999999999],
            [-1, 0, 2000000000, 1, 0],
            [-1, 0, -1, -2, 999999999],
            [-1, 0, -999999999, -2, 1],
            [-1, 0, -1000000000, -2, 0],
            [-1, 0, -1000000001, -3, 999999999],
            [-1, 0, -1999999999, -3, 1],

            [1, 1, 0, 1, 1],
            [1, 1, 1, 1, 2],
            [1, 1, 999999998, 1, 999999999],
            [1, 1, 999999999, 2, 0],
            [1, 1, 1000000000, 2, 1],
            [1, 1, 1999999998, 2, 999999999],
            [1, 1, 1999999999, 3, 0],
            [1, 1, 2000000000, 3, 1],
            [1, 1, -1, 1, 0],
            [1, 1, -2, 0, 999999999],
            [1, 1, -1000000000, 0, 1],
            [1, 1, -1000000001, 0, 0],
            [1, 1, -1000000002, -1, 999999999],
            [1, 1, -2000000000, -1, 1],

            [1, 999999999, 0, 1, 999999999],
            [1, 999999999, 1, 2, 0],
            [1, 999999999, 999999999, 2, 999999998],
            [1, 999999999, 1000000000, 2, 999999999],
            [1, 999999999, 1000000001, 3, 0],
            [1, 999999999, -1, 1, 999999998],
            [1, 999999999, -1000000000, 0, 999999999],
            [1, 999999999, -1000000001, 0, 999999998],
            [1, 999999999, -1999999999, 0, 0],
            [1, 999999999, -2000000000, -1, 999999999],

            [self::MAX_SECOND(), 0, 999999999, self::MAX_SECOND(), 999999999],
            [self::MAX_SECOND() - 1, 0, 1999999999, self::MAX_SECOND(), 999999999],
            [self::MIN_SECOND(), 1, -1, self::MIN_SECOND(), 0],
            [self::MIN_SECOND() + 1, 1, -1000000001, self::MIN_SECOND(), 0],

            [0, 0, self::MAX_SECOND(), \intdiv(self::MAX_SECOND(), 1000000000), (int)(self::MAX_SECOND() % 1000000000)],
            [0, 0, self::MIN_SECOND(), \intdiv(self::MIN_SECOND(), 1000000000) - 1, (int)(self::MIN_SECOND() % 1000000000) + 1000000000],
        ];
    }

    /**
     * @dataProvider provider_plusNanos_long
     */
    public function test_plusNanos_long($seconds, $nanos, $amount, $expectedSeconds, $expectedNanoOfSecond)
    {
        $t = Instant::ofEpochSecond($seconds, $nanos);
        $t = $t->plusNanos($amount);
        $this->assertEquals($t->getEpochSecond(), $expectedSeconds);
        $this->assertEquals($t->getNano(), $expectedNanoOfSecond);
    }

    /**
     * @expectedException \Celest\DateTimeException
     */
    public function test_plusNanos_long_overflowTooBig()
    {
        $t = Instant::ofEpochSecond(self::MAX_SECOND(), 999999999);
        $t->plusNanos(1);
    }

    /**
     * @expectedException \Celest\DateTimeException
     */
    public function test_plusNanos_long_overflowTooSmall()
    {
        $t = Instant::ofEpochSecond(self::MIN_SECOND(), 0);
        $t->plusNanos(-1);
    }

    //-----------------------------------------------------------------------
    function provider_minus()
    {
        return [
            [self::MIN_SECOND(), 0, self::MIN_SECOND(), 0, 0, 0],

            [self::MIN_SECOND(), 0, -1, 0, self::MIN_SECOND() + 1, 0],
            [self::MIN_SECOND(), 0, 0, -500, self::MIN_SECOND(), 500],
            [self::MIN_SECOND(), 0, 0, -1000000000, self::MIN_SECOND() + 1, 0],

            [self::MIN_SECOND() + 1, 0, 1, 0, self::MIN_SECOND(), 0],
            [self::MIN_SECOND() + 1, 0, 0, 500, self::MIN_SECOND(), 999999500],
            [self::MIN_SECOND() + 1, 0, 0, 1000000000, self::MIN_SECOND(), 0],

            [-4, 666666667, -4, 666666667, 0, 0],
            [-4, 666666667, -3, 0, -1, 666666667],
            [-4, 666666667, -2, 0, -2, 666666667],
            [-4, 666666667, -1, 0, -3, 666666667],
            [-4, 666666667, -1, 333333334, -3, 333333333],
            [-4, 666666667, -1, 666666667, -3, 0],
            [-4, 666666667, -1, 999999999, -4, 666666668],
            [-4, 666666667, 0, 0, -4, 666666667],
            [-4, 666666667, 0, 1, -4, 666666666],
            [-4, 666666667, 0, 333333333, -4, 333333334],
            [-4, 666666667, 0, 666666666, -4, 1],
            [-4, 666666667, 1, 0, -5, 666666667],
            [-4, 666666667, 2, 0, -6, 666666667],
            [-4, 666666667, 3, 0, -7, 666666667],
            [-4, 666666667, 3, 333333333, -7, 333333334],

            [-3, 0, -4, 666666667, 0, 333333333],
            [-3, 0, -3, 0, 0, 0],
            [-3, 0, -2, 0, -1, 0],
            [-3, 0, -1, 0, -2, 0],
            [-3, 0, -1, 333333334, -3, 666666666],
            [-3, 0, -1, 666666667, -3, 333333333],
            [-3, 0, -1, 999999999, -3, 1],
            [-3, 0, 0, 0, -3, 0],
            [-3, 0, 0, 1, -4, 999999999],
            [-3, 0, 0, 333333333, -4, 666666667],
            [-3, 0, 0, 666666666, -4, 333333334],
            [-3, 0, 1, 0, -4, 0],
            [-3, 0, 2, 0, -5, 0],
            [-3, 0, 3, 0, -6, 0],
            [-3, 0, 3, 333333333, -7, 666666667],

            [-2, 0, -4, 666666667, 1, 333333333],
            [-2, 0, -3, 0, 1, 0],
            [-2, 0, -2, 0, 0, 0],
            [-2, 0, -1, 0, -1, 0],
            [-2, 0, -1, 333333334, -2, 666666666],
            [-2, 0, -1, 666666667, -2, 333333333],
            [-2, 0, -1, 999999999, -2, 1],
            [-2, 0, 0, 0, -2, 0],
            [-2, 0, 0, 1, -3, 999999999],
            [-2, 0, 0, 333333333, -3, 666666667],
            [-2, 0, 0, 666666666, -3, 333333334],
            [-2, 0, 1, 0, -3, 0],
            [-2, 0, 2, 0, -4, 0],
            [-2, 0, 3, 0, -5, 0],
            [-2, 0, 3, 333333333, -6, 666666667],

            [-1, 0, -4, 666666667, 2, 333333333],
            [-1, 0, -3, 0, 2, 0],
            [-1, 0, -2, 0, 1, 0],
            [-1, 0, -1, 0, 0, 0],
            [-1, 0, -1, 333333334, -1, 666666666],
            [-1, 0, -1, 666666667, -1, 333333333],
            [-1, 0, -1, 999999999, -1, 1],
            [-1, 0, 0, 0, -1, 0],
            [-1, 0, 0, 1, -2, 999999999],
            [-1, 0, 0, 333333333, -2, 666666667],
            [-1, 0, 0, 666666666, -2, 333333334],
            [-1, 0, 1, 0, -2, 0],
            [-1, 0, 2, 0, -3, 0],
            [-1, 0, 3, 0, -4, 0],
            [-1, 0, 3, 333333333, -5, 666666667],

            [-1, 666666667, -4, 666666667, 3, 0],
            [-1, 666666667, -3, 0, 2, 666666667],
            [-1, 666666667, -2, 0, 1, 666666667],
            [-1, 666666667, -1, 0, 0, 666666667],
            [-1, 666666667, -1, 333333334, 0, 333333333],
            [-1, 666666667, -1, 666666667, 0, 0],
            [-1, 666666667, -1, 999999999, -1, 666666668],
            [-1, 666666667, 0, 0, -1, 666666667],
            [-1, 666666667, 0, 1, -1, 666666666],
            [-1, 666666667, 0, 333333333, -1, 333333334],
            [-1, 666666667, 0, 666666666, -1, 1],
            [-1, 666666667, 1, 0, -2, 666666667],
            [-1, 666666667, 2, 0, -3, 666666667],
            [-1, 666666667, 3, 0, -4, 666666667],
            [-1, 666666667, 3, 333333333, -4, 333333334],

            [0, 0, -4, 666666667, 3, 333333333],
            [0, 0, -3, 0, 3, 0],
            [0, 0, -2, 0, 2, 0],
            [0, 0, -1, 0, 1, 0],
            [0, 0, -1, 333333334, 0, 666666666],
            [0, 0, -1, 666666667, 0, 333333333],
            [0, 0, -1, 999999999, 0, 1],
            [0, 0, 0, 0, 0, 0],
            [0, 0, 0, 1, -1, 999999999],
            [0, 0, 0, 333333333, -1, 666666667],
            [0, 0, 0, 666666666, -1, 333333334],
            [0, 0, 1, 0, -1, 0],
            [0, 0, 2, 0, -2, 0],
            [0, 0, 3, 0, -3, 0],
            [0, 0, 3, 333333333, -4, 666666667],

            [0, 333333333, -4, 666666667, 3, 666666666],
            [0, 333333333, -3, 0, 3, 333333333],
            [0, 333333333, -2, 0, 2, 333333333],
            [0, 333333333, -1, 0, 1, 333333333],
            [0, 333333333, -1, 333333334, 0, 999999999],
            [0, 333333333, -1, 666666667, 0, 666666666],
            [0, 333333333, -1, 999999999, 0, 333333334],
            [0, 333333333, 0, 0, 0, 333333333],
            [0, 333333333, 0, 1, 0, 333333332],
            [0, 333333333, 0, 333333333, 0, 0],
            [0, 333333333, 0, 666666666, -1, 666666667],
            [0, 333333333, 1, 0, -1, 333333333],
            [0, 333333333, 2, 0, -2, 333333333],
            [0, 333333333, 3, 0, -3, 333333333],
            [0, 333333333, 3, 333333333, -3, 0],

            [1, 0, -4, 666666667, 4, 333333333],
            [1, 0, -3, 0, 4, 0],
            [1, 0, -2, 0, 3, 0],
            [1, 0, -1, 0, 2, 0],
            [1, 0, -1, 333333334, 1, 666666666],
            [1, 0, -1, 666666667, 1, 333333333],
            [1, 0, -1, 999999999, 1, 1],
            [1, 0, 0, 0, 1, 0],
            [1, 0, 0, 1, 0, 999999999],
            [1, 0, 0, 333333333, 0, 666666667],
            [1, 0, 0, 666666666, 0, 333333334],
            [1, 0, 1, 0, 0, 0],
            [1, 0, 2, 0, -1, 0],
            [1, 0, 3, 0, -2, 0],
            [1, 0, 3, 333333333, -3, 666666667],

            [2, 0, -4, 666666667, 5, 333333333],
            [2, 0, -3, 0, 5, 0],
            [2, 0, -2, 0, 4, 0],
            [2, 0, -1, 0, 3, 0],
            [2, 0, -1, 333333334, 2, 666666666],
            [2, 0, -1, 666666667, 2, 333333333],
            [2, 0, -1, 999999999, 2, 1],
            [2, 0, 0, 0, 2, 0],
            [2, 0, 0, 1, 1, 999999999],
            [2, 0, 0, 333333333, 1, 666666667],
            [2, 0, 0, 666666666, 1, 333333334],
            [2, 0, 1, 0, 1, 0],
            [2, 0, 2, 0, 0, 0],
            [2, 0, 3, 0, -1, 0],
            [2, 0, 3, 333333333, -2, 666666667],

            [3, 0, -4, 666666667, 6, 333333333],
            [3, 0, -3, 0, 6, 0],
            [3, 0, -2, 0, 5, 0],
            [3, 0, -1, 0, 4, 0],
            [3, 0, -1, 333333334, 3, 666666666],
            [3, 0, -1, 666666667, 3, 333333333],
            [3, 0, -1, 999999999, 3, 1],
            [3, 0, 0, 0, 3, 0],
            [3, 0, 0, 1, 2, 999999999],
            [3, 0, 0, 333333333, 2, 666666667],
            [3, 0, 0, 666666666, 2, 333333334],
            [3, 0, 1, 0, 2, 0],
            [3, 0, 2, 0, 1, 0],
            [3, 0, 3, 0, 0, 0],
            [3, 0, 3, 333333333, -1, 666666667],

            [3, 333333333, -4, 666666667, 6, 666666666],
            [3, 333333333, -3, 0, 6, 333333333],
            [3, 333333333, -2, 0, 5, 333333333],
            [3, 333333333, -1, 0, 4, 333333333],
            [3, 333333333, -1, 333333334, 3, 999999999],
            [3, 333333333, -1, 666666667, 3, 666666666],
            [3, 333333333, -1, 999999999, 3, 333333334],
            [3, 333333333, 0, 0, 3, 333333333],
            [3, 333333333, 0, 1, 3, 333333332],
            [3, 333333333, 0, 333333333, 3, 0],
            [3, 333333333, 0, 666666666, 2, 666666667],
            [3, 333333333, 1, 0, 2, 333333333],
            [3, 333333333, 2, 0, 1, 333333333],
            [3, 333333333, 3, 0, 0, 333333333],
            [3, 333333333, 3, 333333333, 0, 0],

            [self::MAX_SECOND() - 1, 0, -1, 0, self::MAX_SECOND(), 0],
            [self::MAX_SECOND() - 1, 0, 0, -500, self::MAX_SECOND() - 1, 500],
            [self::MAX_SECOND() - 1, 0, 0, -1000000000, self::MAX_SECOND(), 0],

            [self::MAX_SECOND(), 0, 1, 0, self::MAX_SECOND() - 1, 0],
            [self::MAX_SECOND(), 0, 0, 500, self::MAX_SECOND() - 1, 999999500],
            [self::MAX_SECOND(), 0, 0, 1000000000, self::MAX_SECOND() - 1, 0],

            [self::MAX_SECOND(), 0, self::MAX_SECOND(), 0, 0, 0],
        ];
    }

    /**
     * @dataProvider provider_minus
     */

    public function test_minus_Duration($seconds, $nanos, $otherSeconds, $otherNanos, $expectedSeconds, $expectedNanoOfSecond)
    {
        $i = Instant::ofEpochSecond($seconds, $nanos)->minusAmount(Duration::ofSeconds($otherSeconds, $otherNanos));
        $this->assertEquals($i->getEpochSecond(), $expectedSeconds);
        $this->assertEquals($i->getNano(), $expectedNanoOfSecond);
    }

    /**
     * @expectedException \Celest\DateTimeException
     */
    public function test_minus_Duration_overflowTooSmall()
    {
        $i = Instant::ofEpochSecond(self::MIN_SECOND());
        $i->minusAmount(Duration::ofSeconds(0, 1));
    }

    /**
     * @expectedException \Celest\DateTimeException
     */
    public function test_minus_Duration_overflowTooBig()
    {
        $i = Instant::ofEpochSecond(self::MAX_SECOND(), 999999999);
        $i->minusAmount(Duration::ofSeconds(-1, 999999999));
    }

    //-----------------------------------------------------------------------
    /**
     * @dataProvider provider_minus
     */

    public function test_minus_longTemporalUnit($seconds, $nanos, $otherSeconds, $otherNanos, $expectedSeconds, $expectedNanoOfSecond)
    {
        $i = Instant::ofEpochSecond($seconds, $nanos)->minus($otherSeconds, CU::SECONDS())->minus($otherNanos, CU::NANOS());
        $this->assertEquals($i->getEpochSecond(), $expectedSeconds);
        $this->assertEquals($i->getNano(), $expectedNanoOfSecond);
    }

    /**
     * @expectedException \Celest\DateTimeException
     */
    public function test_minus_longTemporalUnit_overflowTooSmall()
    {
        $i = Instant::ofEpochSecond(self::MIN_SECOND());
        $i->minus(1, CU::NANOS());
    }

    /**
     * @expectedException \Celest\DateTimeException
     */
    public function test_minus_longTemporalUnit_overflowTooBig()
    {
        $i = Instant::ofEpochSecond(self::MAX_SECOND(), 999999999);
        $i->minus(999999999, CU::NANOS());
        $i->minus(-1, CU::SECONDS());
    }

    //-----------------------------------------------------------------------
    function provider_minusSeconds_long()
    {
        return [
            [0, 0, 0, 0, 0],
            [0, 0, 1, -1, 0],
            [0, 0, -1, 1, 0],
            [0, 0, -self::MIN_SECOND(), self::MIN_SECOND(), 0],
            [1, 0, 0, 1, 0],
            [1, 0, 1, 0, 0],
            [1, 0, -1, 2, 0],
            [1, 0, -self::MIN_SECOND() + 1, self::MIN_SECOND(), 0],
            [1, 1, 0, 1, 1],
            [1, 1, 1, 0, 1],
            [1, 1, -1, 2, 1],
            [1, 1, -self::MIN_SECOND(), self::MIN_SECOND() + 1, 1],
            [1, 1, -self::MIN_SECOND() + 1, self::MIN_SECOND(), 1],
            [-1, 1, 0, -1, 1],
            [-1, 1, 1, -2, 1],
            [-1, 1, -1, 0, 1],
            [-1, 1, -self::MAX_SECOND(), self::MAX_SECOND() - 1, 1],
            [-1, 1, -(self::MAX_SECOND() + 1), self::MAX_SECOND(), 1],

            [self::MIN_SECOND(), 2, self::MIN_SECOND(), 0, 2],
            [self::MIN_SECOND() + 1, 2, self::MIN_SECOND(), 1, 2],
            [self::MAX_SECOND() - 1, 2, self::MAX_SECOND(), -1, 2],
            [self::MAX_SECOND(), 2, self::MAX_SECOND(), 0, 2],
        ];
    }

    /**
     * @dataProvider provider_minusSeconds_long
     */

    public function test_minusSeconds_long($seconds, $nanos, $amount, $expectedSeconds, $expectedNanoOfSecond)
    {
        $i = Instant::ofEpochSecond($seconds, $nanos);
        $i = $i->minusSeconds($amount);
        $this->assertEquals($i->getEpochSecond(), $expectedSeconds);
        $this->assertEquals($i->getNano(), $expectedNanoOfSecond);
    }

    /**
     * @expectedException \Celest\ArithmeticException
     */
    public function test_minusSeconds_long_overflowTooBig()
    {
        $i = Instant::ofEpochSecond(1, 0);
        $i->minusSeconds(Long::MIN_VALUE + 1);
    }

    /**
     * @expectedException \Celest\ArithmeticException
     */
    public function test_minusSeconds_long_overflowTooSmall()
    {
        $i = Instant::ofEpochSecond(-2, 0);
        $i->minusSeconds(Long::MAX_VALUE);
    }

//-----------------------------------------------------------------------
    function provider_minusMillis_long()
    {
        return [
            [0, 0, 0, 0, 0],
            [0, 0, 1, -1, 999000000],
            [0, 0, 999, -1, 1000000],
            [0, 0, 1000, -1, 0],
            [0, 0, 1001, -2, 999000000],
            [0, 0, 1999, -2, 1000000],
            [0, 0, 2000, -2, 0],
            [0, 0, -1, 0, 1000000],
            [0, 0, -999, 0, 999000000],
            [0, 0, -1000, 1, 0],
            [0, 0, -1001, 1, 1000000],
            [0, 0, -1999, 1, 999000000],

            [0, 1, 0, 0, 1],
            [0, 1, 1, -1, 999000001],
            [0, 1, 998, -1, 2000001],
            [0, 1, 999, -1, 1000001],
            [0, 1, 1000, -1, 1],
            [0, 1, 1998, -2, 2000001],
            [0, 1, 1999, -2, 1000001],
            [0, 1, 2000, -2, 1],
            [0, 1, -1, 0, 1000001],
            [0, 1, -2, 0, 2000001],
            [0, 1, -1000, 1, 1],
            [0, 1, -1001, 1, 1000001],

            [0, 1000000, 0, 0, 1000000],
            [0, 1000000, 1, 0, 0],
            [0, 1000000, 998, -1, 3000000],
            [0, 1000000, 999, -1, 2000000],
            [0, 1000000, 1000, -1, 1000000],
            [0, 1000000, 1998, -2, 3000000],
            [0, 1000000, 1999, -2, 2000000],
            [0, 1000000, 2000, -2, 1000000],
            [0, 1000000, -1, 0, 2000000],
            [0, 1000000, -2, 0, 3000000],
            [0, 1000000, -999, 1, 0],
            [0, 1000000, -1000, 1, 1000000],
            [0, 1000000, -1001, 1, 2000000],
            [0, 1000000, -1002, 1, 3000000],

            [0, 999999999, 0, 0, 999999999],
            [0, 999999999, 1, 0, 998999999],
            [0, 999999999, 999, 0, 999999],
            [0, 999999999, 1000, -1, 999999999],
            [0, 999999999, 1001, -1, 998999999],
            [0, 999999999, -1, 1, 999999],
            [0, 999999999, -1000, 1, 999999999],
            [0, 999999999, -1001, 2, 999999],

            [0, 0, Long::MAX_VALUE, -\intdiv(Long::MAX_VALUE, 1000) - 1, (int)-(Long::MAX_VALUE % 1000) * 1000000 + 1000000000],
            [0, 0, Long::MIN_VALUE, -\intdiv(Long::MIN_VALUE, 1000), (int)-(Long::MIN_VALUE % 1000) * 1000000],
        ];
    }

    /**
     * @dataProvider provider_minusMillis_long
     */
    public function test_minusMillis_long($seconds, $nanos, $amount, $expectedSeconds, $expectedNanoOfSecond)
    {
        $i = Instant::ofEpochSecond($seconds, $nanos);
        $i = $i->minusMillis($amount);
        $this->assertEquals($i->getEpochSecond(), $expectedSeconds);
        $this->assertEquals($i->getNano(), $expectedNanoOfSecond);
    }

    /**
     * @dataProvider provider_minusMillis_long
     */

    public function test_minusMillis_long_oneMore($seconds, $nanos, $amount, $expectedSeconds, $expectedNanoOfSecond)
    {
        $i = Instant::ofEpochSecond($seconds + 1, $nanos);
        $i = $i->minusMillis($amount);
        $this->assertEquals($i->getEpochSecond(), $expectedSeconds + 1);
        $this->assertEquals($i->getNano(), $expectedNanoOfSecond);
    }

    /**
     * @dataProvider provider_minusMillis_long
     */

    public function test_minusMillis_long_minusOneLess($seconds, $nanos, $amount, $expectedSeconds, $expectedNanoOfSecond)
    {
        $i = Instant::ofEpochSecond($seconds - 1, $nanos);
        $i = $i->minusMillis($amount);
        $this->assertEquals($i->getEpochSecond(), $expectedSeconds - 1);
        $this->assertEquals($i->getNano(), $expectedNanoOfSecond);
    }


    public function test_minusMillis_long_max()
    {
        $i = Instant::ofEpochSecond(self::MAX_SECOND(), 998999999);
        $i = $i->minusMillis(-1);
        $this->assertEquals($i->getEpochSecond(), self::MAX_SECOND());
        $this->assertEquals($i->getNano(), 999999999);
    }

    /**
     * @expectedException \Celest\DateTimeException
     */
    public function test_minusMillis_long_overflowTooBig()
    {
        $i = Instant::ofEpochSecond(self::MAX_SECOND(), 999000000);
        $i->minusMillis(-1);
    }


    public function test_minusMillis_long_min()
    {
        $i = Instant::ofEpochSecond(self::MIN_SECOND(), 1000000);
        $i = $i->minusMillis(1);
        $this->assertEquals($i->getEpochSecond(), self::MIN_SECOND());
        $this->assertEquals($i->getNano(), 0);
    }

    /**
     * @expectedException \Celest\DateTimeException
     */
    public function test_minusMillis_long_overflowTooSmall()
    {
        $i = Instant::ofEpochSecond(self::MIN_SECOND(), 0);
        $i->minusMillis(1);
    }

    //-----------------------------------------------------------------------
    function provider_minusNanos_long()
    {
        return [
            [0, 0, 0, 0, 0],
            [0, 0, 1, -1, 999999999],
            [0, 0, 999999999, -1, 1],
            [0, 0, 1000000000, -1, 0],
            [0, 0, 1000000001, -2, 999999999],
            [0, 0, 1999999999, -2, 1],
            [0, 0, 2000000000, -2, 0],
            [0, 0, -1, 0, 1],
            [0, 0, -999999999, 0, 999999999],
            [0, 0, -1000000000, 1, 0],
            [0, 0, -1000000001, 1, 1],
            [0, 0, -1999999999, 1, 999999999],

            [1, 0, 0, 1, 0],
            [1, 0, 1, 0, 999999999],
            [1, 0, 999999999, 0, 1],
            [1, 0, 1000000000, 0, 0],
            [1, 0, 1000000001, -1, 999999999],
            [1, 0, 1999999999, -1, 1],
            [1, 0, 2000000000, -1, 0],
            [1, 0, -1, 1, 1],
            [1, 0, -999999999, 1, 999999999],
            [1, 0, -1000000000, 2, 0],
            [1, 0, -1000000001, 2, 1],
            [1, 0, -1999999999, 2, 999999999],

            [-1, 0, 0, -1, 0],
            [-1, 0, 1, -2, 999999999],
            [-1, 0, 999999999, -2, 1],
            [-1, 0, 1000000000, -2, 0],
            [-1, 0, 1000000001, -3, 999999999],
            [-1, 0, 1999999999, -3, 1],
            [-1, 0, 2000000000, -3, 0],
            [-1, 0, -1, -1, 1],
            [-1, 0, -999999999, -1, 999999999],
            [-1, 0, -1000000000, 0, 0],
            [-1, 0, -1000000001, 0, 1],
            [-1, 0, -1999999999, 0, 999999999],

            [1, 1, 0, 1, 1],
            [1, 1, 1, 1, 0],
            [1, 1, 999999998, 0, 3],
            [1, 1, 999999999, 0, 2],
            [1, 1, 1000000000, 0, 1],
            [1, 1, 1999999998, -1, 3],
            [1, 1, 1999999999, -1, 2],
            [1, 1, 2000000000, -1, 1],
            [1, 1, -1, 1, 2],
            [1, 1, -2, 1, 3],
            [1, 1, -1000000000, 2, 1],
            [1, 1, -1000000001, 2, 2],
            [1, 1, -1000000002, 2, 3],
            [1, 1, -2000000000, 3, 1],

            [1, 999999999, 0, 1, 999999999],
            [1, 999999999, 1, 1, 999999998],
            [1, 999999999, 999999999, 1, 0],
            [1, 999999999, 1000000000, 0, 999999999],
            [1, 999999999, 1000000001, 0, 999999998],
            [1, 999999999, -1, 2, 0],
            [1, 999999999, -1000000000, 2, 999999999],
            [1, 999999999, -1000000001, 3, 0],
            [1, 999999999, -1999999999, 3, 999999998],
            [1, 999999999, -2000000000, 3, 999999999],

            [self::MAX_SECOND(), 0, -999999999, self::MAX_SECOND(), 999999999],
            [self::MAX_SECOND() - 1, 0, -1999999999, self::MAX_SECOND(), 999999999],
            [self::MIN_SECOND(), 1, 1, self::MIN_SECOND(), 0],
            [self::MIN_SECOND() + 1, 1, 1000000001, self::MIN_SECOND(), 0],

            [0, 0, Long::MAX_VALUE, -\intdiv(Long::MAX_VALUE, 1000000000) - 1, (int)-(Long::MAX_VALUE % 1000000000) + 1000000000],
            [0, 0, Long::MIN_VALUE, -\intdiv(Long::MIN_VALUE, 1000000000), (int)-(Long::MIN_VALUE % 1000000000)],
        ];
    }

    /**
     * @dataProvider provider_minusNanos_long
     */

    public function test_minusNanos_long($seconds, $nanos, $amount, $expectedSeconds, $expectedNanoOfSecond)
    {
        $i = Instant::ofEpochSecond($seconds, $nanos);
        $i = $i->minusNanos($amount);
        $this->assertEquals($i->getEpochSecond(), $expectedSeconds);
        $this->assertEquals($i->getNano(), $expectedNanoOfSecond);
    }

    /**
     * @expectedException \Celest\DateTimeException
     */
    public function test_minusNanos_long_overflowTooBig()
    {
        $i = Instant::ofEpochSecond(self::MAX_SECOND(), 999999999);
        $i->minusNanos(-1);
    }

    /**
     * @expectedException \Celest\DateTimeException
     */
    public function test_minusNanos_long_overflowTooSmall()
    {
        $i = Instant::ofEpochSecond(self::MIN_SECOND(), 0);
        $i->minusNanos(1);
    }

    //-----------------------------------------------------------------------
    // until(Temporal, TemporalUnit)
    //-----------------------------------------------------------------------
    function data_periodUntilUnit()
    {
        return [
            [5, 650, -1, 650, CU::SECONDS(), -6],
            [5, 650, 0, 650, CU::SECONDS(), -5],
            [5, 650, 3, 650, CU::SECONDS(), -2],
            [5, 650, 4, 650, CU::SECONDS(), -1],
            [5, 650, 5, 650, CU::SECONDS(), 0],
            [5, 650, 6, 650, CU::SECONDS(), 1],
            [5, 650, 7, 650, CU::SECONDS(), 2],

            [5, 650, -1, 0, CU::SECONDS(), -6],
            [5, 650, 0, 0, CU::SECONDS(), -5],
            [5, 650, 3, 0, CU::SECONDS(), -2],
            [5, 650, 4, 0, CU::SECONDS(), -1],
            [5, 650, 5, 0, CU::SECONDS(), 0],
            [5, 650, 6, 0, CU::SECONDS(), 0],
            [5, 650, 7, 0, CU::SECONDS(), 1],

            [5, 650, -1, 950, CU::SECONDS(), -5],
            [5, 650, 0, 950, CU::SECONDS(), -4],
            [5, 650, 3, 950, CU::SECONDS(), -1],
            [5, 650, 4, 950, CU::SECONDS(), 0],
            [5, 650, 5, 950, CU::SECONDS(), 0],
            [5, 650, 6, 950, CU::SECONDS(), 1],
            [5, 650, 7, 950, CU::SECONDS(), 2],

            [5, 650, -1, 50, CU::SECONDS(), -6],
            [5, 650, 0, 50, CU::SECONDS(), -5],
            [5, 650, 4, 50, CU::SECONDS(), -1],
            [5, 650, 5, 50, CU::SECONDS(), 0],
            [5, 650, 6, 50, CU::SECONDS(), 0],
            [5, 650, 7, 50, CU::SECONDS(), 1],
            [5, 650, 8, 50, CU::SECONDS(), 2],

            [5, 650000000, -1, 650000000, CU::NANOS(), -6000000000],
            [5, 650000000, 0, 650000000, CU::NANOS(), -5000000000],
            [5, 650000000, 3, 650000000, CU::NANOS(), -2000000000],
            [5, 650000000, 4, 650000000, CU::NANOS(), -1000000000],
            [5, 650000000, 5, 650000000, CU::NANOS(), 0],
            [5, 650000000, 6, 650000000, CU::NANOS(), 1000000000],
            [5, 650000000, 7, 650000000, CU::NANOS(), 2000000000],

            [5, 650000000, -1, 0, CU::NANOS(), -6650000000],
            [5, 650000000, 0, 0, CU::NANOS(), -5650000000],
            [5, 650000000, 3, 0, CU::NANOS(), -2650000000],
            [5, 650000000, 4, 0, CU::NANOS(), -1650000000],
            [5, 650000000, 5, 0, CU::NANOS(), -650000000],
            [5, 650000000, 6, 0, CU::NANOS(), 350000000],
            [5, 650000000, 7, 0, CU::NANOS(), 1350000000],

            [5, 650000000, -1, 950000000, CU::NANOS(), -5700000000],
            [5, 650000000, 0, 950000000, CU::NANOS(), -4700000000],
            [5, 650000000, 3, 950000000, CU::NANOS(), -1700000000],
            [5, 650000000, 4, 950000000, CU::NANOS(), -700000000],
            [5, 650000000, 5, 950000000, CU::NANOS(), 300000000],
            [5, 650000000, 6, 950000000, CU::NANOS(), 1300000000],
            [5, 650000000, 7, 950000000, CU::NANOS(), 2300000000],

            [5, 650000000, -1, 50000000, CU::NANOS(), -6600000000],
            [5, 650000000, 0, 50000000, CU::NANOS(), -5600000000],
            [5, 650000000, 4, 50000000, CU::NANOS(), -1600000000],
            [5, 650000000, 5, 50000000, CU::NANOS(), -600000000],
            [5, 650000000, 6, 50000000, CU::NANOS(), 400000000],
            [5, 650000000, 7, 50000000, CU::NANOS(), 1400000000],
            [5, 650000000, 8, 50000000, CU::NANOS(), 2400000000],

            [0, 0, -60, 0, CU::MINUTES(), -1],
            [0, 0, -1, 999999999, CU::MINUTES(), 0],
            [0, 0, 59, 0, CU::MINUTES(), 0],
            [0, 0, 59, 999999999, CU::MINUTES(), 0],
            [0, 0, 60, 0, CU::MINUTES(), 1],
            [0, 0, 61, 0, CU::MINUTES(), 1],

            [0, 0, -3600, 0, CU::HOURS(), -1],
            [0, 0, -1, 999999999, CU::HOURS(), 0],
            [0, 0, 3599, 0, CU::HOURS(), 0],
            [0, 0, 3599, 999999999, CU::HOURS(), 0],
            [0, 0, 3600, 0, CU::HOURS(), 1],
            [0, 0, 3601, 0, CU::HOURS(), 1],

            [0, 0, -86400, 0, CU::DAYS(), -1],
            [0, 0, -1, 999999999, CU::DAYS(), 0],
            [0, 0, 86399, 0, CU::DAYS(), 0],
            [0, 0, 86399, 999999999, CU::DAYS(), 0],
            [0, 0, 86400, 0, CU::DAYS(), 1],
            [0, 0, 86401, 0, CU::DAYS(), 1],
        ];
    }

    /**
     * @dataProvider data_periodUntilUnit
     */
    public function test_until_TemporalUnit($seconds1, $nanos1, $seconds2, $nanos2, TemporalUnit $unit, $expected)
    {
        $i1 = Instant::ofEpochSecond($seconds1, $nanos1);
        $i2 = Instant::ofEpochSecond($seconds2, $nanos2);
        $amount = $i1->until($i2, $unit);
        $this->assertEquals($amount, $expected);
    }

    /**
     * @dataProvider data_periodUntilUnit
     */
    public function test_until_TemporalUnit_negated($seconds1, $nanos1, $seconds2, $nanos2, TemporalUnit $unit, $expected)
    {
        $i1 = Instant::ofEpochSecond($seconds1, $nanos1);
        $i2 = Instant::ofEpochSecond($seconds2, $nanos2);
        $amount = $i2->until($i1, $unit);
        $this->assertEquals($amount, -$expected);
    }

    /**
     * @dataProvider data_periodUntilUnit
     */
    public function test_until_TemporalUnit_between($seconds1, $nanos1, $seconds2, $nanos2, TemporalUnit $unit, $expected)
    {
        $i1 = Instant::ofEpochSecond($seconds1, $nanos1);
        $i2 = Instant::ofEpochSecond($seconds2, $nanos2);
        $amount = $unit->between($i1, $i2);
        $this->assertEquals($amount, $expected);
    }


    public function test_until_convertedType()
    {
        $start = Instant::ofEpochSecond(12, 3000);
        $end = $start->plusSeconds(2)->atOffset(ZoneOffset::ofHours(2));
        $this->assertEquals($start->until($end, CU::SECONDS()), 2);
    }

    /**
     * @expectedException \Celest\DateTimeException
     */
    public function test_until_invalidType()
    {
        $start = Instant::ofEpochSecond(12, 3000);
        $start->until(LocalTime::of(11, 30), CU::SECONDS());
    }

    /**
     * @expectedException \Celest\Temporal\UnsupportedTemporalTypeException
     */
    public function test_until_TemporalUnit_unsupportedUnit()
    {
        self::TEST_12345123456789()->until(self::TEST_12345123456789(), CU::MONTHS());
    }

    public function test_until_TemporalUnit_nullEnd()
    {
        TestHelper::assertNullException($this, function () {
            self::TEST_12345123456789()->until(null, CU::HOURS());
        });
    }

    public function test_until_TemporalUnit_nullUnit()
    {
        TestHelper::assertNullException($this, function () {
            self::TEST_12345123456789()->until(self::TEST_12345123456789(), null);
        });
    }

    //-----------------------------------------------------------------------
    // atOffset()
    //-----------------------------------------------------------------------

    /**
     * @group long
     */
    public function test_atOffset()
    {
        for ($i = 0; $i < (24 * 60 * 60); $i++) {
            $instant = Instant::ofEpochSecond($i);
            $test = $instant->atOffset(ZoneOffset::ofHours(1));
            $this->assertEquals($test->getYear(), 1970);
            $this->assertEquals($test->getMonthValue(), 1);
            $this->assertEquals($test->getDayOfMonth(), 1 + ($i >= 23 * 60 * 60 ? 1 : 0));
            $this->assertEquals($test->getHour(), (($i / (60 * 60)) + 1) % 24);
            $this->assertEquals($test->getMinute(), ($i / 60) % 60);
            $this->assertEquals($test->getSecond(), $i % 60);
        }
    }

    public function test_atOffset_null()
    {
        TestHelper::assertNullException($this, function () {
            self::TEST_12345123456789()->atOffset(null);
        });
    }

    //-----------------------------------------------------------------------
    // atZone()
    //-----------------------------------------------------------------------

    /**
     * @group long
     */
    public function test_atZone()
    {

        for ($i = 0; $i < (24 * 60 * 60); $i++) {
            $instant = Instant::ofEpochSecond($i);
            $test = $instant->atZone(ZoneOffset::ofHours(1));
            $this->assertEquals($test->getYear(), 1970);
            $this->assertEquals($test->getMonthValue(), 1);
            $this->assertEquals($test->getDayOfMonth(), 1 + ($i >= 23 * 60 * 60 ? 1 : 0));
            $this->assertEquals($test->getHour(), (($i / (60 * 60)) + 1) % 24);
            $this->assertEquals($test->getMinute(), ($i / 60) % 60);
            $this->assertEquals($test->getSecond(), $i % 60);
        }
    }

    public function test_atZone_null()
    {
        TestHelper::assertNullException($this, function () {
            self::TEST_12345123456789()->atZone(null);
        });
    }

    //-----------------------------------------------------------------------
    // toEpochMilli()
    //-----------------------------------------------------------------------

    public function test_toEpochMilli()
    {
        $this->assertEquals(Instant::ofEpochSecond(1, 1000000)->toEpochMilli(), 1001);
        $this->assertEquals(Instant::ofEpochSecond(1, 2000000)->toEpochMilli(), 1002);
        $this->assertEquals(Instant::ofEpochSecond(1, 567)->toEpochMilli(), 1000);
        $this->assertEquals(Instant::ofEpochSecond(\intdiv(Long::MAX_VALUE, 1000))->toEpochMilli(), \intdiv(Long::MAX_VALUE, 1000) * 1000);
        $this->assertEquals(Instant::ofEpochSecond(\intdiv(Long::MIN_VALUE, 1000))->toEpochMilli(), \intdiv(Long::MIN_VALUE, 1000) * 1000);
        $this->assertEquals(Instant::ofEpochSecond(0, -1000000)->toEpochMilli(), -1);
        $this->assertEquals(Instant::ofEpochSecond(0, 1000000)->toEpochMilli(), 1);
        $this->assertEquals(Instant::ofEpochSecond(0, 999999)->toEpochMilli(), 0);
        $this->assertEquals(Instant::ofEpochSecond(0, 1)->toEpochMilli(), 0);
        $this->assertEquals(Instant::ofEpochSecond(0, 0)->toEpochMilli(), 0);
        $this->assertEquals(Instant::ofEpochSecond(0, -1)->toEpochMilli(), -1);
        $this->assertEquals(Instant::ofEpochSecond(0, -999999)->toEpochMilli(), -1);
        $this->assertEquals(Instant::ofEpochSecond(0, -1000000)->toEpochMilli(), -1);
        $this->assertEquals(Instant::ofEpochSecond(0, -1000001)->toEpochMilli(), -2);
    }

    /**
     * @expectedException \Celest\ArithmeticException
     */
    public function test_toEpochMilli_tooBig()
    {
        Instant::ofEpochSecond(\intdiv(Long::MAX_VALUE, 1000) + 1)->toEpochMilli();
    }

    /**
     * @expectedException \Celest\ArithmeticException
     */
    public function test_toEpochMilli_tooSmall()
    {
        Instant::ofEpochSecond(\intdiv(Long::MIN_VALUE, 1000) - 1)->toEpochMilli();
    }

    public function data_toDateTime()
    {
        return [
            [Instant::ofEpochSecond(-1, 0)],
            [Instant::ofEpochSecond(1, 0)],
            [Instant::ofEpochSecond(1, 1337)],
            [Instant::ofEpochSecond(60, 0)],
            [Instant::ofEpochSecond(3600, 0)],
            [Instant::MAX()],
        ];
    }

    /**
     * @dataProvider data_toDateTime
     */
    public function test_toDateTime(Instant $instant)
    {
        if ((version_compare(PHP_VERSION, "5.6.24", "<") || version_compare(PHP_VERSION, "7.0.9", "<")) && $instant->getEpochSecond() < 0) {
            $this->markTestSkipped('Negative timestamps are not supported #66836');
        }

        $d = $instant->toDateTime();
        $this->assertEquals($instant->getEpochSecond(), $d->getTimestamp());
        $this->assertEquals(\intdiv($instant->getNano(), 1000), $d->format('u'));
        $this->assertEquals('UTC', $d->getTimezone()->getName());
    }

    public function data_toDateTime_error_data()
    {
        return [
            [Instant::ofEpochSecond(-1, 0)],
        ];
    }

    /**
     * @dataProvider data_toDateTime_error_data
     * @expectedException \Celest\DateTimeException
     */
    public function test_toDateTime_error(Instant $instant)
    {
        $instant->toDateTime();
        if (version_compare(PHP_VERSION, "5.6.24", ">=")
            || version_compare(PHP_VERSION, "7.0.9", ">=")
        ) {
            $this->markTestSkipped('Negative timestamps are supported #66836');
        }
    }
    /**
     * @group long
     */
    public function test_datetime_conversion()
    {
        for ($i = 0; $i < (24 * 60 * 60); $i++) {
            $i1= Instant::ofEpochSecond($i * 1000, $i * 1000);
            $dt = $i1->toDateTime();
            $i2 = Instant::ofDateTime($dt);
            $this->assertEquals($i1, $i2);
        }
    }

    //-----------------------------------------------------------------------
    // compareTo()
    //-----------------------------------------------------------------------

    public function test_comparisons()
    {
        $this->doTest_comparisons_Instant(
            Instant::ofEpochSecond(-2, 0),
            Instant::ofEpochSecond(-2, 999999998),
            Instant::ofEpochSecond(-2, 999999999),
            Instant::ofEpochSecond(-1, 0),
            Instant::ofEpochSecond(-1, 1),
            Instant::ofEpochSecond(-1, 999999998),
            Instant::ofEpochSecond(-1, 999999999),
            Instant::ofEpochSecond(0, 0),
            Instant::ofEpochSecond(0, 1),
            Instant::ofEpochSecond(0, 2),
            Instant::ofEpochSecond(0, 999999999),
            Instant::ofEpochSecond(1, 0),
            Instant::ofEpochSecond(2, 0)
        );
    }

    function doTest_comparisons_Instant(... $instants)
    {
        for ($i = 0; $i < count($instants); $i++) {
            $a = $instants[$i];
            for ($j = 0; $j < count($instants); $j++) {
                $b = $instants[$j];
                if ($i < $j) {
                    $this->assertEquals($a->compareTo($b) < 0, true, $a . " <=> " . $b);
                    $this->assertEquals($a->isBefore($b), true, $a . " <=> " . $b);
                    $this->assertEquals($a->isAfter($b), false, $a . " <=> " . $b);
                    $this->assertEquals($a->equals($b), false, $a . " <=> " . $b);
                } else if ($i > $j) {
                    $this->assertEquals($a->compareTo($b) > 0, true, $a . " <=> " . $b);
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
            $a = Instant::ofEpochSecond(0, 0);
            $a->compareTo(null);
        });

    }

    public function test_isBefore_ObjectNull()
    {
        TestHelper::assertNullException($this, function () {
            $a = Instant::ofEpochSecond(0, 0);
            $a->isBefore(null);
        });

    }

    public function test_isAfter_ObjectNull()
    {
        TestHelper::assertNullException($this, function () {
            $a = Instant::ofEpochSecond(0, 0);
            $a->isAfter(null);
        });

    }

    public function test_compareToNonInstant()
    {
        TestHelper::assertTypeError($this, function () {
            $c = Instant::ofEpochSecond(0);
            $c->compareTo(new \stdClass());
        });
    }

    //-----------------------------------------------------------------------
    // equals()
    //-----------------------------------------------------------------------

    public function test_equals()
    {
        $test5a = Instant::ofEpochSecond(5, 20);
        $test5b = Instant::ofEpochSecond(5, 20);
        $test5n = Instant::ofEpochSecond(5, 30);
        $test6 = Instant::ofEpochSecond(6, 20);

        $this->assertEquals($test5a->equals($test5a), true);
        $this->assertEquals($test5a->equals($test5b), true);
        $this->assertEquals($test5a->equals($test5n), false);
        $this->assertEquals($test5a->equals($test6), false);

        $this->assertEquals($test5b->equals($test5a), true);
        $this->assertEquals($test5b->equals($test5b), true);
        $this->assertEquals($test5b->equals($test5n), false);
        $this->assertEquals($test5b->equals($test6), false);

        $this->assertEquals($test5n->equals($test5a), false);
        $this->assertEquals($test5n->equals($test5b), false);
        $this->assertEquals($test5n->equals($test5n), true);
        $this->assertEquals($test5n->equals($test6), false);

        $this->assertEquals($test6->equals($test5a), false);
        $this->assertEquals($test6->equals($test5b), false);
        $this->assertEquals($test6->equals($test5n), false);
        $this->assertEquals($test6->equals($test6), true);
    }


    public function test_equals_null()
    {
        $test5 = Instant::ofEpochSecond(5, 20);
        $this->assertEquals($test5->equals(null), false);
    }


    public function test_equals_otherClass()
    {
        $test5 = Instant::ofEpochSecond(5, 20);
        $this->assertEquals($test5->equals(""), false);
    }

    //-----------------------------------------------------------------------
    // hashCode()
    //-----------------------------------------------------------------------

    /*public function test_hashCode()
    {
        $test5a = Instant::ofEpochSecond(5, 20);
        $test5b = Instant::ofEpochSecond(5, 20);
        $test5n = Instant::ofEpochSecond(5, 30);
        $test6 = Instant::ofEpochSecond(6, 20);

        $this->assertEquals($test5a->hashCode() == $test5a->hashCode(), true);
        $this->assertEquals($test5a->hashCode() == $test5b->hashCode(), true);
        $this->assertEquals($test5b->hashCode() == $test5b->hashCode(), true);

        $this->assertEquals($test5a->hashCode() == $test5n->hashCode(), false);
        $this->assertEquals($test5a->hashCode() == $test6->hashCode(), false);
    }*/

    //-----------------------------------------------------------------------
    // toString()
    //-----------------------------------------------------------------------
    function data_toString()
    {
        return [
            [Instant::ofEpochSecond(65, 567), "1970-01-01T00:01:05.000000567Z"],
            [Instant::ofEpochSecond(65, 560), "1970-01-01T00:01:05.000000560Z"],
            [Instant::ofEpochSecond(65, 560000), "1970-01-01T00:01:05.000560Z"],
            [Instant::ofEpochSecond(65, 560000000), "1970-01-01T00:01:05.560Z"],

            [Instant::ofEpochSecond(1, 0), "1970-01-01T00:00:01Z"],
            [Instant::ofEpochSecond(60, 0), "1970-01-01T00:01:00Z"],
            [Instant::ofEpochSecond(3600, 0), "1970-01-01T01:00:00Z"],
            [Instant::ofEpochSecond(-1, 0), "1969-12-31T23:59:59Z"],

            [LocalDateTime::of(0, 1, 2, 0, 0)->toInstant(ZoneOffset::UTC()), "0000-01-02T00:00:00Z"],
            [LocalDateTime::of(0, 1, 1, 12, 30)->toInstant(ZoneOffset::UTC()), "0000-01-01T12:30:00Z"],
            [LocalDateTime::of(0, 1, 1, 0, 0, 0, 1)->toInstant(ZoneOffset::UTC()), "0000-01-01T00:00:00.000000001Z"],
            [LocalDateTime::of(0, 1, 1, 0, 0)->toInstant(ZoneOffset::UTC()), "0000-01-01T00:00:00Z"],

            [LocalDateTime::of(-1, 12, 31, 23, 59, 59, 999999999)->toInstant(ZoneOffset::UTC()), "-0001-12-31T23:59:59.999999999Z"],
            [LocalDateTime::of(-1, 12, 31, 12, 30)->toInstant(ZoneOffset::UTC()), "-0001-12-31T12:30:00Z"],
            [LocalDateTime::of(-1, 12, 30, 12, 30)->toInstant(ZoneOffset::UTC()), "-0001-12-30T12:30:00Z"],

            [LocalDateTime::of(-9999, 1, 2, 12, 30)->toInstant(ZoneOffset::UTC()), "-9999-01-02T12:30:00Z"],
            [LocalDateTime::of(-9999, 1, 1, 12, 30)->toInstant(ZoneOffset::UTC()), "-9999-01-01T12:30:00Z"],
            [LocalDateTime::of(-9999, 1, 1, 0, 0)->toInstant(ZoneOffset::UTC()), "-9999-01-01T00:00:00Z"],

            [LocalDateTime::of(-10000, 12, 31, 23, 59, 59, 999999999)->toInstant(ZoneOffset::UTC()), "-10000-12-31T23:59:59.999999999Z"],
            [LocalDateTime::of(-10000, 12, 31, 12, 30)->toInstant(ZoneOffset::UTC()), "-10000-12-31T12:30:00Z"],
            [LocalDateTime::of(-10000, 12, 30, 12, 30)->toInstant(ZoneOffset::UTC()), "-10000-12-30T12:30:00Z"],
            [LocalDateTime::of(-15000, 12, 31, 12, 30)->toInstant(ZoneOffset::UTC()), "-15000-12-31T12:30:00Z"],

            [LocalDateTime::of(-19999, 1, 2, 12, 30)->toInstant(ZoneOffset::UTC()), "-19999-01-02T12:30:00Z"],
            [LocalDateTime::of(-19999, 1, 1, 12, 30)->toInstant(ZoneOffset::UTC()), "-19999-01-01T12:30:00Z"],
            [LocalDateTime::of(-19999, 1, 1, 0, 0)->toInstant(ZoneOffset::UTC()), "-19999-01-01T00:00:00Z"],

            [LocalDateTime::of(-20000, 12, 31, 23, 59, 59, 999999999)->toInstant(ZoneOffset::UTC()), "-20000-12-31T23:59:59.999999999Z"],
            [LocalDateTime::of(-20000, 12, 31, 12, 30)->toInstant(ZoneOffset::UTC()), "-20000-12-31T12:30:00Z"],
            [LocalDateTime::of(-20000, 12, 30, 12, 30)->toInstant(ZoneOffset::UTC()), "-20000-12-30T12:30:00Z"],
            [LocalDateTime::of(-25000, 12, 31, 12, 30)->toInstant(ZoneOffset::UTC()), "-25000-12-31T12:30:00Z"],

            [LocalDateTime::of(9999, 12, 30, 12, 30)->toInstant(ZoneOffset::UTC()), "9999-12-30T12:30:00Z"],
            [LocalDateTime::of(9999, 12, 31, 12, 30)->toInstant(ZoneOffset::UTC()), "9999-12-31T12:30:00Z"],
            [LocalDateTime::of(9999, 12, 31, 23, 59, 59, 999999999)->toInstant(ZoneOffset::UTC()), "9999-12-31T23:59:59.999999999Z"],

            [LocalDateTime::of(10000, 1, 1, 0, 0)->toInstant(ZoneOffset::UTC()), "+10000-01-01T00:00:00Z"],
            [LocalDateTime::of(10000, 1, 1, 12, 30)->toInstant(ZoneOffset::UTC()), "+10000-01-01T12:30:00Z"],
            [LocalDateTime::of(10000, 1, 2, 12, 30)->toInstant(ZoneOffset::UTC()), "+10000-01-02T12:30:00Z"],
            [LocalDateTime::of(15000, 12, 31, 12, 30)->toInstant(ZoneOffset::UTC()), "+15000-12-31T12:30:00Z"],

            [LocalDateTime::of(19999, 12, 30, 12, 30)->toInstant(ZoneOffset::UTC()), "+19999-12-30T12:30:00Z"],
            [LocalDateTime::of(19999, 12, 31, 12, 30)->toInstant(ZoneOffset::UTC()), "+19999-12-31T12:30:00Z"],
            [LocalDateTime::of(19999, 12, 31, 23, 59, 59, 999999999)->toInstant(ZoneOffset::UTC()), "+19999-12-31T23:59:59.999999999Z"],

            [LocalDateTime::of(20000, 1, 1, 0, 0)->toInstant(ZoneOffset::UTC()), "+20000-01-01T00:00:00Z"],
            [LocalDateTime::of(20000, 1, 1, 12, 30)->toInstant(ZoneOffset::UTC()), "+20000-01-01T12:30:00Z"],
            [LocalDateTime::of(20000, 1, 2, 12, 30)->toInstant(ZoneOffset::UTC()), "+20000-01-02T12:30:00Z"],
            [LocalDateTime::of(25000, 12, 31, 12, 30)->toInstant(ZoneOffset::UTC()), "+25000-12-31T12:30:00Z"],

            [LocalDateTime::of(-999999999, 1, 1, 12, 30)->toInstant(ZoneOffset::UTC())->minus(1, CU::DAYS()), "-1000000000-12-31T12:30:00Z"],
            [LocalDateTime::of(999999999, 12, 31, 12, 30)->toInstant(ZoneOffset::UTC())->plus(1, CU::DAYS()), "+1000000000-01-01T12:30:00Z"],

            [Instant::MIN(), "-1000000000-01-01T00:00:00Z"],
            [Instant::MAX(), "+1000000000-12-31T23:59:59.999999999Z"],
        ];
    }

    /**
     * @dataProvider  data_toString
     */
    public function test_toString(Instant $instant, $expected)
    {
        $this->assertEquals($expected, $instant->__toString());
    }

    /**
     * @dataProvider  data_toString
     */

    public function test_parse(Instant $instant, $text)
    {
        $this->assertEquals($instant, Instant::parse($text));
    }

    /**
     * @dataProvider  data_toString
     */

    public function test_parseLowercase(Instant $instant, $text)
    {
        $this->assertEquals($instant, Instant::parse(strtolower($text)));
    }
}
