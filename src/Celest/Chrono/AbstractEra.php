<?php

namespace Celest\Chrono;

use Celest\Format\DateTimeFormatterBuilder;
use Celest\Format\TextStyle;
use Celest\Locale;
use Celest\Temporal\AbstractTemporalAccessor;
use Celest\Temporal\ChronoField;
use Celest\Temporal\ChronoUnit;
use Celest\Temporal\Temporal;
use Celest\Temporal\TemporalField;
use Celest\Temporal\TemporalQueries;
use Celest\Temporal\TemporalQuery;
use Celest\Temporal\UnsupportedTemporalTypeException;

abstract class AbstractEra extends AbstractTemporalAccessor implements Era
{
    /**
     * @inheritdoc
     */
    public function isSupported(TemporalField $field)
    {
        if ($field instanceof ChronoField) {
            return $field == ChronoField::ERA();
        }
        return $field !== null && $field->isSupportedBy($this);
    }

    /**
     * @inheritdoc
     */
    public function range(TemporalField $field)
    {
        return parent::range($field);
    }

    /**
     * @inheritdoc
     */
    public function get(TemporalField $field)
    {
        if ($field == ChronoField::ERA()) {
            return $this->getValue();
        }

        return parent::get($field);
    }

    /**
     * @inheritdoc
     */
    public function getLong(TemporalField $field)
    {
        if ($field == ChronoField::ERA()) {
            return $this->getValue();
        } else
            if ($field instanceof ChronoField) {
                throw new UnsupportedTemporalTypeException("Unsupported field: " . $field);
            }
        return $field->getFrom($this);
    }

    /**
     * @inheritdoc
     */
    public function query(TemporalQuery $query)
    {
        if ($query == TemporalQueries::precision()) {
            return ChronoUnit::ERAS();
        }

        return parent::query($query);
    }

    /**
     * @inheritdoc
     */
    public function adjustInto(Temporal $temporal)
    {
        return $temporal->with(ChronoField::ERA(), $this->getValue());
    }

    //-----------------------------------------------------------------------

    /**
     * @inheritdoc
     */
    public function getDisplayName(TextStyle $style, Locale $locale)
    {
        return (new DateTimeFormatterBuilder())->appendText2(ChronoField::ERA(), $style)->toFormatter2($locale)->format($this);
    }

}
