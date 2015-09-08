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
 * Localized decimal style used in date and time formatting.
 * <p>
 * A significant part of dealing with dates and times is the localization.
 * This class acts as a central point for accessing the information.
 *
 * @implSpec
 * This class is immutable and thread-safe.
 *
 * @since 1.8
 */
final class DecimalStyle
{
    public static function init()
    {
        self::$STANDARD = new DecimalStyle('0', '+', '-', '.');
    }

    /**
     * The standard set of non-localized decimal style symbols.
     * <p>
     * This uses standard ASCII characters for zero, positive, negative and a dot for the decimal point.
     */
    public function STANDARD()
    {
        return self::$STANDARD;
    }

    /** @var DecimalStyle */
    public static $STANDARD;
    /**
     * The cache of DecimalStyle instances.
     * ConcurrentMap<Locale, DecimalStyle>
     * @var DecimalStyle[]
     */
    private static $CACHE = [];

    /**
     * The zero digit.
     * @var string
     */
    private $zeroDigit;
    /**
     * The positive sign.
     * @var string
     */
    private $positiveSign;
    /**
     * The negative sign.
     * @var string
     */
    private $negativeSign;
    /**
     * The decimal separator.
     * @var string
     */
    private $decimalSeparator;

    //-----------------------------------------------------------------------
    /**
     * Lists all the locales that are supported.
     * <p>
     * The locale 'en_US' will always be present.
     *
     * @return Locale[] a Set of Locales for which localization is supported
     */
    public static function getAvailableLocales()
    {
        return DecimalFormatSymbols::getAvailableLocales();
    }

    /**
     * Obtains the DecimalStyle for the default
     * {@link java.util.Locale.Category#FORMAT FORMAT} locale.
     * <p>
     * This method provides access to locale sensitive decimal style symbols.
     * <p>
     * This is equivalent to calling
     * {@link #of(Locale)
     *     of(Locale.getDefault(Locale.Category.FORMAT))}.
     *
     * @see java.util.Locale.Category#FORMAT
     * @return DecimalStyle the decimal style, not null
     */
    public
    static function ofDefaultLocale()
    {
        return self::of(Locale::getDefault(Locale::CategoryFORMAT));
    }

    /**
     * Obtains the DecimalStyle for the specified locale.
     * <p>
     * This method provides access to locale sensitive decimal style symbols.
     *
     * @param $locale Locale the locale, not null
     * @return DecimalStyle the decimal style, not null
     */
    public
    static function of(Locale $locale)
    {
        $info = self::$CACHE->get($locale);
        if ($info == null) {
            $info = self::create($locale);
            self::$CACHE->putIfAbsent($locale, $info);
            $info = self::$CACHE->get($locale);
        }

        return $info;
    }

    private
    static function create(Locale $locale)
    {
        $oldSymbols = DecimalFormatSymbols::getInstance($locale);
        $zeroDigit = $oldSymbols->getZeroDigit();
        $positiveSign = '+';
        $negativeSign = $oldSymbols->getMinusSign();
        $decimalSeparator = $oldSymbols->getDecimalSeparator();
        if ($zeroDigit == '0' && $negativeSign == '-' && $decimalSeparator == '.') {
            return self::$STANDARD;
        }

        return new DecimalStyle($zeroDigit, $positiveSign, $negativeSign, $decimalSeparator);
    }

//-----------------------------------------------------------------------
    /**
     * Restricted constructor.
     *
     * @param $zeroChar string the character to use for the digit of zero
     * @param $positiveSignChar string the character to use for the positive sign
     * @param $negativeSignChar string the character to use for the negative sign
     * @param $decimalPointChar string the character to use for the decimal point
     */
    private function __construct($zeroChar, $positiveSignChar, $negativeSignChar, $decimalPointChar)
    {
        $this->zeroDigit = $zeroChar;
        $this->positiveSign = $positiveSignChar;
        $this->negativeSign = $negativeSignChar;
        $this->decimalSeparator = $decimalPointChar;
    }

//-----------------------------------------------------------------------
    /**
     * Gets the character that represents zero.
     * <p>
     * The character used to represent digits may vary by culture.
     * This method specifies the zero character to use, which implies the characters for one to nine.
     *
     * @return string the character for zero
     */
    public
    function getZeroDigit()
    {
        return $this->zeroDigit;
    }

    /**
     * Returns a copy of the info with a new character that represents zero.
     * <p>
     * The character used to represent digits may vary by culture.
     * This method specifies the zero character to use, which implies the characters for one to nine.
     *
     * @param $zeroDigit string the character for zero
     * @return DecimalStyle a copy with a new character that represents zero, not null
     */
    public
    function withZeroDigit($zeroDigit)
    {
        if ($zeroDigit === $this->zeroDigit) {
            return $this;
        }

        return new DecimalStyle($zeroDigit, $this->positiveSign, $this->negativeSign, $this->decimalSeparator);
    }

//-----------------------------------------------------------------------
    /**
     * Gets the character that represents the positive sign.
     * <p>
     * The character used to represent a positive number may vary by culture.
     * This method specifies the character to use.
     *
     * @return string the character for the positive sign
     */
    public
    function getPositiveSign()
    {
        return $this->positiveSign;
    }

