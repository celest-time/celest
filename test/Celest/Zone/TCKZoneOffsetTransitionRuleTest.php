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
 * Copyright (c) 2010-2012, Stephen Colebourne & Michael Nascimento Santos
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
namespace Celest\Zone;

use Celest\DayOfWeek;
use Celest\LocalDateTime;
use Celest\LocalTime;
use Celest\Month;
use Celest\TestHelper;
use Celest\ZoneOffset;
use PHPUnit_Framework_TestCase;

/**
 * Test ZoneOffsetTransitionRule.
 */
class TCKZoneOffsetTransitionRuleTest extends PHPUnit_Framework_TestCase
{

    private static function TIME_0100()
    {
        return LocalTime::of(1, 0);
    }

    private static function OFFSET_0200()
    {
        return ZoneOffset::ofHours(2);
    }

    private static function OFFSET_0300()
    {
        return ZoneOffset::ofHours(3);
    }

    //-----------------------------------------------------------------------
    // factory
    //-----------------------------------------------------------------------
    public function test_factory_nullMonth()
    {
        TestHelper::assertNullException($this, function () {
            ZoneOffsetTransitionRule::of(
                null, 20, DayOfWeek::SUNDAY(), self::TIME_0100(), false, TimeDefinition::WALL(),
                self::OFFSET_0200(), self::OFFSET_0200(), self::OFFSET_0300());
        });

    }

    public function test_factory_nullTime()
    {
        TestHelper::assertNullException($this, function () {
            ZoneOffsetTransitionRule::of(
                Month::MARCH(), 20, DayOfWeek::SUNDAY(), null, false, TimeDefinition::WALL(),
                self::OFFSET_0200(), self::OFFSET_0200(), self::OFFSET_0300());
        });
    }

    public function test_factory_nullTimeDefinition()
    {
        TestHelper::assertNullException($this, function () {
            ZoneOffsetTransitionRule::of(
                Month::MARCH(), 20, DayOfWeek::SUNDAY(), self::TIME_0100(), false, null,
                self::OFFSET_0200(), self::OFFSET_0200(), self::OFFSET_0300());
        });
    }

    public function test_factory_nullStandardOffset()
    {
        TestHelper::assertNullException($this, function () {
            ZoneOffsetTransitionRule::of(
                Month::MARCH(), 20, DayOfWeek::SUNDAY(), self::TIME_0100(), false, TimeDefinition::WALL(),
                null, self::OFFSET_0200(), self::OFFSET_0300());
        });
    }

    public function test_factory_nullOffsetBefore()
    {
        TestHelper::assertNullException($this, function () {
            ZoneOffsetTransitionRule::of(
                Month::MARCH(), 20, DayOfWeek::SUNDAY(), self::TIME_0100(), false, TimeDefinition::WALL(),
                self::OFFSET_0200(), null, self::OFFSET_0300());
        });
    }

    public function test_factory_nullOffsetAfter()
    {
        TestHelper::assertNullException($this, function () {
            ZoneOffsetTransitionRule::of(
                Month::MARCH(), 20, DayOfWeek::SUNDAY(), self::TIME_0100(), false, TimeDefinition::WALL(),
                self::OFFSET_0200(), self::OFFSET_0200(), null);
        });
    }

    /**
     * @expectedException \Celest\IllegalArgumentException
     */
    public function test_factory_invalidDayOfMonthIndicator_tooSmall()
    {
        ZoneOffsetTransitionRule::of(
            Month::MARCH(), -29, DayOfWeek::SUNDAY(), self::TIME_0100(), false, TimeDefinition::WALL(),
            self::OFFSET_0200(), self::OFFSET_0200(), self::OFFSET_0300());
    }

    /**
     * @expectedException \Celest\IllegalArgumentException
     */
    public function test_factory_invalidDayOfMonthIndicator_zero()
    {
        ZoneOffsetTransitionRule::of(
            Month::MARCH(), 0, DayOfWeek::SUNDAY(), self::TIME_0100(), false, TimeDefinition::WALL(),
            self::OFFSET_0200(), self::OFFSET_0200(), self::OFFSET_0300());
    }

    /**
     * @expectedException \Celest\IllegalArgumentException
     */
    public function test_factory_invalidDayOfMonthIndicator_tooLarge()
    {
        ZoneOffsetTransitionRule::of(
            Month::MARCH(), 32, DayOfWeek::SUNDAY(), self::TIME_0100(), false, TimeDefinition::WALL(),
            self::OFFSET_0200(), self::OFFSET_0200(), self::OFFSET_0300());
    }

