<?php

require_once __DIR__ . '/../vendor/autoload.php';

use App\Core\App;

try {

    App::run();

    $router = require __DIR__ . '/../routes/web.php';

    $requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

    $basePath = '/adaptive-secure-file-vault/public';

    $path = preg_replace(
        '#^' . preg_quote($basePath, '#') . '#i',
        '',
        $requestUri
    );

    if ($path === '' || $path === false) {
        $path = '/';
    }

    $method = $_SERVER['REQUEST_METHOD'];

    $router->dispatch($path, $method);

} catch (Throwable $e) {

    http_response_code(500);

    echo '<h2>Application Error</h2>';
    echo '<p>' . htmlspecialchars($e->getMessage()) . '</p>';
}