<?php

declare(strict_types=1);

include(__DIR__ . "/utils.php");

/**
 * https://adventofcode.com/2024/day/16
 *
 * strategy: walk possible paths recursively, calc and find min score on the way, remember visited pos & score to drop higher scored paths, exit early if score > min score
 *
 * run with php -d xdebug.max_nesting_level=-1 -d memory_limit=1024M 2024/16.2.php
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
    case BEST = 'O';
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

    public function turnOpposite(): Direction
    {
        return $this->turn(2);
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
$paths = [];
$minScore = PHP_INT_MAX;
$iterations = 0;
$walkMaze = function (array $pos, Direction $direction, int $score = 0, array $path = []) use (
    &$walkMaze,
    &$maze,
    &$visited,
    &$paths,
    &$minScore,
    &$iterations,
): int|null {
    $iterations++;
    if ($maze[$pos[0]][$pos[1]] === Maze::WALL) {
        return PHP_INT_MAX;
    }

    if ($score > $minScore) {
        return PHP_INT_MAX;
    }

    $id = sprintf('%s-%s', $pos[0], $pos[1]);
    // check if already visited with at least one turn more (else it is not the shortest way forward, so drop these longer paths)
    if (array_key_exists($id, $visited) && $score - 1000 > $visited[$id]) {
        return PHP_INT_MAX;
    }
    $visited[$id] = $score;
    $path[] = &$pos;

    if ($maze[$pos[0]][$pos[1]] === Maze::END) {
        $paths[] = [
            'path' => &$path,
            'score' => $score,
        ];
        $minScore = min($minScore, $score);

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
            $path,
        ),
        $walkMaze(
            [
                $pos[0] + $direction->turnLeft()->offset()[0],
                $pos[1] + $direction->turnLeft()->offset()[1],
            ],
            $direction->turnLeft(),
            $score + 1001,
            $path,
        ),
        $walkMaze(
            [
                $pos[0] + $direction->turnRight()->offset()[0],
                $pos[1] + $direction->turnRight()->offset()[1],
            ],
            $direction->turnRight(),
            $score + 1001,
            $path,
        ),
        $walkMaze(
            [
                $pos[0] + $direction->turnOpposite()->offset()[0],
                $pos[1] + $direction->turnOpposite()->offset()[1],
            ],
            $direction->turnOpposite(),
            $score + 2001,
            $path,
        ),
    );
};

$score = $walkMaze($pos, Direction::EAST);

$bestTiles = [];
$bestPaths = array_filter($paths, fn($path) => $path['score'] === $score);
foreach ($bestPaths as $path) {
    foreach ($path['path'] as $pos) {
        $id = sprintf('-%s-%s', $pos[0], $pos[1]);
        $bestTiles[$id] = true;
        $maze[$pos[0]][$pos[1]] = Maze::BEST;
    }
}

Console::vv(
    <<<TEXT
        The maaap:
        %s
        TEXT,
    implode(
        PHP_EOL,
        array_map(
            fn($row) => implode('', array_map(fn($n) => $n->value, $row)),
            $maze,
        ),
    ),
);

Console::l(
    'min score is %s, %s tiles are part of the %s best paths out of %s paths found (%s iterations)',
    $score,
    count($bestTiles),
    count($bestPaths),
    count($paths),
    $iterations,
);
