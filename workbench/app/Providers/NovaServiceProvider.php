<?php

declare(strict_types=1);

namespace Workbench\App\Providers;

use Illuminate\Support\Facades\Gate;
use Laravel\Fortify\Features;
use Laravel\Nova\Dashboard;
use Laravel\Nova\Dashboards\Main;
use Laravel\Nova\Nova;
use Laravel\Nova\NovaApplicationServiceProvider;
use Laravel\Nova\Tool;
use Workbench\App\Nova\User;

class NovaServiceProvider extends NovaApplicationServiceProvider
{
    public function boot(): void
    {
        // Enable Nova's built-in (Fortify) 2FA BEFORE parent::boot() runs
        // routes(): Nova only registers the two-factor challenge routes
        // (nova.two-factor.login) when the 2FA feature is active at that point.
        Nova::fortify()
            ->features([
                Features::twoFactorAuthentication(['confirm' => true, 'confirmPassword' => true]),
            ])
            ->register();

        parent::boot();
    }

    protected function routes(): void
    {
        Nova::routes()
            ->withAuthenticationRoutes(default: true)
            ->withPasswordResetRoutes()
            ->register();
    }

    protected function gate(): void
    {
        Gate::define('viewNova', fn ($user) => true);
    }

    /**
     * @return array<int, Dashboard>
     */
    protected function dashboards(): array
    {
        return [
            new Main,
        ];
    }

    /**
     * @return array<int, Tool>
     */
    public function tools(): array
    {
        return [];
    }

    protected function resources(): void
    {
        Nova::resources([
            User::class,
        ]);
    }
}
