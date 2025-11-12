<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EventCategory extends Model
{
    use HasFactory;

    protected $table = 'event_categories';
    protected $primaryKey = 'category_id';
    public $timestamps = true;

    // If your table has ONLY name + description, keep it like this.
    // (Add 'slug' here ONLY if you actually create that column.)
    protected $fillable = [
        'name',
        'description',
    ];

    public function events()
    {
        return $this->hasMany(Event::class, 'category_id', 'category_id');
    }
}
