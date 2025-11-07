/* ========= Bootstrapped data from backend ========= */

let events = Array.isArray(window.initialEvents) ? window.initialEvents : [];
let currentFilter = 'all';

const categoriesFromBackend = Array.isArray(window.initialCategories)
  ? window.initialCategories
  : [];

const rolesFromBackend = Array.isArray(window.initialRoleTypes)
  ? window.initialRoleTypes
  : [];

// Use real DB values; fall back only if tables are empty
let categoryList = categoriesFromBackend.length
  ? categoriesFromBackend.map(c => c.name)
  : ["wedding", "graduation"];

let workerTypeList = rolesFromBackend.length
  ? rolesFromBackend.map(r => r.name)
  : ["Organizer","Civil Defense","Media Staff","Tech Support","Cleaner","Decorator","Cooking Team","Waiter"];

/* ========= Helpers ========= */

const $  = (sel, root=document) => root.querySelector(sel);
const $$ = (sel, root=document) => Array.from(root.querySelectorAll(sel));

function slugifyRole(name){
  return (name || '')
    .toLowerCase()
    .replace(/[^a-z0-9]+/gi,'-')
    .replace(/(^-|-$)/g,'');
}

/* ========= Table render ========= */

function renderEvents() {
  const tbody = $('#eventsTableBody');
  const filtered = currentFilter === 'all'
    ? events
    : events.filter(e => (e.status || '').toLowerCase() === currentFilter);

  tbody.innerHTML = filtered.map(event => {
    const status = (event.status || 'draft').toLowerCase();
    const statusClass = `status-${status}`;
    const statusText = status.charAt(0).toUpperCase() + status.slice(1);

    return `
      <tr>
        <td class="event-title-cell">${event.title}</td>
        <td><span class="event-category">${event.category || '-'}</span></td>
        <td>${event.date || ''}</td>
        <td>${event.location || ''}</td>
        <td>${event.applicants || 0} / ${event.totalSpots || 0}</td>
        <td><span class="status-badge ${statusClass}">${statusText}</span></td>
        <td>
          <div class="action-buttons">
            <button class="btn btn-secondary btn-sm" data-edit="${event.id}">Edit</button>
            <button class="btn btn-danger btn-sm" data-del="${event.id}">Delete</button>
          </div>
        </td>
      </tr>`;
  }).join('');
}

/* ========= Tabs ========= */

function wireTabs(){
  $$('.tab').forEach(tab=>{
    tab.addEventListener('click', ev=>{
      $$('.tab').forEach(t=>t.classList.remove('active'));
      ev.currentTarget.classList.add('active');
      currentFilter = ev.currentTarget.dataset.filter;
      renderEvents();
    });
  });
}

/* ========= Roles UI (uses role_types) ========= */

function getAvailableRoles(){
  const used = new Set(
    $$('#rolesContainer select.role-select')
      .map(s => s.value)
      .filter(Boolean)
  );
  return workerTypeList.filter(r => !used.has(r));
}

function refreshRoleSelectOptions(){
  $$('#rolesContainer select.role-select').forEach(select=>{
    const current = select.value;
    const options = new Set([current, ...getAvailableRoles()]);
    select.innerHTML = '';
    options.forEach(role=>{
      if (!role) return;
      const opt = document.createElement('option');
      opt.value = role;
      opt.textContent = role;
      select.appendChild(opt);
    });
  });
}

function renderRoleRow(roleName = '', spots = 0){
  const wrap = $('#rolesContainer');
  const row  = document.createElement('div');
  row.className = 'role-item';

  const sel = document.createElement('select');
  sel.className = 'role-select';

  workerTypeList.forEach(r=>{
    const o = document.createElement('option');
    o.value = r;
    o.textContent = r;
    sel.appendChild(o);
  });

  if (roleName && workerTypeList.includes(roleName)) {
    sel.value = roleName;
  }

  const inp = document.createElement('input');
  inp.type = 'number';
  inp.min = '0';
  inp.placeholder = 'Spots';
  inp.className = 'role-spots';
  inp.value = String(spots || 0);

  const btn = document.createElement('button');
  btn.type = 'button';
  btn.className = 'btn-remove';
  btn.textContent = 'Remove';
  btn.onclick = () => { row.remove(); refreshRoleSelectOptions(); };

  sel.addEventListener('change', refreshRoleSelectOptions);

  row.appendChild(sel);
  row.appendChild(inp);
  row.appendChild(btn);
  wrap.appendChild(row);

  refreshRoleSelectOptions();
}

function addRoleRow(){
  const avail = getAvailableRoles();
  if (!avail.length){
    alert('All worker types are already added.');
    return;
  }
  renderRoleRow(avail[0], 0);
}

function collectRolesFromModal(){
  return $$('#rolesContainer .role-item').map(r=>{
    const name  = r.querySelector('select.role-select').value;
    const spots = Number(r.querySelector('input.role-spots').value || 0);
    return { name, slug: slugifyRole(name), spots };
  }).filter(x => x.spots > 0);
}

