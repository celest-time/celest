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

use Celest\Chrono\ThaiBuddhistChronology;
use Celest\Helper\Integer;
use Celest\Helper\Long;
use Celest\Helper\Math;
use Celest\Temporal\ChronoUnit as CU;
use Celest\Temporal\Temporal;
use Celest\Temporal\TemporalAmount;
use Celest\Temporal\TemporalUnit;
use PHPUnit\Framework\TestCase;

class TemporalAmount_Years_tooBig implements TemporalAmount
{
    public function get(TemporalUnit $unit)
    {
        return Integer::MAX_VALUE + 1;
    }

    public function getUnits()
    {
        return [CU::YEARS()];
    }

    public function addTo(Temporal $temporal)
    {
        throw new \LogicException();
    }

    public function subtractFrom(Temporal $temporal)
    {
        throw new \LogicException();
    }
}

class TemporalAmount_YearsDays implements TemporalAmount
{
    public function get(TemporalUnit $unit)
    {
        if ($unit == CU::YEARS()) {
            return 23;
        } else {
            return 45;
        }
    }

    public function getUnits()
    {
        return [
            CU::YEARS(),
            CU::DAYS(),
        ];
    }

    public function addTo(Temporal $temporal)
    {
        throw new \LogicException();
    }

    public function subtractFrom(Temporal $temporal)
    {
        throw new \LogicException();
    }
}

class TemporalAmount_DaysHours implements TemporalAmount
{
    public function get(TemporalUnit $unit)
    {
        if ($unit == CU::DAYS()) {
            return 1;
        } else {
            return 2;
        }
    }

    public function getUnits()
    {
        return [
            CU::DAYS(),
            CU::HOURS(),
        ];
    }

    public function addTo(Temporal $temporal)
    {
        throw new \LogicException();
    }

    public function subtractFrom(Temporal $temporal)
    {
        throw new \LogicException();
    }
}

/**
 * Test Period.
 */
class TCKPeriod extends TestCase
{

    //-----------------------------------------------------------------------
    // ofYears(int)
    //-----------------------------------------------------------------------

    public function test_factory_ofYears_int()
    {
        $this->assertPeriod(Period::ofYears(0), 0, 0, 0);
        $this->assertPeriod(Period::ofYears(1), 1, 0, 0);
        $this->assertPeriod(Period::ofYears(234), 234, 0, 0);
        $this->assertPeriod(Period::ofYears(-100), -100, 0, 0);
        $this->assertPeriod(Period::ofYears(Integer::MAX_VALUE), Integer::MAX_VALUE, 0, 0);
        $this->assertPeriod(Period::ofYears(Integer::MIN_VALUE), Integer::MIN_VALUE, 0, 0);
    }

    //-----------------------------------------------------------------------
    // ofMonths(int)
    //-----------------------------------------------------------------------

    public function test_factory_ofMonths_int()
    {
        $this->assertPeriod(Period::ofMonths(0), 0, 0, 0);
        $this->assertPeriod(Period::ofMonths(1), 0, 1, 0);
        $this->assertPeriod(Period::ofMonths(234), 0, 234, 0);
        $this->assertPeriod(Period::ofMonths(-100), 0, -100, 0);
        $this->assertPeriod(Period::ofMonths(Integer::MAX_VALUE), 0, Integer::MAX_VALUE, 0);
        $this->assertPeriod(Period::ofMonths(Integer::MIN_VALUE), 0, Integer::MIN_VALUE, 0);
    }

    //-----------------------------------------------------------------------
    // ofWeeks(int)
    //-----------------------------------------------------------------------

    public function test_factory_ofWeeks_int()
    {
        $this->assertPeriod(Period::ofWeeks(0), 0, 0, 0);
        $this->assertPeriod(Period::ofWeeks(1), 0, 0, 7);
        $this->assertPeriod(Period::ofWeeks(234), 0, 0, 234 * 7);
        $this->assertPeriod(Period::ofWeeks(-100), 0, 0, -100 * 7);
        $this->assertPeriod(Period::ofWeeks(Math::div(Integer::MAX_VALUE, 7)), 0, 0, Math::div(Integer::MAX_VALUE, 7) * 7);
        $this->assertPeriod(Period::ofWeeks(Math::div(Integer::MIN_VALUE, 7)), 0, 0, Math::div(Integer::MIN_VALUE, 7) * 7);
    }

    //-----------------------------------------------------------------------
    // ofDays(int)
    //-----------------------------------------------------------------------

    public function test_factory_ofDays_int()
    {
        $this->assertPeriod(Period::ofDays(0), 0, 0, 0);
        $this->assertPeriod(Period::ofDays(1), 0, 0, 1);
        $this->assertPeriod(Period::ofDays(234), 0, 0, 234);
        $this->assertPeriod(Period::ofDays(-100), 0, 0, -100);
        $this->assertPeriod(Period::ofDays(Integer::MAX_VALUE), 0, 0, Integer::MAX_VALUE);
        $this->assertPeriod(Period::ofDays(Integer::MIN_VALUE), 0, 0, Integer::MIN_VALUE);
    }

    //-----------------------------------------------------------------------
    // of(int3)
    //-----------------------------------------------------------------------

    public function test_factory_of_ints()
    {
        $this->assertPeriod(Period::of(1, 2, 3), 1, 2, 3);
        $this->assertPeriod(Period::of(0, 2, 3), 0, 2, 3);
        $this->assertPeriod(Period::of(1, 0, 0), 1, 0, 0);
        $this->assertPeriod(Period::of(0, 0, 0), 0, 0, 0);
        $this->assertPeriod(Period::of(-1, -2, -3), -1, -2, -3);
    }

    //-----------------------------------------------------------------------
    // from(TemporalAmount)
    //-----------------------------------------------------------------------

    public function test_factory_from_TemporalAmount_Period()
    {
        $amount = Period::of(1, 2, 3);
        $this->assertPeriod(Period::from($amount), 1, 2, 3);
    }

    public function test_factory_from_TemporalAmount_YearsDays()
    {
        $amount = new TemporalAmount_YearsDays();
        $this->assertPeriod(Period::from($amount), 23, 0, 45);
    }

