<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ProductCategory;

class ProductCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $product_category = ProductCategory::where('name', '=', 'PHONE')->first();

        if($product_category == null)
        {
            $product_category = new ProductCategory();

            $product_category->name = "PHONE";

            $product_category->type = "Variable";

            $product_category->save();
        }


        $product_category = ProductCategory::where('name', '=', 'CHARGER/POWER ADAPTER')->first();

        if($product_category == null)
        {
            $product_category = new ProductCategory();

            $product_category->name = "CHARGER/POWER ADAPTER";

            $product_category->type = "Single";

            $product_category->save();
        }
    }
}
