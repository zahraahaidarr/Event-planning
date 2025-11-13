<!doctype html>
<html lang="en" dir="ltr" data-theme="dark">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Messages • Volunteer</title>
<script src="{{ asset('js/preferences.js') }}" defer></script>
  {{-- CSS (public/css/worker/messages.css) --}}
  <link rel="stylesheet" href="{{ asset('css/messages.css') }}">
</head>
<body>
  <div class="wrap">
    <!-- Sidebar (kept for layout footprint only) -->
    <aside class="sidebar" aria-label="Sidebar">
      <div class="brand">🌟 <span id="brandName">Volunteer</span></div>
      <nav class="nav" aria-label="Primary">
        <a href="{{ route('worker.dashboard') }}">🏠 <span id="navDashboard">Dashboard</span></a>
        <a href="{{ route('worker.events.discover') }}">🗓️ <span id="navDiscover">Discover Events</span></a>
        <a href="{{ route('worker.reservations') }}">✅ <span id="navMyRes">My Reservations</span></a>
        <a href="{{ route('worker.submissions') }}">📝 <span id="navSubmissions">Post-Event Submissions</span></a>
        <a href="{{ route('worker.announcements') }}">📣 <span id="navAnnouncements">Announcements</span></a>
        <a href="{{ route('worker.messages') }}" aria-current="page">💬 <span id="navChat">Chat</span></a>
        <a href="{{ route('worker.profile') }}">👤 <span id="navProfile">Profile</span></a>
        <a href="{{ route('worker.settings') }}">⚙️ <span id="navSettings">Settings</span></a>
      </nav>
    </aside>

    <!-- Main Content (center column) -->
    <main class="content" id="main">
      <!-- Top bar -->
      <div class="topbar">
        <div class="search" role="search">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none"><path d="m21 21-4.2-4.2M10.8 18a7.2 7.2 0 1 1 0-14.4 7.2 7.2 0 0 1 0 14.4Z" stroke="currentColor" stroke-width="1.6" opacity=".55"/></svg>
          <input id="globalSearch" placeholder="Search messages…" aria-label="Search messages"/>
        </div>
        <div class="bar-actions">
          <button class="btn ghost" id="langToggle" title="Switch Language">EN/AR</button>
          <button class="btn ghost" id="themeToggle" title="Toggle Theme">🌓</button>
        </div>
      </div>

      <!-- Chat Container -->
      <div class="chat-container">
        <!-- Conversations List -->
        <aside class="conversations">
          <div class="conversations-header">
            <h2 id="convTitle">Messages</h2>
            <input class="search" placeholder="Search..." style="width:100%;padding:8px 10px;border-radius:8px;border:0;background:var(--surface-2);color:var(--text)"/>
          </div>
          <div class="conversations-list" id="convList"></div>
        </aside>

        <!-- Chat Panel -->
        <section class="chat-panel" id="chatPanel" aria-live="polite">
          <div class="empty">
            <div class="empty-emoji">💬</div>
            <div class="empty-title">Select a conversation</div>
            <div class="empty-sub">Choose a conversation from the list to start messaging</div>
          </div>
        </section>
      </div>
    </main>
  </div>

  {{-- JS (public/js/worker/messages.js) --}}
  <script src="{{ asset('js/messages.js') }}" defer></script>
</body>
</html>
