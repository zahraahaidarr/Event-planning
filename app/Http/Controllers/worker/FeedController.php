<?php

namespace App\Http\Controllers\Worker;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\EmployeePost;
use App\Models\EmployeeReel;
use App\Models\EmployeeStory;
use Illuminate\Http\Request;
use App\Models\EmployeeStoryView;
use Illuminate\Http\JsonResponse;
use App\Models\Employee;

class FeedController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

       $followedUserIds = $user->followingEmployees()
    ->pluck('users.id')
    ->filter()
    ->unique()
    ->values();

// convert followed users -> employee_ids
$followedEmployeeIds = Employee::whereIn('user_id', $followedUserIds)
    ->pluck('employee_id');

// Events (created_by stores employee_id)
$events = Event::query()
    ->whereIn('created_by', $followedEmployeeIds)
    ->whereIn('status', ['PUBLISHED']) // ✅ ADD THIS LINE
    ->where('starts_at', '>=', now())
    ->orderBy('starts_at', 'asc')
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
    ->with(['employeeUser:id,first_name,last_name,avatar_path'])
    ->withExists(['views as seen_by_me' => function($q) use ($user) {
        $q->where('viewer_user_id', $user->id);
    }])
    ->latest('created_at')
    ->get();


        return view('worker.feed', compact('events', 'posts', 'reels', 'stories'));
    }

    public function markStorySeen(Request $request): JsonResponse
{
    $request->validate([
        'story_id' => ['required','integer','exists:employee_stories,id'],
    ]);

    $userId = $request->user()->id;
    $storyId = (int) $request->story_id;

    EmployeeStoryView::updateOrCreate(
        ['viewer_user_id' => $userId, 'employee_story_id' => $storyId],
        ['seen_at' => now()]
    );

    return response()->json(['ok' => true]);
}
}
