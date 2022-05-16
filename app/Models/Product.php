<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = "products";

    public function productCategory()
    {
        return $this->belongsTo('App\Models\ProductCategory');
    }

    public function purchaseVariations()
    {
        return $this->hasMany('App\Models\PurchaseVariation');
    }
}
