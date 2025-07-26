<?php

namespace UseClassy\Laravel\Tests;

use Illuminate\View\Compilers\BladeCompiler;
use Orchestra\Testbench\TestCase;
use UseClassy\Laravel\UseClassyServiceProvider;

class UseClassyServiceProviderTest extends TestCase
{
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

    public function test_blade_compiler_extension_is_registered(): void
    {
        $bladeCompiler = $this->app->make('blade.compiler');
        $this->assertInstanceOf(BladeCompiler::class, $bladeCompiler);
    }

    public function test_transform_single_class_modifier(): void
    {
        $serviceProvider = new UseClassyServiceProvider($this->app);
        $input = '<div class:hover="bg-blue-500">Content</div>';
        $expected = '<div class="hover:bg-blue-500">Content</div>';

        $result = $this->invokePrivateMethod($serviceProvider, 'transformUseClassySyntax', [$input]);

        $this->assertEquals($expected, $result);
    }

    public function test_transform_multiple_classes_single_modifier(): void
    {
        $serviceProvider = new UseClassyServiceProvider($this->app);
        $input = '<div class:hover="bg-blue-500 text-white">Content</div>';
        $expected = '<div class="hover:bg-blue-500 hover:text-white">Content</div>';

        $result = $this->invokePrivateMethod($serviceProvider, 'transformUseClassySyntax', [$input]);

        $this->assertEquals($expected, $result);
    }

    public function test_transform_multiple_modifiers(): void
    {
        $serviceProvider = new UseClassyServiceProvider($this->app);
        $input = '<div class:hover="bg-blue-500" class:focus="ring-2">Content</div>';
        $expected = '<div class="hover:bg-blue-500 focus:ring-2">Content</div>';

        $result = $this->invokePrivateMethod($serviceProvider, 'transformUseClassySyntax', [$input]);

        $this->assertEquals($expected, $result);
    }

    public function test_transform_with_existing_class_attribute(): void
    {
        $serviceProvider = new UseClassyServiceProvider($this->app);
        $input = '<div class="p-4 text-center" class:hover="bg-blue-500">Content</div>';
        $expected = '<div class="p-4 text-center hover:bg-blue-500" >Content</div>';

        $result = $this->invokePrivateMethod($serviceProvider, 'transformUseClassySyntax', [$input]);

        $this->assertEquals($expected, $result);
    }

    public function test_transform_with_existing_class_and_multiple_modifiers(): void
    {
        $serviceProvider = new UseClassyServiceProvider($this->app);
        $input = '<div class="p-4" class:hover="bg-blue-500" class:focus="ring-2">Content</div>';
        $expected = '<div class="p-4 hover:bg-blue-500 focus:ring-2"  >Content</div>';

        $result = $this->invokePrivateMethod($serviceProvider, 'transformUseClassySyntax', [$input]);

        $this->assertEquals($expected, $result);
    }

    public function test_transform_different_quote_types(): void
    {
        $serviceProvider = new UseClassyServiceProvider($this->app);

        // Test double quotes
        $input1 = '<div class:hover="bg-blue-500">Content</div>';
        $expected1 = '<div class="hover:bg-blue-500">Content</div>';
        $result1 = $this->invokePrivateMethod($serviceProvider, 'transformUseClassySyntax', [$input1]);
        $this->assertEquals($expected1, $result1);

        // Test single quotes (converted to double quotes)
        $input2 = "<div class:hover='bg-blue-500'>Content</div>";
        $expected2 = '<div class="hover:bg-blue-500">Content</div>';
        $result2 = $this->invokePrivateMethod($serviceProvider, 'transformUseClassySyntax', [$input2]);
        $this->assertEquals($expected2, $result2);

        // Test backticks (converted to double quotes)
        $input3 = '<div class:hover=`bg-blue-500`>Content</div>';
        $expected3 = '<div class="hover:bg-blue-500">Content</div>';
        $result3 = $this->invokePrivateMethod($serviceProvider, 'transformUseClassySyntax', [$input3]);
        $this->assertEquals($expected3, $result3);
    }

    public function test_transform_empty_class_values(): void
    {
        $serviceProvider = new UseClassyServiceProvider($this->app);
        $input = '<div class:hover="">Content</div>';
        $expected = '<div class="">Content</div>';

        $result = $this->invokePrivateMethod($serviceProvider, 'transformUseClassySyntax', [$input]);

        $this->assertEquals($expected, $result);
    }

