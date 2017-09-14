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
 * LIABILITY, WHETHER IN CONTRACT, ResolverStyle::STRICT() LIABILITY, OR TORT (INCLUDING
 * NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
 * SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 */
namespace Celest\Temporal;

use Celest\DateTimeException;
use Celest\DayOfWeek;
use Celest\Format\DateTimeFormatterBuilder;
use Celest\Format\ResolverStyle;
use Celest\Helper\Math;
use Celest\LocalDate;
use Celest\Temporal\ChronoField as CF;
use PHPUnit\Framework\TestCase;

/**
 * Test WeekFields.
 */
class TCKWeekFieldsTest extends TestCase
{

    /**
     * @dataProvider data_weekFields
     */
    public function test_of_DayOfWeek_int_singleton(DayOfWeek $firstDayOfWeek, $minDays)
    {
        $week = WeekFields::of($firstDayOfWeek, $minDays);
        $this->assertEquals($week->getFirstDayOfWeek(), $firstDayOfWeek, "Incorrect $firstDayOfWeek");
        $this->assertEquals($week->getMinimalDaysInFirstWeek(), $minDays, "Incorrect MinimalDaysInFirstWeek");
        $this->assertSame(WeekFields::of($firstDayOfWeek, $minDays), $week);
    }

    //-----------------------------------------------------------------------
    /**
     * @dataProvider data_weekFields
     */
    public function test_basics(DayOfWeek $firstDayOfWeek, $minDays)
    {
        $week = WeekFields::of($firstDayOfWeek, $minDays);
        $this->assertEquals($week->dayOfWeek()->isDateBased(), true);
        $this->assertEquals($week->dayOfWeek()->isTimeBased(), false);

        $this->assertEquals($week->weekOfMonth()->isDateBased(), true);
        $this->assertEquals($week->weekOfMonth()->isTimeBased(), false);

        $this->assertEquals($week->weekOfYear()->isDateBased(), true);
        $this->assertEquals($week->weekOfYear()->isTimeBased(), false);

        $this->assertEquals($week->weekOfWeekBasedYear()->isDateBased(), true);
        $this->assertEquals($week->weekOfWeekBasedYear()->isTimeBased(), false);

        $this->assertEquals($week->weekBasedYear()->isDateBased(), true);
        $this->assertEquals($week->weekBasedYear()->isTimeBased(), false);
    }

    //-----------------------------------------------------------------------
    public function test_dayOfWeekField_simpleGet()
    {
        $date = LocalDate::of(2000, 1, 10);  // Known to be ISO Monday
        $this->assertEquals($date->get(WeekFields::ISO()->dayOfWeek()), 1);
        $this->assertEquals($date->get(WeekFields::of(DayOfWeek::MONDAY(), 1)->dayOfWeek()), 1);
        $this->assertEquals($date->get(WeekFields::of(DayOfWeek::MONDAY(), 7)->dayOfWeek()), 1);
        $this->assertEquals($date->get(WeekFields::SUNDAY_START()->dayOfWeek()), 2);
        $this->assertEquals($date->get(WeekFields::of(DayOfWeek::SUNDAY(), 1)->dayOfWeek()), 2);
        $this->assertEquals($date->get(WeekFields::of(DayOfWeek::SUNDAY(), 7)->dayOfWeek()), 2);
        $this->assertEquals($date->get(WeekFields::of(DayOfWeek::SATURDAY(), 1)->dayOfWeek()), 3);
        $this->assertEquals($date->get(WeekFields::of(DayOfWeek::FRIDAY(), 1)->dayOfWeek()), 4);
        $this->assertEquals($date->get(WeekFields::of(DayOfWeek::TUESDAY(), 1)->dayOfWeek()), 7);
    }

    public function test_dayOfWeekField_simpleSet()
    {
        $date = LocalDate::of(2000, 1, 10);  // Known to be ISO Monday
        $this->assertEquals($date->with(WeekFields::ISO()->dayOfWeek(), 2), LocalDate::of(2000, 1, 11));
        $this->assertEquals($date->with(WeekFields::ISO()->dayOfWeek(), 7), LocalDate::of(2000, 1, 16));

        $this->assertEquals($date->with(WeekFields::SUNDAY_START()->dayOfWeek(), 3), LocalDate::of(2000, 1, 11));
        $this->assertEquals($date->with(WeekFields::SUNDAY_START()->dayOfWeek(), 7), LocalDate::of(2000, 1, 15));

        $this->assertEquals($date->with(WeekFields::of(DayOfWeek::SATURDAY(), 1)->dayOfWeek(), 4), LocalDate::of(2000, 1, 11));
        $this->assertEquals($date->with(WeekFields::of(DayOfWeek::TUESDAY(), 1)->dayOfWeek(), 1), LocalDate::of(2000, 1, 4));
    }

    /**
     * @dataProvider data_weekFields
     */
    public function test_dayOfWeekField(DayOfWeek $firstDayOfWeek, $minDays)
    {
        $day = LocalDate::of(2000, 1, 10);  // Known to be ISO Monday
        $week = WeekFields::of($firstDayOfWeek, $minDays);
        $f = $week->dayOfWeek();

        for ($i = 1; $i <= 7; $i++) {
            $this->assertEquals($day->get($f), (7 + $day->getDayOfWeek()->getValue() - $firstDayOfWeek->getValue()) % 7 + 1);
            $day = $day->plusDays(1);
        }
    }

    /**
     * @dataProvider data_weekFields
     */
    public function test_weekOfMonthField(DayOfWeek $firstDayOfWeek, $minDays)
    {
        $day = LocalDate::of(2012, 12, 31);  // Known to be ISO Monday
        $week = WeekFields::of($firstDayOfWeek, $minDays);
        $dowField = $week->dayOfWeek();
        $womField = $week->weekOfMonth();

        for ($i = 1; $i <= 15; $i++) {
            $actualDOW = $day->get($dowField);
            $actualWOM = $day->get($womField);

            // Verify that the combination of $day of $week and $week of month can be used
            // to reconstruct the same $date.
            $day1 = $day->withDayOfMonth(1);
            $offset = -($day1->get($dowField) - 1);

            $week1 = $day1->get($womField);
            if ($week1 == 0) {
                // $week of the 1st is partial; start with $first full $week
                $offset += 7;
            }

            $offset += $actualDOW - 1;
            $offset += ($actualWOM - 1) * 7;
            $result = $day1->plusDays($offset);

            $this->assertEquals($result, $day, "Incorrect dayOfWeek or weekOfMonth: "
                . sprintf("%s, ISO Dow: %s, offset: %s, actualDOW: %s, actualWOM: %s, expected: %s, result: %s\n",
                    $week, $day->getDayOfWeek(), $offset, $actualDOW, $actualWOM, $day, $result));
            $day = $day->plusDays(1);
        }
    }

    /**
     * @dataProvider data_weekFields
     */
    public function test_weekOfYearField(DayOfWeek $firstDayOfWeek, $minDays)
    {
        $day = LocalDate::of(2012, 12, 31);  // Known to be ISO Monday
        $week = WeekFields::of($firstDayOfWeek, $minDays);
        $dowField = $week->dayOfWeek();
        $woyField = $week->weekOfYear();

        for ($i = 1; $i <= 15; $i++) {
            $actualDOW = $day->get($dowField);
            $actualWOY = $day->get($woyField);

            // Verify that the combination of $day of $week and $week of month can be used
            // to reconstruct the same $date.
            $day1 = $day->withDayOfYear(1);
            $offset = -($day1->get($dowField) - 1);
            $week1 = $day1->get($woyField);
            if ($week1 == 0) {
                // $week of the 1st is partial; start with $first full $week
                $offset += 7;
            }
            $offset += $actualDOW - 1;
            $offset += ($actualWOY - 1) * 7;
            $result = $day1->plusDays($offset);

            $this->assertEquals($result, $day, "Incorrect dayOfWeek or weekOfYear "
                . sprintf("%s, ISO Dow: %s, offset: %s, actualDOW: %s, actualWOM: %s, expected: %s, result: %s\n",
                    $week, $day->getDayOfWeek(), $offset, $actualDOW, $actualWOY, $day, $result));
            $day = $day->plusDays(1);
        }
    }

    /**
     * Verify that the $date can be reconstructed from the DOW, WeekOfWeekBasedYear,
     * and WeekBasedYear for every combination of start of $week
     * and minimal days in $week.
     * @param DayOfWeek $firstDayOfWeek the $first $day of the $week
     * @param int $minDays the minimum number of days in the $week
     */
    /**
     * @dataProvider data_weekFields
     */
    public function test_weekOfWeekBasedYearField(DayOfWeek $firstDayOfWeek, $minDays)
    {
        $day = LocalDate::of(2012, 12, 31);  // Known to be ISO Monday
        $weekDef = WeekFields::of($firstDayOfWeek, $minDays);
        $dowField = $weekDef->dayOfWeek();
        $wowbyField = $weekDef->weekOfWeekBasedYear();
        $yowbyField = $weekDef->weekBasedYear();

        for ($i = 1; $i <= 15; $i++) {
            $actualDOW = $day->get($dowField);
            $actualWOWBY = $day->get($wowbyField);
            $actualYOWBY = $day->get($yowbyField);

            // Verify that the combination of $day of $week and $week of month can be used
            // to reconstruct the same $date.
            $day1 = LocalDate::of($actualYOWBY, 1, 1);
            $isoDOW = $day1->getDayOfWeek();
            $dow = (7 + $isoDOW->getValue() - $firstDayOfWeek->getValue()) % 7 + 1;

            $weekStart = Math::floorMod(1 - $dow, 7);
            if ($weekStart + 1 > $weekDef->getMinimalDaysInFirstWeek()) {
                // The previous $week has the minimum days in the current month to be a '$week'
                $weekStart -= 7;
            }
            $weekStart += $actualDOW - 1;
            $weekStart += ($actualWOWBY - 1) * 7;
            $result = $day1->plusDays($weekStart);

            $this->assertEquals($result, $day, "Incorrect dayOfWeek or weekOfYear "
                . sprintf("%s, ISO Dow: %s, weekStart: %s, actualDOW: %s, actualWOWBY: %s, YearOfWBY: %d, expected day: %s, result: %s\n",
                    $weekDef, $day->getDayOfWeek(), $weekStart, $actualDOW, $actualWOWBY, $actualYOWBY, $day, $result));
            $day = $day->plusDays(1);
        }
    }

    /**
     * @dataProvider data_weekFields
     * @group long
     */
    public function test_fieldRanges(DayOfWeek $firstDayOfWeek, $minDays)
    {
        $weekDef = WeekFields::of($firstDayOfWeek, $minDays);
        $womField = $weekDef->weekOfMonth();
        $woyField = $weekDef->weekOfYear();

        $day = LocalDate::of(2012, 11, 30);
        $endDay = LocalDate::of(2013, 1, 2);
        while ($day->isBefore($endDay)) {
            $last = $day->with(CF::DAY_OF_MONTH(), $day->lengthOfMonth());
            $lastWOM = $last->get($womField);
            $first = $day->with(CF::DAY_OF_MONTH(), 1);
            $firstWOM = $first->get($womField);
            $rangeWOM = $day->range($womField);
            $this->assertEquals($rangeWOM->getMinimum(), $firstWOM,
                "Range min should be same as WeekOfMonth for first day of month: "
                . $first . ", " . $weekDef);
            $this->assertEquals($rangeWOM->getMaximum(), $lastWOM,
                "Range max should be same as WeekOfMonth for last day of month: "
                . $last . ", " . $weekDef);

            $last = $day->with(CF::DAY_OF_YEAR(), $day->lengthOfYear());
            $lastWOY = $last->get($woyField);
            $first = $day->with(CF::DAY_OF_YEAR(), 1);
            $firstWOY = $first->get($woyField);
            $rangeWOY = $day->range($woyField);
            $this->assertEquals($rangeWOY->getMinimum(), $firstWOY,
                "Range min should be same as WeekOfYear for first day of Year: "
                . $day . ", " . $weekDef);
            $this->assertEquals($rangeWOY->getMaximum(), $lastWOY,
                "Range max should be same as WeekOfYear for last day of Year: "
                . $day . ", " . $weekDef);

            $day = $day->plusDays(1);
        }
    }

    //-----------------------------------------------------------------------
    // withDayOfWeek()
    //-----------------------------------------------------------------------
    /**
     * @dataProvider data_weekFields
     */
    public function test_withDayOfWeek(DayOfWeek $firstDayOfWeek, $minDays)
    {
        $day = LocalDate::of(2012, 12, 15);  // Safely in the middle of a month
        $week = WeekFields::of($firstDayOfWeek, $minDays);
        $dowField = $week->dayOfWeek();
        $womField = $week->weekOfMonth();
        $woyField = $week->weekOfYear();

        $wom = $day->get($womField);
        $woy = $day->get($woyField);
        for ($dow = 1; $dow <= 7; $dow++) {
            $result = $day->with($dowField, $dow);
            $this->assertEquals($result->get($dowField), $dow, sprintf("Incorrect new Day of week: %s", $result));
            $this->assertEquals($result->get($womField), $wom, "Week of Month should not change");
            $this->assertEquals($result->get($woyField), $woy, "Week of Year should not change");
        }
    }

    /**
     * @dataProvider data_weekFields
     */
    public function test_rangeWeekOfWeekBasedYear(DayOfWeek $firstDayOfWeek, $minDays)
    {
        $weekFields = WeekFields::of($firstDayOfWeek, $minDays);
        $dowField = $weekFields->dayOfWeek();
        $wowByField = $weekFields->weekOfWeekBasedYear();

        $day1 = LocalDate::of(2012, 1, $weekFields->getMinimalDaysInFirstWeek());
        $day1 = $day1->with($wowByField, 1)->with($dowField, 1);

        $day2 = LocalDate::of(2013, 1, $weekFields->getMinimalDaysInFirstWeek());
        $day2 = $day2->with($wowByField, 1)->with($dowField, 1);

        $expectedWeeks = ChronoUnit::DAYS()->between($day1, $day2) / 7;

        $range = $day1->range($wowByField);
        $this->assertEquals($range->getMaximum(), $expectedWeeks, "Range incorrect");
    }

    /**
     * @dataProvider data_weekFields
     */
    public function test_withWeekOfWeekBasedYear(DayOfWeek $firstDayOfWeek, $minDays)
    {
        $day = LocalDate::of(2012, 12, 31);
        $week = WeekFields::of($firstDayOfWeek, $minDays);
        $dowField = $week->dayOfWeek();
        $wowbyField = $week->weekOfWeekBasedYear();
        $yowbyField = $week->weekBasedYear();

        $dowExpected = ($day->get($dowField) - 1) % 7 + 1;
        $dowDate = $day->with($dowField, $dowExpected);
        $dowResult = $dowDate->get($dowField);
        $this->assertEquals($dowResult, $dowExpected, "Localized DayOfWeek not correct; " . $day . " -->" . $dowDate);

        $weekExpected = $day->get($wowbyField) + 1;
        $range = $day->range($wowbyField);
        $weekExpected = (($weekExpected - 1) % (int)$range->getMaximum()) + 1;
        $weekDate = $day->with($wowbyField, $weekExpected);
        $weekResult = $weekDate->get($wowbyField);
        $this->assertEquals($weekResult, $weekExpected, "Localized WeekOfWeekBasedYear not correct; " . $day . " -->" . $weekDate);

        $yearExpected = $day->get($yowbyField) + 1;

        $yearDate = $day->with($yowbyField, $yearExpected);
        $yearResult = $yearDate->get($yowbyField);
        $this->assertEquals($yearResult, $yearExpected, "Localized WeekBasedYear not correct; " . $day . " --> " . $yearDate);

        $range = $yearDate->range($wowbyField);
        $weekExpected = Math::min($day->get($wowbyField), $range->getMaximum());

        $weekActual = $yearDate->get($wowbyField);
        $this->assertEquals($weekActual, $weekExpected, "Localized WeekOfWeekBasedYear week should not change; " . $day . " --> " . $yearDate . ", actual: " . $weekActual . ", weekExpected: " . $weekExpected);
    }

    //-----------------------------------------------------------------------
    /**
     * @dataProvider data_weekFields
     * @group long
     */
    public function test_parse_resolve_localizedWom(DayOfWeek $firstDayOfWeek, $minDays)
    {
        $date = LocalDate::of(2012, 12, 15);
        $week = WeekFields::of($firstDayOfWeek, $minDays);
        $womField = $week->weekOfMonth();

        for ($i = 1; $i <= 60; $i++) {
            $f = (new DateTimeFormatterBuilder())
                ->appendValue(CF::YEAR())->appendLiteral(':')
                ->appendValue(CF::MONTH_OF_YEAR())->appendLiteral(':')
                ->appendValue($womField)->appendLiteral(':')
                ->appendValue(CF::DAY_OF_WEEK())->toFormatter()->withResolverStyle(ResolverStyle::SMART());
            $str = $date->getYear() . ":" . $date->getMonthValue() . ":" .
                $date->get($womField) . ":" . $date->get(CF::DAY_OF_WEEK());
            $parsed = LocalDate::parseWith($str, $f);
            $this->assertEquals($parsed, $date, " ::" . $str . "::" . $i);

            $date = $date->plusDays(1);
        }
    }

    /**
     * @dataProvider data_weekFields
     * @group long
     */
    public function test_parse_resolve_localizedWom_lenient(DayOfWeek $firstDayOfWeek, $minDays)
    {
        $date = LocalDate::of(2012, 12, 15);
        $week = WeekFields::of($firstDayOfWeek, $minDays);
        $womField = $week->weekOfMonth();

        for ($i = 1; $i <= 60; $i++) {
            $f = (new DateTimeFormatterBuilder())
                ->appendValue(CF::YEAR())->appendLiteral(':')
                ->appendValue(CF::MONTH_OF_YEAR())->appendLiteral(':')
                ->appendValue($womField)->appendLiteral(':')
                ->appendValue(CF::DAY_OF_WEEK())->toFormatter()->withResolverStyle(ResolverStyle::LENIENT());
            $wom = $date->get($womField);
            $dow = $date->get(CF::DAY_OF_WEEK());
            for ($j = $wom - 10; $j < $wom + 10; $j++) {
                $str = $date->getYear() . ":" . $date->getMonthValue() . ":" . $j . ":" . $dow;
                $parsed = LocalDate::parseWith($str, $f);
                $this->assertEquals($parsed, $date->plusWeeks($j - $wom), " ::" . $str . ": :" . $i . "::" . $j);
            }

            $date = $date->plusDays(1);
        }
    }

    /**
     * @dataProvider data_weekFields
     */
    public function test_parse_resolve_localizedWom_strict(DayOfWeek $firstDayOfWeek, $minDays)
    {
        $week = WeekFields::of($firstDayOfWeek, $minDays);
        $womField = $week->weekOfMonth();
        $f = (new DateTimeFormatterBuilder())
            ->appendValue(CF::YEAR())->appendLiteral(':')
            ->appendValue(CF::MONTH_OF_YEAR())->appendLiteral(':')
            ->appendValue($womField)->appendLiteral(':')
            ->appendValue(CF::DAY_OF_WEEK())->toFormatter()->withResolverStyle(ResolverStyle::STRICT());
        $str = "2012:1:0:1";
        try {
            $date = LocalDate::parseWith($str, $f);
            $this->assertEquals($date->getYear(), 2012);
            $this->assertEquals($date->getMonthValue(), 1);
            $this->assertEquals($date->get($womField), 0);
            $this->assertEquals($date->get(CF::DAY_OF_WEEK()), 1);
        } catch (DateTimeException $ex) {
            // expected
        }
    }

    //-----------------------------------------------------------------------
    /**
     * @dataProvider data_weekFields
     */
    public function test_parse_resolve_localizedWomDow(DayOfWeek $firstDayOfWeek, $minDays)
    {
        $date = LocalDate::of(2012, 12, 15);
        $week = WeekFields::of($firstDayOfWeek, $minDays);
        $dowField = $week->dayOfWeek();
        $womField = $week->weekOfMonth();

        for ($i = 1; $i <= 15; $i++) {
            $f = (new DateTimeFormatterBuilder())
                ->appendValue(CF::YEAR())->appendLiteral(':')
                ->appendValue(CF::MONTH_OF_YEAR())->appendLiteral(':')
                ->appendValue($womField)->appendLiteral(':')
                ->appendValue($dowField)->toFormatter();
            $str = $date->getYear() . ":" . $date->getMonthValue() . ":" .
                $date->get($womField) . ":" . $date->get($dowField);
            $parsed = LocalDate::parseWith($str, $f);
            $this->assertEquals($parsed, $date, " :: " . $str . " " . $i);

            $date = $date->plusDays(1);
        }
    }

    /**
     * @dataProvider data_weekFields
     * @group long
     */
    public function test_parse_resolve_localizedWomDow_lenient(DayOfWeek $firstDayOfWeek, $minDays)
    {
        $date = LocalDate::of(2012, 12, 15);
        $week = WeekFields::of($firstDayOfWeek, $minDays);
        $dowField = $week->dayOfWeek();
        $womField = $week->weekOfMonth();

        for ($i = 1; $i <= 60; $i++) {
            $f = (new DateTimeFormatterBuilder())
                ->appendValue(CF::YEAR())->appendLiteral(':')
                ->appendValue(CF::MONTH_OF_YEAR())->appendLiteral(':')
                ->appendValue($womField)->appendLiteral(':')
                ->appendValue($dowField)->toFormatter()->withResolverStyle(ResolverStyle::LENIENT());
            $wom = $date->get($womField);
            $dow = $date->get($dowField);
            for ($j = $wom - 10; $j < $wom + 10; $j++) {
                $str = $date->getYear() . ":" . $date->getMonthValue() . ":" . $j . ":" . $dow;
                $parsed = LocalDate::parseWith($str, $f);
                $this->assertEquals($parsed, $date->plusWeeks($j - $wom), " ::" . $str . ": :" . $i . "::" . $j);
            }

            $date = $date->plusDays(1);
        }
    }

    //-----------------------------------------------------------------------
    /**
     * @dataProvider data_weekFields
     * @group long
     */
    public function test_parse_resolve_localizedWoy(DayOfWeek $firstDayOfWeek, $minDays)
    {
        $date = LocalDate::of(2012, 12, 15);
        $week = WeekFields::of($firstDayOfWeek, $minDays);
        $woyField = $week->weekOfYear();

        for ($i = 1; $i <= 60; $i++) {
            $f = (new DateTimeFormatterBuilder())
                ->appendValue(CF::YEAR())->appendLiteral(':')
                ->appendValue($woyField)->appendLiteral(':')
                ->appendValue(CF::DAY_OF_WEEK())->toFormatter();
            $str = $date->getYear() . ":" .
                $date->get($woyField) . ":" . $date->get(CF::DAY_OF_WEEK());
            $parsed = LocalDate::parseWith($str, $f);
            $this->assertEquals($parsed, $date, " :: " . $str . " " . $i);

            $date = $date->plusDays(1);
        }
    }

    /**
     * @dataProvider data_weekFields
     * @group long
     */
    public function test_parse_resolve_localizedWoy_lenient(DayOfWeek $firstDayOfWeek, $minDays)
    {
        $date = LocalDate::of(2012, 12, 15);
        $week = WeekFields::of($firstDayOfWeek, $minDays);
        $woyField = $week->weekOfYear();

        for ($i = 1; $i <= 60; $i++) {
            $f = (new DateTimeFormatterBuilder())
                ->appendValue(CF::YEAR())->appendLiteral(':')
                ->appendValue($woyField)->appendLiteral(':')
                ->appendValue(CF::DAY_OF_WEEK())->toFormatter()->withResolverStyle(ResolverStyle::LENIENT());
            $woy = $date->get($woyField);
            $dow = $date->get(CF::DAY_OF_WEEK());
            for ($j = $woy - 60; $j < $woy + 60; $j++) {
                $str = $date->getYear() . ":" . $j . ":" . $dow;
                $parsed = LocalDate::parseWith($str, $f);
                $this->assertEquals($parsed, $date->plusWeeks($j - $woy), " ::" . $str . ": :" . $i . "::" . $j);
            }

            $date = $date->plusDays(1);
        }
    }

    /**
     * @dataProvider data_weekFields
     */
    public function test_parse_resolve_localizedWoy_strict(DayOfWeek $firstDayOfWeek, $minDays)
    {
        $week = WeekFields::of($firstDayOfWeek, $minDays);
        $woyField = $week->weekOfYear();
        $f = (new DateTimeFormatterBuilder())
            ->appendValue(CF::YEAR())->appendLiteral(':')
            ->appendValue($woyField)->appendLiteral(':')
            ->appendValue(CF::DAY_OF_WEEK())->toFormatter()->withResolverStyle(ResolverStyle::STRICT());
        $str = "2012:0:1";
        try {
            $date = LocalDate::parseWith($str, $f);
            $this->assertEquals($date->getYear(), 2012);
            $this->assertEquals($date->get($woyField), 0);
            $this->assertEquals($date->get(CF::DAY_OF_WEEK()), 1);
        } catch (DateTimeException $ex) {
            // expected
        }
    }

    //-----------------------------------------------------------------------
    /**
     * @dataProvider data_weekFields
     * @group long
     */
    public function test_parse_resolve_localizedWoyDow(DayOfWeek $firstDayOfWeek, $minDays)
    {
        $date = LocalDate::of(2012, 12, 15);
        $week = WeekFields::of($firstDayOfWeek, $minDays);
        $dowField = $week->dayOfWeek();
        $woyField = $week->weekOfYear();

        for ($i = 1; $i <= 60; $i++) {
            $f = (new DateTimeFormatterBuilder())
                ->appendValue(CF::YEAR())->appendLiteral(':')
                ->appendValue(CF::MONTH_OF_YEAR())->appendLiteral(':')
                ->appendValue($woyField)->appendLiteral(':')
                ->appendValue($dowField)->toFormatter();
            $str = $date->getYear() . ":" . $date->getMonthValue() . ":" .
                $date->get($woyField) . ":" . $date->get($dowField);
            $parsed = LocalDate::parseWith($str, $f);
            $this->assertEquals($parsed, $date, " :: " . $str . " " . $i);

            $date = $date->plusDays(1);
        }
    }

    /**
     * @dataProvider data_weekFields
     * @group long
     */
    public function test_parse_resolve_localizedWoyDow_lenient(DayOfWeek $firstDayOfWeek, $minDays)
    {
        $date = LocalDate::of(2012, 12, 15);
        $week = WeekFields::of($firstDayOfWeek, $minDays);
        $dowField = $week->dayOfWeek();
        $woyField = $week->weekOfYear();

        for ($i = 1; $i <= 60; $i++) {
            $f = (new DateTimeFormatterBuilder())
                ->appendValue(CF::YEAR())->appendLiteral(':')
                ->appendValue($woyField)->appendLiteral(':')
                ->appendValue($dowField)->toFormatter()->withResolverStyle(ResolverStyle::LENIENT());
            $woy = $date->get($woyField);
            $dow = $date->get($dowField);
            for ($j = $woy - 60; $j < $woy + 60; $j++) {
                $str = $date->getYear() . ":" . $j . ":" . $dow;
                $parsed = LocalDate::parseWith($str, $f);
                $this->assertEquals($parsed, $date->plusWeeks($j - $woy), " ::" . $str . ": :" . $i . "::" . $j);
            }

            $date = $date->plusDays(1);
        }
    }

    //-----------------------------------------------------------------------
    /**
     * @dataProvider data_weekFields
     * @group long
     */
    public function test_parse_resolve_localizedWoWBY(DayOfWeek $firstDayOfWeek, $minDays)
    {
        $date = LocalDate::of(2012, 12, 31);
        $week = WeekFields::of($firstDayOfWeek, $minDays);
        $wowbyField = $week->weekOfWeekBasedYear();
        $yowbyField = $week->weekBasedYear();

        for ($i = 1; $i <= 60; $i++) {
            $f = (new DateTimeFormatterBuilder())
                ->appendValue($yowbyField)->appendLiteral(':')
                ->appendValue($wowbyField)->appendLiteral(':')
                ->appendValue(CF::DAY_OF_WEEK())->toFormatter();
            $str = $date->get($yowbyField) . ":" . $date->get($wowbyField) . ":" .
                $date->get(CF::DAY_OF_WEEK());
            $parsed = LocalDate::parseWith($str, $f);
            $this->assertEquals($parsed, $date, " :: " . $str . " " . $i);

            $date = $date->plusDays(1);
        }
    }

    /**
     * @dataProvider data_weekFields
     * @group long
     */
    public function test_parse_resolve_localizedWoWBY_lenient(DayOfWeek $firstDayOfWeek, $minDays)
    {
        $date = LocalDate::of(2012, 12, 31);
        $week = WeekFields::of($firstDayOfWeek, $minDays);
        $wowbyField = $week->weekOfWeekBasedYear();
        $yowbyField = $week->weekBasedYear();

        for ($i = 1; $i <= 60; $i++) {
            $f = (new DateTimeFormatterBuilder())
                ->appendValue($yowbyField)->appendLiteral(':')
                ->appendValue($wowbyField)->appendLiteral(':')
                ->appendValue(CF::DAY_OF_WEEK())->toFormatter()->withResolverStyle(ResolverStyle::LENIENT());
            $wowby = $date->get($wowbyField);
            $dow = $date->get(CF::DAY_OF_WEEK());
            for ($j = $wowby - 60; $j < $wowby + 60; $j++) {
                $str = $date->get($yowbyField) . ":" . $j . ":" . $dow;
                $parsed = LocalDate::parseWith($str, $f);
                $this->assertEquals($parsed, $date->plusWeeks($j - $wowby), " ::" . $str . ": :" . $i . "::" . $j);
            }

            $date = $date->plusDays(1);
        }
    }

    /**
     * @dataProvider data_weekFields
     */
    public function test_parse_resolve_localizedWoWBY_strict(DayOfWeek $firstDayOfWeek, $minDays)
    {
        $week = WeekFields::of($firstDayOfWeek, $minDays);
        $wowbyField = $week->weekOfWeekBasedYear();
        $yowbyField = $week->weekBasedYear();
        $f = (new DateTimeFormatterBuilder())
            ->appendValue($yowbyField)->appendLiteral(':')
            ->appendValue($wowbyField)->appendLiteral(':')
            ->appendValue(CF::DAY_OF_WEEK())->toFormatter()->withResolverStyle(ResolverStyle::STRICT());
        $str = "2012:0:1";
        try {
            $date = LocalDate::parseWith($str, $f);
            $this->assertEquals($date->get($yowbyField), 2012);
            $this->assertEquals($date->get($wowbyField), 0);
            $this->assertEquals($date->get(CF::DAY_OF_WEEK()), 1);
        } catch (DateTimeException $ex) {
            // expected
        }
    }

    //-----------------------------------------------------------------------
    /**
     * @dataProvider data_weekFields
     * @group long
     */
    public function test_parse_resolve_localizedWoWBYDow(DayOfWeek $firstDayOfWeek, $minDays)
    {
        $date = LocalDate::of(2012, 12, 31);
        $week = WeekFields::of($firstDayOfWeek, $minDays);
        $dowField = $week->dayOfWeek();
        $wowbyField = $week->weekOfWeekBasedYear();
        $yowbyField = $week->weekBasedYear();

        for ($i = 1; $i <= 60; $i++) {
            $f = (new DateTimeFormatterBuilder())
                ->appendValue($yowbyField)->appendLiteral(':')
                ->appendValue($wowbyField)->appendLiteral(':')
                ->appendValue($dowField)->toFormatter();
            $str = $date->get($yowbyField) . ":" . $date->get($wowbyField) . ":" .
                $date->get($dowField);
            $parsed = LocalDate::parseWith($str, $f);
            $this->assertEquals($parsed, $date, " :: " . $str . " " . $i);

            $date = $date->plusDays(1);
        }
    }

    /**
     * @dataProvider data_weekFields
     * @group long
     */
    public function test_parse_resolve_localizedWoWBYDow_lenient(DayOfWeek $firstDayOfWeek, $minDays)
    {
        $date = LocalDate::of(2012, 12, 31);
        $week = WeekFields::of($firstDayOfWeek, $minDays);
        $dowField = $week->dayOfWeek();
        $wowbyField = $week->weekOfWeekBasedYear();
        $yowbyField = $week->weekBasedYear();

        for ($i = 1; $i <= 60; $i++) {
            $f = (new DateTimeFormatterBuilder())
                ->appendValue($yowbyField)->appendLiteral(':')
                ->appendValue($wowbyField)->appendLiteral(':')
                ->appendValue($dowField)->toFormatter()->withResolverStyle(ResolverStyle::LENIENT());
            $wowby = $date->get($wowbyField);
            $dow = $date->get($dowField);
            for ($j = $wowby - 60; $j < $wowby + 60; $j++) {
                $str = $date->get($yowbyField) . ":" . $j . ":" . $dow;
                $parsed = LocalDate::parseWith($str, $f);
                $this->assertEquals($parsed, $date->plusWeeks($j - $wowby), " ::" . $str . ": :" . $i . "::" . $j);
            }

            $date = $date->plusDays(1);
        }
    }


    //-----------------------------------------------------------------------
    function data_weekFields()
    {
        $objects = [];
        $i = 0;
        foreach (DayOfWeek::values() as $firstDayOfWeek) {
            for ($minDays = 1; $minDays <= 7; $minDays++) {
                $objects[$i++] = [$firstDayOfWeek, $minDays];
            }
        }
        return $objects;
    }

    //-----------------------------------------------------------------------
    function provider_WeekBasedYearData()
    {
        return [
            [WeekFields::of(DayOfWeek::SUNDAY(), 1), 2008, 52, 7, LocalDate::of(2008, 12, 27)],
            [WeekFields::of(DayOfWeek::SUNDAY(), 1), 2009, 1, 1, LocalDate::of(2008, 12, 28)],
            [WeekFields::of(DayOfWeek::SUNDAY(), 1), 2009, 1, 2, LocalDate::of(2008, 12, 29)],
            [WeekFields::of(DayOfWeek::SUNDAY(), 1), 2009, 1, 3, LocalDate::of(2008, 12, 30)],
            [WeekFields::of(DayOfWeek::SUNDAY(), 1), 2009, 1, 4, LocalDate::of(2008, 12, 31)],
            [WeekFields::of(DayOfWeek::SUNDAY(), 1), 2009, 1, 5, LocalDate::of(2009, 1, 1)],
            [WeekFields::of(DayOfWeek::SUNDAY(), 1), 2009, 2, 1, LocalDate::of(2009, 1, 4)],
            [WeekFields::of(DayOfWeek::SUNDAY(), 1), 2009, 2, 2, LocalDate::of(2009, 1, 5)],
            [WeekFields::of(DayOfWeek::SUNDAY(), 1), 2009, 2, 3, LocalDate::of(2009, 1, 6)],
        ];
    }

    /**
     * @dataProvider provider_WeekBasedYearData
     */
    public function test_weekBasedYears(WeekFields $weekDef, $weekBasedYear,
                                        $weekOfWeekBasedYear, $dayOfWeek, LocalDate $date)
    {
        $dowField = $weekDef->dayOfWeek();
        $wowbyField = $weekDef->weekOfWeekBasedYear();
        $yowbyField = $weekDef->weekBasedYear();
        $this->assertEquals($date->get($dowField), $dayOfWeek, "DayOfWeek mismatch");
        $this->assertEquals($date->get($wowbyField), $weekOfWeekBasedYear, "Week of WeekBasedYear mismatch");
        $this->assertEquals($date->get($yowbyField), $weekBasedYear, "Year of WeekBasedYear mismatch");
    }


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
    // Validate with the same data used by IsoFields.
    //-----------------------------------------------------------------------
    /**
     * @dataProvider data_week
     */
    public function test_WOWBY(LocalDate $date, DayOfWeek $dow, $week, $wby)
    {
        $weekDef = WeekFields::ISO();
        $dowField = $weekDef->dayOfWeek();
        $wowbyField = $weekDef->weekOfWeekBasedYear();
        $yowbyField = $weekDef->weekBasedYear();

        $this->assertEquals($date->get($dowField), $dow->getValue());
        $this->assertEquals($date->get($wowbyField), $week);
        $this->assertEquals($date->get($yowbyField), $wby);
    }

    //-----------------------------------------------------------------------
    // equals() and hashCode().
    //-----------------------------------------------------------------------
    public function test_equals()
    {
        $weekDef_iso = WeekFields::ISO();
        $weekDef_sundayStart = WeekFields::SUNDAY_START();

        $this->assertTrue($weekDef_iso->equals(WeekFields::of(DayOfWeek::MONDAY(), 4)));
        $this->assertTrue($weekDef_sundayStart->equals(WeekFields::of(DayOfWeek::SUNDAY(), 1)));
        //$this->assertEquals($weekDef_iso->hashCode(), WeekFields::of(DayOfWeek::MONDAY(), 4)->hashCode());
        // $this->assertEquals($weekDef_sundayStart->hashCode(), WeekFields::of(DayOfWeek::SUNDAY(), 1)->hashCode());

        $this->assertFalse($weekDef_iso->equals($weekDef_sundayStart));
        //$this->assertNotEquals($weekDef_iso->hashCode(), $weekDef_sundayStart->hashCode());
    }

}
