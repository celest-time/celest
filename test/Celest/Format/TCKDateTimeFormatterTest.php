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

use Celest\Chrono\IsoChronology;
use Celest\Chrono\ThaiBuddhistChronology;
use Celest\DateTimeException;
use Celest\DateTimeParseException;
use Celest\DayOfWeek;
use Celest\Instant;
use Celest\LocalDate;
use Celest\LocalDateTime;
use Celest\Locale;
use Celest\LocalTime;
use Celest\OffsetDateTime;
use Celest\OffsetTime;
use Celest\Temporal\ChronoField as CF;
use Celest\Temporal\IsoFields;
use Celest\Temporal\TemporalAccessor;
use Celest\Temporal\TemporalField;
use Celest\Temporal\TemporalQueries;
use Celest\Temporal\TemporalQuery;
use Celest\Temporal\UnsupportedTemporalTypeException;
use Celest\TestHelper;
use Celest\YearMonth;
use Celest\ZonedDateTime;
use Celest\ZoneId;
use Celest\ZoneOffset;
use PHPUnit\Framework\TestCase;

class format_withChronology_nonChronoFieldMapLink implements TemporalAccessor
{

    public function range(TemporalField $field)
    {
    }

    public function get(TemporalField $field)
    {
    }

    public function query(TemporalQuery $query)
    {
    }

    public function __toString()
    {
        return '';
    }

    public function isSupported(TemporalField $field)
    {
        return $field == IsoFields::WEEK_BASED_YEAR();
    }

    public function getLong(TemporalField $field)
    {
        if ($field == IsoFields::WEEK_BASED_YEAR()) {
            return 2345;
        }
        throw new UnsupportedTemporalTypeException("Unsupported field: " . $field);
    }
}

;

/**
 * Test DateTimeFormatter.
 */
class TCKDateTimeFormatterTest extends TestCase
{
    private static function OFFSET_PONE()
    {
        return ZoneOffset::ofHours(1);
    }

    private static function OFFSET_PTHREE()
    {
        return ZoneOffset::ofHours(3);
    }

    private static function ZONE_PARIS()
    {
        return ZoneId::of("Europe/Paris");
    }

    private static function BASIC_FORMATTER()
    {
        return DateTimeFormatter::ofPattern("'ONE'd");
    }

    private static function DATE_FORMATTER()
    {
        return DateTimeFormatter::ofPattern("'ONE'yyyy MM dd");
    }

    /** @var DateTimeFormatter */
    private $fmt;

    public function setUp()
    {
        $this->fmt = (new DateTimeFormatterBuilder())->appendLiteral2("ONE")
            ->appendValue3(CF::DAY_OF_MONTH(), 1, 2, SignStyle::NOT_NEGATIVE())
            ->toFormatter();
    }

    //-----------------------------------------------------------------------

    public function test_withLocale()
    {
        $base = $this->fmt->withLocale(Locale::ENGLISH())->withDecimalStyle(DecimalStyle::STANDARD());
        $test = $base->withLocale(Locale::GERMAN());
        $this->assertEquals($test->getLocale(), Locale::GERMAN());
    }

    public function test_withLocale_null()
    {
        TestHelper::assertNullException($this, function () {
            $base = $this->fmt->withLocale(Locale::ENGLISH())->withDecimalStyle(DecimalStyle::STANDARD());
            $base->withLocale(null);
        });
    }

    //-----------------------------------------------------------------------

    public function test_withChronology()
    {
        $test = $this->fmt;
        $this->assertEquals($test->getChronology(), null);
        $test = $test->withChronology(IsoChronology::INSTANCE());
        $this->assertEquals($test->getChronology(), IsoChronology::INSTANCE());
        $test = $test->withChronology(null);
        $this->assertEquals($test->getChronology(), null);
    }

    //-----------------------------------------------------------------------

    public function test_withZone()
    {
        $test = $this->fmt;
        $this->assertEquals($test->getZone(), null);
        $test = $test->withZone(ZoneId::of("Europe/Paris"));
        $this->assertEquals($test->getZone(), ZoneId::of("Europe/Paris"));
        $test = $test->withZone(ZoneOffset::UTC());
        $this->assertEquals($test->getZone(), ZoneOffset::UTC());
        $test = $test->withZone(null);
        $this->assertEquals($test->getZone(), null);
    }

    //-----------------------------------------------------------------------

