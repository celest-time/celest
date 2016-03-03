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

namespace Celest\Chrono;

use Celest\DateTimeException;
use Celest\Format\ResolverStyle;
use Celest\Helper\Integer;
use Celest\LocalDate;
use Celest\Temporal\ChronoField;
use Celest\Temporal\FieldValues;
use Celest\Temporal\TemporalAccessor;
use Celest\Temporal\TemporalField;
use Celest\Temporal\TemporalQueries;
use Celest\Temporal\TemporalQuery;
use Celest\TestHelper;


class test_factory_from_TemporalAccessor_chronology implements TemporalAccessor
{
    public function isSupported(TemporalField $field)
    {
        throw new UnsupportedOperationException();
    }

    public function getLong(TemporalField $field)
    {
        throw new UnsupportedOperationException();
    }

    public function query(TemporalQuery $query)
    {
        if ($query == TemporalQueries::chronology()) {
            return IsoChronology::INSTANCE();
        }
        throw new UnsupportedOperationException();
    }

    public function range(TemporalField $field)
    {
    }

    public function get(TemporalField $field)
    {
    }

    public function __toString()
    {
    }
}

class test_factory_from_TemporalAccessor_noChronology implements TemporalAccessor
{

    public function query(TemporalQuery $query)
    {
        if ($query == TemporalQueries::chronology()) {
            return null;
        }
        throw new UnsupportedOperationException();
    }

    public function isSupported(TemporalField $field)
    {
    }

    public function range(TemporalField $field)
    {
    }

    public function get(TemporalField $field)
    {
    }

    public function getLong(TemporalField $field)
    {
    }

    public function __toString()
    {
    }
}

class test_date_TemporalAccessor implements TemporalAccessor
{
    public function isSupported(TemporalField $field)
    {
        if ($field == ChronoField::EPOCH_DAY()) {
            return true;
        }
        throw new UnsupportedOperationException();
    }

    public function getLong(TemporalField $field)
    {
        if ($field == ChronoField::EPOCH_DAY()) {
            return LocalDate::of(2012, 6, 30)->toEpochDay();
        }
        throw new UnsupportedOperationException();
    }

    public function query(TemporalQuery $query)
    {
        if ($query == TemporalQueries::localDate()) {
        return LocalDate::of(2012, 6, 30);
                }
        throw new UnsupportedOperationException();
    }

    public function range(TemporalField $field)
    {
    }

    public function get(TemporalField $field)
    {
    }

    public function __toString()
    {
    }
}

/**
 * Test.
 */
class TCKIsoChronologyTest extends \PHPUnit_Framework_TestCase
{
    // Can only work with IsoChronology here
    // others may be in separate module

    public function test_factory_from_TemporalAccessor_dateWithChronlogy()
    {
        $this->assertEquals(AbstractChronology::from(LocalDate::of(2012, 6, 30)), IsoChronology::INSTANCE());
    }


    public function test_factory_from_TemporalAccessor_chronology()
    {
        $this->assertEquals(AbstractChronology::from(new test_factory_from_TemporalAccessor_chronology()), IsoChronology::INSTANCE());
    }

    public function test_factory_from_TemporalAccessor_noChronology()
    {
        $this->assertEquals(AbstractChronology::from(new test_factory_from_TemporalAccessor_noChronology()), IsoChronology::INSTANCE());
    }

    public function test_factory_from_TemporalAccessor_null()
    {
        TestHelper::assertNullException($this, function () {
            AbstractChronology::from(null);
        });
    }

    //-----------------------------------------------------------------------

    public function test_date_TemporalAccessor()
    {
        $this->assertEquals(IsoChronology::INSTANCE()->dateFrom(new test_date_TemporalAccessor()), LocalDate::of(2012, 6, 30));
    }

    public function test_date_TemporalAccessor_null()
    {
        TestHelper::assertNullException($this, function () {
            IsoChronology::INSTANCE()->dateFrom(null);
        });
    }

    //-----------------------------------------------------------------------

