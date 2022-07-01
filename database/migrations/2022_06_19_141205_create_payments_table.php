<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePaymentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('payments', function (Blueprint $table) {
            
            $table->increments('id');

            $table->string('payment_no')->unique()->nullable();

            $table->string('payment_for');

            $table->integer('transaction_id')->unsigned();

            $table->decimal('amount', 20, 2);

            $table->dateTime('payment_date');


            $table->integer('payment_method_id')->unsigned();

            $table->foreign('payment_method_id')->references('id')->on('payment_methods');


            $table->string('payment_note')->nullable();


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
        Schema::dropIfExists('payments');
    }
}
