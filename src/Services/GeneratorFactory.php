<?php

declare(strict_types=1);

namespace Custode\Services;

use Custode\App;

final class GeneratorFactory
{
    public static function make(): GeneratorInterface
    {
        $provider = strtolower((string) (App::$config['generation']['provider'] ?? 'claude'));

        return match ($provider) {
            'kimi'  => new KimiService(),
            default => new ClaudeService(),
        };
    }
}
