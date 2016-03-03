<?php

namespace Celest\Format\Builder;

use Celest\DateTimeException;
use Celest\Format\DateTimeFormatterBuilder;
use Celest\Format\DecimalStyle;
use Celest\Format\SignStyle;
use Celest\Format\TextStyle;
use Celest\LocalDateTime;
use Celest\Locale;
use Celest\Temporal\AbstractTemporalAccessor;
use Celest\Temporal\TemporalAccessor;
use Celest\Temporal\TemporalField;
use Celest\Temporal\TemporalQuery;
use Celest\ZonedDateTime;
use Celest\ZoneId;
use PHPUnit_Framework_TestCase;

class TemporalTest extends AbstractTemporalAccessor {

    public function isSupported(TemporalField $field)
    {
        return true;
    }

    public function getLong(TemporalField $field)
    {
        throw new DateTimeException("Mock");
    }

}

class AbstractTestPrinterParser extends PHPUnit_Framework_TestCase
{

    /** @var DateTimeFormatterBuilder */
    protected $builder;
    /** @var TemporalAccessor */
    protected $dta;
    /** @var Locale */
    protected $locale;
    /** @var DecimalStyle */
    protected $decimalStyle;

    /**
     * @setUp
     */
    public function setUp()
    {
        $this->builder = new DateTimeFormatterBuilder();
        $this->dta = ZonedDateTime::ofDateTime(LocalDateTime::of(2011, 6, 30, 12, 30, 40, 0), ZoneId::of("Europe/Paris"));
        $this->locale = Locale::of("en");
        $this->decimalStyle = DecimalStyle::STANDARD();
    }

    protected
    function setCaseSensitive($caseSensitive)
    {
        if ($caseSensitive) {
            $this->builder->parseCaseSensitive();
        } else {
            $this->builder->parseCaseInsensitive();
        }
    }

    protected
    function setStrict($strict)
    {
        if ($strict) {
            $this->builder->parseStrict();
        } else {
            $this->builder->parseLenient();
        }
    }

    protected
    function getFormatter()
    {
        return $this->builder->toFormatter2($this->locale)->withDecimalStyle($this->decimalStyle);
    }

    protected
    function getFormatterChar($c)
    {
        return $this->builder->appendLiteral($c)->toFormatter2($this->locale)->withDecimalStyle($this->decimalStyle);
    }

    protected
    function getFormatterString($s)
    {
        return $this->builder->appendLiteral2($s)->toFormatter2($this->locale)->withDecimalStyle($this->decimalStyle);
    }

    protected
    function getFormatterField(TemporalField $field)
    {
        return $this->builder->appendText($field)->toFormatter2($this->locale)->withDecimalStyle($this->decimalStyle);
    }

    protected
    function getFormatterFieldStyle(TemporalField $field, TextStyle $style)
    {
        return $this->builder->appendText2($field, $style)->toFormatter2($this->locale)->withDecimalStyle($this->decimalStyle);
    }

    protected
    function getFormatterWidth(TemporalField $field, $minWidth, $maxWidth, SignStyle $signStyle)
    {
        return $this->builder->appendValue3($field, $minWidth, $maxWidth, $signStyle)->toFormatter2($this->locale)->withDecimalStyle($this->decimalStyle);
    }

    protected
    function getFormatterPattern($pattern, $noOffsetText)
    {
        return $this->builder->appendOffset($pattern, $noOffsetText)->toFormatter2($this->locale)->withDecimalStyle($this->decimalStyle);
    }

    protected
    function getPatternFormatter($pattern)
    {
        return $this->builder->appendPattern($pattern)->toFormatter2($this->locale)->withDecimalStyle($this->decimalStyle);
    }

    protected function getFormatterFraction(TemporalField $field, $minWidth, $maxWidth, $decimalPoint)
    {
        return $this->builder->appendFraction($field, $minWidth, $maxWidth, $decimalPoint)->toFormatter2($this->locale)->withDecimalStyle($this->decimalStyle);
    }

    protected static function EMPTY_DTA()
    {
        return new TemporalTest();
    }
}