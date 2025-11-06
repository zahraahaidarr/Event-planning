(function () {
  // --- CSRF helper (used by both actions) ---
  function csrf() {
    const t = document.querySelector('meta[name="csrf-token"]');
    return t ? t.getAttribute('content') : '';
  }

  // ---- Demo fallback if backend didn't pass employees ----
  const fallback = [
    { id: 1, name: "Ahmed Al-Rashid",   email: "ahmed.rashid@volunteerhub.com",   role: "Event Coordinator",      status: "active",    eventsManaged: 24, joinDate: "2024-06-15" },
    { id: 2, name: "Fatima Hassan",     email: "fatima.hassan@volunteerhub.com", role: "Volunteer Manager",      status: "suspended", eventsManaged: 18, joinDate: "2024-07-20" },
    { id: 3, name: "Mohammed Ali",      email: "mohammed.ali@volunteerhub.com",  role: "Event Coordinator",      status: "active",    eventsManaged: 15, joinDate: "2024-08-10" },
    { id: 4, name: "Sarah Abdullah",    email: "sarah.abdullah@volunteerhub.com",role: "Communications Manager", status: "pending",   eventsManaged: 8,  joinDate: "2024-09-05" }
  ];

  // Master list (source after server search), and current filtered list
  let employeesAll = (Array.isArray(window.initialEmployees) && window.initialEmployees.length)
    ? window.initialEmployees.map((e, idx) => ({
        id: e.id ?? e.employee_id ?? (idx + 1),
        name: e.name ?? (e.user?.name || ""),
        email: e.email ?? (e.user?.email || ""),
        role: e.position ?? e.role ?? "",
        status: ((e.status ?? e.user?.status ?? '') + '').toLowerCase() || 'pending',
        eventsManaged: e.eventsManaged ?? e.events_managed ?? 0,
        joinDate: e.hire_date ?? e.joinDate ?? ""     // expecting YYYY-MM-DD
      }))
    : fallback.slice();

  let employeesView = employeesAll.slice(); // what we render

  // ========= Helpers =========
  const escapeHtml = s => String(s).replace(/[&<>"']/g, m => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[m]));
  const byNameAZ   = (a,b) => (a.name||"").localeCompare(b.name||"", undefined, {sensitivity:'base'});
  const byNameZA   = (a,b) => -byNameAZ(a,b);

  function parts(d){ // returns {y, m, day} or null
    if(!d) return null;
    const m = /^(\d{4})-(\d{2})-(\d{2})$/.exec(d.trim());
    if(!m) return null;
    return { y: m[1], m: m[2], day: m[3] };
  }

  // ========= Card template =========
  function actionButtons(emp){
    const isActive = (emp.status === 'active');
    const toggleLabel = isActive ? 'Deactivate' : 'Activate';
    const toggleClass = isActive ? 'btn-danger' : 'btn-secondary';
    return `
      <button class="btn ${toggleClass} btn-sm" onclick="toggleActive(${emp.id})">${toggleLabel}</button>
      <button class="btn btn-secondary btn-sm" onclick="deleteEmployee(${emp.id})">Delete</button>
    `;
  }

  function cardTemplate(emp) {
    const initials = (emp.name || "").split(' ').map(n => n[0]).join('').slice(0, 3) || "VH";
    const badgeClass = emp.status === 'active'
      ? 'badge-active'
      : (emp.status === 'suspended' ? 'badge-suspended' : 'badge-pending');
    const badgeText  = emp.status ? (emp.status.charAt(0).toUpperCase() + emp.status.slice(1)) : "Pending";

    return `
      <div class="employee-card">
        <div class="employee-header">
          <div class="employee-avatar">${initials}</div>
          <div class="employee-info">
            <div class="employee-name">${escapeHtml(emp.name || "")}</div>
            <div class="employee-role">${escapeHtml(emp.role || "")}</div>
            <span class="employee-badge ${badgeClass}">${badgeText}</span>
          </div>
        </div>

        <div class="employee-meta">
          <div class="meta-item">
            <span class="meta-label">Email</span>
            <span class="meta-value">${escapeHtml(emp.email || "")}</span>
          </div>
          <div class="meta-item">
            <span class="meta-label">Join Date</span>
            <span class="meta-value">${escapeHtml(emp.joinDate || "")}</span>
          </div>
        </div>

        <div class="employee-actions">
          ${actionButtons(emp)}
        </div>
      </div>
    `;
  }

  function render(list = employeesView) {
    const grid = document.getElementById('employeesGrid');
    grid.innerHTML = list.length
      ? list.map(cardTemplate).join('')
      : `<div style="color:var(--text-secondary)">No employees found.</div>`;
  }

  // ========= Search (server) + local filters =========
  function debounce(fn, delay = 300){ let t; return (...args)=>{ clearTimeout(t); t = setTimeout(()=>fn(...args), delay); }; }

  async function doSearch(q) {
    const url = `/admin/employees/search?q=${encodeURIComponent(q || "")}`;
    const res = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
    if (!res.ok) throw new Error('Search failed');
    const data = await res.json();
    employeesAll = data.map(e => ({
      id: e.id ?? e.employee_id ?? 0,
      name: e.name ?? '',
      email: e.email ?? '',
      role: e.role ?? e.position ?? '',
      status: ((e.status ?? '') + '').toLowerCase() || 'pending',
      eventsManaged: e.eventsManaged ?? e.events_managed ?? 0,
      joinDate: e.joinDate ?? e.hire_date ?? ''
    }));
    applyFiltersAndRender();  // always re-apply current filters
  }

  const searchInput = document.getElementById('searchInput');
  if (searchInput){
    const onType = debounce(e=>{
      const q = e.target.value.trim();
      if (!q) { applyFiltersAndRender(); }
      else { doSearch(q).catch(console.error); }
    },300);
    searchInput.addEventListener('input', onType);
    searchInput.addEventListener('keydown', e => { if (e.key === 'Enter') e.preventDefault(); });
  }

  // ========= Filter state + application =========
  const sortNameSel   = document.getElementById('sortName');
  const statusSel     = document.getElementById('statusFilter');
  const yearInp       = document.getElementById('yearFilter');
  const monthSel      = document.getElementById('monthFilter');
  const daySel        = document.getElementById('dayFilter');
  const clearBtn      = document.getElementById('clearFilters');

  (function fillDays(){
    if(!daySel) return;
    for(let d=1; d<=31; d++){
      const op = document.createElement('option');
      op.value = String(d).padStart(2,'0');
      op.textContent = d;
      daySel.appendChild(op);
    }
  })();

  function applyFiltersAndRender(){
    let list = employeesAll.slice();

    const s = (statusSel?.value || 'all').toLowerCase();
    if (s !== 'all'){
      list = list.filter(e => (e.status || '').toLowerCase() === s);
    }

    const y = (yearInp?.value || '').trim();
    const m = (monthSel?.value || '').trim();  // '' or '01'..'12'
    const d = (daySel?.value || '').trim();    // '' or '01'..'31'
    if (y || m || d){
      list = list.filter(e => {
        const p = parts(e.joinDate);
        if(!p) return false;
        if (y && p.y !== y) return false;
        if (m && p.m !== m) return false;
        if (d && p.day !== d) return false;
        return true;
      });
    }

    const order = (sortNameSel?.value || 'az');
    list.sort(order === 'za' ? byNameZA : byNameAZ);

    employeesView = list;
    render();
  }

  [sortNameSel, statusSel, yearInp, monthSel, daySel].forEach(el=>{
    if(!el) return;
    const evt = el.tagName === 'INPUT' ? 'input' : 'change';
    el.addEventListener(evt, applyFiltersAndRender);
  });
  if (clearBtn){
    clearBtn.addEventListener('click', () => {
      sortNameSel.value = 'az';
      statusSel.value   = 'all';
      yearInp.value     = '';
      monthSel.value    = '';
      daySel.value      = '';
      applyFiltersAndRender();
    });
  }

  // ========= Actions (wired to backend) =========
  window.toggleActive = async function(id){
    const idx = employeesAll.findIndex(e => e.id === id);
    if (idx === -1) return;

    const current = (employeesAll[idx].status || '').toLowerCase();
    const nextUi = current === 'active' ? 'suspended' : 'active';
    const nextDb = nextUi === 'active' ? 'ACTIVE' : 'SUSPENDED';

    try {
      const res = await fetch(`/admin/employees/${id}/status`, {
        method: 'POST',
        credentials: 'same-origin',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
          'X-CSRF-TOKEN': csrf(),
        },
        body: JSON.stringify({ status: nextDb })
      });
      if (!res.ok) {
        const txt = await res.text();
        console.error('Status update failed', res.status, txt);
        alert(`Failed to update status (HTTP ${res.status}).`);
        return;
      }
      employeesAll[idx].status = nextUi;
      applyFiltersAndRender();
    } catch (err) {
      console.error(err);
      alert('Failed to update status (network).');
    }
  };

  window.deleteEmployee = async function(id){
    if (!confirm('Delete this employee? This action cannot be undone.')) return;
    try {
      const res = await fetch(`/admin/employees/${id}`, {
        method: 'DELETE',
        credentials: 'same-origin',
        headers: {
          'Accept': 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
          'X-CSRF-TOKEN': csrf(),
        }
      });
      if (!res.ok) {
        const txt = await res.text();
        console.error('Delete failed', res.status, txt);
        alert(`Failed to delete employee (HTTP ${res.status}).`);
        return;
      }
      employeesAll = employeesAll.filter(e => e.id !== id);
      applyFiltersAndRender();
    } catch (err) {
      console.error(err);
      alert('Failed to delete employee (network).');
    }
  };

  // ========= Initial render =========
  applyFiltersAndRender();
})();
