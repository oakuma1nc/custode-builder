<?php

declare(strict_types=1);

namespace Custode\Services;

use Custode\App;

final class MailService
{
    public static function send(string $to, string $subject, string $textBody, ?string $htmlBody = null): bool
    {
        $cfg = App::$config['mail'] ?? [];
        $driver = (string) ($cfg['driver'] ?? 'log');
        $from = (string) ($cfg['from'] ?? 'noreply@custode.digital');
        if ($to === '') {
            return false;
        }
        if ($driver === 'log') {
            $root = App::$config['paths']['root'];
            $path = $root . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'logs' . DIRECTORY_SEPARATOR . 'mail.log';
            $line = date('c') . " TO={$to} SUBJECT={$subject}\n" . $textBody . "\n---\n";
            return (bool) @file_put_contents($path, $line, FILE_APPEND | LOCK_EX);
        }
        if ($driver === 'mail') {
            $headers = [
                'From: ' . $from,
                'MIME-Version: 1.0',
                'Content-Type: ' . ($htmlBody ? 'text/html; charset=UTF-8' : 'text/plain; charset=UTF-8'),
            ];
            $body = $htmlBody ?? $textBody;
            return @mail($to, '=?UTF-8?B?' . base64_encode($subject) . '?=', $body, implode("\r\n", $headers));
        }
        return false;
    }

    public static function notifyAdmin(string $subject, string $textBody): void
    {
        $to = (string) (App::$config['mail']['admin_alert'] ?? '');
        if ($to === '') {
            return;
        }
        self::send($to, $subject, $textBody);
    }
}
