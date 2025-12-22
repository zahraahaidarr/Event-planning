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

        $followedUserIds = $user->followingEmployees()
            ->pluck('users.id')   // or ->pluck('id')
            ->filter()
            ->unique()
            ->values();

        // Events (only published)
        $events = Event::query()
            ->whereIn('created_by', $followedUserIds)
            ->where('status', 'PUBLISHED')
            ->latest('created_at')
            ->get();

        // Posts (with like/comment counts + whether current user liked)
        $posts = EmployeePost::query()
            ->whereIn('employee_user_id', $followedUserIds)
            ->withCount(['likes', 'comments'])
            ->with(['likes' => function ($q) use ($user) {
                $q->where('user_id', $user->id);
            }])
            ->latest('created_at')
            ->get();

        // Reels (with like/comment counts + whether current user liked)
        $reels = EmployeeReel::query()
            ->whereIn('employee_user_id', $followedUserIds)
            ->withCount(['likes', 'comments'])
            ->with(['likes' => function ($q) use ($user) {
                $q->where('user_id', $user->id);
            }])
            ->latest('created_at')
            ->get();

        // Stories (only not expired)
        $stories = EmployeeStory::query()
            ->whereIn('employee_user_id', $followedUserIds)
            ->where(function ($q) {
                $q->whereNull('expires_at')
                  ->orWhere('expires_at', '>', now());
            })
            ->latest('created_at')
            ->get();

        return view('worker.feed', compact('events', 'posts', 'reels', 'stories'));
    }
}
