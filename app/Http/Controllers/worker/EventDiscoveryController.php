<?php

namespace App\Http\Controllers\Worker;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\EventCategory;
use App\Models\Worker;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\RoleType;
use App\Models\WorkRole;
use App\Models\WorkerReservation;
use Illuminate\Http\JsonResponse;




class EventDiscoveryController extends Controller
{
    /** Show the Discover Events page */
    public function index(Request $request)
    {
        // current authenticated user
        $user = Auth::user();

        // get related worker record:
        // - if you have $user->worker relation, this uses it
        // - otherwise it falls back to workers.user_id
        $worker = $user?->worker
            ?? Worker::where('user_id', $user?->id)->first();

        // worker's role_type_id (Organizer, Cleaner, etc. from role_types table)
        $roleTypeId = $worker?->role_type_id;

        // initial events batch, including roles
        $events = Event::publishedFuture()
            ->with(['category', 'workRoles'])
            ->orderBy('starts_at')
            ->take(60)
            ->get()
            ->map(fn (Event $e) => $e->toWorkerCard($roleTypeId));

        $categories = EventCategory::orderBy('name')
            ->get(['category_id', 'name']);

        $locations = Event::whereNotNull('location')
            ->select('location')
            ->distinct()
            ->orderBy('location')
            ->pluck('location');

        $workerRoleName = null;
        if ($roleTypeId) {
             $workerRoleName = RoleType::where('role_type_id', $roleTypeId)->value('name');
        }


        return view('worker.event-discovery', [
            'eventsBootstrap' => $events,
            'categories'      => $categories,
            'locations'       => $locations,
            'workerRoleName' => $workerRoleName,

        ]);
    }

    /** JSON list for filters / pagination */
    public function list(Request $request)
    {
        $user = Auth::user();

        $worker = $user?->worker
            ?? Worker::where('user_id', $user?->id)->first();

        $roleTypeId = $worker?->role_type_id;

        $q            = trim((string) $request->input('q', ''));
        $category     = $request->input('category');
        $location     = $request->input('location');
        $availability = $request->input('availability'); // open|limited|full
        $perPage      = (int) $request->input('per_page', 12);

        $builder = Event::publishedFuture()
            ->with(['category', 'workRoles']);

        if ($q !== '') {
            $builder->where(function ($query) use ($q) {
                $query->where('title', 'like', "%{$q}%")
                      ->orWhere('description', 'like', "%{$q}%")
                      ->orWhere('location', 'like', "%{$q}%");
            });
        }

        if ($category) {
            $builder->where(function ($query) use ($category) {
                $query->where('category_id', $category)
                      ->orWhereHas('category', function ($q2) use ($category) {
                          $q2->where('name', 'like', "%{$category}%");
                      });
            });
        }

        if ($location) {
            $builder->where('location', 'like', "%{$location}%");
        }

        $paginator = $builder
            ->orderBy('starts_at')
            ->paginate($perPage);

        // map events to role-aware cards
        $items = $paginator->getCollection()
            ->map(fn (Event $e) => $e->toWorkerCard($roleTypeId));

        // optional: filter by availability (after role logic)
        if ($availability) {
            $items = $items->filter(
                fn ($e) => $e['status'] === $availability
            )->values();
        }

        return response()->json([
            'data'         => $items->values(),
            'current_page' => $paginator->currentPage(),
            'last_page'    => $paginator->lastPage(),
            'total'        => $paginator->total(),
        ]);
    }

  public function apply(Request $request, Event $event): JsonResponse
{
    $userId = auth()->id();
    $worker = Worker::where('user_id', $userId)->first();

    if (!$worker) {
        return response()->json([
            'ok'      => false,
            'message' => 'Worker profile not found.',
        ], 422);
    }

    // worker main role type (matches role_types / work_roles.role_type_id)
    $roleTypeId = $worker->role_type_id;

    $role = $event->workRoles()
        ->when($roleTypeId, fn ($q) => $q->where('role_type_id', $roleTypeId))
        ->orderBy('role_id')
        ->first();

    if (!$role) {
        return response()->json([
            'ok'      => false,
            'message' => 'No matching role for your profile in this event.',
        ], 422);
    }

    // Use the same active states your ENUM supports
    $activeStatuses = ['RESERVED', 'CHECKED_IN'];

    // prevent duplicate active reservation
    $exists = WorkerReservation::where('worker_id', $worker->worker_id)
        ->where('event_id', $event->event_id)
        ->whereIn('status', $activeStatuses)
        ->exists();

    if ($exists) {
        return response()->json([
            'ok'      => false,
            'message' => 'You already applied for this event.',
        ], 422);
    }

    // capacity check for that role
    $used = WorkerReservation::where('event_id', $event->event_id)
        ->where('work_role_id', $role->role_id)
        ->whereIn('status', $activeStatuses)
        ->count();

    if ($used >= $role->required_spots) {
        return response()->json([
            'ok'      => false,
            'message' => 'No remaining spots for your role.',
        ], 422);
    }

    $reservation = WorkerReservation::create([
        'event_id'     => $event->event_id,
        'work_role_id' => $role->role_id,
        'worker_id'    => $worker->worker_id,
        'reserved_at'  => now(),
        'status'       => 'RESERVED',   // <-- match ENUM
        'created_at'   => now(),
        'updated_at'   => now(),
    ]);

    $spotsRemaining = max(0, $role->required_spots - ($used + 1));

    return response()->json([
        'ok'             => true,
        'message'        => 'Application submitted.',
        'reservation_id' => $reservation->reservation_id,
        'spotsRemaining' => $spotsRemaining,
    ]);
}


}
