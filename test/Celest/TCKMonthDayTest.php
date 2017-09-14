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
use Celest\Temporal\ChronoField as CF;
use Celest\Temporal\JulianFields;
use Celest\Temporal\TemporalAccessor;
use Celest\Temporal\TemporalQueries;
use Celest\Temporal\TemporalQuery;

class TCKMonthDayTest extends AbstractDateTimeTest
{

    public static function TEST_07_15()
    {
        return MonthDay::of(7, 15);
    }

    //-----------------------------------------------------------------------
    protected function samples()
    {
        return [self::TEST_07_15()];
    }

    protected function validFields()
    {
        return [
            CF::DAY_OF_MONTH(),
            CF::MONTH_OF_YEAR(),
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
    function check(MonthDay $test, $m, $d)
    {
        $this->assertEquals($test->getMonth()->getValue(), $m);
        $this->assertEquals($test->getDayOfMonth(), $d);
    }

    //-----------------------------------------------------------------------
    // now()
    //-----------------------------------------------------------------------
    public function test_now()
    {
        $expected = MonthDay::nowOf(Clock::systemDefaultZone());
        $test = MonthDay::now();
        for ($i = 0; $i < 100; $i++) {
            if ($expected->equals($test)) {
                $this->assertTrue(true);
                return;
            }
            $expected = MonthDay::nowOf(Clock::systemDefaultZone());
            $test = MonthDay::now();
        }
        $this->assertEquals($test, $expected);
    }

    //-----------------------------------------------------------------------
    // now(ZoneId)
    //-----------------------------------------------------------------------
    public function test_now_ZoneId_nullZoneId()
    {
        TestHelper::assertNullException($this, function () {
            MonthDay::nowIn(null);
        });
    }


    public function test_now_ZoneId()
    {
        $zone = ZoneId::of("UTC+01:02:03");
        $expected = MonthDay::nowOf(Clock::system($zone));
        $test = MonthDay::nowIn($zone);
        for ($i = 0; $i < 100; $i++) {
            if ($expected->equals($test)) {
                $this->assertTrue(true);
                return;
            }
            $expected = MonthDay::nowOf(Clock::system($zone));
            $test = MonthDay::nowIn($zone);
        }
        $this->assertEquals($test, $expected);
    }

    //-----------------------------------------------------------------------
    // now(Clock::)
    //-----------------------------------------------------------------------

    public function test_now_Clock()
    {
        $instant = LocalDateTime::of(2010, 12, 31, 0, 0)->toInstant(ZoneOffset::UTC());
        $clock = Clock::fixed($instant, ZoneOffset::UTC());
        $test = MonthDay::nowOf($clock);
        $this->assertEquals($test->getMonth(), Month::DECEMBER());
        $this->assertEquals($test->getDayOfMonth(), 31);
    }

    public function test_now_Clock_nullClock()
    {
        TestHelper::assertNullException($this, function () {
            MonthDay::nowOf(null);
        });
    }

    //-----------------------------------------------------------------------

    public function test_factory_intMonth()
    {
        $this->assertEquals(self::TEST_07_15(), MonthDay::ofMonth(Month::JULY(), 15));
    }

    /**
     * @expectedException \Celest\DateTimeException
     */
    public function test_factory_intMonth_dayTooLow()
    {
        MonthDay::ofMonth(Month::JANUARY(), 0);
    }

    /**
     * @expectedException \Celest\DateTimeException
     */

    public function test_factory_intMonth_dayTooHigh()
    {
        MonthDay::ofMonth(Month::JANUARY(), 32);
    }

    public function test_factory_intMonth_nullMonth()
    {
        TestHelper::assertNullException($this, function () {
            MonthDay::ofMonth(null, 15);
        });
    }

    //-----------------------------------------------------------------------

    public function factory_ints()
    {
        $this->check(self::TEST_07_15(), 7, 15);
    }

    /**
     * @expectedException \Celest\DateTimeException
     */

    public function test_factory_ints_dayTooLow()
    {
        MonthDay::of(1, 0);
    }

    /**
     * @expectedException \Celest\DateTimeException
     */

    public function test_factory_ints_dayTooHigh()
    {
        MonthDay::of(1, 32);
    }


    /**
     * @expectedException \Celest\DateTimeException
     */

    public function test_factory_ints_monthTooLow()
    {
        MonthDay::of(0, 1);
    }

    /**
     * @expectedException \Celest\DateTimeException
     */

    public function test_factory_ints_monthTooHigh()
    {
        MonthDay::of(13, 1);
    }

    //-----------------------------------------------------------------------

    public function test_factory_CalendricalObject()
    {
        $this->assertEquals(MonthDay::from(LocalDate::of(2007, 7, 15)), self::TEST_07_15());
    }

    /**
     * @expectedException \Celest\DateTimeException
     */

    public function test_factory_CalendricalObject_invalid_noDerive()
    {
        MonthDay::from(LocalTime::of(12, 30));
    }

    public function test_factory_CalendricalObject_null()
    {
        TestHelper::assertNullException($this, function () {
            MonthDay::from(null);
        });
    }

    //-----------------------------------------------------------------------
    // parse()
    //-----------------------------------------------------------------------
    function provider_goodParseData()
    {
        return [
            ["--01-01", MonthDay::of(1, 1)],
            ["--01-31", MonthDay::of(1, 31)],
            ["--02-01", MonthDay::of(2, 1)],
            ["--02-29", MonthDay::of(2, 29)],
            ["--03-01", MonthDay::of(3, 1)],
            ["--03-31", MonthDay::of(3, 31)],
            ["--04-01", MonthDay::of(4, 1)],
            ["--04-30", MonthDay::of(4, 30)],
            ["--05-01", MonthDay::of(5, 1)],
            ["--05-31", MonthDay::of(5, 31)],
            ["--06-01", MonthDay::of(6, 1)],
            ["--06-30", MonthDay::of(6, 30)],
            ["--07-01", MonthDay::of(7, 1)],
            ["--07-31", MonthDay::of(7, 31)],
            ["--08-01", MonthDay::of(8, 1)],
            ["--08-31", MonthDay::of(8, 31)],
            ["--09-01", MonthDay::of(9, 1)],
            ["--09-30", MonthDay::of(9, 30)],
            ["--10-01", MonthDay::of(10, 1)],
            ["--10-31", MonthDay::of(10, 31)],
            ["--11-01", MonthDay::of(11, 1)],
            ["--11-30", MonthDay::of(11, 30)],
            ["--12-01", MonthDay::of(12, 1)],
            ["--12-31", MonthDay::of(12, 31)],
        ];
    }

    /**
     * @dataProvider provider_goodParseData
     */

    public function test_factory_parse_success($text, $expected)
    {
        $monthDay = MonthDay::parse($text);
        $this->assertEquals($monthDay, $expected);
    }

    //-----------------------------------------------------------------------
    function provider_badParseData()
    {
        return [
            ["", 0],
            ["-00", 0],
            ["--FEB-23", 2],
            ["--01-0", 5],
            ["--01-3A", 5],
        ];
    }

    /**
     * @dataProvider provider_badParseData
     * @expectedException \Celest\DateTimeParseException
     */

    public function test_factory_parse_fail($text, $pos)
    {
        try {
            MonthDay::parse($text);
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

    public function test_factory_parse_illegalValue_Day()
    {
        MonthDay::parse("--06-32");
    }

    /**
     * @expectedException \Celest\DateTimeParseException
     */

    public function test_factory_parse_invalidValue_Day()
    {
        MonthDay::parse("--06-31");
    }

    /**
     * @expectedException \Celest\DateTimeParseException
     */

    public function test_factory_parse_illegalValue_Month()
    {
        MonthDay::parse("--13-25");
    }

    public function test_factory_parse_nullText()
    {
        TestHelper::assertNullException($this, function () {
            MonthDay::parse(null);
        });
    }

    //-----------------------------------------------------------------------
    // parse(DateTimeFormatter)
    //-----------------------------------------------------------------------

    public function test_factory_parse_formatter()
    {
        $f = DateTimeFormatter::ofPattern("M d");
        $test = MonthDay::parseWith("12 3", $f);
        $this->assertEquals($test, MonthDay::of(12, 3));
    }

    public function test_factory_parse_formatter_nullText()
    {
        TestHelper::assertNullException($this, function () {
            $f = DateTimeFormatter::ofPattern("M d");
            MonthDay::parseWith(null, $f);
        });
    }

    public function test_factory_parse_formatter_nullFormatter()
    {
        TestHelper::assertNullException($this, function () {
            MonthDay::parseWith("ANY", null);
        });
    }

    //-----------------------------------------------------------------------
    // isSupported(TemporalField)
    //-----------------------------------------------------------------------

    public function test_isSupported_TemporalField()
    {
        // TODO $this->assertEquals(self::TEST_07_15()->isSupported(null), false);
        $this->assertEquals(self::TEST_07_15()->isSupported(CF::NANO_OF_SECOND()), false);
        $this->assertEquals(self::TEST_07_15()->isSupported(CF::NANO_OF_DAY()), false);
        $this->assertEquals(self::TEST_07_15()->isSupported(CF::MICRO_OF_SECOND()), false);
        $this->assertEquals(self::TEST_07_15()->isSupported(CF::MICRO_OF_DAY()), false);
        $this->assertEquals(self::TEST_07_15()->isSupported(CF::MILLI_OF_SECOND()), false);
        $this->assertEquals(self::TEST_07_15()->isSupported(CF::MILLI_OF_DAY()), false);
        $this->assertEquals(self::TEST_07_15()->isSupported(CF::SECOND_OF_MINUTE()), false);
        $this->assertEquals(self::TEST_07_15()->isSupported(CF::SECOND_OF_DAY()), false);
        $this->assertEquals(self::TEST_07_15()->isSupported(CF::MINUTE_OF_HOUR()), false);
        $this->assertEquals(self::TEST_07_15()->isSupported(CF::MINUTE_OF_DAY()), false);
        $this->assertEquals(self::TEST_07_15()->isSupported(CF::HOUR_OF_AMPM()), false);
        $this->assertEquals(self::TEST_07_15()->isSupported(CF::CLOCK_HOUR_OF_AMPM()), false);
        $this->assertEquals(self::TEST_07_15()->isSupported(CF::HOUR_OF_DAY()), false);
        $this->assertEquals(self::TEST_07_15()->isSupported(CF::CLOCK_HOUR_OF_DAY()), false);
        $this->assertEquals(self::TEST_07_15()->isSupported(CF::AMPM_OF_DAY()), false);
        $this->assertEquals(self::TEST_07_15()->isSupported(CF::DAY_OF_WEEK()), false);
        $this->assertEquals(self::TEST_07_15()->isSupported(CF::ALIGNED_DAY_OF_WEEK_IN_MONTH()), false);
        $this->assertEquals(self::TEST_07_15()->isSupported(CF::ALIGNED_DAY_OF_WEEK_IN_YEAR()), false);
        $this->assertEquals(self::TEST_07_15()->isSupported(CF::DAY_OF_MONTH()), true);
        $this->assertEquals(self::TEST_07_15()->isSupported(CF::DAY_OF_YEAR()), false);
        $this->assertEquals(self::TEST_07_15()->isSupported(CF::EPOCH_DAY()), false);
        $this->assertEquals(self::TEST_07_15()->isSupported(CF::ALIGNED_WEEK_OF_MONTH()), false);
        $this->assertEquals(self::TEST_07_15()->isSupported(CF::ALIGNED_WEEK_OF_YEAR()), false);
        $this->assertEquals(self::TEST_07_15()->isSupported(CF::MONTH_OF_YEAR()), true);
        $this->assertEquals(self::TEST_07_15()->isSupported(CF::PROLEPTIC_MONTH()), false);
        $this->assertEquals(self::TEST_07_15()->isSupported(CF::YEAR()), false);
        $this->assertEquals(self::TEST_07_15()->isSupported(CF::YEAR_OF_ERA()), false);
        $this->assertEquals(self::TEST_07_15()->isSupported(CF::ERA()), false);
        $this->assertEquals(self::TEST_07_15()->isSupported(CF::INSTANT_SECONDS()), false);
        $this->assertEquals(self::TEST_07_15()->isSupported(CF::OFFSET_SECONDS()), false);
    }

    //-----------------------------------------------------------------------
    // get(TemporalField)
    //-----------------------------------------------------------------------

    public function test_get_TemporalField()
    {
        $this->assertEquals(self::TEST_07_15()->get(CF::DAY_OF_MONTH()), 15);
        $this->assertEquals(self::TEST_07_15()->get(CF::MONTH_OF_YEAR()), 7);
    }


    public function test_getLong_TemporalField()
    {
        $this->assertEquals(self::TEST_07_15()->getLong(CF::DAY_OF_MONTH()), 15);
        $this->assertEquals(self::TEST_07_15()->getLong(CF::MONTH_OF_YEAR()), 7);
    }

    //-----------------------------------------------------------------------
    // query(TemporalQuery)
    //-----------------------------------------------------------------------
    function data_query()
    {
        return [
            [self::TEST_07_15(), TemporalQueries::chronology(), IsoChronology::INSTANCE()],
            [self::TEST_07_15(), TemporalQueries::zoneId(), null],
            [self::TEST_07_15(), TemporalQueries::precision(), null],
            [self::TEST_07_15(), TemporalQueries::zone(), null],
            [self::TEST_07_15(), TemporalQueries::offset(), null],
            [self::TEST_07_15(), TemporalQueries::localDate(), null],
            [self::TEST_07_15(), TemporalQueries::localTime(), null],
        ];
    }

    /**
     * @dataProvider data_query
     */

    public function test_query(TemporalAccessor $temporal, $query, $expected)
    {
        $this->assertEquals($temporal->query($query), $expected);
    }

    /**
     * @dataProvider data_query
     */

    public function test_queryFrom($temporal, TemporalQuery $query, $expected)
    {
        $this->assertEquals($query->queryFrom($temporal), $expected);
    }

    public function test_query_null()
    {
        TestHelper::assertNullException($this, function () {
            self::TEST_07_15()->query(null);
        });
    }

    //-----------------------------------------------------------------------
    // get*()
    //-----------------------------------------------------------------------
    function provider_sampleDates()
    {
        return [
            [1, 1],
            [1, 31],
            [2, 1],
            [2, 28],
            [2, 29],
            [7, 4],
            [7, 5],
        ];
    }

    /**
     * @dataProvider provider_sampleDates
     */
    public function test_get($m, $d)
    {
        $a = MonthDay::of($m, $d);
        $this->assertEquals($a->getMonth(), Month::of($m));
        $this->assertEquals($a->getMonthValue(), $m);
        $this->assertEquals($a->getDayOfMonth(), $d);
    }

    //-----------------------------------------------------------------------
    // with(Month)
    //-----------------------------------------------------------------------

    public function test_with_Month()
    {
        $this->assertEquals(MonthDay::of(6, 30)->with(Month::JANUARY()), MonthDay::of(1, 30));
    }


    public function test_with_Month_adjustToValid()
    {
        $this->assertEquals(MonthDay::of(7, 31)->with(Month::JUNE()), MonthDay::of(6, 30));
    }


    public function test_with_Month_adjustToValidFeb()
    {
        $this->assertEquals(MonthDay::of(7, 31)->with(Month::FEBRUARY()), MonthDay::of(2, 29));
    }


    public function test_with_Month_noChangeEqual()
    {
        $test = MonthDay::of(6, 30);
        $this->assertEquals($test->with(Month::JUNE()), $test);
    }

    public function test_with_Month_null()
    {
        TestHelper::assertNullException($this, function () {
            MonthDay::of(6, 30)->with(null);
        });
    }

    //-----------------------------------------------------------------------
    // withMonth()
    //-----------------------------------------------------------------------

    public function test_withMonth()
    {
        $this->assertEquals(MonthDay::of(6, 30)->withMonth(1), MonthDay::of(1, 30));
    }


    public function test_withMonth_adjustToValid()
    {
        $this->assertEquals(MonthDay::of(7, 31)->withMonth(6), MonthDay::of(6, 30));
    }


    public function test_withMonth_adjustToValidFeb()
    {
        $this->assertEquals(MonthDay::of(7, 31)->withMonth(2), MonthDay::of(2, 29));
    }


    public function test_withMonth_int_noChangeEqual()
    {
        $test = MonthDay::of(6, 30);
        $this->assertEquals($test->withMonth(6), $test);
    }

    /**
     * @expectedException \Celest\DateTimeException
     */

    public function test_withMonth_tooLow()
    {
        MonthDay::of(6, 30)->withMonth(0);
    }

    /**
     * @expectedException \Celest\DateTimeException
     */

    public function test_withMonth_tooHigh()
    {
        MonthDay::of(6, 30)->withMonth(13);
    }

    //-----------------------------------------------------------------------
    // withDayOfMonth()
    //-----------------------------------------------------------------------

    public function test_withDayOfMonth()
    {
        $this->assertEquals(MonthDay::of(6, 30)->withDayOfMonth(1), MonthDay::of(6, 1));
    }

    /**
     * @expectedException \Celest\DateTimeException
     */

    public function test_withDayOfMonth_invalid()
    {
        MonthDay::of(6, 30)->withDayOfMonth(31);
    }


    public function test_withDayOfMonth_adjustToValidFeb()
    {
        $this->assertEquals(MonthDay::of(2, 1)->withDayOfMonth(29), MonthDay::of(2, 29));
    }


    public function test_withDayOfMonth_noChangeEqual()
    {
        $test = MonthDay::of(6, 30);
        $this->assertEquals($test->withDayOfMonth(30), $test);
    }

    /**
     * @expectedException \Celest\DateTimeException
     */

    public function test_withDayOfMonth_tooLow()
    {
        MonthDay::of(6, 30)->withDayOfMonth(0);
    }

    /**
     * @expectedException \Celest\DateTimeException
     */

    public function test_withDayOfMonth_tooHigh()
    {
        MonthDay::of(6, 30)->withDayOfMonth(32);
    }

    //-----------------------------------------------------------------------
    // adjustInto()
    //-----------------------------------------------------------------------

    public function test_adjustDate()
    {
        $test = MonthDay::of(6, 30);
        $date = LocalDate::of(2007, 1, 1);
        $this->assertEquals($test->adjustInto($date), LocalDate::of(2007, 6, 30));
    }


    public function test_adjustDate_resolve()
    {
        $test = MonthDay::of(2, 29);
        $date = LocalDate::of(2007, 6, 30);
        $this->assertEquals($test->adjustInto($date), LocalDate::of(2007, 2, 28));
    }


    public function test_adjustDate_equal()
    {
        $test = MonthDay::of(6, 30);
        $date = LocalDate::of(2007, 6, 30);
        $this->assertEquals($test->adjustInto($date), $date);
    }

    public function test_adjustDate_null()
    {
        TestHelper::assertNullException($this, function () {
            self::TEST_07_15()->adjustInto(null);
        });
    }

    //-----------------------------------------------------------------------
    // isValidYear()
    //-----------------------------------------------------------------------

    public function test_isValidYear_june()
    {
        $test = MonthDay::of(6, 30);
        $this->assertEquals($test->isValidYear(2007), true);
    }


    public function test_isValidYear_febNonLeap()
    {
        $test = MonthDay::of(2, 29);
        $this->assertEquals($test->isValidYear(2007), false);
    }


    public function test_isValidYear_febLeap()
    {
        $test = MonthDay::of(2, 29);
        $this->assertEquals($test->isValidYear(2008), true);
    }

    //-----------------------------------------------------------------------
    // format(DateTimeFormatter)
    //-----------------------------------------------------------------------

    public function test_format_formatter()
    {
        $f = DateTimeFormatter::ofPattern("M d");
        $t = MonthDay::of(12, 3)->format($f);
        $this->assertEquals($t, "12 3");
    }

    public function test_format_formatter_null()
    {
        TestHelper::assertNullException($this, function () {
            MonthDay::of(12, 3)->format(null);
        });
    }

    //-----------------------------------------------------------------------
    // atYear()
    //-----------------------------------------------------------------------

    public function test_atYear_int()
    {
        $test = MonthDay::of(6, 30);
        $this->assertEquals($test->atYear(2008), LocalDate::of(2008, 6, 30));
    }


    public function test_atYear_int_leapYearAdjust()
    {
        $test = MonthDay::of(2, 29);
        $this->assertEquals($test->atYear(2005), LocalDate::of(2005, 2, 28));
    }

    /**
     * @expectedException \Celest\DateTimeException
     */

    public function test_atYear_int_invalidYear()
    {
        $test = MonthDay::of(6, 30);
        $test->atYear(Integer::MIN_VALUE);
    }

    //-----------------------------------------------------------------------
    // compareTo()
    //-----------------------------------------------------------------------

    public function test_comparisons()
    {
        $this->doTest_comparisons_MonthDay([
                MonthDay::of(1, 1),
                MonthDay::of(1, 31),
                MonthDay::of(2, 1),
                MonthDay::of(2, 29),
                MonthDay::of(3, 1),
                MonthDay::of(12, 31)
            ]
        );
    }

    function doTest_comparisons_MonthDay(array $localDates)
    {
        for ($i = 0;
             $i < count($localDates);
             $i++) {
            $a = $localDates[$i];
            for ($j = 0;
                 $j < count($localDates);
                 $j++) {
                $b = $localDates[$j];
                if ($i < $j) {
                    $this->assertTrue($a->compareTo($b) < 0, $a . " <=> " . $b);
                    $this->assertEquals($a->isBefore($b), true, $a . " <=> " . $b);
                    $this->assertEquals($a->isAfter($b), false, $a . " <=> " . $b);
                    $this->assertEquals($a->equals($b), false, $a . " <=> " . $b);
                } else
                    if ($i > $j) {
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
            self::TEST_07_15()->compareTo(null);
        });
    }

    public function test_isBefore_ObjectNull()
    {
        TestHelper::assertNullException($this, function () {
            self::TEST_07_15()->isBefore(null);
        });
    }

    public function test_isAfter_ObjectNull()
    {
        TestHelper::assertNullException($this, function () {
            self::TEST_07_15()->isAfter(null);
        });
    }

    //-----------------------------------------------------------------------
    // equals()
    //-----------------------------------------------------------------------

    public function test_equals()
    {
        $a = MonthDay::of(1, 1);
        $b = MonthDay::of(1, 1);
        $c = MonthDay::of(2, 1);
        $d = MonthDay::of(1, 2);

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
        $this->assertEquals(self::TEST_07_15()->equals(self::TEST_07_15()), true);
    }


    public function test_equals_string_false()
    {
        $this->assertEquals(self::TEST_07_15()->equals("2007-07-15"), false);
    }


    public function test_equals_null_false()
    {
        $this->assertEquals(self::TEST_07_15()->equals(null), false);
    }

    //-----------------------------------------------------------------------
    // toString()
    //-----------------------------------------------------------------------
    function provider_sampleToString()
    {
        return [
            [7, 5, "--07-05"],
            [12, 31, "--12-31"],
            [1, 2, "--01-02"],
        ];
    }

    /**
     * @dataProvider provider_sampleToString
     */
    public function test_toString($m, $d, $expected)
    {
        $test = MonthDay::of($m, $d);
        $str = $test->__toString();
        $this->assertEquals($expected, $str);
    }

}
