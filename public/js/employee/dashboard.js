function escapeHtml(str) {
    if (str == null) return '';
    return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

function formatDayMonth(dateStr) {
    if (!dateStr) return { day: '--', month: '---' };
    const d = new Date(dateStr);
    if (isNaN(d.getTime())) return { day: '--', month: '---' };
    const day = String(d.getDate()).padStart(2, '0');
    const month = d.toLocaleString('en-US', { month: 'short' }); // keep UI same as screenshot
    return { day, month };
}

document.addEventListener('DOMContentLoaded', () => {
    const data = window.dashboardData || {};

    // ---------------- Upcoming Events ----------------
    const upcomingList = document.getElementById('upcoming-events-list');
    const upcoming = Array.isArray(data.upcomingEvents) ? data.upcomingEvents : [];

    if (upcomingList) {
        if (!upcoming.length) {
            upcomingList.innerHTML = `
                <div class="event-item">
                    <div class="event-details">
                        <div class="event-title">No upcoming events</div>
                        <div class="event-meta"><span class="muted">Create a new event to see it here.</span></div>
                    </div>
                    <span class="badge badge-primary">Info</span>
                </div>
            `;
        } else {
            upcomingList.innerHTML = upcoming.map(ev => {
                const { day, month } = formatDayMonth(ev.starts_at);
                const title = escapeHtml(ev.title || 'Untitled Event');
                const location = escapeHtml(ev.location || '-');
                const timeRange = escapeHtml(ev.time_range || ev.timeRange || ev.time || ''); // support multiple keys

                const pct = Number(ev.progress_pct ?? ev.progressPct ?? 0);
                const safePct = isFinite(pct) ? Math.max(0, Math.min(100, pct)) : 0;

                const done = Number(ev.assigned_done ?? ev.assignedDone ?? 0) || 0;
                const total = Number(ev.assigned_total ?? ev.assignedTotal ?? 0) || 0;

                const badgeClass = escapeHtml(ev.badge_class || ev.badgeClass || 'badge-primary');
                const badgeText = escapeHtml(ev.badge_text || ev.badgeText || 'Info');

                return `
                    <div class="event-item">
                        <div class="event-date">
                            <div class="event-day">${day}</div>
                            <div class="event-month">${month}</div>
                        </div>

                        <div class="event-details">
                            <div class="event-title">${title}</div>
                            <div class="event-meta">
                                <span>📍 ${location}</span>
                                <span>⏰ ${timeRange}</span>
                            </div>

                            <div class="event-progress">
                                <div class="progress-bar">
                                    <div class="progress-fill" style="width:${safePct}%"></div>
                                </div>
                                <span>${done}/${total} volunteers</span>
                            </div>
                        </div>

                        <span class="badge ${badgeClass}">${badgeText}</span>
                    </div>
                `;
            }).join('');
        }
    }

    // ---------------- Tasks ----------------
    const tasksList = document.getElementById('tasks-list');
    const tasksCountEl = document.getElementById('tasks-count');
    const tasks = Array.isArray(data.tasks) ? data.tasks : [];

    if (tasksCountEl) {
        tasksCountEl.textContent = `${tasks.length} tasks`;
    }

    if (tasksList) {
        if (!tasks.length) {
            tasksList.innerHTML = `
                <div class="task-item">
                    <div class="task-text">No pending tasks</div>
                    <span class="badge badge-success">Ready</span>
                </div>
            `;
        } else {
            tasksList.innerHTML = tasks.map(t => {
                const text = escapeHtml(t.text || '');
                const badgeClass = escapeHtml(t.badgeClass || t.badge_class || 'badge-primary');
                const priority = escapeHtml(t.priority || 'Medium');

                return `
                    <div class="task-item">
                        <div class="task-text">${text}</div>
                        <span class="badge ${badgeClass}">${priority}</span>
                    </div>
                `;
            }).join('');
        }
    }

    // ---------------- Recent Activity ----------------
    const activityList = document.getElementById('recent-activity-list');
    const activity = Array.isArray(data.recentActivity) ? data.recentActivity : [];

    if (activityList) {
        if (!activity.length) {
            activityList.innerHTML = `<div class="muted">No recent activity yet.</div>`;
        } else {
            activityList.innerHTML = activity.map(a => {
                const iconClass = escapeHtml(a.icon || 'primary');
                const title = escapeHtml(a.title || '');
                const meta = escapeHtml(a.meta || '');
                const time = escapeHtml(a.time || '');

                // same icons logic you used in blade
                let iconChar = '📝';
                if (iconClass === 'success') iconChar = '✓';
                else if (iconClass === 'primary') iconChar = '👤';

                return `
                    <div class="event-item">
                        <div class="status-icon ${iconClass}">${iconChar}</div>

                        <div class="event-details">
                            <div class="event-title">${title}</div>
                            <div class="event-meta">
                                <span>${meta}</span>
                                <span>•</span>
                                <span>${time}</span>
                            </div>
                        </div>
                    </div>
                `;
            }).join('');
        }
    }
});
