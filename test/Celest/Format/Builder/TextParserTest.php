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

use Celest\DayOfWeek;
use Celest\Format\ParsePosition;
use Celest\Format\TextStyle;
use Celest\Locale;
use Celest\Temporal\ChronoField;
use Celest\Temporal\IsoFields;
use Celest\Temporal\TemporalField;
use Celest\TestHelper;

/**
 * Test TextPrinterParser.
 */
class TestTextParser extends AbstractTestPrinterParser
{
    private static function RUSSIAN()
    {
        return Locale::of("ru");
    }

    private static function FINNISH()
    {
        return Locale::of("fi");
    }

//-----------------------------------------------------------------------
    function data_error()
    {
        return [
            [ChronoField::DAY_OF_WEEK(), TextStyle::FULL(), "Monday", -1, \OutOfRangeException::class],
            [ChronoField::DAY_OF_WEEK(), TextStyle::FULL(), "Monday", 7, \OutOfRangeException::class],
        ];
    }

    /**
     * @dataProvider data_error
     */
    public
    function test_parse_error(TemporalField $field, TextStyle $style, $text, $pos, $expected)
    {
        try {
            $this->getFormatterFieldStyle($field, $style)->parseUnresolved($text, new ParsePosition($pos));
        } catch
        (\Exception $ex) {
            $this->assertInstanceOf($expected, $ex);
        }
    }

//-----------------------------------------------------------------------
    public
    function test_parse_midStr()
    {
        $pos = new ParsePosition(3);
        $this->assertEquals($this->getFormatterFieldStyle(ChronoField::DAY_OF_WEEK(), TextStyle::FULL())
            ->parseUnresolved("XxxMondayXxx", $pos)
            ->getLong(ChronoField::DAY_OF_WEEK()), 1);
        $this->assertEquals($pos->getIndex(), 9);
    }

    public
    function test_parse_remainderIgnored()
    {
        $pos = new ParsePosition(0);
        $this->assertEquals($this->getFormatterFieldStyle(ChronoField::DAY_OF_WEEK(), TextStyle::SHORT())
            ->parseUnresolved("Wednesday", $pos)
            ->getLong(ChronoField::DAY_OF_WEEK()), 3);
        $this->assertEquals($pos->getIndex(), 3);
    }

//-----------------------------------------------------------------------
    public
    function test_parse_noMatch1()
    {
        $pos = new ParsePosition(0);
        $parsed =
            $this->getFormatterFieldStyle(ChronoField::DAY_OF_WEEK(), TextStyle::FULL())->parseUnresolved("Munday", $pos);
        $this->assertEquals($pos->getErrorIndex(), 0);
        $this->assertEquals($parsed, null);
    }

    public
    function test_parse_noMatch2()
    {
        $pos = new ParsePosition(3);
        $parsed =
            $this->getFormatterFieldStyle(ChronoField::DAY_OF_WEEK(), TextStyle::FULL())->parseUnresolved("Monday", $pos);
        $this->assertEquals($pos->getErrorIndex(), 3);
        $this->assertEquals($parsed, null);
    }

