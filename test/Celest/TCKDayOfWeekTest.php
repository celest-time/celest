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
use Celest\Temporal\ChronoField;
use Celest\Temporal\ChronoUnit;
use Celest\Temporal\TemporalAccessor;
use Celest\Temporal\TemporalQueries;
use Celest\Temporal\TemporalQuery;

/**
 * Test DayOfWeek::
 */
class TCKDayOfWeekTest extends AbstractDateTimeTest
{

    //-----------------------------------------------------------------------
    protected function samples()
    {
        return [DayOfWeek::MONDAY(), DayOfWeek::WEDNESDAY(), DayOfWeek::SUNDAY()];
    }

    protected function validFields()
    {
        return [Chronofield::DAY_OF_WEEK()];
    }

    protected function invalidFields()
    {
        /*List<TemporalField> list = new ArrayList<>(Arrays.<TemporalField>asList(ChronoField.values()));
                list.removeAll(validFields());
                list.add(JulianFields.JULIAN_DAY);
                list.add(JulianFields.MODIFIED_JULIAN_DAY);
                list.add(JulianFields.RATA_DIE);*/
        return [];
    }

    //-----------------------------------------------------------------------

    public function test_factory_int_singleton()
    {
        for ($i = 1; $i <= 7; $i++) {
            $test = DayOfWeek::of($i);
            $this->assertEquals($test->getValue(), $i);
            $this->assertSame(DayOfWeek::of($i), $test);
        }
    }

    /**
     * @expectedException \Celest\DateTimeException
     */
    public function test_factory_int_valueTooLow()
    {
        DayOfWeek::of(0);
    }

    /**
     * @expectedException \Celest\DateTimeException
     */
    public function test_factory_int_valueTooHigh()
    {
        DayOfWeek::of(8);
    }

    //-----------------------------------------------------------------------

    public function test_factory_CalendricalObject()
    {
        $this->assertEquals(DayOfWeek::from(LocalDate::of(2011, 6, 6)), DayOfWeek::MONDAY());
    }

    /**
     * @expectedException \Celest\DateTimeException
     */
    public function test_factory_CalendricalObject_invalid_noDerive()
    {
        DayOfWeek::from(LocalTime::of(12, 30));
    }

    public function test_factory_CalendricalObject_null()
    {
        TestHelper::assertNullException($this, function () {
            DayOfWeek::from(null);
        });
    }

    //-----------------------------------------------------------------------
    // isSupported(TemporalField)
    //-----------------------------------------------------------------------

