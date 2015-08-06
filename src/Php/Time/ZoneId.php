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
namespace Php\Time;

use Php\Time\Temporal\TemporalAccessor;

/**
 * A time-zone ID, such as {@code Europe/Paris}.
 * <p>
 * A {@code ZoneId} is used to identify the rules used to convert between
 * an {@link Instant} and a {@link LocalDateTime}.
 * There are two distinct types of ID:
 * <ul>
 * <li>Fixed offsets - a fully resolved offset from UTC/Greenwich, that uses
 *  the same offset for all local date-times
 * <li>Geographical regions - an area where a specific set of rules for finding
 *  the offset from UTC/Greenwich apply
 * </ul>
 * Most fixed offsets are represented by {@link ZoneOffset}.
 * Calling {@link #normalized()} on any {@code ZoneId} will ensure that a
 * fixed offset ID will be represented as a {@code ZoneOffset}.
 * <p>
 * The actual rules, describing when and how the offset changes, are defined by {@link ZoneRules}.
 * This class is simply an ID used to obtain the underlying rules.
 * This approach is taken because rules are defined by governments and change
 * frequently, whereas the ID is stable.
 * <p>
 * The distinction has other effects. Serializing the {@code ZoneId} will only send
 * the ID, whereas serializing the rules sends the entire data set.
 * Similarly, a comparison of two IDs only examines the ID, whereas
 * a comparison of two rules examines the entire data set.
 *
 * <h3>Time-zone IDs</h3>
 * The ID is unique within the system.
 * There are three types of ID.
 * <p>
 * The simplest type of ID is that from {@code ZoneOffset}.
 * This consists of 'Z' and IDs starting with '+' or '-'.
 * <p>
 * The next type of ID are offset-style IDs with some form of prefix,
 * such as 'GMT+2' or 'UTC+01:00'.
 * The recognised prefixes are 'UTC', 'GMT' and 'UT'.
 * The offset is the suffix and will be normalized during creation.
 * These IDs can be normalized to a {@code ZoneOffset} using {@code normalized()}.
 * <p>
 * The third type of ID are region-based IDs. A region-based ID must be of
 * two or more characters, and not start with 'UTC', 'GMT', 'UT' '+' or '-'.
 * Region-based IDs are defined by configuration, see {@link ZoneRulesProvider}.
 * The configuration focuses on providing the lookup from the ID to the
 * underlying {@code ZoneRules}.
 * <p>
 * Time-zone rules are defined by governments and change frequently.
 * There are a number of organizations, known here as groups, that monitor
 * time-zone changes and collate them.
 * The default group is the IANA Time Zone Database (TZDB).
 * Other organizations include IATA (the airline industry body) and Microsoft.
 * <p>
 * Each group defines its own format for the region ID it provides.
 * The TZDB group defines IDs such as 'Europe/London' or 'America/New_York'.
 * TZDB IDs take precedence over other groups.
 * <p>
 * It is strongly recommended that the group name is included in all IDs supplied by
 * groups other than TZDB to avoid conflicts. For example, IATA airline time-zone
 * region IDs are typically the same as the three letter airport code.
 * However, the airport of Utrecht has the code 'UTC', which is obviously a conflict.
 * The recommended format for region IDs from groups other than TZDB is 'group~region'.
 * Thus if IATA data were defined, Utrecht airport would be 'IATA~UTC'.
 *
 * <h3>Serialization</h3>
 * This class can be serialized and stores the string zone ID in the external form.
 * The {@code ZoneOffset} subclass uses a dedicated format that only stores the
 * offset from UTC/Greenwich.
 * <p>
 * A {@code ZoneId} can be deserialized in a Java Runtime where the ID is unknown.
 * For example, if a server-side Java Runtime has been updated with a new zone ID, but
 * the client-side Java Runtime has not been updated. In this case, the {@code ZoneId}
 * object will exist, and can be queried using {@code getId}, {@code equals},
 * {@code hashCode}, {@code toString}, {@code getDisplayName} and {@code normalized}.
 * However, any call to {@code getRules} will fail with {@code ZoneRulesException}.
 * This approach is designed to allow a {@link ZonedDateTime} to be loaded and
 * queried, but not modified, on a Java Runtime with incomplete time-zone information.
 *
 * <p>
 * This is a <a href="{@docRoot}/java/lang/doc-files/ValueBased.html">value-based</a>
 * class; use of identity-sensitive operations (including reference equality
 * ({@code ==}), identity hash code, or synchronization) on instances of
 * {@code ZoneId} may have unpredictable results and should be avoided.
 * The {@code equals} method should be used for comparisons.
 *
 * @implSpec
 * This abstract class has two implementations, both of which are immutable and thread-safe.
 * One implementation models region-based IDs, the other is {@code ZoneOffset} modelling
 * offset-based IDs. This difference is visible in serialization.
 *
 * @since 1.8
 */
