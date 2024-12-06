<?php

declare(strict_types=1);

/**
 * https://adventofcode.com/2024/day/2
 *
 * strategy: check all levels and all variations with one level less, if safe count and next report, else unsafe and next report.
 */

$puzzle = file_get_contents(__DIR__ . '/2.txt');
//$puzzle = <<<PUZZLE
//    7 6 4 2 1
//    1 2 7 8 9
//    9 7 6 2 1
//    1 3 2 4 5
//    8 6 4 4 1
//    1 3 6 7 9
//    PUZZLE;
$reports = [];
$right = [];
foreach (explode("\n", $puzzle) as $i => $line) {
    $reports[] = array_map('intval', explode(" ", trim($line)));
}

$maxRemovedLevels = 1;

$rulesMissed = function (int $diff, int|null $lastDiff): bool {
    if ($lastDiff != null && ($diff <=> 0) != ($lastDiff <=> 0)) {
        return true;
    }
    if ((abs($diff) < 1 || abs($diff) > 3)) {
        return true;
    }

    return false;
};

$checkLevels = function (array $levels, Closure $rulesMissed): bool {
    echo sprintf(
        '- check levels: %s' . PHP_EOL,
        implode(',', $levels),
    );
    $lastDiff = null;
    for ($i = 0; $i < count($levels) - 1; $i++) {
        $diff = $levels[$i] - $levels[$i + 1];
        echo sprintf(
            '  - compare %s to %s: %s' . PHP_EOL,
            $levels[$i],
            $levels[$i + 1],
            $diff,
        );
        if ($rulesMissed($diff, $lastDiff)) {
            echo sprintf(
                '  - unsafe %s to %s' . PHP_EOL,
                $levels[$i],
                $levels[$i + 1],
            );

            return false;
        }

        $lastDiff = $diff;
    }

    return true;
};

$safe = 0;
foreach ($reports as $r => $levels) {
    echo sprintf(
        'report %s: %s' . PHP_EOL,
        $r,
        implode(',', $levels),
    );

    if ($checkLevels($levels, $rulesMissed)) {
        $safe++;
        echo sprintf('- safe' . PHP_EOL);
        continue;
    }

    for ($i = 0; $i < count($levels); $i++) {
        $levelsRemoved = $levels;
        array_splice($levelsRemoved, $i, 1);
        if ($checkLevels($levelsRemoved, $rulesMissed)) {
            $safe++;
            echo sprintf('- safe' . PHP_EOL);
            continue 2;
        }
    }

    echo sprintf('- unsafe' . PHP_EOL);
}

echo sprintf(
    'found %s / %s safe reports' . PHP_EOL,
    $safe,
    count($reports),
);
