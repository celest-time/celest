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

use Php\Time\Helper\Math;
use const Php\Time\NANOS_PER_SECOND;
use const Php\Time\SECONDS_PER_DAY;
use const Php\Time\SECONDS_PER_HOUR;
use const Php\Time\SECONDS_PER_MINUTE;
use Php\Time\Temporal\ChronoField;
use Php\Time\Temporal\ChronoUnit;
use Php\Time\Temporal\Temporal;
use Php\Time\Temporal\TemporalAmount;
use Php\Time\Temporal\TemporalUnit;

/**
 * A time-based amount of time, such as '34.5 seconds'.
 * <p>
 * This class models a quantity or amount of time in terms of seconds and nanoseconds.
 * It can be accessed using other duration-based units, such as minutes and hours.
 * In addition, the {@link ChronoUnit#DAYS DAYS} unit can be used and is treated as
 * exactly equal to 24 hours, thus ignoring daylight savings effects.
 * See {@link Period} for the date-based equivalent to this class.
 * <p>
 * A physical duration could be of infinite length.
 * For practicality, the duration is stored with constraints similar to {@link Instant}.
 * The duration uses nanosecond resolution with a maximum value of the seconds that can
 * be held in a {@code long}. This is greater than the current estimated age of the universe.
 * <p>
 * The range of a duration requires the storage of a number larger than a {@code long}.
 * To achieve this, the class stores a {@code long} representing seconds and an {@code int}
 * representing nanosecond-of-second, which will always be between 0 and 999,999,999.
 * The model is of a directed duration, meaning that the duration may be negative.
 * <p>
 * The duration is measured in "seconds", but these are not necessarily identical to
 * the scientific "SI second" definition based on atomic clocks.
 * This difference only impacts durations measured near a leap-second and should not affect
 * most applications.
 * See {@link Instant} for a discussion as to the meaning of the second and time-scales.
 *
 * <p>
 * This is a <a href="{@docRoot}/java/lang/doc-files/ValueBased.html">value-based</a>
 * class; use of identity-sensitive operations (including reference equality
 * ({@code ==}), identity hash code, or synchronization) on instances of
 * {@code Duration} may have unpredictable results and should be avoided.
 * The {@code equals} method should be used for comparisons.
 *
 * @implSpec
 * This class is immutable and thread-safe.
 *
 * @since 1.8
 */
final class Duration implements TemporalAmount
{
    /** @var Duration $ZERO */
    private static $ZERO;

    public static function init()
    {
        self::$ZERO = new Duration(0, 0);
    }

    /**
     * Constant for a duration of zero.
     * @return Duration
     */
    public function ZERO()
    {
        return self::$ZERO;
    }

    /**
     * @var int
     */
    private static $BI_NANOS_PER_SECOND = NANOS_PER_SECOND;
    /**
     * The pattern for parsing.
     * TODO
     */
    private static $PATTERN =
        "([-+]?)P(?:([-+]?[0-9]+)D)?" .
        "(T(?:([-+]?[0-9]+)H)?(?:([-+]?[0-9]+)M)?(?:([-+]?[0-9]+)(?:[.,]([0-9]{0,9}))?S)?)?";

    /**
     * The number of seconds in the duration.
     * @var int
     */
    private $seconds;
    /**
     * The number of nanoseconds in the duration, expressed as a fraction of the
     * number of seconds. This is always positive, and never exceeds 999,999,999.
     * @var $nanos
     */
    private $nanos;

    //-----------------------------------------------------------------------
    /**
     * Obtains a {@code Duration} representing a number of standard 24 hour days.
     * <p>
     * The seconds are calculated based on the standard definition of a day,
     * where each day is 86400 seconds which implies a 24 hour day.
     * The nanosecond in second field is set to zero.
     *
     * @param $days int the number of days, positive or negative
     * @return Duration a {@code Duration}, not null
     * @throws ArithmeticException if the input days exceeds the capacity of {@code Duration}
     */
    public static function ofDays($days)
    {
        return self::create(Math::multiplyExact($days, SECONDS_PER_DAY), 0);
    }

    /**
     * Obtains a {@code Duration} representing a number of standard hours.
     * <p>
     * The seconds are calculated based on the standard definition of an hour,
     * where each hour is 3600 seconds.
     * The nanosecond in second field is set to zero.
     *
     * @param $hours int the number of hours, positive or negative
     * @return Duration a {@code Duration}, not null
     * @throws ArithmeticException if the input hours exceeds the capacity of {@code Duration}
     */
    public
    static function ofHours($hours)
    {
        return self::create(Math::multiplyExact($hours, SECONDS_PER_HOUR), 0);
    }

    /**
     * Obtains a {@code Duration} representing a number of standard minutes.
     * <p>
     * The seconds are calculated based on the standard definition of a minute,
     * where each minute is 60 seconds.
     * The nanosecond in second field is set to zero.
     *
     * @param $minutes int the number of minutes, positive or negative
     * @return Duration a {@code Duration}, not null
     * @throws ArithmeticException if the input minutes exceeds the capacity of {@code Duration}
     */
    public
    static function ofMinutes($minutes)
    {
        return self::create(Math::multiplyExact($minutes, SECONDS_PER_MINUTE), 0);
    }

    //-----------------------------------------------------------------------
    /**
     * Obtains a {@code Duration} representing a number of seconds and an
     * adjustment in nanoseconds.
     * <p>
     * This method allows an arbitrary number of nanoseconds to be passed in.
     * The factory will alter the values of the second and nanosecond in order
     * to ensure that the stored nanosecond is in the range 0 to 999,999,999.
     * For example, the following will result in the exactly the same duration:
     * <pre>
     *  Duration.ofSeconds(3, 1);
     *  Duration.ofSeconds(4, -999_999_999);
     *  Duration.ofSeconds(2, 1000_000_001);
     * </pre>
     *
     * @param $seconds int the number of seconds, positive or negative
     * @param $nanoAdjustment int the nanosecond adjustment to the number of seconds, positive or negative
     * @return Duration a {@code Duration}, not null
     * @throws ArithmeticException if the adjustment causes the seconds to exceed the capacity of {@code Duration}
     */
    public static function ofSeconds($seconds, $nanoAdjustment = 0)
    {
        $secs = Math::addExact($seconds, Math::floorDiv($nanoAdjustment, NANOS_PER_SECOND));
        $nos = Math::floorMod($nanoAdjustment, NANOS_PER_SECOND);
        return self::create($secs, $nos);
    }