    /**
     * @expectedException \Celest\DateTimeException
     */
    public function test_factory_from_TemporalAmount_DaysHours()
    {
        $amount = new TemporalAmount_DaysHours();
        Period::from($amount);
    }

    /**
     * @expectedException \Celest\DateTimeException
     */
    public function test_factory_from_TemporalAmount_NonISO()
    {
        Period::from(ThaiBuddhistChronology::INSTANCE()->period(1, 1, 1));
    }

    /**
     * @expectedException \Celest\DateTimeException
     */
    public function test_factory_from_TemporalAmount_Duration()
    {
        Period::from(Duration::ZERO());
    }

    public function test_factory_from_TemporalAmount_null()
    {
        TestHelper::assertNullException($this, function () {
            Period::from(null);
        });
    }

    //-----------------------------------------------------------------------
    // parse(String)
    //-----------------------------------------------------------------------
    function data_factory_parseSuccess()
    {
        return [
            ["P1Y", Period::ofYears(1)],
            ["P12Y", Period::ofYears(12)],
            ["P987654321Y", Period::ofYears(987654321)],
            ["P+1Y", Period::ofYears(1)],
            ["P+12Y", Period::ofYears(12)],
            ["P+987654321Y", Period::ofYears(987654321)],
            ["P+0Y", Period::ofYears(0)],
            ["P0Y", Period::ofYears(0)],
            ["P-0Y", Period::ofYears(0)],
            ["P-25Y", Period::ofYears(-25)],
            ["P-987654321Y", Period::ofYears(-987654321)],
            ["P" . Integer::MAX_VALUE . "Y", Period::ofYears(Integer::MAX_VALUE)],
            ["P" . Integer::MIN_VALUE . "Y", Period::ofYears(Integer::MIN_VALUE)],

            ["P1M", Period::ofMonths(1)],
            ["P12M", Period::ofMonths(12)],
            ["P987654321M", Period::ofMonths(987654321)],
            ["P+1M", Period::ofMonths(1)],
            ["P+12M", Period::ofMonths(12)],
            ["P+987654321M", Period::ofMonths(987654321)],
            ["P+0M", Period::ofMonths(0)],
            ["P0M", Period::ofMonths(0)],
            ["P-0M", Period::ofMonths(0)],
            ["P-25M", Period::ofMonths(-25)],
            ["P-987654321M", Period::ofMonths(-987654321)],
            ["P" . Integer::MAX_VALUE . "M", Period::ofMonths(Integer::MAX_VALUE)],
            ["P" . Integer::MIN_VALUE . "M", Period::ofMonths(Integer::MIN_VALUE)],

            ["P1W", Period::ofDays(1 * 7)],
            ["P12W", Period::ofDays(12 * 7)],
            ["P7654321W", Period::ofDays(7654321 * 7)],
            ["P+1W", Period::ofDays(1 * 7)],
            ["P+12W", Period::ofDays(12 * 7)],
            ["P+7654321W", Period::ofDays(7654321 * 7)],
            ["P+0W", Period::ofDays(0)],
            ["P0W", Period::ofDays(0)],
            ["P-0W", Period::ofDays(0)],
            ["P-25W", Period::ofDays(-25 * 7)],
            ["P-7654321W", Period::ofDays(-7654321 * 7)],

            ["P1D", Period::ofDays(1)],
            ["P12D", Period::ofDays(12)],
            ["P987654321D", Period::ofDays(987654321)],
            ["P+1D", Period::ofDays(1)],
            ["P+12D", Period::ofDays(12)],
            ["P+987654321D", Period::ofDays(987654321)],
            ["P+0D", Period::ofDays(0)],
            ["P0D", Period::ofDays(0)],
            ["P-0D", Period::ofDays(0)],
            ["P-25D", Period::ofDays(-25)],
            ["P-987654321D", Period::ofDays(-987654321)],
            ["P" . Integer::MAX_VALUE . "D", Period::ofDays(Integer::MAX_VALUE)],
            ["P" . Integer::MIN_VALUE . "D", Period::ofDays(Integer::MIN_VALUE)],

            ["P0Y0M0D", Period::of(0, 0, 0)],
            ["P2Y0M0D", Period::of(2, 0, 0)],
            ["P0Y3M0D", Period::of(0, 3, 0)],
            ["P0Y0M4D", Period::of(0, 0, 4)],
            ["P2Y3M25D", Period::of(2, 3, 25)],
            ["P-2Y3M25D", Period::of(-2, 3, 25)],
            ["P2Y-3M25D", Period::of(2, -3, 25)],
            ["P2Y3M-25D", Period::of(2, 3, -25)],
            ["P-2Y-3M-25D", Period::of(-2, -3, -25)],

            ["P0Y0M0W0D", Period::of(0, 0, 0)],
            ["P2Y3M4W25D", Period::of(2, 3, 4 * 7 + 25)],
            ["P-2Y-3M-4W-25D", Period::of(-2, -3, -4 * 7 - 25)],
        ];
    }

    /**
     * @dataProvider data_factory_parseSuccess
     */
    public function test_factory_parse($text, Period $expected)
    {
        $p = Period::parse($text);
        $this->assertEquals($p, $expected);
    }

    /**
     * @dataProvider data_factory_parseSuccess
     */
    public function test_factory_parse_plus($text, Period $expected)
    {
        $p = Period::parse("+" . $text);
        $this->assertEquals($p, $expected);
    }

    /**
     * @dataProvider data_factory_parseSuccess
     */
    public function test_factory_parse_minus($text, Period $expected)
    {
        $p = null;
        try {
            $p = Period::parse("-" . $text);
        } catch (DateTimeParseException $ex) {
            $this->assertEquals($expected->getYears() == Integer::MIN_VALUE ||
                $expected->getMonths() == Integer::MIN_VALUE ||
                $expected->getDays() == Integer::MIN_VALUE, true);
            return;
        }
        // not inside try/catch or it breaks test
        $this->assertEquals($p, $expected->negated());
    }

    /**
     * @dataProvider data_factory_parseSuccess
     */
    public function test_factory_parse_lowerCase($text, Period $expected)
    {
        $p = Period::parse(strtolower($text));
        $this->assertEquals($p, $expected);
    }

