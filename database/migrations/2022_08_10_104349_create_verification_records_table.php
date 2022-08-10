<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVerificationRecordsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('verification_records', function (Blueprint $table) {
            
            $table->increments('id');

            $table->string('type');

            $table->integer('reference_id')->unsigned();
            

            $table->integer('verified_by')->unsigned();

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
        Schema::dropIfExists('verification_records');
    }
}
