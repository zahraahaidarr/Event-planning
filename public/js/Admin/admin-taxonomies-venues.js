/* ================== storage helpers ================== */
const getStored = (k, fb) => { try { const v = JSON.parse(localStorage.getItem(k) || 'null'); return v ?? fb; } catch { return fb; } };
const setStored = (k, v) => localStorage.setItem(k, JSON.stringify(v));

/* ===== Helpers ===== */
const csrf = () => document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
const jsonOrText = async (res) => {
  const ct = res.headers.get('content-type') || '';
  if (ct.includes('application/json')) return res.json();
  return res.text();
};

/* ========== Render helpers ========== */
function renderSelect(selectEl, items){
  if (!selectEl) return;
  selectEl.innerHTML = items.length
    ? items.map((t,i)=>`<option value="${i}">${t}</option>`).join('')
    : `<option value="">(empty)</option>`;
  selectEl.disabled = items.length === 0;
}

/* ------------------------------------------------------------------
   EVENT CATEGORIES (DB-backed)
-------------------------------------------------------------------*/
let categories = []; // [{category_id,name,description}]

async function fetchCategories() {
  try {
    const res = await fetch(window.VH.routes.catIndex, {
      credentials: 'same-origin',
      headers: { 'Accept':'application/json', 'X-Requested-With':'XMLHttpRequest' }
    });
    if (!res.ok) { console.error('Fetch categories failed', res.status, await jsonOrText(res)); return; }
    const json = await res.json();
    categories = Array.isArray(json.data) ? json.data : [];
    renderCategoryList();
  } catch (e) { console.error('Network (fetchCategories):', e); }
}

function renderCategoryList(){
  const listEl = document.getElementById('cat_list');
  if (!listEl) return;

  if (!categories.length){
    listEl.innerHTML = `<div class="list-item"><span class="muted">(no items)</span><span></span></div>`;
    return;
  }

  listEl.innerHTML = categories.map(c => `
    <div class="list-item">
      <span class="list-name">${c.name}</span>
      <span class="list-delete" data-id="${c.category_id}">Delete</span>
    </div>
  `).join('');

  // bind deletes
  listEl.querySelectorAll('.list-delete').forEach(el => {
    el.addEventListener('click', async (e) => {
      const id = e.currentTarget.getAttribute('data-id');
      if (!id || !confirm('Delete this category?')) return;

      try {
        const url = window.VH.routes.catDelete.replace('__ID__', encodeURIComponent(id));
        const res = await fetch(url, {
          method: 'DELETE',
          credentials: 'same-origin',
          headers: {
            'Accept':'application/json',
            'X-CSRF-TOKEN': csrf(),
            'X-Requested-With':'XMLHttpRequest'
          }
        });

        if (res.ok){
          categories = categories.filter(x => String(x.category_id) !== String(id));
          renderCategoryList();
          return;
        }

        let msg = `Failed to delete (${res.status}). `;
        const body = await jsonOrText(res);
        if (typeof body === 'object' && body?.msg) msg += body.msg;
        alert(msg);
      } catch (err) {
        console.error('Network (delete category):', err);
        alert('Network error. Check console.');
      }
    });
  });
}

// Add Category
const catAddBtn = document.getElementById('cat_add');
if (catAddBtn){
  catAddBtn.onclick = async ()=>{
    const input = document.getElementById('cat_input');
    const name = (input.value || '').trim();
    if(!name) return;

    try {
      const res = await fetch(window.VH.routes.catStore, {
        method: 'POST',
        credentials: 'same-origin',
        headers: {
          'Content-Type':'application/json',
          'Accept':'application/json',
          'X-CSRF-TOKEN': csrf(),
          'X-Requested-With':'XMLHttpRequest'
        },
        body: JSON.stringify({ name })
      });

      if (res.ok){
        const json = await res.json();
        categories.push(json.data);
        renderCategoryList();
        input.value = '';
        return;
      }

      if (res.status === 422){
        const j = await res.json().catch(()=>({}));
        alert(Object.values(j.errors || {}).flat().join('\n') || 'Validation error.');
        return;
      }

      let msg = `Failed to add (${res.status}). `;
      const body = await jsonOrText(res);
      if (typeof body === 'object' && body?.message) msg += body.message;
      alert(msg);
    } catch (e) {
      console.error('Network (add category):', e);
      alert('Network error. Check console.');
    }
  };
}

