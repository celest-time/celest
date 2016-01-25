<?php

namespace Php\Time\Format\Builder;

use Php\Time\Format\DateTimeParseContext;
use Php\Time\Format\DateTimeTextProvider;
use Php\Time\Temporal\TemporalField;
use Php\Time\Format\TextStyle;
use Php\Time\Format\DateTimePrintContext;
use Php\Time\Format\SignStyle;
use Php\Time\Temporal\TemporalQueries;
use Php\Time\Chrono\IsoChronology;

/**
 * Prints or parses field text.
 */
final class TextPrinterParser implements DateTimePrinterParser
{
    /** @var TemporalField */
    private $field;
    /** @var TextStyle */
    private $textStyle;
    /** @var DateTimeTextProvider */
    private $provider;
    /**
     * The cached number printer parser.
     * Immutable and volatile, so no synchronization needed.
     * @var NumberPrinterParser
     */

    private $numberPrinterParser;

    /**
     * Constructor.
     *
     * @param TemporalField $field the field to output, not null
     * @param TextStyle $textStyle the text style, not null
     * @param DateTimeTextProvider $provider the text provider, not null
     */
    public function __construct(TemporalField $field, TextStyle $textStyle, DateTimeTextProvider $provider)
    {
        // validated by caller
        $this->field = $field;
        $this->textStyle = $textStyle;
        $this->provider = $provider;
    }

    public function format(DateTimePrintContext $context, &$buf)
    {
        $value = $context->getValue($this->field);
        if ($value == null) {
            return false;
        }

        $text = null;
        $chrono = $context->getTemporal()->query(TemporalQueries::chronology());
        if ($chrono == null || $chrono == IsoChronology::INSTANCE()) {
            $text = $this->provider->getText($this->field, $value, $this->textStyle, $context->getLocale());
        } else {
            $text = $this->provider->getText($chrono, $this->field, $value, $this->textStyle, $context->getLocale());
        }
        if ($text == null) {
            return $this->numberPrinterParser()->format($context, $buf);
        }
        $buf .= $text;
            return true;
        }

    public function parse(DateTimeParseContext $context, $parseText, $position)
    {
        $length = strlen($parseText);
        if ($position < 0 || $position > $length) {
            throw new IndexOutOfBoundsException();
        }

        $style = ($context->isStrict() ? $this->textStyle : null);
        $chrono = $context->getEffectiveChronology();
        $it = null;
        if ($chrono == null || $chrono == IsoChronology::INSTANCE()) {
            $it = $this->provider->getTextIterator($this->field, $style, $context->getLocale());
        } else {
            $it = $this->provider->getTextIterator($chrono, $this->field, $style, $context->getLocale());
        }
        if ($it != null) {
            while ($it->hasNext()) {
                $entry = $it->next();
                $itText = $entry->getKey();
                if ($context->subSequenceEquals($itText, 0, $parseText, $position, strlen($itText))) {
                    return $context->setParsedField($this->field, $entry->getValue(), $position, $position + strlen($itText));
                }
            }
            if ($context->isStrict()) {
                return ~$position;
            }
        }
        return $this->numberPrinterParser()->parse($context, $parseText, $position);
    }

    /**
     * Create and cache a number printer parser.
     * @return NumberPrinterParser the number printer parser for this field, not null
     */
    private function numberPrinterParser()
    {
        if ($this->numberPrinterParser == null) {
            $this->numberPrinterParser = new NumberPrinterParser($this->field, 1, 19, SignStyle::NORMAL());
        }
        return $this->numberPrinterParser;
    }

    public function __toString()
    {
        if ($this->textStyle == TextStyle::FULL()) {
            return "Text(" . $this->field . ")";
        }
        return "Text(" . $this->field . "," . $this->textStyle . ")";
    }
}