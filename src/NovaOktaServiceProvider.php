<?php

declare(strict_types=1);

namespace BBSLab\NovaOkta;

use BBSLab\LaravelOkta\Contracts\OktaPanel;
use BBSLab\LaravelOkta\Support\OktaRoutes;
use BBSLab\NovaOkta\Http\Controllers\OktaLoginController;
use BBSLab\NovaOkta\Support\NovaOktaPanel;
use Illuminate\Support\Facades\Route;
use Laravel\Nova\Nova;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class NovaOktaServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('nova-okta')
            ->hasConfigFile()
            ->hasTranslations()
            ->hasViews();
    }

    public function packageRegistered(): void
    {
        // Point the framework-agnostic Okta flow (bbs-lab/laravel-okta) at Nova.
        // Overrides the base default (NullOktaPanel); the base still owns the
        // Socialite driver, the controller, the resolver and the facade hooks.
        $this->app->bind(OktaPanel::class, NovaOktaPanel::class);
    }

    public function packageBooted(): void
    {
        // Nova registers its own auth routes during its provider's boot(). We
        // register ours once the whole application has booted so the login
        // override reliably takes precedence over Nova's default login route.
        $this->app->booted(function (): void {
            $panel = $this->app->make(OktaPanel::class);

            OktaRoutes::register($panel);
            $this->registerNovaRoutes($panel);
        });
    }

    /**
     * Nova-specific routes on top of the base okta/* routes: the login-screen
     * override and the Fortify two-factor challenge bridge.
     */
    protected function registerNovaRoutes(OktaPanel $panel): void
    {
        Route::group([
            'prefix' => $panel->routePrefix(),
            'middleware' => $panel->middleware(),
        ], function (): void {
            if (config('nova-okta.override_nova_login')) {
                // Same URI + name as Nova's login GET; registered last so it wins.
                Route::get('login', OktaLoginController::class)
                    ->name('nova.pages.login');
            }

            // Fortify redirects a 2FA-enabled login to route('two-factor.login'),
            // a name Nova never registers (it only names its own challenge
            // nova.two-factor.login, and calls Fortify::ignoreRoutes()). We must
            // NOT re-register Nova's challenge URI ourselves: that would strip its
            // `nova` middleware (breaking the Inertia challenge page) and, under
            // route:cache, drop Nova's own nova.two-factor.login. Instead expose the
            // name at a distinct URI that redirects to Nova's native challenge.
            if (config('nova-okta.two_factor_challenge_route')) {
                Route::redirect('okta/two-factor-challenge', Nova::url('/user-security/two-factor-challenge'))
                    ->name('two-factor.login');
            }
        });
    }
}
