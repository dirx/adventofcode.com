<?php

declare(strict_types=1);

include(__DIR__ . "/utils.php");

/**
 * https://adventofcode.com/2024/day/3
 *
 * strategy: collect valid values with regex, mark do / don´t and omit values if don´t, else calc, sum
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
$do = true;
$validInstructions = [
    '@(mul\((\d+),(\d+)\)|do\(\)|don\'t\(\))@' => function ($matches) use (&$results, &$do) {
        if ($matches[0] == 'do()') {
            $do = true;
            Console::v('do');

            return;
        }
        if ($matches[0] == 'don\'t()') {
            $do = false;
            Console::v('don\'t');

            return;
        }

        if ($do) {
            Console::v('mul %s with %s', (int)$matches[2], (int)$matches[3]);
            $results[] = (int)$matches[2] * (int)$matches[3];
        } else {
            Console::vv('ignore mul %s with %s', (int)$matches[2], (int)$matches[3]);
        }
    },
];

foreach ($validInstructions as $pattern => $callback) {
    preg_replace_callback($pattern, $callback, $instruction);
}

Console::l('%s results found. sum is %s', count($results), array_sum($results));
