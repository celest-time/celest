<?php
/*
 * Copyright (c) 2012, 2013, Oracle and/or its affiliates. All rights reserved.
 * DO NOT ALTER OR REMOVE COPYRIGHT NOTICES OR THIS FILE HEADER.
 *
 * This code is free software; you can redistribute it and/or modify it
 * under the terms of the GNU General Public License version 2 only, as
 * published by the Free Software Foundation.  Oracle designates this
 * particular file as subject to the "Classpath" exception as provided
 * by Oracle in the LICENSE file that accompanied this code.
 *
 * This code is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or
 * FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License
 * version 2 for more details (a copy is included in the LICENSE file that
 * accompanied this code).
 *
 * You should have received a copy of the GNU General Public License version
 * 2 along with this work; if not, write to the Free Software Foundation,
 * Inc., 51 Franklin St, Fifth Floor, Boston, MA 02110-1301 USA.
 *
 * Please contact Oracle, 500 Oracle Parkway, Redwood Shores, CA 94065 USA
 * or visit www.oracle.com if you need additional information or have any
 * questions.
 */

/*
 * Copyright (c) 2009-2012, Stephen Colebourne & Michael Nascimento Santos
 *
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *
 *  * Redistributions of source code must retain the above copyright notice,
 *    this list of conditions and the following disclaimer.
 *
 *  * Redistributions in binary form must reproduce the above copyright notice,
 *    this list of conditions and the following disclaimer in the documentation
 *    and/or other materials provided with the distribution.
 *
 *  * Neither the name of JSR-310 nor the names of its contributors
 *    may be used to endorse or promote products derived from this software
 *    without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR
 * CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL,
 * EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO,
 * PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR
 * PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF
 * LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING
 * NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
 * SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 */
namespace Celest\Zone;

use Celest\DayOfWeek;
use Celest\IllegalArgumentException;
use Celest\LocalTime;
use Celest\Month;
use Celest\Year;
use Celest\Zone\Compiler\TZDBMonthDayTime;
use Celest\Zone\Compiler\TZDBRule;
use Celest\Zone\Compiler\TZDBZone;
use Celest\Zone\Compiler\ZoneRulesBuilder;
use Celest\ZoneOffset;

/**
 * A compiler that reads a set of TZDB time-zone files and builds a single
 * combined TZDB data file.
 *
 * @since 1.8
 */
final class TzdbZoneRulesCompiler
{

    public static function main($argv)
    {
        require __DIR__ . '/../../../../vendor/autoload.php';
        array_splice($argv, 0, 1);
        (new TzdbZoneRulesCompiler())->compile($argv);
    }

    /** Whether to output verbose messages. @var bool */
    private $verbose;

    /** The TZDB links. @var string[] */
    private $links = [];

    /** The built zones. @var ZoneRules[] */
    private $builtZones = [];

