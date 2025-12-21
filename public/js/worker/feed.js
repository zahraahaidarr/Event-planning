(() => {
  const tabs = Array.from(document.querySelectorAll('.feedTab'));
  const panes = Array.from(document.querySelectorAll('.tabPane'));

  const pauseAllVideos = () => {
    document.querySelectorAll('video').forEach(v => {
      try { v.pause(); } catch(e) {}
    });
  };

  const openTab = (name) => {
    // activate tab button
    tabs.forEach(t => t.classList.toggle('active', t.dataset.tab === name));

    // show only the matching pane
    panes.forEach(p => {
      const shouldShow = (p.id === `tab-${name}`);
      p.classList.toggle('hidden', !shouldShow);
    });

    // pause videos from hidden panes
    pauseAllVideos();
  };

  tabs.forEach(btn => btn.addEventListener('click', () => openTab(btn.dataset.tab)));

  // ----------------------------
  // Generic IG slider (posts/reels/stories)
  // ----------------------------
  function setupStage(stageName) {
    const pane = document.querySelector(`#tab-${stageName}`);
    if (!pane) return;

    // scope queries INSIDE the pane (safer)
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

    // keyboard arrows only when this pane is visible
    window.addEventListener('keydown', (e) => {
      const activePane = document.querySelector(`#tab-${stageName}`);
      if (!activePane || activePane.classList.contains('hidden')) return;

      const tag = (document.activeElement?.tagName || '').toLowerCase();
      if (tag === 'input' || tag === 'textarea') return;

      if (e.key === 'ArrowRight') next();
      if (e.key === 'ArrowLeft') prev();
    });

    // swipe
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
})();
