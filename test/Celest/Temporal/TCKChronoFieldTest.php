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
use Celest\LocalDateTime;
use Celest\LocalTime;
use Celest\Temporal\ChronoField as CF;
use Celest\Temporal\ChronoUnit as CU;

/**
 * Test.
 */
class TCKChronoFieldTest extends \PHPUnit_Framework_TestCase
{

    //-----------------------------------------------------------------------
    // getBaseUnit() and getRangeUnit()
    //-----------------------------------------------------------------------
    function data_fieldUnit()
    {
        return [
            [CF::YEAR(), CU::YEARS(), CU::FOREVER()],
            [CF::MONTH_OF_YEAR(), CU::MONTHS(), CU::YEARS()],
            [CF::DAY_OF_MONTH(), CU::DAYS(), CU::MONTHS()],
            [CF::DAY_OF_WEEK(), CU::DAYS(), CU::WEEKS()],
            [CF::DAY_OF_YEAR(), CU::DAYS(), CU::YEARS()],
            [CF::HOUR_OF_DAY(), CU::HOURS(), CU::DAYS()],
            [CF::MINUTE_OF_DAY(), CU::MINUTES(), CU::DAYS()],
            [CF::MINUTE_OF_HOUR(), CU::MINUTES(), CU::HOURS()],
            [CF::SECOND_OF_DAY(), CU::SECONDS(), CU::DAYS()],
            [CF::SECOND_OF_MINUTE(), CU::SECONDS(), CU::MINUTES()],
            [CF::MILLI_OF_DAY(), CU::MILLIS(), CU::DAYS()],
            [CF::MILLI_OF_SECOND(), CU::MILLIS(), CU::SECONDS()],
            [CF::MICRO_OF_SECOND(), CU::MICROS(), CU::SECONDS()],
            [CF::MICRO_OF_DAY(), CU::MICROS(), CU::DAYS()],
            [CF::NANO_OF_SECOND(), CU::NANOS(), CU::SECONDS()],
            [CF::NANO_OF_DAY(), CU::NANOS(), CU::DAYS()],

        ];
    }

    /**
     * @dataProvider data_fieldUnit
     */
    public function test_getBaseUnit(ChronoField $field, ChronoUnit $baseUnit, ChronoUnit $rangeUnit)
    {
        $this->assertEquals($field->getBaseUnit(), $baseUnit);
        $this->assertEquals($field->getRangeUnit(), $rangeUnit);
    }

//-----------------------------------------------------------------------
// $isDateBased() and $isTimeBased()
//-----------------------------------------------------------------------
    function data_fieldBased()
    {
        return [
            [
                CF::DAY_OF_WEEK(), true, false],
            [
                CF::ALIGNED_DAY_OF_WEEK_IN_MONTH(), true, false],
            [
                CF::ALIGNED_DAY_OF_WEEK_IN_YEAR(), true, false],
            [
                CF::DAY_OF_MONTH(), true, false],
            [
                CF::DAY_OF_YEAR(), true, false],
            [
                CF::EPOCH_DAY(), true, false],
            [
                CF::ALIGNED_WEEK_OF_MONTH(), true, false],
            [
                CF::ALIGNED_WEEK_OF_YEAR(), true, false],
            [
                CF::MONTH_OF_YEAR(), true, false],
            [
                CF::PROLEPTIC_MONTH(), true, false],
            [
                CF::YEAR_OF_ERA(), true, false],
            [
                CF::YEAR(), true, false],
            [
                CF::ERA(), true, false],

            [
                CF::AMPM_OF_DAY(), false, true],
            [
                CF::CLOCK_HOUR_OF_DAY(), false, true],
            [
                CF::HOUR_OF_DAY(), false, true],
            [
                CF::CLOCK_HOUR_OF_AMPM(), false, true],
            [
                CF::HOUR_OF_AMPM(), false, true],
            [
                CF::MINUTE_OF_DAY(), false, true],
            [
                CF::MINUTE_OF_HOUR(), false, true],
            [
                CF::SECOND_OF_DAY(), false, true],
            [
                CF::SECOND_OF_MINUTE(), false, true],
            [
                CF::MILLI_OF_DAY(), false, true],
            [
                CF::MILLI_OF_SECOND(), false, true],
            [
                CF::MICRO_OF_DAY(), false, true],
            [
                CF::MICRO_OF_SECOND(), false, true],
            [
                CF::NANO_OF_DAY(), false, true],
            [
                CF::NANO_OF_SECOND(), false, true],
        ];
    }

    /**
     * @dataProvider data_fieldBased
     */
    public function test_isDateBased(ChronoField $field, $isDateBased, $isTimeBased)
    {
        $this->assertEquals($field->isDateBased(), $isDateBased);
        $this->assertEquals($field->isTimeBased(), $isTimeBased);
    }

