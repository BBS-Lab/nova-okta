<?php

declare(strict_types=1);

use BBSLab\LaravelOkta\Facades\Okta;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Laravel\Socialite\Contracts\Provider as SocialiteProvider;
use Laravel\Socialite\Contracts\User as OktaUserContract;
use Laravel\Socialite\Facades\Socialite;
use SocialiteProviders\Manager\OAuth2\User as SocialiteUser;
use Workbench\App\Models\User;

uses(RefreshDatabase::class);

it('renders the okta login screen at the nova login path', function (): void {
    $this->get(route('nova.pages.login'))
        ->assertOk()
        ->assertSee('Log In with Okta')
        ->assertSee(route('nova-okta.login'), false);
});

it('respects the nova brand colours on the custom login screen', function (): void {
    config(['nova.brand.colors' => ['500' => '124, 58, 237']]);

    $this->get(route('nova.pages.login'))
        ->assertOk()
        // Nova's brand colours are injected, overriding the primary palette...
        ->assertSee('--colors-primary-500: 124, 58, 237', false)
        // ...and the Okta button uses that brand primary (not a hard-coded colour).
        ->assertSee('bg-primary-500', false);
});

it('renders the nova brand name when no logo is configured', function (): void {
    config(['nova.name' => 'Acme Nova', 'nova.brand.logo' => null]);

    $this->get(route('nova.pages.login'))->assertSee('Acme Nova');
});

it('shows the password form when nova login is available', function (): void {
    stubNovaLogin();

    $this->get(route('nova.pages.login'))->assertSee('name="password"', false);
});

it('hides the password form when disabled', function (): void {
    stubNovaLogin();
    config(['nova-okta.password_login' => false]);

    $this->get(route('nova.pages.login'))
        ->assertDontSee('name="password"', false)
        ->assertSee('Log In with Okta');
});

it('redirects to okta to start the login', function (): void {
    $response = $this->get(route('nova-okta.login'));

    $response->assertStatus(302);
    expect($response->headers->get('Location'))
        ->toContain('example.okta.com')
        ->toContain('authorize');
});

it('derives the redirect_uri from the nova callback route when none is configured', function (): void {
    // No explicit services.okta.redirect — it falls back to the route under the
    // Nova path, so OKTA_REDIRECT_URI is not required.
    config(['services.okta.redirect' => null]);

    $location = (string) $this->get(route('nova-okta.login'))->headers->get('Location');

    expect($location)->toContain('redirect_uri='.urlencode(route('nova-okta.callback')));
});

it('honours an explicitly configured redirect_uri', function (): void {
    config(['services.okta.redirect' => 'https://proxied.example/nova/okta/callback']);

    $location = (string) $this->get(route('nova-okta.login'))->headers->get('Location');

    expect($location)->toContain('redirect_uri='.urlencode('https://proxied.example/nova/okta/callback'));
});

it('logs in a resolved user on callback and stores the id token', function (): void {
    $user = User::factory()->create(['email' => 'okta@example.com']);

    $oktaUser = (new SocialiteUser)->map(['email' => 'okta@example.com', 'name' => 'Okta User']);
    $oktaUser->setAccessTokenResponseBody(['id_token' => 'the-id-token']);

    fakeSocialiteUser($oktaUser);

    $this->get(route('nova-okta.callback'))->assertRedirect();

    $this->assertAuthenticatedAs($user->fresh());
    expect(session('okta_authenticated'))->toBeTrue()
        ->and(session('okta_id_token'))->toBe('the-id-token');
});

it('honours the intended url after login', function (): void {
    User::factory()->create(['email' => 'intended@example.com']);

    $oktaUser = Mockery::mock(OktaUserContract::class);
    $oktaUser->shouldReceive('getEmail')->andReturn('intended@example.com');
    $oktaUser->shouldReceive('getId')->andReturn(null);
    fakeSocialiteUser($oktaUser);

    $this->withSession(['url.intended' => 'http://localhost/nova/resources/things'])
        ->get(route('nova-okta.callback'))
        ->assertRedirect('http://localhost/nova/resources/things');

    $this->assertAuthenticated();
});

it('logs in when the okta user has no access-token body (null id token)', function (): void {
    User::factory()->create(['email' => 'plain@example.com']);

    $oktaUser = Mockery::mock(OktaUserContract::class);
    $oktaUser->shouldReceive('getEmail')->andReturn('plain@example.com');
    $oktaUser->shouldReceive('getId')->andReturn(null);

    fakeSocialiteUser($oktaUser);

    $this->get(route('nova-okta.callback'))->assertRedirect();

    $this->assertAuthenticated();
    expect(session('okta_id_token'))->toBeNull();
});

it('runs the beforeLogin and afterLogin hooks on a successful login', function (): void {
    $user = User::factory()->create(['email' => 'hooks@example.com', 'logged_at' => null]);

    $order = [];
    Okta::beforeLogin(function () use (&$order): void {
        $order[] = 'before';
    });
    Okta::afterLogin(function ($loggedIn) use (&$order): void {
        $order[] = 'after';
        $loggedIn->forceFill(['logged_at' => now()])->save();
    });

    $oktaUser = Mockery::mock(OktaUserContract::class);
    $oktaUser->shouldReceive('getEmail')->andReturn('hooks@example.com');
    $oktaUser->shouldReceive('getId')->andReturn(null);
    fakeSocialiteUser($oktaUser);

    $this->get(route('nova-okta.callback'))->assertRedirect();

    $this->assertAuthenticated();
    expect($order)->toBe(['before', 'after'])
        ->and($user->fresh()->logged_at)->not->toBeNull();
});

