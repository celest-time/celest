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
 * Copyright (c) 2008-2013, Stephen Colebourne & Michael Nascimento Santos
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
namespace Php\Time\Format;

use Php\Time\Chrono\ChronoLocalDate;
use Php\Time\Chrono\ChronoLocalDateTime;
use Php\Time\Chrono\Chronology;
use Php\Time\Chrono\ChronoZonedDateTime;
use Php\Time\DateTimeException;
use Php\Time\Helper\Math;
use Php\Time\Instant;
use Php\Time\LocalDate;
use Php\Time\LocalTime;
use Php\Time\Period;
use Php\Time\Temporal\ChronoField;
use Php\Time\Temporal\TemporalField;
use Php\Time\Temporal\TemporalAccessor;
use Php\Time\Temporal\TemporalQueries;
use Php\Time\Temporal\TemporalQuery;
use Php\Time\Temporal\UnsupportedTemporalTypeException;
use Php\Time\ZoneId;
use Php\Time\ZoneOffset;

/**
 * A store of parsed data.
 * <p>
 * This class is used during parsing to collect the data. Part of the parsing process
 * involves handling optional blocks and multiple copies of the data get created to
 * support the necessary backtracking.
 * <p>
 * Once parsing is completed, this class can be used as the resultant {@code TemporalAccessor}.
 * In most cases, it is only exposed once the fields have been resolved.
 *
 * @implSpec
 * This class is a mutable context intended for use from a single thread.
 * Usage of the class is thread-safe within standard parsing as a new instance of this class
 * is automatically created for each parse and parsing is single-threaded
 *
 * @since 1.8
 */
final class Parsed implements TemporalAccessor
{
// some fields are accessed using package scope from DateTimeParseContext

    /**
     * The parsed fields.
     * @var int[] TemporalField->int
     */
    public $fieldValues = [];
    /**
     * The parsed zone.
     * @var ZoneId
     */
    public $zone;
    /**
     * The parsed chronology.
     * @var Chronology
     */
    public $chrono;
    /**
     * Whether a leap-second is parsed.
     * @var bool
     */
    public $leapSecond;
    /**
     * The resolver style to use.
     * @var ResolverStyle
     */
    private $resolverStyle;
    /**
     * The resolved date.
     * @var ChronoLocalDate
     */
    private $date;
    /**
     * The resolved time.
     * @var LocalTime
     */
    private $time;
    /**
     * The excess period from time-only parsing.
     * @var Period
     */
    public $excessDays;

    /**
     * Creates an instance.
     */
    public function __construct()
    {
        $this->excessDays = Period::ZERO();
    }

    /**
     * Creates a copy.
     * @return Parsed
     */
    public function copy()
    {
        // only copy fields used in parsing stage
        $cloned = new Parsed();
        $cloned->fieldValues = $this->fieldValues;
        $cloned->zone = $this->zone;
        $cloned->chrono = $this->chrono;
        $cloned->leapSecond = $this->leapSecond;
        return $cloned;
    }

//-----------------------------------------------------------------------
    /**
     * @param TemporalField $field
     * @return bool
     */
    public function isSupported(TemporalField $field)
    {
        if (array_key_exists($field->__toString(), $this->fieldValues) ||
            ($this->date != null && $this->date->isSupported($field)) ||
            ($this->time != null && $this->time->isSupported($field))
        ) {
            return true;
        }

        return $field != null && ($field instanceof ChronoField == false) && $field->isSupportedBy($this);
    }

    /**
     * @param TemporalField $field
     * @return int
     * @throws UnsupportedTemporalTypeException
     */
    public function getLong(TemporalField $field)
    {
        $value = @$this->fieldValues[$field->__toString()];
        if ($value != null) {
            return $value;
        }

        if ($this->date != null && $this->date->isSupported($field)) {
            return $this->date->getLong($field);
        }
        if ($this->time != null && $this->time->isSupported($field)) {
            return $this->time->getLong($field);
        }
        if ($field instanceof ChronoField) {
            throw new UnsupportedTemporalTypeException("Unsupported field: " . $field);
        }
        return $field->getFrom($this);
    }

