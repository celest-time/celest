<?php declare(strict_types=1);

namespace Celest;


class DateTimeException extends \Exception
{
    /**
     * DateTimeException constructor.
     * @param string $message
     * @param \Exception $e
     */
    public function __construct(string $message, \Exception $e = null)
    {
        parent::__construct($message, 0, $e);
    }
}