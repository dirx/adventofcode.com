<?php

declare(strict_types=1);

include(__DIR__ . "/utils.php");

/**
 * https://adventofcode.com/2024/day/2
 *
 * strategy: check all levels and all variations with one level less, if safe count and next report, else unsafe and next report.
 */

if (Console::isTest()) {
    $puzzle = <<<PUZZLE
        7 6 4 2 1
        1 2 7 8 9
        9 7 6 2 1
        1 3 2 4 5
        8 6 4 4 1
        1 3 6 7 9
        PUZZLE;
} else {
    $puzzle = file_get_contents(__DIR__ . '/2.txt');
}

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
    Console::vv(sprintf('check levels: %s', implode(',', $levels)));
    $lastDiff = null;
    for ($i = 0; $i < count($levels) - 1; $i++) {
        $diff = $levels[$i] - $levels[$i + 1];
        Console::vvv(sprintf('compare %s to %s: %s', $levels[$i], $levels[$i + 1], $diff));
        if ($rulesMissed($diff, $lastDiff)) {
            Console::vv(sprintf('- unsafe %s to %s', $levels[$i], $levels[$i + 1]));

            return false;
        }

        $lastDiff = $diff;
    }

    return true;
};

$safe = 0;
foreach ($reports as $r => $levels) {
    Console::v(sprintf('report %s: %s', $r, implode(',', $levels)));

    if ($checkLevels($levels, $rulesMissed)) {
        $safe++;
        Console::v('- safe');
        continue;
    }

    for ($i = 0; $i < count($levels); $i++) {
        $levelsRemoved = $levels;
        array_splice($levelsRemoved, $i, 1);
        if ($checkLevels($levelsRemoved, $rulesMissed)) {
            $safe++;
            Console::v('- safe');
            continue 2;
        }
    }

    Console::v('- unsafe');
}

Console::l(sprintf('found %s / %s safe reports', $safe, count($reports)));
