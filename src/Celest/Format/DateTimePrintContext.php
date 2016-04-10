<?php
/*
 * Copyright (c) 2012, 2013, Oracle and/or its affiliates. All rights reserved.
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
namespace Celest\Format;

use Celest\Chrono\ChronoLocalDate;
use Celest\Chrono\Chronology;
use Celest\Chrono\IsoChronology;
use Celest\DateTimeException;
use Celest\Instant;
use Celest\Locale;
use Celest\Temporal\AbstractTemporalAccessor;
use Celest\Temporal\ChronoField;
use Celest\Temporal\TemporalAccessor;
use Celest\Temporal\TemporalField;
use Celest\Temporal\TemporalQueries;
use Celest\Temporal\TemporalQuery;
use Celest\ZoneId;
use Celest\ZoneOffset;

class Test extends AbstractTemporalAccessor
{
    /** @var ChronoLocalDate */
    private $effectiveDate;
    /** @var TemporalAccessor */
    private $temporal;
    /** @var ZoneId|null */
    private $effectiveZone;
    /** @var Chronology */
    private $effectiveChrono;

    /**
     * Test constructor.
     * @param ChronoLocalDate $effectiveDate
     * @param TemporalAccessor $temporal
     * @param ZoneId|null $effectiveZone
     * @param Chronology|null $effectiveChrono
     */
    public function __construct($effectiveDate, TemporalAccessor $temporal, $effectiveZone, $effectiveChrono)
    {
        $this->effectiveDate = $effectiveDate;
        $this->temporal = $temporal;
        $this->effectiveZone = $effectiveZone;
        $this->effectiveChrono = $effectiveChrono;
    }


    public function isSupported(TemporalField $field)
    {
        if ($this->effectiveDate != null && $field->isDateBased()) {
            return $this->effectiveDate->isSupported($field);
        }

        return $this->temporal->isSupported($field);
    }

    public function range(TemporalField $field)
    {
        if ($this->effectiveDate != null && $field->isDateBased()) {
            return $this->effectiveDate->range($field);
        }

        return $this->temporal->range($field);
    }

    public function getLong(TemporalField $field)
    {
        if ($this->effectiveDate != null && $field->isDateBased()) {
            return $this->effectiveDate->getLong($field);
        }

        return $this->temporal->getLong($field);
    }

    public function query(TemporalQuery $query)
    {
        if ($query == TemporalQueries::chronology()) {
            return $this->effectiveChrono;
        }

        if ($query == TemporalQueries::zoneId()) {
            return $this->effectiveZone;
        }
        if ($query == TemporalQueries::precision()) {
            return $this->temporal->query($query);
        }
        return $query->queryFrom($this);
    }
}


/**
 * Context object used during date and time printing.
 * <p>
 * This class provides a single wrapper to items used in the format.
 *
 * @implSpec
 * This class is a mutable context intended for use from a single thread.
 * Usage of the class is thread-safe within standard printing as the framework creates
 * a new instance of the class for each format and printing is single-threaded.
 *
 * @since 1.8
 */
final class DateTimePrintContext
{

    /**
     * The temporal being output.
     * @var TemporalAccessor
     */
    private $temporal;
    /**
     * The formatter, not null.
     * @var DateTimeFormatter
     */
    private $formatter;
    /**
     * Whether the current formatter is optional.
     * @var int
     */
    private $optional;

    /**
     * Creates a new instance of the context.
     *
     * @param TemporalAccessor $temporal the temporal object being output, not null
     * @param DateTimeFormatter $formatter the formatter controlling the format, not null
     */
    public function __construct(TemporalAccessor $temporal, DateTimeFormatter $formatter)
    {
        $this->temporal = $this->adjust($temporal, $formatter);
        $this->formatter = $formatter;
    }

