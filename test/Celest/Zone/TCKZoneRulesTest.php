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
namespace Celest\Zone;

use Celest\DayOfWeek;
use Celest\Duration;
use Celest\LocalDate;
use Celest\LocalDateTime;
use Celest\LocalTime;
use Celest\Month;
use Celest\OffsetDateTime;
use Celest\Year;
use Celest\ZonedDateTime;
use Celest\ZoneId;
use Celest\ZoneOffset;
use PHPUnit\Framework\TestCase;

class TCKZoneRulesTest extends TestCase
{
    private static $OFFSET_ZERO;
    private static $OFFSET_PONE;
    private static $OFFSET_PTWO;
    public static $LATEST_TZDB = "2009b";
    private static $OVERLAP = 2;
    private static $GAP = 0;

    protected function setUp()
    {
        self::$OFFSET_ZERO = ZoneOffset::ofHours(0);
        self::$OFFSET_PONE = ZoneOffset::ofHours(1);
        self::$OFFSET_PTWO = ZoneOffset::ofHours(2);
    }

    //-----------------------------------------------------------------------
    // Europe/London
    //-----------------------------------------------------------------------
    private function europeLondon()
    {
        return ZoneId::of("Europe/London")->getRules();
    }

    public function test_London()
    {
        $test = $this->europeLondon();
        $this->assertEquals($test->isFixedOffset(), false);
    }

    public function test_London_preTimeZones()
    {
        $test = $this->europeLondon();
        $old = $this->createZDT(1800, 1, 1, ZoneOffset::UTC());
        $instant = $old->toInstant();
        $offset = ZoneOffset::ofHoursMinutesSeconds(0, -1, -15);
        $this->assertEquals($test->getOffset($instant), $offset);
        $this->checkOffset($test, $old->toLocalDateTime(), $offset, 1);
        $this->assertEquals($test->getStandardOffset($instant), $offset);
        $this->assertEquals($test->getDaylightSavings($instant), Duration::ZERO());
        $this->assertEquals($test->isDaylightSavings($instant), false);
    }

    public function test_London_getOffset()
    {
        $test = $this->europeLondon();
        $this->assertEquals($test->getOffset($this->createInstant(2008, 1, 1, ZoneOffset::UTC())), self::$OFFSET_ZERO);
        $this->assertEquals($test->getOffset($this->createInstant(2008, 2, 1, ZoneOffset::UTC())), self::$OFFSET_ZERO);
        $this->assertEquals($test->getOffset($this->createInstant(2008, 3, 1, ZoneOffset::UTC())), self::$OFFSET_ZERO);
        $this->assertEquals($test->getOffset($this->createInstant(2008, 4, 1, ZoneOffset::UTC())), self::$OFFSET_PONE);
        $this->assertEquals($test->getOffset($this->createInstant(2008, 5, 1, ZoneOffset::UTC())), self::$OFFSET_PONE);
        $this->assertEquals($test->getOffset($this->createInstant(2008, 6, 1, ZoneOffset::UTC())), self::$OFFSET_PONE);
        $this->assertEquals($test->getOffset($this->createInstant(2008, 7, 1, ZoneOffset::UTC())), self::$OFFSET_PONE);
        $this->assertEquals($test->getOffset($this->createInstant(2008, 8, 1, ZoneOffset::UTC())), self::$OFFSET_PONE);
        $this->assertEquals($test->getOffset($this->createInstant(2008, 9, 1, ZoneOffset::UTC())), self::$OFFSET_PONE);
        $this->assertEquals($test->getOffset($this->createInstant(2008, 10, 1, ZoneOffset::UTC())), self::$OFFSET_PONE);
        $this->assertEquals($test->getOffset($this->createInstant(2008, 11, 1, ZoneOffset::UTC())), self::$OFFSET_ZERO);
        $this->assertEquals($test->getOffset($this->createInstant(2008, 12, 1, ZoneOffset::UTC())), self::$OFFSET_ZERO);
    }

    public function test_London_getOffset_toDST()
    {
        $test = $this->europeLondon();
        $this->assertEquals($test->getOffset($this->createInstant(2008, 3, 24, ZoneOffset::UTC())), self::$OFFSET_ZERO);
        $this->assertEquals($test->getOffset($this->createInstant(2008, 3, 25, ZoneOffset::UTC())), self::$OFFSET_ZERO);
        $this->assertEquals($test->getOffset($this->createInstant(2008, 3, 26, ZoneOffset::UTC())), self::$OFFSET_ZERO);
        $this->assertEquals($test->getOffset($this->createInstant(2008, 3, 27, ZoneOffset::UTC())), self::$OFFSET_ZERO);
        $this->assertEquals($test->getOffset($this->createInstant(2008, 3, 28, ZoneOffset::UTC())), self::$OFFSET_ZERO);
        $this->assertEquals($test->getOffset($this->createInstant(2008, 3, 29, ZoneOffset::UTC())), self::$OFFSET_ZERO);
        $this->assertEquals($test->getOffset($this->createInstant(2008, 3, 30, ZoneOffset::UTC())), self::$OFFSET_ZERO);
        $this->assertEquals($test->getOffset($this->createInstant(2008, 3, 31, ZoneOffset::UTC())), self::$OFFSET_PONE);
        // cutover at 01:00Z
        $this->assertEquals($test->getOffset($this->createInstant8(2008, 3, 30, 0, 59, 59, 999999999, ZoneOffset::UTC())), self::$OFFSET_ZERO);
        $this->assertEquals($test->getOffset($this->createInstant8(2008, 3, 30, 1, 0, 0, 0, ZoneOffset::UTC())), self::$OFFSET_PONE);
    }

    public function test_London_getOffset_fromDST()
    {
        $test = $this->europeLondon();
        $this->assertEquals($test->getOffset($this->createInstant(2008, 10, 24, ZoneOffset::UTC())), self::$OFFSET_PONE);
        $this->assertEquals($test->getOffset($this->createInstant(2008, 10, 25, ZoneOffset::UTC())), self::$OFFSET_PONE);
        $this->assertEquals($test->getOffset($this->createInstant(2008, 10, 26, ZoneOffset::UTC())), self::$OFFSET_PONE);
        $this->assertEquals($test->getOffset($this->createInstant(2008, 10, 27, ZoneOffset::UTC())), self::$OFFSET_ZERO);
        $this->assertEquals($test->getOffset($this->createInstant(2008, 10, 28, ZoneOffset::UTC())), self::$OFFSET_ZERO);
        $this->assertEquals($test->getOffset($this->createInstant(2008, 10, 29, ZoneOffset::UTC())), self::$OFFSET_ZERO);
        $this->assertEquals($test->getOffset($this->createInstant(2008, 10, 30, ZoneOffset::UTC())), self::$OFFSET_ZERO);
        $this->assertEquals($test->getOffset($this->createInstant(2008, 10, 31, ZoneOffset::UTC())), self::$OFFSET_ZERO);
        // cutover at 01:00Z
        $this->assertEquals($test->getOffset($this->createInstant8(2008, 10, 26, 0, 59, 59, 999999999, ZoneOffset::UTC())), self::$OFFSET_PONE);
        $this->assertEquals($test->getOffset($this->createInstant8(2008, 10, 26, 1, 0, 0, 0, ZoneOffset::UTC())), self::$OFFSET_ZERO);
    }

    public function test_London_getOffsetInfo()
    {
        $test = $this->europeLondon();
        $this->checkOffset($test, $this->createLDT(2008, 1, 1), self::$OFFSET_ZERO, 1);
        $this->checkOffset($test, $this->createLDT(2008, 2, 1), self::$OFFSET_ZERO, 1);
        $this->checkOffset($test, $this->createLDT(2008, 3, 1), self::$OFFSET_ZERO, 1);
        $this->checkOffset($test, $this->createLDT(2008, 4, 1), self::$OFFSET_PONE, 1);
        $this->checkOffset($test, $this->createLDT(2008, 5, 1), self::$OFFSET_PONE, 1);
        $this->checkOffset($test, $this->createLDT(2008, 6, 1), self::$OFFSET_PONE, 1);
        $this->checkOffset($test, $this->createLDT(2008, 7, 1), self::$OFFSET_PONE, 1);
        $this->checkOffset($test, $this->createLDT(2008, 8, 1), self::$OFFSET_PONE, 1);
        $this->checkOffset($test, $this->createLDT(2008, 9, 1), self::$OFFSET_PONE, 1);
        $this->checkOffset($test, $this->createLDT(2008, 10, 1), self::$OFFSET_PONE, 1);
        $this->checkOffset($test, $this->createLDT(2008, 11, 1), self::$OFFSET_ZERO, 1);
        $this->checkOffset($test, $this->createLDT(2008, 12, 1), self::$OFFSET_ZERO, 1);
    }

