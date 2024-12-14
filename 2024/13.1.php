<?php

declare(strict_types=1);

include(__DIR__ . "/utils.php");

/**
 * https://adventofcode.com/2024/day/13
 *
 * strategy: for each maschine, calc max a / b presses, collect min a/b presses combinations, calc tokens used, sum tokens per win
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
        'P' => [(int)$match[5], (int)$match[6]],
    ];
}
$wins = [];
foreach ($machines as $machine) {
    $minPresses = PHP_INT_MAX;
    $win = null;
    $maxPresses = [
        'A' => (int)ceil(min($machine['P'][0] / $machine['A'][0], $machine['P'][1] / $machine['A'][1])),
        'B' => (int)ceil(min($machine['P'][0] / $machine['B'][0], $machine['P'][1] / $machine['B'][1])),
    ];

    for ($a = $maxPresses['A']; $a >= 0; $a--) {
        for ($b = 0; $b < $maxPresses['B']; $b++) {
            $claw = [
                $machine['A'][0] * $a + $machine['B'][0] * $b,
                $machine['A'][1] * $a + $machine['B'][1] * $b,
            ];

            if ($claw[0] === $machine['P'][0] && $claw[1] === $machine['P'][1]) {
                if ($minPresses > $a + $b) {
                    $win = $a * 3 + $b;
                    $minPresses = min($minPresses, $a + $b);
                    continue 2;
                }
            }
            if ($claw[0] > $machine['P'][0] || $claw[1] > $machine['P'][1]) {
                continue 2;
            }
        }
    }

    if ($win !== null) {
        $wins[] = $win;
    }
}

Console::l('found %s wins, with a total %s tokens used', count($wins), array_sum($wins));
