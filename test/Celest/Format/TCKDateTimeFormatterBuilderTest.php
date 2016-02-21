<?php
/*
 * Copyright (c) 2012, 2013, Oracle and/or its affiliates. All rights reserved.
 * DO NOT ALTER OR REMOVE COPYRIGHT NOTICES OR THIS FILE HEADER.
 *
 * This code is free software; you can redistribute it and/or modify it
 * under the terms o$f the GNU General Public License version 2 only, as
 * published by the Free Software Foundation.
 *
 * This code is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty o$f MERCHANTABILITY or
 * FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License
 * version 2 for more details (a copy is included in the LICENSE file that
 * accompanied this code).
 *
 * You should have received a copy o$f the GNU General Public License version
 * 2 along with this work; i$f not, write to the Free Software Foundation,
 * Inc., 51 Franklin St, Fifth Floor, Boston, MA 02110-1301 USA.
 *
 * Please contact Oracle, 500 Oracle Parkway, Redwood Shores, CA 94065 USA
 * or visit www.oracle.com i$f you need additional information or have any
 * questions.
 */

/*
 * This file is available under and governed by the GNU General Public
 * License version 2 only, as published by the Free Software Foundation.
 * However, the following notice accompanied the original version o$f this
 * file:
 *
 * Copyright (c) 2009-2012, Stephen Colebourne & Michael Nascimento Santos
 *
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *
 *  * Redistributions o$f source code must retain the above copyright notice,
 *    this list o$f conditions and the following disclaimer.
 *
 *  * Redistributions in binary form must reproduce the above copyright notice,
 *    this list o$f conditions and the following disclaimer in the documentation
 *    and/or other materials provided with the distribution.
 *
 *  * Neither the name o$f JSR-310 nor the names o$f its contributors
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

use Celest\LocalDate;
use Celest\Locale;
use Celest\Temporal\ChronoField;
use Celest\Temporal\Temporal;
use Celest\Temporal\TemporalQueries;
use Celest\TestHelper;
use Celest\YearMonth;
use Celest\ZoneOffset;

/**
 * Test DateTimeFormatterBuilder.
 */
class TCKDateTimeFormatterBuilderTest extends \PHPUnit_Framework_TestCase
{

    /** @var DateTimeFormatterBuilder */
    private $builder;

    public function setUp()
    {
        $this->builder = new DateTimeFormatterBuilder();
    }

    //-----------------------------------------------------------------------

    public function test_toFormatter_empty()
    {
        $f = $this->builder->toFormatter();
        $this->assertEquals($f->format(LocalDate::of(2012, 6, 30)), "");
    }

    //-----------------------------------------------------------------------

    public function test_parseDefaulting_entireDate()
    {
        $f = $this->builder
            ->parseDefaulting(ChronoField::YEAR(), 2012)->parseDefaulting(ChronoField::MONTH_OF_YEAR(), 6)
            ->parseDefaulting(ChronoField::DAY_OF_MONTH(), 30)->toFormatter();
        $parsed = $f->parseQuery("", TemporalQueries::fromCallable([LocalDate::class, 'from']));  // blank string can be parsed
        $this->assertEquals($parsed, LocalDate::of(2012, 6, 30));
    }


    public function test_parseDefaulting_yearOptionalMonthOptionalDay()
    {
        $f = $this->builder
            ->appendValue(ChronoField::YEAR())
            ->optionalStart()->appendLiteral('-')->appendValue(ChronoField::MONTH_OF_YEAR())
            ->optionalStart()->appendLiteral('-')->appendValue(ChronoField::DAY_OF_MONTH())
            ->optionalEnd()->optionalEnd()
            ->parseDefaulting(ChronoField::MONTH_OF_YEAR(), 1)
            ->parseDefaulting(ChronoField::DAY_OF_MONTH(), 1)->toFormatter();
        $this->assertEquals($f->parseQuery("2012", TemporalQueries::fromCallable([LocalDate::class, 'from'])), LocalDate::of(2012, 1, 1));
        $this->assertEquals($f->parseQuery("2012-6", TemporalQueries::fromCallable([LocalDate::class, 'from'])), LocalDate::of(2012, 6, 1));
        $this->assertEquals($f->parseQuery("2012-6-30", TemporalQueries::fromCallable([LocalDate::class, 'from'])), LocalDate::of(2012, 6, 30));
    }

    public function test_parseDefaulting_null()
    {
        TestHelper::assertNullException($this, function () {
            $this->builder->parseDefaulting(null, 1);
        });
    }

    //-----------------------------------------------------------------------
    public function test_appendValue_1arg_null()
    {
        TestHelper::assertNullException($this, function () {
            $this->builder->appendValue(null);
        });
    }