    private function compile($args)
    {
        if (count($args) < 2) {
            $this->outputHelp();
            return;
        }

        $srcDir = null;
        $dstDir = null;
        $version = null;
        // parse args/options
        for ($i = 0; $i < count($args); $i++) {
            $arg = $args[$i];
            if ($arg[0] !== "-") {
                break;
            }
            if ("-srcdir" === $arg) {
                if ($srcDir === null && ++$i < count($args)) {
                    $srcDir = $args[$i];
                    continue;
                }
            } else if ("-dstdir" === $arg) {
                if ($dstDir === null && ++$i < count($args)) {
                    $dstDir = $args[$i];
                    continue;
                }
            } else if ("-verbose" === $arg) {
                if (!$this->verbose) {
                    $this->verbose = true;
                    continue;
                }
            } else if (!"-help" === $arg) {
                echo "Unrecognised option: " . $arg;
            }
            $this->outputHelp();
            return;
        }
        // check source directory
        if ($srcDir === null) {
            echo "Source directory must be specified using -srcdir";
            exit(1);
        }
        if (!is_dir($srcDir)) {
            echo "Source does not exist or is not a directory: " . $srcDir;
            exit(1);
        }
        if ($dstDir === null) {
            echo "Destination directory must be specified using -dstdir";
            exit(1);
        }
        if (!is_dir($dstDir)) {
            echo "Destination does not exist or is not a directory: " . $dstDir;
            exit(1);
        }
        // parse source file names
        if ($i === count($args)) {
            $i = 0;
            $args = [
                "africa", "antarctica", "asia", "australasia", "europe",
                "northamerica", "southamerica", "backward", "etcetera"];
            echo "Source filenames not specified, using default set ( ";
            foreach ($args as $name) {
                echo $name . " ";
            }
            echo ")\n";
        }
        // source files in this directory
        $srcFiles = [];
        for (; $i < count($args); $i++) {
            $file = $srcDir . '/' . $args[$i];
            if (is_file($file)) {
                $srcFiles[] = $file;
            } else {
                echo "Source directory does not contain source file: " . $args[$i];
                exit(1);
            }
        }

        try {
            // get tzdb source version
            $contents = file_get_contents($srcDir . "/Makefile");
            $m = preg_match("/VERSION=\t(?<ver>[0-9]{4}[A-z])/", $contents, $matches);
            if ($m === 1) {
                $version = $matches['ver'];
            } else {
                echo "Source directory does not contain file: Makefile\n";
                exit(1);
            }
            $this->printVerbose("Compiling TZDB version " . $version . "\n");
            // parse source files
            foreach ($srcFiles as $file) {
                $this->printVerbose("Parsing file: " . $file . "\n");
                $this->parseFile($file);
            }
            // build zone rules
            $this->printVerbose("Building rules\n");
            $this->buildZoneRules();
            // output to file
            $this->printVerbose("Outputting tzdb dir: " . $dstDir . "\n");
            $this->output($dstDir, $version, $this->builtZones, $this->links);
        } catch (\Throwable $ex) {
            echo "Failed: " . $ex->__toString();
            exit(1);
        }
        exit(0);
    }

    /**
     * Output usage text for the command line.
     */
    private static function outputHelp()
    {
        echo "Usage: TzdbZoneRulesCompiler <options> <tzdb source filenames>\n";
        echo "where options include:\n";
        echo "   -srcdir <directory>   Where to find tzdb source directory (required)\n";
        echo "   -dstdir <directory>   Where to output generated file (default srcdir/tzdb.dat)\n";
        echo "   -help                 Print this usage message\n";
        echo "   -verbose              Output verbose information during compilation\n";
        echo " The source directory must contain the unpacked tzdb files, such as asia or europe\n";
    }

    /**
     * @param string $dstDir
     * @param string $version
     * @param ZoneRules $builtZones []
     * @param string $links []
     */
    private function output($dstDir, $version,
                            array $builtZones,
                            array $links)
    {
        $baseDir = $dstDir . '/';

        foreach ($builtZones as $name => $zone) {
            if (array_key_exists($name, $links))
                continue;

            $p = explode('/', $name);
            if (count($p) > 1) {
                @mkdir(\dirname($baseDir . 'zones/' . $name), 0777, true);
            }
            $file = fopen($baseDir . 'zones/' . $name . '.php', 'w');
            fwrite($file, "<?php\nreturn '");
            fwrite($file, serialize($zone));
            fwrite($file, "';\n");
            fclose($file);
        }

        $file = fopen($baseDir . 'links.php', 'w');
        fwrite($file, "<?php\nreturn ");
        fwrite($file, var_export($links, true));
        fwrite($file, ";\n");
        fclose($file);

        $file = fopen($baseDir . 'provides.php', 'w');
        fwrite($file, "<?php\nreturn ");
        fwrite($file, var_export(array_keys($builtZones), true));
        fwrite($file, ";\n");
        fclose($file);

        $file = fopen($baseDir . 'version.php', 'w');
        fwrite($file, "<?php\nreturn ");
        fwrite($file, var_export($version, true));
        fwrite($file, ";\n");
        fclose($file);
    }

    private static $YEAR = "/(?i)(?<min>min)|(?<max>max)|(?<only>only)|(?<year>[0-9]+)/";
    private static $MONTH = "/(?i)(jan)|(feb)|(mar)|(apr)|(may)|(jun)|(jul)|(aug)|(sep)|(oct)|(nov)|(dec)/";
    private static $DOW = "/(?i)(mon)|(tue)|(wed)|(thu)|(fri)|(sat)|(sun)/";
    private static $TIME = "/(?<neg>-)?+(?<hour>[0-9]{1,2})(:(?<minute>[0-5][0-9]))?+(:(?<second>[0-5][0-9]))?+/";

