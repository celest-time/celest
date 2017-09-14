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

use Celest\Chrono\Chronology;
use Celest\Chrono\IsoChronology;
use Celest\DateTimeException;
use Celest\DateTimeParseException;
use Celest\LocalDate;
use Celest\LocalDateTime;
use Celest\Locale;
use Celest\Month;
use Celest\Temporal\AbstractTemporalAccessor;
use Celest\Temporal\ChronoField;
use Celest\Temporal\FieldValues;
use Celest\Temporal\IsoFields;
use Celest\Temporal\TemporalAccessor;
use Celest\Temporal\TemporalField;
use Celest\Temporal\TemporalQueries;
use Celest\Temporal\TemporalQuery;
use Celest\TestHelper;
use Celest\Year;
use Celest\YearMonth;
use Celest\ZonedDateTime;
use Celest\ZoneId;
use Celest\ZoneOffset;
use PHPUnit\Framework\TestCase;

class Expected
{
    /** @var FieldValues */
    public $fieldValues;
    /** @var ZoneId */
    public $zone;
    /** @var Chronology */
    public $chrono;

    public function __construct(TemporalField $field1 = null, $value1 = null, TemporalField $field2 = null, $value2 = null)
    {
        $this->fieldValues = new FieldValues();

        if ($field1 === null)
            return;

        $this->fieldValues->put($field1, $value1);
        $this->fieldValues->put($field2, $value2);
    }

    function add(ZoneOffset $offset)
    {
        $this->fieldValues->put(ChronoField::OFFSET_SECONDS(), $offset->getTotalSeconds());
    }
}

class MockAccessor extends AbstractTemporalAccessor
{
    /** @var FieldValues */
    public $fields;
    /** @var ZoneId */
    public $zoneId;

    public function __construct()
    {
        $this->fields = new FieldValues();
    }


    public function setFields(LocalDate $dt)
    {
        if ($dt !== null) {
            $this->fields->put(ChronoField::YEAR(), $dt->getYear());
            $this->fields->put(ChronoField::MONTH_OF_YEAR(), $dt->getMonthValue());
            $this->fields->put(ChronoField::DAY_OF_MONTH(), $dt->getDayOfMonth());
            $this->fields->put(ChronoField::DAY_OF_YEAR(), $dt->getDayOfYear());
            $this->fields->put(ChronoField::DAY_OF_WEEK(), $dt->getDayOfWeek()->getValue());
            $this->fields->put(IsoFields::WEEK_BASED_YEAR(), $dt->getLong(IsoFields::WEEK_BASED_YEAR()));
            $this->fields->put(IsoFields::WEEK_OF_WEEK_BASED_YEAR(), $dt->getLong(IsoFields::WEEK_OF_WEEK_BASED_YEAR()));
        }
    }

    public function setFieldsDateTime(LocalDateTime $dt)
    {
        if ($dt !== null) {
            $this->fields->put(ChronoField::YEAR(), $dt->getYear());
            $this->fields->put(ChronoField::MONTH_OF_YEAR(), $dt->getMonthValue());
            $this->fields->put(ChronoField::DAY_OF_MONTH(), $dt->getDayOfMonth());
            $this->fields->put(ChronoField::DAY_OF_YEAR(), $dt->getDayOfYear());
            $this->fields->put(ChronoField::DAY_OF_WEEK(), $dt->getDayOfWeek()->getValue());
            $this->fields->put(IsoFields::WEEK_BASED_YEAR(), $dt->getLong(IsoFields::WEEK_BASED_YEAR()));
            $this->fields->put(IsoFields::WEEK_OF_WEEK_BASED_YEAR(), $dt->getLong(IsoFields::WEEK_OF_WEEK_BASED_YEAR()));
            $this->fields->put(ChronoField::HOUR_OF_DAY(), $dt->getHour());
            $this->fields->put(ChronoField::MINUTE_OF_HOUR(), $dt->getMinute());
            $this->fields->put(ChronoField::SECOND_OF_MINUTE(), $dt->getSecond());
            $this->fields->put(ChronoField::NANO_OF_SECOND(), $dt->getNano());
        }
    }

    public function setOffset($offsetId)
    {
        if ($offsetId !== null) {
            $this->fields->put(ChronoField::OFFSET_SECONDS(), ZoneOffset::of($offsetId)->getTotalSeconds());
        }
    }

    public function setZone($zoneId)
    {
        if ($zoneId !== null) {
            $this->zoneId = ZoneId::of($zoneId);
        }
    }

    public function isSupported(TemporalField $field)
    {
        return $this->fields->has($field);
    }

    public function getLong(TemporalField $field)
    {
        $val = $this->fields->get($field);

        if ($val === null) {
            throw new DateTimeException("Field missing: " . $field);
        }
        return $val;
    }

    public function query(TemporalQuery $query)
    {
        if ($query == TemporalQueries::zoneId()) {
            return $this->zoneId;
        }
        return parent::query($query);
    }

    public function __toString()
    {
        return print_r($this->fields, true) . ($this->zoneId !== null ? " " . $this->zoneId : "");
    }

    public function range(TemporalField $field)
    {
        throw new \Exception();
    }


    public function get(TemporalField $field)
    {
        throw new \Exception();
    }
}

class TestAccessor extends AbstractTemporalAccessor
{
    public function isSupported(TemporalField $field)
    {
        return $field == ChronoField::YEAR() || $field == ChronoField::DAY_OF_YEAR();
    }

    public function getLong(TemporalField $field)
    {
        if ($field == ChronoField::YEAR()) {
            return 2008;
        }
        if ($field == ChronoField::DAY_OF_YEAR()) {
            return 231;
        }
        throw new DateTimeException("Unsupported");
    }

    public function range(TemporalField $field)
    {
        return null;
    }
}

/**
 * Test DateTimeFormatter.
 *
 */
class TCKDateTimeFormattersTest extends TestCase
{

    //-----------------------------------------------------------------------
    public function test_format_nullTemporalAccessor()
    {
        TestHelper::assertNullException($this, function () {
            DateTimeFormatter::ISO_DATE()->format(null);
        });
    }

    //-----------------------------------------------------------------------
    //-----------------------------------------------------------------------
    //-----------------------------------------------------------------------

    public function test_pattern_String()
    {
        $test = DateTimeFormatter::ofPattern("d MMM yyyy");
        $fmtLocale = Locale::getDefault();
        $this->assertEquals($test->format(LocalDate::of(2012, 6, 30)), "30 " .
            Month::JUNE()->getDisplayName(TextStyle::SHORT(), $fmtLocale) . " 2012");
        $this->assertEquals($test->getLocale(), $fmtLocale, "Locale.Category.FORMAT");
    }

    /**
     * @expectedException \Celest\IllegalArgumentException
     */
    public function test_pattern_String_invalid()
    {
        DateTimeFormatter::ofPattern("p");
    }

    public function test_pattern_String_null()
    {
        TestHelper::assertNullException($this, function () {
            DateTimeFormatter::ofPattern(null);
        });
    }

    //-----------------------------------------------------------------------
    //-----------------------------------------------------------------------
    //-----------------------------------------------------------------------

    public function test_pattern_StringLocale()
    {
        $test = DateTimeFormatter::ofPatternLocale("d MMM yyyy", Locale::UK());
        $this->assertEquals($test->format(LocalDate::of(2012, 6, 30)), "30 Jun 2012");
        $this->assertEquals($test->getLocale(), Locale::UK());
    }

    /**
     * @expectedException \Celest\IllegalArgumentException
     */
    public function test_pattern_StringLocale_invalid()
    {
        DateTimeFormatter::ofPatternLocale("p", Locale::UK());
    }

    public function test_pattern_StringLocale_nullPattern()
    {
        TestHelper::assertNullException($this, function () {
            DateTimeFormatter::ofPatternLocale(null, Locale::UK());

        });
    }

    public function test_pattern_StringLocale_nullLocale()
    {
        TestHelper::assertNullException($this, function () {
            DateTimeFormatter::ofPatternLocale("yyyy", null);

        });
    }

    //-----------------------------------------------------------------------
    //-----------------------------------------------------------------------
    //-----------------------------------------------------------------------

    public function test_ofLocalizedDate_basics()
    {
        $this->assertEquals(DateTimeFormatter::ofLocalizedDate(FormatStyle::FULL())->getChronology(), IsoChronology::INSTANCE());
        $this->assertEquals(DateTimeFormatter::ofLocalizedDate(FormatStyle::FULL())->getZone(), null);
        $this->assertEquals(DateTimeFormatter::ofLocalizedDate(FormatStyle::FULL())->getResolverStyle(), ResolverStyle::SMART());
    }


    public function test_ofLocalizedTime_basics()
    {
        $this->assertEquals(DateTimeFormatter::ofLocalizedTime(FormatStyle::FULL())->getChronology(), IsoChronology::INSTANCE());
        $this->assertEquals(DateTimeFormatter::ofLocalizedTime(FormatStyle::FULL())->getZone(), null);
        $this->assertEquals(DateTimeFormatter::ofLocalizedTime(FormatStyle::FULL())->getResolverStyle(), ResolverStyle::SMART());
    }


