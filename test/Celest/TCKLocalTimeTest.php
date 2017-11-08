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

use Celest\Format\DateTimeFormatter;
use Celest\Format\ResolverStyle;
use Celest\Helper\Long;
use Celest\Helper\Math;
use Celest\Temporal\ChronoField as CF;
use Celest\Temporal\ChronoUnit as CU;
use Celest\Temporal\FieldValues;
use Celest\Temporal\JulianFields;
use Celest\Temporal\Temporal;
use Celest\Temporal\TemporalAccessor;
use Celest\Temporal\TemporalAdjusters;
use Celest\Temporal\TemporalField;
use Celest\Temporal\TemporalQueries;
use Celest\Temporal\TemporalQuery;
use Celest\Temporal\TemporalUnit;
use Exception;
use PHPUnit\Framework\TestCase;

class TemporalField_notChronoField implements TemporalField
{
    private $_this;
    private $result;
    private $base;

    public function __construct(TestCase $_this, $base, $result)
    {
        $this->_this = $_this;
        $this->result = $result;
        $this->base = $base;
    }

    public function rangeRefinedBy(TemporalAccessor $temporal)
    {
        throw new \LogicException();
    }

    public function range()
    {
        return null;
    }

    public function isTimeBased()
    {
        throw new \LogicException();
    }

    public function isSupportedBy(TemporalAccessor $temporal)
    {
        throw new \LogicException();
    }

    public function isDateBased()
    {
        throw new \LogicException();
    }

    public function getRangeUnit()
    {
        throw new \LogicException();
    }

    public function getFrom(TemporalAccessor $temporal)
    {
        throw new \LogicException();
    }

    public function getBaseUnit()
    {
        throw new \LogicException();
    }

    public function adjustInto(Temporal $temporal, $newValue)
    {
        $this->_this->assertEquals($temporal, $this->base);
        $this->_this->assertEquals($newValue, 12);
        return $this->result;
    }

    public function getDisplayName(Locale $locale)
    {
        throw new \LogicException();
    }

    public function resolve(
        FieldValues $fieldValues,
        TemporalAccessor $partialTemporal,
        ResolverStyle $resolverStyle)
    {
        throw new \LogicException();
    }

    public function __toString()
    {
        throw new \LogicException();
    }
}

class TCKLocalTimeTest extends AbstractDateTimeTest
{

    private static function OFFSET_PTWO()
    {
        return ZoneOffset::ofHours(2);
    }

    private static function ZONE_PARIS()
    {
        return ZoneId::of("Europe/Paris");
    }

    private static function TEST_123040987654321()
    {
        return LocalTime::of(12, 30, 40, 987654321);
    }

    private static function INVALID_UNITS()
    {
        return [CU::DAYS(), CU::FOREVER()];
    }

//-----------------------------------------------------------------------
    protected function samples()
    {
        return [self::TEST_123040987654321(), LocalTime::MIN(), LocalTime::MAX(), LocalTime::MIDNIGHT(), LocalTime::NOON()];
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

    private function check(LocalTime $test, $h, $m, $s, $n)
    {
        $this->assertEquals($test->getHour(), $h);
        $this->assertEquals($test->getMinute(), $m);
        $this->assertEquals($test->getSecond(), $s);
        $this->assertEquals($test->getNano(), $n);
        $this->assertEquals($test, $test);
        $this->assertEquals(LocalTime::of($h, $m, $s, $n), $test);
    }

//-----------------------------------------------------------------------
// constants
//-----------------------------------------------------------------------

    public function test_constant_MIDNIGHT()
    {
        $this->check(LocalTime::MIDNIGHT(), 0, 0, 0, 0);
    }


    public function test_constant_MIDDAY()
    {
        $this->check(LocalTime::NOON(), 12, 0, 0, 0);
    }


    public function test_constant_MIN()
    {
        $this->check(LocalTime::MIN(), 0, 0, 0, 0);
    }


    public function test_constant_MAX()
    {
        $this->check(LocalTime::MAX(), 23, 59, 59, 999999999);
    }

//-----------------------------------------------------------------------
// now(ZoneId)
//-----------------------------------------------------------------------
    public function test_now_ZoneId_nullZoneId()
    {
        TestHelper::assertNullException($this, function () {
            LocalTime::nowIn(null);
        });
    }


    public function test_now_ZoneId()
    {
        $zone = ZoneId::of("UTC+01:02:03");
        $expected = LocalTime::nowOf(Clock::system($zone));
        $test = LocalTime::nowIn($zone);
        for ($i = 0; $i < 100; $i++) {
            if ($expected->equals($test)) {
                $this->assertTrue(true);
                return;
            }
            $expected = LocalTime::nowOf(Clock::system($zone));
            $test = LocalTime::nowIn($zone);
        }
        $this->assertEquals($test, $expected);
    }

    //-----------------------------------------------------------------------
    // now(Clock)
    //-----------------------------------------------------------------------
    public function test_now_Clock_nullClock()
    {
        TestHelper::assertNullException($this, function () {
            LocalTime::nowOf(null);
        });
    }

    /**
     * @group long
     */
    public function test_now_Clock_allSecsInDay()
    {
        for ($i = 0; $i < (2 * 24 * 60 * 60); $i++) {
            $instant = Instant::ofEpochSecond($i, 8);
            $clock = Clock::fixed($instant, ZoneOffset::UTC());
            $test = LocalTime::nowOf($clock);
            $this->assertEquals($test->getHour(), ($i / (60 * 60)) % 24);
            $this->assertEquals($test->getMinute(), ($i / 60) % 60);
            $this->assertEquals($test->getSecond(), $i % 60);
            $this->assertEquals($test->getNano(), 8);
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
            $test = LocalTime::nowOf($clock);
            $this->assertEquals($test->getHour(), (($i + 24 * 60 * 60) / (60 * 60)) % 24);
            $this->assertEquals($test->getMinute(), (($i + 24 * 60 * 60) / 60) % 60);
            $this->assertEquals($test->getSecond(), ($i + 24 * 60 * 60) % 60);
            $this->assertEquals($test->getNano(), 8);
        }
    }

    //-----------------------------------------------------------------------

    public function test_now_Clock_max()
    {
        $clock = Clock::fixed(Instant::MAX(), ZoneOffset::UTC());
        $test = LocalTime::nowOf($clock);
        $this->assertEquals($test->getHour(), 23);
        $this->assertEquals($test->getMinute(), 59);
        $this->assertEquals($test->getSecond(), 59);
        $this->assertEquals($test->getNano(), 999999999);
    }


    public function test_now_Clock_min()
    {
        $clock = Clock::fixed(Instant::MIN(), ZoneOffset::UTC());
        $test = LocalTime::nowOf($clock);
        $this->assertEquals($test->getHour(), 0);
        $this->assertEquals($test->getMinute(), 0);
        $this->assertEquals($test->getSecond(), 0);
        $this->assertEquals($test->getNano(), 0);
    }

    //-----------------------------------------------------------------------
    // of() factories
    //-----------------------------------------------------------------------

    public function test_factory_time_2ints()
    {
        $test = LocalTime::of(12, 30);
        $this->check($test, 12, 30, 0, 0);
    }

    /**
     * @expectedException \Celest\DateTimeException
     */
    public function test_factory_time_2ints_hourTooLow()
    {
        LocalTime::of(-1, 0);
    }

    /**
     * @expectedException \Celest\DateTimeException
     */

    public function test_factory_time_2ints_hourTooHigh()
    {
        LocalTime::of(24, 0);
    }

    /**
     * @expectedException \Celest\DateTimeException
     */

    public function test_factory_time_2ints_minuteTooLow()
    {
        LocalTime::of(0, -1);
    }

    /**
     * @expectedException \Celest\DateTimeException
     */

    public function test_factory_time_2ints_minuteTooHigh()
    {
        LocalTime::of(0, 60);
    }

    //-----------------------------------------------------------------------

    public function test_factory_time_3ints()
    {
        $test = LocalTime::of(12, 30, 40);
        $this->check($test, 12, 30, 40, 0);
    }

    /**
     * @expectedException \Celest\DateTimeException
     */

    public function test_factory_time_3ints_hourTooLow()
    {
        LocalTime::of(-1, 0, 0);
    }

    /**
     * @expectedException \Celest\DateTimeException
     */

    public function test_factory_time_3ints_hourTooHigh()
    {
        LocalTime::of(24, 0, 0);
    }

    /**
     * @expectedException \Celest\DateTimeException
     */

    public function test_factory_time_3ints_minuteTooLow()
    {
        LocalTime::of(0, -1, 0);
    }

    /**
     * @expectedException \Celest\DateTimeException
     */

    public function test_factory_time_3ints_minuteTooHigh()
    {
        LocalTime::of(0, 60, 0);
    }

    /**
     * @expectedException \Celest\DateTimeException
     */

    public function test_factory_time_3ints_secondTooLow()
    {
        LocalTime::of(0, 0, -1);
    }

    /**
     * @expectedException \Celest\DateTimeException
     */

    public function test_factory_time_3ints_secondTooHigh()
    {
        LocalTime::of(0, 0, 60);
    }

    //-----------------------------------------------------------------------

    public function test_factory_time_4ints()
    {
        $test = LocalTime::of(12, 30, 40, 987654321);
        $this->check($test, 12, 30, 40, 987654321);
        $test = LocalTime::of(12, 0, 40, 987654321);
        $this->check($test, 12, 0, 40, 987654321);
    }

    /**
     * @expectedException \Celest\DateTimeException
     */

    public function test_factory_time_4ints_hourTooLow()
    {
        LocalTime::of(-1, 0, 0, 0);
    }

    /**
     * @expectedException \Celest\DateTimeException
     */

    public function test_factory_time_4ints_hourTooHigh()
    {
        LocalTime::of(24, 0, 0, 0);
    }

    /**
     * @expectedException \Celest\DateTimeException
     */

    public function test_factory_time_4ints_minuteTooLow()
    {
        LocalTime::of(0, -1, 0, 0);
    }

    /**
     * @expectedException \Celest\DateTimeException
     */

    public function test_factory_time_4ints_minuteTooHigh()
    {
        LocalTime::of(0, 60, 0, 0);
    }

    /**
     * @expectedException \Celest\DateTimeException
     */

    public function test_factory_time_4ints_secondTooLow()
    {
        LocalTime::of(0, 0, -1, 0);
    }

    /**
     * @expectedException \Celest\DateTimeException
     */

    public function test_factory_time_4ints_secondTooHigh()
    {
        LocalTime::of(0, 0, 60, 0);
    }

    /**
     * @expectedException \Celest\DateTimeException
     */

    public function test_factory_time_4ints_nanoTooLow()
    {
        LocalTime::of(0, 0, 0, -1);
    }

    /**
     * @expectedException \Celest\DateTimeException
     */

    public function test_factory_time_4ints_nanoTooHigh()
    {
        LocalTime::of(0, 0, 0, 1000000000);
    }

    //-----------------------------------------------------------------------
    // ofSecondOfDay(long)
    //-----------------------------------------------------------------------

    public function test_factory_ofSecondOfDay()
    {
        $localTime = LocalTime::ofSecondOfDay(2 * 60 * 60 + 17 * 60 + 23);
        $this->check($localTime, 2, 17, 23, 0);
    }

    /**
     * @expectedException \Celest\DateTimeException
     */

    public function test_factory_ofSecondOfDay_tooLow()
    {
        LocalTime::ofSecondOfDay(-1);
    }

    /**
     * @expectedException \Celest\DateTimeException
     */

    public function test_factory_ofSecondOfDay_tooHigh()
    {
        LocalTime::ofSecondOfDay(24 * 60 * 60);
    }

    //-----------------------------------------------------------------------
    // ofNanoOfDay(long)
    //-----------------------------------------------------------------------

    public function test_factory_ofNanoOfDay()
    {
        $localTime = LocalTime::ofNanoOfDay(60 * 60 * 1000000000 + 17);
        $this->check($localTime, 1, 0, 0, 17);
    }

    /**
     * @expectedException \Celest\DateTimeException
     */

    public function test_factory_ofNanoOfDay_tooLow()
    {
        LocalTime::ofNanoOfDay(-1);
    }

    /**
     * @expectedException \Celest\DateTimeException
     */

    public function test_factory_ofNanoOfDay_tooHigh()
    {
        LocalTime::ofNanoOfDay(24 * 60 * 60 * 1000000000);
    }

    //-----------------------------------------------------------------------
    // from()
    //-----------------------------------------------------------------------

    public function test_factory_from_TemporalAccessor()
    {
        $this->assertEquals(LocalTime::from(LocalTime::of(17, 30)), LocalTime::of(17, 30));
        $this->assertEquals(LocalTime::from(LocalDateTime::of(2012, 5, 1, 17, 30)), LocalTime::of(17, 30));
    }

    /**
     * @expectedException \Celest\DateTimeException
     */

    public function test_factory_from_TemporalAccessor_invalid_noDerive()
    {
        LocalTime::from(LocalDate::of(2007, 7, 15));
    }

    public function test_factory_from_TemporalAccessor_null()
    {
        TestHelper::assertNullException($this, function () {
            LocalTime::from(null);
        });
    }

    //-----------------------------------------------------------------------
    // parse()
    //-----------------------------------------------------------------------
    /**
     * @dataProvider provider_sampleToString
     */
    public function test_factory_parse_validText($h, $m, $s, $n, $parsable)
    {
        $t = LocalTime::parse($parsable);
        $this->assertNotNull($t, $parsable);
        $this->assertEquals($t->getHour(), $h);
        $this->assertEquals($t->getMinute(), $m);
        $this->assertEquals($t->getSecond(), $s);
        $this->assertEquals($t->getNano(), $n);
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
            ["11:30+01:00"],
            ["11:30+01:00[Europe/Paris]"],
        ];
    }

