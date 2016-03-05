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
namespace Celest\Format;
use Celest\DateTimeException;
use Celest\DayOfWeek;
use Celest\Instant;
use Celest\LocalDate;
use Celest\LocalDateTime;
use Celest\Locale;
use Celest\LocalTime;
use Celest\Month;
use Celest\MonthDay;
use Celest\OffsetDateTime;
use Celest\OffsetTime;
use Celest\Temporal\ChronoField;
use Celest\Year;
use Celest\YearMonth;
use Celest\ZonedDateTime;
use Celest\ZoneId;
use Celest\ZoneOffset;

/**
 * Test DateTimeFormatter.
 */
class DateTimeFormatterTest extends \PHPUnit_Framework_TestCase
{

    public function test_withLocale_same()
    {
        $base =
            (new DateTimeFormatterBuilder())->appendLiteral2("ONE")
       ->appendValue3(ChronoField::DAY_OF_MONTH(), 1, 2, SignStyle::NOT_NEGATIVE())
       ->toFormatter2(Locale::ENGLISH())
       ->withDecimalStyle(DecimalStyle::STANDARD());
$test = $base->withLocale(Locale::ENGLISH());
$this->assertSame($test, $base);
}

    public function test_parse_errorMessage()
    {
        $this->assertGoodErrorDate([DayOfWeek::class, 'from'], "DayOfWeek");
        $this->assertGoodErrorDate([Month::class, 'from'], "Month");
        $this->assertGoodErrorDate([YearMonth::class, 'from'], "YearMonth");
        $this->assertGoodErrorDate([MonthDay::class, 'from'], "MonthDay");
        $this->assertGoodErrorDate([LocalDate::class, 'from'], "LocalDate");
        $this->assertGoodErrorDate([LocalTime::class, 'from'], "LocalTime");
        $this->assertGoodErrorDate([LocalDateTime::class, 'from'], "LocalDateTime");
        $this->assertGoodErrorDate([OffsetTime::class, 'from'], "OffsetTime");
        $this->assertGoodErrorDate([OffsetDateTime::class, 'from'], "OffsetDateTime");
        $this->assertGoodErrorDate([ZonedDateTime::class, 'from'], "ZonedDateTime");
        $this->assertGoodErrorDate([Instant::class, 'from'], "Instant");
        $this->assertGoodErrorDate([ZoneOffset::class, 'from'], "ZoneOffset");
        $this->assertGoodErrorDate([ZoneId::class, 'from'], "ZoneId");
        // TODO $this->assertGoodErrorDate(ThaiBuddhistChronology->INSTANCE::date, "");

        $this->assertGoodErrorTime([DayOfWeek::class, 'from'], "DayOfWeek");
        $this->assertGoodErrorTime([Month::class, 'from'], "Month");
        $this->assertGoodErrorTime([Year::class, 'from'], "Year");
        $this->assertGoodErrorTime([YearMonth::class, 'from'], "YearMonth");
        $this->assertGoodErrorTime([MonthDay::class, 'from'], "MonthDay");
        $this->assertGoodErrorTime([LocalDate::class, 'from'], "LocalDate");
        $this->assertGoodErrorTime([LocalTime::class, 'from'], "LocalTime");
        $this->assertGoodErrorTime([LocalDateTime::class, 'from'], "LocalDateTime");
        $this->assertGoodErrorTime([OffsetTime::class, 'from'], "OffsetTime");
        $this->assertGoodErrorTime([OffsetDateTime::class, 'from'], "OffsetDateTime");
        $this->assertGoodErrorTime([ZonedDateTime::class, 'from'], "ZonedDateTime");
        $this->assertGoodErrorTime([Instant::class, 'from'], "Instant");
        $this->assertGoodErrorTime([ZoneOffset::class, 'from'], "ZoneOffset");
        $this->assertGoodErrorTime([ZoneId::class, 'from'], "ZoneId");
        // TODO $this->assertGoodErrorTime(ThaiBuddhistChronology->INSTANCE::date, "");
    }

    private function assertGoodErrorDate($function, $expectedText)
    {
         $f = DateTimeFormatter::ofPattern("yyyy-mm-dd");
         $temporal = $f->parse("2010-06-30");
        try {
            $function($temporal);
            $this->fail("Should have failed");
        } catch (DateTimeException $ex) {
             $msg = $ex->getMessage();
            $this->assertContains($expectedText, $msg, $msg);
            $this->assertContains("Year", $msg, $msg);
            $this->assertContains("MinuteOfHour", $msg, $msg);
            $this->assertContains("DayOfMonth", $msg, $msg);
        }
    }

    private function assertGoodErrorTime($function, $expectedText)
    {
         $f = DateTimeFormatter::ofPattern("HH:MM:ss");
         $temporal = $f->parse("11:30:56");
        try {
            $function($temporal);
            $this->fail("Should have failed");
        } catch (DateTimeException $ex) {
            $msg = $ex->getMessage();
            $this->assertContains($expectedText, $msg, $msg);
            $this->assertContains("HourOfDay", $msg, $msg);
            $this->assertContains("MonthOfYear", $msg, $msg);
            $this->assertContains("SecondOfMinute", $msg, $msg);
        }
    }

    public function test_parsed_toString_resolvedTime()
    {
         $f = DateTimeFormatter::ofPattern("HH:mm:ss");
         $temporal = $f->parse("11:30:56");
         $msg = $temporal->__toString();
        $this->assertContains("11:30:56",$msg, $msg);
    }

    public function test_parsed_toString_resolvedDate()
    {
         $f = DateTimeFormatter::ofPattern("yyyy-MM-dd");
         $temporal = $f->parse("2010-06-30");
         $msg = $temporal->__toString();
        $this->assertContains("2010-06-30",$msg, $msg);
    }

    public function test_parsed_toString_resolvedDateTime()
    {
         $f = DateTimeFormatter::ofPattern("yyyy-MM-dd HH:mm:ss");
         $temporal = $f->parse("2010-06-30 11:30:56");
         $msg = $temporal->__toString();
        $this->assertContains("2010-06-30",$msg, $msg);
        $this->assertContains("11:30:56",$msg, $msg);
    }

}
