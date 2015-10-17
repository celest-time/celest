<?php

namespace Php\Time\Format\Builder;

use Php\Time\Format\TextStyle;
use Php\Time\Format\DateTimePrintContext;
use Php\Time\Temporal\ChronoField;
use Php\Time\Helper\Math;
use Php\Time\Format\DateTimeParseContext;

/**
 * Prints or parses an offset ID.
 */
final class LocalizedOffsetIdPrinterParser implements DateTimePrinterParser
{
    /** @var TextStyle */
    private $style;

    /**
     * Constructor.
     *
     * @param $style TextStyle the style, not null
     */
    public function __construct(TextStyle $style)
    {
        $this->style = $style;
    }

    private
    static function appendHMS(&$buf, $t)
    {
        $buf .= ($t / 10 + '0') . (t % 10 + '0');
    }

    public function format(DateTimePrintContext $context, &$buf)
    {
        $offsetSecs = $context->getValue(ChronoField::OFFSET_SECONDS());
        if ($offsetSecs == null) {
            return false;
        }

        $gmtText = "GMT";  // TODO: get localized version of 'GMT'
        if ($gmtText != null) {
            $buf .= $gmtText;
        }
        $totalSecs = Math::toIntExact($offsetSecs);
        if ($totalSecs != 0) {
            $absHours = Math::abs(($totalSecs / 3600) % 100);  // anything larger than 99 silently dropped
            $absMinutes = Math::abs(($totalSecs / 60) % 60);
            $absSeconds = Math::abs($totalSecs % 60);
            $buf .= $totalSecs < 0 ? "-" : "+";
                if ($this->style == TextStyle::FULL()) {
                    $this->appendHMS($buf, $absHours);
                    $buf .= ':';
                    $this->appendHMS($buf, $absMinutes);
                    if ($absSeconds != 0) {
                        $buf .= ':';
                        $this->appendHMS($buf, $absSeconds);
                    }
                } else {
                    if ($absHours >= 10) {
                        $buf .= ($absHours / 10 + '0');
                    }
                    $buf .= ($absHours % 10 + '0');
                    if ($absMinutes != 0 || $absSeconds != 0) {
                        $buf .= ':';
                        $this->appendHMS($buf, $absMinutes);
                        if ($absSeconds != 0) {
                            $buf .= ':';
                            self::appendHMS($buf, $absSeconds);
                        }
                    }
                }
            }
        return true;
    }

    private function getDigit($text, $position)
    {
        $c = $text[$position];
        if ($c < '0' || $c > '9') {
            return -1;
        }

        return $c - '0';
    }

    public function parse(DateTimeParseContext $context, $text, $position)
    {
        $pos = $position;
        $end = $pos + strlen($text);
        $gmtText = "GMT";  // TODO: get localized version of 'GMT'
        if ($gmtText != null) {
            if (!$context->subSequenceEquals($text, $pos, $gmtText, 0, strlen($gmtText))) {
                return ~$position;
            }

            $pos += strlen($gmtText);
        }
        // parse normal plus/minus offset
        $negative = 0;
        if ($pos == $end) {
            return $context->setParsedField(ChronoField::OFFSET_SECONDS(), 0, $position, $pos);
        }
        $sign = $text[$pos];  // IOOBE if invalid position
        if ($sign == '+') {
            $negative = 1;
        } else if ($sign == '-') {
            $negative = -1;
        } else {
            return $context->setParsedField(ChronoField::OFFSET_SECONDS(), 0, $position, $pos);
        }
        $pos++;
        $h = 0;
        $m = 0;
        $s = 0;
        if ($this->style == TextStyle::FULL()) {
            $h1 = $this->getDigit($text, $pos++);
            $h2 = $this->getDigit($text, $pos++);
            if ($h1 < 0 || $h2 < 0 || $text[$pos++] !== ':') {
                return ~$position;
            }
            $h = $h1 * 10 + $h2;
            $m1 = $this->getDigit($text, $pos++);
            $m2 = $this->getDigit($text, $pos++);
            if ($m1 < 0 || $m2 < 0) {
                return ~$position;
            }
            $m = $m1 * 10 + $m2;
            if ($pos + 2 < $end && $text[$pos] === ':') {
                $s1 = $this->getDigit($text, $pos + 1);
                $s2 = $this->getDigit($text, $pos + 2);
                if ($s1 >= 0 && $s2 >= 0) {
                    $s = $s1 * 10 + $s2;
                    $pos += 3;
                }
            }
        } else {
            $h = $this->getDigit($text, $pos++);
            if ($h < 0) {
                return ~$position;
            }
            if ($pos < $end) {
                $h2 = $this->getDigit($text, $pos);
                if ($h2 >= 0) {
                    $h = $h * 10 + $h2;
                    $pos++;
                }
                if ($pos + 2 < $end && $text[$pos] === ':') {
                    if ($pos + 2 < $end && $text[$pos] === ':') {
                        $m1 = $this->getDigit($text, $pos + 1);
                        $m2 = $this->getDigit($text, $pos + 2);
                        if ($m1 >= 0 && $m2 >= 0) {
                            $m = $m1 * 10 + $m2;
                            $pos += 3;
                            if ($pos + 2 < end && $text[$pos] === ':') {
                                $s1 = $this->getDigit($text, $pos + 1);
                                $s2 = $this->getDigit($text, $pos + 2);
                                if ($s1 >= 0 && $s2 >= 0) {
                                    $s = $s1 * 10 + $s2;
                                    $pos += 3;
                                }
                            }
                        }
                    }
                }
            }
        }
        $offsetSecs = $negative * ($h * 3600 + $m * 60 + $s);
        return $context->setParsedField(ChronoField::OFFSET_SECONDS(), $offsetSecs, $position, $pos);
    }

    public function __toString()
    {
        return "LocalizedOffset(" . $this->style . ")";
    }
}
