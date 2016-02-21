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

namespace Celest\Temporal;

use Celest\DayOfWeek;
use Celest\LocalDate;
use Celest\Month;
use Celest\TestHelper;

/**
 * Test TemporalAdjusters
 */
class TCKTemporalAdjustersTest extends \PHPUnit_Framework_TestCase
{

    //-----------------------------------------------------------------------
    // ofDateAdjuster()
    //-----------------------------------------------------------------------

    /** TODO
     * public function test_factory_ofDateAdjuster() {
     * $test = TemporalAdjusters::ofDateAdjuster($date -> $date.plusDays(2));
     * $this->assertEquals(LocalDate::of(2012, 6, 30).with($test), LocalDate::of(2012, 7, 2));
     * }
     *
     * public function test_factory_ofDateAdjuster_null() {
     * TemporalAdjusters::ofDateAdjuster(null);
     * }*/


    //-----------------------------------------------------------------------
    // firstDayOfMonth()
    //-----------------------------------------------------------------------

    public function test_factory_firstDayOfMonth()
    {
        $this->assertNotNull(TemporalAdjusters::firstDayOfMonth());
    }


    public function test_firstDayOfMonth_nonLeap()
    {
        foreach (Month::values() as $month) {
            for ($i = 1; $i <= $month->length(false); $i++) {
                $date = self::date(2007, $month, $i);
                $test = TemporalAdjusters::firstDayOfMonth()->adjustInto($date);
                $this->assertEquals($test->getYear(), 2007);
                $this->assertEquals($test->getMonth(), $month);
                $this->assertEquals($test->getDayOfMonth(), 1);
            }
        }
    }

    
    public function test_firstDayOfMonth_leap()
    {
        foreach (Month::values() as $month) {
            for ($i = 1; $i <= $month->length(true); $i++) {
                $date = self::date(2008, $month, $i);
                $test = TemporalAdjusters::firstDayOfMonth()->adjustInto($date);
                $this->assertEquals($test->getYear(), 2008);
                $this->assertEquals($test->getMonth(), $month);
                $this->assertEquals($test->getDayOfMonth(), 1);
            }
        }
    }

    //-----------------------------------------------------------------------
    // lastDayOfMonth()
    //-----------------------------------------------------------------------
    
    public function test_factory_lastDayOfMonth()
    {
        $this->assertNotNull(TemporalAdjusters::lastDayOfMonth());
    }

    
    public function test_lastDayOfMonth_nonLeap()
    {
        foreach (Month::values() as $month) {
            for ($i = 1; $i <= $month->length(false); $i++) {
                $date = self::date(2007, $month, $i);
                $test = TemporalAdjusters::lastDayOfMonth()->adjustInto($date);
                $this->assertEquals($test->getYear(), 2007);
                $this->assertEquals($test->getMonth(), $month);
                $this->assertEquals($test->getDayOfMonth(), $month->length(false));
            }
        }
    }

    
    public function test_lastDayOfMonth_leap()
    {
        foreach (Month::values() as $month) {
            for ($i = 1; $i <= $month->length(true); $i++) {
                $date = self::date(2008, $month, $i);
                $test = TemporalAdjusters::lastDayOfMonth()->adjustInto($date);
                $this->assertEquals($test->getYear(), 2008);
                $this->assertEquals($test->getMonth(), $month);
                $this->assertEquals($test->getDayOfMonth(), $month->length(true));
            }
        }
    }

    //-----------------------------------------------------------------------
    // firstDayOfNextMonth()
    //-----------------------------------------------------------------------
    