    public
    function test_parse_noMatch_atEnd()
    {
        $pos = new ParsePosition(6);
        $parsed =
            $this->getFormatterFieldStyle(ChronoField::DAY_OF_WEEK(), TextStyle::FULL())->parseUnresolved("Monday", $pos);
        $this->assertEquals($pos->getErrorIndex(), 6);
        $this->assertEquals($parsed, null);
    }

//-----------------------------------------------------------------------
    function provider_text()
    {
        return
            [
                [
                    ChronoField::DAY_OF_WEEK(), TextStyle::FULL(), 1, "Monday"],
                [
                    ChronoField::DAY_OF_WEEK(), TextStyle::FULL(), 2, "Tuesday"],
                [
                    ChronoField::DAY_OF_WEEK(), TextStyle::FULL(), 3, "Wednesday"],
                [
                    ChronoField::DAY_OF_WEEK(), TextStyle::FULL(), 4, "Thursday"],
                [
                    ChronoField::DAY_OF_WEEK(), TextStyle::FULL(), 5, "Friday"],
                [
                    ChronoField::DAY_OF_WEEK(), TextStyle::FULL(), 6, "Saturday"],
                [
                    ChronoField::DAY_OF_WEEK(), TextStyle::FULL(), 7, "Sunday"],

                [
                    ChronoField::DAY_OF_WEEK(), TextStyle::SHORT(), 1, "Mon"],
                [
                    ChronoField::DAY_OF_WEEK(), TextStyle::SHORT(), 2, "Tue"],
                [
                    ChronoField::DAY_OF_WEEK(), TextStyle::SHORT(), 3, "Wed"],
                [
                    ChronoField::DAY_OF_WEEK(), TextStyle::SHORT(), 4, "Thu"],
                [
                    ChronoField::DAY_OF_WEEK(), TextStyle::SHORT(), 5, "Fri"],
                [
                    ChronoField::DAY_OF_WEEK(), TextStyle::SHORT(), 6, "Sat"],
                [
                    ChronoField::DAY_OF_WEEK(), TextStyle::SHORT(), 7, "Sun"],

                [
                    ChronoField::MONTH_OF_YEAR(), TextStyle::FULL(), 1, "January"],
                [
                    ChronoField::MONTH_OF_YEAR(), TextStyle::FULL(), 12, "December"],

                [
                    ChronoField::MONTH_OF_YEAR(), TextStyle::SHORT(), 1, "Jan"],
                [
                    ChronoField::MONTH_OF_YEAR(), TextStyle::SHORT(), 12, "Dec"],

                [
                    IsoFields::QUARTER_OF_YEAR(), TextStyle::FULL(), 1, "1st quarter"],
                [
                    IsoFields::QUARTER_OF_YEAR(), TextStyle::FULL(), 2, "2nd quarter"],
                [
                    IsoFields::QUARTER_OF_YEAR(), TextStyle::FULL(), 3, "3rd quarter"],
                [
                    IsoFields::QUARTER_OF_YEAR(), TextStyle::FULL(), 4, "4th quarter"],

                [
                    IsoFields::QUARTER_OF_YEAR(), TextStyle::SHORT(), 1, "Q1"],
                [
                    IsoFields::QUARTER_OF_YEAR(), TextStyle::SHORT(), 2, "Q2"],
                [
                    IsoFields::QUARTER_OF_YEAR(), TextStyle::SHORT(), 3, "Q3"],
                [
                    IsoFields::QUARTER_OF_YEAR(), TextStyle::SHORT(), 4, "Q4"],

                [
                    IsoFields::QUARTER_OF_YEAR(), TextStyle::NARROW(), 1, "1"],
                [
                    IsoFields::QUARTER_OF_YEAR(), TextStyle::NARROW(), 2, "2"],
                [
                    IsoFields::QUARTER_OF_YEAR(), TextStyle::NARROW(), 3, "3"],
                [
                    IsoFields::QUARTER_OF_YEAR(), TextStyle::NARROW(), 4, "4"],
            ];
    }

    function provider_number()
    {
        return
            [
                [
                    ChronoField::DAY_OF_MONTH(), TextStyle::FULL(), 1, "1"
                ],
                [
                    ChronoField::DAY_OF_MONTH(), TextStyle::FULL(), 2, "2"],
                [
                    ChronoField::DAY_OF_MONTH(), TextStyle::FULL(), 30, "30"],
                [
                    ChronoField::DAY_OF_MONTH(), TextStyle::FULL(), 31, "31"],

                [
                    ChronoField::DAY_OF_MONTH(), TextStyle::SHORT(), 1, "1"],
                [
                    ChronoField::DAY_OF_MONTH(), TextStyle::SHORT(), 2, "2"],
                [
                    ChronoField::DAY_OF_MONTH(), TextStyle::SHORT(), 30, "30"],
                [
                    ChronoField::DAY_OF_MONTH(), TextStyle::SHORT(), 31, "31"],
            ];
    }

// Test data is dependent on localized resources.
    function providerStandaloneText()
    {
// Locale, TemporalField, TextStyle, expected value, input text
        return [
            [
                self::RUSSIAN(), ChronoField::MONTH_OF_YEAR(), TextStyle::FULL_STANDALONE(), 1, TestHelper::getRussianJanuary()],
            [
                self::RUSSIAN(), ChronoField::MONTH_OF_YEAR(), TextStyle::FULL_STANDALONE(), 12, TestHelper::getRussianDecember()],
            [
                self::RUSSIAN(), ChronoField::MONTH_OF_YEAR(), TextStyle::SHORT_STANDALONE(), 1, TestHelper::getRussianJan()],
            [
                self::RUSSIAN(), ChronoField::MONTH_OF_YEAR(), TextStyle::SHORT_STANDALONE(), 12, TestHelper::getRussianDec()],
            [
                self::FINNISH(), ChronoField::DAY_OF_WEEK(), TextStyle::FULL_STANDALONE(), 2, "tiistai"],
            [
                self::FINNISH(), ChronoField::DAY_OF_WEEK(), TextStyle::SHORT_STANDALONE(), 2, "ti"],
        ];
    }