    public function test_localDateTime_TemporalAccessor()
    {
        /*$this->assertEquals(IsoChronology::INSTANCE()->localDateTime(new TemporalAccessor() {
            @Override
            public isSupported(TemporalField $field) {
        if ($field == ChronoField->EPOCH_DAY || $field == ChronoField->NANO_OF_DAY) {
            return true;
        }
        throw new UnsupportedOperationException();
    }

            @Override
            public long getLong(TemporalField $field) {
        if ($field == ChronoField->EPOCH_DAY) {
            return LocalDate::ofNumerical(2012, 6, 30)->toEpochDay();
        }
        if ($field == ChronoField->NANO_OF_DAY) {
            return LocalTime->of(12, 30, 40)->toNanoOfDay();
        }
        throw new UnsupportedOperationException();
    }

            @SuppressWarnings("unchecked")
            @Override
            public <R > R query(TemporalQuery < R> query) {
        if (query == TemporalQueries->localDate()) {
            return (R) LocalDate::ofNumerical(2012, 6, 30);
                }
        if (query == TemporalQueries->localTime()) {
            return (R) LocalTime->of(12, 30, 40);
                }
        throw new UnsupportedOperationException();
    }
        }), LocalDateTime->of(2012, 6, 30, 12, 30, 40));
    }

    public function test_localDateTime_TemporalAccessor_null()
    {
        TestHelper::assertNullException($this, function () {
            IsoChronology::INSTANCE()->localDateTime(null);
        });*/
    }

    //-----------------------------------------------------------------------

    public function test_zonedDateTime_TemporalAccessor()
    {
        /*$this->assertEquals(IsoChronology::INSTANCE()->zonedDateTime(new TemporalAccessor() {
            @Override
            public isSupported(TemporalField $field) {
        if ($field == ChronoField->EPOCH_DAY || $field == ChronoField->NANO_OF_DAY ||
        $field == ChronoField->INSTANT_SECONDS || $field == ChronoField->NANO_OF_SECOND
        ) {
            return true;
        }
        throw new UnsupportedOperationException();
    }

            @Override
            public long getLong(TemporalField $field) {
        if ($field == ChronoField->INSTANT_SECONDS) {
            return ZonedDateTime->of(2012, 6, 30, 12, 30, 40, 0, ZoneId->of("Europe/London"))->toEpochSecond();
        }
        if ($field == ChronoField->NANO_OF_SECOND) {
            return 0;
        }
        if ($field == ChronoField->EPOCH_DAY) {
            return LocalDate::ofNumerical(2012, 6, 30)->toEpochDay();
        }
        if ($field == ChronoField->NANO_OF_DAY) {
            return LocalTime->of(12, 30, 40)->toNanoOfDay();
        }
        throw new UnsupportedOperationException();
    }

            @SuppressWarnings("unchecked")
            @Override
            public <R > R query(TemporalQuery < R> query) {
        if (query == TemporalQueries->localDate()) {
            return (R) LocalDate::ofNumerical(2012, 6, 30);
                }
        if (query == TemporalQueries->localTime()) {
            return (R) LocalTime->of(12, 30, 40);
                }
        if (query == TemporalQueries->zoneId() || query == TemporalQueries->zone()) {
            return (R) ZoneId->of("Europe/London");
                }
        throw new UnsupportedOperationException();
    }
        }), ZonedDateTime->of(2012, 6, 30, 12, 30, 40, 0, ZoneId->of("Europe/London")));*/
    }

    public function test_zonedDateTime_TemporalAccessor_null()
    {
        TestHelper::assertNullException($this, function () {
            IsoChronology::INSTANCE()->zonedDateTimeFrom(null);
        });
    }

