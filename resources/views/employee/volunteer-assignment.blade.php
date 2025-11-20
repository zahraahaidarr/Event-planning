<!doctype html>
<html
    lang="{{ app()->getLocale() }}"
    dir="{{ app()->getLocale()==='ar' ? 'rtl' : 'ltr' }}"
    data-theme="dark"
>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Volunteer Assignment - Employee Portal</title>

    <script src="{{ asset('js/preferences.js') }}" defer></script>
    <link rel="stylesheet" href="{{ asset('css/employee/volunteer-assignment.css') }}">
</head>
<body>
<div class="container">
    <!-- Sidebar -->
    <aside class="sidebar">
        <div class="logo">
            <div class="logo-icon">🎯</div>
            <span>VolunteerHub</span>
        </div>

        <nav>
            <div class="nav-section">
                <div class="nav-label">Employee</div>

                <a href="{{ Route::has('employee.dashboard') ? route('employee.dashboard') : '#' }}"
                   class="nav-item {{ request()->routeIs('employee.dashboard') ? 'active' : '' }}">
                    <span class="nav-icon">📊</span><span>Dashboard</span>
                </a>

                <a href="{{ Route::has('events.index') ? route('events.index') : '#' }}"
                   class="nav-item">
                    <span class="nav-icon">📅</span><span>Event Management</span>
                </a>

                <a href="{{ Route::has('volunteers.assign') ? route('volunteers.assign') : '#' }}"
                   class="nav-item {{ request()->routeIs('volunteers.assign') ? 'active' : '' }}">
                    <span class="nav-icon">👥</span><span>Volunteer Assignment</span>
                </a>

                <a href="{{ Route::has('employee.reports') ? route('employee.reports') : '#' }}"
                   class="nav-item">
                    <span class="nav-icon">📝</span><span>Post-Event Reports</span>
                </a>
            </div>

            <div class="nav-section">
                <div class="nav-label">Communication</div>

                <a href="{{ Route::has('messages.index') ? route('messages.index') : '#' }}"
                   class="nav-item">
                    <span class="nav-icon">💬</span><span>Messages</span>
                </a>

                <a href="{{ route('announcements.create') }}" class="nav-item">
                    <span class="nav-icon">📢</span><span>Send Announcement</span>
                </a>

                <a href="{{ Route::has('employee.announcements.index') ? route('employee.announcements.index') : '#' }}"
                   class="nav-item">
                    <span class="nav-icon">📢</span><span>Announcements</span>
                </a>
            </div>

            <div class="nav-section">
                <div class="nav-label">Account</div>

                <a href="{{ Route::has('profile') ? route('profile') : '#' }}" class="nav-item">
                    <span class="nav-icon">👤</span><span>Profile</span>
                </a>

                <a href="{{ Route::has('settings') ? route('settings') : '#' }}" class="nav-item">
                    <span class="nav-icon">⚙️</span><span>Settings</span>
                </a>
            </div>
        </nav>
    </aside>

    <!-- Main Content -->
    <main class="main-content">
        <div class="header">
            <div class="header-left">
                <h1>Volunteer Assignment</h1>
                <p>Review and manage volunteer applications</p>
            </div>
            <div class="header-actions">
                <button class="icon-btn" id="btnTheme" title="Toggle theme">
                    <span id="theme-icon">🌙</span>
                </button>
                <button class="icon-btn" id="btnLang" title="Toggle language">
                    <span id="lang-icon">AR</span>
                </button>
            </div>
        </div>

        <!-- Event Selection -->
        <div class="event-selection">
            <h2 class="section-title">Select Event</h2>
            <select class="event-select" id="eventSelect">
    <option value="">Choose an event...</option>
    @foreach($events as $event)
        <option value="{{ $event->event_id }}">
            {{ $event->title }} – {{ $event->starts_at->format('Y-m-d') }}
        </option>
    @endforeach

</select>

        </div>

        <!-- Stats -->
        <div class="stats-grid" id="statsGrid" style="display:none;">
            <div class="stat-card">
                <div class="stat-label">Total Reservations</div>
                <div class="stat-value" id="statTotal">0</div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Remaining</div>
                <div class="stat-value" id="statPending">0</div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Rejected</div>
                <div class="stat-value" id="statRejected">0</div>
            </div>
        </div>

        <!-- Applications -->
        <div class="applications-section" id="applicationsSection" style="display:none;">
            <div class="applications-header">
                <h2 class="section-title">Applications</h2>
                <div class="filter-buttons">
                    <button class="filter-btn active" data-filter="all">All</button>
                    <button class="filter-btn" data-filter="rejected">Rejected</button>
                </div>
            </div>

            <div id="applicationsList"></div>
        </div>
    </main>
</div>

<!-- Volunteer Profile Modal -->
<div class="modal" id="profileModal">
    <div class="modal-content">
        <div class="modal-header">
            <h2 class="modal-title" id="modalVolunteerName">Volunteer Profile</h2>
            <button class="modal-close" id="btnCloseModal">&times;</button>
        </div>
        <div class="modal-body" id="modalBody"></div>
        <div class="modal-footer">
            <button class="btn btn-secondary" id="btnCloseModalFooter">Close</button>
        </div>
    </div>
</div>

@include('notify.widget')
<script src="{{ asset('js/notify-poll.js') }}" defer></script>
<script>
    window.csrfToken = "{{ csrf_token() }}";
    window.ENDPOINT_APPS_BASE = "{{ url('/employee/volunteer-assignment/events') }}";
</script>
<script src="{{ asset('js/employee/volunteer-assignment.js') }}" defer></script>

</body>
</html>
