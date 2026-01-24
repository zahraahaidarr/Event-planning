// public/js/employee/volunteer-assignment.js

const $  = (s, r = document) => r.querySelector(s);
const $$ = (s, r = document) => Array.from(r.querySelectorAll(s));

let currentEventId = null;
let currentFilter  = 'all';
let currentApps    = [];
let currentStats   = { total: 0, pending: 0, rejected: 0 };

// event_id => starts_at (ms)
let eventStartMap = new Map();

document.addEventListener('DOMContentLoaded', () => {
  const eventButtons        = $$('.event-card');
  const statsGrid           = $('#statsGrid');
  const applicationsSection = $('#applicationsSection');
  const filterButtons       = $$('.filter-btn');

  const btnTheme            = $('#btnTheme');
  const btnLang             = $('#btnLang');
  const btnCloseModal       = $('#btnCloseModal');
  const btnCloseModalFooter = $('#btnCloseModalFooter');

  // Build eventStartMap from the event cards
  // Requires: data-starts-at on each event card button
  eventStartMap = new Map();
  eventButtons.forEach(btn => {
    const id = btn.dataset.eventId;
    const startsAt = btn.dataset.startsAt;
    if (!id || !startsAt) return;

    const t = new Date(startsAt).getTime();
    if (!Number.isNaN(t)) eventStartMap.set(String(id), t);
  });

  function resetFilterToAll() {
    currentFilter = 'all';
    filterButtons.forEach(b => b.classList.remove('active'));
    const allBtn = filterButtons.find(b => (b.dataset.filter || 'all') === 'all');
    if (allBtn) allBtn.classList.add('active');
  }

  // ----------------- event cards -----------------
  if (eventButtons.length) {
    const activeBtn = eventButtons.find(b => b.classList.contains('active')) || eventButtons[0];
    const activeId  = activeBtn?.dataset.eventId;

    if (activeId) {
      currentEventId = activeId;

      eventButtons.forEach(b => b.classList.remove('active'));
      activeBtn.classList.add('active');

      resetFilterToAll();
      loadApplicationsForEvent(currentEventId, statsGrid, applicationsSection);
    }

    eventButtons.forEach(btn => {
      btn.addEventListener('click', async () => {
        const eventId = btn.dataset.eventId;
        if (!eventId || eventId === currentEventId) return;

        currentEventId = eventId;
        resetFilterToAll();

        eventButtons.forEach(b => b.classList.remove('active'));
        btn.classList.add('active');

        await loadApplicationsForEvent(eventId, statsGrid, applicationsSection);
      });
    });
  }

  // ----------------- filters -----------------
  filterButtons.forEach(btn => {
    btn.addEventListener('click', (e) => {
      currentFilter = btn.dataset.filter || 'all';

      filterButtons.forEach(b => b.classList.remove('active'));
      e.currentTarget.classList.add('active');

      renderApplications();
    });
  });

  // ----------------- theme / lang -----------------
  if (btnTheme) btnTheme.addEventListener('click', toggleTheme);
  if (btnLang)  btnLang.addEventListener('click', toggleLanguage);

  // ----------------- modal close -----------------
  if (btnCloseModal)       btnCloseModal.addEventListener('click', closeModal);
  if (btnCloseModalFooter) btnCloseModalFooter.addEventListener('click', closeModal);
});

async function loadApplicationsForEvent(eventId, statsGrid, applicationsSection) {
  const base = window.ENDPOINT_APPS_BASE;
  if (!base) {
    console.error('ENDPOINT_APPS_BASE is not defined on window.');
    return;
  }

  const url = `${base}/${encodeURIComponent(eventId)}/applications`;

  try {
    const res = await fetch(url, {
      headers: {
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest'
      },
      credentials: 'same-origin'
    });

    if (!res.ok) {
      console.error('Failed to load applications', res.status);
      currentApps  = [];
      currentStats = { total: 0, pending: 0, rejected: 0 };
      updateStats();
      renderApplications();
      if (statsGrid)           statsGrid.style.display = 'grid';
      if (applicationsSection) applicationsSection.style.display = 'block';
      return;
    }

    const data = await res.json();
    currentApps  = data.applications || [];
    currentStats = data.stats || {
      total:    currentApps.length,
      pending:  currentApps.filter(a => a.status === 'pending').length,
      rejected: currentApps.filter(a => a.status === 'rejected').length,
    };

    if (statsGrid)           statsGrid.style.display = 'grid';
    if (applicationsSection) applicationsSection.style.display = 'block';

    updateStats();
    renderApplications();
  } catch (err) {
    console.error('Error fetching applications:', err);
  }
}

