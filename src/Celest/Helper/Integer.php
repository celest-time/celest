<?php
/**
 * Created by IntelliJ IDEA.
 * User: hanikel
 * Date: 17.08.15
 * Time: 17:53
 */

namespace Celest\Helper;


class Integer
{
    const MIN_VALUE = -2147483648;
    const MAX_VALUE = 2147483647;

    /**
     * @param int $x
     * @param int $y
     * @return int
     */
    public static function compare($x, $y)
    {
        return ($x < $y) ? -1 : (($x === $y) ? 0 : 1);
    }

    /**
     * @param string $str
     * @return int
     */
    public static function parseInt($str)
    {
        return (int)$str;
    }

    /**
     * @param int $val
     * @return string
     */
    public static function toString($val)
    {
        return (string)$val;
    }
}