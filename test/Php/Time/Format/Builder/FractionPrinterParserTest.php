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
namespace Php\Time\Format\Builder;

use Php\Time\Format\ParsePosition;
use Php\Time\LocalTime;
use Php\Time\Temporal\ChronoField;
use Php\Time\Temporal\MockFieldValue;
use Php\Time\Temporal\TemporalAccessor;
use Php\Time\Temporal\TemporalField;

/**
 * Test FractionPrinterParser.
 */
class TestFractionPrinterParser extends AbstractTestPrinterParser
{

    protected function getFormatter(TemporalField $field, $minWidth, $maxWidth, $decimalPoint)
    {
        return $this->builder->appendFraction($field, $minWidth, $maxWidth, $decimalPoint)->toFormatter2($this->locale)->withDecimalStyle($this->decimalStyle);
    }

    //-----------------------------------------------------------------------
    // print
    //-----------------------------------------------------------------------
    /**
     * @expectedException \Php\Time\DateTimeException
     */
    public function test_print_emptyCalendrical()
    {
        $buf = '';
        $this->getFormatter(ChronoField::NANO_OF_SECOND(), 0, 9, true)->formatTo(self::EMPTY_DTA(), $buf);
    }

    public
    function test_print_append()
    {
        $buf = "EXISTING";
        $this->getFormatter(ChronoField::NANO_OF_SECOND(), 0, 9, true)->formatTo(LocalTime::of(12, 30, 40, 3), $buf);
        $this->assertEquals("EXISTING.000000003", $buf);
    }

//-----------------------------------------------------------------------
    public function provider_nanos()
    {
        return
            [
                [
                    2, 3, 2, ".000"],
                [
                    2, 3, 20, ".000"],
                [
                    2, 3, 200, ".000"],
                [
                    2, 3, 2000, ".000"],
                [
                    2, 3, 20000, ".000"],
                [
                    2, 3, 200000, ".000"],
                [
                    2, 3, 2000000, ".002"],
                [
                    2, 3, 20000000, ".02"],
                [
                    2, 3, 200000000, ".20"],
                [
                    2, 3, 1, ".000"],
                [
                    2, 3, 12, ".000"],
                [
                    2, 3, 123, ".000"],
                [
                    2, 3, 1234, ".000"],
                [
                    2, 3, 12345, ".000"],
                [
                    2, 3, 123456, ".000"],
                [
                    2, 3, 1234567, ".001"],
                [
                    2, 3, 12345678, ".012"],
                [
                    2, 3, 123456789, ".123"],

                [
                    6, 6, 0, ".000000"],
                [
                    6, 6, 2, ".000000"],
                [
                    6, 6, 20, ".000000"],
                [
                    6, 6, 200, ".000000"],
                [
                    6, 6, 2000, ".000002"],
                [
                    6, 6, 20000, ".000020"],
                [
                    6, 6, 200000, ".000200"],
                [
                    6, 6, 2000000, ".002000"],
                [
                    6, 6, 20000000, ".020000"],
                [
                    6, 6, 200000000, ".200000"],
                [
                    6, 6, 1, ".000000"],
                [
                    6, 6, 12, ".000000"],
                [
                    6, 6, 123, ".000000"],
                [
                    6, 6, 1234, ".000001"],
                [
                    6, 6, 12345, ".000012"],
                [
                    6, 6, 123456, ".000123"],
                [
                    6, 6, 1234567, ".001234"],
                [
                    6, 6, 12345678, ".012345"],
                [
                    6, 6, 123456789, ".123456"],
            ];
    }

    /**
     * @dataProvider provider_nanos
     */
    public function test_print_nanos($minWidth, $maxWidth, $value, $result)
    {
        $buf = '';
        $this->getFormatter(ChronoField::NANO_OF_SECOND(), $minWidth, $maxWidth, true)->formatTo(new MockFieldValue(ChronoField::NANO_OF_SECOND(), $value), $buf);
        if ($result === null) {
            $this->fail("Expected exception");
        }

        $this->assertEquals($result, $buf);
    }

