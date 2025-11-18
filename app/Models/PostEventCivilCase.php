<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PostEventCivilCase extends Model
{
    protected $fillable = [
        'submission_id',
        'case_type',
        'age',
        'gender',
        'action_taken',
        'notes',
    ];

    public function submission()
    {
        return $this->belongsTo(PostEventSubmission::class, 'submission_id');
    }
}
