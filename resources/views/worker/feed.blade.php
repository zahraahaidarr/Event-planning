<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Feed</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    {{-- CSS --}}
    <link rel="stylesheet" href="{{ asset('css/worker/feed.css') }}">
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
        <a href="{{ route('worker.follow.index') }}"
   class="nav-item {{ request()->routeIs('worker.following.*') ? 'active' : '' }}">
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

        @if(Route::has('logout'))
          <form method="POST" action="{{ route('logout') }}" style="margin-top:10px;">
            @csrf
            <button type="submit" class="nav-item" style="border:none;background:transparent;width:100%;text-align:left;">
              
            </button>
          </form>
        @endif
      </nav>
    </aside>
<div id="feedPage">

    <header id="feedHeader">
        <h2>Feed (From Employees You Follow)</h2>

        <a id="goFollowBtn" href="{{ route('worker.follow.index') }}">
            Follow Employees
        </a>
    </header>

    <nav id="feedTabs">
        <button class="feedTab active" data-tab="events">Events</button>
        <button class="feedTab" data-tab="posts">Posts</button>
        <button class="feedTab" data-tab="reels">Reels</button>
        <button class="feedTab" data-tab="stories">Stories</button>
    </nav>

    <main id="feedContent">

        {{-- EVENTS --}}
        <section class="tabPane" id="tab-events">
            @forelse($events as $event)
                <article class="feedCard">
                    <div class="feedCardTop">
                        <div class="feedTitle">
                            {{ $event->title ?? 'Event' }}
                        </div>
                        <div class="feedDate">
                            {{ optional($event->created_at)->format('Y-m-d H:i') }}
                        </div>
                    </div>

                    <div class="feedBody">
                        <div>
                            <strong>Location:</strong>
                            {{ $event->location ?? '-' }}
                        </div>
                        <div>
                            <strong>Description:</strong>
                            {{ $event->description ?? '-' }}
                        </div>
                    </div>
                </article>
            @empty
                <div class="emptyBox">
                    No events yet. Follow employees to see their events.
                </div>
            @endforelse

            <div class="pagerBox">
                {{ $events->links() }}
            </div>
        </section>

        {{-- POSTS --}}
        <section class="tabPane hidden" id="tab-posts">
            <div class="emptyBox">
                Posts will appear here (employee_posts).
            </div>
        </section>

        {{-- REELS --}}
        <section class="tabPane hidden" id="tab-reels">
            <div class="emptyBox">
                Reels will appear here (employee_reels).
            </div>
        </section>

        {{-- STORIES --}}
        <section class="tabPane hidden" id="tab-stories">
            <div class="emptyBox">
                Stories will appear here (employee_stories).
            </div>
        </section>

    </main>
</div>

{{-- JS --}}
<script>
document.querySelectorAll('.feedTab').forEach(btn => {
    btn.addEventListener('click', () => {
        document.querySelectorAll('.feedTab').forEach(b =>
            b.classList.remove('active')
        );
        btn.classList.add('active');

        const tab = btn.dataset.tab;

        document.querySelectorAll('.tabPane').forEach(p =>
            p.classList.add('hidden')
        );

        document.getElementById('tab-' + tab)
            .classList.remove('hidden');
    });
});
</script>

</body>
</html>