// =====================================================
// FILTER LOGIC
// accepted = RESERVED + COMPLETED (because completed was accepted earlier)
// =====================================================
function matchesFilter(app, filter) {
  switch (filter) {
    case 'all':
      return true;

    case 'pending':
      return app.status === 'pending';

    case 'accepted':
      // accepted lifecycle = RESERVED + COMPLETED
      return app.status === 'accepted' || app.status === 'completed';

    case 'rejected':
      return app.status === 'rejected';

    default:
      return false;
  }
}


// =====================================================
// TIME LOCK LOGIC
// - If app.eventStartsAt exists => use it
// - Else use eventStartMap via app.eventId
// - If still unknown => ALLOW (but your Blade data-starts-at removes this problem)
// =====================================================
function getEventStartMs(app) {
  if (app.eventStartsAt) {
    const t = new Date(app.eventStartsAt).getTime();
    if (!Number.isNaN(t)) return t;
  }

  // requires backend to send eventId in "all events" mode (you already do)
  if (app.eventId && eventStartMap.has(String(app.eventId))) {
    return eventStartMap.get(String(app.eventId));
  }

  // if you're on a specific event page, fallback to currentEventId map
  if (currentEventId && eventStartMap.has(String(currentEventId))) {
    return eventStartMap.get(String(currentEventId));
  }

  return null;
}

function canModifyBeforeStart(app) {
  // completed should never allow accept/reject toggles
  if (app.status === 'completed') return false;

  const startMs = getEventStartMs(app);
  if (startMs === null) return true; // fallback

  return Date.now() < startMs;
}

function updateStats() {
  const totalEl    = $('#statTotal');
  const pendingEl  = $('#statPending');
  const rejectedEl = $('#statRejected');

  if (!totalEl || !pendingEl || !rejectedEl) return;

  const total    = currentStats.total ?? currentApps.length;
  const pending  = currentStats.pending ?? currentApps.filter(a => a.status === 'pending').length;
  const rejected = currentStats.rejected ?? currentApps.filter(a => a.status === 'rejected').length;

  totalEl.textContent    = total;
  pendingEl.textContent  = pending;
  rejectedEl.textContent = rejected;
}

function renderApplications() {
  const list = $('#applicationsList');
  if (!list) return;

  const filtered = currentApps.filter(app => matchesFilter(app, currentFilter));

  if (filtered.length === 0) {
    list.innerHTML = `
      <div class="empty-state">
        <div class="empty-icon">📭</div>
        <h3 class="empty-title">No applications found</h3>
        <p class="empty-description">
          There are no ${currentFilter === 'all' ? '' : currentFilter} applications for this selection.
        </p>
      </div>
    `;
    return;
  }

  list.innerHTML = filtered.map(app => {
    const initials = (app.name || '??')
      .split(' ')
      .filter(Boolean)
      .map(n => n[0])
      .join('');

    const hours      = Number(app.creditedHours ?? 0);
    const hourlyRate = Number(app.hourlyRate ?? 0);
    const workerType = app.workerType || app.engagementKind || '-';

    let actions = '';

    if (app.status === 'pending') {
      actions = `
        <button class="btn btn-success" onclick="acceptApplication(${app.id})">Accept</button>
        <button class="btn btn-danger"  onclick="rejectApplication(${app.id})">Reject</button>
        <button class="btn btn-secondary" onclick="viewProfile(${app.volunteerId})">View Profile</button>
      `;
    }
    else if (app.status === 'rejected') {
      // ✅ IMPORTANT: do NOT allow accept if event already started/completed
      const acceptBtn = canModifyBeforeStart(app)
        ? `<button class="btn btn-success" onclick="acceptApplication(${app.id})">Accept</button>`
        : '';

      actions = `
        ${acceptBtn}
        <button class="btn btn-secondary" onclick="viewProfile(${app.volunteerId})">View Profile</button>
      `;
    }
    else if (app.status === 'accepted') {
      // Reject accepted only if event not started
      const rejectBtn = canModifyBeforeStart(app)
        ? `<button class="btn btn-danger" onclick="rejectApplication(${app.id})">Reject</button>`
        : '';

      actions = `
        ${rejectBtn}
        <button class="btn btn-secondary" onclick="viewProfile(${app.volunteerId})">View Profile</button>
      `;
    }
    else if (app.status === 'completed') {
      actions = `
        <button class="btn btn-secondary" onclick="viewProfile(${app.volunteerId})">View Profile</button>
      `;
    }
    else {
      actions = `<button class="btn btn-secondary" onclick="viewProfile(${app.volunteerId})">View Profile</button>`;
    }

    const eventLine = app.eventTitle
      ? `<div class="volunteer-meta">Event: ${app.eventTitle}</div>`
      : '';

    return `
      <div class="application-card">
        <div class="application-header">
          <div class="volunteer-info">
            <div class="volunteer-avatar">${initials}</div>
            <div class="volunteer-details">
              <h3>${app.name ?? 'Unknown'}</h3>
              <div class="volunteer-meta">
                Applied for: ${app.role ?? 'N/A'} • ${app.appliedDate ?? ''}
              </div>
              ${eventLine}
            </div>
          </div>
        </div>

        <div class="application-body">
          <div class="info-item">
            <span class="info-label">Email</span>
            <span class="info-value">${app.email ?? '-'}</span>
          </div>
          <div class="info-item">
            <span class="info-label">Phone</span>
            <span class="info-value">${app.phone ?? '-'}</span>
          </div>

          

          <div class="info-item">
            <span class="info-label">Previous Events</span>
            <span class="info-value">${app.previousEvents ?? 0} events</span>
          </div>

          <div class="info-item">
            <span class="info-label">Type</span>
            <span class="info-value">${workerType}</span>
          </div>

          <div class="info-item">
            <span class="info-label">Hourly rate</span>
            <span class="info-value">
              ${hourlyRate > 0 ? hourlyRate.toFixed(2) + ' $/h' : '-'}
            </span>
          </div>
        </div>

        <div class="application-actions">
          ${actions}
        </div>
      </div>
    `;
  }).join('');
}

