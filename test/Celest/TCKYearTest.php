<?php

namespace Celest;

use Celest\Chrono\IsoChronology;
use Celest\Chrono\IsoEra;
use Celest\Format\DateTimeFormatter;
use Celest\Temporal\ChronoField;
use Celest\Temporal\ChronoUnit;
use Celest\Temporal\TemporalAccessor;
use Celest\Temporal\TemporalAmount;
use Celest\Temporal\TemporalQueries;
use Celest\Temporal\TemporalQuery;
use Celest\Temporal\TemporalUnit;
use PHPUnit_Framework_TestCase;

class TCKYearTest extends AbstractDateTimeTest
{
    /** @var Year */
    private static $TEST_2008;

    public function __construct($name = null, array $data = array(), $dataName = '')
    {
        self::$TEST_2008 = Year::of(2008);
        parent::__construct($name, $data, $dataName);
    }

    protected function samples()
    {
        return [self::$TEST_2008];
    }

    protected function validFields()
    {
        return [
            ChronoField::YEAR_OF_ERA(),
            ChronoField::YEAR(),
            ChronoField::ERA(),
        ];
    }

    protected function invalidFields()
    {
        /* TODO
            list.removeAll(validFields());
            list.add(JulianFields.JULIAN_DAY);
            list.add(JulianFields.MODIFIED_JULIAN_DAY);
            list.add(JulianFields.RATA_DIE);*/
        return [];
    }

    //-----------------------------------------------------------------------
    // now()
    //-----------------------------------------------------------------------
    public function test_now()
    {
        $expected = Year::nowOf(Clock::systemDefaultZone());
        $test = Year::now();
        for ($i = 0; $i < 100; $i++) {
            if ($expected->equals($test)) {
                return;
            }
            $expected = Year::nowOf(Clock::systemDefaultZone());
            $test = Year::now();
        }
        $this->assertEquals($test, $expected);
    }

    //-----------------------------------------------------------------------
    // now(ZoneId)
    //-----------------------------------------------------------------------
    public function test_now_ZoneId_nullZoneId()
    {
        TestHelper::assertNullException($this, function () {
            Year::nowIn(null);
        });
    }

    public function test_now_ZoneId()
    {
        $zone = ZoneId::of("UTC+01:02:03");
        $expected = Year::nowOf(Clock::system($zone));
        $test = Year::nowIn($zone);
        for ($i = 0; $i < 100; $i++) {
            if ($expected->equals($test)) {
                return;
            }
            $expected = Year::nowOf(Clock::system($zone));
            $test = Year::nowIn($zone);
        }
        $this->assertEquals($test, $expected);
    }

    //-----------------------------------------------------------------------
    // now(Clock)
    //-----------------------------------------------------------------------
    public function test_now_Clock()
    {
        $instant = OffsetDateTime::ofDateAndTime(LocalDate::of(2010, 12, 31), LocalTime::of(0, 0), ZoneOffset::UTC())->toInstant();
        $clock = Clock::fixed($instant, ZoneOffset::UTC());
        $test = Year::nowof($clock);
        $this->assertEquals($test->getValue(), 2010);
    }

    public function test_now_Clock_nullClock()
    {
        TestHelper::assertNullException($this, function () {
            Year::nowOf(null);
        });
    }

    //-----------------------------------------------------------------------
    public function test_factory_int_singleton()
    {
        for ($i = -4; $i <= 2104; $i++) {
            $test = Year::of($i);
            $this->assertEquals($test->getValue(), $i);
            $this->assertEquals(Year::of($i), $test);
        }
    }

    /**
     * @expectedException     \Celest\DateTimeException
     */
    public function test_factory_int_tooLow()
    {
        Year::of(Year::MIN_VALUE - 1);
    }

    /**
     * @expectedException     \Celest\DateTimeException
     */
    public function test_factory_int_tooHigh()
    {
        Year::of(Year::MAX_VALUE + 1);
    }

    //-----------------------------------------------------------------------
    public function test_from_TemporalAccessor()
    {
        $this->assertEquals(Year::from(LocalDate::of(2007, 7, 15)), Year::of(2007));
    }

    /**
     * @expectedException     \Celest\DateTimeException
     */
    public function test_from_TemporalAccessor_invalid_noDerive()
    {
        Year::from(LocalTime::of(12, 30));
    }

    public function test_from_TemporalAccessor_null()
    {
        TestHelper::assertNullException($this, function () {
            Year::from(null);
        });
    }

    //-----------------------------------------------------------------------
    // parse()
    //-----------------------------------------------------------------------
    public function provider_goodParseData()
    {
        return [
            ["0000", Year::of(0)],
            ["9999", Year::of(9999)],
            ["2000", Year::of(2000)],

            ["+12345678", Year::of(12345678)],
            ["+123456", Year::of(123456)],
            ["-1234", Year::of(-1234)],
            ["-12345678", Year::of(-12345678)],

            ["+" . Year::MAX_VALUE, Year::of(Year::MAX_VALUE)],
            ["" . Year::MIN_VALUE, Year::of(Year::MIN_VALUE)],
        ];
    }

    /**
     * @dataProvider provider_goodParseData
     */
    public function test_factory_parse_success($text, Year $expected)
    {
        $year = Year::parse($text);
        $this->assertEquals($expected, $year);
    }

