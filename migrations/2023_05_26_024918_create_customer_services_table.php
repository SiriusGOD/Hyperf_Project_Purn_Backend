<?php

use Hyperf\Database\Schema\Schema;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Migrations\Migration;

class CreateCustomerServicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('customer_services', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('member_id')->comment('會員 id');
            $table->tinyInteger('type')->comment('客服問題種類');
            $table->string('title')->comment('客服標題');
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
        Schema::dropIfExists('customer_services');
    }
};