    function data_parseFailure()
    {
        return [
            [""],
            ["PTD"],
            ["AT0D"],
            ["PA0D"],
            ["PT0A"],

            ["PT+D"],
            ["PT-D"],
            ["PT.D"],
            ["PTAD"],

            ["PT+0D"],
            ["PT-0D"],
            ["PT+1D"],
            ["PT-.D"],

            ["P1Y1MT1D"],
            ["P1YMD"],
            ["P1Y2Y"],
            ["PT1M+3S"],

            ["P1M2Y"],
            ["P1W2Y"],
            ["P1D2Y"],
            ["P1W2M"],
            ["P1D2M"],
            ["P1D2W"],

            ["PT1S1"],
            ["PT1S."],
            ["PT1SA"],
            ["PT1M1"],
            ["PT1M."],
            ["PT1MA"],

            /* We support longs
            ["P" . ((Integer::MAX_VALUE) + 1) . "Y"],
            ["P" . ((Integer::MAX_VALUE) + 1) . "M"],
            ["P" . ((Integer::MAX_VALUE) + 1) . "D"],
            ["P" . ((Integer::MIN_VALUE) - 1) . "Y"],
            ["P" . ((Integer::MIN_VALUE) - 1) . "M"],
            ["P" . ((Integer::MIN_VALUE) - 1) . "D"],*/

            ["Rubbish"],
        ];
    }

    /**
     * @expectedException \Celest\DateTimeParseException
     * @dataProvider data_parseFailure
     */
    public function test_factory_parseFailures($text)
    {
        try {
            Period::parse($text);
        } catch (DateTimeParseException $ex) {
            $this->assertEquals($ex->getParsedString(), $text);
            throw $ex;
        }
    }

    public function test_factory_parse_null()
    {
        TestHelper::assertNullException($this, function () {
            Period::parse(null);
        });
    }

    //-----------------------------------------------------------------------
    // between(LocalDate,LocalDate)
    //-----------------------------------------------------------------------
    function data_between()
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
     * @dataProvider data_between
     */
    public function test_factory_between_LocalDate($y1, $m1, $d1, $y2, $m2, $d2, $ye, $me, $de)
    {
        $start = LocalDate::of($y1, $m1, $d1);
        $end = LocalDate::of($y2, $m2, $d2);
        $test = Period::between($start, $end);
        $this->assertPeriod($test, $ye, $me, $de);
        //assertEquals(start.plus(test), end);
    }

    public function test_factory_between_LocalDate_nullFirst()
    {
        TestHelper::assertNullException($this, function () {
            Period::between(null, LocalDate::of(2010, 1, 1));
        });
    }

    public function test_factory_between_LocalDate_nullSecond()
    {
        TestHelper::assertNullException($this, function () {
            Period::between(LocalDate::of(2010, 1, 1), null);
        });
    }

    //-----------------------------------------------------------------------
    // isZero()
    //-----------------------------------------------------------------------

    public function test_isZero()
    {
        $this->assertEquals(Period::of(0, 0, 0)->isZero(), true);
        $this->assertEquals(Period::of(1, 2, 3)->isZero(), false);
        $this->assertEquals(Period::of(1, 0, 0)->isZero(), false);
        $this->assertEquals(Period::of(0, 2, 0)->isZero(), false);
        $this->assertEquals(Period::of(0, 0, 3)->isZero(), false);
    }

    //-----------------------------------------------------------------------
    // isNegative()
    //-----------------------------------------------------------------------

    public function test_isPositive()
    {
        $this->assertEquals(Period::of(0, 0, 0)->isNegative(), false);
        $this->assertEquals(Period::of(1, 2, 3)->isNegative(), false);
        $this->assertEquals(Period::of(1, 0, 0)->isNegative(), false);
        $this->assertEquals(Period::of(0, 2, 0)->isNegative(), false);
        $this->assertEquals(Period::of(0, 0, 3)->isNegative(), false);

        $this->assertEquals(Period::of(-1, -2, -3)->isNegative(), true);
        $this->assertEquals(Period::of(-1, -2, 3)->isNegative(), true);
        $this->assertEquals(Period::of(1, -2, -3)->isNegative(), true);
        $this->assertEquals(Period::of(-1, 2, -3)->isNegative(), true);
        $this->assertEquals(Period::of(-1, 2, 3)->isNegative(), true);
        $this->assertEquals(Period::of(1, -2, 3)->isNegative(), true);
        $this->assertEquals(Period::of(1, 2, -3)->isNegative(), true);
    }

    //-----------------------------------------------------------------------
    // withYears()
    //-----------------------------------------------------------------------

    public function test_withYears()
    {
        $this->assertPeriod(Period::of(1, 2, 3)->withYears(1), 1, 2, 3);
        $this->assertPeriod(Period::of(1, 2, 3)->withYears(10), 10, 2, 3);
        $this->assertPeriod(Period::of(1, 2, 3)->withYears(-10), -10, 2, 3);
        $this->assertPeriod(Period::of(-1, -2, -3)->withYears(10), 10, -2, -3);
        $this->assertPeriod(Period::of(1, 2, 3)->withYears(0), 0, 2, 3);
    }

    //-----------------------------------------------------------------------
    // withMonths()
    //-----------------------------------------------------------------------

    public function test_withMonths()
    {
        $this->assertPeriod(Period::of(1, 2, 3)->withMonths(2), 1, 2, 3);
        $this->assertPeriod(Period::of(1, 2, 3)->withMonths(10), 1, 10, 3);
        $this->assertPeriod(Period::of(1, 2, 3)->withMonths(-10), 1, -10, 3);
        $this->assertPeriod(Period::of(-1, -2, -3)->withMonths(10), -1, 10, -3);
        $this->assertPeriod(Period::of(1, 2, 3)->withMonths(0), 1, 0, 3);
    }

    //-----------------------------------------------------------------------
    // withDays()
    //-----------------------------------------------------------------------

