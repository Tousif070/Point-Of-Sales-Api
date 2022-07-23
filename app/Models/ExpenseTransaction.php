<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ExpenseTransaction extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = "expense_transactions";

    public function payments()
    {
        return $this->hasMany('App\Models\Payment', 'transaction_id', 'id');
    }
}
