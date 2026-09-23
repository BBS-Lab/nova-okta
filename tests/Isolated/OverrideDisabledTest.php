<?php

declare(strict_types=1);

use BBSLab\NovaOkta\Http\Controllers\OktaLoginController;
use BBSLab\NovaOkta\Tests\OverrideDisabledTestCase;
use Illuminate\Support\Facades\Route;

uses(OverrideDisabledTestCase::class);

it('does not register the login override when disabled', function (): void {
    $login = Route::getRoutes()->getByName('nova.pages.login');

    // The package must not claim nova.pages.login when the override is off.
    expect($login === null || ! str_contains($login->getActionName(), OktaLoginController::class))
        ->toBeTrue();
});
