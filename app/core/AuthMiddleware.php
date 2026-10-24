<?php

namespace App\Core;

class AuthMiddleware
{
    public static function requireLogin(): void
    {
        Session::start();

        if (!Session::has('authenticated')) {
            header('Location: /adaptive-secure-file-vault/public/login');
            exit;
        }
    }

    public static function requireRole(int $requiredRoleId): void
    {
        self::requireLogin();

        $userRoleId = (int) Session::get('role_id');

        if ($userRoleId !== $requiredRoleId) {
            http_response_code(403);

            ?>
            <!DOCTYPE html>
            <html lang="en">
            <head>
                <meta charset="UTF-8">
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <title>Access Denied</title>

                <style>
                    body {
                        margin: 0;
                        font-family: Arial, sans-serif;
                        background: #f5f7fb;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        height: 100vh;
                    }

                    .box {
                        background: white;
                        padding: 40px;
                        border-radius: 12px;
                        text-align: center;
                        box-shadow: 0 4px 20px rgba(0,0,0,0.08);
                    }

                    h1 {
                        color: #dc2626;
                        margin-bottom: 10px;
                    }

                    p {
                        color: #6b7280;
                    }

                    a {
                        display: inline-block;
                        margin-top: 20px;
                        padding: 10px 18px;
                        background: #2563eb;
                        color: white;
                        text-decoration: none;
                        border-radius: 6px;
                    }
                </style>
            </head>

            <body>

                <div class="box">

                    <h1>403 - Access Denied</h1>

                    <p>
                        You do not have permission to access this resource.
                    </p>

                    <a href="/adaptive-secure-file-vault/public/dashboard">
                        Back to Dashboard
                    </a>

                </div>

            </body>
            </html>
            <?php

            exit;
        }
    }

    public static function requireAdmin(): void
    {
        self::requireRole(1);
    }

    public static function requireUser(): void
    {
        self::requireRole(2);
    }
}