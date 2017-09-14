<?php declare(strict_types=1);

namespace Celest\Clocks;

use Celest\Clock;
use Celest\Helper\Math;
use Celest\Instant;
use Celest\ZoneId;


/**
 * Implementation of a clock that always returns the latest time from
 * {@link System#currentTimeMillis()}.
 */
final class SystemClock extends Clock
{
    /** @var  ZoneId */
    private $zone;

    public function __construct(ZoneId $zone)
    {
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
        return new SystemClock($zone);
    }

    public function millis() : int
    {
        $tofd = \gettimeofday();
        return $tofd['sec'] * 1000 + Math::floorDiv($tofd['usec'], 1000);
    }

    public function instant() : Instant
    {
        return Instant::ofEpochMilli($this->millis());
    }

    public function equals($obj) : bool
    {
        if ($obj instanceof SystemClock) {
            return $this->zone->equals($obj->zone);
        }
        return false;
    }

    public function __toString() : string
    {
        return "SystemClock[" . $this->zone . "]";
    }
}
