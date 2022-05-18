<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSaleVariationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sale_variations', function (Blueprint $table) {
            
            $table->increments('id');


            $table->integer('sale_transaction_id')->unsigned();

            $table->foreign('sale_transaction_id')->references('id')->on('sale_transactions');


            $table->integer('product_id')->unsigned();

            $table->foreign('product_id')->references('id')->on('products');


            $table->integer('purchase_variation_id')->unsigned();

            $table->foreign('purchase_variation_id')->references('id')->on('purchase_variations');


            $table->unique(['sale_transaction_id', 'purchase_variation_id']);


            $table->integer('quantity')->unsigned();

            $table->integer('return_quantity')->unsigned()->default(0);

            $table->decimal('selling_price', 10, 2);

            $table->decimal('purchase_price', 10, 2);

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
        Schema::dropIfExists('sale_variations');
    }
}
