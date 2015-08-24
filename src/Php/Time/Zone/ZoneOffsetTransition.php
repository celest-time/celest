<?php

/*
 * Copyright (c) 2012, 2015, Oracle and/or its affiliates. All rights reserved.
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
 * Copyright (c) 2009-2012, Stephen Colebourne & Michael Nascimento Santos
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

use Php\Time\Duration;
use Php\Time\LocalDateTime;
use Php\Time\ZoneOffset;
use Php\Time\IllegalArgumentException;
use Php\Time\Instant;

/**
 * A transition between two offsets caused by a discontinuity in the local time-line.
 * <p>
 * A transition between two offsets is normally the result of a daylight savings cutover.
 * The discontinuity is normally a gap in spring and an overlap in autumn.
 * {@code ZoneOffsetTransition} models the transition between the two offsets.
 * <p>
 * Gaps occur where there are local date-times that simply do not exist.
 * An example would be when the offset changes from {@code +03:00} to {@code +04:00}.
 * This might be described as 'the clocks will move forward one hour tonight at 1am'.
 * <p>
 * Overlaps occur where there are local date-times that exist twice.
 * An example would be when the offset changes from {@code +04:00} to {@code +03:00}.
 * This might be described as 'the clocks will move back one hour tonight at 2am'.
 *
 * @implSpec
 * This class is immutable and thread-safe.
 *
 * @since 1.8
 */
final class ZoneOffsetTransition
{
    /**
     * The local transition date-time at the transition.
     * @var LocalDateTime
     */
    private $transition;
    /**
     * The offset before transition.
     * @var ZoneOffset
     */
    private $offsetBefore;
    /**
     * The offset after transition.
     * @var ZoneOffset
     */
    private $offsetAfter;

    //-----------------------------------------------------------------------
    /**
     * Obtains an instance defining a transition between two offsets.
     * <p>
     * Applications should normally obtain an instance from {@link ZoneRules}.
     * This factory is only intended for use when creating {@link ZoneRules}.
     *
     * @param $transition LocalDateTime the transition date-time at the transition, which never
     *  actually occurs, expressed local to the before offset, not null
     * @param $offsetBefore ZoneOffset the offset before the transition, not null
     * @param $offsetAfter ZoneOffset the offset at and after the transition, not null
     * @return ZoneOffsetTransition the transition, not null
     * @throws IllegalArgumentException if {@code offsetBefore} and {@code offsetAfter}
     *         are equal, or {@code transition.getNano()} returns non-zero value
     */
    public static function of(LocalDateTime $transition, ZoneOffset $offsetBefore, ZoneOffset $offsetAfter)
    {
        if ($offsetBefore->equals($offsetAfter)) {
            throw new IllegalArgumentException("Offsets must not be equal");
        }
        if ($transition->getNano() != 0) {
            throw new IllegalArgumentException("Nano-of-second must be zero");
        }
        return new ZoneOffsetTransition($transition, $offsetBefore, $offsetAfter);
    }

    /**
     * Creates an instance from epoch-second and offsets.
     *
     * @param $epochSecond int the transition epoch-second
     * @param $offsetBefore ZoneOffset the offset before the transition, not null
     * @param $offsetAfter ZoneOffset the offset at and after the transition, not null
     * @return ZoneOffsetTransition
     */
    public static function ofEpoch($epochSecond, ZoneOffset $offsetBefore, ZoneOffset $offsetAfter)
    {
        return self::of(LocalDateTime::ofEpochSecond($epochSecond, 0, $offsetBefore), $offsetBefore, $offsetAfter);
    }

    /**
     * Creates an instance defining a transition between two offsets.
     *
     * @param $transition LocalDateTime the transition date-time with the offset before the transition, not null
     * @param $offsetBefore ZoneOffset the offset before the transition, not null
     * @param $offsetAfter ZoneOffset the offset at and after the transition, not null
     */
    private function __construct(LocalDateTime $transition, ZoneOffset $offsetBefore, ZoneOffset $offsetAfter)
    {
        $this->transition = $transition;
        $this->offsetBefore = $offsetBefore;
        $this->offsetAfter = $offsetAfter;
    }

    //-----------------------------------------------------------------------
    /**
     * Gets the transition instant.
     * <p>
     * This is the instant of the discontinuity, which is defined as the first
     * instant that the 'after' offset applies.
     * <p>
     * The methods {@link #getInstant()}, {@link #getDateTimeBefore()} and {@link #getDateTimeAfter()}
     * all represent the same instant.
     *
     * @return Instant the transition instant, not null
     */
    public function getInstant()
    {
        return $this->transition->toInstant($this->offsetBefore);
    }

    /**
     * Gets the transition instant as an epoch second.
     *
     * @return int the transition epoch second
     */
    public function toEpochSecond()
    {
        return $this->transition->toEpochSecond($this->offsetBefore);
    }

//-------------------------------------------------------------------------
    /**
     * Gets the local transition date-time, as would be expressed with the 'before' offset.
     * <p>
     * This is the date-time where the discontinuity begins expressed with the 'before' offset.
     * At this instant, the 'after' offset is actually used, therefore the combination of this
     * date-time and the 'before' offset will never occur.
     * <p>
     * The combination of the 'before' date-time and offset represents the same instant
     * as the 'after' date-time and offset.
     *
     * @return LocalDateTime the transition date-time expressed with the before offset, not null
     */
    public
    function getDateTimeBefore()
    {
        return $this->transition;
    }

