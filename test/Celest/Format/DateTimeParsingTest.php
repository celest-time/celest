<?php
/*
 * Copyright (c) 2014, Oracle and/or its affiliates. All rights reserved.
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
 * Copyright (c) 2014, Stephen Colebourne & Michael Nascimento Santos
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

use Celest\Instant;
use Celest\LocalDateTime;
use Celest\Temporal\ChronoField as CF;
use Celest\ZonedDateTime;
use Celest\ZoneId;
use Celest\ZoneOffset;
use PHPUnit\Framework\TestCase;

/**
 * Test parsing of edge cases.
 */
class DateTimeParsingTest extends TestCase
{

    private static function PARIS()
    {
        return ZoneId::of("Europe/Paris");
    }

    private static function OFFSET_0230()
    {
        return ZoneOffset::ofHoursMinutes(2, 30);
    }

    private static function LOCALFIELDS()
    {
        return (new DateTimeFormatterBuilder())->appendPattern("yyyy-MM-dd HH:mm:ss")->toFormatter();
    }

    private static function LOCALFIELDS_ZONEID()
    {
        return (new DateTimeFormatterBuilder())->appendPattern("yyyy-MM-dd HH:mm:ss ")->appendZoneId()->toFormatter();
    }

    private static function LOCALFIELDS_OFFSETID()
    {
        return (new DateTimeFormatterBuilder())->appendPattern("yyyy-MM-dd HH:mm:ss ")->appendOffsetId()->toFormatter();
    }

    private static function LOCALFIELDS_WITH_PARIS()
    {
        return self::LOCALFIELDS()->withZone(self::PARIS());
    }

    private static function LOCALFIELDS_WITH_0230()
    {
        return self::LOCALFIELDS()->withZone(self::OFFSET_0230());
    }

    private static function INSTANT()
    {
        return (new DateTimeFormatterBuilder())->appendInstant()->toFormatter();
    }

    private static function INSTANT_WITH_PARIS()
    {
        return self::INSTANT()->withZone(self::PARIS());
    }

    private static function INSTANT_WITH_0230()
    {
        return self::INSTANT()->withZone(self::OFFSET_0230());
    }

    private static function INSTANT_OFFSETID()
    {
        return (new DateTimeFormatterBuilder())->appendInstant()->appendLiteral(' ')->appendOffsetId()->toFormatter();
    }

    private static function INSTANT_OFFSETSECONDS()
    {
        return (new DateTimeFormatterBuilder())->appendInstant()->appendLiteral(' ')->appendValue(CF::OFFSET_SECONDS())->toFormatter();
    }

    private static function INSTANTSECONDS()
    {
        return (new DateTimeFormatterBuilder())->appendValue(CF::INSTANT_SECONDS())->toFormatter();
    }

    private static function INSTANTSECONDS_WITH_PARIS()
    {
        return self::INSTANTSECONDS()->withZone(self::PARIS());
    }

    private static function INSTANTSECONDS_NOS()
    {
        return (new DateTimeFormatterBuilder())->appendValue(CF::INSTANT_SECONDS())->appendLiteral('.')->appendValue(CF::NANO_OF_SECOND())->toFormatter();
    }

    private static function INSTANTSECONDS_NOS_WITH_PARIS()
    {
        return self::INSTANTSECONDS_NOS()->withZone(self::PARIS());
    }

    private static function INSTANTSECONDS_OFFSETSECONDS()
    {
        return (new DateTimeFormatterBuilder())->appendValue(CF::INSTANT_SECONDS())->appendLiteral(' ')->appendValue(CF::OFFSET_SECONDS())->toFormatter();
    }

    function data_instantZones()
    {
        return [
            [self::LOCALFIELDS_ZONEID(), "2014-06-30 01:02:03 Europe/Paris", ZonedDateTime::of(2014, 6, 30, 1, 2, 3, 0, self::PARIS())],
            [self::LOCALFIELDS_ZONEID(), "2014-06-30 01:02:03 +02:30", ZonedDateTime::of(2014, 6, 30, 1, 2, 3, 0, self::OFFSET_0230())],
            [self::LOCALFIELDS_OFFSETID(), "2014-06-30 01:02:03 +02:30", ZonedDateTime::of(2014, 6, 30, 1, 2, 3, 0, self::OFFSET_0230())],
            [self::LOCALFIELDS_WITH_PARIS(), "2014-06-30 01:02:03", ZonedDateTime::of(2014, 6, 30, 1, 2, 3, 0, self::PARIS())],
            [self::LOCALFIELDS_WITH_0230(), "2014-06-30 01:02:03", ZonedDateTime::of(2014, 6, 30, 1, 2, 3, 0, self::OFFSET_0230())],
            [self::INSTANT_WITH_PARIS(), "2014-06-30T01:02:03Z", ZonedDateTime::of(2014, 6, 30, 1, 2, 3, 0, ZoneOffset::UTC())->withZoneSameInstant(self::PARIS())],
            [self::INSTANT_WITH_0230(), "2014-06-30T01:02:03Z", ZonedDateTime::of(2014, 6, 30, 1, 2, 3, 0, ZoneOffset::UTC())->withZoneSameInstant(self::OFFSET_0230())],
            [self::INSTANT_OFFSETID(), "2014-06-30T01:02:03Z +02:30", ZonedDateTime::of(2014, 6, 30, 1, 2, 3, 0, ZoneOffset::UTC())->withZoneSameInstant(self::OFFSET_0230())],
            [self::INSTANT_OFFSETSECONDS(), "2014-06-30T01:02:03Z 9000", ZonedDateTime::of(2014, 6, 30, 1, 2, 3, 0, ZoneOffset::UTC())->withZoneSameInstant(self::OFFSET_0230())],
            [self::INSTANTSECONDS_WITH_PARIS(), "86402", Instant::ofEpochSecond(86402)->atZone(self::PARIS())],
            [self::INSTANTSECONDS_NOS_WITH_PARIS(), "86402.123456789", Instant::ofEpochSecond(86402, 123456789)->atZone(self::PARIS())],
            [self::INSTANTSECONDS_OFFSETSECONDS(), "86402 9000", Instant::ofEpochSecond(86402)->atZone(self::OFFSET_0230())],
        ];
    }

