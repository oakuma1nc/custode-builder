<?php

declare(strict_types=1);

namespace Custode\Models;

use Custode\Helpers\Database;
use PDO;

final class Client
{
    /**
     * @param array{name: string, email: string, phone: ?string, business_name: string, business_type: string} $data
     */
    public static function create(array $data): int
    {
        $pdo = Database::pdo();
        $stmt = $pdo->prepare(
            'INSERT INTO clients (name, email, phone, business_name, business_type)
             VALUES (:name, :email, :phone, :business_name, :business_type)'
        );
        $stmt->execute([
            ':name' => $data['name'],
            ':email' => $data['email'],
            ':phone' => $data['phone'] !== null && $data['phone'] !== '' ? $data['phone'] : null,
            ':business_name' => $data['business_name'],
            ':business_type' => $data['business_type'],
        ]);
        return (int) $pdo->lastInsertId();
    }

    /**
     * @return array<string, mixed>|null
     */
    public static function find(int $id): ?array
    {
        $stmt = Database::pdo()->prepare('SELECT * FROM clients WHERE id = :id LIMIT 1');
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row === false ? null : $row;
    }
}
