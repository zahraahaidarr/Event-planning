// ---- Mock Data (replace with API/Ajax later) ----
const events = [
  {
    id: 1,
    title: "Community Garden Cleanup",
    description: "Help maintain our community garden by weeding, planting, and general maintenance.",
    category: "environment",
    location: "Riyadh",
    date: "2025-01-15",
    time: "09:00 AM",
    duration: "4 hours",
    spotsTotal: 20,
    spotsRemaining: 12,
    status: "open",
    roles: ["Gardener", "Organizer"],
    image: "/placeholder.svg?height=180&width=340"
  },
  {
    id: 2,
    title: "Children's Reading Program",
    description: "Read stories to children at the local library and help foster a love of reading.",
    category: "education",
    location: "Jeddah",
    date: "2025-01-18",
    time: "02:00 PM",
    duration: "3 hours",
    spotsTotal: 10,
    spotsRemaining: 3,
    status: "limited",
    roles: ["Reader", "Activity Leader"],
    image: "/placeholder.svg?height=180&width=340"
  },
  {
    id: 3,
    title: "Food Bank Distribution",
    description: "Assist with sorting and distributing food to families in need.",
    category: "community",
    location: "Riyadh",
    date: "2025-01-20",
    time: "08:00 AM",
    duration: "5 hours",
    spotsTotal: 30,
    spotsRemaining: 0,
    status: "full",
    roles: ["Sorter", "Distributor", "Driver"],
    image: "/placeholder.svg?height=180&width=340"
  },
  {
    id: 4,
    title: "Senior Center Activities",
    description: "Spend time with elderly residents, play games, and provide companionship.",
    category: "elderly",
    location: "Dammam",
    date: "2025-01-22",
    time: "10:00 AM",
    duration: "3 hours",
    spotsTotal: 15,
    spotsRemaining: 8,
    status: "open",
    roles: ["Companion", "Activity Coordinator"],
    image: "/placeholder.svg?height=180&width=340"
  },
  {
    id: 5,
    title: "Beach Cleanup Initiative",
    description: "Join us in cleaning up the beach and protecting marine life.",
    category: "environment",
    location: "Jeddah",
    date: "2025-01-25",
    time: "07:00 AM",
    duration: "4 hours",
    spotsTotal: 50,
    spotsRemaining: 35,
    status: "open",
    roles: ["Cleanup Crew", "Team Leader"],
    image: "/placeholder.svg?height=180&width=340"
  },
  {
    id: 6,
    title: "Health Awareness Campaign",
    description: "Help distribute health information and assist with free health screenings.",
    category: "health",
    location: "Riyadh",
    date: "2025-01-28",
    time: "09:00 AM",
    duration: "6 hours",
    spotsTotal: 25,
    spotsRemaining: 18,
    status: "open",
    roles: ["Information Desk", "Registration", "Guide"],
    image: "/placeholder.svg?height=180&width=340"
  },
  {
    id: 7,
    title: "Youth Mentorship Program",
    description: "Mentor young students in career development and life skills.",
    category: "education",
    location: "Mecca",
    date: "2025-02-01",
    time: "03:00 PM",
    duration: "2 hours",
    spotsTotal: 12,
    spotsRemaining: 5,
    status: "limited",
    roles: ["Mentor", "Workshop Leader"],
    image: "/placeholder.svg?height=180&width=340"
  },
  {
    id: 8,
    title: "Animal Shelter Support",
    description: "Help care for animals at the local shelter, including feeding and cleaning.",
    category: "community",
    location: "Medina",
    date: "2025-02-03",
    time: "10:00 AM",
    duration: "4 hours",
    spotsTotal: 20,
    spotsRemaining: 14,
    status: "open",
    roles: ["Animal Care", "Cleaner", "Walker"],
    image: "/placeholder.svg?height=180&width=340"
  }
];

let filteredEvents = [...events];

