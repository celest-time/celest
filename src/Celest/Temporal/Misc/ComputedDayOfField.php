<?php

namespace Celest\Temporal\Misc;


use Celest\Chrono\ChronoLocalDate;
use Celest\Chrono\Chronology;
use Celest\Chrono\ChronologyDefaults;
use Celest\DateTimeException;
use Celest\Format\DateTimeTextProvider;
use Celest\Format\ResolverStyle;
use Celest\Helper\Math;
use Celest\Locale;
use Celest\Temporal\ChronoField as CF;
use Celest\Temporal\ChronoUnit;
use Celest\Temporal\FieldValues;
use Celest\Temporal\IsoFields;
use Celest\Temporal\Temporal;
use Celest\Temporal\TemporalAccessor;
use Celest\Temporal\TemporalField;
use Celest\Temporal\TemporalUnit;
use Celest\Temporal\ValueRange;
use Celest\Temporal\WeekFields;

/**
 * Field type that computes DayOfWeek, WeekOfMonth, and WeekOfYear
 * based on a WeekFields.
 * A separate Field instance is required for each different WeekFields;
 * combination of start of week and minimum number of days.
 * Constructors are provided to create fields for DayOfWeek, WeekOfMonth,
 * and WeekOfYear.
 */
class ComputedDayOfField implements TemporalField
{

    /**
     * Returns a field to access the day of week,
     * computed based on a WeekFields.
     * <p>
     * The WeekDefintion of the first day of the week is used with
     * the ISO DAY_OF_WEEK field to compute week boundaries.
     */
    static function ofDayOfWeekField(WeekFields $weekDef)
    {
        return new ComputedDayOfField("DayOfWeek", $weekDef, ChronoUnit::DAYS(), ChronoUnit::WEEKS(), self::DAY_OF_WEEK_RANGE());
    }

    /**
     * Returns a field to access the week of month,
     * computed based on a WeekFields.
     * @see WeekFields#weekOfMonth()
     */
    static function ofWeekOfMonthField(WeekFields $weekDef)
    {
        return new ComputedDayOfField("WeekOfMonth", $weekDef, ChronoUnit::WEEKS(), ChronoUnit::MONTHS(), self::WEEK_OF_MONTH_RANGE());
    }

    /**
     * Returns a field to access the week of year,
     * computed based on a WeekFields.
     * @see WeekFields#weekOfYear()
     */
    static function ofWeekOfYearField(WeekFields $weekDef)
    {
        return new ComputedDayOfField("WeekOfYear", $weekDef, ChronoUnit::WEEKS(), ChronoUnit::YEARS(), self::WEEK_OF_YEAR_RANGE());
    }

    /**
     * Returns a field to access the week of week-based-year,
     * computed based on a WeekFields.
     * @see WeekFields#weekOfWeekBasedYear()
     */
    static function ofWeekOfWeekBasedYearField(WeekFields $weekDef)
    {
        return new ComputedDayOfField("WeekOfWeekBasedYear", $weekDef, ChronoUnit::WEEKS(), IsoFields::WEEK_BASED_YEARS(), self::WEEK_OF_WEEK_BASED_YEAR_RANGE());
    }

    /**
     * Returns a field to access the week of week-based-year,
     * computed based on a WeekFields.
     * @see WeekFields#weekBasedYear()
     */
    static function ofWeekBasedYearField(WeekFields $weekDef)
    {
        return new ComputedDayOfField("WeekBasedYear", $weekDef, IsoFields::WEEK_BASED_YEARS(), ChronoUnit::FOREVER(), CF::YEAR()->range());
    }

    /**
     * Return a new week-based-year date of the Chronology, year, week-of-year,
     * and dow of week.
     * @param Chronology $chrono The chronology of the new date
     * @param int $yowby the year of the week-based-year
     * @param int $wowby the week of the week-based-year
     * @param int $dow the day of the week
     * @return ChronoLocalDate a ChronoLocalDate for the requested year, week of year, and day of week
     */
    private function ofWeekBasedYear(Chronology $chrono,
                                     $yowby, $wowby, $dow)
    {
        $date = $chrono->date($yowby, 1, 1);
        $ldow = $this->localizedDayOfWeek($date);
        $offset = $this->startOfWeekOffset(1, $ldow);

        // Clamp the week of year to keep it in the same year
        $yearLen = $date->lengthOfYear();
        $newYearWeek = $this->computeWeek($offset, $yearLen + $this->weekDef->getMinimalDaysInFirstWeek());
        $wowby = Math::min($wowby, $newYearWeek - 1);

        $days = -$offset + ($dow - 1) + ($wowby - 1) * 7;
        return $date->plus($days, ChronoUnit::DAYS());
    }