    //-----------------------------------------------------------------------
    public function test_appendValue_2arg_null()
    {
        TestHelper::assertNullException($this, function () {
            $this->builder->appendValue2(null, 3);
        });
    }

    /**
     * @expectedException \Celest\IllegalArgumentException
     */
    public function test_appendValue_2arg_widthTooSmall()
    {
        $this->builder->appendValue2(ChronoField::DAY_OF_MONTH(), 0);
    }

    /**
     * @expectedException \Celest\IllegalArgumentException
     */
    public function test_appendValue_2arg_widthTooBig()
    {
        $this->builder->appendValue2(ChronoField::DAY_OF_MONTH(), 20);
    }

    //-----------------------------------------------------------------------
    public function test_appendValue_3arg_nullField()
    {
        TestHelper::assertNullException($this, function () {
            $this->builder->appendValue3(null, 2, 3, SignStyle::NORMAL());
        });
    }

    /**
     * @expectedException \Celest\IllegalArgumentException
     */
    public function test_appendValue_3arg_minWidthTooSmall()
    {
        $this->builder->appendValue3(ChronoField::DAY_OF_MONTH(), 0, 2, SignStyle::NORMAL());
    }

    /**
     * @expectedException \Celest\IllegalArgumentException
     */
    public function test_appendValue_3arg_minWidthTooBig()
    {
        $this->builder->appendValue3(ChronoField::DAY_OF_MONTH(), 20, 2, SignStyle::NORMAL());
    }

    /**
     * @expectedException \Celest\IllegalArgumentException
     */
    public function test_appendValue_3arg_maxWidthTooSmall()
    {
        $this->builder->appendValue3(ChronoField::DAY_OF_MONTH(), 2, 0, SignStyle::NORMAL());
    }

    /**
     * @expectedException \Celest\IllegalArgumentException
     */
    public function test_appendValue_3arg_maxWidthTooBig()
    {
        $this->builder->appendValue3(ChronoField::DAY_OF_MONTH(), 2, 20, SignStyle::NORMAL());
    }

    /**
     * @expectedException \Celest\IllegalArgumentException
     */
    public function test_appendValue_3arg_maxWidthMinWidth()
    {
        $this->builder->appendValue3(ChronoField::DAY_OF_MONTH(), 4, 2, SignStyle::NORMAL());
    }

    public function test_appendValue_3arg_nullSignStyle()
    {
        TestHelper::assertNullException($this, function () {
            $this->builder->appendValue3(ChronoField::DAY_OF_MONTH(), 2, 3, null);
        });
    }

    //-----------------------------------------------------------------------
    public function test_appendValueReduced_int_nullField()
    {
        TestHelper::assertNullException($this, function () {
            $this->builder->appendValueReduced(null, 2, 2, 2000);
        });
    }

    /**
     * @expectedException \Celest\IllegalArgumentException
     */
    public function test_appendValueReduced_int_minWidthTooSmall()
    {
        $this->builder->appendValueReduced(ChronoField::YEAR(), 0, 2, 2000);
    }

    /**
     * @expectedException \Celest\IllegalArgumentException
     */
    public function test_appendValueReduced_int_minWidthTooBig()
    {
        $this->builder->appendValueReduced(ChronoField::YEAR(), 11, 2, 2000);
    }

    /**
     * @expectedException \Celest\IllegalArgumentException
     */
    public function test_appendValueReduced_int_maxWidthTooSmall()
    {
        $this->builder->appendValueReduced(ChronoField::YEAR(), 2, 0, 2000);
    }

    /**
     * @expectedException \Celest\IllegalArgumentException
     */
    public function test_appendValueReduced_int_maxWidthTooBig()
    {
        $this->builder->appendValueReduced(ChronoField::YEAR(), 2, 11, 2000);
    }

    /**
     * @expectedException \Celest\IllegalArgumentException
     */
    public function test_appendValueReduced_int_maxWidthLessThanMin()
    {
        $this->builder->appendValueReduced(ChronoField::YEAR(), 2, 1, 2000);
    }

    //-----------------------------------------------------------------------
    public function test_appendValueReduced_date_nullField()
    {
        TestHelper::assertNullException($this, function () {
            $this->builder->appendValueReduced2(null, 2, 2, LocalDate::of(2000, 1, 1));
        });
    }

    public function test_appendValueReduced_date_nullDate()
    {
        TestHelper::assertNullException($this, function () {
            $this->builder->appendValueReduced2(ChronoField::YEAR(), 2, 2, null);
        });
    }

