- generics
- function overloading
- rounding errors
- move default implementations
- ENUMS
- switches on ENUMS
- get/getLong
- query call
- equals calls
- closures
- clean up data in constructors?
- Use http://php.net/manual/de/function.intdiv.php for PHP 7
- Fix integer division
- strong/weak equality
- PHP5.6 const array
- PHP5.6 use const
- php7 anonymous classes for queries!
- gmp operator overloading
- Serializable

- map<TemporalField, long> use TemporalField->__toString as index.

Disable Autocompletion in IDE for easier adding of $ ;)

Useful regex:
Add $ to phpdoc
@param ([^$])
@param \\$$1

Overloaded functions:
\Php\Time\Temporal\Temporal::with(TemporalAdjuster $adjuster)
adjust
\Php\Time\Temporal\Temporal::with(TemporalField $field, $newValue)
with

\Php\Time\Temporal\TemporalAccessor::isSupported(TemporalField $field) : bool
isSupported
\Php\Time\Temporal\Temporal::isSupported(TemporalUnit $unit) : bool
isUnitSupported

\Php\Time\Temporal\Temporal::plus(TemporalAmount $amount);
plusAmount
\Php\Time\Temporal\Temporal::plus($amountToAdd, TemporalUnit $unit);
plus



\Php\Time\Temporal\ValueRange::of
$min, $max
$min, $maxSmallest, $maxLargest
($minSmallest, $minLargest, $maxSmallest, $maxLargest)
rearrange and merge?

\Php\Time\Chrono\AbstractChronology::registerChrono