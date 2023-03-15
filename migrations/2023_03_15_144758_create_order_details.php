<?php

use Hyperf\Database\Schema\Schema;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Migrations\Migration;

class CreateOrderDetails extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('order_details', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('order_id')->unsigned()->comment('訂單 id');
            $table->bigInteger('product_id')->unsigned()->comment('產品 id');
            $table->string('product_name')->unsigned()->comment('產品名稱');
            $table->string('product_currency')->comment('產品幣別');
            $table->decimal('product_selling_price')->comment('產品當時價格');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_details');
    }
}
