<?php

use Hyperf\Database\Schema\Schema;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Migrations\Migration;

class CreateChannelAchievements extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('channel_achievements', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('channel_id')->comment('渠道ID');
            $table->string('channel')->comment('渠道');
            $table->date('date');
            $table->integer('hour')->comment('小時,24小時制');
            $table->decimal('pay_amount', 8, 2)->comment('今日業積')->nullable()->index();
            $table->string('currency')->comment('幣別')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('channel_achievements');
    }
}
