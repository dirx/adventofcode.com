<?php

declare(strict_types=1);

/**
 * https://adventofcode.com/2024/day/2
 *
 * strategy: calc diff, check next report on first rule miss, count on success
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

$rulesMissed = function (int $diff, int|null $lastDiff): bool {
    if ($lastDiff != null && ($diff <=> 0) != ($lastDiff <=> 0)) {
        return true;
    }
    if ((abs($diff) < 1 || abs($diff) > 3)) {
        return true;
    }

    return false;
};

$safe = 0;
foreach ($reports as $r => $levels) {
    echo sprintf(
        'report %s: %s' . PHP_EOL,
        $r,
        implode(',', $levels),
    );
    $lastDiff = null;
    for ($i = 1; $i < count($levels); $i++) {
        $diff = $levels[$i] - $levels[$i - 1];
        if ($rulesMissed($diff, $lastDiff)) {
            echo sprintf(
                '- unsafe %s to %s' . PHP_EOL,
                $levels[$i - 1],
                $levels[$i],
            );
            continue 2;
        }

        $lastDiff = $diff;
    }
    echo sprintf('- safe' . PHP_EOL);
    $safe++;
}

echo sprintf(
    'found %s / %s safe reports' . PHP_EOL,
    $safe,
    count($reports),
);
