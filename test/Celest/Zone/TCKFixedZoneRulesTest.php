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
use Celest\ZoneOffset;
use PHPUnit_Framework_TestCase;

/**
 * Test ZoneRules for fixed offset time-zones.
 */
class TCKFixedZoneRules extends PHPUnit_Framework_TestCase
{


    private static function OFFSET_PONE()
    {
        return ZoneOffset::ofHours(1);
    }

    private static function OFFSET_PTWO()
    {
        return ZoneOffset::ofHours(2);
    }

    private static function OFFSET_M18()
    {
        return ZoneOffset::ofHours(-18);
    }

    private static function LDT()
    {
        return LocalDateTime::of(2010, 12, 3, 11, 30);
    }

    private static function INSTANT()
    {
        return self::LDT()->toInstant(self::OFFSET_PONE());
    }

    private static function make(ZoneOffset $offset)
    {
        return $offset->getRules();
    }

    public function provider_rules()
    {
        return
            [[
                self::make(self::OFFSET_PONE()), self::OFFSET_PONE()
            ],
                [
                    self::make(self::OFFSET_PTWO()), self::OFFSET_PTWO()],
                [
                    self::make(self::OFFSET_M18()), self::OFFSET_M18()],
            ];
    }

//-----------------------------------------------------------------------
// Basics
//-----------------------------------------------------------------------

    /**
     * @dataProvider provider_rules
     */
    public function test_getOffset_Instant(ZoneRules $test, ZoneOffset $expectedOffset)
    {
        $this->assertEquals($test->getOffset(self::INSTANT()), $expectedOffset);
        $this->assertEquals($test->getOffsetDateTime(null), $expectedOffset);
    }

    /**
     * @dataProvider provider_rules
     */
    public function test_getOffset_LocalDateTime(ZoneRules $test, ZoneOffset $expectedOffset)
    {
        $this->assertEquals($test->getOffsetDateTime(self::LDT()), $expectedOffset);
        $this->assertEquals($test->getOffset(null), $expectedOffset);
    }

    /**
     * @dataProvider provider_rules
     */
    public function test_getValidOffsets_LDT(ZoneRules $test, ZoneOffset $expectedOffset)
    {
        $this->assertEquals(count($test->getValidOffsets(self::LDT())), 1);
        $this->assertEquals($test->getValidOffsets(self::LDT())[0], $expectedOffset);
        $this->assertEquals(count($test->getValidOffsets(null)), 1);
        $this->assertEquals($test->getValidOffsets(null)[0], $expectedOffset);
    }

    /**
     * @dataProvider provider_rules
     */
    public function test_getTransition_LDT(ZoneRules $test, ZoneOffset $expectedOffset)
    {
        $this->assertEquals($test->getTransition(self::LDT()), null);
        $this->assertEquals($test->getTransition(null), null);
    }

    /**
     * @dataProvider provider_rules
     */
    public function test_isValidOffset_LDT_ZO(ZoneRules $test, ZoneOffset $expectedOffset)
    {
        $this->assertEquals($test->isValidOffset(self::LDT(), $expectedOffset), true);
        $this->assertEquals($test->isValidOffset(self::LDT(), ZoneOffset::UTC()), false);
        $this->assertEquals($test->isValidOffset(self::LDT(), null), false);

        $this->assertEquals($test->isValidOffset(null, $expectedOffset), true);
        $this->assertEquals($test->isValidOffset(null, ZoneOffset::UTC()), false);
        $this->assertEquals($test->isValidOffset(null, null), false);
    }

    /**
     * @dataProvider provider_rules
     */
    public function test_getStandardOffset_Instant(ZoneRules $test, ZoneOffset $expectedOffset)
    {
        $this->assertEquals($test->getStandardOffset(self::INSTANT()), $expectedOffset);
        $this->assertEquals($test->getStandardOffset(null), $expectedOffset);
    }

    /**
     * @dataProvider provider_rules
     */
    public function test_getDaylightSavings_Instant(ZoneRules $test, ZoneOffset $expectedOffset)
    {
        $this->assertEquals($test->getDaylightSavings(self::INSTANT()), Duration::ZERO());
        $this->assertEquals($test->getDaylightSavings(null), Duration::ZERO());
    }

    /**
     * @dataProvider provider_rules
     */
    public function test_isDaylightSavings_Instant(ZoneRules $test, ZoneOffset $expectedOffset)
    {
        $this->assertEquals($test->isDaylightSavings(self::INSTANT()), false);
        $this->assertEquals($test->isDaylightSavings(null), false);
    }

    //-------------------------------------------------------------------------
    /**
     * @dataProvider provider_rules
     */
    public function test_nextTransition_Instant(ZoneRules $test, ZoneOffset $expectedOffset)
    {
        $this->assertEquals($test->nextTransition(self::INSTANT()), null);
        $this->assertEquals($test->nextTransition(null), null);
    }

    /**
     * @dataProvider provider_rules
     */
    public function test_previousTransition_Instant(ZoneRules $test, ZoneOffset $expectedOffset)
    {
        $this->assertEquals($test->previousTransition(self::INSTANT()), null);
        $this->assertEquals($test->previousTransition(null), null);
    }

    //-------------------------------------------------------------------------
    /**
     * @dataProvider provider_rules
     */
    public function test_getTransitions(ZoneRules $test, ZoneOffset $expectedOffset)
    {
        $this->assertEquals(count($test->getTransitions()), 0);
    }

    /**
     * @dataProvider provider_rules
     */
    public function test_getTransitionRules(ZoneRules $test, ZoneOffset $expectedOffset)
    {
        $this->assertEquals(count($test->getTransitionRules()), 0);
    }

    //-----------------------------------------------------------------------
    // equals() / hashCode()
    //-----------------------------------------------------------------------
    public function test_equalsHashCode()
    {
        $a = self::make(self::OFFSET_PONE());
        $b = self::make(self::OFFSET_PTWO());

        $this->assertEquals($a->equals($a), true);
        $this->assertEquals($a->equals($b), false);
        $this->assertEquals($b->equals($a), false);
        $this->assertEquals($b->equals($b), true);

        $this->assertEquals($a->equals("Rubbish"), false);
        $this->assertEquals($a->equals(null), false);

        //$this->assertEquals($a->hashCode() == $a->hashCode(), true);
        //$this->assertEquals($b->hashCode() == $b->hashCode(), true);
    }

}