    /**
     * @expectedException \Celest\IllegalArgumentException
     */
    public function test_appendValueReduced_date_minWidthTooSmall()
    {
        $this->builder->appendValueReduced2(ChronoField::YEAR(), 0, 2, LocalDate::of(2000, 1, 1));
    }

    /**
     * @expectedException \Celest\IllegalArgumentException
     */
    public function test_appendValueReduced_date_minWidthTooBig()
    {
        $this->builder->appendValueReduced2(ChronoField::YEAR(), 11, 2, LocalDate::of(2000, 1, 1));
    }

    /**
     * @expectedException \Celest\IllegalArgumentException
     */
    public function test_appendValueReduced_date_maxWidthTooSmall()
    {
        $this->builder->appendValueReduced2(ChronoField::YEAR(), 2, 0, LocalDate::of(2000, 1, 1));
    }

    /**
     * @expectedException \Celest\IllegalArgumentException
     */
    public function test_appendValueReduced_date_maxWidthTooBig()
    {
        $this->builder->appendValueReduced2(ChronoField::YEAR(), 2, 11, LocalDate::of(2000, 1, 1));
    }

    /**
     * @expectedException \Celest\IllegalArgumentException
     */
    public function test_appendValueReduced_date_maxWidthLessThanMin()
    {
        $this->builder->appendValueReduced2(ChronoField::YEAR(), 2, 1, LocalDate::of(2000, 1, 1));
    }

    //-----------------------------------------------------------------------
    //-----------------------------------------------------------------------
    //-----------------------------------------------------------------------
    public function test_appendFraction_4arg_nullRule()
    {
        TestHelper::assertNullException($this, function () {
            $this->builder->appendFraction(null, 1, 9, false);
        });
    }

    /**
     * @expectedException \Celest\IllegalArgumentException
     */
    public function test_appendFraction_4arg_invalidRuleNotFixedSet()
    {
        $this->builder->appendFraction(ChronoField::DAY_OF_MONTH(), 1, 9, false);
    }

    /**
     * @expectedException \Celest\IllegalArgumentException
     */
    public function test_appendFraction_4arg_minTooSmall()
    {
        $this->builder->appendFraction(ChronoField::MINUTE_OF_HOUR(), -1, 9, false);
    }

    /**
     * @expectedException \Celest\IllegalArgumentException
     */
    public function test_appendFraction_4arg_minTooBig()
    {
        $this->builder->appendFraction(ChronoField::MINUTE_OF_HOUR(), 10, 9, false);
    }

    /**
     * @expectedException \Celest\IllegalArgumentException
     */
    public function test_appendFraction_4arg_maxTooSmall()
    {
        $this->builder->appendFraction(ChronoField::MINUTE_OF_HOUR(), 0, -1, false);
    }

    /**
     * @expectedException \Celest\IllegalArgumentException
     */
    public function test_appendFraction_4arg_maxTooBig()
    {
        $this->builder->appendFraction(ChronoField::MINUTE_OF_HOUR(), 1, 10, false);
    }

    /**
     * @expectedException \Celest\IllegalArgumentException
     */
    public function test_appendFraction_4arg_maxWidthMinWidth()
    {
        $this->builder->appendFraction(ChronoField::MINUTE_OF_HOUR(), 9, 3, false);
    }

    //-----------------------------------------------------------------------
    //-----------------------------------------------------------------------
    //-----------------------------------------------------------------------
    public function test_appendText_1arg_null()
    {
        TestHelper::assertNullException($this, function () {
            $this->builder->appendText(null);
        });
    }

    //-----------------------------------------------------------------------
    public function test_appendText_2arg_nullRule()
    {
        TestHelper::assertNullException($this, function () {
            $this->builder->appendText2(null, TextStyle::SHORT());
        });
    }

    public function test_appendText_2arg_nullStyle()
    {
        TestHelper::assertNullException($this, function () {
            $this->builder->appendText2(ChronoField::MONTH_OF_YEAR(), null);
        });
    }

    //-----------------------------------------------------------------------
    public function test_appendTextMap_nullRule()
    {
        TestHelper::assertNullException($this, function () {
            $this->builder->appendText3(null, []);
        });
    }

    public function test_appendTextMap_nullStyle()
    {
        TestHelper::assertNullException($this, function () {
            $this->builder->appendText3(ChronoField::MONTH_OF_YEAR(), null);
        });
    }

