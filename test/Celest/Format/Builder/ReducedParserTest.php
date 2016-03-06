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
namespace Celest\Format\Builder;

use Celest\Chrono\ChronoLocalDate;
use Celest\Chrono\IsoChronology;
use Celest\Chrono\MinguoChronology;
use Celest\Chrono\ThaiBuddhistChronology;
use Celest\Chrono\ThaiBuddhistDate;
use Celest\Format\DateTimeFormatterBuilder;
use Celest\Format\ParsePosition;
use Celest\LocalDate;
use Celest\Temporal\ChronoField;
use Celest\Temporal\ChronoUnit;
use Celest\Temporal\TemporalAccessor;
use Celest\Temporal\TemporalField;
use Celest\Temporal\TemporalQueries;

//-----------------------------------------------------------------------
// Class to structure the test data
//-----------------------------------------------------------------------

class Pair
{
    public $parseLen;
    public $parseVal;
    private $strict;

    public function __construct($parseLen, $parseVal, $strict)
    {
        $this->parseLen = $parseLen;
        $this->parseVal = $parseVal;
        $this->strict = $strict;
    }

    public function toString()
    {
        return ($this->strict ? "strict" : "lenient") . "(" . $this->parseLen . "," . $this->parseVal . ")";
    }
}

/**
 * Test ReducedPrinterParser.
 */
class ReducedParserTest extends AbstractTestPrinterParser
{
    private static $STRICT = true;
    private static $LENIENT = false;

    private function getFormatter0(TemporalField $field, $width, $baseValue)
    {
        return $this->builder->appendValueReduced($field, $width, $width, $baseValue)->toFormatter2($this->locale)->withDecimalStyle($this->decimalStyle);
    }

    private
    function getFormatter1(TemporalField $field, $minWidth, $maxWidth, $baseValue)
    {
        return $this->builder->appendValueReduced($field, $minWidth, $maxWidth, $baseValue)->toFormatter2($this->locale)->withDecimalStyle($this->decimalStyle);
    }

    private
    function getFormatterBaseDate(TemporalField $field, $minWidth, $maxWidth, $baseValue)
    {
        return $this->builder->appendValueReduced2($field, $minWidth, $maxWidth, LocalDate::of($baseValue, 1, 1))->toFormatter2($this->locale)->withDecimalStyle($this->decimalStyle);
    }

//-----------------------------------------------------------------------
    public function data_error()
    {
        return
            [
                [
                    ChronoField::YEAR(), 2, 2010, "12", -1, \OutOfRangeException::class
                ],
                [
                    ChronoField::YEAR(), 2, 2010, "12", 3, \OutOfRangeException::class],
            ];
    }

