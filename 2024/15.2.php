<?php

declare(strict_types=1);

include(__DIR__ . "/utils.php");

/**
 * https://adventofcode.com/2024/day/15
 *
 * strategy: move bot based on rules (push boxes with vertical and horizontal rules, until walls), update map, get boxes coords (left box) and calc sum
 */

if (Console::isTest()) {
    $puzzle = <<<PUZZLE
        ##########
        #..O..O.O#
        #......O.#
        #.OO..O.O#
        #..O@..O.#
        #O#..O...#
        #O..O..O.#
        #.OO.O.OO#
        #....O...#
        ##########
        
        <vv>^<v^>v>^vv^v>v<>v^v<v<^vv<<<^><<><>>v<vvv<>^v^>^<<<><<v<<<v^vv^v>^
        vvv<<^>^v^^><<>>><>^<<><^vv^^<>vvv<>><^^v>^>vv<>v<<<<v<^v>^<^^>>>^<v<v
        ><>vv>v^v^<>><>>>><^^>vv>v<^^^>>v^v^<^^>v^^>v^<^v>v<>>v^v^<v>v^^<^^vv<
        <<v<^>>^^^^>>>v^<>vvv^><v<<<>^^^vv^<vvv>^>v<^^^^v<>^>vvvv><>>v^<<^^^^^
        ^><^><>>><>^^<<^^v>>><^<v>^<vv>>v>>>^v><>^v><<<<v>>v<v<v>vvv>^<><<>^><
        ^>><>^v<><^vvv<^^<><v<<<<<><^v<<<><<<^^<v<^^^><^>>^<v^><<<^>>^v<v^v<v^
        >^>>^v>vv>^<<^v<>><<><<v<<v><>v<^vv<<<>^^v^>^^>>><<^v>>v^v><^^>>^<>vv^
        <><^^>^^^<><vvvvv^v<v<<>^v<v>v<<^><<><<><<<^^<<<^<<>><<><^^^>^^<>^>v<>
        ^^>vv<^v^v<vv>^<><v<^v>^^^>>>^^vvv^>vvv<>>>^<^>>>>>^<<^v>^vvv<>^<><<v>
        v^^>>><<^^<>>^v^<v^vv<>v^<<>^<^v^v><^<<<><<^<v><v<>vv>>v><v^<vv<>v^<<^
        PUZZLE;
} else {
    $puzzle = file_get_contents(__DIR__ . '/15.txt');
}

enum Map: string
{
    case ROBOT = '@';
    case WALL = '#';
    case BOX_LEFT = '[';
    case BOX_RIGHT = ']';
    case SPACE = '.';
}

enum Direction: string
{
    case UP = '^';
    case RIGHT = '>';
    case DOWN = 'v';
    case LEFT = '<';

    public function offset(): array
    {
        return match ($this) {
            self::UP => [-1, 0], // y, x
            self::RIGHT => [0, 1],
            self::DOWN => [1, 0],
            self::LEFT => [0, -1],
        };
    }

    public function isHorizontal(): bool
    {
        return $this == Direction::LEFT || $this == Direction::RIGHT;
    }
}

$map = [];
$moves = [];
$robot = [];
foreach (explode("\n", $puzzle) as $y => $row) {
    $row = str_replace(
        [
            Map::SPACE->value,
            Map::WALL->value,
            Map::ROBOT->value,
            'O',
        ],
        [
            Map::SPACE->value . Map::SPACE->value,
            Map::WALL->value . Map::WALL->value,
            Map::ROBOT->value . Map::SPACE->value,
            Map::BOX_LEFT->value . Map::BOX_RIGHT->value,
        ],
        $row,
    );

    $columns = preg_split('//', $row, flags: PREG_SPLIT_NO_EMPTY);

    if (count($columns) === 0) {
        continue;
    }
    if ($columns[0] === Map::WALL->value) {
        $columns = array_map(fn($char) => MAP::from($char), $columns);
        if (($x = strpos($row, Map::ROBOT->value)) !== false) {
            $robot = [$y, $x];
            $columns[$x] = MAP::SPACE;
        }
        $map[] = $columns;
        continue;
    }
    array_push($moves, ...array_map(fn($char) => Direction::from($char), $columns));
}

$moveBoxHorizontal = function (array $pos, Direction $direction) use (&$moveBoxHorizontal, &$map): bool {
    $newPos = [
        $pos[0] + $direction->offset()[0],
        $pos[1] + $direction->offset()[1],
    ];

    if ($map[$newPos[0]][$newPos[1]] === Map::WALL) {
        return false;
    }

    if ($map[$newPos[0]][$newPos[1]] === Map::SPACE || $moveBoxHorizontal($newPos, $direction)) {
        $map[$newPos[0]][$newPos[1]] = $map[$pos[0]][$pos[1]];
        $map[$pos[0]][$pos[1]] = MAP::SPACE;

        return true;
    }

    return false;
};

