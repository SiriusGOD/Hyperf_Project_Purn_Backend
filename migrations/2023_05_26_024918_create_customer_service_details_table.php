<?php

use Hyperf\Database\Schema\Schema;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Migrations\Migration;

class CreateCustomerServiceDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('customer_service_details', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('customer_service_id');
            $table->bigInteger('user_id')->nullable()->comment('客服 id');
            $table->bigInteger('member_id')->nullable()->comment('會員 id');
            $table->text('message')->comment('訊息');
            $table->string('image_url')->default('')->comment('圖片 url');
            $table->tinyInteger('is_read')->comment('是否已讀');
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
        Schema::dropIfExists('customer_service_details');
    }
};
