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
        $product_category = ProductCategory::where('name', '=', 'Phone')->first();

        if($product_category == null)
        {
            $product_category = new ProductCategory();

            $product_category->name = "Phone";

            $product_category->type = "Variable";

            $product_category->save();
        }


        $product_category = ProductCategory::where('name', '=', 'Charger')->first();

        if($product_category == null)
        {
            $product_category = new ProductCategory();

            $product_category->name = "Charger";

            $product_category->type = "Single";

            $product_category->save();
        }
    }
}
