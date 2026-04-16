<?php

declare(strict_types=1);

namespace Custode\Services;

interface GeneratorInterface
{
    public function generateForSite(int $siteId): bool;
}
