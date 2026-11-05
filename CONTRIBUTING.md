# Contributing to VVJR

Thanks for contributing. Views Vanilla JavaScript Reveal is a Views format built on the `drupal/vvj_core` foundation.

- Issues and feature requests: <https://www.drupal.org/project/issues/vvjr>
- Source: <https://git.drupalcode.org/project/vvjr>
- Security issues: do not use the public queue; see [SECURITY.md](./SECURITY.md).

Read [AGENTS.md](./AGENTS.md) first: it says where each fact lives and what must not change.

## Code style

- Drupal and DrupalPractice coding standards, configured in `phpcs.xml.dist` (it checks `src`, `tests` and `vvjr.module`).
- `declare(strict_types=1);` in every PHP file.
- Hooks are OOP hook classes in `src/Hook/` using the `#[Hook]` attribute.
- This module's defaults live in `src/VvjrConstants.php`; do not type a default or bound in a second place.
- PHPStan runs at level max with no baseline (see `phpstan.neon`).
- JavaScript attaches through `Drupal.behaviors` with `once()`, and the custom element extends `VvjElementBase` from `vvj_core`.

## Local checks

```bash
vendor/bin/phpcs
vendor/bin/phpcbf
vendor/bin/phpstan analyse
```

## Running the tests

This module ships no `phpunit.xml` of its own, deliberately: its suites are
Kernel, Functional and FunctionalJavascript, which need a real Drupal, so a
module-local config would have to hard-code a relative path to `core` and would
break the moment the module is not physically inside a docroot (a symlinked
checkout, for instance). Drupal core's runner already does the job.

Run the fast suites from the docroot:

```bash
cd /path/to/drupal/web
export SIMPLETEST_DB="mysql://user:pass@localhost/dbname"
../vendor/bin/phpunit -c core modules/contrib/vvjr/tests/src/Unit
../vendor/bin/phpunit -c core modules/contrib/vvjr/tests/src/Kernel
```

The **Functional** and **FunctionalJavascript** suites also need
`SIMPLETEST_BASE_URL` pointing at a running web server, and the JavaScript ones
need chromedriver. Without those they error rather than fail, and the errors
name missing infrastructure rather than anything about this module, so don't
read them as test failures. The drupal.org pipeline provides both.

## Continuous integration

`.gitlab-ci.yml` includes the Drupal Association templates and turns on two extra jobs beyond the default current-core run: the previous minor of core and the next major. The template marks the next-major job as allowed to fail, so it shows a break without blocking a merge.

## Adding or changing a Pattern

1. Create `patterns/<id>/pattern.yml` with `label`, `description`, `weight`, `options`, `features` and `needs`, plus the Pattern's CSS (and JS when it needs it).
2. Add a `pattern.<id>` library to `vvjr.libraries.yml` that depends on `vvjr-pattern-base`.
3. Use only option names this module defines; others are ignored.
4. Add the Pattern to the table in the README and an entry to `CHANGELOG.md`.

## Pull request checklist

- [ ] `phpcs` reports no errors
- [ ] `phpstan analyse` reports no errors and no baseline was added
- [ ] No default or bound is typed in a second place
- [ ] A user-visible change has a `CHANGELOG.md` entry
- [ ] A change that alters stored options has an update function in `vvjr.install`
- [ ] Names listed under *Upgrading from 1.x* in the README are unchanged
