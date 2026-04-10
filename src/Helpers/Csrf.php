<?php

declare(strict_types=1);

namespace Custode\Helpers;

final class Csrf
{
    public static function token(): string
    {
        Auth::startSession();
        if (empty($_SESSION['_csrf'])) {
            $_SESSION['_csrf'] = bin2hex(random_bytes(32));
        }
        return (string) $_SESSION['_csrf'];
    }

    public static function validate(?string $token): bool
    {
        Auth::startSession();
        if ($token === null || $token === '') {
            return false;
        }
        return isset($_SESSION['_csrf']) && hash_equals((string) $_SESSION['_csrf'], $token);
    }

    public static function headerToken(): ?string
    {
        $h = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        return $h !== '' ? (string) $h : null;
    }
}