    //-----------------------------------------------------------------------
    /**
     * Obtains a {@code Duration} representing a number of milliseconds.
     * <p>
     * The seconds and nanoseconds are extracted from the specified milliseconds.
     *
     * @param $millis int the number of milliseconds, positive or negative
     * @return Duration a {@code Duration}, not null
     */
    public static function ofMillis($millis)
    {
        $secs = $millis / 1000;
        $mos = (int)($millis % 1000);
        if ($mos < 0) {
            $mos += 1000;
            $secs--;
        }
        return self::create($secs, $mos * 1000000);
    }

    //-----------------------------------------------------------------------
    /**
     * Obtains a {@code Duration} representing a number of nanoseconds.
     * <p>
     * The seconds and nanoseconds are extracted from the specified nanoseconds.
     *
     * @param $nanos int the number of nanoseconds, positive or negative
     * @return Duration a {@code Duration}, not null
     */
    public static function ofNanos($nanos)
    {
        $secs = $nanos / NANOS_PER_SECOND;
        $nos = $nanos % NANOS_PER_SECOND;
        if ($nos < 0) {
            $nos += NANOS_PER_SECOND;
            $secs--;
        }
        return self::create($secs, $nos);
    }

    //-----------------------------------------------------------------------
    /**
     * Obtains a {@code Duration} representing an amount in the specified unit.
     * <p>
     * The parameters represent the two parts of a phrase like '6 Hours'. For example:
     * <pre>
     *  Duration.of(3, SECONDS);
     *  Duration.of(465, HOURS);
     * </pre>
     * Only a subset of units are accepted by this method.
     * The unit must either have an {@linkplain TemporalUnit#isDurationEstimated() exact duration} or
     * be {@link ChronoUnit#DAYS} which is treated as 24 hours. Other units throw an exception.
     *
     * @param $amount int the amount of the duration, measured in terms of the unit, positive or negative
     * @param $unit TemporalUnit the unit that the duration is measured in, must have an exact duration, not null
     * @return Duration a {@code Duration}, not null
     * @throws DateTimeException if the period unit has an estimated duration
     * @throws ArithmeticException if a numeric overflow occurs
     */
    public static function of($amount, TemporalUnit $unit)
    {
        return self::$ZERO->plus($amount, $unit);
    }

//-----------------------------------------------------------------------
    /**
     * Obtains an instance of {@code Duration} from a temporal amount.
     * <p>
     * This obtains a duration based on the specified amount.
     * A {@code TemporalAmount} represents an  amount of time, which may be
     * date-based or time-based, which this factory extracts to a duration.
     * <p>
     * The conversion loops around the set of units from the amount and uses
     * the {@linkplain TemporalUnit#getDuration() duration} of the unit to
     * calculate the total {@code Duration}.
     * Only a subset of units are accepted by this method. The unit must either
     * have an {@linkplain TemporalUnit#isDurationEstimated() exact duration}
     * or be {@link ChronoUnit#DAYS} which is treated as 24 hours.
     * If any other units are found then an exception is thrown.
     *
     * @param $amount TemporalAmount the temporal amount to convert, not null
     * @return Duration the equivalent duration, not null
     * @throws DateTimeException if unable to convert to a {@code Duration}
     * @throws ArithmeticException if numeric overflow occurs
     */
    public static function from(TemporalAmount $amount)
    {
        $duration = self::$ZERO;
        foreach ($amount->getUnits() as $unit) {
            $duration = $duration->plus($amount->get($unit), $unit);
        }
        return $duration;
    }

    //-----------------------------------------------------------------------
    /**
     * Obtains a {@code Duration} from a text string such as {@code PnDTnHnMn.nS}.
     * <p>
     * This will parse a textual representation of a duration, including the
     * string produced by {@code toString()}. The formats accepted are based
     * on the ISO-8601 duration format {@code PnDTnHnMn.nS} with days
     * considered to be exactly 24 hours.
     * <p>
     * The string starts with an optional sign, denoted by the ASCII negative
     * or positive symbol. If negative, the whole period is negated.
     * The ASCII letter "P" is next in upper or lower case.
     * There are then four sections, each consisting of a number and a suffix.
     * The sections have suffixes in ASCII of "D", "H", "M" and "S" for
     * days, hours, minutes and seconds, accepted in upper or lower case.
     * The suffixes must occur in order. The ASCII letter "T" must occur before
     * the first occurrence, if any, of an hour, minute or second section.
     * At least one of the four sections must be present, and if "T" is present
     * there must be at least one section after the "T".
     * The number part of each section must consist of one or more ASCII digits.
     * The number may be prefixed by the ASCII negative or positive symbol.
     * The number of days, hours and minutes must parse to an {@code long}.
     * The number of seconds must parse to an {@code long} with optional fraction.
     * The decimal point may be either a dot or a comma.
     * The fractional part may have from zero to 9 digits.
     * <p>
     * The leading plus/minus sign, and negative values for other units are
     * not part of the ISO-8601 standard.
     * <p>
     * Examples:
     * <pre>
     *    "PT20.345S" -- parses as "20.345 seconds"
     *    "PT15M"     -- parses as "15 minutes" (where a minute is 60 seconds)
     *    "PT10H"     -- parses as "10 hours" (where an hour is 3600 seconds)
     *    "P2D"       -- parses as "2 days" (where a day is 24 hours or 86400 seconds)
     *    "P2DT3H4M"  -- parses as "2 days, 3 hours and 4 minutes"
     *    "P-6H3M"    -- parses as "-6 hours and +3 minutes"
     *    "-P6H3M"    -- parses as "-6 hours and -3 minutes"
     *    "-P-6H+3M"  -- parses as "+6 hours and -3 minutes"
     * </pre>
     *
     * @param $text string the text to parse, not null
     * @return Duration the parsed duration, not null
     * @throws DateTimeParseException if the text cannot be parsed to a duration
     */
    public static function parse($text)
    {
        $matcher = self::$PATTERN->matcher($text);
        if ($matcher->matches()) {
            // check for letter T but no time sections
            if (("T" == ($matcher->group(3))) == false) {
                $negate = "-" == $matcher->group(1);
                $dayMatch = $matcher->group(2);
                $hourMatch = $matcher->group(4);
                $minuteMatch = $matcher->group(5);
                $secondMatch = $matcher->group(6);
                $fractionMatch = $matcher->group(7);
                if ($dayMatch != null || $hourMatch != null || $minuteMatch != null || $secondMatch != null) {
                    $daysAsSecs = self::parseNumber($text, $dayMatch, SECONDS_PER_DAY, "days");
                    $hoursAsSecs = self::parseNumber($text, $hourMatch, SECONDS_PER_HOUR, "hours");
                    $minsAsSecs = self::parseNumber($text, $minuteMatch, SECONDS_PER_MINUTE, "minutes");
                    $seconds = self::parseNumber($text, $secondMatch, 1, "seconds");
                    $nanos = self::parseFraction($text, $fractionMatch, $seconds < 0 ? -1 : 1);
                    try {
                        return self::createSpecial($negate, $daysAsSecs, $hoursAsSecs, $minsAsSecs, $seconds, $nanos);
                    } catch (ArithmeticException $ex) {
                        throw (new DateTimeParseException("Text cannot be parsed to a Duration: overflow", $text, 0))->initCause($ex);
                    }
                }
            }
        }
        throw new DateTimeParseException("Text cannot be parsed to a Duration", $text, 0);
    }

