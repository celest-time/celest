<?php
/*
 * Copyright (c) 2013, Oracle and/or its affiliates. All rights reserved.
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
 * Copyright (c) 2008-2013, Stephen Colebourne & Michael Nascimento Santos
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
namespace Celest\Format;

use Celest\Chrono\IsoChronology;
use Celest\Chrono\MinguoChronology;
use Celest\Chrono\MinguoDate;
use Celest\Chrono\ThaiBuddhistChronology;
use Celest\DateTimeParseException;
use Celest\Instant;
use Celest\LocalDate;
use Celest\LocalDateTime;
use Celest\Locale;
use Celest\LocalTime;
use Celest\Period;
use Celest\Temporal\ChronoField;
use Celest\Temporal\FieldValues;
use Celest\Temporal\IsoFields;
use Celest\Temporal\Temporal;
use Celest\Temporal\TemporalAccessor;
use Celest\Temporal\TemporalField;
use Celest\Temporal\TemporalQueries;
use Celest\ZonedDateTime;
use Celest\ZoneId;
use PHPUnit\Framework\TestCase;

class ResolvingField implements TemporalField
{
    private $resolvedValue;

    function __construct($resolvedValue)
    {
        $this->resolvedValue = $resolvedValue;
    }

    public function getBaseUnit()
    {
        throw new UnsupportedOperationException();
    }

    public function getRangeUnit()
    {
        throw new UnsupportedOperationException();
    }

    public function range()
    {
        throw new UnsupportedOperationException();
    }

    public function isDateBased()
    {
        throw new UnsupportedOperationException();
    }

    public function isTimeBased()
    {
        throw new UnsupportedOperationException();
    }

    public function isSupportedBy(TemporalAccessor $temporal)
    {
        throw new UnsupportedOperationException();
    }

    public function rangeRefinedBy(TemporalAccessor $temporal)
    {
        throw new UnsupportedOperationException();
    }

    public function getFrom(TemporalAccessor $temporal)
    {
        throw new UnsupportedOperationException();
    }

    public function adjustInto(Temporal $temporal, $newValue)
    {
        throw new UnsupportedOperationException();
    }

    public function resolve(FieldValues $fieldValues, TemporalAccessor $partialTemporal, ResolverStyle $resolverStyle)
    {
        $fieldValues->remove($this);
        return $this->resolvedValue;
    }

    public function getDisplayName(Locale $locale)
    {
        return $this->__toString();
    }

    public function __toString()
    {
        return 'ResolvingField';
    }
}

/**
 * Test parse resolving.
 */
class TCKDateTimeParseResolverTest extends TestCase
{
    // TODO: tests with weird TenporalField implementations
    // TODO: tests with non-ISO chronologies

    private static function EUROPE_ATHENS()
    {
        return ZoneId::of("Europe/Athens");
    }

    private static function EUROPE_PARIS()
    {
        return ZoneId::of("Europe/Paris");
    }

    //-----------------------------------------------------------------------
    function data_resolveOneNoChange()
    {
        return
            [
                [
                    ChronoField::YEAR(), 2012],
                [
                    ChronoField::MONTH_OF_YEAR(), 8],
                [
                    ChronoField::DAY_OF_MONTH(), 7],
                [
                    ChronoField::DAY_OF_YEAR(), 6],
                [
                    ChronoField::DAY_OF_WEEK(), 5],
            ];
    }

    /**
     * @dataProvider data_resolveOneNoChange
     */
    public function test_resolveOneNoChange(TemporalField $field1, $value1)
    {
        $str = strval($value1);
        $f = (new DateTimeFormatterBuilder())->appendValue($field1)->toFormatter();

        $accessor = $f->parse($str);
        $this->assertEquals($accessor->query(TemporalQueries::localDate()), null);
        $this->assertEquals($accessor->query(TemporalQueries::localTime()), null);
        $this->assertEquals($accessor->isSupported($field1), true);
        $this->assertEquals($accessor->getLong($field1), $value1);
    }

//-----------------------------------------------------------------------

    function data_resolveTwoNoChange()
    {
        return [[
            ChronoField::YEAR(), 2012, ChronoField::MONTH_OF_YEAR(), 5],
            [
                ChronoField::YEAR(), 2012, ChronoField::DAY_OF_MONTH(), 5],
            [
                ChronoField::YEAR(), 2012, ChronoField::DAY_OF_WEEK(), 5],
            [
                ChronoField::YEAR(), 2012, ChronoField::ALIGNED_WEEK_OF_YEAR(), 5],
            [
                ChronoField::YEAR(), 2012, ChronoField::ALIGNED_WEEK_OF_MONTH(), 5],
            [
                ChronoField::YEAR(), 2012, IsoFields::QUARTER_OF_YEAR(), 3],
            [
                ChronoField::YEAR(), 2012, ChronoField::MINUTE_OF_HOUR(), 5],
            [
                ChronoField::YEAR(), 2012, ChronoField::SECOND_OF_MINUTE(), 5],
            [
                ChronoField::YEAR(), 2012, ChronoField::NANO_OF_SECOND(), 5],

            [
                ChronoField::MONTH_OF_YEAR(), 5, ChronoField::DAY_OF_MONTH(), 5],
            [
                ChronoField::MONTH_OF_YEAR(), 5, ChronoField::DAY_OF_WEEK(), 5],
            [
                ChronoField::MONTH_OF_YEAR(), 5, ChronoField::ALIGNED_WEEK_OF_YEAR(), 5],
            [
                ChronoField::MONTH_OF_YEAR(), 5, ChronoField::ALIGNED_WEEK_OF_MONTH(), 5],
            [
                ChronoField::MONTH_OF_YEAR(), 3, IsoFields::QUARTER_OF_YEAR(), 5],
            [
                ChronoField::MONTH_OF_YEAR(), 5, ChronoField::MINUTE_OF_HOUR(), 5],
            [
                ChronoField::MONTH_OF_YEAR(), 5, ChronoField::SECOND_OF_MINUTE(), 5],
            [
                ChronoField::MONTH_OF_YEAR(), 5, ChronoField::NANO_OF_SECOND(), 5],
        ];
    }

    /**
     * @dataProvider data_resolveTwoNoChange
     */
    public function test_resolveTwoNoChange(TemporalField $field1, $value1, TemporalField $field2, $value2)
    {
        $str = $value1 . " " . $value2;
        $f = (new DateTimeFormatterBuilder())
            ->appendValue($field1)->appendLiteral(' ')
            ->appendValue($field2)->toFormatter();
        $accessor = $f->parse($str);

        $this->assertEquals($accessor->query(TemporalQueries::localDate()), null);
        $this->assertEquals($accessor->query(TemporalQueries::localTime()), null);
        $this->assertEquals($accessor->isSupported($field1), true);
        $this->assertEquals($accessor->isSupported($field2), true);
        $this->assertEquals($accessor->getLong($field1), $value1);
        $this->assertEquals($accessor->getLong($field2), $value2);
    }

//-----------------------------------------------------------------------
    function data_resolveThreeNoChange()
    {
        return [
            [
                ChronoField::YEAR(), 2012, ChronoField::MONTH_OF_YEAR(), 5, ChronoField::DAY_OF_WEEK(), 5],
            [
                ChronoField::YEAR(), 2012, ChronoField::ALIGNED_WEEK_OF_YEAR(), 5, ChronoField::DAY_OF_MONTH(), 5],
            [
                ChronoField::YEAR(), 2012, ChronoField::ALIGNED_WEEK_OF_MONTH(), 5, ChronoField::DAY_OF_MONTH(), 5],
            [
                ChronoField::YEAR(), 2012, ChronoField::MONTH_OF_YEAR(), 5, ChronoField::DAY_OF_WEEK(), 5],
            [
                ChronoField::ERA(), 1, ChronoField::MONTH_OF_YEAR(), 5, ChronoField::DAY_OF_MONTH(), 5],
            [
                ChronoField::MONTH_OF_YEAR(), 1, ChronoField::DAY_OF_MONTH(), 5, IsoFields::QUARTER_OF_YEAR(), 3],
            [
                ChronoField::HOUR_OF_DAY(), 1, ChronoField::SECOND_OF_MINUTE(), 5, ChronoField::NANO_OF_SECOND(), 5],
            [
                ChronoField::MINUTE_OF_HOUR(), 1, ChronoField::SECOND_OF_MINUTE(), 5, ChronoField::NANO_OF_SECOND(), 5],
        ];
    }

