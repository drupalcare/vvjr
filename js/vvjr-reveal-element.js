/**
 * @file
 * VvjrRevealElement — `<vvjr-reveal>` custom element.
 *
 * Extends `Drupal.Vvj.ElementBase` from `vvj_core`. Reveal-card grid
 * with hover or click triggers, breakpoint-gated activation (the window
 * width, or for a Pattern its own width, as its `@container` CSS uses).
 * Animation
 * is driven by inline `style.transform` / `style.opacity` writes
 * (matches v1 behavior verbatim) keyed by the per-card animation class
 * (`a-fade`, `a-zoom`, `a-top`, `a-right`, `a-bottom`, `a-left`). The
 * base class is pulled from the `Drupal.Vvj` namespace — Drupal core's
 * cross-file sharing pattern — with load order guaranteed by the `vvjr`
 * library's `dependencies:`.
 *
 * @license SPDX-License-Identifier: GPL-2.0-or-later
 */

// phpcs:disable Drupal.NamingConventions.UpperCaseConstant
// phpcs:disable Generic.PHP.UpperCaseConstant
// phpcs:disable Squiz.WhiteSpace.OperatorSpacing

((Drupal) => {
  const { ElementBase, ownWidth, watchOwnWidth } = Drupal.Vvj;

  const TABBABLE = 'a, button, input, select, textarea, [tabindex]';
  // Pattern views: a click or key on one of these never toggles the card.
  const INTERACTIVE = 'a, button, input, select, textarea, label, summary';

  /**
   * The control inside a card face an event started on, if any.
   *
   * @param {Event} event
   *   The click or keydown event.
   * @param {HTMLElement} inner
   *   The `.reveal-item` of the card.
   *
   * @return {Element|null}
   *   The link or form control inside the card, or NULL.
   */
  function fromControl(event, inner) {
    const { target } = event;
    if (!(target instanceof Element) || target === inner) {
      return null;
    }
    const control = target.closest(INTERACTIVE);
    return control && inner.contains(control) ? control : null;
  }

  function throttle(fn, limit) {
    let lastFn;
    let lastRan = 0;
    return (...args) => {
      const now = Date.now();
      if (!lastRan || now - lastRan >= limit) {
        fn(...args);
        lastRan = now;
      } else {
        clearTimeout(lastFn);
        lastFn = setTimeout(
          () => {
            fn(...args);
            lastRan = Date.now();
          },
          limit - (now - lastRan),
        );
      }
    };
  }

  class VvjrRevealElement extends ElementBase {
    static get patternSlug() {
      return 'vvjr';
    }

    constructor() {
      super();
      this._method = 'click';
      this._breakpoint = 0;
      this._breakpointAll = false;
      this._items = [];
      this._cardListeners = new WeakMap();
      this._patterned = false;
      this._revealers = new Map();
      this._width = 0;
    }

    onHydrate() {
      const breakpointAttr = this.getAttribute('data-breakpoints') ?? 'all';
      this._breakpointAll = breakpointAttr === 'all';
      const parsed = parseInt(breakpointAttr, 10);
      this._breakpoint = Number.isFinite(parsed) ? parsed : 0;

      this._method =
        this.getAttribute('data-reveal-method') === 'hover' ? 'hover' : 'click';

      this._items = this.querySelectorAll('.reveal-item');
      if (!this._items.length) {
        return;
      }
      // Pattern views only (no change for None): CSS owns the end state, a
      // real toggle button per card, links on the shown face work.
      this._patterned = this.classList.contains('vvj-pattern');
      if (this._patterned) {
        this.classList.add('vvjr-ready');
        // A Pattern follows the room it has (a narrow column on a wide
        // screen stacks too); a view without a Pattern keeps the window.
        this._width = ownWidth(this);
      }

      this._applyState();

      if (this._patterned) {
        watchOwnWidth(this, this.signal, (width) => {
          if (width === this._width) {
            return;
          }
          this._width = width;
          this._resetRevealed();
          this._applyState();
        });
        return;
      }

      const onResize = throttle(() => {
        this._resetRevealed();
        this._applyState();
      }, 200);
      window.addEventListener('resize', onResize, { signal: this.signal });
    }

    // -----------------------------------------------------------------
    // State application.
    // -----------------------------------------------------------------

    _applyState() {
      const active = this._isActiveAtCurrentWidth();
      if (active) {
        this._activate();
        this._setAriaForActive();
      } else {
        this._deactivate();
        this._setAriaForInactive();
      }
      if (this._patterned) {
        this.classList.toggle('vvjr-stacked', !active);
      }
    }

    _isActiveAtCurrentWidth() {
      if (this._breakpointAll) {
        return true;
      }
      const width = this._patterned ? this._width : window.innerWidth;
      return width >= this._breakpoint;
    }

    _activate() {
      this._items.forEach((inner) => this._bindCard(inner));
    }

    _deactivate() {
      this._items.forEach((inner) => {
        inner.classList.remove('revealed');
        this._unbindCard(inner);
      });
      this._revealers.clear();
    }

    _resetRevealed() {
      this._items.forEach((inner) => {
        if (inner.classList.contains('revealed')) {
          inner.classList.remove('revealed');
          const front = inner.querySelector('.front-box');
          const back = inner.querySelector('.back-box');
          this._updateAccessibility(front, back, false);
          this._resetEffect(inner, back);
        }
      });
    }

    // -----------------------------------------------------------------
    // Per-card listener wiring (re-entrant via per-card AbortController).
    // -----------------------------------------------------------------

    _bindCard(inner) {
      if (this._cardListeners.has(inner)) {
        this._unbindCard(inner);
      }

      const front = inner.querySelector('.front-box');
      const back = inner.querySelector('.back-box');
      if (!front || !back) {
        return;
      }

      const cardAbort = new AbortController();
      this._cardListeners.set(inner, cardAbort);

      const reveal = (value) => {
        const isRevealed =
          value !== undefined ? value : inner.classList.toggle('revealed');
        if (value !== undefined) {
          inner.classList.toggle('revealed', isRevealed);
        }
        this._updateAccessibility(front, back, isRevealed);
        if (isRevealed) {
          this._applyEffect(inner, back);
        } else {
          this._resetEffect(inner, back);
        }
        if (this._patterned) {
          this._announceToggle(inner, isRevealed);
        }
      };
      this._revealers.set(inner, reveal);

      if (this._patterned) {
        this._bindPatternCard(inner, back, reveal, cardAbort.signal);
        this.signal.addEventListener('abort', () => cardAbort.abort(), {
          once: true,
        });
        return;
      }

      const onKeydown = (event) => {
        // Enter on a link or a button inside a face does its own job.
        if (fromControl(event, inner)) {
          return;
        }
        if (event.key === 'Enter' || event.key === ' ') {
          event.preventDefault();
          reveal();
        }
      };

      if (this._method === 'hover') {
        inner.addEventListener('mouseenter', () => reveal(true), {
          signal: cardAbort.signal,
        });
        inner.addEventListener('mouseleave', () => reveal(false), {
          signal: cardAbort.signal,
        });
      } else {
        inner.addEventListener(
          'click',
          (event) => {
            const control = fromControl(event, inner);
            if (!control) {
              reveal();
              return;
            }
            // A control on the hidden face never acts on a card that looks
            // like plain content: the click turns the card instead.
            if (control.closest('[aria-hidden="true"]')) {
              event.preventDefault();
              reveal();
            }
            // A control on the shown face works as itself.
          },
          { signal: cardAbort.signal },
        );
      }
      inner.addEventListener('keydown', onKeydown, {
        signal: cardAbort.signal,
      });

      this.signal.addEventListener('abort', () => cardAbort.abort(), {
        once: true,
      });
    }

    /**
     * Pattern views: button, Escape, and a card click that spares controls.
     *
     * @param {HTMLElement} inner
     *   The `.reveal-item`.
     * @param {HTMLElement} back
     *   Its `.back-box`.
     * @param {(value?: boolean) => void} reveal
     *   Shows (true), hides (false) or toggles (undefined) the back face.
     * @param {AbortSignal} signal
     *   Released when the card is unbound.
     */
    _bindPatternCard(inner, back, reveal, signal) {
      // One class per mode on the toggle (vvj_core/docs/JS-RULES.md rule 11):
      // the template sets vvjr-toggle--click or vvjr-toggle--hover, and no
      // handler reaches both.
      const clickButton = inner.querySelector(':scope > .vvjr-toggle--click');
      const hoverButton = inner.querySelector(':scope > .vvjr-toggle--hover');
      const button = clickButton || hoverButton;
      if (clickButton) {
        clickButton.addEventListener(
          'click',
          (event) => {
            event.stopPropagation();
            reveal();
          },
          { signal },
        );
      }
      if (hoverButton) {
        // Hover mode: the pointer already reveals the card, so a mouse click
        // on the toggle would undo it. Only keyboard (detail 0) and devices
        // without hover (touch) use the toggle.
        hoverButton.addEventListener(
          'click',
          (event) => {
            event.stopPropagation();
            if (
              event.detail !== 0 &&
              window.matchMedia('(hover: hover)').matches
            ) {
              return;
            }
            reveal();
          },
          { signal },
        );
      }
      if (this._method === 'hover') {
        inner.addEventListener('mouseenter', () => reveal(true), { signal });
        inner.addEventListener('mouseleave', () => reveal(false), { signal });
      } else {
        inner.addEventListener(
          'click',
          (event) => {
            const target = event.target;
            if (target instanceof Element && target.closest(INTERACTIVE)) {
              return;
            }
            reveal();
          },
          { signal },
        );
      }
      inner.addEventListener(
        'keydown',
        (event) => {
          if (event.key !== 'Escape' || !inner.classList.contains('revealed')) {
            return;
          }
          const hadFocus = back.contains(document.activeElement);
          reveal(false);
          if (hadFocus && button) {
            button.focus();
          }
        },
        { signal },
      );
    }

    /**
     * Pattern views: shared state, a `vvjr:toggle` event, open count.
     *
     * @param {HTMLElement} inner
     *   The `.reveal-item` that changed.
     * @param {boolean} isRevealed
     *   Its new state.
     */
    _announceToggle(inner, isRevealed) {
      const items = Array.from(this._items);
      const index = items.indexOf(inner);
      const open = items.filter((item) =>
        item.classList.contains('revealed'),
      ).length;
      this.style.setProperty('--vvjr-open', String(open));
      this.writeState({ index, total: items.length, items });
      this.dispatchEvent(
        new CustomEvent('vvjr:toggle', {
          bubbles: true,
          detail: { index, revealed: isRevealed, open, total: items.length },
        }),
      );
    }

    /**
     * Shows or hides one card's back face (Pattern scripts use this).
     *
     * @param {number} index
     *   Zero-based card index.
     * @param {boolean} value
     *   True shows the back face, false the front.
     */
    setRevealed(index, value) {
      const inner = this._items[index];
      const reveal = inner ? this._revealers.get(inner) : null;
      if (reveal && inner.classList.contains('revealed') !== value) {
        reveal(value);
      }
    }

    _unbindCard(inner) {
      const ctl = this._cardListeners.get(inner);
      if (ctl) {
        ctl.abort();
        this._cardListeners.delete(inner);
      }
    }

    // -----------------------------------------------------------------
    // Inline-style effect (matches v1 verbatim).
    // -----------------------------------------------------------------

    _applyEffect(inner, back) {
      if (this._patterned) {
        return;
      }
      back.style.opacity = '1';
      if (inner.classList.contains('a-zoom')) {
        back.style.transform = 'scale(1)';
      } else if (inner.classList.contains('a-top')) {
        back.style.transform = 'translateY(0)';
      } else if (inner.classList.contains('a-right')) {
        back.style.transform = 'translateX(0)';
      } else if (inner.classList.contains('a-bottom')) {
        back.style.transform = 'translateY(0)';
      } else if (inner.classList.contains('a-left')) {
        back.style.transform = 'translateX(0)';
      }
    }

    _resetEffect(inner, back) {
      if (this._patterned) {
        return;
      }
      back.style.opacity = '0';
      if (inner.classList.contains('a-zoom')) {
        back.style.transform = 'scale(0)';
      } else if (inner.classList.contains('a-top')) {
        back.style.transform = 'translateY(-100%)';
      } else if (inner.classList.contains('a-right')) {
        back.style.transform = 'translateX(100%)';
      } else if (inner.classList.contains('a-bottom')) {
        back.style.transform = 'translateY(100%)';
      } else if (inner.classList.contains('a-left')) {
        back.style.transform = 'translateX(-100%)';
      }
    }

    // -----------------------------------------------------------------
    // ARIA + tabindex.
    // -----------------------------------------------------------------

    _setAriaForActive() {
      this._items.forEach((inner) => {
        const front = inner.querySelector('.front-box');
        const back = inner.querySelector('.back-box');
        if (!front || !back) {
          return;
        }
        this._updateAccessibility(
          front,
          back,
          inner.classList.contains('revealed'),
        );
      });
    }

    _setAriaForInactive() {
      this._items.forEach((inner) => {
        const front = inner.querySelector('.front-box');
        const back = inner.querySelector('.back-box');
        if (!front || !back) {
          return;
        }
        front.setAttribute('aria-hidden', 'false');
        back.setAttribute('aria-hidden', 'false');
        front.removeAttribute('tabindex');
        back.removeAttribute('tabindex');
        // Both faces are shown below the breakpoint.
        back.style.pointerEvents = '';
        if (this._patterned) {
          front.inert = false;
          back.inert = false;
          inner
            .querySelector(
              ':scope > :is(.vvjr-toggle--click, .vvjr-toggle--hover)',
            )
            ?.setAttribute('aria-expanded', 'false');
        }
      });
      this.querySelectorAll(TABBABLE).forEach((el) =>
        el.setAttribute('tabindex', '0'),
      );
    }

    _updateAccessibility(front, back, isRevealed) {
      if (!front || !back) {
        return;
      }
      const frontHidden = isRevealed ? 'true' : 'false';
      const backHidden = isRevealed ? 'false' : 'true';
      front.setAttribute('aria-hidden', frontHidden);
      back.setAttribute('aria-hidden', backHidden);
      front.setAttribute('tabindex', frontHidden === 'true' ? '-1' : '0');
      back.setAttribute('tabindex', backHidden === 'true' ? '-1' : '0');

      front.querySelectorAll(TABBABLE).forEach((el) => {
        el.setAttribute('tabindex', isRevealed ? '-1' : '0');
      });
      back.querySelectorAll(TABBABLE).forEach((el) => {
        el.setAttribute('tabindex', isRevealed ? '0' : '-1');
      });
      if (this._patterned) {
        // The hidden face can be neither clicked nor focused.
        front.inert = isRevealed;
        back.inert = !isRevealed;
        // The toggle is a disclosure for the back face (aria-controls).
        front.parentElement
          ?.querySelector(
            ':scope > :is(.vvjr-toggle--click, .vvjr-toggle--hover)',
          )
          ?.setAttribute('aria-expanded', isRevealed ? 'true' : 'false');
      } else {
        // The hidden back face covers the front at opacity 0 (fade). Let
        // clicks pass through it so links on the shown front work and the
        // unseen links on the back cannot be followed.
        back.style.pointerEvents = isRevealed ? '' : 'none';
      }
      // Focus never stays inside the face that was just hidden: it moves to
      // the face now showing, without scrolling the page.
      const hidden = isRevealed ? front : back;
      const shown = isRevealed ? back : front;
      if (hidden.contains(document.activeElement)) {
        shown.focus({ preventScroll: true });
      }
    }
  }

  if (!customElements.get('vvjr-reveal')) {
    customElements.define('vvjr-reveal', VvjrRevealElement);
  }
})(Drupal);