    public function query(TemporalQuery $query)
    {
        if ($query == TemporalQueries::zoneId()) {
            return $this->zone;
        } else
            if ($query == TemporalQueries::chronology()) {
                return $this->chrono;
            } else if ($query == TemporalQueries::localDate()) {
                return ($this->date != null ? LocalDate::from($this->date) : null);
            } else if ($query == TemporalQueries::localTime()) {
                return $this->time;
            } else if ($query == TemporalQueries::zone() || $query == TemporalQueries::offset()) {
                return $query->queryFrom($this);
            } else if ($query == TemporalQueries::precision()) {
                return null;  // not a complete date/time
            }
        // inline TemporalAccessor.super.query(query) as an optimization
        // non-JDK classes are not permitted to make this optimization
        return $query->queryFrom($this);
    }

//-----------------------------------------------------------------------
    /**
     * Resolves the fields in this context.
     *
     * @param $$this->resolverStyle ResolverStyle the resolver style, not null
     * @param $resolverFields TemporalField[] the fields to use for resolving, null for all fields
     * @return $this, for method chaining
     * @throws DateTimeException if resolving one field results in a value for
     *  another field that is in conflict
     */
    public function resolve(ResolverStyle $resolverStyle, array $resolverFields)
    {
        if ($resolverFields != null) {
            $this->fieldValues->keySet()->retainAll($resolverFields);
        }

        $this->$this->resolverStyle = $$this->resolverStyle;
        $this->resolveFields();
        $this->resolveTimeLenient();
        $this->crossCheck();
        $this->resolvePeriod();
        $this->resolveFractional();
        $this->resolveInstant();
        return $this;
    }

//-----------------------------------------------------------------------
    private
    function resolveFields()
    {
        // resolve ChronoField
        $this->resolveInstantFields();
        $this->resolveDateFields();
        $this->resolveTimeFields();

        // if any other fields, handle them
        // any lenient date resolution should return epoch-day
        if (!empty($this->fieldValues)) {
            $changedCount = 0;
            outer:
            while ($changedCount < 50) {
                foreach ($this->fieldValues as $entry) {
                    $targetField = $entry->getKey();
                    $resolvedObject = $targetField->resolve($this->fieldValues, $this, $this->$this->resolverStyle);
                    if ($resolvedObject != null) {
                        if ($resolvedObject instanceof ChronoZonedDateTime) {
                            $czdt = $resolvedObject;
                            if ($this->zone == null) {
                                $this->zone = $czdt->getZone();
                            } else
                                if ($this->zone->equals($czdt->getZone()) == false) {
                                    throw new DateTimeException("ChronoZonedDateTime must use the effective parsed zone: " . $this->zone);
                                }
                            $resolvedObject = $czdt->toLocalDateTime();
                        }
                        if ($resolvedObject instanceof ChronoLocalDateTime) {
                            $cldt = $resolvedObject;
                            $this->updateCheckConflict($cldt->toLocalTime(), Period::ZERO());
                            $this->updateCheckConflict($cldt->toLocalDate());
                            $changedCount++;
                            continue 2;  // have to restart to avoid concurrent modification
                        }
                        if ($resolvedObject instanceof ChronoLocalDate) {
                            $this->updateCheckConflict($resolvedObject);
                            $changedCount++;
                            continue 2;  // have to restart to avoid concurrent modification
                        }
                        if ($resolvedObject instanceof LocalTime) {
                            $this->updateCheckConflict($resolvedObject, Period::ZERO());
                            $changedCount++;
                            continue 2;  // have to restart to avoid concurrent modification
                        }
                        throw new DateTimeException("Method resolve() can only return ChronoZonedDateTime, " .
                            "ChronoLocalDateTime, ChronoLocalDate or LocalTime");
                    } else if ($this->fieldValues->containsKey($targetField) == false) {
                        $changedCount++;
                        continue 2;  // have to restart to avoid concurrent modification
                    }
                }
                break;
            }
            if ($changedCount == 50) {  // catch infinite loops
                throw new DateTimeException("One of the parsed fields has an incorrectly implemented resolve method");
            }
            // if something changed then have to redo ChronoField resolve
            if ($changedCount > 0) {
                $this->resolveInstantFields();
                $this->resolveDateFields();
                $this->resolveTimeFields();
            }
        }
    }

