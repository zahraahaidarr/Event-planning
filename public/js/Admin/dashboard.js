function toggleTheme(){
    const html=document.documentElement;
    const next=html.getAttribute('data-theme')==='dark'?'light':'dark';
    html.setAttribute('data-theme',next);
    const icon=document.getElementById('theme-icon');
    if(icon){ icon.textContent = next==='dark'?'☀️':'🌙'; }
}

function toggleLanguage(){
    const html=document.documentElement;
    const next=html.getAttribute('lang')==='en'?'ar':'en';
    html.setAttribute('lang',next);
    html.setAttribute('dir', next==='ar'?'rtl':'ltr');
    const el=document.getElementById('lang-icon');
    if(el){ el.textContent = next==='en'?'AR':'EN'; }
}
