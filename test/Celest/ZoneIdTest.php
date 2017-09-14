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

use Celest\Format\TextStyle;
use Celest\Zone\ZoneRules;
use PHPUnit\Framework\TestCase;

/**
 * Test ZoneId.
 */
class ZoneIdTest extends TestCase
{

    private static $OVERLAP = 2;
    private static $GAP = 0;


    //-----------------------------------------------------------------------
    // UTC
    //-----------------------------------------------------------------------
    public function test_constant_UTC()
    {
        $test = ZoneOffset::UTC();
        $this->assertEquals($test->getId(), "Z");
        $this->assertEquals($test->getDisplayName(TextStyle::FULL(), Locale::UK()), "Z");
        $this->assertEquals($test->getRules()->isFixedOffset(), true);
        $this->assertEquals($test->getRules()->getOffset(Instant::ofEpochSecond(0)), ZoneOffset::UTC());
        $this->checkOffset($test->getRules(), $this->createLDT(2008, 6, 30), ZoneOffset::UTC(), 1);
    }

    //-----------------------------------------------------------------------
    // system default
    //-----------------------------------------------------------------------
    public function test_systemDefault()
    {
        $test = ZoneId::systemDefault();
        $this->assertEquals($test->getId(), date_default_timezone_get());
    }

    //-----------------------------------------------------------------------
    // Europe/London
    //-----------------------------------------------------------------------
    public function test_London()
    {
        $test = ZoneId::of("Europe/London");
        $this->assertEquals($test->getId(), "Europe/London");
        $this->assertEquals($test->getRules()->isFixedOffset(), false);
    }

    public function test_London_getOffset()
    {
        $test = ZoneId::of("Europe/London");
        $this->assertEquals($test->getRules()->getOffset($this->createInstant(2008, 1, 1, ZoneOffset::UTC())), ZoneOffset::ofHours(0));
        $this->assertEquals($test->getRules()->getOffset($this->createInstant(2008, 2, 1, ZoneOffset::UTC())), ZoneOffset::ofHours(0));
        $this->assertEquals($test->getRules()->getOffset($this->createInstant(2008, 3, 1, ZoneOffset::UTC())), ZoneOffset::ofHours(0));
        $this->assertEquals($test->getRules()->getOffset($this->createInstant(2008, 4, 1, ZoneOffset::UTC())), ZoneOffset::ofHours(1));
        $this->assertEquals($test->getRules()->getOffset($this->createInstant(2008, 5, 1, ZoneOffset::UTC())), ZoneOffset::ofHours(1));
        $this->assertEquals($test->getRules()->getOffset($this->createInstant(2008, 6, 1, ZoneOffset::UTC())), ZoneOffset::ofHours(1));
        $this->assertEquals($test->getRules()->getOffset($this->createInstant(2008, 7, 1, ZoneOffset::UTC())), ZoneOffset::ofHours(1));
        $this->assertEquals($test->getRules()->getOffset($this->createInstant(2008, 8, 1, ZoneOffset::UTC())), ZoneOffset::ofHours(1));
        $this->assertEquals($test->getRules()->getOffset($this->createInstant(2008, 9, 1, ZoneOffset::UTC())), ZoneOffset::ofHours(1));
        $this->assertEquals($test->getRules()->getOffset($this->createInstant(2008, 10, 1, ZoneOffset::UTC())), ZoneOffset::ofHours(1));
        $this->assertEquals($test->getRules()->getOffset($this->createInstant(2008, 11, 1, ZoneOffset::UTC())), ZoneOffset::ofHours(0));
        $this->assertEquals($test->getRules()->getOffset($this->createInstant(2008, 12, 1, ZoneOffset::UTC())), ZoneOffset::ofHours(0));
    }

    public function test_London_getOffset_toDST()
    {
        $test = ZoneId::of("Europe/London");
        $this->assertEquals($test->getRules()->getOffset($this->createInstant(2008, 3, 24, ZoneOffset::UTC())), ZoneOffset::ofHours(0));
        $this->assertEquals($test->getRules()->getOffset($this->createInstant(2008, 3, 25, ZoneOffset::UTC())), ZoneOffset::ofHours(0));
        $this->assertEquals($test->getRules()->getOffset($this->createInstant(2008, 3, 26, ZoneOffset::UTC())), ZoneOffset::ofHours(0));
        $this->assertEquals($test->getRules()->getOffset($this->createInstant(2008, 3, 27, ZoneOffset::UTC())), ZoneOffset::ofHours(0));
        $this->assertEquals($test->getRules()->getOffset($this->createInstant(2008, 3, 28, ZoneOffset::UTC())), ZoneOffset::ofHours(0));
        $this->assertEquals($test->getRules()->getOffset($this->createInstant(2008, 3, 29, ZoneOffset::UTC())), ZoneOffset::ofHours(0));
        $this->assertEquals($test->getRules()->getOffset($this->createInstant(2008, 3, 30, ZoneOffset::UTC())), ZoneOffset::ofHours(0));
        $this->assertEquals($test->getRules()->getOffset($this->createInstant(2008, 3, 31, ZoneOffset::UTC())), ZoneOffset::ofHours(1));
        // cutover at 01:00Z
        $this->assertEquals($test->getRules()->getOffset($this->createInstant8(2008, 3, 30, 0, 59, 59, 999999999, ZoneOffset::UTC())), ZoneOffset::ofHours(0));
        $this->assertEquals($test->getRules()->getOffset($this->createInstant8(2008, 3, 30, 1, 0, 0, 0, ZoneOffset::UTC())), ZoneOffset::ofHours(1));
    }

