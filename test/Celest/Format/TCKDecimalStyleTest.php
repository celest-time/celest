<?php
/*
 * Copyright (c) 2012, 2013, Oracle and/or its affiliates. All rights reserved.
 * DO NOT ALTER OR REMOVE COPYRIGHT NOTICES OR THIS FILE HEADER.
 *
 * This code is free software; you can redistribute it and/or modify it
 * under the terms of the GNU General Public License version 2 only, as
 * published by the Free Software Foundation.
 *
 * This code is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or
 * FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License
 * version 2 for more details ($a copy is included in the LICENSE file that
 * accompanied this code).
 *
 * You should have received $a copy of the GNU General Public License version
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
namespace Celest\Format;

use Celest\Locale;

/**
 * Test DecimalStyle.
 */
class TCKDecimalStyleTest extends \PHPUnit_Framework_TestCase
{

    public function test_getAvailableLocales()
    {
        $this->markTestIncomplete('TBD, DecimalStyle');
        $locales = DecimalStyle::getAvailableLocales();
        $this->assertEquals(count($locales) > 0, true, "locales: " . $locales);
        $this->assertEquals($locales->contains(Locale::US()), true, "Locale.US not found in available Locales");
    }

    //-----------------------------------------------------------------------

    public function test_of_Locale()
    {
        $loc1 = DecimalStyle::of(Locale::CANADA());
        $this->assertEquals($loc1->getZeroDigit(), '0');
        $this->assertEquals($loc1->getPositiveSign(), '+');
        $this->assertEquals($loc1->getNegativeSign(), '-');
        $this->assertEquals($loc1->getDecimalSeparator(), '.');
    }

    //-----------------------------------------------------------------------

    public function test_STANDARD()
    {
        $loc1 = DecimalStyle::STANDARD();
        $this->assertEquals($loc1->getZeroDigit(), '0');
        $this->assertEquals($loc1->getPositiveSign(), '+');
        $this->assertEquals($loc1->getNegativeSign(), '-');
        $this->assertEquals($loc1->getDecimalSeparator(), '.');
    }

    //-----------------------------------------------------------------------

    public function test_zeroDigit()
    {
        $base = DecimalStyle::STANDARD();
        $this->assertEquals($base->withZeroDigit('A')->getZeroDigit(), 'A');
    }


    public function test_positiveSign()
    {
        $base = DecimalStyle::STANDARD();
        $this->assertEquals($base->withPositiveSign('A')->getPositiveSign(), 'A');
    }


    public function test_negativeSign()
    {
        $base = DecimalStyle::STANDARD();
        $this->assertEquals($base->withNegativeSign('A')->getNegativeSign(), 'A');
    }


    public function test_decimalSeparator()
    {
        $base = DecimalStyle::STANDARD();
        $this->assertEquals($base->withDecimalSeparator('A')->getDecimalSeparator(), 'A');
    }

    //-----------------------------------------------------------------------
    /* TBD: convertToDigit and convertNumberToI18N are package-private methods
    
    public function test_convertToDigit_$base() {
        $base = DecimalStyle.STANDARD;
        $this->assertEquals($base.convertToDigit('0'), 0);
        $this->assertEquals($base.convertToDigit('1'), 1);
        $this->assertEquals($base.convertToDigit('9'), 9);
        $this->assertEquals($base.convertToDigit(' '), -1);
        $this->assertEquals($base.convertToDigit('A'), -1);
    }

    
    public function test_convertToDigit_altered() {
        $base = DecimalStyle.STANDARD.withZeroDigit('A');
        $this->assertEquals($base.convertToDigit('A'), 0);
        $this->assertEquals($base.convertToDigit('B'), 1);
        $this->assertEquals($base.convertToDigit('J'), 9);
        $this->assertEquals($base.convertToDigit(' '), -1);
        $this->assertEquals($base.convertToDigit('0'), -1);
    }

    //-----------------------------------------------------------------------
    
    public function test_convertNumberToI18N_$base() {
        $base = DecimalStyle.STANDARD;
        $this->assertEquals($base.convertNumberToI18N("134"), "134");
    }

    
    public function test_convertNumberToI18N_altered() {
        $base = DecimalStyle.STANDARD.withZeroDigit('A');
        $this->assertEquals($base.convertNumberToI18N("134"), "BDE");
    }
    */
    //-----------------------------------------------------------------------

    public function test_equalsHashCode1()
    {
        $a = DecimalStyle::STANDARD();
        $b = DecimalStyle::STANDARD();
        $this->assertEquals($a->equals($b), true);
        $this->assertEquals($b->equals($a), true);
    }


    public function test_equalsHashCode2()
    {
        $a = DecimalStyle::STANDARD()->withZeroDigit('A');
        $b = DecimalStyle::STANDARD()->withZeroDigit('A');
        $this->assertEquals($a->equals($b), true);
        $this->assertEquals($b->equals($a), true);
    }


    public function test_equalsHashCode3()
    {
        $a = DecimalStyle::STANDARD()->withZeroDigit('A');
        $b = DecimalStyle::STANDARD()->withDecimalSeparator('A');
        $this->assertEquals($a->equals($b), false);
        $this->assertEquals($b->equals($a), false);
    }


    public function test_equalsHashCode_bad()
    {
        $a = DecimalStyle::STANDARD();
        $this->assertEquals($a->equals(""), false);
        $this->assertEquals($a->equals(null), false);
    }

    //-----------------------------------------------------------------------

    public function test_toString_base()
    {
        $base = DecimalStyle::STANDARD();
        $this->assertEquals($base->__toString(), "DecimalStyle[0+-.]");
    }


    public function test_toString_altered()
    {
        $base = DecimalStyle::of(Locale::US())->withZeroDigit('A')->withDecimalSeparator('@');
        $this->assertEquals($base->__toString(), "DecimalStyle[A+-@]");
    }

}
