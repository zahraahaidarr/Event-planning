<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale()==='ar' ? 'rtl' : 'ltr' }}" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Discover Events - Worker Portal</title>
    <link rel="stylesheet" href="{{ asset('css/worker/event-discovery.css') }}">
</head>
<body>
    <div class="container">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="logo">
                <div class="logo-icon">V</div>
                <span class="logo-text">VolunteerHub</span>
            </div>

            <nav class="nav-section">
                <div class="nav-label">Worker</div>
                <a href="{{ route('worker.dashboard') }}" class="nav-item">
                    <span class="nav-icon">🏠</span>
                    <span>Dashboard</span>
                </a>
                <a href="{{ route('worker.events.discover') }}" class="nav-item active">
                    <span class="nav-icon">🗓️</span>
                    <span>Discover Events</span>
                </a>
                <a href="{{ route('worker.reservations') }}" class="nav-item">
                    <span class="nav-icon">✅</span>
                    <span>My Reservations</span>
                </a>
                <a href="{{ route('worker.submissions') }}" class="nav-item">
                    <span class="nav-icon">📝</span>
                    <span>Post-Event Submissions</span>
                </a>
            </nav>

            <nav class="nav-section">
                <div class="nav-label">Account</div>
                <a href="{{ route('worker.profile') }}" class="nav-item">
                    <span class="nav-icon">👤</span>
                    <span>Profile</span>
                </a>
                <a href="{{ route('worker.messages') }}" class="nav-item">
                    <span class="nav-icon">💬</span>
                    <span>Chat</span>
                </a>
                <a href="{{ route('worker.announcements') }}" class="nav-item">
                    <span class="nav-icon">📢</span>
                    <span>Announcements</span>
                </a>
                <a href="{{ route('worker.settings') }}" class="nav-item">
                    <span class="nav-icon">⚙️</span>
                    <span>Settings</span>
                </a>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <div class="header">
                <div class="header-left">
                    <h1>Discover Events</h1>
                    <p>Find volunteer opportunities that match your interests and skills</p>
                </div>
                <div class="header-actions">
                    <button class="icon-btn" onclick="toggleTheme()" title="Toggle theme">
                        <span id="theme-icon">🌙</span>
                    </button>
                    <button class="icon-btn" onclick="toggleLanguage()" title="Toggle language">
                        <span id="lang-icon">AR</span>
                    </button>
                </div>
            </div>

            <!-- Search and Filters -->
            <div class="search-filter-section">
                <div class="search-bar">
                    <div class="search-input-wrapper">
                        <span class="search-icon">🔍</span>
                        <input type="text" class="search-input" placeholder="Search events by name, location, or description..." id="searchInput">
                    </div>
                    <button class="btn btn-primary" onclick="applyFilters()">Search</button>
                </div>

                <div class="filters-grid">
                    <div class="filter-group">
                        <label class="filter-label">Category</label>
                        <select class="filter-select" id="categoryFilter">
                            <option value="">All Categories</option>
                            <option value="education">Education</option>
                            <option value="environment">Environment</option>
                            <option value="health">Health</option>
                            <option value="community">Community</option>
                            <option value="elderly">Elderly Care</option>
                            <option value="children">Children</option>
                        </select>
                    </div>

                    <div class="filter-group">
                        <label class="filter-label">Date Range</label>
                        <select class="filter-select" id="dateFilter">
                            <option value="">Any Time</option>
                            <option value="today">Today</option>
                            <option value="week">This Week</option>
                            <option value="month">This Month</option>
                            <option value="custom">Custom Range</option>
                        </select>
                    </div>

                    <div class="filter-group">
                        <label class="filter-label">Location</label>
                        <select class="filter-select" id="locationFilter">
                            <option value="">All Locations</option>
                            <option value="riyadh">Riyadh</option>
                            <option value="jeddah">Jeddah</option>
                            <option value="dammam">Dammam</option>
                            <option value="mecca">Mecca</option>
                            <option value="medina">Medina</option>
                        </select>
                    </div>

                    <div class="filter-group">
                        <label class="filter-label">Availability</label>
                        <select class="filter-select" id="availabilityFilter">
                            <option value="">All Events</option>
                            <option value="open">Open for Applications</option>
                            <option value="limited">Limited Spots</option>
                        </select>
                    </div>
                </div>

                <div class="active-filters" id="activeFilters" style="display: none;"></div>
            </div>

            <!-- Results Header -->
            <div class="results-header">
                <div class="results-count">
                    Showing <strong id="resultsCount">0</strong> events
                </div>
                <div class="view-toggle">
                    <button class="view-btn active" id="gridViewBtn">Grid</button>
                    <button class="view-btn" id="listViewBtn">List</button>
                </div>
            </div>

            <!-- Events Grid -->
            <div class="events-grid" id="eventsGrid"></div>

            <!-- Pagination -->
            <div class="pagination">
                <button class="page-btn">Previous</button>
                <button class="page-btn active">1</button>
                <button class="page-btn">2</button>
                <button class="page-btn">3</button>
                <button class="page-btn">4</button>
                <button class="page-btn">Next</button>
            </div>
        </main>
    </div>

    <!-- Event Details Modal -->
    <div class="modal" id="eventModal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title" id="modalTitle">Event Details</h2>
                <button class="modal-close" onclick="closeModal()">&times;</button>
            </div>
            <div class="modal-body" id="modalBody"></div>
            <div class="modal-footer">
                <button class="btn btn-secondary" onclick="closeModal()">Close</button>
                <button class="btn btn-primary" onclick="applyToEvent()">Apply Now</button>
            </div>
        </div>
    </div>

    <script src="{{ asset('js/worker/event-discovery.js') }}" defer></script>
</body>
</html>
