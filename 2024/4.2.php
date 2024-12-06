<?php

declare(strict_types=1);

/**
 * https://adventofcode.com/2024/day/4
 *
 * strategy: at row/column search for all 4 variants by using a "half" word offset, 2 matches = found
 */

$puzzle = file_get_contents(__DIR__ . '/4.txt');
//$puzzle = <<<PUZZLE
//    MMMSXXMASM
//    MSAMXMSMSA
//    AMXSXMAAMM
//    MSAMASMSMX
//    XMASAMXAMM
//    XXAMMXXAMA
//    SMSMSASXSS
//    SAXAMASAAA
//    MAMMMXMMMM
//    MXMXAXMASX
//    PUZZLE;
$puzzleIndex = [];
foreach (explode("\n", $puzzle) as $i => $line) {
    $puzzleIndex[] = preg_split("//", trim($line), flags: PREG_SPLIT_NO_EMPTY);
}
$puzzleRows = count($puzzleIndex);
$puzzleColumns = count($puzzleIndex[0]);

$word = 'MAS';
$wordOffset = (int)-floor(strlen($word) / 2);

$find = function (
    array $puzzleArray,
    string $word,
    array $position,
    array $searchDirection,
    int $wordDirection,
    int $wordOffset,
): array|null {
    $searchDirectionRow = $searchDirection[0] * $wordDirection;
    $searchDirectionColumn = $searchDirection[1] * $wordDirection;
    $wordLength = strlen($word);

    for ($i = 0; $i < $wordLength; $i++) {
        $offsetRow = $position[0] + $i * $searchDirectionRow + $wordOffset * $searchDirectionRow;
        $offsetColumn = $position[1] + $i * $searchDirectionColumn + $wordOffset * $searchDirectionColumn;
        if ( ! isset($puzzleArray[$offsetRow][$offsetColumn])) {
            return null;
        }
        if ($word[$i] != $puzzleArray[$offsetRow][$offsetColumn]) {
            return null;
        }
    }

    return [
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
        $results = [];
        foreach ([[1, 1], [1, -1]] as $searchDirection) {
            foreach ([1, -1] as $wordDirection) {
                $result = $find($puzzleIndex, $word, [$row, $column], $searchDirection, $wordDirection, $wordOffset);
                if ($result !== null) {
                    $results[] = $result;
                }
            }
        }
        if (count($results) === 2) {
            $found[] = $results;
            echo sprintf(
                'found %s at row %s, column %s in direction %s and %s.' . PHP_EOL,
                $word,
                $row + 1,
                $column + 1,
                $directionName($results[0]['direction']),
                $directionName($results[1]['direction']),
            );
        }
    }
}

echo sprintf(
    'Found X-%s %d times' . PHP_EOL,
    $word,
    count($found),
);
