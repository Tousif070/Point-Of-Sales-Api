<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCustomerUserAssociationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('customer_user_associations', function (Blueprint $table) {
            
            $table->increments('id');
            

            $table->integer('user_official_id')->unsigned();

            $table->foreign('user_official_id')->references('id')->on('users');


            $table->integer('customer_id')->unsigned();

            $table->foreign('customer_id')->references('id')->on('users');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('customer_user_associations');
    }
}
