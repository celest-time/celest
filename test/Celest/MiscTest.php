<?php

namespace Celest;


use Celest\Helper\Long;
use Celest\Helper\Math;
use PHPUnit_Framework_TestCase;

class MiscTest extends PHPUnit_Framework_TestCase
{
    public function testAnonymousFunctionEqualsReference() {
        $f = function () {};
        $x = $f;
        $this->assertTrue($f == $x);
        $this->assertTrue($f === $x);
    }

    public function testAnonymousFunctionEqualsSemantic() {
        $f = function () {};
        $x = function () {};

        $this->assertFalse($f == $x);
        $this->assertFalse($f === $x);
    }

    public function testMaxDateEpochSec() {
        $offset = ZoneOffset::ofTotalSeconds(0);
        $seconds = LocalDateTime::MAX()->toEpochSecond($offset);
        $new = LocalDateTime::ofEpochSecond($seconds, 999999999, $offset);
        $this->assertEquals(LocalDateTime::MAX(), $new);
    }

    public function testMinDateEpochSec() {
        $offset = ZoneOffset::ofTotalSeconds(0);
        $seconds = LocalDateTime::MIN()->toEpochSecond($offset);
        $new = LocalDateTime::ofEpochSecond($seconds, 0, $offset);
        $this->assertEquals(LocalDateTime::MIN(), $new);
    }
}