    //-----------------------------------------------------------------------
    //-----------------------------------------------------------------------
    //-----------------------------------------------------------------------
    function data_offsetPatterns()
    {
        return
            [
                [
                    "+HH", 2, 0, 0, "+02"
                ],
                [
                    "+HH", -2, 0, 0, "-02"],
                [
                    "+HH", 2, 30, 0, "+02"],
                [
                    "+HH", 2, 0, 45, "+02"],
                [
                    "+HH", 2, 30, 45, "+02"],

                [
                    "+HHMM", 2, 0, 0, "+0200"],
                [
                    "+HHMM", -2, 0, 0, "-0200"],
                [
                    "+HHMM", 2, 30, 0, "+0230"],
                [
                    "+HHMM", 2, 0, 45, "+0200"],
                [
                    "+HHMM", 2, 30, 45, "+0230"],

                [
                    "+HH:MM", 2, 0, 0, "+02:00"],
                [
                    "+HH:MM", -2, 0, 0, "-02:00"],
                [
                    "+HH:MM", 2, 30, 0, "+02:30"],
                [
                    "+HH:MM", 2, 0, 45, "+02:00"],
                [
                    "+HH:MM", 2, 30, 45, "+02:30"],

                [
                    "+HHMMss", 2, 0, 0, "+0200"],
                [
                    "+HHMMss", -2, 0, 0, "-0200"],
                [
                    "+HHMMss", 2, 30, 0, "+0230"],
                [
                    "+HHMMss", 2, 0, 45, "+020045"],
                [
                    "+HHMMss", 2, 30, 45, "+023045"],

                [
                    "+HH:MM:ss", 2, 0, 0, "+02:00"],
                [
                    "+HH:MM:ss", -2, 0, 0, "-02:00"],
                [
                    "+HH:MM:ss", 2, 30, 0, "+02:30"],
                [
                    "+HH:MM:ss", 2, 0, 45, "+02:00:45"],
                [
                    "+HH:MM:ss", 2, 30, 45, "+02:30:45"],

                [
                    "+HHMMSS", 2, 0, 0, "+020000"],
                [
                    "+HHMMSS", -2, 0, 0, "-020000"],
                [
                    "+HHMMSS", 2, 30, 0, "+023000"],
                [
                    "+HHMMSS", 2, 0, 45, "+020045"],
                [
                    "+HHMMSS", 2, 30, 45, "+023045"],

                [
                    "+HH:MM:SS", 2, 0, 0, "+02:00:00"],
                [
                    "+HH:MM:SS", -2, 0, 0, "-02:00:00"],
                [
                    "+HH:MM:SS", 2, 30, 0, "+02:30:00"],
                [
                    "+HH:MM:SS", 2, 0, 45, "+02:00:45"],
                [
                    "+HH:MM:SS", 2, 30, 45, "+02:30:45"],
            ];
    }

    /**
     * @dataProvider data_offsetPatterns
     */
    public function test_appendOffset_format($pattern, $h, $m, $s, $expected)
    {
        $this->builder->appendOffset($pattern, "Z");
        $f = $this->builder->toFormatter();
        $offset = ZoneOffset::ofHoursMinutesSeconds($h, $m, $s);
        $this->assertEquals($f->format($offset), $expected);
    }

    /**
     * @dataProvider data_offsetPatterns
     */
    public function test_appendOffset_parse($pattern, $h, $m, $s, $expected)
    {
        $this->builder->appendOffset($pattern, "Z");
        $f = $this->builder->toFormatter();
        $parsed = $f->parseQuery($expected, TemporalQueries::fromCallable([ZoneOffset::class, 'from']));
        $this->assertEquals($f->format($parsed), $expected);
    }

    function data_badOffsetPatterns()
    {
        return [
            [
                "HH"],
            [
                "HHMM"],
            [
                "HH:MM"],
            [
                "HHMMss"],
            [
                "HH:MM:ss"],
            [
                "HHMMSS"],
            [
                "HH:MM:SS"],
            [
                "+H"],
            [
                "+HMM"],
            [
                "+HHM"],
            [
                "+A"],
        ];
    }

    /**
     * @dataProvider data_badOffsetPatterns
     * @expectedException \Celest\IllegalArgumentException
     */
    public function test_appendOffset_badPattern($pattern)
    {
        $this->builder->appendOffset($pattern, "Z");
    }

    public function test_appendOffset_3arg_nullText()
    {
        TestHelper::assertNullException($this, function () {
            $this->builder->appendOffset("+HH:MM", null);
        });
    }

    public function test_appendOffset_3arg_nullPattern()
    {
        TestHelper::assertNullException($this, function () {
            $this->builder->appendOffset(null, "Z");
        });
    }

    //-----------------------------------------------------------------------
    //-----------------------------------------------------------------------
    //-----------------------------------------------------------------------
    public function test_appendZoneText_1arg_nullText()
    {
        TestHelper::assertNullException($this, function () {
            $this->builder->appendZoneText(null);
        });
    }