    public function provider_badParseData()
    {
        return [[
            "", 0],
            [
                "-00", 1],
            [
                "--01-0", 1],
            [
                "A01", 0],
            [
                "200", 0],
            [
                "2009/12", 4],

            [
                "-0000-10", 0],
            [
                "-12345678901-10", 11],
            [
                "+1-10", 1],
            [
                "+12-10", 1],
            [
                "+123-10", 1],
            [
                "+1234-10", 0],
            [
                "12345-10", 0],
            [
                "+12345678901-10", 11],
        ];
    }

    /**
     * @dataProvider provider_badParseData
     * @expectedException     \Celest\DateTimeParseException
     */
    public function test_factory_parse_fail($text, $pos)
    {
        try {
            Year::parse($text);
            $this->fail(sprintf("Parse should have failed for %s at position %d", $text, $pos));
        } catch (DateTimeParseException $ex) {
            $this->assertEquals($ex->getParsedString(), $text);
            $this->assertEquals($ex->getErrorIndex(), $pos);
            throw $ex;
        }
    }

    public function test_factory_parse_nullText()
    {
        TestHelper::assertNullException($this, function () {
            Year::parse(null);
        });
    }

    //-----------------------------------------------------------------------
    // isSupported(TemporalField)
    //-----------------------------------------------------------------------
    public function test_isSupported_TemporalField()
    {
        // TODO check
//$this->assertEquals(TEST_2008.isSupported((TemporalField) null()), false);
        $this->assertEquals(self::$TEST_2008->isSupported(ChronoField::NANO_OF_SECOND()), false);
        $this->assertEquals(self::$TEST_2008->isSupported(ChronoField::NANO_OF_DAY()), false);
        $this->assertEquals(self::$TEST_2008->isSupported(ChronoField::MICRO_OF_SECOND()), false);
        $this->assertEquals(self::$TEST_2008->isSupported(ChronoField::MICRO_OF_DAY()), false);
        $this->assertEquals(self::$TEST_2008->isSupported(ChronoField::MILLI_OF_SECOND()), false);
        $this->assertEquals(self::$TEST_2008->isSupported(ChronoField::MILLI_OF_DAY()), false);
        $this->assertEquals(self::$TEST_2008->isSupported(ChronoField::SECOND_OF_MINUTE()), false);
        $this->assertEquals(self::$TEST_2008->isSupported(ChronoField::SECOND_OF_DAY()), false);
        $this->assertEquals(self::$TEST_2008->isSupported(ChronoField::MINUTE_OF_HOUR()), false);
        $this->assertEquals(self::$TEST_2008->isSupported(ChronoField::MINUTE_OF_DAY()), false);
        $this->assertEquals(self::$TEST_2008->isSupported(ChronoField::HOUR_OF_AMPM()), false);
        $this->assertEquals(self::$TEST_2008->isSupported(ChronoField::CLOCK_HOUR_OF_AMPM()), false);
        $this->assertEquals(self::$TEST_2008->isSupported(ChronoField::HOUR_OF_DAY()), false);
        $this->assertEquals(self::$TEST_2008->isSupported(ChronoField::CLOCK_HOUR_OF_DAY()), false);
        $this->assertEquals(self::$TEST_2008->isSupported(ChronoField::AMPM_OF_DAY()), false);
        $this->assertEquals(self::$TEST_2008->isSupported(ChronoField::DAY_OF_WEEK()), false);
        $this->assertEquals(self::$TEST_2008->isSupported(ChronoField::ALIGNED_DAY_OF_WEEK_IN_MONTH()), false);
        $this->assertEquals(self::$TEST_2008->isSupported(ChronoField::ALIGNED_DAY_OF_WEEK_IN_YEAR()), false);
        $this->assertEquals(self::$TEST_2008->isSupported(ChronoField::DAY_OF_MONTH()), false);
        $this->assertEquals(self::$TEST_2008->isSupported(ChronoField::DAY_OF_YEAR()), false);
        $this->assertEquals(self::$TEST_2008->isSupported(ChronoField::EPOCH_DAY()), false);
        $this->assertEquals(self::$TEST_2008->isSupported(ChronoField::ALIGNED_WEEK_OF_MONTH()), false);
        $this->assertEquals(self::$TEST_2008->isSupported(ChronoField::ALIGNED_WEEK_OF_YEAR()), false);
        $this->assertEquals(self::$TEST_2008->isSupported(ChronoField::MONTH_OF_YEAR()), false);
        $this->assertEquals(self::$TEST_2008->isSupported(ChronoField::PROLEPTIC_MONTH()), false);
        $this->assertEquals(self::$TEST_2008->isSupported(ChronoField::YEAR()), true);
        $this->assertEquals(self::$TEST_2008->isSupported(ChronoField::YEAR_OF_ERA()), true);
        $this->assertEquals(self::$TEST_2008->isSupported(ChronoField::ERA()), true);
        $this->assertEquals(self::$TEST_2008->isSupported(ChronoField::INSTANT_SECONDS()), false);
        $this->assertEquals(self::$TEST_2008->isSupported(ChronoField::OFFSET_SECONDS()), false);
    }

//-----------------------------------------------------------------------
// isSupported(TemporalUnit)
//-----------------------------------------------------------------------
    public function test_isSupported_TemporalUnit()
    {
        // TODO check
        //$this->assertEquals(self::$TEST_2008->isUnitSupported(null), false);
        $this->assertEquals(self::$TEST_2008->isUnitSupported(ChronoUnit::NANOS()), false);
        $this->assertEquals(self::$TEST_2008->isUnitSupported(ChronoUnit::MICROS()), false);
        $this->assertEquals(self::$TEST_2008->isUnitSupported(ChronoUnit::MILLIS()), false);
        $this->assertEquals(self::$TEST_2008->isUnitSupported(ChronoUnit::SECONDS()), false);
        $this->assertEquals(self::$TEST_2008->isUnitSupported(ChronoUnit::MINUTES()), false);
        $this->assertEquals(self::$TEST_2008->isUnitSupported(ChronoUnit::HOURS()), false);
        $this->assertEquals(self::$TEST_2008->isUnitSupported(ChronoUnit::HALF_DAYS()), false);
        $this->assertEquals(self::$TEST_2008->isUnitSupported(ChronoUnit::DAYS()), false);
        $this->assertEquals(self::$TEST_2008->isUnitSupported(ChronoUnit::WEEKS()), false);
        $this->assertEquals(self::$TEST_2008->isUnitSupported(ChronoUnit::MONTHS()), false);
        $this->assertEquals(self::$TEST_2008->isUnitSupported(ChronoUnit::YEARS()), true);
        $this->assertEquals(self::$TEST_2008->isUnitSupported(ChronoUnit::DECADES()), true);
        $this->assertEquals(self::$TEST_2008->isUnitSupported(ChronoUnit::CENTURIES()), true);
        $this->assertEquals(self::$TEST_2008->isUnitSupported(ChronoUnit::MILLENNIA()), true);
        $this->assertEquals(self::$TEST_2008->isUnitSupported(ChronoUnit::ERAS()), true);
        $this->assertEquals(self::$TEST_2008->isUnitSupported(ChronoUnit::FOREVER()), false);
    }

