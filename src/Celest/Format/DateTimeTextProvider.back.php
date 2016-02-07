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
use Celest\Chrono\IsoChronology;
use Celest\Locale;
use Celest\Temporal\ChronoField;
use Celest\Temporal\TemporalField;

/**
 * Stores the text for a single locale.
 * <p>
 * Some fields have a textual representation, such as day-of-week or month-of-year.
 * These textual representations can be captured in this class for printing
 * and parsing.
 * <p>
 * This class is immutable and thread-safe.
 */
final class LocaleStore
{
    /**
     * Map of value to text.
     */
    // Map<TextStyle, Map<Long, String>>
    private $valueTextMap;
    /**
     * Parsable data.
     */
    // Map<TextStyle, List<Entry<String, Long>>>
    private $parsable;

    /**
     * Constructor.
     *
     * @param array $valueTextMap Map<TextStyle, Map<Long, String>>  the map of values to text to store, assigned and not altered, not null
     */
    public function __construct($valueTextMap)
    {
        $this->valueTextMap = $valueTextMap;
        /*Map < TextStyle, List<Entry < String, Long >>> map = new HashMap <> ();
List<Entry < String, Long >> allList = new ArrayList <> ();
for (Map . Entry < TextStyle, Map < Long, String >> vtmEntry : valueTextMap . entrySet())
{
    Map < String, Entry < String, Long >> reverse = new HashMap <> ();
for (Map . Entry < Long, String > entry : vtmEntry . getValue() . entrySet())
{
    if (reverse . put(entry . getValue(), createEntry(entry . getValue(), entry . getKey())) != null) {
        // TODO: BUG: this has no effect
        continue;  // not parsable, try next style
    }
}
List<Entry < String, Long >> list = new ArrayList <> (reverse . values());
                Collections . sort(list, COMPARATOR);
                map . put(vtmEntry . getKey(), list);
                allList . addAll(list);
                map . put(null, allList);
            }
            Collections . sort(allList, COMPARATOR);
            this . parsable = map;*/
    }

    /**
     * Gets the text for the specified field value, locale and style
     * for the purpose of printing.
     *
     * @param int $value the value to get text for, not null
     * @param TextStyle $style the style to get text for, not null
     * @return string the text for the field value, null if no text found
     */
    function getText($value, TextStyle $style)
    {
        $map = $this->valueTextMap[$style];
        return $map !== null ? @$map[$value] : null;
    }

