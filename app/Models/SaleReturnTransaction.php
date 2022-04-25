<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SaleReturnTransaction extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = "sale_return_transactions";

    public function saleTransaction()
    {
        return $this->belongsTo('App\Models\SaleTransaction');
    }

    public function saleReturnVariations()
    {
        return $this->hasMany('App\Models\SaleReturnVariation');
    }
}
