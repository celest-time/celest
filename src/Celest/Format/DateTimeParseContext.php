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
namespace Celest\Format;

use Celest\Chrono\Chronology;
use Celest\Chrono\IsoChronology;
use Celest\Locale;
use Celest\Temporal\TemporalAccessor;
use Celest\Temporal\TemporalField;
use Celest\ZoneId;

/**
 * Context object used during date and time parsing.
 * <p>
 * This class represents the current state of the parse.
 * It has the ability to store and retrieve the parsed values and manage optional segments.
 * It also provides key information to the parsing methods.
 * <p>
 * Once parsing is complete, the {@link #toUnresolved()} is used to obtain the unresolved
 * result data. The {@link #toResolved()} is used to obtain the resolved result.
 *
 * @implSpec
 * This class is a mutable context intended for use from a single thread.
 * Usage of the class is thread-safe within standard parsing as a new instance of this class
 * is automatically created for each parse and parsing is single-threaded
 *
 * @since 1.8
 */
final class DateTimeParseContext
{

    /**
     * The formatter, not null.
     * @var DateTimeFormatter
     */
    private $formatter;
    /**
     * Whether to parse using case sensitively.
     * @var bool
     */
    private $caseSensitive = true;
    /**
     * Whether to parse using strict rules.
     * @var bool
     */
    private $strict = true;
    /**
     * The list of parsed data.
     * @var Parsed[]
     */
    private $parsed;
    /**
     * List of Consumers<Chronology> to be notified if the Chronology changes.
     * @var callable[]
     */
    private $chronoListeners = null;

    /**
     * Creates a new instance of the context.
     *
     * @param DateTimeFormatter $formatter the formatter controlling the parse, not null
     */
    public function __construct(DateTimeFormatter $formatter)
    {
        $this->formatter = $formatter;
        $this->parsed = [new Parsed()];
    }

    /**
     * Creates a copy of this context.
     * This retains the case sensitive and strict flags.
     * @return DateTimeParseContext
     */
    public function copy()
    {
        $newContext = new DateTimeParseContext($this->formatter);
        $newContext->caseSensitive = $this->caseSensitive;
        $newContext->strict = $this->strict;
        return $newContext;
    }

//-----------------------------------------------------------------------
    /**
     * Gets the locale.
     * <p>
     * This locale is used to control localization in the parse except
     * where localization is controlled by the DecimalStyle.
     *
     * @return Locale the locale, not null
     */
    public function getLocale()
    {
        return $this->formatter->getLocale();
    }

    /**
     * Gets the DecimalStyle.
     * <p>
     * The DecimalStyle controls the numeric parsing.
     *
     * @return DecimalStyle the DecimalStyle, not null
     */
    public function getDecimalStyle()
    {
        return $this->formatter->getDecimalStyle();
    }

    /**
     * Gets the effective chronology during parsing.
     *
     * @return Chronology the effective parsing chronology, not null
     */
    public function getEffectiveChronology()
    {
        $chrono = $this->currentParsed()->chrono;
        if ($chrono == null) {
            $chrono = $this->formatter->getChronology();
            if ($chrono == null) {
                $chrono = IsoChronology::INSTANCE();
            }
        }
        return $chrono;
    }

//-----------------------------------------------------------------------
    /**
     * Checks if parsing is case sensitive.
     *
     * @return bool true if parsing is case sensitive, false if case insensitive
     */
    public function isCaseSensitive()
    {
        return $this->caseSensitive;
    }

    /**
     * Sets whether the parsing is case sensitive or not.
     *
     * @param bool $caseSensitive changes the parsing to be case sensitive or not from now on
     */
    public function setCaseSensitive($caseSensitive)
    {
        $this->caseSensitive = $caseSensitive;
    }

    // TODO move to Helper class
    /** @var \Transliterator */
    private static $to_upper;

    private static function toUpperMb($str)
    {
        if (self::$to_upper === null)
            self::$to_upper = \Transliterator::create('Any-Upper');
        return self::$to_upper->transliterate($str);
    }

    /** @var \Transliterator */
    private static $to_lower;

    private static function toLowerMb($str)
    {
        if (self::$to_lower === null)
            self::$to_lower = \Transliterator::create('Any-Lower');
        return self::$to_lower->transliterate($str);
    }

//-----------------------------------------------------------------------
    /**
     * Helper to compare two {@code CharSequence} instances.
     * This uses {@link #isCaseSensitive()}.
     *
     * @param $cs1 string the first character sequence, not null
     * @param $offset1 int the offset into the first sequence, valid
     * @param $cs2 string the second character sequence, not null
     * @param $offset2 int the offset into the second sequence, valid
     * @param int $length the length to check, valid
     * @return bool true if equal
     */
    public function subSequenceEquals($cs1, $offset1, $cs2, $offset2, $length)
    {
        // TODO improve multibyte compatibility
        if ($offset1 + $length > strlen($cs1) || $offset2 + $length > strlen($cs2)) {
            return false;
        }

        $x = substr($cs1, $offset1, $length);
        $y = substr($cs2, $offset2, $length);

        if ($this->isCaseSensitive()) {
            return $x === $y;
        } else {
            if ($x !== $y && self::toUpperMb($x) !== self::toUpperMb($y) &&
                self::toLowerMb($x) !== self::toLowerMb($y)
            ) {
                return false;
            }
        }
        return true;
    }

    /**
     * Helper to compare two {@code char}.
     * This uses {@link #isCaseSensitive()}.
     *
     * @param $ch1 string the first character
     * @param $ch2 string the second character
     * @return true if equal
     */
    public function charEquals($ch1, $ch2)
    {
        if ($this->isCaseSensitive()) {
            return $ch1 === $ch2;
        }

        return $this->charEqualsIgnoreCase($ch1, $ch2);
    }

