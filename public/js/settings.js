// public/js/worker/settings.js

// ----- i18n strings -----
const STRINGS = {
  en: {
    brand:"Volunteer",
    dashboard:"Dashboard",
    discover:"Discover Events",
    myRes:"My Reservations",
    submissions:"Post-Event Submissions",
    announcements:"Announcements",
    chat:"Chat",
    profile:"Profile",
    settings:"Settings",
    pageTitle:"Settings",
    pageSubtitle:"Manage your account preferences and notifications.",
    search:"Search settings..."
  },
  ar: {
    brand:"متطوّع",
    dashboard:"لوحة التحكم",
    discover:"استكشف الفعاليات",
    myRes:"حجوزاتي",
    submissions:"تقارير ما بعد الحدث",
    announcements:"التعاميم",
    chat:"المحادثة",
    profile:"الملف الشخصي",
    settings:"الإعدادات",
    pageTitle:"الإعدادات",
    pageSubtitle:"إدارة تفضيلات حسابك والإشعارات.",
    search:"ابحث في الإعدادات..."
  }
};

const $ = (sel) => document.querySelector(sel);

// ===== Load saved language/theme from localStorage =====
let lang = window.vhLang || localStorage.getItem('vh_lang')
  || (document.documentElement.lang === 'ar' ? 'ar' : 'en');

function applyInitialTheme() {
  const savedTheme =
    localStorage.getItem('vh_theme') ||
    document.body.getAttribute('data-theme') ||
    document.documentElement.getAttribute('data-theme') ||
    'dark';

  document.documentElement.setAttribute('data-theme', savedTheme);
  document.body.setAttribute('data-theme', savedTheme);
}

// ----- Apply language to UI -----
function i18nApply(){
  const s = STRINGS[lang];
  document.documentElement.dir = (lang==='ar') ? 'rtl' : 'ltr';
  document.documentElement.lang = lang;

  $('#brandName')       && ($('#brandName').textContent = s.brand);
  $('#navDashboard')    && ($('#navDashboard').textContent = s.dashboard);
  $('#navDiscover')     && ($('#navDiscover').textContent = s.discover);
  $('#navMyRes')        && ($('#navMyRes').textContent = s.myRes);
  $('#navSubmissions')  && ($('#navSubmissions').textContent = s.submissions);
  $('#navAnnouncements')&& ($('#navAnnouncements').textContent = s.announcements);
  $('#navChat')         && ($('#navChat').textContent = s.chat);
  $('#navProfile')      && ($('#navProfile').textContent = s.profile);
  $('#navSettings')     && ($('#navSettings').textContent = s.settings);
  $('#pageTitle')       && ($('#pageTitle').textContent = s.pageTitle);
  $('#pageSubtitle')    && ($('#pageSubtitle').textContent = s.pageSubtitle);
  $('#globalSearch')    && ($('#globalSearch').placeholder = s.search);
}

// ===== Backend save helper =====
async function saveSettings(payload) {
  if (!window.WORKER_SETTINGS_UPDATE_URL) return;

  try {
    await fetch(window.WORKER_SETTINGS_UPDATE_URL, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': window.CSRF_TOKEN,
        'Accept': 'application/json',
      },
      body: JSON.stringify({ settings: payload }),
    });
  } catch (e) {
    console.error('Failed to save settings', e);
  }
}

// ----- Theme toggle -----
function toggleTheme(){
  const current =
    document.body.getAttribute('data-theme') ||
    document.documentElement.getAttribute('data-theme') ||
    'dark';

  const next = current === 'light' ? 'dark' : 'light';

  document.documentElement.setAttribute('data-theme', next);
  document.body.setAttribute('data-theme', next);

  // persist in localStorage
  localStorage.setItem('vh_theme', next);

  // optionally persist in DB
  saveSettings({ ui_theme: next });
}

// ----- Language toggle -----
function toggleLang(){
  lang = (lang === 'en') ? 'ar' : 'en';

  // persist in localStorage
  localStorage.setItem('vh_lang', lang);

  // update global
  window.vhLang = lang;

  i18nApply();

  // optionally persist in DB
  saveSettings({ ui_language: lang });
}


// ===== Wire buttons =====
const themeBtn         = $('#themeToggle');
const themeBtnSecondary= $('#themeToggleSecondary');
themeBtn         && themeBtn.addEventListener('click', toggleTheme);
themeBtnSecondary&& themeBtnSecondary.addEventListener('click', toggleTheme);

const langBtn          = $('#langToggle');
const langBtnSecondary = $('#langToggleSecondary');
langBtn          && langBtn.addEventListener('click', toggleLang);
langBtnSecondary && langBtnSecondary.addEventListener('click', toggleLang);

// ----- Notification toggle switches -----
// IMPORTANT: only ONE listener per [data-toggle] (you had two before)
document.querySelectorAll('[data-toggle]').forEach(el => {
  el.addEventListener('click', () => {
    el.classList.toggle('active');

    // collect all toggle states and send to backend
    const payload = {};
    document.querySelectorAll('[data-toggle]').forEach(t => {
      const key = t.getAttribute('data-setting');
      if (!key) return;
      payload[key] = t.classList.contains('active') ? '1' : '0';
    });

    saveSettings(payload);
  });
});

// ----- Search filter inside settings -----
const searchInput = $('#globalSearch');
if (searchInput){
  searchInput.addEventListener('input', (e)=>{
    const q = e.target.value.toLowerCase().trim();
    document.querySelectorAll('.setting-item').forEach(item=>{
      const txt = item.innerText.toLowerCase();
      item.style.display = txt.includes(q) ? '' : 'none';
    });
  });
}

// ===== Initialize on page load =====
applyInitialTheme();   // apply saved theme
i18nApply();           // apply saved language
