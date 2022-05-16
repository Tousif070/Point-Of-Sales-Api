<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ExpenseReference extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = "expense_references";
}
