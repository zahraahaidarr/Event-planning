// public/js/Admin/volunteers.js

// Demo data (includes 'approved' flag)
const volunteers = [
  { id: 1, name: "Sarah Ahmed",   role: "Gardener",      location: "Riyadh", events: 12, hours: 48, status: "active",    approved: false },
  { id: 2, name: "Mohammed Ali", role: "Organizer",     location: "Jeddah", events: 24, hours: 96, status: "active",    approved: true  },
  { id: 3, name: "Fatima Hassan",role: "Media Staff",   location: "Riyadh", events:  8, hours: 32, status: "active",    approved: false },
  { id: 4, name: "Ahmed Ibrahim",role: "Civil Defense", location: "Dammam", events:  3, hours: 12, status: "suspended", approved: true  }
];

function renderVolunteers() {
  const tbody = document.getElementById('volunteersTable');

  const roleFilter = document.getElementById('filterRole').value;
  const approvalFilter = document.getElementById('filterApproval').value;
  const locationFilter = document.getElementById('filterLocation').value;
  const statusFilter = document.getElementById('filterStatus').value;

  const filtered = volunteers.filter(v => {
    if (roleFilter && v.role !== roleFilter) return false;
    if (locationFilter && v.location !== locationFilter) return false;
    if (statusFilter && v.status !== statusFilter) return false;
    if (approvalFilter === 'approved' && !v.approved) return false;
    if (approvalFilter === 'pending' && v.approved) return false;
    return true;
  });

  tbody.innerHTML = filtered.map(vol => {
    const statusClass = `badge-${vol.status}`;
    const statusText = vol.status.charAt(0).toUpperCase() + vol.status.slice(1);
    const approvalClass = vol.approved ? 'approval-approved' : 'approval-pending';
    const approvalText = vol.approved ? 'Approved' : 'Pending';
    const approveDisabled = vol.approved ? 'disabled' : '';

    return `
      <tr>
        <td class="volunteer-name">${vol.name}</td>
        <td>${vol.role}</td>
        <td>${vol.location}</td>
        <td>${vol.events}</td>
        <td>${vol.hours}h</td>
        <td><span class="status-badge ${statusClass}">${statusText}</span></td>
        <td><span class="approval-badge ${approvalClass}">${approvalText}</span></td>
        <td>
          <div class="action-buttons">
            <button class="btn btn-success" ${approveDisabled} onclick="approveVolunteer(${vol.id})">Approve</button>
            <button class="btn btn-secondary" onclick="viewVolunteer(${vol.id})">View</button>
            <button class="btn btn-warning" onclick="suspendVolunteer(${vol.id})">Suspend</button>
            <button class="btn btn-danger" onclick="banVolunteer(${vol.id})">Ban</button>
          </div>
        </td>
      </tr>
    `;
  }).join('');
}

function approveVolunteer(id) {
  const v = volunteers.find(x => x.id === id);
  if (!v || v.approved) return;
  v.approved = true;
  renderVolunteers();
  alert(`Volunteer "${v.name}" has been approved and can now log in.`);
}

function viewVolunteer(id) { alert(`View volunteer ${id} details`); }

function suspendVolunteer(id) {
  const v = volunteers.find(x => x.id === id);
  if (!v) return;
  if (confirm(`Suspend ${v.name}?`)) {
    v.status = 'suspended';
    renderVolunteers();
  }
}

function banVolunteer(id) {
  const v = volunteers.find(x => x.id === id);
  if (!v) return;
  if (confirm(`Ban ${v.name}? This action is permanent in a real system.`)) {
    v.status = 'banned';
    renderVolunteers();
  }
}

function toggleTheme() {
  const html = document.documentElement;
  const currentTheme = html.getAttribute('data-theme');
  const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
  html.setAttribute('data-theme', newTheme);
  const icon = document.getElementById('theme-icon');
  if (icon) icon.textContent = newTheme === 'dark' ? '☀️' : '🌙';
}

function toggleLanguage() {
  const html = document.documentElement;
  const currentLang = html.getAttribute('lang') || 'en';
  const newLang = currentLang === 'en' ? 'ar' : 'en';
  html.setAttribute('lang', newLang);
  html.setAttribute('dir', newLang === 'ar' ? 'rtl' : 'ltr');
  const icon = document.getElementById('lang-icon');
  if (icon) icon.textContent = newLang === 'en' ? 'AR' : 'EN';
}

document.addEventListener('DOMContentLoaded', renderVolunteers);
