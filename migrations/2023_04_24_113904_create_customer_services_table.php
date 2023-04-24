<?php

use Hyperf\Database\Schema\Schema;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Migrations\Migration;

class CreateCustomerServicesTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('customer_services', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('member_id')->comment('會員 id');
            $table->tinyInteger('type')->comment('客服問題種類');
            $table->string('title')->comment('客服標題');
            $table->tinyInteger('is_unread')->comment('用戶是否未讀');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer_services');
    }
}
