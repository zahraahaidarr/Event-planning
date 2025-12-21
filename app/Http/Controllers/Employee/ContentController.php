<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\ModelNotFoundException;

// If you already have models, import them.
// Otherwise you can use DB::table, but models are cleaner.
use App\Models\EmployeePost;
use App\Models\EmployeeReel;
use App\Models\EmployeeStory;

class ContentController extends Controller
{
    public function index(Request $request)
{
    $userId = $request->user()->id;

    // ✅ Get ALL posts/reels/stories for this employee (latest first)
    $posts = EmployeePost::where('employee_user_id', $userId)->latest()->get();
    $reels = EmployeeReel::where('employee_user_id', $userId)->latest()->get();
    $stories = EmployeeStory::where('employee_user_id', $userId)->latest()->get();

    // ✅ If JS asked for JSON, return JSON
    if ($request->expectsJson() || $request->wantsJson()) {
        return response()->json([
            'posts' => $posts->map(function ($p) {
                return [
                    'id' => $p->id,
                    'title' => $p->title,
                    'content' => $p->content,
                    'media_url' => $p->media_path ? asset('storage/' . $p->media_path) : null,
                    'created_at_formatted' => optional($p->created_at)->format('Y-m-d H:i'),
                ];
            })->values(),

            'reels' => $reels->map(function ($r) {
                return [
                    'id' => $r->id,
                    'caption' => $r->caption,
                    'video_url' => $r->video_path ? asset('storage/' . $r->video_path) : null,
                    'created_at_formatted' => optional($r->created_at)->format('Y-m-d H:i'),
                ];
            })->values(),

            'stories' => $stories->map(function ($s) {
                $path = $s->media_path ?? '';
                $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
                $isVideo = in_array($ext, ['mp4','mov','webm']);

                return [
                    'id' => $s->id,
                    'media_url' => $s->media_path ? asset('storage/' . $s->media_path) : null,
                    'media_type' => $isVideo ? 'video' : 'image',
                    'created_at_formatted' => optional($s->created_at)->format('Y-m-d H:i'),
                    'expires_at_formatted' => optional($s->expires_at)->format('Y-m-d H:i'),
                ];
            })->values(),
        ]);
    }

    // ✅ Normal page load (HTML)
    return view('employee.content', compact('posts', 'reels', 'stories'));
}


    public function storePost(Request $request)
    {
        $userId = $request->user()->id;

        $data = $request->validate([
            'title'   => ['required', 'string', 'max:120'],
            'content' => ['required', 'string', 'max:5000'],
            'media'   => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp', 'max:5120'], // 5MB
        ]);

        $mediaPath = null;
        if ($request->hasFile('media')) {
            $mediaPath = $request->file('media')->store('employee_posts', 'public');
        }

        EmployeePost::create([
            'employee_user_id' => $userId,
            'title'            => $data['title'],
            'content'          => $data['content'],
            'media_path'       => $mediaPath,
        ]);

        return back()->with('ok', 'Post created successfully.');
    }

    public function storeReel(Request $request)
    {
        $userId = $request->user()->id;

        $data = $request->validate([
            'caption' => ['nullable', 'string', 'max:1000'],
            'video'   => ['required', 'file', 'mimes:mp4,mov,webm', 'max:51200'], // 50MB
        ]);

        $videoPath = $request->file('video')->store('employee_reels', 'public');

        EmployeeReel::create([
            'employee_user_id' => $userId,
            'caption'          => $data['caption'] ?? null,
            'video_path'       => $videoPath,
        ]);

        return back()->with('ok', 'Reel uploaded successfully.');
    }

    public function storeStory(Request $request)
    {
        $userId = $request->user()->id;

        $data = $request->validate([
            'media'      => ['required', 'file', 'mimes:jpg,jpeg,png,webp,mp4,mov,webm', 'max:51200'],
            'expires_at' => ['nullable', 'date'], // optional
        ]);

        $mediaPath = $request->file('media')->store('employee_stories', 'public');

        // Default: 24 hours
        $expiresAt = isset($data['expires_at'])
            ? Carbon::parse($data['expires_at'])
            : now()->addHours(24);

        EmployeeStory::create([
            'employee_user_id' => $userId,
            'media_path'       => $mediaPath,
            'expires_at'       => $expiresAt,
        ]);

        return back()->with('ok', 'Story uploaded successfully.');
    }

    public function destroyPost(Request $request, EmployeePost $post)
    {
        $this->authorizeOwner($request->user()->id, $post->employee_user_id);

        if ($post->media_path) {
            Storage::disk('public')->delete($post->media_path);
        }
        $post->delete();

        return response()->json(['ok' => true]);
    }

    public function destroyReel(Request $request, EmployeeReel $reel)
    {
        $this->authorizeOwner($request->user()->id, $reel->employee_user_id);

        if ($reel->video_path) {
            Storage::disk('public')->delete($reel->video_path);
        }
        $reel->delete();

        return response()->json(['ok' => true]);
    }

    public function destroyStory(Request $request, EmployeeStory $story)
    {
        $this->authorizeOwner($request->user()->id, $story->employee_user_id);

        if ($story->media_path) {
            Storage::disk('public')->delete($story->media_path);
        }
        $story->delete();

        return response()->json(['ok' => true]);
    }

    private function authorizeOwner(int $currentUserId, int $rowUserId): void
    {
        if ($currentUserId !== $rowUserId) {
            abort(403, 'Unauthorized');
        }
    }
}
