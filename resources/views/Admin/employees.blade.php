<!DOCTYPE html>
<html lang="en" dir="ltr" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee Management - Admin Portal</title>

    {{-- Styles --}}
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
            <a href="{{ route('admin.dashboard') }}" class="nav-item">
                <span class="nav-icon">📊</span><span>Dashboard</span>
            </a>
            <a href="{{ route('employees.index') }}" class="nav-item active">
                <span class="nav-icon">👔</span><span>Employees</span>
            </a>
            <a href="{{ route('volunteers.index') }}" class="nav-item">
                <span class="nav-icon">👥</span><span>Volunteers</span>
            </a>
            <a href="#" class="nav-item">
                <span class="nav-icon">📅</span><span>Events</span>
            </a>
        </nav>

        <nav class="nav-section">
            <div class="nav-label">Account</div>
            <a href="#" class="nav-item">
                <span class="nav-icon">👤</span><span>Profile</span>
            </a>
            <a href="#" class="nav-item">
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
                <button class="btn btn-primary" onclick="openAddModal()">
                    <span>➕</span> Add Employee
                </button>
                <button class="icon-btn" onclick="toggleTheme()" title="Toggle theme">
                    <span id="theme-icon">☀️</span>
                </button>
                <button class="icon-btn" onclick="toggleLanguage()" title="Toggle language">
                    <span id="lang-icon">AR</span>
                </button>
            </div>
        </div>

        {{-- Flash / validation --}}
        @if (session('status'))
            <div class="flash success">{{ session('status') }}</div>
        @endif
        @if ($errors->any())
            <div class="flash danger">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Search --}}
        <div class="search-section">
            <div class="search-input-wrapper">
                <span class="search-icon">🔍</span>
                <input type="text" class="search-input" placeholder="Search employees by name, email, or role..." id="searchInput">
            </div>
        </div>

        {{-- Employees Grid --}}
        <div class="employees-grid" id="employeesGrid"><!-- rendered by JS --></div>
    </main>
</div>

{{-- Add Employee Modal (posts to employees.store) --}}
<div class="modal-backdrop" id="addModal" role="dialog" aria-modal="true" aria-labelledby="addModalTitle">
    <div class="modal" onclick="event.stopPropagation()">
        <div class="modal-header">
            <div class="modal-title" id="addModalTitle">Add Employee</div>
            <button class="modal-close" aria-label="Close" onclick="closeAddModal()">✖</button>
        </div>

        <form id="addEmployeeForm" action="{{ route('employees.store') }}" method="POST">
            @csrf
            <div class="modal-body">
                <div class="form-grid">
                    <div class="form-control">
                        <label for="name">Full Name</label>
                        <input id="name" name="name" class="input" type="text" placeholder="e.g., Lina Khoury" required>
                    </div>

                    <div class="form-control">
                        <label for="email">Email</label>
                        <input id="email" name="email" class="input" type="email" placeholder="name@volunteerhub.com" required>
                    </div>

                    <div class="form-control">
                        <label for="password">Password</label>
                        <input id="password" name="password" class="input" type="password" placeholder="Minimum 8 characters" minlength="8" required>
                    </div>

                    <div class="form-control">
                        <label for="position">Position</label>
                        <select id="position" name="position" class="select">
                            <option value="" selected>Select a position</option>
                            <option>Event Coordinator</option>
                            <option>Volunteer Manager</option>
                        </select>
                    </div>

                    <div class="form-control">
                        <label for="department">Department</label>
                        <select id="department" name="department" class="select">
                            <option value="" selected>Select a department</option>
                            <option>Event Management Department</option>
                            <option>Volunteer & HR Department</option>
                        </select>
                    </div>
                    <div class="form-control">
                        <label for="Phone Number">Phone Number</label>
                        <input id="Phone Number" name="number" class="input" type="text" placeholder="Phone Number" required>
                    </div>

                    <div class="form-control">
                        <label for="hire_date">Hire Date</label>
                        <input id="hire_date" name="hire_date" class="input" type="date" required>
                        <div class="helper">Default: today if left empty.</div>
                    </div>

                    <div class="form-control">
    <label for="status">Status</label>
    <select id="status" name="status" class="select" required>
        <option value="active" selected>Active</option>
        <option value="suspended">Suspended</option>
    </select>
</div>

                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeAddModal()">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Employee</button>
            </div>
        </form>
    </div>
</div>

{{-- Hydrate initial employees if the controller passes them --}}
<script>
    window.initialEmployees = @json($employees ?? []);
</script>

{{-- Scripts --}}

</body>
</html>
