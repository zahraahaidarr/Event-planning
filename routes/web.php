<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Admin\employeesController;
use App\Http\Controllers\Admin\dashboardController;
use App\Http\Controllers\Employee\dashboardController as EmployeeDashboardController;
use App\Http\Controllers\Admin\VolunteerController;


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
        //volunteers routes
        Route::get('/admin/volunteers', [VolunteerController::class, 'index'])->name('volunteers.index');
    });


    Route::middleware(['auth', 'role:EMPLOYEE'])->group(function () {
        Route::get('/employee/dashboard', [EmployeeDashboardController::class, 'index'])->name('employee.dashboard');
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



Route::middleware(['auth', 'role:WORKER'])->get('/worker/dashboard', fn() => view('Worker.dashboard'))
    ->name('worker.dashboard');
