<?php

declare(strict_types=1);

/**
 * [ method, path, Controller@action ]. Public landing GET /. Admin GET|POST /admin.
 * POST /api/auth/logout — admin logout.
 */
return [
    ['GET', '/health', 'HealthController@index'],
    ['GET', '/', 'LandingController@index'],
    ['GET', '/start', 'WizardController@show'],
    ['GET', '/admin', 'DashboardController@index'],
    ['POST', '/admin', 'DashboardController@authenticate'],
    ['POST', '/api/auth/logout', 'DashboardController@logout'],
    ['GET', '/brief', 'BriefController@showForm'],
    ['POST', '/api/brief', 'BriefController@submit'],
    ['GET', '/preview/{token}', 'PreviewController@show'],
    ['GET', '/api/preview/frame/{token}', 'PreviewController@frame'],
    ['GET', '/api/preview/status/{token}', 'PreviewController@previewStatus'],
    ['POST', '/api/generate/{site_id}', 'GenerateController@regenerate'],
    ['POST', '/api/checkout/{site_id}', 'PaymentController@createCheckout'],
    ['POST', '/api/checkout/monthly/{site_id}', 'PaymentController@createMonthlyCheckout'],
    ['GET', '/payment/success', 'PaymentController@success'],
    ['GET', '/payment/cancel', 'PaymentController@cancel'],
    ['POST', '/api/webhook/stripe', 'PaymentController@webhook'],
    ['GET', '/editor/{site_id}', 'EditorController@show'],
    ['POST', '/api/editor/save', 'ApiController@saveEditor'],
    ['GET', '/api/editor/load/{site_id}', 'ApiController@loadEditor'],
    ['POST', '/api/deploy/{site_id}', 'DeployController@deploy'],
    ['GET', '/api/site/{site_id}/deploy-status', 'SiteStatusController@deployStatus'],
];
