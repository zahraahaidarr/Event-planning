<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

class VolunteerController extends Controller
{
    public function index()
    {
        // Loads resources/views/Admin/volunteers.blade.php
        return view('Admin.volunteers');
    }
}
