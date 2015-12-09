<?php

namespace Php\Time\Format\Builder;

use Php\Time\DateTimeException;
use Php\Time\Format\DateTimeFormatterBuilder;
use Php\Time\Format\DecimalStyle;
use Php\Time\Format\SignStyle;
use Php\Time\Format\TextStyle;
use Php\Time\LocalDateTime;
use Php\Time\Locale;
use Php\Time\Temporal\TemporalAccessor;
use Php\Time\Temporal\TemporalAccessorDefaults;
use Php\Time\Temporal\TemporalField;
use Php\Time\Temporal\TemporalQuery;
use Php\Time\ZonedDateTime;
use Php\Time\ZoneId;
use PHPUnit_Framework_TestCase;

class TemporalTest implements TemporalAccessor {

    public function isSupported(TemporalField $field)
    {
        return true;
    }


    public function range(TemporalField $field)
    {
        return TemporalAccessorDefaults::range($this, $field);
    }

    public function get(TemporalField $field)
    {
        return TemporalAccessorDefaults::get($this, $field);
    }

    public function getLong(TemporalField $field)
    {
        return TemporalAccessorDefaults::get($this, $field);
    }

    public function query(TemporalQuery $query)
    {
        return TemporalAccessorDefaults::query($this, $query);
    }

    public function __toString()
    {
        return '';
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
        $this->dta = ZonedDateTime::of(LocalDateTime::ofNumerical(2011, 6, 30, 12, 30, 40, 0), ZoneId::of("Europe/Paris"));
        // TODO Locale $this->locale = Locale . ENGLISH;
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
        return $this->builder->appendLiteral($s)->toFormatter2($this->locale)->withDecimalStyle($this->decimalStyle);
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

    protected static function EMPTY_DTA()
    {
        return new TemporalTest();
    }

    public
    function getLong(TemporalField $field)
    {
        throw new DateTimeException("Mock");
    }
}