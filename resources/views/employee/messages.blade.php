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


    <!-- Main Content -->
    <main class="content" id="main">

      <!-- Title + subtitle (same style as worker) -->
      <header class="messages-title-bar">
        <div class="messages-title-wrapper">
          <h1>Messages</h1>
          <p>Chat with coordinators and volunteers, and keep track of your conversations.</p>
        </div>
      </header>

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
