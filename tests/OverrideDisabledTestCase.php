<?php

declare(strict_types=1);

namespace BBSLab\NovaOkta\Tests;

use Illuminate\Foundation\Application;

/**
 * Boots the package with the Nova login override disabled, so the "does not
 * hijack the login route" case can be asserted (the flag is read at boot time).
 */
class OverrideDisabledTestCase extends TestCase
{
    /**
     * @param  Application  $app
     */
    protected function defineEnvironment($app): void
    {
        parent::defineEnvironment($app);

        $app['config']->set('nova-okta.override_nova_login', false);
    }
}
