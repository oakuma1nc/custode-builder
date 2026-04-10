<?php

declare(strict_types=1);

namespace Custode\Models;

use Custode\Helpers\Database;

final class GenerationLog
{
    public static function insert(
        int $siteId,
        ?int $promptTokens,
        ?int $completionTokens,
        ?float $costUsd,
        string $model,
        int $durationMs,
        ?string $errorMessage = null
    ): void {
        $stmt = Database::pdo()->prepare(
            'INSERT INTO generation_logs (site_id, prompt_tokens, completion_tokens, cost_usd, model, duration_ms, error_message)
             VALUES (:site_id, :pt, :ct, :cost, :model, :ms, :err)'
        );
        $stmt->execute([
            ':site_id' => $siteId,
            ':pt' => $promptTokens,
            ':ct' => $completionTokens,
            ':cost' => $costUsd,
            ':model' => $model,
            ':ms' => $durationMs,
            ':err' => $errorMessage,
        ]);
    }
}
