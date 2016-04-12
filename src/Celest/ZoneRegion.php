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
namespace Celest;

use Celest\Zone\ZoneRulesException;
use Celest\Zone\ZoneRules;
use Celest\Zone\ZoneRulesProvider;

/**
 * A geographical region where the same time-zone rules apply.
 * <p>
 * Time-zone information is categorized as a set of rules defining when and
 * how the offset from UTC/Greenwich changes. These rules are accessed using
 * identifiers based on geographical regions, such as countries or states.
 * The most common region classification is the Time Zone Database (TZDB),
 * which defines regions such as 'Europe/Paris' and 'Asia/Tokyo'.
 * <p>
 * The region identifier, modeled by this class, is distinct from the
 * underlying rules, modeled by {@link ZoneRules}.
 * The rules are defined by governments and change frequently.
 * By contrast, the region identifier is well-defined and long-lived.
 * This separation also allows rules to be shared between regions if appropriate.
 *
 * @implSpec
 * This class is immutable and thread-safe.
 *
 * @since 1.8
 */
class ZoneRegion extends ZoneId
{
    /**
     * The time-zone ID, not null.
     * @var string
     */
    private $id;
    /**
     * The time-zone rules, null if zone ID was loaded leniently.
     * @var ZoneRules
     */
    private $rules;

    /**
     * Obtains an instance of {@code ZoneId} from an identifier.
     *
     * TODO package visiblity
     *
     * @param string $zoneId the time-zone ID, not null
     * @param bool $checkAvailable whether to check if the zone ID is available
     * @return ZoneRegion the zone ID, not null
     * @throws DateTimeException if the ID format is invalid
     * @throws ZoneRulesException if checking availability and the ID cannot be found
     */
    static function ofId($zoneId, $checkAvailable)
    {
        self::checkName($zoneId);
        $rules = null;
        try {
            // always attempt load for better behavior after deserialization
            $rules = ZoneRulesProvider::getRules($zoneId, true);
        } catch
        (ZoneRulesException $ex) {
            if ($checkAvailable) {
                throw $ex;
            }
        }
        return new ZoneRegion($zoneId, $rules);
    }

    /**
     * Checks that the given string is a legal ZondId name.
     *
     * @param string $zoneId the time-zone ID, not null
     * @throws DateTimeException if the ID format is invalid
     */
    private static function checkName($zoneId)
    {
        $n = strlen($zoneId);
        if ($n < 2) {
            throw new DateTimeException("Invalid ID for region-based ZoneId, invalid format: " . $zoneId);
        }

        for ($i = 0; $i < $n; $i++) {
            $c = $zoneId[$i];
            if ($c >= 'a' && $c <= 'z') continue;
            if ($c >= 'A' && $c <= 'Z') continue;
            if ($c === '/' && $i !== 0) continue;
            if ($c >= '0' && $c <= '9' && $i !== 0) continue;
            if ($c === '~' && $i !== 0) continue;
            if ($c === '.' && $i !== 0) continue;
            if ($c === '_' && $i !== 0) continue;
            if ($c === '+' && $i !== 0) continue;
            if ($c === '-' && $i !== 0) continue;
            throw new DateTimeException("Invalid ID for region-based ZoneId, invalid format: " . $zoneId);
        }
    }

    //-------------------------------------------------------------------------
    /**
     * Constructor.
     * TODO Package visiblity
     * @param string $id the time-zone ID, not null
     * @param ZoneRules $rules the rules, null for lazy lookup
     */
    public function __construct($id, ZoneRules $rules)
    {
        parent::__construct();
        $this->id = $id;
        $this->rules = $rules;
    }

//-----------------------------------------------------------------------
    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return ZoneRules
     */
    public function getRules()
    {
        // additional query for group provider when null allows for possibility
        // that the provider was updated after the ZoneId was created
        return ($this->rules !== null ? $this->rules : ZoneRulesProvider::getRules($this->id, false));
    }
}
