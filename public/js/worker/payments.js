// public/js/worker/payments.js

document.addEventListener('DOMContentLoaded', () => {
    const tableBody = document.getElementById('paymentsTableBody');
    if (!tableBody) return;

    const data = window.paymentsData || {};
    const reservations = Array.isArray(data.reservations) ? data.reservations : [];
    const hourlyRate = Number(data.hourlyRate || 0);

    // If no reservations, show the "empty" row
    if (!reservations.length) {
        tableBody.innerHTML = `
            <tr>
                <td colspan="5" class="payments-table-empty">
                    No completed paid shifts yet.
                </td>
            </tr>
        `;
        return;
    }

    const rowsHtml = reservations.map(res => {
        const event = res.event || {};
        const hours = Number(res.credited_hours || 0);
        const amount = hours * hourlyRate;

        // starts_at might be "2025-12-07T10:00:00Z" etc -> keep YYYY-MM-DD
        const startsAt = event.starts_at
            ? String(event.starts_at).slice(0, 10)
            : '';

        const location = event.location || '-';
        const title = event.title || ('Event #' + (res.event_id ?? ''));

        return `
            <tr>
                <td>${escapeHtml(title)}</td>
                <td>${escapeHtml(startsAt)}</td>
                <td>${escapeHtml(location)}</td>
                <td>${hours.toFixed(2)} h</td>
                <td>${amount.toFixed(2)} $</td>
            </tr>
        `;
    }).join('');

    tableBody.innerHTML = rowsHtml;

    console.log('Worker payments page JS rendered', { count: reservations.length });
});


function escapeHtml(str) {
    return String(str).replace(/[&<>"']/g, m => ({
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#39;'
    }[m]));
}
