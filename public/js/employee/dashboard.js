function toggleTheme(){
  const html=document.documentElement;
  const next=html.getAttribute('data-theme')==='dark'?'light':'dark';
  html.setAttribute('data-theme', next);
  const btn=document.querySelector('.theme-toggle');
  if(btn) btn.textContent = next==='dark' ? '🌙' : '☀️';
}

function toggleLanguage(){
  const btn=document.querySelector('.lang-toggle');
  if(!btn) return;
  const current=btn.textContent.trim();
  const next=current==='EN'?'AR':'EN';
  btn.textContent=next;
  document.documentElement.setAttribute('dir', next==='AR'?'rtl':'ltr');
}
