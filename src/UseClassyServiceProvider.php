<?php

declare(strict_types=1);

namespace UseClassy\Laravel;

use Illuminate\Support\ServiceProvider;
use Illuminate\View\Compilers\BladeCompiler;

class UseClassyServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(BladeClassModifierTransformer::class);
    }

    public function boot(): void
    {
        // afterResolving covers an already-resolved blade.compiler; resolving() would not run.
        $this->callAfterResolving('blade.compiler', function (BladeCompiler $bladeCompiler): void {
            $this->registerClassModifierExtension($bladeCompiler);
        });
    }

    private function registerClassModifierExtension(BladeCompiler $bladeCompiler): void
    {
        $transformer = $this->app->make(BladeClassModifierTransformer::class);

        $bladeCompiler->extend(function (string $value) use ($transformer): string {
            return $transformer->transform($value);
        });
    }
}
