(() => {
  const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';
  const page = document.getElementById('contentPage');

  const apiUrl = page?.dataset.api;
  const commentsUrl = page?.dataset.commentsUrl; // ✅ NEW
  const postTpl = page?.dataset.deletePostTemplate || '';
  const reelTpl = page?.dataset.deleteReelTemplate || '';
  const storyTpl = page?.dataset.deleteStoryTemplate || '';

  const postsList = document.getElementById('postsList');
  const reelsList = document.getElementById('reelsList');
  const storiesList = document.getElementById('storiesList');

  // ---------------- Tabs ----------------
  document.querySelectorAll('.tab').forEach(btn => {
    btn.addEventListener('click', () => {
      document.querySelectorAll('.tab').forEach(b => b.classList.remove('active'));
      btn.classList.add('active');

      const tab = btn.dataset.tab;
      document.querySelectorAll('.tabPane').forEach(p => p.classList.add('hidden'));
      document.getElementById('tab-' + tab)?.classList.remove('hidden');
    });
  });

  // ---------------- Helpers ----------------
  const esc = (s) => (s ?? '').toString()
    .replaceAll('&', '&amp;')
    .replaceAll('<', '&lt;')
    .replaceAll('>', '&gt;')
    .replaceAll('"', '&quot;')
    .replaceAll("'", '&#039;');

  const emptyBox = (text) => `<div class="emptyBox">${esc(text)}</div>`;

  const resolveDeleteUrl = (type, id) => {
    const tpl = type === 'post' ? postTpl : type === 'reel' ? reelTpl : storyTpl;
    return tpl.replace(':id', id);
  };

  const bindDelete = (root) => {
    root.querySelectorAll('.js-delete').forEach(btn => {
      btn.addEventListener('click', async () => {
        const type = btn.dataset.type;
        const id = btn.dataset.id;
        const url = resolveDeleteUrl(type, id);

        if (!url || !id || !type) return;
        if (!confirm('Delete this item?')) return;

        btn.disabled = true;

        try {
          const res = await fetch(url, {
            method: 'DELETE',
            headers: {
              'X-CSRF-TOKEN': csrf,
              'Accept': 'application/json'
            }
          });

          if (!res.ok) {
            const txt = await res.text();
            alert('Failed to delete.\n' + txt);
            btn.disabled = false;
            return;
          }

          btn.closest('.feedCard')?.remove();
        } catch (e) {
          alert('Network error.');
          btn.disabled = false;
        }
      });
    });
  };

  const ensureGrid = (el) => {
    if (!el) return;
    el.classList.add('items', 'gridCards');
  };

  // ---------------- Comments Modal ----------------
  const modal = document.getElementById('commentsModal');
  const modalBody = document.getElementById('commentsModalBody');

  const openModal = () => {
    if (!modal) return;
    modal.classList.remove('hidden');
    modal.setAttribute('aria-hidden', 'false');
  };

  const closeModal = () => {
    if (!modal) return;
    modal.classList.add('hidden');
    modal.setAttribute('aria-hidden', 'true');
  };

  if (modal) {
    modal.addEventListener('click', (e) => {
      if (e.target?.dataset?.close === '1') closeModal();
    });
    document.addEventListener('keydown', (e) => {
      if (e.key === 'Escape' && !modal.classList.contains('hidden')) closeModal();
    });
  }

  const renderComments = (comments) => {
    if (!modalBody) return;

    if (!comments?.length) {
      modalBody.innerHTML = `<div class="muted">No comments yet.</div>`;
      return;
    }

    modalBody.innerHTML = comments.map(c => `
      <div class="cItem">
        <div class="cTop">
          <div class="cName">${esc(c.user_name || 'User')}</div>
          <div class="cDate">${esc(c.created_at_formatted || '')}</div>
        </div>
        <div class="cText">${esc(c.body || '')}</div>
      </div>
    `).join('');
  };

  const loadComments = async (type, id) => {
    if (!commentsUrl || !modalBody) return;

    modalBody.innerHTML = `<div class="muted">Loading...</div>`;
    openModal();

    try {
      const url = new URL(commentsUrl, window.location.origin);
      url.searchParams.set('type', type);
      url.searchParams.set('id', id);

      const res = await fetch(url.toString(), {
        headers: { 'Accept': 'application/json' }
      });

      if (!res.ok) {
        const txt = await res.text();
        modalBody.innerHTML = `<div class="muted">Failed to load comments.</div><pre class="muted" style="white-space:pre-wrap">${esc(txt)}</pre>`;
        return;
      }

      const data = await res.json();
      renderComments(data.comments || []);
    } catch (e) {
      modalBody.innerHTML = `<div class="muted">Network error while loading comments.</div>`;
    }
  };

  // Event delegation: click on 💬
  const bindCommentClicks = (root) => {
    if (!root) return;

    root.querySelectorAll('.js-comments').forEach(btn => {
      btn.addEventListener('click', () => {
        const type = btn.dataset.type;
        const id = btn.dataset.id;
        if (!type || !id) return;
        loadComments(type, id);
      });
    });
  };

  // ---------------- Stats UI ----------------
  const renderStats = (type, id, likes, comments) => {
    const l = Number(likes || 0);
    const c = Number(comments || 0);
    return `
      <div class="feedStats">
        <span class="statItem">❤️ ${l}</span>
        <button type="button" class="statItem statBtn js-comments" data-type="${esc(type)}" data-id="${esc(id)}">💬 ${c}</button>
      </div>
    `;
  };

  // ---------------- Render POSTS ----------------
  const renderPosts = (posts) => {
    ensureGrid(postsList);

    if (!posts?.length) {
      postsList.innerHTML = emptyBox('No posts yet.');
      return;
    }

    postsList.innerHTML = posts.map(p => `
      <article class="feedCard">
        ${p.media_url ? `
          <div class="feedMedia">
            <img src="${esc(p.media_url)}" alt="post media">
          </div>
        ` : `
          <div class="feedMedia"></div>
        `}

        <div class="feedBody">
          <div class="feedCardTop">
            <div class="feedTitle">${esc(p.title)}</div>

            <div class="feedRight">
              <div class="feedDate">${esc(p.created_at_formatted || '')}</div>
              <button class="feedDeleteBtn js-delete" data-type="post" data-id="${esc(p.id)}">Delete</button>
            </div>
          </div>

          <div class="feedText">${esc(p.content)}</div>

          ${renderStats('post', p.id, p.likes_count, p.comments_count)}
        </div>
      </article>
    `).join('');

    bindDelete(postsList);
    bindCommentClicks(postsList); // ✅ NEW
  };

  // ---------------- Render REELS ----------------
  const renderReels = (reels) => {
    ensureGrid(reelsList);

    if (!reels?.length) {
      reelsList.innerHTML = emptyBox('No reels yet.');
      return;
    }

    reelsList.innerHTML = reels.map(r => `
      <article class="feedCard">
        ${r.video_url ? `
          <div class="feedMedia">
            <video controls preload="metadata">
              <source src="${esc(r.video_url)}">
            </video>
          </div>
        ` : `
          <div class="feedMedia"></div>
        `}

        <div class="feedBody">
          <div class="feedCardTop">
            <div class="feedTitle">Reel</div>

            <div class="feedRight">
              <div class="feedDate">${esc(r.created_at_formatted || '')}</div>
              <button class="feedDeleteBtn js-delete" data-type="reel" data-id="${esc(r.id)}">Delete</button>
            </div>
          </div>

          ${r.caption
            ? `<div class="feedSubText">${esc(r.caption)}</div>`
            : `<div class="feedSubText muted">No caption</div>`
          }

          ${renderStats('reel', r.id, r.likes_count, r.comments_count)}
        </div>
      </article>
    `).join('');

    bindDelete(reelsList);
    bindCommentClicks(reelsList); // ✅ NEW
  };

  // ---------------- Render STORIES ----------------
  const renderStories = (stories) => {
    ensureGrid(storiesList);

    if (!stories?.length) {
      storiesList.innerHTML = emptyBox('No stories yet.');
      return;
    }

    storiesList.innerHTML = stories.map(s => `
      <article class="feedCard">
        ${s.media_url ? `
          <div class="feedMedia">
            ${
              s.media_type === 'video'
                ? `<video controls preload="metadata"><source src="${esc(s.media_url)}"></video>`
                : `<img src="${esc(s.media_url)}" alt="story media">`
            }
          </div>
        ` : `
          <div class="feedMedia"></div>
        `}

        <div class="feedBody">
          <div class="feedCardTop">
            <div class="feedTitle">Story</div>

            <div class="feedRight">
              <div class="feedDate">${esc(s.created_at_formatted || '')}</div>
              <button class="feedDeleteBtn js-delete" data-type="story" data-id="${esc(s.id)}">Delete</button>
            </div>
          </div>

          <div class="feedSubText">
            ${s.expires_at_formatted ? `Expires: ${esc(s.expires_at_formatted)}` : `Expires: (default)`}
          </div>
        </div>
      </article>
    `).join('');

    bindDelete(storiesList);
  };

  // ---------------- Load JSON ----------------
  const load = async () => {
    if (!apiUrl) return;

    try {
      const res = await fetch(apiUrl, { headers: { 'Accept': 'application/json' } });

      if (!res.ok) {
        postsList.innerHTML = emptyBox('Failed to load posts.');
        reelsList.innerHTML = emptyBox('Failed to load reels.');
        storiesList.innerHTML = emptyBox('Failed to load stories.');
        return;
      }

      const data = await res.json();
      renderPosts(data.posts || []);
      renderReels(data.reels || []);
      renderStories(data.stories || []);
    } catch (e) {
      postsList.innerHTML = emptyBox('Network error while loading posts.');
      reelsList.innerHTML = emptyBox('Network error while loading reels.');
      storiesList.innerHTML = emptyBox('Network error while loading stories.');
    }
  };

  load();
})();
