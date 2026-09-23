<?php

declare(strict_types=1);

use BBSLab\NovaOkta\Tests\NovaRoutesTestCase;
use Illuminate\Support\Facades\Route;

uses(NovaRoutesTestCase::class);

it('links to Nova password reset when that route exists', function (): void {
    // The workbench Nova app registers the password-reset routes, so the screen
    // must surface the forgot-password link pointing at Nova's own route.
    expect(Route::has('nova.pages.password.email'))->toBeTrue();

    $this->get(route('nova.pages.login'))
        ->assertOk()
        ->assertSee(route('nova.pages.password.email'), false)
        ->assertSee((string) trans('nova-okta::messages.forgot_password'));
});

it('points the password form at Nova own login POST', function (): void {
    expect(Route::has('nova.login'))->toBeTrue();

    $this->get(route('nova.pages.login'))
        ->assertOk()
        ->assertSee('action="'.route('nova.login').'"', false)
        ->assertSee('name="password"', false)
        ->assertSee('Log In with Okta');
});
