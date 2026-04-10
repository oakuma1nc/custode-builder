<?php

declare(strict_types=1);

namespace Custode\Controllers;

use Custode\App;
use Custode\Helpers\Auth;
use Custode\Helpers\Csrf;
use Custode\Helpers\Response;
use Custode\Helpers\RateLimiter;
use Custode\Helpers\View;
use Custode\Models\Client;
use Custode\Models\Payment;
use Custode\Models\Site;
use Custode\Services\MailService;
use Custode\Services\SiteDeployer;
use Custode\Services\StripeService;
use Throwable;

final class PaymentController
{
    private static function baseUrl(): string
    {
        $c = (string) (App::$config['app']['url'] ?? '');
        if ($c !== '') {
            return rtrim($c, '/');
        }
        $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
            || ((int) ($_SERVER['SERVER_PORT'] ?? 0) === 443);
        $host = (string) ($_SERVER['HTTP_HOST'] ?? 'localhost');
        return ($https ? 'https' : 'http') . '://' . $host;
    }

    private static function checkoutRateLimited(): bool
    {
        $max = (int) (App::$config['rate_limit']['checkout_per_hour'] ?? 40);
        return RateLimiter::tooMany('checkout', $max, 3600);
    }

    public function createCheckout(string $siteId): void
    {
        if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
            Response::json(['error' => 'Method not allowed'], 405);
            return;
        }
        if (!Csrf::validate(Csrf::headerToken())) {
            Response::json(['error' => 'Invalid security token. Refresh the preview page.'], 419);
            return;
        }
        if (self::checkoutRateLimited()) {
            Response::json(['error' => 'Too many checkout attempts. Try again later.'], 429);
            return;
        }
        $id = (int) $siteId;
        $site = Site::find($id);
        if ($site === null || ($site['status'] ?? '') !== 'preview') {
            Response::json(['error' => 'Checkout unavailable for this site.'], 400);
            return;
        }
        $base = self::baseUrl();
        $stripe = new StripeService();
        $amount = (int) (App::$config['stripe']['setup_amount_cents'] ?? 14900);
        $currency = (string) (App::$config['stripe']['currency'] ?? 'chf');
        try {
            $session = $stripe->createCheckoutSession(
                $id,
                $base . '/payment/success?session_id={CHECKOUT_SESSION_ID}',
                $base . '/payment/cancel'
            );
            Payment::createPending($id, $session['id'], $amount, $currency, 'setup');
            Response::json(['url' => $session['url']]);
        } catch (Throwable $e) {
            Response::json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Monthly hosting subscription (Stripe Price required). Site must already be unlocked.
     */
    public function createMonthlyCheckout(string $siteId): void
    {
        if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
            Response::json(['error' => 'Method not allowed'], 405);
            return;
        }
        if (!Csrf::validate(Csrf::headerToken())) {
            Response::json(['error' => 'Invalid security token.'], 419);
            return;
        }
        if (self::checkoutRateLimited()) {
            Response::json(['error' => 'Too many checkout attempts. Try again later.'], 429);
            return;
        }
        $id = (int) $siteId;
        $site = Site::find($id);
        if ($site === null) {
            Response::json(['error' => 'Site not found.'], 404);
            return;
        }
        $st = (string) ($site['status'] ?? '');
        if (!in_array($st, ['paid', 'editing', 'deployed', 'live'], true)) {
            Response::json(['error' => 'Subscribe after your website setup is complete.'], 400);
            return;
        }
        $client = Client::find((int) $site['client_id']);
        if ($client === null) {
            Response::json(['error' => 'Client not found.'], 400);
            return;
        }
        $email = (string) ($client['email'] ?? '');
        $base = self::baseUrl();
        $stripe = new StripeService();
        $labelCents = (int) (App::$config['stripe']['monthly_label_cents'] ?? 4900);
        try {
            $session = $stripe->createMonthlySubscriptionSession(
                $id,
                $email,
                $base . '/payment/success?session_id={CHECKOUT_SESSION_ID}',
                $base . '/payment/cancel'
            );
            Payment::createPending($id, $session['id'], $labelCents, strtoupper((string) (App::$config['stripe']['currency'] ?? 'chf')), 'monthly');
            Response::json(['url' => $session['url']]);
        } catch (Throwable $e) {
            Response::json(['error' => $e->getMessage()], 500);
        }
    }