    //-----------------------------------------------------------------------
    // get(TemporalField)
    //-----------------------------------------------------------------------
    public function test_get_TemporalField()
    {
        $this->assertEquals(self::$TEST_2008->get(ChronoField::YEAR()), 2008);
        $this->assertEquals(self::$TEST_2008->get(ChronoField::YEAR_OF_ERA()), 2008);
        $this->assertEquals(self::$TEST_2008->get(ChronoField::ERA()), 1);
    }

    public function test_getLong_TemporalField()
    {
        $this->assertEquals(self::$TEST_2008->getLong(ChronoField::YEAR()), 2008);
        $this->assertEquals(self::$TEST_2008->getLong(ChronoField::YEAR_OF_ERA()), 2008);
        $this->assertEquals(self::$TEST_2008->getLong(ChronoField::ERA()), 1);
    }

    //-----------------------------------------------------------------------
    // query(TemporalQuery)
    //-----------------------------------------------------------------------
    function data_query()
    {
        return [
            [
                self::$TEST_2008, TemporalQueries::chronology(), IsoChronology::INSTANCE()],
            [
                self::$TEST_2008, TemporalQueries::zoneId(), null],
            [
                self::$TEST_2008, TemporalQueries::precision(), ChronoUnit::YEARS()],
            [
                self::$TEST_2008, TemporalQueries::zone(), null],
            [
                self::$TEST_2008, TemporalQueries::offset(), null],
            [
                self::$TEST_2008, TemporalQueries::localDate(), null],
            [
                self::$TEST_2008, TemporalQueries::localTime(), null],
        ];
    }

    /**
     * @dataProvider data_query
     */
    public function test_query(TemporalAccessor $temporal, TemporalQuery $query, $expected)
    {
        $this->assertEquals($temporal->query($query), $expected);
    }

    /**
     * @dataProvider data_query
     */
    public
    function test_queryFrom(TemporalAccessor $temporal, TemporalQuery $query, $expected)
    {
        $this->assertEquals($query->queryFrom($temporal), $expected);
    }

    public function test_query_null()
    {
        TestHelper::assertNullException($this, function () {
            self::$TEST_2008->query(null);
        });
    }

//-----------------------------------------------------------------------
// isLeap()
//-----------------------------------------------------------------------
    public
    function test_isLeap()
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
    // plus(Period)
    //-----------------------------------------------------------------------
    function data_plusValid()
    {
        return [
            [2012, Period::ofYears(0), 2012],
            [2012, Period::ofYears(1), 2013],
            [2012, Period::ofYears(2), 2014],
            [2012, Period::ofYears(-2), 2010],
        ];
    }

    /**
     * @dataProvider data_plusValid
     */
    public function test_plusValid($year, TemporalAmount $amount, $expected)
    {
        $this->assertEquals(Year::of($year)->plusAmount($amount), Year::of($expected));
    }

    function data_plusInvalidUnit()
    {
        return [[
            Period::of(0, 1, 0)],
            [
                Period::of(0, 0, 1)],
            [
                Period::of(0, 1, 1)],
            [
                Period::of(1, 1, 1)],
            [
                Duration::ofDays(1)],
            [
                Duration::ofHours(1)],
            [
                Duration::ofMinutes(1)],
            [
                Duration::ofSeconds(1)],
        ];
    }

