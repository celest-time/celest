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

namespace Celest\Temporal;

use Celest\Temporal\Misc\JulianField;
use Celest\Temporal\ChronoUnit as CU;

/**
 * A set of date fields that provide access to Julian Days.
 * <p>
 * The Julian Day is a standard way of expressing date and time commonly used in the scientific community.
 * It is expressed as a decimal number of whole days where days start at midday.
 * This class represents variations on Julian Days that count whole days from midnight.
 * <p>
 * The fields are implemented relative to {@link ChronoField#EPOCH_DAY EPOCH_DAY}.
 * The fields are supported, and can be queried and set if {@code EPOCH_DAY} is available.
 * The fields work with all chronologies.
 *
 * @implSpec
 * This is an immutable and thread-safe class.
 *
 * @since 1.8
 */
final class JulianFields
{
    /**
     * Julian Day field.
     * <p>
     * This is an integer-based version of the Julian Day Number.
     * Julian Day is a well-known system that represents the count of whole days since day 0,
     * which is defined to be January 1, 4713 BCE in the Julian calendar, and -4713-11-24 Gregorian.
     * The field  has "JulianDay" as 'name', and 'DAYS' as 'baseUnit'.
     * The field always refers to the local date-time, ignoring the offset or zone.
     * <p>
     * For date-times, 'JULIAN_DAY.getFrom()' assumes the same value from
     * midnight until just before the next midnight.
     * When 'JULIAN_DAY.adjustInto()' is applied to a date-time, the time of day portion remains unaltered.
     * 'JULIAN_DAY.adjustInto()' and 'JULIAN_DAY.getFrom()' only apply to {@code Temporal} objects that
     * can be converted into {@link ChronoField#EPOCH_DAY}.
     * An {@link UnsupportedTemporalTypeException} is thrown for any other type of object.
     * <p>
     * In the resolving phase of parsing, a date can be created from a Julian Day field.
     * In {@linkplain ResolverStyle#STRICT strict mode} and {@linkplain ResolverStyle#SMART smart mode}
     * the Julian Day value is validated against the range of valid values.
     * In {@linkplain ResolverStyle#LENIENT lenient mode} no validation occurs.
     *
     * <h3>Astronomical and Scientific Notes</h3>
     * The standard astronomical definition uses a fraction to indicate the time-of-day,
     * thus 3.25 would represent the time 18:00, since days start at midday.
     * This implementation uses an integer and days starting at midnight.
     * The integer value for the Julian Day Number is the astronomical Julian Day value at midday
     * of the date in question.
     * This amounts to the astronomical Julian Day, rounded to an integer {@code JDN = floor(JD + 0.5)}.
     *
     * <pre>
     *  | ISO date          |  Julian Day Number | Astronomical Julian Day |
     *  | 1970-01-01T00:00  |         2,440,588  |         2,440,587.5     |
     *  | 1970-01-01T06:00  |         2,440,588  |         2,440,587.75    |
     *  | 1970-01-01T12:00  |         2,440,588  |         2,440,588.0     |
     *  | 1970-01-01T18:00  |         2,440,588  |         2,440,588.25    |
     *  | 1970-01-02T00:00  |         2,440,589  |         2,440,588.5     |
     *  | 1970-01-02T06:00  |         2,440,589  |         2,440,588.75    |
     *  | 1970-01-02T12:00  |         2,440,589  |         2,440,589.0     |
     * </pre>
     * <p>
     * Julian Days are sometimes taken to imply Universal Time or UTC, but this
     * implementation always uses the Julian Day number for the local date,
     * regardless of the offset or time-zone.
     * @return TemporalField
     */
    public static function JULIAN_DAY()
    {
        if (self::$JULIAN_DAY === null) {
            self::$JULIAN_DAY = new JulianField("JulianDay", CU::DAYS(), CU::FOREVER(), 2440588);
        }
        return self::$JULIAN_DAY;
    }

    /** @var TemporalField */
    private static $JULIAN_DAY;

