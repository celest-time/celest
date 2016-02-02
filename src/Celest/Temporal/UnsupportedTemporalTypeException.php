<?php

namespace Celest\Temporal;


use Celest\DateTimeException;

class UnsupportedTemporalTypeException extends DateTimeException
{
    /**
     * UnsupportedTemporalTypeException constructor.
     * @param string $message
     * @param \Exception $e
     */
    public function __construct($message, \Exception $e = null)
    {
        parent::__construct($message, $e);
    }

}