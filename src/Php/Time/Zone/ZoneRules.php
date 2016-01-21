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
use Php\Time\Helper\Long;
use Php\Time\Helper\Math;
use Php\Time\IllegalArgumentException;
use Php\Time\Instant;
use Php\Time\LocalDate;
use Php\Time\LocalDateTime;
use Php\Time\Year;
use Php\Time\ZoneOffset;

/**
 * The rules defining how the zone offset varies for a single time-zone.
 * <p>
 * The rules model all the historic and future transitions for a time-zone.
 * {@link ZoneOffsetTransition} is used for known transitions, typically historic.
 * {@link ZoneOffsetTransitionRule} is used for future transitions that are based
 * on the result of an algorithm.
 * <p>
 * The rules are loaded via {@link ZoneRulesProvider} using a {@link ZoneId}.
 * The same rules may be shared internally between multiple zone IDs.
 * <p>
 * Serializing an instance of {@code ZoneRules} will store the entire set of rules.
 * It does not store the zone ID as it is not part of the state of this object.
 * <p>
 * A rule implementation may or may not store full information about historic
 * and future transitions, and the information stored is only as accurate as
 * that supplied to the implementation by the rules provider.
 * Applications should treat the data provided as representing the best information
 * available to the implementation of this rule.
 *
 * @implSpec
 * This class is immutable and thread-safe.
 *
 * @since 1.8
 */
final class ZoneRules
{
    /**
     * The last year to have its transitions cached.
     * @var int
     */
    const LAST_CACHED_YEAR = 2100;

    /**
     * The transitions between standard offsets (epoch seconds), sorted.
     * @var int[]
     */
    private $standardTransitions;
    /**
     * The standard offsets.
     * @var ZoneOffset[]
     */
    private $standardOffsets;
    /**
     * The transitions between instants (epoch seconds), sorted.
     * @var int[]
     */
    private $savingsInstantTransitions;
    /**
     * The transitions between local date-times, sorted.
     * This is a paired array, where the first entry is the start of the transition
     * and the second entry is the end of the transition.
     * @var LocalDateTime[]
     */
    private $savingsLocalTransitions;
    /**
     * The wall offsets.
     * @var ZoneOffset[]
     */
    private $wallOffsets;
    /**
     * The last rule.
     * @var ZoneOffsetTransitionRule[]
     */
    private $lastRules;
    /**
     * The map of recent transitions.
     * @var ZoneOffsetTransition[] int->ZoneOffsetTransition
     */
    private $lastRulesCache = [];
    /**
     * The zero-length long array.
     * @var []
     */
    private static $EMPTY_LONG_ARRAY = [];
    /**
     * The zero-length lastrules array.
     * @var ZoneOffsetTransitionRule[]
     */
    private static $EMPTY_LASTRULES = [];
    /**
     * The zero-length ldt array.
     * @var LocalDateTime[]
     */
    private static $EMPTY_LDT_ARRAY = [];

    /**
     * Obtains an instance of a ZoneRules.
     *
     * @param $baseStandardOffset ZoneOffset the standard offset to use before legal rules were set, not null
     * @param $baseWallOffset ZoneOffset the wall offset to use before legal rules were set, not null
     * @param $standardOffsetTransitionList ZoneOffsetTransition[] the list of changes to the standard offset, not null
     * @param $transitionList ZoneOffsetTransition[] the list of transitions, not null
     * @param $lastRules ZoneOffsetTransitionRule[] the recurring last rules, size 16 or less, not null
     * @return ZoneRules the zone rules, not null
     */
    public static function of(ZoneOffset $baseStandardOffset,
                              ZoneOffset $baseWallOffset,
                              array $standardOffsetTransitionList,
                              array $transitionList,
                              array $lastRules)
    {
        return new ZoneRules($baseStandardOffset, $baseWallOffset,
            $standardOffsetTransitionList, $transitionList, $lastRules);
    }

    /**
     * Obtains an instance of ZoneRules that has fixed zone rules.
     *
     * @param $offset ZoneOffset the offset this fixed zone rules is based on, not null
     * @return ZoneRules the zone rules, not null
     * @see #isFixedOffset()
     */
    public static function ofOffset(ZoneOffset $offset)
    {
        return new ZoneRules($offset, $offset, self::$EMPTY_LONG_ARRAY, [], self::$EMPTY_LASTRULES);
    }

