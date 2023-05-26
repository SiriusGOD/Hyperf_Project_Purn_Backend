<?php

use Hyperf\Database\Schema\Schema;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Migrations\Migration;

class CreateMemberInviteStatTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('member_invite_stat', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('member_id')->index();
            $table->decimal('d_count', 10);
            $table->decimal('k_count', 10);
            $table->integer('level')->index()->comment('0 默认 1黄金 2 铂金 3钻石 4 星耀 5 王者');
            $table->integer('differ_num')->comment('支付邀请日期');
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
        Schema::dropIfExists('member_invite_stat');
    }
};
