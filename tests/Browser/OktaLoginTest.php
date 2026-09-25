<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

/**
 * Browser (Pest v4) coverage of the pre-redirect login UX. The full SSO
 * round-trip cannot be driven end to end without a real Okta org, so these
 * scenarios assert the Nova login screen renders the Okta button and that it
 * targets the login route (which starts the OIDC redirect).
 */
it('shows the Log In with Okta button on the Nova login screen', function (): void {
    $page = visit(route('nova.pages.login'));

    $page->assertSee('Log In with Okta')
        ->assertPresent('#okta-signin-submit');
});

it('points the Okta button at the nova okta login route', function (): void {
    visit(route('nova.pages.login'))
        ->assertAttribute('#okta-signin-submit', 'href', route('nova-okta.login'));
});

it('renders the Nova login screen without JavaScript errors', function (): void {
    visit(route('nova.pages.login'))
        ->assertNoJavaScriptErrors()
        ->assertNoConsoleLogs();
});

it('shows a visible Okta button and a titled login page', function (): void {
    visit(route('nova.pages.login'))
        ->assertTitleContains('Log in')
        ->assertVisible('#okta-signin-submit')
        ->assertSeeIn('#okta-signin-submit', 'Log In with Okta');
});
