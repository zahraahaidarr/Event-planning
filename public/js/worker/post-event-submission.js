// i18n strings
const STRINGS = {
  en: {
    brand:"Volunteer", dashboard:"Dashboard", discover:"Discover Events",
    myRes:"My Reservations", submissions:"Post-Event Submissions",
    announcements:"Announcements", chat:"Chat", profile:"Profile", settings:"Settings",
    pageTitle:"Post-Event Submissions",
    pageSubtitle:"Submit your post-event reports within 24 hours of event completion. Include photos, videos, and detailed descriptions.",
    search:"Search submissions…",
    submitted:"Submitted", pending:"Pending Review",
    viewReport:"View Report"
  },
  ar: {
    brand:"متطوّع", dashboard:"لوحة التحكم", discover:"استكشف الفعاليات",
    myRes:"حجوزاتي", submissions:"تقارير ما بعد الحدث",
    announcements:"التعميمات", chat:"المحادثة", profile:"الملف الشخصي", settings:"الإعدادات",
    pageTitle:"تقارير ما بعد الحدث",
    pageSubtitle:"قدّم تقاريرك بعد الفعالية خلال 24 ساعة من انتهائها. أضف الصور والفيديوهات والوصف التفصيلي.",
    search:"ابحث في التقارير…",
    submitted:"تم التقديم", pending:"قيد المراجعة",
    viewReport:"عرض التقرير"
  }
};
let lang = 'en';

const $ = (sel) => document.querySelector(sel);
const list = $('#submissionsList');

const submissions = [
  { id:1, event:"Summer Marathon", date:"2025-09-16T10:00:00", status:"submitted" },
  { id:2, event:"Neighborhood Reading Day", date:"2025-09-05T14:00:00", status:"pending" },
];

function i18nApply(){
  const s = STRINGS[lang];
  document.documentElement.dir = (lang==='ar') ? 'rtl' : 'ltr';
  $('#brandName').textContent = s.brand;
  $('#navDashboard').textContent = s.dashboard;
  $('#navDiscover').textContent = s.discover;
  $('#navMyRes').textContent = s.myRes;
  $('#navSubmissions').textContent = s.submissions;
  $('#navAnnouncements').textContent = s.announcements;
  $('#navChat').textContent = s.chat;
  $('#navProfile').textContent = s.profile;
  $('#navSettings').textContent = s.settings;
  $('#pageTitle').textContent = s.pageTitle;
  $('#pageSubtitle').textContent = s.pageSubtitle;
  $('#globalSearch').placeholder = s.search;
}

function renderSubmissions(){
  list.innerHTML = '';
  if(submissions.length === 0){
    list.innerHTML = '<div style="text-align:center;padding:40px;color:var(--muted)">No submissions yet.</div>';
    return;
  }
  submissions.forEach(sub=>{
    const s = STRINGS[lang];
    const chip = sub.status==='submitted' ? {cls:'chip-submitted', label:s.submitted} : {cls:'chip-pending', label:s.pending};
    const card = document.createElement('article');
    card.className = 'card';
    card.innerHTML = `
      <div class="card-header">
        <div class="card-title">${sub.event}</div>
        <span class="chip-status ${chip.cls}">${chip.label}</span>
      </div>
      <div class="meta">
        <span>📅 Submitted: ${new Date(sub.date).toLocaleString([], {dateStyle:'medium', timeStyle:'short'})}</span>
      </div>
      <div>
        <button class="btn small ghost" data-act="view" data-id="${sub.id}">${s.viewReport}</button>
      </div>
    `;
    list.appendChild(card);
  });
}

/* ---------- Role form logic ---------- */
const roleSelect = document.getElementById('roleSelect');
const roleForms = document.getElementById('roleForms');

function showRoleForm(role){
  roleForms.querySelectorAll('.role-set').forEach(fs => {
    fs.style.display = (fs.getAttribute('data-role') === role) ? 'block' : 'none';
  });
}
roleSelect.addEventListener('change', e => showRoleForm(e.target.value));