    public function test_transform_with_extra_spaces(): void
    {
        $serviceProvider = new UseClassyServiceProvider($this->app);
        $input = '<div class:hover="  bg-blue-500   text-white  ">Content</div>';
        $expected = '<div class="hover:bg-blue-500 hover:text-white">Content</div>';

        $result = $this->invokePrivateMethod($serviceProvider, 'transformUseClassySyntax', [$input]);

        $this->assertEquals($expected, $result);
    }

    public function test_transform_complex_html_structure(): void
    {
        $serviceProvider = new UseClassyServiceProvider($this->app);
        $input = '
            <div class="container" class:hover="bg-gray-100">
                <button class:focus="ring-2" class:hover="bg-blue-500 text-white">
                    Click me
                </button>
                <span class="text-sm" class:active="font-bold">Text</span>
            </div>
        ';

        $result = $this->invokePrivateMethod($serviceProvider, 'transformUseClassySyntax', [$input]);

        $this->assertStringContainsString('class="container hover:bg-gray-100"', $result);
        $this->assertStringContainsString('class="focus:ring-2 hover:bg-blue-500 hover:text-white"', $result);
        $this->assertStringContainsString('class="text-sm active:font-bold"', $result);
    }

    public function test_transform_no_class_modifiers(): void
    {
        $serviceProvider = new UseClassyServiceProvider($this->app);
        $input = '<div class="p-4 text-center">No modifiers here</div>';
        $expected = '<div class="p-4 text-center">No modifiers here</div>';

        $result = $this->invokePrivateMethod($serviceProvider, 'transformUseClassySyntax', [$input]);

        $this->assertEquals($expected, $result);
    }

    public function test_transform_responsive_and_state_modifiers(): void
    {
        $serviceProvider = new UseClassyServiceProvider($this->app);
        $input = '<div class:md="text-lg" class:hover="bg-blue-500" class:sm="p-2">Content</div>';
        $expected = '<div class="md:text-lg hover:bg-blue-500 sm:p-2">Content</div>';

        $result = $this->invokePrivateMethod($serviceProvider, 'transformUseClassySyntax', [$input]);

        $this->assertEquals($expected, $result);
    }

    public function test_transform_with_blade_variables(): void
    {
        $serviceProvider = new UseClassyServiceProvider($this->app);
        $input = '<div class="{{ $baseClasses }}" class:hover="bg-blue-500">Content</div>';
        $expected = '<div class="{{ $baseClasses }} hover:bg-blue-500" >Content</div>';

        $result = $this->invokePrivateMethod($serviceProvider, 'transformUseClassySyntax', [$input]);

        $this->assertEquals($expected, $result);
    }

    public function test_transform_complex_pseudo_selectors(): void
    {
        $serviceProvider = new UseClassyServiceProvider($this->app);

        // Test dark:hover
        $input1 = '<div class:dark:hover="text-blue-500">Content</div>';
        $expected1 = '<div class="dark:hover:text-blue-500">Content</div>';
        $result1 = $this->invokePrivateMethod($serviceProvider, 'transformUseClassySyntax', [$input1]);
        $this->assertEquals($expected1, $result1);

        // Test md:hover
        $input2 = '<div class:md:hover="bg-red-500">Content</div>';
        $expected2 = '<div class="md:hover:bg-red-500">Content</div>';
        $result2 = $this->invokePrivateMethod($serviceProvider, 'transformUseClassySyntax', [$input2]);
        $this->assertEquals($expected2, $result2);

        // Test lg:dark:focus
        $input3 = '<div class:lg:dark:focus="ring-2">Content</div>';
        $expected3 = '<div class="lg:dark:focus:ring-2">Content</div>';
        $result3 = $this->invokePrivateMethod($serviceProvider, 'transformUseClassySyntax', [$input3]);
        $this->assertEquals($expected3, $result3);

        // Test multiple complex modifiers on same element
        $input4 = '<div class="p-4" class:dark:hover="text-blue-500" class:md:focus="ring-2">Content</div>';
        $expected4 = '<div class="p-4 dark:hover:text-blue-500 md:focus:ring-2"  >Content</div>';
        $result4 = $this->invokePrivateMethod($serviceProvider, 'transformUseClassySyntax', [$input4]);
        $this->assertEquals($expected4, $result4);
    }

    /**
     * Invoke a private method for testing purposes
     */
    private function invokePrivateMethod($object, $methodName, array $parameters = [])
    {
        $reflection = new \ReflectionClass(get_class($object));
        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);

        return $method->invokeArgs($object, $parameters);
    }
}
