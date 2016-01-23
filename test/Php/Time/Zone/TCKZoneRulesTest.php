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
namespace Php\Time\Zone;

use Php\Time\DayOfWeek;
use Php\Time\LocalDateTime;
use Php\Time\LocalTime;
use Php\Time\Month;
use Php\Time\OffsetDateTime;
use Php\Time\ZoneOffset;
use PHPUnit_Framework_TestCase;

/**
 * TODO incomplete
 */
class ZoneRulesTest extends PHPUnit_Framework_TestCase
{

    //-----------------------------------------------------------------------
    // of()
    //-----------------------------------------------------------------------
    public function test_of()
    {
        //used for standard offset
        $stdOffset1 = ZoneOffset::UTC();
        $stdOffset2 = ZoneOffset::ofHours(1);
        $time_of_stdOffsetTransition1 = LocalDateTime::ofNumerical(2013, 1, 5, 1, 0);
        $stdOffsetTransition1 = ZoneOffsetTransition::of($time_of_stdOffsetTransition1, $stdOffset1, $stdOffset2);
        $stdOffsetTransition_list = [];
        $stdOffsetTransition_list[] = $stdOffsetTransition1;

        //used for wall offset
        $wallOffset1 = ZoneOffset::ofHours(2);
        $wallOffset2 = ZoneOffset::ofHours(4);
        $wallOffset3 = ZoneOffset::ofHours(7);

        $time_of_wallOffsetTransition1 = LocalDateTime::ofNumerical(2013, 2, 5, 1, 0);
        $time_of_wallOffsetTransition2 = LocalDateTime::ofNumerical(2013, 3, 5, 1, 0);
        $time_of_wallOffsetTransition3 = LocalDateTime::ofNumerical(2013, 10, 5, 1, 0);

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

        $before_time_of_stdOffsetTransition1 = OffsetDateTime::of($time_of_stdOffsetTransition1, $stdOffset1)->minusSeconds(1);
        $after_time_of_stdOffsetTransition1 = OffsetDateTime::of($time_of_stdOffsetTransition1, $stdOffset1)->plusSeconds(1);;
        $this->assertEquals($zoneRule->getStandardOffset($before_time_of_stdOffsetTransition1->toInstant()), $stdOffset1);
        $this->assertEquals($zoneRule->getStandardOffset($after_time_of_stdOffsetTransition1->toInstant()), $stdOffset2);

        $before_time_of_wallOffsetTransition1 = OffsetDateTime::of($time_of_wallOffsetTransition1, $wallOffset1)->minusSeconds(1);
        $after_time_of_wallOffsetTransition1 = OffsetDateTime::of($time_of_wallOffsetTransition1, $wallOffset1)->plusSeconds(1);
        $this->assertEquals($zoneRule->nextTransition($before_time_of_wallOffsetTransition1->toInstant()), $wallOffsetTransition1);
        $this->assertEquals($zoneRule->nextTransition($after_time_of_wallOffsetTransition1->toInstant()), $wallOffsetTransition2);

        $before_time_of_wallOffsetTransition2 = OffsetDateTime::of($time_of_wallOffsetTransition2, $wallOffset2)->minusSeconds(1);
        $after_time_of_wallOffsetTransition2 = OffsetDateTime::of($time_of_wallOffsetTransition2, $wallOffset2)->plusSeconds(1);
        $this->assertEquals($zoneRule->nextTransition($before_time_of_wallOffsetTransition2->toInstant()), $wallOffsetTransition2);
        $this->assertEquals($zoneRule->nextTransition($after_time_of_wallOffsetTransition2->toInstant()), $wallOffsetTransition3);

        $before_time_of_wallOffsetTransition3 = OffsetDateTime::of($time_of_wallOffsetTransition3, $wallOffset3)->minusSeconds(1);
        $after_time_of_wallOffsetTransition3 = OffsetDateTime::of($time_of_wallOffsetTransition3, $wallOffset3)->plusSeconds(1);
        $this->assertEquals($zoneRule->nextTransition($before_time_of_wallOffsetTransition3->toInstant()), $wallOffsetTransition3);
        $this->assertEquals($zoneRule->nextTransition($after_time_of_wallOffsetTransition3->toInstant()), $rule1->createTransition(2014));
    }

}
