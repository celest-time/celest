<?php
/**
 * Created by IntelliJ IDEA.
 * User: hanikel
 * Date: 24.07.15
 * Time: 13:39
 */

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

    public function getZone()
    {
        return $this->zone;
    }

    public function withZone(ZoneId $zone)
    {
        if ($zone->equals($this->zone)) {  // intentional NPE
            return $this;
        }
        return new SystemClock($zone);
    }

    public function millis()
    {
        $tofd = \gettimeofday();
        return $tofd['sec'] + Math::floorDiv($tofd['usec'], 1000);
    }

    public function instant()
    {
        return Instant::ofEpochMilli($this->millis());
    }

    public function equals($obj)
    {
        if ($obj instanceof SystemClock) {
            return $this->zone->equals($obj->zone);
    }
        return false;
    }

    public function __toString()
    {
        return "SystemClock[" . $this->zone . "]";
    }
}