    public function test_resolverFields_selectOneDateResolveYMD()
    {
        $base = (new DateTimeFormatterBuilder())
            ->appendValue(CF::YEAR())->appendLiteral('-')->appendValue(CF::MONTH_OF_YEAR())->appendLiteral('-')
            ->appendValue(CF::DAY_OF_MONTH())->appendLiteral('-')->appendValue(CF::DAY_OF_YEAR())->toFormatter();
        $f = $base->withResolverFields(CF::YEAR(), CF::MONTH_OF_YEAR(), CF::DAY_OF_MONTH());
        try {
            $base->parseQuery("2012-6-30-321", TemporalQueries::fromCallable([LocalDate::class, 'from']));  // wrong day-of-year
            $this->fail();
        } catch
        (DateTimeException $ex) {
            // $expected, fails as it produces two different dates
        }
        $parsed = $f->parseQuery("2012-6-30-321", TemporalQueries::fromCallable([LocalDate::class, 'from']));  // ignored day-of-year
        $this->assertEquals($parsed, LocalDate::of(2012, 6, 30));
    }


    public function test_resolverFields_selectOneDateResolveYD()
    {
        $base = (new DateTimeFormatterBuilder())
            ->appendValue(CF::YEAR())->appendLiteral('-')->appendValue(CF::MONTH_OF_YEAR())->appendLiteral('-')
            ->appendValue(CF::DAY_OF_MONTH())->appendLiteral('-')->appendValue(CF::DAY_OF_YEAR())->toFormatter();
        $f = $base->withResolverFields(CF::YEAR(), CF::DAY_OF_YEAR());
        $expected = [CF::YEAR(), CF::DAY_OF_YEAR()];
        // Use set.equals();  testNG comparison of Collections is ordered
        $this->assertTrue($f->getResolverFields() == $expected, "ResolveFields: " . print_r($f->getResolverFields(), true));
        try {
            $base->parseQuery("2012-6-30-321", TemporalQueries::fromCallable([LocalDate::class, 'from']));  // wrong month/day-of-month
            $this->fail();
        } catch (DateTimeException $ex) {
            // $expected, fails as it produces two different dates
        }
        $parsed = $f->parseQuery("2012-6-30-321", TemporalQueries::fromCallable([LocalDate::class, 'from']));  // ignored month/day-of-month
        $this->assertEquals($parsed, LocalDate::of(2012, 11, 16));
    }


    public function test_resolverFields_ignoreCrossCheck()
    {
        $base = (new DateTimeFormatterBuilder())
            ->appendValue(CF::YEAR())->appendLiteral('-')->appendValue(CF::DAY_OF_YEAR())->appendLiteral('-')
            ->appendValue(CF::DAY_OF_WEEK())->toFormatter();
        $f = $base->withResolverFields(CF::YEAR(), CF::DAY_OF_YEAR());
        try {
            $base->parseQuery("2012-321-1", TemporalQueries::fromCallable([LocalDate::class, 'from']));  // wrong day-of-week
            $this->fail();
        } catch (DateTimeException $ex) {
            // $expected, should $this->fail() in cross-check of day-of-week
        }
        $parsed = $f->parseQuery("2012-321-1", TemporalQueries::fromCallable([LocalDate::class, 'from']));  // ignored wrong day-of-week
        $this->assertEquals($parsed, LocalDate::of(2012, 11, 16));
    }


    public function test_resolverFields_emptyList()
    {
        $f = (new DateTimeFormatterBuilder())
            ->appendValue(CF::YEAR())->toFormatter()->withResolverFields();
        $parsed = $f->parse("2012");
        $this->assertEquals($parsed->isSupported(CF::YEAR()), false);  // not in the list of resolverFields
    }


    public function test_resolverFields_listOfOneMatching()
    {
        $f = (new DateTimeFormatterBuilder())
            ->appendValue(CF::YEAR())->toFormatter()->withResolverFields(CF::YEAR());
        $parsed = $f->parse("2012");
        $this->assertEquals($parsed->isSupported(CF::YEAR()), true);
    }


    public function test_resolverFields_listOfOneNotMatching()
    {
        $f = (new DateTimeFormatterBuilder())
            ->appendValue(CF::YEAR())->toFormatter()->withResolverFields(CF::MONTH_OF_YEAR());
        $parsed = $f->parse("2012");
        $this->assertEquals($parsed->isSupported(CF::YEAR()), false);  // not in the list of resolverFields
        $this->assertEquals($parsed->isSupported(CF::MONTH_OF_YEAR()), false);
    }

    public function test_resolverFields_listOfOneNull()
    {
        $f = (new DateTimeFormatterBuilder())
            ->appendValue(CF::YEAR())->toFormatter()->withResolverFields();
        $parsed = $f->parse("2012");
        $this->assertEquals($parsed->isSupported(CF::YEAR()), false);  // not in the list of resolverFields
    }


