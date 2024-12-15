<?php

declare(strict_types=1);

include(__DIR__ . "/utils.php");

/**
 * https://adventofcode.com/2024/day/15
 *
 * strategy: move bot based on rules (push boxes, until walls), update map, get boxes coords and calc sum
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
    case BOX = 'O';
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
}

$map = [];
$moves = [];
$robot = [];
foreach (explode("\n", $puzzle) as $y => $row) {
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

$moveBox = function (array $pos, Direction $direction) use (&$moveBox, &$map): bool {
    $newPos = [
        $pos[0] + $direction->offset()[0],
        $pos[1] + $direction->offset()[1],
    ];
    if ($map[$newPos[0]][$newPos[1]] === Map::WALL) {
        return false;
    }
    if ($map[$newPos[0]][$newPos[1]] === Map::SPACE || $moveBox($newPos, $direction)) {
        $map[$pos[0]][$pos[1]] = MAP::SPACE;
        $map[$newPos[0]][$newPos[1]] = MAP::BOX;

        return true;
    }

    return false;
};

$moveRobot = function (array $pos, Direction $direction) use (&$moveBox, &$map): array {
    $newPos = [
        $pos[0] + $direction->offset()[0],
        $pos[1] + $direction->offset()[1],
    ];
    if ($map[$newPos[0]][$newPos[1]] === Map::WALL) {
        return $pos;
    }
    if ($map[$newPos[0]][$newPos[1]] === Map::BOX) {
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
        if ($item === Map::BOX) {
            $boxesCoordinates[] = $x + 100 * $y;
        }
    }
}

Console::l('sum of all boxes GPS coordinates is %s', array_sum($boxesCoordinates));
