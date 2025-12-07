{{-- resources/views/worker/payments.blade.php --}}
<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}"
      dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}"
      data-theme="dark">
{{-- resources/views/worker/payments.blade.php --}}
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payments - Worker Portal</title>

    <script src="{{ asset('js/preferences.js') }}" defer></script>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- 1) base layout/theme (same as event-discovery) --}}
    <link rel="stylesheet" href="{{ asset('css/worker/event-discovery.css') }}">

    {{-- 2) payment-specific styles on top --}}
    <link rel="stylesheet" href="{{ asset('css/worker/payments.css') }}">
</head>

<body>
<div class="container">
    <!-- Sidebar (copied to match event-discovery) -->
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
            {{-- same structure as event-discovery, but with dynamic "active" --}}
            <a href="{{ route('worker.dashboard') }}"
               class="nav-item {{ request()->routeIs('worker.dashboard') ? 'active' : '' }}">
                <span class="nav-icon">🏠</span>
                <span>Dashboard</span>
            </a>

            <a href="{{ route('worker.events.discover') }}"
               class="nav-item {{ request()->routeIs('worker.events.discover*') ? 'active' : '' }}">
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
    {{-- define $worker in the same scope --}}
    @php($worker = optional(auth()->user())->worker)

    @if($worker && !$worker->is_volunteer)
        <a href="{{ route('worker.payments.index') }}"
           class="nav-item {{ request()->routeIs('worker.payments.index') ? 'active' : '' }}">
            <span class="nav-icon">💰</span>
            <span>Payments</span>
        </a>
    @endif
    <a href="{{ route('worker.messages') }}" class="nav-item">
        <span class="nav-icon">💬</span>
        <span>Chat</span>
    </a>
    <a href="{{ route('worker.announcements.index') }}" class="nav-item">
        <span class="nav-icon">📢</span>
        <span>Announcements</span>
    </a>
    <a href="{{ route('settings') }}" class="nav-item">
        <span class="nav-icon">⚙️</span>
        <span>Settings</span>
    </a>


</nav>
    </aside>

    <!-- Main Content -->
    <main class="main-content">
        <div class="payments-page">

            {{-- header section visually similar to discovery header --}}
            <div class="payments-header">
                <div class="payments-header-left">
                    <h1 class="payments-title">Payment Summary</h1>
                    <p class="payments-subtitle">
                        Hello {{ $worker->user->first_name }}, this page shows your
                        <strong>paid work only</strong>.
                    </p>
                </div>

                <div class="payments-meta">
                    <div class="payments-meta-item">
                        <span class="meta-label">Engagement</span>
                        <span class="meta-value">{{ $worker->engagement_kind }}</span>
                    </div>
                    <div class="payments-meta-item">
                        <span class="meta-label">Hourly rate</span>
                        <span class="meta-value">{{ number_format($hourlyRate, 2) }} $</span>
                    </div>
                </div>
            </div>

            {{-- summary cards (use same card feeling as event cards) --}}
            <section class="payments-summary">
                <div class="summary-card">
                    <div class="summary-label">Total credited hours</div>
                    <div class="summary-value">{{ number_format($totalHours, 2) }}</div>
                    <div class="summary-unit">hours</div>
                </div>

                <div class="summary-card">
                    <div class="summary-label">Hourly rate</div>
                    <div class="summary-value">{{ number_format($hourlyRate, 2) }}</div>
                    <div class="summary-unit">$ / hour</div>
                </div>

                <div class="summary-card summary-card-highlight">
                    <div class="summary-label">Estimated total payment</div>
                    <div class="summary-value">{{ number_format($totalPay, 2) }}</div>
                    <div class="summary-unit">$</div>
                </div>
            </section>

            {{-- table section styled to match overall theme --}}
            <section class="payments-table-section">
                <div class="payments-table-header">
                    <h2>Completed Shifts</h2>
                    <p class="payments-table-subtitle">
                        Only reservations marked as <strong>COMPLETED</strong> are shown here.
                    </p>
                </div>

                <div class="payments-table-wrapper">
                    <table class="payments-table">
                        <thead>
                        <tr>
                            <th>Event</th>
                            <th>Date</th>
                            <th>Location</th>
                            <th>Credited hours</th>
                            <th>Amount</th>
                        </tr>
                        </thead>
                        <tbody id="paymentsTableBody"></tbody>
                    </table>
                </div>
            </section>
        </div>
    </main>
</div>

{{-- Bootstrap data for JS --}}
<script>
    window.paymentsData = {
        hourlyRate: {{ $hourlyRate ?? 0 }},
        reservations: @json($reservations ?? [])
    };
</script>

<script src="{{ asset('js/worker/payments.js') }}"></script>
@include('notify.widget')
<script src="{{ asset('js/notify-poll.js') }}" defer></script>
</body>
</html>
