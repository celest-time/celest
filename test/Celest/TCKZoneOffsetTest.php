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

use Celest\Helper\Math;
use Celest\Temporal\ChronoField;
use Celest\Temporal\JulianFields;
use Celest\Temporal\TemporalAccessor;
use Celest\Temporal\TemporalQueries;
use Celest\Temporal\TemporalQuery;

class TCKZoneOffsetTest extends AbstractDateTimeTest
{

    //-----------------------------------------------------------------------
    public function samples()
    {
        return $array = [ZoneOffset::ofHours(1), ZoneOffset::ofHoursMinutesSeconds(-5, -6, -30)];
    }

    public function validFields()
    {
        return [
            ChronoField::OFFSET_SECONDS()
        ];
    }

    protected function invalidFields()
    {
        $list = array_diff(ChronoField::values(), $this->validFields());
        $list[] = JulianFields::JULIAN_DAY();
        $list[] = JulianFields::MODIFIED_JULIAN_DAY();
        $list[] = JulianFields::RATA_DIE();
        return $list;
    }

//-----------------------------------------------------------------------
// constants
//-----------------------------------------------------------------------
    public function test_constant_UTC()
    {
        $test = ZoneOffset::UTC();
        $this->doTestOffset($test, 0, 0, 0);
    }

    public function test_constant_MIN()
    {
        $test = ZoneOffset::MIN();
        $this->doTestOffset($test, -18, 0, 0);
    }

    public function test_constant_MAX()
    {
        $test = ZoneOffset::MAX();
        $this->doTestOffset($test, 18, 0, 0);
    }

    //-----------------------------------------------------------------------
    // of(String)
    //-----------------------------------------------------------------------
    public function test_factory_string_UTC()
    {
        $values = [
            "Z", "+0",
            "+00", "+0000", "+00:00", "+000000", "+00:00:00",
            "-00", "-0000", "-00:00", "-000000", "-00:00:00",
        ];
        for ($i = 0; $i < count($values); $i++) {
            $test = ZoneOffset::of($values[$i]);
            $this->assertSame($test, ZoneOffset::UTC());
        }
    }

    public function test_factory_string_invalid()
    {
        $values = [
            "", "A", "B", "C", "D", "E", "F", "G", "H", "I", "J", "K", "L", "M",
            "N", "O", "P", "Q", "R", "S", "T", "U", "V", "W", "X", "Y", "ZZ",
            "0", "+0:00", "+00:0", "+0:0",
            "+000", "+00000",
            "+0:00:00", "+00:0:00", "+00:00:0", "+0:0:0", "+0:0:00", "+00:0:0", "+0:00:0",
            "1", "+01_00", "+01;00", "+01@00", "+01:AA",
            "+19", "+19:00", "+18:01", "+18:00:01", "+1801", "+180001",
            "-0:00", "-00:0", "-0:0",
            "-000", "-00000",
            "-0:00:00", "-00:0:00", "-00:00:0", "-0:0:0", "-0:0:00", "-00:0:0", "-0:00:0",
            "-19", "-19:00", "-18:01", "-18:00:01", "-1801", "-180001",
            "-01_00", "-01;00", "-01@00", "-01:AA",
            "@01:00",
        ];
        for ($i = 0; $i < count($values); $i++) {
            try {
                ZoneOffset::of($values[$i]);
                $this->fail("Should have failed:" . $values[$i]);
            } catch (DateTimeException $ex) {
                // expected
            }
        }
    }

    public function test_factory_string_null()
    {
        TestHelper::assertNullException($this, function () {
            ZoneOffset::of(null);
        });
    }

    //-----------------------------------------------------------------------
    public function test_factory_string_singleDigitHours()
    {
        for ($i = -9; $i <= 9;
             $i++) {
            $str = ($i < 0 ? "-" : "+") . Math::abs($i);
            $test = ZoneOffset::of($str);
            $this->doTestOffset($test, $i, 0, 0);
        }
    }

    public function test_factory_string_hours()
    {
        for ($i = -18; $i <= 18;
             $i++) {
            $str = ($i < 0 ? "-" : "+") . substr(Math::abs($i) + 100, 1);
            $test = ZoneOffset::of($str);
            $this->doTestOffset($test, $i, 0, 0);
        }
    }

