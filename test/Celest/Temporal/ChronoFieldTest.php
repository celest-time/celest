<?php
/*
 * Copyright (c) 2013, Oracle and/or its affiliates. All rights reserved.
 * DO NOT ALTER OR REMOVE COPYRIGHT NOTICES OR THIS FILE HEADER.
 *
 * This code is free software; you can redistribute it and/or modify it
 * under the terms of the GNU General Public License version 2 only, as
 * published by the Free Software Foundation.  Oracle designates this
 * particular file as subject to the "Classpath" exception as provided
 * by Oracle in the LICENSE file that accompanied this code.
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
 * Copyright (c) 2013, Stephen Colebourne & Michael Nascimento Santos
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
 *  * Neither the $name of JSR-310 nor the names of its contributors
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
namespace Celest\Temporal;

use Celest\Locale;
use Celest\TestHelper;
use PHPUnit\Framework\TestCase;

class TestChronoField extends TestCase
{
    /** @var FieldValues */
    private $fieldMap;


    public function setUp()
    {
        // slight abuse of FieldValues
        $this->fieldMap = new FieldValues();
        $this->fieldMap->put(ChronoField::ERA(), "era");
        $this->fieldMap->put(ChronoField::YEAR(), "year");
        $this->fieldMap->put(ChronoField::MONTH_OF_YEAR(), "month");
        $this->fieldMap->put(ChronoField::DAY_OF_MONTH(), "day");
        $this->fieldMap->put(ChronoField::AMPM_OF_DAY(), "dayperiod");
        $this->fieldMap->put(ChronoField::ALIGNED_WEEK_OF_YEAR(), "week");
        $this->fieldMap->put(ChronoField::DAY_OF_WEEK(), "weekday");
        $this->fieldMap->put(ChronoField::HOUR_OF_DAY(), "hour");
        $this->fieldMap->put(ChronoField::MINUTE_OF_HOUR(), "minute");
        $this->fieldMap->put(ChronoField::SECOND_OF_MINUTE(), "second");
        $this->fieldMap->put(ChronoField::OFFSET_SECONDS(), "zone");
    }

    function data_localeList()
    {
        return
            [
                Locale::US(),
                Locale::GERMAN(),
                Locale::JAPAN(),
                Locale::ROOT(),
            ];
    }

//-----------------------------------------------------------------------
    function data_localeDisplayNames()
    {
        return
            [
                [
                    ChronoField::ERA()],
                [
                    ChronoField::YEAR()],
                [
                    ChronoField::MONTH_OF_YEAR()],
                [
                    ChronoField::DAY_OF_WEEK()],
// [ChronoField.ALIGNED_WEEK_OF_YEAR],
                [
                    ChronoField::DAY_OF_MONTH()],
                [
                    ChronoField::AMPM_OF_DAY()],
                [
                    ChronoField::HOUR_OF_DAY()],
                [
                    ChronoField::MINUTE_OF_HOUR()],
                [
                    ChronoField::SECOND_OF_MINUTE()],
            ];
    }


    public function test_IsoFields_week_based_year()
    {
        $locale = Locale::US();
        $name = IsoFields::WEEK_OF_WEEK_BASED_YEAR()->getDisplayName($locale);
        $this->assertEquals(TestHelper::getEnglishWeek(), $name, TestHelper::INTLinfo($locale->getLocale()));
    }

    public function test_nullIsoFields_week_based_year()
    {
        TestHelper::assertNullException($this, function () {
            IsoFields::WEEK_OF_WEEK_BASED_YEAR()->getDisplayName(null);
        });
    }


    public function test_WeekFields_week_based_year()
    {
        $locale = Locale::US();
        $weekOfYearField = WeekFields::SUNDAY_START()->weekOfYear();
        $name = $weekOfYearField->getDisplayName($locale);
        $this->assertEquals(TestHelper::getEnglishWeek(), $name, TestHelper::INTLinfo($locale->getLocale()));
    }

    public function test_nullWeekFields_week_based_year()
    {
        TestHelper::assertNullException($this, function () {
            $weekOfYearField = WeekFields::SUNDAY_START()->weekOfYear();
            $weekOfYearField->getDisplayName(null);
        });
    }

    public function test_nullLocaleChronoFieldDisplayName()
    {
        TestHelper::assertNullException($this, function () {
            ChronoField::YEAR()->getDisplayName(null);

        });
    }

    public function test_nullLocaleTemporalFieldDisplayName()
    {
        TestHelper::assertNullException($this, function () {
            // Test the default method in TemporalField using the
            // IsoFields.DAY_OF_QUARTER which does not override getDisplayName
            IsoFields::DAY_OF_QUARTER()->getDisplayName(null);
        });

    }
}
