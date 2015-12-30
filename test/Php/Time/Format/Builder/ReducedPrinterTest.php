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
namespace Php\Time\Format\Builder;

use Php\Time\DateTimeException;
use Php\Time\Format\DateTimeFormatterBuilder;
use Php\Time\LocalDate;
use Php\Time\Temporal\ChronoField;
use Php\Time\Temporal\MockFieldValue;
use Php\Time\Temporal\TemporalField;

/**
 * Test ReducedPrinterParser.
 */
class ReducedPrinterTest extends AbstractTestPrinterParser
{

    private function getFormatter0(TemporalField $field, $width, $baseValue)
    {
        return $this->builder->appendValueReduced($field, $width, $width, $baseValue)->toFormatter2($this->locale)->withDecimalStyle($this->decimalStyle);
    }

    private function getFormatter1(TemporalField $field, $minWidth, $maxWidth, $baseValue)
    {
        return $this->builder->appendValueReduced($field, $minWidth, $maxWidth, $baseValue)->toFormatter2($this->locale)->withDecimalStyle($this->decimalStyle);
    }

    private
    function getFormatterBaseDate(TemporalField $field, $minWidth, $maxWidth, $baseValue)
    {
        return $this->builder->appendValueReduced2($field, $minWidth, $maxWidth, LocalDate::ofNumerical($baseValue, 1, 1))->toFormatter2($this->locale)->withDecimalStyle($this->decimalStyle);
    }

//-----------------------------------------------------------------------
    /**
     * @expectedException \Php\Time\DateTimeException
     */
    public function test_print_emptyCalendrical()
    {
        $buf = '';
        $this->getFormatter0(ChronoField::YEAR(), 2, 2010)->formatTo(self::EMPTY_DTA(), $buf);
    }

//-----------------------------------------------------------------------
    public
    function test_print_append()
    {
        $buf = "EXISTING";
        $this->getFormatter0(ChronoField::YEAR(), 2, 2010)->formatTo(LocalDate::ofNumerical(2012, 1, 1), $buf);
        $this->assertEquals($buf, "EXISTING12");
    }

//-----------------------------------------------------------------------
    public function provider_pivot()
    {
        return [
            [
                1, 1, 2010, 2010, "0"
            ],
            [
                1, 1, 2010, 2011, "1"],
            [
                1, 1, 2010, 2012, "2"],
            [
                1, 1, 2010, 2013, "3"],
            [
                1, 1, 2010, 2014, "4"],
            [
                1, 1, 2010, 2015, "5"],
            [
                1, 1, 2010, 2016, "6"],
            [
                1, 1, 2010, 2017, "7"],
            [
                1, 1, 2010, 2018, "8"],
            [
                1, 1, 2010, 2019, "9"],
            [
                1, 1, 2010, 2009, "9"],
            [
                1, 1, 2010, 2020, "0"],

            [
                2, 2, 2010, 2010, "10"],
            [
                2, 2, 2010, 2011, "11"],
            [
                2, 2, 2010, 2021, "21"],
            [
                2, 2, 2010, 2099, "99"],
            [
                2, 2, 2010, 2100, "00"],
            [
                2, 2, 2010, 2109, "09"],
            [
                2, 2, 2010, 2009, "09"],
            [
                2, 2, 2010, 2110, "10"],

            [
                2, 2, 2005, 2005, "05"],
            [
                2, 2, 2005, 2099, "99"],
            [
                2, 2, 2005, 2100, "00"],
            [
                2, 2, 2005, 2104, "04"],
            [
                2, 2, 2005, 2004, "04"],
            [
                2, 2, 2005, 2105, "05"],

            [
                3, 3, 2005, 2005, "005"],
            [
                3, 3, 2005, 2099, "099"],
            [
                3, 3, 2005, 2100, "100"],
            [
                3, 3, 2005, 2999, "999"],
            [
                3, 3, 2005, 3000, "000"],
            [
                3, 3, 2005, 3004, "004"],
            [
                3, 3, 2005, 2004, "004"],
            [
                3, 3, 2005, 3005, "005"],

            [
                9, 9, 2005, 2005, "000002005"],
            [
                9, 9, 2005, 2099, "000002099"],
            [
                9, 9, 2005, 2100, "000002100"],
            [
                9, 9, 2005, 999999999, "999999999"],
            [
                9, 9, 2005, 1000000000, "000000000"],
            [
                9, 9, 2005, 1000002004, "000002004"],
            [
                9, 9, 2005, 2004, "000002004"],
            [
                9, 9, 2005, 1000002005, "000002005"],

            [
                2, 2, -2005, -2005, "05"],
            [
                2, 2, -2005, -2000, "00"],
            [
                2, 2, -2005, -1999, "99"],
            [
                2, 2, -2005, -1904, "04"],
            [
                2, 2, -2005, -2006, "06"],
            [
                2, 2, -2005, -1905, "05"],

            [
                2, 4, 2005, 2099, "99"],
            [
                2, 4, 2005, 2100, "00"],
            [
                2, 4, 2005, 9999, "9999"],
            [
                2, 4, 2005, 1000000000, "00"],
            [
                2, 4, 2005, 1000002004, "2004"],
            [
                2, 4, 2005, 2004, "2004"],
            [
                2, 4, 2005, 2005, "05"],
            [
                2, 4, 2005, 2104, "04"],
            [
                2, 4, 2005, 2105, "2105"],
        ];
    }