abstract class ZoneId
{
    //-----------------------------------------------------------------------
    /**
     * Gets the system default time-zone.
     * <p>
     * This queries {@link TimeZone#getDefault()} to find the default time-zone
     * and converts it to a {@code ZoneId}. If the system default time-zone is changed,
     * then the result of this method will also change.
     *
     * @return ZoneId the zone ID, not null
     * @throws DateTimeException if the converted zone ID has an invalid format
     * @throws ZoneRulesException if the converted zone region ID cannot be found
     */
    public static function systemDefault()
    {
        return TimeZone::getDefault()->toZoneId();
    }

    /**
     * Gets the set of available zone IDs.
     * <p>
     * This set includes the string form of all available region-based IDs.
     * Offset-based zone IDs are not included in the returned set.
     * The ID can be passed to {@link #of(String)} to create a {@code ZoneId}.
     * <p>
     * The set of zone IDs can increase over time, although in a typical application
     * the set of IDs is fixed. Each call to this method is thread-safe.
     *
     * @return String[] a modifiable copy of the set of zone IDs, not null
     */
    public static function getAvailableZoneIds()
    {
        return ZoneRulesProvider::getAvailableZoneIds();
    }

    /**
     * Obtains an instance of {@code ZoneId} from an ID ensuring that the
     * ID is valid and available for use.
     * <p>
     * This method parses the ID producing a {@code ZoneId} or {@code ZoneOffset}.
     * A {@code ZoneOffset} is returned if the ID is 'Z', or starts with '+' or '-'.
     * The result will always be a valid ID for which {@link ZoneRules} can be obtained.
     * <p>
     * Parsing matches the zone ID step by step as follows.
     * <ul>
     * <li>If the zone ID equals 'Z', the result is {@code ZoneOffset.UTC}.
     * <li>If the zone ID consists of a single letter, the zone ID is invalid
     *  and {@code DateTimeException} is thrown.
     * <li>If the zone ID starts with '+' or '-', the ID is parsed as a
     *  {@code ZoneOffset} using {@link ZoneOffset#of(String)}.
     * <li>If the zone ID equals 'GMT', 'UTC' or 'UT' then the result is a {@code ZoneId}
     *  with the same ID and rules equivalent to {@code ZoneOffset.UTC}.
     * <li>If the zone ID starts with 'UTC+', 'UTC-', 'GMT+', 'GMT-', 'UT+' or 'UT-'
     *  then the ID is a prefixed offset-based ID. The ID is split in two, with
     *  a two or three letter prefix and a suffix starting with the sign.
     *  The suffix is parsed as a {@link ZoneOffset#of(String) ZoneOffset}.
     *  The result will be a {@code ZoneId} with the specified UTC/GMT/UT prefix
     *  and the normalized offset ID as per {@link ZoneOffset#getId()}.
     *  The rules of the returned {@code ZoneId} will be equivalent to the
     *  parsed {@code ZoneOffset}.
     * <li>All other IDs are parsed as region-based zone IDs. Region IDs must
     *  match the regular expression <code>[A-Za-z][A-Za-z0-9~/._+-]+</code>
     *  otherwise a {@code DateTimeException} is thrown. If the zone ID is not
     *  in the configured set of IDs, {@code ZoneRulesException} is thrown.
     *  The detailed format of the region ID depends on the group supplying the data.
     *  The default set of data is supplied by the IANA Time Zone Database (TZDB).
     *  This has region IDs of the form '{area}/{city}', such as 'Europe/Paris' or 'America/New_York'.
     *  This is compatible with most IDs from {@link java.util.TimeZone}.
     * </ul>
     *
     * @param $zoneId string the time-zone ID, not null
     * @return ZoneId the zone ID, not null
     * @throws DateTimeException if the zone ID has an invalid format
     * @throws ZoneRulesException if the zone ID is a region ID that cannot be found
     */
    public
    static function of($zoneId)
    {
        return self::of($zoneId, true);
    }

