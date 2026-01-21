{{-- resources/views/Worker/dashboard.blade.php --}}
<!doctype html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale()==='ar' ? 'rtl' : 'ltr' }}" data-theme="dark">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Worker • Dashboard</title>

  <meta name="csrf-token" content="{{ csrf_token() }}">

  <link rel="stylesheet" href="{{ asset('css/worker/dashboard.css') }}">
  <script src="{{ asset('js/preferences.js') }}" defer></script>
  <script src="{{ asset('js/worker/dashboard.js') }}" defer></script>
</head>

<body data-theme="dark">
  <div class="wrap">

    {{-- Sidebar --}}
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
       

        <a href="{{ route('worker.reservations') }}" class="nav-item {{ request()->routeIs('worker.reservations*') ? 'active' : '' }}">
          <span class="nav-icon">✅</span><span>My Reservations</span>
        </a>
        <a href="{{ route('worker.submissions') }}" class="nav-item {{ request()->routeIs('worker.submissions*') ? 'active' : '' }}">
          <span class="nav-icon">📝</span><span>Post-Event Submissions</span>
        </a>
         <a href="{{ route('worker.follow.index') }}"
   class="nav-item {{ request()->routeIs('worker.following.*') ? 'active' : '' }}">
  <span class="nav-icon">👥</span><span>Follow clients</span>
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

    {{-- Main --}}
    <main class="content" id="main">

      {{-- Topbar --}}
      <div class="topbar">
        <div class="search" role="search">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none">
            <path d="m21 21-4.2-4.2M10.8 18a7.2 7.2 0 1 1 0-14.4 7.2 7.2 0 0 1 0 14.4Z"
              stroke="currentColor" stroke-width="1.6" opacity=".55"/>
          </svg>
          <input id="globalSearch" placeholder="Search this week…" value="{{ request('q') }}" aria-label="Search this week events">
        </div>

        <div class="bar-actions">
          <button class="btn" onclick="window.location='{{ route('worker.events.discover') }}'">Find Events</button>

          @if(Route::has('logout'))
            <form method="POST" action="{{ route('logout') }}">
              @csrf
              <button type="submit" class="btn danger">Logout</button>
            </form>
          @endif
        </div>
      </div>

      {{-- Hero --}}
      <section class="hero" aria-labelledby="heroTitle">
        <div>
          <h1 id="heroTitle">Welcome <span id="volName">{{ Auth::user()->name ?? 'Worker' }}</span> 👋</h1>
          <p id="heroSubtitle">Browse events that match your role and location, track your reservations, and submit post-event reports on time.</p>
          <div class="cta">
            <button class="btn" onclick="window.location='{{ route('worker.events.discover') }}'">Discover Events</button>
            <button class="btn secondary" onclick="window.location='{{ route('worker.announcements.index') }}'">View Announcements</button>
          </div>
        </div>
      </section>

      {{-- KPIs --}}
      <section class="metrics" aria-label="Key metrics">
        <div class="kpi"><small>Upcoming Events</small><div class="value">{{ $upcomingEventsCount }}</div><span class="pill">Next 14 days</span></div>
        <div class="kpi"><small>Reserved</small><div class="value">{{ $reservedCount }}</div><span class="pill">Confirmed</span></div>
        <div class="kpi"><small>Completed</small><div class="value">{{ $completedAwaitingReviewCount }}</div><span class="pill">Event finished</span></div>
        <div class="kpi"><small>Hours Worked</small><div class="value">{{ rtrim(rtrim(number_format($hoursVolunteered, 1), '0'), '.') }}h</div><span class="pill">Approved hours</span></div>
      </section>

      {{-- This week --}}
      <section>
        <div class="section-head">
          <h2>This Week’s Events</h2>

          <form class="filter-row" method="GET" action="{{ route('worker.dashboard') }}" id="filtersForm">
            <select id="filterType" name="type_id">
              <option value="">Type</option>
              @foreach($types as $t)
                <option value="{{ $t->category_id }}" {{ (string)request('type_id') === (string)$t->category_id ? 'selected' : '' }}>
                  {{ $t->name }}
                </option>
              @endforeach
            </select>

            <select id="filterCategory" name="role_type_id">
              <option value="">Category</option>
              @foreach($roleTypes as $rt)
                <option value="{{ $rt->role_type_id }}" {{ (string)request('role_type_id') === (string)$rt->role_type_id ? 'selected' : '' }}>
                  {{ $rt->name }}
                </option>
              @endforeach
            </select>

            <input class="input" type="date" id="filterDate" name="date" value="{{ request('date') }}">

            <select id="filterLocation" name="location">
              <option value="">Location</option>
              @foreach($locations as $loc)
                <option value="{{ $loc }}" {{ request('location') === $loc ? 'selected' : '' }}>
                  {{ $loc }}
                </option>
              @endforeach
            </select>

            <input type="hidden" name="q" id="qHidden" value="{{ request('q') }}">
          </form>
        </div>

        <div class="grid" id="eventGrid" aria-live="polite">
          @forelse($thisWeekEvents as $e)
            @php
              $myRes = $myReservationsByEvent[$e->event_id] ?? null;
              $badge = $myRes ? $myRes->status : 'OPEN';
            @endphp

            <div class="card">
              <div class="card-banner" style="background-image:url('{{ $e->image_url }}');">
                <span class="chip">{{ $badge }}</span>
              </div>

              <div class="card-body">
                <div class="card-title">{{ $e->title ?? 'Event' }}</div>
                <div class="meta">
                  <span>📍 {{ $e->location ?? '—' }}</span>
                  <span>🗓️ {{ $e->starts_at ? $e->starts_at->format('Y-m-d') : '—' }}</span>
                </div>

                <div class="actions">
                  <a class="btn small" href="{{ route('worker.events.discover') }}">View</a>
                </div>
              </div>
            </div>

          @empty
            <div class="panel" style="grid-column:1/-1;">No events found for this week.</div>
          @endforelse
        </div>
      </section>
    </main>

    {{-- Right rail --}}