    function providerDayOfWeekData()
    {
        return [// Locale, pattern, input text, expected DayOfWeek
            [
                Locale::US(), "e", "1", DayOfWeek::SUNDAY()],
            [
                Locale::US(), "ee", "01", DayOfWeek::SUNDAY()],
            [
                Locale::US(), "c", "1", DayOfWeek::SUNDAY()],

            [
                Locale::UK(), "e", "1", DayOfWeek::MONDAY()],
            [
                Locale::UK(), "ee", "01", DayOfWeek::MONDAY()],
            [
                Locale::UK(), "c", "1", DayOfWeek::MONDAY()],
        ];
    }

// Test data is dependent on localized resources.
// TODO double check why short format vs. short standalone are different / use different test case
    function providerLenientText()
    {
// Locale, TemporalField, expected value, input text
        return [
            [
                self::RUSSIAN(), ChronoField::MONTH_OF_YEAR(), 1, "января"], // full format
            [
                self::RUSSIAN(), ChronoField::MONTH_OF_YEAR(), 1, TestHelper::getRussianJanuary()], // full standalone
            [
                self::RUSSIAN(), ChronoField::MONTH_OF_YEAR(), 1, "янв."],  // short format
            [
                self::RUSSIAN(), ChronoField::MONTH_OF_YEAR(), 1, "янв."], // short standalone
        ];
    }

    /**
     * @dataProvider provider_text
     */
    public function test_parseText(TemporalField $field, TextStyle $style, $value, $input)
    {
        $pos = new ParsePosition(0);
        $this->assertEquals($this->getFormatterFieldStyle($field, $style)->parseUnresolved($input, $pos)->getLong($field), $value);
        $this->assertEquals($pos->getIndex(), strlen($input));
    }

    /**
     * @dataProvider provider_number
     */
    public function test_parseNumber(TemporalField $field, TextStyle $style, $value, $input)
    {
        $pos = new ParsePosition(0);
        $this->assertEquals($this->getFormatterFieldStyle($field, $style)->parseUnresolved($input, $pos)->getLong($field), $value);
        $this->assertEquals($pos->getIndex(), strlen($input));
    }

    /**
     * @dataProvider providerStandaloneText
     */
    public function test_parseStandaloneText(Locale $locale, TemporalField $field, TextStyle $style, $expectedValue, $input)
    {
        $formatter = $this->getFormatterFieldStyle($field, $style)->withLocale($locale);
        $pos = new ParsePosition(0);
        $this->assertEquals($formatter->parseUnresolved($input, $pos)->getLong($field), $expectedValue);
        $this->assertEquals($pos->getIndex(), strlen($input));
    }

    /**
     * @dataProvider providerDayOfWeekData
     */
    public function test_parseDayOfWeekText(Locale $locale, $pattern, $input, DayOfWeek $expected)
    {
        $this->markTestSkipped();
        $formatter = $this->getPatternFormatter($pattern)->withLocale($locale);
        $pos = new ParsePosition(0);
        $this->assertEquals(DayOfWeek::from($formatter->parsePos($input, $pos)), $expected);
        $this->assertEquals($pos->getIndex(), strlen($input));
    }

//-----------------------------------------------------------------------
    /**
     * @dataProvider provider_text
     */
    public function test_parse_strict_caseSensitive_parseUpper(TemporalField $field, TextStyle $style, $value, $input)
    {
        if ($input === TestHelper::toUpperMb($input)) {
// Skip if the given $input is all upper case (e.g., "Q1")
            return;
        }

        $this->setCaseSensitive(true);
        $pos = new ParsePosition(0);
        $this->getFormatterFieldStyle($field, $style)->parseUnresolved(TestHelper::toUpperMb($input), $pos);
        $this->assertEquals($pos->getErrorIndex(), 0);
    }

