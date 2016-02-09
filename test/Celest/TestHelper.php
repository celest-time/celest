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

    public static function getRussianJanFormat() {
        if (version_compare(INTL_ICU_DATA_VERSION, "52", "<")) {
            return 'янв.';
        } else if (version_compare(INTL_ICU_DATA_VERSION, "54", "<")) {
            return 'Янв.';
        } else {
            return 'янв.';
        }
    }

    public static function getRussian()
    {
        if (version_compare(INTL_ICU_DATA_VERSION, "54", "<")) {
            return [
                'january' => 'Январь',
                'december' => 'Декабрь',
                'jan.' => 'Янв.',
                'dec.' => 'Дек.'
            ];
        } else {
            return [
                'january' => 'январь',
                'december' => 'декабрь',
                'jan.' => 'янв.',
                'dec.' => 'дек.'
            ];
        }
    }

    public static function getRussianJanuary()
    {
        return self::getRussian()['january'];
    }

    public static function getRussianJan()
    {
        return self::getRussian()['jan.'];
    }

    public static function getRussianDecember()
    {
        return self::getRussian()['december'];
    }

    public static function getRussianDec()
    {
        return self::getRussian()['dec.'];
    }

    /** @var \Transliterator */
    static $to_upper;

    public static function toUpperMb($str)
    {
        if (self::$to_upper === null)
            self::$to_upper = \Transliterator::create('Any-Upper');
        return self::$to_upper->transliterate($str);
    }

    /** @var \Transliterator */
    static $to_lower;

    public static function toLowerMb($str)
    {
        if (self::$to_lower === null)
            self::$to_lower = \Transliterator::create('Any-Lower');
        return self::$to_lower->transliterate($str);
    }
}