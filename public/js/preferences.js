// public/js/worker/preferences.js

(function () {
  // THEME (global)
  const savedTheme = localStorage.getItem('vh_theme');
  if (savedTheme === 'light' || savedTheme === 'dark') {
    document.documentElement.setAttribute('data-theme', savedTheme);
    document.addEventListener('DOMContentLoaded', () => {
      if (document.body) {
        document.body.setAttribute('data-theme', savedTheme);
      }
    });
  }

  // LANGUAGE (global: lang + dir only)
  const savedLang = localStorage.getItem('vh_lang');
  let effectiveLang;

  if (savedLang === 'ar' || savedLang === 'en') {
    effectiveLang = savedLang;
  } else {
    // fallback to current html lang
    effectiveLang = document.documentElement.lang === 'ar' ? 'ar' : 'en';
  }

  document.documentElement.lang = effectiveLang;
  document.documentElement.dir  = (effectiveLang === 'ar') ? 'rtl' : 'ltr';

  // expose globally so every page JS can reuse
  window.vhLang = effectiveLang;
})();
