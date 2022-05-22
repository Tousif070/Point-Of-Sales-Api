<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSaleReturnTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sale_return_transactions', function (Blueprint $table) {
            
            $table->increments('id');

            $table->string('invoice_no')->unique()->nullable();

            $table->decimal('amount', 20, 2)->default(0.00);


            $table->integer('sale_transaction_id')->unsigned();

            $table->foreign('sale_transaction_id')->references('id')->on('sale_transactions');


            $table->dateTime('transaction_date');

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
        Schema::dropIfExists('sale_return_transactions');
    }
}
