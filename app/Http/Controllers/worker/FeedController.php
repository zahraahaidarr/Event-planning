<?php

namespace App\Http\Controllers\Worker;

use App\Http\Controllers\Controller;
use App\Models\Event;
use Illuminate\Http\Request;

class FeedController extends Controller
{
    public function index(Request $request)
    {
        $worker = $request->user();

        // User IDs of employees the worker follows
        $followedUserIds = $worker
            ->followingEmployees()
            ->pluck('users.id')
            ->toArray();

        // Events created by followed employees (users.id → events.created_by)
        $events = Event::query()
            ->whereIn('created_by', $followedUserIds)
            ->where('status', 'PUBLISHED') // optional but recommended
            ->latest()
            ->paginate(10);

        return view('worker.feed', compact('events'));
    }
}
