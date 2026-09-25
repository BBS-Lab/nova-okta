# Changelog

All notable changes to `bbs-lab/nova-okta` will be documented in this file.

## v2.0.0 - 2026-09-25

### ⚠️ Breaking

- Requires [`bbs-lab/laravel-okta` v2.0](https://github.com/BBS-Lab/laravel-okta/releases/tag/v2.0.0), which **changes the default Okta route paths** from `okta/*` to `authorization-code/*`:

  | Purpose | Before | After (default) |
  |---------|--------|-----------------|
  | Login | `{nova-path}/okta/login` | `{nova-path}/authorization-code/redirect` |
  | Callback (redirect URI) | `{nova-path}/okta/callback` | `{nova-path}/authorization-code/callback` |
  | Logout | `{nova-path}/okta/logout` | `{nova-path}/authorization-code/logout` |
  | Post-logout landing | `{nova-path}/okta/callback/logout` | `{nova-path}/authorization-code/callback/logout` |

  **Action required:** update your Okta application's **Sign-in** and **Sign-out redirect URIs** in the Okta admin console, or logins fail with a `redirect_uri` mismatch (400). An explicit `services.{driver}.redirect` still wins.

- The route **names are unchanged** (`nova-okta.login`, `.callback`, `.logout`, `.callback.logout`), so `route()` callers, the login button and the derived redirect URI keep working. The `two-factor.login` bridge (`okta/two-factor-challenge`) is unchanged.

### ✨ Added

- The Okta route paths are **configurable** — for Nova, via the base `okta.paths.*` config (env `OKTA_LOGIN_PATH`, `OKTA_CALLBACK_PATH`, `OKTA_LOGOUT_PATH`, `OKTA_CALLBACK_LOGOUT_PATH`). `NovaOktaPanel` inherits this from `ConfigOktaPanel`.

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
