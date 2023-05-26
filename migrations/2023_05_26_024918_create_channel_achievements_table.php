<?php

use Hyperf\Database\Schema\Schema;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Migrations\Migration;

class CreateChannelAchievementsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('channel_achievements', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('channel_id')->comment('渠道ID');
            $table->string('channel')->comment('渠道');
            $table->date('date');
            $table->integer('hour')->comment('小時,24小時制');
            $table->decimal('pay_amount')->nullable()->index()->comment('今日業積');
            $table->string('currency')->nullable()->comment('幣別');
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
        Schema::dropIfExists('channel_achievements');
    }
};
