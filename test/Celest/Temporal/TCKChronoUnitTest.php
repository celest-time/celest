<?php
/*
 * Copyright (c) 2012, 2013, Oracle and/or its affiliates. All rights reserved.
 * DO NOT ALTER OR REMOVE COPYRIGHT NOTICES OR THIS FILE HEADER.
 *
 * This code is free software; you can redistribute it and/or modify it
 * under the terms of the GNU General Public License version 2 only, as
 * published by the Free Software Foundation.  Oracle designates this
 * particular file as subject to the "Classpath" exception as provided
 * by Oracle in the LICENSE file that accompanied this code.
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

namespace Celest\Temporal;

use Celest\LocalDate;
use Celest\LocalTime;
use Celest\Temporal\ChronoUnit as CU;

/**
 * Test.
 */
class TCKChronoUnit extends \PHPUnit_Framework_TestCase
{

    //-----------------------------------------------------------------------
    // $isDateBased(), $isTimeBased() and $isDurationEstimated()
    //-----------------------------------------------------------------------
    function data_chronoUnit()
    {
        return [
            [CU::FOREVER(), false, false, true],
            [CU::ERAS(), true, false, true],
            [CU::MILLENNIA(), true, false, true],
            [CU::CENTURIES(), true, false, true],
            [CU::DECADES(), true, false, true],
            [CU::YEARS(), true, false, true],
            [CU::MONTHS(), true, false, true],
            [CU::WEEKS(), true, false, true],
            [CU::DAYS(), true, false, true],

            [CU::HALF_DAYS(), false, true, false],
            [CU::HOURS(), false, true, false],
            [CU::MINUTES(), false, true, false],
            [CU::SECONDS(), false, true, false],
            [CU::MICROS(), false, true, false],
            [CU::MILLIS(), false, true, false],
            [CU::NANOS(), false, true, false],

        ];
    }

    /**
     * @dataProvider data_chronoUnit
     */
    public function test_unitType(ChronoUnit $unit, $isDateBased, $isTimeBased, $isDurationEstimated)
    {
        $this->assertEquals($unit->isDateBased(), $isDateBased);
        $this->assertEquals($unit->isTimeBased(), $isTimeBased);
        $this->assertEquals($unit->isDurationEstimated(), $isDurationEstimated);
    }

//-----------------------------------------------------------------------
// $isSupportedBy(), addTo() and between()
//-----------------------------------------------------------------------
    function data_unitAndTemporal()
    {
        return [
            [CU::CENTURIES(), LocalDate::of(2000, 1, 10), true, 1, LocalDate::of(2100, 1, 10)],
            [CU::DECADES(), LocalDate::of(2000, 1, 10), true, 1, LocalDate::of(2010, 1, 10)],
            [CU::YEARS(), LocalDate::of(2000, 1, 10), true, 1, LocalDate::of(2001, 1, 10)],
            [CU::MONTHS(), LocalDate::of(2000, 1, 10), true, 1, LocalDate::of(2000, 2, 10)],
            [CU::WEEKS(), LocalDate::of(2000, 1, 10), true, 1, LocalDate::of(2000, 1, 17)],
            [CU::DAYS(), LocalDate::of(2000, 1, 10), true, 1, LocalDate::of(2000, 1, 11)],

            [CU::HALF_DAYS(), LocalTime::of(1, 2, 3, 400), true, 1, LocalTime::of(13, 2, 3, 400)],
            [CU::HOURS(), LocalTime::of(1, 2, 3, 400), true, 1, LocalTime::of(2, 2, 3, 400)],
            [CU::MINUTES(), LocalTime::of(1, 2, 3, 400), true, 1, LocalTime::of(1, 3, 3, 400)],
            [CU::SECONDS(), LocalTime::of(1, 2, 3, 400), true, 1, LocalTime::of(1, 2, 4, 400)],
            [CU::MICROS(), LocalTime::of(1, 2, 3, 400), true, 1, LocalTime::of(1, 2, 3, 1000 + 400)],
            [CU::MILLIS(), LocalTime::of(1, 2, 3, 400), true, 1, LocalTime::of(1, 2, 3, 1000 * 1000 + 400)],
            [CU::NANOS(), LocalTime::of(1, 2, 3, 400), true, 1, LocalTime::of(1, 2, 3, 1 + 400)],

            [CU::CENTURIES(), LocalTime::of(1, 2, 3, 400), false, 1, null],
            [CU::DECADES(), LocalTime::of(1, 2, 3, 400), false, 1, null],
            [CU::YEARS(), LocalTime::of(1, 2, 3, 400), false, 1, null],
            [CU::MONTHS(), LocalTime::of(1, 2, 3, 400), false, 1, null],
            [CU::WEEKS(), LocalTime::of(1, 2, 3, 400), false, 1, null],
            [CU::DAYS(), LocalTime::of(1, 2, 3, 400), false, 1, null],

            [CU::HALF_DAYS(), LocalDate::of(2000, 2, 29), false, 1, null],
            [CU::HOURS(), LocalDate::of(2000, 2, 29), false, 1, null],
            [CU::MINUTES(), LocalDate::of(2000, 2, 29), false, 1, null],
            [CU::SECONDS(), LocalDate::of(2000, 2, 29), false, 1, null],
            [CU::MICROS(), LocalDate::of(2000, 2, 29), false, 1, null],
            [CU::MILLIS(), LocalDate::of(2000, 2, 29), false, 1, null],
            [CU::NANOS(), LocalDate::of(2000, 2, 29), false, 1, null],

        ];
    }

    /**
     * @dataProvider data_unitAndTemporal
     */
    public
    function test_unitAndTemporal(ChronoUnit $unit, Temporal $base, $isSupportedBy, $amount, $target)
    {
        $this->assertEquals($unit->isSupportedBy($base), $isSupportedBy);
        if ($isSupportedBy) {
            $result = $unit->addTo($base, $amount);
            $this->assertEquals($result, $target);
            $this->assertEquals($unit->between($base, $result), $amount);
        }
    }

//-----------------------------------------------------------------------
// valueOf()
//-----------------------------------------------------------------------
    public
    function test_valueOf()
    {
        $this->markTestIncomplete('ChronoUnit::values');
        foreach (ChronoUnit::values() as $unit) {
            $this->assertEquals(ChronoUnit::valueOf($unit->name()), $unit);
        }
    }
}
