<?php

declare(strict_types=1);

namespace Custode\Controllers;

use Custode\Helpers\Database;
use Custode\Helpers\Response;
use Throwable;

final class HealthController
{
    public function index(): void
    {
        try {
            Database::pdo()->query('SELECT 1');
            Response::json(['ok' => true, 'database' => true]);
        } catch (Throwable) {
            Response::json(['ok' => false, 'database' => false], 503);
        }
    }
}
