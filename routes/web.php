<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Admin\employeesController;
use App\Http\Controllers\Admin\dashboardController;
use App\Http\Controllers\Employee\dashboardController as EmployeeDashboardController;
use App\Http\Controllers\Admin\VolunteerController;
use App\Http\Controllers\AnnouncementController;
use App\Http\Controllers\Admin\TaxonomiesVenuesController;
use App\Http\Controllers\AI\StaffingController;
use App\Http\Controllers\Admin\EventsController;
use App\Http\Controllers\AnnouncementFeedController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Worker\EventDiscoveryController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\SystemSettingController;
use App\Http\Controllers\Worker\PostEventSubmissionController;

Route::post('/ai/staffing', [StaffingController::class, 'predict'])->name('api.ai.staffing');
   
Route::get('/register', [RegisteredUserController::class, 'create'])->name('register');
    Route::post('/register', [RegisteredUserController::class, 'store'])->name('register.store');
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store'])->name('login.store');
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->middleware('auth')->name('logout');


    Route::middleware(['auth', 'role:ADMIN'])->group(function () {
        //admin dashboard route
        Route::get('/admin/dashboard', [dashboardController::class, 'index'])->name('admin.dashboard');
        //employees routes
        Route::get('/admin/employees', [EmployeesController::class, 'index'])->name('employees.index');
        Route::post('admin/employees', [EmployeesController::class, 'store'])->name('employees.store');
        Route::get('/admin/employees/search', [EmployeesController::class, 'search'])->name('employees.search');
        Route::get('/admin/employees/json', [EmployeesController::class, 'json'])->name('employees.json');
        Route::post('/admin/employees/{id}/status', [EmployeesController::class, 'setStatus'])->name('set-status');
        Route::delete('/admin/employees/{id}',      [EmployeesController::class, 'destroy'])->name('destroy');
        //volunteers routes
        Route::get('/admin/volunteers', [VolunteerController::class, 'index'])->name('volunteers.index');
        Route::get('/admin/volunteers/list', [VolunteerController::class, 'list'])->name('volunteers.list');
        Route::get('/admin/volunteers/search', [VolunteerController::class, 'search'])->name('volunteers.search');
        Route::post('/admin/volunteers/{id}/status', [VolunteerController::class, 'setStatus'])->name('set-status');
        // Taxonomies & Venues routes
        Route::get('/admin/taxonomies-venues', [TaxonomiesVenuesController::class, 'index'])->name('taxonomies-venues.index');
        Route::get('/admin/taxonomies-venues/worker-types',    [TaxonomiesVenuesController::class, 'workerTypesIndex'])->name('taxonomies-venues.worker-types.index');
        Route::post('/admin/taxonomies-venues/worker-types',   [TaxonomiesVenuesController::class, 'workerTypesStore'])->name('taxonomies-venues.worker-types.store');
        Route::delete('/admin/taxonomies-venues/worker-types/{roleType}', [TaxonomiesVenuesController::class, 'workerTypesDestroy'])->name('taxonomies-venues.worker-types.destroy');
        Route::get('/admin/taxonomies-venues/event-categories',[TaxonomiesVenuesController::class,'eventCategoriesIndex']) ->name('taxonomies-venues.event-categories.index');
        Route::post('/admin/taxonomies-venues/event-categories',[TaxonomiesVenuesController::class,'eventCategoriesStore'])->name('taxonomies-venues.event-categories.store');
        Route::delete('/admin/taxonomies-venues/event-categories/{eventCategory}',[TaxonomiesVenuesController::class,'eventCategoriesDestroy'])->name('taxonomies-venues.event-categories.destroy');
        Route::get('/admin/taxonomies-venues/venues',[TaxonomiesVenuesController::class,'venuesIndex'])->name('taxonomies-venues.venues.index');
        Route::post('/admin/taxonomies-venues/venues',[TaxonomiesVenuesController::class,'venuesStore'])->name('taxonomies-venues.venues.store');
        Route::delete('/admin/taxonomies-venues/venues/{venue}',[TaxonomiesVenuesController::class,'venuesDestroy'])->name('taxonomies-venues.venues.destroy');
        
        // events routes
        Route::get('/admin/events', [EventsController::class, 'index'])->name('events.index');
        Route::post('/admin/events', [EventsController::class, 'store'])->name('admin.events.store');
        Route::get   ('/admin/events/{event}',        [EventsController::class, 'show'])->name('admin.events.show'); // JSON for edit
        Route::put   ('/admin/events/{event}',        [EventsController::class, 'update'])->name('admin.events.update');
        Route::patch ('/admin/events/{event}/status', [EventsController::class, 'updateStatus'])->name('admin.events.update-status');

    });


    Route::middleware(['auth', 'role:EMPLOYEE'])->group(function () {
        Route::get('/employee/dashboard', [EmployeeDashboardController::class, 'index'])->name('employee.dashboard');
    });



    Route::middleware(['auth'])->group(function () {
        Route::get('/announcements/create', [AnnouncementController::class, 'create'])->name('announcements.create');
        Route::post('/announcements', [AnnouncementController::class, 'store'])->name('announcements.store');
        Route::get('/employee/announcements', [AnnouncementFeedController::class, 'index'])->name('employee.announcements.index');
        Route::get('/worker/announcements', [AnnouncementFeedController::class, 'index'])->name('worker.announcements.index');
        
        Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
        Route::get('/api/notifications/unread-count', [NotificationController::class, 'unreadCount'])->name('notifications.unreadCount');
        Route::post('/api/notifications/read-all', [NotificationController::class, 'markAllRead'])->name('notifications.markAllRead');
    
        Route::get('/profile',          [ProfileController::class,'show'])->name('profile');
        Route::get('/profile/data',     [ProfileController::class,'data'])->name('profile.data');
        Route::put('/profile/account',  [ProfileController::class,'updateAccount'])->name('profile.account');
        Route::put('/profile/personal', [ProfileController::class,'updatePersonal'])->name('profile.personal');
        Route::put('/profile/password', [ProfileController::class,'updatePassword'])->name('profile.password');
        Route::post('/profile/avatar',  [ProfileController::class,'uploadAvatar'])->name('profile.avatar');

         Route::get('/settings',  [SystemSettingController::class, 'edit'])
        ->name('settings');

    Route::post('/settings', [SystemSettingController::class, 'update'])
        ->name('settings.update');

    Route::post('/settings/logout-all', [SystemSettingController::class, 'logoutAll'])
        ->name('settings.logoutAll');

    Route::delete('/settings/delete-account', [SystemSettingController::class, 'destroyAccount'])
        ->name('settings.deleteAccount');
    });




    Route::get('/dashboard', function () {
        $user = Auth::user();

        return match ($user?->role) {
            'ADMIN'    => redirect()->route('admin.dashboard'),
            'EMPLOYEE' => redirect()->route('employee.dashboard'),
            'WORKER'   => redirect()->route('worker.dashboard'),
            default    => redirect()->route('login'),
        };})->middleware('auth')->name('dashboard');

    Route::get('/', function () {
        if (Auth::check()) {
            return match (Auth::user()->role) {
                'ADMIN'    => redirect()->route('admin.dashboard'),
                'EMPLOYEE' => redirect()->route('employee.dashboard'),
                'WORKER'   => redirect()->route('worker.dashboard'),
                default    => redirect()->route('login'),
            };
        }
        return redirect()->route('login');})->name('home');



Route::middleware(['auth', 'role:WORKER'])->prefix('worker')->name('worker.')->group(function () {

        Route::view('/dashboard', 'worker.dashboard')->name('dashboard');


        Route::get('/events/discover', [EventDiscoveryController::class, 'index'])->name('events.discover');
        Route::get('/events/discover/list', [EventDiscoveryController::class, 'list'])->name('events.discover.list');
        Route::post('/events/{event}/apply', [EventDiscoveryController::class, 'apply'])->name('events.apply');
        
        Route::view('/my-reservations', 'worker.my-reservations')->name('reservations');

        Route::get('/submissions', [PostEventSubmissionController::class, 'index'])->name('submissions'); // used by your Blade
        Route::post('/submissions', [PostEventSubmissionController::class, 'store'])->name('submissions.store');

        Route::view('/messages', 'worker.messages')->name('messages');

    });

