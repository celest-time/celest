<?php

namespace Celest;


class Locale
{
    private $locale;

    public static function of($language, $region = '')
    {
        return new Locale(\Locale::composeLocale([
            'language' => $language,
            'region' => $region,
        ]));
    }

    private function __construct($locale) {
        $this->locale = $locale;
    }

    public static function getDefault()
    {
        return new Locale(\Locale::getDefault());
    }

    public static function ENGLISH()
    {
        return new Locale("en");
    }

    public static function UK()
    {
        return new Locale("en_UK");
    }

    public static function US()
    {
        return new Locale("en_US");
    }

    public static function FRENCH()
    {
        return new Locale('fr');
    }

    public function getLocale()
    {
        return $this->locale;
    }
}