    private static function adjust(TemporalAccessor $temporal, DateTimeFormatter $formatter)
    {
        // normal case first (early return is an optimization)
        $overrideChrono = $formatter->getChronology();
        $overrideZone = $formatter->getZone();
        if ($overrideChrono == null && $overrideZone == null) {
            return $temporal;
        }

// ensure minimal change (early return is an optimization)
        $temporalChrono = $temporal->query(TemporalQueries::chronology());
        $temporalZone = $temporal->query(TemporalQueries::zoneId());
        if ($temporalChrono !== null && $temporalChrono->equals($overrideChrono)) {
            $overrideChrono = null;
        }
        if ($temporalZone !== null && $temporalZone->equals($overrideZone)) {
            $overrideZone = null;
        }
        if ($overrideChrono === null && $overrideZone === null) {
            return $temporal;
        }

        // make adjustment
        $effectiveChrono = ($overrideChrono != null ? $overrideChrono : $temporalChrono);
        if ($overrideZone != null) {
            // if have zone and instant, calculation is simple, defaulting chrono if necessary
            if ($temporal->isSupported(ChronoField::INSTANT_SECONDS())) {
                $chrono = ($effectiveChrono != null ? $effectiveChrono : IsoChronology::INSTANCE());
                return $chrono->zonedDateTime(Instant::from($temporal), $overrideZone);
            }
            // block changing zone on OffsetTime, and similar problem cases
            if ($overrideZone->normalized() instanceof ZoneOffset && $temporal->isSupported(ChronoField::OFFSET_SECONDS()) &&
                $temporal->get(ChronoField::OFFSET_SECONDS()) != $overrideZone->getRules()->getOffset(Instant::EPOCH())->getTotalSeconds()
            ) {
                throw new DateTimeException("Unable to apply override zone '" . $overrideZone .
                    "' because the temporal object being formatted has a different offset but" .
                    " does not represent an instant: " . $temporal);
            }
        }

        $effectiveZone = ($overrideZone !== null ? $overrideZone : $temporalZone);
        $effectiveDate = null;
        if ($overrideChrono !== null) {
            if ($temporal->isSupported(ChronoField::EPOCH_DAY())) {
                $effectiveDate = $effectiveChrono->dateFrom($temporal);
            } else {
                // check for date fields other than epoch-day, ignoring case of converting null to ISO
                if (!($overrideChrono == IsoChronology::INSTANCE() && $temporalChrono === null)) {
                    foreach (ChronoField::values() as $f) {
                        if ($f->isDateBased() && $temporal->isSupported($f)) {
                            throw new DateTimeException("Unable to apply override chronology '" . $overrideChrono .
                                "' because the temporal object being formatted contains date fields but" .
                                " does not represent a whole date: " . $temporal);
                        }
                    }
                }
                $effectiveDate = null;
            }
        } else {
            $effectiveDate = null;
        }

        // combine available data
        // this is a non-standard temporal that is almost a pure delegate
        // this better handles map-like underlying temporal instances
        return new Test($effectiveDate, $temporal, $effectiveZone, $effectiveChrono);
    }

    //-----------------------------------------------------------------------
    /**
     * Gets the temporal object being output.
     *
     * @return TemporalAccessor the temporal object, not null
     */
    public function getTemporal()
    {
        return $this->temporal;
    }

    /**
     * Gets the locale.
     * <p>
     * This locale is used to control localization in the format output except
     * where localization is controlled by the DecimalStyle.
     *
     * @return Locale the locale, not null
     */
    public function getLocale()
    {
        return $this->formatter->getLocale();
    }

    /**
     * Gets the DecimalStyle.
     * <p>
     * The DecimalStyle controls the localization of numeric output.
     *
     * @return DecimalStyle the DecimalStyle, not null
     */
    public function getDecimalStyle()
    {
        return $this->formatter->getDecimalStyle();
    }

    //-----------------------------------------------------------------------
    /**
     * Starts the printing of an optional segment of the input.
     */
    public function startOptional()
    {
        $this->optional++;
    }

    /**
     * Ends the printing of an optional segment of the input.
     */
    public function endOptional()
    {
        $this->optional--;
    }

    /**
     * Gets a value using a query.
     *
     * @param TemporalQuery $query the query to use, not null
     * @return mixed the result, null if not found and optional is true
     * @throws DateTimeException if the type is not available and the section is not optional
     */
    public function getValue(TemporalQuery $query)
    {
        $result = $this->temporal->query($query);
        if ($result == null && $this->optional == 0) {
            throw new DateTimeException("Unable to extract value: " . get_class($this->temporal));
        }

        return $result;
    }

    /**
     * Gets the value of the specified field.
     * <p>
     * This will return the value for the specified field.
     *
     * @param TemporalField $field the field to find, not null
     * @return int the value, null if not found and optional is true
     * @throws DateTimeException if the field is not available and the section is not optional
     */
    public function getValueField(TemporalField $field)
    {
        try {
            return $this->temporal->getLong($field);
        } catch (DateTimeException $ex) {
            if ($this->optional > 0) {
                return null;
            }
            throw $ex;
        }
    }

//-----------------------------------------------------------------------
    /**
     * Returns a string version of the context for debugging.
     *
     * @return string a string representation of the context, not null
     */
    public function __toString()
    {
        return $this->temporal->toString();
    }

}
