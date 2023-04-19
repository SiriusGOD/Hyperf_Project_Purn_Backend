<?php

use Hyperf\Database\Schema\Schema;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Migrations\Migration;

class CreateMemberInviteReceiveLog extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('member_invite_receive_log', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('member_id')->comment('来自充值用户')->index();
            $table->integer('invite_by')->comment('用户被谁邀请')->index();
            $table->string('order_sn')->comment('订单号');
            $table->decimal('amount', 10, 2)->comment('订单金额元');
            $table->decimal('reach_amount', 10, 2)->comment('到账元');
            $table->integer('level')->comment('用户被谁邀请')->index();
            $table->decimal('rate', 8, 2)->comment('分层比例');
            $table->integer('type')->comment('支付邀请日期');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('member_invite_receive_log');
    }
}