    //-----------------------------------------------------------------------
    // isSupportedBy(TemporalAccessor temporal) and getFrom(TemporalAccessor temporal)
    //-----------------------------------------------------------------------
    function data_fieldAndAccessor()
    {
        return
            [
                [
                    CF::YEAR(), LocalDate::of(2000, 2, 29), true, 2000],
                [
                    CF::YEAR(), LocalDateTime::of(2000, 2, 29, 5, 4, 3, 200), true, 2000],
                [
                    CF::MONTH_OF_YEAR(), LocalDate::of(2000, 2, 29), true, 2],
                [
                    CF::MONTH_OF_YEAR(), LocalDateTime::of(2000, 2, 29, 5, 4, 3, 200), true, 2],
                [
                    CF::DAY_OF_MONTH(), LocalDate::of(2000, 2, 29), true, 29],
                [
                    CF::DAY_OF_MONTH(), LocalDateTime::of(2000, 2, 29, 5, 4, 3, 200), true, 29],
                [
                    CF::DAY_OF_YEAR(), LocalDate::of(2000, 2, 29), true, 60],
                [
                    CF::DAY_OF_YEAR(), LocalDateTime::of(2000, 2, 29, 5, 4, 3, 200), true, 60],

                [
                    CF::HOUR_OF_DAY(), LocalTime::of(5, 4, 3, 200), true, 5],
                [
                    CF::HOUR_OF_DAY(), LocalDateTime::of(2000, 2, 29, 5, 4, 3, 200), true, 5],

                [
                    CF::MINUTE_OF_DAY(), LocalTime::of(5, 4, 3, 200), true, 5 * 60 + 4],
                [
                    CF::MINUTE_OF_DAY(), LocalDateTime::of(2000, 2, 29, 5, 4, 3, 200), true, 5 * 60 + 4],
                [
                    CF::MINUTE_OF_HOUR(), LocalTime::of(5, 4, 3, 200), true, 4],
                [
                    CF::MINUTE_OF_HOUR(), LocalDateTime::of(2000, 2, 29, 5, 4, 3, 200), true, 4],

                [
                    CF::SECOND_OF_DAY(), LocalTime::of(5, 4, 3, 200), true, 5 * 3600 + 4 * 60 + 3],
                [
                    CF::SECOND_OF_DAY(), LocalDateTime::of(2000, 2, 29, 5, 4, 3, 200), true, 5 * 3600 + 4 * 60 + 3],
                [
                    CF::SECOND_OF_MINUTE(), LocalTime::of(5, 4, 3, 200), true, 3],
                [
                    CF::SECOND_OF_MINUTE(), LocalDateTime::of(2000, 2, 29, 5, 4, 3, 200), true, 3],

                [
                    CF::NANO_OF_SECOND(), LocalTime::of(5, 4, 3, 200), true, 200],
                [
                    CF::NANO_OF_SECOND(), LocalDateTime::of(2000, 2, 29, 5, 4, 3, 200), true, 200],

                [
                    CF::YEAR(), LocalTime::of(5, 4, 3, 200), false, -1],
                [
                    CF::MONTH_OF_YEAR(), LocalTime::of(5, 4, 3, 200), false, -1],
                [
                    CF::DAY_OF_MONTH(), LocalTime::of(5, 4, 3, 200), false, -1],
                [
                    CF::DAY_OF_YEAR(), LocalTime::of(5, 4, 3, 200), false, -1],
                [
                    CF::HOUR_OF_DAY(), LocalDate::of(2000, 2, 29), false, -1],
                [
                    CF::MINUTE_OF_DAY(), LocalDate::of(2000, 2, 29), false, -1],
                [
                    CF::MINUTE_OF_HOUR(), LocalDate::of(2000, 2, 29), false, -1],
                [
                    CF::SECOND_OF_DAY(), LocalDate::of(2000, 2, 29), false, -1],
                [
                    CF::SECOND_OF_MINUTE(), LocalDate::of(2000, 2, 29), false, -1],
                [
                    CF::NANO_OF_SECOND(), LocalDate::of(2000, 2, 29), false, -1],
            ];
    }

    /**
     * @dataProvider data_fieldAndAccessor
     */
    public function test_supportedAccessor(ChronoField $field, TemporalAccessor $accessor, $isSupported, $value)
    {
        $this->assertEquals($field->isSupportedBy($accessor), $isSupported);
        if ($isSupported) {
            $this->assertEquals($field->getFrom($accessor), $value);
        }
    }

//-----------------------------------------------------------------------
// range() and rangeRefinedBy(TemporalAccessor temporal)
//-----------------------------------------------------------------------
    public function test_range()
    {
        $this->assertEquals(CF::MONTH_OF_YEAR()->range(), ValueRange::of(1, 12));
        $this->assertEquals(CF::MONTH_OF_YEAR()->rangeRefinedBy(LocalDate::of(2000, 2, 29)), ValueRange::of(1, 12));

        $this->assertEquals(CF::DAY_OF_MONTH()->range(), ValueRange::ofVariable(1, 28, 31));
        $this->assertEquals(CF::DAY_OF_MONTH()->rangeRefinedBy(LocalDate::of(2000, 2, 29)), ValueRange::of(1, 29));
    }

    //-----------------------------------------------------------------------
    // valueOf()
    //-----------------------------------------------------------------------
    public function test_valueOf()
    {
        $this->markTestIncomplete('ChronoField::values');

        foreach (ChronoField::values() as $field) {
            $this->assertEquals(ChronoField::valueOf($field->name()), $field);
        }
    }
}
