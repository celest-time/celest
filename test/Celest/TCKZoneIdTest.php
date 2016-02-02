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
namespace Celest;

use Celest\Format\TextStyle;
use Celest\Temporal\TemporalAccessor;
use Celest\Temporal\TemporalAccessorDefaults;
use Celest\Temporal\TemporalField;
use Celest\Temporal\TemporalQueries;
use Celest\Temporal\TemporalQuery;
use Celest\Zone\ZoneRulesException;

class MockTemporalAccessor implements TemporalAccessor
{
    public function isSupported(TemporalField $field)
    {
        return false;
    }

    public
    function getLong(TemporalField $field)
    {
        throw new DateTimeException("Mock");
    }

    public function query(TemporalQuery $query)
    {
        if ($query == TemporalQueries::zoneId()) {
            return ZoneId::of("Europe/Paris");
        }

        return TemporalAccessorDefaults::query($this, $query);
    }

    public function range(TemporalField $field)
    {
    }

    public function get(TemporalField $field)
    {
    }

    public function __toString()
    {
        return '';
    }
}

;

class TCKZoneIdTest extends \PHPUnit_Framework_TestCase
{

    //-----------------------------------------------------------------------
    // getAvailableZoneIds()
    //-----------------------------------------------------------------------
    public function test_getAvailableGroupIds()
    {
        $zoneIds = ZoneId::getAvailableZoneIds();
        $this->assertContains("Europe/London", $zoneIds);
    }

    //-----------------------------------------------------------------------
    // mapped factory
    //-----------------------------------------------------------------------
    public function test_of_string_Map()
    {
        $this->markTestSkipped('TBD');
        $map = [
            "LONDON" => "Europe/London",
            "PARIS" => "Europe/Paris"
        ];
        $test = ZoneId::of("LONDON", $map);
        $this->assertEquals($test->getId(), "Europe/London");
    }

    public function test_of_string_Map_lookThrough()
    {
        $this->markTestSkipped('TBD');
        $map = [
            "LONDON" => "Europe/London",
            "PARIS" => "Europe/Paris"
        ];
        $test = ZoneId::of("Europe/Madrid", $map);
        $this->assertEquals($test->getId(), "Europe/Madrid");
    }

    public function test_of_string_Map_emptyMap()
    {
        $this->markTestSkipped('TBD');
        $map = [];
        $test = ZoneId::of("Europe/Madrid", $map);
        $this->assertEquals($test->getId(), "Europe/Madrid");
    }

    /**
     * @expectedException \Celest\DateTimeException
     */
    public function test_of_string_Map_badFormat()
    {
        $this->markTestSkipped('TBD');
        $map = [];
        ZoneId::of("Not known", $map);
    }

    /**
     * @expectedException \Celest\Zone\ZoneRulesException
     */
    public function test_of_string_Map_unknown()
    {
        $this->markTestSkipped('TBD');
        $map = [];
        ZoneId::of("Unknown", $map);
    }

    //-----------------------------------------------------------------------
    // regular factory and .normalized()
    //-----------------------------------------------------------------------
    public function data_offsetBasedValid()
    {
        return
            [
                [
                    "Z", "Z"],
                [
                    "+0", "Z"],
                [
                    "-0", "Z"],
                [
                    "+00", "Z"],
                [
                    "+0000", "Z"],
                [
                    "+00:00", "Z"],
                [
                    "+000000", "Z"],
                [
                    "+00:00:00", "Z"],
                [
                    "-00", "Z"],
                [
                    "-0000", "Z"],
                [
                    "-00:00", "Z"],
                [
                    "-000000", "Z"],
                [
                    "-00:00:00", "Z"],
                [
                    "+5", "+05:00"],
                [
                    "+01", "+01:00"],
                [
                    "+0100", "+01:00"],
                [
                    "+01:00", "+01:00"],
                [
                    "+010000", "+01:00"],
                [
                    "+01:00:00", "+01:00"],
                [
                    "+12", "+12:00"],
                [
                    "+1234", "+12:34"],
                [
                    "+12:34", "+12:34"],
                [
                    "+123456", "+12:34:56"],
                [
                    "+12:34:56", "+12:34:56"],
                [
                    "-02", "-02:00"],
                [
                    "-5", "-05:00"],
                [
                    "-0200", "-02:00"],
                [
                    "-02:00", "-02:00"],
                [
                    "-020000", "-02:00"],
                [
                    "-02:00:00", "-02:00"],
            ];
    }

