<?php

declare(strict_types=1);

namespace Custode\Helpers;

final class Response
{
    /**
     * @param array<string, mixed> $data
     */
    public static function json(array $data, int $code = 200): void
    {
        if (!headers_sent()) {
            http_response_code($code);
            header('Content-Type: application/json; charset=utf-8');
        }
        echo json_encode($data, JSON_THROW_ON_ERROR);
    }

    public static function redirect(string $url, int $code = 302): void
    {
        if (!headers_sent()) {
            http_response_code($code);
            header('Location: ' . $url);
        }
        exit;
    }

    public static function html(string $html, int $code = 200): void
    {
        if (!headers_sent()) {
            http_response_code($code);
            header('Content-Type: text/html; charset=utf-8');
        }
        echo $html;
    }
}
