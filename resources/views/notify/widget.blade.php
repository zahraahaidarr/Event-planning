<div id="notify-fab"
     style="position:fixed;right:18px;bottom:18px;z-index:9999;display:flex;flex-direction:column;gap:10px">
  <a href="{{ route('notifications.index') }}"
     style="text-decoration:none">
    <div style="position:relative;background:var(--surface,#1f2937);color:var(--text,#fff);
                width:52px;height:52px;border-radius:50%;display:flex;align-items:center;justify-content:center;
                box-shadow:0 8px 24px rgba(0,0,0,.25);font-size:22px;">
      🔔
      <span id="notify-count"
            style="position:absolute;top:-6px;right:-6px;background:#ef4444;color:#fff;border-radius:999px;
                   font-size:12px;line-height:1;padding:4px 7px;min-width:18px;text-align:center;display:none">0</span>
    </div>
  </a>
</div>
