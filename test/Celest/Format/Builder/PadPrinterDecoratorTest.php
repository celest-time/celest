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
namespace Celest\Format\Builder;

use Celest\DateTimeException;
use Celest\LocalDate;

/**
 * Test PadPrinterDecorator.
 */
class PadPrinterDecoratorTest extends AbstractTestPrinterParser
{

    //-----------------------------------------------------------------------
    public function test__print_emptyCalendrical()
    {
        $buf = '';
        $this->builder->padNext2(3, '-')->appendLiteral('Z');
        $this->getFormatter()->formatTo(self::EMPTY_DTA(), $buf);
        $this->assertEquals($buf, "--Z");
    }

    public function test__print_fullDateTime()
    {
        $buf = '';
        $this->builder->padNext2(3, '-')->appendLiteral('Z');
        $this->getFormatter()->formatTo(LocalDate::of(2008, 12, 3), $buf);
        $this->assertEquals($buf, "--Z");
    }

    public function test__print_append()
    {
        $buf = 'EXISTING';
        $this->builder->padNext2(3, '-')->appendLiteral('Z');
        $this->getFormatter()->formatTo(self::EMPTY_DTA(), $buf);
        $this->assertEquals($buf, "EXISTING--Z");
    }

    //-----------------------------------------------------------------------
    public function test__print_noPadRequiredSingle()
    {
        $buf = '';
        $this->builder->padNext2(1, '-')->appendLiteral('Z');
        $this->getFormatter()->formatTo(self::EMPTY_DTA(), $buf);
        $this->assertEquals($buf, "Z");
    }

    public function test__print_padRequiredSingle()
    {
        $buf = '';
        $this->builder->padNext2(5, '-')->appendLiteral('Z');
        $this->getFormatter()->formatTo(self::EMPTY_DTA(), $buf);
        $this->assertEquals($buf, "----Z");
    }

    public function test__print_noPadRequiredMultiple()
    {
        $buf = '';
        $this->builder->padNext2(4, '-')->appendLiteral2("WXYZ");
        $this->getFormatter()->formatTo(self::EMPTY_DTA(), $buf);
        $this->assertEquals($buf, "WXYZ");
    }

    public function test__print_padRequiredMultiple()
    {
        $buf = '';
        $this->builder->padNext2(5, '-')->appendLiteral2("WXYZ");
        $this->getFormatter()->formatTo(self::EMPTY_DTA(), $buf);
        $this->assertEquals($buf, "-WXYZ");
    }

    /**
     * @expectedException \Celest\DateTimeException
     */
    public function test__print_overPad()
    {
        $buf = '';
        $this->builder->padNext2(3, '-')->appendLiteral2("WXYZ");
        $this->getFormatter()->formatTo(self::EMPTY_DTA(), $buf);
    }

    //-----------------------------------------------------------------------
    public function test__toString1()
    {
        $this->builder->padNext2(5, ' ')->appendLiteral('Y');
        $this->assertEquals($this->getFormatter()->__toString(), "Pad('Y',5)");
    }

    public function test__toString2()
    {
        $this->builder->padNext2(5, '-')->appendLiteral('Y');
        $this->assertEquals($this->getFormatter()->__toString(), "Pad('Y',5,'-')");
    }

}
