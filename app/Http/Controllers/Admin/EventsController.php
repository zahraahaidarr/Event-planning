<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;

use App\Models\Event;
use App\Models\WorkRole;
use App\Models\RoleType;
use App\Models\EventCategory;
use App\Models\Employee;


class EventsController extends Controller
{
    /**
     * GET /admin/events
     */
    public function index()
    {
        $events = Event::orderByDesc('created_at')
            ->get([
                'event_id',
                'title',
                'category_id',
                'location',
                'status',
                'total_spots',
                'starts_at',
                'created_at',
            ]);

        $categories = EventCategory::orderBy('name')
            ->get(['category_id', 'name']);

        $roleTypes = RoleType::orderBy('name')
            ->get(['role_type_id', 'name']);

        $eventsPayload = $events->map(function ($e) {
            return [
                'id'          => $e->event_id,
                'title'       => $e->title,
                'category'    => $e->category_id,
                'date'        => optional($e->starts_at)->format('Y-m-d'),
                'location'    => $e->location,
                'status'      => $e->status ?? 'published',
                'totalSpots'  => $e->total_spots ?? 0,
                'applicants'  => 0,
            ];
        });

        $categoriesPayload = $categories->map(fn ($c) => [
            'id'   => $c->category_id,
            'name' => $c->name,
        ]);

        $roleTypesPayload = $roleTypes->map(fn ($r) => [
            'id'   => $r->role_type_id,
            'name' => $r->name,
        ]);

        return view('admin.events', [
            'eventsPayload'     => $eventsPayload,
            'categoriesPayload' => $categoriesPayload,
            'roleTypesPayload'  => $roleTypesPayload,
        ]);
    }

    /**
     * POST /admin/events
     */
public function store(Request $request)
{

    // If roles came as JSON string (multipart/FormData), decode to array for validation
if ($request->has('roles') && is_string($request->input('roles'))) {
    $decoded = json_decode($request->input('roles'), true);
    if (json_last_error() === JSON_ERROR_NONE) {
        $request->merge(['roles' => $decoded]);
    }
}

    // 1) Validate incoming JSON from the wizard
    $validated = $request->validate([
        'title'              => ['required', 'string', 'max:255'],
        'description'        => ['required', 'string'],
        'category'           => ['required', 'string', 'max:100'], // category NAME
        'location'           => ['required', 'string', 'max:255'],
        'date'               => ['required', 'date_format:Y-m-d'],
        'time'               => ['required', 'date_format:H:i'],
        'duration_hours'     => ['required', 'numeric', 'min:0.5', 'max:240'],
        'total_spots'        => ['required', 'integer', 'min:1'],
        'requirements'       => ['nullable', 'string'],

        'roles'              => ['required', 'array', 'min:1'],
        'roles.*.name'       => ['required', 'string', 'max:100'],
        'roles.*.slug'       => ['nullable', 'string', 'max:120'],
        'roles.*.spots'      => ['required', 'integer', 'min:1'],

        'venue_area_m2'      => ['nullable', 'numeric', 'min:0'],
        'expected_attendees' => ['nullable', 'integer', 'min:0'],

        // NEW: optional status coming from UI buttons
        'status'             => ['nullable', 'string', 'in:DRAFT,PUBLISHED,ACTIVE,COMPLETED,CANCELLED'],
        'image' => 'nullable|image|max:2048',

    ]);
    // Handle optional event image upload
if ($request->hasFile('image')) {
    $path = $request->file('image')->store('events', 'public');
    $validated['image_path'] = $path;
}

    // Decide final status (keep working default)
    $status = strtoupper($validated['status'] ?? 'PUBLISHED');

    // 2) Build start/end datetime
    $startsAt = Carbon::createFromFormat('Y-m-d H:i', $validated['date'].' '.$validated['time'])
        ->seconds(0);
    $endsAt = (clone $startsAt)->addHours((float) $validated['duration_hours']);

    // 3) Ensure total_spots = sum(role.spots)
    $sumRoles = collect($validated['roles'])->sum('spots');
    if ($sumRoles <= 0) {
        return response()->json([
            'ok'      => false,
            'message' => 'At least one role with spots > 0 is required.',
        ], 422);
    }
    $validated['total_spots'] = $sumRoles;

    // 4) Resolve category by NAME -> ID
    $category = EventCategory::firstOrCreate(
        ['name' => $validated['category']],
        ['description' => null]
    );

    // 5) Resolve created_by (FK -> employees.employee_id)
    $userId = auth()->id();

    // primary: employee row linked to this user
    $creatorId = Employee::where('user_id', $userId)->value('employee_id');

    // fallback: use first employee as "system" creator
    if (!$creatorId) {
        $creatorId = Employee::min('employee_id');
    }

    if (!$creatorId) {
        return response()->json([
            'ok'      => false,
            'message' => 'No employees exist to assign as created_by (check employees table).',
        ], 422);
    }

    // 6) Create event + roles transactionally
    try {
        $event = DB::transaction(function () use ($validated, $category, $startsAt, $endsAt, $creatorId, $status) {

            $event = Event::create([
                'title'               => $validated['title'],
                'description'         => $validated['description'],
                'category_id'         => $category->category_id,
                'location'            => $validated['location'],
                'venue_area_sqm'      => $validated['venue_area_m2'] ?? null,
                'expected_attendance' => $validated['expected_attendees'] ?? null,
                'total_spots'         => (int) $validated['total_spots'],
                'status'              => $status, // <-- only change here
                'requirements'        => $validated['requirements'] ?? null,
                'starts_at'           => $startsAt,
                'ends_at'             => $endsAt,
                'duration_hours'      => (float) $validated['duration_hours'],
                'created_by'          => $creatorId,
                'image_path'          => $validated['image_path'] ?? null,

            ]);

            foreach ($validated['roles'] as $role) {
                $name  = trim($role['name']);
                $spots = (int) $role['spots'];

                if ($name === '' || $spots <= 0) {
                    continue;
                }

                $roleType = RoleType::firstOrCreate(
                    ['name' => $name],
                    ['description' => null]
                );

                WorkRole::create([
                    'event_id'        => $event->event_id,
                    'role_type_id'    => $roleType->role_type_id,
                    'role_name'       => $name,
                    'required_spots'  => $spots,
                    'calc_source'     => 'manual',
                    'calc_confidence' => null,
                    'description'     => null,
                ]);
            }

            return $event;
        });

        // 7) Success JSON
        return response()->json([
            'ok'    => true,
            'event' => [
                'id'          => $event->event_id,
                'title'       => $event->title,
                'category'    => $category->name,
                'location'    => $event->location,
                'status'      => $event->status,
                'total_spots' => $event->total_spots,
                'starts_at'   => optional($event->starts_at)->toIso8601String(),
            ],
            'message' => 'Event created successfully.',
        ], 201);

    } catch (\Throwable $e) {
        return response()->json([
            'ok'      => false,
            'message' => 'Server error while creating event.',
            'error'   => $e->getMessage(),
        ], 500);
    }
}