    private
    function updateCheckConflict(TemporalField $targetField, TemporalField $changeField, $changeValue)
    {
        $old = $this->fieldValues->put($changeField, $changeValue);
        if ($old != null && $old->longValue() != $changeValue->longValue()) {
            throw new DateTimeException("Conflict found: " . $changeField . " " . $old .
                " differs from " . $changeField . " " . $changeValue .
                " while resolving  " . $targetField);
        }
    }

//-----------------------------------------------------------------------
    private
    function resolveInstantFields()
    {
        // resolve parsed instant seconds to date and time if zone available
        if ($this->fieldValues->containsKey(ChronoField::INSTANT_SECONDS())) {
            if ($this->zone != null) {
                $this->resolveInstantFields0($this->zone);
            } else {
                $offsetSecs = $this->fieldValues->get(ChronoField::OFFSET_SECONDS());
                if ($offsetSecs != null) {
                    $offset = ZoneOffset::ofTotalSeconds($offsetSecs->intValue());
                    $this->resolveInstantFields0($offset);
                }
            }
        }
    }

    private
    function resolveInstantFields0(ZoneId $selectedZone)
    {
        $instant = Instant::ofEpochSecond($this->fieldValues->remove(ChronoField::INSTANT_SECONDS()));
        $zdt = $this->chrono->zonedDateTime($instant, $selectedZone);
        $this->updateCheckConflict($zdt->toLocalDate());
        $this->updateCheckConflict(ChronoField::INSTANT_SECONDS(), ChronoField::SECOND_OF_DAY(), $zdt->toLocalTime()->toSecondOfDay());
    }

//-----------------------------------------------------------------------
    private
    function resolveDateFields()
    {
        $this->updateCheckConflict($this->chrono->resolveDate($this->fieldValues, $this->$this->resolverStyle));
    }

