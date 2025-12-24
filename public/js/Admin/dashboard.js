/* =========================================================
   Admin Dashboard JS
   - Recent Clients / Recent Events cards
   - Top Workers & Volunteers rating chart (worker_rating)
   - Top Clients rating chart (owner_rating)
========================================================= */

function escapeHtml(str) {
    if (str == null) return '';
    return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

function drawBarChart(canvasId, labels, data, color) {
    const canvas = document.getElementById(canvasId);
    if (!canvas) return;

    const parent = canvas.parentElement;

    // ✅ HARD RESET CANVAS (removes ANY stray glyph like "!" 100%)
    // Also makes canvas fit the card width
    const targetWidth = parent ? parent.clientWidth : canvas.width || 600;
    canvas.width = Math.max(300, targetWidth); // reset clears bitmap
    canvas.height = canvas.height || 180;      // keep your blade height (180)

    // make sure it behaves like a block element (no weird inline text artifacts)
    canvas.style.display = 'block';

    const ctx = canvas.getContext('2d');

    // If no data -> stop (canvas already cleared by width reset)
    if (!Array.isArray(data) || data.length === 0) return;

    const numeric = data.map(v => {
        const n = Number(v);
        return Number.isFinite(n) ? n : 0;
    });

    const max = Math.max(...numeric, 5);

    const barHeight = 22;
    const gap = 14;

    // layout
    const labelX = 0;
    const barX = 150;
    const barMaxWidth = canvas.width - (barX + 80); // leave space for value text

    // drawing settings
    ctx.font = '13px sans-serif';
    ctx.textBaseline = 'alphabetic';

    labels.forEach((label, i) => {
        const y = i * (barHeight + gap);

        // Label
        ctx.fillStyle = '#cbd5ff';
        ctx.fillText(label, labelX, y + barHeight - 6);

        // Bar width (safe)
        const rawWidth = (numeric[i] / max) * barMaxWidth;
        const barWidth = Number.isFinite(rawWidth) ? Math.max(0, rawWidth) : 0;

        // Bar
        ctx.fillStyle = color;
        ctx.fillRect(barX, y, barWidth, barHeight);

        // ✅ Value (numbers ONLY — no "!" possible after hard reset)
        const val = numeric[i];
        const text = (Number.isFinite(val) && val > 0) ? val.toFixed(2) : '';

        if (text) {
            ctx.fillStyle = '#fff';
            ctx.fillText(text, barX + barWidth + 8, y + barHeight - 6);
        }
    });
}

function showChartPlaceholder(canvasId, message) {
    const canvas = document.getElementById(canvasId);
    if (!canvas) return;

    const parent = canvas.parentElement;
    if (!parent) return;

    const old = parent.querySelector('.chart-empty');
    if (old) old.remove();

    const p = document.createElement('p');
    p.className = 'placeholder chart-empty';
    p.textContent = message || 'No data yet.';
    parent.appendChild(p);
}

document.addEventListener('DOMContentLoaded', function () {
    const data = window.dashboardData || {};

    /* ===== Recent Clients ===== */
    const empList = document.getElementById('recent-employees-list');
    if (empList) {
        const employees = data.recentEmployees || [];
        if (!employees.length) {
            empList.innerHTML = '<p class="placeholder">No clients found.</p>';
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

    /* ===== Recent Events ===== */
    const evList = document.getElementById('recent-events-list');
    if (evList) {
        const events = data.recentEvents || [];
        if (!events.length) {
            evList.innerHTML = '<p class="placeholder">No events found.</p>';
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

    /* ===== Top Workers & Volunteers Rating ===== */
    const w = data.topWorkersRating || [];
    if (!w.length) {
        showChartPlaceholder('workersRatingChart', 'No worker ratings yet.');
    } else {
        drawBarChart(
            'workersRatingChart',
            w.map(x => `${x.name}`), // ✅ no parentheses / no counts
            w.map(x => Number(x.avg_rating || 0)),
            '#4f7cff'
        );
    }

    /* ===== Top Clients Rating (owner_rating) ===== */
    const c = data.topClientsRating || [];
    if (!c.length) {
        showChartPlaceholder('clientsRatingChart', 'No client ratings yet.');
    } else {
        drawBarChart(
            'clientsRatingChart',
            c.map(x => `${x.name}`), // ✅ no parentheses / no counts
            c.map(x => Number(x.avg_rating || 0)),
            '#9c6cff'
        );
    }
});