    public function test_London_getOffset_fromDST()
    {
        $test = ZoneId::of("Europe/London");
        $this->assertEquals($test->getRules()->getOffset($this->createInstant(2008, 10, 24, ZoneOffset::UTC())), ZoneOffset::ofHours(1));
        $this->assertEquals($test->getRules()->getOffset($this->createInstant(2008, 10, 25, ZoneOffset::UTC())), ZoneOffset::ofHours(1));
        $this->assertEquals($test->getRules()->getOffset($this->createInstant(2008, 10, 26, ZoneOffset::UTC())), ZoneOffset::ofHours(1));
        $this->assertEquals($test->getRules()->getOffset($this->createInstant(2008, 10, 27, ZoneOffset::UTC())), ZoneOffset::ofHours(0));
        $this->assertEquals($test->getRules()->getOffset($this->createInstant(2008, 10, 28, ZoneOffset::UTC())), ZoneOffset::ofHours(0));
        $this->assertEquals($test->getRules()->getOffset($this->createInstant(2008, 10, 29, ZoneOffset::UTC())), ZoneOffset::ofHours(0));
        $this->assertEquals($test->getRules()->getOffset($this->createInstant(2008, 10, 30, ZoneOffset::UTC())), ZoneOffset::ofHours(0));
        $this->assertEquals($test->getRules()->getOffset($this->createInstant(2008, 10, 31, ZoneOffset::UTC())), ZoneOffset::ofHours(0));
        // cutover at 01:00Z
        $this->assertEquals($test->getRules()->getOffset($this->createInstant8(2008, 10, 26, 0, 59, 59, 999999999, ZoneOffset::UTC())), ZoneOffset::ofHours(1));
        $this->assertEquals($test->getRules()->getOffset($this->createInstant8(2008, 10, 26, 1, 0, 0, 0, ZoneOffset::UTC())), ZoneOffset::ofHours(0));
    }

    public function test_London_getOffsetInfo()
    {
        $test = ZoneId::of("Europe/London");
        $this->checkOffset($test->getRules(), $this->createLDT(2008, 1, 1), ZoneOffset::ofHours(0), 1);
        $this->checkOffset($test->getRules(), $this->createLDT(2008, 2, 1), ZoneOffset::ofHours(0), 1);
        $this->checkOffset($test->getRules(), $this->createLDT(2008, 3, 1), ZoneOffset::ofHours(0), 1);
        $this->checkOffset($test->getRules(), $this->createLDT(2008, 4, 1), ZoneOffset::ofHours(1), 1);
        $this->checkOffset($test->getRules(), $this->createLDT(2008, 5, 1), ZoneOffset::ofHours(1), 1);
        $this->checkOffset($test->getRules(), $this->createLDT(2008, 6, 1), ZoneOffset::ofHours(1), 1);
        $this->checkOffset($test->getRules(), $this->createLDT(2008, 7, 1), ZoneOffset::ofHours(1), 1);
        $this->checkOffset($test->getRules(), $this->createLDT(2008, 8, 1), ZoneOffset::ofHours(1), 1);
        $this->checkOffset($test->getRules(), $this->createLDT(2008, 9, 1), ZoneOffset::ofHours(1), 1);
        $this->checkOffset($test->getRules(), $this->createLDT(2008, 10, 1), ZoneOffset::ofHours(1), 1);
        $this->checkOffset($test->getRules(), $this->createLDT(2008, 11, 1), ZoneOffset::ofHours(0), 1);
        $this->checkOffset($test->getRules(), $this->createLDT(2008, 12, 1), ZoneOffset::ofHours(0), 1);
    }

    public function test_London_getOffsetInfo_toDST()
    {
        $test = ZoneId::of("Europe/London");
        $this->checkOffset($test->getRules(), $this->createLDT(2008, 3, 24), ZoneOffset::ofHours(0), 1);
        $this->checkOffset($test->getRules(), $this->createLDT(2008, 3, 25), ZoneOffset::ofHours(0), 1);
        $this->checkOffset($test->getRules(), $this->createLDT(2008, 3, 26), ZoneOffset::ofHours(0), 1);
        $this->checkOffset($test->getRules(), $this->createLDT(2008, 3, 27), ZoneOffset::ofHours(0), 1);
        $this->checkOffset($test->getRules(), $this->createLDT(2008, 3, 28), ZoneOffset::ofHours(0), 1);
        $this->checkOffset($test->getRules(), $this->createLDT(2008, 3, 29), ZoneOffset::ofHours(0), 1);
        $this->checkOffset($test->getRules(), $this->createLDT(2008, 3, 30), ZoneOffset::ofHours(0), 1);
        $this->checkOffset($test->getRules(), $this->createLDT(2008, 3, 31), ZoneOffset::ofHours(1), 1);
        // cutover at 01:00Z
        $this->checkOffset($test->getRules(), LocalDateTime::of(2008, 3, 30, 0, 59, 59, 999999999), ZoneOffset::ofHours(0), 1);
        $this->checkOffset($test->getRules(), LocalDateTime::of(2008, 3, 30, 1, 30, 0, 0), ZoneOffset::ofHours(0), self::$GAP);
        $this->checkOffset($test->getRules(), LocalDateTime::of(2008, 3, 30, 2, 0, 0, 0), ZoneOffset::ofHours(1), 1);
    }

    public function test_London_getOffsetInfo_fromDST()
    {
        $test = ZoneId::of("Europe/London");
        $this->checkOffset($test->getRules(), $this->createLDT(2008, 10, 24), ZoneOffset::ofHours(1), 1);
        $this->checkOffset($test->getRules(), $this->createLDT(2008, 10, 25), ZoneOffset::ofHours(1), 1);
        $this->checkOffset($test->getRules(), $this->createLDT(2008, 10, 26), ZoneOffset::ofHours(1), 1);
        $this->checkOffset($test->getRules(), $this->createLDT(2008, 10, 27), ZoneOffset::ofHours(0), 1);
        $this->checkOffset($test->getRules(), $this->createLDT(2008, 10, 28), ZoneOffset::ofHours(0), 1);
        $this->checkOffset($test->getRules(), $this->createLDT(2008, 10, 29), ZoneOffset::ofHours(0), 1);
        $this->checkOffset($test->getRules(), $this->createLDT(2008, 10, 30), ZoneOffset::ofHours(0), 1);
        $this->checkOffset($test->getRules(), $this->createLDT(2008, 10, 31), ZoneOffset::ofHours(0), 1);
        // cutover at 01:00Z
        $this->checkOffset($test->getRules(), LocalDateTime::of(2008, 10, 26, 0, 59, 59, 999999999), ZoneOffset::ofHours(1), 1);
        $this->checkOffset($test->getRules(), LocalDateTime::of(2008, 10, 26, 1, 30, 0, 0), ZoneOffset::ofHours(1), self::$OVERLAP);
        $this->checkOffset($test->getRules(), LocalDateTime::of(2008, 10, 26, 2, 0, 0, 0), ZoneOffset::ofHours(0), 1);
    }

