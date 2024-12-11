<?php

declare(strict_types=1);

include(__DIR__ . "/utils.php");

/**
 * https://adventofcode.com/2024/day/11
 *
 * strategy: map blinking rules via generator (yielding splits seems easier)
 */

if (Console::isTest()) {
    $puzzle = <<<PUZZLE
        125 17
        PUZZLE;
} else {
    $puzzle = file_get_contents(__DIR__ . '/11.txt');
}

$stones = array_map('intval', explode(" ", $puzzle));

$blinking = function ($stones): Generator {
    foreach ($stones as $stone) {
        if ($stone === 0) {
            yield 1;
            continue;
        }

        $digits = strlen((string)$stone);
        if ($digits % 2 === 0) {
            yield (int)substr((string)$stone, 0, $digits / 2);
            yield (int)substr((string)$stone, $digits / 2);
            continue;
        }

        yield $stone * 2024;
    }
};

for ($blink = 0; $blink < 25; $blink++) {
    $stones = iterator_to_array($blinking($stones));
    Console::v('%s: %s', $blink, implode(' ', $stones));
}

Console::l('%s stones after 25 blinks', count($stones));