    public function test_ofLocalizedDateTime1_basics()
    {
        $this->assertEquals(DateTimeFormatter::ofLocalizedDateTime(FormatStyle::FULL())->getChronology(), IsoChronology::INSTANCE());
        $this->assertEquals(DateTimeFormatter::ofLocalizedDateTime(FormatStyle::FULL())->getZone(), null);
        $this->assertEquals(DateTimeFormatter::ofLocalizedDateTime(FormatStyle::FULL())->getResolverStyle(), ResolverStyle::SMART());
    }


    public function test_ofLocalizedDateTime2_basics()
    {
        $this->assertEquals(DateTimeFormatter::ofLocalizedDateTimeSplit(FormatStyle::FULL(), FormatStyle::MEDIUM())->getChronology(), IsoChronology::INSTANCE());
        $this->assertEquals(DateTimeFormatter::ofLocalizedDateTimeSplit(FormatStyle::FULL(), FormatStyle::MEDIUM())->getZone(), null);
        $this->assertEquals(DateTimeFormatter::ofLocalizedDateTimeSplit(FormatStyle::FULL(), FormatStyle::MEDIUM())->getResolverStyle(), ResolverStyle::SMART());
    }

    //-----------------------------------------------------------------------
    //-----------------------------------------------------------------------
    //-----------------------------------------------------------------------
    function provider_sample_isoLocalDate()
    {
        return
            [
                [
                    2008, null, null, null, null, null, DateTimeException::class],
                [
                    null, 6, null, null, null, null, DateTimeException::class],
                [
                    null, null, 30, null, null, null, DateTimeException::class],
                [
                    null, null, null, "+01:00", null, null, DateTimeException::class],
                [
                    null, null, null, null, "Europe/Paris", null, DateTimeException::class],
                [
                    2008, 6, null, null, null, null, DateTimeException::class],
                [
                    null, 6, 30, null, null, null, DateTimeException::class],

                [
                    2008, 6, 30, null, null, "2008-06-30", null],
                [
                    2008, 6, 30, "+01:00", null, "2008-06-30", null],
                [
                    2008, 6, 30, "+01:00", "Europe/Paris", "2008-06-30", null],
                [
                    2008, 6, 30, null, "Europe/Paris", "2008-06-30", null],

                [
                    123456, 6, 30, null, null, "+123456-06-30", null],
            ];
    }

    /**
     * @dataProvider provider_sample_isoLocalDate
     */
    public function test_print_isoLocalDate(
        $year, $month, $day, $offsetId, $zoneId,
        $expected, $expectedEx)
    {
        $test = $this->buildAccessor($year, $month, $day, null, null, null, null, $offsetId, $zoneId);
        if ($expectedEx === null) {
            $this->assertEquals(DateTimeFormatter::ISO_LOCAL_DATE()->format($test), $expected);
        } else {
            try {
                DateTimeFormatter::ISO_LOCAL_DATE()->format($test);
                $this->fail();
            } catch (\Exception $ex) {
                $this->assertInstanceOf($expectedEx, $ex);
            }
        }
    }

    /**
     * @dataProvider provider_sample_isoLocalDate
     */
    public function test_parse_isoLocalDate(
        $year, $month, $day, $offsetId, $zoneId,
        $input, $invalid)
    {
        if ($input !== null) {
            $expected = $this->createDate($year, $month, $day);
            // offset/zone not $expected to be $parsed
            $this->assertParseMatch(DateTimeFormatter::ISO_LOCAL_DATE()->parseUnresolved($input, new ParsePosition(0)), $expected);
        } else {
            $this->assertTrue(true);
        }
    }


    public function test_parse_isoLocalDate_999999999()
    {
        $expected = $this->createDate(999999999, 8, 6);
        $this->assertParseMatch(DateTimeFormatter::ISO_LOCAL_DATE()->parseUnresolved("+999999999-08-06", new ParsePosition(0)), $expected);
        $this->assertEquals(LocalDate::parse("+999999999-08-06"), LocalDate::of(999999999, 8, 6));
    }


    public function test_parse_isoLocalDate_1000000000()
    {
        $expected = $this->createDate(1000000000, 8, 6);
        $this->assertParseMatch(DateTimeFormatter::ISO_LOCAL_DATE()->parseUnresolved("+1000000000-08-06", new ParsePosition(0)), $expected);
    }

    /**
     * @expectedException \Celest\DateTimeException
     */
    public function test_parse_isoLocalDate_1000000000_failedCreate()
    {
        LocalDate::parse("+1000000000-08-06");
    }


    public function test_parse_isoLocalDate_M999999999()
    {
        $expected = $this->createDate(-999999999, 8, 6);
        $this->assertParseMatch(DateTimeFormatter::ISO_LOCAL_DATE()->parseUnresolved("-999999999-08-06", new ParsePosition(0)), $expected);
        $this->assertEquals(LocalDate::parse("-999999999-08-06"), LocalDate::of(-999999999, 8, 6));
    }


    public function test_parse_isoLocalDate_M1000000000()
    {
        $expected = $this->createDate(-1000000000, 8, 6);
        $this->assertParseMatch(DateTimeFormatter::ISO_LOCAL_DATE()->parseUnresolved("-1000000000-08-06", new ParsePosition(0)), $expected);
    }

    /**
     * @expectedException \Celest\DateTimeException
     */
    public function test_parse_isoLocalDate_M1000000000_failedCreate()
    {
        LocalDate::parse("-1000000000-08-06");
    }


    public function test_isoLocalDate_basics()
    {
        $this->assertEquals(DateTimeFormatter::ISO_LOCAL_DATE()->getChronology(), IsoChronology::INSTANCE());
        $this->assertEquals(DateTimeFormatter::ISO_LOCAL_DATE()->getZone(), null);
        $this->assertEquals(DateTimeFormatter::ISO_LOCAL_DATE()->getResolverStyle(), ResolverStyle::STRICT());
    }

    //-----------------------------------------------------------------------
    //-----------------------------------------------------------------------
    //-----------------------------------------------------------------------
    function provider_sample_isoOffsetDate()
    {
        return [
            [
                2008, null, null, null, null, null, DateTimeException::class],
            [
                null, 6, null, null, null, null, DateTimeException::class],
            [
                null, null, 30, null, null, null, DateTimeException::class],
            [
                null, null, null, "+01:00", null, null, DateTimeException::class],
            [
                null, null, null, null, "Europe/Paris", null, DateTimeException::class],
            [
                2008, 6, null, null, null, null, DateTimeException::class],
            [
                null, 6, 30, null, null, null, DateTimeException::class],

            [
                2008, 6, 30, null, null, null, DateTimeException::class],
            [
                2008, 6, 30, "+01:00", null, "2008-06-30+01:00", null],
            [
                2008, 6, 30, "+01:00", "Europe/Paris", "2008-06-30+01:00", null],
            [
                2008, 6, 30, null, "Europe/Paris", null, DateTimeException::class],

            [
                123456, 6, 30, "+01:00", null, "+123456-06-30+01:00", null],
        ];
    }

    /**
     * @dataProvider provider_sample_isoOffsetDate
     */
    public function test_print_isoOffsetDate(
        $year, $month, $day, $offsetId, $zoneId,
        $expected, $expectedEx)
    {
        $test = $this->buildAccessor($year, $month, $day, null, null, null, null, $offsetId, $zoneId);
        if ($expectedEx == null) {
            $this->assertEquals(DateTimeFormatter::ISO_OFFSET_DATE()->format($test), $expected);
        } else {
            try {
                DateTimeFormatter::ISO_OFFSET_DATE()->format($test);
                $this->fail();
            } catch (\Exception $ex) {
                $this->assertInstanceOf($expectedEx, $ex);
            }
        }
    }

    /**
     * @dataProvider provider_sample_isoOffsetDate
     */
    public function test_parse_isoOffsetDate(
        $year, $month, $day, $offsetId, $zoneId,
        $input, $invalid)
    {
        if ($input !== null) {
            $expected = $this->createDate($year, $month, $day);
            $this->buildCalendrical($expected, $offsetId, null);  // zone not $expected to be $parsed
            $this->assertParseMatch(DateTimeFormatter::ISO_OFFSET_DATE()->parseUnresolved($input, new ParsePosition(0)), $expected);
        } else {
            $this->assertTrue(true);
        }
    }


    public function test_isoOffsetDate_basics()
    {
        $this->assertEquals(DateTimeFormatter::ISO_OFFSET_DATE()->getChronology(), IsoChronology::INSTANCE());
        $this->assertEquals(DateTimeFormatter::ISO_OFFSET_DATE()->getZone(), null);
        $this->assertEquals(DateTimeFormatter::ISO_OFFSET_DATE()->getResolverStyle(), ResolverStyle::STRICT());
    }

    //-----------------------------------------------------------------------
    //-----------------------------------------------------------------------
    //-----------------------------------------------------------------------
    function provider_sample_isoDate()
    {
        return [
            [
                2008, null, null, null, null, null, DateTimeException::class],
            [
                null, 6, null, null, null, null, DateTimeException::class],
            [
                null, null, 30, null, null, null, DateTimeException::class],
            [
                null, null, null, "+01:00", null, null, DateTimeException::class],
            [
                null, null, null, null, "Europe/Paris", null, DateTimeException::class],
            [
                2008, 6, null, null, null, null, DateTimeException::class],
            [
                null, 6, 30, null, null, null, DateTimeException::class],

            [
                2008, 6, 30, null, null, "2008-06-30", null],
            [
                2008, 6, 30, "+01:00", null, "2008-06-30+01:00", null],
            [
                2008, 6, 30, "+01:00", "Europe/Paris", "2008-06-30+01:00", null],
            [
                2008, 6, 30, null, "Europe/Paris", "2008-06-30", null],

            [
                123456, 6, 30, "+01:00", "Europe/Paris", "+123456-06-30+01:00", null],
        ];
    }