    public function test_factory_firstDayOfNextMonth()
    {
        $this->assertNotNull(TemporalAdjusters::firstDayOfNextMonth());
    }

    
    public function test_firstDayOfNextMonth_nonLeap()
    {
        foreach (Month::values() as $month) {
            for ($i = 1; $i <= $month->length(false); $i++) {
                $date = self::date(2007, $month, $i);
                $test = TemporalAdjusters::firstDayOfNextMonth()->adjustInto($date);
                $this->assertEquals($test->getYear(), $month == Month::DECEMBER() ? 2008 : 2007);
                $this->assertEquals($test->getMonth(), $month->plus(1));
                $this->assertEquals($test->getDayOfMonth(), 1);
            }
        }
    }

    
    public function test_firstDayOfNextMonth_leap()
    {
        foreach (Month::values() as $month) {
            for ($i = 1; $i <= $month->length(true); $i++) {
                $date = self::date(2008, $month, $i);
                $test = TemporalAdjusters::firstDayOfNextMonth()->adjustInto($date);
                $this->assertEquals($test->getYear(), $month == Month::DECEMBER() ? 2009 : 2008);
                $this->assertEquals($test->getMonth(), $month->plus(1));
                $this->assertEquals($test->getDayOfMonth(), 1);
            }
        }
    }

    //-----------------------------------------------------------------------
    // firstDayOfYear()
    //-----------------------------------------------------------------------
    
    public function test_factory_firstDayOfYear()
    {
        $this->assertNotNull(TemporalAdjusters::firstDayOfYear());
    }

    
    public function test_firstDayOfYear_nonLeap()
    {
        foreach (Month::values() as $month) {
            for ($i = 1; $i <= $month->length(false); $i++) {
                $date = self::date(2007, $month, $i);
                $test = TemporalAdjusters::firstDayOfYear()->adjustInto($date);
                $this->assertEquals($test->getYear(), 2007);
                $this->assertEquals($test->getMonth(), Month::JANUARY());
                $this->assertEquals($test->getDayOfMonth(), 1);
            }
        }
    }

    
    public function test_firstDayOfYear_leap()
    {
        foreach (Month::values() as $month) {
            for ($i = 1; $i <= $month->length(true); $i++) {
                $date = self::date(2008, $month, $i);
                $test = TemporalAdjusters::firstDayOfYear()->adjustInto($date);
                $this->assertEquals($test->getYear(), 2008);
                $this->assertEquals($test->getMonth(), Month::JANUARY());
                $this->assertEquals($test->getDayOfMonth(), 1);
            }
        }
    }

    //-----------------------------------------------------------------------
    // lastDayOfYear()
    //-----------------------------------------------------------------------
    
    public function test_factory_lastDayOfYear()
    {
        $this->assertNotNull(TemporalAdjusters::lastDayOfYear());
    }

    
    public function test_lastDayOfYear_nonLeap()
    {
        foreach (Month::values() as $month) {
            for ($i = 1; $i <= $month->length(false); $i++) {
                $date = self::date(2007, $month, $i);
                $test = TemporalAdjusters::lastDayOfYear()->adjustInto($date);
                $this->assertEquals($test->getYear(), 2007);
                $this->assertEquals($test->getMonth(), Month::DECEMBER());
                $this->assertEquals($test->getDayOfMonth(), 31);
            }
        }
    }

    
    public function test_lastDayOfYear_leap()
    {
        foreach (Month::values() as $month) {
            for ($i = 1; $i <= $month->length(true); $i++) {
                $date = self::date(2008, $month, $i);
                $test = TemporalAdjusters::lastDayOfYear()->adjustInto($date);
                $this->assertEquals($test->getYear(), 2008);
                $this->assertEquals($test->getMonth(), Month::DECEMBER());
                $this->assertEquals($test->getDayOfMonth(), 31);
            }
        }
    }

    //-----------------------------------------------------------------------
    // firstDayOfNextYear()
    //-----------------------------------------------------------------------
    