    public function test_London_getOffsetInfo_toDST()
    {
        $test = $this->europeLondon();
        $this->checkOffset($test, $this->createLDT(2008, 3, 24), self::$OFFSET_ZERO, 1);
        $this->checkOffset($test, $this->createLDT(2008, 3, 25), self::$OFFSET_ZERO, 1);
        $this->checkOffset($test, $this->createLDT(2008, 3, 26), self::$OFFSET_ZERO, 1);
        $this->checkOffset($test, $this->createLDT(2008, 3, 27), self::$OFFSET_ZERO, 1);
        $this->checkOffset($test, $this->createLDT(2008, 3, 28), self::$OFFSET_ZERO, 1);
        $this->checkOffset($test, $this->createLDT(2008, 3, 29), self::$OFFSET_ZERO, 1);
        $this->checkOffset($test, $this->createLDT(2008, 3, 30), self::$OFFSET_ZERO, 1);
        $this->checkOffset($test, $this->createLDT(2008, 3, 31), self::$OFFSET_PONE, 1);
        // cutover at 01:00Z
        $this->checkOffset($test, LocalDateTime::of(2008, 3, 30, 0, 59, 59, 999999999), self::$OFFSET_ZERO, 1);
        $this->checkOffset($test, LocalDateTime::of(2008, 3, 30, 2, 0, 0, 0), self::$OFFSET_PONE, 1);
    }

    public function test_London_getOffsetInfo_fromDST()
    {
        $test = $this->europeLondon();
        $this->checkOffset($test, $this->createLDT(2008, 10, 24), self::$OFFSET_PONE, 1);
        $this->checkOffset($test, $this->createLDT(2008, 10, 25), self::$OFFSET_PONE, 1);
        $this->checkOffset($test, $this->createLDT(2008, 10, 26), self::$OFFSET_PONE, 1);
        $this->checkOffset($test, $this->createLDT(2008, 10, 27), self::$OFFSET_ZERO, 1);
        $this->checkOffset($test, $this->createLDT(2008, 10, 28), self::$OFFSET_ZERO, 1);
        $this->checkOffset($test, $this->createLDT(2008, 10, 29), self::$OFFSET_ZERO, 1);
        $this->checkOffset($test, $this->createLDT(2008, 10, 30), self::$OFFSET_ZERO, 1);
        $this->checkOffset($test, $this->createLDT(2008, 10, 31), self::$OFFSET_ZERO, 1);
        // cutover at 01:00Z
        $this->checkOffset($test, LocalDateTime::of(2008, 10, 26, 0, 59, 59, 999999999), self::$OFFSET_PONE, 1);
        $this->checkOffset($test, LocalDateTime::of(2008, 10, 26, 2, 0, 0, 0), self::$OFFSET_ZERO, 1);
    }

    public function test_London_getOffsetInfo_gap()
    {
        $test = $this->europeLondon();
        $dateTime = LocalDateTime::of(2008, 3, 30, 1, 0, 0, 0);
        $trans = $this->checkOffset($test, $dateTime, self::$OFFSET_ZERO, self::$GAP);
        $this->assertEquals($trans->isGap(), true);
        $this->assertEquals($trans->isOverlap(), false);
        $this->assertEquals($trans->getOffsetBefore(), self::$OFFSET_ZERO);
        $this->assertEquals($trans->getOffsetAfter(), self::$OFFSET_PONE);
        $this->assertEquals($trans->getInstant(), $this->createInstant6(2008, 3, 30, 1, 0, ZoneOffset::UTC()));
        $this->assertEquals($trans->getDateTimeBefore(), LocalDateTime::of(2008, 3, 30, 1, 0));
        $this->assertEquals($trans->getDateTimeAfter(), LocalDateTime::of(2008, 3, 30, 2, 0));
        $this->assertEquals($trans->isValidOffset(self::$OFFSET_ZERO), false);
        $this->assertEquals($trans->isValidOffset(self::$OFFSET_PONE), false);
        $this->assertEquals($trans->isValidOffset(self::$OFFSET_PTWO), false);
        $this->assertEquals($trans->__toString(), "Transition[Gap at 2008-03-30T01:00Z to +01:00]");

        $this->assertFalse($trans->equals(null));
        $this->assertFalse($trans->equals(self::$OFFSET_ZERO));
        $this->assertTrue($trans->equals($trans));

        $otherTrans = $test->getTransition($dateTime);
        $this->assertTrue($trans->equals($otherTrans));
        //$this->assertEquals($trans->hashCode(), $otherTrans->hashCode());
    }

    public function test_London_getOffsetInfo_overlap()
    {
        $test = $this->europeLondon();

        $dateTime = LocalDateTime::of(2008, 10, 26, 1, 0, 0, 0);
        $trans = $this->checkOffset($test, $dateTime, self::$OFFSET_PONE, self::$OVERLAP);
        $this->assertEquals($trans->isGap(), false);
        $this->assertEquals($trans->isOverlap(), true);
        $this->assertEquals($trans->getOffsetBefore(), self::$OFFSET_PONE);
        $this->assertEquals($trans->getOffsetAfter(), self::$OFFSET_ZERO);
        $this->assertEquals($trans->getInstant(), $this->createInstant6(2008, 10, 26, 1, 0, ZoneOffset::UTC()));
        $this->assertEquals($trans->getDateTimeBefore(), LocalDateTime::of(2008, 10, 26, 2, 0));
        $this->assertEquals($trans->getDateTimeAfter(), LocalDateTime::of(2008, 10, 26, 1, 0));
        $this->assertEquals($trans->isValidOffset(ZoneOffset::ofHours(-1)), false);
        $this->assertEquals($trans->isValidOffset(self::$OFFSET_ZERO), true);
        $this->assertEquals($trans->isValidOffset(self::$OFFSET_PONE), true);
        $this->assertEquals($trans->isValidOffset(self::$OFFSET_PTWO), false);
        $this->assertEquals($trans->__toString(), "Transition[Overlap at 2008-10-26T02:00+01:00 to Z]");

        $this->assertFalse($trans->equals(null));
        $this->assertFalse($trans->equals(self::$OFFSET_PONE));
        $this->assertTrue($trans->equals($trans));

        $otherTrans = $test->getTransition($dateTime);
        $this->assertTrue($trans->equals($otherTrans));
        //$this->assertEquals($trans->hashCode(), $otherTrans->hashCode());
    }

    public function test_London_getStandardOffset()
    {
        $test = $this->europeLondon();
        $zdt = $this->createZDT(1840, 1, 1, ZoneOffset::UTC());
        while ($zdt->getYear() < 2010) {
            $instant = $zdt->toInstant();
            if ($zdt->getYear() < 1848) {
                $this->assertEquals($test->getStandardOffset($instant), ZoneOffset::ofHoursMinutesSeconds(0, -1, -15));
            } else if ($zdt->getYear() >= 1969 && $zdt->getYear() < 1972) {
                $this->assertEquals($test->getStandardOffset($instant), self::$OFFSET_PONE);
            } else {
                $this->assertEquals($test->getStandardOffset($instant), self::$OFFSET_ZERO);
            }
            $zdt = $zdt->plusMonths(6);
        }
    }

