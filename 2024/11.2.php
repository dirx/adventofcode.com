<?php

declare(strict_types=1);

include(__DIR__ . "/utils.php");

/**
 * https://adventofcode.com/2024/day/11
 *
 * strategy: count stones recursive, use an index by stone and depth to shortcut
 */

if (Console::isTest()) {
    $puzzle = <<<PUZZLE
        125 17
        PUZZLE;
} else {
    $puzzle = file_get_contents(__DIR__ . '/11.txt');
}

$stones = array_map('intval', explode(" ", $puzzle));
$blinks = 75;

$blinkStone = function (int $stone): Generator {
    if ($stone === 0) {
        yield 1;

        return;
    }

    $digits = strlen((string)$stone);
    if ($digits % 2 === 0) {
        yield (int)substr((string)$stone, 0, $digits / 2);
        yield (int)substr((string)$stone, $digits / 2);

        return;
    }

    yield $stone * 2024;
};

$index = [];
$count = 0;
$blinkStones = function ($stones, $max, $i = 0) use (&$count, &$index, &$blinkStone, &$blinkStones) {
    if ($i === $max) {
        $count += iterator_count($stones);
        Console::v('count stones: %s, %s (max)', $count, $i);

        return;
    }
    foreach ($stones as $stone) {
        if (isset($index[$stone][$max - $i])) {
            $count += $index[$stone][$max - $i];
            Console::v('count stones: %s, %s (index)', $count, $i);
            continue;
        }

        $out = $blinkStone($stone);

        $lastCount = $count;
        $blinkStones($out, $max, $i + 1);

        $index[$stone][$max - $i] = $count - $lastCount;
        Console::vv('add index stone %s, depth %s = %s', $stone, $max - $i, $index[$stone][$max - $i]);
    }
};

$blinkStones(new ArrayIterator($stones), $blinks);

Console::l('%s stones after %s blinks', $count, $blinks);
