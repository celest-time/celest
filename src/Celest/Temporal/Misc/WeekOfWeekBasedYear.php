<?php declare(strict_types=1);

namespace Celest\Temporal\Misc;


use Celest\Format\DateTimeTextProvider;
use Celest\Format\ResolverStyle;
use Celest\Helper\Math;
use Celest\LocalDate;
use Celest\Locale;
use Celest\Temporal\ChronoField;
use Celest\Temporal\ChronoUnit;
use Celest\Temporal\FieldValues;
use Celest\Temporal\IsoFields;
use Celest\Temporal\Temporal;
use Celest\Temporal\TemporalAccessor;
use Celest\Temporal\TemporalField;
use Celest\Temporal\TemporalUnit;
use Celest\Temporal\UnsupportedTemporalTypeException;
use Celest\Temporal\ValueRange;

class WeekOfWeekBasedYear implements TemporalField
{
    public function getDisplayName(Locale $locale) : string
    {
        $name = DateTimeTextProvider::tryField('week', $locale);
        return $name !== null ? $name : $this->__toString();
    }

    public function getBaseUnit() : TemporalUnit
    {
        return ChronoUnit::WEEKS();
    }

    public function getRangeUnit() : TemporalUnit
    {
        return IsoFields::WEEK_BASED_YEARS();
    }

    public function range() : ValueRange
    {
        return ValueRange::ofVariable(1, 52, 53);
    }

    public function isSupportedBy(TemporalAccessor $temporal) : bool
    {
        return $temporal->isSupported(ChronoField::EPOCH_DAY()) && IsoFields::isIso($temporal);
    }

    public function rangeRefinedBy(TemporalAccessor $temporal) : ValueRange
    {
        if ($this->isSupportedBy($temporal) === false) {
            throw new UnsupportedTemporalTypeException("Unsupported field: WeekOfWeekBasedYear");
        }
        return IsoFields::getWeekRange(LocalDate::from($temporal));
    }

    public function getFrom(TemporalAccessor $temporal) : int
    {
        if ($this->isSupportedBy($temporal) === false) {
            throw new UnsupportedTemporalTypeException("Unsupported field: WeekOfWeekBasedYear");
        }

        return IsoFields::getWeek(LocalDate::from($temporal));
    }

    public function adjustInto(Temporal $temporal, int $newValue) : Temporal
    {
        // calls getFrom() to check if supported
        $this->range()->checkValidValue($newValue, $this);  // lenient range
        return $temporal->plus(Math::subtractExact($newValue, $this->getFrom($temporal)), ChronoUnit::WEEKS());
    }

    public function resolve(
        FieldValues $fieldValues, TemporalAccessor $partialTemporal, ResolverStyle $resolverStyle) : ?TemporalAccessor
    {
        $wbyLong = $fieldValues->get(IsoFields::WEEK_BASED_YEAR());
        $dowLong = $fieldValues->get(ChronoField::DAY_OF_WEEK());
        if ($wbyLong === null || $dowLong === null) {
            return null;
        }
        $wby = IsoFields::WEEK_BASED_YEAR()->range()->checkValidIntValue($wbyLong, IsoFields::WEEK_BASED_YEAR());  // always validate
        $wowby = $fieldValues->get(IsoFields::WEEK_OF_WEEK_BASED_YEAR());
        IsoFields::ensureIso($partialTemporal);
        $date = LocalDate::of($wby, 1, 4);
        if ($resolverStyle == ResolverStyle::LENIENT()) {
            $dow = $dowLong;  // unvalidated
            if ($dow > 7) {
                $date = $date->plusWeeks(\intdiv($dow - 1, 7));
                $dow = (($dow - 1) % 7) + 1;
            } else if ($dow < 1) {
                $date = $date->plusWeeks(\intdiv(Math::subtractExact($dow, 7), 7));
                $dow = (($dow + 6) % 7) + 1;
            }
            $date = $date->plusWeeks(Math::subtractExact($wowby, 1))->with(ChronoField::DAY_OF_WEEK(), $dow);
        } else {
            $dow = ChronoField::DAY_OF_WEEK()->checkValidIntValue($dowLong);  // validated
            if ($wowby < 1 || $wowby > 52) {
                if ($resolverStyle == ResolverStyle::STRICT()) {
                    IsoFields::getWeekRange($date)->checkValidValue($wowby, $this);  // only allow exact range
                } else {  // SMART
                    $this->range()->checkValidValue($wowby, $this);  // allow 1-53 rolling into next year
                }
            }
            $date = $date->plusWeeks($wowby - 1)->with(ChronoField::DAY_OF_WEEK(), $dow);
        }
        $fieldValues->remove($this);
        $fieldValues->remove(IsoFields::WEEK_BASED_YEAR());
        $fieldValues->remove(ChronoField::DAY_OF_WEEK());
        return $date;
    }

    public function __toString() : string
    {
        return "WeekOfWeekBasedYear";
    }

    public function isDateBased() : bool
    {
        return true;
    }

    public function isTimeBased() : bool
    {
        return false;
    }
}