    private
    function updateCheckConflict(ChronoLocalDate $cld)
    {
        if ($this->date != null) {
            if ($cld != null && $this->date->equals($cld) == false) {
                throw new DateTimeException("Conflict found: Fields resolved to two different dates: " . $this->date . " " . $cld);
            }
        } else if ($cld != null) {
            if ($this->chrono->equals($cld->getChronology()) == false) {
                throw new DateTimeException("ChronoLocalDate must use the effective parsed chronology: " . $this->chrono);
            }
            $this->date = $cld;
        }
    }

//-----------------------------------------------------------------------
    private
    function resolveTimeFields()
    {
// simplify fields
        if ($this->fieldValues->containsKey(ChronoField::CLOCK_HOUR_OF_DAY())) {
// lenient allows anything, smart allows 0-24, strict allows 1-24
            $ch = $this->fieldValues->remove(ChronoField::CLOCK_HOUR_OF_DAY());
            if ($this->resolverStyle == ResolverStyle::STRICT() || ($this->resolverStyle == ResolverStyle::SMART() && $ch != 0)) {
                ChronoField::CLOCK_HOUR_OF_DAY()->checkValidValue($ch);
            }

            $this->updateCheckConflict(ChronoField::CLOCK_HOUR_OF_DAY(), ChronoField::HOUR_OF_DAY(), $ch == 24 ? 0 : $ch);
        }
        if ($this->fieldValues->containsKey(ChronoField::CLOCK_HOUR_OF_AMPM())) {
// lenient allows anything, smart allows 0-12, strict allows 1-12
            $ch = $this->fieldValues->remove(ChronoField::CLOCK_HOUR_OF_AMPM());
            if ($this->resolverStyle == ResolverStyle::STRICT() || ($this->resolverStyle == ResolverStyle::SMART() && $ch != 0)) {
                ChronoField::CLOCK_HOUR_OF_AMPM()->checkValidValue($ch);
            }
            $this->updateCheckConflict(ChronoField::CLOCK_HOUR_OF_AMPM(), ChronoField::HOUR_OF_AMPM(), $ch == 12 ? 0 : $ch);
        }
        if ($this->fieldValues->containsKey(ChronoField::AMPM_OF_DAY()) && $this->fieldValues->containsKey(ChronoField::HOUR_OF_AMPM())) {
            $ap = $this->fieldValues->remove(ChronoField::AMPM_OF_DAY());
            $hap = $this->fieldValues->remove(ChronoField::HOUR_OF_AMPM());
            if ($this->resolverStyle == ResolverStyle::LENIENT()) {
                $this->updateCheckConflict(ChronoField::AMPM_OF_DAY(), ChronoField::HOUR_OF_DAY(), Math::addExact(Math::multiplyExact($ap, 12), $hap));
            } else {  // STRICT or SMART
                ChronoField::AMPM_OF_DAY()->checkValidValue($ap);
                ChronoField::HOUR_OF_AMPM()->checkValidValue($ap);
                $this->updateCheckConflict(ChronoField::AMPM_OF_DAY(), ChronoField::HOUR_OF_DAY(), $ap * 12 + $hap);
            }
        }
        if ($this->fieldValues->containsKey(ChronoField::NANO_OF_DAY())) {
            $nod = $this->fieldValues->remove(ChronoField::NANO_OF_DAY());
            if ($this->resolverStyle != ResolverStyle::LENIENT()) {
                ChronoField::NANO_OF_DAY()->checkValidValue($nod);
            }
            $this->updateCheckConflict(ChronoField::NANO_OF_DAY(), ChronoField::HOUR_OF_DAY(), $nod / 3600000000000);
            $this->updateCheckConflict(ChronoField::NANO_OF_DAY(), ChronoField::MINUTE_OF_HOUR(), ($nod / 60000000000) % 60);
            $this->updateCheckConflict(ChronoField::NANO_OF_DAY(), ChronoField::SECOND_OF_MINUTE(), ($nod / 1000000000) % 60);
            $this->updateCheckConflict(ChronoField::NANO_OF_DAY(), ChronoField::NANO_OF_SECOND(), $nod % 1000000000);
        }
        if ($this->fieldValues->containsKey(ChronoField::MICRO_OF_DAY())) {
            $cod = $this->fieldValues->remove(ChronoField::MICRO_OF_DAY());
            if ($this->resolverStyle != ResolverStyle::LENIENT()) {
                ChronoField::MICRO_OF_DAY()->checkValidValue($cod);
            }
            $this->updateCheckConflict(ChronoField::MICRO_OF_DAY(), ChronoField::SECOND_OF_DAY(), $cod / 1000000);
            $this->updateCheckConflict(ChronoField::MICRO_OF_DAY(), ChronoField::MICRO_OF_SECOND(), $cod % 1000000);
        }
        if ($this->fieldValues->containsKey(ChronoField::MILLI_OF_DAY())) {
            $lod = $this->fieldValues->remove(ChronoField::MILLI_OF_DAY());
            if ($this->resolverStyle != ResolverStyle::LENIENT()) {
                ChronoField::MILLI_OF_DAY()->checkValidValue($lod);
            }
            $this->updateCheckConflict(ChronoField::MILLI_OF_DAY(), ChronoField::SECOND_OF_DAY(), $lod / 1000);
            $this->updateCheckConflict(ChronoField::MILLI_OF_DAY(), ChronoField::MILLI_OF_SECOND(), $lod % 1000);
        }
        if ($this->fieldValues->containsKey(ChronoField::SECOND_OF_DAY())) {
            $sod = $this->fieldValues->remove(ChronoField::SECOND_OF_DAY());
            if ($this->resolverStyle != ResolverStyle::LENIENT()) {
                ChronoField::SECOND_OF_DAY()->checkValidValue($sod);
            }
            $this->updateCheckConflict(ChronoField::SECOND_OF_DAY(), ChronoField::HOUR_OF_DAY(), $sod / 3600);
            $this->updateCheckConflict(ChronoField::SECOND_OF_DAY(), ChronoField::MINUTE_OF_HOUR(), ($sod / 60) % 60);
            $this->updateCheckConflict(ChronoField::SECOND_OF_DAY(), ChronoField::SECOND_OF_MINUTE(), $sod % 60);
        }
        if ($this->fieldValues->containsKey(ChronoField::MINUTE_OF_DAY())) {
            $mod = $this->fieldValues->remove(ChronoField::MINUTE_OF_DAY());
            if ($this->resolverStyle != ResolverStyle::LENIENT()) {
                ChronoField::MINUTE_OF_DAY()->checkValidValue($mod);
            }
            $this->updateCheckConflict(ChronoField::MINUTE_OF_DAY(), ChronoField::HOUR_OF_DAY(), $mod / 60);
            $this->updateCheckConflict(ChronoField::MINUTE_OF_DAY(), ChronoField::MINUTE_OF_HOUR(), $mod % 60);
        }

// combine partial second fields strictly, leaving lenient expansion to later
        if ($this->fieldValues->containsKey(ChronoField::NANO_OF_SECOND())) {
            $nos = $this->fieldValues->get(ChronoField::NANO_OF_SECOND());
            if ($this->resolverStyle != ResolverStyle::LENIENT()) {
                ChronoField::NANO_OF_SECOND()->checkValidValue($nos);
            }
            if ($this->fieldValues->containsKey(ChronoField::MICRO_OF_SECOND())) {
                $cos = $this->fieldValues->remove(ChronoField::MICRO_OF_SECOND());
                if ($this->resolverStyle != ResolverStyle::LENIENT()) {
                    ChronoField::MICRO_OF_SECOND()->checkValidValue($cos);
                }
                $nos = $cos * 1000 + ($nos % 1000);
                $this->updateCheckConflict(ChronoField::MICRO_OF_SECOND(), ChronoField::NANO_OF_SECOND(), $nos);
            }
            if ($this->fieldValues->containsKey(ChronoField::MILLI_OF_SECOND())) {
                $los = $this->fieldValues->remove(ChronoField::MILLI_OF_SECOND());
                if ($this->resolverStyle != ResolverStyle::LENIENT()) {
                    ChronoField::MILLI_OF_SECOND()->checkValidValue($los);
                }
                $this->updateCheckConflict(ChronoField::MILLI_OF_SECOND(), ChronoField::NANO_OF_SECOND(), $los * 1000000 + ($nos % 1000000));
            }
        }

// convert to time if all four fields available (optimization)
        if ($this->fieldValues->containsKey(ChronoField::HOUR_OF_DAY()) && $this->fieldValues->containsKey(ChronoField::MINUTE_OF_HOUR()) &&
            $this->fieldValues->containsKey(ChronoField::SECOND_OF_MINUTE()) && $this->fieldValues->containsKey(ChronoField::NANO_OF_SECOND())
        ) {
            $hod = $this->fieldValues->remove(ChronoField::HOUR_OF_DAY());
            $moh = $this->fieldValues->remove(ChronoField::MINUTE_OF_HOUR());
            $som = $this->fieldValues->remove(ChronoField::SECOND_OF_MINUTE());
            $nos = $this->fieldValues->remove(ChronoField::NANO_OF_SECOND());
            $this->resolveTime($hod, $moh, $som, $nos);
        }
    }

