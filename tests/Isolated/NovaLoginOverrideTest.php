<?php

declare(strict_types=1);

use BBSLab\NovaOkta\Http\Controllers\OktaLoginController;
use BBSLab\NovaOkta\Tests\NovaRoutesTestCase;
use Illuminate\Support\Facades\Route;
use Laravel\Nova\Http\Controllers\Fortify\TwoFactorAuthenticatedSessionController;

uses(NovaRoutesTestCase::class);

it('wins the GET login dispatch over Nova own login route', function (): void {
    // Both Nova and the package register GET {nova}/login; exactly one survives
    // in the collection, and it must be ours.
    $getLogin = collect(Route::getRoutes()->getRoutes())
        ->filter(fn ($route) => $route->uri() === 'nova/login' && in_array('GET', $route->methods(), true));

    expect($getLogin)->toHaveCount(1)
        ->and($getLogin->first()->getActionName())->toContain(OktaLoginController::class);

    $this->get(route('nova.pages.login'))
        ->assertOk()
        ->assertSee('Log In with Okta');
});

it('leaves Nova native two-factor challenge route intact', function (): void {
    // The bridge lives at a distinct URI, so Nova's own challenge + middleware survive.
    expect(Route::has('nova.two-factor.login'))->toBeTrue()
        ->and(Route::getRoutes()->getByName('nova.two-factor.login')->getActionName())
        ->toContain(TwoFactorAuthenticatedSessionController::class)
        ->and(Route::has('two-factor.login'))->toBeTrue()
        ->and(Route::getRoutes()->getByName('two-factor.login')->uri())
        ->not->toBe(Route::getRoutes()->getByName('nova.two-factor.login')->uri());
});
