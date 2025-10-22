// ===== i18n =====
const STRINGS = {
  en:{brand:"Volunteer",dashboard:"Dashboard",discover:"Discover Events",myRes:"My Reservations",submissions:"Post-Event Submissions",
      announcements:"Announcements",chat:"Chat",profile:"Profile",welcome:"Welcome back",
      subtitle:"Browse events that match your role and location, track your reservations, and submit post-event reports on time.",
      upcoming:"Upcoming Events",accepted:"Accepted Reservations",pending:"Pending Requests",hours:"Hours Volunteered",
      recommended:"Recommended for You",type:"Type",category:"Category",location:"Location",search:"Search events…",
      nextEvent:"Next Event",acceptedChip:"Accepted",pendingChip:"Pending",rejectedChip:"Rejected",waitingChip:"Waiting",
      findEvents:"Find Events",viewAnnouncements:"View Announcements"},
  ar:{brand:"متطوّع",dashboard:"لوحة التحكم",discover:"استكشف الفعاليات",myRes:"حجوزاتي",submissions:"تقارير ما بعد الحدث",
      announcements:"التعميمات",chat:"المحادثة",profile:"الملف الشخصي",welcome:"مرحباً بعودتك",
      subtitle:"استعرض الفعاليات وفق دورك وموقعك، وتتبع الحجوزات وقدّم التقارير في الوقت.",
      upcoming:"فعاليات قادمة",accepted:"حجوزات مقبولة",pending:"طلبات قيد المراجعة",hours:"ساعات التطوع",
      recommended:"مقترحة لك",type:"النوع",category:"الفئة",location:"الموقع",search:"ابحث عن فعالية…",
      nextEvent:"الفعالية القادمة",acceptedChip:"مقبول",pendingChip:"قيد الانتظار",rejectedChip:"مرفوض",waitingChip:"قائمة انتظار",
      findEvents:"اعثر على فعاليات",viewAnnouncements:"عرض التعميمات"}
};
let lang='en';

// ===== Mock data =====
const me={name:"Fatima",role:"Media Staff",city:"Beirut",hoursYTD:62};
const events=[
  {id:1,title:"Community Cleanup – Waterfront",type:"Community",category:"Cleaner",city:"Beirut",date:"2025-10-12T14:00:00",slots:{Cleaner:[2,6]},status:"upcoming",my:"pending"},
  {id:2,title:"Health Awareness Fair",type:"Health",category:"Media Staff",city:"Beirut",date:"2025-10-08T10:00:00",slots:{"Media Staff":[1,4]},status:"upcoming",my:"accepted"},
  {id:3,title:"STEM Day at School",type:"Education",category:"Tech Support",city:"Jounieh",date:"2025-10-20T09:00:00",slots:{"Tech Support":[0,3]},status:"upcoming",my:"none"},
  {id:4,title:"Food Drive – Old Souks",type:"Community",category:"Organizer",city:"Tripoli",date:"2025-10-28T16:00:00",slots:{"Organizer":[3,5]},status:"upcoming",my:"waiting"}
];
const activities=[
  {text:"Your reservation for “Health Awareness Fair” was accepted.",time:"2h"},
  {text:"You submitted a media report for “Summer Marathon”.",time:"1d"},
  {text:"Reminder: Submit report for “Neighborhood Reading Day”.",time:"3d"}
];
const announcements=[
  {text:"New event near Beirut this weekend. Media Staff needed.",time:"1h"},
  {text:"Post-event submission deadline moved to 48h after event.",time:"Yesterday"}
];

// ===== Helpers =====
const $=s=>document.querySelector(s);
const grid=$('#eventGrid');

