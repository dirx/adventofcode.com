<?php

declare(strict_types=1);

include(__DIR__ . "/utils.php");

/**
 * https://adventofcode.com/2024/day/5
 *
 * strategy: recursive comparison (in case not all pages have direct rules), if correct, collect middle page, sum middle pages
 */

if (Console::isTest()) {
    $puzzle = <<<PUZZLE
        47|53
        97|13
        97|61
        97|47
        75|29
        61|13
        75|53
        29|13
        97|29
        53|29
        61|53
        97|53
        61|29
        47|13
        75|47
        97|75
        47|61
        75|61
        47|29
        75|13
        53|13
        
        75,47,61,53,29
        97,61,53,29,13
        75,29,13
        75,97,47,61,53
        61,13,29
        97,13,75,29,47
        PUZZLE;
} else {
    $puzzle = file_get_contents(__DIR__ . '/5.txt');
}

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

function compare($a, $b, $orderRules, $level = 0): int
{
    $indent = str_repeat(' ', $level * 2);
    Console::v(
        sprintf(
            '%s- compare %s - %s',
            $indent,
            $a,
            $b,
        ),
    );

    $next = [];
    foreach ($orderRules as $rule) {
        if ($rule[0] === $a && $rule[1] === $b) {
            Console::vv(
                sprintf(
                    '%s- found %s < %s',
                    $indent,
                    $a,
                    $b,
                ),
            );

            return -1;
        }
        if ($rule[0] === $b && $rule[1] === $a) {
            Console::vv(
                sprintf(
                    '%s- found %s > %s',
                    $indent,
                    $a,
                    $b,
                ),
            );

            return 1;
        }
        if ($rule[0] === $a) {
            $next[] = $rule[1];
        }
    }

    foreach ($next as $c) {
        $result = compare($c, $b, $orderRules, $level + 1);
        if ($result !== 0) {
            return $result;
        }
    }

    Console::vv(
        sprintf(
            '- not found %s - %s',
            $a,
            $b,
        ),
    );

    return 0;
}

foreach ($printOrders as $printOrder) {
    Console::v(sprintf('check print order %s', implode(',', $printOrder)));
    $middle = floor(count($printOrder) / 2);
    for ($i = 0; $i < count($printOrder) - 1; $i++) {
        $pageA = $printOrder[$i];
        $pageB = $printOrder[$i + 1];

        $result = compare($pageA, $pageB, $orderRules);
        if ($result === 0) {
            Console::v('- no rule');
        } elseif ($result === 1) {
            Console::v('- incorrect');
            continue 2;
        } else {
            Console::v('- correct');
        }
    }
    $correctPrintOrdersMiddlePage[] = $printOrder[$middle];
}

Console::l(
    sprintf(
        'found %s / %s correct orders. middle page sum is %s (%s)',
        count($correctPrintOrdersMiddlePage),
        count($printOrders),
        array_sum($correctPrintOrdersMiddlePage),
        implode(',', $correctPrintOrdersMiddlePage),
    ),
);