    /**
     * Obtains an instance of {@code ZoneId} wrapping an offset.
     * <p>
     * If the prefix is "GMT", "UTC", or "UT" a {@code ZoneId}
     * with the prefix and the non-zero offset is returned.
     * If the prefix is empty {@code ""} the {@code ZoneOffset} is returned.
     *
     * @param $prefix string the time-zone ID, not null
     * @param $offset ZoneOffset the offset, not null
     * @return ZoneId the zone ID, not null
     * @throws IllegalArgumentException if the prefix is not one of
     *     "GMT", "UTC", or "UT", or ""
     */
    public static function ofOffset($prefix, ZoneOffset $offset)
    {
        if (strlen($prefix) == 0) {
            return $offset;
        }

        if ($prefix !== "GMT" && $prefix !== "UTC" && $prefix !== "UT") {
            throw new IllegalArgumentException("prefix should be GMT, UTC or UT, is: " . $prefix);
        }

        if ($offset->getTotalSeconds() != 0) {
            $prefix .= $offset->getId();
        }
        return new ZoneRegion($prefix, $offset->getRules());
    }

    /**
     * Parses the ID, taking a flag to indicate whether {@code ZoneRulesException}
     * should be thrown or not, used in deserialization.
     *
     * TODO package visiblity
     *
     * @param $zoneId string the time-zone ID, not null
     * @param $checkAvailable bool whether to check if the zone ID is available
     * @return the zone ID, not null
     * @throws DateTimeException if the ID format is invalid
     * @throws ZoneRulesException if checking availability and the ID cannot be found
     */
    public static function of($zoneId, $checkAvailable)
    {
        if (strlen($zoneId) <= 1 || $zoneId->startsWith("+") || $zoneId->startsWith("-")) {
            return ZoneOffset::of($zoneId);
        } else
            if ($zoneId->startsWith("UTC") || $zoneId->startsWith("GMT")) {
                return self::ofWithPrefix($zoneId, 3, $checkAvailable);
            } else if ($zoneId->startsWith("UT")) {
                return self::ofWithPrefix($zoneId, 2, $checkAvailable);
            }
        return ZoneRegion::ofId($zoneId, $checkAvailable);
    }

    /**
     * Parse once a prefix is established.
     *
     * @param $zoneId string the time-zone ID, not null
     * @param $prefixLength int the length of the prefix, 2 or 3
     * @param $checkAvailable bool
     * @return ZoneId the zone ID, not null
     * @throws DateTimeException if the zone ID has an invalid format
     */
    private
    static function ofWithPrefix($zoneId, $prefixLength, $checkAvailable)
    {
        $prefix = substr($zoneId, 0, $prefixLength);
        if (strlen($zoneId) == $prefixLength) {
            return self::ofOffset($prefix, ZoneOffset::UTC);
        }

        if ($zoneId[$prefixLength] != '+' && $zoneId[$prefixLength] != '-') {
            return ZoneRegion::ofId($zoneId, $checkAvailable);  // drop through to ZoneRulesProvider
        }
        try {
            $offset = ZoneOffset::of(substr($zoneId, $prefixLength));
            if ($offset == ZoneOffset::UTC) {
                return self::ofOffset($prefix, $offset);
            }
            return self::ofOffset($prefix, $offset);
        } catch (DateTimeException $ex) {
            throw new DateTimeException("Invalid ID for offset-based ZoneId: " . $zoneId, $ex);
        }
    }

    //-----------------------------------------------------------------------
    /**
     * Obtains an instance of {@code ZoneId} from a temporal object.
     * <p>
     * This obtains a zone based on the specified temporal.
     * A {@code TemporalAccessor} represents an arbitrary set of date and time information,
     * which this factory converts to an instance of {@code ZoneId}.
     * <p>
     * A {@code TemporalAccessor} represents some form of date and time information.
     * This factory converts the arbitrary temporal object to an instance of {@code ZoneId}.
     * <p>
     * The conversion will try to obtain the zone in a way that favours region-based
     * zones over offset-based zones using {@link TemporalQueries#zone()}.
     * <p>
     * This method matches the signature of the functional interface {@link TemporalQuery}
     * allowing it to be used as a query via method reference, {@code ZoneId::from}.
     *
     * @param $temporal TemporalAccessor the temporal object to convert, not null
     * @return ZoneId the zone ID, not null
     * @throws DateTimeException if unable to convert to a {@code ZoneId}
     */
    public static function from(TemporalAccessor $temporal)
    {
        $obj = $temporal->query(TemporalQueries::zone());
        if ($obj == null) {
            throw new DateTimeException("Unable to obtain ZoneId from TemporalAccessor: " .
                $temporal . " of type " . get_class($temporal));
        }

        return $obj;
    }

//-----------------------------------------------------------------------
    /**
     * Constructor only accessible within the package.
     * TODO package visiblity
     */
    public function __constructor()
    {
        if (get_class() != 'Php\Time\ZoneOffset' && get_class() != 'Php\Time\ZoneRegion') {
            throw new AssertionError("Invalid subclass");
        }
    }

//-----------------------------------------------------------------------
    /**
     * Gets the unique time-zone ID.
     * <p>
     * This ID uniquely defines this object.
     * The format of an offset based ID is defined by {@link ZoneOffset#getId()}.
     *
     * @return string the time-zone unique ID, not null
     */
    public abstract function getId();