    /**
     * Gets an iterator of text to field for the specified style for the purpose of parsing.
     * <p>
     * The iterator must be returned in order from the longest text to the shortest.
     *
     * @param TextStyle $style the style to get text for, null for all parsable text
     * @return array the iterator of text to field pairs, in order from longest text to shortest text,
     *  null if the style is not parsable
     */
    function getTextIterator(TextStyle $style)
    {
        $list = $this->parsable[$style];
        return $list;
    }
}

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
        $store = $this->findStore($field, $locale);
        if ($store instanceof LocaleStore) {
            return $store->getText($value, $style);
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
    public
    function getText2(Chronology $chrono, TemporalField $field, $value,
                      TextStyle $style, Locale $locale)
    {
        if ($chrono == IsoChronology::INSTANCE()
            || !($field instanceof ChronoField)
        ) {
            return $this->getText($field, $value, $style, $locale);
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
            $chrono->getCalendarType(), $fieldIndex, $fieldValue, $style->toCalendarStyle(), $locale);
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
    public function getTextIterator(TemporalField $field, TextStyle $style, Locale $locale)
    {
        $store = $this->findStore($field, $locale);
        if ($store instanceof LocaleStore) {
            return $store->getTextIterator($style);
        }

        return null;
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
                                     TextStyle $style, Locale $locale)
    {
        if ($chrono == IsoChronology::INSTANCE()
            || !($field instanceof ChronoField)
        ) {
            return $this->getTextIterator($field, $style, $locale);
        }

        switch ($field) {
            case ChronoField::ERA():
                $fieldIndex = \IntlCalendar::FIELD_ERA;
                break;
            case ChronoField::MONTH_OF_YEAR():
                $fieldIndex = \IntlCalendar::FIELD_MONTH;
                break;
            case ChronoField::DAY_OF_WEEK():
                $fieldIndex = \IntlCalendar::FIELD_DAY_OF_WEEK;
                break;
            case ChronoField::AMPM_OF_DAY():
                $fieldIndex = \IntlCalendar::FIELD_AM_PM;
                break;
            default:
                return null;
        }

        $calendarStyle = ($style == null) ? Calendar::ALL_STYLES : $style->toCalendarStyle();
        $map = CalendarDataUtility::retrieveJavaTimeFieldValueNames(
            $chrono->getCalendarType(), $fieldIndex, $calendarStyle, $locale);
        if ($map == null) {
            return null;
        }
        $list = [];
        switch ($fieldIndex) {
            case \IntlCalendar::FIELD_ERA:
                foreach ($map as $key => $entry) {
                    $era = $entry->getValue();
                    if ($chrono == JapaneseChronology::INSTANCE()) {
                        if ($era == 0) {
                            $era = -999;
                        } else {
                            $era -= 2;
                        }
                    }
                    $list[] = $this->createEntry($key, $era);
                }
                break;
            case \IntlCalendar::FIELD_MONTH:
                foreach ($map as $key => $entry) {
                    $list->add($this->createEntry($key, $entry->getValue() + 1));
                }
                break;
            case \IntlCalendar::FIELD_DAY_OF_WEEK:
                foreach ($map as $key => $entry) {
                    $list->add($this->createEntry($key, $this->toWeekDay($entry->getValue())));
                }
                break;
            default:
                foreach ($map as $key => $entry) {
                    $list->add($this->createEntry($key, $entry->getValue()));
                }
                break;
        }
        return $list;
    }

    private function findStore(TemporalField $field, Locale $locale)
    {
        $key = $this->createEntry($field, $locale);
        $store = @self::$CACHE[$key];
        if ($store == null) {
            $store = $this->createStore($field, $locale);
            self::$CACHE->putIfAbsent($key, $store);
            $store = self::$CACHE->get($key);
        }

        return $store;
    }

    private
    static function toWeekDay($calWeekDay)
    {
        if ($calWeekDay == \IntlCalendar::DOW_SUNDAY) {
            return 7;
        } else {
            return $calWeekDay - 1;
        }
    }

    private
    function createStore(TemporalField $field, Locale $locale)
    {
        // Map < TextStyle, Map < Long, String >> 
        $styleMap = [];
        if ($field == ChronoField::ERA()) {
            foreach (TextStyle::values() as $textStyle) {
                if ($textStyle->isStandalone()) {
                    // Stand-alone isn't applicable to era names.
                    continue;
                }

                $displayNames = CalendarDataUtility::retrieveJavaTimeFieldValueNames(
                    "gregory", \IntlCalendar::FIELD_ERA, $textStyle->toCalendarStyle(), $locale);
                if ($displayNames != null) {
                    $map = [];
                    foreach ($displayNames as $key => $value) {
                        $map[$value] = $key;
                    }
                    if (count($map) > 0) {
                        $styleMap[$textStyle] = $map;
                    }
                }
            }
            return new LocaleStore($styleMap);
        }

        if ($field == ChronoField::MONTH_OF_YEAR()) {
            foreach (TextStyle::values() as $textStyle) {
                $displayNames = CalendarDataUtility::retrieveJavaTimeFieldValueNames(
                    "gregory", \IntlCalendar::FIELD_MONTH, $textStyle->toCalendarStyle(), $locale);
                $map = [];
                if ($displayNames !== null) {
                    foreach ($displayNames as $key => $value) {
                        $map[$value + 1] = $key;
                    }

                } else {
                    // Narrow names may have duplicated names, such as "J" for January, Jun, July.
                    // Get names one by one in that case.
                    for ($month = \IntlCalendar::FIELD_JANUARY; $month <= \IntlCalendar::FIELD_DECEMBER;
                         $month++) {
                        $name = CalendarDataUtility::retrieveJavaTimeFieldValueName(
                            "gregory", \IntlCalendar::FIELD_MONTH, $month, $textStyle->toCalendarStyle(), $locale);
                        if ($name == null) {
                            break;
                        }
                        $map[$month + 1] = $name;
                    }
                }
                if (count($map) > 0) {
                    $styleMap[$textStyle] = $map;
                }
            }
            return new LocaleStore($styleMap);
        }

        if ($field == ChronoField::DAY_OF_WEEK()) {
            for (TextStyle textStyle : TextStyle . values()) {
                Map < String, Integer > displayNames = CalendarDataUtility . retrieveJavaTimeFieldValueNames(
                        "gregory", \IntlCalendar::FIELD_DAY_OF_WEEK, textStyle . toCalendarStyle(), locale);
                Map < Long, String > map = new HashMap <> ();
                if (displayNames != null) {
                    for (Entry < String, Integer > entry : displayNames . entrySet()) {
                        map . put((long)toWeekDay(entry . getValue()), entry . getKey());
                    }

                } else {
                    // Narrow names may have duplicated names, such as "S" for Sunday and Saturday.
                    // Get names one by one in that case.
                    for (int wday = \IntlCalendar::FIELD_SUNDAY; wday <= \IntlCalendar::FIELD_SATURDAY;
                    wday++) {
                        String name;
                        name = CalendarDataUtility . retrieveJavaTimeFieldValueName(
                                "gregory", \IntlCalendar::FIELD_DAY_OF_WEEK, wday, textStyle . toCalendarStyle(), locale);
                        if (name == null) {
                            break;
                        }
                        map . put((long)toWeekDay(wday), name);
                    }
                }
                if (!map . isEmpty()) {
                    styleMap . put(textStyle, map);
                }
            }
            return new LocaleStore(styleMap);
        }

        if ($field == ChronoField::AMPM_OF_DAY()) {
            for (TextStyle textStyle : TextStyle . values()) {
                if (textStyle . isStandalone()) {
                    // Stand-alone isn't applicable to AM/PM.
                    continue;
                }
                Map < String, Integer > displayNames = CalendarDataUtility . retrieveJavaTimeFieldValueNames(
                        "gregory", \IntlCalendar::FIELD_AM_PM, textStyle . toCalendarStyle(), locale);
                if (displayNames != null) {
                    Map < Long, String > map = new HashMap <> ();
                    for (Entry < String, Integer > entry : displayNames . entrySet()) {
                        map . put((long) entry . getValue(), entry . getKey());
                    }
                    if (!map . isEmpty()) {
                        styleMap . put(textStyle, map);
                    }
                }
            }
            return new LocaleStore($styleMap);
        }

        if ($field == IsoFields::QUARTER_OF_YEAR()) {
            // The order of keys must correspond to the TextStyle.values() order.
            $keys = [
                "QuarterNames",
                "standalone.QuarterNames",
                "QuarterAbbreviations",
                "standalone.QuarterAbbreviations",
                "QuarterNarrows",
                "standalone.QuarterNarrows",
            ];
            for ($i = 0; $i < count($keys); $i++) {
                $names = $this->getLocalizedResource($keys[$i], $locale);
                if ($names != null) {
                    Map < Long, String > map = new HashMap <> ();
                    for (int q = 0; q < names . length; q++) {
                        map . put((long) (q + 1), names[q]);
                    }
                    $styleMap . put(TextStyle . values()[i], map);
                }
            }
            return new LocaleStore($styleMap);
        }

        return "";  // null marker for map
    }

    /**
     * Helper method to create an immutable entry.
     *
     * @param $text mixed the text, not null
     * @param $field mixed the field, not null
     * @return array the entry, not null
     */
    private static function createEntry($text, $field)
    {
        return [$text, $field];
    }

    /**
     * Returns the localized resource of the given key and locale, or null
     * if no localized resource is available.
     *
     * @param string $key the key of the localized resource, not null
     * @param Locale $locale the locale, not null
     * @return string the localized resource, or null if not available
     * @throws NullPointerException if key or locale is null
     */
    static function getLocalizedResource($key, Locale $locale)
    {
        // TODO
        /*LocaleResources lr = LocaleProviderAdapter . getResourceBundleBased()
               . getLocaleResources(locale);
       ResourceBundle rb = lr . getJavaTimeFormatData();
       return rb . containsKey(key) ? (T) rb . getObject(key) : null;*/
    }

}
