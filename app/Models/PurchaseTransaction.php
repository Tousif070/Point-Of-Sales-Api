<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PurchaseTransaction extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = "purchase_transactions";

    public function purchaseVariations()
    {
        return $this->hasMany('App\Models\PurchaseVariation');
    }
}
