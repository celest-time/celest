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
namespace Celest\Chrono;


use Celest\ArithmeticException;
use Celest\DateTimeException;
use Celest\Format\DateTimeFormatter;
use Celest\Instant;
use Celest\LocalDate;
use Celest\LocalDateTime;
use Celest\LocalTime;
use Celest\Temporal\Temporal;
use Celest\Temporal\TemporalAccessor;
use Celest\Temporal\TemporalAdjuster;
use Celest\Temporal\TemporalAmount;
use Celest\Temporal\TemporalField;
use Celest\Temporal\TemporalQuery;
use Celest\Temporal\TemporalUnit;
use Celest\ZoneId;
use Celest\ZoneOffset;

/**
 * A date-time with a time-zone in an arbitrary chronology,
 * intended for advanced globalization use cases.
 * <p>
 * <b>Most applications should declare method signatures, fields and variables
 * as {@link ZonedDateTime}, not this interface.</b>
 * <p>
 * A {@code ChronoZonedDateTime} is the abstract representation of an offset date-time
 * where the {@code Chronology chronology}, or calendar system, is pluggable.
 * The date-time is defined in terms of fields expressed by {@link TemporalField},
 * where most common implementations are defined in {@link ChronoField}.
 * The chronology defines how the calendar system operates and the meaning of
 * the standard fields.
 *
 * <h3>When to use this interface</h3>
 * The design of the API encourages the use of {@code ZonedDateTime} rather than this
 * interface, even in the case where the application needs to deal with multiple
 * calendar systems. The rationale for this is explored in detail in {@link ChronoLocalDate}.
 * <p>
 * Ensure that the discussion in {@code ChronoLocalDate} has been read and understood
 * before using this interface.
 *
 * @implSpec
 * This interface must be implemented with care to ensure other classes operate correctly.
 * All implementations that can be instantiated must be final, immutable and thread-safe.
 * Subclasses should be Serializable wherever possible.
 *
 * @param $<D> the concrete type for the date of this date-time
 * @since 1.8
 */
interface ChronoZonedDateTime extends Temporal
{

    /**
     * Gets a comparator that compares {@code ChronoZonedDateTime} in
     * time-line order ignoring the chronology.
     * <p>
     * This comparator differs from the comparison in {@link #compareTo} in that it
     * only compares the underlying instant and not the chronology.
     * This allows dates in different calendar systems to be compared based
     * on the position of the date-time on the instant time-line.
     * The underlying comparison is equivalent to comparing the epoch-second and nano-of-second.
     *
     * @return a comparator that compares in time-line order ignoring the chronology
     * @see #isAfter
     * @see #isBefore
     * @see #isEqual
     */
    /*static function Comparator<ChronoZonedDateTime<> timeLineOrder()
    {
    return AbstractChronology.INSTANT_ORDER;
    }*/

//-----------------------------------------------------------------------
    /**
     * Obtains an instance of {@code ChronoZonedDateTime} from a temporal object.
     * <p>
     * This creates a zoned date-time based on the specified temporal.
     * A {@code TemporalAccessor} represents an arbitrary set of date and time information,
     * which this factory converts to an instance of {@code ChronoZonedDateTime}.
     * <p>
     * The conversion extracts and combines the chronology, date, time and zone
     * from the temporal object. The behavior is equivalent to using
     * {@link Chronology#zonedDateTime(TemporalAccessor)} with the extracted chronology.
     * Implementations are permitted to perform optimizations such as accessing
     * those fields that are equivalent to the relevant objects.
     * <p>
     * This method matches the signature of the functional interface {@link TemporalQuery}
     * allowing it to be used as a query via method reference, {@code ChronoZonedDateTime::from}.
     *
     * @param TemporalAccessor $temporal the temporal object to convert, not null
     * @return ChronoZonedDateTime the date-time, not null
     * @throws DateTimeException if unable to convert to a {@code ChronoZonedDateTime}
     * @see Chronology#zonedDateTime(TemporalAccessor)
     */
    static function from(TemporalAccessor $temporal);

    //-----------------------------------------------------------------------
    function range(TemporalField $field);

    function get(TemporalField $field);

    function getLong(TemporalField $field);

    /**
     * Gets the local date part of this date-time.
     * <p>
     * This returns a local date with the same year, month and day
     * as this date-time.
     *
     * @return LocalDate the date part of this date-time, not null
     */
    function toLocalDate();

