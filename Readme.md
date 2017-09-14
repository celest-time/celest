Celest
======

[![Build Status](https://travis-ci.org/celest-time/celest.svg?branch=master)](https://travis-ci.org/celest-time/celest) [![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/celest-time/celest/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/celest-time/celest/?branch=master) [![Code Coverage](https://scrutinizer-ci.com/g/celest-time/celest/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/celest-time/celest/?branch=master)

Celest provides a alternative date and time API for PHP. It allows you to correctly manage date and time and to do operations on them. It is based on the [JSR-310](http://www.threeten.org/) project for Java 8.

## Installation
The recommended way to install Celest is through [Composer](https://getcomposer.org/):

```
composer require celest/celest
```

## Requirements
PHP >= 5.6 with GMP and intl extension or a current release of HHVM

## Timezone Data
Celest uses IANA's [time zone database](https://www.iana.org/time-zones). Changes are distributed via the
[Celest tzdata](https://github.com/celest-time/tzdata) repo.

## Localization
Besides the ISO 8061 calendar, the Minguo and Thai Buddhist calendars are supported. Localization of Month, weekday etc.,
localized parsing/formatting patterns and names of time zones are supported and provided via the intl extension.

## Testing
You need to install phpunit manually and a version of doctrine/instantiator that's compatible with php5:
```
composer require --dev phpunit/phpunit:^5.7 rianium/paratest:~0.15.0 doctrine/instantiator:~1.0.5
```
Then run `make test` for the full testsuite or `make test-short` for a testsuite that runs in under a minute.


## Interoperability with native \DateTime
As PHP's native `\DateTime` mixes Instants with local Date/Time there's two different conversions:
- Instant.fromDateTime creates an Instant from a `\DateTime` object 
- Instant.toDateTime converts an Instant to a `\DateTime` object in the UTC timezone
- ZonedDateTime.fromDateTime creates a ZonedDateTime object from a `\DateTime` object
- ZonedDateTime.toDateTime converts ZonedDateTime object to a `\DateTime` object
- TODO TimeZones

## Known limitations
- No builtin ranges
- Multibyte string parsing is not fully tested
- Parsing of timezone names is broken on HHVM. See [#6852](https://github.com/facebook/hhvm/issues/6852)
- Offset based timezones can't be converted to native `\DatetimeZones` on HVVM. See [#6783](https://github.com/facebook/hhvm/issues/6783)
- Celest is not suited if you care about correct historical dates before ~1970 or subsecond synchronization with external
timesystems like TAI, UT or scientific uses of UTC

## Roadmap
- Performance optimizations
- Optimized serialization
- Doctrine mappings
- Check passed types to behave like php 7 with type annotations
- Documentation cleanup
- Support for the Japanese and Hijrah calendars
- 2.0 with full use of PHP 7 type annotations for primitive types and return values.

## Similar projects:
- https://github.com/briannesbitt/Carbon
- https://github.com/cakephp/chronos
- https://packagist.org/packages/icecave/chrono

### In other languages:
- http://www.joda.org/joda-time/
- http://nodatime.org/
- https://github.com/MenoData/Time4J

## Acknowledgements
Thanks to Stephen Colebourne and Michael Nascimento Santos the original authors of [JSR-310](http://www.threeten.org/). Also to [Flixbus](https://www.flixbus.de/) who partially sponsored the work on Celest.