    /**
     * @dataProvider provider_text
     */
    public function test_parse_strict_caseInsensitive_parseUpper(TemporalField $field, TextStyle $style, $value, $input)
    {
        $this->setCaseSensitive(false);
        $pos = new ParsePosition(0);
        $this->assertEquals($this->getFormatterFieldStyle($field, $style)->parseUnresolved(TestHelper::toUpperMb($input), $pos)->getLong($field), $value);
        $this->assertEquals($pos->getIndex(), strlen($input));
    }

//-----------------------------------------------------------------------
    /**
     * @dataProvider provider_text
     */
    public function test_parse_strict_caseSensitive_parseLower(TemporalField $field, TextStyle $style, $value, $input)
    {
        if ($input === TestHelper::toLowerMb($input)) {
// Skip if the given $input is all lower case (e.g., "1st quarter")
            return;
        }
        $this->setCaseSensitive(true);
        $pos = new ParsePosition(0);
        $this->getFormatterFieldStyle($field, $style)->parseUnresolved(TestHelper::toLowerMb($input), $pos);
        $this->assertEquals($pos->getErrorIndex(), 0);
    }

    /**
     * @dataProvider provider_text
     */
    public function test_parse_strict_caseInsensitive_parseLower(TemporalField $field, TextStyle $style, $value, $input)
    {
        $this->setCaseSensitive(false);
        $pos = new ParsePosition(0);
        $this->assertEquals($this->getFormatterFieldStyle($field, $style)->parseUnresolved(TestHelper::toLowerMb($input), $pos)->getLong($field), $value);
        $this->assertEquals($pos->getIndex(), strlen($input));
    }

//-----------------------------------------------------------------------
//-----------------------------------------------------------------------
//-----------------------------------------------------------------------
    public function test_parse_full_strict_full_match()
    {
        $this->setStrict(true);
        $pos = new ParsePosition(0);
        $this->assertEquals($this->getFormatterFieldStyle(ChronoField::MONTH_OF_YEAR(), TextStyle::FULL())->parseUnresolved("January", $pos)->getLong(ChronoField::MONTH_OF_YEAR()), 1);
        $this->assertEquals($pos->getIndex(), 7);
    }

    public function test_parse_full_strict_short_noMatch()
    {
        $this->setStrict(true);
        $pos = new ParsePosition(0);
        $this->getFormatterFieldStyle(ChronoField::MONTH_OF_YEAR(), TextStyle::FULL())->parseUnresolved("Janua", $pos);
        $this->assertEquals($pos->getErrorIndex(), 0);
    }

    public function test_parse_full_strict_number_noMatch()
    {
        $this->setStrict(true);
        $pos = new ParsePosition(0);
        $this->getFormatterFieldStyle(ChronoField::MONTH_OF_YEAR(), TextStyle::FULL())->parseUnresolved("1", $pos);
        $this->assertEquals($pos->getErrorIndex(), 0);
    }

//-----------------------------------------------------------------------
    public function test_parse_short_strict_full_match()
    {
        $this->setStrict(true);
        $pos = new ParsePosition(0);
        $this->assertEquals($this->getFormatterFieldStyle(ChronoField::MONTH_OF_YEAR(), TextStyle::SHORT())->parseUnresolved("January", $pos)->getLong(ChronoField::MONTH_OF_YEAR()), 1);
        $this->assertEquals($pos->getIndex(), 3);
    }

    public function test_parse_short_strict_short_match()
    {
        $this->setStrict(true);
        $pos = new ParsePosition(0);
        $this->assertEquals($this->getFormatterFieldStyle(ChronoField::MONTH_OF_YEAR(), TextStyle::SHORT())->parseUnresolved("Janua", $pos)->getLong(ChronoField::MONTH_OF_YEAR()), 1);
        $this->assertEquals($pos->getIndex(), 3);
    }

