{{-- resources/views/Employee/dashboard.blade.php --}}
<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" data-theme="dark" dir="{{ app()->getLocale()==='ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Client Dashboard</title>

    <script src="{{ asset('js/preferences.js') }}" defer></script>
    <link rel="stylesheet" href="{{ asset('css/employee/dashboard.css') }}">

    @php
        $dashboardData = [
            'totalEvents'          => $totalEvents ?? 0,
            'completedEvents'      => $completedEvents ?? 0,
            'totalPeople'          => $totalPeople ?? 0,
            'totalVolunteersOnly'  => $totalVolunteersOnly ?? 0,
            'totalPaidWorkersOnly' => $totalPaidWorkersOnly ?? 0,
            'pendingReports'       => $pendingReports ?? 0,

            'upcomingEvents'  => $upcomingEvents ?? [],
            'tasks'           => $tasks ?? [],
            'recentActivity'  => $recentActivity ?? [],
            'eventsMonthlyChart' => $eventsMonthlyChart ?? null,

        ];
    @endphp

    <script>
        window.dashboardData = {{ \Illuminate\Support\Js::from($dashboardData) }};
    </script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script src="{{ asset('js/employee/dashboard.js') }}" defer></script>
</head>
<body>
<div class="app-container">

    {{-- Sidebar --}}
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
                    <div class="logo-role">Client</div>
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
                    <span class="nav-icon">👥</span><span>Worker Application</span>
                </a>

                <a href="{{ route('employee.postEventReports.index') }}"
                   class="nav-item {{ request()->routeIs('employee.postEventReports.*') ? 'active' : '' }}">
                    <span class="nav-icon">📝</span><span>Post-Event Reports</span>
                </a>
                <a href="{{ route('content.index') }}" class="nav-item {{ request()->routeIs('employee.content.*') ? 'active' : '' }}">
                    <span class="nav-icon">📝</span><span>Create Content</span>
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

    <main class="main-content">
        <div class="header">
            <h1 class="header-title">Client Dashboard</h1>

            <div class="header-actions">
                <button class="btn btn-primary"
                        @if(Route::has('events.index'))
                            onclick="window.location.href='{{ route('events.index') }}'"
                        @else
                            onclick="return false"
                        @endif>
                    <span></span><span>Create Event</span>
                </button>

                @if(Route::has('logout'))
                    <form method="POST" action="{{ route('logout') }}" class="logout-form">
                        @csrf
                        <button type="submit" class="logout-btn">Logout</button>
                    </form>
                @endif
            </div>
        </div>

        {{-- Stats --}}
        <div class="stats-grid">
            <div class="stat-card" style="grid-column: span 2;">
    <div class="stat-header">
        <span class="stat-label">Events Overview</span>
    </div>

    <div style="height:220px;">
        <canvas id="eventsLineChart"></canvas>

    </div>

    <div class="stat-change positive" style="margin-top:10px;">
        <span>Completed: {{ $completedEvents }}</span>
        <span>•</span>
        <span>Not completed: {{ max(($totalEvents - $completedEvents), 0) }}</span>
    </div>
</div>


            <div class="stat-card">
    <div class="stat-header">
        <span class="stat-label">Workers Overview</span>
    </div>

    <div style="height:180px;">
        <canvas id="workersPieChart"></canvas>
    </div>

    
</div>


            
        </div>

        {{-- Upcoming / Tasks --}}
        <div class="content-grid">
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">Upcoming Events</h2>
                    @if(Route::has('events.index'))
                        <a href="{{ route('events.index') }}" class="view-all-link">View All →</a>
                    @endif
                </div>
                <div class="event-list" id="upcoming-events-list"></div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">Pending Tasks</h2>
                    <span class="muted" id="tasks-count">0 tasks</span>
                </div>
                <div class="task-list" id="tasks-list"></div>
            </div>
        </div>

        {{-- ✅ Recent Activity (half width) --}}
        <div class="content-grid">
            <div class="card">
                <div class="card-header"><h2 class="card-title">Recent Activity</h2></div>
                <div class="event-list" id="recent-activity-list"></div>
            </div>

            {{-- empty right side to keep same grid width --}}
            <div class="card recent-activity-placeholder"></div>
        </div>

    </main>
</div>

@include('notify.widget')
<script src="{{ asset('js/notify-poll.js') }}" defer></script>
</body>
</html>
