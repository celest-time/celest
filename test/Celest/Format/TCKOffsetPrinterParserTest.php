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

namespace Celest\Format;

use Celest\LocalDateTime;
use Celest\OffsetDateTime;
use Celest\Temporal\TemporalQueries;
use Celest\ZoneId;
use Celest\ZoneOffset;

/**
 * Test DateTimeFormatterBuilder.appendOffset().
 */
class TCKOffsetPrinterParser extends \PHPUnit_Framework_TestCase
{

    private static function OFFSET_UTC()
    {
        return ZoneOffset::UTC();
    }

    private static function OFFSET_P0100()
    {
        return ZoneOffset::ofHours(1);
    }

    private static function OFFSET_P0123()
    {
        return ZoneOffset::ofHoursMinutes(1, 23);
    }

    private static function OFFSET_P0023()
    {
        return ZoneOffset::ofHoursMinutes(0, 23);
    }

    private static function OFFSET_P012345()
    {
        return ZoneOffset::ofHoursMinutesSeconds(1, 23, 45);
    }

    private static function OFFSET_P000045()
    {
        return ZoneOffset::ofHoursMinutesSeconds(0, 0, 45);
    }

    private static function OFFSET_M0100()
    {
        return ZoneOffset::ofHours(-1);
    }

    private static function OFFSET_M0123()
    {
        return ZoneOffset::ofHoursMinutes(-1, -23);
    }

    private static function OFFSET_M0023()
    {
        return ZoneOffset::ofHoursMinutes(0, -23);
    }

    private static function OFFSET_M012345()
    {
        return ZoneOffset::ofHoursMinutesSeconds(-1, -23, -45);
    }

    private static function OFFSET_M000045()
    {
        return ZoneOffset::ofHoursMinutesSeconds(0, 0, -45);
    }

    private static function DT_2012_06_30_12_30_40()
    {
        return LocalDateTime::ofNumerical(2012, 6, 30, 12, 30, 40);
    }

    /** @var DateTimeFormatterBuilder */
    private $builder;

    public function setUp()
    {
        $this->builder = new DateTimeFormatterBuilder();
    }

