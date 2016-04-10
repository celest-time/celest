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
use Celest\Helper\Integer;
use Celest\Helper\Long;
use Celest\Helper\Math;
use Celest\Temporal\ChronoField as CF;
use Celest\Temporal\ChronoUnit as CU;
use Celest\Temporal\JulianFields;
use Celest\Temporal\TemporalAccessor;
use Celest\Temporal\TemporalAmount;
use Celest\Temporal\TemporalQueries;
use Celest\Temporal\TemporalQuery;
use Celest\Temporal\TemporalUnit;
use Exception;

class TCKYearMonth extends AbstractDateTimeTest
{


    private static function TEST_2008_06()
    {
        return YearMonth::of(2008, 6);
    }

    //-----------------------------------------------------------------------
    protected function samples()
    {
        return [self::TEST_2008_06()];
    }

    protected function validFields()
    {
        return [
            CF::MONTH_OF_YEAR(),
            CF::PROLEPTIC_MONTH(),
            CF::YEAR_OF_ERA(),
            CF::YEAR(),
            CF::ERA(),
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
    private function check(YearMonth $test, $y, $m)
    {
        $this->assertEquals($test->getYear(), $y);
        $this->assertEquals($test->getMonth()->getValue(), $m);
    }

    //-----------------------------------------------------------------------
    // now()
    //-----------------------------------------------------------------------

    public function test_now()
    {
        $expected = YearMonth::nowOf(Clock::systemDefaultZone());
        $test = YearMonth::now();
        for ($i = 0; $i < 100; $i++) {
            if ($expected->equals($test)) {
                return;
            }
            $expected = YearMonth::nowOf(Clock::systemDefaultZone());
            $test = YearMonth::now();
        }
        $this->assertEquals($test, $expected);
    }

    //-----------------------------------------------------------------------
    // now(ZoneId)
    //-----------------------------------------------------------------------
    public function test_now_ZoneId_nullZoneId()
    {
        TestHelper::assertNullException($this, function () {
            YearMonth::nowIn(null);
        });
    }


    public function test_now_ZoneId()
    {
        $zone = ZoneId::of("UTC+01:02:03");
        $expected = YearMonth::nowOf(Clock::system($zone));
        $test = YearMonth::nowIn($zone);
        for ($i = 0; $i < 100; $i++) {
            if ($expected->equals($test)) {
                return;
            }
            $expected = YearMonth::nowOf(Clock::system($zone));
            $test = YearMonth::nowIn($zone);
        }
        $this->assertEquals($test, $expected);
    }

    //-----------------------------------------------------------------------
    // now(Clock)
    //-----------------------------------------------------------------------

    public function test_now_Clock()
    {
        $instant = LocalDateTime::of(2010, 12, 31, 0, 0)->toInstant(ZoneOffset::UTC());
        $clock = Clock::fixed($instant, ZoneOffset::UTC());
        $test = YearMonth::nowOf($clock);
        $this->assertEquals($test->getYear(), 2010);
        $this->assertEquals($test->getMonth(), Month::DECEMBER());
    }

    public function test_now_Clock_nullClock()
    {
        TestHelper::assertNullException($this, function () {
            YearMonth::nowOf(null);
        });
    }

    //-----------------------------------------------------------------------

    public function test_factory_intsMonth()
    {
        $test = YearMonth::ofMonth(2008, Month::FEBRUARY());
        $this->check($test, 2008, 2);
    }

    /**
     * @expectedException \Celest\DateTimeException
     */

    public function test_factory_intsMonth_yearTooLow()
    {
        YearMonth::ofMonth(Year::MIN_VALUE - 1, Month::JANUARY());
    }

    /**
     * @expectedException \Celest\DateTimeException
     */

    public function test_factory_intsMonth_dayTooHigh()
    {
        YearMonth::ofMonth(Year::MAX_VALUE + 1, Month::JANUARY());
    }

    public function test_factory_intsMonth_nullMonth()
    {
        TestHelper::assertNullException($this, function () {
            YearMonth::ofMonth(2008, null);
        });
    }

    //-----------------------------------------------------------------------

    public function test_factory_ints()
    {
        $test = YearMonth::of(2008, 2);
        $this->check($test, 2008, 2);
    }

    /**
     * @expectedException \Celest\DateTimeException
     */

    public function test_factory_ints_yearTooLow()
    {
        YearMonth::of(Year::MIN_VALUE - 1, 2);
    }

    /**
     * @expectedException \Celest\DateTimeException
     */

    public function test_factory_ints_dayTooHigh()
    {
        YearMonth::of(Year::MAX_VALUE + 1, 2);
    }

    /**
     * @expectedException \Celest\DateTimeException
     */

    public function test_factory_ints_monthTooLow()
    {
        YearMonth::of(2008, 0);
    }

    /**
     * @expectedException \Celest\DateTimeException
     */

    public function test_factory_ints_monthTooHigh()
    {
        YearMonth::of(2008, 13);
    }

    //-----------------------------------------------------------------------

    public function test_from_TemporalAccessor()
    {
        $this->assertEquals(YearMonth::from(LocalDate::of(2007, 7, 15)), YearMonth::of(2007, 7));
    }

    /**
     * @expectedException \Celest\DateTimeException
     */

    public function test_from_TemporalAccessor_invalid_noDerive()
    {
        YearMonth::from(LocalTime::of(12, 30));
    }

    public function test_from_TemporalAccessor_null()
    {
        TestHelper::assertNullException($this, function () {
            YearMonth::from(null);
        });
    }

    //-----------------------------------------------------------------------
    // parse()
    //-----------------------------------------------------------------------
    function provider_goodParseData()
    {
        return [
            ["0000-01", YearMonth::of(0, 1)],
            ["0000-12", YearMonth::of(0, 12)],
            ["9999-12", YearMonth::of(9999, 12)],
            ["2000-01", YearMonth::of(2000, 1)],
            ["2000-02", YearMonth::of(2000, 2)],
            ["2000-03", YearMonth::of(2000, 3)],
            ["2000-04", YearMonth::of(2000, 4)],
            ["2000-05", YearMonth::of(2000, 5)],
            ["2000-06", YearMonth::of(2000, 6)],
            ["2000-07", YearMonth::of(2000, 7)],
            ["2000-08", YearMonth::of(2000, 8)],
            ["2000-09", YearMonth::of(2000, 9)],
            ["2000-10", YearMonth::of(2000, 10)],
            ["2000-11", YearMonth::of(2000, 11)],
            ["2000-12", YearMonth::of(2000, 12)],

            ["+12345678-03", YearMonth::of(12345678, 3)],
            ["+123456-03", YearMonth::of(123456, 3)],
            ["0000-03", YearMonth::of(0, 3)],
            ["-1234-03", YearMonth::of(-1234, 3)],
            ["-12345678-03", YearMonth::of(-12345678, 3)],

            ["+" . Year::MAX_VALUE . "-03", YearMonth::of(Year::MAX_VALUE, 3)],
            [Year::MIN_VALUE . "-03", YearMonth::of(Year::MIN_VALUE, 3)],
        ];
    }

    /**
     * @dataProvider provider_goodParseData
     */
    public function test_factory_parse_success($text, YearMonth $expected)
    {
        $yearMonth = YearMonth::parse($text);
        $this->assertEquals($yearMonth, $expected);
    }

    //-----------------------------------------------------------------------
    function provider_badParseData()
    {
        return [
            ["", 0],
            ["-00", 1],
            ["--01-0", 1],
            ["A01-3", 0],
            ["200-01", 0],
            ["2009/12", 4],

            ["-0000-10", 0],
            ["-12345678901-10", 11],
            ["+1-10", 1],
            ["+12-10", 1],
            ["+123-10", 1],
            ["+1234-10", 0],
            ["12345-10", 0],
            ["+12345678901-10", 11],
        ];
    }

    /**
     * @dataProvider provider_badParseData
     * @expectedException \Celest\DateTimeParseException
     */

    public function test_factory_parse_fail($text, $pos)
    {
        try {
            YearMonth::parse($text);
            $this->fail(sprintf("Parse should have failed for %s at position %d", $text, $pos));
        } catch (DateTimeParseException $ex) {
            $this->assertEquals($ex->getParsedString(), $text);
            $this->assertEquals($ex->getErrorIndex(), $pos);
            throw $ex;
        }
    }

    //-----------------------------------------------------------------------
    /**
     * @expectedException \Celest\DateTimeParseException
     */

    public function test_factory_parse_illegalValue_Month()
    {
        YearMonth::parse("2008-13");
    }

    public function test_factory_parse_nullText()
    {
        TestHelper::assertNullException($this, function () {
            YearMonth::parse(null);
        });
    }

    //-----------------------------------------------------------------------
    // parse(DateTimeFormatter)
    //-----------------------------------------------------------------------

    public function test_factory_parse_formatter()
    {
        $f = DateTimeFormatter::ofPattern("y M");
        $test = YearMonth::parseWith("2010 12", $f);
        $this->assertEquals($test, YearMonth::of(2010, 12));
    }

    public function test_factory_parse_formatter_nullText()
    {
        TestHelper::assertNullException($this, function () {
            $f = DateTimeFormatter::ofPattern("y M");
            YearMonth::parseWith(null, $f);
        });
    }

    public function test_factory_parse_formatter_nullFormatter()
    {
        TestHelper::assertNullException($this, function () {
            YearMonth::parseWith("ANY", null);
        });
    }

    //-----------------------------------------------------------------------
    // isSupported(TemporalField)
    //-----------------------------------------------------------------------

    public function test_isSupported_TemporalField()
    {
        // TODO $this->assertEquals(self::TEST_2008_06()->isSupported(null), false);
        $this->assertEquals(self::TEST_2008_06()->isSupported(CF::NANO_OF_SECOND()), false);
        $this->assertEquals(self::TEST_2008_06()->isSupported(CF::NANO_OF_DAY()), false);
        $this->assertEquals(self::TEST_2008_06()->isSupported(CF::MICRO_OF_SECOND()), false);
        $this->assertEquals(self::TEST_2008_06()->isSupported(CF::MICRO_OF_DAY()), false);
        $this->assertEquals(self::TEST_2008_06()->isSupported(CF::MILLI_OF_SECOND()), false);
        $this->assertEquals(self::TEST_2008_06()->isSupported(CF::MILLI_OF_DAY()), false);
        $this->assertEquals(self::TEST_2008_06()->isSupported(CF::SECOND_OF_MINUTE()), false);
        $this->assertEquals(self::TEST_2008_06()->isSupported(CF::SECOND_OF_DAY()), false);
        $this->assertEquals(self::TEST_2008_06()->isSupported(CF::MINUTE_OF_HOUR()), false);
        $this->assertEquals(self::TEST_2008_06()->isSupported(CF::MINUTE_OF_DAY()), false);
        $this->assertEquals(self::TEST_2008_06()->isSupported(CF::HOUR_OF_AMPM()), false);
        $this->assertEquals(self::TEST_2008_06()->isSupported(CF::CLOCK_HOUR_OF_AMPM()), false);
        $this->assertEquals(self::TEST_2008_06()->isSupported(CF::HOUR_OF_DAY()), false);
        $this->assertEquals(self::TEST_2008_06()->isSupported(CF::CLOCK_HOUR_OF_DAY()), false);
        $this->assertEquals(self::TEST_2008_06()->isSupported(CF::AMPM_OF_DAY()), false);
        $this->assertEquals(self::TEST_2008_06()->isSupported(CF::DAY_OF_WEEK()), false);
        $this->assertEquals(self::TEST_2008_06()->isSupported(CF::ALIGNED_DAY_OF_WEEK_IN_MONTH()), false);
        $this->assertEquals(self::TEST_2008_06()->isSupported(CF::ALIGNED_DAY_OF_WEEK_IN_YEAR()), false);
        $this->assertEquals(self::TEST_2008_06()->isSupported(CF::DAY_OF_MONTH()), false);
        $this->assertEquals(self::TEST_2008_06()->isSupported(CF::DAY_OF_YEAR()), false);
        $this->assertEquals(self::TEST_2008_06()->isSupported(CF::EPOCH_DAY()), false);
        $this->assertEquals(self::TEST_2008_06()->isSupported(CF::ALIGNED_WEEK_OF_MONTH()), false);
        $this->assertEquals(self::TEST_2008_06()->isSupported(CF::ALIGNED_WEEK_OF_YEAR()), false);
        $this->assertEquals(self::TEST_2008_06()->isSupported(CF::MONTH_OF_YEAR()), true);
        $this->assertEquals(self::TEST_2008_06()->isSupported(CF::PROLEPTIC_MONTH()), true);
        $this->assertEquals(self::TEST_2008_06()->isSupported(CF::YEAR()), true);
        $this->assertEquals(self::TEST_2008_06()->isSupported(CF::YEAR_OF_ERA()), true);
        $this->assertEquals(self::TEST_2008_06()->isSupported(CF::ERA()), true);
        $this->assertEquals(self::TEST_2008_06()->isSupported(CF::INSTANT_SECONDS()), false);
        $this->assertEquals(self::TEST_2008_06()->isSupported(CF::OFFSET_SECONDS()), false);
    }

    //-----------------------------------------------------------------------
    // isSupported(TemporalUnit)
    //-----------------------------------------------------------------------

    public function test_isSupported_TemporalUnit()
    {
        // TODO $this->assertEquals(self::TEST_2008_06()->isUnitSupported(null), false);
        $this->assertEquals(self::TEST_2008_06()->isUnitSupported(CU::NANOS()), false);
        $this->assertEquals(self::TEST_2008_06()->isUnitSupported(CU::MICROS()), false);
        $this->assertEquals(self::TEST_2008_06()->isUnitSupported(CU::MILLIS()), false);
        $this->assertEquals(self::TEST_2008_06()->isUnitSupported(CU::SECONDS()), false);
        $this->assertEquals(self::TEST_2008_06()->isUnitSupported(CU::MINUTES()), false);
        $this->assertEquals(self::TEST_2008_06()->isUnitSupported(CU::HOURS()), false);
        $this->assertEquals(self::TEST_2008_06()->isUnitSupported(CU::HALF_DAYS()), false);
        $this->assertEquals(self::TEST_2008_06()->isUnitSupported(CU::DAYS()), false);
        $this->assertEquals(self::TEST_2008_06()->isUnitSupported(CU::WEEKS()), false);
        $this->assertEquals(self::TEST_2008_06()->isUnitSupported(CU::MONTHS()), true);
        $this->assertEquals(self::TEST_2008_06()->isUnitSupported(CU::YEARS()), true);
        $this->assertEquals(self::TEST_2008_06()->isUnitSupported(CU::DECADES()), true);
        $this->assertEquals(self::TEST_2008_06()->isUnitSupported(CU::CENTURIES()), true);
        $this->assertEquals(self::TEST_2008_06()->isUnitSupported(CU::MILLENNIA()), true);
        $this->assertEquals(self::TEST_2008_06()->isUnitSupported(CU::ERAS()), true);
        $this->assertEquals(self::TEST_2008_06()->isUnitSupported(CU::FOREVER()), false);
    }

    //-----------------------------------------------------------------------
    // get(TemporalField)
    //-----------------------------------------------------------------------

    public function test_get_TemporalField()
    {
        $this->assertEquals(self::TEST_2008_06()->get(CF::YEAR()), 2008);
        $this->assertEquals(self::TEST_2008_06()->get(CF::MONTH_OF_YEAR()), 6);
        $this->assertEquals(self::TEST_2008_06()->get(CF::YEAR_OF_ERA()), 2008);
        $this->assertEquals(self::TEST_2008_06()->get(CF::ERA()), 1);
    }


    public function test_getLong_TemporalField()
    {
        $this->assertEquals(self::TEST_2008_06()->getLong(CF::YEAR()), 2008);
        $this->assertEquals(self::TEST_2008_06()->getLong(CF::MONTH_OF_YEAR()), 6);
        $this->assertEquals(self::TEST_2008_06()->getLong(CF::YEAR_OF_ERA()), 2008);
        $this->assertEquals(self::TEST_2008_06()->getLong(CF::ERA()), 1);
        $this->assertEquals(self::TEST_2008_06()->getLong(CF::PROLEPTIC_MONTH()), 2008 * 12 + 6 - 1);
    }

    //-----------------------------------------------------------------------
    // query(TemporalQuery)
    //-----------------------------------------------------------------------
    function data_query()
    {
        return [
            [self::TEST_2008_06(), TemporalQueries::chronology(), IsoChronology::INSTANCE()],
            [self::TEST_2008_06(), TemporalQueries::zoneId(), null],
            [self::TEST_2008_06(), TemporalQueries::precision(), CU::MONTHS()],
            [self::TEST_2008_06(), TemporalQueries::zone(), null],
            [self::TEST_2008_06(), TemporalQueries::offset(), null],
            [self::TEST_2008_06(), TemporalQueries::localDate(), null],
            [self::TEST_2008_06(), TemporalQueries::localTime(), null],
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
            self::TEST_2008_06()->query(null);
        });
    }

    //-----------------------------------------------------------------------
    // get*()
    //-----------------------------------------------------------------------
    function provider_sampleDates()
    {
        return [
            [2008, 1],
            [2008, 2],
            [-1, 3],
            [0, 12],
        ];
    }

    /**
     * @dataProvider provider_sampleDates
     */
    public function test_get($y, $m)
    {
        $a = YearMonth::of($y, $m);
        $this->assertEquals($a->getYear(), $y);
        $this->assertEquals($a->getMonth(), Month::of($m));
        $this->assertEquals($a->getMonthValue(), $m);
    }

    //-----------------------------------------------------------------------
    // with(Year)
    //-----------------------------------------------------------------------

    public function test_with_Year()
    {
        $test = YearMonth::of(2008, 6);
        $this->assertEquals($test->adjust(Year::of(2000)), YearMonth::of(2000, 6));
    }


    public function test_with_Year_noChange_equal()
    {
        $test = YearMonth::of(2008, 6);
        $this->assertEquals($test->adjust(Year::of(2008)), $test);
    }

    public function test_with_Year_null()
    {
        TestHelper::assertNullException($this, function () {
            $test = YearMonth::of(2008, 6);
            $test->adjust(null);
        });

    }

    //-----------------------------------------------------------------------
    // with(Month)
    //-----------------------------------------------------------------------

    public function test_with_Month()
    {
        $test = YearMonth::of(2008, 6);
        $this->assertEquals($test->adjust(Month::JANUARY()), YearMonth::of(2008, 1));
    }


    public function test_with_Month_noChange_equal()
    {
        $test = YearMonth::of(2008, 6);
        $this->assertEquals($test->adjust(Month::JUNE()), $test);
    }

    public function test_with_Month_null()
    {
        TestHelper::assertNullException($this, function () {
            $test = YearMonth::of(2008, 6);
            $test->adjust(null);
        });

    }

    //-----------------------------------------------------------------------
    // withYear()
    //-----------------------------------------------------------------------

    public function test_withYear()
    {
        $test = YearMonth::of(2008, 6);
        $this->assertEquals($test->withYear(1999), YearMonth::of(1999, 6));
    }


    public function test_withYear_int_noChange_equal()
    {
        $test = YearMonth::of(2008, 6);
        $this->assertEquals($test->withYear(2008), $test);
    }

    /**
     * @expectedException \Celest\DateTimeException
     */

    public function test_withYear_tooLow()
    {
        $test = YearMonth::of(2008, 6);
        $test->withYear(Year::MIN_VALUE - 1);
    }

    /**
     * @expectedException \Celest\DateTimeException
     */

    public function test_withYear_tooHigh()
    {
        $test = YearMonth::of(2008, 6);
        $test->withYear(Year::MAX_VALUE + 1);
    }

    //-----------------------------------------------------------------------
    // withMonth()
    //-----------------------------------------------------------------------

    public function test_withMonth()
    {
        $test = YearMonth::of(2008, 6);
        $this->assertEquals($test->withMonth(1), YearMonth::of(2008, 1));
    }


    public function test_withMonth_int_noChange_equal()
    {
        $test = YearMonth::of(2008, 6);
        $this->assertEquals($test->withMonth(6), $test);
    }

    /**
     * @expectedException \Celest\DateTimeException
     */

    public function test_withMonth_tooLow()
    {
        $test = YearMonth::of(2008, 6);
        $test->withMonth(0);
    }

    /**
     * @expectedException \Celest\DateTimeException
     */

    public function test_withMonth_tooHigh()
    {
        $test = YearMonth::of(2008, 6);
        $test->withMonth(13);
    }

    //-----------------------------------------------------------------------
    // plusYears()
    //-----------------------------------------------------------------------

    public function test_plusYears_long()
    {
        $test = YearMonth::of(2008, 6);
        $this->assertEquals($test->plusYears(1), YearMonth::of(2009, 6));
    }


    public function test_plusYears_long_noChange_equal()
    {
        $test = YearMonth::of(2008, 6);
        $this->assertEquals($test->plusYears(0), $test);
    }


    public function test_plusYears_long_negative()
    {
        $test = YearMonth::of(2008, 6);
        $this->assertEquals($test->plusYears(-1), YearMonth::of(2007, 6));
    }


    public function test_plusYears_long_big()
    {
        $test = YearMonth::of(-40, 6);
        $this->assertEquals($test->plusYears(20 + Year::MAX_VALUE), YearMonth::of((int)(-40 + 20 + Year::MAX_VALUE), 6));
    }

    /**
     * @expectedException \Celest\DateTimeException
     */

    public function test_plusYears_long_invalidTooLarge()
    {
        $test = YearMonth::of(Year::MAX_VALUE, 6);
        $test->plusYears(1);
    }

    /**
     * @expectedException \Celest\DateTimeException
     */

    public function test_plusYears_long_invalidTooLargeMaxAddMax()
    {
        $test = YearMonth::of(Year::MAX_VALUE, 12);
        $test->plusYears(Long::MAX_VALUE);
    }

    /**
     * @expectedException \Celest\DateTimeException
     */

    public function test_plusYears_long_invalidTooLargeMaxAddMin()
    {
        $test = YearMonth::of(Year::MAX_VALUE, 12);
        $test->plusYears(Long::MIN_VALUE);
    }

    /**
     * @expectedException \Celest\DateTimeException
     */

    public function test_plusYears_long_invalidTooSmall()
    {
        $test = YearMonth::of(Year::MIN_VALUE, 6);
        $test->plusYears(-1);
    }

    //-----------------------------------------------------------------------
    // plusMonths()
    //-----------------------------------------------------------------------

    public function test_plusMonths_long()
    {
        $test = YearMonth::of(2008, 6);
        $this->assertEquals($test->plusMonths(1), YearMonth::of(2008, 7));
    }


    public function test_plusMonths_long_noChange_equal()
    {
        $test = YearMonth::of(2008, 6);
        $this->assertEquals($test->plusMonths(0), $test);
    }


    public function test_plusMonths_long_overYears()
    {
        $test = YearMonth::of(2008, 6);
        $this->assertEquals($test->plusMonths(7), YearMonth::of(2009, 1));
    }


    public function test_plusMonths_long_negative()
    {
        $test = YearMonth::of(2008, 6);
        $this->assertEquals($test->plusMonths(-1), YearMonth::of(2008, 5));
    }


    public function test_plusMonths_long_negativeOverYear()
    {
        $test = YearMonth::of(2008, 6);
        $this->assertEquals($test->plusMonths(-6), YearMonth::of(2007, 12));
    }


    public function test_plusMonths_long_big()
    {
        $test = YearMonth::of(-40, 6);
        $months = 20 + Integer::MAX_VALUE;
        $this->assertEquals($test->plusMonths($months), YearMonth::of((-40 + Math::div($months, 12)), 6 + ($months % 12)));
    }

    /**
     * @expectedException \Celest\DateTimeException
     */

    public function test_plusMonths_long_invalidTooLarge()
    {
        $test = YearMonth::of(Year::MAX_VALUE, 12);
        $test->plusMonths(1);
    }

    /**
     * @expectedException \Celest\DateTimeException
     */

    public function test_plusMonths_long_invalidTooLargeMaxAddMax()
    {
        $test = YearMonth::of(Year::MAX_VALUE, 12);
        $test->plusMonths(Long::MAX_VALUE);
    }

    /**
     * @expectedException \Celest\DateTimeException
     */

    public function test_plusMonths_long_invalidTooLargeMaxAddMin()
    {
        $test = YearMonth::of(Year::MAX_VALUE, 12);
        $test->plusMonths(Long::MIN_VALUE);
    }

    /**
     * @expectedException \Celest\DateTimeException
     */

    public function test_plusMonths_long_invalidTooSmall()
    {
        $test = YearMonth::of(Year::MIN_VALUE, 1);
        $test->plusMonths(-1);
    }

    //-----------------------------------------------------------------------
    // plus(long, TemporalUnit)
    //-----------------------------------------------------------------------
    function data_plus_long_TemporalUnit()
    {
        return [
            [YearMonth::of(1, 10), 1, CU::YEARS(), YearMonth::of(2, 10), null],
            [YearMonth::of(1, 10), -12, CU::YEARS(), YearMonth::of(-11, 10), null],
            [YearMonth::of(1, 10), 0, CU::YEARS(), YearMonth::of(1, 10), null],
            [YearMonth::of(999999999, 12), 0, CU::YEARS(), YearMonth::of(999999999, 12), null],
            [YearMonth::of(-999999999, 1), 0, CU::YEARS(), YearMonth::of(-999999999, 1), null],
            [YearMonth::of(0, 1), -999999999, CU::YEARS(), YearMonth::of(-999999999, 1), null],
            [YearMonth::of(0, 12), 999999999, CU::YEARS(), YearMonth::of(999999999, 12), null],

            [YearMonth::of(1, 10), 1, CU::MONTHS(), YearMonth::of(1, 11), null],
            [YearMonth::of(1, 10), -12, CU::MONTHS(), YearMonth::of(0, 10), null],
            [YearMonth::of(1, 10), 0, CU::MONTHS(), YearMonth::of(1, 10), null],
            [YearMonth::of(999999999, 12), 0, CU::MONTHS(), YearMonth::of(999999999, 12), null],
            [YearMonth::of(-999999999, 1), 0, CU::MONTHS(), YearMonth::of(-999999999, 1), null],
            [YearMonth::of(-999999999, 2), -1, CU::MONTHS(), YearMonth::of(-999999999, 1), null],
            [YearMonth::of(999999999, 3), 9, CU::MONTHS(), YearMonth::of(999999999, 12), null],

            [YearMonth::of(-1, 10), 1, CU::ERAS(), YearMonth::of(2, 10), null],
            [YearMonth::of(5, 10), 1, CU::CENTURIES(), YearMonth::of(105, 10), null],
            [YearMonth::of(5, 10), 1, CU::DECADES(), YearMonth::of(15, 10), null],

            [YearMonth::of(999999999, 12), 1, CU::MONTHS(), null, DateTimeException::class],
            [YearMonth::of(-999999999, 1), -1, CU::MONTHS(), null, DateTimeException::class],

            [YearMonth::of(1, 1), 0, CU::DAYS(), null, DateTimeException::class],
            [YearMonth::of(1, 1), 0, CU::WEEKS(), null, DateTimeException::class],
        ];
    }

    /**
     * @dataProvider data_plus_long_TemporalUnit
     */
    public function test_plus_long_TemporalUnit(YearMonth $base, $amount, TemporalUnit $unit, $expectedYearMonth, $expectedEx)
    {
        if ($expectedEx == null) {
            $this->assertEquals($base->plus($amount, $unit), $expectedYearMonth);
        } else {
            try {
                $result = $base->plus($amount, $unit);
                $this->fail();
            } catch (Exception $ex) {
                $this->assertInstanceOf($expectedEx, $ex);
            }
        }
    }

    //-----------------------------------------------------------------------
    // plus(TemporalAmount)
    //-----------------------------------------------------------------------
    function data_plus_TemporalAmount()
    {
        return [
            [YearMonth::of(1, 1), Period::ofYears(1), YearMonth::of(2, 1), null],
            [YearMonth::of(1, 1), Period::ofYears(-12), YearMonth::of(-11, 1), null],
            [YearMonth::of(1, 1), Period::ofYears(0), YearMonth::of(1, 1), null],
            [YearMonth::of(999999999, 12), Period::ofYears(0), YearMonth::of(999999999, 12), null],
            [YearMonth::of(-999999999, 1), Period::ofYears(0), YearMonth::of(-999999999, 1), null],
            [YearMonth::of(0, 1), Period::ofYears(-999999999), YearMonth::of(-999999999, 1), null],
            [YearMonth::of(0, 12), Period::ofYears(999999999), YearMonth::of(999999999, 12), null],

            [YearMonth::of(1, 1), Period::ofMonths(1), YearMonth::of(1, 2), null],
            [YearMonth::of(1, 1), Period::ofMonths(-12), YearMonth::of(0, 1), null],
            [YearMonth::of(1, 1), Period::ofMonths(121), YearMonth::of(11, 2), null],
            [YearMonth::of(1, 1), Period::ofMonths(0), YearMonth::of(1, 1), null],
            [YearMonth::of(999999999, 12), Period::ofMonths(0), YearMonth::of(999999999, 12), null],
            [YearMonth::of(-999999999, 1), Period::ofMonths(0), YearMonth::of(-999999999, 1), null],
            [YearMonth::of(-999999999, 2), Period::ofMonths(-1), YearMonth::of(-999999999, 1), null],
            [YearMonth::of(999999999, 11), Period::ofMonths(1), YearMonth::of(999999999, 12), null],

            [YearMonth::of(1, 1), Period::ofYears(1)->withMonths(2), YearMonth::of(2, 3), null],
            [YearMonth::of(1, 1), Period::ofYears(-12)->withMonths(-1), YearMonth::of(-12, 12), null],

            [YearMonth::of(1, 1), Period::ofMonths(2)->withYears(1), YearMonth::of(2, 3), null],
            [YearMonth::of(1, 1), Period::ofMonths(-1)->withYears(-12), YearMonth::of(-12, 12), null],

            [YearMonth::of(1, 1), Period::ofDays(365), null, DateTimeException::class],
            [YearMonth::of(1, 1), Duration::ofDays(365), null, DateTimeException::class],
            [YearMonth::of(1, 1), Duration::ofHours(365 * 24), null, DateTimeException::class],
            [YearMonth::of(1, 1), Duration::ofMinutes(365 * 24 * 60), null, DateTimeException::class],
            [YearMonth::of(1, 1), Duration::ofSeconds(365 * 24 * 3600), null, DateTimeException::class],
            [YearMonth::of(1, 1), Duration::ofNanos(365 * 24 * 3600 * 1000000000), null, DateTimeException::class],
        ];
    }

    /**
     * @dataProvider data_plus_TemporalAmount
     */

    public function test_plus_TemporalAmount(YearMonth $base, TemporalAmount $temporalAmount, $expectedYearMonth, $expectedEx)
    {
        if ($expectedEx == null) {
            $this->assertEquals($base->plusAmount($temporalAmount), $expectedYearMonth);
        } else {
            try {
                $result = $base->plusAmount($temporalAmount);
                $this->fail();
            } catch (Exception $ex) {
                $this->assertInstanceOf($expectedEx, $ex);
            }
        }
    }

    //-----------------------------------------------------------------------
    // minusYears()
    //-----------------------------------------------------------------------

    public function test_minusYears_long()
    {
        $test = YearMonth::of(2008, 6);
        $this->assertEquals($test->minusYears(1), YearMonth::of(2007, 6));
    }


    public function test_minusYears_long_noChange_equal()
    {
        $test = YearMonth::of(2008, 6);
        $this->assertEquals($test->minusYears(0), $test);
    }


    public function test_minusYears_long_negative()
    {
        $test = YearMonth::of(2008, 6);
        $this->assertEquals($test->minusYears(-1), YearMonth::of(2009, 6));
    }


    public function test_minusYears_long_big()
    {
        $test = YearMonth::of(40, 6);
        $this->assertEquals($test->minusYears(20 + Year::MAX_VALUE), YearMonth::of((int)(40 - 20 - Year::MAX_VALUE), 6));
    }

    /**
     * @expectedException \Celest\DateTimeException
     */

    public function test_minusYears_long_invalidTooLarge()
    {
        $test = YearMonth::of(Year::MAX_VALUE, 6);
        $test->minusYears(-1);
    }

    /**
     * @expectedException \Celest\DateTimeException
     */

    public function test_minusYears_long_invalidTooLargeMaxSubtractMax()
    {
        $test = YearMonth::of(Year::MIN_VALUE, 12);
        $test->minusYears(Long::MAX_VALUE);
    }

    /**
     * @expectedException \Celest\DateTimeException
     */

    public function test_minusYears_long_invalidTooLargeMaxSubtractMin()
    {
        $test = YearMonth::of(Year::MIN_VALUE, 12);
        $test->minusYears(Long::MIN_VALUE);
    }

    /**
     * @expectedException \Celest\DateTimeException
     */

    public function test_minusYears_long_invalidTooSmall()
    {
        $test = YearMonth::of(Year::MIN_VALUE, 6);
        $test->minusYears(1);
    }

    //-----------------------------------------------------------------------
    // minusMonths()
    //-----------------------------------------------------------------------

    public function test_minusMonths_long()
    {
        $test = YearMonth::of(2008, 6);
        $this->assertEquals($test->minusMonths(1), YearMonth::of(2008, 5));
    }


    public function test_minusMonths_long_noChange_equal()
    {
        $test = YearMonth::of(2008, 6);
        $this->assertEquals($test->minusMonths(0), $test);
    }


    public function test_minusMonths_long_overYears()
    {
        $test = YearMonth::of(2008, 6);
        $this->assertEquals($test->minusMonths(6), YearMonth::of(2007, 12));
    }


    public function test_minusMonths_long_negative()
    {
        $test = YearMonth::of(2008, 6);
        $this->assertEquals($test->minusMonths(-1), YearMonth::of(2008, 7));
    }


    public function test_minusMonths_long_negativeOverYear()
    {
        $test = YearMonth::of(2008, 6);
        $this->assertEquals($test->minusMonths(-7), YearMonth::of(2009, 1));
    }


    public function test_minusMonths_long_big()
    {
        $test = YearMonth::of(40, 6);
        $months = 20 + Integer::MAX_VALUE;
        $this->assertEquals($test->minusMonths($months), YearMonth::of((40 - Math::div($months, 12)), 6 - ($months % 12)));
    }

    /**
     * @expectedException \Celest\DateTimeException
     */

    public function test_minusMonths_long_invalidTooLarge()
    {
        $test = YearMonth::of(Year::MAX_VALUE, 12);
        $test->minusMonths(-1);
    }

    /**
     * @expectedException \Celest\DateTimeException
     */

    public function test_minusMonths_long_invalidTooLargeMaxSubtractMax()
    {
        $test = YearMonth::of(Year::MAX_VALUE, 12);
        $test->minusMonths(Long::MAX_VALUE);
    }

    /**
     * @expectedException \Celest\DateTimeException
     */

    public function test_minusMonths_long_invalidTooLargeMaxSubtractMin()
    {
        $test = YearMonth::of(Year::MAX_VALUE, 12);
        $test->minusMonths(Long::MIN_VALUE);
    }

    /**
     * @expectedException \Celest\DateTimeException
     */

    public function test_minusMonths_long_invalidTooSmall()
    {
        $test = YearMonth::of(Year::MIN_VALUE, 1);
        $test->minusMonths(1);
    }

    //-----------------------------------------------------------------------
    // minus(long, TemporalUnit)
    //-----------------------------------------------------------------------
    function data_minus_long_TemporalUnit()
    {
        return [
            [YearMonth::of(1, 10), 1, CU::YEARS(), YearMonth::of(0, 10), null],
            [YearMonth::of(1, 10), 12, CU::YEARS(), YearMonth::of(-11, 10), null],
            [YearMonth::of(1, 10), 0, CU::YEARS(), YearMonth::of(1, 10), null],
            [YearMonth::of(999999999, 12), 0, CU::YEARS(), YearMonth::of(999999999, 12), null],
            [YearMonth::of(-999999999, 1), 0, CU::YEARS(), YearMonth::of(-999999999, 1), null],
            [YearMonth::of(0, 1), 999999999, CU::YEARS(), YearMonth::of(-999999999, 1), null],
            [YearMonth::of(0, 12), -999999999, CU::YEARS(), YearMonth::of(999999999, 12), null],

            [YearMonth::of(1, 10), 1, CU::MONTHS(), YearMonth::of(1, 9), null],
            [YearMonth::of(1, 10), 12, CU::MONTHS(), YearMonth::of(0, 10), null],
            [YearMonth::of(1, 10), 0, CU::MONTHS(), YearMonth::of(1, 10), null],
            [YearMonth::of(999999999, 12), 0, CU::MONTHS(), YearMonth::of(999999999, 12), null],
            [YearMonth::of(-999999999, 1), 0, CU::MONTHS(), YearMonth::of(-999999999, 1), null],
            [YearMonth::of(-999999999, 2), 1, CU::MONTHS(), YearMonth::of(-999999999, 1), null],
            [YearMonth::of(999999999, 11), -1, CU::MONTHS(), YearMonth::of(999999999, 12), null],

            [YearMonth::of(1, 10), 1, CU::ERAS(), YearMonth::of(0, 10), null],
            [YearMonth::of(5, 10), 1, CU::CENTURIES(), YearMonth::of(-95, 10), null],
            [YearMonth::of(5, 10), 1, CU::DECADES(), YearMonth::of(-5, 10), null],

            [YearMonth::of(999999999, 12), -1, CU::MONTHS(), null, DateTimeException::class],
            [YearMonth::of(-999999999, 1), 1, CU::MONTHS(), null, DateTimeException::class],

            [YearMonth::of(1, 1), 0, CU::DAYS(), null, DateTimeException::class],
            [YearMonth::of(1, 1), 0, CU::WEEKS(), null, DateTimeException::class],
        ];
    }

    /**
     * @dataProvider data_minus_long_TemporalUnit
     */
    public function test_minus_long_TemporalUnit(YearMonth $base, $amount, TemporalUnit $unit, $expectedYearMonth, $expectedEx)
    {
        if ($expectedEx == null) {
            $this->assertEquals($base->minus($amount, $unit), $expectedYearMonth);
        } else {
            try {
                $result = $base->minus($amount, $unit);
                $this->fail();
            } catch (Exception $ex) {
                $this->assertInstanceOf($expectedEx, $ex);
            }
        }
    }

    //-----------------------------------------------------------------------
    // minus(TemporalAmount)
    //-----------------------------------------------------------------------
    function data_minus_TemporalAmount()
    {
        return [
            [YearMonth::of(1, 1), Period::ofYears(1), YearMonth::of(0, 1), null],
            [YearMonth::of(1, 1), Period::ofYears(-12), YearMonth::of(13, 1), null],
            [YearMonth::of(1, 1), Period::ofYears(0), YearMonth::of(1, 1), null],
            [YearMonth::of(999999999, 12), Period::ofYears(0), YearMonth::of(999999999, 12), null],
            [YearMonth::of(-999999999, 1), Period::ofYears(0), YearMonth::of(-999999999, 1), null],
            [YearMonth::of(0, 1), Period::ofYears(999999999), YearMonth::of(-999999999, 1), null],
            [YearMonth::of(0, 12), Period::ofYears(-999999999), YearMonth::of(999999999, 12), null],

            [YearMonth::of(1, 1), Period::ofMonths(1), YearMonth::of(0, 12), null],
            [YearMonth::of(1, 1), Period::ofMonths(-12), YearMonth::of(2, 1), null],
            [YearMonth::of(1, 1), Period::ofMonths(121), YearMonth::of(-10, 12), null],
            [YearMonth::of(1, 1), Period::ofMonths(0), YearMonth::of(1, 1), null],
            [YearMonth::of(999999999, 12), Period::ofMonths(0), YearMonth::of(999999999, 12), null],
            [YearMonth::of(-999999999, 1), Period::ofMonths(0), YearMonth::of(-999999999, 1), null],
            [YearMonth::of(-999999999, 2), Period::ofMonths(1), YearMonth::of(-999999999, 1), null],
            [YearMonth::of(999999999, 11), Period::ofMonths(-1), YearMonth::of(999999999, 12), null],

            [YearMonth::of(1, 1), Period::ofYears(1)->withMonths(2), YearMonth::of(-1, 11), null],
            [YearMonth::of(1, 1), Period::ofYears(-12)->withMonths(-1), YearMonth::of(13, 2), null],

            [YearMonth::of(1, 1), Period::ofMonths(2)->withYears(1), YearMonth::of(-1, 11), null],
            [YearMonth::of(1, 1), Period::ofMonths(-1)->withYears(-12), YearMonth::of(13, 2), null],

            [YearMonth::of(1, 1), Period::ofDays(365), null, DateTimeException::class],
            [YearMonth::of(1, 1), Duration::ofDays(365), null, DateTimeException::class],
            [YearMonth::of(1, 1), Duration::ofHours(365 * 24), null, DateTimeException::class],
            [YearMonth::of(1, 1), Duration::ofMinutes(365 * 24 * 60), null, DateTimeException::class],
            [YearMonth::of(1, 1), Duration::ofSeconds(365 * 24 * 3600), null, DateTimeException::class],
            [YearMonth::of(1, 1), Duration::ofNanos(365 * 24 * 3600 * 1000000000), null, DateTimeException::class],
        ];
    }

    /**
     * @dataProvider data_minus_TemporalAmount
     */
    public function test_minus_TemporalAmount(YearMonth $base, TemporalAmount $temporalAmount, $expectedYearMonth, $expectedEx)
    {
        if ($expectedEx === null) {
            $this->assertEquals($base->minusAmount($temporalAmount), $expectedYearMonth);
        } else {
            try {
                $result = $base->minusAmount($temporalAmount);
                $this->fail();
            } catch (Exception $ex) {
                $this->assertInstanceOf($expectedEx, $ex);
            }
        }
    }

    //-----------------------------------------------------------------------
    // adjustInto()
    //-----------------------------------------------------------------------

    public function test_adjustDate()
    {
        $test = YearMonth::of(2008, 6);
        $date = LocalDate::of(2007, 1, 1);
        $this->assertEquals($test->adjustInto($date), LocalDate::of(2008, 6, 1));
    }


    public function test_adjustDate_preserveDoM()
    {
        $test = YearMonth::of(2011, 3);
        $date = LocalDate::of(2008, 2, 29);
        $this->assertEquals($test->adjustInto($date), LocalDate::of(2011, 3, 29));
    }


    public function test_adjustDate_resolve()
    {
        $test = YearMonth::of(2007, 2);
        $date = LocalDate::of(2008, 3, 31);
        $this->assertEquals($test->adjustInto($date), LocalDate::of(2007, 2, 28));
    }


    public function test_adjustDate_equal()
    {
        $test = YearMonth::of(2008, 6);
        $date = LocalDate::of(2008, 6, 30);
        $this->assertEquals($test->adjustInto($date), $date);
    }

    public function test_adjustDate_null()
    {
        TestHelper::assertNullException($this, function () {
            self::TEST_2008_06()->adjustInto(null);
        });
    }

    //-----------------------------------------------------------------------
    // isLeapYear()
    //-----------------------------------------------------------------------

    public function test_isLeapYear()
    {
        $this->assertEquals(YearMonth::of(2007, 6)->isLeapYear(), false);
        $this->assertEquals(YearMonth::of(2008, 6)->isLeapYear(), true);
    }

    //-----------------------------------------------------------------------
    // lengthOfMonth()
    //-----------------------------------------------------------------------

    public function test_lengthOfMonth_june()
    {
        $test = YearMonth::of(2007, 6);
        $this->assertEquals($test->lengthOfMonth(), 30);
    }


    public function test_lengthOfMonth_febNonLeap()
    {
        $test = YearMonth::of(2007, 2);
        $this->assertEquals($test->lengthOfMonth(), 28);
    }


    public function test_lengthOfMonth_febLeap()
    {
        $test = YearMonth::of(2008, 2);
        $this->assertEquals($test->lengthOfMonth(), 29);
    }

    //-----------------------------------------------------------------------
    // lengthOfYear()
    //-----------------------------------------------------------------------

    public function test_lengthOfYear()
    {
        $this->assertEquals(YearMonth::of(2007, 6)->lengthOfYear(), 365);
        $this->assertEquals(YearMonth::of(2008, 6)->lengthOfYear(), 366);
    }

    //-----------------------------------------------------------------------
    // isValidDay(int)
    //-----------------------------------------------------------------------

    public function test_isValidDay_int_june()
    {
        $test = YearMonth::of(2007, 6);
        $this->assertEquals($test->isValidDay(1), true);
        $this->assertEquals($test->isValidDay(30), true);

        $this->assertEquals($test->isValidDay(-1), false);
        $this->assertEquals($test->isValidDay(0), false);
        $this->assertEquals($test->isValidDay(31), false);
        $this->assertEquals($test->isValidDay(32), false);
    }


    public function test_isValidDay_int_febNonLeap()
    {
        $test = YearMonth::of(2007, 2);
        $this->assertEquals($test->isValidDay(1), true);
        $this->assertEquals($test->isValidDay(28), true);

        $this->assertEquals($test->isValidDay(-1), false);
        $this->assertEquals($test->isValidDay(0), false);
        $this->assertEquals($test->isValidDay(29), false);
        $this->assertEquals($test->isValidDay(32), false);
    }


    public function test_isValidDay_int_febLeap()
    {
        $test = YearMonth::of(2008, 2);
        $this->assertEquals($test->isValidDay(1), true);
        $this->assertEquals($test->isValidDay(29), true);

        $this->assertEquals($test->isValidDay(-1), false);
        $this->assertEquals($test->isValidDay(0), false);
        $this->assertEquals($test->isValidDay(30), false);
        $this->assertEquals($test->isValidDay(32), false);
    }

    //-----------------------------------------------------------------------
    // until(Temporal, TemporalUnit)
    function data_periodUntilUnit()
    {
        return [
            [$this->ym(2000, 1), $this->ym(-1, 12), CU::MONTHS(), -2000 * 12 - 1],
            [$this->ym(2000, 1), $this->ym(0, 1), CU::MONTHS(), -2000 * 12],
            [$this->ym(2000, 1), $this->ym(0, 12), CU::MONTHS(), -1999 * 12 - 1],
            [$this->ym(2000, 1), $this->ym(1, 1), CU::MONTHS(), -1999 * 12],
            [$this->ym(2000, 1), $this->ym(1999, 12), CU::MONTHS(), -1],
            [$this->ym(2000, 1), $this->ym(2000, 1), CU::MONTHS(), 0],
            [$this->ym(2000, 1), $this->ym(2000, 2), CU::MONTHS(), 1],
            [$this->ym(2000, 1), $this->ym(2000, 3), CU::MONTHS(), 2],
            [$this->ym(2000, 1), $this->ym(2000, 12), CU::MONTHS(), 11],
            [$this->ym(2000, 1), $this->ym(2001, 1), CU::MONTHS(), 12],
            [$this->ym(2000, 1), $this->ym(2246, 5), CU::MONTHS(), 246 * 12 + 4],

            [$this->ym(2000, 1), $this->ym(-1, 12), CU::YEARS(), -2000],
            [$this->ym(2000, 1), $this->ym(0, 1), CU::YEARS(), -2000],
            [$this->ym(2000, 1), $this->ym(0, 12), CU::YEARS(), -1999],
            [$this->ym(2000, 1), $this->ym(1, 1), CU::YEARS(), -1999],
            [$this->ym(2000, 1), $this->ym(1998, 12), CU::YEARS(), -1],
            [$this->ym(2000, 1), $this->ym(1999, 1), CU::YEARS(), -1],
            [$this->ym(2000, 1), $this->ym(1999, 2), CU::YEARS(), 0],
            [$this->ym(2000, 1), $this->ym(1999, 12), CU::YEARS(), 0],
            [$this->ym(2000, 1), $this->ym(2000, 1), CU::YEARS(), 0],
            [$this->ym(2000, 1), $this->ym(2000, 2), CU::YEARS(), 0],
            [$this->ym(2000, 1), $this->ym(2000, 12), CU::YEARS(), 0],
            [$this->ym(2000, 1), $this->ym(2001, 1), CU::YEARS(), 1],
            [$this->ym(2000, 1), $this->ym(2246, 5), CU::YEARS(), 246],

            [$this->ym(2000, 5), $this->ym(-1, 5), CU::DECADES(), -200],
            [$this->ym(2000, 5), $this->ym(0, 4), CU::DECADES(), -200],
            [$this->ym(2000, 5), $this->ym(0, 5), CU::DECADES(), -200],
            [$this->ym(2000, 5), $this->ym(0, 6), CU::DECADES(), -199],
            [$this->ym(2000, 5), $this->ym(1, 5), CU::DECADES(), -199],
            [$this->ym(2000, 5), $this->ym(1990, 4), CU::DECADES(), -1],
            [$this->ym(2000, 5), $this->ym(1990, 5), CU::DECADES(), -1],
            [$this->ym(2000, 5), $this->ym(1990, 6), CU::DECADES(), 0],
            [$this->ym(2000, 5), $this->ym(2000, 4), CU::DECADES(), 0],
            [$this->ym(2000, 5), $this->ym(2000, 5), CU::DECADES(), 0],
            [$this->ym(2000, 5), $this->ym(2000, 6), CU::DECADES(), 0],
            [$this->ym(2000, 5), $this->ym(2010, 4), CU::DECADES(), 0],
            [$this->ym(2000, 5), $this->ym(2010, 5), CU::DECADES(), 1],
            [$this->ym(2000, 5), $this->ym(2010, 6), CU::DECADES(), 1],

            [$this->ym(2000, 5), $this->ym(-1, 5), CU::CENTURIES(), -20],
            [$this->ym(2000, 5), $this->ym(0, 4), CU::CENTURIES(), -20],
            [$this->ym(2000, 5), $this->ym(0, 5), CU::CENTURIES(), -20],
            [$this->ym(2000, 5), $this->ym(0, 6), CU::CENTURIES(), -19],
            [$this->ym(2000, 5), $this->ym(1, 5), CU::CENTURIES(), -19],
            [$this->ym(2000, 5), $this->ym(1900, 4), CU::CENTURIES(), -1],
            [$this->ym(2000, 5), $this->ym(1900, 5), CU::CENTURIES(), -1],
            [$this->ym(2000, 5), $this->ym(1900, 6), CU::CENTURIES(), 0],
            [$this->ym(2000, 5), $this->ym(2000, 4), CU::CENTURIES(), 0],
            [$this->ym(2000, 5), $this->ym(2000, 5), CU::CENTURIES(), 0],
            [$this->ym(2000, 5), $this->ym(2000, 6), CU::CENTURIES(), 0],
            [$this->ym(2000, 5), $this->ym(2100, 4), CU::CENTURIES(), 0],
            [$this->ym(2000, 5), $this->ym(2100, 5), CU::CENTURIES(), 1],
            [$this->ym(2000, 5), $this->ym(2100, 6), CU::CENTURIES(), 1],

            [$this->ym(2000, 5), $this->ym(-1, 5), CU::MILLENNIA(), -2],
            [$this->ym(2000, 5), $this->ym(0, 4), CU::MILLENNIA(), -2],
            [$this->ym(2000, 5), $this->ym(0, 5), CU::MILLENNIA(), -2],
            [$this->ym(2000, 5), $this->ym(0, 6), CU::MILLENNIA(), -1],
            [$this->ym(2000, 5), $this->ym(1, 5), CU::MILLENNIA(), -1],
            [$this->ym(2000, 5), $this->ym(1000, 4), CU::MILLENNIA(), -1],
            [$this->ym(2000, 5), $this->ym(1000, 5), CU::MILLENNIA(), -1],
            [$this->ym(2000, 5), $this->ym(1000, 6), CU::MILLENNIA(), 0],
            [$this->ym(2000, 5), $this->ym(2000, 4), CU::MILLENNIA(), 0],
            [$this->ym(2000, 5), $this->ym(2000, 5), CU::MILLENNIA(), 0],
            [$this->ym(2000, 5), $this->ym(2000, 6), CU::MILLENNIA(), 0],
            [$this->ym(2000, 5), $this->ym(3000, 4), CU::MILLENNIA(), 0],
            [$this->ym(2000, 5), $this->ym(3000, 5), CU::MILLENNIA(), 1],
            [$this->ym(2000, 5), $this->ym(3000, 5), CU::MILLENNIA(), 1],
        ];
    }

    /**
     * @dataProvider data_periodUntilUnit
     */
    public function test_until_TemporalUnit(YearMonth $ym1, YearMonth $ym2, TemporalUnit $unit, $expected)
    {
        $amount = $ym1->until($ym2, $unit);
        $this->assertEquals($amount, $expected);
    }

    /**
     * @dataProvider data_periodUntilUnit
     */

    public function test_until_TemporalUnit_negated(YearMonth $ym1, YearMonth $ym2, TemporalUnit $unit, $expected)
    {
        $amount = $ym2->until($ym1, $unit);
        $this->assertEquals($amount, -$expected);
    }

    /**
     * @dataProvider data_periodUntilUnit
     */

    public function test_until_TemporalUnit_between(YearMonth $ym1, YearMonth $ym2, TemporalUnit $unit, $expected)
    {
        $amount = $unit->between($ym1, $ym2);
        $this->assertEquals($amount, $expected);
    }


    public function test_until_convertedType()
    {
        $start = YearMonth::of(2010, 6);
        $end = $start->plusMonths(2)->atDay(12);
        $this->assertEquals($start->until($end, CU::MONTHS()), 2);
    }

    /**
     * @expectedException \Celest\DateTimeException
     */

    public function test_until_invalidType()
    {
        $start = YearMonth::of(2010, 6);
        $start->until(LocalTime::of(11, 30), CU::MONTHS());
    }

    /**
     * @expectedException \Celest\Temporal\UnsupportedTemporalTypeException
     */
    public function test_until_TemporalUnit_unsupportedUnit()
    {
        self::TEST_2008_06()->until(self::TEST_2008_06(), CU::HOURS());
    }

    public function test_until_TemporalUnit_nullEnd()
    {
        TestHelper::assertNullException($this, function () {
            self::TEST_2008_06()->until(null, CU::DAYS());
        });
    }

    public function test_until_TemporalUnit_nullUnit()
    {
        TestHelper::assertNullException($this, function () {
            self::TEST_2008_06()->until(self::TEST_2008_06(), null);
        });
    }

    //-----------------------------------------------------------------------
    // format(DateTimeFormatter)
    //-----------------------------------------------------------------------

    public function test_format_formatter()
    {
        $f = DateTimeFormatter::ofPattern("y M");
        $t = YearMonth::of(2010, 12)->format($f);
        $this->assertEquals($t, "2010 12");
    }

    public function test_format_formatter_null()
    {
        TestHelper::assertNullException($this, function () {
            YearMonth::of(2010, 12)->format(null);
        });
    }

    //-----------------------------------------------------------------------
    // atDay(int)
    //-----------------------------------------------------------------------
    function data_atDay()
    {
        return [
            [YearMonth::of(2008, 6), 8, LocalDate::of(2008, 6, 8)],

            [YearMonth::of(2008, 1), 31, LocalDate::of(2008, 1, 31)],
            [YearMonth::of(2008, 2), 29, LocalDate::of(2008, 2, 29)],
            [YearMonth::of(2008, 3), 31, LocalDate::of(2008, 3, 31)],
            [YearMonth::of(2008, 4), 30, LocalDate::of(2008, 4, 30)],

            [YearMonth::of(2009, 1), 32, null],
            [YearMonth::of(2009, 1), 0, null],
            [YearMonth::of(2009, 2), 29, null],
            [YearMonth::of(2009, 2), 30, null],
            [YearMonth::of(2009, 2), 31, null],
            [YearMonth::of(2009, 4), 31, null],
        ];
    }

    /**
     * @dataProvider data_atDay
     */
    public function test_atDay(YearMonth $test, $day, $expected)
    {
        if ($expected !== null) {
            $this->assertEquals($test->atDay($day), $expected);
        } else {
            try {
                $test->atDay($day);
                $this->fail();
            } catch (DateTimeException $ex) {
                // $expected
            }
        }
    }

    //-----------------------------------------------------------------------
    // atEndOfMonth()
    //-----------------------------------------------------------------------
    function data_atEndOfMonth()
    {
        return [
            [YearMonth::of(2008, 1), LocalDate::of(2008, 1, 31)],
            [YearMonth::of(2008, 2), LocalDate::of(2008, 2, 29)],
            [YearMonth::of(2008, 3), LocalDate::of(2008, 3, 31)],
            [YearMonth::of(2008, 4), LocalDate::of(2008, 4, 30)],
            [YearMonth::of(2008, 5), LocalDate::of(2008, 5, 31)],
            [YearMonth::of(2008, 6), LocalDate::of(2008, 6, 30)],
            [YearMonth::of(2008, 12), LocalDate::of(2008, 12, 31)],

            [YearMonth::of(2009, 1), LocalDate::of(2009, 1, 31)],
            [YearMonth::of(2009, 2), LocalDate::of(2009, 2, 28)],
            [YearMonth::of(2009, 3), LocalDate::of(2009, 3, 31)],
            [YearMonth::of(2009, 4), LocalDate::of(2009, 4, 30)],
            [YearMonth::of(2009, 5), LocalDate::of(2009, 5, 31)],
            [YearMonth::of(2009, 6), LocalDate::of(2009, 6, 30)],
            [YearMonth::of(2009, 12), LocalDate::of(2009, 12, 31)],
        ];
    }

    /**
     * @dataProvider data_atEndOfMonth
     */

    public function test_atEndOfMonth(YearMonth $test, LocalDate $expected)
    {
        $this->assertEquals($test->atEndOfMonth(), $expected);
    }

    //-----------------------------------------------------------------------
    // compareTo()
    //-----------------------------------------------------------------------

    public function test_comparisons()
    {
        $this->doTest_comparisons_YearMonth([
                YearMonth::of(-1, 1),
                YearMonth::of(0, 1),
                YearMonth::of(0, 12),
                YearMonth::of(1, 1),
                YearMonth::of(1, 2),
                YearMonth::of(1, 12),
                YearMonth::of(2008, 1),
                YearMonth::of(2008, 6),
                YearMonth::of(2008, 12)
            ]
        );
    }

    function doTest_comparisons_YearMonth(array $localDates)
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
            self::TEST_2008_06()->compareTo(null);
        });
    }

    public function test_isBefore_ObjectNull()
    {
        TestHelper::assertNullException($this, function () {
            self::TEST_2008_06()->isBefore(null);
        });
    }

    public function test_isAfter_ObjectNull()
    {
        TestHelper::assertNullException($this, function () {
            self::TEST_2008_06()->isAfter(null);
        });
    }

    //-----------------------------------------------------------------------
    // equals()
    //-----------------------------------------------------------------------

    public function test_equals()
    {
        $a = YearMonth::of(2008, 6);
        $b = YearMonth::of(2008, 6);
        $c = YearMonth::of(2007, 6);
        $d = YearMonth::of(2008, 5);

        $this->assertEquals($a->equals($a), true);
        $this->assertEquals($a->equals($b), true);
        $this->assertEquals($a->equals($c), false);
        $this->assertEquals($a->equals($d), false);

        $this->assertEquals($b->equals($a), true);
        $this->assertEquals($b->equals($b), true);
        $this->assertEquals($b->equals($c), false);
        $this->assertEquals($b->equals($d), false);

        $this->assertEquals($c->equals($a), false);
        $this->assertEquals($c->equals($b), false);
        $this->assertEquals($c->equals($c), true);
        $this->assertEquals($c->equals($d), false);

        $this->assertEquals($d->equals($a), false);
        $this->assertEquals($d->equals($b), false);
        $this->assertEquals($d->equals($c), false);
        $this->assertEquals($d->equals($d), true);
    }


    public function test_equals_itself_true()
    {
        $this->assertEquals(self::TEST_2008_06()->equals(self::TEST_2008_06()), true);
    }


    public function test_equals_string_false()
    {
        $this->assertEquals(self::TEST_2008_06()->equals("2007-07-15"), false);
    }


    public function test_equals_null_false()
    {
        $this->assertEquals(self::TEST_2008_06()->equals(null), false);
    }

    //-----------------------------------------------------------------------
    // toString()
    //-----------------------------------------------------------------------
    function provider_sampleToString()
    {
        return [
            [2008, 1, "2008-01"],
            [2008, 12, "2008-12"],
            [7, 5, "0007-05"],
            [0, 5, "0000-05"],
            [-1, 1, "-0001-01"],
        ];
    }

    /**
     * @dataProvider provider_sampleToString
     */
    public function test_toString($y, $m, $expected)
    {
        $test = YearMonth::of($y, $m);
        $str = $test->__toString();
        $this->assertEquals($str, $expected);
    }

    private function ym($year, $month)
    {
        return YearMonth::of($year, $month);
    }

}