    public function test_London_getTransitions()
    {
        $test = $this->europeLondon();
        $trans = $test->getTransitions();

        $first = $trans[0];
        $this->assertEquals($first->getDateTimeBefore(), LocalDateTime::of(1847, 12, 1, 0, 0));
        $this->assertEquals($first->getOffsetBefore(), ZoneOffset::ofHoursMinutesSeconds(0, -1, -15));
        $this->assertEquals($first->getOffsetAfter(), self::$OFFSET_ZERO);

        $spring1916 = $trans[1];
        $this->assertEquals($spring1916->getDateTimeBefore(), LocalDateTime::of(1916, 5, 21, 2, 0));
        $this->assertEquals($spring1916->getOffsetBefore(), self::$OFFSET_ZERO);
        $this->assertEquals($spring1916->getOffsetAfter(), self::$OFFSET_PONE);

        $autumn1916 = $trans[2];
        $this->assertEquals($autumn1916->getDateTimeBefore(), LocalDateTime::of(1916, 10, 1, 3, 0));
        $this->assertEquals($autumn1916->getOffsetBefore(), self::$OFFSET_PONE);
        $this->assertEquals($autumn1916->getOffsetAfter(), self::$OFFSET_ZERO);

        $zot = null;
        $it = new \CachingIterator(new \ArrayIterator($trans));
        while ($it->hasNext()) {
            $it->next();
            $zot = $it->current();
            if ($zot->getDateTimeBefore()->getYear() === 1990) {
                break;
            }
        }
        $this->assertEquals($zot->getDateTimeBefore(), LocalDateTime::of(1990, 3, 25, 1, 0));
        $this->assertEquals($zot->getOffsetBefore(), self::$OFFSET_ZERO);
        $it->next();
        $zot = $it->current();
        $this->assertEquals($zot->getDateTimeBefore(), LocalDateTime::of(1990, 10, 28, 2, 0));
        $this->assertEquals($zot->getOffsetBefore(), self::$OFFSET_PONE);
        $it->next();
        $zot = $it->current();
        $this->assertEquals($zot->getDateTimeBefore(), LocalDateTime::of(1991, 3, 31, 1, 0));
        $this->assertEquals($zot->getOffsetBefore(), self::$OFFSET_ZERO);
        $it->next();
        $zot = $it->current();
        $this->assertEquals($zot->getDateTimeBefore(), LocalDateTime::of(1991, 10, 27, 2, 0));
        $this->assertEquals($zot->getOffsetBefore(), self::$OFFSET_PONE);
        $it->next();
        $zot = $it->current();
        $this->assertEquals($zot->getDateTimeBefore(), LocalDateTime::of(1992, 3, 29, 1, 0));
        $this->assertEquals($zot->getOffsetBefore(), self::$OFFSET_ZERO);
        $it->next();
        $zot = $it->current();
        $this->assertEquals($zot->getDateTimeBefore(), LocalDateTime::of(1992, 10, 25, 2, 0));
        $this->assertEquals($zot->getOffsetBefore(), self::$OFFSET_PONE);
        $it->next();
        $zot = $it->current();
        $this->assertEquals($zot->getDateTimeBefore(), LocalDateTime::of(1993, 3, 28, 1, 0));
        $this->assertEquals($zot->getOffsetBefore(), self::$OFFSET_ZERO);
        $it->next();
        $zot = $it->current();
        $this->assertEquals($zot->getDateTimeBefore(), LocalDateTime::of(1993, 10, 24, 2, 0));
        $this->assertEquals($zot->getOffsetBefore(), self::$OFFSET_PONE);
        $it->next();
        $zot = $it->current();
        $this->assertEquals($zot->getDateTimeBefore(), LocalDateTime::of(1994, 3, 27, 1, 0));
        $this->assertEquals($zot->getOffsetBefore(), self::$OFFSET_ZERO);
        $it->next();
        $zot = $it->current();
        $this->assertEquals($zot->getDateTimeBefore(), LocalDateTime::of(1994, 10, 23, 2, 0));
        $this->assertEquals($zot->getOffsetBefore(), self::$OFFSET_PONE);
        $it->next();
        $zot = $it->current();
        $this->assertEquals($zot->getDateTimeBefore(), LocalDateTime::of(1995, 3, 26, 1, 0));
        $this->assertEquals($zot->getOffsetBefore(), self::$OFFSET_ZERO);
        $it->next();
        $zot = $it->current();
        $this->assertEquals($zot->getDateTimeBefore(), LocalDateTime::of(1995, 10, 22, 2, 0));
        $this->assertEquals($zot->getOffsetBefore(), self::$OFFSET_PONE);
        $it->next();
        $zot = $it->current();
        $this->assertEquals($zot->getDateTimeBefore(), LocalDateTime::of(1996, 3, 31, 1, 0));
        $this->assertEquals($zot->getOffsetBefore(), self::$OFFSET_ZERO);
        $it->next();
        $zot = $it->current();
        $this->assertEquals($zot->getDateTimeBefore(), LocalDateTime::of(1996, 10, 27, 2, 0));
        $this->assertEquals($zot->getOffsetBefore(), self::$OFFSET_PONE);
        $it->next();
        $zot = $it->current();
        $this->assertEquals($zot->getDateTimeBefore(), LocalDateTime::of(1997, 3, 30, 1, 0));
        $this->assertEquals($zot->getOffsetBefore(), self::$OFFSET_ZERO);
        $it->next();
        $zot = $it->current();
        $this->assertEquals($zot->getDateTimeBefore(), LocalDateTime::of(1997, 10, 26, 2, 0));
        $this->assertEquals($zot->getOffsetBefore(), self::$OFFSET_PONE);
        $this->assertEquals($it->hasNext(), false);
    }

    public function test_London_getTransitionRules()
    {
        $test = $this->europeLondon();
        $rules = $test->getTransitionRules();
        $this->assertEquals(count($rules), 2);

        $in = $rules[0];
        $this->assertEquals($in->getMonth(), Month::MARCH());
        $this->assertEquals($in->getDayOfMonthIndicator(), 25);  // optimized from -1
        $this->assertEquals($in->getDayOfWeek(), DayOfWeek::SUNDAY());
        $this->assertEquals($in->getLocalTime(), LocalTime::of(1, 0));
        $this->assertEquals($in->getTimeDefinition(), TimeDefinition::UTC());
        $this->assertEquals($in->getStandardOffset(), self::$OFFSET_ZERO);
        $this->assertEquals($in->getOffsetBefore(), self::$OFFSET_ZERO);
        $this->assertEquals($in->getOffsetAfter(), self::$OFFSET_PONE);

        $out = $rules[1];
        $this->assertEquals($out->getMonth(), Month::OCTOBER());
        $this->assertEquals($out->getDayOfMonthIndicator(), 25);  // optimized from -1
        $this->assertEquals($out->getDayOfWeek(), DayOfWeek::SUNDAY());
        $this->assertEquals($out->getLocalTime(), LocalTime::of(1, 0));
        $this->assertEquals($out->getTimeDefinition(), TimeDefinition::UTC());
        $this->assertEquals($out->getStandardOffset(), self::$OFFSET_ZERO);
        $this->assertEquals($out->getOffsetBefore(), self::$OFFSET_PONE);
        $this->assertEquals($out->getOffsetAfter(), self::$OFFSET_ZERO);
    }

    //-----------------------------------------------------------------------
    public function test_London_nextTransition_historic()
    {
        $test = $this->europeLondon();
        $trans = $test->getTransitions();

        $first = $trans[0];
        $this->assertEquals($test->nextTransition($first->getInstant()->minusNanos(1)), $first);

        for ($i = 0; $i < count($trans) - 1; $i++) {
            $cur = $trans[$i];
            $next = $trans[$i + 1];

            $this->assertEquals($test->nextTransition($cur->getInstant()), $next);
            $this->assertEquals($test->nextTransition($next->getInstant()->minusNanos(1)), $next);
        }
    }

    public function test_London_nextTransition_rulesBased()
    {
        $test = $this->europeLondon();
        $rules = $test->getTransitionRules();
        $trans = $test->getTransitions();

        $last = $trans[count($trans) - 1];
        $this->assertEquals($test->nextTransition($last->getInstant()), $rules[0]->createTransition(1998));

        for ($year = 1998; $year < 2010; $year++) {
            $a = $rules[0]->createTransition($year);
            $b = $rules[1]->createTransition($year);
            $c = $rules[0]->createTransition($year + 1);

            $this->assertEquals($test->nextTransition($a->getInstant()), $b);
            $this->assertEquals($test->nextTransition($b->getInstant()->minusNanos(1)), $b);

            $this->assertEquals($test->nextTransition($b->getInstant()), $c);
            $this->assertEquals($test->nextTransition($c->getInstant()->minusNanos(1)), $c);
        }
    }

    public function test_London_nextTransition_lastYear()
    {
        $test = $this->europeLondon();
        $rules = $test->getTransitionRules();
        $zot = $rules[1]->createTransition(Year::MAX_VALUE);
        $this->assertEquals($test->nextTransition($zot->getInstant()), null);
    }

    //-----------------------------------------------------------------------
    public function test_London_previousTransition_historic()
    {
        $test = $this->europeLondon();
        $trans = $test->getTransitions();

        $first = $trans[0];
        $this->assertEquals($test->previousTransition($first->getInstant()), null);
        $this->assertEquals($test->previousTransition($first->getInstant()->minusNanos(1)), null);

        for ($i = 0; $i < count($trans) - 1; $i++) {
            $prev = $trans[$i];
            $cur = $trans[$i + 1];

            $this->assertEquals($test->previousTransition($cur->getInstant()), $prev);
            $this->assertEquals($test->previousTransition($prev->getInstant()->plusSeconds(1)), $prev);
            $this->assertEquals($test->previousTransition($prev->getInstant()->plusNanos(1)), $prev);
        }
    }

    public function test_London_previousTransition_rulesBased()
    {
        $test = $this->europeLondon();
        $rules = $test->getTransitionRules();
        $trans = $test->getTransitions();

        $last = $trans[count($trans) - 1];
        $this->assertEquals($test->previousTransition($last->getInstant()->plusSeconds(1)), $last);
        $this->assertEquals($test->previousTransition($last->getInstant()->plusNanos(1)), $last);

        // Jan 1st of year between transitions and rules
        $odt = ZonedDateTime::ofInstant($last->getInstant(), $last->getOffsetAfter());
        $odt = $odt->withDayOfYear(1)->plusYears(1)->adjust(LocalTime::MIDNIGHT());
        $this->assertEquals($test->previousTransition($odt->toInstant()), $last);

        // later years
        for ($year = 1998; $year < 2010; $year++) {
            $a = $rules[0]->createTransition($year);
            $b = $rules[1]->createTransition($year);
            $c = $rules[0]->createTransition($year + 1);

            $this->assertEquals($test->previousTransition($c->getInstant()), $b);
            $this->assertEquals($test->previousTransition($b->getInstant()->plusSeconds(1)), $b);
            $this->assertEquals($test->previousTransition($b->getInstant()->plusNanos(1)), $b);

            $this->assertEquals($test->previousTransition($b->getInstant()), $a);
            $this->assertEquals($test->previousTransition($a->getInstant()->plusSeconds(1)), $a);
            $this->assertEquals($test->previousTransition($a->getInstant()->plusNanos(1)), $a);
        }
    }

