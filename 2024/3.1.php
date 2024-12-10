<?php

declare(strict_types=1);

include(__DIR__ . "/utils.php");

/**
 * https://adventofcode.com/2024/day/3
 *
 * strategy: collect valid values with regex, calc, sum
 */

if (Console::isTest()) {
    $puzzle = <<<PUZZLE
        xmul(2,4)%&mul[3,7]!@^do_not_mul(5,5)+mul(32,64]then(mul(11,8)mul(8,5))
        PUZZLE;
} else {
    $puzzle = file_get_contents(__DIR__ . '/3.txt');
}

$instruction = $puzzle;

$results = [];
$validInstructions = [
    '@mul\((\d+),(\d+)\)@' => function ($matches) use (&$results) {
        $results[] = (int)$matches[1] * (int)$matches[2];
    },
];

foreach ($validInstructions as $pattern => $callback) {
    preg_replace_callback($pattern, $callback, $instruction);
}

Console::l('%s results found. sum is %s', count($results), array_sum($results));
