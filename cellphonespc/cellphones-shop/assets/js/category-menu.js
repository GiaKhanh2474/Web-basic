(() => {
  'use strict';
  const menu = document.querySelector('[data-category-menu]');
  const backdrop = document.querySelector('[data-category-backdrop]');
  if (!menu) return;
  const trigger = menu.querySelector('summary');

  const syncBackdrop = () => { if (backdrop) backdrop.hidden = !menu.open; };
  const closeMenu = () => { menu.open = false; syncBackdrop(); };
  menu.addEventListener('toggle', syncBackdrop);
  syncBackdrop();

  document.addEventListener('click', event => {
    if (menu.open && !menu.contains(event.target)) closeMenu();
  });
  document.addEventListener('focusin', event => {
    if (menu.open && !menu.contains(event.target)) closeMenu();
  });
  document.addEventListener('keydown', event => {
    if (event.key === 'Escape' && menu.open) {
      event.preventDefault();
      closeMenu();
      trigger.focus();
    }
  });
  menu.addEventListener('click', event => {
    if (event.target.closest('a[href]')) closeMenu();
  });
})();