    /** @var string */
    private $name;
    /** @var WeekFields */
    private $weekDef;
    /** @var TemporalUnit */
    private $baseUnit;
    /** @var TemporalUnit */
    private $rangeUnit;
    /** @var ValueRange */
    private $range;

    private function __construct($name, WeekFields $weekDef, TemporalUnit $baseUnit, TemporalUnit $rangeUnit, ValueRange $range)
    {
        $this->name = $name;
        $this->weekDef = $weekDef;
        $this->baseUnit = $baseUnit;
        $this->rangeUnit = $rangeUnit;
        $this->range = $range;
    }

    private static function DAY_OF_WEEK_RANGE()
    {
        if (self::$DAY_OF_WEEK_RANGE === null) {
            self::$DAY_OF_WEEK_RANGE = ValueRange::of(1, 7);
        }
        return self::$DAY_OF_WEEK_RANGE;
    }

    private static $DAY_OF_WEEK_RANGE;

    private static function WEEK_OF_MONTH_RANGE()
    {
        if (self::$WEEK_OF_MONTH_RANGE === null) {
            self::$WEEK_OF_MONTH_RANGE = ValueRange::ofFullyVariable(0, 1, 4, 6);
        }
        return self::$WEEK_OF_MONTH_RANGE;
    }

    private static $WEEK_OF_MONTH_RANGE;

    private static function WEEK_OF_YEAR_RANGE()
    {
        if (self::$WEEK_OF_YEAR_RANGE === null) {
            self::$WEEK_OF_YEAR_RANGE = ValueRange::ofFullyVariable(0, 1, 52, 54);
        }
        return self::$WEEK_OF_YEAR_RANGE;
    }

    private static $WEEK_OF_YEAR_RANGE;

    private static function WEEK_OF_WEEK_BASED_YEAR_RANGE()
    {
        if (self::$WEEK_OF_WEEK_BASED_YEAR_RANGE === null) {
            self::$WEEK_OF_WEEK_BASED_YEAR_RANGE = ValueRange::ofVariable(1, 52, 53);
        }
        return self::$WEEK_OF_WEEK_BASED_YEAR_RANGE;
    }

    private static $WEEK_OF_WEEK_BASED_YEAR_RANGE;

    public function getFrom(TemporalAccessor $temporal)
    {
        if ($this->rangeUnit == ChronoUnit::WEEKS()) {  // day-of-week
            return $this->localizedDayOfWeek($temporal);
        } else
            if ($this->rangeUnit == ChronoUnit::MONTHS()) {  // week-of-month
                return $this->localizedWeekOfMonth($temporal);
            } else if ($this->rangeUnit == ChronoUnit::YEARS()) {  // week-of-year
                return $this->localizedWeekOfYear($temporal);
            } else if ($this->rangeUnit == IsoFields::WEEK_BASED_YEARS()) {
                return $this->localizedWeekOfWeekBasedYear($temporal);
            } else if ($this->rangeUnit == ChronoUnit::FOREVER()) {
                return $this->localizedWeekBasedYear($temporal);
            } else {
                throw new IllegalStateException("unreachable, rangeUnit: " . $this->rangeUnit . ", this: " . $this);
            }
    }

    private function localizedDayOfWeek(TemporalAccessor $temporal)
    {
        $sow = $this->weekDef->getFirstDayOfWeek()->getValue();
        $isoDow = $temporal->get(CF::DAY_OF_WEEK());
        return Math::floorMod($isoDow - $sow, 7) + 1;
    }

    private function localizedDayOfWeekNumerical($isoDow)
    {
        $sow = $this->weekDef->getFirstDayOfWeek()->getValue();
        return Math::floorMod($isoDow - $sow, 7) + 1;
    }

    private function localizedWeekOfMonth(TemporalAccessor $temporal)
    {
        $dow = $this->localizedDayOfWeek($temporal);
        $dom = $temporal->get(CF::DAY_OF_MONTH());
        $offset = $this->startOfWeekOffset($dom, $dow);
        return $this->computeWeek($offset, $dom);
    }