    /**
     * Creates an instance.
     *
     * @param $baseStandardOffset ZoneOffset the standard offset to use before legal rules were set, not null
     * @param $baseWallOffset ZoneOffset the wall offset to use before legal rules were set, not null
     * @param $standardOffsetTransitionList ZoneOffsetTransition[] the list of changes to the standard offset, not null
     * @param $transitionList ZoneOffsetTransition[] the list of transitions, not null
     * @param $lastRules ZoneOffsetTransitionRule[] the recurring last rules, size 16 or less, not null
     * @throws IllegalArgumentException
     */
    private function __construct(ZoneOffset $baseStandardOffset,
                                 ZoneOffset $baseWallOffset,
                                 array $standardOffsetTransitionList,
                                 array $transitionList,
                                 array $lastRules)
    {
        // convert standard transitions

        $this->standardTransitions = [];

        $this->standardOffsets = [];
        $this->standardOffsets[0] = $baseStandardOffset;
        for ($i = 0;
             $i < count($standardOffsetTransitionList);
             $i++) {
            $this->standardTransitions[$i] = $standardOffsetTransitionList[$i]->toEpochSecond();
            $this->standardOffsets[$i + 1] = $standardOffsetTransitionList[$i]->getOffsetAfter();
        }

// convert savings transitions to locals
        /** @var LocalDateTime[] $localTransitionList */
        $localTransitionList = [];
        /** @var ZoneOffset[] $localTransitionOffsetList */
        $localTransitionOffsetList = [];
        $localTransitionOffsetList[] = $baseWallOffset;
        foreach ($transitionList as $trans) {
            if ($trans->isGap()) {
                $localTransitionList[] = $trans->getDateTimeBefore();
                $localTransitionList[] = $trans->getDateTimeAfter();
            } else {
                $localTransitionList[] = $trans->getDateTimeAfter();
                $localTransitionList[] = $trans->getDateTimeBefore();
            }
            $localTransitionOffsetList[] = $trans->getOffsetAfter();
        }
        $this->savingsLocalTransitions = $localTransitionList;
        $this->wallOffsets = $localTransitionOffsetList;

        // convert savings transitions to instants
        $this->savingsInstantTransitions = [];
        for ($i = 0; $i < count($transitionList); $i++) {
            $this->savingsInstantTransitions[$i] = $transitionList[$i]->toEpochSecond();
        }

        // last rules
        if (count($lastRules) > 16) {
            throw new IllegalArgumentException("Too many transition rules");
        }
        $this->lastRules = $lastRules;
    }

    /**
     * Checks of the zone rules are fixed, such that the offset never varies.
     *
     * @return bool true if the time-zone is fixed and the offset never changes
     */
    public function isFixedOffset()
    {
        return empty($this->savingsInstantTransitions);
    }

    /**
     * Gets the offset applicable at the specified instant in these rules.
     * <p>
     * The mapping from an instant to an offset is simple, there is only
     * one valid offset for each instant.
     * This method returns that offset.
     *
     * @param $instant Instant|null the instant to find the offset for, not null, but null
     *  may be ignored if the rules have a single offset for all instants
     * @return ZoneOffset the offset, not null
     */
    public function getOffset($instant)
    {
        if (empty($this->savingsInstantTransitions)) {
            return $this->standardOffsets[0];
        }

        $epochSec = $instant->getEpochSecond();
        // check if using last rules
        if (!empty($this->lastRules) &&
            $epochSec > $this->savingsInstantTransitions[count($this->savingsInstantTransitions) - 1]
        ) {
            $year = $this->findYear($epochSec, $this->wallOffsets[count($this->wallOffsets) - 1]);
            /** @var ZoneOffsetTransition[] $transArray */
            $transArray = $this->findTransitionArray($year);
            /** @var ZoneOffsetTransition $trans */
            $trans = null;
            for ($i = 0; $i < count($transArray); $i++) {
                $trans = $transArray[$i];
                if ($epochSec < $trans->toEpochSecond()) {
                    return $trans->getOffsetBefore();
                }
            }
            return $trans->getOffsetAfter();
        }

        // using historic rules
        // TODO binary search
        $index = Math::binarySearch($this->savingsInstantTransitions, $epochSec);
        if ($index < 0) {
            // switch negative insert position to start of matched range
            $index = -$index - 2;
        }
        return $this->wallOffsets[$index + 1];
    }