    //-----------------------------------------------------------------------
    // Europe/Paris
    //-----------------------------------------------------------------------
    private function europeParis()
    {
        return ZoneId::of("Europe/Paris")->getRules();
    }

    public function test_Paris()
    {
        $test = $this->europeParis();
        $this->assertEquals($test->isFixedOffset(), false);
    }

    public function test_Paris_preTimeZones()
    {
        $test = $this->europeParis();
        $old = $this->createZDT(1800, 1, 1, ZoneOffset::UTC());
        $instant = $old->toInstant();
        $offset = ZoneOffset::ofHoursMinutesSeconds(0, 9, 21);
        $this->assertEquals($test->getOffset($instant), $offset);
        $this->checkOffset($test, $old->toLocalDateTime(), $offset, 1);
        $this->assertEquals($test->getStandardOffset($instant), $offset);
        $this->assertEquals($test->getDaylightSavings($instant), Duration::ZERO());
        $this->assertEquals($test->isDaylightSavings($instant), false);
    }

    public function test_Paris_getOffset()
    {
        $test = $this->europeParis();
        $this->assertEquals($test->getOffset($this->createInstant(2008, 1, 1, ZoneOffset::UTC())), self::$OFFSET_PONE);
        $this->assertEquals($test->getOffset($this->createInstant(2008, 2, 1, ZoneOffset::UTC())), self::$OFFSET_PONE);
        $this->assertEquals($test->getOffset($this->createInstant(2008, 3, 1, ZoneOffset::UTC())), self::$OFFSET_PONE);
        $this->assertEquals($test->getOffset($this->createInstant(2008, 4, 1, ZoneOffset::UTC())), self::$OFFSET_PTWO);
        $this->assertEquals($test->getOffset($this->createInstant(2008, 5, 1, ZoneOffset::UTC())), self::$OFFSET_PTWO);
        $this->assertEquals($test->getOffset($this->createInstant(2008, 6, 1, ZoneOffset::UTC())), self::$OFFSET_PTWO);
        $this->assertEquals($test->getOffset($this->createInstant(2008, 7, 1, ZoneOffset::UTC())), self::$OFFSET_PTWO);
        $this->assertEquals($test->getOffset($this->createInstant(2008, 8, 1, ZoneOffset::UTC())), self::$OFFSET_PTWO);
        $this->assertEquals($test->getOffset($this->createInstant(2008, 9, 1, ZoneOffset::UTC())), self::$OFFSET_PTWO);
        $this->assertEquals($test->getOffset($this->createInstant(2008, 10, 1, ZoneOffset::UTC())), self::$OFFSET_PTWO);
        $this->assertEquals($test->getOffset($this->createInstant(2008, 11, 1, ZoneOffset::UTC())), self::$OFFSET_PONE);
        $this->assertEquals($test->getOffset($this->createInstant(2008, 12, 1, ZoneOffset::UTC())), self::$OFFSET_PONE);
    }

    public function test_Paris_getOffset_toDST()
    {
        $test = $this->europeParis();
        $this->assertEquals($test->getOffset($this->createInstant(2008, 3, 24, ZoneOffset::UTC())), self::$OFFSET_PONE);
        $this->assertEquals($test->getOffset($this->createInstant(2008, 3, 25, ZoneOffset::UTC())), self::$OFFSET_PONE);
        $this->assertEquals($test->getOffset($this->createInstant(2008, 3, 26, ZoneOffset::UTC())), self::$OFFSET_PONE);
        $this->assertEquals($test->getOffset($this->createInstant(2008, 3, 27, ZoneOffset::UTC())), self::$OFFSET_PONE);
        $this->assertEquals($test->getOffset($this->createInstant(2008, 3, 28, ZoneOffset::UTC())), self::$OFFSET_PONE);
        $this->assertEquals($test->getOffset($this->createInstant(2008, 3, 29, ZoneOffset::UTC())), self::$OFFSET_PONE);
        $this->assertEquals($test->getOffset($this->createInstant(2008, 3, 30, ZoneOffset::UTC())), self::$OFFSET_PONE);
        $this->assertEquals($test->getOffset($this->createInstant(2008, 3, 31, ZoneOffset::UTC())), self::$OFFSET_PTWO);
        // cutover at 01:00Z
        $this->assertEquals($test->getOffset($this->createInstant8(2008, 3, 30, 0, 59, 59, 999999999, ZoneOffset::UTC())), self::$OFFSET_PONE);
        $this->assertEquals($test->getOffset($this->createInstant8(2008, 3, 30, 1, 0, 0, 0, ZoneOffset::UTC())), self::$OFFSET_PTWO);
    }

    public function test_Paris_getOffset_fromDST()
    {
        $test = $this->europeParis();
        $this->assertEquals($test->getOffset($this->createInstant(2008, 10, 24, ZoneOffset::UTC())), self::$OFFSET_PTWO);
        $this->assertEquals($test->getOffset($this->createInstant(2008, 10, 25, ZoneOffset::UTC())), self::$OFFSET_PTWO);
        $this->assertEquals($test->getOffset($this->createInstant(2008, 10, 26, ZoneOffset::UTC())), self::$OFFSET_PTWO);
        $this->assertEquals($test->getOffset($this->createInstant(2008, 10, 27, ZoneOffset::UTC())), self::$OFFSET_PONE);
        $this->assertEquals($test->getOffset($this->createInstant(2008, 10, 28, ZoneOffset::UTC())), self::$OFFSET_PONE);
        $this->assertEquals($test->getOffset($this->createInstant(2008, 10, 29, ZoneOffset::UTC())), self::$OFFSET_PONE);
        $this->assertEquals($test->getOffset($this->createInstant(2008, 10, 30, ZoneOffset::UTC())), self::$OFFSET_PONE);
        $this->assertEquals($test->getOffset($this->createInstant(2008, 10, 31, ZoneOffset::UTC())), self::$OFFSET_PONE);
        // cutover at 01:00Z
        $this->assertEquals($test->getOffset($this->createInstant8(2008, 10, 26, 0, 59, 59, 999999999, ZoneOffset::UTC())), self::$OFFSET_PTWO);
        $this->assertEquals($test->getOffset($this->createInstant8(2008, 10, 26, 1, 0, 0, 0, ZoneOffset::UTC())), self::$OFFSET_PONE);
    }

    public function test_Paris_getOffsetInfo()
    {
        $test = $this->europeParis();
        $this->checkOffset($test, $this->createLDT(2008, 1, 1), self::$OFFSET_PONE, 1);
        $this->checkOffset($test, $this->createLDT(2008, 2, 1), self::$OFFSET_PONE, 1);
        $this->checkOffset($test, $this->createLDT(2008, 3, 1), self::$OFFSET_PONE, 1);
        $this->checkOffset($test, $this->createLDT(2008, 4, 1), self::$OFFSET_PTWO, 1);
        $this->checkOffset($test, $this->createLDT(2008, 5, 1), self::$OFFSET_PTWO, 1);
        $this->checkOffset($test, $this->createLDT(2008, 6, 1), self::$OFFSET_PTWO, 1);
        $this->checkOffset($test, $this->createLDT(2008, 7, 1), self::$OFFSET_PTWO, 1);
        $this->checkOffset($test, $this->createLDT(2008, 8, 1), self::$OFFSET_PTWO, 1);
        $this->checkOffset($test, $this->createLDT(2008, 9, 1), self::$OFFSET_PTWO, 1);
        $this->checkOffset($test, $this->createLDT(2008, 10, 1), self::$OFFSET_PTWO, 1);
        $this->checkOffset($test, $this->createLDT(2008, 11, 1), self::$OFFSET_PONE, 1);
        $this->checkOffset($test, $this->createLDT(2008, 12, 1), self::$OFFSET_PONE, 1);
    }

    public function test_Paris_getOffsetInfo_toDST()
    {
        $test = $this->europeParis();
        $this->checkOffset($test, $this->createLDT(2008, 3, 24), self::$OFFSET_PONE, 1);
        $this->checkOffset($test, $this->createLDT(2008, 3, 25), self::$OFFSET_PONE, 1);
        $this->checkOffset($test, $this->createLDT(2008, 3, 26), self::$OFFSET_PONE, 1);
        $this->checkOffset($test, $this->createLDT(2008, 3, 27), self::$OFFSET_PONE, 1);
        $this->checkOffset($test, $this->createLDT(2008, 3, 28), self::$OFFSET_PONE, 1);
        $this->checkOffset($test, $this->createLDT(2008, 3, 29), self::$OFFSET_PONE, 1);
        $this->checkOffset($test, $this->createLDT(2008, 3, 30), self::$OFFSET_PONE, 1);
        $this->checkOffset($test, $this->createLDT(2008, 3, 31), self::$OFFSET_PTWO, 1);
        // cutover at 01:00Z which is 02:00+01:00(local Paris time)
        $this->checkOffset($test, LocalDateTime::of(2008, 3, 30, 1, 59, 59, 999999999), self::$OFFSET_PONE, 1);
        $this->checkOffset($test, LocalDateTime::of(2008, 3, 30, 3, 0, 0, 0), self::$OFFSET_PTWO, 1);
    }

