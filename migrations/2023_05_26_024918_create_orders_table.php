<?php

use Hyperf\Database\Schema\Schema;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Migrations\Migration;

class CreateOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id')->comment('會員id');
            $table->string('order_number')->unique()->comment('訂單編號');
            $table->string('pay_order_id')->default('')->index()->comment('產生支付鏈接後回傳的訂單編號');
            $table->string('pay_third_id')->default('')->index()->comment('第三方訂單編號');
            $table->string('address')->comment('訂單收件地址');
            $table->string('email')->comment('訂單人電子郵件');
            $table->string('mobile')->comment('訂單人手機');
            $table->string('telephone')->comment('訂單人電話');
            $table->integer('payment_type')->comment('付款類型');
            $table->string('currency')->comment('訂單幣別');
            $table->decimal('total_price')->nullable();
            $table->decimal('pay_amount')->nullable()->index()->comment('實際付款金額');
            $table->string('pay_way')->comment('支付方式');
            $table->string('pay_url')->comment('支付链接');
            $table->string('pay_proxy')->comment('online线上充值/agent代理充值');
            $table->tinyInteger('status')->default(1);
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
        Schema::dropIfExists('orders');
    }
};
