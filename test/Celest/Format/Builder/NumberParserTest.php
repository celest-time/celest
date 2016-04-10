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
namespace Celest\Format\Builder;

use Celest\Format\ParsePosition;
use Celest\Format\SignStyle;
use Celest\Helper\Integer;
use Celest\Helper\Long;
use Celest\Temporal\ChronoField;
use Celest\Temporal\TemporalField;
use Celest\Temporal\TemporalQueries;


/**
 * Test NumberPrinterParser.
 */
class NumberParserTest extends AbstractTestPrinterParser
{

    //-----------------------------------------------------------------------
    public function provider_dataError()
    {
        return [
            [
                ChronoField::DAY_OF_MONTH(), 1, 2, SignStyle::NEVER(), "12", -1, \OutOfRangeException::class],
            [
                ChronoField::DAY_OF_MONTH(), 1, 2, SignStyle::NEVER(), "12", 3, \OutOfRangeException::class],
        ];
    }

    /**
     * @dataProvider provider_dataError
     */
    public function test_parse_error(TemporalField $field, $min, $max, $style, $text, $pos, $expected)
    {
        try {
            $this->getFormatterWidth($field, $min, $max, $style)->parseUnresolved($text, new ParsePosition($pos));
            $this->fail();
        } catch (\Exception $ex) {
            $this->assertInstanceOf($expected, $ex);
        }
    }

//-----------------------------------------------------------------------
    function provider_parseData()
    {
        return
            [
// normal
                [
                    1, 2, SignStyle::NEVER(), 0, "12", 0, 2, 12
                ],       // normal
                [
                    1, 2, SignStyle::NEVER(), 0, "Xxx12Xxx", 3, 5, 12], // parse in middle
                [
                    1, 2, SignStyle::NEVER(), 0, "99912999", 3, 5, 12], // parse in middle
                [
                    2, 4, SignStyle::NEVER(), 0, "12345", 0, 4, 1234],  // stops at max width
                [
                    2, 4, SignStyle::NEVER(), 0, "12-45", 0, 2, 12],    // stops at dash
                [
                    2, 4, SignStyle::NEVER(), 0, "123-5", 0, 3, 123],   // stops at dash
                [
                    1, 10, SignStyle::NORMAL(), 0, "2147483647", 0, 10, Integer::MAX_VALUE],
                [
                    1, 10, SignStyle::NORMAL(), 0, "-2147483648", 0, 11, Integer::MIN_VALUE],
                [
                    1, 10, SignStyle::NORMAL(), 0, "2147483648", 0, 10, 2147483648],
                [
                    1, 10, SignStyle::NORMAL(), 0, "-2147483649", 0, 11, -2147483649],
                [
                    1, 10, SignStyle::NORMAL(), 0, "987659876598765", 0, 10, 9876598765],
                [
                    1, 19, SignStyle::NORMAL(), 0, "999999999999999999", 0, 18, 999999999999999999],
                [
                    1, 19, SignStyle::NORMAL(), 0, "-999999999999999999", 0, 19, -999999999999999999],
                [
                    1, 19, SignStyle::NORMAL(), 0, "1000000000000000000", 0, 19, 1000000000000000000],
                [
                    1, 19, SignStyle::NORMAL(), 0, "-1000000000000000000", 0, 20, -1000000000000000000],
                [
                    1, 19, SignStyle::NORMAL(), 0, "000000000000000000", 0, 18, 0],
                [
                    1, 19, SignStyle::NORMAL(), 0, "0000000000000000000", 0, 19, 0],
                [
                    1, 19, SignStyle::NORMAL(), 0, "9223372036854775807", 0, 19, Long::MAX_VALUE],
                [
                    1, 19, SignStyle::NORMAL(), 0, "-9223372036854775808", 0, 20, Long:: MIN_VALUE],
                [
                    1, 19, SignStyle::NORMAL(), 0, "9223372036854775808", 0, 18, 922337203685477580],  // last digit not parsed
                [
                    1, 19, SignStyle::NORMAL(), 0, "-9223372036854775809", 0, 19, -922337203685477580], // last digit not parsed
// no match
                [
                    1, 2, SignStyle::NEVER(), 1, "A1", 0, 0, 0],
                [
                    1, 2, SignStyle::NEVER(), 1, " 1", 0, 0, 0],
                [
                    1, 2, SignStyle::NEVER(), 1, "  1", 1, 1, 0],
                [
                    2, 2, SignStyle::NEVER(), 1, "1", 0, 0, 0],
                [
                    2, 2, SignStyle::NEVER(), 1, "Xxx1", 0, 0, 0],
                [
                    2, 2, SignStyle::NEVER(), 1, "1", 1, 1, 0],
                [
                    2, 2, SignStyle::NEVER(), 1, "Xxx1", 4, 4, 0],
                [
                    2, 2, SignStyle::NEVER(), 1, "1-2", 0, 0, 0],
                [
                    1, 19, SignStyle::NORMAL(), 0, "-000000000000000000", 0, 0, 0],
                [
                    1, 19, SignStyle::NORMAL(), 0, "-0000000000000000000", 0, 0, 0],
// parse reserving space 1 (adjacent-parsing)
                [
                    1, 1, SignStyle::NEVER(), 1, "12", 0, 1, 1],
                [
                    1, 19, SignStyle::NEVER(), 1, "12", 0, 1, 1],
                [
                    1, 19, SignStyle::NEVER(), 1, "12345", 0, 4, 1234],
                [
                    1, 19, SignStyle::NEVER(), 1, "12345678901", 0, 10, 1234567890],
                [
                    1, 19, SignStyle::NEVER(), 1, "123456789012345678901234567890", 0, 19, 1234567890123456789],
                [
                    1, 19, SignStyle::NEVER(), 1, "1", 0, 1, 1],  // error from next field
                [
                    2, 2, SignStyle::NEVER(), 1, "12", 0, 2, 12],  // error from next field
                [
                    2, 19, SignStyle::NEVER(), 1, "1", 0, 0, 0],
// parse reserving space 2 (adjacent-parsing)
                [
                    1, 1, SignStyle::NEVER(), 2, "123", 0, 1, 1],
                [
                    1, 19, SignStyle::NEVER(), 2, "123", 0, 1, 1],
                [
                    1, 19, SignStyle::NEVER(), 2, "12345", 0, 3, 123],
                [
                    1, 19, SignStyle::NEVER(), 2, "12345678901", 0, 9, 123456789],
                [
                    1, 19, SignStyle::NEVER(), 2, "123456789012345678901234567890", 0, 19, 1234567890123456789],
                [
                    1, 19, SignStyle::NEVER(), 2, "1", 0, 1, 1],  // error from next field
                [
                    1, 19, SignStyle::NEVER(), 2, "12", 0, 1, 1],  // error from next field
                [
                    2, 2, SignStyle::NEVER(), 2, "12", 0, 2, 12],  // error from next field
                [
                    2, 19, SignStyle::NEVER(), 2, "1", 0, 0, 0],
                [
                    2, 19, SignStyle::NEVER(), 2, "1AAAAABBBBBCCCCC", 0, 0, 0],
            ];
    }

//-----------------------------------------------------------------------
    /**
     * @dataProvider provider_parseData
     */
    public function test_parse_fresh($minWidth, $maxWidth, SignStyle $signStyle, $subsequentWidth, $text, $pos, $expectedPos, $expectedValue)
    {
        $ppos = new ParsePosition($pos);
        $dtf = $this->getFormatterWidth(ChronoField::DAY_OF_MONTH(), $minWidth, $maxWidth, $signStyle);
        if ($subsequentWidth > 0) {
// hacky, to reserve space
            $dtf = $this->builder->appendValue2(ChronoField::DAY_OF_YEAR(), $subsequentWidth)->toFormatter2($this->locale)->withDecimalStyle($this->decimalStyle);
        }

        $parsed = $dtf->parseUnresolved($text, $ppos);
        if ($ppos->getErrorIndex() !== -1) {
            $this->assertEquals($ppos->getErrorIndex(), $expectedPos);
        } else {
            $this->assertTrue($subsequentWidth >= 0);
            $this->assertEquals($ppos->getIndex(), $expectedPos + $subsequentWidth);
            $this->assertEquals($parsed->getLong(ChronoField::DAY_OF_MONTH()), $expectedValue);
            $this->assertEquals($parsed->query(TemporalQueries::chronology()), null);
            $this->assertEquals($parsed->query(TemporalQueries::zoneId()), null);
        }
    }