/* Civil Defense cases dynamic rows */
const cdCasesList = document.getElementById('cd_cases_list');
const cdAddBtn = document.getElementById('cd_add_case');
function addCivilCaseRow(){
  const wrap = document.createElement('div');
  wrap.className = 'two-col';
  wrap.style.alignItems = 'end';
  wrap.style.border = '1px solid var(--border-color)';
  wrap.style.borderRadius = '10px';
  wrap.style.padding = '10px';
  wrap.style.marginBottom = '8px';

  wrap.innerHTML = `
    <div class="form-group">
      <label>Type of case</label>
      <select data-cd="type">
        <option value="injury">Injury</option>
        <option value="fainting">Fainting</option>
        <option value="panic">Panic attack</option>
        <option value="other">Other</option>
      </select>
    </div>
    <div class="form-group">
      <label>Age (optional)</label>
      <input type="number" min="0" data-cd="age" placeholder="e.g., 27">
    </div>
    <div class="form-group">
      <label>Gender (optional)</label>
      <select data-cd="gender">
        <option value="">—</option>
        <option>Male</option>
        <option>Female</option>
        <option>Other</option>
      </select>
    </div>
    <div class="form-group">
      <label>Action taken</label>
      <select data-cd="action">
        <option value="bandage">Bandage</option>
        <option value="on-site-care">On-site care</option>
        <option value="hospital-referral">Hospital referral</option>
        <option value="other">Other</option>
      </select>
    </div>
    <div class="form-group" style="grid-column:1/-1">
      <label>Notes (optional)</label>
      <input type="text" data-cd="notes" placeholder="Short description or context…">
    </div>
    <div style="grid-column:1/-1;display:flex;justify-content:flex-end">
      <button type="button" class="btn small ghost" data-remove>Remove</button>
    </div>
  `;
  wrap.querySelector('[data-remove]').addEventListener('click', ()=> wrap.remove());
  cdCasesList.appendChild(wrap);
}
if (cdAddBtn) cdAddBtn.addEventListener('click', addCivilCaseRow);

/* ---------- Helpers ---------- */
function getCivilCases(){
  const rows = [...cdCasesList.querySelectorAll('.two-col')];
  return rows.map(r => ({
    type: r.querySelector('[data-cd="type"]')?.value || '',
    age: r.querySelector('[data-cd="age"]')?.value || '',
    gender: r.querySelector('[data-cd="gender"]')?.value || '',
    action: r.querySelector('[data-cd="action"]')?.value || '',
    notes: r.querySelector('[data-cd="notes"]')?.value || ''
  }));
}
function filesToList(input){
  if (!input) return [];
  return [...(input.files || [])].map(f => ({ name:f.name, size:f.size, type:f.type }));
}

