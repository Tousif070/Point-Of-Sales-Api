<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateExpenseTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('expense_transactions', function (Blueprint $table) {
            
            $table->increments('id');

            
            $table->integer('expense_reference_id')->unsigned();

            $table->foreign('expense_reference_id')->references('id')->on('expense_references');


            $table->integer('expense_category_id')->unsigned();

            $table->foreign('expense_category_id')->references('id')->on('expense_categories');


            $table->decimal('amount', 20, 2);

            $table->string('payment_status');

            $table->dateTime('transaction_date');


            $table->integer('expense_for')->unsigned()->nullable();

            $table->foreign('expense_for')->references('id')->on('users');


            $table->string('expense_note');


            $table->integer('finalized_by')->unsigned();

            $table->foreign('finalized_by')->references('id')->on('users');


            $table->dateTime('finalized_at');

            $table->integer('verification_status')->unsigned()->default(2);

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
        Schema::dropIfExists('expense_transactions');
    }
}
