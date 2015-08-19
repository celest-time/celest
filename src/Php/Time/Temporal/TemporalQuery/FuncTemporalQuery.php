<?php

namespace Php\Time\Temporal\TemporalQuery;


use Php\Time\IllegalArgumentException;
use Php\Time\Temporal\TemporalAccessor;
use Php\Time\Temporal\TemporalQuery;

class FuncTemporalQuery implements TemporalQuery
{
    private $func;

    /**
     * @param $func callable TemporalAccessor->mixed
     */
    public function __construct($func) {
        if(!is_callable($func)) {
            new IllegalArgumentException('The supplied function is not callable.' . $func);
        }
        $this->func = $func;
    }

    /**
     * @inheritdoc
     */
    function queryFrom(TemporalAccessor $temporal)
    {
        return call_user_func($this->func, $temporal);
    }
}