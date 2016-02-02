<?php

namespace Celest\Clocks;

use Celest\Clock;
use Celest\Duration;
use Celest\ZoneId;

/**
 * Implementation of a clock that adds an offset to an underlying clock.
 */
final class OffsetClock extends Clock
{
    /** @var Clock */
    private $baseClock;
    /** @var Duration */
    private $offset;

    public function __construct(Clock $baseClock, Duration $offset)
    {
        $this->baseClock = $baseClock;
        $this->offset = $offset;
    }

    public function getZone()
    {
        return $this->baseClock->getZone();
    }

    public function withZone(ZoneId $zone)
    {
        if ($zone->equals($this->baseClock->getZone())) {  // intentional NPE
            return $this;
        }
        return new OffsetClock($this->baseClock->withZone($zone), $this->offset);
    }

    public function millis()
    {
        return $this->baseClock->millis() + $this->offset->toMillis();
    }

    public function instant()
    {
        return $this->baseClock->instant()->plusAmount($this->offset);
    }

    public function equals($obj)
    {
        if ($obj instanceof OffsetClock) {
            return $this->baseClock->equals($obj->baseClock) && $this->offset->equals($obj->offset);
        }
        return false;
    }

    public function __toString()
    {
        return "OffsetClock[" . $this->baseClock . "," . $this->offset . "]";
    }
}
