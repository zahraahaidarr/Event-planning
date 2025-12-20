
{{-- resources/views/worker/follow-employees.blade.php --}}
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Follow Employees</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">

  <link rel="stylesheet" href="{{ asset('css/worker/follow-employees.css') }}">
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
  <main  id="followPage">
    <div class="follow-inner">

      <div class="page-top">
        <div>
          <h1 class="page-title">Follow Employees</h1>
          <p class="page-subtitle">Search and follow employees to see their events in your feed.</p>
        </div>

        <a class="btn btn-ghost" href="{{ route('worker.feed.index') }}">
          Go to Feed
        </a>
      </div>

      <div class="card">
        <div class="toolbar">
          <div class="search-wrap">
            <span class="search-ico">🔎</span>
            <input id="searchInput" type="text" placeholder="Search by name or email..." />
          </div>

          <button id="refreshBtn" type="button" class="btn btn-solid">Refresh</button>
        </div>

        <div id="statusLine" class="statusLine"></div>
        <div id="employeesList" class="list"></div>
      </div>

    </div>
  </main>

  <script src="{{ asset('js/worker/follow-employees.js') }}"></script>
</body>
</html>
