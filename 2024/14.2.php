<?php

declare(strict_types=1);

include(__DIR__ . "/utils.php");

/**
 * https://adventofcode.com/2024/day/14
 *
 * strategy: move robots based on rules, if double the normal distribution are in the same block we probably find it, play with block grid size & treshold
 */

if (Console::isTest()) {
    $puzzle = <<<PUZZLE
        p=0,4 v=3,-3
        p=6,3 v=-1,-3
        p=10,3 v=-1,2
        p=2,0 v=2,-1
        p=0,0 v=1,3
        p=3,0 v=-2,-2
        p=7,6 v=-1,-3
        p=3,0 v=-1,-2
        p=9,3 v=2,3
        p=7,3 v=-1,2
        p=2,4 v=2,-3
        p=9,5 v=-3,-3
        PUZZLE;
} else {
    $puzzle = file_get_contents(__DIR__ . '/14.txt');
}

$robots = [];
preg_match_all(
    '/p=(\d+),(\d+).+?v=([-\d]+),([-\d]+)/s',
    $puzzle,
    $matches,
    PREG_SET_ORDER,
);
foreach ($matches as $match) {
    $robots[] = [
        'position' => [(int)$match[1], (int)$match[2]],
        'velocity' => [(int)$match[3], (int)$match[4]],
    ];
}

$mapSize = [
    Console::isTest() ? 11 : 101,
    Console::isTest() ? 7 : 103,
];
$mapMiddle = [
    floor($mapSize[0] / 2),
    floor($mapSize[1] / 2),
];

$blockGridSize = 2; // 2 = quadrant
$blockTreshold = 1 / $blockGridSize;

$calcDistribution = function (array $robots, int $blockGridSize) use (&$mapSize): array {
    $blocks = array_fill(0, pow($blockGridSize, 2), 0);
    foreach ($robots as $robot) {
        $id = floor($blockGridSize * $robot['position'][0] / $mapSize[0])
              + $blockGridSize * floor($blockGridSize * $robot['position'][1] / $mapSize[1]);
        $blocks[$id]++;
    }

    return $blocks;
};

$robotsStart = $robots;
$robotsCount = count($robots);
$seconds = 0;
while (true) {
    foreach ($robots as $i => $robot) {
        $robots[$i]['position'] = [
            ($seconds * $mapSize[0] + $robotsStart[$i]['position'][0] + $seconds * $robot['velocity'][0]) % $mapSize[0],
            ($seconds * $mapSize[1] + $robotsStart[$i]['position'][1] + $seconds * $robot['velocity'][1]) % $mapSize[1],
        ];
    }

    foreach ($calcDistribution($robots, $blockGridSize) as $block) {
        if ($block / $robotsCount > $blockTreshold) {
            break 2;
        }
    }

    $seconds++;
}

$map = array_fill(0, $mapSize[0], array_fill(0, $mapSize[1], ' '));
foreach ($robots as $robot) {
    $map[$robot['position'][0]][$robot['position'][1]] = '*';
}
Console::vv(
    <<<TEXT
        The tree:
        %s
        TEXT,
    implode(
        PHP_EOL,
        array_map(
            fn($row) => implode('', $row),
            $map,
        ),
    ),
);

Console::l('found the tree at second %s', $seconds);
