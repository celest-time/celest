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
use Celest\Format\TextStyle;
use Celest\LocalDate;
use Celest\Locale;
use Celest\Temporal\ChronoField;
use Celest\Temporal\IsoFields;
use Celest\Temporal\MockFieldValue;
use Celest\Temporal\TemporalField;
use Celest\TestHelper;

class TextPrinterTest extends AbstractTestPrinterParser
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
    /**
     * @expectedException \Celest\DateTimeException
     */
    public function test_print_emptyCalendrical()
    {
        $this->getFormatterFieldStyle(ChronoField::DAY_OF_WEEK(), TextStyle::FULL())->formatTo(self::EMPTY_DTA(), $buf);
    }

    public function test_print_append()
    {
        $buf = "EXISTING";
        $this->getFormatterFieldStyle(ChronoField::DAY_OF_WEEK(), TextStyle::FULL())->formatTo(LocalDate::of(2012, 4, 18), $buf);
        $this->assertEquals("EXISTINGWednesday", $buf);
    }

    //-----------------------------------------------------------------------
    function provider_dow()
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
                    ChronoField::DAY_OF_WEEK(), TextStyle::NARROW(), 1, "M"],
                [
                    ChronoField::DAY_OF_WEEK(), TextStyle::NARROW(), 2, "T"],
                [
                    ChronoField::DAY_OF_WEEK(), TextStyle::NARROW(), 3, "W"],
                [
                    ChronoField::DAY_OF_WEEK(), TextStyle::NARROW(), 4, "T"],
                [
                    ChronoField::DAY_OF_WEEK(), TextStyle::NARROW(), 5, "F"],
                [
                    ChronoField::DAY_OF_WEEK(), TextStyle::NARROW(), 6, "S"],
                [
                    ChronoField::DAY_OF_WEEK(), TextStyle::NARROW(), 7, "S"],

                [
                    ChronoField::DAY_OF_MONTH(), TextStyle::FULL(), 1, "1"],
                [
                    ChronoField::DAY_OF_MONTH(), TextStyle::FULL(), 2, "2"],
                [
                    ChronoField::DAY_OF_MONTH(), TextStyle::FULL(), 3, "3"],
                [
                    ChronoField::DAY_OF_MONTH(), TextStyle::FULL(), 28, "28"],
                [
                    ChronoField::DAY_OF_MONTH(), TextStyle::FULL(), 29, "29"],
                [
                    ChronoField::DAY_OF_MONTH(), TextStyle::FULL(), 30, "30"],
                [
                    ChronoField::DAY_OF_MONTH(), TextStyle::FULL(), 31, "31"],

                [
                    ChronoField::DAY_OF_MONTH(), TextStyle::SHORT(), 1, "1"],
                [
                    ChronoField::DAY_OF_MONTH(), TextStyle::SHORT(), 2, "2"],
                [
                    ChronoField::DAY_OF_MONTH(), TextStyle::SHORT(), 3, "3"],
                [
                    ChronoField::DAY_OF_MONTH(), TextStyle::SHORT(), 28, "28"],
                [
                    ChronoField::DAY_OF_MONTH(), TextStyle::SHORT(), 29, "29"],
                [
                    ChronoField::DAY_OF_MONTH(), TextStyle::SHORT(), 30, "30"],
                [
                    ChronoField::DAY_OF_MONTH(), TextStyle::SHORT(), 31, "31"],

                [
                    ChronoField::MONTH_OF_YEAR(), TextStyle::FULL(), 1, "January"],
                [
                    ChronoField::MONTH_OF_YEAR(), TextStyle::FULL(), 2, "February"],
                [
                    ChronoField::MONTH_OF_YEAR(), TextStyle::FULL(), 3, "March"],
                [
                    ChronoField::MONTH_OF_YEAR(), TextStyle::FULL(), 4, "April"],
                [
                    ChronoField::MONTH_OF_YEAR(), TextStyle::FULL(), 5, "May"],
                [
                    ChronoField::MONTH_OF_YEAR(), TextStyle::FULL(), 6, "June"],
                [
                    ChronoField::MONTH_OF_YEAR(), TextStyle::FULL(), 7, "July"],
                [
                    ChronoField::MONTH_OF_YEAR(), TextStyle::FULL(), 8, "August"],
                [
                    ChronoField::MONTH_OF_YEAR(), TextStyle::FULL(), 9, "September"],
                [
                    ChronoField::MONTH_OF_YEAR(), TextStyle::FULL(), 10, "October"],
                [
                    ChronoField::MONTH_OF_YEAR(), TextStyle::FULL(), 11, "November"],
                [
                    ChronoField::MONTH_OF_YEAR(), TextStyle::FULL(), 12, "December"],

                [
                    ChronoField::MONTH_OF_YEAR(), TextStyle::SHORT(), 1, "Jan"],
                [
                    ChronoField::MONTH_OF_YEAR(), TextStyle::SHORT(), 2, "Feb"],
                [
                    ChronoField::MONTH_OF_YEAR(), TextStyle::SHORT(), 3, "Mar"],
                [
                    ChronoField::MONTH_OF_YEAR(), TextStyle::SHORT(), 4, "Apr"],
                [
                    ChronoField::MONTH_OF_YEAR(), TextStyle::SHORT(), 5, "May"],
                [
                    ChronoField::MONTH_OF_YEAR(), TextStyle::SHORT(), 6, "Jun"],
                [
                    ChronoField::MONTH_OF_YEAR(), TextStyle::SHORT(), 7, "Jul"],
                [
                    ChronoField::MONTH_OF_YEAR(), TextStyle::SHORT(), 8, "Aug"],
                [
                    ChronoField::MONTH_OF_YEAR(), TextStyle::SHORT(), 9, "Sep"],
                [
                    ChronoField::MONTH_OF_YEAR(), TextStyle::SHORT(), 10, "Oct"],
                [
                    ChronoField::MONTH_OF_YEAR(), TextStyle::SHORT(), 11, "Nov"],
                [
                    ChronoField::MONTH_OF_YEAR(), TextStyle::SHORT(), 12, "Dec"],

                [
                    ChronoField::MONTH_OF_YEAR(), TextStyle::NARROW(), 1, "J"],
                [
                    ChronoField::MONTH_OF_YEAR(), TextStyle::NARROW(), 2, "F"],
                [
                    ChronoField::MONTH_OF_YEAR(), TextStyle::NARROW(), 3, "M"],
                [
                    ChronoField::MONTH_OF_YEAR(), TextStyle::NARROW(), 4, "A"],
                [
                    ChronoField::MONTH_OF_YEAR(), TextStyle::NARROW(), 5, "M"],
                [
                    ChronoField::MONTH_OF_YEAR(), TextStyle::NARROW(), 6, "J"],
                [
                    ChronoField::MONTH_OF_YEAR(), TextStyle::NARROW(), 7, "J"],
                [
                    ChronoField::MONTH_OF_YEAR(), TextStyle::NARROW(), 8, "A"],
                [
                    ChronoField::MONTH_OF_YEAR(), TextStyle::NARROW(), 9, "S"],
                [
                    ChronoField::MONTH_OF_YEAR(), TextStyle::NARROW(), 10, "O"],
                [
                    ChronoField::MONTH_OF_YEAR(), TextStyle::NARROW(), 11, "N"],
                [
                    ChronoField::MONTH_OF_YEAR(), TextStyle::NARROW(), 12, "D"],

                [
                    ChronoField::ERA(), TextStyle::FULL(), 0, "Before Christ"],
                [
                    ChronoField::ERA(), TextStyle::FULL(), 1, "Anno Domini"],
                [
                    ChronoField::ERA(), TextStyle::SHORT(), 0, "BC"],
                [
                    ChronoField::ERA(), TextStyle::SHORT(), 1, "AD"],
                [
                    ChronoField::ERA(), TextStyle::NARROW(), 0, "B"],
                [
                    ChronoField::ERA(), TextStyle::NARROW(), 1, "A"],

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

    function providerDayOfWeekData()
    {
        return
            [
                // Locale, pattern, $expected text, input DayOfWeek
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

    function provider_japaneseEra()
    {
        return
            [
                [
                    ChronoField::ERA(), TextStyle::FULL(), 2, "Heisei"
                ], // Note: CLDR doesn't define "wide" Japanese era names.
                [
                    ChronoField::ERA(), TextStyle::SHORT(), 2, "Heisei"],
                [
                    ChronoField::ERA(), TextStyle::NARROW(), 2, "H"],
            ];
    }

// Test data is dependent on localized resources.
    function provider_StandaloneNames()
    {
        return
            [
                // standalone names for 2013-01-01 (Tue)
                // Locale, TemporalField, TextStyle, $expected text
                [
                    self::RUSSIAN(), ChronoField::MONTH_OF_YEAR(), TextStyle::FULL_STANDALONE(), TestHelper::getRussianJanuary()],
                [
                    self::RUSSIAN(), ChronoField::MONTH_OF_YEAR(), TextStyle::SHORT_STANDALONE(), TestHelper::getRussianJan()],
                [
                    self::FINNISH(), ChronoField::DAY_OF_WEEK(), TextStyle::FULL_STANDALONE(), "tiistai"],
                [
                    self::FINNISH(), ChronoField::DAY_OF_WEEK(), TextStyle::SHORT_STANDALONE(), "ti"],
            ];
    }

    /**
     * @dataProvider provider_dow
     */
    public function test_format(TemporalField $field, TextStyle $style, $value, $expected)
    {
        $buf = '';
        $this->getFormatterFieldStyle($field, $style)->formatTo(new MockFieldValue($field, $value), $buf);
        $this->assertEquals($expected, $buf);
    }

    /**
     * @dataProvider providerDayOfWeekData
     */
    public function test_formatDayOfWeek(Locale $locale, $pattern, $expected, DayOfWeek $dayOfWeek)
    {
        $formatter = $this->getPatternFormatter($pattern)->withLocale($locale);
        $text = $formatter->format($dayOfWeek);
        $this->assertEquals($expected, $text);
    }

    /**
     * @dataProvider provider_japaneseEra
     */
    public function test_formatJapaneseEra(TemporalField $field, TextStyle $style, $value, $expected)
    {
        $this->markTestIncomplete('JapaneseChronology');
        $ld = LocalDate::of(2013, 1, 31);
        $buf = '';
        $this->getFormatterFieldStyle($field, $style)->withChronology(JapaneseChronology::INSTANCE())->formatTo($ld, $buf);
        $this->assertEquals($expected, $buf);
    }

    /**
     * @dataProvider provider_StandaloneNames
     */
    public function test_standaloneNames(Locale $locale, TemporalField $field, TextStyle $style, $expected)
    {
        $buf = '';
        $this->getFormatterFieldStyle($field, $style)->withLocale($locale)->formatTo(LocalDate::of(2013, 1, 1), $buf);
        $this->assertEquals($expected, $buf);
    }

//-----------------------------------------------------------------------
    public function test_print_french_long()
    {
        $buf = '';
        $this->getFormatterFieldStyle(ChronoField::MONTH_OF_YEAR(), TextStyle::FULL())->withLocale(Locale::FRENCH())->formatTo(LocalDate::of(2012, 1, 1), $buf);
        $this->assertEquals("janvier", $buf);
    }

    public function test_print_french_short()
    {
        $buf = '';
        $this->getFormatterFieldStyle(ChronoField::MONTH_OF_YEAR(), TextStyle::SHORT())->withLocale(Locale::FRENCH())->formatTo(LocalDate::of(2012, 1, 1), $buf);
        $this->assertEquals("janv.", $buf);
    }

//-----------------------------------------------------------------------
    public function test_toString1()
    {
        $this->assertEquals("Text(MonthOfYear)", $this->getFormatterFieldStyle(ChronoField::MONTH_OF_YEAR(), TextStyle::FULL())->__toString());
    }

    public function test_toString2()
    {
        $this->assertEquals("Text(MonthOfYear,SHORT)", $this->getFormatterFieldStyle(ChronoField::MONTH_OF_YEAR(), TextStyle::SHORT())->__toString());
    }

}
