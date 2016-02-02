<?php

namespace Celest\Temporal\TemporalQuery;


use Celest\IllegalArgumentException;
use Celest\Temporal\TemporalAccessor;
use Celest\Temporal\TemporalQuery;

final class FuncTemporalQuery implements TemporalQuery
{
    private $func;

    /**
     * @param callable $func TemporalAccessor->mixed
     * @throws IllegalArgumentException
     */
    public function __construct($func) {
        if(!is_callable($func)) {
            throw new IllegalArgumentException('The supplied function is not callable.' . var_export($func, true));
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