    /**
     * @dataProvider data_error
     */
    public function test_parse_error(TemporalField $field, $width, $baseValue, $text, $pos, $expected)
    {
        try {
            $this->getFormatter0($field, $width, $baseValue)->parseUnresolved($text, new ParsePosition($pos));
        } catch (\Exception $ex) {
            $this->assertInstanceOf($expected, $ex);
        }
    }

//-----------------------------------------------------------------------
    public function test_parse_fieldRangeIgnored()
    {
        $pos = new ParsePosition(0);
        $parsed = $this->getFormatter0(ChronoField::DAY_OF_YEAR(), 3, 10)->parseUnresolved("456", $pos);
        $this->assertEquals($pos->getIndex(), 3);
        $this->assertParsed($parsed, ChronoField::DAY_OF_YEAR(), 456);  // parsed dayOfYear=456
    }

//-----------------------------------------------------------------------
// Parse data and values that are consistent whether strict or lenient
// The data is the ChronoField, width, baseValue, text, startPos, endPos, value
//-----------------------------------------------------------------------
    public function provider_parseAll()
    {
        return
            [
// negative zero
                [
                    ChronoField::YEAR(), 1, 2010, "-0", 0, 0, null
                ],

// general
                [
                    ChronoField::YEAR(), 2, 2010, "Xxx12Xxx", 3, 5, 2012],
                [
                    ChronoField::YEAR(), 2, 2010, "12-45", 0, 2, 2012],

// other junk
                [
                    ChronoField::YEAR(), 2, 2010, "A0", 0, 0, null],
                [
                    ChronoField::YEAR(), 2, 2010, "  1", 0, 0, null],
                [
                    ChronoField::YEAR(), 2, 2010, "-1", 0, 0, null],
                [
                    ChronoField::YEAR(), 2, 2010, "-10", 0, 0, null],
                [
                    ChronoField::YEAR(), 2, 2000, " 1", 0, 0, null],

// parse OK 1
                [
                    ChronoField::YEAR(), 1, 2010, "1", 0, 1, 2011],
                [
                    ChronoField::YEAR(), 1, 2010, "3", 1, 1, null],
                [
                    ChronoField::YEAR(), 1, 2010, "9", 0, 1, 2019],

                [
                    ChronoField::YEAR(), 1, 2005, "0", 0, 1, 2010],
                [
                    ChronoField::YEAR(), 1, 2005, "4", 0, 1, 2014],
                [
                    ChronoField::YEAR(), 1, 2005, "5", 0, 1, 2005],
                [
                    ChronoField::YEAR(), 1, 2005, "9", 0, 1, 2009],
                [
                    ChronoField::YEAR(), 1, 2010, "1-2", 0, 1, 2011],

// parse OK 2
                [
                    ChronoField::YEAR(), 2, 2010, "00", 0, 2, 2100],
                [
                    ChronoField::YEAR(), 2, 2010, "09", 0, 2, 2109],
                [
                    ChronoField::YEAR(), 2, 2010, "10", 0, 2, 2010],
                [
                    ChronoField::YEAR(), 2, 2010, "99", 0, 2, 2099],

// parse OK 2
                [
                    ChronoField::YEAR(), 2, -2005, "05", 0, 2, -2005],
                [
                    ChronoField::YEAR(), 2, -2005, "00", 0, 2, -2000],
                [
                    ChronoField::YEAR(), 2, -2005, "99", 0, 2, -1999],
                [
                    ChronoField::YEAR(), 2, -2005, "06", 0, 2, -1906],

                [
                    ChronoField::YEAR(), 2, -2005, "43", 0, 2, -1943],
            ];
    }

    /**
     * @dataProvider provider_parseAll
     */
    public function test_parseAllStrict(TemporalField $field, $width, $baseValue, $input, $pos, $parseLen, $parseVal)
    {
        $ppos = new ParsePosition($pos);
        $this->setStrict(true);
        $parsed = $this->getFormatter0($field, $width, $baseValue)->parseUnresolved($input, $ppos);
        if ($ppos->getErrorIndex() != -1) {
            $this->assertEquals($ppos->getErrorIndex(), $parseLen, "error case parse position");
            $this->assertEquals($parsed, $parseVal, "unexpected parse result");
        } else {
            $this->assertEquals($ppos->getIndex(), $parseLen, "parse position");
            $this->assertParsed($parsed, ChronoField::YEAR(), $parseVal != null ? $parseVal : null);
        }
    }

