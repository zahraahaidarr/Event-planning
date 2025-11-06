<!doctype html>
<html lang="en" dir="ltr" data-theme="dark">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Announcements • Volunteer</title>

  {{-- CSS (public/css/worker/announcements.css) --}}
  <link rel="stylesheet" href="{{ asset('css/worker/announcements.css') }}">
</head>
<body>
  <div class="wrap">

    <!-- Sidebar (kept only for layout footprint) -->
    <aside class="sidebar" aria-label="Sidebar">
      <div class="brand">🌟 <span id="brandName">Volunteer</span></div>
      <nav class="nav" aria-label="Primary">
        <a href="{{ route('worker.dashboard') }}">🏠 <span id="navDashboard">Dashboard</span></a>
        <a href="{{ route('worker.events.discover') }}">🗓️ <span id="navDiscover">Discover Events</span></a>
        <a href="{{ route('worker.reservations') }}">✅ <span id="navMyRes">My Reservations</span></a>
        <a href="{{ route('worker.submissions') }}">📝 <span id="navSubmissions">Post-Event Submissions</span></a>
        <a href="{{ route('worker.announcements') }}" aria-current="page">📣 <span id="navAnnouncements">Announcements</span></a>
        <a href="{{ route('worker.messages') }}">💬 <span id="navChat">Chat</span></a>
        <a href="{{ route('worker.profile') }}">👤 <span id="navProfile">Profile</span></a>
        <a href="{{ route('worker.settings') }}">⚙️ <span id="navSettings">Settings</span></a>
      </nav>
    </aside>

    <!-- Main Content -->
    <main class="content" id="main">
      <!-- Top bar -->
      <div class="topbar">
        <div class="search" role="search">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none">
            <path d="m21 21-4.2-4.2M10.8 18a7.2 7.2 0 1 1 0-14.4 7.2 7.2 0 0 1 0 14.4Z" stroke="currentColor" stroke-width="1.6" opacity=".55"/>
          </svg>
          <input id="globalSearch" placeholder="Search announcements…" aria-label="Search announcements"/>
        </div>
        <div class="bar-actions">
          <button class="btn ghost" id="langToggle" title="Switch Language">EN/AR</button>
          <button class="btn ghost" id="themeToggle" title="Toggle Theme">🌓</button>
        </div>
      </div>

      <!-- Page Header -->
      <section class="page-header">
        <h1 id="pageTitle">Announcements</h1>
        <p id="pageSubtitle">Important updates and news for the volunteer community.</p>
      </section>

      <!-- List -->
      <section>
        <div class="list" id="announceList" aria-live="polite"></div>
      </section>
    </main>
  </div>

  {{-- JS (public/js/worker/announcements.js) --}}
  <script src="{{ asset('js/worker/announcements.js') }}" defer></script>
</body>
</html>