    /**
     * Gets the local time part of this date-time.
     * <p>
     * This returns a local time with the same hour, minute, second and
     * nanosecond as this date-time.
     *
     * @return LocalTime the time part of this date-time, not null
     */
    function toLocalTime();

    /**
     * Gets the local date-time part of this date-time.
     * <p>
     * This returns a local date with the same year, month and day
     * as this date-time.
     *
     * @return ChronoLocalDateTime the local date-time part of this date-time, not null
     */
    function toLocalDateTime();

    /**
     * Gets the chronology of this date-time.
     * <p>
     * The {@code Chronology} represents the calendar system in use.
     * The era and other fields in {@link ChronoField} are defined by the chronology.
     *
     * @return Chronology the chronology, not null
     */
    function getChronology();

    /**
     * Gets the zone offset, such as '+01:00'.
     * <p>
     * This is the offset of the local date-time from UTC/Greenwich.
     *
     * @return ZoneOffset the zone offset, not null
     */
    function getOffset();

    /**
     * Gets the zone ID, such as 'Europe/Paris'.
     * <p>
     * This returns the stored time-zone id used to determine the time-zone rules.
     *
     * @return ZoneId the zone ID, not null
     */
    function getZone();

    //-----------------------------------------------------------------------
    /**
     * Returns a copy of this date-time changing the zone offset to the
     * earlier of the two valid offsets at a local time-line overlap.
     * <p>
     * This method only has any effect when the local time-line overlaps, such as
     * at an autumn daylight savings cutover. In this scenario, there are two
     * valid offsets for the local date-time. Calling this method will return
     * a zoned date-time with the earlier of the two selected.
     * <p>
     * If this method is called when it is not an overlap, {@code this}
     * is returned.
     * <p>
     * This instance is immutable and unaffected by this method call.
     *
     * @return ChronoZonedDateTime a {@code ChronoZonedDateTime} based on this date-time with the earlier offset, not null
     * @throws DateTimeException if no rules can be found for the zone
     * @throws DateTimeException if no rules are valid for this date-time
     */
    function withEarlierOffsetAtOverlap();

    /**
     * Returns a copy of this date-time changing the zone offset to the
     * later of the two valid offsets at a local time-line overlap.
     * <p>
     * This method only has any effect when the local time-line overlaps, such as
     * at an autumn daylight savings cutover. In this scenario, there are two
     * valid offsets for the local date-time. Calling this method will return
     * a zoned date-time with the later of the two selected.
     * <p>
     * If this method is called when it is not an overlap, {@code this}
     * is returned.
     * <p>
     * This instance is immutable and unaffected by this method call.
     *
     * @return ChronoZonedDateTime a {@code ChronoZonedDateTime} based on this date-time with the later offset, not null
     * @throws DateTimeException if no rules can be found for the zone
     * @throws DateTimeException if no rules are valid for this date-time
     */
    function withLaterOffsetAtOverlap();

    /**
     * Returns a copy of this date-time with a different time-zone,
     * retaining the local date-time if possible.
     * <p>
     * This method changes the time-zone and retains the local date-time.
     * The local date-time is only changed if it is invalid for the new zone.
     * <p>
     * To change the zone and adjust the local date-time,
     * use {@link #withZoneSameInstant(ZoneId)}.
     * <p>
     * This instance is immutable and unaffected by this method call.
     *
     * @param ZoneId $zone the time-zone to change to, not null
     * @return ChronoZonedDateTime a {@code ChronoZonedDateTime} based on this date-time with the requested zone, not null
     */
    function withZoneSameLocal(ZoneId $zone);

    /**
     * Returns a copy of this date-time with a different time-zone,
     * retaining the instant.
     * <p>
     * This method changes the time-zone and retains the instant.
     * This normally results in a change to the local date-time.
     * <p>
     * This method is based on retaining the same instant, thus gaps and overlaps
     * in the local time-line have no effect on the result.
     * <p>
     * To change the offset while keeping the local time,
     * use {@link #withZoneSameLocal(ZoneId)}.
     *
     * @param ZoneId $zone the time-zone to change to, not null
     * @return ChronoZonedDateTime a {@code ChronoZonedDateTime} based on this date-time with the requested zone, not null
     * @throws DateTimeException if the result exceeds the supported date range
     */
    function withZoneSameInstant(ZoneId $zone);

