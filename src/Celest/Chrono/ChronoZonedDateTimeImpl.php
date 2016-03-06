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
 * Copyright (c) 2007-2012, Stephen Colebourne & Michael Nascimento Santos
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

use Celest\Instant;
use Celest\LocalDateTime;
use Celest\Temporal\ChronoField;
use Celest\Temporal\ChronoUnit;
use Celest\Temporal\Temporal;
use Celest\Temporal\TemporalField;
use Celest\Temporal\TemporalUnit;
use Celest\ZoneId;
use Celest\ZoneOffset;

/**
 * A date-time with a time-zone in the calendar neutral API.
 * <p>
 * {@code ZoneChronoDateTime} is an immutable representation of a date-time with a time-zone.
 * This class stores all date and time fields, to a precision of nanoseconds,
 * as well as a time-zone and zone offset.
 * <p>
 * The purpose of storing the time-zone is to distinguish the ambiguous case where
 * the local time-line overlaps, typically as a result of the end of daylight time.
 * Information about the local-time can be obtained using methods on the time-zone.
 *
 * @implSpec
 * This class is immutable and thread-safe.
 *
 * @serial Document the delegation of this class in the serialized-form specification.
 * @param <D> the concrete type for the date of this date-time
 * @since 1.8
 */
final class ChronoZonedDateTimeImpl extends AbstractChronoZonedDateTime
{
    /**
     * The local date-time.
     * @var ChronoLocalDateTimeImpl
     */
    private $dateTime;
    /**
     * The zone offset.
     * @var ZoneOffset
     */
    private $offset;
    /**
     * The zone ID.
     * @var ZoneId
     */
    private $zone;

    //-----------------------------------------------------------------------
    /**
     * Obtains an instance from a local date-time using the preferred offset if possible.
     *
     * @param ChronoLocalDateTimeImpl $localDateTime the local date-time, not null
     * @param ZoneId $zone the zone identifier, not null
     * @param ZoneOffset $preferredOffset the zone offset, null if no preference
     * @return ChronoZonedDateTimeImpl the zoned date-time, not null
     */
    static function ofBest(ChronoLocalDateTimeImpl $localDateTime, ZoneId $zone, $preferredOffset)
    {
        if ($zone instanceof ZoneOffset) {
            return new ChronoZonedDateTimeImpl($localDateTime, $zone, $zone);
        }
        $rules = $zone->getRules();
        $isoLDT = LocalDateTime::from($localDateTime);
        $validOffsets = $rules->getValidOffsets($isoLDT);
        $offset = null;
        if (count($validOffsets) === 1) {
            $offset = $validOffsets[0];
        } else if (count($validOffsets) === 0) {
            $trans = $rules->getTransition($isoLDT);
            $localDateTime = $localDateTime->plusSeconds($trans->getDuration()->getSeconds());
            $offset = $trans->getOffsetAfter();
        } else {
            if ($preferredOffset !== null && $validOffsets->contains($preferredOffset)) {
                $offset = $preferredOffset;
            } else {
                $offset = $validOffsets[0];
            }
        }
        //Objects.requireNonNull(offset, "offset");  // protect against bad ZoneRules TODO
        return new ChronoZonedDateTimeImpl($localDateTime, $offset, $zone);
    }

    /**
     * Obtains an instance from an instant using the specified time-zone.
     *
     * @param Chronology $chrono the chronology, not null
     * @param Instant $instant the instant, not null
     * @param ZoneId $zone the zone identifier, not null
     * @return ChronoZonedDateTimeImpl the zoned date-time, not null
     */
    static function ofInstant(Chronology $chrono, Instant $instant, ZoneId $zone)
    {
        $rules = $zone->getRules();
        $offset = $rules->getOffset($instant);
// TODO Objects.requireNonNull(offset, "offset");  // protect against bad ZoneRules
        $ldt = LocalDateTime::ofEpochSecond($instant->getEpochSecond(), $instant->getNano(), $offset);
        $cldt = $chrono->localDateTime($ldt);
        return new ChronoZonedDateTimeImpl($cldt, $offset, $zone);
    }

    /**
     * Obtains an instance from an {@code Instant}.
     *
     * @param Instant $instant the instant to create the date-time from, not null
     * @param ZoneId $zone the time-zone to use, validated not null
     * @return ChronoZonedDateTimeImpl the zoned date-time, validated not null
     */
    private function create(Instant $instant, ZoneId $zone)
    {
        return $this->ofInstant($this->getChronology(), $instant, $zone);
    }