    /**
     * Gets the local transition date-time, as would be expressed with the 'after' offset.
     * <p>
     * This is the first date-time after the discontinuity, when the new offset applies.
     * <p>
     * The combination of the 'before' date-time and offset represents the same instant
     * as the 'after' date-time and offset.
     *
     * @return LocalDateTime the transition date-time expressed with the after offset, not null
     */
    public
    function getDateTimeAfter()
    {
        return $this->transition->plusSeconds($this->getDurationSeconds());
    }

    /**
     * Gets the offset before the transition.
     * <p>
     * This is the offset in use before the instant of the transition.
     *
     * @return ZoneOffset the offset before the transition, not null
     */
    public
    function getOffsetBefore()
    {
        return $this->offsetBefore;
    }

    /**
     * Gets the offset after the transition.
     * <p>
     * This is the offset in use on and after the instant of the transition.
     *
     * @return ZoneOffset the offset after the transition, not null
     */
    public
    function getOffsetAfter()
    {
        return $this->offsetAfter;
    }

    /**
     * Gets the duration of the transition.
     * <p>
     * In most cases, the transition duration is one hour, however this is not always the case.
     * The duration will be positive for a gap and negative for an overlap.
     * Time-zones are second-based, so the nanosecond part of the duration will be zero.
     *
     * @return Duration the duration of the transition, positive for gaps, negative for overlaps
     */
    public function getDuration()
    {
        return Duration::ofSeconds($this->getDurationSeconds());
    }

    /**
     * Gets the duration of the transition in seconds.
     *
     * @return int the duration in seconds
     */
    private function getDurationSeconds()
    {
        return $this->getOffsetAfter()->getTotalSeconds() - $this->getOffsetBefore()->getTotalSeconds();
    }

    /**
     * Does this transition represent a gap in the local time-line.
     * <p>
     * Gaps occur where there are local date-times that simply do not exist.
     * An example would be when the offset changes from {@code +01:00} to {@code +02:00}.
     * This might be described as 'the clocks will move forward one hour tonight at 1am'.
     *
     * @return bool true if this transition is a gap, false if it is an overlap
     */
    public function isGap()
    {
        return $this->getOffsetAfter()->getTotalSeconds() > $this->getOffsetBefore()->getTotalSeconds();
    }

    /**
     * Does this transition represent an overlap in the local time-line.
     * <p>
     * Overlaps occur where there are local date-times that exist twice.
     * An example would be when the offset changes from {@code +02:00} to {@code +01:00}.
     * This might be described as 'the clocks will move back one hour tonight at 2am'.
     *
     * @return bool true if this transition is an overlap, false if it is a gap
     */
    public function isOverlap()
    {
        return $this->getOffsetAfter()->getTotalSeconds() < $this->getOffsetBefore()->getTotalSeconds();
    }

    /**
     * Checks if the specified offset is valid during this transition.
     * <p>
     * This checks to see if the given offset will be valid at some point in the transition.
     * A gap will always return false.
     * An overlap will return true if the offset is either the before or after offset.
     *
     * @param $offset ZoneOffset the offset to check, null returns false
     * @return true if the offset is valid during the transition
     */
    public function isValidOffset(ZoneOffset $offset)
    {
        return $this->isGap() ? false : ($this->getOffsetBefore()->equals($offset) || $this->getOffsetAfter()->equals($offset));
    }

    /**
     * Gets the valid offsets during this transition.
     * <p>
     * A gap will return an empty list, while an overlap will return both offsets.
     *
     * @return ZoneOffset[] the list of valid offsets
     */
    function getValidOffsets()
    {
        if ($this->isGap()) {
            return [];
        }
        return [$this->getOffsetBefore(), $this->getOffsetAfter()];
    }

    //-----------------------------------------------------------------------
    /**
     * Compares this transition to another based on the transition instant.
     * <p>
     * This compares the instants of each transition.
     * The offsets are ignored, making this order inconsistent with equals.
     *
     * @param $transition ZoneOffsetTransition the transition to compare to, not null
     * @return bool the comparator value, negative if less, positive if greater
     */
    public function compareTo(ZoneOffsetTransition $transition)
    {
        return $this->getInstant()->compareTo($transition->getInstant());
    }

    //-----------------------------------------------------------------------
    /**
     * Checks if this object equals another.
     * <p>
     * The entire state of the object is compared.
     *
     * @param $other mixed the other object to compare to, null returns false
     * @return bool true if equal
     */
    public function equals($other)
    {
        if ($other === $this) {
            return true;
        }
        if ($other instanceof ZoneOffsetTransition) {
            $d = $other;
            return $this->transition->equals($d->transition) &&
            $this->offsetBefore->equals($d->offsetBefore) && $this->offsetAfter->equals($d->offsetAfter);
        }
        return false;
    }

    //-----------------------------------------------------------------------
    /**
     * Returns a string describing this object.
     *
     * @return string a string for debugging, not null
     */
    public function __toString()
    {
        return "Transition["
        . ($this->isGap() ? "Gap" : "Overlap")
        . " at "
        . $this->transition
        . $this->offsetBefore
        . " to "
        . $this->offsetAfter
        . ']';
    }

}