    /** The TZDB rules @var TZDBRule[]. */
    private $rules = [];

    /** The TZDB zones. @var TZDBZone[][] */
    private $zones = [];

    /**
     * private contructor
     */
    private function __construct()
    {
    }

    /**
     * Parses a source file.
     *
     * @param string $file the file being read, not null
     * @throws \Exception if an error occurs
     */
    private function parseFile($file)
    {
        $lineNumber = 1;
        $line = null;
        try {
            $lines = \file($file);
            $openZone = null;
            $openZoneName = '';
            for (;
                $lineNumber < count($lines) - 1;
                $lineNumber++) {
                $line = $lines[$lineNumber - 1];
                $index = strpos($line, '#');  // remove comments (doesn't handle # in quotes)
                if ($index !== false && $index >= 0) {
                    $line = substr($line, 0, $index);
                }

                if (strlen(trim($line)) === 0) {  // ignore blank lines
                    continue;
                }
                $parts = preg_split("/[\t \n]/", $line, null, PREG_SPLIT_NO_EMPTY);
                $s = new \CachingIterator(new \ArrayIterator($parts));
                if ($openZone !== null && ctype_space($line[0]) && $s->hasNext()) {
                    if ($this->parseZoneLine($s, $openZone)) {
                        $this->zones[$openZoneName] = $openZone;
                        $openZone = null;
                    }
                } else {
                    if ($s->hasNext()) {
                        $s->next();
                        $first = $s->current();
                        if ($first === "Zone") {
                            $openZone = [];
                            try {
                                $s->next();
                                $openZoneName = $s->current();
                                if ($this->parseZoneLine($s, $openZone)) {
                                    $this->zones[$openZoneName] = $openZone;
                                    $openZone = null;
                                }
                            } catch (NoSuchElementException $x) {
                                $this->printVerbose("Invalid Zone line in file: " . $file . ", line: " . $line);
                                throw new IllegalArgumentException("Invalid Zone line");
                            }
                        } else {
                            $openZone = null;
                            if ($first === "Rule") {
                                try {
                                    $this->parseRuleLine($s);
                                } catch (NoSuchElementException $x) {
                                    $this->printVerbose("Invalid Rule line in file: " . $file . ", line: " . $line);
                                    throw new IllegalArgumentException("Invalid Rule line");
                                }
                            } else if ($first === "Link") {
                                try {
                                    $s->next();
                                    $realId = $s->current();
                                    $s->next();
                                    $aliasId = $s->current();
                                    $this->links[$aliasId] = $realId;
                                } catch (NoSuchElementException $x) {
                                    $this->printVerbose("Invalid Link line in file: " . $file . ", line: " . $line);
                                    throw new IllegalArgumentException("Invalid Link line");
                                }

                            } else {
                                throw new IllegalArgumentException("Unknown line");
                            }
                        }
                    }
                }
            }
        } catch (\Exception $ex) {
            throw new \Exception("Failed while parsing file '" . $file . "' on line " . $lineNumber . " '" . $line . "'", 0, $ex);
        }
    }

    private function parseRuleLine(\CachingIterator $s)
    {
        $rule = new TZDBRule();
        $s->next();
        $name = $s->current();
        if (array_key_exists($name, $this->rules) === false) {
            $rules[$name] = [];
        }

        $rule->startYear = $this->parseYear($s, 0);
        $rule->endYear = $this->parseYear($s, $rule->startYear);
        if ($rule->startYear > $rule->endYear) {
            throw new IllegalArgumentException("Year order invalid: " . $rule->startYear . " > " . $rule->endYear);
        }
        $s->next();
        $this->parseOptional($s->current());  // type is unused
        $this->parseMonthDayTime($s, $rule);
        $s->next();
        $rule->savingsAmount = $this->parsePeriod($s->current());
        $s->next();
        $rule->text = $this->parseOptional($s->current());
        $this->rules[$name][] = $rule;
    }

