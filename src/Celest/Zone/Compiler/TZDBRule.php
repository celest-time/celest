<?php

namespace Celest\Zone\Compiler;

/**
 * Class representing a rule line in the TZDB file.
 */
final class TZDBRule extends TZDBMonthDayTime
{
    /** The start year. @var int */
    public $startYear;
    /** The end year. @var int */
    public $endYear;
    /** The amount of savings. @var int */
    public $savingsAmount;
    /** The text name of the zone. @var string */
    public $text;

    public function addToBuilder(ZoneRulesBuilder $bld)
    {
        $this->adjustToFowards(2004);  // irrelevant, treat as leap year
        $bld->addRuleToWindow9($this->startYear, $this->endYear, $this->month, $this->dayOfMonth, $this->dayOfWeek, $this->time, $this->endOfDay, $this->timeDefinition, $this->savingsAmount);
    }
}