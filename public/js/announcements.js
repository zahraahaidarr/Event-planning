// Employee: lock hidden audience
(function(){
  const hidden = document.querySelector('body.has-employee input[name="audience"][type="hidden"]');
  if(hidden) hidden.value = 'workers';
})();

// Optional theme toggle (if you add buttons later). Safe no-ops otherwise.
(function(){
  const themeBtn = document.querySelector('.theme-toggle, .icon-btn[data-theme]');
  if(!themeBtn) return;
  themeBtn.addEventListener('click', () => {
    const html = document.documentElement;
    const cur = html.getAttribute('data-theme') || 'dark';
    const next = cur === 'dark' ? 'light' : 'dark';
    html.setAttribute('data-theme', next);
    const icon = document.getElementById('theme-icon');
    if(icon) icon.textContent = next === 'dark' ? '☀️' : '🌙';
  });
})();

(function(){
  const langBtn = document.querySelector('.lang-toggle, .icon-btn[data-lang]');
  if(!langBtn) return;
  langBtn.addEventListener('click', () => {
    const cur = (document.documentElement.getAttribute('lang') || 'en').toLowerCase();
    const next = cur === 'en' ? 'ar' : 'en';
    document.documentElement.setAttribute('lang', next);
    document.documentElement.setAttribute('dir', next === 'ar' ? 'rtl' : 'ltr');
    const icon = document.getElementById('lang-icon');
    if(icon) icon.textContent = next === 'en' ? 'AR' : 'EN';
  });
})();
