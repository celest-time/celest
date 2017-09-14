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

namespace Celest\Temporal;

use Celest\DateTimeParseException;
use Celest\DayOfWeek;
use Celest\Format\DateTimeFormatterBuilder;
use Celest\Format\ResolverStyle;
use Celest\LocalDate;
use Celest\LocalDateTime;
use Celest\Temporal\ChronoField as CF;
use PHPUnit\Framework\TestCase;

/**
 * Test.
 */
class TCKIsoFieldsTest extends TestCase
{

    function data_quarter()
    {
        return [
            [LocalDate::of(1969, 12, 29), 90, 4],
            [LocalDate::of(1969, 12, 30), 91, 4],
            [LocalDate::of(1969, 12, 31), 92, 4],

            [LocalDate::of(1970, 1, 1), 1, 1],
            [LocalDate::of(1970, 1, 2), 2, 1],
            [LocalDate::of(1970, 2, 28), 59, 1],
            [LocalDate::of(1970, 3, 1), 60, 1],
            [LocalDate::of(1970, 3, 31), 90, 1],

            [LocalDate::of(1970, 4, 1), 1, 2],
            [LocalDate::of(1970, 6, 30), 91, 2],

            [LocalDate::of(1970, 7, 1), 1, 3],
            [LocalDate::of(1970, 9, 30), 92, 3],

            [LocalDate::of(1970, 10, 1), 1, 4],
            [LocalDate::of(1970, 12, 31), 92, 4],

            [LocalDate::of(1972, 2, 28), 59, 1],
            [LocalDate::of(1972, 2, 29), 60, 1],
            [LocalDate::of(1972, 3, 1), 61, 1],
            [LocalDate::of(1972, 3, 31), 91, 1],
        ];
    }

    //-----------------------------------------------------------------------
    // DAY_OF_QUARTER
    //-----------------------------------------------------------------------
    /**
     * @dataProvider data_quarter
     */
    public function test_DOQ(LocalDate $date, $doq, $qoy)
    {
        $this->assertEquals(IsoFields::DAY_OF_QUARTER()->getFrom($date), $doq);
        $this->assertEquals($date->get(IsoFields::DAY_OF_QUARTER()), $doq);
    }

    public function test_DOQ_basics()
    {
        $this->assertEquals(IsoFields::DAY_OF_QUARTER()->isDateBased(), true);
        $this->assertEquals(IsoFields::DAY_OF_QUARTER()->isTimeBased(), false);
    }

    //-----------------------------------------------------------------------
    // QUARTER_OF_YEAR
    //-----------------------------------------------------------------------
    /**
     * @dataProvider data_quarter
     */
    public function test_QOY(LocalDate $date, $doq, $qoy)
    {
        $this->assertEquals(IsoFields::QUARTER_OF_YEAR()->getFrom($date), $qoy);
        $this->assertEquals($date->get(IsoFields::QUARTER_OF_YEAR()), $qoy);
    }

    public function test_QOY_basics()
    {
        $this->assertEquals(IsoFields::QUARTER_OF_YEAR()->isDateBased(), true);
        $this->assertEquals(IsoFields::QUARTER_OF_YEAR()->isTimeBased(), false);
    }

    //-----------------------------------------------------------------------
    // parse quarters
    //-----------------------------------------------------------------------
    /**
     * @dataProvider data_quarter
     */
    public function test_parse_quarters(LocalDate $date, $doq, $qoy)
    {
        $f = (new DateTimeFormatterBuilder())
            ->appendValue(CF::YEAR())->appendLiteral('-')
            ->appendValue(IsoFields::QUARTER_OF_YEAR())->appendLiteral('-')
            ->appendValue(IsoFields::DAY_OF_QUARTER())
            ->toFormatter()->withResolverStyle(ResolverStyle::STRICT());
        $parsed = LocalDate::parseWith($date->getYear() . "-" . $qoy . "-" . $doq, $f);
        $this->assertEquals($parsed, $date);
    }

    /**
     * @dataProvider data_quarter
     */
    public function test_parse_quarters_SMART(LocalDate $date, $doq, $qoy)
    {
        $f = (new DateTimeFormatterBuilder())
            ->appendValue(CF::YEAR())->appendLiteral('-')
            ->appendValue(IsoFields::QUARTER_OF_YEAR())->appendLiteral('-')
            ->appendValue(IsoFields::DAY_OF_QUARTER())
            ->toFormatter()->withResolverStyle(ResolverStyle::SMART());
        $parsed = LocalDate::parseWith($date->getYear() . "-" . $qoy . "-" . $doq, $f);
        $this->assertEquals($parsed, $date);
    }

