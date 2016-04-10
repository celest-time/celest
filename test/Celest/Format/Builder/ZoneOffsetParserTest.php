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
use Celest\Temporal\ChronoField;
use Celest\Temporal\TemporalAccessor;
use Celest\ZoneOffset;
use RuntimeException;

/**
 * Test ZoneOffsetPrinterParser.
 */
class TestZoneOffsetParser extends AbstractTestPrinterParser
{

//-----------------------------------------------------------------------
    public function data_error()
    {
        return [
            ["+HH:MM:ss", "Z", "hello", -1, RuntimeException::class],
            ["+HH:MM:ss", "Z", "hello", 6, RuntimeException ::class],
        ];
    }

    /**
     * @dataProvider data_error
     */
    public function test_parse_error($pattern, $noOffsetText, $text, $pos, $expected)
    {
        try {
            $this->getFormatterPattern($pattern, $noOffsetText)->parseUnresolved($text, new ParsePosition($pos));
        } catch (RuntimeException $ex) {
            $this->assertInstanceOf($expected, $ex);
        }
    }

//-----------------------------------------------------------------------
    public function test_parse_exactMatch_UTC()
    {
        $pos = new ParsePosition(0);
        $parsed = $this->getFormatterPattern("+HH:MM:ss", "Z")->parseUnresolved("Z", $pos);
        $this->assertEquals(1, $pos->getIndex());
        $this->assertParsed($parsed, ZoneOffset::UTC());
    }

    public function test_parse_startStringMatch_UTC()
    {
        $pos = new ParsePosition(0);
        $parsed = $this->getFormatterPattern("+HH:MM:ss", "Z")->parseUnresolved("ZOTHER", $pos);
        $this->assertEquals($pos->getIndex(), 1);
        $this->assertParsed($parsed, ZoneOffset::UTC());
    }

    public function test_parse_midStringMatch_UTC()
    {
        $pos = new ParsePosition(5);
        $parsed = $this->getFormatterPattern("+HH:MM:ss", "Z")->parseUnresolved("OTHERZOTHER", $pos);
        $this->assertEquals($pos->getIndex(), 6);
        $this->assertParsed($parsed, ZoneOffset::UTC());
    }

    public function test_parse_endStringMatch_UTC()
    {
        $pos = new ParsePosition(5);
        $parsed = $this->getFormatterPattern("+HH:MM:ss", "Z")->parseUnresolved("OTHERZ", $pos);
        $this->assertEquals($pos->getIndex(), 6);
        $this->assertParsed($parsed, ZoneOffset::UTC());
    }

//-----------------------------------------------------------------------
    public function test_parse_exactMatch_UTC_EmptyUTC()
    {
        $pos = new ParsePosition(0);
        $parsed = $this->getFormatterPattern("+HH:MM:ss", "")->parseUnresolved("", $pos);
        $this->assertEquals($pos->getIndex(), 0);
        $this->assertParsed($parsed, ZoneOffset::UTC());
    }

    public function test_parse_startStringMatch_UTC_EmptyUTC()
    {
        $pos = new ParsePosition(0);
        $parsed = $this->getFormatterPattern("+HH:MM:ss", "")->parseUnresolved("OTHER", $pos);
        $this->assertEquals($pos->getIndex(), 0);
        $this->assertParsed($parsed, ZoneOffset::UTC());
    }

    public function test_parse_midStringMatch_UTC_EmptyUTC()
    {
        $pos = new ParsePosition(5);
        $parsed = $this->getFormatterPattern("+HH:MM:ss", "")->parseUnresolved("OTHEROTHER", $pos);
        $this->assertEquals($pos->getIndex(), 5);
        $this->assertParsed($parsed, ZoneOffset::UTC());
    }

