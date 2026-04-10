<?php

declare(strict_types=1);

namespace Custode\Controllers;

use Custode\App;
use Custode\Helpers\Locale;
use Custode\Helpers\View;

final class LandingController
{
    public function index(): void
    {
        $locale = Locale::resolve();
        $t = Locale::landingStrings($locale);
        $setupCents = (int) (App::$config['stripe']['setup_amount_cents'] ?? 14900);
        $hasMonthly = (string) (App::$config['stripe']['monthly_price_id'] ?? '') !== '';
        $monthlyChf = $hasMonthly
            ? $this->formatChf((int) (App::$config['stripe']['monthly_label_cents'] ?? 4900))
            : '';

        View::render('landing', [
            'locale' => $locale,
            't' => $t,
            'title' => (string) ($t['meta_title'] ?? 'Custode'),
            'setup_chf' => $this->formatChf($setupCents),
            'has_monthly' => $hasMonthly,
            'monthly_chf' => $monthlyChf,
        ], null);
    }

    private function formatChf(int $cents): string
    {
        $v = $cents / 100;
        return number_format($v, $v === floor($v) ? 0 : 2, '.', "'");
    }
}
