<?php

use Hyperf\Database\Schema\Schema;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Migrations\Migration;

class CreatePaysTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pays', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('user_id')->nullable()->comment('管理者 id');
            $table->string('name', 20)->comment('支付名稱');
            $table->string('pronoun', 20)->comment('代稱');
            $table->string('proxy', 10)->default('online')->comment('支付類型 agent 代理 / online 一般線上支付');
            $table->unsignedInteger('expire')->default(0)->index()->comment('是否關閉:0.否、1.是');
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
        Schema::dropIfExists('pays');
    }
};
