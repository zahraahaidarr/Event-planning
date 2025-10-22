<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;

class dashboardController extends Controller
{
    public function index()
    {
        // Loads: resources/views/Employee/dashboard.blade.php
        return view('Employee.dashboard');
    }
}
