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

namespace Celest\Format;

use Celest\Chrono\Chronology;
use Celest\Locale;
use Celest\Temporal\ChronoField;
use Celest\Temporal\IsoFields;
use Celest\Temporal\TemporalField;
use ResourceBundle;

/**
 * A provider to obtain the textual form of a date-time field.
 *
 * @implSpec
 * Implementations must be thread-safe.
 * Implementations should cache the textual information.
 *
 * @since 1.8
 */
class DateTimeTextProvider
{

    /** Cache. */
    // ConcurrentMap<Entry<TemporalField, Locale>, Object>
    private static $CACHE = [];

    private function __construct()
    {
    }

    /**
     * Gets the provider of text.
     *
     * @return DateTimeTextProvider the provider, not null
     */
    static function getInstance()
    {
        return new DateTimeTextProvider();
    }

    public static function tryField($field, Locale $locale)
    {
        $bundle = new ResourceBundle($locale->getLocale(), null);
        if (version_compare(INTL_ICU_DATA_VERSION, "51", "<")) {
            return $bundle['calendar']['gregorian']['fields'][$field]['dn'];
        } else {
            return $bundle['fields'][$field]['dn'];
        }
    }

    public static function getZoneNames($zoneid, Locale $locale)
    {
        if ($zoneid === "UTC") {
            // TODO remove hardcoded string
            return ['ls' => "Coordinated Universal Time"];
        }

        $meta_names = self::getMetaNames($zoneid);

        if ($meta_names === null) {
            return null;
        }

        $bundle = new ResourceBundle($locale->getLocale(), 'ICUDATA-zone');

        $names = [];
        foreach ($meta_names as $name) {
            foreach ($bundle['zoneStrings']['meta:' . $name] as $key => $val) {
                $names[$key] = $val;
            }
        }

        return $names;
    }

    public static function getMetaNames($zoneid)
    {
        if ($zoneid === 'GMT') {
            return [$zoneid];
        }

        $name = str_replace('/', ':', $zoneid);

        $tmp = [];
        $bundle = new ResourceBundle('metaZones', 'ICUDATA', false);

        $metas = $bundle['metazoneInfo'][$name][0];

        if ($metas === null) {
            return null;
        }

        foreach ($metas as $value) {
            $tmp[] = $value;
        }

        return $tmp;
    }

    public static function tryFetch($field, $value, TextStyle $style, Locale $locale)
    {
        $bundle = new ResourceBundle($locale->getLocale(), null);

        $id = $style->isStandalone() ? 'stand-alone' : 'format';

        $styles = [
            \IntlDateFormatter::FULL => 'wide',
            \IntlDateFormatter::MEDIUM => 'abbreviated',
            \IntlDateFormatter::SHORT => 'narrow',
        ];

        $name = $bundle['calendar']['gregorian'][$field][$id][$styles[$style->toCalendarStyle()]][$value];

        // fallback to stand alone if not found
        if ($name === null && $id === 'format')
            $name = $bundle['calendar']['gregorian'][$field]['stand-alone'][$styles[$style->toCalendarStyle()]][$value];

        // ERA
        if ($name === null)
            $name = $bundle['calendar']['gregorian'][$field][$styles[$style->toCalendarStyle()]][$value];

        return $name;
    }

    /**
     * @param $field
     * @param TextStyle $style
     * @param Locale $locale
     * @param callable $transformer
     * @return array|null
     */
    public static function tryFetchStyleValues($field, $style, Locale $locale, callable $transformer)
    {
        $bundle = new ResourceBundle($locale->getLocale(), null);

        if ($style === null) {
            $tmp = [];
            foreach (['stand-alone', 'format'] as $id) {
                foreach (['wide', 'abbreviated', 'narrow'] as $style) {
                    $values = $bundle['calendar']['gregorian'][$field][$id][$style];
                    if ($values === null)
                        continue;

                    foreach ($values as $key => $value) {
                        $tmp[$value] = $transformer($key);
                    }
                }
            }

            return $tmp;
        }

        $id = $style->isStandalone() ? 'stand-alone' : 'format';

        $styles = [
            \IntlDateFormatter::FULL => 'wide',
            \IntlDateFormatter::MEDIUM => 'abbreviated',
            \IntlDateFormatter::SHORT => 'narrow',
        ];

        $values = $bundle['calendar']['gregorian'][$field][$id][$styles[$style->toCalendarStyle()]];

        // fallback to stand alone if not found
        if ($values === null && $id === 'format')
            $values = $bundle['calendar']['gregorian'][$field]['stand-alone'][$styles[$style->toCalendarStyle()]];

        // ERA
        if ($values === null)
            $values = $bundle['calendar']['gregorian'][$field][$styles[$style->toCalendarStyle()]];

        // AMPM
        if ($values === null)
            $values = $bundle['calendar']['gregorian'][$field];

        if (!$values)
            return null;

        $tmp = [];
        foreach ($values as $key => $value) {
            $tmp[$value] = $transformer($key);
        }

        return $tmp;
    }

