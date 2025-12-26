<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

use App\Models\EmployeePost;
use App\Models\EmployeeReel;
use App\Models\EmployeeStory;
use App\Services\AiEventGuard;

class ContentController extends Controller
{
    public function index(Request $request)
    {
        $userId = $request->user()->id;

        $posts   = EmployeePost::where('employee_user_id', $userId)->latest()->get();
        $reels   = EmployeeReel::where('employee_user_id', $userId)->latest()->get();
        $stories = EmployeeStory::where('employee_user_id', $userId)->latest()->get();

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
                    $isVideo = in_array($ext, ['mp4', 'mov', 'webm']);

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

        return view('employee.content', compact('posts', 'reels', 'stories'));
    }

    public function storePost(Request $request, AiEventGuard $ai)
    {
        $userId = $request->user()->id;

        $data = $request->validate([
            'title'   => ['required', 'string', 'max:120'],
            'content' => ['required', 'string', 'max:5000'],
            'media'   => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ]);

        // 1) TEMP upload (so we can delete if rejected)
        $tempPath = null;
        if ($request->hasFile('media')) {
            $tempPath = $request->file('media')->store('temp/employee_posts', 'public');
        }

        // 2) AI check
       $result = $ai->check("Post upload", $tempPath);


        // AI errors
        if (($result['related'] ?? null) === null || ($result['reason'] ?? '') !== '') {
            // If it's a normal "not related", we handle below.
            // Here we only catch technical issues:
            $techReasons = [
                'ai_unreachable',
                'image_too_large_for_ai',
                'ai_http_error',
                'ai_bad_json',
                'ai_missing_output',
                'bad_ai_format',
                'file_missing',
            ];

            if (in_array(($result['reason'] ?? ''), $techReasons, true)) {
                if ($tempPath) Storage::disk('public')->delete($tempPath);
                return $this->handleAiFailure('media', $result, true);
            }
        }

        // Not related
        if (!($result['related'] ?? false) || empty($result['category_id'])) {
            if ($tempPath) Storage::disk('public')->delete($tempPath);

            return back()->withErrors([
                'media' => 'Your photo is not related to the events category we have.',
            ])->withInput();
        }

        // 3) Move to final
        $finalPath = null;
        if ($tempPath) {
            $finalPath = str_replace('temp/employee_posts', 'employee_posts', $tempPath);
            Storage::disk('public')->move($tempPath, $finalPath);
        }

        // 4) Publish
        EmployeePost::create([
            'employee_user_id' => $userId,
            'category_id'      => $result['category_id'],
            'title'            => $data['title'],
            'content'          => $data['content'],
            'media_path'       => $finalPath,
        ]);

        return back()->with('ok', 'Post created successfully.');
    }

    public function storeReel(Request $request, AiEventGuard $ai)
    {
        $userId = $request->user()->id;

        $data = $request->validate([
            'caption' => ['nullable', 'string', 'max:1000'],
            'video'   => ['required', 'file', 'mimes:mp4,mov,webm', 'max:51200'],
        ]);

        // TEMP upload
        $tempVideo = $request->file('video')->store('temp/employee_reels', 'public');

        $captionText = trim($data['caption'] ?? '');
        $result = $ai->check("Reel caption:\n" . $captionText, null);

        // Technical AI errors
        $techReasons = ['ai_unreachable', 'ai_http_error', 'ai_bad_json', 'ai_missing_output', 'bad_ai_format'];
        if (in_array(($result['reason'] ?? ''), $techReasons, true)) {
            Storage::disk('public')->delete($tempVideo);
            return $this->handleAiFailure('video', $result, true);
        }

        // Not related
        if (!($result['related'] ?? false) || empty($result['category_id'])) {
            Storage::disk('public')->delete($tempVideo);

            return back()->withErrors([
                'video' => 'Your video is not related to the events category we have.',
            ])->withInput();
        }

        // Move to final
        $finalPath = str_replace('temp/employee_reels', 'employee_reels', $tempVideo);
        Storage::disk('public')->move($tempVideo, $finalPath);

        EmployeeReel::create([
            'employee_user_id' => $userId,
            'category_id'      => $result['category_id'],
            'caption'          => $data['caption'] ?? null,
            'video_path'       => $finalPath,
        ]);

        return back()->with('ok', 'Reel uploaded successfully.');
    }