    private function localizedWeekOfYear(TemporalAccessor $temporal)
    {
        $dow = $this->localizedDayOfWeek($temporal);
        $doy = $temporal->get(CF::DAY_OF_YEAR());
        $offset = $this->startOfWeekOffset($doy, $dow);
        return $this->computeWeek($offset, $doy);
    }

    /**
     * Returns the year of week-based-year for the temporal.
     * The year can be the previous year, the current year, or the next year.
     * @param TemporalAccessor $temporal a date of any chronology, not null
     * @return int the year of week-based-year for the date
     */
    private function localizedWeekBasedYear(TemporalAccessor $temporal)
    {
        $dow = $this->localizedDayOfWeek($temporal);
        $year = $temporal->get(CF::YEAR());
        $doy = $temporal->get(CF::DAY_OF_YEAR());
        $offset = $this->startOfWeekOffset($doy, $dow);
        $week = $this->computeWeek($offset, $doy);
        if ($week === 0) {
            // Day is in end of week of previous year; return the previous year
            return $year - 1;
        } else {
            // If getting close to end of year, use higher precision logic
            // Check if date of year is in partial week associated with next year
            $dayRange = $temporal->range(CF::DAY_OF_YEAR());
            $yearLen = $dayRange->getMaximum();
            $newYearWeek = $this->computeWeek($offset, $yearLen + $this->weekDef->getMinimalDaysInFirstWeek());
            if ($week >= $newYearWeek) {
                return $year + 1;
            }
        }
        return $year;
    }

    /**
     * Returns the week of week-based-year for the temporal.
     * The week can be part of the previous year, the current year,
     * or the next year depending on the week start and minimum number
     * of days.
     * @param TemporalAccessor $temporal a date of any chronology
     * @return int the week of the year
     * @see #localizedWeekBasedYear(java.time.temporal.TemporalAccessor)
     */
    private function localizedWeekOfWeekBasedYear(TemporalAccessor $temporal)
    {
        $dow = $this->localizedDayOfWeek($temporal);
        $doy = $temporal->get(CF::DAY_OF_YEAR());
        $offset = $this->startOfWeekOffset($doy, $dow);
        $week = $this->computeWeek($offset, $doy);
        if ($week === 0) {
            // Day is in end of week of previous year
            // Recompute from the last day of the previous year
            $date = ChronologyDefaults::from($temporal)->dateFrom($temporal);
            $date = $date->minus($doy, ChronoUnit::DAYS());   // Back down into previous year
            return $this->localizedWeekOfWeekBasedYear($date);
        } else
            if ($week > 50) {
                // If getting close to end of year, use higher precision logic
                // Check if date of year is in partial week associated with next year
                $dayRange = $temporal->range(CF::DAY_OF_YEAR());
                $yearLen = $dayRange->getMaximum();
                $newYearWeek = $this->computeWeek($offset, $yearLen + $this->weekDef->getMinimalDaysInFirstWeek());
                if ($week >= $newYearWeek) {
                    // Overlaps with week of following year; reduce to week in following year
                    $week = $week - $newYearWeek + 1;
                }
            }
        return $week;
    }

    /**
     * Returns an offset to align week start with a day of month or day of year.
     *
     * @param int $day the day; 1 through infinity
     * @param int $dow the day of the week of that day; 1 through 7
     * @return int an offset in days to align a day with the start of the first 'full' week
     */
    private function startOfWeekOffset($day, $dow)
    {
        // offset of first day corresponding to the day of week in first 7 days (zero origin)
        $weekStart = Math::floorMod($day - $dow, 7);
        $offset = -$weekStart;
        if ($weekStart + 1 > $this->weekDef->getMinimalDaysInFirstWeek()) {
            // The previous week has the minimum days in the current month to be a 'week'
            $offset = 7 - $weekStart;
        }

        return $offset;
    }

    /**
     * Returns the week number computed from the reference day and reference dayOfWeek.
     *
     * @param int $offset the offset to align a date with the start of week
     *     from {@link #startOfWeekOffset}.
     * @param int $day the day for which to compute the week number
     * @return int the week number where zero is used for a partial week and 1 for the first full week
     */
    private function computeWeek($offset, $day)
    {
        return Math::div((7 + $offset + ($day - 1)), 7);
    }

