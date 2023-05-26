<?php

use Hyperf\Database\Schema\Schema;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Migrations\Migration;

class CreateMemberInviteLogTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('member_invite_log', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('invited_by')->index()->comment('用户被谁邀请');
            $table->integer('member_id')->index()->comment('用户ID');
            $table->integer('level')->index()->comment('代理等級');
            $table->string('invited_code')->index()->comment('推廌碼');
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
        Schema::dropIfExists('member_invite_log');
    }
};
