<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Carbon\Carbon;

class Event extends Model
{
    use HasFactory;

    protected $table = 'events';
    protected $primaryKey = 'event_id';
    public $incrementing = true;
    public $timestamps = true;

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at'   => 'datetime',
    ];
    protected $dates = ['starts_at'];
    protected $fillable = [
        'title',
        'description',
        'category_id',
        'location',
        'venue_area_sqm',
        'expected_attendance',
        'total_spots',
        'status',
        'requirements',
        'starts_at',
        'ends_at',
        'duration_hours',
        'created_by',
        'staffing_mode',
        'image_path',
    ];

    /* ========= Relationships ========= */

    public function category()
    {
        return $this->belongsTo(EventCategory::class, 'category_id', 'category_id');
    }

    public function workRoles()
    {
        return $this->hasMany(WorkRole::class, 'event_id', 'event_id');
    }

    public function reservations()
    {
        return $this->hasMany(WorkerReservation::class, 'event_id', 'event_id');
    }

    public function postEventSubmissions()
    {
        return $this->hasMany(PostEventSubmission::class, 'event_id', 'event_id');
    }

    /* ========= Scope ========= */

    public function scopePublishedFuture($query)
    {
        return $query
            ->where('status', 'PUBLISHED')
            ->whereNotNull('starts_at')
            ->where('starts_at', '>=', now());
    }

    /* ========= Internals ========= */

    protected function safeFormatDate($value, string $format): string
    {
        if (!$value) return '';

        if ($value instanceof \DateTimeInterface) {
            return $value->format($format);
        }

        try {
            return Carbon::parse($value)->format($format);
        } catch (\Throwable $e) {
            return '';
        }
    }

    /**
     * Calculate spots for a given worker role_type_id.
     * If $roleTypeId is null => uses all roles.
     */
     public function calculateRoleSpots(?int $roleTypeId): array
    {
        // statuses that actually occupy a spot
        $activeStatuses = ['RESERVED', 'CHECKED_IN'];

        if ($roleTypeId) {
            // roles in this event matching worker's role_type
            $roleQuery = $this->workRoles()->where('role_type_id', $roleTypeId);

            $total   = (int) $roleQuery->sum('required_spots');
            $roleIds = $roleQuery->pluck('role_id');

            $taken = (int) WorkerReservation::query()
                ->where('event_id', $this->event_id)
                ->whereIn('work_role_id', $roleIds)
                ->whereIn('status', $activeStatuses)   // ✅ count only active
                ->count();
        } else {
            // fallback: summary across all roles
            $total = (int) $this->workRoles()->sum('required_spots');

            $taken = (int) WorkerReservation::query()
                ->where('event_id', $this->event_id)
                ->whereIn('status', $activeStatuses)   // ✅ count only active
                ->count();
        }

        $remaining = max(0, $total - $taken);

        if ($total <= 0) {
            $status = 'full';
        } elseif ($remaining <= 0) {
            $status = 'full';
        } elseif ($remaining <= max(1, (int) round($total * 0.25))) {
            $status = 'limited';
        } else {
            $status = 'open';
        }

        return [
            'total'     => $total,
            'taken'     => $taken,        // active reservations only
            'remaining' => $remaining,    // true remaining
            'status'    => $status,
        ];
    }



    /* ========= Transformer for Discover Grid ========= */

    /**
     * Build card data tailored for a worker with given role_type_id.
     * - spotsTotal / spotsRemaining / status => ONLY for that worker's role.
     * - roles[] => all role names needed for that event.
     */
  
public function toWorkerCard(?int $workerRoleTypeId = null): array
{
    // calculateRoleSpots() should return:
    // ['total' => totalSpots, 'taken' => takenSpots, 'status' => 'open|limited|full']
    $roleSpots = $this->calculateRoleSpots($workerRoleTypeId);

    $allRoles = $this->workRoles()
        ->orderBy('role_name')
        ->pluck('role_name')
        ->unique()
        ->values()
        ->all();

    // Normalize + derive remaining
    $spotsTotal = $roleSpots['total'] ?? 0;
    $spotsTaken = $roleSpots['taken'] ?? 0;
    $spotsRemaining = max(0, $spotsTotal - $spotsTaken);

    return [
        'id'             => $this->event_id,
        'title'          => $this->title,
        'description'    => Str::limit(strip_tags((string) $this->description), 260),

        'category'       => optional($this->category)->name ?? 'General',
        'location'       => $this->location ?? 'TBD',

        'date'           => $this->safeFormatDate($this->starts_at, 'Y-m-d'),
        'time'           => $this->safeFormatDate($this->starts_at, 'H:i'),

        'duration'       => $this->duration_hours
                            ? $this->duration_hours . ' hours'
                            : '—',

        // JS can now do:
        //   used = spotsTotal - spotsRemaining
        // or use spotsUsed directly.
        'spotsTotal'     => $spotsTotal,
        'spotsRemaining' => $spotsRemaining,   // ✅ true remaining
        'spotsUsed'      => $spotsTaken,       // ✅ true taken (RESERVED + CHECKED_IN)

        'status'         => $roleSpots['status'] ?? 'open',

        'image'          => $this->image_url
                            ?? asset('images/events/default.jpg'),

        'roles'          => $allRoles ?: ['General Volunteer'],
    ];
}

public function getImageUrlAttribute(): string
{
    if ($this->image_path) {
        return asset('storage/'.$this->image_path);
    }

    return asset('images/events/default.jpg');
}


}