    public function test_resolverFields_Array_null()
    {
        $f = DateTimeFormatter::ISO_DATE()->withResolverFields(CF::MONTH_OF_YEAR());
        $this->assertEquals(count($f->getResolverFields()), 1);
        $f = $f->withResolverFields(null);
        $this->assertEquals($f->getResolverFields(), null);
    }


    public function test_resolverFields_Set_null()
    {
        $f = DateTimeFormatter::ISO_DATE()->withResolverFields(CF::MONTH_OF_YEAR());
        $this->assertEquals(count($f->getResolverFields()), 1);
        $f = $f->withResolverFields2(null);
        $this->assertEquals($f->getResolverFields(), null);
    }

    //-----------------------------------------------------------------------
    // format
    //-----------------------------------------------------------------------
    function data_format_withZone_withChronology()
    {
        $ym = YearMonth::of(2008, 6);
        $ld = LocalDate::of(2008, 6, 30);
        $lt = LocalTime::of(11, 30);
        $ldt = LocalDateTime::of(2008, 6, 30, 11, 30);
        $ot = OffsetTime::ofLocalTime(LocalTime::of(11, 30), self::OFFSET_PONE());
        $odt = OffsetDateTime::ofDateTime(LocalDateTime::of(2008, 6, 30, 11, 30), self::OFFSET_PONE());
        $zdt = ZonedDateTime::ofDateTime(LocalDateTime::of(2008, 6, 30, 11, 30), self::ZONE_PARIS());
        $thaiZdt = ThaiBuddhistChronology::INSTANCE()->zonedDateTimeFrom($zdt);
        $instant = Instant::ofEpochSecond(3600);
        return [
            [null, null, DayOfWeek::MONDAY(), "::::"],
            [null, null, $ym, "2008::::ISO"],
            [null, null, $ld, "2008::::ISO"],
            [null, null, $lt, ":11:::"],
            [null, null, $ldt, "2008:11:::ISO"],
            [null, null, $ot, ":11:+01:00::"],
            [null, null, $odt, "2008:11:+01:00::ISO"],
            [null, null, $zdt, "2008:11:+02:00:Europe/Paris:ISO"],
            [null, null, $instant, "::::"],

            [IsoChronology::INSTANCE(), null, DayOfWeek::MONDAY(), "::::ISO"],
            [IsoChronology::INSTANCE(), null, $ym, "2008::::ISO"],
            [IsoChronology::INSTANCE(), null, $ld, "2008::::ISO"],
            [IsoChronology::INSTANCE(), null, $lt, ":11:::ISO"],
            [IsoChronology::INSTANCE(), null, $ldt, "2008:11:::ISO"],
            [IsoChronology::INSTANCE(), null, $ot, ":11:+01:00::ISO"],
            [IsoChronology::INSTANCE(), null, $odt, "2008:11:+01:00::ISO"],
            [IsoChronology::INSTANCE(), null, $zdt, "2008:11:+02:00:Europe/Paris:ISO"],
            [IsoChronology::INSTANCE(), null, $instant, "::::ISO"],

            [null, self::ZONE_PARIS(), DayOfWeek::MONDAY(), ":::Europe/Paris:"],
            [null, self::ZONE_PARIS(), $ym, "2008:::Europe/Paris:ISO"],
            [null, self::ZONE_PARIS(), $ld, "2008:::Europe/Paris:ISO"],
            [null, self::ZONE_PARIS(), $lt, ":11::Europe/Paris:"],
            [null, self::ZONE_PARIS(), $ldt, "2008:11::Europe/Paris:ISO"],
            [null, self::ZONE_PARIS(), $ot, ":11:+01:00:Europe/Paris:"],
            [null, self::ZONE_PARIS(), $odt, "2008:12:+02:00:Europe/Paris:ISO"],
            [null, self::ZONE_PARIS(), $zdt, "2008:11:+02:00:Europe/Paris:ISO"],
            [null, self::ZONE_PARIS(), $instant, "1970:02:+01:00:Europe/Paris:ISO"],

            [null, self::OFFSET_PTHREE(), DayOfWeek::MONDAY(), ":::+03:00:"],
            [null, self::OFFSET_PTHREE(), $ym, "2008:::+03:00:ISO"],
            [null, self::OFFSET_PTHREE(), $ld, "2008:::+03:00:ISO"],
            [null, self::OFFSET_PTHREE(), $lt, ":11::+03:00:"],
            [null, self::OFFSET_PTHREE(), $ldt, "2008:11::+03:00:ISO"],
            [null, self::OFFSET_PTHREE(), $ot, null],  // offset and zone clash
            [null, self::OFFSET_PTHREE(), $odt, "2008:13:+03:00:+03:00:ISO"],
            [null, self::OFFSET_PTHREE(), $zdt, "2008:12:+03:00:+03:00:ISO"],
            [null, self::OFFSET_PTHREE(), $instant, "1970:04:+03:00:+03:00:ISO"],

            [ThaiBuddhistChronology::INSTANCE(), null, DayOfWeek::MONDAY(), null],  // not a complete date
            [ThaiBuddhistChronology::INSTANCE(), null, $ym, null],  // not a complete date
            [ThaiBuddhistChronology::INSTANCE(), null, $ld, "2551::::ThaiBuddhist"],
            [ThaiBuddhistChronology::INSTANCE(), null, $lt, ":11:::ThaiBuddhist"],
            [ThaiBuddhistChronology::INSTANCE(), null, $ldt, "2551:11:::ThaiBuddhist"],
            [ThaiBuddhistChronology::INSTANCE(), null, $ot, ":11:+01:00::ThaiBuddhist"],
            [ThaiBuddhistChronology::INSTANCE(), null, $odt, "2551:11:+01:00::ThaiBuddhist"],
            [ThaiBuddhistChronology::INSTANCE(), null, $zdt, "2551:11:+02:00:Europe/Paris:ThaiBuddhist"],
            [ThaiBuddhistChronology::INSTANCE(), null, $instant, "::::ThaiBuddhist"],

            [ThaiBuddhistChronology::INSTANCE(), null, DayOfWeek::MONDAY(), null],  // not a complete date
            [ThaiBuddhistChronology::INSTANCE(), self::ZONE_PARIS(), $ym, null],  // not a complete date
            [ThaiBuddhistChronology::INSTANCE(), self::ZONE_PARIS(), $ld, "2551:::Europe/Paris:ThaiBuddhist"],
            [ThaiBuddhistChronology::INSTANCE(), self::ZONE_PARIS(), $lt, ":11::Europe/Paris:ThaiBuddhist"],
            [ThaiBuddhistChronology::INSTANCE(), self::ZONE_PARIS(), $ldt, "2551:11::Europe/Paris:ThaiBuddhist"],
            [ThaiBuddhistChronology::INSTANCE(), self::ZONE_PARIS(), $ot, ":11:+01:00:Europe/Paris:ThaiBuddhist"],
            [ThaiBuddhistChronology::INSTANCE(), self::ZONE_PARIS(), $odt, "2551:12:+02:00:Europe/Paris:ThaiBuddhist"],
            [ThaiBuddhistChronology::INSTANCE(), self::ZONE_PARIS(), $zdt, "2551:11:+02:00:Europe/Paris:ThaiBuddhist"],
            [ThaiBuddhistChronology::INSTANCE(), self::ZONE_PARIS(), $instant, "2513:02:+01:00:Europe/Paris:ThaiBuddhist"],

            [null, self::ZONE_PARIS(), $thaiZdt, "2551:11:+02:00:Europe/Paris:ThaiBuddhist"],
            [ThaiBuddhistChronology::INSTANCE(), self::ZONE_PARIS(), $thaiZdt, "2551:11:+02:00:Europe/Paris:ThaiBuddhist"],
            [IsoChronology::INSTANCE(), self::ZONE_PARIS(), $thaiZdt, "2008:11:+02:00:Europe/Paris:ISO"],
        ];
    }

