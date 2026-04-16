<?php

declare(strict_types=1);

namespace Custode\Services;

use Custode\App;
use Custode\Models\Client;
use Custode\Models\GenerationLog;
use Custode\Models\Site;
use RuntimeException;
use Throwable;

/**
 * Site generator backed by Kimi (Moonshot AI).
 * Uses the OpenAI-compatible Chat Completions API at api.moonshot.cn.
 */
final class KimiService implements GeneratorInterface
{
    private const API_URL = 'https://api.moonshot.cn/v1/chat/completions';

    public function generateForSite(int $siteId): bool
    {
        $site = Site::find($siteId);
        if ($site === null) {
            return false;
        }
        $client = Client::find((int) $site['client_id']);
        if ($client === null) {
            return false;
        }

        Site::setGenerating($siteId);

        $systemPath = App::$config['paths']['root'] . '/prompts/website-generation.txt';
        if (!is_readable($systemPath)) {
            Site::markFailed($siteId, 'System prompt file missing.');
            return false;
        }
        $systemText = (string) file_get_contents($systemPath);

        $brief = [
            'client' => [
                'name'          => $client['name'],
                'email'         => $client['email'],
                'phone'         => $client['phone'],
                'business_name' => $client['business_name'],
                'business_type' => $client['business_type'],
            ],
            'brief' => json_decode((string) $site['brief_json'], true) ?: [],
        ];
        $userMessage = "Build the website using this JSON brief. Respond with HTML only.\n\n"
            . json_encode($brief, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        $apiKey = (string) (App::$config['kimi']['api_key'] ?? '');
        if ($apiKey === '') {
            Site::markFailed($siteId, 'KIMI_API_KEY is not configured.');
            return false;
        }
        $model = (string) (App::$config['kimi']['model'] ?? 'moonshot-v1-32k');

        $lastError = null;
        for ($attempt = 0; $attempt < 2; $attempt++) {
            if ($attempt > 0) {
                usleep((int) (1_000_000 * (2 ** ($attempt - 1))));
            }
            $started = (int) (microtime(true) * 1000);
            try {
                $response = $this->callChatApi($apiKey, $model, $systemText, $userMessage);
                $ended    = (int) (microtime(true) * 1000);
                $duration = $ended - $started;

                $html  = $this->extractHtml($response['text']);
                $usage = $response['usage'];
                $cost  = $this->estimateCostUsd($usage['prompt_tokens'] ?? 0, $usage['completion_tokens'] ?? 0);

                Site::saveGenerated($siteId, ['html' => $html, 'css' => null]);

                GenerationLog::insert(
                    $siteId,
                    $usage['prompt_tokens']     ?? null,
                    $usage['completion_tokens'] ?? null,
                    $cost,
                    $model,
                    $duration
                );
                return true;
            } catch (Throwable $e) {
                $lastError = $e;
                $ended     = (int) (microtime(true) * 1000);
                GenerationLog::insert(
                    $siteId,
                    null, null, null,
                    $model,
                    $ended - $started,
                    substr($e->getMessage(), 0, 2000)
                );
            }
        }

        $msg = $lastError !== null ? $lastError->getMessage() : 'Generation failed after retries.';
        Site::markFailed($siteId, $msg);
        $log = App::$config['paths']['log_file'] ?? '';
        if ($log !== '') {
            @file_put_contents(
                $log,
                date('c') . ' Kimi generation failed site=' . $siteId . ' ' . $msg . PHP_EOL,
                FILE_APPEND | LOCK_EX
            );
        }

        return false;
    }

    /**
     * @return array{text: string, usage: array{prompt_tokens?: int, completion_tokens?: int}}
     */
    private function callChatApi(string $apiKey, string $model, string $systemText, string $userMessage): array
    {
        $body = [
            'model'      => $model,
            'max_tokens' => 16384,
            'messages'   => [
                ['role' => 'system', 'content' => $systemText],
                ['role' => 'user',   'content' => $userMessage],
            ],
        ];

        $raw     = $this->curlJson(self::API_URL, $apiKey, $body);
        $decoded = json_decode($raw, true);
        if (!is_array($decoded)) {
            throw new RuntimeException('Invalid Kimi API response.');
        }

        // Surface API-level errors (e.g. quota, auth)
        if (!empty($decoded['error'])) {
            $errMsg = (string) ($decoded['error']['message'] ?? json_encode($decoded['error']));
            throw new RuntimeException('Kimi API error: ' . $errMsg);
        }

        $text = '';
        if (!empty($decoded['choices']) && is_array($decoded['choices'])) {
            $text = (string) ($decoded['choices'][0]['message']['content'] ?? '');
        }
        if ($text === '') {
            throw new RuntimeException('Empty completion from Kimi.');
        }

        $usage = is_array($decoded['usage'] ?? null) ? $decoded['usage'] : [];
        return ['text' => $text, 'usage' => $usage];
    }

    /**
     * @param array<string, mixed> $body
     */
    private function curlJson(string $url, string $apiKey, array $body): string
    {
        $payload = json_encode($body, JSON_THROW_ON_ERROR);
        $ch = curl_init($url);
        if ($ch === false) {
            throw new RuntimeException('Unable to init cURL.');
        }
        curl_setopt_array($ch, [
            CURLOPT_POST          => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER    => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $apiKey,
            ],
            CURLOPT_POSTFIELDS => $payload,
            CURLOPT_TIMEOUT    => 600,
        ]);
        $result = curl_exec($ch);
        $code   = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $err    = curl_error($ch);
        curl_close($ch);

        if ($result === false) {
            throw new RuntimeException('Kimi request failed: ' . $err);
        }
        if ($code < 200 || $code >= 300) {
            throw new RuntimeException('Kimi HTTP ' . $code . ': ' . substr((string) $result, 0, 500));
        }
        return (string) $result;
    }

    private function extractHtml(string $raw): string
    {
        $t = trim($raw);
        if (preg_match('/^```(?:html)?\s*/i', $t)) {
            $t = (string) preg_replace('/^```(?:html)?\s*/i', '', $t);
            $t = (string) preg_replace('/```\s*$/', '', trim($t));
        }
        return trim($t);
    }

    private function estimateCostUsd(int $inTok, int $outTok): ?float
    {
        $inPrice  = getenv('KIMI_PRICE_IN_PER_MTOK');
        $outPrice = getenv('KIMI_PRICE_OUT_PER_MTOK');
        if ($inPrice === false || $outPrice === false || $inPrice === '' || $outPrice === '') {
            return null;
        }
        return round(($inTok / 1_000_000) * (float) $inPrice + ($outTok / 1_000_000) * (float) $outPrice, 6);
    }
}
