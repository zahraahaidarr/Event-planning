<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>My Feed</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <meta name="csrf-token" content="{{ csrf_token() }}">

  <link rel="stylesheet" href="{{ asset('css/worker/feed.css') }}?v={{ filemtime(public_path('css/worker/feed.css')) }}">
  <script defer src="{{ asset('js/worker/feed.js') }}?v={{ filemtime(public_path('js/worker/feed.js')) }}"></script>
</head>

<body>
  <aside class="sidebar">
    @php
      $user   = Auth::user();
      $worker = optional($user)->worker;
      $roleLabel = $worker ? ($worker->is_volunteer ? 'VOLUNTEER' : 'WORKER') : 'WORKER';
    @endphp

    <div class="logo">
      <a href="{{ Route::has('profile') ? route('profile') : '#' }}" class="logo-link">
        @if($user && $user->avatar_path)
          <img
            src="{{ asset('storage/' . ltrim($user->avatar_path, '/')) }}"
            alt="{{ $user->first_name ?? $user->name ?? 'Profile' }}"
            class="logo-avatar"
          >
        @else
          <div class="logo-icon">
            {{ strtoupper(substr($user->first_name ?? $user->name ?? 'U', 0, 1)) }}
          </div>
        @endif

        <div class="logo-id">
          <div class="logo-name">
            {{ trim(($user->first_name ?? '').' '.($user->last_name ?? '')) ?: ($user->name ?? 'User') }}
          </div>
          <div class="logo-role">{{ $roleLabel }}</div>
        </div>
      </a>
    </div>

    <nav class="nav-section">
      <a href="{{ route('worker.dashboard') }}" class="nav-item {{ request()->routeIs('worker.dashboard') ? 'active' : '' }}">
        <span class="nav-icon">🏠</span><span>Dashboard</span>
      </a>
      <a href="{{ route('worker.events.discover') }}" class="nav-item {{ request()->routeIs('worker.events.discover*') ? 'active' : '' }}">
        <span class="nav-icon">🗓️</span><span>Discover Events</span>
      </a>
      <a href="{{ route('worker.follow.index') }}" class="nav-item {{ request()->routeIs('worker.follow.*') ? 'active' : '' }}">
        <span class="nav-icon">👥</span><span>Follow Employees</span>
      </a>
      <a href="{{ route('worker.reservations') }}" class="nav-item {{ request()->routeIs('worker.reservations*') ? 'active' : '' }}">
        <span class="nav-icon">✅</span><span>My Reservations</span>
      </a>
      <a href="{{ route('worker.submissions') }}" class="nav-item {{ request()->routeIs('worker.submissions*') ? 'active' : '' }}">
        <span class="nav-icon">📝</span><span>Post-Event Submissions</span>
      </a>
    </nav>

    <nav class="nav-section">
      <div class="nav-label">Account</div>

      @if($worker && !$worker->is_volunteer)
        <a href="{{ route('worker.payments.index') }}" class="nav-item {{ request()->routeIs('worker.payments.index') ? 'active' : '' }}">
          <span class="nav-icon">💰</span><span>Payments</span>
        </a>
      @endif

      <a href="{{ route('worker.messages') }}" class="nav-item {{ request()->routeIs('worker.messages*') ? 'active' : '' }}">
        <span class="nav-icon">💬</span><span>Chat</span>
      </a>

      <a href="{{ route('worker.announcements.index') }}" class="nav-item {{ request()->routeIs('worker.announcements.*') ? 'active' : '' }}">
        <span class="nav-icon">📢</span><span>Announcements</span>
      </a>

      <a href="{{ route('settings') }}" class="nav-item {{ request()->routeIs('settings') ? 'active' : '' }}">
        <span class="nav-icon">⚙️</span><span>Settings</span>
      </a>
    </nav>
  </aside>

  <div id="feedPage">
    <header id="feedHeader">
      <h2>Feed (From Employees You Follow)</h2>
      <a id="goFollowBtn" href="{{ route('worker.follow.index') }}">Follow Employees</a>
    </header>

    <nav id="feedTabs">
      <button type="button" class="feedTab active" data-tab="events">Events</button>
      <button type="button" class="feedTab" data-tab="posts">Posts</button>
      <button type="button" class="feedTab" data-tab="reels">Reels</button>
      <button type="button" class="feedTab" data-tab="stories">Stories</button>
    </nav>

    <main id="feedContent">

      {{-- EVENTS --}}
      <section class="tabPane" id="tab-events">
        @forelse($events as $event)
          <article class="feedCard">
            <div class="feedCardTop">
              <div class="feedTitle">{{ $event->title ?? 'Event' }}</div>
              <div class="feedDate">{{ optional($event->created_at)->format('Y-m-d H:i') }}</div>
            </div>

            <div class="feedBody">
              <div><strong>Location:</strong> {{ $event->location ?? '-' }}</div>
              <div><strong>Description:</strong> {{ $event->description ?? '-' }}</div>
            </div>
          </article>
        @empty
          <div class="emptyBox">No events yet. Follow employees to see their events.</div>
        @endforelse
      </section>

      {{-- POSTS --}}
      <section class="tabPane hidden" id="tab-posts">
        <div class="igStage" data-stage="posts">
          <button type="button" class="igArrow left" data-prev="posts" aria-label="Previous post">‹</button>

          <div class="igViewport" data-viewport="posts">
            @forelse($posts as $i => $p)
              @php
                $liked = isset($p->likes) && $p->likes->isNotEmpty();
                $likesCount = $p->likes_count ?? ($p->likes->count() ?? 0);
                $commentsCount = $p->comments_count ?? ($p->comments->count() ?? 0);
              @endphp

              <article class="igCard {{ $i === 0 ? 'active' : '' }}" data-slide="posts" data-index="{{ $i }}">
                <div class="igTop">
                  <div class="igTitle">{{ $p->title }}</div>
                  <div class="igMeta">{{ $p->created_at?->format('Y-m-d H:i') }}</div>
                </div>

                <div class="igMedia">
                  @if($p->media_path)
                    <img src="{{ asset('storage/' . ltrim($p->media_path, '/')) }}" alt="post media" loading="lazy">
                  @else
                    <div class="igMediaEmpty">No image</div>
                  @endif
                </div>

                {{-- ❤️ + 💬 actions --}}
                <div class="igActions">
                  <button type="button"
                          class="igActionBtn igLikeBtn {{ $liked ? 'liked' : '' }}"
                          data-like-type="post"
                          data-like-id="{{ $p->id }}"
                          aria-label="Like">♥</button>

                  {{-- ✅ open modal comments --}}
                  <button type="button"
                          class="igActionBtn jsCommentOpen"
                          data-type="post"
                          data-id="{{ $p->id }}"
                          data-count-el="post-comment-count-{{ $p->id }}"
                          aria-label="Comments">💬</button>

                  <div class="igCounts">
                    <span class="igLikeCount" data-like-count="post-{{ $p->id }}">{{ $likesCount }}</span> likes ·
                    <span id="post-comment-count-{{ $p->id }}">{{ $commentsCount }}</span> comments
                  </div>
                </div>

                <div class="igBody">
                  <div class="igText">{{ $p->content }}</div>
                </div>
              </article>
            @empty
              <div class="emptyBox">No posts yet.</div>
            @endforelse
          </div>

          <button type="button" class="igArrow right" data-next="posts" aria-label="Next post">›</button>
        </div>

        @if($posts->count())
          <div class="igDots" data-dots="posts">
            @foreach($posts as $i => $p)
              <button type="button" class="dot {{ $i===0?'active':'' }}" data-go="posts" data-index="{{ $i }}"></button>
            @endforeach
          </div>
        @endif
      </section>

      {{-- REELS --}}
      <section class="tabPane hidden" id="tab-reels">
        <div class="igStage" data-stage="reels">
          <button type="button" class="igArrow left" data-prev="reels" aria-label="Previous reel">‹</button>

          <div class="igViewport" data-viewport="reels">
            @forelse($reels as $i => $r)
              @php
                $liked = isset($r->likes) && $r->likes->isNotEmpty();
                $likesCount = $r->likes_count ?? ($r->likes->count() ?? 0);
                $commentsCount = $r->comments_count ?? ($r->comments->count() ?? 0);
              @endphp

              <article class="igCard {{ $i === 0 ? 'active' : '' }}" data-slide="reels" data-index="{{ $i }}">
                <div class="igTop">
                  <div class="igTitle">Reel</div>
                  <div class="igMeta">{{ optional($r->created_at)->format('Y-m-d H:i') }}</div>
                </div>

                <div class="igMedia">
                  @if($r->video_path)
                    <video class="igVideo" controls>
                      <source src="{{ asset('storage/' . ltrim($r->video_path,'/')) }}">
                    </video>
                  @else
                    <div class="igMediaEmpty">No video</div>
                  @endif
                </div>

                {{-- ❤️ + 💬 actions --}}
                <div class="igActions">
                  <button type="button"
                          class="igActionBtn igLikeBtn {{ $liked ? 'liked' : '' }}"
                          data-like-type="reel"
                          data-like-id="{{ $r->id }}"
                          aria-label="Like">♥</button>

                  {{-- ✅ open modal comments --}}
                  <button type="button"
                          class="igActionBtn jsCommentOpen"
                          data-type="reel"
                          data-id="{{ $r->id }}"
                          data-count-el="reel-comment-count-{{ $r->id }}"
                          aria-label="Comments">💬</button>

                  <div class="igCounts">
                    <span class="igLikeCount" data-like-count="reel-{{ $r->id }}">{{ $likesCount }}</span> likes ·
                    <span id="reel-comment-count-{{ $r->id }}">{{ $commentsCount }}</span> comments
                  </div>
                </div>

                <div class="igBody">
                  @if($r->caption)
                    <div class="igText">{{ $r->caption }}</div>
                  @else
                    <div class="igText muted">No caption</div>
                  @endif
                </div>
              </article>
            @empty
              <div class="emptyBox">No reels yet.</div>
            @endforelse
          </div>

          <button type="button" class="igArrow right" data-next="reels" aria-label="Next reel">›</button>
        </div>

        @if($reels->count())
          <div class="igDots" data-dots="reels">
            @foreach($reels as $i => $r)
              <button type="button" class="dot {{ $i===0?'active':'' }}" data-go="reels" data-index="{{ $i }}"></button>
            @endforeach
          </div>
        @endif
      </section>

