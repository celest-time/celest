<?php
/**
 * Created by IntelliJ IDEA.
 * User: hanikel
 * Date: 14.08.15
 * Time: 16:30
 */

namespace Php\Time\Chrono;


use Php\Time\Clock;
use Php\Time\DateTimeException;
use Php\Time\Instant;
use Php\Time\LocalDate;
use Php\Time\LocalTime;
use Php\Time\Temporal\TemporalAccessor;
use Php\Time\Temporal\TemporalQueries;
use Php\Time\ZoneId;

final class ChronologyDefaults
{
    private function __construct()
    {
    }

    public static function from(TemporalAccessor $temporal)
    {
        $obj = $temporal->query(TemporalQueries::chronology());
        return ($obj != null ? $obj : IsoChronology::INSTANCE());
    }

    public static function ofLocale(Locale $locale)
    {
        return AbstractChronology::ofLocale($locale);
    }

    public static function of($id)
    {
        return AbstractChronology::of($id);
    }

    public static function getAvailableChronologies()
    {
        return AbstractChronology::getAvailableChronologies();
    }

    public static function date(Chronology $_this, Era $era, $yearOfEra, $month, $dayOfMonth)
    {
        return $_this->date($_this->prolepticYear($era, $yearOfEra), $month, $dayOfMonth);
    }

    public static function dateYearDay(Chronology $_this, Era $era, $yearOfEra, $dayOfYear)
    {
        return $_this->dateYearDay($_this->prolepticYear($era, $yearOfEra), $dayOfYear);
    }

    public static function dateNow(Chronology $_this)
    {
        return $_this->dateNow(Clock::systemDefaultZone());
    }

    public static function dateNow(Chronology $_this, ZoneId $zone)
    {
        return $_this->dateNow(Clock::system($zone));
    }

    public static function dateNow(Chronology $_this, Clock $clock)
    {
        return $_this->date(LocalDate::now($clock));
    }

    public static function localDateTime(Chronology $_this, TemporalAccessor $temporal)
    {
        try {
            return $_this->date($temporal)->atTime(LocalTime::from($temporal));
        } catch
        (DateTimeException $ex) {
            throw new DateTimeException("Unable to obtain ChronoLocalDateTime from TemporalAccessor: " . get_class($temporal), $ex);
        }
    }

    public static function zonedDateTime(Chronology $_this, TemporalAccessor $temporal)
    {
        try {
            $zone = ZoneId::from($temporal);
            try {
                $instant = Instant::from($temporal);
                return $_this->zonedDateTime($instant, $zone);

            } catch
            (DateTimeException $ex1) {
                $cldt = ChronoLocalDateTimeImpl::ensureValid($_this, $_this->localDateTime($temporal));
                return ChronoZonedDateTimeImpl::ofBest($cldt, $zone, null);
            }
        } catch (DateTimeException $ex) {
            throw new DateTimeException("Unable to obtain ChronoZonedDateTime from TemporalAccessor: " . get_class($temporal), $ex);
        }
    }

    public static function zonedDateTime(Chronology $_this, Instant $instant, ZoneId $zone)
    {
        return ChronoZonedDateTimeImpl::ofInstant($_this, $instant, $zone);
    }

    public static function getDisplayName(Chronology $_this, TextStyle $style, Locale $locale)
    {
        // TODO implement
        /*   $temporal = new TemporalAccessor()
   {
       @Override
   public boolean isSupported(TemporalField field)
   {
       return false;
   }

   @Override
               public long getLong(TemporalField field) {
       throw new UnsupportedTemporalTypeException("Unsupported field: " + field);
   }
               @SuppressWarnings("unchecked")
               @Override
               public <R > R query(TemporalQuery < R> query) {
       if (query == TemporalQueries->chronology()) {
           return (R) Chronology->this;
                   }
                           return TemporalAccessor->super->query(query);
                       }
           };
           return new DateTimeFormatterBuilder()->appendChronologyText($style)->toFormatter($locale)->format($temporal);*/
        return "";
    }

    public static function period(Chronology $_this, $years, $months, $days)
    {
        return new ChronoPeriodImpl($_this, $years, $months, $days);
    }

}