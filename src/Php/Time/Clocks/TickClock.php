<?php

namespace Php\Time\Clocks;

use Php\Time\Clock;
use Php\Time\Duration;
use Php\Time\Helper\Math;
use Php\Time\Instant;
use Php\Time\ZoneId;


/**
 * Implementation of a clock that adds an offset to an underlying clock.
 */
final class TickClock extends Clock
{
    /** @var Clock */
    private $baseClock;
    /** @var  int */
    private $tickNanos;

    /**
     * @param Clock $baseClock
     * @param int $tickNanos
     */
    public function __construct(Clock $baseClock, $tickNanos)
    {
        $this->baseClock = $baseClock;
        $this->tickNanos = $tickNanos;
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
        return new TickClock($this->baseClock->withZone($zone), $this->tickNanos);
    }

    public function millis()
    {
        $millis = $this->baseClock->millis();
        return $millis - Math::floorMod($millis, $this->tickNanos / 1000000);
    }

    public function instant()
    {
        if (($this->tickNanos % 1000000) == 0) {
            $millis = $this->baseClock->millis();
            return Instant::ofEpochMilli($millis - Math::floorMod($millis, $this->tickNanos / 1000000));
    }
        $instant = $this->baseClock->instant();
        $nanos = $instant->getNano();
        $adjust = Math::floorMod($nanos, $this->tickNanos);
        return $instant->minusNanos($adjust);
    }

    public function equals($obj)
    {
        if ($obj instanceof TickClock) {
            return $this->baseClock->equals($obj->baseClock) && $this->tickNanos == $obj->tickNanos;
        } else {
            return false;
        }
    }

    public function __toString()
    {
        return "TickClock[" . $this->baseClock . "," . Duration::ofNanos($this->tickNanos) . "]";
    }
}