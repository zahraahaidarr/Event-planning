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
function drawBarChart(canvasId, labels, data, color) {
    const canvas = document.getElementById(canvasId);
    if (!canvas) return;

    const ctx = canvas.getContext('2d');
    const max = Math.max(...data, 5);

    const barHeight = 22;
    const gap = 14;

    ctx.clearRect(0, 0, canvas.width, canvas.height);

    labels.forEach((label, i) => {
        const y = i * (barHeight + gap);

        // Label
        ctx.fillStyle = '#cbd5ff';
        ctx.font = '13px sans-serif';
        ctx.fillText(label, 0, y + barHeight - 6);

        // Bar
        const barWidth = (data[i] / max) * (canvas.width - 160);
        ctx.fillStyle = color;
        ctx.fillRect(150, y, barWidth, barHeight);

        // Value
        ctx.fillStyle = '#fff';
        ctx.fillText(data[i].toFixed(2), 150 + barWidth + 8, y + barHeight - 6);
    });
}

// ===== Render charts =====
document.addEventListener('DOMContentLoaded', () => {
    const w = window.dashboardData.topWorkersRating || [];
    const c = window.dashboardData.topClientsRating || [];

    drawBarChart(
        'workersRatingChart',
        w.map(x => `${x.name} (${x.ratings_count})`),
w.map(x => Number(x.avg_rating || 0)),
        '#4f7cff'
    );

    drawBarChart(
        'clientsRatingChart',
        c.map(x => `${x.name} (${x.ratings_count})`),
c.map(x => Number(x.avg_rating || 0)),
        '#9c6cff'
    );
});
function renderReliability(listId, items) {
    const wrap = document.getElementById(listId);
    if (!wrap) return;

    if (!items.length) {
        wrap.innerHTML = `<p class="placeholder">No reliability data yet.</p>`;
        return;
    }

    wrap.innerHTML = items.map(x => {
        const name = escapeHtml(x.name || 'Client');
        const pct = Number(x.reliability_pct ?? 0);
        const safePct = isFinite(pct) ? Math.max(0, Math.min(100, pct)) : 0;

        const total = Number(x.total_events ?? 0) || 0;
        const completed = Number(x.completed_events ?? 0) || 0;
        const cancelled = Number(x.cancelled_events ?? 0) || 0;

        return `
            <div class="reliability-item">
                <div class="reliability-ring" style="--p:${safePct}">
                    <div class="reliability-ring-inner">
                        <div class="reliability-pct">${safePct}%</div>
                    </div>
                </div>

                <div class="reliability-info">
                    <div class="reliability-name">${name}</div>
                    <div class="reliability-meta">
                        <span>✅ ${completed} completed</span>
                        <span>•</span>
                        <span>❌ ${cancelled} cancelled</span>
                        <span>•</span>
                        <span>📦 ${total} total</span>
                    </div>
                </div>
            </div>
        `;
    }).join('');
}

document.addEventListener('DOMContentLoaded', () => {
    const data = window.dashboardData || {};

    // keep your workers chart
    const w = data.topWorkersRating || [];
    drawBarChart(
        'workersRatingChart',
        w.map(x => `${x.name} (${x.ratings_count})`),
        w.map(x => parseFloat(x.avg_rating)),
        '#4f7cff'
    );

    // ✅ NEW reliability circles
    renderReliability('clientsReliabilityList', data.topClientsReliability || []);
});
