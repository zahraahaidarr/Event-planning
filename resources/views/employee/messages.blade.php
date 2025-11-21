<!doctype html>
<html
    lang="{{ app()->getLocale() }}"
    dir="{{ app()->getLocale()==='ar' ? 'rtl' : 'ltr' }}"
>
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Messages • Employee</title>

  <link rel="stylesheet" href="{{ asset('css/employee/messages.css') }}">
</head>
<body data-theme="dark"
      data-contacts-url="{{ route('employee.messages.contacts') }}"
      data-thread-url-base="{{ url('/employee/messages/thread') }}"
>
  <div class="wrap">

    <!-- Sidebar -->
    <aside class="sidebar" aria-label="Sidebar">
      <div class="brand">🌟 <span id="brandName">VolunteerHub</span></div>
      <nav class="nav" aria-label="Primary">
        <a href="{{ route('employee.dashboard') }}">🏠 <span id="navDashboard">Dashboard</span></a>
        {{-- Add more employee links here if you want --}}
        <a href="{{ route('employee.messages') }}" aria-current="page">💬 <span id="navChat">Chat</span></a>
      </nav>
    </aside>

    <!-- Main Content -->
    <main class="content" id="main">
      <!-- Top bar -->
      <div class="topbar">
        <div class="search" role="search">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none">
            <path d="m21 21-4.2-4.2M10.8 18a7.2 7.2 0 1 1 0-14.4 7.2 7.2 0 0 1 0 14.4Z"
                  stroke="currentColor" stroke-width="1.6" opacity=".55"/>
          </svg>
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
        <div class="conversations">
          <div class="conversations-header">
            <h2 id="convTitle">Messages</h2>
            <input id="contactSearch" class="search-input" placeholder="Search..." />
          </div>
          <div class="conversations-list" id="convList"></div>
        </div>

        <!-- Chat Panel -->
        <div class="chat-panel" id="chatPanel">
          <div class="empty-chat">
            <div>
              <div class="empty-icon">💬</div>
              <div class="empty-title">Select a conversation</div>
              <div class="empty-subtitle">Choose a conversation from the list to start messaging</div>
            </div>
          </div>
        </div>
      </div>
    </main>
  </div>

  <script src="{{ asset('js/employee/messages.js') }}" defer></script>
</body>
</html>
