<?php

declare(strict_types=1);

namespace Custode\Controllers;

use Custode\Helpers\Auth;
use Custode\Helpers\HtmlShell;
use Custode\Helpers\Response;
use Custode\Helpers\View;
use Custode\Models\Site;

final class EditorController
{
    public function show(string $siteId): void
    {
        Auth::startSession();
        $id = (int) $siteId;
        $site = Site::find($id);
        if ($site === null) {
            Response::html(HtmlShell::centeredPage('Editor — Custode', 'Site not found', '', '/admin', 'Admin'), 404);
            return;
        }

        $token = (string) ($_GET['t'] ?? '');
        if ($token !== '' && hash_equals((string) $site['preview_token'], $token)) {
            Auth::grantEditorAccess($id);
        }

        $status = (string) ($site['status'] ?? '');
        if (!in_array($status, ['paid', 'editing', 'deployed', 'live'], true)) {
            Response::html(HtmlShell::centeredPage('Editor — Custode', 'Not unlocked yet', 'Complete checkout to access the editor.', '/start', 'Wizard'), 403);
            return;
        }

        if (!Auth::canEditSite($id)) {
            View::render('editor-denied', [
                'title' => 'Editor access — Custode',
                'site_id' => $id,
            ], null);
            return;
        }

        View::render('editor', [
            'title' => 'Editor — Custode',
            'site' => $site,
        ], 'layout-editor');
    }
}
