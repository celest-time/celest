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
use Celest\Temporal\TemporalQueries;
use Celest\ZoneId;
use Celest\ZoneOffset;
use PHPUnit\Framework\TestCase;

/**
 * Test DateTimeFormatterBuilder.appendZoneId().
 */
class TCKZoneIdPrinterParserTest extends TestCase
{

    private static function OFFSET_UTC()
    {
        return ZoneOffset::UTC();
    }

    private static function OFFSET_P0123()
    {
        return ZoneOffset::ofHoursMinutes(1, 23);
    }

    private static function EUROPE_PARIS()
    {
        return ZoneId::of("Europe/Paris");
    }

    private static function AMERICA_NEW_YORK()
    {
        return ZoneId::of("America/New_York");
    }

    private static function DT_2012_06_30_12_30_40()
    {
        return LocalDateTime::of(2012, 6, 30, 12, 30, 40);
    }

    /** @var DateTimeFormatterBuilder */
    private $builder;
    /** @var ParsePosition */
    private $pos;

    public function setUp()
    {
        $this->builder = new DateTimeFormatterBuilder();
        $this->pos = new ParsePosition(0);
    }

    //-----------------------------------------------------------------------
    function data_print()
    {
        return
            [
                [
                    self::DT_2012_06_30_12_30_40(), self::EUROPE_PARIS(), "Europe/Paris"
                ],
                [
                    self::DT_2012_06_30_12_30_40(), self::AMERICA_NEW_YORK(), "America/New_York"],
                [
                    self::DT_2012_06_30_12_30_40(), self::OFFSET_UTC(), "Z"],
                [
                    self::DT_2012_06_30_12_30_40(), self::OFFSET_P0123(), "+01:23"],
            ];
    }

    /**
     * @dataProvider data_print
     */
    public function test_print(LocalDateTime $ldt, ZoneId $zone, $expected)
    {
        $zdt = $ldt->atZone($zone);
        $this->builder->appendZoneId();
        $output = $this->builder->toFormatter()->format($zdt);
        $this->assertEquals($output, $expected);
    }

    /**
     * @dataProvider data_print
     */
    public function test_print_pattern_VV(LocalDateTime $ldt, ZoneId $zone, $expected)
    {
        $zdt = $ldt->atZone($zone);
        $this->builder->appendPattern("VV");
        $output = $this->builder->toFormatter()->format($zdt);
        $this->assertEquals($output, $expected);
    }

    //-----------------------------------------------------------------------
    /**
     * @expectedException \Celest\IllegalArgumentException
     */
    public function test_print_pattern_V1rejected()
    {
        $this->builder->appendPattern("V");
    }

    /**
     * @expectedException \Celest\IllegalArgumentException
     */
    public function test_print_pattern_V3rejected()
    {
        $this->builder->appendPattern("VVV");
    }

    /**
     * @expectedException \Celest\IllegalArgumentException
     */
    public function test_print_pattern_V4rejected()
    {
        $this->builder->appendPattern("VVVV");
    }

    /**
     * @expectedException \Celest\IllegalArgumentException
     */
    public function test_print_pattern_V5rejected()
    {
        $this->builder->appendPattern("VVVVV");
    }