    /**
     * @dataProvider provider_sampleBadParse
     * @expectedException \Celest\DateTimeParseException
     */
    public function test_factory_parse_invalidText($unparsable)
    {
        LocalTime::parse($unparsable);
    }

//-----------------------------------------------------------------------$s
    /**
     * @expectedException \Celest\DateTimeParseException
     */
    public function test_factory_parse_illegalHour()
    {
        LocalTime::parse("25:00");
    }

    /**
     * @expectedException \Celest\DateTimeParseException
     */
    public function test_factory_parse_illegalMinute()
    {
        LocalTime::parse("12:60");
    }

    /**
     * @expectedException \Celest\DateTimeParseException
     */
    public function test_factory_parse_illegalSecond()
    {
        LocalTime::parse("12:12:60");
    }

    //-----------------------------------------------------------------------$s
    public function test_factory_parse_nullTest()
    {
        TestHelper::assertNullException($this, function () {
            LocalTime::parse(null);
        });
    }

//-----------------------------------------------------------------------
// parse(DateTimeFormatter)
//-----------------------------------------------------------------------

    public function test_factory_parse_formatter()
    {
        $f = DateTimeFormatter::ofPattern("H m s");
        $test = LocalTime::parseWith("14 30 40", $f);
        $this->assertEquals($test, LocalTime::of(14, 30, 40));
    }

    public function test_factory_parse_formatter_nullText()
    {
        TestHelper::assertNullException($this, function () {
            $f = DateTimeFormatter::ofPattern("H m s");
            LocalTime::parseWith(null, $f);
        });

    }

    public function test_factory_parse_formatter_nullFormatter()
    {
        TestHelper::assertNullException($this, function () {
            LocalTime::parseWith("ANY", null);
        });
    }

    //-----------------------------------------------------------------------
    // isSupported(TemporalField)
    //-----------------------------------------------------------------------

    public function test_isSupported_TemporalField()
    {
        // TODO $this->assertEquals(self::TEST_123040987654321().isSupported((TemporalField) null), false);
        $this->assertEquals(self::TEST_123040987654321()->isSupported(CF::NANO_OF_SECOND()), true);
        $this->assertEquals(self::TEST_123040987654321()->isSupported(CF::NANO_OF_DAY()), true);
        $this->assertEquals(self::TEST_123040987654321()->isSupported(CF::MICRO_OF_SECOND()), true);
        $this->assertEquals(self::TEST_123040987654321()->isSupported(CF::MICRO_OF_DAY()), true);
        $this->assertEquals(self::TEST_123040987654321()->isSupported(CF::MILLI_OF_SECOND()), true);
        $this->assertEquals(self::TEST_123040987654321()->isSupported(CF::MILLI_OF_DAY()), true);
        $this->assertEquals(self::TEST_123040987654321()->isSupported(CF::SECOND_OF_MINUTE()), true);
        $this->assertEquals(self::TEST_123040987654321()->isSupported(CF::SECOND_OF_DAY()), true);
        $this->assertEquals(self::TEST_123040987654321()->isSupported(CF::MINUTE_OF_HOUR()), true);
        $this->assertEquals(self::TEST_123040987654321()->isSupported(CF::MINUTE_OF_DAY()), true);
        $this->assertEquals(self::TEST_123040987654321()->isSupported(CF::HOUR_OF_AMPM()), true);
        $this->assertEquals(self::TEST_123040987654321()->isSupported(CF::CLOCK_HOUR_OF_AMPM()), true);
        $this->assertEquals(self::TEST_123040987654321()->isSupported(CF::HOUR_OF_DAY()), true);
        $this->assertEquals(self::TEST_123040987654321()->isSupported(CF::CLOCK_HOUR_OF_DAY()), true);
        $this->assertEquals(self::TEST_123040987654321()->isSupported(CF::AMPM_OF_DAY()), true);
        $this->assertEquals(self::TEST_123040987654321()->isSupported(CF::DAY_OF_WEEK()), false);
        $this->assertEquals(self::TEST_123040987654321()->isSupported(CF::ALIGNED_DAY_OF_WEEK_IN_MONTH()), false);
        $this->assertEquals(self::TEST_123040987654321()->isSupported(CF::ALIGNED_DAY_OF_WEEK_IN_YEAR()), false);
        $this->assertEquals(self::TEST_123040987654321()->isSupported(CF::DAY_OF_MONTH()), false);
        $this->assertEquals(self::TEST_123040987654321()->isSupported(CF::DAY_OF_YEAR()), false);
        $this->assertEquals(self::TEST_123040987654321()->isSupported(CF::EPOCH_DAY()), false);
        $this->assertEquals(self::TEST_123040987654321()->isSupported(CF::ALIGNED_WEEK_OF_MONTH()), false);
        $this->assertEquals(self::TEST_123040987654321()->isSupported(CF::ALIGNED_WEEK_OF_YEAR()), false);
        $this->assertEquals(self::TEST_123040987654321()->isSupported(CF::MONTH_OF_YEAR()), false);
        $this->assertEquals(self::TEST_123040987654321()->isSupported(CF::PROLEPTIC_MONTH()), false);
        $this->assertEquals(self::TEST_123040987654321()->isSupported(CF::YEAR()), false);
        $this->assertEquals(self::TEST_123040987654321()->isSupported(CF::YEAR_OF_ERA()), false);
        $this->assertEquals(self::TEST_123040987654321()->isSupported(CF::ERA()), false);
        $this->assertEquals(self::TEST_123040987654321()->isSupported(CF::INSTANT_SECONDS()), false);
        $this->assertEquals(self::TEST_123040987654321()->isSupported(CF::OFFSET_SECONDS()), false);
    }

    //-----------------------------------------------------------------------
    // isSupported(TemporalUnit)
    //-----------------------------------------------------------------------

    public function test_isSupported_TemporalUnit()
    {
        // TODO $this->assertEquals(self::TEST_123040987654321().isSupported((TemporalUnit) null), false);
        $this->assertEquals(self::TEST_123040987654321()->isUnitSupported(CU::NANOS()), true);
        $this->assertEquals(self::TEST_123040987654321()->isUnitSupported(CU::MICROS()), true);
        $this->assertEquals(self::TEST_123040987654321()->isUnitSupported(CU::MILLIS()), true);
        $this->assertEquals(self::TEST_123040987654321()->isUnitSupported(CU::SECONDS()), true);
        $this->assertEquals(self::TEST_123040987654321()->isUnitSupported(CU::MINUTES()), true);
        $this->assertEquals(self::TEST_123040987654321()->isUnitSupported(CU::HOURS()), true);
        $this->assertEquals(self::TEST_123040987654321()->isUnitSupported(CU::HALF_DAYS()), true);
        $this->assertEquals(self::TEST_123040987654321()->isUnitSupported(CU::DAYS()), false);
        $this->assertEquals(self::TEST_123040987654321()->isUnitSupported(CU::WEEKS()), false);
        $this->assertEquals(self::TEST_123040987654321()->isUnitSupported(CU::MONTHS()), false);
        $this->assertEquals(self::TEST_123040987654321()->isUnitSupported(CU::YEARS()), false);
        $this->assertEquals(self::TEST_123040987654321()->isUnitSupported(CU::DECADES()), false);
        $this->assertEquals(self::TEST_123040987654321()->isUnitSupported(CU::CENTURIES()), false);
        $this->assertEquals(self::TEST_123040987654321()->isUnitSupported(CU::MILLENNIA()), false);
        $this->assertEquals(self::TEST_123040987654321()->isUnitSupported(CU::ERAS()), false);
        $this->assertEquals(self::TEST_123040987654321()->isUnitSupported(CU::FOREVER()), false);
    }

    //-----------------------------------------------------------------------
    // get(TemporalField)
    //-----------------------------------------------------------------------

    public function test_get_TemporalField()
    {
        $test = self::TEST_123040987654321();
        $this->assertEquals($test->get(CF::HOUR_OF_DAY()), 12);
        $this->assertEquals($test->get(CF::MINUTE_OF_HOUR()), 30);
        $this->assertEquals($test->get(CF::SECOND_OF_MINUTE()), 40);
        $this->assertEquals($test->get(CF::NANO_OF_SECOND()), 987654321);

        $this->assertEquals($test->get(CF::SECOND_OF_DAY()), 12 * 3600 + 30 * 60 + 40);
        $this->assertEquals($test->get(CF::MINUTE_OF_DAY()), 12 * 60 + 30);
        $this->assertEquals($test->get(CF::HOUR_OF_AMPM()), 0);
        $this->assertEquals($test->get(CF::CLOCK_HOUR_OF_AMPM()), 12);
        $this->assertEquals($test->get(CF::CLOCK_HOUR_OF_DAY()), 12);
        $this->assertEquals($test->get(CF::AMPM_OF_DAY()), 1);
    }


    public function test_getLong_TemporalField()
    {
        $test = self::TEST_123040987654321();
        $this->assertEquals($test->getLong(CF::HOUR_OF_DAY()), 12);
        $this->assertEquals($test->getLong(CF::MINUTE_OF_HOUR()), 30);
        $this->assertEquals($test->getLong(CF::SECOND_OF_MINUTE()), 40);
        $this->assertEquals($test->getLong(CF::NANO_OF_SECOND()), 987654321);

        $this->assertEquals($test->getLong(CF::SECOND_OF_DAY()), 12 * 3600 + 30 * 60 + 40);
        $this->assertEquals($test->getLong(CF::MINUTE_OF_DAY()), 12 * 60 + 30);
        $this->assertEquals($test->getLong(CF::HOUR_OF_AMPM()), 0);
        $this->assertEquals($test->getLong(CF::CLOCK_HOUR_OF_AMPM()), 12);
        $this->assertEquals($test->getLong(CF::CLOCK_HOUR_OF_DAY()), 12);
        $this->assertEquals($test->getLong(CF::AMPM_OF_DAY()), 1);
    }