    /**
     * @dataProvider data_offsetBasedValid
     */
    public function test_factory_of_String_offsetBasedValid_noPrefix($input, $id)
    {
        $test = ZoneId::of($input);
        $this->assertEquals($test->getId(), $id);
        $this->assertEquals($test, ZoneOffset::of($id));
        $this->assertEquals($test->normalized(), ZoneOffset::of($id));
        $this->assertEquals($test->getDisplayName(TextStyle::FULL(), Locale::UK()), $id);
        $this->assertEquals($test->getRules()->isFixedOffset(), true);
        $this->assertEquals($test->getRules()->getOffset(Instant::EPOCH()), ZoneOffset::of($id));
    }

//-----------------------------------------------------------------------
    public function data_offsetBasedValidPrefix()
    {
        return
            [
                [
                    "", "", "Z"],
                [
                    "+0", "", "Z"],
                [
                    "-0", "", "Z"],
                [
                    "+00", "", "Z"],
                [
                    "+0000", "", "Z"],
                [
                    "+00:00", "", "Z"],
                [
                    "+000000", "", "Z"],
                [
                    "+00:00:00", "", "Z"],
                [
                    "-00", "", "Z"],
                [
                    "-0000", "", "Z"],
                [
                    "-00:00", "", "Z"],
                [
                    "-000000", "", "Z"],
                [
                    "-00:00:00", "", "Z"],
                [
                    "+5", "+05:00", "+05:00"],
                [
                    "+01", "+01:00", "+01:00"],
                [
                    "+0100", "+01:00", "+01:00"],
                [
                    "+01:00", "+01:00", "+01:00"],
                [
                    "+010000", "+01:00", "+01:00"],
                [
                    "+01:00:00", "+01:00", "+01:00"],
                [
                    "+12", "+12:00", "+12:00"],
                [
                    "+1234", "+12:34", "+12:34"],
                [
                    "+12:34", "+12:34", "+12:34"],
                [
                    "+123456", "+12:34:56", "+12:34:56"],
                [
                    "+12:34:56", "+12:34:56", "+12:34:56"],
                [
                    "-02", "-02:00", "-02:00"],
                [
                    "-5", "-05:00", "-05:00"],
                [
                    "-0200", "-02:00", "-02:00"],
                [
                    "-02:00", "-02:00", "-02:00"],
                [
                    "-020000", "-02:00", "-02:00"],
                [
                    "-02:00:00", "-02:00", "-02:00"],
            ];
    }

    /**
     * @dataProvider data_offsetBasedValidPrefix
     */
    public function test_factory_of_String_offsetBasedValid_prefixUTC($input, $id, $offsetId)
    {
        $test = ZoneId::of("UTC" . $input);
        $this->assertEquals($test->getId(), "UTC" . $id);
        $this->assertEquals($test->getRules(), ZoneOffset::of($offsetId)->getRules());
        $this->assertEquals($test->normalized(), ZoneOffset::of($offsetId));
        // TODO
        //$this->assertEquals($test->getDisplayName(TextStyle::FULL(), Locale::UK()), $this->displayName("UTC" . $id));
        $this->assertEquals($test->getRules()->isFixedOffset(), true);
        $this->assertEquals($test->getRules()->getOffset(Instant::EPOCH()), ZoneOffset::of($offsetId));
    }

    /**
     * @dataProvider data_offsetBasedValidPrefix
     */
    public function test_factory_of_String_offsetBasedValid_prefixGMT($input, $id, $offsetId)
    {
        $test = ZoneId::of("GMT" . $input);
        $this->assertEquals($test->getId(), "GMT" . $id);
        $this->assertEquals($test->getRules(), ZoneOffset::of($offsetId)->getRules());
        $this->assertEquals($test->normalized(), ZoneOffset::of($offsetId));
        // TODO
        //$this->assertEquals($test->getDisplayName(TextStyle::FULL(), Locale::UK()), $this->displayName("GMT" . $id));
        $this->assertEquals($test->getRules()->isFixedOffset(), true);
        $this->assertEquals($test->getRules()->getOffset(Instant::EPOCH()), ZoneOffset::of($offsetId));
    }