    public function test_parse_endStringMatch_UTC_EmptyUTC()
    {
        $pos = new ParsePosition(5);
        $parsed = $this->getFormatterPattern("+HH:MM:ss", "")->parseUnresolved("OTHER", $pos);
        $this->assertEquals($pos->getIndex(), 5);
        $this->assertParsed($parsed, ZoneOffset::UTC());
    }

//-----------------------------------------------------------------------
    public function provider_offsets()
    {
        return
            [
                [
                    "+HH", "+00", ZoneOffset::UTC()
                ],
                [
                    "+HH", "-00", ZoneOffset::UTC()],
                [
                    "+HH", "+01", ZoneOffset::ofHours(1)],
                [
                    "+HH", "-01", ZoneOffset::ofHours(-1)],

                [
                    "+HHMM", "+0000", ZoneOffset::UTC()],
                [
                    "+HHMM", "-0000", ZoneOffset::UTC()],
                [
                    "+HHMM", "+0102", ZoneOffset::ofHoursMinutes(1, 2)],
                [
                    "+HHMM", "-0102", ZoneOffset::ofHoursMinutes(-1, -2)],

                [
                    "+HH:MM", "+00:00", ZoneOffset::UTC()],
                [
                    "+HH:MM", "-00:00", ZoneOffset::UTC()],
                [
                    "+HH:MM", "+01:02", ZoneOffset::ofHoursMinutes(1, 2)],
                [
                    "+HH:MM", "-01:02", ZoneOffset::ofHoursMinutes(-1, -2)],

                [
                    "+HHMMss", "+0000", ZoneOffset::UTC()],
                [
                    "+HHMMss", "-0000", ZoneOffset::UTC()],
                [
                    "+HHMMss", "+0100", ZoneOffset::ofHoursMinutesSeconds(1, 0, 0)],
                [
                    "+HHMMss", "+0159", ZoneOffset::ofHoursMinutesSeconds(1, 59, 0)],
                [
                    "+HHMMss", "+0200", ZoneOffset::ofHoursMinutesSeconds(2, 0, 0)],
                [
                    "+HHMMss", "+1800", ZoneOffset::ofHoursMinutesSeconds(18, 0, 0)],
                [
                    "+HHMMss", "+010215", ZoneOffset::ofHoursMinutesSeconds(1, 2, 15)],
                [
                    "+HHMMss", "-0100", ZoneOffset::ofHoursMinutesSeconds(-1, 0, 0)],
                [
                    "+HHMMss", "-0200", ZoneOffset::ofHoursMinutesSeconds(-2, 0, 0)],
                [
                    "+HHMMss", "-1800", ZoneOffset::ofHoursMinutesSeconds(-18, 0, 0)],

                [
                    "+HHMMss", "+000000", ZoneOffset::UTC()],
                [
                    "+HHMMss", "-000000", ZoneOffset::UTC()],
                [
                    "+HHMMss", "+010000", ZoneOffset::ofHoursMinutesSeconds(1, 0, 0)],
                [
                    "+HHMMss", "+010203", ZoneOffset::ofHoursMinutesSeconds(1, 2, 3)],
                [
                    "+HHMMss", "+015959", ZoneOffset::ofHoursMinutesSeconds(1, 59, 59)],
                [
                    "+HHMMss", "+020000", ZoneOffset::ofHoursMinutesSeconds(2, 0, 0)],
                [
                    "+HHMMss", "+180000", ZoneOffset::ofHoursMinutesSeconds(18, 0, 0)],
                [
                    "+HHMMss", "-010000", ZoneOffset::ofHoursMinutesSeconds(-1, 0, 0)],
                [
                    "+HHMMss", "-020000", ZoneOffset::ofHoursMinutesSeconds(-2, 0, 0)],
                [
                    "+HHMMss", "-180000", ZoneOffset::ofHoursMinutesSeconds(-18, 0, 0)],

                [
                    "+HH:MM:ss", "+00:00", ZoneOffset::UTC()],
                [
                    "+HH:MM:ss", "-00:00", ZoneOffset::UTC()],
                [
                    "+HH:MM:ss", "+01:00", ZoneOffset::ofHoursMinutesSeconds(1, 0, 0)],
                [
                    "+HH:MM:ss", "+01:02", ZoneOffset::ofHoursMinutesSeconds(1, 2, 0)],
                [
                    "+HH:MM:ss", "+01:59", ZoneOffset::ofHoursMinutesSeconds(1, 59, 0)],
                [
                    "+HH:MM:ss", "+02:00", ZoneOffset::ofHoursMinutesSeconds(2, 0, 0)],
                [
                    "+HH:MM:ss", "+18:00", ZoneOffset::ofHoursMinutesSeconds(18, 0, 0)],
                [
                    "+HH:MM:ss", "+01:02:15", ZoneOffset::ofHoursMinutesSeconds(1, 2, 15)],
                [
                    "+HH:MM:ss", "-01:00", ZoneOffset::ofHoursMinutesSeconds(-1, 0, 0)],
                [
                    "+HH:MM:ss", "-02:00", ZoneOffset::ofHoursMinutesSeconds(-2, 0, 0)],
                [
                    "+HH:MM:ss", "-18:00", ZoneOffset::ofHoursMinutesSeconds(-18, 0, 0)],

                [
                    "+HH:MM:ss", "+00:00:00", ZoneOffset::UTC()],
                [
                    "+HH:MM:ss", "-00:00:00", ZoneOffset::UTC()],
                [
                    "+HH:MM:ss", "+01:00:00", ZoneOffset::ofHoursMinutesSeconds(1, 0, 0)],
                [
                    "+HH:MM:ss", "+01:02:03", ZoneOffset::ofHoursMinutesSeconds(1, 2, 3)],
                [
                    "+HH:MM:ss", "+01:59:59", ZoneOffset::ofHoursMinutesSeconds(1, 59, 59)],
                [
                    "+HH:MM:ss", "+02:00:00", ZoneOffset::ofHoursMinutesSeconds(2, 0, 0)],
                [
                    "+HH:MM:ss", "+18:00:00", ZoneOffset::ofHoursMinutesSeconds(18, 0, 0)],
                [
                    "+HH:MM:ss", "-01:00:00", ZoneOffset::ofHoursMinutesSeconds(-1, 0, 0)],
                [
                    "+HH:MM:ss", "-02:00:00", ZoneOffset::ofHoursMinutesSeconds(-2, 0, 0)],
                [
                    "+HH:MM:ss", "-18:00:00", ZoneOffset::ofHoursMinutesSeconds(-18, 0, 0)],

                [
                    "+HHMMSS", "+000000", ZoneOffset::UTC()],
                [
                    "+HHMMSS", "-000000", ZoneOffset::UTC()],
                [
                    "+HHMMSS", "+010203", ZoneOffset::ofHoursMinutesSeconds(1, 2, 3)],
                [
                    "+HHMMSS", "-010203", ZoneOffset::ofHoursMinutesSeconds(-1, -2, -3)],

                [
                    "+HH:MM:SS", "+00:00:00", ZoneOffset::UTC()],
                [
                    "+HH:MM:SS", "-00:00:00", ZoneOffset::UTC()],
                [
                    "+HH:MM:SS", "+01:02:03", ZoneOffset::ofHoursMinutesSeconds(1, 2, 3)],
                [
                    "+HH:MM:SS", "-01:02:03", ZoneOffset::ofHoursMinutesSeconds(-1, -2, -3)],
            ];
    }

