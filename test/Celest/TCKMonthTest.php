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

use Celest\Chrono\IsoChronology;
use Celest\Format\TextStyle;
use Celest\Temporal\ChronoField as CF;
use Celest\Temporal\ChronoField;
use Celest\Temporal\ChronoUnit;
use Celest\Temporal\JulianFields;
use Celest\Temporal\TemporalAccessor;
use Celest\Temporal\TemporalQueries;
use Celest\Temporal\TemporalQuery;

/**
 * Test Month.
 */
class TCKMonthTest extends AbstractDateTimeTest
{

    const MAX_LENGTH = 12;

    //-----------------------------------------------------------------------
    protected function samples()
    {
        return [Month::JANUARY(), Month::JUNE(), Month::DECEMBER()];
    }

    protected function validFields()
    {
        return [CF::MONTH_OF_YEAR()];
    }

    protected function invalidFields()
    {
        $list = array_diff(ChronoField::values(), $this->validFields());
        $list[] = JulianFields::JULIAN_DAY();
        $list[] = JulianFields::MODIFIED_JULIAN_DAY();
        $list[] = JulianFields::RATA_DIE();
        return $list;
    }

//-----------------------------------------------------------------------

    public function test_factory_int_singleton()
    {
        for ($i = 1; $i <= self::MAX_LENGTH; $i++) {
            $test = Month::of($i);
            $this->assertEquals($test->getValue(), $i);
        }
    }

    /**
     * @expectedException \Celest\DateTimeException
     */
    public function test_factory_int_tooLow()
    {
        Month::of(0);
    }

    /**
     * @expectedException \Celest\DateTimeException
     */
    public function test_factory_int_tooHigh()
    {
        Month::of(13);
    }

    //-----------------------------------------------------------------------

    public function test_factory_CalendricalObject()
    {
        $this->assertEquals(Month::from(LocalDate::of(2011, 6, 6)), Month::JUNE());
    }

    /**
     * @expectedException \Celest\DateTimeException
     */
    public function test_factory_CalendricalObject_invalid_noDerive()
    {
        Month::from(LocalTime::of(12, 30));
    }

    public function test_factory_CalendricalObject_null()
    {
        TestHelper::assertNullException($this, function () {
            Month::from(null);
        });
    }

    //-----------------------------------------------------------------------
    // isSupported(TemporalField)
    //-----------------------------------------------------------------------

    public function test_isSupported_TemporalField()
    {
        //$this->assertEquals(Month::AUGUST()->isSupported(null), false); TODO
        $this->assertEquals(Month::AUGUST()->isSupported(ChronoField::NANO_OF_SECOND()), false);
        $this->assertEquals(Month::AUGUST()->isSupported(ChronoField::NANO_OF_DAY()), false);
        $this->assertEquals(Month::AUGUST()->isSupported(ChronoField::MICRO_OF_SECOND()), false);
        $this->assertEquals(Month::AUGUST()->isSupported(ChronoField::MICRO_OF_DAY()), false);
        $this->assertEquals(Month::AUGUST()->isSupported(ChronoField::MILLI_OF_SECOND()), false);
        $this->assertEquals(Month::AUGUST()->isSupported(ChronoField::MILLI_OF_DAY()), false);
        $this->assertEquals(Month::AUGUST()->isSupported(ChronoField::SECOND_OF_MINUTE()), false);
        $this->assertEquals(Month::AUGUST()->isSupported(ChronoField::SECOND_OF_DAY()), false);
        $this->assertEquals(Month::AUGUST()->isSupported(ChronoField::MINUTE_OF_HOUR()), false);
        $this->assertEquals(Month::AUGUST()->isSupported(ChronoField::MINUTE_OF_DAY()), false);
        $this->assertEquals(Month::AUGUST()->isSupported(ChronoField::HOUR_OF_AMPM()), false);
        $this->assertEquals(Month::AUGUST()->isSupported(ChronoField::CLOCK_HOUR_OF_AMPM()), false);
        $this->assertEquals(Month::AUGUST()->isSupported(ChronoField::HOUR_OF_DAY()), false);
        $this->assertEquals(Month::AUGUST()->isSupported(ChronoField::CLOCK_HOUR_OF_DAY()), false);
        $this->assertEquals(Month::AUGUST()->isSupported(ChronoField::AMPM_OF_DAY()), false);
        $this->assertEquals(Month::AUGUST()->isSupported(ChronoField::DAY_OF_WEEK()), false);
        $this->assertEquals(Month::AUGUST()->isSupported(ChronoField::ALIGNED_DAY_OF_WEEK_IN_MONTH()), false);
        $this->assertEquals(Month::AUGUST()->isSupported(ChronoField::ALIGNED_DAY_OF_WEEK_IN_YEAR()), false);
        $this->assertEquals(Month::AUGUST()->isSupported(ChronoField::DAY_OF_MONTH()), false);
        $this->assertEquals(Month::AUGUST()->isSupported(ChronoField::DAY_OF_YEAR()), false);
        $this->assertEquals(Month::AUGUST()->isSupported(ChronoField::EPOCH_DAY()), false);
        $this->assertEquals(Month::AUGUST()->isSupported(ChronoField::ALIGNED_WEEK_OF_MONTH()), false);
        $this->assertEquals(Month::AUGUST()->isSupported(ChronoField::ALIGNED_WEEK_OF_YEAR()), false);
        $this->assertEquals(Month::AUGUST()->isSupported(ChronoField::MONTH_OF_YEAR()), true);
        $this->assertEquals(Month::AUGUST()->isSupported(ChronoField::PROLEPTIC_MONTH()), false);
        $this->assertEquals(Month::AUGUST()->isSupported(ChronoField::YEAR()), false);
        $this->assertEquals(Month::AUGUST()->isSupported(ChronoField::YEAR_OF_ERA()), false);
        $this->assertEquals(Month::AUGUST()->isSupported(ChronoField::ERA()), false);
        $this->assertEquals(Month::AUGUST()->isSupported(ChronoField::INSTANT_SECONDS()), false);
        $this->assertEquals(Month::AUGUST()->isSupported(ChronoField::OFFSET_SECONDS()), false);
    }

