<?php

namespace Php\Time;

/**
 * Hours per day.
 */
use Php\Time\Temporal\ArithmeticException;
use Php\Time\Temporal\Temporal;
use Php\Time\Temporal\TemporalAdjuster;
use Php\Time\Temporal\TemporalAmount;
use Php\Time\Temporal\TemporalField;
use Php\Time\Temporal\TemporalQuery;
use Php\Time\Temporal\TemporalUnit;
use Php\Time\Temporal\UnsupportedTemporalTypeException;
use Php\Time\Temporal\ValueRange;
const HOURS_PER_DAY = 24;
/**
 * Minutes per hour.
 */
const MINUTES_PER_HOUR = 60;
/**
 * Minutes per day.
 */
const MINUTES_PER_DAY = MINUTES_PER_HOUR * HOURS_PER_DAY;
/**
 * Seconds per minute.
 */
const SECONDS_PER_MINUTE = 60;
/**
 * Seconds per hour.
 */
const SECONDS_PER_HOUR = SECONDS_PER_MINUTE * MINUTES_PER_HOUR;
/**
 * Seconds per day.
 */
const SECONDS_PER_DAY = SECONDS_PER_HOUR * HOURS_PER_DAY;
/**
 * Milliseconds per day.
 */
const MILLIS_PER_DAY = SECONDS_PER_DAY * 1000;
/**
 * Microseconds per day.
 */
const MICROS_PER_DAY = SECONDS_PER_DAY * 1000000;
/**
 * Nanos per second.
 */
const NANOS_PER_SECOND = 1000000000;
/**
 * Nanos per minute.
 */
const NANOS_PER_MINUTE = NANOS_PER_SECOND * SECONDS_PER_MINUTE;
/**
 * Nanos per hour.
 */
const NANOS_PER_HOUR = NANOS_PER_MINUTE * MINUTES_PER_HOUR;
/**
 * Nanos per day.
 */
const NANOS_PER_DAY = NANOS_PER_HOUR * HOURS_PER_DAY;

final class LocalTime implements Temporal, TemporalAdjuster
{

}