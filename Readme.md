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

## Localication
Besides the ISO 8061 calendar, the Minguo and Thai Buddhist calendars are supported. Localication of Month, weekday etc.,
localized parsing/formatting patterns and names of time zones are supported and provided via the intl extension.

## Known limitations
- No interopablity with `\DateTime` yet
- No builtin ranges
- Multibyte string parsing is not fully tested
- Parsing of timezone names is broken on HHVM. See [#6852](https://github.com/facebook/hhvm/issues/6852)
- Celest is not suited if you care about correct historical dates before ~1970 or subsecond synchronization with external
timesystems like TAI, UT or scientific uses of UTC

## Roadmap
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