    //-----------------------------------------------------------------------
    //-----------------------------------------------------------------------
    //-----------------------------------------------------------------------

    public function test_padNext_1arg()
    {
        $this->builder->appendValue(ChronoField::MONTH_OF_YEAR())->appendLiteral(':')->padNext(2)->appendValue(ChronoField::DAY_OF_MONTH());
        $this->assertEquals($this->builder->toFormatter()->format(LocalDate::of(2013, 2, 1)), "2: 1");
    }

    /**
     * @expectedException \Celest\IllegalArgumentException
     */
    public function test_padNext_1arg_invalidWidth()
    {
        $this->builder->padNext(0);
    }

    //-----------------------------------------------------------------------

    public function test_padNext_2arg_dash()
    {
        $this->builder->appendValue(ChronoField::MONTH_OF_YEAR())->appendLiteral(':')->padNext2(2, '-')->appendValue(ChronoField::DAY_OF_MONTH());
        $this->assertEquals($this->builder->toFormatter()->format(LocalDate::of(2013, 2, 1)), "2:-1");
    }

    /**
     * @expectedException \Celest\IllegalArgumentException
     */
    public function test_padNext_2arg_invalidWidth()
    {
        $this->builder->padNext2(0, '-');
    }

    //-----------------------------------------------------------------------

    public function test_padOptional()
    {
        $this->builder->appendValue(ChronoField::MONTH_OF_YEAR())->appendLiteral(':')
            ->padNext(5)->optionalStart()->appendValue(ChronoField::DAY_OF_MONTH())->optionalEnd()
            ->appendLiteral(':')->appendValue(ChronoField::YEAR());
        $this->assertEquals($this->builder->toFormatter()->format(LocalDate::of(2013, 2, 1)), "2:    1:2013");
        $this->assertEquals($this->builder->toFormatter()->format(YearMonth::of(2013, 2)), "2:     :2013");
}

    //-----------------------------------------------------------------------
    //-----------------------------------------------------------------------
    //-----------------------------------------------------------------------
    /**
     * @expectedException \LogicException
     */
    public function test_optionalEnd_noStart()
    {
        $this->builder->optionalEnd();
    }

    //-----------------------------------------------------------------------
    //-----------------------------------------------------------------------
    //-----------------------------------------------------------------------
    function dataValid()
    {
        return [
            [
                "'a'"],
            [
                "''"],
            [
                "'!'"],
            [
                "!"],
            [
                "'#'"],

            [
                "'hello_people,][)('"],
            [
                "'hi'"],
            [
                "'yyyy'"],
            [
                "''''"],
            [
                "'o''clock'"],

            [
                "G"],
            [
                "GG"],
            [
                "GGG"],
            [
                "GGGG"],
            [
                "GGGGG"],

            [
                "y"],
            [
                "yy"],
            [
                "yyy"],
            [
                "yyyy"],
            [
                "yyyyy"],

            [
                "M"],
            [
                "MM"],
            [
                "MMM"],
            [
                "MMMM"],
            [
                "MMMMM"],

            [
                "L"],
            [
                "LL"],
            [
                "LLL"],
            [
                "LLLL"],
            [
                "LLLLL"],

            [
                "D"],
            [
                "DD"],
            [
                "DDD"],

            [
                "d"],
            [
                "dd"],

            [
                "F"],

            [
                "Q"],
            [
                "QQ"],
            [
                "QQQ"],
            [
                "QQQQ"],
            [
                "QQQQQ"],

            [
                "q"],
            [
                "qq"],
            [
                "qqq"],
            [
                "qqqq"],
            [
                "qqqqq"],

            [
                "E"],
            [
                "EE"],
            [
                "EEE"],
            [
                "EEEE"],
            [
                "EEEEE"],

            [
                "e"],
            [
                "ee"],
            [
                "eee"],
            [
                "eeee"],
            [
                "eeeee"],

            [
                "c"],
            [
                "ccc"],
            [
                "cccc"],
            [
                "ccccc"],

            [
                "a"],

            [
                "H"],
            [
                "HH"],

            [
                "K"],
            [
                "KK"],

            [
                "k"],
            [
                "kk"],

            [
                "h"],
            [
                "hh"],

            [
                "m"],
            [
                "mm"],

            [
                "s"],
            [
                "ss"],

            [
                "S"],
            [
                "SS"],
            [
                "SSS"],
            [
                "SSSSSSSSS"],

            [
                "A"],
            [
                "AA"],
            [
                "AAA"],

            [
                "n"],
            [
                "nn"],
            [
                "nnn"],

            [
                "N"],
            [
                "NN"],
            [
                "NNN"],

            [
                "z"],
            [
                "zz"],
            [
                "zzz"],
            [
                "zzzz"],

            [
                "VV"],

            [
                "Z"],
            [
                "ZZ"],
            [
                "ZZZ"],

            [
                "X"],
            [
                "XX"],
            [
                "XXX"],
            [
                "XXXX"],
            [
                "XXXXX"],

            [
                "x"],
            [
                "xx"],
            [
                "xxx"],
            [
                "xxxx"],
            [
                "xxxxx"],

            [
                "ppH"],
            [
                "pppDD"],

            [
                "yyyy[-MM[-dd"],
            [
                "yyyy[-MM[-dd]]"],
            [
                "yyyy[-MM[]-dd]"],

            [
                "yyyy-MM-dd'T'HH:mm:ss.SSS"],

            [
                "e"],
            [
                "w"],
            [
                "ww"],
            [
                "W"],
            [
                "W"],

        ];
    }