    public function success(): void
    {
        Auth::startSession();
        $sessionId = (string) ($_GET['session_id'] ?? '');
        if ($sessionId === '') {
            View::render('payment-success', [
                'title' => 'Payment',
                'error' => 'Missing session.',
                'editor_url' => '',
                'site_id' => 0,
            ], null);
            return;
        }
        $editorUrl = '';
        $siteId = 0;
        try {
            $stripe = new StripeService();
            $session = $stripe->retrieveSession($sessionId);
            $siteId = (int) ($session->metadata['site_id'] ?? 0);
            if ($siteId < 1) {
                $siteId = (int) ($session->client_reference_id ?? 0);
            }
            if ($siteId > 0 && ($session->payment_status === 'paid' || $session->payment_status === 'no_payment_required')) {
                Auth::grantEditorAccess($siteId);
                $editorUrl = '/editor/' . $siteId;
            }
        } catch (Throwable) {
            // Webhook is source of truth; still show confirmation page.
        }
        View::render('payment-success', [
            'title' => 'Thank you — Custode',
            'session_id' => $sessionId,
            'editor_url' => $editorUrl,
            'site_id' => $siteId,
        ], null);
    }

    public function cancel(): void
    {
        View::render('payment-cancel', ['title' => 'Payment cancelled — Custode'], null);
    }

    public function webhook(): void
    {
        $payload = file_get_contents('php://input') ?: '';
        $sig = (string) ($_SERVER['HTTP_STRIPE_SIGNATURE'] ?? '');
        $logFile = (string) (App::$config['paths']['log_file'] ?? '');

        $okResponse = static function (): void {
            if (!headers_sent()) {
                http_response_code(200);
                header('Content-Type: application/json');
            }
            echo '{}';
        };

        try {
            $stripe = new StripeService();
            $event = $stripe->constructWebhookEvent($payload, $sig);
        } catch (Throwable $e) {
            if ($logFile !== '') {
                @file_put_contents($logFile, date('c') . ' Stripe webhook verify failed: ' . $e->getMessage() . PHP_EOL, FILE_APPEND | LOCK_EX);
            }
            $okResponse();
            return;
        }

        try {
            if ($event->type === 'checkout.session.completed') {
                $obj = $event->data->object;
                $sessionId = (string) ($obj->id ?? '');
                if ($sessionId === '') {
                    $okResponse();
                    return;
                }

                $dup = Payment::findByStripeSession($sessionId);
                if ($dup !== null && ($dup['status'] ?? '') === 'completed') {
                    $okResponse();
                    return;
                }

                $siteId = (int) ($obj->metadata['site_id'] ?? 0);
                if ($siteId < 1) {
                    $siteId = (int) ($obj->client_reference_id ?? 0);
                }
                $billing = strtolower((string) ($obj->metadata['billing'] ?? 'setup'));
                $pi = (string) ($obj->payment_intent ?? '');

                if ($siteId < 1) {
                    $okResponse();
                    return;
                }

                Payment::markCompleted($sessionId, $pi !== '' ? $pi : null);

                if ($billing === 'monthly') {
                    $site = Site::find($siteId);
                    $client = $site ? Client::find((int) $site['client_id']) : null;
                    $to = (string) ($client['email'] ?? '');
                    if ($to !== '') {
                        MailService::send(
                            $to,
                            'Custode — hosting subscription active',
                            "Thank you. Your monthly hosting subscription for site #{$siteId} is now active.\n\n— Custode"
                        );
                    }
                    $okResponse();
                    return;
                }

                Site::markPaid($siteId);
                MailService::notifyAdmin(
                    'Custode — setup payment completed',
                    "Site ID {$siteId} paid. Session {$sessionId}."
                );
                $deployer = new SiteDeployer();
                try {
                    $deployer->deploy($siteId);
                } catch (Throwable $deployErr) {
                    if ($logFile !== '') {
                        @file_put_contents(
                            $logFile,
                            date('c') . ' Deploy after payment failed site=' . $siteId . ' ' . $deployErr->getMessage() . PHP_EOL,
                            FILE_APPEND | LOCK_EX
                        );
                    }
                }
                $site = Site::find($siteId);
                $client = $site ? Client::find((int) $site['client_id']) : null;
                $to = (string) ($client['email'] ?? '');
                if ($to !== '') {
                    $live = (string) ($site['live_url'] ?? '');
                    MailService::send(
                        $to,
                        'Custode — your website is ready to edit',
                        "Payment received. Open your editor to customise your site.\n\n"
                        . ($live !== '' ? "Live preview URL: {$live}\n" : '')
                        . 'Editor: append /editor/' . $siteId . ' to the builder URL (use your secure link after sign-in).' . "\n\n— Custode"
                    );
                }
            }
        } catch (Throwable $e) {
            if ($logFile !== '') {
                @file_put_contents($logFile, date('c') . ' Stripe webhook handler error: ' . $e->getMessage() . PHP_EOL, FILE_APPEND | LOCK_EX);
            }
        }

        $okResponse();
    }
}
