<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductTransfersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('product_transfers', function (Blueprint $table) {
            
            $table->increments('id');

            $table->string('batch_no')->nullable();


            $table->integer('sender_id')->unsigned();

            $table->foreign('sender_id')->references('id')->on('users');


            $table->integer('receiver_id')->unsigned();

            $table->foreign('receiver_id')->references('id')->on('users');


            $table->integer('purchase_variation_id')->unsigned();

            $table->foreign('purchase_variation_id')->references('id')->on('purchase_variations');


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
        Schema::dropIfExists('product_transfers');
    }
}
