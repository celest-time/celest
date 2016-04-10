<?php
/**
 * Created by IntelliJ IDEA.
 * User: hanikel
 * Date: 21.08.15
 * Time: 16:02
 */

namespace Celest\Helper;


class StringHelper
{
    /**
     * @param $needle
     * @param $haystack
     * @return bool
     */
    public static function startsWith($needle, $haystack)
    {
        return $needle === "" || strrpos($haystack, $needle, -strlen($haystack)) !== false;
    }

}