    /**
     * @dataProvider provider_nanos
     */
    public function test_print_nanos_noDecimalPoint($minWidth, $maxWidth, $value, $result)
    {
        $this->getFormatter(ChronoField::NANO_OF_SECOND(), $minWidth, $maxWidth, false)->formatTo(new MockFieldValue(ChronoField::NANO_OF_SECOND(), $value), $buf);
        if ($result === null) {
            $this->fail("Expected exception");
        }

        $this->assertEquals((@$result[0] === "." ? substr($result, 1) : $result), $buf);
    }

//-----------------------------------------------------------------------
    function provider_seconds()
    {
        return
            [
                [
                    0, 9, 3, ".05"],
                [
                    0, 9, 6, ".1"],
                [
                    0, 9, 9, ".15"],
                [
                    0, 9, 12, ".2"],
                [
                    0, 9, 15, ".25"],
                [
                    0, 9, 30, ".5"],
                [
                    0, 9, 45, ".75"],

                [
                    2, 2, 0, ".00"],
                [
                    2, 2, 3, ".05"],
                [
                    2, 2, 6, ".10"],
                [
                    2, 2, 9, ".15"],
                [
                    2, 2, 12, ".20"],
                [
                    2, 2, 15, ".25"],
                [
                    2, 2, 30, ".50"],
                [
                    2, 2, 45, ".75"],
            ];
    }

    /**
     * @dataProvider provider_seconds
     */
    public function test_print_seconds($minWidth, $maxWidth, $value, $result)
    {
        $buf = '';
        $this->getFormatter(ChronoField::SECOND_OF_MINUTE(), $minWidth, $maxWidth, true)->formatTo(new MockFieldValue(ChronoField::SECOND_OF_MINUTE(), $value), $buf);
        if ($result === null) {
            $this->fail("Expected exception");
        }

        $this->assertEquals($result, $buf);
    }

    /**
     * @dataProvider provider_seconds
     */
    public function test_print_seconds_noDecimalPoint($minWidth, $maxWidth, $value, $result)
    {
        $this->getFormatter(ChronoField::SECOND_OF_MINUTE(), $minWidth, $maxWidth, false)->formatTo(new MockFieldValue(ChronoField::SECOND_OF_MINUTE(), $value), $buf);
        if ($result === null) {
            $this->fail("Expected exception");
        }

        $this->assertEquals((@$result[0] === "." ? substr($result, 1) : $result), $buf);
    }

//-----------------------------------------------------------------------
// parse
//-----------------------------------------------------------------------
    /**
     * @dataProvider provider_nanos
     */
    public function test_reverseParse($minWidth, $maxWidth, $value, $result)
    {
        $pos = new ParsePosition(0);
        $expectedValue = $this->fixParsedValue($maxWidth, $value);
        $parsed = $this->getFormatter(ChronoField::NANO_OF_SECOND(), $minWidth, $maxWidth, true)->parseUnresolved($result, $pos);
        $this->assertEquals(strlen($result), $pos->getIndex());
        $this->assertParsed($parsed, ChronoField::NANO_OF_SECOND(), $value == 0 && $minWidth == 0 ? null : $expectedValue);
    }

    /**
     * @dataProvider provider_nanos
     */
    public function test_reverseParse_noDecimalPoint($minWidth, $maxWidth, $value, $result)
    {
        $pos = new ParsePosition(@$result[0] === "." ? 1 : 0);
        $parsed = $this->getFormatter(ChronoField::NANO_OF_SECOND(), $minWidth, $maxWidth, false)->parseUnresolved($result, $pos);
        $this->assertEquals(strlen($result), $pos->getIndex());
        $expectedValue = $this->fixParsedValue($maxWidth, $value);
        $this->assertParsed($parsed, ChronoField::NANO_OF_SECOND(), $value == 0 && $minWidth == 0 ? null : $expectedValue);
    }

    /**
     * @dataProvider provider_nanos
     */
    public
    function test_reverseParse_followedByNonDigit($minWidth, $maxWidth, $value, $result)
    {
        $pos = new ParsePosition(0);
        $expectedValue = $this->fixParsedValue($maxWidth, $value);
        $parsed = $this->getFormatter(ChronoField::NANO_OF_SECOND(), $minWidth, $maxWidth, true)->parseUnresolved($result . " ", $pos);
        $this->assertEquals(strlen($result), $pos->getIndex());
        $this->assertParsed($parsed, ChronoField::NANO_OF_SECOND(), $value == 0 && $minWidth == 0 ? null : $expectedValue);
    }

//    @Test(dataProvider="Nanos")
//    public void test_reverseParse_followedByNonDigit_noDecimalPoint(int minWidth, int maxWidth, int value, String result){
//        FractionPrinterParser pp = new FractionPrinterParser(NANO_OF_SECOND, minWidth, maxWidth, false);
//        int newPos = pp.parse(parseContext, result + " ", (result.startsWith(".") ? 1 : 0));
//        assertEquals(newPos, result.length());
//        int expectedValue = fixParsedValue(maxWidth, value);
//        assertParsed(parseContext, NANO_OF_SECOND, value == 0 && minWidth == 0 ? null : (long) expectedValue);
//    }