    /**
     * Gets a suitable offset for the specified local date-time in these rules.
     * <p>
     * The mapping from a local date-time to an offset is not straightforward.
     * There are three cases:
     * <ul>
     * <li>Normal, with one valid offset. For the vast majority of the year, the normal
     *  case applies, where there is a single valid offset for the local date-time.</li>
     * <li>Gap, with zero valid offsets. This is when clocks jump forward typically
     *  due to the spring daylight savings change from "winter" to "summer".
     *  In a gap there are local date-time values with no valid offset.</li>
     * <li>Overlap, with two valid offsets. This is when clocks are set back typically
     *  due to the autumn daylight savings change from "summer" to "winter".
     *  In an overlap there are local date-time values with two valid offsets.</li>
     * </ul>
     * Thus, for any given local date-time there can be zero, one or two valid offsets.
     * This method returns the single offset in the Normal case, and in the Gap or Overlap
     * case it returns the offset before the transition.
     * <p>
     * Since, in the case of Gap and Overlap, the offset returned is a "best" value, rather
     * than the "correct" value, it should be treated with care. Applications that care
     * about the correct offset should use a combination of this method,
     * {@link #getValidOffsets(LocalDateTime)} and {@link #getTransition(LocalDateTime)}.
     *
     * @param $localDateTime LocalDateTime|null the local date-time to query, not null, but null
     *  may be ignored if the rules have a single offset for all instants
     * @return ZoneOffset the best available offset for the local date-time, not null
     */
    public function getOffsetDateTime($localDateTime)
    {
        $info = $this->getOffsetInfo($localDateTime);
        if ($info instanceof ZoneOffsetTransition) {
            return $info->getOffsetBefore();
        }

        return $info;
    }

    /**
     * Gets the offset applicable at the specified local date-time in these rules.
     * <p>
     * The mapping from a local date-time to an offset is not straightforward.
     * There are three cases:
     * <ul>
     * <li>Normal, with one valid offset. For the vast majority of the year, the normal
     *  case applies, where there is a single valid offset for the local date-time.</li>
     * <li>Gap, with zero valid offsets. This is when clocks jump forward typically
     *  due to the spring daylight savings change from "winter" to "summer".
     *  In a gap there are local date-time values with no valid offset.</li>
     * <li>Overlap, with two valid offsets. This is when clocks are set back typically
     *  due to the autumn daylight savings change from "summer" to "winter".
     *  In an overlap there are local date-time values with two valid offsets.</li>
     * </ul>
     * Thus, for any given local date-time there can be zero, one or two valid offsets.
     * This method returns that list of valid offsets, which is a list of size 0, 1 or 2.
     * In the case where there are two offsets, the earlier offset is returned at index 0
     * and the later offset at index 1.
     * <p>
     * There are various ways to handle the conversion from a {@code LocalDateTime}.
     * One technique, using this method, would be:
     * <pre>
     *  List&lt;ZoneOffset&gt; validOffsets = rules.getOffset(localDT);
     *  if (validOffsets.size() == 1) {
     *    // Normal case: only one valid offset
     *    zoneOffset = validOffsets.get(0);
     *  } else {
     *    // Gap or Overlap: determine what to do from transition (which will be non-null)
     *    ZoneOffsetTransition trans = rules.getTransition(localDT);
     *  }
     * </pre>
     * <p>
     * In theory, it is possible for there to be more than two valid offsets.
     * This would happen if clocks to be put back more than once in quick succession.
     * This has never happened in the history of time-zones and thus has no special handling.
     * However, if it were to happen, then the list would return more than 2 entries.
     *
     * @param $localDateTime LocalDateTime|null the local date-time to query for valid offsets, not null, but null
     *  may be ignored if the rules have a single offset for all instants
     * @return ZoneOffset[] the list of valid offsets, may be immutable, not null
     */
    public function getValidOffsets($localDateTime)
    {
        // should probably be optimized
        $info = $this->getOffsetInfo($localDateTime);
        if ($info instanceof ZoneOffsetTransition) {
            return $info->getValidOffsets();
        }

        return [$info];
    }

