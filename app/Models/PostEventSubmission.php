<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PostEventSubmission extends Model
{
    protected $fillable = [
        'worker_reservation_id',
        'event_id',
        'worker_id',
        'work_role_id',
        'role_slug',
        'general_notes',
        'data',
        'status',
        'submitted_at',
        'reviewed_at',
        'reviewed_by',
        'review_notes',
    ];
    protected $guarded = [];  // easiest while you’re building
    protected $casts = [
        'data'         => 'array',
        'submitted_at' => 'datetime',
        'reviewed_at'  => 'datetime',
    ];

    public function reservation()
    {
        return $this->belongsTo(WorkerReservation::class, 'worker_reservation_id');
    }

    public function event()
    {
        return $this->belongsTo(Event::class, 'event_id', 'event_id');
    }

    public function worker()
    {
        // workers.worker_id is PK
        return $this->belongsTo(Worker::class, 'worker_id', 'worker_id');
    }

    public function role()
    {
        return $this->belongsTo(WorkRole::class, 'work_role_id');
    }

    public function files()
    {
        return $this->hasMany(PostEventSubmissionFile::class, 'submission_id');
    }

    public function civilCases()
    {
        return $this->hasMany(PostEventCivilCase::class, 'submission_id');
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}