    /**
     * @dataProvider provider_parseData
     */
    public function test_parse_textField($minWidth, $maxWidth, SignStyle $signStyle, $subsequentWidth, $text, $pos, $expectedPos, $expectedValue)
    {
        $ppos = new ParsePosition($pos);
        $dtf = $this->getFormatterWidth(ChronoField::DAY_OF_WEEK(), $minWidth, $maxWidth, $signStyle);
        if ($subsequentWidth > 0) {
// hacky, to reserve space
            $dtf = $this->builder->appendValue2(ChronoField::DAY_OF_YEAR(), $subsequentWidth)->toFormatter2($this->locale)->withDecimalStyle($this->decimalStyle);
        }

        $parsed = $dtf->parseUnresolved($text, $ppos);
        if ($ppos->getErrorIndex() != -1) {
            $this->assertEquals($ppos->getErrorIndex(), $expectedPos);
        } else {
            $this->assertTrue($subsequentWidth >= 0);
            $this->assertEquals($ppos->getIndex(), $expectedPos + $subsequentWidth);
            $this->assertEquals($parsed->getLong(ChronoField::DAY_OF_WEEK()), $expectedValue);
            $this->assertEquals($parsed->query(TemporalQueries::chronology()), null);
            $this->assertEquals($parsed->query(TemporalQueries::zoneId()), null);
        }
    }

//-----------------------------------------------------------------------
    public function provider_parseSignsStrict()
    {
        return [
// basics
            [
                "0", 1, 2, SignStyle::NEVER(), 1, 0],
            [
                "1", 1, 2, SignStyle::NEVER(), 1, 1],
            [
                "2", 1, 2, SignStyle::NEVER(), 1, 2],
            [
                "3", 1, 2, SignStyle::NEVER(), 1, 3],
            [
                "4", 1, 2, SignStyle::NEVER(), 1, 4],
            [
                "5", 1, 2, SignStyle::NEVER(), 1, 5],
            [
                "6", 1, 2, SignStyle::NEVER(), 1, 6],
            [
                "7", 1, 2, SignStyle::NEVER(), 1, 7],
            [
                "8", 1, 2, SignStyle::NEVER(), 1, 8],
            [
                "9", 1, 2, SignStyle::NEVER(), 1, 9],
            [
                "10", 1, 2, SignStyle::NEVER(), 2, 10],
            [
                "100", 1, 2, SignStyle::NEVER(), 2, 10],
            [
                "100", 1, 3, SignStyle::NEVER(), 3, 100],

// never
            [
                "0", 1, 2, SignStyle::NEVER(), 1, 0],
            [
                "5", 1, 2, SignStyle::NEVER(), 1, 5],
            [
                "50", 1, 2, SignStyle::NEVER(), 2, 50],
            [
                "500", 1, 2, SignStyle::NEVER(), 2, 50],
            [
                "-0", 1, 2, SignStyle::NEVER(), 0, null],
            [
                "-5", 1, 2, SignStyle::NEVER(), 0, null],
            [
                "-50", 1, 2, SignStyle::NEVER(), 0, null],
            [
                "-500", 1, 2, SignStyle::NEVER(), 0, null],
            [
                "-AAA", 1, 2, SignStyle::NEVER(), 0, null],
            [
                "+0", 1, 2, SignStyle::NEVER(), 0, null],
            [
                "+5", 1, 2, SignStyle::NEVER(), 0, null],
            [
                "+50", 1, 2, SignStyle::NEVER(), 0, null],
            [
                "+500", 1, 2, SignStyle::NEVER(), 0, null],
            [
                "+AAA", 1, 2, SignStyle::NEVER(), 0, null],

// not negative
            [
                "0", 1, 2, SignStyle::NOT_NEGATIVE(), 1, 0],
            [
                "5", 1, 2, SignStyle::NOT_NEGATIVE(), 1, 5],
            [
                "50", 1, 2, SignStyle::NOT_NEGATIVE(), 2, 50],
            [
                "500", 1, 2, SignStyle::NOT_NEGATIVE(), 2, 50],
            [
                "-0", 1, 2, SignStyle::NOT_NEGATIVE(), 0, null],
            [
                "-5", 1, 2, SignStyle::NOT_NEGATIVE(), 0, null],
            [
                "-50", 1, 2, SignStyle::NOT_NEGATIVE(), 0, null],
            [
                "-500", 1, 2, SignStyle::NOT_NEGATIVE(), 0, null],
            [
                "-AAA", 1, 2, SignStyle::NOT_NEGATIVE(), 0, null],
            [
                "+0", 1, 2, SignStyle::NOT_NEGATIVE(), 0, null],
            [
                "+5", 1, 2, SignStyle::NOT_NEGATIVE(), 0, null],
            [
                "+50", 1, 2, SignStyle::NOT_NEGATIVE(), 0, null],
            [
                "+500", 1, 2, SignStyle::NOT_NEGATIVE(), 0, null],
            [
                "+AAA", 1, 2, SignStyle::NOT_NEGATIVE(), 0, null],

// normal
            [
                "0", 1, 2, SignStyle::NORMAL(), 1, 0],
            [
                "5", 1, 2, SignStyle::NORMAL(), 1, 5],
            [
                "50", 1, 2, SignStyle::NORMAL(), 2, 50],
            [
                "500", 1, 2, SignStyle::NORMAL(), 2, 50],
            [
                "-0", 1, 2, SignStyle::NORMAL(), 0, null],
            [
                "-5", 1, 2, SignStyle::NORMAL(), 2, -5],
            [
                "-50", 1, 2, SignStyle::NORMAL(), 3, -50],
            [
                "-500", 1, 2, SignStyle::NORMAL(), 3, -50],
            [
                "-AAA", 1, 2, SignStyle::NORMAL(), 1, null],
            [
                "+0", 1, 2, SignStyle::NORMAL(), 0, null],
            [
                "+5", 1, 2, SignStyle::NORMAL(), 0, null],
            [
                "+50", 1, 2, SignStyle::NORMAL(), 0, null],
            [
                "+500", 1, 2, SignStyle::NORMAL(), 0, null],
            [
                "+AAA", 1, 2, SignStyle::NORMAL(), 0, null],

// always
            [
                "0", 1, 2, SignStyle::ALWAYS(), 0, null],
            [
                "5", 1, 2, SignStyle::ALWAYS(), 0, null],
            [
                "50", 1, 2, SignStyle::ALWAYS(), 0, null],
            [
                "500", 1, 2, SignStyle::ALWAYS(), 0, null],
            [
                "-0", 1, 2, SignStyle::ALWAYS(), 0, null],
            [
                "-5", 1, 2, SignStyle::ALWAYS(), 2, -5],
            [
                "-50", 1, 2, SignStyle::ALWAYS(), 3, -50],
            [
                "-500", 1, 2, SignStyle::ALWAYS(), 3, -50],
            [
                "-AAA", 1, 2, SignStyle::ALWAYS(), 1, null],
            [
                "+0", 1, 2, SignStyle::ALWAYS(), 2, 0],
            [
                "+5", 1, 2, SignStyle::ALWAYS(), 2, 5],
            [
                "+50", 1, 2, SignStyle::ALWAYS(), 3, 50],
            [
                "+500", 1, 2, SignStyle::ALWAYS(), 3, 50],
            [
                "+AAA", 1, 2, SignStyle::ALWAYS(), 1, null],

// exceeds pad
            [
                "0", 1, 2, SignStyle::EXCEEDS_PAD(), 1, 0],
            [
                "5", 1, 2, SignStyle::EXCEEDS_PAD(), 1, 5],
            [
                "50", 1, 2, SignStyle::EXCEEDS_PAD(), 0, null],
            [
                "500", 1, 2, SignStyle::EXCEEDS_PAD(), 0, null],
            [
                "-0", 1, 2, SignStyle::EXCEEDS_PAD(), 0, null],
            [
                "-5", 1, 2, SignStyle::EXCEEDS_PAD(), 2, -5],
            [
                "-50", 1, 2, SignStyle::EXCEEDS_PAD(), 3, -50],
            [
                "-500", 1, 2, SignStyle::EXCEEDS_PAD(), 3, -50],
            [
                "-AAA", 1, 2, SignStyle::EXCEEDS_PAD(), 1, null],
            [
                "+0", 1, 2, SignStyle::EXCEEDS_PAD(), 0, null],
            [
                "+5", 1, 2, SignStyle::EXCEEDS_PAD(), 0, null],
            [
                "+50", 1, 2, SignStyle::EXCEEDS_PAD(), 3, 50],
            [
                "+500", 1, 2, SignStyle::EXCEEDS_PAD(), 3, 50],
            [
                "+AAA", 1, 2, SignStyle::EXCEEDS_PAD(), 1, null],
        ];
    }