    /**
     * @dataProvider data_resolveThreeNoChange
     */
    public function test_resolveThreeNoChange(TemporalField $field1, $value1, TemporalField $field2, $value2, TemporalField $field3, $value3)
    {
        $str = $value1 . " " . $value2 . " " . $value3;
        $f = (new DateTimeFormatterBuilder())
            ->appendValue($field1)->appendLiteral(' ')
            ->appendValue($field2)->appendLiteral(' ')
            ->appendValue($field3)->toFormatter();
        $accessor = $f->parse($str);

        $this->assertEquals($accessor->query(TemporalQueries::localDate()), null);
        $this->assertEquals($accessor->query(TemporalQueries::localTime()), null);
        $this->assertEquals($accessor->isSupported($field1), true);
        $this->assertEquals($accessor->isSupported($field2), true);
        $this->assertEquals($accessor->isSupported($field3), true);
        $this->assertEquals($accessor->getLong($field1), $value1);
        $this->assertEquals($accessor->getLong($field2), $value2);
        $this->assertEquals($accessor->getLong($field3), $value3);
    }

//-----------------------------------------------------------------------
//-----------------------------------------------------------------------
//-----------------------------------------------------------------------
    function data_resolveOneToField()
    {
        return [[
            ChronoField::YEAR_OF_ERA(), 2012, ChronoField::YEAR(), 2012, null, null],
            [
                ChronoField::PROLEPTIC_MONTH(), 2012 * 12 + (3 - 1), ChronoField::YEAR(), 2012, ChronoField::MONTH_OF_YEAR(), 3],

            [
                ChronoField::CLOCK_HOUR_OF_AMPM(), 8, ChronoField::HOUR_OF_AMPM(), 8, null, null],
            [
                ChronoField::CLOCK_HOUR_OF_AMPM(), 12, ChronoField::HOUR_OF_AMPM(), 0, null, null],
            [
                ChronoField::MICRO_OF_SECOND(), 12, ChronoField::NANO_OF_SECOND(), 12000, null, null],
            [
                ChronoField::MILLI_OF_SECOND(), 12, ChronoField::NANO_OF_SECOND(), 12000000, null, null],
        ];
    }

    /**
     * @dataProvider data_resolveOneToField
     */
    public function test_resolveOneToField(TemporalField $field1, $value1,
                                           TemporalField $expectedField1, $expectedValue1,
                                           $expectedField2, $expectedValue2)
    {
        $str = strval($value1);
        $f = (new DateTimeFormatterBuilder())->appendValue($field1)->toFormatter();

        $accessor = $f->parse($str);
        $this->assertEquals($accessor->query(TemporalQueries::localDate()), null);
        $this->assertEquals($accessor->query(TemporalQueries::localTime()), null);
        if ($expectedField1 != null) {
            $this->assertEquals($accessor->isSupported($expectedField1), true);
            $this->assertEquals($accessor->getLong($expectedField1), $expectedValue1);
        }
        if ($expectedField2 != null) {
            $this->assertEquals($accessor->isSupported($expectedField2), true);
            $this->assertEquals($accessor->getLong($expectedField2), $expectedValue2);
        }
    }

    //-----------------------------------------------------------------------
    function data_resolveOneToDate()
    {
        return [[
            ChronoField::EPOCH_DAY(), 32, LocalDate::of(1970, 2, 2)],
        ];
    }

    /**
     * @dataProvider data_resolveOneToDate
     */
    public function test_resolveOneToDate(TemporalField $field1, $value1, $expectedDate)
    {
        $str = strval($value1);
        $f = (new DateTimeFormatterBuilder())->appendValue($field1)->toFormatter();

        $accessor = $f->parse($str);
        $this->assertEquals($accessor->query(TemporalQueries::localDate()), $expectedDate);
        $this->assertEquals($accessor->query(TemporalQueries::localTime()), null);
    }

    //-----------------------------------------------------------------------
    function data_resolveOneToTime()
    {
        return [[
            ChronoField::HOUR_OF_DAY(), 8, LocalTime::of(8, 0)],
            [
                ChronoField::CLOCK_HOUR_OF_DAY(), 8, LocalTime::of(8, 0)],
            [
                ChronoField::CLOCK_HOUR_OF_DAY(), 24, LocalTime::of(0, 0)],
            [
                ChronoField::MINUTE_OF_DAY(), 650, LocalTime::of(10, 50)],
            [
                ChronoField::SECOND_OF_DAY(), 3600 + 650, LocalTime::of(1, 10, 50)],
            [
                ChronoField::MILLI_OF_DAY(), (3600 + 650) * 1000 + 2, LocalTime::of(1, 10, 50, 2000000)],
            [
                ChronoField::MICRO_OF_DAY(), (3600 + 650) * 1000000 + 2, LocalTime::of(1, 10, 50, 2000)],
            [
                ChronoField::NANO_OF_DAY(), (3600 + 650) * 1000000000 + 2, LocalTime::of(1, 10, 50, 2)],
        ];
    }

    /**
     * @dataProvider data_resolveOneToTime
     */
    public function test_resolveOneToTime(TemporalField $field1, $value1, $expectedTime)
    {
        $str = strval($value1);
        $f = (new DateTimeFormatterBuilder())->appendValue($field1)->toFormatter();

        $accessor = $f->parse($str);
        $this->assertEquals($accessor->query(TemporalQueries::localDate()), null);
        $this->assertEquals($accessor->query(TemporalQueries::localTime()), $expectedTime);
    }

    //-----------------------------------------------------------------------
    //-----------------------------------------------------------------------
    //-----------------------------------------------------------------------
    function data_resolveTwoToField()
    {
        return [// cross-check
            [
                ChronoField::PROLEPTIC_MONTH(), 2012 * 12 + (3 - 1), ChronoField::YEAR(), 2012, ChronoField::YEAR(), 2012, ChronoField::MONTH_OF_YEAR(), 3],
            [
                ChronoField::PROLEPTIC_MONTH(), 2012 * 12 + (3 - 1), ChronoField::YEAR_OF_ERA(), 2012, ChronoField::YEAR(), 2012, ChronoField::MONTH_OF_YEAR(), 3],
            [
                ChronoField::PROLEPTIC_MONTH(), 2012 * 12 + (3 - 1), ChronoField::ERA(), 1, ChronoField::YEAR(), 2012, ChronoField::MONTH_OF_YEAR(), 3],
            [
                ChronoField::PROLEPTIC_MONTH(), (3 - 1), ChronoField::YEAR(), 0, ChronoField::YEAR(), 0, ChronoField::MONTH_OF_YEAR(), 3],
            [
                ChronoField::PROLEPTIC_MONTH(), (3 - 1), ChronoField::YEAR_OF_ERA(), 1, ChronoField::YEAR(), 0, ChronoField::MONTH_OF_YEAR(), 3],
            [
                ChronoField::PROLEPTIC_MONTH(), (3 - 1), ChronoField::ERA(), 0, ChronoField::YEAR(), 0, ChronoField::MONTH_OF_YEAR(), 3],
        ];
    }

    /**
     * @dataProvider data_resolveTwoToField
     */
    public function test_resolveTwoToField(TemporalField $field1, $value1,
                                           TemporalField $field2, $value2,
                                           TemporalField $expectedField1, $expectedValue1,
                                           TemporalField $expectedField2, $expectedValue2)
    {
        $str = $value1 . " " . $value2;
        $f = (new DateTimeFormatterBuilder())
            ->appendValue($field1)->appendLiteral(' ')
            ->appendValue($field2)->toFormatter();

        $accessor = $f->parse($str);
        $this->assertEquals($accessor->query(TemporalQueries::localDate()), null);
        $this->assertEquals($accessor->query(TemporalQueries::localTime()), null);
        if ($expectedField1 != null) {
            $this->assertEquals($accessor->isSupported($expectedField1), true);
            $this->assertEquals($accessor->getLong($expectedField1), $expectedValue1);
        }
        if ($expectedField2 != null) {
            $this->assertEquals($accessor->isSupported($expectedField2), true);
            $this->assertEquals($accessor->getLong($expectedField2), $expectedValue2);
        }
    }

    //-----------------------------------------------------------------------
    function data_resolveTwoToDate()
    {
        return [// merge
            [
                ChronoField::YEAR(), 2012, ChronoField::DAY_OF_YEAR(), 32, LocalDate::of(2012, 2, 1)],
            [
                ChronoField::YEAR_OF_ERA(), 2012, ChronoField::DAY_OF_YEAR(), 32, LocalDate::of(2012, 2, 1)],

            // merge
            [
                ChronoField::PROLEPTIC_MONTH(), 2012 * 12 + (2 - 1), ChronoField::DAY_OF_MONTH(), 25, LocalDate::of(2012, 2, 25)],
            [
                ChronoField::PROLEPTIC_MONTH(), 2012 * 12 + (2 - 1), ChronoField::DAY_OF_YEAR(), 56, LocalDate::of(2012, 2, 25)],

            // cross-check
            [
                ChronoField::EPOCH_DAY(), 32, ChronoField::ERA(), 1, LocalDate::of(1970, 2, 2)],
            [
                ChronoField::EPOCH_DAY(), -146097 * 5, ChronoField::ERA(), 0, LocalDate::of(1970 - (400 * 5), 1, 1)],
            [
                ChronoField::EPOCH_DAY(), 32, ChronoField::YEAR(), 1970, LocalDate::of(1970, 2, 2)],
            [
                ChronoField::EPOCH_DAY(), -146097 * 5, ChronoField::YEAR(), 1970 - (400 * 5), LocalDate::of(1970 - (400 * 5), 1, 1)],
            [
                ChronoField::EPOCH_DAY(), 32, ChronoField::YEAR_OF_ERA(), 1970, LocalDate::of(1970, 2, 2)],
            [
                ChronoField::EPOCH_DAY(), -146097 * 5, ChronoField::YEAR_OF_ERA(), 1 - (1970 - (400 * 5)), LocalDate::of(1970 - (400 * 5), 1, 1)],
            [
                ChronoField::EPOCH_DAY(), 32, ChronoField::MONTH_OF_YEAR(), 2, LocalDate::of(1970, 2, 2)],
            [
                ChronoField::EPOCH_DAY(), 32, ChronoField::DAY_OF_YEAR(), 33, LocalDate::of(1970, 2, 2)],
            [
                ChronoField::EPOCH_DAY(), 32, ChronoField::DAY_OF_MONTH(), 2, LocalDate::of(1970, 2, 2)],
            [
                ChronoField::EPOCH_DAY(), 32, ChronoField::DAY_OF_WEEK(), 1, LocalDate::of(1970, 2, 2)],
        ];
    }

