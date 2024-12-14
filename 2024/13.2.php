<?php

declare(strict_types=1);

include(__DIR__ . "/utils.php");

/**
 * https://adventofcode.com/2024/day/13
 *
 * strategy: use math (why not from the start...)
 *
 * p0 = a0 * a + b0 * a
 * p1 = a1 * a + b1 * a
 * a = (b0 * p1 - b1 * p0) / (b0 * a1 - b1 * a0)
 * a ∈ Z, a > 0
 * b = (a0 * p1 - a1 * p0) / (a0 * b1 - a1 * b0)
 * b ∈ Z, b > 0
 * t = 3a + b
 */

if (Console::isTest()) {
    $puzzle = <<<PUZZLE
        Button A: X+94, Y+34
        Button B: X+22, Y+67
        Prize: X=8400, Y=5400
        
        Button A: X+26, Y+66
        Button B: X+67, Y+21
        Prize: X=12748, Y=12176
        
        Button A: X+17, Y+86
        Button B: X+84, Y+37
        Prize: X=7870, Y=6450
        
        Button A: X+69, Y+23
        Button B: X+27, Y+71
        Prize: X=18641, Y=10279
        PUZZLE;
} else {
    $puzzle = file_get_contents(__DIR__ . '/13.txt');
}

$machines = [];
preg_match_all(
    '/Button A: X\+(\d+), Y\+(\d+).+?Button B: X\+(\d+), Y\+(\d+).+?Prize: X=(\d+), Y=(\d+)/s',
    $puzzle,
    $matches,
    PREG_SET_ORDER,
);
foreach ($matches as $match) {
    $machines[] = [
        'A' => [(int)$match[1], (int)$match[2]],
        'B' => [(int)$match[3], (int)$match[4]],
        'P' => [(int)$match[5] + 10000000000000, intval($match[6]) + 10000000000000],
    ];
}

$calcA = function (array $machine): int {
    // a = (b0 * p1 - b1 * p0) / (b0 * a1 - b1 * a0)
    $a = ($machine['B']['0'] * $machine['P']['1'] - $machine['B']['1'] * $machine['P']['0']) / ($machine['B']['0'] * $machine['A']['1'] - $machine['B']['1'] * $machine['A']['0']);
    if ((float)$a === floor($a) && $a > 0) {
        return (int)$a;
    }

    return 0;
};
$calcB = function (array $machine): int {
    // b = (a0 * p1 - a1 * p0) / (a0 * b1 - a1 * b0)
    $b = ($machine['A']['0'] * $machine['P']['1'] - $machine['A']['1'] * $machine['P']['0']) / ($machine['A']['0'] * $machine['B']['1'] - $machine['A']['1'] * $machine['B']['0']);
    if ((float)$b === floor($b) && $b > 0) {
        return (int)$b;
    }

    return 0;
};
$calcT = function (int $a, $b): int {
    // t = 3a + b
    return 3 * $a + $b;
};

$wins = [];
foreach ($machines as $machine) {
    $tokens = $calcT($calcA($machine), $calcB($machine));
    if ($tokens > 0) {
        $wins[] = $tokens;
    }
}

Console::l('found %s wins, with a total %s tokens used', count($wins), array_sum($wins));