    private
    function resolveTimeLenient()
    {
// leniently create a time from incomplete information
// done after everything else as it creates information from nothing
// which would break updateCheckConflict(field)

        if ($this->time == null) {
// ChronoField::NANO_OF_SECOND() merged with MILLI/MICRO above
            if ($this->fieldValues->containsKey(ChronoField::MILLI_OF_SECOND())) {
                $los = $this->fieldValues->remove(ChronoField::MILLI_OF_SECOND());
                if ($this->fieldValues->containsKey(ChronoField::MICRO_OF_SECOND())) {
// merge milli-of-second and micro-of-second for better error message
                    $cos = $los * 1000 + ($this->fieldValues->get(ChronoField::MICRO_OF_SECOND()) % 1000);
                    $this->updateCheckConflict(ChronoField::MILLI_OF_SECOND(), ChronoField::MICRO_OF_SECOND(), $cos);
                    $this->fieldValues->remove(ChronoField::MICRO_OF_SECOND());
                    $this->fieldValues->put(ChronoField::NANO_OF_SECOND(), $cos * 1000);
                } else {
// convert milli-of-second to nano-of-second
                    $this->fieldValues->put(ChronoField::NANO_OF_SECOND(), $los * 1000000);
                }
            } else
                if ($this->fieldValues->containsKey(ChronoField::MICRO_OF_SECOND())) {
// convert micro-of-second to nano-of-second
                    $cos = $this->fieldValues->remove(ChronoField::MICRO_OF_SECOND());
                    $this->fieldValues->put(ChronoField::NANO_OF_SECOND(), $cos * 1000);
                }

// merge hour/minute/second/nano leniently
            $hod = $this->fieldValues->get(ChronoField::HOUR_OF_DAY());
            if ($hod != null) {
                $moh = $this->fieldValues->get(ChronoField::MINUTE_OF_HOUR());
                $som = $this->fieldValues->get(ChronoField::SECOND_OF_MINUTE());
                $nos = $this->fieldValues->get(ChronoField::NANO_OF_SECOND());

// check for invalid combinations that cannot be defaulted
                if (($moh == null && ($som != null || $nos != null)) ||
                    ($moh != null && $som == null && $nos != null)
                ) {
                    return;
                }

// default as necessary and build time
                $mohVal = ($moh != null ? $moh : 0);
                $somVal = ($som != null ? $som : 0);
                $nosVal = ($nos != null ? $nos : 0);
                $this->resolveTime($hod, $mohVal, $somVal, $nosVal);
                $this->fieldValues->remove(ChronoField::HOUR_OF_DAY());
                $this->fieldValues->remove(ChronoField::MINUTE_OF_HOUR());
                $this->fieldValues->remove(ChronoField::SECOND_OF_MINUTE());
                $this->fieldValues->remove(ChronoField::NANO_OF_SECOND());
            }
        }

// validate remaining
        if ($this->resolverStyle != ResolverStyle::LENIENT() && $this->fieldValues->size() > 0) {
            foreach ($this->fieldValues->entrySet() as $entry) {
                $field = $entry->getKey();
                if ($field instanceof ChronoField && $field->isTimeBased()) {
                    $field->checkValidValue($entry->getValue());
                }
            }
        }
    }