    /**
     * Parses a Zone line.
     *
     * @param $s \CachingIterator the line scanner, not null
     * @param $zoneList
     * @return true if the zone is complete
     */
    private function parseZoneLine(\CachingIterator $s, &$zoneList)
    {
        $zone = new TZDBZone();
        $zoneList[] = $zone;
        $s->next();
        $zone->standardOffset = $this->parseOffset($s->current());
        $s->next();
        $savingsRule = $this->parseOptional($s->current());
        if ($savingsRule === null) {
            $zone->fixedSavingsSecs = 0;
            $zone->savingsRule = null;
        } else {
            try {
                $zone->fixedSavingsSecs = $this->parsePeriod($savingsRule);
                $zone->savingsRule = null;
            } catch (\Exception $ex) {
                $zone->fixedSavingsSecs = null;
                $zone->savingsRule = $savingsRule;
            }
        }
        $s->next();
        $zone->text = $s->current();
        if ($s->hasNext()) {
            $s->next();
            $zone->year = intval($s->current());
            if ($s->hasNext()) {
                $this->parseMonthDayTime($s, $zone);
            }
            return false;
        } else {
            return true;
        }
    }

    /**
     * Parses a Rule line.
     *
     * @param $s \CachingIterator the line scanner, not null
     * @param TZDBMonthDayTime $mdt the object to parse into, not null
     */
    private function parseMonthDayTime(\CachingIterator $s, TZDBMonthDayTime $mdt)
    {
        $mdt->month = $this->parseMonth($s);
        if ($s->hasNext()) {
            $s->next();
            $dayRule = $s->current();
            if (strpos($dayRule, "last") === 0) {
                $mdt->dayOfMonth = -1;
                $mdt->dayOfWeek = $this->parseDayOfWeek(substr($dayRule, 4));
                $mdt->adjustForwards = false;
            } else {
                $index = strpos($dayRule, ">=");
                if ($index > 0) {
                    $mdt->dayOfWeek = $this->parseDayOfWeek(substr($dayRule, 0, $index));
                    $dayRule = substr($dayRule, $index + 2);
                } else {
                    $index = strpos($dayRule, "<=");
                    if ($index > 0) {
                        $mdt->dayOfWeek = $this->parseDayOfWeek(substr($dayRule, 0, $index));
                        $mdt->adjustForwards = false;
                        $dayRule = substr($dayRule, $index + 2);
                    }
                }
                $mdt->dayOfMonth = \intval($dayRule);
            }
            if ($s->hasNext()) {
                $s->next();
                $timeStr = $s->current();
                $secsOfDay = $this->parseSecs($timeStr);
                if ($secsOfDay === 86400) {
                    $mdt->endOfDay = true;
                    $secsOfDay = 0;
                }
                $mdt->time = LocalTime::ofSecondOfDay($secsOfDay);
                $mdt->timeDefinition = $this->parseTimeDefinition($timeStr[strlen($timeStr) - 1]);
            }
        }
    }

    private function parseYear(\CachingIterator $s, $defaultYear)
    {
        $s->next();
        $year = $s->current();
        if ($m = preg_match(self::$YEAR, $year, $mr)) {
            if ($mr['min'] !== '') {
                return 1900;  // systemv has min
            } else
                if ($mr['max'] !== '') {
                    return YEAR::MAX_VALUE;
                } else if ($mr['only'] !== '') {
                    return $defaultYear;
                }
            return intval($mr['year']);
            /*
            if (mr.group("min") != null) {
                //return YEAR_MIN_VALUE;
                return 1900;  // systemv has min
            } else if (mr.group("max") != null) {
                return YEAR_MAX_VALUE;
            } else if (mr.group("only") != null) {
                return defaultYear;
            }
            return Integer.parseInt(mr.group("year"));
            */
        }
        throw new IllegalArgumentException("Unknown year: " . $year);
    }

    private function parseMonth(\CachingIterator $s)
    {
        $s->next();
        $month = $s->current();
        if ($m = preg_match(self::$MONTH, $month, $mr)) {
            for ($moy = 1; $moy < 13 && $moy < count($mr);
                 $moy++) {
                if ($mr[$moy] !== '') {
                    return Month::of($moy);
                }
            }
        }
        throw new IllegalArgumentException("Unknown month: " . $month);
    }

