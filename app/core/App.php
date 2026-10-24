<?php

namespace App\Core;

class App
{
    public static function run(): void
    {
        $envPath = dirname(__DIR__, 2) . '/config/.env';

        Config::load($envPath);

        Database::connect();
    }
}