    /**
     * @dataProvider provider_offsets
     */
    public function test_parse_exactMatch($pattern, $parse, ZoneOffset $expected)
    {
        $pos = new ParsePosition(0);
        $parsed = $this->getFormatterPattern($pattern, "Z")->parseUnresolved($parse, $pos);
        $this->assertEquals($pos->getIndex(), strlen($parse));
        $this->assertParsed($parsed, $expected);
    }

    /**
     * @dataProvider provider_offsets
     */
    public function test_parse_startStringMatch($pattern, $parse, ZoneOffset $expected)
    {
        $pos = new ParsePosition(0);
        $parsed = $this->getFormatterPattern($pattern, "Z")->parseUnresolved($parse . ":OTHER", $pos);
        $this->assertEquals($pos->getIndex(), strlen($parse));
        $this->assertParsed($parsed, $expected);
    }

    /**
     * @dataProvider provider_offsets
     */
    public function test_parse_midStringMatch($pattern, $parse, ZoneOffset $expected)
    {
        $pos = new ParsePosition(5);
        $parsed = $this->getFormatterPattern($pattern, "Z")->parseUnresolved("OTHER" . $parse . ":OTHER", $pos);
        $this->assertEquals($pos->getIndex(), strlen($parse) + 5);
        $this->assertParsed($parsed, $expected);
    }

    /**
     * @dataProvider provider_offsets
     */
    public function test_parse_endStringMatch($pattern, $parse, ZoneOffset $expected)
    {
        $pos = new ParsePosition(5);
        $parsed = $this->getFormatterPattern($pattern, "Z")->parseUnresolved("OTHER" . $parse, $pos);
        $this->assertEquals($pos->getIndex(), strlen($parse) + 5);
        $this->assertParsed($parsed, $expected);
    }

    /**
     * @dataProvider provider_offsets
     */
    public function test_parse_exactMatch_EmptyUTC($pattern, $parse, ZoneOffset $expected)
    {
        $pos = new ParsePosition(0);
        $parsed = $this->getFormatterPattern($pattern, "")->parseUnresolved($parse, $pos);
        $this->assertEquals($pos->getIndex(), strlen($parse));
        $this->assertParsed($parsed, $expected);
    }

