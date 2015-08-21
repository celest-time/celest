<?php

namespace Php\Time\Format;


class DateTimeFormatter
{
    const ISO_LOCAL_DATE_TIME = null;

    public static function ofPattern($string)
    {
        return new DateTimeFormatter();
    }

    public function format($tmp)
    {
        return "";
    }

    public function parse($text, $from)
    {
        return null;
    }
}