    public function test_withDays()
    {
        $this->assertPeriod(Period::of(1, 2, 3)->withDays(3), 1, 2, 3);
        $this->assertPeriod(Period::of(1, 2, 3)->withDays(10), 1, 2, 10);
        $this->assertPeriod(Period::of(1, 2, 3)->withDays(-10), 1, 2, -10);
        $this->assertPeriod(Period::of(-1, -2, -3)->withDays(10), -1, -2, 10);
        $this->assertPeriod(Period::of(1, 2, 3)->withDays(0), 1, 2, 0);
    }

    //-----------------------------------------------------------------------
    // plus(Period)
    //-----------------------------------------------------------------------
    function data_plus()
    {
        return [
            [$this->pymd(0, 0, 0), $this->pymd(0, 0, 0), $this->pymd(0, 0, 0)],
            [$this->pymd(0, 0, 0), $this->pymd(5, 0, 0), $this->pymd(5, 0, 0)],
            [$this->pymd(0, 0, 0), $this->pymd(-5, 0, 0), $this->pymd(-5, 0, 0)],
            [$this->pymd(0, 0, 0), $this->pymd(0, 5, 0), $this->pymd(0, 5, 0)],
            [$this->pymd(0, 0, 0), $this->pymd(0, -5, 0), $this->pymd(0, -5, 0)],
            [$this->pymd(0, 0, 0), $this->pymd(0, 0, 5), $this->pymd(0, 0, 5)],
            [$this->pymd(0, 0, 0), $this->pymd(0, 0, -5), $this->pymd(0, 0, -5)],
            [$this->pymd(0, 0, 0), $this->pymd(2, 3, 4), $this->pymd(2, 3, 4)],
            [$this->pymd(0, 0, 0), $this->pymd(-2, -3, -4), $this->pymd(-2, -3, -4)],

            [$this->pymd(4, 5, 6), $this->pymd(2, 3, 4), $this->pymd(6, 8, 10)],
            [$this->pymd(4, 5, 6), $this->pymd(-2, -3, -4), $this->pymd(2, 2, 2)],
        ];
    }

    /**
     * @dataProvider data_plus
     */
    public function test_plus_TemporalAmount(Period $base, Period $add, $expected)
    {
        $this->assertEquals($base->plusAmount($add), $expected);
    }

    /**
     * @expectedException \Celest\DateTimeException
     */
    public function test_plus_TemporalAmount_nonISO()
    {
        $this->pymd(4, 5, 6)->plusAmount(ThaiBuddhistChronology::INSTANCE()->period(1, 0, 0));
    }

    /**
     * @expectedException \Celest\DateTimeException
     */
    public function test_plus_TemporalAmount_DaysHours()
    {
        $amount = new TemporalAmount_DaysHours();
        $this->pymd(4, 5, 6)->plusAmount($amount);
    }

    //-----------------------------------------------------------------------
    // plusYears()
    //-----------------------------------------------------------------------

    public function test_plusYears()
    {
        $this->assertPeriod(Period::of(1, 2, 3)->plusYears(0), 1, 2, 3);
        $this->assertPeriod(Period::of(1, 2, 3)->plusYears(10), 11, 2, 3);
        $this->assertPeriod(Period::of(1, 2, 3)->plusYears(-10), -9, 2, 3);
        $this->assertPeriod(Period::of(1, 2, 3)->plusYears(-1), 0, 2, 3);

        $this->assertPeriod(Period::of(1, 2, 3)->plusAmount(Period::ofYears(0)), 1, 2, 3);
        $this->assertPeriod(Period::of(1, 2, 3)->plusAmount(Period::ofYears(10)), 11, 2, 3);
        $this->assertPeriod(Period::of(1, 2, 3)->plusAmount(Period::ofYears(-10)), -9, 2, 3);
        $this->assertPeriod(Period::of(1, 2, 3)->plusAmount(Period::ofYears(-1)), 0, 2, 3);
    }

    /**
     * @expectedException \Celest\ArithmeticException
     */
    public function test_plusYears_overflowTooBig()
    {
        $test = Period::ofYears(Long::MAX_VALUE);
        $test->plusYears(1);
    }

    /**
     * @expectedException \Celest\ArithmeticException
     */
    public function test_plusYears_overflowTooSmall()
    {
        $test = Period::ofYears(Long::MIN_VALUE);
        $test->plusYears(-1);
    }

    //-----------------------------------------------------------------------
    // plusMonths()
    //-----------------------------------------------------------------------

    public function test_plusMonths()
    {
        $this->assertPeriod(Period::of(1, 2, 3)->plusMonths(0), 1, 2, 3);
        $this->assertPeriod(Period::of(1, 2, 3)->plusMonths(10), 1, 12, 3);
        $this->assertPeriod(Period::of(1, 2, 3)->plusMonths(-10), 1, -8, 3);
        $this->assertPeriod(Period::of(1, 2, 3)->plusMonths(-2), 1, 0, 3);

        $this->assertPeriod(Period::of(1, 2, 3)->plusAmount(Period::ofMonths(0)), 1, 2, 3);
        $this->assertPeriod(Period::of(1, 2, 3)->plusAmount(Period::ofMonths(10)), 1, 12, 3);
        $this->assertPeriod(Period::of(1, 2, 3)->plusAmount(Period::ofMonths(-10)), 1, -8, 3);
        $this->assertPeriod(Period::of(1, 2, 3)->plusAmount(Period::ofMonths(-2)), 1, 0, 3);
    }

    /**
     * @expectedException \Celest\ArithmeticException
     */
    public function test_plusMonths_overflowTooBig()
    {
        $test = Period::ofMonths(Long::MAX_VALUE);
        $test->plusMonths(1);
    }

    /**
     * @expectedException \Celest\ArithmeticException
     */
    public function test_plusMonths_overflowTooSmall()
    {
        $test = Period::ofMonths(Long::MIN_VALUE);
        $test->plusMonths(-1);
    }

    //-----------------------------------------------------------------------
    // plusDays()
    //-----------------------------------------------------------------------

