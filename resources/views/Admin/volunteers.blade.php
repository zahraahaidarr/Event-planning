{{-- resources/views/Admin/volunteers.blade.php --}}
<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale()==='ar' ? 'rtl' : 'ltr' }}" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Volunteer Oversight - Admin Portal</title>

    {{-- Static assets served from /public --}}
    <link rel="stylesheet" href="{{ asset('css/Admin/volunteers.css') }}">
    <script src="{{ asset('js/Admin/volunteers.js') }}" defer></script>
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

            <a href="{{ Route::has('volunteers.index') ? route('volunteers.index') : '#' }}"
               class="nav-item {{ request()->routeIs('volunteers.index') ? 'active' : '' }}">
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

            {{-- Optional settings link (static) --}}
            <a href="#" class="nav-item">
                <span class="nav-icon">🔧</span><span>Settings</span>
            </a>
        </nav>
    </aside>

    {{-- Main --}}
    <main class="main-content">
        <div class="header">
            <div class="header-left">
                <h1>Volunteer Oversight</h1>
                <p>Monitor and manage volunteer accounts</p>
            </div>
            <div class="header-actions">
                <button class="icon-btn" onclick="toggleTheme()" title="Toggle theme">
                    <span id="theme-icon">☀️</span>
                </button>
                <button class="icon-btn" onclick="toggleLanguage()" title="Toggle language">
                    <span id="lang-icon">{{ app()->getLocale()==='ar' ? 'EN' : 'AR' }}</span>
                </button>
            </div>
        </div>

        {{-- Filters --}}
        <div class="filters-section">
            <div class="filters-grid">
                <div class="filter-group">
                    <label class="filter-label">Role</label>
                    <select class="filter-select" id="filterRole" onchange="renderVolunteers()">
                        <option value="">All Roles</option>
                        <option value="Organizer">Organizer</option>
                        <option value="Civil Defense">Civil Defense</option>
                        <option value="Media Staff">Media Staff</option>
                        <option value="Tech Support">Tech Support</option>
                        <option value="Cleaner">Cleaner</option>
                        <option value="Decorator">Decorator</option>
                        <option value="Gardener">Gardener</option>
                    </select>
                </div>

                <div class="filter-group">
                    <label class="filter-label">Approval</label>
                    <select class="filter-select" id="filterApproval" onchange="renderVolunteers()">
                        <option value="">All</option>
                        <option value="approved">Approved</option>
                        <option value="pending">Pending</option>
                    </select>
                </div>

                <div class="filter-group">
                    <label class="filter-label">Location</label>
                    <select class="filter-select" id="filterLocation" onchange="renderVolunteers()">
                        <option value="">All Locations</option>
                        <option value="Riyadh">Riyadh</option>
                        <option value="Jeddah">Jeddah</option>
                        <option value="Dammam">Dammam</option>
                    </select>
                </div>

                <div class="filter-group">
                    <label class="filter-label">Status</label>
                    <select class="filter-select" id="filterStatus" onchange="renderVolunteers()">
                        <option value="">All Status</option>
                        <option value="active">Active</option>
                        <option value="suspended">Suspended</option>
                        <option value="banned">Banned</option>
                    </select>
                </div>
            </div>
        </div>

        {{-- Table --}}
        <div class="table-container">
            <table class="table">
                <thead>
                <tr>
                    <th>Name</th>
                    <th>Role</th>
                    <th>Location</th>
                    <th>Events</th>
                    <th>Hours</th>
                    <th>Status</th>
                    <th>Approval</th>
                    <th>Actions</th>
                </tr>
                </thead>
                <tbody id="volunteersTable">
                    {{-- Rows injected by volunteers.js --}}
                </tbody>
            </table>
        </div>
    </main>
</div>
</body>
</html>