it('rejects an unknown user and runs onLoginDenied with a null user', function (): void {
    $deniedUser = 'unset';
    Okta::onLoginDenied(function ($user) use (&$deniedUser): void {
        $deniedUser = $user;
    });

    $oktaUser = Mockery::mock(OktaUserContract::class);
    $oktaUser->shouldReceive('getEmail')->andReturn('ghost@example.com');
    $oktaUser->shouldReceive('getId')->andReturn(null);
    fakeSocialiteUser($oktaUser);

    $this->get(route('nova-okta.callback'))
        ->assertRedirect(route('nova.pages.login'))
        ->assertSessionHas('okta::error');

    $this->assertGuest();
    expect($deniedUser)->toBeNull();
});

it('rejects when an authorize callback denies the resolved user', function (): void {
    $user = User::factory()->create(['email' => 'blocked@example.com']);
    Okta::authorizeUserToLogin(fn (): bool => false);

    $denied = null;
    Okta::onLoginDenied(function ($u) use (&$denied): void {
        $denied = $u;
    });

    $oktaUser = Mockery::mock(OktaUserContract::class);
    $oktaUser->shouldReceive('getEmail')->andReturn('blocked@example.com');
    $oktaUser->shouldReceive('getId')->andReturn(null);
    fakeSocialiteUser($oktaUser);

    $this->get(route('nova-okta.callback'))
        ->assertRedirect(route('nova.pages.login'))
        ->assertSessionHas('okta::error');

    $this->assertGuest();
    expect($denied?->getKey())->toBe($user->getKey());
});

it('renders the error message on the login screen after a denied callback', function (): void {
    $oktaUser = Mockery::mock(OktaUserContract::class);
    $oktaUser->shouldReceive('getEmail')->andReturn('nobody@example.com');
    $oktaUser->shouldReceive('getId')->andReturn(null);
    fakeSocialiteUser($oktaUser);

    $this->get(route('nova-okta.callback'))->assertRedirect(route('nova.pages.login'));

    $this->get(route('nova.pages.login'))->assertSee((string) trans('okta::messages.not_allowed'));
});

it('flashes an error when the okta callback throws', function (): void {
    $provider = Mockery::mock(SocialiteProvider::class);
    $provider->shouldReceive('user')->andThrow(new RuntimeException('boom'));
    Socialite::shouldReceive('driver')->with('okta')->andReturn($provider);

    $this->get(route('nova-okta.callback'))
        ->assertRedirect(route('nova.pages.login'))
        ->assertSessionHas('okta::error');

    $this->assertGuest();
});

it('logs out via okta end-session when an id token is present', function (): void {
    $user = User::factory()->create();

    $response = $this->actingAs($user)
        ->withSession(['okta_id_token' => 'the-id-token'])
        ->get(route('nova-okta.logout'));

    $response->assertStatus(302);
    expect($response->headers->get('Location'))
        ->toContain('example.okta.com')
        ->toContain('/v1/logout')
        ->toContain('id_token_hint=the-id-token')
        ->toContain('post_logout_redirect_uri=');
    $this->assertGuest();
});

it('logs out to the login screen when no id token is present', function (): void {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('nova-okta.logout'))
        ->assertRedirect(route('nova.pages.login'));

    $this->assertGuest();
});

it('still logs out locally when the okta driver cannot be built', function (): void {
    $user = User::factory()->create();

    Socialite::shouldReceive('driver')->with('okta')->andThrow(new RuntimeException('misconfig'));

    $this->actingAs($user)
        ->withSession(['okta_id_token' => 'the-id-token'])
        ->get(route('nova-okta.logout'))
        ->assertRedirect(route('nova.pages.login'));

    $this->assertGuest();
});

it('does a local-only logout when sso logout is disabled', function (): void {
    config(['okta.sso_logout' => false]);
    $user = User::factory()->create();

    // Even with an id token, we must not redirect to Okta's end-session.
    $this->actingAs($user)
        ->withSession(['okta_id_token' => 'the-id-token'])
        ->get(route('nova-okta.logout'))
        ->assertRedirect(route('nova.pages.login'));

    $this->assertGuest();
});

it('bounces the post-logout callback back to the login screen', function (): void {
    $this->get(route('nova-okta.callback.logout'))
        ->assertRedirect(route('nova.pages.login'));
});

/**
 * Swap the Socialite okta driver for a stub whose user() returns $oktaUser.
 */
function fakeSocialiteUser(OktaUserContract $oktaUser): void
{
    $provider = Mockery::mock(SocialiteProvider::class);
    $provider->shouldReceive('user')->andReturn($oktaUser);

    Socialite::shouldReceive('driver')->with('okta')->andReturn($provider);
}

/**
 * Nova's Fortify login POST (route('nova.login')) is not registered in the
 * isolated harness, so stub it when a test renders the password form.
 */
function stubNovaLogin(): void
{
    Route::post('nova/login', fn () => '')->name('nova.login');
}
