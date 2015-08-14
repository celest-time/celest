<?php

namespace Php\Time;


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
}