    //-----------------------------------------------------------------------
    function data_print()
    {
        return
            [





                [
                    "+HHMM", "Z", self::DT_2012_06_30_12_30_40(), self::OFFSET_P000045(), "Z"],
                [
                    "+HHMM", "Z", self::DT_2012_06_30_12_30_40(), self::OFFSET_M0100(), "-0100"],
                [
                    "+HHMM", "Z", self::DT_2012_06_30_12_30_40(), self::OFFSET_M0123(), "-0123"],
                [
                    "+HHMM", "Z", self::DT_2012_06_30_12_30_40(), self::OFFSET_M0023(), "-0023"],
                [
                    "+HHMM", "Z", self::DT_2012_06_30_12_30_40(), self::OFFSET_M012345(), "-0123"],
                [
                    "+HHMM", "Z", self::DT_2012_06_30_12_30_40(), self::OFFSET_M000045(), "Z"],

                [
                    "+HH:MM", "Z", self::DT_2012_06_30_12_30_40(), self::OFFSET_UTC(), "Z"],
                [
                    "+HH:MM", "Z", self::DT_2012_06_30_12_30_40(), self::OFFSET_P0100(), "+01:00"],
                [
                    "+HH:MM", "Z", self::DT_2012_06_30_12_30_40(), self::OFFSET_P0123(), "+01:23"],
                [
                    "+HH:MM", "Z", self::DT_2012_06_30_12_30_40(), self::OFFSET_P0023(), "+00:23"],
                [
                    "+HH:MM", "Z", self::DT_2012_06_30_12_30_40(), self::OFFSET_P012345(), "+01:23"],
                [
                    "+HH:MM", "Z", self::DT_2012_06_30_12_30_40(), self::OFFSET_P000045(), "Z"],
                [
                    "+HH:MM", "Z", self::DT_2012_06_30_12_30_40(), self::OFFSET_M0100(), "-01:00"],
                [
                    "+HH:MM", "Z", self::DT_2012_06_30_12_30_40(), self::OFFSET_M0123(), "-01:23"],
                [
                    "+HH:MM", "Z", self::DT_2012_06_30_12_30_40(), self::OFFSET_M0023(), "-00:23"],
                [
                    "+HH:MM", "Z", self::DT_2012_06_30_12_30_40(), self::OFFSET_M012345(), "-01:23"],
                [
                    "+HH:MM", "Z", self::DT_2012_06_30_12_30_40(), self::OFFSET_M000045(), "Z"],

                [
                    "+HHMMss", "Z", self::DT_2012_06_30_12_30_40(), self::OFFSET_UTC(), "Z"],
                [
                    "+HHMMss", "Z", self::DT_2012_06_30_12_30_40(), self::OFFSET_P0100(), "+0100"],
                [
                    "+HHMMss", "Z", self::DT_2012_06_30_12_30_40(), self::OFFSET_P0123(), "+0123"],
                [
                    "+HHMMss", "Z", self::DT_2012_06_30_12_30_40(), self::OFFSET_P0023(), "+0023"],
                [
                    "+HHMMss", "Z", self::DT_2012_06_30_12_30_40(), self::OFFSET_P012345(), "+012345"],
                [
                    "+HHMMss", "Z", self::DT_2012_06_30_12_30_40(), self::OFFSET_P000045(), "+000045"],
                [
                    "+HHMMss", "Z", self::DT_2012_06_30_12_30_40(), self::OFFSET_M0100(), "-0100"],
                [
                    "+HHMMss", "Z", self::DT_2012_06_30_12_30_40(), self::OFFSET_M0123(), "-0123"],
                [
                    "+HHMMss", "Z", self::DT_2012_06_30_12_30_40(), self::OFFSET_M0023(), "-0023"],
                [
                    "+HHMMss", "Z", self::DT_2012_06_30_12_30_40(), self::OFFSET_M012345(), "-012345"],
                [
                    "+HHMMss", "Z", self::DT_2012_06_30_12_30_40(), self::OFFSET_M000045(), "-000045"],

                [
                    "+HH:MM:ss", "Z", self::DT_2012_06_30_12_30_40(), self::OFFSET_UTC(), "Z"],
                [
                    "+HH:MM:ss", "Z", self::DT_2012_06_30_12_30_40(), self::OFFSET_P0100(), "+01:00"],
                [
                    "+HH:MM:ss", "Z", self::DT_2012_06_30_12_30_40(), self::OFFSET_P0123(), "+01:23"],
                [
                    "+HH:MM:ss", "Z", self::DT_2012_06_30_12_30_40(), self::OFFSET_P0023(), "+00:23"],
                [
                    "+HH:MM:ss", "Z", self::DT_2012_06_30_12_30_40(), self::OFFSET_P012345(), "+01:23:45"],
                [
                    "+HH:MM:ss", "Z", self::DT_2012_06_30_12_30_40(), self::OFFSET_M000045(), "-00:00:45"],
                [
                    "+HH:MM:ss", "Z", self::DT_2012_06_30_12_30_40(), self::OFFSET_M0100(), "-01:00"],
                [
                    "+HH:MM:ss", "Z", self::DT_2012_06_30_12_30_40(), self::OFFSET_M0123(), "-01:23"],
                [
                    "+HH:MM:ss", "Z", self::DT_2012_06_30_12_30_40(), self::OFFSET_M0023(), "-00:23"],
                [
                    "+HH:MM:ss", "Z", self::DT_2012_06_30_12_30_40(), self::OFFSET_M012345(), "-01:23:45"],
                [
                    "+HH:MM:ss", "Z", self::DT_2012_06_30_12_30_40(), self::OFFSET_M000045(), "-00:00:45"],

                [
                    "+HHMMSS", "Z", self::DT_2012_06_30_12_30_40(), self::OFFSET_UTC(), "Z"],
                [
                    "+HHMMSS", "Z", self::DT_2012_06_30_12_30_40(), self::OFFSET_P0100(), "+010000"],
                [
                    "+HHMMSS", "Z", self::DT_2012_06_30_12_30_40(), self::OFFSET_P0123(), "+012300"],
                [
                    "+HHMMSS", "Z", self::DT_2012_06_30_12_30_40(), self::OFFSET_P0023(), "+002300"],
                [
                    "+HHMMSS", "Z", self::DT_2012_06_30_12_30_40(), self::OFFSET_P012345(), "+012345"],
                [
                    "+HHMMSS", "Z", self::DT_2012_06_30_12_30_40(), self::OFFSET_M000045(), "-000045"],
                [
                    "+HHMMSS", "Z", self::DT_2012_06_30_12_30_40(), self::OFFSET_M0100(), "-010000"],
                [
                    "+HHMMSS", "Z", self::DT_2012_06_30_12_30_40(), self::OFFSET_M0123(), "-012300"],
                [
                    "+HHMMSS", "Z", self::DT_2012_06_30_12_30_40(), self::OFFSET_M0023(), "-002300"],
                [
                    "+HHMMSS", "Z", self::DT_2012_06_30_12_30_40(), self::OFFSET_M012345(), "-012345"],
                [
                    "+HHMMSS", "Z", self::DT_2012_06_30_12_30_40(), self::OFFSET_M000045(), "-000045"],

                [
                    "+HH:MM:SS", "Z", self::DT_2012_06_30_12_30_40(), self::OFFSET_UTC(), "Z"],
                [
                    "+HH:MM:SS", "Z", self::DT_2012_06_30_12_30_40(), self::OFFSET_P0100(), "+01:00:00"],
                [
                    "+HH:MM:SS", "Z", self::DT_2012_06_30_12_30_40(), self::OFFSET_P0123(), "+01:23:00"],
                [
                    "+HH:MM:SS", "Z", self::DT_2012_06_30_12_30_40(), self::OFFSET_P0023(), "+00:23:00"],
                [
                    "+HH:MM:SS", "Z", self::DT_2012_06_30_12_30_40(), self::OFFSET_P012345(), "+01:23:45"],
                [
                    "+HH:MM:SS", "Z", self::DT_2012_06_30_12_30_40(), self::OFFSET_M000045(), "-00:00:45"],
                [
                    "+HH:MM:SS", "Z", self::DT_2012_06_30_12_30_40(), self::OFFSET_M0100(), "-01:00:00"],
                [
                    "+HH:MM:SS", "Z", self::DT_2012_06_30_12_30_40(), self::OFFSET_M0123(), "-01:23:00"],
                [
                    "+HH:MM:SS", "Z", self::DT_2012_06_30_12_30_40(), self::OFFSET_M0023(), "-00:23:00"],
                [
                    "+HH:MM:SS", "Z", self::DT_2012_06_30_12_30_40(), self::OFFSET_M012345(), "-01:23:45"],
                [
                    "+HH:MM:SS", "Z", self::DT_2012_06_30_12_30_40(), self::OFFSET_M000045(), "-00:00:45"],
            ];
    }

