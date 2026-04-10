<?php

declare(strict_types=1);

namespace Custode\Controllers;

use Custode\App;
use Custode\Helpers\Auth;
use Custode\Helpers\Csrf;
use Custode\Helpers\RateLimiter;
use Custode\Helpers\Response;
use Custode\Models\Site;
use Custode\Services\SiteDeployer;
use Throwable;

final class DeployController
{
    public function deploy(string $siteId): void
    {
        Auth::requireAdmin();
        if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
            Response::json(['error' => 'Method not allowed'], 405);
            return;
        }
        if (!Csrf::validate(Csrf::headerToken())) {
            Response::json(['error' => 'Invalid security token. Reload the dashboard.'], 419);
            return;
        }
        $perMin = (int) (App::$config['rate_limit']['admin_api_per_minute'] ?? 120);
        if (RateLimiter::tooMany('admin_api', $perMin, 60)) {
            Response::json(['error' => 'Too many requests. Slow down.'], 429);
            return;
        }
        $id = (int) $siteId;
        if ($id < 1 || Site::find($id) === null) {
            Response::json(['error' => 'Site not found'], 404);
            return;
        }
        try {
            (new SiteDeployer())->deploy($id);
            $site = Site::find($id);
            Response::json([
                'ok' => true,
                'live_url' => $site['live_url'] ?? null,
                'status' => $site['status'] ?? null,
            ]);
        } catch (Throwable $e) {
            Response::json(['ok' => false, 'error' => $e->getMessage()], 500);
        }
    }
}
