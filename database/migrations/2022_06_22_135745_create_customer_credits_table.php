<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCustomerCreditsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('customer_credits', function (Blueprint $table) {
            
            $table->increments('id');

            $table->decimal('amount', 20, 2);

            $table->string('type');

            $table->string('sale_invoice')->default("N/A");

            $table->string('sale_return_invoice')->default("N/A");

            
            $table->integer('customer_id')->unsigned();

            $table->foreign('customer_id')->references('id')->on('users');

            
            $table->string('note')->nullable();


            $table->integer('finalized_by')->unsigned();

            $table->foreign('finalized_by')->references('id')->on('users');


            $table->dateTime('finalized_at');

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
        Schema::dropIfExists('customer_credits');
    }
}