    public function test_Paris_getOffsetInfo_fromDST()
    {
        $test = $this->europeParis();
        $this->checkOffset($test, $this->createLDT(2008, 10, 24), self::$OFFSET_PTWO, 1);
        $this->checkOffset($test, $this->createLDT(2008, 10, 25), self::$OFFSET_PTWO, 1);
        $this->checkOffset($test, $this->createLDT(2008, 10, 26), self::$OFFSET_PTWO, 1);
        $this->checkOffset($test, $this->createLDT(2008, 10, 27), self::$OFFSET_PONE, 1);
        $this->checkOffset($test, $this->createLDT(2008, 10, 28), self::$OFFSET_PONE, 1);
        $this->checkOffset($test, $this->createLDT(2008, 10, 29), self::$OFFSET_PONE, 1);
        $this->checkOffset($test, $this->createLDT(2008, 10, 30), self::$OFFSET_PONE, 1);
        $this->checkOffset($test, $this->createLDT(2008, 10, 31), self::$OFFSET_PONE, 1);
        // cutover at 01:00Z which is 02:00+01:00(local Paris time)
        $this->checkOffset($test, LocalDateTime::of(2008, 10, 26, 1, 59, 59, 999999999), self::$OFFSET_PTWO, 1);
        $this->checkOffset($test, LocalDateTime::of(2008, 10, 26, 3, 0, 0, 0), self::$OFFSET_PONE, 1);
    }

    public function test_Paris_getOffsetInfo_gap()
    {
        $test = $this->europeParis();
        $dateTime = LocalDateTime::of(2008, 3, 30, 2, 0, 0, 0);
        $trans = $this->checkOffset($test, $dateTime, self::$OFFSET_PONE, self::$GAP);
        $this->assertEquals($trans->isGap(), true);
        $this->assertEquals($trans->isOverlap(), false);
        $this->assertEquals($trans->getOffsetBefore(), self::$OFFSET_PONE);
        $this->assertEquals($trans->getOffsetAfter(), self::$OFFSET_PTWO);
        $this->assertEquals($trans->getInstant(), $this->createInstant6(2008, 3, 30, 1, 0, ZoneOffset::UTC()));
        $this->assertEquals($trans->isValidOffset(self::$OFFSET_ZERO), false);
        $this->assertEquals($trans->isValidOffset(self::$OFFSET_PONE), false);
        $this->assertEquals($trans->isValidOffset(self::$OFFSET_PTWO), false);
        $this->assertEquals($trans->__toString(), "Transition[Gap at 2008-03-30T02:00+01:00 to +02:00]");

        $this->assertFalse($trans->equals(null));
        $this->assertFalse($trans->equals(self::$OFFSET_PONE));
        $this->assertTrue($trans->equals($trans));

        $otherTrans = $test->getTransition($dateTime);
        $this->assertTrue($trans->equals($otherTrans));
        //$this->assertEquals($trans->hashCode(), otherTrans.hashCode());
    }

    public function test_Paris_getOffsetInfo_overlap()
    {
        $test = $this->europeParis();
        $dateTime = LocalDateTime::of(2008, 10, 26, 2, 0, 0, 0);
        $trans = $this->checkOffset($test, $dateTime, self::$OFFSET_PTWO, self::$OVERLAP);
        $this->assertEquals($trans->isGap(), false);
        $this->assertEquals($trans->isOverlap(), true);
        $this->assertEquals($trans->getOffsetBefore(), self::$OFFSET_PTWO);
        $this->assertEquals($trans->getOffsetAfter(), self::$OFFSET_PONE);
        $this->assertEquals($trans->getInstant(), $this->createInstant6(2008, 10, 26, 1, 0, ZoneOffset::UTC()));
        $this->assertEquals($trans->isValidOffset(self::$OFFSET_ZERO), false);
        $this->assertEquals($trans->isValidOffset(self::$OFFSET_PONE), true);
        $this->assertEquals($trans->isValidOffset(self::$OFFSET_PTWO), true);
        $this->assertEquals($trans->isValidOffset(ZoneOffset::ofHours(3)), false);
        $this->assertEquals($trans->__toString(), "Transition[Overlap at 2008-10-26T03:00+02:00 to +01:00]");

        $this->assertFalse($trans->equals(null));
        $this->assertFalse($trans->equals(self::$OFFSET_PTWO));
        $this->assertTrue($trans->equals($trans));

        $otherTrans = $test->getTransition($dateTime);
        $this->assertTrue($trans->equals($otherTrans));
        //$this->assertEquals($trans->hashCode(), otherTrans.hashCode());
    }

    public function test_Paris_getStandardOffset()
    {
        $test = $this->europeParis();
        $zdt = $this->createZDT(1840, 1, 1, ZoneOffset::UTC());
        while ($zdt->getYear() < 2010) {
            $instant = $zdt->toInstant();
            if ($zdt->toLocalDate()->isBefore(LocalDate::of(1911, 3, 11))) {
                $this->assertEquals($test->getStandardOffset($instant), ZoneOffset::ofHoursMinutesSeconds(0, 9, 21));
            } else if ($zdt->toLocalDate()->isBefore(LocalDate::of(1940, 6, 14))) {
                $this->assertEquals($test->getStandardOffset($instant), self::$OFFSET_ZERO);
            } else if ($zdt->toLocalDate()->isBefore(LocalDate::of(1944, 8, 25))) {
                $this->assertEquals($test->getStandardOffset($instant), self::$OFFSET_PONE);
            } else if ($zdt->toLocalDate()->isBefore(LocalDate::of(1945, 9, 16))) {
                $this->assertEquals($test->getStandardOffset($instant), self::$OFFSET_ZERO);
            } else {
                $this->assertEquals($test->getStandardOffset($instant), self::$OFFSET_PONE);
            }
            $zdt = $zdt->plusMonths(6);
        }
    }

    //-----------------------------------------------------------------------
    // America/New_York
    //-----------------------------------------------------------------------
    private function americaNewYork()
    {
        return ZoneId::of("America/New_York")->getRules();
    }

    public function test_NewYork()
    {
        $test = $this->americaNewYork();
        $this->assertEquals($test->isFixedOffset(), false);
    }

    public function test_NewYork_preTimeZones()
    {
        $test = $this->americaNewYork();
        $old = $this->createZDT(1800, 1, 1, ZoneOffset::UTC());
        $instant = $old->toInstant();
        $offset = ZoneOffset::of("-04:56:02");
        $this->assertEquals($test->getOffset($instant), $offset);
        $this->checkOffset($test, $old->toLocalDateTime(), $offset, 1);
        $this->assertEquals($test->getStandardOffset($instant), $offset);
        $this->assertEquals($test->getDaylightSavings($instant), Duration::ZERO());
        $this->assertEquals($test->isDaylightSavings($instant), false);
    }

    public function test_NewYork_getOffset()
    {
        $test = $this->americaNewYork();
        $offset = ZoneOffset::ofHours(-5);
        $this->assertEquals($test->getOffset($this->createInstant(2008, 1, 1, $offset)), ZoneOffset::ofHours(-5));
        $this->assertEquals($test->getOffset($this->createInstant(2008, 2, 1, $offset)), ZoneOffset::ofHours(-5));
        $this->assertEquals($test->getOffset($this->createInstant(2008, 3, 1, $offset)), ZoneOffset::ofHours(-5));
        $this->assertEquals($test->getOffset($this->createInstant(2008, 4, 1, $offset)), ZoneOffset::ofHours(-4));
        $this->assertEquals($test->getOffset($this->createInstant(2008, 5, 1, $offset)), ZoneOffset::ofHours(-4));
        $this->assertEquals($test->getOffset($this->createInstant(2008, 6, 1, $offset)), ZoneOffset::ofHours(-4));
        $this->assertEquals($test->getOffset($this->createInstant(2008, 7, 1, $offset)), ZoneOffset::ofHours(-4));
        $this->assertEquals($test->getOffset($this->createInstant(2008, 8, 1, $offset)), ZoneOffset::ofHours(-4));
        $this->assertEquals($test->getOffset($this->createInstant(2008, 9, 1, $offset)), ZoneOffset::ofHours(-4));
        $this->assertEquals($test->getOffset($this->createInstant(2008, 10, 1, $offset)), ZoneOffset::ofHours(-4));
        $this->assertEquals($test->getOffset($this->createInstant(2008, 11, 1, $offset)), ZoneOffset::ofHours(-4));
        $this->assertEquals($test->getOffset($this->createInstant(2008, 12, 1, $offset)), ZoneOffset::ofHours(-5));
        $this->assertEquals($test->getOffset($this->createInstant(2008, 1, 28, $offset)), ZoneOffset::ofHours(-5));
        $this->assertEquals($test->getOffset($this->createInstant(2008, 2, 28, $offset)), ZoneOffset::ofHours(-5));
        $this->assertEquals($test->getOffset($this->createInstant(2008, 3, 28, $offset)), ZoneOffset::ofHours(-4));
        $this->assertEquals($test->getOffset($this->createInstant(2008, 4, 28, $offset)), ZoneOffset::ofHours(-4));
        $this->assertEquals($test->getOffset($this->createInstant(2008, 5, 28, $offset)), ZoneOffset::ofHours(-4));
        $this->assertEquals($test->getOffset($this->createInstant(2008, 6, 28, $offset)), ZoneOffset::ofHours(-4));
        $this->assertEquals($test->getOffset($this->createInstant(2008, 7, 28, $offset)), ZoneOffset::ofHours(-4));
        $this->assertEquals($test->getOffset($this->createInstant(2008, 8, 28, $offset)), ZoneOffset::ofHours(-4));
        $this->assertEquals($test->getOffset($this->createInstant(2008, 9, 28, $offset)), ZoneOffset::ofHours(-4));
        $this->assertEquals($test->getOffset($this->createInstant(2008, 10, 28, $offset)), ZoneOffset::ofHours(-4));
        $this->assertEquals($test->getOffset($this->createInstant(2008, 11, 28, $offset)), ZoneOffset::ofHours(-5));
        $this->assertEquals($test->getOffset($this->createInstant(2008, 12, 28, $offset)), ZoneOffset::ofHours(-5));
    }

