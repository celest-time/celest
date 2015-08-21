<?php

namespace Php\Time\Format;


class DateTimeFormatterBuilder
{

    /**
     * @param $YEAR
     * @param $int
     * @param $int1
     * @param $EXCEEDS_PAD
     * @return $this
     */
    public function appendValue($YEAR, $int, $int1, $EXCEEDS_PAD)
    {
        return $this;
    }

    /**
     * @return DateTimeFormatter
     */
    public function toFormatter()
    {
        return new DateTimeFormatter();
    }
}