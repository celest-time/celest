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
 * Copyright (c) 2009-2012, Stephen Colebourne & Michael Nascimento Santos
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
use Celest\Locale;
use Celest\Temporal\ChronoField;
use Celest\Temporal\TemporalField;

/**
 * Test text printing.
 */
class TCKDateTimeTextPrintingTest extends \PHPUnit_Framework_TestCase
{

    /** @var DateTimeFormatterBuilder */
    private $builder;

    public function setUp()
    {
        $this->builder = new DateTimeFormatterBuilder();
    }

    //-----------------------------------------------------------------------
    function data_text()
    {
        return
            [
                [
                    ChronoField::DAY_OF_WEEK(), TextStyle::FULL(), 1, "Monday"
                ],
                [
                    ChronoField::DAY_OF_WEEK(), TextStyle::FULL(), 2, "Tuesday"],
                [
                    ChronoField::DAY_OF_WEEK(), TextStyle::FULL(), 3, "Wednesday"],
                [
                    ChronoField::DAY_OF_WEEK(), TextStyle::FULL(), 4, "Thursday"],
                [
                    ChronoField::DAY_OF_WEEK(), TextStyle::FULL(), 5, "Friday"],
                [
                    ChronoField::DAY_OF_WEEK(), TextStyle::FULL(), 6, "Saturday"],
                [
                    ChronoField::DAY_OF_WEEK(), TextStyle::FULL(), 7, "Sunday"],

                [
                    ChronoField::DAY_OF_WEEK(), TextStyle::SHORT(), 1, "Mon"],
                [
                    ChronoField::DAY_OF_WEEK(), TextStyle::SHORT(), 2, "Tue"],
                [
                    ChronoField::DAY_OF_WEEK(), TextStyle::SHORT(), 3, "Wed"],
                [
                    ChronoField::DAY_OF_WEEK(), TextStyle::SHORT(), 4, "Thu"],
                [
                    ChronoField::DAY_OF_WEEK(), TextStyle::SHORT(), 5, "Fri"],
                [
                    ChronoField::DAY_OF_WEEK(), TextStyle::SHORT(), 6, "Sat"],
                [
                    ChronoField::DAY_OF_WEEK(), TextStyle::SHORT(), 7, "Sun"],

                [
                    ChronoField::DAY_OF_MONTH(), TextStyle::FULL(), 1, "1"],
                [
                    ChronoField::DAY_OF_MONTH(), TextStyle::FULL(), 2, "2"],
                [
                    ChronoField::DAY_OF_MONTH(), TextStyle::FULL(), 3, "3"],
                [
                    ChronoField::DAY_OF_MONTH(), TextStyle::FULL(), 28, "28"],
                [
                    ChronoField::DAY_OF_MONTH(), TextStyle::FULL(), 29, "29"],
                [
                    ChronoField::DAY_OF_MONTH(), TextStyle::FULL(), 30, "30"],
                [
                    ChronoField::DAY_OF_MONTH(), TextStyle::FULL(), 31, "31"],

                [
                    ChronoField::DAY_OF_MONTH(), TextStyle::SHORT(), 1, "1"],
                [
                    ChronoField::DAY_OF_MONTH(), TextStyle::SHORT(), 2, "2"],
                [
                    ChronoField::DAY_OF_MONTH(), TextStyle::SHORT(), 3, "3"],
                [
                    ChronoField::DAY_OF_MONTH(), TextStyle::SHORT(), 28, "28"],
                [
                    ChronoField::DAY_OF_MONTH(), TextStyle::SHORT(), 29, "29"],
                [
                    ChronoField::DAY_OF_MONTH(), TextStyle::SHORT(), 30, "30"],
                [
                    ChronoField::DAY_OF_MONTH(), TextStyle::SHORT(), 31, "31"],

                [
                    ChronoField::MONTH_OF_YEAR(), TextStyle::FULL(), 1, "January"],
                [
                    ChronoField::MONTH_OF_YEAR(), TextStyle::FULL(), 12, "December"],

                [
                    ChronoField::MONTH_OF_YEAR(), TextStyle::SHORT(), 1, "Jan"],
                [
                    ChronoField::MONTH_OF_YEAR(), TextStyle::SHORT(), 12, "Dec"],
            ];
    }

    /**
     * @dataProvider data_text
     */
    public function test_appendText2arg_format(TemporalField $field, TextStyle $style, $value, $expected)
    {
        $f = $this->builder->appendText2($field, $style)->toFormatter2(Locale::ENGLISH());
        $dt = LocalDateTime::ofNumerical(2010, 1, 1, 0, 0);
        $dt = $dt->with($field, $value);
        $text = $f->format($dt);
        $this->assertEquals($text, $expected);
    }

    /**
     * @dataProvider data_text
     */
    public function test_appendText1arg_format(TemporalField $field, TextStyle $style, $value, $expected)
    {
        if ($style == TextStyle::FULL()) {
            $f = $this->builder->appendText($field)->toFormatter2(Locale::ENGLISH());
            $dt = LocalDateTime::ofNumerical(2010, 1, 1, 0, 0);
            $dt = $dt->with($field, $value);
            $text = $f->format($dt);
            $this->assertEquals($text, $expected);
        }
    }

    //-----------------------------------------------------------------------
    public function test_appendTextMap()
    {
        $map = [
            1 => "JNY",
            2 => "FBY",
            3 => "MCH",
            4 => "APL",
            5 => "MAY",
            6 => "JUN",
            7 => "JLY",
            8 => "AGT",
            9 => "SPT",
            10 => "OBR",
            11 => "NVR",
            12 => "DBR",
        ];
        $this->builder->appendText3(ChronoField::MONTH_OF_YEAR(), $map);
        $f = $this->builder->toFormatter();
        $dt = LocalDateTime::ofNumerical(2010, 1, 1, 0, 0);
        foreach ($map as $month => $val) {
            $this->assertEquals($f->format($dt->withMonth($month)), $val);
        }
    }

    public function test_appendTextMap_DOM()
    {
        $map = [
            1 => "1st",
            2 => "2nd",
            3 => "3rd"
        ];
        $this->builder->appendText3(ChronoField::DAY_OF_MONTH(), $map);
        $f = $this->builder->toFormatter();
        $dt = LocalDateTime::ofNumerical(2010, 1, 1, 0, 0);
        $this->assertEquals($f->format($dt->withDayOfMonth(1)), "1st");
        $this->assertEquals($f->format($dt->withDayOfMonth(2)), "2nd");
        $this->assertEquals($f->format($dt->withDayOfMonth(3)), "3rd");
    }

    public function test_appendTextMapIncomplete()
    {
        $map = [1, "JNY"];
        $this->builder->appendText3(ChronoField::MONTH_OF_YEAR(), $map);
        $f = $this->builder->toFormatter();
        $dt = LocalDateTime::ofNumerical(2010, 2, 1, 0, 0);
        $this->assertEquals($f->format($dt), "2");
    }

}
