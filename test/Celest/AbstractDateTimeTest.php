<?php

namespace Celest;

use Celest\Temporal\MockFieldNoValue;
use Celest\Temporal\TemporalAccessor;
use Celest\Temporal\TemporalField;
use Celest\Temporal\TemporalQueries;

abstract class AbstractDateTimeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Sample {@code Temporal} objects.
     * @return TemporalAccessor[] the objects, not null
     */
    protected abstract function samples();

    /**
     * List of valid supported fields.
     * @return TemporalField[] the fields, not null
     */
    protected abstract function validFields();

    /**
     * List of invalid unsupported fields.
     * @return TemporalField[]  the fields, not null
     */
    protected abstract function invalidFields();

    //-----------------------------------------------------------------------
    // isSupported(TemporalField)
    //-----------------------------------------------------------------------
    public function test_basicTest_isSupported_TemporalField_supported()
    {
        foreach ($this->samples() as $sample) {
            foreach ($this->validFields() as $field) {
                $this->assertEquals($sample->isSupported($field), true, "Failed on " . $sample . " " . $field);
            }
        }
    }

    public function test_basicTest_isSupported_TemporalField_unsupported()
    {
        foreach ($this->samples() as $sample) {
            foreach ($this->invalidFields() as $field) {
                $this->assertEquals($sample->isSupported($field), false, "Failed on " . $sample . " " . $field);
            }
        }
    }

    public function test_basicTest_isSupported_TemporalField_null()
    {
        foreach ($this->samples() as $sample) {
            $this->markTestIncomplete('TBD, TemporalField::isSupported(null)');
            $this->assertEquals($sample->isSupported(null), false, "Failed on " . $sample);
        }
    }

    //-----------------------------------------------------------------------
    // range(TemporalField)
    //-----------------------------------------------------------------------
    public function test_basicTest_range_TemporalField_supported()
    {
        foreach ($this->samples() as $sample) {
            foreach ($this->validFields() as $field) {
                $sample->range($field);  // no exception
            }
        }
    }

    public function test_basicTest_range_TemporalField_unsupported()
    {
        foreach ($this->samples() as $sample) {
            foreach ($this->invalidFields() as $field) {
                try {
                    $sample->range($field);
                    $this->fail("Failed on " . $sample . " " . $field);
                } catch (DateTimeException $ex) {
                    // expected
                }
            }
        }
    }

    public function test_basicTest_range_TemporalField_null()
    {
        foreach ($this->samples() as $sample) {
            TestHelper::assertNullException($this, function() use ($sample) {
                $sample->range(null);
            });
        }
    }

    //-----------------------------------------------------------------------
    // get(TemporalField)
    //-----------------------------------------------------------------------
    public function test_basicTest_get_TemporalField_supported()
    {
        foreach ($this->samples() as $sample) {
            foreach ($this->validFields() as $field) {
                if ($sample->range($field)->isIntValue()) {
                    $sample->get($field);  // no exception
                } else {
                    try {
                        $sample->get($field);
                        $this->fail("Failed on " . $sample . " " . $field);
                    } catch (DateTimeException $ex) {
                        // expected
                    }
                }
            }
        }
    }

    public function test_basicTest_get_TemporalField_unsupported()
    {
        foreach ($this->samples() as $sample) {
            foreach ($this->invalidFields() as $field) {
                try {
                    $sample->get($field);
                    $this->fail("Failed on " . $sample . " " . $field);
                } catch (DateTimeException $ex) {
                    // expected
                }
            }
        }
    }

    /**
     * @expectedException \Celest\DateTimeException
     */
    public function test_get_TemporalField_invalidField()
    {
        foreach ($this->samples() as $sample) {
            $sample->get(MockFieldNoValue::INSTANCE());
        }
    }

    public function test_basicTest_get_TemporalField_null()
    {
        foreach ($this->samples() as $sample) {
            TestHelper::assertNullException($this, function() use ($sample) {
                $sample->get(null);
            });
        }
    }

    //-----------------------------------------------------------------------
    // getLong(TemporalField)
    //-----------------------------------------------------------------------
    public function test_basicTest_getLong_TemporalField_supported()
    {
        foreach ($this->samples() as $sample) {
            foreach ($this->validFields() as $field) {
                $sample->getLong($field);  // no exception
            }
        }
    }

    public function test_basicTest_getLong_TemporalField_unsupported()
    {
        foreach ($this->samples() as $sample) {
            foreach ($this->invalidFields() as $field) {
                try {
                    $sample->getLong($field);
                    $this->fail("Failed on " . $sample . " " . $field);
                } catch (DateTimeException $ex) {
                    // expected
                }
            }
        }
    }

    /**
     * @expectedException \Celest\DateTimeException
     */
    public function test_getLong_TemporalField_invalidField()
    {
        foreach ($this->samples() as $sample) {
            $sample->getLong(MockFieldNoValue::INSTANCE());
        }
    }

    public function test_basicTest_getLong_TemporalField_null()
    {
        foreach ($this->samples() as $sample) {
            TestHelper::assertNullException($this, function() use ($sample) {
                $sample->getLong(null);
            });
        }
    }

    //-----------------------------------------------------------------------
    public function test_basicTest_query()
    {
        foreach ($this->samples() as $sample) {
            $this->assertEquals($sample->query(TemporalQueries::fromCallable(function () {
                return "foo";
            })), "foo");
        }
    }
}