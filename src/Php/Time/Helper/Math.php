<?php

namespace Php\Time\Helper;

final class Math
{
    private function __construct()
    {
    }

    /**
     * @param $val int
     * @return int
     */
    public static function abs($val)
    {
        return abs((int)$val);
    }

    /**
     * TODO overflow check
     * @param $l int
     * @param $r int
     * @return int
     */
    public static function multiplyExact($l, $r)
    {
        return (int)($l * $r);
    }

    /**
     * @param $l int
     * @param $r int
     * @return int
     */
    public static function addExact($l, $r)
    {
        return (int)($l + $r);
    }

    /**
     * @param $dividend int
     * @param $divisor int
     * @return int
     */
    public static function floorMod($dividend, $divisor)
    {
        $res = (int)$dividend % (int)$divisor;
        if($res < 0) {
            return $res + $divisor;
        } else {
            return $res;
        }
    }

    /**
     * @param $dividend int
     * @param $divisor int
     * @return int
     */
    public static function floorDiv($dividend, $divisor)
    {
        return (int)floor($dividend / $divisor);
    }

    /**
     * @param $l int
     * @param $r int
     * @return int
     */
    public static function subtractExact($l, $r)
    {
        return (int)($l - $r);
    }

    /**
     * @param $a int
     * @param $b int
     * @return int
     */
    public static function min($a, $b)
    {
        return min($a, $b);
    }

    /**
     * @param $val int
     * @return int
     */
    public static function toIntExact($val)
    {
        return $val;
    }

    public static function div($dividend, $divisor)
    {
        return (int)($dividend / $divisor);
    }
}