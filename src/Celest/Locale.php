<?php declare(strict_types=1);

namespace Celest;


class Locale
{
    private $locale;

    public static function of(string $language, string $region = '') : Locale
    {
        return new Locale(\Locale::composeLocale([
            'language' => $language,
            'region' => $region,
        ]));
    }

    private function __construct(string $locale)
    {
        $this->locale = $locale;
    }

    public static function getDefault() : Locale
    {
        return new Locale(\Locale::getDefault());
    }

    public static function ROOT() : Locale
    {
        return new Locale("");
    }

    public static function ENGLISH() : Locale
    {
        return new Locale("en");
    }

    public static function UK() : Locale
    {
        return new Locale("en_UK");
    }

    public static function US() : Locale
    {
        return new Locale("en_US");
    }

    public static function FRENCH() : Locale
    {
        return new Locale('fr');
    }

    public static function CANADA() : Locale
    {
        return new Locale('en_CA');
    }

    public static function FRANCE() : Locale
    {
        return new Locale('fr_FR');
    }

    public static function JAPAN() : Locale
    {
        return new Locale('ja_JP');
    }

    public static function GERMAN() : Locale
    {
        return new Locale('de');
    }

    public static function JAPANESE() : Locale
    {
        return new Locale('ja');
    }

    public static function CHINESE() : Locale
    {
        return New Locale('zh');
    }

    public function getLocale() : string
    {
        return $this->locale;
    }

    function __toString() : string
    {
        return $this->locale;
    }


}