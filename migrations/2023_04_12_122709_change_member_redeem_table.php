<?php

use Hyperf\Database\Schema\Schema;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Migrations\Migration;

class ChangeMemberRedeemTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('member_redeems', function (Blueprint $table) {
            $table->dropColumn('start');
            $table->dropColumn('end');
            $table->dropColumn('count');
            $table->dropColumn('counted');
        });

        Schema::table('member_redeems', function (Blueprint $table) {
            $table->timestamp('start')->comment('開始日期')->index();
            $table->timestamp('end')->comment('結束日期')->index();
            $table->integer('used')->comment('己使用點數');
            $table->integer('status')->default(0)->comment('況態 0:可用,1:不可用');
            $table->integer('diamond_point')->default(0)->comment('鑽石點數');
            $table->integer('vip_days')->default(0)->comment('VIP天數');
            $table->integer('free_watch')->default(0)->comment('免費觀看次數');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
    }
}
