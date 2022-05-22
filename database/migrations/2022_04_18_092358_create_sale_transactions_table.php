<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSaleTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sale_transactions', function (Blueprint $table) {
            
            $table->increments('id');

            $table->string('invoice_no')->unique()->nullable();

            $table->string('status');

            $table->decimal('amount', 20, 2)->default(0.00);

            $table->string('payment_status');

            $table->dateTime('transaction_date');


            $table->integer('customer_id')->unsigned();

            $table->foreign('customer_id')->references('id')->on('users');


            $table->decimal('shipping_charge', 10, 2)->nullable();

            $table->string('shipping_details')->nullable();

            $table->string('shipping_tracking_number')->nullable();


            $table->integer('finalized_by')->unsigned();

            $table->foreign('finalized_by')->references('id')->on('users');


            $table->dateTime('finalized_at');

            $table->tinyInteger('verification_status')->unsigned()->default(2);

            $table->string('verification_note')->nullable();


            $table->integer('verified_by')->unsigned()->nullable();

            $table->foreign('verified_by')->references('id')->on('users');


            $table->dateTime('verified_at')->nullable();

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
        Schema::dropIfExists('sale_transactions');
    }
}
