{{-- resources/views/Admin/dashboard.blade.php --}}
<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale()==='ar' ? 'rtl' : 'ltr' }}" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - VolunteerHub</title>
    <link rel="stylesheet" href="{{ asset('css/Admin/dashboard.css') }}">
    <script src="{{ asset('js/Admin/dashboard.js') }}" defer></script>
</head>
<body>
<div class="container">

    {{-- Sidebar --}}
    <aside class="sidebar">
        <div class="logo">
            <div class="logo-icon">V</div>
            <span class="logo-text">VolunteerHub</span>
        </div>

        <nav class="nav-section">
            <div class="nav-label">Admin</div>

            <a href="{{ Route::has('admin.dashboard') ? route('admin.dashboard') : '#' }}"
               class="nav-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                <span class="nav-icon">📊</span><span>Dashboard</span>
            </a>

            <a href="{{ Route::has('employees.index') ? route('employees.index') : '#' }}" class="nav-item">
                <span class="nav-icon">👔</span><span>Employees</span>
            </a>

            <a href="{{ Route::has('volunteers.index') ? route('volunteers.index') : '#' }}" class="nav-item">
                <span class="nav-icon">👥</span><span>Volunteers</span>
            </a>

            <a href="{{ Route::has('events.index') ? route('events.index') : '#' }}" class="nav-item">
                <span class="nav-icon">📅</span><span>Events</span>
            </a>
            <a href="{{ route('announcements.create') }}" class="nav-item">
                <span class="nav-icon">📢</span><span>Send Announcement</span>
            </a>
        </nav>

        <nav class="nav-section">
            <div class="nav-label">Account</div>

            <a href="{{ Route::has('profile.show') ? route('profile.show') : '#' }}" class="nav-item">
                <span class="nav-icon">👤</span><span>Profile</span>
            </a>
<a href="{{ Route::has('settings.show') ? route('settings.show') : '#' }}" class="nav-item">
  <span class="nav-icon">🔧</span><span>Settings</span>
</a>

            
        </nav>
    </aside>

    {{-- Main --}}
    <main class="main-content">
        <div class="header">
            <div class="header-left">
                <h1>Admin Dashboard</h1>
                <p>System overview and management</p>
            </div>

            <div class="header-actions">
                <button class="icon-btn" onclick="toggleTheme()" title="Toggle theme">
                    <span id="theme-icon">☀️</span>
                </button>

                <button class="icon-btn" onclick="toggleLanguage()" title="Toggle language">
                    <span id="lang-icon">{{ app()->getLocale()==='ar' ? 'EN' : 'AR' }}</span>
                </button>

                {{-- Header Logout (new) --}}
                @if(Route::has('logout'))
                    <form method="POST" action="{{ route('logout') }}" class="logout-form">
                      @csrf
                        <button type="submit" class="icon-btn logout-btn">Logout</button>
                    </form>
                @endif


            </div>
        </div>

        {{-- Stats --}}
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-header">
                    <span class="stat-label">Total Volunteers</span>
                    <div class="stat-icon" style="background:rgba(79,124,255,.18);color:var(--primary)">👥</div>
                </div>
                <div class="stat-value">1,247</div>
                <div class="stat-change positive">+48 this month</div>
            </div>

            <div class="stat-card">
                <div class="stat-header">
                    <span class="stat-label">Active Employees</span>
                    <div class="stat-icon" style="background:rgba(54,211,153,.18);color:var(--success)">👔</div>
                </div>
                <div class="stat-value">32</div>
                <div class="stat-change">All active</div>
            </div>

            <div class="stat-card">
                <div class="stat-header">
                    <span class="stat-label">Total Events</span>
                    <div class="stat-icon" style="background:rgba(156,108,255,.18);color:var(--accent)">📅</div>
                </div>
                <div class="stat-value">156</div>
                <div class="stat-change positive">+12 this month</div>
            </div>

            <div class="stat-card">
                <div class="stat-header">
                    <span class="stat-label">Hours Volunteered</span>
                    <div class="stat-icon" style="background:rgba(244,191,80,.18);color:var(--warning)">⏱️</div>
                </div>
                <div class="stat-value">8,432</div>
                <div class="stat-change positive">+524 this month</div>
            </div>
        </div>

        {{-- Charts --}}
        <div class="charts-section">
            <div class="chart-card">
                <h2 class="chart-title">Volunteer Growth</h2>
                <div class="chart-placeholder">Chart: Monthly volunteer registrations</div>
            </div>

            <div class="chart-card">
                <h2 class="chart-title">Event Categories</h2>
                <div class="chart-placeholder">Chart: Events by category distribution</div>
            </div>
        </div>

        {{-- Recent Activity --}}
        <div class="recent-section">
            <div class="activity-card">
                <h2 class="section-title">Recent Employees</h2>
                <div class="activity-list">
                    <div class="activity-item">
                        <div class="activity-info">
                            <h4>Ahmed Al-Rashid</h4>
                            <p>Event Coordinator — Joined 2 days ago</p>
                        </div>
                        <span class="activity-badge badge-active">Active</span>
                    </div>
                    <div class="activity-item">
                        <div class="activity-info">
                            <h4>Fatima Hassan</h4>
                            <p>Volunteer Manager — Joined 5 days ago</p>
                        </div>
                        <span class="activity-badge badge-active">Active</span>
                    </div>
                    <div class="activity-item">
                        <div class="activity-info">
                            <h4>Mohammed Ali</h4>
                            <p>Event Coordinator — Joined 1 week ago</p>
                        </div>
                        <span class="activity-badge badge-active">Active</span>
                    </div>
                </div>
            </div>

            <div class="activity-card">
                <h2 class="section-title">Recent Events</h2>
                <div class="activity-list">
                    <div class="activity-item">
                        <div class="activity-info">
                            <h4>Community Garden Cleanup</h4>
                            <p>Environment — 18/20 volunteers</p>
                        </div>
                        <span class="activity-badge badge-active">Active</span>
                    </div>
                    <div class="activity-item">
                        <div class="activity-info">
                            <h4>Health Awareness Campaign</h4>
                            <p>Health — 7/25 volunteers</p>
                        </div>
                        <span class="activity-badge badge-pending">Pending</span>
                    </div>
                    <div class="activity-item">
                        <div class="activity-info">
                            <h4>Beach Cleanup Initiative</h4>
                            <p>Environment — 35/50 volunteers</p>
                        </div>
                        <span class="activity-badge badge-active">Active</span>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>
</body>
</html>
