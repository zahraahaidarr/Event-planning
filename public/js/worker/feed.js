(() => {
  // =========================================================
  // Tabs + panes
  // =========================================================
  const tabs = Array.from(document.querySelectorAll('.feedTab'));
  const panes = Array.from(document.querySelectorAll('.tabPane'));

  const pauseAllVideos = () => {
    document.querySelectorAll('video').forEach(v => { try { v.pause(); } catch (e) {} });
  };

  const openTab = (name) => {
    tabs.forEach(t => t.classList.toggle('active', t.dataset.tab === name));
    panes.forEach(p => p.classList.toggle('hidden', p.id !== `tab-${name}`));
    pauseAllVideos();
  };

  tabs.forEach(btn => btn.addEventListener('click', () => openTab(btn.dataset.tab)));

  // =========================================================
  // Slider
  // =========================================================
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
      if (prevBtn) prevBtn.disabled = index === 0;
      if (nextBtn) nextBtn.disabled = index === slides.length - 1;
    };

    prevBtn?.addEventListener('click', () => setActive(index - 1));
    nextBtn?.addEventListener('click', () => setActive(index + 1));

    dots.forEach(d => d.addEventListener('click', () => {
      const go = Number(d.dataset.index);
      if (!Number.isNaN(go)) setActive(go);
    }));

    // Keyboard arrows
    window.addEventListener('keydown', (e) => {
      const activePane = document.querySelector(`#tab-${stageName}`);
      if (!activePane || activePane.classList.contains('hidden')) return;

      const tag = (document.activeElement?.tagName || '').toLowerCase();
      if (tag === 'input' || tag === 'textarea') return;

      if (e.key === 'ArrowRight') setActive(index + 1);
      if (e.key === 'ArrowLeft') setActive(index - 1);
    });

    // Swipe
    const viewport = pane.querySelector(`[data-viewport="${stageName}"]`);
    if (viewport) {
      let startX = null;
      viewport.addEventListener('pointerdown', (e) => { startX = e.clientX; });
      viewport.addEventListener('pointerup', (e) => {
        if (startX == null) return;
        const dx = e.clientX - startX;
        startX = null;
        if (Math.abs(dx) < 60) return;
        if (dx < 0) setActive(index + 1); else setActive(index - 1);
      });
    }

    setActive(index);
  }

  setupStage('posts');
  setupStage('reels');
  setupStage('stories');

  openTab('events');

  // =========================================================
  // Helpers
  // =========================================================
  const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

  function escapeHtml(str) {
    return String(str ?? '')
      .replaceAll('&', '&amp;')
      .replaceAll('<', '&lt;')
      .replaceAll('>', '&gt;')
      .replaceAll('"', '&quot;')
      .replaceAll("'", '&#039;');
  }

  async function safeJson(res) {
    // if Laravel redirected -> HTML, JSON parse will fail
    const text = await res.text();
    try { return JSON.parse(text); } catch (_) { return { __raw: text }; }
  }

  // =========================================================
  // Likes (keep this)
  // =========================================================
  document.addEventListener('click', async (e) => {
    const btn = e.target.closest('.igLikeBtn');
    if (!btn) return;

    const type = btn.dataset.likeType;
    const id = Number(btn.dataset.likeId);
    if (!type || !id) return;

    try {
      const res = await fetch('/social/like', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'X-CSRF-TOKEN': csrf,
          'X-Requested-With': 'XMLHttpRequest',
        },
        body: JSON.stringify({ type, id }),
      });

      const data = await safeJson(res);

      if (!res.ok) {
        console.error('LIKE failed:', res.status, data);
        return;
      }

      if (data?.ok) {
        btn.classList.toggle('liked', !!data.liked);
        const countEl = document.querySelector(`[data-like-count="${type}-${id}"]`);
        if (countEl) countEl.textContent = data.likes_count;
      }
    } catch (err) {
      console.error(err);
    }
  });

  // =========================================================
  // Comments Modal (ONLY SYSTEM)
  // =========================================================
  const modal  = document.getElementById('commentModal');
  const cmList = document.getElementById('cmList');
  const cmForm = document.getElementById('cmForm');
  const cmType = document.getElementById('cmType');
  const cmId   = document.getElementById('cmId');
  const cmInput= document.getElementById('cmInput');

  let activeCountElId = null;

  const openModal = () => {
    modal.classList.remove('hidden');
    modal.setAttribute('aria-hidden', 'false');
    document.body.style.overflow = 'hidden';
    setTimeout(() => cmInput?.focus(), 50);
  };

  const closeModal = () => {
    modal.classList.add('hidden');
    modal.setAttribute('aria-hidden', 'true');
    document.body.style.overflow = '';
    cmList.innerHTML = '';
    cmInput.value = '';
    activeCountElId = null;
  };

  modal?.addEventListener('click', (e) => {
    if (e.target && e.target.hasAttribute('data-cm-close')) closeModal();
  });

  window.addEventListener('keydown', (e) => {
    if (e.key === 'Escape' && modal && !modal.classList.contains('hidden')) closeModal();
  });

  function renderCommentItem(c) {
    const avatar = c.user?.avatar
      ? `<img class="cmAvatarImg" src="${c.user.avatar}" alt="${escapeHtml(c.user.name)}">`
      : `<div class="cmAvatarFallback">${escapeHtml(c.user?.initial || 'U')}</div>`;

    return `
      <div class="cmItem">
        <div class="cmAvatar">${avatar}</div>
        <div class="cmContent">
          <div class="cmLine">
            <span class="cmName">${escapeHtml(c.user?.name || 'User')}</span>
            <span class="cmTime">${escapeHtml(c.created_at || '')}</span>
          </div>
          <div class="cmText">${escapeHtml(c.body)}</div>
        </div>
      </div>
    `;
  }

  async function loadComments(type, id) {
    cmList.innerHTML = `<div class="cmLoading">Loading...</div>`;

    try {
      const url = new URL('/worker/feed/comments', window.location.origin);
      url.searchParams.set('type', type);
      url.searchParams.set('id', String(id));

      const res = await fetch(url.toString(), {
        headers: {
          'Accept': 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
        }
      });

      const data = await safeJson(res);

      if (!res.ok) {
        console.error('GET comments failed:', res.status, data);
        cmList.innerHTML = `<div class="cmLoading">Failed to load comments.</div>`;
        return;
      }

      const comments = data.comments || [];

      if (!comments.length) {
        cmList.innerHTML = `<div class="cmEmpty">No comments yet. Be the first.</div>`;
        return;
      }

      cmList.innerHTML = comments.map(renderCommentItem).join('');
      cmList.scrollTop = cmList.scrollHeight;
    } catch (err) {
      console.error(err);
      cmList.innerHTML = `<div class="cmLoading">Failed to load comments.</div>`;
    }
  }

  // Open modal
  document.addEventListener('click', async (e) => {
    const btn = e.target.closest('.jsCommentOpen');
    if (!btn) return;

    const type = btn.dataset.type;
    const id = btn.dataset.id;

    activeCountElId = btn.dataset.countEl || null;

    cmType.value = type;
    cmId.value = id;

    openModal();
    await loadComments(type, id);
  });

  // Submit comment
  cmForm?.addEventListener('submit', async (e) => {
    e.preventDefault();

    const type = cmType.value;
    const id   = cmId.value;
    const body = cmInput.value.trim();
    if (!body) return;

    cmInput.disabled = true;

    try {
      const res = await fetch('/worker/feed/comments', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'X-CSRF-TOKEN': csrf,
          'X-Requested-With': 'XMLHttpRequest',
        },
        body: JSON.stringify({ type, id, body }),
      });

      const data = await safeJson(res);

      if (!res.ok) {
        console.error('POST comment failed:', res.status, data);
        return;
      }

      const c = data.comment;

      // remove empty/loading/failed text
      cmList.querySelector('.cmEmpty')?.remove();
      cmList.querySelector('.cmLoading')?.remove();

      cmList.insertAdjacentHTML('beforeend', renderCommentItem(c));
      cmList.scrollTop = cmList.scrollHeight;

      cmInput.value = '';

      // update count text (optional)
      if (activeCountElId) {
        const el = document.getElementById(activeCountElId);
        if (el) el.textContent = String((parseInt(el.textContent || '0', 10) || 0) + 1);
      }
    } finally {
      cmInput.disabled = false;
      cmInput.focus();
    }
  });
  // =========================================================
  // IG STORIES VIEWER (tray + modal) + SEEN + MOVE TO END
  // =========================================================
  const tray = document.getElementById('storyTray');
  const viewer = document.getElementById('storyViewer');
  const svProgress = document.getElementById('svProgress');
  const svMedia = document.getElementById('svMedia');
  const svAvatar = document.getElementById('svAvatar');
  const svName = document.getElementById('svName');
  const svTime = document.getElementById('svTime');

  let currentStories = [];
  let currentIndex = 0;
  let timer = null;
  let startAt = 0;
  let durationMs = 6000;
  let paused = false;

  let openedBubble = null; // ✅ remember which employee bubble opened

  function clearTimer() {
    if (timer) { clearInterval(timer); timer = null; }
  }

  function openViewer() {
    viewer.classList.remove('hidden');
    viewer.setAttribute('aria-hidden', 'false');
    document.body.style.overflow = 'hidden';
  }

  function closeViewer() {
    clearTimer();
    paused = false;

    // ✅ if ALL stories were seen, mark bubble gray and move to end
    if (openedBubble && currentStories.length) {
      const allSeen = currentStories.every(s => s.seen === true);
      if (allSeen) openedBubble.classList.add('seen');
      reorderTray();
    }

    openedBubble = null;
    currentStories = [];
    currentIndex = 0;
    svProgress.innerHTML = '';
    svMedia.innerHTML = '';
    viewer.classList.add('hidden');
    viewer.setAttribute('aria-hidden', 'true');
    document.body.style.overflow = '';
  }

  function setProgressBars(count) {
    svProgress.innerHTML = '';
    for (let i = 0; i < count; i++) {
      const bar = document.createElement('div');
      bar.className = 'svBar';
      bar.innerHTML = `<span class="svFill"></span>`;
      svProgress.appendChild(bar);
    }
  }

  function setBarState(i, state) {
    const bars = Array.from(svProgress.querySelectorAll('.svBar'));
    const fill = bars[i]?.querySelector('.svFill');
    if (!fill) return;
    if (state === 'done') fill.style.width = '100%';
    if (state === 'empty') fill.style.width = '0%';
  }

  function animateCurrentBar(progress01) {
    const bars = Array.from(svProgress.querySelectorAll('.svBar'));
    const fill = bars[currentIndex]?.querySelector('.svFill');
    if (!fill) return;
    fill.style.width = `${Math.max(0, Math.min(1, progress01)) * 100}%`;
  }

  async function markSeen(storyId) {
    try {
      await fetch('/worker/feed/stories/seen', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'X-CSRF-TOKEN': csrf,
          'X-Requested-With': 'XMLHttpRequest',
        },
        body: JSON.stringify({ story_id: storyId }),
      });
    } catch (e) {}
  }

  function renderStory(story) {
    svMedia.innerHTML = '';
    svTime.textContent = story.created_at || '';

    if (!story.src) {
      svMedia.innerHTML = `<div class="svEmpty">No media</div>`;
      durationMs = 2500;
      startTimer();
      return;
    }

    if (story.type === 'video') {
      const v = document.createElement('video');
      v.className = 'svVideo';
      v.src = story.src;
      v.autoplay = true;
      v.playsInline = true;
      v.controls = false;

      v.addEventListener('ended', () => nextStory());

      v.addEventListener('loadedmetadata', () => {
        const d = (v.duration && isFinite(v.duration)) ? v.duration * 1000 : 8000;
        durationMs = Math.max(1500, d);
        startTimer();
      });

      svMedia.appendChild(v);
      return;
    }

    const img = document.createElement('img');
    img.className = 'svImg';
    img.src = story.src;
    img.alt = 'story';
    svMedia.appendChild(img);

    durationMs = 6000;
    startTimer();
  }

  function startTimer() {
    clearTimer();
    startAt = Date.now();

    timer = setInterval(() => {
      if (paused) return;

      const elapsed = Date.now() - startAt;
      const p = elapsed / durationMs;
      animateCurrentBar(p);

      if (p >= 1) nextStory();
    }, 50);
  }

  function goTo(index) {
    if (!currentStories.length) return;

    currentIndex = Math.max(0, Math.min(currentStories.length - 1, index));

    for (let i = 0; i < currentStories.length; i++) {
      if (i < currentIndex) setBarState(i, 'done');
      if (i > currentIndex) setBarState(i, 'empty');
    }
    animateCurrentBar(0);

    // pause old videos
    svMedia.querySelectorAll('video').forEach(v => { try { v.pause(); } catch(e){} });

    // ✅ mark seen in DB + locally
    const st = currentStories[currentIndex];
    if (st?.id && st.seen !== true) {
      st.seen = true;
      markSeen(st.id);
    }

    renderStory(st);
  }

