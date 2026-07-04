/**
 * @file
 * VvjrRevealElement — `<vvjr-reveal>` custom element.
 *
 * Extends `Drupal.Vvj.ElementBase` from `vvj_core`. Reveal-card grid
 * with hover or click triggers, breakpoint-gated activation. Animation
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
  'use strict';

  const { ElementBase } = Drupal.Vvj;

  const TABBABLE = 'a, button, input, select, textarea, [tabindex]';

  function throttle(fn, limit) {
    let lastFn;
    let lastRan = 0;
    return (...args) => {
      const now = Date.now();
      if (!lastRan || now - lastRan >= limit) {
        fn(...args);
        lastRan = now;
      }
      else {
        clearTimeout(lastFn);
        lastFn = setTimeout(() => {
          fn(...args);
          lastRan = Date.now();
        }, limit - (now - lastRan));
      }
    };
  }

  class VvjrRevealElement extends ElementBase {

    static get patternSlug() {
 return 'vvjr'; }

    constructor() {
      super();
      this._method = 'click';
      this._breakpoint = 0;
      this._breakpointAll = false;
      this._items = [];
      this._cardListeners = new WeakMap();
    }

    onHydrate() {
      const breakpointAttr = this.getAttribute('data-breakpoints') ?? 'all';
      this._breakpointAll = breakpointAttr === 'all';
      const parsed = parseInt(breakpointAttr, 10);
      this._breakpoint = Number.isFinite(parsed) ? parsed : 0;

      this._method = this.getAttribute('data-reveal-method') === 'hover' ? 'hover' : 'click';

      this._items = this.querySelectorAll('.reveal-item');
      if (!this._items.length) { return;
      }

      this._applyState();

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
        this._disableNestedLinks(this._method === 'click');
      }
      else {
        this._deactivate();
        this._setAriaForInactive();
        this._disableNestedLinks(false);
      }
    }

    _isActiveAtCurrentWidth() {
      if (this._breakpointAll) { return true;
      }
      return window.innerWidth >= this._breakpoint;
    }

    _activate() {
      this._items.forEach((inner) => this._bindCard(inner));
    }

    _deactivate() {
      this._items.forEach((inner) => {
        inner.classList.remove('revealed');
        this._unbindCard(inner);
      });
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
      if (!front || !back) { return;
      }

      const cardAbort = new AbortController();
      this._cardListeners.set(inner, cardAbort);

      const reveal = (value) => {
        const isRevealed = value !== undefined ? value : inner.classList.toggle('revealed');
        if (value !== undefined) {
          inner.classList.toggle('revealed', isRevealed);
        }
        this._updateAccessibility(front, back, isRevealed);
        if (isRevealed) {
          this._applyEffect(inner, back);
        }
        else {
          this._resetEffect(inner, back);
        }
      };

      const onKeydown = (event) => {
        if (event.key === 'Enter' || event.key === ' ') {
          event.preventDefault();
          reveal();
        }
      };

      if (this._method === 'hover') {
        inner.addEventListener('mouseenter', () => reveal(true), { signal: cardAbort.signal });
        inner.addEventListener('mouseleave', () => reveal(false), { signal: cardAbort.signal });
      }
      else {
        inner.addEventListener('click', () => reveal(), { signal: cardAbort.signal });
      }
      inner.addEventListener('keydown', onKeydown, { signal: cardAbort.signal });

      this.signal.addEventListener('abort', () => cardAbort.abort(), { once: true });
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
      back.style.opacity = '1';
      if (inner.classList.contains('a-zoom')) { back.style.transform = 'scale(1)';
      } else if (inner.classList.contains('a-top')) { back.style.transform = 'translateY(0)';
      } else if (inner.classList.contains('a-right')) { back.style.transform = 'translateX(0)';
      } else if (inner.classList.contains('a-bottom')) { back.style.transform = 'translateY(0)';
      } else if (inner.classList.contains('a-left')) { back.style.transform = 'translateX(0)';
      }
    }

    _resetEffect(inner, back) {
      back.style.opacity = '0';
      if (inner.classList.contains('a-zoom')) { back.style.transform = 'scale(0)';
      } else if (inner.classList.contains('a-top')) { back.style.transform = 'translateY(-100%)';
      } else if (inner.classList.contains('a-right')) { back.style.transform = 'translateX(100%)';
      } else if (inner.classList.contains('a-bottom')) { back.style.transform = 'translateY(100%)';
      } else if (inner.classList.contains('a-left')) { back.style.transform = 'translateX(-100%)';
      }
    }

    // -----------------------------------------------------------------
    // ARIA + tabindex.
    // -----------------------------------------------------------------

    _setAriaForActive() {
      this._items.forEach((inner) => {
        const front = inner.querySelector('.front-box');
        const back = inner.querySelector('.back-box');
        if (!front || !back) { return;
        }
        this._updateAccessibility(front, back, inner.classList.contains('revealed'));
      });
    }

    _setAriaForInactive() {
      this._items.forEach((inner) => {
        const front = inner.querySelector('.front-box');
        const back = inner.querySelector('.back-box');
        if (!front || !back) { return;
        }
        front.setAttribute('aria-hidden', 'false');
        back.setAttribute('aria-hidden', 'false');
        front.removeAttribute('tabindex');
        back.removeAttribute('tabindex');
      });
      this.querySelectorAll(TABBABLE).forEach((el) => el.setAttribute('tabindex', '0'));
    }

    _updateAccessibility(front, back, isRevealed) {
      if (!front || !back) { return;
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
    }

    /**
     * Disable nested anchors when the parent card is the click target.
     *
     * @param {boolean} disable
     *   True to suppress nested-link clicks.
     */
    _disableNestedLinks(disable) {
      this._nestedLinksDisabled = disable;
      this.querySelectorAll('.front-box a, .back-box a').forEach((link) => {
        if (disable) {
          link.setAttribute('tabindex', '-1');
          link.dataset.vvjrDisabled = 'true';
        }
        else if (link.dataset.vvjrDisabled === 'true') {
          link.removeAttribute('tabindex');
          delete link.dataset.vvjrDisabled;
        }
      });
      // Suppress mouse navigation on nested links while disabled (v1 parity;
      // tabindex=-1 alone only blocks keyboard focus). Bind the delegated
      // click guard once — it honors the current _nestedLinksDisabled flag.
      if (!this._nestedLinkGuardBound) {
        this._nestedLinkGuardBound = true;
        this.addEventListener('click', (event) => {
          if (!this._nestedLinksDisabled) { return;
          }
          const target = event.target;
          if (target instanceof Element
            && target.closest('.front-box a, .back-box a')) {
            event.preventDefault();
          }
        }, { signal: this.signal });
      }
    }

  }

  if (!customElements.get('vvjr-reveal')) {
    customElements.define('vvjr-reveal', VvjrRevealElement);
  }

})(Drupal);
