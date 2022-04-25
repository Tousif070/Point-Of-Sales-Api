<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSaleReturnVariationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sale_return_variations', function (Blueprint $table) {

            $table->increments('id');


            $table->integer('sale_return_transaction_id')->unsigned();

            $table->foreign('sale_return_transaction_id')->references('id')->on('sale_return_transactions')->onDelete('cascade');


            $table->integer('product_id')->unsigned();

            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');


            $table->integer('purchase_variation_id')->unsigned();

            $table->foreign('purchase_variation_id')->references('id')->on('purchase_variations')->onDelete('cascade');


            $table->integer('quantity')->unsigned();

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
        Schema::dropIfExists('sale_return_variations');
    }
}
