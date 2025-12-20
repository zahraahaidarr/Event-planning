function escapeHtml(str) {
  if (str == null) return '';
  return String(str)
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#039;');
}

document.addEventListener('DOMContentLoaded', () => {
  const csrf = document.querySelector('meta[name="csrf-token"]').content;

  const searchInput = document.getElementById('searchInput');
  const refreshBtn = document.getElementById('refreshBtn');
  const employeesList = document.getElementById('employeesList');
  const statusLine = document.getElementById('statusLine');

  let lastQ = '';

  const debounce = (fn, ms = 350) => {
    let t;
    return (...args) => {
      clearTimeout(t);
      t = setTimeout(() => fn(...args), ms);
    };
  };

 async function loadEmployees(q = '') {
  lastQ = q;
  statusLine.textContent = 'Loading...';
  employeesList.innerHTML = '';

  const url = new URL('/worker/follow-employees/search', window.location.origin);
  if (q.trim() !== '') url.searchParams.set('q', q.trim());

  let res;
  try {
    res = await fetch(url.toString(), {
      headers: { 'Accept': 'application/json' }
    });
  } catch (err) {
    console.error('Fetch error:', err);
    statusLine.textContent = 'Failed to load (network error).';
    return;
  }

  // ✅ If backend returns 403/500/302 etc
  if (!res.ok) {
    statusLine.textContent = `Failed to load. HTTP ${res.status}`;
    const text = await res.text();
    console.error('Response text:', text);
    return;
  }

  // ✅ Parse JSON safely
  let data;
  try {
    data = await res.json();
  } catch (err) {
    statusLine.textContent = 'Failed to load (invalid JSON response).';
    const text = await res.text();
    console.error('JSON parse error:', err);
    console.error('Response text:', text);
    return;
  }

  // ✅ Your API contract: { ok: true, data: [...] }
  if (!data.ok) {
    statusLine.textContent = 'Failed to load (API ok=false).';
    console.error('API JSON:', data);
    return;
  }

  const arr = data.data || [];
  statusLine.textContent = arr.length ? `Found: ${arr.length}` : 'No employees found.';

  employeesList.innerHTML = arr.map(emp => {
    return `
      <div class="empRow" data-id="${emp.id}">
        <div class="empInfo">
          <div class="empName">${escapeHtml(emp.name)}</div>
          <div class="empEmail">${escapeHtml(emp.email)}</div>
        </div>
        <button class="followBtn ${emp.is_following ? 'following' : ''}">
          ${emp.is_following ? 'Following ✓' : 'Follow +'}
        </button>
      </div>
    `;
  }).join('');
}

  async function toggleFollow(employeeId, btnEl) {
    btnEl.disabled = true;
    const res = await fetch(`/worker/follow-employees/${employeeId}/toggle`, {
      method: 'POST',
      headers: {
        'X-CSRF-TOKEN': csrf,
        'Accept': 'application/json'
      }
    });

    const data = await res.json();
    btnEl.disabled = false;

    if (!data.ok) return;

    const following = !!data.following;
    btnEl.classList.toggle('following', following);
    btnEl.textContent = following ? 'Following ✓' : 'Follow +';
  }

  employeesList.addEventListener('click', (e) => {
    const btn = e.target.closest('.followBtn');
    if (!btn) return;
    const row = e.target.closest('.empRow');
    if (!row) return;

    const employeeId = row.getAttribute('data-id');
    toggleFollow(employeeId, btn);
  });

  const debouncedSearch = debounce(() => loadEmployees(searchInput.value), 350);

  searchInput.addEventListener('input', debouncedSearch);

  refreshBtn.addEventListener('click', () => {
    loadEmployees(lastQ);
  });

  // initial load
  loadEmployees('');
});
