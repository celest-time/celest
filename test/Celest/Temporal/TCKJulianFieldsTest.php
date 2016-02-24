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

use Celest\Format\DateTimeFormatterBuilder;
use Celest\Format\ResolverStyle;
use Celest\LocalDate;

/**
 * Test Julian Fields.
 */
class TCKJulianFieldTest extends \PHPUnit_Framework_TestCase
{

    private static function JAN01_1970()
    {
        return LocalDate::of(1970, 1, 1);
    }

    private static function DEC31_1969()
    {
        return LocalDate::of(1969, 12, 31);
    }

    private static function NOV12_1945()
    {
        return LocalDate::of(1945, 11, 12);
    }

    private static function JAN01_0001()
    {
        return LocalDate::of(1, 1, 1);
    }

    function data_samples()
    {
        return [
            [ChronoField::EPOCH_DAY(), self::JAN01_1970(), 0],
            [JulianFields::JULIAN_DAY(), self::JAN01_1970(), 2400001 + 40587],
            [JulianFields::MODIFIED_JULIAN_DAY(), self::JAN01_1970(), 40587],
            [JulianFields::RATA_DIE(), self::JAN01_1970(), 710347 + (40587 - 31771)],

            [ChronoField::EPOCH_DAY(), self::DEC31_1969(), -1],
            [JulianFields::JULIAN_DAY(), self::DEC31_1969(), 2400001 + 40586],
            [JulianFields::MODIFIED_JULIAN_DAY(), self::DEC31_1969(), 40586],
            [JulianFields::RATA_DIE(), self::DEC31_1969(), 710347 + (40586 - 31771)],

            [ChronoField::EPOCH_DAY(), self::NOV12_1945(), (-24 * 365 - 6) - 31 - 30 + 11],
            [JulianFields::JULIAN_DAY(), self::NOV12_1945(), 2431772],
            [JulianFields::MODIFIED_JULIAN_DAY(), self::NOV12_1945(), 31771],
            [JulianFields::RATA_DIE(), self::NOV12_1945(), 710347],

            [ChronoField::EPOCH_DAY(), self::JAN01_0001(), (-24 * 365 - 6) - 31 - 30 + 11 - 710346],
            [JulianFields::JULIAN_DAY(), self::JAN01_0001(), 2431772 - 710346],
            [JulianFields::MODIFIED_JULIAN_DAY(), self::JAN01_0001(), 31771 - 710346],
            [JulianFields::RATA_DIE(), self::JAN01_0001(), 1],
        ];
    }

    //-----------------------------------------------------------------------
    public function test_basics()
    {
        $this->assertEquals(JulianFields::JULIAN_DAY()->isDateBased(), true);
        $this->assertEquals(JulianFields::JULIAN_DAY()->isTimeBased(), false);

        $this->assertEquals(JulianFields::MODIFIED_JULIAN_DAY()->isDateBased(), true);
        $this->assertEquals(JulianFields::MODIFIED_JULIAN_DAY()->isTimeBased(), false);

        $this->assertEquals(JulianFields::RATA_DIE()->isDateBased(), true);
        $this->assertEquals(JulianFields::RATA_DIE()->isTimeBased(), false);
    }

    //-----------------------------------------------------------------------
    /**
     * @dataProvider data_samples
     */
    public function test_samples_get(TemporalField $field, LocalDate $date, $expected)
    {
        $this->assertEquals($date->getLong($field), $expected);
    }

    /**
     * @dataProvider data_samples
     */
    public function test_samples_set(TemporalField $field, LocalDate $date, $value)
    {
        $this->assertEquals($field->adjustInto(LocalDate::MAX(), $value), $date);
        $this->assertEquals($field->adjustInto(LocalDate::MIN(), $value), $date);
        $this->assertEquals($field->adjustInto(self::JAN01_1970(), $value), $date);
        $this->assertEquals($field->adjustInto(self::DEC31_1969(), $value), $date);
        $this->assertEquals($field->adjustInto(self::NOV12_1945(), $value), $date);
    }

    //-----------------------------------------------------------------------
    /**
     * @dataProvider data_samples
     */
    public function test_samples_parse_STRICT(TemporalField $field, LocalDate $date, $value)
    {
        $f = (new DateTimeFormatterBuilder())->appendValue($field)
       ->toFormatter()->withResolverStyle(ResolverStyle::STRICT());
        $parsed = LocalDate::parseWith(strval($value), $f);
        $this->assertEquals($parsed, $date);
    }

    /**
     * @dataProvider data_samples
     */
    public function test_samples_parse_SMART(TemporalField $field, LocalDate $date, $value)
    {
        $f = (new DateTimeFormatterBuilder())->appendValue($field)
       ->toFormatter()->withResolverStyle(ResolverStyle::SMART());
        $parsed = LocalDate::parseWith(strval($value), $f);
        $this->assertEquals($parsed, $date);
    }

    /**
     * @dataProvider data_samples
     */
    public function test_samples_parse_LENIENT(TemporalField $field, LocalDate $date, $value)
    {
        $f = (new DateTimeFormatterBuilder())->appendValue($field)
       ->toFormatter()->withResolverStyle(ResolverStyle::LENIENT());
        $parsed = LocalDate::parseWith(strval($value), $f);
        $this->assertEquals($parsed, $date);
    }

}
