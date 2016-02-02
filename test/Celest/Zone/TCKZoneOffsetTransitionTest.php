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

use Celest\Duration;
use Celest\LocalDateTime;
use Celest\Temporal\ChronoUnit;
use Celest\TestHelper;
use Celest\ZoneOffset;
use PHPUnit_Framework_TestCase;

/**
 * Test ZoneOffsetTransition.
 */
class TCKZoneOffsetTransition extends PHPUnit_Framework_TestCase
{

    private static function OFFSET_0100()
    {
        return ZoneOffset::ofHours(1);
    }

    private static function OFFSET_0200()
    {
        return ZoneOffset::ofHours(2);
    }

    private static function OFFSET_0230()
    {
        return ZoneOffset::ofHoursMinutes(2, 30);
    }

    private static function OFFSET_0300()
    {
        return ZoneOffset::ofHours(3);
    }

    private static function OFFSET_0400()
    {
        return ZoneOffset::ofHours(4);
    }

    //-----------------------------------------------------------------------
    // factory
    //-----------------------------------------------------------------------
    public function test_factory_nullTransition()
    {
        TestHelper::assertNullException($this, function () {
            ZoneOffsetTransition::of(null, self::OFFSET_0100(), self::OFFSET_0200());
        });
    }

    public function test_factory_nullOffsetBefore()
    {
        TestHelper::assertNullException($this, function () {
            ZoneOffsetTransition::of(LocalDateTime::ofNumerical(2010, 12, 3, 11, 30), null, self::OFFSET_0200());
        });
    }

    public function test_factory_nullOffsetAfter()
    {
        TestHelper::assertNullException($this, function () {
            ZoneOffsetTransition::of(LocalDateTime::ofNumerical(2010, 12, 3, 11, 30), self::OFFSET_0200(), null);
        });
    }

    /**
     * @expectedException \Celest\IllegalArgumentException
     */
    public function test_factory_sameOffset()
    {
        ZoneOffsetTransition::of(LocalDateTime::ofNumerical(2010, 12, 3, 11, 30), self::OFFSET_0200(), self::OFFSET_0200());
    }

    /**
     * @expectedException \Celest\IllegalArgumentException
     */
    public function test_factory_noNanos()
    {
        ZoneOffsetTransition::of(LocalDateTime::ofNumerical(2010, 12, 3, 11, 30, 0, 500), self::OFFSET_0200(), self::OFFSET_0300());
    }

    //-----------------------------------------------------------------------
    // getters
    //-----------------------------------------------------------------------
    public function test_getters_gap()
    {
        $before = LocalDateTime::ofNumerical(2010, 3, 31, 1, 0);
        $after = LocalDateTime::ofNumerical(2010, 3, 31, 2, 0);
        $test = ZoneOffsetTransition::of($before, self::OFFSET_0200(), self::OFFSET_0300());
        $this->assertEquals($test->isGap(), true);
        $this->assertEquals($test->isOverlap(), false);
        $this->assertEquals($test->getDateTimeBefore(), $before);
        $this->assertEquals($test->getDateTimeAfter(), $after);
        $this->assertEquals($test->getInstant(), $before->toInstant(self::OFFSET_0200()));
        $this->assertEquals($test->getOffsetBefore(), self::OFFSET_0200());
        $this->assertEquals($test->getOffsetAfter(), self::OFFSET_0300());
        $this->assertEquals($test->getDuration(), Duration::of(1, ChronoUnit::HOURS()));
    }

    public
    function test_getters_overlap()
    {
        $before = LocalDateTime::ofNumerical(2010, 10, 31, 1, 0);
        $after = LocalDateTime::ofNumerical(2010, 10, 31, 0, 0);
        $test = ZoneOffsetTransition::of($before, self::OFFSET_0300(), self::OFFSET_0200());
        $this->assertEquals($test->isGap(), false);
        $this->assertEquals($test->isOverlap(), true);
        $this->assertEquals($test->getDateTimeBefore(), $before);
        $this->assertEquals($test->getDateTimeAfter(), $after);
        $this->assertEquals($test->getInstant(), $before->toInstant(self::OFFSET_0300()));
        $this->assertEquals($test->getOffsetBefore(), self::OFFSET_0300());
        $this->assertEquals($test->getOffsetAfter(), self::OFFSET_0200());
        $this->assertEquals($test->getDuration(), Duration::of(-1, ChronoUnit::HOURS()));
    }


    //-----------------------------------------------------------------------
    // isValidOffset()
    //-----------------------------------------------------------------------
    public function test_isValidOffset_gap()
    {
        $ldt = LocalDateTime::ofNumerical(2010, 3, 31, 1, 0);
        $test = ZoneOffsetTransition::of($ldt, self::OFFSET_0200(), self::OFFSET_0300());
        $this->assertEquals($test->isValidOffset(self::OFFSET_0100()), false);
        $this->assertEquals($test->isValidOffset(self::OFFSET_0200()), false);
        $this->assertEquals($test->isValidOffset(self::OFFSET_0230()), false);
        $this->assertEquals($test->isValidOffset(self::OFFSET_0300()), false);
        $this->assertEquals($test->isValidOffset(self::OFFSET_0400()), false);
    }

    public function test_isValidOffset_overlap()
    {
        $ldt = LocalDateTime::ofNumerical(2010, 10, 31, 1, 0);
        $test = ZoneOffsetTransition::of($ldt, self::OFFSET_0300(), self::OFFSET_0200());
        $this->assertEquals($test->isValidOffset(self::OFFSET_0100()), false);
        $this->assertEquals($test->isValidOffset(self::OFFSET_0200()), true);
        $this->assertEquals($test->isValidOffset(self::OFFSET_0230()), false);
        $this->assertEquals($test->isValidOffset(self::OFFSET_0300()), true);
        $this->assertEquals($test->isValidOffset(self::OFFSET_0400()), false);
    }