    /**
     * @dataProvider data_format_withZone_withChronology
     */
    public function test_format_withZone_withChronology($overrideChrono, $overrideZone, $temporal, $expected)
    {
        $test = (new DateTimeFormatterBuilder())
            ->optionalStart()->appendValue2(CF::YEAR(), 4)->optionalEnd()
            ->appendLiteral(':')->optionalStart()->appendValue2(CF::HOUR_OF_DAY(), 2)->optionalEnd()
            ->appendLiteral(':')->optionalStart()->appendOffsetId()->optionalEnd()
            ->appendLiteral(':')->optionalStart()->appendZoneId()->optionalEnd()
            ->appendLiteral(':')->optionalStart()->appendChronologyId()->optionalEnd()
            ->toFormatter2(Locale::ENGLISH())
            ->withChronology($overrideChrono)->withZone($overrideZone);
        if ($expected !== null) {
            $result = $test->format($temporal);
            $this->assertEquals($result, $expected);
        } else {
            try {
                $test->format($temporal);
                $this->fail("Formatting should have failed");
            } catch (DateTimeException $ex) {
                // $expected
            }
        }
    }


    public function test_format_withChronology_nonChronoFieldMapLink()
    {
        $temporal = new format_withChronology_nonChronoFieldMapLink();
        $test = (new DateTimeFormatterBuilder())
            ->appendValue2(IsoFields::WEEK_BASED_YEAR(), 4)
            ->toFormatter2(Locale::ENGLISH())
            ->withChronology(IsoChronology::INSTANCE());
        $result = $test->format($temporal);
        $this->assertEquals($result, "2345");
    }

//-----------------------------------------------------------------------