    public function test_isSupported_TemporalField()
    {
        //$this->assertEquals(DayOfWeek::THURSDAY()->isSupported(null), false); TODO
        $this->assertEquals(DayOfWeek::THURSDAY()->isSupported(ChronoField::NANO_OF_SECOND()), false);
        $this->assertEquals(DayOfWeek::THURSDAY()->isSupported(ChronoField::NANO_OF_DAY()), false);
        $this->assertEquals(DayOfWeek::THURSDAY()->isSupported(ChronoField::MICRO_OF_SECOND()), false);
        $this->assertEquals(DayOfWeek::THURSDAY()->isSupported(ChronoField::MICRO_OF_DAY()), false);
        $this->assertEquals(DayOfWeek::THURSDAY()->isSupported(ChronoField::MILLI_OF_SECOND()), false);
        $this->assertEquals(DayOfWeek::THURSDAY()->isSupported(ChronoField::MILLI_OF_DAY()), false);
        $this->assertEquals(DayOfWeek::THURSDAY()->isSupported(ChronoField::SECOND_OF_MINUTE()), false);
        $this->assertEquals(DayOfWeek::THURSDAY()->isSupported(ChronoField::SECOND_OF_DAY()), false);
        $this->assertEquals(DayOfWeek::THURSDAY()->isSupported(ChronoField::MINUTE_OF_HOUR()), false);
        $this->assertEquals(DayOfWeek::THURSDAY()->isSupported(ChronoField::MINUTE_OF_DAY()), false);
        $this->assertEquals(DayOfWeek::THURSDAY()->isSupported(ChronoField::HOUR_OF_AMPM()), false);
        $this->assertEquals(DayOfWeek::THURSDAY()->isSupported(ChronoField::CLOCK_HOUR_OF_AMPM()), false);
        $this->assertEquals(DayOfWeek::THURSDAY()->isSupported(ChronoField::HOUR_OF_DAY()), false);
        $this->assertEquals(DayOfWeek::THURSDAY()->isSupported(ChronoField::CLOCK_HOUR_OF_DAY()), false);
        $this->assertEquals(DayOfWeek::THURSDAY()->isSupported(ChronoField::AMPM_OF_DAY()), false);
        $this->assertEquals(DayOfWeek::THURSDAY()->isSupported(ChronoField::DAY_OF_WEEK()), true);
        $this->assertEquals(DayOfWeek::THURSDAY()->isSupported(ChronoField::ALIGNED_DAY_OF_WEEK_IN_MONTH()), false);
        $this->assertEquals(DayOfWeek::THURSDAY()->isSupported(ChronoField::ALIGNED_DAY_OF_WEEK_IN_YEAR()), false);
        $this->assertEquals(DayOfWeek::THURSDAY()->isSupported(ChronoField::DAY_OF_MONTH()), false);
        $this->assertEquals(DayOfWeek::THURSDAY()->isSupported(ChronoField::DAY_OF_YEAR()), false);
        $this->assertEquals(DayOfWeek::THURSDAY()->isSupported(ChronoField::EPOCH_DAY()), false);
        $this->assertEquals(DayOfWeek::THURSDAY()->isSupported(ChronoField::ALIGNED_WEEK_OF_MONTH()), false);
        $this->assertEquals(DayOfWeek::THURSDAY()->isSupported(ChronoField::ALIGNED_WEEK_OF_YEAR()), false);
        $this->assertEquals(DayOfWeek::THURSDAY()->isSupported(ChronoField::MONTH_OF_YEAR()), false);
        $this->assertEquals(DayOfWeek::THURSDAY()->isSupported(ChronoField::PROLEPTIC_MONTH()), false);
        $this->assertEquals(DayOfWeek::THURSDAY()->isSupported(ChronoField::YEAR()), false);
        $this->assertEquals(DayOfWeek::THURSDAY()->isSupported(ChronoField::YEAR_OF_ERA()), false);
        $this->assertEquals(DayOfWeek::THURSDAY()->isSupported(ChronoField::ERA()), false);
        $this->assertEquals(DayOfWeek::THURSDAY()->isSupported(ChronoField::INSTANT_SECONDS()), false);
        $this->assertEquals(DayOfWeek::THURSDAY()->isSupported(ChronoField::OFFSET_SECONDS()), false);
    }

    //-----------------------------------------------------------------------
    // get(TemporalField)
    //-----------------------------------------------------------------------

    public function test_get_TemporalField()
    {
        $this->assertEquals(DayOfWeek::WEDNESDAY()->getLong(ChronoField::DAY_OF_WEEK()), 3);
    }


    public function test_getLong_TemporalField()
    {
        $this->assertEquals(DayOfWeek::WEDNESDAY()->getLong(ChronoField::DAY_OF_WEEK()), 3);
    }

    //-----------------------------------------------------------------------
    // $query(TemporalQuery)
    //-----------------------------------------------------------------------
    function data_query()
    {
        return [
            [DayOfWeek::FRIDAY(), TemporalQueries::chronology(), null],
            [DayOfWeek::FRIDAY(), TemporalQueries::zoneId(), null],
            [DayOfWeek::FRIDAY(), TemporalQueries::precision(), ChronoUnit::DAYS()],
            [DayOfWeek::FRIDAY(), TemporalQueries::zone(), null],
            [DayOfWeek::FRIDAY(), TemporalQueries::offset(), null],
            [DayOfWeek::FRIDAY(), TemporalQueries::localDate(), null],
            [DayOfWeek::FRIDAY(), TemporalQueries::localTime(), null],
        ];
    }

    /**
     * @dataProvider data_query
     */
    public function test_query(TemporalAccessor $temporal, TemporalQuery $query, $expected)
    {
        $this->assertEquals($temporal->query($query), $expected);
    }

    /**
     * @dataProvider data_query
     */
    public function test_queryFrom(TemporalAccessor $temporal, TemporalQuery $query, $expected)
    {
        $this->assertEquals($query->queryFrom($temporal), $expected);
    }

    public
    function test_query_null()
    {
        TestHelper::assertNullException($this, function () {
            DayOfWeek::FRIDAY()->query(null);
        });
    }

//-----------------------------------------------------------------------
// getText()
//-----------------------------------------------------------------------

    public
    function test_getText()
    {
        $this->assertEquals(DayOfWeek::MONDAY()->getDisplayName(TextStyle::SHORT(), Locale::US()), "Mon");
    }

    public
    function test_getText_nullStyle()
    {
        TestHelper::assertNullException($this, function () {
            DayOfWeek::MONDAY()->getDisplayName(null, Locale::US());
        });
    }

