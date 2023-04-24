<?php

use Hyperf\Database\Schema\Schema;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Migrations\Migration;

class CreateMemberInviteLog extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('member_invite_log', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('invited_by')->comment('用户被谁邀请')->index();
            $table->integer('member_id')->comment('用户ID')->index();
            $table->integer('level')->comment('代理等級')->index();
            $table->string('invited_code')->comment('推廌碼')->index();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('member_invite_log');
    }
}
