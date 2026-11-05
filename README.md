# Views Vanilla JavaScript Reveal (VVJR)

A vanilla-JavaScript reveal-card grid **Views format** for Drupal.
Renders Views rows as cards that swap their displayed content for a "revealed"
content layer on hover or click, with six animation styles, configurable
speed/easing, breakpoint-gated activation, and AAA-conformant ARIA state
management.

v2.0 builds on the **`drupal/vvj_core` foundation** (composer auto-installs it).
Behavior runs in a `<vvjr-reveal>` custom element extending `VvjElementBase`.

## Table of contents

- Overview
- Requirements
- Installation
- Configuration
- Patterns
- Usage
- Troubleshooting
- Changelog
- Maintainers
- License

## Overview

### Features

- Hover and click triggers (Enter/Space keyboard fallback)
- Six animation styles: fade / zoom / slide-from-top / slide-from-right / slide-from-bottom / slide-from-left
- Configurable reveal speed (0.1 - 2.0 s) + 5 easing curves
- Configurable box height, min-width, grid gap
- Front + back background colors (independent)
- 6 responsive breakpoints (`all` / 576 / 768 / 992 / 1200 / 1400 px). Reveal
  active above the chosen viewport
- ARIA `aria-hidden` and `tabindex` synchronized to the revealed state
- On viewports below the breakpoint: both sides visible, reveal disabled, every
  nested element tabbable
- Lazy IntersectionObserver hydration; AbortController-tracked listeners
- Throttled resize handler keeps state in sync with viewport changes
- **Views tokens**: `[vvjr:field]` / `[vvjr:field:plain]` in Views text areas
  with *Use replacement tokens from the first row*
- **Drupal admin help**: `/admin/help/vvjr`

## Requirements

- Drupal core `^11.3 || ^12`
- PHP 8.3 or later
- Drupal core modules Views (`drupal:views`) and Filter (`drupal:filter`)
- [VVJ Core](https://www.drupal.org/project/vvj_core) (`drupal/vvj_core` `^2.0.1`), which composer installs for you

Optional: the `vvj_patterns` sub-module of `drupal/vvj_core` adds the Pattern select to the format settings (see [Patterns](#patterns)), and `vvj_patterns_demo` adds example content.

## Installation

```bash
composer require drupal/vvjr:^2.0
drush en vvjr
```

### Upgrading from 1.x

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

`drush updb` enables `vvj_core` (`vvjr_update_10001`) and converts existing views to the v2 option shape (`vvjr_update_10002`). Without `vvj_core` on disk the update stops with a message telling you to download `drupal/vvj_core` and run `drush updb` again.

## Configuration

All settings live in the format settings of the *Views Vanilla JavaScript Reveal* style on a View. They are grouped into responsive, dimensions, reveal and colors sections, and a **Patterns** section when `vvj_patterns` is enabled. The extended help topic is at `/admin/help/vvjr`.

Settings are stored in the View itself, as options of the `views_vvjr` style plugin; their types are declared in `config/schema/vvjr.schema.yml`.

## Patterns

A Pattern is a ready-made design for this format. VVJR ships 5. Enable the `vvj_patterns` sub-module of `drupal/vvj_core` (`drush en vvj_patterns`), open the format settings and choose one in **Patterns** > **Pattern**.

| Pattern | Look |
|---|---|
| Lens | Portrait cards; the back face opens as a circle from where the pointer entered, with a round + button. |
| Blinds | Wide photo cards; the facts panel closes over the photo in eight blinds, opened by a pill button. |
| Index | A numbered full-width list; each row wipes open into an accent panel; Open all and Close all with a live count. |
| Cover | Bento of photo cards, the first one large; a paper cover drops over the photo from a folded corner. |
| Nocturne | A dark band of tall cards; details sweep in on a band of light, chosen with a Photo and Details control. |

When you choose a different Pattern and save, its settings replace the layout, animation, styling and deep linking settings of the view; you can still change them afterwards. Choose **None** to keep your own settings. Drupal then shows a message naming the settings the Pattern set.

Each Pattern also lists what the view still needs, for example the fields to add, their order and the CSS classes to give them. The list appears under **To finish the Pattern** in the Patterns section and as a warning after you save. The `vvj_patterns_demo` sub-module shows a working setup: it adds the "VVJ Slide" content type (fields `vvj_nav_label`, `vvj_image`, `vvj_kicker`, `vvj_summary` and `vvj_link`, besides the title), five demo slides and, as optional config, an example view for each VVJ module named `vvj_demo_<module>`.

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

### Tokens in Views text areas

In Views headers, footers, or empty text with *Use replacement tokens from the
first row*, default Twig tokens (`{{ title }}`) do not work. Use VVJR tokens
instead:

- `{{ title }}` → `[vvjr:title]`
- `{{ field_image }}` → `[vvjr:field_image]`
- Append `:plain` for plain text: `[vvjr:title:plain]`

v2 resolves tokens through the shared `vvj_core.token_resolver` service; syntax
is unchanged from v1. Tokens read from the **first row** of rendered View
fields. Complex field rewrites are not supported.

## Troubleshooting

- **There is no Patterns section.** The section appears only while `vvj_patterns` is enabled. Run `drush en vvj_patterns`.
- **Saving the view changed my settings.** Choosing a different Pattern replaces the layout, animation, styling and deep linking settings. Choose **None** to keep your own.
- **A Pattern looks unfinished.** Follow the list under **To finish the Pattern**: the fields, their order and the CSS classes the Pattern needs.
- **After upgrading from 1.x the view fails or `vvj_core` is not enabled.** Run `drush updb`; update `vvjr_update_10001` enables `vvj_core`. Composer installs it automatically, and a manual install needs `drupal/vvj_core` in `modules/contrib/vvj_core` first.
- **Test errors that name missing infrastructure.** See *Running the tests* in [CONTRIBUTING.md](./CONTRIBUTING.md).
- **Anything else.** Search or open an issue in the [issue queue](https://www.drupal.org/project/issues/vvjr).

## Changelog

See [CHANGELOG.md](./CHANGELOG.md).

## Maintainers

- Alaa Haddad - [flashwebcenter](https://www.drupal.org/u/flashwebcenter)

## License

GPL-2.0-or-later.
