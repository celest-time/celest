<?php

namespace Celest\Format\Builder;


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
 * Copyright (c) 2011-2012, Stephen Colebourne & Michael Nascimento Santos
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
use Celest\Format\TextStyle;
use Celest\Locale;
use Celest\Temporal\ChronoField;
use Celest\Temporal\TemporalField;
use Celest\ZonedDateTime;

/**
 * Test SimpleDateTimeTextProvider.
 */
class DateTimeTextProviderTest extends AbstractTestPrinterParser
{

    private static function enUS()
    {
        return Locale::of("en", "US");
    }

    private static function ptBR()
    {
        return Locale::of("pt", "BR");
    }

    //-----------------------------------------------------------------------
    public function data_text()
    {
        return
            [
                [
                    ChronoField::DAY_OF_WEEK(), 1, TextStyle::SHORT(), self::enUS(), "Mon"],
                [
                    ChronoField::DAY_OF_WEEK(), 2, TextStyle::SHORT(), self::enUS(), "Tue"],
                [
                    ChronoField::DAY_OF_WEEK(), 3, TextStyle::SHORT(), self::enUS(), "Wed"],
                [
                    ChronoField::DAY_OF_WEEK(), 4, TextStyle::SHORT(), self::enUS(), "Thu"],
                [
                    ChronoField::DAY_OF_WEEK(), 5, TextStyle::SHORT(), self::enUS(), "Fri"],
                [
                    ChronoField::DAY_OF_WEEK(), 6, TextStyle::SHORT(), self::enUS(), "Sat"],
                [
                    ChronoField::DAY_OF_WEEK(), 7, TextStyle::SHORT(), self::enUS(), "Sun"],

                [
                    ChronoField::DAY_OF_WEEK(), 1, TextStyle::SHORT(), self::ptBR(), "seg"],
                [
                    ChronoField::DAY_OF_WEEK(), 2, TextStyle::SHORT(), self::ptBR(), "ter"],
                [
                    ChronoField::DAY_OF_WEEK(), 3, TextStyle::SHORT(), self::ptBR(), "qua"],
                [
                    ChronoField::DAY_OF_WEEK(), 4, TextStyle::SHORT(), self::ptBR(), "qui"],
                [
                    ChronoField::DAY_OF_WEEK(), 5, TextStyle::SHORT(), self::ptBR(), "sex"],
                [
                    ChronoField::DAY_OF_WEEK(), 6, TextStyle::SHORT(), self::ptBR(), "sáb"],
                [
                    ChronoField::DAY_OF_WEEK(), 7, TextStyle::SHORT(), self::ptBR(), "dom"],

                [
                    ChronoField::DAY_OF_WEEK(), 1, TextStyle::FULL(), self::enUS(), "Monday"],
                [
                    ChronoField::DAY_OF_WEEK(), 2, TextStyle::FULL(), self::enUS(), "Tuesday"],
                [
                    ChronoField::DAY_OF_WEEK(), 3, TextStyle::FULL(), self::enUS(), "Wednesday"],
                [
                    ChronoField::DAY_OF_WEEK(), 4, TextStyle::FULL(), self::enUS(), "Thursday"],
                [
                    ChronoField::DAY_OF_WEEK(), 5, TextStyle::FULL(), self::enUS(), "Friday"],
                [
                    ChronoField::DAY_OF_WEEK(), 6, TextStyle::FULL(), self::enUS(), "Saturday"],
                [
                    ChronoField::DAY_OF_WEEK(), 7, TextStyle::FULL(), self::enUS(), "Sunday"],

                [
                    ChronoField::DAY_OF_WEEK(), 1, TextStyle::FULL(), self::ptBR(), "segunda-feira"],
                [
                    ChronoField::DAY_OF_WEEK(), 2, TextStyle::FULL(), self::ptBR(), "terça-feira"],
                [
                    ChronoField::DAY_OF_WEEK(), 3, TextStyle::FULL(), self::ptBR(), "quarta-feira"],
                [
                    ChronoField::DAY_OF_WEEK(), 4, TextStyle::FULL(), self::ptBR(), "quinta-feira"],
                [
                    ChronoField::DAY_OF_WEEK(), 5, TextStyle::FULL(), self::ptBR(), "sexta-feira"],
                [
                    ChronoField::DAY_OF_WEEK(), 6, TextStyle::FULL(), self::ptBR(), "sábado"],
                [
                    ChronoField::DAY_OF_WEEK(), 7, TextStyle::FULL(), self::ptBR(), "domingo"],

                [
                    ChronoField::MONTH_OF_YEAR(), 1, TextStyle::SHORT(), self::enUS(), "Jan"],
                [
                    ChronoField::MONTH_OF_YEAR(), 2, TextStyle::SHORT(), self::enUS(), "Feb"],
                [
                    ChronoField::MONTH_OF_YEAR(), 3, TextStyle::SHORT(), self::enUS(), "Mar"],
                [
                    ChronoField::MONTH_OF_YEAR(), 4, TextStyle::SHORT(), self::enUS(), "Apr"],
                [
                    ChronoField::MONTH_OF_YEAR(), 5, TextStyle::SHORT(), self::enUS(), "May"],
                [
                    ChronoField::MONTH_OF_YEAR(), 6, TextStyle::SHORT(), self::enUS(), "Jun"],
                [
                    ChronoField::MONTH_OF_YEAR(), 7, TextStyle::SHORT(), self::enUS(), "Jul"],
                [
                    ChronoField::MONTH_OF_YEAR(), 8, TextStyle::SHORT(), self::enUS(), "Aug"],
                [
                    ChronoField::MONTH_OF_YEAR(), 9, TextStyle::SHORT(), self::enUS(), "Sep"],
                [
                    ChronoField::MONTH_OF_YEAR(), 10, TextStyle::SHORT(), self::enUS(), "Oct"],
                [
                    ChronoField::MONTH_OF_YEAR(), 11, TextStyle::SHORT(), self::enUS(), "Nov"],
                [
                    ChronoField::MONTH_OF_YEAR(), 12, TextStyle::SHORT(), self::enUS(), "Dec"],

                [
                    ChronoField::MONTH_OF_YEAR(), 1, TextStyle::SHORT(), self::ptBR(), "jan"],
                [
                    ChronoField::MONTH_OF_YEAR(), 2, TextStyle::SHORT(), self::ptBR(), "fev"],
                [
                    ChronoField::MONTH_OF_YEAR(), 3, TextStyle::SHORT(), self::ptBR(), "mar"],
                [
                    ChronoField::MONTH_OF_YEAR(), 4, TextStyle::SHORT(), self::ptBR(), "abr"],
                [
                    ChronoField::MONTH_OF_YEAR(), 5, TextStyle::SHORT(), self::ptBR(), "mai"],
                [
                    ChronoField::MONTH_OF_YEAR(), 6, TextStyle::SHORT(), self::ptBR(), "jun"],
                [
                    ChronoField::MONTH_OF_YEAR(), 7, TextStyle::SHORT(), self::ptBR(), "jul"],
                [
                    ChronoField::MONTH_OF_YEAR(), 8, TextStyle::SHORT(), self::ptBR(), "ago"],
                [
                    ChronoField::MONTH_OF_YEAR(), 9, TextStyle::SHORT(), self::ptBR(), "set"],
                [
                    ChronoField::MONTH_OF_YEAR(), 10, TextStyle::SHORT(), self::ptBR(), "out"],
                [
                    ChronoField::MONTH_OF_YEAR(), 11, TextStyle::SHORT(), self::ptBR(), "nov"],
                [
                    ChronoField::MONTH_OF_YEAR(), 12, TextStyle::SHORT(), self::ptBR(), "dez"],

                [
                    ChronoField::MONTH_OF_YEAR(), 1, TextStyle::FULL(), self::enUS(), "January"],
                [
                    ChronoField::MONTH_OF_YEAR(), 2, TextStyle::FULL(), self::enUS(), "February"],
                [
                    ChronoField::MONTH_OF_YEAR(), 3, TextStyle::FULL(), self::enUS(), "March"],
                [
                    ChronoField::MONTH_OF_YEAR(), 4, TextStyle::FULL(), self::enUS(), "April"],
                [
                    ChronoField::MONTH_OF_YEAR(), 5, TextStyle::FULL(), self::enUS(), "May"],
                [
                    ChronoField::MONTH_OF_YEAR(), 6, TextStyle::FULL(), self::enUS(), "June"],
                [
                    ChronoField::MONTH_OF_YEAR(), 7, TextStyle::FULL(), self::enUS(), "July"],
                [
                    ChronoField::MONTH_OF_YEAR(), 8, TextStyle::FULL(), self::enUS(), "August"],
                [
                    ChronoField::MONTH_OF_YEAR(), 9, TextStyle::FULL(), self::enUS(), "September"],
                [
                    ChronoField::MONTH_OF_YEAR(), 10, TextStyle::FULL(), self::enUS(), "October"],
                [
                    ChronoField::MONTH_OF_YEAR(), 11, TextStyle::FULL(), self::enUS(), "November"],
                [
                    ChronoField::MONTH_OF_YEAR(), 12, TextStyle::FULL(), self::enUS(), "December"],

                [
                    ChronoField::MONTH_OF_YEAR(), 1, TextStyle::FULL(), self::ptBR(), "janeiro"],
                [
                    ChronoField::MONTH_OF_YEAR(), 2, TextStyle::FULL(), self::ptBR(), "fevereiro"],
                [
                    ChronoField::MONTH_OF_YEAR(), 3, TextStyle::FULL(), self::ptBR(), "março"],
                [
                    ChronoField::MONTH_OF_YEAR(), 4, TextStyle::FULL(), self::ptBR(), "abril"],
                [
                    ChronoField::MONTH_OF_YEAR(), 5, TextStyle::FULL(), self::ptBR(), "maio"],
                [
                    ChronoField::MONTH_OF_YEAR(), 6, TextStyle::FULL(), self::ptBR(), "junho"],
                [
                    ChronoField::MONTH_OF_YEAR(), 7, TextStyle::FULL(), self::ptBR(), "julho"],
                [
                    ChronoField::MONTH_OF_YEAR(), 8, TextStyle::FULL(), self::ptBR(), "agosto"],
                [
                    ChronoField::MONTH_OF_YEAR(), 9, TextStyle::FULL(), self::ptBR(), "setembro"],
                [
                    ChronoField::MONTH_OF_YEAR(), 10, TextStyle::FULL(), self::ptBR(), "outubro"],
                [
                    ChronoField::MONTH_OF_YEAR(), 11, TextStyle::FULL(), self::ptBR(), "novembro"],
                [
                    ChronoField::MONTH_OF_YEAR(), 12, TextStyle::FULL(), self::ptBR(), "dezembro"],

                [
                    ChronoField::AMPM_OF_DAY(), 0, TextStyle::SHORT(), self::enUS(), "AM"],
                [
                    ChronoField::AMPM_OF_DAY(), 1, TextStyle::SHORT(), self::enUS(), "PM"],

            ];
    }

    /**
     * @dataProvider data_text
     */
    public function test_getText(TemporalField $field, $value, TextStyle $style, Locale $locale, $expected)
    {
        $fmt = $this->getFormatterFieldStyle($field, $style)->withLocale($locale);
        $this->assertEquals($expected, $fmt->format(ZonedDateTime::now()->with($field, $value)));
    }

}