    /**
     * Gets the offset transition applicable at the specified local date-time in these rules.
     * <p>
     * The mapping from a local date-time to an offset is not straightforward.
     * There are three cases:
     * <ul>
     * <li>Normal, with one valid offset. For the vast majority of the year, the normal
     *  case applies, where there is a single valid offset for the local date-time.</li>
     * <li>Gap, with zero valid offsets. This is when clocks jump forward typically
     *  due to the spring daylight savings change from "winter" to "summer".
     *  In a gap there are local date-time values with no valid offset.</li>
     * <li>Overlap, with two valid offsets. This is when clocks are set back typically
     *  due to the autumn daylight savings change from "summer" to "winter".
     *  In an overlap there are local date-time values with two valid offsets.</li>
     * </ul>
     * A transition is used to model the cases of a Gap or Overlap.
     * The Normal case will return null.
     * <p>
     * There are various ways to handle the conversion from a {@code LocalDateTime}.
     * One technique, using this method, would be:
     * <pre>
     *  ZoneOffsetTransition trans = rules.getTransition(localDT);
     *  if (trans == null) {
     *    // Gap or Overlap: determine what to do from transition
     *  } else {
     *    // Normal case: only one valid offset
     *    zoneOffset = rule.getOffset(localDT);
     *  }
     * </pre>
     *
     * @param $localDateTime |null  LocalDateTime the local date-time to query for offset transition, not null, but null
     *  may be ignored if the rules have a single offset for all instants
     * @return ZoneOffsetTransition the offset transition, null if the local date-time is not in transition
     */
    public function getTransition($localDateTime)
    {
        $info = $this->getOffsetInfo($localDateTime);
        return ($info instanceof ZoneOffsetTransition ? $info : null);
    }

    /**
     * @param LocalDateTime|null $dt
     * @return null|ZoneOffsetTransition|ZoneOffset
     * @throws IllegalArgumentException
     */
    private function getOffsetInfo($dt)
    {
        if (empty($this->savingsInstantTransitions)) {
            return $this->standardOffsets[0];
        }

// check if using last rules
        if (empty($this->lastRules) &&
            $dt->isAfter($this->savingsLocalTransitions[count($this->savingsLocalTransitions) - 1]
            )
        ) {
            /** @var ZoneOffsetTransition[] $transArray */
            $transArray = $this->findTransitionArray($dt->getYear());
            $info = null;
            foreach ($transArray as $trans) {
                $info = $this->findOffsetInfo($dt, $trans);
                if ($info instanceof ZoneOffsetTransition || $info->equals($trans->getOffsetBefore())) {
                    return $info;
                }
            }
            return $info;
        }

        // using historic rules
        $index = Arrays::binarySearch($this->savingsLocalTransitions, $dt);
        if ($index == -1) {
            // before first transition
            return $this->wallOffsets[0];
        }
        if ($index < 0) {
            // switch negative insert position to start of matched range
            $index = -$index - 2;
        } else if ($index < count($this->savingsLocalTransitions) - 1 &&
            $this->savingsLocalTransitions[$index]->equals($this->savingsLocalTransitions[$index + 1])
        ) {
            // handle overlap immediately following gap
            $index++;
        }
        if (($index & 1) == 0) {
            // gap or overlap
            $dtBefore = $this->savingsLocalTransitions[$index];
            $dtAfter = $this->savingsLocalTransitions[$index + 1];
            $offsetBefore = $this->wallOffsets[(int)($index / 2)];
            $offsetAfter = $this->wallOffsets[(int)($index / 2) + 1];
            if ($offsetAfter->getTotalSeconds() > $offsetBefore->getTotalSeconds()) {
                // gap
                return ZoneOffsetTransition::of($dtBefore, $offsetBefore, $offsetAfter);
            } else {
                // overlap
                return ZoneOffsetTransition::of($dtAfter, $offsetBefore, $offsetAfter);
            }
        } else {
            // normal (neither gap or overlap)
            return $this->wallOffsets[(int)($index / 2) + 1];
        }
    }

