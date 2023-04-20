<?php

use Hyperf\Database\Schema\Schema;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Migrations\Migration;

class AddColumnToMembers extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('members', function (Blueprint $table) {
            $table->string('aff')->default("")->comment('邀请码md5( md5(uuid) )');
            $table->integer('invited_by')->default(0)->comment('被谁 aff 邀请');
            $table->integer('invited_num')->default(0)->comment('已邀请安装个数');
            $table->decimal('tui_coins', 10, 2)->default(0)->comment('推广收入');
            $table->decimal('total_tui_coins', 10, 2)->default(0)->comment('累计推广收入');
        });

        Schema::dropIfExists('member_invite_start');
        Schema::create('member_invite_stat', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('member_id')->index();
            $table->decimal('d_count', 10, 2);
            $table->decimal('k_count', 10, 2);
            $table->integer('level')->comment('0 默认 1黄金 2 铂金 3钻石 4 星耀 5 王者')->index();
            $table->integer('differ_num')->comment('支付邀请日期');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('member_invite_stat');
    }
}