    //-----------------------------------------------------------------------
    function data_resolve_yearOfEra()
    {
        return
            [
                // era only
                [
                    ResolverStyle::STRICT(), -1, null, null, null, null],
                [
                    ResolverStyle::SMART(), -1, null, null, null, null],
                [
                    ResolverStyle::LENIENT(), -1, null, null, null, null],

                [
                    ResolverStyle::STRICT(), 0, null, null, ChronoField::ERA(), 0],
                [
                    ResolverStyle::SMART(), 0, null, null, ChronoField::ERA(), 0],
                [
                    ResolverStyle::LENIENT(), 0, null, null, ChronoField::ERA(), 0],

                [
                    ResolverStyle::STRICT(), 1, null, null, ChronoField::ERA(), 1],
                [
                    ResolverStyle::SMART(), 1, null, null, ChronoField::ERA(), 1],
                [
                    ResolverStyle::LENIENT(), 1, null, null, ChronoField::ERA(), 1],

                [
                    ResolverStyle::STRICT(), 2, null, null, null, null],
                [
                    ResolverStyle::SMART(), 2, null, null, null, null],
                [
                    ResolverStyle::LENIENT(), 2, null, null, null, null],

// era and year-of-era
                [
                    ResolverStyle::STRICT(), -1, 2012, null, null, null],
                [
                    ResolverStyle::SMART(), -1, 2012, null, null, null],
                [
                    ResolverStyle::LENIENT(), -1, 2012, null, null, null],

                [
                    ResolverStyle::STRICT(), 0, 2012, null, ChronoField::YEAR(), -2011],
                [
                    ResolverStyle::SMART(), 0, 2012, null, ChronoField::YEAR(), -2011],
                [
                    ResolverStyle::LENIENT(), 0, 2012, null, ChronoField::YEAR(), -2011],

                [
                    ResolverStyle::STRICT(), 1, 2012, null, ChronoField::YEAR(), 2012],
                [
                    ResolverStyle::SMART(), 1, 2012, null, ChronoField::YEAR(), 2012],
                [
                    ResolverStyle::LENIENT(), 1, 2012, null, ChronoField::YEAR(), 2012],

                [
                    ResolverStyle::STRICT(), 2, 2012, null, null, null],
                [
                    ResolverStyle::SMART(), 2, 2012, null, null, null],
                [
                    ResolverStyle::LENIENT(), 2, 2012, null, null, null],

// year-of-era only
                [
                    ResolverStyle::STRICT(), null, 2012, null, ChronoField::YEAR_OF_ERA(), 2012],
                [
                    ResolverStyle::SMART(), null, 2012, null, ChronoField::YEAR(), 2012],
                [
                    ResolverStyle::LENIENT(), null, 2012, null, ChronoField::YEAR(), 2012],

                [
                    ResolverStyle::STRICT(), null, Integer::MAX_VALUE, null, null, null],
                [
                    ResolverStyle::SMART(), null, Integer::MAX_VALUE, null, null, null],
                [
                    ResolverStyle::LENIENT(), null, Integer::MAX_VALUE, null, ChronoField::YEAR(), Integer::MAX_VALUE],

// year-of-era and year
                [
                    ResolverStyle::STRICT(), null, 2012, 2012, ChronoField::YEAR(), 2012],
                [
                    ResolverStyle::SMART(), null, 2012, 2012, ChronoField::YEAR(), 2012],
                [
                    ResolverStyle::LENIENT(), null, 2012, 2012, ChronoField::YEAR(), 2012],

                [
                    ResolverStyle::STRICT(), null, 2012, -2011, ChronoField::YEAR(), -2011],
                [
                    ResolverStyle::SMART(), null, 2012, -2011, ChronoField::YEAR(), -2011],
                [
                    ResolverStyle::LENIENT(), null, 2012, -2011, ChronoField::YEAR(), -2011],

                [
                    ResolverStyle::STRICT(), null, 2012, 2013, null, null],
                [
                    ResolverStyle::SMART(), null, 2012, 2013, null, null],
                [
                    ResolverStyle::LENIENT(), null, 2012, 2013, null, null],

                [
                    ResolverStyle::STRICT(), null, 2012, -2013, null, null],
                [
                    ResolverStyle::SMART(), null, 2012, -2013, null, null],
                [
                    ResolverStyle::LENIENT(), null, 2012, -2013, null, null],
            ];
    }

