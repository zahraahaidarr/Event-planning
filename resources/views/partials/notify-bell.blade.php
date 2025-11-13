{{-- Header Notification Bell (inline, not fixed) --}}
@php($count = $unreadCount ?? 0)

<div class="hdr-bell relative" x-data="{open:false}" @click.outside="open=false">
  <button type="button"
          class="hdr-bell-btn"
          @click="open = !open"
          aria-label="Notifications">
    <span class="i">🔔</span>
    <span class="badge" x-bind:hidden="{{ $count }} === 0">{{ $count }}</span>
  </button>

  <div class="panel" x-show="open" x-transition>
    <div class="panel-head">
      <strong>Notifications</strong>
      <button class="link" type="button" id="hdrMarkAll">Mark all as read</button>
    </div>
    <div class="panel-list" id="hdrNotifList">
      {{-- optional: render last few notifications from backend (fallback before JS loads) --}}
      @isset($latestNotifications)
        @forelse($latestNotifications as $n)
          <div class="item {{ $n->is_read ? '' : 'unread' }}">
            <div class="title">{{ $n->title }}</div>
            <div class="body">{{ $n->message }}</div>
            <div class="time">{{ \Carbon\Carbon::parse($n->created_at)->diffForHumans() }}</div>
          </div>
        @empty
          <div class="empty">No notifications</div>
        @endforelse
      @endisset
    </div>
    <div class="panel-foot">
      <a class="link" href="{{ route('notifications.index') }}">View all</a>
    </div>
  </div>
</div>

<style>
  .hdr-bell{display:inline-block; position:relative;}
  .hdr-bell-btn{
    background:#1f2937; color:#fff; border:1px solid #374151; border-radius:12px;
    width:44px; height:44px; display:flex; align-items:center; justify-content:center;
  }
  .hdr-bell-btn .i{font-size:18px; line-height:1}
  .hdr-bell .badge{
    position:absolute; top:-6px; right:-6px; background:#ef4444; color:#fff;
    font-size:12px; min-width:18px; height:18px; border-radius:999px;
    display:inline-flex; align-items:center; justify-content:center; padding:0 6px;
  }
  .hdr-bell .panel{
    position:absolute; right:0; top:52px; width:340px; max-height:60vh; overflow:auto;
    background:#0b1220; color:#e5e7eb; border:1px solid #1f2937; border-radius:14px; padding:10px;
    box-shadow:0 12px 30px rgba(0,0,0,.45); z-index:1000;
  }
  .panel-head,.panel-foot{display:flex; justify-content:space-between; align-items:center; padding:6px 8px}
  .panel-list{padding:6px 4px}
  .item{padding:10px; border-radius:10px; border:1px solid rgba(255,255,255,.06); margin:8px}
  .item.unread{background:#111827}
  .title{font-weight:600}
  .time{opacity:.7; font-size:.85rem}
  .link{color:#93c5fd; background:none; border:none; padding:0; cursor:pointer; text-decoration:none}
  .empty{opacity:.75; padding:10px}
</style>

<script>
document.addEventListener('DOMContentLoaded', () => {
  const btnMark = document.getElementById('hdrMarkAll');
  if(btnMark){
    btnMark.addEventListener('click', async () => {
      await fetch('/api/notifications/read-all', {
        method:'POST',
        headers:{
          'X-Requested-With':'XMLHttpRequest',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
      });
      // quick UI reset
      const badge = document.querySelector('.hdr-bell .badge');
      if(badge){ badge.hidden = true; badge.textContent = '0'; }
      // optionally clear “unread” styles
      document.querySelectorAll('.panel-list .item').forEach(el => el.classList.remove('unread'));
    });
  }
});
</script>
