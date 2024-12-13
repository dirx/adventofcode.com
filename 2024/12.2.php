<?php

declare(strict_types=1);

include(__DIR__ . "/utils.php");

/**
 * https://adventofcode.com/2024/day/12
 *
 * strategy: walk each and find region by looking at top right left down same neighbors and calc side by each plant, sum
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
$sides = [];
$costsPerRegion = [];

enum Direction
{
    case LEFT;
    case LEFTUP;
    case UP;
    case RIGHTUP;
    case RIGHT;
    case RIGHTDOWN;
    case DOWN;
    case LEFTDOWN;

    public function value(): array
    {
        return match ($this) {
            self::LEFT => [0, -1],
            self::LEFTUP => [-1, -1],
            self::UP => [-1, 0],
            self::RIGHTUP => [-1, 1],
            self::RIGHT => [0, 1],
            self::RIGHTDOWN => [1, 1],
            self::DOWN => [1, 0],
            self::LEFTDOWN => [1, -1],
        };
    }
}

$findRegion = function (int $row, int $column, array $area = []) use (&$findRegion, &$map, &$perimeters, &$sides) {
    if (isset($sides[$row][$column])) {
        return $area;
    }
    $area[] = [$row, $column];
    $sides[$row][$column] = 0;
    $directions = [];
    foreach (Direction::cases() as $direction) {
        $c = $column + $direction->value()[0];
        $r = $row + $direction->value()[1];

        if ( ! isset($map[$r][$c]) || $map[$r][$c] !== $map[$row][$column]) {
            $directions[$direction->name] = false;
            continue;
        }
        $directions[$direction->name] = true;
        if (in_array($direction, [Direction::UP, Direction::DOWN, DIRECTION::LEFT, DIRECTION::RIGHT])) {
            $area = $findRegion($r, $c, $area);
        }
    }

    $sideUp = ! $directions[Direction::UP->name] &&
              (
                  ! $directions[Direction::LEFT->name]
                  || ($directions[Direction::LEFTUP->name] && $directions[Direction::LEFT->name])
              ) ? 1 : 0;
    $sideLeft = ! $directions[Direction::LEFT->name] &&
                (
                    ! $directions[Direction::UP->name]
                    || ($directions[Direction::LEFTUP->name] && $directions[Direction::UP->name])
                ) ? 1 : 0;
    $sideDown = ! $directions[Direction::DOWN->name] &&
                (
                    ! $directions[Direction::LEFT->name]
                    || ($directions[Direction::LEFT->name] && $directions[Direction::LEFTDOWN->name])
                ) ? 1 : 0;
    $sideRight = ! $directions[Direction::RIGHT->name] &&
                 (
                     ! $directions[Direction::UP->name]
                     || ($directions[Direction::UP->name] && $directions[Direction::RIGHTUP->name])
                 ) ? 1 : 0;

    $sides[$row][$column] = $sideUp + $sideLeft + $sideDown + $sideRight;

    return $area;
};

for ($row = 0; $row < $mapSize[0]; $row++) {
    for ($column = 0; $column < $mapSize[1]; $column++) {
        if (isset($sides[$row][$column])) {
            continue;
        }

        $region = $findRegion($row, $column);
        $area = count($region);
        $side = array_reduce($region, function ($carry, $item) use (&$sides) {
            return $carry + $sides[$item[0]][$item[1]];
        });
        $costsPerRegion[] = $cost = $area * $side;
        Console::v('cost for %s: %s * %s = %s', $map[$row][$column], $area, $side, $cost);
    }
}

Console::vv(
    <<<TEXT
        Sides Map:
        %s
        TEXT,
    implode(
        PHP_EOL,
        array_map(
            fn($row) => implode('', $row),
            $sides,
        ),
    ),
);

Console::l('found %s areas, total cost %s', count($costsPerRegion), array_sum($costsPerRegion));
