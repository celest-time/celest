<?php

namespace Php\Time\Format\Builder;
use Php\Time\Format\DateTimePrintContext;
use Php\Time\Format\DateTimeParseContext;

/**
 * Composite printer and parser.
 */
final class CompositePrinterParser implements DateTimePrinterParser
{
    /** @var DateTimePrinterParser[] */
    private $printerParsers;
    /** @var bool */
    private $optional;

    public function __construct($printerParsers, $optional)
    {
        $this->printerParsers = $printerParsers;
        $this->optional = $optional;
    }

    /**
     * Returns a copy of this printer-parser with the optional flag changed.
     *
     * @param bool $optional the optional flag to set in the copy
     * @return CompositePrinterParser the new printer-parser, not null
     */
    public function withOptional($optional)
    {
        if ($optional == $this->optional) {
            return $this;
        }

        return new CompositePrinterParser($this->printerParsers, $optional);
    }

    public function format(DateTimePrintContext $context, &$buf)
    {
        $length = strlen($buf);
        if ($this->optional) {
            $context->startOptional();
        }

        try {
            foreach ($this->printerParsers as $pp) {
                if ($pp->format($context, $buf) === false) {
                    $buf = substr($buf, 0, $length);  // reset buffer
                    return true;
                }
            }
        } finally {
            if ($this->optional) {
                $context->endOptional();
            }
        }
        return true;
    }

    public function parse(DateTimeParseContext $context, $text, $position)
    {
        if ($this->optional) {
            $context->startOptional();
            $pos = $position;
            foreach ($this->printerParsers as $pp) {
                $pos = $pp->parse($context, $text, $pos);
                if ($pos < 0) {
                    $context->endOptional(false);
                    return $position;  // return original position
                }
            }
            $context->endOptional(true);
            return $pos;
        } else {
            foreach ($this->printerParsers as $pp) {
                $position = $pp->parse($context, $text, $position);
                if ($position < 0) {
                    break;
                }
            }
            return $position;
        }
    }

    public function __toString()
    {
        $buf = '';
        if ($this->printerParsers !== null) {
            $buf .= $this->optional ? "[" : "(";
            foreach ($this->printerParsers as $pp) {
                $buf .= $pp;
            }

            $buf .= $this->optional ? "]" : ")";
            }
        return $buf;
    }
}
