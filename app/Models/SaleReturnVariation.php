<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SaleReturnVariation extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = "sale_return_variations";

    public function saleReturnTransaction()
    {
        return $this->belongsTo('App\Models\SaleReturnTransaction');
    }
}
