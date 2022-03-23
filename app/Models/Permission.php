<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Permission extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = "permissions";

    public function roles()
    {
        $this->belongsToMany('App\Models\Role', 'role_permission', 'permission_id', 'role_id');
    }
}
