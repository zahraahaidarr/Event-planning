<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PostEventSubmissionFile extends Model
{
    protected $fillable = [
        'submission_id',
        'section',
        'path',
        'original_name',
        'mime_type',
        'size_bytes',
    ];

    public function submission()
    {
        return $this->belongsTo(PostEventSubmission::class, 'submission_id');
    }
}
