<?php

declare(strict_types=1);

use BBSLab\NovaOkta\Tests\TestCase;

uses(TestCase::class)->in('Feature', 'Unit');

// Browser tests (Pest v4 + Playwright) need a browser, so they are grouped and
// excluded from the default suite — run them with `composer test:browser`.
uses(TestCase::class)->group('browser')->in('Browser');
