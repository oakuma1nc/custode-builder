<?php

declare(strict_types=1);

namespace Custode\Helpers;

final class Str
{
    public static function slug(string $text): string
    {
        $text = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $text) ?: $text;
        $text = strtolower((string) preg_replace('/[^a-zA-Z0-9]+/', '-', $text));
        $text = trim($text, '-');
        return $text !== '' ? $text : 'site';
    }

    public static function randomToken(int $bytes = 32): string
    {
        return bin2hex(random_bytes($bytes));
    }
}
