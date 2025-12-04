<!DOCTYPE html>
<html lang="{{ app()->getLocale() === 'ar' ? 'ar' : 'en' }}"
      dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}"
      data-theme="dark">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <title>Announcements • VolunteerHub</title>
    <script src="{{ asset('js/preferences.js') }}" defer></script>
    <link rel="stylesheet" href="{{ asset('css/announcements/index.css') }}">
</head>
<body>
<div class="app-container">

    {{-- =================== SIDEBAR (DYNAMIC) =================== --}}
    @if ($role === 'employee')
        {{-- EMPLOYEE SIDEBAR – same structure as employee pages --}}
        <aside class="sidebar employee-sidebar">
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
                    {{-- (no label at top on the other employee pages either) --}}

                    <a href="{{ Route::has('employee.dashboard') ? route('employee.dashboard') : '#' }}"
                       class="nav-item {{ request()->routeIs('employee.dashboard') ? 'active' : '' }}">
                        <span class="nav-icon">📊</span><span>Dashboard</span>
                    </a>

                    <a href="{{ Route::has('events.index') ? route('events.index') : '#' }}"
                       class="nav-item">
                        <span class="nav-icon">📅</span><span>Event Management</span>
                    </a>

                    <a href="{{ route('employee.volunteer.assignment') }}"
                       class="nav-item {{ request()->routeIs('volunteers.assign') ? 'active' : '' }}">
                        <span class="nav-icon">👥</span><span>Volunteer Assignment</span>
                    </a>

                    <a href="{{ route('employee.postEventReports.index') }}"
                   class="nav-item">
                    <span class="nav-icon">📝</span><span>Post-Event Reports</span>
                </a>
                </div>

                <div class="nav-section">
                    <div class="nav-label">Communication</div>

                    <a href="{{ Route::has('employee.messages') ? route('employee.messages') : '#' }}"
                       class="nav-item {{ request()->routeIs('employee.messages') ? 'active' : '' }}">
                        <span class="nav-icon">💬</span><span>Messages</span>
                    </a>

                    <a href="{{ route('announcements.create') }}"
                       class="nav-item {{ request()->routeIs('announcements.create') ? 'active' : '' }}">
                        <span class="nav-icon">📢</span><span>Send Announcement</span>
                    </a>

                    <a href="{{ Route::has('employee.announcements.index') ? route('employee.announcements.index') : '#' }}"
                       class="nav-item {{ request()->routeIs('employee.announcements.index') ? 'active' : '' }}">
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

    @else {{-- WORKER (default if not employee) --}}
        {{-- WORKER SIDEBAR – unchanged, just added class worker-sidebar --}}
        <aside class="sidebar worker-sidebar">
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
                            {{ strtoupper($user->role ?? 'WORKER') }}
                        </div>
                    </div>
                </a>
            </div>

            <nav class="nav-section">
                {{-- same links as Discover Events page --}}
                <a href="{{ route('worker.dashboard') }}"
                   class="nav-item {{ request()->routeIs('worker.dashboard') ? 'active' : '' }}">
                    <span class="nav-icon">🏠</span>
                    <span>Dashboard</span>
                </a>
                <a href="{{ route('worker.events.discover') }}"
                   class="nav-item {{ request()->routeIs('worker.events.discover') ? 'active' : '' }}">
                    <span class="nav-icon">🗓️</span>
                    <span>Discover Events</span>
                </a>
                <a href="{{ route('worker.reservations') }}"
                   class="nav-item {{ request()->routeIs('worker.reservations') ? 'active' : '' }}">
                    <span class="nav-icon">✅</span>
                    <span>My Reservations</span>
                </a>
                <a href="{{ route('worker.submissions') }}"
                   class="nav-item {{ request()->routeIs('worker.submissions') ? 'active' : '' }}">
                    <span class="nav-icon">📝</span>
                    <span>Post-Event Submissions</span>
                </a>
            </nav>

            <nav class="nav-section">
                <div class="nav-label">Account</div>

                <a href="{{ route('worker.messages') }}"
                   class="nav-item {{ request()->routeIs('worker.messages') ? 'active' : '' }}">
                    <span class="nav-icon">💬</span>
                    <span>Chat</span>
                </a>

                <a href="{{ route('worker.announcements.index') }}"
                   class="nav-item {{ request()->routeIs('worker.announcements.index') ? 'active' : '' }}">
                    <span class="nav-icon">📢</span>
                    <span>Announcements</span>
                </a>

                <a href="{{ route('settings') }}"
                   class="nav-item {{ request()->routeIs('worker.settings') ? 'active' : '' }}">
                    <span class="nav-icon">⚙️</span>
                    <span>Settings</span>
                </a>
            </nav>
        </aside>
    @endif

    {{-- =================== MAIN CONTENT =================== --}}
    <main class="main-content" id="main">
        <div class="header">
            <h1 class="header-title" id="pageTitle">Announcements</h1>
        </div>

        <section class="card" style="margin-bottom:20px;">
            <div class="card-header">
                <h2 class="card-title" id="pageSubtitle">
                    Important updates and news for the volunteer community.
                </h2>
            </div>
        </section>

        <div class="topbar">
            <div class="search" role="search">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none">
                    <path d="m21 21-4.2-4.2M10.8 18a7.2 7.2 0 1 1 0-14.4 7.2 7.2 0 0 1 0 14.4Z"
                          stroke="currentColor" stroke-width="1.6" opacity=".55"/>
                </svg>
                <input id="globalSearch" placeholder="Search announcements…" aria-label="Search announcements"/>
            </div>
        </div>

        <section>
            <div class="list" id="announceList" aria-live="polite"></div>
        </section>
    </main>
</div>

<script>
    window.initialAnnouncements = @json($announcements);
    window.currentRole          = @json($role);
</script>
<script src="{{ asset('js/announcements/index.js') }}"></script>
@include('notify.widget')
<script src="{{ asset('js/notify-poll.js') }}" defer></script>

</body>
</html>
