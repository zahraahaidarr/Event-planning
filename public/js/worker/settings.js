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
    announcements:"التعميمات",
    chat:"المحادثة",
    profile:"الملف الشخصي",
    settings:"الإعدادات",
    pageTitle:"الإعدادات",
    pageSubtitle:"إدارة تفضيلات حسابك والإشعارات.",
    search:"ابحث في الإعدادات..."
  }
};

let lang = 'en';
const $ = (sel) => document.querySelector(sel);

function i18nApply(){
  const s = STRINGS[lang];
  document.documentElement.dir = (lang==='ar') ? 'rtl' : 'ltr';
  $('#brandName') && ($('#brandName').textContent = s.brand);
  $('#navDashboard') && ($('#navDashboard').textContent = s.dashboard);
  $('#navDiscover') && ($('#navDiscover').textContent = s.discover);
  $('#navMyRes') && ($('#navMyRes').textContent = s.myRes);
  $('#navSubmissions') && ($('#navSubmissions').textContent = s.submissions);
  $('#navAnnouncements') && ($('#navAnnouncements').textContent = s.announcements);
  $('#navChat') && ($('#navChat').textContent = s.chat);
  $('#navProfile') && ($('#navProfile').textContent = s.profile);
  $('#navSettings') && ($('#navSettings').textContent = s.settings);
  $('#pageTitle') && ($('#pageTitle').textContent = s.pageTitle);
  $('#pageSubtitle') && ($('#pageSubtitle').textContent = s.pageSubtitle);
  $('#globalSearch') && ($('#globalSearch').placeholder = s.search);
}

// ----- Theme toggle -----
const themeBtn = $('#themeToggle');
if (themeBtn){
  themeBtn.addEventListener('click', ()=>{
    const isLight = document.documentElement.getAttribute('data-theme')==='light'
                 || document.body.getAttribute('data-theme')==='light';
    document.documentElement.setAttribute('data-theme', isLight ? 'dark' : 'light');
    document.body.setAttribute('data-theme', isLight ? 'dark' : 'light');
  });
}

// ----- Language toggle -----
const langBtn = $('#langToggle');
if (langBtn){
  langBtn.addEventListener('click', ()=>{
    lang = (lang==='en') ? 'ar' : 'en';
    i18nApply();
  });
}

// ----- Toggle switches -----
document.querySelectorAll('[data-toggle]').forEach(el=>{
  el.addEventListener('click', ()=> el.classList.toggle('active'));
});

// ----- Optional: search filter inside settings -----
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

// Initialize texts
i18nApply();
