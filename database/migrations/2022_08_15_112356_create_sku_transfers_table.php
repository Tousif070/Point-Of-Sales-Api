<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSkuTransfersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sku_transfers', function (Blueprint $table) {
            
            $table->increments('id');

            $table->string('batch_no')->nullable();


            $table->integer('purchase_variation_id')->unsigned();

            $table->foreign('purchase_variation_id')->references('id')->on('purchase_variations');


            $table->integer('previous_product_id')->unsigned();

            $table->foreign('previous_product_id')->references('id')->on('products');


            $table->integer('current_product_id')->unsigned();

            $table->foreign('current_product_id')->references('id')->on('products');


            $table->timestamps();

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sku_transfers');
    }
}