    /**
     * @dataProvider data_resolve_yearOfEra
     */
    public function test_resolve_yearOfEra(ResolverStyle $style, $e, $yoe, $y, $field, $expected)
    {
        $fieldValues = new FieldValues();
        if ($e !== null) {
            $fieldValues->put(ChronoField::ERA(), $e);
        }

        if ($yoe !== null) {
            $fieldValues->put(ChronoField::YEAR_OF_ERA(), $yoe);
        }
        if ($y !== null) {
            $fieldValues->put(ChronoField::YEAR(), $y);
        }
        if ($field !== null) {
            $date = IsoChronology::INSTANCE()->resolveDate($fieldValues, $style);
            $this->assertEquals($date, null);
            $this->assertEquals($fieldValues->get($field), $expected);
            $this->assertEquals($fieldValues->size(), 1);
        } else {
            try {
                IsoChronology::INSTANCE()->resolveDate($fieldValues, $style);
                $this->$this->fail("Should have failed");
            } catch (DateTimeException $ex) {
                // $expected
            }
        }
    }

//-----------------------------------------------------------------------
//-----------------------------------------------------------------------
    function data_resolve_ymd()
    {
        return
            [
                [
                    2012, 1, -365, LocalDate::of(2010, 12, 31), false, false
                ],
                [
                    2012, 1, -364, LocalDate::of(2011, 1, 1), false, false],
                [
                    2012, 1, -31, LocalDate::of(2011, 11, 30), false, false],
                [
                    2012, 1, -30, LocalDate::of(2011, 12, 1), false, false],
                [
                    2012, 1, -12, LocalDate::of(2011, 12, 19), false, false],
                [
                    2012, 1, 1, LocalDate::of(2012, 1, 1), true, true],
                [
                    2012, 1, 27, LocalDate::of(2012, 1, 27), true, true],
                [
                    2012, 1, 28, LocalDate::of(2012, 1, 28), true, true],
                [
                    2012, 1, 29, LocalDate::of(2012, 1, 29), true, true],
                [
                    2012, 1, 30, LocalDate::of(2012, 1, 30), true, true],
                [
                    2012, 1, 31, LocalDate::of(2012, 1, 31), true, true],
                [
                    2012, 1, 59, LocalDate::of(2012, 2, 28), false, false],
                [
                    2012, 1, 60, LocalDate::of(2012, 2, 29), false, false],
                [
                    2012, 1, 61, LocalDate::of(2012, 3, 1), false, false],
                [
                    2012, 1, 365, LocalDate::of(2012, 12, 30), false, false],
                [
                    2012, 1, 366, LocalDate::of(2012, 12, 31), false, false],
                [
                    2012, 1, 367, LocalDate::of(2013, 1, 1), false, false],
                [
                    2012, 1, 367 + 364, LocalDate::of(2013, 12, 31), false, false],
                [
                    2012, 1, 367 + 365, LocalDate::of(2014, 1, 1), false, false],

                [
                    2012, 2, 1, LocalDate::of(2012, 2, 1), true, true],
                [
                    2012, 2, 28, LocalDate::of(2012, 2, 28), true, true],
                [
                    2012, 2, 29, LocalDate::of(2012, 2, 29), true, true],
                [
                    2012, 2, 30, LocalDate::of(2012, 3, 1), LocalDate::of(2012, 2, 29), false],
                [
                    2012, 2, 31, LocalDate::of(2012, 3, 2), LocalDate::of(2012, 2, 29), false],
                [
                    2012, 2, 32, LocalDate::of(2012, 3, 3), false, false],

                [
                    2012, -12, 1, LocalDate::of(2010, 12, 1), false, false],
                [
                    2012, -11, 1, LocalDate::of(2011, 1, 1), false, false],
                [
                    2012, -1, 1, LocalDate::of(2011, 11, 1), false, false],
                [
                    2012, 0, 1, LocalDate::of(2011, 12, 1), false, false],
                [
                    2012, 1, 1, LocalDate::of(2012, 1, 1), true, true],
                [
                    2012, 12, 1, LocalDate::of(2012, 12, 1), true, true],
                [
                    2012, 13, 1, LocalDate::of(2013, 1, 1), false, false],
                [
                    2012, 24, 1, LocalDate::of(2013, 12, 1), false, false],
                [
                    2012, 25, 1, LocalDate::of(2014, 1, 1), false, false],

                [
                    2012, 6, -31, LocalDate::of(2012, 4, 30), false, false],
                [
                    2012, 6, -30, LocalDate::of(2012, 5, 1), false, false],
                [
                    2012, 6, -1, LocalDate::of(2012, 5, 30), false, false],
                [
                    2012, 6, 0, LocalDate::of(2012, 5, 31), false, false],
                [
                    2012, 6, 1, LocalDate::of(2012, 6, 1), true, true],
                [
                    2012, 6, 30, LocalDate::of(2012, 6, 30), true, true],
                [
                    2012, 6, 31, LocalDate::of(2012, 7, 1), LocalDate::of(2012, 6, 30), false],
                [
                    2012, 6, 61, LocalDate::of(2012, 7, 31), false, false],
                [
                    2012, 6, 62, LocalDate::of(2012, 8, 1), false, false],

                [
                    2011, 2, 1, LocalDate::of(2011, 2, 1), true, true],
                [
                    2011, 2, 28, LocalDate::of(2011, 2, 28), true, true],
                [
                    2011, 2, 29, LocalDate::of(2011, 3, 1), LocalDate::of(2011, 2, 28), false],
                [
                    2011, 2, 30, LocalDate::of(2011, 3, 2), LocalDate::of(2011, 2, 28), false],
                [
                    2011, 2, 31, LocalDate::of(2011, 3, 3), LocalDate::of(2011, 2, 28), false],
                [
                    2011, 2, 32, LocalDate::of(2011, 3, 4), false, false],
            ];
    }

