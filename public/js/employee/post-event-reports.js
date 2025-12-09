document.addEventListener('DOMContentLoaded', () => {
    // Theme toggle
    const html = document.documentElement;
    const themeToggle = document.getElementById('themeToggle');

    if (themeToggle) {
        themeToggle.addEventListener('click', () => {
            const current = html.getAttribute('data-theme') || 'dark';
            const next = current === 'dark' ? 'light' : 'dark';
            html.setAttribute('data-theme', next);
            themeToggle.textContent = next === 'dark' ? '🌙' : '☀️';
        });
    }

    // Language (just switches dir for now)
    const langToggle = document.getElementById('langToggle');
    if (langToggle) {
        langToggle.addEventListener('click', () => {
            const current = langToggle.textContent.trim();
            const next = current === 'EN' ? 'AR' : 'EN';
            langToggle.textContent = next;
            document.documentElement.setAttribute('dir', next === 'AR' ? 'rtl' : 'ltr');
        });
    }

    // Submit filters automatically on change
    const filtersForm = document.getElementById('filtersForm');
    ['eventFilter', 'roleFilter', 'statusFilter'].forEach(id => {
        const el = document.getElementById(id);
        if (el && filtersForm) {
            el.addEventListener('change', () => filtersForm.submit());
        }
    });

    const searchInput = document.getElementById('searchInput');
    if (searchInput && filtersForm) {
        searchInput.addEventListener('keydown', (e) => {
            if (e.key === 'Enter') {
                filtersForm.submit();
            }
        });
    }

    // Attach comment text + rating to approve/reject forms before submit
    document.querySelectorAll('.report-card').forEach(card => {
        const textarea    = card.querySelector('.comment-input');
        const approveForm = card.querySelector('.approve-form');
        const rejectForm  = card.querySelector('.reject-form');

        if (approveForm && textarea) {
            approveForm.addEventListener('submit', () => {
                approveForm.querySelector('input[name="review_notes"]').value = textarea.value;
            });
        }

        if (rejectForm) {
            rejectForm.addEventListener('submit', (e) => {
                const reasonInput = rejectForm.querySelector('input[name="reason"]');

                if (!textarea || !textarea.value.trim()) {
                    e.preventDefault();
                    const reason = prompt('Please provide a reason for rejection:');
                    if (!reason) return;
                    reasonInput.value = reason;
                    rejectForm.submit();
                } else {
                    reasonInput.value = textarea.value;
                }
            });
        }

        // --- ⭐ Worker rating inside each card ---
        const ratingBlock  = card.querySelector('.worker-rating');
        if (!ratingBlock) return;

        const stars        = ratingBlock.querySelectorAll('.star');
        const ratingFields = card.querySelectorAll('input.worker-rating-field');
        const canRate      = ratingBlock.dataset.canRate === '1';

        let currentRating  = parseInt(ratingBlock.dataset.workerRating || '0', 10);

        const syncRatingUI = () => {
            stars.forEach(star => {
                const v = parseInt(star.dataset.value, 10);
                if (v <= currentRating) {
                    star.classList.add('active');
                } else {
                    star.classList.remove('active');
                }
            });
            ratingFields.forEach(input => {
                input.value = currentRating || '';
            });
        };

        if (canRate && stars.length) {
            stars.forEach(star => {
                star.addEventListener('click', () => {
                    const v = parseInt(star.dataset.value, 10) || 0;
                    // click same star again to clear
                    currentRating = (currentRating === v ? 0 : v);
                    syncRatingUI();
                });
            });
        }

        // initial state from DB
        syncRatingUI();
    });
});

// Export stub – later you can connect to real PDF export route
function exportReports(e) {
    e.preventDefault();
    alert('Export to PDF can be wired to a dedicated controller (e.g. using dompdf).');
}
