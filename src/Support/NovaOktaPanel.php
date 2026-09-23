<?php

declare(strict_types=1);

namespace BBSLab\NovaOkta\Support;

use BBSLab\LaravelOkta\Support\ConfigOktaPanel;
use Illuminate\Http\Request;
use Laravel\Nova\Nova;

/**
 * The Nova panel seam: points the framework-agnostic Okta flow at Nova's login
 * screen, guard and path. Okta behaviour (sso_logout, verified-email,
 * identifier) comes from the shared config('okta.*') via ConfigOktaPanel.
 */
class NovaOktaPanel extends ConfigOktaPanel
{
    public function guard(): ?string
    {
        $guard = config('nova.guard');

        return is_string($guard) && $guard !== '' ? $guard : null;
    }

    public function loginUrl(): string
    {
        return route('nova.pages.login');
    }

    public function homeUrl(Request $request): string
    {
        return Nova::initialPathUrl($request);
    }

    public function routePrefix(): string
    {
        return trim(Nova::path(), '/');
    }

    public function routeName(): string
    {
        return 'nova-okta';
    }

    public function middleware(): array
    {
        return ['web'];
    }
}
