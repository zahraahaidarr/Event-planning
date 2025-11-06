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

    protected $fillable = ['name','description'];

    public function events()
    {
        return $this->hasMany(Event::class, 'category_id', 'category_id');
    }
}