    function data_print_localized()
    {
        return [
            [
                TextStyle::FULL(), self::DT_2012_06_30_12_30_40(), self::OFFSET_UTC(), "GMT"
            ],
            [
                TextStyle::FULL(), self::DT_2012_06_30_12_30_40(), self::OFFSET_P0100(), "GMT+01:00"],
            [
                TextStyle::FULL(), self::DT_2012_06_30_12_30_40(), self::OFFSET_P0123(), "GMT+01:23"],
            [
                TextStyle::FULL(), self::DT_2012_06_30_12_30_40(), self::OFFSET_P0023(), "GMT+00:23"],
            [
                TextStyle::FULL(), self::DT_2012_06_30_12_30_40(), self::OFFSET_P012345(), "GMT+01:23:45"],
            [
                TextStyle::FULL(), self::DT_2012_06_30_12_30_40(), self::OFFSET_M000045(), "GMT-00:00:45"],
            [
                TextStyle::FULL(), self::DT_2012_06_30_12_30_40(), self::OFFSET_M0100(), "GMT-01:00"],
            [
                TextStyle::FULL(), self::DT_2012_06_30_12_30_40(), self::OFFSET_M0123(), "GMT-01:23"],
            [
                TextStyle::FULL(), self::DT_2012_06_30_12_30_40(), self::OFFSET_M0023(), "GMT-00:23"],
            [
                TextStyle::FULL(), self::DT_2012_06_30_12_30_40(), self::OFFSET_M012345(), "GMT-01:23:45"],
            [
                TextStyle::FULL(), self::DT_2012_06_30_12_30_40(), self::OFFSET_M000045(), "GMT-00:00:45"],
            [
                TextStyle::SHORT(), self::DT_2012_06_30_12_30_40(), self::OFFSET_UTC(), "GMT"],
            [
                TextStyle::SHORT(), self::DT_2012_06_30_12_30_40(), self::OFFSET_P0100(), "GMT+1"],
            [
                TextStyle::SHORT(), self::DT_2012_06_30_12_30_40(), self::OFFSET_P0123(), "GMT+1:23"],
            [
                TextStyle::SHORT(), self::DT_2012_06_30_12_30_40(), self::OFFSET_P0023(), "GMT+0:23"],
            [
                TextStyle::SHORT(), self::DT_2012_06_30_12_30_40(), self::OFFSET_P012345(), "GMT+1:23:45"],
            [
                TextStyle::SHORT(), self::DT_2012_06_30_12_30_40(), self::OFFSET_M000045(), "GMT-0:00:45"],
            [
                TextStyle::SHORT(), self::DT_2012_06_30_12_30_40(), self::OFFSET_M0100(), "GMT-1"],
            [
                TextStyle::SHORT(), self::DT_2012_06_30_12_30_40(), self::OFFSET_M0123(), "GMT-1:23"],
            [
                TextStyle::SHORT(), self::DT_2012_06_30_12_30_40(), self::OFFSET_M0023(), "GMT-0:23"],
            [
                TextStyle::SHORT(), self::DT_2012_06_30_12_30_40(), self::OFFSET_M012345(), "GMT-1:23:45"],
            [
                TextStyle::SHORT(), self::DT_2012_06_30_12_30_40(), self::OFFSET_M000045(), "GMT-0:00:45"],
        ];
    }

