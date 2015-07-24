<?php

namespace Php\Time\Clocks;

use Php\Time\Clock;
use Php\Time\Instant;
use Php\Time\ZoneId;


/**
 * Implementation of a clock that always returns the same instant.
 * This is typically used for testing.
 */
final class FixedClock extends Clock
{
    /** @var Instant */
    private $instant;
    /** @var ZoneId */
    private $zone;

    public function __construct(Instant $fixedInstant, ZoneId $zone)
    {
        $this->instant = $fixedInstant;
        $this->zone = $zone;
    }

    public function getZone()
    {
        return $this->zone;
    }

    public function withZone(ZoneId $zone)
    {
        if ($zone->equals($this->zone)) {  // intentional NPE
            return $this;
        }
        return new FixedClock($this->instant, $zone);
    }

    public function millis()
    {
        return $this->instant->toEpochMilli();
    }

    public function instant()
    {
        return $this->instant;
    }

    public function equals($obj)
    {
        if ($obj instanceof FixedClock) {
            return $this->instant->equals($obj->instant) && $this->zone->equals($obj->zone);
        }
        return false;
    }

    public function __toString()
    {
        return "FixedClock[" . $this->instant . "," . $this->zone . "]";
    }
}