    private static function parseNumber($text, $parsed, $multiplier, $errorText)
    {
        // regex limits to [-+]?[0-9]+
        if ($parsed == null) {
            return 0;
        }

        try {
            $val = Long::parseLong($parsed);
            return Math::multiplyExact($val, $multiplier);
        } catch (\Exception $ex) {
            throw (new DateTimeParseException("Text cannot be parsed to a Duration: " . $errorText, $text, 0))->initCause(ex);
        }
    }

    private static function parseFraction($text, $parsed, $negate)
    {
        // regex limits to [0-9]{0,9}
        if ($parsed == null || $parsed->length() == 0) {
            return 0;
        }

        try {
            $parsed = substr($parsed . "000000000", 0, 9);
            return Integer::parseInt($parsed) * $negate;
        } catch (\Exception $ex) {
            throw (new DateTimeParseException("Text cannot be parsed to a Duration: fraction", $text, 0))->initCause($ex);
        }
    }

    private static function createSpecial($negate, $daysAsSecs, $hoursAsSecs, $minsAsSecs, $secs, $nanos)
    {
        $seconds = Math::addExact($daysAsSecs, Math::addExact($hoursAsSecs, Math::addExact($minsAsSecs, $secs)));
        if ($negate) {
            return self::ofSeconds($seconds, $nanos)->negated();
        }

        return self::ofSeconds($seconds, $nanos);
    }

//-----------------------------------------------------------------------
    /**
     * Obtains a {@code Duration} representing the duration between two temporal objects.
     * <p>
     * This calculates the duration between two temporal objects. If the objects
     * are of different types, then the duration is calculated based on the type
     * of the first object. For example, if the first argument is a {@code LocalTime}
     * then the second argument is converted to a {@code LocalTime}.
     * <p>
     * The specified temporal objects must support the {@link ChronoUnit#SECONDS SECONDS} unit.
     * For full accuracy, either the {@link ChronoUnit#NANOS NANOS} unit or the
     * {@link ChronoField#NANO_OF_SECOND NANO_OF_SECOND} field should be supported.
     * <p>
     * The result of this method can be a negative period if the end is before the start.
     * To guarantee to obtain a positive duration call {@link #abs()} on the result.
     *
     * @param startInclusive Temporal the start instant, inclusive, not null
     * @param endExclusive Temporal the end instant, exclusive, not null
     * @return Duration a {@code Duration}, not null
     * @throws DateTimeException if the seconds between the temporals cannot be obtained
     * @throws ArithmeticException if the calculation exceeds the capacity of {@code Duration}
     *
     * TODO check
     */
    public function between(Temporal $startInclusive, Temporal $endExclusive)
    {
        try {
            return self::ofNanos($startInclusive->until($endExclusive, ChronoUnit::NANOS()));
        } catch
        (\Exception $ex) {
            $secs = $startInclusive->until($endExclusive, ChronoUnit::SECONDS());
            $nanos = 0;
            try {
                $nanos = $endExclusive->getLong(ChronoField::NANO_OF_SECOND()) - $startInclusive->getLong(ChronoField::NANO_OF_SECOND());
                if ($secs > 0 && $nanos < 0) {
                    $secs++;
                } else if ($secs < 0 && $nanos > 0) {
                    $secs--;
                }
            } catch (DateTimeException $ex2) {
                $nanos = 0;
            }
            return self::ofSeconds($secs, $nanos);
        }
    }

    //-----------------------------------------------------------------------
    /**
     * Obtains an instance of {@code Duration} using seconds and nanoseconds.
     *
     * @param $seconds int the length of the duration in seconds, positive or negative
     * @param $nanoAdjustment int the nanosecond adjustment within the second, from 0 to 999,999,999
     * @return Duration
     */
    private static function create($seconds, $nanoAdjustment)
    {
        if (($seconds | $nanoAdjustment) == 0) {
            return self::$ZERO;
        }

        return new Duration($seconds, $nanoAdjustment);
    }

