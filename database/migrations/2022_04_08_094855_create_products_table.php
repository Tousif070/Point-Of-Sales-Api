<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('products', function (Blueprint $table) {

            $table->increments('id');

            $table->string('name');


            $table->integer('brand_id')->unsigned();

            $table->foreign('brand_id')->references('id')->on('brands');


            $table->integer('product_category_id')->unsigned();

            $table->foreign('product_category_id')->references('id')->on('product_categories');


            $table->integer('product_model_id')->unsigned();

            $table->foreign('product_model_id')->references('id')->on('product_models');


            $table->integer('sku')->unsigned()->unique()->nullable();

            
            $table->integer('file_id')->unsigned();

            $table->foreign('file_id')->references('id')->on('files');


            $table->string('color');

            $table->integer('ram')->unsigned()->nullable();

            $table->integer('storage')->unsigned()->nullable();

            $table->string('condition');

            $table->string('size')->nullable();

            $table->string('wattage')->nullable();

            $table->string('type')->nullable();

            $table->string('length')->nullable();

            $table->timestamps();

            $table->softDeletes();
            
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('products');
    }
}