    public function adjustInto(Temporal $temporal, $newValue)
    {
        // Check the new value and get the old value of the field
        $newVal = $this->range->checkValidIntValue($newValue, $this);  // lenient check range
        $currentVal = $temporal->get($this);
        if ($newVal === $currentVal) {
            return $temporal;
        }

        if ($this->rangeUnit == ChronoUnit::FOREVER()) {     // replace year of WeekBasedYear
            // Create a new date object with the same chronology,
            // the desired year and the same week and dow.
            $idow = $temporal->get($this->weekDef->dayOfWeek);
            $wowby = $temporal->get($this->weekDef->weekOfWeekBasedYear);
            return $this->ofWeekBasedYear(ChronologyDefaults::from($temporal), $newValue, $wowby, $idow);
        } else {
            // Compute the difference and add that using the base unit of the field
            return $temporal->plus($newVal - $currentVal, $this->baseUnit);
        }
    }

    public function resolve(FieldValues $fieldValues, TemporalAccessor $partialTemporal, ResolverStyle $resolverStyle)
    {
        $value = $fieldValues->get($this);
        $newValue = Math::toIntExact($value);  // broad limit makes overflow checking lighter
        // first convert localized day-of-week to ISO day-of-week
        // doing this first handles case where both ISO and localized were parsed and might mismatch
        // day-of-week is always strict as two different day-of-week values makes lenient complex
        if ($this->rangeUnit == ChronoUnit::WEEKS()) {  // day-of-week
            $checkedValue = $this->range->checkValidIntValue($value, $this);  // no leniency as too complex
            $startDow = $this->weekDef->getFirstDayOfWeek()->getValue();
            $isoDow = Math::floorMod(($startDow - 1) + ($checkedValue - 1), 7) + 1;
            $fieldValues->remove($this);
            $fieldValues->put(CF::DAY_OF_WEEK(), $isoDow);
            return null;
        }

// can only build date if ISO day-of-week is present
        if (!$fieldValues->has(CF::DAY_OF_WEEK())) {
            return null;
        }
        $isoDow = CF::DAY_OF_WEEK()->checkValidIntValue($fieldValues->get(CF::DAY_OF_WEEK()));
        $dow = $this->localizedDayOfWeekNumerical($isoDow);

        // build date
        $chrono = ChronologyDefaults::from($partialTemporal);
        if ($fieldValues->has(CF::YEAR())) {
            $year = CF::YEAR()->checkValidIntValue($fieldValues->get(CF::YEAR()));  // validate
            if ($this->rangeUnit == ChronoUnit::MONTHS() && $fieldValues->has(CF::MONTH_OF_YEAR())) {  // week-of-month
                $month = $fieldValues->get(CF::MONTH_OF_YEAR());  // not validated yet
                return $this->resolveWoM($fieldValues, $chrono, $year, $month, $newValue, $dow, $resolverStyle);
            }
            if ($this->rangeUnit == ChronoUnit::YEARS()) {  // week-of-year
                return $this->resolveWoY($fieldValues, $chrono, $year, $newValue, $dow, $resolverStyle);
            }
        } else if (($this->rangeUnit == IsoFields::WEEK_BASED_YEARS() || $this->rangeUnit == ChronoUnit::FOREVER()) &&
            $fieldValues->has($this->weekDef->weekBasedYear) &&
            $fieldValues->has($this->weekDef->weekOfWeekBasedYear)
        ) { // week-of-week-based-year and year-of-week-based-year
            return $this->resolveWBY($fieldValues, $chrono, $dow, $resolverStyle);
        }
        return null;
    }

    private function resolveWoM(FieldValues $fieldValues, Chronology $chrono, $year, $month, $wom, $localDow, ResolverStyle $resolverStyle)
    {
        if ($resolverStyle == ResolverStyle::LENIENT()) {
            $date = $chrono->date($year, 1, 1)->plus(Math::subtractExact($month, 1), ChronoUnit::MONTHS());
            $weeks = Math::subtractExact($wom, $this->localizedWeekOfMonth($date));
            $days = $localDow - $this->localizedDayOfWeek($date);  // safe from overflow
            $date = $date->plus(Math::addExact(Math::multiplyExact($weeks, 7), $days), ChronoUnit::DAYS());
        } else {
            $monthValid = CF::MONTH_OF_YEAR()->checkValidIntValue($month);  // validate
            $date = $chrono->date($year, $monthValid, 1);
            $womInt = $this->range->checkValidIntValue($wom, $this);  // validate
            $weeks = (int)($womInt - $this->localizedWeekOfMonth($date));  // safe from overflow
            $days = $localDow - $this->localizedDayOfWeek($date);  // safe from overflow
            $date = $date->plus($weeks * 7 + $days, ChronoUnit::DAYS());
            if ($resolverStyle == ResolverStyle::STRICT() && $date->getLong(CF::MONTH_OF_YEAR()) !== $month) {
                throw new DateTimeException("Strict mode rejected resolved date as it is in a different month");
            }
        }
        $fieldValues->remove($this);
        $fieldValues->remove(CF::YEAR());
        $fieldValues->remove(CF::MONTH_OF_YEAR());
        $fieldValues->remove(CF::DAY_OF_WEEK());
        return $date;
    }