    /**
     * @dataProvider data_resolve_ymd
     */
    public function test_resolve_ymd_lenient($y, $m, $d, $expected, $smar, $strict)
    {
        $fieldValues = new FieldValues();
        $fieldValues->put(ChronoField::YEAR(), $y);
        $fieldValues->put(ChronoField::MONTH_OF_YEAR(), $m);
        $fieldValues->put(ChronoField::DAY_OF_MONTH(), $d);
        $date = IsoChronology::INSTANCE()->resolveDate($fieldValues, ResolverStyle::LENIENT());
        $this->assertEquals($date, $expected);
        $this->assertEquals($fieldValues->size(), 0);
    }

    /**
     * @dataProvider data_resolve_ymd
     */
    public function test_resolve_ymd_smart($y, $m, $d, $expected, $smar, $strict)
    {
        $fieldValues = new FieldValues();
        $fieldValues->put(ChronoField::YEAR(), $y);
        $fieldValues->put(ChronoField::MONTH_OF_YEAR(), $m);
        $fieldValues->put(ChronoField::DAY_OF_MONTH(), $d);
        if (true === $smar) {
            $date = IsoChronology::INSTANCE()->resolveDate($fieldValues, ResolverStyle::SMART());
            $this->assertEquals($date, $expected);
            $this->assertEquals($fieldValues->size(), 0);
        } else if ($smar instanceof LocalDate) {
            $date = IsoChronology::INSTANCE()->resolveDate($fieldValues, ResolverStyle::SMART());
            $this->assertEquals($date, $smar);
        } else {
            try {
                IsoChronology::INSTANCE()->resolveDate($fieldValues, ResolverStyle::SMART());
                $this->fail("Should have failed");
            } catch (DateTimeException $ex) {
                // $expected
            }
        }
    }

    /**
     * @dataProvider data_resolve_ymd
     */
    public function test_resolve_ymd_strict($y, $m, $d, $expected, $smar, $strict)
    {
        $fieldValues = new FieldValues();
        $fieldValues->put(ChronoField::YEAR(), $y);
        $fieldValues->put(ChronoField::MONTH_OF_YEAR(), $m);
        $fieldValues->put(ChronoField::DAY_OF_MONTH(), $d);
        if ($strict) {
            $date = IsoChronology::INSTANCE()->resolveDate($fieldValues, ResolverStyle::STRICT());
            $this->assertEquals($date, $expected);
            $this->assertEquals($fieldValues->size(), 0);
        } else {
            try {
                IsoChronology::INSTANCE()->resolveDate($fieldValues, ResolverStyle::STRICT());
                $this->fail("Should have failed");
            } catch (DateTimeException $ex) {
                // $expected
            }
        }
    }

