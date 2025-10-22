<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

class dashboardController extends Controller
{
    public function index()
    {
        // Loads: resources/views/Admin/dashboard.blade.php
        return view('Admin.dashboard');
    }
}
