<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Announcement extends Model
{
    use HasFactory;

    protected $primaryKey = 'announcement_id';
    public $timestamps = false;

    protected $fillable = ['posted_by','title','body','published_at', /* … */];

    public function postedBy()
    {
        return $this->belongsTo(Employee::class, 'posted_by', 'employee_id')->withDefault();
    }
}