    /**
     * Constructs an instance of {@code Duration} using seconds and nanoseconds.
     *
     * @param $seconds int the length of the duration in seconds, positive or negative
     * @param $nanos int the nanoseconds within the second, from 0 to 999,999,999
     */
    private function __construct($seconds, $nanos)
    {
        $this->seconds = $seconds;
        $this->nanos = $nanos;
    }

//-----------------------------------------------------------------------
    /**
     * Gets the value of the requested unit.
     * <p>
     * This returns a value for each of the two supported units,
     * {@link ChronoUnit#SECONDS SECONDS} and {@link ChronoUnit#NANOS NANOS}.
     * All other units throw an exception.
     *
     * @param $unit TemporalUnit the {@code TemporalUnit} for which to return the value
     * @return int the long value of the unit
     * @throws DateTimeException if the unit is not supported
     * @throws UnsupportedTemporalTypeException if the unit is not supported
     */
    public function get(TemporalUnit $unit)
    {
        if ($unit == ChronoUnit::SECONDS()) {
            return $this->seconds;
        } else
            if ($unit == ChronoUnit::NANOS()) {
                return $this->nanos;
            } else {
                throw new UnsupportedTemporalTypeException("Unsupported unit: " . $unit);
            }
    }

    /**
     * Gets the set of units supported by this duration.
     * <p>
     * The supported units are {@link ChronoUnit#SECONDS SECONDS},
     * and {@link ChronoUnit#NANOS NANOS}.
     * They are returned in the order seconds, nanos.
     * <p>
     * This set can be used in conjunction with {@link #get(TemporalUnit)}
     * to access the entire state of the duration.
     *
     * @return TemporalUnit[] a list containing the seconds and nanos units, not null
     */
    public function getUnits()
    {
        return [ChronoUnit::SECONDS(), ChronoUnit::NANOS()];
    }

    /**
     * Private class to delay initialization of this list until needed.
     * The circular dependency between Duration and ChronoUnit prevents
     * the simple initialization in Duration.
     */
//private
//static class DurationUnits
//{
//static final List<TemporalUnit> UNITS =
//Collections.unmodifiableList(Arrays.<TemporalUnit>asList(SECONDS, NANOS));
//}

    //-----------------------------------------------------------------------
    /**
     * Checks if this duration is zero length.
     * <p>
     * A {@code Duration} represents a directed distance between two points on
     * the time-line and can therefore be positive, zero or negative.
     * This method checks whether the length is zero.
     *
     * @return bool true if this duration has a total length equal to zero
     */
    public function isZero()
    {
        return ($this->seconds | $this->nanos) == 0;
    }

    /**
     * Checks if this duration is negative, excluding zero.
     * <p>
     * A {@code Duration} represents a directed distance between two points on
     * the time-line and can therefore be positive, zero or negative.
     * This method checks whether the length is less than zero.
     *
     * @return bool true if this duration has a total length less than zero
     */
    public function isNegative()
    {
        return $this->seconds < 0;
    }

    //-----------------------------------------------------------------------
    /**
     * Gets the number of seconds in this duration.
     * <p>
     * The length of the duration is stored using two fields - seconds and nanoseconds.
     * The nanoseconds part is a value from 0 to 999,999,999 that is an adjustment to
     * the length in seconds.
     * The total duration is defined by calling this method and {@link #getNano()}.
     * <p>
     * A {@code Duration} represents a directed distance between two points on the time-line.
     * A negative duration is expressed by the negative sign of the seconds part.
     * A duration of -1 nanosecond is stored as -1 seconds plus 999,999,999 nanoseconds.
     *
     * @return int the whole seconds part of the length of the duration, positive or negative
     */
    public function getSeconds()
    {
        return $this->seconds;
    }

    /**
     * Gets the number of nanoseconds within the second in this duration.
     * <p>
     * The length of the duration is stored using two fields - seconds and nanoseconds.
     * The nanoseconds part is a value from 0 to 999,999,999 that is an adjustment to
     * the length in seconds.
     * The total duration is defined by calling this method and {@link #getSeconds()}.
     * <p>
     * A {@code Duration} represents a directed distance between two points on the time-line.
     * A negative duration is expressed by the negative sign of the seconds part.
     * A duration of -1 nanosecond is stored as -1 seconds plus 999,999,999 nanoseconds.
     *
     * @return int the nanoseconds within the second part of the length of the duration, from 0 to 999,999,999
     */
    public function getNano()
    {
        return $this->nanos;
    }

    //-----------------------------------------------------------------------
    /**
     * Returns a copy of this duration with the specified amount of seconds.
     * <p>
     * This returns a duration with the specified seconds, retaining the
     * nano-of-second part of this duration.
     * <p>
     * This instance is immutable and unaffected by this method call.
     *
     * @param int $seconds the seconds to represent, may be negative
     * @return Duration a {@code Duration} based on this period with the requested seconds, not null
     */
    public function withSeconds($seconds)
    {
        return self::create($seconds, $this->nanos);
    }

    /**
     * Returns a copy of this duration with the specified nano-of-second.
     * <p>
     * This returns a duration with the specified nano-of-second, retaining the
     * seconds part of this duration.
     * <p>
     * This instance is immutable and unaffected by this method call.
     *
     * @param $nanoOfSecond int the nano-of-second to represent, from 0 to 999,999,999
     * @return Duration a {@code Duration} based on this period with the requested nano-of-second, not null
     * @throws DateTimeException if the nano-of-second is invalid
     */
    public
    function withNanos($nanoOfSecond)
    {
        ChronoField::SECOND_OF_DAY()->checkValidIntValue($nanoOfSecond);
        return self::create($this->seconds, $nanoOfSecond);
    }

    //-----------------------------------------------------------------------
    /**
     * Returns a copy of this duration with the specified duration added.
     * <p>
     * This instance is immutable and unaffected by this method call.
     *
     * @param $duration Duration the duration to add, positive or negative, not null
     * @return Duration a {@code Duration} based on this duration with the specified duration added, not null
     * @throws ArithmeticException if numeric overflow occurs
     */
    public function plus(Duration $duration)
    {
        return $this->plus($duration->getSeconds(), $duration->getNano());
    }

