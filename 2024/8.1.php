<?php

declare(strict_types=1);

include(__DIR__ . "/utils.php");

/**
 * https://adventofcode.com/2024/day/8
 *
 * strategy: nodes pos and distances, calc anti node pos repeating in both directions (take care of map boundary), sum distinct pos
 */

if (Console::isTest()) {
    $puzzle = <<<PUZZLE
        ............
        ........0...
        .....0......
        .......0....
        ....0.......
        ......A.....
        ............
        ............
        ........A...
        .........A..
        ............
        ............
        PUZZLE;
} else {
    $puzzle = file_get_contents(__DIR__ . '/8.txt');
}

$map = [];
foreach (explode("\n", $puzzle) as $row => $line) {
    $map[] = preg_split('//', $line, flags: PREG_SPLIT_NO_EMPTY);
}
$mapSize = [
    count($map) - 1,
    count($map[0]) - 1,
];
$antiNodeChar = '#';
$nodeCharPattern = '/[a-zA-Z0-9]/';

$nodes = [];
$antiNodes = [];

// find node pos
for ($row = 0; $row < count($map); $row++) {
    for ($column = 0; $column < count($map[$row]); $column++) {
        if (preg_match($nodeCharPattern, $map[$row][$column], $match)) {
            $nodes[$match[0]][] = [$row, $column];
        }
    }
}

// calc distance and anti node pos
$distances = [];
foreach ($nodes as $node => $nodePositions) {
    for ($i = 0; $i < count($nodePositions); $i++) {
        for ($j = $i + 1; $j < count($nodePositions); $j++) {
            Console::v('compare %s node %s to %s', $node, $i, $j);
            $distance = [
                $nodePositions[$j][0] - $nodePositions[$i][0],
                $nodePositions[$j][1] - $nodePositions[$i][1],
            ];
            Console::vvv('distance %s,%s - %s,%s = %s,%s', ...$nodePositions[$i], ...$nodePositions[$j], ...$distance);
            $distances[$node][] = $distance;

            foreach (
                [
                    [
                        $nodePositions[$i][0] - $distance[0],
                        $nodePositions[$i][1] - $distance[1],
                    ],
                    [
                        $nodePositions[$j][0] + $distance[0],
                        $nodePositions[$j][1] + $distance[1],
                    ],
                ] as $antiNode
            ) {
                if ($antiNode[0] < 0 || $antiNode[1] < 0 || $antiNode[0] > $mapSize[0] || $antiNode[1] > $mapSize[1]) {
                    Console::vv('skip - out of bounds anti node at %s,%s', ...$antiNode);
                    continue;
                }
                $antiNodeId = sprintf('%s-%s', ...$antiNode);
                if (isset($antiNodes[$antiNodeId])) {
                    Console::vv('skip - already found anti node at %s,%s', ...$antiNode);
                } else {
                    Console::v('> found - anti node at %s,%s', ...$antiNode);
                    $antiNodes[$antiNodeId] = $antiNode;
                }
            }
        }
    }
}

Console::l(
    'found %s distinct nodes, %s distinct anti nodes',
    count($nodes),
    count($antiNodes),
);

// mark anti nodes on map
foreach ($antiNodes as [$row, $column]) {
    if ( ! preg_match($nodeCharPattern, $map[$row][$column], $match)) {
        $map[$row][$column] = $antiNodeChar;
    }
}
Console::vv(
    sprintf(
        <<<TEXT
            The maaaaaap:
            %s
            TEXT,
        implode(
            PHP_EOL,
            array_map(
                fn($row) => implode('', $row),
                $map,
            ),
        ),
    ),
);