    /**
     * @dataProvider provider_offsets
     */
    public function test_parse_startStringMatch_EmptyUTC($pattern, $parse, ZoneOffset $expected)
    {
        $pos = new ParsePosition(0);
        $parsed = $this->getFormatterPattern($pattern, "")->parseUnresolved($parse . ":OTHER", $pos);
        $this->assertEquals($pos->getIndex(), strlen($parse));
        $this->assertParsed($parsed, $expected);
    }

    /**
     * @dataProvider provider_offsets
     */
    public function test_parse_midStringMatch_EmptyUTC($pattern, $parse, ZoneOffset $expected)
    {
        $pos = new ParsePosition(5);
        $parsed = $this->getFormatterPattern($pattern, "")->parseUnresolved("OTHER" . $parse . ":OTHER", $pos);
        $this->assertEquals($pos->getIndex(), strlen($parse) + 5);
        $this->assertParsed($parsed, $expected);
    }

    /**
     * @dataProvider provider_offsets
     */
    public function test_parse_endStringMatch_EmptyUTC($pattern, $parse, ZoneOffset $expected)
    {
        $pos = new ParsePosition(5);
        $parsed = $this->getFormatterPattern($pattern, "")->parseUnresolved("OTHER" . $parse, $pos);
        $this->assertEquals($pos->getIndex(), strlen($parse) + 5);
        $this->assertParsed($parsed, $expected);
    }

//-----------------------------------------------------------------------
    public function provider_bigOffsets()
    {
        return
            [
                [
                    "+HH", "+59", 59 * 3600
                ],
                [
                    "+HH", "-19", -(19 * 3600)],

                [
                    "+HHMM", "+1801", 18 * 3600 + 1 * 60],
                [
                    "+HHMM", "-1801", -(18 * 3600 + 1 * 60)],

                [
                    "+HH:MM", "+18:01", 18 * 3600 + 1 * 60],
                [
                    "+HH:MM", "-18:01", -(18 * 3600 + 1 * 60)],

                [
                    "+HHMMss", "+180103", 18 * 3600 + 1 * 60 + 3],
                [
                    "+HHMMss", "-180103", -(18 * 3600 + 1 * 60 + 3)],

                [
                    "+HH:MM:ss", "+18:01:03", 18 * 3600 + 1 * 60 + 3],
                [
                    "+HH:MM:ss", "-18:01:03", -(18 * 3600 + 1 * 60 + 3)],

                [
                    "+HHMMSS", "+180103", 18 * 3600 + 1 * 60 + 3],
                [
                    "+HHMMSS", "-180103", -(18 * 3600 + 1 * 60 + 3)],

                [
                    "+HH:MM:SS", "+18:01:03", 18 * 3600 + 1 * 60 + 3],
                [
                    "+HH:MM:SS", "-18:01:03", -(18 * 3600 + 1 * 60 + 3)],
            ];
    }