    /**
     * @dataProvider data_print
     */
    public function test_print($offsetPattern, $noOffset, LocalDateTime $ldt, ZoneId $zone, $expected)
    {
        $zdt = $ldt->atZone($zone);
        $this->builder->appendOffset($offsetPattern, $noOffset);
        $output = $this->builder->toFormatter()->format($zdt);
        $this->assertEquals($output, $expected);
    }

//-----------------------------------------------------------------------
    /**
     * @dataProvider data_print
     */
    public function test_print_pattern_X($offsetPattern, $noOffset, LocalDateTime $ldt, ZoneId $zone, $expected)
    {
        $pattern = null;
        if ($offsetPattern === "+HHmm" && $noOffset === "Z") {
            $pattern = "X";
        } else
            if ($offsetPattern === "+HHMM" && $noOffset === "Z") {
                $pattern = "XX";
            } else if ($offsetPattern === "+HH:MM" && $noOffset === "Z") {
                $pattern = "XXX";
            } else if ($offsetPattern === "+HHMMss" && $noOffset === "Z") {
                $pattern = "XXXX";
            } else if ($offsetPattern === "+HH:MM:ss" && $noOffset === "Z") {
                $pattern = "XXXXX";
            }
        if ($pattern !== null) {
            $zdt = $ldt->atZone($zone);
            $this->builder->appendPattern($pattern);
            $output = $this->builder->toFormatter()->format($zdt);
            $this->assertEquals($output, $expected);
        }
    }

    /**
     * @dataProvider data_print
     */
    public function test_print_pattern_x_($offsetPattern, $noOffset, LocalDateTime $ldt, ZoneId $zone, $expected)
    {
        $pattern = null;
        $zero = null;
        if ($offsetPattern === "+HHmm" && $noOffset === "Z") {
            $pattern = "x";
            $zero = "+00";
        } else
            if ($offsetPattern === "+HHMM" && $noOffset === "Z") {
                $pattern = "xx";
                $zero = "+0000";
            } else if ($offsetPattern === "+HH:MM" && $noOffset === "Z") {
                $pattern = "xxx";
                $zero = "+00:00";
            } else if ($offsetPattern === "+HHMMss" && $noOffset === "Z") {
                $pattern = "xxxx";
                $zero = "+0000";
            } else if ($offsetPattern === "+HH:MM:ss" && $noOffset === "Z") {
                $pattern = "xxxxx";
                $zero = "+00:00";
            }
        if ($pattern != null) {
            $zdt = $ldt->atZone($zone);
            $this->builder->appendPattern($pattern);
            $output = $this->builder->toFormatter()->format($zdt);
            $this->assertEquals($output, ($expected === "Z" ? $zero : $expected));
        }
    }

    /**
     * @dataProvider data_print
     */
    public
    function test_print_pattern_Z($offsetPattern, $noOffset, LocalDateTime $ldt, ZoneId $zone, $expected)
    {
        $pattern = null;
        if ($offsetPattern === "+HHMM" && $noOffset === "Z") {
            $zdt = $ldt->atZone($zone);
            $f1 = (new DateTimeFormatterBuilder())->appendPattern("Z")->toFormatter();
            $output1 = $f1->format($zdt);
            $this->assertEquals($output1, ($expected === "Z" ? "+0000" : $expected));

            $f2 = (new DateTimeFormatterBuilder())->appendPattern("ZZ")->toFormatter();
            $output2 = $f2->format($zdt);
            $this->assertEquals($output2, ($expected === "Z" ? "+0000" : $expected));

            $f3 = (new DateTimeFormatterBuilder())->appendPattern("ZZZ")->toFormatter();
            $output3 = $f3->format($zdt);
            $this->assertEquals($output3, ($expected === "Z" ? "+0000" : $expected));
        } else if ($offsetPattern === "+HH:MM:ss" && $noOffset === "Z") {
            $zdt = $ldt->atZone($zone);
            $f = (new DateTimeFormatterBuilder())->appendPattern("ZZZZZ")->toFormatter();
            $output = $f->format($zdt);
            $this->assertEquals($output, $expected);
        }
    }

