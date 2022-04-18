<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SaleVariation extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = "sale_variations";

    public function saleTransaction()
    {
        return $this->belongsTo('App\Models\SaleTransaction');
    }

    public function saleReturnTransaction()
    {
        return $this->belongsTo('App\Models\SaleReturnTransaction');
    }
}
