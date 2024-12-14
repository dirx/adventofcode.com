<?php

declare(strict_types=1);

class Console
{
    private static int $verbosity = 0;
    private static bool $test = false;
    private static float $started = 0.0;

    static function init(): void
    {
        $options = getopt('tvh');
        if (isset($options['h'])) {
            self::help();
        }
        if (isset($options['v'])) {
            self::$verbosity = $options['v'] === false ? 1 : count($options['v']);
        }
        self::$test = isset($options['t']);
        self::$started = microtime(true);
    }

    static function help(): void
    {
        echo sprintf(
            <<<TEXT
                -v  verbosity levels (eg. -vv)
                -t  run with test data
                -h  show this help
                TEXT,
        );
        echo PHP_EOL;
        exit();
    }

    static function l(string $message, string|int|float ...$vars): void
    {
        echo sprintf("$message\n", ...$vars);
        self::v(
            sprintf(
                "time: %s ms, peak memory: %s mb",
                number_format((microtime(true) - self::$started) * 1_000, 4),
                number_format(memory_get_peak_usage(true) / 1024 / 1024, 4),
            ),
        );
    }

    static function v(string $message, string|int|float ...$vars): void
    {
        if (self::$verbosity >= 1) {
            echo sprintf("  $message\n", ...$vars);
        }
    }

    static function vv(string $message, string|int|float ...$vars): void
    {
        if (self::$verbosity >= 2) {
            echo sprintf("    $message\n", ...$vars);
        }
    }

    static function vvv(string $message, string|int|float ...$vars): void
    {
        if (self::$verbosity >= 3) {
            echo sprintf("      $message\n", ...$vars);
        }
    }

    static function isTest(): bool
    {
        return self::$test;
    }
}

Console::init();