    /**
     * Checks if the specified field is supported.
     * <p>
     * This checks if the specified field can be queried on this date-time.
     * If false, then calling the {@link #range(TemporalField) range},
     * {@link #get(TemporalField) get} and {@link #with(TemporalField, long)}
     * methods will throw an exception.
     * <p>
     * The set of supported fields is defined by the chronology and normally includes
     * all {@code ChronoField} fields.
     * <p>
     * If the field is not a {@code ChronoField}, then the result of this method
     * is obtained by invoking {@code TemporalField.isSupportedBy(TemporalAccessor)}
     * passing {@code this} as the argument.
     * Whether the field is supported is determined by the field.
     *
     * @param TemporalField $field the field to check, null returns false
     * @return bool true if the field can be queried, false if not
     */
    function isSupported(TemporalField $field);

    /**
     * Checks if the specified unit is supported.
     * <p>
     * This checks if the specified unit can be added to or subtracted from this date-time.
     * If false, then calling the {@link #plus(long, TemporalUnit)} and
     * {@link #minus(long, TemporalUnit) minus} methods will throw an exception.
     * <p>
     * The set of supported units is defined by the chronology and normally includes
     * all {@code ChronoUnit} units except {@code FOREVER}.
     * <p>
     * If the unit is not a {@code ChronoUnit}, then the result of this method
     * is obtained by invoking {@code TemporalUnit.isSupportedBy(Temporal)}
     * passing {@code this} as the argument.
     * Whether the unit is supported is determined by the unit.
     *
     * @param TemporalUnit $unit the unit to check, null returns false
     * @return bool true if the unit can be added/subtracted, false if not
     */
    function isUnitSupported(TemporalUnit $unit);


    //-----------------------------------------------------------------------
    // override for covariant return type
    /**
     * {@inheritDoc}
     * @throws DateTimeException {@inheritDoc}
     * @throws ArithmeticException {@inheritDoc}
     * @return ChronoZonedDateTime
     */
    function adjust(TemporalAdjuster $adjuster);

    /**
     * {@inheritDoc}
     * @throws DateTimeException {@inheritDoc}
     * @throws ArithmeticException {@inheritDoc}
     * @return ChronoZonedDateTime
     */
    function with(TemporalField $field, $newValue);

    /**
     * {@inheritDoc}
     * @throws DateTimeException {@inheritDoc}
     * @throws ArithmeticException {@inheritDoc}
     * @return ChronoZonedDateTime
     */
    function plusAmount(TemporalAmount $amount);

    /**
     * {@inheritDoc}
     * @throws DateTimeException {@inheritDoc}
     * @throws ArithmeticException {@inheritDoc}
     * @return ChronoZonedDateTime
     */
    function plus($amountToAdd, TemporalUnit $unit);

    /**
     * {@inheritDoc}
     * @throws DateTimeException {@inheritDoc}
     * @throws ArithmeticException {@inheritDoc}
     * @return ChronoZonedDateTime
     */
    function minusAmount(TemporalAmount $amount);

    /**
     * {@inheritDoc}
     * @throws DateTimeException {@inheritDoc}
     * @throws ArithmeticException {@inheritDoc}
     * @return ChronoZonedDateTime
     */
    function minus($amountToSubtract, TemporalUnit $unit);

    //-----------------------------------------------------------------------
    /**
     * Queries this date-time using the specified query.
     * <p>
     * This queries this date-time using the specified query strategy object.
     * The {@code TemporalQuery} object defines the logic to be used to
     * obtain the result. Read the documentation of the query to understand
     * what the result of this method will be.
     * <p>
     * The result of this method is obtained by invoking the
     * {@link TemporalQuery#queryFrom(TemporalAccessor)} method on the
     * specified query passing {@code this} as the argument.
     *
     * @param <R> the type of the result
     * @param TemporalQuery $query the query to invoke, not null
     * @return mixed the query result, null may be returned (defined by the query)
     * @throws DateTimeException if unable to query (defined by the query)
     * @throws ArithmeticException if numeric overflow occurs (defined by the query)
     */
    function query(TemporalQuery $query);