    public function test_NewYork_getOffset_toDST()
    {
        $test = $this->americaNewYork();
        $offset = ZoneOffset::ofHours(-5);
        $this->assertEquals($test->getOffset($this->createInstant(2008, 3, 8, $offset)), ZoneOffset::ofHours(-5));
        $this->assertEquals($test->getOffset($this->createInstant(2008, 3, 9, $offset)), ZoneOffset::ofHours(-5));
        $this->assertEquals($test->getOffset($this->createInstant(2008, 3, 10, $offset)), ZoneOffset::ofHours(-4));
        $this->assertEquals($test->getOffset($this->createInstant(2008, 3, 11, $offset)), ZoneOffset::ofHours(-4));
        $this->assertEquals($test->getOffset($this->createInstant(2008, 3, 12, $offset)), ZoneOffset::ofHours(-4));
        $this->assertEquals($test->getOffset($this->createInstant(2008, 3, 13, $offset)), ZoneOffset::ofHours(-4));
        $this->assertEquals($test->getOffset($this->createInstant(2008, 3, 14, $offset)), ZoneOffset::ofHours(-4));
        // cutover at 02:00 local
        $this->assertEquals($test->getOffset($this->createInstant8(2008, 3, 9, 1, 59, 59, 999999999, $offset)), ZoneOffset::ofHours(-5));
        $this->assertEquals($test->getOffset($this->createInstant8(2008, 3, 9, 2, 0, 0, 0, $offset)), ZoneOffset::ofHours(-4));
    }

    public function test_NewYork_getOffset_fromDST()
    {
        $test = $this->americaNewYork();
        $offset = ZoneOffset::ofHours(-4);
        $this->assertEquals($test->getOffset($this->createInstant(2008, 11, 1, $offset)), ZoneOffset::ofHours(-4));
        $this->assertEquals($test->getOffset($this->createInstant(2008, 11, 2, $offset)), ZoneOffset::ofHours(-4));
        $this->assertEquals($test->getOffset($this->createInstant(2008, 11, 3, $offset)), ZoneOffset::ofHours(-5));
        $this->assertEquals($test->getOffset($this->createInstant(2008, 11, 4, $offset)), ZoneOffset::ofHours(-5));
        $this->assertEquals($test->getOffset($this->createInstant(2008, 11, 5, $offset)), ZoneOffset::ofHours(-5));
        $this->assertEquals($test->getOffset($this->createInstant(2008, 11, 6, $offset)), ZoneOffset::ofHours(-5));
        $this->assertEquals($test->getOffset($this->createInstant(2008, 11, 7, $offset)), ZoneOffset::ofHours(-5));
        // cutover at 02:00 local
        $this->assertEquals($test->getOffset($this->createInstant8(2008, 11, 2, 1, 59, 59, 999999999, $offset)), ZoneOffset::ofHours(-4));
        $this->assertEquals($test->getOffset($this->createInstant8(2008, 11, 2, 2, 0, 0, 0, $offset)), ZoneOffset::ofHours(-5));
    }

    public function test_NewYork_getOffsetInfo()
    {
        $test = $this->americaNewYork();
        $this->checkOffset($test, $this->createLDT(2008, 1, 1), ZoneOffset::ofHours(-5), 1);
        $this->checkOffset($test, $this->createLDT(2008, 2, 1), ZoneOffset::ofHours(-5), 1);
        $this->checkOffset($test, $this->createLDT(2008, 3, 1), ZoneOffset::ofHours(-5), 1);
        $this->checkOffset($test, $this->createLDT(2008, 4, 1), ZoneOffset::ofHours(-4), 1);
        $this->checkOffset($test, $this->createLDT(2008, 5, 1), ZoneOffset::ofHours(-4), 1);
        $this->checkOffset($test, $this->createLDT(2008, 6, 1), ZoneOffset::ofHours(-4), 1);
        $this->checkOffset($test, $this->createLDT(2008, 7, 1), ZoneOffset::ofHours(-4), 1);
        $this->checkOffset($test, $this->createLDT(2008, 8, 1), ZoneOffset::ofHours(-4), 1);
        $this->checkOffset($test, $this->createLDT(2008, 9, 1), ZoneOffset::ofHours(-4), 1);
        $this->checkOffset($test, $this->createLDT(2008, 10, 1), ZoneOffset::ofHours(-4), 1);
        $this->checkOffset($test, $this->createLDT(2008, 11, 1), ZoneOffset::ofHours(-4), 1);
        $this->checkOffset($test, $this->createLDT(2008, 12, 1), ZoneOffset::ofHours(-5), 1);
        $this->checkOffset($test, $this->createLDT(2008, 1, 28), ZoneOffset::ofHours(-5), 1);
        $this->checkOffset($test, $this->createLDT(2008, 2, 28), ZoneOffset::ofHours(-5), 1);
        $this->checkOffset($test, $this->createLDT(2008, 3, 28), ZoneOffset::ofHours(-4), 1);
        $this->checkOffset($test, $this->createLDT(2008, 4, 28), ZoneOffset::ofHours(-4), 1);
        $this->checkOffset($test, $this->createLDT(2008, 5, 28), ZoneOffset::ofHours(-4), 1);
        $this->checkOffset($test, $this->createLDT(2008, 6, 28), ZoneOffset::ofHours(-4), 1);
        $this->checkOffset($test, $this->createLDT(2008, 7, 28), ZoneOffset::ofHours(-4), 1);
        $this->checkOffset($test, $this->createLDT(2008, 8, 28), ZoneOffset::ofHours(-4), 1);
        $this->checkOffset($test, $this->createLDT(2008, 9, 28), ZoneOffset::ofHours(-4), 1);
        $this->checkOffset($test, $this->createLDT(2008, 10, 28), ZoneOffset::ofHours(-4), 1);
        $this->checkOffset($test, $this->createLDT(2008, 11, 28), ZoneOffset::ofHours(-5), 1);
        $this->checkOffset($test, $this->createLDT(2008, 12, 28), ZoneOffset::ofHours(-5), 1);
    }

    public function test_NewYork_getOffsetInfo_toDST()
    {
        $test = $this->americaNewYork();
        $this->checkOffset($test, $this->createLDT(2008, 3, 8), ZoneOffset::ofHours(-5), 1);
        $this->checkOffset($test, $this->createLDT(2008, 3, 9), ZoneOffset::ofHours(-5), 1);
        $this->checkOffset($test, $this->createLDT(2008, 3, 10), ZoneOffset::ofHours(-4), 1);
        $this->checkOffset($test, $this->createLDT(2008, 3, 11), ZoneOffset::ofHours(-4), 1);
        $this->checkOffset($test, $this->createLDT(2008, 3, 12), ZoneOffset::ofHours(-4), 1);
        $this->checkOffset($test, $this->createLDT(2008, 3, 13), ZoneOffset::ofHours(-4), 1);
        $this->checkOffset($test, $this->createLDT(2008, 3, 14), ZoneOffset::ofHours(-4), 1);
        // cutover at 02:00 local
        $this->checkOffset($test, LocalDateTime::of(2008, 3, 9, 1, 59, 59, 999999999), ZoneOffset::ofHours(-5), 1);
        $this->checkOffset($test, LocalDateTime::of(2008, 3, 9, 3, 0, 0, 0), ZoneOffset::ofHours(-4), 1);
    }