// ---- Rendering ----
function renderEvents(view = "grid") {
  const grid = document.getElementById('eventsGrid');
  if (!grid) return;

  grid.innerHTML = '';

  filteredEvents.forEach(event => {
    const statusClass = `status-${event.status}`;
    const statusText = event.status === 'open' ? 'Open' : event.status === 'limited' ? 'Limited Spots' : 'Full';

    const card = document.createElement('div');
    card.className = 'event-card';
    card.onclick = () => openEventModal(event);

    // Optionally adapt layout when in "list" view (simple modifier)
    if (view === 'list') {
      card.style.display = 'grid';
      card.style.gridTemplateColumns = '180px 1fr';
      card.style.alignItems = 'stretch';
    }

    card.innerHTML = `
      <img src="${event.image}" alt="${event.title}" class="event-image">
      <div class="event-content">
        <div class="event-header">
          <span class="event-category">${event.category}</span>
          <span class="event-status ${statusClass}">${statusText}</span>
        </div>
        <h3 class="event-title">${event.title}</h3>
        <p class="event-description">${event.description}</p>
        <div class="event-meta">
          <div class="meta-item">
            <span class="meta-icon">📅</span>
            <span>${event.date} at ${event.time}</span>
          </div>
          <div class="meta-item">
            <span class="meta-icon">📍</span>
            <span>${event.location}</span>
          </div>
          <div class="meta-item">
            <span class="meta-icon">⏱️</span>
            <span>${event.duration}</span>
          </div>
        </div>
        <div class="event-roles">
          ${event.roles.map(role => `<span class="role-badge">${role}</span>`).join('')}
        </div>
        <div class="event-footer">
          <span class="spots-remaining">
            <strong>${event.spotsRemaining}</strong> / ${event.spotsTotal} spots
          </span>
          <button class="btn-apply" ${event.status === 'full' ? 'disabled' : ''} onclick="event.stopPropagation(); openEventModal(${event.id});">
            ${event.status === 'full' ? 'Full' : 'Apply'}
          </button>
        </div>
      </div>
    `;

    grid.appendChild(card);
  });

  const countEl = document.getElementById('resultsCount');
  if (countEl) countEl.textContent = filteredEvents.length;
}

// ---- Filters ----
function applyFilters() {
  const termInput = document.getElementById('searchInput');
  const categorySel = document.getElementById('categoryFilter');
  const locationSel = document.getElementById('locationFilter');
  const availabilitySel = document.getElementById('availabilityFilter');

  const searchTerm = (termInput?.value || '').toLowerCase();
  const category = categorySel?.value || '';
  const location = locationSel?.value || '';
  const availability = availabilitySel?.value || '';

  filteredEvents = events.filter(event => {
    const matchesSearch =
      event.title.toLowerCase().includes(searchTerm) ||
      event.description.toLowerCase().includes(searchTerm) ||
      event.location.toLowerCase().includes(searchTerm);

    const matchesCategory = !category || event.category === category;
    const matchesLocation = !location || event.location.toLowerCase() === location;
    const matchesAvailability = !availability || event.status === availability;

    return matchesSearch && matchesCategory && matchesLocation && matchesAvailability;
  });

  renderEvents(getCurrentView());
  updateActiveFilters();
}

function updateActiveFilters() {
  const activeFiltersDiv = document.getElementById('activeFilters');
  if (!activeFiltersDiv) return;

  const filters = [];

  const category = document.getElementById('categoryFilter')?.value;
  const location = document.getElementById('locationFilter')?.value;
  const availability = document.getElementById('availabilityFilter')?.value;

  if (category) filters.push({ type: 'category', value: category });
  if (location) filters.push({ type: 'location', value: location });
  if (availability) filters.push({ type: 'availability', value: availability });

  if (filters.length > 0) {
    activeFiltersDiv.style.display = 'flex';
    activeFiltersDiv.innerHTML = filters
      .map(f => `
        <span class="filter-tag">
          ${f.value}
          <span class="filter-tag-close" onclick="removeFilter('${f.type}')">×</span>
        </span>
      `).join('');
  } else {
    activeFiltersDiv.style.display = 'none';
    activeFiltersDiv.innerHTML = '';
  }
}

function removeFilter(type) {
  const el = document.getElementById(`${type}Filter`);
  if (el) el.value = '';
  applyFilters();
}

