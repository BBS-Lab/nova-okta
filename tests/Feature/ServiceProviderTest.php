<?php

declare(strict_types=1);

use BBSLab\NovaOkta\Http\Controllers\OktaLoginController;
use Illuminate\Support\Facades\Route;
use Laravel\Socialite\Facades\Socialite;
use SocialiteProviders\Okta\Provider;

it('registers the okta socialite driver', function (): void {
    expect(Socialite::driver('okta'))->toBeInstanceOf(Provider::class);
});

it('registers the sso routes under the nova path', function (): void {
    expect(Route::has('nova-okta.login'))->toBeTrue()
        ->and(Route::has('nova-okta.callback'))->toBeTrue()
        ->and(Route::has('nova-okta.callback.logout'))->toBeTrue()
        ->and(Route::has('nova-okta.logout'))->toBeTrue();
});

it('overrides the nova login route by default', function (): void {
    $route = Route::getRoutes()->getByName('nova.pages.login');

    expect($route)->not->toBeNull()
        ->and($route->getActionName())->toContain(OktaLoginController::class);
});

it('applies the web middleware to the sso routes', function (): void {
    $route = Route::getRoutes()->getByName('nova-okta.login');

    expect($route->gatherMiddleware())->toContain('web');
});

it('prefixes every base okta route with the nova path', function (): void {
    expect(Route::getRoutes()->getByName('nova-okta.login')->uri())->toBe('nova/okta/login')
        ->and(Route::getRoutes()->getByName('nova-okta.callback')->uri())->toBe('nova/okta/callback')
        ->and(Route::getRoutes()->getByName('nova-okta.logout')->uri())->toBe('nova/okta/logout')
        ->and(Route::getRoutes()->getByName('nova-okta.callback.logout')->uri())->toBe('nova/okta/callback/logout');
});

it('registers the login override under the nova path with web middleware', function (): void {
    // Mirror of OverrideDisabledTest: when the override is ON (the default) the
    // package claims GET {nova}/login, names it nova.pages.login, points it at
    // the controller and carries Nova's own web middleware.
    $override = Route::getRoutes()->getByName('nova.pages.login');

    expect($override)->not->toBeNull()
        ->and($override->uri())->toBe('nova/login')
        ->and($override->methods())->toContain('GET')
        ->and($override->getActionName())->toContain(OktaLoginController::class)
        ->and($override->gatherMiddleware())->toContain('web');
});
