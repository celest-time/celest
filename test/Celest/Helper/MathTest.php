<?php
/**
 * Created by IntelliJ IDEA.
 * User: steffen
 * Date: 24.01.16
 * Time: 15:33
 */

namespace Celest\Helper;


class MathTest extends \PHPUnit_Framework_TestCase
{
    public function testLong() {
        $this->assertTrue(Long::MAX_VALUE === PHP_INT_MAX);
    }

    public function testFloorDiv()
    {
        $this->assertEquals(1, Math::div(4, 3));
        $this->assertEquals(1, Math::floorDiv(4, 3));

        $this->assertEquals(-1, Math::div(-4, 3));
        $this->assertEquals(-2, Math::floorDiv(-4, 3));
    }

    public function testFloorDivMaxEpoch() {
        $this->assertEquals(365241780471, Math::floorDiv(31556889832780799, 86400));
    }
}