    /**
     * Casts the {@code Temporal} to {@code ChronoZonedDateTimeImpl} ensuring it bas the specified chronology.
     *
     * @param Chronology $chrono the chronology to check for, not null
     * @param Temporal $temporal a date-time to cast, not null
     * @return ChronoZonedDateTimeImpl the date-time checked and cast to {@code ChronoZonedDateTimeImpl}, not null
     * @throws ClassCastException if the date-time cannot be cast to ChronoZonedDateTimeImpl
     *  or the chronology is not equal this Chronology
     */
    static function ensureValid(Chronology $chrono, Temporal $temporal)
    {
        $other = $temporal; // TODO cast
        if ($chrono->equals($other->getChronology()) === false) {
            throw new ClassCastException("Chronology mismatch, required: " . $chrono->getId()
                . ", actual: " . $other->getChronology()->getId());
        }

        return $other;
    }

//-----------------------------------------------------------------------
    /**
     * Constructor.
     *
     * @param ChronoLocalDateTimeImpl $dateTime the date-time, not null
     * @param ZoneOffset $offset the zone offset, not null
     * @param ZoneId $zone the zone ID, not null
     */
    private function __construct(ChronoLocalDateTimeImpl $dateTime, ZoneOffset $offset, ZoneId $zone)
    {
        $this->dateTime = $dateTime;
        $this->offset = $offset;
        $this->zone = $zone;
    }

//-----------------------------------------------------------------------
    public function getOffset()
    {
        return $this->offset;
    }

    public function withEarlierOffsetAtOverlap()
    {
        $trans = $this->getZone()->getRules()->getTransition(LocalDateTime::from($this));
        if ($trans !== null && $trans->isOverlap()) {
            $earlierOffset = $trans->getOffsetBefore();
            if ($earlierOffset->equals($this->offset) === false) {
                return new ChronoZonedDateTimeImpl($this->dateTime, $earlierOffset, $this->zone);
            }
        }
        return $this;
    }


    public function withLaterOffsetAtOverlap()
    {
        $trans = $this->getZone()->getRules()->getTransition(LocalDateTime::from($this));
        if ($trans !== null) {
            $offset = $trans->getOffsetAfter();
            if ($offset->equals($this->getOffset()) === false) {
                return new ChronoZonedDateTimeImpl($this->dateTime, $offset, $this->zone);
            }
        }
        return $this;
    }

    //-----------------------------------------------------------------------

    public function toLocalDateTime()
    {
        return $this->dateTime;
    }


    public function getZone()
    {
        return $this->zone;
    }


    public function withZoneSameLocal(ZoneId $zone)
    {
        return $this->ofBest($this->dateTime, $zone, $this->offset);
    }


    public function withZoneSameInstant(ZoneId $zone)
    {
        return $this->zone->equals($zone) ? $this : $this->create($this->dateTime->toInstant($this->offset), $zone);
    }

    //-----------------------------------------------------------------------

    public function isSupported(TemporalField $field)
    {
        return $field instanceof ChronoField || ($field !== null && $field->isSupportedBy($this));
    }

//-----------------------------------------------------------------------

    public function with(TemporalField $field, $newValue)
    {
        if ($field instanceof ChronoField) {
            $f = $field;
            switch ($f) {
                case ChronoField::INSTANT_SECONDS():
                    return $this->plus($newValue - $this->toEpochSecond(), ChronoUnit::SECONDS());
                case ChronoField::OFFSET_SECONDS(): {
                    $offset = ZoneOffset::ofTotalSeconds($f->checkValidIntValue($newValue));
                    return $this->create($this->dateTime->toInstant($offset), $this->zone);
                }
            }
            return $this->ofBest($this->dateTime->with($field, $newValue), $this->zone, $this->offset);
        }
        return ChronoZonedDateTimeImpl::ensureValid($this->getChronology(), $field->adjustInto($this, $newValue));
    }

    //-----------------------------------------------------------------------

    public function plus($amountToAdd, TemporalUnit $unit)
    {
        if ($unit instanceof ChronoUnit) {
            return $this->adjust($this->dateTime->plus($amountToAdd, $unit));
        }
        return ChronoZonedDateTimeImpl::ensureValid($this->getChronology(), $unit->addTo($this, $amountToAdd));   /// TODO: Generics replacement Risk!
    }

    //-----------------------------------------------------------------------

    public function until(Temporal $endExclusive, TemporalUnit $unit)
    {
        $end = $this->getChronology()->zonedDateTime($endExclusive);
        if ($unit instanceof ChronoUnit) {
            $end = $end->withZoneSameInstant($this->offset);
            return $this->dateTime->until($end->toLocalDateTime(), $unit);
        }
        return $unit->between($this, $end);
    }

    //-------------------------------------------------------------------------

    public function equals($obj)
    {
        if ($this === $obj) {
            return true;
        }
        if ($obj instanceof ChronoZonedDateTime) {
            return $this->compareTo($obj) === 0;
        }
        return false;
    }


    public function __toString()
    {
        $str = $this->toLocalDateTime()->__toString() . $this->getOffset()->__toString();
        if ($this->getOffset() != $this->getZone()) {
            $str .= '[' . $this->getZone()->__toString() . ']';
        }
        return $str;
    }


}
