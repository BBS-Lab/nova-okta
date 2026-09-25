<?php

declare(strict_types=1);

use BBSLab\LaravelOkta\Enums\OktaRoute;
use BBSLab\NovaOkta\Support\NovaOktaPanel;
use Illuminate\Http\Request;
use Laravel\Nova\Nova;

it('uses the configured nova guard', function (): void {
    config(['nova.guard' => 'admins']);

    expect((new NovaOktaPanel)->guard())->toBe('admins');
});

it('falls back to the default guard when the nova guard is blank or unset', function (): void {
    config(['nova.guard' => '']);
    expect((new NovaOktaPanel)->guard())->toBeNull();

    config(['nova.guard' => null]);
    expect((new NovaOktaPanel)->guard())->toBeNull();
});

it('trims slashes from the nova path for the route prefix', function (): void {
    config(['nova.path' => '/admin/']);

    expect((new NovaOktaPanel)->routePrefix())->toBe('admin');
});

it('defaults the route prefix to the nova path', function (): void {
    expect((new NovaOktaPanel)->routePrefix())->toBe('nova');
});

it('mounts each okta route at its default authorization path', function (): void {
    $panel = new NovaOktaPanel;

    expect($panel->path(OktaRoute::Login))->toBe('authorization-code/redirect')
        ->and($panel->path(OktaRoute::Callback))->toBe('authorization-code/callback')
        ->and($panel->path(OktaRoute::Logout))->toBe('authorization-code/logout')
        ->and($panel->path(OktaRoute::CallbackLogout))->toBe('authorization-code/callback/logout');
});

it('reads the okta route paths from config (per config for Nova)', function (): void {
    config(['okta.paths.login' => 'sso/start', 'okta.paths.callback' => 'sso/cb']);

    $panel = new NovaOktaPanel;

    expect($panel->path(OktaRoute::Login))->toBe('sso/start')
        ->and($panel->path(OktaRoute::Callback))->toBe('sso/cb');
});

it('delegates the home url to nova initial path', function (): void {
    $request = Request::create('/nova');

    expect((new NovaOktaPanel)->homeUrl($request))
        ->toBe(Nova::initialPathUrl($request))
        ->toContain('/nova/');
});

it('targets nova for its routes, login screen and driver', function (): void {
    $panel = new NovaOktaPanel;

    expect($panel->routeName())->toBe('nova-okta')
        ->and($panel->middleware())->toBe(['web'])
        ->and($panel->socialiteDriver())->toBe('okta')
        ->and($panel->loginUrl())->toBe(route('nova.pages.login'))
        ->and($panel->homeUrl(Request::create('/nova')))->toBeString();
});
