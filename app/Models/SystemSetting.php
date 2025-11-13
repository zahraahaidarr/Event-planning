<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SystemSetting extends Model
{
    use HasFactory;
    protected $table = 'system_settings';
    protected $primaryKey = 'setting_id';
    public $timestamps = false;

    protected $fillable = ['key','value','description'];
}
