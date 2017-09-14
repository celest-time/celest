<?php declare(strict_types=1);

namespace Celest\Clocks;

use Celest\Clock;
use Celest\Duration;
use Celest\Instant;
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

    public function getZone() : ZoneId
    {
        return $this->baseClock->getZone();
    }

    public function withZone(ZoneId $zone) : Clock
    {
        if ($zone->equals($this->baseClock->getZone())) {  // intentional NPE
            return $this;
        }
        return new OffsetClock($this->baseClock->withZone($zone), $this->offset);
    }

    public function millis() : int
    {
        return $this->baseClock->millis() + $this->offset->toMillis();
    }

    public function instant() : Instant
    {
        return $this->baseClock->instant()->plusAmount($this->offset);
    }

    public function equals($obj) : bool
    {
        if ($obj instanceof OffsetClock) {
            return $this->baseClock->equals($obj->baseClock) && $this->offset->equals($obj->offset);
        }
        return false;
    }

    public function __toString() : string
    {
        return "OffsetClock[" . $this->baseClock . "," . $this->offset . "]";
    }
}