    //-----------------------------------------------------------------------
    // get(TemporalField)
    //-----------------------------------------------------------------------

    public function test_get_TemporalField()
    {
        $this->assertEquals(Month::JULY()->get(ChronoField::MONTH_OF_YEAR()), 7);
    }


    public function test_getLong_TemporalField()
    {
        $this->assertEquals(Month::JULY()->getLong(ChronoField::MONTH_OF_YEAR()), 7);
    }

    //-----------------------------------------------------------------------
    // $query(TemporalQuery)
    //-----------------------------------------------------------------------
    function data_query()
    {
        return
            [
                [Month::JUNE(), TemporalQueries::chronology(), IsoChronology::INSTANCE()],
                [Month::JUNE(), TemporalQueries::zoneId(), null],
                [Month::JUNE(), TemporalQueries::precision(), ChronoUnit::MONTHS()],
                [Month::JUNE(), TemporalQueries::zone(), null],
                [Month::JUNE(), TemporalQueries::offset(), null],
                [Month::JUNE(), TemporalQueries::localDate(), null],
                [Month::JUNE(), TemporalQueries::localTime(), null],
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

    public function test_query_null()
    {
        TestHelper::assertNullException($this, function () {
            Month::JUNE()->query(null);
        });
    }

    //-----------------------------------------------------------------------
    // getText()
    //-----------------------------------------------------------------------

    public function test_getText()
    {
        $this->assertEquals(Month::JANUARY()->getDisplayName(TextStyle::SHORT(), Locale::US()), "Jan");
    }

    public function test_getText_nullStyle()
    {
        TestHelper::assertNullException($this, function () {
            Month::JANUARY()->getDisplayName(null, Locale::US());
        });
    }

    public function test_getText_nullLocale()
    {
        TestHelper::assertNullException($this, function () {
            Month::JANUARY()->getDisplayName(TextStyle::FULL(), null);
        });
    }

    //-----------------------------------------------------------------------
    // plus(long), plus(long,unit)
    //-----------------------------------------------------------------------
    function data_plus()
    {
        return
            [
                [1, -13, 12
                ],
                [
                    1, -12, 1],
                [
                    1, -11, 2],
                [
                    1, -10, 3],
                [
                    1, -9, 4],
                [
                    1, -8, 5],
                [
                    1, -7, 6],
                [
                    1, -6, 7],
                [
                    1, -5, 8],
                [
                    1, -4, 9],
                [
                    1, -3, 10],
                [
                    1, -2, 11],
                [
                    1, -1, 12],
                [
                    1, 0, 1],
                [
                    1, 1, 2],
                [
                    1, 2, 3],
                [
                    1, 3, 4],
                [
                    1, 4, 5],
                [
                    1, 5, 6],
                [
                    1, 6, 7],
                [
                    1, 7, 8],
                [
                    1, 8, 9],
                [
                    1, 9, 10],
                [
                    1, 10, 11],
                [
                    1, 11, 12],
                [
                    1, 12, 1],
                [
                    1, 13, 2],

                [
                    1, 1, 2],
                [
                    2, 1, 3],
                [
                    3, 1, 4],
                [
                    4, 1, 5],
                [
                    5, 1, 6],
                [
                    6, 1, 7],
                [
                    7, 1, 8],
                [
                    8, 1, 9],
                [
                    9, 1, 10],
                [
                    10, 1, 11],
                [
                    11, 1, 12],
                [
                    12, 1, 1],

                [
                    1, -1, 12],
                [
                    2, -1, 1],
                [
                    3, -1, 2],
                [
                    4, -1, 3],
                [
                    5, -1, 4],
                [
                    6, -1, 5],
                [
                    7, -1, 6],
                [
                    8, -1, 7],
                [
                    9, -1, 8],
                [
                    10, -1, 9],
                [
                    11, -1, 10],
                [
                    12, -1, 11],
            ];
    }

    /**
     * @dataProvider data_plus
     */
    public function test_plus_long($base, $amount, $expected)
    {
        $this->assertEquals(Month::of($base)->plus($amount), Month::of($expected));
    }

//-----------------------------------------------------------------------
// minus(long), minus(long,unit)
//-----------------------------------------------------------------------
    function data_minus()
    {
        return [
            [
                1, -13, 2],
            [
                1, -12, 1],
            [
                1, -11, 12],
            [
                1, -10, 11],
            [
                1, -9, 10],
            [
                1, -8, 9],
            [
                1, -7, 8],
            [
                1, -6, 7],
            [
                1, -5, 6],
            [
                1, -4, 5],
            [
                1, -3, 4],
            [
                1, -2, 3],
            [
                1, -1, 2],
            [
                1, 0, 1],
            [
                1, 1, 12],
            [
                1, 2, 11],
            [
                1, 3, 10],
            [
                1, 4, 9],
            [
                1, 5, 8],
            [
                1, 6, 7],
            [
                1, 7, 6],
            [
                1, 8, 5],
            [
                1, 9, 4],
            [
                1, 10, 3],
            [
                1, 11, 2],
            [
                1, 12, 1],
            [
                1, 13, 12],
        ];
    }

    /**
     * @dataProvider data_minus
     */
    public function test_minus_long( $base,  $amount,  $expected)
    {
        $this->assertEquals(Month::of($base)->minus($amount), Month::of($expected));
    }

    //-----------------------------------------------------------------------
    // length(boolean)
    //-----------------------------------------------------------------------

    public function test_length_boolean_notLeapYear()
    {
        $this->assertEquals(Month::JANUARY()->length(false), 31);
        $this->assertEquals(Month::FEBRUARY()->length(false), 28);
        $this->assertEquals(Month::MARCH()->length(false), 31);
        $this->assertEquals(Month::APRIL()->length(false), 30);
        $this->assertEquals(Month::MAY()->length(false), 31);
        $this->assertEquals(Month::JUNE()->length(false), 30);
        $this->assertEquals(Month::JULY()->length(false), 31);
        $this->assertEquals(Month::AUGUST()->length(false), 31);
        $this->assertEquals(Month::SEPTEMBER()->length(false), 30);
        $this->assertEquals(Month::OCTOBER()->length(false), 31);
        $this->assertEquals(Month::NOVEMBER()->length(false), 30);
        $this->assertEquals(Month::DECEMBER()->length(false), 31);
    }


    public function test_length_boolean_leapYear()
    {
        $this->assertEquals(Month::JANUARY()->length(true), 31);
        $this->assertEquals(Month::FEBRUARY()->length(true), 29);
        $this->assertEquals(Month::MARCH()->length(true), 31);
        $this->assertEquals(Month::APRIL()->length(true), 30);
        $this->assertEquals(Month::MAY()->length(true), 31);
        $this->assertEquals(Month::JUNE()->length(true), 30);
        $this->assertEquals(Month::JULY()->length(true), 31);
        $this->assertEquals(Month::AUGUST()->length(true), 31);
        $this->assertEquals(Month::SEPTEMBER()->length(true), 30);
        $this->assertEquals(Month::OCTOBER()->length(true), 31);
        $this->assertEquals(Month::NOVEMBER()->length(true), 30);
        $this->assertEquals(Month::DECEMBER()->length(true), 31);
    }

    //-----------------------------------------------------------------------
    // minLength()
    //-----------------------------------------------------------------------

    public function test_minLength()
    {
        $this->assertEquals(Month::JANUARY()->minLength(), 31);
        $this->assertEquals(Month::FEBRUARY()->minLength(), 28);
        $this->assertEquals(Month::MARCH()->minLength(), 31);
        $this->assertEquals(Month::APRIL()->minLength(), 30);
        $this->assertEquals(Month::MAY()->minLength(), 31);
        $this->assertEquals(Month::JUNE()->minLength(), 30);
        $this->assertEquals(Month::JULY()->minLength(), 31);
        $this->assertEquals(Month::AUGUST()->minLength(), 31);
        $this->assertEquals(Month::SEPTEMBER()->minLength(), 30);
        $this->assertEquals(Month::OCTOBER()->minLength(), 31);
        $this->assertEquals(Month::NOVEMBER()->minLength(), 30);
        $this->assertEquals(Month::DECEMBER()->minLength(), 31);
    }

    //-----------------------------------------------------------------------
    // maxLength()
    //-----------------------------------------------------------------------

    public function test_maxLength()
    {
        $this->assertEquals(Month::JANUARY()->maxLength(), 31);
        $this->assertEquals(Month::FEBRUARY()->maxLength(), 29);
        $this->assertEquals(Month::MARCH()->maxLength(), 31);
        $this->assertEquals(Month::APRIL()->maxLength(), 30);
        $this->assertEquals(Month::MAY()->maxLength(), 31);
        $this->assertEquals(Month::JUNE()->maxLength(), 30);
        $this->assertEquals(Month::JULY()->maxLength(), 31);
        $this->assertEquals(Month::AUGUST()->maxLength(), 31);
        $this->assertEquals(Month::SEPTEMBER()->maxLength(), 30);
        $this->assertEquals(Month::OCTOBER()->maxLength(), 31);
        $this->assertEquals(Month::NOVEMBER()->maxLength(), 30);
        $this->assertEquals(Month::DECEMBER()->maxLength(), 31);
    }

    //-----------------------------------------------------------------------
    // firstDayOfYear(boolean)
    //-----------------------------------------------------------------------

    public function test_firstDayOfYear_notLeapYear()
    {
        $this->assertEquals(Month::JANUARY()->firstDayOfYear(false), 1);
        $this->assertEquals(Month::FEBRUARY()->firstDayOfYear(false), 1 + 31);
        $this->assertEquals(Month::MARCH()->firstDayOfYear(false), 1 + 31 + 28);
        $this->assertEquals(Month::APRIL()->firstDayOfYear(false), 1 + 31 + 28 + 31);
        $this->assertEquals(Month::MAY()->firstDayOfYear(false), 1 + 31 + 28 + 31 + 30);
        $this->assertEquals(Month::JUNE()->firstDayOfYear(false), 1 + 31 + 28 + 31 + 30 + 31);
        $this->assertEquals(Month::JULY()->firstDayOfYear(false), 1 + 31 + 28 + 31 + 30 + 31 + 30);
        $this->assertEquals(Month::AUGUST()->firstDayOfYear(false), 1 + 31 + 28 + 31 + 30 + 31 + 30 + 31);
        $this->assertEquals(Month::SEPTEMBER()->firstDayOfYear(false), 1 + 31 + 28 + 31 + 30 + 31 + 30 + 31 + 31);
        $this->assertEquals(Month::OCTOBER()->firstDayOfYear(false), 1 + 31 + 28 + 31 + 30 + 31 + 30 + 31 + 31 + 30);
        $this->assertEquals(Month::NOVEMBER()->firstDayOfYear(false), 1 + 31 + 28 + 31 + 30 + 31 + 30 + 31 + 31 + 30 + 31);
        $this->assertEquals(Month::DECEMBER()->firstDayOfYear(false), 1 + 31 + 28 + 31 + 30 + 31 + 30 + 31 + 31 + 30 + 31 + 30);
    }


    public function test_firstDayOfYear_leapYear()
    {
        $this->assertEquals(Month::JANUARY()->firstDayOfYear(true), 1);
        $this->assertEquals(Month::FEBRUARY()->firstDayOfYear(true), 1 + 31);
        $this->assertEquals(Month::MARCH()->firstDayOfYear(true), 1 + 31 + 29);
        $this->assertEquals(Month::APRIL()->firstDayOfYear(true), 1 + 31 + 29 + 31);
        $this->assertEquals(Month::MAY()->firstDayOfYear(true), 1 + 31 + 29 + 31 + 30);
        $this->assertEquals(Month::JUNE()->firstDayOfYear(true), 1 + 31 + 29 + 31 + 30 + 31);
        $this->assertEquals(Month::JULY()->firstDayOfYear(true), 1 + 31 + 29 + 31 + 30 + 31 + 30);
        $this->assertEquals(Month::AUGUST()->firstDayOfYear(true), 1 + 31 + 29 + 31 + 30 + 31 + 30 + 31);
        $this->assertEquals(Month::SEPTEMBER()->firstDayOfYear(true), 1 + 31 + 29 + 31 + 30 + 31 + 30 + 31 + 31);
        $this->assertEquals(Month::OCTOBER()->firstDayOfYear(true), 1 + 31 + 29 + 31 + 30 + 31 + 30 + 31 + 31 + 30);
        $this->assertEquals(Month::NOVEMBER()->firstDayOfYear(true), 1 + 31 + 29 + 31 + 30 + 31 + 30 + 31 + 31 + 30 + 31);
        $this->assertEquals(Month::DECEMBER()->firstDayOfYear(true), 1 + 31 + 29 + 31 + 30 + 31 + 30 + 31 + 31 + 30 + 31 + 30);
    }

    //-----------------------------------------------------------------------
    // firstMonthOfQuarter()
    //-----------------------------------------------------------------------

    public function test_firstMonthOfQuarter()
    {
        $this->assertEquals(Month::JANUARY()->firstMonthOfQuarter(), Month::JANUARY());
        $this->assertEquals(Month::FEBRUARY()->firstMonthOfQuarter(), Month::JANUARY());
        $this->assertEquals(Month::MARCH()->firstMonthOfQuarter(), Month::JANUARY());
        $this->assertEquals(Month::APRIL()->firstMonthOfQuarter(), Month::APRIL());
        $this->assertEquals(Month::MAY()->firstMonthOfQuarter(), Month::APRIL());
        $this->assertEquals(Month::JUNE()->firstMonthOfQuarter(), Month::APRIL());
        $this->assertEquals(Month::JULY()->firstMonthOfQuarter(), Month::JULY());
        $this->assertEquals(Month::AUGUST()->firstMonthOfQuarter(), Month::JULY());
        $this->assertEquals(Month::SEPTEMBER()->firstMonthOfQuarter(), Month::JULY());
        $this->assertEquals(Month::OCTOBER()->firstMonthOfQuarter(), Month::OCTOBER());
        $this->assertEquals(Month::NOVEMBER()->firstMonthOfQuarter(), Month::OCTOBER());
        $this->assertEquals(Month::DECEMBER()->firstMonthOfQuarter(), Month::OCTOBER());
    }

    //-----------------------------------------------------------------------
    // toString()
    //-----------------------------------------------------------------------

    public function test_toString()
    {
        $this->assertEquals(Month::JANUARY()->__toString(), "JANUARY");
        $this->assertEquals(Month::FEBRUARY()->__toString(), "FEBRUARY");
        $this->assertEquals(Month::MARCH()->__toString(), "MARCH");
        $this->assertEquals(Month::APRIL()->__toString(), "APRIL");
        $this->assertEquals(Month::MAY()->__toString(), "MAY");
        $this->assertEquals(Month::JUNE()->__toString(), "JUNE");
        $this->assertEquals(Month::JULY()->__toString(), "JULY");
        $this->assertEquals(Month::AUGUST()->__toString(), "AUGUST");
        $this->assertEquals(Month::SEPTEMBER()->__toString(), "SEPTEMBER");
        $this->assertEquals(Month::OCTOBER()->__toString(), "OCTOBER");
        $this->assertEquals(Month::NOVEMBER()->__toString(), "NOVEMBER");
        $this->assertEquals(Month::DECEMBER()->__toString(), "DECEMBER");
    }

    //-----------------------------------------------------------------------
    // generated methods
    //-----------------------------------------------------------------------

    public function test_enum()
    {
        $this->assertEquals(Month::valueOf("JANUARY"), Month::JANUARY());
        $this->assertEquals(Month::values()[0], Month::JANUARY());
    }

}
