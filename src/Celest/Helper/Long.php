<?php

namespace Celest\Helper;


use Celest\ArithmeticException;

final class Long
{
    // See https://bugs.php.net/bug.php?id=53934 for -1
    const MIN_VALUE = -9223372036854775807 - 1;
    const MAX_VALUE = 9223372036854775807;

    private function __construct() {}

    public static function compare(int $x, int $y) : int
    {
        return ($x < $y) ? -1 : (($x === $y) ? 0 : 1);
    }

    /**
     * @param string $str
     * @return int
     * @throws ArithmeticException
     */
    public static function parseLong(string $str) : int
    {
        $val = intval($str, 10);

        if ((string)($val) !== $str && '+' . $val !== $str) {
            throw new ArithmeticException();
        }

        return $val;
    }
}