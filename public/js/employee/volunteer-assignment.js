// public/js/employee/volunteer-assignment.js

// Small helpers (like we used on other pages)
const $  = (s, r = document) => r.querySelector(s);
const $$ = (s, r = document) => Array.from(r.querySelectorAll(s));

let currentEventId   = null;
let currentFilter    = 'all';
let currentApps      = [];
let currentStats     = { total: 0, pending: 0, rejected: 0 };

document.addEventListener('DOMContentLoaded', () => {
    const eventSelect         = $('#eventSelect');
    const statsGrid           = $('#statsGrid');
    const applicationsSection = $('#applicationsSection');
    const filterButtons       = $$('.filter-btn');

    const btnTheme            = $('#btnTheme');
    const btnLang             = $('#btnLang');
    const btnCloseModal       = $('#btnCloseModal');
    const btnCloseModalFooter = $('#btnCloseModalFooter');

    // ----------------- event dropdown -----------------
    if (eventSelect) {
        // Load applications for the initially selected event (if any)
        if (eventSelect.value) {
            currentEventId = eventSelect.value;
            loadApplicationsForEvent(currentEventId, statsGrid, applicationsSection);
        }

        // Reload when user changes event
        eventSelect.addEventListener('change', async () => {
            const eventId = eventSelect.value;

            if (!eventId) {
                if (statsGrid)           statsGrid.style.display = 'none';
                if (applicationsSection) applicationsSection.style.display = 'none';
                currentEventId = null;
                currentApps = [];
                currentStats = { total: 0, pending: 0, rejected: 0 };
                updateStats();
                renderApplications();
                return;
            }

            currentEventId = eventId;
            await loadApplicationsForEvent(eventId, statsGrid, applicationsSection);
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

// =====================================================
// AJAX: load applications for one event from backend
// =====================================================
async function loadApplicationsForEvent(eventId, statsGrid, applicationsSection) {
    // window.ENDPOINT_APPS_BASE is set in Blade, e.g.
    // window.ENDPOINT_APPS_BASE = "{{ url('/employee/volunteer-assignment/events') }}";
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
        console.log('Applications response:', data); // <= should show rows from DB

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
// UI helpers
// =====================================================
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

    const filtered = currentFilter === 'all'
        ? currentApps
        : currentApps.filter(a => a.status === currentFilter);

    if (filtered.length === 0) {
        list.innerHTML = `
            <div class="empty-state">
                <div class="empty-icon">📭</div>
                <h3 class="empty-title">No applications found</h3>
                <p class="empty-description">
                    There are no ${currentFilter === 'all' ? '' : currentFilter} applications for this event.
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

        let actions = '';

        if (app.status === 'pending') {
            // ⬇️ Pending: show green Accept + red Reject + View Profile
            actions = `
                <button class="btn btn-success" onclick="acceptApplication(${app.id})">Accept</button>
                <button class="btn btn-danger"  onclick="rejectApplication(${app.id})">Reject</button>
                <button class="btn btn-secondary" onclick="viewProfile(${app.volunteerId})">View Profile</button>
            `;
        } else if (app.status === 'rejected') {
            // Still allow re-accepting if needed
            actions = `
                <button class="btn btn-success" onclick="acceptApplication(${app.id})">Accept</button>
                <button class="btn btn-secondary" onclick="viewProfile(${app.volunteerId})">View Profile</button>
            `;
        } else if (app.status === 'accepted') {
            actions = `
                <button class="btn btn-secondary" onclick="viewProfile(${app.volunteerId})">View Profile</button>
            `;
        } else {
            // fallback
            actions = `<button class="btn btn-secondary" onclick="viewProfile(${app.volunteerId})">View Profile</button>`;
        }

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
                        <span class="info-label">Experience</span>
                        <span class="info-value">${app.experience ?? '-'}</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Previous Events</span>
                        <span class="info-value">${app.previousEvents ?? 0} events</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Skills</span>
                        <span class="info-value">${app.skills ?? '-'}</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Availability</span>
                        <span class="info-value">${app.availability ?? '-'}</span>
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

        // Update the in-memory item
        const app = currentApps.find(a => a.id === id);
        if (app) {
            app.status = data.uiStatus || app.status;
        }

        // Recalculate stats
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

// Local-only status changes (UI only)
async function rejectApplication(id) {
    // DB status -> CANCELLED
    await updateReservationStatus(id, 'REJECTED');
}

async function acceptApplication(id) {
    // DB status -> RESERVED
    await updateReservationStatus(id, 'RESERVED');
}


// ---------- Profile modal ----------
function viewProfile(volunteerId) {
    const app = currentApps.find(a => a.volunteerId === volunteerId);
    if (!app) return;

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
        <div class="profile-section">
            <h3>Volunteer Experience</h3>
            <div class="profile-grid">
                <div class="profile-row">
                    <span class="info-label">Experience:</span>
                    <span class="info-value">${app.experience ?? '-'}</span>
                </div>
                <div class="profile-row">
                    <span class="info-label">Previous Events:</span>
                    <span class="info-value">${app.previousEvents ?? 0} events completed</span>
                </div>
                <div class="profile-row">
                    <span class="info-label">Skills:</span>
                    <span class="info-value">${app.skills ?? '-'}</span>
                </div>
                <div class="profile-row">
                    <span class="info-label">Availability:</span>
                    <span class="info-value">${app.availability ?? '-'}</span>
                </div>
            </div>
        </div>
        <div class="profile-section">
            <h3>Application Details</h3>
            <div class="profile-grid">
                <div class="profile-row">
                    <span class="info-label">Applied Role:</span>
                    <span class="info-value">${app.role ?? '-'}</span>
                </div>
                <div class="profile-row">
                    <span class="info-label">Application Date:</span>
                    <span class="info-value">${app.appliedDate ?? '-'}</span>
                </div>
                <div class="profile-row">
                    <span class="info-label">Status:</span>
                    <span class="info-value">${(app.status || '').charAt(0).toUpperCase() + (app.status || '').slice(1)}</span>
                </div>
            </div>
        </div>
    `;

    $('#profileModal').classList.add('active');
}

function closeModal() {
    $('#profileModal').classList.remove('active');
}

// Theme / language (same as other pages)
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