    /**
     * @dataProvider data_print_localized
     */
    public function test_print_localized(TextStyle $style, LocalDateTime $ldt, ZoneOffset $offset, $expected)
    {
        $odt = OffsetDateTime::of($ldt, $offset);
        $zdt = $ldt->atZone($offset);

        $f = (new DateTimeFormatterBuilder())->appendLocalizedOffset($style)
            ->toFormatter();
        $this->assertEquals($f->format($odt), $expected);
        $this->assertEquals($f->format($zdt), $expected);
        $this->assertEquals($f->parseQuery($expected, TemporalQueries::fromCallable([ZoneOffset::class, 'from'])), $offset);

        if ($style == TextStyle::FULL()) {
            $f = (new DateTimeFormatterBuilder())->appendPattern("ZZZZ")
                ->toFormatter();
            $this->assertEquals($f->format($odt), $expected);
            $this->assertEquals($f->format($zdt), $expected);
            $this->assertEquals($f->parseQuery($expected, TemporalQueries::fromCallable([ZoneOffset::class, 'from'])), $offset);

            $f = (new DateTimeFormatterBuilder())->appendPattern("OOOO")
                ->toFormatter();
            $this->assertEquals($f->format($odt), $expected);
            $this->assertEquals($f->format($zdt), $expected);
            $this->assertEquals($f->parseQuery($expected, TemporalQueries::fromCallable([ZoneOffset::class, 'from'])), $offset);
        }

        if ($style == TextStyle::SHORT()) {
            $f = (new DateTimeFormatterBuilder())->appendPattern("O")
                ->toFormatter();
            $this->assertEquals($f->format($odt), $expected);
            $this->assertEquals($f->format($zdt), $expected);
            $this->assertEquals($f->parseQuery($expected, TemporalQueries::fromCallable([ZoneOffset::class, 'from'])), $offset);
        }
    }

//-----------------------------------------------------------------------
    /**
     * @expectedException \Celest\IllegalArgumentException
     */
    public
    function test_print_pattern_X6rejected()
    {
        $this->builder->appendPattern("XXXXXX");
    }

    /**
     * @expectedException \Celest\IllegalArgumentException
     */
    public
    function test_print_pattern_x6rejected_()
    {
        $this->builder->appendPattern("xxxxxx");
    }

    /**
     * @expectedException \Celest\IllegalArgumentException
     */
    public
    function test_print_pattern_Z6rejected()
    {
        $this->builder->appendPattern("ZZZZZZ");
    }

    /**
     * @expectedException \Celest\IllegalArgumentException
     */
    public
    function test_print_pattern_O2rejected()
    {
        $this->builder->appendPattern("OO");
    }

    /**
     * @expectedException \Celest\IllegalArgumentException
     */
    public
    function test_print_pattern_O3rejected()
    {
        $this->builder->appendPattern("OOO");
    }

    /**
     * @expectedException \Celest\IllegalArgumentException
     */
    public function test_print_pattern_O5rejected()
    {
        $this->builder->appendPattern("OOOOO");
    }

    /**
     * @expectedException \Celest\IllegalArgumentException
     */
    public
    function test_print_pattern_localzed_full_standline()
    {
        $this->builder->appendLocalizedOffset(TextStyle::FULL_STANDALONE());
    }

    /**
     * @expectedException \Celest\IllegalArgumentException
     */
    public
    function test_print_pattern_localzed_short_standalone()
    {
        $this->builder->appendLocalizedOffset(TextStyle::SHORT_STANDALONE());
    }

    /**
     * @expectedException \Celest\IllegalArgumentException
     */
    public
    function test_print_pattern_localzed_narrow()
    {
        $this->builder->appendLocalizedOffset(TextStyle::NARROW());
    }

    /**
     * @expectedException \Celest\IllegalArgumentException
     */
    public
    function test_print_pattern_localzed_narrow_standalone()
    {
        $this->builder->appendLocalizedOffset(TextStyle::NARROW_STANDALONE());
    }

}
