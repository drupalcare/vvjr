/**
 * @file
 * Pattern Index: Open all and Close all, and a live count of open cards.
 *
 * @license SPDX-License-Identifier: GPL-2.0-or-later
 */

((Drupal, once) => {
  const controllers = new WeakMap();
  const SELECTOR = 'vvjr-reveal.vvj-pattern--vvjr-index';

  const wire = (root, signal) => {
    const inner = root.querySelector(':scope > .reveal-inner');
    if (!inner || root.querySelector(':scope > .vvjr-bulk')) {
      return;
    }
    const total = inner.querySelectorAll(':scope > .reveal-item').length;
    const bar = document.createElement('div');
    bar.className = 'vvjr-bulk';
    const group = document.createElement('div');
    group.className = 'vvjr-bulk-seg';
    group.setAttribute('role', 'group');
    group.setAttribute('aria-label', Drupal.t('All cards'));
    const make = (cls, text, pressed) => {
      const button = document.createElement('button');
      button.type = 'button';
      button.className = cls;
      button.textContent = text;
      button.setAttribute('aria-pressed', pressed ? 'true' : 'false');
      group.append(button);
      return button;
    };
    const openAll = make('vvjr-bulk-open', Drupal.t('Open all'), false);
    const closeAll = make('vvjr-bulk-close', Drupal.t('Close all'), true);
    const count = document.createElement('p');
    count.className = 'vvjr-count';
    count.setAttribute('aria-live', 'polite');
    const render = (open) => {
      count.textContent = Drupal.t('@open of @total open', {
        '@open': open,
        '@total': total,
      });
      openAll.setAttribute('aria-pressed', open === total ? 'true' : 'false');
      closeAll.setAttribute('aria-pressed', open === 0 ? 'true' : 'false');
    };
    bar.append(group, count);
    root.insertBefore(bar, inner);
    render(inner.querySelectorAll(':scope > .reveal-item.revealed').length);

    const setAll = (value) => {
      for (let i = 0; i < total; i++) {
        root.setRevealed?.(i, value);
      }
    };
    openAll.addEventListener('click', () => setAll(true), { signal });
    closeAll.addEventListener('click', () => setAll(false), { signal });
    root.addEventListener('vvjr:toggle', (event) => render(event.detail.open), {
      signal,
    });
    signal.addEventListener('abort', () => bar.remove(), { once: true });
  };

  Drupal.behaviors.vvjPatternVvjrIndex = {
    attach(context) {
      once('vvj-pattern-vvjr-index', SELECTOR, context).forEach((root) => {
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
        .remove('vvj-pattern-vvjr-index', SELECTOR, context)
        .forEach((root) => {
          controllers.get(root)?.abort();
          controllers.delete(root);
        });
    },
  };
})(Drupal, once);
