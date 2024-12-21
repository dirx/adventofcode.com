<?php

declare(strict_types=1);

include(__DIR__ . "/utils.php");

/**
 * https://adventofcode.com/2024/day/17
 *
 * strategy: use bitwise operators
 */

if (Console::isTest()) {
    $puzzle = <<<PUZZLE
        Register A: 729
        Register B: 0
        Register C: 0
        
        Program: 0,1,5,4,3,0
        PUZZLE;
} else {
    $puzzle = file_get_contents(__DIR__ . '/17.txt');
}

$register = [];
$program = [];
preg_match_all(
    '/(Register) ([ABC]): (\d+)|(Program): ([\d,]+)/s',
    $puzzle,
    $matches,
    PREG_SET_ORDER,
);
foreach ($matches as $match) {
    if ($match[1] === 'Register') {
        $register[$match[2]] = intval($match[3]);
    } elseif ($match[4] === 'Program') {
        $program = array_map('intval', explode(',', $match[5]));
    }
}


enum Opcode: int
{
    case adv = 0;
    case bxl = 1;
    case bst = 2;
    case jnz = 3;
    case bxc = 4;
    case out = 5;
    case bdv = 6;
    case cdv = 7;
}

$combo = function (int $operand) use (&$register): int {
    return match ($operand) {
        0, 1, 2, 3 => $operand,
        4 => $register['A'],
        5 => $register['B'],
        6 => $register['C'],
    };
};

$output = [];
$pointer = 0;
$iterations = 0;
$programLength = count($program);
while (true) {
    $iterations++;

    $instruction = Opcode::from($program[$pointer++]);
    $operand = $program[$pointer++];

    switch ($instruction) {
        case Opcode::adv:
            $register['A'] = $register['A'] >> $combo($operand);
            break;
        case Opcode::bxl:
            $register['B'] ^= $operand;
            break;
        case Opcode::bst:
            $register['B'] = $combo($operand) & 7;
            break;
        case Opcode::jnz:
            $pointer = $register['A'] === 0 ? $pointer : $operand;
            break;
        case Opcode::bxc:
            $register['B'] ^= $register['C'];
            break;
        case Opcode::out:
            $output[] = $combo($operand) & 7;
            break;
        case Opcode::bdv:
            $register['B'] = $register['A'] >> $combo($operand);
            break;
        case Opcode::cdv:
            $register['C'] = $register['A'] >> $combo($operand);
            break;
    }
    Console::v(
        '%s, p: %s, i: %s %s:, o: %s, r: A=%s B=%s C=%s, out: %s',
        $iterations,
        $pointer,
        $instruction->value,
        $instruction->name,
        $operand,
        $register['A'],
        $register['B'],
        $register['C'],
        implode(',', $output),
    );

    if ($pointer >= $programLength) {
        break;
    }
}

Console::l('the output is: %s (after %s iterations) for program %s', implode(',', $output), $iterations, implode(',', $program));
