<?php

use Hyperf\Database\Schema\Schema;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Migrations\Migration;

class CreateChannelRegisterTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('channel_register', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('channel_id')->comment('渠道ID');
            $table->string('channel')->comment('渠道');
            $table->date('date')->comment('日期');
            $table->integer('hour')->comment('時,24小時制');
            $table->integer('total')->default(0)->comment('人數');
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('channel_register');
    }
};
