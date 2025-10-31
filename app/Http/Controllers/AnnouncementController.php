<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AnnouncementController extends Controller
{
    public function create()
    {
        return view('announcements.create');
    }

    public function store(Request $request)
    {
        // normalize role (your DB uses uppercase ADMIN/EMPLOYEE)
        $user = $request->user();
        $role = strtoupper($user->role ?? '');
        $isAdmin    = ($role === 'ADMIN');
        $isEmployee = ($role === 'EMPLOYEE');

        if (!($isAdmin || $isEmployee)) {
            return back()->withErrors('Not allowed.')->withInput();
        }

        $data = $request->validate([
            'title'      => ['required','string','max:255'],
            'body'       => ['required','string'],
            'audience'   => ['nullable','string','in:workers,employees,both'],
            'expires_at' => ['nullable','date'],
        ]);

        // employees always -> workers
        $data['audience'] = $isEmployee ? 'workers' : ($data['audience'] ?? 'workers');

        // ✅ robust poster id (handles users with user_id or id)
        $posterId = $user->user_id ?? $user->id;

        try {
            Announcement::create([
                'title'      => $data['title'],
                'body'       => $data['body'],
                'audience'   => $data['audience'],
                'expires_at' => $data['expires_at'] ?? null,
                'posted_by'  => $posterId,
                'created_at' => now(),
            ]);
        } catch (\Throwable $e) {
            // Log details so we know exactly which constraint failed
            Log::error('ANNOUNCEMENT INSERT FAILED', [
                'posterId' => $posterId,
                'role'     => $role,
                'error'    => $e->getMessage(),
            ]);
            return back()->withErrors('Save failed: '.$e->getMessage())->withInput();
        }

        return redirect()->route('announcements.create')->with('success','Announcement sent.');
    }
}