    /**
     * @dataProvider dataValid
     */
    public function test_appendPattern_valid($input)
    {
        $this->builder->appendPattern($input);  // test is for no error here
    }

    //-----------------------------------------------------------------------
    function dataInvalid()
    {
        return [
            [
                "'"],
            [
                "'hello"],
            [
                "'hel''lo"],
            [
                "'hello''"],
            [
                "{"],
            [
                "}"],
            [
                "{}"],
            [
                "#"],
            [
                "]"],
            [
                "yyyy]"],
            [
                "yyyy]MM"],
            [
                "yyyy[MM]]"],

            [
                "aa"],
            [
                "aaa"],
            [
                "aaaa"],
            [
                "aaaaa"],
            [
                "aaaaaa"],
            [
                "MMMMMM"],
            [
                "QQQQQQ"],
            [
                "qqqqqq"],
            [
                "EEEEEE"],
            [
                "eeeeee"],
            [
                "cc"],
            [
                "cccccc"],
            [
                "ddd"],
            [
                "DDDD"],
            [
                "FF"],
            [
                "FFF"],
            [
                "hhh"],
            [
                "HHH"],
            [
                "kkk"],
            [
                "KKK"],
            [
                "mmm"],
            [
                "sss"],
            [
                "OO"],
            [
                "OOO"],
            [
                "OOOOO"],
            [
                "XXXXXX"],
            [
                "zzzzz"],
            [
                "ZZZZZZ"],

            [
                "RO"],

            [
                "p"],
            [
                "pp"],
            [
                "p:"],

            [
                "f"],
            [
                "ff"],
            [
                "f:"],
            [
                "fy"],
            [
                "fa"],
            [
                "fM"],

            [
                "www"],
            [
                "WW"],
        ];
    }

    /**
     * @dataProvider dataInvalid
     * @expectedException \Celest\IllegalArgumentException
     */
    public function test_appendPattern_invalid($input)
    {
        $this->builder->appendPattern($input);  // test is for error here
    }

    //-----------------------------------------------------------------------
    function data_patternPrint()
    {
        return  [
        [
            "Q", LocalDate::of(2012, 2, 10), "1"],
        [
            "QQ", LocalDate::of(2012, 2, 10), "01"],
        [
            "QQQ", LocalDate::of(2012, 2, 10), "Q1"],
        [
            "QQQQ", LocalDate::of(2012, 2, 10), "1st quarter"],
        [
            "QQQQQ", LocalDate::of(2012, 2, 10), "1"],
    ];
    }

    /**
     * @dataProvider data_patternPrint
     */
    public function test_appendPattern_patternPrint($input, Temporal $temporal, $expected)
    {
        $f = $this->builder->appendPattern($input)->toFormatter2(Locale::UK());
        $test = $f->format($temporal);
        $this->assertEquals($test, $expected);
    }

    //-----------------------------------------------------------------------

    public function test_adjacent_strict_firstFixedWidth()
    {
        // succeeds because both number elements are fixed width
        $f = $this->builder->appendValue2(ChronoField::HOUR_OF_DAY(), 2)->appendValue2(ChronoField::MINUTE_OF_HOUR(), 2)->appendLiteral('9')->toFormatter2(Locale::UK());
        $pp = new ParsePosition(0);
        $parsed = $f->parseUnresolved("12309", $pp);
        $this->assertEquals($pp->getErrorIndex(), -1);
        $this->assertEquals($pp->getIndex(), 5);
        $this->assertEquals($parsed->getLong(ChronoField::HOUR_OF_DAY()), 12);
        $this->assertEquals($parsed->getLong(ChronoField::MINUTE_OF_HOUR()), 30);
    }


