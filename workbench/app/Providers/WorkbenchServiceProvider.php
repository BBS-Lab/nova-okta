<?php

declare(strict_types=1);

namespace Workbench\App\Providers;

use Illuminate\Support\ServiceProvider;
use Workbench\App\Models\User;

/**
 * Configures the served workbench (composer serve) so the live Playwright
 * scenarios have a working app: the workbench user model, a lenient
 * verified-email default, a custom Nova brand (name + colours, to prove the
 * custom login respects branding), and demo Okta credentials. No 'redirect' —
 * the redirect_uri is derived from the okta/callback route.
 */
class WorkbenchServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        config([
            'auth.providers.users.model' => User::class,
            'okta.require_verified_email' => false,

            // Custom Nova branding — the custom login must honour it.
            'nova.name' => 'Acme Nova',
            'nova.brand.colors' => [
                '400' => '167, 139, 250',
                '500' => '124, 58, 237', // violet-600
                '600' => '109, 40, 217',
            ],

            'services.okta' => [
                'client_id' => 'demo-client-id',
                'client_secret' => 'demo-client-secret',
                'redirect' => env('OKTA_REDIRECT_URI'),
                'base_url' => 'https://example.okta.com',
            ],
        ]);
    }
}