    /**
     * Returns a copy of this duration with the specified duration added.
     * <p>
     * The duration amount is measured in terms of the specified unit.
     * Only a subset of units are accepted by this method.
     * The unit must either have an {@linkplain TemporalUnit#isDurationEstimated() exact duration} or
     * be {@link ChronoUnit#DAYS} which is treated as 24 hours. Other units throw an exception.
     * <p>
     * This instance is immutable and unaffected by this method call.
     *
     * @param $amountToAdd int  the amount to add, measured in terms of the unit, positive or negative
     * @param $unit TemporalUnit  the unit that the amount is measured in, must have an exact duration, not null
     * @return Duration a {@code Duration} based on this duration with the specified duration added, not null
     * @throws UnsupportedTemporalTypeException if the unit is not supported
     * @throws ArithmeticException if numeric overflow occurs
     */
    public function plus($amountToAdd, TemporalUnit $unit)
    {
        if ($unit == ChronoUnit::DAYS()) {
            return $this->plus(Math::multiplyExact($amountToAdd, SECONDS_PER_DAY), 0);
        }

        if ($unit->isDurationEstimated()) {
            throw new UnsupportedTemporalTypeException("Unit must not have an estimated duration");
        }
        if ($amountToAdd == 0) {
            return $this;
        }
        if ($unit instanceof ChronoUnit) {
            switch ($unit) {
                case ChronoUnit::NANOS():
                    return $this->plusNanos($amountToAdd);
                case ChronoUnit::MICROS():
                    return $this->plusSeconds(($amountToAdd / (1000000 * 1000)) * 1000)->plusNanos(($amountToAdd % (1000000 * 1000)) * 1000);
                case ChronoUnit::MILLIS():
                    return $this->plusMillis($amountToAdd);
                case ChronoUnit::SECONDS():
                    return $this->plusSeconds($amountToAdd);
            }
            return $this->plusSeconds(Math::multiplyExact($unit->getDuration()->seconds, $amountToAdd));
        }
        $duration = $unit->getDuration()->multipliedBy($amountToAdd);
        return $this->plusSeconds($duration->getSeconds())->plusNanos($duration->getNano());
    }

    //-----------------------------------------------------------------------
    /**
     * Returns a copy of this duration with the specified duration in standard 24 hour days added.
     * <p>
     * The number of days is multiplied by 86400 to obtain the number of seconds to add.
     * This is based on the standard definition of a day as 24 hours.
     * <p>
     * This instance is immutable and unaffected by this method call.
     *
     * @param $daysToAdd int  the days to add, positive or negative
     * @return Duration a {@code Duration} based on this duration with the specified days added, not null
     * @throws ArithmeticException if numeric overflow occurs
     */
    public function plusDays($daysToAdd)
    {
        return $this->plus(Math::multiplyExact($daysToAdd, SECONDS_PER_DAY), 0);
    }

    /**
     * Returns a copy of this duration with the specified duration in hours added.
     * <p>
     * This instance is immutable and unaffected by this method call.
     *
     * @param $hoursToAdd int the hours to add, positive or negative
     * @return Duration a {@code Duration} based on this duration with the specified hours added, not null
     * @throws ArithmeticException if numeric overflow occurs
     */
    public
    function plusHours($hoursToAdd)
    {
        return $this->plus(Math::multiplyExact($hoursToAdd, SECONDS_PER_HOUR), 0);
    }

    /**
     * Returns a copy of this duration with the specified duration in minutes added.
     * <p>
     * This instance is immutable and unaffected by this method call.
     *
     * @param $minutesToAdd int the minutes to add, positive or negative
     * @return Duration a {@code Duration} based on this duration with the specified minutes added, not null
     * @throws ArithmeticException if numeric overflow occurs
     */
    public function plusMinutes($minutesToAdd)
    {
        return $this->plus(Math::multiplyExact($minutesToAdd, SECONDS_PER_MINUTE), 0);
    }

    /**
     * Returns a copy of this duration with the specified duration in seconds added.
     * <p>
     * This instance is immutable and unaffected by this method call.
     *
     * @param $secondsToAdd int the seconds to add, positive or negative
     * @return Duration a {@code Duration} based on this duration with the specified seconds added, not null
     * @throws ArithmeticException if numeric overflow occurs
     */
    public function plusSeconds($secondsToAdd)
    {
        return $this->plus($secondsToAdd, 0);
    }

    /**
     * Returns a copy of this duration with the specified duration in milliseconds added.
     * <p>
     * This instance is immutable and unaffected by this method call.
     *
     * @param $millisToAdd int the milliseconds to add, positive or negative
     * @return Duration a {@code Duration} based on this duration with the specified milliseconds added, not null
     * @throws ArithmeticException if numeric overflow occurs
     */
    public function plusMillis($millisToAdd)
    {
        return $this->plus($millisToAdd / 1000, ($millisToAdd % 1000) * 1000000);
    }

    /**
     * Returns a copy of this duration with the specified duration in nanoseconds added.
     * <p>
     * This instance is immutable and unaffected by this method call.
     *
     * @param $nanosToAdd int the nanoseconds to add, positive or negative
     * @return Duration a {@code Duration} based on this duration with the specified nanoseconds added, not null
     * @throws ArithmeticException if numeric overflow occurs
     */
    public function plusNanos($nanosToAdd)
    {
        return $this->plus(0, $nanosToAdd);
    }

    /**
     * Returns a copy of this duration with the specified duration added.
     * <p>
     * This instance is immutable and unaffected by this method call.
     *
     * @param $secondsToAdd int the seconds to add, positive or negative
     * @param $nanosToAdd int the nanos to add, positive or negative
     * @return Duration a {@code Duration} based on this duration with the specified seconds added, not null
     * @throws ArithmeticException if numeric overflow occurs
     */
    private function plus($secondsToAdd, $nanosToAdd)
    {
        if (($secondsToAdd | $nanosToAdd) == 0) {
            return $this;
        }

        $epochSec = Math::addExact($this->seconds, $secondsToAdd);
        $epochSec = Math::addExact($epochSec, $nanosToAdd / NANOS_PER_SECOND);
        $nanosToAdd = $nanosToAdd % NANOS_PER_SECOND;
        $nanoAdjustment = $this->nanos + $nanosToAdd;  // safe int+NANOS_PER_SECOND
        return self::ofSeconds($epochSec, $nanoAdjustment);
    }