function i18nApply(){
  const s=STRINGS[lang];
  document.documentElement.dir=(lang==='ar')?'rtl':'ltr';
  $('#brandName').textContent=s.brand;
  $('#navDashboard').textContent=s.dashboard;
  $('#navDiscover').textContent=s.discover;
  $('#navMyRes').textContent=s.myRes;
  $('#navSubmissions').textContent=s.submissions;
  $('#navAnnouncements').textContent=s.announcements;
  $('#navChat').textContent=s.chat;
  $('#navProfile').textContent=s.profile;
  $('#heroTitle').firstChild.textContent=s.welcome+", ";
  $('#heroSubtitle').textContent=s.subtitle;
  $('#kpi1Label').textContent=s.upcoming;
  $('#kpi2Label').textContent=s.accepted;
  $('#kpi3Label').textContent=s.pending;
  $('#kpi4Label').textContent=s.hours;
  $('#sectionTitle').textContent=s.recommended;
  $('#globalSearch').placeholder=s.search;
  $('#nextEventPanel strong').textContent=s.nextEvent;
  $('#ctaDiscover').textContent=s.discover;
  $('#ctaAnnouncements').textContent=s.viewAnnouncements;
  // selects
  $('#filterType').options[0].text=s.type;
  $('#filterCategory').options[0].text=s.category;
  $('#filterLocation').placeholder=s.location;
}

function hydrateHeader(){
  $('#volName').textContent=me.name;
  $('#railName').textContent=me.name;
  $('#railRole').textContent=(lang==='ar'?'الدور: ':'Role: ')+me.role;
  $('#railLocation').textContent=me.city;
}

function statCounts(){
  const now=new Date();
  const upcoming=events.filter(e=>new Date(e.date)>now).length;
  const accepted=events.filter(e=>e.my==='accepted').length;
  const pending=events.filter(e=>e.my==='pending'||e.my==='waiting').length;
  $('#kpiUpcoming').textContent=upcoming;
  $('#kpiAccepted').textContent=accepted;
  $('#kpiPending').textContent=pending;
  $('#kpiHours').textContent=me.hoursYTD+'h';
}

function statusChip(status){
  const map={
    pending:{cls:'chip-pending',label:STRINGS[lang].pendingChip},
    accepted:{cls:'chip-accepted',label:STRINGS[lang].acceptedChip},
    rejected:{cls:'chip-rejected',label:STRINGS[lang].rejectedChip},
    waiting:{cls:'chip-pending',label:STRINGS[lang].waitingChip},
    none:null
  };
  return map[status];
}

function actionButtons(e){
  if(e.my==='accepted'){
    return `<button class="btn small" data-act="view" data-id="${e.id}">View</button>
            <button class="btn small ghost" data-act="cancel" data-id="${e.id}">Cancel</button>`;
  }
  if(e.my==='pending'||e.my==='waiting'){
    return `<button class="btn small ghost" data-act="withdraw" data-id="${e.id}">Withdraw</button>`;
  }
  return `<button class="btn small" data-act="apply" data-id="${e.id}">Apply / Reserve</button>`;
}

function renderEvents(list){
  grid.innerHTML='';
  list.forEach(e=>{
    const chip=statusChip(e.my);
    const role=Object.keys(e.slots)[0];
    const [filled,total]=e.slots[role];
    const card=document.createElement('article');
    card.className='card';
    card.innerHTML=`
      <div class="card-banner">
        ${chip?`<span class="chip chip-status ${chip.cls}">${chip.label}</span>`:''}
      </div>
      <div class="card-body">
        <div class="card-title">${e.title}</div>
        <div class="meta">
          <span>📍 ${e.city}</span>
          <span>🗓️ ${new Date(e.date).toLocaleString([], {dateStyle:'medium', timeStyle:'short'})}</span>
          <span>🏷️ ${e.type} • ${e.category}</span>
        </div>
        <div class="slots">Slots (${e.category}): ${filled}/${total}</div>
        <div class="actions">${actionButtons(e)}</div>
      </div>`;
    grid.appendChild(card);
  });
}

function bindGridActions(){
  grid.addEventListener('click', e=>{
    const b=e.target.closest('button[data-act]'); if(!b) return;
    const id=+b.dataset.id, act=b.dataset.act;
    const ev=events.find(x=>x.id===id);
    if(act==='apply'){ev.my='pending'; toast('Reservation submitted and pending review.');}
    if(act==='withdraw'){ev.my='none'; toast('Reservation withdrawn.');}
    if(act==='cancel'){ev.my='none'; toast('Reservation cancelled before deadline.');}
    renderEvents(filterApply()); statCounts(); hydrateNextEvent();
  });
}