    //-----------------------------------------------------------------------
    //-----------------------------------------------------------------------
    function data_resolve_yd()
    {
        return [
            [
                2012, -365, LocalDate::of(2010, 12, 31), false, false],
            [
                2012, -364, LocalDate::of(2011, 1, 1), false, false],
            [
                2012, -31, LocalDate::of(2011, 11, 30), false, false],
            [
                2012, -30, LocalDate::of(2011, 12, 1), false, false],
            [
                2012, -12, LocalDate::of(2011, 12, 19), false, false],
            [
                2012, -1, LocalDate::of(2011, 12, 30), false, false],
            [
                2012, 0, LocalDate::of(2011, 12, 31), false, false],
            [
                2012, 1, LocalDate::of(2012, 1, 1), true, true],
            [
                2012, 2, LocalDate::of(2012, 1, 2), true, true],
            [
                2012, 31, LocalDate::of(2012, 1, 31), true, true],
            [
                2012, 32, LocalDate::of(2012, 2, 1), true, true],
            [
                2012, 59, LocalDate::of(2012, 2, 28), true, true],
            [
                2012, 60, LocalDate::of(2012, 2, 29), true, true],
            [
                2012, 61, LocalDate::of(2012, 3, 1), true, true],
            [
                2012, 365, LocalDate::of(2012, 12, 30), true, true],
            [
                2012, 366, LocalDate::of(2012, 12, 31), true, true],
            [
                2012, 367, LocalDate::of(2013, 1, 1), false, false],
            [
                2012, 367 + 364, LocalDate::of(2013, 12, 31), false, false],
            [
                2012, 367 + 365, LocalDate::of(2014, 1, 1), false, false],

            [
                2011, 59, LocalDate::of(2011, 2, 28), true, true],
            [
                2011, 60, LocalDate::of(2011, 3, 1), true, true],
        ];
    }

    /**
     * @dataProvider data_resolve_yd
     */
    public function test_resolve_yd_lenient($y, $d, $expected, $smar, $strict)
    {
        $fieldValues = new FieldValues();
        $fieldValues->put(ChronoField::YEAR(), $y);
        $fieldValues->put(ChronoField::DAY_OF_YEAR(), $d);
        $date = IsoChronology::INSTANCE()->resolveDate($fieldValues, ResolverStyle::LENIENT());
        $this->assertEquals($date, $expected);
        $this->assertEquals($fieldValues->size(), 0);
    }

    /**
     * @dataProvider data_resolve_yd
     */
    public function test_resolve_yd_smart($y, $d, $expected, $smar, $strict)
    {
        $fieldValues = new FieldValues();
        $fieldValues->put(ChronoField::YEAR(), $y);
        $fieldValues->put(ChronoField::DAY_OF_YEAR(), $d);
        if ($smar) {
            $date = IsoChronology::INSTANCE()->resolveDate($fieldValues, ResolverStyle::SMART());
            $this->assertEquals($date, $expected);
            $this->assertEquals($fieldValues->size(), 0);
        } else {
            try {
                IsoChronology::INSTANCE()->resolveDate($fieldValues, ResolverStyle::SMART());
                $this->fail("Should have failed");
            } catch (DateTimeException $ex) {
                // $expected
            }
        }
    }