    /**
     * @dataProvider provider_sample_isoDate
     */
    public function test_print_isoDate(
        $year, $month, $day, $offsetId, $zoneId,
        $expected, $expectedEx)
    {
        $test = $this->buildAccessor($year, $month, $day, null, null, null, null, $offsetId, $zoneId);
        if ($expectedEx == null) {
            $this->assertEquals(DateTimeFormatter::ISO_DATE()->format($test), $expected);
        } else {
            try {
                DateTimeFormatter::ISO_DATE()->format($test);
                $this->fail();
            } catch (\Exception $ex) {
                $this->assertInstanceOf($expectedEx, $ex);
            }
        }
    }

    /**
     * @dataProvider provider_sample_isoDate
     */
    public function test_parse_isoDate(
        $year, $month, $day, $offsetId, $zoneId,
        $input, $invalid)
    {
        if ($input !== null) {
            $expected = $this->createDate($year, $month, $day);
            if ($offsetId !== null) {
                $expected->add(ZoneOffset::of($offsetId));
            }
            $this->assertParseMatch(DateTimeFormatter::ISO_DATE()->parseUnresolved($input, new ParsePosition(0)), $expected);
        } else {
            $this->assertTrue(true);
        }
    }


    public function test_isoDate_basics()
    {
        $this->assertEquals(DateTimeFormatter::ISO_DATE()->getChronology(), IsoChronology::INSTANCE());
        $this->assertEquals(DateTimeFormatter::ISO_DATE()->getZone(), null);
        $this->assertEquals(DateTimeFormatter::ISO_DATE()->getResolverStyle(), ResolverStyle::STRICT());
    }

    //-----------------------------------------------------------------------
    //-----------------------------------------------------------------------
    //-----------------------------------------------------------------------
    function provider_sample_isoLocalTime()
    {
        return [
            [
                11, null, null, null, null, null, null, DateTimeException::class],
            [
                null, 5, null, null, null, null, null, DateTimeException::class],
            [
                null, null, 30, null, null, null, null, DateTimeException::class],
            [
                null, null, null, 1, null, null, null, DateTimeException::class],
            [
                null, null, null, null, "+01:00", null, null, DateTimeException::class],
            [
                null, null, null, null, null, "Europe/Paris", null, DateTimeException::class],

            [
                11, 5, null, null, null, null, "11:05", null],
            [
                11, 5, 30, null, null, null, "11:05:30", null],
            [
                11, 5, 30, 500000000, null, null, "11:05:30.5", null],
            [
                11, 5, 30, 1, null, null, "11:05:30.000000001", null],

            [
                11, 5, null, null, "+01:00", null, "11:05", null],
            [
                11, 5, 30, null, "+01:00", null, "11:05:30", null],
            [
                11, 5, 30, 500000000, "+01:00", null, "11:05:30.5", null],
            [
                11, 5, 30, 1, "+01:00", null, "11:05:30.000000001", null],

            [
                11, 5, null, null, "+01:00", "Europe/Paris", "11:05", null],
            [
                11, 5, 30, null, "+01:00", "Europe/Paris", "11:05:30", null],
            [
                11, 5, 30, 500000000, "+01:00", "Europe/Paris", "11:05:30.5", null],
            [
                11, 5, 30, 1, "+01:00", "Europe/Paris", "11:05:30.000000001", null],

            [
                11, 5, null, null, null, "Europe/Paris", "11:05", null],
            [
                11, 5, 30, null, null, "Europe/Paris", "11:05:30", null],
            [
                11, 5, 30, 500000000, null, "Europe/Paris", "11:05:30.5", null],
            [
                11, 5, 30, 1, null, "Europe/Paris", "11:05:30.000000001", null],
        ];
    }

    /**
     * @dataProvider provider_sample_isoLocalTime
     */
    public function test_print_isoLocalTime(
        $hour, $min, $sec, $nano, $offsetId, $zoneId,
        $expected, $expectedEx)
    {
        $test = $this->buildAccessor(null, null, null, $hour, $min, $sec, $nano, $offsetId, $zoneId);
        if ($expectedEx == null) {
            $this->assertEquals(DateTimeFormatter::ISO_LOCAL_TIME()->format($test), $expected);
        } else {
            try {
                DateTimeFormatter::ISO_LOCAL_TIME()->format($test);
                $this->fail();
            } catch (\Exception $ex) {
                $this->assertInstanceOf($expectedEx, $ex);
            }
        }
    }

    /**
     * @dataProvider provider_sample_isoLocalTime
     */
    public function test_parse_isoLocalTime(
        $hour, $min, $sec, $nano, $offsetId, $zoneId,
        $input, $invalid)
    {
        if ($input !== null) {
            $expected = $this->createTime($hour, $min, $sec, $nano);
            // offset/zone not $expected to be $parsed
            $this->assertParseMatch(DateTimeFormatter::ISO_LOCAL_TIME()->parseUnresolved($input, new ParsePosition(0)), $expected);
        } else {
            $this->assertTrue(true);
        }
    }


    public function test_isoLocalTime_basics()
    {
        $this->assertEquals(DateTimeFormatter::ISO_LOCAL_TIME()->getChronology(), null);
        $this->assertEquals(DateTimeFormatter::ISO_LOCAL_TIME()->getZone(), null);
        $this->assertEquals(DateTimeFormatter::ISO_LOCAL_TIME()->getResolverStyle(), ResolverStyle::STRICT());
    }

    //-----------------------------------------------------------------------
    //-----------------------------------------------------------------------
    //-----------------------------------------------------------------------
    function provider_sample_isoOffsetTime()
    {
        return [
            [
                11, null, null, null, null, null, null, DateTimeException::class],
            [
                null, 5, null, null, null, null, null, DateTimeException::class],
            [
                null, null, 30, null, null, null, null, DateTimeException::class],
            [
                null, null, null, 1, null, null, null, DateTimeException::class],
            [
                null, null, null, null, "+01:00", null, null, DateTimeException::class],
            [
                null, null, null, null, null, "Europe/Paris", null, DateTimeException::class],

            [
                11, 5, null, null, null, null, null, DateTimeException::class],
            [
                11, 5, 30, null, null, null, null, DateTimeException::class],
            [
                11, 5, 30, 500000000, null, null, null, DateTimeException::class],
            [
                11, 5, 30, 1, null, null, null, DateTimeException::class],

            [
                11, 5, null, null, "+01:00", null, "11:05+01:00", null],
            [
                11, 5, 30, null, "+01:00", null, "11:05:30+01:00", null],
            [
                11, 5, 30, 500000000, "+01:00", null, "11:05:30.5+01:00", null],
            [
                11, 5, 30, 1, "+01:00", null, "11:05:30.000000001+01:00", null],

            [
                11, 5, null, null, "+01:00", "Europe/Paris", "11:05+01:00", null],
            [
                11, 5, 30, null, "+01:00", "Europe/Paris", "11:05:30+01:00", null],
            [
                11, 5, 30, 500000000, "+01:00", "Europe/Paris", "11:05:30.5+01:00", null],
            [
                11, 5, 30, 1, "+01:00", "Europe/Paris", "11:05:30.000000001+01:00", null],

            [
                11, 5, null, null, null, "Europe/Paris", null, DateTimeException::class],
            [
                11, 5, 30, null, null, "Europe/Paris", null, DateTimeException::class],
            [
                11, 5, 30, 500000000, null, "Europe/Paris", null, DateTimeException::class],
            [
                11, 5, 30, 1, null, "Europe/Paris", null, DateTimeException::class],
        ];
    }

    /**
     * @dataProvider provider_sample_isoOffsetTime
     */
    public function test_print_isoOffsetTime(
        $hour, $min, $sec, $nano, $offsetId, $zoneId,
        $expected, $expectedEx)
    {
        $test = $this->buildAccessor(null, null, null, $hour, $min, $sec, $nano, $offsetId, $zoneId);
        if ($expectedEx == null) {
            $this->assertEquals(DateTimeFormatter::ISO_OFFSET_TIME()->format($test), $expected);
        } else {
            try {
                DateTimeFormatter::ISO_OFFSET_TIME()->format($test);
                $this->fail();
            } catch (\Exception $ex) {
                $this->assertInstanceOf($expectedEx, $ex);
            }
        }
    }

    /**
     * @dataProvider provider_sample_isoOffsetTime
     */
    public function test_parse_isoOffsetTime(
        $hour, $min, $sec, $nano, $offsetId, $zoneId,
        $input, $invalid)
    {
        if ($input !== null) {
            $expected = $this->createTime($hour, $min, $sec, $nano);
            $this->buildCalendrical($expected, $offsetId, null);  // $zoneId is not $expected from parse
            $this->assertParseMatch(DateTimeFormatter::ISO_OFFSET_TIME()->parseUnresolved($input, new ParsePosition(0)), $expected);
        } else {
            $this->assertTrue(true);
        }
    }


