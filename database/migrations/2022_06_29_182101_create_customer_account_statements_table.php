<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCustomerAccountStatementsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('customer_account_statements', function (Blueprint $table) {
            
            $table->increments('id');
            
            $table->string('type');

            $table->integer('reference_id')->unsigned();

            $table->decimal('amount', 20, 2);

            
            $table->integer('customer_id')->unsigned();

            $table->foreign('customer_id')->references('id')->on('users');


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
        Schema::dropIfExists('customer_account_statements');
    }
}
