<?php

namespace Php\Time\Format\Builder;
use Php\Time\Format\DateTimePrintContext;
use Php\Time\DateTimeException;
use Php\Time\Format\DateTimeParseContext;

/**
 * Pads the output to a fixed width.
 */
final class PadPrinterParserDecorator implements DateTimePrinterParser
{
    /** @var DateTimePrinterParser */
    private $printerParser;
    /** @var int */
    private $padWidth;
    /** @var string */
    private $padChar;

    /**
     * Constructor.
     *
     * @param $printerParser DateTimePrinterParser the printer, not null
     * @param $padWidth int the width to pad to, 1 or greater
     * @param $padChar string the pad character
     */
    public function __construct(DateTimePrinterParser $printerParser, $padWidth, $padChar)
    {
        // input checked by DateTimeFormatterBuilder
        $this->printerParser = $printerParser;
        $this->padWidth = $padWidth;
        $this->padChar = $padChar;
    }

    public function format(DateTimePrintContext $context, &$buf)
    {
        $preLen = strlen($buf);
        if ($this->printerParser->format($context, $buf) === false) {
            return false;
        }

        $len = strlen($buf) - $preLen;
        if ($len > $this->padWidth) {
            throw new DateTimeException(
                "Cannot print as output of " . $len . " characters exceeds pad width of " . $this->padWidth);
        }
        for ($i = 0; $i < $this->padWidth - $len; $i++) {
            $buf->insert($preLen, $this->padChar);
        }
        return true;
    }

    public function parse(DateTimeParseContext $context, $text, $position)
    {
        // cache context before changed by decorated parser
        $strict = $context->isStrict();
        // parse
        if ($position > strlen($text)) {
            throw new IndexOutOfBoundsException();
        }

        if ($position == strlen($text)) {
            return $position;  // no more characters in the string
        }
        $endPos = $position + $this->padWidth;
        if ($endPos > strlen($text)) {
            if ($strict) {
                return ~$position;  // not enough characters in the string to meet the parse width
            }
            $endPos = strlen($text);
        }
        $pos = $position;
        while ($pos < $endPos && $context->charEquals($text[$pos], $this->padChar)) {
            $pos++;
        }
        $text = $text->subSequence(0, $endPos);
        $resultPos = $this->printerParser->parse($context, $text, $pos);
        if ($resultPos != $endPos && $strict) {
            return ~($position + $pos);  // parse of decorated field didn't parse to the end
        }
        return $resultPos;
    }

    public function __toString()
    {
        return "Pad(" . $this->printerParser . "," . $this->padWidth . ($this->padChar === ' ' ? ")" : ",'" . $this->padChar . "')");
    }
}

//-----------------------------------------------------------------------
/**
 * Enumeration to apply simple parse settings.
 */
class SettingsParser implements DateTimePrinterParser
{
    //SENSITIVE,
    // INSENSITIVE,
    //STRICT,
    //LENIENT;

    public function format(DateTimePrintContext $context, &$buf)
    {
        return true;  // nothing to do here
    }

    public function parse(DateTimeParseContext $context, $text, $position)
    {
        // using ordinals to avoid javac synthetic inner class
        switch ($this->ordinal()) {
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
        switch ($this->ordinal()) {
            case 0:
                return "ParseCaseSensitive(true)";
            case 1:
                return "ParseCaseSensitive(false)";
            case 2:
                return "ParseStrict(true)";
            case 3:
                return "ParseStrict(false)";
        }

        throw new IllegalStateException("Unreachable");
    }
}