    public function test_London_getOffsetInfo_gap()
    {
        $test = ZoneId::of("Europe/London");
        $dateTime = LocalDateTime::of(2008, 3, 30, 1, 0, 0, 0);
        $trans = $this->checkOffset($test->getRules(), $dateTime, ZoneOffset::ofHours(0), self::$GAP);
        $this->assertEquals($trans->isGap(), true);
        $this->assertEquals($trans->isOverlap(), false);
        $this->assertEquals($trans->getOffsetBefore(), ZoneOffset::ofHours(0));
        $this->assertEquals($trans->getOffsetAfter(), ZoneOffset::ofHours(1));
        $this->assertEquals($trans->getInstant(), $dateTime->toInstant(ZoneOffset::UTC()));
        $this->assertEquals($trans->getDateTimeBefore(), LocalDateTime::of(2008, 3, 30, 1, 0));
        $this->assertEquals($trans->getDateTimeAfter(), LocalDateTime::of(2008, 3, 30, 2, 0));
        $this->assertEquals($trans->isValidOffset(ZoneOffset::ofHours(-1)), false);
        $this->assertEquals($trans->isValidOffset(ZoneOffset::ofHours(0)), false);
        $this->assertEquals($trans->isValidOffset(ZoneOffset::ofHours(1)), false);
        $this->assertEquals($trans->isValidOffset(ZoneOffset::ofHours(2)), false);
        $this->assertEquals($trans->__toString(), "Transition[Gap at 2008-03-30T01:00Z to +01:00]");

        $this->assertFalse($trans->equals(null));
        $this->assertFalse($trans->equals(ZoneOffset::ofHours(0)));
        $this->assertTrue($trans->equals($trans));

        $otherTrans = $test->getRules()->getTransition($dateTime);
        $this->assertTrue($trans->equals($otherTrans));
    }

    public function test_London_getOffsetInfo_overlap()
    {
        $test = ZoneId::of("Europe/London");
        $dateTime = LocalDateTime::of(2008, 10, 26, 1, 0, 0, 0);
        $trans = $this->checkOffset($test->getRules(), $dateTime, ZoneOffset::ofHours(1), self::$OVERLAP);
        $this->assertEquals($trans->isGap(), false);
        $this->assertEquals($trans->isOverlap(), true);
        $this->assertEquals($trans->getOffsetBefore(), ZoneOffset::ofHours(1));
        $this->assertEquals($trans->getOffsetAfter(), ZoneOffset::ofHours(0));
        $this->assertEquals($trans->getInstant(), $dateTime->toInstant(ZoneOffset::UTC()));
        $this->assertEquals($trans->getDateTimeBefore(), LocalDateTime::of(2008, 10, 26, 2, 0));
        $this->assertEquals($trans->getDateTimeAfter(), LocalDateTime::of(2008, 10, 26, 1, 0));
        $this->assertEquals($trans->isValidOffset(ZoneOffset::ofHours(-1)), false);
        $this->assertEquals($trans->isValidOffset(ZoneOffset::ofHours(0)), true);
        $this->assertEquals($trans->isValidOffset(ZoneOffset::ofHours(1)), true);
        $this->assertEquals($trans->isValidOffset(ZoneOffset::ofHours(2)), false);
        $this->assertEquals($trans->__toString(), "Transition[Overlap at 2008-10-26T02:00+01:00 to Z]");

        $this->assertFalse($trans->equals(null));
        $this->assertFalse($trans->equals(ZoneOffset::ofHours(1)));
        $this->assertTrue($trans->equals($trans));

        $otherTrans = $test->getRules()->getTransition($dateTime);
        $this->assertTrue($trans->equals($otherTrans));
    }

    //-----------------------------------------------------------------------
    // Europe/Paris
    //-----------------------------------------------------------------------
    public function test_Paris()
    {
        $test = ZoneId::of("Europe/Paris");
        $this->assertEquals($test->getId(), "Europe/Paris");
        $this->assertEquals($test->getRules()->isFixedOffset(), false);
    }

    public function test_Paris_getOffset()
    {
        $test = ZoneId::of("Europe/Paris");
        $this->assertEquals($test->getRules()->getOffset($this->createInstant(2008, 1, 1, ZoneOffset::UTC())), ZoneOffset::ofHours(1));
        $this->assertEquals($test->getRules()->getOffset($this->createInstant(2008, 2, 1, ZoneOffset::UTC())), ZoneOffset::ofHours(1));
        $this->assertEquals($test->getRules()->getOffset($this->createInstant(2008, 3, 1, ZoneOffset::UTC())), ZoneOffset::ofHours(1));
        $this->assertEquals($test->getRules()->getOffset($this->createInstant(2008, 4, 1, ZoneOffset::UTC())), ZoneOffset::ofHours(2));
        $this->assertEquals($test->getRules()->getOffset($this->createInstant(2008, 5, 1, ZoneOffset::UTC())), ZoneOffset::ofHours(2));
        $this->assertEquals($test->getRules()->getOffset($this->createInstant(2008, 6, 1, ZoneOffset::UTC())), ZoneOffset::ofHours(2));
        $this->assertEquals($test->getRules()->getOffset($this->createInstant(2008, 7, 1, ZoneOffset::UTC())), ZoneOffset::ofHours(2));
        $this->assertEquals($test->getRules()->getOffset($this->createInstant(2008, 8, 1, ZoneOffset::UTC())), ZoneOffset::ofHours(2));
        $this->assertEquals($test->getRules()->getOffset($this->createInstant(2008, 9, 1, ZoneOffset::UTC())), ZoneOffset::ofHours(2));
        $this->assertEquals($test->getRules()->getOffset($this->createInstant(2008, 10, 1, ZoneOffset::UTC())), ZoneOffset::ofHours(2));
        $this->assertEquals($test->getRules()->getOffset($this->createInstant(2008, 11, 1, ZoneOffset::UTC())), ZoneOffset::ofHours(1));
        $this->assertEquals($test->getRules()->getOffset($this->createInstant(2008, 12, 1, ZoneOffset::UTC())), ZoneOffset::ofHours(1));
    }

