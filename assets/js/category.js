/* category.js

Client-side validation + async callers for category actions:
- add_category_action.php  (POST)
- fetch_category_action.php (GET)
- update_category_action.php (POST)
- delete_category_action.php (POST)

Features:
- Validates inputs (type checks, length, allowed characters)
- Uses fetch() with graceful handling if server returns JSON or redirects
- Shows a small toast popup for success / error messages
- Provides init helpers to wire your forms / buttons

Usage examples (at bottom) show how to bind forms/buttons.
*/

(() => {
  // ------------------ Utilities ------------------
  function el(html) {
    const template = document.createElement('template');
    template.innerHTML = html.trim();
    return template.content.firstChild;
  }

  // Simple toast system
  const TOAST_LIFETIME = 3500;
  function ensureToastContainer() {
    let c = document.getElementById('cat-toast-container');
    if (!c) {
      c = document.createElement('div');
      c.id = 'cat-toast-container';
      Object.assign(c.style, {
        position: 'fixed',
        right: '18px',
        top: '18px',
        zIndex: 99999,
        display: 'flex',
        flexDirection: 'column',
        gap: '8px',
        alignItems: 'flex-end',
      });
      document.body.appendChild(c);
    }
    return c;
  }

  function showToast(message, type = 'info') {
    const container = ensureToastContainer();
    const node = document.createElement('div');
    node.textContent = message;
    Object.assign(node.style, {
      padding: '10px 14px',
      borderRadius: '10px',
      boxShadow: '0 6px 18px rgba(0,0,0,0.08)',
      maxWidth: '320px',
      fontFamily: 'system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial',
      fontSize: '14px',
      color: '#fff',
      opacity: '0',
      transform: 'translateY(-6px)',
      transition: 'opacity .18s ease, transform .18s ease',
    });

    if (type === 'success') node.style.background = 'linear-gradient(90deg,#16a34a,#059669)';
    else if (type === 'error') node.style.background = 'linear-gradient(90deg,#dc2626,#b91c1c)';
    else node.style.background = 'linear-gradient(90deg,#2563eb,#1d4ed8)';

    container.appendChild(node);

    // entrance
    requestAnimationFrame(() => {
      node.style.opacity = '1';
      node.style.transform = 'translateY(0)';
    });

    // auto remove
    setTimeout(() => {
      node.style.opacity = '0';
      node.style.transform = 'translateY(-6px)';
      setTimeout(() => node.remove(), 200);
    }, TOAST_LIFETIME);
  }

  // Validate category name
  function validateCategoryName(name) {
    if (typeof name !== 'string') return { ok: false, reason: 'Category name must be text.' };
    const trimmed = name.trim();
    if (trimmed.length === 0) return { ok: false, reason: 'Category name cannot be empty.' };
    if (trimmed.length < 2 || trimmed.length > 80) return { ok: false, reason: 'Category name must be between 2 and 80 characters.' };
    // allow letters, numbers, spaces, hyphen, underscore, ampersand, parentheses
    const re = /^[\p{L}0-9 _\-&()\.]+$/u;
    if (!re.test(trimmed)) return { ok: false, reason: 'Category name contains invalid characters.' };
    return { ok: true, value: trimmed };
  }

  function validateId(id) {
    const n = Number(id);
    if (!Number.isInteger(n) || n <= 0) return { ok: false, reason: 'Invalid category id.' };
    return { ok: true, value: n };
  }

  // fetch helper - tries to parse JSON, otherwise fallbacks to success/error based on response.ok
  async function doFetch(url, opts = {}) {
    opts.headers = Object.assign({ 'X-Requested-With': 'XMLHttpRequest' }, opts.headers || {});
    const resp = await fetch(url, opts);
    const ctype = resp.headers.get('Content-Type') || '';

    // If server returned JSON, parse and return
    if (ctype.includes('application/json')) {
      const json = await resp.json();
      return { ok: resp.ok, json };
    }

    // Not JSON: attempt to read text but treat 200-299 as ok
    const text = await resp.text();
    return { ok: resp.ok, text };
  }

  // ------------------ API Actions ------------------
  async function addCategory({ name, onSuccess, onError }) {
    const v = validateCategoryName(name);
    if (!v.ok) {
      showToast(v.reason, 'error');
      if (onError) onError(v.reason);
      return false;
    }

    const form = new FormData();
    form.append('category_name', v.value);

    try {
      const res = await doFetch('../actions/add_category_action.php', {
        method: 'POST',
        body: form,
      });

      // Prefer JSON success message
      if (res.json) {
        if (res.json.success) {
          showToast(res.json.message || 'Category added', 'success');
          if (onSuccess) onSuccess(res.json);
          return res.json;
        } else {
          const msg = res.json.message || 'Failed to add category.';
          showToast(msg, 'error');
          if (onError) onError(res.json);
          return res.json;
        }
      }

      // fallback based on HTTP status
      if (res.ok) {
        showToast('Category added', 'success');
        if (onSuccess) onSuccess(res);
        return true;
      } else {
        showToast(' category', 'error');
        if (onError) onError(res);
        return false;
      }
    } catch (e) {
      showToast('Network error: ' + e.message, 'error');
      if (onError) onError(e);
      return false;
    }
  }

  async function fetchCategories({ id = null, limit = 100, offset = 0, q = '', onSuccess, onError } = {}) {
    const params = new URLSearchParams();
    if (id !== null) params.set('id', String(id));
    if (limit) params.set('limit', String(limit));
    if (offset) params.set('offset', String(offset));
    if (q) params.set('q', q);
    const url = '/actions/fetch_category_action.php?' + params.toString();

    try {
      const res = await doFetch(url, { method: 'GET' });
      if (res.json) {
        if (res.json.success) {
          if (onSuccess) onSuccess(res.json.data || []);
          return res.json.data || [];
        } else {
          showToast(res.json.message || 'Failed to fetch categories', 'error');
          if (onError) onError(res.json);
          return [];
        }
      }

      if (res.ok) {
        // if non-json but ok, attempt naive HTML parse? return text
        if (onSuccess) onSuccess(res.text);
        return res.text;
      }

      showToast('Failed to fetch categories', 'error');
      if (onError) onError(res);
      return [];
    } catch (e) {
      showToast('Network error: ' + e.message, 'error');
      if (onError) onError(e);
      return [];
    }
  }

  async function updateCategory({ id, name, onSuccess, onError }) {
    const vId = validateId(id);
    if (!vId.ok) {
      showToast(vId.reason, 'error');
      if (onError) onError(vId.reason);
      return false;
    }
    const v = validateCategoryName(name);
    if (!v.ok) {
      showToast(v.reason, 'error');
      if (onError) onError(v.reason);
      return false;
    }

    const form = new FormData();
    form.append('category_id', String(vId.value));
    form.append('category_name', v.value);

    try {
      const res = await doFetch('/actions/update_category_action.php', { method: 'POST', body: form });
      if (res.json) {
        if (res.json.success) {
          showToast(res.json.message || 'Category updated', 'success');
          if (onSuccess) onSuccess(res.json);
          return res.json;
        } else {
          showToast(res.json.message || 'Failed to update category', 'error');
          if (onError) onError(res.json);
          return res.json;
        }
      }

      if (res.ok) {
        showToast('Category updated', 'success');
        if (onSuccess) onSuccess(res);
        return true;
      }

      showToast('Failed to update category', 'error');
      if (onError) onError(res);
      return false;
    } catch (e) {
      showToast('Network error: ' + e.message, 'error');
      if (onError) onError(e);
      return false;
    }
  }

  async function deleteCategory({ id, onSuccess, onError }) {
    const vId = validateId(id);
    if (!vId.ok) {
      showToast(vId.reason, 'error');
      if (onError) onError(vId.reason);
      return false;
    }

    const form = new FormData();
    form.append('category_id', String(vId.value));

    try {
      const res = await doFetch('/actions/delete_category_action.php', { method: 'POST', body: form });

      if (res.json) {
        if (res.json.success) {
          showToast(res.json.message || 'Category deleted', 'success');
          if (onSuccess) onSuccess(res.json);
          return res.json;
        } else {
          showToast(res.json.message || 'Failed to delete category', 'error');
          if (onError) onError(res.json);
          return res.json;
        }
      }

      if (res.ok) {
        showToast('Category deleted', 'success');
        if (onSuccess) onSuccess(res);
        return true;
      }

      showToast('Failed to delete category', 'error');
      if (onError) onError(res);
      return false;
    } catch (e) {
      showToast('Network error: ' + e.message, 'error');
      if (onError) onError(e);
      return false;
    }
  }

  // ------------------ Convenience bindings ------------------
  function wireAddForm(selector) {
    const form = document.querySelector(selector);
    if (!form) return;
    form.addEventListener('submit', async (ev) => {
      ev.preventDefault();
      const input = form.querySelector('input[name="category_name"]');
      if (!input) return showToast('Category input not found', 'error');
      await addCategory({ name: input.value, onSuccess: () => { form.reset(); } });
    });
  }

  function wireEditForm(selector) {
    const form = document.querySelector(selector);
    if (!form) return;
    form.addEventListener('submit', async (ev) => {
      ev.preventDefault();
      const idEl = form.querySelector('input[name="category_id"]');
      const nameEl = form.querySelector('input[name="category_name"]');
      if (!idEl || !nameEl) return showToast('Required fields missing', 'error');
      await updateCategory({ id: idEl.value, name: nameEl.value });
    });
  }

  function wireDeleteButtons(selector) {
    // expects buttons with data-id attribute
    document.addEventListener('click', (ev) => {
      const btn = ev.target.closest(selector);
      if (!btn) return;
      const id = btn.dataset.id;
      if (!id) return showToast('Missing category id', 'error');

      // confirmation
      if (!confirm('Are you sure you want to delete this category? This cannot be undone.')) return;
      deleteCategory({ id, onSuccess: () => {
        // optionally remove the row from DOM if the button sits in a row
        const row = btn.closest('.user-item, .category-row, tr');
        if (row) row.remove();
      }});
    });
  }

  // Expose public API
  window.CategoryAPI = {
    addCategory,
    fetchCategories,
    updateCategory,
    deleteCategory,
    wireAddForm,
    wireEditForm,
    wireDeleteButtons,
    showToast,
  };

  // Auto-wire common selectors if present on page
  document.addEventListener('DOMContentLoaded', () => {
    // example form names used in your current HTML
    CategoryAPI.wireAddForm('form.collection-form');
    CategoryAPI.wireEditForm('form.edit-category-form');
    CategoryAPI.wireDeleteButtons('button[data-action="delete-category"]');
  });
})();
