<?php

declare(strict_types=1);

namespace Custode\Helpers;

use RuntimeException;

/**
 * Regex-based router: matches METHOD + path, extracts {param} values in order, dispatches to Controller@action.
 */
final class Router
{
    /** @var list<array{method: string, pattern: string, handler: string}> */
    private array $routes = [];

    /**
     * @param list<array{0: string, 1: string, 2: string}> $definitions
     */
    public function __construct(array $definitions = [])
    {
        foreach ($definitions as $row) {
            if (count($row) !== 3) {
                continue;
            }
            $this->addRoute($row[0], $row[1], $row[2]);
        }
    }

    public function addRoute(string $method, string $pattern, string $handler): void
    {
        $method = strtoupper(trim($method));
        $pattern = trim($pattern);
        if ($pattern === '' || $pattern === '/') {
            $pattern = '/';
        } else {
            $pattern = '/' . trim($pattern, '/');
        }
        $this->routes[] = [
            'method' => $method,
            'pattern' => $pattern,
            'handler' => $handler,
        ];
    }

    public function dispatch(string $method, string $uri): void
    {
        $method = strtoupper($method);
        $path = $this->normalizePath($uri);

        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) {
                continue;
            }
            $regex = $this->patternToRegex($route['pattern']);
            if (!preg_match($regex, $path, $matches)) {
                continue;
            }
            $args = $this->orderedParams($route['pattern'], $matches);
            $this->invoke($route['handler'], $args);
            return;
        }

        http_response_code(404);
        header('Content-Type: text/html; charset=utf-8');
        echo '<!DOCTYPE html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">'
            . '<title>Not found — Custode</title>'
            . '<link rel="stylesheet" href="/public/css/custode-app-shell.css">'
            . '</head><body class="cas-body">'
            . '<div class="cas-page-center"><div class="cas-page-inner">'
            . '<h1 class="cas-title cas-title--center">Page not found</h1>'
            . '<p class="cas-subtitle cas-subtitle--mb" style="max-width:22rem;margin-left:auto;margin-right:auto;">'
            . '<a class="cas-link" href="/">Back to home</a></p>'
            . '</div></div></body></html>';
    }

    private function normalizePath(string $uri): string
    {
        $path = parse_url($uri, PHP_URL_PATH);
        if (!is_string($path) || $path === '') {
            $path = '/';
        }
        $path = '/' . trim($path, '/');
        if ($path !== '/') {
            $path = rtrim($path, '/') ?: '/';
        }

        $scriptName = $_SERVER['SCRIPT_NAME'] ?? '/index.php';
        $base = dirname($scriptName);
        if ($base !== '/' && $base !== '\\' && $base !== '.') {
            $base = '/' . trim($base, '/');
            if (str_starts_with($path, $base)) {
                $path = substr($path, strlen($base)) ?: '/';
            }
        }

        $path = '/' . trim($path, '/');
        return $path === '//' ? '/' : ($path === '' ? '/' : $path);
    }

    private function patternToRegex(string $pattern): string
    {
        if ($pattern === '/') {
            return '#^/$#';
        }
        $inner = trim($pattern, '/');
        $segments = $inner === '' ? [] : explode('/', $inner);
        $parts = [];
        foreach ($segments as $segment) {
            if (preg_match('#^\{([a-zA-Z_][a-zA-Z0-9_]*)\}$#', $segment, $m)) {
                $parts[] = '(?P<' . $m[1] . '>[^/]+)';
            } else {
                $parts[] = preg_quote($segment, '#');
            }
        }
        return '#^/' . implode('/', $parts) . '$#';
    }

    /**
     * @param array<string, string|int> $matches
     * @return list<string>
     */
    private function orderedParams(string $pattern, array $matches): array
    {
        if (!preg_match_all('#\{([a-zA-Z_][a-zA-Z0-9_]*)\}#', $pattern, $names)) {
            return [];
        }
        $out = [];
        foreach ($names[1] as $name) {
            $out[] = isset($matches[$name]) ? (string) $matches[$name] : '';
        }
        return $out;
    }

    /**
     * @param list<string> $args
     */
    private function invoke(string $handler, array $args): void
    {
        if (!str_contains($handler, '@')) {
            throw new RuntimeException('Invalid route handler: ' . $handler);
        }
        [$classShort, $action] = explode('@', $handler, 2);
        $class = 'Custode\\Controllers\\' . $classShort;
        if (!class_exists($class)) {
            throw new RuntimeException('Controller not found: ' . $class);
        }
        $controller = new $class();
        if (!method_exists($controller, $action)) {
            throw new RuntimeException('Action not found: ' . $class . '::' . $action);
        }
        $controller->{$action}(...$args);
    }
}