    /**
     * @expectedException \Celest\IllegalArgumentException
     */
    public function test_factory_invalidMidnightFlag()
    {
        ZoneOffsetTransitionRule::of(
            Month::MARCH(), 20, DayOfWeek::SUNDAY(), self::TIME_0100(), true, TimeDefinition::WALL(),
            self::OFFSET_0200(), self::OFFSET_0200(), self::OFFSET_0300());
    }

    //-----------------------------------------------------------------------
    // getters
    //-----------------------------------------------------------------------

    public function test_getters_floatingWeek()
    {
        $test = ZoneOffsetTransitionRule::of(
            Month::MARCH(), 20, DayOfWeek::SUNDAY(), self::TIME_0100(), false, TimeDefinition::WALL(),
            self::OFFSET_0200(), self::OFFSET_0200(), self::OFFSET_0300());
        $this->assertEquals($test->getMonth(), Month::MARCH());
        $this->assertEquals($test->getDayOfMonthIndicator(), 20);
        $this->assertEquals($test->getDayOfWeek(), DayOfWeek::SUNDAY());
        $this->assertEquals($test->getLocalTime(), self::TIME_0100());
        $this->assertEquals($test->isMidnightEndOfDay(), false);
        $this->assertEquals($test->getTimeDefinition(), TimeDefinition::WALL());
        $this->assertEquals($test->getStandardOffset(), self::OFFSET_0200());
        $this->assertEquals($test->getOffsetBefore(), self::OFFSET_0200());
        $this->assertEquals($test->getOffsetAfter(), self::OFFSET_0300());
    }


    public function test_getters_floatingWeekBackwards()
    {
        $test = ZoneOffsetTransitionRule::of(
            Month::MARCH(), -1, DayOfWeek::SUNDAY(), self::TIME_0100(), false, TimeDefinition::WALL(),
            self::OFFSET_0200(), self::OFFSET_0200(), self::OFFSET_0300());
        $this->assertEquals($test->getMonth(), Month::MARCH());
        $this->assertEquals($test->getDayOfMonthIndicator(), -1);
        $this->assertEquals($test->getDayOfWeek(), DayOfWeek::SUNDAY());
        $this->assertEquals($test->getLocalTime(), self::TIME_0100());
        $this->assertEquals($test->isMidnightEndOfDay(), false);
        $this->assertEquals($test->getTimeDefinition(), TimeDefinition::WALL());
        $this->assertEquals($test->getStandardOffset(), self::OFFSET_0200());
        $this->assertEquals($test->getOffsetBefore(), self::OFFSET_0200());
        $this->assertEquals($test->getOffsetAfter(), self::OFFSET_0300());
    }


    public function test_getters_fixedDate()
    {
        $test = ZoneOffsetTransitionRule::of(
            Month::MARCH(), 20, null, self::TIME_0100(), false, TimeDefinition::WALL(),
            self::OFFSET_0200(), self::OFFSET_0200(), self::OFFSET_0300());
        $this->assertEquals($test->getMonth(), Month::MARCH());
        $this->assertEquals($test->getDayOfMonthIndicator(), 20);
        $this->assertEquals($test->getDayOfWeek(), null);
        $this->assertEquals($test->getLocalTime(), self::TIME_0100());
        $this->assertEquals($test->isMidnightEndOfDay(), false);
        $this->assertEquals($test->getTimeDefinition(), TimeDefinition::WALL());
        $this->assertEquals($test->getStandardOffset(), self::OFFSET_0200());
        $this->assertEquals($test->getOffsetBefore(), self::OFFSET_0200());
        $this->assertEquals($test->getOffsetAfter(), self::OFFSET_0300());
    }


    //-----------------------------------------------------------------------
    // createTransition()
    //-----------------------------------------------------------------------

    public function test_createTransition_floatingWeek_gap_notEndOfDay()
    {
        $test = ZoneOffsetTransitionRule::of(
            Month::MARCH(), 20, DayOfWeek::SUNDAY(), self::TIME_0100(), false, TimeDefinition::WALL(),
            self::OFFSET_0200(), self::OFFSET_0200(), self::OFFSET_0300());
        $trans = ZoneOffsetTransition::of(
            LocalDateTime::ofMonth(2000, Month::MARCH(), 26, 1, 0), self::OFFSET_0200(), self::OFFSET_0300());
        $this->assertEquals($test->createTransition(2000), $trans);
    }


