<?php

use Hyperf\Database\Schema\Schema;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Migrations\Migration;

class AddColumnBuyMemberLevelsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('buy_member_levels', function (Blueprint $table) {
            $table->string('member_level_type')->comment('會員卡類型')->index()->after('member_id');
            $table->timestamp('start_time')->comment('會員資格持續時間')->after('order_number');
            $table->timestamp('end_time')->comment('會員資格結束時間')->after('start_time');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('buy_member_levels', function (Blueprint $table) {
            $table->dropColumn('member_level_type');
            $table->dropColumn('start_time');
            $table->dropColumn('end_time');
        });
    }
}