function nextStory() {
  clearTimer();

  if (currentIndex >= currentStories.length - 1) {
    // ✅ finished this employee -> go to next employee bubble
    nextBubble();
    return;
  }

  goTo(currentIndex + 1);
}

function prevStory() {
  clearTimer();

  if (currentIndex <= 0) {
    // ✅ at first story -> go to previous employee bubble (last story)
    const i = getOpenedBubbleIndex();
    if (i <= 0) {
      goTo(0);
      return;
    }

    // open previous bubble and jump to last story
    const bubbles = getBubblesOrdered();
    const prev = bubbles[i - 1];
    if (!prev) return goTo(0);

    openedBubble = prev;

    const name = prev.dataset.storyUser || 'Employee';
    const avatar = prev.dataset.storyAvatar || '';
    let stories = [];
    try { stories = JSON.parse(prev.dataset.stories || '[]'); } catch(_) {}
    if (!stories.length) return goTo(0);

    currentStories = stories;

    svName.textContent = name;
    if (avatar) {
      svAvatar.src = avatar;
      svAvatar.style.display = 'block';
    } else {
      svAvatar.style.display = 'none';
      svAvatar.removeAttribute('src');
    }

    setProgressBars(currentStories.length);

    // ✅ go to last story of previous employee
    goTo(currentStories.length - 1);
    return;
  }

  goTo(currentIndex - 1);
}


  // ✅ reorder bubbles: unseen first, seen last
  function reorderTray() {
    if (!tray) return;
    const bubbles = Array.from(tray.querySelectorAll('.storyBubble'));
    bubbles.sort((a, b) => {
      const aSeen = a.classList.contains('seen') ? 1 : 0;
      const bSeen = b.classList.contains('seen') ? 1 : 0;
      return aSeen - bSeen; // unseen (0) first
    });
    bubbles.forEach(b => tray.appendChild(b));
  }

  // Tray click -> open
  tray?.addEventListener('click', (e) => {
    const btn = e.target.closest('.storyBubble');
    if (!btn) return;

    openedBubble = btn; // ✅ remember

    const name = btn.dataset.storyUser || 'Employee';
    const avatar = btn.dataset.storyAvatar || '';
    let stories = [];

    try { stories = JSON.parse(btn.dataset.stories || '[]'); } catch(_) {}

    if (!stories.length) return;

    currentStories = stories;
    currentIndex = 0;

    svName.textContent = name;
    if (avatar) {
      svAvatar.src = avatar;
      svAvatar.style.display = 'block';
    } else {
      svAvatar.style.display = 'none';
      svAvatar.removeAttribute('src');
    }

    setProgressBars(currentStories.length);
    openViewer();
    goTo(0);
  });

  // close
  viewer?.addEventListener('click', (e) => {
    if (e.target.hasAttribute('data-sv-close')) closeViewer();
  });

  // click zones
  viewer?.querySelector('.svLeft')?.addEventListener('click', prevStory);
  viewer?.querySelector('.svRight')?.addEventListener('click', nextStory);

  // hold to pause
  viewer?.addEventListener('pointerdown', () => {
    if (viewer.classList.contains('hidden')) return;
    paused = true;
    const v = svMedia.querySelector('video');
    if (v) { try { v.pause(); } catch(e){} }
  });

  viewer?.addEventListener('pointerup', () => {
    if (viewer.classList.contains('hidden')) return;
    paused = false;

    // resume progress from current fill width
    const fill = svProgress.querySelectorAll('.svFill')[currentIndex];
    const width = parseFloat(fill?.style.width || '0');
    startAt = Date.now() - (width / 100) * durationMs;

    const v = svMedia.querySelector('video');
    if (v) { try { v.play(); } catch(e){} }
  });

  // keyboard
  window.addEventListener('keydown', (e) => {
    if (viewer.classList.contains('hidden')) return;
    if (e.key === 'Escape') closeViewer();
    if (e.key === 'ArrowRight') nextStory();
    if (e.key === 'ArrowLeft') prevStory();
  });


  function getBubblesOrdered() {
  if (!tray) return [];
  return Array.from(tray.querySelectorAll('.storyBubble'));
}

