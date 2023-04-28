<?php

use Hyperf\Database\Schema\Schema;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Migrations\Migration;

class CreateMemberWithdraw extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('member_withdraw', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('member_id')->comment('會員ID');
            $table->integer('type')->comment('类型，1表示支付宝，2表示微信，0表示银行卡');
            $table->string('uuid')->comment('');
            $table->string('cash_id')->comment('提现订单号');
            $table->string('account')->comment('銀行帳號');
            $table->string('account_name')->comment('銀行號');
            $table->string('name')->comment('姓名');
            $table->decimal('amount', 10, 2)->default(0)->comment('提现金额');
            $table->decimal('trueto_amount', 10, 2)->default(0)->comment('實際收到金額');
            $table->integer('status')->comment('提现状态 0:审核中;1:已完成;2:未通过');
            $table->string('descp')->comment('状态说明');
            $table->dateTime('payed_at')->comment('兑换回调时间');
            $table->string('channel')->comment('渠道');
            $table->string('third_id')->comment('三方订单号');
            $table->string('order_desc')->comment('订单说明\n 打款回调等备注');
            $table->decimal('coins', 10, 2)->default(0)->comment('提现余额 金币');
            $table->integer('withdraw_type')->comment('提现收款方式1银行卡2');
            $table->integer('withdraw_from')->comment('提现来源1货币2代理');
            $table->string('ip')->comment('ip信息');
            $table->string('address')->comment('ip地区解析');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('member_withdraw');
    }
}
