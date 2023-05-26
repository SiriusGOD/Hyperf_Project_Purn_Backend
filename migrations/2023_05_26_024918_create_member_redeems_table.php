<?php

use Hyperf\Database\Schema\Schema;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Migrations\Migration;

class CreateMemberRedeemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('member_redeems', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('member_id')->index()->comment('會員ID');
            $table->integer('redeem_id')->index()->comment('兌換ID');
            $table->string('redeem_code')->comment('兌換code');
            $table->integer('redeem_category_id')->index()->comment('兌換分類ID');
            $table->datetimes();
            $table->datetime('start')->index()->comment('開始日期');
            $table->datetime('end')->index()->comment('結束日期');
            $table->integer('used')->comment('己使用點數');
            $table->integer('status')->default(0)->comment('況態 0:可用,1:不可用');
            $table->integer('diamond_point')->default(0)->comment('鑽石點數');
            $table->integer('vip_days')->default(0)->comment('VIP天數');
            $table->integer('free_watch')->default(0)->comment('免費觀看次數');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('member_redeems');
    }
};