    public function test_plusDays()
    {
        $this->assertPeriod(Period::of(1, 2, 3)->plusDays(0), 1, 2, 3);
        $this->assertPeriod(Period::of(1, 2, 3)->plusDays(10), 1, 2, 13);
        $this->assertPeriod(Period::of(1, 2, 3)->plusDays(-10), 1, 2, -7);
        $this->assertPeriod(Period::of(1, 2, 3)->plusDays(-3), 1, 2, 0);

        $this->assertPeriod(Period::of(1, 2, 3)->plusAmount(Period::ofDays(0)), 1, 2, 3);
        $this->assertPeriod(Period::of(1, 2, 3)->plusAmount(Period::ofDays(10)), 1, 2, 13);
        $this->assertPeriod(Period::of(1, 2, 3)->plusAmount(Period::ofDays(-10)), 1, 2, -7);
        $this->assertPeriod(Period::of(1, 2, 3)->plusAmount(Period::ofDays(-3)), 1, 2, 0);
    }

    /**
     * @expectedException \Celest\ArithmeticException
     */
    public function test_plusDays_overflowTooBig()
    {
        $test = Period::ofDays(Long::MAX_VALUE);
        $test->plusDays(1);
    }

    /**
     * @expectedException \Celest\ArithmeticException
     */
    public function test_plusDays_overflowTooSmall()
    {
        $test = Period::ofDays(Long::MIN_VALUE);
        $test->plusDays(-1);
    }

    //-----------------------------------------------------------------------
    // minus(Period)
    //-----------------------------------------------------------------------
    function data_minus()
    {
        return [
            [$this->pymd(0, 0, 0), $this->pymd(0, 0, 0), $this->pymd(0, 0, 0)],
            [$this->pymd(0, 0, 0), $this->pymd(5, 0, 0), $this->pymd(-5, 0, 0)],
            [$this->pymd(0, 0, 0), $this->pymd(-5, 0, 0), $this->pymd(5, 0, 0)],
            [$this->pymd(0, 0, 0), $this->pymd(0, 5, 0), $this->pymd(0, -5, 0)],
            [$this->pymd(0, 0, 0), $this->pymd(0, -5, 0), $this->pymd(0, 5, 0)],
            [$this->pymd(0, 0, 0), $this->pymd(0, 0, 5), $this->pymd(0, 0, -5)],
            [$this->pymd(0, 0, 0), $this->pymd(0, 0, -5), $this->pymd(0, 0, 5)],
            [$this->pymd(0, 0, 0), $this->pymd(2, 3, 4), $this->pymd(-2, -3, -4)],
            [$this->pymd(0, 0, 0), $this->pymd(-2, -3, -4), $this->pymd(2, 3, 4)],

            [$this->pymd(4, 5, 6), $this->pymd(2, 3, 4), $this->pymd(2, 2, 2)],
            [$this->pymd(4, 5, 6), $this->pymd(-2, -3, -4), $this->pymd(6, 8, 10)],
        ];
    }

    /**
     * @dataProvider data_minus
     */
    public function test_minus_TemporalAmount(Period $base, Period $subtract, Period $expected)
    {
        $this->assertEquals($base->minusAmount($subtract), $expected);
    }

    /**
     * @expectedException \Celest\DateTimeException
     */
    public function test_minus_TemporalAmount_nonISO()
    {
        $this->pymd(4, 5, 6)->minusAmount(ThaiBuddhistChronology::INSTANCE()->period(1, 0, 0));
    }

    /**
     * @expectedException \Celest\DateTimeException
     */
    public function test_minus_TemporalAmount_DaysHours()
    {
        $amount = new TemporalAmount_DaysHours();
        $this->pymd(4, 5, 6)->minusAmount($amount);
    }

    //-----------------------------------------------------------------------
    // minusYears()
    //-----------------------------------------------------------------------

    public function test_minusYears()
    {
        $this->assertPeriod(Period::of(1, 2, 3)->minusYears(0), 1, 2, 3);
        $this->assertPeriod(Period::of(1, 2, 3)->minusYears(10), -9, 2, 3);
        $this->assertPeriod(Period::of(1, 2, 3)->minusYears(-10), 11, 2, 3);
        $this->assertPeriod(Period::of(1, 2, 3)->minusYears(-1), 2, 2, 3);

        $this->assertPeriod(Period::of(1, 2, 3)->minusAmount(Period::ofYears(0)), 1, 2, 3);
        $this->assertPeriod(Period::of(1, 2, 3)->minusAmount(Period::ofYears(10)), -9, 2, 3);
        $this->assertPeriod(Period::of(1, 2, 3)->minusAmount(Period::ofYears(-10)), 11, 2, 3);
        $this->assertPeriod(Period::of(1, 2, 3)->minusAmount(Period::ofYears(-1)), 2, 2, 3);
    }

    /**
     * @expectedException \Celest\ArithmeticException
     */
    public function test_minusYears_overflowTooBig()
    {
        $test = Period::ofYears(Long::MAX_VALUE);
        $test->minusYears(-1);
    }

    /**
     * @expectedException \Celest\ArithmeticException
     */
    public function test_minusYears_overflowTooSmall()
    {
        $test = Period::ofYears(Long::MIN_VALUE);
        $test->minusYears(1);
    }

    //-----------------------------------------------------------------------
    // minusMonths()
    //-----------------------------------------------------------------------

    public function test_minusMonths()
    {
        $this->assertPeriod(Period::of(1, 2, 3)->minusMonths(0), 1, 2, 3);
        $this->assertPeriod(Period::of(1, 2, 3)->minusMonths(10), 1, -8, 3);
        $this->assertPeriod(Period::of(1, 2, 3)->minusMonths(-10), 1, 12, 3);
        $this->assertPeriod(Period::of(1, 2, 3)->minusMonths(-2), 1, 4, 3);

        $this->assertPeriod(Period::of(1, 2, 3)->minusAmount(Period::ofMonths(0)), 1, 2, 3);
        $this->assertPeriod(Period::of(1, 2, 3)->minusAmount(Period::ofMonths(10)), 1, -8, 3);
        $this->assertPeriod(Period::of(1, 2, 3)->minusAmount(Period::ofMonths(-10)), 1, 12, 3);
        $this->assertPeriod(Period::of(1, 2, 3)->minusAmount(Period::ofMonths(-2)), 1, 4, 3);
    }

