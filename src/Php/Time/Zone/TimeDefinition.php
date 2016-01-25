<?php

namespace Php\Time\Zone;

//-----------------------------------------------------------------------
use Php\Time\LocalDateTime;
use Php\Time\ZoneOffset;

/**
 * A definition of the way a local time can be converted to the actual
 * transition date-time.
 * <p>
 * Time zone rules are expressed in one of three ways:
 * <ul>
 * <li>Relative to UTC</li>
 * <li>Relative to the standard offset in force</li>
 * <li>Relative to the wall offset (what you would see on a clock on the wall)</li>
 * </ul>
 */
class TimeDefinition
{
    public static function init()
    {
        self::$UTC = new TimeDefinition(0);
        self::$WALL = new TimeDefinition(1);
        self::$STANDARD = new TimeDefinition(2);
    }

    /** The local date-time is expressed in terms of the UTC offset.
     * @return TimeDefinition
     */
    public static function UTC()
    {
        return self::$UTC;
    }

    /** @var TimeDefinition */
    private static $UTC;

    /** The local date-time is expressed in terms of the wall offset. */
    public static function WALL()
    {
        return self::$WALL;
    }

    /** @var TimeDefinition */
    private static $WALL;

    /** The local date-time is expressed in terms of the standard offset. */
    public static function STANDARD()
    {
        return self::$STANDARD;
    }

    /** @var TimeDefinition */
    private static $STANDARD;

    /** @var int */
    private $val;

    /**
     * TimeDefinition constructor.
     * @param int $val
     */
    private function __construct($val)
    {
        $this->val = $val;
    }


    /**
     * Converts the specified local date-time to the local date-time actually
     * seen on a wall clock.
     * <p>
     * This method converts using the type of this enum.
     * The output is defined relative to the 'before' offset of the transition.
     * <p>
     * The UTC type uses the UTC offset.
     * The STANDARD type uses the standard offset.
     * The WALL type returns the input date-time.
     * The result is intended for use with the wall-offset.
     *
     * @param LocalDateTime $dateTime the local date-time, not null
     * @param ZoneOffset $standardOffset the standard offset, not null
     * @param ZoneOffset $wallOffset the wall offset, not null
     * @return LocalDateTime the date-time relative to the wall/before offset, not null
     */
    public function createDateTime(LocalDateTime $dateTime, ZoneOffset $standardOffset, ZoneOffset $wallOffset)
    {
        switch ($this->val) {
            case 0: {
                $difference = $wallOffset->getTotalSeconds() - ZoneOffset::UTC()->getTotalSeconds();
                return $dateTime->plusSeconds($difference);
            }

            case 2: {
                $difference = $wallOffset->getTotalSeconds() - $standardOffset->getTotalSeconds();
                return $dateTime->plusSeconds($difference);
            }
            default:  // WALL
                return $dateTime;
        }
    }

    public function __toString()
    {
        switch ($this->val) {
            case 0:
                return "UTC";
            case 1:
                return "WALL";
            case 2:
                return "STANDARD";
        }
    }
}

TimeDefinition::init();