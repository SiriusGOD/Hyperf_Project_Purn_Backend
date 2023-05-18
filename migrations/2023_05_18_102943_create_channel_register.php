<?php

use Hyperf\Database\Schema\Schema;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Migrations\Migration;

class CreateChannelRegister extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('channel_register', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('channel_id')->comment('渠道ID');
            $table->string('channel')->comment('渠道');
            $table->date('date')->comment('日期');
            $table->integer('hour')->comment('時,24小時制');
            $table->integer('total')->default(0)->comment('人數');
            $table->datetimes();
        });
        Schema::table('channels', function (Blueprint $table) {
            $table->integer('register_total')->comment('註冊總數')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('channel_register');
    }
}