    public function test_createTransition_floatingWeek_overlap_endOfDay()
    {
        $test = ZoneOffsetTransitionRule::of(
            Month::MARCH(), 20, DayOfWeek::SUNDAY(), LocalTime::MIDNIGHT(), true, TimeDefinition::WALL(),
            self::OFFSET_0200(), self::OFFSET_0300(), self::OFFSET_0200());
        $trans = ZoneOffsetTransition::of(
            LocalDateTime::ofMonth(2000, Month::MARCH(), 27, 0, 0), self::OFFSET_0300(), self::OFFSET_0200());
        $this->assertEquals($test->createTransition(2000), $trans);
    }


    public function test_createTransition_floatingWeekBackwards_last()
    {
        $test = ZoneOffsetTransitionRule::of(
            Month::MARCH(), -1, DayOfWeek::SUNDAY(), self::TIME_0100(), false, TimeDefinition::WALL(),
            self::OFFSET_0200(), self::OFFSET_0200(), self::OFFSET_0300());
        $trans = ZoneOffsetTransition::of(
            LocalDateTime::ofMonth(2000, Month::MARCH(), 26, 1, 0), self::OFFSET_0200(), self::OFFSET_0300());
        $this->assertEquals($test->createTransition(2000), $trans);
    }


    public function test_createTransition_floatingWeekBackwards_seventhLast()
    {
        $test = ZoneOffsetTransitionRule::of(
            Month::MARCH(), -7, DayOfWeek::SUNDAY(), self::TIME_0100(), false, TimeDefinition::WALL(),
            self::OFFSET_0200(), self::OFFSET_0200(), self::OFFSET_0300());
        $trans = ZoneOffsetTransition::of(
            LocalDateTime::ofMonth(2000, Month::MARCH(), 19, 1, 0), self::OFFSET_0200(), self::OFFSET_0300());
        $this->assertEquals($test->createTransition(2000), $trans);
    }


    public function test_createTransition_floatingWeekBackwards_secondLast()
    {
        $test = ZoneOffsetTransitionRule::of(
            Month::MARCH(), -2, DayOfWeek::SUNDAY(), self::TIME_0100(), false, TimeDefinition::WALL(),
            self::OFFSET_0200(), self::OFFSET_0200(), self::OFFSET_0300());
        $trans = ZoneOffsetTransition::of(
            LocalDateTime::ofMonth(2000, Month::MARCH(), 26, 1, 0), self::OFFSET_0200(), self::OFFSET_0300());
        $this->assertEquals($test->createTransition(2000), $trans);
    }


    public function test_createTransition_fixedDate()
    {
        $test = ZoneOffsetTransitionRule::of(
            Month::MARCH(), 20, null, self::TIME_0100(), false, TimeDefinition::STANDARD(),
            self::OFFSET_0200(), self::OFFSET_0200(), self::OFFSET_0300());
        $trans = ZoneOffsetTransition::of(
            LocalDateTime::ofMonth(2000, Month::MARCH(), 20, 1, 0), self::OFFSET_0200(), self::OFFSET_0300());
        $this->assertEquals($test->createTransition(2000), $trans);
    }

    //-----------------------------------------------------------------------
    // equals()
    //-----------------------------------------------------------------------

    public function test_equals_monthDifferent()
    {
        $a = ZoneOffsetTransitionRule::of(
            Month::MARCH(), 20, DayOfWeek::SUNDAY(), self::TIME_0100(), false, TimeDefinition::WALL(),
            self::OFFSET_0200(), self::OFFSET_0200(), self::OFFSET_0300());
        $b = ZoneOffsetTransitionRule::of(
            Month::APRIL(), 20, DayOfWeek::SUNDAY(), self::TIME_0100(), false, TimeDefinition::WALL(),
            self::OFFSET_0200(), self::OFFSET_0200(), self::OFFSET_0300());
        $this->assertEquals($a->equals($a), true);
        $this->assertEquals($a->equals($b), false);
        $this->assertEquals($b->equals($a), false);
        $this->assertEquals($b->equals($b), true);
    }


    public function test_equals_dayOfMonthDifferent()
    {
        $a = ZoneOffsetTransitionRule::of(
            Month::MARCH(), 20, DayOfWeek::SUNDAY(), self::TIME_0100(), false, TimeDefinition::WALL(),
            self::OFFSET_0200(), self::OFFSET_0200(), self::OFFSET_0300());
        $b = ZoneOffsetTransitionRule::of(
            Month::MARCH(), 21, DayOfWeek::SUNDAY(), self::TIME_0100(), false, TimeDefinition::WALL(),
            self::OFFSET_0200(), self::OFFSET_0200(), self::OFFSET_0300());
        $this->assertEquals($a->equals($a), true);
        $this->assertEquals($a->equals($b), false);
        $this->assertEquals($b->equals($a), false);
        $this->assertEquals($b->equals($b), true);
    }


