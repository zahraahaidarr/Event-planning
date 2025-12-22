(() => {
  const tabs = Array.from(document.querySelectorAll('.feedTab'));
  const panes = Array.from(document.querySelectorAll('.tabPane'));

  const pauseAllVideos = () => {
    document.querySelectorAll('video').forEach(v => {
      try { v.pause(); } catch(e) {}
    });
  };

  const openTab = (name) => {
    tabs.forEach(t => t.classList.toggle('active', t.dataset.tab === name));

    panes.forEach(p => {
      const shouldShow = (p.id === `tab-${name}`);
      p.classList.toggle('hidden', !shouldShow);
    });

    // close comment panels when switching
    document.querySelectorAll('.igComments').forEach(x => x.classList.add('hidden'));

    pauseAllVideos();
  };

  tabs.forEach(btn => btn.addEventListener('click', () => openTab(btn.dataset.tab)));

  // ----------------------------
  // Generic IG slider (posts/reels/stories)
  // ----------------------------
  function setupStage(stageName) {
    const pane = document.querySelector(`#tab-${stageName}`);
    if (!pane) return;

    const slides = Array.from(pane.querySelectorAll(`[data-slide="${stageName}"]`));
    if (!slides.length) return;

    const prevBtn = pane.querySelector(`[data-prev="${stageName}"]`);
    const nextBtn = pane.querySelector(`[data-next="${stageName}"]`);
    const dotsWrap = pane.querySelector(`[data-dots="${stageName}"]`);
    const dots = dotsWrap ? Array.from(dotsWrap.querySelectorAll('.dot')) : [];

    let index = slides.findIndex(s => s.classList.contains('active'));
    if (index < 0) index = 0;

    const setActive = (i) => {
      index = Math.max(0, Math.min(slides.length - 1, i));
      slides.forEach((s, k) => s.classList.toggle('active', k === index));
      dots.forEach((d, k) => d.classList.toggle('active', k === index));

      // close comment panels when changing slide
      document.querySelectorAll('.igComments').forEach(x => x.classList.add('hidden'));

      if (prevBtn) prevBtn.disabled = index === 0;
      if (nextBtn) nextBtn.disabled = index === slides.length - 1;
    };

    const next = () => setActive(index + 1);
    const prev = () => setActive(index - 1);

    prevBtn?.addEventListener('click', prev);
    nextBtn?.addEventListener('click', next);

    dots.forEach(d => {
      d.addEventListener('click', () => {
        const go = Number(d.dataset.index);
        if (!Number.isNaN(go)) setActive(go);
      });
    });

    window.addEventListener('keydown', (e) => {
      const activePane = document.querySelector(`#tab-${stageName}`);
      if (!activePane || activePane.classList.contains('hidden')) return;

      const tag = (document.activeElement?.tagName || '').toLowerCase();
      if (tag === 'input' || tag === 'textarea') return;

      if (e.key === 'ArrowRight') next();
      if (e.key === 'ArrowLeft') prev();
    });

    const viewport = pane.querySelector(`[data-viewport="${stageName}"]`);
    if (viewport) {
      let startX = null;

      viewport.addEventListener('pointerdown', (e) => { startX = e.clientX; });
      viewport.addEventListener('pointerup', (e) => {
        if (startX == null) return;
        const dx = e.clientX - startX;
        startX = null;
        if (Math.abs(dx) < 60) return;
        if (dx < 0) next(); else prev();
      });
    }

    setActive(index);
  }

  setupStage('posts');
  setupStage('reels');
  setupStage('stories');

  // default tab
  openTab('events');

  // =========================================================
  // Likes + Comments (AJAX)
  // =========================================================

  const csrf = document.querySelector('meta[name="csrf-token"]')?.content;

  async function postJson(url, data){
    const res = await fetch(url, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': csrf,
        'Accept': 'application/json',
      },
      body: JSON.stringify(data),
    });
    return res.json();
  }

  async function getJson(url){
    const res = await fetch(url, { headers: { 'Accept': 'application/json' }});
    return res.json();
  }

  function escapeHtml(s){
    return String(s ?? '')
      .replaceAll('&','&amp;')
      .replaceAll('<','&lt;')
      .replaceAll('>','&gt;')
      .replaceAll('"','&quot;')
      .replaceAll("'","&#039;");
  }

  // ----------------------------
  // Likes
  // ----------------------------
  document.addEventListener('click', async (e) => {
    const btn = e.target.closest('.igLikeBtn');
    if (!btn) return;

    const type = btn.dataset.likeType;
    const id = Number(btn.dataset.likeId);
    if (!type || !id) return;

    try{
      const data = await postJson('/social/like', { type, id });

      if (data?.ok){
        btn.classList.toggle('liked', !!data.liked);

        const countEl = document.querySelector(`[data-like-count="${type}-${id}"]`);
        if (countEl) countEl.textContent = data.likes_count;
      }
    }catch(err){
      console.error(err);
    }
  });

  // ----------------------------
  // Comments open + load
  // ----------------------------
  document.addEventListener('click', async (e) => {
    const btn = e.target.closest('.igCommentBtn');
    if (!btn) return;

    const type = btn.dataset.cType;
    const id = Number(btn.dataset.cId);
    if (!type || !id) return;

    const box = document.querySelector(`[data-comments-box="${type}-${id}"]`);
    const list = document.querySelector(`[data-comments-list="${type}-${id}"]`);
    if (!box || !list) return;

    const isHidden = box.classList.contains('hidden');

    // Close other boxes
    document.querySelectorAll('.igComments').forEach(x => x.classList.add('hidden'));

    // Toggle this box
    if (isHidden) box.classList.remove('hidden');
    else box.classList.add('hidden');

    if (!isHidden) return; // closed

    list.innerHTML = `<div class="muted">Loading...</div>`;

    try{
      const data = await getJson(`/social/comments?type=${encodeURIComponent(type)}&id=${encodeURIComponent(id)}`);

      if (!data?.ok){
        list.innerHTML = `<div class="muted">Failed to load comments.</div>`;
        return;
      }

      if (!data.comments.length){
        list.innerHTML = `<div class="muted">No comments yet.</div>`;
        return;
      }

      list.innerHTML = data.comments.map(c => {
        const avatar = c.user.avatar
          ? `<img class="igCAvatar" src="${c.user.avatar}" alt="avatar">`
          : `<div class="igCAvatar" style="display:flex;align-items:center;justify-content:center;background:rgba(255,255,255,.06);font-weight:900;">?</div>`;

        return `
          <div class="igCommentRow">
            ${avatar}
            <div class="igCBody">
              <span class="igCName">${escapeHtml(c.user.name)}</span>
              ${escapeHtml(c.body)}
              <span class="igCTime">${escapeHtml(c.created_at)}</span>
            </div>
          </div>
        `;
      }).join('');

      list.scrollTop = list.scrollHeight;
    }catch(err){
      console.error(err);
      list.innerHTML = `<div class="muted">Error loading comments.</div>`;
    }
  });

  // ----------------------------
  // Comment submit
  // ----------------------------
  document.addEventListener('submit', async (e) => {
    const form = e.target.closest('.igCommentForm');
    if (!form) return;

    e.preventDefault();

    const key = form.dataset.commentForm; // e.g. post-5
    const [type, idStr] = key.split('-');
    const id = Number(idStr);

    const input = form.querySelector('input[name="body"]');
    const body = input?.value?.trim();
    if (!body) return;

    input.disabled = true;

    try{
      const data = await postJson('/social/comments', { type, id, body });

      if (data?.ok){
        const list = document.querySelector(`[data-comments-list="${type}-${id}"]`);
        if (list){
          if (list.textContent.includes('No comments yet')) list.innerHTML = '';

          const c = data.comment;
          const avatar = c.user.avatar
            ? `<img class="igCAvatar" src="${c.user.avatar}" alt="avatar">`
            : `<div class="igCAvatar" style="display:flex;align-items:center;justify-content:center;background:rgba(255,255,255,.06);font-weight:900;">?</div>`;

          const row = document.createElement('div');
          row.innerHTML = `
            <div class="igCommentRow">
              ${avatar}
              <div class="igCBody">
                <span class="igCName">${escapeHtml(c.user.name)}</span>
                ${escapeHtml(c.body)}
                <span class="igCTime">${escapeHtml(c.created_at)}</span>
              </div>
            </div>
          `;
          list.appendChild(row.firstElementChild);
          list.scrollTop = list.scrollHeight;
        }

        const countEl = document.querySelector(`[data-comment-count="${type}-${id}"]`);
        if (countEl) countEl.textContent = data.comments_count;

        input.value = '';
      }
    }catch(err){
      console.error(err);
    }finally{
      input.disabled = false;
      input.focus();
    }
  });

})();