    public function test_Paris_getOffset_toDST()
    {
        $test = ZoneId::of("Europe/Paris");
        $this->assertEquals($test->getRules()->getOffset($this->createInstant(2008, 3, 24, ZoneOffset::UTC())), ZoneOffset::ofHours(1));
        $this->assertEquals($test->getRules()->getOffset($this->createInstant(2008, 3, 25, ZoneOffset::UTC())), ZoneOffset::ofHours(1));
        $this->assertEquals($test->getRules()->getOffset($this->createInstant(2008, 3, 26, ZoneOffset::UTC())), ZoneOffset::ofHours(1));
        $this->assertEquals($test->getRules()->getOffset($this->createInstant(2008, 3, 27, ZoneOffset::UTC())), ZoneOffset::ofHours(1));
        $this->assertEquals($test->getRules()->getOffset($this->createInstant(2008, 3, 28, ZoneOffset::UTC())), ZoneOffset::ofHours(1));
        $this->assertEquals($test->getRules()->getOffset($this->createInstant(2008, 3, 29, ZoneOffset::UTC())), ZoneOffset::ofHours(1));
        $this->assertEquals($test->getRules()->getOffset($this->createInstant(2008, 3, 30, ZoneOffset::UTC())), ZoneOffset::ofHours(1));
        $this->assertEquals($test->getRules()->getOffset($this->createInstant(2008, 3, 31, ZoneOffset::UTC())), ZoneOffset::ofHours(2));
        // cutover at 01:00Z
        $this->assertEquals($test->getRules()->getOffset($this->createInstant8(2008, 3, 30, 0, 59, 59, 999999999, ZoneOffset::UTC())), ZoneOffset::ofHours(1));
        $this->assertEquals($test->getRules()->getOffset($this->createInstant8(2008, 3, 30, 1, 0, 0, 0, ZoneOffset::UTC())), ZoneOffset::ofHours(2));
    }

    public function test_Paris_getOffset_fromDST()
    {
        $test = ZoneId::of("Europe/Paris");
        $this->assertEquals($test->getRules()->getOffset($this->createInstant(2008, 10, 24, ZoneOffset::UTC())), ZoneOffset::ofHours(2));
        $this->assertEquals($test->getRules()->getOffset($this->createInstant(2008, 10, 25, ZoneOffset::UTC())), ZoneOffset::ofHours(2));
        $this->assertEquals($test->getRules()->getOffset($this->createInstant(2008, 10, 26, ZoneOffset::UTC())), ZoneOffset::ofHours(2));
        $this->assertEquals($test->getRules()->getOffset($this->createInstant(2008, 10, 27, ZoneOffset::UTC())), ZoneOffset::ofHours(1));
        $this->assertEquals($test->getRules()->getOffset($this->createInstant(2008, 10, 28, ZoneOffset::UTC())), ZoneOffset::ofHours(1));
        $this->assertEquals($test->getRules()->getOffset($this->createInstant(2008, 10, 29, ZoneOffset::UTC())), ZoneOffset::ofHours(1));
        $this->assertEquals($test->getRules()->getOffset($this->createInstant(2008, 10, 30, ZoneOffset::UTC())), ZoneOffset::ofHours(1));
        $this->assertEquals($test->getRules()->getOffset($this->createInstant(2008, 10, 31, ZoneOffset::UTC())), ZoneOffset::ofHours(1));
        // cutover at 01:00Z
        $this->assertEquals($test->getRules()->getOffset($this->createInstant8(2008, 10, 26, 0, 59, 59, 999999999, ZoneOffset::UTC())), ZoneOffset::ofHours(2));
        $this->assertEquals($test->getRules()->getOffset($this->createInstant8(2008, 10, 26, 1, 0, 0, 0, ZoneOffset::UTC())), ZoneOffset::ofHours(1));
    }

    public function test_Paris_getOffsetInfo()
    {
        $test = ZoneId::of("Europe/Paris");
        $this->checkOffset($test->getRules(), $this->createLDT(2008, 1, 1), ZoneOffset::ofHours(1), 1);
        $this->checkOffset($test->getRules(), $this->createLDT(2008, 2, 1), ZoneOffset::ofHours(1), 1);
        $this->checkOffset($test->getRules(), $this->createLDT(2008, 3, 1), ZoneOffset::ofHours(1), 1);
        $this->checkOffset($test->getRules(), $this->createLDT(2008, 4, 1), ZoneOffset::ofHours(2), 1);
        $this->checkOffset($test->getRules(), $this->createLDT(2008, 5, 1), ZoneOffset::ofHours(2), 1);
        $this->checkOffset($test->getRules(), $this->createLDT(2008, 6, 1), ZoneOffset::ofHours(2), 1);
        $this->checkOffset($test->getRules(), $this->createLDT(2008, 7, 1), ZoneOffset::ofHours(2), 1);
        $this->checkOffset($test->getRules(), $this->createLDT(2008, 8, 1), ZoneOffset::ofHours(2), 1);
        $this->checkOffset($test->getRules(), $this->createLDT(2008, 9, 1), ZoneOffset::ofHours(2), 1);
        $this->checkOffset($test->getRules(), $this->createLDT(2008, 10, 1), ZoneOffset::ofHours(2), 1);
        $this->checkOffset($test->getRules(), $this->createLDT(2008, 11, 1), ZoneOffset::ofHours(1), 1);
        $this->checkOffset($test->getRules(), $this->createLDT(2008, 12, 1), ZoneOffset::ofHours(1), 1);
    }

