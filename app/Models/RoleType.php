<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RoleType extends Model
{
    use HasFactory;

    protected $primaryKey = 'role_type_id';
    public $timestamps = false;

    protected $fillable = ['role_name','description'];

    public function workRoles()
    {
        return $this->hasMany(WorkRole::class, 'role_type_id', 'role_type_id');
    }
}