    /**
     * Formats this date-time using the specified formatter.
     * <p>
     * This date-time will be passed to the formatter to produce a string.
     * <p>
     * The default implementation must behave as follows:
     * <pre>
     *  return formatter.format(this);
     * </pre>
     *
     * @param DateTimeFormatter $formatter the formatter to use, not null
     * @return string the formatted date-time string, not null
     * @throws DateTimeException if an error occurs during printing
     */
    function format(DateTimeFormatter $formatter);
    //-----------------------------------------------------------------------
    /**
     * Converts this date-time to an {@code Instant}.
     * <p>
     * This returns an {@code Instant} representing the same point on the
     * time-line as this date-time. The calculation combines the
     * {@linkplain #toLocalDateTime() local date-time} and
     * {@linkplain #getOffset() offset}.
     *
     * @return Instant an {@code Instant} representing the same instant, not null
     */
    function toInstant();

    /**
     * Converts this date-time to the number of seconds from the epoch
     * of 1970-01-01T00:00:00Z.
     * <p>
     * This uses the {@linkplain #toLocalDateTime() local date-time} and
     * {@linkplain #getOffset() offset} to calculate the epoch-second value,
     * which is the number of elapsed seconds from 1970-01-01T00:00:00Z.
     * Instants on the time-line after the epoch are positive, earlier are negative.
     *
     * @return int the number of seconds from the epoch of 1970-01-01T00:00:00Z
     */
    function toEpochSecond();
    //-----------------------------------------------------------------------
    /**
     * Compares this date-time to another date-time, including the chronology.
     * <p>
     * The comparison is based first on the instant, then on the local date-time,
     * then on the zone ID, then on the chronology.
     * It is "consistent with equals", as defined by {@link Comparable}.
     * <p>
     * If all the date-time objects being compared are in the same chronology, then the
     * additional chronology stage is not required.
     * <p>
     * This default implementation performs the comparison defined above.
     *
     * @param ChronoZonedDateTime $other the other date-time to compare to, not null
     * @return int the comparator value, negative if less, positive if greater
     */
    function compareTo(ChronoZonedDateTime $other);

    /**
     * Checks if the instant of this date-time is before that of the specified date-time.
     * <p>
     * This method differs from the comparison in {@link #compareTo} in that it
     * only compares the instant of the date-time. This is equivalent to using
     * {@code dateTime1.toInstant().isBefore(dateTime2.toInstant());}.
     * <p>
     * This default implementation performs the comparison based on the epoch-second
     * and nano-of-second.
     *
     * @param ChronoZonedDateTime $other the other date-time to compare to, not null
     * @return true if this point is before the specified date-time
     */
    function isBefore(ChronoZonedDateTime $other);

    /**
     * Checks if the instant of this date-time is after that of the specified date-time.
     * <p>
     * This method differs from the comparison in {@link #compareTo} in that it
     * only compares the instant of the date-time. This is equivalent to using
     * {@code dateTime1.toInstant().isAfter(dateTime2.toInstant());}.
     * <p>
     * This default implementation performs the comparison based on the epoch-second
     * and nano-of-second.
     *
     * @param ChronoZonedDateTime $other the other date-time to compare to, not null
     * @return bool true if this is after the specified date-time
     */
    function isAfter(ChronoZonedDateTime $other);

    /**
     * Checks if the instant of this date-time is equal to that of the specified date-time.
     * <p>
     * This method differs from the comparison in {@link #compareTo} and {@link #equals}
     * in that it only compares the instant of the date-time. This is equivalent to using
     * {@code dateTime1.toInstant().equals(dateTime2.toInstant());}.
     * <p>
     * This default implementation performs the comparison based on the epoch-second
     * and nano-of-second.
     *
     * @param ChronoZonedDateTime $other the other date-time to compare to, not null
     * @return bool true if the instant equals the instant of the specified date-time
     */
    function isEqual(ChronoZonedDateTime $other);
    //-----------------------------------------------------------------------
    /**
     * Checks if this date-time is equal to another date-time.
     * <p>
     * The comparison is based on the offset date-time and the zone.
     * To compare for the same instant on the time-line, use {@link #compareTo}.
     * Only objects of type {@code ChronoZonedDateTime} are compared, other types return false.
     *
     * @param mixed $obj the object to check, null returns false
     * @return bool true if this is equal to the other date-time
     */
    function equals($obj);

    //-----------------------------------------------------------------------
    /**
     * Outputs this date-time as a {@code String}.
     * <p>
     * The output will include the full zoned date-time.
     *
     * @return string a string representation of this date-time, not null
     */
    function __toString();

}
