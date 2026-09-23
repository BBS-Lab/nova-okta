<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

it('registers the two-factor.login bridge that redirects to the nova challenge', function (): void {
    expect(Route::has('two-factor.login'))->toBeTrue();

    $response = $this->get(route('two-factor.login'));

    $response->assertStatus(302);
    expect($response->headers->get('Location'))->toContain('user-security/two-factor-challenge');
});

it('keeps the bridge at a distinct uri so it cannot strip the native nova challenge', function (): void {
    // The bridge must live at a distinct URI so Nova's own nova.two-factor.login
    // (and its middleware) survive — including under route:cache.
    expect(Route::getRoutes()->getByName('two-factor.login')->uri())
        ->not->toContain('user-security/two-factor-challenge');
});

it('registers the bridge under the nova path with web middleware', function (): void {
    $bridge = Route::getRoutes()->getByName('two-factor.login');

    expect($bridge->uri())->toBe('nova/okta/two-factor-challenge')
        ->and($bridge->gatherMiddleware())->toContain('web');
});