    public function test_Paris_getOffsetInfo_toDST()
    {
        $test = ZoneId::of("Europe/Paris");
        $this->checkOffset($test->getRules(), $this->createLDT(2008, 3, 24), ZoneOffset::ofHours(1), 1);
        $this->checkOffset($test->getRules(), $this->createLDT(2008, 3, 25), ZoneOffset::ofHours(1), 1);
        $this->checkOffset($test->getRules(), $this->createLDT(2008, 3, 26), ZoneOffset::ofHours(1), 1);
        $this->checkOffset($test->getRules(), $this->createLDT(2008, 3, 27), ZoneOffset::ofHours(1), 1);
        $this->checkOffset($test->getRules(), $this->createLDT(2008, 3, 28), ZoneOffset::ofHours(1), 1);
        $this->checkOffset($test->getRules(), $this->createLDT(2008, 3, 29), ZoneOffset::ofHours(1), 1);
        $this->checkOffset($test->getRules(), $this->createLDT(2008, 3, 30), ZoneOffset::ofHours(1), 1);
        $this->checkOffset($test->getRules(), $this->createLDT(2008, 3, 31), ZoneOffset::ofHours(2), 1);
        // cutover at 01:00Z which is 02:00+01:00(local Paris time)
        $this->checkOffset($test->getRules(), LocalDateTime::of(2008, 3, 30, 1, 59, 59, 999999999), ZoneOffset::ofHours(1), 1);
        $this->checkOffset($test->getRules(), LocalDateTime::of(2008, 3, 30, 2, 30, 0, 0), ZoneOffset::ofHours(1), self::$GAP);
        $this->checkOffset($test->getRules(), LocalDateTime::of(2008, 3, 30, 3, 0, 0, 0), ZoneOffset::ofHours(2), 1);
    }

    public function test_Paris_getOffsetInfo_fromDST()
    {
        $test = ZoneId::of("Europe/Paris");
        $this->checkOffset($test->getRules(), $this->createLDT(2008, 10, 24), ZoneOffset::ofHours(2), 1);
        $this->checkOffset($test->getRules(), $this->createLDT(2008, 10, 25), ZoneOffset::ofHours(2), 1);
        $this->checkOffset($test->getRules(), $this->createLDT(2008, 10, 26), ZoneOffset::ofHours(2), 1);
        $this->checkOffset($test->getRules(), $this->createLDT(2008, 10, 27), ZoneOffset::ofHours(1), 1);
        $this->checkOffset($test->getRules(), $this->createLDT(2008, 10, 28), ZoneOffset::ofHours(1), 1);
        $this->checkOffset($test->getRules(), $this->createLDT(2008, 10, 29), ZoneOffset::ofHours(1), 1);
        $this->checkOffset($test->getRules(), $this->createLDT(2008, 10, 30), ZoneOffset::ofHours(1), 1);
        $this->checkOffset($test->getRules(), $this->createLDT(2008, 10, 31), ZoneOffset::ofHours(1), 1);
        // cutover at 01:00Z which is 02:00+01:00(local Paris time)
        $this->checkOffset($test->getRules(), LocalDateTime::of(2008, 10, 26, 1, 59, 59, 999999999), ZoneOffset::ofHours(2), 1);
        $this->checkOffset($test->getRules(), LocalDateTime::of(2008, 10, 26, 2, 30, 0, 0), ZoneOffset::ofHours(2), self::$OVERLAP);
        $this->checkOffset($test->getRules(), LocalDateTime::of(2008, 10, 26, 3, 0, 0, 0), ZoneOffset::ofHours(1), 1);
    }

    public function test_Paris_getOffsetInfo_gap()
    {
        $test = ZoneId::of("Europe/Paris");

        $dateTime = LocalDateTime::of(2008, 3, 30, 2, 0, 0, 0);
        $trans = $this->checkOffset($test->getRules(), $dateTime, ZoneOffset::ofHours(1), self::$GAP);
        $this->assertEquals($trans->isGap(), true);
        $this->assertEquals($trans->isOverlap(), false);
        $this->assertEquals($trans->getOffsetBefore(), ZoneOffset::ofHours(1));
        $this->assertEquals($trans->getOffsetAfter(), ZoneOffset::ofHours(2));
        $this->assertEquals($trans->getInstant(), $this->createInstant8(2008, 3, 30, 1, 0, 0, 0, ZoneOffset::UTC()));
        $this->assertEquals($trans->isValidOffset(ZoneOffset::ofHours(0)), false);
        $this->assertEquals($trans->isValidOffset(ZoneOffset::ofHours(1)), false);
        $this->assertEquals($trans->isValidOffset(ZoneOffset::ofHours(2)), false);
        $this->assertEquals($trans->isValidOffset(ZoneOffset::ofHours(3)), false);
        $this->assertEquals($trans->__toString(), "Transition[Gap at 2008-03-30T02:00+01:00 to +02:00]");

        $this->assertFalse($trans->equals(null));
        $this->assertFalse($trans->equals(ZoneOffset::ofHours(1)));
        $this->assertTrue($trans->equals($trans));

        $otherDis = $test->getRules()->getTransition($dateTime);
        $this->assertTrue($trans->equals($otherDis));
    }

    public function test_Paris_getOffsetInfo_overlap()
    {
        $test = ZoneId::of("Europe/Paris");

        $dateTime = LocalDateTime::of(2008, 10, 26, 2, 0, 0, 0);
        $trans = $this->checkOffset($test->getRules(), $dateTime, ZoneOffset::ofHours(2), self::$OVERLAP);
        $this->assertEquals($trans->isGap(), false);
        $this->assertEquals($trans->isOverlap(), true);
        $this->assertEquals($trans->getOffsetBefore(), ZoneOffset::ofHours(2));
        $this->assertEquals($trans->getOffsetAfter(), ZoneOffset::ofHours(1));
        $this->assertEquals($trans->getInstant(), $this->createInstant8(2008, 10, 26, 1, 0, 0, 0, ZoneOffset::UTC()));
        $this->assertEquals($trans->isValidOffset(ZoneOffset::ofHours(0)), false);
        $this->assertEquals($trans->isValidOffset(ZoneOffset::ofHours(1)), true);
        $this->assertEquals($trans->isValidOffset(ZoneOffset::ofHours(2)), true);
        $this->assertEquals($trans->isValidOffset(ZoneOffset::ofHours(3)), false);
        $this->assertEquals($trans->__toString(), "Transition[Overlap at 2008-10-26T03:00+02:00 to +01:00]");

        $this->assertFalse($trans->equals(null));
        $this->assertFalse($trans->equals(ZoneOffset::ofHours(2)));
        $this->assertTrue($trans->equals($trans));

        $otherDis = $test->getRules()->getTransition($dateTime);
        $this->assertTrue($trans->equals($otherDis));
    }

    //-----------------------------------------------------------------------
    // America/New_York
    //-----------------------------------------------------------------------
    public function test_NewYork()
    {
        $test = ZoneId::of("America/New_York");
        $this->assertEquals($test->getId(), "America/New_York");
        $this->assertEquals($test->getRules()->isFixedOffset(), false);
    }

