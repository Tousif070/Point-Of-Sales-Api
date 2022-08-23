<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePurchaseTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('purchase_transactions', function (Blueprint $table) {

            $table->increments('id');

            $table->string('reference_no')->unique()->nullable();

            $table->decimal('amount', 20, 2)->default(0.00);

            $table->string('purchase_status');

            $table->string('payment_status');

            $table->dateTime('transaction_date');


            $table->integer('supplier_id')->unsigned();

            $table->foreign('supplier_id')->references('id')->on('users');


            $table->integer('file_id')->unsigned()->nullable();

            $table->foreign('file_id')->references('id')->on('files');


            $table->tinyInteger('locked')->unsigned()->default(0);

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
        Schema::dropIfExists('purchase_transactions');
    }
}
