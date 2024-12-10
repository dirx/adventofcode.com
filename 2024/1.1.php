<?php

declare(strict_types=1);

include(__DIR__ . "/utils.php");

/**
 * https://adventofcode.com/2024/day/1
 *
 * strategy: sort, distance, sum
 */

if (Console::isTest()) {
    $puzzle = <<<PUZZLE
        3   4
        4   3
        2   5
        1   3
        3   9
        3   3
        PUZZLE;
} else {
    $puzzle = file_get_contents(__DIR__ . '/1.txt');
}

$left = [];
$right = [];
foreach (explode("\n", $puzzle) as $i => $line) {
    preg_match("/(\d+)\s+(\d+)/", trim($line), $matches);
    $left[] = (int)$matches[1];
    $right[] = (int)$matches[2];
}

sort($left, SORT_NUMERIC);
sort($right, SORT_NUMERIC);

$distance = [];
for ($i = 0; $i < count($left); $i++) {
    $distance[] = abs($left[$i] - $right[$i]);
}

Console::l('the distance is %s', array_sum($distance));
