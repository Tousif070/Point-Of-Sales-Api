<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePoolsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pools', function (Blueprint $table) {

            $table->increments('id');

            $table->string('type');

            $table->decimal('amount', 20, 2);

            $table->string('note')->nullable();


            $table->integer('finalized_by')->unsigned()->nullable();

            $table->foreign('finalized_by')->references('id')->on('users');
            

            $table->dateTime('finalized_at')->nullable();

            $table->timestamps();

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('pools');
    }
}
