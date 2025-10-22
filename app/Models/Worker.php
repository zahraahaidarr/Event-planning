<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Worker extends Model
{
    use HasFactory;

    protected $table = 'workers';
    protected $primaryKey = 'worker_id';
    protected $keyType = 'int';
    public $incrementing = true;

    // Your migration ends with $table->timestamps();
    public $timestamps = true;

    protected $fillable = [
        'user_id',
        'engagement_kind',      // VOLUNTEER | STIPENDED | PAID
        'is_volunteer',
        'location',
        'certificate_path',     // REQUIRED (non-nullable in DB)
        'total_hours',
        'verification_status',  // UNVERIFIED | PENDING | VERIFIED
        'hourly_rate',
        'approval_status',      // PENDING | APPROVED | REJECTED | SUSPENDED
        'approved_by',
        'approved_at',
        'joined_at',
    ];

    protected $casts = [
        'is_volunteer'   => 'boolean',
        'total_hours'    => 'decimal:2',
        'hourly_rate'    => 'decimal:2',
        'approved_at'    => 'datetime',
        'joined_at'      => 'date',
    ];

    /* ========= Relationships ========= */

    // User (1–1 inverse)
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    // Approver (optional) – employees.employee_id
    public function approvedBy()
    {
        return $this->belongsTo(Employee::class, 'approved_by', 'employee_id');
    }

    // Skills (many-to-many via workers_skills)
    public function skills()
    {
        return $this->belongsToMany(
            Skill::class,
            'workers_skills',
            'worker_id',
            'skill_id'
        )->withTimestamps();
    }

    // Reservations (1–many)
    public function reservations()
    {
        return $this->hasMany(WorkerReservation::class, 'worker_id', 'worker_id');
    }

    // Post-Event Submissions (1–many)
    public function postEventSubmissions()
    {
        return $this->hasMany(PostEventSubmission::class, 'worker_id', 'worker_id');
    }

    /* ========= Scopes (handy filters) ========= */

    public function scopePending($query)
    {
        return $query->where('approval_status', 'PENDING');
    }

    public function scopeApproved($query)
    {
        return $query->where('approval_status', 'APPROVED');
    }

    public function scopeVerified($query)
    {
        return $query->where('verification_status', 'VERIFIED');
    }
}
