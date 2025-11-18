// =========================
// i18n strings
// =========================
const STRINGS = {
  en: {
    brand:"Volunteer", dashboard:"Dashboard", discover:"Discover Events",
    myRes:"My Reservations", submissions:"Post-Event Submissions",
    announcements:"Announcements", chat:"Chat", profile:"Profile", settings:"Settings",
    pageTitle:"Post-Event Submissions",
    pageSubtitle:"Submit your post-event reports within 24 hours of event completion. Include photos, videos, and detailed descriptions.",
    search:"Search submissions…",
    submitted:"Submitted", pending:"Pending Review",
    viewReport:"View Report",
    noResults:"No submissions found.",
    noSubmissions:"No submissions yet.",
    chooseEventRole:"Please choose event and role.",
    submitOk:"Report submitted successfully!",
    submitFail:(st)=>`Failed to submit report (status ${st}).`,
    submitError:"Error submitting report."
  },
  ar: {
    brand:"متطوّع", dashboard:"لوحة التحكم", discover:"استكشف الفعاليات",
    myRes:"حجوزاتي", submissions:"تقارير ما بعد الحدث",
    announcements:"التعميمات", chat:"المحادثة", profile:"الملف الشخصي", settings:"الإعدادات",
    pageTitle:"تقارير ما بعد الحدث",
    pageSubtitle:"قدّم تقاريرك بعد الفعالية خلال 24 ساعة من انتهائها. أضف الصور والفيديوهات والوصف التفصيلي.",
    search:"ابحث في التقارير…",
    submitted:"تم التقديم", pending:"قيد المراجعة",
    viewReport:"عرض التقرير",
    noResults:"لا توجد تقارير مطابقة.",
    noSubmissions:"لا توجد تقارير حتى الآن.",
    chooseEventRole:"الرجاء اختيار الفعالية والدور.",
    submitOk:"تم إرسال التقرير بنجاح!",
    submitFail:(st)=>`فشل إرسال التقرير (رمز ${st}).`,
    submitError:"حدث خطأ أثناء إرسال التقرير."
  }
};

let lang = 'en';
const $  = sel => document.querySelector(sel);
const list = $('#submissionsList');

// =========================
// i18n on static elements
// =========================
function i18nApply(){
  const s = STRINGS[lang];
  document.documentElement.dir = (lang==='ar') ? 'rtl' : 'ltr';

  $('#brandName')        && ($('#brandName').textContent        = s.brand);
  $('#navDashboard')     && ($('#navDashboard').textContent     = s.dashboard);
  $('#navDiscover')      && ($('#navDiscover').textContent      = s.discover);
  $('#navMyRes')         && ($('#navMyRes').textContent         = s.myRes);
  $('#navSubmissions')   && ($('#navSubmissions').textContent   = s.submissions);
  $('#navAnnouncements') && ($('#navAnnouncements').textContent = s.announcements);
  $('#navChat')          && ($('#navChat').textContent          = s.chat);
  $('#navProfile')       && ($('#navProfile').textContent       = s.profile);
  $('#navSettings')      && ($('#navSettings').textContent      = s.settings);
  $('#pageTitle')        && ($('#pageTitle').textContent        = s.pageTitle);
  $('#pageSubtitle')     && ($('#pageSubtitle').textContent     = s.pageSubtitle);
  $('#globalSearch')     && ($('#globalSearch').placeholder     = s.search);

  // If you later add data-i18n attributes on chips/buttons you can translate them here.
}

// =========================
// Search over existing cards (DB-rendered)
// =========================
function bindSearch(){
  const input = $('#globalSearch');
  if (!input || !list) return;

  input.addEventListener('input', () => {
    const q = input.value.toLowerCase().trim();
    const cards = list.querySelectorAll('.card');
    let visible = 0;

    cards.forEach(card => {
      const txt = card.textContent.toLowerCase();
      const show = !q || txt.includes(q);
      card.style.display = show ? '' : 'none';
      if (show) visible++;
    });

    const msgId = 'submissionsEmptyMsg';
    let msg = document.getElementById(msgId);
    if (visible === 0) {
      if (!msg) {
        msg = document.createElement('div');
        msg.id = msgId;
        msg.style.textAlign = 'center';
        msg.style.padding = '40px';
        msg.style.color = 'var(--muted)';
        list.appendChild(msg);
      }
      msg.textContent = STRINGS[lang].noResults;
    } else if (msg) {
      msg.remove();
    }
  });
}

// =========================
// Role form logic
// =========================
const roleSelect = document.getElementById('roleSelect');
const roleForms  = document.getElementById('roleForms');

