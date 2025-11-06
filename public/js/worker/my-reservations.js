// ---- Mock reservations data (pending exists but stays hidden in view) ----
const reservations = [
  { id: 1, eventTitle: "Community Garden Cleanup", role: "Gardener",        status: "accepted",  date: "2025-01-15", time: "09:00 AM", location: "Riyadh", appliedDate: "2025-01-05", duration: "4 hours" },
  { id: 2, eventTitle: "Children's Reading Program", role: "Reader",        status: "pending",   date: "2025-01-18", time: "02:00 PM", location: "Jeddah", appliedDate: "2025-01-08", duration: "3 hours" },
  { id: 3, eventTitle: "Beach Cleanup Initiative",   role: "Cleanup Crew",  status: "accepted",  date: "2025-01-25", time: "07:00 AM", location: "Jeddah", appliedDate: "2025-01-10", duration: "4 hours" },
  { id: 4, eventTitle: "Senior Center Activities",   role: "Companion",     status: "completed", date: "2025-01-10", time: "10:00 AM", location: "Dammam", appliedDate: "2024-12-28", duration: "3 hours" },
  { id: 5, eventTitle: "Food Bank Distribution",     role: "Sorter",        status: "rejected",  date: "2025-01-20", time: "08:00 AM", location: "Riyadh", appliedDate: "2025-01-07", duration: "5 hours", rejectionReason: "Event reached maximum capacity" },
  { id: 6, eventTitle: "Health Awareness Campaign",  role: "Information Desk", status: "pending", date: "2025-01-28", time: "09:00 AM", location: "Riyadh", appliedDate: "2025-01-09", duration: "6 hours" },
  { id: 7, eventTitle: "Youth Mentorship Program",   role: "Mentor",        status: "accepted",  date: "2025-02-01", time: "03:00 PM", location: "Mecca", appliedDate: "2025-01-11", duration: "2 hours" },
  { id: 8, eventTitle: "Animal Shelter Support",     role: "Animal Care",   status: "completed", date: "2025-01-08", time: "10:00 AM", location: "Medina", appliedDate: "2024-12-30", duration: "4 hours" }
];

// Default: show All (but exclude "pending")
let currentFilter = 'all';

// Helpers
const discoverUrl = document.body.getAttribute('data-discover-url') || '#';

function renderReservations() {
  const list = document.getElementById('reservationsList');
  if (!list) return;

  const filterMap = {
    all:       r => (r.status === 'accepted' || r.status === 'completed' || r.status === 'rejected'),
    reserved:  r => r.status === 'accepted',
    completed: r => r.status === 'completed',
    rejected:  r => r.status === 'rejected'
  };

  const filtered = reservations.filter(filterMap[currentFilter]);

  if (filtered.length === 0) {
    const label = currentFilter === 'all' ? 'reservations' : currentFilter;
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

  list.innerHTML = filtered.map(r => {
    const statusText = r.status === 'accepted'
      ? 'Reserved'
      : r.status.charAt(0).toUpperCase() + r.status.slice(1);

    const statusClass = `status-${r.status}`;

    let actions = '';
    if (r.status === 'accepted') {
      actions = `
        <button class="btn btn-primary" data-act="view" data-id="${r.id}">View Details</button>
        <button class="btn btn-danger"  data-act="cancel" data-id="${r.id}">Cancel</button>
      `;
    } else if (r.status === 'completed') {
      actions = `
        <a class="btn btn-primary" href="#">Submit Report</a>
        <button class="btn btn-secondary" data-act="certificate" data-id="${r.id}">View Certificate</button>
      `;
    } else if (r.status === 'rejected') {
      actions = `<button class="btn btn-secondary" data-act="reason" data-id="${r.id}">View Reason</button>`;
    }

    return `
      <div class="reservation-card">
        <div class="reservation-header">
          <div>
            <h3 class="reservation-title">${r.eventTitle}</h3>
            <p class="reservation-role">Role: ${r.role}</p>
          </div>
          <span class="status-badge ${statusClass}">${statusText}</span>
        </div>
        <div class="reservation-meta">
          <div class="meta-item"><span class="meta-icon">📅</span><span>${r.date} at ${r.time}</span></div>
          <div class="meta-item"><span class="meta-icon">📍</span><span>${r.location}</span></div>
          <div class="meta-item"><span class="meta-icon">⏱️</span><span>${r.duration}</span></div>
          <div class="meta-item"><span class="meta-icon">📝</span><span>Applied: ${r.appliedDate}</span></div>
        </div>
        ${actions ? `<div class="reservation-actions">${actions}</div>` : ''}
      </div>
    `;
  }).join('');
}

function switchTab(filter, btn){
  currentFilter = filter;
  document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
  if (btn) btn.classList.add('active');
  renderReservations();
}

// Actions (basic demo handlers)
function handleActionClick(e){
  const t = e.target;
  const act = t.getAttribute('data-act');
  const id  = Number(t.getAttribute('data-id'));
  if (!act || !id) return;

  const r = reservations.find(x => x.id === id);
  if (!r) return;

  if (act === 'view') {
    alert('Viewing event details...');
  } else if (act === 'cancel') {
    if (confirm('Are you sure you want to cancel this reservation?')) {
      alert('Reservation cancelled successfully');
    }
  } else if (act === 'certificate') {
    alert('Opening certificate...');
  } else if (act === 'reason') {
    alert(`Rejection Reason: ${r.rejectionReason || 'No reason provided'}`);
  }
}

// Theme & language
function toggleTheme(){
  const html = document.documentElement;
  const current = html.getAttribute('data-theme');
  const next = current === 'dark' ? 'light' : 'dark';
  html.setAttribute('data-theme', next);
  const icon = document.getElementById('theme-icon');
  if (icon) icon.textContent = next === 'dark' ? '☀️' : '🌙';
}
function toggleLanguage(){
  const html = document.documentElement;
  const curr = html.getAttribute('lang') || 'en';
  const next = curr === 'en' ? 'ar' : 'en';
  html.setAttribute('lang', next);
  html.setAttribute('dir', next === 'ar' ? 'rtl' : 'ltr');
  const icon = document.getElementById('lang-icon');
  if (icon) icon.textContent = next === 'en' ? 'AR' : 'EN';
}

// Init
document.addEventListener('DOMContentLoaded', () => {
  renderReservations();

  // Tabs
  document.querySelectorAll('.tab').forEach(btn => {
    btn.addEventListener('click', () => switchTab(btn.dataset.tab, btn));
  });

  // Delegate action buttons
  const list = document.getElementById('reservationsList');
  if (list) list.addEventListener('click', handleActionClick);
});

// Expose for inline buttons in Blade header
window.toggleTheme = toggleTheme;
window.toggleLanguage = toggleLanguage;
