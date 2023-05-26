<?php

use Hyperf\Database\Schema\Schema;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Migrations\Migration;

class CreateMemberCashAccountTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('member_cash_account', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('member_id')->comment('使用者ID');
            $table->integer('type')->comment('类型，1表示支付宝，2表示微信，0表示银行卡');
            $table->string('account_name')->nullable()->comment('银行名称');
            $table->string('account_number')->nullable()->comment('账号');
            $table->string('realname')->nullable()->comment('姓名');
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
        Schema::dropIfExists('member_cash_account');
    }
};
