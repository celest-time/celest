<?php

namespace Php\Time;

use PHPUnit_Framework_TestCase;

class YearTest extends PHPUnit_Framework_TestCase
{
    public function testAsd() {
        $this->assertTrue(Year::of(2000)->isLeap());
        $this->assertTrue(Year::isLeapYear(2000));
        $year = Year::of(2000);
        $year->atDay(20);
    }

}