    /**
     * Compares two characters ignoring case.
     *
     * @param $c1 string the first
     * @param $c2 string the second
     * @return true if equal
     */
    public static function charEqualsIgnoreCase($c1, $c2)
    {
        return $c1 === $c2 ||
        strtoupper($c1) === strtoupper($c2) ||
        strtolower($c1) === strtolower($c2);
    }

//-----------------------------------------------------------------------
    /**
     * Checks if parsing is strict.
     * <p>
     * Strict parsing requires exact matching of the text and sign styles.
     *
     * @return bool true if parsing is strict, false if lenient
     */
    public function isStrict()
    {
        return $this->strict;
    }

    /**
     * Sets whether parsing is strict or lenient.
     *
     * @param bool $strict changes the parsing to be strict or lenient from now on
     */
    public function setStrict($strict)
    {
        $this->strict = $strict;
    }

//-----------------------------------------------------------------------
    /**
     * Starts the parsing of an optional segment of the input.
     */
    public function startOptional()
    {
        $this->parsed[] = $this->currentParsed()->copy();
    }

    /**
     * Ends the parsing of an optional segment of the input.
     *
     * @param bool $successful whether the optional segment was successfully parsed
     */
    public function endOptional($successful)
    {
        if ($successful) {
            unset($this->parsed[count($this->parsed) - 2]);
        } else {
            unset($this->parsed[count($this->parsed) - 1]);
        }
    }

//-----------------------------------------------------------------------
    /**
     * Gets the currently active temporal objects.
     *
     * @return Parsed the current temporal objects, not null
     */
    private function currentParsed()
    {
        return end($this->parsed);
    }

    /**
     * Gets the unresolved result of the parse.
     *
     * @return Parsed the result of the parse, not null
     */
    public function toUnresolved()
    {
        return $this->currentParsed();
    }

    /**
     * Gets the resolved result of the parse.
     *
     * @param ResolverStyle $resolverStyle
     * @param array $resolverFields
     * @return TemporalAccessor the result of the parse, not null
     */
    public function toResolved(ResolverStyle $resolverStyle, $resolverFields)
    {
        $parsed = $this->currentParsed();
        $parsed->chrono = $this->getEffectiveChronology();
        $parsed->zone = ($parsed->zone !== null ? $parsed->zone : $this->formatter->getZone());
        return $parsed->resolve($resolverStyle, $resolverFields);
    }


//-----------------------------------------------------------------------
    /**
     * Gets the first value that was parsed for the specified field.
     * <p>
     * This searches the results of the parse, returning the first value found
     * for the specified field. No attempt is made to derive a value.
     * The field may have an out of range value.
     * For example, the day-of-month might be set to 50, or the hour to 1000.
     *
     * @param TemporalField $field the field to query from the map, null returns null
     * @return int the value mapped to the specified field, null if field was not parsed
     */
    public function getParsed(TemporalField $field)
    {
        return $this->currentParsed()->fieldValues->get($field);
    }

    /**
     * Stores the parsed field.
     * <p>
     * This stores a field-value pair that has been parsed.
     * The value stored may be out of range for the field - no checks are performed.
     *
     * @param TemporalField $field the field to set in the field-value map, not null
     * @param int $value the value to set in the field-value map
     * @param int $errorPos the position of the field being parsed
     * @param int $successPos the position after the field being parsed
     * @return int the new position
     */
    public function setParsedField(TemporalField $field, $value, $errorPos, $successPos)
    {
        $fieldValues = $this->currentParsed()->fieldValues;
        $old = $fieldValues->put($field, $value);
        return ($old !== null && $old !== $value) ? ~$errorPos : $successPos;
    }

    /**
     * Stores the parsed chronology.
     * <p>
     * This stores the chronology that has been parsed.
     * No validation is performed other than ensuring it is not null.
     * <p>
     * The list of listeners is copied and cleared so that each
     * listener is called only once.  A listener can add itself again
     * if it needs to be notified of future changes.
     *
     * @param Chronology $chrono the parsed chronology, not null
     */
    public function setParsed(Chronology $chrono)
    {
        $this->currentParsed()->chrono = $chrono;
        if ($this->chronoListeners !== null && !empty($this->chronoListeners)) {

            $tmp = $this->chronoListeners;
            $this->chronoListeners = null;
            foreach ($tmp as $l) {
                $l($chrono);
            }
        }
    }

    /**
     * Adds a Consumer<Chronology> to the list of listeners to be notified
     * if the Chronology changes.
     * @param callable $listener a Consumer<Chronology> to be called when Chronology changes
     */
    public function addChronoChangedListener($listener)
    {
        if ($this->chronoListeners === null) {
            $this->chronoListeners = [];
        }

        $this->chronoListeners[] = $listener;
    }

    /**
     * Stores the parsed zone.
     * <p>
     * This stores the zone that has been parsed.
     * No validation is performed other than ensuring it is not null.
     *
     * @param ZoneId $zone the parsed zone, not null
     */
    public function setParsedZone(ZoneId $zone)
    {
        $this->currentParsed()->zone = $zone;
    }

    /**
     * Stores the parsed leap second.
     */
    public function setParsedLeapSecond()
    {
        $this->currentParsed()->leapSecond = true;
    }

//-----------------------------------------------------------------------
    /**
     * Returns a string version of the context for debugging.
     *
     * @return string a string representation of the context data, not null
     */
    public function __toString()
    {
        return $this->currentParsed()->__toString();
    }

}