    /**
     * @dataProvider data_quarter
     */
    public function test_parse_quarters_LENIENT(LocalDate $date, $doq, $qoy)
    {
        $f = (new DateTimeFormatterBuilder())
            ->appendValue(CF::YEAR())->appendLiteral('-')
            ->appendValue(IsoFields::QUARTER_OF_YEAR())->appendLiteral('-')
            ->appendValue(IsoFields::DAY_OF_QUARTER())
            ->toFormatter()->withResolverStyle(ResolverStyle::LENIENT());
        $parsed = LocalDate::parseWith($date->getYear() . "-" . $qoy . "-" . $doq, $f);
        $this->assertEquals($parsed, $date);
    }

    //-----------------------------------------------------------------------
    function data_parseLenientQuarter()
    {
        return [
            ["2012:0:1", LocalDate::of(2011, 10, 1), false],
            ["2012:5:1", LocalDate::of(2013, 1, 1), false],

            ["2012:1:-1", LocalDate::of(2011, 12, 30), false],
            ["2012:1:0", LocalDate::of(2011, 12, 31), false],
            ["2012:0:0", LocalDate::of(2011, 9, 30), false],

            ["2012:1:92", LocalDate::of(2012, 4, 1), true],
            ["2012:2:92", LocalDate::of(2012, 7, 1), true],
            ["2012:2:93", LocalDate::of(2012, 7, 2), false],
            ["2012:3:93", LocalDate::of(2012, 10, 1), false],
            ["2012:4:93", LocalDate::of(2013, 1, 1), false],
            ["2012:4:182", LocalDate::of(2013, 3, 31), false],
            ["2012:4:183", LocalDate::of(2013, 4, 1), false],

            ["2011:1:91", LocalDate::of(2011, 4, 1), true],
            ["2011:1:92", LocalDate::of(2011, 4, 2), true],
        ];
    }

    /**
     * @dataProvider data_parseLenientQuarter
     * @expectedException \Celest\DateTimeParseException
     */
    public function test_parse_parseLenientQuarter_STRICT($str, LocalDate $expected, $smart)
    {
        $f = (new DateTimeFormatterBuilder())
            ->appendValue(CF::YEAR())->appendLiteral(':')
            ->appendValue(IsoFields::QUARTER_OF_YEAR())->appendLiteral(':')
            ->appendValue(IsoFields::DAY_OF_QUARTER())
            ->toFormatter()->withResolverStyle(ResolverStyle::STRICT());
        LocalDate::parseWith($str, $f);
    }

    /**
     * @dataProvider data_parseLenientQuarter
     */
    public function test_parse_parseLenientQuarter_SMART($str, LocalDate $expected, $smart)
    {
        $f = (new DateTimeFormatterBuilder())
            ->appendValue(CF::YEAR())->appendLiteral(':')
            ->appendValue(IsoFields::QUARTER_OF_YEAR())->appendLiteral(':')
            ->appendValue(IsoFields::DAY_OF_QUARTER())
            ->toFormatter()->withResolverStyle(ResolverStyle::SMART());
        if ($smart) {
            $parsed = LocalDate::parseWith($str, $f);
            $this->assertEquals($parsed, $expected);
        } else {
            try {
                LocalDate::parseWith($str, $f);
                $this->fail("Should have failed");
            } catch (DateTimeParseException $ex) {
                // $expected
            }
        }
    }

    /**
     * @dataProvider data_parseLenientQuarter
     */
    public function test_parse_parseLenientQuarter_LENIENT($str, LocalDate $expected, $smart)
    {
        $f = (new DateTimeFormatterBuilder())
            ->appendValue(CF::YEAR())->appendLiteral(':')
            ->appendValue(IsoFields::QUARTER_OF_YEAR())->appendLiteral(':')
            ->appendValue(IsoFields::DAY_OF_QUARTER())
            ->toFormatter()->withResolverStyle(ResolverStyle::LENIENT());
        $parsed = LocalDate::parseWith($str, $f);
        $this->assertEquals($parsed, $expected);
    }