    /**
     * @dataProvider provider_parseSignsStrict
     */
    public function test_parseSignsStrict($input, $min, $max, SignStyle $style, $parseLen, $parseVal)
    {
        $pos = new ParsePosition(0);
        $parsed = $this->getFormatterWidth(ChronoField::DAY_OF_MONTH(), $min, $max, $style)->parseUnresolved($input, $pos);
        if ($pos->getErrorIndex() != -1) {
            $this->assertEquals($pos->getErrorIndex(), $parseLen);
        } else {
            $this->assertEquals($pos->getIndex(), $parseLen);
            $this->assertEquals($parsed->getLong(ChronoField::DAY_OF_MONTH()), $parseVal);
            $this->assertEquals($parsed->query(TemporalQueries::chronology()), null);
            $this->assertEquals($parsed->query(TemporalQueries::zoneId()), null);
        }
    }

//-----------------------------------------------------------------------
    public function provider_parseSignsLenient()
    {
        return [
// never
            [
                "0", 1, 2, SignStyle::NEVER(), 1, 0],
            [
                "5", 1, 2, SignStyle::NEVER(), 1, 5],
            [
                "50", 1, 2, SignStyle::NEVER(), 2, 50],
            [
                "500", 1, 2, SignStyle::NEVER(), 3, 500],
            [
                "-0", 1, 2, SignStyle::NEVER(), 2, 0],
            [
                "-5", 1, 2, SignStyle::NEVER(), 2, -5],
            [
                "-50", 1, 2, SignStyle::NEVER(), 3, -50],
            [
                "-500", 1, 2, SignStyle::NEVER(), 4, -500],
            [
                "-AAA", 1, 2, SignStyle::NEVER(), 1, null],
            [
                "+0", 1, 2, SignStyle::NEVER(), 2, 0],
            [
                "+5", 1, 2, SignStyle::NEVER(), 2, 5],
            [
                "+50", 1, 2, SignStyle::NEVER(), 3, 50],
            [
                "+500", 1, 2, SignStyle::NEVER(), 4, 500],
            [
                "+AAA", 1, 2, SignStyle::NEVER(), 1, null],
            [
                "50", 2, 2, SignStyle::NEVER(), 2, 50],
            [
                "-50", 2, 2, SignStyle::NEVER(), 0, null],
            [
                "+50", 2, 2, SignStyle::NEVER(), 0, null],

// not negative
            [
                "0", 1, 2, SignStyle::NOT_NEGATIVE(), 1, 0],
            [
                "5", 1, 2, SignStyle::NOT_NEGATIVE(), 1, 5],
            [
                "50", 1, 2, SignStyle::NOT_NEGATIVE(), 2, 50],
            [
                "500", 1, 2, SignStyle::NOT_NEGATIVE(), 3, 500],
            [
                "-0", 1, 2, SignStyle::NOT_NEGATIVE(), 2, 0],
            [
                "-5", 1, 2, SignStyle::NOT_NEGATIVE(), 2, -5],
            [
                "-50", 1, 2, SignStyle::NOT_NEGATIVE(), 3, -50],
            [
                "-500", 1, 2, SignStyle::NOT_NEGATIVE(), 4, -500],
            [
                "-AAA", 1, 2, SignStyle::NOT_NEGATIVE(), 1, null],
            [
                "+0", 1, 2, SignStyle::NOT_NEGATIVE(), 2, 0],
            [
                "+5", 1, 2, SignStyle::NOT_NEGATIVE(), 2, 5],
            [
                "+50", 1, 2, SignStyle::NOT_NEGATIVE(), 3, 50],
            [
                "+500", 1, 2, SignStyle::NOT_NEGATIVE(), 4, 500],
            [
                "+AAA", 1, 2, SignStyle::NOT_NEGATIVE(), 1, null],
            [
                "50", 2, 2, SignStyle::NOT_NEGATIVE(), 2, 50],
            [
                "-50", 2, 2, SignStyle::NOT_NEGATIVE(), 0, null],
            [
                "+50", 2, 2, SignStyle::NOT_NEGATIVE(), 0, null],

// normal
            [
                "0", 1, 2, SignStyle::NORMAL(), 1, 0],
            [
                "5", 1, 2, SignStyle::NORMAL(), 1, 5],
            [
                "50", 1, 2, SignStyle::NORMAL(), 2, 50],
            [
                "500", 1, 2, SignStyle::NORMAL(), 3, 500],
            [
                "-0", 1, 2, SignStyle::NORMAL(), 2, 0],
            [
                "-5", 1, 2, SignStyle::NORMAL(), 2, -5],
            [
                "-50", 1, 2, SignStyle::NORMAL(), 3, -50],
            [
                "-500", 1, 2, SignStyle::NORMAL(), 4, -500],
            [
                "-AAA", 1, 2, SignStyle::NORMAL(), 1, null],
            [
                "+0", 1, 2, SignStyle::NORMAL(), 2, 0],
            [
                "+5", 1, 2, SignStyle::NORMAL(), 2, 5],
            [
                "+50", 1, 2, SignStyle::NORMAL(), 3, 50],
            [
                "+500", 1, 2, SignStyle::NORMAL(), 4, 500],
            [
                "+AAA", 1, 2, SignStyle::NORMAL(), 1, null],
            [
                "50", 2, 2, SignStyle::NORMAL(), 2, 50],
            [
                "-50", 2, 2, SignStyle::NORMAL(), 3, -50],
            [
                "+50", 2, 2, SignStyle::NORMAL(), 3, 50],

// always
            [
                "0", 1, 2, SignStyle::ALWAYS(), 1, 0],
            [
                "5", 1, 2, SignStyle::ALWAYS(), 1, 5],
            [
                "50", 1, 2, SignStyle::ALWAYS(), 2, 50],
            [
                "500", 1, 2, SignStyle::ALWAYS(), 3, 500],
            [
                "-0", 1, 2, SignStyle::ALWAYS(), 2, 0],
            [
                "-5", 1, 2, SignStyle::ALWAYS(), 2, -5],
            [
                "-50", 1, 2, SignStyle::ALWAYS(), 3, -50],
            [
                "-500", 1, 2, SignStyle::ALWAYS(), 4, -500],
            [
                "-AAA", 1, 2, SignStyle::ALWAYS(), 1, null],
            [
                "+0", 1, 2, SignStyle::ALWAYS(), 2, 0],
            [
                "+5", 1, 2, SignStyle::ALWAYS(), 2, 5],
            [
                "+50", 1, 2, SignStyle::ALWAYS(), 3, 50],
            [
                "+500", 1, 2, SignStyle::ALWAYS(), 4, 500],
            [
                "+AAA", 1, 2, SignStyle::ALWAYS(), 1, null],

// exceeds pad
            [
                "0", 1, 2, SignStyle::EXCEEDS_PAD(), 1, 0],
            [
                "5", 1, 2, SignStyle::EXCEEDS_PAD(), 1, 5],
            [
                "50", 1, 2, SignStyle::EXCEEDS_PAD(), 2, 50],
            [
                "500", 1, 2, SignStyle::EXCEEDS_PAD(), 3, 500],
            [
                "-0", 1, 2, SignStyle::EXCEEDS_PAD(), 2, 0],
            [
                "-5", 1, 2, SignStyle::EXCEEDS_PAD(), 2, -5],
            [
                "-50", 1, 2, SignStyle::EXCEEDS_PAD(), 3, -50],
            [
                "-500", 1, 2, SignStyle::EXCEEDS_PAD(), 4, -500],
            [
                "-AAA", 1, 2, SignStyle::EXCEEDS_PAD(), 1, null],
            [
                "+0", 1, 2, SignStyle::EXCEEDS_PAD(), 2, 0],
            [
                "+5", 1, 2, SignStyle::EXCEEDS_PAD(), 2, 5],
            [
                "+50", 1, 2, SignStyle::EXCEEDS_PAD(), 3, 50],
            [
                "+500", 1, 2, SignStyle::EXCEEDS_PAD(), 4, 500],
            [
                "+AAA", 1, 2, SignStyle::EXCEEDS_PAD(), 1, null],
        ];
    }