/* ---------- Submit ---------- */
function bindForm(){
  document.getElementById('reportForm').addEventListener('submit', (e)=>{
    e.preventDefault();

    const eventSel = document.getElementById('eventSelect');
    const role = document.getElementById('roleSelect').value;

    const payload = {
      // If you wire to Laravel later, include the CSRF token (meta or hidden input)
      eventId: eventSel.value,
      eventName: eventSel.options[eventSel.selectedIndex]?.text || '',
      role,
      generalNotes: document.getElementById('reportText')?.value?.trim?.() || '',
      additionalMedia: filesToList(document.getElementById('mediaUpload')),
      data: {}
    };

    switch(role){
      case 'organizer':
        payload.data = {
          attendees: +document.getElementById('org_attendees').value || 0,
          noShows: document.getElementById('org_noshows').value.trim(),
          issues: document.getElementById('org_issues').value.trim(),
          improvements: document.getElementById('org_improve').value.trim()
        };
        break;
      case 'civil':
        payload.data = {
          attendanceState: document.getElementById('cd_check').value,
          totalCases: +document.getElementById('cd_total_cases').value || 0,
          cases: getCivilCases(),
          concerns: document.getElementById('cd_concerns').value.trim(),
          forms: filesToList(document.getElementById('cd_forms'))
        };
        break;
      case 'media':
        payload.data = {
          files: filesToList(document.getElementById('media_files')),
          labels: document.getElementById('media_labels').value.trim(),
          photosCount: +document.getElementById('media_report_photos').value || 0,
          videosCount: +document.getElementById('media_report_videos').value || 0,
          problems: document.getElementById('media_problems').value.trim(),
          captions: document.getElementById('media_captions').value.trim()
        };
        break;
      case 'tech':
        payload.data = {
          allOk: document.getElementById('tech_ok').value,
          returned: document.getElementById('tech_returned').value,
          issues: document.getElementById('tech_issues').value.trim(),
          recording: filesToList(document.getElementById('tech_recording')),
          improvements: document.getElementById('tech_suggest').value.trim()
        };
        break;
      case 'cleaner':
        payload.data = {
          zones: +document.getElementById('clean_zones').value || 0,
          extraHelp: document.getElementById('clean_extra').value,
          notes: document.getElementById('clean_notes').value.trim(),
          suggestions: document.getElementById('clean_suggest').value.trim()
        };
        break;
      case 'decorator':
        payload.data = {
          photos: filesToList(document.getElementById('dec_photos')),
          used: document.getElementById('dec_used').value.trim(),
          damaged: document.getElementById('dec_damaged').value.trim(),
          replace: document.getElementById('dec_replace').value.trim(),
          feedback: document.getElementById('dec_feedback').value.trim()
        };
        break;
      case 'cooking':
        payload.data = {
          meals: document.getElementById('cook_meals').value.trim(),
          ingredients: document.getElementById('cook_ingredients').value.trim(),
          leftovers: document.getElementById('cook_leftovers').value.trim(),
          hygiene: document.getElementById('cook_hygiene').value.trim(),
          photos: filesToList(document.getElementById('cook_photos'))
        };
        break;
      case 'waiter':
        payload.data = {
          attendance: document.getElementById('wait_attendance').value,
          itemsServed: document.getElementById('wait_items').value.trim(),
          serviceIssues: document.getElementById('wait_issues').value.trim(),
          leftovers: document.getElementById('wait_leftovers').value.trim()
        };
        break;
    }

    // TODO: POST to your Laravel controller/route if needed
    console.log('Post-event submission payload:', payload);

    submissions.unshift({
      id: Date.now(),
      event: payload.eventName,
      date: new Date().toISOString(),
      status: 'pending'
    });

    toast('Report submitted successfully!');
    e.target.reset();
    showRoleForm(''); // hide role forms
    renderSubmissions();
  });
}

function bindActions(){
  list.addEventListener('click', (e)=>{
    const b = e.target.closest('button[data-act]');
    if(!b) return;
    toast('Report details would open here.');
  });
}

function toast(msg){
  let box = document.getElementById('toastContainer');
  if(!box){ box = document.createElement('div'); box.id='toastContainer'; document.body.appendChild(box); Object.assign(box.style,{
    position:'fixed', left:'50%', transform:'translateX(-50%)', bottom:'24px', zIndex:9999
  }); }
  const t = document.createElement('div');
  Object.assign(t.style,{ background:'rgba(0,0,0,.7)', color:'#fff', padding:'10px 14px', borderRadius:'10px', marginTop:'8px' });
  t.textContent = msg; box.appendChild(t);
  setTimeout(()=>{ t.remove(); }, 2200);
}

// Theme / Language
document.getElementById('themeToggle').addEventListener('click', ()=>{
  const isLight = document.body.getAttribute('data-theme')==='light';
  document.body.setAttribute('data-theme', isLight? 'dark':'light');
});
document.getElementById('langToggle').addEventListener('click', ()=>{
  lang = (lang==='en') ? 'ar' : 'en';
  i18nApply(); renderSubmissions();
});

// Init
i18nApply();
renderSubmissions();
bindForm();
bindActions();