    public function test_isoOffsetTime_basics()
    {
        $this->assertEquals(DateTimeFormatter::ISO_OFFSET_TIME()->getChronology(), null);
        $this->assertEquals(DateTimeFormatter::ISO_OFFSET_TIME()->getZone(), null);
        $this->assertEquals(DateTimeFormatter::ISO_OFFSET_TIME()->getResolverStyle(), ResolverStyle::STRICT());
    }

    //-----------------------------------------------------------------------
    //-----------------------------------------------------------------------
    //-----------------------------------------------------------------------
    function provider_sample_isoTime()
    {
        return [
            [
                11, null, null, null, null, null, null, DateTimeException::class],
            [
                null, 5, null, null, null, null, null, DateTimeException::class],
            [
                null, null, 30, null, null, null, null, DateTimeException::class],
            [
                null, null, null, 1, null, null, null, DateTimeException::class],
            [
                null, null, null, null, "+01:00", null, null, DateTimeException::class],
            [
                null, null, null, null, null, "Europe/Paris", null, DateTimeException::class],

            [
                11, 5, null, null, null, null, "11:05", null],
            [
                11, 5, 30, null, null, null, "11:05:30", null],
            [
                11, 5, 30, 500000000, null, null, "11:05:30.5", null],
            [
                11, 5, 30, 1, null, null, "11:05:30.000000001", null],

            [
                11, 5, null, null, "+01:00", null, "11:05+01:00", null],
            [
                11, 5, 30, null, "+01:00", null, "11:05:30+01:00", null],
            [
                11, 5, 30, 500000000, "+01:00", null, "11:05:30.5+01:00", null],
            [
                11, 5, 30, 1, "+01:00", null, "11:05:30.000000001+01:00", null],

            [
                11, 5, null, null, "+01:00", "Europe/Paris", "11:05+01:00", null],
            [
                11, 5, 30, null, "+01:00", "Europe/Paris", "11:05:30+01:00", null],
            [
                11, 5, 30, 500000000, "+01:00", "Europe/Paris", "11:05:30.5+01:00", null],
            [
                11, 5, 30, 1, "+01:00", "Europe/Paris", "11:05:30.000000001+01:00", null],

            [
                11, 5, null, null, null, "Europe/Paris", "11:05", null],
            [
                11, 5, 30, null, null, "Europe/Paris", "11:05:30", null],
            [
                11, 5, 30, 500000000, null, "Europe/Paris", "11:05:30.5", null],
            [
                11, 5, 30, 1, null, "Europe/Paris", "11:05:30.000000001", null],
        ];
    }

    /**
     * @dataProvider provider_sample_isoTime
     */
    public function test_print_isoTime(
        $hour, $min, $sec, $nano, $offsetId, $zoneId,
        $expected, $expectedEx)
    {
        $test = $this->buildAccessor(null, null, null, $hour, $min, $sec, $nano, $offsetId, $zoneId);
        if ($expectedEx == null) {
            $this->assertEquals(DateTimeFormatter::ISO_TIME()->format($test), $expected);
        } else {
            try {
                DateTimeFormatter::ISO_TIME()->format($test);
                $this->fail();
            } catch (\Exception $ex) {
                $this->assertInstanceOf($expectedEx, $ex);
            }
        }
    }

    /**
     * @dataProvider provider_sample_isoTime
     */
    public function test_parse_isoTime(
        $hour, $min, $sec, $nano, $offsetId, $zoneId,
        $input, $invalid)
    {
        if ($input !== null) {
            $expected = $this->createTime($hour, $min, $sec, $nano);
            if ($offsetId !== null) {
                $expected->add(ZoneOffset::of($offsetId));
            }
            $this->assertParseMatch(DateTimeFormatter::ISO_TIME()->parseUnresolved($input, new ParsePosition(0)), $expected);
        } else {
            $this->assertTrue(true);
        }
    }


    public function test_isoTime_basics()
    {
        $this->assertEquals(DateTimeFormatter::ISO_TIME()->getChronology(), null);
        $this->assertEquals(DateTimeFormatter::ISO_TIME()->getZone(), null);
        $this->assertEquals(DateTimeFormatter::ISO_TIME()->getResolverStyle(), ResolverStyle::STRICT());
    }

    //-----------------------------------------------------------------------
    //-----------------------------------------------------------------------
    //-----------------------------------------------------------------------
    function provider_sample_isoLocalDateTime()
    {
        return [
            [
                2008, null, null, null, null, null, null, null, null, null, DateTimeException::class],
            [
                null, 6, null, null, null, null, null, null, null, null, DateTimeException::class],
            [
                null, null, 30, null, null, null, null, null, null, null, DateTimeException::class],
            [
                null, null, null, 11, null, null, null, null, null, null, DateTimeException::class],
            [
                null, null, null, null, 5, null, null, null, null, null, DateTimeException::class],
            [
                null, null, null, null, null, null, null, "+01:00", null, null, DateTimeException::class],
            [
                null, null, null, null, null, null, null, null, "Europe/Paris", null, DateTimeException::class],
            [
                2008, 6, 30, 11, null, null, null, null, null, null, DateTimeException::class],
            [
                2008, 6, 30, null, 5, null, null, null, null, null, DateTimeException::class],
            [
                2008, 6, null, 11, 5, null, null, null, null, null, DateTimeException::class],
            [
                2008, null, 30, 11, 5, null, null, null, null, null, DateTimeException::class],
            [
                null, 6, 30, 11, 5, null, null, null, null, null, DateTimeException::class],

            [
                2008, 6, 30, 11, 5, null, null, null, null, "2008-06-30T11:05", null],
            [
                2008, 6, 30, 11, 5, 30, null, null, null, "2008-06-30T11:05:30", null],
            [
                2008, 6, 30, 11, 5, 30, 500000000, null, null, "2008-06-30T11:05:30.5", null],
            [
                2008, 6, 30, 11, 5, 30, 1, null, null, "2008-06-30T11:05:30.000000001", null],

            [
                2008, 6, 30, 11, 5, null, null, "+01:00", null, "2008-06-30T11:05", null],
            [
                2008, 6, 30, 11, 5, 30, null, "+01:00", null, "2008-06-30T11:05:30", null],
            [
                2008, 6, 30, 11, 5, 30, 500000000, "+01:00", null, "2008-06-30T11:05:30.5", null],
            [
                2008, 6, 30, 11, 5, 30, 1, "+01:00", null, "2008-06-30T11:05:30.000000001", null],

            [
                2008, 6, 30, 11, 5, null, null, "+01:00", "Europe/Paris", "2008-06-30T11:05", null],
            [
                2008, 6, 30, 11, 5, 30, null, "+01:00", "Europe/Paris", "2008-06-30T11:05:30", null],
            [
                2008, 6, 30, 11, 5, 30, 500000000, "+01:00", "Europe/Paris", "2008-06-30T11:05:30.5", null],
            [
                2008, 6, 30, 11, 5, 30, 1, "+01:00", "Europe/Paris", "2008-06-30T11:05:30.000000001", null],

            [
                2008, 6, 30, 11, 5, null, null, null, "Europe/Paris", "2008-06-30T11:05", null],
            [
                2008, 6, 30, 11, 5, 30, null, null, "Europe/Paris", "2008-06-30T11:05:30", null],
            [
                2008, 6, 30, 11, 5, 30, 500000000, null, "Europe/Paris", "2008-06-30T11:05:30.5", null],
            [
                2008, 6, 30, 11, 5, 30, 1, null, "Europe/Paris", "2008-06-30T11:05:30.000000001", null],

            [
                123456, 6, 30, 11, 5, null, null, null, null, "+123456-06-30T11:05", null],
        ];
    }

    /**
     * @dataProvider provider_sample_isoLocalDateTime
     */
    public function test_print_isoLocalDateTime(
        $year, $month, $day,
        $hour, $min, $sec, $nano, $offsetId, $zoneId,
        $expected, $expectedEx)
    {
        $test = $this->buildAccessor($year, $month, $day, $hour, $min, $sec, $nano, $offsetId, $zoneId);
        if ($expectedEx == null) {
            $this->assertEquals(DateTimeFormatter::ISO_LOCAL_DATE_TIME()->format($test), $expected);
        } else {
            try {
                DateTimeFormatter::ISO_LOCAL_DATE_TIME()->format($test);
                $this->fail();
            } catch (\Exception $ex) {
                $this->assertInstanceOf($expectedEx, $ex);
            }
        }
    }

    /**
     * @dataProvider provider_sample_isoLocalDateTime
     */
    public function test_parse_isoLocalDateTime(
        $year, $month, $day,
        $hour, $min, $sec,
        $nano, $offsetId, $zoneId,
        $input, $invalid)
    {
        if ($input !== null) {
            $expected = $this->createDateTime($year, $month, $day, $hour, $min, $sec, $nano);
            $this->assertParseMatch(DateTimeFormatter::ISO_LOCAL_DATE_TIME()->parseUnresolved($input, new ParsePosition(0)), $expected);
        } else {
            $this->assertTrue(true);
        }
    }


    public function test_isoLocalDateTime_basics()
    {
        $this->assertEquals(DateTimeFormatter::ISO_LOCAL_DATE_TIME()->getChronology(), IsoChronology::INSTANCE());
        $this->assertEquals(DateTimeFormatter::ISO_LOCAL_DATE_TIME()->getZone(), null);
        $this->assertEquals(DateTimeFormatter::ISO_LOCAL_DATE_TIME()->getResolverStyle(), ResolverStyle::STRICT());
    }