    //-----------------------------------------------------------------------
    function data_parseSuccess()
    {
        return
            [
                [
                    "Z", 1, -1, ZoneId::of("Z")],
                [
                    "UTC", 3, -1, ZoneId::of("UTC")],
                [
                    "UT", 2, -1, ZoneId::of("UT")],
                [
                    "GMT", 3, -1, ZoneId::of("GMT")],

                [
                    "+00:00", 6, -1, ZoneOffset::UTC()],
                [
                    "UTC+00:00", 9, -1, ZoneId::of("UTC")],
                [
                    "UT+00:00", 8, -1, ZoneId::of("UT")],
                [
                    "GMT+00:00", 9, -1, ZoneId::of("GMT")],
                [
                    "-00:00", 6, -1, ZoneOffset::UTC()],
                [
                    "UTC-00:00", 9, -1, ZoneId::of("UTC")],
                [
                    "UT-00:00", 8, -1, ZoneId::of("UT")],
                [
                    "GMT-00:00", 9, -1, ZoneId::of("GMT")],

                [
                    "+01:30", 6, -1, ZoneOffset::ofHoursMinutes(1, 30)],
                [
                    "UTC+01:30", 9, -1, ZoneId::of("UTC+01:30")],
                [
                    "UT+02:30", 8, -1, ZoneId::of("UT+02:30")],
                [
                    "GMT+03:30", 9, -1, ZoneId::of("GMT+03:30")],
                [
                    "-01:30", 6, -1, ZoneOffset::ofHoursMinutes(-1, -30)],
                [
                    "UTC-01:30", 9, -1, ZoneId::of("UTC-01:30")],
                [
                    "UT-02:30", 8, -1, ZoneId::of("UT-02:30")],
                [
                    "GMT-03:30", 9, -1, ZoneId::of("GMT-03:30")],

// fallback to UTC
                [
                    "UTC-01:WW", 3, -1, ZoneId::of("UTC")],
                [
                    "UT-02:WW", 2, -1, ZoneId::of("UT")],
                [
                    "GMT-03:WW", 3, -1, ZoneId::of("GMT")],
                [
                    "Z0", 1, -1, ZoneOffset::UTC()],
                [
                    "UTC1", 3, -1, ZoneId::of("UTC")],

// Z not $parsed as zero
                [
                    "UTCZ", 3, -1, ZoneId::of("UTC")],
                [
                    "UTZ", 2, -1, ZoneId::of("UT")],
                [
                    "GMTZ", 3, -1, ZoneId::of("GMT")],

// 0 not $parsed
                [
                    "UTC0", 3, -1, ZoneId::of("UTC")],
                [
                    "UT0", 2, -1, ZoneId::of("UT")],

// fail to parse
                [
                    "", 0, 0, null],
                [
                    "A", 0, 0, null],
                [
                    "UZ", 0, 0, null],
                [
                    "GMA", 0, 0, null],
                [
                    "0", 0, 0, null],
                [
                    "+", 0, 0, null],
                [
                    "-", 0, 0, null],

// $zone IDs
                [
                    "Europe/London", 13, -1, ZoneId::of("Europe/London")],
                [
                    "America/New_York", 16, -1, ZoneId::of("America/New_York")],
                [
                    "America/Bogusville", 0, 0, null],
            ];
    }

    /**
     * @dataProvider data_parseSuccess
     */
    public function test_parseSuccess_plain($text, $expectedIndex, $expectedErrorIndex, $expected)
    {
        $this->builder->appendZoneId();
        $parsed = $this->builder->toFormatter()->parseUnresolved($text, $this->pos);
        $this->assertEquals($this->pos->getErrorIndex(), $expectedErrorIndex, "Incorrect error index parsing: " . $text);
        $this->assertEquals($this->pos->getIndex(), $expectedIndex, "Incorrect index parsing: " . $text);
        if ($expected !== null) {
            $this->assertEquals($parsed->query(TemporalQueries::zoneId()), $expected, "Incorrect zoneId parsing: " . $text);
            $this->assertEquals($parsed->query(TemporalQueries::offset()), null, "Incorrect offset parsing: " . $text);
            $this->assertEquals($parsed->query(TemporalQueries::zone()), $expected, "Incorrect zone parsing: " . $text);
        } else {
            $this->assertEquals($parsed, null);
        }
    }

    /**
     * @dataProvider data_parseSuccess
     */
    public function test_parseSuccess_prefix($text, $expectedIndex, $expectedErrorIndex, $expected)
    {
        $this->builder->appendZoneId();
        $this->pos->setIndex(3);
        $prefixText = "XXX" . $text;
        $parsed = $this->builder->toFormatter()->parseUnresolved($prefixText, $this->pos);
        $this->assertEquals($this->pos->getErrorIndex(), $expectedErrorIndex >= 0 ? $expectedErrorIndex + 3 : $expectedErrorIndex, "Incorrect error index parsing: " . $prefixText);
        $this->assertEquals($this->pos->getIndex(), $expectedIndex + 3, "Incorrect index parsing: " . $prefixText);
        if ($expected !== null) {
            $this->assertEquals($parsed->query(TemporalQueries::zoneId()), $expected, "Incorrect zoneId parsing: " . $prefixText);
            $this->assertEquals($parsed->query(TemporalQueries::offset()), null, "Incorrect offset parsing: " . $prefixText);
            $this->assertEquals($parsed->query(TemporalQueries::zone()), $expected, "Incorrect zone parsing: " . $prefixText);
        } else {
            $this->assertEquals($parsed, null);
        }
    }

