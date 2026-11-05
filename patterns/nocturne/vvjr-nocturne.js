/**
 * @file
 * Pattern Nocturne: a Photo and Details segmented control on every card.
 *
 * @license SPDX-License-Identifier: GPL-2.0-or-later
 */

((Drupal, once) => {
  const controllers = new WeakMap();
  const SELECTOR = 'vvjr-reveal.vvj-pattern--vvjr-nocturne';

  const wire = (root, signal) => {
    const items = Array.from(
      root.querySelectorAll(':scope > .reveal-inner > .reveal-item'),
    );
    const groups = [];
    items.forEach((item, i) => {
      const toggle = item.querySelector(':scope > .vvjr-toggle');
      if (!toggle) {
        return;
      }
      const controls = toggle.getAttribute('aria-controls') || '';
      const group = document.createElement('div');
      group.className = 'vvjr-seg';
      group.setAttribute('role', 'group');
      group.setAttribute('aria-label', Drupal.t('Show'));
      const thumb = document.createElement('span');
      thumb.className = 'vvjr-seg-thumb';
      thumb.setAttribute('aria-hidden', 'true');
      group.append(thumb);
      const make = (cls, text, value) => {
        const button = document.createElement('button');
        button.type = 'button';
        button.className = cls;
        if (controls) {
          button.setAttribute('aria-controls', controls);
        }
        const icon = document.createElement('span');
        icon.className = 'vvjr-icon';
        icon.setAttribute('aria-hidden', 'true');
        const label = document.createElement('span');
        label.textContent = text;
        button.append(icon, label);
        button.addEventListener(
          'click',
          (event) => {
            event.stopPropagation();
            root.setRevealed?.(i, value);
          },
          { signal },
        );
        group.append(button);
        return button;
      };
      const front = make('vvjr-seg-front', Drupal.t('Photo'), false);
      const back = make('vvjr-seg-back', Drupal.t('Details'), true);
      const sync = (revealed) => {
        front.setAttribute('aria-pressed', revealed ? 'false' : 'true');
        back.setAttribute('aria-pressed', revealed ? 'true' : 'false');
      };
      sync(item.classList.contains('revealed'));
      toggle.hidden = true;
      item.append(group);
      groups[i] = sync;
      signal.addEventListener(
        'abort',
        () => {
          group.remove();
          toggle.hidden = false;
        },
        { once: true },
      );
    });
    root.addEventListener(
      'vvjr:toggle',
      (event) => groups[event.detail.index]?.(event.detail.revealed),
      { signal },
    );
  };

  Drupal.behaviors.vvjPatternVvjrNocturne = {
    attach(context) {
      once('vvj-pattern-vvjr-nocturne', SELECTOR, context).forEach((root) => {
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
        .remove('vvj-pattern-vvjr-nocturne', SELECTOR, context)
        .forEach((root) => {
          controllers.get(root)?.abort();
          controllers.delete(root);
        });
    },
  };
})(Drupal, once);
