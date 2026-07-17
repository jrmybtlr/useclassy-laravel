# Sync useclassy/laravel with vite-plugin-useclassy 3.x

- [x] Update BladeClassModifierTransformer regex: lookbehind + hyphens
- [x] Add tests for group-hover/focus-within and false-positive guards
- [x] Document Vite blade language + Tailwind manifest companion setup
- [x] Run PHPUnit to confirm parity tests and existing suite pass

## Review

- Regex in `BladeClassModifierTransformer` now matches Vite 3.x: `(?<![:\w])class:([\w:-]+)=...`
- Hyphenated modifiers (`group-hover`, `focus-within`, `peer-focus`) transform correctly
- False positives (`someclass:hover`, `:class:hover`) left untouched
- README documents Vite `language: "blade"` + Tailwind `@source` / `content` companion setup
- PHPUnit: 23 tests, 41 assertions, OK