    public function storeStory(Request $request, AiEventGuard $ai)
    {
        $userId = $request->user()->id;

        $data = $request->validate([
            'media'      => ['required', 'file', 'mimes:jpg,jpeg,png,webp,mp4,mov,webm', 'max:51200'],
            'expires_at' => ['nullable', 'date'],
        ]);

        // TEMP upload
        $tempPath = $request->file('media')->store('temp/employee_stories', 'public');

        $ext = strtolower($request->file('media')->getClientOriginalExtension());
        $isVideo = in_array($ext, ['mp4', 'mov', 'webm']);

        if ($isVideo) {
            Storage::disk('public')->delete($tempPath);
            return back()->withErrors([
                'media' => 'Video stories AI-check not enabled yet. Upload an image story for now.',
            ]);
        }

        $result = $ai->check("Story upload", $tempPath);

        // Technical AI errors
        $techReasons = [
            'ai_unreachable',
            'image_too_large_for_ai',
            'ai_http_error',
            'ai_bad_json',
            'ai_missing_output',
            'bad_ai_format',
            'file_missing',
        ];
        if (in_array(($result['reason'] ?? ''), $techReasons, true)) {
            Storage::disk('public')->delete($tempPath);
            return $this->handleAiFailure('media', $result, true);
        }

        // Not related
        if (!($result['related'] ?? false) || empty($result['category_id'])) {
            Storage::disk('public')->delete($tempPath);

            return back()->withErrors([
                'media' => 'Your photo is not related to the events category we have.',
            ]);
        }

        // Move to final
        $finalPath = str_replace('temp/employee_stories', 'employee_stories', $tempPath);
        Storage::disk('public')->move($tempPath, $finalPath);

        $expiresAt = isset($data['expires_at'])
            ? Carbon::parse($data['expires_at'])
            : now()->addHours(24);

        EmployeeStory::create([
            'employee_user_id' => $userId,
            'category_id'      => $result['category_id'],
            'media_path'       => $finalPath,
            'expires_at'       => $expiresAt,
        ]);

        return back()->with('ok', 'Story uploaded successfully.');
    }

    private function handleAiFailure(string $field, array $result, bool $withInput = false)
    {
        // Log details for debugging (not shown to user)
        Log::error('AI Guard failure', [
            'reason'      => $result['reason'] ?? null,
            'http_status' => $result['http_status'] ?? null,
            'http_body'   => $result['http_body'] ?? null,
        ]);

        // Friendly messages
        $reason = $result['reason'] ?? 'unknown';

        $msg = match ($reason) {
            'ai_unreachable' => 'AI service is currently unavailable. Please try again later.',
            'image_too_large_for_ai' => 'Image is too large for AI check. Please upload a smaller image (max 2MB).',
            'ai_http_error' => 'AI returned an invalid response. Please try again.',
            'ai_bad_json', 'bad_ai_format', 'ai_missing_output' => 'AI returned an invalid response. Please try again.',
            'file_missing' => 'Uploaded file could not be read. Please upload again.',
            default => 'AI returned an invalid response. Please try again.',
        };

        $resp = back()->withErrors([$field => $msg]);

        return $withInput ? $resp->withInput() : $resp;
    }

    public function destroyPost(Request $request, EmployeePost $post)
    {
        $this->authorizeOwner($request->user()->id, $post->employee_user_id);

        if ($post->media_path) Storage::disk('public')->delete($post->media_path);
        $post->delete();

        return response()->json(['ok' => true]);
    }

    public function destroyReel(Request $request, EmployeeReel $reel)
    {
        $this->authorizeOwner($request->user()->id, $reel->employee_user_id);

        if ($reel->video_path) Storage::disk('public')->delete($reel->video_path);
        $reel->delete();

        return response()->json(['ok' => true]);
    }

    public function destroyStory(Request $request, EmployeeStory $story)
    {
        $this->authorizeOwner($request->user()->id, $story->employee_user_id);

        if ($story->media_path) Storage::disk('public')->delete($story->media_path);
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