    /**
     * @dataProvider provider_nanos
     */
    public
    function test_reverseParse_preceededByNonDigit($minWidth, $maxWidth, $value, $result)
    {
        $pos = new ParsePosition(1);
        $expectedValue = $this->fixParsedValue($maxWidth, $value);
        $parsed = $this->getFormatter(ChronoField::NANO_OF_SECOND(), $minWidth, $maxWidth, true)->parseUnresolved(" " . $result, $pos);
        $this->assertEquals(strlen($result) + 1, $pos->getIndex());
        $this->assertParsed($parsed, ChronoField::NANO_OF_SECOND(), $value == 0 && $minWidth == 0 ? null : $expectedValue);
    }

    private function fixParsedValue($maxWidth, $value)
    {
        if ($maxWidth < 9) {
            $power = (int)pow(10, (9 - $maxWidth));
            $value = (int)($value / $power) * $power;
        }

        return $value;
    }

    /**
     * @dataProvider provider_seconds
     */
    public
    function test_reverseParse_seconds($minWidth, $maxWidth, $value, $result)
    {
        $pos = new ParsePosition(0);
        $parsed = $this->getFormatter(ChronoField::SECOND_OF_MINUTE(), $minWidth, $maxWidth, true)->parseUnresolved($result, $pos);
        $this->assertEquals(strlen($result), $pos->getIndex());
        $this->assertParsed($parsed, ChronoField::SECOND_OF_MINUTE(), $value == 0 && $minWidth == 0 ? null : $value);
    }

    private
    function assertParsed(TemporalAccessor $parsed, TemporalField $field, $value)
    {
        if ($value === null) {
            $this->assertEquals(false, $parsed->isSupported($field));
        } else {
            $this->assertEquals(true, $parsed->isSupported($field));
            $this->assertEquals($value, $parsed->getLong($field));
        }
    }

//-----------------------------------------------------------------------
    public
    function provider_parseNothing()
    {
        return [[
            Chronofield::NANO_OF_SECOND(), 3, 6, true, "", 0, 0],
            [
                Chronofield::NANO_OF_SECOND(), 3, 6, true, "A", 0, 0],
            [
                Chronofield::NANO_OF_SECOND(), 3, 6, true, ".", 0, 1],
            [
                Chronofield::NANO_OF_SECOND(), 3, 6, true, ".5", 0, 1],
            [
                Chronofield::NANO_OF_SECOND(), 3, 6, true, ".51", 0, 1],
            [
                Chronofield::NANO_OF_SECOND(), 3, 6, true, ".A23456", 0, 1],
            [
                Chronofield::NANO_OF_SECOND(), 3, 6, true, ".1A3456", 0, 1],
        ];
    }

    /**
     * @dataProvider provider_parseNothing
     */
    public
    function test_parse_nothing(TemporalField $field, $min, $max, $decimalPoint, $text, $pos, $expected)
    {
        $ppos = new ParsePosition($pos);
        $parsed = $this->getFormatter($field, $min, $max, $decimalPoint)->parseUnresolved($text, $ppos);
        $this->assertEquals($expected, $ppos->getErrorIndex());
        $this->assertEquals(null, $parsed);
    }

//-----------------------------------------------------------------------
    public
    function test_toString()
    {
        $this->assertEquals("Fraction(NanoOfSecond,3,6,DecimalPoint)", $this->getFormatter(ChronoField::NANO_OF_SECOND(), 3, 6, true)->__toString());
    }

    public
    function test_toString_noDecimalPoint()
    {
        $this->assertEquals("Fraction(NanoOfSecond,3,6)", $this->getFormatter(ChronoField::NANO_OF_SECOND(), 3, 6, false)->__toString());
    }

}
