function escapeHtml(str) {
    if (str == null) return '';
    return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

document.addEventListener('DOMContentLoaded', function () {
    const data = window.dashboardData || {};

    // ----- Recent Employees -----
    const empList = document.getElementById('recent-employees-list');
    if (empList) {
        const employees = data.recentEmployees || [];

        if (!employees.length) {
            empList.innerHTML = '<p>No employees found.</p>';
        } else {
            empList.innerHTML = employees.map(e => {
                const badgeClass = e.is_active ? 'badge-active' : 'badge-pending';
                const badgeLabel = e.is_active ? 'Active' : 'Inactive';

                return `
                    <div class="activity-item">
                        <div class="activity-info">
                            <h4>${escapeHtml(e.name)}</h4>
                            <p>${escapeHtml(e.role)} — ${escapeHtml(e.joined)}</p>
                        </div>
                        <span class="activity-badge ${badgeClass}">
                            ${badgeLabel}
                        </span>
                    </div>
                `;
            }).join('');
        }
    }

    // ----- Recent Events -----
    const evList = document.getElementById('recent-events-list');
    if (evList) {
        const events = data.recentEvents || [];

        if (!events.length) {
            evList.innerHTML = '<p>No events found.</p>';
        } else {
            evList.innerHTML = events.map(ev => {
                const badgeClass = ev.is_done ? 'badge-active' : 'badge-pending';
                const badgeLabel = ev.is_done ? 'Done' : 'Upcoming';

                return `
                    <div class="activity-item">
                        <div class="activity-info">
                            <h4>${escapeHtml(ev.title)}</h4>
                            <p>${escapeHtml(ev.location)} — ${escapeHtml(ev.starts_at || '')}</p>
                        </div>
                        <span class="activity-badge ${badgeClass}">
                            ${badgeLabel}
                        </span>
                    </div>
                `;
            }).join('');
        }
    }
});
