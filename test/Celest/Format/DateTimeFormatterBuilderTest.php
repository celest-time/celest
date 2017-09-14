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
 * Copyright (c) 2009-2012, Stephen Colebourne & Michael Nascimento Santos
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

use Celest\Chrono\Chronology;
use Celest\Chrono\IsoChronology;
use Celest\Chrono\MinguoChronology;
use Celest\IllegalArgumentException;
use Celest\LocalDate;
use Celest\Locale;
use Celest\Temporal\ChronoField as CF;
use Celest\Temporal\Temporal;
use Celest\Temporal\TemporalQueries;
use Celest\TestHelper;
use Celest\YearMonth;
use Celest\ZoneOffset;
use PHPUnit\Framework\TestCase;

/**
 * Test DateTimeFormatterBuilder.
 */
class TestDateTimeFormatterBuilder extends TestCase
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
        $this->assertEquals($f->__toString(), "");
    }

    //-----------------------------------------------------------------------

    public function test_parseCaseSensitive()
    {
        $this->builder->parseCaseSensitive();
        $f = $this->builder->toFormatter();
        $this->assertEquals($f->__toString(), "ParseCaseSensitive(true)");
    }


    public function test_parseCaseInsensitive()
    {
        $this->builder->parseCaseInsensitive();
        $f = $this->builder->toFormatter();
        $this->assertEquals($f->__toString(), "ParseCaseSensitive(false)");
    }

    //-----------------------------------------------------------------------

    public function test_parseStrict()
    {
        $this->builder->parseStrict();
        $f = $this->builder->toFormatter();
        $this->assertEquals($f->__toString(), "ParseStrict(true)");
    }


    public function test_parseLenient()
    {
        $this->builder->parseLenient();
        $f = $this->builder->toFormatter();
        $this->assertEquals($f->__toString(), "ParseStrict(false)");
    }

    //-----------------------------------------------------------------------

    public function test_appendValue_1arg()
    {
        $this->builder->appendValue(CF::DAY_OF_MONTH());
        $f = $this->builder->toFormatter();
        $this->assertEquals($f->__toString(), "Value(DayOfMonth)");
    }

    public function test_appendValue_1arg_null()
    {
        TestHelper::assertNullException($this, function () {
            $this->builder->appendValue(null);
        });
    }

    //-----------------------------------------------------------------------

    public function test_appendValue_2arg()
    {
        $this->builder->appendValue2(CF::DAY_OF_MONTH(), 3);
        $f = $this->builder->toFormatter();
        $this->assertEquals($f->__toString(), "Value(DayOfMonth,3)");
    }

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
        $this->builder->appendValue2(CF::DAY_OF_MONTH(), 0);
    }

    /**
     * @expectedException \Celest\IllegalArgumentException
     */

    public function test_appendValue_2arg_widthTooBig()
    {
        $this->builder->appendValue2(CF::DAY_OF_MONTH(), 20);
    }

    //-----------------------------------------------------------------------

    public function test_appendValue_3arg()
    {
        $this->builder->appendValue3(CF::DAY_OF_MONTH(), 2, 3, SignStyle::NORMAL());
        $f = $this->builder->toFormatter();
        $this->assertEquals($f->__toString(), "Value(DayOfMonth,2,3,NORMAL)");
    }

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
        $this->builder->appendValue3(CF::DAY_OF_MONTH(), 0, 2, SignStyle::NORMAL());
    }

    /**
     * @expectedException \Celest\IllegalArgumentException
     */
    public function test_appendValue_3arg_minWidthTooBig()
    {
        $this->builder->appendValue3(CF::DAY_OF_MONTH(), 20, 2, SignStyle::NORMAL());
    }

    /**
     * @expectedException \Celest\IllegalArgumentException
     */
    public function test_appendValue_3arg_maxWidthTooSmall()
    {
        $this->builder->appendValue3(CF::DAY_OF_MONTH(), 2, 0, SignStyle::NORMAL());
    }

    /**
     * @expectedException \Celest\IllegalArgumentException
     */
    public function test_appendValue_3arg_maxWidthTooBig()
    {
        $this->builder->appendValue3(CF::DAY_OF_MONTH(), 2, 20, SignStyle::NORMAL());
    }

    /**
     * @expectedException \Celest\IllegalArgumentException
     */
    public function test_appendValue_3arg_maxWidthMinWidth()
    {
        $this->builder->appendValue3(CF::DAY_OF_MONTH(), 4, 2, SignStyle::NORMAL());
    }

    public function test_appendValue_3arg_nullSignStyle()
    {
        TestHelper::assertNullException($this, function () {
            $this->builder->appendValue3(CF::DAY_OF_MONTH(), 2, 3, null);
        });
    }

    //-----------------------------------------------------------------------

    public function test_appendValue_subsequent2_parse3()
    {
        $this->builder->appendValue3(CF::MONTH_OF_YEAR(), 1, 2, SignStyle::NORMAL())->appendValue2(CF::DAY_OF_MONTH(), 2);
        $f = $this->builder->toFormatter();
        $this->assertEquals($f->__toString(), "Value(MonthOfYear,1,2,NORMAL)Value(DayOfMonth,2)");
        $parsed = $f->parseUnresolved("123", new ParsePosition(0));
        $this->assertEquals($parsed->getLong(CF::MONTH_OF_YEAR()), 1);
        $this->assertEquals($parsed->getLong(CF::DAY_OF_MONTH()), 23);
    }


    public function test_appendValue_subsequent2_parse4()
    {
        $this->builder->appendValue3(CF::MONTH_OF_YEAR(), 1, 2, SignStyle::NORMAL())->appendValue2(CF::DAY_OF_MONTH(), 2);
        $f = $this->builder->toFormatter();
        $this->assertEquals($f->__toString(), "Value(MonthOfYear,1,2,NORMAL)Value(DayOfMonth,2)");
        $parsed = $f->parseUnresolved("0123", new ParsePosition(0));
        $this->assertEquals($parsed->getLong(CF::MONTH_OF_YEAR()), 1);
        $this->assertEquals($parsed->getLong(CF::DAY_OF_MONTH()), 23);
    }


    public function test_appendValue_subsequent2_parse5()
    {
        $this->builder->appendValue3(CF::MONTH_OF_YEAR(), 1, 2, SignStyle::NORMAL())->appendValue2(CF::DAY_OF_MONTH(), 2)->appendLiteral('4');
        $f = $this->builder->toFormatter();
        $this->assertEquals($f->__toString(), "Value(MonthOfYear,1,2,NORMAL)Value(DayOfMonth,2)'4'");
        $parsed = $f->parseUnresolved("01234", new ParsePosition(0));
        $this->assertEquals($parsed->getLong(CF::MONTH_OF_YEAR()), 1);
        $this->assertEquals($parsed->getLong(CF::DAY_OF_MONTH()), 23);
    }


    public function test_appendValue_subsequent3_parse6()
    {
        $this->builder
            ->appendValue3(CF::YEAR(), 4, 10, SignStyle::EXCEEDS_PAD())
            ->appendValue2(CF::MONTH_OF_YEAR(), 2)
            ->appendValue2(CF::DAY_OF_MONTH(), 2);
        $f = $this->builder->toFormatter();
        $this->assertEquals($f->__toString(), "Value(Year,4,10,EXCEEDS_PAD)Value(MonthOfYear,2)Value(DayOfMonth,2)");
        $parsed = $f->parseUnresolved("20090630", new ParsePosition(0));
        $this->assertEquals($parsed->getLong(CF::YEAR()), 2009);
        $this->assertEquals($parsed->getLong(CF::MONTH_OF_YEAR()), 6);
        $this->assertEquals($parsed->getLong(CF::DAY_OF_MONTH()), 30);
    }

    //-----------------------------------------------------------------------
    public function test_appendValueReduced_null()
    {
        TestHelper::assertNullException($this, function () {
            $this->builder->appendValueReduced(null, 2, 2, 2000);
        });
    }


    public function test_appendValueReduced()
    {
        $this->builder->appendValueReduced(CF::YEAR(), 2, 2, 2000);
        $f = $this->builder->toFormatter();
        $this->assertEquals($f->__toString(), "ReducedValue(Year,2,2,2000)");
        $parsed = $f->parseUnresolved("12", new ParsePosition(0));
        $this->assertEquals($parsed->getLong(CF::YEAR()), 2012);
    }


    public function test_appendValueReduced_subsequent_parse()
    {
        $this->builder->appendValue3(CF::MONTH_OF_YEAR(), 1, 2, SignStyle::NORMAL())->appendValueReduced(CF::YEAR(), 2, 2, 2000);
        $f = $this->builder->toFormatter();
        $this->assertEquals($f->__toString(), "Value(MonthOfYear,1,2,NORMAL)ReducedValue(Year,2,2,2000)");
        $ppos = new ParsePosition(0);
        $parsed = $f->parseUnresolved("123", $ppos);
        $this->assertNotNull($parsed, "Parse failed: " . $ppos->__toString());
        $this->assertEquals($parsed->getLong(CF::MONTH_OF_YEAR()), 1);
        $this->assertEquals($parsed->getLong(CF::YEAR()), 2023);
    }

    //-----------------------------------------------------------------------
    //-----------------------------------------------------------------------
    //-----------------------------------------------------------------------

    public function test_appendFraction_4arg()
    {
        $this->builder->appendFraction(CF::MINUTE_OF_HOUR(), 1, 9, false);
        $f = $this->builder->toFormatter();
        $this->assertEquals($f->__toString(), "Fraction(MinuteOfHour,1,9)");
    }

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
        $this->builder->appendFraction(CF::DAY_OF_MONTH(), 1, 9, false);
    }

    /**
     * @expectedException \Celest\IllegalArgumentException
     */
    public function test_appendFraction_4arg_minTooSmall()
    {
        $this->builder->appendFraction(CF::MINUTE_OF_HOUR(), -1, 9, false);
    }

    /**
     * @expectedException \Celest\IllegalArgumentException
     */
    public function test_appendFraction_4arg_minTooBig()
    {
        $this->builder->appendFraction(CF::MINUTE_OF_HOUR(), 10, 9, false);
    }

    /**
     * @expectedException \Celest\IllegalArgumentException
     */
    public function test_appendFraction_4arg_maxTooSmall()
    {
        $this->builder->appendFraction(CF::MINUTE_OF_HOUR(), 0, -1, false);
    }

    /**
     * @expectedException \Celest\IllegalArgumentException
     */
    public function test_appendFraction_4arg_maxTooBig()
    {
        $this->builder->appendFraction(CF::MINUTE_OF_HOUR(), 1, 10, false);
    }

    /**
     * @expectedException \Celest\IllegalArgumentException
     */
    public function test_appendFraction_4arg_maxWidthMinWidth()
    {
        $this->builder->appendFraction(CF::MINUTE_OF_HOUR(), 9, 3, false);
    }

    //-----------------------------------------------------------------------
    //-----------------------------------------------------------------------
    //-----------------------------------------------------------------------

    public function test_appendText_1arg()
    {
        $this->builder->appendText(CF::MONTH_OF_YEAR());
        $f = $this->builder->toFormatter();
        $this->assertEquals($f->__toString(), "Text(MonthOfYear)");
    }

    public function test_appendText_1arg_null()
    {
        TestHelper::assertNullException($this, function () {
            $this->builder->appendText(null);
        });
    }

    //-----------------------------------------------------------------------

    public function test_appendText_2arg()
    {
        $this->builder->appendText2(CF::MONTH_OF_YEAR(), TextStyle::SHORT());
        $f = $this->builder->toFormatter();
        $this->assertEquals($f->__toString(), "Text(MonthOfYear,SHORT)");
    }

    public function test_appendText_2arg_nullRule()
    {
        TestHelper::assertNullException($this, function () {
            $this->builder->appendText2(null, TextStyle::SHORT());
        });
    }

    public function test_appendText_2arg_nullStyle()
    {
        TestHelper::assertNullException($this, function () {
            $this->builder->appendText2(CF::MONTH_OF_YEAR(), null);
        });
    }

    //-----------------------------------------------------------------------

    public function test_appendTextMap()
    {
        $map = [
            1 => "JNY",
            2 => "FBY",
            3 => "MCH",
            4 => "APL",
            5 => "MAY",
            6 => "JUN",
            7 => "JLY",
            8 => "AGT",
            9 => "SPT",
            10 => "OBR",
            11 => "NVR",
            12 => "DBR",
        ];
        $this->builder->appendText3(CF::MONTH_OF_YEAR(), $map);
        $f = $this->builder->toFormatter();
        $this->assertEquals($f->__toString(), "Text(MonthOfYear)");  // TODO: toshould be different?
    }

    public function test_appendTextMap_nullRule()
    {
        TestHelper::assertNullException($this, function () {
            $this->builder->appendText3(null, []);
        });
    }

    public function test_appendTextMap_nullStyle()
    {
        TestHelper::assertNullException($this, function () {
            $this->builder->appendText3(CF::MONTH_OF_YEAR(), null);
        });
    }

    //-----------------------------------------------------------------------
    //-----------------------------------------------------------------------
    //-----------------------------------------------------------------------

    public function test_appendOffsetId()
    {
        $this->builder->appendOffsetId();
        $f = $this->builder->toFormatter();
        $this->assertEquals($f->__toString(), "Offset(+HH:MM:ss,'Z')");
    }

    function data_offsetPatterns()
    {
        return [
            ["+HH", 2, 0, 0, "+02"],
            ["+HH", -2, 0, 0, "-02"],
            ["+HH", 2, 30, 0, "+02"],
            ["+HH", 2, 0, 45, "+02"],
            ["+HH", 2, 30, 45, "+02"],

            ["+HHMM", 2, 0, 0, "+0200"],
            ["+HHMM", -2, 0, 0, "-0200"],
            ["+HHMM", 2, 30, 0, "+0230"],
            ["+HHMM", 2, 0, 45, "+0200"],
            ["+HHMM", 2, 30, 45, "+0230"],

            ["+HH:MM", 2, 0, 0, "+02:00"],
            ["+HH:MM", -2, 0, 0, "-02:00"],
            ["+HH:MM", 2, 30, 0, "+02:30"],
            ["+HH:MM", 2, 0, 45, "+02:00"],
            ["+HH:MM", 2, 30, 45, "+02:30"],

            ["+HHMMss", 2, 0, 0, "+0200"],
            ["+HHMMss", -2, 0, 0, "-0200"],
            ["+HHMMss", 2, 30, 0, "+0230"],
            ["+HHMMss", 2, 0, 45, "+020045"],
            ["+HHMMss", 2, 30, 45, "+023045"],

            ["+HH:MM:ss", 2, 0, 0, "+02:00"],
            ["+HH:MM:ss", -2, 0, 0, "-02:00"],
            ["+HH:MM:ss", 2, 30, 0, "+02:30"],
            ["+HH:MM:ss", 2, 0, 45, "+02:00:45"],
            ["+HH:MM:ss", 2, 30, 45, "+02:30:45"],

            ["+HHMMSS", 2, 0, 0, "+020000"],
            ["+HHMMSS", -2, 0, 0, "-020000"],
            ["+HHMMSS", 2, 30, 0, "+023000"],
            ["+HHMMSS", 2, 0, 45, "+020045"],
            ["+HHMMSS", 2, 30, 45, "+023045"],

            ["+HH:MM:SS", 2, 0, 0, "+02:00:00"],
            ["+HH:MM:SS", -2, 0, 0, "-02:00:00"],
            ["+HH:MM:SS", 2, 30, 0, "+02:30:00"],
            ["+HH:MM:SS", 2, 0, 45, "+02:00:45"],
            ["+HH:MM:SS", 2, 30, 45, "+02:30:45"],
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
        $offset = ZoneOffset::ofHoursMinutesSeconds($h, $m, $s);
        $parsed = $f->parseQuery($expected, TemporalQueries::fromCallable([ZoneOffset::class, 'from']));
        $this->assertEquals($f->format($parsed), $expected);
    }

    function data_badOffsetPatterns()
    {
        return [
            ["HH"],
            ["HHMM"],
            ["HH:MM"],
            ["HHMMss"],
            ["HH:MM:ss"],
            ["HHMMSS"],
            ["HH:MM:SS"],
            ["+H"],
            ["+HMM"],
            ["+HHM"],
            ["+A"],
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

    public function test_appendZoneId()
    {
        $this->builder->appendZoneId();
        $f = $this->builder->toFormatter();
        $this->assertEquals($f->__toString(), "ZoneId()");
    }


    public function test_appendZoneText_1arg()
    {
        $this->builder->appendZoneText(TextStyle::FULL());
        $f = $this->builder->toFormatter();
        $this->assertEquals($f->__toString(), "ZoneText(FULL)");
    }

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
        $this->builder->appendValue(CF::MONTH_OF_YEAR())->appendLiteral(':')->padNext(2)->appendValue(CF::DAY_OF_MONTH());
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
        $this->builder->appendValue(CF::MONTH_OF_YEAR())->appendLiteral(':')->padNext2(2, '-')->appendValue(CF::DAY_OF_MONTH());
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
        $this->builder->appendValue(CF::MONTH_OF_YEAR())->appendLiteral(':')
            ->padNext(5)->optionalStart()->appendValue(CF::DAY_OF_MONTH())->optionalEnd()
            ->appendLiteral(':')->appendValue(CF::YEAR());
        $this->assertEquals($this->builder->toFormatter()->format(LocalDate::of(2013, 2, 1)), "2:    1:2013");
        $this->assertEquals($this->builder->toFormatter()->format(YearMonth::of(2013, 2)), "2:     :2013");
    }

    //-----------------------------------------------------------------------
    //-----------------------------------------------------------------------
    //-----------------------------------------------------------------------

    public function test_optionalStart_noEnd()
    {
        $this->builder->appendValue(CF::MONTH_OF_YEAR())->optionalStart()->appendValue(CF::DAY_OF_MONTH())->appendValue(CF::DAY_OF_WEEK());
        $f = $this->builder->toFormatter();
        $this->assertEquals($f->__toString(), "Value(MonthOfYear)[Value(DayOfMonth)Value(DayOfWeek)]");
    }


    public function test_optionalStart2_noEnd()
    {
        $this->builder->appendValue(CF::MONTH_OF_YEAR())->optionalStart()->appendValue(CF::DAY_OF_MONTH())->optionalStart()->appendValue(CF::DAY_OF_WEEK());
        $f = $this->builder->toFormatter();
        $this->assertEquals($f->__toString(), "Value(MonthOfYear)[Value(DayOfMonth)[Value(DayOfWeek)]]");
    }


    public function test_optionalStart_doubleStart()
    {
        $this->builder->appendValue(CF::MONTH_OF_YEAR())->optionalStart()->optionalStart()->appendValue(CF::DAY_OF_MONTH());
        $f = $this->builder->toFormatter();
        $this->assertEquals($f->__toString(), "Value(MonthOfYear)[[Value(DayOfMonth)]]");
    }

    //-----------------------------------------------------------------------

    public function test_optionalEnd()
    {
        $this->builder->appendValue(CF::MONTH_OF_YEAR())->optionalStart()->appendValue(CF::DAY_OF_MONTH())->optionalEnd()->appendValue(CF::DAY_OF_WEEK());
        $f = $this->builder->toFormatter();
        $this->assertEquals($f->__toString(), "Value(MonthOfYear)[Value(DayOfMonth)]Value(DayOfWeek)");
    }


    public function test_optionalEnd2()
    {
        $this->builder->appendValue(CF::MONTH_OF_YEAR())->optionalStart()->appendValue(CF::DAY_OF_MONTH())
            ->optionalStart()->appendValue(CF::DAY_OF_WEEK())->optionalEnd()->appendValue(CF::DAY_OF_MONTH())->optionalEnd();
        $f = $this->builder->toFormatter();
        $this->assertEquals($f->__toString(), "Value(MonthOfYear)[Value(DayOfMonth)[Value(DayOfWeek)]Value(DayOfMonth)]");
    }


    public function test_optionalEnd_doubleStartSingleEnd()
    {
        $this->builder->appendValue(CF::MONTH_OF_YEAR())->optionalStart()->optionalStart()->appendValue(CF::DAY_OF_MONTH())->optionalEnd();
        $f = $this->builder->toFormatter();
        $this->assertEquals($f->__toString(), "Value(MonthOfYear)[[Value(DayOfMonth)]]");
    }


    public function test_optionalEnd_doubleStartDoubleEnd()
    {
        $this->builder->appendValue(CF::MONTH_OF_YEAR())->optionalStart()->optionalStart()->appendValue(CF::DAY_OF_MONTH())->optionalEnd()->optionalEnd();
        $f = $this->builder->toFormatter();
        $this->assertEquals($f->__toString(), "Value(MonthOfYear)[[Value(DayOfMonth)]]");
    }


    public function test_optionalStartEnd_immediateStartEnd()
    {
        $this->builder->appendValue(CF::MONTH_OF_YEAR())->optionalStart()->optionalEnd()->appendValue(CF::DAY_OF_MONTH());
        $f = $this->builder->toFormatter();
        $this->assertEquals($f->__toString(), "Value(MonthOfYear)Value(DayOfMonth)");
    }

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
            ["'a'", "'a'"],
            ["''", "''"],
            ["'!'", "'!'"],
            ["!", "'!'"],

            ["'hello_people,][)('", "'hello_people,][)('"],
            ["'hi'", "'hi'"],
            ["'yyyy'", "'yyyy'"],
            ["''''", "''"],
            ["'o''clock'", "'o''clock'"],

            ["G", "Text(Era,SHORT)"],
            ["GG", "Text(Era,SHORT)"],
            ["GGG", "Text(Era,SHORT)"],
            ["GGGG", "Text(Era)"],
            ["GGGGG", "Text(Era,NARROW)"],

            ["u", "Value(Year)"],
            ["uu", "ReducedValue(Year,2,2,2000-01-01)"],
            ["uuu", "Value(Year,3,19,NORMAL)"],
            ["uuuu", "Value(Year,4,19,EXCEEDS_PAD)"],
            ["uuuuu", "Value(Year,5,19,EXCEEDS_PAD)"],

            ["y", "Value(YearOfEra)"],
            ["yy", "ReducedValue(YearOfEra,2,2,2000-01-01)"],
            ["yyy", "Value(YearOfEra,3,19,NORMAL)"],
            ["yyyy", "Value(YearOfEra,4,19,EXCEEDS_PAD)"],
            ["yyyyy", "Value(YearOfEra,5,19,EXCEEDS_PAD)"],

            ["Y", "Localized(WeekBasedYear)"],
            ["YY", "Localized(ReducedValue(WeekBasedYear,2,2,2000-01-01))"],
            ["YYY", "Localized(WeekBasedYear,3,19,NORMAL)"],
            ["YYYY", "Localized(WeekBasedYear,4,19,EXCEEDS_PAD)"],
            ["YYYYY", "Localized(WeekBasedYear,5,19,EXCEEDS_PAD)"],

            ["M", "Value(MonthOfYear)"],
            ["MM", "Value(MonthOfYear,2)"],
            ["MMM", "Text(MonthOfYear,SHORT)"],
            ["MMMM", "Text(MonthOfYear)"],
            ["MMMMM", "Text(MonthOfYear,NARROW)"],

            ["L", "Value(MonthOfYear)"],
            ["LL", "Value(MonthOfYear,2)"],
            ["LLL", "Text(MonthOfYear,SHORT_STANDALONE)"],
            ["LLLL", "Text(MonthOfYear,FULL_STANDALONE)"],
            ["LLLLL", "Text(MonthOfYear,NARROW_STANDALONE)"],

            ["D", "Value(DayOfYear)"],
            ["DD", "Value(DayOfYear,2)"],
            ["DDD", "Value(DayOfYear,3)"],

            ["d", "Value(DayOfMonth)"],
            ["dd", "Value(DayOfMonth,2)"],

            ["F", "Value(AlignedDayOfWeekInMonth)"],

            ["Q", "Value(QuarterOfYear)"],
            ["QQ", "Value(QuarterOfYear,2)"],
            ["QQQ", "Text(QuarterOfYear,SHORT)"],
            ["QQQQ", "Text(QuarterOfYear)"],
            ["QQQQQ", "Text(QuarterOfYear,NARROW)"],

            ["q", "Value(QuarterOfYear)"],
            ["qq", "Value(QuarterOfYear,2)"],
            ["qqq", "Text(QuarterOfYear,SHORT_STANDALONE)"],
            ["qqqq", "Text(QuarterOfYear,FULL_STANDALONE)"],
            ["qqqqq", "Text(QuarterOfYear,NARROW_STANDALONE)"],

            ["E", "Text(DayOfWeek,SHORT)"],
            ["EE", "Text(DayOfWeek,SHORT)"],
            ["EEE", "Text(DayOfWeek,SHORT)"],
            ["EEEE", "Text(DayOfWeek)"],
            ["EEEEE", "Text(DayOfWeek,NARROW)"],

            ["e", "Localized(DayOfWeek,1)"],
            ["ee", "Localized(DayOfWeek,2)"],
            ["eee", "Text(DayOfWeek,SHORT)"],
            ["eeee", "Text(DayOfWeek)"],
            ["eeeee", "Text(DayOfWeek,NARROW)"],

            ["c", "Localized(DayOfWeek,1)"],
            ["ccc", "Text(DayOfWeek,SHORT_STANDALONE)"],
            ["cccc", "Text(DayOfWeek,FULL_STANDALONE)"],
            ["ccccc", "Text(DayOfWeek,NARROW_STANDALONE)"],

            ["a", "Text(AmPmOfDay,SHORT)"],

            ["H", "Value(HourOfDay)"],
            ["HH", "Value(HourOfDay,2)"],

            ["K", "Value(HourOfAmPm)"],
            ["KK", "Value(HourOfAmPm,2)"],

            ["k", "Value(ClockHourOfDay)"],
            ["kk", "Value(ClockHourOfDay,2)"],

            ["h", "Value(ClockHourOfAmPm)"],
            ["hh", "Value(ClockHourOfAmPm,2)"],

            ["m", "Value(MinuteOfHour)"],
            ["mm", "Value(MinuteOfHour,2)"],

            ["s", "Value(SecondOfMinute)"],
            ["ss", "Value(SecondOfMinute,2)"],

            ["S", "Fraction(NanoOfSecond,1,1)"],
            ["SS", "Fraction(NanoOfSecond,2,2)"],
            ["SSS", "Fraction(NanoOfSecond,3,3)"],
            ["SSSSSSSSS", "Fraction(NanoOfSecond,9,9)"],

            ["A", "Value(MilliOfDay)"],
            ["AA", "Value(MilliOfDay,2)"],
            ["AAA", "Value(MilliOfDay,3)"],

            ["n", "Value(NanoOfSecond)"],
            ["nn", "Value(NanoOfSecond,2)"],
            ["nnn", "Value(NanoOfSecond,3)"],

            ["N", "Value(NanoOfDay)"],
            ["NN", "Value(NanoOfDay,2)"],
            ["NNN", "Value(NanoOfDay,3)"],

            ["z", "ZoneText(SHORT)"],
            ["zz", "ZoneText(SHORT)"],
            ["zzz", "ZoneText(SHORT)"],
            ["zzzz", "ZoneText(FULL)"],

            ["VV", "ZoneId()"],

            ["Z", "Offset(+HHMM,'+0000')"],  // SimpleDateFormat
            ["ZZ", "Offset(+HHMM,'+0000')"],  // SimpleDateFormat
            ["ZZZ", "Offset(+HHMM,'+0000')"],  // SimpleDateFormat

            ["X", "Offset(+HHmm,'Z')"],  // LDML/almost SimpleDateFormat
            ["XX", "Offset(+HHMM,'Z')"],  // LDML/SimpleDateFormat
            ["XXX", "Offset(+HH:MM,'Z')"],  // LDML/SimpleDateFormat
            ["XXXX", "Offset(+HHMMss,'Z')"],  // LDML
            ["XXXXX", "Offset(+HH:MM:ss,'Z')"],  // LDML

            ["x", "Offset(+HHmm,'+00')"],  // LDML
            ["xx", "Offset(+HHMM,'+0000')"],  // LDML
            ["xxx", "Offset(+HH:MM,'+00:00')"],  // LDML
            ["xxxx", "Offset(+HHMMss,'+0000')"],  // LDML
            ["xxxxx", "Offset(+HH:MM:ss,'+00:00')"],  // LDML

            ["ppH", "Pad(Value(HourOfDay),2)"],
            ["pppDD", "Pad(Value(DayOfYear,2),3)"],

            ["yyyy[-MM[-dd", "Value(YearOfEra,4,19,EXCEEDS_PAD)['-'Value(MonthOfYear,2)['-'Value(DayOfMonth,2)]]"],
            ["yyyy[-MM[-dd]]", "Value(YearOfEra,4,19,EXCEEDS_PAD)['-'Value(MonthOfYear,2)['-'Value(DayOfMonth,2)]]"],
            ["yyyy[-MM[]-dd]", "Value(YearOfEra,4,19,EXCEEDS_PAD)['-'Value(MonthOfYear,2)'-'Value(DayOfMonth,2)]"],

            ["yyyy-MM-dd'T'HH:mm:ss.SSS", "Value(YearOfEra,4,19,EXCEEDS_PAD)'-'Value(MonthOfYear,2)'-'Value(DayOfMonth,2)" .
                "'T'Value(HourOfDay,2)':'Value(MinuteOfHour,2)':'Value(SecondOfMinute,2)'.'Fraction(NanoOfSecond,3,3)"],

            ["w", "Localized(WeekOfWeekBasedYear,1)"],
            ["ww", "Localized(WeekOfWeekBasedYear,2)"],
            ["W", "Localized(WeekOfMonth,1)"],
        ];
    }

    /**
     * @dataProvider dataValid
     */

    public function test_appendPattern_valid($input, $expected)
    {
        $this->builder->appendPattern($input);
        $f = $this->builder->toFormatter();
        $this->assertEquals($f->__toString(), $expected);
    }

    //-----------------------------------------------------------------------
    function dataInvalid()
    {
        return [
            ["'"],
            ["'hello"],
            ["'hel''lo"],
            ["'hello''"],
            ["{"],
            ["}"],
            ["{}"],
            ["}"],
            ["yyyy]"],
            ["yyyy]MM"],
            ["yyyy[MM]]"],

            ["aa"],
            ["aaa"],
            ["aaaa"],
            ["aaaaa"],
            ["aaaaaa"],
            ["MMMMMM"],
            ["LLLLLL"],
            ["QQQQQQ"],
            ["qqqqqq"],
            ["EEEEEE"],
            ["eeeeee"],
            ["cc"],
            ["cccccc"],
            ["ddd"],
            ["DDDD"],
            ["FF"],
            ["FFF"],
            ["hhh"],
            ["HHH"],
            ["kkk"],
            ["KKK"],
            ["mmm"],
            ["sss"],
            ["OO"],
            ["OOO"],
            ["OOOOO"],
            ["XXXXXX"],
            ["ZZZZZZ"],
            ["zzzzz"],
            ["V"],
            ["VVV"],
            ["VVVV"],
            ["VVVVV"],

            ["RO"],

            ["p"],
            ["pp"],
            ["p:"],

            ["f"],
            ["ff"],
            ["f:"],
            ["fy"],
            ["fa"],
            ["fM"],

            ["www"],
            ["WW"],
        ];
    }

    /**
     * @dataProvider dataInvalid
     * @expectedException \Celest\IllegalArgumentException
     */

    public function test_appendPattern_invalid($input)
    {
        try {
            $this->builder->appendPattern($input);
        } catch (IllegalArgumentException $ex) {
            throw $ex;
        }
    }

    //-----------------------------------------------------------------------
    function data_patternPrint()
    {
        return [
            ["Q", LocalDate::of(2012, 2, 10), "1"],
            ["QQ", LocalDate::of(2012, 2, 10), "01"],
            ["QQQ", LocalDate::of(2012, 2, 10), "Q1"],
            ["QQQQ", LocalDate::of(2012, 2, 10), "1st quarter"],
            ["QQQQQ", LocalDate::of(2012, 2, 10), "1"],
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
    // Warning highly dependet on CLDR data
    function localizedDateTimePatterns()
    {
        return [
            [FormatStyle::FULL(), FormatStyle::FULL(), IsoChronology::INSTANCE(), Locale::US(), "EEEE, MMMM d, y 'at' h:mm:ss a zzzz"],
            [FormatStyle::LONG(), FormatStyle::LONG(), IsoChronology::INSTANCE(), Locale::US(), "MMMM d, y 'at' h:mm:ss a z"],
            [FormatStyle::MEDIUM(), FormatStyle::MEDIUM(), IsoChronology::INSTANCE(), Locale::US(), "MMM d, y, h:mm:ss a"],
            [FormatStyle::SHORT(), FormatStyle::SHORT(), IsoChronology::INSTANCE(), Locale::US(), "M/d/yy, h:mm a"],
            [FormatStyle::FULL(), null, IsoChronology::INSTANCE(), Locale::US(), "EEEE, MMMM d, y"],
            [FormatStyle::LONG(), null, IsoChronology::INSTANCE(), Locale::US(), "MMMM d, y"],
            [FormatStyle::MEDIUM(), null, IsoChronology::INSTANCE(), Locale::US(), "MMM d, y"],
            [FormatStyle::SHORT(), null, IsoChronology::INSTANCE(), Locale::US(), "M/d/yy"],
            [null, FormatStyle::FULL(), IsoChronology::INSTANCE(), Locale::US(), "h:mm:ss a zzzz"],
            [null, FormatStyle::LONG(), IsoChronology::INSTANCE(), Locale::US(), "h:mm:ss a z"],
            [null, FormatStyle::MEDIUM(), IsoChronology::INSTANCE(), Locale::US(), "h:mm:ss a"],
            [null, FormatStyle::SHORT(), IsoChronology::INSTANCE(), Locale::US(), "h:mm a"],

            // French Locale and ISO Chronology
            [FormatStyle::FULL(), FormatStyle::FULL(), IsoChronology::INSTANCE(), Locale::FRENCH(), "EEEE d MMMM y 'à' HH:mm:ss zzzz"],
            [FormatStyle::LONG(), FormatStyle::LONG(), IsoChronology::INSTANCE(), Locale::FRENCH(), "d MMMM y 'à' HH:mm:ss z"],
            [FormatStyle::MEDIUM(), FormatStyle::MEDIUM(), IsoChronology::INSTANCE(), Locale::FRENCH(), "d MMM y 'à' HH:mm:ss"],
            [FormatStyle::SHORT(), FormatStyle::SHORT(), IsoChronology::INSTANCE(), Locale::FRENCH(), "dd/MM/y HH:mm"],
            [FormatStyle::FULL(), null, IsoChronology::INSTANCE(), Locale::FRENCH(), "EEEE d MMMM y"],
            [FormatStyle::LONG(), null, IsoChronology::INSTANCE(), Locale::FRENCH(), "d MMMM y"],
            [FormatStyle::MEDIUM(), null, IsoChronology::INSTANCE(), Locale::FRENCH(), "d MMM y"],
            [FormatStyle::SHORT(), null, IsoChronology::INSTANCE(), Locale::FRENCH(), "dd/MM/y"],
            [null, FormatStyle::FULL(), IsoChronology::INSTANCE(), Locale::FRENCH(), "HH:mm:ss zzzz"],
            [null, FormatStyle::LONG(), IsoChronology::INSTANCE(), Locale::FRENCH(), "HH:mm:ss z"],
            [null, FormatStyle::MEDIUM(), IsoChronology::INSTANCE(), Locale::FRENCH(), "HH:mm:ss"],
            [null, FormatStyle::SHORT(), IsoChronology::INSTANCE(), Locale::FRENCH(), "HH:mm"],

            /*// Japanese Locale and JapaneseChronology TODO
            [FormatStyle::FULL(), FormatStyle::FULL(), JapaneseChronology::INSTANCE(), Locale::JAPANESE(), "Gy'\u5e74'M'\u6708'd'\u65e5' H'\u6642'mm'\u5206'ss'\u79d2' z"],
            [FormatStyle::LONG(), FormatStyle::LONG(), JapaneseChronology::INSTANCE(), Locale::JAPANESE(), "GGGGGy.MM.dd H:mm:ss z"],
            [FormatStyle::MEDIUM(), FormatStyle::MEDIUM(), JapaneseChronology::INSTANCE(), Locale::JAPANESE(), "GGGGGy.MM.dd H:mm:ss"],
            [FormatStyle::SHORT(), FormatStyle::SHORT(), JapaneseChronology::INSTANCE(), Locale::JAPANESE(), "GGGGGy.MM.dd H:mm"],
            [FormatStyle::FULL(), null, JapaneseChronology::INSTANCE(), Locale::JAPANESE(), "Gy'\u5e74'M'\u6708'd'\u65e5'"],
            [FormatStyle::LONG(), null, JapaneseChronology::INSTANCE(), Locale::JAPANESE(), "GGGGGy.MM.dd"],
            [FormatStyle::MEDIUM(), null, JapaneseChronology::INSTANCE(), Locale::JAPANESE(), "GGGGGy.MM.dd"],
            [FormatStyle::SHORT(), null, JapaneseChronology::INSTANCE(), Locale::JAPANESE(), "GGGGGy.MM.dd"],
            [null, FormatStyle::FULL(), JapaneseChronology::INSTANCE(), Locale::JAPANESE(), "H'\u6642'mm'\u5206'ss'\u79d2' z"],
            [null, FormatStyle::LONG(), JapaneseChronology::INSTANCE(), Locale::JAPANESE(), "H:mm:ss z"],
            [null, FormatStyle::MEDIUM(), JapaneseChronology::INSTANCE(), Locale::JAPANESE(), "H:mm:ss"],
            [null, FormatStyle::SHORT(), JapaneseChronology::INSTANCE(), Locale::JAPANESE(), "H:mm"],*/

            // Chinese Local and Chronology
            [FormatStyle::FULL(), FormatStyle::FULL(), MinguoChronology::INSTANCE(), Locale::CHINESE(), "Gy\u5e74M\u6708d\u65e5EEEE ahh'\u65f6'mm'\u5206'ss'\u79d2' z"],
            [FormatStyle::LONG(), FormatStyle::LONG(), MinguoChronology::INSTANCE(), Locale::CHINESE(), "Gy\u5e74M\u6708d\u65e5 ahh'\u65f6'mm'\u5206'ss'\u79d2'"],
            [FormatStyle::MEDIUM(), FormatStyle::MEDIUM(), MinguoChronology::INSTANCE(), Locale::CHINESE(), "Gy-M-d H:mm:ss"],
            [FormatStyle::SHORT(), FormatStyle::SHORT(), MinguoChronology::INSTANCE(), Locale::CHINESE(), "Gy-M-d ah:mm"],
            [FormatStyle::FULL(), null, MinguoChronology::INSTANCE(), Locale::CHINESE(), "Gy\u5e74M\u6708d\u65e5EEEE"],
            [FormatStyle::LONG(), null, MinguoChronology::INSTANCE(), Locale::CHINESE(), "Gy\u5e74M\u6708d\u65e5"],
            [FormatStyle::MEDIUM(), null, MinguoChronology::INSTANCE(), Locale::CHINESE(), "Gy-M-d"],
            [FormatStyle::SHORT(), null, MinguoChronology::INSTANCE(), Locale::CHINESE(), "Gy-M-d"],
            [null, FormatStyle::FULL(), MinguoChronology::INSTANCE(), Locale::CHINESE(), "ahh'\u65f6'mm'\u5206'ss'\u79d2' z"],
            [null, FormatStyle::LONG(), MinguoChronology::INSTANCE(), Locale::CHINESE(), "ahh'\u65f6'mm'\u5206'ss'\u79d2'"],
            [null, FormatStyle::MEDIUM(), MinguoChronology::INSTANCE(), Locale::CHINESE(), "H:mm:ss"],
            [null, FormatStyle::SHORT(), MinguoChronology::INSTANCE(), Locale::CHINESE(), "ah:mm"],
        ];
    }

    /**
     * @dataProvider localizedDateTimePatterns
     */
    public function test_getLocalizedDateTimePattern($dateStyle, $timeStyle,
                                                     Chronology $chrono, Locale $locale, $expected)
    {
        $this->markTestSkipped('Too unstable, check ICU data first');
        $actual = DateTimeFormatterBuilder::getLocalizedDateTimePattern($dateStyle, $timeStyle, $chrono, $locale);
        $this->assertEquals($expected, $actual);
    }

    /**
     * @expectedException \Celest\IllegalArgumentException
     */
    public function test_getLocalizedDateTimePatternIAE()
    {
        DateTimeFormatterBuilder::getLocalizedDateTimePattern(null, null, IsoChronology::INSTANCE(), Locale::US());
    }

    public function test_getLocalizedChronoNPE()
    {
        TestHelper::assertNullException($this, function () {
            DateTimeFormatterBuilder::getLocalizedDateTimePattern(FormatStyle::SHORT(), FormatStyle::SHORT(), null, Locale::US());
        });
    }

    public function test_getLocalizedLocaleNPE()
    {
        TestHelper::assertNullException($this, function () {
            DateTimeFormatterBuilder::getLocalizedDateTimePattern(FormatStyle::SHORT(), FormatStyle::SHORT(), IsoChronology::INSTANCE(), null);
        });
    }

}