    private
    function resolveTime($hod, $moh, $som, $nos)
    {
        if ($this->resolverStyle == ResolverStyle::LENIENT()) {
            $totalNanos = Math::multiplyExact($hod, 3600000000000);
            $totalNanos = Math::addExact($totalNanos, Math::multiplyExact($moh, 60000000000));
            $totalNanos = Math::addExact($totalNanos, Math::multiplyExact($som, 1000000000));
            $totalNanos = Math::addExact($totalNanos, $nos);
            $excessDays = (int)Math::floorDiv($totalNanos, 86400000000000);  // safe int cast
            $nod = Math::floorMod($totalNanos, 86400000000000);
            $this->updateCheckConflict(LocalTime::ofNanoOfDay($nod), Period::ofDays($excessDays));
        } else {  // STRICT or SMART
            $mohVal = ChronoField::MINUTE_OF_HOUR()->checkValidIntValue($moh);
            $nosVal = ChronoField::NANO_OF_SECOND()->checkValidIntValue($nos);
// handle 24:00 end of day
            if ($this->resolverStyle == ResolverStyle::SMART() && $hod == 24 && $mohVal == 0 && $som == 0 && $nosVal == 0) {
                $this->updateCheckConflict(LocalTime::MIDNIGHT(), Period::ofDays(1));
            } else {
                $hodVal = ChronoField::HOUR_OF_DAY()->checkValidIntValue($hod);
                $somVal = ChronoField::SECOND_OF_MINUTE()->checkValidIntValue($som);
                $this->updateCheckConflict(LocalTime::of($hodVal, $mohVal, $somVal, $nosVal), Period::ZERO());
            }
        }
    }

    private
    function resolvePeriod()
    {
// add whole days if we have both date and time
        if ($this->date != null && $this->time != null && $this->excessDays->isZero() == false) {
            $this->date = $this->date->plusAmount($this->excessDays);
            $this->excessDays = Period::ZERO();
        }
    }

