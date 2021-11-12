<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePersonnelTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('personnel', function (Blueprint $table) {
            $table->increments('id');
            $table->biginteger('user_id');
            $table->string('firstname', 100)->nullable();
            $table->string('lastname', 255)->nullable();
            $table->string('gender_id', 5)->nullable();
            $table->date('birthdate')->nullable();
            $table->mediumText('address')->nullable();
            $table->string('sub_district_id', 5)->nullable();
            $table->string('district_id', 5)->nullable();
            $table->string('province_id', 5)->nullable();
            $table->string('zipcode', 5)->nullable();
            $table->string('team_id', 5)->nullable();
            $table->string('phone', 15)->nullable();
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
        Schema::dropIfExists('personnel');
    }
}