{{-- STORIES --}}
<section class="tabPane hidden" id="tab-stories">

  <div class="storyTray" id="storyTray">
    @php
      // ✅ Sort employee bubbles: unseen first, fully-seen last
      $grouped = $stories
        ->groupBy('employee_user_id')
        ->sortBy(function($items){
            return $items->every(fn($st) => (bool)($st->seen_by_me ?? false)) ? 1 : 0;
        });
    @endphp

    @forelse($grouped as $employeeId => $items)
      @php
        $u = optional($items->first())->employeeUser;
        $name = trim(($u->first_name ?? '').' '.($u->last_name ?? '')) ?: 'Employee';
        $avatar = $u && $u->avatar_path ? asset('storage/' . ltrim($u->avatar_path,'/')) : null;

        // ✅ If ALL stories for this employee are seen => gray ring
        $allSeen = $items->every(fn($st) => (bool)($st->seen_by_me ?? false));

        // ✅ payload includes seen flag per story
        $payload = $items->map(function($s){
          $path = $s->media_path ?? '';
          $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
          $isVideo = in_array($ext, ['mp4','mov','webm']);

          return [
            'id' => $s->id,
            'type' => $isVideo ? 'video' : 'image',
            'src' => $path ? asset('storage/' . ltrim($path,'/')) : null,
            'created_at' => optional($s->created_at)->format('Y-m-d H:i'),
            'expires_at' => $s->expires_at ? $s->expires_at->format('Y-m-d H:i') : null,
            'seen' => (bool)($s->seen_by_me ?? false),
          ];
        })->values();
      @endphp

      <button
        type="button"
        class="storyBubble {{ $allSeen ? 'seen' : '' }}"
        data-story-user="{{ $name }}"
        data-story-avatar="{{ $avatar ?? '' }}"
        data-stories='@json($payload)'
        aria-label="Open story for {{ $name }}"
      >
        <span class="storyRing"></span>

        @if($avatar)
          <img class="storyAvatar" src="{{ $avatar }}" alt="{{ $name }}">
        @else
          <span class="storyAvatarFallback">{{ strtoupper(substr($name,0,1)) }}</span>
        @endif

        <span class="storyName">{{ $name }}</span>
      </button>
    @empty
      <div class="emptyBox">No stories yet.</div>
    @endforelse
  </div>

  {{-- Viewer --}}
  <div class="storyViewer hidden" id="storyViewer" aria-hidden="true">
    <div class="svOverlay" data-sv-close></div>

    <div class="svCard" role="dialog" aria-modal="true">
      <div class="svProgress" id="svProgress"></div>

      <div class="svTop">
        <div class="svUser">
          <img id="svAvatar" class="svAvatar" src="" alt="avatar">
          <div class="svUserMeta">
            <div id="svName" class="svName"></div>
            <div id="svTime" class="svTime"></div>
          </div>
        </div>

        <button type="button" class="svClose" data-sv-close aria-label="Close">×</button>
      </div>

      <div class="svMedia" id="svMedia"></div>

      <button type="button" class="svZone svLeft" aria-label="Previous story"></button>
      <button type="button" class="svZone svRight" aria-label="Next story"></button>
    </div>
  </div>

</section>



    </main>
  </div>

  {{-- ✅ Comments Modal (Instagram-style) --}}
  <div id="commentModal" class="cm hidden" aria-hidden="true">
    <div class="cmOverlay" data-cm-close></div>

    <div class="cmDialog" role="dialog" aria-modal="true" aria-labelledby="cmTitle">
      <div class="cmHead">
        <div id="cmTitle" class="cmTitle">Comments</div>
        <button type="button" class="cmClose" data-cm-close aria-label="Close">×</button>
      </div>

      <div class="cmBody">
        <div id="cmList" class="cmList"></div>
      </div>

      <form id="cmForm" class="cmForm">
        <input type="hidden" id="cmType" name="type">
        <input type="hidden" id="cmId" name="id">

        <input id="cmInput" name="body" class="cmInput" type="text"
               placeholder="Add a comment..." autocomplete="off" maxlength="500" required>

        <button class="cmSend" type="submit">Send</button>
      </form>
    </div>
  </div>

</body>
</html>
