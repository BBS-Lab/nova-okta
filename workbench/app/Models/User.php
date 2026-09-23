<?php

declare(strict_types=1);

namespace Workbench\App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Workbench\Database\Factories\UserFactory;

/**
 * Uses Fortify's TwoFactorAuthenticatable so Nova's built-in 2FA (and its
 * challenge route) is available. `okta_id` backs the default resolver's optional
 * identifier match; `role` / `is_sso_allowed` / `logged_at` back the Okta facade
 * hooks and the README's gated-resolver example, not the default resolver.
 */
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, TwoFactorAuthenticatable;

    /**
     * Workbench models live outside the app namespace Laravel guesses factories
     * from, so point at the workbench factory explicitly.
     */
    protected static function newFactory(): UserFactory
    {
        return UserFactory::new();
    }

    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'okta_id',
        'role',
        'is_sso_allowed',
        'logged_at',
    ];

    /**
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_secret',
        'two_factor_recovery_codes',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_sso_allowed' => 'boolean',
            'logged_at' => 'datetime',
            'two_factor_confirmed_at' => 'datetime',
        ];
    }
}
