<?php

declare(strict_types=1);

namespace Custode\Controllers;

use Custode\App;
use Custode\Helpers\Csrf;
use Custode\Helpers\Database;
use Custode\Helpers\RateLimiter;
use Custode\Helpers\Response;
use Custode\Helpers\Str;
use Custode\Models\Client;
use Custode\Models\Site;
use Custode\Services\ClaudeService;
use Custode\Services\MailService;
use Throwable;

final class BriefController
{
    public function showForm(): void
    {
        Response::redirect('/start');
    }

    public function submit(): void
    {
        if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
            Response::json(['error' => 'Method not allowed'], 405);
            return;
        }

        $raw = file_get_contents('php://input') ?: '';
        $json = json_decode($raw, true);
        if (!is_array($json)) {
            $json = $_POST;
        }

        $csrfTok = (string) ($json['csrf_token'] ?? '');
        if (!Csrf::validate($csrfTok)) {
            Response::json(['error' => 'Invalid security token. Please refresh the page.'], 419);
            return;
        }

        $maxBrief = (int) (App::$config['rate_limit']['brief_per_hour'] ?? 30);
        if (RateLimiter::tooMany('brief', $maxBrief, 3600)) {
            Response::json(['error' => 'Too many brief submissions from this network. Try again later.'], 429);
            return;
        }

        $name = trim((string) ($json['name'] ?? ''));
        $email = trim((string) ($json['email'] ?? ''));
        $phone = trim((string) ($json['phone'] ?? ''));
        $businessName = trim((string) ($json['business_name'] ?? ''));
        $businessType = trim((string) ($json['business_type'] ?? 'restaurant'));

        $allowedTypes = ['restaurant', 'cafe', 'bar', 'bakery', 'hotel', 'retail', 'service', 'other'];
        if (!in_array($businessType, $allowedTypes, true)) {
            $businessType = 'restaurant';
        }

        if ($name === '' || $businessName === '') {
            Response::json(['error' => 'Name and business name are required.'], 422);
            return;
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            Response::json(['error' => 'A valid email is required.'], 422);
            return;
        }

        $briefPayload = [
            'tagline' => trim((string) ($json['tagline'] ?? '')),
            'description' => trim((string) ($json['description'] ?? '')),
            'highlights' => trim((string) ($json['highlights'] ?? '')),
            'menu_or_services' => trim((string) ($json['menu_or_services'] ?? '')),
            'address' => trim((string) ($json['address'] ?? '')),
            'hours' => trim((string) ($json['hours'] ?? '')),
            'cta' => trim((string) ($json['cta'] ?? '')),
            'notes' => trim((string) ($json['notes'] ?? '')),
        ];

        $pdo = Database::pdo();
        try {
            $pdo->beginTransaction();
            $clientId = Client::create([
                'name' => $name,
                'email' => $email,
                'phone' => $phone !== '' ? $phone : null,
                'business_name' => $businessName,
                'business_type' => $businessType,
            ]);
            $slug = Site::uniqueSlug($businessName);
            $token = Str::randomToken(32);
            $siteId = Site::create([
                'client_id' => $clientId,
                'slug' => $slug,
                'preview_token' => $token,
                'status' => 'generating',
                'brief_json' => json_encode($briefPayload, JSON_UNESCAPED_UNICODE),
            ]);
            $pdo->commit();
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            Response::json(['error' => 'Could not save your brief. Please try again.'], 500);
            return;
        }

        $gen = new ClaudeService();
        try {
            $ok = $gen->generateForSite($siteId);
        } catch (Throwable) {
            $ok = false;
        }

        $site = Site::find($siteId);
        $previewPath = '/preview/' . rawurlencode($token);
        $payloadBase = [
            'site_id' => $siteId,
            'preview_token' => $token,
            'preview_url' => $previewPath,
        ];

        if (!$ok || $site === null || ($site['status'] ?? '') !== 'preview') {
            $st = (string) ($site['status'] ?? 'generating');
            if ($st === 'failed') {
                MailService::notifyAdmin(
                    'Custode: AI generation failed',
                    "Site #{$siteId} ({$businessName}). Error: " . (string) ($site['generation_error'] ?? 'unknown')
                );
            }
            Response::json(array_merge($payloadBase, [
                'ok' => false,
                'status' => $st,
                'message' => $st === 'failed'
                    ? 'Generation failed. You can share this with support or ask an admin to retry from the dashboard.'
                    : 'We received your brief, but the preview is not ready yet. Keep this page open or check back shortly.',
            ]), 202);
            return;
        }

        Response::json(array_merge($payloadBase, [
            'ok' => true,
        ]));
    }
}