    /**
     * @dataProvider data_plusInvalidUnit
     * @expectedException \Celest\Temporal\UnsupportedTemporalTypeException
     */
    public function test_plusInvalidUnit(TemporalAmount $amount)
    {
        self::$TEST_2008->plusAmount($amount);
    }

    public function test_plus_null()
    {
        TestHelper::assertNullException($this, function () {
            self::$TEST_2008->plusAmount(null);
        });
    }

//-----------------------------------------------------------------------
// plusYears()
//-----------------------------------------------------------------------
    public
    function test_plusYears()
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
     * @expectedException     \Celest\DateTimeException
     */
    public
    function test_plusYears_max()
    {
        Year::of(Year :: MAX_VALUE)->plusYears(1);
    }

    /**
     * @expectedException     \Celest\DateTimeException
     */
    public
    function test_plusYears_maxLots()
    {
        Year::of(Year :: MAX_VALUE)->plusYears(1000);
    }

    /**
     * @expectedException     \Celest\DateTimeException
     */
    public
    function test_plusYears_min()
    {
        Year::of(Year :: MIN_VALUE)->plusYears(-1);
    }

    /**
     * @expectedException     \Celest\DateTimeException
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
                Year::of(999999999), 1, ChronoUnit::YEARS(), null, 'Celest\DateTimeException'],
            [
                Year::of(-999999999), -1, ChronoUnit::YEARS(), null, 'Celest\DateTimeException'],

            [
                Year::of(1), 0, ChronoUnit::DAYS(), null, 'Celest\DateTimeException'],
            [
                Year::of(1), 0, ChronoUnit::WEEKS(), null, 'Celest\DateTimeException'],
            [
                Year::of(1), 0, ChronoUnit::MONTHS(), null, 'Celest\DateTimeException'],
        ];
    }

    /**
     * @dataProvider data_plus_long_TemporalUnit
     */
    public
    function test_plus_long_TemporalUnit(Year $base, $amount, TemporalUnit $unit, $expectedYear, $expectedEx)
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

    //-----------------------------------------------------------------------
    // minus(Period)
    //-----------------------------------------------------------------------
    function data_minusValid()
    {
        return
            [
                [
                    2012, Period::ofYears(0), 2012
                ],
                [
                    2012, Period::ofYears(1), 2011],
                [
                    2012, Period::ofYears(2), 2010],
                [
                    2012, Period::ofYears(-2), 2014],
            ];
    }

    /**
     * @dataProvider data_minusValid
     */
    public function test_minusValid($year, TemporalAmount $amount, $expected)
    {
        $this->assertEquals(Year::of($year)->minusAmount($amount), Year:: of($expected));
    }

    function data_minusInvalidUnit()
    {
        return [
            [Period::of(0, 1, 0)],
            [
                Period::of(0, 0, 1)],
            [
                Period::of(0, 1, 1)],
            [
                Period::of(1, 1, 1)],
            [
                Duration::ofDays(1)],
            [
                Duration::ofHours(1)],
            [
                Duration::ofMinutes(1)],
            [
                Duration::ofSeconds(1)],
        ];
    }

    /**
     * @dataProvider data_minusInvalidUnit
     * @expectedException \Celest\Temporal\UnsupportedTemporalTypeException
     */
    public function test_minusInvalidUnit(TemporalAmount $amount)
    {
        self::$TEST_2008->minusAmount($amount);
    }

    public function test_minus_null()
    {
        TestHelper::assertNullException($this, function () {
            self::$TEST_2008->minusAmount(null);
        });
    }

    //-----------------------------------------------------------------------
    // minusYears()
    //-----------------------------------------------------------------------
    public function test_minusYears()
    {
        $this->assertEquals(Year::of(2007)->minusYears(-1), Year::of(2008));
        $this->assertEquals(Year::of(2007)->minusYears(0), Year::of(2007));
        $this->assertEquals(Year::of(2007)->minusYears(1), Year::of(2006));
        $this->assertEquals(Year::of(2007)->minusYears(2), Year::of(2005));

        $this->assertEquals(Year::of(Year::MAX_VALUE - 1)->minusYears(-1), Year::of(Year::MAX_VALUE));
        $this->assertEquals(Year::of(Year::MAX_VALUE)->minusYears(0), Year::of(Year::MAX_VALUE));

        $this->assertEquals(Year::of(Year::MIN_VALUE + 1)->minusYears(1), Year::of(Year::MIN_VALUE));
        $this->assertEquals(Year::of(Year::MIN_VALUE)->minusYears(0), Year::of(Year::MIN_VALUE));
    }

    public function test_minusYear_zero_equals()
    {
        $base = Year::of(2007);
        $this->assertEquals($base->minusYears(0), $base);
    }

    public function test_minusYears_big()
    {
        $years = 20 + Year::MAX_VALUE;
        $this->assertEquals(Year::of(40)->minusYears($years), Year::of((int)(40 - $years)));
    }

    /**
     * @expectedException     \Celest\DateTimeException
     */
    public function test_minusYears_max()
    {
        Year::of(Year::MAX_VALUE)->minusYears(-1);
    }

    /**
     * @expectedException     \Celest\DateTimeException
     */
    public function test_minusYears_maxLots()
    {
        Year::of(Year::MAX_VALUE)->minusYears(-1000);
    }

