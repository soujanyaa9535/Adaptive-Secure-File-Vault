<?php

namespace App\Controllers;

class AuthController
{
    public function register(): void
    {
        require __DIR__ . '/../views/auth/register.php';
    }

    public function login(): void
    {
        require __DIR__ . '/../views/auth/login.php';
    }

    public function home(): void
    {
        require __DIR__ . '/../views/home/index.php';
    }
}