    public function test_NewYork_getOffset()
    {
        $test = ZoneId::of("America/New_York");
        $offset = ZoneOffset::ofHours(-5);
        $this->assertEquals($test->getRules()->getOffset($this->createInstant(2008, 1, 1, $offset)), ZoneOffset::ofHours(-5));
        $this->assertEquals($test->getRules()->getOffset($this->createInstant(2008, 2, 1, $offset)), ZoneOffset::ofHours(-5));
        $this->assertEquals($test->getRules()->getOffset($this->createInstant(2008, 3, 1, $offset)), ZoneOffset::ofHours(-5));
        $this->assertEquals($test->getRules()->getOffset($this->createInstant(2008, 4, 1, $offset)), ZoneOffset::ofHours(-4));
        $this->assertEquals($test->getRules()->getOffset($this->createInstant(2008, 5, 1, $offset)), ZoneOffset::ofHours(-4));
        $this->assertEquals($test->getRules()->getOffset($this->createInstant(2008, 6, 1, $offset)), ZoneOffset::ofHours(-4));
        $this->assertEquals($test->getRules()->getOffset($this->createInstant(2008, 7, 1, $offset)), ZoneOffset::ofHours(-4));
        $this->assertEquals($test->getRules()->getOffset($this->createInstant(2008, 8, 1, $offset)), ZoneOffset::ofHours(-4));
        $this->assertEquals($test->getRules()->getOffset($this->createInstant(2008, 9, 1, $offset)), ZoneOffset::ofHours(-4));
        $this->assertEquals($test->getRules()->getOffset($this->createInstant(2008, 10, 1, $offset)), ZoneOffset::ofHours(-4));
        $this->assertEquals($test->getRules()->getOffset($this->createInstant(2008, 11, 1, $offset)), ZoneOffset::ofHours(-4));
        $this->assertEquals($test->getRules()->getOffset($this->createInstant(2008, 12, 1, $offset)), ZoneOffset::ofHours(-5));
        $this->assertEquals($test->getRules()->getOffset($this->createInstant(2008, 1, 28, $offset)), ZoneOffset::ofHours(-5));
        $this->assertEquals($test->getRules()->getOffset($this->createInstant(2008, 2, 28, $offset)), ZoneOffset::ofHours(-5));
        $this->assertEquals($test->getRules()->getOffset($this->createInstant(2008, 3, 28, $offset)), ZoneOffset::ofHours(-4));
        $this->assertEquals($test->getRules()->getOffset($this->createInstant(2008, 4, 28, $offset)), ZoneOffset::ofHours(-4));
        $this->assertEquals($test->getRules()->getOffset($this->createInstant(2008, 5, 28, $offset)), ZoneOffset::ofHours(-4));
        $this->assertEquals($test->getRules()->getOffset($this->createInstant(2008, 6, 28, $offset)), ZoneOffset::ofHours(-4));
        $this->assertEquals($test->getRules()->getOffset($this->createInstant(2008, 7, 28, $offset)), ZoneOffset::ofHours(-4));
        $this->assertEquals($test->getRules()->getOffset($this->createInstant(2008, 8, 28, $offset)), ZoneOffset::ofHours(-4));
        $this->assertEquals($test->getRules()->getOffset($this->createInstant(2008, 9, 28, $offset)), ZoneOffset::ofHours(-4));
        $this->assertEquals($test->getRules()->getOffset($this->createInstant(2008, 10, 28, $offset)), ZoneOffset::ofHours(-4));
        $this->assertEquals($test->getRules()->getOffset($this->createInstant(2008, 11, 28, $offset)), ZoneOffset::ofHours(-5));
        $this->assertEquals($test->getRules()->getOffset($this->createInstant(2008, 12, 28, $offset)), ZoneOffset::ofHours(-5));
    }

    public function test_NewYork_getOffset_toDST()
    {
        $test = ZoneId::of("America/New_York");
        $offset = ZoneOffset::ofHours(-5);
        $this->assertEquals($test->getRules()->getOffset($this->createInstant(2008, 3, 8, $offset)), ZoneOffset::ofHours(-5));
        $this->assertEquals($test->getRules()->getOffset($this->createInstant(2008, 3, 9, $offset)), ZoneOffset::ofHours(-5));
        $this->assertEquals($test->getRules()->getOffset($this->createInstant(2008, 3, 10, $offset)), ZoneOffset::ofHours(-4));
        $this->assertEquals($test->getRules()->getOffset($this->createInstant(2008, 3, 11, $offset)), ZoneOffset::ofHours(-4));
        $this->assertEquals($test->getRules()->getOffset($this->createInstant(2008, 3, 12, $offset)), ZoneOffset::ofHours(-4));
        $this->assertEquals($test->getRules()->getOffset($this->createInstant(2008, 3, 13, $offset)), ZoneOffset::ofHours(-4));
        $this->assertEquals($test->getRules()->getOffset($this->createInstant(2008, 3, 14, $offset)), ZoneOffset::ofHours(-4));
        // cutover at 02:00 local
        $this->assertEquals($test->getRules()->getOffset($this->createInstant8(2008, 3, 9, 1, 59, 59, 999999999, $offset)), ZoneOffset::ofHours(-5));
        $this->assertEquals($test->getRules()->getOffset($this->createInstant8(2008, 3, 9, 2, 0, 0, 0, $offset)), ZoneOffset::ofHours(-4));
    }

    public function test_NewYork_getOffset_fromDST()
    {
        $test = ZoneId::of("America/New_York");
        $offset = ZoneOffset::ofHours(-4);
        $this->assertEquals($test->getRules()->getOffset($this->createInstant(2008, 11, 1, $offset)), ZoneOffset::ofHours(-4));
        $this->assertEquals($test->getRules()->getOffset($this->createInstant(2008, 11, 2, $offset)), ZoneOffset::ofHours(-4));
        $this->assertEquals($test->getRules()->getOffset($this->createInstant(2008, 11, 3, $offset)), ZoneOffset::ofHours(-5));
        $this->assertEquals($test->getRules()->getOffset($this->createInstant(2008, 11, 4, $offset)), ZoneOffset::ofHours(-5));
        $this->assertEquals($test->getRules()->getOffset($this->createInstant(2008, 11, 5, $offset)), ZoneOffset::ofHours(-5));
        $this->assertEquals($test->getRules()->getOffset($this->createInstant(2008, 11, 6, $offset)), ZoneOffset::ofHours(-5));
        $this->assertEquals($test->getRules()->getOffset($this->createInstant(2008, 11, 7, $offset)), ZoneOffset::ofHours(-5));
        // cutover at 02:00 local
        $this->assertEquals($test->getRules()->getOffset($this->createInstant8(2008, 11, 2, 1, 59, 59, 999999999, $offset)), ZoneOffset::ofHours(-4));
        $this->assertEquals($test->getRules()->getOffset($this->createInstant8(2008, 11, 2, 2, 0, 0, 0, $offset)), ZoneOffset::ofHours(-5));
    }