/* ========= Wizard navigation ========= */

let WZ_STEP = 1;

function setWizardStep(n){
  WZ_STEP = n;
  ['step1','step2','step3'].forEach((id,i)=>{
    $('#'+id).classList.toggle('active', i+1 === n);
  });
  [1,2,3].forEach(i=>{
    $('#wz'+i).classList.toggle('active', i <= n);
  });
  $('#btn_back').style.display    = n > 1 ? '' : 'none';
  $('#btn_next').style.display    = n < 3 ? '' : 'none';
  $('#btn_publish').style.display = n === 3 ? '' : 'none';
}

function wizardBack(){
  setWizardStep(Math.max(1, WZ_STEP - 1));
}

async function wizardNext(){
  if (WZ_STEP === 1){
    ensureCategoryOptions();
    const cat1 = $('#wizard_event_category').value;
    const cat3 = $('#eventCategory');
    if (cat3 && cat1) cat3.value = cat1;

    buildStep2CapacityRows();
    await runStaffingAndFillStep2(); // optional

    setWizardStep(2);
    return;
  }

  if (WZ_STEP === 2){
    const rows = $$('#wizard_role_capacity_rows tr').map(r=>{
      const name = r.querySelector('td').textContent.trim();
      const cap  = Number(r.querySelector('input.capacity').value || 0);
      return { name, cap };
    }).filter(x => x.cap > 0);

    $('#rolesContainer').innerHTML = '';
    rows.forEach(p => renderRoleRow(p.name, p.cap));

    const total = rows.reduce((s,x)=> s + x.cap, 0);
    if (total > 0) $('#eventSpots').value = String(total);

    setWizardStep(3);
    return;
  }
}

/* ========= Modal ========= */

function openCreateModal(){
  $('#modalTitle').textContent = 'Create New Event';
  $('#eventForm').reset();
  $('#rolesContainer').innerHTML = '';
  ensureCategoryOptions();
  buildStep2CapacityRows();
  setWizardStep(1);
  $('#eventModal').classList.add('active');
}

function closeModal(){
  $('#eventModal').classList.remove('active');
}

/* ========= Category options (uses event_categories) ========= */

function ensureCategoryOptions(){
  const select = $('#eventCategory');
  if (select){
    const existing = new Set($$('#eventCategory option').map(o => o.value));
    categoryList.forEach(c=>{
      if (!existing.has(c)){
        const opt = document.createElement('option');
        opt.value = c;
        opt.textContent = c.charAt(0).toUpperCase() + c.slice(1);
        select.appendChild(opt);
      }
    });
  }

  // Step 1 dropdown:
  $('#wizard_event_category').innerHTML = categoryList
    .map(c => `<option value="${c}">${c[0].toUpperCase()+c.slice(1)}</option>`)
    .join('');
}

/* ========= Step 2 rows ========= */

function buildStep2CapacityRows(){
  const tbody = $('#wizard_role_capacity_rows');
  tbody.innerHTML = workerTypeList.map(w=>{
    const slug = slugifyRole(w);
    return `
      <tr data-worker-type="${slug}">
        <td>${w}</td>
        <td><input class="capacity" type="number" min="0" value="0" style="max-width:180px"></td>
      </tr>`;
  }).join('');
}

/* ========= AI staffing (optional) ========= */

async function runStaffingAndFillStep2(){
  if (!window.ENDPOINT_AI_STAFFING) return false;

  const area   = Number($('#venue_area_m2').value || 0);
  const people = Number($('#expected_attendees').value || 0);
  const cat    = $('#wizard_event_category').value || (categoryList[0] || 'general');

  try {
    const res = await fetch(window.ENDPOINT_AI_STAFFING, {
      method:'POST',
      headers:{
        'Content-Type':'application/json',
        'X-Requested-With':'XMLHttpRequest'
      },
      body: JSON.stringify({
        venue_area_m2: area,
        expected_attendees: people,
        category: cat,
        available_roles: [...workerTypeList]
      })
    });

    if (!res.ok) return false;

    const ai = await res.json();   // {roles:[{name,spots}]}

    buildStep2CapacityRows();
    const map = new Map(ai.roles.map(r => [r.name, r.spots]));

    $$('#wizard_role_capacity_rows tr').forEach(tr=>{
      const label = tr.querySelector('td').textContent.trim();
      const inp   = tr.querySelector('input.capacity');
      inp.value = map.get(label) ?? 0;
    });

    return true;
  } catch (e){
    console.error('AI staffing error', e);
    return false;
  }
}

/* ========= Publish (SAVE TO DB) ========= */

