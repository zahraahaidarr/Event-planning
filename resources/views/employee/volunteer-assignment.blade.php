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
<div class="container layout-employee">
    <!-- Sidebar -->
    <aside class="sidebar">
        @php($user = Auth::user())

        <div class="logo">
            <a href="{{ Route::has('profile') ? route('profile') : '#' }}" class="logo-link">
                @if($user && $user->avatar_path)
                    <img
                        src="{{ asset('storage/' . ltrim($user->avatar_path, '/')) }}"
                        alt="{{ $user->first_name ?? $user->name ?? 'Profile' }}"
                        class="logo-avatar"
                    >
                @else
                    <div class="logo-icon">
                        {{ strtoupper(substr($user->first_name ?? $user->name ?? 'U', 0, 1)) }}
                    </div>
                @endif

                <div class="logo-id">
                    <div class="logo-name">
                        {{ trim(($user->first_name ?? '').' '.($user->last_name ?? '')) ?: ($user->name ?? 'User') }}
                    </div>
                    <div class="logo-role">
                        EMPLOYEE
                    </div>
                </div>
            </a>
        </div>

        <nav>
    <div class="nav-section">
        <a href="{{ Route::has('employee.dashboard') ? route('employee.dashboard') : '#' }}"
           class="nav-item {{ request()->routeIs('employee.dashboard') ? 'active' : '' }}">
            <span class="nav-icon">📊</span><span>Dashboard</span>
        </a>

        <a href="{{ Route::has('events.index') ? route('events.index') : '#' }}"
           class="nav-item {{ request()->routeIs('events.*') ? 'active' : '' }}">
            <span class="nav-icon">📅</span><span>Event Management</span>
        </a>

        <a href="{{ route('employee.volunteer.assignment') }}"
           class="nav-item {{ request()->routeIs('employee.volunteer.assignment') ? 'active' : '' }}">
            <span class="nav-icon">👥</span>
            <span>Volunteer Assignment</span>
        </a>

        {{-- 🔹 HERE: keep Post-Event Reports highlighted on all its routes --}}
        <a href="{{ route('employee.postEventReports.index') }}"
           class="nav-item {{ request()->routeIs('employee.postEventReports.*') ? 'active' : '' }}">
            <span class="nav-icon">📝</span><span>Post-Event Reports</span>
        </a>
    </div>

    <div class="nav-section">
        <div class="nav-label">Communication</div>

        <a href="{{ route('employee.messages') }}"
           class="nav-item {{ request()->routeIs('employee.messages') ? 'active' : '' }}">
            <span class="nav-icon">💬</span><span>Messages</span>
        </a>

        <a href="{{ route('announcements.create') }}"
           class="nav-item {{ request()->routeIs('announcements.create') ? 'active' : '' }}">
            <span class="nav-icon">📢</span><span>Send Announcement</span>
        </a>

        <a href="{{ Route::has('employee.announcements.index') ? route('employee.announcements.index') : '#' }}"
           class="nav-item {{ request()->routeIs('employee.announcements.*') ? 'active' : '' }}">
            <span class="nav-icon">📢</span><span>Announcements</span>
        </a>
    </div>

    <div class="nav-section">
        <div class="nav-label">Account</div>

        <a href="{{ Route::has('settings') ? route('settings') : '#' }}"
           class="nav-item {{ request()->routeIs('settings') ? 'active' : '' }}">
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
            
        </div>

        <!-- Event Selection (cards instead of dropdown) -->
        <div class="event-selection">
            <h2 class="section-title">Select Event</h2>
            <p class="event-hint">Click an event to view and manage its volunteer applications.</p>

            <div class="event-list" id="eventList">
                @forelse($events as $event)
                    <button
                        type="button"
                        class="event-card {{ $loop->first ? 'active' : '' }}"
                        data-event-id="{{ $event->event_id }}"
                    >
                        <div class="event-card-title">
                            {{ $event->title }}
                        </div>
                        <div class="event-card-meta">
                            <span class="event-date">
                                {{ $event->starts_at ? $event->starts_at->format('Y-m-d') : 'No date' }}
                            </span>
                            @if(optional($event->venue)->name)
                                <span class="event-dot">•</span>
                                <span class="event-venue">
                                    {{ $event->venue->name }}
                                </span>
                            @endif
                        </div>
                    </button>
                @empty
                    <div class="empty-state small">
                        <div class="empty-icon">📭</div>
                        <h3 class="empty-title">No events found</h3>
                        <p class="empty-description">
                            Create an event first to assign volunteers.
                        </p>
                    </div>
                @endforelse
            </div>
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
        <button class="filter-btn" data-filter="accepted">Accepted</button>
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
    window.csrfToken           = "{{ csrf_token() }}";
    window.ENDPOINT_APPS_BASE  = "{{ url('/employee/volunteer-assignment/events') }}";
    window.ENDPOINT_STATUS_BASE = "{{ url('/employee/volunteer-assignment/reservations') }}";
</script>
<script src="{{ asset('js/employee/volunteer-assignment.js') }}" defer></script>

</body>
</html>
