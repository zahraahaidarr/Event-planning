<!doctype html>
<html lang="en" data-theme="dark">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Notifications</title>
  <script src="{{ asset('js/preferences.js') }}" defer></script>
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <link rel="stylesheet" href="{{ asset('css/admin/events.css') }}">

  <style>
    :root{
      --bg:#0f1222;
      --surface:#14183a;
    }

    html, body{
      margin:0;
      padding:0;
      min-height:100%;
    }

    body{
      background:
        radial-gradient(1200px 800px at -10% -10%, #1a2050 0%, var(--bg) 55%),
        radial-gradient(1200px 700px at 110% -10%, #281a55 0%, transparent 50%),
        var(--bg);
      background-attachment: fixed;
      background-repeat: no-repeat;
      color:#fff;
      font-family: Arial, sans-serif;
    }

    .notif-list{
      max-width:860px;
      margin:32px auto;
      padding:0 16px 80px;
    }

    .notif-row{
      background:rgba(15,18,34,0.95);
      border-radius:12px;
      padding:14px 16px;
      margin-bottom:12px;
      display:flex;
      gap:12px;
      align-items:flex-start;
      border:1px solid rgba(255,255,255,.06);
    }

    .dot{width:10px;height:10px;border-radius:50%;margin-top:8px}
    .dot.unread{background:#ef4444}
    .dot.read{background:#374151}
    .title{font-weight:600}
    .time{opacity:.7;font-size:.85rem}

    /* Search Bar */
.search-wrapper{
    margin: 20px 0 35px;
    position: relative;
    width:100%;
  }

  .search-input{
    width:100%;
    padding:12px 0 12px 42px;
    background:transparent;
    border:none;
    border-bottom:2px solid rgba(255,255,255,0.12);
    color:#fff;
    font-size:16px;
    outline:none;
    transition:.25s;
  }

  .search-input:focus{
    border-bottom-color:#4f7cff;
    box-shadow:0 4px 12px rgba(79,124,255,0.25);
  }

  .search-icon{
    position:absolute;
    left:0;
    top:50%;
    transform:translateY(-50%);
    font-size:18px;
    color:#7f8fcf;
    opacity:.75;
  }
  </style>
</head>

<body>
  @include('notify.widget')

  <div class="notif-list">
    <h1>Notifications</h1>

    <!-- 🔍 Search Bar -->
    <div class="search-wrapper">
      <span class="search-icon">🔍</span>
      <input type="text" id="searchInput" class="search-input" placeholder="Search notifications...">
    </div>

    <!-- Notification List -->
    <div id="notificationsContainer">
      @foreach($notifications as $n)
        <div class="notif-row">
          <div class="dot {{ $n->is_read ? 'read' : 'unread' }}"></div>
          <div>
            <div class="title">{{ $n->title }}</div>
            <div class="body">{{ $n->message }}</div>
            <div class="time">{{ \Carbon\Carbon::parse($n->created_at)->diffForHumans() }}</div>
          </div>
        </div>
      @endforeach
    </div>

    {{ $notifications->links() }}
  </div>

  <script src="{{ asset('js/notify-poll.js') }}" defer></script>

  <script>
    // Local search filter
    document.getElementById('searchInput').addEventListener('input', function () {
      const q = this.value.toLowerCase();
      const rows = document.querySelectorAll('.notif-row');

      rows.forEach(row => {
        const text = row.innerText.toLowerCase();
        row.style.display = text.includes(q) ? '' : 'none';
      });
    });
  </script>

</body>
</html>
