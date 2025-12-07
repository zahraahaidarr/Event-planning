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
      const roleName = v.role ?? v.role_name ?? '';
      if (roleFilter && roleName !== roleFilter) return false;
      if (locationFilter && v.location !== locationFilter) return false;
      if (statusFilter && v.status !== statusFilter) return false;
      return true;
    });

    tbody.innerHTML = filtered.map(vol => {
      const statusClass = `badge-${vol.status}`;
      const statusText  = (vol.status || 'pending').replace(/^\w/, c => c.toUpperCase());
      const roleName    = vol.role ?? vol.role_name ?? '—';

      return `
        <tr>
          <td class="volunteer-name">${escapeHtml(vol.name ?? '—')}</td>
          <td>${escapeHtml(roleName)}</td>
          <td>${escapeHtml(vol.location ?? '—')}</td>
          <td>${Number(vol.events) || 0}</td>
          <td>${Number(vol.hours) || 0}h</td>
          <td><span class="status-badge ${statusClass}">${statusText}</span></td>
          <td>
            <div class="action-buttons">
              <button class="btn btn-success"  onclick="setActive(${vol.id}, event)"    ${vol.status==='ACTIVE' || vol.status==='active' ? 'disabled' : ''}>Activate</button>
              <button class="btn btn-warning"  onclick="setSuspended(${vol.id}, event)" ${vol.status==='SUSPENDED' || vol.status==='suspended' ? 'disabled' : ''}>Suspend</button>
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

  function setText(id, value) {
    const el = document.getElementById(id);
    if (el) el.textContent = value ?? '';
  }

  function setInput(id, value) {
    const el = document.getElementById(id);
    if (el) el.value = value ?? '';
  }

  window.viewVolunteer = function(id) {
    const vol = VOLUNTEERS.find(v => Number(v.id) === Number(id));
    if (!vol) {
      alert('Volunteer not found.');
      return;
    }

    // Top header
    setText('vm-name', vol.name || 'Volunteer details');
    setText('vm-email', vol.email || '');

    // Form fields
    setInput('vm-first_name', vol.first_name || '');
    setInput('vm-last_name',  vol.last_name || '');
    setInput('vm-phone',      vol.phone || '');

    const roleName = vol.role ?? vol.role_name ?? '';
    setInput('vm-role',       roleName);

    setInput('vm-location',   vol.location || '');
    setInput('vm-engagement_kind', vol.engagement_kind || '');

    // normalize is_volunteer (bool / tinyint / string)
    const isVol = String(vol.is_volunteer) === '1' || vol.is_volunteer === true;
    setInput('vm-is_volunteer', isVol ? 'Volunteer' : 'Worker');

    // Hourly rate field instead of verification status
    let hourlyText;
    if (isVol) {
      hourlyText = '0 $';
    } else if (vol.hourly_rate !== null && vol.hourly_rate !== undefined) {
      hourlyText = vol.hourly_rate + ' $';
    } else {
      hourlyText = '—';
    }
    setInput('vm-hourly_rate', hourlyText);

    setInput('vm-status',     vol.status || '');
    setInput('vm-events',     vol.events ?? 0);
    setInput('vm-hours',      (vol.hours ?? 0) + ' h');
    setInput('vm-joined_at',  vol.joined_at || '');

    // Certificate link
    const certLink = document.getElementById('vm-certificate_link');
    if (certLink) {
      if (vol.certificate_url) {
        certLink.href = vol.certificate_url;
        certLink.textContent = 'View certificate';
      } else {
        certLink.href = '#';
        certLink.textContent = 'No certificate uploaded';
      }
    }

    const modal = document.getElementById('volunteerModal');
    if (modal) modal.classList.remove('hidden');
  };

  window.closeVolunteerModal = function() {
    const modal = document.getElementById('volunteerModal');
    if (modal) modal.classList.add('hidden');
  };

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
