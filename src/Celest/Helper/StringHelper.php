<?php
/**
 * Created by IntelliJ IDEA.
 * User: hanikel
 * Date: 21.08.15
 * Time: 16:02
 */

namespace Celest\Helper;


final class StringHelper
{
    private function __construct() {}

    /**
     * @param string $needle
     * @param string $haystack
     * @return bool
     */
    public static function startsWith(string $needle, string $haystack) : bool
    {
        return $needle === "" || strrpos($haystack, $needle, -strlen($haystack)) !== false;
    }

}