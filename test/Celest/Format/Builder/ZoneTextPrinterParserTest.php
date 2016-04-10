<?php
/*
 * Copyright (c) 2012, 2015, Oracle and/or its affiliates. All rights reserved.
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

namespace Celest\Format\Builder;

use Celest\Format\DateTimeFormatter;
use Celest\Format\DateTimeFormatterBuilder;
use Celest\Format\DecimalStyle;
use Celest\Format\TextStyle;
use Celest\Locale;
use Celest\Temporal\TemporalQueries;
use Celest\Zone\ZoneRulesProvider;
use Celest\ZonedDateTime;
use Celest\ZoneId;

/**
 * Test ZoneTextPrinterParser
 */
class TestZoneTextPrinterParser extends AbstractTestPrinterParser
{

    protected function getFormatterLocale(Locale $locale, TextStyle $style)
    {
        return (new DateTimeFormatterBuilder())->appendZoneText($style)
            ->toFormatter2($locale)
            ->withDecimalStyle(DecimalStyle::of($locale));
    }

    private function printText(Locale $locale, ZonedDateTime $zdt, TextStyle $style, TimeZone $zone, $expected)
    {
        $result = $this->getFormatterLocale($locale, $style)->format($zdt);
        if ($result !== $expected) {
            if ($result === "FooLocation") { // from rules provider test if same vm
                return;
            }

            echo "----------------\n";
            echo printf("tdz[%s]%n", $zdt->__toString());
            echo printf("[%-5s, %5s] :[%s]%n", $locale->__toString(), $style->__toString(), $result);
            echo printf(" %5s, %5s  :[%s] %s%n", "", "", $expected, $zone);
        }
        $this->assertEquals($result, $expected);
    }

    public function test_ParseText()
    {
        $this->markTestIncomplete('ZoneTextPrinterParser, Localized Zone Names');
        $locales =
            [
                Locale::ENGLISH(), Locale::JAPANESE(), Locale::FRENCH()
            ];
        $zids = ZoneRulesProvider::getAvailableZoneIds();
        foreach ($locales as $locale) {
            $this->parseText($zids, $locale, TextStyle::FULL(), false);
            $this->parseText($zids, $locale, TextStyle::FULL(), true);
            $this->parseText($zids, $locale, TextStyle::SHORT(), false);
            $this->parseText($zids, $locale, TextStyle::SHORT(), true);
        }
    }

    private static function preferred()
    {
        return

            [
                //ZoneId::of("EST", ZoneId::SHORT_IDS),
                ZoneId::of("Asia/Taipei"),
                ZoneId::of("CET"),
            ];
    }

    private static function preferred_s()
    {
        return [
            //ZoneId::of("EST", ZoneId::SHORT_IDS),
            ZoneId::of("CET"),
            ZoneId::of("Australia/South"),
            ZoneId::of("Australia/West"),
            ZoneId::of("Asia/Shanghai"),
        ];
    }

    function data_preferredZones()
    {
        return [
            [
                "America/New_York", "Eastern Standard Time", [], Locale::ENGLISH(), TextStyle::FULL()],
//          ["EST",              "Eastern Standard Time", $preferred, Locale.ENGLISH, TextStyle.FULL],
            [
                "Europe/Paris", "Central European Time", [], Locale::ENGLISH(), TextStyle::FULL()],
            [
                "CET", "Central European Time", self::preferred(), Locale::ENGLISH(), TextStyle::FULL()],
            [
                "Asia/Shanghai", "China Standard Time", [], Locale::ENGLISH(), TextStyle::FULL()],
            [
                "Asia/Taipei", "China Standard Time", self::preferred(), Locale::ENGLISH(), TextStyle::FULL()],
            [
                "America/Chicago", "CST", [], Locale::ENGLISH(), TextStyle::SHORT()],
            [
                "Asia/Taipei", "CST", self::preferred(), Locale::ENGLISH(), TextStyle::SHORT()],
            [
                "Australia/South", "ACST", self::preferred_s(), Locale::ENGLISH(), TextStyle::SHORT()],
            [
                "America/Chicago", "CDT", [], Locale::ENGLISH(), TextStyle::SHORT()],
            [
                "Asia/Shanghai", "CDT", self::preferred_s(), Locale::ENGLISH(), TextStyle::SHORT()],
        ];
    }

    /**
     * @dataProvider data_preferredZones
     */
    public function test_ParseText5($expected, $text, $preferred, Locale $locale, TextStyle $style)
    {
        $this->markTestIncomplete('ZoneTextPrinterParser, Localized Zone Names');
        $fmt = (new DateTimeFormatterBuilder())->appendZoneText2($style, $preferred)
            ->toFormatter2($locale)
            ->withDecimalStyle(DecimalStyle::of($locale));

        $ret = $fmt->parseQuery($text, TemporalQueries::zone())->getId();

        echo printf("[%-5s %s] %24s -> %s(%s)%n",
            $locale->__toString(),
            $style == TextStyle::FULL() ? " full" : "short",
            $text, $ret, $expected);

        $this->assertEquals($ret, $expected);

    }

    private function parseText($zids, Locale $locale, TextStyle $style, $ci)
    {
        echo "---------------------------------------\n";
        $fmt = $this->getFormatter3($locale, $style, $ci);
        foreach ((new DateFormatSymbols($locale))->getZoneStrings() as $names) {
            if (!$zids->contains($names[0])) {
                continue;
            }
            $zid = $names[0];
            $expected = ZoneName::toZid($zid, $locale);

            $this->parse($fmt, $zid, $expected, $zid, $locale, $style, $ci);
            $i = $style == TextStyle::FULL() ? 1 : 2;
            for (; $i < count($names); $i += 2) {
                $this->parse($fmt, $zid, $expected, $names[$i], $locale, $style, $ci);
            }
        }
    }

    private function parse(DateTimeFormatter $fmt,
                           $zid, $expected, $text,
                           Locale $locale, TextStyle $style, $ci)
    {
        if ($ci) {
            $text = $text->toUpperCase();
        }
        /** @var ZoneId $ret */
        $ret = $fmt->parseQuery($text, TemporalQueries::zone())->getId();
        // TBD: need an excluding list
        // assertEquals(...);
        if ($ret->equals($expected) ||
            $ret->equals($zid) ||
            $ret->equals(ZoneName::toZid($zid)) ||
            $ret->equals($expected->replace("UTC", "UCT"))
        ) {
            return;
        }
        echo printf("[%-5s %s %s %16s] %24s -> %s(%s)%n",
            $locale->__toString(),
            $ci ? "ci" : "  ",
            $style == TextStyle::FULL() ? " full" : "short",
            $zid, $text, $ret, $expected);
    }

    protected function getFormatter3(Locale $locale, TextStyle $style, $ci)
    {
        $db = new DateTimeFormatterBuilder();
        if ($ci) {
            $db = $db->parseCaseInsensitive();
        }
        return $db->appendZoneText($style)
            ->toFormatter2($locale)
            ->withDecimalStyle(DecimalStyle::of($locale));
    }

}
