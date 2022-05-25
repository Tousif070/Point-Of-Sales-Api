<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    protected $table = "users";

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'pin_number'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];


    public function roles()
    {
        return $this->belongsToMany('App\Models\Role', 'role_user', 'user_id', 'role_id');
    }

    public function userDetail()
    {
        return $this->hasOne('App\Models\UserDetail');
    }

    public function hasPermission($permission_name)
    {
        foreach($this->roles as $role)
        {
            if($role->name == "super_admin")
            {
                return true;
            }

            foreach($role->permissions as $permission)
            {
                if($permission->name == $permission_name)
                {
                    return true;
                }
            }
        }

        return false;
    }

    public function getPermissions()
    {
        $permissions = [];

        foreach($this->roles as $role)
        {
            if($role->name == "super_admin")
            {
                $permissions = "all";

                break;
            }

            foreach($role->permissions as $permission)
            {
                $permissions[] = $permission->name;
            }
        }

        return $permissions;
    }

    public function getRoles()
    {
        $roles = [];

        foreach($this->roles as $role)
        {
            $roles[] = $role->name;
        }

        return $roles;
    }


}
