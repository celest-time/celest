<?php declare(strict_types=1);

namespace Celest\Clocks;

use Celest\Clock;
use Celest\Instant;
use Celest\ZoneId;


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

    public function getZone() : ZoneId
    {
        return $this->zone;
    }

    public function withZone(ZoneId $zone) : Clock
    {
        if ($zone->equals($this->zone)) {  // intentional NPE
            return $this;
        }
        return new FixedClock($this->instant, $zone);
    }

    public function millis() : int
    {
        return $this->instant->toEpochMilli();
    }

    public function instant() : Instant
    {
        return $this->instant;
    }

    public function equals($obj) : bool
    {
        if ($obj instanceof FixedClock) {
            return $this->instant->equals($obj->instant) && $this->zone->equals($obj->zone);
        }
        return false;
    }

    public function __toString() : string
    {
        return "FixedClock[" . $this->instant . "," . $this->zone . "]";
    }
}