    /**
     * @dataProvider data_resolve_yd
     */
    public function test_resolve_yd_strict($y, $d, $expected, $smar, $strict)
    {
        $fieldValues = new FieldValues();
        $fieldValues->put(ChronoField::YEAR(), $y);
        $fieldValues->put(ChronoField::DAY_OF_YEAR(), $d);
        if ($strict) {
            $date = IsoChronology::INSTANCE()->resolveDate($fieldValues, ResolverStyle::STRICT());
            $this->assertEquals($date, $expected);
            $this->assertEquals($fieldValues->size(), 0);
        } else {
            try {
                IsoChronology::INSTANCE()->resolveDate($fieldValues, ResolverStyle::STRICT());
                $this->fail("Should have failed");
            } catch (DateTimeException $ex) {
                // $expected
            }
        }
    }

    //-----------------------------------------------------------------------
    //-----------------------------------------------------------------------
    function data_resolve_ymaa()
    {
        return [
            [
                2012, 1, 1, -365, LocalDate::of(2010, 12, 31), false, false],
            [
                2012, 1, 1, -364, LocalDate::of(2011, 1, 1), false, false],
            [
                2012, 1, 1, -31, LocalDate::of(2011, 11, 30), false, false],
            [
                2012, 1, 1, -30, LocalDate::of(2011, 12, 1), false, false],
            [
                2012, 1, 1, -12, LocalDate::of(2011, 12, 19), false, false],
            [
                2012, 1, 1, 1, LocalDate::of(2012, 1, 1), true, true],
            [
                2012, 1, 1, 59, LocalDate::of(2012, 2, 28), false, false],
            [
                2012, 1, 1, 60, LocalDate::of(2012, 2, 29), false, false],
            [
                2012, 1, 1, 61, LocalDate::of(2012, 3, 1), false, false],
            [
                2012, 1, 1, 365, LocalDate::of(2012, 12, 30), false, false],
            [
                2012, 1, 1, 366, LocalDate::of(2012, 12, 31), false, false],
            [
                2012, 1, 1, 367, LocalDate::of(2013, 1, 1), false, false],
            [
                2012, 1, 1, 367 + 364, LocalDate::of(2013, 12, 31), false, false],
            [
                2012, 1, 1, 367 + 365, LocalDate::of(2014, 1, 1), false, false],

            [
                2012, 2, 0, 1, LocalDate::of(2012, 1, 25), false, false],
            [
                2012, 2, 0, 7, LocalDate::of(2012, 1, 31), false, false],
            [
                2012, 2, 1, 1, LocalDate::of(2012, 2, 1), true, true],
            [
                2012, 2, 1, 7, LocalDate::of(2012, 2, 7), true, true],
            [
                2012, 2, 2, 1, LocalDate::of(2012, 2, 8), true, true],
            [
                2012, 2, 2, 7, LocalDate::of(2012, 2, 14), true, true],
            [
                2012, 2, 3, 1, LocalDate::of(2012, 2, 15), true, true],
            [
                2012, 2, 3, 7, LocalDate::of(2012, 2, 21), true, true],
            [
                2012, 2, 4, 1, LocalDate::of(2012, 2, 22), true, true],
            [
                2012, 2, 4, 7, LocalDate::of(2012, 2, 28), true, true],
            [
                2012, 2, 5, 1, LocalDate::of(2012, 2, 29), true, true],
            [
                2012, 2, 5, 2, LocalDate::of(2012, 3, 1), true, false],
            [
                2012, 2, 5, 7, LocalDate::of(2012, 3, 6), true, false],
            [
                2012, 2, 6, 1, LocalDate::of(2012, 3, 7), false, false],
            [
                2012, 2, 6, 7, LocalDate::of(2012, 3, 13), false, false],

            [
                2012, 12, 1, 1, LocalDate::of(2012, 12, 1), true, true],
            [
                2012, 12, 5, 1, LocalDate::of(2012, 12, 29), true, true],
            [
                2012, 12, 5, 2, LocalDate::of(2012, 12, 30), true, true],
            [
                2012, 12, 5, 3, LocalDate::of(2012, 12, 31), true, true],
            [
                2012, 12, 5, 4, LocalDate::of(2013, 1, 1), true, false],
            [
                2012, 12, 5, 7, LocalDate::of(2013, 1, 4), true, false],

            [
                2012, -12, 1, 1, LocalDate::of(2010, 12, 1), false, false],
            [
                2012, -11, 1, 1, LocalDate::of(2011, 1, 1), false, false],
            [
                2012, -1, 1, 1, LocalDate::of(2011, 11, 1), false, false],
            [
                2012, 0, 1, 1, LocalDate::of(2011, 12, 1), false, false],
            [
                2012, 1, 1, 1, LocalDate::of(2012, 1, 1), true, true],
            [
                2012, 12, 1, 1, LocalDate::of(2012, 12, 1), true, true],
            [
                2012, 13, 1, 1, LocalDate::of(2013, 1, 1), false, false],
            [
                2012, 24, 1, 1, LocalDate::of(2013, 12, 1), false, false],
            [
                2012, 25, 1, 1, LocalDate::of(2014, 1, 1), false, false],

            [
                2011, 2, 1, 1, LocalDate::of(2011, 2, 1), true, true],
            [
                2011, 2, 4, 7, LocalDate::of(2011, 2, 28), true, true],
            [
                2011, 2, 5, 1, LocalDate::of(2011, 3, 1), true, false],
        ];
    }