    /**
     * @dataProvider data_resolveTwoToDate
     */
    public function test_resolveTwoToDate(TemporalField $field1, $value1,
                                          TemporalField $field2, $value2,
                                          $expectedDate)
    {
        $str = $value1 . " " . $value2;
        $f = (new DateTimeFormatterBuilder())
            ->appendValue($field1)->appendLiteral(' ')
            ->appendValue($field2)->toFormatter();

        $accessor = $f->parse($str);
        $this->assertEquals($accessor->query(TemporalQueries::localDate()), $expectedDate);
        $this->assertEquals($accessor->query(TemporalQueries::localTime()), null);
    }

    //-----------------------------------------------------------------------
    function data_resolveTwoToTime()
    {
        return [// merge
            [
                ChronoField::HOUR_OF_DAY(), 8, ChronoField::MINUTE_OF_HOUR(), 6, LocalTime::of(8, 6)],

            // merge
            [
                ChronoField::AMPM_OF_DAY(), 0, ChronoField::HOUR_OF_AMPM(), 5, LocalTime::of(5, 0)],
            [
                ChronoField::AMPM_OF_DAY(), 1, ChronoField::HOUR_OF_AMPM(), 5, LocalTime::of(17, 0)],
            [
                ChronoField::AMPM_OF_DAY(), 0, ChronoField::CLOCK_HOUR_OF_AMPM(), 5, LocalTime::of(5, 0)],
            [
                ChronoField::AMPM_OF_DAY(), 1, ChronoField::CLOCK_HOUR_OF_AMPM(), 5, LocalTime::of(17, 0)],
            [
                ChronoField::AMPM_OF_DAY(), 0, ChronoField::HOUR_OF_DAY(), 5, LocalTime::of(5, 0)],
            [
                ChronoField::AMPM_OF_DAY(), 1, ChronoField::HOUR_OF_DAY(), 17, LocalTime::of(17, 0)],
            [
                ChronoField::AMPM_OF_DAY(), 0, ChronoField::CLOCK_HOUR_OF_DAY(), 5, LocalTime::of(5, 0)],
            [
                ChronoField::AMPM_OF_DAY(), 1, ChronoField::CLOCK_HOUR_OF_DAY(), 17, LocalTime::of(17, 0)],

            // merge
            [
                ChronoField::CLOCK_HOUR_OF_DAY(), 8, ChronoField::MINUTE_OF_HOUR(), 6, LocalTime::of(8, 6)],
            [
                ChronoField::CLOCK_HOUR_OF_DAY(), 24, ChronoField::MINUTE_OF_HOUR(), 6, LocalTime::of(0, 6)],
            // cross-check
            [
                ChronoField::CLOCK_HOUR_OF_DAY(), 8, ChronoField::HOUR_OF_DAY(), 8, LocalTime::of(8, 0)],
            [
                ChronoField::CLOCK_HOUR_OF_DAY(), 8, ChronoField::CLOCK_HOUR_OF_AMPM(), 8, LocalTime::of(8, 0)],
            [
                ChronoField::CLOCK_HOUR_OF_DAY(), 20, ChronoField::CLOCK_HOUR_OF_AMPM(), 8, LocalTime::of(20, 0)],
            [
                ChronoField::CLOCK_HOUR_OF_DAY(), 8, ChronoField::AMPM_OF_DAY(), 0, LocalTime::of(8, 0)],
            [
                ChronoField::CLOCK_HOUR_OF_DAY(), 20, ChronoField::AMPM_OF_DAY(), 1, LocalTime::of(20, 0)],

            // merge
            [
                ChronoField::MINUTE_OF_DAY(), 650, ChronoField::SECOND_OF_MINUTE(), 8, LocalTime::of(10, 50, 8)],
            // cross-check
            [
                ChronoField::MINUTE_OF_DAY(), 650, ChronoField::HOUR_OF_DAY(), 10, LocalTime::of(10, 50)],
            [
                ChronoField::MINUTE_OF_DAY(), 650, ChronoField::CLOCK_HOUR_OF_DAY(), 10, LocalTime::of(10, 50)],
            [
                ChronoField::MINUTE_OF_DAY(), 650, ChronoField::CLOCK_HOUR_OF_AMPM(), 10, LocalTime::of(10, 50)],
            [
                ChronoField::MINUTE_OF_DAY(), 650, ChronoField::AMPM_OF_DAY(), 0, LocalTime::of(10, 50)],
            [
                ChronoField::MINUTE_OF_DAY(), 650, ChronoField::MINUTE_OF_HOUR(), 50, LocalTime::of(10, 50)],

            // merge
            [
                ChronoField::SECOND_OF_DAY(), 3600 + 650, ChronoField::MILLI_OF_SECOND(), 2, LocalTime::of(1, 10, 50, 2000000)],
            [
                ChronoField::SECOND_OF_DAY(), 3600 + 650, ChronoField::MICRO_OF_SECOND(), 2, LocalTime::of(1, 10, 50, 2000)],
            [
                ChronoField::SECOND_OF_DAY(), 3600 + 650, ChronoField::NANO_OF_SECOND(), 2, LocalTime::of(1, 10, 50, 2)],
            // cross-check
            [
                ChronoField::SECOND_OF_DAY(), 3600 + 650, ChronoField::HOUR_OF_DAY(), 1, LocalTime::of(1, 10, 50)],
            [
                ChronoField::SECOND_OF_DAY(), 3600 + 650, ChronoField::MINUTE_OF_HOUR(), 10, LocalTime::of(1, 10, 50)],
            [
                ChronoField::SECOND_OF_DAY(), 3600 + 650, ChronoField::SECOND_OF_MINUTE(), 50, LocalTime::of(1, 10, 50)],

            // merge
            [
                ChronoField::MILLI_OF_DAY(), (3600 + 650) * 1000 + 2, ChronoField::MICRO_OF_SECOND(), 2004, LocalTime::of(1, 10, 50, 2004000)],
            [
                ChronoField::MILLI_OF_DAY(), (3600 + 650) * 1000 + 2, ChronoField::NANO_OF_SECOND(), 2000004, LocalTime::of(1, 10, 50, 2000004)],
            // cross-check
            [
                ChronoField::MILLI_OF_DAY(), (3600 + 650) * 1000 + 2, ChronoField::HOUR_OF_DAY(), 1, LocalTime::of(1, 10, 50, 2000000)],
            [
                ChronoField::MILLI_OF_DAY(), (3600 + 650) * 1000 + 2, ChronoField::MINUTE_OF_HOUR(), 10, LocalTime::of(1, 10, 50, 2000000)],
            [
                ChronoField::MILLI_OF_DAY(), (3600 + 650) * 1000 + 2, ChronoField::SECOND_OF_MINUTE(), 50, LocalTime::of(1, 10, 50, 2000000)],
            [
                ChronoField::MILLI_OF_DAY(), (3600 + 650) * 1000 + 2, ChronoField::MILLI_OF_SECOND(), 2, LocalTime::of(1, 10, 50, 2000000)],

            // merge
            [
                ChronoField::MICRO_OF_DAY(), (3600 + 650) * 1000000 + 2, ChronoField::NANO_OF_SECOND(), 2004, LocalTime::of(1, 10, 50, 2004)],
            // cross-check
            [
                ChronoField::MICRO_OF_DAY(), (3600 + 650) * 1000000 + 2, ChronoField::HOUR_OF_DAY(), 1, LocalTime::of(1, 10, 50, 2000)],
            [
                ChronoField::MICRO_OF_DAY(), (3600 + 650) * 1000000 + 2, ChronoField::MINUTE_OF_HOUR(), 10, LocalTime::of(1, 10, 50, 2000)],
            [
                ChronoField::MICRO_OF_DAY(), (3600 + 650) * 1000000 + 2, ChronoField::SECOND_OF_MINUTE(), 50, LocalTime::of(1, 10, 50, 2000)],
            [
                ChronoField::MICRO_OF_DAY(), (3600 + 650) * 1000000 + 2, ChronoField::MILLI_OF_SECOND(), 0, LocalTime::of(1, 10, 50, 2000)],
            [
                ChronoField::MICRO_OF_DAY(), (3600 + 650) * 1000000 + 2, ChronoField::MICRO_OF_SECOND(), 2, LocalTime::of(1, 10, 50, 2000)],

            // cross-check
            [
                ChronoField::NANO_OF_DAY(), (3600 + 650) * 1000000000 + 2, ChronoField::HOUR_OF_DAY(), 1, LocalTime::of(1, 10, 50, 2)],
            [
                ChronoField::NANO_OF_DAY(), (3600 + 650) * 1000000000 + 2, ChronoField::MINUTE_OF_HOUR(), 10, LocalTime::of(1, 10, 50, 2)],
            [
                ChronoField::NANO_OF_DAY(), (3600 + 650) * 1000000000 + 2, ChronoField::SECOND_OF_MINUTE(), 50, LocalTime::of(1, 10, 50, 2)],
            [
                ChronoField::NANO_OF_DAY(), (3600 + 650) * 1000000000 + 2, ChronoField::MILLI_OF_SECOND(), 0, LocalTime::of(1, 10, 50, 2)],
            [
                ChronoField::NANO_OF_DAY(), (3600 + 650) * 1000000000 + 2, ChronoField::MICRO_OF_SECOND(), 0, LocalTime::of(1, 10, 50, 2)],
            [
                ChronoField::NANO_OF_DAY(), (3600 + 650) * 1000000000 + 2, ChronoField::NANO_OF_SECOND(), 2, LocalTime::of(1, 10, 50, 2)],
        ];
    }

