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

namespace Celest\Chrono;

use Celest\DateTimeException;
use Celest\Helper\Math;
use Celest\Temporal\ChronoField;
use Celest\Temporal\ChronoUnit;
use Celest\Temporal\Temporal;
use Celest\Temporal\TemporalAccessor;
use Celest\Temporal\TemporalAmount;
use Celest\Temporal\TemporalQueries;
use Celest\Temporal\TemporalUnit;
use Celest\Temporal\UnsupportedTemporalTypeException;

/**
 * A period expressed in terms of a standard year-month-day calendar system.
 * <p>
 * This class is used by applications seeking to handle dates in non-ISO calendar systems.
 * For example, the Japanese, Minguo, Thai Buddhist and others.
 *
 * @implSpec
 * This class is immutable nad thread-safe.
 *
 * @since 1.8
 */
final class ChronoPeriodImpl extends AbstractChronoPeriod implements ChronoPeriod
{
    // this class is only used by JDK chronology implementations and makes assumptions based on that fact

    /**
     * The chronology.
     * @var Chronology
     */
    private $chrono;
    /**
     * The number of years.
     * @var int
     */
    private $years;
    /**
     * The number of months.
     */
    private $months;
    /**
     * The number of days.
     */
    private $days;

    /**
     * Creates an instance.
     * @internal
     */
    public function __construct(Chronology $chrono, $years, $months, $days)
    {
        $this->chrono = $chrono;
        $this->years = $years;
        $this->months = $months;
        $this->days = $days;
    }

    //-----------------------------------------------------------------------
    public function get(TemporalUnit $unit)
    {
        if ($unit == ChronoUnit::YEARS()) {
            return $this->years;
        } else if ($unit == ChronoUnit::MONTHS()) {
            return $this->months;
        } else if ($unit == ChronoUnit::DAYS()) {
            return $this->days;
        } else {
            throw new UnsupportedTemporalTypeException("Unsupported unit: " . $unit);
        }
    }

    public function getUnits()
    {
        return [ChronoUnit::YEARS(), ChronoUnit::MONTHS(), ChronoUnit::DAYS()];
    }

    public function getChronology()
    {
        return $this->chrono;
    }

    //-----------------------------------------------------------------------
    public function isZero()
    {
        return $this->years === 0 && $this->months === 0 && $this->days === 0;
    }

    public function isNegative()
    {
        return $this->years < 0 || $this->months < 0 || $this->days < 0;
    }

    //-----------------------------------------------------------------------
    public function plusAmount(TemporalAmount $amountToAdd)
    {
        $amount = $this->validateAmount($amountToAdd);
        return new ChronoPeriodImpl(
            $this->chrono,
            Math::addExact($this->years, $amount->years),
            Math::addExact($this->months, $amount->months),
            Math::addExact($this->days, $amount->days));
    }

    public function minusAmount(TemporalAmount $amountToSubtract)
    {
        $amount = $this->validateAmount($amountToSubtract);
        return new ChronoPeriodImpl(
            $this->chrono,
            Math::subtractExact($this->years, $amount->years),
            Math::subtractExact($this->months, $amount->months),
            Math::subtractExact($this->days, $amount->days));
    }

    /**
     * Obtains an instance of {@code ChronoPeriodImpl} from a temporal amount.
     *
     * @param $amount TemporalAmount the temporal amount to convert, not null
     * @return ChronoPeriodImpl the period, not null
     */
    private function validateAmount(TemporalAmount $amount)
    {
        if ($amount instanceof ChronoPeriodImpl === false) {
            throw new DateTimeException("Unable to obtain ChronoPeriod from TemporalAmount: " . get_class($amount));
        }
        /** @var ChronoPeriodImpl $period */
        $period = $amount;
        if ($this->chrono->equals($period->getChronology()) == false) {
            throw new \InvalidArgumentException("Chronology mismatch, expected: " . $this->chrono->getId() . ", actual: " . $period->getChronology()->getId());
        }
        return $period;
    }

    //-----------------------------------------------------------------------
    public function multipliedBy($scalar)
    {
        if ($this->isZero() || $scalar === 1) {
            return $this;
        }
        return new ChronoPeriodImpl(
            $this->chrono,
            Math::multiplyExact($this->years, $scalar),
            Math::multiplyExact($this->months, $scalar),
            Math::multiplyExact($this->days, $scalar));
    }

