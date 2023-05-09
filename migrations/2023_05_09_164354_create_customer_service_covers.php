<?php

use Hyperf\Database\Schema\Schema;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Migrations\Migration;

class CreateCustomerServiceCovers extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('customer_service_covers', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('customer_service_id')->comment('客服 id');
            $table->string('url')->comment('圖片網址');
            $table->datetimes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer_service_covers');
    }
}
