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

use Celest\Helper\Long;
use Celest\Helper\Math;
use Celest\Temporal\ChronoUnit as CU;
use Celest\Temporal\Temporal;
use Celest\Temporal\TemporalAmount;
use Celest\Temporal\TemporalUnit;

class TemporalAmount_DaysNanos implements TemporalAmount
{
    public function get(TemporalUnit $unit)
    {
        if ($unit == CU::DAYS()) {
            return 23;
        } else {
            return 45;
        }
    }

    public function getUnits()
    {
        return [
            CU::DAYS(),
            CU::NANOS(),
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

class TemporalAmount_Minutes_tooBig implements TemporalAmount
{
    public function get(TemporalUnit $unit)
    {
        return Math::div(Long::MAX_VALUE, 60) + 2;
    }

    public function getUnits()
    {
        return [CU::MINUTES()];
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

class TCKDurationTest extends \PHPUnit_Framework_TestCase
{

    const CYCLE_SECS = 146097 * 86400;

    //-----------------------------------------------------------------------
    // constants
    //-----------------------------------------------------------------------
    public function test_zero()
    {
        $this->assertEquals(Duration::ZERO()->getSeconds(), 0);
        $this->assertEquals(Duration::ZERO()->getNano(), 0);
    }

    //-----------------------------------------------------------------------
    // ofSeconds(long)
    //-----------------------------------------------------------------------

    public function test_factory_seconds_long()
    {
        for ($i = -2; $i <= 2; $i++) {
            $t = Duration::ofSeconds($i);
            $this->assertEquals($t->getSeconds(), $i);
            $this->assertEquals($t->getNano(), 0);
        }
    }

    //-----------------------------------------------------------------------
    // ofSeconds(long,long)
    //-----------------------------------------------------------------------

    public function test_factory_seconds_long_long()
    {
        for ($i = -2; $i <= 2; $i++) {
            for ($j = 0; $j < 10; $j++) {
                $t = Duration::ofSeconds($i, $j);
                $this->assertEquals($t->getSeconds(), $i);
                $this->assertEquals($t->getNano(), $j);
            }
            for ($j = -10; $j < 0; $j++) {
                $t = Duration::ofSeconds($i, $j);
                $this->assertEquals($t->getSeconds(), $i - 1);
                $this->assertEquals($t->getNano(), $j + 1000000000);
            }
            for ($j = 999999990; $j < 1000000000; $j++) {
                $t = Duration::ofSeconds($i, $j);
                $this->assertEquals($t->getSeconds(), $i);
                $this->assertEquals($t->getNano(), $j);
            }
        }
    }


    public function test_factory_seconds_long_long_nanosNegativeAdjusted()
    {
        $test = Duration::ofSeconds(2, -1);
        $this->assertEquals($test->getSeconds(), 1);
        $this->assertEquals($test->getNano(), 999999999);
    }

    /**
     * @expectedException \Celest\ArithmeticException
     */
    public function test_factory_seconds_long_long_tooBig()
    {
        Duration::ofSeconds(Long::MAX_VALUE, 1000000000);
    }

    //-----------------------------------------------------------------------
    // ofMillis(long)
    //-----------------------------------------------------------------------
    function provider_factory_millis_long()
    {
        return [
            [0, 0, 0],
            [1, 0, 1000000],
            [2, 0, 2000000],
            [999, 0, 999000000],
            [1000, 1, 0],
            [1001, 1, 1000000],
            [-1, -1, 999000000],
            [-2, -1, 998000000],
            [-999, -1, 1000000],
            [-1000, -1, 0],
            [-1001, -2, 999000000],
        ];
    }

    /**
     * @dataProvider provider_factory_millis_long
     */
    public function test_factory_millis_long($millis, $expectedSeconds, $expectedNanoOfSecond)
    {
        $test = Duration::ofMillis($millis);
        $this->assertEquals($test->getSeconds(), $expectedSeconds);
        $this->assertEquals($test->getNano(), $expectedNanoOfSecond);
    }

    //-----------------------------------------------------------------------
    // ofNanos(long)
    //-----------------------------------------------------------------------

    public function test_factory_nanos_nanos()
    {
        $test = Duration::ofNanos(1);
        $this->assertEquals($test->getSeconds(), 0);
        $this->assertEquals($test->getNano(), 1);
    }


    public function test_factory_nanos_nanosSecs()
    {
        $test = Duration::ofNanos(1000000002);
        $this->assertEquals($test->getSeconds(), 1);
        $this->assertEquals($test->getNano(), 2);
    }


    public function test_factory_nanos_negative()
    {
        $test = Duration::ofNanos(-2000000001);
        $this->assertEquals($test->getSeconds(), -3);
        $this->assertEquals($test->getNano(), 999999999);
    }


    public function test_factory_nanos_max()
    {
        $test = Duration::ofNanos(Long::MAX_VALUE);
        $this->assertEquals($test->getSeconds(), Math::div(Long::MAX_VALUE, 1000000000));
        $this->assertEquals($test->getNano(), Long::MAX_VALUE % 1000000000);
    }


    public function test_factory_nanos_min()
    {
        $test = Duration::ofNanos(Long::MIN_VALUE);
        $this->assertEquals($test->getSeconds(), Math::div(Long::MIN_VALUE, 1000000000) - 1);
        $this->assertEquals($test->getNano(), Long::MIN_VALUE % 1000000000 + 1000000000);
    }

    //-----------------------------------------------------------------------
    // ofMinutes()
    //-----------------------------------------------------------------------

    public function test_factory_minutes()
    {
        $test = Duration::ofMinutes(2);
        $this->assertEquals($test->getSeconds(), 120);
        $this->assertEquals($test->getNano(), 0);
    }


    public function test_factory_minutes_max()
    {
        $test = Duration::ofMinutes(Math::div(Long::MAX_VALUE, 60));
        $this->assertEquals($test->getSeconds(), (Math::div(Long::MAX_VALUE, 60)) * 60);
        $this->assertEquals($test->getNano(), 0);
    }


    public function test_factory_minutes_min()
    {
        $test = Duration::ofMinutes(Math::div(Long::MIN_VALUE, 60));
        $this->assertEquals($test->getSeconds(), (Math::div(Long::MIN_VALUE, 60)) * 60);
        $this->assertEquals($test->getNano(), 0);
    }

    /**
     * @expectedException \Celest\ArithmeticException
     */

    public function test_factory_minutes_tooBig()
    {
        Duration::ofMinutes(Math::div(Long::MAX_VALUE, 60) + 1);
    }

    /**
     * @expectedException \Celest\ArithmeticException
     */

    public function test_factory_minutes_tooSmall()
    {
        Duration::ofMinutes(Math::div(Long::MIN_VALUE, 60) - 1);
    }

    //-----------------------------------------------------------------------
    // ofHours()
    //-----------------------------------------------------------------------

    public function test_factory_hours()
    {
        $test = Duration::ofHours(2);
        $this->assertEquals($test->getSeconds(), 2 * 3600);
        $this->assertEquals($test->getNano(), 0);
    }


    public function test_factory_hours_max()
    {
        $test = Duration::ofHours(Math::div(Long::MAX_VALUE, 3600));
        $this->assertEquals($test->getSeconds(), (Math::div(Long::MAX_VALUE, 3600)) * 3600);
        $this->assertEquals($test->getNano(), 0);
    }


    public function test_factory_hours_min()
    {
        $test = Duration::ofHours(Math::div(Long::MIN_VALUE, 3600));
        $this->assertEquals($test->getSeconds(), (Math::div(Long::MIN_VALUE, 3600)) * 3600);
        $this->assertEquals($test->getNano(), 0);
    }

    /**
     * @expectedException \Celest\ArithmeticException
     */
    public function test_factory_hours_tooBig()
    {
        Duration::ofHours(Math::div(Long::MAX_VALUE, 3600) + 1);
    }

    /**
     * @expectedException \Celest\ArithmeticException
     */
    public function test_factory_hours_tooSmall()
    {
        Duration::ofHours(Math::div(Long::MIN_VALUE, 3600) - 1);
    }

    //-----------------------------------------------------------------------
    // ofDays()
    //-----------------------------------------------------------------------

    public function test_factory_days()
    {
        $test = Duration::ofDays(2);
        $this->assertEquals($test->getSeconds(), 2 * 86400);
        $this->assertEquals($test->getNano(), 0);
    }


    public function test_factory_days_max()
    {
        $test = Duration::ofDays(Math::div(Long::MAX_VALUE, 86400));
        $this->assertEquals($test->getSeconds(), (Math::div(Long::MAX_VALUE, 86400)) * 86400);
        $this->assertEquals($test->getNano(), 0);
    }


    public function test_factory_days_min()
    {
        $test = Duration::ofDays(Math::div(Long::MIN_VALUE, 86400));
        $this->assertEquals($test->getSeconds(), (Math::div(Long::MIN_VALUE, 86400)) * 86400);
        $this->assertEquals($test->getNano(), 0);
    }

    /**
     * @expectedException \Celest\ArithmeticException
     */
    public function test_factory_days_tooBig()
    {
        Duration::ofDays(Math::div(Long::MAX_VALUE, 86400) + 1);
    }

    /**
     * @expectedException \Celest\ArithmeticException
     */
    public function test_factory_days_tooSmall()
    {
        Duration::ofDays(Math::div(Long::MIN_VALUE, 86400) - 1);
    }

    //-----------------------------------------------------------------------
    // of(long,TemporalUnit)
    //-----------------------------------------------------------------------
    function provider_factory_of_longTemporalUnit()
    {
        return [
            [0, CU::NANOS(), 0, 0],
            [0, CU::MICROS(), 0, 0],
            [0, CU::MILLIS(), 0, 0],
            [0, CU::SECONDS(), 0, 0],
            [0, CU::MINUTES(), 0, 0],
            [0, CU::HOURS(), 0, 0],
            [0, CU::HALF_DAYS(), 0, 0],
            [0, CU::DAYS(), 0, 0],
            [1, CU::NANOS(), 0, 1],
            [1, CU::MICROS(), 0, 1000],
            [1, CU::MILLIS(), 0, 1000000],
            [1, CU::SECONDS(), 1, 0],
            [1, CU::MINUTES(), 60, 0],
            [1, CU::HOURS(), 3600, 0],
            [1, CU::HALF_DAYS(), 43200, 0],
            [1, CU::DAYS(), 86400, 0],
            [3, CU::NANOS(), 0, 3],
            [3, CU::MICROS(), 0, 3000],
            [3, CU::MILLIS(), 0, 3000000],
            [3, CU::SECONDS(), 3, 0],
            [3, CU::MINUTES(), 3 * 60, 0],
            [3, CU::HOURS(), 3 * 3600, 0],
            [3, CU::HALF_DAYS(), 3 * 43200, 0],
            [3, CU::DAYS(), 3 * 86400, 0],
            [-1, CU::NANOS(), -1, 999999999],
            [-1, CU::MICROS(), -1, 999999000],
            [-1, CU::MILLIS(), -1, 999000000],
            [-1, CU::SECONDS(), -1, 0],
            [-1, CU::MINUTES(), -60, 0],
            [-1, CU::HOURS(), -3600, 0],
            [-1, CU::HALF_DAYS(), -43200, 0],
            [-1, CU::DAYS(), -86400, 0],
            [-3, CU::NANOS(), -1, 999999997],
            [-3, CU::MICROS(), -1, 999997000],
            [-3, CU::MILLIS(), -1, 997000000],
            [-3, CU::SECONDS(), -3, 0],
            [-3, CU::MINUTES(), -3 * 60, 0],
            [-3, CU::HOURS(), -3 * 3600, 0],
            [-3, CU::HALF_DAYS(), -3 * 43200, 0],
            [-3, CU::DAYS(), -3 * 86400, 0],
            [Long::MAX_VALUE, CU::NANOS(), Math::div(Long::MAX_VALUE, 1000000000), (int)(Long::MAX_VALUE % 1000000000)],
            [Long::MIN_VALUE, CU::NANOS(), Math::div(Long::MIN_VALUE, 1000000000) - 1, (int)(Long::MIN_VALUE % 1000000000 + 1000000000)],
            [Long::MAX_VALUE, CU::MICROS(), Math::div(Long::MAX_VALUE, 1000000), (int)((Long::MAX_VALUE % 1000000) * 1000)],
            [Long::MIN_VALUE, CU::MICROS(), Math::div(Long::MIN_VALUE, 1000000) - 1, (int)((Long::MIN_VALUE % 1000000 + 1000000) * 1000)],
            [Long::MAX_VALUE, CU::MILLIS(), Math::div(Long::MAX_VALUE, 1000), (int)((Long::MAX_VALUE % 1000) * 1000000)],
            [Long::MIN_VALUE, CU::MILLIS(), Math::div(Long::MIN_VALUE, 1000) - 1, (int)((Long::MIN_VALUE % 1000 + 1000) * 1000000)],
            [Long::MAX_VALUE, CU::SECONDS(), Long::MAX_VALUE, 0],
            [Long::MIN_VALUE, CU::SECONDS(), Long::MIN_VALUE, 0],
            [Math::div(Long::MAX_VALUE, 60), CU::MINUTES(), Math::div(Long::MAX_VALUE, 60) * 60, 0],
            [Math::div(Long::MIN_VALUE, 60), CU::MINUTES(), Math::div(Long::MIN_VALUE, 60) * 60, 0],
            [Math::div(Long::MAX_VALUE, 3600), CU::HOURS(), Math::div(Long::MAX_VALUE, 3600) * 3600, 0],
            [Math::div(Long::MIN_VALUE, 3600), CU::HOURS(), Math::div(Long::MIN_VALUE, 3600) * 3600, 0],
            [Math::div(Long::MAX_VALUE, 43200), CU::HALF_DAYS(), Math::div(Long::MAX_VALUE, 43200) * 43200, 0],
            [Math::div(Long::MIN_VALUE, 43200), CU::HALF_DAYS(), Math::div(Long::MIN_VALUE, 43200) * 43200, 0],
        ];
    }

    /**
     * @dataProvider provider_factory_of_longTemporalUnit
     */
    public function test_factory_of_longTemporalUnit($amount, TemporalUnit $unit, $expectedSeconds, $expectedNanoOfSecond)
    {
        $t = Duration::of($amount, $unit);
        $this->assertEquals($t->getSeconds(), $expectedSeconds);
        $this->assertEquals($t->getNano(), $expectedNanoOfSecond);
    }

    function provider_factory_of_longTemporalUnit_outOfRange()
    {
        return [
            [Math::div(Long::MAX_VALUE, 60) + 1, CU::MINUTES()],
            [Math::div(Long::MIN_VALUE, 60) - 1, CU::MINUTES()],
            [Math::div(Long::MAX_VALUE, 3600) + 1, CU::HOURS()],
            [Math::div(Long::MIN_VALUE, 3600) - 1, CU::HOURS()],
            [Math::div(Long::MAX_VALUE, 43200) + 1, CU::HALF_DAYS()],
            [Math::div(Long::MIN_VALUE, 43200) - 1, CU::HALF_DAYS()],
        ];
    }

    /**
     * @dataProvider provider_factory_of_longTemporalUnit_outOfRange
     * @expectedException \Celest\ArithmeticException
     */
    public function test_factory_of_longTemporalUnit_outOfRange($amount, TemporalUnit $unit)
    {
        Duration::of($amount, $unit);
    }

    /**
     * @expectedException \Celest\DateTimeException
     */
    public function test_factory_of_longTemporalUnit_estimatedUnit()
    {
        Duration::of(2, CU::WEEKS());
    }

    public function test_factory_of_longTemporalUnit_null()
    {
        TestHelper::assertNullException($this, function () {
            Duration::of(1, null);
        });
    }

    //-----------------------------------------------------------------------
    // from(TemporalAmount)
    //-----------------------------------------------------------------------

    public function test_factory_from_TemporalAmount_Duration()
    {
        $amount = Duration::ofHours(3);
        $this->assertEquals(Duration::from($amount), Duration::ofHours(3));
    }


    public function test_factory_from_TemporalAmount_DaysNanos()
    {
        $amount = new TemporalAmount_DaysNanos();
        $t = Duration::from($amount);
        $this->assertEquals($t->getSeconds(), 23 * 86400);
        $this->assertEquals($t->getNano(), 45);
    }

    /**
     * @expectedException \Celest\ArithmeticException
     */
    public function test_factory_from_TemporalAmount_Minutes_tooBig()
    {
        $amount = new TemporalAmount_Minutes_tooBig();
        Duration::from($amount);
    }

    /**
     * @expectedException \Celest\DateTimeException
     */
    public function test_factory_from_TemporalAmount_Period()
    {
        Duration::from(Period::ZERO());
    }

    public function test_factory_from_TemporalAmount_null()
    {
        TestHelper::assertNullException($this, function () {
            Duration::from(null);
        });
    }

    //-----------------------------------------------------------------------
    // parse(String)
    //-----------------------------------------------------------------------
    function data_parseSuccess()
    {
        return [
            ["PT0S", 0, 0],
            ["PT1S", 1, 0],
            ["PT12S", 12, 0],
            ["PT123456789S", 123456789, 0],
            ["PT" . Long::MAX_VALUE . "S", Long::MAX_VALUE, 0],

            ["PT+1S", 1, 0],
            ["PT+12S", 12, 0],
            ["PT+123456789S", 123456789, 0],
            ["PT+" . Long::MAX_VALUE . "S", Long::MAX_VALUE, 0],

            ["PT-1S", -1, 0],
            ["PT-12S", -12, 0],
            ["PT-123456789S", -123456789, 0],
            ["PT" . Long::MIN_VALUE . "S", Long::MIN_VALUE, 0],

            ["PT1.1S", 1, 100000000],
            ["PT1.12S", 1, 120000000],
            ["PT1.123S", 1, 123000000],
            ["PT1.1234S", 1, 123400000],
            ["PT1.12345S", 1, 123450000],
            ["PT1.123456S", 1, 123456000],
            ["PT1.1234567S", 1, 123456700],
            ["PT1.12345678S", 1, 123456780],
            ["PT1.123456789S", 1, 123456789],

            ["PT-1.1S", -2, 1000000000 - 100000000],
            ["PT-1.12S", -2, 1000000000 - 120000000],
            ["PT-1.123S", -2, 1000000000 - 123000000],
            ["PT-1.1234S", -2, 1000000000 - 123400000],
            ["PT-1.12345S", -2, 1000000000 - 123450000],
            ["PT-1.123456S", -2, 1000000000 - 123456000],
            ["PT-1.1234567S", -2, 1000000000 - 123456700],
            ["PT-1.12345678S", -2, 1000000000 - 123456780],
            ["PT-1.123456789S", -2, 1000000000 - 123456789],

            ["PT" . Long::MAX_VALUE . ".123456789S", Long::MAX_VALUE, 123456789],
            ["PT" . Long::MIN_VALUE . ".000000000S", Long::MIN_VALUE, 0],

            /*["PT01S", 1, 0], TODO fix parsing of zeroprefixed durations
            ["PT001S", 1, 0],
            ["PT000S", 0, 0],
            ["PT+01S", 1, 0],
            ["PT-01S", -1, 0],*/

            ["PT1.S", 1, 0],
            ["PT+1.S", 1, 0],
            ["PT-1.S", -1, 0],

            ["P0D", 0, 0],
            ["P0DT0H", 0, 0],
            ["P0DT0M", 0, 0],
            ["P0DT0S", 0, 0],
            ["P0DT0H0S", 0, 0],
            ["P0DT0M0S", 0, 0],
            ["P0DT0H0M0S", 0, 0],

            ["P1D", 86400, 0],
            ["P1DT0H", 86400, 0],
            ["P1DT0M", 86400, 0],
            ["P1DT0S", 86400, 0],
            ["P1DT0H0S", 86400, 0],
            ["P1DT0M0S", 86400, 0],
            ["P1DT0H0M0S", 86400, 0],

            ["P3D", 86400 * 3, 0],
            ["P3DT2H", 86400 * 3 + 3600 * 2, 0],
            ["P3DT2M", 86400 * 3 + 60 * 2, 0],
            ["P3DT2S", 86400 * 3 + 2, 0],
            ["P3DT2H1S", 86400 * 3 + 3600 * 2 + 1, 0],
            ["P3DT2M1S", 86400 * 3 + 60 * 2 + 1, 0],
            ["P3DT2H1M1S", 86400 * 3 + 3600 * 2 + 60 + 1, 0],

            ["P-3D", -86400 * 3, 0],
            ["P-3DT2H", -86400 * 3 + 3600 * 2, 0],
            ["P-3DT2M", -86400 * 3 + 60 * 2, 0],
            ["P-3DT2S", -86400 * 3 + 2, 0],
            ["P-3DT2H1S", -86400 * 3 + 3600 * 2 + 1, 0],
            ["P-3DT2M1S", -86400 * 3 + 60 * 2 + 1, 0],
            ["P-3DT2H1M1S", -86400 * 3 + 3600 * 2 + 60 + 1, 0],

            ["P-3DT-2H", -86400 * 3 - 3600 * 2, 0],
            ["P-3DT-2M", -86400 * 3 - 60 * 2, 0],
            ["P-3DT-2S", -86400 * 3 - 2, 0],
            ["P-3DT-2H1S", -86400 * 3 - 3600 * 2 + 1, 0],
            ["P-3DT-2M1S", -86400 * 3 - 60 * 2 + 1, 0],
            ["P-3DT-2H1M1S", -86400 * 3 - 3600 * 2 + 60 + 1, 0],

            ["PT0H", 0, 0],
            ["PT0H0M", 0, 0],
            ["PT0H0S", 0, 0],
            ["PT0H0M0S", 0, 0],

            ["PT1H", 3600, 0],
            ["PT3H", 3600 * 3, 0],
            ["PT-1H", -3600, 0],
            ["PT-3H", -3600 * 3, 0],

            ["PT2H5M", 3600 * 2 + 60 * 5, 0],
            ["PT2H5S", 3600 * 2 + 5, 0],
            ["PT2H5M8S", 3600 * 2 + 60 * 5 + 8, 0],
            ["PT-2H5M", -3600 * 2 + 60 * 5, 0],
            ["PT-2H5S", -3600 * 2 + 5, 0],
            ["PT-2H5M8S", -3600 * 2 + 60 * 5 + 8, 0],
            ["PT-2H-5M", -3600 * 2 - 60 * 5, 0],
            ["PT-2H-5S", -3600 * 2 - 5, 0],
            ["PT-2H-5M8S", -3600 * 2 - 60 * 5 + 8, 0],
            ["PT-2H-5M-8S", -3600 * 2 - 60 * 5 - 8, 0],

            ["PT0M", 0, 0],
            ["PT1M", 60, 0],
            ["PT3M", 60 * 3, 0],
            ["PT-1M", -60, 0],
            ["PT-3M", -60 * 3, 0],
            ["P0DT3M", 60 * 3, 0],
            ["P0DT-3M", -60 * 3, 0],
        ];
    }

    /**
     * @dataProvider data_parseSuccess
     */
    public function test_factory_parse($text, $expectedSeconds, $expectedNanoOfSecond)
    {
        $test = Duration::parse($text);
        $this->assertEquals($test->getSeconds(), $expectedSeconds);
        $this->assertEquals($test->getNano(), $expectedNanoOfSecond);
    }

    /**
     * @dataProvider data_parseSuccess
     */
    public function test_factory_parse_plus($text, $expectedSeconds, $expectedNanoOfSecond)
    {
        $test = Duration::parse("+" . $text);
        $this->assertEquals($test->getSeconds(), $expectedSeconds);
        $this->assertEquals($test->getNano(), $expectedNanoOfSecond);
    }

    /**
     * @dataProvider data_parseSuccess
     */
    public function test_factory_parse_minus($text, $expectedSeconds, $expectedNanoOfSecond)
    {
        try {
            $test = Duration::parse("-" . $text);
        } catch (DateTimeParseException $ex) {
            $this->assertEquals($expectedSeconds == Long::MIN_VALUE, true);
            return;
        }
        // not inside try/catch or it breaks $test
        $this->assertEquals($test, Duration::ofSeconds($expectedSeconds, $expectedNanoOfSecond)->negated());
    }

    /**
     * @dataProvider data_parseSuccess
     */
    public function test_factory_parse_comma($text, $expectedSeconds, $expectedNanoOfSecond)
    {
        $text = str_replace('.', ',', $text);
        $test = Duration::parse($text);
        $this->assertEquals($test->getSeconds(), $expectedSeconds);
        $this->assertEquals($test->getNano(), $expectedNanoOfSecond);
    }

    /**
     * @dataProvider data_parseSuccess
     */
    public function test_factory_parse_lowerCase($text, $expectedSeconds, $expectedNanoOfSecond)
    {
        $test = Duration::parse(strtolower($text));
        $this->assertEquals($test->getSeconds(), $expectedSeconds);
        $this->assertEquals($test->getNano(), $expectedNanoOfSecond);
    }

    function data_parseFailure()
    {
        return [
            [""],
            ["ABCDEF"],
            [" PT0S"],
            ["PT0S "],

            ["PTS"],
            ["AT0S"],
            ["PA0S"],
            ["PT0A"],

            ["P0Y"],
            ["P1Y"],
            ["P-2Y"],
            ["P0M"],
            ["P1M"],
            ["P-2M"],
            ["P3Y2D"],
            ["P3M2D"],
            ["P3W"],
            ["P-3W"],
            ["P2YT30S"],
            ["P2MT30S"],

            ["P1DT"],

            ["PT+S"],
            ["PT-S"],
            ["PT.S"],
            ["PTAS"],

            ["PT-.S"],
            ["PT+.S"],

            ["PT1ABC2S"],
            ["PT1.1ABC2S"],

            ["PT123456789123456789123456789S"],
            ["PT0.1234567891S"],

            ["PT2.-3"],
            ["PT-2.-3"],
            ["PT2.+3"],
            ["PT-2.+3"],
        ];
    }

    /**
     * @dataProvider data_parseFailure
     * @expectedException \Celest\DateTimeParseException
     */
    public function test_factory_parseFailures($text)
    {
        Duration::parse($text);
    }

    /**
     * @dataProvider data_parseFailure
     * @expectedException \Celest\DateTimeParseException
     */
    public function test_factory_parseFailures_comma($text)
    {
        $text = str_replace('.', ',', $text);
        Duration::parse($text);
    }

    /**
     * @expectedException \Celest\DateTimeParseException
     */
    public function test_factory_parse_tooBig()
    {
        Duration::parse("PT" . Long::MAX_VALUE . "1S");
    }

    /**
     * @expectedException \Celest\DateTimeParseException
     */
    public function test_factory_parse_tooBig_decimal()
    {
        Duration::parse("PT" . Long::MAX_VALUE . "1.1S");
    }

    /**
     * @expectedException \Celest\DateTimeParseException
     */
    public function test_factory_parse_tooSmall()
    {
        Duration::parse("PT" . Long::MIN_VALUE . "1S");
    }

    /**
     * @expectedException \Celest\DateTimeParseException
     */
    public function test_factory_parse_tooSmall_decimal()
    {
        Duration::parse("PT" . Long::MIN_VALUE . ".1S");
    }

    public function test_factory_parse_nullText()
    {
        TestHelper::assertNullException($this, function () {
            Duration::parse(null);
        });
    }

    //-----------------------------------------------------------------------
    // between()
    //-----------------------------------------------------------------------
    function data_durationBetweenInstant()
    {
        return [
            [0, 0, 0, 0, 0, 0],
            [3, 0, 7, 0, 4, 0],
            [7, 0, 3, 0, -4, 0],

            [3, 20, 7, 50, 4, 30],
            [3, 80, 7, 50, 3, 999999970],
            [3, 80, 7, 79, 3, 999999999],
            [3, 80, 7, 80, 4, 0],
            [3, 80, 7, 81, 4, 1],
        ];
    }

    /**
     * @dataProvider data_durationBetweenInstant
     */
    public function test_factory_between_TemporalTemporal_Instant($secs1, $nanos1, $secs2, $nanos2, $expectedSeconds, $expectedNanoOfSecond)
    {
        $start = Instant::ofEpochSecond($secs1, $nanos1);
        $end = Instant::ofEpochSecond($secs2, $nanos2);
        $t = Duration::between($start, $end);
        $this->assertEquals($t->getSeconds(), $expectedSeconds);
        $this->assertEquals($t->getNano(), $expectedNanoOfSecond);
    }

    /**
     * @dataProvider data_durationBetweenInstant
     */
    public function test_factory_between_TemporalTemporal_Instant_negated($secs1, $nanos1, $secs2, $nanos2, $expectedSeconds, $expectedNanoOfSecond)
    {
        $start = Instant::ofEpochSecond($secs1, $nanos1);
        $end = Instant::ofEpochSecond($secs2, $nanos2);
        $this->assertEquals(Duration::between($end, $start), Duration::between($start, $end)->negated());
    }

    function data_durationBetweenLocalTime()
    {
        return [
            [LocalTime::of(11, 0, 30), LocalTime::of(11, 0, 45), 15, 0],
            [LocalTime::of(11, 0, 30), LocalTime::of(11, 0, 25), -5, 0],
        ];
    }

    /**
     * @dataProvider data_durationBetweenLocalTime
     */
    public function test_factory_between_TemporalTemporal_LT(LocalTime $start, LocalTime $end, $expectedSeconds, $expectedNanoOfSecond)
    {
        $t = Duration::between($start, $end);
        $this->assertEquals($t->getSeconds(), $expectedSeconds);
        $this->assertEquals($t->getNano(), $expectedNanoOfSecond);
    }

    /**
     * @dataProvider data_durationBetweenLocalTime
     */
    public function test_factory_between_TemporalTemporal_LT_negated(LocalTime $start, LocalTime $end, $expectedSeconds, $expectedNanoOfSecond)
    {
        $this->assertEquals(Duration::between($end, $start), Duration::between($start, $end)->negated());
    }

    function data_durationBetweenLocalDateTime()
    {
        return [
            [LocalDateTime::of(2013, 3, 24, 0, 44, 31, 565000000), LocalDateTime::of(2013, 3, 24, 0, 44, 30, 65000000), -2, 500000000],
            [LocalDateTime::of(2013, 3, 24, 0, 44, 31, 565000000), LocalDateTime::of(2013, 3, 24, 0, 44, 31, 65000000), -1, 500000000],
            [LocalDateTime::of(2013, 3, 24, 0, 44, 31, 565000000), LocalDateTime::of(2013, 3, 24, 0, 44, 32, 65000000), 0, 500000000],
            [LocalDateTime::of(2013, 3, 24, 0, 44, 31, 565000000), LocalDateTime::of(2013, 3, 24, 0, 44, 33, 65000000), 1, 500000000],
            [LocalDateTime::of(2013, 3, 24, 0, 44, 31, 565000000), LocalDateTime::of(2013, 3, 24, 0, 44, 34, 65000000), 2, 500000000],

            [LocalDateTime::of(2013, 3, 24, 0, 44, 31, 65000000), LocalDateTime::of(2013, 3, 24, 0, 44, 30, 565000000), -1, 500000000],
            [LocalDateTime::of(2013, 3, 24, 0, 44, 31, 65000000), LocalDateTime::of(2013, 3, 24, 0, 44, 31, 565000000), 0, 500000000],
            [LocalDateTime::of(2013, 3, 24, 0, 44, 31, 65000000), LocalDateTime::of(2013, 3, 24, 0, 44, 32, 565000000), 1, 500000000],
            [LocalDateTime::of(2013, 3, 24, 0, 44, 31, 65000000), LocalDateTime::of(2013, 3, 24, 0, 44, 33, 565000000), 2, 500000000],
            [LocalDateTime::of(2013, 3, 24, 0, 44, 31, 65000000), LocalDateTime::of(2013, 3, 24, 0, 44, 34, 565000000), 3, 500000000],

            [LocalDateTime::of(2013, 3, 24, 0, 44, 31, 65000000), LocalDateTime::of(2013, 3, 24, 0, 44, 30, 65000000), -1, 0],
            [LocalDateTime::of(2013, 3, 24, 0, 44, 31, 65000000), LocalDateTime::of(2013, 3, 24, 0, 44, 31, 65000000), 0, 0],
            [LocalDateTime::of(2013, 3, 24, 0, 44, 31, 65000000), LocalDateTime::of(2013, 3, 24, 0, 44, 32, 65000000), 1, 0],
            [LocalDateTime::of(2013, 3, 24, 0, 44, 31, 65000000), LocalDateTime::of(2013, 3, 24, 0, 44, 33, 65000000), 2, 0],
            [LocalDateTime::of(2013, 3, 24, 0, 44, 31, 65000000), LocalDateTime::of(2013, 3, 24, 0, 44, 34, 65000000), 3, 0],

            [LocalDateTime::of(2013, 3, 24, 0, 44, 31, 65000000), LocalDateTime::of(2813, 3, 24, 0, 44, 30, 565000000), 2 * self::CYCLE_SECS - 1, 500000000],
            [LocalDateTime::of(2013, 3, 24, 0, 44, 31, 65000000), LocalDateTime::of(2813, 3, 24, 0, 44, 31, 565000000), 2 * self::CYCLE_SECS + 0, 500000000],
            [LocalDateTime::of(2013, 3, 24, 0, 44, 31, 65000000), LocalDateTime::of(2813, 3, 24, 0, 44, 32, 565000000), 2 * self::CYCLE_SECS + 1, 500000000],
        ];
    }

    /**
     * @dataProvider data_durationBetweenLocalDateTime
     */
    public function test_factory_between_TemporalTemporal_LDT(LocalDateTime $start, LocalDateTime $end, $expectedSeconds, $expectedNanoOfSecond)
    {
        $t = Duration::between($start, $end);
        $this->assertEquals($t->getSeconds(), $expectedSeconds);
        $this->assertEquals($t->getNano(), $expectedNanoOfSecond);
    }

    /**
     * @dataProvider data_durationBetweenLocalDateTime
     */
    public function test_factory_between_TemporalTemporal_LDT_negated(LocalDateTime $start, LocalDateTime $end, $expectedSeconds, $expectedNanoOfSecond)
    {
        $this->assertEquals(Duration::between($end, $start), Duration::between($start, $end)->negated());
    }


    public function test_factory_between_TemporalTemporal_mixedTypes()
    {
        $start = Instant::ofEpochSecond(1);
        $end = Instant::ofEpochSecond(4)->atZone(ZoneOffset::UTC());
        $this->assertEquals(Duration::between($start, $end), Duration::ofSeconds(3));
    }

    /**
     * @expectedException \Celest\DateTimeException
     */
    public function test_factory_between_TemporalTemporal_invalidMixedTypes()
    {
        $start = Instant::ofEpochSecond(1);
        $end = LocalDate::of(2010, 6, 20);
        Duration::between($start, $end);
    }

    public function test_factory_between__TemporalTemporal_startNull()
    {
        TestHelper::assertNullException($this, function () {
            $end = Instant::ofEpochSecond(1);
            Duration::between(null, $end);
        });

    }

    public function test_factory_between__TemporalTemporal_endNull()
    {
        TestHelper::assertNullException($this, function () {
            $start = Instant::ofEpochSecond(1);
            Duration::between($start, null);
        });

    }

    //-----------------------------------------------------------------------
    // isZero(), isPositive(), isPositiveOrZero(), isNegative(), isNegativeOrZero()
    //-----------------------------------------------------------------------

    public function test_isZero()
    {
        $this->assertEquals(Duration::ofNanos(0)->isZero(), true);
        $this->assertEquals(Duration::ofSeconds(0)->isZero(), true);
        $this->assertEquals(Duration::ofNanos(1)->isZero(), false);
        $this->assertEquals(Duration::ofSeconds(1)->isZero(), false);
        $this->assertEquals(Duration::ofSeconds(1, 1)->isZero(), false);
        $this->assertEquals(Duration::ofNanos(-1)->isZero(), false);
        $this->assertEquals(Duration::ofSeconds(-1)->isZero(), false);
        $this->assertEquals(Duration::ofSeconds(-1, -1)->isZero(), false);
    }


    public function test_isNegative()
    {
        $this->assertEquals(Duration::ofNanos(0)->isNegative(), false);
        $this->assertEquals(Duration::ofSeconds(0)->isNegative(), false);
        $this->assertEquals(Duration::ofNanos(1)->isNegative(), false);
        $this->assertEquals(Duration::ofSeconds(1)->isNegative(), false);
        $this->assertEquals(Duration::ofSeconds(1, 1)->isNegative(), false);
        $this->assertEquals(Duration::ofNanos(-1)->isNegative(), true);
        $this->assertEquals(Duration::ofSeconds(-1)->isNegative(), true);
        $this->assertEquals(Duration::ofSeconds(-1, -1)->isNegative(), true);
    }

    //-----------------------------------------------------------------------
    // plus()
    //-----------------------------------------------------------------------
    function provider_plus()
    {
        return [
            [Long::MIN_VALUE, 0, Long::MAX_VALUE, 0, -1, 0],

            [-4, 666666667, -4, 666666667, -7, 333333334],
            [-4, 666666667, -3, 0, -7, 666666667],
            [-4, 666666667, -2, 0, -6, 666666667],
            [-4, 666666667, -1, 0, -5, 666666667],
            [-4, 666666667, -1, 333333334, -4, 1],
            [-4, 666666667, -1, 666666667, -4, 333333334],
            [-4, 666666667, -1, 999999999, -4, 666666666],
            [-4, 666666667, 0, 0, -4, 666666667],
            [-4, 666666667, 0, 1, -4, 666666668],
            [-4, 666666667, 0, 333333333, -3, 0],
            [-4, 666666667, 0, 666666666, -3, 333333333],
            [-4, 666666667, 1, 0, -3, 666666667],
            [-4, 666666667, 2, 0, -2, 666666667],
            [-4, 666666667, 3, 0, -1, 666666667],
            [-4, 666666667, 3, 333333333, 0, 0],

            [-3, 0, -4, 666666667, -7, 666666667],
            [-3, 0, -3, 0, -6, 0],
            [-3, 0, -2, 0, -5, 0],
            [-3, 0, -1, 0, -4, 0],
            [-3, 0, -1, 333333334, -4, 333333334],
            [-3, 0, -1, 666666667, -4, 666666667],
            [-3, 0, -1, 999999999, -4, 999999999],
            [-3, 0, 0, 0, -3, 0],
            [-3, 0, 0, 1, -3, 1],
            [-3, 0, 0, 333333333, -3, 333333333],
            [-3, 0, 0, 666666666, -3, 666666666],
            [-3, 0, 1, 0, -2, 0],
            [-3, 0, 2, 0, -1, 0],
            [-3, 0, 3, 0, 0, 0],
            [-3, 0, 3, 333333333, 0, 333333333],

            [-2, 0, -4, 666666667, -6, 666666667],
            [-2, 0, -3, 0, -5, 0],
            [-2, 0, -2, 0, -4, 0],
            [-2, 0, -1, 0, -3, 0],
            [-2, 0, -1, 333333334, -3, 333333334],
            [-2, 0, -1, 666666667, -3, 666666667],
            [-2, 0, -1, 999999999, -3, 999999999],
            [-2, 0, 0, 0, -2, 0],
            [-2, 0, 0, 1, -2, 1],
            [-2, 0, 0, 333333333, -2, 333333333],
            [-2, 0, 0, 666666666, -2, 666666666],
            [-2, 0, 1, 0, -1, 0],
            [-2, 0, 2, 0, 0, 0],
            [-2, 0, 3, 0, 1, 0],
            [-2, 0, 3, 333333333, 1, 333333333],

            [-1, 0, -4, 666666667, -5, 666666667],
            [-1, 0, -3, 0, -4, 0],
            [-1, 0, -2, 0, -3, 0],
            [-1, 0, -1, 0, -2, 0],
            [-1, 0, -1, 333333334, -2, 333333334],
            [-1, 0, -1, 666666667, -2, 666666667],
            [-1, 0, -1, 999999999, -2, 999999999],
            [-1, 0, 0, 0, -1, 0],
            [-1, 0, 0, 1, -1, 1],
            [-1, 0, 0, 333333333, -1, 333333333],
            [-1, 0, 0, 666666666, -1, 666666666],
            [-1, 0, 1, 0, 0, 0],
            [-1, 0, 2, 0, 1, 0],
            [-1, 0, 3, 0, 2, 0],
            [-1, 0, 3, 333333333, 2, 333333333],

            [-1, 666666667, -4, 666666667, -4, 333333334],
            [-1, 666666667, -3, 0, -4, 666666667],
            [-1, 666666667, -2, 0, -3, 666666667],
            [-1, 666666667, -1, 0, -2, 666666667],
            [-1, 666666667, -1, 333333334, -1, 1],
            [-1, 666666667, -1, 666666667, -1, 333333334],
            [-1, 666666667, -1, 999999999, -1, 666666666],
            [-1, 666666667, 0, 0, -1, 666666667],
            [-1, 666666667, 0, 1, -1, 666666668],
            [-1, 666666667, 0, 333333333, 0, 0],
            [-1, 666666667, 0, 666666666, 0, 333333333],
            [-1, 666666667, 1, 0, 0, 666666667],
            [-1, 666666667, 2, 0, 1, 666666667],
            [-1, 666666667, 3, 0, 2, 666666667],
            [-1, 666666667, 3, 333333333, 3, 0],

            [0, 0, -4, 666666667, -4, 666666667],
            [0, 0, -3, 0, -3, 0],
            [0, 0, -2, 0, -2, 0],
            [0, 0, -1, 0, -1, 0],
            [0, 0, -1, 333333334, -1, 333333334],
            [0, 0, -1, 666666667, -1, 666666667],
            [0, 0, -1, 999999999, -1, 999999999],
            [0, 0, 0, 0, 0, 0],
            [0, 0, 0, 1, 0, 1],
            [0, 0, 0, 333333333, 0, 333333333],
            [0, 0, 0, 666666666, 0, 666666666],
            [0, 0, 1, 0, 1, 0],
            [0, 0, 2, 0, 2, 0],
            [0, 0, 3, 0, 3, 0],
            [0, 0, 3, 333333333, 3, 333333333],

            [0, 333333333, -4, 666666667, -3, 0],
            [0, 333333333, -3, 0, -3, 333333333],
            [0, 333333333, -2, 0, -2, 333333333],
            [0, 333333333, -1, 0, -1, 333333333],
            [0, 333333333, -1, 333333334, -1, 666666667],
            [0, 333333333, -1, 666666667, 0, 0],
            [0, 333333333, -1, 999999999, 0, 333333332],
            [0, 333333333, 0, 0, 0, 333333333],
            [0, 333333333, 0, 1, 0, 333333334],
            [0, 333333333, 0, 333333333, 0, 666666666],
            [0, 333333333, 0, 666666666, 0, 999999999],
            [0, 333333333, 1, 0, 1, 333333333],
            [0, 333333333, 2, 0, 2, 333333333],
            [0, 333333333, 3, 0, 3, 333333333],
            [0, 333333333, 3, 333333333, 3, 666666666],

            [1, 0, -4, 666666667, -3, 666666667],
            [1, 0, -3, 0, -2, 0],
            [1, 0, -2, 0, -1, 0],
            [1, 0, -1, 0, 0, 0],
            [1, 0, -1, 333333334, 0, 333333334],
            [1, 0, -1, 666666667, 0, 666666667],
            [1, 0, -1, 999999999, 0, 999999999],
            [1, 0, 0, 0, 1, 0],
            [1, 0, 0, 1, 1, 1],
            [1, 0, 0, 333333333, 1, 333333333],
            [1, 0, 0, 666666666, 1, 666666666],
            [1, 0, 1, 0, 2, 0],
            [1, 0, 2, 0, 3, 0],
            [1, 0, 3, 0, 4, 0],
            [1, 0, 3, 333333333, 4, 333333333],

            [2, 0, -4, 666666667, -2, 666666667],
            [2, 0, -3, 0, -1, 0],
            [2, 0, -2, 0, 0, 0],
            [2, 0, -1, 0, 1, 0],
            [2, 0, -1, 333333334, 1, 333333334],
            [2, 0, -1, 666666667, 1, 666666667],
            [2, 0, -1, 999999999, 1, 999999999],
            [2, 0, 0, 0, 2, 0],
            [2, 0, 0, 1, 2, 1],
            [2, 0, 0, 333333333, 2, 333333333],
            [2, 0, 0, 666666666, 2, 666666666],
            [2, 0, 1, 0, 3, 0],
            [2, 0, 2, 0, 4, 0],
            [2, 0, 3, 0, 5, 0],
            [2, 0, 3, 333333333, 5, 333333333],

            [3, 0, -4, 666666667, -1, 666666667],
            [3, 0, -3, 0, 0, 0],
            [3, 0, -2, 0, 1, 0],
            [3, 0, -1, 0, 2, 0],
            [3, 0, -1, 333333334, 2, 333333334],
            [3, 0, -1, 666666667, 2, 666666667],
            [3, 0, -1, 999999999, 2, 999999999],
            [3, 0, 0, 0, 3, 0],
            [3, 0, 0, 1, 3, 1],
            [3, 0, 0, 333333333, 3, 333333333],
            [3, 0, 0, 666666666, 3, 666666666],
            [3, 0, 1, 0, 4, 0],
            [3, 0, 2, 0, 5, 0],
            [3, 0, 3, 0, 6, 0],
            [3, 0, 3, 333333333, 6, 333333333],

            [3, 333333333, -4, 666666667, 0, 0],
            [3, 333333333, -3, 0, 0, 333333333],
            [3, 333333333, -2, 0, 1, 333333333],
            [3, 333333333, -1, 0, 2, 333333333],
            [3, 333333333, -1, 333333334, 2, 666666667],
            [3, 333333333, -1, 666666667, 3, 0],
            [3, 333333333, -1, 999999999, 3, 333333332],
            [3, 333333333, 0, 0, 3, 333333333],
            [3, 333333333, 0, 1, 3, 333333334],
            [3, 333333333, 0, 333333333, 3, 666666666],
            [3, 333333333, 0, 666666666, 3, 999999999],
            [3, 333333333, 1, 0, 4, 333333333],
            [3, 333333333, 2, 0, 5, 333333333],
            [3, 333333333, 3, 0, 6, 333333333],
            [3, 333333333, 3, 333333333, 6, 666666666],

            [Long::MAX_VALUE, 0, Long::MIN_VALUE, 0, -1, 0],
        ];
    }

    /**
     * @dataProvider provider_plus
     */
    public function test_plus($seconds, $nanos, $otherSeconds, $otherNanos, $expectedSeconds, $expectedNanoOfSecond)
    {
        $t = Duration::ofSeconds($seconds, $nanos)->plusAmount(Duration::ofSeconds($otherSeconds, $otherNanos));
        $this->assertEquals($t->getSeconds(), $expectedSeconds);
        $this->assertEquals($t->getNano(), $expectedNanoOfSecond);
    }

    /**
     * @expectedException \Celest\ArithmeticException
     */
    public function test_plusOverflowTooBig()
    {
        $t = Duration::ofSeconds(Long::MAX_VALUE, 999999999);
        $t->plusAmount(Duration::ofSeconds(0, 1));
    }

    /**
     * @expectedException \Celest\ArithmeticException
     */
    public function test_plusOverflowTooSmall()
    {
        $t = Duration::ofSeconds(Long::MIN_VALUE);
        $t->plusAmount(Duration::ofSeconds(-1, 999999999));
    }

    //-----------------------------------------------------------------------

    public function test_plus_longTemporalUnit_seconds()
    {
        $t = Duration::ofSeconds(1);
        $t = $t->plus(1, CU::SECONDS());
        $this->assertEquals(2, $t->getSeconds());
        $this->assertEquals(0, $t->getNano());
    }


    public function test_plus_longTemporalUnit_millis()
    {
        $t = Duration::ofSeconds(1);
        $t = $t->plus(1, CU::MILLIS());
        $this->assertEquals(1, $t->getSeconds());
        $this->assertEquals(1000000, $t->getNano());
    }


    public function test_plus_longTemporalUnit_micros()
    {
        $t = Duration::ofSeconds(1);
        $t = $t->plus(1, CU::MICROS());
        $this->assertEquals(1, $t->getSeconds());
        $this->assertEquals(1000, $t->getNano());
    }


    public function test_plus_longTemporalUnit_nanos()
    {
        $t = Duration::ofSeconds(1);
        $t = $t->plus(1, CU::NANOS());
        $this->assertEquals(1, $t->getSeconds());
        $this->assertEquals(1, $t->getNano());
    }

    public function test_plus_longTemporalUnit_null()
    {
        TestHelper::assertNullException($this, function () {
            $t = Duration::ofSeconds(1);
            $t->plus(1, null);
        });

    }

    //-----------------------------------------------------------------------
    function provider_plusDays_long()
    {
        return [
            [0, 0, 0],
            [0, 1, 1],
            [0, -1, -1],
            [Math::div(Math::div(Long::MAX_VALUE, 3600), 24), 0, Math::div(Math::div(Long::MAX_VALUE, 3600), 24)],
            [Math::div(Math::div(Long::MIN_VALUE, 3600), 24), 0, Math::div(Math::div(Long::MIN_VALUE, 3600), 24)],
            [1, 0, 1],
            [1, 1, 2],
            [1, -1, 0],
            [1, Math::div(Math::div(Long::MIN_VALUE, 3600), 24), Math::div(Math::div(Long::MIN_VALUE, 3600), 24) + 1],
            [1, 0, 1],
            [1, 1, 2],
            [1, -1, 0],
            [-1, 0, -1],
            [-1, 1, 0],
            [-1, -1, -2],
            [-1, Math::div(Math::div(Long::MAX_VALUE, 3600), 24), Math::div(Math::div(Long::MAX_VALUE, 3600), 24) - 1],
        ];
    }

    /**
     * @dataProvider provider_plusDays_long
     */
    public function test_plusDays_long($days, $amount, $expectedDays)
    {
        $t = Duration::ofDays($days);
        $t = $t->plusDays($amount);
        $this->assertEquals($t->toDays(), $expectedDays);
    }

    /**
     * @expectedException \Celest\ArithmeticException
     */
    public function test_plusDays_long_overflowTooBig()
    {
        $t = Duration::ofDays(1);
        $t->plusDays(Math::div(Math::div(Long::MAX_VALUE, 3600), 24));
    }

    /**
     * @expectedException \Celest\ArithmeticException
     */
    public function test_plusDays_long_overflowTooSmall()
    {
        $t = Duration::ofDays(-1);
        $t->plusDays(Math::div(Math::div(Long::MIN_VALUE, 3600), 24));
    }

    //-----------------------------------------------------------------------
    function provider_plusHours_long()
    {
        return [
            [0, 0, 0],
            [0, 1, 1],
            [0, -1, -1],
            [Math::div(Long::MAX_VALUE, 3600), 0, Math::div(Long::MAX_VALUE, 3600)],
            [Math::div(Long::MIN_VALUE, 3600), 0, Math::div(Long::MIN_VALUE, 3600)],
            [1, 0, 1],
            [1, 1, 2],
            [1, -1, 0],
            [1, Math::div(Long::MIN_VALUE, 3600), Math::div(Long::MIN_VALUE, 3600) + 1],
            [1, 0, 1],
            [1, 1, 2],
            [1, -1, 0],
            [-1, 0, -1],
            [-1, 1, 0],
            [-1, -1, -2],
            [-1, Math::div(Long::MAX_VALUE, 3600), Math::div(Long::MAX_VALUE, 3600) - 1],
        ];
    }

    /**
     * @dataProvider provider_plusHours_long
     */
    public function test_plusHours_long($hours, $amount, $expectedHours)
    {
        $t = Duration::ofHours($hours);
        $t = $t->plusHours($amount);
        $this->assertEquals($t->toHours(), $expectedHours);
    }

    /**
     * @expectedException \Celest\ArithmeticException
     */
    public function test_plusHours_long_overflowTooBig()
    {
        $t = Duration::ofHours(1);
        $t->plusHours(Math::div(Long::MAX_VALUE, 3600));
    }

    /**
     * @expectedException \Celest\ArithmeticException
     */
    public function test_plusHours_long_overflowTooSmall()
    {
        $t = Duration::ofHours(-1);
        $t->plusHours(Math::div(Long::MIN_VALUE, 3600));
    }

    //-----------------------------------------------------------------------
    function provider_plusMinutes_long()
    {
        return [
            [0, 0, 0],
            [0, 1, 1],
            [0, -1, -1],
            [Math::div(Long::MAX_VALUE, 60), 0, Math::div(Long::MAX_VALUE, 60)],
            [Math::div(Long::MIN_VALUE, 60), 0, Math::div(Long::MIN_VALUE, 60)],
            [1, 0, 1],
            [1, 1, 2],
            [1, -1, 0],
            [1, Math::div(Long::MIN_VALUE, 60), Math::div(Long::MIN_VALUE, 60) + 1],
            [1, 0, 1],
            [1, 1, 2],
            [1, -1, 0],
            [-1, 0, -1],
            [-1, 1, 0],
            [-1, -1, -2],
            [-1, Math::div(Long::MAX_VALUE, 60), Math::div(Long::MAX_VALUE, 60) - 1],
        ];
    }

    /**
     * @dataProvider provider_plusMinutes_long
     */
    public function test_plusMinutes_long($minutes, $amount, $expectedMinutes)
    {
        $t = Duration::ofMinutes($minutes);
        $t = $t->plusMinutes($amount);
        $this->assertEquals($t->toMinutes(), $expectedMinutes);
    }

    /**
     * @expectedException \Celest\ArithmeticException
     */
    public function test_plusMinutes_long_overflowTooBig()
    {
        $t = Duration::ofMinutes(1);
        $t->plusMinutes(Math::div(Long::MAX_VALUE, 60));
    }

    /**
     * @expectedException \Celest\ArithmeticException
     */
    public function test_plusMinutes_long_overflowTooSmall()
    {
        $t = Duration::ofMinutes(-1);
        $t->plusMinutes(Math::div(Long::MIN_VALUE, 60));
    }

    //-----------------------------------------------------------------------
    function provider_plusSeconds_long()
    {
        return [
            [0, 0, 0, 0, 0],
            [0, 0, 1, 1, 0],
            [0, 0, -1, -1, 0],
            [0, 0, Long::MAX_VALUE, Long::MAX_VALUE, 0],
            [0, 0, Long::MIN_VALUE, Long::MIN_VALUE, 0],
            [1, 0, 0, 1, 0],
            [1, 0, 1, 2, 0],
            [1, 0, -1, 0, 0],
            [1, 0, Long::MAX_VALUE - 1, Long::MAX_VALUE, 0],
            [1, 0, Long::MIN_VALUE, Long::MIN_VALUE + 1, 0],
            [1, 1, 0, 1, 1],
            [1, 1, 1, 2, 1],
            [1, 1, -1, 0, 1],
            [1, 1, Long::MAX_VALUE - 1, Long::MAX_VALUE, 1],
            [1, 1, Long::MIN_VALUE, Long::MIN_VALUE + 1, 1],
            [-1, 1, 0, -1, 1],
            [-1, 1, 1, 0, 1],
            [-1, 1, -1, -2, 1],
            [-1, 1, Long::MAX_VALUE, Long::MAX_VALUE - 1, 1],
            [-1, 1, Long::MIN_VALUE + 1, Long::MIN_VALUE, 1],
        ];
    }

    /**
     * @dataProvider provider_plusSeconds_long
     */
    public function test_plusSeconds_long($seconds, $nanos, $amount, $expectedSeconds, $expectedNanoOfSecond)
    {
        $t = Duration::ofSeconds($seconds, $nanos);
        $t = $t->plusSeconds($amount);
        $this->assertEquals($t->getSeconds(), $expectedSeconds);
        $this->assertEquals($t->getNano(), $expectedNanoOfSecond);
    }

    /**
     * @expectedException \Celest\ArithmeticException
     */
    public function test_plusSeconds_long_overflowTooBig()
    {
        $t = Duration::ofSeconds(1, 0);
        $t->plusSeconds(Long::MAX_VALUE);
    }

    /**
     * @expectedException \Celest\ArithmeticException
     */
    public function test_plusSeconds_long_overflowTooSmall()
    {
        $t = Duration::ofSeconds(-1, 0);
        $t->plusSeconds(Long::MIN_VALUE);
    }

    //-----------------------------------------------------------------------
    function provider_plusMillis_long()
    {
        return [
            [0, 0, 0, 0, 0],
            [0, 0, 1, 0, 1000000],
            [0, 0, 999, 0, 999000000],
            [0, 0, 1000, 1, 0],
            [0, 0, 1001, 1, 1000000],
            [0, 0, 1999, 1, 999000000],
            [0, 0, 2000, 2, 0],
            [0, 0, -1, -1, 999000000],
            [0, 0, -999, -1, 1000000],
            [0, 0, -1000, -1, 0],
            [0, 0, -1001, -2, 999000000],
            [0, 0, -1999, -2, 1000000],

            [0, 1, 0, 0, 1],
            [0, 1, 1, 0, 1000001],
            [0, 1, 998, 0, 998000001],
            [0, 1, 999, 0, 999000001],
            [0, 1, 1000, 1, 1],
            [0, 1, 1998, 1, 998000001],
            [0, 1, 1999, 1, 999000001],
            [0, 1, 2000, 2, 1],
            [0, 1, -1, -1, 999000001],
            [0, 1, -2, -1, 998000001],
            [0, 1, -1000, -1, 1],
            [0, 1, -1001, -2, 999000001],

            [0, 1000000, 0, 0, 1000000],
            [0, 1000000, 1, 0, 2000000],
            [0, 1000000, 998, 0, 999000000],
            [0, 1000000, 999, 1, 0],
            [0, 1000000, 1000, 1, 1000000],
            [0, 1000000, 1998, 1, 999000000],
            [0, 1000000, 1999, 2, 0],
            [0, 1000000, 2000, 2, 1000000],
            [0, 1000000, -1, 0, 0],
            [0, 1000000, -2, -1, 999000000],
            [0, 1000000, -999, -1, 2000000],
            [0, 1000000, -1000, -1, 1000000],
            [0, 1000000, -1001, -1, 0],
            [0, 1000000, -1002, -2, 999000000],

            [0, 999999999, 0, 0, 999999999],
            [0, 999999999, 1, 1, 999999],
            [0, 999999999, 999, 1, 998999999],
            [0, 999999999, 1000, 1, 999999999],
            [0, 999999999, 1001, 2, 999999],
            [0, 999999999, -1, 0, 998999999],
            [0, 999999999, -1000, -1, 999999999],
            [0, 999999999, -1001, -1, 998999999],
        ];
    }

    /**
     * @dataProvider provider_plusMillis_long
     */
    public function test_plusMillis_long($seconds, $nanos, $amount, $expectedSeconds, $expectedNanoOfSecond)
    {
        $t = Duration::ofSeconds($seconds, $nanos);
        $t = $t->plusMillis($amount);
        $this->assertEquals($t->getSeconds(), $expectedSeconds);
        $this->assertEquals($t->getNano(), $expectedNanoOfSecond);
    }

    /**
     * @dataProvider provider_plusMillis_long
     */
    public function test_plusMillis_long_oneMore($seconds, $nanos, $amount, $expectedSeconds, $expectedNanoOfSecond)
    {
        $t = Duration::ofSeconds($seconds + 1, $nanos);
        $t = $t->plusMillis($amount);
        $this->assertEquals($t->getSeconds(), $expectedSeconds + 1);
        $this->assertEquals($t->getNano(), $expectedNanoOfSecond);
    }

    /**
     * @dataProvider provider_plusMillis_long
     */
    public function test_plusMillis_long_minusOneLess($seconds, $nanos, $amount, $expectedSeconds, $expectedNanoOfSecond)
    {
        $t = Duration::ofSeconds($seconds - 1, $nanos);
        $t = $t->plusMillis($amount);
        $this->assertEquals($t->getSeconds(), $expectedSeconds - 1);
        $this->assertEquals($t->getNano(), $expectedNanoOfSecond);
    }


    public function test_plusMillis_long_max()
    {
        $t = Duration::ofSeconds(Long::MAX_VALUE, 998999999);
        $t = $t->plusMillis(1);
        $this->assertEquals($t->getSeconds(), Long::MAX_VALUE);
        $this->assertEquals($t->getNano(), 999999999);
    }

    /**
     * @expectedException \Celest\ArithmeticException
     */
    public function test_plusMillis_long_overflowTooBig()
    {
        $t = Duration::ofSeconds(Long::MAX_VALUE, 999000000);
        $t->plusMillis(1);
    }


    public function test_plusMillis_long_min()
    {
        $t = Duration::ofSeconds(Long::MIN_VALUE, 1000000);
        $t = $t->plusMillis(-1);
        $this->assertEquals($t->getSeconds(), Long::MIN_VALUE);
        $this->assertEquals($t->getNano(), 0);
    }

    /**
     * @expectedException \Celest\ArithmeticException
     */
    public function test_plusMillis_long_overflowTooSmall()
    {
        $t = Duration::ofSeconds(Long::MIN_VALUE, 0);
        $t->plusMillis(-1);
    }

    //-----------------------------------------------------------------------
    function provider_plusNanos_long()
    {
        return [
            [0, 0, 0, 0, 0],
            [0, 0, 1, 0, 1],
            [0, 0, 999999999, 0, 999999999],
            [0, 0, 1000000000, 1, 0],
            [0, 0, 1000000001, 1, 1],
            [0, 0, 1999999999, 1, 999999999],
            [0, 0, 2000000000, 2, 0],
            [0, 0, -1, -1, 999999999],
            [0, 0, -999999999, -1, 1],
            [0, 0, -1000000000, -1, 0],
            [0, 0, -1000000001, -2, 999999999],
            [0, 0, -1999999999, -2, 1],

            [1, 0, 0, 1, 0],
            [1, 0, 1, 1, 1],
            [1, 0, 999999999, 1, 999999999],
            [1, 0, 1000000000, 2, 0],
            [1, 0, 1000000001, 2, 1],
            [1, 0, 1999999999, 2, 999999999],
            [1, 0, 2000000000, 3, 0],
            [1, 0, -1, 0, 999999999],
            [1, 0, -999999999, 0, 1],
            [1, 0, -1000000000, 0, 0],
            [1, 0, -1000000001, -1, 999999999],
            [1, 0, -1999999999, -1, 1],

            [-1, 0, 0, -1, 0],
            [-1, 0, 1, -1, 1],
            [-1, 0, 999999999, -1, 999999999],
            [-1, 0, 1000000000, 0, 0],
            [-1, 0, 1000000001, 0, 1],
            [-1, 0, 1999999999, 0, 999999999],
            [-1, 0, 2000000000, 1, 0],
            [-1, 0, -1, -2, 999999999],
            [-1, 0, -999999999, -2, 1],
            [-1, 0, -1000000000, -2, 0],
            [-1, 0, -1000000001, -3, 999999999],
            [-1, 0, -1999999999, -3, 1],

            [1, 1, 0, 1, 1],
            [1, 1, 1, 1, 2],
            [1, 1, 999999998, 1, 999999999],
            [1, 1, 999999999, 2, 0],
            [1, 1, 1000000000, 2, 1],
            [1, 1, 1999999998, 2, 999999999],
            [1, 1, 1999999999, 3, 0],
            [1, 1, 2000000000, 3, 1],
            [1, 1, -1, 1, 0],
            [1, 1, -2, 0, 999999999],
            [1, 1, -1000000000, 0, 1],
            [1, 1, -1000000001, 0, 0],
            [1, 1, -1000000002, -1, 999999999],
            [1, 1, -2000000000, -1, 1],

            [1, 999999999, 0, 1, 999999999],
            [1, 999999999, 1, 2, 0],
            [1, 999999999, 999999999, 2, 999999998],
            [1, 999999999, 1000000000, 2, 999999999],
            [1, 999999999, 1000000001, 3, 0],
            [1, 999999999, -1, 1, 999999998],
            [1, 999999999, -1000000000, 0, 999999999],
            [1, 999999999, -1000000001, 0, 999999998],
            [1, 999999999, -1999999999, 0, 0],
            [1, 999999999, -2000000000, -1, 999999999],

            [Long::MAX_VALUE, 0, 999999999, Long::MAX_VALUE, 999999999],
            [Long::MAX_VALUE - 1, 0, 1999999999, Long::MAX_VALUE, 999999999],
            [Long::MIN_VALUE, 1, -1, Long::MIN_VALUE, 0],
            [Long::MIN_VALUE + 1, 1, -1000000001, Long::MIN_VALUE, 0],
        ];
    }

    /**
     * @dataProvider provider_plusNanos_long
     */
    public function test_plusNanos_long($seconds, $nanos, $amount, $expectedSeconds, $expectedNanoOfSecond)
    {
        $t = Duration::ofSeconds($seconds, $nanos);
        $t = $t->plusNanos($amount);
        $this->assertEquals($t->getSeconds(), $expectedSeconds);
        $this->assertEquals($t->getNano(), $expectedNanoOfSecond);
    }

    /**
     * @expectedException \Celest\ArithmeticException
     */
    public function test_plusNanos_long_overflowTooBig()
    {
        $t = Duration::ofSeconds(Long::MAX_VALUE, 999999999);
        $t->plusNanos(1);
    }

    /**
     * @expectedException \Celest\ArithmeticException
     */
    public function test_plusNanos_long_overflowTooSmall()
    {
        $t = Duration::ofSeconds(Long::MIN_VALUE, 0);
        $t->plusNanos(-1);
    }

    //-----------------------------------------------------------------------
    function provider_minus()
    {
        return [
            [Long::MIN_VALUE, 0, Long::MIN_VALUE + 1, 0, -1, 0],

            [-4, 666666667, -4, 666666667, 0, 0],
            [-4, 666666667, -3, 0, -1, 666666667],
            [-4, 666666667, -2, 0, -2, 666666667],
            [-4, 666666667, -1, 0, -3, 666666667],
            [-4, 666666667, -1, 333333334, -3, 333333333],
            [-4, 666666667, -1, 666666667, -3, 0],
            [-4, 666666667, -1, 999999999, -4, 666666668],
            [-4, 666666667, 0, 0, -4, 666666667],
            [-4, 666666667, 0, 1, -4, 666666666],
            [-4, 666666667, 0, 333333333, -4, 333333334],
            [-4, 666666667, 0, 666666666, -4, 1],
            [-4, 666666667, 1, 0, -5, 666666667],
            [-4, 666666667, 2, 0, -6, 666666667],
            [-4, 666666667, 3, 0, -7, 666666667],
            [-4, 666666667, 3, 333333333, -7, 333333334],

            [-3, 0, -4, 666666667, 0, 333333333],
            [-3, 0, -3, 0, 0, 0],
            [-3, 0, -2, 0, -1, 0],
            [-3, 0, -1, 0, -2, 0],
            [-3, 0, -1, 333333334, -3, 666666666],
            [-3, 0, -1, 666666667, -3, 333333333],
            [-3, 0, -1, 999999999, -3, 1],
            [-3, 0, 0, 0, -3, 0],
            [-3, 0, 0, 1, -4, 999999999],
            [-3, 0, 0, 333333333, -4, 666666667],
            [-3, 0, 0, 666666666, -4, 333333334],
            [-3, 0, 1, 0, -4, 0],
            [-3, 0, 2, 0, -5, 0],
            [-3, 0, 3, 0, -6, 0],
            [-3, 0, 3, 333333333, -7, 666666667],

            [-2, 0, -4, 666666667, 1, 333333333],
            [-2, 0, -3, 0, 1, 0],
            [-2, 0, -2, 0, 0, 0],
            [-2, 0, -1, 0, -1, 0],
            [-2, 0, -1, 333333334, -2, 666666666],
            [-2, 0, -1, 666666667, -2, 333333333],
            [-2, 0, -1, 999999999, -2, 1],
            [-2, 0, 0, 0, -2, 0],
            [-2, 0, 0, 1, -3, 999999999],
            [-2, 0, 0, 333333333, -3, 666666667],
            [-2, 0, 0, 666666666, -3, 333333334],
            [-2, 0, 1, 0, -3, 0],
            [-2, 0, 2, 0, -4, 0],
            [-2, 0, 3, 0, -5, 0],
            [-2, 0, 3, 333333333, -6, 666666667],

            [-1, 0, -4, 666666667, 2, 333333333],
            [-1, 0, -3, 0, 2, 0],
            [-1, 0, -2, 0, 1, 0],
            [-1, 0, -1, 0, 0, 0],
            [-1, 0, -1, 333333334, -1, 666666666],
            [-1, 0, -1, 666666667, -1, 333333333],
            [-1, 0, -1, 999999999, -1, 1],
            [-1, 0, 0, 0, -1, 0],
            [-1, 0, 0, 1, -2, 999999999],
            [-1, 0, 0, 333333333, -2, 666666667],
            [-1, 0, 0, 666666666, -2, 333333334],
            [-1, 0, 1, 0, -2, 0],
            [-1, 0, 2, 0, -3, 0],
            [-1, 0, 3, 0, -4, 0],
            [-1, 0, 3, 333333333, -5, 666666667],

            [-1, 666666667, -4, 666666667, 3, 0],
            [-1, 666666667, -3, 0, 2, 666666667],
            [-1, 666666667, -2, 0, 1, 666666667],
            [-1, 666666667, -1, 0, 0, 666666667],
            [-1, 666666667, -1, 333333334, 0, 333333333],
            [-1, 666666667, -1, 666666667, 0, 0],
            [-1, 666666667, -1, 999999999, -1, 666666668],
            [-1, 666666667, 0, 0, -1, 666666667],
            [-1, 666666667, 0, 1, -1, 666666666],
            [-1, 666666667, 0, 333333333, -1, 333333334],
            [-1, 666666667, 0, 666666666, -1, 1],
            [-1, 666666667, 1, 0, -2, 666666667],
            [-1, 666666667, 2, 0, -3, 666666667],
            [-1, 666666667, 3, 0, -4, 666666667],
            [-1, 666666667, 3, 333333333, -4, 333333334],

            [0, 0, -4, 666666667, 3, 333333333],
            [0, 0, -3, 0, 3, 0],
            [0, 0, -2, 0, 2, 0],
            [0, 0, -1, 0, 1, 0],
            [0, 0, -1, 333333334, 0, 666666666],
            [0, 0, -1, 666666667, 0, 333333333],
            [0, 0, -1, 999999999, 0, 1],
            [0, 0, 0, 0, 0, 0],
            [0, 0, 0, 1, -1, 999999999],
            [0, 0, 0, 333333333, -1, 666666667],
            [0, 0, 0, 666666666, -1, 333333334],
            [0, 0, 1, 0, -1, 0],
            [0, 0, 2, 0, -2, 0],
            [0, 0, 3, 0, -3, 0],
            [0, 0, 3, 333333333, -4, 666666667],

            [0, 333333333, -4, 666666667, 3, 666666666],
            [0, 333333333, -3, 0, 3, 333333333],
            [0, 333333333, -2, 0, 2, 333333333],
            [0, 333333333, -1, 0, 1, 333333333],
            [0, 333333333, -1, 333333334, 0, 999999999],
            [0, 333333333, -1, 666666667, 0, 666666666],
            [0, 333333333, -1, 999999999, 0, 333333334],
            [0, 333333333, 0, 0, 0, 333333333],
            [0, 333333333, 0, 1, 0, 333333332],
            [0, 333333333, 0, 333333333, 0, 0],
            [0, 333333333, 0, 666666666, -1, 666666667],
            [0, 333333333, 1, 0, -1, 333333333],
            [0, 333333333, 2, 0, -2, 333333333],
            [0, 333333333, 3, 0, -3, 333333333],
            [0, 333333333, 3, 333333333, -3, 0],

            [1, 0, -4, 666666667, 4, 333333333],
            [1, 0, -3, 0, 4, 0],
            [1, 0, -2, 0, 3, 0],
            [1, 0, -1, 0, 2, 0],
            [1, 0, -1, 333333334, 1, 666666666],
            [1, 0, -1, 666666667, 1, 333333333],
            [1, 0, -1, 999999999, 1, 1],
            [1, 0, 0, 0, 1, 0],
            [1, 0, 0, 1, 0, 999999999],
            [1, 0, 0, 333333333, 0, 666666667],
            [1, 0, 0, 666666666, 0, 333333334],
            [1, 0, 1, 0, 0, 0],
            [1, 0, 2, 0, -1, 0],
            [1, 0, 3, 0, -2, 0],
            [1, 0, 3, 333333333, -3, 666666667],

            [2, 0, -4, 666666667, 5, 333333333],
            [2, 0, -3, 0, 5, 0],
            [2, 0, -2, 0, 4, 0],
            [2, 0, -1, 0, 3, 0],
            [2, 0, -1, 333333334, 2, 666666666],
            [2, 0, -1, 666666667, 2, 333333333],
            [2, 0, -1, 999999999, 2, 1],
            [2, 0, 0, 0, 2, 0],
            [2, 0, 0, 1, 1, 999999999],
            [2, 0, 0, 333333333, 1, 666666667],
            [2, 0, 0, 666666666, 1, 333333334],
            [2, 0, 1, 0, 1, 0],
            [2, 0, 2, 0, 0, 0],
            [2, 0, 3, 0, -1, 0],
            [2, 0, 3, 333333333, -2, 666666667],

            [3, 0, -4, 666666667, 6, 333333333],
            [3, 0, -3, 0, 6, 0],
            [3, 0, -2, 0, 5, 0],
            [3, 0, -1, 0, 4, 0],
            [3, 0, -1, 333333334, 3, 666666666],
            [3, 0, -1, 666666667, 3, 333333333],
            [3, 0, -1, 999999999, 3, 1],
            [3, 0, 0, 0, 3, 0],
            [3, 0, 0, 1, 2, 999999999],
            [3, 0, 0, 333333333, 2, 666666667],
            [3, 0, 0, 666666666, 2, 333333334],
            [3, 0, 1, 0, 2, 0],
            [3, 0, 2, 0, 1, 0],
            [3, 0, 3, 0, 0, 0],
            [3, 0, 3, 333333333, -1, 666666667],

            [3, 333333333, -4, 666666667, 6, 666666666],
            [3, 333333333, -3, 0, 6, 333333333],
            [3, 333333333, -2, 0, 5, 333333333],
            [3, 333333333, -1, 0, 4, 333333333],
            [3, 333333333, -1, 333333334, 3, 999999999],
            [3, 333333333, -1, 666666667, 3, 666666666],
            [3, 333333333, -1, 999999999, 3, 333333334],
            [3, 333333333, 0, 0, 3, 333333333],
            [3, 333333333, 0, 1, 3, 333333332],
            [3, 333333333, 0, 333333333, 3, 0],
            [3, 333333333, 0, 666666666, 2, 666666667],
            [3, 333333333, 1, 0, 2, 333333333],
            [3, 333333333, 2, 0, 1, 333333333],
            [3, 333333333, 3, 0, 0, 333333333],
            [3, 333333333, 3, 333333333, 0, 0],

            [Long::MAX_VALUE, 0, Long::MAX_VALUE, 0, 0, 0],
        ];
    }

    /**
     * @dataProvider provider_minus
     */
    public function test_minus($seconds, $nanos, $otherSeconds, $otherNanos, $expectedSeconds, $expectedNanoOfSecond)
    {
        $t = Duration::ofSeconds($seconds, $nanos)->minusAmount(Duration::ofSeconds($otherSeconds, $otherNanos));
        $this->assertEquals($t->getSeconds(), $expectedSeconds);
        $this->assertEquals($t->getNano(), $expectedNanoOfSecond);
    }

    /**
     * @expectedException \Celest\ArithmeticException
     */
    public function test_minusOverflowTooSmall()
    {
        $t = Duration::ofSeconds(Long::MIN_VALUE);
        $t->minusAmount(Duration::ofSeconds(0, 1));
    }

    /**
     * @expectedException \Celest\ArithmeticException
     */
    public function test_minusOverflowTooBig()
    {
        $t = Duration::ofSeconds(Long::MAX_VALUE, 999999999);
        $t->minusAmount(Duration::ofSeconds(-1, 999999999));
    }

    //-----------------------------------------------------------------------

    public function test_minus_longTemporalUnit_seconds()
    {
        $t = Duration::ofSeconds(1);
        $t = $t->minus(1, CU::SECONDS());
        $this->assertEquals(0, $t->getSeconds());
        $this->assertEquals(0, $t->getNano());
    }


    public function test_minus_longTemporalUnit_millis()
    {
        $t = Duration::ofSeconds(1);
        $t = $t->minus(1, CU::MILLIS());
        $this->assertEquals(0, $t->getSeconds());
        $this->assertEquals(999000000, $t->getNano());
    }


    public function test_minus_longTemporalUnit_micros()
    {
        $t = Duration::ofSeconds(1);
        $t = $t->minus(1, CU::MICROS());
        $this->assertEquals(0, $t->getSeconds());
        $this->assertEquals(999999000, $t->getNano());
    }


    public function test_minus_longTemporalUnit_nanos()
    {
        $t = Duration::ofSeconds(1);
        $t = $t->minus(1, CU::NANOS());
        $this->assertEquals(0, $t->getSeconds());
        $this->assertEquals(999999999, $t->getNano());
    }

    public function test_minus_longTemporalUnit_null()
    {
        TestHelper::assertNullException($this, function () {
            $t = Duration::ofSeconds(1);
            $t->minus(1, null);
        });

    }

    //-----------------------------------------------------------------------
    function provider_minusDays_long()
    {
        return [
            [0, 0, 0],
            [0, 1, -1],
            [0, -1, 1],
            [Math::div(Math::div(Long::MAX_VALUE, 3600), 24), 0, Math::div(Math::div(Long::MAX_VALUE, 3600), 24)],
            [Math::div(Math::div(Long::MIN_VALUE, 3600), 24), 0, Math::div(Math::div(Long::MIN_VALUE, 3600), 24)],
            [1, 0, 1],
            [1, 1, 0],
            [1, -1, 2],
            [Math::div(Math::div(Long::MAX_VALUE, 3600), 24), 1, Math::div(Math::div(Long::MAX_VALUE, 3600), 24) - 1],
            [Math::div(Math::div(Long::MIN_VALUE, 3600), 24), -1, Math::div(Math::div(Long::MIN_VALUE, 3600), 24) + 1],
            [1, 0, 1],
            [1, 1, 0],
            [1, -1, 2],
            [-1, 0, -1],
            [-1, 1, -2],
            [-1, -1, 0],
        ];
    }

    /**
     * @dataProvider provider_minusDays_long
     */
    public function test_minusDays_long($days, $amount, $expectedDays)
    {
        $t = Duration::ofDays($days);
        $t = $t->minusDays($amount);
        $this->assertEquals($t->toDays(), $expectedDays);
    }

    /**
     * @expectedException \Celest\ArithmeticException
     */
    public function test_minusDays_long_overflowTooBig()
    {
        $t = Duration::ofDays(Math::div(Math::div(Long::MAX_VALUE, 3600), 24));
        $t->minusDays(-1);
    }

    /**
     * @expectedException \Celest\ArithmeticException
     */
    public function test_minusDays_long_overflowTooSmall()
    {
        $t = Duration::ofDays(Math::div(Math::div(Long::MIN_VALUE, 3600), 24));
        $t->minusDays(1);
    }

    //-----------------------------------------------------------------------
    function provider_minusHours_long()
    {
        return [
            [0, 0, 0],
            [0, 1, -1],
            [0, -1, 1],
            [Math::div(Long::MAX_VALUE, 3600), 0, Math::div(Long::MAX_VALUE, 3600)],
            [Math::div(Long::MIN_VALUE, 3600), 0, Math::div(Long::MIN_VALUE, 3600)],
            [1, 0, 1],
            [1, 1, 0],
            [1, -1, 2],
            [Math::div(Long::MAX_VALUE, 3600), 1, Math::div(Long::MAX_VALUE, 3600) - 1],
            [Math::div(Long::MIN_VALUE, 3600), -1, Math::div(Long::MIN_VALUE, 3600) + 1],
            [1, 0, 1],
            [1, 1, 0],
            [1, -1, 2],
            [-1, 0, -1],
            [-1, 1, -2],
            [-1, -1, 0],
        ];
    }

    /**
     * @dataProvider provider_minusHours_long
     */
    public function test_minusHours_long($hours, $amount, $expectedHours)
    {
        $t = Duration::ofHours($hours);
        $t = $t->minusHours($amount);
        $this->assertEquals($t->toHours(), $expectedHours);
    }

    /**
     * @expectedException \Celest\ArithmeticException
     */
    public function test_minusHours_long_overflowTooBig()
    {
        $t = Duration::ofHours(Math::div(Long::MAX_VALUE, 3600));
        $t->minusHours(-1);
    }

    /**
     * @expectedException \Celest\ArithmeticException
     */
    public function test_minusHours_long_overflowTooSmall()
    {
        $t = Duration::ofHours(Math::div(Long::MIN_VALUE, 3600));
        $t->minusHours(1);
    }

    //-----------------------------------------------------------------------
    function provider_minusminutes_long()
    {
        return [
            [0, 0, 0],
            [0, 1, -1],
            [0, -1, 1],
            [Math::div(Long::MAX_VALUE, 60), 0, Math::div(Long::MAX_VALUE, 60)],
            [Math::div(Long::MIN_VALUE, 60), 0, Math::div(Long::MIN_VALUE, 60)],
            [1, 0, 1],
            [1, 1, 0],
            [1, -1, 2],
            [Math::div(Long::MAX_VALUE, 60), 1, Math::div(Long::MAX_VALUE, 60) - 1],
            [Math::div(Long::MIN_VALUE, 60), -1, Math::div(Long::MIN_VALUE, 60) + 1],
            [1, 0, 1],
            [1, 1, 0],
            [1, -1, 2],
            [-1, 0, -1],
            [-1, 1, -2],
            [-1, -1, 0],
        ];
    }

    /**
     * @dataProvider provider_minusminutes_long
     */
    public function test_minusMinutes_long($minutes, $amount, $expectedMinutes)
    {
        $t = Duration::ofMinutes($minutes);
        $t = $t->minusMinutes($amount);
        $this->assertEquals($t->toMinutes(), $expectedMinutes);
    }

    /**
     * @expectedException \Celest\ArithmeticException
     */
    public function test_minusMinutes_long_overflowTooBig()
    {
        $t = Duration::ofMinutes(Math::div(Long::MAX_VALUE, 60));
        $t->minusMinutes(-1);
    }

    /**
     * @expectedException \Celest\ArithmeticException
     */
    public function test_minusMinutes_long_overflowTooSmall()
    {
        $t = Duration::ofMinutes(Math::div(Long::MIN_VALUE, 60));
        $t->minusMinutes(1);
    }

    //-----------------------------------------------------------------------
    function provider_minusSeconds_long()
    {
        return [
            [0, 0, 0, 0, 0],
            [0, 0, 1, -1, 0],
            [0, 0, -1, 1, 0],
            [0, 0, Long::MAX_VALUE, -Long::MAX_VALUE, 0],
            [0, 0, Long::MIN_VALUE + 1, Long::MAX_VALUE, 0],
            [1, 0, 0, 1, 0],
            [1, 0, 1, 0, 0],
            [1, 0, -1, 2, 0],
            [1, 0, Long::MAX_VALUE - 1, -Long::MAX_VALUE + 2, 0],
            [1, 0, Long::MIN_VALUE + 2, Long::MAX_VALUE, 0],
            [1, 1, 0, 1, 1],
            [1, 1, 1, 0, 1],
            [1, 1, -1, 2, 1],
            [1, 1, Long::MAX_VALUE, -Long::MAX_VALUE + 1, 1],
            [1, 1, Long::MIN_VALUE + 2, Long::MAX_VALUE, 1],
            [-1, 1, 0, -1, 1],
            [-1, 1, 1, -2, 1],
            [-1, 1, -1, 0, 1],
            [-1, 1, Long::MAX_VALUE, Long::MIN_VALUE, 1],
            [-1, 1, Long::MIN_VALUE + 1, Long::MAX_VALUE - 1, 1],
        ];
    }

    /**
     * @dataProvider provider_minusSeconds_long
     */
    public function test_minusSeconds_long($seconds, $nanos, $amount, $expectedSeconds, $expectedNanoOfSecond)
    {
        $t = Duration::ofSeconds($seconds, $nanos);
        $t = $t->minusSeconds($amount);
        $this->assertEquals($t->getSeconds(), $expectedSeconds);
        $this->assertEquals($t->getNano(), $expectedNanoOfSecond);
    }

    /**
     * @expectedException \Celest\ArithmeticException
     */
    public function test_minusSeconds_long_overflowTooBig()
    {
        $t = Duration::ofSeconds(1, 0);
        $t->minusSeconds(Long::MIN_VALUE + 1);
    }

    /**
     * @expectedException \Celest\ArithmeticException
     */
    public function test_minusSeconds_long_overflowTooSmall()
    {
        $t = Duration::ofSeconds(-2, 0);
        $t->minusSeconds(Long::MAX_VALUE);
    }

    //-----------------------------------------------------------------------
    function provider_minusMillis_long()
    {
        return [
            [0, 0, 0, 0, 0],
            [0, 0, 1, -1, 999000000],
            [0, 0, 999, -1, 1000000],
            [0, 0, 1000, -1, 0],
            [0, 0, 1001, -2, 999000000],
            [0, 0, 1999, -2, 1000000],
            [0, 0, 2000, -2, 0],
            [0, 0, -1, 0, 1000000],
            [0, 0, -999, 0, 999000000],
            [0, 0, -1000, 1, 0],
            [0, 0, -1001, 1, 1000000],
            [0, 0, -1999, 1, 999000000],

            [0, 1, 0, 0, 1],
            [0, 1, 1, -1, 999000001],
            [0, 1, 998, -1, 2000001],
            [0, 1, 999, -1, 1000001],
            [0, 1, 1000, -1, 1],
            [0, 1, 1998, -2, 2000001],
            [0, 1, 1999, -2, 1000001],
            [0, 1, 2000, -2, 1],
            [0, 1, -1, 0, 1000001],
            [0, 1, -2, 0, 2000001],
            [0, 1, -1000, 1, 1],
            [0, 1, -1001, 1, 1000001],

            [0, 1000000, 0, 0, 1000000],
            [0, 1000000, 1, 0, 0],
            [0, 1000000, 998, -1, 3000000],
            [0, 1000000, 999, -1, 2000000],
            [0, 1000000, 1000, -1, 1000000],
            [0, 1000000, 1998, -2, 3000000],
            [0, 1000000, 1999, -2, 2000000],
            [0, 1000000, 2000, -2, 1000000],
            [0, 1000000, -1, 0, 2000000],
            [0, 1000000, -2, 0, 3000000],
            [0, 1000000, -999, 1, 0],
            [0, 1000000, -1000, 1, 1000000],
            [0, 1000000, -1001, 1, 2000000],
            [0, 1000000, -1002, 1, 3000000],

            [0, 999999999, 0, 0, 999999999],
            [0, 999999999, 1, 0, 998999999],
            [0, 999999999, 999, 0, 999999],
            [0, 999999999, 1000, -1, 999999999],
            [0, 999999999, 1001, -1, 998999999],
            [0, 999999999, -1, 1, 999999],
            [0, 999999999, -1000, 1, 999999999],
            [0, 999999999, -1001, 2, 999999],
        ];
    }

    /**
     * @dataProvider provider_minusMillis_long
     */
    public function test_minusMillis_long($seconds, $nanos, $amount, $expectedSeconds, $expectedNanoOfSecond)
    {
        $t = Duration::ofSeconds($seconds, $nanos);
        $t = $t->minusMillis($amount);
        $this->assertEquals($t->getSeconds(), $expectedSeconds);
        $this->assertEquals($t->getNano(), $expectedNanoOfSecond);
    }

    /**
     * @dataProvider provider_minusMillis_long
     */
    public function test_minusMillis_long_oneMore($seconds, $nanos, $amount, $expectedSeconds, $expectedNanoOfSecond)
    {
        $t = Duration::ofSeconds($seconds + 1, $nanos);
        $t = $t->minusMillis($amount);
        $this->assertEquals($t->getSeconds(), $expectedSeconds + 1);
        $this->assertEquals($t->getNano(), $expectedNanoOfSecond);
    }

    /**
     * @dataProvider provider_minusMillis_long
     */
    public function test_minusMillis_long_minusOneLess($seconds, $nanos, $amount, $expectedSeconds, $expectedNanoOfSecond)
    {
        $t = Duration::ofSeconds($seconds - 1, $nanos);
        $t = $t->minusMillis($amount);
        $this->assertEquals($t->getSeconds(), $expectedSeconds - 1);
        $this->assertEquals($t->getNano(), $expectedNanoOfSecond);
    }


    public function test_minusMillis_long_max()
    {
        $t = Duration::ofSeconds(Long::MAX_VALUE, 998999999);
        $t = $t->minusMillis(-1);
        $this->assertEquals($t->getSeconds(), Long::MAX_VALUE);
        $this->assertEquals($t->getNano(), 999999999);
    }

    /**
     * @expectedException \Celest\ArithmeticException
     */
    public function test_minusMillis_long_overflowTooBig()
    {
        $t = Duration::ofSeconds(Long::MAX_VALUE, 999000000);
        $t->minusMillis(-1);
    }


    public function test_minusMillis_long_min()
    {
        $t = Duration::ofSeconds(Long::MIN_VALUE, 1000000);
        $t = $t->minusMillis(1);
        $this->assertEquals($t->getSeconds(), Long::MIN_VALUE);
        $this->assertEquals($t->getNano(), 0);
    }

    /**
     * @expectedException \Celest\ArithmeticException
     */
    public function test_minusMillis_long_overflowTooSmall()
    {
        $t = Duration::ofSeconds(Long::MIN_VALUE, 0);
        $t->minusMillis(1);
    }

    //-----------------------------------------------------------------------
    function provider_minusNanos_long()
    {
        return [
            [0, 0, 0, 0, 0],
            [0, 0, 1, -1, 999999999],
            [0, 0, 999999999, -1, 1],
            [0, 0, 1000000000, -1, 0],
            [0, 0, 1000000001, -2, 999999999],
            [0, 0, 1999999999, -2, 1],
            [0, 0, 2000000000, -2, 0],
            [0, 0, -1, 0, 1],
            [0, 0, -999999999, 0, 999999999],
            [0, 0, -1000000000, 1, 0],
            [0, 0, -1000000001, 1, 1],
            [0, 0, -1999999999, 1, 999999999],

            [1, 0, 0, 1, 0],
            [1, 0, 1, 0, 999999999],
            [1, 0, 999999999, 0, 1],
            [1, 0, 1000000000, 0, 0],
            [1, 0, 1000000001, -1, 999999999],
            [1, 0, 1999999999, -1, 1],
            [1, 0, 2000000000, -1, 0],
            [1, 0, -1, 1, 1],
            [1, 0, -999999999, 1, 999999999],
            [1, 0, -1000000000, 2, 0],
            [1, 0, -1000000001, 2, 1],
            [1, 0, -1999999999, 2, 999999999],

            [-1, 0, 0, -1, 0],
            [-1, 0, 1, -2, 999999999],
            [-1, 0, 999999999, -2, 1],
            [-1, 0, 1000000000, -2, 0],
            [-1, 0, 1000000001, -3, 999999999],
            [-1, 0, 1999999999, -3, 1],
            [-1, 0, 2000000000, -3, 0],
            [-1, 0, -1, -1, 1],
            [-1, 0, -999999999, -1, 999999999],
            [-1, 0, -1000000000, 0, 0],
            [-1, 0, -1000000001, 0, 1],
            [-1, 0, -1999999999, 0, 999999999],

            [1, 1, 0, 1, 1],
            [1, 1, 1, 1, 0],
            [1, 1, 999999998, 0, 3],
            [1, 1, 999999999, 0, 2],
            [1, 1, 1000000000, 0, 1],
            [1, 1, 1999999998, -1, 3],
            [1, 1, 1999999999, -1, 2],
            [1, 1, 2000000000, -1, 1],
            [1, 1, -1, 1, 2],
            [1, 1, -2, 1, 3],
            [1, 1, -1000000000, 2, 1],
            [1, 1, -1000000001, 2, 2],
            [1, 1, -1000000002, 2, 3],
            [1, 1, -2000000000, 3, 1],

            [1, 999999999, 0, 1, 999999999],
            [1, 999999999, 1, 1, 999999998],
            [1, 999999999, 999999999, 1, 0],
            [1, 999999999, 1000000000, 0, 999999999],
            [1, 999999999, 1000000001, 0, 999999998],
            [1, 999999999, -1, 2, 0],
            [1, 999999999, -1000000000, 2, 999999999],
            [1, 999999999, -1000000001, 3, 0],
            [1, 999999999, -1999999999, 3, 999999998],
            [1, 999999999, -2000000000, 3, 999999999],

            [Long::MAX_VALUE, 0, -999999999, Long::MAX_VALUE, 999999999],
            [Long::MAX_VALUE - 1, 0, -1999999999, Long::MAX_VALUE, 999999999],
            [Long::MIN_VALUE, 1, 1, Long::MIN_VALUE, 0],
            [Long::MIN_VALUE + 1, 1, 1000000001, Long::MIN_VALUE, 0],
        ];
    }

    /**
     * @dataProvider provider_minusNanos_long
     */
    public function test_minusNanos_long($seconds, $nanos, $amount, $expectedSeconds, $expectedNanoOfSecond)
    {
        $t = Duration::ofSeconds($seconds, $nanos);
        $t = $t->minusNanos($amount);
        $this->assertEquals($t->getSeconds(), $expectedSeconds);
        $this->assertEquals($t->getNano(), $expectedNanoOfSecond);
    }

    /**
     * @expectedException \Celest\ArithmeticException
     */
    public function test_minusNanos_long_overflowTooBig()
    {
        $t = Duration::ofSeconds(Long::MAX_VALUE, 999999999);
        $t->minusNanos(-1);
    }

    /**
     * @expectedException \Celest\ArithmeticException
     */
    public function test_minusNanos_long_overflowTooSmall()
    {
        $t = Duration::ofSeconds(Long::MIN_VALUE, 0);
        $t->minusNanos(1);
    }

    //-----------------------------------------------------------------------
    // multipliedBy()
    //-----------------------------------------------------------------------
    function provider_multipliedBy()
    {
        return [
            [-4, 666666667, -3, 9, 999999999],
            [-4, 666666667, -2, 6, 666666666],
            [-4, 666666667, -1, 3, 333333333],
            [-4, 666666667, 0, 0, 0],
            [-4, 666666667, 1, -4, 666666667],
            [-4, 666666667, 2, -7, 333333334],
            [-4, 666666667, 3, -10, 000000001],

            [-3, 0, -3, 9, 0],
            [-3, 0, -2, 6, 0],
            [-3, 0, -1, 3, 0],
            [-3, 0, 0, 0, 0],
            [-3, 0, 1, -3, 0],
            [-3, 0, 2, -6, 0],
            [-3, 0, 3, -9, 0],

            [-2, 0, -3, 6, 0],
            [-2, 0, -2, 4, 0],
            [-2, 0, -1, 2, 0],
            [-2, 0, 0, 0, 0],
            [-2, 0, 1, -2, 0],
            [-2, 0, 2, -4, 0],
            [-2, 0, 3, -6, 0],

            [-1, 0, -3, 3, 0],
            [-1, 0, -2, 2, 0],
            [-1, 0, -1, 1, 0],
            [-1, 0, 0, 0, 0],
            [-1, 0, 1, -1, 0],
            [-1, 0, 2, -2, 0],
            [-1, 0, 3, -3, 0],

            [-1, 500000000, -3, 1, 500000000],
            [-1, 500000000, -2, 1, 0],
            [-1, 500000000, -1, 0, 500000000],
            [-1, 500000000, 0, 0, 0],
            [-1, 500000000, 1, -1, 500000000],
            [-1, 500000000, 2, -1, 0],
            [-1, 500000000, 3, -2, 500000000],

            [0, 0, -3, 0, 0],
            [0, 0, -2, 0, 0],
            [0, 0, -1, 0, 0],
            [0, 0, 0, 0, 0],
            [0, 0, 1, 0, 0],
            [0, 0, 2, 0, 0],
            [0, 0, 3, 0, 0],

            [0, 500000000, -3, -2, 500000000],
            [0, 500000000, -2, -1, 0],
            [0, 500000000, -1, -1, 500000000],
            [0, 500000000, 0, 0, 0],
            [0, 500000000, 1, 0, 500000000],
            [0, 500000000, 2, 1, 0],
            [0, 500000000, 3, 1, 500000000],

            [1, 0, -3, -3, 0],
            [1, 0, -2, -2, 0],
            [1, 0, -1, -1, 0],
            [1, 0, 0, 0, 0],
            [1, 0, 1, 1, 0],
            [1, 0, 2, 2, 0],
            [1, 0, 3, 3, 0],

            [2, 0, -3, -6, 0],
            [2, 0, -2, -4, 0],
            [2, 0, -1, -2, 0],
            [2, 0, 0, 0, 0],
            [2, 0, 1, 2, 0],
            [2, 0, 2, 4, 0],
            [2, 0, 3, 6, 0],

            [3, 0, -3, -9, 0],
            [3, 0, -2, -6, 0],
            [3, 0, -1, -3, 0],
            [3, 0, 0, 0, 0],
            [3, 0, 1, 3, 0],
            [3, 0, 2, 6, 0],
            [3, 0, 3, 9, 0],

            [3, 333333333, -3, -10, 000000001],
            [3, 333333333, -2, -7, 333333334],
            [3, 333333333, -1, -4, 666666667],
            [3, 333333333, 0, 0, 0],
            [3, 333333333, 1, 3, 333333333],
            [3, 333333333, 2, 6, 666666666],
            [3, 333333333, 3, 9, 999999999],
        ];
    }

    /**
     * @dataProvider provider_multipliedBy
     */
    public function test_multipliedBy($seconds, $nanos, $multiplicand, $expectedSeconds, $expectedNanos)
    {
        $t = Duration::ofSeconds($seconds, $nanos);
        $t = $t->multipliedBy($multiplicand);
        $this->assertEquals($t->getSeconds(), $expectedSeconds);
        $this->assertEquals($t->getNano(), $expectedNanos);
    }


    public function test_multipliedBy_max()
    {
        $test = Duration::ofSeconds(1);
        $this->assertEquals($test->multipliedBy(Long::MAX_VALUE), Duration::ofSeconds(Long::MAX_VALUE));
    }


    public function test_multipliedBy_min()
    {
        $test = Duration::ofSeconds(1);
        $this->assertEquals($test->multipliedBy(Long::MIN_VALUE), Duration::ofSeconds(Long::MIN_VALUE));
    }

    /**
     * @expectedException \Celest\ArithmeticException
     */
    public function test_multipliedBy_tooBig()
    {
        $test = Duration::ofSeconds(1, 1);
        $test->multipliedBy(Long::MAX_VALUE);
    }

    /**
     * @expectedException \Celest\ArithmeticException
     */
    public function test_multipliedBy_tooBig_negative()
    {
        $test = Duration::ofSeconds(1, 1);
        $test->multipliedBy(Long::MIN_VALUE);
    }

    //-----------------------------------------------------------------------
    // dividedBy()
    //-----------------------------------------------------------------------
    function provider_dividedBy()
    {
        return [
            [-4, 666666667, -3, 1, 111111111],
            [-4, 666666667, -2, 1, 666666666],
            [-4, 666666667, -1, 3, 333333333],
            [-4, 666666667, 1, -4, 666666667],
            [-4, 666666667, 2, -2, 333333334],
            [-4, 666666667, 3, -2, 888888889],

            [-3, 0, -3, 1, 0],
            [-3, 0, -2, 1, 500000000],
            [-3, 0, -1, 3, 0],
            [-3, 0, 1, -3, 0],
            [-3, 0, 2, -2, 500000000],
            [-3, 0, 3, -1, 0],

            [-2, 0, -3, 0, 666666666],
            [-2, 0, -2, 1, 0],
            [-2, 0, -1, 2, 0],
            [-2, 0, 1, -2, 0],
            [-2, 0, 2, -1, 0],
            [-2, 0, 3, -1, 333333334],

            [-1, 0, -3, 0, 333333333],
            [-1, 0, -2, 0, 500000000],
            [-1, 0, -1, 1, 0],
            [-1, 0, 1, -1, 0],
            [-1, 0, 2, -1, 500000000],
            [-1, 0, 3, -1, 666666667],

            [-1, 500000000, -3, 0, 166666666],
            [-1, 500000000, -2, 0, 250000000],
            [-1, 500000000, -1, 0, 500000000],
            [-1, 500000000, 1, -1, 500000000],
            [-1, 500000000, 2, -1, 750000000],
            [-1, 500000000, 3, -1, 833333334],

            [0, 0, -3, 0, 0],
            [0, 0, -2, 0, 0],
            [0, 0, -1, 0, 0],
            [0, 0, 1, 0, 0],
            [0, 0, 2, 0, 0],
            [0, 0, 3, 0, 0],

            [0, 500000000, -3, -1, 833333334],
            [0, 500000000, -2, -1, 750000000],
            [0, 500000000, -1, -1, 500000000],
            [0, 500000000, 1, 0, 500000000],
            [0, 500000000, 2, 0, 250000000],
            [0, 500000000, 3, 0, 166666666],

            [1, 0, -3, -1, 666666667],
            [1, 0, -2, -1, 500000000],
            [1, 0, -1, -1, 0],
            [1, 0, 1, 1, 0],
            [1, 0, 2, 0, 500000000],
            [1, 0, 3, 0, 333333333],

            [2, 0, -3, -1, 333333334],
            [2, 0, -2, -1, 0],
            [2, 0, -1, -2, 0],
            [2, 0, 1, 2, 0],
            [2, 0, 2, 1, 0],
            [2, 0, 3, 0, 666666666],

            [3, 0, -3, -1, 0],
            [3, 0, -2, -2, 500000000],
            [3, 0, -1, -3, 0],
            [3, 0, 1, 3, 0],
            [3, 0, 2, 1, 500000000],
            [3, 0, 3, 1, 0],

            [3, 333333333, -3, -2, 888888889],
            [3, 333333333, -2, -2, 333333334],
            [3, 333333333, -1, -4, 666666667],
            [3, 333333333, 1, 3, 333333333],
            [3, 333333333, 2, 1, 666666666],
            [3, 333333333, 3, 1, 111111111],
        ];
    }

    /**
     * @dataProvider provider_dividedBy
     */
    public function test_dividedBy($seconds, $nanos, $divisor, $expectedSeconds, $expectedNanos)
    {
        $t = Duration::ofSeconds($seconds, $nanos);
        $t = $t->dividedBy($divisor);
        $this->assertEquals($t->getSeconds(), $expectedSeconds);
        $this->assertEquals($t->getNano(), $expectedNanos);
    }

    /**
     * @dataProvider provider_dividedBy
     * @expectedException \Celest\ArithmeticException
     */
    public function test_dividedByZero($seconds, $nanos, $divisor, $expectedSeconds, $expectedNanos)
    {
        $t = Duration::ofSeconds($seconds, $nanos);
        $t->dividedBy(0);
        $this->fail($t . " divided by zero did not throw ArithmeticException");
    }


    public function test_dividedBy_max()
    {
        $test = Duration::ofSeconds(Long::MAX_VALUE);
        $this->assertEquals($test->dividedBy(Long::MAX_VALUE), Duration::ofSeconds(1));
    }

    //-----------------------------------------------------------------------
    // negated()
    //-----------------------------------------------------------------------

    public function test_negated()
    {
        $this->assertEquals(Duration::ofSeconds(0)->negated(), Duration::ofSeconds(0));
        $this->assertEquals(Duration::ofSeconds(12)->negated(), Duration::ofSeconds(-12));
        $this->assertEquals(Duration::ofSeconds(-12)->negated(), Duration::ofSeconds(12));
        $this->assertEquals(Duration::ofSeconds(12, 20)->negated(), Duration::ofSeconds(-12, -20));
        $this->assertEquals(Duration::ofSeconds(12, -20)->negated(), Duration::ofSeconds(-12, 20));
        $this->assertEquals(Duration::ofSeconds(-12, -20)->negated(), Duration::ofSeconds(12, 20));
        $this->assertEquals(Duration::ofSeconds(-12, 20)->negated(), Duration::ofSeconds(12, -20));
        $this->assertEquals(Duration::ofSeconds(Long::MAX_VALUE)->negated(), Duration::ofSeconds(-Long::MAX_VALUE));
    }

    /**
     * @expectedException \Celest\ArithmeticException
     */
    public function test_negated_overflow()
    {
        Duration::ofSeconds(Long::MIN_VALUE)->negated();
    }

    //-----------------------------------------------------------------------
    // abs()
    //-----------------------------------------------------------------------

    public function test_abs()
    {
        $this->assertEquals(Duration::ofSeconds(0)->abs(), Duration::ofSeconds(0));
        $this->assertEquals(Duration::ofSeconds(12)->abs(), Duration::ofSeconds(12));
        $this->assertEquals(Duration::ofSeconds(-12)->abs(), Duration::ofSeconds(12));
        $this->assertEquals(Duration::ofSeconds(12, 20)->abs(), Duration::ofSeconds(12, 20));
        $this->assertEquals(Duration::ofSeconds(12, -20)->abs(), Duration::ofSeconds(12, -20));
        $this->assertEquals(Duration::ofSeconds(-12, -20)->abs(), Duration::ofSeconds(12, 20));
        $this->assertEquals(Duration::ofSeconds(-12, 20)->abs(), Duration::ofSeconds(12, -20));
        $this->assertEquals(Duration::ofSeconds(Long::MAX_VALUE)->abs(), Duration::ofSeconds(Long::MAX_VALUE));
    }

    /**
     * @expectedException \Celest\ArithmeticException
     */
    public function test_abs_overflow()
    {
        Duration::ofSeconds(Long::MIN_VALUE)->abs();
    }

    //-----------------------------------------------------------------------
    // toNanos()
    //-----------------------------------------------------------------------

    public function test_toNanos()
    {
        $test = Duration::ofSeconds(321, 123456789);
        $this->assertEquals($test->toNanos(), 321123456789);
    }


    public function test_toNanos_max()
    {
        $test = Duration::ofSeconds(0, Long::MAX_VALUE);
        $this->assertEquals($test->toNanos(), Long::MAX_VALUE);
    }

    /**
     * @expectedException \Celest\ArithmeticException
     */
    public function test_toNanos_tooBig()
    {
        $test = Duration::ofSeconds(0, Long::MAX_VALUE)->plusNanos(1);
        $test->toNanos();
    }

    //-----------------------------------------------------------------------
    // toMillis()
    //-----------------------------------------------------------------------

    public function test_toMillis()
    {
        $test = Duration::ofSeconds(321, 123456789);
        $this->assertEquals($test->toMillis(), 321000 + 123);
    }


    public function test_toMillis_max()
    {
        $test = Duration::ofSeconds(Math::div(Long::MAX_VALUE, 1000), (Long::MAX_VALUE % 1000) * 1000000);
        $this->assertEquals($test->toMillis(), Long::MAX_VALUE);
    }

    /**
     * @expectedException \Celest\ArithmeticException
     */
    public function test_toMillis_tooBig()
    {
        $test = Duration::ofSeconds(Math::div(Long::MAX_VALUE, 1000), ((Long::MAX_VALUE % 1000) + 1) * 1000000);
        $test->toMillis();
    }

    //-----------------------------------------------------------------------
    // compareTo()
    //-----------------------------------------------------------------------

    public function test_comparisons()
    {
        $this->doTest_comparisons_Duration([
                Duration::ofSeconds(-2, 0),
                Duration::ofSeconds(-2, 999999998),
                Duration::ofSeconds(-2, 999999999),
                Duration::ofSeconds(-1, 0),
                Duration::ofSeconds(-1, 1),
                Duration::ofSeconds(-1, 999999998),
                Duration::ofSeconds(-1, 999999999),
                Duration::ofSeconds(0, 0),
                Duration::ofSeconds(0, 1),
                Duration::ofSeconds(0, 2),
                Duration::ofSeconds(0, 999999999),
                Duration::ofSeconds(1, 0),
                Duration::ofSeconds(2, 0)]
        );
    }

    /**
     * @param $durations Duration[]
     */
    function doTest_comparisons_Duration($durations)
    {
        for ($i = 0; $i < count($durations); $i++) {
            $a = $durations[$i];
            for ($j = 0; $j < count($durations); $j++) {
                $b = $durations[$j];
                if ($i < $j) {
                    $this->assertEquals($a->compareTo($b) < 0, true, $a . " <=> " . $b);
                    $this->assertEquals($a->equals($b), false, $a . " <=> " . $b);
                } else if ($i > $j) {
                    $this->assertEquals($a->compareTo($b) > 0, true, $a . " <=> " . $b);
                    $this->assertEquals($a->equals($b), false, $a . " <=> " . $b);
                } else {
                    $this->assertEquals($a->compareTo($b), 0, $a . " <=> " . $b);
                    $this->assertEquals($a->equals($b), true, $a . " <=> " . $b);
                }
            }
        }
    }

    public function test_compareTo_ObjectNull()
    {
        TestHelper::assertNullException($this, function () {
            $a = Duration::ofSeconds(0, 0);
            $a->compareTo(null);
        });

    }

    public function test_compareToNonDuration()
    {
        TestHelper::assertTypeError($this, function () {
            $c = Duration::ofSeconds(0);
            $c->compareTo(new \stdClass());
        });
    }

//-----------------------------------------------------------------------
// equals()
//-----------------------------------------------------------------------

    public function test_equals()
    {
        $test5a = Duration::ofSeconds(5, 20);
        $test5b = Duration::ofSeconds(5, 20);
        $test5n = Duration::ofSeconds(5, 30);
        $test6 = Duration::ofSeconds(6, 20);

        $this->assertEquals($test5a->equals($test5a), true);
        $this->assertEquals($test5a->equals($test5b), true);
        $this->assertEquals($test5a->equals($test5n), false);
        $this->assertEquals($test5a->equals($test6), false);

        $this->assertEquals($test5b->equals($test5a), true);
        $this->assertEquals($test5b->equals($test5b), true);
        $this->assertEquals($test5b->equals($test5n), false);
        $this->assertEquals($test5b->equals($test6), false);

        $this->assertEquals($test5n->equals($test5a), false);
        $this->assertEquals($test5n->equals($test5b), false);
        $this->assertEquals($test5n->equals($test5n), true);
        $this->assertEquals($test5n->equals($test6), false);

        $this->assertEquals($test6->equals($test5a), false);
        $this->assertEquals($test6->equals($test5b), false);
        $this->assertEquals($test6->equals($test5n), false);
        $this->assertEquals($test6->equals($test6), true);
    }


    public function test_equals_null()
    {
        $test5 = Duration::ofSeconds(5, 20);
        $this->assertEquals($test5->equals(null), false);
    }


    public function test_equals_otherClass()
    {
        $test5 = Duration::ofSeconds(5, 20);
        $this->assertEquals($test5->equals(""), false);
    }

//-----------------------------------------------------------------------
    function provider_withNanos_int()
    {
        return [
            [0, 0, 0, 0, 0],
            [0, 0, 1, 0, 1],
            [0, 0, 999999999, 0, 999999999],

            [1, 0, 0, 1, 0],
            [1, 0, 1, 1, 1],
            [1, 0, 999999999, 1, 999999999],

            [-1, 0, 0, -1, 0],
            [-1, 0, 1, -1, 1],
            [-1, 0, 999999999, -1, 999999999],

            [1, 999999999, 0, 1, 0],
            [1, 999999999, 1, 1, 1],
            [1, 999999998, 2, 1, 2],

            [Long::MAX_VALUE, 0, 999999999, Long::MAX_VALUE, 999999999],
            [Long::MIN_VALUE, 0, 999999999, Long::MIN_VALUE, 999999999],
        ];
    }

    /**
     * @dataProvider provider_withNanos_int
     */
    public function test_withNanos_long($seconds, $nanos, $amount, $expectedSeconds, $expectedNanoOfSecond)
    {
        $t = Duration::ofSeconds($seconds, $nanos);
        $t = $t->withNanos($amount);
        $this->assertEquals($t->getSeconds(), $expectedSeconds);
        $this->assertEquals($t->getNano(), $expectedNanoOfSecond);
    }

    //-----------------------------------------------------------------------
    function provider_withSeconds_long()
    {
        return [
            [0, 0, 0, 0, 0],
            [0, 0, 1, 1, 0],
            [0, 0, -1, -1, 0],
            [0, 0, Long::MAX_VALUE, Long::MAX_VALUE, 0],
            [0, 0, Long::MIN_VALUE, Long::MIN_VALUE, 0],

            [1, 0, 0, 0, 0],
            [1, 0, 2, 2, 0],
            [1, 0, -1, -1, 0],
            [1, 0, Long::MAX_VALUE, Long::MAX_VALUE, 0],
            [1, 0, Long::MIN_VALUE, Long::MIN_VALUE, 0],

            [-1, 1, 0, 0, 1],
            [-1, 1, 1, 1, 1],
            [-1, 1, -1, -1, 1],
            [-1, 1, Long::MAX_VALUE, Long::MAX_VALUE, 1],
            [-1, 1, Long::MIN_VALUE, Long::MIN_VALUE, 1],
        ];
    }

    /**
     * @dataProvider provider_withSeconds_long
     */
    public function test_withSeconds_long($seconds, $nanos, $amount, $expectedSeconds, $expectedNanoOfSecond)
    {
        $t = Duration::ofSeconds($seconds, $nanos);
        $t = $t->withSeconds($amount);
        $this->assertEquals($t->getSeconds(), $expectedSeconds);
        $this->assertEquals($t->getNano(), $expectedNanoOfSecond);
    }

    //-----------------------------------------------------------------------
    // toString()
    //-----------------------------------------------------------------------
    function provider_toString()
    {
        return [
            [0, 0, "PT0S"],
            [0, 1, "PT0.000000001S"],
            [0, 10, "PT0.00000001S"],
            [0, 100, "PT0.0000001S"],
            [0, 1000, "PT0.000001S"],
            [0, 10000, "PT0.00001S"],
            [0, 100000, "PT0.0001S"],
            [0, 1000000, "PT0.001S"],
            [0, 10000000, "PT0.01S"],
            [0, 100000000, "PT0.1S"],
            [0, 120000000, "PT0.12S"],
            [0, 123000000, "PT0.123S"],
            [0, 123400000, "PT0.1234S"],
            [0, 123450000, "PT0.12345S"],
            [0, 123456000, "PT0.123456S"],
            [0, 123456700, "PT0.1234567S"],
            [0, 123456780, "PT0.12345678S"],
            [0, 123456789, "PT0.123456789S"],
            [1, 0, "PT1S"],
            [59, 0, "PT59S"],
            [60, 0, "PT1M"],
            [61, 0, "PT1M1S"],
            [3599, 0, "PT59M59S"],
            [3600, 0, "PT1H"],
            [3601, 0, "PT1H1S"],
            [3661, 0, "PT1H1M1S"],
            [86399, 0, "PT23H59M59S"],
            [86400, 0, "PT24H"],
            [59, 0, "PT59S"],
            [59, 0, "PT59S"],
            [-1, 0, "PT-1S"],
            [-1, 1000, "PT-0.999999S"],
            [-1, 900000000, "PT-0.1S"],
            [Long::MAX_VALUE, 0, "PT" . (Math::div(Long::MAX_VALUE, 3600)) . "H" .
                Math::div((Long::MAX_VALUE % 3600), 60) . "M" . (Long::MAX_VALUE % 60) . "S"],
            [Long::MIN_VALUE, 0, "PT" . (Math::div(Long::MIN_VALUE, 3600)) . "H" .
                Math::div((Long::MIN_VALUE % 3600), 60) . "M" . (Long::MIN_VALUE % 60) . "S"],
        ];
    }

    /**
     * @dataProvider provider_toString
     */
    public function test_toString($seconds, $nanos, $expected)
    {
        $t = Duration::ofSeconds($seconds, $nanos);
        $this->assertEquals($t->__toString(), $expected);
    }

    //-----------------------------------------------------------------------
    public function test_duration_getUnits()
    {
        $duration = Duration::ofSeconds(5000, 1000);
        $units = $duration->getUnits();
        $this->assertEquals(count($units), 2, "Period->getUnits length");
        $this->assertContains(CU::SECONDS(), $units, "Period->getUnits contains ChronoUnit.SECONDS");
        $this->assertContains(CU::NANOS(), $units, "contains ChronoUnit.NANOS");
    }

    public function test_getUnit()
    {
        $test = Duration::ofSeconds(2000, 1000);
        $seconds = $test->get(CU::SECONDS());
        $this->assertEquals($seconds, 2000, "duration->get(SECONDS)");
        $nanos = $test->get(CU::NANOS());
        $this->assertEquals($nanos, 1000, "duration->get(NANOS)");
    }

    function provider_factory_of_badTemporalUnit()
    {
        return [
            [0, CU::MICROS()],
            [0, CU::MILLIS()],
            [0, CU::MINUTES()],
            [0, CU::HOURS()],
            [0, CU::HALF_DAYS()],
            [0, CU::DAYS()],
            [0, CU::MONTHS()],
            [0, CU::YEARS()],
            [0, CU::DECADES()],
            [0, CU::CENTURIES()],
            [0, CU::MILLENNIA()],
        ];
    }

    /**
     * @expectedException \Celest\DateTimeException
     * @dataProvider provider_factory_of_badTemporalUnit
     */
    public function test_bad_getUnit($amount, TemporalUnit $unit)
    {
        $t = Duration::of($amount, $unit);
        $t->get($unit);
    }
}