    /**
     * @dataProvider provider_pivot
     */
    public function test_pivot($minWidth, $maxWidth, $baseValue, $value, $result)
    {
        $buf = '';
        try {
            $this->getFormatter1(ChronoField::YEAR(), $minWidth, $maxWidth, $baseValue)->formatTo(new MockFieldValue(ChronoField::YEAR(), $value), $buf);
            if ($result === null) {
                $this->fail("Expected exception");
            }

            $this->assertEquals($buf, $result);
        } catch
        (DateTimeException $ex) {
            if ($result === null || $value < 0) {
                $this->assertContains(ChronoField::YEAR()->__toString(), $ex->getMessage());
            } else {
                throw $ex;
            }
        }
    }

    /**
     * @dataProvider provider_pivot
     */
    public function test_pivot_baseDate($minWidth, $maxWidth, $baseValue, $value, $result)
    {
        $buf = '';
        try {
            $this->getFormatterBaseDate(ChronoField::YEAR(), $minWidth, $maxWidth, $baseValue)->formatTo(new MockFieldValue(ChronoField::YEAR(), $value), $buf);
            if ($result === null) {
                $this->fail("Expected exception");
            }

            $this->assertEquals($buf, $result);
        } catch
        (DateTimeException $ex) {
            if ($result == null || $value < 0) {
                $this->assertContains(ChronoField::YEAR()->__toString(), $ex->getMessage());
            } else {
                throw $ex;
            }
        }
    }

    //-----------------------------------------------------------------------
    /**
     * TODO enable
     */
    /**
     * public function test_minguoChrono_fixedWidth()
     * {
     * // ISO 2021 is Minguo 110
     * $f = $this->getFormatterBaseDate(ChronoField::YEAR(), 2, 2, 2021);
     * MinguoDate $date = MinguoDate::of(109, 6, 30);
     * $this->assertEquals(f . format(date), "09");
     * date = MinguoDate . of(110, 6, 30);
     * $this->assertEquals(f . format(date), "10");
     * date = MinguoDate . of(199, 6, 30);
     * $this->assertEquals(f . format(date), "99");
     * date = MinguoDate . of(200, 6, 30);
     * $this->assertEquals(f . format(date), "00");
     * date = MinguoDate . of(209, 6, 30);
     * $this->assertEquals(f . format(date), "09");
     * date = MinguoDate . of(210, 6, 30);
     * $this->assertEquals(f . format(date), "10");
     * }
     *
     * public
     * void test_minguoChrono_extendedWidth() throws Exception
     * {
     * // ISO 2021 is Minguo 110
     * DateTimeFormatter f = getFormatterBaseDate(YEAR, 2, 4, 2021);
     * MinguoDate date = MinguoDate . of(109, 6, 30);
     * assertEquals(f . format(date), "109");
     * date = MinguoDate . of(110, 6, 30);
     * assertEquals(f . format(date), "10");
     * date = MinguoDate . of(199, 6, 30);
     * assertEquals(f . format(date), "99");
     * date = MinguoDate . of(200, 6, 30);
     * assertEquals(f . format(date), "00");
     * date = MinguoDate . of(209, 6, 30);
     * assertEquals(f . format(date), "09");
     * date = MinguoDate . of(210, 6, 30);
     * assertEquals(f . format(date), "210");
     * }
     **/
//-----------------------------------------------------------------------
    public
    function test_toString()
    {
        $this->assertEquals($this->getFormatter1(ChronoField::YEAR(), 2, 2, 2005)->__toString(), "ReducedValue(Year,2,2,2005)");
    }

    //-----------------------------------------------------------------------
    // Cases and values in adjacent parsing mode
    //-----------------------------------------------------------------------
    public function provider_printAdjacent()
    {
        return
            [
                // general
                [
                    "yyMMdd", "990703", 1999, 7, 3
                ],
                [
                    "yyMMdd", "990703", 2099, 7, 3],
                [
                    "yyMMdd", "200703", 2020, 7, 3],
                [
                    "ddMMyy", "030714", 2014, 7, 3],
                [
                    "ddMMyy", "030720", 2020, 7, 3],
                [
                    "ddMMy", "03072001", 2001, 7, 3],
            ];
    }

    /**
     * @dataProvider provider_printAdjacent
     */
    public function test_printAdjacent($pattern, $text, $year, $month, $day)
    {
        $builder = new DateTimeFormatterBuilder();
        $builder->appendPattern($pattern);
        $dtf = $builder->toFormatter();

        $ld = LocalDate::ofNumerical($year, $month, $day);
        $actual = $dtf->format($ld);
        $this->assertEquals($text, $actual, "formatter output: " . $dtf);
    }

}