async function updateReservationStatus(id, newStatus) {
  if (!window.ENDPOINT_STATUS_BASE) {
    console.error('ENDPOINT_STATUS_BASE is not defined');
    return false;
  }

  try {
    const res = await fetch(
      `${window.ENDPOINT_STATUS_BASE}/${encodeURIComponent(id)}/status`,
      {
        method: 'PATCH',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
          'X-CSRF-TOKEN': window.csrfToken || ''
        },
        body: JSON.stringify({ status: newStatus })
      }
    );

    if (!res.ok) {
      console.error('Failed to update reservation', res.status);
      return false;
    }

    const data = await res.json();
    if (!data.ok) return false;

    const app = currentApps.find(a => a.id === id);
    if (app) app.status = data.uiStatus || app.status;

    currentStats.total    = currentApps.length;
    currentStats.pending  = currentApps.filter(a => a.status === 'pending').length;
    currentStats.rejected = currentApps.filter(a => a.status === 'rejected').length;

    updateStats();
    renderApplications();
    return true;
  } catch (e) {
    console.error('Error updating status', e);
    return false;
  }
}

async function rejectApplication(id) {
  await updateReservationStatus(id, 'REJECTED');
}

async function acceptApplication(id) {
  await updateReservationStatus(id, 'RESERVED');
}

// ---------- Profile modal ----------
function viewProfile(volunteerId) {
  const app = currentApps.find(a => a.volunteerId === volunteerId);
  if (!app) return;

  const hours      = Number(app.creditedHours ?? 0);
  const hourlyRate = Number(app.hourlyRate ?? 0);
  const workerType = app.workerType || app.engagementKind || '-';

  $('#modalVolunteerName').textContent = app.name ?? 'Volunteer Profile';
  $('#modalBody').innerHTML = `
    <div class="profile-section">
      <h3>Contact Information</h3>
      <div class="profile-grid">
        <div class="profile-row">
          <span class="info-label">Email:</span>
          <span class="info-value">${app.email ?? '-'}</span>
        </div>
        <div class="profile-row">
          <span class="info-label">Phone:</span>
          <span class="info-value">${app.phone ?? '-'}</span>
        </div>
      </div>
    </div>
  `;
  $('#profileModal').classList.add('active');
}

function closeModal() {
  $('#profileModal').classList.remove('active');
}

function toggleTheme() {
  const html    = document.documentElement;
  const current = html.getAttribute('data-theme') || 'dark';
  const next    = current === 'dark' ? 'light' : 'dark';
  html.setAttribute('data-theme', next);
  const icon = $('#theme-icon');
  if (icon) icon.textContent = next === 'dark' ? '☀️' : '🌙';
}

function toggleLanguage() {
  const html    = document.documentElement;
  const current = html.getAttribute('lang') || 'en';
  const next    = current === 'en' ? 'ar' : 'en';
  const dir     = next === 'ar' ? 'rtl' : 'ltr';
  html.setAttribute('lang', next);
  html.setAttribute('dir', dir);
  const icon = $('#lang-icon');
  if (icon) icon.textContent = next === 'en' ? 'AR' : 'EN';
}