    /**
     * @dataProvider provider_parseAll
     */
    public function test_parseAllLenient(TemporalField $field, $width, $baseValue, $input, $pos, $parseLen, $parseVal)
    {
        $ppos = new ParsePosition($pos);
        $this->setStrict(false);
        $parsed = $this->getFormatter0($field, $width, $baseValue)->parseUnresolved($input, $ppos);
        if ($ppos->getErrorIndex() != -1) {
            $this->assertEquals($ppos->getErrorIndex(), $parseLen, "error case parse position");
            $this->assertEquals($parsed, $parseVal, "unexpected parse result");
        } else {
            $this->assertEquals($ppos->getIndex(), $parseLen, "parse position");
            $this->assertParsed($parsed, ChronoField::YEAR(), $parseVal != null ? $parseVal : null);
        }
    }

//-----------------------------------------------------------------------
// Parse data and values in strict and lenient modes.
// The data is the ChronoField, minWidth, maxWidth, baseValue, text, startPos,
// Strict Pair(endPos, value), Lenient Pair(endPos, value)
//-----------------------------------------------------------------------
    public function provider_parseLenientSensitive()
    {
        return
            [
// few digits supplied
                [
                    ChronoField::YEAR(), 2, 2, 2010, "3", 0, self::strict(0, null), self::lenient(1, 3)
                ],
                [
                    ChronoField::YEAR(), 2, 2, 2010, "4", 0, self::strict(0, null), self::lenient(1, 4)],
                [
                    ChronoField::YEAR(), 2, 2, 2010, "5", 1, self::strict(1, null), self::lenient(1, null)],
                [
                    ChronoField::YEAR(), 2, 2, 2010, "6-2", 0, self::strict(0, null), self::lenient(1, 6)],
                [
                    ChronoField::YEAR(), 2, 2, 2010, "9", 0, self::strict(0, null), self::lenient(1, 9)],

// other junk
                [
                    ChronoField::YEAR(), 1, 4, 2000, "7A", 0, self::strict(1, 2007), self::lenient(1, 2007)],
                [
                    ChronoField::YEAR(), 2, 2, 2010, "8A", 0, self::strict(0, null), self::lenient(1, 8)],

// Negative sign cases
                [
                    ChronoField::YEAR(), 2, 4, 2000, "-1", 0, self::strict(0, null), self::lenient(2, -1)],
                [
                    ChronoField::YEAR(), 2, 4, 2000, "-10", 0, self::strict(0, null), self::lenient(3, -10)],

// Positive sign cases
                [
                    ChronoField::YEAR(), 2, 4, 2000, "+1", 0, self::strict(0, null), self::lenient(2, 1)],
                [
                    ChronoField::YEAR(), 2, 4, 2000, "+10", 0, self::strict(0, null), self::lenient(3, 2010)],

// No sign cases
                [
                    ChronoField::YEAR(), 1, 1, 2005, "21", 0, self::strict(1, 2012), self::lenient(2, 21)],
                [
                    ChronoField::YEAR(), 1, 2, 2010, "12", 0, self::strict(2, 12), self::lenient(2, 12)],
                [
                    ChronoField::YEAR(), 1, 4, 2000, "87", 0, self::strict(2, 87), self::lenient(2, 87)],
                [
                    ChronoField::YEAR(), 1, 4, 2000, "9876", 0, self::strict(4, 9876), self::lenient(4, 9876)],
                [
                    ChronoField::YEAR(), 2, 2, 2010, "321", 0, self::strict(2, 2032), self::lenient(3, 321)],
                [
                    ChronoField::YEAR(), 2, 4, 2010, "2", 0, self::strict(0, null), self::lenient(1, 2)],
                [
                    ChronoField::YEAR(), 2, 4, 2010, "21", 0, self::strict(2, 2021), self::lenient(2, 2021)],
                [
                    ChronoField::YEAR(), 2, 4, 2010, "321", 0, self::strict(3, 321), self::lenient(3, 321)],
                [
                    ChronoField::YEAR(), 2, 4, 2010, "4321", 0, self::strict(4, 4321), self::lenient(4, 4321)],
                [
                    ChronoField::YEAR(), 2, 4, 2010, "54321", 0, self::strict(4, 5432), self::lenient(5, 54321)],
                [
                    ChronoField::YEAR(), 2, 8, 2010, "87654321", 3, self::strict(8, 54321), self::lenient(8, 54321)],
                [
                    ChronoField::YEAR(), 2, 9, 2010, "987654321", 0, self::strict(9, 987654321), self::lenient(9, 987654321)],
                [
                    ChronoField::YEAR(), 3, 3, 2010, "765", 0, self::strict(3, 2765), self::lenient(3, 2765)],
                [
                    ChronoField::YEAR(), 3, 4, 2010, "76", 0, self::strict(0, null), self::lenient(2, 76)],
                [
                    ChronoField::YEAR(), 3, 4, 2010, "765", 0, self::strict(3, 2765), self::lenient(3, 2765)],
                [
                    ChronoField::YEAR(), 3, 4, 2010, "7654", 0, self::strict(4, 7654), self::lenient(4, 7654)],
                [
                    ChronoField::YEAR(), 3, 4, 2010, "76543", 0, self::strict(4, 7654), self::lenient(5, 76543)],

// Negative baseValue
                [
                    ChronoField::YEAR(), 2, 4, -2005, "123", 0, self::strict(3, 123), self::lenient(3, 123)],

// Basics
                [
                    ChronoField::YEAR(), 2, 4, 2010, "10", 0, self::strict(2, 2010), self::lenient(2, 2010)],
                [
                    ChronoField::YEAR(), 2, 4, 2010, "09", 0, self::strict(2, 2109), self::lenient(2, 2109)],
            ];
    }

//-----------------------------------------------------------------------
// Parsing tests for strict mode
//-----------------------------------------------------------------------
    /**
     * @dataProvider provider_parseLenientSensitive
     */
    public function test_parseStrict(TemporalField $field, $minWidth, $maxWidth, $baseValue, $input, $pos,
                                     $strict, $lenient)
    {
        $ppos = new ParsePosition($pos);
        $this->setStrict(true);
        $parsed = $this->getFormatter1($field, $minWidth, $maxWidth, $baseValue)->parseUnresolved($input, $ppos);
        if ($ppos->getErrorIndex() != -1) {
            $this->assertEquals($ppos->getErrorIndex(), $strict->parseLen, "error case parse position");
            $this->assertEquals($parsed, $strict->parseVal, "unexpected parse result");
        } else {
            $this->assertEquals($ppos->getIndex(), $strict->parseLen, "parse position");
            $this->assertParsed($parsed, ChronoField::YEAR(), $strict->parseVal != null ? $strict->parseVal : null);
        }
    }

