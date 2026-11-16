(() => {
  'use strict';
  const base = document.querySelector('meta[name="app-url"]')?.content || './';
  const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';
  const appUrl = path => `${base.replace(/\/$/, '')}/${path}`;
  const icons = {
    check: '<path d="m5 12 4 4L19 6"/>', close: '<path d="m6 6 12 12M6 18 18 6"/>',
    arrow: '<path d="M4 12h16m-6-6 6 6-6 6"/>',
    heart: '<path d="M20.8 4.6a5.5 5.5 0 0 0-7.8 0L12 5.7l-1.1-1.1a5.5 5.5 0 0 0-7.8 7.8L12 21l8.8-8.6a5.5 5.5 0 0 0 0-7.8Z"/>'
  };
  function makeIcon(name) {
    const element = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
    element.setAttribute('viewBox', '0 0 24 24'); element.setAttribute('fill', 'none');
    element.setAttribute('stroke', 'currentColor'); element.setAttribute('stroke-width', '1.7');
    element.setAttribute('stroke-linecap', 'round'); element.setAttribute('stroke-linejoin', 'round');
    element.setAttribute('aria-hidden', 'true'); element.classList.add('icon');
    element.innerHTML = icons[name] || icons.check;
    return element;
  }
  function toast(message, error = false, showCart = false) {
    const region = document.getElementById('toast-region');
    if (!region) return;
    while (region.children.length > 1) region.firstElementChild.remove();
    const el = document.createElement('div'); el.className = `toast${error ? ' error' : ''}`;
    el.setAttribute('role', error ? 'alert' : 'status');
    const symbol = document.createElement('span'); symbol.className = 'toast-symbol'; symbol.append(makeIcon(error ? 'close' : 'check'));
    const copy = document.createElement('div'); copy.className = 'toast-copy';
    const text = document.createElement('p'); text.textContent = message; copy.append(text);
    if (showCart) { const link = document.createElement('a'); link.href = appUrl('cart.php'); link.append('Xem giỏ hàng', makeIcon('arrow')); copy.append(link); }
    const close = document.createElement('button'); close.className = 'toast-close'; close.type = 'button'; close.setAttribute('aria-label', 'Đóng thông báo'); close.append(makeIcon('close')); close.addEventListener('click', () => el.remove());
    el.append(symbol, copy, close); region.append(el);
    setTimeout(() => el.remove(), error ? 9000 : 6500);
  }
  let cartBusy = false;
  async function changeCart(action, id, quantity, button) {
    if (cartBusy) return;
    cartBusy = true;
    const wasDisabled = button.disabled; button.disabled = true; button.setAttribute('aria-busy', 'true');
    try {
      const response = await fetch(appUrl('api/cart.php'), {
        method: 'POST', credentials: 'same-origin',
        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
        body: JSON.stringify({ action, product_id: Number(id), quantity: Number(quantity), csrf_token: csrf })
      });
      const data = await response.json().catch(() => { throw new Error('Chưa thể cập nhật giỏ hàng. Vui lòng thử lại.'); });
      if (!response.ok || !data.success) throw new Error(data.message || 'Không thể cập nhật giỏ hàng.');
      document.querySelectorAll('[data-cart-count]').forEach(badge => { badge.textContent = data.count; });
      if (action === 'add') { toast(data.message, false, true); }
      else { try { sessionStorage.setItem('cart-feedback', data.message || 'Đã cập nhật giỏ hàng.'); } catch (_) { /* Reload even if browser storage is unavailable. */ } window.location.reload(); }
    } catch (error) {
      toast(error.message || 'Không kết nối được máy chủ. Vui lòng thử lại.', true);
    } finally { cartBusy = false; button.disabled = wasDisabled; button.removeAttribute('aria-busy'); }
  }
  document.querySelectorAll('[data-add-cart]').forEach(button => {
    button.addEventListener('click', () => {
      const input = button.dataset.quantityInput ? document.getElementById(button.dataset.quantityInput) : null;
      const quantity = input ? Number(input.value) : 1;
      if (!Number.isInteger(quantity) || quantity < 1 || (input && quantity > Number(input.max))) {
        toast('Vui lòng chọn số lượng hợp lệ trong giới hạn hàng có sẵn.', true); input?.focus(); return;
      }
      changeCart('add', button.dataset.addCart, quantity, button);
    });
  });
  document.querySelectorAll('[data-update-cart]').forEach(button => button.addEventListener('click', () => changeCart('update', button.dataset.updateCart, button.dataset.quantity, button)));
  document.querySelectorAll('[data-remove-cart]').forEach(button => button.addEventListener('click', () => changeCart('remove', button.dataset.removeCart, 0, button)));
  document.querySelectorAll('[data-step]').forEach(button => button.addEventListener('click', () => {
    const input = document.getElementById(button.dataset.target); if (!input) return;
    const value = Number(input.value) || 1;
    input.value = Math.max(Number(input.min) || 1, Math.min(Number(input.max) || 99, value + Number(button.dataset.step)));
    input.dispatchEvent(new Event('change', { bubbles: true }));
  }));
  document.querySelectorAll('[data-auto-submit]').forEach(select => select.addEventListener('change', () => select.form.requestSubmit()));
  document.querySelector('.back-to-top')?.addEventListener('click', () => window.scrollTo({ top: 0, behavior: window.matchMedia('(prefers-reduced-motion: reduce)').matches ? 'instant' : 'smooth' }));
  try { const message = sessionStorage.getItem('cart-feedback'); if (message) { sessionStorage.removeItem('cart-feedback'); toast(message); } } catch (_) { /* Storage can be unavailable in a private browser. */ }

  const favoriteKey = 'monitor-shop-favorites-v1';
  let favorites = [];
  try {
    const saved = JSON.parse(localStorage.getItem(favoriteKey) || '[]');
    if (Array.isArray(saved)) favorites = saved.filter(item => item && Number.isInteger(Number(item.id)) && typeof item.name === 'string' && typeof item.href === 'string' && typeof item.image === 'string' && typeof item.price === 'string');
  } catch (_) { favorites = []; }
  function safeLocalUrl(value) { try { const target = new URL(value, window.location.href); return target.origin === location.origin && ['http:', 'https:'].includes(target.protocol) ? target.href : appUrl('laptop.php'); } catch (_) { return appUrl('laptop.php'); } }
  function saveFavorites() {
    try { localStorage.setItem(favoriteKey, JSON.stringify(favorites)); } catch (_) { /* Favorites still work for the current page. */ }
    document.querySelectorAll('[data-favorite]').forEach(button => {
      const saved = favorites.some(item => String(item.id) === button.dataset.favorite);
      button.classList.toggle('active', saved); button.setAttribute('aria-pressed', String(saved));
    });
  }
  document.querySelectorAll('[data-favorite]').forEach(button => button.addEventListener('click', () => {
    const id = button.dataset.favorite;
    if (favorites.some(item => String(item.id) === id)) { favorites = favorites.filter(item => String(item.id) !== id); toast('Đã bỏ sản phẩm khỏi danh sách yêu thích.'); }
    else { favorites.push({ id, name: button.dataset.name, image: button.dataset.image, href: button.dataset.href, price: button.dataset.price }); toast('Đã lưu sản phẩm vào danh sách yêu thích.'); }
    saveFavorites();
  }));
  saveFavorites();
  const dialog = document.getElementById('favorites-dialog');
  function renderFavorites() {
    const content = document.getElementById('favorites-content'); if (!content) return; content.replaceChildren();
    if (!favorites.length) {
      const empty = document.createElement('div'); empty.className = 'favorites-empty';
      const p = document.createElement('p'); p.textContent = 'Chạm vào trái tim trên sản phẩm để lưu lựa chọn bạn yêu thích.';
      empty.append(makeIcon('heart'), p); content.append(empty); return;
    }
    favorites.forEach(item => {
      const row = document.createElement('div'); row.className = 'favorite-item';
      const imageLink = document.createElement('a'); imageLink.href = safeLocalUrl(item.href);
      const img = document.createElement('img'); img.src = safeLocalUrl(item.image); img.alt = item.name; imageLink.append(img);
      const info = document.createElement('div'); const link = document.createElement('a'); link.href = safeLocalUrl(item.href);
      const name = document.createElement('h3'); name.textContent = item.name; link.append(name);
      const price = document.createElement('strong'); price.textContent = item.price; info.append(link, price);
      const remove = document.createElement('button'); remove.type = 'button'; remove.setAttribute('aria-label', `Bỏ yêu thích ${item.name}`); remove.append(makeIcon('close'));
      remove.addEventListener('click', () => { favorites = favorites.filter(f => String(f.id) !== String(item.id)); saveFavorites(); renderFavorites(); });
      row.append(imageLink, info, remove); content.append(row);
    });
  }
  document.querySelectorAll('[data-show-favorites]').forEach(button => button.addEventListener('click', () => { renderFavorites(); if (dialog && !dialog.open) dialog.showModal(); }));
  document.querySelector('[data-close-dialog]')?.addEventListener('click', () => dialog.close());
  dialog?.addEventListener('click', event => { if (event.target === dialog) { const rect = dialog.getBoundingClientRect(); if (event.clientX < rect.left || event.clientX > rect.right || event.clientY < rect.top || event.clientY > rect.bottom) dialog.close(); } });
})();