    //-----------------------------------------------------------------------
    // compareTo()
    //-----------------------------------------------------------------------
    public function test_compareTo()
    {
        $a = ZoneOffsetTransition::of(
            LocalDateTime::ofEpochSecond(23875287 - 1, 0, self::OFFSET_0200()), self::OFFSET_0200(), self::OFFSET_0300());
        $b = ZoneOffsetTransition::of(
            LocalDateTime::ofEpochSecond(23875287, 0, self::OFFSET_0300()), self::OFFSET_0300(), self::OFFSET_0200());
        $c = ZoneOffsetTransition::of(
            LocalDateTime::ofEpochSecond(23875287 + 1, 0, self::OFFSET_0100()), self::OFFSET_0100(), self::OFFSET_0400());

        $this->assertEquals($a->compareTo($a) == 0, true);
        $this->assertEquals($a->compareTo($b) < 0, true);
        $this->assertEquals($a->compareTo($c) < 0, true);

        $this->assertEquals($b->compareTo($a) > 0, true);
        $this->assertEquals($b->compareTo($b) == 0, true);
        $this->assertEquals($b->compareTo($c) < 0, true);

        $this->assertEquals($c->compareTo($a) > 0, true);
        $this->assertEquals($c->compareTo($b) > 0, true);
        $this->assertEquals($c->compareTo($c) == 0, true);
    }

    public function test_compareTo_sameInstant()
    {
        $a = ZoneOffsetTransition::of(
            LocalDateTime::ofEpochSecond(23875287, 0, self::OFFSET_0200()), self::OFFSET_0200(), self::OFFSET_0300());
        $b = ZoneOffsetTransition::of(
            LocalDateTime::ofEpochSecond(23875287, 0, self::OFFSET_0300()), self::OFFSET_0300(), self::OFFSET_0200());
        $c = ZoneOffsetTransition::of(
            LocalDateTime::ofEpochSecond(23875287, 0, self::OFFSET_0100()), self::OFFSET_0100(), self::OFFSET_0400());

        $this->assertEquals($a->compareTo($a) == 0, true);
        $this->assertEquals($a->compareTo($b) == 0, true);
        $this->assertEquals($a->compareTo($c) == 0, true);

        $this->assertEquals($b->compareTo($a) == 0, true);
        $this->assertEquals($b->compareTo($b) == 0, true);
        $this->assertEquals($b->compareTo($c) == 0, true);

        $this->assertEquals($c->compareTo($a) == 0, true);
        $this->assertEquals($c->compareTo($b) == 0, true);
        $this->assertEquals($c->compareTo($c) == 0, true);
    }

    //-----------------------------------------------------------------------
    // equals()
    //-----------------------------------------------------------------------
    public function test_equals()
    {
        $ldtA = LocalDateTime::ofNumerical(2010, 3, 31, 1, 0);
        $a1 = ZoneOffsetTransition::of($ldtA, self::OFFSET_0200(), self::OFFSET_0300());
        $a2 = ZoneOffsetTransition::of($ldtA, self::OFFSET_0200(), self::OFFSET_0300());
        $ldtB = LocalDateTime::ofNumerical(2010, 10, 31, 1, 0);
        $b = ZoneOffsetTransition::of($ldtB, self::OFFSET_0300(), self::OFFSET_0200());

        $this->assertEquals($a1->equals($a1), true);
        $this->assertEquals($a1->equals($a2), true);
        $this->assertEquals($a1->equals($b), false);
        $this->assertEquals($a2->equals($a1), true);
        $this->assertEquals($a2->equals($a2), true);
        $this->assertEquals($a2->equals($b), false);
        $this->assertEquals($b->equals($a1), false);
        $this->assertEquals($b->equals($a2), false);
        $this->assertEquals($b->equals($b), true);

        $this->assertEquals($a1->equals(""), false);
        $this->assertEquals($a1->equals(null), false);
    }

    //-----------------------------------------------------------------------
    // hashCode()
    //-----------------------------------------------------------------------
    public function test_hashCode_floatingWeek_gap_notEndOfDay()
    {
        $ldtA = LocalDateTime::ofNumerical(2010, 3, 31, 1, 0);
        $a1 = ZoneOffsetTransition::of($ldtA, self::OFFSET_0200(), self::OFFSET_0300());
        $a2 = ZoneOffsetTransition::of($ldtA, self::OFFSET_0200(), self::OFFSET_0300());
        $ldtB = LocalDateTime::ofNumerical(2010, 10, 31, 1, 0);
        $b = ZoneOffsetTransition::of($ldtB, self::OFFSET_0300(), self::OFFSET_0200());

        $this->assertTrue($a1->equals($a1));
        $this->assertTrue($a1->equals($a2));
        $this->assertTrue($b->equals($b));
    }

    //-----------------------------------------------------------------------
    // toString()
    //-----------------------------------------------------------------------
    public function test_toString_gap()
    {
        $ldt = LocalDateTime::ofNumerical(2010, 3, 31, 1, 0);
        $test = ZoneOffsetTransition::of($ldt, self::OFFSET_0200(), self::OFFSET_0300());
        $this->assertEquals("Transition[Gap at 2010-03-31T01:00+02:00 to +03:00]", $test->__toString());
    }

    public function test_toString_overlap()
    {
        $ldt = LocalDateTime::ofNumerical(2010, 10, 31, 1, 0);
        $test = ZoneOffsetTransition::of($ldt, self::OFFSET_0300(), self::OFFSET_0200());
        $this->assertEquals("Transition[Overlap at 2010-10-31T01:00+03:00 to +02:00]", $test->__toString());
    }

}