    public
    function test_getText_nullLocale()
    {
        TestHelper::assertNullException($this, function () {
            DayOfWeek::MONDAY()->getDisplayName(TextStyle::FULL(), null);
        });
    }

//-----------------------------------------------------------------------
// plus(long), plus(long,unit)
//-----------------------------------------------------------------------
    function data_plus()
    {
        return [
            [1, -8, 7],
            [1, -7, 1],
            [1, -6, 2],
            [1, -5, 3],
            [1, -4, 4],
            [1, -3, 5],
            [1, -2, 6],
            [1, -1, 7],
            [1, 0, 1],
            [1, 1, 2],
            [1, 2, 3],
            [1, 3, 4],
            [1, 4, 5],
            [1, 5, 6],
            [1, 6, 7],
            [1, 7, 1],
            [1, 8, 2],

            [1, 1, 2],
            [2, 1, 3],
            [3, 1, 4],
            [4, 1, 5],
            [5, 1, 6],
            [6, 1, 7],
            [7, 1, 1],

            [1, -1, 7],
            [2, -1, 1],
            [3, -1, 2],
            [4, -1, 3],
            [5, -1, 4],
            [6, -1, 5],
            [7, -1, 6],
        ];
    }

    /**
     * @dataProvider data_plus
     */
    public function test_plus_long($base, $amount, $expected)
    {
        $this->assertEquals(DayOfWeek::of($base)->plus($amount), DayOfWeek::of($expected));
    }

//-----------------------------------------------------------------------
// minus(long), minus(long,unit)
//-----------------------------------------------------------------------
    function data_minus()
    {
        return [
            [1, -8, 2],
            [1, -7, 1],
            [1, -6, 7],
            [1, -5, 6],
            [1, -4, 5],
            [1, -3, 4],
            [1, -2, 3],
            [1, -1, 2],
            [1, 0, 1],
            [1, 1, 7],
            [1, 2, 6],
            [1, 3, 5],
            [1, 4, 4],
            [1, 5, 3],
            [1, 6, 2],
            [1, 7, 1],
            [1, 8, 7],
        ];
    }

    /**
     * @dataProvider data_minus
     */
    public function test_minus_long($base, $amount, $expected)
    {
        $this->assertEquals(DayOfWeek::of($base)->minus($amount), DayOfWeek::of($expected));
    }

    //-----------------------------------------------------------------------
    // adjustInto()
    //-----------------------------------------------------------------------

    public function test_adjustInto()
    {
        $this->assertEquals(DayOfWeek::MONDAY()->adjustInto(LocalDate::of(2012, 9, 2)), LocalDate::of(2012, 8, 27));
        $this->assertEquals(DayOfWeek::MONDAY()->adjustInto(LocalDate::of(2012, 9, 3)), LocalDate::of(2012, 9, 3));
        $this->assertEquals(DayOfWeek::MONDAY()->adjustInto(LocalDate::of(2012, 9, 4)), LocalDate::of(2012, 9, 3));
        $this->assertEquals(DayOfWeek::MONDAY()->adjustInto(LocalDate::of(2012, 9, 10)), LocalDate::of(2012, 9, 10));
        $this->assertEquals(DayOfWeek::MONDAY()->adjustInto(LocalDate::of(2012, 9, 11)), LocalDate::of(2012, 9, 10));
    }

    public function test_adjustInto_null()
    {
        TestHelper::assertNullException($this, function () {
            DayOfWeek::MONDAY()->adjustInto(null);
        });
    }

    //-----------------------------------------------------------------------
    // toString()
    //-----------------------------------------------------------------------

    public function test_toString()
    {
        $this->assertEquals(DayOfWeek::MONDAY()->__toString(), "MONDAY");
        $this->assertEquals(DayOfWeek::TUESDAY()->__toString(), "TUESDAY");
        $this->assertEquals(DayOfWeek::WEDNESDAY()->__toString(), "WEDNESDAY");
        $this->assertEquals(DayOfWeek::THURSDAY()->__toString(), "THURSDAY");
        $this->assertEquals(DayOfWeek::FRIDAY()->__toString(), "FRIDAY");
        $this->assertEquals(DayOfWeek::SATURDAY()->__toString(), "SATURDAY");
        $this->assertEquals(DayOfWeek::SUNDAY()->__toString(), "SUNDAY");
    }

    //-----------------------------------------------------------------------
    // generated methods
    //-----------------------------------------------------------------------

    public function test_enum()
    {
        $this->assertEquals(DayOfWeek::valueOf("MONDAY"), DayOfWeek::MONDAY());
        $this->assertEquals(DayOfWeek::values()[0], DayOfWeek::MONDAY());
    }

}
