<?php

namespace App\Http\Controllers\Social;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Models\EmployeePost;
use App\Models\EmployeeReel;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'type' => 'required|in:post,reel',
            'id'   => 'required|integer',
        ]);

        $map = [
            'post' => EmployeePost::class,
            'reel' => EmployeeReel::class,
        ];
        $modelClass = $map[$request->type];

        $comments = Comment::with('user:id,first_name,last_name,name,avatar_path')
            ->where('commentable_type', $modelClass)
            ->where('commentable_id', $request->id)
            ->orderBy('created_at', 'asc')
            ->get()
            ->map(function($c){
                return [
                    'id' => $c->id,
                    'body' => $c->body,
                    'created_at' => $c->created_at?->format('Y-m-d H:i'),
                    'user' => [
                        'name' => trim(($c->user->first_name ?? '').' '.($c->user->last_name ?? '')) ?: ($c->user->name ?? 'User'),
                        'avatar' => $c->user->avatar_path ? asset('storage/'.ltrim($c->user->avatar_path,'/')) : null,
                    ],
                ];
            });

        return response()->json(['ok' => true, 'comments' => $comments]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'type' => 'required|in:post,reel',
            'id'   => 'required|integer',
            'body' => 'required|string|max:500',
        ]);

        $map = [
            'post' => EmployeePost::class,
            'reel' => EmployeeReel::class,
        ];
        $modelClass = $map[$request->type];

        $comment = Comment::create([
            'user_id' => $request->user()->id,
            'commentable_type' => $modelClass,
            'commentable_id' => $request->id,
            'body' => $request->body,
        ]);

        $count = Comment::where('commentable_type', $modelClass)
            ->where('commentable_id', $request->id)
            ->count();

        return response()->json([
            'ok' => true,
            'comment' => [
                'id' => $comment->id,
                'body' => $comment->body,
                'created_at' => $comment->created_at?->format('Y-m-d H:i'),
                'user' => [
                    'name' => trim(($request->user()->first_name ?? '').' '.($request->user()->last_name ?? '')) ?: ($request->user()->name ?? 'User'),
                    'avatar' => $request->user()->avatar_path ? asset('storage/'.ltrim($request->user()->avatar_path,'/')) : null,
                ],
            ],
            'comments_count' => $count,
        ]);
    }
}
