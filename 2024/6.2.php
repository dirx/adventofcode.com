<?php

declare(strict_types=1);

include __DIR__ . '/utils.php';

/**
 * https://adventofcode.com/2024/day/6
 *
 * strategy: based on 6.1 - for each next position check if we would hit a loop if a potential obstacle, if so collect distinct positions, sum
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
$potentialObstacle = 'O';
$upDown = '|';
$leftRight = '-';
$crossroad = '+';
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
$potentialObstacles = [];

$checkLoop = function (array $potentialObstaclePosition) use (
    $map,
    $mapSize,
    $startPosition,
    $direction,
    $obstacle,
    $upDown,
    $leftRight,
    $crossroad,
): bool {
    Console::vv('Check loop with obstacle at %s, %s', $potentialObstaclePosition[0], $potentialObstaclePosition[1]);

    // mark obstacle
    $map[$potentialObstaclePosition[0]][$potentialObstaclePosition[1]] = $obstacle;

    $position = $startPosition;
    $track = [];
    do {
        if ( ! in_array($map[$position[0]][$position[1]], [$upDown, $leftRight, $crossroad])) {
            Console::vvv('Marked %s, %s as visited in direction %s', $position[0], $position[1], $direction->name());
            $map[$position[0]][$position[1]] = match ($direction) {
                Direction::UP, Direction::DOWN => $upDown,
                Direction::LEFT, Direction::RIGHT => $leftRight,
            };
        }
        if (($map[$position[0]][$position[1]] === $upDown && ($direction === Direction::LEFT || $direction === Direction::RIGHT))
            || ($map[$position[0]][$position[1]] === $leftRight && ($direction === Direction::UP || $direction === Direction::DOWN))) {
            $map[$position[0]][$position[1]] = $crossroad;
        }

        // loop? hit same pos in same dir?
        $distinctPositionId = sprintf('%s,%s,%s', $position[0], $position[1], $direction->name());
        if (array_key_exists($distinctPositionId, $track)) {
            Console::vv('LOOP: walked again on %s, %s in direction %s', $position[0], $position[1], $direction->name());

            Console::vvv(
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
            );

            return true;
        }

        $track[$distinctPositionId] = $position;

        $nextPosition = [
            $position[0] + $direction->value()[0],
            $position[1] + $direction->value()[1],
        ];

        // out of bounds? there is nothing
        if ($nextPosition[0] < 0 || $nextPosition[1] < 0 || $nextPosition[0] > $mapSize[0] || $nextPosition[1] > $mapSize[1]) {
            Console::vvv('NOLOOP: left the map at %s, %s (map size %s, %s)', $position[0], $position[1], $mapSize[0], $mapSize[1]);

            return false;
        }

        // obstacle in the way? turn right
        if ($map[$nextPosition[0]][$nextPosition[1]] === $obstacle) {
            $oldDirection = $direction;
            $direction = $direction->turnRight();
            Console::vvv(
                'Obstacle at %s, %s: turned from %s to %s',
                $nextPosition[0],
                $nextPosition[1],
                $oldDirection->name(),
                $direction->name(),
            );

            continue;
        }

        $position = $nextPosition;
    } while (true);
};

$position = $startPosition;
$distinctPositions = 0;
$steps = 0;
$turns = 0;
while (true) {
    // mark pos as visited and count
    if ( ! in_array($map[$position[0]][$position[1]], [$upDown, $leftRight, $crossroad])) {
        Console::vv('Marked %s, %s as visited', $position[0], $position[1]);
        $map[$position[0]][$position[1]] = match ($direction) {
            Direction::UP, Direction::DOWN => $upDown,
            Direction::LEFT, Direction::RIGHT => $leftRight,
        };
        $distinctPositions++;
    }
    if (($map[$position[0]][$position[1]] === $upDown && ($direction === Direction::LEFT || $direction === Direction::RIGHT))
        || ($map[$position[0]][$position[1]] === $leftRight && ($direction === Direction::UP || $direction === Direction::DOWN))) {
        $map[$position[0]][$position[1]] = $crossroad;
    }

    // calc next pos
    $nextPosition = [
        $position[0] + $direction->value()[0],
        $position[1] + $direction->value()[1],
    ];

    // out of bounds? leave
    if ($nextPosition[0] < 0 || $nextPosition[1] < 0 || $nextPosition[0] > $mapSize[0] || $nextPosition[1] > $mapSize[1]) {
        Console::v('Left the map at %s, %s (map size %s, %s)', $position[0], $position[1], $mapSize[0], $mapSize[1]);
        break;
    }

    // obstacle? new dir and continue
    if ($map[$nextPosition[0]][$nextPosition[1]] === $obstacle) {
        $oldDirection = $direction;
        $direction = $direction->turnRight();
        $turns++;
        Console::vv(
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

    // let´s check for potential obstacle pos
    if ($checkLoop($nextPosition)) {
        $potentialObstacleId = sprintf('%s-%s', $nextPosition[0], $nextPosition[1]);
        Console::v(
            'Potential obstacle at %s, %s%s',
            $nextPosition[0],
            $nextPosition[1],
            array_key_exists($potentialObstacleId, $potentialObstacles) ? ' (already found)' : '',
        );
        $potentialObstacles[$potentialObstacleId] = $nextPosition;
    }

    // walk
    $position = $nextPosition;
    $steps++;
    Console::vv('Walked to %s, %s', $position[0], $position[1]);
}

Console::l(
    'Walked %s steps, turned %s times, visited %s distinct positions, found %s potential obstacles.',
    $steps,
    $turns,
    $distinctPositions,
    count($potentialObstacles),
);

// add potential obstacles
foreach ($potentialObstacles as $obstacle) {
    $map[$obstacle[0]][$obstacle[1]] = $potentialObstacle;
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
