<?php

namespace Celest\TzData;

class TzData
{
    public static function load($file) {
        return include __DIR__ . '/tzdata/' . $file;
    }
}