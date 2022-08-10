<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class VerificationRecord extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = "verification_records";
}
