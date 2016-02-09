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

    const RUSSIAN_LOWER = [
        'january' => 'январь',
        'december' => 'декабрь',
        'jan.' => 'янв.',
        'dec.' => 'дек.'
    ];

    const RUSSIAN_UPPER = [
        'january' => 'Январь',
        'december' => 'Декабрь',
        'jan.' => 'Янв.',
        'dec.' => 'Дек.'
    ];

    public static function getRussianJanuary()
    {
        if (INTL_ICU_DATA_VERSION < 54) {
            return self::RUSSIAN_UPPER['january'];
        } else {
            return self::RUSSIAN_LOWER['january'];
        }
    }

    public static function getRussianJan()
    {
        if (INTL_ICU_DATA_VERSION < 54) {
            return self::RUSSIAN_UPPER['jan.'];
        } else {
            return self::RUSSIAN_LOWER['jan.'];
        }
    }

    public static function getRussianDecember()
    {
        if (INTL_ICU_DATA_VERSION < 54) {
            return self::RUSSIAN_UPPER['december'];
        } else {
            return self::RUSSIAN_LOWER['december'];
        }
    }

    public static function getRussianDec()
    {
        if (INTL_ICU_DATA_VERSION < 54) {
            return self::RUSSIAN_UPPER['dec.'];
        } else {
            return self::RUSSIAN_LOWER['dec.'];
        }
    }

    /** @var \Transliterator */
    static $to_upper;

    public static function toUpperMb($str) {
        if(self::$to_upper === null)
            self::$to_upper = \Transliterator::create('Any-Upper');
        return self::$to_upper->transliterate($str);
    }

    /** @var \Transliterator */
    static $to_lower;

    public static function toLowerMb($str) {
        if(self::$to_lower === null)
            self::$to_lower = \Transliterator::create('Any-Lower');
        return self::$to_lower->transliterate($str);
    }
}