    private
    function resolveFractional()
    {
// ensure fractional seconds available as ChronoField requires
// resolveTimeLenient() will have merged ChronoField::MICRO_OF_SECOND()/MILLI_OF_SECOND to NANO_OF_SECOND
        if ($this->time == null &&
            ($this->fieldValues->containsKey(ChronoField::INSTANT_SECONDS()) ||
                $this->fieldValues->containsKey(ChronoField::SECOND_OF_DAY()) ||
                $this->fieldValues->containsKey(ChronoField::SECOND_OF_MINUTE()))
        ) {
            if ($this->fieldValues->containsKey(ChronoField::NANO_OF_SECOND())) {
                $nos = $this->fieldValues->get(ChronoField::NANO_OF_SECOND());
                $this->fieldValues->put(ChronoField::MICRO_OF_SECOND(), $nos / 1000);
                $this->fieldValues->put(ChronoField::MILLI_OF_SECOND(), $nos / 1000000);
            } else {
                $this->fieldValues->put(ChronoField::NANO_OF_SECOND(), 0);
                $this->fieldValues->put(ChronoField::MICRO_OF_SECOND(), 0);
                $this->fieldValues->put(ChronoField::MILLI_OF_SECOND(), 0);
            }
        }
    }

    private
    function resolveInstant()
    {
// add instant seconds if we have date, time and zone
        if ($this->date != null && $this->time != null) {
            if ($this->zone != null) {
                $instant = $this->date->atTime($this->time)->atZone($this->zone)->getLong(ChronoField::INSTANT_SECONDS());
                $this->fieldValues->put(ChronoField::INSTANT_SECONDS(), $this->instant);
            } else {
                $offsetSecs = $this->fieldValues->get(ChronoField::OFFSET_SECONDS());
                if ($offsetSecs != null) {
                    $offset = ZoneOffset::ofTotalSeconds($offsetSecs->intValue());
                    $instant = $this->date->atTime($this->time)->atZone($offset)->getLong(ChronoField::INSTANT_SECONDS());
                    $this->fieldValues->put(ChronoField::INSTANT_SECONDS(), $instant);
                }
            }
        }
    }

    private
    function updateCheckConflict(LocalTime $timeToSet, Period $periodToSet)
    {
        if ($this->time != null) {
            if ($this->time->equals($timeToSet) == false) {
                throw new DateTimeException("Conflict found: Fields resolved to different times: " . $this->time . " " . $timeToSet);
            }
            if ($this->excessDays->isZero() == false && $periodToSet->isZero() == false && $this->excessDays->equals($periodToSet) == false) {
                throw new DateTimeException("Conflict found: Fields resolved to different excess periods: " . $this->excessDays . " " . $periodToSet);
            } else {
                $excessDays = $periodToSet;
            }
        } else {
            $this->time = $timeToSet;
            $this->excessDays = $periodToSet;
        }
    }

//-----------------------------------------------------------------------
    private
    function crossCheck()
    {
// only cross-check date, time and date-time
// avoid object creation if possible
        if ($this->date != null) {
            $this->crossCheck($this->date);
        }
        if ($this->time != null) {
            $this->crossCheck($this->time);
            if ($this->date != null && count($this->fieldValues) > 0) {
                $this->crossCheck($this->date->atTime($this->time));
            }
        }
    }

    private
    function crossCheck(TemporalAccessor $target)
    {
        for ($it = $this->fieldValues->entrySet()->iterator(); $it->hasNext();) {
            $entry = $it->next();
            $field = $entry->getKey();
            if ($target->isSupported($field)) {
                try {
                    $val1 = $target->getLong($field);
                } catch (\RuntimeException $ex) {
                    continue;
                }
                $val2 = $entry->getValue();
                if ($val1 != $val2) {
                    throw new DateTimeException("Conflict found: Field " . $field . " " . $val1 .
                        " differs from " . $field . " " . $$val2 . " derived from " . $target);
                }
                $it->remove();
            }
        }
    }

//-----------------------------------------------------------------------
    public function __toString()
    {
        $buf = $this->fieldValues . ',' . $this->chrono;
        if ($this->zone != null) {
            $buf .= ',' . $this->zone;
        }
        if ($this->date != null || $this->time != null) {
            $buf .= " resolved to ";
            if ($this->date != null) {
                $buf .= $this->date;
                if ($this->time != null) {
                    $buf .= 'T' . $this->time;
                }
            } else {
                $buf .= $this->time;
            }
        }
        return $buf;
    }

}