    /**
     * @expectedException \Celest\ArithmeticException
     */
    public function test_minusMonths_overflowTooBig()
    {
        $test = Period::ofMonths(Long::MAX_VALUE);
        $test->minusMonths(-1);
    }

    /**
     * @expectedException \Celest\ArithmeticException
     */
    public function test_minusMonths_overflowTooSmall()
    {
        $test = Period::ofMonths(Long::MIN_VALUE);
        $test->minusMonths(1);
    }

    //-----------------------------------------------------------------------
    // minusDays()
    //-----------------------------------------------------------------------

    public function test_minusDays()
    {
        $this->assertPeriod(Period::of(1, 2, 3)->minusDays(0), 1, 2, 3);
        $this->assertPeriod(Period::of(1, 2, 3)->minusDays(10), 1, 2, -7);
        $this->assertPeriod(Period::of(1, 2, 3)->minusDays(-10), 1, 2, 13);
        $this->assertPeriod(Period::of(1, 2, 3)->minusDays(-3), 1, 2, 6);

        $this->assertPeriod(Period::of(1, 2, 3)->minusAmount(Period::ofDays(0)), 1, 2, 3);
        $this->assertPeriod(Period::of(1, 2, 3)->minusAmount(Period::ofDays(10)), 1, 2, -7);
        $this->assertPeriod(Period::of(1, 2, 3)->minusAmount(Period::ofDays(-10)), 1, 2, 13);
        $this->assertPeriod(Period::of(1, 2, 3)->minusAmount(Period::ofDays(-3)), 1, 2, 6);
    }

    /**
     * @expectedException \Celest\ArithmeticException
     */
    public function test_minusDays_overflowTooBig()
    {
        $test = Period::ofDays(Long::MAX_VALUE);
        $test->minusDays(-1);
    }

    /**
     * @expectedException \Celest\ArithmeticException
     */
    public function test_minusDays_overflowTooSmall()
    {
        $test = Period::ofDays(Long::MIN_VALUE);
        $test->minusDays(1);
    }

    //-----------------------------------------------------------------------
    // multipliedBy()
    //-----------------------------------------------------------------------

    public function test_multipliedBy()
    {
        $test = Period::of(1, 2, 3);
        $this->assertPeriod($test->multipliedBy(0), 0, 0, 0);
        $this->assertPeriod($test->multipliedBy(1), 1, 2, 3);
        $this->assertPeriod($test->multipliedBy(2), 2, 4, 6);
        $this->assertPeriod($test->multipliedBy(-3), -3, -6, -9);
    }


    public function test_multipliedBy_zeroBase()
    {
        $this->assertPeriod(Period::ZERO()->multipliedBy(2), 0, 0, 0);
    }

    /**
     * @expectedException \Celest\ArithmeticException
     */
    public function test_multipliedBy_overflowTooBig()
    {
        $test = Period::ofYears(Math::div(Long::MAX_VALUE, 2) + 1);
        $test->multipliedBy(2);
    }

    /**
     * @expectedException \Celest\ArithmeticException
     */
    public function test_multipliedBy_overflowTooSmall()
    {
        $test = Period::ofYears(Math::div(Long::MIN_VALUE, 2) - 1);
        $test->multipliedBy(2);
    }

    //-----------------------------------------------------------------------
    // negated()
    //-----------------------------------------------------------------------

    public function test_negated()
    {
        $this->assertPeriod(Period::of(0, 0, 0)->negated(), 0, 0, 0);
        $this->assertPeriod(Period::of(1, 2, 3)->negated(), -1, -2, -3);
        $this->assertPeriod(Period::of(-1, -2, -3)->negated(), 1, 2, 3);
        $this->assertPeriod(Period::of(-1, 2, -3)->negated(), 1, -2, 3);
        $this->assertPeriod(Period::of(Integer::MAX_VALUE, Integer::MAX_VALUE, Integer::MAX_VALUE)->negated(),
            -Integer::MAX_VALUE, -Integer::MAX_VALUE, -Integer::MAX_VALUE);
    }

    /**
     * @expectedException \Celest\ArithmeticException
     */
    public function test_negated_overflow_years()
    {
        Period::ofYears(Long::MIN_VALUE)->negated();
    }

    /**
     * @expectedException \Celest\ArithmeticException
     */
    public function test_negated_overflow_months()
    {
        Period::ofMonths(Long::MIN_VALUE)->negated();
    }

    /**
     * @expectedException \Celest\ArithmeticException
     */
    public function test_negated_overflow_days()
    {
        Period::ofDays(Long::MIN_VALUE)->negated();
    }

    //-----------------------------------------------------------------------
    // normalized()
    //-----------------------------------------------------------------------
    function data_normalized()
    {
        return [
            [0, 0, 0, 0],
            [1, 0, 1, 0],
            [-1, 0, -1, 0],

            [1, 1, 1, 1],
            [1, 2, 1, 2],
            [1, 11, 1, 11],
            [1, 12, 2, 0],
            [1, 13, 2, 1],
            [1, 23, 2, 11],
            [1, 24, 3, 0],
            [1, 25, 3, 1],

            [1, -1, 0, 11],
            [1, -2, 0, 10],
            [1, -11, 0, 1],
            [1, -12, 0, 0],
            [1, -13, 0, -1],
            [1, -23, 0, -11],
            [1, -24, -1, 0],
            [1, -25, -1, -1],
            [1, -35, -1, -11],
            [1, -36, -2, 0],
            [1, -37, -2, -1],

            [-1, 1, 0, -11],
            [-1, 11, 0, -1],
            [-1, 12, 0, 0],
            [-1, 13, 0, 1],
            [-1, 23, 0, 11],
            [-1, 24, 1, 0],
            [-1, 25, 1, 1],

            [-1, -1, -1, -1],
            [-1, -11, -1, -11],
            [-1, -12, -2, 0],
            [-1, -13, -2, -1],
        ];
    }

    /**
     * @dataProvider data_normalized
     */
    public function test_normalized($inputYears, $inputMonths, $expectedYears, $expectedMonths)
    {
        $this->assertPeriod(Period::of($inputYears, $inputMonths, 0)->normalized(), $expectedYears, $expectedMonths, 0);
    }