/* ------------------------------------------------------------------
   WORKER TYPES (DB-backed)
-------------------------------------------------------------------*/
let workers = []; // [{role_type_id,name,description}]

async function fetchWorkerTypes() {
  try {
    const res = await fetch(window.VH.routes.wtIndex, {
      credentials: 'same-origin',
      headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
    });
    if (!res.ok) {
      const body = await jsonOrText(res);
      console.error('Fetch worker types failed:', res.status, body);
      return;
    }
    const json = await res.json();
    workers = Array.isArray(json.data) ? json.data : [];
    renderWorkerList();
  } catch (e) {
    console.error('Network error (fetchWorkerTypes):', e);
  }
}

function renderWorkerList() {
  const listEl = document.getElementById('wt_list');
  if (!listEl) return;

  if (!workers.length) {
    listEl.innerHTML = `<div class="list-item"><span class="muted">(no items)</span><span></span></div>`;
    return;
  }

  listEl.innerHTML = workers.map(w => `
    <div class="list-item">
      <span class="list-name">${w.name}</span>
      <span class="list-delete" data-id="${w.role_type_id}">Delete</span>
    </div>
  `).join('');

  // bind delete clicks
  listEl.querySelectorAll('.list-delete').forEach(el => {
    el.addEventListener('click', async (e) => {
      const id = e.currentTarget.getAttribute('data-id');
      if (!id || !confirm('Delete this worker type?')) return;
      try {
        const url = window.VH.routes.wtDelete.replace('__ID__', encodeURIComponent(id));
        const res = await fetch(url, {
          method: 'DELETE',
          credentials: 'same-origin',
          headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': csrf(),
            'X-Requested-With': 'XMLHttpRequest'
          }
        });

        if (res.ok) {
          workers = workers.filter(x => String(x.role_type_id) !== String(id));
          renderWorkerList();
          return;
        }

        let msg = `Failed to delete (${res.status}). `;
        if (res.status === 419) msg += 'CSRF/session expired.';
        const body = await jsonOrText(res);
        if (typeof body === 'object' && body?.msg) msg += body.msg;
        alert(msg);
      } catch (err) {
        console.error('Network error (delete worker type):', err);
        alert('Network error. Check console.');
      }
    });
  });
}

// Add Worker Type
const wtAddBtn = document.getElementById('wt_add');
if (wtAddBtn){
  wtAddBtn.onclick = async ()=>{
    const input = document.getElementById('wt_input');
    const name = (input.value || '').trim();
    if(!name) return;

    try {
      const res = await fetch(window.VH.routes.wtStore, {
        method: 'POST',
        credentials: 'same-origin',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'X-CSRF-TOKEN': csrf(),
          'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({ name })
      });

      if (res.ok) {
        const json = await res.json();
        workers.push(json.data);
        renderWorkerList();
        input.value = '';
        return;
      }

      if (res.status === 422) {
        const j = await res.json().catch(()=>({}));
        alert(Object.values(j.errors || {}).flat().join('\n') || 'Validation error.');
        return;
      }
      let msg = `Failed to add (${res.status}). `;
      if (res.status === 419) msg += 'CSRF token mismatch or session expired.';
      if (res.status === 404) msg += 'Route not found.';
      if (res.status === 401 || res.status === 302) msg += 'Not authenticated.';
      const body = await jsonOrText(res);
      if (typeof body === 'object' && body?.message) msg += body.message;
      alert(msg);
    } catch (e) {
      console.error('Network error (add worker type):', e);
      alert('Network error. Check console.');
    }
  };
}

/* ------------------------------------------------------------------
   VENUES (still localStorage, unchanged)
-------------------------------------------------------------------*/
/* ------------------------------------------------------------------
   VENUES (DB-backed)
-------------------------------------------------------------------*/
let venues = []; // [{id, name, city, area_m2}]

async function fetchVenues() {
  try {
    const res = await fetch(window.VH.routes.venuesIndex, {
      headers: { 'Accept': 'application/json', 'X-Requested-With':'XMLHttpRequest' },
      credentials: 'same-origin'
    });
    if (!res.ok) {
      console.error('Fetch venues failed', res.status, await jsonOrText(res));
      renderVenues();
      return;
    }
    const json = await res.json();
    venues = Array.isArray(json.data) ? json.data : [];
    renderVenues();
  } catch (e) {
    console.error('Network (fetchVenues):', e);
  }
}

