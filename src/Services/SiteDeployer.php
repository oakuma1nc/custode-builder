<?php

declare(strict_types=1);

namespace Custode\Services;

use Custode\App;
use Custode\Models\Site;
use RuntimeException;

final class SiteDeployer
{
    public function __construct(
        private readonly CpanelService $cpanel = new CpanelService()
    ) {
    }

    public function deploy(int $siteId): void
    {
        $site = Site::find($siteId);
        if ($site === null) {
            throw new RuntimeException('Site not found.');
        }
        $html = (string) ($site['html_content'] ?? '');
        if ($html === '') {
            throw new RuntimeException('Site has no HTML to deploy.');
        }
        $slug = (string) $site['slug'];
        $relativeBase = 'public_html/sites/' . $slug;
        $this->cpanel->ensureDirectory($relativeBase);

        $tmp = tempnam(sys_get_temp_dir(), 'cb_');
        if ($tmp === false) {
            throw new RuntimeException('Could not create temp file.');
        }
        try {
            file_put_contents($tmp, $html);
            $this->cpanel->uploadFile($relativeBase, $tmp, 'index.html');
        } finally {
            @unlink($tmp);
        }

        $domain = (string) (App::$config['cpanel']['base_domain'] ?? 'custode.digital');
        $liveUrl = 'https://' . $domain . '/sites/' . rawurlencode($slug) . '/';
        $status = 'deployed';

        if (!empty(App::$config['cpanel']['enable_subdomain'])) {
            try {
                $this->cpanel->addSubdomain($slug, $domain, $relativeBase);
                $liveUrl = 'https://' . rawurlencode($slug) . '.' . $domain . '/';
                $status = 'live';
            } catch (\Throwable $e) {
                $log = (string) (App::$config['paths']['log_file'] ?? '');
                if ($log !== '') {
                    @file_put_contents(
                        $log,
                        date('c') . ' Subdomain skipped for site=' . $siteId . ': ' . $e->getMessage() . PHP_EOL,
                        FILE_APPEND | LOCK_EX
                    );
                }
            }
        }

        Site::saveDeploy($siteId, [
            'deploy_path' => $relativeBase . '/index.html',
            'live_url' => $liveUrl,
            'status' => $status,
        ]);
    }
}
