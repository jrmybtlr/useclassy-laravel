# Sync useclassy/laravel with vite-plugin-useclassy Phase 1

- [x] Expand Blade modifier regex to `[\w/:@-]+` (named groups + container queries)
- [x] Use `#` regex delimiters so `/` in named groups is not treated as the pattern end
- [x] Add tests for `group-hover/item`, `@md`, and full-chain-only `sm:hover`
- [x] Document UnoCSS companion setup and `/` / `@` modifier names
- [x] Run PHPUnit to confirm new parity tests and existing suite pass

## Review

- Regex now matches Vite `CLASS_MODIFIER_NAME_PATTERN`: hyphens, named groups (`group-hover/item`), container queries (`@md`)
- PHP `#…#` delimiters are required; `/…/` treated `/` in `[\w/:@-]` as the end of the pattern
- Chained modifiers already emitted the full chain only (`sm:hover:underline`); added an explicit regression test
- README covers Tailwind *and* UnoCSS manifest wiring (Blade is outside Vite's module graph)
- PHPUnit: 25 tests, 47 assertions, OK
