<!doctype html>
<html lang="en" data-theme="dark">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width,initial-scale=1"/>
  <title>Send Announcement</title>
  <script src="{{ asset('js/preferences.js') }}" defer></script>
  <link rel="stylesheet" href="{{ asset('css/announcements/create.css') }}">
</head>

@php
    $user = auth()->user();
    $roleValue = strtoupper($user->role ?? '');
    $isAdmin = ($roleValue === 'ADMIN');
@endphp

<body class="{{ $isAdmin ? 'has-admin' : 'has-employee' }}">

{{-- ========================================================
     EMPLOYEE LAYOUT
======================================================== --}}
@if(!$isAdmin)
<div class="app-container">
    <aside class="sidebar employee-sidebar">

        {{-- USER BLOCK --}}
        @php($user = Auth::user())
        <div class="logo">
            <a href="{{ route('profile') }}" class="logo-link">

                @if($user->avatar_path)
                    <img src="{{ asset('storage/' . $user->avatar_path) }}" class="logo-avatar">
                @else
                    <div class="logo-icon">{{ strtoupper(substr($user->first_name ?? 'U', 0, 1)) }}</div>
                @endif

                <div class="logo-id">
                    <div class="logo-name">
                        {{ trim($user->first_name.' '.$user->last_name) }}
                    </div>
                    <div class="logo-role">{{ strtoupper($user->role) }}</div>
                </div>
            </a>
        </div>

        {{-- EMPLOYEE NAVIGATION --}}
        <nav class="nav-section">
            <div class="nav-label">Employee</div>

            <a href="{{ route('employee.dashboard') }}"
               class="nav-item {{ request()->routeIs('employee.dashboard') ? 'active' : '' }}">
                <span class="nav-icon">📊</span><span>Dashboard</span>
            </a>

            <a href="{{ route('employee.messages') }}" class="nav-item">
                <span class="nav-icon">💬</span><span>Messages</span>
            </a>

            <a href="{{ route('employee.announcements.index') }}" class="nav-item active">
                <span class="nav-icon">📢</span><span>Announcements</span>
            </a>
        </nav>

        <nav class="nav-section">
            <div class="nav-label">Account</div>

            <a href="{{ route('profile') }}" class="nav-item">
                <span class="nav-icon">👤</span><span>Profile</span>
            </a>

            <a href="{{ route('settings') }}" class="nav-item">
                <span class="nav-icon">⚙️</span><span>Settings</span>
            </a>
        </nav>

    </aside>

    {{-- EMPLOYEE FORM AREA --}}
    <main class="content-area">
        <div class="container">
            <h1>📢 Send Announcement</h1>

            @if(session('success'))
                <div class="alert success">{{ session('success') }}</div>
            @endif

            @if ($errors->any())
                <div class="alert danger">
                    <ul>
                        @foreach ($errors->all() as $e)
                            <li>{{ $e }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('announcements.store') }}">
                @csrf

                <label>Title</label>
                <input type="text" name="title" value="{{ old('title') }}" required>

                <label>Description</label>
                <textarea name="body" required>{{ old('body') }}</textarea>

                {{-- Employees cannot select audience --}}
                <input type="hidden" name="audience" value="workers">
                <div class="hint">Audience: Workers you manage</div>

                <button type="submit">Send Announcement</button>
            </form>
        </div>
    </main>
</div>

{{-- ========================================================
     ADMIN LAYOUT
======================================================== --}}
@else
<div class="admin-app">

    <aside class="sidebar admin-sidebar">

        {{-- USER BLOCK --}}
        @php($user = Auth::user())
        <div class="logo">
            <a href="{{ route('profile') }}" class="logo-link">

                @if($user->avatar_path)
                    <img src="{{ asset('storage/'.$user->avatar_path) }}" class="logo-avatar">
                @else
                    <div class="logo-icon">{{ strtoupper(substr($user->first_name ?? 'A', 0, 1)) }}</div>
                @endif

                <div class="logo-id">
                    <div class="logo-name">{{ trim($user->first_name.' '.$user->last_name) }}</div>
                    <div class="logo-role">ADMIN</div>
                </div>
            </a>
        </div>

        {{-- ADMIN NAVIGATION --}}
        <nav class="nav-section">
            

            <a href="{{ route('admin.dashboard') }}" class="nav-item">
                <span class="nav-icon">📊</span><span>Dashboard</span>
            </a>

            <a href="{{ route('employees.index') }}" class="nav-item">
                <span class="nav-icon">👔</span><span>Employees</span>
            </a>

            <a href="{{ route('volunteers.index') }}" class="nav-item">
                <span class="nav-icon">👥</span><span>Volunteers</span>
            </a>

            <a href="{{ route('events.index') }}" class="nav-item">
                <span class="nav-icon">📅</span><span>Events</span>
            </a>

            <a href="{{ route('taxonomies-venues.index') }}" class="nav-item active">
                <span class="nav-icon">🏷️</span><span>Taxonomies & Venues</span>
            </a>

            <a href="{{ route('announcements.create') }}" class="nav-item">
                <span class="nav-icon">📢</span><span>Send Announcement</span>
            </a>
        </nav>

        <nav class="nav-section">
            <div class="nav-label">Account</div>

            <a href="{{ route('profile') }}" class="nav-item">
                <span class="nav-icon">👤</span><span>Profile</span>
            </a>

            <a href="{{ route('settings') }}" class="nav-item">
                <span class="nav-icon">⚙️</span><span>Settings</span>
            </a>
        </nav>

    </aside>

    {{-- ADMIN FORM AREA --}}
    <main class="admin-content-area">
        <div class="container">
            <h1>📢 Send Announcement</h1>

            @if(session('success'))
                <div class="alert success">{{ session('success') }}</div>
            @endif

            @if ($errors->any())
                <div class="alert danger">
                    <ul>
                        @foreach ($errors->all() as $e)
                            <li>{{ $e }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('announcements.store') }}">
                @csrf

                <label>Title</label>
                <input type="text" name="title" value="{{ old('title') }}" placeholder="Announcement Title" required>

                <label>Description</label>
                <textarea name="body" required>{{ old('body') }}</textarea>

                <label>Audience</label>
                <select name="audience" required>
                    <option value="workers"   {{ old('audience')==='workers' ? 'selected' : '' }}>Workers</option>
                    <option value="employees" {{ old('audience')==='employees' ? 'selected' : '' }}>Employees</option>
                    <option value="both"      {{ old('audience')==='both' ? 'selected' : '' }}>Both</option>
                </select>

                <button type="submit">Send Announcement</button>
            </form>
        </div>
    </main>

</div>
@endif

<script src="{{ asset('js/announcements/create.js') }}"></script>
@include('notify.widget')
<script src="{{ asset('js/notify-poll.js') }}" defer></script>

</body>
</html>