    public function test_factory_firstDayOfNextYear()
    {
        $this->assertNotNull(TemporalAdjusters::firstDayOfNextYear());
    }

    
    public function test_firstDayOfNextYear_nonLeap()
    {
        foreach (Month::values() as $month) {
            for ($i = 1; $i <= $month->length(false); $i++) {
                $date = self::date(2007, $month, $i);
                $test = TemporalAdjusters::firstDayOfNextYear()->adjustInto($date);
                $this->assertEquals($test->getYear(), 2008);
                $this->assertEquals($test->getMonth(), Month::JANUARY());
                $this->assertEquals($test->getDayOfMonth(), 1);
            }
        }
    }

    
    public function test_firstDayOfNextYear_leap()
    {
        foreach (Month::values() as $month) {
            for ($i = 1; $i <= $month->length(true); $i++) {
                $date = self::date(2008, $month, $i);
                $test = TemporalAdjusters::firstDayOfNextYear()->adjustInto($date);
                $this->assertEquals($test->getYear(), 2009);
                $this->assertEquals($test->getMonth(), Month::JANUARY());
                $this->assertEquals($test->getDayOfMonth(), 1);
            }
        }
    }

    //-----------------------------------------------------------------------
    // dayOfWeekInMonth()
    //-----------------------------------------------------------------------
    
    public function test_factory_dayOfWeekInMonth()
    {
        $this->assertNotNull(TemporalAdjusters::dayOfWeekInMonth(1, DayOfWeek::MONDAY()));
    }

    public function test_factory_dayOfWeekInMonth_nullDayOfWeek()
    {
        TestHelper::assertNullException($this, function () {
            TemporalAdjusters::dayOfWeekInMonth(1, null);
        });
    }

    function data_dayOfWeekInMonth_positive()
    {
        return [
            [2011, 1, DayOfWeek::TUESDAY(), LocalDate::of(2011, 1, 4)],
            [2011, 2, DayOfWeek::TUESDAY(), LocalDate::of(2011, 2, 1)],
            [2011, 3, DayOfWeek::TUESDAY(), LocalDate::of(2011, 3, 1)],
            [2011, 4, DayOfWeek::TUESDAY(), LocalDate::of(2011, 4, 5)],
            [2011, 5, DayOfWeek::TUESDAY(), LocalDate::of(2011, 5, 3)],
            [2011, 6, DayOfWeek::TUESDAY(), LocalDate::of(2011, 6, 7)],
            [2011, 7, DayOfWeek::TUESDAY(), LocalDate::of(2011, 7, 5)],
            [2011, 8, DayOfWeek::TUESDAY(), LocalDate::of(2011, 8, 2)],
            [2011, 9, DayOfWeek::TUESDAY(), LocalDate::of(2011, 9, 6)],
            [2011, 10, DayOfWeek::TUESDAY(), LocalDate::of(2011, 10, 4)],
            [2011, 11, DayOfWeek::TUESDAY(), LocalDate::of(2011, 11, 1)],
            [2011, 12, DayOfWeek::TUESDAY(), LocalDate::of(2011, 12, 6)],
        ];
    }