    public function test_format_TemporalAccessor_simple()
    {
        $test = $this->fmt->withLocale(Locale::ENGLISH())->withDecimalStyle(DecimalStyle::STANDARD());
        $result = $test->format(LocalDate::of(2008, 6, 30));
        $this->assertEquals($result, "ONE30");
    }

    /**
     * @expectedException \Celest\DateTimeException
     */
    public function test_format_TemporalAccessor_noSuchField()
    {
        $test = $this->fmt->withLocale(Locale::ENGLISH())->withDecimalStyle(DecimalStyle::STANDARD());
        $test->format(LocalTime::of(11, 30));
    }

    public function test_format_TemporalAccessor_null()
    {
        TestHelper::assertNullException($this, function () {
            $test = $this->fmt->withLocale(Locale::ENGLISH())->withDecimalStyle(DecimalStyle::STANDARD());
            $test->format(null);
        });
    }

    //-----------------------------------------------------------------------

    public function test_print_TemporalAppendable()
    {
        $test = $this->fmt->withLocale(Locale::ENGLISH())->withDecimalStyle(DecimalStyle::STANDARD());
        $test->formatTo(LocalDate::of(2008, 6, 30), $buf);
        $this->assertEquals($buf, "ONE30");
    }

    /**
     * @expectedException \Celest\DateTimeException
     */
    public function test_print_TemporalAppendable_noSuchField()
    {
        $test = $this->fmt->withLocale(Locale::ENGLISH())->withDecimalStyle(DecimalStyle::STANDARD());
        $test->formatTo(LocalTime::of(11, 30), $buf);
    }

    public function test_print_TemporalAppendable_nullTemporal()
    {
        TestHelper::assertNullException($this, function () {
            $test = $this->fmt->withLocale(Locale::ENGLISH())->withDecimalStyle(DecimalStyle::STANDARD());
            $test->formatTo(null, $buf);
        });
    }

    //-----------------------------------------------------------------------
    // parse(CharSequence)
    //-----------------------------------------------------------------------

    public function test_parse_CharSequence()
    {
        $test = $this->fmt->withLocale(Locale::ENGLISH())->withDecimalStyle(DecimalStyle::STANDARD());
        $result = $test->parse("ONE30");
        $this->assertEquals($result->isSupported(CF::DAY_OF_MONTH()), true);
        $this->assertEquals($result->getLong(CF::DAY_OF_MONTH()), 30);
        $this->assertEquals($result->isSupported(CF::HOUR_OF_DAY()), false);
    }


    public function test_parse_CharSequence_resolved()
    {
        $test = DateTimeFormatter::ISO_DATE();
        $result = $test->parse("2012-06-30");
        $this->assertEquals($result->isSupported(CF::YEAR()), true);
        $this->assertEquals($result->isSupported(CF::MONTH_OF_YEAR()), true);
        $this->assertEquals($result->isSupported(CF::DAY_OF_MONTH()), true);
        $this->assertEquals($result->isSupported(CF::HOUR_OF_DAY()), false);
        $this->assertEquals($result->getLong(CF::YEAR()), 2012);
        $this->assertEquals($result->getLong(CF::MONTH_OF_YEAR()), 6);
        $this->assertEquals($result->getLong(CF::DAY_OF_MONTH()), 30);
        $this->assertEquals($result->query(TemporalQueries::fromCallable([LocalDate::class, 'from'])), LocalDate::of(2012, 6, 30));
    }

    public function test_parse_CharSequence_null()
    {
        TestHelper::assertNullException($this, function () {
            $test = $this->fmt->withLocale(Locale::ENGLISH())->withDecimalStyle(DecimalStyle::STANDARD());
            $test->parse(null);
        });
    }

    //-----------------------------------------------------------------------
    // parse(CharSequence)
    //-----------------------------------------------------------------------

    public function test_parse_CharSequence_ParsePosition()
    {
        $test = $this->fmt->withLocale(Locale::ENGLISH())->withDecimalStyle(DecimalStyle::STANDARD());
        $pos = new ParsePosition(3);
        $result = $test->parsePos("XXXONE30XXX", $pos);
        $this->assertEquals($pos->getIndex(), 8);
        $this->assertEquals($pos->getErrorIndex(), -1);
        $this->assertEquals($result->isSupported(CF::DAY_OF_MONTH()), true);
        $this->assertEquals($result->getLong(CF::DAY_OF_MONTH()), 30);
        $this->assertEquals($result->isSupported(CF::HOUR_OF_DAY()), false);
    }


