<?php

declare(strict_types=1);

include(__DIR__ . "/utils.php");

/**
 * https://adventofcode.com/2024/day/9
 *
 * strategy: map to blocks, find file by highest id from end of blocks, search enough space from start, swap if it matches, repeat with all ids desc, then calc checksum
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
for ($spaceEnd = 0; $spaceEnd < strlen($diskMap); $spaceEnd++) {
    if ($spaceEnd % 2 === 0) {
        array_push($blocks, ...array_fill(0, (int)$diskMap[$spaceEnd], $id));
        $id++;
    } else {
        array_push($blocks, ...array_fill(0, (int)$diskMap[$spaceEnd], $spaceChar));
    }
}
Console::vv('blocks: %s', implode(' ', $blocks));

$fileStart = count($blocks) - 1;
$spaceEnd = 0;
while (true) {
    // search for file from end
    $id--;
    $fileEnd = $fileStart;
    $fileId = null;
    while (true) {
        $block = $blocks[$fileStart];

        // mark end of file
        if ($fileId === null && $block === $id) {
            $fileId = $id;
            $fileEnd = $fileStart;
        }

        // file start found
        if ($fileId !== null && $block !== $fileId) {
            break;
        }

        $fileStart--;
        // file start found
        if ($fileStart < 0) {
            break;
        }
    }
    $fileSize = $fileEnd - $fileStart;

    // search for matching space from start
    $spaceEnd = 0;
    $spaceStart = $spaceEnd;
    while (true) {
        $block = $blocks[$spaceEnd];

        // no space, move startfile pos
        if ($block !== $spaceChar) {
            $spaceStart = $spaceEnd;
        }

        // if end file size matches space - swap and done
        if ($spaceEnd - $spaceStart === $fileSize) {
            for ($i = 1; $i <= $fileSize; $i++) {
                $blocks[$spaceStart + $i] = $blocks[$fileStart + $i];
                $blocks[$fileStart + $i] = $spaceChar;
            }
            break;
        }

        $spaceEnd++;
        if ($spaceEnd > $fileStart) {
            break;
        }
    }
    $spaceSize = $spaceEnd - $spaceStart;

    Console::vv('%s: swap %s - %s (%s) with %s - %s (%s)', $id, $spaceStart, $spaceEnd, $spaceSize, $fileStart, $fileEnd, $fileSize);

    // stop when all ids are done
    if ($id === 0) {
        break;
    }
}

// calc sum
$checksum = 0;
array_walk($blocks, function ($id, $index) use (&$checksum) {
    $checksum += (int)$id * $index;
});

Console::v('defragged blocks: %s', implode(' ', $blocks));
Console::l('checksum is %s', $checksum);
