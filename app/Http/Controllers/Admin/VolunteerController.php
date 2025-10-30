<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Worker;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;                  // <-- add this
use Symfony\Component\HttpFoundation\Response;     // <-- and this

class VolunteerController extends Controller
{
    public function index()
    {
        $initial = $this->baseQuery()->get();
        $volunteers = $this->normalize($initial);

        return view('Admin.volunteers', [
            'volunteers' => $volunteers,
        ]);
    }

    public function list()
    {
        $rows = $this->baseQuery()->get();
        return response()->json($this->normalize($rows));
    }

    public function search(Request $request)
    {
        $q = $this->baseQuery();

        if ($loc = $request->string('location')->trim()) {
            $q->where('location', $loc);
        }
        if ($approval = $request->string('approval')->trim()) {
            if ($approval === 'approved') $q->where('approval_status', 'APPROVED');
            if ($approval === 'pending')  $q->where('approval_status', 'PENDING');
        }
        if ($status = $request->string('status')->trim()) {
            $q->where('approval_status', strtoupper($status));
        }
        if ($role = $request->string('role')->trim()) {
            $q->whereHas('reservations.workRole.roleType', fn($qq) => $qq->where('name', $role));
        }

        $rows = $q->get();
        return response()->json($this->normalize($rows));
    }

    private function baseQuery()
    {
        return Worker::query()
            ->with([
                'user:id,name,email',
                'reservations.workRole.roleType:id,name',
            ])
            ->withCount('reservations'); // events count
    }

private function normalize($collection)
{
    return $collection->map(function ($w) {
        $roleName   = $w->reservations->first()?->workRole?->roleType?->name ?? '';
        $userStatus = strtoupper((string) ($w->user->status ?? ''));
        $approval   = strtoupper((string) $w->approval_status);   // <-- keep exact DB value

        // UI status primarily from users.status (fallback to approval)
        $statusForUi = match ($userStatus) {
            'ACTIVE'    => 'active',
            'SUSPENDED' => 'suspended',
            'BANNED'    => 'banned',
            'PENDING'   => 'pending',
            default     => match ($approval) {
                'APPROVED'  => 'active',
                'SUSPENDED' => 'suspended',
                'REJECTED'  => 'banned',
                default     => 'pending',
            },
        };

        return [
            'id'        => $w->worker_id,
            'name'      => $w->user->name ?? '',
            'email'     => $w->user->email ?? '',
            'role'      => $roleName,
            'location'  => $w->location ?? '',
            'events'    => (int) ($w->reservations_count ?? 0),
            'hours'     => (float) ($w->total_hours ?? 0),
            'status'    => $statusForUi,                  // active/suspended/banned/pending
            'approval'  => strtolower($approval ?: 'pending'), // approved/suspended/rejected/pending
        ];
    });
}




public function approve($id)
{
    $worker = Worker::with('user')->find($id);
    if (!$worker) {
        return response()->json(['ok' => false, 'message' => 'Worker not found'], 404);
    }

    DB::transaction(function () use ($worker) {
        $worker->approval_status = 'APPROVED';
        $worker->save();

        $this->syncUserStatusFromApproval($worker);
    });

    return response()->json(['ok' => true, 'message' => 'Volunteer approved.']);
}

public function suspend($id)
{
    $worker = Worker::with('user')->find($id);
    if (!$worker) {
        return response()->json(['ok' => false, 'message' => 'Worker not found'], 404);
    }

    DB::transaction(function () use ($worker) {
        $worker->approval_status = 'SUSPENDED';
        $worker->save();

        $this->syncUserStatusFromApproval($worker);
    });

    return response()->json(['ok' => true, 'message' => 'Volunteer suspended.']);
}

// app/Http/Controllers/Admin/VolunteerController.php

public function ban($id)
{
    $worker = Worker::with('user')->find($id);
    if (!$worker) {
        return response()->json(['ok' => false, 'message' => 'Worker not found'], 404);
    }

    DB::transaction(function () use ($worker) {
        $worker->approval_status = 'REJECTED'; // logical pairing
        $worker->save();

        if ($worker->user) {
            $worker->user->status = 'BANNED'; // user can’t log in anymore
            $worker->user->save();
        }
    });

    return response()->json(['ok' => true, 'message' => 'Volunteer banned.']);
}



// app/Http/Controllers/Admin/VolunteerController.php

private function syncUserStatusFromApproval(\App\Models\Worker $worker): void
{
    if (!$worker->relationLoaded('user')) {
        $worker->load('user:id,status');
    }
    if (!$worker->user) {
        return; // no user attached; nothing to sync
    }

    // Map approval_status -> users.status
    $approval = strtoupper((string) $worker->approval_status);
    $worker->user->status = match ($approval) {
        'APPROVED'  => 'ACTIVE',
        'SUSPENDED' => 'SUSPENDED',
        'REJECTED'  => 'BANNED',
        default     => 'PENDING',   // e.g., newly registered
    };

    $worker->user->save();
}

}