    //-----------------------------------------------------------------------
    /**
     * Returns a copy of this duration with the specified duration subtracted.
     * <p>
     * This instance is immutable and unaffected by this method call.
     *
     * @param duration Duration the duration to subtract, positive or negative, not null
     * @return Duration a {@code Duration} based on this duration with the specified duration subtracted, not null
     * @throws ArithmeticException if numeric overflow occurs
     */
    public function minus($duration)
    {
        $secsToSubtract = $duration->getSeconds();
        $nanosToSubtract = $duration->getNano();
        if ($secsToSubtract == Long::MIN_VALUE) {
            return $this->plus(Long::MAX_VALUE, -$nanosToSubtract)->plus(1, 0);
        }

        return $this->plus(-$secsToSubtract, -$nanosToSubtract);
    }

    /**
     * Returns a copy of this duration with the specified duration subtracted.
     * <p>
     * The duration amount is measured in terms of the specified unit.
     * Only a subset of units are accepted by this method.
     * The unit must either have an {@linkplain TemporalUnit#isDurationEstimated() exact duration} or
     * be {@link ChronoUnit#DAYS} which is treated as 24 hours. Other units throw an exception.
     * <p>
     * This instance is immutable and unaffected by this method call.
     *
     * @param $amountToSubtract int the amount to subtract, measured in terms of the unit, positive or negative
     * @param $unit TemporalUnit the unit that the amount is measured in, must have an exact duration, not null
     * @return Duration a {@code Duration} based on this duration with the specified duration subtracted, not null
     * @throws ArithmeticException if numeric overflow occurs
     */
    public function minus($amountToSubtract, TemporalUnit $unit)
    {
        return ($amountToSubtract == Long::MIN_VALUE ? plus(Long::MAX_VALUE, $unit)->plus(1, $unit) : $this->plus(-$amountToSubtract, $unit));
    }

//-----------------------------------------------------------------------
    /**
     * Returns a copy of this duration with the specified duration in standard 24 hour days subtracted.
     * <p>
     * The number of days is multiplied by 86400 to obtain the number of seconds to subtract.
     * This is based on the standard definition of a day as 24 hours.
     * <p>
     * This instance is immutable and unaffected by this method call.
     *
     * @param $daysToSubtract int the days to subtract, positive or negative
     * @return Duration a {@code Duration} based on this duration with the specified days subtracted, not null
     * @throws ArithmeticException if numeric overflow occurs
     */
    public
    function minusDays($daysToSubtract)
    {
        return ($daysToSubtract == Long::MIN_VALUE ? $this->plusDays(Long::MAX_VALUE)->plusDays(1) : $this->plusDays(-$daysToSubtract));
    }

    /**
     * Returns a copy of this duration with the specified duration in hours subtracted.
     * <p>
     * The number of hours is multiplied by 3600 to obtain the number of seconds to subtract.
     * <p>
     * This instance is immutable and unaffected by this method call.
     *
     * @param $hoursToSubtract int the hours to subtract, positive or negative
     * @return Duration a {@code Duration} based on this duration with the specified hours subtracted, not null
     * @throws ArithmeticException if numeric overflow occurs
     */
    public
    function minusHours($hoursToSubtract)
    {
        return (hoursToSubtract == Long::MIN_VALUE ? $this->plusHours(Long::MAX_VALUE)->plusHours(1) : $this->plusHours(-hoursToSubtract));
    }

    /**
     * Returns a copy of this duration with the specified duration in minutes subtracted.
     * <p>
     * The number of hours is multiplied by 60 to obtain the number of seconds to subtract.
     * <p>
     * This instance is immutable and unaffected by this method call.
     *
     * @param $minutesToSubtract int the minutes to subtract, positive or negative
     * @return Duration a {@code Duration} based on this duration with the specified minutes subtracted, not null
     * @throws ArithmeticException if numeric overflow occurs
     */
    public
    function minusMinutes($minutesToSubtract)
    {
        return ($minutesToSubtract == Long::MIN_VALUE ? $this->plusMinutes(Long::MAX_VALUE)->plusMinutes(1) : $this->plusMinutes(-$minutesToSubtract));
    }

    /**
     * Returns a copy of this duration with the specified duration in seconds subtracted.
     * <p>
     * This instance is immutable and unaffected by this method call.
     *
     * @param secondsToSubtract int the seconds to subtract, positive or negative
     * @return Duration a {@code Duration} based on this duration with the specified seconds subtracted, not null
     * @throws ArithmeticException if numeric overflow occurs
     */
    public function minusSeconds($secondsToSubtract)
    {
        return ($secondsToSubtract == Long::MIN_VALUE ? $this->plusSeconds(Long::MAX_VALUE)->plusSeconds(1) : $this->plusSeconds(-$secondsToSubtract));
    }

    /**
     * Returns a copy of this duration with the specified duration in milliseconds subtracted.
     * <p>
     * This instance is immutable and unaffected by this method call.
     *
     * @param millisToSubtract int the milliseconds to subtract, positive or negative
     * @return Duration a {@code Duration} based on this duration with the specified milliseconds subtracted, not null
     * @throws ArithmeticException if numeric overflow occurs
     */
    public function minusMillis($millisToSubtract)
    {
        return ($millisToSubtract == Long::MIN_VALUE ? $this->plusMillis(Long::MAX_VALUE)->plusMillis(1) : $this->plusMillis(-$millisToSubtract));
    }

    /**
     * Returns a copy of this duration with the specified duration in nanoseconds subtracted.
     * <p>
     * This instance is immutable and unaffected by this method call.
     *
     * @param nanosToSubtract int the nanoseconds to subtract, positive or negative
     * @return Duration a {@code Duration} based on this duration with the specified nanoseconds subtracted, not null
     * @throws ArithmeticException if numeric overflow occurs
     */
    public function minusNanos($nanosToSubtract)
    {
        return ($nanosToSubtract == Long::MIN_VALUE ? $this->plusNanos(Long::MAX_VALUE)->plusNanos(1) : $this->plusNanos(-$nanosToSubtract));
    }