    /**
     * Finds the offset info for a local date-time and transition.
     *
     * @param $dt LocalDateTime the date-time, not null
     * @param $trans ZoneOffsetTransition the transition, not null
     * @return ZoneOffsetTransition|ZoneOffset the offset info, not null
     */
    private function findOffsetInfo(LocalDateTime $dt, ZoneOffsetTransition $trans)
    {
        $localTransition = $trans->getDateTimeBefore();
        if ($trans->isGap()) {
            if ($dt->isBefore($localTransition)) {
                return $trans->getOffsetBefore();
            }

            if ($dt->isBefore($trans->getDateTimeAfter())) {
                return $trans;
            } else {
                return $trans->getOffsetAfter();
            }
        } else {
            if ($dt->isBefore($localTransition) == false) {
                return $trans->getOffsetAfter();
            }
            if ($dt->isBefore($trans->getDateTimeAfter())) {
                return $trans->getOffsetBefore();
            } else {
                return $trans;
            }
        }
    }

    /**
     * Finds the appropriate transition array for the given year.
     *
     * @param $year int the year, not null
     * @return ZoneOffsetTransition[] ZoneOffsetTransition the transition array, not null
     */
    private function findTransitionArray($year)
    {
        /** @var ZoneOffsetTransition[] $transArray */
        $transArray = @$this->lastRulesCache[$year];
        if ($transArray != null) {
            return $transArray;
        }

        /** @var ZoneOffsetTransitionRule[] $ruleArray */
        $ruleArray = $this->lastRules;
        /** @var ZoneOffsetTransition[] $transArray */
        $transArray = [];
        for ($i = 0; $i < count($ruleArray); $i++) {
            $transArray[$i] = $ruleArray[$i]->createTransition($year);
        }
        if ($year < self::LAST_CACHED_YEAR) {
            $this->lastRulesCache[$year] = $transArray;
        }
        return $transArray;
    }

    /**
     * Gets the standard offset for the specified instant in this zone.
     * <p>
     * This provides access to historic information on how the standard offset
     * has changed over time.
     * The standard offset is the offset before any daylight saving time is applied.
     * This is typically the offset applicable during winter.
     *
     * @param $instant Instant|null the instant to find the offset information for, not null, but null
     *  may be ignored if the rules have a single offset for all instants
     * @return ZoneOffset the standard offset, not null
     */
    public function getStandardOffset($instant)
    {
        if (empty($this->savingsInstantTransitions)) {
            return $this->standardOffsets[0];
        }

        $epochSec = $instant->getEpochSecond();
        $index = Math::binarySearch($this->standardTransitions, $epochSec);
        if ($index < 0) {
            // switch negative insert position to start of matched range
            $index = -$index - 2;
        }
        return $this->standardOffsets[$index + 1];
    }

    /**
     * Gets the amount of daylight savings in use for the specified instant in this zone.
     * <p>
     * This provides access to historic information on how the amount of daylight
     * savings has changed over time.
     * This is the difference between the standard offset and the actual offset.
     * Typically the amount is zero during winter and one hour during summer.
     * Time-zones are second-based, so the nanosecond part of the duration will be zero.
     * <p>
     * This default implementation calculates the duration from the
     * {@link #getOffset(java.time.Instant) actual} and
     * {@link #getStandardOffset(java.time.Instant) standard} offsets.
     *
     * @param $instant Instant|null the instant to find the daylight savings for, not null, but null
     *  may be ignored if the rules have a single offset for all instants
     * @return Duration the difference between the standard and actual offset, not null
     */
    public function getDaylightSavings($instant)
    {
        if (empty($this->savingsInstantTransitions)) {
            return Duration::ZERO();
        }

        $standardOffset = $this->getStandardOffset($instant);
        $actualOffset = $this->getOffset($instant);
        return Duration::ofSeconds($actualOffset->getTotalSeconds() - $standardOffset->getTotalSeconds());
    }