    /**
     * @dataProvider provider_parseLenientSensitive
     */
    public function test_parseStrict_baseDate(TemporalField $field, $minWidth, $maxWidth, $baseValue, $input, $pos,
                                              $strict, $lenient)
    {
        $ppos = new ParsePosition($pos);
        $this->setStrict(true);
        $parsed = $this->getFormatterBaseDate($field, $minWidth, $maxWidth, $baseValue)->parseUnresolved($input, $ppos);
        if ($ppos->getErrorIndex() != -1) {
            $this->assertEquals($ppos->getErrorIndex(), $strict->parseLen, "error case parse position");
            $this->assertEquals($parsed, $strict->parseVal, "unexpected parse result");
        } else {
            $this->assertEquals($ppos->getIndex(), $strict->parseLen, "parse position");
            $this->assertParsed($parsed, ChronoField::YEAR(), $strict->parseVal != null ? $strict->parseVal : null);
        }
    }

//-----------------------------------------------------------------------
// Parsing tests for lenient mode
//-----------------------------------------------------------------------
    /**
     * @dataProvider provider_parseLenientSensitive
     */
    public function test_parseLenient(TemporalField $field, $minWidth, $maxWidth, $baseValue, $input, $pos,
                                      $strict, $lenient)
    {
        $ppos = new ParsePosition($pos);
        $this->setStrict(false);
        $parsed = $this->getFormatter1($field, $minWidth, $maxWidth, $baseValue)->parseUnresolved($input, $ppos);
        if ($ppos->getErrorIndex() != -1) {
            $this->assertEquals($ppos->getErrorIndex(), $lenient->parseLen, "error case parse position");
            $this->assertEquals($parsed, $lenient->parseVal, "unexpected parse result");
        } else {
            $this->assertEquals($ppos->getIndex(), $lenient->parseLen, "parse position");
            $this->assertParsed($parsed, ChronoField::YEAR(), $lenient->parseVal != null ? $lenient->parseVal : null);
        }
    }

    /**
     * @dataProvider provider_parseLenientSensitive
     */
    public function test_parseLenient_baseDate(TemporalField $field, $minWidth, $maxWidth, $baseValue, $input, $pos,
                                               $strict, $lenient)
    {
        $ppos = new ParsePosition($pos);
        $this->setStrict(false);
        $parsed = $this->getFormatterBaseDate($field, $minWidth, $maxWidth, $baseValue)->parseUnresolved($input, $ppos);
        if ($ppos->getErrorIndex() != -1) {
            $this->assertEquals($ppos->getErrorIndex(), $lenient->parseLen, "error case parse position");
            $this->assertEquals($parsed, $lenient->parseVal, "unexpected parse result");
        } else {
            $this->assertEquals($ppos->getIndex(), $lenient->parseLen, "parse position");
            $this->assertParsed($parsed, ChronoField::YEAR(), $lenient->parseVal != null ? $lenient->parseVal : null);
        }
    }

