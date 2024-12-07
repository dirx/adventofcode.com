<?php

declare(strict_types=1);

include(__DIR__ . "/utils.php");

/**
 * https://adventofcode.com/2024/day/4
 *
 * strategy: at row/column search in directions up, right-up, right, right-down for the forward word and in the other 4 directions for the backward word
 */

if (Console::isTest()) {
    $puzzle = <<<PUZZLE
        MMMSXXMASM
        MSAMXMSMSA
        AMXSXMAAMM
        MSAMASMSMX
        XMASAMXAMM
        XXAMMXXAMA
        SMSMSASXSS
        SAXAMASAAA
        MAMMMXMMMM
        MXMXAXMASX
        PUZZLE;
} else {
    $puzzle = file_get_contents(__DIR__ . '/4.txt');
}

$puzzleIndex = [];
foreach (explode("\n", $puzzle) as $i => $line) {
    $puzzleIndex[] = preg_split("//", trim($line), flags: PREG_SPLIT_NO_EMPTY);
}
$puzzleRows = count($puzzleIndex);
$puzzleColumns = count($puzzleIndex[0]);

$word = 'XMAS';

$find = function (
    array $puzzleArray,
    string $word,
    array $position,
    array $searchDirection,
    int $wordDirection,
): array|null {
    $searchDirectionRow = $searchDirection[0] * $wordDirection;
    $searchDirectionColumn = $searchDirection[1] * $wordDirection;
    $wordLength = strlen($word);
    for ($i = 0; $i < $wordLength; $i++) {
        $offsetRow = $position[0] + $i * $searchDirectionRow;
        $offsetColumn = $position[1] + $i * $searchDirectionColumn;
        if ( ! isset($puzzleArray[$offsetRow][$offsetColumn])) {
            return null;
        }
        if ($word[$i] != $puzzleArray[$offsetRow][$offsetColumn]) {
            return null;
        }
    }

    return [
        'word' => $word,
        'position' => $position,
        'direction' => [$searchDirectionRow, $searchDirectionColumn],
    ];
};

$directionName = function (array $direction) {
    return match ($direction) {
        [1, 0] => 'down',
        [1, 1] => 'right-down',
        [0, 1] => 'right',
        [-1, 1] => 'right-up',
        [-1, 0] => 'up',
        [-1, -1] => 'left-up',
        [0, -1] => 'left',
        [1, -1] => 'left-down',
        default => 'unknown',
    };
};

$found = [];
for ($row = 0; $row < $puzzleRows; $row++) {
    for ($column = 0; $column < $puzzleColumns; $column++) {
        foreach ([[0, 1], [1, 0], [1, 1], [-1, 1]] as $searchDirection) {
            foreach ([1, -1] as $wordDirection) {
                $result = $find($puzzleIndex, $word, [$row, $column], $searchDirection, $wordDirection);
                if ($result !== null) {
                    $found[] = $result;
                    Console::v(
                        sprintf(
                            'found %s at row %s, column %s in direction %s.',
                            $word,
                            $row,
                            $column,
                            $directionName($result['direction']),
                        ),
                    );
                }
            }
        }
    }
}

Console::l(sprintf('found %s %d times', $word, count($found)));