    //-----------------------------------------------------------------------
    //-----------------------------------------------------------------------
    //-----------------------------------------------------------------------
    function provider_sample_isoOffsetDateTime()
    {
        return [
            [
                2008, null, null, null, null, null, null, null, null, null, DateTimeException::class],
            [
                null, 6, null, null, null, null, null, null, null, null, DateTimeException::class],
            [
                null, null, 30, null, null, null, null, null, null, null, DateTimeException::class],
            [
                null, null, null, 11, null, null, null, null, null, null, DateTimeException::class],
            [
                null, null, null, null, 5, null, null, null, null, null, DateTimeException::class],
            [
                null, null, null, null, null, null, null, "+01:00", null, null, DateTimeException::class],
            [
                null, null, null, null, null, null, null, null, "Europe/Paris", null, DateTimeException::class],
            [
                2008, 6, 30, 11, null, null, null, null, null, null, DateTimeException::class],
            [
                2008, 6, 30, null, 5, null, null, null, null, null, DateTimeException::class],
            [
                2008, 6, null, 11, 5, null, null, null, null, null, DateTimeException::class],
            [
                2008, null, 30, 11, 5, null, null, null, null, null, DateTimeException::class],
            [
                null, 6, 30, 11, 5, null, null, null, null, null, DateTimeException::class],

            [
                2008, 6, 30, 11, 5, null, null, null, null, null, DateTimeException::class],
            [
                2008, 6, 30, 11, 5, 30, null, null, null, null, DateTimeException::class],
            [
                2008, 6, 30, 11, 5, 30, 500000000, null, null, null, DateTimeException::class],
            [
                2008, 6, 30, 11, 5, 30, 1, null, null, null, DateTimeException::class],

            [
                2008, 6, 30, 11, 5, null, null, "+01:00", null, "2008-06-30T11:05+01:00", null],
            [
                2008, 6, 30, 11, 5, 30, null, "+01:00", null, "2008-06-30T11:05:30+01:00", null],
            [
                2008, 6, 30, 11, 5, 30, 500000000, "+01:00", null, "2008-06-30T11:05:30.5+01:00", null],
            [
                2008, 6, 30, 11, 5, 30, 1, "+01:00", null, "2008-06-30T11:05:30.000000001+01:00", null],

            [
                2008, 6, 30, 11, 5, null, null, "+01:00", "Europe/Paris", "2008-06-30T11:05+01:00", null],
            [
                2008, 6, 30, 11, 5, 30, null, "+01:00", "Europe/Paris", "2008-06-30T11:05:30+01:00", null],
            [
                2008, 6, 30, 11, 5, 30, 500000000, "+01:00", "Europe/Paris", "2008-06-30T11:05:30.5+01:00", null],
            [
                2008, 6, 30, 11, 5, 30, 1, "+01:00", "Europe/Paris", "2008-06-30T11:05:30.000000001+01:00", null],

            [
                2008, 6, 30, 11, 5, null, null, null, "Europe/Paris", null, DateTimeException::class],
            [
                2008, 6, 30, 11, 5, 30, null, null, "Europe/Paris", null, DateTimeException::class],
            [
                2008, 6, 30, 11, 5, 30, 500000000, null, "Europe/Paris", null, DateTimeException::class],
            [
                2008, 6, 30, 11, 5, 30, 1, null, "Europe/Paris", null, DateTimeException::class],

            [
                123456, 6, 30, 11, 5, null, null, "+01:00", null, "+123456-06-30T11:05+01:00", null],
        ];
    }

    /**
     * @dataProvider provider_sample_isoOffsetDateTime
     */
    public function test_print_isoOffsetDateTime(
        $year, $month, $day,
        $hour, $min, $sec, $nano, $offsetId, $zoneId,
        $expected, $expectedEx)
    {
        $test = $this->buildAccessor($year, $month, $day, $hour, $min, $sec, $nano, $offsetId, $zoneId);
        if ($expectedEx == null) {
            $this->assertEquals(DateTimeFormatter::ISO_OFFSET_DATE_TIME()->format($test), $expected);
        } else {
            try {
                DateTimeFormatter::ISO_OFFSET_DATE_TIME()->format($test);
                $this->fail();
            } catch (\Exception $ex) {
                $this->assertInstanceOf($expectedEx, $ex);
            }
        }
    }

    /**
     * @dataProvider provider_sample_isoOffsetDateTime
     */
    public function test_parse_isoOffsetDateTime(
        $year, $month, $day,
        $hour, $min, $sec, $nano, $offsetId, $zoneId,
        $input, $invalid)
    {
        if ($input !== null) {
            $expected = $this->createDateTime($year, $month, $day, $hour, $min, $sec, $nano);
            $this->buildCalendrical($expected, $offsetId, null);  // zone not $expected to be $parsed
            $this->assertParseMatch(DateTimeFormatter::ISO_OFFSET_DATE_TIME()->parseUnresolved($input, new ParsePosition(0)), $expected);
        } else {
            $this->assertTrue(true);
        }
    }


    public function test_isoOffsetDateTime_basics()
    {
        $this->assertEquals(DateTimeFormatter::ISO_OFFSET_DATE_TIME()->getChronology(), IsoChronology::INSTANCE());
        $this->assertEquals(DateTimeFormatter::ISO_OFFSET_DATE_TIME()->getZone(), null);
        $this->assertEquals(DateTimeFormatter::ISO_OFFSET_DATE_TIME()->getResolverStyle(), ResolverStyle::STRICT());
    }

    //-----------------------------------------------------------------------
    //-----------------------------------------------------------------------
    //-----------------------------------------------------------------------
    function provider_sample_isoZonedDateTime()
    {
        return [
            [
                2008, null, null, null, null, null, null, null, null, null, DateTimeException::class],
            [
                null, 6, null, null, null, null, null, null, null, null, DateTimeException::class],
            [
                null, null, 30, null, null, null, null, null, null, null, DateTimeException::class],
            [
                null, null, null, 11, null, null, null, null, null, null, DateTimeException::class],
            [
                null, null, null, null, 5, null, null, null, null, null, DateTimeException::class],
            [
                null, null, null, null, null, null, null, "+01:00", null, null, DateTimeException::class],
            [
                null, null, null, null, null, null, null, null, "Europe/Paris", null, DateTimeException::class],
            [
                2008, 6, 30, 11, null, null, null, null, null, null, DateTimeException::class],
            [
                2008, 6, 30, null, 5, null, null, null, null, null, DateTimeException::class],
            [
                2008, 6, null, 11, 5, null, null, null, null, null, DateTimeException::class],
            [
                2008, null, 30, 11, 5, null, null, null, null, null, DateTimeException::class],
            [
                null, 6, 30, 11, 5, null, null, null, null, null, DateTimeException::class],

            [
                2008, 6, 30, 11, 5, null, null, null, null, null, DateTimeException::class],
            [
                2008, 6, 30, 11, 5, 30, null, null, null, null, DateTimeException::class],
            [
                2008, 6, 30, 11, 5, 30, 500000000, null, null, null, DateTimeException::class],
            [
                2008, 6, 30, 11, 5, 30, 1, null, null, null, DateTimeException::class],

            // allow OffsetDateTime (no harm comes of this AFAICT)
            [
                2008, 6, 30, 11, 5, null, null, "+01:00", null, "2008-06-30T11:05+01:00", null],
            [
                2008, 6, 30, 11, 5, 30, null, "+01:00", null, "2008-06-30T11:05:30+01:00", null],
            [
                2008, 6, 30, 11, 5, 30, 500000000, "+01:00", null, "2008-06-30T11:05:30.5+01:00", null],
            [
                2008, 6, 30, 11, 5, 30, 1, "+01:00", null, "2008-06-30T11:05:30.000000001+01:00", null],

            // ZonedDateTime with ZoneId of ZoneOffset
            [
                2008, 6, 30, 11, 5, null, null, "+01:00", "+01:00", "2008-06-30T11:05+01:00", null],
            [
                2008, 6, 30, 11, 5, 30, null, "+01:00", "+01:00", "2008-06-30T11:05:30+01:00", null],
            [
                2008, 6, 30, 11, 5, 30, 500000000, "+01:00", "+01:00", "2008-06-30T11:05:30.5+01:00", null],
            [
                2008, 6, 30, 11, 5, 30, 1, "+01:00", "+01:00", "2008-06-30T11:05:30.000000001+01:00", null],

            // ZonedDateTime with ZoneId of ZoneRegion
            [
                2008, 6, 30, 11, 5, null, null, "+01:00", "Europe/Paris", "2008-06-30T11:05+01:00[Europe/Paris]", null],
            [
                2008, 6, 30, 11, 5, 30, null, "+01:00", "Europe/Paris", "2008-06-30T11:05:30+01:00[Europe/Paris]", null],
            [
                2008, 6, 30, 11, 5, 30, 500000000, "+01:00", "Europe/Paris", "2008-06-30T11:05:30.5+01:00[Europe/Paris]", null],
            [
                2008, 6, 30, 11, 5, 30, 1, "+01:00", "Europe/Paris", "2008-06-30T11:05:30.000000001+01:00[Europe/Paris]", null],

            // offset required
            [
                2008, 6, 30, 11, 5, null, null, null, "Europe/Paris", null, DateTimeException::class],
            [
                2008, 6, 30, 11, 5, 30, null, null, "Europe/Paris", null, DateTimeException::class],
            [
                2008, 6, 30, 11, 5, 30, 500000000, null, "Europe/Paris", null, DateTimeException::class],
            [
                2008, 6, 30, 11, 5, 30, 1, null, "Europe/Paris", null, DateTimeException::class],

            [
                123456, 6, 30, 11, 5, null, null, "+01:00", "Europe/Paris", "+123456-06-30T11:05+01:00[Europe/Paris]", null],
        ];
    }

