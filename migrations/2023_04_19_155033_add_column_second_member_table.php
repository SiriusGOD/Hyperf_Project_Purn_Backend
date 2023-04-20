<?php

use Hyperf\Database\Schema\Schema;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Migrations\Migration;

class AddColumnSecondMemberTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('members', function (Blueprint $table) {
            $table->integer('diamond_quota')->comment('鑽石觀看次數 購買鑽石會員卡1天才會獲得')->default(0);
            $table->integer('vip_quota')->comment('VIP觀看次數 購買VIP會員卡1天才會獲得')->default(0);
            $table->integer('free_quota')->comment('免費觀看次數')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('members', function (Blueprint $table) {
            $table->dropColumn('diamond_quota');
            $table->dropColumn('vip_quota');
            $table->dropColumn('free_quota');
        });
    }
}
