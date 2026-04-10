<?php

declare(strict_types=1);

namespace Custode\Services;

use Custode\App;
use Stripe\Exception\SignatureVerificationException;
use Stripe\StripeClient;
use Stripe\Webhook;

final class StripeService
{
    private function client(): StripeClient
    {
        $key = (string) (App::$config['stripe']['secret_key'] ?? '');
        if ($key === '') {
            throw new \RuntimeException('STRIPE_SECRET_KEY is not configured.');
        }
        return new StripeClient($key);
    }

    /**
     * @return array{url: string, id: string}
     */
    public function createCheckoutSession(int $siteId, string $successUrl, string $cancelUrl): array
    {
        $amount = (int) (App::$config['stripe']['setup_amount_cents'] ?? 14900);
        $currency = strtolower((string) (App::$config['stripe']['currency'] ?? 'chf'));

        $session = $this->client()->checkout->sessions->create([
            'mode' => 'payment',
            'success_url' => $successUrl,
            'cancel_url' => $cancelUrl,
            'client_reference_id' => (string) $siteId,
            'metadata' => [
                'site_id' => (string) $siteId,
                'billing' => 'setup',
            ],
            'line_items' => [
                [
                    'quantity' => 1,
                    'price_data' => [
                        'currency' => $currency,
                        'unit_amount' => $amount,
                        'product_data' => [
                            'name' => 'Custode website setup',
                            'description' => 'Unlock your generated website and editor access',
                        ],
                    ],
                ],
            ],
        ]);

        $url = $session->url ?? '';
        if ($url === '') {
            throw new \RuntimeException('Stripe did not return a checkout URL.');
        }
        return ['url' => $url, 'id' => $session->id];
    }

    /**
     * Hosted subscription Checkout (monthly hosting). Requires STRIPE_MONTHLY_PRICE_ID in config.
     *
     * @return array{url: string, id: string}
     */
    public function createMonthlySubscriptionSession(
        int $siteId,
        string $customerEmail,
        string $successUrl,
        string $cancelUrl
    ): array {
        $priceId = (string) (App::$config['stripe']['monthly_price_id'] ?? '');
        if ($priceId === '') {
            throw new \RuntimeException('STRIPE_MONTHLY_PRICE_ID is not configured.');
        }
        if (!filter_var($customerEmail, FILTER_VALIDATE_EMAIL)) {
            throw new \RuntimeException('Invalid customer email for subscription checkout.');
        }
        $session = $this->client()->checkout->sessions->create([
            'mode' => 'subscription',
            'customer_email' => $customerEmail,
            'success_url' => $successUrl,
            'cancel_url' => $cancelUrl,
            'client_reference_id' => (string) $siteId,
            'metadata' => [
                'site_id' => (string) $siteId,
                'billing' => 'monthly',
            ],
            'subscription_data' => [
                'metadata' => [
                    'site_id' => (string) $siteId,
                ],
            ],
            'line_items' => [
                [
                    'price' => $priceId,
                    'quantity' => 1,
                ],
            ],
        ]);
        $url = $session->url ?? '';
        if ($url === '') {
            throw new \RuntimeException('Stripe did not return a checkout URL.');
        }
        return ['url' => $url, 'id' => $session->id];
    }

    /**
     * @return \Stripe\Event
     */
    public function constructWebhookEvent(string $payload, string $signatureHeader): \Stripe\Event
    {
        $secret = (string) (App::$config['stripe']['webhook_secret'] ?? '');
        if ($secret === '') {
            throw new \RuntimeException('STRIPE_WEBHOOK_SECRET is not configured.');
        }
        try {
            return Webhook::constructEvent($payload, $signatureHeader, $secret);
        } catch (SignatureVerificationException $e) {
            throw new \RuntimeException('Invalid Stripe signature: ' . $e->getMessage(), 0, $e);
        }
    }

    public function retrieveSession(string $sessionId): \Stripe\Checkout\Session
    {
        return $this->client()->checkout->sessions->retrieve($sessionId, []);
    }
}
