{{-- resources/views/worker/settings.blade.php --}}
<!doctype html>
<html lang="en" dir="ltr" data-theme="dark">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Settings • Volunteer</title>

  {{-- Worker Settings CSS --}}
  <link rel="stylesheet" href="{{ asset('css/worker/settings.css') }}">
</head>
<body>
  <div class="wrap">

    <!-- Sidebar (kept hidden to preserve centered layout footprint) -->
    <aside class="sidebar" aria-label="Sidebar" aria-hidden="true">
      <div class="brand">🌟 <span id="brandName">Volunteer</span></div>
      <nav class="nav" aria-label="Primary">
        <a href="{{ route('worker.dashboard') }}">🏠 <span id="navDashboard">Dashboard</span></a>
        <a href="{{ route('worker.events.discover') }}">🗓️ <span id="navDiscover">Discover Events</span></a>
        <a href="{{ route('worker.reservations') }}">✅ <span id="navMyRes">My Reservations</span></a>
        <a href="{{ route('worker.submissions') }}">📝 <span id="navSubmissions">Post-Event Submissions</span></a>
        <a href="{{ route('worker.announcements') }}">📣 <span id="navAnnouncements">Announcements</span></a>
        <a href="{{ route('worker.messages') }}">💬 <span id="navChat">Chat</span></a>
        <a href="{{ route('worker.profile') }}">👤 <span id="navProfile">Profile</span></a>
        <a href="{{ route('worker.settings') }}" aria-current="page">⚙️ <span id="navSettings">Settings</span></a>
      </nav>
    </aside>

    <!-- Main Content -->
    <main class="content" id="main">
      <!-- Top bar -->
      <div class="topbar">
        <div class="search" role="search">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <path d="m21 21-4.2-4.2M10.8 18a7.2 7.2 0 1 1 0-14.4 7.2 7.2 0 0 1 0 14.4Z" stroke="currentColor" stroke-width="1.6" opacity=".55"/>
          </svg>
          <input id="globalSearch" placeholder="Search settings..." aria-label="Search settings"/>
        </div>
        <div class="bar-actions">
          <button class="btn ghost" id="langToggle" type="button" title="Switch Language">EN/AR</button>
          <button class="btn ghost" id="themeToggle" type="button" title="Toggle Theme">🌓</button>
        </div>
      </div>

      <!-- Page Header -->
      <section class="page-header">
        <h1 id="pageTitle">Settings</h1>
        <p id="pageSubtitle">Manage your account preferences and notifications.</p>
      </section>

      <!-- ROW 1: Notifications | Privacy -->
      <section class="row">
        <article class="card">
          <h2 class="section-title">Notifications</h2>
          <div class="settings-list">
            <div class="setting-item">
              <div class="setting-info">
                <h4>Email Notifications</h4>
                <p>Receive email updates about events and activities</p>
              </div>
              <div class="toggle active" data-toggle></div>
            </div>
            <div class="setting-item">
              <div class="setting-info">
                <h4>Push Notifications</h4>
                <p>Get push notifications on your device</p>
              </div>
              <div class="toggle active" data-toggle></div>
            </div>
            <div class="setting-item">
              <div class="setting-info">
                <h4>Event Reminders</h4>
                <p>Receive reminders before events start</p>
              </div>
              <div class="toggle active" data-toggle></div>
            </div>
          </div>
        </article>

        <article class="card">
          <h2 class="section-title">Privacy</h2>
          <div class="settings-list">
            <div class="setting-item">
              <div class="setting-info">
                <h4>Profile Visibility</h4>
                <p>Make your profile visible to other volunteers</p>
              </div>
              <div class="toggle active" data-toggle></div>
            </div>
            <div class="setting-item">
              <div class="setting-info">
                <h4>Show Activity Status</h4>
                <p>Let others see when you're active</p>
              </div>
              <div class="toggle active" data-toggle></div>
            </div>
          </div>
        </article>
      </section>

      <!-- ROW 2: Security | Account Management -->
      <section class="row">
        <article class="card">
          <h2 class="section-title">Security</h2>
          <div class="settings-list">
            <div class="setting-item">
              <div class="setting-info">
                <h4>Change Password</h4>
                <p>Update your account password</p>
              </div>
              <button class="btn small ghost" type="button">Change</button>
            </div>
            <div class="setting-item">
              <div class="setting-info">
                <h4>Two-Factor Authentication</h4>
                <p>Add an extra layer of security</p>
              </div>
              <button class="btn small" type="button">Enable</button>
            </div>
          </div>
        </article>

        <article class="card">
          <h2 class="section-title">Account Management</h2>
          <div class="settings-list">
            <div class="setting-item">
              <div class="setting-info">
                <h4>Download Your Data</h4>
                <p>Get a copy of your volunteer data</p>
              </div>
              <button class="btn small ghost" type="button">Download</button>
            </div>
            <div class="setting-item">
              <div class="setting-info">
                <h4>Delete Account</h4>
                <p>Permanently delete your account and data</p>
              </div>
              <button class="btn small danger" type="button">Delete</button>
            </div>
          </div>
        </article>
      </section>
    </main>
  </div>

  {{-- Worker Settings JS --}}
  <script src="{{ asset('js/worker/settings.js') }}"></script>
  @include('notify.widget')
<script src="{{ asset('js/notify-poll.js') }}" defer></script>

</body>
</html>