    public function test_parse_CharSequence_ParsePosition_resolved()
    {
        $test = DateTimeFormatter::ISO_DATE();
        $pos = new ParsePosition(3);
        $result = $test->parsePos("XXX2012-06-30XXX", $pos);
        $this->assertEquals($pos->getIndex(), 13);
        $this->assertEquals($pos->getErrorIndex(), -1);
        $this->assertEquals($result->isSupported(CF::YEAR()), true);
        $this->assertEquals($result->isSupported(CF::MONTH_OF_YEAR()), true);
        $this->assertEquals($result->isSupported(CF::DAY_OF_MONTH()), true);
        $this->assertEquals($result->isSupported(CF::HOUR_OF_DAY()), false);
        $this->assertEquals($result->getLong(CF::YEAR()), 2012);
        $this->assertEquals($result->getLong(CF::MONTH_OF_YEAR()), 6);
        $this->assertEquals($result->getLong(CF::DAY_OF_MONTH()), 30);
        $this->assertEquals($result->query(TemporalQueries::fromCallable([LocalDate::class, 'from'])), LocalDate::of(2012, 6, 30));
    }

    /**
     * @expectedException \Celest\DateTimeParseException
     */
    public function test_parse_CharSequence_ParsePosition_parseError()
    {
        $test = DateTimeFormatter::ISO_DATE();
        $pos = new ParsePosition(3);
        try {
            $test->parsePos("XXX2012XXX", $pos);
            $this->fail();
        } catch (DateTimeParseException $ex) {
            $this->assertEquals($ex->getErrorIndex(), 7);
            throw $ex;
        }
    }

    /**
     * @expectedException \Celest\DateTimeParseException
     */
    public function test_parse_CharSequence_ParsePosition_indexTooBig()
    {
        $test = DateTimeFormatter::ISO_DATE();
        $test->parsePos("Text", new ParsePosition(5));
    }

    public function test_parse_CharSequence_ParsePosition_nullText()
    {
        TestHelper::assertNullException($this, function () {
            $test = $this->fmt->withLocale(Locale::ENGLISH())->withDecimalStyle(DecimalStyle::STANDARD());
            $test->parsePos(null, new ParsePosition(0));
        });
    }

    public function test_parse_CharSequence_ParsePosition_nullParsePosition()
    {
        TestHelper::assertNullException($this, function () {
            $test = $this->fmt->withLocale(Locale::ENGLISH())->withDecimalStyle(DecimalStyle::STANDARD());
            $test->parsePos("Text", null);
        });
    }

    //-----------------------------------------------------------------------
    // parse(Query)
    //-----------------------------------------------------------------------

    public function test_parse_Query_String()
    {
        $result = self::DATE_FORMATTER()->parseQuery("ONE2012 07 27", TemporalQueries::fromCallable([LocalDate::class, 'from']));
        $this->assertEquals($result, LocalDate::of(2012, 7, 27));
    }


    public function test_parse_Query_CharSequence()
    {
        $result = self::DATE_FORMATTER()->parseQuery("ONE2012 07 27", TemporalQueries::fromCallable([LocalDate::class, 'from']));
        $this->assertEquals($result, LocalDate::of(2012, 7, 27));
    }

    /**
     * @expectedException \Celest\DateTimeParseException
     */
    public function test_parse_Query_String_parseError()
    {
        try {
            self::DATE_FORMATTER()->parseQuery("ONE2012 07 XX", TemporalQueries::fromCallable([LocalDate::class, 'from']));
        } catch (DateTimeParseException $ex) {
            $this->assertContains("could not be parsed", $ex->getMessage());
            $this->assertContains("ONE2012 07 XX", $ex->getMessage());
            $this->assertEquals($ex->getParsedString(), "ONE2012 07 XX");
            $this->assertEquals($ex->getErrorIndex(), 11);
            throw $ex;
        }
    }

    /**
     * @expectedException \Celest\DateTimeParseException
     */
    public function test_parse_Query_String_parseErrorLongText()
    {
        try {
            self::DATE_FORMATTER()->parseQuery("ONEXXX67890123456789012345678901234567890123456789012345678901234567890123456789", TemporalQueries::fromCallable([LocalDate::class, 'from']));
        } catch (DateTimeParseException $ex) {
            $this->assertContains("could not be parsed", $ex->getMessage());
            $this->assertContains("ONEXXX6789012345678901234567890123456789012345678901234567890123...", $ex->getMessage());
            $this->assertEquals($ex->getParsedString(), "ONEXXX67890123456789012345678901234567890123456789012345678901234567890123456789");
            $this->assertEquals($ex->getErrorIndex(), 3);
            throw $ex;
        }
    }