    /**
     * @dataProvider data_parseSuccess
     */
    public function test_parseSuccess_suffix($text, $expectedIndex, $expectedErrorIndex, $expected)
    {
        $this->builder->appendZoneId();
        $suffixText = $text . "XXX";
        $parsed = $this->builder->toFormatter()->parseUnresolved($suffixText, $this->pos);
        $this->assertEquals($this->pos->getErrorIndex(), $expectedErrorIndex, "Incorrect error index parsing: " . $suffixText);
        $this->assertEquals($this->pos->getIndex(), $expectedIndex, "Incorrect index parsing: " . $suffixText);
        if ($expected !== null) {
            $this->assertEquals($parsed->query(TemporalQueries::zoneId()), $expected, "Incorrect zoneId parsing: " . $suffixText);
            $this->assertEquals($parsed->query(TemporalQueries::offset()), null, "Incorrect offset parsing: " . $suffixText);
            $this->assertEquals($parsed->query(TemporalQueries::zone()), $expected, "Incorrect zone parsing: " . $suffixText);
        } else {
            $this->assertEquals($parsed, null);
        }
    }

    /**
     * @dataProvider data_parseSuccess
     */
    public function test_parseSuccess_caseSensitive($text, $expectedIndex, $expectedErrorIndex, $expected)
    {
        $this->builder->parseCaseSensitive()->appendZoneId();
        $lcText = strtolower($text);
        $parsed = $this->builder->toFormatter()->parseUnresolved($lcText, $this->pos);
        if (preg_match("/[^A-Z]*[A-Z].*/", $text)) {  // if input has letters
            $this->assertEquals($this->pos->getErrorIndex() >= 0, true);
            $this->assertEquals($this->pos->getIndex(), 0);
            $this->assertEquals($parsed, null);
        } else {
            // case sensitive made no difference
            $this->assertEquals($this->pos->getIndex(), $expectedIndex, "Incorrect index parsing: " . $lcText);
            $this->assertEquals($this->pos->getErrorIndex(), $expectedErrorIndex, "Incorrect error index parsing: " . $lcText);
            if ($expected !== null) {
                $this->assertEquals($parsed->query(TemporalQueries::zoneId()), $expected);
                $this->assertEquals($parsed->query(TemporalQueries::offset()), null);
                $this->assertEquals($parsed->query(TemporalQueries::zone()), $expected);
            } else {
                $this->assertEquals($parsed, null);
            }
        }
    }

    /**
     * @dataProvider data_parseSuccess
     */
    public function test_parseSuccess_caseInsensitive($text, $expectedIndex, $expectedErrorIndex, $expected)
    {
        $this->builder->parseCaseInsensitive()->appendZoneId();
        $lcText = strtolower($text);
        $parsed = $this->builder->toFormatter()->parseUnresolved($lcText, $this->pos);
        $this->assertEquals($this->pos->getErrorIndex(), $expectedErrorIndex, "Incorrect error index parsing: " . $lcText);
        $this->assertEquals($this->pos->getIndex(), $expectedIndex, "Incorrect index parsing: " . $lcText);
        if ($expected !== null) {
            $zid = $parsed->query(TemporalQueries::zoneId());
            $this->assertEquals($parsed->query(TemporalQueries::zoneId()), $expected, "Incorrect zoneId parsing: " . $lcText);
            $this->assertEquals($parsed->query(TemporalQueries::offset()), null, "Incorrect offset parsing: " . $lcText);
            $this->assertEquals($parsed->query(TemporalQueries::zone()), $expected, "Incorrect zone parsing: " . $lcText);
        } else {
            $this->assertEquals($parsed, null);
        }
    }

}
