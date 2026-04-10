<?php

declare(strict_types=1);

namespace Custode\Models;

use Custode\Helpers\Database;
use PDO;

final class Payment
{
    /**
     * @return array<string, mixed>|null
     */
    public static function findByStripeSession(string $sessionId): ?array
    {
        $stmt = Database::pdo()->prepare(
            'SELECT * FROM payments WHERE stripe_session_id = :sid LIMIT 1'
        );
        $stmt->execute([':sid' => $sessionId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row === false ? null : $row;
    }

    public static function createPending(
        int $siteId,
        string $stripeSessionId,
        int $amountCents,
        string $currency,
        string $paymentType = 'setup'
    ): int {
        $pdo = Database::pdo();
        $stmt = $pdo->prepare(
            'INSERT INTO payments (site_id, stripe_session_id, amount_cents, currency, status, payment_type)
             VALUES (:site_id, :sid, :amt, :cur, \'pending\', :ptype)'
        );
        $stmt->execute([
            ':site_id' => $siteId,
            ':sid' => $stripeSessionId,
            ':amt' => $amountCents,
            ':cur' => strtoupper($currency),
            ':ptype' => $paymentType,
        ]);
        return (int) $pdo->lastInsertId();
    }

    public static function markCompleted(string $stripeSessionId, ?string $paymentIntent): void
    {
        $stmt = Database::pdo()->prepare(
            'UPDATE payments SET
                status = \'completed\',
                stripe_payment_intent = COALESCE(:pi, stripe_payment_intent)
             WHERE stripe_session_id = :sid'
        );
        $stmt->execute([':pi' => $paymentIntent, ':sid' => $stripeSessionId]);
    }
}
