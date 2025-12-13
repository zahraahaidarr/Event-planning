// public/js/worker/dashboard.js
document.addEventListener('DOMContentLoaded', () => {
  const form = document.getElementById('filtersForm');
  if (!form) return;

  const qHidden = document.getElementById('qHidden');
  const globalSearch = document.getElementById('globalSearch');

  const debounce = (fn, ms = 350) => {
    let t;
    return (...args) => {
      clearTimeout(t);
      t = setTimeout(() => fn(...args), ms);
    };
  };

  // Auto-submit when filters change
  form.querySelectorAll('select, input[type="date"]').forEach(el => {
    el.addEventListener('change', () => {
      if (globalSearch && qHidden) qHidden.value = globalSearch.value.trim();
      form.submit();
    });
  });

  // Top search => writes to hidden q => submit
  if (globalSearch && qHidden) {
    const submitSearch = debounce(() => {
      qHidden.value = globalSearch.value.trim();
      form.submit();
    }, 400);

    globalSearch.addEventListener('input', submitSearch);

    globalSearch.addEventListener('keydown', (e) => {
      if (e.key === 'Enter') {
        e.preventDefault();
        qHidden.value = globalSearch.value.trim();
        form.submit();
      }
    });
  }
});