    //-----------------------------------------------------------------------
    // $query(TemporalQuery)
    //-----------------------------------------------------------------------
    function data_query()
    {
        return [
            [self::TEST_123040987654321(), TemporalQueries::chronology(), null],
            [self::TEST_123040987654321(), TemporalQueries::zoneId(), null],
            [self::TEST_123040987654321(), TemporalQueries::precision(), CU::NANOS()],
            [self::TEST_123040987654321(), TemporalQueries::zone(), null],
            [self::TEST_123040987654321(), TemporalQueries::offset(), null],
            [self::TEST_123040987654321(), TemporalQueries::localDate(), null],
            [self::TEST_123040987654321(), TemporalQueries::localTime(), self::TEST_123040987654321()],
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
            self::TEST_123040987654321()->query(null);
        });
    }

    //-----------------------------------------------------------------------
    // get*()
    //-----------------------------------------------------------------------
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
    /**
     * @dataProvider provider_sampleTimes
     */
    public function test_get($h, $m, $s, $ns)
    {
        $a = LocalTime::of($h, $m, $s, $ns);
        $this->assertEquals($a->getHour(), $h);
        $this->assertEquals($a->getMinute(), $m);
        $this->assertEquals($a->getSecond(), $s);
        $this->assertEquals($a->getNano(), $ns);
    }

    //-----------------------------------------------------------------------
    // adjustInto(Temporal)
    //-----------------------------------------------------------------------
    function data_adjustInto()
    {
        return [
            [LocalTime::of(23, 5), LocalTime::of(4, 1, 1, 100), LocalTime::of(23, 5, 0, 0), null],
            [LocalTime::of(23, 5, 20), LocalTime::of(4, 1, 1, 100), LocalTime::of(23, 5, 20, 0), null],
            [LocalTime::of(23, 5, 20, 1000), LocalTime::of(4, 1, 1, 100), LocalTime::of(23, 5, 20, 1000), null],
            [LocalTime::of(23, 5, 20, 1000), LocalTime::MAX(), LocalTime::of(23, 5, 20, 1000), null],
            [LocalTime::of(23, 5, 20, 1000), LocalTime::MIN(), LocalTime::of(23, 5, 20, 1000), null],
            [LocalTime::of(23, 5, 20, 1000), LocalTime::NOON(), LocalTime::of(23, 5, 20, 1000), null],
            [LocalTime::of(23, 5, 20, 1000), LocalTime::MIDNIGHT(), LocalTime::of(23, 5, 20, 1000), null],
            [LocalTime::MAX(), LocalTime::of(23, 5, 20, 1000), LocalTime::of(23, 59, 59, 999999999), null],
            [LocalTime::MIN(), LocalTime::of(23, 5, 20, 1000), LocalTime::of(0, 0, 0), null],
            [LocalTime::NOON(), LocalTime::of(23, 5, 20, 1000), LocalTime::of(12, 0, 0), null],
            [LocalTime::MIDNIGHT(), LocalTime::of(23, 5, 20, 1000), LocalTime::of(0, 0, 0), null],

            [LocalTime::of(23, 5), LocalDateTime::of(2210, 2, 2, 1, 1), LocalDateTime::of(2210, 2, 2, 23, 5), null],
            [LocalTime::of(23, 5), OffsetTime::of(1, 1, 0, 0, self::OFFSET_PTWO()), OffsetTime::of(23, 5, 0, 0, self::OFFSET_PTWO()), null],
            [LocalTime::of(23, 5), OffsetDateTime::of(2210, 2, 2, 1, 1, 0, 0, self::OFFSET_PTWO()), OffsetDateTime::of(2210, 2, 2, 23, 5, 0, 0, self::OFFSET_PTWO()), null],
            [LocalTime::of(23, 5), ZonedDateTime::of(2210, 2, 2, 1, 1, 0, 0, self::ZONE_PARIS()), ZonedDateTime::of(2210, 2, 2, 23, 5, 0, 0, self::ZONE_PARIS()), null],

            [LocalTime::of(23, 5), LocalDate::of(2210, 2, 2), null, DateTimeException::class],
            // TODO [LocalTime::of(23, 5), null, null, NullPointerException::class],

        ];
    }

    /**
     * @dataProvider data_adjustInto
     */
    public function test_adjustInto(LocalTime $test, Temporal $temporal, $expected, $expectedEx)
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
// with(TemporalAdjuster)
//-----------------------------------------------------------------------

    public function test_with_adjustment()
    {
        $sample = LocalTime::of(23, 5);
        $adjuster = TemporalAdjusters::fromCallable(function (Temporal $dateTime) use ($sample) {
            return $sample;
        });
        $this->assertEquals(self::TEST_123040987654321()->adjust($adjuster), $sample);
    }

    public function test_with_adjustment_null()
    {
        TestHelper::assertNullException($this, function () {
            self::TEST_123040987654321()->adjust(null);
        });
    }

//-----------------------------------------------------------------------
// with(TemporalField, long)
//-----------------------------------------------------------------------
    private function points($max)
    {
        return [
            0,
            1,
            2,
            Math::div($max, 7),
            Math::div($max, 7) * 2,
            Math::div($max, 2),
            Math::div($max, 7) * 6,
            $max - 2,
            $max - 1];
    }

// Returns $a {@code LocalTime} with the specified $nano-of-second.
// The $hour, minute and second will be unchanged.

    public function test_with_longTemporalField_nanoOfSecond()
    {
        foreach ($this->points(1000000000) as $i) {
            $test = self::TEST_123040987654321()->with(CF::NANO_OF_SECOND(), $i);
            $this->assertEquals($test->get(CF::NANO_OF_SECOND()), $i);
            $this->assertEquals($test->get(CF::HOUR_OF_DAY()), self::TEST_123040987654321()->get(CF::HOUR_OF_DAY()));
            $this->assertEquals($test->get(CF::MINUTE_OF_HOUR()), self::TEST_123040987654321()->get(CF::MINUTE_OF_HOUR()));
            $this->assertEquals($test->get(CF::SECOND_OF_MINUTE()), self::TEST_123040987654321()->get(CF::SECOND_OF_MINUTE()));
        }
    }

// Returns $a {@code LocalTime} with the specified $nano-of-day.
// This completely replaces the time and is equivalent to {@link #ofNanoOfDay(long)}.

    public function test_with_longTemporalField_nanoOfDay()
    {
        foreach ($this->points(86400000000000) as $i) {
            $test = self::TEST_123040987654321()->with(CF::NANO_OF_DAY(), $i);
            $this->assertEquals($test, LocalTime::ofNanoOfDay($i));
        }
    }

// Returns $a {@code LocalTime} with the $nano-of-second replaced by the specified
// micro-of-second multiplied by 1,000.
// The $hour, minute and second will be unchanged.

    public function test_with_longTemporalField_microOfSecond()
    {
        foreach ($this->points(1000000) as $i) {
            $test = self::TEST_123040987654321()->with(CF::MICRO_OF_SECOND(), $i);
            $this->assertEquals($test->get(CF::NANO_OF_SECOND()), $i * 1000);
            $this->assertEquals($test->get(CF::HOUR_OF_DAY()), self::TEST_123040987654321()->get(CF::HOUR_OF_DAY()));
            $this->assertEquals($test->get(CF::MINUTE_OF_HOUR()), self::TEST_123040987654321()->get(CF::MINUTE_OF_HOUR()));
            $this->assertEquals($test->get(CF::SECOND_OF_MINUTE()), self::TEST_123040987654321()->get(CF::SECOND_OF_MINUTE()));
        }
    }

// Returns $a {@code LocalTime} with the specified micro-of-day.
// This completely replaces the time and is equivalent to using {@link #ofNanoOfDay(long)}
// with the micro-of-day multiplied by 1,000.

    public function test_with_longTemporalField_microOfDay()
    {
        foreach ($this->points(86400000000) as $i) {
            $test = self::TEST_123040987654321()->with(CF::MICRO_OF_DAY(), $i);
            $this->assertEquals($test, LocalTime::ofNanoOfDay($i * 1000));
        }
    }

// Returns $a {@code LocalTime} with the $nano-of-second replaced by the specified
// milli-of-second multiplied by 1,000,000.
// The $hour, minute and second will be unchanged.

    public function test_with_longTemporalField_milliOfSecond()
    {
        foreach ($this->points(1000) as $i) {
            $test = self::TEST_123040987654321()->with(CF::MILLI_OF_SECOND(), $i);
            $this->assertEquals($test->get(CF::NANO_OF_SECOND()), $i * 1000000);
            $this->assertEquals($test->get(CF::HOUR_OF_DAY()), self::TEST_123040987654321()->get(CF::HOUR_OF_DAY()));
            $this->assertEquals($test->get(CF::MINUTE_OF_HOUR()), self::TEST_123040987654321()->get(CF::MINUTE_OF_HOUR()));
            $this->assertEquals($test->get(CF::SECOND_OF_MINUTE()), self::TEST_123040987654321()->get(CF::SECOND_OF_MINUTE()));
        }
    }

// Returns $a {@code LocalTime} with the specified milli-of-day.
// This completely replaces the time and is equivalent to using {@link #ofNanoOfDay(long)}
// with the milli-of-day multiplied by 1,000,000.

    public function test_with_longTemporalField_milliOfDay()
    {
        foreach ($this->points(86400000) as $i) {
            $test = self::TEST_123040987654321()->with(CF::MILLI_OF_DAY(), $i);
            $this->assertEquals($test, LocalTime::ofNanoOfDay($i * 1000000));
        }
    }

// Returns $a {@code LocalTime} with the specified second-of-minute.
// The $hour, minute and $nano-of-second will be unchanged.

    public function test_with_longTemporalField_secondOfMinute()
    {
        foreach ($this->points(60) as $i) {
            $test = self::TEST_123040987654321()->with(CF::SECOND_OF_MINUTE(), $i);
            $this->assertEquals($test->get(CF::SECOND_OF_MINUTE()), $i);
            $this->assertEquals($test->get(CF::HOUR_OF_DAY()), self::TEST_123040987654321()->get(CF::HOUR_OF_DAY()));
            $this->assertEquals($test->get(CF::MINUTE_OF_HOUR()), self::TEST_123040987654321()->get(CF::MINUTE_OF_HOUR()));
            $this->assertEquals($test->get(CF::NANO_OF_SECOND()), self::TEST_123040987654321()->get(CF::NANO_OF_SECOND()));
        }
    }

// Returns $a {@code LocalTime} with the specified second-of-day.
// The $nano-of-second will be unchanged.

    public function test_with_longTemporalField_secondOfDay()
    {
        foreach ($this->points(24 * 60 * 60) as $i) {
            $test = self::TEST_123040987654321()->with(CF::SECOND_OF_DAY(), $i);
            $this->assertEquals($test->get(CF::SECOND_OF_DAY()), $i);
            $this->assertEquals($test->get(CF::NANO_OF_SECOND()), self::TEST_123040987654321()->get(CF::NANO_OF_SECOND()));
        }
    }

// Returns $a {@code LocalTime} with the specified minute-of-$hour.
// The $hour, second-of-minute and $nano-of-second will be unchanged.

    public function test_with_longTemporalField_minuteOfHour()
    {
        foreach ($this->points(60) as $i) {
            $test = self::TEST_123040987654321()->with(CF::MINUTE_OF_HOUR(), $i);
            $this->assertEquals($test->get(CF::MINUTE_OF_HOUR()), $i);
            $this->assertEquals($test->get(CF::HOUR_OF_DAY()), self::TEST_123040987654321()->get(CF::HOUR_OF_DAY()));
            $this->assertEquals($test->get(CF::SECOND_OF_MINUTE()), self::TEST_123040987654321()->get(CF::SECOND_OF_MINUTE()));
            $this->assertEquals($test->get(CF::NANO_OF_SECOND()), self::TEST_123040987654321()->get(CF::NANO_OF_SECOND()));
        }
    }