    public function test_adjacent_strict_firstVariableWidth_success()
    {
        // succeeds greedily parsing variable width, then fixed width, to non-numeric Z
        $f = $this->builder->appendValue(ChronoField::HOUR_OF_DAY())->appendValue2(ChronoField::MINUTE_OF_HOUR(), 2)->appendLiteral('Z')->toFormatter2(Locale::UK());
        $pp = new ParsePosition(0);
        $parsed = $f->parseUnresolved("12309Z", $pp);
        $this->assertEquals($pp->getErrorIndex(), -1);
        $this->assertEquals($pp->getIndex(), 6);
        $this->assertEquals($parsed->getLong(ChronoField::HOUR_OF_DAY()), 123);
        $this->assertEquals($parsed->getLong(ChronoField::MINUTE_OF_HOUR()), 9);
    }


    public function test_adjacent_strict_firstVariableWidth_fails()
    {
        // fails because literal is a number and variable width parse greedily absorbs it
        $f = $this->builder->appendValue(ChronoField::HOUR_OF_DAY())->appendValue2(ChronoField::MINUTE_OF_HOUR(), 2)->appendLiteral('9')->toFormatter2(Locale::UK());
        $pp = new ParsePosition(0);
        $parsed = $f->parseUnresolved("12309", $pp);
        $this->assertEquals($pp->getErrorIndex(), 5);
        $this->assertEquals($parsed, null);
    }


    public function test_adjacent_lenient()
    {
        // succeeds because both number elements are fixed width even in lenient mode
        $f = $this->builder->parseLenient()->appendValue2(ChronoField::HOUR_OF_DAY(), 2)->appendValue2(ChronoField::MINUTE_OF_HOUR(), 2)->appendLiteral('9')->toFormatter2(Locale::UK());
        $pp = new ParsePosition(0);
        $parsed = $f->parseUnresolved("12309", $pp);
        $this->assertEquals($pp->getErrorIndex(), -1);
        $this->assertEquals($pp->getIndex(), 5);
        $this->assertEquals($parsed->getLong(ChronoField::HOUR_OF_DAY()), 12);
        $this->assertEquals($parsed->getLong(ChronoField::MINUTE_OF_HOUR()), 30);
    }


    public function test_adjacent_lenient_firstVariableWidth_success()
    {
        // succeeds greedily parsing variable width, then fixed width, to non-numeric Z
        $f = $this->builder->parseLenient()->appendValue(ChronoField::HOUR_OF_DAY())->appendValue2(ChronoField::MINUTE_OF_HOUR(), 2)->appendLiteral('Z')->toFormatter2(Locale::UK());
        $pp = new ParsePosition(0);
        $parsed = $f->parseUnresolved("12309Z", $pp);
        $this->assertEquals($pp->getErrorIndex(), -1);
        $this->assertEquals($pp->getIndex(), 6);
        $this->assertEquals($parsed->getLong(ChronoField::HOUR_OF_DAY()), 123);
        $this->assertEquals($parsed->getLong(ChronoField::MINUTE_OF_HOUR()), 9);
    }


    public function test_adjacent_lenient_firstVariableWidth_fails()
    {
        // fails because literal is a number and variable width parse greedily absorbs it
        $f = $this->builder->parseLenient()->appendValue(ChronoField::HOUR_OF_DAY())->appendValue2(ChronoField::MINUTE_OF_HOUR(), 2)->appendLiteral('9')->toFormatter2(Locale::UK());
        $pp = new ParsePosition(0);
        $parsed = $f->parseUnresolved("12309", $pp);
        $this->assertEquals($pp->getErrorIndex(), 5);
        $this->assertEquals($parsed, null);
    }

    //-----------------------------------------------------------------------

    public function test_adjacent_strict_fractionFollows()
    {
        // succeeds because hour/min are fixed width
        $f = $this->builder->appendValue2(ChronoField::HOUR_OF_DAY(), 2)->appendValue2(ChronoField::MINUTE_OF_HOUR(), 2)->appendFraction(ChronoField::NANO_OF_SECOND(), 0, 3, false)->toFormatter2(Locale::UK());
        $pp = new ParsePosition(0);
        $parsed = $f->parseUnresolved("1230567", $pp);
        $this->assertEquals($pp->getErrorIndex(), -1);
        $this->assertEquals($pp->getIndex(), 7);
        $this->assertEquals($parsed->getLong(ChronoField::HOUR_OF_DAY()), 12);
        $this->assertEquals($parsed->getLong(ChronoField::MINUTE_OF_HOUR()), 30);
        $this->assertEquals($parsed->getLong(ChronoField::NANO_OF_SECOND()), 567000000);
    }