    //-----------------------------------------------------------------------
    // quarters between
    //-----------------------------------------------------------------------
    function data_quartersBetween()
    {
        return [
            [LocalDate::of(2000, 1, 1), LocalDate::of(2000, 1, 1), 0],
            [LocalDate::of(2000, 1, 1), LocalDate::of(2000, 1, 2), 0],
            [LocalDate::of(2000, 1, 1), LocalDate::of(2000, 2, 1), 0],
            [LocalDate::of(2000, 1, 1), LocalDate::of(2000, 3, 1), 0],
            [LocalDate::of(2000, 1, 1), LocalDate::of(2000, 3, 31), 0],
            [LocalDate::of(2000, 1, 1), LocalDate::of(2000, 4, 1), 1],
            [LocalDate::of(2000, 1, 1), LocalDate::of(2000, 4, 2), 1],
            [LocalDate::of(2000, 1, 1), LocalDate::of(2000, 6, 30), 1],
            [LocalDate::of(2000, 1, 1), LocalDate::of(2000, 7, 1), 2],
            [LocalDate::of(2000, 1, 1), LocalDate::of(2000, 10, 1), 3],
            [LocalDate::of(2000, 1, 1), LocalDate::of(2000, 12, 31), 3],
            [LocalDate::of(2000, 1, 1), LocalDate::of(2001, 1, 1), 4],
            [LocalDate::of(2000, 1, 1), LocalDate::of(2002, 1, 1), 8],

            [LocalDate::of(2000, 1, 1), LocalDate::of(1999, 12, 31), 0],
            [LocalDate::of(2000, 1, 1), LocalDate::of(1999, 10, 2), 0],
            [LocalDate::of(2000, 1, 1), LocalDate::of(1999, 10, 1), -1],
            [LocalDate::of(2000, 1, 1), LocalDate::of(1999, 7, 2), -1],
            [LocalDate::of(2000, 1, 1), LocalDate::of(1999, 7, 1), -2],
            [LocalDate::of(2000, 1, 1), LocalDate::of(1999, 4, 2), -2],
            [LocalDate::of(2000, 1, 1), LocalDate::of(1999, 4, 1), -3],
            [LocalDate::of(2000, 1, 1), LocalDate::of(1999, 1, 2), -3],
            [LocalDate::of(2000, 1, 1), LocalDate::of(1999, 1, 1), -4],
            [LocalDate::of(2000, 1, 1), LocalDate::of(1998, 12, 31), -4],
            [LocalDate::of(2000, 1, 1), LocalDate::of(1998, 10, 2), -4],
            [LocalDate::of(2000, 1, 1), LocalDate::of(1998, 10, 1), -5],

            [LocalDate::of(2000, 1, 1), LocalDateTime::of(2001, 4, 5, 0, 0), 5],
        ];
    }

    /**
     * @dataProvider data_quartersBetween
     */
    public function test_quarters_between(LocalDate $start, Temporal $end, $expected)
    {
        $this->assertEquals(IsoFields::QUARTER_YEARS()->between($start, $end), $expected);
    }

    /**
     * @dataProvider data_quartersBetween
     */
    public function test_quarters_between_until(LocalDate $start, Temporal $end, $expected)
    {
        $this->assertEquals($start->until($end, IsoFields::QUARTER_YEARS()), $expected);
    }

    //-----------------------------------------------------------------------
    //-----------------------------------------------------------------------
    //-----------------------------------------------------------------------
    function data_week()
    {
        return [
            [LocalDate::of(1969, 12, 29), DayOfWeek::MONDAY(), 1, 1970],
            [LocalDate::of(2012, 12, 23), DayOfWeek::SUNDAY(), 51, 2012],
            [LocalDate::of(2012, 12, 24), DayOfWeek::MONDAY(), 52, 2012],
            [LocalDate::of(2012, 12, 27), DayOfWeek::THURSDAY(), 52, 2012],
            [LocalDate::of(2012, 12, 28), DayOfWeek::FRIDAY(), 52, 2012],
            [LocalDate::of(2012, 12, 29), DayOfWeek::SATURDAY(), 52, 2012],
            [LocalDate::of(2012, 12, 30), DayOfWeek::SUNDAY(), 52, 2012],
            [LocalDate::of(2012, 12, 31), DayOfWeek::MONDAY(), 1, 2013],
            [LocalDate::of(2013, 1, 1), DayOfWeek::TUESDAY(), 1, 2013],
            [LocalDate::of(2013, 1, 2), DayOfWeek::WEDNESDAY(), 1, 2013],
            [LocalDate::of(2013, 1, 6), DayOfWeek::SUNDAY(), 1, 2013],
            [LocalDate::of(2013, 1, 7), DayOfWeek::MONDAY(), 2, 2013],
        ];
    }

//-----------------------------------------------------------------------
// WEEK_OF_WEEK_BASED_YEAR
//-----------------------------------------------------------------------
    /**
     * @dataProvider data_week
     */
    public function test_WOWBY(LocalDate $date, DayOfWeek $dow, $week, $wby)
    {
        $this->assertEquals($date->getDayOfWeek(), $dow);
        $this->assertEquals(IsoFields::WEEK_OF_WEEK_BASED_YEAR()->getFrom($date), $week);
        $this->assertEquals($date->get(IsoFields::WEEK_OF_WEEK_BASED_YEAR()), $week);
    }