    public function show(Event $event): JsonResponse
    {
        $event->load('category', 'workRoles');

        return response()->json([
            'ok'    => true,
            'event' => [
                'id'                 => $event->event_id,
                'title'              => $event->title,
                'description'        => $event->description,
                'category'           => optional($event->category)->name,
                'location'           => $event->location,
                'date'               => optional($event->starts_at)->format('Y-m-d'),
                'time'               => optional($event->starts_at)->format('H:i'),
                'duration_hours'     => $event->duration_hours,
                'total_spots'        => $event->total_spots,
                'venue_area_m2'      => $event->venue_area_sqm,
                'expected_attendees' => $event->expected_attendance,
                'status'             => $event->status,
                'roles'              => $event->workRoles
                    ->map(fn ($r) => [
                        'name'  => $r->role_name,
                        'spots' => $r->required_spots,
                    ])
                    ->values(),
            ],
        ]);
    }

    /** PUT /admin/events/{event} */
    public function update(Request $request, Event $event): JsonResponse
    {

        // If roles came as JSON string (multipart/FormData), decode to array for validation
if ($request->has('roles') && is_string($request->input('roles'))) {
    $decoded = json_decode($request->input('roles'), true);
    if (json_last_error() === JSON_ERROR_NONE) {
        $request->merge(['roles' => $decoded]);
    }
}

        $validated = $request->validate([
            'title'              => ['required', 'string', 'max:255'],
            'description'        => ['required', 'string'],
            'category'           => ['required', 'string', 'max:100'],
            'location'           => ['required', 'string', 'max:255'],
            'date'               => ['required', 'date_format:Y-m-d'],
            'time'               => ['required', 'date_format:H:i'],
            'duration_hours'     => ['required', 'numeric', 'min:0.5', 'max:240'],
            'total_spots'        => ['required', 'integer', 'min:1'],
            'requirements'       => ['nullable', 'string'],

            'roles'              => ['required', 'array', 'min:1'],
            'roles.*.name'       => ['required', 'string', 'max:100'],
            'roles.*.slug'       => ['nullable', 'string', 'max:120'],
            'roles.*.spots'      => ['required', 'integer', 'min:1'],

            'venue_area_m2'      => ['nullable', 'numeric', 'min:0'],
            'expected_attendees' => ['nullable', 'integer', 'min:0'],

            'status'             => ['nullable', 'string', 'in:DRAFT,PUBLISHED,ACTIVE,COMPLETED,CANCELLED'],
            'image'              => ['nullable', 'image', 'max:2048'],

        ]);
        // Optional: replace event image if a new one is uploaded
if ($request->hasFile('image')) {
    if ($event->image_path) {
        Storage::disk('public')->delete($event->image_path);
    }
    $validated['image_path'] = $request->file('image')->store('events', 'public');
}

        $status = strtoupper($validated['status'] ?? $event->status ?? 'PUBLISHED');

        $startsAt = Carbon::createFromFormat('Y-m-d H:i', $validated['date'].' '.$validated['time'])
            ->seconds(0);
        $endsAt = (clone $startsAt)->addHours((float) $validated['duration_hours']);

        $sumRoles = collect($validated['roles'])->sum('spots');
        if ($sumRoles <= 0) {
            return response()->json([
                'ok'      => false,
                'message' => 'At least one role with spots > 0 is required.',
            ], 422);
        }
        $validated['total_spots'] = $sumRoles;

        $category = EventCategory::firstOrCreate(
            ['name' => $validated['category']],
            ['description' => null]
        );

        try {
            DB::transaction(function () use (
                $event,
                $validated,
                $category,
                $startsAt,
                $endsAt,
                $status
            ) {
                $event->update([
                    'title'               => $validated['title'],
                    'description'         => $validated['description'],
                    'category_id'         => $category->category_id,
                    'location'            => $validated['location'],
                    'venue_area_sqm'      => $validated['venue_area_m2'] ?? null,
                    'expected_attendance' => $validated['expected_attendees'] ?? null,
                    'total_spots'         => (int) $validated['total_spots'],
                    'status'              => $status,
                    'requirements'        => $validated['requirements'] ?? null,
                    'starts_at'           => $startsAt,
                    'ends_at'             => $endsAt,
                    'duration_hours'      => (float) $validated['duration_hours'],
                    'image_path'          => $validated['image_path'] ?? $event->image_path,

                ]);

                // reset roles
                $event->workRoles()->delete();

                foreach ($validated['roles'] as $role) {
                    $name  = trim($role['name']);
                    $spots = (int) $role['spots'];
                    if ($name === '' || $spots <= 0) {
                        continue;
                    }

                    $roleType = RoleType::firstOrCreate(
                        ['name' => $name],
                        ['description' => null]
                    );

                    WorkRole::create([
                        'event_id'        => $event->event_id,
                        'role_type_id'    => $roleType->role_type_id,
                        'role_name'       => $name,
                        'required_spots'  => $spots,
                        'calc_source'     => 'manual',
                        'calc_confidence' => null,
                        'description'     => null,
                    ]);
                }
            });

            return response()->json([
                'ok'    => true,
                'event' => [
                    'id'          => $event->event_id,
                    'title'       => $event->title,
                    'category'    => $category->name,
                    'location'    => $event->location,
                    'status'      => $event->status,
                    'total_spots' => $event->total_spots,
                    'starts_at'   => optional($event->starts_at)->toIso8601String(),
                ],
                'message' => 'Event updated successfully.',
            ]);

        } catch (\Throwable $e) {
            return response()->json([
                'ok'      => false,
                'message' => 'Server error while updating event.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /** PATCH /admin/events/{event}/status */
public function updateStatus(Request $request, Event $event): \Illuminate\Http\JsonResponse
{
    $data = $request->validate([
        'status' => ['required', 'string', 'in:DRAFT,PUBLISHED,ACTIVE,COMPLETED,CANCELLED'],
    ]);

    $event->status = $data['status'];
    $event->save();

    return response()->json([
        'ok'    => true,
        'event' => [
            'id'     => $event->event_id,
            'status' => $event->status,
        ],
    ]);
}




}