// ---- Modal ----
function openEventModal(eventOrId) {
  const event = typeof eventOrId === 'number' ? events.find(e => e.id === eventOrId) : eventOrId;
  const modal = document.getElementById('eventModal');
  const modalBody = document.getElementById('modalBody');

  if (!modal || !modalBody || !event) return;

  modalBody.innerHTML = `
    <img src="${event.image}" alt="${event.title}" style="width:100%;border-radius:var(--radius-md);margin-bottom:20px;">
    <h3 style="margin-bottom:12px;">${event.title}</h3>
    <p style="color:var(--text-secondary);margin-bottom:20px;">${event.description}</p>

    <div style="display:grid;gap:16px;margin-bottom:20px;">
      <div class="meta-item"><span class="meta-icon">📅</span><span><strong>Date:</strong> ${event.date} at ${event.time}</span></div>
      <div class="meta-item"><span class="meta-icon">📍</span><span><strong>Location:</strong> ${event.location}</span></div>
      <div class="meta-item"><span class="meta-icon">⏱️</span><span><strong>Duration:</strong> ${event.duration}</span></div>
      <div class="meta-item"><span class="meta-icon">👥</span><span><strong>Available Spots:</strong> ${event.spotsRemaining} / ${event.spotsTotal}</span></div>
    </div>

    <div style="margin-bottom:20px;">
      <strong style="display:block;margin-bottom:8px;">Available Roles:</strong>
      <div class="event-roles">
        ${event.roles.map(role => `<span class="role-badge">${role}</span>`).join('')}
      </div>
    </div>

    <div style="padding:16px;background:var(--bg-secondary);border-radius:var(--radius-md);">
      <strong>Requirements:</strong>
      <ul style="margin-top:8px;padding-left:20px;color:var(--text-secondary);">
        <li>Must be 18 years or older</li>
        <li>Commitment to full event duration</li>
        <li>Appropriate attire for the activity</li>
      </ul>
    </div>
  `;

  modal.classList.add('active');
}

function closeModal() {
  const modal = document.getElementById('eventModal');
  if (modal) modal.classList.remove('active');
}

function applyToEvent() {
  // Hook this to a real POST later.
  alert('Application submitted successfully!');
  closeModal();
}

// ---- Theme & Language ----
function toggleTheme() {
  const html = document.documentElement;
  const current = html.getAttribute('data-theme');
  const next = current === 'dark' ? 'light' : 'dark';
  html.setAttribute('data-theme', next);
  const icon = document.getElementById('theme-icon');
  if (icon) icon.textContent = next === 'dark' ? '☀️' : '🌙';
}

function toggleLanguage() {
  const html = document.documentElement;
  const currentLang = html.getAttribute('lang') || 'en';
  const newLang = currentLang === 'en' ? 'ar' : 'en';
  const newDir = newLang === 'ar' ? 'rtl' : 'ltr';
  html.setAttribute('lang', newLang);
  html.setAttribute('dir', newDir);
  const icon = document.getElementById('lang-icon');
  if (icon) icon.textContent = newLang === 'en' ? 'AR' : 'EN';
}

// ---- View toggle ----
function getCurrentView(){
  const gridBtn = document.getElementById('gridViewBtn');
  return gridBtn && gridBtn.classList.contains('active') ? 'grid' : 'list';
}

function setView(view){
  const gridBtn = document.getElementById('gridViewBtn');
  const listBtn = document.getElementById('listViewBtn');
  if (!gridBtn || !listBtn) return;

  if (view === 'grid'){
    gridBtn.classList.add('active');
    listBtn.classList.remove('active');
  } else {
    listBtn.classList.add('active');
    gridBtn.classList.remove('active');
  }
  renderEvents(view);
}

// ---- Init ----
document.addEventListener('DOMContentLoaded', () => {
  // initial render
  renderEvents('grid');

  // Listeners
  const searchInput = document.getElementById('searchInput');
  const categoryFilter = document.getElementById('categoryFilter');
  const locationFilter = document.getElementById('locationFilter');
  const availabilityFilter = document.getElementById('availabilityFilter');
  const gridBtn = document.getElementById('gridViewBtn');
  const listBtn = document.getElementById('listViewBtn');

  if (searchInput) searchInput.addEventListener('input', applyFilters);
  if (categoryFilter) categoryFilter.addEventListener('change', applyFilters);
  if (locationFilter) locationFilter.addEventListener('change', applyFilters);
  if (availabilityFilter) availabilityFilter.addEventListener('change', applyFilters);

  if (gridBtn) gridBtn.addEventListener('click', () => setView('grid'));
  if (listBtn) listBtn.addEventListener('click', () => setView('list'));
});

// Expose functions used by inline handlers in Blade
window.applyFilters = applyFilters;
window.removeFilter = removeFilter;
window.openEventModal = openEventModal;
window.closeModal = closeModal;
window.applyToEvent = applyToEvent;
window.toggleTheme = toggleTheme;
window.toggleLanguage = toggleLanguage;
