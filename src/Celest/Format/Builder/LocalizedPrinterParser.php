<?php

namespace Celest\Format\Builder;

use Celest\Chrono\ChronologyDefaults;
use Celest\Format\FormatStyle;
use Celest\Format\DateTimeFormatter;
use Celest\Format\DateTimePrintContext;
use Celest\Chrono\Chronology;
use Celest\Format\DateTimeParseContext;
use Celest\IllegalArgumentException;
use Celest\Format\DateTimeFormatterBuilder;
use Celest\Locale;


/**
 * Prints or parses a localized pattern.
 */
final class LocalizedPrinterParser implements DateTimePrinterParser
{
    /** Cache of formatters.
     * @var DateTimeFormatter[]
     */
    private static $FORMATTER_CACHE = [];

    /** @var FormatStyle */
    private $dateStyle;
    /** @var FormatStyle */
    private $timeStyle;

    /**
     * Constructor.
     *
     * @param FormatStyle $dateStyle the date style to use, may be null
     * @param FormatStyle $timeStyle the time style to use, may be null
     */
    public function __construct($dateStyle, $timeStyle)
    {
        // validated by caller
        $this->dateStyle = $dateStyle;
        $this->timeStyle = $timeStyle;
    }

    public function format(DateTimePrintContext $context, &$buf)
    {
        $chrono = ChronologyDefaults::from($context->getTemporal());
        return $this->formatter($context->getLocale(), $chrono)->toPrinterParser(false)->format($context, $buf);
    }

    public function parse(DateTimeParseContext $context, $text, $position)
    {
        $chrono = $context->getEffectiveChronology();
        return $this->formatter($context->getLocale(), $chrono)->toPrinterParser(false)->parse($context, $text, $position);
    }

    /**
     * Gets the formatter to use.
     * <p>
     * The formatter will be the most appropriate to use for the date and time style in the locale.
     * For example, some locales will use the month name while others will use the number.
     *
     * @param Locale $locale the locale to use, not null
     * @param Chronology $chrono the chronology to use, not null
     * @return DateTimeFormatter the formatter, not null
     * @throws IllegalArgumentException if the formatter cannot be found
     */
    private
    function formatter(Locale $locale, Chronology $chrono)
    {
        $key = $chrono->getId() . '|' . $locale . '|' . $this->dateStyle . '|' . $this->timeStyle;
        $formatter = @self::$FORMATTER_CACHE[$key];
        if ($formatter === null) {
            $pattern = DateTimeFormatterBuilder::getLocalizedDateTimePattern($this->dateStyle, $this->timeStyle, $chrono, $locale);
            $formatter = (new DateTimeFormatterBuilder())->appendPattern($pattern)->toFormatter2($locale);
            $old = self::$FORMATTER_CACHE[$key] = $formatter;
            if ($old !== null) {
                $formatter = $old;
            }
        }
        return $formatter;
    }

    public function __toString()
    {
        return "Localized(" . ($this->dateStyle != null ? $this->dateStyle : "") . "," .
        ($this->timeStyle != null ? $this->timeStyle : "") . ")";
    }
}