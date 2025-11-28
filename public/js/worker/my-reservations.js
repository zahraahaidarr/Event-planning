// public/js/worker/my-reservations.js

// ---- Config ----
let currentFilter = 'all'; // Default tab
const discoverUrl = document.body.getAttribute('data-discover-url') || '#';

// Laravel routes:
// DELETE  /worker/reservation/{reservation}
// PATCH   /worker/reservation/{reservation}/complete
const cancelBaseUrl   = '/worker/reservation';
const completeBaseUrl = '/worker/reservation';

// ---- Data from backend (see Blade script) ----
let reservations = Array.isArray(window.initialReservations)
  ? window.initialReservations
  : [];

// Helpers
function normalizeStatus(status) {
  return (status || '').toString().toLowerCase();
}

function renderReservations() {
  const list = document.getElementById('reservationsList');
  if (!list) return;

  const filterMap = {
    // "All" => reserved + completed + rejected (hide pending)
    all: (r) => {
      const st = normalizeStatus(r.status);
      return st === 'pending' || st === 'reserved' || st === 'completed' || st === 'rejected' ||st === 'cancelled';
    },
    pending:   (r) => normalizeStatus(r.status) === 'pending',
    reserved: (r) => normalizeStatus(r.status) === 'reserved',
    completed: (r) => normalizeStatus(r.status) === 'completed',
    rejected: (r) => normalizeStatus(r.status) === 'rejected',
    cancelled: (r) => normalizeStatus(r.status) === 'cancelled',

  };

  const filterFn = filterMap[currentFilter] || filterMap.all;
  const filtered = reservations.filter(filterFn);

  if (filtered.length === 0) {
    const label =
      currentFilter === 'all' ? 'reservations' : currentFilter.toLowerCase();

    list.innerHTML = `
      <div class="empty-state">
        <div class="empty-icon">📭</div>
        <h3 class="empty-title">No ${label} found</h3>
        <p class="empty-description">You don't have any ${label} yet.</p>
        <a class="btn btn-primary" href="${discoverUrl}">Discover Events</a>
      </div>
    `;
    return;
  }

  list.innerHTML = filtered
    .map((r) => {
      const statusKey = normalizeStatus(r.status);

      const statusLabelMap = {
        reserved: 'Reserved',
        completed: 'Completed',
        rejected: 'Rejected',
        pending: 'Pending',
        cancelled: 'Cancelled',
      };

      const statusText =
        statusLabelMap[statusKey] ||
        (statusKey.charAt(0).toUpperCase() + statusKey.slice(1));

      // Use green "accepted" style for reserved
      const cssStatusKey = statusKey === 'reserved' ? 'accepted' : statusKey;
      const statusClass = `status-${cssStatusKey}`;

      let actions = '';
      if (statusKey === 'reserved') {
        actions = `
          <button class="btn btn-danger"  data-act="cancel" data-id="${r.id}">Cancel</button>
        `;
      } else if (statusKey === 'completed') {
        actions = `
          <a class="btn btn-primary" href="${window.submissionsUrl}">Submit Report</a>
        `;
      } else if (statusKey === 'rejected') {
        actions = `
        `;
      } else if (statusKey === 'pending') {
  actions = `
    <button class="btn btn-danger"  data-act="cancel" data-id="${r.id}">Cancel</button>
  `;
}


      const dateText = r.date || 'N/A';
      const timeText = r.time || 'N/A';
      const locationText = r.location || '—';
      const durationText = r.duration || 'N/A';
      const appliedText = r.appliedDate || '—';

      return `
        <div class="reservation-card">
          <div class="reservation-header">
            <div>
              <h3 class="reservation-title">${r.eventTitle || 'Untitled Event'}</h3>
              <p class="reservation-role">Role: ${r.role || 'Volunteer'}</p>
            </div>
            <span class="status-badge ${statusClass}">${statusText}</span>
          </div>
          <div class="reservation-meta">
            <div class="meta-item">
              <span class="meta-icon">📅</span>
              <span>${dateText} ${timeText ? 'at ' + timeText : ''}</span>
            </div>
            <div class="meta-item">
              <span class="meta-icon">📍</span>
              <span>${locationText}</span>
            </div>
            <div class="meta-item">
              <span class="meta-icon">⏱️</span>
              <span>${durationText}</span>
            </div>
            <div class="meta-item">
              <span class="meta-icon">📝</span>
              <span>Applied: ${appliedText}</span>
            </div>
          </div>
          ${
            actions
              ? `<div class="reservation-actions">${actions}</div>`
              : ''
          }
        </div>
      `;
    })
    .join('');
}

