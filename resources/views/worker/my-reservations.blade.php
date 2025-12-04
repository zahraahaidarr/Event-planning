<!doctype html>
<html
    lang="{{ app()->getLocale() }}"
    dir="{{ app()->getLocale()==='ar' ? 'rtl' : 'ltr' }}"
    data-theme="dark"
>
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1"/>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>My Reservations • Worker</title>

    <script src="{{ asset('js/preferences.js') }}" defer></script>
    <link rel="stylesheet" href="{{ asset('css/worker/my-reservations.css') }}">
</head>

<body data-discover-url="{{ route('worker.events.discover') }}">
<div class="container">

    <!-- Sidebar -->
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
                    {{ strtoupper($user->role ?? 'WORKER') }}
                </div>
            </div>
        </a>
    </div>

    <nav class="nav-section">
        {{-- main worker links --}}
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
           class="nav-item {{ request()->routeIs('settings') ? 'active' : '' }}">
            <span class="nav-icon">⚙️</span>
            <span>Settings</span>
        </a>
    </nav>
</aside>


    <!-- Main Content -->
    <main class="main-content">

        <div class="header">
            <div class="header-left">
                <h1>My Reservations</h1>
                <p>Track your volunteer applications and commitments</p>
            </div>

            
        </div>

        <!-- Stats -->
        <div class="stats-grid">
            <!-- Total Applications -->
            <div class="stat-card">
                <div class="stat-header">
                    <span class="stat-label">Total Applications</span>
                    <div class="stat-icon" style="background:#e7f3ff;">📝</div>
                </div>
                <div class="stat-value">
                    {{ $stats['totalApplications'] }}
                </div>
                <div class="stat-change positive">
                    +{{ $stats['applicationsThisMonth'] }} this month
                </div>
            </div>

            <!-- Reserved -->
            <div class="stat-card">
                <div class="stat-header">
                    <span class="stat-label">Reserved</span>
                    <div class="stat-icon" style="background:#d1f4e0;">✅</div>
                </div>
                <div class="stat-value">
                    {{ $stats['reservedCount'] }}
                </div>
                <div class="stat-change positive">Confirmed spots</div>
            </div>

            <!-- Completed -->
            <div class="stat-card">
                <div class="stat-header">
                    <span class="stat-label">Completed</span>
                    <div class="stat-icon" style="background:#d1ecf1;">🎉</div>
                </div>
                <div class="stat-value">
                    {{ $stats['completedCount'] }}
                </div>
                <div class="stat-change positive">
                    +{{ $stats['completedThisMonth'] }} this month
                </div>
            </div>
        </div>

        <!-- Tabs -->
        <div class="tabs">
            <button class="tab active" data-tab="all">All</button>
            <button class="tab" data-tab="pending">Pending</button>
            <button class="tab" data-tab="reserved">Reserved</button>
            <button class="tab" data-tab="completed">Completed</button>
            <button class="tab" data-tab="rejected">Rejected</button>
            <button class="tab" data-tab="cancelled">Cancelled</button> 
        </div>

        <!-- Reservations List -->
        <div class="reservations-list" id="reservationsList"></div>

    </main>
</div>

<!-- Bootstrapped Data -->
<script>
    window.initialReservations = @json($reservationsBootstrap ?? []);
     window.submissionsUrl = "{{ route('worker.submissions') }}";
</script>

<script src="{{ asset('js/worker/my-reservations.js') }}" defer></script>
@include('notify.widget')
<script src="{{ asset('js/notify-poll.js') }}" defer></script>
</body>
</html>