    /**
     * @param string $str
     * @return DayOfWeek
     * @throws IllegalArgumentException
     */
    private function parseDayOfWeek($str)
    {
        if (preg_match(self::$DOW, $str, $mr)) {
            for ($dow = 1; $dow < 8 && $dow < count($mr);
                 $dow++) {
                if ($mr[$dow] !== '') {
                    return DayOfWeek::of($dow);
                }
            }
        }
        throw new IllegalArgumentException("Unknown day-of-week: " . $str);
    }

    private function parseOptional($str)
    {
        return $str === "-" ? null : $str;
    }

    private function parseSecs($str)
    {
        if ($str === "-") {
            return 0;
        }
        try {
            if (preg_match(self::$TIME, $str, $mr)) {
                $secs = intval($mr["hour"]) * 60 * 60;
                if (!empty(@$mr["minute"])) {
                    $secs += intval($mr["minute"]) * 60;
                }
                if (!empty(@$mr["second"])) {
                    $secs += intval($mr["second"]);
                }
                if ($mr["neg"] !== '') {
                    $secs = -$secs;
                }
                return $secs;
            }
        } catch (NumberFormatException $x) {
        }
        throw new IllegalArgumentException($str);
    }

    private function parseOffset($str)
    {
        $secs = $this->parseSecs($str);
        return ZoneOffset::ofTotalSeconds($secs);
    }

    private function parsePeriod($str)
    {
        return $this->parseSecs($str);
    }

    private function parseTimeDefinition($c)
    {
        switch ($c) {
            case 's':
            case 'S':
                // standard time
                return TimeDefinition::STANDARD();
            case 'u':
            case 'U':
            case 'g':
            case 'G':
            case 'z':
            case 'Z':
                // UTC
                return TimeDefinition::UTC();
            case 'w':
            case 'W':
            default:
                // wall time
                return TimeDefinition::WALL();
        }
    }

    private function buildZoneRules()
    {
        // build zones
        $dedupMap = [];
        foreach ($this->zones as $zoneId => $tzdbZones) {
            $this->printVerbose("Building zone " . $zoneId . "\n");
            $bld = new ZoneRulesBuilder();
            foreach ($tzdbZones as $tzdbZone) {
                $bld = $tzdbZone->addToBuilder($bld, $this->rules);
            }
            $this->builtZones[$zoneId] = $bld->_toRules($zoneId, $dedupMap);
        }

        $this->links['Zulu'] = 'Etc/UTC';

        // build aliases
        foreach ($this->links as $aliasId => $realId) {
            $this->printVerbose("Linking alias " . $aliasId . " to " . $realId . "\n");
            $realRules = @$this->builtZones[$realId];
            if ($realRules === null) {
                $realId = @$this->links[$realId];  // try again (handle alias liked to alias)
                $this->printVerbose("Relinking alias " . $aliasId . " to " . $realId . "\n");
                $realRules = @$this->builtZones[$realId];
                if ($realRules === null) {
                    throw new IllegalArgumentException("Alias '" . $aliasId . "' links to invalid zone '" . $realId . "'\n");
                }
                $this->links[$aliasId] = $realId;
            }
            $this->builtZones[$aliasId] = $realRules;
        }
        // remove UTC and GMT
        // builtZones.remove("UTC");
        // builtZones.remove("GMT");
        // builtZones.remove("GMT0");
        unset($this->builtZones["GMT+0"]);
        unset($this->builtZones["GMT-0"]);
        unset($this->links["GMT+0"]);
        unset($this->links["GMT-0"]);
        // remove ROC, which is not supported in j.u.tz
        //unset($this->builtZones ["ROC"]);
        //unset($this->links ["ROC"]);
        // remove EST, HST and MST. They are supported via
        // the short-id mapping
        unset($this->builtZones["EST"]);
        unset($this->builtZones["HST"]);
        unset($this->builtZones["MST"]);
    }

    /**
     * Prints a verbose message.
     *
     * @param string $message the message, not null
     */
    private function printVerbose($message)
    {
        if ($this->verbose) {
            echo $message;
        }
    }
}

global $argv;
TzdbZoneRulesCompiler::main($argv);