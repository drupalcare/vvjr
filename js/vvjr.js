/**
 * @file
 * VVJ Reveal.
 *
 * Filename:     vvjr.js
 * Website:      https://www.flashwebcenter.com
 * Developer:    Alaa Haddad https://www.alaahaddad.com.
 */

(function (Drupal, drupalSettings, once) {
  Drupal.behaviors.VVJReveal = {
    attach: function (context, settings) {
      const reveales = once('vvjReveal', '.vvjr', context);

      reveales.forEach(reveal => {
        const breakpoints = reveal.getAttribute('data-breakpoints');
        const trigger = reveal.getAttribute('data-reveal-method');
        const parsedBreakpoint = parseInt(breakpoints, 10);
        const isBreakpointValid = !isNaN(parsedBreakpoint);

        function updateAccessibility(frontSide, backSide, isRevealed) {
          if (frontSide && backSide) {
            frontSide.setAttribute('aria-hidden', isRevealed ? 'true' : 'false');
            backSide.setAttribute('aria-hidden', isRevealed ? 'false' : 'true');

            const frontTabbables = frontSide.querySelectorAll('a, button, input, select, textarea, [tabindex]');
            const backTabbables = backSide.querySelectorAll('a, button, input, select, textarea, [tabindex]');

            frontTabbables.forEach(el => el.setAttribute('tabindex', isRevealed ? '-1' : '0'));
            backTabbables.forEach(el => el.setAttribute('tabindex', isRevealed ? '0' : '-1'));
          }
        }

        function applyRevealBehavior() {
          const currentWidth = window.innerWidth;

          if (breakpoints === 'all' || (isBreakpointValid && currentWidth >= parsedBreakpoint)) {
            activateReveal(reveal);
          } else {
            deactivateReveal(reveal);
          }
        }

        function activateReveal(reveal) {
          const revealItems = reveal.querySelectorAll('.reveal-item');
          if (!revealItems.length) return;

          revealItems.forEach(inner => {
            const frontSide = inner.querySelector('.front-box');
            const backSide = inner.querySelector('.back-box');
            if (!frontSide || !backSide) return;

            function handleMouseEnter() {
              inner.classList.add('revealed');
              updateAccessibility(frontSide, backSide, true);
            }

            function handleMouseLeave() {
              inner.classList.remove('revealed');
              updateAccessibility(frontSide, backSide, false);
            }

            function handleClick() {
              const isRevealed = inner.classList.toggle('revealed');
              updateAccessibility(frontSide, backSide, isRevealed);

              if (isRevealed) {
                applyRevealEffect(inner, backSide);
              } else {
                resetRevealEffect(inner, backSide);
              }
            }

            function handleKeyDown(event) {
              if (event.key === 'Enter' || event.key === ' ') {
                event.preventDefault();
                const isRevealed = inner.classList.toggle('revealed');
                updateAccessibility(frontSide, backSide, isRevealed);

                if (isRevealed) {
                  applyRevealEffect(inner, backSide);
                } else {
                  resetRevealEffect(inner, backSide);
                }
              }
            }

            // Remove existing event listeners to prevent duplication
            inner.removeEventListener('mouseenter', inner.revealHandlers?.handleMouseEnter);
            inner.removeEventListener('mouseleave', inner.revealHandlers?.handleMouseLeave);
            inner.removeEventListener('click', inner.revealHandlers?.handleClick);
            inner.removeEventListener('keydown', inner.revealHandlers?.handleKeyDown);

            // Attach event listeners based on the trigger type
            if (trigger === 'hover') {
              inner.addEventListener('mouseenter', handleMouseEnter);
              inner.addEventListener('mouseleave', handleMouseLeave);
            }

            if (trigger === 'click') {
              inner.addEventListener('click', handleClick);
            }

            inner.addEventListener('keydown', handleKeyDown);

            inner.revealHandlers = { handleMouseEnter, handleMouseLeave, handleClick, handleKeyDown };
          });
        }

        function applyRevealEffect(inner, backSide) {
          backSide.style.opacity = '1';
          if (inner.classList.contains('fade')) {
            backSide.style.opacity = '1';
          } else if (inner.classList.contains('zoom')) {
            backSide.style.transform = 'scale(1)';
          } else if (inner.classList.contains('top')) {
            backSide.style.transform = 'translateY(0)';
          } else if (inner.classList.contains('right')) {
            backSide.style.transform = 'translateX(0)';
          } else if (inner.classList.contains('bottom')) {
            backSide.style.transform = 'translateY(0)';
          } else if (inner.classList.contains('left')) {
            backSide.style.transform = 'translateX(0)';
          }
        }

        function resetRevealEffect(inner, backSide) {
          backSide.style.opacity = '0';
          if (inner.classList.contains('fade')) {
            backSide.style.opacity = '0';
          } else if (inner.classList.contains('zoom')) {
            backSide.style.transform = 'scale(0)';
          } else if (inner.classList.contains('top')) {
            backSide.style.transform = 'translateY(-100%)';
          } else if (inner.classList.contains('right')) {
            backSide.style.transform = 'translateX(100%)';
          } else if (inner.classList.contains('bottom')) {
            backSide.style.transform = 'translateY(100%)';
          } else if (inner.classList.contains('left')) {
            backSide.style.transform = 'translateX(-100%)';
          }
        }

        function deactivateReveal(reveal) {
          const revealItems = reveal.querySelectorAll('.reveal-item');
          if (!revealItems.length) return;

          revealItems.forEach(inner => {
            inner.classList.remove('revealed');
            if (inner.revealHandlers) {
              const { handleMouseEnter, handleMouseLeave, handleClick, handleKeyDown } = inner.revealHandlers;
              inner.removeEventListener('mouseenter', handleMouseEnter);
              inner.removeEventListener('mouseleave', handleMouseLeave);
              inner.removeEventListener('click', handleClick);
              inner.removeEventListener('keydown', handleKeyDown);
            }
          });
        }

        function resetRevealedOnResize() {
          const revealItems = reveal.querySelectorAll('.reveal-item');
          if (!revealItems.length) return;

          revealItems.forEach(inner => {
            if (inner.classList.contains('revealed')) {
              inner.classList.remove('revealed');
              const frontSide = inner.querySelector('.front-box');
              const backSide = inner.querySelector('.back-box');
              updateAccessibility(frontSide, backSide, false);
              resetRevealEffect(inner, backSide);
            }
          });
        }

        function updateAriaHiddenBasedOnWidth() {
          const currentWidth = window.innerWidth;
          const revealItems = reveal.querySelectorAll('.reveal-item');

          revealItems.forEach(inner => {
            const frontSide = inner.querySelector('.front-box');
            const backSide = inner.querySelector('.back-box');

            if (currentWidth < parsedBreakpoint && breakpoints !== 'all') {
              // Small screen: both sides visible, disable revealing
              frontSide.setAttribute('aria-hidden', 'false');
              backSide.setAttribute('aria-hidden', 'false');

              // Remove reveal classes and event listeners
              if (inner.classList.contains('revealed')) {
                inner.classList.remove('revealed');
              }
              inner.removeEventListener('mouseenter', inner.revealHandlers?.handleMouseEnter);
              inner.removeEventListener('mouseleave', inner.revealHandlers?.handleMouseLeave);
              inner.removeEventListener('click', inner.revealHandlers?.handleClick);
              inner.removeEventListener('keydown', inner.revealHandlers?.handleKeyDown);

            } else {
              // Large screen: standard behavior
              const isRevealed = inner.classList.contains('revealed');
              frontSide.setAttribute('aria-hidden', isRevealed ? 'true' : 'false');
              backSide.setAttribute('aria-hidden', isRevealed ? 'false' : 'true');

              // Reattach event listeners
              activateReveal(reveal);
            }
          });
        }

        // Throttle the resize event handler
        function throttle(func, limit) {
          let lastFunc;
          let lastRan;
          return function() {
            const context = this;
            const args = arguments;
            if (!lastRan) {
              func.apply(context, args);
              lastRan = Date.now();
            } else {
              clearTimeout(lastFunc);
              lastFunc = setTimeout(function() {
                if (Date.now() - lastRan >= limit) {
                  func.apply(context, args);
                  lastRan = Date.now();
                }
              }, limit - (Date.now() - lastRan));
            }
          };
        }

        // Initial check on load
        applyRevealBehavior();
        updateAriaHiddenBasedOnWidth();

        // Throttled resize event listener
        const resizeHandler = throttle(() => {
          resetRevealedOnResize();
          applyRevealBehavior();
          updateAriaHiddenBasedOnWidth();
        }, 200);

        window.addEventListener('resize', resizeHandler);
      });
    }
  };
})(Drupal, drupalSettings, once);
