<?php

declare(strict_types=1);

namespace BBSLab\NovaOkta\Tests;

use Illuminate\Foundation\Application;

/**
 * Boots the package with the two-factor challenge bridge route disabled, so the
 * "route absent" case can be asserted (the flag is read at boot time).
 */
class TwoFactorDisabledTestCase extends TestCase
{
    /**
     * @param  Application  $app
     */
    protected function defineEnvironment($app): void
    {
        parent::defineEnvironment($app);

        $app['config']->set('nova-okta.two_factor_challenge_route', false);
    }
}