    public function test_NewYork_getOffsetInfo_fromDST()
    {
        $test = $this->americaNewYork();
        $this->checkOffset($test, $this->createLDT(2008, 11, 1), ZoneOffset::ofHours(-4), 1);
        $this->checkOffset($test, $this->createLDT(2008, 11, 2), ZoneOffset::ofHours(-4), 1);
        $this->checkOffset($test, $this->createLDT(2008, 11, 3), ZoneOffset::ofHours(-5), 1);
        $this->checkOffset($test, $this->createLDT(2008, 11, 4), ZoneOffset::ofHours(-5), 1);
        $this->checkOffset($test, $this->createLDT(2008, 11, 5), ZoneOffset::ofHours(-5), 1);
        $this->checkOffset($test, $this->createLDT(2008, 11, 6), ZoneOffset::ofHours(-5), 1);
        $this->checkOffset($test, $this->createLDT(2008, 11, 7), ZoneOffset::ofHours(-5), 1);
        // cutover at 02:00 local
        $this->checkOffset($test, LocalDateTime::of(2008, 11, 2, 0, 59, 59, 999999999), ZoneOffset::ofHours(-4), 1);
        $this->checkOffset($test, LocalDateTime::of(2008, 11, 2, 2, 0, 0, 0), ZoneOffset::ofHours(-5), 1);
    }

    public function test_NewYork_getOffsetInfo_gap()
    {
        $test = $this->americaNewYork();
        $dateTime = LocalDateTime::of(2008, 3, 9, 2, 0, 0, 0);
        $trans = $this->checkOffset($test, $dateTime, ZoneOffset::ofHours(-5), self::$GAP);
        $this->assertEquals($trans->isGap(), true);
        $this->assertEquals($trans->isOverlap(), false);
        $this->assertEquals($trans->getOffsetBefore(), ZoneOffset::ofHours(-5));
        $this->assertEquals($trans->getOffsetAfter(), ZoneOffset::ofHours(-4));
        $this->assertEquals($trans->getInstant(), $this->createInstant6(2008, 3, 9, 2, 0, ZoneOffset::ofHours(-5)));
        $this->assertEquals($trans->isValidOffset(self::$OFFSET_PTWO), false);
        $this->assertEquals($trans->isValidOffset(ZoneOffset::ofHours(-5)), false);
        $this->assertEquals($trans->isValidOffset(ZoneOffset::ofHours(-4)), false);
        $this->assertEquals($trans->__toString(), "Transition[Gap at 2008-03-09T02:00-05:00 to -04:00]");

        $this->assertFalse($trans->equals(null));
        $this->assertFalse($trans->equals(ZoneOffset::ofHours(-5)));
        $this->assertTrue($trans->equals($trans));

        $otherTrans = $test->getTransition($dateTime);
        $this->assertTrue($trans->equals($otherTrans));
        //$this->assertEquals($trans->hashCode(), otherTrans.hashCode());
    }

    public function test_NewYork_getOffsetInfo_overlap()
    {
        $test = $this->americaNewYork();
        $dateTime = LocalDateTime::of(2008, 11, 2, 1, 0, 0, 0);
        $trans = $this->checkOffset($test, $dateTime, ZoneOffset::ofHours(-4), self::$OVERLAP);
        $this->assertEquals($trans->isGap(), false);
        $this->assertEquals($trans->isOverlap(), true);
        $this->assertEquals($trans->getOffsetBefore(), ZoneOffset::ofHours(-4));
        $this->assertEquals($trans->getOffsetAfter(), ZoneOffset::ofHours(-5));
        $this->assertEquals($trans->getInstant(), $this->createInstant6(2008, 11, 2, 2, 0, ZoneOffset::ofHours(-4)));
        $this->assertEquals($trans->isValidOffset(ZoneOffset::ofHours(-1)), false);
        $this->assertEquals($trans->isValidOffset(ZoneOffset::ofHours(-5)), true);
        $this->assertEquals($trans->isValidOffset(ZoneOffset::ofHours(-4)), true);
        $this->assertEquals($trans->isValidOffset(self::$OFFSET_PTWO), false);
        $this->assertEquals($trans->__toString(), "Transition[Overlap at 2008-11-02T02:00-04:00 to -05:00]");

        $this->assertFalse($trans->equals(null));
        $this->assertFalse($trans->equals(ZoneOffset::ofHours(-4)));
        $this->assertTrue($trans->equals($trans));

        $otherTrans = $test->getTransition($dateTime);
        $this->assertTrue($trans->equals($otherTrans));
        //$this->assertEquals($trans->hashCode(), otherTrans.hashCode());
    }

    public function test_NewYork_getStandardOffset()
    {
        $test = $this->americaNewYork();
        $dateTime = $this->createZDT(1860, 1, 1, ZoneOffset::UTC());
        while ($dateTime->getYear() < 2010) {
            $instant = $dateTime->toInstant();
            if ($dateTime->toLocalDate()->isBefore(LocalDate::of(1883, 11, 18))) {
                $this->assertEquals($test->getStandardOffset($instant), ZoneOffset::of("-04:56:02"));
            } else {
                $this->assertEquals($test->getStandardOffset($instant), ZoneOffset::ofHours(-5));
            }
            $dateTime = $dateTime->plusMonths(6);
        }
    }

    //-----------------------------------------------------------------------
    // Kathmandu
    //-----------------------------------------------------------------------
    private function asiaKathmandu()
    {
        return ZoneId::of("Asia/Kathmandu")->getRules();
    }

    public function test_Kathmandu_nextTransition_historic()
    {
        $test = $this->asiaKathmandu();
        $trans = $test->getTransitions();

        $first = $trans[0];
        $this->assertEquals($test->nextTransition($first->getInstant()->minusNanos(1)), $first);

        for ($i = 0; $i < count($trans) - 1; $i++) {
            $cur = $trans[$i];
            $next = $trans[$i + 1];

            $this->assertEquals($test->nextTransition($cur->getInstant()), $next);
            $this->assertEquals($test->nextTransition($next->getInstant()->minusNanos(1)), $next);
        }
    }

    public function test_Kathmandu_nextTransition_noRules()
    {
        $test = $this->asiaKathmandu();
        $trans = $test->getTransitions();

        $last = $trans[count($trans) - 1];
        $this->assertEquals($test->nextTransition($last->getInstant()), null);
    }

    //-----------------------------------------------------------------------
    // Apia
    //-----------------------------------------------------------------------
    private function pacificApia()
    {
        return ZoneId::of("Pacific/Apia")->getRules();
    }

    public function test_Apia_nextTransition_historic()
    {
        $test = $this->pacificApia();
        $trans = $test->getTransitions();

        $first = $trans[0];
        $this->assertEquals($test->nextTransition($first->getInstant()->minusNanos(1)), $first);

        for ($i = 0; $i < count($trans) - 1; $i++) {
            $cur = $trans[$i];
            $next = $trans[$i + 1];

            $this->assertEquals($test->nextTransition($cur->getInstant()), $next);
            $this->assertEquals($test->nextTransition($next->getInstant()->minusNanos(1)), $next);
        }
    }

    public function test_Apia_jumpOverInternationalDateLine_M10_to_P14()
    {
        // transition occurred at 2011-12-30T00:00-10:00
        $test = $this->pacificApia();
        $instantBefore = LocalDate::of(2011, 12, 27)->atStartOfDayWithZone(ZoneOffset::UTC())->toInstant();
        $trans = $test->nextTransition($instantBefore);
        $this->assertEquals($trans->getDateTimeBefore(), LocalDateTime::of(2011, 12, 30, 0, 0));
        $this->assertEquals($trans->getDateTimeAfter(), LocalDateTime::of(2011, 12, 31, 0, 0));
        $this->assertEquals($trans->isGap(), true);
        $this->assertEquals($trans->isOverlap(), false);
        $this->assertEquals($trans->isValidOffset(ZoneOffset::ofHours(-10)), false);
        $this->assertEquals($trans->isValidOffset(ZoneOffset::ofHours(+14)), false);
        $this->assertEquals($trans->getDuration(), Duration::ofHours(24));
        $this->assertEquals($trans->getInstant(), LocalDateTime::of(2011, 12, 31, 0, 0)->toInstant(ZoneOffset::ofHours(+14)));

        $zdt = ZonedDateTime::of(2011, 12, 29, 23, 0, 0, 0, ZoneId::of("Pacific/Apia"));
        $this->assertEquals($zdt->plusHours(2)->toLocalDateTime(), LocalDateTime::of(2011, 12, 31, 1, 0));
    }

