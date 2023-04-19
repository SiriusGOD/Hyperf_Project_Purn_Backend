<?php

use Hyperf\Database\Schema\Schema;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Migrations\Migration;

class CreateMemberInviteStart extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('member_invite_start', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('member_id')->index();
            $table->decimal('d_count', 10, 2);
            $table->decimal('k_count', 10, 2);
            $table->integer('level')->comment('0 默认 1黄金 2 铂金 3钻石 4 星耀 5 王者')->index();
            $table->integer('differ_num')->comment('支付邀请日期');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('member_invite_start');
    }
}
