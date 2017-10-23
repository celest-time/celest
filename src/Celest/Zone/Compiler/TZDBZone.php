<?php

namespace Celest\Zone\Compiler;

use Celest\IllegalArgumentException;
use Celest\LocalDate;
use Celest\LocalDateTime;
use Celest\Temporal\TemporalAdjusters;
use Celest\Year;
use Celest\ZoneOffset;

/**
 * Class representing a linked set of zone lines in the TZDB file.
 */
final class TZDBZone extends TZDBMonthDayTime
{
    /** The standard offset. @var ZoneOffset */
    public $standardOffset;
    /** The fixed savings amount. @var int */
    public $fixedSavingsSecs;
    /** The savings rule. @var string */
    public $savingsRule;
    /** The text name of the zone. @var string */
    public $text;
    /** The year of the cutover. @var int */
    public $year = Year::MAX_VALUE;

    /**
     * @param ZoneRulesBuilder $bld
     * @param TZDBRule[][] $rules
     * @return ZoneRulesBuilder
     * @throws IllegalArgumentException
     */
    public function addToBuilder(ZoneRulesBuilder $bld, array $rules)
    {
        if ($this->year !== Year::MAX_VALUE) {
            $bld->addWindow($this->standardOffset, $this->toDateTime($this->year), $this->timeDefinition);
        } else {
            $bld->addWindowForever($this->standardOffset);
        }
        if ($this->fixedSavingsSecs !== null) {
            $bld->setFixedSavingsToWindow($this->fixedSavingsSecs);
        } else {
            if (isset($rules[$this->savingsRule])) {
                $tzdbRules = @$rules[$this->savingsRule];
            } else {
                throw new IllegalArgumentException("Rule not found: " . $this->savingsRule);
            }
            foreach ($tzdbRules as $tzdbRule) {
                $tzdbRule->addToBuilder($bld);
            }
        }
        return $bld;
    }

    private function toDateTime($year)
    {
        $this->adjustToFowards($year);
        if ($this->dayOfMonth === -1) {
            $dayOfMonth = $this->month->length(Year::isLeapYear($year));
            $date = LocalDate::ofMonth($year, $this->month, $dayOfMonth);
            if ($this->dayOfWeek !== null) {
                $date = $date->adjust(TemporalAdjusters::previousOrSame($this->dayOfWeek));
            }
        } else {
            $date = LocalDate::ofMonth($year, $this->month, $this->dayOfMonth);
            if ($this->dayOfWeek !== null) {
                $date = $date->adjust(TemporalAdjusters::nextOrSame($this->dayOfWeek));
            }
        }
        $ldt = LocalDateTime::ofDateAndTime($date, $this->time);
        if ($this->endOfDay) {
            $ldt = $ldt->plusDays(1);
        }
        return $ldt;
    }
}