    //-----------------------------------------------------------------------
    /**
     * Returns a copy of this duration multiplied by the scalar.
     * <p>
     * This instance is immutable and unaffected by this method call.
     *
     * @param $multiplicand int the value to multiply the duration by, positive or negative
     * @return Duration a {@code Duration} based on this duration multiplied by the specified scalar, not null
     * @throws ArithmeticException if numeric overflow occurs
     */
    public function multipliedBy($multiplicand)
    {
        if ($multiplicand == 0) {
            return self::$ZERO;
        }
        if ($multiplicand == 1) {
            return $this;
        }
        return self::createBC(bcmul($this->toSeconds(), $multiplicand));
    }

    /**
     * Returns a copy of this duration divided by the specified value.
     * <p>
     * This instance is immutable and unaffected by this method call.
     *
     * @param $divisor int the value to divide the duration by, positive or negative, not zero
     * @return Duration a {@code Duration} based on this duration divided by the specified divisor, not null
     * @throws ArithmeticException if the divisor is zero or if numeric overflow occurs
     */
    public function dividedBy($divisor)
    {
        if ($divisor == 0) {
            throw new ArithmeticException("Cannot divide by zero");
        }
        if ($divisor == 1) {
            return $this;
        }
        return self::createBC(gmp_div($this->toSeconds(), $divisor, GMP_ROUND_ZERO));
    }

    /**
     * Converts this duration to the total length in seconds and
     * fractional nanoseconds expressed as a {@code BigDecimal}.
     *
     * @return \GMP the total length of the duration in seconds, with a scale of 9, not null
     */
    private function toSeconds()
    {
        return gmp_add($this->seconds, $this->nanos);
    }

    /**
     * Creates an instance of {@code Duration} from a number of seconds.
     *
     * @param $seconds \GMP the number of seconds, up to scale 9, positive or negative
     * @return Duration a {@code Duration}, not null
     * @throws ArithmeticException if numeric overflow occurs
     * TODO Fix big init arithmetic
     */
    private
    static function BC(\GMP $seconds)
    {
        $nanos = $seconds->movePointRight(9)->toBigIntegerExact();
        $divRem = gmp_div_qr($nanos, self::$BI_NANOS_PER_SECOND);
        if ($divRem[0]->bitLength() > 63) {
            throw new ArithmeticException("Exceeds capacity of Duration: " . $nanos);
        }

        return self::ofSeconds(gmp_intval($divRem[0]), gmp_intval($divRem[1]));
    }

    //-----------------------------------------------------------------------
    /**
     * Returns a copy of this duration with the length negated.
     * <p>
     * This method swaps the sign of the total length of this duration.
     * For example, {@code PT1.3S} will be returned as {@code PT-1.3S}.
     * <p>
     * This instance is immutable and unaffected by this method call.
     *
     * @return Duration a {@code Duration} based on this duration with the amount negated, not null
     * @throws ArithmeticException if numeric overflow occurs
     */
    public function negated()
    {
        return $this->multipliedBy(-1);
    }

    /**
     * Returns a copy of this duration with a positive length.
     * <p>
     * This method returns a positive duration by effectively removing the sign from any negative total length.
     * For example, {@code PT-1.3S} will be returned as {@code PT1.3S}.
     * <p>
     * This instance is immutable and unaffected by this method call.
     *
     * @return Duration a {@code Duration} based on this duration with an absolute length, not null
     * @throws ArithmeticException if numeric overflow occurs
     */
    public function abs()
    {
        return $this->isNegative() ? $this->negated() : $this;
    }

    //-------------------------------------------------------------------------
    /**
     * Adds this duration to the specified temporal object.
     * <p>
     * This returns a temporal object of the same observable type as the input
     * with this duration added.
     * <p>
     * In most cases, it is clearer to reverse the calling pattern by using
     * {@link Temporal#plus(TemporalAmount)}.
     * <pre>
     *   // these two lines are equivalent, but the second approach is recommended
     *   dateTime = thisDuration.addTo(dateTime);
     *   dateTime = dateTime.plus(thisDuration);
     * </pre>
     * <p>
     * The calculation will add the seconds, then nanos.
     * Only non-zero amounts will be added.
     * <p>
     * This instance is immutable and unaffected by this method call.
     *
     * @param $temporal Temporal the temporal object to adjust, not null
     * @return Temporal an object of the same type with the adjustment made, not null
     * @throws DateTimeException if unable to add
     * @throws ArithmeticException if numeric overflow occurs
     */
    public function addTo(Temporal $temporal)
    {
        if ($this->seconds != 0) {
            $temporal = $temporal->plus($this->seconds, ChronoUnit::SECONDS());
        }

        if ($this->nanos != 0) {
            $temporal = $temporal->plus($this->nanos, ChronoUnit::NANOS());
        }
        return $temporal;
    }

    /**
     * Subtracts this duration from the specified temporal object.
     * <p>
     * This returns a temporal object of the same observable type as the input
     * with this duration subtracted.
     * <p>
     * In most cases, it is clearer to reverse the calling pattern by using
     * {@link Temporal#minus(TemporalAmount)}.
     * <pre>
     *   // these two lines are equivalent, but the second approach is recommended
     *   dateTime = thisDuration.subtractFrom(dateTime);
     *   dateTime = dateTime.minus(thisDuration);
     * </pre>
     * <p>
     * The calculation will subtract the seconds, then nanos.
     * Only non-zero amounts will be added.
     * <p>
     * This instance is immutable and unaffected by this method call.
     *
     * @param $temporal Temporal the temporal object to adjust, not null
     * @return Temporal an object of the same type with the adjustment made, not null
     * @throws DateTimeException if unable to subtract
     * @throws ArithmeticException if numeric overflow occurs
     */
    public function subtractFrom(Temporal $temporal)
    {
        if ($this->seconds != 0) {
            $temporal = $temporal->minus($this->seconds, ChronoUnit::SECONDS());
        }

        if ($this->nanos != 0) {
            $temporal = $temporal->minus($this->nanos, ChronoUnit::NANOS());
        }
        return $temporal;
    }

//-----------------------------------------------------------------------
    /**
     * Gets the number of days in this duration.
     * <p>
     * This returns the total number of days in the duration by dividing the
     * number of seconds by 86400.
     * This is based on the standard definition of a day as 24 hours.
     * <p>
     * This instance is immutable and unaffected by this method call.
     *
     * @return int the number of days in the duration, may be negative
     */
    public function toDays()
    {
        return $this->seconds / SECONDS_PER_DAY;
    }

