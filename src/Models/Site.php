<?php

declare(strict_types=1);

namespace Custode\Models;

use Custode\Helpers\Database;
use Custode\Helpers\Str;
use PDO;

final class Site
{
    /**
     * @param array{client_id: int, slug: string, preview_token: string, brief_json: string, status: string} $data
     */
    public static function create(array $data): int
    {
        $pdo = Database::pdo();
        $stmt = $pdo->prepare(
            'INSERT INTO sites (client_id, slug, preview_token, status, brief_json)
             VALUES (:client_id, :slug, :preview_token, :status, :brief_json)'
        );
        $stmt->execute([
            ':client_id' => $data['client_id'],
            ':slug' => $data['slug'],
            ':preview_token' => $data['preview_token'],
            ':status' => $data['status'],
            ':brief_json' => $data['brief_json'],
        ]);
        return (int) $pdo->lastInsertId();
    }

    public static function uniqueSlug(string $base): string
    {
        $slug = Str::slug($base);
        $pdo = Database::pdo();
        $candidate = $slug;
        $n = 1;
        while (self::slugExists($pdo, $candidate)) {
            $candidate = $slug . '-' . $n;
            $n++;
        }
        return $candidate;
    }

    private static function slugExists(PDO $pdo, string $slug): bool
    {
        $stmt = $pdo->prepare('SELECT 1 FROM sites WHERE slug = :s LIMIT 1');
        $stmt->execute([':s' => $slug]);
        return (bool) $stmt->fetchColumn();
    }

    /**
     * @return array<string, mixed>|null
     */
    public static function find(int $id): ?array
    {
        $stmt = Database::pdo()->prepare('SELECT * FROM sites WHERE id = :id LIMIT 1');
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row === false ? null : $row;
    }

    /**
     * @return array<string, mixed>|null
     */
    public static function findByPreviewToken(string $token): ?array
    {
        $stmt = Database::pdo()->prepare('SELECT * FROM sites WHERE preview_token = :t LIMIT 1');
        $stmt->execute([':t' => $token]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row === false ? null : $row;
    }

    /**
     * @return list<array<string, mixed>>
     */
    public static function allWithClients(): array
    {
        $sql = 'SELECT s.*, c.name AS client_name, c.email AS client_email, c.business_name, c.business_type
                FROM sites s
                INNER JOIN clients c ON c.id = s.client_id
                ORDER BY s.id DESC';
        return Database::pdo()->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function setGenerating(int $id): void
    {
        $stmt = Database::pdo()->prepare(
            "UPDATE sites SET status = 'generating', generation_error = NULL, updated_at = CURRENT_TIMESTAMP WHERE id = :id"
        );
        $stmt->execute([':id' => $id]);
    }

    public static function markFailed(int $id, string $message): void
    {
        $stmt = Database::pdo()->prepare(
            "UPDATE sites SET status = 'failed', generation_error = :err, updated_at = CURRENT_TIMESTAMP WHERE id = :id"
        );
        $stmt->execute([
            ':err' => substr($message, 0, 8000),
            ':id' => $id,
        ]);
    }

    /**
     * @param array{html?: string, css?: ?string, gjs_components?: ?string, gjs_styles?: ?string} $content
     */
    public static function saveGenerated(int $id, array $content): void
    {
        $pdo = Database::pdo();
        $stmt = $pdo->prepare(
            'UPDATE sites SET
                html_content = :html,
                css_content = :css,
                gjs_components = COALESCE(:gjc, gjs_components),
                gjs_styles = COALESCE(:gjs, gjs_styles),
                status = \'preview\',
                generation_error = NULL,
                generated_at = CURRENT_TIMESTAMP,
                updated_at = CURRENT_TIMESTAMP
             WHERE id = :id'
        );
        $stmt->execute([
            ':html' => $content['html'] ?? '',
            ':css' => $content['css'] ?? null,
            ':gjc' => $content['gjs_components'] ?? null,
            ':gjs' => $content['gjs_styles'] ?? null,
            ':id' => $id,
        ]);
    }

    public static function markPaid(int $id): void
    {
        $stmt = Database::pdo()->prepare(
            "UPDATE sites SET status = 'paid', paid_at = CURRENT_TIMESTAMP, updated_at = CURRENT_TIMESTAMP WHERE id = :id"
        );
        $stmt->execute([':id' => $id]);
    }

    public static function markEditing(int $id): void
    {
        $stmt = Database::pdo()->prepare(
            "UPDATE sites SET status = 'editing', updated_at = CURRENT_TIMESTAMP WHERE id = :id"
        );
        $stmt->execute([':id' => $id]);
    }

    /**
     * @param array{deploy_path?: string, live_url?: string, status: string} $deploy
     */
    public static function saveDeploy(int $id, array $deploy): void
    {
        $stmt = Database::pdo()->prepare(
            'UPDATE sites SET
                deploy_path = COALESCE(:dp, deploy_path),
                live_url = COALESCE(:lu, live_url),
                status = :st,
                deployed_at = CASE WHEN :st IN (\'deployed\', \'live\') THEN CURRENT_TIMESTAMP ELSE deployed_at END,
                updated_at = CURRENT_TIMESTAMP
             WHERE id = :id'
        );
        $stmt->execute([
            ':dp' => $deploy['deploy_path'] ?? null,
            ':lu' => $deploy['live_url'] ?? null,
            ':st' => $deploy['status'],
            ':id' => $id,
        ]);
    }

    /**
     * @param array{components?: string, styles?: string, html?: string, css?: string} $payload
     */
    public static function saveEditorPayload(int $id, array $payload): void
    {
        $stmt = Database::pdo()->prepare(
            'UPDATE sites SET
                gjs_components = :gc,
                gjs_styles = :gs,
                html_content = :html,
                css_content = :css,
                status = CASE WHEN status = \'paid\' THEN \'editing\' ELSE status END,
                updated_at = CURRENT_TIMESTAMP
             WHERE id = :id'
        );
        $stmt->execute([
            ':gc' => $payload['components'] ?? '',
            ':gs' => $payload['styles'] ?? '',
            ':html' => $payload['html'] ?? '',
            ':css' => $payload['css'] ?? '',
            ':id' => $id,
        ]);
    }
}
