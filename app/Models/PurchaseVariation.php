<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PurchaseVariation extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = "purchase_variations";

    public function purchaseTransaction()
    {
        return $this->belongsTo('App\Models\PurchaseTransaction');
    }

    public function product()
    {
        return $this->belongsTo('App\Models\Product');
    }
}
