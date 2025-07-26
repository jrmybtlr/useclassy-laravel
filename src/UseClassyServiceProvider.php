<?php

namespace UseClassy\Laravel;

use Illuminate\Support\ServiceProvider;
use Illuminate\View\Compilers\BladeCompiler;

class UseClassyServiceProvider extends ServiceProvider
{
    public function register()
    {
        //
    }

    public function boot()
    {
        // Hook into Blade compilation to transform class:modifier syntax
        $this->app->resolving('blade.compiler', function (BladeCompiler $bladeCompiler) {
            $bladeCompiler->extend(function ($value) {
                return $this->transformUseClassySyntax($value);
            });
        });
    }

    private function transformUseClassySyntax($value)
    {
        // Step 1: Transform class:modifier="value" to the modifier classes
        $pattern = '/\bclass:([\w:]+)=(["\'`])([^"\'`]*)\2/';

        $value = preg_replace_callback($pattern, function ($matches) {
            $modifier = $matches[1];
            $classes = $matches[3];

            // Transform each class with the modifier prefix
            $transformedClasses = [];
            foreach (explode(' ', trim($classes)) as $class) {
                if (! empty($class)) {
                    $transformedClasses[] = "{$modifier}:{$class}";
                }
            }

            $modifierClasses = implode(' ', $transformedClasses);

            // Return as a temporary marker to be merged later
            return "__USECLASSY_MODIFIER__{$modifierClasses}__USECLASSY_END__";
        }, $value);

        // Step 2: Process each HTML element that contains markers
        $value = preg_replace_callback(
            '/(<[^>]*>)/',
            function ($matches) {
                $element = $matches[1];

                // Check if this element has modifier markers
                if (! preg_match('/__USECLASSY_MODIFIER__/', $element)) {
                    return $element;
                }

                // Extract all modifier classes from this element
                preg_match_all('/__USECLASSY_MODIFIER__([^_]*)__USECLASSY_END__/', $element, $modifierMatches);
                $allModifierClasses = implode(' ', $modifierMatches[1]);

                // Remove the modifier markers
                $cleanElement = preg_replace('/__USECLASSY_MODIFIER__[^_]*__USECLASSY_END__/', '', $element);

                // Check if element already has a class attribute
                if (preg_match('/\bclass=(["\'`])([^"\'`]*)\1/', $cleanElement, $classMatches)) {
                    $quote = $classMatches[1];
                    $existingClasses = $classMatches[2];
                    $combinedClasses = trim("{$existingClasses} {$allModifierClasses}");

                    return preg_replace(
                        '/\bclass=(["\'`])([^"\'`]*)\1/',
                        "class={$quote}{$combinedClasses}{$quote}",
                        $cleanElement
                    );
                } else {
                    // Add class attribute before the closing >
                    return preg_replace('/(\s*)>$/', " class=\"{$allModifierClasses}\">", $cleanElement);
                }
            },
            $value
        );

        return $value;
    }
}