    private function resolveWoY(FieldValues $fieldValues, Chronology $chrono, $year, $woy, $localDow, ResolverStyle $resolverStyle)
    {
        $date = $chrono->date($year, 1, 1);
        if ($resolverStyle == ResolverStyle::LENIENT()) {
            $weeks = Math::subtractExact($woy, $this->localizedWeekOfYear($date));
            $days = $localDow - $this->localizedDayOfWeek($date);  // safe from overflow
            $date = $date->plus(Math::addExact(Math::multiplyExact($weeks, 7), $days), ChronoUnit::DAYS());
        } else {
            $womInt = $this->range->checkValidIntValue($woy, $this);  // validate
            $weeks = (int)($womInt - $this->localizedWeekOfYear($date));  // safe from overflow
            $days = $localDow - $this->localizedDayOfWeek($date);  // safe from overflow
            $date = $date->plus($weeks * 7 + $days, ChronoUnit::DAYS());
            if ($resolverStyle == ResolverStyle::STRICT() && $date->getLong(CF::YEAR()) !== $year) {
                throw new DateTimeException("Strict mode rejected resolved date as it is in a different year");
            }
        }
        $fieldValues->remove($this);
        $fieldValues->remove(CF::YEAR());
        $fieldValues->remove(CF::DAY_OF_WEEK());
        return $date;
    }

    private function resolveWBY(FieldValues $fieldValues, Chronology $chrono, $localDow, ResolverStyle $resolverStyle)
    {
        $yowby = $this->weekDef->weekBasedYear->range()->checkValidIntValue(
            $fieldValues->get($this->weekDef->weekBasedYear), $this->weekDef->weekBasedYear);
        if ($resolverStyle == ResolverStyle::LENIENT()) {
            $date = $this->ofWeekBasedYear($chrono, $yowby, 1, $localDow);
            $wowby = $fieldValues->get($this->weekDef->weekOfWeekBasedYear);
            $weeks = Math::subtractExact($wowby, 1);
            $date = $date->plus($weeks, ChronoUnit::WEEKS());
        } else {
            $wowby = $this->weekDef->weekOfWeekBasedYear->range()->checkValidIntValue(
                $fieldValues->get($this->weekDef->weekOfWeekBasedYear), $this->weekDef->weekOfWeekBasedYear);  // validate
            $date = $this->ofWeekBasedYear($chrono, $yowby, $wowby, $localDow);
            if ($resolverStyle == ResolverStyle::STRICT() && $this->localizedWeekBasedYear($date) != $yowby) {
                throw new DateTimeException("Strict mode rejected resolved date as it is in a different week-based-year");
            }
        }
        $fieldValues->remove($this);
        $fieldValues->remove($this->weekDef->weekBasedYear);
        $fieldValues->remove($this->weekDef->weekOfWeekBasedYear);
        $fieldValues->remove(CF::DAY_OF_WEEK());
        return $date;
    }

    //-----------------------------------------------------------------------
    public function getDisplayName(Locale $locale)
    {
        if ($this->rangeUnit == ChronoUnit::YEARS()) {  // only have values for week-of-year
            $name = DateTimeTextProvider::tryField('week', $locale);
            if($name !== null) {
                return $name;
            }
        }

        return $this->name;
    }

    public function getBaseUnit()
    {
        return $this->baseUnit;
    }

    public function getRangeUnit()
    {
        return $this->rangeUnit;
    }

    public function isDateBased()
    {
        return true;
    }

    public function isTimeBased()
    {
        return false;
    }

    public function range()
    {
        return $this->range;
    }