        /**
         * @dataProvider data_dayOfWeekInMonth_positive
         */
    public function test_dayOfWeekInMonth_positive($year, $month, DayOfWeek $dow, LocalDate $expected)
    {
        for ($ordinal = 1; $ordinal <= 5; $ordinal++) {
            for ($day = 1; $day <= Month::of($month)->length(false); $day++) {
                $date = LocalDate::of($year, $month, $day);
                $test = TemporalAdjusters::dayOfWeekInMonth($ordinal, $dow)->adjustInto($date);
                $this->assertEquals($test, $expected->plusWeeks($ordinal - 1));
            }
        }
    }

function data_dayOfWeekInMonth_zero()
{
    return [
        [2011, 1, DayOfWeek::TUESDAY(), LocalDate::of(2010, 12, 28)],
        [2011, 2, DayOfWeek::TUESDAY(), LocalDate::of(2011, 1, 25)],
        [2011, 3, DayOfWeek::TUESDAY(), LocalDate::of(2011, 2, 22)],
        [2011, 4, DayOfWeek::TUESDAY(), LocalDate::of(2011, 3, 29)],
        [2011, 5, DayOfWeek::TUESDAY(), LocalDate::of(2011, 4, 26)],
        [2011, 6, DayOfWeek::TUESDAY(), LocalDate::of(2011, 5, 31)],
        [2011, 7, DayOfWeek::TUESDAY(), LocalDate::of(2011, 6, 28)],
        [2011, 8, DayOfWeek::TUESDAY(), LocalDate::of(2011, 7, 26)],
        [2011, 9, DayOfWeek::TUESDAY(), LocalDate::of(2011, 8, 30)],
        [2011, 10, DayOfWeek::TUESDAY(), LocalDate::of(2011, 9, 27)],
        [2011, 11, DayOfWeek::TUESDAY(), LocalDate::of(2011, 10, 25)],
        [2011, 12, DayOfWeek::TUESDAY(), LocalDate::of(2011, 11, 29)],
    ];
}

/**
 * @dataProvider data_dayOfWeekInMonth_zero
 */
public
function test_dayOfWeekInMonth_zero($year, $month, DayOfWeek $dow, LocalDate $expected)
{
    for ($day = 1; $day <= Month::of($month)->length(false);
         $day++) {
        $date = LocalDate::of($year, $month, $day);
        $test = TemporalAdjusters::dayOfWeekInMonth(0, $dow)->adjustInto($date);
        $this->assertEquals($test, $expected);
    }
}

    function data_dayOfWeekInMonth_negative()
    {
        return [
            [2011, 1, DayOfWeek::TUESDAY(), LocalDate::of(2011, 1, 25)],
            [2011, 2, DayOfWeek::TUESDAY(), LocalDate::of(2011, 2, 22)],
            [2011, 3, DayOfWeek::TUESDAY(), LocalDate::of(2011, 3, 29)],
            [2011, 4, DayOfWeek::TUESDAY(), LocalDate::of(2011, 4, 26)],
            [2011, 5, DayOfWeek::TUESDAY(), LocalDate::of(2011, 5, 31)],
            [2011, 6, DayOfWeek::TUESDAY(), LocalDate::of(2011, 6, 28)],
            [2011, 7, DayOfWeek::TUESDAY(), LocalDate::of(2011, 7, 26)],
            [2011, 8, DayOfWeek::TUESDAY(), LocalDate::of(2011, 8, 30)],
            [2011, 9, DayOfWeek::TUESDAY(), LocalDate::of(2011, 9, 27)],
            [2011, 10, DayOfWeek::TUESDAY(), LocalDate::of(2011, 10, 25)],
            [2011, 11, DayOfWeek::TUESDAY(), LocalDate::of(2011, 11, 29)],
            [2011, 12, DayOfWeek::TUESDAY(), LocalDate::of(2011, 12, 27)],
        ];
    }

    /**
     * @dataProvider data_dayOfWeekInMonth_negative
     */
    public function test_dayOfWeekInMonth_negative($year, $month, DayOfWeek $dow, LocalDate $expected)
    {
        for ($ordinal = 0; $ordinal < 5;
             $ordinal++) {
            for ($day = 1; $day <= Month::of($month)->length(false);
                 $day++) {
                $date = LocalDate::of($year, $month, $day);
                $test = TemporalAdjusters::dayOfWeekInMonth(-1 - $ordinal, $dow)->adjustInto($date);
                $this->assertEquals($test, $expected->minusWeeks($ordinal));
            }
        }
    }

    //-----------------------------------------------------------------------
    // firstInMonth()
    //-----------------------------------------------------------------------
    
    public function test_factory_firstInMonth()
    {
        $this->assertNotNull(TemporalAdjusters::firstInMonth(DayOfWeek::MONDAY()));
    }

