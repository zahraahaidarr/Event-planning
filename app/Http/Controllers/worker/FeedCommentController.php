<?php

namespace App\Http\Controllers\Worker;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Models\EmployeePost;
use App\Models\EmployeeReel;
use Illuminate\Http\Request;

class FeedCommentController extends Controller
{
    private function resolveType(string $type): ?string
    {
        return match ($type) {
            'post' => EmployeePost::class,
            'reel' => EmployeeReel::class,
            default => null,
        };
    }

    // GET comments (JSON)
    public function index(Request $request)
    {
        $type = (string) $request->query('type', '');
        $id   = (int) $request->query('id', 0);

        $commentableClass = $this->resolveType($type);
        if (!$commentableClass || $id <= 0) {
            return response()->json(['message' => 'Invalid type or id'], 422);
        }

        $comments = Comment::query()
            ->where('commentable_type', $commentableClass)
            ->where('commentable_id', $id)
            ->with(['user:id,first_name,last_name,avatar_path'])
            ->orderBy('created_at', 'asc')
            ->get()
            ->map(function ($c) {
                $name = trim(($c->user->first_name ?? '') . ' ' . ($c->user->last_name ?? ''));
                if ($name === '') $name = $c->user->name ?? 'User';

                return [
                    'id' => $c->id,
                    'body' => $c->body,
                    'created_at' => optional($c->created_at)->format('Y-m-d H:i'),
                    'user' => [
                        'name' => $name,
                        'avatar' => $c->user->avatar_path ? asset('storage/' . ltrim($c->user->avatar_path, '/')) : null,
                        'initial' => strtoupper(substr($name, 0, 1)),
                    ],
                ];
            });

        return response()->json(['comments' => $comments]);
    }

    // POST add comment (JSON)
    public function store(Request $request)
    {
        $request->validate([
            'type' => ['required', 'in:post,reel'],
            'id'   => ['required', 'integer', 'min:1'],
            'body' => ['required', 'string', 'max:500'],
        ]);

        $commentableClass = $this->resolveType($request->type);
        $commentableId    = (int) $request->id;

        // ensure target exists
        $model = $commentableClass::findOrFail($commentableId);

        $comment = Comment::create([
            'user_id' => $request->user()->id,
            'commentable_type' => $commentableClass,
            'commentable_id' => $commentableId,
            'body' => $request->body,
        ]);

        $comment->load(['user:id,first_name,last_name,avatar_path']);

        $name = trim(($comment->user->first_name ?? '') . ' ' . ($comment->user->last_name ?? ''));
        if ($name === '') $name = $comment->user->name ?? 'User';

        $commentsCount = Comment::where('commentable_type', $commentableClass)
    ->where('commentable_id', $commentableId)
    ->count();

        return response()->json([
            'comment' => [
                'id' => $comment->id,
                'body' => $comment->body,
                'created_at' => optional($comment->created_at)->format('Y-m-d H:i'),
                'user' => [
                    'name' => $name,
                    'avatar' => $comment->user->avatar_path ? asset('storage/' . ltrim($comment->user->avatar_path, '/')) : null,
                    'initial' => strtoupper(substr($name, 0, 1)),
                ],
            ],
            'comments_count' => $commentsCount,
        ]);
    }
}