    //-----------------------------------------------------------------------
    public function isSupportedBy(TemporalAccessor $temporal)
    {
        if ($temporal->isSupported(CF::DAY_OF_WEEK())) {
            if ($this->rangeUnit == ChronoUnit::WEEKS()) {  // day-of-week
                return true;
            } else
                if ($this->rangeUnit == ChronoUnit::MONTHS()) {  // week-of-month
                    return $temporal->isSupported(CF::DAY_OF_MONTH());
                } else if ($this->rangeUnit == ChronoUnit::YEARS()) {  // week-of-year
                    return $temporal->isSupported(CF::DAY_OF_YEAR());
                } else if ($this->rangeUnit == IsoFields::WEEK_BASED_YEARS()) {
                    return $temporal->isSupported(CF::DAY_OF_YEAR());
                } else if ($this->rangeUnit == ChronoUnit::FOREVER()) {
                    return $temporal->isSupported(CF::YEAR());
                }
        }
        return false;
    }

    public function rangeRefinedBy(TemporalAccessor $temporal)
    {
        if ($this->rangeUnit == ChronoUnit::WEEKS()) {  // day-of-week
            return $this->range;
        } else
            if ($this->rangeUnit == ChronoUnit::MONTHS()) {  // week-of-month
                return $this->rangeByWeek($temporal, CF::DAY_OF_MONTH());
            } else if ($this->rangeUnit == ChronoUnit::YEARS()) {  // week-of-year
                return $this->rangeByWeek($temporal, CF::DAY_OF_YEAR());
            } else if ($this->rangeUnit == IsoFields::WEEK_BASED_YEARS()) {
                return $this->rangeWeekOfWeekBasedYear($temporal);
            } else if ($this->rangeUnit == ChronoUnit::FOREVER()) {
                return CF::YEAR()->range();
            } else {
                throw new IllegalStateException("unreachable, rangeUnit: " . $this->rangeUnit . ", this: " . $this);
            }
    }

    /**
     * Map the field range to a week range
     * @param TemporalAccessor $temporal the temporal
     * @param TemporalField $field the field to get the range of
     * @return ValueRange the ValueRange with the range adjusted to weeks.
     */
    private function rangeByWeek(TemporalAccessor $temporal, TemporalField $field)
    {
        $dow = $this->localizedDayOfWeek($temporal);
        $offset = $this->startOfWeekOffset($temporal->get($field), $dow);
        $fieldRange = $temporal->range($field);
        return ValueRange::of($this->computeWeek($offset, $fieldRange->getMinimum()),
            $this->computeWeek($offset, $fieldRange->getMaximum()));
    }

    /**
     * Map the field range to a week range of a week year.
     * @param TemporalAccessor $temporal the temporal
     * @return ValueRange the ValueRange with the range adjusted to weeks.
     */
    private function rangeWeekOfWeekBasedYear(TemporalAccessor $temporal)
    {
        if (!$temporal->isSupported(CF::DAY_OF_YEAR())) {
            return self::WEEK_OF_YEAR_RANGE();
        }
        $dow = $this->localizedDayOfWeek($temporal);
        $doy = $temporal->get(CF::DAY_OF_YEAR());
        $offset = $this->startOfWeekOffset($doy, $dow);
        $week = $this->computeWeek($offset, $doy);
        if ($week === 0) {
            // Day is in end of week of previous year
            // Recompute from the last day of the previous year
            $date = ChronologyDefaults::from($temporal)->dateFrom($temporal);
            $date = $date->minus($doy + 7, ChronoUnit::DAYS());   // Back down into previous year
            return $this->rangeWeekOfWeekBasedYear($date);
        }
        // Check if day of year is in partial week associated with next year
        $dayRange = $temporal->range(CF::DAY_OF_YEAR());
        $yearLen = $dayRange->getMaximum();
        $newYearWeek = $this->computeWeek($offset, $yearLen + $this->weekDef->getMinimalDaysInFirstWeek());

        if ($week >= $newYearWeek) {
            // Overlaps with weeks of following year; recompute from a week in following year
            $date = ChronologyDefaults::from($temporal)->dateFrom($temporal);
            $date = $date->plus($yearLen - $doy + 1 + 7, ChronoUnit::DAYS());
            return $this->rangeWeekOfWeekBasedYear($date);
        }
        return ValueRange::of(1, $newYearWeek - 1);
    }

    //-----------------------------------------------------------------------
    public function __toString()
    {
        return $this->name . "[" . $this->weekDef->__toString() . "]";
    }
}