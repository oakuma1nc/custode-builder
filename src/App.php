<?php

declare(strict_types=1);

namespace Custode;

/**
 * Application container (config + shared state). Set from index.php before routing.
 */
final class App
{
    /** @var array<string, mixed> */
    public static array $config = [];
}
