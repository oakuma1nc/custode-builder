<?php

declare(strict_types=1);

namespace Custode\Controllers;

use Custode\App;
use Custode\Helpers\Auth;
use Custode\Helpers\Csrf;
use Custode\Helpers\Response;
use Custode\Helpers\View;
use Custode\Models\Site;

final class DashboardController
{
    public function index(): void
    {
        Auth::startSession();
        if (!Auth::isAdmin()) {
            View::render('login', ['title' => 'Sign in — Custode Builder'], null);
            return;
        }
        $sites = Site::allWithClients();
        View::render('dashboard', [
            'title' => 'Dashboard — Custode Builder',
            'sites' => $sites,
            'monthly_available' => (string) (App::$config['stripe']['monthly_price_id'] ?? '') !== '',
        ]);
    }

    public function authenticate(): void
    {
        Auth::startSession();
        if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
            Response::redirect('/admin');
            return;
        }
        if (!Csrf::validate((string) ($_POST['csrf_token'] ?? ''))) {
            View::render('login', [
                'title' => 'Sign in — Custode Builder',
                'error' => 'Invalid session token. Refresh and try again.',
            ], null);
            return;
        }
        $user = (string) ($_POST['username'] ?? '');
        $pass = (string) ($_POST['password'] ?? '');
        if (Auth::attemptAdminLogin($user, $pass)) {
            Response::redirect('/admin');
            return;
        }
        View::render('login', [
            'title' => 'Sign in — Custode Builder',
            'error' => 'Invalid credentials or admin not configured.',
        ], null);
    }

    public function logout(): void
    {
        Auth::startSession();
        if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
            Response::redirect('/admin');
            return;
        }
        if (!Csrf::validate((string) ($_POST['csrf_token'] ?? ''))) {
            Response::redirect('/admin');
            return;
        }
        Auth::logout();
        Response::redirect('/');
    }
}
