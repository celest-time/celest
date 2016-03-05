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
 * Copyright (c) 2012, Stephen Colebourne & Michael Nascimento Santos
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

use Celest\Helper\Long;
use Celest\Temporal\ChronoUnit;
use Celest\Temporal\Temporal;
use Celest\Temporal\TemporalAmount;
use Celest\Temporal\TemporalUnit;
use Celest\Temporal\ChronoUnit as CU;

/**
 * Mock period of time measured using a single unit, such as {@code 3 Days}.
 */
final class MockSimplePeriod implements TemporalAmount {

    /**
     * A constant for a period of zero, measured in days.
     * @return MockSimplePeriod
     */
    public static function ZERO_DAYS() { return new MockSimplePeriod(0, CU::DAYS()); }
    /**
     * A constant for a period of zero, measured in seconds.
     * @return MockSimplePeriod
     */
    public static function ZERO_SECONDS() { return new MockSimplePeriod(0, CU::SECONDS()); }

    /**
     * The amount of the period.
     * @var int
     */
    private $amount;
    /**
     * The unit the period is measured in.
     * @var TemporalUnit
     */
    private $unit;

    /**
     * Obtains a {@code MockSimplePeriod} from an amount and unit.
     * <p>
     * The parameters represent the two parts of a phrase like '6 Days'.
     *
     * @param int $amount the amount of the period, measured in terms of the unit, positive or negative
     * @param TemporalUnit $unit  the unit that the period is measured in, must not be the 'Forever' unit, not null
     * @return MockSimplePeriod the {@code MockSimplePeriod} instance, not null
     * @throws DateTimeException if the period unit is {@link java.time.temporal.ChronoUnit#FOREVER}.
     */
    public static function of($amount, TemporalUnit $unit) {
        return new MockSimplePeriod($amount, $unit);
    }

    private function __construct($amount, TemporalUnit $unit) {
        if ($unit == CU::FOREVER()) {
            throw new DateTimeException("Cannot create a period of the Forever unit");
        }
        $this->amount = $amount;
        $this->unit = $unit;
    }

    
    public function get(TemporalUnit $unit) {
        throw new \LogicException("Not supported yet.");
    }

    
    public function getUnits() {
        throw new \LogicException("Not supported yet.");
    }

    //-----------------------------------------------------------------------
    public function getAmount() {
        return $this->amount;
    }

    public function getUnit() {
        return $this->unit;
    }

    //-------------------------------------------------------------------------
    public function addTo(Temporal $temporal) {
        return $temporal->plus($this->amount, $this->unit);
    }

    public function subtractFrom(Temporal $temporal) {
        return $temporal->minus($this->amount, $this->unit);
    }

    //-----------------------------------------------------------------------
    
    public function compareTo(MockSimplePeriod $otherPeriod) {
        if ($this->unit->equals($otherPeriod->getUnit()) == false) {
            throw new IllegalArgumentException("Units cannot be compared: " . $this->unit . " and " . $otherPeriod->getUnit());
        }
        return Long::compare($this->amount, $otherPeriod->amount);
    }

    
    public function equals($obj) {
        if ($this === $obj) {
            return true;
        }
        if ($obj instanceof MockSimplePeriod) {
            $other = $obj;
            return $this->amount == $other->amount &&
            $this->unit->equals($other->unit);
        }
        return false;
    }
    
    public function __toString() {
        return $this->amount . " " . $this->unit;
    }

}