    /**
     * Gets the number of hours in this duration.
     * <p>
     * This returns the total number of hours in the duration by dividing the
     * number of seconds by 3600.
     * <p>
     * This instance is immutable and unaffected by this method call.
     *
     * @return int the number of hours in the duration, may be negative
     */
    public function toHours()
    {
        return $this->seconds / SECONDS_PER_HOUR;
    }

    /**
     * Gets the number of minutes in this duration.
     * <p>
     * This returns the total number of minutes in the duration by dividing the
     * number of seconds by 60.
     * <p>
     * This instance is immutable and unaffected by this method call.
     *
     * @return int the number of minutes in the duration, may be negative
     */
    public function toMinutes()
    {
        return $this->seconds / SECONDS_PER_MINUTE;
    }

    /**
     * Converts this duration to the total length in milliseconds.
     * <p>
     * If this duration is too large to fit in a {@code long} milliseconds, then an
     * exception is thrown.
     * <p>
     * If this duration has greater than millisecond precision, then the conversion
     * will drop any excess precision information as though the amount in nanoseconds
     * was subject to integer division by one million.
     *
     * @return int the total length of the duration in milliseconds
     * @throws ArithmeticException if numeric overflow occurs
     */
    public function toMillis()
    {
        $millis = Math::multiplyExact($this->seconds, 1000);
        $millis = Math::addExact($millis, $this->nanos / 1000000);
        return $millis;
    }

    /**
     * Converts this duration to the total length in nanoseconds expressed as a {@code long}.
     * <p>
     * If this duration is too large to fit in a {@code long} nanoseconds, then an
     * exception is thrown.
     *
     * @return int the total length of the duration in nanoseconds
     * @throws ArithmeticException if numeric overflow occurs
     */
    public function  toNanos()
    {
        $totalNanos = Math::multiplyExact($this->seconds, NANOS_PER_SECOND);
        $totalNanos = Math::addExact($totalNanos, $this->nanos);
        return $totalNanos;
    }

//-----------------------------------------------------------------------
    /**
     * Compares this duration to the specified {@code Duration}.
     * <p>
     * The comparison is based on the total length of the durations.
     * It is "consistent with equals", as defined by {@link Comparable}.
     *
     * @param $otherDuration Duration the other duration to compare to, not null
     * @return int the comparator value, negative if less, positive if greater
     */
    public function compareTo(Duration $otherDuration)
    {
        $cmp = Long::compare($this->seconds, $otherDuration->seconds);
        if ($cmp != 0) {
            return $cmp;
        }

        return $this->nanos - $otherDuration->nanos;
    }

    //-----------------------------------------------------------------------
    /**
     * Checks if this duration is equal to the specified {@code Duration}.
     * <p>
     * The comparison is based on the total length of the durations.
     *
     * @param $otherDuration Duration the other duration, null returns false
     * @return bool true if the other duration is equal to this one
     */
    public function equals($otherDuration)
    {
        if ($this == $otherDuration) {
            return true;
        }

        if ($otherDuration instanceof Duration) {
            return $this->seconds == $otherDuration->seconds &&
            $this->nanos == $otherDuration->nanos;
        }
        return false;
    }


//-----------------------------------------------------------------------
    /**
     * A string representation of this duration using ISO-8601 seconds
     * based representation, such as {@code PT8H6M12.345S}.
     * <p>
     * The format of the returned string will be {@code PTnHnMnS}, where n is
     * the relevant hours, minutes or seconds part of the duration.
     * Any fractional seconds are placed after a decimal point i the seconds section.
     * If a section has a zero value, it is omitted.
     * The hours, minutes and seconds will all have the same sign.
     * <p>
     * Examples:
     * <pre>
     *    "20.345 seconds"                 -- "PT20.345S
     *    "15 minutes" (15 * 60 seconds)   -- "PT15M"
     *    "10 hours" (10 * 3600 seconds)   -- "PT10H"
     *    "2 days" (2 * 86400 seconds)     -- "PT48H"
     * </pre>
     * Note that multiples of 24 hours are not output as days to avoid confusion
     * with {@code Period}.
     *
     * @return string an ISO-8601 representation of this duration, not null
     */
    public function __toString()
    {
        if ($this == self::$ZERO) {
            return "PT0S";
        }
        $hours = $this->seconds / SECONDS_PER_HOUR;
        $minutes = (int)(($this->seconds % SECONDS_PER_HOUR) / SECONDS_PER_MINUTE);
        $secs = (int)($this->seconds % SECONDS_PER_MINUTE);
        $buf = '';
        $buf .= "PT";
        if ($hours != 0) {
            $buf .= $hours . 'H';
        }
        if ($minutes != 0) {
            $buf .= $minutes . 'M';
        }
        if ($secs == 0 && $this->nanos == 0 && strlen($buf) > 2) {
            return $buf;
        }
        if ($secs < 0 && $this->nanos > 0) {
            if ($secs == -1) {
                $buf .= "-0";
            } else {
                $buf .= ($secs + 1);
            }
        } else {
            $buf .= $secs;
        }
        if ($this->nanos > 0) {
            $pos = strlen($buf);
            if ($secs < 0) {
                $buf .= 2 * NANOS_PER_SECOND - $this->nanos;
            } else {
                $buf .= $this->nanos + NANOS_PER_SECOND;
            }
            rtrim($buf, "0");
            $buf[$pos] = '.';
        }
        $buf .= 'S';
        return $buf;
    }
}

Duration::init();