    /**
     * @expectedException \Celest\DateTimeParseException
     */
    public function test_parse_Query_String_parseIncomplete()
    {
        try {
            self::DATE_FORMATTER()->parseQuery("ONE2012 07 27SomethingElse", TemporalQueries::fromCallable([LocalDate::class, 'from']));
        } catch (DateTimeParseException $ex) {
            $this->assertContains("could not be parsed", $ex->getMessage());
            $this->assertContains("ONE2012 07 27SomethingElse", $ex->getMessage());
            $this->assertEquals($ex->getParsedString(), "ONE2012 07 27SomethingElse");
            $this->assertEquals($ex->getErrorIndex(), 13);
            throw $ex;
        }
    }

    public function test_parse_Query_String_nullText()
    {
        TestHelper::assertNullException($this, function () {
            self::DATE_FORMATTER()->parseQuery(null, TemporalQueries::fromCallable([LocalDate::class, 'from']));
        });
    }

    public function test_parse_Query_String_nullRule()
    {
        TestHelper::assertNullException($this, function () {
            $test = $this->fmt->withLocale(Locale::ENGLISH())->withDecimalStyle(DecimalStyle::STANDARD());
            $test->parseQuery("30", null);
        });

    }

    //-----------------------------------------------------------------------

    public function test_parseBest_firstOption()
    {
        $test = DateTimeFormatter::ofPattern("yyyy-MM-dd HH:mm[XXX]");
        $result = $test->parseBest("2011-06-30 12:30+03:00", TemporalQueries::fromCallable([ZonedDateTime::class, "from"]), TemporalQueries::fromCallable([LocalDateTime::class, "from"]));
        $ldt = LocalDateTime::of(2011, 6, 30, 12, 30);
        $this->assertEquals($result, ZonedDateTime::ofDateTime($ldt, ZoneOffset::ofHours(3)));
    }


    public function test_parseBest_secondOption()
    {
        $test = DateTimeFormatter::ofPattern("yyyy-MM-dd[ HH:mm[XXX]]");
        $result = $test->parseBest("2011-06-30", TemporalQueries::fromCallable([ZonedDateTime::class, "from"]), TemporalQueries::fromCallable([LocalDate::class, 'from']));
        $this->assertEquals($result, LocalDate::of(2011, 6, 30));
    }

    /**
     * @expectedException \Celest\DateTimeParseException
     */
    public function test_parseBest_String_parseError()
    {
        $test = DateTimeFormatter::ofPattern("yyyy-MM-dd HH:mm[XXX]");
        try {
            $test->parseBest("2011-06-XX", TemporalQueries::fromCallable([ZonedDateTime::class, "from"]), TemporalQueries::fromCallable([LocalDateTime::class, 'from']));
        } catch (DateTimeParseException $ex) {
            $this->assertContains("could not be parsed", $ex->getMessage());
            $this->assertContains("XX", $ex->getMessage());
            $this->assertEquals($ex->getParsedString(), "2011-06-XX");
            $this->assertEquals($ex->getErrorIndex(), 8);
            throw $ex;
        }
    }

    /**
     * @expectedException \Celest\DateTimeParseException
     */
    public function test_parseBest_String_parseErrorLongText()
    {
        $test = $this->fmt->withLocale(Locale::ENGLISH())->withDecimalStyle(DecimalStyle::STANDARD());
        try {
            $test->parseBest("ONEXXX67890123456789012345678901234567890123456789012345678901234567890123456789", TemporalQueries::fromCallable([ZonedDateTime::class, "from"]), TemporalQueries::fromCallable([LocalDate::class, 'from']));
        } catch (DateTimeParseException $ex) {
            $this->assertContains("could not be parsed", $ex->getMessage());
            $this->assertContains("ONEXXX6789012345678901234567890123456789012345678901234567890123...", $ex->getMessage());
            $this->assertEquals($ex->getParsedString(), "ONEXXX67890123456789012345678901234567890123456789012345678901234567890123456789");
            $this->assertEquals($ex->getErrorIndex(), 3);
            throw $ex;
        }
    }

    /**
     * @expectedException \Celest\DateTimeParseException
     */
    public function test_parseBest_String_parseIncomplete()
    {
        $test = $this->fmt->withLocale(Locale::ENGLISH())->withDecimalStyle(DecimalStyle::STANDARD());
        try {
            $test->parseBest("ONE30SomethingElse", TemporalQueries::fromCallable([ZonedDateTime::class, "from"]), TemporalQueries::fromCallable([LocalDate::class, 'from']));
        } catch (DateTimeParseException $ex) {
            $this->assertContains("could not be parsed", $ex->getMessage());
            $this->assertContains("ONE30SomethingElse", $ex->getMessage());
            $this->assertEquals($ex->getParsedString(), "ONE30SomethingElse");
            $this->assertEquals($ex->getErrorIndex(), 5);
            throw $ex;
        }
    }

