<?php

namespace Php\Time;


class UnsupportedTemporalTypeException extends \Exception
{
    /**
     * UnsupportedTemporalTypeException constructor.
     * @param string $message
     * @param \Exception $e
     */
    public function __construct($message, \Exception $e = null)
    {
        parent::__construct($message, 0, $e);
    }

}