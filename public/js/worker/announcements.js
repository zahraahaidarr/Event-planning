// i18n
const STRINGS = {
  en: {
    brand:"Volunteer", dashboard:"Dashboard", discover:"Discover Events",
    myRes:"My Reservations", submissions:"Post-Event Submissions",
    announcements:"Announcements", chat:"Chat", profile:"Profile", settings:"Settings",
    pageTitle:"Announcements",
    pageSubtitle:"Important updates and news for the volunteer community.",
    search:"Search announcements…", important:"Important", info:"Info", success:"Success",
    readMore:"Read More", views:"views"
  },
  ar: {
    brand:"متطوّع", dashboard:"لوحة التحكم", discover:"استكشف الفعاليات",
    myRes:"حجوزاتي", submissions:"تقارير ما بعد الحدث",
    announcements:"التعميمات", chat:"المحادثة", profile:"الملف الشخصي", settings:"الإعدادات",
    pageTitle:"التعميمات",
    pageSubtitle:"التحديثات والأخبار المهمة لمجتمع المتطوعين.",
    search:"ابحث في التعميمات…", important:"مهم", info:"معلومة", success:"نجاح",
    readMore:"اقرأ المزيد", views:"مشاهدة"
  }
};
let lang = 'en';

// demo data
const announcements = [
  { id:1, title:"New Volunteer Safety Guidelines", type:"important", date:"2024-12-15", views:1247, content:"We've updated our volunteer safety guidelines to ensure everyone's wellbeing during events. All volunteers are required to review the new guidelines before participating in upcoming events.", author:"Ahmed Al-Rashid", role:"Event Coordinator", featured:true },
  { id:2, title:"1000+ Volunteers Milestone Reached!", type:"success",  date:"2024-12-10", views:892,  content:"We're thrilled to announce that our volunteer community has grown to over 1,000 active members! This incredible milestone represents thousands of hours dedicated to making our community better.", author:"Fatima Hassan", role:"Volunteer Manager", featured:false },
  { id:3, title:"New Event Categories Available",   type:"info",     date:"2024-12-05", views:654,  content:"We've added new event categories to help you find volunteer opportunities that match your interests. The new categories include Technology & Innovation, Arts & Culture, and Animal Welfare.", author:"Mohammed Ali", role:"Event Coordinator", featured:false },
  { id:4, title:"Holiday Season Event Schedule",     type:"info",     date:"2024-12-01", views:1103, content:"Check out our special holiday season volunteer events! We have exciting opportunities including winter charity drives, community celebrations, and year-end cleanup initiatives.", author:"Ahmed Al-Rashid", role:"Event Coordinator", featured:false },
];

const $ = (sel) => document.querySelector(sel);
const list = $('#announceList');

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
  $('#globalSearch') && $('#globalSearch').setAttribute('placeholder', s.search);
}

function typeChip(type){
  const s = STRINGS[lang];
  const map = {
    important: {cls:'chip-important', label: s.important},
    info:      {cls:'chip-info',      label: s.info},
    success:   {cls:'chip-success',   label: s.success},
  };
  return map[type];
}

function renderAnnouncements(rows){
  list.innerHTML = '';
  if(!rows.length){
    list.innerHTML = '<div style="text-align:center;padding:40px;color:var(--muted)">No announcements found.</div>';
    return;
  }
  rows.forEach(a=>{
    const chip = typeChip(a.type);
    const initials = a.author.split(' ').map(n=>n[0]).join('');
    const card = document.createElement('article');
    card.className = 'announce-card' + (a.featured?' featured':'');
    card.innerHTML = `
      <div class="announce-header">
        <div class="announce-title">${a.title}</div>
        <span class="chip-status ${chip.cls}">${chip.label}</span>
      </div>
      <div class="meta">
        <span>📅 ${new Date(a.date).toLocaleDateString([], {dateStyle:'medium'})}</span>
        <span>👁️ ${a.views} ${STRINGS[lang].views}</span>
      </div>
      <div class="announce-content">${a.content}</div>
      <div class="announce-footer">
        <div class="author">
          <div class="avatar">${initials}</div>
          <div class="author-info">
            <div class="author-name">${a.author}</div>
            <div class="author-role">${a.role}</div>
          </div>
        </div>
        <button class="btn small" data-act="read" data-id="${a.id}">${STRINGS[lang].readMore}</button>
      </div>
    `;
    list.appendChild(card);
  });
}

function filterAnnouncements(){
  const q = ($('#globalSearch')?.value || '').trim().toLowerCase();
  return announcements.filter(a => !q || a.title.toLowerCase().includes(q) || a.content.toLowerCase().includes(q));
}

function bindActions(){
  list.addEventListener('click', (e)=>{
    const b = e.target.closest('button[data-act="read"]');
    if(!b) return;
    toast('Full announcement would open here.');
  });
}

function toast(msg){
  let box = document.getElementById('toastContainer');
  if(!box){
    box = document.createElement('div'); box.id='toastContainer'; document.body.appendChild(box);
    Object.assign(box.style,{ position:'fixed', left:'50%', transform:'translateX(-50%)', bottom:'24px', zIndex:9999 });
  }
  const t = document.createElement('div');
  Object.assign(t.style,{ background:'rgba(0,0,0,.7)', color:'#fff', padding:'10px 14px', borderRadius:'10px', marginTop:'8px' });
  t.textContent = msg; box.appendChild(t);
  setTimeout(()=> t.remove(), 2200);
}

/* --- Theme & Language --- */
document.getElementById('themeToggle')?.addEventListener('click', ()=>{
  const root = document.documentElement;
  const next = root.getAttribute('data-theme') === 'light' ? 'dark' : 'light';
  root.setAttribute('data-theme', next);
  document.body.setAttribute('data-theme', next); // optional mirror for legacy styles
});
document.getElementById('langToggle')?.addEventListener('click', ()=>{
  lang = (lang==='en') ? 'ar' : 'en';
  i18nApply();
  renderAnnouncements(filterAnnouncements());
});
document.getElementById('globalSearch')?.addEventListener('input', ()=> renderAnnouncements(filterAnnouncements()));

/* Init */
i18nApply();
renderAnnouncements(announcements);
bindActions();