    /**
     * @dataProvider data_resolveTwoToTime
     */
    public function test_resolveTwoToTime(TemporalField $field1, $value1,
                                          TemporalField $field2, $value2,
                                          $expectedTime)
    {
        $str = $value1 . " " . $value2;
        $f = (new DateTimeFormatterBuilder())
            ->appendValue($field1)->appendLiteral(' ')
            ->appendValue($field2)->toFormatter();

        $accessor = $f->parse($str);
        $this->assertEquals($accessor->query(TemporalQueries::localDate()), null);
        $this->assertEquals($accessor->query(TemporalQueries::localTime()), $expectedTime);
    }

    //-----------------------------------------------------------------------
    function data_resolveThreeToDate()
    {
        return [// merge
            [
                ChronoField::YEAR(), 2012, ChronoField::MONTH_OF_YEAR(), 2, ChronoField::DAY_OF_MONTH(), 1, LocalDate::of(2012, 2, 1)],
            [
                ChronoField::YEAR(), 2012, ChronoField::ALIGNED_WEEK_OF_YEAR(), 5, ChronoField::ALIGNED_DAY_OF_WEEK_IN_YEAR(), 4, LocalDate::of(2012, 2, 1)],
            [
                ChronoField::YEAR(), 2012, ChronoField::ALIGNED_WEEK_OF_YEAR(), 5, ChronoField::DAY_OF_WEEK(), 3, LocalDate::of(2012, 2, 1)],

            // cross-check
            [
                ChronoField::YEAR(), 2012, ChronoField::DAY_OF_YEAR(), 32, ChronoField::DAY_OF_MONTH(), 1, LocalDate::of(2012, 2, 1)],
            [
                ChronoField::YEAR_OF_ERA(), 2012, ChronoField::DAY_OF_YEAR(), 32, ChronoField::DAY_OF_MONTH(), 1, LocalDate::of(2012, 2, 1)],
            [
                ChronoField::YEAR(), 2012, ChronoField::DAY_OF_YEAR(), 32, ChronoField::DAY_OF_WEEK(), 3, LocalDate::of(2012, 2, 1)],
            [
                ChronoField::PROLEPTIC_MONTH(), 2012 * 12 + (2 - 1), ChronoField::DAY_OF_MONTH(), 25, ChronoField::DAY_OF_WEEK(), 6, LocalDate::of(2012, 2, 25)],
        ];
    }

    /**
     * @dataProvider data_resolveThreeToDate
     */
    public function test_resolveThreeToDate(TemporalField $field1, $value1,
                                            TemporalField $field2, $value2,
                                            TemporalField $field3, $value3,
                                            $expectedDate)
    {
        $str = $value1 . " " . $value2 . " " . $value3;
        $f = (new DateTimeFormatterBuilder())
            ->appendValue($field1)->appendLiteral(' ')
            ->appendValue($field2)->appendLiteral(' ')
            ->appendValue($field3)->toFormatter();

        $accessor = $f->parse($str);
        $this->assertEquals($accessor->query(TemporalQueries::localDate()), $expectedDate);
        $this->assertEquals($accessor->query(TemporalQueries::localTime()), null);
    }

    //-----------------------------------------------------------------------
    function data_resolveFourToDate()
    {
        return [// merge
            [
                ChronoField::YEAR(), 2012, ChronoField::MONTH_OF_YEAR(), 2, ChronoField::ALIGNED_WEEK_OF_MONTH(), 1, ChronoField::ALIGNED_DAY_OF_WEEK_IN_MONTH(), 1, LocalDate::of(2012, 2, 1)],
            [
                ChronoField::YEAR(), 2012, ChronoField::MONTH_OF_YEAR(), 2, ChronoField::ALIGNED_WEEK_OF_MONTH(), 1, ChronoField::DAY_OF_WEEK(), 3, LocalDate::of(2012, 2, 1)],

            // cross-check
            [
                ChronoField::YEAR(), 2012, ChronoField::MONTH_OF_YEAR(), 2, ChronoField::DAY_OF_MONTH(), 1, ChronoField::DAY_OF_WEEK(), 3, LocalDate::of(2012, 2, 1)],
            [
                ChronoField::YEAR(), 2012, ChronoField::ALIGNED_WEEK_OF_YEAR(), 5, ChronoField::ALIGNED_DAY_OF_WEEK_IN_YEAR(), 4, ChronoField::DAY_OF_WEEK(), 3, LocalDate::of(2012, 2, 1)],
            [
                ChronoField::YEAR(), 2012, ChronoField::ALIGNED_WEEK_OF_YEAR(), 5, ChronoField::DAY_OF_WEEK(), 3, ChronoField::DAY_OF_MONTH(), 1, LocalDate::of(2012, 2, 1)],
        ];
    }

    /**
     * @dataProvider data_resolveFourToDate
     */
    public function test_resolveFourToDate(TemporalField $field1, $value1,
                                           TemporalField $field2, $value2,
                                           TemporalField $field3, $value3,
                                           TemporalField $field4, $value4,
                                           $expectedDate)
    {
        $str = $value1 . " " . $value2 . " " . $value3 . " " . $value4;
        $f = (new DateTimeFormatterBuilder())
            ->appendValue($field1)->appendLiteral(' ')
            ->appendValue($field2)->appendLiteral(' ')
            ->appendValue($field3)->appendLiteral(' ')
            ->appendValue($field4)->toFormatter();

        $accessor = $f->parse($str);
        $this->assertEquals($accessor->query(TemporalQueries::localDate()), $expectedDate);
        $this->assertEquals($accessor->query(TemporalQueries::localTime()), null);
    }

//-----------------------------------------------------------------------
    function data_resolveFourToTime()
    {
        return [// merge
            [
                null, 0, 0, 0, 0, LocalTime::of(0, 0, 0, 0), Period::ZERO()],
            [
                null, 1, 0, 0, 0, LocalTime::of(1, 0, 0, 0), Period::ZERO()],
            [
                null, 0, 2, 0, 0, LocalTime::of(0, 2, 0, 0), Period::ZERO()],
            [
                null, 0, 0, 3, 0, LocalTime::of(0, 0, 3, 0), Period::ZERO()],
            [
                null, 0, 0, 0, 4, LocalTime::of(0, 0, 0, 4), Period::ZERO()],
            [
                null, 1, 2, 3, 4, LocalTime::of(1, 2, 3, 4), Period::ZERO()],
            [
                null, 23, 59, 59, 123456789, LocalTime::of(23, 59, 59, 123456789), Period::ZERO()],

            [
                ResolverStyle::STRICT(), 14, 59, 60, 123456789, null, null],
            [
                ResolverStyle::SMART(), 14, 59, 60, 123456789, null, null],
            [
                ResolverStyle::LENIENT(), 14, 59, 60, 123456789, LocalTime::of(15, 0, 0, 123456789), Period::ZERO()],

            [
                ResolverStyle::STRICT(), 23, 59, 60, 123456789, null, null],
            [
                ResolverStyle::SMART(), 23, 59, 60, 123456789, null, null],
            [
                ResolverStyle::LENIENT(), 23, 59, 60, 123456789, LocalTime::of(0, 0, 0, 123456789), Period::ofDays(1)],

            [
                ResolverStyle::STRICT(), 24, 0, 0, 0, null, null],
            [
                ResolverStyle::SMART(), 24, 0, 0, 0, LocalTime::of(0, 0, 0, 0), Period::ofDays(1)],
            [
                ResolverStyle::LENIENT(), 24, 0, 0, 0, LocalTime::of(0, 0, 0, 0), Period::ofDays(1)],

            [
                ResolverStyle::STRICT(), 24, 1, 0, 0, null, null],
            [
                ResolverStyle::SMART(), 24, 1, 0, 0, null, null],
            [
                ResolverStyle::LENIENT(), 24, 1, 0, 0, LocalTime::of(0, 1, 0, 0), Period::ofDays(1)],

            [
                ResolverStyle::STRICT(), 25, 0, 0, 0, null, null],
            [
                ResolverStyle::SMART(), 25, 0, 0, 0, null, null],
            [
                ResolverStyle::LENIENT(), 25, 0, 0, 0, LocalTime::of(1, 0, 0, 0), Period::ofDays(1)],

            [
                ResolverStyle::STRICT(), 49, 2, 3, 4, null, null],
            [
                ResolverStyle::SMART(), 49, 2, 3, 4, null, null],
            [
                ResolverStyle::LENIENT(), 49, 2, 3, 4, LocalTime::of(1, 2, 3, 4), Period::ofDays(2)],

            [
                ResolverStyle::STRICT(), -1, 2, 3, 4, null, null],
            [
                ResolverStyle::SMART(), -1, 2, 3, 4, null, null],
            [
                ResolverStyle::LENIENT(), -1, 2, 3, 4, LocalTime::of(23, 2, 3, 4), Period::ofDays(-1)],

            [
                ResolverStyle::STRICT(), -6, 2, 3, 4, null, null],
            [
                ResolverStyle::SMART(), -6, 2, 3, 4, null, null],
            [
                ResolverStyle::LENIENT(), -6, 2, 3, 4, LocalTime::of(18, 2, 3, 4), Period::ofDays(-1)],

            [
                ResolverStyle::STRICT(), 25, 61, 61, 1123456789, null, null],
            [
                ResolverStyle::SMART(), 25, 61, 61, 1123456789, null, null],
            [
                ResolverStyle::LENIENT(), 25, 61, 61, 1123456789, LocalTime::of(2, 2, 2, 123456789), Period::ofDays(1)],
        ];
    }

