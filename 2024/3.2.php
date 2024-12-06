<?php

declare(strict_types=1);

/**
 * https://adventofcode.com/2024/day/3
 *
 * strategy: collect valid values with regex, mark do / don´t and omit values if don´t, else calc, sum
 */

$puzzle = file_get_contents(__DIR__ . '/3.txt');
//$puzzle = <<<PUZZLE
//    xmul(2,4)&mul[3,7]!^don't()_mul(5,5)+mul(32,64](mul(11,8)undo()?mul(8,5))
//    PUZZLE;
$instruction = $puzzle;

$results = [];
$do = true;
$validInstructions = [
    '@(mul\((\d+),(\d+)\)|do\(\)|don\'t\(\))@' => function ($matches) use (&$results, &$do) {
        if ($matches[0] == 'do()') {
            $do = true;
            echo sprintf(
                'do' . PHP_EOL,
            );

            return;
        }
        if ($matches[0] == 'don\'t()') {
            $do = false;

            echo sprintf(
                'don´t' . PHP_EOL,
            );

            return;
        }

        if ($do) {
            echo sprintf(
                'mul %s with %s' . PHP_EOL,
                (int)$matches[2],
                (int)$matches[3],
            );
            $results[] = (int)$matches[2] * (int)$matches[3];
        }
    },
];

foreach ($validInstructions as $pattern => $callback) {
    preg_replace_callback($pattern, $callback, $instruction);
}

echo sprintf(
    '%s results found. sum is %s.' . PHP_EOL,
    count($results),
    array_sum($results),
);
