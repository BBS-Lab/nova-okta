<?php

declare(strict_types=1);

namespace BBSLab\NovaOkta\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\View as ViewFactory;

class OktaLoginController
{
    /**
     * Render the package's Nova-styled login screen (with the Okta button).
     */
    public function __invoke(): View
    {
        return ViewFactory::make('nova-okta::login');
    }
}
