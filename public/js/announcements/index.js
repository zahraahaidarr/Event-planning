(function () {
  const STRINGS = {
    en: {
      pageTitle: "Announcements",
      pageSubtitle: "Important updates and news for the volunteer community.",
      search: "Search announcements…",
      important: "Important",
      info: "Info",
      success: "Success",
      empty: "No announcements found."
    },
    ar: {
      pageTitle: "التعميمات",
      pageSubtitle: "التحديثات والأخبار المهمة لمجتمع المتطوعين.",
      search: "ابحث في التعميمات…",
      important: "مهم",
      info: "معلومة",
      success: "نجاح",
      empty: "لا توجد تعميمات."
    }
  };

  let lang = 'en';
  const role = (window.currentRole || 'worker').toLowerCase();
  const announcements = Array.isArray(window.initialAnnouncements)
    ? window.initialAnnouncements
    : [];

  const $ = sel => document.querySelector(sel);
  const list = $('#announceList');
  const searchInput = $('#globalSearch');
  const pageTitle = $('#pageTitle');
  const pageSubtitle = $('#pageSubtitle');
  const langToggle = $('#langToggle');
  const themeToggle = $('#themeToggle');

  function applyI18n() {
    const s = STRINGS[lang];
    pageTitle.textContent = s.pageTitle;
    pageSubtitle.textContent = s.pageSubtitle;
    if (searchInput && !searchInput.value) {
      searchInput.placeholder = s.search;
    }
    if (langToggle) langToggle.textContent = (lang === 'en') ? 'EN' : 'AR';
  }

  function mapType(type) {
    const s = STRINGS[lang];
    if (type === 'important') return { cls: 'chip-important', label: s.important };
    if (type === 'success') return { cls: 'chip-success', label: s.success };
    return { cls: 'chip-info', label: s.info };
  }

  function render(items) {
    const s = STRINGS[lang];
    list.innerHTML = '';

    if (!items.length) {
      list.innerHTML =
        `<div style="text-align:center;padding:40px;color:var(--muted)">${s.empty}</div>`;
      return;
    }

    items.forEach(a => {
      const chip = mapType(a.type || 'info');
      const date = a.date ? new Date(a.date) : null;
      const formattedDate = date
        ? date.toLocaleDateString([], { dateStyle: 'medium' })
        : '';

      const initials = (a.author || 'VH')
        .split(' ')
        .map(n => n[0])
        .join('')
        .substring(0, 2)
        .toUpperCase();

      const card = document.createElement('article');
      card.className = 'announce-card' + (a.featured ? ' featured' : '');
      card.innerHTML = `
        <div class="announce-header">
          <div class="announce-title">${a.title}</div>
          <span class="chip-status ${chip.cls}">${chip.label}</span>
        </div>
        <div class="meta">
          ${formattedDate ? `<span>📅 ${formattedDate}</span>` : ''}
          <span>👥 ${a.audience || ''}</span>
        </div>
        <div class="announce-content">${a.body}</div>
        <div class="announce-footer">
          <div class="author">
            <div class="avatar">${initials}</div>
            <div class="author-info">
              <div class="author-name">${a.author || 'System'}</div>
              <div class="author-role">${a.author_role || ''}</div>
            </div>
          </div>
        </div>
      `;
      list.appendChild(card);
    });
  }

  function filter() {
    const q = (searchInput.value || '').trim().toLowerCase();
    return announcements.filter(a => {
      if (!q) return true;
      return (
        (a.title && a.title.toLowerCase().includes(q)) ||
        (a.body && a.body.toLowerCase().includes(q))
      );
    });
  }

  // Theme toggle
  if (themeToggle) {
    themeToggle.addEventListener('click', () => {
      const html = document.documentElement;
      const current = html.getAttribute('data-theme') || 'dark';
      const next = current === 'dark' ? 'light' : 'dark';
      html.setAttribute('data-theme', next);
      themeToggle.textContent = next === 'dark' ? '🌙' : '☀️';
    });
  }

  // Lang toggle
  if (langToggle) {
    langToggle.addEventListener('click', () => {
      lang = (lang === 'en') ? 'ar' : 'en';
      document.documentElement.setAttribute('dir', lang === 'ar' ? 'rtl' : 'ltr');
      applyI18n();
      render(filter());
    });
  }

  if (searchInput) {
    searchInput.addEventListener('input', () => {
      render(filter());
    });
  }

  // Init
  applyI18n();
  render(announcements);
})();
