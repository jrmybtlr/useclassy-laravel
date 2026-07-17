# UseClassy Laravel Package

Laravel integration for UseClassy that transforms `class:modifier="value"` syntax in Blade templates.

## Installation

```bash
composer require useclassy/laravel
```

The service provider will be automatically registered via Laravel's package auto-discovery.

### Vite + Tailwind companion

This package rewrites Blade at compile time. For Tailwind JIT to discover the generated variant classes, also install [`vite-plugin-useclassy`](https://www.npmjs.com/package/vite-plugin-useclassy) in your app and set `language: "blade"`:

```ts
// vite.config.ts
import useClassy from "vite-plugin-useclassy";

export default {
  plugins: [
    useClassy({
      language: "blade",
    }),
    // ... other plugins
  ],
};
```

Point Tailwind at the plugin manifest (default `.classy/output.classy.html`):

- **Tailwind v4** — in your CSS entry: `@source "./.classy/output.classy.html";` (path relative to that CSS file)
- **Tailwind v3** — add `"./.classy/output.classy.html"` to the `content` array in `tailwind.config.*`

Or run `npx vite-plugin-useclassy init --language blade` from your app root to patch Vite, Tailwind, and editor settings when possible.

## Usage

Use the `class:modifier` syntax in your Blade templates:

```blade
<h1 class="text-xl" class:lg="text-3xl" class:hover="text-blue-600">
    Responsive heading that changes on large screens and hover
</h1>

<div class:dark="bg-gray-800 text-white" class:lg="p-6" class:group-hover="opacity-100">
    Dark mode, responsive padding, and group hover
</div>
```

The package will automatically transform these during Blade compilation:

- `class:lg="text-3xl"` becomes `lg:text-3xl`
- `class:hover="text-blue-600"` becomes `hover:text-blue-600`
- `class:dark="bg-gray-800 text-white"` becomes `dark:bg-gray-800 dark:text-white`
- `class:group-hover="opacity-100"` becomes `group-hover:opacity-100`

These transformed classes are merged with any existing `class` attributes.

## How it Works

This package hooks into Laravel's Blade compiler to transform the UseClassy syntax before the template is rendered. It works seamlessly with:

- Hot module reloading
- Blade caching
- Laravel 11, 12, and 13

## Requirements

- PHP ^8.2
- Laravel ^11.0|^12.0|^13.0

## License

MIT
