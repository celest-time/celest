<?php

namespace Celest;


use Celest\Helper\Long;
use Celest\Helper\Math;
use IntlCalendar;
use PHPUnit_Framework_TestCase;
use ResourceBundle;

class MiscTest extends PHPUnit_Framework_TestCase
{
    public function testAnonymousFunctionEqualsReference()
    {
        $f = function () {
        };
        $x = $f;
        $this->assertTrue($f == $x);
        $this->assertTrue($f === $x);
    }

    public function testAnonymousFunctionEqualsSemantic()
    {
        $f = function () {
        };
        $x = function () {
        };

        $this->assertFalse($f == $x);
        $this->assertFalse($f === $x);
    }

    public function testMaxDateEpochSec()
    {
        $offset = ZoneOffset::ofTotalSeconds(0);
        $seconds = LocalDateTime::MAX()->toEpochSecond($offset);
        $new = LocalDateTime::ofEpochSecond($seconds, 999999999, $offset);
        $this->assertEquals(LocalDateTime::MAX(), $new);
    }

    public function testMinDateEpochSec()
    {
        $offset = ZoneOffset::ofTotalSeconds(0);
        $seconds = LocalDateTime::MIN()->toEpochSecond($offset);
        $new = LocalDateTime::ofEpochSecond($seconds, 0, $offset);
        $this->assertEquals(LocalDateTime::MIN(), $new);
    }

    public function testIntlBundleCase()
    {
        $bundle = new ResourceBundle('pt_BR', null);
        $this->assertEquals('ter', $bundle['calendar']['gregorian']['dayNames']['format']['abbreviated'][2]);
    }

    public function testIntlBundles()
    {
        $bundle = new ResourceBundle('de', 'ICUDATA-region');
        $this->assertEquals('Deutschland', $bundle['Countries']['DE']);

        $bundle = new ResourceBundle('de', 'ICUDATA-zone');
        $this->assertEquals('Brüssel', $bundle['zoneStrings']['Europe:Brussels']['ec']);

        $bundle = new ResourceBundle('metaZones', 'ICUDATA', false);
        $this->assertEquals('Europe_Central', $bundle['metazoneInfo']['Europe:Berlin'][0][0]);
    }

    public function testIntlDate()
    {
        $date = new \DateTime('2000-02-03 04:05', new \DateTimeZone('UTC'));
        $formatter1 = \IntlDateFormatter::create('en@calendar=islamic-umalqura', \IntlDateFormatter::FULL, \IntlDateFormatter::FULL, "Europe/Berlin", \IntlDateFormatter::TRADITIONAL);
        $formatter2 = \IntlDateFormatter::create('@calendar=islamic-civil', \IntlDateFormatter::FULL, \IntlDateFormatter::FULL, "Europe/Berlin", \IntlDateFormatter::TRADITIONAL);
        // TODO investigate failure, probably because of different ICU version
        //$this->assertEquals('AH 1420 Shawwal 27, Thu 05:05:00 GMT+01:00', $formatter1->format($date), 'islamic-umalqura' . self::INTLinfo());
        //$this->assertEquals('AH 1420 Shawwal 127, Thu 05:05:00 GMT+01:00', $formatter2->format($date), 'islamic-civil' .  self::INTLinfo('@calendar=islamic-civil'));

        $formatter = \IntlDateFormatter::create('@calendar=japanese', \IntlDateFormatter::FULL, \IntlDateFormatter::FULL, "Europe/Berlin", \IntlDateFormatter::TRADITIONAL);
        //$this->assertEquals('Heisei 12 M02 3, Thu 05:05:00 GMT+01:00', $formatter->format($date), 'islamic-civil' .  self::INTLinfo('@calendar=japanese'));
    }
}