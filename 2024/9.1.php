<?php

declare(strict_types=1);

include(__DIR__ . "/utils.php");

/**
 * https://adventofcode.com/2024/day/9
 *
 * strategy: map to blocks, defrag and calc checksum
 */

if (Console::isTest()) {
    $puzzle = <<<PUZZLE
        2333133121414131402
        PUZZLE;
} else {
    $puzzle = file_get_contents(__DIR__ . '/9.txt');
}

$diskMap = trim($puzzle);
$spaceChar = '.';

$id = 0;
$decoded = '';
$blocks = [];
for ($start = 0; $start < strlen($diskMap); $start++) {
    if ($start % 2 === 0) {
        array_push($blocks, ...array_fill(0, (int)$diskMap[$start], (string)$id));
        $id++;
    } else {
        array_push($blocks, ...array_fill(0, (int)$diskMap[$start], $spaceChar));
    }
}
Console::vv(sprintf('blocks: %s', implode(' ', $blocks)));

$start = 0;
$end = count($blocks) - 1;
$checksum = 0;
while ($start < $end) {
    if ($blocks[$start] === $spaceChar) {
        for ($i = $end; $i >= $start + 2; $i--) {
            if ($blocks[$i] !== $spaceChar) {
                $blocks[$start] = $blocks[$i];
                $blocks[$i] = $spaceChar;
                Console::v(
                    sprintf(
                        'defragged (%s - %s): %s - %s ',
                        $start,
                        $end,
                        implode(' ', array_slice($blocks, $start - 10, 20)),
                        implode(' ', array_slice($blocks, $end - 10, 20)),
                    ),
                );
                break;
            }
            $end = $i;
        }
    }

    $checksum += $start * (int)$blocks[$start];
    Console::v(
        sprintf(
            'calc: %s (%s) = %s',
            $start,
            $blocks[$start],
            $checksum,
        ),
    );
    $start++;
}

Console::vv(sprintf('defragged blocks: %s', implode(' ', $blocks)));
Console::l(
    sprintf(
        'checksum is %s',
        $checksum,
    ),
);