    //-----------------------------------------------------------------------
    public function normalized()
    {
        $monthRange = $this->monthRange();
        if ($monthRange > 0) {
            $totalMonths = $this->years * $monthRange + $this->months;
            $splitYears = Math::div($totalMonths, $monthRange);
            $splitMonths = $totalMonths % $monthRange;  // no overflow
            if ($splitYears === $this->years && $splitMonths === $this->months) {
                return $this;
            }
            return new ChronoPeriodImpl($this->chrono, Math::toIntExact($splitYears), $splitMonths, $this->days);

        }
        return $this;
    }

    /**
     * Calculates the range of months.
     *
     * @return int the month range, -1 if not fixed range
     */
    private function monthRange()
    {
        $startRange = $this->chrono->range(ChronoField::MONTH_OF_YEAR());
        if ($startRange->isFixed() && $startRange->isIntValue()) {
            return $startRange->getMaximum() - $startRange->getMinimum() + 1;
        }
        return -1;
    }

    //-------------------------------------------------------------------------
    public function addTo(Temporal $temporal)
    {
        $this->validateChrono($temporal);
        if ($this->months === 0) {
            if ($this->years !== 0) {
                $temporal = $temporal->plus($this->years, ChronoUnit::YEARS());
            }
        } else {
            $monthRange = $this->monthRange();
            if ($monthRange > 0) {
                $temporal = $temporal->plus($this->years * $monthRange + $this->months, ChronoUnit::MONTHS());
            } else {
                if ($this->years !== 0) {
                    $temporal = $temporal->plus($this->years, ChronoUnit::YEARS());
                }
                $temporal = $temporal->plus($this->months, ChronoUnit::MONTHS());
            }
        }
        if ($this->days !== 0) {
            $temporal = $temporal->plus($this->days, ChronoUnit::DAYS());
        }
        return $temporal;
    }


    public function subtractFrom(Temporal $temporal)
    {
        $this->validateChrono($temporal);
        if ($this->months === 0) {
            if ($this->years !== 0) {
                $temporal = $temporal->minus($this->years, ChronoUnit::YEARS());
            }
        } else {
            $monthRange = $this->monthRange();
            if ($monthRange > 0) {
                $temporal = $temporal->minus($this->years * $monthRange + $this->months, ChronoUnit::MONTHS());
            } else {
                if ($this->years !== 0) {
                    $temporal = $temporal->minus($this->years, ChronoUnit::YEARS());
                }
                $temporal = $temporal->minus($this->months, ChronoUnit::MONTHS());
            }
        }
        if ($this->days !== 0) {
            $temporal = $temporal->minus($this->days, ChronoUnit::DAYS());
        }
        return $temporal;
    }

    /**
     * Validates that the temporal has the correct chronology.
     */
    private function validateChrono(TemporalAccessor $temporal)
    {
        $temporalChrono = $temporal->query(TemporalQueries::chronology());
        if ($temporalChrono !== null && $this->chrono->equals($temporalChrono) == false) {
            throw new DateTimeException("Chronology mismatch, expected: " . $this->chrono->getId() . ", actual: " . $temporalChrono->getId());
        }
    }

    //-----------------------------------------------------------------------
    public function equals($obj)
    {
        if ($this === $obj) {
            return true;
        }
        if ($obj instanceof ChronoPeriodImpl) {
            return $this->years === $obj->years && $this->months === $obj->months &&
            $this->days === $obj->days && $this->chrono->equals($obj->chrono);
        }
        return false;
    }

    //-----------------------------------------------------------------------
    public function __toString()
    {
        if ($this->isZero()) {
            return $this->getChronology()->__toString() . " P0D";
        } else {
            $buf = $this->getChronology()->__toString() . ' P';
            if ($this->years !== 0) {
                $buf .= $this->years . 'Y';
            }
            if ($this->months !== 0) {
                $buf .= $this->months . 'M';
            }
            if ($this->days !== 0) {
                $buf .= $this->days . 'D';
            }
            return $buf;
        }
    }
}