    public function test_factory_string_hours_minutes_noColon()
    {
        for ($i = -17; $i <= 17;
             $i++) {
            for ($j = -59; $j <= 59;
                 $j++) {
                if (($i < 0 && $j <= 0) || ($i > 0 && $j >= 0) || $i == 0) {
                    $str = ($i < 0 || $j < 0 ? "-" : "+") .
                        substr(Math::abs($i) + 100, 1) .
                        substr(Math::abs($j) + 100, 1);
                    $test = ZoneOffset::of($str);
                    $this->doTestOffset($test, $i, $j, 0);
                }
            }
        }
        $test1 = ZoneOffset::of("-1800");
        $this->doTestOffset($test1, -18, 0, 0);
        $test2 = ZoneOffset::of("+1800");
        $this->doTestOffset($test2, 18, 0, 0);
    }

    public function test_factory_string_hours_minutes_colon()
    {
        for ($i = -17; $i <= 17;
             $i++) {
            for ($j = -59; $j <= 59;
                 $j++) {
                if (($i < 0 && $j <= 0) || ($i > 0 && $j >= 0) || $i == 0) {
                    $str = ($i < 0 || $j < 0 ? "-" : "+") .
                        substr(Math::abs($i) + 100, 1) . ":" .
                        substr(Math::abs($j) + 100, 1);
                    $test = ZoneOffset::of($str);
                    $this->doTestOffset($test, $i, $j, 0);
                }
            }
        }
        $test1 = ZoneOffset::of("-18:00");
        $this->doTestOffset($test1, -18, 0, 0);
        $test2 = ZoneOffset::of("+18:00");
        $this->doTestOffset($test2, 18, 0, 0);
    }

    /**
     * @group long
     */
    public function test_factory_string_hours_minutes_seconds_noColon()
    {
        for ($i = -17; $i <= 17;
             $i++) {
            for ($j = -59; $j <= 59;
                 $j++) {
                for ($k = -59; $k <= 59;
                     $k++) {
                    if (($i < 0 && $j <= 0 && $k <= 0) || ($i > 0 && $j >= 0 && $k >= 0) ||
                        ($i == 0 && (($j < 0 && $k <= 0) || ($j > 0 && $k >= 0) || $j == 0))
                    ) {
                        $str = ($i < 0 || $j < 0 || $k < 0 ? "-" : "+") .
                            substr(Math::abs($i) + 100, 1) .
                            substr(Math::abs($j) + 100, 1) .
                            substr(Math::abs($k) + 100, 1);
                        $test = ZoneOffset::of($str);
                        $this->doTestOffset($test, $i, $j, $k);
                    }
                }
            }
        }
        $test1 = ZoneOffset::of("-180000");
        $this->doTestOffset($test1, -18, 0, 0);
        $test2 = ZoneOffset::of("+180000");
        $this->doTestOffset($test2, 18, 0, 0);
    }

    /**
     * @group long
     */
    public function test_factory_string_hours_minutes_seconds_colon()
    {
        for ($i = -17; $i <= 17;
             $i++) {
            for ($j = -59; $j <= 59;
                 $j++) {
                for ($k = -59; $k <= 59;
                     $k++) {
                    if (($i < 0 && $j <= 0 && $k <= 0) || ($i > 0 && $j >= 0 && $k >= 0) ||
                        ($i == 0 && (($j < 0 && $k <= 0) || ($j > 0 && $k >= 0) || $j == 0))
                    ) {
                        $str = ($i < 0 || $j < 0 || $k < 0 ? "-" : "+") .
                            substr(Math::abs($i) + 100, 1) . ":" .
                            substr(Math::abs($j) + 100, 1) . ":" .
                            substr(Math::abs($k) + 100, 1);
                        $test = ZoneOffset::of($str);
                        $this->doTestOffset($test, $i, $j, $k);
                    }
                }
            }
        }
        $test1 = ZoneOffset::of("-18:00:00");
        $this->doTestOffset($test1, -18, 0, 0);
        $test2 = ZoneOffset::of("+18:00:00");
        $this->doTestOffset($test2, 18, 0, 0);
    }

    //-----------------------------------------------------------------------
    public function test_factory_int_hours()
    {
        for ($i = -18; $i <= 18;
             $i++) {
            $test = ZoneOffset::ofHours($i);
            $this->doTestOffset($test, $i, 0, 0);
        }
    }

    /**
     * @expectedException \Celest\DateTimeException
     */
    public function test_factory_int_hours_tooBig()
    {
        ZoneOffset::ofHours(19);
    }

