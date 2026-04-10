<?php

declare(strict_types=1);

namespace Custode\Helpers;

use Custode\App;

final class RateLimiter
{
    /**
     * @return true if request should be blocked (over limit)
     */
    public static function tooMany(string $bucket, int $maxPerWindow, int $windowSeconds): bool
    {
        if ($maxPerWindow < 1 || $windowSeconds < 1) {
            return false;
        }
        $root = App::$config['paths']['root'];
        $dir = $root . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'ratelimit';
        if (!is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }
        $ip = (string) ($_SERVER['REMOTE_ADDR'] ?? '0');
        $key = preg_replace('/[^a-zA-Z0-9_-]/', '_', $bucket . '_' . $ip);
        $file = $dir . DIRECTORY_SEPARATOR . $key . '.json';
        $now = time();
        $state = ['t' => $now, 'n' => 0];
        if (is_readable($file)) {
            $raw = file_get_contents($file);
            $prev = is_string($raw) ? json_decode($raw, true) : null;
            if (is_array($prev) && isset($prev['t'], $prev['n'])) {
                if ($now - (int) $prev['t'] <= $windowSeconds) {
                    $state = ['t' => (int) $prev['t'], 'n' => (int) $prev['n']];
                }
            }
        }
        $state['n']++;
        @file_put_contents($file, json_encode($state), LOCK_EX);
        return $state['n'] > $maxPerWindow;
    }
}
