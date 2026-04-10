<?php

declare(strict_types=1);

namespace Custode\Controllers;

use Custode\Helpers\Locale;
use Custode\Helpers\View;

final class WizardController
{
    public function show(): void
    {
        $locale = Locale::resolve();
        $t = Locale::landingStrings($locale);
        View::render('wizard', [
            'locale' => $locale,
            't' => $t,
            'title' => (string) ($t['wizard_meta_title'] ?? $t['meta_title'] ?? 'Custode'),
        ], null);
    }
}