    public function test_parseBest_String_nullText()
    {
        TestHelper::assertNullException($this, function () {
            $test = $this->fmt->withLocale(Locale::ENGLISH())->withDecimalStyle(DecimalStyle::STANDARD());
            $test->parseBest(null, TemporalQueries::fromCallable([ZonedDateTime::class, "from"]), TemporalQueries::fromCallable([LocalDate::class, 'from']));
        });
    }

    public function test_parseBest_String_nullRules()
    {
        TestHelper::assertNullException($this, function () {
            $test = $this->fmt->withLocale(Locale::ENGLISH())->withDecimalStyle(DecimalStyle::STANDARD());
            $test->parseBest("30", null);
        });
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function test_parseBest_String_zeroRules()
    {
        $test = $this->fmt->withLocale(Locale::ENGLISH())->withDecimalStyle(DecimalStyle::STANDARD());
        $test->parseBest("30");
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function test_parseBest_String_oneRule()
    {
        $test = $this->fmt->withLocale(Locale::ENGLISH())->withDecimalStyle(DecimalStyle::STANDARD());
        $test->parseBest("30", TemporalQueries::fromCallable([LocalDate::class, 'from']));
    }

//-----------------------------------------------------------------------

    public function test_parseUnresolved_StringParsePosition()
    {
        $test = $this->fmt->withLocale(Locale::ENGLISH())->withDecimalStyle(DecimalStyle::STANDARD());
        $pos = new ParsePosition(0);
        $result = $test->parseUnresolved("ONE30XXX", $pos);
        $this->assertEquals($pos->getIndex(), 5);
        $this->assertEquals($pos->getErrorIndex(), -1);
        $this->assertEquals($result->getLong(CF::DAY_OF_MONTH()), 30);
    }


    public function test_parseUnresolved_StringParsePosition_parseError()
    {
        $test = $this->fmt->withLocale(Locale::ENGLISH())->withDecimalStyle(DecimalStyle::STANDARD());
        $pos = new ParsePosition(0);
        $result = $test->parseUnresolved("ONEXXX", $pos);
        $this->assertEquals($pos->getIndex(), 0);
        $this->assertEquals($pos->getErrorIndex(), 3);
        $this->assertEquals($result, null);
    }


    public function test_parseUnresolved_StringParsePosition_duplicateFieldSameValue()
    {
        $test = (new DateTimeFormatterBuilder())
            ->appendValue(CF::MONTH_OF_YEAR())->appendLiteral('-')->appendValue(CF::MONTH_OF_YEAR())->toFormatter();
        $pos = new ParsePosition(3);
        $result = $test->parseUnresolved("XXX6-6", $pos);
        $this->assertEquals($pos->getIndex(), 6);
        $this->assertEquals($pos->getErrorIndex(), -1);
        $this->assertEquals($result->getLong(CF::MONTH_OF_YEAR()), 6);
    }


    public function test_parseUnresolved_StringParsePosition_duplicateFieldDifferentValue()
    {
        $test = (new DateTimeFormatterBuilder())
            ->appendValue(CF::MONTH_OF_YEAR())->appendLiteral('-')->appendValue(CF::MONTH_OF_YEAR())->toFormatter();
        $pos = new ParsePosition(3);
        $result = $test->parseUnresolved("XXX6-7", $pos);
        $this->assertEquals($pos->getIndex(), 3);
        $this->assertEquals($pos->getErrorIndex(), 5);
        $this->assertEquals($result, null);
    }

    public function test_parseUnresolved_StringParsePosition_nullString()
    {
        TestHelper::assertNullException($this, function () {
            $test = $this->fmt->withLocale(Locale::ENGLISH())->withDecimalStyle(DecimalStyle::STANDARD());
            $pos = new ParsePosition(0);
            $test->parseUnresolved(null, $pos);
        });
    }

    public function test_parseUnresolved_StringParsePosition_nullParsePosition()
    {
        TestHelper::assertNullException($this, function () {
            $test = $this->fmt->withLocale(Locale::ENGLISH())->withDecimalStyle(DecimalStyle::STANDARD());
            $test->parseUnresolved("ONE30", null);
        });
    }

    /**
     * @expectedException \OutOfRangeException
     */
    public function test_parseUnresolved_StringParsePosition_invalidPosition()
    {
        $test = $this->fmt->withLocale(Locale::ENGLISH())->withDecimalStyle(DecimalStyle::STANDARD());
        $pos = new ParsePosition(6);
        $test->parseUnresolved("ONE30", $pos);
    }
}

