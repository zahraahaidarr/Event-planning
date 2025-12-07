// public/js/employee/volunteer-assignment.js

// Small helpers (like we used on other pages)
const $  = (s, r = document) => r.querySelector(s);
const $$ = (s, r = document) => Array.from(r.querySelectorAll(s));

let currentEventId   = null;
let currentFilter    = 'all';
let currentApps      = [];
let currentStats     = { total: 0, pending: 0, rejected: 0 };

document.addEventListener('DOMContentLoaded', () => {
    const eventButtons       = $$('.event-card');
    const statsGrid          = $('#statsGrid');
    const applicationsSection= $('#applicationsSection');
    const filterButtons      = $$('.filter-btn');

    const btnTheme            = $('#btnTheme');
    const btnLang             = $('#btnLang');
    const btnCloseModal       = $('#btnCloseModal');
    const btnCloseModalFooter = $('#btnCloseModalFooter');

    // ----------------- event cards -----------------
    if (eventButtons.length) {
        // Auto-load first event on page open
        const firstBtn = eventButtons[0];
        const firstId  = firstBtn.dataset.eventId;
        if (firstId) {
            currentEventId = firstId;
            firstBtn.classList.add('active');
            loadApplicationsForEvent(currentEventId, statsGrid, applicationsSection);
        }

        // Click handlers: change active card & reload applications
        eventButtons.forEach(btn => {
            btn.addEventListener('click', async () => {
                const eventId = btn.dataset.eventId;
                if (!eventId || eventId === currentEventId) return;

                currentEventId = eventId;

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

// =====================================================
// AJAX: load applications for one event from backend
// =====================================================
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
        console.log('Applications response:', data);

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
        } else if (app.status === 'rejected') {
            actions = `
                <button class="btn btn-success" onclick="acceptApplication(${app.id})">Accept</button>
                <button class="btn btn-secondary" onclick="viewProfile(${app.volunteerId})">View Profile</button>
            `;
        } else if (app.status === 'accepted') {
            actions = `
                <button class="btn btn-secondary" onclick="viewProfile(${app.volunteerId})">View Profile</button>
            `;
        } else {
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

                    <!-- 🔹 Credited hours instead of Experience -->
                    <div class="info-item">
                        <span class="info-label">Credited hours</span>
                        <span class="info-value">${hours.toFixed(2)} h</span>
                    </div>

                    <div class="info-item">
                        <span class="info-label">Previous Events</span>
                        <span class="info-value">${app.previousEvents ?? 0} events</span>
                    </div>

                    <!-- 🔹 Worker type instead of Skills -->
                    <div class="info-item">
                        <span class="info-label">Type</span>
                        <span class="info-value">${workerType}</span>
                    </div>

                    <!-- 🔹 Hourly rate instead of Availability -->
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
        if (app) {
            app.status = data.uiStatus || app.status;
        }

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

        <div class="profile-section">
            <h3>Work & Hours</h3>
            <div class="profile-grid">
                <div class="profile-row">
                    <span class="info-label">Type:</span>
                    <span class="info-value">${workerType}</span>
                </div>
                <div class="profile-row">
                    <span class="info-label">Credited hours (this event):</span>
                    <span class="info-value">${hours.toFixed(2)} h</span>
                </div>
                <div class="profile-row">
                    <span class="info-label">Hourly rate:</span>
                    <span class="info-value">
                        ${hourlyRate > 0 ? hourlyRate.toFixed(2) + ' $/h' : '-'}
                    </span>
                </div>
                <div class="profile-row">
                    <span class="info-label">Completed events:</span>
                    <span class="info-value">${app.previousEvents ?? 0} events</span>
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
                    <span class="info-value">
                        ${(app.status || '').charAt(0).toUpperCase() + (app.status || '').slice(1)}
                    </span>
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