    /**
     * @expectedException     \Celest\DateTimeException
     */
    public function test_minusYears_min()
    {
        Year::of(Year::MIN_VALUE)->minusYears(1);
    }

    /**
     * @expectedException     \Celest\DateTimeException
     */
    public function test_minusYears_minLots()
    {
        Year::of(Year::MIN_VALUE)->minusYears(1000);
    }

    //-----------------------------------------------------------------------
    // minus(long, TemporalUnit)
    //-----------------------------------------------------------------------
    function data_minus_long_TemporalUnit()
    {
        return [
            [
                Year::of(1), 1, ChronoUnit::YEARS(), Year::of(0), null],
            [
                Year::of(1), -12, ChronoUnit::YEARS(), Year::of(13), null],
            [
                Year::of(1), 0, ChronoUnit::YEARS(), Year::of(1), null],
            [
                Year::of(999999999), 0, ChronoUnit::YEARS(), Year::of(999999999), null],
            [
                Year::of(-999999999), 0, ChronoUnit::YEARS(), Year::of(-999999999), null],
            [
                Year::of(0), -999999999, ChronoUnit::YEARS(), Year::of(999999999), null],
            [
                Year::of(0), 999999999, ChronoUnit::YEARS(), Year::of(-999999999), null],

            [
                Year::of(999999999), 1, ChronoUnit::ERAS(), Year::of(-999999999 + 1), null],
            [
                Year::of(105), 1, ChronoUnit::CENTURIES(), Year::of(5), null],
            [
                Year::of(15), 1, ChronoUnit::DECADES(), Year::of(5), null],

            [
                Year::of(-999999999), 1, ChronoUnit::YEARS(), null, 'Celest\DateTimeException'],
            [
                Year::of(1), -999999999, ChronoUnit::YEARS(), null, 'Celest\DateTimeException'],

            [
                Year::of(1), 0, ChronoUnit::DAYS(), null, 'Celest\DateTimeException'],
            [
                Year::of(1), 0, ChronoUnit::WEEKS(), null, 'Celest\DateTimeException'],
            [
                Year::of(1), 0, ChronoUnit::MONTHS(), null, 'Celest\DateTimeException'],
        ];
    }

    /**
     * @dataProvider data_minus_long_TemporalUnit
     */
    public function test_minus_long_TemporalUnit(Year $base, $amount, TemporalUnit $unit, $expectedYear, $expectedEx)
    {
        if ($expectedEx == null) {
            $this->assertEquals($base->minus($amount, $unit), $expectedYear);
        } else {
            try {
                $result = $base->minus($amount, $unit);
                $this->fail();
            } catch (\Exception $ex) {
                $this->assertTrue($ex instanceof $expectedEx);
            }
        }
    }

//-----------------------------------------------------------------------
// adjustInto()
//-----------------------------------------------------------------------
    public
    function test_adjustDate()
    {
        $base = LocalDate::of(2007, 2, 12);
        for ($i = -4; $i <= 2104; $i++) {
            $result = Year::of($i)->adjustInto($base);
            $this->assertEquals($result, LocalDate::of($i, 2, 12));
        }
    }

    public
    function test_adjustDate_resolve()
    {
        $test = Year::of(2011);
        $this->assertEquals($test->adjustInto(LocalDate::of(2012, 2, 29)), LocalDate::of(2011, 2, 28));
    }

    public
    function test_adjustDate_nullLocalDate()
    {
        TestHelper::assertNullException($this, function () {
            $test = Year::of(1);
            $test->adjustInto(null);
        });
    }

    //-----------------------------------------------------------------------
    // with(TemporalAdjuster)
    //-----------------------------------------------------------------------
    public function test_with_TemporalAdjuster()
    {
        $base = Year::of(-10);
        for ($i = -4; $i <= 2104; $i++) {
            $result = $base->adjust(Year::of($i));
            $this->assertEquals($result, Year::of($i));
        }
    }

    /**
     * @expectedException \Celest\DateTimeException
     */
    public function test_with_BadTemporalAdjuster()
    {
        $test = Year::of(1);
        $test->adjust(LocalTime::of(18, 1, 2));
    }

    //-----------------------------------------------------------------------
    // with(TemporalField, long)
    //-----------------------------------------------------------------------
    public function test_with()
    {
        $base = Year::of(5);
        $result = $base->with(ChronoField::ERA(), 0);
        $ad = $base->adjust(IsoEra::of(0));
        $this->assertEquals($result, $ad);

        $prolepticYear = IsoChronology::INSTANCE()->prolepticYear(IsoEra::of(0), 5);
        $this->assertEquals($result->get(ChronoField::ERA()), 0);
        $this->assertEquals($result->get(ChronoField::YEAR()), $prolepticYear);
        $this->assertEquals($result->get(ChronoField::YEAR_OF_ERA()), 5);

        $result = $base->with(ChronoField::YEAR(), 10);
        $this->assertEquals($result->get(ChronoField::ERA()), $base->get(ChronoField::ERA()));
        $this->assertEquals($result->get(ChronoField::YEAR()), 10);
        $this->assertEquals($result->get(ChronoField::YEAR_OF_ERA()), 10);

        $result = $base->with(ChronoField::YEAR_OF_ERA(), 20);
        $this->assertEquals($result->get(ChronoField::ERA()), $base->get(ChronoField::ERA()));
        $this->assertEquals($result->get(ChronoField::YEAR()), 20);
        $this->assertEquals($result->get(ChronoField::YEAR_OF_ERA()), 20);
    }

