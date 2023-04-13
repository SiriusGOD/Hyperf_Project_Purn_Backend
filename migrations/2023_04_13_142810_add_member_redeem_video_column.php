<?php

use Hyperf\Database\Schema\Schema;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Migrations\Migration;

class AddMemberRedeemVideoColumn extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('member_redeem_videos', function (Blueprint $table) {
            $table->integer('redeem_category_id')->comment('兌換分類的ID');
            $table->integer('member_id')->index()->comment('會員ID');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('member_redeem_videos', function (Blueprint $table) {
            $table->dropColumn('redeem_category_id');
            $table->dropColumn('member_id');
        });
    }
}
