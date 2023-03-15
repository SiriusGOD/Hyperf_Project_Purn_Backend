<?php

use Hyperf\Database\Schema\Schema;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Migrations\Migration;

class CreateProductsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('user_id')->comment('會員id')->unsigned();
            $table->string('type');
            $table->bigInteger('correspond_id')->unsigned();
            $table->string('name')->comment('產品名稱');
            $table->integer('position')->comment('產品位置');
            $table->timestamp('start_time')->comment('產品上架時間');
            $table->timestamp('end_time')->comment('產品下架時間');
            $table->string('currency')->comment('產品幣別');
            $table->decimal('selling_price')->comment('產品價格');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
}
