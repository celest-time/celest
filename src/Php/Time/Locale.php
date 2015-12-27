<?php

namespace Php\Time;


class Locale
{
    public $FORMAT;

    public static function Category() {
        return new Locale();
    }

    public static function getDefault($FORMAT)
    {
        return new Locale();
    }
}