    /**
     * Gets the text for the specified field, locale and style
     * for the purpose of formatting.
     * <p>
     * The text associated with the value is returned.
     * The null return value should be used if there is no applicable text, or
     * if the text would be a numeric representation of the value.
     *
     * @param TemporalField $field the field to get text for, not null
     * @param int $value the field value to get text for, not null
     * @param TextStyle $style the style to get text for, not null
     * @param Locale $locale the locale to get text for, not null
     * @return string|null the text for the field value, null if no text found
     */
    public function getText(TemporalField $field, $value, TextStyle $style, Locale $locale)
    {
        if ($field == ChronoField::DAY_OF_WEEK()) {
            if ($value === 7)
                $value = 0;

            return self::tryFetch('dayNames', $value, $style, $locale);
        }

        if ($field == ChronoField::MONTH_OF_YEAR()) {
            return self::tryFetch('monthNames', $value - 1, $style, $locale);
        }

        if ($field == ChronoField::AMPM_OF_DAY()) {
            $bundle = new ResourceBundle($locale->getLocale(), null);
            return $bundle['calendar']['gregorian']['AmPmMarkers'][$value];
        }

        if ($field == ChronoField::ERA()) {
            return self::tryFetch('eras', $value, $style, $locale);
        }

        if ($field == IsoFields::QUARTER_OF_YEAR()) {
            return self::tryFetch('quarters', $value - 1, $style, $locale);
        }

        return null;
    }

    /**
     * Gets the text for the specified chrono, field, locale and style
     * for the purpose of formatting.
     * <p>
     * The text associated with the value is returned.
     * The null return value should be used if there is no applicable text, or
     * if the text would be a numeric representation of the value.
     *
     * @param Chronology $chrono the Chronology to get text for, not null
     * @param TemporalField $field the field to get text for, not null
     * @param int $value the field value to get text for, not null
     * @param TextStyle $style the style to get text for, not null
     * @param Locale $locale the locale to get text for, not null
     * @return string|null the text for the field value, null if no text found
     */
    public function getText2(Chronology $chrono, TemporalField $field, $value,
                             TextStyle $style, Locale $locale)
    {
        /*if ($chrono == IsoChronology::INSTANCE()
            || !($field instanceof ChronoField)
        ) {
            return $this->getText($field, $value, $style, $locale);wtf
        }

        if ($field == ChronoField::ERA()) {
            $fieldIndex = \IntlCalendar::FIELD_ERA;
            if ($chrono == JapaneseChronology::INSTANCE()) {
                if ($value == -999) {
                    $fieldValue = 0;
                } else {
                    $fieldValue = $value + 2;
                }
            } else {
                $fieldValue = $value;
            }
        } else if ($field == ChronoField::MONTH_OF_YEAR()) {
            $fieldIndex = \IntlCalendar::FIELD_MONTH;
            $fieldValue = $value - 1;
        } else if ($field == ChronoField::DAY_OF_WEEK()) {
            $fieldIndex = \IntlCalendar::FIELD_DAY_OF_WEEK;
            $fieldValue = $value + 1;
            if ($fieldValue > 7) {
                $fieldValue = \IntlCalendar::DOW_SUNDAY;
            }
        } else if ($field == ChronoField::AMPM_OF_DAY()) {
            $fieldIndex = \IntlCalendar::FIELD_AM_PM;
            $fieldValue = $value;
        } else {
            return null;
        }
        return CalendarDataUtility::retrieveJavaTimeFieldValueName(
            $chrono->getCalendarType(), $fieldIndex, $fieldValue, $style->toCalendarStyle(), $locale);*/
        return null;
    }

    /**
     * Gets an iterator of text to field for the specified field, locale and style
     * for the purpose of parsing.
     * <p>
     * The iterator must be returned in order from the longest text to the shortest.
     * <p>
     * The null return value should be used if there is no applicable parsable text, or
     * if the text would be a numeric representation of the value.
     * Text can only be parsed if all the values for that field-style-locale combination are unique.
     *
     * @param TemporalField $field the field to get text for, not null
     * @param TextStyle $style the style to get text for, null for all parsable text
     * @param Locale $locale the locale to get text for, not null
     * @return array the iterator of text to field pairs, in order from longest text to shortest text,
     *  null if the field or style is not parsable
     */
    public function getTextIterator(TemporalField $field, $style, Locale $locale)
    {
        $values = null;

        if ($field == ChronoField::DAY_OF_WEEK()) {
            $values = self::tryFetchStyleValues('dayNames', $style, $locale, function ($i) {
                return $i === 0 ? 7 : $i;
            });
        }
        if ($field == ChronoField::MONTH_OF_YEAR()) {
            $values = self::tryFetchStyleValues('monthNames', $style, $locale, function ($i) {
                return $i + 1;
            });
        }

        if ($field == IsoFields::QUARTER_OF_YEAR()) {
            $values = self::tryFetchStyleValues('quarters', $style, $locale, function ($i) {
                return $i + 1;
            });
        }

        if ($field == ChronoField::AMPM_OF_DAY()) {
            $values = self::tryFetchStyleValues('AmPmMarkers', $style, $locale, function ($i) {
                return $i;
            });
        }

        if ($values === null)
            return null;

        \uksort($values, function ($a, $b) {
            return strlen($b) - strlen($a);
        });

        return $values;
    }

    /**
     * Gets an iterator of text to field for the specified chrono, field, locale and style
     * for the purpose of parsing.
     * <p>
     * The iterator must be returned in order from the longest text to the shortest.
     * <p>
     * The null return value should be used if there is no applicable parsable text, or
     * if the text would be a numeric representation of the value.
     * Text can only be parsed if all the values for that field-style-locale combination are unique.
     *
     * @param Chronology $chrono the Chronology to get text for, not null
     * @param TemporalField $field the field to get text for, not null
     * @param TextStyle $style the style to get text for, null for all parsable text
     * @param Locale $locale the locale to get text for, not null
     * @return array the iterator of text to field pairs, in order from longest text to shortest text,
     *  null if the field or style is not parsable
     */
    public function getTextIterator2(Chronology $chrono, TemporalField $field,
                                     $style, Locale $locale)
    {
        return $this->getTextIterator($field, $style, $locale);
    }
}