    /**
     * @dataProvider data_offsetBasedValidPrefix
     */
    public
    function test_factory_of_String_offsetBasedValid_prefixUT($input, $id, $offsetId)
    {
        $test = ZoneId::of("UT" . $input);
        $this->assertEquals($test->getId(), "UT" . $id);
        $this->assertEquals($test->getRules(), ZoneOffset::of($offsetId)->getRules());
        $this->assertEquals($test->normalized(), ZoneOffset::of($offsetId));
        // TODO
        //$this->assertEquals($test->getDisplayName(TextStyle::FULL(), Locale::UK()), $this->displayName("UT" . $id));
        $this->assertEquals($test->getRules()->isFixedOffset(), true);
        $this->assertEquals($test->getRules()->getOffset(Instant::EPOCH()), ZoneOffset::of($offsetId));
    }

    private function displayName($id)
    {
        if ($id === "GMT") {
            return "Greenwich Mean Time";
        }
        if ($id === "GMT0") {
            return "Greenwich Mean Time";
        }
        if ($id === "UTC") {
            return "Coordinated Universal Time";
        }
        return $id;
    }

    //-----------------------------------------------------------------------

    public function data_prefixValid()
    {
        return [
            [
                "GMT", "+01:00"],
            [
                "UTC", "+01:00"],
            [
                "UT", "+01:00"],
            [
                "", "+01:00"],
        ];
    }

    /**
     * @dataProvider data_prefixvalid
     */
    public function test_prefixOfOffset($prefix, $offset)
    {
        $zoff = ZoneOffset::of($offset);
        $zoneId = ZoneId::ofOffset($prefix, $zoff);
        $this->assertEquals($zoneId->getId(), $prefix . $zoff->getId(), "in correct id for : " . $prefix . ", zoff: " . $zoff);

    }

//-----------------------------------------------------------------------
    public function data_prefixInvalid()
    {
        return [
            [
                "GM", "+01:00"],
            [
                "U", "+01:00"],
            [
                "UTC0", "+01:00"],
            [
                "A", "+01:00"],
        ];
    }

    /**
     * @dataProvider data_prefixInvalid
     * @expectedException \Celest\IllegalArgumentException
     */
    public function test_invalidPrefixOfOffset($prefix, $offset)
    {
        $zoff = ZoneOffset::of($offset);
        $zoneId = ZoneId::ofOffset($prefix, $zoff);
        $this->fail("should have thrown an exception for prefix: " . $prefix);
    }


    public
    function test_nullPrefixOfOffset()
    {
        TestHelper::assertNullException($this, function () {
            ZoneId::ofOffset(null, ZoneOffset::ofTotalSeconds(1));
        });
    }

    public
    function test_nullOffsetOfOffset()
    {
        TestHelper::assertNullException($this, function () {
            ZoneId::ofOffset("GMT", null);
        });
    }

//-----------------------------------------------------------------------
    public
    function data_offsetBasedValidOther()
    {
        return [
            [
                "GMT", "Z"],
            [
                "GMT0", "Z"],
            [
                "UCT", "Z"],
            [
                "Greenwich", "Z"],
            [
                "Universal", "Z"],
            [
                "Zulu", "Z"],
            [
                "Etc/GMT", "Z"],
            [
                "Etc/GMT+0", "Z"],
            [
                "Etc/GMT+1", "-01:00"],
            [
                "Etc/GMT-1", "+01:00"],
            [
                "Etc/GMT+9", "-09:00"],
            [
                "Etc/GMT-9", "+09:00"],
            [
                "Etc/GMT0", "Z"],
            [
                "Etc/UCT", "Z"],
            [
                "Etc/UTC", "Z"],
            [
                "Etc/Greenwich", "Z"],
            [
                "Etc/Universal", "Z"],
            [
                "Etc/Zulu", "Z"],
        ];
    }