    public function test_factory_firstInMonth_nullDayOfWeek()
    {
        TestHelper::assertNullException($this, function () {
            TemporalAdjusters::firstInMonth(null);
        });
    }

    /**
     * @dataProvider data_dayOfWeekInMonth_positive
     */
    public function test_firstInMonth($year, $month, DayOfWeek $dow, LocalDate $expected)
    {
        for ($day = 1; $day <= Month::of($month)->length(false);
             $day++) {
            $date = LocalDate::of($year, $month, $day);
            $test = TemporalAdjusters::firstInMonth($dow)->adjustInto($date);
            $this->assertEquals($test, $expected, "day-of-month=" . $day);
        }
    }

    //-----------------------------------------------------------------------
    // lastInMonth()
    //-----------------------------------------------------------------------
    
    public function test_factory_lastInMonth()
    {
        $this->assertNotNull(TemporalAdjusters::lastInMonth(DayOfWeek::MONDAY()));
    }

    public function test_factory_lastInMonth_nullDayOfWeek()
    {
        TestHelper::assertNullException($this, function () {
            TemporalAdjusters::lastInMonth(null);
        });
    }

    /**
     * @dataProvider data_dayOfWeekInMonth_negative
     */
    public function test_lastInMonth($year, $month, DayOfWeek $dow, LocalDate $expected)
    {
        for ($day = 1; $day <= Month::of($month)->length(false);
             $day++) {
            $date = LocalDate::of($year, $month, $day);
            $test = TemporalAdjusters::lastInMonth($dow)->adjustInto($date);
            $this->assertEquals($test, $expected, "day-of-month=" . $day);
        }
    }

    //-----------------------------------------------------------------------
    // next()
    //-----------------------------------------------------------------------
    
    public function test_factory_next()
    {
        $this->assertNotNull(TemporalAdjusters::next(DayOfWeek::MONDAY()));
    }

    public function test_factory_next_nullDayOfWeek()
    {
        TestHelper::assertNullException($this, function () {
            TemporalAdjusters::next(null);
        });
    }

    
    public function test_next()
    {
        foreach (Month::values() as $month) {
            for ($i = 1; $i <= $month->length(false); $i++) {
                $date = self::date(2007, $month, $i);

                foreach (DayOfWeek::values() as $dow) {
                    $test = TemporalAdjusters::next($dow)->adjustInto($date);

                    $this->assertSame($test->getDayOfWeek(), $dow, $date . " " . $test);

                    if ($test->getYear() == 2007) {
                        $dayDiff = $test->getDayOfYear() - $date->getDayOfYear();
                        $this->assertTrue($dayDiff > 0 && $dayDiff < 8);
                    } else {
                        $this->assertSame($month, Month::DECEMBER());
                        $this->assertTrue($date->getDayOfMonth() > 24);
                        $this->assertEquals($test->getYear(), 2008);
                        $this->assertSame($test->getMonth(), Month::JANUARY());
                        $this->assertTrue($test->getDayOfMonth() < 8);
                    }
                }
            }
        }
    }

    //-----------------------------------------------------------------------
    // nextOrSame()
    //-----------------------------------------------------------------------
    
    public function test_factory_nextOrCurrent()
    {
        $this->assertNotNull(TemporalAdjusters::nextOrSame(DayOfWeek::MONDAY()));
    }

