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

    protected $casts = [
        'data'         => 'array',
        'submitted_at' => 'datetime',
        'reviewed_at'  => 'datetime',
    ];

    public function reservation()
    {
        return $this->belongsTo(WorkersReservation::class, 'worker_reservation_id');
    }

    public function event()
    {
        // if events PK = id; if PK = event_id, add third argument 'event_id'
        return $this->belongsTo(Event::class, 'event_id', 'event_id');
    }

    public function worker()
    {
        // if workers PK is worker_id
        return $this->belongsTo(Worker::class, 'worker_id', 'worker_id');
    }

    public function workRole()
    {
        return $this->belongsTo(WorkRole::class, 'work_role_id', 'role_id');
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
