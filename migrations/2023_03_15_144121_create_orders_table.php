<?php

use Hyperf\Database\Schema\Schema;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Migrations\Migration;

class CreateOrdersTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('user_id')->comment('會員id')->unsigned();
            $table->string('order_number')->comment('訂單編號');
            $table->string('address')->comment('訂單收件地址');
            $table->string('email')->comment('訂單人電子郵件');
            $table->string('mobile')->comment('訂單人手機');
            $table->string('telephone')->comment('訂單人電話');
            $table->integer('payment_type')->comment('付款類型');
            $table->string('currency')->comment('訂單幣別');
            $table->string('total_price')->comment('訂單總金額');
            $table->tinyInteger('status')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
}
