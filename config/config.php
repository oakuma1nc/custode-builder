<?php

declare(strict_types=1);

/**
 * Application configuration. All secrets come from the environment (or .env).
 * Never commit real credentials.
 */

(function (): void {
    $root = dirname(__DIR__);
    $envFile = $root . DIRECTORY_SEPARATOR . '.env';
    if (!is_readable($envFile)) {
        return;
    }
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if ($lines === false) {
        return;
    }
    foreach ($lines as $line) {
        $trimmed = ltrim($line);
        if ($trimmed === '' || str_starts_with($trimmed, '#')) {
            continue;
        }
        if (!str_contains($line, '=')) {
            continue;
        }
        [$name, $value] = explode('=', $line, 2);
        $name = trim($name);
        $value = trim($value);
        if ($name === '') {
            continue;
        }
        if (
            (str_starts_with($value, '"') && str_ends_with($value, '"'))
            || (str_starts_with($value, "'") && str_ends_with($value, "'"))
        ) {
            $value = substr($value, 1, -1);
        }
        putenv($name . '=' . $value);
        $_ENV[$name] = $value;
        $_SERVER[$name] = $value;
    }
})();

$root = dirname(__DIR__);

return [
    'app' => [
        'env' => getenv('APP_ENV') ?: 'production',
        'debug' => filter_var(getenv('APP_DEBUG') ?: 'false', FILTER_VALIDATE_BOOLEAN),
        'url' => rtrim(getenv('APP_URL') ?: '', '/') ?: '',
        'timezone' => getenv('APP_TIMEZONE') ?: 'UTC',
    ],
    'db' => [
        'host' => getenv('DB_HOST') ?: '127.0.0.1',
        'port' => (int) (getenv('DB_PORT') ?: '3306'),
        'name' => getenv('DB_NAME') ?: 'custode_builder',
        'user' => getenv('DB_USER') ?: '',
        'pass' => getenv('DB_PASS') ?: '',
        'charset' => 'utf8mb4',
    ],
    'anthropic' => [
        'api_key' => getenv('ANTHROPIC_API_KEY') ?: '',
        'model' => getenv('ANTHROPIC_MODEL') ?: 'claude-sonnet-4-6',
    ],
    'stripe' => [
        'secret_key' => getenv('STRIPE_SECRET_KEY') ?: '',
        'webhook_secret' => getenv('STRIPE_WEBHOOK_SECRET') ?: '',
        'setup_amount_cents' => (int) (getenv('STRIPE_SETUP_AMOUNT_CENTS') ?: '14900'),
        'currency' => getenv('STRIPE_CURRENCY') ?: 'chf',
        'monthly_price_id' => getenv('STRIPE_MONTHLY_PRICE_ID') ?: '',
        'monthly_label_cents' => (int) (getenv('STRIPE_MONTHLY_LABEL_CENTS') ?: '4900'),
    ],
    'cpanel' => [
        'host' => getenv('CPANEL_HOST') ?: '',
        'user' => getenv('CPANEL_USER') ?: '',
        'token' => getenv('CPANEL_TOKEN') ?: '',
        'base_domain' => getenv('CPANEL_BASE_DOMAIN') ?: 'custode.digital',
        'enable_subdomain' => filter_var(getenv('CPANEL_ENABLE_SUBDOMAIN') ?: 'false', FILTER_VALIDATE_BOOLEAN),
    ],
    'mail' => [
        'driver' => getenv('MAIL_DRIVER') ?: 'log',
        'from' => getenv('MAIL_FROM') ?: 'noreply@custode.digital',
        'admin_alert' => getenv('MAIL_ADMIN_ALERT') ?: '',
    ],
    'rate_limit' => [
        'brief_per_hour' => (int) (getenv('RATE_LIMIT_BRIEF_PER_HOUR') ?: '30'),
        'checkout_per_hour' => (int) (getenv('RATE_LIMIT_CHECKOUT_PER_HOUR') ?: '40'),
        'admin_api_per_minute' => (int) (getenv('RATE_LIMIT_ADMIN_API_PER_MINUTE') ?: '120'),
    ],
    'admin' => [
        'session_name' => getenv('ADMIN_SESSION_NAME') ?: 'custode_admin',
        'user' => getenv('ADMIN_USER') ?: '',
        'password_hash' => getenv('ADMIN_PASSWORD_HASH') ?: '',
    ],
    'paths' => [
        'root' => $root,
        'log_file' => $root . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'logs' . DIRECTORY_SEPARATOR . 'app.log',
    ],
];