    public function test_adjacent_strict_fractionFollows_2digit()
    {
        // succeeds because hour/min are fixed width
        $f = $this->builder->appendValue2(ChronoField::HOUR_OF_DAY(), 2)->appendValue2(ChronoField::MINUTE_OF_HOUR(), 2)->appendFraction(ChronoField::NANO_OF_SECOND(), 0, 3, false)->toFormatter2(Locale::UK());
        $pp = new ParsePosition(0);
        $parsed = $f->parseUnresolved("123056", $pp);
        $this->assertEquals($pp->getErrorIndex(), -1);
        $this->assertEquals($pp->getIndex(), 6);
        $this->assertEquals($parsed->getLong(ChronoField::HOUR_OF_DAY()), 12);
        $this->assertEquals($parsed->getLong(ChronoField::MINUTE_OF_HOUR()), 30);
        $this->assertEquals($parsed->getLong(ChronoField::NANO_OF_SECOND()), 560000000);
    }


    public function test_adjacent_strict_fractionFollows_0digit()
    {
        // succeeds because hour/min are fixed width
        $f = $this->builder->appendValue2(ChronoField::HOUR_OF_DAY(), 2)->appendValue2(ChronoField::MINUTE_OF_HOUR(), 2)->appendFraction(ChronoField::NANO_OF_SECOND(), 0, 3, false)->toFormatter2(Locale::UK());
        $pp = new ParsePosition(0);
        $parsed = $f->parseUnresolved("1230", $pp);
        $this->assertEquals($pp->getErrorIndex(), -1);
        $this->assertEquals($pp->getIndex(), 4);
        $this->assertEquals($parsed->getLong(ChronoField::HOUR_OF_DAY()), 12);
        $this->assertEquals($parsed->getLong(ChronoField::MINUTE_OF_HOUR()), 30);
    }


    public function test_adjacent_lenient_fractionFollows()
    {
        // succeeds because hour/min are fixed width
        $f = $this->builder->parseLenient()->appendValue2(ChronoField::HOUR_OF_DAY(), 2)->appendValue2(ChronoField::MINUTE_OF_HOUR(), 2)->appendFraction(ChronoField::NANO_OF_SECOND(), 3, 3, false)->toFormatter2(Locale::UK());
        $pp = new ParsePosition(0);
        $parsed = $f->parseUnresolved("1230567", $pp);
        $this->assertEquals($pp->getErrorIndex(), -1);
        $this->assertEquals($pp->getIndex(), 7);
        $this->assertEquals($parsed->getLong(ChronoField::HOUR_OF_DAY()), 12);
        $this->assertEquals($parsed->getLong(ChronoField::MINUTE_OF_HOUR()), 30);
        $this->assertEquals($parsed->getLong(ChronoField::NANO_OF_SECOND()), 567000000);
    }


    public function test_adjacent_lenient_fractionFollows_2digit()
    {
        // succeeds because hour/min are fixed width
        $f = $this->builder->parseLenient()->appendValue2(ChronoField::HOUR_OF_DAY(), 2)->appendValue2(ChronoField::MINUTE_OF_HOUR(), 2)->appendFraction(ChronoField::NANO_OF_SECOND(), 3, 3, false)->toFormatter2(Locale::UK());
        $pp = new ParsePosition(0);
        $parsed = $f->parseUnresolved("123056", $pp);
        $this->assertEquals($pp->getErrorIndex(), -1);
        $this->assertEquals($pp->getIndex(), 6);
        $this->assertEquals($parsed->getLong(ChronoField::HOUR_OF_DAY()), 12);
        $this->assertEquals($parsed->getLong(ChronoField::MINUTE_OF_HOUR()), 30);
        $this->assertEquals($parsed->getLong(ChronoField::NANO_OF_SECOND()), 560000000);
    }


    public function test_adjacent_lenient_fractionFollows_0digit()
    {
        // succeeds because hour/min are fixed width
        $f = $this->builder->parseLenient()->appendValue2(ChronoField::HOUR_OF_DAY(), 2)->appendValue2(ChronoField::MINUTE_OF_HOUR(), 2)->appendFraction(ChronoField::NANO_OF_SECOND(), 3, 3, false)->toFormatter2(Locale::UK());
        $pp = new ParsePosition(0);
        $parsed = $f->parseUnresolved("1230", $pp);
        $this->assertEquals($pp->getErrorIndex(), -1);
        $this->assertEquals($pp->getIndex(), 4);
        $this->assertEquals($parsed->getLong(ChronoField::HOUR_OF_DAY()), 12);
        $this->assertEquals($parsed->getLong(ChronoField::MINUTE_OF_HOUR()), 30);
    }

}