    /**
     * @dataProvider provider_sample_isoZonedDateTime
     */
    public function test_print_isoZonedDateTime(
        $year, $month, $day,
        $hour, $min, $sec, $nano, $offsetId, $zoneId,
        $expected, $expectedEx)
    {
        $test = $this->buildAccessor($year, $month, $day, $hour, $min, $sec, $nano, $offsetId, $zoneId);
        if ($expectedEx == null) {
            $this->assertEquals(DateTimeFormatter::ISO_ZONED_DATE_TIME()->format($test), $expected);
        } else {
            try {
                DateTimeFormatter::ISO_ZONED_DATE_TIME()->format($test);
                $this->fail($test->__toString());
            } catch (\Exception $ex) {
                $this->assertInstanceOf($expectedEx, $ex);
            }
        }
    }

    /**
     * @dataProvider provider_sample_isoZonedDateTime
     */
    public function test_parse_isoZonedDateTime(
        $year, $month, $day,
        $hour, $min, $sec, $nano, $offsetId, $zoneId,
        $input, $invalid)
    {
        if ($input !== null) {
            $expected = $this->createDateTime($year, $month, $day, $hour, $min, $sec, $nano);
            if ($offsetId === $zoneId) {
                $this->buildCalendrical($expected, $offsetId, null);
            } else {
                $this->buildCalendrical($expected, $offsetId, $zoneId);
            }
            $this->assertParseMatch(DateTimeFormatter::ISO_ZONED_DATE_TIME()->parseUnresolved($input, new ParsePosition(0)), $expected);
        } else {
            $this->assertTrue(true);
        }
    }


    public function test_isoZonedDateTime_basics()
    {
        $this->assertEquals(DateTimeFormatter::ISO_ZONED_DATE_TIME()->getChronology(), IsoChronology::INSTANCE());
        $this->assertEquals(DateTimeFormatter::ISO_ZONED_DATE_TIME()->getZone(), null);
        $this->assertEquals(DateTimeFormatter::ISO_ZONED_DATE_TIME()->getResolverStyle(), ResolverStyle::STRICT());
    }

    //-----------------------------------------------------------------------
    //-----------------------------------------------------------------------
    //-----------------------------------------------------------------------
    function provider_sample_isoDateTime()
    {
        return [
            [
                2008, null, null, null, null, null, null, null, null, null, DateTimeException::class],
            [
                null, 6, null, null, null, null, null, null, null, null, DateTimeException::class],
            [
                null, null, 30, null, null, null, null, null, null, null, DateTimeException::class],
            [
                null, null, null, 11, null, null, null, null, null, null, DateTimeException::class],
            [
                null, null, null, null, 5, null, null, null, null, null, DateTimeException::class],
            [
                null, null, null, null, null, null, null, "+01:00", null, null, DateTimeException::class],
            [
                null, null, null, null, null, null, null, null, "Europe/Paris", null, DateTimeException::class],
            [
                2008, 6, 30, 11, null, null, null, null, null, null, DateTimeException::class],
            [
                2008, 6, 30, null, 5, null, null, null, null, null, DateTimeException::class],
            [
                2008, 6, null, 11, 5, null, null, null, null, null, DateTimeException::class],
            [
                2008, null, 30, 11, 5, null, null, null, null, null, DateTimeException::class],
            [
                null, 6, 30, 11, 5, null, null, null, null, null, DateTimeException::class],

            [
                2008, 6, 30, 11, 5, null, null, null, null, "2008-06-30T11:05", null],
            [
                2008, 6, 30, 11, 5, 30, null, null, null, "2008-06-30T11:05:30", null],
            [
                2008, 6, 30, 11, 5, 30, 500000000, null, null, "2008-06-30T11:05:30.5", null],
            [
                2008, 6, 30, 11, 5, 30, 1, null, null, "2008-06-30T11:05:30.000000001", null],

            [
                2008, 6, 30, 11, 5, null, null, "+01:00", null, "2008-06-30T11:05+01:00", null],
            [
                2008, 6, 30, 11, 5, 30, null, "+01:00", null, "2008-06-30T11:05:30+01:00", null],
            [
                2008, 6, 30, 11, 5, 30, 500000000, "+01:00", null, "2008-06-30T11:05:30.5+01:00", null],
            [
                2008, 6, 30, 11, 5, 30, 1, "+01:00", null, "2008-06-30T11:05:30.000000001+01:00", null],

            [
                2008, 6, 30, 11, 5, null, null, "+01:00", "Europe/Paris", "2008-06-30T11:05+01:00[Europe/Paris]", null],
            [
                2008, 6, 30, 11, 5, 30, null, "+01:00", "Europe/Paris", "2008-06-30T11:05:30+01:00[Europe/Paris]", null],
            [
                2008, 6, 30, 11, 5, 30, 500000000, "+01:00", "Europe/Paris", "2008-06-30T11:05:30.5+01:00[Europe/Paris]", null],
            [
                2008, 6, 30, 11, 5, 30, 1, "+01:00", "Europe/Paris", "2008-06-30T11:05:30.000000001+01:00[Europe/Paris]", null],

            [
                2008, 6, 30, 11, 5, null, null, null, "Europe/Paris", "2008-06-30T11:05", null],
            [
                2008, 6, 30, 11, 5, 30, null, null, "Europe/Paris", "2008-06-30T11:05:30", null],
            [
                2008, 6, 30, 11, 5, 30, 500000000, null, "Europe/Paris", "2008-06-30T11:05:30.5", null],
            [
                2008, 6, 30, 11, 5, 30, 1, null, "Europe/Paris", "2008-06-30T11:05:30.000000001", null],

            [
                123456, 6, 30, 11, 5, null, null, null, null, "+123456-06-30T11:05", null],
        ];
    }

    /**
     * @dataProvider provider_sample_isoDateTime
     */
    public function test_print_isoDateTime(
        $year, $month, $day,
        $hour, $min, $sec, $nano, $offsetId, $zoneId,
        $expected, $expectedEx)
    {
        $test = $this->buildAccessor($year, $month, $day, $hour, $min, $sec, $nano, $offsetId, $zoneId);
        if ($expectedEx == null) {
            $this->assertEquals(DateTimeFormatter::ISO_DATE_TIME()->format($test), $expected);
        } else {
            try {
                DateTimeFormatter::ISO_DATE_TIME()->format($test);
                $this->fail();
            } catch (\Exception $ex) {
                $this->assertInstanceOf($expectedEx, $ex);
            }
        }
    }

    /**
     * @dataProvider provider_sample_isoDateTime
     */

    public function test_parse_isoDateTime(
        $year, $month, $day,
        $hour, $min, $sec, $nano, $offsetId, $zoneId,
        $input, $invalid)
    {
        if ($input !== null) {
            $expected = $this->createDateTime($year, $month, $day, $hour, $min, $sec, $nano);
            if ($offsetId !== null) {
                $expected->add(ZoneOffset::of($offsetId));
                if ($zoneId !== null) {
                    $expected->zone = ZoneId::of($zoneId);
                }
            }
            $this->assertParseMatch(DateTimeFormatter::ISO_DATE_TIME()->parseUnresolved($input, new ParsePosition(0)), $expected);
        } else {
            $this->assertTrue(true);
        }
    }


    public function test_isoDateTime_basics()
    {
        $this->assertEquals(DateTimeFormatter::ISO_DATE_TIME()->getChronology(), IsoChronology::INSTANCE());
        $this->assertEquals(DateTimeFormatter::ISO_DATE_TIME()->getZone(), null);
        $this->assertEquals(DateTimeFormatter::ISO_DATE_TIME()->getResolverStyle(), ResolverStyle::STRICT());
    }

    //-----------------------------------------------------------------------
    //-----------------------------------------------------------------------
    //-----------------------------------------------------------------------

    public function test_print_isoOrdinalDate()
    {
        $test = $this->buildAccessorDateTime(LocalDateTime::of(2008, 6, 3, 11, 5, 30), null, null);
        $this->assertEquals(DateTimeFormatter::ISO_ORDINAL_DATE()->format($test), "2008-155");
    }


    public function test_print_isoOrdinalDate_offset()
    {
        $test = $this->buildAccessorDateTime(LocalDateTime::of(2008, 6, 3, 11, 5, 30), "Z", null);
        $this->assertEquals(DateTimeFormatter::ISO_ORDINAL_DATE()->format($test), "2008-155Z");
    }


    public function test_print_isoOrdinalDate_zoned()
    {
        $test = $this->buildAccessorDateTime(LocalDateTime::of(2008, 6, 3, 11, 5, 30), "+02:00", "Europe/Paris");
        $this->assertEquals(DateTimeFormatter::ISO_ORDINAL_DATE()->format($test), "2008-155+02:00");
    }