    /**
     * @dataProvider data_resolveFourToTime
     */
    public function test_resolveFourToTime($style,
                                           $hour, $min, $sec, $nano, $expectedTime, $excessPeriod)
    {
        $f = (new DateTimeFormatterBuilder())
            ->parseDefaulting(ChronoField::HOUR_OF_DAY(), $hour)
            ->parseDefaulting(ChronoField::MINUTE_OF_HOUR(), $min)
            ->parseDefaulting(ChronoField::SECOND_OF_MINUTE(), $sec)
            ->parseDefaulting(ChronoField::NANO_OF_SECOND(), $nano)->toFormatter();

        $styles = ($style !== null ? [$style] : ResolverStyle::values());

        foreach ($styles as $s) {
            if ($expectedTime !== null) {
                $accessor = $f->withResolverStyle($s)->parse("");
                $this->assertEquals($accessor->query(TemporalQueries::localDate()), null, "ResolverStyle: " . $s);
                $this->assertEquals($accessor->query(TemporalQueries::localTime()), $expectedTime, "ResolverStyle: " . $s);
                $this->assertEquals($accessor->query(DateTimeFormatter::parsedExcessDays()), $excessPeriod, "ResolverStyle: " . $s);
            } else {
                try {
                    $f->withResolverStyle($style)->parse("");
                    $this->fail();
                } catch (DateTimeParseException $ex) {
                    // expected
                    $this->assertTrue(true);
                }
            }
        }
    }

    /**
     * @param LocalTime|null $expectedTime
     * @dataProvider data_resolveFourToTime
     */
    public function test_resolveThreeToTime($style,
                                            $hour, $min, $sec, $nano, $expectedTime, $excessPeriod)
    {
        $f = (new DateTimeFormatterBuilder())
            ->parseDefaulting(ChronoField::HOUR_OF_DAY(), $hour)
            ->parseDefaulting(ChronoField::MINUTE_OF_HOUR(), $min)
            ->parseDefaulting(ChronoField::SECOND_OF_MINUTE(), $sec)->toFormatter();

        $styles = ($style !== null ? [$style] : ResolverStyle::values());

        foreach ($styles as $s) {
            if ($expectedTime !== null) {
                $accessor = $f->withResolverStyle($s)->parse("");
                $this->assertEquals($accessor->query(TemporalQueries::localDate()), null, "ResolverStyle: " . $s);
                $this->assertEquals($accessor->query(TemporalQueries::localTime()), $expectedTime->minusNanos($nano), "ResolverStyle: " . $s);
                $this->assertEquals($accessor->query(DateTimeFormatter::parsedExcessDays()), $excessPeriod, "ResolverStyle: " . $s);
            } else {
                try {
                    $f->withResolverStyle($style)->parse("");
                    $this->fail();
                } catch (DateTimeParseException $ex) {
                    // expected
                    $this->assertTrue(true);
                }
            }
        }
    }

    /**
     * @dataProvider data_resolveFourToTime
     */
    public function test_resolveFourToDateTime($style,
                                               $hour, $min, $sec, $nano, $expectedTime, $excessPeriod)
    {
        $f = (new DateTimeFormatterBuilder())
            ->parseDefaulting(ChronoField::YEAR(), 2012)->parseDefaulting(ChronoField::MONTH_OF_YEAR(), 6)->parseDefaulting(ChronoField::DAY_OF_MONTH(), 30)
            ->parseDefaulting(ChronoField::HOUR_OF_DAY(), $hour)
            ->parseDefaulting(ChronoField::MINUTE_OF_HOUR(), $min)
            ->parseDefaulting(ChronoField::SECOND_OF_MINUTE(), $sec)
            ->parseDefaulting(ChronoField::NANO_OF_SECOND(), $nano)->toFormatter();


        $styles = ($style !== null ? [$style] : ResolverStyle::values());

        if ($expectedTime !== null && $excessPeriod !== null) {
            $expectedDate = LocalDate::of(2012, 6, 30)->plusAmount($excessPeriod);
            foreach ($styles as $s) {
                $accessor = $f->withResolverStyle($s)->parse("");
                $this->assertEquals($accessor->query(TemporalQueries::localDate()), $expectedDate, "ResolverStyle: " . $s);
                $this->assertEquals($accessor->query(TemporalQueries::localTime()), $expectedTime, "ResolverStyle: " . $s);
                $this->assertEquals($accessor->query(DateTimeFormatter::parsedExcessDays()), Period::ZERO(), "ResolverStyle: " . $s);
            }
        } else {
            $this->assertTrue(true);
        }
    }

    //-----------------------------------------------------------------------
    function data_resolveSecondOfDay()
    {
        return [[
            ResolverStyle::STRICT(), 0, 0, 0],
            [
                ResolverStyle::STRICT(), 1, 1, 0],
            [
                ResolverStyle::STRICT(), 86399, 86399, 0],
            [
                ResolverStyle::STRICT(), -1, null, 0],
            [
                ResolverStyle::STRICT(), 86400, null, 0],

            [
                ResolverStyle::SMART(), 0, 0, 0],
            [
                ResolverStyle::SMART(), 1, 1, 0],
            [
                ResolverStyle::SMART(), 86399, 86399, 0],
            [
                ResolverStyle::SMART(), -1, null, 0],
            [
                ResolverStyle::SMART(), 86400, null, 0],

            [
                ResolverStyle::LENIENT(), 0, 0, 0],
            [
                ResolverStyle::LENIENT(), 1, 1, 0],
            [
                ResolverStyle::LENIENT(), 86399, 86399, 0],
            [
                ResolverStyle::LENIENT(), -1, 86399, -1],
            [
                ResolverStyle::LENIENT(), 86400, 0, 1],
        ];
    }

    /**
     * @dataProvider data_resolveSecondOfDay
     */
    public function test_resolveSecondOfDay(ResolverStyle $style, $value, $expectedSecond, $expectedDays)
    {
        $str = strval($value);
        $f = (new DateTimeFormatterBuilder())->appendValue(ChronoField::SECOND_OF_DAY())->toFormatter();

        if ($expectedSecond !== null) {
            $accessor = $f->withResolverStyle($style)->parse($str);
            $this->assertEquals($accessor->query(TemporalQueries::localDate()), null);
            $this->assertEquals($accessor->query(TemporalQueries::localTime()), LocalTime::ofSecondOfDay($expectedSecond));
            $this->assertEquals($accessor->query(DateTimeFormatter::parsedExcessDays()), Period::ofDays($expectedDays));
        } else {
            try {
                $f->withResolverStyle($style)->parse($str);
                $this->fail();
            } catch (DateTimeParseException $ex) {
                // expected
                $this->assertTrue(true);
            }
        }
    }

    //-----------------------------------------------------------------------
    function data_resolveMinuteOfDay()
    {
        return [
            [
                ResolverStyle::STRICT(), 0, 0, 0],
            [
                ResolverStyle::STRICT(), 1, 1, 0],
            [
                ResolverStyle::STRICT(), 1439, 1439, 0],
            [
                ResolverStyle::STRICT(), -1, null, 0],
            [
                ResolverStyle::STRICT(), 1440, null, 0],

            [
                ResolverStyle::SMART(), 0, 0, 0],
            [
                ResolverStyle::SMART(), 1, 1, 0],
            [
                ResolverStyle::SMART(), 1439, 1439, 0],
            [
                ResolverStyle::SMART(), -1, null, 0],
            [
                ResolverStyle::SMART(), 1440, null, 0],

            [
                ResolverStyle::LENIENT(), 0, 0, 0],
            [
                ResolverStyle::LENIENT(), 1, 1, 0],
            [
                ResolverStyle::LENIENT(), 1439, 1439, 0],
            [
                ResolverStyle::LENIENT(), -1, 1439, -1],
            [
                ResolverStyle::LENIENT(), 1440, 0, 1],
        ];
    }

