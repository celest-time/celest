<?php

namespace Php\Time\Format\Builder;
use Php\Time\Format\DateTimeParseContext;
use Php\Time\Format\DateTimePrintContext;

/**
 * Enumeration to apply simple parse settings.
 */
class SettingsParser implements DateTimePrinterParser
{
    public static $SENSITIVE;
    public static function SENSITIVE()
    {
        if(self::$SENSITIVE === null)
            self::$SENSITIVE = new SettingsParser(0);

        return self::$SENSITIVE;
    }

    public static $INSENSITIVE;
    public static function INSENSITIVE()
    {
        if(self::$INSENSITIVE === null)
            self::$INSENSITIVE = new SettingsParser(1);

        return self::$INSENSITIVE;
    }

    public static $STRICT;
    public static function STRICT()
    {
        if(self::$STRICT === null)
            self::$STRICT = new SettingsParser(2);

        return self::$STRICT;
    }

    public static $LENIENT;
    public static function LENIENT()
    {
        if(self::$LENIENT === null)
            self::$LENIENT = new SettingsParser(3);

        return self::$LENIENT;
    }

    private $ordinal;

    private function __construct($ordinal)
    {
        $this->ordinal = $ordinal;
    }

    public function format(DateTimePrintContext $context, &$buf)
    {
        return true;  // nothing to do here
    }

    public function parse(DateTimeParseContext $context, $text, $position)
    {
        // using ordinals to avoid javac synthetic inner class
        switch ($this->ordinal) {
            case 0:
                $context->setCaseSensitive(true);
                break;
            case 1:
                $context->setCaseSensitive(false);
                break;
            case 2:
                $context->setStrict(true);
                break;
            case 3:
                $context->setStrict(false);
                break;
        }

        return $position;
    }

    public function __toString()
    {
        // using ordinals to avoid javac synthetic inner class
        switch ($this->ordinal) {
            case 0:
                return "ParseCaseSensitive(true)";
            case 1:
                return "ParseCaseSensitive(false)";
            case 2:
                return "ParseStrict(true)";
            case 3:
                return "ParseStrict(false)";
        }

        throw new \RuntimeException("Unreachable");
    }
}
