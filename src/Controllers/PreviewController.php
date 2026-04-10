<?php

declare(strict_types=1);

namespace Custode\Controllers;

use Custode\Helpers\Auth;
use Custode\Helpers\HtmlShell;
use Custode\Helpers\Locale;
use Custode\Helpers\Response;
use Custode\Helpers\View;
use Custode\Models\Site;
use Throwable;

final class PreviewController
{
    public function show(string $token): void
    {
        $site = Site::findByPreviewToken($token);
        if ($site === null) {
            Response::html(HtmlShell::centeredPage('Preview — Custode', 'Preview not found', 'This link may be invalid or expired.', '/start', 'Open wizard'), 404);
            return;
        }

        $status = (string) ($site['status'] ?? '');
        if (in_array($status, ['paid', 'editing', 'deployed', 'live'], true)) {
            Auth::startSession();
            if (Auth::canEditSite((int) $site['id'])) {
                Response::redirect('/editor/' . (int) $site['id']);
                return;
            }
            $live = (string) ($site['live_url'] ?? '');
            if ($live !== '') {
                Response::redirect($live);
                return;
            }
            Response::redirect('/editor/' . (int) $site['id']);
            return;
        }

        if ($status === 'generating') {
            View::render('preview-pending', ['title' => 'Generating… — Custode'], null);
            return;
        }

        if ($status === 'failed') {
            View::render('preview-failed', [
                'title' => 'Generation issue — Custode',
                'site' => $site,
                'token' => $token,
            ], null);
            return;
        }

        $html = (string) ($site['html_content'] ?? '');
        if ($html === '') {
            View::render('preview-pending', ['title' => 'Preview — Custode'], null);
            return;
        }

        $pvStrings = Locale::landingStrings();
        $pv = $pvStrings['preview_paywall'] ?? [];
        View::render('preview', [
            'title' => (string) ($pv['html_title'] ?? 'Preview — Custode'),
            'site' => $site,
            'token' => $token,
            'locale' => Locale::resolve(),
            'pv' => is_array($pv) ? $pv : [],
        ], null);
    }

    /**
     * Sandboxed iframe document: HTML only, no layout chrome.
     */
    public function frame(string $token): void
    {
        $site = Site::findByPreviewToken($token);
        if ($site === null) {
            http_response_code(404);
            header('Content-Type: text/html; charset=utf-8');
            echo '<!DOCTYPE html><html><head><meta charset="utf-8"><style>body{margin:0;font-family:system-ui,sans-serif;background:#0a0a0a;color:#999;padding:1rem;font-size:14px;}</style></head><body>Not found</body></html>';
            return;
        }
        if (($site['status'] ?? '') !== 'preview') {
            http_response_code(403);
            header('Content-Type: text/html; charset=utf-8');
            echo '<!DOCTYPE html><html><head><meta charset="utf-8"><style>body{margin:0;font-family:system-ui,sans-serif;background:#0a0a0a;color:#999;padding:1rem;font-size:14px;}</style></head><body>Unavailable</body></html>';
            return;
        }
        $html = (string) ($site['html_content'] ?? '');
        if (!headers_sent()) {
            header('Content-Type: text/html; charset=utf-8');
            header('X-Frame-Options: SAMEORIGIN');
        }
        echo $html;
    }

    public function previewStatus(string $token): void
    {
        try {
            $site = Site::findByPreviewToken($token);
            if ($site === null) {
                Response::json(['error' => 'Not found'], 404);
                return;
            }
            $st = (string) ($site['status'] ?? '');
            $hasHtml = ((string) ($site['html_content'] ?? '')) !== '';
            $ready = $st === 'preview' && $hasHtml;
            Response::json([
                'status' => $st,
                'ready' => $ready,
                'failed' => $st === 'failed',
                'error' => $st === 'failed' ? (string) ($site['generation_error'] ?? '') : null,
                'preview_url' => '/preview/' . rawurlencode($token),
            ]);
        } catch (Throwable) {
            Response::json(['error' => 'Status unavailable'], 500);
        }
    }
}
