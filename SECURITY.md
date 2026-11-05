# Security policy

VVJR follows the Drupal Security Team's process for handling security issues.

## Reporting a vulnerability

**Do not** report security issues in the public issue queue. Instead, report them privately through the Drupal Security Team:

- <https://www.drupal.org/security-team/report-issue>

Please include the module version, Drupal core version, and clear reproduction steps. Reports for stable releases covered by the Security Advisory policy are triaged by the Security Team; see:

- <https://www.drupal.org/security-team>
- <https://www.drupal.org/drupal-security-team/security-advisory-process-and-permissions-policy>

## Supported versions

Security coverage applies to the latest stable release once the project opts into Security Advisory coverage on drupal.org. Development, alpha and beta releases are not covered by security advisories.

## What this module exposes

VVJR ships no routes and no permissions of its own: it has no `*.routing.yml` and no `*.permissions.yml`. It renders the rows of a View through the Views access system, so who can see the output is decided by the View's own access settings. Shared services such as token resolution and the `safe_html` filter come from `drupal/vvj_core`; report issues in them against that project.