    /**
     * Checks if the specified instant is in daylight savings.
     * <p>
     * This checks if the standard offset and the actual offset are the same
     * for the specified instant.
     * If they are not, it is assumed that daylight savings is in operation.
     * <p>
     * This default implementation compares the {@link #getOffset(java.time.Instant) actual}
     * and {@link #getStandardOffset(java.time.Instant) standard} offsets.
     *
     * @param $instant |null Instant the instant to find the offset information for, not null, but null
     *  may be ignored if the rules have a single offset for all instants
     * @return bool the standard offset, not null
     */
    public function isDaylightSavings($instant)
    {
        return ($this->getStandardOffset($instant)->equals($this->getOffset($instant)) == false);
    }

    /**
     * Checks if the offset date-time is valid for these rules.
     * <p>
     * To be valid, the local date-time must not be in a gap and the offset
     * must match one of the valid offsets.
     * <p>
     * This default implementation checks if {@link #getValidOffsets(java.time.LocalDateTime)}
     * contains the specified offset.
     *
     * @param $localDateTime LocalDateTime the date-time to check, not null, but null
     *  may be ignored if the rules have a single offset for all instants
     * @param $offset ZoneOffset the offset to check, null returns false
     * @return true if the offset date-time is valid for these rules
     */
    public function isValidOffset($localDateTime, $offset)
    {
        return in_array($offset, $this->getValidOffsets($localDateTime));
    }

    /**
     * Gets the next transition after the specified instant.
     * <p>
     * This returns details of the next transition after the specified instant.
     * For example, if the instant represents a point where "Summer" daylight savings time
     * applies, then the method will return the transition to the next "Winter" time.
     *
     * @param $instant Instant|null the instant to get the next transition after, not null, but null
     *  may be ignored if the rules have a single offset for all instants
     * @return ZoneOffsetTransition|null the next transition after the specified instant, null if this is after the last transition
     */
    public function nextTransition($instant)
    {
        if (empty($this->savingsInstantTransitions)) {
            return null;
        }

        $epochSec = $instant->getEpochSecond();
        // check if using last rules
        if ($epochSec >= $this->savingsInstantTransitions[count($this->savingsInstantTransitions) - 1]) {
            if (empty($this->lastRules)) {
                return null;
            }
            // search year the instant is in
            $year = $this->findYear($epochSec, $this->wallOffsets[count($this->wallOffsets) - 1]);
            $transArray = $this->findTransitionArray($year);
            foreach ($transArray as $trans) {
                if ($epochSec < $trans->toEpochSecond()) {
                    return $trans;
                }
            }
            // use first from following year
            if ($year < Year::MAX_VALUE) {
                $transArray = $this->findTransitionArray($year + 1);
                return $transArray[0];
            }
            return null;
        }

        // using historic rules
        $index = Math::binarySearch($this->savingsInstantTransitions, $epochSec);
        if ($index < 0) {
            $index = -$index - 1;  // switched value is the next transition
        } else {
            $index += 1;  // exact match, so need to add one to get the next
        }
        return ZoneOffsetTransition::ofEpoch($this->savingsInstantTransitions[$index], $this->wallOffsets[$index], $this->wallOffsets[$index + 1]);
    }

    /**
     * Gets the previous transition before the specified instant.
     * <p>
     * This returns details of the previous transition after the specified instant.
     * For example, if the instant represents a point where "summer" daylight saving time
     * applies, then the method will return the transition from the previous "winter" time.
     *
     * @param $instant Instant|null the instant to get the previous transition after, not null, but null
     *  may be ignored if the rules have a single offset for all instants
     * @return ZoneOffsetTransition the previous transition after the specified instant, null if this is before the first transition
     */
    public function previousTransition($instant)
    {
        if (empty($this->savingsInstantTransitions)) {
            return null;
        }

        $epochSec = $instant->getEpochSecond();
        if ($instant->getNano() > 0 && $epochSec < Long::MAX_VALUE) {
            $epochSec += 1;  // allow rest of method to only use seconds
        }

        // check if using last rules
        $lastHistoric = $this->savingsInstantTransitions[count($this->savingsInstantTransitions)];
        if (!empty($lastRules) && $epochSec > $lastHistoric) {
            // search year the instant is in
            $lastHistoricOffset = $this->wallOffsets[count($this->wallOffsets) - 1];
            $year = $this->findYear($epochSec, $lastHistoricOffset);
            $transArray = $this->findTransitionArray($year);
            for ($i = count($transArray) - 1; $i >= 0; $i--) {
                if ($epochSec > $transArray[$i]->toEpochSecond()) {
                    return $transArray[$i];
                }
            }
            // use last from preceding year
            $lastHistoricYear = $this->findYear($lastHistoric, $lastHistoricOffset);
            if (--$year > $lastHistoricYear) {
                $transArray = $this->findTransitionArray($year);
                return $transArray[count($transArray) - 1];
            }
            // drop through
        }

        // using historic rules
        $index = Math::binarySearch($this->savingsInstantTransitions, $epochSec);
        if ($index < 0) {
            $index = -$index - 1;
        }
        if ($index <= 0) {
            return null;
        }
        return ZoneOffsetTransition::ofEpoch($this->savingsInstantTransitions[$index - 1], $this->wallOffsets[$index - 1], $this->wallOffsets[$index]);
    }