    private
    function assertParsed(TemporalAccessor $parsed, TemporalField $field, $value)
    {
        if ($value === null) {
            $this->assertNull(null, $parsed, "Parsed Value");
        } else {
            $this->assertTrue($parsed->isSupported($field), "isSupported: " . $field);
            $this->assertEquals($value, $parsed->getLong($field), "Temporal.getLong: " . $field);
        }
    }


//-----------------------------------------------------------------------
// Cases and values in adjacent parsing mode
//-----------------------------------------------------------------------
    public function provider_parseAdjacent()
    {
        return
            [
// general
                [
                    "yyMMdd", "19990703", self::$LENIENT, 0, 8, 1999, 7, 3
                ],
                [
                    "yyMMdd", "19990703", self::$STRICT, 0, 6, 2019, 99, 7],
                [
                    "yyMMdd", "990703", self::$LENIENT, 0, 6, 2099, 7, 3],
                [
                    "yyMMdd", "990703", self::$STRICT, 0, 6, 2099, 7, 3],
                [
                    "yyMMdd", "200703", self::$LENIENT, 0, 6, 2020, 7, 3],
                [
                    "yyMMdd", "200703", self::$STRICT, 0, 6, 2020, 7, 3],
                [
                    "ddMMyy", "230714", self::$LENIENT, 0, 6, 2014, 7, 23],
                [
                    "ddMMyy", "230714", self::$STRICT, 0, 6, 2014, 7, 23],
                [
                    "ddMMyy", "25062001", self::$LENIENT, 0, 8, 2001, 6, 25],
                [
                    "ddMMyy", "25062001", self::$STRICT, 0, 6, 2020, 6, 25],
                [
                    "ddMMy", "27052002", self::$LENIENT, 0, 8, 2002, 5, 27],
                [
                    "ddMMy", "27052002", self::$STRICT, 0, 8, 2002, 5, 27],
            ];
    }

    /**
     * @dataProvider provider_parseAdjacent
     */
    public function test_parseAdjacent($pattern, $input, $strict, $pos, $parseLen, $year, $month, $day)
    {
        $ppos = new ParsePosition(0);
        $this->setStrict($strict);
        $this->builder->appendPattern($pattern);
        $dtf = $this->builder->toFormatter();

        $parsed = $dtf->parseUnresolved($input, $ppos);
        $this->assertNotNull($parsed, "parse failed: ppos: " . $ppos . ", formatter: " . $dtf);
        if ($ppos->getErrorIndex() != -1) {
            $this->assertEquals($ppos->getErrorIndex(), $parseLen, "error case parse position");
        } else {
            $this->assertEquals($parseLen, $ppos->getIndex(), "parse position");
            $this->assertParsed($parsed, ChronoField::YEAR_OF_ERA(), $year);
            $this->assertParsed($parsed, ChronoField::MONTH_OF_YEAR(), $month);
            $this->assertParsed($parsed, ChronoField::DAY_OF_MONTH(), $day);
        }
    }

//-----------------------------------------------------------------------
// Cases and values in reduced value parsing mode
//-----------------------------------------------------------------------
    public function provider_reducedWithChrono()
    {
        $baseYear = LocalDate::of(2000, 1, 1);
        return
            [
                [IsoChronology::INSTANCE()->dateFrom($baseYear)],
                [IsoChronology::INSTANCE()->dateFrom($baseYear)->plus(1, ChronoUnit::YEARS())],
                [IsoChronology::INSTANCE()->dateFrom($baseYear)->plus(99, ChronoUnit::YEARS())],
                /* TODO enable
                [
                     HijrahChronology . INSTANCE . date(baseYear)],
                 [
                     HijrahChronology . INSTANCE . date(baseYear) . plus(1, YEARS)],
                 [
                     HijrahChronology . INSTANCE . date(baseYear) . plus(99, YEARS)],
                 [
                     JapaneseChronology . INSTANCE . date(baseYear)],
                 [
                     JapaneseChronology . INSTANCE . date(baseYear) . plus(1, YEARS)],
                 [
                     JapaneseChronology . INSTANCE . date(baseYear) . plus(99, YEARS)],*/
                [MinguoChronology::INSTANCE()->dateFrom($baseYear)],
                [MinguoChronology::INSTANCE()->dateFrom($baseYear)->plus(1, ChronoUnit::YEARS())],
                [MinguoChronology::INSTANCE()->dateFrom($baseYear)->plus(99, ChronoUnit::YEARS())],
                [ThaiBuddhistChronology::INSTANCE()->dateFrom($baseYear)],
                [ThaiBuddhistChronology::INSTANCE()->dateFrom($baseYear)->plus(1, ChronoUnit::YEARS())],
                [ThaiBuddhistChronology::INSTANCE()->dateFrom($baseYear)->plus(99, ChronoUnit::YEARS())],
            ];
    }