    //-----------------------------------------------------------------------
    // length()
    //-----------------------------------------------------------------------
    public function test_length()
    {
        $this->assertEquals(Year::of(1999)->length(), 365);
        $this->assertEquals(Year::of(2000)->length(), 366);
        $this->assertEquals(Year::of(2001)->length(), 365);

        $this->assertEquals(Year::of(2007)->length(), 365);
        $this->assertEquals(Year::of(2008)->length(), 366);
        $this->assertEquals(Year::of(2009)->length(), 365);
        $this->assertEquals(Year::of(2010)->length(), 365);
        $this->assertEquals(Year::of(2011)->length(), 365);
        $this->assertEquals(Year::of(2012)->length(), 366);

        $this->assertEquals(Year::of(2095)->length(), 365);
        $this->assertEquals(Year::of(2096)->length(), 366);
        $this->assertEquals(Year::of(2097)->length(), 365);
        $this->assertEquals(Year::of(2098)->length(), 365);
        $this->assertEquals(Year::of(2099)->length(), 365);
        $this->assertEquals(Year::of(2100)->length(), 365);
        $this->assertEquals(Year::of(2101)->length(), 365);
        $this->assertEquals(Year::of(2102)->length(), 365);
        $this->assertEquals(Year::of(2103)->length(), 365);
        $this->assertEquals(Year::of(2104)->length(), 366);
        $this->assertEquals(Year::of(2105)->length(), 365);

        $this->assertEquals(Year::of(-500)->length(), 365);
        $this->assertEquals(Year::of(-400)->length(), 366);
        $this->assertEquals(Year::of(-300)->length(), 365);
        $this->assertEquals(Year::of(-200)->length(), 365);
        $this->assertEquals(Year::of(-100)->length(), 365);
        $this->assertEquals(Year::of(0)->length(), 366);
        $this->assertEquals(Year::of(100)->length(), 365);
        $this->assertEquals(Year::of(200)->length(), 365);
        $this->assertEquals(Year::of(300)->length(), 365);
        $this->assertEquals(Year::of(400)->length(), 366);
        $this->assertEquals(Year::of(500)->length(), 365);
    }

    //-----------------------------------------------------------------------
    // isValidMonthDay(MonthDay)
    //-----------------------------------------------------------------------
    function data_isValidMonthDay()
    {
        return [
            [
                Year::of(2007), MonthDay::of(6, 30), true],
            [
                Year::of(2008), MonthDay::of(2, 28), true],
            [
                Year::of(2008), MonthDay::of(2, 29), true],
            [
                Year::of(2009), MonthDay::of(2, 28), true],
            [
                Year::of(2009), MonthDay::of(2, 29), false],
            [
                Year::of(2009), null, false],
        ];
    }

    /**
     * @dataProvider data_isValidMonthDay
     */
    public function test_isValidMonthDay(Year $year, $monthDay, $expected)
    {
        $this->assertEquals($year->isValidMonthDay($monthDay), $expected);
    }

//-----------------------------------------------------------------------
// until(Temporal, TemporalUnit)
//-----------------------------------------------------------------------
    function data_periodUntilUnit()
    {
        return [
            [
                Year::of(2000), Year::of(-1), ChronoUnit::YEARS(), -2001],
            [
                Year::of(2000), Year::of(0), ChronoUnit::YEARS(), -2000],
            [
                Year::of(2000), Year::of(1), ChronoUnit::YEARS(), -1999],
            [
                Year::of(2000), Year::of(1998), ChronoUnit::YEARS(), -2],
            [
                Year::of(2000), Year::of(1999), ChronoUnit::YEARS(), -1],
            [
                Year::of(2000), Year::of(2000), ChronoUnit::YEARS(), 0],
            [
                Year::of(2000), Year::of(2001), ChronoUnit::YEARS(), 1],
            [
                Year::of(2000), Year::of(2002), ChronoUnit::YEARS(), 2],
            [
                Year::of(2000), Year::of(2246), ChronoUnit::YEARS(), 246],

            [
                Year::of(2000), Year::of(-1), ChronoUnit::DECADES(), -200],
            [
                Year::of(2000), Year::of(0), ChronoUnit::DECADES(), -200],
            [
                Year::of(2000), Year::of(1), ChronoUnit::DECADES(), -199],
            [
                Year::of(2000), Year::of(1989), ChronoUnit::DECADES(), -1],
            [
                Year::of(2000), Year::of(1990), ChronoUnit::DECADES(), -1],
            [
                Year::of(2000), Year::of(1991), ChronoUnit::DECADES(), 0],
            [
                Year::of(2000), Year::of(2000), ChronoUnit::DECADES(), 0],
            [
                Year::of(2000), Year::of(2009), ChronoUnit::DECADES(), 0],
            [
                Year::of(2000), Year::of(2010), ChronoUnit::DECADES(), 1],
            [
                Year::of(2000), Year::of(2011), ChronoUnit::DECADES(), 1],

            [
                Year::of(2000), Year::of(-1), ChronoUnit::CENTURIES(), -20],
            [
                Year::of(2000), Year::of(0), ChronoUnit::CENTURIES(), -20],
            [
                Year::of(2000), Year::of(1), ChronoUnit::CENTURIES(), -19],
            [
                Year::of(2000), Year::of(1899), ChronoUnit::CENTURIES(), -1],
            [
                Year::of(2000), Year::of(1900), ChronoUnit::CENTURIES(), -1],
            [
                Year::of(2000), Year::of(1901), ChronoUnit::CENTURIES(), 0],
            [
                Year::of(2000), Year::of(2000), ChronoUnit::CENTURIES(), 0],
            [
                Year::of(2000), Year::of(2099), ChronoUnit::CENTURIES(), 0],
            [
                Year::of(2000), Year::of(2100), ChronoUnit::CENTURIES(), 1],
            [
                Year::of(2000), Year::of(2101), ChronoUnit::CENTURIES(), 1],

            [
                Year::of(2000), Year::of(-1), ChronoUnit::MILLENNIA(), -2],
            [
                Year::of(2000), Year::of(0), ChronoUnit::MILLENNIA(), -2],
            [
                Year::of(2000), Year::of(1), ChronoUnit::MILLENNIA(), -1],
            [
                Year::of(2000), Year::of(999), ChronoUnit::MILLENNIA(), -1],
            [
                Year::of(2000), Year::of(1000), ChronoUnit::MILLENNIA(), -1],
            [
                Year::of(2000), Year::of(1001), ChronoUnit::MILLENNIA(), 0],
            [
                Year::of(2000), Year::of(2000), ChronoUnit::MILLENNIA(), 0],
            [
                Year::of(2000), Year::of(2999), ChronoUnit::MILLENNIA(), 0],
            [
                Year::of(2000), Year::of(3000), ChronoUnit::MILLENNIA(), 1],
            [
                Year::of(2000), Year::of(3001), ChronoUnit::MILLENNIA(), 1],
        ];
    }

