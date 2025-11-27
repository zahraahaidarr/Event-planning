(function () {
  let VOLUNTEERS = Array.isArray(window.initialVolunteers) ? window.initialVolunteers : [];

  function csrf() {
    const t = document.querySelector('meta[name="csrf-token"]');
    return t ? t.getAttribute('content') : '';
  }

  async function getJSON(url) {
    const res = await fetch(url, {
      method: 'GET',
      credentials: 'same-origin',
      headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
    });
    if (!res.ok) throw new Error(`HTTP ${res.status} ${res.statusText}`);
    return res.json();
  }

  async function postJSON(url, payload = {}) {
    const res = await fetch(url, {
      method: 'POST',
      credentials: 'same-origin',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-CSRF-TOKEN': csrf(),
        'X-Requested-With': 'XMLHttpRequest'
      },
      body: JSON.stringify(payload)
    });
    if (!res.ok) {
      let t = '';
      try { t = await res.text(); } catch (_) {}
      throw new Error(`HTTP ${res.status} ${res.statusText}\n${t.slice(0,300)}`);
    }
    return res.json();
  }

  async function reloadFromServer() {
    const data = await getJSON('/admin/volunteers/list');
    VOLUNTEERS = Array.isArray(data) ? data : [];
    renderVolunteers();
  }

  window.renderVolunteers = function renderVolunteers() {
    const tbody = document.getElementById('volunteersTable');

    const roleFilter     = document.getElementById('filterRole')?.value || '';
    const locationFilter = document.getElementById('filterLocation')?.value || '';
    const statusFilter   = document.getElementById('filterStatus')?.value || '';

    const filtered = VOLUNTEERS.filter(v => {
      if (roleFilter && v.role !== roleFilter) return false;
      if (locationFilter && v.location !== locationFilter) return false;
      if (statusFilter && v.status !== statusFilter) return false;
      return true;
    });

    tbody.innerHTML = filtered.map(vol => {
      const statusClass = `badge-${vol.status}`;
      const statusText  = (vol.status || 'pending').replace(/^\w/, c => c.toUpperCase());

      return `
        <tr>
          <td class="volunteer-name">${escapeHtml(vol.name ?? '—')}</td>
          <td>${escapeHtml(vol.role ?? '—')}</td>
          <td>${escapeHtml(vol.location ?? '—')}</td>
          <td>${Number(vol.events) || 0}</td>
          <td>${Number(vol.hours) || 0}h</td>
          <td><span class="status-badge ${statusClass}">${statusText}</span></td>
          <td>
            <div class="action-buttons">
              <button class="btn btn-success"  onclick="setActive(${vol.id}, event)"    ${vol.status==='active'?'disabled':''}>Activate</button>
              <button class="btn btn-warning"  onclick="setSuspended(${vol.id}, event)" ${vol.status==='suspended'?'disabled':''}>Suspend</button>
              <button class="btn btn-secondary" onclick="viewVolunteer(${vol.id})">View</button>
            </div>
          </td>
        </tr>
      `;
    }).join('');
  };

  function escapeHtml(s) {
    return String(s).replace(/[&<>"']/g, m => ({
      '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'
    }[m]));
  }

  // === Actions: set users.status via unified endpoint ===
  window.setActive = async function(id, ev){
    const btn = ev?.currentTarget; if (btn) { btn.disabled = true; btn.textContent = 'Activating...'; }
    try {
      await postJSON(`/admin/volunteers/${id}/status`, { status: 'ACTIVE' });
      await reloadFromServer();
      alert('Volunteer activated.');
    } catch (e) { console.error(e); alert('Failed to activate.'); }
    finally { if (btn) { btn.disabled = false; btn.textContent = 'Activate'; } }
  };

  window.setSuspended = async function(id, ev){
    if (!confirm('Suspend this volunteer?')) return;
    const btn = ev?.currentTarget; if (btn) { btn.disabled = true; btn.textContent = 'Suspending...'; }
    try {
      await postJSON(`/admin/volunteers/${id}/status`, { status: 'SUSPENDED' });
      await reloadFromServer();
      alert('Volunteer suspended.');
    } catch (e) { console.error(e); alert('Failed to suspend.'); }
    finally { if (btn) { btn.disabled = false; btn.textContent = 'Suspend'; } }
  };

  window.setBanned = async function(id, ev){
    if (!confirm('Ban this volunteer? This is a hard block.')) return;
    const btn = ev?.currentTarget; if (btn) { btn.disabled = true; btn.textContent = 'Banning...'; }
    try {
      await postJSON(`/admin/volunteers/${id}/status`, { status: 'BANNED' });
      await reloadFromServer();
      alert('Volunteer banned.');
    } catch (e) { console.error(e); alert('Failed to ban.'); }
    finally { if (btn) { btn.disabled = false; btn.textContent = 'Ban'; } }
  };

  window.viewVolunteer = function(id){ alert(`View volunteer ${id}`); };

  // UI toggles
  window.toggleTheme = function() {
    const html = document.documentElement;
    const newTheme = (html.getAttribute('data-theme') === 'dark') ? 'light' : 'dark';
    html.setAttribute('data-theme', newTheme);
    const icon = document.getElementById('theme-icon');
    if (icon) icon.textContent = newTheme === 'dark' ? '☀️' : '🌙';
  };

  window.toggleLanguage = function() {
    const html = document.documentElement;
    const newLang = (html.getAttribute('lang') || 'en') === 'en' ? 'ar' : 'en';
    html.setAttribute('lang', newLang);
    html.setAttribute('dir', newLang === 'ar' ? 'rtl' : 'ltr');
    const icon = document.getElementById('lang-icon');
    if (icon) icon.textContent = newLang === 'en' ? 'AR' : 'EN';
  };

  document.addEventListener('DOMContentLoaded', () => {
    renderVolunteers(); // first paint from injected data
    // reloadFromServer().catch(console.error); // optional: refresh from server
  });
})();
