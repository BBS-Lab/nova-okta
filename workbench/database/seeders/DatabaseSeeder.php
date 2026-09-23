<?php

declare(strict_types=1);

namespace Workbench\Database\Seeders;

use Illuminate\Database\Seeder;
use Workbench\App\Models\User;

class DatabaseSeeder extends Seeder
{
    /**
     * A single SSO-allowed admin so the package can be tried end-to-end with
     * `composer serve` (password is "password").
     */
    public function run(): void
    {
        User::query()->firstOrCreate(
            ['email' => 'nova@laravel.com'],
            [
                'name' => 'Laravel Nova',
                'password' => 'password',
                'role' => 'admin',
                'is_sso_allowed' => true,
            ],
        );
    }
}
