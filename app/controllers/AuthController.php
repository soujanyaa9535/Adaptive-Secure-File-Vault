<?php

namespace App\Controllers;

use App\Core\Database;
use App\Core\Session;
use App\Core\AuditLogger;

class AuthController
{
    public function showLogin(): void
    {
        Session::start();
        if (Session::has('authenticated')) {
            header('Location: /adaptive-secure-file-vault/public/dashboard');
            exit;
        }

        if (file_exists(__DIR__ . '/../views/auth/login.php')) {
            require __DIR__ . '/../views/auth/login.php';
        } else {
            require __DIR__ . '/../views/login.php';
        }
    }

    public function login(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $this->showLogin();
            return;
        }

        Session::start();
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($email) || empty($password)) {
            header('Location: /adaptive-secure-file-vault/public/login?error=invalid');
            exit;
        }

        try {
            $db = Database::connect();
            $stmt = $db->prepare("SELECT * FROM users WHERE email = :email LIMIT 1");
            $stmt->execute([':email' => $email]);
            $user = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$user) {
                if (class_exists('App\Services\AuditLogger')) {
                    \App\Services\AuditLogger::log('LOGIN', 'user', null, 'failure', 'Login failed: invalid email or password.');
                }
                header('Location: /adaptive-secure-file-vault/public/login?error=invalid');
                exit;
            }

            if (!password_verify($password, $user['password'])) {
                if (class_exists('App\Services\AuditLogger')) {
                    \App\Services\AuditLogger::log('LOGIN', 'user', (int) $user['id'], 'failure', 'Login failed: invalid email or password.');
                }
                header('Location: /adaptive-secure-file-vault/public/login?error=invalid');
                exit;
            }

            // 1. Pass AuthMiddleware::requireLogin() guard
            Session::set('authenticated', true);
            Session::set('user_id', (int) $user['id']);
            Session::set('full_name', $user['full_name']);
            Session::set('role_id', (int) ($user['role_id'] ?? 2));
            Session::set('user', $user);

            // 2. Also populate native $_SESSION superglobal for compatibility
            $_SESSION['authenticated'] = true;
            $_SESSION['user_id'] = (int) $user['id'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['role_id'] = (int) ($user['role_id'] ?? 2);
            $_SESSION['user'] = $user;

            if (class_exists('App\Services\AuditLogger')) {
                \App\Services\AuditLogger::log('LOGIN', 'user', (int) $user['id'], 'success', 'User logged in successfully.');
            }

            header('Location: /adaptive-secure-file-vault/public/dashboard');
            exit;

        } catch (\Exception $e) {
            header('Location: /adaptive-secure-file-vault/public/login?error=invalid');
            exit;
        }
    }

    public function showRegister(): void
    {
        Session::start();
        if (file_exists(__DIR__ . '/../views/auth/register.php')) {
            require __DIR__ . '/../views/auth/register.php';
        } else {
            require __DIR__ . '/../views/register.php';
        }
    }

    public function register(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $this->showRegister();
            return;
        }

        Session::start();
        $fullName = trim($_POST['full_name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            header('Location: /adaptive-secure-file-vault/public/register?error=invalid_email');
            exit;
        }

        if (strlen($password) < 6) {
            header('Location: /adaptive-secure-file-vault/public/register?error=weak_password');
            exit;
        }

        try {
            $db = Database::connect();
            $stmt = $db->prepare("SELECT id FROM users WHERE email = :email LIMIT 1");
            $stmt->execute([':email' => $email]);
            if ($stmt->fetch()) {
                header('Location: /adaptive-secure-file-vault/public/register?error=email_exists');
                exit;
            }

            $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
            $insertStmt = $db->prepare("INSERT INTO users (full_name, email, password, role_id) VALUES (:name, :email, :password, 2)");
            $insertStmt->execute([
                ':name' => $fullName,
                ':email' => $email,
                ':password' => $hashedPassword
            ]);
            $newUserId = (int) $db->lastInsertId();

            if (class_exists('App\Services\AuditLogger')) {
                \App\Services\AuditLogger::log('REGISTER', 'user', $newUserId, 'success', 'User registered new account.');
            }

            header('Location: /adaptive-secure-file-vault/public/login?success=registered');
            exit;

        } catch (\Exception $e) {
            header('Location: /adaptive-secure-file-vault/public/register?error=server_error');
            exit;
        }
    }

    public function logout(): void
    {
        Session::start();
        $userId = Session::get('user_id');
        if ($userId && class_exists('App\Services\AuditLogger')) {
            \App\Services\AuditLogger::log('LOGOUT', 'user', (int) $userId, 'success', 'User logged out.');
        }
        Session::destroy();
        header('Location: /adaptive-secure-file-vault/public/login');
        exit;
    }

    public function accountSecurity(): void
    {
        Session::start();
        if (!Session::has('authenticated')) {
            header('Location: /adaptive-secure-file-vault/public/login');
            exit;
        }

        $userId = (int) Session::get('user_id');
        $db = Database::connect();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $currentPassword = $_POST['current_password'] ?? '';
            $newPassword = $_POST['new_password'] ?? '';
            $confirmPassword = $_POST['confirm_password'] ?? '';

            if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
                header('Location: /adaptive-secure-file-vault/public/account-security?error=missing_fields');
                exit;
            }

            if ($newPassword !== $confirmPassword) {
                header('Location: /adaptive-secure-file-vault/public/account-security?error=mismatch');
                exit;
            }

            if (strlen($newPassword) < 6) {
                header('Location: /adaptive-secure-file-vault/public/account-security?error=weak');
                exit;
            }

            $stmt = $db->prepare("SELECT password FROM users WHERE id = :id LIMIT 1");
            $stmt->execute([':id' => $userId]);
            $user = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$user || !password_verify($currentPassword, $user['password'])) {
                header('Location: /adaptive-secure-file-vault/public/account-security?error=invalid_current');
                exit;
            }

            $hashed = password_hash($newPassword, PASSWORD_BCRYPT);
            $upStmt = $db->prepare("UPDATE users SET password = :password WHERE id = :id");
            $upStmt->execute([':password' => $hashed, ':id' => $userId]);

            if (class_exists('App\Services\AuditLogger')) {
                \App\Services\AuditLogger::log('PASSWORD_UPDATE', 'user', $userId, 'success', 'User updated account passphrase.');
            }

            header('Location: /adaptive-secure-file-vault/public/account-security?success=updated');
            exit;
        }

        // Fetch user data for display
        $stmt = $db->prepare("SELECT id, full_name, email, role_id, created_at FROM users WHERE id = :id LIMIT 1");
        $stmt->execute([':id' => $userId]);
        $userData = $stmt->fetch(\PDO::FETCH_ASSOC) ?: [
            'id' => $userId,
            'full_name' => Session::get('full_name') ?? 'Operator',
            'email' => 'N/A',
            'role_id' => Session::get('role_id') ?? 2,
            'created_at' => date('Y-m-d H:i:s')
        ];

        $viewFile = __DIR__ . '/../views/auth/account_security.php';
        if (!file_exists($viewFile)) {
            $viewFile = __DIR__ . '/../views/account_security.php';
        }
        require $viewFile;
    }


    public function home(): void
    {
        if (isset($_SESSION['user_id'])) {
            header('Location: /adaptive-secure-file-vault/public/dashboard');
            exit;
        }
        require __DIR__ . '/../views/auth/login.php';
    }

}