    public function test_equals_dayOfWeekDifferent()
    {
        $a = ZoneOffsetTransitionRule::of(
            Month::MARCH(), 20, DayOfWeek::SUNDAY(), self::TIME_0100(), false, TimeDefinition::WALL(),
            self::OFFSET_0200(), self::OFFSET_0200(), self::OFFSET_0300());
        $b = ZoneOffsetTransitionRule::of(
            Month::MARCH(), 20, DayOfWeek::SATURDAY(), self::TIME_0100(), false, TimeDefinition::WALL(),
            self::OFFSET_0200(), self::OFFSET_0200(), self::OFFSET_0300());
        $this->assertEquals($a->equals($a), true);
        $this->assertEquals($a->equals($b), false);
        $this->assertEquals($b->equals($a), false);
        $this->assertEquals($b->equals($b), true);
    }


    public function test_equals_dayOfWeekDifferentNull()
    {
        $a = ZoneOffsetTransitionRule::of(
            Month::MARCH(), 20, DayOfWeek::SUNDAY(), self::TIME_0100(), false, TimeDefinition::WALL(),
            self::OFFSET_0200(), self::OFFSET_0200(), self::OFFSET_0300());
        $b = ZoneOffsetTransitionRule::of(
            Month::MARCH(), 20, null, self::TIME_0100(), false, TimeDefinition::WALL(),
            self::OFFSET_0200(), self::OFFSET_0200(), self::OFFSET_0300());
        $this->assertEquals($a->equals($a), true);
        $this->assertEquals($a->equals($b), false);
        $this->assertEquals($b->equals($a), false);
        $this->assertEquals($b->equals($b), true);
    }


    public function test_equals_localTimeDifferent()
    {
        $a = ZoneOffsetTransitionRule::of(
            Month::MARCH(), 20, DayOfWeek::SUNDAY(), self::TIME_0100(), false, TimeDefinition::WALL(),
            self::OFFSET_0200(), self::OFFSET_0200(), self::OFFSET_0300());
        $b = ZoneOffsetTransitionRule::of(
            Month::MARCH(), 20, DayOfWeek::SUNDAY(), LocalTime::MIDNIGHT(), false, TimeDefinition::WALL(),
            self::OFFSET_0200(), self::OFFSET_0200(), self::OFFSET_0300());
        $this->assertEquals($a->equals($a), true);
        $this->assertEquals($a->equals($b), false);
        $this->assertEquals($b->equals($a), false);
        $this->assertEquals($b->equals($b), true);
    }


    public function test_equals_endOfDayDifferent()
    {
        $a = ZoneOffsetTransitionRule::of(
            Month::MARCH(), 20, DayOfWeek::SUNDAY(), LocalTime::MIDNIGHT(), false, TimeDefinition::WALL(),
            self::OFFSET_0200(), self::OFFSET_0200(), self::OFFSET_0300());
        $b = ZoneOffsetTransitionRule::of(
            Month::MARCH(), 20, DayOfWeek::SUNDAY(), LocalTime::MIDNIGHT(), true, TimeDefinition::WALL(),
            self::OFFSET_0200(), self::OFFSET_0200(), self::OFFSET_0300());
        $this->assertEquals($a->equals($a), true);
        $this->assertEquals($a->equals($b), false);
        $this->assertEquals($b->equals($a), false);
        $this->assertEquals($b->equals($b), true);
    }


    public function test_equals_timeDefinitionDifferent()
    {
        $a = ZoneOffsetTransitionRule::of(
            Month::MARCH(), 20, DayOfWeek::SUNDAY(), self::TIME_0100(), false, TimeDefinition::WALL(),
            self::OFFSET_0200(), self::OFFSET_0200(), self::OFFSET_0300());
        $b = ZoneOffsetTransitionRule::of(
            Month::MARCH(), 20, DayOfWeek::SUNDAY(), self::TIME_0100(), false, TimeDefinition::STANDARD(),
            self::OFFSET_0200(), self::OFFSET_0200(), self::OFFSET_0300());
        $this->assertEquals($a->equals($a), true);
        $this->assertEquals($a->equals($b), false);
        $this->assertEquals($b->equals($a), false);
        $this->assertEquals($b->equals($b), true);
    }


