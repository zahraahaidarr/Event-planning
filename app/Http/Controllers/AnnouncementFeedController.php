<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AnnouncementFeedController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        // Determine role
        $role = strtolower((string) ($user->role ?? ''));
        if (!in_array($role, ['worker', 'employee'])) {
            // Fallback: if worker relation exists, treat as worker
            if (method_exists($user, 'worker') && $user->worker()->exists()) {
                $role = 'worker';
            } else {
                $role = 'employee'; // safe default
            }
        }

        $announcements = Announcement::visibleForRole($role)
            ->with('author')
            ->orderByDesc('created_at')
            ->get()
            ->map(function (Announcement $a) {
                $author = $a->author;
                // use accessor getNameAttribute() you added on User
                $authorName = $author ? ($author->name ?? ($author->first_name . ' ' . $author->last_name)) : 'System';

                return [
                    'id'      => $a->announcement_id,
                    'title'   => $a->title,
                    'body'    => $a->body,
                    'audience'=> $a->audience,
                    'date'    => optional($a->created_at)->toIso8601String(),
                    'author'  => $authorName,
                    'author_role' => $author->role ?? '',
                    // simple type mapping for chips
                    'type'    => $this->mapType($a),
                    'featured'=> false,
                ];
            });

        return view('announcements.index', [
            'announcements' => $announcements,
            'role'          => $role,
        ]);
    }

    protected function mapType(Announcement $a): string
    {
        // You can tune this logic (for now: important for both, info for targeted)
        if ($a->audience === 'both') {
            return 'important';
        }
        if ($a->audience === 'workers' || $a->audience === 'employees') {
            return 'info';
        }
        return 'info';
    }
}
