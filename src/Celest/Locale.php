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

    public static function ROOT()
    {
        return new Locale("");
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

    public static function CANADA()
    {
        return new Locale('en_CA');
    }

    public static function FRANCE()
    {
        return new Locale('fr_FR');
    }

    public static function JAPAN()
    {
        return new Locale('ja_JP');
    }

    public static function GERMAN()
    {
        return new Locale('de');
    }

    public static function JAPANESE()
    {
        return new Locale('ja');
    }

    public static function CHINESE()
    {
        return New Locale('zh');
    }

    public function getLocale()
    {
        return $this->locale;
    }

    function __toString()
    {
        return $this->locale;
    }


}