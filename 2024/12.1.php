<?php

declare(strict_types=1);

include(__DIR__ . "/utils.php");

/**
 * https://adventofcode.com/2024/day/12
 *
 * strategy: walk each and find region by looking at top right left down same neighbors and calc perimeter by each plant, sum
 */

if (Console::isTest()) {
    $puzzle = <<<PUZZLE
        RRRRIICCFF
        RRRRIICCCF
        VVRRRCCFFF
        VVRCCCJFFF
        VVVVCJJCFE
        VVIVCCJJEE
        VVIIICJJEE
        MIIIIIJJEE
        MIIISIJEEE
        MMMISSJEEE
        PUZZLE;
} else {
    $puzzle = file_get_contents(__DIR__ . '/12.txt');
}

$map = [];
foreach (explode("\n", $puzzle) as $row => $line) {
    $map[] = array_map(fn($i) => is_numeric($i) ? (int)$i : $i, preg_split('//', $line, flags: PREG_SPLIT_NO_EMPTY));
}
$mapSize = [
    count($map), // row
    count($map[0]), // col
];
$perimeters = [];
$costsPerRegion = [];

enum Direction
{
    case UP;
    case RIGHT;
    case DOWN;
    case LEFT;

    public function value(): array
    {
        return match ($this) {
            self::LEFT => [0, -1],
            self::UP => [-1, 0],
            self::RIGHT => [0, 1],
            self::DOWN => [1, 0],
        };
    }
}

$findRegion = function (int $row, int $column, array $area = []) use (&$findRegion, &$map, &$perimeters) {
    if (isset($perimeters[$row][$column])) {
        return $area;
    }
    $area[] = [$row, $column];
    $perimeters[$row][$column] = 0;
    foreach (Direction::cases() as $direction) {
        $c = $column + $direction->value()[0];
        $r = $row + $direction->value()[1];

        if ( ! isset($map[$r][$c]) || $map[$r][$c] !== $map[$row][$column]) {
            $perimeters[$row][$column]++;
            continue;
        }
        $area = $findRegion($r, $c, $area);
    }


    return $area;
};

for ($row = 0; $row < $mapSize[0]; $row++) {
    for ($column = 0; $column < $mapSize[1]; $column++) {
        if (isset($perimeters[$row][$column])) {
            continue;
        }

        $region = $findRegion($row, $column);
        $area = count($region);
        $perimeter = array_reduce($region, function ($carry, $item) use (&$perimeters) {
            return $carry + $perimeters[$item[0]][$item[1]];
        });
        $costsPerRegion[] = $cost = $area * $perimeter;
        Console::v('cost for %s: %s * %s = %s', $map[$row][$column], $area, $perimeter, $cost);
    }
}

Console::vv(
    <<<TEXT
        Perimeter Map:
        %s
        TEXT,
    implode(
        PHP_EOL,
        array_map(
            fn($row) => implode('', $row),
            $perimeters,
        ),
    ),
);

Console::l('found %s areas, total cost %s', count($costsPerRegion), array_sum($costsPerRegion));