    /**
     * @dataProvider data_normalized
     */
    public function test_normalized_daysUnaffected($inputYears, $inputMonths, $expectedYears, $expectedMonths)
    {
        $this->assertPeriod(Period::of($inputYears, $inputMonths, 5)->normalized(), $expectedYears, $expectedMonths, 5);
    }

    /**
     * @expectedException \Celest\ArithmeticException
     */
    public function test_normalized_min()
    {
        $base = Period::of(Long::MIN_VALUE, -12, 0);
        $base->normalized();
    }

    /**
     * @expectedException \Celest\ArithmeticException
     */
    public function test_normalized_max()
    {
        $base = Period::of(Long::MAX_VALUE, 12, 0);
        $base->normalized();
    }

    //-----------------------------------------------------------------------
    // addTo()
    //-----------------------------------------------------------------------
    function data_addTo()
    {
        return [
            [$this->pymd(0, 0, 0), $this->date(2012, 6, 30), $this->date(2012, 6, 30)],

            [$this->pymd(1, 0, 0), $this->date(2012, 6, 10), $this->date(2013, 6, 10)],
            [$this->pymd(0, 1, 0), $this->date(2012, 6, 10), $this->date(2012, 7, 10)],
            [$this->pymd(0, 0, 1), $this->date(2012, 6, 10), $this->date(2012, 6, 11)],

            [$this->pymd(-1, 0, 0), $this->date(2012, 6, 10), $this->date(2011, 6, 10)],
            [$this->pymd(0, -1, 0), $this->date(2012, 6, 10), $this->date(2012, 5, 10)],
            [$this->pymd(0, 0, -1), $this->date(2012, 6, 10), $this->date(2012, 6, 9)],

            [$this->pymd(1, 2, 3), $this->date(2012, 6, 27), $this->date(2013, 8, 30)],
            [$this->pymd(1, 2, 3), $this->date(2012, 6, 28), $this->date(2013, 8, 31)],
            [$this->pymd(1, 2, 3), $this->date(2012, 6, 29), $this->date(2013, 9, 1)],
            [$this->pymd(1, 2, 3), $this->date(2012, 6, 30), $this->date(2013, 9, 2)],
            [$this->pymd(1, 2, 3), $this->date(2012, 7, 1), $this->date(2013, 9, 4)],

            [$this->pymd(1, 0, 0), $this->date(2011, 2, 28), $this->date(2012, 2, 28)],
            [$this->pymd(4, 0, 0), $this->date(2011, 2, 28), $this->date(2015, 2, 28)],
            [$this->pymd(1, 0, 0), $this->date(2012, 2, 29), $this->date(2013, 2, 28)],
            [$this->pymd(4, 0, 0), $this->date(2012, 2, 29), $this->date(2016, 2, 29)],

            [$this->pymd(1, 1, 0), $this->date(2011, 1, 29), $this->date(2012, 2, 29)],
            [$this->pymd(1, 2, 0), $this->date(2012, 2, 29), $this->date(2013, 4, 29)],
        ];
    }

    /**
     * @dataProvider data_addTo
     */
    public function test_addTo(Period $period, LocalDate $baseDate, LocalDate $expected)
    {
        $this->assertEquals($period->addTo($baseDate), $expected);
    }

    /**
     * @dataProvider data_addTo
     */
    public function test_addTo_usingLocalDatePlus(Period $period, LocalDate $baseDate, LocalDate $expected)
    {
        $this->assertEquals($baseDate->plusAmount($period), $expected);
    }

    public function test_addTo_nullZero()
    {
        TestHelper::assertNullException($this, function () {
            Period::ZERO()->addTo(null);
        });
    }

    public function test_addTo_nullNonZero()
    {
        TestHelper::assertNullException($this, function () {
            Period::ofDays(2)->addTo(null);
        });
    }

    //-----------------------------------------------------------------------
    // subtractFrom()
    //-----------------------------------------------------------------------
    function data_subtractFrom()
    {
        return [
            [$this->pymd(0, 0, 0), $this->date(2012, 6, 30), $this->date(2012, 6, 30)],

            [$this->pymd(1, 0, 0), $this->date(2012, 6, 10), $this->date(2011, 6, 10)],
            [$this->pymd(0, 1, 0), $this->date(2012, 6, 10), $this->date(2012, 5, 10)],
            [$this->pymd(0, 0, 1), $this->date(2012, 6, 10), $this->date(2012, 6, 9)],

            [$this->pymd(-1, 0, 0), $this->date(2012, 6, 10), $this->date(2013, 6, 10)],
            [$this->pymd(0, -1, 0), $this->date(2012, 6, 10), $this->date(2012, 7, 10)],
            [$this->pymd(0, 0, -1), $this->date(2012, 6, 10), $this->date(2012, 6, 11)],

            [$this->pymd(1, 2, 3), $this->date(2012, 8, 30), $this->date(2011, 6, 27)],
            [$this->pymd(1, 2, 3), $this->date(2012, 8, 31), $this->date(2011, 6, 27)],
            [$this->pymd(1, 2, 3), $this->date(2012, 9, 1), $this->date(2011, 6, 28)],
            [$this->pymd(1, 2, 3), $this->date(2012, 9, 2), $this->date(2011, 6, 29)],
            [$this->pymd(1, 2, 3), $this->date(2012, 9, 3), $this->date(2011, 6, 30)],
            [$this->pymd(1, 2, 3), $this->date(2012, 9, 4), $this->date(2011, 7, 1)],

            [$this->pymd(1, 0, 0), $this->date(2011, 2, 28), $this->date(2010, 2, 28)],
            [$this->pymd(4, 0, 0), $this->date(2011, 2, 28), $this->date(2007, 2, 28)],
            [$this->pymd(1, 0, 0), $this->date(2012, 2, 29), $this->date(2011, 2, 28)],
            [$this->pymd(4, 0, 0), $this->date(2012, 2, 29), $this->date(2008, 2, 29)],

            [$this->pymd(1, 1, 0), $this->date(2013, 3, 29), $this->date(2012, 2, 29)],
            [$this->pymd(1, 2, 0), $this->date(2012, 2, 29), $this->date(2010, 12, 29)],
        ];
    }

