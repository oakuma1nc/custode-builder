<?php

declare(strict_types=1);

namespace Custode\Services;

use Custode\App;
use RuntimeException;

final class CpanelService
{
    private function api(): \cpanelAPI
    {
        $host = (string) (App::$config['cpanel']['host'] ?? '');
        $user = (string) (App::$config['cpanel']['user'] ?? '');
        $token = (string) (App::$config['cpanel']['token'] ?? '');
        if ($host === '' || $user === '' || $token === '') {
            throw new RuntimeException('cPanel credentials are not configured (CPANEL_HOST, CPANEL_USER, CPANEL_TOKEN).');
        }
        $path = dirname(__DIR__, 2) . '/vendor/scorpionslh/cpanel-uapi-php-class/cpaneluapi.class.php';
        if (!is_file($path)) {
            throw new RuntimeException('cPanel API class not found. Run composer install.');
        }
        require_once $path;
        return new \cpanelAPI($user, $token, $host);
    }

    /**
     * Ensure a path relative to the cPanel home exists (e.g. public_html/sites/my-slug).
     * Uses Fileman::mkdir with parent path + directory name for each segment.
     */
    public function ensureDirectory(string $relativePath): void
    {
        $parts = array_values(array_filter(explode('/', trim($relativePath, '/'))));
        if ($parts === []) {
            return;
        }
        $cp = $this->api();
        $parent = '';
        foreach ($parts as $segment) {
            $res = $cp->uapi->post->Fileman->mkdir([
                'path' => $parent,
                'name' => $segment,
            ]);
            $parent = $parent === '' ? $segment : $parent . '/' . $segment;
            if ($res === null) {
                continue;
            }
            if (!empty($res->errors) && is_array($res->errors)) {
                $msg = strtolower((string) ($res->errors[0] ?? ''));
                if ($msg !== '' && !str_contains($msg, 'exists') && !str_contains($msg, 'already')) {
                    throw new RuntimeException('cPanel mkdir: ' . implode(' ', $res->errors));
                }
            }
        }
    }

    /**
     * Upload a local file into a directory relative to home using Fileman::upload_files.
     */
    public function uploadFile(string $relativeDir, string $localPath, string $remoteName): void
    {
        if (!is_readable($localPath)) {
            throw new RuntimeException('Local file not readable: ' . $localPath);
        }
        $cp = $this->api();
        $cf = curl_file_create($localPath, 'text/html', $remoteName);
        $payload = [
            'dir' => trim($relativeDir, '/'),
            'file-1' => $cf,
        ];
        $res = $cp->uapi->post->Fileman->upload_files($payload);
        if ($res === null) {
            throw new RuntimeException('cPanel upload returned no response.');
        }
        $status = $res->status ?? 0;
        if ((int) $status !== 1) {
            $err = '';
            if (!empty($res->errors) && is_array($res->errors)) {
                $err = implode(' ', $res->errors);
            }
            throw new RuntimeException('cPanel upload failed: ' . ($err ?: json_encode($res)));
        }
    }

    /**
     * Create {sub}.{rootDomain} pointing at a directory under the account home (API2).
     * Example: sub "acme", root "custode.digital", dir "public_html/sites/acme".
     */
    public function addSubdomain(string $subdomain, string $rootDomain, string $documentRootRelative): void
    {
        $subdomain = trim($subdomain, '.');
        $rootDomain = trim($rootDomain);
        $dir = trim($documentRootRelative, '/');
        if ($subdomain === '' || $rootDomain === '' || $dir === '') {
            throw new RuntimeException('Invalid subdomain parameters.');
        }
        $cp = $this->api();
        $res = $cp->api2->post->SubDomain->addsubdomain([
            'domain' => $subdomain,
            'rootdomain' => $rootDomain,
            'dir' => $dir,
        ]);
        if ($res === null) {
            throw new RuntimeException('cPanel addsubdomain returned no response.');
        }
        if (!empty($res->errors)) {
            $err = is_array($res->errors) ? implode(' ', $res->errors) : (string) $res->errors;
            $low = strtolower($err);
            if (!str_contains($low, 'already') && !str_contains($low, 'exists')) {
                throw new RuntimeException('cPanel addsubdomain: ' . $err);
            }
        }
    }
}
