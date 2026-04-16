<?php

declare(strict_types=1);

namespace Custode\Helpers;

final class Locale
{
    public const ALLOWED = ['it', 'de', 'en', 'fr'];

    public const COOKIE = 'custode_lang';

    public static function resolve(): string
    {
        $fromGet = isset($_GET['lang']) ? strtolower((string) $_GET['lang']) : '';
        if (in_array($fromGet, self::ALLOWED, true)) {
            if (!headers_sent()) {
                setcookie(self::COOKIE, $fromGet, [
                    'expires' => time() + 365 * 86400,
                    'path' => '/',
                    'secure' => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
                    'httponly' => false,
                    'samesite' => 'Lax',
                ]);
            }
            return $fromGet;
        }
        $c = strtolower((string) ($_COOKIE[self::COOKIE] ?? ''));
        if (in_array($c, self::ALLOWED, true)) {
            return $c;
        }
        return 'it';
    }

    /**
     * @return array<string, mixed>
     */
    public static function landingStrings(?string $lang = null): array
    {
        $lang = $lang ?? self::resolve();
        if (!in_array($lang, self::ALLOWED, true)) {
            $lang = 'it';
        }
        $path = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'i18n' . DIRECTORY_SEPARATOR . 'landing.php';
        /** @var array<string, array<string, mixed>> $all */
        $all = require $path;
        return $all[$lang] ?? $all['it'];
    }

    public static function langHref(string $code): string
    {
        $code = strtolower($code);
        if (!in_array($code, self::ALLOWED, true)) {
            return '/';
        }
        return '/?lang=' . rawurlencode($code);
    }
}
