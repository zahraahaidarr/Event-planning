<?php

namespace App\Http\Controllers\Social;

use App\Http\Controllers\Controller;
use App\Models\Like;
use App\Models\EmployeePost;
use App\Models\EmployeeReel;
use Illuminate\Http\Request;

class LikeController extends Controller
{
    public function toggle(Request $request)
    {
        $request->validate([
            'type' => 'required|in:post,reel',
            'id'   => 'required|integer',
        ]);

        $userId = $request->user()->id;

        $map = [
            'post' => EmployeePost::class,
            'reel' => EmployeeReel::class,
        ];

        $modelClass = $map[$request->type];
        $item = $modelClass::findOrFail($request->id);

        $like = Like::where('user_id', $userId)
            ->where('likeable_type', $modelClass)
            ->where('likeable_id', $item->id)
            ->first();

        if ($like) {
            $like->delete();
            $liked = false;
        } else {
            Like::create([
                'user_id' => $userId,
                'likeable_type' => $modelClass,
                'likeable_id' => $item->id,
            ]);
            $liked = true;
        }

        $count = Like::where('likeable_type', $modelClass)
            ->where('likeable_id', $item->id)
            ->count();

        return response()->json([
            'ok' => true,
            'liked' => $liked,
            'likes_count' => $count,
        ]);
    }
}
