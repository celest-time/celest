<?php

namespace Php\Time\Temporal\TemporalQuery;


use Php\Time\IllegalArgumentException;
use Php\Time\Temporal\Temporal;
use Php\Time\Temporal\TemporalAdjuster;

final class FuncTemporalAdjuster implements TemporalAdjuster
{
    private $func;

    /**
     * @param callable $func Temporal->Temporal
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
    public function adjustInto(Temporal $temporal)
    {
        return call_user_func($this->func, $temporal);
    }
}