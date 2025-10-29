(function () {
  // ---- Demo fallback if backend didn't pass employees ----
  const fallback = [
    { id: 1, name: "Ahmed Al-Rashid",   email: "ahmed.rashid@volunteerhub.com",   role: "Event Coordinator",      status: "active",   eventsManaged: 24, joinDate: "2024-06-15" },
    { id: 2, name: "Fatima Hassan",     email: "fatima.hassan@volunteerhub.com", role: "Volunteer Manager",      status: "active",   eventsManaged: 18, joinDate: "2024-07-20" },
    { id: 3, name: "Mohammed Ali",      email: "mohammed.ali@volunteerhub.com",  role: "Event Coordinator",      status: "active",   eventsManaged: 15, joinDate: "2024-08-10" },
    { id: 4, name: "Sarah Abdullah",    email: "sarah.abdullah@volunteerhub.com",role: "Communications Manager", status: "pending",  eventsManaged: 8,  joinDate: "2024-09-05" }
  ];

  // Use DB data if provided; normalize STATUS to lowercase string (active/suspended/pending)
  const initial = (Array.isArray(window.initialEmployees) && window.initialEmployees.length)
    ? window.initialEmployees.map((e, idx) => ({
        id: e.id ?? e.employee_id ?? (idx + 1),
        name: e.name ?? (e.user?.name || ""),
        email: e.email ?? (e.user?.email || ""),
        role: e.position ?? e.role ?? "",
        status: ((e.status ?? e.user?.status ?? '') + '').toLowerCase() || 'pending',
        eventsManaged: e.eventsManaged ?? e.events_managed ?? 0,
        joinDate: e.hire_date ?? e.joinDate ?? ""
      }))
    : fallback;

  // ========= Helpers =========
  function escapeHtml(s) {
    return String(s).replace(/[&<>"']/g, m => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[m]));
  }

  function cardTemplate(emp) {
    const initials = (emp.name || "").split(' ').map(n => n[0]).join('').slice(0, 3) || "VH";
    const statusClass = `badge-${emp.status}`; // expects badge-active, badge-suspended, badge-pending in CSS
    const statusText = emp.status ? (emp.status.charAt(0).toUpperCase() + emp.status.slice(1)) : "Pending";

    return `
      <div class="employee-card">
        <div class="employee-header">
          <div class="employee-avatar">${initials}</div>
          <div class="employee-info">
            <div class="employee-name">${escapeHtml(emp.name || "")}</div>
            <div class="employee-role">${escapeHtml(emp.role || "")}</div>
            <span class="employee-badge ${statusClass}">${statusText}</span>
          </div>
        </div>
        <div class="employee-meta">
          <div class="meta-item">
            <span class="meta-label">Email</span>
            <span class="meta-value">${escapeHtml(emp.email || "")}</span>
          </div>
          <div class="meta-item">
            <span class="meta-label">Events Managed</span>
            <span class="meta-value">${Number(emp.eventsManaged) || 0}</span>
          </div>
          <div class="meta-item">
            <span class="meta-label">Join Date</span>
            <span class="meta-value">${escapeHtml(emp.joinDate || "")}</span>
          </div>
          <div class="meta-item">
            <span class="meta-label">Status</span>
            <span class="meta-value">${statusText}</span>
          </div>
        </div>
        <div class="employee-actions">
          <button class="btn btn-secondary btn-sm" onclick="editEmployee(${emp.id})">Edit</button>
          <button class="btn btn-secondary btn-sm" onclick="resetPassword(${emp.id})">Reset Password</button>
          <button class="btn btn-danger btn-sm" onclick="deactivateEmployee(${emp.id})">Deactivate</button>
        </div>
      </div>
    `;
  }

  function renderFromArray(arr) {
    const grid = document.getElementById('employeesGrid');
    if (!grid) return;
    grid.innerHTML = arr.length
      ? arr.map(cardTemplate).join('')
      : `<div style="color:var(--text-secondary)">No employees found.</div>`;
  }

  function debounce(fn, delay = 300) {
    let t; return (...args) => { clearTimeout(t); t = setTimeout(() => fn(...args), delay); };
  }

  // Fetch search results and normalize STATUS to lowercase string
  async function doSearch(q) {
    const url = `/admin/employees/search?q=${encodeURIComponent(q || "")}`;
    const res = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
    if (!res.ok) throw new Error('Search failed');
    const data = await res.json();

    const normalized = data.map(e => ({
      id: e.id ?? e.employee_id ?? 0,
      name: e.name ?? '',
      email: e.email ?? '',
      role: e.role ?? e.position ?? '',
      status: ((e.status ?? '') + '').toLowerCase() || 'pending',
      eventsManaged: e.eventsManaged ?? e.events_managed ?? 0,
      joinDate: e.joinDate ?? e.hire_date ?? ''
    }));

    renderFromArray(normalized);
  }

  // ========= Modal controls (no AJAX; normal POST) =========
  window.openAddModal = function() {
    const backdrop = document.getElementById('addModal');
    if (!backdrop) return;

    // Always start clean to avoid browser autofill remnants
    const form = document.getElementById('addEmployeeForm');
    if (form) form.reset();

    // Explicit defaults
    const statusSel = document.getElementById('status');      // <select id="status">
    if (statusSel) statusSel.value = 'active';

    const dateInput = document.getElementById('hire_date');   // <input id="hire_date" type="date">
    if (dateInput) dateInput.valueAsDate = new Date();

    // Optional: hard-clear typical autofill targets
    const nameEl = document.getElementById('name');
    const emailEl = document.getElementById('email');
    const passEl  = document.getElementById('password');
    if (nameEl)  nameEl.value = '';
    if (emailEl) emailEl.value = '';
    if (passEl)  passEl.value  = '';

    backdrop.classList.add('show');
    document.body.classList.add('modal-open');

    setTimeout(() => nameEl?.focus(), 0);
  };

  window.closeAddModal = function() {
    const backdrop = document.getElementById('addModal');
    if (!backdrop) return;
    backdrop.classList.remove('show');
    document.body.classList.remove('modal-open');

    const form = document.getElementById('addEmployeeForm');
    if (form) form.reset();
  };

  // Close when clicking outside modal
  const modalBackdrop = document.getElementById('addModal');
  if (modalBackdrop) {
    modalBackdrop.addEventListener('click', window.closeAddModal);
  }

  // Esc to close
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
      const isOpen = document.getElementById('addModal')?.classList.contains('show');
      if (isOpen) window.closeAddModal();
    }
  });

  // ========= Live search (same cards, same modal, no page reload) =========
  const searchInput = document.getElementById('searchInput');
  if (searchInput) {
    const onType = debounce((e) => {
      const q = e.target.value.trim();
      if (!q) {
        renderFromArray(initial);
      } else {
        doSearch(q).catch(console.error);
      }
    }, 300);

    searchInput.addEventListener('input', onType);
    searchInput.addEventListener('keydown', (e) => { if (e.key === 'Enter') e.preventDefault(); });
  }

  // ========= Theme & Language toggles =========
  window.toggleTheme = function() {
    const html = document.documentElement;
    const currentTheme = html.getAttribute('data-theme');
    const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
    html.setAttribute('data-theme', newTheme);
    const icon = document.getElementById('theme-icon');
    if (icon) icon.textContent = newTheme === 'dark' ? '☀️' : '🌙';
  };

  window.toggleLanguage = function() {
    const html = document.documentElement;
    const currentLang = html.getAttribute('lang') || 'en';
    const newLang = currentLang === 'en' ? 'ar' : 'en';
    const newDir = newLang === 'ar' ? 'rtl' : 'ltr';
    html.setAttribute('lang', newLang);
    html.setAttribute('dir', newDir);
    const icon = document.getElementById('lang-icon');
    if (icon) icon.textContent = newLang === 'en' ? 'AR' : 'EN';
  };

  // ========= Placeholder actions =========
  window.editEmployee = function(id){ alert(`Edit employee ${id}`); };
  window.resetPassword = function(id){ if (confirm('Reset password for this employee?')) alert('Password reset email sent'); };
  window.deactivateEmployee = function(id){ if (confirm('Deactivate this employee account?')) alert('Employee deactivated'); };

  // ========= Initial render =========
  renderFromArray(initial);
})();