    public function test_equals_standardOffsetDifferent()
    {
        $a = ZoneOffsetTransitionRule::of(
            Month::MARCH(), 20, DayOfWeek::SUNDAY(), self::TIME_0100(), false, TimeDefinition::WALL(),
            self::OFFSET_0200(), self::OFFSET_0200(), self::OFFSET_0300());
        $b = ZoneOffsetTransitionRule::of(
            Month::MARCH(), 20, DayOfWeek::SUNDAY(), self::TIME_0100(), false, TimeDefinition::WALL(),
            self::OFFSET_0300(), self::OFFSET_0200(), self::OFFSET_0300());
        $this->assertEquals($a->equals($a), true);
        $this->assertEquals($a->equals($b), false);
        $this->assertEquals($b->equals($a), false);
        $this->assertEquals($b->equals($b), true);
    }


    public function test_equals_offsetBeforeDifferent()
    {
        $a = ZoneOffsetTransitionRule::of(
            Month::MARCH(), 20, DayOfWeek::SUNDAY(), self::TIME_0100(), false, TimeDefinition::WALL(),
            self::OFFSET_0200(), self::OFFSET_0200(), self::OFFSET_0300());
        $b = ZoneOffsetTransitionRule::of(
            Month::MARCH(), 20, DayOfWeek::SUNDAY(), self::TIME_0100(), false, TimeDefinition::WALL(),
            self::OFFSET_0200(), self::OFFSET_0300(), self::OFFSET_0300());
        $this->assertEquals($a->equals($a), true);
        $this->assertEquals($a->equals($b), false);
        $this->assertEquals($b->equals($a), false);
        $this->assertEquals($b->equals($b), true);
    }


    public function test_equals_offsetAfterDifferent()
    {
        $a = ZoneOffsetTransitionRule::of(
            Month::MARCH(), 20, DayOfWeek::SUNDAY(), self::TIME_0100(), false, TimeDefinition::WALL(),
            self::OFFSET_0200(), self::OFFSET_0200(), self::OFFSET_0300());
        $b = ZoneOffsetTransitionRule::of(
            Month::MARCH(), 20, DayOfWeek::SUNDAY(), self::TIME_0100(), false, TimeDefinition::WALL(),
            self::OFFSET_0200(), self::OFFSET_0200(), self::OFFSET_0200());
        $this->assertEquals($a->equals($a), true);
        $this->assertEquals($a->equals($b), false);
        $this->assertEquals($b->equals($a), false);
        $this->assertEquals($b->equals($b), true);
    }


    public function test_equals_string_false()
    {
        $a = ZoneOffsetTransitionRule::of(
            Month::MARCH(), 20, DayOfWeek::SUNDAY(), self::TIME_0100(), false, TimeDefinition::WALL(),
            self::OFFSET_0200(), self::OFFSET_0200(), self::OFFSET_0300());
        $this->assertEquals($a->equals("TZDB"), false);
    }


    public function test_equals_null_false()
    {
        $a = ZoneOffsetTransitionRule::of(
            Month::MARCH(), 20, DayOfWeek::SUNDAY(), self::TIME_0100(), false, TimeDefinition::WALL(),
            self::OFFSET_0200(), self::OFFSET_0200(), self::OFFSET_0300());
        $this->assertEquals($a->equals(null), false);
    }

    //-----------------------------------------------------------------------
    // hashCode()
    //-----------------------------------------------------------------------

    public function test_hashCode_floatingWeek_gap_notEndOfDay()
    {
        $a = ZoneOffsetTransitionRule::of(
            Month::MARCH(), 20, DayOfWeek::SUNDAY(), self::TIME_0100(), false, TimeDefinition::WALL(),
            self::OFFSET_0200(), self::OFFSET_0200(), self::OFFSET_0300());
        $b = ZoneOffsetTransitionRule::of(
            Month::MARCH(), 20, DayOfWeek::SUNDAY(), self::TIME_0100(), false, TimeDefinition::WALL(),
            self::OFFSET_0200(), self::OFFSET_0200(), self::OFFSET_0300());
        $this->assertTrue($a->equals($b));
    }


