<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SaleTransaction extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = "sale_transactions";

    public function saleVariations()
    {
        return $this->hasMany('App\Models\SaleVariation');
    }

    public function saleReturnTransactions()
    {
        return $this->hasMany('App\Models\SaleReturnTransaction');
    }
}