    /**
     * @dataProvider data_offsetBasedValidOther
     */
    public function test_factory_of_String_offsetBasedValidOther($input, $offsetId)
    {
        $test = ZoneId::of($input);
        $this->assertEquals($test->getId(), $input);
        $this->assertEquals($test->getRules(), ZoneOffset::of($offsetId)->getRules());
        $this->assertEquals($test->normalized(), ZoneOffset::of($offsetId));
    }

//-----------------------------------------------------------------------
    public function data_offsetBasedInvalid()
    {
        return
            [
                [
                    "A"
                ], [
                "B"], [
                "C"], [
                "D"], [
                "E"], [
                "F"], [
                "G"], [
                "H"], [
                "I"], [
                "J"], [
                "K"], [
                "L"], [
                "M"],
                [
                    "N"], [
                "O"], [
                "P"], [
                "Q"], [
                "R"], [
                "S"], [
                "T"], [
                "U"], [
                "V"], [
                "W"], [
                "X"], [
                "Y"], [
                "Z"],
                [
                    "+0:00"], [
                "+00:0"], [
                "+0:0"],
                [
                    "+000"], [
                "+00000"],
                [
                    "+0:00:00"], [
                "+00:0:00"], [
                "+00:00:0"], [
                "+0:0:0"], [
                "+0:0:00"], [
                "+00:0:0"], [
                "+0:00:0"],
                [
                    "+01_00"], [
                "+01;00"], [
                "+01@00"], [
                "+01:AA"],
                [
                    "+19"], [
                "+19:00"], [
                "+18:01"], [
                "+18:00:01"], [
                "+1801"], [
                "+180001"],
                [
                    "-0:00"], [
                "-00:0"], [
                "-0:0"],
                [
                    "-000"], [
                "-00000"],
                [
                    "-0:00:00"], [
                "-00:0:00"], [
                "-00:00:0"], [
                "-0:0:0"], [
                "-0:0:00"], [
                "-00:0:0"], [
                "-0:00:0"],
                [
                    "-19"], [
                "-19:00"], [
                "-18:01"], [
                "-18:00:01"], [
                "-1801"], [
                "-180001"],
                [
                    "-01_00"], [
                "-01;00"], [
                "-01@00"], [
                "-01:AA"],
                [
                    "@01:00"],
                [
                    "0"],
                [
                    "UT0"],
                [
                    "UTZ"],
                [
                    "UTC0"],
                [
                    "UTCZ"],
                [
                    "GMTZ"],  // GMT0 is valid in ZoneRulesProvider
            ];
    }

    /**
     * @dataProvider data_offsetBasedInvalid
     * @expectedException \Celest\DateTimeException
     */
    public function test_factory_of_String_offsetBasedInvalid_noPrefix($id)
    {
        if ($id === "Z") {
            throw new DateTimeException("Fake exception: Z alone is valid, not invalid");
        }

        ZoneId::of($id);
    }

    /**
     * @dataProvider data_offsetBasedInvalid
     * @expectedException \Celest\DateTimeException
     */
    public function test_factory_of_String_offsetBasedInvalid_prefixUTC($id)
    {
        ZoneId::of("UTC" . $id);
    }

    /**
     * @dataProvider data_offsetBasedInvalid
     * @expectedException \Celest\DateTimeException
     */
    public function test_factory_of_String_offsetBasedInvalid_prefixGMT($id)
    {
        if ($id === "0") {
            throw new DateTimeException("Fake exception: GMT0 is valid, not invalid");
        }

        ZoneId::of("GMT" . $id);
    }

    /**
     * @dataProvider data_offsetBasedInvalid
     * @expectedException \Celest\DateTimeException
     */
    public function test_factory_of_String_offsetBasedInvalid_prefixUT($id)
    {
        if ($id === "C") {
            throw new DateTimeException("Fake exception: UT + C = UTC, thus it is valid, not invalid");
        }
        ZoneId::of("UT" . $id);
    }

    //-----------------------------------------------------------------------
    public function data_regionBasedInvalid()
    {
        // \u00ef is a random unicode character
        return [
            [
                ""], [
                ":"], [
                "#"],
            [
                "\u00ef"], [
                "`"], [
                "!"], [
                "\""], [
                "\u00ef"], [
                "$"], [
                "^"], [
                "&"], [
                "*"], [
                "("], [
                ")"], [
                "="],
            [
                "\\"], [
                "|"], [
                ","], [
                "<"], [
                ">"], [
                "?"], [
                ";"], [
                "'"], [
                "["], [
                "]"], [
                "["], [
                "]"],
            [
                "\u00ef:A"], [
                "`:A"], [
                "!:A"], [
                "\":A"], [
                "\u00ef:A"], [
                "$:A"], [
                "^:A"], [
                "&:A"], [
                "*:A"], [
                "(:A"], [
                "):A"], [
                "=:A"], [
                "+:A"],
            [
                "\\:A"], [
                "|:A"], [
                ",:A"], [
                "<:A"], [
                ">:A"], [
                "?:A"], [
                ";:A"], [
                "::A"], [
                "':A"], [
                "@:A"], [
                "~:A"], [
                "[:A"], [
                "]:A"], [
                "[:A"], [
                "]:A"],
            [
                "A:B#\u00ef"], [
                "A:B#`"], [
                "A:B#!"], [
                "A:B#\""], [
                "A:B#\u00ef"], [
                "A:B#$"], [
                "A:B#^"], [
                "A:B#&"], [
                "A:B#*"],
            [
                "A:B#("], [
                "A:B#)"], [
                "A:B#="], [
                "A:B#+"],
            [
                "A:B#\\"], [
                "A:B#|"], [
                "A:B#,"], [
                "A:B#<"], [
                "A:B#>"], [
                "A:B#?"], [
                "A:B#;"], [
                "A:B#:"],
            [
                "A:B#'"], [
                "A:B#@"], [
                "A:B#~"], [
                "A:B#["], [
                "A:B#]"], [
                "A:B#["], [
                "A:B#]"],
        ];
    }

