<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLoginLogoutRecordsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('login_logout_records', function (Blueprint $table) {
            
            $table->increments('id');


            $table->integer('user_id')->unsigned();

            $table->foreign('user_id')->references('id')->on('users');


            $table->string('type');

            $table->tinyInteger('user_type')->unsigned();

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
        Schema::dropIfExists('login_logout_records');
    }
}