async function publishEvent() {
  const form = $('#eventForm');
  if (!form.checkValidity()) {
    form.reportValidity();
    return;
  }

  const roles = collectRolesFromModal();
  if (!roles.length) {
    alert('Add at least one role with spots > 0.');
    return;
  }

  const payload = {
    title: $('#eventTitle').value,
    description: $('#eventDescription').value,
    category: $('#eventCategory').value,
    location: $('#eventLocation').value,
    date: $('#eventDate').value,
    time: $('#eventTime').value,
    duration_hours: Number($('#eventDuration').value),
    total_spots: Number($('#eventSpots').value),
    requirements: '',
    venue_area_m2: Number($('#venue_area_m2').value || 0),
    expected_attendees: Number($('#expected_attendees').value || 0),
    roles // [{ name, slug, spots }]
  };

  try {
    const res = await fetch(window.ENDPOINT_CREATE_EVENT, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
        'X-CSRF-TOKEN': window.csrfToken,
      },
      body: JSON.stringify(payload),
    });

    // Read body safely even on 422/500
    const raw = await res.text();
    let data = null;
    try { data = raw ? JSON.parse(raw) : null; } catch (e) { /* not JSON */ }

    // Helper to format error for alert
    const buildErrorMessage = () => {
      let msg = `Request failed (${res.status} ${res.statusText})`;

      if (data) {
        if (data.message) msg += `\nMessage: ${data.message}`;
        if (data.error)   msg += `\nError: ${data.error}`;

        // Laravel validation errors: { field: [..] }
        if (data.errors) {
          msg += `\nValidation errors:`;
          Object.entries(data.errors).forEach(([field, msgs]) => {
            msgs.forEach(m => { msg += `\n - ${field}: ${m}`; });
          });
        }
      } else if (raw) {
        msg += `\nRaw response: ${raw.substring(0, 400)}`;
      }

      msg += `\n\n(Check DevTools Network tab / storage/logs/laravel.log for full details.)`;
      return msg;
    };

    // Handle non-2xx or backend "ok:false"
    if (!res.ok || !data || data.ok === false) {
      console.error('Event create failed', {
        status: res.status,
        statusText: res.statusText,
        data,
        raw,
      });
      alert(buildErrorMessage());
      return;
    }

    // Success path
    const ev = data.event;

    events.unshift({
      id: ev.id,
      title: ev.title,
      category: ev.category,
      date: (ev.starts_at || '').substring(0, 10),
      location: ev.location,
      applicants: 0,
      totalSpots: ev.total_spots,
      status: ev.status || 'published',
    });

    alert(data.message || 'Event published successfully.');
    closeModal();
    renderEvents();

  } catch (err) {
    console.error('Unexpected error while creating event', err);
    alert('Unexpected error while creating event: ' + err.message);
  }
}


/* ========= Theme / Language ========= */

function toggleTheme(){
  const html = document.documentElement;
  const current = html.getAttribute('data-theme') || 'dark';
  const next = current === 'dark' ? 'light' : 'dark';
  html.setAttribute('data-theme', next);
  $('#theme-icon').textContent = next === 'dark' ? '☀️' : '🌙';
}

function toggleLanguage(){
  const html = document.documentElement;
  const current = html.getAttribute('lang') || 'en';
  const next = current === 'en' ? 'ar' : 'en';
  const dir  = next === 'ar' ? 'rtl' : 'ltr';
  html.setAttribute('lang', next);
  html.setAttribute('dir', dir);
  $('#lang-icon').textContent = next === 'en' ? 'AR' : 'EN';
}

/* ========= Table actions (Edit/Delete placeholders) ========= */

function handleTableClicks(e){
  const editId = e.target.getAttribute('data-edit');
  const delId  = e.target.getAttribute('data-del');

  if (editId){
    const ev = events.find(x => String(x.id) === String(editId));
    if (ev){
      $('#modalTitle').textContent = 'Edit Event';
      $('#eventTitle').value = ev.title;
      ensureCategoryOptions();
      $('#eventCategory').value = ev.category || '';
      $('#eventLocation').value = ev.location || '';
      $('#eventDate').value = ev.date || '';
      setWizardStep(3);
      $('#eventModal').classList.add('active');
    }
  }

  if (delId){
    if (confirm('This should call DELETE endpoint (to implement).')){
      // TODO: implement DELETE /admin/events/{id}
    }
  }
}

/* ========= Init ========= */

document.addEventListener('DOMContentLoaded', ()=>{
  $('#btn_open_create').addEventListener('click', openCreateModal);
  $('#btn_close_modal').addEventListener('click', closeModal);
  $('#btn_back').addEventListener('click', wizardBack);
  $('#btn_next').addEventListener('click', wizardNext);
  $('#btn_publish').addEventListener('click', publishEvent);
  $('#btn_add_role').addEventListener('click', addRoleRow);
  $('#btn_theme').addEventListener('click', toggleTheme);
  $('#btn_lang').addEventListener('click', toggleLanguage);
  $('.table').addEventListener('click', handleTableClicks);

  ensureCategoryOptions();
  buildStep2CapacityRows();
  wireTabs();
  renderEvents();
});
