(() => {
  const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';
  const page = document.getElementById('contentPage');

  const apiUrl = page?.dataset.api;
  const postTpl = page?.dataset.deletePostTemplate || '';
  const reelTpl = page?.dataset.deleteReelTemplate || '';
  const storyTpl = page?.dataset.deleteStoryTemplate || '';

  const postsList = document.getElementById('postsList');
  const reelsList = document.getElementById('reelsList');
  const storiesList = document.getElementById('storiesList');

  // Tabs
  document.querySelectorAll('.tab').forEach(btn => {
    btn.addEventListener('click', () => {
      document.querySelectorAll('.tab').forEach(b => b.classList.remove('active'));
      btn.classList.add('active');

      const tab = btn.dataset.tab;
      document.querySelectorAll('.tabPane').forEach(p => p.classList.add('hidden'));
      document.getElementById('tab-' + tab)?.classList.remove('hidden');
    });
  });

  // Helpers
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

  // Make list a grid
  const ensureGrid = (el) => {
    if (!el) return;
    el.classList.add('items', 'gridCards');
  };

  // Render POSTS (grid cards)
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
        </div>
      </article>
    `).join('');

    bindDelete(postsList);
  };

  // Render REELS (grid cards, video not cropped)
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

          ${r.caption ? `<div class="feedSubText">${esc(r.caption)}</div>` : `<div class="feedSubText muted">No caption</div>`}
        </div>
      </article>
    `).join('');

    bindDelete(reelsList);
  };

  // Render STORIES (grid cards, image/video not cropped)
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

  // Load JSON
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
