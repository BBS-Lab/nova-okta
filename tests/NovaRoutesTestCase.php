<?php

declare(strict_types=1);

namespace BBSLab\NovaOkta\Tests;

use Illuminate\Foundation\Application;
use Workbench\App\Providers\NovaServiceProvider as WorkbenchNovaServiceProvider;

/**
 * Boots the workbench Nova app provider so Nova registers its OWN auth routes
 * (nova.pages.login, nova.login, nova.two-factor.login, …). That lets tests prove
 * the package's login override actually WINS dispatch, and that the 2FA bridge
 * coexists with Nova's native challenge — things the plain harness can't show
 * because it never loads Nova's auth routes.
 */
class NovaRoutesTestCase extends TestCase
{
    /**
     * @param  Application  $app
     * @return array<int, class-string>
     */
    protected function getPackageProviders($app): array
    {
        return [
            ...parent::getPackageProviders($app),
            WorkbenchNovaServiceProvider::class,
        ];
    }
}
