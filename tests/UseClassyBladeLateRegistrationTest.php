<?php

declare(strict_types=1);

namespace UseClassy\Laravel\Tests;

use Orchestra\Testbench\TestCase;
use UseClassy\Laravel\UseClassyServiceProvider;

class UseClassyBladeLateRegistrationTest extends TestCase
{
    protected function getPackageProviders($app): array
    {
        return [];
    }

    public function test_blade_extension_when_compiler_resolved_before_provider(): void
    {
        $this->app->make('blade.compiler');

        $this->app->register(UseClassyServiceProvider::class);

        $compiled = $this->app->make('blade.compiler')->compileString(
            '<div class:hover="bg-blue-500">X</div>'
        );

        $this->assertStringContainsString('hover:bg-blue-500', $compiled);
        $this->assertStringNotContainsString('class:hover', $compiled);
    }
}