    /**
     * @dataProvider provider_parseSignsLenient
     */
    public function test_parseSignsLenient($input, $min, $max, SignStyle $style, $parseLen, $parseVal)
    {
        $this->setStrict(false);
        $pos = new ParsePosition(0);
        $parsed = $this->getFormatterWidth(ChronoField::DAY_OF_MONTH(), $min, $max, $style)->parseUnresolved($input, $pos);
        if ($pos->getErrorIndex() != -1) {
            $this->assertEquals($pos->getErrorIndex(), $parseLen);
        } else {
            $this->assertEquals($pos->getIndex(), $parseLen);
            $this->assertEquals($parsed->getLong(ChronoField::DAY_OF_MONTH()), $parseVal);
            $this->assertEquals($parsed->query(TemporalQueries::chronology()), null);
            $this->assertEquals($parsed->query(TemporalQueries::zoneId()), null);
        }
    }

//-----------------------------------------------------------------------
    public function provider_parseDigitsLenient()
    {
        return [
// never
            [
                "5", 1, 2, SignStyle::NEVER(), 1, 5],
            [
                "5", 2, 2, SignStyle::NEVER(), 1, 5],
            [
                "54", 1, 3, SignStyle::NEVER(), 2, 54],
            [
                "54", 2, 3, SignStyle::NEVER(), 2, 54],
            [
                "54", 3, 3, SignStyle::NEVER(), 2, 54],
            [
                "543", 1, 3, SignStyle::NEVER(), 3, 543],
            [
                "543", 2, 3, SignStyle::NEVER(), 3, 543],
            [
                "543", 3, 3, SignStyle::NEVER(), 3, 543],
            [
                "5432", 1, 3, SignStyle::NEVER(), 4, 5432],
            [
                "5432", 2, 3, SignStyle::NEVER(), 4, 5432],
            [
                "5432", 3, 3, SignStyle::NEVER(), 4, 5432],
            [
                "5AAA", 2, 3, SignStyle::NEVER(), 1, 5],

// not negative
            [
                "5", 1, 2, SignStyle::NOT_NEGATIVE(), 1, 5],
            [
                "5", 2, 2, SignStyle::NOT_NEGATIVE(), 1, 5],
            [
                "54", 1, 3, SignStyle::NOT_NEGATIVE(), 2, 54],
            [
                "54", 2, 3, SignStyle::NOT_NEGATIVE(), 2, 54],
            [
                "54", 3, 3, SignStyle::NOT_NEGATIVE(), 2, 54],
            [
                "543", 1, 3, SignStyle::NOT_NEGATIVE(), 3, 543],
            [
                "543", 2, 3, SignStyle::NOT_NEGATIVE(), 3, 543],
            [
                "543", 3, 3, SignStyle::NOT_NEGATIVE(), 3, 543],
            [
                "5432", 1, 3, SignStyle::NOT_NEGATIVE(), 4, 5432],
            [
                "5432", 2, 3, SignStyle::NOT_NEGATIVE(), 4, 5432],
            [
                "5432", 3, 3, SignStyle::NOT_NEGATIVE(), 4, 5432],
            [
                "5AAA", 2, 3, SignStyle::NOT_NEGATIVE(), 1, 5],

// normal
            [
                "5", 1, 2, SignStyle::NORMAL(), 1, 5],
            [
                "5", 2, 2, SignStyle::NORMAL(), 1, 5],
            [
                "54", 1, 3, SignStyle::NORMAL(), 2, 54],
            [
                "54", 2, 3, SignStyle::NORMAL(), 2, 54],
            [
                "54", 3, 3, SignStyle::NORMAL(), 2, 54],
            [
                "543", 1, 3, SignStyle::NORMAL(), 3, 543],
            [
                "543", 2, 3, SignStyle::NORMAL(), 3, 543],
            [
                "543", 3, 3, SignStyle::NORMAL(), 3, 543],
            [
                "5432", 1, 3, SignStyle::NORMAL(), 4, 5432],
            [
                "5432", 2, 3, SignStyle::NORMAL(), 4, 5432],
            [
                "5432", 3, 3, SignStyle::NORMAL(), 4, 5432],
            [
                "5AAA", 2, 3, SignStyle::NORMAL(), 1, 5],

// always
            [
                "5", 1, 2, SignStyle::ALWAYS(), 1, 5],
            [
                "5", 2, 2, SignStyle::ALWAYS(), 1, 5],
            [
                "54", 1, 3, SignStyle::ALWAYS(), 2, 54],
            [
                "54", 2, 3, SignStyle::ALWAYS(), 2, 54],
            [
                "54", 3, 3, SignStyle::ALWAYS(), 2, 54],
            [
                "543", 1, 3, SignStyle::ALWAYS(), 3, 543],
            [
                "543", 2, 3, SignStyle::ALWAYS(), 3, 543],
            [
                "543", 3, 3, SignStyle::ALWAYS(), 3, 543],
            [
                "5432", 1, 3, SignStyle::ALWAYS(), 4, 5432],
            [
                "5432", 2, 3, SignStyle::ALWAYS(), 4, 5432],
            [
                "5432", 3, 3, SignStyle::ALWAYS(), 4, 5432],
            [
                "5AAA", 2, 3, SignStyle::ALWAYS(), 1, 5],

// exceeds pad
            [
                "5", 1, 2, SignStyle::EXCEEDS_PAD(), 1, 5],
            [
                "5", 2, 2, SignStyle::EXCEEDS_PAD(), 1, 5],
            [
                "54", 1, 3, SignStyle::EXCEEDS_PAD(), 2, 54],
            [
                "54", 2, 3, SignStyle::EXCEEDS_PAD(), 2, 54],
            [
                "54", 3, 3, SignStyle::EXCEEDS_PAD(), 2, 54],
            [
                "543", 1, 3, SignStyle::EXCEEDS_PAD(), 3, 543],
            [
                "543", 2, 3, SignStyle::EXCEEDS_PAD(), 3, 543],
            [
                "543", 3, 3, SignStyle::EXCEEDS_PAD(), 3, 543],
            [
                "5432", 1, 3, SignStyle::EXCEEDS_PAD(), 4, 5432],
            [
                "5432", 2, 3, SignStyle::EXCEEDS_PAD(), 4, 5432],
            [
                "5432", 3, 3, SignStyle::EXCEEDS_PAD(), 4, 5432],
            [
                "5AAA", 2, 3, SignStyle::EXCEEDS_PAD(), 1, 5],
        ];
    }