    public function test_parse_short_strict_number_noMatch()
    {
        $this->setStrict(true);
        $pos = new ParsePosition(0);
        $this->getFormatterFieldStyle(ChronoField::MONTH_OF_YEAR(), TextStyle::SHORT())->parseUnresolved("1", $pos);
        $this->assertEquals($pos->getErrorIndex(), 0);
    }

//-----------------------------------------------------------------------
    public function test_parse_french_short_strict_full_noMatch()
    {
        $this->setStrict(true);
        $pos = new ParsePosition(0);
        $this->getFormatterFieldStyle(ChronoField::MONTH_OF_YEAR(), TextStyle::SHORT())->withLocale(Locale::FRENCH())
            ->parseUnresolved("janvier", $pos);
        $this->assertEquals($pos->getErrorIndex(), 0);
    }

    public function test_parse_french_short_strict_short_match()
    {
        $this->setStrict(true);
        $pos = new ParsePosition(0);
        $this->assertEquals($this->getFormatterFieldStyle(ChronoField::MONTH_OF_YEAR(), TextStyle::SHORT())->withLocale(Locale::FRENCH())
            ->parseUnresolved("janv.", $pos)
            ->getLong(ChronoField::MONTH_OF_YEAR()),
            1);
        $this->assertEquals($pos->getIndex(), 5);
    }

//-----------------------------------------------------------------------
    public function test_parse_full_lenient_full_match()
    {
        $this->setStrict(false);
        $pos = new ParsePosition(0);
        $this->assertEquals($this->getFormatterFieldStyle(ChronoField::MONTH_OF_YEAR(), TextStyle::FULL())->parseUnresolved("January.", $pos)->getLong(ChronoField::MONTH_OF_YEAR()), 1);
        $this->assertEquals($pos->getIndex(), 7);
    }

    public function test_parse_full_lenient_short_match()
    {
        $this->setStrict(false);
        $pos = new ParsePosition(0);
        $this->assertEquals($this->getFormatterFieldStyle(ChronoField::MONTH_OF_YEAR(), TextStyle::FULL())->parseUnresolved("Janua", $pos)->getLong(ChronoField::MONTH_OF_YEAR()), 1);
        $this->assertEquals($pos->getIndex(), 3);
    }

    public function test_parse_full_lenient_number_match()
    {
        $this->setStrict(false);
        $pos = new ParsePosition(0);
        $this->assertEquals($this->getFormatterFieldStyle(ChronoField::MONTH_OF_YEAR(), TextStyle::FULL())->parseUnresolved("1", $pos)->getLong(ChronoField::MONTH_OF_YEAR()), 1);
        $this->assertEquals($pos->getIndex(), 1);
    }

//-----------------------------------------------------------------------
    public function test_parse_short_lenient_full_match()
    {
        $this->setStrict(false);
        $pos = new ParsePosition(0);
        $this->assertEquals($this->getFormatterFieldStyle(ChronoField::MONTH_OF_YEAR(), TextStyle::SHORT())->parseUnresolved("January", $pos)->getLong(ChronoField::MONTH_OF_YEAR()), 1);
        $this->assertEquals($pos->getIndex(), 7);
    }

    public function test_parse_short_lenient_short_match()
    {
        $this->setStrict(false);
        $pos = new ParsePosition(0);
        $this->assertEquals($this->getFormatterFieldStyle(ChronoField::MONTH_OF_YEAR(), TextStyle::SHORT())->parseUnresolved("Janua", $pos)->getLong(ChronoField::MONTH_OF_YEAR()), 1);
        $this->assertEquals($pos->getIndex(), 3);
    }

    public function test_parse_short_lenient_number_match()
    {
        $this->setStrict(false);
        $pos = new ParsePosition(0);
        $this->assertEquals($this->getFormatterFieldStyle(ChronoField::MONTH_OF_YEAR(), TextStyle::SHORT())->parseUnresolved("1", $pos)->getLong(ChronoField::MONTH_OF_YEAR()), 1);
        $this->assertEquals($pos->getIndex(), 1);
    }

    /**
     * @dataProvider providerLenientText
     */
    public function test_parseLenientText(Locale $locale, TemporalField $field, $expectedValue, $input)
    {
        $this->setStrict(false);
        $pos = new ParsePosition(0);
        $formatter = $this->getFormatterField($field)->withLocale($locale);
        $this->assertEquals($formatter->parseUnresolved($input, $pos)->getLong($field), $expectedValue);
        $this->assertEquals($pos->getIndex(), strlen($input));
    }

    // TODO add test case for case insensitive multibyte strings

}
