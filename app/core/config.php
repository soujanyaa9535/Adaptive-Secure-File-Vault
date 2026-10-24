<?php

namespace App\core;

class Config
{
    private static array $config = [];

    public static function load(string $filePath): void
    {
        if (!file_exists($filePath)) {
            throw new \Exception(".env file not found.");
        }

        $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        foreach ($lines as $line) {

            $line = trim($line);

            if ($line === '' || str_starts_with($line, '#')) {
                continue;
            }

            [$key, $value] = explode('=', $line, 2);

            self::$config[trim($key)] = trim($value);
        }
    }

    public static function get(string $key, $default = null)
    {
        return self::$config[$key] ?? $default;
    }
}