    /**
     * Modified Julian Day field.
     * <p>
     * This is an integer-based version of the Modified Julian Day Number.
     * Modified Julian Day (MJD) is a well-known system that counts days continuously.
     * It is defined relative to astronomical Julian Day as  {@code MJD = JD - 2400000.5}.
     * Each Modified Julian Day runs from midnight to midnight.
     * The field always refers to the local date-time, ignoring the offset or zone.
     * <p>
     * For date-times, 'MODIFIED_JULIAN_DAY.getFrom()' assumes the same value from
     * midnight until just before the next midnight.
     * When 'MODIFIED_JULIAN_DAY.adjustInto()' is applied to a date-time, the time of day portion remains unaltered.
     * 'MODIFIED_JULIAN_DAY.adjustInto()' and 'MODIFIED_JULIAN_DAY.getFrom()' only apply to {@code Temporal} objects
     * that can be converted into {@link ChronoField#EPOCH_DAY}.
     * An {@link UnsupportedTemporalTypeException} is thrown for any other type of object.
     * <p>
     * This implementation is an integer version of MJD with the decimal part rounded to floor.
     * <p>
     * In the resolving phase of parsing, a date can be created from a Modified Julian Day field.
     * In {@linkplain ResolverStyle#STRICT strict mode} and {@linkplain ResolverStyle#SMART smart mode}
     * the Modified Julian Day value is validated against the range of valid values.
     * In {@linkplain ResolverStyle#LENIENT lenient mode} no validation occurs.
     *
     * <h3>Astronomical and Scientific Notes</h3>
     * <pre>
     *  | ISO date          | Modified Julian Day |      Decimal MJD |
     *  | 1970-01-01T00:00  |             40,587  |       40,587.0   |
     *  | 1970-01-01T06:00  |             40,587  |       40,587.25  |
     *  | 1970-01-01T12:00  |             40,587  |       40,587.5   |
     *  | 1970-01-01T18:00  |             40,587  |       40,587.75  |
     *  | 1970-01-02T00:00  |             40,588  |       40,588.0   |
     *  | 1970-01-02T06:00  |             40,588  |       40,588.25  |
     *  | 1970-01-02T12:00  |             40,588  |       40,588.5   |
     * </pre>
     *
     * Modified Julian Days are sometimes taken to imply Universal Time or UTC, but this
     * implementation always uses the Modified Julian Day for the local date,
     * regardless of the offset or time-zone.
     * @return TemporalField
     */
    public static function MODIFIED_JULIAN_DAY()
    {
        if (self::$MODIFIED_JULIAN_DAY === null) {
            self::$MODIFIED_JULIAN_DAY = new JulianField("ModifiedJulianDay", CU::DAYS(), CU::FOREVER(), 40587);
        }
        return self::$MODIFIED_JULIAN_DAY;
    }

    /** @var TemporalField */
    private static $MODIFIED_JULIAN_DAY;

    /**
     * Rata Die field.
     * <p>
     * Rata Die counts whole days continuously starting day 1 at midnight at the beginning of 0001-01-01 (ISO).
     * The field always refers to the local date-time, ignoring the offset or zone.
     * <p>
     * For date-times, 'RATA_DIE.getFrom()' assumes the same value from
     * midnight until just before the next midnight.
     * When 'RATA_DIE.adjustInto()' is applied to a date-time, the time of day portion remains unaltered.
     * 'RATA_DIE.adjustInto()' and 'RATA_DIE.getFrom()' only apply to {@code Temporal} objects
     * that can be converted into {@link ChronoField#EPOCH_DAY}.
     * An {@link UnsupportedTemporalTypeException} is thrown for any other type of object.
     * <p>
     * In the resolving phase of parsing, a date can be created from a Rata Die field.
     * In {@linkplain ResolverStyle#STRICT strict mode} and {@linkplain ResolverStyle#SMART smart mode}
     * the Rata Die value is validated against the range of valid values.
     * In {@linkplain ResolverStyle#LENIENT lenient mode} no validation occurs.
     * @return TemporalField
     */
    public static function RATA_DIE()
    {
        if (self::$RATA_DIE === null) {
            self::$RATA_DIE = new JulianField("RataDie", CU::DAYS(), CU::FOREVER(), 719163);
        }
        return self::$RATA_DIE;
    }

    /** @var TemporalField */
    private static $RATA_DIE;

    /**
     * Restricted constructor.
     */
    private function __construct()
    {
        throw new \RuntimeException("Not instantiable");
    }
}
