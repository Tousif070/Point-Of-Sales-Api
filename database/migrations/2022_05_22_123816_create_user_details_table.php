<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_details', function (Blueprint $table) {
            
            $table->increments('id');

            
            $table->integer('user_id')->unsigned();

            $table->foreign('user_id')->references('id')->on('users');


            $table->string('business_name')->nullable();

            $table->string('business_website')->nullable();

            $table->string('tax_id')->nullable();

            
            $table->integer('file_id')->unsigned()->nullable();

            $table->foreign('file_id')->references('id')->on('files');


            $table->string('contact_no');

            $table->string('address');

            $table->string('city');

            $table->string('state');

            $table->string('country');

            $table->string('zip_code')->nullable();

            $table->text('shipping_addresses')->default("[]");

            $table->tinyInteger('customer_approval_status')->unsigned()->nullable();

            $table->string('customer_type')->nullable();

            $table->decimal('available_credit', 20, 2)->nullable();

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
        Schema::dropIfExists('user_details');
    }
}
