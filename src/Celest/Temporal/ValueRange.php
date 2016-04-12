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
namespace Celest\Temporal;

use Celest\DateTimeException;
use Celest\Helper\Integer;
use Celest\IllegalArgumentException;

/**
 * The range of valid values for a date-time field.
 * <p>
 * All {@link TemporalField} instances have a valid range of values.
 * For example, the ISO day-of-month runs from 1 to somewhere between 28 and 31.
 * This class captures that valid range.
 * <p>
 * It is important to be aware of the limitations of this class.
 * Only the minimum and maximum values are provided.
 * It is possible for there to be invalid values within the outer range.
 * For example, a weird field may have valid values of 1, 2, 4, 6, 7, thus
 * have a range of '1 - 7', despite that fact that values 3 and 5 are invalid.
 * <p>
 * Instances of this class are not tied to a specific field.
 *
 * @implSpec
 * This class is immutable and thread-safe.
 *
 * @since 1.8
 */
final class ValueRange
{
    /**
     * The smallest minimum value.
     * @var int
     */
    private $minSmallest;
    /**
     * The largest minimum value.
     * @var int
     */
    private $minLargest;
    /**
     * The smallest maximum value.
     * @var int
     */
    private $maxSmallest;
    /**
     * The largest maximum value.
     * @var int
     */
    private $maxLargest;

    /**
     * Obtains a fixed value range.
     * <p>
     * This factory obtains a range where the minimum and maximum values are fixed.
     * For example, the ISO month-of-year always runs from 1 to 12.
     *
     * @param int $min the minimum value
     * @param int $max the maximum value
     * @return ValueRange the ValueRange for min, max, not null
     * @throws IllegalArgumentException if the minimum is greater than the maximum
     */
    public static function of($min, $max)
    {
        if ($min > $max) {
            throw new IllegalArgumentException("Minimum value must be less than maximum value");
        }

        return new ValueRange($min, $min, $max, $max);
    }

    /**
     * Obtains a variable value range.
     * <p>
     * This factory obtains a range where the minimum value is fixed and the maximum value may vary.
     * For example, the ISO day-of-month always starts at 1, but ends between 28 and 31.
     *
     * @param int $min the minimum value
     * @param int $maxSmallest the smallest maximum value
     * @param int $maxLargest the largest maximum value
     * @return ValueRange the ValueRange for min, smallest max, largest max, not null
     * @throws IllegalArgumentException if
     *     the minimum is greater than the smallest maximum,
     *  or the smallest maximum is greater than the largest maximum
     */
    public static function ofVariable($min, $maxSmallest, $maxLargest)
    {
        return self::ofFullyVariable($min, $min, $maxSmallest, $maxLargest);
    }

    /**
     * Obtains a fully variable value range.
     * <p>
     * This factory obtains a range where both the minimum and maximum value may vary.
     *
     * @param int $minSmallest the smallest minimum value
     * @param int $minLargest the largest minimum value
     * @param int $maxSmallest the smallest maximum value
     * @param int $maxLargest the largest maximum value
     * @return ValueRange the ValueRange for smallest min, largest min, smallest max, largest max, not null
     * @throws IllegalArgumentException if
     *     the smallest minimum is greater than the smallest maximum,
     *  or the smallest maximum is greater than the largest maximum
     *  or the largest minimum is greater than the largest maximum
     */
    public static function ofFullyVariable($minSmallest, $minLargest, $maxSmallest, $maxLargest)
    {
        if ($minSmallest > $minLargest) {
            throw new IllegalArgumentException("Smallest minimum value must be less than largest minimum value");
        }

        if ($maxSmallest > $maxLargest) {
            throw new IllegalArgumentException("Smallest maximum value must be less than largest maximum value");
        }
        if ($minLargest > $maxLargest) {
            throw new IllegalArgumentException("Minimum value must be less than maximum value");
        }
        return new ValueRange($minSmallest, $minLargest, $maxSmallest, $maxLargest);
    }

    /**
     * Restrictive constructor.
     *
     * @param int $minSmallest the smallest minimum value
     * @param int $minLargest the largest minimum value
     * @param int $maxSmallest the smallest minimum value
     * @param int $maxLargest the largest minimum value
     */
    private function __construct($minSmallest, $minLargest, $maxSmallest, $maxLargest)
    {
        $this->minSmallest = $minSmallest;
        $this->minLargest = $minLargest;
        $this->maxSmallest = $maxSmallest;
        $this->maxLargest = $maxLargest;
    }

//-----------------------------------------------------------------------
    /**
     * Is the value range fixed and fully known.
     * <p>
     * For example, the ISO day-of-month runs from 1 to between 28 and 31.
     * Since there is uncertainty about the maximum value, the range is not fixed.
     * However, for the month of January, the range is always 1 to 31, thus it is fixed.
     *
     * @return bool true if the set of values is fixed
     */
    public function isFixed()
    {
        return $this->minSmallest === $this->minLargest && $this->maxSmallest === $this->maxLargest;
    }

//-----------------------------------------------------------------------
    /**
     * Gets the minimum value that the field can take.
     * <p>
     * For example, the ISO day-of-month always starts at 1.
     * The minimum is therefore 1.
     *
     * @return int the minimum value for this field
     */
    public function getMinimum()
    {
        return $this->minSmallest;
    }

