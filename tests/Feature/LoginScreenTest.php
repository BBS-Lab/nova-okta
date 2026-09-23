<?php

declare(strict_types=1);

it('marks the login screen noindex so it is never crawled', function (): void {
    $this->get(route('nova.pages.login'))
        ->assertOk()
        ->assertSee('<meta name="robots" content="noindex">', false);
});

it('localises the okta button and the page title', function (): void {
    app()->setLocale('fr');

    $this->get(route('nova.pages.login'))
        ->assertOk()
        ->assertSee('Se connecter avec Okta')
        ->assertSee('Connexion');
});

it('spaces the okta button from the password form only when the form shows', function (): void {
    // The mt-6 gap between the password form and the Okta button is driven by the
    // password_login flag, so it appears with the form and vanishes on an Okta-only screen.
    stubNovaLogin();

    $this->get(route('nova.pages.login'))
        ->assertSee('name="password"', false)
        ->assertSee('mt-6', false);

    config(['nova-okta.password_login' => false]);

    $this->get(route('nova.pages.login'))
        ->assertDontSee('name="password"', false)
        ->assertDontSee('mt-6', false);
});
