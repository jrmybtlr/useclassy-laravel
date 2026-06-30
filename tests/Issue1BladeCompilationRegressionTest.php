<?php

declare(strict_types=1);

namespace UseClassy\Laravel\Tests;

use Illuminate\View\Compilers\BladeCompiler;
use Orchestra\Testbench\TestCase;
use UseClassy\Laravel\UseClassyServiceProvider;

/**
 * Regression tests for https://github.com/jrmybtlr/useclassy-laravel/issues/1
 *
 * The original service provider used resolving('blade.compiler', ...), which never
 * runs when the compiler was already resolved during boot. UseClassy must register
 * its Blade extension via callAfterResolving so class:modifier syntax is transformed.
 */
class Issue1BladeCompilationRegressionTest extends TestCase
{
    protected function getPackageProviders($app): array
    {
        return [];
    }

    public function test_resolving_hook_is_not_invoked_when_blade_compiler_already_resolved(): void
    {
        $this->app->make('blade.compiler');

        $invoked = false;
        $this->app->resolving('blade.compiler', function () use (&$invoked): void {
            $invoked = true;
        });

        $this->assertFalse(
            $invoked,
            'resolving() does not run for bindings that were resolved before the callback was registered (issue #1 root cause).'
        );
    }

    public function test_class_modifier_syntax_is_transformed_after_late_provider_registration(): void
    {
        $this->app->make('blade.compiler');

        $this->app->register(UseClassyServiceProvider::class);

        $compiled = $this->app->make('blade.compiler')->compileString(
            '<div class="text-xl" class:hover="bg-blue-500" class:lg="text-3xl">Heading</div>'
        );

        $this->assertStringContainsString('hover:bg-blue-500', $compiled);
        $this->assertStringContainsString('lg:text-3xl', $compiled);
        $this->assertStringNotContainsString('class:hover', $compiled);
        $this->assertStringNotContainsString('class:lg', $compiled);
    }

    public function test_blade_extension_is_registered_when_compiler_was_resolved_before_boot(): void
    {
        $compiler = $this->app->make('blade.compiler');
        $this->assertInstanceOf(BladeCompiler::class, $compiler);

        $this->app->register(UseClassyServiceProvider::class);

        $compiled = $this->app->make('blade.compiler')->compileString(
            '<span class:dark="bg-gray-800 text-white">Panel</span>'
        );

        $this->assertStringContainsString('dark:bg-gray-800', $compiled);
        $this->assertStringContainsString('dark:text-white', $compiled);
        $this->assertStringNotContainsString('class:dark', $compiled);
    }
}