    /**
     * @dataProvider provider_parseDigitsLenient
     */
    public function test_parseDigitsLenient($input, $min, $max, SignStyle $style, $parseLen, $parseVal)
    {
        $this->setStrict(false);
        $pos = new ParsePosition(0);
        $parsed = $this->getFormatterWidth(ChronoField::DAY_OF_MONTH(), $min, $max, $style)->parseUnresolved($input, $pos);
        if ($pos->getErrorIndex() != -1) {
            $this->assertEquals($pos->getErrorIndex(), $parseLen);
        } else {
            $this->assertEquals($pos->getIndex(), $parseLen);
            $this->assertEquals($parsed->getLong(ChronoField::DAY_OF_MONTH()), $parseVal);
            $this->assertEquals($parsed->query(TemporalQueries::chronology()), null);
            $this->assertEquals($parsed->query(TemporalQueries::zoneId()), null);
        }
    }

//-----------------------------------------------------------------------
    public function provider_parseDigitsAdjacentLenient()
    {
        return [
// never
            [
                "5", 1, null, null],
            [
                "54", 1, null, null],

            [
                "543", 3, 5, 43],
            [
                "543A", 3, 5, 43],

            [
                "5432", 4, 54, 32],
            [
                "5432A", 4, 54, 32],

            [
                "54321", 5, 543, 21],
            [
                "54321A", 5, 543, 21],
        ];
    }

    /**
     * @dataProvider provider_parseDigitsAdjacentLenient
     */
    public function test_parseDigitsAdjacentLenient($input, $parseLen, $parseMonth, $parsedDay)
    {
        $this->setStrict(false);
        $pos = new ParsePosition(0);
        $f = $this->builder
            ->appendValue3(ChronoField::MONTH_OF_YEAR(), 1, 2, SignStyle::NORMAL())
            ->appendValue2(ChronoField::DAY_OF_MONTH(), 2)->toFormatter2($this->locale)->withDecimalStyle($this->decimalStyle);
        $parsed = $f->parseUnresolved($input, $pos);
        if ($pos->getErrorIndex() !== -1) {
            $this->assertEquals($pos->getErrorIndex(), $parseLen);
        } else {
            $this->assertEquals($pos->getIndex(), $parseLen);
            $this->assertEquals($parsed->getLong(ChronoField::MONTH_OF_YEAR()), $parseMonth);
            $this->assertEquals($parsed->getLong(ChronoField::DAY_OF_MONTH()), $parsedDay);
            $this->assertEquals($parsed->query(TemporalQueries::chronology()), null);
            $this->assertEquals($parsed->query(TemporalQueries::zoneId()), null);
        }
    }

}
