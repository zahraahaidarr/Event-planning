{{-- resources/views/Admin/employees.blade.php --}}
<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}"
      dir="{{ app()->getLocale()==='ar' ? 'rtl' : 'ltr' }}"
      data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee Management - Admin Portal</title>

    {{-- If you aren’t using Vite, keep asset() with files placed under public/ --}}
    <link rel="stylesheet" href="{{ asset('css/Admin/employees.css') }}">
    <script src="{{ asset('js/Admin/employees.js') }}" defer></script>
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

            <a href="{{ Route::has('employees.index') ? route('employees.index') : '#' }}"
               class="nav-item {{ request()->routeIs('employees.index') ? 'active' : '' }}">
                <span class="nav-icon">👔</span><span>Employees</span>
            </a>

            <a href="{{ Route::has('volunteers.index') ? route('volunteers.index') : '#' }}" class="nav-item">
                <span class="nav-icon">👥</span><span>Volunteers</span>
            </a>

            <a href="{{ Route::has('events.index') ? route('events.index') : '#' }}" class="nav-item">
                <span class="nav-icon">📅</span><span>Events</span>
            </a>
        </nav>

        <nav class="nav-section">
            <div class="nav-label">Account</div>

            <a href="{{ Route::has('profile.show') ? route('profile.show') : '#' }}" class="nav-item">
                <span class="nav-icon">👤</span><span>Profile</span>
            </a>

            <a href="{{ Route::has('settings') ? route('settings') : '#' }}" class="nav-item">
                <span class="nav-icon">🔧</span><span>Settings</span>
            </a>

        </nav>
    </aside>

    {{-- Main --}}
    <main class="main-content">
        <div class="header">
            <div class="header-left">
                <h1>Employee Management</h1>
                <p>Manage employee accounts and permissions</p>
            </div>

            <div class="header-actions">
                <button class="btn btn-primary" id="btnAddEmployee">
                    <span>➕</span>
                    Add Employee
                </button>
                <button class="icon-btn" id="btnTheme" title="Toggle theme">
                    <span id="theme-icon">☀️</span>
                </button>
                <button class="icon-btn" id="btnLang" title="Toggle language">
                    <span id="lang-icon">{{ app()->getLocale()==='ar' ? 'EN' : 'AR' }}</span>
                </button>
            </div>
        </div>

        {{-- Search --}}
        <div class="search-section">
            <div class="search-input-wrapper">
                <span class="search-icon">🔍</span>
                <input type="text" class="search-input"
                       placeholder="Search employees by name, email, or role..."
                       id="searchInput">
            </div>
        </div>

        {{-- Employees Grid --}}
        <div class="employees-grid" id="employeesGrid">
            {{-- Cards are rendered by employees.js --}}
        </div>
    </main>
</div>
</body>
</html>