    //-----------------------------------------------------------------------
    /**
     * Gets the textual representation of the zone, such as 'British Time' or
     * '+02:00'.
     * <p>
     * This returns the textual name used to identify the time-zone ID,
     * suitable for presentation to the user.
     * The parameters control the style of the returned text and the locale.
     * <p>
     * If no textual mapping is found then the {@link #getId() full ID} is returned.
     *
     * @param $style TextStyle the length of the text required, not null
     * @param $locale Locale the locale to use, not null
     * @return string the text value of the zone, not null
     */
    public function getDisplayName(TextStyle $style, Locale $locale)
    {
        return (new DateTimeFormatterBuilder())->appendZoneText($style)->toFormatter($locale)->format($this->toTemporal());
    }

    /**
     * Converts this zone to a {@code TemporalAccessor}.
     * <p>
     * A {@code ZoneId} can be fully represented as a {@code TemporalAccessor}.
     * However, the interface is not implemented by this class as most of the
     * methods on the interface have no meaning to {@code ZoneId}.
     * <p>
     * The returned temporal has no supported fields, with the query method
     * supporting the return of the zone using {@link TemporalQueries#zoneId()}.
     *
     * TODO use
     *
     * @return TemporalAccessor a temporal equivalent to this zone, not null
     */
    private function toTemporal()
    {
        return null;
    }

    //-----------------------------------------------------------------------
    /**
     * Gets the time-zone rules for this ID allowing calculations to be performed.
     * <p>
     * The rules provide the functionality associated with a time-zone,
     * such as finding the offset for a given instant or local date-time.
     * <p>
     * A time-zone can be invalid if it is deserialized in a Java Runtime which
     * does not have the same rules loaded as the Java Runtime that stored it.
     * In this case, calling this method will throw a {@code ZoneRulesException}.
     * <p>
     * The rules are supplied by {@link ZoneRulesProvider}. An advanced provider may
     * support dynamic updates to the rules without restarting the Java Runtime.
     * If so, then the result of this method may change over time.
     * Each individual call will be still remain thread-safe.
     * <p>
     * {@link ZoneOffset} will always return a set of rules where the offset never changes.
     *
     * @return ZoneRules the rules, not null
     * @throws ZoneRulesException if no rules are available for this ID
     */
    public abstract function getRules();

    /**
     * Normalizes the time-zone ID, returning a {@code ZoneOffset} where possible.
     * <p>
     * The returns a normalized {@code ZoneId} that can be used in place of this ID.
     * The result will have {@code ZoneRules} equivalent to those returned by this object,
     * however the ID returned by {@code getId()} may be different.
     * <p>
     * The normalization checks if the rules of this {@code ZoneId} have a fixed offset.
     * If they do, then the {@code ZoneOffset} equal to that offset is returned.
     * Otherwise {@code this} is returned.
     *
     * @return ZoneId the time-zone unique ID, not null
     */
    public function normalized()
    {
        try {
            $rules = $this->getRules();
            if ($rules->isFixedOffset()) {
                return $rules->getOffset(Instant::EPOCH());
            }
        } catch (ZoneRulesException $ex) {
            // invalid ZoneRegion is not important to this method
        }
        return $this;
    }

    //-----------------------------------------------------------------------
    /**
     * Checks if this time-zone ID is equal to another time-zone ID.
     * <p>
     * The comparison is based on the ID.
     *
     * @param $obj mixed the object to check, null returns false
     * @return bool true if this is equal to the other time-zone ID
     */
    public function equals($obj)
    {
        if ($this === $obj) {
            return true;
        }

        if ($obj instanceof ZoneId) {
            $other = $obj;
            return $this->getId()->equals($other->getId());
        }
        return false;
    }

    /**
     * Outputs this zone as a {@code String}, using the ID.
     *
     * @return string a string representation of this time-zone ID, not null
     */
    public
    function __toString()
    {
        return $this->getId();
    }
}
