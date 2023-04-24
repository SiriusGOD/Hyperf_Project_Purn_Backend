<?php

use Hyperf\Database\Schema\Schema;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Migrations\Migration;

class CreateCustomerServiceDetailsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('customer_service_details', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('customer_service_id');
            $table->bigInteger('user_id')->comment('客服 id')->nullable();
            $table->bigInteger('member_id')->comment('會員 id')->nullable();
            $table->text('message')->comment('訊息');
            $table->string('image_url')->comment('圖片 url')->default('');
            $table->tinyInteger('is_read')->comment('是否已讀');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer_service_details');
    }
}