    /**
     * @dataProvider data_instantZones
     */
    public function test_parse_instantZones_ZDT(DateTimeFormatter $formatter, $text, ZonedDateTime $expected)
    {
        $actual = $formatter->parse($text);
        $this->assertEquals(ZonedDateTime::from($actual), $expected);
    }

    /**
     * @dataProvider data_instantZones
     */
    public function test_parse_instantZones_LDT(DateTimeFormatter $formatter, $text, ZonedDateTime $expected)
    {
        $actual = $formatter->parse($text);
        $this->assertEquals(LocalDateTime::from($actual), $expected->toLocalDateTime());
    }

    /**
     * @dataProvider data_instantZones
     */
    public function test_parse_instantZones_Instant(DateTimeFormatter $formatter, $text, ZonedDateTime $expected)
    {
        $actual = $formatter->parse($text);
        $this->assertEquals(Instant::from($actual), $expected->toInstant());
    }

    /**
     * @dataProvider data_instantZones
     */
    public function test_parse_instantZones_supported(DateTimeFormatter $formatter, $text, ZonedDateTime $expected)
    {
        $actual = $formatter->parse($text);
        $this->assertEquals($actual->isSupported(CF::INSTANT_SECONDS()), true);
        $this->assertEquals($actual->isSupported(CF::EPOCH_DAY()), true);
        $this->assertEquals($actual->isSupported(CF::SECOND_OF_DAY()), true);
        $this->assertEquals($actual->isSupported(CF::NANO_OF_SECOND()), true);
        $this->assertEquals($actual->isSupported(CF::MICRO_OF_SECOND()), true);
        $this->assertEquals($actual->isSupported(CF::MILLI_OF_SECOND()), true);
    }

    //-----------------------------------------------------------------------
    function data_instantNoZone()
    {
        return [
            [self::INSTANT(), "2014-06-30T01:02:03Z", ZonedDateTime::of(2014, 6, 30, 1, 2, 3, 0, ZoneOffset::UTC())->toInstant()],
            [self::INSTANTSECONDS(), "86402", Instant::ofEpochSecond(86402)],
            [self::INSTANTSECONDS_NOS(), "86402.123456789", Instant::ofEpochSecond(86402, 123456789)],
        ];
    }

    /**
     * @dataProvider data_instantNoZone
     * @expectedException \Celest\DateTimeException
     */
    public function test_parse_instantNoZone_ZDT(DateTimeFormatter $formatter, $text, Instant $expected)
    {
        $actual = $formatter->parse($text);
        ZonedDateTime::from($actual);
    }

    /**
     * @dataProvider data_instantNoZone
     * @expectedException \Celest\DateTimeException
     */
    public function test_parse_instantNoZone_LDT(DateTimeFormatter $formatter, $text, Instant $expected)
    {
        $actual = $formatter->parse($text);
        LocalDateTime::from($actual);
    }

    /**
     * @dataProvider data_instantNoZone
     */
    public function test_parse_instantNoZone_Instant(DateTimeFormatter $formatter, $text, Instant $expected)
    {
        $actual = $formatter->parse($text);
        $this->assertEquals(Instant::from($actual), $expected);
    }

    /**
     * @dataProvider data_instantNoZone
     */
    public function test_parse_instantNoZone_supported(DateTimeFormatter $formatter, $text, Instant $expected)
    {
        $actual = $formatter->parse($text);
        $this->assertEquals($actual->isSupported(CF::INSTANT_SECONDS()), true);
        $this->assertEquals($actual->isSupported(CF::EPOCH_DAY()), false);
        $this->assertEquals($actual->isSupported(CF::SECOND_OF_DAY()), false);
        $this->assertEquals($actual->isSupported(CF::NANO_OF_SECOND()), true);
        $this->assertEquals($actual->isSupported(CF::MICRO_OF_SECOND()), true);
        $this->assertEquals($actual->isSupported(CF::MILLI_OF_SECOND()), true);
    }

}
