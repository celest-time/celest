<?php

namespace Celest;


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

    public static function ENGLISH()
    {
        return new Locale();
    }
}