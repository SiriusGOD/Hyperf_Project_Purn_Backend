<?php

use Hyperf\Database\Schema\Schema;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Migrations\Migration;

class CreateMemberInviteReceiveLogTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('member_invite_receive_log', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('member_id')->index()->comment('来自充值用户');
            $table->integer('invite_by')->index()->comment('用户被谁邀请');
            $table->string('order_sn')->comment('订单号');
            $table->decimal('amount', 10)->comment('订单金额元');
            $table->decimal('reach_amount', 10)->comment('到账元');
            $table->integer('level')->index()->comment('用户被谁邀请');
            $table->decimal('rate')->comment('分层比例');
            $table->integer('type')->comment('支付邀请日期');
            $table->datetimes();
            $table->string('product_name')->default('')->comment('商品名稱');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('member_invite_receive_log');
    }
};
