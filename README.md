# UseClassy Laravel Package

Laravel integration for UseClassy that transforms `class:modifier="value"` syntax in Blade templates.

## Installation

```bash
composer require useclassy/laravel
```

The service provider will be automatically registered via Laravel's package auto-discovery.

## Usage

Use the `class:modifier` syntax in your Blade templates:

```blade
<h1 class="text-xl" class:lg="text-3xl" class:hover="text-blue-600">
    Responsive heading that changes on large screens and hover
</h1>

<div class:dark="bg-gray-800 text-white" class:lg="p-6">
    Dark mode and responsive padding
</div>
```

The package will automatically transform these during Blade compilation:

- `class:lg="text-3xl"` becomes `lg:text-3xl`
- `class:hover="text-blue-600"` becomes `hover:text-blue-600`
- `class:dark="bg-gray-800 text-white"` becomes `dark:bg-gray-800 dark:text-white`

These transformed classes are merged with any existing `class` attributes.

## How it Works

This package hooks into Laravel's Blade compiler to transform the UseClassy syntax before the template is rendered. It works seamlessly with:

- Hot module reloading
- Blade caching
- All Laravel versions 10+

## Requirements

- PHP ^8.1
- Laravel ^10.0|^11.0|^12.0

## License

MIT