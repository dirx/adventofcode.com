<?php

declare(strict_types=1);

include(__DIR__ . "/utils.php");

/**
 * https://adventofcode.com/2024/day/10
 *
 * strategy: scan column/row, if 0 start collect trailheads recursive in all directions, count trailheads, sum
 */

if (Console::isTest()) {
    $puzzle = <<<PUZZLE
        89010123
        78121874
        87430965
        96549874
        45678903
        32019012
        01329801
        10456732
        PUZZLE;
} else {
    $puzzle = file_get_contents(__DIR__ . '/10.txt');
}

$map = [];
foreach (explode("\n", $puzzle) as $row => $line) {
    $map[] = array_map(fn($i) => is_numeric($i) ? (int)$i : $i, preg_split('//', $line, flags: PREG_SPLIT_NO_EMPTY));
}
$mapSize = [
    count($map[0]),
    count($map),
];

const TRAIL_START = 0;
const TRAIL_END = 9;


enum Direction
{
    case UP;
    case RIGHT;
    case DOWN;
    case LEFT;

    public function value(): array
    {
        return match ($this) {
            self::UP => [-1, 0],
            self::RIGHT => [0, 1],
            self::DOWN => [1, 0],
            self::LEFT => [0, -1],
        };
    }
}

$findTrailhead = function (int $column, int $row, int $height, array &$heads) use (&$findTrailhead, $map, $mapSize): void {
    foreach (Direction::cases() as $direction) {
        $r = $row + $direction->value()[0];
        $c = $column + $direction->value()[1];

        if ( ! isset($map[$r][$c]) || $map[$r][$c] !== $height) {
            continue;
        }

        Console::v('checked %s: %s, %s - go direction %s', $height, $c, $r, $direction->name);

        if ($height === TRAIL_END) {
            $heads[] = [$c, $r];
        } else {
            $findTrailhead($c, $r, $height + 1, $heads);
        }
    }
};

$trailheads = [];
for ($row = 0; $row < $mapSize[1]; $row++) {
    for ($column = 0; $column < $mapSize[0]; $column++) {
        if ($map[$row][$column] === TRAIL_START) {
            $trailheads[sprintf('%s-%s', $column, $row)] = [];
            Console::v('checked %s: %s, %s - start', TRAIL_START, $column, $row);
            $findTrailhead($column, $row, TRAIL_START + 1, $trailheads[sprintf('%s-%s', $column, $row)]);
        }
    }
}

Console::l(
    'found %s trailheads with a rating of %s',
    count($trailheads),
    array_reduce($trailheads, fn($carry, $trailhead) => $carry + count($trailhead), 0),
);