    /**
     * @dataProvider data_regionBasedInvalid
     * @expectedException \Celest\DateTimeException
     */
    public function test_factory_of_String_regionBasedInvalid($id)
    {
        ZoneId::of($id);
    }

//-----------------------------------------------------------------------
    public function test_factory_of_String_region_EuropeLondon()
    {
        $test = ZoneId::of("Europe/London");
        $this->assertEquals($test->getId(), "Europe/London");
        $this->assertEquals($test->getRules()->isFixedOffset(), false);
        $this->assertEquals($test->normalized(), $test);
    }

//-----------------------------------------------------------------------
    public function test_factory_of_String_null()
    {
        TestHelper::assertNullException($this, function () {
            ZoneId::of(null);
        });
    }

    /**
     * @expectedException \Celest\DateTimeException
     */
    public function factory_of_String_badFormat()
    {
        ZoneId::of("Unknown rule");
    }

    /**
     * @expectedException \Celest\Zone\ZoneRulesException
     */
    public function test_factory_of_String_unknown()
    {
        ZoneId::of("Unknown");
    }

//-----------------------------------------------------------------------
// from(TemporalAccessor)
//-----------------------------------------------------------------------
    public function test_factory_from_TemporalAccessor_zoneId()
    {
        $mock = new MockTemporalAccessor();
        $this->assertEquals(ZoneId::from($mock), ZoneId::of("Europe/Paris"));
    }

    public function test_factory_from_TemporalAccessor_offset()
    {
        $offset = ZoneOffset::ofHours(1);
        $this->assertEquals(ZoneId::from($offset), $offset);
    }

    /**
     * @expectedException \Celest\DateTimeException
     */
    public function test_factory_from_TemporalAccessor_invalid_noDerive()
    {
        ZoneId::from(LocalTime::of(12, 30));
    }

    public function test_factory_from_TemporalAccessor_null()
    {
        TestHelper::assertNullException($this, function () {
            ZoneId::from(null);
        });
    }

    //-----------------------------------------------------------------------
    // equals() / hashCode()
    //-----------------------------------------------------------------------
    public function test_equals()
    {
        $test1 = ZoneId::of("Europe/London");
        $test2 = ZoneId::of("Europe/Paris");
        $test2b = ZoneId::of("Europe/Paris");
        $this->assertEquals($test1->equals($test2), false);
        $this->assertEquals($test2->equals($test1), false);

        $this->assertEquals($test1->equals($test1), true);
        $this->assertEquals($test2->equals($test2), true);
        $this->assertEquals($test2->equals($test2b), true);
    }

    public function test_equals_null()
    {
        $this->assertEquals(ZoneId::of("Europe/London")->equals(null), false);
    }

    public function test_equals_notEqualWrongType()
    {
        $this->assertEquals(ZoneId::of("Europe/London")->equals("Europe/London"), false);
    }

    //-----------------------------------------------------------------------
    // toString()
    //-----------------------------------------------------------------------
    public function data_toString()
    {
        return [
            [
                "Europe/London", "Europe/London"],
            [
                "Europe/Paris", "Europe/Paris"],
            [
                "Europe/Berlin", "Europe/Berlin"],
            [
                "Z", "Z"],
            [
                "+01:00", "+01:00"],
            [
                "UTC", "UTC"],
            [
                "UTC+01:00", "UTC+01:00"],
        ];
    }

    /**
     * @dataProvider data_toString
     */
    public function test_toString($id, $expected)
    {
        $test = ZoneId::of($id);
        $this->assertEquals($test->__toString(), $expected);
    }

}