function hydrateRailLists(){
  const actUl=$('#activityList'); actUl.innerHTML='';
  activities.forEach(a=>{
    const d=document.createElement('div'); d.className='activity-item';
    d.innerHTML=`<div>🟣</div><div><div>${a.text}</div><div class="time">${a.time}</div></div>`;
    actUl.appendChild(d);
  });
  const ann=$('#announceList'); ann.innerHTML='';
  announcements.forEach(a=>{
    const d=document.createElement('div'); d.className='announce-item';
    d.innerHTML=`<div>📣</div><div><div>${a.text}</div><div class="time">${a.time}</div></div>`;
    ann.appendChild(d);
  });
}

function hydrateNextEvent(){
  const next=events.filter(e=>e.my==='accepted').sort((a,b)=>new Date(a.date)-new Date(b.date))[0];
  if(!next){
    $('#nextEventTitle').textContent='—';
    $('#nextEventMeta').textContent='No accepted reservations yet.';
    $('#nextEventStatus').textContent=STRINGS[lang].pendingChip;
    $('#nextEventStatus').className='chip-status chip-pending';
    return;
  }
  $('#nextEventTitle').textContent=next.title;
  $('#nextEventMeta').textContent=`${next.city} • ${new Date(next.date).toLocaleString()}`;
  $('#nextEventStatus').textContent=STRINGS[lang].acceptedChip;
  $('#nextEventStatus').className='chip-status chip-accepted';
}

function filterApply(){
  const t=$('#filterType').value, c=$('#filterCategory').value, d=$('#filterDate').value, loc=$('#filterLocation').value.trim().toLowerCase();
  return events.filter(e=>{
    const okT=!t||e.type===t, okC=!c||e.category===c, okD=!d||e.date.slice(0,10)===d, okL=!loc||e.city.toLowerCase().includes(loc);
    return okT&&okC&&okD&&okL;
  });
}

function bindFilters(){
  ['#filterType','#filterCategory','#filterDate','#filterLocation','#globalSearch'].forEach(sel=>{
    $(sel).addEventListener('input', ()=>{
      const q=$('#globalSearch').value.trim().toLowerCase();
      const list=filterApply().filter(e=> !q || e.title.toLowerCase().includes(q) || e.city.toLowerCase().includes(q) || e.category.toLowerCase().includes(q));
      renderEvents(list);
    });
  });
}

function toast(msg){
  let box=document.getElementById('toastContainer');
  if(!box){
    box=document.createElement('div'); box.id='toastContainer'; document.body.appendChild(box);
    Object.assign(box.style,{position:'fixed',left:'50%',transform:'translateX(-50%)',bottom:'24px',zIndex:9999});
  }
  const t=document.createElement('div');
  Object.assign(t.style,{background:'rgba(0,0,0,.7)',color:'#fff',padding:'10px 14px',borderRadius:'10px',marginTop:'8px'});
  t.textContent=msg; box.appendChild(t); setTimeout(()=>t.remove(),2200);
}

// Theme & language
$('#themeToggle').addEventListener('click', ()=>{
  const isLight=document.body.getAttribute('data-theme')==='light';
  document.body.setAttribute('data-theme', isLight? 'dark':'light');
});
$('#langToggle').addEventListener('click', ()=>{
  lang=(lang==='en')?'ar':'en';
  i18nApply(); hydrateHeader(); renderEvents(filterApply()); hydrateNextEvent();
});

// Quick buttons
$('#quickFindEvents').addEventListener('click', ()=> toast('Navigate: Discover Events'));
$('#ctaDiscover').addEventListener('click', ()=> toast('Navigate: Discover Events'));
$('#ctaAnnouncements').addEventListener('click', ()=> toast('Navigate: Announcements'));

// ===== Init =====
i18nApply(); hydrateHeader(); statCounts(); hydrateRailLists(); renderEvents(events); bindGridActions(); bindFilters(); hydrateNextEvent();