    /**
     * @dataProvider provider_bigOffsets
     */
    public function test_parse_bigOffsets($pattern, $parse, $offsetSecs)
    {
        $pos = new ParsePosition(0);
        $parsed = $this->getFormatterPattern($pattern, "")->parseUnresolved($parse, $pos);
        $this->assertEquals($pos->getIndex(), strlen($parse));
        $this->assertEquals($parsed->getLong(ChronoField::OFFSET_SECONDS()), $offsetSecs);
    }

//-----------------------------------------------------------------------
    public function provider_badOffsets()
    {
        return [
            [
                "+HH", "+1", 0],
            [
                "+HH", "-1", 0],
            [
                "+HH", "01", 0],
            [
                "+HH", "01", 0],
            [
                "+HH", "+AA", 0],

            [
                "+HHMM", "+1", 0],
            [
                "+HHMM", "+01", 0],
            [
                "+HHMM", "+001", 0],
            [
                "+HHMM", "0102", 0],
            [
                "+HHMM", "+01:02", 0],
            [
                "+HHMM", "+AAAA", 0],

            [
                "+HH:MM", "+1", 0],
            [
                "+HH:MM", "+01", 0],
            [
                "+HH:MM", "+0:01", 0],
            [
                "+HH:MM", "+00:1", 0],
            [
                "+HH:MM", "+0:1", 0],
            [
                "+HH:MM", "+:", 0],
            [
                "+HH:MM", "01:02", 0],
            [
                "+HH:MM", "+0102", 0],
            [
                "+HH:MM", "+AA:AA", 0],

            [
                "+HHMMss", "+1", 0],
            [
                "+HHMMss", "+01", 0],
            [
                "+HHMMss", "+001", 0],
            [
                "+HHMMss", "0102", 0],
            [
                "+HHMMss", "+01:02", 0],
            [
                "+HHMMss", "+AAAA", 0],

            [
                "+HH:MM:ss", "+1", 0],
            [
                "+HH:MM:ss", "+01", 0],
            [
                "+HH:MM:ss", "+0:01", 0],
            [
                "+HH:MM:ss", "+00:1", 0],
            [
                "+HH:MM:ss", "+0:1", 0],
            [
                "+HH:MM:ss", "+:", 0],
            [
                "+HH:MM:ss", "01:02", 0],
            [
                "+HH:MM:ss", "+0102", 0],
            [
                "+HH:MM:ss", "+AA:AA", 0],

            [
                "+HHMMSS", "+1", 0],
            [
                "+HHMMSS", "+01", 0],
            [
                "+HHMMSS", "+001", 0],
            [
                "+HHMMSS", "0102", 0],
            [
                "+HHMMSS", "+01:02", 0],
            [
                "+HHMMSS", "+AAAA", 0],

            [
                "+HH:MM:SS", "+1", 0],
            [
                "+HH:MM:SS", "+01", 0],
            [
                "+HH:MM:SS", "+0:01", 0],
            [
                "+HH:MM:SS", "+00:1", 0],
            [
                "+HH:MM:SS", "+0:1", 0],
            [
                "+HH:MM:SS", "+:", 0],
            [
                "+HH:MM:SS", "01:02", 0],
            [
                "+HH:MM:SS", "+0102", 0],
            [
                "+HH:MM:SS", "+AA:AA", 0],
        ];
    }

    /**
     * @dataProvider provider_badOffsets
     */
    public function test_parse_invalid($pattern, $parse, $expectedPosition)
    {
        $pos = new ParsePosition(0);
        $parsed = $this->getFormatterPattern($pattern, "Z")->parseUnresolved($parse, $pos);
        $this->assertEquals($pos->getErrorIndex(), $expectedPosition);
        $this->assertEquals($parsed, null);
    }

//-----------------------------------------------------------------------
//-----------------------------------------------------------------------
//-----------------------------------------------------------------------
    public function test_parse_caseSensitiveUTC_matchedCase()
    {
        $this->setCaseSensitive(true);
        $pos = new ParsePosition(0);
        $parsed = $this->getFormatterPattern("+HH:MM:ss", "Z")->parseUnresolved("Z", $pos);
        $this->assertEquals($pos->getIndex(), 1);
        $this->assertParsed($parsed, ZoneOffset::UTC());
    }

    public function test_parse_caseSensitiveUTC_unmatchedCase()
    {
        $this->setCaseSensitive(true);
        $pos = new ParsePosition(0);
        $parsed = $this->getFormatterPattern("+HH:MM:ss", "Z")->parseUnresolved("z", $pos);
        $this->assertEquals($pos->getErrorIndex(), 0);
        $this->assertEquals($parsed, null);
    }

    public function test_parse_caseInsensitiveUTC_matchedCase()
    {
        $this->setCaseSensitive(false);
        $pos = new ParsePosition(0);
        $parsed = $this->getFormatterPattern("+HH:MM:ss", "Z")->parseUnresolved("Z", $pos);
        $this->assertEquals($pos->getIndex(), 1);
        $this->assertParsed($parsed, ZoneOffset::UTC());
    }

    public function test_parse_caseInsensitiveUTC_unmatchedCase()
    {
        $this->setCaseSensitive(false);
        $pos = new ParsePosition(0);
        $parsed = $this->getFormatterPattern("+HH:MM:ss", "Z")->parseUnresolved("z", $pos);
        $this->assertEquals($pos->getIndex(), 1);
        $this->assertParsed($parsed, ZoneOffset::UTC());
    }

    private function assertParsed(TemporalAccessor $parsed, ZoneOffset $expectedOffset)
    {
        if ($expectedOffset === null) {
            $this->assertEquals(null, $parsed);
        } else {
            $this->assertEquals(true, $parsed->isSupported(ChronoField::OFFSET_SECONDS()), true);
            $this->assertEquals($expectedOffset->getTotalSeconds(), $parsed->getLong(ChronoField::OFFSET_SECONDS()));
        }
    }

}
