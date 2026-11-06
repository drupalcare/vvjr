# Changelog

## [2.0.0] (UNRELEASED)

**Drop-in upgrade for 1.x sites.** v2 modernizes internals: extends `vvj_core`
foundation, ships `<vvjr-reveal>` custom element extending `VvjElementBase`,
AbortController-tracked listeners, throttled resize, while preserving every CSS
rule, library name, option key, plugin ID, theme hook, JS behavior key, and the
JS-driven inline-style reveal effect from 1.x.

### Fixed

- Patterns: the accent token falls back to the ink, so accent text keeps the ink's contrast in black and white.
- Patterns: the stacked state (`.vvjr-stacked`) now follows the Pattern's own width (a ResizeObserver on the element) instead of the window. Views without a Pattern still use the window width.
- Accessibility: the reveal container is no longer an empty tab stop; the Pattern toggle carries `aria-expanded` for the back face it controls.
- Hover rules that must beat a rest rule are flattened with `:where()` so no new Pattern selector exceeds 0,2,0; the look is unchanged (check-vvj-cascade: winners changed 0). Blinds toggle: the hover widening moved from the button's padding to two pseudo spacers (stable box).
- Update 10002 converts v1 views to the v2 option shape (value types:
  strings and integers stored where v2 declares integer, boolean and float).
- Accessibility: links on the shown face of a click-trigger card work by mouse and keyboard (Enter no longer toggles the card); links on the hidden face stay out of the tab order and cannot be clicked.
- Patterns: the viewport breakpoint files (`vvjr-NNN.css`, `vvjr-all.css`) no longer lay out Pattern markup; the Pattern's own CSS and its `vvjr-stacked` state are the only layout, and the back face keeps its hidden horizontal overflow. Classic reveals are unchanged.


### Added

- Patterns: 5 ready-made designs (Lens, Blinds, Index, Cover, Nocturne) chosen in the format settings. They need the `vvj_patterns` sub-module of `drupal/vvj_core`.
- Patterns: the bridge reads the shared roles; the Blinds pill and Lens toggle keep the stage tokens; dead `--vvj-accent` read removed.
- Pattern theme bridges `pattern-bridge-solo` and `pattern-bridge-apex`: under Solo or APEX the Patterns take the region colours; every Pattern colour is now a token declared once in `vvjr-pattern-base.css`, with the same black-and-white look elsewhere.
- `drupal/vvj_core` dependency (auto-installed).
- `vvjr.install` with `vvjr_update_10001()` to auto-enable vvj_core during
  `drush updb`.
- `<vvjr-reveal>` custom element. Lazy IntersectionObserver hydration;
  AbortController-tracked event listeners (per-card sub-controllers); throttled
  resize handler.

### Changed

- Plugin extends `VvjStylePluginBase`. Implements four small subclass hooks;
  standard sections come from the base.
- `supportsDeeplinking()` returns FALSE (transient per-card state).
- Token resolution delegates to `vvj_core.token_resolver`.
- Hooks moved to OOP `#[Hook]` classes.
- Inline-style reveal effect (per-card `style.opacity` + `style.transform`
  writes keyed by animation class) preserved verbatim from v1.
- Code style: JavaScript and CSS follow Drupal core's ESLint, Stylelint and Prettier rules, and the spell-check job passes with a project word file. Rendering and behavior are unchanged.
- Layout: Pattern layout now follows the width the Pattern itself gets (container queries on `vvj-region`) instead of the screen width, so Patterns lay out correctly in a narrow content column; overlays and the Pattern root keep screen-width queries.

### Drupal core / PHP

- Requires Drupal `^11.3 || ^12`, PHP `>=8.3`.