    /**
     * @dataProvider data_periodUntilUnit
     */
    public function test_until_TemporalUnit(Year $year1, Year $year2, TemporalUnit $unit, $expected)
    {
        $amount = $year1->until($year2, $unit);
        $this->assertEquals($amount, $expected);
    }

    /**
     * @dataProvider data_periodUntilUnit
     */
    public
    function test_until_TemporalUnit_negated(Year $year1, Year $year2, TemporalUnit $unit, $expected)
    {
        $amount = $year2->until($year1, $unit);
        $this->assertEquals($amount, -$expected);
    }

    /**
     * @dataProvider data_periodUntilUnit
     */
    public
    function test_until_TemporalUnit_between(Year $year1, Year $year2, TemporalUnit $unit, $expected)
    {
        $amount = $unit->between($year1, $year2);
        $this->assertEquals($amount, $expected);
    }

    public
    function test_until_convertedType()
    {
        $start = Year::of(2010);
        $end = $start->plusYears(2)->atMonth(Month::APRIL());
        $this->assertEquals($start->until($end, ChronoUnit::YEARS()), 2);
    }

    /**
     * @expectedException     \Celest\DateTimeException
     */
    public function test_until_invalidType()
    {
        $start = Year::of(2010);
        $start->until(LocalTime::of(11, 30), ChronoUnit::YEARS());
    }

    /**
     * @expectedException     \Celest\Temporal\UnsupportedTemporalTypeException
     */
    public function test_until_TemporalUnit_unsupportedUnit()
    {
        self::$TEST_2008->until(self::$TEST_2008, ChronoUnit::MONTHS());
    }

    public function test_until_TemporalUnit_nullEnd()
    {
        TestHelper::assertNullException($this, function () {
            self::$TEST_2008->until(null, ChronoUnit::DAYS());
        });
    }

    public function test_until_TemporalUnit_nullUnit()
    {
        TestHelper::assertNullException($this, function () {
            self::$TEST_2008->until(self::$TEST_2008, null);
        });
    }

    //-----------------------------------------------------------------------
    // format(DateTimeFormatter)
    //-----------------------------------------------------------------------
    public function test_format_formatter()
    {
        $f = DateTimeFormatter::ofPattern("y");
        $t = Year::of(2010)->format($f);
        $this->assertEquals($t, "2010");
    }

    public function test_format_formatter_null()
    {
        TestHelper::assertNullException($this, function () {
            Year::of(2010)->format(null);
        });
    }

    //-----------------------------------------------------------------------
    // atMonth(Month)
    //-----------------------------------------------------------------------
    public function test_atMonth()
    {
        $test = Year::of(2008);
        $this->assertEquals($test->atMonth(Month::JUNE()), YearMonth::of(2008, 6));
    }

    public function test_atMonth_nullMonth()
    {
        TestHelper::assertNullException($this, function () {
            $test = Year::of(2008);
            $test->atMonth(null);
        });
    }

    //-----------------------------------------------------------------------
    // atMonth(int)
    //-----------------------------------------------------------------------
    public function test_atMonth_int()
    {
        $test = Year::of(2008);
        $this->assertEquals($test->atMonthNumerical(6), YearMonth::of(2008, 6));
    }

    /**
     * @expectedException \Celest\DateTimeException
     */
    public function test_atMonth_int_invalidMonth()
    {
        $test = Year::of(2008);
        $test->atMonthNumerical(13);
    }

