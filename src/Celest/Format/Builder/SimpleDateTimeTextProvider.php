<?php declare(strict_types=1);

namespace Celest\Format\Builder;


use Celest\Format\DateTimeTextProvider;
use Celest\Format\TextStyle;
use Celest\Locale;
use Celest\Temporal\TemporalField;

final class SimpleDateTimeTextProvider extends DateTimeTextProvider
{
    private $data;
    private $parse_data;

    public function __construct(array $data)
    {
        $this->data = $data;
        $this->parse_data = array_flip($data);
    }

    public function getText(TemporalField $field, int $value, TextStyle $style, Locale $locale) : ?string
    {
        return @$this->data[$value];
    }

    public function getTextIterator(TemporalField $field, ?TextStyle $style, Locale $locale) : array
    {
        return $this->parse_data;
    }
}