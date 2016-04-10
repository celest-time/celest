<?php

namespace Celest;

use Celest\Temporal\ChronoUnit;
use Celest\Zone\TZDBZoneRulesProvider;
use Celest\Zone\ZoneRulesProvider;

class TZDBZoneRulesProviderPerf
{
    public static function main($argv)
    {
        require __DIR__ . '/../../vendor/autoload.php';
        array_splice($argv, 0, 1);
        (new TZDBZoneRulesProviderPerf())->run($argv);
    }

    private function run($argv)
    {
        $ids = ZoneRulesProvider::getAvailableZoneIds();

        $start = Instant::now();
        foreach ($ids as $id) {
            TZDBZoneRulesProvider::getRules($id, false);
        }
        $end = Instant::now();

        echo 'First run: ', ChronoUnit::MILLIS()->between($start, $end), "ms, count: ", count($ids), "\n";
    }

}

global $argv;
TZDBZoneRulesProviderPerf::main($argv);