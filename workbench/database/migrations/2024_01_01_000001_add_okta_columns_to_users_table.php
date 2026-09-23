<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            // okta_id backs the default resolver's identifier match (unique, as a
            // real app must); role/is_sso_allowed/logged_at back the hooks + the
            // README gated-resolver demo.
            $table->string('okta_id')->nullable()->unique()->after('password');
            $table->string('role')->nullable()->after('okta_id');
            $table->boolean('is_sso_allowed')->default(false)->after('role');
            $table->timestamp('logged_at')->nullable()->after('is_sso_allowed');

            // Fortify's 2FA columns, so Nova's built-in two-factor (and its
            // challenge route) is available in the workbench.
            $table->text('two_factor_secret')->nullable()->after('logged_at');
            $table->text('two_factor_recovery_codes')->nullable()->after('two_factor_secret');
            $table->timestamp('two_factor_confirmed_at')->nullable()->after('two_factor_recovery_codes');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropColumn([
                'okta_id',
                'role',
                'is_sso_allowed',
                'logged_at',
                'two_factor_secret',
                'two_factor_recovery_codes',
                'two_factor_confirmed_at',
            ]);
        });
    }
};
