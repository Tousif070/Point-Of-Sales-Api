<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePurchaseVariationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('purchase_variations', function (Blueprint $table) {

            $table->increments('id');


            $table->integer('purchase_transaction_id')->unsigned();

            $table->foreign('purchase_transaction_id')->references('id')->on('purchase_transactions');


            $table->integer('product_id')->unsigned();

            $table->foreign('product_id')->references('id')->on('products');


            $table->string('serial')->unique()->nullable();

            $table->string('group')->unique()->nullable();

            $table->integer('quantity_purchased')->unsigned();

            $table->integer('quantity_available')->unsigned();

            $table->integer('quantity_sold')->unsigned()->default(0);

            $table->decimal('purchase_price', 10, 2);

            $table->decimal('risk_fund', 10, 2);

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
        Schema::dropIfExists('purchase_variations');
    }
}
