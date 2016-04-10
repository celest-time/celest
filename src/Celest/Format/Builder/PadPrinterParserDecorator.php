<?php

namespace Celest\Format\Builder;

use Celest\DateTimeException;
use Celest\Format\DateTimeParseContext;
use Celest\Format\DateTimePrintContext;

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
     * @param DateTimePrinterParser $printerParser the printer, not null
     * @param int $padWidth the width to pad to, 1 or greater
     * @param string $padChar the pad character
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
            $buf = substr_replace($buf, $this->padChar, $preLen, 0);
        }
        return true;
    }

    public function parse(DateTimeParseContext $context, $text, $position)
    {
        // cache context before changed by decorated parser
        $strict = $context->isStrict();
        // parse
        if ($position > strlen($text) || $position < 0) {
            throw new \OutOfRangeException();
        }

        if ($position === strlen($text)) {
            return ~$position;  // no more characters in the string
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
        $text = substr($text, 0, $endPos);
        $resultPos = $this->printerParser->parse($context, $text, $pos);
        if ($resultPos !== $endPos && $strict) {
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
