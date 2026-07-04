# Views Vanilla JavaScript Reveal (VVJR)

A vanilla-JavaScript reveal-card grid **Views format** for Drupal 11.3+ / 12.
Renders Views rows as cards that swap their displayed content for a "revealed"
content layer on hover or click — with six animation styles, configurable
speed/easing, breakpoint-gated activation, and AAA-conformant ARIA state
management.

v2.0 builds on the **`drupal/vvj_core` foundation** (composer auto-installs it).
Behavior runs in a `<vvjr-reveal>` custom element extending `VvjElementBase`.

## Features

- Hover and click triggers (Enter/Space keyboard fallback)
- Six animation styles: fade / zoom / slide-from-top / slide-from-right / slide-
  from-bottom / slide-from-left
- Configurable reveal speed (0.1 - 2.0 s) + 5 easing curves
- Configurable box height, min-width, grid gap
- Front + back background colors (independent)
- 6 responsive breakpoints (`all` / 576 / 768 / 992 / 1200 / 1400 px) — reveal
  active above the chosen viewport
- ARIA `aria-hidden` and `tabindex` synchronized to the revealed state
- On viewports below the breakpoint: both sides visible, reveal disabled, every
  nested element tabbable
- Lazy IntersectionObserver hydration; AbortController-tracked listeners
- Throttled resize handler keeps state in sync with viewport changes
- **Views tokens** — `[vvjr:field]` / `[vvjr:field:plain]` in Views text areas
  with *Use replacement tokens from the first row*
- **Drupal admin help** — `/admin/help/vvjr`

## Token Support in Views Text Areas

In Views headers, footers, or empty text with *Use replacement tokens from the
first row*, default Twig tokens (`{{ title }}`) do not work. Use VVJR tokens
instead:

- `{{ title }}` → `[vvjr:title]`
- `{{ field_image }}` → `[vvjr:field_image]`
- Append `:plain` for plain text: `[vvjr:title:plain]`

v2 resolves tokens through the shared `vvj_core.token_resolver` service; syntax
is unchanged from v1. Tokens read from the **first row** of rendered View
fields. Complex field rewrites are not supported.

## Installation

```bash
composer require drupal/vvjr:^2.0
drush en vvjr
```

## Usage

1. Create or edit a View.
2. Set **Format** to *Views Vanilla JavaScript Reveal*.
3. Set **Show** to *Fields*.
4. Place a `<div class="vvjr-separator"></div>` field between the displayed and
   revealed fields. Everything before becomes the front; everything after
   becomes the back.
5. Configure responsive / dimensions / reveal / colors in the format settings.
6. Save.

**Sample view:** Optional config `views.view.vvjr_example` (`config/optional/`)
installs when there is no ID conflict.

## Drop-in upgrade from 1.x

`composer update drupal/vvjr && drush updb && drush cr` is sufficient. v2
preserves:

- Plugin ID `views_vvjr`, theme hook `views_view_vvjr`
- Twig template names
- All option keys
- Library names (`vvjr`, `vvjr-style`, `vvjr__all/576/768/992/1200/1400`)
- JS behavior key `Drupal.behaviors.VVJReveal`
- All CSS class names (`.vvjr`, `.reveal-inner`, `.reveal-item`, `.front-box`,
  `.back-box`, `.revealed`, animation classes)
- Separator marker `<div class="vvjr-separator"></div>`

Outer rendered tag changed from `<div>` to `<vvjr-reveal>`. Theme selectors
targeting `.vvjr` keep matching.

## License

GPL-2.0-or-later.
