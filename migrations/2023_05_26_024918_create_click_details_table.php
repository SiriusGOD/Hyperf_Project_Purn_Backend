<?php

use Hyperf\Database\Schema\Schema;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Migrations\Migration;

class CreateClickDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('click_details', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('click_id')->comment('點擊次數 id');
            $table->bigInteger('member_id')->comment('點擊 user');
            $table->datetimes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('click_details');
    }
};
