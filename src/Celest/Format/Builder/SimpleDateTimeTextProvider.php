<?php

namespace Celest\Format\Builder;


use Celest\Format\DateTimeTextProvider;
use Celest\Format\TextStyle;
use Celest\Locale;
use Celest\Temporal\TemporalField;

class SimpleDateTimeTextProvider extends DateTimeTextProvider
{
    private $data;
    private $parse_data;

    public function __construct(array $data)
    {
        $this->data = $data;
        $this->parse_data = array_flip($data);
    }

    public function getText(TemporalField $field, $value, TextStyle $style, Locale $locale)
    {
        if(isset($this->data[$value])) {
            return $this->data[$value];
        } else {
            return null;
        }
    }

    public function getTextIterator(TemporalField $field, $style, Locale $locale)
    {
        return $this->parse_data;
    }
}