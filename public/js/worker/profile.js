// Simple helpers
function toast(msg){
  let box = document.getElementById('toastContainer');
  if(!box){
    box = document.createElement('div');
    box.id = 'toastContainer';
    Object.assign(box.style, {position:'fixed',left:'50%',transform:'translateX(-50%)',bottom:'24px',zIndex:9999});
    document.body.appendChild(box);
  }
  const t = document.createElement('div');
  Object.assign(t.style,{background:'rgba(0,0,0,.75)',color:'#fff',padding:'10px 14px',borderRadius:'10px',marginTop:'8px'});
  t.textContent = msg;
  box.appendChild(t);
  setTimeout(()=>t.remove(), 2200);
}

// Theme toggle
document.getElementById('themeToggle').addEventListener('click', ()=>{
  const isLight = document.body.getAttribute('data-theme')==='light';
  document.body.setAttribute('data-theme', isLight ? 'dark' : 'light');
});

// Lang toggle (LTR/RTL only – copy tweaks as needed)
document.getElementById('langToggle').addEventListener('click', ()=>{
  const html = document.documentElement;
  const next = html.getAttribute('lang') === 'ar' ? 'en' : 'ar';
  html.setAttribute('lang', next);
  html.setAttribute('dir', next === 'ar' ? 'rtl' : 'ltr');
  document.getElementById('langToggle').textContent = next === 'ar' ? 'AR/EN' : 'EN/AR';
});

// Avatar initials from Full Name
(function updateInitials(){
  const el = document.getElementById('avatarInitials');
  const name = document.getElementById('fullName').value.trim();
  const initials = name.split(/\s+/).slice(0,2).map(s=>s[0]?.toUpperCase()||'').join('');
  el.textContent = initials || 'U';
})();
document.getElementById('fullName').addEventListener('input', ()=>{
  const el = document.getElementById('avatarInitials');
  const name = document.getElementById('fullName').value.trim();
  const initials = name.split(/\s+/).slice(0,2).map(s=>s[0]?.toUpperCase()||'').join('');
  el.textContent = initials || 'U';
});

// Fake save handlers (replace with real POST later)
document.getElementById('saveAccount').addEventListener('click', ()=>{
  // Example of syncing the “Account Info” email line:
  document.getElementById('infoEmail').textContent = document.getElementById('email').value || '—';
  toast('Account changes saved.');
});
document.getElementById('cancelAccount').addEventListener('click', ()=>{
  toast('Changes cancelled.');
});
document.getElementById('savePersonal').addEventListener('click', ()=>{
  toast('Personal information saved.');
});
document.getElementById('cancelPersonal').addEventListener('click', ()=>{
  toast('Cancelled.');
});

// Upload button demo (client-side only)
document.getElementById('uploadPhoto').addEventListener('click', ()=>{
  const file = document.getElementById('photoFile').files?.[0];
  if(!file){ toast('Choose a photo first.'); return; }
  if(file.size > 2*1024*1024){ toast('Max size is 2MB.'); return; }
  toast('Photo uploaded (demo).');
});

// Password update (demo validation)
document.getElementById('updatePassword').addEventListener('click', ()=>{
  const oldP = document.getElementById('oldPass').value;
  const newP = document.getElementById('newPass').value;
  const cP   = document.getElementById('confirmPass').value;
  if(!oldP || !newP){ toast('Fill current & new password.'); return; }
  if(newP !== cP){ toast('Passwords do not match.'); return; }
  if(newP.length < 8){ toast('New password must be at least 8 chars.'); return; }
  toast('Password updated (demo).');
});
