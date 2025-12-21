<?php

namespace App\Http\Controllers\Worker;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\EmployeePost;
use App\Models\EmployeeReel;
use App\Models\EmployeeStory;
use Illuminate\Http\Request;

class FeedController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        // ✅ followingEmployees() returns followed USERS (table: users)
        $followedUserIds = $user->followingEmployees()
            ->pluck('users.id') // you can also use ->pluck('id')
            ->filter()
            ->unique()
            ->values();

        // ✅ Events (only published)
        $events = Event::query()
            ->whereIn('created_by', $followedUserIds)
            ->where('status', 'PUBLISHED')
            ->latest('created_at')
            ->get();

        // ✅ Posts
        $posts = EmployeePost::query()
            ->whereIn('employee_user_id', $followedUserIds)
            ->latest('created_at')
            ->get();

        // ✅ Reels
        $reels = EmployeeReel::query()
            ->whereIn('employee_user_id', $followedUserIds)
            ->latest('created_at')
            ->get();

        // ✅ Stories
        $stories = EmployeeStory::query()
            ->whereIn('employee_user_id', $followedUserIds)
            ->where(function ($q) {
                $q->whereNull('expires_at')
                  ->orWhere('expires_at', '>', now());
            })
            ->latest('created_at')
            ->get();

        return view('worker.feed', compact('events','posts','reels','stories'));
    }
}