// Returns $a {@code LocalTime} with the specified minute-of-day.
// The second-of-minute and $nano-of-second will be unchanged.

    public function test_with_longTemporalField_minuteOfDay()
    {
        foreach ($this->points(24 * 60) as $i) {
            $test = self::TEST_123040987654321()->with(CF::MINUTE_OF_DAY(), $i);
            $this->assertEquals($test->get(CF::MINUTE_OF_DAY()), $i);
            $this->assertEquals($test->get(CF::SECOND_OF_MINUTE()), self::TEST_123040987654321()->get(CF::SECOND_OF_MINUTE()));
            $this->assertEquals($test->get(CF::NANO_OF_SECOND()), self::TEST_123040987654321()->get(CF::NANO_OF_SECOND()));
        }
    }

// Returns $a {@code LocalTime} with the specified $hour-of-am-pm.
// The AM/PM, minute-of-$hour, second-of-minute and $nano-of-second will be unchanged.

    public function test_with_longTemporalField_hourOfAmPm()
    {
        for ($i = 0; $i < 12; $i++) {
            $test = self::TEST_123040987654321()->with(CF::HOUR_OF_AMPM(), $i);
            $this->assertEquals($test->get(CF::HOUR_OF_AMPM()), $i);
            $this->assertEquals($test->get(CF::AMPM_OF_DAY()), self::TEST_123040987654321()->get(CF::AMPM_OF_DAY()));
            $this->assertEquals($test->get(CF::MINUTE_OF_HOUR()), self::TEST_123040987654321()->get(CF::MINUTE_OF_HOUR()));
            $this->assertEquals($test->get(CF::SECOND_OF_MINUTE()), self::TEST_123040987654321()->get(CF::SECOND_OF_MINUTE()));
            $this->assertEquals($test->get(CF::NANO_OF_SECOND()), self::TEST_123040987654321()->get(CF::NANO_OF_SECOND()));
        }
    }

// Returns $a {@code LocalTime} with the specified $clock-$hour-of-am-pm.
// The AM/PM, minute-of-$hour, second-of-minute and $nano-of-second will be unchanged.

    public function test_with_longTemporalField_clockHourOfAmPm()
    {
        for ($i = 1; $i <= 12; $i++) {
            $test = self::TEST_123040987654321()->with(CF::CLOCK_HOUR_OF_AMPM(), $i);
            $this->assertEquals($test->get(CF::CLOCK_HOUR_OF_AMPM()), $i);
            $this->assertEquals($test->get(CF::AMPM_OF_DAY()), self::TEST_123040987654321()->get(CF::AMPM_OF_DAY()));
            $this->assertEquals($test->get(CF::MINUTE_OF_HOUR()), self::TEST_123040987654321()->get(CF::MINUTE_OF_HOUR()));
            $this->assertEquals($test->get(CF::SECOND_OF_MINUTE()), self::TEST_123040987654321()->get(CF::SECOND_OF_MINUTE()));
            $this->assertEquals($test->get(CF::NANO_OF_SECOND()), self::TEST_123040987654321()->get(CF::NANO_OF_SECOND()));
        }
    }

// Returns $a {@code LocalTime} with the specified $hour-of-day.
// The minute-of-$hour, second-of-minute and $nano-of-second will be unchanged.

    public function test_with_longTemporalField_hourOfDay()
    {
        for ($i = 0; $i < 24; $i++) {
            $test = self::TEST_123040987654321()->with(CF::HOUR_OF_DAY(), $i);
            $this->assertEquals($test->get(CF::HOUR_OF_DAY()), $i);
            $this->assertEquals($test->get(CF::MINUTE_OF_HOUR()), self::TEST_123040987654321()->get(CF::MINUTE_OF_HOUR()));
            $this->assertEquals($test->get(CF::SECOND_OF_MINUTE()), self::TEST_123040987654321()->get(CF::SECOND_OF_MINUTE()));
            $this->assertEquals($test->get(CF::NANO_OF_SECOND()), self::TEST_123040987654321()->get(CF::NANO_OF_SECOND()));
        }
    }

// Returns $a {@code LocalTime} with the specified $clock-$hour-of-day.
// The minute-of-$hour, second-of-minute and $nano-of-second will be unchanged.

    public function test_with_longTemporalField_clockHourOfDay()
    {
        for ($i = 1; $i <= 24; $i++) {
            $test = self::TEST_123040987654321()->with(CF::CLOCK_HOUR_OF_DAY(), $i);
            $this->assertEquals($test->get(CF::CLOCK_HOUR_OF_DAY()), $i);
            $this->assertEquals($test->get(CF::MINUTE_OF_HOUR()), self::TEST_123040987654321()->get(CF::MINUTE_OF_HOUR()));
            $this->assertEquals($test->get(CF::SECOND_OF_MINUTE()), self::TEST_123040987654321()->get(CF::SECOND_OF_MINUTE()));
            $this->assertEquals($test->get(CF::NANO_OF_SECOND()), self::TEST_123040987654321()->get(CF::NANO_OF_SECOND()));
        }
    }

// Returns $a {@code LocalTime} with the specified AM/PM.
// The $hour-of-am-pm, minute-of-$hour, second-of-minute and $nano-of-second will be unchanged.

    public function test_with_longTemporalField_amPmOfDay()
    {
        for ($i = 0; $i <= 1; $i++) {
            $test = self::TEST_123040987654321()->with(CF::AMPM_OF_DAY(), $i);
            $this->assertEquals($test->get(CF::AMPM_OF_DAY()), $i);
            $this->assertEquals($test->get(CF::HOUR_OF_AMPM()), self::TEST_123040987654321()->get(CF::HOUR_OF_AMPM()));
            $this->assertEquals($test->get(CF::MINUTE_OF_HOUR()), self::TEST_123040987654321()->get(CF::MINUTE_OF_HOUR()));
            $this->assertEquals($test->get(CF::SECOND_OF_MINUTE()), self::TEST_123040987654321()->get(CF::SECOND_OF_MINUTE()));
            $this->assertEquals($test->get(CF::NANO_OF_SECOND()), self::TEST_123040987654321()->get(CF::NANO_OF_SECOND()));
        }
    }

// The supported fields behave as follows...
// In all cases, if the new value is outside the valid range of values for the $field
// then $a {@code DateTimeException} will be thrown.
    function data_withTemporalField_outOfRange()
    {
        return [
            [CF::NANO_OF_SECOND(), $this->time(0, 0, 0, 0), CF::NANO_OF_SECOND()->range()->getMinimum() - 1],
            [CF::NANO_OF_SECOND(), $this->time(0, 0, 0, 0), CF::NANO_OF_SECOND()->range()->getMaximum() + 1],

            [CF::NANO_OF_DAY(), $this->time(0, 0, 0, 0), CF::NANO_OF_DAY()->range()->getMinimum() - 1],
            [CF::NANO_OF_DAY(), $this->time(0, 0, 0, 0), CF::NANO_OF_DAY()->range()->getMaximum() + 1],

            [CF::MICRO_OF_SECOND(), $this->time(0, 0, 0, 0), CF::MICRO_OF_SECOND()->range()->getMinimum() - 1],
            [CF::MICRO_OF_SECOND(), $this->time(0, 0, 0, 0), CF::MICRO_OF_SECOND()->range()->getMaximum() + 1],

            [CF::MICRO_OF_DAY(), $this->time(0, 0, 0, 0), CF::MICRO_OF_DAY()->range()->getMinimum() - 1],
            [CF::MICRO_OF_DAY(), $this->time(0, 0, 0, 0), CF::MICRO_OF_DAY()->range()->getMaximum() + 1],

            [CF::MILLI_OF_SECOND(), $this->time(0, 0, 0, 0), CF::MILLI_OF_SECOND()->range()->getMinimum() - 1],
            [CF::MILLI_OF_SECOND(), $this->time(0, 0, 0, 0), CF::MILLI_OF_SECOND()->range()->getMaximum() + 1],

            [CF::MILLI_OF_DAY(), $this->time(0, 0, 0, 0), CF::MILLI_OF_DAY()->range()->getMinimum() - 1],
            [CF::MILLI_OF_DAY(), $this->time(0, 0, 0, 0), CF::MILLI_OF_DAY()->range()->getMaximum() + 1],

            [CF::SECOND_OF_MINUTE(), $this->time(0, 0, 0, 0), CF::SECOND_OF_MINUTE()->range()->getMinimum() - 1],
            [CF::SECOND_OF_MINUTE(), $this->time(0, 0, 0, 0), CF::SECOND_OF_MINUTE()->range()->getMaximum() + 1],

            [CF::SECOND_OF_DAY(), $this->time(0, 0, 0, 0), CF::SECOND_OF_DAY()->range()->getMinimum() - 1],
            [CF::SECOND_OF_DAY(), $this->time(0, 0, 0, 0), CF::SECOND_OF_DAY()->range()->getMaximum() + 1],

            [CF::MINUTE_OF_HOUR(), $this->time(0, 0, 0, 0), CF::MINUTE_OF_HOUR()->range()->getMinimum() - 1],
            [CF::MINUTE_OF_HOUR(), $this->time(0, 0, 0, 0), CF::MINUTE_OF_HOUR()->range()->getMaximum() + 1],

            [CF::MINUTE_OF_DAY(), $this->time(0, 0, 0, 0), CF::MINUTE_OF_DAY()->range()->getMinimum() - 1],
            [CF::MINUTE_OF_DAY(), $this->time(0, 0, 0, 0), CF::MINUTE_OF_DAY()->range()->getMaximum() + 1],

            [CF::HOUR_OF_AMPM(), $this->time(0, 0, 0, 0), CF::HOUR_OF_AMPM()->range()->getMinimum() - 1],
            [CF::HOUR_OF_AMPM(), $this->time(0, 0, 0, 0), CF::HOUR_OF_AMPM()->range()->getMaximum() + 1],

            [CF::CLOCK_HOUR_OF_AMPM(), $this->time(0, 0, 0, 0), CF::CLOCK_HOUR_OF_AMPM()->range()->getMinimum() - 1],
            [CF::CLOCK_HOUR_OF_AMPM(), $this->time(0, 0, 0, 0), CF::CLOCK_HOUR_OF_AMPM()->range()->getMaximum() + 1],

            [CF::HOUR_OF_DAY(), $this->time(0, 0, 0, 0), CF::HOUR_OF_DAY()->range()->getMinimum() - 1],
            [CF::HOUR_OF_DAY(), $this->time(0, 0, 0, 0), CF::HOUR_OF_DAY()->range()->getMaximum() + 1],

            [CF::CLOCK_HOUR_OF_DAY(), $this->time(0, 0, 0, 0), CF::CLOCK_HOUR_OF_DAY()->range()->getMinimum() - 1],
            [CF::CLOCK_HOUR_OF_DAY(), $this->time(0, 0, 0, 0), CF::CLOCK_HOUR_OF_DAY()->range()->getMaximum() + 1],

            [CF::AMPM_OF_DAY(), $this->time(0, 0, 0, 0), CF::AMPM_OF_DAY()->range()->getMinimum() - 1],
            [CF::AMPM_OF_DAY(), $this->time(0, 0, 0, 0), CF::AMPM_OF_DAY()->range()->getMaximum() + 1],
        ];
    }

    /**
     * @dataProvider data_withTemporalField_outOfRange
     */
    public function test_with_longTemporalField_invalid(TemporalField $field, LocalTime $base, $newValue)
    {
        try {
            $base->with($field, $newValue);
            $this->fail("Field should not be allowed " . $field);
        } catch (DateTimeException $ex) {
            // expected
            $this->assertTrue(true);
        }
    }

