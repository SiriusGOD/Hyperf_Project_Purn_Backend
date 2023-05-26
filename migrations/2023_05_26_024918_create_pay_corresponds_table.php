<?php

use Hyperf\Database\Schema\Schema;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Migrations\Migration;

class CreatePayCorrespondsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pay_corresponds', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('product_id')->index()->comment('商品id');
            $table->unsignedInteger('pay_id')->index()->comment('支付id');
            $table->datetimes();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('pay_corresponds');
    }
};