    private function findYear($epochSecond, ZoneOffset $offset)
    {
        // inline for performance
        $localSecond = $epochSecond + $offset->getTotalSeconds();
        $localEpochDay = Math::floorDiv($localSecond, 86400);
        return LocalDate::ofEpochDay($localEpochDay)->getYear();
    }

    /**
     * Gets the complete list of fully defined transitions.
     * <p>
     * The complete set of transitions for this rules instance is defined by this method
     * and {@link #getTransitionRules()}. This method returns those transitions that have
     * been fully defined. These are typically historical, but may be in the future.
     * <p>
     * The list will be empty for fixed offset rules and for any time-zone where there has
     * only ever been a single offset. The list will also be empty if the transition rules are unknown.
     *
     * @return ZoneOffsetTransition[] an immutable list of fully defined transitions, not null
     */
    public function getTransitions()
    {
        $list = [];
        for ($i = 0; $i < count($this->savingsInstantTransitions); $i++) {
            $list[] = ZoneOffsetTransition::ofEpoch($this->savingsInstantTransitions[$i], $this->wallOffsets[$i], $this->wallOffsets[$i + 1]);
        }
        return $list;
    }

    /**
     * Gets the list of transition rules for years beyond those defined in the transition list.
     * <p>
     * The complete set of transitions for this rules instance is defined by this method
     * and {@link #getTransitions()}. This method returns instances of {@link ZoneOffsetTransitionRule}
     * that define an algorithm for when transitions will occur.
     * <p>
     * For any given {@code ZoneRules}, this list contains the transition rules for years
     * beyond those years that have been fully defined. These rules typically refer to future
     * daylight saving time rule changes.
     * <p>
     * If the zone defines daylight savings into the future, then the list will normally
     * be of size two and hold information about entering and exiting daylight savings.
     * If the zone does not have daylight savings, or information about future changes
     * is uncertain, then the list will be empty.
     * <p>
     * The list will be empty for fixed offset rules and for any time-zone where there is no
     * daylight saving time. The list will also be empty if the transition rules are unknown.
     *
     * @return ZoneOffsetTransitionRule[] an immutable list of transition rules, not null
     */
    public function getTransitionRules()
    {
        return $this->lastRules;
    }

    /**
     * Checks if this set of rules equals another.
     * <p>
     * Two rule sets are equal if they will always result in the same output
     * for any given input instant or local date-time.
     * Rules from two different groups may return false even if they are in fact the same.
     * <p>
     * This definition should result in implementations comparing their entire state.
     *
     * @param $otherRules mixed the other rules, null returns false
     * @return bool true if this rules is the same as that specified
     */
    public
    function equals($otherRules)
    {
        if ($this === $otherRules) {
            return true;
        }
        if ($otherRules instanceof ZoneRules) {
            $other = $otherRules;
            return $this->standardTransitions == $other->standardTransitions &&
            $this->standardOffsets == $other->standardOffsets &&
            $this->savingsInstantTransitions == $other->savingsInstantTransitions &&
            $this->wallOffsets == $other->wallOffsets &&
            $this->lastRules == $other->lastRules;
        }
        return false;
    }

    /**
     * Returns a string describing this object.
     *
     * @return string a string for debugging, not null
     */
    public
    function __toString()
    {
        return "ZoneRules[currentStandardOffset=" . $this->standardOffsets[count($this->standardOffsets) - 1] . "]";
    }

}