    public function test_print_isoOrdinalDate_zoned_largeYear()
    {
        $test = $this->buildAccessorDateTime(LocalDateTime::of(123456, 6, 3, 11, 5, 30), "Z", null);
        $this->assertEquals(DateTimeFormatter::ISO_ORDINAL_DATE()->format($test), "+123456-155Z");
    }


    public function test_print_isoOrdinalDate_fields()
    {
        // $mock for testing that does not fully comply with TemporalAccessor contract
        $test = new TestAccessor();
        $this->assertEquals(DateTimeFormatter::ISO_ORDINAL_DATE()->format($test), "2008-231");
    }

    /**
     * @expectedException \Celest\DateTimeException
     */
    public function test_print_isoOrdinalDate_missingField()
    {
        $test = Year::of(2008);
        DateTimeFormatter::ISO_ORDINAL_DATE()->format($test);
    }

    //-----------------------------------------------------------------------

    public function test_parse_isoOrdinalDate()
    {
        $expected = new Expected(ChronoField::YEAR(), 2008, ChronoField::DAY_OF_YEAR(), 123);
        $this->assertParseMatch(DateTimeFormatter::ISO_ORDINAL_DATE()->parseUnresolved("2008-123", new ParsePosition(0)), $expected);
    }


    public function test_parse_isoOrdinalDate_largeYear()
    {
        $expected = new Expected(ChronoField::YEAR(), 123456, ChronoField::DAY_OF_YEAR(), 123);
        $this->assertParseMatch(DateTimeFormatter::ISO_ORDINAL_DATE()->parseUnresolved("+123456-123", new ParsePosition(0)), $expected);
    }


    public function test_isoOrdinalDate_basics()
    {
        $this->assertEquals(DateTimeFormatter::ISO_ORDINAL_DATE()->getChronology(), IsoChronology::INSTANCE());
        $this->assertEquals(DateTimeFormatter::ISO_ORDINAL_DATE()->getZone(), null);
        $this->assertEquals(DateTimeFormatter::ISO_ORDINAL_DATE()->getResolverStyle(), ResolverStyle::STRICT());
    }

    //-----------------------------------------------------------------------
    //-----------------------------------------------------------------------
    //-----------------------------------------------------------------------

    public function test_print_basicIsoDate()
    {
        $test = $this->buildAccessorDateTime(LocalDateTime::of(2008, 6, 3, 11, 5, 30), null, null);
        $this->assertEquals(DateTimeFormatter::BASIC_ISO_DATE()->format($test), "20080603");
    }


    public function test_print_basicIsoDate_offset()
    {
        $test = $this->buildAccessorDateTime(LocalDateTime::of(2008, 6, 3, 11, 5, 30), "Z", null);
        $this->assertEquals(DateTimeFormatter::BASIC_ISO_DATE()->format($test), "20080603Z");
    }


    public function test_print_basicIsoDate_zoned()
    {
        $test = $this->buildAccessorDateTime(LocalDateTime::of(2008, 6, 3, 11, 5, 30), "+02:00", "Europe/Paris");
        $this->assertEquals(DateTimeFormatter::BASIC_ISO_DATE()->format($test), "20080603+0200");
    }

    /**
     * @expectedException \Celest\DateTimeException
     */
    public function test_print_basicIsoDate_largeYear()
    {
        $test = $this->buildAccessorDateTime(LocalDateTime::of(123456, 6, 3, 11, 5, 30), "Z", null);
        DateTimeFormatter::BASIC_ISO_DATE()->format($test);
    }


    public function test_print_basicIsoDate_fields()
    {
        $test = $this->buildAccessorDate(LocalDate::of(2008, 6, 3), null, null);
        $this->assertEquals(DateTimeFormatter::BASIC_ISO_DATE()->format($test), "20080603");
    }

    /**
     * @expectedException \Celest\DateTimeException
     */
    public function test_print_basicIsoDate_missingField()
    {
        $test = YearMonth::of(2008, 6);
        DateTimeFormatter::BASIC_ISO_DATE()->format($test);
    }

    //-----------------------------------------------------------------------

    public function test_parse_basicIsoDate()
    {
        $expected = LocalDate::of(2008, 6, 3);
        $this->assertEquals(DateTimeFormatter::BASIC_ISO_DATE()->parseQuery("20080603", TemporalQueries::fromCallable([LocalDate::class, 'from'])), $expected);
    }

    /**
     * @expectedException \Celest\DateTimeParseException
     */
    public function test_parse_basicIsoDate_largeYear()
    {
        try {
            $expected = LocalDate::of(123456, 6, 3);
            $this->assertEquals(DateTimeFormatter::BASIC_ISO_DATE()->parseQuery("+1234560603", TemporalQueries::fromCallable([LocalDate::class, 'from'])), $expected);
        } catch (DateTimeParseException $ex) {
            $this->assertEquals($ex->getErrorIndex(), 0);
            $this->assertEquals($ex->getParsedString(), "+1234560603");
            throw $ex;
        }
    }


    public function test_basicIsoDate_basics()
    {
        $this->assertEquals(DateTimeFormatter::BASIC_ISO_DATE()->getChronology(), IsoChronology::INSTANCE());
        $this->assertEquals(DateTimeFormatter::BASIC_ISO_DATE()->getZone(), null);
        $this->assertEquals(DateTimeFormatter::BASIC_ISO_DATE()->getResolverStyle(), ResolverStyle::STRICT());
    }

    //-----------------------------------------------------------------------
    //-----------------------------------------------------------------------
    //-----------------------------------------------------------------------
    function provider_weekDate()
    {
        $date = ZonedDateTime::ofDateTime(LocalDateTime::of(2003, 12, 29, 11, 5, 30), ZoneId::of("Europe/Paris"));
        $endDate = $date->withYear(2005)->withMonth(1)->withDayOfMonth(2);
        $week = 1;
        $day = 1;
        $ret = [];

        while (!$date->isAfter($endDate)) {
            $sb = "2004-W";
            if ($week < 10) {
                $sb .= '0';
            }
            $sb .= $week . '-' . $day . $date->getOffset();
            $ret[] = [$date, $sb];
            $date = $date->plusDays(1);
            $day += 1;
            if ($day == 8) {
                $day = 1;
                $week++;
            }
        }

        return $ret;
    }

    /**
     * @dataProvider provider_weekDate
     */
    public function test_print_isoWeekDate($test, $expected)
    {
        $this->assertEquals(DateTimeFormatter::ISO_WEEK_DATE()->format($test), $expected);
    }


    public function test_print_isoWeekDate_zoned_largeYear()
    {
        $test = $this->buildAccessorDateTime(LocalDateTime::of(123456, 6, 3, 11, 5, 30), "Z", null);
        $this->assertEquals(DateTimeFormatter::ISO_WEEK_DATE()->format($test), "+123456-W23-2Z");
    }


    public function test_print_isoWeekDate_fields()
    {
        $test = $this->buildAccessorDate(LocalDate::of(2004, 1, 27), null, null);
        $this->assertEquals(DateTimeFormatter::ISO_WEEK_DATE()->format($test), "2004-W05-2");
    }

    /**
     * @expectedException \Celest\DateTimeException
     */
    public function test_print_isoWeekDate_missingField()
    {
        $test = YearMonth::of(2008, 6);
        DateTimeFormatter::ISO_WEEK_DATE()->format($test);
    }

    //-----------------------------------------------------------------------

    public function test_parse_weekDate()
    {
        $expected = LocalDate::of(2004, 1, 28);
        $this->assertEquals(DateTimeFormatter::ISO_WEEK_DATE()->parseQuery("2004-W05-3", TemporalQueries::fromCallable([LocalDate::class, 'from'])), $expected);
    }


    public function test_parse_weekDate_largeYear()
    {
        $parsed = DateTimeFormatter::ISO_WEEK_DATE()->parseUnresolved("+123456-W04-5", new ParsePosition(0));
        $this->assertEquals($parsed->getLong(IsoFields::WEEK_BASED_YEAR()), 123456);
        $this->assertEquals($parsed->getLong(IsoFields::WEEK_OF_WEEK_BASED_YEAR()), 4);
        $this->assertEquals($parsed->getLong(ChronoField::DAY_OF_WEEK()), 5);
    }


    public function test_isoWeekDate_basics()
    {
        $this->assertEquals(DateTimeFormatter::ISO_WEEK_DATE()->getChronology(), IsoChronology::INSTANCE());
        $this->assertEquals(DateTimeFormatter::ISO_WEEK_DATE()->getZone(), null);
        $this->assertEquals(DateTimeFormatter::ISO_WEEK_DATE()->getResolverStyle(), ResolverStyle::STRICT());
    }

    //-----------------------------------------------------------------------
    //-----------------------------------------------------------------------
    //-----------------------------------------------------------------------
    function provider_sample_isoInstant()
    {
        return [
            [
                0, 0, "1970-01-01T00:00:00Z", null],
            [
                0, null, "1970-01-01T00:00:00Z", null],
            [
                0, -1, null, DateTimeException::class],

            [
                -1, 0, "1969-12-31T23:59:59Z", null],
            [
                1, 0, "1970-01-01T00:00:01Z", null],
            [
                60, 0, "1970-01-01T00:01:00Z", null],
            [
                3600, 0, "1970-01-01T01:00:00Z", null],
            [
                86400, 0, "1970-01-02T00:00:00Z", null],

            [
                0, 1, "1970-01-01T00:00:00.000000001Z", null],
            [
                0, 2, "1970-01-01T00:00:00.000000002Z", null],
            [
                0, 10, "1970-01-01T00:00:00.000000010Z", null],
            [
                0, 100, "1970-01-01T00:00:00.000000100Z", null],
        ];
    }