    public function test_WOWBY_basics()
    {
        $this->assertEquals(IsoFields::WEEK_OF_WEEK_BASED_YEAR()->isDateBased(), true);
        $this->assertEquals(IsoFields::WEEK_OF_WEEK_BASED_YEAR()->isTimeBased(), false);
    }

//-----------------------------------------------------------------------
// WEEK_BASED_YEAR
//-----------------------------------------------------------------------
    /**
     * @dataProvider data_week
     */
    public function test_WBY(LocalDate $date, DayOfWeek $dow, $week, $wby)
    {
        $this->assertEquals($date->getDayOfWeek(), $dow);
        $this->assertEquals(IsoFields::WEEK_BASED_YEAR()->getFrom($date), $wby);
        $this->assertEquals($date->get(IsoFields::WEEK_BASED_YEAR()), $wby);
    }

    public function test_WBY_basics()
    {
        $this->assertEquals(IsoFields::WEEK_BASED_YEAR()->isDateBased(), true);
        $this->assertEquals(IsoFields::WEEK_BASED_YEAR()->isTimeBased(), false);
    }

    //-----------------------------------------------------------------------
    // parse weeks
    //-----------------------------------------------------------------------
    /**
     * @dataProvider data_week
     */
    public function test_parse_weeks_STRICT($date, DayOfWeek $dow, $week, $wby)
    {
        $f = (new DateTimeFormatterBuilder())
            ->appendValue(IsoFields::WEEK_BASED_YEAR())->appendLiteral('-')
            ->appendValue(IsoFields::WEEK_OF_WEEK_BASED_YEAR())->appendLiteral('-')
            ->appendValue(CF::DAY_OF_WEEK())
            ->toFormatter()->withResolverStyle(ResolverStyle::STRICT());
        $parsed = LocalDate::parseWith($wby . "-" . $week . "-" . $dow->getValue(), $f);
        $this->assertEquals($parsed, $date);
    }

    /**
     * @dataProvider data_week
     */
    public function test_parse_weeks_SMART($date, DayOfWeek $dow, $week, $wby)
    {
        $f = (new DateTimeFormatterBuilder())
            ->appendValue(IsoFields::WEEK_BASED_YEAR())->appendLiteral('-')
            ->appendValue(IsoFields::WEEK_OF_WEEK_BASED_YEAR())->appendLiteral('-')
            ->appendValue(CF::DAY_OF_WEEK())
            ->toFormatter()->withResolverStyle(ResolverStyle::SMART());
        $parsed = LocalDate::parseWith($wby . "-" . $week . "-" . $dow->getValue(), $f);
        $this->assertEquals($parsed, $date);
    }

    /**
     * @dataProvider data_week
     */
    public function test_parse_weeks_LENIENT($date, DayOfWeek $dow, $week, $wby)
    {
        $f = (new DateTimeFormatterBuilder())
            ->appendValue(IsoFields::WEEK_BASED_YEAR())->appendLiteral('-')
            ->appendValue(IsoFields::WEEK_OF_WEEK_BASED_YEAR())->appendLiteral('-')
            ->appendValue(CF::DAY_OF_WEEK())
            ->toFormatter()->withResolverStyle(ResolverStyle::LENIENT());
        $parsed = LocalDate::parseWith($wby . "-" . $week . "-" . $dow->getValue(), $f);
        $this->assertEquals($parsed, $date);
    }

    //-----------------------------------------------------------------------
    function data_parseLenientWeek()
    {
        return [
            ["2012:52:-1", LocalDate::of(2012, 12, 22), false],
            ["2012:52:0", LocalDate::of(2012, 12, 23), false],
            ["2012:52:8", LocalDate::of(2012, 12, 31), false],
            ["2012:52:9", LocalDate::of(2013, 1, 1), false],

            ["2012:53:1", LocalDate::of(2012, 12, 31), true],
            ["2012:54:1", LocalDate::of(2013, 1, 7), false],

            ["2013:0:1", LocalDate::of(2012, 12, 24), false],
            ["2013:0:0", LocalDate::of(2012, 12, 23), false],
        ];
    }