// All other {@code ChronoField} instances will throw an {@code UnsupportedTemporalTypeException}.
    /**
     * @expectedException \Celest\Temporal\UnsupportedTemporalTypeException
     */
    public function test_with_longTemporalField_otherChronoField()
    {
        self::TEST_123040987654321()->with(CF::DAY_OF_MONTH(), 1);
    }

// If the $field is not $a {@code ChronoField}, then the $result of this method
// is obtained by invoking {@code TemporalField.adjustInto(Temporal, long)}
// passing {@code this} as the argument.

    public function test_with_longTemporalField_notChronoField()
    {
        $result = LocalTime::of(12, 30);
        $base = LocalTime::of(15, 45);
        $field = new TemporalField_notChronoField($this, $base, $result);
        $test = $base->with($field, 12);
        $this->assertSame($test, $result);
    }

    public function test_with_longTemporalField_null()
    {
        TestHelper::assertNullException($this, function () {
            self::TEST_123040987654321()->with(null, 1);
        });
    }

    //-----------------------------------------------------------------------
    // withHour()
    //-----------------------------------------------------------------------

    public function test_withHour_normal()
    {
        $t = self::TEST_123040987654321();
        for ($i = 0; $i < 24; $i++) {
            $t = $t->withHour($i);
            $this->assertEquals($t->getHour(), $i);
        }
    }


    public function test_withHour_noChange_equal()
    {
        $t = self::TEST_123040987654321()->withHour(12);
        $this->assertEquals($t, self::TEST_123040987654321());
    }


    public function test_withHour_toMidnight_equal()
    {
        $t = LocalTime::of(1, 0)->withHour(0);
        $this->assertEquals($t, LocalTime::MIDNIGHT());
    }


    public function test_withHour_toMidday_equal()
    {
        $t = LocalTime::of(1, 0)->withHour(12);
        $this->assertEquals($t, LocalTime::NOON());
    }

    /**
     * @expectedException \Celest\DateTimeException
     */

    public function test_withHour_hourTooLow()
    {
        self::TEST_123040987654321()->withHour(-1);
    }

    /**
     * @expectedException \Celest\DateTimeException
     */

    public function test_withHour_hourTooHigh()
    {
        self::TEST_123040987654321()->withHour(24);
    }

    //-----------------------------------------------------------------------
    // withMinute()
    //-----------------------------------------------------------------------

    public function test_withMinute_normal()
    {
        $t = self::TEST_123040987654321();
        for ($i = 0; $i < 60; $i++) {
            $t = $t->withMinute($i);
            $this->assertEquals($t->getMinute(), $i);
        }
    }


    public function test_withMinute_noChange_equal()
    {
        $t = self::TEST_123040987654321()->withMinute(30);
        $this->assertEquals($t, self::TEST_123040987654321());
    }


    public function test_withMinute_toMidnight_equal()
    {
        $t = LocalTime::of(0, 1)->withMinute(0);
        $this->assertEquals($t, LocalTime::MIDNIGHT());
    }


    public function test_withMinute_toMidday_equals()
    {
        $t = LocalTime::of(12, 1)->withMinute(0);
        $this->assertEquals($t, LocalTime::NOON());
    }

    /**
     * @expectedException \Celest\DateTimeException
     */

    public function test_withMinute_minuteTooLow()
    {
        self::TEST_123040987654321()->withMinute(-1);
    }

    /**
     * @expectedException \Celest\DateTimeException
     */

    public function test_withMinute_minuteTooHigh()
    {
        self::TEST_123040987654321()->withMinute(60);
    }

    //-----------------------------------------------------------------------
    // withSecond()
    //-----------------------------------------------------------------------

    public function test_withSecond_normal()
    {
        $t = self::TEST_123040987654321();
        for ($i = 0; $i < 60; $i++) {
            $t = $t->withSecond($i);
            $this->assertEquals($t->getSecond(), $i);
        }
    }


    public function test_withSecond_noChange_equal()
    {
        $t = self::TEST_123040987654321()->withSecond(40);
        $this->assertEquals($t, self::TEST_123040987654321());
    }


    public function test_withSecond_toMidnight_equal()
    {
        $t = LocalTime::of(0, 0, 1)->withSecond(0);
        $this->assertEquals($t, LocalTime::MIDNIGHT());
    }


    public function test_withSecond_toMidday_equal()
    {
        $t = LocalTime::of(12, 0, 1)->withSecond(0);
        $this->assertEquals($t, LocalTime::NOON());
    }

    /**
     * @expectedException \Celest\DateTimeException
     */

    public function test_withSecond_secondTooLow()
    {
        self::TEST_123040987654321()->withSecond(-1);
    }

    /**
     * @expectedException \Celest\DateTimeException
     */
    public function test_withSecond_secondTooHigh()
    {
        self::TEST_123040987654321()->withSecond(60);
    }

    //-----------------------------------------------------------------------
    // withNano()
    //-----------------------------------------------------------------------

    public function test_withNanoOfSecond_normal()
    {
        $t = self::TEST_123040987654321();
        $t = $t->withNano(1);
        $this->assertEquals($t->getNano(), 1);
        $t = $t->withNano(10);
        $this->assertEquals($t->getNano(), 10);
        $t = $t->withNano(100);
        $this->assertEquals($t->getNano(), 100);
        $t = $t->withNano(999999999);
        $this->assertEquals($t->getNano(), 999999999);
    }


    public function test_withNanoOfSecond_noChange_equal()
    {
        $t = self::TEST_123040987654321()->withNano(987654321);
        $this->assertEquals($t, self::TEST_123040987654321());
    }


    public function test_withNanoOfSecond_toMidnight_equal()
    {
        $t = LocalTime::of(0, 0, 0, 1)->withNano(0);
        $this->assertEquals($t, LocalTime::MIDNIGHT());
    }


    public function test_withNanoOfSecond_toMidday_equal()
    {
        $t = LocalTime::of(12, 0, 0, 1)->withNano(0);
        $this->assertEquals($t, LocalTime::NOON());
    }

    /**
     * @expectedException \Celest\DateTimeException
     */

    public function test_withNanoOfSecond_nanoTooLow()
    {
        self::TEST_123040987654321()->withNano(-1);
    }

    /**
     * @expectedException \Celest\DateTimeException
     */

    public function test_withNanoOfSecond_nanoTooHigh()
    {
        self::TEST_123040987654321()->withNano(1000000000);
    }

    //-----------------------------------------------------------------------
    // truncated(TemporalUnit)
    //-----------------------------------------------------------------------

    function data_truncatedToValid()
    {
        return [
            [LocalTime::of(1, 2, 3, 123456789), CU::NANOS(), LocalTime::of(1, 2, 3, 123456789)],
            [LocalTime::of(1, 2, 3, 123456789), CU::MICROS(), LocalTime::of(1, 2, 3, 123456000)],
            [LocalTime::of(1, 2, 3, 123456789), CU::MILLIS(), LocalTime::of(1, 2, 3, 123000000)],
            [LocalTime::of(1, 2, 3, 123456789), CU::SECONDS(), LocalTime::of(1, 2, 3)],
            [LocalTime::of(1, 2, 3, 123456789), CU::MINUTES(), LocalTime::of(1, 2)],
            [LocalTime::of(1, 2, 3, 123456789), CU::HOURS(), LocalTime::of(1, 0)],
            [LocalTime::of(1, 2, 3, 123456789), CU::DAYS(), LocalTime::MIDNIGHT()],

            [LocalTime::of(1, 1, 1, 123456789), new NINETY_MINS(), LocalTime::of(0, 0)],
            [LocalTime::of(2, 1, 1, 123456789), new NINETY_MINS(), LocalTime::of(1, 30)],
            [LocalTime::of(3, 1, 1, 123456789), new NINETY_MINS(), LocalTime::of(3, 0)],
        ];
    }

    /**
     * @dataProvider data_truncatedToValid
     */
    public function test_truncatedTo_valid(LocalTime $input, TemporalUnit $unit, LocalTime $expected)
    {
        $this->assertEquals($input->truncatedTo($unit), $expected);
    }

    function data_truncatedToInvalid()
    {
        return [
            [LocalTime::of(1, 2, 3, 123456789), new NINETY_FIVE_MINS()],
            [LocalTime::of(1, 2, 3, 123456789), CU::WEEKS()],
            [LocalTime::of(1, 2, 3, 123456789), CU::MONTHS()],
            [LocalTime::of(1, 2, 3, 123456789), CU::YEARS()],
        ];
    }

    /**
     * @dataProvider data_truncatedToInvalid
     * @expectedException \Celest\DateTimeException
     */
    public function test_truncatedTo_invalid(LocalTime $input, TemporalUnit $unit)
    {
        $input->truncatedTo($unit);
    }

    public function test_truncatedTo_null()
    {
        TestHelper::assertNullException($this, function () {
            self::TEST_123040987654321()->truncatedTo(null);
        });
    }

    //-----------------------------------------------------------------------
    // plus(TemporalAmount)
    //-----------------------------------------------------------------------

    public function test_plus_TemporalAmount_positiveHours()
    {
        $period = MockSimplePeriod::of(7, CU::HOURS());
        $t = self::TEST_123040987654321()->plusAmount($period);
        $this->assertEquals($t, LocalTime::of(19, 30, 40, 987654321));
    }


    public function test_plus_TemporalAmount_negativeMinutes()
    {
        $period = MockSimplePeriod::of(-25, CU::MINUTES());
        $t = self::TEST_123040987654321()->plusAmount($period);
        $this->assertEquals($t, LocalTime::of(12, 5, 40, 987654321));
    }


    public function test_plus_TemporalAmount_zero()
    {
        $period = Period::ZERO();
        $t = self::TEST_123040987654321()->plusAmount($period);
        $this->assertEquals($t, self::TEST_123040987654321());
    }


    public function test_plus_TemporalAmount_wrap()
    {
        $p = MockSimplePeriod::of(1, CU::HOURS());
        $t = LocalTime::of(23, 30)->plusAmount($p);
        $this->assertEquals($t, LocalTime::of(0, 30));
    }

    /**
     * @expectedException \Celest\DateTimeException
     */

    public function test_plus_TemporalAmount_dateNotAllowed()
    {
        $period = MockSimplePeriod::of(7, CU::MONTHS());
        self::TEST_123040987654321()->plusAmount($period);
    }

    public function test_plus_TemporalAmount_null()
    {
        TestHelper::assertNullException($this, function () {
            self::TEST_123040987654321()->plusAmount(null);
        });
    }

    //-----------------------------------------------------------------------
    // plus(long,TemporalUnit)
    //-----------------------------------------------------------------------

    public function test_plus_longTemporalUnit_positiveHours()
    {
        $t = self::TEST_123040987654321()->plus(7, CU::HOURS());
        $this->assertEquals($t, LocalTime::of(19, 30, 40, 987654321));
    }


    public function test_plus_longTemporalUnit_negativeMinutes()
    {
        $t = self::TEST_123040987654321()->plus(-25, CU::MINUTES());
        $this->assertEquals($t, LocalTime::of(12, 5, 40, 987654321));
    }


    public function test_plus_longTemporalUnit_zero()
    {
        $t = self::TEST_123040987654321()->plus(0, CU::MINUTES());
        $this->assertEquals($t, self::TEST_123040987654321());
    }


    public function test_plus_longTemporalUnit_invalidUnit()
    {
        foreach (self::INVALID_UNITS() as $unit) {
            try {
                self::TEST_123040987654321()->plus(1, $unit);
                $this->fail("Unit should not be allowed " . $unit);
            } catch (DateTimeException $ex) {
                // expected
                $this->assertTrue(true);
            }
        }
    }

    public function test_plus_longTemporalUnit_null()
    {
        TestHelper::assertNullException($this, function () {
            self::TEST_123040987654321()->plus(1, null);
        });
    }

    //-----------------------------------------------------------------------
    // plusHours()
    //-----------------------------------------------------------------------

    public function test_plusHours_one()
    {
        $t = LocalTime::MIDNIGHT();
        for ($i = 0; $i < 50; $i++) {
            $t = $t->plusHours(1);
            $this->assertEquals($t->getHour(), ($i + 1) % 24);
        }
    }


    public function test_plusHours_fromZero()
    {
        $base = LocalTime::MIDNIGHT();
        for ($i = -50; $i < 50; $i++) {
            $t = $base->plusHours($i);
            $this->assertEquals($t->getHour(), ($i + 72) % 24);
        }
    }


    public function test_plusHours_fromOne()
    {
        $base = LocalTime::of(1, 0);
        for ($i = -50; $i < 50; $i++) {
            $t = $base->plusHours($i);
            $this->assertEquals($t->getHour(), (1 + $i + 72) % 24);
        }
    }


    public function test_plusHours_noChange_equal()
    {
        $t = self::TEST_123040987654321()->plusHours(0);
        $this->assertEquals($t, self::TEST_123040987654321());
    }


    public function test_plusHours_toMidnight_equal()
    {
        $t = LocalTime::of(23, 0)->plusHours(1);
        $this->assertEquals($t, LocalTime::MIDNIGHT());
    }


    public function test_plusHours_toMidday_equal()
    {
        $t = LocalTime::of(11, 0)->plusHours(1);
        $this->assertEquals($t, LocalTime::NOON());
    }


    public function test_plusHours_big()
    {
        $t = LocalTime::of(2, 30)->plusHours(Long::MAX_VALUE);
        $hours = (int)(Long::MAX_VALUE % 24);
        $this->assertEquals($t, LocalTime::of(2, 30)->plusHours($hours));
    }

    //-----------------------------------------------------------------------
    // plusMinutes()
    //-----------------------------------------------------------------------

    public function test_plusMinutes_one()
    {
        $t = LocalTime::MIDNIGHT();
        $hour = 0;
        $min = 0;
        for ($i = 0; $i < 70; $i++) {
            $t = $t->plusMinutes(1);
            $min++;
            if ($min == 60) {
                $hour++;
                $min = 0;
            }
            $this->assertEquals($t->getHour(), $hour);
            $this->assertEquals($t->getMinute(), $min);
        }
    }


    public function test_plusMinutes_fromZero()
    {
        $base = LocalTime::MIDNIGHT();
        for ($i = -70; $i < 70; $i++) {
            $t = $base->plusMinutes($i);
            if ($i < -60) {
                $hour = 22;
                $min = $i + 120;
            } else if ($i < 0) {
                $hour = 23;
                $min = $i + 60;
            } else if ($i >= 60) {
                $hour = 1;
                $min = $i - 60;
            } else {
                $hour = 0;
                $min = $i;
            }
            $this->assertEquals($t->getHour(), $hour);
            $this->assertEquals($t->getMinute(), $min);
        }
    }


    public function test_plusMinutes_noChange_equal()
    {
        $t = self::TEST_123040987654321()->plusMinutes(0);
        $this->assertEquals($t, self::TEST_123040987654321());
    }


    public function test_plusMinutes_noChange_oneDay_equal()
    {
        $t = self::TEST_123040987654321()->plusMinutes(24 * 60);
        $this->assertEquals($t, self::TEST_123040987654321());
    }


    public function test_plusMinutes_toMidnight_equal()
    {
        $t = LocalTime::of(23, 59)->plusMinutes(1);
        $this->assertEquals($t, LocalTime::MIDNIGHT());
    }


    public function test_plusMinutes_toMidday_equal()
    {
        $t = LocalTime::of(11, 59)->plusMinutes(1);
        $this->assertEquals($t, LocalTime::NOON());
    }


    public function test_plusMinutes_big()
    {
        $t = LocalTime::of(2, 30)->plusMinutes(Long::MAX_VALUE);
        $mins = (int)(Long::MAX_VALUE % (24 * 60));
        $this->assertEquals($t, LocalTime::of(2, 30)->plusMinutes($mins));
    }

    //-----------------------------------------------------------------------
    // plusSeconds()
    //-----------------------------------------------------------------------

    public function test_plusSeconds_one()
    {
        $t = LocalTime::MIDNIGHT();
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
            $this->assertEquals($t->getHour(), $hour);
            $this->assertEquals($t->getMinute(), $min);
            $this->assertEquals($t->getSecond(), $sec);
        }
    }


    function plusSeconds_fromZero()
    {
        $delta = 30;
        $hour = 22;
        $min = 59;
        $sec = 0;

        $ret = [];

        for ($i = -3660; $i <= 3660;) {
            $ret[] = [$i, $hour, $min, $sec];
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
        }
        return $ret;
    }

    /**
     * @dataProvider plusSeconds_fromZero
     */
    public function test_plusSeconds_fromZero($seconds, $hour, $min, $sec)
    {
        $base = LocalTime::MIDNIGHT();
        $t = $base->plusSeconds($seconds);

        $this->assertEquals($hour, $t->getHour());
        $this->assertEquals($min, $t->getMinute());
        $this->assertEquals($sec, $t->getSecond());
    }


    public function test_plusSeconds_noChange_equal()
    {
        $t = self::TEST_123040987654321()->plusSeconds(0);
        $this->assertEquals($t, self::TEST_123040987654321());
    }


    public function test_plusSeconds_noChange_oneDay_equal()
    {
        $t = self::TEST_123040987654321()->plusSeconds(24 * 60 * 60);
        $this->assertEquals($t, self::TEST_123040987654321());
    }


    public function test_plusSeconds_toMidnight_equal()
    {
        $t = LocalTime::of(23, 59, 59)->plusSeconds(1);
        $this->assertEquals($t, LocalTime::MIDNIGHT());
    }


    public function test_plusSeconds_toMidday_equal()
    {
        $t = LocalTime::of(11, 59, 59)->plusSeconds(1);
        $this->assertEquals($t, LocalTime::NOON());
    }

    //-----------------------------------------------------------------------
    // plusNanos()
    //-----------------------------------------------------------------------

    public function test_plusNanos_halfABillion()
    {
        $t = LocalTime::MIDNIGHT();
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
            $this->assertEquals($t->getHour(), $hour);
            $this->assertEquals($t->getMinute(), $min);
            $this->assertEquals($t->getSecond(), $sec);
            $this->assertEquals($t->getNano(), $nanos);
        }
    }

    function plusNanos_fromZero()
    {
        $delta = 7500000000;
        $hour = 22;
        $min = 59;
        $sec = 0;
        $nanos = 0;

        $ret = [];

        for ($i = -3660 * 1000000000; $i <= 3660 * 1000000000;) {
            $ret[] = [$i, $hour, $min, $sec, $nanos];
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
    public function test_plusNanos_fromZero($nanoseconds, $hour, $min, $sec, $nanos)
    {
        $base = LocalTime::MIDNIGHT();
        $t = $base->plusNanos($nanoseconds);

        $this->assertEquals($hour, $t->getHour());
        $this->assertEquals($min, $t->getMinute());
        $this->assertEquals($sec, $t->getSecond());
        $this->assertEquals($nanos, $t->getNano());
    }


    public function test_plusNanos_noChange_equal()
    {
        $t = self::TEST_123040987654321()->plusNanos(0);
        $this->assertEquals($t, self::TEST_123040987654321());
    }


    public function test_plusNanos_noChange_oneDay_equal()
    {
        $t = self::TEST_123040987654321()->plusNanos(24 * 60 * 60 * 1000000000);
        $this->assertEquals($t, self::TEST_123040987654321());
    }


    public function test_plusNanos_toMidnight_equal()
    {
        $t = LocalTime::of(23, 59, 59, 999999999)->plusNanos(1);
        $this->assertEquals($t, LocalTime::MIDNIGHT());
    }


    public function test_plusNanos_toMidday_equal()
    {
        $t = LocalTime::of(11, 59, 59, 999999999)->plusNanos(1);
        $this->assertEquals($t, LocalTime::NOON());
    }

    //-----------------------------------------------------------------------
    // minus(TemporalAmount)
    //-----------------------------------------------------------------------

    public function test_minus_TemporalAmount_positiveHours()
    {
        $period = MockSimplePeriod::of(7, CU::HOURS());
        $t = self::TEST_123040987654321()->minusAmount($period);
        $this->assertEquals($t, LocalTime::of(5, 30, 40, 987654321));
    }


    public function test_minus_TemporalAmount_negativeMinutes()
    {
        $period = MockSimplePeriod::of(-25, CU::MINUTES());
        $t = self::TEST_123040987654321()->minusAmount($period);
        $this->assertEquals($t, LocalTime::of(12, 55, 40, 987654321));
    }


    public function test_minus_TemporalAmount_zero()
    {
        $period = Period::ZERO();
        $t = self::TEST_123040987654321()->minusAmount($period);
        $this->assertEquals($t, self::TEST_123040987654321());
    }


    public function test_minus_TemporalAmount_wrap()
    {
        $p = MockSimplePeriod::of(1, CU::HOURS());
        $t = LocalTime::of(0, 30)->minusAmount($p);
        $this->assertEquals($t, LocalTime::of(23, 30));
    }

    /**
     * @expectedException \Celest\DateTimeException
     */

    public function test_minus_TemporalAmount_dateNotAllowed()
    {
        $period = MockSimplePeriod::of(7, CU::MONTHS());
        self::TEST_123040987654321()->minusAmount($period);
    }

    public function test_minus_TemporalAmount_null()
    {
        TestHelper::assertNullException($this, function () {
            self::TEST_123040987654321()->minusAmount(null);
        });
    }

    //-----------------------------------------------------------------------
    // minus(long,TemporalUnit)
    //-----------------------------------------------------------------------

    public function test_minus_longTemporalUnit_positiveHours()
    {
        $t = self::TEST_123040987654321()->minus(7, CU::HOURS());
        $this->assertEquals($t, LocalTime::of(5, 30, 40, 987654321));
    }


    public function test_minus_longTemporalUnit_negativeMinutes()
    {
        $t = self::TEST_123040987654321()->minus(-25, CU::MINUTES());
        $this->assertEquals($t, LocalTime::of(12, 55, 40, 987654321));
    }


    public function test_minus_longTemporalUnit_zero()
    {
        $t = self::TEST_123040987654321()->minus(0, CU::MINUTES());
        $this->assertEquals($t, self::TEST_123040987654321());
    }


    public function test_minus_longTemporalUnit_invalidUnit()
    {
        foreach (self::INVALID_UNITS() as $unit) {
            try {
                self::TEST_123040987654321()->minus(1, $unit);
                $this->fail("Unit should not be allowed " . $unit);
            } catch (DateTimeException $ex) {
                // expected
                $this->assertTrue(true);
            }
        }
    }

    public function test_minus_longTemporalUnit_null()
    {
        TestHelper::assertNullException($this, function () {
            self::TEST_123040987654321()->minus(1, null);
        });
    }

    //-----------------------------------------------------------------------
    // minusHours()
    //-----------------------------------------------------------------------

    public function test_minusHours_one()
    {
        $t = LocalTime::MIDNIGHT();
        for ($i = 0; $i < 50; $i++) {
            $t = $t->minusHours(1);
            $this->assertEquals($t->getHour(), (((-$i + 23) % 24) + 24) % 24, $i);
        }
    }


    public function test_minusHours_fromZero()
    {
        $base = LocalTime::MIDNIGHT();
        for ($i = -50; $i < 50; $i++) {
            $t = $base->minusHours($i);
            $this->assertEquals($t->getHour(), ((-$i % 24) + 24) % 24);
        }
    }


    public function test_minusHours_fromOne()
    {
        $base = LocalTime::of(1, 0);
        for ($i = -50; $i < 50; $i++) {
            $t = $base->minusHours($i);
            $this->assertEquals($t->getHour(), (1 + (-$i % 24) + 24) % 24);
        }
    }


    public function test_minusHours_noChange_equal()
    {
        $t = self::TEST_123040987654321()->minusHours(0);
        $this->assertEquals($t, self::TEST_123040987654321());
    }


    public function test_minusHours_toMidnight_equal()
    {
        $t = LocalTime::of(1, 0)->minusHours(1);
        $this->assertEquals($t, LocalTime::MIDNIGHT());
    }


    public function test_minusHours_toMidday_equal()
    {
        $t = LocalTime::of(13, 0)->minusHours(1);
        $this->assertEquals($t, LocalTime::NOON());
    }


    public function test_minusHours_big()
    {
        $t = LocalTime::of(2, 30)->minusHours(Long::MAX_VALUE);
        $hours = (int)(Long::MAX_VALUE % 24);
        $this->assertEquals($t, LocalTime::of(2, 30)->minusHours($hours));
    }

    //-----------------------------------------------------------------------
    // minusMinutes()
    //-----------------------------------------------------------------------

    public function test_minusMinutes_one()
    {
        $t = LocalTime::MIDNIGHT();
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
            $this->assertEquals($t->getHour(), $hour);
            $this->assertEquals($t->getMinute(), $min);
        }
    }


    public function test_minusMinutes_fromZero()
    {
        $base = LocalTime::MIDNIGHT();
        $hour = 22;
        $min = 49;
        for ($i = 70; $i > -70; $i--) {
            $t = $base->minusMinutes($i);
            $min++;

            if ($min == 60) {
                $hour++;
                $min = 0;

                if ($hour == 24) {
                    $hour = 0;
                }
            }

            $this->assertEquals($t->getHour(), $hour);
            $this->assertEquals($t->getMinute(), $min);
        }
    }


    public function test_minusMinutes_noChange_equal()
    {
        $t = self::TEST_123040987654321()->minusMinutes(0);
        $this->assertEquals($t, self::TEST_123040987654321());
    }


    public function test_minusMinutes_noChange_oneDay_equal()
    {
        $t = self::TEST_123040987654321()->minusMinutes(24 * 60);
        $this->assertEquals($t, self::TEST_123040987654321());
    }


    public function test_minusMinutes_toMidnight_equal()
    {
        $t = LocalTime::of(0, 1)->minusMinutes(1);
        $this->assertEquals($t, LocalTime::MIDNIGHT());
    }


    public function test_minusMinutes_toMidday_equals()
    {
        $t = LocalTime::of(12, 1)->minusMinutes(1);
        $this->assertEquals($t, LocalTime::NOON());
    }


    public function test_minusMinutes_big()
    {
        $t = LocalTime::of(2, 30)->minusMinutes(Long::MAX_VALUE);
        $mins = (int)(Long::MAX_VALUE % (24 * 60));
        $this->assertEquals($t, LocalTime::of(2, 30)->minusMinutes($mins));
    }

    //-----------------------------------------------------------------------
    // minusSeconds()
    //-----------------------------------------------------------------------

    public function test_minusSeconds_one()
    {
        $t = LocalTime::MIDNIGHT();
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
            $this->assertEquals($t->getHour(), $hour);
            $this->assertEquals($t->getMinute(), $min);
            $this->assertEquals($t->getSecond(), $sec);
        }
    }

    function minusSeconds_fromZero()
    {
        $delta = 30;
        $hour = 22;
        $min = 59;
        $sec = 0;

        $ret = [];

        for ($i = 3660; $i >= -3660;) {
            $ret[] = [$i, $hour, $min, $sec];
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
        }
        return $ret;
    }

    /**
     * @dataProvider minusSeconds_fromZero
     */
    public function test_minusSeconds_fromZero($seconds, $hour, $min, $sec)
    {
        $base = LocalTime::MIDNIGHT();
        $t = $base->minusSeconds($seconds);

        $this->assertEquals($t->getHour(), $hour);
        $this->assertEquals($t->getMinute(), $min);
        $this->assertEquals($t->getSecond(), $sec);
    }


    public function test_minusSeconds_noChange_equal()
    {
        $t = self::TEST_123040987654321()->minusSeconds(0);
        $this->assertEquals($t, self::TEST_123040987654321());
    }


    public function test_minusSeconds_noChange_oneDay_equal()
    {
        $t = self::TEST_123040987654321()->minusSeconds(24 * 60 * 60);
        $this->assertEquals($t, self::TEST_123040987654321());
    }


    public function test_minusSeconds_toMidnight_equal()
    {
        $t = LocalTime::of(0, 0, 1)->minusSeconds(1);
        $this->assertEquals($t, LocalTime::MIDNIGHT());
    }


    public function test_minusSeconds_toMidday_equal()
    {
        $t = LocalTime::of(12, 0, 1)->minusSeconds(1);
        $this->assertEquals($t, LocalTime::NOON());
    }


    public function test_minusSeconds_big()
    {
        $t = LocalTime::of(2, 30)->minusSeconds(Long::MAX_VALUE);
        $secs = (int)(Long::MAX_VALUE % (24 * 60 * 60));
        $this->assertEquals($t, LocalTime::of(2, 30)->minusSeconds($secs));
    }

    //-----------------------------------------------------------------------
    // minusNanos()
    //-----------------------------------------------------------------------

    public function test_minusNanos_halfABillion()
    {
        $t = LocalTime::MIDNIGHT();
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

            $this->assertEquals($t->getHour(), $hour);
            $this->assertEquals($t->getMinute(), $min);
            $this->assertEquals($t->getSecond(), $sec);
            $this->assertEquals($t->getNano(), $nanos);
        }
    }

    function minusNanos_fromZero()
    {
        $delta = 7500000000;
        $hour = 22;
        $min = 59;
        $sec = 0;
        $nanos = 0;

        $ret = [];

        for ($i = 3660 * 1000000000; $i >= -3660 * 1000000000;) {
            $ret[] = [$i, $hour, $min, $sec, $nanos];
            $i -= $delta;
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
    public function test_minusNanos_fromZero($nanoseconds, $hour, $min, $sec, $nanos)
    {
        $base = LocalTime::MIDNIGHT();
        $t = $base->minusNanos($nanoseconds);

        $this->assertEquals($hour, $t->getHour());
        $this->assertEquals($min, $t->getMinute());
        $this->assertEquals($sec, $t->getSecond());
        $this->assertEquals($nanos, $t->getNano());
    }


    public function test_minusNanos_noChange_equal()
    {
        $t = self::TEST_123040987654321()->minusNanos(0);
        $this->assertEquals($t, self::TEST_123040987654321());
    }


    public function test_minusNanos_noChange_oneDay_equal()
    {
        $t = self::TEST_123040987654321()->minusNanos(24 * 60 * 60 * 1000000000);
        $this->assertEquals($t, self::TEST_123040987654321());
    }


    public function test_minusNanos_toMidnight_equal()
    {
        $t = LocalTime::of(0, 0, 0, 1)->minusNanos(1);
        $this->assertEquals($t, LocalTime::MIDNIGHT());
    }


    public function test_minusNanos_toMidday_equal()
    {
        $t = LocalTime::of(12, 0, 0, 1)->minusNanos(1);
        $this->assertEquals($t, LocalTime::NOON());
    }

    //-----------------------------------------------------------------------
    // until(Temporal, TemporalUnit)
    //-----------------------------------------------------------------------
    function data_periodUntilUnit()
    {
        return [
            [$this->time(0, 0, 0, 0), $this->time(0, 0, 0, 0), CU::NANOS(), 0],
            [$this->time(0, 0, 0, 0), $this->time(0, 0, 0, 0), CU::MICROS(), 0],
            [$this->time(0, 0, 0, 0), $this->time(0, 0, 0, 0), CU::MILLIS(), 0],
            [$this->time(0, 0, 0, 0), $this->time(0, 0, 0, 0), CU::SECONDS(), 0],
            [$this->time(0, 0, 0, 0), $this->time(0, 0, 0, 0), CU::MINUTES(), 0],
            [$this->time(0, 0, 0, 0), $this->time(0, 0, 0, 0), CU::HOURS(), 0],
            [$this->time(0, 0, 0, 0), $this->time(0, 0, 0, 0), CU::HALF_DAYS(), 0],

            [$this->time(0, 0, 0, 0), $this->time(2, 0, 0, 0), CU::NANOS(), 2 * 3600 * 1000000000],
            [$this->time(0, 0, 0, 0), $this->time(2, 0, 0, 0), CU::MICROS(), 2 * 3600 * 1000000],
            [$this->time(0, 0, 0, 0), $this->time(2, 0, 0, 0), CU::MILLIS(), 2 * 3600 * 1000],
            [$this->time(0, 0, 0, 0), $this->time(2, 0, 0, 0), CU::SECONDS(), 2 * 3600],
            [$this->time(0, 0, 0, 0), $this->time(2, 0, 0, 0), CU::MINUTES(), 2 * 60],
            [$this->time(0, 0, 0, 0), $this->time(2, 0, 0, 0), CU::HOURS(), 2],
            [$this->time(0, 0, 0, 0), $this->time(2, 0, 0, 0), CU::HALF_DAYS(), 0],

            [$this->time(0, 0, 0, 0), $this->time(14, 0, 0, 0), CU::NANOS(), 14 * 3600 * 1000000000],
            [$this->time(0, 0, 0, 0), $this->time(14, 0, 0, 0), CU::MICROS(), 14 * 3600 * 1000000],
            [$this->time(0, 0, 0, 0), $this->time(14, 0, 0, 0), CU::MILLIS(), 14 * 3600 * 1000],
            [$this->time(0, 0, 0, 0), $this->time(14, 0, 0, 0), CU::SECONDS(), 14 * 3600],
            [$this->time(0, 0, 0, 0), $this->time(14, 0, 0, 0), CU::MINUTES(), 14 * 60],
            [$this->time(0, 0, 0, 0), $this->time(14, 0, 0, 0), CU::HOURS(), 14],
            [$this->time(0, 0, 0, 0), $this->time(14, 0, 0, 0), CU::HALF_DAYS(), 1],

            [$this->time(0, 0, 0, 0), $this->time(2, 30, 40, 1500), CU::NANOS(), (2 * 3600 + 30 * 60 + 40) * 1000000000 + 1500],
            [$this->time(0, 0, 0, 0), $this->time(2, 30, 40, 1500), CU::MICROS(), (2 * 3600 + 30 * 60 + 40) * 1000000 + 1],
            [$this->time(0, 0, 0, 0), $this->time(2, 30, 40, 1500), CU::MILLIS(), (2 * 3600 + 30 * 60 + 40) * 1000],
            [$this->time(0, 0, 0, 0), $this->time(2, 30, 40, 1500), CU::SECONDS(), 2 * 3600 + 30 * 60 + 40],
            [$this->time(0, 0, 0, 0), $this->time(2, 30, 40, 1500), CU::MINUTES(), 2 * 60 + 30],
            [$this->time(0, 0, 0, 0), $this->time(2, 30, 40, 1500), CU::HOURS(), 2],
        ];
    }

    /**
     * @dataProvider data_periodUntilUnit
     */
    public function test_until_TemporalUnit(LocalTime $time1, LocalTime $time2, TemporalUnit $unit, $expected)
    {
        $amount = $time1->until($time2, $unit);
        $this->assertEquals($amount, $expected);
    }

    /**
     * @dataProvider data_periodUntilUnit
     */
    public function test_until_TemporalUnit_negated(LocalTime $time1, LocalTime $time2, TemporalUnit $unit, $expected)
    {
        $amount = $time2->until($time1, $unit);
        $this->assertEquals($amount, -$expected);
    }

    /**
     * @dataProvider data_periodUntilUnit
     */
    public function test_until_TemporalUnit_between(LocalTime $time1, LocalTime $time2, TemporalUnit $unit, $expected)
    {
        $amount = $unit->between($time1, $time2);
        $this->assertEquals($amount, $expected);
    }


    public function test_until_convertedType()
    {
        $start = LocalTime::of(11, 30);
        $end = $start->plusSeconds(2)->atDate(LocalDate::of(2010, 6, 30));
        $this->assertEquals($start->until($end, CU::SECONDS()), 2);
    }

    /**
     * @expectedException \Celest\DateTimeException
     */
    public function test_until_invalidType()
    {
        $start = LocalTime::of(11, 30);
        $start->until(LocalDate::of(2010, 6, 30), CU::SECONDS());
    }

    /**
     * @expectedException \Celest\Temporal\UnsupportedTemporalTypeException
     */
    public function test_until_TemporalUnit_unsupportedUnit()
    {
        self::TEST_123040987654321()->until(self::TEST_123040987654321(), CU::DAYS());
    }

    public function test_until_TemporalUnit_nullEnd()
    {
        TestHelper::assertNullException($this, function () {
            self::TEST_123040987654321()->until(null, CU::HOURS());
        });
    }

    public function test_until_TemporalUnit_nullUnit()
    {
        TestHelper::assertNullException($this, function () {
            self::TEST_123040987654321()->until(self::TEST_123040987654321(), null);
        });
    }

    //-----------------------------------------------------------------------
    // format(DateTimeFormatter)
    //-----------------------------------------------------------------------

    public function test_format_formatter()
    {
        $f = DateTimeFormatter::ofPattern("H m s");
        $t = LocalTime::of(11, 30, 45)->format($f);
        $this->assertEquals($t, "11 30 45");
    }

    public function test_format_formatter_null()
    {
        TestHelper::assertNullException($this, function () {
            LocalTime::of(11, 30, 45)->format(null);
        });
    }

    //-----------------------------------------------------------------------
    // atDate()
    //-----------------------------------------------------------------------

    public function test_atDate()
    {
        $t = LocalTime::of(11, 30);
        $this->assertEquals($t->atDate(LocalDate::of(2012, 6, 30)), LocalDateTime::of(2012, 6, 30, 11, 30));
    }

    public function test_atDate_nullDate()
    {
        TestHelper::assertNullException($this, function () {
            self::TEST_123040987654321()->atDate(null);
        });
    }

    //-----------------------------------------------------------------------
    // atOffset()
    //-----------------------------------------------------------------------

    public function test_atOffset()
    {
        $t = LocalTime::of(11, 30);
        $this->assertEquals($t->atOffset(self::OFFSET_PTWO()), OffsetTime::ofLocalTime(LocalTime::of(11, 30), self::OFFSET_PTWO()));
    }

    public function test_atOffset_nullZoneOffset()
    {
        TestHelper::assertNullException($this, function () {
            $t = LocalTime::of(11, 30);
            $t->atOffset(null);
        });

    }

    //-----------------------------------------------------------------------
    // toSecondOfDay()
    //-----------------------------------------------------------------------

    /**
     * @group long
     */
    public function test_toSecondOfDay()
    {
        $t = LocalTime::of(0, 0);
        for ($i = 0; $i < 24 * 60 * 60; $i++) {
            $this->assertEquals($t->toSecondOfDay(), $i);
            $t = $t->plusSeconds(1);
        }
    }

    /**
     * @group long
     */
    public function test_toSecondOfDay_fromNanoOfDay_symmetry()
    {
        $t = LocalTime::of(0, 0);
        for ($i = 0; $i < 24 * 60 * 60; $i++) {
            $this->assertEquals(LocalTime::ofSecondOfDay($t->toSecondOfDay()), $t);
            $t = $t->plusSeconds(1);
        }
    }

    //-----------------------------------------------------------------------
    // toNanoOfDay()
    //-----------------------------------------------------------------------
    /**
     * @group long
     */
    public function test_toNanoOfDay()
    {
        $t = LocalTime::of(0, 0);
        for ($i = 0; $i < 1000000; $i++) {
            $this->assertEquals($t->toNanoOfDay(), $i);
            $t = $t->plusNanos(1);
        }
        $t = LocalTime::of(0, 0);
        for ($i = 1; $i <= 1000000; $i++) {
            $t = $t->minusNanos(1);
            $this->assertEquals($t->toNanoOfDay(), 24 * 60 * 60 * 1000000000 - $i);
        }
    }

    /**
     * @group long
     */
    public function test_toNanoOfDay_fromNanoOfDay_symmetry()
    {
        $t = LocalTime::of(0, 0);
        for ($i = 0; $i < 1000000; $i++) {
            $this->assertEquals(LocalTime::ofNanoOfDay($t->toNanoOfDay()), $t);
            $t = $t->plusNanos(1);
        }
        $t = LocalTime::of(0, 0);
        for ($i = 1; $i <= 1000000; $i++) {
            $t = $t->minusNanos(1);
            $this->assertEquals(LocalTime::ofNanoOfDay($t->toNanoOfDay()), $t);
        }
    }

    //-----------------------------------------------------------------------
    // compareTo()
    //-----------------------------------------------------------------------

    public function test_comparisons()
    {
        $this->doTest_comparisons_LocalTime([
                LocalTime::MIDNIGHT(),
                LocalTime::of(0, 0, 0, 999999999),
                LocalTime::of(0, 0, 59, 0),
                LocalTime::of(0, 0, 59, 999999999),
                LocalTime::of(0, 59, 0, 0),
                LocalTime::of(0, 59, 0, 999999999),
                LocalTime::of(0, 59, 59, 0),
                LocalTime::of(0, 59, 59, 999999999),
                LocalTime::NOON(),
                LocalTime::of(12, 0, 0, 999999999),
                LocalTime::of(12, 0, 59, 0),
                LocalTime::of(12, 0, 59, 999999999),
                LocalTime::of(12, 59, 0, 0),
                LocalTime::of(12, 59, 0, 999999999),
                LocalTime::of(12, 59, 59, 0),
                LocalTime::of(12, 59, 59, 999999999),
                LocalTime::of(23, 0, 0, 0),
                LocalTime::of(23, 0, 0, 999999999),
                LocalTime::of(23, 0, 59, 0),
                LocalTime::of(23, 0, 59, 999999999),
                LocalTime::of(23, 59, 0, 0),
                LocalTime::of(23, 59, 0, 999999999),
                LocalTime::of(23, 59, 59, 0),
                LocalTime::of(23, 59, 59, 999999999)]
        );
    }

    /**
     * @param $localTimes LocalTime[]
     */
    function doTest_comparisons_LocalTime($localTimes)
    {
        for ($i = 0; $i < count($localTimes); $i++) {
            $a = $localTimes[$i];
            for ($j = 0; $j < count($localTimes); $j++) {
                $b = $localTimes[$j];
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
            self::TEST_123040987654321()->compareTo(null);
        });
    }

    public function test_isBefore_ObjectNull()
    {
        TestHelper::assertNullException($this, function () {
            self::TEST_123040987654321()->isBefore(null);
        });
    }

    public function test_isAfter_ObjectNull()
    {
        TestHelper::assertNullException($this, function () {
            self::TEST_123040987654321()->isAfter(null);
        });
    }

    public function test_compareToNonLocalTime()
    {
        TestHelper::assertTypeError($this, function () {
            $c = self::TEST_123040987654321();
            $c->compareTo(new \stdClass());
        });
    }

//-----------------------------------------------------------------------
// equals()
//-----------------------------------------------------------------------
    /**
     * @dataProvider provider_sampleTimes
     */
    public function test_equals_true($h, $m, $s, $n)
    {
        $a = LocalTime::of($h, $m, $s, $n);
        $b = LocalTime::of($h, $m, $s, $n);
        $this->assertEquals($a->equals($b), true);
    }

    /**
     * @dataProvider provider_sampleTimes
     */
    public function test_equals_false_hour_differs($h, $m, $s, $n)
    {
        $a = LocalTime::of($h, $m, $s, $n);
        $b = LocalTime::of($h + 1, $m, $s, $n);
        $this->assertEquals($a->equals($b), false);
    }

    /**
     * @dataProvider provider_sampleTimes
     */
    public function test_equals_false_minute_differs($h, $m, $s, $n)
    {
        $a = LocalTime::of($h, $m, $s, $n);
        $b = LocalTime::of($h, $m + 1, $s, $n);
        $this->assertEquals($a->equals($b), false);
    }

    /**
     * @dataProvider provider_sampleTimes
     */
    public function test_equals_false_second_differs($h, $m, $s, $n)
    {
        $a = LocalTime::of($h, $m, $s, $n);
        $b = LocalTime::of($h, $m, $s + 1, $n);
        $this->assertEquals($a->equals($b), false);
    }

    /**
     * @dataProvider provider_sampleTimes
     */
    public function test_equals_false_nano_differs($h, $m, $s, $n)
    {
        $a = LocalTime::of($h, $m, $s, $n);
        $b = LocalTime::of($h, $m, $s, $n + 1);
        $this->assertEquals($a->equals($b), false);
    }


    public function test_equals_itself_true()
    {
        $this->assertEquals(self::TEST_123040987654321()->equals(self::TEST_123040987654321()), true);
    }


    public function test_equals_string_false()
    {
        $this->assertEquals(self::TEST_123040987654321()->equals("2007-07-15"), false);
    }


    public function test_equals_null_false()
    {
        $this->assertEquals(self::TEST_123040987654321()->equals(null), false);
    }

    //-----------------------------------------------------------------------
    // toString()
    //-----------------------------------------------------------------------
    function provider_sampleToString()
    {
        return [
            [0, 0, 0, 0, "00:00"],
            [1, 0, 0, 0, "01:00"],
            [23, 0, 0, 0, "23:00"],
            [0, 1, 0, 0, "00:01"],
            [12, 30, 0, 0, "12:30"],
            [23, 59, 0, 0, "23:59"],
            [0, 0, 1, 0, "00:00:01"],
            [0, 0, 59, 0, "00:00:59"],
            [0, 0, 0, 100000000, "00:00:00.100"],
            [0, 0, 0, 10000000, "00:00:00.010"],
            [0, 0, 0, 1000000, "00:00:00.001"],
            [0, 0, 0, 100000, "00:00:00.000100"],
            [0, 0, 0, 10000, "00:00:00.000010"],
            [0, 0, 0, 1000, "00:00:00.000001"],
            [0, 0, 0, 100, "00:00:00.000000100"],
            [0, 0, 0, 10, "00:00:00.000000010"],
            [0, 0, 0, 1, "00:00:00.000000001"],
            [0, 0, 0, 999999999, "00:00:00.999999999"],
            [0, 0, 0, 99999999, "00:00:00.099999999"],
            [0, 0, 0, 9999999, "00:00:00.009999999"],
            [0, 0, 0, 999999, "00:00:00.000999999"],
            [0, 0, 0, 99999, "00:00:00.000099999"],
            [0, 0, 0, 9999, "00:00:00.000009999"],
            [0, 0, 0, 999, "00:00:00.000000999"],
            [0, 0, 0, 99, "00:00:00.000000099"],
            [0, 0, 0, 9, "00:00:00.000000009"],
        ];
    }

    /**
     * @dataProvider provider_sampleToString
     */
    public function test_toString($h, $m, $s, $n, $expected)
    {
        $t = LocalTime::of($h, $m, $s, $n);
        $str = $t->__toString();
        $this->assertEquals($str, $expected);
    }

    /**
     * @dataProvider provider_sampleToString
     */
    public function test_jsonSerialize($h, $m, $s, $n, $expected)
    {
        $t = LocalTime::of($h, $m, $s, $n);
        $str = $t->__toString();
        $this->assertEquals(json_decode(json_encode($t)), $expected);
    }

    private function time($hour, $min, $sec, $nano)
    {
        return LocalTime::of($hour, $min, $sec, $nano);
    }
}
