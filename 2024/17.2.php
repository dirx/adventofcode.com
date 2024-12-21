<?php

declare(strict_types=1);

include(__DIR__ . "/utils.php");

/**
 * https://adventofcode.com/2024/day/17
 *
 * strategy: manual digging, it loops, it is always A << 3, output depends on B, B depends on A & 7 (with variants 0-7), test A = (A << 3) + variants until match of program & outout
 */

if (Console::isTest()) {
    $puzzle = <<<PUZZLE
        Register A: 2024
        Register B: 0
        Register C: 0
        
        Program: 0,3,5,4,3,0
        PUZZLE;
} else {
    $puzzle = file_get_contents(__DIR__ . '/17.txt');
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

$combo = function (int $operand) use (&$register): int {
    return match ($operand) {
        0, 1, 2, 3 => $operand,
        4 => $register['A'],
        5 => $register['B'],
        6 => $register['C'],
    };
};

$step = function (Opcode $instruction, int $operand, int &$pointer) use (&$register, &$output, &$combo, &$program): void {
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
};

$run = function (array $program, array $initRegister) use (&$register, &$step, &$output): void {
    $output = [];
    $register = $initRegister;
    $pointer = 0;
    $iterations = 0;
    $programLength = count($program);
    while (true) {
        $iterations++;
        $instruction = Opcode::from($program[$pointer++]);
        $operand = $program[$pointer++];

        $step($instruction, $operand, $pointer);

        Console::vv(
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
};

$findRegisterA = function (int $i, int $current = 0) use (&$findRegisterA, &$program, &$run, &$output, &$register): int {
    if ($i < 0) {
        return $current;
    }
    for ($variation = 0; $variation < 8; $variation++) {
        $next = ($current << 3) + $variation;
        $run($program, ['A' => $next, 'B' => 0, 'C' => 0]);
        if ($program[$i] === $output[0]) {
            Console::v(
                '%s: found match with %s, out: %s, program: %s',
                $i,
                $next,
                implode(',', $output),
                implode(',', $program),
            );
            $next = $findRegisterA($i - 1, $next);
            if ($next !== -1) {
                return $next;
            }
        }
    }

    return -1;
};

$registerA = $findRegisterA(count($program) - 1, 0);

if ($registerA === -1) {
    Console::l('no register A found');
} else {
    Console::l(
        'the output %s = program %s for register A %s',
        implode(',', $output),
        implode(',', $program),
        $registerA,
    );
}
