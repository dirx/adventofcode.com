<?php

declare(strict_types=1);

/**
 * https://adventofcode.com/2024/day/5
 *
 * strategy: recursive comparison (in case not all pages have direct rules), if incorrect, sort with comparison and collect middle page, sum middle pages
 */

$puzzle = file_get_contents(__DIR__ . '/5.txt');
//$puzzle = <<<PUZZLE
//    47|53
//    97|13
//    97|61
//    97|47
//    75|29
//    61|13
//    75|53
//    29|13
//    97|29
//    53|29
//    61|53
//    97|53
//    61|29
//    47|13
//    75|47
//    97|75
//    47|61
//    75|61
//    47|29
//    75|13
//    53|13
//
//    75,47,61,53,29
//    97,61,53,29,13
//    75,29,13
//    75,97,47,61,53
//    61,13,29
//    97,13,75,29,47
//    PUZZLE;

$orderRules = [];
$printOrders = [];
$correctPrintOrdersMiddlePage = [];

foreach (explode("\n", $puzzle) as $i => $line) {
    if (str_contains($line, '|')) {
        $orderRules[] = array_map('intval', explode('|', $line));
    } elseif (str_contains($line, ',')) {
        $printOrders[] = array_map('intval', explode(',', $line));
    }
}

$compare = function ($a, $b, $level = 0) use (&$compare, $orderRules): int {
    $indent = str_repeat(' ', $level * 2);
    echo sprintf(
        '%s- compare %s - %s' . PHP_EOL,
        $indent,
        $a,
        $b,
    );

    $next = [];
    foreach ($orderRules as $rule) {
        if ($rule[0] === $a && $rule[1] === $b) {
            echo sprintf(
                '%s- found %s < %s' . PHP_EOL,
                $indent,
                $a,
                $b,
            );

            return -1;
        }
        if ($rule[0] === $b && $rule[1] === $a) {
            echo sprintf(
                '%s- found %s > %s' . PHP_EOL,
                $indent,
                $a,
                $b,
            );

            return 1;
        }
        if ($rule[0] === $a) {
            $next[] = $rule[1];
        }
    }

    foreach ($next as $c) {
        $result = $compare($c, $b, $level + 1);
        if ($result !== 0) {
            return $result;
        }
    }

    echo sprintf(
        '- not found %s - %s' . PHP_EOL,
        $a,
        $b,
    );

    return 0;
};

foreach ($printOrders as $printOrder) {
    echo sprintf(
        'Check print order %s' . PHP_EOL,
        implode(',', $printOrder),
    );
    $middle = floor(count($printOrder) / 2);
    for ($i = 0; $i < count($printOrder) - 1; $i++) {
        $pageA = $printOrder[$i];
        $pageB = $printOrder[$i + 1];

        $result = $compare($pageA, $pageB);
        if ($result === 0) {
            echo sprintf(
                '  - no rule' . PHP_EOL,
            );
        } elseif ($result === 1) {
            echo sprintf(
                '  - fix incorrect' . PHP_EOL,
            );
            usort($printOrder, $compare);
            echo sprintf(
                '  - corrected %s' . PHP_EOL,
                implode(',', $printOrder),
            );
            $correctPrintOrdersMiddlePage[] = $printOrder[$middle];
            break;
        } else {
            echo sprintf(
                '  - correct' . PHP_EOL,
            );
        }
    }
}

echo sprintf(
    'corrected %s / %s orders. middle page sum is %s (%s).' . PHP_EOL,
    count($correctPrintOrdersMiddlePage),
    count($printOrders),
    array_sum($correctPrintOrdersMiddlePage),
    implode(',', $correctPrintOrdersMiddlePage),
);
