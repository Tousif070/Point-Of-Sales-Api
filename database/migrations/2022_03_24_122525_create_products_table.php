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

            $table->foreign('brand_id')->references('id')->on('brands')->onDelete('cascade');


            $table->integer('product_category_id')->unsigned();

            $table->foreign('product_category_id')->references('id')->on('product_categories')->onDelete('cascade');


            $table->integer('sku')->unsigned()->nullable();

            $table->string('image');

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
