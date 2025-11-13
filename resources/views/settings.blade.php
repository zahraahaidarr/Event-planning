{{-- resources/views/worker/settings.blade.php --}}
<!doctype html>
<html lang="{{ app()->getLocale() }}"
      dir="{{ app()->getLocale()==='ar' ? 'rtl' : 'ltr' }}"
      data-theme="dark">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Settings • Volunteer</title>

  {{-- Worker Settings CSS --}}
  <link rel="stylesheet" href="{{ asset('css/settings.css') }}">
</head>

@php
  // Settings array from controller, with safe fallback
  $s = $settings ?? [];
@endphp

<body data-theme="{{ $s['ui_theme'] ?? 'dark' }}">
  <div class="wrap">

    <!-- Main Content -->
    <main class="content" id="main">
      <!-- Top bar -->
      <div class="topbar">
        <div class="search" role="search">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <path d="m21 21-4.2-4.2M10.8 18a7.2 7.2 0 1 1 0-14.4 7.2 7.2 0 0 1 0 14.4Z"
                  stroke="currentColor" stroke-width="1.6" opacity=".55"/>
          </svg>
          <input id="globalSearch" placeholder="Search settings..." aria-label="Search settings"/>
        </div>

      </div>

      <!-- Page Header -->
      <section class="page-header">
        <h1 id="pageTitle">Settings</h1>
        <p id="pageSubtitle">Manage your account preferences and notifications.</p>
      </section>

      <!-- ROW 1: Notifications + Interface -->
      <section class="row">
        {{-- Notifications card --}}
        <article class="card">
          <h2 class="section-title">Notifications</h2>
          <div class="settings-list">

            <div class="setting-item">
              <div class="setting-info">
                <h4>App Notifications</h4>
                <p>Receive in-app notifications for updates inside VolunteerHub.</p>
              </div>
              <div class="toggle {{ ($s['notifications_app'] ?? '1') === '1' ? 'active' : '' }}"
                   data-toggle
                   data-setting="notifications_app"></div>
            </div>

            <div class="setting-item">
              <div class="setting-info">
                <h4>Announcements</h4>
                <p>Get notified when new announcements are published.</p>
              </div>
              <div class="toggle {{ ($s['notifications_announcements'] ?? '1') === '1' ? 'active' : '' }}"
                   data-toggle
                   data-setting="notifications_announcements"></div>
            </div>

            <div class="setting-item">
              <div class="setting-info">
                <h4>Chat Messages</h4>
                <p>Receive alerts for new chat messages.</p>
              </div>
              <div class="toggle {{ ($s['notifications_chat'] ?? '1') === '1' ? 'active' : '' }}"
                   data-toggle
                   data-setting="notifications_chat"></div>
            </div>

            <div class="setting-item">
              <div class="setting-info">
                <h4>Event Reminders</h4>
                <p>Get reminders before your accepted events start.</p>
              </div>
              <div class="toggle {{ ($s['notifications_event_reminders'] ?? '1') === '1' ? 'active' : '' }}"
                   data-toggle
                   data-setting="notifications_event_reminders"></div>
            </div>

          </div>
        </article>

        {{-- Interface & Preferences card --}}
        <article class="card">
          <h2 class="section-title">Interface & Preferences</h2>
          <div class="settings-list">

            <div class="setting-item">
              <div class="setting-info">
                <h4>Language</h4>
                <p>Switch between English and Arabic.</p>
              </div>
              <button class="btn small ghost"
                      type="button"
                      id="langToggleSecondary">
                Toggle EN / AR
              </button>
            </div>

            <div class="setting-item">
              <div class="setting-info">
                <h4>Theme</h4>
                <p>Toggle between light and dark mode.</p>
              </div>
              <button class="btn small ghost"
                      type="button"
                      id="themeToggleSecondary">
                Toggle Theme
              </button>
            </div>

          </div>
        </article>
      </section>

      <!-- ROW 2: Security | Account Management -->
      <section class="row">
        {{-- Security card --}}
        <article class="card">
          <h2 class="section-title">Security</h2>
          <div class="settings-list">

            <div class="setting-item">
              <div class="setting-info">
                <h4>Logout From All Devices</h4>
                <p>Log out from all active sessions on other devices.</p>
              </div>
              <form method="POST" action="{{ route('settings.logoutAll') }}">
                @csrf
                <button class="btn small" type="submit">Logout All</button>
              </form>
            </div>

          </div>
        </article>

        {{-- Account Management card --}}
        <article class="card">
          <h2 class="section-title">Account Management</h2>
          <div class="settings-list">

            <div class="setting-item">
              <div class="setting-info">
                <h4>Delete Account</h4>
                <p>Permanently delete your account and information.</p>
              </div>
              <form method="POST" action="{{ route('settings.deleteAccount') }}"
                    onsubmit="return confirm('Are you sure you want to delete your account?');">
                @csrf
                @method('DELETE')
                <button class="btn small danger" type="submit">Delete</button>
              </form>
            </div>

          </div>
        </article>
      </section>
    </main>
  </div>

  <script>
    window.WORKER_SETTINGS_UPDATE_URL = "{{ route('settings.update') }}";
    window.WORKER_SETTINGS_LOGOUT_ALL = "{{ route('settings.logoutAll') }}";
    window.WORKER_SETTINGS_DELETE_URL = "{{ route('settings.deleteAccount') }}";
    window.WORKER_SETTINGS = @json($settings ?? []);
    window.CSRF_TOKEN = "{{ csrf_token() }}";
  </script>

  {{-- Worker Settings JS --}}
  <script src="{{ asset('js/settings.js') }}" defer></script>
  <script src="{{ asset('js/notify-poll.js') }}" defer></script>

</body>
</html>