    /**
     * @dataProvider data_resolve_ymaa
     */
    public function test_resolve_ymaa_lenient($y, $m, $w, $d, $expected, $smar, $strict)
    {
        $fieldValues = new FieldValues();
        $fieldValues->put(ChronoField::YEAR(), $y);
        $fieldValues->put(ChronoField::MONTH_OF_YEAR(), $m);
        $fieldValues->put(ChronoField::ALIGNED_WEEK_OF_MONTH(), $w);
        $fieldValues->put(ChronoField::ALIGNED_DAY_OF_WEEK_IN_MONTH(), $d);
        $date = IsoChronology::INSTANCE()->resolveDate($fieldValues, ResolverStyle::LENIENT());
        $this->assertEquals($date, $expected);
        $this->assertEquals($fieldValues->size(), 0);
    }

    /**
     * @dataProvider data_resolve_ymaa
     */
    public function test_resolve_ymaa_smart($y, $m, $w, $d, $expected, $smar, $strict)
    {
        $fieldValues = new FieldValues();
        $fieldValues->put(ChronoField::YEAR(), $y);
        $fieldValues->put(ChronoField::MONTH_OF_YEAR(), $m);
        $fieldValues->put(ChronoField::ALIGNED_WEEK_OF_MONTH(), $w);
        $fieldValues->put(ChronoField::ALIGNED_DAY_OF_WEEK_IN_MONTH(), $d);
        if ($smar) {
            $date = IsoChronology::INSTANCE()->resolveDate($fieldValues, ResolverStyle::SMART());
            $this->assertEquals($date, $expected);
            $this->assertEquals($fieldValues->size(), 0);
        } else {
            try {
                IsoChronology::INSTANCE()->resolveDate($fieldValues, ResolverStyle::SMART());
                $this->fail("Should have failed");
            } catch (DateTimeException $ex) {
                // $expected
            }
        }
    }

    /**
     * @dataProvider data_resolve_ymaa
     */
    public function test_resolve_ymaa_strict($y, $m, $w, $d, $expected, $smar, $strict)
    {
        $fieldValues = new FieldValues();
        $fieldValues->put(ChronoField::YEAR(), $y);
        $fieldValues->put(ChronoField::MONTH_OF_YEAR(), $m);
        $fieldValues->put(ChronoField::ALIGNED_WEEK_OF_MONTH(), $w);
        $fieldValues->put(ChronoField::ALIGNED_DAY_OF_WEEK_IN_MONTH(), $d);
        if ($strict) {
            $date = IsoChronology::INSTANCE()->resolveDate($fieldValues, ResolverStyle::STRICT());
            $this->assertEquals($date, $expected);
            $this->assertEquals($fieldValues->size(), 0);
        } else {
            try {
                IsoChronology::INSTANCE()->resolveDate($fieldValues, ResolverStyle::STRICT());
                $this->fail("Should have failed");
            } catch (DateTimeException $ex) {
                // $expected
            }
        }
    }
}