    /**
     * @dataProvider provider_sample_isoInstant
     */
    public function test_print_isoInstant(
        $instantSecs, $nano, $expected, $expectedEx)
    {
        $test = $this->buildAccessorInstant($instantSecs, $nano);
        if ($expectedEx == null) {
            $this->assertEquals(DateTimeFormatter::ISO_INSTANT()->format($test), $expected);
        } else {
            try {
                DateTimeFormatter::ISO_INSTANT()->format($test);
                $this->fail();
            } catch (\Exception $ex) {
                $this->assertInstanceOf($expectedEx, $ex);
            }
        }
    }

    /**
     * @dataProvider provider_sample_isoInstant
     */
    public function test_parse_isoInstant(
        $instantSecs, $nano, $input, $invalid)
    {
        if ($input !== null) {
            $parsed = DateTimeFormatter::ISO_INSTANT()->parseUnresolved($input, new ParsePosition(0));
            $this->assertEquals($parsed->getLong(ChronoField::INSTANT_SECONDS()), $instantSecs);
            $this->assertEquals($parsed->getLong(ChronoField::NANO_OF_SECOND()), ($nano == null ? 0 : $nano));
        } else {
            $this->assertTrue(true);
        }
    }


    public function test_isoInstant_basics()
    {
        $this->assertEquals(DateTimeFormatter::ISO_INSTANT()->getChronology(), null);
        $this->assertEquals(DateTimeFormatter::ISO_INSTANT()->getZone(), null);
        $this->assertEquals(DateTimeFormatter::ISO_INSTANT()->getResolverStyle(), ResolverStyle::STRICT());
    }

    //-----------------------------------------------------------------------
    //-----------------------------------------------------------------------
    //-----------------------------------------------------------------------
    function data_rfc()
    {
        return
            [
                [
                    LocalDateTime::of(2008, 6, 3, 11, 5, 30), "Z", "Tue, 3 Jun 2008 11:05:30 GMT"],
                [
                    LocalDateTime::of(2008, 6, 30, 11, 5, 30), "Z", "Mon, 30 Jun 2008 11:05:30 GMT"],
                [
                    LocalDateTime::of(2008, 6, 3, 11, 5, 30), "+02:00", "Tue, 3 Jun 2008 11:05:30 +0200"],
                [
                    LocalDateTime::of(2008, 6, 30, 11, 5, 30), "-03:00", "Mon, 30 Jun 2008 11:05:30 -0300"],
            ];
    }

    /**
     * @dataProvider data_rfc
     */
    public function test_print_rfc1123(LocalDateTime $base, $offsetId, $expected)
    {
        $test = $this->buildAccessorDateTime($base, $offsetId, null);
        $this->assertEquals(DateTimeFormatter::RFC_1123_DATE_TIME()->format($test), $expected);
    }

    /**
     * @dataProvider data_rfc
     */
    public function test_print_rfc1123_french(LocalDateTime $base, $offsetId, $expected)
    {
        $test = $this->buildAccessorDateTime($base, $offsetId, null);
        $this->assertEquals(DateTimeFormatter::RFC_1123_DATE_TIME()->withLocale(Locale::FRENCH())->format($test), $expected);
    }

    /**
     * @expectedException \Celest\DateTimeException
     */
    public function test_print_rfc1123_missingField()
    {
        $test = YearMonth::of(2008, 6);
        DateTimeFormatter::RFC_1123_DATE_TIME()->format($test);
    }


    public function test_rfc1123_basics()
    {
        $this->assertEquals(DateTimeFormatter::RFC_1123_DATE_TIME()->getChronology(), IsoChronology::INSTANCE());
        $this->assertEquals(DateTimeFormatter::RFC_1123_DATE_TIME()->getZone(), null);
        $this->assertEquals(DateTimeFormatter::RFC_1123_DATE_TIME()->getResolverStyle(), ResolverStyle::SMART());
    }

    //-----------------------------------------------------------------------
    //-----------------------------------------------------------------------
    //-----------------------------------------------------------------------
    private function createDate($year, $month, $day)
    {
        $test = new Expected();
        if ($year !== null) {
            $test->fieldValues->put(ChronoField::YEAR(), $year);
        }

        if ($month !== null) {
            $test->fieldValues->put(ChronoField::MONTH_OF_YEAR(), $month);
        }
        if ($day !== null) {
            $test->fieldValues->put(ChronoField::DAY_OF_MONTH(), $day);
        }
        return $test;
    }

    private function createTime($hour, $min, $sec, $nano)
    {
        $test = new Expected();
        if ($hour !== null) {
            $test->fieldValues->put(ChronoField::HOUR_OF_DAY(), $hour);
        }
        if ($min !== null) {
            $test->fieldValues->put(ChronoField::MINUTE_OF_HOUR(), $min);
        }
        if ($sec !== null) {
            $test->fieldValues->put(ChronoField::SECOND_OF_MINUTE(), $sec);
        }
        if ($nano !== null) {
            $test->fieldValues->put(ChronoField::NANO_OF_SECOND(), $nano);
        }
        return $test;
    }

    private function createDateTime(
        $year, $month, $day,
        $hour, $min, $sec, $nano)
    {
        $test = new Expected();
        if ($year !== null) {
            $test->fieldValues->put(ChronoField::YEAR(), $year);
        }
        if ($month !== null) {
            $test->fieldValues->put(ChronoField::MONTH_OF_YEAR(), $month);
        }
        if ($day !== null) {
            $test->fieldValues->put(ChronoField::DAY_OF_MONTH(), $day);
        }
        if ($hour !== null) {
            $test->fieldValues->put(ChronoField::HOUR_OF_DAY(), $hour);
        }
        if ($min !== null) {
            $test->fieldValues->put(ChronoField::MINUTE_OF_HOUR(), $min);
        }
        if ($sec !== null) {
            $test->fieldValues->put(ChronoField::SECOND_OF_MINUTE(), $sec);
        }
        if ($nano !== null) {
            $test->fieldValues->put(ChronoField::NANO_OF_SECOND(), $nano);
        }
        return $test;
    }

    private function buildAccessor(
        $year, $month, $day,
        $hour, $min, $sec, $nano,
        $offsetId, $zoneId)
    {
        $mock = new MockAccessor();
        if ($year !== null) {
            $mock->fields->put(ChronoField::YEAR(), $year);
        }
        if ($month !== null) {
            $mock->fields->put(ChronoField::MONTH_OF_YEAR(), $month);
        }
        if ($day !== null) {
            $mock->fields->put(ChronoField::DAY_OF_MONTH(), $day);
        }
        if ($hour !== null) {
            $mock->fields->put(ChronoField::HOUR_OF_DAY(), $hour);
        }
        if ($min !== null) {
            $mock->fields->put(ChronoField::MINUTE_OF_HOUR(), $min);
        }
        if ($sec !== null) {
            $mock->fields->put(ChronoField::SECOND_OF_MINUTE(), $sec);
        }
        if ($nano !== null) {
            $mock->fields->put(ChronoField::NANO_OF_SECOND(), $nano);
        }
        $mock->setOffset($offsetId);
        $mock->setZone($zoneId);
        return $mock;
    }

    private function buildAccessorDateTime(LocalDateTime $base, $offsetId, $zoneId)
    {
        $mock = new MockAccessor();
        $mock->setFieldsDateTime($base);
        $mock->setOffset($offsetId);
        $mock->setZone($zoneId);
        return $mock;
    }

    private function buildAccessorDate(LocalDate $base, $offsetId, $zoneId)
    {
        $mock = new MockAccessor();
        $mock->setFields($base);
        $mock->setOffset($offsetId);
        $mock->setZone($zoneId);
        return $mock;
    }

    private function buildAccessorInstant($instantSecs, $nano)
    {
        $mock = new MockAccessor();
        $mock->fields->put(ChronoField::INSTANT_SECONDS(), $instantSecs);
        if ($nano !== null) {
            $mock->fields->put(ChronoField::NANO_OF_SECOND(), $nano);
        }
        return $mock;
    }

    /**
     * @param Expected $expected
     * @param $offsetId
     * @param $zoneId
     * @throws DateTimeException
     */
    private function buildCalendrical($expected, $offsetId, $zoneId)
    {
        if ($offsetId !== null) {
            $expected->add(ZoneOffset::of($offsetId));
        }
        if ($zoneId !== null) {
            $expected->zone = ZoneId::of($zoneId);
        }
    }

    /**
     * @param TemporalAccessor $parsed
     * @param Expected $expected
     */
    private function assertParseMatch($parsed, $expected)
    {
        foreach ($expected->fieldValues as $field => $val) {
            $this->assertEquals($parsed->isSupported($field), true);
            $parsed->getLong($field);
        }

        $this->assertEquals($expected->chrono, $parsed->query(TemporalQueries::chronology()));
        $this->assertEquals($expected->zone, $parsed->query(TemporalQueries::zoneId()));
    }
}
