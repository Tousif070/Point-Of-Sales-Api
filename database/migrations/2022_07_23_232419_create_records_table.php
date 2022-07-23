<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRecordsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('records', function (Blueprint $table) {

            $table->increments('id');

            $table->string('category');

            $table->string('type');

            $table->string('cash_flow');

            $table->decimal('amount', 20, 2);

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
        Schema::dropIfExists('records');
    }
}