    /**
     * Gets the largest possible minimum value that the field can take.
     * <p>
     * For example, the ISO day-of-month always starts at 1.
     * The largest minimum is therefore 1.
     *
     * @return int the largest possible minimum value for this field
     */
    public function getLargestMinimum()
    {
        return $this->minLargest;
    }

    /**
     * Gets the smallest possible maximum value that the field can take.
     * <p>
     * For example, the ISO day-of-month runs to between 28 and 31 days.
     * The smallest maximum is therefore 28.
     *
     * @return int the smallest possible maximum value for this field
     */
    public function getSmallestMaximum()
    {
        return $this->maxSmallest;
    }

    /**
     * Gets the maximum value that the field can take.
     * <p>
     * For example, the ISO day-of-month runs to between 28 and 31 days.
     * The maximum is therefore 31.
     *
     * @return int the maximum value for this field
     */
    public function getMaximum()
    {
        return $this->maxLargest;
    }

    //-----------------------------------------------------------------------
    /**
     * Checks if all values in the range fit in an {@code int}.
     * <p>
     * This checks that all valid values are within the bounds of an {@code int}.
     * <p>
     * For example, the ISO month-of-year has values from 1 to 12, which fits in an {@code int}.
     * By comparison, ISO nano-of-day runs from 1 to 86,400,000,000,000 which does not fit in an {@code int}.
     * <p>
     * This implementation uses {@link #getMinimum()} and {@link #getMaximum()}.
     *
     * @return bool true if a valid value always fits in an {@code int}
     */
    public function isIntValue()
    {
        return $this->getMinimum() >= Integer::MIN_VALUE && $this->getMaximum() <= Integer::MAX_VALUE;
    }

    /**
     * Checks if the value is within the valid range.
     * <p>
     * This checks that the value is within the stored range of values.
     *
     * @param int $value the value to check
     * @return bool true if the value is valid
     */
    public function isValidValue($value)
    {
        return ($value >= $this->getMinimum() && $value <= $this->getMaximum());
    }

    /**
     * Checks if the value is within the valid range and that all values
     * in the range fit in an {@code int}.
     * <p>
     * This method combines {@link #isIntValue()} and {@link #isValidValue(long)}.
     *
     * @param int $value the value to check
     * @return true if the value is valid and fits in an {@code int}
     */
    public function isValidIntValue($value)
    {
        return $this->isIntValue() && $this->isValidValue($value);
    }

    /**
     * Checks that the specified value is valid.
     * <p>
     * This validates that the value is within the valid range of values.
     * The field is only used to improve the error message.
     *
     * @param int $value the value to check
     * @param TemporalField $field the field being checked, may be null
     * @return int the value that was passed in
     * @throws DateTimeException
     * @see #isValidValue(long)
     */
    public function checkValidValue($value, TemporalField $field)
    {
        if ($this->isValidValue($value) === false) {
            throw new DateTimeException($this->genInvalidFieldMessage($field, $value));
        }

        return $value;
    }

    /**
     * Checks that the specified value is valid and fits in an {@code int}.
     * <p>
     * This validates that the value is within the valid range of values and that
     * all valid values are within the bounds of an {@code int}.
     * The field is only used to improve the error message.
     *
     * @param int $value the value to check
     * @param TemporalField $field
     * @return int the value that was passed in
     * @throws DateTimeException
     * @internal param TemporalField $field the field being checked, may be null
     * @see #isValidIntValue(long)
     */
    public function checkValidIntValue($value, TemporalField $field)
    {
        if ($this->isValidIntValue($value) === false) {
            throw new DateTimeException($this->genInvalidFieldMessage($field, $value));
        }

        return (int)$value;
    }

    private function genInvalidFieldMessage(TemporalField $field, $value)
    {
        if ($field !== null) {
            return "Invalid value for " . $field . " (valid values " . $this . "): " . $value;
        } else {
            return "Invalid value (valid values " . $this . "): " . $value;
        }
    }

//-----------------------------------------------------------------------
    /**
     * Checks if this range is equal to another range.
     * <p>
     * The comparison is based on the four values, minimum, largest minimum,
     * smallest maximum and maximum.
     * Only objects of type {@code ValueRange} are compared, other types return false.
     *
     * @param mixed $obj the object to check, null returns false
     * @return bool true if this is equal to the other range
     */
    public function equals($obj)
    {
        if ($obj === $this) {
            return true;
        }

        if ($obj instanceof ValueRange) {
            $other = $obj;
            return $this->minSmallest === $other->minSmallest && $this->minLargest === $other->minLargest &&
            $this->maxSmallest === $other->maxSmallest && $this->maxLargest === $other->maxLargest;
        }
        return false;
    }


//-----------------------------------------------------------------------
    /**
     * Outputs this range as a {@code String}.
     * <p>
     * The format will be '{min}/{largestMin} - {smallestMax}/{max}',
     * where the largestMin or smallestMax sections may be omitted, together
     * with associated slash, if they are the same as the min or max.
     *
     * @return string a string representation of this range, not null
     */
    public function __toString()
    {
        $buf = '';
        $buf .= $this->minSmallest;
        if ($this->minSmallest !== $this->minLargest) {
            $buf .= '/' . $this->minLargest;
        }

        $buf .= " - " . $this->maxSmallest;
        if ($this->maxSmallest !== $this->maxLargest) {
            $buf .= '/' . $this->maxLargest;
        }
        return $buf;
    }

}
