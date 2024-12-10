<?php

declare(strict_types=1);

include(__DIR__ . "/utils.php");

/**
 * https://adventofcode.com/2024/day/7
 *
 * strategy: check each equation against operator permutations, calc, skip early if too high, else collect, sum
 */

if (Console::isTest()) {
    $puzzle = <<<PUZZLE
        190: 10 19
        3267: 81 40 27
        83: 17 5
        156: 15 6
        7290: 6 8 6 15
        161011: 16 10 13
        192: 17 8 14
        21037: 9 7 18 13
        292: 11 6 16 20
        PUZZLE;
} else {
    $puzzle = file_get_contents(__DIR__ . '/7.txt');
}

$equations = [];
foreach (explode("\n", $puzzle) as $row => $line) {
    [$sum, $numbers] = explode(":", $line);
    $equations[] = [
        (int)$sum,
        array_map('intval', explode(" ", trim($numbers))),
    ];
}

$operators = [
    '*', // start with higher impact
    '+',
    '||',
];

$operatorPermutations = function (array $operators, int $equationNumbersCount) use (&$operatorPermutations): Generator {
    $equationOperatorsCount = $equationNumbersCount - 1;
    $operatorsCount = count($operators);
    $permutationsCount = pow($operatorsCount, $equationOperatorsCount);

    for ($j = 0; $j < $permutationsCount; $j++) {
        $permutation = [];
        for ($i = 0; $i < $equationOperatorsCount; $i++) {
            $operator = floor($j / (pow($operatorsCount, $equationOperatorsCount - $i - 1))) % ($operatorsCount);
            $permutation[] = $operators[$operator];
        }
        Console::vvv('permutation (%s/%s) %s', $j, $permutationsCount, implode(' ', $permutation));
        yield $permutation;
    }
};

$correctEquations = [];
foreach ($equations as $equationId => [$sum, $numbers]) {
    Console::v('check equation %s: %s = %s', $equationId, $sum, implode(' ? ', $numbers));

    $sumCandidates = [];
    $equationNumbersCount = count($numbers);
    foreach ($operatorPermutations($operators, $equationNumbersCount) as $operatorPermutation) {
        $sumCandidate = $numbers[0];
        $equation = [$numbers[0]];
        for ($i = 1; $i < $equationNumbersCount; $i++) {
            $equation[] = $operatorPermutation[$i - 1];
            $equation[] = $numbers[$i];
            $sumCandidate = match ($operatorPermutation[$i - 1]) {
                '*' => $sumCandidate * $numbers[$i],
                '+' => $sumCandidate + $numbers[$i],
                '||' => (int)($sumCandidate . $numbers[$i]),
//                '||' => $sumCandidate * pow(10, strlen((string)$sumCandidate) - 1) + $numbers[$i],
            };
            if ($sumCandidate > $sum) {
                Console::vv('skip - too high: %s < %s = %s', $sum, $sumCandidate, implode(' ', $equation));
                continue 2;
            }
        }

        if ($sumCandidate === $sum) {
            if (isset($correctEquations[$equationId])) {
                $correctEquations[$equationId]['equations'][] = $equation;
            } else {
                $correctEquations[$equationId] = [
                    'sum' => $sum,
                    'equations' => [$equation],
                ];
            }

            Console::v('> match: %s = %s', $sum, implode(' ', $equation));
        } elseif ($sumCandidate < $sum) {
            Console::vv('skip - too low: %s > %s = %s', $sum, $sumCandidate, implode(' ', $equation));
        }
    }
}

Console::l(
    'found %s / %s correct equations, total sum is %s',
    array_reduce($correctEquations, fn($carry, $equation) => $carry + count($equation['equations']), 0),
    count($equations),
    array_reduce($correctEquations, fn($carry, $equation) => $carry + $equation['sum'], "0"),
);
