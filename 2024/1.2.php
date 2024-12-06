<?php

declare(strict_types=1);

/**
 * https://adventofcode.com/2024/day/1
 *
 * strategy: each left value * with count in right, sum
 */

$puzzle = file_get_contents(__DIR__ . '/1.txt');
//$puzzle = <<<PUZZLE
//    3   4
//    4   3
//    2   5
//    1   3
//    3   9
//    3   3
//    PUZZLE;
$left = [];
$right = [];
foreach (explode("\n", $puzzle) as $i => $line) {
    preg_match("/(\d+)\s+(\d+)/", trim($line), $matches);
    $left[] = (int)$matches[1];
    $right[] = (int)$matches[2];
}

$similarity = [];
for ($i = 0; $i < count($left); $i++) {
    $found = 0;
    for ($j = 0; $j < count($right); $j++) {
        if ($left[$i] === $right[$j]) {
            $found++;
        }
    }
    $similarity[] = $left[$i] * $found;
}

echo sprintf(
    'the similarity is %s.' . PHP_EOL,
    array_sum($similarity),
);