    /**
     * @dataProvider data_subtractFrom
     */
    public function test_subtractFrom(Period $period, LocalDate $baseDate, LocalDate $expected)
    {
        $this->assertEquals($period->subtractFrom($baseDate), $expected);
    }

    /**
     * @dataProvider data_subtractFrom
     */
    public function test_subtractFrom_usingLocalDateMinus(Period $period, LocalDate $baseDate, LocalDate $expected)
    {
        $this->assertEquals($baseDate->minusAmount($period), $expected);
    }

    public function test_subtractFrom_nullZero()
    {
        TestHelper::assertNullException($this, function () {
            Period::ZERO()->subtractFrom(null);
        });
    }

    public function test_subtractFrom_nullNonZero()
    {
        TestHelper::assertNullException($this, function () {
            Period::ofDays(2)->subtractFrom(null);
        });
    }

    //-----------------------------------------------------------------------
    // get units
    //-----------------------------------------------------------------------

    public function test_Period_getUnits()
    {
        $period = Period::of(2012, 1, 1);
        $units = $period->getUnits();
        $this->assertEquals(count($units), 3, "Period.getUnits should return 3 units");
        $this->assertEquals($units[0], CU::YEARS(), "Period.getUnits contains ChronoUnit.YEARS");
        $this->assertEquals($units[1], CU::MONTHS(), "Period.getUnits contains ChronoUnit.MONTHS");
        $this->assertEquals($units[2], CU::DAYS(), "Period.getUnits contains ChronoUnit.DAYS");
    }


    function data_goodTemporalUnit()
    {
        return [
            [2, CU::DAYS()],
            [2, CU::MONTHS()],
            [2, CU::YEARS()],
        ];
    }

    /**
     * @dataProvider data_goodTemporalUnit
     */
    public function test_good_getUnit($amount, TemporalUnit $unit)
    {
        $period = Period::of(2, 2, 2);
        $actual = $period->get($unit);
        $this->assertEquals($actual, $amount, "Value of unit: " . $unit);
    }

    function data_badTemporalUnit()
    {
        return [
            [CU::MICROS()],
            [CU::MILLIS()],
            [CU::HALF_DAYS()],
            [CU::DECADES()],
            [CU::CENTURIES()],
            [CU::MILLENNIA()],
        ];
    }

    /**
     * @expectedException \Celest\DateTimeException
     * @dataProvider data_badTemporalUnit
     */
    public function test_bad_getUnit(TemporalUnit $unit)
    {
        $period = Period::of(2, 2, 2);
        $period->get($unit);
    }

    //-----------------------------------------------------------------------
    // equals() / hashCode()
    //-----------------------------------------------------------------------
    public function test_equals()
    {
        $this->assertEquals(Period::of(1, 0, 0)->equals(Period::ofYears(1)), true);
        $this->assertEquals(Period::of(0, 1, 0)->equals(Period::ofMonths(1)), true);
        $this->assertEquals(Period::of(0, 0, 1)->equals(Period::ofDays(1)), true);
        $this->assertEquals(Period::of(1, 2, 3)->equals(Period::of(1, 2, 3)), true);

        $this->assertEquals(Period::ofYears(1)->equals(Period::ofYears(1)), true);
        $this->assertEquals(Period::ofYears(1)->equals(Period::ofYears(2)), false);

        $this->assertEquals(Period::ofMonths(1)->equals(Period::ofMonths(1)), true);
        $this->assertEquals(Period::ofMonths(1)->equals(Period::ofMonths(2)), false);

        $this->assertEquals(Period::ofDays(1)->equals(Period::ofDays(1)), true);
        $this->assertEquals(Period::ofDays(1)->equals(Period::ofDays(2)), false);

        $this->assertEquals(Period::of(1, 2, 3)->equals(Period::of(0, 2, 3)), false);
        $this->assertEquals(Period::of(1, 2, 3)->equals(Period::of(1, 0, 3)), false);
        $this->assertEquals(Period::of(1, 2, 3)->equals(Period::of(1, 2, 0)), false);
    }

    public function test_equals_self()
    {
        $test = Period::of(1, 2, 3);
        $this->assertEquals($test->equals($test), true);
    }

    public function test_equals_null()
    {
        $test = Period::of(1, 2, 3);
        $this->assertEquals($test->equals(null), false);
    }

    public function test_equals_otherClass()
    {
        $test = Period::of(1, 2, 3);
        $this->assertEquals($test->equals(""), false);
    }

    //-----------------------------------------------------------------------
    // toString()
    //-----------------------------------------------------------------------
    function data_toString()
    {
        return [
            [Period::ZERO(), "P0D"],
            [Period::ofDays(0), "P0D"],
            [Period::ofYears(1), "P1Y"],
            [Period::ofMonths(1), "P1M"],
            [Period::ofDays(1), "P1D"],
            [Period::of(1, 2, 0), "P1Y2M"],
            [Period::of(0, 2, 3), "P2M3D"],
            [Period::of(1, 2, 3), "P1Y2M3D"],
        ];
    }

    /**
     * @dataProvider data_toString
     */
    public function test_toString(Period $input, $expected)
    {
        $this->assertEquals($input->__toString(), $expected);
    }

    /**
     * @dataProvider data_toString
     */
    public function test_jsonSerializable(Period $input, $expected)
    {
        $this->assertEquals(json_decode(json_encode($input)), $expected);
    }

    /**
     * @dataProvider data_toString
     */
    public function test_parse(Period $test, $expected)
    {
        $this->assertEquals(Period::parse($expected), $test);
    }

    //-----------------------------------------------------------------------
    private function assertPeriod(Period $test, $y, $m, $d)
    {
        $this->assertEquals($test->getYears(), $y, "years");
        $this->assertEquals($test->getMonths(), $m, "months");
        $this->assertEquals($test->getDays(), $d, "days");
        $this->assertEquals($test->toTotalMonths(), $y * 12 + $m, "totalMonths");
    }

    private static function pymd($y, $m, $d)
    {
        return Period::of($y, $m, $d);
    }

    private static function date($y, $m, $d)
    {
        return LocalDate::of($y, $m, $d);
    }

}
