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

use Celest\LocalDate;
use Celest\Locale;
use Celest\LocalTime;

/**
 * Test localized behavior of formatter.
 */
class TCKLocalizedPrinterParserTest extends \PHPUnit_Framework_TestCase
{
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
    /**
     * @expectedException \Celest\IllegalArgumentException
     */
    public function test_parse_negativePosition()
    {
        $this->builder->appendLocalized(null, null);
    }

//-----------------------------------------------------------------------
    function data_date()
    {
        return
            [
                [
                    LocalDate::of(2012, 6, 30), FormatStyle::SHORT(), \IntlDateFormatter::SHORT, Locale::UK()],
                [
                    LocalDate::of(2012, 6, 30), FormatStyle::SHORT(), \IntlDateFormatter::SHORT, Locale::US()],
                [
                    LocalDate::of(2012, 6, 30), FormatStyle::SHORT(), \IntlDateFormatter::SHORT, Locale::FRANCE()],
                [
                    LocalDate::of(2012, 6, 30), FormatStyle::SHORT(), \IntlDateFormatter::SHORT, Locale::JAPAN()],

                [
                    LocalDate::of(2012, 6, 30), FormatStyle::MEDIUM(), \IntlDateFormatter::MEDIUM, Locale::UK()],
                [
                    LocalDate::of(2012, 6, 30), FormatStyle::MEDIUM(), \IntlDateFormatter::MEDIUM, Locale::US()],
                [
                    LocalDate::of(2012, 6, 30), FormatStyle::MEDIUM(), \IntlDateFormatter::MEDIUM, Locale::FRANCE()],
                [
                    LocalDate::of(2012, 6, 30), FormatStyle::MEDIUM(), \IntlDateFormatter::MEDIUM, Locale::JAPAN()],

                [
                    LocalDate::of(2012, 6, 30), FormatStyle::LONG(), \IntlDateFormatter::LONG, Locale::UK()],
                [
                    LocalDate::of(2012, 6, 30), FormatStyle::LONG(), \IntlDateFormatter::LONG, Locale::US()],
                [
                    LocalDate::of(2012, 6, 30), FormatStyle::LONG(), \IntlDateFormatter::LONG, Locale::FRANCE()],
                [
                    LocalDate::of(2012, 6, 30), FormatStyle::LONG(), \IntlDateFormatter::LONG, Locale::JAPAN()],

                [
                    LocalDate::of(2012, 6, 30), FormatStyle::FULL(), \IntlDateFormatter::FULL, Locale::UK()],
                [
                    LocalDate::of(2012, 6, 30), FormatStyle::FULL(), \IntlDateFormatter::FULL, Locale::US()],
                [
                    LocalDate::of(2012, 6, 30), FormatStyle::FULL(), \IntlDateFormatter::FULL, Locale::FRANCE()],
                [
                    LocalDate::of(2012, 6, 30), FormatStyle::FULL(), \IntlDateFormatter::FULL, Locale::JAPAN()],
            ];
    }

    /**
     * @dataProvider data_date
     */
    public function test_date_print(LocalDate $date, FormatStyle $dateStyle, $dateStyleOld, Locale $locale)
    {
        $old = \IntlDateFormatter::create($locale->getLocale(), $dateStyleOld, \IntlDateFormatter::NONE, new \DateTimeZone('UTC'));
        $oldDate = new \DateTime($date->getYear() . '-' . $date->getMonthValue() . '-' . $date->getDayOfMonth(), new \DateTimeZone('UTC'));
        $text = $old->format($oldDate);

        $f = $this->builder->appendLocalized($dateStyle, null)->toFormatter2($locale);
        $formatted = $f->format($date);
        $this->assertEquals($text, $formatted);
    }

