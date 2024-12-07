<?php

declare(strict_types=1);

include(__DIR__ . "/utils.php");

/**
 * https://adventofcode.com/2024/day/6
 *
 * strategy: 2d map, find start pos, then mark as visited and count distinct, check next pos, if out of bounds leave, if obstacle turn else walk and repeat.
 */

if (Console::isTest()) {
    $puzzle = <<<PUZZLE
        ....#.....
        .........#
        ..........
        ..#.......
        .......#..
        ..........
        .#..^.....
        ........#.
        #.........
        ......#...
        PUZZLE;
} else {
    $puzzle = file_get_contents(__DIR__ . '/6.txt');
}

enum Direction
{
    case UP;
    case RIGHT;
    case DOWN;
    case LEFT;

    public function name(): string
    {
        return match ($this) {
            self::UP => '^',
            self::RIGHT => '>',
            self::DOWN => 'v',
            self::LEFT => '<',
        };
    }

    public function turnRight(): Direction
    {
        return match ($this) {
            self::UP => self::RIGHT,
            self::RIGHT => self::DOWN,
            self::DOWN => self::LEFT,
            self::LEFT => self::UP,
        };
    }

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

$direction = Direction::UP;
$obstacle = '#';
$visited = 'X';

$map = [];
$startPosition = [];
foreach (explode("\n", $puzzle) as $row => $line) {
    $map[] = preg_split('//', $line, flags: PREG_SPLIT_NO_EMPTY);
    $column = strpos($line, $direction->name());
    if ($column !== false) {
        $startPosition = [$row, $column];
    }
}
$mapSize = [
    count($map) - 1,
    count($map[0]) - 1,
];

$position = $startPosition;
$distinctPositions = 0;
$steps = 0;
$turns = 0;
while (true) {
    // mark pos as visited and count
    if ($map[$position[0]][$position[1]] !== $visited) {
        Console::v(sprintf('Marked %s, %s as visited', $position[0], $position[1]));
        $map[$position[0]][$position[1]] = $visited;
        $distinctPositions++;
    }

    // calc next pos
    $nextPosition = [
        $position[0] + $direction->value()[0],
        $position[1] + $direction->value()[1],
    ];

    // out of bounds? leave
    if ($nextPosition[0] < 0 || $nextPosition[1] < 0 || $nextPosition[0] > $mapSize[0] || $nextPosition[1] > $mapSize[1]) {
        Console::v(sprintf('Left the map at %s, %s (map size %s, %s)', $position[0], $position[1], $mapSize[0], $mapSize[1]));
        break;
    }

    // obstacle? turn right and continue
    if ($map[$nextPosition[0]][$nextPosition[1]] === $obstacle) {
        $oldDirection = $direction;
        $direction = $direction->turnRight();
        $turns++;
        Console::v(
            sprintf(
                'Obstacle at %s, %s: turned from %s to %s',
                $nextPosition[0],
                $nextPosition[1],
                $oldDirection->name(),
                $direction->name(),
            ),
        );
        continue;
    }

    // walk
    $position = $nextPosition;
    $steps++;
    Console::v(sprintf('Walked to %s, %s', $position[0], $position[1]));
}

Console::l(
    sprintf(
        'Walked %s steps, turned %s times, visited %s distinct positions.',
        $steps,
        $turns,
        $distinctPositions,
    ),
);

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