    public function test_hashCode_floatingWeek_overlap_endOfDay_nullDayOfWeek()
    {
        $a = ZoneOffsetTransitionRule::of(
            Month::OCTOBER(), 20, null, LocalTime::MIDNIGHT(), true, TimeDefinition::WALL(),
            self::OFFSET_0200(), self::OFFSET_0300(), self::OFFSET_0200());
        $b = ZoneOffsetTransitionRule::of(
            Month::OCTOBER(), 20, null, LocalTime::MIDNIGHT(), true, TimeDefinition::WALL(),
            self::OFFSET_0200(), self::OFFSET_0300(), self::OFFSET_0200());
        $this->assertTrue($a->equals($b));
    }


    public function test_hashCode_floatingWeekBackwards()
    {
        $a = ZoneOffsetTransitionRule::of(
            Month::MARCH(), -1, DayOfWeek::SUNDAY(), self::TIME_0100(), false, TimeDefinition::WALL(),
            self::OFFSET_0200(), self::OFFSET_0200(), self::OFFSET_0300());
        $b = ZoneOffsetTransitionRule::of(
            Month::MARCH(), -1, DayOfWeek::SUNDAY(), self::TIME_0100(), false, TimeDefinition::WALL(),
            self::OFFSET_0200(), self::OFFSET_0200(), self::OFFSET_0300());
        $this->assertTrue($a->equals($b));
    }


    public function test_hashCode_fixedDate()
    {
        $a = ZoneOffsetTransitionRule::of(
            Month::MARCH(), 20, null, self::TIME_0100(), false, TimeDefinition::STANDARD(),
            self::OFFSET_0200(), self::OFFSET_0200(), self::OFFSET_0300());
        $b = ZoneOffsetTransitionRule::of(
            Month::MARCH(), 20, null, self::TIME_0100(), false, TimeDefinition::STANDARD(),
            self::OFFSET_0200(), self::OFFSET_0200(), self::OFFSET_0300());
        $this->assertTrue($a->equals($b));
    }

    //-----------------------------------------------------------------------
    // toString()
    //-----------------------------------------------------------------------

    public function test_toString_floatingWeek_gap_notEndOfDay()
    {
        $test = ZoneOffsetTransitionRule::of(
            Month::MARCH(), 20, DayOfWeek::SUNDAY(), self::TIME_0100(), false, TimeDefinition::WALL(),
            self::OFFSET_0200(), self::OFFSET_0200(), self::OFFSET_0300());
        $this->assertEquals($test->__toString(), "TransitionRule[Gap +02:00 to +03:00, SUNDAY on or after MARCH 20 at 01:00 WALL, standard offset +02:00]");
    }


    public function test_toString_floatingWeek_overlap_endOfDay()
    {
        $test = ZoneOffsetTransitionRule::of(
            Month::OCTOBER(), 20, DayOfWeek::SUNDAY(), LocalTime::MIDNIGHT(), true, TimeDefinition::WALL(),
            self::OFFSET_0200(), self::OFFSET_0300(), self::OFFSET_0200());
        $this->assertEquals($test->__toString(), "TransitionRule[Overlap +03:00 to +02:00, SUNDAY on or after OCTOBER 20 at 24:00 WALL, standard offset +02:00]");
    }


    public function test_toString_floatingWeekBackwards_last()
    {
        $test = ZoneOffsetTransitionRule::of(
            Month::MARCH(), -1, DayOfWeek::SUNDAY(), self::TIME_0100(), false, TimeDefinition::WALL(),
            self::OFFSET_0200(), self::OFFSET_0200(), self::OFFSET_0300());
        $this->assertEquals($test->__toString(), "TransitionRule[Gap +02:00 to +03:00, SUNDAY on or before last day of MARCH at 01:00 WALL, standard offset +02:00]");
    }


    public function test_toString_floatingWeekBackwards_secondLast()
    {
        $test = ZoneOffsetTransitionRule::of(
            Month::MARCH(), -2, DayOfWeek::SUNDAY(), self::TIME_0100(), false, TimeDefinition::WALL(),
            self::OFFSET_0200(), self::OFFSET_0200(), self::OFFSET_0300());
        $this->assertEquals($test->__toString(), "TransitionRule[Gap +02:00 to +03:00, SUNDAY on or before last day minus 1 of MARCH at 01:00 WALL, standard offset +02:00]");
    }


    public function test_toString_fixedDate()
    {
        $test = ZoneOffsetTransitionRule::of(
            Month::MARCH(), 20, null, self::TIME_0100(), false, TimeDefinition::STANDARD(),
            self::OFFSET_0200(), self::OFFSET_0200(), self::OFFSET_0300());
        $this->assertEquals($test->__toString(), "TransitionRule[Gap +02:00 to +03:00, MARCH 20 at 01:00 STANDARD, standard offset +02:00]");
    }

}