    /**
     * @dataProvider data_resolveMinuteOfDay
     */
    public function test_resolveMinuteOfDay(ResolverStyle $style, $value, $expectedMinute, $expectedDays)
    {
        $str = strval($value);
        $f = (new DateTimeFormatterBuilder())->appendValue(ChronoField::MINUTE_OF_DAY())->toFormatter();

        if ($expectedMinute !== null) {
            $accessor = $f->withResolverStyle($style)->parse($str);
            $this->assertEquals($accessor->query(TemporalQueries::localDate()), null);
            $this->assertEquals($accessor->query(TemporalQueries::localTime()), LocalTime::ofSecondOfDay($expectedMinute * 60));
            $this->assertEquals($accessor->query(DateTimeFormatter::parsedExcessDays()), Period::ofDays($expectedDays));
        } else {
            try {
                $f->withResolverStyle($style)->parse($str);
                $this->fail();
            } catch (DateTimeParseException $ex) {
                // expected
                $this->assertTrue(true);
            }
        }
    }

    //-----------------------------------------------------------------------
    function data_resolveClockHourOfDay()
    {
        return [
            [
                ResolverStyle::STRICT(), 1, 1, 0],
            [
                ResolverStyle::STRICT(), 24, 0, 0],
            [
                ResolverStyle::STRICT(), 0, null, 0],
            [
                ResolverStyle::STRICT(), -1, null, 0],
            [
                ResolverStyle::STRICT(), 25, null, 0],

            [
                ResolverStyle::SMART(), 1, 1, 0],
            [
                ResolverStyle::SMART(), 24, 0, 0],
            [
                ResolverStyle::SMART(), 0, 0, 0],
            [
                ResolverStyle::SMART(), -1, null, 0],
            [
                ResolverStyle::SMART(), 25, null, 0],

            [
                ResolverStyle::LENIENT(), 1, 1, 0],
            [
                ResolverStyle::LENIENT(), 24, 0, 0],
            [
                ResolverStyle::LENIENT(), 0, 0, 0],
            [
                ResolverStyle::LENIENT(), -1, 23, -1],
            [
                ResolverStyle::LENIENT(), 25, 1, 1],
        ];
    }

    /**
     * @dataProvider data_resolveClockHourOfDay
     */
    public function test_resolveClockHourOfDay(ResolverStyle $style, $value, $expectedHour, $expectedDays)
    {
        $str = strval($value);
        $f = (new DateTimeFormatterBuilder())->appendValue(ChronoField::CLOCK_HOUR_OF_DAY())->toFormatter();

        if ($expectedHour !== null) {
            $accessor = $f->withResolverStyle($style)->parse($str);
            $this->assertEquals($accessor->query(TemporalQueries::localDate()), null);
            $this->assertEquals($accessor->query(TemporalQueries::localTime()), LocalTime::of($expectedHour, 0));
            $this->assertEquals($accessor->query(DateTimeFormatter::parsedExcessDays()), Period::ofDays($expectedDays));
        } else {
            try {
                $f->withResolverStyle($style)->parse($str);
                $this->fail();
            } catch (DateTimeParseException $ex) {
                // expected
                $this->assertTrue(true);
            }
        }
    }

    //-----------------------------------------------------------------------
    function data_resolveClockHourOfAmPm()
    {
        return [
            [
                ResolverStyle::STRICT(), 1, 1],
            [
                ResolverStyle::STRICT(), 12, 0],
            [
                ResolverStyle::STRICT(), 0, null],
            [
                ResolverStyle::STRICT(), -1, null],
            [
                ResolverStyle::STRICT(), 13, null],

            [
                ResolverStyle::SMART(), 1, 1],
            [
                ResolverStyle::SMART(), 12, 0],
            [
                ResolverStyle::SMART(), 0, 0],
            [
                ResolverStyle::SMART(), -1, null],
            [
                ResolverStyle::SMART(), 13, null],

            [
                ResolverStyle::LENIENT(), 1, 1],
            [
                ResolverStyle::LENIENT(), 12, 0],
            [
                ResolverStyle::LENIENT(), 0, 0],
            [
                ResolverStyle::LENIENT(), -1, -1],
            [
                ResolverStyle::LENIENT(), 13, 13],
        ];
    }

    /**
     * @dataProvider data_resolveClockHourOfAmPm
     */
    public function test_resolveClockHourOfAmPm(ResolverStyle $style, $value, $expectedValue)
    {
        $str = strval($value);
        $f = (new DateTimeFormatterBuilder())->appendValue(ChronoField::CLOCK_HOUR_OF_AMPM())->toFormatter();

        if ($expectedValue !== null) {
            $accessor = $f->withResolverStyle($style)->parse($str);
            $this->assertEquals($accessor->query(TemporalQueries::localDate()), null);
            $this->assertEquals($accessor->query(TemporalQueries::localTime()), null);
            $this->assertEquals($accessor->isSupported(ChronoField::CLOCK_HOUR_OF_AMPM()), false);
            $this->assertEquals($accessor->isSupported(ChronoField::HOUR_OF_AMPM()), true);
            $this->assertEquals($accessor->getLong(ChronoField::HOUR_OF_AMPM()), $expectedValue);
        } else {
            try {
                $f->withResolverStyle($style)->parse($str);
                $this->fail();
            } catch (DateTimeParseException $ex) {
                // expected
                $this->assertTrue(true);
            }
        }
    }

    //-----------------------------------------------------------------------
    function data_resolveAmPm()
    {
        return [
            [
                ResolverStyle::STRICT(), 0, 0],
            [
                ResolverStyle::STRICT(), 1, 1],
            [
                ResolverStyle::STRICT(), -1, null],
            [
                ResolverStyle::STRICT(), 2, null],

            [
                ResolverStyle::SMART(), 0, 0],
            [
                ResolverStyle::SMART(), 1, 1],
            [
                ResolverStyle::SMART(), -1, null],
            [
                ResolverStyle::SMART(), 2, null],

            [
                ResolverStyle::LENIENT(), 0, 0],
            [
                ResolverStyle::LENIENT(), 1, 1],
            [
                ResolverStyle::LENIENT(), -1, -1],
            [
                ResolverStyle::LENIENT(), 2, 2],
        ];
    }

    /**
     * @dataProvider data_resolveAmPm
     */
    public function test_resolveAmPm(ResolverStyle $style, $value, $expectedValue)
    {
        $str = strval($value);
        $f = (new DateTimeFormatterBuilder())->appendValue(ChronoField::AMPM_OF_DAY())->toFormatter();

        if ($expectedValue !== null) {
            $accessor = $f->withResolverStyle($style)->parse($str);
            $this->assertEquals($accessor->query(TemporalQueries::localDate()), null);
            $this->assertEquals($accessor->query(TemporalQueries::localTime()), null);
            $this->assertEquals($accessor->isSupported(ChronoField::AMPM_OF_DAY()), true);
            $this->assertEquals($accessor->getLong(ChronoField::AMPM_OF_DAY()), $expectedValue);
        } else {
            try {
                $f->withResolverStyle($style)->parse($str);
                $this->fail();
            } catch (DateTimeParseException $ex) {
                // expected
                $this->assertTrue(true);
            }
        }
    }

    //-----------------------------------------------------------------------
    // SPEC: DateTimeFormatter.withChronology()
    public function test_withChronology_noOverride()
    {
        $f = (new DateTimeFormatterBuilder())->parseDefaulting(ChronoField::EPOCH_DAY(), 2)->toFormatter();
        $accessor = $f->parse("");
        $this->assertEquals($accessor->query(TemporalQueries::localDate()), LocalDate::of(1970, 1, 3));
        $this->assertEquals($accessor->query(TemporalQueries::localTime()), null);
        $this->assertEquals($accessor->query(TemporalQueries::chronology()), IsoChronology::INSTANCE());
    }

    public function test_withChronology_override()
    {
        $f = (new DateTimeFormatterBuilder())->parseDefaulting(ChronoField::EPOCH_DAY(), 2)->toFormatter();
        $f = $f->withChronology(MinguoChronology::INSTANCE());
        $accessor = $f->parse("");
        $this->assertEquals($accessor->query(TemporalQueries::localDate()), LocalDate::of(1970, 1, 3));
        $this->assertEquals($accessor->query(TemporalQueries::localTime()), null);
        $this->assertEquals($accessor->query(TemporalQueries::chronology()), MinguoChronology::INSTANCE());
    }

    public function test_withChronology_parsedChronology_noOverride()
    {
        $f = (new DateTimeFormatterBuilder())->parseDefaulting(ChronoField::EPOCH_DAY(), 2)->appendChronologyId()->toFormatter();
        $accessor = $f->parse("ThaiBuddhist");
        $this->assertEquals($accessor->query(TemporalQueries::localDate()), LocalDate::of(1970, 1, 3));
        $this->assertEquals($accessor->query(TemporalQueries::localTime()), null);
        $this->assertEquals($accessor->query(TemporalQueries::chronology()), ThaiBuddhistChronology::INSTANCE());
    }

    public function test_withChronology_parsedChronology_override()
    {
        $f = (new DateTimeFormatterBuilder())->parseDefaulting(ChronoField::EPOCH_DAY(), 2)->appendChronologyId()->toFormatter();
        $f = $f->withChronology(MinguoChronology::INSTANCE());
        $accessor = $f->parse("ThaiBuddhist");
        $this->assertEquals($accessor->query(TemporalQueries::localDate()), LocalDate::of(1970, 1, 3));
        $this->assertEquals($accessor->query(TemporalQueries::localTime()), null);
        $this->assertEquals($accessor->query(TemporalQueries::chronology()), ThaiBuddhistChronology::INSTANCE());
    }