function openBubbleAtIndex(bubbleIndex) {
  const bubbles = getBubblesOrdered();
  const btn = bubbles[bubbleIndex];
  if (!btn) { closeViewer(); return; }

  openedBubble = btn;

  const name = btn.dataset.storyUser || 'Employee';
  const avatar = btn.dataset.storyAvatar || '';
  let stories = [];

  try { stories = JSON.parse(btn.dataset.stories || '[]'); } catch(_) {}
  if (!stories.length) { closeViewer(); return; }

  currentStories = stories;
  currentIndex = 0;

  svName.textContent = name;
  if (avatar) {
    svAvatar.src = avatar;
    svAvatar.style.display = 'block';
  } else {
    svAvatar.style.display = 'none';
    svAvatar.removeAttribute('src');
  }

  setProgressBars(currentStories.length);
  openViewer();
  goTo(0);
}

function getOpenedBubbleIndex() {
  const bubbles = getBubblesOrdered();
  if (!openedBubble) return -1;
  return bubbles.indexOf(openedBubble);
}

function nextBubble() {
  const i = getOpenedBubbleIndex();
  if (i < 0) return closeViewer();
  openBubbleAtIndex(i + 1);
}

function prevBubble() {
  const i = getOpenedBubbleIndex();
  if (i < 0) return closeViewer();
  openBubbleAtIndex(i - 1);
}

})();
