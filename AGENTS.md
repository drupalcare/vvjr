# Views Vanilla JavaScript Reveal (VVJR): agent brief

**Who this is for:** whoever works this module next, human or agent, with no memory of how it got this way.

**Scope:** this repository only. The shared foundation is the separate project `drupal/vvj_core`; fix shared behavior there, never by copying it here.

---

## Before you change anything

1. **`README.md`** states what the module promises: requirements, Patterns, usage, upgrade path.
2. **`CHANGELOG.md`** is Keep a Changelog. Every change a site owner could notice goes under `[2.0.0]` (unreleased) before it is done.
3. **Find the owner of the fact** in the table below and change it there. A value typed in a second place is the bug.
4. **`vvj_core` owns the shared parts**: the style plugin base `VvjStylePluginBase`, the custom element base `VvjElementBase`, the `vvj_core.token_resolver` and `vvj_core.upgrade_auditor` services and the `ValidationBounds` constants. If a change belongs to every VVJ module, make it in `vvj_core`.

## Where facts live

| Fact | Owner |
|---|---|
| This module's defaults and allowed values | `src/VvjrConstants.php`; cross-cutting bounds are in `vvj_core`'s `ValidationBounds` |
| Option keys and their types | `config/schema/vvjr.schema.yml` |
| The Views format (plugin ID `views_vvjr`, form sections, Patterns support) | `src/Plugin/views/style/Reveal.php` |
| Theme hook, preprocess, tokens, help page | `src/Hook/*.php`, `templates/` |
| The custom element `<vvjr-reveal>` and its behavior | `js/` |
| Libraries | `vvjr.libraries.yml` |
| Patterns | `patterns/<id>/pattern.yml`, the Pattern's CSS, and the library `pattern.<id>` |
| Upgrade steps | `vvjr.install` |
| Example view | `config/optional/views.view.vvjr_example.yml` |

## Patterns

- A Pattern is a folder under `patterns/` with a `pattern.yml` (keys: `label`, `description`, `weight`, `options`, `features`, `needs`), its stylesheet, and a `pattern.<id>` entry in `vvjr.libraries.yml` that depends on `vvjr-pattern-base`.
- Only option names this module defines are applied from a Pattern's `options`; any other option name is ignored (`StylePatterns::applyChoice()` in `vvj_core/modules/vvj_patterns`).
- A saved view stores the Pattern's folder name in the `vvj_pattern` option. Renaming or deleting a folder leaves saved views with an id that no longer resolves, and they fall back to no Pattern. Treat a Pattern id as public.
- The `label`, `description` and `needs` strings are shown to the site owner through `t()`. Write them as plain, final text.

## Commands that check a change

From the Drupal project root, with Drupal's Coder and PHPStan installed through Composer:

```bash
vendor/bin/phpcs --standard=web/modules/contrib/vvjr/phpcs.xml.dist web/modules/contrib/vvjr
vendor/bin/phpstan analyse -c web/modules/contrib/vvjr/phpstan.neon
```

The suites are described in `CONTRIBUTING.md` (Running the tests). The `.eslintrc.json` and `.cspell.json` files configure the JavaScript lint and the spelling check; the drupal.org pipeline (`.gitlab-ci.yml`) runs both.

## What must not change

- **The 1.x contract.** *Upgrading from 1.x* in `README.md` lists the names a drop-in upgrade preserves: plugin ID, theme hook, Twig template names, option keys, library names, JS behavior key and CSS class names. Renaming any of them breaks existing sites and themes.
- **Shipped update functions.** Never edit one that has been released; add a new `vvjr_update_NNNN()`.
- **The PHPStan baseline stays absent.** `phpstan.neon` explains why; fix the finding instead of generating a baseline.
- **The `vvj_core` floor** in `composer.json` (`^2.0.1`) and `vvjr.info.yml`: raise it only together with the code that needs it.
