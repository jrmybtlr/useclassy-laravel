<?php

declare(strict_types=1);

namespace UseClassy\Laravel\Tests;

use Illuminate\View\Compilers\BladeCompiler;
use Orchestra\Testbench\TestCase;
use UseClassy\Laravel\BladeClassModifierTransformer;
use UseClassy\Laravel\UseClassyServiceProvider;

class UseClassyServiceProviderTest extends TestCase
{
    private function transformer(): BladeClassModifierTransformer
    {
        return new BladeClassModifierTransformer;
    }

    protected function getPackageProviders($app): array
    {
        return [
            UseClassyServiceProvider::class,
        ];
    }

    public function test_service_provider_registers_correctly(): void
    {
        $this->assertTrue(
            $this->app->getProvider(UseClassyServiceProvider::class) instanceof UseClassyServiceProvider
        );
    }

    public function test_blade_compiler_applies_useclassy_transform(): void
    {
        $bladeCompiler = $this->app->make('blade.compiler');
        $this->assertInstanceOf(BladeCompiler::class, $bladeCompiler);

        $compiled = $bladeCompiler->compileString('<div class:hover="bg-blue-500">X</div>');

        $this->assertStringContainsString('hover:bg-blue-500', $compiled);
        $this->assertStringNotContainsString('class:hover', $compiled);
    }

    public function test_transform_single_class_modifier(): void
    {
        $input = '<div class:hover="bg-blue-500">Content</div>';
        $expected = '<div class="hover:bg-blue-500">Content</div>';

        $result = $this->transformer()->transform($input);

        $this->assertEquals($expected, $result);
    }

    public function test_transform_multiple_classes_single_modifier(): void
    {
        $input = '<div class:hover="bg-blue-500 text-white">Content</div>';
        $expected = '<div class="hover:bg-blue-500 hover:text-white">Content</div>';

        $result = $this->transformer()->transform($input);

        $this->assertEquals($expected, $result);
    }

    public function test_transform_multiple_modifiers(): void
    {
        $input = '<div class:hover="bg-blue-500" class:focus="ring-2">Content</div>';
        $expected = '<div class="hover:bg-blue-500 focus:ring-2">Content</div>';

        $result = $this->transformer()->transform($input);

        $this->assertEquals($expected, $result);
    }

    public function test_transform_with_existing_class_attribute(): void
    {
        $input = '<div class="p-4 text-center" class:hover="bg-blue-500">Content</div>';
        $expected = '<div class="p-4 text-center hover:bg-blue-500" >Content</div>';

        $result = $this->transformer()->transform($input);

        $this->assertEquals($expected, $result);
    }

    public function test_transform_with_existing_class_and_multiple_modifiers(): void
    {
        $input = '<div class="p-4" class:hover="bg-blue-500" class:focus="ring-2">Content</div>';
        $expected = '<div class="p-4 hover:bg-blue-500 focus:ring-2"  >Content</div>';

        $result = $this->transformer()->transform($input);

        $this->assertEquals($expected, $result);
    }

    public function test_transform_different_quote_types(): void
    {
        $t = $this->transformer();

        $input1 = '<div class:hover="bg-blue-500">Content</div>';
        $expected1 = '<div class="hover:bg-blue-500">Content</div>';
        $this->assertEquals($expected1, $t->transform($input1));

        $input2 = "<div class:hover='bg-blue-500'>Content</div>";
        $expected2 = '<div class="hover:bg-blue-500">Content</div>';
        $this->assertEquals($expected2, $t->transform($input2));

        $input3 = '<div class:hover=`bg-blue-500`>Content</div>';
        $expected3 = '<div class="hover:bg-blue-500">Content</div>';
        $this->assertEquals($expected3, $t->transform($input3));
    }

    public function test_transform_empty_class_values(): void
    {
        $input = '<div class:hover="">Content</div>';
        $expected = '<div class="">Content</div>';

        $result = $this->transformer()->transform($input);

        $this->assertEquals($expected, $result);
    }

    public function test_transform_with_extra_spaces(): void
    {
        $input = '<div class:hover="  bg-blue-500   text-white  ">Content</div>';
        $expected = '<div class="hover:bg-blue-500 hover:text-white">Content</div>';

        $result = $this->transformer()->transform($input);

        $this->assertEquals($expected, $result);
    }

