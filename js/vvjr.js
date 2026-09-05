/**
 * @file
 * VVJR compat shim. Behavior lives in `<vvjr-reveal>` custom element.
 *
 * @license SPDX-License-Identifier: GPL-2.0-or-later
 */

// phpcs:disable Drupal.NamingConventions.UpperCaseConstant
// phpcs:disable Generic.PHP.UpperCaseConstant
// phpcs:disable Squiz.WhiteSpace.OperatorSpacing

((Drupal, once) => {
  Drupal.behaviors.VVJReveal = {
    attach(context) {
      once('vvjr-reveal-marker', 'vvjr-reveal', context);
    },
    detach() {},
  };
})(Drupal, once);
