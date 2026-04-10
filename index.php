<?php

declare(strict_types=1);

/**
 * PHP built-in server (e.g. php -S 127.0.0.1:8080 index.php) uses this file as a router.
 * Without returning false for static assets, every request hits the app and /public/*.css 404s.
 * Apache/nginx with .htaccess “serve existing files” does not need this branch.
 */
if (PHP_SAPI === 'cli-server') {
    $path = parse_url((string) ($_SERVER['REQUEST_URI'] ?? '/'), PHP_URL_PATH);
    if (is_string($path) && str_starts_with($path, '/public/') && !str_contains($path, '..')) {
        $file = __DIR__ . $path;
        if (is_file($file)) {
            return false;
        }
    }
}

/**
 * Custode Builder — front controller
 */

$config = require __DIR__ . '/config/config.php';

if (is_file(__DIR__ . '/vendor/autoload.php')) {
    require_once __DIR__ . '/vendor/autoload.php';
}

$logFile = $config['paths']['log_file'] ?? (__DIR__ . '/storage/logs/app.log');
$logDir = dirname($logFile);
if (!is_dir($logDir)) {
    @mkdir($logDir, 0755, true);
}

$writeLog = static function (string $message) use ($logFile): void {
    $line = date('c') . ' ' . $message . PHP_EOL;
    @file_put_contents($logFile, $line, FILE_APPEND | LOCK_EX);
};

set_exception_handler(static function (\Throwable $e) use ($writeLog, $config): void {
    $writeLog('Exception: ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
    $writeLog($e->getTraceAsString());
    if (!headers_sent()) {
        http_response_code(500);
        header('Content-Type: text/html; charset=utf-8');
    }
    $debug = !empty($config['app']['debug']);
    echo '<!DOCTYPE html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">';
    echo '<title>Error — Custode</title><link rel="stylesheet" href="/public/css/custode-app-shell.css"></head><body class="cas-body">';
    echo '<div class="cas-page-center"><div class="cas-page-inner">';
    echo '<h1 class="cas-title cas-title--center">Something went wrong</h1>';
    echo '<p class="cas-subtitle cas-subtitle--mb" style="max-width:24rem;margin-left:auto;margin-right:auto;">An error occurred. Please try again later.</p>';
    if ($debug) {
        echo '<pre class="cas-code-block" style="max-width:36rem;margin-left:auto;margin-right:auto;text-align:left;">'
            . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8') . '</pre>';
    }
    echo '<a class="cas-link" href="/">Home</a></div></div></body></html>';
});

$tz = $config['app']['timezone'] ?? 'UTC';
try {
    date_default_timezone_set($tz);
} catch (\Throwable) {
    date_default_timezone_set('UTC');
}

spl_autoload_register(static function (string $class): void {
    $prefix = 'Custode\\';
    if (!str_starts_with($class, $prefix)) {
        return;
    }
    $relative = substr($class, strlen($prefix));
    $file = __DIR__ . '/src/' . str_replace('\\', '/', $relative) . '.php';
    if (is_file($file)) {
        require $file;
    }
});

\Custode\App::$config = $config;

\Custode\Helpers\SecurityHeaders::send();

/** @var list<array{0: string, 1: string, 2: string}> $routeDefs */
$routeDefs = require __DIR__ . '/config/routes.php';

use Custode\Helpers\Router;

$router = new Router($routeDefs);
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$uri = $_SERVER['REQUEST_URI'] ?? '/';

$router->dispatch($method, $uri);
