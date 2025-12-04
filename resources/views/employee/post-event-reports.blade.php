<!doctype html>
<html lang="{{ app()->getLocale() }}"
      data-theme="dark"
      dir="{{ app()->getLocale()==='ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Post-Event Reports • Employee</title>

    <link rel="stylesheet" href="{{ asset('css/employee/post-event-reports.css') }}">
    <script src="{{ asset('js/employee/post-event-reports.js') }}" defer></script>
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

    <main class="main-content">
        <div class="header">
            <h1 class="header-title">Post-Event Reports</h1>

           
        </div>

        @if(session('success'))
            <div class="alert-success">
                {{ session('success') }}
            </div>
        @endif

        {{-- Stats --}}
        <div class="stats-bar">
            <div class="stat-item">
                <div class="stat-value" style="color: var(--warning);">
                    {{ $stats['pending'] }}
                </div>
                <div class="stat-label">Pending Review</div>
            </div>
            <div class="stat-item">
                <div class="stat-value" style="color: var(--success);">
                    {{ $stats['approved'] }}
                </div>
                <div class="stat-label">Approved</div>
            </div>
            <div class="stat-item">
                <div class="stat-value" style="color: var(--danger);">
                    {{ $stats['rejected'] }}
                </div>
                <div class="stat-label">Rejected</div>
            </div>
            <div class="stat-item">
                <div class="stat-value" style="color: var(--primary);">
                    {{ $stats['total'] }}
                </div>
                <div class="stat-label">Total Reports</div>
            </div>
        </div>

        {{-- Filters --}}
        <form class="filters" id="filtersForm" method="get" action="{{ route('employee.postEventReports.index') }}">
            <div class="filter-group">
                <span class="filter-label">Event:</span>
                <select name="event_id" id="eventFilter">
                    <option value="">All Events</option>
                    @foreach($filterEvents as $event)
                        <option value="{{ $event->event_id }}"
                            {{ (int)($filters['event_id'] ?? 0) === $event->event_id ? 'selected' : '' }}>
                            {{ $event->title }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="filter-group">
                <span class="filter-label">Role:</span>
                <select name="role_slug" id="roleFilter">
                    <option value="">All Roles</option>
                    @foreach($filterRoles as $role)
                        <option value="{{ $role->role_type_id }}"
                            {{ (int)($filters['role_slug'] ?? 0) === $role->role_type_id ? 'selected' : '' }}>
                            {{ $role->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="filter-group">
                <span class="filter-label">Status:</span>
                <select name="status" id="statusFilter">
                    <option value="">All Status</option>
                    <option value="pending"  {{ ($filters['status'] ?? '') === 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="approved" {{ ($filters['status'] ?? '') === 'approved' ? 'selected' : '' }}>Approved</option>
                    <option value="rejected" {{ ($filters['status'] ?? '') === 'rejected' ? 'selected' : '' }}>Rejected</option>
                </select>
            </div>

            <div class="filter-group">
                <span class="filter-label">Search:</span>
                <input type="text"
                       id="searchInput"
                       name="search"
                       placeholder="Volunteer name..."
                       value="{{ $filters['search'] ?? '' }}">
            </div>
        </form>

        {{-- Reports list --}}
        <div class="reports-grid">
            @forelse($submissions as $submission)
                <div class="report-card">
                    <div class="report-header">
                        <div class="report-info">
                            <div class="report-title">
                                {{ $submission->event->title ?? 'Event #'.$submission->event_id }}
                                - {{ $submission->role_label }} Report
                            </div>

                            <div class="report-meta">
                                <span>
                                    👤
                                    {{ optional(optional($submission->worker)->user)->name
                                       ?? $submission->worker->name
                                       ?? 'Worker #'.$submission->worker_id }}
                                </span>
                                <span>
                                    📅
                                    {{ optional($submission->submitted_at)->format('Y-m-d H:i') }}
                                </span>
                                 <span class="role-badge">
        🏷️ {{ $submission->workRole->role_name
           ?? $submission->role_label
           ?? ucfirst($submission->role_slug ?? 'Role') }}
    </span>
                            </div>
                        </div>

                        {{-- STATUS BADGE TOP RIGHT --}}
                        <div class="report-status status-{{ $submission->status }}">
                            {{ ucfirst($submission->status) }}
                        </div>
                    </div>

                    {{-- General Notes + JSON data --}}
                    <div class="report-content">
                        @if($submission->general_notes)
                            <strong>General Notes:</strong><br>
                            {{ $submission->general_notes }}<br><br>
                        @endif

                        @if(is_array($submission->data) && count($submission->data))
                            <strong>Details:</strong>
                            <ul class="data-list">
                                @foreach($submission->data as $key => $value)
                                    <li>
                                        <span class="data-key">{{ ucfirst(str_replace('_',' ',$key)) }}:</span>
                                        <span class="data-value">
                                            @if(is_array($value))
                                                {{ json_encode($value, JSON_UNESCAPED_UNICODE) }}
                                            @else
                                                {{ $value }}
                                            @endif
                                        </span>
                                    </li>
                                @endforeach
                            </ul>
                        @else
                            <em>No structured data submitted.</em>
                        @endif

                        {{-- Civil defense cases block --}}
                        @if($submission->civilCases->isNotEmpty())
                            <br>
                            <strong>Civil Cases:</strong>
                            <table class="civil-table">
                                <thead>
                                <tr>
                                    <th>Type</th>
                                    <th>Age</th>
                                    <th>Gender</th>
                                    <th>Action Taken</th>
                                    <th>Notes</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($submission->civilCases as $case)
                                    <tr>
                                        <td>{{ $case->case_type }}</td>
                                        <td>{{ $case->age }}</td>
                                        <td>{{ ucfirst($case->gender) }}</td>
                                        <td>{{ $case->action_taken }}</td>
                                        <td>{{ $case->notes }}</td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        @endif
                    </div>

                    {{-- Media files --}}
                    @if($submission->files->isNotEmpty())
                        <div class="media-grid">
                            @foreach($submission->files as $file)
                                <a href="{{ $file->url }}" class="media-item" target="_blank" title="{{ $file->original_name }}">
                                    @if(\Illuminate\Support\Str::startsWith($file->mime_type, 'image/'))
                                        📷
                                    @elseif(\Illuminate\Support\Str::startsWith($file->mime_type, 'video/'))
                                        🎥
                                    @else
                                        📎
                                    @endif
                                </a>
                            @endforeach
                        </div>
                    @endif

                    {{-- Review notes / comments --}}
                    <div class="comment-section">
                        @if($submission->review_notes)
                            <div class="existing-comment">
                                <strong>Employee Notes:</strong><br>
                                {{ $submission->review_notes }}
                            </div>
                        @endif

                        @if($submission->status !== 'approved' && $submission->status !== 'rejected')
                            <textarea class="comment-input"
                                      placeholder="Add internal notes (optional)..."
                                      data-submission-id="{{ $submission->id }}"></textarea>
                        @endif
                    </div>

                    {{-- Actions --}}
                    <div class="report-actions">
                        @if($submission->status === 'approved')
                            <button class="btn btn-secondary" disabled>✓ Already Approved</button>
                        @elseif($submission->status === 'rejected')
                            <button class="btn btn-secondary" disabled>✗ Rejected</button>
                        @else
                            <form action="{{ route('employee.postEventReports.approve', $submission) }}"
                                  method="post"
                                  class="inline-form approve-form">
                                @csrf
                                <input type="hidden" name="review_notes">
                                <button class="btn btn-primary" type="submit">✓ Approve</button>
                            </form>

                            <form action="{{ route('employee.postEventReports.reject', $submission) }}"
                                  method="post"
                                  class="inline-form reject-form">
                                @csrf
                                <input type="hidden" name="reason">
                                <button class="btn btn-secondary" type="submit">✗ Reject</button>
                            </form>
                        @endif
                    </div>
                </div>
            @empty
                <p>No post-event reports found.</p>
            @endforelse
        </div>

        <div class="pagination-wrapper">
            {{ $submissions->links() }}
        </div>
    </main>
</div>
<script>
document.addEventListener("DOMContentLoaded", function () {
    const searchInput = document.getElementById("searchInput");
    const form = document.getElementById("filtersForm");

    let typingTimer;
    const delay = 400; // milliseconds

    searchInput.addEventListener("keyup", function () {
        clearTimeout(typingTimer);
        typingTimer = setTimeout(() => form.submit(), delay);
    });

    searchInput.addEventListener("keydown", function () {
        clearTimeout(typingTimer);
    });
});
</script>
@include('notify.widget')
<script src="{{ asset('js/notify-poll.js') }}" defer></script>

</body>
</html>
