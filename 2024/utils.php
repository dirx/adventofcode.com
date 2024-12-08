<?php

declare(strict_types=1);

class Console
{
    private static int $verbosity = 0;
    private static bool $test = false;
    private static float $started = 0.0;

    static function init(): void
    {
        $options = getopt('tv');
        if (isset($options['v'])) {
            self::$verbosity = $options['v'] === false ? 1 : count($options['v']);
        }
        self::$test = isset($options['t']);
        self::$started = microtime(true);
    }

    static function l(string $message): void
    {
        echo sprintf("%s\n", $message);
        self::v(
            sprintf(
                "time: %s ms, peak memory: %s mb",
                number_format((microtime(true) - self::$started) * 1_000, 4),
                number_format(memory_get_peak_usage(true) / 1024 / 1024, 4),
            ),
        );
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
