<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LoginLogoutRecord extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = "login_logout_records";
}
