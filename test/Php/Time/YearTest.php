<?php

namespace Php\Time;

use Php\Time\Temporal\ChronoUnit;
use Php\Time\Temporal\TemporalUnit;
use PHPUnit_Framework_TestCase;

class YearTest extends PHPUnit_Framework_TestCase
{
//-----------------------------------------------------------------------
// isLeap()
//-----------------------------------------------------------------------
    public function test_isLeap()
    {
        $this->assertEquals(Year::of(1999)->isLeap(), false);
        $this->assertEquals(Year::of(2000)->isLeap(), true);
        $this->assertEquals(Year::of(2001)->isLeap(), false);

        $this->assertEquals(Year::of(2007)->isLeap(), false);
        $this->assertEquals(Year::of(2008)->isLeap(), true);
        $this->assertEquals(Year::of(2009)->isLeap(), false);
        $this->assertEquals(Year::of(2010)->isLeap(), false);
        $this->assertEquals(Year::of(2011)->isLeap(), false);
        $this->assertEquals(Year::of(2012)->isLeap(), true);

        $this->assertEquals(Year::of(2095)->isLeap(), false);
        $this->assertEquals(Year::of(2096)->isLeap(), true);
        $this->assertEquals(Year::of(2097)->isLeap(), false);
        $this->assertEquals(Year::of(2098)->isLeap(), false);
        $this->assertEquals(Year::of(2099)->isLeap(), false);
        $this->assertEquals(Year::of(2100)->isLeap(), false);
        $this->assertEquals(Year::of(2101)->isLeap(), false);
        $this->assertEquals(Year::of(2102)->isLeap(), false);
        $this->assertEquals(Year::of(2103)->isLeap(), false);
        $this->assertEquals(Year::of(2104)->isLeap(), true);
        $this->assertEquals(Year::of(2105)->isLeap(), false);

        $this->assertEquals(Year::of(-500)->isLeap(), false);
        $this->assertEquals(Year::of(-400)->isLeap(), true);
        $this->assertEquals(Year::of(-300)->isLeap(), false);
        $this->assertEquals(Year::of(-200)->isLeap(), false);
        $this->assertEquals(Year::of(-100)->isLeap(), false);
        $this->assertEquals(Year::of(0)->isLeap(), true);
        $this->assertEquals(Year::of(100)->isLeap(), false);
        $this->assertEquals(Year::of(200)->isLeap(), false);
        $this->assertEquals(Year::of(300)->isLeap(), false);
        $this->assertEquals(Year::of(400)->isLeap(), true);
        $this->assertEquals(Year::of(500)->isLeap(), false);
    }

//-----------------------------------------------------------------------
// plusYears()
//-----------------------------------------------------------------------
    public function test_plusYears()
    {
        $this->assertEquals(Year::of(2007)->plusYears(-1), Year::of(2006));
        $this->assertEquals(Year::of(2007)->plusYears(0), Year::of(2007));
        $this->assertEquals(Year::of(2007)->plusYears(1), Year::of(2008));
        $this->assertEquals(Year::of(2007)->plusYears(2), Year::of(2009));

        $this->assertEquals(Year::of(Year::MAX_VALUE - 1)->plusYears(1), Year::of(Year::MAX_VALUE));
        $this->assertEquals(Year::of(Year::MAX_VALUE)->plusYears(0), Year::of(Year::MAX_VALUE));

        $this->assertEquals(Year::of(Year::MIN_VALUE + 1)->plusYears(-1), Year::of(Year::MIN_VALUE));
        $this->assertEquals(Year::of(Year::MIN_VALUE)->plusYears(0), Year::of(Year::MIN_VALUE));
    }

    public
    function test_plusYear_zero_equals()
    {
        $base = Year::of(2007);
        $this->assertEquals($base->plusYears(0), $base);
    }

    public
    function test_plusYears_big()
    {
        $years = 20 + Year::MAX_VALUE;
        $this->assertEquals(Year::of(-40)->plusYears($years), Year::of((int)(-40 + $years)));
    }

    /**
     * @expectedException     \Php\Time\DateTimeException
     */
    public
    function test_plusYears_max()
    {
        Year::of(Year :: MAX_VALUE)->plusYears(1);
    }

    /**
     * @expectedException     \Php\Time\DateTimeException
     */
    public
    function test_plusYears_maxLots()
    {
        Year::of(Year :: MAX_VALUE)->plusYears(1000);
    }

    /**
     * @expectedException     \Php\Time\DateTimeException
     */
    public
    function test_plusYears_min()
    {
        Year::of(Year :: MIN_VALUE)->plusYears(-1);
    }

    /**
     * @expectedException     \Php\Time\DateTimeException
     */
    public
    function test_plusYears_minLots()
    {
        Year::of(Year :: MIN_VALUE)->plusYears(-1000);
    }

//-----------------------------------------------------------------------
// plus(long, TemporalUnit)
//-----------------------------------------------------------------------
    function data_plus_long_TemporalUnit()
    {
        return [
            [
                Year::of(1), 1, ChronoUnit::YEARS(), Year::of(2), null],
            [
                Year::of(1), -12, ChronoUnit::YEARS(), Year::of(-11), null],
            [
                Year::of(1), 0, ChronoUnit::YEARS(), Year::of(1), null],
            [
                Year::of(999999999), 0, ChronoUnit::YEARS(), Year::of(999999999), null],
            [
                Year::of(-999999999), 0, ChronoUnit::YEARS(), Year::of(-999999999), null],
            [
                Year::of(0), -999999999, ChronoUnit::YEARS(), Year::of(-999999999), null],
            [
                Year::of(0), 999999999, ChronoUnit::YEARS(), Year::of(999999999), null],

            [
                Year::of(-1), 1, ChronoUnit::ERAS(), Year::of(2), null],
            [
                Year::of(5), 1, ChronoUnit::CENTURIES(), Year::of(105), null],
            [
                Year::of(5), 1, ChronoUnit::DECADES(), Year::of(15), null],

            [
                Year::of(999999999), 1, ChronoUnit::YEARS(), null, 'Php\Time\DateTimeException'],
            [
                Year::of(-999999999), -1, ChronoUnit::YEARS(), null, 'Php\Time\DateTimeException'],

            [
                Year::of(1), 0, ChronoUnit::DAYS(), null, 'Php\Time\DateTimeException'],
            [
                Year::of(1), 0, ChronoUnit::WEEKS(), null, 'Php\Time\DateTimeException'],
            [
                Year::of(1), 0, ChronoUnit::MONTHS(), null, 'Php\Time\DateTimeException'],
        ];
    }

    /**
     * @dataProvider data_plus_long_TemporalUnit
     */
    public function test_plus_long_TemporalUnit(Year $base, $amount, TemporalUnit $unit, $expectedYear, $expectedEx)
    {
        if ($expectedEx == null) {
            $this->assertEquals($base->plus($amount, $unit), $expectedYear);
        } else {
            try {
                $base->plus($amount, $unit);
                $this->fail();
            } catch (\Exception $ex) {
                $this->assertTrue($ex instanceof $expectedEx);
            }
        }
    }

}