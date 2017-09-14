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
 * Copyright (c) 2008-2012 Stephen Colebourne & Michael Nascimento Santos
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
use PHPUnit\Framework\TestCase;

class TCKClock_Tick extends TestCase
{

    private static function MOSCOW()
    {
        return ZoneId::of("Europe/Moscow");
    }

    private static function PARIS()
    {
        return ZoneId::of("Europe/Paris");
    }

    private static function ZDT()
    {
        return LocalDateTime::of(2008, 6, 30, 11, 30, 10, 500)->atZone(ZoneOffset::ofHours(2));
    }

    //-----------------------------------------------------------------------
    /**
     * @group long
     */
    public function test_tick_ClockDuration_250millis()
    {
        for ($i = 0; $i < 1000; $i++) {
            $test = Clock::tick(Clock::fixed(self::ZDT()->withNano($i * 1000000)->toInstant(), self::PARIS()), Duration::ofMillis(250));
            $this->assertEquals($test->instant(), self::ZDT()->withNano((\intdiv($i, 250)) * 250000000)->toInstant());
            $this->assertEquals($test->getZone(), self::PARIS());
        }
    }

    /**
     * @group long
     */
    public function test_tick_ClockDuration_250micros()
    {
        for ($i = 0; $i < 1000; $i++) {
            $test = Clock::tick(Clock::fixed(self::ZDT()->withNano($i * 1000)->toInstant(), self::PARIS()), Duration::ofNanos(250000));
            $this->assertEquals($test->instant(), self::ZDT()->withNano((\intdiv($i, 250)) * 250000)->toInstant());
            $this->assertEquals($test->getZone(), self::PARIS());
        }
    }

    /**
     * @group long
     */
    public function test_tick_ClockDuration_20nanos()
    {
        for ($i = 0; $i < 1000; $i++) {
            $test = Clock::tick(Clock::fixed(self::ZDT()->withNano($i)->toInstant(), self::PARIS()), Duration::ofNanos(20));
            $this->assertEquals($test->instant(), self::ZDT()->withNano(\intdiv($i, 20) * 20)->toInstant());
            $this->assertEquals($test->getZone(), self::PARIS());
        }
    }

    public function test_tick_ClockDuration_zeroDuration()
    {
        $underlying = Clock::system(self::PARIS());
        $test = Clock::tick($underlying, Duration::ZERO());
        $this->assertSame($test, $underlying);  // spec says same
    }

    public function test_tick_ClockDuration_1nsDuration()
    {
        $underlying = Clock::system(self::PARIS());
        $test = Clock::tick($underlying, Duration::ofNanos(1));
        $this->assertSame($test, $underlying);  // spec says same
    }

    /**
     * @expectedException \Celest\ArithmeticException
     */
    public function test_tick_ClockDuration_maxDuration()
    {
        Clock::tick(Clock::systemUTC(), Duration::ofSeconds(Long::MAX_VALUE));
    }

    /**
     * @expectedException  \InvalidArgumentException
     */
    public function test_tick_ClockDuration_subMilliNotDivisible_123ns()
    {
        Clock::tick(Clock::systemUTC(), Duration::ofSeconds(0, 123));
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function test_tick_ClockDuration_subMilliNotDivisible_999ns()
    {
        Clock::tick(Clock::systemUTC(), Duration::ofSeconds(0, 999));
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function test_tick_ClockDuration_subMilliNotDivisible_999_999_999ns()
    {
        Clock::tick(Clock::systemUTC(), Duration::ofSeconds(0, 999999999));
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function test_tick_ClockDuration_negative1ns()
    {
        Clock::tick(Clock::systemUTC(), Duration::ofSeconds(0, -1));
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function test_tick_ClockDuration_negative1s()
    {
        Clock::tick(Clock::systemUTC(), Duration::ofSeconds(-1));
    }

    public function test_tick_ClockDuration_nullClock()
    {
        TestHelper::assertNullException($this, function () {
            Clock::tick(null, Duration::ZERO());
        });
    }

    public function test_tick_ClockDuration_nullDuration()
    {
        TestHelper::assertNullException($this, function () {
            Clock::tick(Clock::systemUTC(), null);
        });
    }

    //-----------------------------------------------------------------------
    public function test_tickSeconds_ZoneId()
    {
        $test = Clock::tickSeconds(self::PARIS());
        $this->assertEquals($test->getZone(), self::PARIS());
        $this->assertEquals($test->instant()->getNano(), 0);
        \usleep(100 * 1000);
        $this->assertEquals($test->instant()->getNano(), 0);
    }

    public function test_tickSeconds_ZoneId_nullZoneId()
    {
        TestHelper::assertNullException($this, function () {
            Clock::tickSeconds(null);
        });
    }

    //-----------------------------------------------------------------------
    public function test_tickMinutes_ZoneId()
    {
        $test = Clock::tickMinutes(self::PARIS());
        $this->assertEquals($test->getZone(), self::PARIS());
        $instant = $test->instant();
        $this->assertEquals($instant->getEpochSecond() % 60, 0);
        $this->assertEquals($instant->getNano(), 0);
    }

    public function test_tickMinutes_ZoneId_nullZoneId()
    {
        TestHelper::assertNullException($this, function () {
            Clock::tickMinutes(null);
        });
    }

    //-------------------------------------------------------------------------
    public function test_withZone()
    {
        $test = Clock::tick(Clock::system(self::PARIS()), Duration::ofMillis(500));
        $changed = $test->withZone(self::MOSCOW());
        $this->assertEquals($test->getZone(), self::PARIS());
        $this->assertEquals($changed->getZone(), self::MOSCOW());
    }

    public function test_withZone_equal()
    {
        $test = Clock::tick(Clock::system(self::PARIS()), Duration::ofMillis(500));
        $changed = $test->withZone(self::PARIS());
        $this->assertEquals($test, $changed);
    }

    public function test_withZone_null()
    {
        TestHelper::assertNullException($this, function () {
            Clock::tick(Clock::system(self::PARIS()), Duration::ofMillis(500))->withZone(null);
        });
    }

    //-----------------------------------------------------------------------
    public function test__equals()
    {
        $a = Clock::tick(Clock::system(self::PARIS()), Duration::ofMillis(500));
        $b = Clock::tick(Clock::system(self::PARIS()), Duration::ofMillis(500));
        $this->assertEquals($a->equals($a), true);
        $this->assertEquals($a->equals($b), true);
        $this->assertEquals($b->equals($a), true);
        $this->assertEquals($b->equals($b), true);

        $c = Clock::tick(Clock::system(self::MOSCOW()), Duration::ofMillis(500));
        $this->assertEquals($a->equals($c), false);

        $d = Clock::tick(Clock::system(self::PARIS()), Duration::ofMillis(499));
        $this->assertEquals($a->equals($d), false);

        $this->assertEquals($a->equals(null), false);
        $this->assertEquals($a->equals("other type"), false);
        $this->assertEquals($a->equals(Clock::systemUTC()), false);
    }
}
