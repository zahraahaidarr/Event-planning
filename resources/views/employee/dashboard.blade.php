{{-- resources/views/Employee/dashboard.blade.php --}}
<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" data-theme="dark" dir="{{ app()->getLocale()==='ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee Dashboard - VolunteerHub</title>
    {{-- If you’re not using Vite, keep asset() and place files under public/ as shown below --}}
    <link rel="stylesheet" href="{{ asset('css/employee/dashboard.css') }}">
    <script src="{{ asset('js/employee/dashboard.js') }}" defer></script>
</head>
<body>
<div class="app-container">
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

                <a href="{{ Route::has('events.index') ? route('events.index') : '#' }}" class="nav-item">
                    <span class="nav-icon">📅</span><span>Event Management</span>
                </a>

                <a href="{{ Route::has('volunteers.assign') ? route('volunteers.assign') : '#' }}" class="nav-item">
                    <span class="nav-icon">👥</span><span>Volunteer Assignment</span>
                </a>

                <a href="{{ Route::has('employee.reports') ? route('employee.reports') : '#' }}" class="nav-item">
                    <span class="nav-icon">📝</span><span>Post-Event Reports</span>
                </a>
            </div>

            <div class="nav-section">
                <div class="nav-label">Communication</div>
                <a href="{{ Route::has('messages.index') ? route('messages.index') : '#' }}" class="nav-item">
                    <span class="nav-icon">💬</span><span>Messages</span>
                </a>
                <a href="{{ Route::has('announcements.index') ? route('announcements.index') : '#' }}" class="nav-item">
                    <span class="nav-icon">📢</span><span>Announcements</span>
                </a>
            </div>

            <div class="nav-section">
                <div class="nav-label">Account</div>
                <a href="{{ Route::has('profile.show') ? route('profile.show') : '#' }}" class="nav-item">
                    <span class="nav-icon">👤</span><span>Profile</span>
                </a>

                <a href="{{ Route::has('events.index') ? route('Settings.index') : '#' }}" class="nav-item">
                <span class="nav-icon">🔧</span><span>Settings</span>
            </a>
            <a href="{{ route('announcements.create') }}">Send Announcement</a>
            </div>
        </nav>
    </aside>

    <main class="main-content">
        <div class="header">
            <h1 class="header-title">Employee Dashboard</h1>
            <div class="header-actions">
                <button class="lang-toggle" onclick="toggleLanguage()">
                    {{ app()->getLocale()==='ar' ? 'EN' : 'AR' }}
                </button>
                <button class="theme-toggle" onclick="toggleTheme()">🌙</button>

                <button class="btn btn-primary"
                    @if(Route::has('events.create'))
                        onclick="window.location.href='{{ route('events.create') }}'"
                    @else
                        onclick="return false"
                    @endif>
                    <span>➕</span><span>Create Event</span>
                </button>

                {{-- Header Logout (pill red) --}}
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
            <div class="stat-card">
                <div class="stat-header">
                    <span class="stat-label">Total Events</span>
                    <div class="stat-icon" style="background:rgba(79,124,255,.15);color:var(--primary)">📅</div>
                </div>
                <div class="stat-value">24</div>
                <div class="stat-change positive"><span>↑</span><span>12% from last month</span></div>
            </div>

            <div class="stat-card">
                <div class="stat-header">
                    <span class="stat-label">Active Events</span>
                    <div class="stat-icon" style="background:rgba(54,211,153,.15);color:var(--success)">✅</div>
                </div>
                <div class="stat-value">8</div>
                <div class="stat-change positive"><span>↑</span><span>3 new this week</span></div>
            </div>

            <div class="stat-card">
                <div class="stat-header">
                    <span class="stat-label">Total Volunteers</span>
                    <div class="stat-icon" style="background:rgba(156,108,255,.15);color:var(--accent)">👥</div>
                </div>
                <div class="stat-value">156</div>
                <div class="stat-change positive"><span>↑</span><span>8% increase</span></div>
            </div>

            <div class="stat-card">
                <div class="stat-header">
                    <span class="stat-label">Pending Reports</span>
                    <div class="stat-icon" style="background:rgba(244,191,80,.15);color:var(--warning)">📝</div>
                </div>
                <div class="stat-value">12</div>
                <div class="stat-change negative"><span>↓</span><span>Needs review</span></div>
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

                <div class="event-list">
                    <div class="event-item">
                        <div class="event-date"><div class="event-day">15</div><div class="event-month">Jan</div></div>
                        <div class="event-details">
                            <div class="event-title">Community Health Fair</div>
                            <div class="event-meta"><span>📍 City Center</span><span>⏰ 9:00 AM - 5:00 PM</span></div>
                            <div class="event-progress"><div class="progress-bar"><div class="progress-fill" style="width:75%"></div></div><span>15/20 volunteers</span></div>
                        </div>
                        <span class="badge badge-success">Ready</span>
                    </div>

                    <div class="event-item">
                        <div class="event-date"><div class="event-day">18</div><div class="event-month">Jan</div></div>
                        <div class="event-details">
                            <div class="event-title">Food Distribution Drive</div>
                            <div class="event-meta"><span>📍 North District</span><span>⏰ 8:00 AM - 2:00 PM</span></div>
                            <div class="event-progress"><div class="progress-bar"><div class="progress-fill" style="width:45%"></div></div><span>9/20 volunteers</span></div>
                        </div>
                        <span class="badge badge-warning">Needs Volunteers</span>
                    </div>

                    <div class="event-item">
                        <div class="event-date"><div class="event-day">22</div><div class="event-month">Jan</div></div>
                        <div class="event-details">
                            <div class="event-title">Youth Education Workshop</div>
                            <div class="event-meta"><span>📍 Community Center</span><span>⏰ 10:00 AM - 4:00 PM</span></div>
                            <div class="event-progress"><div class="progress-bar"><div class="progress-fill" style="width:100%"></div></div><span>12/12 volunteers</span></div>
                        </div>
                        <span class="badge badge-success">Full</span>
                    </div>

                    <div class="event-item">
                        <div class="event-date"><div class="event-day">25</div><div class="event-month">Jan</div></div>
                        <div class="event-details">
                            <div class="event-title">Environmental Cleanup</div>
                            <div class="event-meta"><span>📍 Beach Area</span><span>⏰ 7:00 AM - 12:00 PM</span></div>
                            <div class="event-progress"><div class="progress-bar"><div class="progress-fill" style="width:20%"></div></div><span>3/15 volunteers</span></div>
                        </div>
                        <span class="badge badge-danger">Urgent</span>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">Pending Tasks</h2>
                    <span class="muted">12 tasks</span>
                </div>
                <div class="task-list">
                    <div class="task-item"><div class="task-checkbox"></div><div class="task-text">Review 5 post-event reports</div><span class="badge badge-warning">High</span></div>
                    <div class="task-item"><div class="task-checkbox"></div><div class="task-text">Approve 8 volunteer applications</div><span class="badge badge-primary">Medium</span></div>
                    <div class="task-item"><div class="task-checkbox"></div><div class="task-text">Send reminder for Food Drive</div><span class="badge badge-warning">High</span></div>
                    <div class="task-item"><div class="task-checkbox"></div><div class="task-text">Update event flyer</div><span class="badge badge-primary">Low</span></div>
                    <div class="task-item"><div class="task-checkbox"></div><div class="task-text">Respond to 3 volunteer messages</div><span class="badge badge-primary">Medium</span></div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header"><h2 class="card-title">Recent Activity</h2></div>
            <div class="event-list">
                <div class="event-item">
                    <div class="status-icon success">✓</div>
                    <div class="event-details">
                        <div class="event-title">Community Health Fair - Event Completed</div>
                        <div class="event-meta"><span>18 volunteers participated</span><span>•</span><span>2 hours ago</span></div>
                    </div>
                </div>
                <div class="event-item">
                    <div class="status-icon primary">👤</div>
                    <div class="event-details">
                        <div class="event-title">New volunteer application received</div>
                        <div class="event-meta"><span>Sarah Ahmed applied for Media Staff role</span><span>•</span><span>5 hours ago</span></div>
                    </div>
                </div>
                <div class="event-item">
                    <div class="status-icon warning">📝</div>
                    <div class="event-details">
                        <div class="event-title">Post-event report submitted</div>
                        <div class="event-meta"><span>Mohammed Ali submitted organizer report</span><span>•</span><span>1 day ago</span></div>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>
</body>
</html>
