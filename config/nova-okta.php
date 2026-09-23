<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Override Nova's login screen
    |--------------------------------------------------------------------------
    |
    | When enabled, the package replaces Nova's default GET login route with its
    | own Blade screen (the one carrying the "Log In with Okta" button). The
    | POST login is left untouched, so Fortify still handles password logins.
    | Set NOVA_OKTA_OVERRIDE_LOGIN=false to keep Nova's default login screen and
    | wire the Okta button in yourself.
    |
    */

    'override_nova_login' => (bool) env('NOVA_OKTA_OVERRIDE_LOGIN', true),

    /*
    |--------------------------------------------------------------------------
    | Two-factor challenge bridge route
    |--------------------------------------------------------------------------
    |
    | Fortify redirects a 2FA-enabled login to route('two-factor.login'), a name
    | Nova never registers (it only names its own challenge nova.two-factor.login).
    | When enabled, the package registers that name pointing at Nova's challenge
    | controller so the two-factor step works on top of the custom login. The route
    | is dormant unless a 2FA login redirects to it, so it is safe to leave on; set
    | NOVA_OKTA_TWO_FACTOR_CHALLENGE=false if you do not use Nova's 2FA.
    |
    */

    'two_factor_challenge_route' => (bool) env('NOVA_OKTA_TWO_FACTOR_CHALLENGE', true),

    /*
    |--------------------------------------------------------------------------
    | Show the password login form
    |--------------------------------------------------------------------------
    |
    | Whether the overridden login screen also renders the Fortify email/password
    | form alongside the Okta button. Set NOVA_OKTA_PASSWORD_LOGIN=false for an
    | Okta-only login screen.
    |
    */

    'password_login' => (bool) env('NOVA_OKTA_PASSWORD_LOGIN', true),

    /*
    |--------------------------------------------------------------------------
    | Okta behaviour (shared)
    |--------------------------------------------------------------------------
    |
    | SSO logout, verified-email enforcement and stable-identifier matching are
    | owned by the framework-agnostic base and configured in config/okta.php
    | (bbs-lab/laravel-okta). Publish it with:
    |
    |   php artisan vendor:publish --tag=okta-config
    |
    */

];