    /**
     * @dataProvider data_parseLenientWeek
     * @expectedException \Celest\DateTimeParseException
     */
    public function test_parse_parseLenientWeek_STRICT($str, LocalDate $expected, $smart)
    {
        $f = (new DateTimeFormatterBuilder())
            ->appendValue(IsoFields::WEEK_BASED_YEAR())->appendLiteral(':')
            ->appendValue(IsoFields::WEEK_OF_WEEK_BASED_YEAR())->appendLiteral(':')
            ->appendValue(CF::DAY_OF_WEEK())
            ->toFormatter()->withResolverStyle(ResolverStyle::STRICT());
        LocalDate::parseWith($str, $f);
    }

    /**
     * @dataProvider data_parseLenientWeek
     */
    public function test_parse_parseLenientWeek_SMART($str, LocalDate $expected, $smart)
    {
        $f = (new DateTimeFormatterBuilder())
            ->appendValue(IsoFields::WEEK_BASED_YEAR())->appendLiteral(':')
            ->appendValue(IsoFields::WEEK_OF_WEEK_BASED_YEAR())->appendLiteral(':')
            ->appendValue(CF::DAY_OF_WEEK())
            ->toFormatter()->withResolverStyle(ResolverStyle::SMART());
        if ($smart) {
            $parsed = LocalDate::parseWith($str, $f);
            $this->assertEquals($parsed, $expected);
        } else {
            try {
                LocalDate::parseWith($str, $f);
                $this->fail("Should have failed");
            } catch (DateTimeParseException $ex) {
                // $expected
            }
        }
    }

    /**
     * @dataProvider data_parseLenientWeek
     */
    public function test_parse_parseLenientWeek_LENIENT($str, LocalDate $expected, $smart)
    {
        $f = (new DateTimeFormatterBuilder())
            ->appendValue(IsoFields::WEEK_BASED_YEAR())->appendLiteral(':')
            ->appendValue(IsoFields::WEEK_OF_WEEK_BASED_YEAR())->appendLiteral(':')
            ->appendValue(CF::DAY_OF_WEEK())
            ->toFormatter()->withResolverStyle(ResolverStyle::LENIENT());
        $parsed = LocalDate::parseWith($str, $f);
        $this->assertEquals($parsed, $expected);
    }

    //-----------------------------------------------------------------------
    /**
     * @group long
     */
    public function test_loop()
    {
        // loop round at least one 400 $year cycle, including before 1970
        $date = LocalDate::of(1960, 1, 5);  // Tuseday of $week 1 1960
        $year = 1960;
        $wby = 1960;
        $weekLen = 52;
        $week = 1;
        while ($date->getYear() < 2400) {
            $loopDow = $date->getDayOfWeek();
            if ($date->getYear() != $year) {
                $year = $date->getYear();
            }
            if ($loopDow == DayOfWeek::MONDAY()) {
                $week++;
                if (($week == 53 && $weekLen == 52) || $week == 54) {
                    $week = 1;
                    $firstDayOfWeekBasedYear = $date->plusDays(14)->withDayOfYear(1);
                    $firstDay = $firstDayOfWeekBasedYear->getDayOfWeek();
                    $weekLen = ($firstDay == DayOfWeek::THURSDAY() || ($firstDay == DayOfWeek::WEDNESDAY() && $firstDayOfWeekBasedYear->isLeapYear()) ? 53 : 52);
                    $wby++;
                }
            }
            $this->assertEquals(IsoFields::WEEK_OF_WEEK_BASED_YEAR()->rangeRefinedBy($date), ValueRange::of(1, $weekLen), "Failed on " . $date . " " . $date->getDayOfWeek());
            $this->assertEquals(IsoFields::WEEK_OF_WEEK_BASED_YEAR()->getFrom($date), $week, "Failed on " . $date . " " . $date->getDayOfWeek());
            $this->assertEquals($date->get(IsoFields::WEEK_OF_WEEK_BASED_YEAR()), $week, "Failed on " . $date . " " . $date->getDayOfWeek());
            $this->assertEquals(IsoFields::WEEK_BASED_YEAR()->getFrom($date), $wby, "Failed on " . $date . " " . $date->getDayOfWeek());
            $this->assertEquals($date->get(IsoFields::WEEK_BASED_YEAR()), $wby, "Failed on " . $date . " " . $date->getDayOfWeek());
            $date = $date->plusDays(1);
        }
    }

    // TODO: more tests
}
