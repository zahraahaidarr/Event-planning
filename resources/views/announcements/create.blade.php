<!doctype html>
<html lang="en" data-theme="dark">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width,initial-scale=1"/>
  <title>Send Announcement</title>
  <link rel="stylesheet" href="{{ asset('css/announcements.css') }}">
</head>
@php
    $user = auth()->user();
    $roleValue = strtoupper($user->role ?? '');
    $isAdmin = ($roleValue === 'ADMIN');
@endphp
<body class="{{ $isAdmin ? 'has-admin' : 'has-employee' }}">
@if(!$isAdmin)
  <!-- ====== EMPLOYEE LAYOUT (unchanged) ====== -->
  <div class="app-container">
    <aside class="sidebar employee-sidebar">
      <div class="logo">
        <div class="logo-icon">🎯</div>
        <span>VolunteerHub</span>
      </div>

      <nav>
        <div class="nav-section">
          <div class="nav-label">Employee</div>
          <a href="{{ url('employee-dashboard') }}" class="nav-item">
            <span class="nav-icon">📊</span><span>Dashboard</span>
          </a>
          <a href="{{ url('event-management') }}" class="nav-item">
            <span class="nav-icon">📅</span><span>Event Management</span>
          </a>
          <a href="{{ url('volunteer-assignment') }}" class="nav-item">
            <span class="nav-icon">👥</span><span>Volunteer Assignment</span>
          </a>
          <a href="{{ url('employee-reports') }}" class="nav-item">
            <span class="nav-icon">📝</span><span>Post-Event Reports</span>
          </a>
        </div>

        <div class="nav-section">
          <div class="nav-label">Communication</div>
          <a href="{{ url('messages') }}" class="nav-item">
            <span class="nav-icon">💬</span><span>Messages</span>
          </a>
          <a href="{{ url('announcements') }}" class="nav-item active">
            <span class="nav-icon">📢</span><span>Announcements</span>
          </a>
        </div>

        <div class="nav-section">
          <div class="nav-label">Account</div>
          <a href="{{ url('profile') }}" class="nav-item">
            <span class="nav-icon">👤</span><span>Profile</span>
          </a>
          <a href="{{ url('settings') }}" class="nav-item">
            <span class="nav-icon">⚙️</span><span>Settings</span>
          </a>
        </div>
      </nav>
    </aside>

    <!-- keep your form styling exactly; just centered next to sidebar -->
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

          {{-- Employees cannot choose the audience --}}
          <input type="hidden" name="audience" value="workers">
          <div class="hint">Audience: Workers you manage</div>

          <button type="submit">Send Announcement</button>
        </form>
      </div>
    </main>
  </div>
@else
  <!-- ====== ADMIN LAYOUT WITH EXACT ADMIN DASHBOARD SIDEBAR ====== -->
  <div class="admin-app">
    <aside class="sidebar admin-sidebar">
      <div class="logo">
        <div class="logo-icon">V</div>
        <span class="logo-text">VolunteerHub</span>
      </div>

      <nav class="nav-section">
        <div class="nav-label">Admin</div>
        <a href="{{ Route::has('admin.dashboard') ? route('admin.dashboard') : '#' }}" class="nav-item">
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
            <a href="#" class="nav-item">
                <span class="nav-icon">🔧</span><span>Settings</span>
            </a>
      </nav>
    </aside>

    <!-- keep your original .container EXACTLY, just placed to the right -->
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
          <input type="text" name="title" value="{{ old('title') }}" placeholder="Announcment Title" required>

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

<script src="{{ asset('js/announcements.js') }}"></script>
</body>
</html>
