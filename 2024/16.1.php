<?php

declare(strict_types=1);

include(__DIR__ . "/utils.php");

/**
 * https://adventofcode.com/2024/day/16
 *
 * strategy: walk possible paths recursively, calc and find min score on the way, remember visited pos & score to drop higher scored paths.
 *
 * run with php -d xdebug.max_nesting_level=-1 2024/16.1.php
 */

if (Console::isTest()) {
    $puzzle = <<<PUZZLE
        ###############
        #.......#....E#
        #.#.###.#.###.#
        #.....#.#...#.#
        #.###.#####.#.#
        #.#.#.......#.#
        #.#.#####.###.#
        #...........#.#
        ###.#.#####.#.#
        #...#.....#.#.#
        #.#.#.###.#.#.#
        #.....#...#.#.#
        #.###.#.#.#.#.#
        #S..#.....#...#
        ###############
        PUZZLE;
} else {
    $puzzle = file_get_contents(__DIR__ . '/16.txt');
}

enum Maze: string
{
    case START = 'S';
    case END = 'E';
    case SPACE = '.';
    case WALL = '#';
}

enum Direction: string
{
    case NORTH = '^';
    case EAST = '>';
    case SOUTH = 'v';
    case WEST = '<';

    public function offset(): array
    {
        return match ($this) {
            self::NORTH => [-1, 0], // y, x
            self::EAST => [0, 1],
            self::SOUTH => [1, 0],
            self::WEST => [0, -1],
        };
    }

    private function turn(int $times): Direction
    {
        $cases = self::cases();
        $key = array_find_key($cases, fn($case) => $case === $this);

        return $cases[($key + count($cases) + $times) % count($cases)];
    }

    public function turnLeft(): Direction
    {
        return $this->turn(-1);
    }

    public function turnRight(): Direction
    {
        return $this->turn(1);
    }
}


$maze = [];
$pos = [];
foreach (explode("\n", $puzzle) as $y => $row) {
    $columns = preg_split('//', $row, flags: PREG_SPLIT_NO_EMPTY);
    $columns = array_map(fn($char) => Maze::from($char), $columns);
    if (($x = strpos($row, Maze::START->value)) !== false) {
        $pos = [$y, $x];
        $columns[$x] = Maze::SPACE;
    }
    $maze[] = $columns;
}

$visited = [];
$walkMaze = function (array $pos, Direction $direction, int $score = 0) use (&$walkMaze, &$maze, &$visited): int|null {
    if ($maze[$pos[0]][$pos[1]] === Maze::WALL) {
        return PHP_INT_MAX;
    }

    $id = sprintf('%s-%s', $pos[0], $pos[1]);
    if (array_key_exists($id, $visited) && $score >= $visited[$id]) {
        return PHP_INT_MAX;
    }
    $visited[$id] = $score;

    if ($maze[$pos[0]][$pos[1]] === Maze::END) {
        return $score;
    }

    return min(
        $walkMaze(
            [
                $pos[0] + $direction->offset()[0],
                $pos[1] + $direction->offset()[1],
            ],
            $direction,
            $score + 1,
        ),
        $walkMaze(
            [
                $pos[0] + $direction->turnLeft()->offset()[0],
                $pos[1] + $direction->turnLeft()->offset()[1],
            ],
            $direction->turnLeft(),
            $score + 1001,
        ),
        $walkMaze(
            [
                $pos[0] + $direction->turnRight()->offset()[0],
                $pos[1] + $direction->turnRight()->offset()[1],
            ],
            $direction->turnRight(),
            $score + 1001,
        ),
    );
};

$score = $walkMaze($pos, Direction::EAST);

Console::l('min score is %s', $score);