    /**
     * @expectedException \Celest\DateTimeException
     */
    public function test_factory_int_hours_tooSmall()
    {
        ZoneOffset::ofHours(-19);
    }

    //-----------------------------------------------------------------------
    public function test_factory_int_hours_minutes()
    {
        for ($i = -17; $i <= 17;
             $i++) {
            for ($j = -59; $j <= 59;
                 $j++) {
                if (($i < 0 && $j <= 0) || ($i > 0 && $j >= 0) || $i == 0) {
                    $test = ZoneOffset::ofHoursMinutes($i, $j);
                    $this->doTestOffset($test, $i, $j, 0);
                }
            }
        }
        $test1 = ZoneOffset::ofHoursMinutes(-18, 0);
        $this->doTestOffset($test1, -18, 0, 0);
        $test2 = ZoneOffset::ofHoursMinutes(18, 0);
        $this->doTestOffset($test2, 18, 0, 0);
    }

    /**
     * @expectedException \Celest\DateTimeException
     */
    public function test_factory_int_hours_minutes_tooBig()
    {
        ZoneOffset::ofHoursMinutes(19, 0);
    }

    /**
     * @expectedException \Celest\DateTimeException
     */
    public function test_factory_int_hours_minutes_tooSmall()
    {
        ZoneOffset::ofHoursMinutes(-19, 0);
    }

    //-----------------------------------------------------------------------
    /**
     * @group long
     */
    public function test_factory_int_hours_minutes_seconds()
    {
        for ($i = -17; $i <= 17;
             $i++) {
            for ($j = -59; $j <= 59;
                 $j++) {
                for ($k = -59; $k <= 59;
                     $k++) {
                    if (($i < 0 && $j <= 0 && $k <= 0) || ($i > 0 && $j >= 0 && $k >= 0) ||
                        ($i == 0 && (($j < 0 && $k <= 0) || ($j > 0 && $k >= 0) || $j == 0))
                    ) {
                        $test = ZoneOffset::ofHoursMinutesSeconds($i, $j, $k);
                        $this->doTestOffset($test, $i, $j, $k);
                    }
                }
            }
        }
        $test1 = ZoneOffset::ofHoursMinutesSeconds(-18, 0, 0);
        $this->doTestOffset($test1, -18, 0, 0);
        $test2 = ZoneOffset::ofHoursMinutesSeconds(18, 0, 0);
        $this->doTestOffset($test2, 18, 0, 0);
    }

    /**
     * @expectedException \Celest\DateTimeException
     */
    public function test_factory_int_hours_minutes_seconds_plusHoursMinusMinutes()
    {
        ZoneOffset::ofHoursMinutesSeconds(1, -1, 0);
    }

    /**
     * @expectedException \Celest\DateTimeException
     */
    public function test_factory_int_hours_minutes_seconds_plusHoursMinusSeconds()
    {
        ZoneOffset::ofHoursMinutesSeconds(1, 0, -1);
    }

    /**
     * @expectedException \Celest\DateTimeException
     */
    public function test_factory_int_hours_minutes_seconds_minusHoursPlusMinutes()
    {
        ZoneOffset::ofHoursMinutesSeconds(-1, 1, 0);
    }

    /**
     * @expectedException \Celest\DateTimeException
     */
    public function test_factory_int_hours_minutes_seconds_minusHoursPlusSeconds()
    {
        ZoneOffset::ofHoursMinutesSeconds(-1, 0, 1);
    }

    /**
     * @expectedException \Celest\DateTimeException
     */
    public function test_factory_int_hours_minutes_seconds_zeroHoursMinusMinutesPlusSeconds()
    {
        ZoneOffset::ofHoursMinutesSeconds(0, -1, 1);
    }

    /**
     * @expectedException \Celest\DateTimeException
     */
    public function test_factory_int_hours_minutes_seconds_zeroHoursPlusMinutesMinusSeconds()
    {
        ZoneOffset::ofHoursMinutesSeconds(0, 1, -1);
    }

    /**
     * @expectedException \Celest\DateTimeException
     */
    public function test_factory_int_hours_minutes_seconds_minutesTooLarge()
    {
        ZoneOffset::ofHoursMinutesSeconds(0, 60, 0);
    }

    /**
     * @expectedException \Celest\DateTimeException
     */
    public function test_factory_int_hours_minutes_seconds_minutesTooSmall()
    {
        ZoneOffset::ofHoursMinutesSeconds(0, -60, 0);
    }

