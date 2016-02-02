<?php

namespace Celest;


use PHPUnit_Framework_Assert;

class TestHelper
{
    public static function assertNullException(PHPUnit_Framework_Assert $_this, callable $asd)
    {
        $catched = false;
        try {
            $asd();
        } catch (\PHPUnit_Framework_Error $e) {
            $_this->assertContains('null given', $e->getMessage());
            $catched = true;
        } catch (\InvalidArgumentException $e) {
            $catched = true;
        } catch (\Throwable $e) {
            $_this->assertInstanceOf('\TypeError', $e);
            $catched = true;
        }

        if (!$catched) {
            $_this->fail('Expected Null Exception');
        }
    }

}