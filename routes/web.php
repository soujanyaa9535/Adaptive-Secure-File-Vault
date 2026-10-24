<?php

use App\Core\Router;
use App\Controllers\AuthController;
use App\Controllers\DashboardController;
use App\Controllers\FileController;

$router = new Router();

// Root route (Smart Auth Redirect)
$router->get('/', [AuthController::class, 'home']);

// Authentication Routes
$router->get('/register', [AuthController::class, 'register']);
$router->post('/register', [AuthController::class, 'register']);

$router->get('/login', [AuthController::class, 'login']);
$router->post('/login', [AuthController::class, 'login']);
$router->get('/logout', [AuthController::class, 'logout']);

// Dashboard & Operations
$router->get('/dashboard', [DashboardController::class, 'index']);
$router->get('/admin', [DashboardController::class, 'adminPanel']);

// Secure File Pipeline
$router->get('/upload', [FileController::class, 'upload']);
$router->post('/upload', [FileController::class, 'upload']);
$router->get('/files', [FileController::class, 'myFiles']);
$router->get('/download', [FileController::class, 'download']);

// Account Security
$router->get('/account-security', [AuthController::class, 'accountSecurity']);

return $router;
