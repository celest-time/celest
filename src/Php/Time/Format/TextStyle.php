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
 * Copyright (c) 2008-2012, Stephen Colebourne & Michael Nascimento Santos
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

/**
 * Enumeration of the style of text formatting and parsing.
 * <p>
 * Text styles define three sizes for the formatted text - 'full', 'short' and 'narrow'.
 * Each of these three sizes is available in both 'standard' and 'stand-alone' variations.
 * <p>
 * The difference between the three sizes is obvious in most languages.
 * For example, in English the 'full' month is 'January', the 'short' month is 'Jan'
 * and the 'narrow' month is 'J'. Note that the narrow size is often not unique.
 * For example, 'January', 'June' and 'July' all have the 'narrow' text 'J'.
 * <p>
 * The difference between the 'standard' and 'stand-alone' forms is trickier to describe
 * as there is no difference in English. However, in other languages there is a difference
 * in the word used when the text is used alone, as opposed to in a complete date.
 * For example, the word used for a month when used alone in a date picker is different
 * to the word used for month in association with a day and year in a date.
 *
 * @implSpec
 * This is immutable and thread-safe enum.
 */
class TextStyle
{
    // ordered from large to small
    // ordered so that bit 0 of the ordinal indicates stand-alone.
    public function init()
    {
        self::$FULL = new TextStyle(Calendar::LONG_FORMAT, 0);
        self::$FULL_STANDALONE = new TextStyle(Calendar::LONG_STANDALONE, 0);
        self::$SHORT = new TextStyle(Calendar::SHORT_FORMAT, 1);
        self::$SHORT_STANDALONE = new TextStyle(Calendar::SHORT_STANDALONE, 1);
        self::$NARROW = new TextStyle(Calendar::NARROW_FORMAT, 1);
        self::$NARROW_STANDALONE = new TextStyle(Calendar::NARROW_STANDALONE, 1);
    }

    /**
     * Full text, typically the full description.
     * For example, day-of-week Monday might output "Monday".
     * @return TextStyle
     */
    public static function FULL()
    {
        return self::$FULL;
    }

    /** @var TextStyle */
    public static $FULL;

    /**
     * Full text for stand-alone use, typically the full description.
     * For example, day-of-week Monday might output "Monday".
     * @return TextStyle
     */
    public static function FULL_STANDALONE()
    {
        return self::$FULL_STANDALONE;
    }

    /** @var TextStyle */
    public static $FULL_STANDALONE;

    /**
     * Short text, typically an abbreviation.
     * For example, day-of-week Monday might output "Mon".
     * @return TextStyle
     */
    public static function SHORT()
    {
        return self::$SHORT;
    }

    /** @var TextStyle */
    public static $SHORT;

    /**
     * Short text for stand-alone use, typically an abbreviation.
     * For example, day-of-week Monday might output "Mon".
     * @return TextStyle
     */
    public static function SHORT_STANDALONE()
    {
        return self::$SHORT_STANDALONE;
    }

    /** @var TextStyle */
    public static $SHORT_STANDALONE;

    /**
     * Narrow text, typically a single letter.
     * For example, day-of-week Monday might output "M".
     * @return TextStyle
     */
    public static function NARROW()
    {
        return self::$NARROW;
    }

    /** @var TextStyle */
    public static $NARROW;

    /**
     * Narrow text for stand-alone use, typically a single letter.
     * For example, day-of-week Monday might output "M".
     * @return TextStyle
     */
    public static function NARROW_STANDALONE()
    {
        return self::$NARROW_STANDALONE;
    }

    /** @var TextStyle */
    public static $NARROW_STANDALONE;

    /** @var int */
    private $calendarStyle;
    /** @var int */
    private $zoneNameStyleIndex;

    private function __construct($calendarStyle, $zoneNameStyleIndex)
    {
        $this->calendarStyle = $calendarStyle;
        $this->zoneNameStyleIndex = $zoneNameStyleIndex;
    }

    /**
     * Returns true if the Style is a stand-alone style.
     * @return bool true if the style is a stand-alone style.
     */
    public function isStandalone()
    {
        return ($this->ordinal() & 1) == 1;
    }

    /**
     * Returns the stand-alone style with the same size.
     * @return TextStyle the stand-alone style with the same size
     */
    public function asStandalone()
    {
        return TextStyle::values()[$this->ordinal() | 1];
    }

    /**
     * Returns the normal style with the same size.
     *
     * @return TextStyle the normal style with the same size
     */
    public function asNormal()
    {
        return TextStyle::values()[$this->ordinal() & ~1];
    }

    /**
     * Returns the {@code Calendar} style corresponding to this {@code TextStyle}.
     *
     * @return int the corresponding {@code Calendar} style
     */
    public function toCalendarStyle()
    {
        return $this->calendarStyle;
    }

    /**
     * Returns the relative index value to an element of the {@link
     * java.text.DateFormatSymbols#getZoneStrings() DateFormatSymbols.getZoneStrings()}
     * value, 0 for long names and 1 for short names (abbreviations). Note that these values
     * do <em>not</em> correspond to the {@link java.util.TimeZone#LONG} and {@link
     * java.util.TimeZone#SHORT} values.
     *
     * @return int the relative index value to time zone names array
     */
    public function zoneNameStyleIndex()
    {
        return $this->zoneNameStyleIndex;
    }
}