    public function test_Apia_jumpForwardOverInternationalDateLine_P12_to_M12()
    {
        // transition occurred at 1879-07-04T00:00+12:33:04
        $test = $this->pacificApia();
        $instantBefore = LocalDate::of(1879, 7, 2)->atStartOfDayWithZone(ZoneOffset::UTC())->toInstant();
        $trans = $test->nextTransition($instantBefore);
        $this->assertEquals($trans->getDateTimeBefore(), LocalDateTime::of(1879, 7, 5, 0, 0));
        $this->assertEquals($trans->getDateTimeAfter(), LocalDateTime::of(1879, 7, 4, 0, 0));
        $this->assertEquals($trans->isGap(), false);
        $this->assertEquals($trans->isOverlap(), true);
        $this->assertEquals($trans->isValidOffset(ZoneOffset::ofHoursMinutesSeconds(+12, 33, 4)), true);
        $this->assertEquals($trans->isValidOffset(ZoneOffset::ofHoursMinutesSeconds(-11, -26, -56)), true);
        $this->assertEquals($trans->getDuration(), Duration::ofHours(-24));
        $this->assertEquals($trans->getInstant(), LocalDateTime::of(1879, 7, 4, 0, 0)->toInstant(ZoneOffset::ofHoursMinutesSeconds(-11, -26, -56)));

        $zdt = ZonedDateTime::of(1879, 7, 4, 23, 0, 0, 0, ZoneId::of("Pacific/Apia"));
        $this->assertEquals($zdt->plusHours(2)->toLocalDateTime(), LocalDateTime::of(1879, 7, 4, 1, 0, 0));
    }

    //-----------------------------------------------------------------------
    // of()
    //-----------------------------------------------------------------------
    public function test_of()
    {
        //used for standard offset
        $stdOffset1 = ZoneOffset::UTC();
        $stdOffset2 = ZoneOffset::ofHours(1);
        $time_of_stdOffsetTransition1 = LocalDateTime::of(2013, 1, 5, 1, 0);
        $stdOffsetTransition1 = ZoneOffsetTransition::of($time_of_stdOffsetTransition1, $stdOffset1, $stdOffset2);
        $stdOffsetTransition_list = [];
        $stdOffsetTransition_list[] = $stdOffsetTransition1;

        //used for wall offset
        $wallOffset1 = ZoneOffset::ofHours(2);
        $wallOffset2 = ZoneOffset::ofHours(4);
        $wallOffset3 = ZoneOffset::ofHours(7);

        $time_of_wallOffsetTransition1 = LocalDateTime::of(2013, 2, 5, 1, 0);
        $time_of_wallOffsetTransition2 = LocalDateTime::of(2013, 3, 5, 1, 0);
        $time_of_wallOffsetTransition3 = LocalDateTime::of(2013, 10, 5, 1, 0);

        $wallOffsetTransition1 = ZoneOffsetTransition::of($time_of_wallOffsetTransition1, $wallOffset1, $wallOffset2);
        $wallOffsetTransition2 = ZoneOffsetTransition::of($time_of_wallOffsetTransition2, $wallOffset2, $wallOffset3);
        $wallOffsetTransition3 = ZoneOffsetTransition::of($time_of_wallOffsetTransition3, $wallOffset3, $wallOffset1);

        $wallOffsetTransition_list = [];
        $wallOffsetTransition_list[] = ($wallOffsetTransition1);
        $wallOffsetTransition_list[] = ($wallOffsetTransition2);
        $wallOffsetTransition_list[] = ($wallOffsetTransition3);

        //used for ZoneOffsetTransitionRule
        $ruleOffset = ZoneOffset::ofHours(3);
        $timeDefinition = TimeDefinition::WALL();
        $rule1 = ZoneOffsetTransitionRule::of(Month::FEBRUARY(),
            2,
            DayOfWeek::MONDAY(),
            LocalTime::of(1, 0),
            false,
            $timeDefinition,
            ZoneOffset::UTC(),
            ZoneOffset::UTC(),
            $ruleOffset
        );
        $rule_list = [];
        $rule_list[] = $rule1;

        //Begin verification
        $zoneRule = ZoneRules::of($stdOffset1,
            $wallOffset1,
            $stdOffsetTransition_list,
            $wallOffsetTransition_list,
            $rule_list
        );

        $before_time_of_stdOffsetTransition1 = OffsetDateTime::ofDateTime($time_of_stdOffsetTransition1, $stdOffset1)->minusSeconds(1);
        $after_time_of_stdOffsetTransition1 = OffsetDateTime::ofDateTime($time_of_stdOffsetTransition1, $stdOffset1)->plusSeconds(1);;
        $this->assertEquals($zoneRule->getStandardOffset($before_time_of_stdOffsetTransition1->toInstant()), $stdOffset1);
        $this->assertEquals($zoneRule->getStandardOffset($after_time_of_stdOffsetTransition1->toInstant()), $stdOffset2);

        $before_time_of_wallOffsetTransition1 = OffsetDateTime::ofDateTime($time_of_wallOffsetTransition1, $wallOffset1)->minusSeconds(1);
        $after_time_of_wallOffsetTransition1 = OffsetDateTime::ofDateTime($time_of_wallOffsetTransition1, $wallOffset1)->plusSeconds(1);
        $this->assertEquals($zoneRule->nextTransition($before_time_of_wallOffsetTransition1->toInstant()), $wallOffsetTransition1);
        $this->assertEquals($zoneRule->nextTransition($after_time_of_wallOffsetTransition1->toInstant()), $wallOffsetTransition2);

        $before_time_of_wallOffsetTransition2 = OffsetDateTime::ofDateTime($time_of_wallOffsetTransition2, $wallOffset2)->minusSeconds(1);
        $after_time_of_wallOffsetTransition2 = OffsetDateTime::ofDateTime($time_of_wallOffsetTransition2, $wallOffset2)->plusSeconds(1);
        $this->assertEquals($zoneRule->nextTransition($before_time_of_wallOffsetTransition2->toInstant()), $wallOffsetTransition2);
        $this->assertEquals($zoneRule->nextTransition($after_time_of_wallOffsetTransition2->toInstant()), $wallOffsetTransition3);

        $before_time_of_wallOffsetTransition3 = OffsetDateTime::ofDateTime($time_of_wallOffsetTransition3, $wallOffset3)->minusSeconds(1);
        $after_time_of_wallOffsetTransition3 = OffsetDateTime::ofDateTime($time_of_wallOffsetTransition3, $wallOffset3)->plusSeconds(1);
        $this->assertEquals($zoneRule->nextTransition($before_time_of_wallOffsetTransition3->toInstant()), $wallOffsetTransition3);
        $this->assertEquals($zoneRule->nextTransition($after_time_of_wallOffsetTransition3->toInstant()), $rule1->createTransition(2014));
    }

    //-----------------------------------------------------------------------
    // equals() / hashCode()
    //-----------------------------------------------------------------------
    public function test_equals()
    {
        $test1 = $this->europeLondon();
        $test2 = $this->europeParis();
        $test2b = $this->europeParis();
        $this->assertEquals($test1->equals($test2), false);
        $this->assertEquals($test2->equals($test1), false);

        $this->assertEquals($test1->equals($test1), true);
        $this->assertEquals($test2->equals($test2), true);
        $this->assertEquals($test2->equals($test2b), true);

        //$this->assertEquals($test1->hashCode() == test1->hashCode(), true);
        //$this->assertEquals($test2->hashCode() == test2->hashCode(), true);
        //$this->assertEquals($test2->hashCode() == test2b->hashCode(), true);
    }

    public function test_equals_null()
    {
        $this->assertEquals($this->europeLondon()->equals(null), false);
    }

    public function test_equals_notZoneRules()
    {
        $this->assertEquals($this->europeLondon()->equals("Europe/London"), false);
    }

    public function test_toString()
    {
        $this->assertContains("ZoneRules", $this->europeLondon()->__toString());
    }

    private function createInstant($year, $month, $day, ZoneOffset $offset)
    {
        return LocalDateTime::of($year, $month, $day, 0, 0)->toInstant($offset);
    }

    private function createInstant6($year, $month, $day, $hour, $min, ZoneOffset $offset)
    {
        return LocalDateTime::of($year, $month, $day, $hour, $min)->toInstant($offset);
    }

    private function createInstant8($year, $month, $day, $hour, $min, $sec, $nano, ZoneOffset $offset)
    {
        return LocalDateTime::of($year, $month, $day, $hour, $min, $sec, $nano)->toInstant($offset);
    }

    private function createZDT($year, $month, $day, ZoneId $zone)
    {
        return LocalDateTime::of($year, $month, $day, 0, 0)->atZone($zone);
    }

    private function createLDT($year, $month, $day)
    {
        return LocalDateTime::of($year, $month, $day, 0, 0);
    }

    private function checkOffset(ZoneRules $rules, LocalDateTime $dateTime, ZoneOffset $offset, $type)
    {
        $validOffsets = $rules->getValidOffsets($dateTime);
        $this->assertEquals(count($validOffsets), $type);
        $this->assertEquals($rules->getOffsetDateTime($dateTime), $offset);
        if ($type === 1) {
            $this->assertEquals($validOffsets[0], $offset);
            return null;
        } else {
            $zot = $rules->getTransition($dateTime);
            $this->assertNotNull($zot);
            $this->assertEquals($zot->isOverlap(), $type == 2);
            $this->assertEquals($zot->isGap(), $type == 0);
            $this->assertEquals($zot->isValidOffset($offset), $type == 2);
            return $zot;
        }
    }
}