    //-----------------------------------------------------------------------
    // atMonthDay(MonthDay)
    //-----------------------------------------------------------------------
    function data_atMonthDay()
    {
        return [
            [
                Year::of(2008), MonthDay::of(6, 30), LocalDate::of(2008, 6, 30)],
            [
                Year::of(2008), MonthDay::of(2, 29), LocalDate::of(2008, 2, 29)],
            [
                Year::of(2009), MonthDay::of(2, 29), LocalDate::of(2009, 2, 28)],
        ];
    }

    /**
     * @dataProvider data_atMonthDay
     */
    public function test_atMonthDay(Year $year, MonthDay $monthDay, LocalDate $expected)
    {
        $this->assertEquals($year->atMonthDay($monthDay), $expected);
    }

    public
    function test_atMonthDay_nullMonthDay()
    {
        TestHelper::assertNullException($this, function () {
            $test = Year::of(2008);
            $test->atMonthDay(null);
        });
    }

//-----------------------------------------------------------------------
// atDay(int)
//-----------------------------------------------------------------------
    public
    function test_atDay_notLeapYear()
    {
        $test = Year::of(2007);
        $expected = LocalDate::of(2007, 1, 1);
        for ($i = 1; $i <= 365; $i++) {
            $this->assertEquals($test->atDay($i), $expected);
            $expected = $expected->plusDays(1);
        }
    }

    /**
     * @expectedException \Celest\DateTimeException
     */
    public
    function test_atDay_notLeapYear_day366()
    {
        $test = Year::of(2007);
        $test->atDay(366);
    }

    public
    function test_atDay_leapYear()
    {
        $test = Year::of(2008);
        $expected = LocalDate::of(2008, 1, 1);
        for ($i = 1; $i <= 366; $i++) {
            $this->assertEquals($test->atDay($i), $expected);
            $expected = $expected->plusDays(1);
        }
    }

    /**
     * @expectedException \Celest\DateTimeException
     */
    public
    function test_atDay_day0()
    {
        $test = Year::of(2007);
        $test->atDay(0);
    }

    /**
     * @expectedException \Celest\DateTimeException
     */
    public
    function test_atDay_day367()
    {
        $test = Year::of(2007);
        $test->atDay(367);
    }

    //-----------------------------------------------------------------------
    // compareTo()
    //-----------------------------------------------------------------------
    /**
     * @group long
     */
    public function test_compareTo()
    {
        for ($i = -4; $i <= 2104;
             $i++) {
            $a = Year:: of($i);
            for ($j = -4; $j <= 2104; $j++) {
                $b = Year:: of($j);
                if ($i < $j) {
                    $this->assertEquals($a->compareTo($b) < 0, true);
                    $this->assertEquals($b->compareTo($a) > 0, true);
                    $this->assertEquals($a->isAfter($b), false);
                    $this->assertEquals($a->isBefore($b), true);
                    $this->assertEquals($b->isAfter($a), true);
                    $this->assertEquals($b->isBefore($a), false);
                } else if ($i > $j) {
                    $this->assertEquals($a->compareTo($b) > 0, true);
                    $this->assertEquals($b->compareTo($a) < 0, true);
                    $this->assertEquals($a->isAfter($b), true);
                    $this->assertEquals($a->isBefore($b), false);
                    $this->assertEquals($b->isAfter($a), false);
                    $this->assertEquals($b->isBefore($a), true);
                } else {
                    $this->assertEquals($a->compareTo($b), 0);
                    $this->assertEquals($b->compareTo($a), 0);
                    $this->assertEquals($a->isAfter($b), false);
                    $this->assertEquals($a->isBefore($b), false);
                    $this->assertEquals($b->isAfter($a), false);
                    $this->assertEquals($b->isBefore($a), false);
                }
            }
        }
    }

    public function test_compareTo_nullYear()
    {
        TestHelper::assertNullException($this, function () {
            $doy = null;
            $test = Year::of(1);
            $test->compareTo($doy);
        });
    }
    //-----------------------------------------------------------------------
    // equals() / hashCode()
    //-----------------------------------------------------------------------
    /**
     * @group long
     */
    public function test_equals()
    {
        for ($i = -4; $i <= 2104;
             $i++) {
            $a = Year::of($i);
            for ($j = -4; $j <= 2104; $j++) {
                $b = Year::of($j);
                $this->assertEquals($a->equals($b), $i == $j);
                //$this->assertEquals($a->hashCode() == $b->hashCode(), $i == $j);
            }
        }
    }

    public function test_equals_same()
    {
        $test = Year::of(2011);
        $this->assertEquals($test->equals($test), true);
    }

    public function test_equals_nullYear()
    {
        $doy = null;
        $test = Year::of(1);
        $this->assertEquals($test->equals($doy), false);
    }

    public function test_equals_incorrectType()
    {
        $test = Year::of(1);
        $this->assertEquals($test->equals("Incorrect type"), false);
    }

//-----------------------------------------------------------------------
// toString()
//-----------------------------------------------------------------------
    public
    function test_toString()
    {
        for ($i = -4; $i <= 2104;
             $i++) {
            $a = Year::of($i);
            $this->assertEquals($a->__toString(), "" . $i);
        }
    }

}