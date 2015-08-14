<?php

namespace Php\Time\Temporal;


class ChronoFieldTest extends \PHPUnit_Framework_TestCase
{
    public function testEquals() {
        $a = ChronoField::DAY_OF_YEAR();
        $b = ChronoField::DAY_OF_YEAR();

        $this->assertTrue($a == $b);
        $this->assertTrue($a === $b);
    }

    public function testSwitch() {

        switch(ChronoField::DAY_OF_YEAR()) {
            case ChronoField::DAY_OF_YEAR():
                $a = true;
                break;
            default:
                $a = false;
                break;

        }

        $this->assertTrue($a);
    }

    public function testSwitch2() {

        switch(ChronoField::SECOND_OF_DAY()) {
            case ChronoField::DAY_OF_YEAR():
                $a = true;
                break;
            default:
                $a = false;
                break;
        }

        $this->assertFalse($a);
    }
}