function switchTab(filter, btn) {
  currentFilter = filter;
  document.querySelectorAll('.tab').forEach((t) => t.classList.remove('active'));
  if (btn) btn.classList.add('active');
  renderReservations();
}

// ---- AJAX Cancel ----
async function cancelReservation(id) {
  const csrf = document
    .querySelector('meta[name="csrf-token"]')
    .getAttribute('content');

  try {
    const response = await fetch(`${cancelBaseUrl}/${id}`, {
      method: 'DELETE',
      headers: {
        'X-CSRF-TOKEN': csrf,
        'X-Requested-With': 'XMLHttpRequest',
        Accept: 'application/json',
      },
    });

    if (!response.ok) {
      throw new Error(`HTTP ${response.status}`);
    }

    const data = await response.json();

    if (data.ok) {
      // 🔁 update status locally instead of removing
      reservations = reservations.map((r) =>
        Number(r.id) === Number(id)
          ? { ...r, status: data.uiStatus || 'cancelled' }
          : r
      );

      renderReservations();
      alert(data.message || 'Reservation cancelled successfully');
    } else {
      alert(data.message || 'Failed to cancel reservation.');
    }
  } catch (err) {
    console.error('Cancel error:', err);
    alert('Something went wrong while cancelling. Please try again.');
  }
}


// ---- AJAX Mark Completed ----
async function completeReservation(id) {
  const csrf = document
    .querySelector('meta[name="csrf-token"]')
    .getAttribute('content');

  try {
    const response = await fetch(`${completeBaseUrl}/${id}/complete`, {
      method: 'PATCH',
      headers: {
        'X-CSRF-TOKEN': csrf,
        'X-Requested-With': 'XMLHttpRequest',
        Accept: 'application/json',
      },
    });

    if (!response.ok) {
      throw new Error(`HTTP ${response.status}`);
    }

    const data = await response.json();

    if (data.ok) {
      // update local status & re-render
      reservations = reservations.map((r) =>
        Number(r.id) === Number(id) ? { ...r, status: 'completed' } : r
      );
      renderReservations();
      alert(data.message || 'Reservation marked as completed');
    } else {
      alert(data.message || 'Failed to mark as completed.');
    }
  } catch (err) {
    console.error('Complete error:', err);
    alert('Something went wrong while marking as completed. Please try again.');
  }
}

// ---- Actions ----
function handleActionClick(e) {
  const t = e.target;
  const act = t.getAttribute('data-act');
  const id = Number(t.getAttribute('data-id'));
  if (!act || !id) return;

  const r = reservations.find((x) => Number(x.id) === id);
  if (!r) return;

  if (act === 'view') {
    alert('Viewing event details...');
  } else if (act === 'cancel') {
    if (confirm('Are you sure you want to cancel this reservation?')) {
      cancelReservation(id);
    }
  } else if (act === 'complete') {
    if (confirm('Mark this reservation as completed?')) {
      completeReservation(id);
    }
  } else if (act === 'certificate') {
    alert('Opening certificate...');
  } else if (act === 'reason') {
    alert(`Rejection Reason: ${r.rejectionReason || 'No reason provided'}`);
  }
}

// ---- Theme & language ----
function toggleTheme() {
  const html = document.documentElement;
  const current = html.getAttribute('data-theme') || 'dark';
  const next = current === 'dark' ? 'light' : 'dark';
  html.setAttribute('data-theme', next);

  const icon = document.getElementById('theme-icon');
  if (icon) icon.textContent = next === 'dark' ? '☀️' : '🌙';
}

function toggleLanguage() {
  const html = document.documentElement;
  const curr = html.getAttribute('lang') || 'en';
  const next = curr === 'en' ? 'ar' : 'en';
  html.setAttribute('lang', next);
  html.setAttribute('dir', next === 'ar' ? 'rtl' : 'ltr');

  const icon = document.getElementById('lang-icon');
  if (icon) icon.textContent = next === 'en' ? 'AR' : 'EN';
}

// ---- Init ----
document.addEventListener('DOMContentLoaded', () => {
  renderReservations();

  document.querySelectorAll('.tab').forEach((btn) => {
    btn.addEventListener('click', () => switchTab(btn.dataset.tab, btn));
  });

  const list = document.getElementById('reservationsList');
  if (list) list.addEventListener('click', handleActionClick);
});

// Expose so Blade can use onclick
window.toggleTheme = toggleTheme;
window.toggleLanguage = toggleLanguage;
