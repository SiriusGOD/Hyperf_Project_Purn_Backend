<?php

use Hyperf\Database\Schema\Schema;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Migrations\Migration;

class CreateMemberCashAccount extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('member_cash_account', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('member_id')->comment('使用者ID');
            $table->integer('type')->comment('类型，1表示支付宝，2表示微信，0表示银行卡');
            $table->string('account_name')->comment('银行名称')->nullable();
            $table->string('account_number')->comment('账号')->nullable();
            $table->string('realname')->comment('姓名')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('member_cash_account');
    }
}
