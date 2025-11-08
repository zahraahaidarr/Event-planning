<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Announcement extends Model
{
    use HasFactory;

    protected $table = 'announcements';
    protected $primaryKey = 'announcement_id';
    public $incrementing = true;
    protected $keyType = 'int';

    // Your table has created_at but no updated_at
    const CREATED_AT = 'created_at';
    const UPDATED_AT = null;
    public $timestamps = false;

    protected $fillable = [
        'title',
        'body',
        'posted_by',
        'audience',     // workers | employees | both
        'created_at',
        'expires_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    // Who posted it (optional but useful)
    public function author()
    {
        return $this->belongsTo(User::class, 'posted_by');
    }

    /**
     * Scope: announcements visible for a given role.
     * Usage: Announcement::visibleForRole('worker')->get();
     */
    public function scopeVisibleForRole($query, string $role)
    {
        $role = strtolower($role);
        $now  = now();

        return $query
            // filter by audience
            ->where(function ($q) use ($role) {
                $q->where('audience', 'both');

                if ($role === 'worker') {
                    $q->orWhere('audience', 'workers');
                } elseif ($role === 'employee') {
                    $q->orWhere('audience', 'employees');
                }
            })
            // filter by expiry date (show only active or no-expiry)
            ->where(function ($q) use ($now) {
                $q->whereNull('expires_at')
                  ->orWhere('expires_at', '>', $now);
            });
    }
}
