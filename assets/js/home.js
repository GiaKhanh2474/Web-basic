(() => {
  'use strict';

  function initializeProductRows() {
    document.querySelectorAll('[data-product-section]').forEach(section => {
      const track = section.querySelector('.home-product-track');
      if (!track || track.dataset.scrollInitialized === 'true') return;
      track.dataset.scrollInitialized = 'true';

      const previous = section.querySelector('[data-scroll-prev]');
      const next = section.querySelector('[data-scroll-next]');
      const position = section.querySelector('[data-scroll-position]');
      const cards = Array.from(track.children).filter(child => child.matches('article'));
      const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)');
      let frame = null;

      function update() {
        frame = null;
        const maximum = Math.max(0, track.scrollWidth - track.clientWidth);
        const offset = Math.max(0, Math.min(maximum, track.scrollLeft));
        if (previous) previous.disabled = maximum <= 2 || offset <= 2;
        if (next) next.disabled = maximum <= 2 || maximum - offset <= 2;

        if (position) {
          const bounds = track.getBoundingClientRect();
          const left = bounds.left + track.clientLeft;
          const right = left + track.clientWidth;
          const visible = [];
          cards.forEach((card, index) => {
            const cardBounds = card.getBoundingClientRect();
            const overlap = Math.min(cardBounds.right, right) - Math.max(cardBounds.left, left);
            if (cardBounds.width > 0 && overlap > 1) visible.push(index + 1);
          });
          const first = visible[0] || 0;
          const last = visible[visible.length - 1] || 0;
          position.textContent = `${first === last ? first : `${first}–${last}`} / ${cards.length}`;
        }
      }

      function scheduleUpdate() {
        if (frame === null) frame = window.requestAnimationFrame(update);
      }

      function scrollToOffset(offset) {
        const maximum = Math.max(0, track.scrollWidth - track.clientWidth);
        track.scrollTo({
          left: Math.max(0, Math.min(maximum, offset)),
          behavior: reducedMotion.matches ? 'auto' : 'smooth'
        });
        scheduleUpdate();
      }

      function scrollPage(direction) {
        scrollToOffset(track.scrollLeft + direction * track.clientWidth * 0.9);
      }

      previous?.addEventListener('click', () => scrollPage(-1));
      next?.addEventListener('click', () => scrollPage(1));
      track.addEventListener('scroll', scheduleUpdate, { passive: true });
      track.addEventListener('keydown', event => {
        // Leave keyboard shortcuts on product links and cart buttons untouched.
        if (event.target !== track || event.altKey || event.ctrlKey || event.metaKey || event.shiftKey) return;
        if (event.key === 'ArrowLeft' || event.key === 'ArrowRight') {
          event.preventDefault();
          scrollPage(event.key === 'ArrowLeft' ? -1 : 1);
        } else if (event.key === 'Home' || event.key === 'End') {
          event.preventDefault();
          scrollToOffset(event.key === 'Home' ? 0 : track.scrollWidth);
        }
      });

      if ('ResizeObserver' in window) {
        const observer = new ResizeObserver(scheduleUpdate);
        observer.observe(track);
        cards.forEach(card => observer.observe(card));
      } else {
        window.addEventListener('resize', scheduleUpdate, { passive: true });
      }
      update();
    });
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initializeProductRows, { once: true });
  } else {
    initializeProductRows();
  }
})();