$moveBoxVertical = function (array $pos, Direction $direction, bool $dryRun = false) use (&$moveBoxVertical, &$map): bool {
    $isLeft = $map[$pos[0]][$pos[1]] === Map::BOX_LEFT;

    $posRight = [
        $pos[0],
        $pos[1] + ($isLeft ? 1 : 0),
    ];
    $posLeft = [
        $pos[0],
        $pos[1] + ($isLeft ? 0 : -1),
    ];
    $newPosRight = [
        $posRight[0] + $direction->offset()[0],
        $posRight[1] + $direction->offset()[1],
    ];
    $newPosLeft = [
        $posLeft[0] + $direction->offset()[0],
        $posLeft[1] + $direction->offset()[1],
    ];

    if ($map[$newPosRight[0]][$newPosRight[1]] === Map::SPACE && $map[$newPosLeft[0]][$newPosLeft[1]] === Map::SPACE) {
        $movable = true;
    } elseif ($map[$newPosRight[0]][$newPosRight[1]] === Map::BOX_RIGHT && $map[$newPosLeft[0]][$newPosLeft[1]] === Map::BOX_LEFT) {
        $movable = $moveBoxVertical($newPosLeft, $direction, $dryRun);
    } elseif ($map[$newPosRight[0]][$newPosRight[1]] === Map::SPACE && $map[$newPosLeft[0]][$newPosLeft[1]] === Map::BOX_RIGHT) {
        $movable = $moveBoxVertical($newPosLeft, $direction, $dryRun);
    } elseif ($map[$newPosLeft[0]][$newPosLeft[1]] === Map::SPACE && $map[$newPosRight[0]][$newPosRight[1]] === Map::BOX_LEFT) {
        $movable = $moveBoxVertical($newPosRight, $direction, $dryRun);
    } elseif ($map[$newPosRight[0]][$newPosRight[1]] === Map::BOX_LEFT && $map[$newPosLeft[0]][$newPosLeft[1]] === Map::BOX_RIGHT) {
        $movable = $moveBoxVertical($newPosRight, $direction, true) && $moveBoxVertical($newPosLeft, $direction, true);

        if ($movable && ! $dryRun) {
            $moveBoxVertical($newPosRight, $direction);
            $moveBoxVertical($newPosLeft, $direction);
        }
    } else {
        return false;
    }

    if ($movable && ! $dryRun) {
        $map[$newPosRight[0]][$newPosRight[1]] = $map[$posRight[0]][$posRight[1]];
        $map[$posRight[0]][$posRight[1]] = MAP::SPACE;
        $map[$newPosLeft[0]][$newPosLeft[1]] = $map[$posLeft[0]][$posLeft[1]];
        $map[$posLeft[0]][$posLeft[1]] = MAP::SPACE;
    }

    return $movable;
};

$moveBox = function (array $pos, Direction $direction) use (&$moveBoxHorizontal, &$moveBoxVertical, &$map): bool {
    return $direction->isHorizontal()
        ? $moveBoxHorizontal($pos, $direction)
        : $moveBoxVertical($pos, $direction);
};

$moveRobot = function (array $pos, Direction $direction) use (&$moveBox, &$map): array {
    $newPos = [
        $pos[0] + $direction->offset()[0],
        $pos[1] + $direction->offset()[1],
    ];
    if ($map[$newPos[0]][$newPos[1]] === Map::WALL) {
        return $pos;
    }
    if ($map[$newPos[0]][$newPos[1]] === Map::BOX_LEFT || $map[$newPos[0]][$newPos[1]] === Map::BOX_RIGHT) {
        if ( ! $moveBox($newPos, $direction)) {
            return $pos;
        }
    }

    return $newPos;
};

$drawMap = function (int $iteration, array $map, array $robot, Direction $direction): void {
    if (Console::verbosity() < 2) {
        return;
    }
    $map[$robot[0]][$robot[1]] = MAP::ROBOT;
    Console::vv(
        <<<TEXT
            The maaap (iteration %s - moved %s):
            %s
            TEXT,
        $iteration,
        $direction->value,
        implode(
            PHP_EOL,
            array_map(
                fn($row) => implode('', array_map(fn($n) => $n->value, $row)),
                $map,
            ),
        ),
    );
};

foreach ($moves as $i => $direction) {
    $robot = $moveRobot($robot, $direction);
    $drawMap($i, $map, $robot, $direction);
}

// calc boxes pos
$boxesCoordinates = [];
foreach ($map as $y => $row) {
    foreach ($row as $x => $item) {
        if ($item === Map::BOX_LEFT) {
            $boxesCoordinates[] = $x + 100 * $y;
        }
    }
}

Console::l('sum of all boxes GPS coordinates is %s after %s moves', array_sum($boxesCoordinates), count($moves));
