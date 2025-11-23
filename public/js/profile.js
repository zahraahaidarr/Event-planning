// ========== Toast helper ==========
function toast(msg){
  let box = document.getElementById('toastContainer');
  if(!box){
    box = document.createElement('div');
    box.id = 'toastContainer';
    Object.assign(box.style,{
      position:'fixed',
      left:'50%',
      transform:'translateX(-50%)',
      bottom:'24px',
      zIndex:9999
    });
    document.body.appendChild(box);
  }
  const t = document.createElement('div');
  Object.assign(t.style,{
    background:'rgba(0,0,0,.75)',
    color:'#fff',
    padding:'10px 14px',
    borderRadius:'10px',
    marginTop:'8px'
  });
  t.textContent = msg;
  box.appendChild(t);
  setTimeout(()=>t.remove(),2200);
}

// ========== Theme toggle ==========
const themeToggle = document.getElementById('themeToggle');
if (themeToggle) {
  themeToggle.addEventListener('click', () => {
    const isLight = document.body.getAttribute('data-theme') === 'light';
    document.body.setAttribute('data-theme', isLight ? 'dark' : 'light');
  });
}

// ========== Lang toggle ==========
const langToggle = document.getElementById('langToggle');
if (langToggle) {
  langToggle.addEventListener('click', () => {
    const html = document.documentElement;
    const next = html.getAttribute('lang') === 'ar' ? 'en' : 'ar';
    html.setAttribute('lang', next);
    html.setAttribute('dir', next === 'ar' ? 'rtl' : 'ltr');
    langToggle.textContent = next === 'ar' ? 'AR/EN' : 'EN/AR';
  });
}

// ========== Avatar initials live update ==========
function updateInitials(){
  const el = document.getElementById('avatarInitials');
  if(!el) return;
  const first = (document.getElementById('firstName').value || '').trim();
  const last  = (document.getElementById('lastName').value  || '').trim();
  const a = first ? first[0].toUpperCase() : '';
  const b = last  ? last[0].toUpperCase()  : '';
  el.textContent = (a + b) || 'U';
}
updateInitials();
document.getElementById('firstName').addEventListener('input', updateInitials);
document.getElementById('lastName').addEventListener('input', updateInitials);

// ========== CSRF ==========
const csrfMeta = document.querySelector('meta[name="csrf-token"]');
const CSRF = csrfMeta ? csrfMeta.content : '';

// ================== REAL SAVE HANDLERS ==================

// ---- Account ----
document.getElementById('saveAccount').addEventListener('click', async ()=>{
  const first_name = (document.getElementById('firstName').value || '').trim();
  const last_name  = (document.getElementById('lastName').value || '').trim();
  const email      = (document.getElementById('email').value     || '').trim();

  try{
    const res = await fetch(window.ROUTES.account,{
      method:'PUT',
      headers:{
        'Content-Type':'application/json',
        'X-CSRF-TOKEN':CSRF,
        'Accept':'application/json'
      },
      body:JSON.stringify({ first_name, last_name, email })
    });
    const data = await res.json();
    if(!res.ok || !data.ok) throw new Error(data.message || 'Save failed');

    document.getElementById('infoEmail').textContent = email || '—';
    toast('Account changes saved.');
  }catch(err){
    toast(err.message);
    console.error(err);
  }
});

document.getElementById('cancelAccount').addEventListener('click', ()=>{
  toast('Changes cancelled.');
});

// ---- Personal ----
document.getElementById('savePersonal').addEventListener('click', async ()=>{
  const phone         = (document.getElementById('phone').value || '').trim();
  const date_of_birth = (document.getElementById('dob').value   || '').trim(); // yyyy-mm-dd

  try{
    const res = await fetch(window.ROUTES.personal,{
      method:'PUT',
      headers:{
        'Content-Type':'application/json',
        'X-CSRF-TOKEN':CSRF,
        'Accept':'application/json'
      },
      body:JSON.stringify({ phone, date_of_birth })
    });
    const data = await res.json();
    if(!res.ok || !data.ok) throw new Error(data.message || 'Save failed');
    toast('Personal information saved.');
  }catch(err){
    toast(err.message);
    console.error(err);
  }
});

document.getElementById('cancelPersonal').addEventListener('click', ()=>{
  toast('Cancelled.');
});

// ---- Password ----
document.getElementById('updatePassword').addEventListener('click', async ()=>{
  const current_password      = document.getElementById('oldPass').value;
  const password              = document.getElementById('newPass').value;
  const password_confirmation = document.getElementById('confirmPass').value;

  try{
    const res = await fetch(window.ROUTES.password,{
      method:'PUT',
      headers:{
        'Content-Type':'application/json',
        'X-CSRF-TOKEN':CSRF,
        'Accept':'application/json'
      },
      body:JSON.stringify({ current_password, password, password_confirmation })
    });
    const data = await res.json();
    if(!res.ok || !data.ok) throw new Error(data.message || 'Password update failed');

    toast('Password updated successfully.');
    document.getElementById('oldPass').value = '';
    document.getElementById('newPass').value = '';
    document.getElementById('confirmPass').value = '';
  }catch(err){
    toast(err.message);
    console.error(err);
  }
});

// ---- Avatar Upload ----
const avatarForm = document.getElementById('avatarForm');
if(avatarForm){
  avatarForm.addEventListener('submit', async (e)=>{
    e.preventDefault();
    const fileInput = document.getElementById('photoFile');
    const file = fileInput?.files?.[0];
    if(!file){ toast('Choose a photo first.'); return; }
    if(file.size > 2*1024*1024){ toast('Max size is 2MB.'); return; }

    const fd = new FormData(avatarForm);
    try{
      const res = await fetch(avatarForm.action,{
        method:'POST',
        headers:{ 'X-CSRF-TOKEN': CSRF },
        body: fd
      });
      const data = await res.json();
      if(!res.ok || !data.ok){
        toast(data.message || 'Upload failed.');
        return;
      }

      const img = document.getElementById('avatarImg');
      const initials = document.getElementById('avatarInitials');
      img.src = data.avatar_url + '?t=' + Date.now();
      img.style.display = 'block';
      if(initials) initials.style.display = 'none';
      toast('Profile photo updated.');
      fileInput.value = '';
    }catch(err){
      console.error(err);
      toast('Upload failed (network).');
    }
  });
}

// ---- DOB: prevent future dates on client ----
const dobInput = document.getElementById('dob');
if(dobInput){
  const today = new Date().toISOString().split('T')[0];
  dobInput.setAttribute('max', today);

  dobInput.addEventListener('change', ()=>{
    if(dobInput.value && dobInput.value > today){
      dobInput.value = today;
      toast('Date of birth cannot be in the future.');
    }
  });
}
