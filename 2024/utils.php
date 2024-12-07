<?php

declare(strict_types=1);

class Console
{
    private static int $verbosity = 0;
    private static bool $test = false;

    static function init(): void
    {
        $options = getopt('tv');
        if (isset($options['v'])) {
            self::$verbosity = $options['v'] === false ? 1 : count($options['v']);
        }
        self::$test = isset($options['t']);
    }

    static function l(string $message): void
    {
        echo sprintf("%s\n", $message);
    }

    static function v(string $message): void
    {
        if (self::$verbosity >= 1) {
            echo sprintf("  %s\n", $message);
        }
    }

    static function vv(string $message): void
    {
        if (self::$verbosity >= 2) {
            echo sprintf("    %s\n", $message);
        }
    }

    static function vvv(string $message): void
    {
        if (self::$verbosity >= 3) {
            echo sprintf("      %s\n", $message);
        }
    }

    static function isTest(): bool
    {
        return self::$test;
    }
}

Console::init();
