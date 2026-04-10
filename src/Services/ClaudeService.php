<?php

declare(strict_types=1);

namespace Custode\Services;

use Custode\App;
use Custode\Models\Client;
use Custode\Models\GenerationLog;
use Custode\Models\Site;
use RuntimeException;
use Throwable;

final class ClaudeService
{
    private const API_URL = 'https://api.anthropic.com/v1/messages';

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
                'name' => $client['name'],
                'email' => $client['email'],
                'phone' => $client['phone'],
                'business_name' => $client['business_name'],
                'business_type' => $client['business_type'],
            ],
            'brief' => json_decode((string) $site['brief_json'], true) ?: [],
        ];
        $userMessage = "Build the website using this JSON brief. Respond with HTML only.\n\n"
            . json_encode($brief, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        $apiKey = (string) (App::$config['anthropic']['api_key'] ?? '');
        if ($apiKey === '') {
            Site::markFailed($siteId, 'ANTHROPIC_API_KEY is not configured.');
            return false;
        }
        $model = (string) (App::$config['anthropic']['model'] ?? 'claude-sonnet-4-6');

        $lastError = null;
        for ($attempt = 0; $attempt < 2; $attempt++) {
            if ($attempt > 0) {
                usleep((int) (1_000_000 * (2 ** ($attempt - 1))));
            }
            $started = (int) (microtime(true) * 1000);
            try {
                $response = $this->callMessagesApi($apiKey, $model, $systemText, $userMessage);
                $ended = (int) (microtime(true) * 1000);
                $duration = $ended - $started;

                $html = $this->extractHtml($response['text']);
                $usage = $response['usage'];
                $cost = $this->estimateCostUsd($usage['input_tokens'] ?? 0, $usage['output_tokens'] ?? 0);

                Site::saveGenerated($siteId, [
                    'html' => $html,
                    'css' => null,
                ]);

                GenerationLog::insert(
                    $siteId,
                    $usage['input_tokens'] ?? null,
                    $usage['output_tokens'] ?? null,
                    $cost,
                    $model,
                    $duration
                );
                return true;
            } catch (Throwable $e) {
                $lastError = $e;
                $ended = (int) (microtime(true) * 1000);
                GenerationLog::insert(
                    $siteId,
                    null,
                    null,
                    null,
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
                date('c') . ' Claude generation failed site=' . $siteId . ' ' . $msg . PHP_EOL,
                FILE_APPEND | LOCK_EX
            );
        }

        return false;
    }

    /**
     * @return array{text: string, usage: array{input_tokens?: int, output_tokens?: int}}
     */
    private function callMessagesApi(string $apiKey, string $model, string $systemText, string $userMessage): array
    {
        $body = [
            'model' => $model,
            'max_tokens' => 16384,
            'system' => [
                [
                    'type' => 'text',
                    'text' => $systemText,
                    'cache_control' => ['type' => 'ephemeral'],
                ],
            ],
            'messages' => [
                [
                    'role' => 'user',
                    'content' => [
                        ['type' => 'text', 'text' => $userMessage],
                    ],
                ],
            ],
        ];

        $raw = $this->curlJson(self::API_URL, $apiKey, $body);
        $decoded = json_decode($raw, true);
        if (!is_array($decoded)) {
            throw new RuntimeException('Invalid Claude API response.');
        }
        $text = '';
        if (!empty($decoded['content']) && is_array($decoded['content'])) {
            foreach ($decoded['content'] as $block) {
                if (is_array($block) && ($block['type'] ?? '') === 'text') {
                    $text .= (string) ($block['text'] ?? '');
                }
            }
        }
        if ($text === '') {
            throw new RuntimeException('Empty completion from Claude.');
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
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'x-api-key: ' . $apiKey,
                'anthropic-version: 2023-06-01',
            ],
            CURLOPT_POSTFIELDS => $payload,
            CURLOPT_TIMEOUT => 600,
        ]);
        $result = curl_exec($ch);
        $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $err = curl_error($ch);
        curl_close($ch);
        if ($result === false) {
            throw new RuntimeException('Claude request failed: ' . $err);
        }
        if ($code < 200 || $code >= 300) {
            throw new RuntimeException('Claude HTTP ' . $code . ': ' . substr((string) $result, 0, 500));
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
        $inPrice = getenv('ANTHROPIC_PRICE_IN_PER_MTOK');
        $outPrice = getenv('ANTHROPIC_PRICE_OUT_PER_MTOK');
        if ($inPrice === false || $outPrice === false || $inPrice === '' || $outPrice === '') {
            return null;
        }
        $in = (float) $inPrice;
        $out = (float) $outPrice;
        return round(($inTok / 1_000_000) * $in + ($outTok / 1_000_000) * $out, 6);
    }
}
