{{-- resources/views/Worker/dashboard.blade.php --}}
<!doctype html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale()==='ar' ? 'rtl' : 'ltr' }}">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Worker • Dashboard</title>
<script src="{{ asset('js/preferences.js') }}" defer></script>
  {{-- If you’re not using Vite, keep asset() with files under public/ --}}
  <link rel="stylesheet" href="{{ asset('css/worker/dashboard.css') }}">
  <script src="{{ asset('js/worker/dashboard.js') }}" defer></script>
</head>
<body data-theme="dark">
  <div class="wrap">

    <!-- Sidebar -->
    <aside class="sidebar" aria-label="Sidebar">
      <div class="brand">🌟 <span id="brandName">Worker</span></div>
      <nav class="nav" aria-label="Primary">
        <a href="{{ route('worker.dashboard') }}" aria-current="page">🏠 <span id="navDashboard">Dashboard</span></a>

        <a href="{{ route('worker.events.discover') }}" id="navEventsDiscoverLink">
          🗓️ <span id="navDiscover">Discover Events</span>
        </a>

        <a href="{{ route('worker.reservations') }}" id="navReservationsLink">
          ✅ <span id="navMyRes">My Reservations</span>
        </a>

        <a href="{{ route('worker.submissions') }}" id="navSubmissionsLink">
          📝 <span id="navSubmissions">Post-Event Submissions</span>
        </a>

        <a href="{{ route('worker.announcements') }}" id="navAnnouncementsLink">
          📣 <span id="navAnnouncements">Announcements</span>
        </a>

        <a href="{{ route('worker.messages') }}" id="navChatLink">
          💬 <span id="navChat">Chat</span>
        </a>

        <a href="{{ route('settings') }}" class="nav-item">
          <span class="nav-icon">🔧</span><span>Settings</span>
        </a>
      </nav>
    </aside>

    <!-- Main Content -->
    <main class="content" id="main">
      <!-- Top bar -->
      <div class="topbar">
        <div class="search" role="search">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none"><path d="m21 21-4.2-4.2M10.8 18a7.2 7.2 0 1 1 0-14.4 7.2 7.2 0 0 1 0 14.4Z" stroke="currentColor" stroke-width="1.6" opacity=".55"/></svg>
          <input id="globalSearch" placeholder="Search events…" aria-label="Search events"/>
        </div>

        <div class="bar-actions">
          <button class="btn ghost" id="langToggle" title="Switch Language">EN/AR</button>
          <button class="btn ghost" id="themeToggle" title="Toggle Theme">🌓</button>
          <button class="btn" id="quickFindEvents" onclick="window.location='{{ route('worker.events.discover') }}'">Find Events</button>

          {{-- Logout --}}
          @if(Route::has('logout'))
            <form method="POST" action="{{ route('logout') }}">
              @csrf
              <button type="submit" class="btn danger" title="Logout">Logout</button>
            </form>
          @endif
        </div>
      </div>

      <!-- Hero -->
      <section class="hero" aria-labelledby="heroTitle">
        <div>
          <h1 id="heroTitle">Welcome back, <span id="volName">{{ Auth::user()->name ?? 'Worker' }}</span> 👋</h1>
          <p id="heroSubtitle">Browse events that match your role and location, track your reservations, and submit post-event reports on time.</p>
          <div class="cta">
            <button class="btn" id="ctaDiscover" onclick="window.location='{{ route('worker.events.discover') }}'">Discover Events</button>
            <button class="btn secondary" id="ctaAnnouncements" onclick="window.location='{{ route('worker.announcements') }}'">View Announcements</button>
          </div>
        </div>
        <div class="hero-visual" aria-hidden="true"></div>
      </section>

      <!-- Metrics -->
      <section class="metrics" aria-label="Key metrics">
        <div class="kpi">
          <small id="kpi1Label">Upcoming Events</small>
          <div class="value" id="kpiUpcoming">0</div>
          <span class="pill" id="kpiUpcomingNote">Next 14 days</span>
        </div>
        <div class="kpi">
          <small id="kpi2Label">Reserved</small>
          <div class="value" id="kpiAccepted">0</div>
          <span class="pill">Confirmed</span>
        </div>
        <div class="kpi">
          <small id="kpi3Label">Completed</small>
          <div class="value" id="kpiPending">0</div>
          <span class="pill">Awaiting review</span>
        </div>
        <div class="kpi">
          <small id="kpi4Label">Hours Volunteered</small>
          <div class="value" id="kpiHours">0h</div>
          <span class="pill">This year</span>
        </div>
      </section>

      <!-- Discover / My Events -->
      <section>
        <div class="section-head">
          <h2 id="sectionTitle">This Week’s Events</h2>
          <div class="filter-row">
            <select id="filterType" aria-label="Filter by type">
              <option value="">Type</option>
              <option>Community</option>
              <option>Health</option>
              <option>Education</option>
            </select>
            <select id="filterCategory" aria-label="Filter by category">
              <option value="">Category</option>
              <option>Organizer</option>
              <option>Civil Defense</option>
              <option>Media Staff</option>
              <option>Tech Support</option>
            </select>
            <input class="input" type="date" id="filterDate" aria-label="Filter by date" />
            <input class="input" placeholder="Location" id="filterLocation" aria-label="Filter by location"/>
          </div>
        </div>

        <div class="grid" id="eventGrid" aria-live="polite"></div>
      </section>
    </main>

    <!-- Right Rail -->
    <aside class="rail" aria-label="Right rail">
      <div class="panel">
        <div class="profile">
          <div class="avatar" aria-hidden="true">VH</div>
          <div>
            <div style="font-weight:700" id="railName">{{ Auth::user()->name ?? 'Worker' }}</div>
            <div class="meta"><span id="railRole">Role: Media Staff</span> · <span id="railLocation">Beirut</span></div>
          </div>
        </div>
        <div style="margin-top:12px; display:flex; gap:8px">
          <a class="btn small" href="{{ route('worker.reservations') }}" id="btnMyReservations">My Reservations</a>
          <a class="btn small secondary" href="{{ route('worker.submissions') }}" id="btnSubmitReport">Submit Report</a>
        </div>
      </div>

      <div class="panel countdown" id="nextEventPanel">
        <div style="display:flex;justify-content:space-between;align-items:center">
          <strong>Next Event</strong>
          <span class="chip-status chip-accepted" id="nextEventStatus">Accepted</span>
        </div>
        <div id="nextEventTitle" style="margin-top:6px;font-weight:700">—</div>
        <div class="meta" id="nextEventMeta">—</div>
        <div style="margin-top:10px;display:flex;gap:8px;flex-wrap:wrap">
          <button class="btn small" id="viewEventBtn" onclick="window.location='{{ route('worker.events.discover') }}'">View</button>
          <button class="btn small ghost" id="cancelReservationBtn">Cancel (before deadline)</button>
        </div>
      </div>

      <div class="panel">
        <strong>Recent Activity</strong>
        <div class="list" id="activityList" style="margin-top:10px"></div>
      </div>

      <div class="panel">
        <strong>Announcements</strong>
        <div class="list" id="announceList" style="margin-top:10px"></div>
      </div>
    </aside>
  </div>
  @include('notify.widget')
  
<script src="{{ asset('js/notify-poll.js') }}" defer></script>

</body>
</html>
