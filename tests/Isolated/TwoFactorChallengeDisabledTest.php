<?php

declare(strict_types=1);

use BBSLab\NovaOkta\Tests\TwoFactorDisabledTestCase;
use Illuminate\Support\Facades\Route;

uses(TwoFactorDisabledTestCase::class);

it('does not register the two-factor challenge bridge when disabled', function (): void {
    expect(Route::has('two-factor.login'))->toBeFalse();
});
