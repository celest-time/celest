<?php declare(strict_types=1);
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
namespace Celest\Format;

use Celest\Chrono\ChronoLocalDate;
use Celest\Chrono\ChronoLocalDateTime;
use Celest\Chrono\Chronology;
use Celest\Chrono\ChronoZonedDateTime;
use Celest\DateTimeException;
use Celest\Helper\Math;
use Celest\Instant;
use Celest\LocalDate;
use Celest\LocalTime;
use Celest\Period;
use Celest\Temporal\AbstractTemporalAccessor;
use Celest\Temporal\ChronoField as CF;
use Celest\Temporal\FieldValues;
use Celest\Temporal\TemporalAccessor;
use Celest\Temporal\TemporalField;
use Celest\Temporal\TemporalQueries;
use Celest\Temporal\TemporalQuery;
use Celest\Temporal\UnsupportedTemporalTypeException;
use Celest\ZoneId;
use Celest\ZoneOffset;

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
final class Parsed extends AbstractTemporalAccessor
{
// some fields are accessed using package scope from DateTimeParseContext

    /**
     * The parsed fields.
     * @var FieldValues
     */
    public $fieldValues;
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
        $this->fieldValues = new FieldValues();
    }

    /**
     * Creates a copy.
     * @return Parsed
     */
    public function copy() : Parsed
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
    public function isSupported(TemporalField $field) : bool
    {
        if ($this->fieldValues->has($field) ||
            ($this->date !== null && $this->date->isSupported($field)) ||
            ($this->time !== null && $this->time->isSupported($field))
        ) {
            return true;
        }

        return $field !== null && !($field instanceof CF) && $field->isSupportedBy($this);
    }

    /**
     * @param TemporalField $field
     * @return int
     * @throws UnsupportedTemporalTypeException
     */
    public function getLong(TemporalField $field) : int
    {
        $value = $this->fieldValues->get($field);
        if ($value !== null) {
            return $value;
        }

        if ($this->date !== null && $this->date->isSupported($field)) {
            return $this->date->getLong($field);
        }
        if ($this->time !== null && $this->time->isSupported($field)) {
            return $this->time->getLong($field);
        }
        if ($field instanceof CF) {
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
                return ($this->date !== null ? LocalDate::from($this->date) : null);
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
     * @param ResolverStyle $resolverStyle the resolver style, not null
     * @param TemporalField[]|null $resolverFields the fields to use for resolving, null for all fields
     * @return $this, for method chaining
     * @throws DateTimeException if resolving one field results in a value for
     *  another field that is in conflict
     */
    public function resolve(ResolverStyle $resolverStyle, ?array $resolverFields) : Parsed
    {
        if ($resolverFields !== null) {
            $this->fieldValues->filter($resolverFields);
        }

        $this->resolverStyle = $resolverStyle;
        $this->resolveFields();
        $this->resolveTimeLenient();
        $this->crossCheck();
        $this->resolvePeriod();
        $this->resolveFractional();
        $this->resolveInstant();
        return $this;
    }

//-----------------------------------------------------------------------
    private function resolveFields() : void
    {
        // resolve CF
        $this->resolveInstantFields();
        $this->resolveDateFields();
        $this->resolveTimeFields();

        // if any other fields, handle them
        // any lenient date resolution should return epoch-day
        if (!$this->fieldValues->isEmpty()) {
            $changedCount = 0;
            outer:
            while ($changedCount < 50) {
                foreach ($this->fieldValues as $targetField => $value) {
                    /** @var CF $targetField */
                    $resolvedObject = $targetField->resolve($this->fieldValues, $this, $this->resolverStyle);
                    if ($resolvedObject !== null) {
                        if ($resolvedObject instanceof ChronoZonedDateTime) {
                            $czdt = $resolvedObject;
                            if ($this->zone === null) {
                                $this->zone = $czdt->getZone();
                            } else
                                if ($this->zone->equals($czdt->getZone()) === false) {
                                    throw new DateTimeException("ChronoZonedDateTime must use the effective parsed zone: " . $this->zone);
                                }
                            $resolvedObject = $czdt->toLocalDateTime();
                        }
                        if ($resolvedObject instanceof ChronoLocalDateTime) {
                            $cldt = $resolvedObject;
                            $this->updateCheckConflict($cldt->toLocalTime(), Period::ZERO());
                            $this->updateCheckConflict1($cldt->toLocalDate());
                            $changedCount++;
                            continue 2;  // have to restart to avoid concurrent modification
                        }
                        if ($resolvedObject instanceof ChronoLocalDate) {
                            $this->updateCheckConflict1($resolvedObject);
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
                    } else if ($this->fieldValues->has($targetField) === false) {
                        $changedCount++;
                        continue 2;  // have to restart to avoid concurrent modification
                    }
                }
                break;
            }
            if ($changedCount === 50) {  // catch infinite loops
                throw new DateTimeException("One of the parsed fields has an incorrectly implemented resolve method");
            }
            // if something changed then have to redo CF resolve
            if ($changedCount > 0) {
                $this->resolveInstantFields();
                $this->resolveDateFields();
                $this->resolveTimeFields();
            }
        }
    }

    private function updateCheckConflict3(TemporalField $targetField, TemporalField $changeField, int $changeValue) : void
    {
        $old = $this->fieldValues->put($changeField, $changeValue);

        if ($old !== null && $old !== $changeValue) {
            throw new DateTimeException("Conflict found: " . $changeField . " " . $old .
                " differs from " . $changeField . " " . $changeValue .
                " while resolving  " . $targetField);
        }
    }

//-----------------------------------------------------------------------
    private function resolveInstantFields() : void
    {
        // resolve parsed instant seconds to date and time if zone available
        if ($this->fieldValues->has(CF::INSTANT_SECONDS())) {
            if ($this->zone !== null) {
                $this->resolveInstantFields0($this->zone);
            } else {
                $offsetSecs = $this->fieldValues->get(CF::OFFSET_SECONDS());
                if ($offsetSecs !== null) {
                    $offset = ZoneOffset::ofTotalSeconds($offsetSecs);
                    $this->resolveInstantFields0($offset);
                }
            }
        }
    }

    private function resolveInstantFields0(ZoneId $selectedZone) : void
    {
        $instant = Instant::ofEpochSecond($this->fieldValues->remove(CF::INSTANT_SECONDS()));
        $zdt = $this->chrono->zonedDateTime($instant, $selectedZone);
        $this->updateCheckConflict1($zdt->toLocalDate());
        $this->updateCheckConflict3(CF::INSTANT_SECONDS(), CF::SECOND_OF_DAY(), $zdt->toLocalTime()->toSecondOfDay());
    }

//-----------------------------------------------------------------------
    private function resolveDateFields() : void
    {
        $this->updateCheckConflict1($this->chrono->resolveDate($this->fieldValues, $this->resolverStyle));
    }

    /**
     * @param ChronoLocalDate|null $cld
     * @throws DateTimeException
     */
    private function updateCheckConflict1(?ChronoLocalDate $cld) : void
    {
        if ($this->date !== null) {
            if ($cld !== null && $this->date->equals($cld) === false) {
                throw new DateTimeException("Conflict found: Fields resolved to two different dates: " . $this->date . " " . $cld);
            }
        } else if ($cld !== null) {
            if ($this->chrono->equals($cld->getChronology()) === false) {
                throw new DateTimeException("ChronoLocalDate must use the effective parsed chronology: " . $this->chrono);
            }
            $this->date = $cld;
        }
    }

//-----------------------------------------------------------------------
    private function resolveTimeFields() : void
    {
// simplify fields
        if ($this->fieldValues->has(CF::CLOCK_HOUR_OF_DAY())) {
// lenient allows anything, smart allows 0-24, strict allows 1-24
            $ch = $this->fieldValues->remove(CF::CLOCK_HOUR_OF_DAY());
            if ($this->resolverStyle == ResolverStyle::STRICT() || ($this->resolverStyle == ResolverStyle::SMART() && $ch !== 0)) {
                CF::CLOCK_HOUR_OF_DAY()->checkValidValue($ch);
            }

            $this->updateCheckConflict3(CF::CLOCK_HOUR_OF_DAY(), CF::HOUR_OF_DAY(), $ch === 24 ? 0 : $ch);
        }
        if ($this->fieldValues->has(CF::CLOCK_HOUR_OF_AMPM())) {
// lenient allows anything, smart allows 0-12, strict allows 1-12
            $ch = $this->fieldValues->remove(CF::CLOCK_HOUR_OF_AMPM());
            if ($this->resolverStyle == ResolverStyle::STRICT() || ($this->resolverStyle == ResolverStyle::SMART() && $ch !== 0)) {
                CF::CLOCK_HOUR_OF_AMPM()->checkValidValue($ch);
            }
            $this->updateCheckConflict3(CF::CLOCK_HOUR_OF_AMPM(), CF::HOUR_OF_AMPM(), $ch === 12 ? 0 : $ch);
        }
        if ($this->fieldValues->has(CF::AMPM_OF_DAY()) && $this->fieldValues->has(CF::HOUR_OF_AMPM())) {
            $ap = $this->fieldValues->remove(CF::AMPM_OF_DAY());
            $hap = $this->fieldValues->remove(CF::HOUR_OF_AMPM());
            if ($this->resolverStyle == ResolverStyle::LENIENT()) {
                $this->updateCheckConflict3(CF::AMPM_OF_DAY(), CF::HOUR_OF_DAY(), Math::addExact(Math::multiplyExact($ap, 12), $hap));
            } else {  // STRICT or SMART
                CF::AMPM_OF_DAY()->checkValidValue($ap);
                CF::HOUR_OF_AMPM()->checkValidValue($ap);
                $this->updateCheckConflict3(CF::AMPM_OF_DAY(), CF::HOUR_OF_DAY(), $ap * 12 + $hap);
            }
        }
        if ($this->fieldValues->has(CF::NANO_OF_DAY())) {
            $nod = $this->fieldValues->remove(CF::NANO_OF_DAY());
            if ($this->resolverStyle != ResolverStyle::LENIENT()) {
                CF::NANO_OF_DAY()->checkValidValue($nod);
            }
            $this->updateCheckConflict3(CF::NANO_OF_DAY(), CF::HOUR_OF_DAY(), \intdiv($nod, 3600000000000));
            $this->updateCheckConflict3(CF::NANO_OF_DAY(), CF::MINUTE_OF_HOUR(), \intdiv($nod, 60000000000) % 60);
            $this->updateCheckConflict3(CF::NANO_OF_DAY(), CF::SECOND_OF_MINUTE(), \intdiv($nod, 1000000000) % 60);
            $this->updateCheckConflict3(CF::NANO_OF_DAY(), CF::NANO_OF_SECOND(), $nod % 1000000000);
        }
        if ($this->fieldValues->has(CF::MICRO_OF_DAY())) {
            $cod = $this->fieldValues->remove(CF::MICRO_OF_DAY());
            if ($this->resolverStyle != ResolverStyle::LENIENT()) {
                CF::MICRO_OF_DAY()->checkValidValue($cod);
            }
            $this->updateCheckConflict3(CF::MICRO_OF_DAY(), CF::SECOND_OF_DAY(), \intdiv($cod, 1000000));
            $this->updateCheckConflict3(CF::MICRO_OF_DAY(), CF::MICRO_OF_SECOND(), $cod % 1000000);
        }
        if ($this->fieldValues->has(CF::MILLI_OF_DAY())) {
            $lod = $this->fieldValues->remove(CF::MILLI_OF_DAY());
            if ($this->resolverStyle != ResolverStyle::LENIENT()) {
                CF::MILLI_OF_DAY()->checkValidValue($lod);
            }
            $this->updateCheckConflict3(CF::MILLI_OF_DAY(), CF::SECOND_OF_DAY(), \intdiv($lod, 1000));
            $this->updateCheckConflict3(CF::MILLI_OF_DAY(), CF::MILLI_OF_SECOND(), $lod % 1000);
        }
        if ($this->fieldValues->has(CF::SECOND_OF_DAY())) {
            $sod = $this->fieldValues->remove(CF::SECOND_OF_DAY());
            if ($this->resolverStyle != ResolverStyle::LENIENT()) {
                CF::SECOND_OF_DAY()->checkValidValue($sod);
            }
            $this->updateCheckConflict3(CF::SECOND_OF_DAY(), CF::HOUR_OF_DAY(), \intdiv($sod, 3600));
            $this->updateCheckConflict3(CF::SECOND_OF_DAY(), CF::MINUTE_OF_HOUR(), \intdiv($sod, 60) % 60);
            $this->updateCheckConflict3(CF::SECOND_OF_DAY(), CF::SECOND_OF_MINUTE(), $sod % 60);
        }
        if ($this->fieldValues->has(CF::MINUTE_OF_DAY())) {
            $mod = $this->fieldValues->remove(CF::MINUTE_OF_DAY());
            if ($this->resolverStyle != ResolverStyle::LENIENT()) {
                CF::MINUTE_OF_DAY()->checkValidValue($mod);
            }
            $this->updateCheckConflict3(CF::MINUTE_OF_DAY(), CF::HOUR_OF_DAY(), \intdiv($mod, 60));
            $this->updateCheckConflict3(CF::MINUTE_OF_DAY(), CF::MINUTE_OF_HOUR(), $mod % 60);
        }

// combine partial second fields strictly, leaving lenient expansion to later
        if ($this->fieldValues->has(CF::NANO_OF_SECOND())) {
            $nos = $this->fieldValues->get(CF::NANO_OF_SECOND());
            if ($this->resolverStyle != ResolverStyle::LENIENT()) {
                CF::NANO_OF_SECOND()->checkValidValue($nos);
            }
            if ($this->fieldValues->has(CF::MICRO_OF_SECOND())) {
                $cos = $this->fieldValues->remove(CF::MICRO_OF_SECOND());
                if ($this->resolverStyle != ResolverStyle::LENIENT()) {
                    CF::MICRO_OF_SECOND()->checkValidValue($cos);
                }
                $nos = $cos * 1000 + ($nos % 1000);
                $this->updateCheckConflict3(CF::MICRO_OF_SECOND(), CF::NANO_OF_SECOND(), $nos);
            }
            if ($this->fieldValues->has(CF::MILLI_OF_SECOND())) {
                $los = $this->fieldValues->remove(CF::MILLI_OF_SECOND());
                if ($this->resolverStyle != ResolverStyle::LENIENT()) {
                    CF::MILLI_OF_SECOND()->checkValidValue($los);
                }
                $this->updateCheckConflict3(CF::MILLI_OF_SECOND(), CF::NANO_OF_SECOND(), $los * 1000000 + ($nos % 1000000));
            }
        }

// convert to time if all four fields available (optimization)
        if ($this->fieldValues->has(CF::HOUR_OF_DAY()) && $this->fieldValues->has(CF::MINUTE_OF_HOUR()) &&
            $this->fieldValues->has(CF::SECOND_OF_MINUTE()) && $this->fieldValues->has(CF::NANO_OF_SECOND())
        ) {
            $hod = $this->fieldValues->remove(CF::HOUR_OF_DAY());
            $moh = $this->fieldValues->remove(CF::MINUTE_OF_HOUR());
            $som = $this->fieldValues->remove(CF::SECOND_OF_MINUTE());
            $nos = $this->fieldValues->remove(CF::NANO_OF_SECOND());

            $this->resolveTime($hod, $moh, $som, $nos);
        }
    }

    private function resolveTimeLenient() : void
    {
// leniently create a time from incomplete information
// done after everything else as it creates information from nothing
// which would break updateCheckConflict(field)

        if ($this->time === null) {
// CF::NANO_OF_SECOND() merged with MILLI/MICRO above
            if ($this->fieldValues->has(CF::MILLI_OF_SECOND())) {
                $los = $this->fieldValues->remove(CF::MILLI_OF_SECOND());
                if ($this->fieldValues->has(CF::MICRO_OF_SECOND())) {
// merge milli-of-second and micro-of-second for better error message
                    $cos = $los * 1000 + ($this->fieldValues->get(CF::MICRO_OF_SECOND()) % 1000);
                    $this->updateCheckConflict3(CF::MILLI_OF_SECOND(), CF::MICRO_OF_SECOND(), $cos);
                    $this->fieldValues->remove(CF::MICRO_OF_SECOND());
                    $this->fieldValues->put(CF::NANO_OF_SECOND(), $cos * 1000);
                } else {
// convert milli-of-second to nano-of-second
                    $this->fieldValues->put(CF::NANO_OF_SECOND(), $los * 1000000);
                }
            } else
                if ($this->fieldValues->has(CF::MICRO_OF_SECOND())) {
// convert micro-of-second to nano-of-second
                    $cos = $this->fieldValues->remove(CF::MICRO_OF_SECOND());
                    $this->fieldValues->put(CF::NANO_OF_SECOND(), $cos * 1000);
                }

// merge hour/minute/second/nano leniently
            $hod = $this->fieldValues->get(CF::HOUR_OF_DAY());
            if ($hod !== null) {
                $moh = $this->fieldValues->get(CF::MINUTE_OF_HOUR());
                $som = $this->fieldValues->get(CF::SECOND_OF_MINUTE());
                $nos = $this->fieldValues->get(CF::NANO_OF_SECOND());

// check for invalid combinations that cannot be defaulted
                if (($moh === null && ($som !== null || $nos !== null)) ||
                    ($moh !== null && $som === null && $nos !== null)
                ) {
                    return;
                }

// default as necessary and build time
                $mohVal = ($moh !== null ? $moh : 0);
                $somVal = ($som !== null ? $som : 0);
                $nosVal = ($nos !== null ? $nos : 0);
                $this->resolveTime($hod, $mohVal, $somVal, $nosVal);
                $this->fieldValues->remove(CF::HOUR_OF_DAY());
                $this->fieldValues->remove(CF::MINUTE_OF_HOUR());
                $this->fieldValues->remove(CF::SECOND_OF_MINUTE());
                $this->fieldValues->remove(CF::NANO_OF_SECOND());
            }
        }

// validate remaining
        if ($this->resolverStyle != ResolverStyle::LENIENT() && !empty($this->fieldValues)) {
            foreach ($this->fieldValues as $field => $value) {
                if ($field instanceof CF && $field->isTimeBased()) {
                    $field->checkValidValue($value);
                }
            }
        }
    }

    private function resolveTime(int $hod, int $moh, int $som, int $nos) : void
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
            $mohVal = CF::MINUTE_OF_HOUR()->checkValidIntValue($moh);
            $nosVal = CF::NANO_OF_SECOND()->checkValidIntValue($nos);
// handle 24:00 end of day
            if ($this->resolverStyle == ResolverStyle::SMART() && $hod === 24 && $mohVal === 0 && $som === 0 && $nosVal === 0) {
                $this->updateCheckConflict(LocalTime::MIDNIGHT(), Period::ofDays(1));
            } else {
                $hodVal = CF::HOUR_OF_DAY()->checkValidIntValue($hod);
                $somVal = CF::SECOND_OF_MINUTE()->checkValidIntValue($som);
                $this->updateCheckConflict(LocalTime::of($hodVal, $mohVal, $somVal, $nosVal), Period::ZERO());
            }
        }
    }

    private function resolvePeriod() : void
    {
// add whole days if we have both date and time
        if ($this->date !== null && $this->time !== null && $this->excessDays->isZero() === false) {
            $this->date = $this->date->plusAmount($this->excessDays);
            $this->excessDays = Period::ZERO();
        }
    }

    private function resolveFractional() : void
    {
// ensure fractional seconds available as CF requires
// resolveTimeLenient() will have merged CF::MICRO_OF_SECOND()/MILLI_OF_SECOND to NANO_OF_SECOND
        if ($this->time === null &&
            ($this->fieldValues->has(CF::INSTANT_SECONDS()) ||
                $this->fieldValues->has(CF::SECOND_OF_DAY()) ||
                $this->fieldValues->has(CF::SECOND_OF_MINUTE()))
        ) {
            if ($this->fieldValues->has(CF::NANO_OF_SECOND())) {
                $nos = $this->fieldValues->get(CF::NANO_OF_SECOND());
                $this->fieldValues->put(CF::MICRO_OF_SECOND(), \intdiv($nos, 1000));
                $this->fieldValues->put(CF::MILLI_OF_SECOND(), \intdiv($nos, 1000000));
            } else {
                $this->fieldValues->put(CF::NANO_OF_SECOND(), 0);
                $this->fieldValues->put(CF::MICRO_OF_SECOND(), 0);
                $this->fieldValues->put(CF::MILLI_OF_SECOND(), 0);
            }
        }
    }

    private function resolveInstant() : void
    {
// add instant seconds if we have date, time and zone
        if ($this->date !== null && $this->time !== null) {
            if ($this->zone !== null) {
                $instant = $this->date->atTime($this->time)->atZone($this->zone)->getLong(CF::INSTANT_SECONDS());
                $this->fieldValues->put(CF::INSTANT_SECONDS(), $instant);
            } else {
                $offsetSecs = $this->fieldValues->get(CF::OFFSET_SECONDS());
                if ($offsetSecs !== null) {
                    $offset = ZoneOffset::ofTotalSeconds($offsetSecs);
                    $instant = $this->date->atTime($this->time)->atZone($offset)->getLong(CF::INSTANT_SECONDS());
                    $this->fieldValues->put(CF::INSTANT_SECONDS(), $instant);
                }
            }
        }
    }

    private function updateCheckConflict(LocalTime $timeToSet, Period $periodToSet) : void
    {
        if ($this->time !== null) {
            if ($this->time->equals($timeToSet) === false) {
                throw new DateTimeException("Conflict found: Fields resolved to different times: " . $this->time . " " . $timeToSet);
            }
            if ($this->excessDays->isZero() === false && $periodToSet->isZero() === false && $this->excessDays->equals($periodToSet) === false) {
                throw new DateTimeException("Conflict found: Fields resolved to different excess periods: " . $this->excessDays . " " . $periodToSet);
            } else {
                $this->excessDays = $periodToSet;
            }
        } else {
            $this->time = $timeToSet;
            $this->excessDays = $periodToSet;
        }
    }

//-----------------------------------------------------------------------
    private function crossCheck() : void
    {
// only cross-check date, time and date-time
// avoid object creation if possible
        if ($this->date !== null) {
            $this->crossCheck1($this->date);
        }
        if ($this->time !== null) {
            $this->crossCheck1($this->time);
            if ($this->date !== null && !$this->fieldValues->isEmpty()) {
                $this->crossCheck1($this->date->atTime($this->time));
            }
        }
    }

    private function crossCheck1(TemporalAccessor $target) : void
    {
        foreach ($this->fieldValues as $field => $entry) {
            /** @var CF $field */
            if ($target->isSupported($field)) {
                try {
                    $val1 = $target->getLong($field);
                } catch (\RuntimeException $ex) {
                    continue;
                }
                $val2 = $entry;
                if ($val1 !== $val2) {
                    throw new DateTimeException("Conflict found: Field " . $field . " " . $val1 .
                        " differs from " . $field . " " . $val2 . " derived from " . $target);
                }
                $this->fieldValues->remove($field);
            }
        }
    }

//-----------------------------------------------------------------------
    public function __toString() : string
    {
        $buf = $this->fieldValues . $this->chrono;
        if ($this->zone !== null) {
            $buf .= ',' . $this->zone;
        }
        if ($this->date !== null || $this->time !== null) {
            $buf .= " resolved to ";
            if ($this->date !== null) {
                $buf .= $this->date;
                if ($this->time !== null) {
                    $buf .= 'T' . $this->time;
                }
            } else {
                $buf .= $this->time;
            }
        }
        return $buf;
    }
}
