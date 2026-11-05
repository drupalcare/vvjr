/**
 * @file
 * Pattern Lens: the back face opens as a circle from the pointer.
 *
 * @license SPDX-License-Identifier: GPL-2.0-or-later
 */

((Drupal, once) => {
  const controllers = new WeakMap();
  const SELECTOR = 'vvjr-reveal.vvj-pattern--vvjr-lens';

  const wire = (root, signal) => {
    // The circle opens from where the pointer entered (or the pressed button).
    Drupal.Vvj.trackOrigin(root, '.reveal-item', { signal });
  };

  Drupal.behaviors.vvjPatternVvjrLens = {
    attach(context) {
      once('vvj-pattern-vvjr-lens', SELECTOR, context).forEach((root) => {
        const controller = new AbortController();
        controllers.set(root, controller);
        wire(root, controller.signal);
      });
    },
    detach(context, settings, trigger) {
      if (trigger !== 'unload') {
        return;
      }
      once
        .remove('vvj-pattern-vvjr-lens', SELECTOR, context)
        .forEach((root) => {
          controllers.get(root)?.abort();
          controllers.delete(root);
        });
    },
  };
})(Drupal, once);