    /**
     * Returns a copy of the info with a new character that represents the positive sign.
     * <p>
     * The character used to represent a positive number may vary by culture.
     * This method specifies the character to use.
     *
     * @param $positiveSign string the character for the positive sign
     * @return DecimalStyle a copy with a new character that represents the positive sign, not null
     */
    public
    function withPositiveSign($positiveSign)
    {
        if ($positiveSign === $this->positiveSign) {
            return $this;
        }

        return new DecimalStyle($this->zeroDigit, $positiveSign, $this->negativeSign, $this->decimalSeparator);
    }

//-----------------------------------------------------------------------
    /**
     * Gets the character that represents the negative sign.
     * <p>
     * The character used to represent a negative number may vary by culture.
     * This method specifies the character to use.
     *
     * @return string the character for the negative sign
     */
    public
    function getNegativeSign()
    {
        return $this->negativeSign;
    }

    /**
     * Returns a copy of the info with a new character that represents the negative sign.
     * <p>
     * The character used to represent a negative number may vary by culture.
     * This method specifies the character to use.
     *
     * @param $negativeSign string the character for the negative sign
     * @return DecimalStyle a copy with a new character that represents the negative sign, not null
     */
    public
    function withNegativeSign($negativeSign)
    {
        if ($negativeSign === $this->negativeSign) {
            return $this;
        }

        return new DecimalStyle($this->zeroDigit, $this->positiveSign, $negativeSign, $this->decimalSeparator);
    }

//-----------------------------------------------------------------------
    /**
     * Gets the character that represents the decimal point.
     * <p>
     * The character used to represent a decimal point may vary by culture.
     * This method specifies the character to use.
     *
     * @return string the character for the decimal point
     */
    public
    function getDecimalSeparator()
    {
        return $this->decimalSeparator;
    }

    /**
     * Returns a copy of the info with a new character that represents the decimal point.
     * <p>
     * The character used to represent a decimal point may vary by culture.
     * This method specifies the character to use.
     *
     * @param $decimalSeparator string the character for the decimal point
     * @return DecimalStyle a copy with a new character that represents the decimal point, not null
     */
    public
    function withDecimalSeparator($decimalSeparator)
    {
        if ($decimalSeparator === $this->decimalSeparator) {
            return $this;
        }

        return new DecimalStyle($this->zeroDigit, $this->positiveSign, $this->negativeSign, $decimalSeparator);
    }

//-----------------------------------------------------------------------
    /**
     * Checks whether the character is a digit, based on the currently set zero character.
     *
     * @param $ch string the character to check
     * @return int the value, 0 to 9, of the character, or -1 if not a digit
     */
    public function convertToDigit($ch)
    {
        $val = \ord($ch) - \ord($this->zeroDigit);
        return ($val >= 0 && $val <= 9) ? $val : -1;
    }

    /**
     * Converts the input numeric text to the internationalized form using the zero character.
     *
     * @param $numericText string the text, consisting of digits 0 to 9, to convert, not null
     * @return string the internationalized text, not null
     */
    public function convertNumberToI18N($numericText)
    {
        if ($this->zeroDigit == '0') {
            return $numericText;
        }

        $diff = \ord($this->zeroDigit) - \ord('0');
        for ($i = 0; $i < \strlen($numericText); $i++) {
            $numericText[$i] = \chr(\ord($numericText[$i]) + $diff);
        }
        return $numericText;
    }

    //-----------------------------------------------------------------------
    /**
     * Checks if this DecimalStyle is equal to another DecimalStyle.
     *
     * @param $obj mixed the object to check, null returns false
     * @return true if this is equal to the other date
     */
    public function equals($obj)
    {
        if ($this === $obj) {
            return true;
        }
        if ($obj instanceof DecimalStyle) {
            $other = $obj;
            return ($this->zeroDigit == $other->zeroDigit && $this->positiveSign == $other->positiveSign &&
                $this->negativeSign == $other->negativeSign && $this->decimalSeparator == $other->decimalSeparator);
        }
        return false;
    }

    //-----------------------------------------------------------------------
    /**
     * Returns a string describing this DecimalStyle.
     *
     * @return string a string description, not null
     */
    public function __toString()
    {
        return "DecimalStyle[" . $this->zeroDigit . $this->positiveSign . $this->negativeSign . $this->decimalSeparator . "]";
    }

}

DecimalStyle::init();
