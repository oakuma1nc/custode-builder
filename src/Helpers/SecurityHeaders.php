<?php

declare(strict_types=1);

namespace Custode\Helpers;

final class SecurityHeaders
{
    public static function send(): void
    {
        if (headers_sent()) {
            return;
        }
        header('X-Content-Type-Options: nosniff');
        header('X-Frame-Options: SAMEORIGIN');
        header('Referrer-Policy: strict-origin-when-cross-origin');
        header('Permissions-Policy: accelerometer=(), camera=(), geolocation=(), gyroscope=(), magnetometer=(), microphone=(), payment=(), usb=()');
        header('Cross-Origin-Resource-Policy: same-site');
    }
}