    //-----------------------------------------------------------------------
    // SPEC: DateTimeFormatter.withZone()
    public function test_withZone_noOverride()
    {
        $f = (new DateTimeFormatterBuilder())->parseDefaulting(ChronoField::EPOCH_DAY(), 2)->toFormatter();
        $accessor = $f->parse("");
        $this->assertEquals($accessor->query(TemporalQueries::localDate()), LocalDate::of(1970, 1, 3));
        $this->assertEquals($accessor->query(TemporalQueries::localTime()), null);
        $this->assertEquals($accessor->query(TemporalQueries::zoneId()), null);
    }


    public function test_withZone_override()
    {
        $f = (new DateTimeFormatterBuilder())->parseDefaulting(ChronoField::EPOCH_DAY(), 2)->toFormatter();
        $f = $f->withZone(self::EUROPE_ATHENS());
        $accessor = $f->parse("");
        $this->assertEquals($accessor->query(TemporalQueries::localDate()), LocalDate::of(1970, 1, 3));
        $this->assertEquals($accessor->query(TemporalQueries::localTime()), null);
        $this->assertEquals($accessor->query(TemporalQueries::zoneId()), self::EUROPE_ATHENS());
    }


    public function test_withZone_parsedZone_noOverride()
    {
        $f = (new DateTimeFormatterBuilder())->parseDefaulting(ChronoField::EPOCH_DAY(), 2)->appendZoneId()->toFormatter();
        $accessor = $f->parse("Europe/Paris");
        $this->assertEquals($accessor->query(TemporalQueries::localDate()), LocalDate::of(1970, 1, 3));
        $this->assertEquals($accessor->query(TemporalQueries::localTime()), null);
        $this->assertEquals($accessor->query(TemporalQueries::zoneId()), self::EUROPE_PARIS());
    }


    public function test_withZone_parsedZone_override()
    {
        $f = (new DateTimeFormatterBuilder())->parseDefaulting(ChronoField::EPOCH_DAY(), 2)->appendZoneId()->toFormatter();
        $f = $f->withZone(self::EUROPE_ATHENS());
        $accessor = $f->parse("Europe/Paris");
        $this->assertEquals($accessor->query(TemporalQueries::localDate()), LocalDate::of(1970, 1, 3));
        $this->assertEquals($accessor->query(TemporalQueries::localTime()), null);
        $this->assertEquals($accessor->query(TemporalQueries::zoneId()), self::EUROPE_PARIS());
    }

    //-----------------------------------------------------------------------

    public function test_fieldResolvesToLocalTime()
    {
        $lt = LocalTime::of(12, 30, 40);
        $f = (new DateTimeFormatterBuilder())->appendValue(new ResolvingField($lt))->toFormatter();
        $accessor = $f->parse("1234567890");
        $this->assertEquals($accessor->query(TemporalQueries::localDate()), null);
        $this->assertEquals($accessor->query(TemporalQueries::localTime()), $lt);
    }

    //-------------------------------------------------------------------------

    public function test_fieldResolvesToChronoLocalDate_noOverrideChrono_matches()
    {
        $ldt = LocalDate::of(2010, 6, 30);
        $f = (new DateTimeFormatterBuilder())->appendValue(new ResolvingField($ldt))->toFormatter();
        $accessor = $f->parse("1234567890");
        $this->assertEquals($accessor->query(TemporalQueries::localDate()), LocalDate::of(2010, 6, 30));
        $this->assertEquals($accessor->query(TemporalQueries::localTime()), null);
        $this->assertEquals($accessor->query(TemporalQueries::chronology()), IsoChronology::INSTANCE());
    }


    public function test_fieldResolvesToChronoLocalDate_overrideChrono_matches()
    {
        $mdt = MinguoDate::of(100, 6, 30);
        $f = (new DateTimeFormatterBuilder())->appendValue(new ResolvingField($mdt))->toFormatter();
        $f = $f->withChronology(MinguoChronology::INSTANCE());
        $accessor = $f->parse("1234567890");
        $this->assertEquals($accessor->query(TemporalQueries::localDate()), LocalDate::from($mdt));
        $this->assertEquals($accessor->query(TemporalQueries::localTime()), null);
        $this->assertEquals($accessor->query(TemporalQueries::chronology()), MinguoChronology::INSTANCE());
    }

    /**
     * @expectedException \Celest\DateTimeParseException
     */
    public function test_fieldResolvesToChronoLocalDate_noOverrideChrono_wrongChrono()
    {
        $cld = ThaiBuddhistChronology::INSTANCE()->dateNow();
        $f = (new DateTimeFormatterBuilder())->appendValue(new ResolvingField($cld))->toFormatter();
        $f->parse("1234567890");
    }

    /**
     * @expectedException \Celest\DateTimeParseException
     */

    public function test_fieldResolvesToChronoLocalDate_overrideChrono_wrongChrono()
    {
        $cld = ThaiBuddhistChronology::INSTANCE()->dateNow();
        $f = (new DateTimeFormatterBuilder())->appendValue(new ResolvingField($cld))->toFormatter();
        $f = $f->withChronology(MinguoChronology::INSTANCE());
        $f->parse("1234567890");
    }

    //-------------------------------------------------------------------------

    public function test_fieldResolvesToChronoLocalDateTime_noOverrideChrono_matches()
    {
        $ldt = LocalDateTime::of(2010, 6, 30, 12, 30);
        $f = (new DateTimeFormatterBuilder())->appendValue(new ResolvingField($ldt))->toFormatter();
        $accessor = $f->parse("1234567890");
        $this->assertEquals($accessor->query(TemporalQueries::localDate()), LocalDate::of(2010, 6, 30));
        $this->assertEquals($accessor->query(TemporalQueries::localTime()), LocalTime::of(12, 30));
        $this->assertEquals($accessor->query(TemporalQueries::chronology()), IsoChronology::INSTANCE());
    }


    public function test_fieldResolvesToChronoLocalDateTime_overrideChrono_matches()
    {
        $mdt = MinguoDate::of(100, 6, 30);
        $f = (new DateTimeFormatterBuilder())->appendValue(new ResolvingField($mdt->atTime(LocalTime::NOON())))->toFormatter();
        $f = $f->withChronology(MinguoChronology::INSTANCE());
        $accessor = $f->parse("1234567890");
        $this->assertEquals($accessor->query(TemporalQueries::localDate()), LocalDate::from($mdt));
        $this->assertEquals($accessor->query(TemporalQueries::localTime()), LocalTime::NOON());
        $this->assertEquals($accessor->query(TemporalQueries::chronology()), MinguoChronology::INSTANCE());
    }

    /**
     * @expectedException \Celest\DateTimeParseException
     */
    public function test_fieldResolvesToChronoLocalDateTime_noOverrideChrono_wrongChrono()
    {
        $cldt = ThaiBuddhistChronology::INSTANCE()->dateNow()->atTime(LocalTime::NOON());
        $f = (new DateTimeFormatterBuilder())->appendValue(new ResolvingField($cldt))->toFormatter();
        $f->parse("1234567890");
    }

    /**
     * @expectedException \Celest\DateTimeParseException
     */
    public function test_fieldResolvesToChronoLocalDateTime_overrideChrono_wrongChrono()
    {
        $cldt = ThaiBuddhistChronology::INSTANCE()->dateNow()->atTime(LocalTime::NOON());
        $f = (new DateTimeFormatterBuilder())->appendValue(new ResolvingField($cldt))->toFormatter();
        $f = $f->withChronology(MinguoChronology::INSTANCE());
        $f->parse("1234567890");
    }

    //-------------------------------------------------------------------------

    public function test_fieldResolvesToChronoZonedDateTime_noOverrideChrono_matches()
    {
        $zdt = ZonedDateTime::of(2010, 6, 30, 12, 30, 0, 0, self::EUROPE_PARIS());
        $f = (new DateTimeFormatterBuilder())->appendValue(new ResolvingField($zdt))->toFormatter();
        $accessor = $f->parse("1234567890");
        $this->assertEquals($accessor->query(TemporalQueries::localDate()), LocalDate::of(2010, 6, 30));
        $this->assertEquals($accessor->query(TemporalQueries::localTime()), LocalTime::of(12, 30));
        $this->assertEquals($accessor->query(TemporalQueries::chronology()), IsoChronology::INSTANCE());
        $this->assertEquals($accessor->query(TemporalQueries::zoneId()), self::EUROPE_PARIS());
    }


    public function test_fieldResolvesToChronoZonedDateTime_overrideChrono_matches()
    {
        $mdt = MinguoDate::of(100, 6, 30);
        $mzdt = $mdt->atTime(LocalTime::NOON())->atZone(self::EUROPE_PARIS());
        $f = (new DateTimeFormatterBuilder())->appendValue(new ResolvingField($mzdt))->toFormatter();
        $f = $f->withChronology(MinguoChronology::INSTANCE());
        $accessor = $f->parse("1234567890");
        $this->assertEquals($accessor->query(TemporalQueries::localDate()), LocalDate::from($mdt));
        $this->assertEquals($accessor->query(TemporalQueries::localTime()), LocalTime::NOON());
        $this->assertEquals($accessor->query(TemporalQueries::chronology()), MinguoChronology::INSTANCE());
        $this->assertEquals($accessor->query(TemporalQueries::zoneId()), self::EUROPE_PARIS());
    }