    public function test_factory_nextOrCurrent_nullDayOfWeek()
    {
        TestHelper::assertNullException($this, function () {
            TemporalAdjusters::nextOrSame(null);
        });
    }

    
    public function test_nextOrCurrent()
    {
        foreach (Month::values() as $month) {
            for ($i = 1; $i <= $month->length(false); $i++) {
                $date = self::date(2007, $month, $i);

                foreach (DayOfWeek::values() as $dow) {
                    $test = TemporalAdjusters::nextOrSame($dow)->adjustInto($date);

                    $this->assertSame($test->getDayOfWeek(), $dow);

                    if ($test->getYear() == 2007) {
                        $dayDiff = $test->getDayOfYear() - $date->getDayOfYear();
                        $this->assertTrue($dayDiff < 8);
                        $this->assertEquals($date->equals($test), $date->getDayOfWeek() == $dow);
                    } else {
                        $this->assertFalse($date->getDayOfWeek() == $dow);
                        $this->assertSame($month, Month::DECEMBER());
                        $this->assertTrue($date->getDayOfMonth() > 24);
                        $this->assertEquals($test->getYear(), 2008);
                        $this->assertSame($test->getMonth(), Month::JANUARY());
                        $this->assertTrue($test->getDayOfMonth() < 8);
                    }
                }
            }
        }
    }

    //-----------------------------------------------------------------------
    // previous()
    //-----------------------------------------------------------------------
    
    public function test_factory_previous()
    {
        $this->assertNotNull(TemporalAdjusters::previous(DayOfWeek::MONDAY()));
    }

    public function test_factory_previous_nullDayOfWeek()
    {
        TestHelper::assertNullException($this, function () {
            TemporalAdjusters::previous(null);
        });
    }

    
    public function test_previous()
    {
        foreach (Month::values() as $month) {
            for ($i = 1; $i <= $month->length(false); $i++) {
                $date = self::date(2007, $month, $i);

                foreach (DayOfWeek::values() as $dow) {
                    $test = TemporalAdjusters::previous($dow)->adjustInto($date);

                    $this->assertSame($test->getDayOfWeek(), $dow, $date . " " . $test);

                    if ($test->getYear() == 2007) {
                        $dayDiff = $test->getDayOfYear() - $date->getDayOfYear();
                        $this->assertTrue($dayDiff < 0 && $dayDiff > -8, $dayDiff . " " . $test);
                    } else {
                        $this->assertSame($month, Month::JANUARY());
                        $this->assertTrue($date->getDayOfMonth() < 8);
                        $this->assertEquals($test->getYear(), 2006);
                        $this->assertSame($test->getMonth(), Month::DECEMBER());
                        $this->assertTrue($test->getDayOfMonth() > 24);
                    }
                }
            }
        }
    }

    //-----------------------------------------------------------------------
    // previousOrSame()
    //-----------------------------------------------------------------------
    
    public function test_factory_previousOrCurrent()
    {
        $this->assertNotNull(TemporalAdjusters::previousOrSame(DayOfWeek::MONDAY()));
    }

    public function test_factory_previousOrCurrent_nullDayOfWeek()
    {
        TestHelper::assertNullException($this, function () {
            TemporalAdjusters::previousOrSame(null);
        });
    }

    
    public function test_previousOrCurrent()
    {
        foreach (Month::values() as $month) {
            for ($i = 1; $i <= $month->length(false); $i++) {
                $date = self::date(2007, $month, $i);

                foreach (DayOfWeek::values() as $dow) {
                    $test = TemporalAdjusters::previousOrSame($dow)->adjustInto($date);

                    $this->assertSame($test->getDayOfWeek(), $dow);

                    if ($test->getYear() == 2007) {
                        $dayDiff = $test->getDayOfYear() - $date->getDayOfYear();
                        $this->assertTrue($dayDiff <= 0 && $dayDiff > -7);
                        $this->assertEquals($date->equals($test), $date->getDayOfWeek() == $dow);
                    } else {
                        $this->assertFalse($date->getDayOfWeek() == $dow);
                        $this->assertSame($month, Month::JANUARY());
                        $this->assertTrue($date->getDayOfMonth() < 7);
                        $this->assertEquals($test->getYear(), 2006);
                        $this->assertSame($test->getMonth(), Month::DECEMBER());
                        $this->assertTrue($test->getDayOfMonth() > 25);
                    }
                }
            }
        }
    }

    private function date($year, Month $month, $day)
    {
        return LocalDate::ofMonth($year, $month, $day);
    }
}