function renderVenues() {
  const tbody = document.getElementById('v_rows');
  if (!tbody) return;

  if (!venues.length) {
    tbody.innerHTML = `<tr><td colspan="4" class="muted" style="padding:16px">No venues yet.</td></tr>`;
    return;
  }

  tbody.innerHTML = venues.map(v => `
    <tr>
      <td>${v.name}</td>
      <td>${v.city ?? '—'}</td>
      <td>${v.area_m2}</td>
      <td style="text-align:right">
        <button class="btn btn-danger btn-sm" onclick="deleteVenue(${v.id})">Delete</button>
      </td>
    </tr>
  `).join('');
}

async function addVenue() {
  const name = (document.getElementById('v_name').value || '').trim();
  const city = (document.getElementById('v_city').value || '').trim();
  const area = Number(document.getElementById('v_area').value || 0);

  if (!name) { alert('Please enter a venue name.'); return; }
  if (!(area > 0)) { alert('Area must be a positive number.'); return; }

  try {
    const res = await fetch(window.VH.routes.venuesStore, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-CSRF-TOKEN': csrf(),
        'X-Requested-With':'XMLHttpRequest'
      },
      credentials: 'same-origin',
      body: JSON.stringify({ name, city, area_m2: area })
    });

    if (res.ok) {
      const json = await res.json();
      venues.push(json.data);
      renderVenues();
      document.getElementById('v_name').value = '';
      document.getElementById('v_city').value = '';
      document.getElementById('v_area').value = '';
      return;
    }

    if (res.status === 422) {
      const j = await res.json().catch(()=>({}));
      alert(Object.values(j.errors || {}).flat().join('\n') || 'Validation error.');
      return;
    }

    let msg = `Failed to add venue (${res.status}). `;
    const body = await jsonOrText(res);
    if (typeof body === 'object' && body?.message) msg += body.message;
    alert(msg);
  } catch (err) {
    console.error('Network (addVenue):', err);
    alert('Network error. Check console.');
  }
}

async function deleteVenue(id) {
  if (!confirm('Delete this venue?')) return;
  const url = window.VH.routes.venuesDelete.replace('__ID__', encodeURIComponent(id));

  try {
    const res = await fetch(url, {
      method: 'DELETE',
      headers: {
        'Accept': 'application/json',
        'X-CSRF-TOKEN': csrf(),
        'X-Requested-With':'XMLHttpRequest'
      },
      credentials: 'same-origin'
    });

    if (res.ok) {
      venues = venues.filter(v => String(v.id) !== String(id));
      renderVenues();
      return;
    }

    let msg = `Failed to delete venue (${res.status}). `;
    const body = await jsonOrText(res);
    if (typeof body === 'object' && body?.message) msg += body.message;
    alert(msg);
  } catch (err) {
    console.error('Network (deleteVenue):', err);
    alert('Network error. Check console.');
  }
}

const vAddBtn = document.getElementById('v_add');
if (vAddBtn) vAddBtn.onclick = addVenue;

/* ===== Init: add this near the bottom where you call renderAll() ===== */
function renderAll() {
  // Categories (DB)
  renderCategoryList(); fetchCategories();

  // Worker Types (DB)
  renderWorkerList();   fetchWorkerTypes();

  // Venues (DB)
  fetchVenues();
}
document.addEventListener('DOMContentLoaded', renderAll);
// ===== Light/Dark Theme Toggle (persistent) =====
(function themeManager() {
  const KEY = 'vh_theme';
  const root = document.documentElement;
  const btn  = document.getElementById('theme-toggle');
  const icon = document.getElementById('theme-icon');

  function apply(theme) {
    root.setAttribute('data-theme', theme);
    // icon: show the opposite action available
    if (icon) icon.textContent = (theme === 'dark') ? '☀️' : '🌙';
  }

  // initial: saved -> OS preference -> 'dark'
  const saved   = localStorage.getItem(KEY);
  const prefers = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
  const initial = saved || prefers || 'dark';
  apply(initial);

  if (btn) {
    btn.addEventListener('click', () => {
      const next = (root.getAttribute('data-theme') === 'dark') ? 'light' : 'dark';
      localStorage.setItem(KEY, next);
      apply(next);
    });
  }
})();
