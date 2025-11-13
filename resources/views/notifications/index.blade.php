<!doctype html>
<html lang="en" data-theme="dark">
<head>
  <meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Notifications</title>
  <script src="{{ asset('js/preferences.js') }}" defer></script>
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <link rel="stylesheet" href="{{ asset('css/admin/events.css') }}">
  <style>
    .notif-list{max-width:860px;margin:32px auto;padding:0 16px}
    .notif-row{background:var(--surface,#111827);border-radius:12px;padding:14px 16px;margin-bottom:12px;
               display:flex;gap:12px;align-items:flex-start;border:1px solid rgba(255,255,255,.06)}
    .dot{width:10px;height:10px;border-radius:50%;margin-top:8px}
    .dot.unread{background:#ef4444}.dot.read{background:#374151}
    .title{font-weight:600}.time{opacity:.7;font-size:.85rem}
    .actions{display:flex;justify-content:flex-end;margin:16px 0}
  </style>
</head>
<body>
  @include('notify.widget')

  <div class="notif-list">
    <h1>Notifications</h1>
    <div class="actions">
      <button id="btnMarkAll" class="btn btn-secondary">Mark all as read</button>
    </div>

    @foreach($notifications as $n)
      <div class="notif-row">
        <div class="dot {{ $n->is_read ? 'read':'unread' }}"></div>
        <div>
          <div class="title">{{ $n->title }}</div>
          <div class="body">{{ $n->message }}</div>
          <div class="time">
    {{ \Carbon\Carbon::parse($n->created_at)->diffForHumans() }}
</div>

        </div>
      </div>
    @endforeach

    {{ $notifications->links() }}
  </div>

  <script src="{{ asset('js/notify-poll.js') }}" defer></script>
  <script>
    document.addEventListener('DOMContentLoaded', () => {
      const btn = document.getElementById('btnMarkAll');
      if(!btn) return;
      btn.addEventListener('click', async () => {
        const res = await fetch('/api/notifications/read-all', {
          method:'POST',
          headers:{
            'X-Requested-With':'XMLHttpRequest',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
          }
        });
        if(res.ok) location.reload();
      });
    });
  </script>
</body>
</html>
