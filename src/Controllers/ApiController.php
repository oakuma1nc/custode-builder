<?php

declare(strict_types=1);

namespace Custode\Controllers;

use Custode\Helpers\Auth;
use Custode\Helpers\Csrf;
use Custode\Helpers\Response;
use Custode\Models\Site;
use Throwable;

final class ApiController
{
    public function saveEditor(): void
    {
        Auth::startSession();
        if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
            Response::json(['error' => 'Method not allowed'], 405);
            return;
        }
        $raw = file_get_contents('php://input') ?: '';
        try {
            $data = json_decode($raw, true, 512, JSON_THROW_ON_ERROR);
        } catch (Throwable) {
            Response::json(['error' => 'Invalid JSON'], 400);
            return;
        }
        if (!is_array($data)) {
            Response::json(['error' => 'Invalid payload'], 400);
            return;
        }
        $csrfIn = (string) ($data['csrf_token'] ?? '');
        if (!Csrf::validate($csrfIn !== '' ? $csrfIn : Csrf::headerToken())) {
            Response::json(['error' => 'Invalid security token. Reload the editor.'], 419);
            return;
        }
        $siteId = (int) ($data['site_id'] ?? 0);
        if ($siteId < 1) {
            Response::json(['error' => 'site_id required'], 422);
            return;
        }
        $site = Site::find($siteId);
        if ($site === null) {
            Response::json(['error' => 'Site not found'], 404);
            return;
        }
        $token = (string) ($data['preview_token'] ?? '');
        if ($token !== '' && hash_equals((string) $site['preview_token'], $token)) {
            Auth::grantEditorAccess($siteId);
        }
        if (!Auth::canEditSite($siteId)) {
            Response::json(['error' => 'Forbidden'], 403);
            return;
        }
        $status = (string) ($site['status'] ?? '');
        if (!in_array($status, ['paid', 'editing', 'deployed', 'live'], true)) {
            Response::json(['error' => 'Site locked'], 403);
            return;
        }

        Site::saveEditorPayload($siteId, [
            'components' => (string) ($data['gjs_components'] ?? $data['components'] ?? ''),
            'styles' => (string) ($data['gjs_styles'] ?? $data['styles'] ?? ''),
            'html' => (string) ($data['html'] ?? ''),
            'css' => (string) ($data['css'] ?? ''),
        ]);
        Response::json(['ok' => true]);
    }

    public function loadEditor(string $siteId): void
    {
        Auth::startSession();
        $id = (int) $siteId;
        $site = Site::find($id);
        if ($site === null) {
            Response::json(['error' => 'Site not found'], 404);
            return;
        }
        $token = (string) ($_GET['t'] ?? '');
        if ($token !== '' && hash_equals((string) $site['preview_token'], $token)) {
            Auth::grantEditorAccess($id);
        }
        if (!Auth::canEditSite($id)) {
            Response::json(['error' => 'Forbidden'], 403);
            return;
        }
        $status = (string) ($site['status'] ?? '');
        if (!in_array($status, ['paid', 'editing', 'deployed', 'live'], true)) {
            Response::json(['error' => 'Site locked'], 403);
            return;
        }

        Response::json([
            'html' => (string) ($site['html_content'] ?? ''),
            'css' => (string) ($site['css_content'] ?? ''),
            'gjs_components' => (string) ($site['gjs_components'] ?? ''),
            'gjs_styles' => (string) ($site['gjs_styles'] ?? ''),
        ]);
    }
}