    /**
     * @dataProvider provider_reducedWithChrono
     */
    public function test_reducedWithChronoYear(ChronoLocalDate $date)
    {
        $chrono = $date->getChronology();
        $df
            = (new DateTimeFormatterBuilder())->appendValueReduced2(ChronoField::YEAR(), 2, 2, LocalDate::of(2000, 1, 1))
            ->toFormatter()
            ->withChronology($chrono);
        $expected = $date->get(ChronoField::YEAR());
        $input = $df->format($date);

        $pos = new ParsePosition(0);
        $parsed = $df->parseUnresolved($input, $pos);
        $actual = $parsed->get(ChronoField::YEAR());
        $this->assertEquals($actual, $expected,
            "Wrong date parsed, chrono: " . $chrono . ", input: " . $input);

    }

    /**
     * @dataProvider provider_reducedWithChrono
     */

    public function test_reducedWithChronoYearOfEra(ChronoLocalDate $date)
    {
        $chrono = $date->getChronology();
        $df
            = (new DateTimeFormatterBuilder())->appendValueReduced2(ChronoField::YEAR_OF_ERA(), 2, 2, LocalDate::of(2000, 1, 1))
            ->toFormatter()
            ->withChronology($chrono);
        $expected = $date->get(ChronoField::YEAR_OF_ERA());
        $input = $df->format($date);

        $pos = new ParsePosition(0);
        $parsed = $df->parseUnresolved($input, $pos);
        $actual = $parsed->get(ChronoField::YEAR_OF_ERA());
        $this->assertEquals($actual, $expected,
            "Wrong date parsed, chrono: " . $chrono . ", input: " . $input);

    }

    public function test_reducedWithLateChronoChange()
    {
        $date = ThaiBuddhistDate::of(2543, 1, 1);
        $df
            = (new DateTimeFormatterBuilder())
            ->appendValueReduced2(ChronoField::YEAR(), 2, 2, LocalDate::of(2000, 1, 1))
            ->appendLiteral(" ")
            ->appendChronologyId()
            ->toFormatter();
        $expected = $date->get(ChronoField::YEAR());
        $input = $df->format($date);

        $pos = new ParsePosition(0);
        $parsed = $df->parseUnresolved($input, $pos);
        $this->assertEquals($pos->getIndex(), strlen($input), "Input not parsed completely");
        $this->assertEquals($pos->getErrorIndex(), -1, "Error index should be -1 (no-error)");
        $actual = $parsed->get(ChronoField::YEAR());
        $this->assertEquals($expected, $actual, sprintf("Wrong date parsed, chrono: %s, input: %s",
            $parsed->query(TemporalQueries::chronology()), $input));

    }

    public function test_reducedWithLateChronoChangeTwice()
    {
        $df
            = (new DateTimeFormatterBuilder())
            ->appendValueReduced2(ChronoField::YEAR(), 2, 2, LocalDate::of(2000, 1, 1))
            ->appendLiteral(" ")
            ->appendChronologyId()
            ->appendLiteral(" ")
            ->appendChronologyId()
            ->toFormatter();
        $expected = 2044;
        $input = "44 ThaiBuddhist ISO";
        $pos = new ParsePosition(0);
        $parsed = $df->parseUnresolved($input, $pos);
        $this->assertEquals($pos->getIndex(), strlen($input), "Input not parsed completely: " . $pos);
        $this->assertEquals($pos->getErrorIndex(), -1, "Error index should be -1 (no-error)");
        $actual = $parsed->get(ChronoField::YEAR());
        $this->assertEquals($expected, $actual, sprintf("Wrong date parsed, chrono: %s, input: %s",
            $parsed->query(TemporalQueries::chronology()), $input));
    }

    private static function strict($parseLen, $parseVal)
    {
        return new Pair($parseLen, $parseVal, self::$STRICT);
    }

    private static function lenient($parseLen, $parseVal)
    {
        return new Pair($parseLen, $parseVal, self::$LENIENT);
    }

}
