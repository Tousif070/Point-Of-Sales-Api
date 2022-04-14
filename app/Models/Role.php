<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Role extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = "roles";

    public function users()
    {
        return $this->belongsToMany('App\Models\User', 'role_user', 'role_id', 'user_id');
    }

    public function permissions()
    {
        return $this->belongsToMany('App\Models\Permission', 'role_permission', 'role_id', 'permission_id');
    }

    public function getPermissions()
    {
        $permissions = [];

        if($this->name != "super_admin")
        {
            foreach($this->permissions as $permission)
            {
                $permissions[] = $permission->name;
            }
        }
        else
        {
            $permissions[] = "all";
        }

        return $permissions;
    }


}
