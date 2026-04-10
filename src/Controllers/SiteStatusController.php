<?php

declare(strict_types=1);

namespace Custode\Controllers;

use Custode\Helpers\Auth;
use Custode\Helpers\Response;
use Custode\Models\Site;

final class SiteStatusController
{
    public function deployStatus(string $siteId): void
    {
        Auth::startSession();
        $id = (int) $siteId;
        if ($id < 1) {
            Response::json(['error' => 'Invalid site'], 400);
            return;
        }
        $site = Site::find($id);
        if ($site === null) {
            Response::json(['error' => 'Not found'], 404);
            return;
        }
        $t = (string) ($_GET['t'] ?? '');
        if ($t !== '' && hash_equals((string) $site['preview_token'], $t)) {
            Auth::grantEditorAccess($id);
        }
        if (!Auth::canEditSite($id)) {
            Response::json(['error' => 'Forbidden'], 403);
            return;
        }
        $st = (string) ($site['status'] ?? '');
        Response::json([
            'status' => $st,
            'live_url' => (string) ($site['live_url'] ?? ''),
            'deployed' => in_array($st, ['deployed', 'live'], true),
            'deploy_path' => (string) ($site['deploy_path'] ?? ''),
        ]);
    }
}
