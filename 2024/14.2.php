<?php

declare(strict_types=1);

include(__DIR__ . "/utils.php");

/**
 * https://adventofcode.com/2024/day/14
 *
 * strategy: move robots based on rules, if 50% are in the same quadrant we probably found it, play with % treshold
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
$quadrantTreshold = 0.5;

$calcQuadrants = function (array $robots) use (&$mapMiddle): array {
    $quadrants = [0 => 0, 1 => 0, 2 => 0, 3 => 0];
    foreach ($robots as $robot) {
        $quadrant = match (true) {
            $robot['position'][0] < $mapMiddle[0] && $robot['position'][1] < $mapMiddle[1] => 0,
            $robot['position'][0] > $mapMiddle[0] && $robot['position'][1] < $mapMiddle[1] => 1,
            $robot['position'][0] < $mapMiddle[0] && $robot['position'][1] > $mapMiddle[1] => 2,
            $robot['position'][0] > $mapMiddle[0] && $robot['position'][1] > $mapMiddle[1] => 3,
            default => null,
        };
        if ($quadrant === null) {
            continue;
        }

        $quadrants[$quadrant]++;
    }

    return $quadrants;
};

$seconds = 0;
while (true) {
    foreach ($robots as $i => $robot) {
        $robots[$i]['position'] = [
            ($mapSize[0] + $robot['position'][0] + $robot['velocity'][0]) % $mapSize[0],
            ($mapSize[1] + $robot['position'][1] + $robot['velocity'][1]) % $mapSize[1],
        ];
    }

    $quadrants = $calcQuadrants($robots);
    $quadrantsSum = array_sum($quadrants);
    foreach ($quadrants as $quadrant) {
        if ($quadrant / $quadrantsSum > $quadrantTreshold) {
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
