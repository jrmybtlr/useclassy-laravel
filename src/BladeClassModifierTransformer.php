<?php

declare(strict_types=1);

namespace UseClassy\Laravel;

/**
 * Rewrites class:modifier="..." on HTML opening tags into a single class attribute.
 *
 * Tag matching uses (<[^>]*>); quoted attribute values must not contain unescaped >.
 */
final class BladeClassModifierTransformer
{
    private const MARKER_PREFIX = '__USECLASSY_MODIFIER__';

    private const MARKER_SUFFIX = '__USECLASSY_END__';

    public function transform(string $value): string
    {
        // Parity with vite-plugin-useclassy: lookbehind avoids :class / someclass false
        // positives; [\w:-]+ allows hyphenated modifiers (group-hover, focus-within, etc.).
        $pattern = '/(?<![:\w])class:([\w:-]+)=(["\'`])([^"\'`]*)\2/';

        $value = preg_replace_callback($pattern, function (array $matches): string {
            $modifier = $matches[1];
            $classes = $matches[3];

            $transformedClasses = [];
            foreach (explode(' ', trim($classes)) as $class) {
                if ($class !== '') {
                    $transformedClasses[] = "{$modifier}:{$class}";
                }
            }

            $modifierClasses = implode(' ', $transformedClasses);

            return self::MARKER_PREFIX.$modifierClasses.self::MARKER_SUFFIX;
        }, $value);

        if (! is_string($value)) {
            return '';
        }

        $markerPattern = '/'.preg_quote(self::MARKER_PREFIX, '/').'(.*?)'.preg_quote(self::MARKER_SUFFIX, '/').'/s';

        $value = preg_replace_callback(
            '/(<[^>]*>)/',
            function (array $matches) use ($markerPattern): string {
                $element = $matches[1];

                if (! str_contains($element, self::MARKER_PREFIX)) {
                    return $element;
                }

                preg_match_all($markerPattern, $element, $modifierMatches);
                $allModifierClasses = implode(' ', $modifierMatches[1]);

                $cleanElement = preg_replace($markerPattern, '', $element);

                if (! is_string($cleanElement)) {
                    return $element;
                }

                if (preg_match('/\bclass=(["\'`])([^"\'`]*)\1/', $cleanElement, $classMatches)) {
                    $quote = $classMatches[1];
                    $existingClasses = $classMatches[2];
                    $combinedClasses = trim("{$existingClasses} {$allModifierClasses}");

                    return preg_replace(
                        '/\bclass=(["\'`])([^"\'`]*)\1/',
                        "class={$quote}{$combinedClasses}{$quote}",
                        $cleanElement
                    ) ?? $element;
                }

                $replaced = preg_replace('/(\s*)>$/', ' class="'.$allModifierClasses.'">', $cleanElement);

                return is_string($replaced) ? $replaced : $element;
            },
            $value
        );

        return is_string($value) ? $value : '';
    }
}
