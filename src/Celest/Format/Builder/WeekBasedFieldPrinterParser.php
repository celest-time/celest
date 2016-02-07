<?php

namespace Celest\Format\Builder;

use Celest\Format\DateTimePrintContext;
use Celest\Format\DateTimeParseContext;
use Celest\Format\SignStyle;
use Celest\IllegalArgumentException;
use Celest\Locale;

/**
 * Prints or parses a localized pattern from a localized field.
 * The specific formatter and parameters is not selected until the
 * the field is to be printed or parsed.
 * The locale is needed to select the proper WeekFields from which
 * the field for day-of-week, week-of-month, or week-of-year is selected.
 */
final class WeekBasedFieldPrinterParser implements DateTimePrinterParser
{
    /** @var string */
    private $chr;
    /** @var int */
    private $count;

    /**
     * Constructor.
     *
     * @param string $chr the pattern format letter that added this PrinterParser.
     * @param int $count the repeat count of the format letter
     */
    public function __construct($chr, $count)
    {
        $this->chr = $chr;
        $this->count = $count;
    }

    public function format(DateTimePrintContext $context, &$buf)
    {
        return $this->printerParser($context->getLocale())->format($context, $buf);
    }

    public function parse(DateTimeParseContext $context, $text, $position)
    {
        return $this->printerParser($context->getLocale())->parse($context, $text, $position);
    }

    /**
     * Gets the printerParser to use based on the field and the locale.
     *
     * @param Locale $locale the locale to use, not null
     * @return DateTimePrinterParser the formatter, not null
     * @throws IllegalArgumentException if the formatter cannot be found
     */
    private function printerParser(Locale $locale)
    {
        $weekDef = WeekFields::of($locale);
        $field = null;
        switch ($this->chr) {
            case 'Y':
                $field = $weekDef->weekBasedYear();
                if ($this->count === 2) {
                    return new ReducedPrinterParser($field, 2, 2, 0, ReducedPrinterParser::BASE_DATE(), 0);
                } else {
                    return new NumberPrinterParser($field, $this->count, 19,
                        ($this->count < 4) ? SignStyle::NORMAL() : SignStyle::EXCEEDS_PAD(), -1);
                }
            case
            'e':
            case 'c':
                $field = $weekDef->dayOfWeek();
                break;
            case 'w':
                $field = $weekDef->weekOfWeekBasedYear();
                break;
            case 'W':
                $field = $weekDef->weekOfMonth();
                break;
            default:
                throw new IllegalStateException("unreachable");
        }
        return new NumberPrinterParser($field, ($this->count == 2 ? 2 : 1), 2, SignStyle::NOT_NEGATIVE());
    }

    public function __toString()
    {
        $sb = "Localized(";
        if ($this->chr === 'Y') {
            if ($this->count == 1) {
                $sb .= "WeekBasedYear";
            } else
                if ($this->count == 2) {
                    $sb .= "ReducedValue(WeekBasedYear,2,2,2000-01-01)";
                } else {
                    $sb .= "WeekBasedYear," . $this->count . ","
                        . 19 . ","
                        . (($this->count < 4) ? SignStyle::NORMAL() : SignStyle::EXCEEDS_PAD());
                }
        } else {
            switch ($this->chr) {
                case 'c':
                case 'e':
                    $sb .= "DayOfWeek";
                    break;
                case 'w':
                    $sb .= "WeekOfWeekBasedYear";
                    break;
                case 'W':
                    $sb .= "WeekOfMonth";
                    break;
                default:
                    break;
            }
            $sb .= ",";
            $sb .= $this->count;
        }
        $sb .= ")";
        return $sb;
    }
}