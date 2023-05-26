<?php

use Hyperf\Database\Schema\Schema;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Migrations\Migration;

class CreateProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('products', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id')->comment('會員id');
            $table->string('type');
            $table->unsignedBigInteger('correspond_id');
            $table->string('name')->comment('產品名稱');
            $table->integer('expire')->comment('產品上下架');
            $table->datetime('start_time')->comment('產品上架時間');
            $table->datetime('end_time')->comment('產品下架時間');
            $table->string('currency')->comment('產品幣別');
            $table->decimal('selling_price')->comment('產品價格');
            $table->integer('diamond_price')->nullable()->comment('鑽石點數');
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
        Schema::dropIfExists('products');
    }
};
