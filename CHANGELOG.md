# Changelog

All notable changes to `bbs-lab/nova-okta` will be documented in this file.

## v1.0.0 - 2026-09-23

Okta SSO for Laravel Nova — the Nova adapter for [bbs-lab/laravel-okta](https://github.com/BBS-Lab/laravel-okta).

### ✨ Features

- Custom Nova-styled login screen with a **Log In with Okta** button (overrides Nova's login GET; optional password form). Respects your Nova branding: renders `nova.brand.logo` (or the name), injects `nova.brand.colors`, and the Okta button uses the brand primary colour.
- Mounts the base `okta/login`, `okta/callback`, `okta/logout`, `okta/callback/logout` routes under the Nova path, plus an optional Fortify `two-factor.login` challenge bridge.
- `NovaOktaPanel` points the base flow at Nova's guard and path. `OKTA_REDIRECT_URI` is optional — the redirect_uri is derived from the `okta/callback` route under the Nova path.
- Reuses `bbs-lab/laravel-okta` entirely: the Socialite Okta driver, the login / callback / logout controller, the default resolver and the `Okta` lifecycle hooks come from the base (installed automatically). Okta behaviour config (`sso_logout`, `require_verified_email`, `identifier`) lives in the base `config/okta.php`; `config/nova-okta.php` keeps only the Nova-specific options (`override_nova_login`, `two_factor_challenge_route`, `password_login`).
- Configurable SSO logout (`sso_logout`, default on): logout ends the Okta session via OIDC end-session, or clears only the local session when disabled.
- Bindable `OktaUserResolver` (default: maps an Okta account to a local user via the auth guard's user provider, by email — never creates a user) and the `Okta` facade of login-lifecycle hooks — `resolveUserUsing`, `authorizeUserToLogin`, `beforeLogin`, `afterLogin`, `onLoginDenied` — so authorization and side effects (last-login stamp, provisioning, audit) are opt-in callbacks instead of baked-in config.
- Optional stable-identifier matching (`okta.identifier`): match on the Okta `sub` via a configurable column first, fall back to verified email, and backfill the column on first match.
- Sets an `okta_authenticated` session flag for interop with `bbs-lab/nova-force-two-factor`.

### 🔒 Security

- The default resolver rejects unverified Okta emails via the `email_verified` claim (`require_verified_email`, on by default).
- The `two-factor.login` bridge redirects to Nova's native challenge from a distinct URI, so Nova keeps its middleware and the route survives `route:cache`.
- Logout always clears local auth even if the Okta driver is misconfigured.
- The stable-identifier link is only ever backfilled from a **verified** email, so an unverified assertion can never persist a durable account link (the identifier column must be unique).

### 📦 Requirements

- PHP 8.2+, Laravel 11/12/13, Nova 5.