    /**
     * @expectedException \Celest\DateTimeException
     */
    public function test_factory_int_hours_minutes_seconds_secondsTooLarge()
    {
        ZoneOffset::ofHoursMinutesSeconds(0, 0, 60);
    }

    /**
     * @expectedException \Celest\DateTimeException
     */
    public function test_factory_int_hours_minutes_seconds_secondsTooSmall()
    {
        ZoneOffset::ofHoursMinutesSeconds(0, 0, 60);
    }

    /**
     * @expectedException \Celest\DateTimeException
     */
    public function test_factory_int_hours_minutes_seconds_hoursTooBig()
    {
        ZoneOffset::ofHoursMinutesSeconds(19, 0, 0);
    }

    /**
     * @expectedException \Celest\DateTimeException
     */
    public function test_factory_int_hours_minutes_seconds_hoursTooSmall()
    {
        ZoneOffset::ofHoursMinutesSeconds(-19, 0, 0);
    }

    //-----------------------------------------------------------------------
    public function test_factory_ofTotalSeconds()
    {
        $this->assertEquals(ZoneOffset::ofTotalSeconds(60 * 60 + 1), ZoneOffset::ofHoursMinutesSeconds(1, 0, 1));
        $this->assertEquals(ZoneOffset::ofTotalSeconds(18 * 60 * 60), ZoneOffset::ofHours(18));
        $this->assertEquals(ZoneOffset::ofTotalSeconds(-18 * 60 * 60), ZoneOffset::ofHours(-18));
    }

    /**
     * @expectedException \Celest\DateTimeException
     */
    public function test_factory_ofTotalSeconds_tooLarge()
    {
        ZoneOffset::ofTotalSeconds(18 * 60 * 60 + 1);
    }

    /**
     * @expectedException \Celest\DateTimeException
     */
    public function test_factory_ofTotalSeconds_tooSmall()
    {
        ZoneOffset::ofTotalSeconds(-18 * 60 * 60 - 1);
    }

    //-----------------------------------------------------------------------
    // from()
    //-----------------------------------------------------------------------
    public function test_factory_CalendricalObject()
    {
        $this->assertEquals(ZoneOffset::from(ZonedDateTime::ofDateTime(LocalDateTime::ofDateAndTime(LocalDate::of(2007, 7, 15),
            LocalTime::of(17, 30)), ZoneOffset::ofHours(2))), ZoneOffset::ofHours(2));
    }

    /**
     * @expectedException \Celest\DateTimeException
     */
    public function test_factory_CalendricalObject_invalid_noDerive()
    {
        ZoneOffset::from(LocalTime::of(12, 30));
    }

    public function test_factory_CalendricalObject_null()
    {
        TestHelper::assertNullException($this, function () {
            ZoneOffset::from(null);
        });
    }

    //-----------------------------------------------------------------------
    // getTotalSeconds()
    //-----------------------------------------------------------------------
    public function test_getTotalSeconds()
    {
        $offset = ZoneOffset::ofTotalSeconds(60 * 60 + 1);
        $this->assertEquals($offset->getTotalSeconds(), 60 * 60 + 1);
    }

    //-----------------------------------------------------------------------
    // getId()
    //-----------------------------------------------------------------------
    public function test_getId()
    {
        $offset = ZoneOffset::ofHoursMinutesSeconds(1, 0, 0);
        $this->assertEquals($offset->getId(), "+01:00");
        $offset = ZoneOffset::ofHoursMinutesSeconds(1, 2, 3);
        $this->assertEquals($offset->getId(), "+01:02:03");
        $offset = ZoneOffset::UTC();
        $this->assertEquals($offset->getId(), "Z");
    }