    /**
     * @dataProvider data_date
     */
    public function test_date_parse(LocalDate $date, FormatStyle $dateStyle, $dateStyleOld, Locale $locale)
    {
        $old = \IntlDateFormatter::create($locale->getLocale(), $dateStyleOld, \IntlDateFormatter::NONE, new \DateTimeZone('UTC'));
        $oldDate = new \DateTime($date->getYear() . '-' . $date->getMonthValue() . '-' . $date->getDayOfMonth(), new \DateTimeZone('UTC'));
        $text = $old->format($oldDate);

        $f = $this->builder->appendLocalized($dateStyle, null)->toFormatter2($locale);
        $parsed = $f->parsePos($text, $this->pos);
        $this->assertEquals($this->pos->getIndex(), strlen($text));
        $this->assertEquals($this->pos->getErrorIndex(), -1);
        $this->assertEquals(LocalDate::from($parsed), $date);
    }

//-----------------------------------------------------------------------
    function data_time()
    {
        return [
            [
                LocalTime::of(11, 30), FormatStyle::SHORT(), \IntlDateFormatter::SHORT, Locale::UK()],
            [
                LocalTime::of(11, 30), FormatStyle::SHORT(), \IntlDateFormatter::SHORT, Locale::US()],
            [
                LocalTime::of(11, 30), FormatStyle::SHORT(), \IntlDateFormatter::SHORT, Locale::FRANCE()],
            [
                LocalTime::of(11, 30), FormatStyle::SHORT(), \IntlDateFormatter::SHORT, Locale::JAPAN()],

            [
                LocalTime::of(11, 30), FormatStyle::MEDIUM(), \IntlDateFormatter::MEDIUM, Locale::UK()],
            [
                LocalTime::of(11, 30), FormatStyle::MEDIUM(), \IntlDateFormatter::MEDIUM, Locale::US()],
            [
                LocalTime::of(11, 30), FormatStyle::MEDIUM(), \IntlDateFormatter::MEDIUM, Locale::FRANCE()],
            [
                LocalTime::of(11, 30), FormatStyle::MEDIUM(), \IntlDateFormatter::MEDIUM, Locale::JAPAN()],

            // these localized patterns include "z" which isn't available from LocalTime
//                [LocalTime.of(11, 30), FormatStyle.LONG, \IntlDateFormatter.LONG, Locale.UK],
//                [LocalTime.of(11, 30), FormatStyle.LONG, \IntlDateFormatter.LONG, Locale.US],
//                [LocalTime.of(11, 30), FormatStyle.LONG, \IntlDateFormatter.LONG, Locale.FRANCE],
//                [LocalTime.of(11, 30), FormatStyle.LONG, \IntlDateFormatter.LONG, Locale.JAPAN],
//
//                [LocalTime.of(11, 30), FormatStyle.FULL, \IntlDateFormatter.FULL, Locale.UK],
//                [LocalTime.of(11, 30), FormatStyle.FULL, \IntlDateFormatter.FULL, Locale.US],
//                [LocalTime.of(11, 30), FormatStyle.FULL, \IntlDateFormatter.FULL, Locale.FRANCE],
//                [LocalTime.of(11, 30), FormatStyle.FULL, \IntlDateFormatter.FULL, Locale.JAPAN],
        ];
    }

    /**
     * @dataProvider data_time
     */
    public function test_time_print(LocalTime $time, FormatStyle $timeStyle, $timeStyleOld, Locale $locale)
    {
        $old = \IntlDateFormatter::create($locale->getLocale(), \IntlDateFormatter::NONE, $timeStyleOld, new \DateTimeZone('UTC'));
        $oldDate = new \DateTime('1970-0-0T' . $time->getHour() . ':' . $time->getMinute() . ':' . $time->getSecond(), new \DateTimeZone('UTC'));
        $text = $old->format($oldDate);

        $f = $this->builder->appendLocalized(null, $timeStyle)->toFormatter2($locale);
        $formatted = $f->format($time);
        $this->assertEquals($formatted, $text);
    }

    /**
     * @dataProvider data_time
     */
    public function test_time_parse(LocalTime $time, FormatStyle $timeStyle, $timeStyleOld, Locale $locale)
    {
        $old = \IntlDateFormatter::create($locale->getLocale(), \IntlDateFormatter::NONE, $timeStyleOld, new \DateTimeZone('UTC'));
        $oldDate = new \DateTime('1970-0-0T' . $time->getHour() . ':' . $time->getMinute() . ':' . $time->getSecond(), new \DateTimeZone('UTC'));
        $text = $old->format($oldDate);

        $f = $this->builder->appendLocalized(null, $timeStyle)->toFormatter2($locale);
        $parsed = $f->parsePos($text, $this->pos);
        $this->assertEquals($this->pos->getIndex(), strlen($text));
        $this->assertEquals($this->pos->getErrorIndex(), -1);
        $this->assertEquals(LocalTime::from($parsed), $time);
    }

}