    public function test_NewYork_getOffsetInfo()
    {
        $test = ZoneId::of("America/New_York");
        $this->checkOffset($test->getRules(), $this->createLDT(2008, 1, 1), ZoneOffset::ofHours(-5), 1);
        $this->checkOffset($test->getRules(), $this->createLDT(2008, 2, 1), ZoneOffset::ofHours(-5), 1);
        $this->checkOffset($test->getRules(), $this->createLDT(2008, 3, 1), ZoneOffset::ofHours(-5), 1);
        $this->checkOffset($test->getRules(), $this->createLDT(2008, 4, 1), ZoneOffset::ofHours(-4), 1);
        $this->checkOffset($test->getRules(), $this->createLDT(2008, 5, 1), ZoneOffset::ofHours(-4), 1);
        $this->checkOffset($test->getRules(), $this->createLDT(2008, 6, 1), ZoneOffset::ofHours(-4), 1);
        $this->checkOffset($test->getRules(), $this->createLDT(2008, 7, 1), ZoneOffset::ofHours(-4), 1);
        $this->checkOffset($test->getRules(), $this->createLDT(2008, 8, 1), ZoneOffset::ofHours(-4), 1);
        $this->checkOffset($test->getRules(), $this->createLDT(2008, 9, 1), ZoneOffset::ofHours(-4), 1);
        $this->checkOffset($test->getRules(), $this->createLDT(2008, 10, 1), ZoneOffset::ofHours(-4), 1);
        $this->checkOffset($test->getRules(), $this->createLDT(2008, 11, 1), ZoneOffset::ofHours(-4), 1);
        $this->checkOffset($test->getRules(), $this->createLDT(2008, 12, 1), ZoneOffset::ofHours(-5), 1);
        $this->checkOffset($test->getRules(), $this->createLDT(2008, 1, 28), ZoneOffset::ofHours(-5), 1);
        $this->checkOffset($test->getRules(), $this->createLDT(2008, 2, 28), ZoneOffset::ofHours(-5), 1);
        $this->checkOffset($test->getRules(), $this->createLDT(2008, 3, 28), ZoneOffset::ofHours(-4), 1);
        $this->checkOffset($test->getRules(), $this->createLDT(2008, 4, 28), ZoneOffset::ofHours(-4), 1);
        $this->checkOffset($test->getRules(), $this->createLDT(2008, 5, 28), ZoneOffset::ofHours(-4), 1);
        $this->checkOffset($test->getRules(), $this->createLDT(2008, 6, 28), ZoneOffset::ofHours(-4), 1);
        $this->checkOffset($test->getRules(), $this->createLDT(2008, 7, 28), ZoneOffset::ofHours(-4), 1);
        $this->checkOffset($test->getRules(), $this->createLDT(2008, 8, 28), ZoneOffset::ofHours(-4), 1);
        $this->checkOffset($test->getRules(), $this->createLDT(2008, 9, 28), ZoneOffset::ofHours(-4), 1);
        $this->checkOffset($test->getRules(), $this->createLDT(2008, 10, 28), ZoneOffset::ofHours(-4), 1);
        $this->checkOffset($test->getRules(), $this->createLDT(2008, 11, 28), ZoneOffset::ofHours(-5), 1);
        $this->checkOffset($test->getRules(), $this->createLDT(2008, 12, 28), ZoneOffset::ofHours(-5), 1);
    }

    public function test_NewYork_getOffsetInfo_toDST()
    {
        $test = ZoneId::of("America/New_York");
        $this->checkOffset($test->getRules(), $this->createLDT(2008, 3, 8), ZoneOffset::ofHours(-5), 1);
        $this->checkOffset($test->getRules(), $this->createLDT(2008, 3, 9), ZoneOffset::ofHours(-5), 1);
        $this->checkOffset($test->getRules(), $this->createLDT(2008, 3, 10), ZoneOffset::ofHours(-4), 1);
        $this->checkOffset($test->getRules(), $this->createLDT(2008, 3, 11), ZoneOffset::ofHours(-4), 1);
        $this->checkOffset($test->getRules(), $this->createLDT(2008, 3, 12), ZoneOffset::ofHours(-4), 1);
        $this->checkOffset($test->getRules(), $this->createLDT(2008, 3, 13), ZoneOffset::ofHours(-4), 1);
        $this->checkOffset($test->getRules(), $this->createLDT(2008, 3, 14), ZoneOffset::ofHours(-4), 1);
        // cutover at 02:00 local
        $this->checkOffset($test->getRules(), LocalDateTime::of(2008, 3, 9, 1, 59, 59, 999999999), ZoneOffset::ofHours(-5), 1);
        $this->checkOffset($test->getRules(), LocalDateTime::of(2008, 3, 9, 2, 30, 0, 0), ZoneOffset::ofHours(-5), self::$GAP);
        $this->checkOffset($test->getRules(), LocalDateTime::of(2008, 3, 9, 3, 0, 0, 0), ZoneOffset::ofHours(-4), 1);
    }