    //-----------------------------------------------------------------------
    // getRules()
    //-----------------------------------------------------------------------
    public function test_getRules()
    {
        $offset = ZoneOffset::ofHoursMinutesSeconds(1, 2, 3);
        $this->assertEquals($offset->getRules()->isFixedOffset(), true);
        $this->assertEquals($offset->getRules()->getOffset(null), $offset);
        $this->assertEquals($offset->getRules()->getDaylightSavings(null), Duration::ZERO());
        $this->assertEquals($offset->getRules()->getStandardOffset(null), $offset);
        $this->assertEquals($offset->getRules()->nextTransition(null), null);
        $this->assertEquals($offset->getRules()->previousTransition(null), null);

        $this->assertEquals($offset->getRules()->isValidOffset(null, $offset), true);
        $this->assertEquals($offset->getRules()->isValidOffset(null, ZoneOffset::UTC()), false);
        $this->assertEquals($offset->getRules()->isValidOffset(null, null), false);
        $this->assertEquals($offset->getRules()->getOffset(null), $offset);
        $this->assertEquals($offset->getRules()->getValidOffsets(null), [$offset]);
        $this->assertEquals($offset->getRules()->getTransition(null), null);
        $this->assertEquals(count($offset->getRules()->getTransitions()), 0);
        $this->assertEquals(count($offset->getRules()->getTransitionRules()), 0);
    }

    //-----------------------------------------------------------------------
    // get(TemporalField)
    //-----------------------------------------------------------------------
    public function test_get_TemporalField()
    {
        $this->assertEquals(ZoneOffset::UTC()->get(ChronoField::OFFSET_SECONDS()), 0);
        $this->assertEquals(ZoneOffset::ofHours(-2)->get(ChronoField::OFFSET_SECONDS()), -7200);
        $this->assertEquals(ZoneOffset::ofHoursMinutesSeconds(0, 1, 5)->get(ChronoField::OFFSET_SECONDS()), 65);
    }

    public function test_getLong_TemporalField()
    {
        $this->assertEquals(ZoneOffset::UTC()->getLong(ChronoField::OFFSET_SECONDS()), 0);
        $this->assertEquals(ZoneOffset::ofHours(-2)->getLong(ChronoField::OFFSET_SECONDS()), -7200);
        $this->assertEquals(ZoneOffset::ofHoursMinutesSeconds(0, 1, 5)->getLong(ChronoField::OFFSET_SECONDS()), 65);
    }

    //-----------------------------------------------------------------------
    // query(TemporalQuery)
    //-----------------------------------------------------------------------
    public function data_query()
    {
        return
            [
                [
                    ZoneOffset::UTC(), TemporalQueries::chronology(), null
                ],
                [
                    ZoneOffset::UTC(), TemporalQueries::zoneId(), null],
                [
                    ZoneOffset::UTC(), TemporalQueries::precision(), null],
                [
                    ZoneOffset::UTC(), TemporalQueries::zone(), ZoneOffset::UTC()],
                [
                    ZoneOffset::UTC(), TemporalQueries::offset(), ZoneOffset::UTC()],
                [
                    ZoneOffset::UTC(), TemporalQueries::localDate(), null],
                [
                    ZoneOffset::UTC(), TemporalQueries::localTime(), null],
            ];
    }

    /**
     * @dataProvider data_query
     */
    public function test_query(TemporalAccessor $temporal, TemporalQuery $query, $expected)
    {
        $this->assertEquals($temporal->query($query), $expected);
    }

    /**
     * @dataProvider data_query
     */
    public function test_queryFrom(TemporalAccessor $temporal, TemporalQuery $query, $expected)
    {
        $this->assertEquals($query->queryFrom($temporal), $expected);
    }

    public function test_query_null()
    {
        TestHelper::assertNullException($this, function () {
            ZoneOffset::UTC()->query(null);
        });

    }

    //-----------------------------------------------------------------------
    // compareTo()
    //-----------------------------------------------------------------------
    public function test_compareTo()
    {
        $offset1 = ZoneOffset::ofHoursMinutesSeconds(1, 2, 3);
        $offset2 = ZoneOffset::ofHoursMinutesSeconds(2, 3, 4);
        $this->assertTrue($offset1->compareTo($offset2) > 0);
        $this->assertTrue($offset2->compareTo($offset1) < 0);
        $this->assertTrue($offset1->compareTo($offset1) == 0);
        $this->assertTrue($offset2->compareTo($offset2) == 0);
    }

    //-----------------------------------------------------------------------
    // equals() / hashCode()
    //-----------------------------------------------------------------------
    public function test_equals()
    {
        $offset1 = ZoneOffset::ofHoursMinutesSeconds(1, 2, 3);
        $offset2 = ZoneOffset::ofHoursMinutesSeconds(2, 3, 4);
        $offset2b = ZoneOffset::ofHoursMinutesSeconds(2, 3, 4);
        $this->assertEquals($offset1->equals($offset2), false);
        $this->assertEquals($offset2->equals($offset1), false);

        $this->assertEquals($offset1->equals($offset1), true);
        $this->assertEquals($offset2->equals($offset2), true);
        $this->assertEquals($offset2->equals($offset2b), true);
    }

