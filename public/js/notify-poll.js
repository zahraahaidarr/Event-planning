(function(){
  const COUNT_EL_ID = 'notify-count';
  const ENDPOINT = '/api/notifications/unread-count';

  async function tick(){
    try{
      const res = await fetch(ENDPOINT, {
        headers: {'X-Requested-With':'XMLHttpRequest','Accept':'application/json'}
      });
      const data = await res.json();
      const el = document.getElementById(COUNT_EL_ID);
      if(!el) return;
      const n = Number(data?.count || 0);
      el.textContent = String(n);
      el.style.display = n > 0 ? 'inline-block' : 'none';
    }catch(e){ /* silent */ }
  }

  document.addEventListener('DOMContentLoaded', () => {
    tick();
    setInterval(tick, 20000);
  });
})();
