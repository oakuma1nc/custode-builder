<?php

declare(strict_types=1);

namespace Custode\Helpers;

use Custode\App;

final class Auth
{
    private static bool $started = false;

    public static function startSession(): void
    {
        if (self::$started) {
            return;
        }
        $name = App::$config['admin']['session_name'] ?? 'custode_admin';
        session_name(preg_replace('/[^a-zA-Z0-9_]/', '', $name) ?: 'custode_admin');
        $secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
        session_set_cookie_params([
            'lifetime' => 0,
            'path' => '/',
            'secure' => $secure,
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
        session_start();
        self::$started = true;
    }

    public static function isAdmin(): bool
    {
        self::startSession();
        return !empty($_SESSION['custode_admin']);
    }

    public static function requireAdmin(): void
    {
        if (!self::isAdmin()) {
            Response::json(['error' => 'Unauthorized'], 401);
            exit;
        }
    }

    /**
     * @param array<string, mixed> $creds username + password from POST
     */
    public static function attemptAdminLogin(string $username, string $password): bool
    {
        $cfg = App::$config['admin'];
        $expectedUser = (string) ($cfg['user'] ?? '');
        $hash = (string) ($cfg['password_hash'] ?? '');
        if ($expectedUser === '' || $hash === '') {
            return false;
        }
        if (!hash_equals($expectedUser, $username)) {
            return false;
        }
        if (!password_verify($password, $hash)) {
            return false;
        }
        self::startSession();
        session_regenerate_id(true);
        $_SESSION['custode_admin'] = true;
        return true;
    }

    public static function logout(): void
    {
        self::startSession();
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $p = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], (bool) $p['secure'], (bool) $p['httponly']);
        }
        session_destroy();
        self::$started = false;
    }

    public static function grantEditorAccess(int $siteId): void
    {
        self::startSession();
        $_SESSION['editor_sites'] ??= [];
        $_SESSION['editor_sites'][$siteId] = true;
    }

    public static function canEditSite(int $siteId): bool
    {
        self::startSession();
        if (!empty($_SESSION['custode_admin'])) {
            return true;
        }
        return !empty($_SESSION['editor_sites'][$siteId]);
    }
}