    public function test_NewYork_getOffsetInfo_fromDST()
    {
        $test = ZoneId::of("America/New_York");
        $this->checkOffset($test->getRules(), $this->createLDT(2008, 11, 1), ZoneOffset::ofHours(-4), 1);
        $this->checkOffset($test->getRules(), $this->createLDT(2008, 11, 2), ZoneOffset::ofHours(-4), 1);
        $this->checkOffset($test->getRules(), $this->createLDT(2008, 11, 3), ZoneOffset::ofHours(-5), 1);
        $this->checkOffset($test->getRules(), $this->createLDT(2008, 11, 4), ZoneOffset::ofHours(-5), 1);
        $this->checkOffset($test->getRules(), $this->createLDT(2008, 11, 5), ZoneOffset::ofHours(-5), 1);
        $this->checkOffset($test->getRules(), $this->createLDT(2008, 11, 6), ZoneOffset::ofHours(-5), 1);
        $this->checkOffset($test->getRules(), $this->createLDT(2008, 11, 7), ZoneOffset::ofHours(-5), 1);
        // cutover at 02:00 local
        $this->checkOffset($test->getRules(), LocalDateTime::of(2008, 11, 2, 0, 59, 59, 999999999), ZoneOffset::ofHours(-4), 1);
        $this->checkOffset($test->getRules(), LocalDateTime::of(2008, 11, 2, 1, 30, 0, 0), ZoneOffset::ofHours(-4), self::$OVERLAP);
        $this->checkOffset($test->getRules(), LocalDateTime::of(2008, 11, 2, 2, 0, 0, 0), ZoneOffset::ofHours(-5), 1);
    }

    public function test_NewYork_getOffsetInfo_gap()
    {
        $test = ZoneId::of("America/New_York");

        $dateTime = LocalDateTime::of(2008, 3, 9, 2, 0, 0, 0);
        $trans = $this->checkOffset($test->getRules(), $dateTime, ZoneOffset::ofHours(-5), self::$GAP);
        $this->assertEquals($trans->getOffsetBefore(), ZoneOffset::ofHours(-5));
        $this->assertEquals($trans->getOffsetAfter(), ZoneOffset::ofHours(-4));
        $this->assertEquals($trans->getInstant(), $this->createInstant8(2008, 3, 9, 2, 0, 0, 0, ZoneOffset::ofHours(-5)));
        $this->assertEquals($trans->isValidOffset(ZoneOffset::ofHours(-6)), false);
        $this->assertEquals($trans->isValidOffset(ZoneOffset::ofHours(-5)), false);
        $this->assertEquals($trans->isValidOffset(ZoneOffset::ofHours(-4)), false);
        $this->assertEquals($trans->isValidOffset(ZoneOffset::ofHours(-3)), false);
        $this->assertEquals($trans->__toString(), "Transition[Gap at 2008-03-09T02:00-05:00 to -04:00]");

        $this->assertFalse($trans->equals(null));
        $this->assertFalse($trans->equals(ZoneOffset::ofHours(-5)));
        $this->assertTrue($trans->equals($trans));

        $otherTrans = $test->getRules()->getTransition($dateTime);
        $this->assertTrue($trans->equals($otherTrans));

    }

    public function test_NewYork_getOffsetInfo_overlap()
    {
        $test = ZoneId::of("America/New_York");

        $dateTime = LocalDateTime::of(2008, 11, 2, 1, 0, 0, 0);
        $trans = $this->checkOffset($test->getRules(), $dateTime, ZoneOffset::ofHours(-4), self::$OVERLAP);
        $this->assertEquals($trans->getOffsetBefore(), ZoneOffset::ofHours(-4));
        $this->assertEquals($trans->getOffsetAfter(), ZoneOffset::ofHours(-5));
        $this->assertEquals($trans->getInstant(), $this->createInstant8(2008, 11, 2, 2, 0, 0, 0, ZoneOffset::ofHours(-4)));
        $this->assertEquals($trans->isValidOffset(ZoneOffset::ofHours(-1)), false);
        $this->assertEquals($trans->isValidOffset(ZoneOffset::ofHours(-5)), true);
        $this->assertEquals($trans->isValidOffset(ZoneOffset::ofHours(-4)), true);
        $this->assertEquals($trans->isValidOffset(ZoneOffset::ofHours(2)), false);
        $this->assertEquals($trans->__toString(), "Transition[Overlap at 2008-11-02T02:00-04:00 to -05:00]");

        $this->assertFalse($trans->equals(null));
        $this->assertFalse($trans->equals(ZoneOffset::ofHours(-4)));
        $this->assertTrue($trans->equals($trans));

        $otherTrans = $test->getRules()->getTransition($dateTime);
        $this->assertTrue($trans->equals($otherTrans));
    }

    //-----------------------------------------------------------------------
    // getXxx() isXxx()
    //-----------------------------------------------------------------------
    public function test_get_Tzdb()
    {
        $test = ZoneId::of("Europe/London");
        $this->assertEquals($test->getId(), "Europe/London");
        $this->assertEquals($test->getRules()->isFixedOffset(), false);
    }

    public function test_get_TzdbFixed()
    {
        $test = ZoneId::of("+01:30");
        $this->assertEquals($test->getId(), "+01:30");
        $this->assertEquals($test->getRules()->isFixedOffset(), true);
    }

    //-----------------------------------------------------------------------
    //-----------------------------------------------------------------------
    //-----------------------------------------------------------------------
    private function createInstant($year, $month, $day, ZoneOffset $offset)
    {
        return LocalDateTime::of($year, $month, $day, 0, 0)->toInstant($offset);
    }

    private function createInstant8($year, $month, $day, $hour, $min, $sec, $nano, ZoneOffset $offset)
    {
        return LocalDateTime::of($year, $month, $day, $hour, $min, $sec, $nano)->toInstant($offset);
    }

    private function createLDT($year, $month, $day)
    {
        return LocalDateTime::of($year, $month, $day, 0, 0);
    }

    private function checkOffset(ZoneRules $rules, LocalDateTime $dateTime, ZoneOffset $offset, $type)
    {
        $validOffsets = $rules->getValidOffsets($dateTime);
        $this->assertEquals(count($validOffsets), $type);
        $this->assertEquals($rules->getOffsetDateTime($dateTime), $offset);
        if ($type === 1) {
            $this->assertEquals($validOffsets[0], $offset);
            return null;
        } else {
            $zot = $rules->getTransition($dateTime);
            $this->assertNotNull($zot);
            $this->assertEquals($zot->isOverlap(), $type == 2);
            $this->assertEquals($zot->isGap(), $type == 0);
            $this->assertEquals($zot->isValidOffset($offset), $type == 2);
            return $zot;
        }
    }
}