<!-- Right Rail -->
<aside class="rail" aria-label="Right rail">

  {{-- Profile --}}
  <div class="panel">
    <div class="profile">
      <div>
        <div style="font-weight:700">{{ Auth::user()->name }}</div>
        <div class="meta">
  <span id="railRole">
    Role: {{ $worker?->roleType?->name ?? '—' }}
  </span>
</div>

      </div>
    </div>
<div class="rating-box">

  <div class="rating-head">
    <strong>My Rating</strong>

    @if(isset($avgWorkerRating) && $avgWorkerRating !== null)
      <span class="rating-score">{{ number_format($avgWorkerRating, 2) }}/5</span>

    @else
      <span class="meta">—</span>
    @endif
  </div>

  @if(isset($avgWorkerRating) && $avgWorkerRating !== null)
    <div class="rating-sub">
      Average rating from clients
    </div>

    @php
      $full = (int) floor($avgWorkerRating);
    @endphp

    <div class="stars">
      @for ($i = 1; $i <= 5; $i++)
        <span class="star {{ $i <= $full ? 'filled' : '' }}">★</span>
      @endfor
    </div>
  @else
    <div class="rating-sub">
      No ratings yet
    </div>
  @endif

</div>


  </div>

  {{-- ✅ NEXT EVENT (MOVED HERE) --}}
  {{-- Next Event (REAL) --}}
<div class="panel countdown" id="nextEventPanel">
  @php
    $nextEvent = $nextReservation?->event;
    $nextStatus = $nextReservation?->status;

    $chipClass = 'chip-pending';
    $chipText  = 'Pending';

    if ($nextStatus === 'RESERVED' || $nextStatus === 'CHECKED_IN') {
      $chipClass = 'chip-accepted';
      $chipText  = 'Accepted';
    } elseif ($nextStatus === 'PENDING') {
      $chipClass = 'chip-pending';
      $chipText  = 'Pending';
    }
  @endphp

  <div style="display:flex;justify-content:space-between;align-items:center">
    <strong>Next Event</strong>
    <span class="chip-status {{ $chipClass }}">{{ $chipText }}</span>
  </div>

  @if($nextEvent)
    <div style="margin-top:6px;font-weight:700">
      {{ $nextEvent->title }}
    </div>

    <div class="meta">
      {{ $nextEvent->location ?? '—' }}
      •
      {{ $nextEvent->starts_at ? $nextEvent->starts_at->format('m/d/Y, H:i') : '—' }}
    </div>

    <div style="margin-top:10px;display:flex;gap:8px;flex-wrap:wrap">
      <a class="btn small" href="{{ route('worker.events.discover') }}">View</a>

      {{-- If you have a cancel route, put it here. Otherwise keep it pointing to reservations page --}}
      <a class="btn small ghost" href="{{ route('worker.reservations') }}">Cancel (before deadline)</a>
    </div>
  @else
    <div style="margin-top:6px;font-weight:700">—</div>
    <div class="meta">No upcoming accepted/pending reservations.</div>
  @endif
</div>


  {{-- Announcements --}}
  <div class="panel">
    <strong>Announcements</strong>

    <div class="list" style="margin-top:10px">
      @forelse($recentAnnouncements as $a)
        <div class="announce-item">
          <div>📣</div>
          <div>
            <div style="font-weight:600">{{ $a->title }}</div>
            <div class="time">{{ $a->created_at->format('Y-m-d') }}</div>
          </div>
        </div>
      @empty
        <div class="announce-item">
          <div>📣</div>
          <div>
            <div>—</div>
            <div class="time">No announcements in the last 2 weeks</div>
          </div>
        </div>
      @endforelse
    </div>
  </div>

</aside>


  </div>

  @include('notify.widget')
  <script src="{{ asset('js/notify-poll.js') }}" defer></script>
</body>
</html>