    //-----------------------------------------------------------------------
    // adjustInto()
    //-----------------------------------------------------------------------
    public function test_adjustInto_ZonedDateTime()
    {
        $base = ZoneOffset::ofHoursMinutesSeconds(1, 1, 1);
        foreach (ZoneId::getAvailableZoneIds() as $zoneId) {
            //Do not change $offset of ZonedDateTime after adjustInto()
            $zonedDateTime_target = ZonedDateTime::ofDateAndTime(LocalDate::of(1909, 2, 2), LocalTime::of(10, 10, 10), ZoneId::of($zoneId));
            $zonedDateTime_result = $base->adjustInto($zonedDateTime_target);
            $this->assertEquals($zonedDateTime_target->getOffset(), $zonedDateTime_result->getOffset());

            $offsetDateTime_target = $zonedDateTime_target->toOffsetDateTime();
            $offsetDateTime_result = $base->adjustInto($offsetDateTime_target);
            $this->assertEquals($base, $offsetDateTime_result->getOffset());
        }
    }

    public function test_adjustInto_OffsetDateTime()
    {
        $base = ZoneOffset::ofHoursMinutesSeconds(1, 1, 1);
        for ($i = -18; $i <= 18; $i++) {
            $offsetDateTime_target = OffsetDateTime::ofDateAndTime(LocalDate::of(1909, 2, 2), LocalTime::of(10, 10, 10), ZoneOffset::ofHours($i));
            $offsetDateTime_result = $base->adjustInto($offsetDateTime_target);
            $this->assertEquals($base, $offsetDateTime_result->getOffset());

            //Do not change $offset of ZonedDateTime after adjustInto()
            $zonedDateTime_target = $offsetDateTime_target->toZonedDateTime();
            $zonedDateTime_result = $base->adjustInto($zonedDateTime_target);
            $this->assertEquals($zonedDateTime_target->getOffset(), $zonedDateTime_result->getOffset());
        }
    }

    /**
     * @expectedException \Celest\DateTimeException
     */
    public function test_adjustInto_dateOnly()
    {
        $base = ZoneOffset::ofHoursMinutesSeconds(1, 1, 1);
        $base->adjustInto((LocalDate::of(1909, 2, 2)));
    }

    //-----------------------------------------------------------------------
    // toString()
    //-----------------------------------------------------------------------
    public function test_toString()
    {
        $offset = ZoneOffset::ofHoursMinutesSeconds(1, 0, 0);
        $this->assertEquals($offset->__toString(), "+01:00");
        $offset = ZoneOffset::ofHoursMinutesSeconds(1, 2, 3);
        $this->assertEquals($offset->__toString(), "+01:02:03");
        $offset = ZoneOffset::UTC();
        $this->assertEquals($offset->__toString(), "Z");
    }

    //-----------------------------------------------------------------------
    //-----------------------------------------------------------------------
    //-----------------------------------------------------------------------
    private function doTestOffset(ZoneOffset $offset, $hours, $minutes, $seconds)
    {
        $this->assertEquals($offset->getTotalSeconds(), $hours * 60 * 60 + $minutes * 60 + $seconds);

        if ($hours == 0 && $minutes == 0 && $seconds == 0) {
            $id = "Z";
        } else {
            $str = ($hours < 0 || $minutes < 0 || $seconds < 0) ? "-" : "+";
            $str .= substr(Math::abs($hours) + 100, 1);
            $str .= ":";
            $str .= substr(Math::abs($minutes) + 100, 1);
            if ($seconds !== 0) {
                $str .= ":";
                $str .= substr(Math::abs($seconds) + 100, 1);
            }
            $id = $str;
        }
        $this->assertEquals($offset->getId(), $id);
        $this->assertEquals($offset, ZoneOffset::ofHoursMinutesSeconds($hours, $minutes, $seconds));
        if ($seconds === 0) {
            $this->assertEquals($offset, ZoneOffset::ofHoursMinutes($hours, $minutes));
            if ($minutes === 0) {
                $this->assertEquals($offset, ZoneOffset::ofHours($hours));
            }
        }
        $this->assertEquals(ZoneOffset::of($id), $offset);
        $this->assertEquals($offset->__toString(), $id);
    }

}