    /**
     * @expectedException \Celest\DateTimeParseException
     */
    public function test_fieldResolvesToChronoZonedDateTime_noOverrideChrono_wrongChrono()
    {
        $cldt = ThaiBuddhistChronology::INSTANCE()->dateNow()->atTime(LocalTime::NOON())->atZone(self::EUROPE_PARIS());
        $f = (new DateTimeFormatterBuilder())->appendValue(new ResolvingField($cldt))->toFormatter();
        $f->parse("1234567890");
    }

    /**
     * @expectedException \Celest\DateTimeParseException
     */
    public function test_fieldResolvesToChronoZonedDateTime_overrideChrono_wrongChrono()
    {
        $cldt = ThaiBuddhistChronology::INSTANCE()->dateNow()->atTime(LocalTime::NOON())->atZone(self::EUROPE_PARIS());
        $f = (new DateTimeFormatterBuilder())->appendValue(new ResolvingField($cldt))->toFormatter();
        $f = $f->withChronology(MinguoChronology::INSTANCE());
        $f->parse("1234567890");
    }


    public function test_fieldResolvesToChronoZonedDateTime_overrideZone_matches()
    {
        $zdt = ZonedDateTime::of(2010, 6, 30, 12, 30, 0, 0, self::EUROPE_PARIS());
        $f = (new DateTimeFormatterBuilder())->appendValue(new ResolvingField($zdt))->toFormatter();
        $f = $f->withZone(self::EUROPE_PARIS());
        $this->assertEquals($f->parseQuery("1234567890", TemporalQueries::fromCallable([ZonedDateTime::class, 'from'])), $zdt);
    }

    /**
     * @expectedException \Celest\DateTimeParseException
     */

    public function test_fieldResolvesToChronoZonedDateTime_overrideZone_wrongZone()
    {
        $zdt = ZonedDateTime::of(2010, 6, 30, 12, 30, 0, 0, self::EUROPE_PARIS());
        $f = (new DateTimeFormatterBuilder())->appendValue(new ResolvingField($zdt))->toFormatter();
        $f = $f->withZone(ZoneId::of("Europe/London"));
        $f->parse("1234567890");
    }

    //-------------------------------------------------------------------------
    // SPEC: ChronoField.ChronoField::INSTANT_SECONDS()

    public function test_parse_fromField_InstantSeconds()
    {
        $fmt = (new DateTimeFormatterBuilder())
            ->appendValue(ChronoField::INSTANT_SECONDS())->toFormatter();
        $acc = $fmt->parse("86402");
        $expected = Instant::ofEpochSecond(86402);
        $this->assertEquals($acc->isSupported(ChronoField::INSTANT_SECONDS()), true);
        $this->assertEquals($acc->isSupported(ChronoField::NANO_OF_SECOND()), true);
        $this->assertEquals($acc->isSupported(ChronoField::MICRO_OF_SECOND()), true);
        $this->assertEquals($acc->isSupported(ChronoField::MILLI_OF_SECOND()), true);
        $this->assertEquals($acc->getLong(ChronoField::INSTANT_SECONDS()), 86402);
        $this->assertEquals($acc->getLong(ChronoField::NANO_OF_SECOND()), 0);
        $this->assertEquals($acc->getLong(ChronoField::MICRO_OF_SECOND()), 0);
        $this->assertEquals($acc->getLong(ChronoField::MILLI_OF_SECOND()), 0);
        $this->assertEquals(Instant::from($acc), $expected);
    }


    public function test_parse_fromField_InstantSeconds_NanoOfSecond()
    {
        $fmt = (new DateTimeFormatterBuilder())
            ->appendValue(ChronoField::INSTANT_SECONDS())->appendLiteral('.')->appendValue(ChronoField::NANO_OF_SECOND())->toFormatter();
        $acc = $fmt->parse("86402.123456789");
        $expected = Instant::ofEpochSecond(86402, 123456789);
        $this->assertEquals($acc->isSupported(ChronoField::INSTANT_SECONDS()), true);
        $this->assertEquals($acc->isSupported(ChronoField::NANO_OF_SECOND()), true);
        $this->assertEquals($acc->isSupported(ChronoField::MICRO_OF_SECOND()), true);
        $this->assertEquals($acc->isSupported(ChronoField::MILLI_OF_SECOND()), true);
        $this->assertEquals($acc->getLong(ChronoField::INSTANT_SECONDS()), 86402);
        $this->assertEquals($acc->getLong(ChronoField::NANO_OF_SECOND()), 123456789);
        $this->assertEquals($acc->getLong(ChronoField::MICRO_OF_SECOND()), 123456);
        $this->assertEquals($acc->getLong(ChronoField::MILLI_OF_SECOND()), 123);
        $this->assertEquals(Instant::from($acc), $expected);
    }

    // SPEC: ChronoField.ChronoField::SECOND_OF_DAY()

    public function test_parse_fromField_SecondOfDay()
    {
        $fmt = (new DateTimeFormatterBuilder())
            ->appendValue(ChronoField::SECOND_OF_DAY())->toFormatter();
        $acc = $fmt->parse("864");
        $this->assertEquals($acc->isSupported(ChronoField::SECOND_OF_DAY()), true);
        $this->assertEquals($acc->isSupported(ChronoField::NANO_OF_SECOND()), true);
        $this->assertEquals($acc->isSupported(ChronoField::MICRO_OF_SECOND()), true);
        $this->assertEquals($acc->isSupported(ChronoField::MILLI_OF_SECOND()), true);
        $this->assertEquals($acc->getLong(ChronoField::SECOND_OF_DAY()), 864);
        $this->assertEquals($acc->getLong(ChronoField::NANO_OF_SECOND()), 0);
        $this->assertEquals($acc->getLong(ChronoField::MICRO_OF_SECOND()), 0);
        $this->assertEquals($acc->getLong(ChronoField::MILLI_OF_SECOND()), 0);
    }


    public function test_parse_fromField_SecondOfDay_NanoOfSecond()
    {
        $fmt = (new DateTimeFormatterBuilder())
            ->appendValue(ChronoField::SECOND_OF_DAY())->appendLiteral('.')->appendValue(ChronoField::NANO_OF_SECOND())->toFormatter();
        $acc = $fmt->parse("864.123456789");
        $this->assertEquals($acc->isSupported(ChronoField::SECOND_OF_DAY()), true);
        $this->assertEquals($acc->isSupported(ChronoField::NANO_OF_SECOND()), true);
        $this->assertEquals($acc->isSupported(ChronoField::MICRO_OF_SECOND()), true);
        $this->assertEquals($acc->isSupported(ChronoField::MILLI_OF_SECOND()), true);
        $this->assertEquals($acc->getLong(ChronoField::SECOND_OF_DAY()), 864);
        $this->assertEquals($acc->getLong(ChronoField::NANO_OF_SECOND()), 123456789);
        $this->assertEquals($acc->getLong(ChronoField::MICRO_OF_SECOND()), 123456);
        $this->assertEquals($acc->getLong(ChronoField::MILLI_OF_SECOND()), 123);
    }

    // SPEC: ChronoField.ChronoField::SECOND_OF_MINUTE()

    public function test_parse_fromField_SecondOfMinute()
    {
        $fmt = (new DateTimeFormatterBuilder())
            ->appendValue(ChronoField::SECOND_OF_MINUTE())->toFormatter();
        $acc = $fmt->parse("32");
        $this->assertEquals($acc->isSupported(ChronoField::SECOND_OF_MINUTE()), true);
        $this->assertEquals($acc->isSupported(ChronoField::NANO_OF_SECOND()), true);
        $this->assertEquals($acc->isSupported(ChronoField::MICRO_OF_SECOND()), true);
        $this->assertEquals($acc->isSupported(ChronoField::MILLI_OF_SECOND()), true);
        $this->assertEquals($acc->getLong(ChronoField::SECOND_OF_MINUTE()), 32);
        $this->assertEquals($acc->getLong(ChronoField::NANO_OF_SECOND()), 0);
        $this->assertEquals($acc->getLong(ChronoField::MICRO_OF_SECOND()), 0);
        $this->assertEquals($acc->getLong(ChronoField::MILLI_OF_SECOND()), 0);
    }


    public function test_parse_fromField_SecondOfMinute_NanoOfSecond()
    {
        $fmt = (new DateTimeFormatterBuilder())
            ->appendValue(ChronoField::SECOND_OF_MINUTE())->appendLiteral('.')->appendValue(ChronoField::NANO_OF_SECOND())->toFormatter();
        $acc = $fmt->parse("32.123456789");
        $this->assertEquals($acc->isSupported(ChronoField::SECOND_OF_MINUTE()), true);
        $this->assertEquals($acc->isSupported(ChronoField::NANO_OF_SECOND()), true);
        $this->assertEquals($acc->isSupported(ChronoField::MICRO_OF_SECOND()), true);
        $this->assertEquals($acc->isSupported(ChronoField::MILLI_OF_SECOND()), true);
        $this->assertEquals($acc->getLong(ChronoField::SECOND_OF_MINUTE()), 32);
        $this->assertEquals($acc->getLong(ChronoField::NANO_OF_SECOND()), 123456789);
        $this->assertEquals($acc->getLong(ChronoField::MICRO_OF_SECOND()), 123456);
        $this->assertEquals($acc->getLong(ChronoField::MILLI_OF_SECOND()), 123);
    }

}
