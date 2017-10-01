<?php

namespace Celest\Helper;

use Celest\ArithmeticException;

final class Math
{
    private function __construct() {}

    public static function binarySearch(array $a, $key) : int
    {
        $low = 0;
        $high = count($a) - 1;

        while ($low <= $high) {
            $mid = ($low + $high) >> 1;
            $midVal = $a[$mid];

            if ($midVal < $key)
                $low = $mid + 1;
            else if ($midVal > $key)
                $high = $mid - 1;
            else
                return $mid; // key found
        }
        return -($low + 1);  // key not found.
    }

    /**
     * @param int $val
     * @return int
     */
    public static function abs(int $val) : int
    {
        return \abs($val);
    }

    /**
     * @param int $l
     * @param int $r
     * @return int
     * @throws ArithmeticException
     */
    public static function multiplyExact(int $l, int $r) : int
    {
        $res = $l * $r;
        // HD 2-12 Overflow iff both arguments have the opposite sign of the result
        if (!\is_int($res)) {
            throw new ArithmeticException("integer overflow");
        }
        return $res;
    }

    /**
     * @param int $l
     * @param int $r
     * @return int
     * @throws ArithmeticException
     */
    public static function addExact(int $l, int $r) : int
    {
        $res = $l + $r;
        if (!\is_int($res)) {
            throw new ArithmeticException("integer overflow");
        }
        return $res;
    }

    /**
     * @param int $dividend
     * @param int $divisor
     * @return int
     */
    public static function floorMod(int $dividend, int $divisor) : int
    {
        return $dividend - Math::floorDiv($dividend, $divisor) * $divisor;
    }

    /**
     * @param int $dividend
     * @param int $divisor
     * @return int
     */
    public static function floorDiv(int $dividend, int $divisor) : int
    {
        $r = \intdiv($dividend, $divisor);

        if (($dividend ^ $divisor) < 0 && ($r * $divisor !== $dividend)) {
            $r--;
        }
        return $r;
    }

    /**
     * @param int $l
     * @param int $r
     * @return int
     */
    public static function subtractExact(int $l, int $r) : int
    {
        return $l - $r;
    }

    /**
     * @param int $a
     * @param int $b
     * @return int
     */
    public static function min(int $a, int $b) : int
    {
        return \min($a, $b);
    }

    /**
     * @param int $val
     * @return int
     */
    public static function toIntExact(int $val) : int
    {
        return $val;
    }

    public static function max(int $a, int $b) : int
    {
        return \max($a, $b);
    }
}