function showRoleForm(role){
  if (!roleForms) return;
  roleForms.querySelectorAll('.role-set').forEach(fs => {
    fs.style.display = (fs.getAttribute('data-role') === role) ? 'block' : 'none';
  });
}
if (roleSelect) {
  roleSelect.addEventListener('change', e => showRoleForm(e.target.value));
}

// =========================
// Civil Defense dynamic cases
// =========================
const cdCasesList = document.getElementById('cd_cases_list');
const cdAddBtn    = document.getElementById('cd_add_case');

function addCivilCaseRow(){
  if (!cdCasesList) return;

  const wrap = document.createElement('div');
  wrap.className = 'two-col';
  wrap.style.alignItems   = 'end';
  wrap.style.border       = '1px solid var(--border-color)';
  wrap.style.borderRadius = '10px';
  wrap.style.padding      = '10px';
  wrap.style.marginBottom = '8px';

  wrap.innerHTML = `
    <div class="form-group">
      <label>Type of case</label>
      <select data-cd="type">
      <option value="" disabled selected>Select type…</option>
        <option value="injury">Injury</option>
        <option value="fainting">Fainting</option>
        <option value="panic">Panic attack</option>
        <option value="other">Other</option>
      </select>
    </div>
    <div class="form-group">
      <label>Age </label>
      <input type="number" min="0" data-cd="age" placeholder="e.g., 27">
    </div>
    <div class="form-group">
      <label>Gender</label>
      <select data-cd="gender">
        <option value="" disabled selected>Gender…</option>
        <option>Male</option>
        <option>Female</option>
      </select>
    </div>
    <div class="form-group">
      <label>Action taken</label>
      <select data-cd="action">
      <option value="" disabled selected>Action taken…</option>
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

function getCivilCases(){
  if (!cdCasesList) return [];
  const rows = [...cdCasesList.querySelectorAll('.two-col')];
  return rows.map(r => ({
    type:   r.querySelector('[data-cd="type"]')?.value   || '',
    age:    r.querySelector('[data-cd="age"]')?.value    || '',
    gender: r.querySelector('[data-cd="gender"]')?.value || '',
    action: r.querySelector('[data-cd="action"]')?.value || '',
    notes:  r.querySelector('[data-cd="notes"]')?.value  || ''
  }));
}

// =========================
// Submit logic (AJAX + files)
// =========================
function bindForm(){
  const form = document.getElementById('reportForm');
  if (!form) return;

  const storeUrl = form.dataset.storeUrl || form.action;
  console.log('storeUrl =', storeUrl);

  const meta = document.querySelector('meta[name="csrf-token"]');
  const csrf = meta ? meta.getAttribute('content')
                    : (form.querySelector('input[name="_token"]')?.value || '');

  form.addEventListener('submit', async (e)=>{
    e.preventDefault();

    const s = STRINGS[lang];

    const eventSel = document.getElementById('eventSelect');
    const role     = document.getElementById('roleSelect')?.value;

    if (!eventSel?.value || !role) {
      toast(s.chooseEventRole);
      return;
    }

    // Build structured payload
    const payload = {
      worker_reservation_id: eventSel.value,
      role_slug: role,
      general_notes: document.getElementById('reportText')?.value?.trim?.() || '',
      data: {},
      civil_cases: [],
    };

    switch(role){
      case 'organizer':
        payload.data = {
    attendance: document.getElementById('org_attendance').value,
    issues: document.getElementById('org_issues').value.trim(),
    improvements: document.getElementById('org_improve').value.trim()
        };
        break;

      case 'civil':
        payload.data = {
          attendanceState: document.getElementById('cd_check').value,
          totalCases:      +document.getElementById('cd_total_cases').value || 0,
          concerns:        document.getElementById('cd_concerns').value.trim(),
        };
        payload.civil_cases = getCivilCases();
        break;

      case 'media':
        payload.data = {
          labels:      document.getElementById('media_labels').value.trim(),
          photosCount: +document.getElementById('media_report_photos').value || 0,
          videosCount: +document.getElementById('media_report_videos').value || 0,
          problems:    document.getElementById('media_problems').value.trim(),
          captions:    document.getElementById('media_captions').value.trim()
        };
        break;

      case 'tech':
        payload.data = {
          allOk:       document.getElementById('tech_ok').value,
          returned:    document.getElementById('tech_returned').value,
          issues:      document.getElementById('tech_issues').value.trim(),
          improvements:document.getElementById('tech_suggest').value.trim()
        };
        break;

      case 'cleaner':
        payload.data = {
          zones:       +document.getElementById('clean_zones').value || 0,
          extraHelp:   document.getElementById('clean_extra').value,
          notes:       document.getElementById('clean_notes').value.trim(),
          suggestions: document.getElementById('clean_suggest').value.trim()
        };
        break;

      case 'decorator':
        payload.data = {
          used:     document.getElementById('dec_used').value.trim(),
          damaged:  document.getElementById('dec_damaged').value.trim(),
          replace:  document.getElementById('dec_replace').value.trim(),
          feedback: document.getElementById('dec_feedback').value.trim()
        };
        break;

      case 'cooking':
        payload.data = {
          meals:       document.getElementById('cook_meals').value.trim(),
          ingredients: document.getElementById('cook_ingredients').value.trim(),
          leftovers:   document.getElementById('cook_leftovers').value.trim(),
          hygiene:     document.getElementById('cook_hygiene').value.trim()
        };
        break;

      case 'waiter':
        payload.data = {
          attendance:   document.getElementById('wait_attendance').value,
          itemsServed:  document.getElementById('wait_items').value.trim(),
          serviceIssues:document.getElementById('wait_issues').value.trim(),
          leftovers:    document.getElementById('wait_leftovers').value.trim()
        };
        break;
    }

    // Build FormData with JSON + files
    const fd = new FormData();
    fd.append('_token', csrf);
    fd.append('worker_reservation_id', payload.worker_reservation_id);
    fd.append('role_slug', payload.role_slug);
    fd.append('general_notes', payload.general_notes);
    fd.append('data', JSON.stringify(payload.data));
    fd.append('civil_cases', JSON.stringify(payload.civil_cases));

    // Attach role-specific files
    if (role === 'civil') {
      const cdForms = document.getElementById('cd_forms');
      if (cdForms) [...cdForms.files].forEach(f => fd.append('cd_forms[]', f));
    }
    if (role === 'media') {
      const media = document.getElementById('media_files');
      if (media) [...media.files].forEach(f => fd.append('media_files[]', f));
    }
    if (role === 'tech') {
      const rec = document.getElementById('tech_recording');
      if (rec?.files[0]) fd.append('tech_recording', rec.files[0]);
    }
    if (role === 'decorator') {
      const dec = document.getElementById('dec_photos');
      if (dec) [...dec.files].forEach(f => fd.append('dec_photos[]', f));
    }
    if (role === 'cooking') {
      const cook = document.getElementById('cook_photos');
      if (cook) [...cook.files].forEach(f => fd.append('cook_photos[]', f));
    }

    try {
      const res = await fetch(storeUrl, {
        method: 'POST',
        headers: {
          'X-CSRF-TOKEN': csrf,
          'Accept': 'application/json',
          // DO NOT set Content-Type; browser sets multipart boundary
        },
        body: fd,
      });

      if (!res.ok) {
        const text = await res.text();
        console.error('Backend error (status ' + res.status + '):', text);
        toast(s.submitFail(res.status));
        return;
      }

      const data = await res.json();
      console.log('Saved submission:', data);

      toast(s.submitOk);

      // Reload so Blade pulls updated submissions list from DB
      setTimeout(() => window.location.reload(), 600);

    } catch (err) {
      console.error('Fetch error:', err);
      toast(STRINGS[lang].submitError);
    }
  });
}

// =========================
// Actions on "View Report"
// =========================
function bindActions(){
  if (!list) return;
  list.addEventListener('click', (e)=>{
    const b = e.target.closest('button[data-act="view"]');
    if(!b) return;
    const id = b.getAttribute('data-id');
    // Later you can open a modal and fetch /worker/submissions/{id}
    toast('Report #' + id + ' details would open here.');
  });
}

// =========================
// Small toast helper
// =========================
function toast(msg){
  let box = document.getElementById('toastContainer');
  if(!box){
    box = document.createElement('div');
    box.id = 'toastContainer';
    document.body.appendChild(box);
    Object.assign(box.style,{
      position:'fixed', left:'50%', transform:'translateX(-50%)',
      bottom:'24px', zIndex:9999
    });
  }
  const t = document.createElement('div');
  Object.assign(t.style,{
    background:'rgba(0,0,0,.7)', color:'#fff',
    padding:'10px 14px', borderRadius:'10px', marginTop:'8px'
  });
  t.textContent = msg;
  box.appendChild(t);
  setTimeout(()=> t.remove(), 2200);
}

// =========================
// Theme / Language toggles
// =========================
document.getElementById('themeToggle')?.addEventListener('click', ()=>{
  const isLight = document.body.getAttribute('data-theme')==='light';
  document.body.setAttribute('data-theme', isLight? 'dark':'light');
});

document.getElementById('langToggle')?.addEventListener('click', ()=>{
  lang = (lang==='en') ? 'ar' : 'en';
  i18nApply();
});

// =========================
// Init
// =========================
i18nApply();
bindForm();
bindActions();
bindSearch();