    public function test_transform_complex_html_structure(): void
    {
        $input = '
            <div class="container" class:hover="bg-gray-100">
                <button class:focus="ring-2" class:hover="bg-blue-500 text-white">
                    Click me
                </button>
                <span class="text-sm" class:active="font-bold">Text</span>
            </div>
        ';

        $result = $this->transformer()->transform($input);

        $this->assertStringContainsString('class="container hover:bg-gray-100"', $result);
        $this->assertStringContainsString('class="focus:ring-2 hover:bg-blue-500 hover:text-white"', $result);
        $this->assertStringContainsString('class="text-sm active:font-bold"', $result);
    }

    public function test_transform_no_class_modifiers(): void
    {
        $input = '<div class="p-4 text-center">No modifiers here</div>';
        $expected = '<div class="p-4 text-center">No modifiers here</div>';

        $result = $this->transformer()->transform($input);

        $this->assertEquals($expected, $result);
    }

    public function test_transform_responsive_and_state_modifiers(): void
    {
        $input = '<div class:md="text-lg" class:hover="bg-blue-500" class:sm="p-2">Content</div>';
        $expected = '<div class="md:text-lg hover:bg-blue-500 sm:p-2">Content</div>';

        $result = $this->transformer()->transform($input);

        $this->assertEquals($expected, $result);
    }

    public function test_transform_with_blade_variables(): void
    {
        $input = '<div class="{{ $baseClasses }}" class:hover="bg-blue-500">Content</div>';
        $expected = '<div class="{{ $baseClasses }} hover:bg-blue-500" >Content</div>';

        $result = $this->transformer()->transform($input);

        $this->assertEquals($expected, $result);
    }

    public function test_transform_complex_pseudo_selectors(): void
    {
        $t = $this->transformer();

        $input1 = '<div class:dark:hover="text-blue-500">Content</div>';
        $expected1 = '<div class="dark:hover:text-blue-500">Content</div>';
        $this->assertEquals($expected1, $t->transform($input1));

        $input2 = '<div class:md:hover="bg-red-500">Content</div>';
        $expected2 = '<div class="md:hover:bg-red-500">Content</div>';
        $this->assertEquals($expected2, $t->transform($input2));

        $input3 = '<div class:lg:dark:focus="ring-2">Content</div>';
        $expected3 = '<div class="lg:dark:focus:ring-2">Content</div>';
        $this->assertEquals($expected3, $t->transform($input3));

        $input4 = '<div class="p-4" class:dark:hover="text-blue-500" class:md:focus="ring-2">Content</div>';
        $expected4 = '<div class="p-4 dark:hover:text-blue-500 md:focus:ring-2"  >Content</div>';
        $this->assertEquals($expected4, $t->transform($input4));
    }

    public function test_transform_preserves_underscores_in_utility_classes(): void
    {
        $input = '<div class:hover="my_custom_utility">Content</div>';
        $expected = '<div class="hover:my_custom_utility">Content</div>';

        $result = $this->transformer()->transform($input);

        $this->assertEquals($expected, $result);
    }

    public function test_transform_hyphenated_modifiers(): void
    {
        $t = $this->transformer();

        $this->assertEquals(
            '<div class="group-hover:bg-blue-100">Content</div>',
            $t->transform('<div class:group-hover="bg-blue-100">Content</div>')
        );

        $this->assertEquals(
            '<div class="focus-within:ring-2">Content</div>',
            $t->transform('<div class:focus-within="ring-2">Content</div>')
        );

        $this->assertEquals(
            '<div class="peer-focus:opacity-100">Content</div>',
            $t->transform('<div class:peer-focus="opacity-100">Content</div>')
        );
    }

    public function test_transform_does_not_match_class_substring_in_larger_word(): void
    {
        $input = '<div someclass:hover="text-blue-500">Content</div>';

        $result = $this->transformer()->transform($input);

        $this->assertEquals($input, $result);
    }

    public function test_transform_does_not_match_colon_prefixed_class_hover(): void
    {
        $input = '<div :class:hover="text-blue-500">Content</div>';

        $result = $this->transformer()->transform($input);

        $this->assertEquals($input, $result);
    }
}
