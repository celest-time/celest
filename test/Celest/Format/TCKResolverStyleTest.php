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

use Celest\DateTimeException;
use Celest\Temporal\ChronoField;
use PHPUnit\Framework\TestCase;

class TCKResolverStyleTest extends TestCase
{

    //-----------------------------------------------------------------------
    // valueOf()
    //-----------------------------------------------------------------------
    public function test_valueOf()
    {
        foreach (ResolverStyle::values() as $style) {
            $this->assertEquals(ResolverStyle::valueOf($style->name()), $style);
        }
    }

    public function data_resolverStyle()
    {
        return [
            ["2000/15/30", ResolverStyle::LENIENT(), null, 2001, 3, 30],
            ["2000/02/30", ResolverStyle::SMART(), null, 2000, 2, 29],
            ["2000/02/29", ResolverStyle::STRICT(), null, 2000, 2, 29],

            ["2000/15/30 CE", ResolverStyle::LENIENT(), null, 2001, 3, 30],
            ["2000/02/30 CE", ResolverStyle::SMART(), null, 2000, 2, 29],
            ["5/02/29 BCE", ResolverStyle::STRICT(), null, 5, 2, 29],

            ["4/02/29 BCE", ResolverStyle::STRICT(), DateTimeException ::class, -1, -1, -1],
            ["2000/02/30 CE", ResolverStyle::STRICT(), DateTimeException ::class, -1, -1, -1],

        ];
    }

    /**
     * @dataProvider data_resolverStyle
     */
    public function test_resolverStyle($str, ResolverStyle $style, $expectedEx, $year, $month, $day)
    {
        $builder = new DateTimeFormatterBuilder();
        $builder->appendValue(ChronoField::YEAR_OF_ERA());
        $builder->appendLiteral("/");
        $builder->appendValue(ChronoField::MONTH_OF_YEAR());
        $builder->appendLiteral("/");
        $builder->appendValue(ChronoField::DAY_OF_MONTH());

        $eraMap = [
            1 => "CE",
            0 => "BCE"
        ];
        $optionalFormatter = (new DateTimeFormatterBuilder())->appendLiteral(" ")->appendText3(ChronoField::ERA(), $eraMap)->toFormatter();

        $formatter = $builder->appendOptional($optionalFormatter)->toFormatter();
        $formatter = $formatter->withResolverStyle($style);
        if ($expectedEx == null) {
            $accessor = $formatter->parse($str);
            $this->assertEquals($accessor->get(ChronoField::YEAR_OF_ERA()), $year);
            $this->assertEquals($accessor->get(ChronoField::MONTH_OF_YEAR()), $month);
            $this->assertEquals($accessor->get(ChronoField::DAY_OF_MONTH()), $day);
        } else {
            try {
                $formatter->parse($str);
                $this->fail();
            } catch (\Exception $ex) {
                $this->assertInstanceOf($expectedEx, $ex);
            }
        }
    }
}
